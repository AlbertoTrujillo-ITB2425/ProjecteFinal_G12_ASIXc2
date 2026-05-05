<?php 
// includes/chatbot.php 
?>
<div id="ai-window" class="hidden fixed bottom-24 right-8 w-96 glass-panel flex flex-col shadow-2xl z-[110] border border-sky-500/30 bg-slate-900/95 overflow-hidden">
    <div class="bg-sky-600 p-3 flex justify-between items-center">
        <span class="text-xs font-black text-white uppercase"><i class="fas fa-robot mr-2"></i>AI SOC Analyst</span>
        <button onclick="toggleChat()" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
    </div>
    <div id="ai-messages" class="h-80 overflow-y-auto p-4 text-[10px] font-mono text-slate-300 space-y-2">
        <div class="text-sky-400 italic">--- Sesión iniciada con s12_ollama ---</div>
    </div>
    <div class="p-3 border-t border-white/10 bg-slate-950">
        <input type="text" id="ai-input" onkeypress="if(event.key==='Enter') sendToAI()" placeholder="Escribe al SOC AI..." class="w-full bg-transparent text-xs text-white outline-none">
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
    const text = input.value;
    if(!text) return;

    box.innerHTML += `<div class="text-white"><strong>></strong> ${text}</div>`;
    input.value = '';

    try {
        // IMPORTANTE: Al estar en Docker, usamos el nombre del contenedor
        // Pero como el JS corre en el cliente, usamos la IP de la máquina o el proxy.
        // Si no tienes el puerto 11434 abierto al exterior, fallará.
        const response = await fetch('http://' + window.location.hostname + ':11434/api/generate', {
            method: 'POST',
            body: JSON.stringify({ model: 'qwen', prompt: text, stream: false })
        });
        const data = await response.json();
        box.innerHTML += `<div class="text-emerald-400"><strong>AI:</strong> ${data.response}</div>`;
    } catch (e) {
        box.innerHTML += `<div class="text-red-500 italic">Error de enlace: Asegúrate de que el puerto 11434 esté abierto en AWS y Docker.</div>`;
    }
    box.scrollTop = box.scrollHeight;
}
</script>
