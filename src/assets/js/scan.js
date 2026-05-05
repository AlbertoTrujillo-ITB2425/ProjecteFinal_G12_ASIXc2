/**
 * CYBERPYME SOC - Advanced Scanner Logic v6.8.0
 * * CARACTERÍSTICAS:
 * - Soporte Bulk Import (.txt) y Single Target.
 * - Filtrado de ruido Nmap para optimizar latencia de IA.
 * - Gestión de estados UI (Scanning -> AI Analysis -> Completed).
 * - Exportación técnica a PDF.
 */

// Variables Globales de Sesión
window.currentAIReport = "";
let accumulatedLogsForAI = "";

/**
 * Inicialización y Event Listeners
 */
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('file-input');
    const targetInput = document.getElementById('target');
    const fileStatus = document.getElementById('file-status');

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const name = e.target.files[0].name;
                fileStatus.querySelector('span').innerText = `Archivo: ${name}`;
                fileStatus.classList.add('border-sky-500', 'bg-sky-500/10');
                
                // Deshabilitar input manual si hay archivo
                targetInput.value = "";
                targetInput.disabled = true;
                targetInput.placeholder = "Modo lista de hosts activo";
            } else {
                resetFileInput();
            }
        });
    }
});

function resetFileInput() {
    const fileInput = document.getElementById('file-input');
    const targetInput = document.getElementById('target');
    const fileStatus = document.getElementById('file-status');
    
    if (fileInput) fileInput.value = "";
    if (fileStatus) {
        fileStatus.querySelector('span').innerText = "Import hosts file";
        fileStatus.classList.remove('border-sky-500', 'bg-sky-500/10');
    }
    if (targetInput) {
        targetInput.disabled = false;
        targetInput.placeholder = "192.168.1.1 / itb.cat";
    }
}

/**
 * Función Principal de Auditoría
 */
async function runAudit() {
    const targetInput = document.getElementById('target');
    const fileInput = document.getElementById('file-input');
    const type = document.getElementById('type').value;
    const btn = document.getElementById('btn-run');

    // 1. Resetear Interfaz
    resetUI();
    
    // 2. Obtener Objetivos
    let targets = [];
    if (fileInput.files.length > 0) {
        const text = await fileInput.files[0].text();
        targets = text.split(/\r?\n/).filter(line => line.trim() !== "");
    } else if (targetInput.value.trim() !== "") {
        targets = [targetInput.value.trim()];
    } else {
        alert("Error: Introduce una IP/Dominio o carga un archivo .txt");
        return;
    }

    // 3. Bloquear UI
    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-shield-halved animate-spin"></i> PROCESANDO...`;
    updateStatusTag("Escaneando", "bg-sky-500 text-white animate-pulse");
    document.getElementById('scan-progress').classList.remove('hidden');

    // 4. Bucle de Escaneo
    for (let i = 0; i < targets.length; i++) {
        const host = targets[i];
        const percent = Math.round(((i + 1) / targets.length) * 100);
        document.getElementById('progress-percent').innerText = `${percent}%`;
        
        appendConsole(`\n[SYSTEM] Iniciando auditoría: ${host} (${i+1}/${targets.length})`, 'text-sky-400 font-bold');
        
        const success = await executeSingleScan(host, type);
        if (!success) appendConsole(`[!] Error en el host ${host}. Saltando...`, 'text-red-400');
    }

    // 5. Análisis de Inteligencia Artificial
    if (accumulatedLogsForAI.trim().length > 20) {
        await processAIAnalysis();
    } else {
        appendConsole("\n[INFO] No se hallaron puertos abiertos. IA en standby.", 'text-slate-500');
    }

    // 6. Finalización
    btn.disabled = false;
    btn.innerHTML = `<i class="fas fa-bolt"></i> Run Audit`;
    updateStatusTag("Completado", "bg-emerald-500/20 text-emerald-400 border border-emerald-500/50");
    document.getElementById('scan-progress').classList.add('hidden');
}

/**
 * Ejecuta Nmap mediante el backend
 */
async function executeSingleScan(host, type) {
    try {
        // Llamada al endpoint de escaneo (asegúrate que la ruta es correcta)
        const response = await fetch(`api/scan_async.php?target=${host}&type=${type}`);
        const rawData = await response.text();

        // Detectar errores del servidor (404, 500 o mensaje de Objetivo Inválido)
        if (rawData.includes('"error"') || rawData.includes('404')) {
            appendConsole(`[ERROR] El servidor rechazó el objetivo: ${host}`, 'text-red-500');
            return false;
        }

        // Mostrar en consola
        appendConsole(rawData, 'text-slate-400');

        // FILTRADO PARA LA IA:
        // Solo enviamos líneas relevantes para no saturar el modelo y que sea rápido.
        const lines = rawData.split('\n');
        const relevantData = lines.filter(line => 
            line.includes('open') || 
            line.includes('vulnerable') || 
            line.includes('Nmap scan report') ||
            line.includes('Service Info')
        ).join('\n');

        accumulatedLogsForAI += `\n--- RESULTADOS PARA ${host} ---\n${relevantData}\n`;
        return true;

    } catch (error) {
        console.error("Scan Error:", error);
        return false;
    }
}

/**
 * Conexión con Ollama a través de PHP
 */
async function processAIAnalysis() {
    const aiContainer = document.getElementById('ai-preview-container');
    const aiText = document.getElementById('ai-live-text');
    const pdfBtn = document.getElementById('btn-pdf');

    aiContainer.classList.remove('hidden');
    aiText.innerHTML = `<span class="animate-pulse">🧠 Analizando topología y vulnerabilidades con Qwen2.5-Mini...</span>`;
    appendConsole("\n[AI] Solicitando informe neural...");

    try {
        const response = await fetch('api/ai_analysis.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ scan_data: accumulatedLogsForAI })
        });

        const data = await response.json();

        if (data.response) {
            window.currentAIReport = data.response;
            aiText.innerHTML = data.response.replace(/\n/g, '<br>');
            
            // Actualizar Risk Score (Lógica basada en palabras clave)
            updateRiskScore(data.response);

            // Habilitar PDF con estilos activos
            pdfBtn.disabled = false;
            pdfBtn.className = "w-full p-5 flex items-center justify-center gap-3 text-sky-400 border-2 border-sky-500 rounded-2xl transition-all cursor-pointer bg-sky-500/5 ai-glow font-black text-[10px] tracking-widest";
            
            appendConsole("\n[SUCCESS] Informe de seguridad generado.", 'text-sky-400 font-black');
            confetti({ particleCount: 100, spread: 70, origin: { y: 0.7 } });
        } else {
            throw new Error("Respuesta de IA vacía");
        }
    } catch (error) {
        aiText.innerHTML = `<span class="text-red-400">Error: El motor Ollama no responde (404/500).</span>`;
        appendConsole("\n[ERROR] El motor de IA falló.", 'text-red-500');
    }
}

/**
 * Helpers de UI
 */
function resetUI() {
    document.getElementById('console-output').innerHTML = "";
    document.getElementById('ai-preview-container').classList.add('hidden');
    document.getElementById('threat-intel').classList.add('hidden');
    document.getElementById('score-bar').style.width = "0%";
    window.currentAIReport = "";
    accumulatedLogsForAI = "";
}

function appendConsole(text, className = "") {
    const consoleOut = document.getElementById('console-output');
    const div = document.createElement('div');
    div.className = className + " mb-1 terminal-font";
    div.innerText = text;
    consoleOut.appendChild(div);
    
    const wrapper = document.getElementById('capture-area');
    wrapper.scrollTop = wrapper.scrollHeight;
}

function updateStatusTag(text, classes) {
    const tag = document.getElementById('status-tag');
    tag.innerText = text;
    tag.className = `px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest ${classes}`;
}

function updateRiskScore(aiText) {
    const threatPanel = document.getElementById('threat-intel');
    const scoreVal = document.getElementById('score-val');
    const scoreBar = document.getElementById('score-bar');
    
    threatPanel.classList.remove('hidden');
    
    let score = 20; // Base
    const text = aiText.toLowerCase();
    
    if (text.includes("crítica") || text.includes("critical")) score = 95;
    else if (text.includes("alta") || text.includes("high")) score = 75;
    else if (text.includes("media") || text.includes("medium")) score = 50;
    
    scoreVal.innerHTML = `${score}<span class="text-xs text-slate-500">/100</span>`;
    scoreBar.style.width = `${score}%`;
}

/**
 * Exportación a PDF (Requiere html2pdf.js)
 */
function exportToPDF() {
    const target = document.getElementById('target').value || "SOC-Audit-Report";
    
    // Rellenar template oculto
    document.getElementById('pdf-target').innerText = target;
    document.getElementById('pdf-date').innerText = new Date().toLocaleString();
    document.getElementById('pdf-ai-content').innerText = window.currentAIReport;
    document.getElementById('pdf-console-content').innerText = document.getElementById('console-output').innerText;

    const element = document.getElementById('pdf-template');
    element.classList.remove('hidden');

    const opt = {
        margin: 10,
        filename: `CyberPyme_Report_${target.replace(/[^a-z0-9]/gi, '_')}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        element.classList.add('hidden');
    });
}
