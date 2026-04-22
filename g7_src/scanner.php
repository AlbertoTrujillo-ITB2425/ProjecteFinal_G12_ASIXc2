<?php
/**
 * ============================================================================
 * CYBERPYME SOC v5.0.0 - MODULE 01: PERIMETER SCANNER (FINAL COMMIT)
 * ============================================================================
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
    <meta name="description" content="CyberPYME SOC - Escáner Perimetral G12">
    <meta name="theme-color" content="#0ea5e9">
    <title>Scanner G12 | CyberPYME SOC</title>

    <!-- FAVICON SVG INLINE -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%230ea5e9'/%3E%3Cstop offset='100%25' stop-color='%231d4ed8'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='64' height='64' rx='14' fill='url(%23g)'/%3E%3Cpath d='M32 10 L48 18 L48 34 C48 44 40 52 32 54 C24 52 16 44 16 34 L16 18 Z' fill='none' stroke='white' stroke-width='3' stroke-linejoin='round'/%3E%3Cpath d='M26 32 L30 36 L38 28' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3C/svg%3E">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        
        :root { 
            --accent-primary: #0ea5e9; 
            --accent-secondary: #10b981;
        }

        .goog-te-banner-frame { display: none !important; }
        .goog-tooltip { display: none !important; }
        body { top: 0px !important; position: static !important; font: inherit !important; }
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
        .terminal-bg { background-color: #010409; }

        @keyframes pulse-border {
            0% { border-color: rgba(14, 165, 233, 0.3); }
            50% { border-color: rgba(14, 165, 233, 0.8); }
            100% { border-color: rgba(14, 165, 233, 0.3); }
        }
        .scanning-active { animation: pulse-border 2s infinite; }

        /* Inputs styled to match the theme */
        .soc-input {
            width: 100%;
            background: rgba(0,0,0,0.4);
            border: 1px solid rgb(51 65 85);
            border-radius: 0.75rem;
            padding: 1rem;
            color: white;
            outline: none;
            transition: all 0.3s ease;
            font-family: 'JetBrains Mono', monospace;
        }
        .soc-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 2px rgba(14,165,233,0.2); }
        .light-mode .soc-input { background: rgba(255,255,255,0.8); border-color: #cbd5e1; color: #0f172a; }

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
            
            <div class="flex items-center gap-5">
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center border border-white/10 group-hover:bg-sky-500/10 group-hover:border-sky-500/30 transition-all">
                        <i class="fas fa-chevron-left text-slate-500 group-hover:text-sky-400 transition-colors text-sm"></i>
                    </div>
                </a>
                <div class="h-8 w-px bg-white/10"></div>
                <div class="flex items-center gap-4 cursor-pointer" onclick="window.location='index.php'">
                    <div class="w-12 h-12 bg-gradient-to-br from-sky-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg shadow-sky-500/30 border border-white/10">
                        <i class="fas fa-shield-halved text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tighter uppercase">CYBER<span class="text-sky-500">PYME</span></h1>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                            <span class="text-[10px] mono-tech text-slate-400 font-bold tracking-[0.3em]">SCANNER MODULE 01</span>
                        </div>
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

                <div id="wallet-indicator" class="hidden sm:flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold mono-tech">
                    <i class="fas fa-circle text-[8px] animate-pulse"></i> WALLET CONNECTED
                </div>
            </div>
        </div>
    </nav>

    <!-- STATUS BAR -->
    <div class="w-full bg-slate-900/80 border-b border-white/5 backdrop-blur-md hidden md:block">
        <div class="max-w-7xl mx-auto px-6 py-2 flex justify-between items-center text-[11px] mono-tech text-slate-400">
            <div class="flex gap-8">
                <span><i class="fas fa-satellite text-sky-500 mr-2"></i>MODULE: <span class="text-white">PERIMETER SCANNER</span></span>
                <span><i class="fas fa-crosshairs text-sky-500 mr-2"></i>ENGINE: <span class="text-white">NMAP v7.94</span></span>
            </div>
            <div class="flex gap-4">
                <span>SCAN DEPTH: <span class="text-sky-500 font-bold">CONFIGURABLE</span></span>
                <span>EXPORT: <span class="text-emerald-500 font-bold">PDF READY</span></span>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-6 py-10 flex-grow w-full">

        <!-- PAGE HEADER -->
        <div class="mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/20 text-sky-400 text-xs font-bold mb-4 tracking-widest">
                <i class="fas fa-satellite"></i> MODULE 01 — ESCÁNER PERIMETRAL
            </div>
            <h2 class="text-4xl md:text-5xl font-extrabold leading-tight tracking-tight">
                Análisis de <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-blue-500">Superficie de Ataque</span>
            </h2>
            <p class="text-slate-400 mt-3 text-base max-w-2xl font-light">
                Mapeo de puertos, servicios y vulnerabilidades. Inteligencia OSINT via motor NMAP integrado.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- SIDEBAR CONTROLES -->
            <div class="lg:col-span-4 space-y-6">
                <div class="glass-panel p-6 shadow-2xl">
                    <h3 class="text-sm font-bold mb-6 flex items-center gap-2 text-sky-400 uppercase tracking-widest">
                        <i class="fas fa-microchip"></i> Parámetros de Auditoría
                    </h3>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold mb-2">IP / Dominio Objetivo</label>
                            <input type="text" id="target" placeholder="127.0.0.1 o ejemplo.com" class="soc-input">
                        </div>

                        <div>
                            <label class="block text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold mb-2">Perfil de Escaneo</label>
                            <select id="type" class="soc-input cursor-pointer appearance-none">
                                <option value="quick">Escaneo Rápido (Top Ports)</option>
                                <option value="full">Escaneo de Servicios (Nmap -sV)</option>
                                <option value="vuln">Detección de Vulns (--script vuln)</option>
                            </select>
                        </div>

                        <button onclick="runAudit()" id="btn-run" class="w-full bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-sky-600/20 transition-all flex items-center justify-center gap-3 border border-sky-400/30 relative overflow-hidden group">
                            <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                            <i class="fas fa-bolt relative z-10"></i>
                            <span class="relative z-10 tracking-widest uppercase text-sm">Lanzar Auditoría</span>
                        </button>
                    </div>
                </div>

                <!-- INFO CARD -->
                <div class="glass-panel p-6">
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.3em] mb-4">Perfiles Disponibles</h3>
                    <div class="space-y-3">
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-black/20 border border-white/5">
                            <i class="fas fa-bolt text-sky-400 mt-0.5 text-sm"></i>
                            <div>
                                <p class="text-xs font-bold text-white">Rápido</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">Top 100 puertos. ~30 segundos.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-black/20 border border-white/5">
                            <i class="fas fa-search text-emerald-400 mt-0.5 text-sm"></i>
                            <div>
                                <p class="text-xs font-bold text-white">Servicios</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">Fingerprinting de versiones. ~2 min.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-black/20 border border-white/5">
                            <i class="fas fa-bug text-amber-400 mt-0.5 text-sm"></i>
                            <div>
                                <p class="text-xs font-bold text-white">Vulnerabilidades</p>
                                <p class="text-[10px] text-slate-500 mt-0.5">Scripts NSE de detección. ~5 min.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button onclick="exportToPDF()" id="btn-pdf" disabled class="w-full glass-panel p-5 flex items-center justify-center gap-3 text-slate-500 opacity-50 cursor-not-allowed transition-all border-dashed border-2 border-slate-700/50">
                    <i class="fas fa-file-pdf text-xl"></i>
                    <span class="font-bold tracking-widest uppercase text-sm">Exportar Reporte PDF</span>
                </button>
            </div>

            <!-- CONSOLA PRINCIPAL -->
            <div class="lg:col-span-8 flex flex-col" style="min-height: 600px;">
                <div id="output-wrapper" class="glass-panel flex-grow overflow-hidden flex flex-col border-slate-800" style="min-height: 600px;">
                    <!-- TERMINAL HEADER -->
                    <div class="bg-slate-900/90 px-6 py-4 border-b border-white/5 flex justify-between items-center flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/50 hover:bg-red-500 transition-colors cursor-pointer"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/50 hover:bg-amber-500 transition-colors cursor-pointer"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/50 hover:bg-emerald-500 transition-colors cursor-pointer"></div>
                            </div>
                            <span class="text-[10px] mono-tech text-slate-400 font-bold ml-4 tracking-widest">G12_AUDIT_CONSOLE_v1.0</span>
                        </div>
                        <div id="status-tag" class="text-[10px] bg-slate-800 text-slate-400 px-3 py-1 rounded-md font-bold uppercase mono-tech border border-white/5">Ready</div>
                    </div>

                    <div id="capture-area" class="flex-grow p-8 terminal-bg overflow-y-auto text-sm leading-relaxed">
                        <!-- PDF HEADER (hidden until export) -->
                        <div id="audit-report-header" class="hidden mb-8 pb-4 border-b border-sky-900/50">
                            <h1 style="color: #0ea5e9; font-size: 24px; font-weight: 800; font-family: 'JetBrains Mono', monospace;">INFORME DE AUDITORÍA PERIMETRAL</h1>
                            <p style="color: #64748b; font-size: 12px; margin-top: 4px;">Generado por: Plataforma CyberPYME G12 SOC</p>
                            <p style="color: #64748b; font-size: 12px;">Fecha: <span id="pdf-date"></span></p>
                        </div>
                        
                        <div id="console-output" class="mono-tech text-slate-400 whitespace-pre-wrap">
<span class="text-sky-500">╔══════════════════════════════════════════╗</span>
<span class="text-sky-500">║     CYBERPYME SOC — SCANNER MODULE 01    ║</span>
<span class="text-sky-500">╚══════════════════════════════════════════╝</span>

<span class="text-slate-500">[!] Sistema G12 en espera de instrucciones...</span>
<span class="text-slate-500">[!] Ingrese un objetivo y pulse "Lanzar Auditoría".</span>
<span class="text-slate-600">──────────────────────────────────────────</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="border-t border-slate-800/80 bg-slate-900/50 backdrop-blur-md mt-12">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-bold text-slate-600 uppercase tracking-widest">
                <div class="flex items-center gap-4">
                    <span class="text-sky-500 font-extrabold">CYBER<span class="text-white">PYME</span></span>
                    <span class="text-slate-700">|</span>
                    <p>&copy; 2026 G12 SOC Platform. All Rights Reserved.</p>
                </div>
                <div class="flex gap-4 text-lg">
                    <i class="fab fa-linux hover:text-white transition-colors cursor-pointer"></i>
                    <i class="fab fa-docker hover:text-sky-500 transition-colors cursor-pointer"></i>
                    <i class="fab fa-php hover:text-indigo-400 transition-colors cursor-pointer"></i>
                </div>
            </div>
        </div>
    </footer>

    <script>
        let isRunning = false;

        // THEME
        function setupTheme() {
            const savedTheme = localStorage.getItem('soc_theme') || 'dark';
            const body = document.body;
            const icon = document.getElementById('theme-icon');
            if (savedTheme === 'light') {
                body.classList.replace('dark-mode', 'light-mode');
                icon.classList.replace('fa-sun', 'fa-moon');
                icon.classList.replace('text-amber-400', 'text-indigo-600');
            }
        }

        function toggleTheme() {
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
        }

        function changeLanguage(lang, flag, code) {
            document.getElementById('current-lang-flag').innerText = flag;
            document.getElementById('current-lang-text').innerText = code;
            const selectField = document.querySelector(".goog-te-combo");
            if (selectField) { selectField.value = lang; selectField.dispatchEvent(new Event('change')); }
        }

        // AUDIT RUNNER
        async function runAudit() {
            if (isRunning) return;
            const target = document.getElementById('target').value.trim();
            const type = document.getElementById('type').value;
            const btn = document.getElementById('btn-run');
            const output = document.getElementById('console-output');
            const status = document.getElementById('status-tag');
            const panel = document.getElementById('output-wrapper');

            if (!target) { alert("⚠️ Error: Especifique un objetivo (IP o dominio)."); return; }

            isRunning = true;
            btn.innerHTML = '<i class="fas fa-sync fa-spin relative z-10"></i> <span class="relative z-10 tracking-widest uppercase text-sm">Ejecutando...</span>';
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            status.innerText = "Scanning...";
            status.className = "text-[10px] bg-sky-500/20 text-sky-400 px-3 py-1 rounded-md font-bold uppercase mono-tech border border-sky-500/30";
            panel.classList.add('scanning-active');
            
            output.innerHTML = `<span class="text-sky-500 font-bold">[+] Iniciando auditoría en: <span class="text-white">${target}</span></span>\n`;
            output.innerHTML += `<span class="text-slate-500">[+] Perfil seleccionado: ${type.toUpperCase()}</span>\n`;
            output.innerHTML += `<span class="text-slate-500">[+] Cargando módulos NMAP desde servidor...</span>\n`;
            output.innerHTML += `<span class="text-slate-500">[*] Esperando respuesta del motor PHP...\n\n</span>`;

            try {
                const response = await fetch('api_nmap.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ target: target, type: type })
                });

                if (!response.ok) throw new Error(`Servidor respondió con código ${response.status}`);

                const data = await response.json();

                if (data.status === "error") {
                    output.innerHTML += `<span class="text-red-400 font-bold">[ERROR] ${data.message}</span>\n`;
                    if (data.result) output.innerHTML += `<span class="text-slate-600">${data.result}</span>`;
                    status.innerText = "Error";
                    status.className = "text-[10px] bg-red-500/20 text-red-400 px-3 py-1 rounded-md font-bold uppercase mono-tech border border-red-500/30";
                } else {
                    output.innerHTML += `<span class="text-emerald-400 font-bold">╔══ RESULTADOS ENCONTRADOS ══╗</span>\n\n`;
                    output.innerHTML += `<span class="text-slate-300">${data.result}</span>`;
                    
                    const btnPdf = document.getElementById('btn-pdf');
                    btnPdf.disabled = false;
                    btnPdf.className = "w-full glass-panel p-5 flex items-center justify-center gap-3 text-emerald-400 transition-all border-2 border-emerald-500/30 hover:bg-emerald-500/10 cursor-pointer";
                    
                    confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 }, colors: ['#0ea5e9', '#10b981'] });

                    status.innerText = "Completed";
                    status.className = "text-[10px] bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-md font-bold uppercase mono-tech border border-emerald-500/30";
                }
            } catch (err) {
                output.innerHTML += `<span class="text-red-400">[ERROR CRÍTICO] Fallo de conexión: ${err.message}</span>`;
                status.innerText = "Error";
                status.className = "text-[10px] bg-red-500/20 text-red-400 px-3 py-1 rounded-md font-bold uppercase mono-tech border border-red-500/30";
            }

            isRunning = false;
            btn.innerHTML = '<i class="fas fa-bolt relative z-10"></i> <span class="relative z-10 tracking-widest uppercase text-sm">Lanzar Auditoría</span>';
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            panel.classList.remove('scanning-active');
        }

        function exportToPDF() {
            const target = document.getElementById('target').value || 'auditoria';
            const element = document.getElementById('capture-area');
            const header = document.getElementById('audit-report-header');
            const dateSpan = document.getElementById('pdf-date');
            
            dateSpan.innerText = new Date().toLocaleString('es-ES');
            header.classList.remove('hidden');

            const opt = {
                margin: 10,
                filename: `G12_Report_${target}_${Date.now()}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, backgroundColor: '#010409' },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                header.classList.add('hidden');
            });
        }

        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'es', includedLanguages: 'es,en,fr,zh-CN',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false
            }, 'google_translate_element');
        }

        document.addEventListener('DOMContentLoaded', setupTheme);
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
