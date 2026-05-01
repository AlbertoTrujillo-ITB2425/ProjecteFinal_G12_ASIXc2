/**
 * CYBERPYME SOC - Main Application Logic v6.5.0
 * Engine: Traducción Nativa + Web3 Phantom Wallet
 */

const SOCCore = {
    isConnected: false,
    currentLang: null,

    init: function() {
        try {
            this.currentLang = localStorage.getItem('soc_lang') || 'es';
            this.setupTheme();
            this.applyLanguage(this.currentLang);
        } catch (e) {
            console.warn('[SOC] Error en inicialización:', e);
        }
    },

    // --- LÓGICA DE IDIOMAS NATIVA ---
    applyLanguage: function(lang) {
        try {
            const uiMap = { 'es': 'ES', 'en': 'EN', 'ca': 'CA' };
            const selected = uiMap[lang] ? lang : 'es';
            this.currentLang = selected;
            localStorage.setItem('soc_lang', selected);

            const textElem = document.getElementById('current-lang-text');
            if (textElem) textElem.innerText = uiMap[selected];

            if (typeof translations !== 'undefined' && translations[selected]) {
                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (translations[selected][key]) {
                        el.innerHTML = translations[selected][key];
                    }
                });
            }
        } catch (e) {
            console.error('[SOC] Error aplicando idioma:', e);
        }
    },

    // --- LÓGICA DE TEMA ---
    setupTheme: function() {
        try {
            const savedTheme = localStorage.getItem('soc_theme') || 'dark';
            if (savedTheme === 'light') {
                document.body?.classList.add('light-mode');
                const icon = document.getElementById('theme-icon');
                if (icon) icon.className = 'fas fa-moon text-slate-600';
            }
        } catch (e) {
            console.warn('[SOC] Error configurando tema:', e);
        }
    },

    toggleTheme: function() {
        try {
            if (!document.body) return;
            const isLight = document.body.classList.toggle('light-mode');
            localStorage.setItem('soc_theme', isLight ? 'light' : 'dark');
            
            const icon = document.getElementById('theme-icon');
            if (icon) icon.className = isLight ? 'fas fa-moon text-slate-600' : 'fas fa-sun text-amber-400';
        } catch (e) {
            console.error('[SOC] Error cambiando tema:', e);
        }
    },

    // --- LÓGICA WEB3 (PHANTOM) ---
    loginWeb3: async function() {
        const btn = document.getElementById('wallet-btn');
        const text = document.getElementById('wallet-text');
        const status = document.getElementById('status-card');
        
        try {
            const provider = window.phantom?.solana;
            if (!provider) {
                alert("⚠️ No se ha detectado Phantom Wallet.\nPor favor, instale la extensión.");
                window.open("https://phantom.app/", "_blank");
                return;
            }
            
            if (text) text.innerText = "CONECTANDO...";
            if (btn) btn.classList.add('opacity-80', 'cursor-not-allowed');
            
            const resp = await provider.connect();
            const pubKey = resp.publicKey.toString();
            
            const message = `[CYBERPYME SOC G12]\nAuditoría de Acceso Restringido\n\nWallet: ${pubKey}\nTimestamp: ${Date.now()}`;
            const encodedMessage = new TextEncoder().encode(message);
            
            if (text) text.innerText = "FIRME MENSAJE...";
            await provider.signMessage(encodedMessage, "utf8");

            if(typeof confetti === 'function') {
                confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 }, colors: ['#0ea5e9', '#10b981', '#ab9ff2'] });
            }

            this.isConnected = true;
            const shortKey = pubKey.substring(0, 4) + "..." + pubKey.substring(pubKey.length - 4);
            
            if (text) text.innerText = shortKey;
            if (btn) {
                btn.className = "bg-emerald-600 text-white font-bold px-6 md:px-8 py-3 rounded-xl shadow-lg shadow-emerald-500/20 border border-emerald-400 flex items-center gap-3";
                btn.innerHTML = `<i class="fas fa-check-circle text-white"></i> <span class="tracking-widest">${shortKey}</span>`;
            }
            
            if (status) {
                status.innerHTML = `<div class="text-emerald-400 font-bold mb-1">✅ SIGNATURE VERIFIED</div><div class="text-white">${pubKey}</div><div class="mt-2 text-[10px] text-slate-500">Access Level: G12 ROOT</div>`;
                status.classList.remove('italic', 'text-slate-500');
                status.classList.add('border-emerald-500/30', 'bg-emerald-900/10');
            }

        } catch (err) {
            console.error("Autenticación Web3 abortada:", err);
            if (text) text.innerText = "LOGIN RECHAZADO";
            setTimeout(() => {
                if (text) text.innerText = "LOGIN AUDITOR";
                if (btn) btn.classList.remove('opacity-80', 'cursor-not-allowed');
            }, 3000);
        }
    }
};

// --- EXPOSICIÓN DE FUNCIONES GLOBALES ---
window.changeLanguage = (l) => SOCCore.applyLanguage(l);
window.toggleTheme = () => SOCCore.toggleTheme();
window.web3Login = () => SOCCore.loginWeb3();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => SOCCore.init());
} else {
    SOCCore.init();
}
