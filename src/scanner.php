<?php
/**
 * CYBERPYME SOC v6.7.0 - FULL AI AUDIT CONSOLE
 * Final Production Version: Incluye Multi-target, Bulk Import y PDF Export (4 Pages Enterprise Style).
 */
session_start();

// Configuración de Seguridad
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: upgrade-insecure-requests");

// (Opcional) Protección de ruta:
// if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="ca" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOC Auditor | CyberPYME AI</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono:wght@400;700&display=swap');

        :root { --accent: #0ea5e9; --bg-dark: #020617; }
        body { background-color: var(--bg-dark); color: #e2e8f0; font-family: 'Inter', sans-serif; }
        
        .terminal-font { font-family: 'JetBrains Mono', monospace; }
        .glass-panel { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.05); border-radius: 1.5rem; }
        .ai-glow { box-shadow: 0 0 25px rgba(14, 165, 233, 0.15); border: 1px solid rgba(14, 165, 233, 0.3); }
        
        /* Efecto de rejilla de fondo */
        .grid-overlay {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.03) 1px, transparent 0);
            background-size: 30px 30px;
        }

        /* Animaciones */
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }

        .status-pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

        /* ESTILOS ESPECÍFICOS PARA EL PDF (Enterprise Style) */
        .pdf-page {
            width: 100%;
            min-height: 297mm; /* A4 Height */
            background: white;
            color: #000;
            padding: 40px;
            font-family: 'Arial', sans-serif;
            position: relative;
            box-sizing: border-box;
        }
        .page-break { page-break-before: always; }
        .pdf-header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
        .pdf-title { font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .pdf-subtitle { font-size: 10px; color: #555; letter-spacing: 2px; text-transform: uppercase; }
        .pdf-section-title { background: #eee; padding: 5px 10px; font-weight: bold; font-size: 14px; border-left: 5px solid #333; margin-top: 20px; margin-bottom: 10px; }
        .pdf-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 15px; }
        .pdf-table th, .pdf-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .pdf-table th { background-color: #f9f9f9; font-weight: bold; }
        .pdf-footer { position: absolute; bottom: 20px; left: 40px; right: 40px; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
        .pdf-logo-placeholder { width: 100px; height: 50px; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #999; border: 1px dashed #ccc; }
    </style>
</head>
<body class="min-h-screen flex flex-col relative overflow-x-hidden">

    <div class="grid-overlay fixed inset-0 pointer-events-none"></div>

    <div class="relative z-[100]">
        <?php include 'includes/header.php'; ?>
    </div>

    <main class="max-w-7xl mx-auto px-6 py-10 flex-grow w-full relative z-10">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic text-white">
                    Audit <span class="text-sky-500">Console.</span>
                </h1>
                <div class="flex items-center gap-3 mt-2">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-mono text-slate-500 tracking-[0.3em] uppercase" data-i18n="ai_status">Neural Engine: Qwen2.5-Mini Enabled</span>
                </div>
            </div>
            
            <div class="flex gap-4">
                <div id="status-tag" class="bg-slate-900 border border-slate-800 px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400">
                    System Ready
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4 space-y-6">
                
                <div class="glass-panel p-8 border-t-2 border-sky-500">
                    <h3 class="text-[10px] font-black mb-8 text-sky-400 uppercase tracking-[0.3em] flex items-center gap-3">
                        <i class="fas fa-microchip text-xs"></i> <span data-i18n="audit_params">Audit Parameters</span>
                    </h3>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[9px] uppercase text-slate-500 font-black mb-2 tracking-widest" data-i18n="target_label">Target Host</label>
                            <input type="text" id="target" placeholder="192.168.1.1 / itb.cat" 
                                class="w-full bg-black/40 border border-slate-800 rounded-xl p-4 text-sm font-mono text-sky-400 outline-none focus:border-sky-500/50 transition-all">
                        </div>

                        <div>
                            <label class="block text-[9px] uppercase text-slate-500 font-black mb-2 tracking-widest" data-i18n="bulk_import">Bulk Import (.txt)</label>
                            <div class="relative group">
                                <input type="file" id="file-input" accept=".txt" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div id="file-status" class="w-full bg-black/20 border-2 border-dashed border-slate-800 rounded-xl p-4 text-center group-hover:border-sky-500/50 transition-all">
                                    <i class="fas fa-file-upload text-slate-600 mb-1 block text-lg"></i>
                                    <span class="text-[9px] text-slate-500 font-bold uppercase" data-i18n="import_txt">Import hosts file</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[9px] uppercase text-slate-500 font-black mb-2 tracking-widest" data-i18n="scan_profile">Scan Profile</label>
                            <select id="type" class="w-full bg-black/40 border border-slate-800 rounded-xl p-4 text-sm font-mono text-slate-300 outline-none focus:border-sky-500/50 cursor-pointer appearance-none">
                                <option value="quick" data-i18n="opt_quick">⚡ NMAP: Quick Scan</option>
                                <option value="full" data-i18n="opt_full">🛡️ NMAP: Vulnerability Scripting</option>
                                <option value="shodan" data-i18n="opt_shodan">🔍 SHODAN: External Intelligence</option>
                            </select>
                        </div>

                        <button id="btn-run" onclick="runAudit()" class="group w-full bg-sky-600 hover:bg-sky-500 text-white font-black py-5 rounded-xl transition-all uppercase tracking-widest text-xs shadow-lg shadow-sky-600/20 flex items-center justify-center gap-3">
                            <i class="fas fa-bolt group-hover:animate-bounce"></i> <span data-i18n="run_audit">Run Audit</span>
                        </button>
                    </div>
                </div>

                <div id="threat-intel" class="hidden glass-panel p-8 border-red-500/10 bg-red-500/[0.02] animate-fade-in">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-[9px] font-black text-red-400 uppercase tracking-widest" data-i18n="risk_level">Risk Level</h4>
                        <i class="fas fa-shield-virus text-red-500"></i>
                    </div>
                    <div class="flex items-end justify-between mb-3">
                        <div class="text-4xl font-black text-white" id="score-val">0<span class="text-xs text-slate-500">/100</span></div>
                        <span class="text-[9px] text-red-400 font-bold uppercase tracking-tighter">AI Analysis</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div id="score-bar" class="h-full bg-red-500 w-0 transition-all duration-1000"></div>
                    </div>
                </div>

                <button id="btn-pdf" onclick="exportToPDF()" disabled 
                    class="w-full p-5 flex items-center justify-center gap-3 text-slate-600 border-2 border-dashed border-slate-800 rounded-2xl transition-all cursor-not-allowed uppercase font-black text-[10px] tracking-widest">
                    <i class="fas fa-file-contract"></i> <span data-i18n="gen_pdf">Generate Technical PDF</span>
                </button>
            </div>

            <div class="lg:col-span-8 flex flex-col gap-6">
                
                <div id="ai-preview-container" class="hidden animate-fade-in">
                    <div class="glass-panel p-6 border-sky-500/30 bg-sky-500/5 ai-glow rounded-2xl">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-[10px] font-black text-sky-400 uppercase tracking-[0.2em] flex items-center gap-2">
                                <i class="fas fa-brain animate-pulse text-xs"></i> <span data-i18n="ai_insights">AI Security Insights (Qwen2.5)</span>
                            </h3>
                            <span class="text-[8px] bg-sky-500/20 text-sky-300 px-2 py-1 rounded border border-sky-500/30 uppercase font-bold tracking-tighter">Neural Analysis</span>
                        </div>
                        <div id="ai-live-text" class="text-sm text-slate-300 leading-relaxed font-sans italic">
                            </div>
                    </div>
                </div>

                <div id="output-wrapper" class="flex-grow flex flex-col border border-white/5 bg-slate-900/40 rounded-3xl overflow-hidden shadow-2xl min-h-[500px]">
                    <div class="bg-slate-900/90 px-8 py-4 border-b border-white/5 flex justify-between items-center">
                        <div class="flex items-center gap-6">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500/40"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/40"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/40"></div>
                            </div>
                            <span class="text-[10px] terminal-font text-slate-500 tracking-widest uppercase" data-i18n="terminal_title">Terminal Output</span>
                        </div>
                        <div id="scan-progress" class="hidden text-[9px] font-mono text-sky-500">
                            PROCESANDO... <span id="progress-percent">0%</span>
                        </div>
                    </div>
                    
                    <div id="capture-area" class="flex-grow p-10 bg-[#010409] overflow-y-auto">
                        <div id="console-output" class="terminal-font text-sky-500/90 text-xs leading-relaxed whitespace-pre-wrap"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- 
        PLANTILLA PDF DE 4 PÁGINAS 
        Estilo: Enterprise / Corporativo
        Estructura oculta que html2pdf renderizará
    -->
    <div id="pdf-template" class="hidden">
        
        <!-- PÁGINA 1: INFORMACIÓN GENERAL -->
        <div class="pdf-page">
            <div class="pdf-header">
                <div>
                    <h1 class="pdf-title">REPORTE DE AUDITORIA</h1>
                    <div class="pdf-subtitle">SEGURIDAD INFRAESTRUCTURA Y RED</div>
                </div>
                <div class="text-right">
                    <div class="pdf-logo-placeholder">LOGO EMPRESA</div>
                </div>
            </div>

            <div class="pdf-section-title">1. INFORMACIÓN GENERAL</div>
            
            <table class="pdf-table">
                <tr>
                    <td width="20%"><strong>Entidad Auditada</strong></td>
                    <td width="30%" id="pdf-client">CYBERPYME CLIENT</td>
                    <td width="20%"><strong>Fecha</strong></td>
                    <td width="30%" id="pdf-date">DD/MM/YYYY</td>
                </tr>
                <tr>
                    <td><strong>Dirección</strong></td>
                    <td>C/ Tecnología 123, Digital City</td>
                    <td><strong>Teléfono</strong></td>
                    <td>+34 900 000 000</td>
                </tr>
            </table>

            <div class="pdf-section-title">1.1 DATOS DE LA AUDITORÍA</div>
            <table class="pdf-table">
                <tr>
                    <td width="25%"><strong>Objetivo</strong></td>
                    <td colspan="3">Identificación de vulnerabilidades y puertos abiertos en los activos de red seleccionados mediante análisis pasivo y activo.</td>
                </tr>
                <tr>
                    <td><strong>Alcance</strong></td>
                    <td colspan="3">Análisis externo de superficies de ataque. Objetivo: <span id="pdf-scope-target" style="color:#d32f2f; font-weight:bold;">192.168.1.1</span></td>
                </tr>
                <tr>
                    <td><strong>Criterios</strong></td>
                    <td colspan="3">OWASP Top 10, NIST Cybersecurity Framework, ISO 27001.</td>
                </tr>
                <tr>
                    <td><strong>Tipo</strong></td>
                    <td>Auditoría Técnica Externa (NMAP / Shodan)</td>
                    <td><strong>Nivel</strong></td>
                    <td>Básico / Intermedio</td>
                </tr>
            </table>

            <div class="pdf-section-title">1.2 EQUIPO AUDITOR</div>
            <table class="pdf-table">
                <tr>
                    <td><strong>Auditado Por</strong></td>
                    <td>SOC Automation Team (AI Powered)</td>
                    <td><strong>Aprobado Por</strong></td>
                    <td>CISO - Chief Information Security Officer</td>
                </tr>
            </table>

            <div class="pdf-footer">
                Documento Confidencial - Página 1 de 4
            </div>
        </div>

        <!-- PÁGINA 2: REGISTROS Y FORTALEZAS -->
        <div class="pdf-page page-break">
            <div class="pdf-header">
                <div>
                    <h1 class="pdf-title">REPORTE DE AUDITORIA</h1>
                    <div class="pdf-subtitle">SEGURIDAD INFRAESTRUCTURA Y RED</div>
                </div>
            </div>

            <div class="pdf-section-title">2. REGISTROS DEL AUDITOR</div>

            <div style="margin-bottom: 15px;">
                <strong>2.1 ACTIVIDADES DESARROLLADAS</strong>
                <ul style="font-size: 10px; margin-left: 20px; margin-top: 5px;">
                    <li>Recolección de inteligencia de fuentes públicas (Shodan / Censys).</li>
                    <li>Ejecución de escaneo de puertos y servicios (NMAP).</li>
                    <li>Detección de sistema operativo y versiones de servicios.</li>
                    <li>Análisis de vulnerabilidades mediante scripts NSE.</li>
                    <li>Generación de informe de conclusiones mediante Inteligencia Artificial.</li>
                </ul>
            </div>

            <div class="pdf-section-title">2.2 PROCESOS AUDITADOS</div>
            <table class="pdf-table">
                <thead>
                    <tr>
                        <th>Proceso</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gestión de Red</td>
                        <td>Configuración de Firewalls y Puertos Externos</td>
                        <td>Auditado</td>
                    </tr>
                    <tr>
                        <td>Seguridad Perimetral</td>
                        <td>Exposición de Servicios Críticos</td>
                        <td>Auditado</td>
                    </tr>
                    <tr>
                        <td>Hardening</td>
                        <td>Versionado y Parcheo de Servicios</td>
                        <td>Auditado</td>
                    </tr>
                </tbody>
            </table>

            <div class="pdf-section-title">2.3 FORTALEZAS</div>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px; font-size: 10px; margin-bottom: 15px;">
                <p><strong>2.3.1</strong> Se ha completado el ciclo de auditoría con éxito sin interrupciones de servicio críticas.</p>
                <p style="margin-top:5px;"><strong>2.3.2</strong> La herramienta de análisis ha permitido identificar la superficie de ataque en tiempo récord.</p>
            </div>

            <div class="pdf-footer">
                Documento Confidencial - Página 2 de 4
            </div>
        </div>

        <!-- PÁGINA 3: HALLAZGOS Y DATOS TÉCNICOS -->
        <div class="pdf-page page-break">
            <div class="pdf-header">
                <div>
                    <h1 class="pdf-title">REPORTE DE AUDITORIA</h1>
                    <div class="pdf-subtitle">SEGURIDAD INFRAESTRUCTURA Y RED</div>
                </div>
            </div>

            <div class="pdf-section-title">2.4 HALLAZGOS</div>

            <!-- Tabla dinámica de hallazgos (simulada con los logs) -->
            <table class="pdf-table" style="font-size: 9px;">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Tipo</th>
                        <th width="15%">Servicio/Puerto</th>
                        <th width="70%">Detalles Técnicos (Log)</th>
                    </tr>
                </thead>
                <tbody id="pdf-findings-body">
                    <!-- Se llenará con JS -->
                </tbody>
            </table>

            <div class="pdf-section-title">2.4.2 OBSERVACIONES GENERALES</div>
            <p style="font-size: 10px; text-align: justify;">
                Se recomienda revisar los puertos detectados en la tabla superior. Cualquier servicio no esencial expuesto a la red pública representa un vector de entrada potencial para atacantes. Verifique que todos los servicios detectados cuenten con los últimos parches de seguridad aplicados.
            </p>

            <div class="pdf-footer">
                Documento Confidencial - Página 3 de 4
            </div>
        </div>

        <!-- PÁGINA 4: CONCLUSIONES E IA -->
        <div class="pdf-page page-break">
            <div class="pdf-header">
                <div>
                    <h1 class="pdf-title">REPORTE DE AUDITORIA</h1>
                    <div class="pdf-subtitle">SEGURIDAD INFRAESTRUCTURA Y RED</div>
                </div>
            </div>

            <div class="pdf-section-title">3. CONCLUSIONES</div>

            <div style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; min-height: 400px;">
                <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 5px;">Análisis por IA (Qwen2.5)</h4>
                <div id="pdf-ai-content" style="font-size: 11px; line-height: 1.6; color: #333; white-space: pre-wrap;">
                    <!-- Contenido IA aquí -->
                </div>
            </div>

            <div style="margin-top: auto;">
                <table class="pdf-table" style="border: none;">
                    <tr>
                        <td style="border: none; width: 50%;">
                            <strong>Cliente:</strong> ___________________________
                        </td>
                        <td style="border: none; width: 50%;">
                            <strong>Auditor:</strong> ___________________________
                        </td>
                    </tr>
                </table>
            </div>

            <div class="pdf-footer">
                Documento Confidencial - Página 4 de 4
            </div>
        </div>

    </div>

    <footer class="py-8 text-center text-[10px] font-bold text-slate-600 uppercase tracking-[0.4em] border-t border-white/5">
        CYBERPYME SOC &copy; 2026 | ALL RIGHTS RESERVED
    </footer>

    <script src="assets/js/languages.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        /**
         * Lógica del Escáner SOC v6.7.0
         */
        window.currentAIReport = "";
        let cleanLogForAI = "";

        // Manejo de archivo TXT
        document.getElementById('file-input').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : "Import hosts file";
            document.getElementById('file-status').querySelector('span').innerText = fileName;
            document.getElementById('target').disabled = !!e.target.files[0];
            if(!!e.target.files[0]) document.getElementById('target').value = "";
        });

        function updateStatus(text, colorClass) {
            const tag = document.getElementById('status-tag');
            tag.innerText = text;
            tag.className = `px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest ${colorClass}`;
        }

        function appendLog(text, isError = false) {
            const consoleOut = document.getElementById('console-output');
            const entry = document.createElement('div');
            entry.className = isError ? "text-red-400 mb-1" : "mb-1";
            entry.innerText = text;
            consoleOut.appendChild(entry);
            document.getElementById('capture-area').scrollTop = document.getElementById('capture-area').scrollHeight;
        }

        async function runAudit() {
            const targetInput = document.getElementById('target');
            const fileInput = document.getElementById('file-input');
            const type = document.getElementById('type').value;
            const btn = document.getElementById('btn-run');

            // Reset UI
            document.getElementById('console-output').innerHTML = "";
            document.getElementById('ai-preview-container').classList.add('hidden');
            document.getElementById('threat-intel').classList.add('hidden');
            cleanLogForAI = "";
            
            let targets = [];
            if (fileInput.files.length > 0) {
                const text = await fileInput.files[0].text();
                targets = text.split(/\r?\n/).filter(line => line.trim() !== "");
            } else if (targetInput.value.trim() !== "") {
                targets = [targetInput.value.trim()];
            } else {
                alert("Please provide a target.");
                return;
            }

            // UI State: Loading
            btn.disabled = true;
            btn.innerHTML = `<i class="fas fa-circle-notch animate-spin"></i> Audit in Progress...`;
            updateStatus("Scanning", "bg-sky-500 text-white animate-pulse");
            document.getElementById('scan-progress').classList.remove('hidden');

            // Procesar cada target
            for (let i = 0; i < targets.length; i++) {
                const host = targets[i];
                const percent = Math.round(((i + 1) / targets.length) * 100);
                document.getElementById('progress-percent').innerText = percent + "%";
                
                appendLog(`\n[SYSTEM] Lanzando auditoría sobre: ${host}...`);
                
                try {
                    const response = await fetch(`api/scan_async.php?target=${host}&type=${type}`);
                    const result = await response.text();
                    
                    if (result.includes('"error"')) {
                        appendLog(`[ERROR] ${host}: Objetivo no aceptado por el servidor.`, true);
                    } else {
                        appendLog(result);
                        const relevant = result.split('\n').filter(l => l.includes('open') || l.includes('vulnerable') || l.includes('Nmap scan')).join('\n');
                        cleanLogForAI += `\nHOST: ${host}\n${relevant}\n`;
                    }
                } catch (err) {
                    appendLog(`[FATAL] Error de conexión en ${host}`, true);
                }
            }

            // FINALIZAR ESCANEO Y PASAR A IA
            if (cleanLogForAI.length > 10) {
                await requestAIAnalysis(cleanLogForAI);
            } else {
                appendLog("\n[WARN] No se detectaron datos relevantes para el análisis IA.", true);
            }

            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-bolt"></i> Run Audit`;
            updateStatus("Completed", "bg-emerald-500/20 text-emerald-400 border border-emerald-500/50");
            document.getElementById('scan-progress').classList.add('hidden');
        }

        async function requestAIAnalysis(data) {
            const aiContainer = document.getElementById('ai-preview-container');
            const aiText = document.getElementById('ai-live-text');
            const pdfBtn = document.getElementById('btn-pdf');
            
            aiContainer.classList.remove('hidden');
            aiText.innerHTML = `<span class="status-pulse">Invocando Qwen2.5-Mini para análisis de vulnerabilidades...</span>`;
            appendLog("\n[AI] Analizando vectores de ataque...");

            try {
                const response = await fetch('api/ai_analysis.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ scan_data: data })
                });

                const json = await response.json();
                
                if (json.response) {
                    window.currentAIReport = json.response;
                    aiText.innerHTML = json.response.replace(/\n/g, '<br>');
                    
                    document.getElementById('threat-intel').classList.remove('hidden');
                    const score = json.response.toLowerCase().includes('crítica') || json.response.toLowerCase().includes('high') ? 85 : 35;
                    document.getElementById('score-val').innerHTML = `${score}<span class="text-xs text-slate-500">/100</span>`;
                    document.getElementById('score-bar').style.width = score + "%";

                    pdfBtn.disabled = false;
                    pdfBtn.className = "w-full p-5 flex items-center justify-center gap-3 text-sky-400 border-2 border-sky-500/50 rounded-2xl transition-all cursor-pointer bg-sky-500/5 ai-glow font-black text-[10px] tracking-widest";
                    
                    confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 } });
                }
            } catch (err) {
                aiText.innerHTML = `<span class="text-red-400">Error de conexión con el motor IA de Ollama.</span>`;
            }
        }

        function exportToPDF() {
            const target = document.getElementById('target').value || "Multiple_Hosts";
            const dateStr = new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString();
            const logs = document.getElementById('console-output').innerText;

            // Rellenar Página 1
            document.getElementById('pdf-client').innerText = target.toUpperCase();
            document.getElementById('pdf-date').innerText = dateStr;
            document.getElementById('pdf-scope-target').innerText = target;

            // Rellenar Página 3 (Parsear logs para la tabla)
            const tbody = document.getElementById('pdf-findings-body');
            tbody.innerHTML = "";
            const lines = logs.split('\n');
            let counter = 1;

            lines.forEach(line => {
                if(line.trim().length > 0 && (line.includes('/tcp') || line.includes('open') || line.includes('vuln'))) {
                    const tr = document.createElement('tr');
                    // Intentar extraer puerto o poner INFO
                    let port = "N/A";
                    let service = "Info";
                    const match = line.match(/(\d+)\/tcp\s+(\w+)/);
                    if(match) {
                        port = match[1];
                        service = match[2];
                    }

                    tr.innerHTML = `
                        <td>${counter++}</td>
                        <td>INFO</td>
                        <td>${port} / ${service}</td>
                        <td>${line.replace(/</g, '&lt;')}</td>
                    `;
                    tbody.appendChild(tr);
                }
            });
            // Si no hay puertos específicos, poner el log completo en una fila
            if(tbody.innerHTML === "") {
                 const tr = document.createElement('tr');
                 tr.innerHTML = `<td colspan="4" style="white-space: pre-wrap; font-family: monospace;">${logs.replace(/</g, '&lt;')}</td>`;
                 tbody.appendChild(tr);
            }

            // Rellenar Página 4
            document.getElementById('pdf-ai-content').innerText = window.currentAIReport;

            const element = document.getElementById('pdf-template');
            element.classList.remove('hidden');

            const opt = {
                margin: 0, // Sin margen adicional porque el padding está en el CSS
                filename: `SOC_Report_${target.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                element.classList.add('hidden');
            });
        }
    </script>
</body>
</html>
