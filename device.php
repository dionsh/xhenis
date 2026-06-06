<?php
	$activePage = 'devices';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Devices - SproutSync</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
	<style>
		:root {
			--bg: #fffbfa;
			--primary: #062f20;
			--muted: #59625b;
			--line: #d8ddd8;
			--card: #fffefd;
			--soft: #f1f4ef;
			--danger: #be3b32;
			--success: #34a853;
		}

		* { box-sizing: border-box; }

		body {
			margin: 0;
			min-height: 100vh;
			background: #ecebea;
			color: #0a2117;
			font-family: "Manrope", Arial, sans-serif;
		}

		button,
		a {
			font: inherit;
		}

		.material-symbols-outlined {
			font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24;
			line-height: 1;
		}

		.app-shell {
			width: min(100%, 1234px);
			min-height: 100vh;
			margin: 0 auto;
			background: var(--bg);
			padding: 22px 20px 112px;
		}

		.topbar {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 34px;
		}

		.brand {
			display: flex;
			align-items: center;
			gap: 14px;
			color: var(--primary);
			font-size: 25px;
			font-weight: 800;
		}

		.brand-mark {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			background: #1d5a3e;
			color: #d7ead8;
			display: grid;
			place-items: center;
		}

		.notify {
			border: 0;
			background: transparent;
			color: var(--primary);
			display: grid;
			place-items: center;
			cursor: pointer;
		}

		.notify .material-symbols-outlined {
			font-size: 30px;
		}

		.device-grid {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 46px;
			align-items: start;
		}

		.page-title {
			margin: 0;
			font-size: 33px;
			line-height: 1.08;
			font-weight: 800;
			letter-spacing: -0.2px;
		}

		.page-copy {
			margin: 6px 0 28px;
			color: #1f2f28;
			font-size: 18px;
			line-height: 1.35;
		}

		.card {
			background: var(--card);
			border: 1px solid var(--line);
			border-radius: 17px;
			box-shadow: 0 2px 8px rgba(1, 45, 29, 0.08);
			padding: 22px;
		}

		.card + .card {
			margin-top: 24px;
		}

		.card h2 {
			margin: 0 0 12px;
			color: #071f16;
			font-size: 25px;
			line-height: 1.1;
			font-weight: 800;
		}

		.divider {
			border-top: 1px solid var(--line);
			margin: 12px 0;
		}

		.device-summary {
			background: linear-gradient(135deg, #f7f8f5, #e7ebe4);
			border: 1px solid var(--line);
			border-radius: 13px;
			padding: 12px;
		}

		.device-head {
			display: grid;
			grid-template-columns: 64px 1fr auto;
			gap: 12px;
			align-items: center;
			margin-bottom: 12px;
		}

		.device-head.compact {
			grid-template-columns: 74px 1fr;
			background: linear-gradient(135deg, #f4f6f3, #e7ebe3);
			border-radius: 10px;
			padding: 10px;
		}

		.board-img {
			width: 64px;
			height: 64px;
			border-radius: 8px;
			object-fit: cover;
			background: #e7ebe4;
		}

		.device-head.compact .board-img {
			width: 74px;
			height: 74px;
		}

		.device-title {
			display: block;
			font-size: 19px;
			line-height: 1.05;
			font-weight: 800;
		}

		.device-state {
			display: block;
			margin-top: 2px;
			font-size: 16px;
			line-height: 1.1;
		}

		.online-dot {
			width: 14px;
			height: 14px;
			border-radius: 50%;
			background: var(--success);
		}

		.status-grid {
			display: grid;
			gap: 16px;
		}

		.status-row,
		.metric-row {
			display: grid;
			grid-template-columns: 36px 1fr auto;
			gap: 12px;
			align-items: center;
			padding: 10px 0;
			border-top: 1px solid var(--line);
		}

		.status-row:first-child,
		.metric-row:first-child {
			border-top: 0;
		}

		.metric-row .material-symbols-outlined,
		.status-row .material-symbols-outlined {
			color: var(--primary);
			font-size: 31px;
		}

		.row-title {
			display: block;
			font-size: 17px;
			font-weight: 800;
			line-height: 1.05;
		}

		.row-sub {
			display: block;
			margin-top: 3px;
			color: #1d2d25;
			font-size: 15px;
			line-height: 1.15;
		}

		.active-text {
			color: var(--success);
			font-weight: 700;
		}

		.big-value {
			font-size: 20px;
			font-weight: 700;
		}

		.calibrate {
			border: 1px solid #14271f;
			border-radius: 9px;
			background: #fff;
			color: #10271d;
			padding: 5px 8px;
			font-size: 16px;
			cursor: pointer;
		}

		.alert {
			margin-top: 14px;
			border-radius: 10px;
			background: #fff0ef;
			color: #321a16;
			padding: 14px;
			display: grid;
			grid-template-columns: 36px 1fr auto;
			gap: 12px;
			align-items: start;
		}

		.alert .material-symbols-outlined {
			color: var(--danger);
			font-size: 30px;
		}

		.alert strong {
			display: block;
			font-size: 16px;
			line-height: 1.1;
		}

		.alert span {
			font-size: 15px;
			line-height: 1.15;
		}

		.close-alert {
			border: 0;
			background: transparent;
			color: #211b18;
			cursor: pointer;
			padding: 0;
		}

		.summary-mini {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 18px;
			margin-top: 14px;
		}

		.summary-item {
			display: grid;
			grid-template-columns: 34px 1fr;
			gap: 8px;
			align-items: start;
		}

		.summary-item .material-symbols-outlined {
			color: var(--primary);
			font-size: 30px;
		}

		.toggle {
			width: 62px;
			height: 34px;
			border: 0;
			border-radius: 999px;
			background: #a8aaa9;
			padding: 4px;
			display: flex;
			justify-content: flex-start;
			cursor: pointer;
			transition: background 0.2s ease;
		}

		.toggle::before {
			content: "";
			width: 26px;
			height: 26px;
			border-radius: 50%;
			background: #fff;
			box-shadow: 0 1px 4px rgba(0,0,0,0.22);
			transition: transform 0.2s ease;
		}

		.toggle.on {
			background: var(--primary);
		}

		.toggle.on::before {
			transform: translateX(28px);
		}

		.green-icon {
			color: var(--success);
		}

		.device-toast {
			position: fixed;
			left: 50%;
			bottom: 94px;
			z-index: 70;
			transform: translate(-50%, 18px);
			min-width: 260px;
			max-width: calc(100vw - 32px);
			background: #062f20;
			color: #fff;
			border-radius: 12px;
			padding: 12px 14px;
			box-shadow: 0 12px 34px rgba(1, 45, 29, 0.26);
			font-size: 13px;
			line-height: 1.35;
			opacity: 0;
			pointer-events: none;
			transition: opacity 0.2s ease, transform 0.2s ease;
		}

		.device-toast.show {
			opacity: 1;
			transform: translate(-50%, 0);
		}

		.settings-modal {
			position: fixed;
			inset: 0;
			z-index: 65;
			background: rgba(1, 45, 29, 0.42);
			backdrop-filter: blur(7px);
			display: none;
			align-items: center;
			justify-content: center;
			padding: 20px;
		}

		.settings-modal.open {
			display: flex;
		}

		.settings-panel {
			width: min(100%, 380px);
			background: var(--bg);
			border: 1px solid var(--line);
			border-radius: 17px;
			box-shadow: 0 28px 70px rgba(1, 45, 29, 0.28);
			padding: 20px;
		}

		.settings-head {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 14px;
			margin-bottom: 16px;
		}

		.settings-head h2 {
			margin: 0;
			color: var(--primary);
			font-size: 22px;
			line-height: 1.1;
		}

		.settings-head p {
			margin: 5px 0 0;
			color: var(--muted);
			font-size: 12px;
			line-height: 1.35;
		}

		.close-settings {
			width: 36px;
			height: 36px;
			border: 0;
			border-radius: 50%;
			background: #eef3ee;
			color: var(--primary);
			display: grid;
			place-items: center;
			cursor: pointer;
			flex: 0 0 auto;
		}

		.settings-fields {
			display: grid;
			gap: 12px;
		}

		.settings-fields label {
			display: grid;
			gap: 6px;
			color: #12271d;
			font-size: 12px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.settings-fields input,
		.settings-fields select {
			width: 100%;
			border: 1px solid var(--line);
			border-radius: 10px;
			background: #fff;
			color: #10271d;
			padding: 11px 12px;
			font-size: 14px;
			font-weight: 600;
		}

		.settings-save {
			width: 100%;
			margin-top: 16px;
			border: 0;
			border-radius: 11px;
			background: var(--primary);
			color: #fff;
			padding: 13px;
			font-size: 12px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: 0.7px;
			cursor: pointer;
		}

		@keyframes cameraScan {
			0% { transform: translateY(0); }
			50% { transform: translateY(335px); }
			100% { transform: translateY(0); }
		}

		.camera-modal {
			position: fixed;
			inset: 0;
			z-index: 60;
			background: rgba(0, 0, 0, 0.9);
			display: none;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			padding: 24px;
		}

		.camera-modal.open {
			display: flex;
		}

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

		#camera-feed.active {
			display: block;
		}

		.camera-placeholder,
		.camera-hint {
			color: rgba(255, 255, 255, 0.72);
			text-align: center;
			font-size: 13px;
		}

		.camera-placeholder .material-symbols-outlined {
			display: block;
			font-size: 54px;
			margin-bottom: 10px;
			color: rgba(255,255,255,0.34);
		}

		.camera-hint {
			margin-top: 24px;
		}

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
			background: rgba(255, 255, 255, 0.12);
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

		@media (max-width: 920px) {
			.app-shell {
				max-width: 430px;
			}

			.device-grid {
				grid-template-columns: 1fr;
				gap: 24px;
			}

			.page-title {
				font-size: 31px;
			}
		}
	</style>
</head>
<body>
	<main class="app-shell">
		<header class="topbar">
			<div class="brand">
				<span class="brand-mark"><span class="material-symbols-outlined">eco</span></span>
				<span>SproutSync</span>
			</div>
			<button class="notify" type="button" aria-label="Notifications" onclick="showToast('No new device notifications.')">
				<span class="material-symbols-outlined">notifications</span>
			</button>
		</header>

		<section class="device-grid" aria-label="Cube-Monitor settings">
			<div>
				<h1 class="page-title">Cube-Monitor Settings</h1>
				<p class="page-copy">Configure and monitor your SproutSync device connection and sensors.</p>

				<article class="card">
					<h2>Connected Devices</h2>
					<div class="device-summary">
						<div class="device-head">
							<img class="board-img" src="assets/devices/cube-monitor.jpg" alt="Cube-Monitor v2.0">
							<span>
								<span class="device-title">Cube-Monitor v2.0</span>
								<span class="device-state">(Active)</span>
							</span>
							<span class="online-dot" aria-label="Active"></span>
						</div>

						<div class="status-row">
							<span class="material-symbols-outlined">wifi</span>
							<span>
								<span class="row-title">WiFi <span class="active-text">active</span></span>
								<span class="row-sub">SSID: MyHomeNet<br>Signal: Strong<br>IP: 192.168.1.50</span>
							</span>
						</div>
						<div class="status-row">
							<span class="material-symbols-outlined">cell_tower</span>
							<span>
								<span class="row-title">Cellular <span class="active-text">active</span></span>
								<span class="row-sub">4G<br>Signal: Good<br>Carrier: GlobalData</span>
							</span>
						</div>
					</div>

					<div class="alert" id="device-alert">
						<span class="material-symbols-outlined">warning</span>
						<span>
							<strong>Active Alerts</strong>
							Calibration button: sa slat an improves a model.
						</span>
						<button class="close-alert" type="button" aria-label="Dismiss alert" onclick="dismissAlert()">
							<span class="material-symbols-outlined">close</span>
						</button>
					</div>
				</article>
			</div>

			<div>
				<article class="card">
					<h2>Connection Summary</h2>
					<div class="device-head compact">
						<img class="board-img" src="assets/devices/cube-monitor.jpg" alt="Cube-Monitor v2.0">
						<span>
							<span class="device-title">Cube-Monitor v2.0</span>
							<span class="device-state">(Active)</span>
						</span>
					</div>
					<div class="summary-mini">
						<div class="summary-item">
							<span class="material-symbols-outlined">wifi</span>
							<span>
								<span class="row-title">WiFi <span class="active-text">active</span></span>
								<span class="row-sub">4G &nbsp; Strong</span>
							</span>
						</div>
						<div class="summary-item">
							<span class="material-symbols-outlined">signal_cellular_alt</span>
							<span>
								<span class="row-title">Signal</span>
								<span class="row-sub">IP: 192.168.1.50</span>
							</span>
						</div>
					</div>
				</article>

				<article class="card">
					<h2>Sensor Status & Calibration</h2>
					<div class="divider"></div>
					<div class="metric-row">
						<span class="material-symbols-outlined">psychiatry</span>
						<span class="row-title">Soil Moisture</span>
						<span class="big-value">72% <button class="calibrate" type="button" data-sensor="Soil Moisture" onclick="calibrateSensor(this)">Calibrate</button></span>
					</div>
					<div class="metric-row">
						<span class="material-symbols-outlined">eco</span>
						<span class="row-title">Soil pH</span>
						<span class="big-value">6.8 <button class="calibrate" type="button" data-sensor="Soil pH" onclick="calibrateSensor(this)">Calibrate</button></span>
					</div>
					<div class="metric-row">
						<span class="material-symbols-outlined">light_mode</span>
						<span class="row-title">Light Intensity</span>
						<span class="big-value">3200 lux</span>
					</div>
					<div class="metric-row">
						<span class="material-symbols-outlined">device_thermostat</span>
						<span class="row-title">Air Temp & Humidity</span>
						<span class="big-value">24C / 55%</span>
					</div>
					<div class="metric-row">
						<span class="material-symbols-outlined">device_thermostat</span>
						<span class="row-title">Soil Temp</span>
						<span class="big-value">19C <button class="calibrate" type="button" data-sensor="Soil Temp" onclick="calibrateSensor(this)">Calibrate</button></span>
					</div>
				</article>
			</div>

			<div>
				<article class="card">
					<h2>Power & Solar</h2>
					<div class="divider"></div>
					<div class="metric-row">
						<span class="material-symbols-outlined">battery_full</span>
						<span>
							<span class="row-title">Battery level</span>
							<span class="row-sub">Estimated 15 days</span>
						</span>
						<span class="big-value">92%</span>
					</div>
					<div class="metric-row">
						<span class="material-symbols-outlined">solar_power</span>
						<span class="row-title">Solar Input</span>
						<span class="big-value"><span class="material-symbols-outlined green-icon">bolt</span> 1.2W</span>
					</div>
					<div class="metric-row">
						<span class="material-symbols-outlined">power_settings_new</span>
						<span>
							<span class="row-title">Low Power Mode</span>
							<span class="row-sub">Currently switch (OFF)</span>
						</span>
						<button class="toggle" id="low-power-toggle" type="button" aria-label="Low power mode off" aria-pressed="false" onclick="toggleLowPower()"></button>
					</div>
				</article>

				<article class="card">
					<h2>Network & Hotspot</h2>
					<div class="divider"></div>
					<div class="metric-row">
						<span class="material-symbols-outlined">settings_input_antenna</span>
						<span>
							<span class="row-title">Hotspot Settings</span>
							<span class="row-sub">SSID: Cube-Hotspot<br>Password: ******</span>
						</span>
						<button class="calibrate" type="button" onclick="openSettings('hotspot')">Configure</button>
					</div>
					<div class="metric-row">
						<span class="material-symbols-outlined">wifi</span>
						<span>
							<span class="row-title">WiFi/Cellular Priority</span>
							<span class="row-sub">WiFi primary<br>Cellular backup</span>
						</span>
						<button class="calibrate" type="button" onclick="openSettings('priority')">Change</button>
					</div>
				</article>
			</div>
		</section>
	</main>

	<?php include('nav.php'); ?>

	<div class="device-toast" id="device-toast" role="status" aria-live="polite"></div>

	<div class="settings-modal" id="settings-modal" aria-hidden="true">
		<div class="settings-panel" role="dialog" aria-modal="true" aria-labelledby="settings-title">
			<div class="settings-head">
				<div>
					<h2 id="settings-title">Device Settings</h2>
					<p id="settings-copy">Update the Cube-Monitor connection setting.</p>
				</div>
				<button class="close-settings" type="button" aria-label="Close settings" onclick="closeSettings()">
					<span class="material-symbols-outlined">close</span>
				</button>
			</div>

			<form id="hotspot-form" class="settings-fields" onsubmit="saveHotspot(event)">
				<label>
					Hotspot SSID
					<input id="hotspot-ssid" type="text" value="Cube-Hotspot">
				</label>
				<label>
					Password
					<input id="hotspot-password" type="password" value="sproutsync">
				</label>
				<button class="settings-save" type="submit">Save Hotspot</button>
			</form>

			<form id="priority-form" class="settings-fields" onsubmit="savePriority(event)">
				<label>
					Primary Network
					<select id="network-priority">
						<option value="WiFi primary">WiFi primary</option>
						<option value="Cellular primary">Cellular primary</option>
						<option value="Auto failover">Auto failover</option>
					</select>
				</label>
				<label>
					Backup Network
					<select id="network-backup">
						<option value="Cellular backup">Cellular backup</option>
						<option value="WiFi backup">WiFi backup</option>
						<option value="No backup">No backup</option>
					</select>
				</label>
				<button class="settings-save" type="submit">Save Priority</button>
			</form>
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
		<p class="camera-hint">Position plant within frame to identify it</p>
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
		let toastTimeout = null;

		function showToast(message) {
			const toast = document.getElementById('device-toast');
			toast.textContent = message;
			toast.classList.add('show');

			window.clearTimeout(toastTimeout);
			toastTimeout = window.setTimeout(function() {
				toast.classList.remove('show');
			}, 2200);
		}

		function dismissAlert() {
			document.getElementById('device-alert').style.display = 'none';
			showToast('Alert dismissed.');
		}

		function calibrateSensor(button) {
			const sensor = button.dataset.sensor || 'Sensor';
			const originalText = button.textContent;
			button.disabled = true;
			button.textContent = 'Calibrating';
			showToast(sensor + ' calibration started.');

			window.setTimeout(function() {
				button.textContent = 'Done';
				showToast(sensor + ' calibrated successfully.');

				window.setTimeout(function() {
					button.disabled = false;
					button.textContent = originalText;
				}, 1400);
			}, 900);
		}

		function toggleLowPower() {
			const toggle = document.getElementById('low-power-toggle');
			const rowSub = toggle.closest('.metric-row').querySelector('.row-sub');
			const isOn = !toggle.classList.contains('on');
			toggle.classList.toggle('on', isOn);
			toggle.setAttribute('aria-pressed', String(isOn));
			toggle.setAttribute('aria-label', isOn ? 'Low power mode on' : 'Low power mode off');
			rowSub.textContent = isOn ? 'Currently switch (ON)' : 'Currently switch (OFF)';
			showToast(isOn ? 'Low Power Mode enabled.' : 'Low Power Mode disabled.');
		}

		function openSettings(mode) {
			const modal = document.getElementById('settings-modal');
			const hotspotForm = document.getElementById('hotspot-form');
			const priorityForm = document.getElementById('priority-form');
			const title = document.getElementById('settings-title');
			const copy = document.getElementById('settings-copy');
			const isHotspot = mode === 'hotspot';

			hotspotForm.style.display = isHotspot ? 'grid' : 'none';
			priorityForm.style.display = isHotspot ? 'none' : 'grid';
			title.textContent = isHotspot ? 'Hotspot Settings' : 'Network Priority';
			copy.textContent = isHotspot ? 'Configure the Cube-Monitor local hotspot.' : 'Choose which network the device should prefer.';
			modal.classList.add('open');
			modal.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
		}

		function closeSettings() {
			const modal = document.getElementById('settings-modal');
			modal.classList.remove('open');
			modal.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
		}

		function saveHotspot(event) {
			event.preventDefault();
			const ssid = document.getElementById('hotspot-ssid').value.trim() || 'Cube-Hotspot';
			const hotspotRows = Array.from(document.querySelectorAll('.metric-row')).filter(function(row) {
				return row.textContent.includes('Hotspot Settings');
			});

			hotspotRows.forEach(function(row) {
				const sub = row.querySelector('.row-sub');
				sub.innerHTML = 'SSID: ' + escapeHtml(ssid) + '<br>Password: ******';
			});

			closeSettings();
			showToast('Hotspot settings saved.');
		}

		function savePriority(event) {
			event.preventDefault();
			const primary = document.getElementById('network-priority').value;
			const backup = document.getElementById('network-backup').value;
			const priorityRows = Array.from(document.querySelectorAll('.metric-row')).filter(function(row) {
				return row.textContent.includes('WiFi/Cellular Priority');
			});

			priorityRows.forEach(function(row) {
				const sub = row.querySelector('.row-sub');
				sub.innerHTML = escapeHtml(primary) + '<br>' + escapeHtml(backup);
			});

			closeSettings();
			showToast('Network priority updated.');
		}

		function escapeHtml(value) {
			const div = document.createElement('div');
			div.textContent = value;
			return div.innerHTML;
		}

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

			await submitPlantImage(canvas.toDataURL('image/png'));
		}

		async function submitPlantImage(imageData) {
			const result = document.getElementById('scan-result');
			const captureButton = document.getElementById('capture-btn');
			const formData = new FormData();
			formData.append('image_data', imageData);
			captureButton.disabled = true;
			result.innerHTML = 'Identifying plant...';
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
				const matched = data.matched_species_id ? 'Matched in SproutSync' : 'Not in your saved species yet';
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
	</script>
</body>
</html>
