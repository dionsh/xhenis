<?php
    $currentFile = basename($_SERVER['PHP_SELF'] ?? '');
    $detectedPage = [
        'home.php' => 'home',
        'diagnose.php' => 'diagnose',
        'plants.php' => 'plants',
        'device.php' => 'devices',
    ][$currentFile] ?? '';
    $activePage = $activePage ?? $detectedPage;
    $navItems = [
        'home' => ['href' => 'home.php', 'icon' => 'home', 'label' => 'Home'],
        'diagnose' => ['href' => 'diagnose.php', 'icon' => 'medical_services', 'label' => 'Diagnose'],
        'plants' => ['href' => 'plants.php', 'icon' => 'potted_plant', 'label' => 'Plants'],
        'devices' => ['href' => 'device.php', 'icon' => 'sensors', 'label' => 'Devices'],
    ];
?>
<style>
    .ss-bottom-nav-wrap {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 50;
        display: flex;
        justify-content: center;
        pointer-events: none;
    }

    .ss-bottom-nav {
        width: 100%;
        max-width: 430px;
        height: 74px;
        background: rgba(255, 255, 255, 0.96);
        border-top: 1px solid rgba(207, 213, 207, 0.75);
        display: grid;
        grid-template-columns: 1fr 1fr 86px 1fr 1fr;
        align-items: center;
        padding: 0 12px;
        position: relative;
        box-shadow: 0 -8px 22px rgba(1, 45, 29, 0.07);
        pointer-events: auto;
        font-family: "Manrope", Arial, sans-serif;
    }

    .ss-nav-item {
        width: 58px;
        height: 58px;
        margin: 0 auto;
        color: #10291d;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 3px;
        border-radius: 14px;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: 0.7px;
        text-transform: uppercase;
        transition: background 0.18s ease, color 0.18s ease;
    }

    .ss-nav-item:hover,
    .ss-nav-item.active {
        background: #062f20;
        color: #fff;
    }

    .ss-nav-item .material-symbols-outlined {
        font-size: 23px;
    }

    .ss-nav-camera {
        position: absolute;
        left: 50%;
        top: -31px;
        transform: translateX(-50%);
        width: 68px;
        height: 68px;
        border: 5px solid #fffbfa;
        border-radius: 50%;
        background: #073522;
        color: #fff;
        display: grid;
        place-items: center;
        box-shadow: 0 12px 24px rgba(1, 45, 29, 0.26);
        cursor: pointer;
    }

    .ss-nav-camera .material-symbols-outlined {
        font-size: 30px;
    }

    .ss-nav-spacer {
        width: 86px;
    }

    @media (min-width: 900px) {
        .ss-bottom-nav {
            max-width: 1234px;
        }
    }
</style>
<nav class="ss-bottom-nav-wrap" aria-label="Primary navigation">
    <div class="ss-bottom-nav">
        <a class="ss-nav-item <?php echo $activePage === 'home' ? 'active' : ''; ?>" href="<?php echo $navItems['home']['href']; ?>" <?php echo $activePage === 'home' ? 'aria-current="page"' : ''; ?>>
            <span class="material-symbols-outlined">home</span>
            <span>Home</span>
        </a>
        <a class="ss-nav-item <?php echo $activePage === 'diagnose' ? 'active' : ''; ?>" href="<?php echo $navItems['diagnose']['href']; ?>" <?php echo $activePage === 'diagnose' ? 'aria-current="page"' : ''; ?>>
            <span class="material-symbols-outlined">medical_services</span>
            <span>Diagnose</span>
        </a>
        <span class="ss-nav-spacer" aria-hidden="true"></span>
        <a class="ss-nav-item <?php echo $activePage === 'plants' ? 'active' : ''; ?>" href="<?php echo $navItems['plants']['href']; ?>" <?php echo $activePage === 'plants' ? 'aria-current="page"' : ''; ?>>
            <span class="material-symbols-outlined">potted_plant</span>
            <span>Plants</span>
        </a>
        <a class="ss-nav-item <?php echo $activePage === 'devices' ? 'active' : ''; ?>" href="<?php echo $navItems['devices']['href']; ?>" <?php echo $activePage === 'devices' ? 'aria-current="page"' : ''; ?>>
            <span class="material-symbols-outlined">sensors</span>
            <span>Devices</span>
        </a>
        <button class="ss-nav-camera" type="button" aria-label="Open camera" onclick="window.location.href='camera.php'">
            <span class="material-symbols-outlined">photo_camera</span>
        </button>
    </div>
</nav>