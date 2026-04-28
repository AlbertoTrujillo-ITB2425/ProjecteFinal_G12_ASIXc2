<?php
/**
 * CYBERPYME SOC v5.1.0
 * Refactorización: Rutas de activos corregidas y seguridad mejorada.
 */
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: upgrade-insecure-requests"); // Opcional: fuerza HTTPS
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberPYME | Advanced SOC</title>

    <link rel="stylesheet" href="assets/css/style.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js" defer></script>
</head>
<body class="dark-mode min-h-screen flex flex-col bg-slate-950 text-slate-200">

    <div class="grid-overlay fixed inset-0 pointer-events-none"></div>
    <div class="bg-glow fixed inset-0 pointer-events-none"></div>
    <div id="google_translate_element" class="hidden"></div>

    <nav class="sticky top-0 z-[100] border-b border-slate-800/50 backdrop-blur-xl bg-slate-900/60">
        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-4 cursor-pointer group" onclick="window.location.reload()">
                <div class="w-12 h-12 bg-gradient-to-br from-sky-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/20 border border-white/10 group-hover:scale-105 transition-transform">
                    <i class="fas fa-shield-halved text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tighter uppercase">CYBER<span class="text-sky-500">PYME</span></h1>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[9px] font-bold text-slate-400 tracking-[0.2em]">G12 SOC ACTIVE</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="lang-container hidden sm:block relative">
                    <button class="bg-white/5 p-2.5 rounded-lg border border-white/10 flex items-center gap-3 hover:bg-white/10 transition-all">
                        <span id="current-lang-flag">🇪🇸</span>
                        <span id="current-lang-text" class="text-xs font-bold uppercase">ES</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-50"></i>
                    </button>
                    <div class="lang-dropdown shadow-2xl rounded-xl border border-white/10 bg-slate-900">
                        <button onclick="changeLanguage('es', '🇪🇸', 'ES')" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-sky-500/20 transition-colors border-b border-white/5"><span>🇪🇸</span> <span class="text-sm">Español</span></button>
                        <button onclick="changeLanguage('en', '🇺🇸', 'EN')" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-sky-500/20 transition-colors border-b border-white/5"><span>🇺🇸</span> <span class="text-sm">English</span></button>
                    </div>
                </div>

                <button onclick="toggleTheme()" class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center border border-white/5 hover:bg-white/10 transition-all">
                    <i id="theme-icon" class="fas fa-sun text-amber-400"></i>
                </button>

                <button id="wallet-btn" onclick="web3Login()" class="bg-sky-600 hover:bg-sky-500 text-white font-bold px-6 py-2.5 rounded-lg transition-all shadow-lg shadow-sky-600/20 flex items-center gap-2 border border-sky-400/30">
                    <i class="fas fa-fingerprint"></i>
                    <span id="wallet-text" class="text-xs tracking-wide uppercase">Login Auditor</span>
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-12 flex-grow flex flex-col justify-center">
        <header class="mb-12">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/20 text-sky-400 text-[10px] font-bold mb-6 tracking-widest uppercase">
                <i class="fas fa-bolt"></i> G12 Next-Gen SOC
            </div>
            <h2 class="text-5xl md:text-6xl font-black mb-6 leading-tight text-white tracking-tight">
                Auditoría <br><span class="text-sky-500 italic">Descentralizada.</span>
            </h2>
            <p class="text-slate-400 text-lg leading-relaxed max-w-xl font-light">
                Seguridad ofensiva y defensiva verificada mediante <span class="text-slate-200 font-medium">Smart Contracts</span> en la red de Solana.
            </p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="glass-panel p-8 group cursor-pointer border border-white/5 bg-white/[0.02] rounded-2xl hover:bg-white/[0.05] transition-all" onclick="window.location='scanner.php'">
                <div class="w-14 h-14 bg-sky-500/10 rounded-xl flex items-center justify-center border border-sky-500/20 mb-6 group-hover:border-sky-400/50 transition-all">
                    <i class="fas fa-satellite text-sky-400 text-xl group-hover:-rotate-12 transition-transform"></i>
                </div>
                <h3 class="text-xl font-bold mb-2 uppercase tracking-tight text-white group-hover:text-sky-400 transition-colors">Escáner</h3>
                <p class="text-slate-500 text-sm mb-6 leading-relaxed">Mapeo de superficie de ataque y detección de activos.</p>
                <div class="text-sky-400 font-bold text-[10px] tracking-widest flex items-center justify-between border-t border-white/5 pt-4">
                    <span>INICIAR</span>
                    <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                </div>
            </div>

            <div class="glass-panel p-8 group cursor-pointer border border-white/5 bg-white/[0.02] rounded-2xl hover:bg-white/[0.05] transition-all" onclick="window.location='forensics.php'">
                <div class="w-14 h-14 bg-emerald-500/10 rounded-xl flex items-center justify-center border border-emerald-500/20 mb-6 group-hover:border-emerald-400/50 transition-all">
                    <i class="fas fa-microscope text-emerald-400 text-xl group-hover:scale-110 transition-transform"></i>
                </div>
                <h3 class="text-xl font-bold mb-2 uppercase tracking-tight text-white group-hover:text-emerald-400 transition-colors">Forense</h3>
                <p class="text-slate-500 text-sm mb-6 leading-relaxed">Análisis de trazas digitales y registros de intrusión.</p>
                <div class="text-emerald-400 font-bold text-[10px] tracking-widest flex items-center justify-between border-t border-white/5 pt-4">
                    <span>CONSOLA ROOT</span>
                    <i class="fas fa-terminal"></i>
                </div>
            </div>

            <div class="p-8 border-2 border-dashed border-slate-800 bg-transparent rounded-2xl flex flex-col justify-between">
                <div>
                    <h3 class="text-[9px] font-bold text-slate-500 uppercase tracking-[0.3em] mb-4">Auditor Status</h3>
                    <div id="status-card" class="text-[11px] font-mono p-4 bg-black/40 rounded-lg border border-slate-800 text-slate-500 italic break-all leading-relaxed">
                        Awaiting Web3 Authentication...
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-[9px] text-slate-600 uppercase font-black">Security Level</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                </div>
            </div>
        </div>
    </main>

    <footer class="border-t border-slate-800/50 bg-slate-900/30 py-8">
        <div class="max-w-7xl mx-auto px-6">
            <p class="text-[10px] font-bold text-slate-600 uppercase tracking-[0.2em]">&copy; 2026 CYBERPYME SOC G12. Securing the Decentralized Future.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>
</body>
</html>
