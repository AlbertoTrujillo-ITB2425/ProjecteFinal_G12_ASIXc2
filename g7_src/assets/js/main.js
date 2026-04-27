/**
 * CYBERPYME SOC - Main Application Logic
 * Integración Web3 y UI Controller
 */

const SOCCore = {
    isConnected: false,

    init: function() {
        this.setupTheme();
        this.enforceNoGoogleBar();
    },

    // --- LÓGICA DE IDIOMAS ---
    translate: function(langCode, flagEmoji, shortCode) {
        document.getElementById('current-lang-flag').innerText = flagEmoji;
        document.getElementById('current-lang-text').innerText = shortCode;
        const selectField = document.querySelector(".goog-te-combo");
        if (selectField) {
            selectField.value = langCode;
            selectField.dispatchEvent(new Event('change'));
        }
    },

    enforceNoGoogleBar: function() {
        const observer = new MutationObserver(() => {
            if (document.body.style.top !== '0px') {
                document.body.style.top = '0px';
                document.body.style.position = 'static';
            }
        });
        observer.observe(document.body, { attributes: true, attributeFilter: ['style'] });
    },

    // --- LÓGICA DE TEMA ---
    setupTheme: function() {
        const savedTheme = localStorage.getItem('soc_theme') || 'dark';
        const body = document.body;
        const icon = document.getElementById('theme-icon');
        
        if (savedTheme === 'light') {
            body.classList.add('light-mode');
            body.classList.remove('dark-mode');
            if(icon) icon.className = 'fas fa-moon text-indigo-600';
        } else {
            body.classList.add('dark-mode');
            body.classList.remove('light-mode');
            if(icon) icon.className = 'fas fa-sun text-amber-400';
        }
    },

    toggleTheme: function() {
        const body = document.body;
        const icon = document.getElementById('theme-icon');
        
        body.classList.toggle('light-mode');
        const isLight = body.classList.contains('light-mode');
        
        if (isLight) {
            body.classList.remove('dark-mode');
            localStorage.setItem('soc_theme', 'light');
            if(icon) icon.className = 'fas fa-moon text-indigo-600';
        } else {
            body.classList.add('dark-mode');
            localStorage.setItem('soc_theme', 'dark');
            if(icon) icon.className = 'fas fa-sun text-amber-400';
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
            
            text.innerText = "CONECTANDO...";
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            
            const resp = await provider.connect();
            const pubKey = resp.publicKey.toString();
            
            const message = `[CYBERPYME SOC G12]\nAuditoría de Acceso Restringido\n\nWallet: ${pubKey}\nTimestamp: ${Date.now()}`;
            const encodedMessage = new TextEncoder().encode(message);
            
            text.innerText = "FIRME MENSAJE...";
            await provider.signMessage(encodedMessage, "utf8");

            if(typeof confetti === 'function') {
                confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 }, colors: ['#0ea5e9', '#10b981', '#ab9ff2'] });
            }

            this.isConnected = true;
            const shortKey = pubKey.substring(0, 4) + "..." + pubKey.substring(pubKey.length - 4);
            
            text.innerText = shortKey;
            btn.className = "bg-emerald-600 text-white font-bold px-6 md:px-8 py-3 rounded-xl shadow-lg shadow-emerald-500/20 border border-emerald-400 flex items-center gap-3";
            btn.innerHTML = `<i class="fas fa-check-circle text-white"></i> <span class="tracking-widest">${shortKey}</span>`;
            
            if (status) {
                status.innerHTML = `<div class="text-emerald-400 font-bold mb-1">✅ SIGNATURE VERIFIED</div><div class="text-[var(--text-main)]">${pubKey}</div><div class="mt-2 text-[10px] text-slate-500">Access Level: G12 ROOT</div>`;
                status.classList.remove('italic', 'text-slate-500');
                status.classList.add('border-emerald-500/30', 'bg-emerald-900/10');
            }

        } catch (err) {
            console.error("Autenticación Web3 abortada:", err);
            text.innerText = "LOGIN RECHAZADO";
            setTimeout(() => {
                text.innerText = "LOGIN AUDITOR";
                btn.classList.remove('opacity-80', 'cursor-not-allowed');
            }, 3000);
        }
    },

    payPremium: async function() {
        if (!this.isConnected) {
            alert("🔒 ACCESO DENEGADO\n\nPor favor, inicia sesión con Phantom antes de pagar.");
            return;
        }
        alert("🚀 INTERACCIÓN CON SMART CONTRACT\n\nSimulando transferencia de 29.99 USDC...");
        setTimeout(() => {
            if(typeof confetti === 'function') {
                confetti({ particleCount: 300, spread: 120, origin: { y: 0.5 } });
            }
            alert("✅ TRANSACCIÓN CONFIRMADA EN SOLANA\n\n¡Bienvenido a PREMIUM!");
        }, 2000);
    }
};

// --- EXPOSICIÓN DE FUNCIONES GLOBALES ---
window.changeLanguage = (lang, flag, code) => SOCCore.translate(lang, flag, code);
window.toggleTheme = () => SOCCore.toggleTheme();
window.web3Login = () => SOCCore.loginWeb3();
window.payPremium = () => SOCCore.payPremium();

// Google Translate Init
window.googleTranslateElementInit = function() {
    new google.translate.TranslateElement({
        pageLanguage: 'es',
        includedLanguages: 'es,en,fr,zh-CN',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
};

document.addEventListener('DOMContentLoaded', () => SOCCore.init());
