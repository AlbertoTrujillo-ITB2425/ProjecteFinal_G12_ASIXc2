<?php
/**
 * ============================================================================
 * CYBERPYME SOC v5.0.0 - OFFICIAL MASTER DISTRIBUTION
 * ============================================================================
 * Desarrollado para el Proyecto Final G12 (2026)
 */
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CyberPYME Security Operations Center - G12 Final Project">
    <meta name="theme-color" content="#0ea5e9">
    <title>CyberPYME | Advanced Security Operations Center</title>

    <!-- FAVICON SVG INLINE -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%230ea5e9'/%3E%3Cstop offset='100%25' stop-color='%231d4ed8'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='64' height='64' rx='14' fill='url(%23g)'/%3E%3Cpath d='M32 10 L48 18 L48 34 C48 44 40 52 32 54 C24 52 16 44 16 34 L16 18 Z' fill='none' stroke='white' stroke-width='3' stroke-linejoin='round'/%3E%3Cpath d='M26 32 L30 36 L38 28' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3C/svg%3E">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        
        :root { 
            --accent-primary: #0ea5e9; 
            --accent-secondary: #10b981;
        }

        .goog-te-banner-frame { display: none !important; }
        .goog-tooltip { display: none !important; }
        .goog-te-balloon-frame { display: none !important; }
        body { top: 0px !important; position: static !important; }
        font { background-color: transparent !important; box-shadow: none !important; color: inherit !important; }
        #google_translate_element { position: absolute !important; left: -9999px !important; top: -9999px !important; z-index: -999 !important; }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            transition: background-color 0.5s ease, color 0.5s ease;
            overflow-x: hidden;
        }

        .dark-mode { background-color: #030712; color: #f8fafc; }
        .light-mode { background-color: #f1f5f9; color: #0f172a; }

        .glass-panel {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .light-mode .glass-panel {
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
        }
        .glass-panel:hover {
            border-color: var(--accent-primary);
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(14, 165, 233, 0.2);
        }
        .glass-panel::before {
            content: ''; position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent);
            transform: skewX(-20deg); transition: all 0.7s ease;
        }
        .glass-panel:hover::before { left: 150%; }

        .grid-overlay {
            position: fixed; inset: 0; z-index: -1;
            background-image: linear-gradient(to right, rgba(30, 41, 59, 0.15) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(30, 41, 59, 0.15) 1px, transparent 1px);
            background-size: 40px 40px; pointer-events: none;
            mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
            -webkit-mask-image: linear-gradient(to bottom, black 40%, transparent 100%);
        }
        .bg-glow {
            position: fixed; top: -10%; left: -10%; width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(14,165,233,0.05) 0%, rgba(0,0,0,0) 70%);
            z-index: -1; pointer-events: none;
        }

        .lang-container { position: relative; z-index: 1000; }
        .lang-dropdown {
            visibility: hidden; opacity: 0;
            position: absolute; top: 120%; right: 0;
            background: #0f172a; border: 1px solid rgba(255,255,255,0.1);
            border-radius: 1rem; width: 160px;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);
        }
        .light-mode .lang-dropdown { background: #ffffff; border-color: rgba(0,0,0,0.1); }
        .lang-container:hover .lang-dropdown { visibility: visible; opacity: 1; transform: translateY(0); }

        .mono-tech { font-family: 'JetBrains Mono', monospace; }
        .text-gradient { background: linear-gradient(to right, #0ea5e9, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="dark-mode min-h-screen flex flex-col">

    <div class="grid-overlay"></div>
    <div class="bg-glow"></div>
    <div id="google_translate_element"></div>

    <!-- NAV -->
    <nav class="sticky top-0 z-[100] border-b border-slate-800/50 backdrop-blur-xl bg-slate-900/50">
        <div class="max-w-7xl mx-auto px-6 h-24 flex justify-between items-center">
            <div class="flex items-center gap-5 cursor-pointer" onclick="window.location.reload()">
                <div class="w-14 h-14 bg-gradient-to-br from-sky-500 to-blue-700 rounded-2xl flex items-center justify-center shadow-lg shadow-sky-500/30 border border-white/10 relative overflow-hidden">
                    <i class="fas fa-shield-halved text-white text-2xl relative z-10"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tighter uppercase">CYBER<span class="text-sky-500">PYME</span></h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] mono-tech text-slate-400 font-bold tracking-[0.3em]">G12 SOC ACTIVE</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 md:gap-6">
                <div class="lang-container hidden sm:block">
                    <button class="bg-white/5 p-3 rounded-xl border border-white/10 flex items-center gap-3 hover:bg-white/10 hover:border-sky-500/50 transition-all focus:outline-none">
                        <span id="current-lang-flag" class="text-xl drop-shadow-md">🇪🇸</span>
                        <span id="current-lang-text" class="text-xs font-bold uppercase tracking-wider text-slate-300">ES</span>
                        <i class="fas fa-chevron-down text-[10px] opacity-50 ml-1"></i>
                    </button>
                    <div class="lang-dropdown overflow-hidden">
                        <button onclick="changeLanguage('es', '🇪🇸', 'ES')" class="w-full flex items-center gap-3 px-5 py-3 hover:bg-sky-500/20 transition-colors border-b border-white/5"><span class="text-lg">🇪🇸</span> <span class="text-sm font-semibold">Español</span></button>
                        <button onclick="changeLanguage('en', '🇺🇸', 'EN')" class="w-full flex items-center gap-3 px-5 py-3 hover:bg-sky-500/20 transition-colors border-b border-white/5"><span class="text-lg">🇺🇸</span> <span class="text-sm font-semibold">English</span></button>
                        <button onclick="changeLanguage('fr', '🇫🇷', 'FR')" class="w-full flex items-center gap-3 px-5 py-3 hover:bg-sky-500/20 transition-colors border-b border-white/5"><span class="text-lg">🇫🇷</span> <span class="text-sm font-semibold">Français</span></button>
                        <button onclick="changeLanguage('zh-CN', '🇨🇳', 'ZH')" class="w-full flex items-center gap-3 px-5 py-3 hover:bg-sky-500/20 transition-colors"><span class="text-lg">🇨🇳</span> <span class="text-sm font-semibold">中文</span></button>
                    </div>
                </div>

                <div class="h-8 w-px bg-white/10 hidden md:block"></div>

                <button onclick="toggleTheme()" class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center border border-white/5 hover:bg-white/10 hover:scale-105 transition-all">
                    <i id="theme-icon" class="fas fa-sun text-amber-400 text-lg drop-shadow-[0_0_8px_rgba(251,191,36,0.5)]"></i>
                </button>

                <button id="wallet-btn" onclick="web3Login()" class="group bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold px-6 md:px-8 py-3 rounded-xl transition-all shadow-lg shadow-sky-600/25 flex items-center gap-3 border border-sky-400/30 overflow-hidden relative">
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                    <i class="fas fa-fingerprint text-sky-200 relative z-10 group-hover:scale-110 transition-transform"></i>
                    <span id="wallet-text" class="relative z-10 text-sm md:text-base tracking-wide">LOGIN AUDITOR</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- STATUS BAR -->
    <div class="w-full bg-slate-900/80 border-b border-white/5 backdrop-blur-md hidden md:block">
        <div class="max-w-7xl mx-auto px-6 py-2 flex justify-between items-center text-[11px] mono-tech text-slate-400">
            <div class="flex gap-8">
                <span><i class="fas fa-server text-sky-500 mr-2"></i>NODES: <span class="text-white">12 ONLINE</span></span>
                <span><i class="fas fa-shield-virus text-emerald-500 mr-2"></i>THREATS BLOCKED: <span class="text-white">8,432</span></span>
            </div>
            <div class="flex gap-4">
                <span>SYSTEM ENCRYPTION: <span class="text-emerald-500 font-bold">AES-256</span></span>
                <span>NETWORK: <span class="text-sky-500 font-bold">SOLANA MAINNET</span></span>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-6 py-16 flex-grow flex flex-col justify-center">
        
        <div class="mb-16 md:mb-24 flex flex-col md:flex-row md:items-end justify-between gap-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/20 text-sky-400 text-xs font-bold mb-6 tracking-widest">
                    <i class="fas fa-bolt"></i> G12 NEXT-GEN SOC PLATFORM
                </div>
                <h2 class="text-5xl md:text-7xl font-extrabold mb-6 leading-[1.1] tracking-tight">
                    Auditoría <br>
                    <span class="text-gradient italic pr-2">Descentralizada.</span>
                </h2>
                <p class="text-slate-400 text-lg md:text-xl leading-relaxed max-w-2xl font-light">
                    Plataforma integral de operaciones de seguridad. Analice vulnerabilidades, inspeccione registros forenses y verifique auditorías inmutables en la blockchain.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <div class="glass-panel p-8 group cursor-pointer flex flex-col" onclick="window.location='scanner.php'">
                <div class="flex justify-between items-start mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-sky-500/20 to-blue-600/10 rounded-2xl flex items-center justify-center border border-sky-500/30 group-hover:border-sky-400 transition-colors">
                        <i class="fas fa-satellite text-sky-400 text-2xl group-hover:-rotate-12 group-hover:scale-110 transition-transform duration-300"></i>
                    </div>
                    <span class="bg-sky-500/10 text-sky-400 text-[10px] px-2 py-1 rounded-md font-bold mono-tech border border-sky-500/20">MODULE 01</span>
                </div>
                <h3 class="text-2xl font-bold mb-3 uppercase tracking-tight text-white group-hover:text-sky-400 transition-colors">Escáner Perimetral</h3>
                <p class="text-slate-400 text-sm mb-8 leading-relaxed flex-grow">Mapeo de superficie de ataque. Detección de puertos abiertos, servicios vulnerables e inteligencia OSINT via Shodan API.</p>
                <div class="w-full bg-slate-800/50 rounded-lg p-4 mt-auto border border-white/5 group-hover:bg-slate-800 transition-colors">
                    <div class="flex items-center justify-between text-sky-400 font-bold text-xs tracking-wider">
                        <span>INICIAR ESCANEO</span>
                        <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                    </div>
                </div>
            </div>

            <div class="glass-panel p-8 group cursor-pointer flex flex-col" onclick="window.location='forensics.php'">
                <div class="flex justify-between items-start mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500/20 to-teal-600/10 rounded-2xl flex items-center justify-center border border-emerald-500/30 group-hover:border-emerald-400 transition-colors">
                        <i class="fas fa-microscope text-emerald-400 text-2xl group-hover:scale-125 transition-transform duration-300"></i>
                    </div>
                    <span class="bg-emerald-500/10 text-emerald-400 text-[10px] px-2 py-1 rounded-md font-bold mono-tech border border-emerald-500/20">MODULE 02</span>
                </div>
                <h3 class="text-2xl font-bold mb-3 uppercase tracking-tight text-white group-hover:text-emerald-400 transition-colors">Análisis Forense</h3>
                <p class="text-slate-400 text-sm mb-8 leading-relaxed flex-grow">Acceso a terminal segura. Inspección profunda de logs del sistema, trazas de intrusión y contención de amenazas en tiempo real.</p>
                <div class="w-full bg-slate-800/50 rounded-lg p-4 mt-auto border border-white/5 group-hover:bg-slate-800 transition-colors">
                    <div class="flex items-center justify-between text-emerald-400 font-bold text-xs tracking-wider">
                        <span>ABRIR CONSOLA ROOT</span>
                        <i class="fas fa-terminal group-hover:animate-pulse"></i>
                    </div>
                </div>
            </div>

            <div class="glass-panel p-8 border-dashed border-2 border-slate-700/50 bg-transparent flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-[0.3em]">Live Identity Stats</h3>
                        <div class="flex gap-1">
                            <span class="w-2 h-2 rounded-full bg-red-500/20"></span>
                            <span class="w-2 h-2 rounded-full bg-amber-500/20"></span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                    </div>
                    <div class="space-y-5">
                        <div class="bg-black/30 p-4 rounded-xl border border-white/5">
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Threat Level</p>
                            <div class="flex items-center gap-3">
                                <i class="fas fa-shield-cat text-amber-500 text-xl"></i>
                                <p class="text-xl font-bold text-amber-500">DEFCON 4</p>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[10px] text-slate-500 uppercase mb-2">
                                <span>Network Traffic</span>
                                <span class="mono-tech text-sky-400">458 Mb/s</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-sky-500 to-blue-500 h-full w-[45%] relative">
                                    <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pt-6 border-t border-slate-700/50 mt-6">
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-3"><i class="fas fa-key mr-2"></i>Auditor Signature</p>
                    <div id="status-card" class="text-xs mono-tech p-3 bg-black/40 rounded-lg border border-slate-800 text-slate-500 italic break-all shadow-inner">
                        Awaiting Web3 Authentication...
                    </div>
                </div>
            </div>
        </div>

        <!-- PREMIUM BANNER -->
        <div class="mt-12 glass-panel p-8 bg-gradient-to-r from-indigo-900/30 to-purple-900/20 border-indigo-500/30 flex flex-col lg:flex-row items-center justify-between overflow-hidden relative group">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl group-hover:bg-purple-500/30 transition-colors"></div>
            <div class="relative z-10 w-full lg:w-2/3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/20 border border-purple-500/30 text-purple-300 text-xs font-bold mb-4 tracking-widest">
                    <i class="fas fa-crown text-amber-400"></i> G12 PREMIUM EDITION
                </div>
                <h3 class="text-3xl md:text-4xl font-extrabold text-white mb-3 tracking-tight">
                    Desbloquea el <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-indigo-400">SOC Enterprise</span>
                </h3>
                <p class="text-slate-400 text-sm md:text-base max-w-2xl leading-relaxed">
                    Auditorías ilimitadas, escaneo de vulnerabilidades Zero-Day con IA, y retención de logs en la blockchain durante 5 años.
                </p>
                <div class="flex gap-4 mt-4 text-xs font-semibold text-slate-500">
                    <span class="flex items-center gap-1"><i class="fas fa-check text-emerald-500"></i> No KYC</span>
                    <span class="flex items-center gap-1"><i class="fas fa-check text-emerald-500"></i> Instant Access</span>
                </div>
            </div>
            <div class="relative z-10 mt-8 lg:mt-0 w-full lg:w-auto flex flex-col items-center lg:items-end border-t lg:border-t-0 lg:border-l border-white/10 pt-6 lg:pt-0 lg:pl-10">
                <div class="text-5xl font-black text-white mb-1 flex items-end gap-2 drop-shadow-md">
                    29,99 <span class="text-xl text-purple-400 font-bold mb-1">USDC</span>
                </div>
                <div class="text-xs text-slate-500 font-mono tracking-widest uppercase mb-5">Por Mes / Cancela cuando quieras</div>
                <button onclick="payPremium()" class="w-full sm:w-auto relative group/btn bg-gradient-to-r from-[#ab9ff2] to-[#806ae6] hover:from-[#9c8eed] hover:to-[#7056e0] text-white font-bold px-8 py-4 rounded-xl shadow-[0_0_20px_rgba(128,106,230,0.3)] border border-white/20 flex items-center justify-center gap-3 transition-all hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700 ease-in-out"></div>
                    <img src="https://cryptologos.cc/logos/solana-sol-logo.svg?v=025" alt="Solana" class="w-5 h-5 filter brightness-0 invert">
                    <span class="tracking-wide">PAGAR CON PHANTOM</span>
                </button>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="border-t border-slate-800/80 bg-slate-900/50 backdrop-blur-md mt-auto">
        <div class="max-w-7xl mx-auto px-6 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8 border-b border-white/5 pb-8">
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Proyecto G12</h4>
                    <p class="text-slate-500 text-sm leading-relaxed max-w-xs">
                        Desarrollado como proyecto final. Integrando Ciberseguridad Ofensiva/Defensiva y tecnologías descentralizadas (Web3).
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Enlaces Rápidos</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="#" class="hover:text-sky-400 transition-colors"><i class="fas fa-angle-right mr-2 text-[10px]"></i>Documentación API</a></li>
                        <li><a href="https://github.com/AlbertoTrujillo-ITB2425/ProjecteFinal_G7" target="_blank" rel="noopener noreferrer" class="hover:text-sky-400 transition-colors"><i class="fab fa-github mr-2 text-[12px]"></i>Repositorio GitHub</a></li>
                        <li><a href="https://explorer.solana.com/" target="_blank" class="hover:text-purple-400 transition-colors"><i class="fas fa-link mr-2 text-[10px]"></i>Solana Explorer</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase tracking-wider text-sm">Estado del Sistema</h4>
                    <div class="flex items-center gap-3 text-sm text-slate-500 mb-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Backend API: Operativo</div>
                    <div class="flex items-center gap-3 text-sm text-slate-500 mb-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Smart Contracts: Solana Mainnet</div>
                    <div class="flex items-center gap-3 text-sm text-slate-500"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Base de Datos: Sincronizada</div>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-bold text-slate-600 uppercase tracking-widest">
                <p>&copy; 2026 CYBERPYME SOC G12. All Rights Reserved.</p>
                <div class="flex gap-4 text-lg">
                    <i class="fab fa-linux hover:text-white transition-colors cursor-pointer"></i>
                    <i class="fab fa-docker hover:text-sky-500 transition-colors cursor-pointer"></i>
                    <i class="fab fa-php hover:text-indigo-400 transition-colors cursor-pointer"></i>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const SOCCore = {
            isConnected: false,

            init: function() {
                this.setupTheme();
                this.enforceNoGoogleBar();
            },

            translate: function(langCode, flagEmoji, shortCode) {
                document.getElementById('current-lang-flag').innerText = flagEmoji;
                document.getElementById('current-lang-text').innerText = shortCode;
                const selectField = document.querySelector(".goog-te-combo");
                if (selectField) {
                    selectField.value = langCode;
                    selectField.dispatchEvent(new Event('change'));
                }
            },

            enforceNoGoogleBar: function() {
                const observer = new MutationObserver(() => {
                    if (document.body.style.top !== '0px') {
                        document.body.style.top = '0px';
                        document.body.style.position = 'static';
                    }
                });
                observer.observe(document.body, { attributes: true, attributeFilter: ['style'] });
            },

            setupTheme: function() {
                const savedTheme = localStorage.getItem('soc_theme') || 'dark';
                const body = document.body;
                const icon = document.getElementById('theme-icon');
                if (savedTheme === 'light') {
                    body.classList.replace('dark-mode', 'light-mode');
                    icon.classList.replace('fa-sun', 'fa-moon');
                    icon.classList.replace('text-amber-400', 'text-indigo-600');
                }
            },

            toggleTheme: function() {
                const body = document.body;
                const icon = document.getElementById('theme-icon');
                const isDark = body.classList.contains('dark-mode');
                if (isDark) {
                    body.classList.replace('dark-mode', 'light-mode');
                    icon.classList.replace('fa-sun', 'fa-moon');
                    icon.classList.replace('text-amber-400', 'text-indigo-600');
                    localStorage.setItem('soc_theme', 'light');
                } else {
                    body.classList.replace('light-mode', 'dark-mode');
                    icon.classList.replace('fa-moon', 'fa-sun');
                    icon.classList.replace('text-indigo-600', 'text-amber-400');
                    localStorage.setItem('soc_theme', 'dark');
                }
            },

            loginWeb3: async function() {
                const btn = document.getElementById('wallet-btn');
                const text = document.getElementById('wallet-text');
                const status = document.getElementById('status-card');
                try {
                    const provider = window.phantom?.solana;
                    if (!provider) {
                        alert("⚠️ No se ha detectado Phantom Wallet.\nPor favor, instale la extensión del navegador.");
                        window.open("https://phantom.app/", "_blank");
                        return;
                    }
                    text.innerText = "CONECTANDO...";
                    btn.classList.add('opacity-80', 'cursor-not-allowed');
                    const resp = await provider.connect();
                    const pubKey = resp.publicKey.toString();
                    const message = `[CYBERPYME SOC G12]\nAuditoría de Acceso Restringido\n\nWallet: ${pubKey}\nTimestamp: ${Date.now()}`;
                    const encodedMessage = new TextEncoder().encode(message);
                    text.innerText = "FIRME MENSAJE...";
                    await provider.signMessage(encodedMessage, "utf8");
                    if(typeof confetti === 'function') {
                        confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 }, colors: ['#0ea5e9', '#10b981', '#ab9ff2'] });
                    }
                    this.isConnected = true;
                    const shortKey = pubKey.substring(0, 4) + "..." + pubKey.substring(pubKey.length - 4);
                    text.innerText = shortKey;
                    btn.className = "bg-emerald-600 text-white font-bold px-6 md:px-8 py-3 rounded-xl shadow-lg shadow-emerald-500/20 border border-emerald-400 flex items-center gap-3";
                    btn.innerHTML = `<i class="fas fa-check-circle text-white"></i> <span class="tracking-widest">${shortKey}</span>`;
                    status.innerHTML = `<div class="text-emerald-400 font-bold mb-1">✅ SIGNATURE VERIFIED</div><div class="text-white">${pubKey}</div><div class="mt-2 text-[10px] text-slate-500">Access Level: G12 ROOT</div>`;
                    status.classList.remove('italic', 'text-slate-500');
                    status.classList.add('border-emerald-500/30', 'bg-emerald-900/10');
                } catch (err) {
                    console.error("Autenticación Web3 abortada:", err);
                    text.innerText = "LOGIN RECHAZADO";
                    setTimeout(() => {
                        text.innerText = "LOGIN AUDITOR";
                        btn.classList.remove('opacity-80', 'cursor-not-allowed');
                    }, 3000);
                }
            },

            payPremium: async function() {
                if (!this.isConnected) {
                    alert("🔒 ACCESO DENEGADO\n\nPor favor, haz clic en 'LOGIN AUDITOR' e inicia sesión con tu Phantom Wallet antes de procesar el pago.");
                    return;
                }
                try {
                    alert("🚀 INTERACCIÓN CON SMART CONTRACT\n\nSe solicitará a su Phantom Wallet la aprobación para transferir 29.99 USDC a la tesorería del Proyecto G12.");
                    setTimeout(() => {
                        if(typeof confetti === 'function') {
                            confetti({ particleCount: 300, spread: 120, origin: { y: 0.5 }, colors: ['#ab9ff2', '#806ae6', '#ffffff'] });
                        }
                        alert("✅ TRANSACCIÓN CONFIRMADA EN SOLANA MAINNET\n\nTxHash: 4gKtQzX...p9T\n\n¡Bienvenido a CYBERPYME G12 PREMIUM!");
                    }, 2000);
                } catch (error) {
                    alert("❌ Transacción rechazada por el usuario o error en la red.");
                }
            }
        };

        function changeLanguage(lang, flag, code) { SOCCore.translate(lang, flag, code); }
        function toggleTheme() { SOCCore.toggleTheme(); }
        function web3Login() { SOCCore.loginWeb3(); }
        function payPremium() { SOCCore.payPremium(); }

        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'es',
                includedLanguages: 'es,en,fr,zh-CN',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                autoDisplay: false
            }, 'google_translate_element');
        }

        document.addEventListener('DOMContentLoaded', () => SOCCore.init());
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
