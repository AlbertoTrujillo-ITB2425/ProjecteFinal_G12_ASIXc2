<?php
/**
 * ============================================================================
 * CYBERPYME SOC v5.0.0 - MODULE 02: FORENSIC TERMINAL (FINAL COMMIT)
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
    <meta name="description" content="CyberPYME SOC - Terminal Forense G12">
    <meta name="theme-color" content="#10b981">
    <title>Forensics G12 | CyberPYME SOC</title>

    <!-- FAVICON SVG INLINE -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%230ea5e9'/%3E%3Cstop offset='100%25' stop-color='%231d4ed8'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='64' height='64' rx='14' fill='url(%23g)'/%3E%3Cpath d='M32 10 L48 18 L48 34 C48 44 40 52 32 54 C24 52 16 44 16 34 L16 18 Z' fill='none' stroke='white' stroke-width='3' stroke-linejoin='round'/%3E%3Cpath d='M26 32 L30 36 L38 28' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3C/svg%3E">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        
        :root { 
            --accent-primary: #0ea5e9; 
            --accent-secondary: #10b981;
        }

        .goog-te-banner-frame { display: none !important; }
        .goog-tooltip { display: none !important; }
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
        .bg-glow-emerald {
            position: fixed; top: -10%; right: -10%; width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(16,185,129,0.04) 0%, rgba(0,0,0,0) 70%);
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

        /* TERMINAL CORE */
        .terminal-shell {
            background: rgba(1, 4, 9, 0.97);
            border: 1px solid rgba(16, 185, 129, 0.15);
            border-radius: 1.5rem;
            box-shadow: 0 0 40px rgba(16, 185, 129, 0.05), 0 20px 50px rgba(0,0,0,0.5);
            overflow: hidden;
        }
        .terminal-output {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            line-height: 1.7;
            color: #94a3b8;
        }
        .terminal-output .cmd { color: #ffffff; }
        .terminal-output .success { color: #10b981; }
        .terminal-output .warning { color: #f59e0b; }
        .terminal-output .error { color: #ef4444; }
        .terminal-output .info { color: #0ea5e9; }
        .terminal-output .prompt { color: #10b981; font-weight: bold; }
        .terminal-output .dim { color: #334155; }

        .command-input {
            background: transparent;
            outline: none;
            border: none;
            color: #ffffff;
            width: 100%;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            caret-color: #10b981;
        }

        /* SCANLINE EFFECT */
        .scanline {
            width: 100%; height: 100%; z-index: 0;
            background: repeating-linear-gradient(
                0deg, transparent, transparent 2px,
                rgba(16, 185, 129, 0.008) 2px, rgba(16, 185, 129, 0.008) 4px
            );
            position: absolute; pointer-events: none; top: 0; left: 0;
            border-radius: 1.5rem;
        }

        @keyframes blink { 50% { opacity: 0; } }
        .blink { animation: blink 1s infinite; }

        @keyframes glow-pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        .status-glow { animation: glow-pulse 2s infinite; }

        /* SCROLLBAR TERMINAL */
        .terminal-scroll::-webkit-scrollbar { width: 6px; }
        .terminal-scroll::-webkit-scrollbar-track { background: rgba(0,0,0,0.3); }
        .terminal-scroll::-webkit-scrollbar-thumb { background: rgba(16,185,129,0.3); border-radius: 4px; }
        .terminal-scroll::-webkit-scrollbar-thumb:hover { background: rgba(16,185,129,0.5); }

        /* SIDEBAR INFO */
        .stat-chip {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .stat-chip:hover { border-color: rgba(16, 185, 129, 0.3); background: rgba(16,185,129,0.05); }
        .light-mode .stat-chip { background: rgba(255,255,255,0.9); border-color: rgba(0,0,0,0.08); }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>
<body class="dark-mode min-h-screen flex flex-col">

    <div class="grid-overlay"></div>
    <div class="bg-glow-emerald"></div>
    <div id="google_translate_element"></div>

    <!-- NAV -->
    <nav class="sticky top-0 z-[100] border-b border-slate-800/50 backdrop-blur-xl bg-slate-900/50">
        <div class="max-w-7xl mx-auto px-6 h-24 flex justify-between items-center">
            
            <div class="flex items-center gap-5">
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center border border-white/10 group-hover:bg-emerald-500/10 group-hover:border-emerald-500/30 transition-all">
                        <i class="fas fa-chevron-left text-slate-500 group-hover:text-emerald-400 transition-colors text-sm"></i>
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
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] mono-tech text-slate-400 font-bold tracking-[0.3em]">FORENSICS MODULE 02</span>
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

                <button onclick="clearTerminal()" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 hover:bg-red-500/10 hover:border-red-500/30 transition-all text-slate-400 hover:text-red-400 text-xs font-bold tracking-widest uppercase mono-tech">
                    <i class="fas fa-trash-alt text-[10px]"></i> Clear
                </button>
            </div>
        </div>
    </nav>

    <!-- STATUS BAR -->
    <div class="w-full bg-slate-900/80 border-b border-white/5 backdrop-blur-md hidden md:block">
        <div class="max-w-7xl mx-auto px-6 py-2 flex justify-between items-center text-[11px] mono-tech text-slate-400">
            <div class="flex gap-8">
                <span><i class="fas fa-microscope text-emerald-500 mr-2"></i>MODULE: <span class="text-white">FORENSIC TERMINAL</span></span>
                <span><i class="fas fa-terminal text-emerald-500 mr-2"></i>SHELL: <span class="text-white">G12 ROOT v1.0.4</span></span>
            </div>
            <div class="flex gap-4">
                <span>ACCESS LEVEL: <span class="text-emerald-500 font-bold">ROOT FORENSIC</span></span>
                <span>NODE: <span class="text-sky-500 font-bold">ip-172-31-43-212</span></span>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-6 py-10 flex-grow w-full">

        <!-- PAGE HEADER -->
        <div class="mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold mb-4 tracking-widest">
                <i class="fas fa-microscope"></i> MODULE 02 — ANÁLISIS FORENSE
            </div>
            <h2 class="text-4xl md:text-5xl font-extrabold leading-tight tracking-tight">
                Terminal <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-500">Forense Segura</span>
            </h2>
            <p class="text-slate-400 mt-3 text-base max-w-2xl font-light">
                Inspección de logs del sistema, trazas de intrusión y análisis de amenazas en tiempo real con acceso Root.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- SIDEBAR INFO -->
            <div class="lg:col-span-3 space-y-5">
                
                <div class="glass-panel p-5">
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.3em] mb-4">Estado del Sistema</h3>
                    <div class="space-y-3">
                        <div class="stat-chip">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[10px] text-slate-500 uppercase tracking-widest">Threat Level</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-shield-cat text-amber-500"></i>
                                <span class="text-sm font-bold text-amber-500 mono-tech">DEFCON 4</span>
                            </div>
                        </div>

                        <div class="stat-chip">
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Uptime</p>
                            <p class="text-sm font-bold text-emerald-400 mono-tech status-glow">● 99.97% ONLINE</p>
                        </div>

                        <div class="stat-chip">
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">Active Sessions</p>
                            <p class="text-sm font-bold text-white mono-tech">1 ROOT SESSION</p>
                        </div>

                        <div class="stat-chip">
                            <div class="flex justify-between text-[10px] text-slate-500 uppercase mb-2">
                                <span>Log Buffer</span>
                                <span class="text-sky-400 mono-tech">72%</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-sky-500 to-emerald-500 h-full w-[72%] relative">
                                    <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-panel p-5">
                    <h3 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.3em] mb-4">Comandos Disponibles</h3>
                    <div class="space-y-2">
                        <?php
                        $cmds = [
                            ['cmd' => 'help', 'desc' => 'Lista de comandos', 'color' => 'sky'],
                            ['cmd' => 'whoami', 'desc' => 'Info del auditor', 'color' => 'emerald'],
                            ['cmd' => 'logs', 'desc' => 'Registros de acceso', 'color' => 'amber'],
                            ['cmd' => 'scan', 'desc' => 'Escaneo de red local', 'color' => 'purple'],
                            ['cmd' => 'netstat', 'desc' => 'Conexiones activas', 'color' => 'sky'],
                            ['cmd' => 'ps', 'desc' => 'Procesos del sistema', 'color' => 'emerald'],
                            ['cmd' => 'clear', 'desc' => 'Limpiar terminal', 'color' => 'slate'],
                            ['cmd' => 'exit', 'desc' => 'Cerrar sesión', 'color' => 'red'],
                        ];
                        foreach ($cmds as $c):
                        ?>
                        <button onclick="injectCommand('<?= $c['cmd'] ?>')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg bg-black/20 border border-white/5 hover:bg-<?= $c['color'] ?>-500/10 hover:border-<?= $c['color'] ?>-500/20 transition-all group">
                            <span class="text-xs mono-tech text-<?= $c['color'] ?>-400 font-bold"><?= $c['cmd'] ?></span>
                            <span class="text-[10px] text-slate-600 group-hover:text-slate-400 transition-colors"><?= $c['desc'] ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- TERMINAL PRINCIPAL -->
            <div class="lg:col-span-9" style="height: 620px; display: flex; flex-direction: column;">
                <div class="terminal-shell flex flex-col" style="height: 100%; position: relative;">
                    <div class="scanline"></div>

                    <!-- TERMINAL HEADER BAR -->
                    <div class="bg-slate-950/90 px-6 py-4 border-b border-emerald-900/30 flex justify-between items-center flex-shrink-0 relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-red-500/60 hover:bg-red-500 transition-colors cursor-pointer" onclick="window.location='index.php'" title="Cerrar"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/60 hover:bg-amber-500 transition-colors cursor-pointer" title="Minimizar"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/60 hover:bg-emerald-500 transition-colors cursor-pointer" title="Maximizar"></div>
                            </div>
                            <span class="text-[11px] mono-tech text-slate-500 ml-4 tracking-widest">auditor@cyberpyme:~</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] mono-tech text-emerald-500 font-bold flex items-center gap-1.5 status-glow">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> CONNECTED
                            </span>
                        </div>
                    </div>

                    <!-- TERMINAL BODY -->
                    <div id="terminal-history" class="flex-grow overflow-y-auto terminal-scroll terminal-output p-6 relative z-10" style="min-height: 0;">
                        <div class="mb-4">
                            <span class="success">╔══════════════════════════════════════════════════╗</span><br>
                            <span class="success">║   CYBERPYME SOC — FORENSIC TERMINAL v1.0.4       ║</span><br>
                            <span class="success">╚══════════════════════════════════════════════════╝</span>
                        </div>
                        <div class="mb-1"><span class="info">[SYSTEM]</span> Inicializando entorno de análisis forense...</div>
                        <div class="mb-1"><span class="info">[SYSTEM]</span> Cargando módulos de inspección de red... <span class="success">OK</span></div>
                        <div class="mb-4"><span class="info">[SYSTEM]</span> Conexión establecida con el nodo central CyberPYME.</div>
                        <div class="mb-1 dim">──────────────────────────────────────────────────</div>
                        <div class="mb-4">Escribe <span class="cmd font-bold">'help'</span> para ver los comandos disponibles.</div>
                    </div>

                    <!-- PROMPT INPUT -->
                    <div class="flex items-center gap-3 border-t border-emerald-900/30 px-6 py-4 bg-slate-950/80 flex-shrink-0 relative z-10">
                        <span class="prompt mono-tech text-sm flex-shrink-0">auditor@cyberpyme:~$</span>
                        <input type="text" id="terminal-input" class="command-input text-sm" autofocus autocomplete="off" spellcheck="false" placeholder="Ingrese un comando...">
                        <span class="w-2 h-5 bg-emerald-500 blink flex-shrink-0 rounded-sm"></span>
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
        const historyEl = document.getElementById('terminal-history');
        const inputEl = document.getElementById('terminal-input');
        let cmdHistory = [];
        let cmdHistoryIndex = -1;

        const COMMANDS = {
            'help': {
                output: `<span class="success">╔══ COMANDOS DISPONIBLES ══╗</span>
  <span class="info">help</span>     — Muestra esta ayuda
  <span class="info">whoami</span>   — Información del auditor actual
  <span class="info">logs</span>     — Registros de acceso recientes
  <span class="info">scan</span>     — Escaneo rápido de red local
  <span class="info">netstat</span>  — Conexiones de red activas
  <span class="info">ps</span>       — Procesos del sistema en ejecución
  <span class="info">clear</span>    — Limpia la terminal
  <span class="info">exit</span>     — Regresa al dashboard principal`
            },
            'whoami': {
                output: `<span class="success">AUDITOR_ID:</span>    G7-SOC-2026
<span class="success">PERMISSIONS:</span>  Root Forensic Access
<span class="success">NODE:</span>          ip-172-31-43-212
<span class="success">AUTH_METHOD:</span>  Web3 Signature (Phantom)
<span class="success">SESSION:</span>       Active since ${new Date().toLocaleTimeString('es-ES')}`
            },
            'logs': {
                output: `<span class="warning">[ ACCESS LOG — ÚLTIMAS 10 ENTRADAS ]</span>
<span class="dim">──────────────────────────────────────────────────</span>
<span class="warning">[${new Date().toTimeString().split(' ')[0]}]</span> Attempted SSH Login from 192.168.1.45 — <span class="error">BLOCKED</span>
<span class="warning">[19:42:15]</span> Firewall rule updated: DROP ICMP — <span class="success">OK</span>
<span class="warning">[19:43:02]</span> Unauthorized API call detected — <span class="warning">TRACING...</span>
<span class="warning">[19:44:18]</span> Port scan from 10.0.0.23 — <span class="error">BLOCKED</span>
<span class="warning">[19:45:00]</span> SSL Certificate renewed — <span class="success">OK</span>
<span class="warning">[19:46:33]</span> Brute force attempt /wp-admin — <span class="error">BLOCKED (42 attempts)</span>
<span class="warning">[19:47:10]</span> New root session opened — <span class="info">THIS SESSION</span>
<span class="warning">[19:48:22]</span> IDS rule triggered: SQL Injection pattern — <span class="warning">ALERT</span>
<span class="warning">[19:49:05]</span> Backup completed to encrypted volume — <span class="success">OK</span>
<span class="warning">[19:50:00]</span> Network traffic anomaly detected — <span class="warning">MONITORING</span>`
            },
            'scan': {
                output: `<span class="info">[ NMAP STEALTH SCAN — 172.31.43.0/24 ]</span>
<span class="dim">Iniciando escaneo... esto puede tardar unos segundos.</span>

Host: <span class="success">172.31.43.1</span>    (UP)   Ports: <span class="warning">80/tcp open</span>
Host: <span class="success">172.31.43.100</span>  (UP)   Ports: <span class="warning">22/tcp open, 3306/tcp open</span>
Host: <span class="success">172.31.43.212</span>  (UP)   Ports: <span class="warning">80/tcp open, 443/tcp open</span>
Host: <span class="error">172.31.43.250</span>  (DOWN) —

<span class="success">Escaneo completado.</span> 3 hosts activos / 1 inactivo.
<span class="dim">Usa el Módulo 01 (Scanner) para un análisis más profundo.</span>`
            },
            'netstat': {
                output: `<span class="info">[ CONEXIONES DE RED ACTIVAS ]</span>
<span class="dim">Proto  Local             Remoto            Estado</span>
<span class="dim">──────────────────────────────────────────────────</span>
tcp    0.0.0.0:80        *:*               <span class="success">LISTEN</span>
tcp    0.0.0.0:443       *:*               <span class="success">LISTEN</span>
tcp    0.0.0.0:22        *:*               <span class="success">LISTEN</span>
tcp    172.31.43.212:443 93.184.216.34:52841  <span class="success">ESTABLISHED</span>
tcp    172.31.43.212:80  185.199.108.153:38921 <span class="success">ESTABLISHED</span>
tcp6   :::3306           :::*              <span class="warning">LISTEN (local only)</span>`
            },
            'ps': {
                output: `<span class="info">[ PROCESOS DEL SISTEMA ]</span>
<span class="dim">PID    CPU%  MEM%  COMANDO</span>
<span class="dim">──────────────────────────────────────────────────</span>
1      0.0   0.1   <span class="success">systemd</span>
423    0.2   1.2   <span class="success">apache2</span>
891    0.1   2.4   <span class="success">mysql</span>
1104   0.0   0.3   <span class="success">sshd</span>
1337   1.5   3.1   <span class="warning">php-fpm</span> [active requests: 3]
2048   0.3   0.8   <span class="info">cyberpyme-soc-monitor</span>
9999   0.0   0.1   <span class="success">cron</span>`
            }
        };

        inputEl.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const cmd = inputEl.value.trim();
                if (cmd) {
                    cmdHistory.unshift(cmd);
                    cmdHistoryIndex = -1;
                    executeCommand(cmd.toLowerCase());
                }
                inputEl.value = '';
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (cmdHistoryIndex < cmdHistory.length - 1) {
                    cmdHistoryIndex++;
                    inputEl.value = cmdHistory[cmdHistoryIndex];
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (cmdHistoryIndex > 0) {
                    cmdHistoryIndex--;
                    inputEl.value = cmdHistory[cmdHistoryIndex];
                } else {
                    cmdHistoryIndex = -1;
                    inputEl.value = '';
                }
            }
        });

        function executeCommand(cmd) {
            const line = document.createElement('div');
            line.className = 'mb-1';
            line.innerHTML = `<span class="prompt">auditor@cyberpyme:~$</span> <span class="cmd">${escapeHtml(cmd)}</span>`;
            historyEl.appendChild(line);

            const response = document.createElement('div');
            response.className = "pl-2 mb-4";

            if (cmd === 'clear') {
                clearTerminal();
                return;
            } else if (cmd === 'exit') {
                response.innerHTML = `<span class="warning">Cerrando sesión forense...</span>`;
                historyEl.appendChild(response);
                setTimeout(() => window.location.href = 'index.php', 800);
            } else if (COMMANDS[cmd]) {
                response.innerHTML = COMMANDS[cmd].output;
                historyEl.appendChild(response);
            } else if (cmd !== '') {
                response.innerHTML = `<span class="error">bash: ${escapeHtml(cmd)}: comando no encontrado.</span> Escribe <span class="cmd">'help'</span> para ver la lista.`;
                historyEl.appendChild(response);
            }

            historyEl.scrollTop = historyEl.scrollHeight;
        }

        function injectCommand(cmd) {
            inputEl.value = cmd;
            inputEl.focus();
            executeCommand(cmd);
            inputEl.value = '';
        }

        function clearTerminal() {
            historyEl.innerHTML = `<div class="mb-4 dim">Terminal limpiada — ${new Date().toLocaleString('es-ES')}</div>`;
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

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

        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'es', includedLanguages: 'es,en,fr,zh-CN',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false
            }, 'google_translate_element');
        }

        document.addEventListener('DOMContentLoaded', () => {
            setupTheme();
            // Always keep focus on input when clicking terminal area
            document.getElementById('terminal-history').addEventListener('click', () => inputEl.focus());
        });

        // Prevent losing focus to outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('input')) {
                inputEl.focus();
            }
        });
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>

