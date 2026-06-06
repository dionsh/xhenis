<?php
	session_start();

	include_once('config.php');

	$plantParam = isset($_GET['plant']) ? $_GET['plant'] : null;
	$isScan = ($plantParam === 'scanned');
	$plantId = is_numeric($plantParam) ? (int)$plantParam : 0;

	$plant = null;
	if ($plantId > 0) {
		try {
			$sql = "SELECT p.plant_id, p.nickname, p.location, p.status,
			               s.common_name, s.scientific_name, s.image_url AS species_image
			        FROM plants p
			        LEFT JOIN plant_species s ON p.species_id = s.species_id
			        WHERE p.plant_id = :pid
			        LIMIT 1";
			$stmt = $conn->prepare($sql);
			$stmt->execute([':pid' => $plantId]);
			$plant = $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			$plant = null;
		}
	}

	$placeholderImg = plant_local_image('default');
	$pImage = $placeholderImg;
	$pTitle = $isScan ? 'New Scan' : 'Plant Diagnosis';
	$pSub = 'Diagnostic Report';

	if ($plant) {
		$pTitle = !empty($plant['nickname']) ? $plant['nickname'] : ($plant['common_name'] ?? 'Plant');
		$pImage = plant_image_src($plant['species_image'] ?? '', $plant['common_name'] ?? $pTitle);
		$pSub = trim(($plant['location'] ?? 'Indoor') . ' - ' . ($plant['common_name'] ?? 'Plant'));
	}

	$healthScore = 64;
	$scoreColor = '#e8a33d';
	$scoreLabel = 'NEEDS ATTENTION';

	$readings = [
		[ 'label' => 'Soil Moisture', 'icon' => 'water_drop', 'value' => 22, 'status' => 'Too Dry', 'bar' => 'bg-red-500', 'text' => 'text-red-600' ],
		[ 'label' => 'Light Level', 'icon' => 'wb_sunny', 'value' => 78, 'status' => 'Optimal', 'bar' => 'bg-[#354c3b]', 'text' => 'text-primary' ],
		[ 'label' => 'Temperature', 'icon' => 'device_thermostat', 'value' => 68, 'status' => 'Stable', 'bar' => 'bg-[#354c3b]', 'text' => 'text-primary' ],
		[ 'label' => 'Humidity', 'icon' => 'humidity_percentage', 'value' => 41, 'status' => 'Slightly Low', 'bar' => 'bg-[#e8a33d]', 'text' => 'text-[#b8761f]' ],
	];

	$issues = [
		[
			'severity' => 'high',
			'icon' => 'water_drop',
			'title' => 'Underwatering',
			'desc' => 'Soil moisture has dropped below the safe threshold for this species. Leaves may begin to wilt or curl.',
			'action' => 'Water thoroughly until moisture reaches 50-60%, then allow the top inch to dry before the next watering.',
		],
		[
			'severity' => 'low',
			'icon' => 'air',
			'title' => 'Low Humidity',
			'desc' => 'Ambient humidity is a little under the preferred range. Not urgent, but worth monitoring.',
			'action' => 'Mist the leaves occasionally or group with other plants to raise local humidity.',
		],
	];

	$timeline = [
		[ 'icon' => 'photo_camera', 'label' => 'Health scan completed', 'time' => 'Just now' ],
		[ 'icon' => 'water_drop', 'label' => 'Last watered', 'time' => '6 days ago' ],
		[ 'icon' => 'compost', 'label' => 'Fertilized', 'time' => '3 weeks ago' ],
		[ 'icon' => 'content_cut', 'label' => 'Pruned', 'time' => '1 month ago' ],
	];

	function e($v) {
		return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
	}
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>SproutSync - Diagnosis</title>
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
	@keyframes scan { 0% { transform: translateY(0); } 50% { transform: translateY(300px); } 100% { transform: translateY(0); } }
	@keyframes ringFill { from { stroke-dashoffset: 339.292; } }
	.ring-progress { animation: ringFill 1.2s ease-out forwards; }
	body { font-family: 'Manrope', sans-serif; }
</style>
</head>
<body class="bg-background text-[#414844] selection:bg-secondary-fixed pb-32">
<header class="sticky top-0 z-40 bg-background/90 backdrop-blur-md">
	<div class="flex justify-between items-center w-full px-6 py-4 max-w-lg mx-auto">
		<div class="flex items-center gap-3">
			<a href="home.php" class="text-primary -ml-1 flex items-center" aria-label="Back to home">
				<span class="material-symbols-outlined">arrow_back</span>
			</a>
			<h1 class="font-bold text-xl text-primary font-heading">Diagnosis</h1>
		</div>
		<button class="text-primary" type="button" aria-label="Share diagnosis">
			<span class="material-symbols-outlined">share</span>
		</button>
	</div>
</header>

<main class="px-5 max-w-lg mx-auto">
	<div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden mt-2 mb-6">
		<div class="relative h-44">
			<img src="<?php echo e($pImage); ?>" class="w-full h-full object-cover" alt="<?php echo e($pTitle); ?>">
			<div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
			<?php if ($isScan): ?>
			<div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-full flex items-center gap-1.5">
				<div class="w-1.5 h-1.5 rounded-full bg-[#2ecc71]"></div>
				<span class="text-[9px] font-bold tracking-widest uppercase text-primary">Fresh Scan</span>
			</div>
			<?php endif; ?>
			<div class="absolute bottom-3 left-4 right-4 text-white">
				<h2 class="text-2xl font-bold font-heading leading-tight"><?php echo e($pTitle); ?></h2>
				<p class="text-[12px] text-white/80 mt-0.5"><?php echo e($pSub); ?></p>
			</div>
		</div>
	</div>

	<div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-6 mb-6 flex items-center gap-6">
		<div class="relative flex-shrink-0">
			<svg class="w-28 h-28 -rotate-90" viewBox="0 0 120 120">
				<circle cx="60" cy="60" r="54" fill="none" stroke="#f0eded" stroke-width="10"></circle>
				<circle class="ring-progress" cx="60" cy="60" r="54" fill="none"
				        stroke="<?php echo e($scoreColor); ?>" stroke-width="10" stroke-linecap="round"
				        stroke-dasharray="339.292"
				        stroke-dashoffset="<?php echo 339.292 - (339.292 * $healthScore / 100); ?>"></circle>
			</svg>
			<div class="absolute inset-0 flex flex-col items-center justify-center">
				<span class="text-3xl font-bold text-primary font-mono"><?php echo (int)$healthScore; ?></span>
				<span class="text-[9px] uppercase tracking-widest text-[#7c7a88]">/ 100</span>
			</div>
		</div>
		<div>
			<div class="text-[10px] font-bold uppercase tracking-widest mb-1" style="color: <?php echo e($scoreColor); ?>;">
				<?php echo e($scoreLabel); ?>
			</div>
			<h3 class="text-lg font-bold text-primary font-heading leading-tight">Overall Health Score</h3>
			<p class="text-[13px] mt-1 leading-relaxed">Based on live sensor readings and the latest leaf scan. A few things need your attention.</p>
		</div>
	</div>

	<div class="mb-6">
		<h3 class="text-[22px] font-bold text-primary font-heading mb-3">Live Readings</h3>
		<div class="grid grid-cols-2 gap-4">
			<?php foreach ($readings as $r): ?>
			<div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-4">
				<div class="flex items-center justify-between mb-3">
					<span class="material-symbols-outlined <?php echo e($r['text']); ?> text-xl"><?php echo e($r['icon']); ?></span>
					<span class="text-[9px] font-bold uppercase tracking-widest <?php echo e($r['text']); ?>"><?php echo e($r['status']); ?></span>
				</div>
				<div class="text-2xl font-bold text-primary font-mono"><?php echo (int)$r['value']; ?><span class="text-sm text-[#7c7a88]">%</span></div>
				<p class="text-[12px] mt-0.5"><?php echo e($r['label']); ?></p>
				<div class="w-full h-1 bg-surface-container rounded-full mt-3">
					<div class="h-full <?php echo e($r['bar']); ?> rounded-full" style="width: <?php echo (int)$r['value']; ?>%"></div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="mb-6">
		<h3 class="text-[22px] font-bold text-primary font-heading mb-3">Detected Issues</h3>
		<div class="space-y-4">
			<?php foreach ($issues as $issue): ?>
				<?php $high = ($issue['severity'] === 'high'); ?>
				<div class="bg-white rounded-2xl shadow-sm border <?php echo $high ? 'border-red-100' : 'border-outline-variant/30'; ?> overflow-hidden">
					<div class="<?php echo $high ? 'bg-red-50/50' : 'bg-[#fdf6ec]'; ?> p-4 flex items-start gap-3">
						<div class="w-9 h-9 rounded-full <?php echo $high ? 'bg-red-100' : 'bg-[#f6e7cf]'; ?> flex items-center justify-center flex-shrink-0">
							<span class="material-symbols-outlined <?php echo $high ? 'text-red-600' : 'text-[#b8761f]'; ?> text-xl"><?php echo e($issue['icon']); ?></span>
						</div>
						<div class="flex-1">
							<div class="flex items-center gap-2">
								<h4 class="text-base font-bold text-primary font-heading"><?php echo e($issue['title']); ?></h4>
								<span class="text-[8px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full <?php echo $high ? 'bg-red-600 text-white' : 'bg-[#e8a33d] text-white'; ?>">
									<?php echo $high ? 'HIGH' : 'LOW'; ?>
								</span>
							</div>
							<p class="text-[13px] mt-1 leading-relaxed"><?php echo e($issue['desc']); ?></p>
						</div>
					</div>
					<div class="p-4 flex gap-3">
						<span class="material-symbols-outlined text-primary text-xl mt-0.5">lightbulb</span>
						<div>
							<div class="text-[10px] font-bold text-primary uppercase tracking-wider mb-1">Recommended Action</div>
							<p class="text-[13px] leading-relaxed"><?php echo e($issue['action']); ?></p>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="bg-[#274e3d] rounded-2xl p-6 text-white shadow-sm mb-6">
		<h3 class="text-[22px] font-heading font-semibold text-white/95 mb-5">Care History</h3>
		<div class="space-y-1">
			<?php foreach ($timeline as $idx => $t): ?>
			<div class="flex items-center gap-4">
				<div class="flex flex-col items-center">
					<div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
						<span class="material-symbols-outlined text-[#cee9d3] text-lg"><?php echo e($t['icon']); ?></span>
					</div>
					<?php if ($idx < count($timeline) - 1): ?>
					<div class="w-px h-6 bg-white/15"></div>
					<?php endif; ?>
				</div>
				<div class="flex-1 flex justify-between items-center <?php echo $idx < count($timeline) - 1 ? 'pb-2' : ''; ?>">
					<span class="text-sm text-white/85"><?php echo e($t['label']); ?></span>
					<span class="text-[12px] text-[#cee9d3]/70 font-mono"><?php echo e($t['time']); ?></span>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="space-y-3 mb-10">
		<button class="block text-center w-full bg-primary text-white py-3.5 rounded-xl text-xs font-bold uppercase tracking-widest shadow-md hover:bg-primary-container transition" type="button">
			Mark As Watered
		</button>
		<button onclick="openCamera()" class="w-full py-3.5 border border-primary text-primary rounded-full text-xs font-bold flex justify-center items-center gap-1.5 hover:bg-primary/5 transition uppercase tracking-widest" type="button">
			<span class="material-symbols-outlined text-lg">photo_camera</span>
			Re-Scan Plant
		</button>
	</div>
</main>

<?php $activePage = 'diagnose'; include('nav.php'); ?>

<div id="plant-chatbot" class="fixed right-4 bottom-24 z-[55] flex flex-col items-end">
	<div id="chat-panel" class="hidden w-[min(92vw,340px)] h-[430px] bg-white border border-outline-variant/60 rounded-2xl shadow-2xl overflow-hidden mb-3">
		<div class="bg-primary text-white px-4 py-3 flex items-center justify-between">
			<div class="flex items-center gap-2">
				<span class="material-symbols-outlined text-[#cee9d3]">psychiatry</span>
				<div>
					<div class="text-sm font-bold leading-tight">Plant Assistant</div>
					<div class="text-[10px] text-white/70 uppercase tracking-widest">SproutSync Care</div>
				</div>
			</div>
			<button type="button" class="w-8 h-8 rounded-full bg-white/10 grid place-items-center" onclick="togglePlantChat()" aria-label="Close plant chat">
				<span class="material-symbols-outlined text-lg">close</span>
			</button>
		</div>
		<div id="chat-messages" class="h-[300px] overflow-y-auto p-4 space-y-3 bg-[#fbfaf8]">
			<div class="max-w-[82%] rounded-2xl rounded-tl-sm bg-[#edf5ee] text-primary px-3 py-2 text-sm leading-snug">
				Ask me about watering, light, humidity, temperature, soil, or what the scan means.
			</div>
		</div>
		<form class="p-3 border-t border-outline-variant/40 flex gap-2" onsubmit="sendPlantQuestion(event)">
			<input id="chat-input" class="flex-1 rounded-full border border-outline-variant/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20" type="text" placeholder="Ask about this plant..." autocomplete="off">
			<button class="w-10 h-10 rounded-full bg-primary text-white grid place-items-center flex-shrink-0" type="submit" aria-label="Send plant question">
				<span class="material-symbols-outlined text-lg">send</span>
			</button>
		</form>
	</div>
	<button id="chat-toggle" class="w-14 h-14 rounded-full bg-primary text-white shadow-xl grid place-items-center border-4 border-background" type="button" onclick="togglePlantChat()" aria-label="Open plant chat">
		<span class="material-symbols-outlined">chat</span>
	</button>
</div>

<div id="camera-modal" class="fixed inset-0 z-[60] bg-black/90 hidden flex-col items-center justify-center backdrop-blur-md">
	<button onclick="closeCamera()" class="absolute top-6 right-6 text-white bg-white/20 p-2 rounded-full hover:bg-white/30 transition z-[70]" type="button" aria-label="Close camera">
		<span class="material-symbols-outlined">close</span>
	</button>
	<div class="w-11/12 max-w-sm aspect-[3/4] bg-zinc-900 border border-white/20 rounded-3xl flex items-center justify-center relative overflow-hidden shadow-2xl">
		<video id="camera-feed" class="w-full h-full object-cover hidden" autoplay playsinline></video>
		<canvas id="camera-canvas" hidden></canvas>
		<div id="camera-placeholder" class="flex flex-col items-center">
			<span class="material-symbols-outlined text-white/30 text-6xl">photo_camera</span>
			<p class="text-white/50 text-xs mt-2">Requesting camera access...</p>
		</div>
		<div class="absolute inset-0 border border-[#2ecc71]/50 rounded-3xl" style="box-shadow: inset 0 0 40px rgba(46, 204, 113, 0.2);"></div>
		<div class="absolute top-0 left-0 w-full h-1 bg-[#2ecc71] opacity-70" style="animation: scan 3s linear infinite;"></div>
	</div>
	<p class="text-white mt-8 text-center px-8 text-sm opacity-80 tracking-wide">Position plant within frame to identify it</p>
	<div class="mt-8 flex items-center gap-4">
		<label class="w-14 h-14 rounded-full border border-white/60 bg-white/10 text-white grid place-items-center cursor-pointer" for="image-upload" aria-label="Upload plant image from phone">
			<span class="material-symbols-outlined">upload_file</span>
		</label>
		<input id="image-upload" type="file" accept="image/*" capture="environment" hidden onchange="uploadPlantImage(this)">
		<button id="capture-btn" class="w-16 h-16 rounded-full border-4 border-white bg-white/10 flex items-center justify-center hover:bg-white/30 transition-colors active:scale-90 shadow-lg shadow-white/20 disabled:opacity-50 disabled:cursor-wait" onclick="takePhoto()" type="button" aria-label="Capture plant photo"></button>
	</div>
	<div id="scan-result" class="hidden w-11/12 max-w-sm mt-4 rounded-xl bg-white/10 text-white text-sm leading-snug text-center px-4 py-3"></div>
</div>

<script>
	let stream = null;
	const diagnosisPlantName = <?php echo json_encode($pTitle); ?>;
	const diagnosisPlantSubtitle = <?php echo json_encode($pSub); ?>;

	function togglePlantChat() {
		const panel = document.getElementById('chat-panel');
		panel.classList.toggle('hidden');

		if (!panel.classList.contains('hidden')) {
			document.getElementById('chat-input').focus();
		}
	}

	function addChatMessage(text, fromUser) {
		const messages = document.getElementById('chat-messages');
		const bubble = document.createElement('div');
		bubble.className = fromUser
			? 'max-w-[82%] ml-auto rounded-2xl rounded-tr-sm bg-primary text-white px-3 py-2 text-sm leading-snug'
			: 'max-w-[82%] rounded-2xl rounded-tl-sm bg-[#edf5ee] text-primary px-3 py-2 text-sm leading-snug';
		bubble.textContent = text;
		messages.appendChild(bubble);
		messages.scrollTop = messages.scrollHeight;
	}

	function plantChatAnswer(question) {
		const q = question.toLowerCase();
		const name = diagnosisPlantName || 'this plant';

		if (q.includes('water') || q.includes('dry') || q.includes('moisture')) {
			return name + ' looks like it needs a careful watering check. Water when the top inch of soil is dry, then let extra water drain fully so roots do not sit wet.';
		}

		if (q.includes('light') || q.includes('sun')) {
			return 'Give ' + name + ' bright, suitable light and rotate it every few days. If leaves scorch, move it away from direct afternoon sun.';
		}

		if (q.includes('humidity') || q.includes('mist')) {
			return 'Humidity is a little low in this report. Grouping plants together or using a pebble tray can help more consistently than heavy misting.';
		}

		if (q.includes('temp') || q.includes('cold') || q.includes('heat')) {
			return 'Keep ' + name + ' away from cold drafts, heaters, and sudden temperature swings. Stable room temperature is usually best.';
		}

		if (q.includes('soil') || q.includes('fertil')) {
			return 'Use well-draining soil and feed lightly during active growth. If the soil stays wet for days, refresh it with a looser mix.';
		}

		if (q.includes('scan') || q.includes('identify') || q.includes('type') || q.includes('what plant')) {
			return 'The camera scan identifies the plant type with Pl@ntNet, then SproutSync tries to match it to your saved species list.';
		}

		if (q.includes('sick') || q.includes('yellow') || q.includes('brown') || q.includes('wilting')) {
			return 'Check three things first: soil moisture, light changes, and leaf texture. Yellow soft leaves often mean too much water; crispy brown edges often mean dryness or low humidity.';
		}

		return 'For ' + name + ', start with the basics: check soil moisture, inspect leaves, confirm light level, and avoid changing too many care habits at once.';
	}

	function sendPlantQuestion(event) {
		event.preventDefault();
		const input = document.getElementById('chat-input');
		const question = input.value.trim();

		if (!question) {
			return;
		}

		addChatMessage(question, true);
		input.value = '';
		window.setTimeout(function() {
			addChatMessage(plantChatAnswer(question), false);
		}, 250);
	}

	async function openCamera() {
		const modal = document.getElementById('camera-modal');
		const result = document.getElementById('scan-result');
		modal.classList.remove('hidden');
		modal.classList.add('flex');
		document.body.style.overflow = 'hidden';
		result.classList.add('hidden');
		result.innerHTML = '';

		try {
			stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
			const video = document.getElementById('camera-feed');
			video.srcObject = stream;
			video.classList.remove('hidden');
			document.getElementById('camera-placeholder').classList.add('hidden');
		} catch (err) {
			document.getElementById('camera-placeholder').innerHTML = '<span class="material-symbols-outlined text-red-400 text-4xl">error</span><p class="text-red-400 text-xs mt-2">Camera access denied</p>';
		}
	}

	function closeCamera() {
		const modal = document.getElementById('camera-modal');
		modal.classList.add('hidden');
		modal.classList.remove('flex');
		document.body.style.overflow = '';

		if (stream) {
			stream.getTracks().forEach(function(track) {
				track.stop();
			});
			stream = null;
		}

		document.getElementById('camera-feed').classList.add('hidden');
		document.getElementById('camera-placeholder').classList.remove('hidden');
		document.getElementById('scan-result').classList.add('hidden');
		document.getElementById('scan-result').innerHTML = '';
	}

	async function takePhoto() {
		const video = document.getElementById('camera-feed');
		const canvas = document.getElementById('camera-canvas');
		const result = document.getElementById('scan-result');
		const captureButton = document.getElementById('capture-btn');
		const videoContainer = document.querySelector('#camera-modal > div');
		const flash = document.createElement('div');
		flash.className = 'absolute inset-0 bg-white z-50 transition-opacity duration-300';
		videoContainer.appendChild(flash);
		setTimeout(function() { flash.style.opacity = '0'; }, 50);
		setTimeout(function() { flash.remove(); }, 350);

		if (!video.videoWidth || !video.videoHeight) {
			result.innerHTML = 'Camera is not ready yet.';
			result.classList.remove('hidden');
			return;
		}

		canvas.width = video.videoWidth;
		canvas.height = video.videoHeight;
		canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

		await submitPlantImage(canvas.toDataURL('image/png'));
	}

	async function submitPlantImage(imageData) {
		const result = document.getElementById('scan-result');
		const captureButton = document.getElementById('capture-btn');
		const formData = new FormData();
		formData.append('image_data', imageData);
		captureButton.disabled = true;
		result.innerHTML = 'Identifying plant...';
		result.classList.remove('hidden');

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
			const matched = data.matched_species_id ? 'Matched in SproutSync' : 'Not in your saved species yet';
			result.innerHTML = '<strong class="block text-base mb-1">' + data.plant + '</strong>' + data.scientific_name + '<br>' + confidence + '% confidence<br>' + matched;
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
</script>
</body>
</html>
