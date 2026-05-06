<?php
/**
 * CYBERPYME SOC v6.7.0 - FULL AI AUDIT CONSOLE
 * Final Production Version: Multi-target, Bulk Import y PDF Export (Enterprise Style).
 */
session_start();

// Configuración de Seguridad
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: upgrade-insecure-requests");

// BLOQUEO DE SEGURIDAD (Activado)
if (!isset($_SESSION['user_id'])) { 
    header('Location: ../../auth.php'); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOC Auditor | CyberPYME AI</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');

        .terminal-font { font-family: 'JetBrains Mono', monospace; }
        .ai-glow { box-shadow: 0 0 25px rgba(14, 165, 233, 0.15); }

        /* Efecto de rejilla de fondo */
        .grid-overlay {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.03) 1px, transparent 0);
            background-size: 30px 30px;
        }

        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .status-pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

        /* ESTILOS ESPECÍFICOS PARA EL PDF (Enterprise Style) */
        .pdf-page { width: 100%; min-height: 297mm; background: white; color: #000; padding: 40px; font-family: 'Arial', sans-serif; position: relative; box-sizing: border-box; }
        .page-break { page-break-before: always; }
        .pdf-header { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
        .pdf-title { font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .pdf-subtitle { font-size: 10px; color: #555; letter-spacing: 2px; text-transform: uppercase; }
        .pdf-section-title { background: #eee; padding: 5px 10px; font-weight: bold; font-size: 14px; border-left: 5px solid #333; margin-top: 20px; margin-bottom: 10px; }
        .pdf-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 15px; }
        .pdf-table th, .pdf-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .pdf-table th { background-color: #f9f9f9; font-weight: bold; }
        .pdf-footer { position: absolute; bottom: 20px; left: 40px; right: 40px; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body class="min-h-screen flex flex-col relative overflow-x-hidden text-slate-200">

    <div class="grid-overlay fixed inset-0 pointer-events-none"></div>

    <div class="relative z-[100]">
        <?php include '../../includes/header.php'; ?>
    </div>

    <main class="max-w-7xl mx-auto px-6 py-10 flex-grow w-full relative z-10">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-6">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic">
                    Audit <span class="text-blue-500">Console.</span>
                </h1>
                <div class="flex items-center gap-3 mt-2">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-mono text-muted tracking-[0.3em] uppercase" data-i18n="ai_status">Neural Engine: Qwen2.5-Mini Enabled</span>
                </div>
            </div>

            <div class="flex gap-4">
                <div id="status-tag" class="bg-nav border border-glass px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest text-muted shadow-lg">
                    System Ready
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-4 space-y-6">

                <div class="bg-glass border-t-2 border-t-blue-500 border-x border-b border-glass rounded-2xl shadow-xl p-8">
                    <h3 class="text-[10px] font-black mb-8 text-blue-400 uppercase tracking-[0.3em] flex items-center gap-3">
                        <i class="fas fa-microchip text-xs"></i> <span data-i18n="audit_params">Audit Parameters</span>
                    </h3>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[9px] uppercase text-muted font-black mb-2 tracking-widest" data-i18n="target_label">Target Host</label>
                            <input type="text" id="target" placeholder="192.168.1.1 / itb.cat"
                                class="w-full bg-nav border border-glass rounded-xl p-4 text-sm font-mono text-blue-400 outline-none focus:border-blue-500/50 transition-all">
                        </div>

                        <div>
                            <label class="block text-[9px] uppercase text-muted font-black mb-2 tracking-widest" data-i18n="bulk_import">Bulk Import (.txt)</label>
                            <div class="relative group">
                                <input type="file" id="file-input" accept=".txt" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div id="file-status" class="w-full bg-nav border-2 border-dashed border-glass rounded-xl p-4 text-center group-hover:border-blue-500/50 transition-all">
                                    <i class="fas fa-file-upload text-muted mb-1 block text-lg"></i>
                                    <span class="text-[9px] text-muted font-bold uppercase" data-i18n="import_txt">Import hosts file</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[9px] uppercase text-muted font-black mb-2 tracking-widest" data-i18n="scan_profile">Scan Profile</label>
                            <select id="type" class="w-full bg-nav border border-glass rounded-xl p-4 text-sm font-mono text-slate-300 outline-none focus:border-blue-500/50 cursor-pointer appearance-none">
                                <option value="quick" data-i18n="opt_quick">⚡ NMAP: Quick Scan</option>
                                <option value="full" data-i18n="opt_full">🛡️ NMAP: Vulnerability Scripting</option>
                                <option value="shodan" data-i18n="opt_shodan">🔍 SHODAN: External Intelligence</option>
                            </select>
                        </div>

                        <button id="btn-run" onclick="runAudit()" class="group w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-5 rounded-xl transition-all uppercase tracking-widest text-xs shadow-lg shadow-blue-600/20 flex items-center justify-center gap-3">
                            <i class="fas fa-bolt group-hover:animate-bounce"></i> <span data-i18n="run_audit">Run Audit</span>
                        </button>
                    </div>
                </div>

                <div id="threat-intel" class="hidden bg-red-950/20 border border-red-500/20 rounded-2xl p-8 animate-fade-in shadow-lg">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-[9px] font-black text-red-400 uppercase tracking-widest" data-i18n="risk_level">Risk Level</h4>
                        <i class="fas fa-shield-virus text-red-500"></i>
                    </div>
                    <div class="flex items-end justify-between mb-3">
                        <div class="text-4xl font-black text-white" id="score-val">0<span class="text-xs text-muted">/100</span></div>
                        <span class="text-[9px] text-red-400 font-bold uppercase tracking-tighter">AI Analysis</span>
                    </div>
                    <div class="w-full h-1.5 bg-nav border border-glass rounded-full overflow-hidden">
                        <div id="score-bar" class="h-full bg-red-500 w-0 transition-all duration-1000"></div>
                    </div>
                </div>

                <button id="btn-pdf" onclick="exportToPDF()" disabled
                    class="w-full p-5 flex items-center justify-center gap-3 text-muted border-2 border-dashed border-glass rounded-2xl transition-all cursor-not-allowed uppercase font-black text-[10px] tracking-widest bg-glass">
                    <i class="fas fa-file-contract"></i> <span data-i18n="gen_pdf">Generate Technical PDF</span>
                </button>
            </div>

            <div class="lg:col-span-8 flex flex-col gap-6">

                <div id="ai-preview-container" class="hidden animate-fade-in">
                    <div class="bg-blue-900/10 border border-blue-500/30 rounded-2xl p-6 ai-glow backdrop-blur-md">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] flex items-center gap-2">
                                <i class="fas fa-brain animate-pulse text-xs"></i> <span data-i18n="ai_insights">AI Security Insights (Qwen2.5)</span>
                            </h3>
                            <span class="text-[8px] bg-blue-500/20 text-blue-300 px-2 py-1 rounded border border-blue-500/30 uppercase font-bold tracking-tighter">Neural Analysis</span>
                        </div>
                        <div id="ai-live-text" class="text-sm text-slate-300 leading-relaxed font-sans italic"></div>
                    </div>
                </div>

                <div id="output-wrapper" class="flex-grow flex flex-col bg-glass border border-glass rounded-3xl overflow-hidden shadow-2xl min-h-[500px]">
                    <div class="bg-nav px-8 py-4 border-b border-glass flex justify-between items-center">
                        <div class="flex items-center gap-6">
                            <div class="flex gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500/40"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/40"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/40"></div>
                            </div>
                            <span class="text-[10px] terminal-font text-muted tracking-widest uppercase" data-i18n="terminal_title">Terminal Output</span>
                        </div>
                        <div id="scan-progress" class="hidden text-[9px] font-mono text-blue-500">
                            PROCESANDO... <span id="progress-percent">0%</span>
                        </div>
                    </div>

                    <div id="capture-area" class="flex-grow p-10 bg-black/60 overflow-y-auto">
                        <div id="console-output" class="terminal-font text-blue-400/90 text-xs leading-relaxed whitespace-pre-wrap"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="pdf-template" class="hidden">
        <div class="pdf-page">
            <div class="pdf-header">
                <div>
                    <h1 class="pdf-title">REPORTE DE AUDITORIA</h1>
                    <div class="pdf-subtitle">SEGURIDAD INFRAESTRUCTURA Y RED</div>
                </div>
                <div class="text-right">
                    <div class="pdf-logo-placeholder">CYBERPYME SOC</div>
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
                    <td><strong>Auditor a cargo</strong></td>
                    <td><?= htmlspecialchars($_SESSION['user_name'] ?? 'SOC Automated System') ?></td>
                    <td><strong>Motor IA</strong></td>
                    <td>Qwen2.5-Mini</td>
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
            </table>

            <div class="pdf-footer">Documento Confidencial - Página 1 de 4</div>
        </div>

        <div class="pdf-page page-break">
            <div class="pdf-header">
                <div><h1 class="pdf-title">REPORTE DE AUDITORIA</h1><div class="pdf-subtitle">SEGURIDAD INFRAESTRUCTURA Y RED</div></div>
            </div>
            <div class="pdf-section-title">2. REGISTROS DEL AUDITOR</div>
            <div style="margin-bottom: 15px;">
                <strong>2.1 ACTIVIDADES DESARROLLADAS</strong>
                <ul style="font-size: 10px; margin-left: 20px; margin-top: 5px;">
                    <li>Recolección de inteligencia de fuentes públicas y escaneo activo (NMAP/Shodan).</li>
                    <li>Detección de sistema operativo y versiones de servicios expuestos.</li>
                    <li>Generación de informe de conclusiones mediante Inteligencia Artificial.</li>
                </ul>
            </div>
            <div class="pdf-footer">Documento Confidencial - Página 2 de 4</div>
        </div>

        <div class="pdf-page page-break">
            <div class="pdf-header">
                <div><h1 class="pdf-title">REPORTE DE AUDITORIA</h1><div class="pdf-subtitle">SEGURIDAD INFRAESTRUCTURA Y RED</div></div>
            </div>
            <div class="pdf-section-title">2.4 HALLAZGOS (RAW DATA)</div>
            <table class="pdf-table" style="font-size: 9px;">
                <thead>
                    <tr><th width="5%">No</th><th width="10%">Tipo</th><th width="15%">Servicio</th><th width="70%">Detalles Técnicos (Log)</th></tr>
                </thead>
                <tbody id="pdf-findings-body"></tbody>
            </table>
            <div class="pdf-footer">Documento Confidencial - Página 3 de 4</div>
        </div>

        <div class="pdf-page page-break">
            <div class="pdf-header">
                <div><h1 class="pdf-title">REPORTE DE AUDITORIA</h1><div class="pdf-subtitle">SEGURIDAD INFRAESTRUCTURA Y RED</div></div>
            </div>
            <div class="pdf-section-title">3. CONCLUSIONES IA</div>
            <div style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; min-height: 400px;">
                <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 5px;">Análisis por IA (Qwen2.5)</h4>
                <div id="pdf-ai-content" style="font-size: 11px; line-height: 1.6; color: #333; white-space: pre-wrap;"></div>
            </div>
            <div class="pdf-footer">Documento Confidencial - Página 4 de 4</div>
        </div>
    </div>

    <footer class="py-8 text-center text-[10px] font-bold text-muted uppercase tracking-[0.4em] border-t border-glass">
        CYBERPYME SOC &copy; 2026 | ALL RIGHTS RESERVED
    </footer>

    <script src="../../assets/js/languages.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <script>
        /**
         * Lógica del Escáner SOC v6.7.0 - Optimización Anti-Bloqueo
         */
        window.currentAIReport = "";
        let cleanLogForAI = "";

        document.getElementById('file-input').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : "Import hosts file";
            document.getElementById('file-status').querySelector('span').innerText = fileName;
            document.getElementById('target').disabled = !!e.target.files[0];
            if(!!e.target.files[0]) document.getElementById('target').value = "";
        });

        function updateStatus(text, colorClass) {
            const tag = document.getElementById('status-tag');
            tag.innerText = text;
            tag.className = `bg-nav border border-glass px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg ${colorClass}`;
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

            btn.disabled = true;
            btn.innerHTML = `<i class="fas fa-circle-notch animate-spin"></i> Audit in Progress...`;
            updateStatus("Scanning", "text-blue-400 animate-pulse");
            document.getElementById('scan-progress').classList.remove('hidden');

            for (let i = 0; i < targets.length; i++) {
                const host = targets[i];
                const percent = Math.round(((i + 1) / targets.length) * 100);
                document.getElementById('progress-percent').innerText = percent + "%";

                appendLog(`\n[SYSTEM] Lanzando auditoría sobre: ${host}...`);

                try {
                    const response = await fetch(`../../api/scan_async.php?target=${host}&type=${type}`);
                    const result = await response.text();

                    if (result.includes('"error"')) {
                        appendLog(`[ERROR] ${host}: Objetivo no aceptado por el servidor.`, true);
                    } else {
                        appendLog(result);
                        // Filtramos líneas relevantes para no saturar el contexto de la IA posteriormente
                        const relevant = result.split('\n').filter(l => 
                            l.includes('open') || l.includes('vulnerable') || l.includes('Nmap scan')
                        ).join('\n');
                        cleanLogForAI += `\nHOST: ${host}\n${relevant}\n`;
                    }
                } catch (err) {
                    appendLog(`[FATAL] Error de conexión en ${host}`, true);
                }
            }

            // --- DISPARADOR DE IA ---
            if (cleanLogForAI.trim().length > 10) {
                await requestAIAnalysis(cleanLogForAI);
            } else {
                appendLog("\n[WARN] No se detectaron datos relevantes para el análisis IA.", true);
            }

            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-bolt"></i> Run Audit`;
            updateStatus("Completed", "text-emerald-400");
            document.getElementById('scan-progress').classList.add('hidden');
        }

        /**
         * NUEVA LÓGICA: Petición IA con protección de tiempo
         */
        async function requestAIAnalysis(data) {
            const aiContainer = document.getElementById('ai-preview-container');
            const aiText = document.getElementById('ai-live-text');
            const pdfBtn = document.getElementById('btn-pdf');
            const threatIntel = document.getElementById('threat-intel');

            aiContainer.classList.remove('hidden');
            aiText.innerHTML = `<span class="status-pulse text-blue-400">🧠 Generando Inteligencia de Amenazas (Max 30s)...</span>`;
            appendLog("\n[AI] Analizando vectores de ataque con Qwen2.5...");

            // Creamos un controlador de aborto para el timeout en el lado del cliente también
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 35000); // 35s de gracia

            try {
                const response = await fetch('../../api/ai_analysis.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ scan_data: data }),
                    signal: controller.signal
                });

                clearTimeout(timeoutId);
                const json = await response.json();

                // Manejo de respuesta (Success o Warning del PHP)
                if (json.response) {
                    window.currentAIReport = json.response;
                    aiText.innerHTML = json.response.replace(/\n/g, '<br>');

                    // Mostrar Score basado en la respuesta
                    threatIntel.classList.remove('hidden');
                    const isCritical = /crítica|peligro|alto|critical|high|vulnerable/i.test(json.response);
                    const score = isCritical ? 85 : 30;
                    
                    document.getElementById('score-val').innerHTML = `${score}<span class="text-xs text-muted">/100</span>`;
                    document.getElementById('score-bar').style.width = score + "%";
                    document.getElementById('score-bar').className = `h-full transition-all duration-1000 ${score > 70 ? 'bg-red-500' : 'bg-amber-500'}`;

                    // Habilitar PDF con estilo activo
                    pdfBtn.disabled = false;
                    pdfBtn.className = "w-full p-5 flex items-center justify-center gap-3 text-blue-400 border-2 border-blue-500/50 rounded-2xl transition-all cursor-pointer bg-blue-900/10 ai-glow font-black text-[10px] tracking-widest";

                    if(score > 70) confetti({ particleCount: 100, spread: 70, origin: { y: 0.8 }, colors: ['#ef4444', '#ffffff'] });
                }
            } catch (err) {
                // Si el controlador abortó o hubo error de red
                const msg = err.name === 'AbortError' 
                    ? "⚠️ Tiempo excedido: Ollama está procesando demasiada carga. Revise el log manual." 
                    : "⚠️ Error de enlace: No se pudo contactar con el motor IA.";
                
                aiText.innerHTML = `<span class="text-amber-400 italic">${msg}</span>`;
                window.currentAIReport = "Análisis automático no disponible temporalmente. Reporte basado en logs técnicos.";
                
                // Aun así permitimos el PDF pero con el aviso
                pdfBtn.disabled = false;
                pdfBtn.classList.replace('text-muted', 'text-amber-400');
                pdfBtn.style.cursor = "pointer";
            }
        }

        function exportToPDF() {
            const target = document.getElementById('target').value || "Multiple_Hosts";
            const dateStr = new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString();
            const logs = document.getElementById('console-output').innerText;

            document.getElementById('pdf-client').innerText = target.toUpperCase();
            document.getElementById('pdf-date').innerText = dateStr;
            document.getElementById('pdf-scope-target').innerText = target;

            const tbody = document.getElementById('pdf-findings-body');
            tbody.innerHTML = "";
            const lines = logs.split('\n');
            let counter = 1;

            lines.forEach(line => {
                if(line.trim().length > 0 && (line.includes('/tcp') || line.includes('open') || line.includes('vuln'))) {
                    const tr = document.createElement('tr');
                    let port = "N/A";
                    let service = "Info";
                    const match = line.match(/(\d+)\/tcp\s+(\w+)/);
                    if(match) { port = match[1]; service = match[2]; }

                    tr.innerHTML = `<td>${counter++}</td><td>INFO</td><td>${port} / ${service}</td><td>${line.replace(/</g, '&lt;')}</td>`;
                    tbody.appendChild(tr);
                }
            });
            if(tbody.innerHTML === "") {
                 const tr = document.createElement('tr');
                 tr.innerHTML = `<td colspan="4" style="white-space: pre-wrap; font-family: monospace;">${logs.substring(0, 1000).replace(/</g, '&lt;')}...</td>`;
                 tbody.appendChild(tr);
            }

            document.getElementById('pdf-ai-content').innerText = window.currentAIReport;

            const element = document.getElementById('pdf-template');
            element.classList.remove('hidden');

            const opt = {
                margin: 0,
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
