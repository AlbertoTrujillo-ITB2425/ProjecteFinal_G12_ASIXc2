<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header('Location: ../../auth.php'); 
    exit; 
}
$auditor_name = $_SESSION['user_name'] ?? "Auditor_Especialista";
?>
<?php include '../../includes/header.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script src="/assets/js/reporter.js?v=<?= time() ?>"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');
    .terminal-font { font-family: 'JetBrains Mono', monospace; }
    .glass-panel { background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.08); }
    .status-pulse { animation: pulse 2s infinite; }
    #capture-area::-webkit-scrollbar { width: 4px; }
    #capture-area::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 10px; }
</style>

<main class="max-w-7xl mx-auto px-6 py-10">
    <div class="flex justify-between items-end mb-10">
        <div>
            <h1 class="text-4xl font-black uppercase italic text-white">Audit <span class="text-blue-500">Console.</span></h1>
            <p class="text-[9px] terminal-font text-slate-500 uppercase">Operador: <span class="text-blue-400"><?= htmlspecialchars($auditor_name) ?></span></p>
        </div>
        <div id="status-tag" class="glass-panel px-6 py-2 rounded-xl text-[10px] font-black uppercase text-slate-400 border-l-2 border-blue-500">System Standby</div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-4 space-y-6">
            <section class="glass-panel p-8 rounded-2xl border-t-2 border-blue-500">
                <div class="space-y-4">
                    <input type="text" id="target" placeholder="127.0.0.1" class="w-full bg-black/50 border border-white/10 rounded-xl p-4 text-blue-400 outline-none">
                    <div class="py-2">
                        <label class="text-[9px] font-bold text-slate-400 uppercase flex items-center gap-2 cursor-pointer hover:text-blue-400">
                            <i class="fas fa-file-import"></i> O cargar .txt
                            <input type="file" id="file-input" accept=".txt" class="hidden" onchange="handleFileUpload()">
                        </label>
                        <div id="file-status" class="text-[8px] text-slate-500 mt-1"></div>
                    </div>
                    <select id="type" class="w-full bg-black/50 border border-white/10 rounded-xl p-4 text-slate-300 outline-none">
                        <option value="quick">⚡ Quick Discovery</option>
                        <option value="full">🛡️ Full Audit</option>
                    </select>
                    <button id="btn-run" onclick="startAuditCycle()" class="w-full bg-blue-600 text-white font-black py-5 rounded-xl uppercase text-[11px]">Launch Audit</button>
                </div>
            </section>
        </div>

        <div class="lg:col-span-8 space-y-6">
            <section id="ai-preview-container" class="hidden glass-panel p-6 border border-blue-500/20 rounded-2xl bg-blue-500/5">
                <div id="ai-live-text" class="text-[13px] text-slate-300 terminal-font whitespace-pre-wrap"></div>
            </section>
            <div class="glass-panel rounded-3xl overflow-hidden min-h-[400px]">
                <div id="capture-area" class="p-10 overflow-y-auto h-[400px] bg-black/30">
                    <pre id="console-output" class="terminal-font text-blue-400/80 text-[12px]"></pre>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <button id="btn-pdf" onclick="exportToPDF()" disabled class="w-full p-4 border-2 border-dashed border-white/10 rounded-xl uppercase font-black text-[10px] text-slate-600">Generate PDF</button>
                <button onclick="window.location.reload()" class="p-4 bg-white/5 rounded-xl text-[10px] text-slate-400 uppercase font-bold">Reset</button>
            </div>
        </div>
    </div>
</main>

<script>
    window.currentAIReport = "";
    window.auditorName = "<?= htmlspecialchars($auditor_name) ?>";
    let hostQueue = [];

    function handleFileUpload() {
        const file = document.getElementById('file-input').files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                hostQueue = e.target.result.split('\n').map(h => h.trim()).filter(h => h.length > 0);
                document.getElementById('file-status').innerText = `${hostQueue.length} hosts listos.`;
            };
            reader.readAsText(file);
        }
    }

    async function startAuditCycle() {
        const target = document.getElementById('target').value.trim();
        if (hostQueue.length === 0 && !target) return alert("Host requerido.");
        if (hostQueue.length === 0) hostQueue = [target];
        
        document.getElementById('btn-run').disabled = true;
        for (const h of hostQueue) {
            await executeSecurityAudit(h);
        }
        document.getElementById('btn-run').disabled = false;
        confetti({ particleCount: 100 });
    }

    async function executeSecurityAudit(target) {
        const response = await fetch(`../../api/scan_async.php?target=${target}&type=${document.getElementById('type').value}`);
        const result = await response.text();
        document.getElementById('console-output').innerText += `\n--- ${target} ---\n` + result;
        await requestNeuralAnalysis(result, target);
    }

    async function requestNeuralAnalysis(logData, target) {
        document.getElementById("ai-preview-container").classList.remove("hidden");
        const response = await fetch("../../api/ai_analysis.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({ scan_data: logData })
        });
        const data = await response.json();
        window.currentAIReport += `\n--- TARGET: ${target} ---\n${data.response}\n`;
        document.getElementById("ai-live-text").innerText = window.currentAIReport;
        
        const btn = document.getElementById('btn-pdf');
        btn.disabled = false;
        btn.className = "w-full p-4 border-2 border-blue-500 text-blue-400 rounded-xl font-black text-[10px]";
    }

    function exportToPDF() {
        if (typeof window.generateFullReport === "function") {
            window.generateFullReport(window.auditorName);
        }
    }
</script>
</body></html>
