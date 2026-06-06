<?php
	// 1. Start session and enforce login (just like camera.php)
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	if (empty($_SESSION['user_id'])) {
		header('Location: login.php');
		exit;
	}
	$userId = (int) $_SESSION['user_id'];

	include_once('config.php');

	$plants = [];
	$errorMsg = "";

	try {
		// 2. Query the user's actual saved plants
		$sql = "SELECT p.plant_id, p.nickname, p.location, p.status, p.image_url AS user_image, p.created_at,
		               s.common_name, s.scientific_name, s.description, s.image_url AS species_image,
		               s.sunlight_level, s.ideal_moisture_min
		        FROM plants p
		        LEFT JOIN plant_species s ON p.species_id = s.species_id
		        WHERE p.user_id = :user_id
		        ORDER BY p.created_at DESC";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$stmt->execute();
		$userPlants = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($userPlants as $plantRow) {
			$plants[] = [
				'plant_id' => (int)$plantRow['plant_id'],
				'nickname' => $plantRow['nickname'],
				'location' => !empty($plantRow['location']) ? $plantRow['location'] : 'Indoor',
				'status' => $plantRow['status'] ?? 'healthy',
				'created_at' => $plantRow['created_at'],
				'common_name' => $plantRow['common_name'],
				'scientific_name' => $plantRow['scientific_name'],
				'description' => $plantRow['description'],
				// Prioritize the photo the user took, fallback to species default image
				'image_url' => !empty($plantRow['user_image']) ? $plantRow['user_image'] : $plantRow['species_image'],
				'sunlight_level' => $plantRow['sunlight_level'],
				'ideal_moisture_min' => $plantRow['ideal_moisture_min'],
			];
		}
	} catch (PDOException $e) {
		$errorMsg = "Could not load your plants right now.";
	}

	function e($value) {
		return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
	}

	function plant_title($plant) {
		return !empty($plant['nickname']) ? $plant['nickname'] : ($plant['common_name'] ?? 'Unknown Plant');
	}

	function classify_plant($plant) {
		$status = $plant['status'] ?? 'healthy';
		return $status === 'healthy' ? 'healthy' : 'unhealthy';
	}

	function filter_categories($plant) {
		$commonName = strtolower($plant['common_name'] ?? '');
		$categories = [classify_plant($plant)];

		if ($commonName === 'tomato') {
			$categories[] = 'crops';
		}

		return implode(' ', array_unique($categories));
	}

	function image_for_plant($plant, $index) {
		return plant_image_src($plant['image_url'] ?? '', $plant['common_name'] ?? plant_title($plant));
	}

	function tag_for_plant($plant) {
		$status = $plant['status'] ?? 'healthy';
		$commonName = strtolower($plant['common_name'] ?? '');

		if ($commonName === 'rose') {
			return ['icon' => 'nutrition', 'text' => 'Needs More Soil', 'detail' => 'Soil level is low. Add fresh nutrient-rich soil around the base.', 'tone' => 'danger'];
		}

		if ($status === 'needs_water') {
			return ['icon' => 'water_drop', 'text' => 'Needs Water', 'detail' => 'Soil moisture is low. Immediate watering required.', 'tone' => 'danger'];
		}

		if ($status === 'overwatered') {
			return ['icon' => 'humidity_high', 'text' => 'Overwatered', 'detail' => 'Soil moisture is above the ideal range.', 'tone' => 'danger'];
		}

		if ($status === 'wilting' || $status === 'dead') {
			return ['icon' => 'warning', 'text' => 'Needs Attention', 'detail' => 'Plant health is declining and should be checked.', 'tone' => 'danger'];
		}

		$sunlight = strtoupper(str_replace('_', ' ', $plant['sunlight_level'] ?? 'optimal light'));
		return ['icon' => 'wb_sunny', 'text' => $sunlight, 'detail' => 'Conditions look stable.', 'tone' => 'healthy'];
	}

	$totalPlants = count($plants);
	$healthyPlants = count(array_filter($plants, function($plant) {
		return classify_plant($plant) === 'healthy';
	}));
	$unhealthyPlants = count(array_filter($plants, function($plant) {
		return classify_plant($plant) === 'unhealthy';
	}));
	$healthyRate = $totalPlants > 0 ? round(($healthyPlants / $totalPlants) * 100) : 0;
	$featured = null;

	foreach ($plants as $plant) {
		if (classify_plant($plant) === 'unhealthy') {
			$featured = $plant;
			break;
		}
	}

	if (!$featured && count($plants) > 0) {
		$featured = $plants[0];
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My Plants - SproutSync</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
	<style>
		:root {
			--bg: #fcf9f8;
			--primary: #012d1d;
			--primary-soft: #1b5139;
			--mint: #cee9d3;
			--text: #1d2f27;
			--muted: #626b64;
			--line: #d5dbd4;
			--danger: #c83c3c;
			--danger-bg: #fff0f0;
		}

		* { box-sizing: border-box; }

		body {
			margin: 0;
			min-height: 100vh;
			background: #ecebea;
			color: var(--text);
			font-family: "Manrope", Arial, sans-serif;
			display: flex;
			justify-content: center;
		}

		.material-symbols-outlined {
			font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
			line-height: 1;
		}

		.page {
			width: 100%;
			max-width: 430px;
			min-height: 100vh;
			background: var(--bg);
			padding: 22px 18px 118px;
		}

		.header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 30px;
		}

		.brand {
			display: flex;
			align-items: center;
			gap: 10px;
			color: var(--primary);
			font-size: 21px;
			font-weight: 800;
		}

		.logo {
			width: 39px;
			height: 39px;
			border-radius: 50%;
			background: var(--mint);
			display: grid;
			place-items: center;
			border: 1px solid #b6d9c0;
		}

		.logo .material-symbols-outlined {
			color: var(--primary);
			font-size: 24px;
		}

		.icon-btn {
			width: 38px;
			height: 38px;
			border: 0;
			border-radius: 50%;
			background: transparent;
			color: var(--primary);
			display: grid;
			place-items: center;
			cursor: pointer;
		}

		.hero-title {
			margin: 0;
			color: #031f16;
			font-size: 30px;
			line-height: 1.05;
			font-weight: 800;
			letter-spacing: -0.2px;
		}

		.hero-copy {
			margin: 8px 0 24px;
			max-width: 330px;
			color: var(--muted);
			font-size: 14px;
			line-height: 1.45;
		}

		.filters {
			display: flex;
			gap: 9px;
			overflow-x: auto;
			padding-bottom: 2px;
			margin-bottom: 26px;
			scrollbar-width: none;
		}

		.filters::-webkit-scrollbar { display: none; }

		.filter-btn {
			border: 0;
			border-radius: 999px;
			background: var(--mint);
			color: var(--primary);
			min-width: 78px;
			padding: 12px 14px;
			font-size: 9px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.7px;
			cursor: pointer;
		}

		.filter-btn.active {
			background: var(--primary);
			color: #fff;
			box-shadow: inset 0 -3px 0 rgba(255,255,255,0.16);
		}

		.favorite-filter {
			display: flex;
			align-items: center;
			gap: 6px;
			flex: 0 0 auto;
		}

		.favorite-plus {
			width: 36px;
			height: 36px;
			border: 0;
			border-radius: 50%;
			background: var(--primary);
			color: #fff;
			display: grid;
			place-items: center;
			cursor: pointer;
			box-shadow: 0 6px 14px rgba(1, 45, 29, 0.16);
		}

		.favorite-plus .material-symbols-outlined {
			font-size: 20px;
		}

		.featured-card,
		.history-card,
		.empty-card {
			background: #fff;
			border: 1px solid var(--line);
			border-radius: 9px;
			overflow: hidden;
			box-shadow: 0 2px 8px rgba(1, 45, 29, 0.08);
		}

		.featured-card {
			margin-bottom: 28px;
		}

		.image-wrap {
			position: relative;
			height: 280px;
			background: #d8ded7;
		}

		.image-wrap img,
		.history-image img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: block;
		}

		.badge {
			position: absolute;
			top: 17px;
			left: 18px;
			background: rgba(255, 255, 255, 0.95);
			border-radius: 999px;
			padding: 6px 11px;
			display: flex;
			align-items: center;
			gap: 7px;
			color: var(--primary);
			font-size: 9px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.8px;
		}

		.badge-dot {
			width: 7px;
			height: 7px;
			border-radius: 50%;
			background: var(--danger);
		}

		.featured-body {
			padding: 18px 26px 24px;
		}

		.date-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 13px;
			font-family: "JetBrains Mono", monospace;
			color: #5b625d;
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 1px;
		}

		.star {
			color: var(--primary);
			font-size: 22px;
			font-variation-settings: "FILL" 1, "wght" 400, "GRAD" 0, "opsz" 24;
		}

		.plant-title {
			margin: 0 0 7px;
			color: #071f16;
			font-size: 23px;
			line-height: 1.1;
			font-weight: 700;
		}

		.location {
			margin: 0;
			color: var(--muted);
			font-size: 13px;
		}

		.warning-box {
			margin: 18px 0 20px;
			border: 1px solid #f1cece;
			background: var(--danger-bg);
			border-radius: 8px;
			padding: 13px 13px;
			display: grid;
			grid-template-columns: 24px 1fr;
			gap: 9px;
			color: #643131;
			font-size: 12px;
			line-height: 1.25;
		}

		.warning-box .material-symbols-outlined {
			color: var(--danger);
			font-size: 22px;
		}

		.warning-title {
			display: block;
			margin-bottom: 2px;
			color: var(--danger);
			font-size: 10px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.7px;
		}

		.history-badge {
			top: 12px;
			left: 12px;
		}

		.history-warning {
			display: none;
			margin: 12px 0 13px;
			border: 1px solid #f1cece;
			background: var(--danger-bg);
			border-radius: 8px;
			padding: 12px;
			grid-template-columns: 22px 1fr;
			gap: 8px;
			color: #643131;
			font-size: 11px;
			line-height: 1.25;
		}

		.history-warning .material-symbols-outlined {
			color: var(--danger);
			font-size: 20px;
		}

		.history-card[data-categories~="unhealthy"] .history-warning {
			display: grid;
		}

		.primary-btn {
			display: block;
			width: 100%;
			border: 0;
			border-radius: 6px;
			background: var(--primary);
			color: #fff;
			padding: 14px 16px;
			text-align: center;
			text-decoration: none;
			font-size: 10px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.7px;
			cursor: pointer;
		}

		.insights {
			background: linear-gradient(135deg, #1a5138, #123f2d);
			border: 1px solid #0d3022;
			border-radius: 9px;
			color: #cce7d4;
			padding: 35px 34px 30px;
			margin-bottom: 26px;
			box-shadow: 0 4px 12px rgba(1, 45, 29, 0.16);
		}

		.insights h2 {
			margin: 0 0 26px;
			font-size: 24px;
			line-height: 1;
			color: rgba(206, 233, 211, 0.78);
		}

		.insight-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 14px 0;
			border-bottom: 1px solid rgba(206, 233, 211, 0.13);
			font-size: 13px;
			color: rgba(206, 233, 211, 0.7);
		}

		.insight-row strong {
			color: #b7e5c4;
			font-size: 23px;
			font-weight: 700;
		}

		.insight-row.warning strong {
			color: #ffd166;
		}

		.insights em {
			display: block;
			margin-top: 25px;
			color: rgba(206, 233, 211, 0.65);
			font-size: 12px;
			line-height: 1.35;
		}

		.history-list {
			display: grid;
			gap: 24px;
		}

		.history-card {
			display: block;
		}

		.history-image {
			position: relative;
			height: 204px;
			background: #d8ded7;
		}

		.date-chip {
			position: absolute;
			left: 10px;
			bottom: 9px;
			background: rgba(255, 255, 255, 0.96);
			border-radius: 2px;
			padding: 8px 9px;
			font-family: "JetBrains Mono", monospace;
			font-size: 10px;
			text-transform: uppercase;
			color: #47504a;
			letter-spacing: 0.7px;
		}

		.history-body {
			padding: 14px 18px 17px;
		}

		.history-head {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 12px;
		}

		.history-title {
			margin: 0;
			color: #22342c;
			font-size: 17px;
			font-weight: 600;
		}

		.status-dot {
			width: 10px;
			height: 10px;
			border-radius: 50%;
			background: #52755e;
			flex: 0 0 auto;
		}

		.status-dot.unhealthy {
			background: #4a1507;
		}

		.tag {
			display: flex;
			align-items: center;
			gap: 8px;
			color: #53635a;
			font-size: 9px;
			text-transform: uppercase;
			letter-spacing: 0.8px;
			margin-bottom: 13px;
		}

		.tag .material-symbols-outlined {
			font-size: 17px;
		}

		.progress {
			height: 4px;
			border-radius: 999px;
			background: #e5e9e4;
			overflow: hidden;
		}

		.progress span {
			display: block;
			height: 100%;
			border-radius: inherit;
			background: #52755e;
		}

		.progress.unhealthy span {
			background: #0d0d0d;
		}

		.history-diagnosis {
			display: none;
			width: 100%;
			margin-top: 14px;
			border: 0;
			border-radius: 6px;
			background: var(--primary);
			color: #fff;
			padding: 12px 14px;
			text-align: center;
			text-decoration: none;
			font-size: 9px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.7px;
		}

		.history-card[data-categories~="unhealthy"] .history-diagnosis {
			display: block;
		}

		.load-more {
			width: 240px;
			margin: 50px auto 0;
			border: 2px solid var(--primary);
			border-radius: 999px;
			background: transparent;
			color: var(--primary);
			padding: 13px 20px;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 24px;
			font-size: 9px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			cursor: pointer;
		}

		.empty-card {
			padding: 40px 24px;
			text-align: center;
			color: var(--muted);
		}

		.empty-card .material-symbols-outlined {
			font-size: 54px;
			color: rgba(1, 45, 29, 0.35);
			margin-bottom: 12px;
		}

		.empty-card h2 {
			margin: 0 0 8px;
			color: var(--primary);
			font-size: 20px;
		}

		.bottom-nav-wrap {
			position: fixed;
			left: 0;
			right: 0;
			bottom: 0;
			z-index: 20;
			display: flex;
			justify-content: center;
			pointer-events: none;
		}

		.bottom-nav {
			width: 100%;
			max-width: 430px;
			height: 78px;
			background: rgba(255, 255, 255, 0.96);
			border-top: 1px solid rgba(213, 219, 212, 0.8);
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 0 24px;
			position: relative;
			pointer-events: auto;
			box-shadow: 0 -8px 22px rgba(1, 45, 29, 0.06);
		}

		.nav-side {
			display: flex;
			align-items: center;
			justify-content: space-around;
			gap: 24px;
		}

		.nav-item {
			width: 48px;
			color: #26352e;
			text-decoration: none;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			gap: 4px;
			font-size: 8px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.7px;
			position: relative;
		}

		.nav-item.active {
			color: var(--primary);
		}

		.nav-item .material-symbols-outlined {
			font-size: 23px;
		}

		.nav-dot {
			position: absolute;
			top: 4px;
			right: 7px;
			width: 6px;
			height: 6px;
			border-radius: 50%;
			background: #d82727;
		}

		.camera-btn {
			position: absolute;
			left: 50%;
			top: -29px;
			transform: translateX(-50%);
			width: 70px;
			height: 70px;
			border: 6px solid var(--bg);
			border-radius: 50%;
			background: #1b5139;
			color: #fff;
			display: grid;
			place-items: center;
			box-shadow: 0 10px 22px rgba(1, 45, 29, 0.24);
			cursor: pointer;
		}

		.camera-btn .material-symbols-outlined {
			font-size: 31px;
		}

		.camera-modal {
			position: fixed;
			inset: 0;
			z-index: 40;
			background: rgba(0, 0, 0, 0.9);
			display: none;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			padding: 24px;
		}

		.camera-modal.open { display: flex; }

		.close-camera {
			position: absolute;
			top: 24px;
			right: 24px;
			width: 42px;
			height: 42px;
			border: 0;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.2);
			color: #fff;
			display: grid;
			place-items: center;
			cursor: pointer;
		}

		.camera-frame {
			width: min(88vw, 360px);
			aspect-ratio: 3 / 4;
			border-radius: 24px;
			background: #101513;
			border: 1px solid rgba(255, 255, 255, 0.2);
			overflow: hidden;
			position: relative;
			display: grid;
			place-items: center;
		}

		@keyframes cameraScan {
			0% { transform: translateY(0); }
			50% { transform: translateY(335px); }
			100% { transform: translateY(0); }
		}

		.camera-frame::after {
			content: "";
			position: absolute;
			left: 0;
			right: 0;
			top: 0;
			height: 3px;
			background: linear-gradient(90deg, transparent, #59f19a, transparent);
			box-shadow: 0 0 18px rgba(89, 241, 154, 0.95);
			animation: cameraScan 3s linear infinite;
			pointer-events: none;
		}

		#camera-feed {
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: none;
		}

		#camera-feed.active { display: block; }

		.camera-placeholder,
		.camera-hint {
			color: rgba(255,255,255,0.72);
			text-align: center;
			font-size: 13px;
		}

		.camera-placeholder .material-symbols-outlined {
			display: block;
			font-size: 54px;
			margin-bottom: 10px;
			color: rgba(255,255,255,0.34);
		}

		.camera-hint { margin-top: 24px; }

		.capture-btn {
			width: 76px;
			height: 76px;
			border: 5px solid #fff;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.18);
			margin-top: 22px;
			display: grid;
			place-items: center;
			color: #fff;
			cursor: pointer;
			box-shadow: 0 8px 24px rgba(255, 255, 255, 0.18);
		}

		.camera-actions {
			display: flex;
			align-items: center;
			gap: 16px;
			margin-top: 22px;
		}

		.upload-btn {
			width: 56px;
			height: 56px;
			border: 1px solid rgba(255,255,255,0.55);
			border-radius: 50%;
			background: rgba(255,255,255,0.12);
			color: #fff;
			display: grid;
			place-items: center;
			cursor: pointer;
		}

		.upload-btn .material-symbols-outlined {
			font-size: 27px;
		}

		.capture-btn:disabled {
			opacity: 0.55;
			cursor: wait;
		}

		.capture-btn .material-symbols-outlined {
			font-size: 31px;
		}

		.scan-result {
			width: min(88vw, 360px);
			min-height: 58px;
			margin-top: 16px;
			border-radius: 12px;
			background: rgba(255, 255, 255, 0.12);
			color: #fff;
			padding: 13px 15px;
			font-size: 13px;
			line-height: 1.35;
			text-align: center;
			display: none;
		}

		.scan-result.show {
			display: block;
		}

		.scan-result strong {
			display: block;
			font-size: 16px;
			margin-bottom: 3px;
		}

		.favorite-modal {
			position: fixed;
			inset: 0;
			z-index: 36;
			background: rgba(1, 45, 29, 0.46);
			backdrop-filter: blur(8px);
			display: none;
			align-items: center;
			justify-content: center;
			padding: 22px;
		}

		.favorite-modal.open {
			display: flex;
		}

		.favorite-panel {
			width: min(100%, 390px);
			max-height: 82vh;
			background: var(--bg);
			border: 1px solid var(--line);
			border-radius: 16px;
			box-shadow: 0 24px 60px rgba(1, 45, 29, 0.24);
			padding: 20px;
			display: flex;
			flex-direction: column;
			gap: 16px;
		}

		.favorite-panel-head {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 16px;
		}

		.favorite-panel h2 {
			margin: 0 0 5px;
			color: var(--primary);
			font-size: 22px;
			line-height: 1.1;
		}

		.favorite-panel p {
			margin: 0;
			color: var(--muted);
			font-size: 12px;
			line-height: 1.35;
		}

		.favorite-close {
			width: 36px;
			height: 36px;
			border: 0;
			border-radius: 50%;
			background: #eef4ee;
			color: var(--primary);
			display: grid;
			place-items: center;
			cursor: pointer;
			flex: 0 0 auto;
		}

		.favorite-list {
			display: grid;
			gap: 10px;
			overflow-y: auto;
			padding-right: 3px;
		}

		.favorite-choice {
			border: 1px solid var(--line);
			border-radius: 12px;
			background: #fff;
			padding: 13px;
			display: grid;
			grid-template-columns: 24px 1fr;
			gap: 11px;
			align-items: center;
			cursor: pointer;
		}

		.favorite-choice input {
			width: 18px;
			height: 18px;
			accent-color: var(--primary);
		}

		.favorite-name {
			display: block;
			color: var(--primary);
			font-size: 15px;
			font-weight: 800;
		}

		.favorite-science {
			display: block;
			margin-top: 2px;
			color: var(--muted);
			font-size: 11px;
		}

		.favorite-save {
			border: 0;
			border-radius: 10px;
			background: var(--primary);
			color: #fff;
			padding: 14px;
			font-size: 11px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.7px;
			cursor: pointer;
		}

		@media (max-width: 370px) {
			.page { padding-left: 12px; padding-right: 12px; }
			.nav-side { gap: 14px; }
			.bottom-nav { padding: 0 16px; }
			.image-wrap { height: 245px; }
		}
	</style>
</head>
<body>
	<div class="page">
		<header class="header">
			<div class="brand">
				<span class="logo"><span class="material-symbols-outlined">psychiatry</span></span>
				SproutSync
			</div>
			<button class="icon-btn" type="button" aria-label="Notifications">
				<span class="material-symbols-outlined">notifications</span>
			</button>
		</header>

		<main>
			<h1 class="hero-title">Scan History</h1>
			<p class="hero-copy">Keep track of your botanical journey and plant health trends.</p>

			<div class="filters" aria-label="Plant filters">
				<button class="filter-btn active" type="button" data-filter="all">All Scans</button>
				<button class="filter-btn" type="button" data-filter="unhealthy">Unhealthy</button>
				<span class="favorite-filter">
					<button class="filter-btn" type="button" data-filter="favorites">Favorites</button>
					<button class="favorite-plus" type="button" aria-label="Choose favorite plants" onclick="openFavoriteModal()">
						<span class="material-symbols-outlined">add</span>
					</button>
				</span>
				<button class="filter-btn" type="button" data-filter="crops">Crops</button>
			</div>

			<?php if (!empty($errorMsg)): ?>
				<div class="empty-card">
					<span class="material-symbols-outlined">error</span>
					<h2>Plants unavailable</h2>
					<p><?php echo e($errorMsg); ?></p>
				</div>
			<?php elseif ($totalPlants === 0): ?>
				<div class="empty-card">
					<span class="material-symbols-outlined">potted_plant</span>
					<h2>No scanned plants yet</h2>
					<p>You haven't added any plants to your garden.</p>
					<button class="primary-btn" type="button" style="margin-top: 15px; width: auto; padding: 12px 24px; display: inline-block;" onclick="window.location.href='camera.php'">
						Scan Your First Plant
					</button>
				</div>
			<?php else: ?>
				<?php
					$featuredTag = tag_for_plant($featured);
					$featuredCategory = classify_plant($featured);
					$featuredDate = strtoupper(date('M d, Y - H:i A', strtotime($featured['created_at'] ?? 'now')));
				?>
				<article class="featured-card plant-card" data-categories="<?php echo e(filter_categories($featured)); ?>" data-plant-name="<?php echo e(plant_title($featured)); ?>">
					<div class="image-wrap">
						<img src="<?php echo e(image_for_plant($featured, 0)); ?>" alt="<?php echo e(plant_title($featured)); ?>">
						<?php if ($featuredCategory === 'unhealthy'): ?>
							<div class="badge"><span class="badge-dot"></span>Critical Attention</div>
						<?php endif; ?>
					</div>
					<div class="featured-body">
						<div class="date-row">
							<span><?php echo e($featuredDate); ?></span>
							<span class="material-symbols-outlined star">star</span>
						</div>
						<h2 class="plant-title"><?php echo e(plant_title($featured)); ?></h2>
						<p class="location"><?php echo e($featured['location'] ?: 'Indoor'); ?></p>
						<div class="warning-box">
							<span class="material-symbols-outlined"><?php echo e($featuredTag['icon']); ?></span>
							<span>
								<strong class="warning-title"><?php echo e($featuredTag['text']); ?></strong>
								<?php echo e($featuredTag['detail']); ?>
							</span>
						</div>
						<a class="primary-btn" href="diagnose.php?plant=<?php echo (int)$featured['plant_id']; ?>">View Diagnosis</a>
					</div>
				</article>

				<section class="insights">
					<h2>Scan Insights</h2>
					<div class="insight-row"><span>Total Scans</span><strong><?php echo $totalPlants; ?></strong></div>
					<div class="insight-row"><span>Healthy Rate</span><strong><?php echo $healthyRate; ?>%</strong></div>
					<div class="insight-row warning"><span>Pending Tasks</span><strong><?php echo $unhealthyPlants; ?></strong></div>
					<em>"You've scanned <?php echo max(0, $totalPlants - 1); ?> more plants than last month. Your urban jungle is thriving!"</em>
				</section>

				<section class="history-list" aria-label="Plant scan list">
					<?php foreach ($plants as $index => $plant): ?>
						<?php
							if ((int)$plant['plant_id'] === (int)$featured['plant_id']) {
								continue;
							}
							$category = classify_plant($plant);
							$tag = tag_for_plant($plant);
							$progress = $category === 'unhealthy' ? 42 : 86;
						?>
						<article class="history-card plant-card" data-categories="<?php echo e(filter_categories($plant)); ?>" data-plant-name="<?php echo e(plant_title($plant)); ?>">
							<div class="history-image">
								<img src="<?php echo e(image_for_plant($plant, $index + 1)); ?>" alt="<?php echo e(plant_title($plant)); ?>">
								<?php if ($category === 'unhealthy'): ?>
									<div class="badge history-badge"><span class="badge-dot"></span>Critical Attention</div>
								<?php endif; ?>
								<span class="date-chip"><?php echo e(date('M d', strtotime($plant['created_at'] ?? 'now'))); ?></span>
							</div>
							<div class="history-body">
								<div class="history-head">
									<h2 class="history-title"><?php echo e(plant_title($plant)); ?></h2>
									<span class="status-dot <?php echo $category === 'unhealthy' ? 'unhealthy' : ''; ?>"></span>
								</div>
								<div class="tag">
									<span class="material-symbols-outlined"><?php echo e($tag['icon']); ?></span>
									<?php echo e($tag['text']); ?>
								</div>
								<div class="history-warning">
									<span class="material-symbols-outlined"><?php echo e($tag['icon']); ?></span>
									<span>
										<strong class="warning-title"><?php echo e($tag['text']); ?></strong>
										<?php echo e($tag['detail']); ?>
									</span>
								</div>
								<div class="progress <?php echo $category === 'unhealthy' ? 'unhealthy' : ''; ?>">
									<span style="width: <?php echo (int)$progress; ?>%"></span>
								</div>
								<a class="history-diagnosis" href="diagnose.php?plant=<?php echo (int)$plant['plant_id']; ?>">View Diagnosis</a>
							</div>
						</article>
					<?php endforeach; ?>
				</section>

				<button class="load-more" type="button">
					Load More<br>History
					<span class="material-symbols-outlined">expand_more</span>
				</button>
			<?php endif; ?>
		</main>
	</div>

	<?php $activePage = 'plants'; include('nav.php'); ?>

	<div class="favorite-modal" id="favorite-modal" aria-hidden="true">
		<div class="favorite-panel" role="dialog" aria-modal="true" aria-labelledby="favorite-title">
			<div class="favorite-panel-head">
				<div>
					<h2 id="favorite-title">Choose Favorites</h2>
					<p>Select the plants you want to see under Favorites.</p>
				</div>
				<button class="favorite-close" type="button" aria-label="Close favorites" onclick="closeFavoriteModal()">
					<span class="material-symbols-outlined">close</span>
				</button>
			</div>
			<div class="favorite-list">
				<?php foreach ($plants as $plant): ?>
					<label class="favorite-choice">
						<input type="checkbox" class="favorite-checkbox" value="<?php echo e(plant_title($plant)); ?>">
						<span>
							<span class="favorite-name"><?php echo e(plant_title($plant)); ?></span>
							<span class="favorite-science"><?php echo e($plant['scientific_name']); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
			<button class="favorite-save" type="button" onclick="saveFavorites()">Save Favorites</button>
		</div>
	</div>

	<div class="camera-modal" id="camera-modal" aria-hidden="true">
		<button class="close-camera" type="button" aria-label="Close camera" onclick="closeCamera()">
			<span class="material-symbols-outlined">close</span>
		</button>
		<div class="camera-frame">
			<video id="camera-feed" autoplay playsinline></video>
			<canvas id="camera-canvas" hidden></canvas>
			<div class="camera-placeholder" id="camera-placeholder">
				<span class="material-symbols-outlined">photo_camera</span>
				Requesting camera access...
			</div>
		</div>
		<p class="camera-hint">Position plant within frame to analyze health</p>
		<div class="camera-actions">
			<label class="upload-btn" for="image-upload" aria-label="Upload plant image from phone">
				<span class="material-symbols-outlined">upload_file</span>
			</label>
			<input id="image-upload" type="file" accept="image/*" capture="environment" hidden onchange="uploadPlantImage(this)">
			<button class="capture-btn" id="capture-btn" type="button" aria-label="Capture plant photo" onclick="capturePlantPhoto()">
				<span class="material-symbols-outlined">center_focus_strong</span>
			</button>
		</div>
		<div class="scan-result" id="scan-result"></div>
	</div>

	<script>
		let cameraStream = null;
		const defaultFavorites = ['Aloe Vera', 'Basil'];

		function getFavorites() {
			const saved = localStorage.getItem('sproutsyncFavorites');
			return saved ? JSON.parse(saved) : defaultFavorites;
		}

		function setFavorites(favorites) {
			localStorage.setItem('sproutsyncFavorites', JSON.stringify(favorites));
		}

		function updateFavoriteCategories() {
			const favorites = new Set(getFavorites());
			document.querySelectorAll('.plant-card').forEach(function(card) {
				if (!card.dataset.baseCategories) {
					card.dataset.baseCategories = card.dataset.categories || '';
				}

				const categories = card.dataset.baseCategories.split(' ').filter(Boolean);
				if (favorites.has(card.dataset.plantName)) {
					categories.push('favorites');
				}

				card.dataset.categories = Array.from(new Set(categories)).join(' ');
			});
		}

		function applyCurrentFilter() {
			const activeButton = document.querySelector('.filter-btn.active');
			const filter = activeButton ? activeButton.dataset.filter : 'all';

			document.querySelectorAll('.plant-card').forEach(function(card) {
				const categories = (card.dataset.categories || '').split(' ');
				card.style.display = filter === 'all' || categories.includes(filter) ? 'block' : 'none';
			});
		}

		function openFavoriteModal() {
			const favorites = new Set(getFavorites());
			document.querySelectorAll('.favorite-checkbox').forEach(function(checkbox) {
				checkbox.checked = favorites.has(checkbox.value);
			});

			const modal = document.getElementById('favorite-modal');
			modal.classList.add('open');
			modal.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
		}

		function closeFavoriteModal() {
			const modal = document.getElementById('favorite-modal');
			modal.classList.remove('open');
			modal.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
		}

		function saveFavorites() {
			const favorites = Array.from(document.querySelectorAll('.favorite-checkbox:checked')).map(function(checkbox) {
				return checkbox.value;
			});

			setFavorites(favorites);
			updateFavoriteCategories();
			applyCurrentFilter();
			closeFavoriteModal();
		}

		document.querySelectorAll('.filter-btn').forEach(function(button) {
			button.addEventListener('click', function() {
				document.querySelectorAll('.filter-btn').forEach(function(item) {
					item.classList.remove('active');
				});
				button.classList.add('active');

				applyCurrentFilter();
			});
		});

		updateFavoriteCategories();
		applyCurrentFilter();

		async function openCamera() {
			const modal = document.getElementById('camera-modal');
			const video = document.getElementById('camera-feed');
			const placeholder = document.getElementById('camera-placeholder');
			const result = document.getElementById('scan-result');

			modal.classList.add('open');
			modal.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
			result.classList.remove('show');
			result.innerHTML = '';
			placeholder.innerHTML = '<span class="material-symbols-outlined">photo_camera</span>Requesting camera access...';

			try {
				cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
				video.srcObject = cameraStream;
				video.classList.add('active');
				placeholder.style.display = 'none';
			} catch (err) {
				placeholder.style.display = 'block';
				placeholder.innerHTML = '<span class="material-symbols-outlined">error</span>Camera access denied';
			}
		}

		function closeCamera() {
			const modal = document.getElementById('camera-modal');
			const video = document.getElementById('camera-feed');
			const placeholder = document.getElementById('camera-placeholder');
			const result = document.getElementById('scan-result');

			modal.classList.remove('open');
			modal.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
			video.classList.remove('active');
			video.srcObject = null;
			placeholder.style.display = 'block';
			result.classList.remove('show');
			result.innerHTML = '';

			if (cameraStream) {
				cameraStream.getTracks().forEach(function(track) {
					track.stop();
				});
				cameraStream = null;
			}
		}

		async function capturePlantPhoto() {
			const video = document.getElementById('camera-feed');
			const canvas = document.getElementById('camera-canvas');
			const result = document.getElementById('scan-result');
			const captureButton = document.getElementById('capture-btn');

			if (!video.videoWidth || !video.videoHeight) {
				result.innerHTML = 'Camera is not ready yet.';
				result.classList.add('show');
				return;
			}

			canvas.width = video.videoWidth;
			canvas.height = video.videoHeight;
			canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

			const imageData = canvas.toDataURL('image/png');
			await submitPlantImage(imageData);
		}

		async function submitPlantImage(imageData) {
			const result = document.getElementById('scan-result');
			const captureButton = document.getElementById('capture-btn');
			const formData = new FormData();
			formData.append('image_data', imageData);
			captureButton.disabled = true;
			result.innerHTML = 'Scanning plant with Pl@ntNet...';
			result.classList.add('show');

			try {
				const response = await fetch('scan_plant.php', {
					method: 'POST',
					body: formData
				});
				const data = await response.json();

				if (!data.ok) {
					result.innerHTML = data.error || 'Plant scan failed. Try another photo.';
					return;
				}

				const confidence = Math.round((data.confidence || 0) * 100);
				const matched = data.matched_species_id ? 'Matched to SproutSync species #' + data.matched_species_id : 'Not matched to your local species yet';
				result.innerHTML = '<strong>' + data.plant + '</strong>' + data.scientific_name + '<br>' + confidence + '% confidence<br>' + matched;
			} catch (error) {
				result.innerHTML = 'Plant scan failed. Check your API key and internet connection.';
			} finally {
				captureButton.disabled = false;
			}
		}

		function uploadPlantImage(input) {
			const file = input.files && input.files[0];

			if (!file) {
				return;
			}

			const reader = new FileReader();
			reader.onload = function(event) {
				submitPlantImage(event.target.result);
				input.value = '';
			};
			reader.readAsDataURL(file);
		}

		window.openFavoriteModal = openFavoriteModal;
		window.closeFavoriteModal = closeFavoriteModal;
		window.saveFavorites = saveFavorites;
		window.openCamera = openCamera;
		window.closeCamera = closeCamera;
		window.capturePlantPhoto = capturePlantPhoto;
		window.uploadPlantImage = uploadPlantImage;
	</script>
</body>
</html>