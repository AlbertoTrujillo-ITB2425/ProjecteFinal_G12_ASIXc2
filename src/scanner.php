<?php
/**
 * CYBERPYME SOC v6.0.0 - AI ENHANCED AUDIT CONSOLE
 * Final Production Version: Optimized for Qwen2.5-Mini & AWS Deployment.
 */
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: upgrade-insecure-requests"); // Evita problemes de contingut mixt en AWS
?>
<!DOCTYPE html>
<html lang="ca" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOC Auditor | CyberPYME AI</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        .terminal-bg { background-color: #010409; }
        .ai-glow { box-shadow: 0 0 25px rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); }
        .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        .glass-panel { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.5rem; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-950 text-slate-200 selection:bg-sky-500/30">

    <div class="grid-overlay fixed inset-0 pointer-events-none opacity-10"></div>

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
                            <span class="text-[9px] font-bold text-slate-500 tracking-[0.2em] uppercase">G12 AI-AUDIT CONSOLE</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden md:block text-[10px] font-mono text-slate-500">SYSTEM STATUS: <span class="text-emerald-500">OPTIMIZED</span></span>
            </div>
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
                            <label class="block text-[9px] uppercase text-slate-500 font-black mb-2 tracking-widest">Target Host</label>
                            <input type="text" id="target" placeholder="192.168.1.1 / domain.com" 
                                class="w-full bg-black/40 border border-slate-800 rounded-xl p-4 text-sm font-mono text-sky-400 outline-none focus:border-sky-500/50 transition-all">
                        </div>

                        <div>
                            <label class="block text-[9px] uppercase text-slate-500 font-black mb-2 tracking-widest">Bulk Import (.txt)</label>
                            <div class="relative group">
                                <input type="file" id="file-input" accept=".txt" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div id="file-status" class="w-full bg-black/20 border-2 border-dashed border-slate-800 rounded-xl p-4 text-center group-hover:border-sky-500/50 transition-all">
                                    <i class="fas fa-file-upload text-slate-600 mb-1 block text-lg"></i>
                                    <span class="text-[9px] text-slate-500 font-bold uppercase">Import hosts file</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[9px] uppercase text-slate-500 font-black mb-2 tracking-widest">Scan Profile</label>
                            <select id="type" class="w-full bg-black/40 border border-slate-800 rounded-xl p-4 text-sm font-mono text-slate-300 outline-none focus:border-sky-500/50 cursor-pointer appearance-none">
                                <option value="quick">⚡ NMAP: Quick Scan</option>
                                <option value="full">🛡️ NMAP: Vulnerability Scripting</option>
                                <option value="shodan">🔍 SHODAN: External Intelligence</option>
                            </select>
                        </div>

                        <div class="pt-4">
                            <button id="btn-run" onclick="runAudit()" class="group w-full bg-sky-600 hover:bg-sky-500 text-white font-black py-5 rounded-xl transition-all uppercase tracking-widest text-xs shadow-lg shadow-sky-600/20 flex items-center justify-center gap-3">
                                <i class="fas fa-bolt group-hover:animate-bounce"></i> Run Audit
                            </button>
                        </div>
                    </div>
                </div>

                <div id="threat-intel" class="hidden glass-panel p-8 border-red-500/10 bg-red-500/[0.02] animate-fade-in">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-[9px] font-black text-red-400 uppercase tracking-widest">Risk Level</h4>
                        <i class="fas fa-shield-virus text-red-500"></i>
                    </div>
                    <div class="flex items-end justify-between mb-3">
                        <div class="text-4xl font-black text-white" id="score-val">0<span class="text-xs text-slate-500">/100</span></div>
                        <span class="text-[9px] text-red-400 font-bold uppercase tracking-tighter">AI Estimated</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div id="score-bar" class="h-full bg-red-500 w-0 transition-all duration-1000"></div>
                    </div>
                </div>

                <button id="btn-pdf" onclick="exportToPDF()" disabled 
                    class="w-full p-5 flex items-center justify-center gap-3 text-slate-600 border-2 border-dashed border-slate-800 rounded-2xl transition-all cursor-not-allowed uppercase font-black text-[10px] tracking-widest">
                    <i class="fas fa-file-contract"></i> Generate Technical PDF
                </button>
            </div>

            <div class="lg:col-span-8 flex flex-col min-h-[700px]">
                
                <div id="ai-preview-container" class="hidden mb-6 animate-fade-in">
                    <div class="glass-panel p-6 border-sky-500/30 bg-sky-500/5 ai-glow rounded-2xl">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-[10px] font-black text-sky-400 uppercase tracking-[0.2em] flex items-center gap-2">
                                <i class="fas fa-brain animate-pulse text-xs"></i> AI Security Insights (Qwen2.5-Mini)
                            </h3>
                            <span class="text-[8px] bg-sky-500/20 text-sky-300 px-2 py-1 rounded border border-sky-500/30 uppercase font-bold tracking-tighter">Neural Analysis</span>
                        </div>
                        <div id="ai-live-text" class="text-sm text-slate-300 leading-relaxed font-sans italic">
                            </div>
                    </div>
                </div>

                <div id="output-wrapper" class="flex-grow flex flex-col border border-white/5 bg-slate-900/40 rounded-3xl overflow-hidden shadow-2xl">
                    <div class="bg-slate-900/90 px-8 py-5 border-b border-white/5 flex justify-between items-center">
                        <div class="flex items-center gap-6">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500/40"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/40"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/40"></div>
                            </div>
                            <span class="text-[10px] font-mono text-slate-500 tracking-widest uppercase tracking-widest">Terminal Output</span>
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

    <div id="pdf-template" class="hidden" style="padding: 40px; background: white; color: #1e293b; font-family: Arial, sans-serif;">
        <div style="border-bottom: 2px solid #0ea5e9; padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin:0; font-size: 24px; color: #0f172a; font-weight: 900;">CYBERPYME <span style="color:#0ea5e9">SOC</span></h1>
                <p style="margin:0; font-size: 10px; font-weight: bold; color: #64748b; letter-spacing: 1px;">AI-POWERED INFRASTRUCTURE AUDIT REPORT</p>
            </div>
            <div style="text-align: right;">
                <p id="pdf-date" style="margin:0; font-size: 10px;"></p>
                <span style="font-size: 8px; background: #0ea5e9; color: white; padding: 3px 8px; border-radius: 4px; font-weight: bold;">RESTRICTED ACCESS</span>
            </div>
        </div>

        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 25px; font-size: 11px; border: 1px solid #e2e8f0;">
            <strong style="color:#0f172a">Assessment Target:</strong> <span id="pdf-target" style="color:#0ea5e9; font-weight: bold;"></span><br>
            <strong style="color:#0f172a">Intelligence Engine:</strong> Qwen2.5 Neural Security Expert<br>
            <strong style="color:#0f172a">Audit Status:</strong> <span style="color:#10b981">Verified by CyberPyme SOC</span>
        </div>

        <div style="margin-bottom: 30px;">
            <h2 style="font-size: 14px; color: #0f172a; border-left: 4px solid #0ea5e9; padding-left: 12px; margin-bottom: 15px;">1. EXECUTIVE VULNERABILITY SUMMARY</h2>
            <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 20px; border-radius: 8px; font-size: 11px; line-height: 1.6; color: #0369a1; font-style: italic;">
                <div id="pdf-ai-content">Awaiting intelligence generation...</div>
            </div>
        </div>

        <div>
            <h2 style="font-size: 14px; color: #0f172a; border-left: 4px solid #64748b; padding-left: 12px; margin-bottom: 15px;">2. RAW TECHNICAL AUDIT DATA</h2>
            <div style="background: #0f172a; padding: 20px; border-radius: 8px; border: 1px solid #1e293b;">
                <pre id="pdf-console-content" style="margin:0; font-family: 'Courier New', monospace; font-size: 9px; color: #38bdf8; white-space: pre-wrap; line-height: 1.4;"></pre>
            </div>
        </div>

        <div style="margin-top: 50px; border-top: 1px solid #e2e8f0; padding-top: 15px; font-size: 8px; color: #94a3b8; text-align: center; font-weight: bold;">
            © 2026 CYBERPYME SOC SOLUTIONS - CRYPTOGRAPHICALLY SIGNED DOCUMENT
        </div>
    </div>

    <footer class="border-t border-slate-800/50 bg-slate-900/30 py-8 text-center text-[10px] font-bold text-slate-600 uppercase tracking-[0.4em]">
        CYBERPYME SOC G12 AUDITOR &copy; 2026 | ALL RIGHTS RESERVED
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/scan.js"></script>
</body>
</html>
