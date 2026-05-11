/**
 * CYBERPYME SOC Logic v6.7.0 
 * Archivo: assets/js/scanner-logic.js
 * Manejo de Auditoría, IA y Exportación PDF
 */

let fullLogs = "";
let finalAIReport = "";
let isScanning = false;

// 1. Inicialización y manejo de Inputs
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('file-input');
    const targetInput = document.getElementById('target');
    const fileStatusText = document.querySelector('#file-status span'); // Ajustado a tu HTML

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : "Import hosts file";
            if (fileStatusText) fileStatusText.innerText = fileName;
            
            // Si hay archivo, deshabilitamos el input manual
            if (targetInput) {
                targetInput.disabled = !!e.target.files[0];
                targetInput.placeholder = e.target.files[0] ? "Usando archivo: " + fileName : "192.168.1.1";
            }
        });
    }
});

// 2. Función Principal: Ejecutar Auditoría
async function runAudit() {
    if (isScanning) return;

    const targetField = document.getElementById('target');
    const fileField = document.getElementById('file-input');
    const type = document.getElementById('type').value;
    const consoleOut = document.getElementById('console-output');
    const btn = document.getElementById('btn-run');
    const progressContainer = document.getElementById('scan-progress');

    let targets = [];

    // Prioridad: Archivo > Texto manual
    if (fileField && fileField.files.length > 0) {
        const text = await fileField.files[0].text();
        targets = text.split(/\r?\n/).map(t => t.trim()).filter(t => t !== "");
    } else if (targetField && targetField.value.trim() !== "") {
        targets = [targetField.value.trim()];
    } else {
        alert("Por favor, introduce una IP o sube un archivo.");
        return;
    }

    // Resetear Interfaz y Estado
    isScanning = true;
    fullLogs = "";
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch animate-spin"></i> Auditando...';
    consoleOut.innerHTML = '<span class="text-blue-500">[SYSTEM] Iniciando secuencia...</span>';
    
    document.getElementById('ai-preview-container')?.classList.add('hidden');
    document.getElementById('threat-intel')?.classList.add('hidden');
    progressContainer?.classList.remove('hidden');

    // Bucle de Escaneo
    for (let i = 0; i < targets.length; i++) {
        const host = targets[i];
        const progressPercent = Math.round(((i + 1) / targets.length) * 100);
        
        const progressText = document.getElementById('progress-percent');
        if (progressText) progressText.innerText = progressPercent + "%";
        
        appendLog(`[TASK] Analizando objetivo ${i+1}/${targets.length}: ${host}`);
        
        try {
            // Ajustado a 'api_nmap.php' según tu código original
            const res = await fetch('api_nmap.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ target: host, type: type })
            });
            
            const data = await res.json();
            const output = data.result || "Sin respuesta técnica.";
            
            appendLog(output);
            fullLogs += `\n--- HOST: ${host} ---\n${output}\n`;

        } catch (e) {
            appendLog(`[ERROR] Fallo de conexión en ${host}`, true);
        }
    }

    // Finalizar Escaneo
    isScanning = false;
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-bolt"></i> Run Audit';
    progressContainer?.classList.add('hidden');

    // Disparar IA si hay datos relevantes
    if (fullLogs.length > 20) {
        await analyzeWithAI(fullLogs);
    }
}

// Helper para escribir en la consola con auto-scroll
function appendLog(text, isError = false) {
    const consoleOut = document.getElementById('console-output');
    const captureArea = document.getElementById('capture-area');
    if (!consoleOut) return;

    const entry = document.createElement('div');
    entry.className = isError ? "text-red-400 mb-1" : "mb-1";
    entry.innerText = text;
    consoleOut.appendChild(entry);
    
    if (captureArea) captureArea.scrollTop = captureArea.scrollHeight;
}

// 3. Función: Análisis con Inteligencia Artificial
async function analyzeWithAI(scanData) {
    const container = document.getElementById('ai-preview-container');
    const aiText = document.getElementById('ai-live-text');
    
    if (container) container.classList.remove('hidden');
    if (aiText) aiText.innerHTML = "<span class='status-pulse text-blue-400'>🧠 [NEURAL_CORE] Procesando logs técnicos...</span>";

    try {
        // Ajustado a 'scan_process.php' según tu estructura
        const res = await fetch('scan_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ scan_data: scanData })
        });
        const json = await res.json();
        
        finalAIReport = json.response;
        if (aiText) aiText.innerHTML = json.response.replace(/\n/g, '<br>');

        // Actualizar Widget de Riesgo
        updateThreatWidget(json.response);

        // Habilitar Botón PDF
        const pdfBtn = document.getElementById('btn-pdf');
        if (pdfBtn) {
            pdfBtn.disabled = false;
            pdfBtn.className = "w-full p-5 flex items-center justify-center gap-3 text-blue-400 border-2 border-blue-500/50 bg-blue-900/10 rounded-2xl cursor-pointer uppercase font-black text-[10px] tracking-widest ai-glow";
        }

    } catch (e) {
        if (aiText) aiText.innerText = "Error: El motor de IA no ha podido generar el informe.";
    }
}

function updateThreatWidget(aiResponse) {
    const threatIntel = document.getElementById('threat-intel');
    const scoreVal = document.getElementById('score-val');
    const scoreBar = document.getElementById('score-bar');
    
    if (!threatIntel) return;

    const isCritical = /critical|high|vulnerable|peligro|riesgo alto/i.test(aiResponse);
    const score = isCritical ? 88 : 35;

    threatIntel.classList.remove('hidden');
    if (scoreVal) scoreVal.innerHTML = `${score}<span class="text-xs opacity-50">/100</span>`;
    if (scoreBar) {
        scoreBar.style.width = score + "%";
        scoreBar.className = `h-full transition-all duration-1000 ${score > 70 ? 'bg-red-500' : 'bg-blue-500'}`;
    }

    if (score > 70 && typeof confetti === 'function') {
        confetti({ particleCount: 100, spread: 70, origin: { y: 0.8 } });
    }
}

// 4. Función: Exportar a PDF
function exportToPDF() {
    const target = document.getElementById('target')?.value || "SOC_Bulk_Report";
    const template = document.getElementById('pdf-template');
    
    if (!template) return;

    // Rellenar la plantilla antes de capturar
    const pdfTarget = document.getElementById('pdf-client') || document.getElementById('pdf-target-val');
    const pdfDate = document.getElementById('pdf-date');
    const pdfAI = document.getElementById('pdf-ai-content');

    if (pdfTarget) pdfTarget.innerText = target.toUpperCase();
    if (pdfDate) pdfDate.innerText = new Date().toLocaleString();
    if (pdfAI) pdfAI.innerText = finalAIReport;

    template.classList.remove('hidden');

    const opt = {
        margin: 5,
        filename: `SOC_Report_${target.replace(/\./g, '_')}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(template).save().then(() => {
        template.classList.add('hidden');
    });
}
