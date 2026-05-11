<?php
// includes/chatbot.php
?>
<div id="ai-window" class="hidden fixed bottom-24 right-8 w-96 glass-panel flex flex-col shadow-2xl z-[110] border border-sky-500/30 bg-slate-900/95 overflow-hidden">
    <div class="bg-sky-600 p-3 flex justify-between items-center">
        <span class="text-xs font-black text-white uppercase"><i class="fas fa-robot mr-2"></i>AI SOC Analyst</span>
        <button onclick="toggleChat()" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
    </div>
    <div id="ai-messages" class="h-80 overflow-y-auto p-4 text-[10px] font-mono text-slate-300 space-y-2">
        <div class="text-sky-400 italic">--- Sesion iniciada con s12_ollama ---</div>
    </div>
    <div class="p-3 border-t border-white/10 bg-slate-950 flex gap-2 items-center">
        <input type="text" id="ai-input" onkeypress="if(event.key==='Enter') sendToAI()" placeholder="Escribe al SOC AI..." class="flex-1 bg-transparent text-xs text-white outline-none">
        <span id="ai-spinner" class="hidden text-sky-400 text-xs animate-pulse">Pensando...</span>
    </div>
</div>
<button onclick="toggleChat()" class="fixed bottom-6 right-6 w-14 h-14 bg-sky-600 rounded-xl shadow-lg shadow-sky-500/20 flex items-center justify-center text-white hover:scale-110 transition-all z-[109]">
    <i class="fas fa-brain"></i>
</button>
<script>
function toggleChat() { document.getElementById('ai-window').classList.toggle('hidden'); }

async function sendToAI() {
    const input = document.getElementById('ai-input');
    const box = document.getElementById('ai-messages');
    const spinner = document.getElementById('ai-spinner');
    const text = input.value.trim();
    if (!text) return;

    input.disabled = true;
    spinner.classList.remove('hidden');
    box.innerHTML += `<div class="text-white"><strong>></strong> ${text}</div>`;
    input.value = '';
    box.scrollTop = box.scrollHeight;

    try {
        const response = await fetch('/includes/ollama-proxy.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt: text })
        });

        const raw = await response.text();

        let reply;
        try {
            const data = JSON.parse(raw);
            reply = data.response ?? data.error ?? 'Sin respuesta del modelo';
        } catch {
            reply = '[ERROR] El servidor no devolvio JSON valido. Puede que Cloudflare este bloqueando la peticion o el modelo no este disponible.';
        }

        box.innerHTML += `<div class="text-emerald-400"><strong>AI:</strong> ${reply}</div>`;
    } catch (e) {
        box.innerHTML += `<div class="text-red-500 italic">[SIN CONEXION] No se pudo contactar con el servidor SOC AI. Verifica que el servicio esta activo.</div>`;
    }

    input.disabled = false;
    spinner.classList.add('hidden');
    box.scrollTop = box.scrollHeight;
}
</script>