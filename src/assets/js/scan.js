/**
 * CYBERPYME SOC - Advanced Scanner Logic v6.0.0
 * Optimized for Qwen2.5-Mini & Real-time UI feedback.
 */

window.currentAIReport = "";

// Helper per netejar la consola i l'estat de la IA
function resetUI() {
    const aiContainer = document.getElementById('ai-preview-container');
    const aiLiveText = document.getElementById('ai-live-text');
    const consoleOut = document.getElementById('console-output');
    
    consoleOut.innerHTML = "";
    window.currentAIReport = "";
    if (aiContainer) aiContainer.classList.add('hidden');
    if (aiLiveText) aiLiveText.innerHTML = "";
    
    const pdfBtn = document.getElementById('btn-pdf');
    pdfBtn.disabled = true;
    pdfBtn.classList.add('text-slate-600', 'cursor-not-allowed');
    pdfBtn.classList.remove('ai-glow', 'text-sky-400', 'border-sky-500');
}

document.getElementById('file-input').addEventListener('change', function(e) {
    const fileStatus = document.getElementById('file-status');
    const fileName = e.target.files[0] ? e.target.files[0].name : "Click or drop hosts file";
    fileStatus.querySelector('span').innerText = fileName;
    document.getElementById('target').disabled = !!e.target.files[0];
});

async function runAudit() {
    const fileInput = document.getElementById('file-input');
    const manualTarget = document.getElementById('target').value.trim();
    const btn = document.getElementById('btn-run');

    resetUI();
    updateStatus("Scanning", "bg-sky-500 animate-pulse text-white");

    let targets = [];
    if (fileInput.files.length > 0) {
        const text = await fileInput.files[0].text();
        targets = text.split(/\r?\n/).filter(line => line.trim() !== "");
    } else if (manualTarget) {
        targets = [manualTarget];
    } else {
        alert("Please enter a target or upload a .txt file.");
        updateStatus("Ready", "bg-slate-800 text-slate-400");
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `<i class="fas fa-circle-notch animate-spin"></i> Processing...`;

    // 1. Execució de NMAP
    for (const host of targets) {
        appendConsole(`\n[SYSTEM] Initializing audit for: ${host}...`, 'text-sky-400 font-bold');
        await executeNmapScan(host);
    }

    // 2. Petició a la IA (Passem tot el text acumulat a la consola)
    const fullConsoleText = document.getElementById('console-output').innerText;
    await requestAIAnalysis(fullConsoleText);

    // 3. Finalització
    btn.disabled = false;
    btn.innerHTML = `<i class="fas fa-bolt"></i> Run Audit`;
    updateStatus("Completed", "bg-emerald-500/20 text-emerald-400 border border-emerald-500/50");
}

async function executeNmapScan(target) {
    const type = document.getElementById('type').value;
    try {
        const response = await fetch(`api/scan_async.php?target=${target}&type=${type}`);
        const data = await response.text(); 
        appendConsole(data, 'text-slate-400 text-[11px]');
        return true;
    } catch (error) {
        appendConsole(`[ERROR] Scan failed for ${target}`, 'text-red-500');
        return false;
    }
}

async function requestAIAnalysis(scanFullText) {
    const aiContainer = document.getElementById('ai-preview-container');
    const aiLiveText = document.getElementById('ai-live-text');
    const pdfBtn = document.getElementById('btn-pdf');
    
    appendConsole("\n[AI] Analyzing vulnerabilities with Qwen2.5-Mini...", 'text-amber-400 animate-pulse');
    console.log("DEBUG: Sending to AI:", scanFullText.substring(0, 100) + "...");

    try {
        const response = await fetch('api/ai_analysis.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ scan_data: scanFullText })
        });

        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);

        const data = await response.json();
        console.log("DEBUG: AI Response received:", data);

        if (data.response) {
            window.currentAIReport = data.response;
            
            // Forçar visibilitat
            if (aiContainer) {
                aiContainer.classList.remove('hidden');
                aiContainer.style.display = 'block'; // Garantia extra
            }
            
            if (aiLiveText) {
                aiLiveText.innerHTML = data.response.replace(/\n/g, '<br>');
            }
            
            appendConsole("\n[SUCCESS] AI Security Insights generated.", 'text-sky-400 font-black');
            
            // Activar PDF
            pdfBtn.disabled = false;
            pdfBtn.classList.remove('text-slate-600', 'cursor-not-allowed');
            pdfBtn.classList.add('text-sky-400', 'border-sky-500', 'ai-glow');
            
            confetti({ particleCount: 100, spread: 70, origin: { y: 0.7 } });
        } else {
            throw new Error("JSON received but 'response' field is missing.");
        }
    } catch (error) {
        console.error("DEBUG: AI Request Error:", error);
        appendConsole(`\n[ERROR] AI Analysis failed: ${error.message}`, 'text-red-500');
        // Mostrar la caixa d'error encara que hagi fallat
        if (aiContainer) aiContainer.classList.remove('hidden');
        if (aiLiveText) aiLiveText.innerHTML = `<span class="text-red-400">Error connecting to AI Engine. Please verify s12_ollama container.</span>`;
    }
}

function appendConsole(text, className = "") {
    const consoleOut = document.getElementById('console-output');
    const span = document.createElement('span');
    span.className = className + " block mb-1 font-mono";
    span.innerText = text;
    consoleOut.appendChild(span);
    
    const wrapper = document.getElementById('capture-area');
    wrapper.scrollTop = wrapper.scrollHeight;
}

function updateStatus(text, classes) {
    const tag = document.getElementById('status-tag');
    if (tag) {
        tag.innerText = text;
        tag.className = `text-[9px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest ${classes}`;
    }
}

function exportToPDF() {
    // Mateixa lògica que tenies, és correcta.
    const target = document.getElementById('target').value || "Bulk_Audit_Report";
    document.getElementById('pdf-target').innerText = target;
    document.getElementById('pdf-date').innerText = new Date().toLocaleString();
    document.getElementById('pdf-console-content').innerText = document.getElementById('console-output').innerText;
    document.getElementById('pdf-ai-content').innerText = window.currentAIReport;

    const element = document.getElementById('pdf-template');
    element.classList.remove('hidden');

    html2pdf().set({
        margin: 10,
        filename: `SOC_Report_${target}.pdf`,
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(element).save().then(() => element.classList.add('hidden'));
}
