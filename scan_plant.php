<?php
	header('Content-Type: application/json');
	include_once('config.php');

	function respond($payload, $statusCode = 200) {
		http_response_code($statusCode);
		echo json_encode($payload);
		exit();
	}

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		respond(['ok' => false, 'error' => 'Invalid request method.'], 405);
	}

	if (empty($plantnetApiKey)) {
		respond([
			'ok' => false,
			'error' => 'Missing Pl@ntNet API key. Set PLANTNET_API_KEY in your environment or config.php.'
		], 400);
	}

	if (empty($_POST['image_data']) || !preg_match('/^data:image\/(png|jpeg);base64,/', $_POST['image_data'], $matches)) {
		respond(['ok' => false, 'error' => 'No camera image was received.'], 400);
	}

	$imageData = preg_replace('/^data:image\/(png|jpeg);base64,/', '', $_POST['image_data']);
	$imageBytes = base64_decode($imageData, true);

	if ($imageBytes === false) {
		respond(['ok' => false, 'error' => 'Camera image could not be decoded.'], 400);
	}

	$tempPath = tempnam(sys_get_temp_dir(), 'sproutsync_scan_');
	file_put_contents($tempPath, $imageBytes);

	$url = 'https://my-api.plantnet.org/v2/identify/all?api-key=' . urlencode($plantnetApiKey) . '&include-related-images=false&nb-results=3&lang=en';
	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_POST => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 35,
		CURLOPT_POSTFIELDS => [
			'images' => new CURLFile($tempPath, 'image/png', 'camera-scan.png'),
			'organs' => 'auto',
		],
	]);

	$response = curl_exec($ch);
	$error = curl_error($ch);
	$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	@unlink($tempPath);

	if ($response === false) {
		respond(['ok' => false, 'error' => 'Plant scan failed: ' . $error], 502);
	}

	$data = json_decode($response, true);

	if ($statusCode < 200 || $statusCode >= 300 || !is_array($data)) {
		respond([
			'ok' => false,
			'error' => 'Pl@ntNet could not identify this image.',
			'details' => $data['message'] ?? $response,
		], 502);
	}

	$best = $data['results'][0] ?? null;

	if (!$best) {
		respond(['ok' => false, 'error' => 'No plant match was found. Try a clearer leaf or flower photo.'], 200);
	}

	$species = $best['species'] ?? [];
	$scientificName = $species['scientificNameWithoutAuthor'] ?? ($species['scientificName'] ?? '');
	$commonNames = $species['commonNames'] ?? [];
	$commonName = $commonNames[0] ?? $scientificName;
	$confidence = isset($best['score']) ? round((float)$best['score'], 4) : 0;
	$matchedSpeciesId = null;

	try {
		$speciesStmt = $conn->query("SELECT species_id, common_name, scientific_name FROM plant_species");
		$localSpecies = $speciesStmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($localSpecies as $row) {
			$rowCommon = strtolower($row['common_name'] ?? '');
			$rowScientific = strtolower($row['scientific_name'] ?? '');
			$detectedScientific = strtolower($scientificName);
			$detectedCommonNames = array_map('strtolower', $commonNames);

			if (
				in_array($rowCommon, $detectedCommonNames, true) ||
				($rowScientific && strpos($detectedScientific, $rowScientific) !== false) ||
				($rowScientific && strpos($rowScientific, $detectedScientific) !== false)
			) {
				$matchedSpeciesId = (int)$row['species_id'];
				break;
			}
		}

		$insert = $conn->prepare("INSERT INTO scans(user_id, identified_species, scan_type, result_name, confidence, notes)
		                         VALUES(1, :identified_species, 'identify', :result_name, :confidence, :notes)");
		$notes = 'Source: Pl@ntNet. Best scientific match: ' . $scientificName;
		$insert->bindValue(':identified_species', $matchedSpeciesId, $matchedSpeciesId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
		$insert->bindValue(':result_name', $commonName);
		$insert->bindValue(':confidence', $confidence);
		$insert->bindValue(':notes', $notes);
		$insert->execute();
	} catch (PDOException $e) {
		// Identification still succeeded, so return it even if saving the scan fails.
	}

	respond([
		'ok' => true,
		'plant' => $commonName,
		'scientific_name' => $scientificName,
		'confidence' => $confidence,
		'matched_species_id' => $matchedSpeciesId,
		'remaining_requests' => $data['remainingIdentificationRequests'] ?? null,
	]);
?>
