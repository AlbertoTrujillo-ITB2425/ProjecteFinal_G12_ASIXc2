<?php
/**
 * CYBERPYME SOC v5.3.0 - ADVANCED AUDIT CONSOLE
 * Final Version: Professional PDF Reporting & UI Refinement.
 */
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOC Auditor | CyberPYME</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        .scanning-active { animation: pulse-border 2s infinite; border-color: #0ea5e9 !important; }
        @keyframes pulse-border {
            0%, 100% { box-shadow: 0 0 15px 0px rgba(14, 165, 233, 0.2); }
            50% { box-shadow: 0 0 25px 10px rgba(14, 165, 233, 0.3); }
        }
        .terminal-bg { background-color: #010409; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
    </style>
</head>
<body class="dark-mode min-h-screen flex flex-col bg-slate-950 text-slate-200">

    <div class="grid-overlay fixed inset-0 pointer-events-none opacity-20"></div>

    <nav class="sticky top-0 z-[100] border-b border-slate-800/50 backdrop-blur-xl bg-slate-900/60">
        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-6">
                <a href="index.php" class="group w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center border border-white/10 hover:border-sky-500/50 transition-all">
                    <i class="fas fa-chevron-left text-slate-500 group-hover:text-sky-400"></i>
                </a>
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 bg-gradient-to-br from-sky-500 to-blue-700 rounded-xl flex items-center justify-center shadow-xl shadow-sky-500/20 border border-white/10">
                        <i class="fas fa-satellite text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-black tracking-tight uppercase">CYBER<span class="text-sky-500">PYME</span></h1>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[9px] font-bold text-slate-500 tracking-[0.2em] uppercase">G12 AUDIT CONSOLE</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <button onclick="toggleTheme()" class="w-11 h-11 rounded-xl bg-white/5 flex items-center justify-center border border-white/10 hover:bg-white/10 transition-all">
                <i id="theme-icon" class="fas fa-sun text-amber-400"></i>
            </button>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10 flex-grow w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4 space-y-6">
                <div class="glass-panel p-8">
                    <h3 class="text-[10px] font-black mb-8 text-sky-400 uppercase tracking-[0.3em] flex items-center gap-3">
                        <i class="fas fa-microchip text-xs"></i> Audit Parameters
                    </h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[9px] uppercase text-slate-500 font-black mb-2 tracking-widest">Network Target</label>
                            <input type="text" id="target" placeholder="IP or Domain" 
                                class="w-full bg-black/40 border border-slate-800 rounded-xl p-4 text-sm font-mono text-sky-400 outline-none focus:border-sky-500/50 transition-all">
                        </div>

                        <div>
                            <label class="block text-[9px] uppercase text-slate-500 font-black mb-2 tracking-widest">Audit Profile</label>
                            <select id="type" class="w-full bg-black/40 border border-slate-800 rounded-xl p-4 text-sm font-mono text-slate-300 outline-none focus:border-sky-500/50 appearance-none cursor-pointer">
                                <option value="quick">⚡ NMAP: Fast Recon</option>
                                <option value="full">🛡️ NMAP: Aggressive</option>
                                <option value="shodan">🔍 SHODAN: OSINT Intelligence</option>
                            </select>
                        </div>

                        <div class="pt-4">
                            <button id="btn-run" onclick="runAudit()" class="group w-full bg-sky-600 hover:bg-sky-500 text-white font-black py-5 rounded-xl transition-all uppercase tracking-widest text-xs shadow-lg shadow-sky-600/20 flex items-center justify-center gap-3">
                                <i class="fas fa-bolt group-hover:animate-bounce"></i> Run Audit
                            </button>
                        </div>
                    </div>
                </div>

                <div id="threat-intel" class="hidden glass-panel p-8 border-red-500/10 bg-red-500/[0.02]">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-[9px] font-black text-red-400 uppercase tracking-widest">Risk Assessment</h4>
                        <i class="fas fa-shield-virus text-red-500"></i>
                    </div>
                    <div class="flex items-end justify-between mb-3">
                        <div class="text-4xl font-black text-white" id="score-val">0<span class="text-xs text-slate-500">/100</span></div>
                        <span class="text-[9px] text-red-400 font-bold uppercase tracking-tighter">Threat Score</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div id="score-bar" class="h-full bg-red-500 w-0 transition-all duration-1000"></div>
                    </div>
                </div>

                <button id="btn-pdf" onclick="exportToPDF()" disabled class="w-full p-5 flex items-center justify-center gap-3 text-slate-600 border-2 border-dashed border-slate-800 rounded-2xl hover:border-sky-500/30 hover:text-sky-400 transition-all cursor-not-allowed uppercase font-black text-[10px] tracking-widest">
                    <i class="fas fa-file-contract"></i> Generate Technical Report
                </button>
            </div>

            <div class="lg:col-span-8 flex flex-col min-h-[650px]">
                <div id="output-wrapper" class="flex-grow flex flex-col border border-white/5 bg-slate-900/40 rounded-3xl overflow-hidden shadow-2xl">
                    <div class="bg-slate-900/90 px-8 py-5 border-b border-white/5 flex justify-between items-center">
                        <div class="flex items-center gap-6">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500/40 border border-red-500/20"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/40 border border-amber-500/20"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/40 border border-emerald-500/20"></div>
                            </div>
                            <span class="text-[10px] font-mono text-slate-500 tracking-widest uppercase">Console v5.3.0</span>
                        </div>
                        <span id="status-tag" class="text-[9px] font-black bg-slate-800 text-slate-400 px-4 py-1.5 rounded-full uppercase tracking-widest">Ready</span>
                    </div>
                    
                    <div id="capture-area" class="flex-grow p-10 terminal-bg overflow-y-auto relative">
                        <div id="console-output" class="relative font-mono text-slate-400 text-xs leading-relaxed whitespace-pre-wrap"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="pdf-template" class="hidden" style="padding: 50px; background-color: #ffffff; color: #1e293b; font-family: 'Helvetica', sans-serif;">
        <div style="border-bottom: 2px solid #0ea5e9; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="color: #0f172a; font-size: 22px; font-weight: bold; margin: 0; text-transform: uppercase;">CyberPyme <span style="color: #0ea5e9;">SOC Services</span></h1>
                <p style="color: #64748b; font-size: 10px; margin-top: 4px; font-weight: bold; letter-spacing: 1px;">OFFICIAL INFRASTRUCTURE AUDIT REPORT</p>
            </div>
            <div style="text-align: right;">
                <div style="background: #0ea5e9; color: white; padding: 5px 12px; border-radius: 4px; font-size: 10px; font-weight: bold; display: inline-block;">CONFIDENTIAL</div>
                <p id="pdf-date" style="font-size: 11px; color: #1e293b; margin-top: 8px; font-weight: bold;"></p>
            </div>
        </div>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
            <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 4px 0; color: #475569; font-weight: bold; width: 30%;">Assessment Target:</td>
                    <td id="pdf-target" style="padding: 4px 0; color: #0ea5e9; font-family: 'Courier New', monospace; font-weight: bold;"></td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #475569; font-weight: bold;">Audit Level:</td>
                    <td style="padding: 4px 0; color: #1e293b;">G12 Advanced Perimeter Scan</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #475569; font-weight: bold;">Integrity Status:</td>
                    <td style="padding: 4px 0; color: #10b981; font-weight: bold;">Verified by G12 Kernel</td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 35px;">
            <h2 style="font-size: 14px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px;">1. Executive Summary</h2>
            <p style="font-size: 11px; color: #334155; line-height: 1.6;">
                This technical report summarizes the perimeter security audit conducted by the CyberPyme SOC platform. 
                The objective is to evaluate exposed services and public data associated with the network target 
                to mitigate potential cyber threats and unauthorized access points.
            </p>
        </div>

        <div>
            <h2 style="font-size: 14px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 12px;">2. Technical Audit Details</h2>
            <div style="background: #0f172a; border-radius: 6px; padding: 20px; border: 1px solid #1e293b;">
                <pre id="pdf-console-content" style="font-family: 'Courier New', monospace; font-size: 9px; color: #38bdf8; white-space: pre-wrap; margin: 0; line-height: 1.4;"></pre>
            </div>
        </div>

        <div style="position: absolute; bottom: 50px; left: 50px; right: 50px; border-top: 1px solid #e2e8f0; padding-top: 15px; display: flex; justify-content: space-between;">
            <p style="font-size: 8px; color: #94a3b8; margin: 0;">REPORT ID: CP-G12-SOC-2026</p>
            <p style="font-size: 8px; color: #94a3b8; margin: 0;">© 2026 CyberPyme SOC - Confidential</p>
        </div>
    </div>

    <footer class="border-t border-slate-800/50 bg-slate-900/30 py-8 text-center text-[10px] font-bold text-slate-600 uppercase tracking-widest">
        CYBERPYME SOC G12 AUDITOR &copy; 2026
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/scan.js"></script>
</body>
</html>
