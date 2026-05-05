<?php
/**
 * header.php - Sistema SOC G12 CyberPyme
 */
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Auditor';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true&size=128";
?>

<nav class="sticky top-0 z-[100] border-b border-slate-800/50 backdrop-blur-xl bg-slate-900/60 transition-all shadow-2xl">
    <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
        
        <div class="flex items-center gap-4 cursor-pointer group" onclick="window.location='index.php'">
            <div class="w-11 h-11 bg-gradient-to-br from-sky-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20 border border-white/10 group-hover:scale-105 transition-transform">
                <i class="fas fa-shield-halved text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tighter uppercase text-white nav-text-main">CYBER<span class="text-sky-500">PYME</span></h1>
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[8px] font-bold text-slate-400 tracking-[0.2em]">SOC G12 LIVE ENGINE</span>
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
                <i id="theme-icon" class="fas fa-moon text-sky-400"></i>
            </button>

            <?php if ($isLoggedIn): ?>
                <div class="relative group">
                    <div class="flex items-center gap-3 bg-white/5 border border-white/10 p-1.5 pr-4 rounded-xl hover:bg-white/10 transition-all cursor-pointer">
                        <img src="<?php echo $avatarUrl; ?>" class="w-8 h-8 rounded-lg shadow-lg border border-sky-500/30" alt="Avatar">
                        <div class="text-left hidden md:block">
                            <p class="text-[10px] font-black text-white uppercase leading-none"><?php echo htmlspecialchars($userName); ?></p>
                            <span class="text-[8px] text-sky-500 font-bold tracking-widest uppercase">Auditor Actiu</span>
                        </div>
                        <i class="fas fa-chevron-down text-[8px] text-slate-500"></i>
                    </div>
                    
                    <div class="absolute right-0 top-full mt-2 w-52 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-all shadow-2xl rounded-2xl border border-white/10 bg-slate-900/95 backdrop-blur-xl p-2 z-[110]">
                        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 hover:bg-sky-500/20 rounded-xl transition-all text-[10px] font-black text-white uppercase group/item">
                            <i class="fas fa-user-gear text-sky-500 group-hover/item:rotate-90 transition-transform"></i> 
                            Configuración
                        </a>
                        <div class="h-px bg-white/5 my-1 mx-2"></div>
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 hover:bg-red-500/20 rounded-xl transition-all text-[10px] font-black text-red-400 uppercase">
                            <i class="fas fa-power-off"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <button onclick="window.location='auth.php'" class="bg-sky-600 hover:bg-sky-500 text-white font-bold px-5 py-2 rounded-lg transition-all flex items-center gap-2">
                    <span class="text-[10px] tracking-widest uppercase font-black">LOGIN</span>
                </button>
            <?php endif; ?>

        </div>
    </div>
</nav>

<script>
/**
 * Lógica Universal de Tema para el Header
 */
function toggleTheme() {
    // Esta función busca la función definida en el archivo principal (profile.php o index.php)
    // Si no existe, usamos una básica aquí.
    if (typeof window.showTab === 'undefined') {
        const body = document.body;
        const icon = document.getElementById('theme-icon');
        
        if (body.classList.contains('dark')) {
            body.classList.replace('dark', 'light');
            icon.classList.replace('fa-moon', 'fa-sun');
            icon.classList.replace('text-sky-400', 'text-amber-400');
            localStorage.setItem('theme', 'light');
        } else {
            body.classList.replace('light', 'dark');
            icon.classList.replace('fa-sun', 'fa-moon');
            icon.classList.replace('text-amber-400', 'text-sky-400');
            localStorage.setItem('theme', 'dark');
        }
    } else {
        // Si estamos en profile.php, llamamos a su función específica
        window.parentToggleTheme(); 
    }
}
</script>
