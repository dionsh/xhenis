<?php
/* ==========================================================================
   camera.php  —  SproutSync standalone plant scanner  (login-protected)
   --------------------------------------------------------------------------
   1) GET  -> renders the camera UI (redirects to login if not signed in)
   2) POST action=scan -> identifies a plant via Pl@ntNet, logs it to `scans`
   3) POST action=save -> saves the plant + its photo into the user's `plants`

   Requires the image column:
     ALTER TABLE `plants` ADD `image_url` VARCHAR(255) DEFAULT NULL AFTER `nickname`;
   ========================================================================== */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* --- Login guard --------------------------------------------------------- */
if (empty($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Your session expired. Please log in again.']);
    } else {
        header('Location: login.php');
    }
    exit;
}
$userId = (int) $_SESSION['user_id'];


/* ==========================================================================
   AJAX HANDLERS — any POST returns JSON; a normal GET renders the page.
   ========================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'] ?? 'scan';

    try {
        if ($action === 'scan') {
            echo json_encode(handle_scan($conn, $plantnetApiKey, $userId));
        } elseif ($action === 'save') {
            echo json_encode(handle_save($conn, $userId));
        } else {
            echo json_encode(['ok' => false, 'error' => 'Unknown action.']);
        }
    } catch (Throwable $ex) {
        echo json_encode(['ok' => false, 'error' => 'Server error: ' . $ex->getMessage()]);
    }
    exit;
}


/* --------------------------------------------------------------------------
   SCAN: decode photo -> Pl@ntNet -> match species -> log -> return result.
   -------------------------------------------------------------------------- */
function handle_scan(PDO $conn, string $apiKey, int $userId): array
{
    $imageData = $_POST['image_data'] ?? '';
    if ($imageData === '' || !preg_match('#^data:image/(\w+);base64,#i', $imageData, $m)) {
        return ['ok' => false, 'error' => 'No valid image was received.'];
    }

    $ext  = (strtolower($m[1]) === 'png') ? 'png' : 'jpg';
    $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
    $raw  = base64_decode(substr($imageData, strpos($imageData, ',') + 1));
    if ($raw === false || strlen($raw) < 100) {
        return ['ok' => false, 'error' => 'The image could not be decoded.'];
    }

    $tmp = tempnam(sys_get_temp_dir(), 'sprout_') . '.' . $ext;
    file_put_contents($tmp, $raw);

    $url   = 'https://my-api.plantnet.org/v2/identify/all?api-key=' . urlencode($apiKey);
    $cfile = new CURLFile($tmp, $mime, 'plant.' . $ext);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => ['organs' => 'auto', 'images' => $cfile],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);
    @unlink($tmp);

    if ($response === false) {
        return ['ok' => false, 'error' => 'Could not reach Pl@ntNet (' . $curlErr . ').'];
    }
    $data = json_decode($response, true);
    if ($httpCode !== 200 || empty($data['results'])) {
        return ['ok' => false, 'error' => $data['message'] ?? 'No plant could be identified. Try a clearer photo.'];
    }

    $best       = $data['results'][0];
    $score      = (float) ($best['score'] ?? 0);
    $sciName    = $best['species']['scientificNameWithoutAuthor'] ?? '';
    $commonName = $best['species']['commonNames'][0] ?? $sciName;
    $matchedId  = find_species_id($conn, $sciName, $commonName);

    try {
        $log = $conn->prepare(
            "INSERT INTO scans (user_id, identified_species, scan_type, result_name, confidence, notes, scanned_at)
             VALUES (?, ?, 'identify', ?, ?, ?, NOW())"
        );
        $log->execute([
            $userId, $matchedId, $commonName, round($score, 2),
            'Source: Pl@ntNet. Best scientific match: ' . $sciName,
        ]);
    } catch (Throwable $e) { /* logging is best-effort */ }

    return [
        'ok'                 => true,
        'plant'              => $commonName,
        'scientific_name'    => $sciName,
        'confidence'         => $score,
        'matched_species_id' => $matchedId,
    ];
}


/* --------------------------------------------------------------------------
   SAVE: store the photo on disk, then insert the plant for this user.
   -------------------------------------------------------------------------- */
function handle_save(PDO $conn, int $userId): array
{
    $resultName = trim($_POST['result_name'] ?? '');
    $sciName    = trim($_POST['scientific_name'] ?? '');
    $location   = trim($_POST['location'] ?? '');
    if ($location === '') { $location = 'Indoor'; }

    $speciesId = $_POST['matched_species_id'] ?? '';
    $speciesId = ctype_digit((string) $speciesId) ? (int) $speciesId : null;
    if (!$speciesId) {
        $speciesId = find_species_id($conn, $sciName, $resultName);
    }

    $imagePath = save_plant_image($_POST['image_data'] ?? '', $userId);

    $nickname = $resultName !== '' ? $resultName : ($sciName !== '' ? $sciName : 'New Plant');
    $nickname = mb_substr($nickname, 0, 120);

    $ins = $conn->prepare(
        "INSERT INTO plants (user_id, species_id, nickname, image_url, location, planted_date, status, created_at)
         VALUES (?, ?, ?, ?, ?, CURDATE(), 'healthy', NOW())"
    );
    $ins->execute([$userId, $speciesId, $nickname, $imagePath, $location]);
    $plantId = (int) $conn->lastInsertId();

    try {
        $n = $conn->prepare(
            "INSERT INTO notifications (user_id, plant_id, type, title, message, created_at)
             VALUES (?, ?, 'general', ?, ?, NOW())"
        );
        $n->execute([$userId, $plantId, 'New plant added', $nickname . ' was added to your garden.']);
    } catch (Throwable $e) { /* ignore */ }

    return [
        'ok'           => true,
        'plant_id'     => $plantId,
        'nickname'     => $nickname,
        'diagnose_url' => 'diagnose.php?plant=' . $plantId,
    ];
}


/* Save a base64 data-URL image into assets/plants/uploads. Returns the web
   path, or null if there was no image / it couldn't be written. */
function save_plant_image(string $imageData, int $userId): ?string
{
    if ($imageData === '' || !preg_match('#^data:image/(\w+);base64,#i', $imageData, $m)) {
        return null;
    }
    $ext = (strtolower($m[1]) === 'png') ? 'png' : 'jpg';
    $raw = base64_decode(substr($imageData, strpos($imageData, ',') + 1));
    if ($raw === false || strlen($raw) < 100) {
        return null;
    }
    $dir = __DIR__ . '/assets/plants/uploads';
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return null;
    }
    $fname = 'plant_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (@file_put_contents($dir . '/' . $fname, $raw) === false) {
        return null;
    }
    return 'assets/plants/uploads/' . $fname;
}


/* Fuzzy-match a scientific/common name to a stored species id. */
function find_species_id(PDO $conn, string $sciName, string $commonName): ?int
{
    if ($sciName === '' && $commonName === '') {
        return null;
    }
    $q = $conn->prepare(
        "SELECT species_id FROM plant_species
         WHERE (? <> '' AND scientific_name LIKE ?)
            OR (? <> '' AND common_name     LIKE ?)
         LIMIT 1"
    );
    $q->execute([$sciName, '%' . $sciName . '%', $commonName, '%' . $commonName . '%']);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    return $row ? (int) $row['species_id'] : null;
}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>SproutSync - Scan</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                "primary": "#012d1d",
                "primary-container": "#1b4332",
                "surface": "#fcf9f8",
                "surface-container": "#f0eded",
                "background": "#fcf9f8",
                "outline-variant": "#c1c8c2",
                "secondary-fixed": "#cee9d3",
            },
            fontFamily: { heading: ['Manrope', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] }
        }
    }
}
</script>
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .material-symbols-outlined.filled { font-variation-settings: 'FILL' 1; }
    body { font-family: 'Manrope', sans-serif; }
    @keyframes scan { 0% { transform: translateY(0); } 50% { transform: translateY(300px); } 100% { transform: translateY(0); } }
</style>
</head>
<body class="bg-background text-[#414844] selection:bg-secondary-fixed pb-32">

<header class="sticky top-0 z-40 bg-background/90 backdrop-blur-md">
    <div class="flex justify-between items-center w-full px-6 py-4 max-w-lg mx-auto">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center overflow-hidden">
                <span class="material-symbols-outlined text-[#cee9d3] text-[20px] filled">eco</span>
            </div>
            <h1 class="font-bold text-xl text-primary font-heading">SproutSync</h1>
        </div>
    </div>
</header>

<main class="px-5 max-w-lg mx-auto">
    <div class="mt-2 mb-6">
        <h2 class="text-3xl text-primary font-bold font-heading tracking-tight">Scan a Plant</h2>
        <p class="text-sm mt-1 leading-relaxed">Point your camera at a plant to identify it, then add it to your garden.</p>
    </div>

    <div class="relative w-full aspect-[3/4] bg-zinc-900 border border-outline-variant/40 rounded-3xl overflow-hidden shadow-sm">
        <video id="camera-feed" class="w-full h-full object-cover hidden" autoplay playsinline></video>
        <canvas id="camera-canvas" hidden></canvas>
        <div id="camera-placeholder" class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="material-symbols-outlined text-white/30 text-6xl">photo_camera</span>
            <p class="text-white/50 text-xs mt-2">Requesting camera access&hellip;</p>
        </div>
        <div class="pointer-events-none absolute inset-0 border border-[#2ecc71]/50 rounded-3xl" style="box-shadow: inset 0 0 40px rgba(46, 204, 113, 0.2);"></div>
        <div id="scan-line" class="pointer-events-none absolute top-0 left-0 w-full h-1 bg-[#2ecc71] opacity-70 hidden" style="animation: scan 3s linear infinite;"></div>
    </div>

    <p class="text-center text-xs mt-3 opacity-70">Position the plant within the frame for the best result.</p>

    <div class="mt-6 flex items-center justify-center gap-5">
        <label class="w-14 h-14 rounded-full border border-primary/40 bg-secondary-fixed text-primary grid place-items-center cursor-pointer" for="image-upload" aria-label="Upload plant image">
            <span class="material-symbols-outlined">upload_file</span>
        </label>
        <input id="image-upload" type="file" accept="image/*" capture="environment" hidden onchange="uploadPlantImage(this)">
        <button id="capture-btn" onclick="takePhoto()" aria-label="Capture plant photo"
            class="w-20 h-20 rounded-full border-4 border-primary bg-white flex items-center justify-center shadow-lg active:scale-90 transition disabled:opacity-50 disabled:cursor-wait">
            <span class="material-symbols-outlined text-primary text-3xl">photo_camera</span>
        </button>
        <button onclick="restartCamera()" class="w-14 h-14 rounded-full border border-primary/40 bg-secondary-fixed text-primary grid place-items-center" aria-label="Restart camera">
            <span class="material-symbols-outlined">cameraswitch</span>
        </button>
    </div>

    <div id="result-panel" class="hidden bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden mt-8 mb-6">
        <div class="p-5">
            <div class="flex justify-between items-center text-[10px] font-mono uppercase tracking-widest mb-1.5">
                <span id="result-confidence">CONFIDENCE 0%</span>
                <span id="result-match" class="text-primary">&mdash;</span>
            </div>
            <h3 id="result-name" class="text-[22px] font-bold text-primary font-heading">&mdash;</h3>
            <p id="result-sci" class="text-[13px] italic mt-0.5 opacity-80">&mdash;</p>
            <div id="result-status" class="hidden mt-4 bg-secondary-fixed/60 rounded-xl p-3.5 flex gap-3 border border-secondary-fixed">
                <span class="material-symbols-outlined text-primary mt-0.5">check_circle</span>
                <div class="text-[13px] leading-tight text-primary" id="result-status-text"></div>
            </div>
            <button id="btn-add" onclick="addPlant()"
                class="block text-center w-full mt-5 bg-primary text-white py-3.5 rounded-xl text-xs font-bold uppercase tracking-widest shadow-md hover:bg-primary-container transition">
                ADD TO MY PLANTS
            </button>
            <button id="btn-diagnose" onclick="seeDiagnosis()"
                class="block text-center w-full mt-3 border border-primary text-primary py-3.5 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-primary/5 transition">
                SEE DIAGNOSIS
            </button>
        </div>
    </div>

    <div id="scan-error" class="hidden bg-red-50/60 border border-red-100 text-red-700 rounded-xl px-4 py-3 text-sm mt-6"></div>
</main>

<?php $activePage = 'camera'; @include('nav.php'); ?>

<script>
    let stream = null;
    let lastScan = null;
    let lastScanImage = null;
    let savedPlantId = null;

    // Bridge for any nav button that calls openCamera() — we're already here.
    function openCamera() { restartCamera(); }

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            const video = document.getElementById('camera-feed');
            video.srcObject = stream;
            video.classList.remove('hidden');
            document.getElementById('camera-placeholder').classList.add('hidden');
            document.getElementById('scan-line').classList.remove('hidden');
        } catch (err) {
            document.getElementById('camera-placeholder').innerHTML =
                '<span class="material-symbols-outlined text-red-400 text-4xl">error</span>' +
                '<p class="text-red-400 text-xs mt-2">Camera access denied. You can still upload a photo.</p>';
        }
    }

    function restartCamera() {
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        document.getElementById('camera-feed').classList.add('hidden');
        startCamera();
    }

    async function takePhoto() {
        const video = document.getElementById('camera-feed');
        const canvas = document.getElementById('camera-canvas');
        if (!video.videoWidth || !video.videoHeight) { showError('Camera is not ready yet.'); return; }
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        await submitPlantImage(canvas.toDataURL('image/jpeg', 0.9));
    }

    function uploadPlantImage(input) {
        const file = input.files && input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => { submitPlantImage(e.target.result); input.value = ''; };
        reader.readAsDataURL(file);
    }

    async function submitPlantImage(imageData) {
        const captureButton = document.getElementById('capture-btn');
        hideError();
        document.getElementById('result-panel').classList.add('hidden');
        captureButton.disabled = true;
        lastScanImage = imageData;

        const fd = new FormData();
        fd.append('action', 'scan');
        fd.append('image_data', imageData);

        try {
            const res = await fetch('camera.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.ok) { showError(data.error || 'Plant scan failed. Try another photo.'); return; }
            lastScan = data;
            savedPlantId = null;
            renderResult(data);
        } catch (err) {
            showError('Plant scan failed. Check your connection and try again.');
        } finally {
            captureButton.disabled = false;
        }
    }

    function renderResult(data) {
        const conf = Math.round((data.confidence || 0) * 100);
        document.getElementById('result-name').textContent = data.plant || 'Unknown plant';
        document.getElementById('result-sci').textContent = data.scientific_name || '';
        document.getElementById('result-confidence').textContent = 'CONFIDENCE ' + conf + '%';
        document.getElementById('result-match').textContent = data.matched_species_id ? 'IN YOUR SPECIES' : 'NEW SPECIES';
        document.getElementById('result-status').classList.add('hidden');
        const addBtn = document.getElementById('btn-add');
        addBtn.disabled = false;
        addBtn.textContent = 'ADD TO MY PLANTS';
        document.getElementById('result-panel').classList.remove('hidden');
        document.getElementById('result-panel').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    async function addPlant() {
        if (!lastScan) return null;
        if (savedPlantId) { showSaved('Already added to your garden.'); return { ok: true, plant_id: savedPlantId }; }

        const addBtn = document.getElementById('btn-add');
        addBtn.disabled = true;
        addBtn.textContent = 'SAVING…';

        const fd = new FormData();
        fd.append('action', 'save');
        fd.append('result_name', lastScan.plant || '');
        fd.append('scientific_name', lastScan.scientific_name || '');
        fd.append('matched_species_id', lastScan.matched_species_id ?? '');
        if (lastScanImage) fd.append('image_data', lastScanImage);

        try {
            const res = await fetch('camera.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.ok) {
                showError(data.error || 'Could not save the plant.');
                addBtn.disabled = false; addBtn.textContent = 'ADD TO MY PLANTS';
                return null;
            }
            savedPlantId = data.plant_id;
            addBtn.textContent = 'ADDED ✓';
            showSaved((data.nickname || 'Plant') + ' was added to your garden.');
            return data;
        } catch (err) {
            showError('Could not save the plant. Try again.');
            addBtn.disabled = false; addBtn.textContent = 'ADD TO MY PLANTS';
            return null;
        }
    }

    async function seeDiagnosis() {
        if (!savedPlantId) {
            const result = await addPlant();
            if (!result || !result.ok) return;
        }
        window.location.href = 'diagnose.php?plant=' + savedPlantId;
    }

    function showSaved(msg) {
        document.getElementById('result-status-text').textContent = msg;
        document.getElementById('result-status').classList.remove('hidden');
    }
    function showError(msg) {
        const box = document.getElementById('scan-error');
        box.textContent = msg; box.classList.remove('hidden');
    }
    function hideError() { document.getElementById('scan-error').classList.add('hidden'); }

    document.addEventListener('DOMContentLoaded', startCamera);
    window.addEventListener('beforeunload', () => { if (stream) stream.getTracks().forEach(t => t.stop()); });
</script>
</body>
</html>