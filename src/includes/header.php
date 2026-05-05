<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Auditor';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true&size=128";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberPyme SOC - Command Center</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/favicon.png">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Global -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- JS Global -->
    <script src="assets/js/main.js" defer></script>
</head>

<body class="dark transition-colors duration-300">

<!-- NAVBAR -->
<nav class="sticky top-0 z-50 shadow-xl bg-nav border-b border-glass">
    <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">

        <!-- LOGO -->
        <div class="flex items-center gap-4 cursor-pointer" onclick="window.location='index.php'">
            <div class="w-11 h-11 rounded-lg flex items-center justify-center shadow-lg border border-glass"
                 style="background: var(--accent-primary);">
                <i class="fas fa-shield-halved text-white text-lg"></i>
            </div>

            <div>
                <h1 class="text-xl font-black tracking-tight uppercase">CYBER<span class="text-blue-500">PYME</span></h1>
                <span class="text-[9px] text-muted tracking-widest uppercase">SOC G12 Command Center</span>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="flex items-center gap-4">

            <!-- LANGUAGE -->
            <div class="lang-container">
                <button class="flex items-center gap-2 px-3 py-2 rounded-lg bg-glass border border-glass text-xs font-bold uppercase">
                    <span id="current-lang-text" class="text-blue-400">ES</span>
                    <i class="fas fa-chevron-down text-[8px] opacity-50"></i>
                </button>

                <div class="lang-dropdown">
                    <button onclick="changeLanguage('es')" class="dropdown-item">Español</button>
                    <button onclick="changeLanguage('en')" class="dropdown-item">English</button>
                    <button onclick="changeLanguage('ca')" class="dropdown-item">Català</button>
                </div>
            </div>

            <!-- THEME -->
            <button onclick="toggleTheme()" class="w-10 h-10 flex items-center justify-center bg-glass border border-glass rounded-lg">
                <i id="theme-icon" class="fas fa-moon text-blue-400"></i>
            </button>

            <!-- USER MENU -->
            <?php if ($isLoggedIn): ?>
            <div class="relative user-menu">
                <div class="flex items-center gap-3 bg-glass border border-glass p-1.5 pr-4 rounded-lg cursor-pointer">
                    <img src="<?= $avatarUrl ?>" class="w-8 h-8 rounded-lg border border-blue-500/40">
                    <div class="hidden md:block">
                        <p class="text-[10px] font-black uppercase"><?= htmlspecialchars($userName) ?></p>
                        <span class="text-[8px] text-blue-400 uppercase tracking-widest">Auditor</span>
                    </div>
                    <i class="fas fa-chevron-down text-[8px] text-muted"></i>
                </div>

                <div class="dropdown absolute right-0 mt-2 w-48 p-2">
                    <a href="profile.php" class="dropdown-item flex items-center gap-2">
                        <i class="fas fa-user-gear text-blue-400"></i> Configuración
                    </a>
                    <div class="h-px bg-glass my-1"></div>
                    <a href="logout.php" class="dropdown-item text-red-400 flex items-center gap-2">
                        <i class="fas fa-power-off"></i> Cerrar Sesión
                    </a>
                </div>
            </div>

            <?php else: ?>
            <button onclick="window.location='auth.php'" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase">
                Login
            </button>
            <?php endif; ?>

        </div>
    </div>
</nav>

