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

    <!-- FAVICON PROFESIONAL SVG -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%230ea5e9;stop-opacity:1' /%3E%3Cstop offset='100%25' style='stop-color:%230284c7;stop-opacity:1' /%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath d='M50 5 L85 20 V50 C85 75 50 95 50 95 C50 95 15 75 15 50 V20 Z' fill='url(%23g)' stroke='%23ffffff' stroke-width='2'/%3E%3Cpath d='M50 25 L65 35 V55 C65 65 50 75 50 75 C50 75 35 65 35 55 V35 Z' fill='%23ffffff' opacity='0.9'/%3E%3C/svg%3E">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { brand: '#0ea5e9' }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Scripts: Languages primero, luego Main -->
    <script src="/assets/js/languages.js"></script>
    <script src="/assets/js/main.js" defer></script>
</head>

<body class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white transition-colors duration-300">

<nav class="sticky top-0 z-[100] w-full border-b backdrop-blur-md transition-all duration-300
            bg-white/90 border-slate-200 
            dark:bg-slate-900/90 dark:border-white/10">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-20 flex justify-between items-center">

        <!-- LOGO -->
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

        <div class="flex items-center gap-2 sm:gap-4">

            <!-- TRADUCTOR MANUAL (Limpio y sin estilos raros) -->
            <div class="relative group">
                <button class="flex items-center gap-2 px-3 py-2 rounded-lg border text-[10px] font-black uppercase transition-all
                               bg-slate-100 border-slate-200 text-slate-700
                               dark:bg-white/5 dark:border-white/10 dark:text-slate-300 hover:border-brand">
                    <span id="current-lang-text" class="text-brand">ES</span>
                    <i class="fas fa-chevron-down text-[8px] opacity-50 group-hover:rotate-180 transition-transform"></i>
                </button>

                <div class="invisible group-hover:visible opacity-0 group-hover:opacity-100 absolute right-0 top-full mt-2 w-40 p-2 rounded-xl shadow-2xl border transition-all duration-200 transform scale-95 group-hover:scale-100
                            bg-white border-slate-200 text-slate-800
                            dark:bg-slate-800 dark:border-white/10 dark:text-white z-[110]">
                    <div class="px-3 py-1 mb-1 border-b border-slate-100 dark:border-white/5 text-[8px] font-bold text-slate-400 uppercase">Idioma</div>
                    <!-- CORRECCIÓN: Usar changeLanguage para conectar con main.js -->
                    <button onclick="changeLanguage('es')" class="w-full text-left px-3 py-2 text-[10px] font-bold uppercase rounded-lg hover:bg-brand hover:text-white transition-colors">Español</button>
                    <button onclick="changeLanguage('en')" class="w-full text-left px-3 py-2 text-[10px] font-bold uppercase rounded-lg hover:bg-brand hover:text-white transition-colors">English</button>
                    <button onclick="changeLanguage('ca')" class="w-full text-left px-3 py-2 text-[10px] font-bold uppercase rounded-lg hover:bg-brand hover:text-white transition-colors">Català</button>
                </div>
            </div>

            <!-- THEME TOGGLE (Icono único que cambia con CSS) -->
            <button onclick="toggleTheme()" class="w-10 h-10 flex items-center justify-center rounded-lg border transition-all
                                                 bg-slate-100 border-slate-200 text-brand
                                                 dark:bg-white/5 dark:border-white/10 hover:bg-brand/10">
                <!-- Tailwind se encarga de mostrar uno u otro según la clase 'dark' -->
                <i class="fas fa-moon block dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-yellow-400"></i>
            </button>

            <div class="h-8 w-px bg-slate-200 dark:bg-white/10 mx-1"></div>

            <!-- USER PROFILE -->
            <?php if ($isLoggedIn): ?>
            <div class="relative group">
                <div class="flex items-center gap-3 p-1.5 pr-4 rounded-xl cursor-pointer border transition-all
                            bg-slate-100 border-slate-200
                            dark:bg-white/5 dark:border-white/10 hover:border-brand">
                    <img src="<?= $avatarUrl ?>" class="w-8 h-8 rounded-lg border border-brand/30">
                    <div class="hidden md:block text-left">
                        <p class="text-[10px] font-black uppercase leading-none text-slate-900 dark:text-white"><?= htmlspecialchars($userName) ?></p>
                        <span class="text-[8px] font-bold text-brand uppercase tracking-tighter">Auditor</span>
                    </div>
                    <i class="fas fa-chevron-down text-[8px] text-slate-400 group-hover:rotate-180 transition-transform"></i>
                </div>

                <div class="invisible group-hover:visible opacity-0 group-hover:opacity-100 absolute right-0 top-full mt-2 w-52 p-2 rounded-xl shadow-2xl border transition-all duration-200 transform scale-95 group-hover:scale-100
                            bg-white border-slate-200 text-slate-800
                            dark:bg-slate-800 dark:border-white/10 dark:text-white z-[110]">
                    <div class="px-3 py-1 mb-1 border-b border-slate-100 dark:border-white/5 text-[8px] font-bold text-slate-400 uppercase">Configuración</div>
                    
                    <a href="/profile.php" class="flex items-center gap-2 px-3 py-2.5 text-[10px] font-black uppercase rounded-lg hover:bg-brand hover:text-white transition-colors">
                        <i class="fas fa-user-circle text-sm"></i> Mi Perfil
                    </a>
                    
                    <div class="h-px bg-slate-100 dark:bg-white/10 my-1"></div>
                    
                    <a href="/logout.php" class="flex items-center gap-2 px-3 py-2.5 text-[10px] font-black uppercase rounded-lg transition-colors
                                              text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white">
                        <i class="fas fa-power-off text-sm"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
            <?php else: ?>
            <button onclick="window.location='/auth.php'" class="bg-brand hover:bg-blue-600 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                Login
            </button>
            <?php endif; ?>

        </div>
    </div>
</nav>

<div class="h-6"></div>
</body>
</html>
