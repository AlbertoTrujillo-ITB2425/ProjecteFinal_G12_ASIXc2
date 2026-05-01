<?php
/**
 * header.php - Sistema SOC G12 CyberPyme
 * No incluir session_start() aquí para evitar errores de headers.
 */

// Variables de sesión para el perfil
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Auditor';
$userEmail = $_SESSION['user_email'] ?? '';

// Generación de Avatar dinámico (Fondo azul SOC, letras blancas)
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true&size=128";
?>

<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">

<style>
    /* Soporte para Modo Claro dinámico */
    body.light-mode { background-color: #f8fafc !important; color: #0f172a !important; }
    body.light-mode .grid-overlay { opacity: 0.04; }
    body.light-mode nav { 
        background: rgba(255, 255, 255, 0.8) !important; 
        border-bottom: 1px solid rgba(0, 0, 0, 0.1); 
    }
    body.light-mode #current-lang-text, 
    body.light-mode nav h1,
    body.light-mode .nav-text-main { color: #0f172a !important; }
    
    body.light-mode .glass-panel {
        background: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid rgba(15, 23, 42, 0.1) !important;
    }
</style>

<nav class="sticky top-0 z-[100] border-b border-slate-800/50 backdrop-blur-xl bg-slate-900/60 transition-all">
    <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
        
        <div class="flex items-center gap-4 cursor-pointer group" onclick="window.location='index.php'">
            <div class="w-11 h-11 bg-gradient-to-br from-sky-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20 border border-white/10 group-hover:scale-105 transition-transform">
                <i class="fas fa-shield-halved text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tighter uppercase text-white nav-text-main">CYBER<span class="text-sky-500">PYME</span></h1>
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[8px] font-bold text-slate-400 tracking-[0.2em]" data-i18n="nav_active">SOC G12 LIVE ENGINE</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            
            <div class="lang-container relative group">
                <button class="bg-white/5 px-4 py-2 rounded-lg border border-white/10 flex items-center gap-3 hover:bg-white/10 transition-all">
                    <span id="current-lang-text" class="text-xs font-black uppercase text-sky-400">ES</span>
                    <i class="fas fa-chevron-down text-[9px] opacity-50"></i>
                </button>
                <div class="lang-dropdown absolute right-0 top-full mt-2 w-32 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all shadow-2xl rounded-xl border border-white/10 bg-slate-900 overflow-hidden z-[101]">
                    <button onclick="changeLanguage('es')" class="w-full text-left px-4 py-3 hover:bg-sky-500/20 transition-colors border-b border-white/5 text-[10px] font-bold text-white uppercase">Español</button>
                    <button onclick="changeLanguage('en')" class="w-full text-left px-4 py-3 hover:bg-sky-500/20 transition-colors border-b border-white/5 text-[10px] font-bold text-white uppercase">English</button>
                    <button onclick="changeLanguage('ca')" class="w-full text-left px-4 py-3 hover:bg-sky-500/20 transition-colors text-[10px] font-bold text-white uppercase">Català</button>
                </div>
            </div>

            <button onclick="toggleTheme()" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center border border-white/10 hover:scale-110 transition-all">
                <i id="theme-icon" class="fas fa-sun text-amber-400"></i>
            </button>

            <?php if ($isLoggedIn): ?>
                <div class="relative group">
                    <button class="flex items-center gap-3 bg-white/5 border border-white/10 p-1.5 pr-4 rounded-xl hover:bg-white/10 transition-all cursor-pointer">
                        <img src="<?php echo $avatarUrl; ?>" class="w-8 h-8 rounded-lg shadow-lg border border-sky-500/30" alt="Avatar">
                        <div class="text-left hidden md:block">
                            <p class="text-[10px] font-black text-white uppercase leading-none"><?php echo htmlspecialchars($userName); ?></p>
                            <span class="text-[8px] text-sky-500 font-bold tracking-widest uppercase">Auditor Actiu</span>
                        </div>
                        <i class="fas fa-chevron-down text-[8px] text-slate-500"></i>
                    </button>
                    
                    <div class="absolute right-0 top-full mt-2 w-48 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all shadow-2xl rounded-xl border border-white/10 bg-slate-900/95 backdrop-blur-xl p-2 z-[101]">
                        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 hover:bg-sky-500/20 rounded-lg transition-colors text-[10px] font-bold text-white uppercase cursor-pointer">
                            <i class="fas fa-user-gear text-sky-500"></i> Configuración
                        </a>
                        <div class="h-px bg-white/5 my-1"></div>
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 hover:bg-red-500/20 rounded-lg transition-colors text-[10px] font-bold text-red-400 uppercase cursor-pointer">
                            <i class="fas fa-power-off"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <button onclick="window.location='auth.php'" class="bg-sky-600 hover:bg-sky-500 text-white font-bold px-5 py-2 rounded-lg transition-all shadow-lg shadow-sky-600/20 flex items-center gap-2 border border-sky-400/30">
                    <i class="fas fa-user-shield text-xs"></i>
                    <span class="text-[10px] tracking-widest uppercase font-black" data-i18n="nav_login">LOGIN AUDITOR</span>
                </button>
            <?php endif; ?>

        </div>
    </div>
</nav>
