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

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { 
                extend: { 
                    colors: { 
                        brand: '#0ea5e9',
                        brandDark: '#0284c7',
                        darkBg: '#020617'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                } 
            }
        }
    </script>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS & Logic -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Scripts: Languages FIRST, then Main -->
    <script src="/assets/js/languages.js"></script>
    <script src="/assets/js/main.js" defer></script>

    <style>
        /* Animación suave para el dropdown */
        .dropdown-menu {
            transform-origin: top right;
            transition: all 0.2s ease-out;
        }
        .group:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }
        .dropdown-hidden {
            opacity: 0;
            visibility: hidden;
            transform: scale(0.95);
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 dark:bg-[#020617] text-slate-900 dark:text-white transition-colors duration-300 font-sans antialiased selection:bg-brand selection:text-white">

<nav class="sticky top-0 z-[100] w-full border-b backdrop-blur-xl transition-all duration-300
            bg-white/80 border-slate-200/50 shadow-sm
            dark:bg-[#020617]/80 dark:border-white/5 dark:shadow-none">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-20 flex justify-between items-center">

        <!-- LOGO AREA -->
        <div class="flex items-center gap-3 cursor-pointer group" onclick="window.location='/'">
            <div class="relative w-10 h-10 rounded-xl bg-gradient-to-br from-brand to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20 group-hover:shadow-blue-500/40 group-hover:scale-105 transition-all duration-300">
                <i class="fas fa-shield-halved text-white text-lg drop-shadow-md"></i>
                <div class="absolute inset-0 rounded-xl ring-1 ring-inset ring-white/20"></div>
            </div>
            <div class="flex flex-col justify-center">
                <h1 class="text-lg font-black tracking-tighter uppercase leading-none italic text-slate-800 dark:text-white">
                    CYBER<span class="text-brand">PYME</span>
                </h1>
                <span class="text-[9px] font-bold uppercase tracking-[0.25em] text-slate-500 dark:text-slate-400 mt-0.5">SOC G12 ENGINE</span>
            </div>
        </div>

        <!-- RIGHT ACTIONS -->
        <div class="flex items-center gap-3 sm:gap-4">
            
            <!-- LANGUAGE SELECTOR -->
            <div class="relative group">
                <button class="flex items-center gap-2 px-3 py-2 rounded-lg border text-[10px] font-black uppercase transition-all
                               bg-slate-100 dark:bg-white/5 border-slate-200 dark:border-white/10 hover:border-brand/50 hover:text-brand">
                    <i class="fas fa-globe text-xs opacity-70"></i>
                    <span id="current-lang-text" class="text-slate-700 dark:text-slate-200">ES</span>
                    <i class="fas fa-chevron-down text-[8px] opacity-50 ml-1"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div class="dropdown-menu dropdown-hidden absolute right-0 top-full mt-2 w-40 p-1.5 rounded-xl shadow-2xl border z-[110]
                            bg-white dark:bg-slate-800 border-slate-200 dark:border-white/10">
                    <button onclick="changeLanguage('es')" class="w-full text-left px-3 py-2.5 text-[10px] font-bold uppercase rounded-lg hover:bg-brand hover:text-white transition-colors flex justify-between items-center group/item">
                        Castellano <i class="fas fa-check text-[8px] opacity-0 group-hover/item:opacity-100"></i>
                    </button>
                    <button onclick="changeLanguage('en')" class="w-full text-left px-3 py-2.5 text-[10px] font-bold uppercase rounded-lg hover:bg-brand hover:text-white transition-colors flex justify-between items-center group/item">
                        English <i class="fas fa-check text-[8px] opacity-0 group-hover/item:opacity-100"></i>
                    </button>
                    <button onclick="changeLanguage('ca')" class="w-full text-left px-3 py-2.5 text-[10px] font-bold uppercase rounded-lg hover:bg-brand hover:text-white transition-colors flex justify-between items-center group/item">
                        Català <i class="fas fa-check text-[8px] opacity-0 group-hover/item:opacity-100"></i>
                    </button>
                </div>
            </div>

            <!-- THEME TOGGLE -->
            <button onclick="toggleTheme()" class="w-10 h-10 flex items-center justify-center rounded-lg border bg-slate-100 dark:bg-white/5 border-slate-200 dark:border-white/10 text-slate-500 dark:text-yellow-400 hover:text-brand dark:hover:text-brand transition-colors">
                <i id="theme-icon" class="fas fa-moon dark:hidden"></i>
                <i id="theme-icon-dark" class="fas fa-sun hidden dark:block"></i>
            </button>

            <!-- USER PROFILE / LOGIN -->
            <?php if ($isLoggedIn): ?>
            <div class="relative group pl-2">
                <div class="flex items-center gap-3 p-1.5 pr-4 rounded-xl cursor-pointer border bg-white dark:bg-white/5 border-slate-200 dark:border-white/10 hover:border-brand/50 transition-all shadow-sm hover:shadow-md">
                    <img src="<?= $avatarUrl ?>" alt="Avatar" class="w-8 h-8 rounded-lg border border-slate-200 dark:border-white/10 object-cover">
                    <div class="hidden md:block text-left">
                        <p class="text-[10px] font-black uppercase leading-none text-slate-700 dark:text-slate-200"><?= htmlspecialchars($userName) ?></p>
                        <span class="text-[8px] font-bold text-brand uppercase tracking-wide">Auditor Activo</span>
                    </div>
                    <i class="fas fa-chevron-down text-[8px] text-slate-400 ml-1 hidden md:block"></i>
                </div>
                
                <!-- User Dropdown -->
                <div class="dropdown-menu dropdown-hidden absolute right-0 top-full mt-2 w-48 p-1.5 rounded-xl shadow-2xl border z-[110]
                            bg-white dark:bg-slate-800 border-slate-200 dark:border-white/10">
                    <a href="/profile.php" class="flex items-center gap-3 px-3 py-2.5 text-[10px] font-black uppercase rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 transition-colors text-slate-600 dark:text-slate-300">
                        <i class="fas fa-user-circle text-brand"></i> Perfil
                    </a>
                    <div class="h-px bg-slate-200 dark:bg-white/10 my-1"></div>
                    <a href="/logout.php" class="flex items-center gap-3 px-3 py-2.5 text-[10px] font-black uppercase rounded-lg text-red-500 hover:bg-red-500/10 hover:text-red-600 transition-colors">
                        <i class="fas fa-power-off"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
            <?php else: ?>
            <a href="/auth.php" class="bg-brand hover:bg-brandDark text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wide shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 transition-all transform hover:-translate-y-0.5">
                Login Auditor
            </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Spacer for sticky header -->
<div class="h-6"></div>

<script>
    // Lógica de Idioma Integrada en Header
    function changeLanguage(lang) {
        // 1. Llamar a la función global definida en languages.js
        if (typeof setLanguage === 'function') {
            setLanguage(lang);
        }
        
        // 2. Actualizar visualmente el botón del header
        const langText = document.getElementById('current-lang-text');
        if (langText) {
            langText.innerText = lang.toUpperCase();
        }
    }

    // Inicializar idioma al cargar si existe la función
    document.addEventListener('DOMContentLoaded', () => {
        const savedLang = localStorage.getItem('preferred_lang') || 'es';
        if (typeof setLanguage === 'function') {
            setLanguage(savedLang);
        }
        document.getElementById('current-lang-text').innerText = savedLang.toUpperCase();
    });
</script>

</body>
</html>
