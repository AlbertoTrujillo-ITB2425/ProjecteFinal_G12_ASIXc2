<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Auditor';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true&size=128";
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberPyme SOC - Command Center</title>
    
    <meta name="description" content="CyberPyme SOC Intelligence System">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { brand: '#0ea5e9' } } }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="/assets/js/languages.js" defer></script>
    <script src="/assets/js/main.js" defer></script>
</head>

<body class="min-h-screen bg-slate-50 dark:bg-[#020617] text-slate-900 dark:text-white transition-colors duration-300">

<nav class="sticky top-0 z-[100] w-full border-b backdrop-blur-md transition-all duration-300
            bg-white/90 border-slate-200 
            dark:bg-slate-900/80 dark:border-white/10">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-20 flex justify-between items-center">

        <div class="flex items-center gap-3 cursor-pointer group" onclick="window.location='/'">
            <div class="w-10 h-10 rounded-xl bg-brand flex items-center justify-center shadow-lg shadow-blue-500/20 group-hover:scale-105 transition-transform">
                <i class="fas fa-shield-halved text-white text-lg"></i>
            </div>
            <div class="flex flex-col">
                <h1 class="text-lg font-black tracking-tighter uppercase leading-none italic">
                    CYBER<span class="text-brand">PYME</span>
                </h1>
                <span class="text-[8px] font-bold uppercase tracking-[0.3em] text-slate-500 dark:text-slate-400">SOC G12</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="relative group">
                <button class="flex items-center gap-2 px-3 py-2 rounded-lg border text-[10px] font-black uppercase bg-slate-100 dark:bg-white/5 border-slate-200 dark:border-white/10">
                    <span id="current-lang-text" class="text-brand">ES</span>
                    <i class="fas fa-chevron-down text-[8px] opacity-50"></i>
                </button>
                <div class="invisible group-hover:visible opacity-0 group-hover:opacity-100 absolute right-0 top-full mt-2 w-32 p-2 rounded-xl shadow-2xl border bg-white dark:bg-slate-800 border-slate-200 dark:border-white/10 transition-all z-[110]">
                    <button onclick="changeLanguage('es')" class="w-full text-left px-3 py-2 text-[10px] font-bold uppercase rounded-lg hover:bg-brand hover:text-white transition-colors">Castellano</button>
                    <button onclick="changeLanguage('en')" class="w-full text-left px-3 py-2 text-[10px] font-bold uppercase rounded-lg hover:bg-brand hover:text-white transition-colors">English</button>
                    <button onclick="changeLanguage('ca')" class="w-full text-left px-3 py-2 text-[10px] font-bold uppercase rounded-lg hover:bg-brand hover:text-white transition-colors">Català</button>
                </div>
            </div>

            <button onclick="toggleTheme()" class="w-10 h-10 flex items-center justify-center rounded-lg border bg-slate-100 dark:bg-white/5 border-slate-200 dark:border-white/10 text-brand">
                <i id="theme-icon" class="fas fa-moon"></i>
            </button>

            <?php if ($isLoggedIn): ?>
            <div class="relative group">
                <div class="flex items-center gap-3 p-1.5 pr-4 rounded-xl cursor-pointer border bg-slate-100 dark:bg-white/5 border-slate-200 dark:border-white/10">
                    <img src="<?= $avatarUrl ?>" class="w-8 h-8 rounded-lg border border-brand/30">
                    <div class="hidden md:block">
                        <p class="text-[10px] font-black uppercase leading-none"><?= htmlspecialchars($userName) ?></p>
                        <span class="text-[8px] font-bold text-brand uppercase">Auditor</span>
                    </div>
                </div>
                <div class="invisible group-hover:visible opacity-0 group-hover:opacity-100 absolute right-0 top-full mt-2 w-48 p-2 rounded-xl shadow-2xl border bg-white dark:bg-slate-800 border-slate-200 dark:border-white/10 transition-all z-[110]">
                    <a href="/profile.php" class="flex items-center gap-2 px-3 py-2 text-[10px] font-black uppercase rounded-lg hover:bg-brand hover:text-white"><i class="fas fa-user"></i> Perfil</a>
                    <a href="/logout.php" class="flex items-center gap-2 px-3 py-2 text-[10px] font-black uppercase rounded-lg text-red-500 hover:bg-red-500 hover:text-white"><i class="fas fa-power-off"></i> Salir</a>
                </div>
            </div>
            <?php else: ?>
            <a href="/auth.php" class="bg-brand text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="h-6"></div>
