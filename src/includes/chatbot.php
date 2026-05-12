<?php
// includes/chatbot.php
?>
<div id="ai-window" class="hidden fixed bottom-24 right-8 w-96 glass-panel flex flex-col shadow-2xl z-[110] border border-sky-500/30 bg-slate-900/95 overflow-hidden rounded-xl">
    <div class="bg-gradient-to-r from-sky-600 to-blue-700 p-3 flex justify-between items-center">
        <span class="text-xs font-black text-white uppercase tracking-wider"><i class="fas fa-robot mr-2"></i>SOC AI Assistant</span>
        <button onclick="toggleChat()" class="text-white/70 hover:text-white transition-colors"><i class="fas fa-times"></i></button>
    </div>
    
    <div id="ai-messages" class="h-80 overflow-y-auto p-4 text-[11px] font-mono text-slate-300 space-y-3 bg-slate-900/50">
        <div class="flex gap-2">
            <div class="w-6 h-6 rounded-full bg-sky-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-[8px] text-white"></i>
            </div>
            <div class="bg-slate-800/50 p-2 rounded-lg rounded-tl-none border border-slate-700">
                Hola, soy tu asistente de seguridad SOC. Estoy usando el motor Llama 3.3 para ayudarte. ¿Qué necesitas analizar?
            </div>
        </div>
    </div>
    
    <div class="p-3 border-t border-white/10 bg-slate-950 flex gap-2 items-center">
        <input type="text" id="ai-input" onkeypress="if(event.key==='Enter') sendToAI()" placeholder="Pregunta sobre vulnerabilidades..." class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white outline-none focus:border-sky-500 transition-colors">
        <button onclick="sendToAI()" class="bg-sky-600 hover:bg-sky-500 text-white p-2 rounded-lg transition-colors">
            <i class="fas fa-paper-plane text-xs"></i>
        </button>
        <span id="ai-spinner" class="hidden text-sky-400 text-xs animate-pulse ml-2">Analizando...</span>
    </div>
</div>

<button onclick="toggleChat()" class="fixed bottom-6 right-6 w-14 h-14 bg-gradient-to-br from-sky-500 to-blue-600 rounded-full shadow-lg shadow-sky-500/30 flex items-center justify-center text-white hover:scale-110 transition-all z-[109] group">
    <i class="fas fa-brain text-xl group-hover:animate-pulse"></i>
</button>

<script>
let chatHistory = []; // Historial para contexto

function toggleChat() { 
    const win = document.getElementById('ai-window');
    win.classList.toggle('hidden');
    if (!win.classList.contains('hidden')) {
        document.getElementById('ai-input').focus();
    }
}

async function sendToAI() {
    const input = document.getElementById('ai-input');
    const box = document.getElementById('ai-messages');
    const spinner = document.getElementById('ai-spinner');
    const text = input.value.trim();
    
    if (!text) return;

    // Deshabilitar input mientras piensa
    input.disabled = true;
    spinner.classList.remove('hidden');
    
    // Añadir mensaje del usuario a la UI
    addMessageToUI(text, 'user');
    input.value = '';
    box.scrollTop = box.scrollHeight;

    try {
        // LLAMADA AL MISMO BACKEND QUE EL SCANNER (Groq + Llama 3.3)
        const response = await fetch('/api/chat_ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                message: text, 
                history: chatHistory 
            })
        });

        if (!response.ok) throw new Error('Error en la red');

        const data = await response.json();
        
        let reply = data.response || 'No se pudo generar una respuesta.';

        // Añadir respuesta de la IA a la UI
        addMessageToUI(reply, 'bot');

        // Actualizar historial para el próximo turno
        chatHistory.push({ role: 'user', content: text });
        chatHistory.push({ role: 'assistant', content: reply });
        
        // Limitar historial a los últimos 10 intercambios (20 mensajes) para no saturar el contexto
        if (chatHistory.length > 20) {
            chatHistory = chatHistory.slice(-20);
        }

    } catch (e) {
        console.error(e);
        addMessageToUI("Error de conexión con el motor de IA. Verifica que el servicio está activo.", 'error');
    } finally {
        input.disabled = false;
        spinner.classList.add('hidden');
        box.scrollTop = box.scrollHeight;
    }
}

function addMessageToUI(text, sender) {
    const box = document.getElementById('ai-messages');
    const div = document.createElement('div');
    div.className = "flex gap-2 mb-3";
    
    if (sender === 'user') {
        div.innerHTML = `
            <div class="ml-auto max-w-[85%] bg-sky-600/20 border border-sky-500/30 p-2 rounded-lg rounded-tr-none text-sky-100">
                ${escapeHtml(text)}
            </div>
            <div class="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-[8px] text-slate-300"></i>
            </div>
        `;
    } else if (sender === 'error') {
        div.innerHTML = `
             <div class="max-w-[90%] bg-red-900/20 border border-red-500/30 p-2 rounded-lg text-red-200 text-xs">
                ⚠️ ${escapeHtml(text)}
            </div>
        `;
    } else {
        // Bot
        div.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-sky-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-[8px] text-white"></i>
            </div>
            <div class="max-w-[85%] bg-slate-800/50 border border-slate-700 p-2 rounded-lg rounded-tl-none text-slate-300 leading-relaxed">
                ${formatText(text)}
            </div>
        `;
    }
    
    box.appendChild(div);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatText(text) {
    // Convertir saltos de línea en <br> y negritas básicas
    return escapeHtml(text).replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
}
</script>
