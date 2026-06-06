<?php require_once __DIR__ . '/auth.php'; ?>


<?php
    include_once('config.php');

    // Pull all plants from the database, joined with their species for care info.
    // No login check (per your choice). image_url comes from the DB; we fall back
    // to a placeholder in the markup if it's empty.
    $plants = [];
    try {
        $sql = "SELECT p.plant_id, p.nickname, p.location, p.status, NULL AS plant_image,
                       s.common_name, s.scientific_name, s.image_url AS species_image
                FROM plants p
                LEFT JOIN plant_species s ON p.species_id = s.species_id
                ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If the query fails we just show an empty state instead of crashing.
        $plants = [];
    }

    $placeholderImg = plant_local_image('default');

    // --- Static / demo values (per your choice) -------------------------------
    // These are just for the visual demo until you wire in real sensor data.
    // Keyed by a rotating index so each card looks a bit different.
    $demoStates = [
        [ 'category' => 'unhealthy', 'dot' => 'bg-red-600',   'tag_icon' => 'water_drop',       'tag' => 'NEEDS WATER',    'tag_color' => 'text-red-600', 'bar' => 12, 'bar_color' => 'bg-red-600' ],
        [ 'category' => 'healthy',   'dot' => 'bg-[#354c3b]', 'tag_icon' => 'wb_sunny',         'tag' => 'OPTIMAL LIGHT',  'tag_color' => 'text-primary', 'bar' => 85, 'bar_color' => 'bg-[#354c3b]' ],
        [ 'category' => 'healthy',   'dot' => 'bg-[#354c3b]', 'tag_icon' => 'device_thermostat','tag' => 'TEMP STABLE',    'tag_color' => 'text-primary', 'bar' => 90, 'bar_color' => 'bg-[#354c3b]' ],
        [ 'category' => 'crops',     'dot' => 'bg-[#354c3b]', 'tag_icon' => 'psychiatry',       'tag' => 'FLOWERING STAGE','tag_color' => 'text-primary', 'bar' => 70, 'bar_color' => 'bg-[#354c3b]' ],
    ];

    // Helper to safely escape output
    function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>SproutSync - Home</title>
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
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes scan { 0% { transform: translateY(0); } 50% { transform: translateY(300px); } 100% { transform: translateY(0); } }
    body { font-family: 'Manrope', sans-serif; }
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
        
        <div class="flex items-center gap-4">
            <button class="text-primary" aria-label="Notifications">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <a href="logout.php" class="text-red-600 hover:text-red-700 transition flex items-center" aria-label="Logout">
                <span class="material-symbols-outlined">logout</span>
            </a>
        </div>
    </div>
</header>
<main class="px-5 max-w-lg mx-auto">
    <div class="mt-2 mb-6">
        <h2 class="text-3xl text-primary font-bold font-heading tracking-tight">Your Garden</h2>
        <p class="text-sm mt-1 leading-relaxed">Keep track of your botanical journey and plant health trends.</p>
    </div>

    <!-- Filters -->
    <div class="flex gap-2.5 mb-6 overflow-x-auto no-scrollbar pb-1" id="filter-buttons">
        <button data-filter="all" class="filter-btn bg-primary text-white opacity-100 px-4 py-1.5 rounded-full font-bold text-[10px] uppercase tracking-wider whitespace-nowrap shadow-sm transition">All Plants</button>
        <button data-filter="healthy" class="filter-btn bg-secondary-fixed text-primary opacity-80 hover:opacity-100 px-4 py-1.5 rounded-full font-bold text-[10px] uppercase tracking-wider whitespace-nowrap shadow-sm transition">Healthy</button>
        <button data-filter="unhealthy" class="filter-btn bg-secondary-fixed text-primary opacity-80 hover:opacity-100 px-4 py-1.5 rounded-full font-bold text-[10px] uppercase tracking-wider whitespace-nowrap shadow-sm transition">Unhealthy</button>
        <button data-filter="crops" class="filter-btn bg-secondary-fixed text-primary opacity-80 hover:opacity-100 px-4 py-1.5 rounded-full font-bold text-[10px] uppercase tracking-wider whitespace-nowrap shadow-sm transition">Crops</button>
    </div>

<?php if (count($plants) === 0): ?>
    <!-- Empty state -->
    <div class="bg-white rounded-2xl shadow-sm border border-outline-variant/30 p-8 text-center mb-6">
        <span class="material-symbols-outlined text-primary/40 text-5xl">potted_plant</span>
        <h3 class="text-lg font-bold text-primary font-heading mt-3">No plants yet</h3>
        <p class="text-sm mt-1">Add some plants to your database to see them here.</p>
    </div>
<?php else: ?>

    <?php
        // FEATURED CARD = the first plant from the database.
        $featured = $plants[0];
        $fState   = $demoStates[0]; // first demo state (the "needs water" look)
        $fTitle   = !empty($featured['nickname']) ? $featured['nickname'] : $featured['common_name'];
        $fSource  = !empty($featured['plant_image']) ? $featured['plant_image'] : ($featured['species_image'] ?? '');
        
        $fImage   = plant_image_src($fSource, $featured['common_name'] ?? $fTitle);
        
        $fSub     = trim(($featured['location'] ?? 'Indoor') . ' • ' . ($featured['common_name'] ?? 'Plant'));
    ?>

    <!-- Featured plant card -->
    <div class="plant-card bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden mb-6" data-category="<?php echo e($fState['category']); ?>">
        <div class="relative h-56">
            <img src="<?php echo e($fImage); ?>" class="w-full h-full object-cover" alt="<?php echo e($fTitle); ?>">
            <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-full flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-red-600"></div>
                <span class="text-[9px] font-bold tracking-widest uppercase text-primary">CRITICAL ATTENTION</span>
            </div>
        </div>
        <div class="p-5">
            <div class="flex justify-between items-center text-[10px] font-mono uppercase tracking-widest mb-1.5">
                <span>SOIL MOISTURE <?php echo (int)$fState['bar']; ?>%</span>
                <span class="material-symbols-outlined text-primary text-xl filled">star</span>
            </div>
            <h3 class="text-[22px] font-bold text-primary font-heading"><?php echo e($fTitle); ?></h3>
            <p class="text-[13px] mt-0.5"><?php echo e($fSub); ?></p>

            <div class="mt-4 bg-red-50/50 rounded-xl p-3.5 flex gap-3 border border-red-100">
                <span class="material-symbols-outlined text-red-500 mt-0.5">water_drop</span>
                <div>
                    <div class="text-[10px] font-bold text-red-700 uppercase tracking-wider mb-1">NEEDS WATER</div>
                    <div class="text-[13px] leading-tight">Soil moisture at <?php echo (int)$fState['bar']; ?>%. Immediate watering required.</div>
                </div>
            </div>

            <a href="diagnose.php?plant=<?php echo (int)$featured['plant_id']; ?>" class="block text-center w-full mt-5 bg-primary text-white py-3.5 rounded-xl text-xs font-bold uppercase tracking-widest shadow-md hover:bg-primary-container transition">VIEW DIAGNOSIS</a>
        </div>
    </div>

    <!-- Insights Card -->
    <div class="bg-[#274e3d] rounded-2xl p-6 text-white shadow-sm mb-6" id="insights-card">
        <h3 class="text-[22px] font-heading font-semibold text-white/95 mb-5">Garden Insights</h3>
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="text-sm text-white/80">Total Plants</span>
            <span class="text-[22px] text-[#cee9d3] font-mono"><?php echo count($plants); ?></span>
        </div>
        <div class="flex justify-between items-center py-3 border-b border-white/10">
            <span class="text-sm text-white/80">Healthy Rate</span>
            <span class="text-[22px] text-[#cee9d3] font-mono">92%</span>
        </div>
        <div class="flex justify-between items-center py-3">
            <span class="text-sm text-white/80">Pending Tasks</span>
            <span class="text-[22px] text-[#ffb780] font-mono">3</span>
        </div>
        <p class="text-[13px] text-[#cee9d3]/80 italic mt-5 leading-relaxed">"Your urban jungle is thriving! Keep up the great care."</p>
    </div>

    <!-- Past plants list (the rest of the plants after the featured one) -->
    <div class="space-y-4 mb-6">
        <?php
            // Loop over the remaining plants (skip the first one, it's featured above).
            $rest = array_slice($plants, 1);
            foreach ($rest as $i => $plant):
                // rotate through demo states, starting at index 1
                $st     = $demoStates[($i + 1) % count($demoStates)];
                $title  = !empty($plant['nickname']) ? $plant['nickname'] : $plant['common_name'];
                $source = !empty($plant['plant_image']) ? $plant['plant_image'] : ($plant['species_image'] ?? '');
                
                $img    = plant_image_src($source, $plant['common_name'] ?? $title);
                
                $isUnhealthy = ($st['category'] === 'unhealthy');
        ?>
        <div class="plant-card bg-white rounded-2xl shadow-sm border border-outline-variant/30 overflow-hidden" data-category="<?php echo e($st['category']); ?>">
            <div class="relative h-28">
                <img src="<?php echo e($img); ?>" class="w-full h-full object-cover object-center" alt="<?php echo e($title); ?>">
                <div class="absolute bottom-2.5 left-2.5 bg-white/95 backdrop-blur-sm px-2 py-0.5 rounded text-[10px] font-bold font-mono tracking-widest"><?php echo e(strtoupper(substr($plant['common_name'] ?? 'PLANT', 0, 10))); ?></div>
            </div>
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-lg font-bold text-primary font-heading"><?php echo e($title); ?></h4>
                    <div class="w-2.5 h-2.5 rounded-full <?php echo e($st['dot']); ?>"></div>
                </div>

                <?php if ($isUnhealthy): ?>
                <p class="text-sm text-red-600 font-medium mt-3 border-l-2 border-red-500 pl-3">Plant looks sick. Soil moisture is low and needs attention.</p>
                <?php endif; ?>

                <div class="flex items-center gap-1.5 mt-<?php echo $isUnhealthy ? '4' : '1'; ?> text-[9px] <?php echo e($st['tag_color']); ?> uppercase font-bold tracking-widest">
                    <span class="material-symbols-outlined text-[14px]"><?php echo e($st['tag_icon']); ?></span>
                    <?php echo e($st['tag']); ?>
                </div>
                <div class="w-full h-1 bg-surface-container rounded-full mt-4 <?php echo $isUnhealthy ? 'mb-4' : ''; ?>">
                    <div class="h-full <?php echo e($st['bar_color']); ?> rounded-full" style="width: <?php echo (int)$st['bar']; ?>%"></div>
                </div>

                <?php if ($isUnhealthy): ?>
                <a href="diagnose.php?plant=<?php echo (int)$plant['plant_id']; ?>" class="block text-center w-full mt-4 bg-primary text-white py-3 rounded-xl text-xs font-bold uppercase tracking-widest shadow-md hover:bg-primary-container transition">VIEW DIAGNOSIS</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button class="w-full py-3.5 border border-primary text-primary rounded-full text-xs font-bold flex justify-center items-center gap-1 hover:bg-primary/5 transition uppercase tracking-widest mb-10">
        LOAD MORE
        <span class="material-symbols-outlined text-lg">expand_more</span>
    </button>

<?php endif; ?>
</main>

<?php $activePage = 'home'; include('nav.php'); ?>

<!-- Camera Modal -->
<div id="camera-modal" class="fixed inset-0 z-[60] bg-black/90 hidden flex-col items-center justify-center backdrop-blur-md">
    <button onclick="closeCamera()" class="absolute top-6 right-6 text-white bg-white/20 p-2 rounded-full hover:bg-white/30 transition z-[70]">
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
        <button id="capture-btn" class="w-16 h-16 rounded-full border-4 border-white bg-white/10 flex items-center justify-center hover:bg-white/30 transition-colors active:scale-90 shadow-lg shadow-white/20 disabled:opacity-50 disabled:cursor-wait" onclick="takePhoto()" aria-label="Capture plant photo"></button>
    </div>
    <div id="scan-result" class="hidden w-11/12 max-w-sm mt-4 rounded-xl bg-white/10 text-white text-sm leading-snug text-center px-4 py-3"></div>
</div>

<script>
    // Camera Logic
    let stream = null;
    async function openCamera() {
        const modal = document.getElementById('camera-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        document.getElementById('scan-result').classList.add('hidden');
        document.getElementById('scan-result').innerHTML = '';
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
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
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
        setTimeout(() => flash.style.opacity = '0', 50);
        setTimeout(() => flash.remove(), 350);

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

    // Category Filtering Logic
    document.addEventListener('DOMContentLoaded', () => {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const plantCards = document.querySelectorAll('.plant-card');
        const insightsCard = document.getElementById('insights-card');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => {
                    b.classList.remove('bg-primary', 'text-white', 'opacity-100');
                    b.classList.add('bg-secondary-fixed', 'text-primary', 'opacity-80');
                });
                btn.classList.remove('bg-secondary-fixed', 'text-primary', 'opacity-80');
                btn.classList.add('bg-primary', 'text-white', 'opacity-100');

                const filter = btn.dataset.filter;
                plantCards.forEach(card => {
                    card.style.display = (filter === 'all' || card.dataset.category === filter) ? 'block' : 'none';
                });
                if (insightsCard) insightsCard.style.display = (filter === 'all') ? 'block' : 'none';
            });
        });
    });
</script>

</body>
</html>
