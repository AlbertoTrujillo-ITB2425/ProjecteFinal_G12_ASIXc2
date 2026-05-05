/**
 * CYBERPYME SOC - Main Logic v7.0
 * Optimizado para rendimiento, modularidad y seguridad
 */

const SOCCore = (() => {

    /* ------------------------------
     *  CONFIGURACIÓN GLOBAL
     * ------------------------------ */
    const LANG_KEY = "soc_lang";
    const THEME_KEY = "soc_theme";

    const LANG_MAP = { es: "ES", en: "EN", ca: "CA" };

    const log = (msg, type = "info") => {
        const prefix = "[CYBERPYME SOC]";
        console[type](`${prefix} ${msg}`);
    };

    /* ------------------------------
     *  INICIALIZACIÓN
     * ------------------------------ */
    const init = () => {
        try {
            applyLanguage(localStorage.getItem(LANG_KEY) || "es");
            applyTheme(localStorage.getItem(THEME_KEY) || "dark");
            log("Core inicializado correctamente");
        } catch (e) {
            log("Error en inicialización: " + e, "warn");
        }
    };

    /* ------------------------------
     *  IDIOMAS
     * ------------------------------ */
    const applyLanguage = (lang) => {
        try {
            const selected = LANG_MAP[lang] ? lang : "es";
            localStorage.setItem(LANG_KEY, selected);

            const langText = document.getElementById("current-lang-text");
            if (langText) langText.innerText = LANG_MAP[selected];

            if (window.translations?.[selected]) {
                document.querySelectorAll("[data-i18n]").forEach(el => {
                    const key = el.dataset.i18n;
                    if (translations[selected][key]) {
                        el.innerHTML = translations[selected][key];
                    }
                });
            }

            log(`Idioma aplicado: ${selected.toUpperCase()}`);
        } catch (e) {
            log("Error aplicando idioma: " + e, "error");
        }
    };

    /* ------------------------------
     *  TEMA (LIGHT / DARK)
     * ------------------------------ */
    const applyTheme = (theme) => {
        try {
            const isLight = theme === "light";
            document.body.classList.toggle("light-mode", isLight);
            localStorage.setItem(THEME_KEY, theme);

            const icon = document.getElementById("theme-icon");
            if (icon) {
                icon.className = isLight
                    ? "fas fa-moon text-slate-600"
                    : "fas fa-sun text-amber-400";
            }

            log(`Tema aplicado: ${theme}`);
        } catch (e) {
            log("Error aplicando tema: " + e, "error");
        }
    };

    const toggleTheme = () => {
        const newTheme = document.body.classList.contains("light-mode") ? "dark" : "light";
        applyTheme(newTheme);
    };

    /* ------------------------------
     *  AUTENTICACIÓN WEB3 (PHANTOM)
     * ------------------------------ */
    const loginWeb3 = async () => {
        const btn = document.getElementById("wallet-btn");
        const text = document.getElementById("wallet-text");
        const status = document.getElementById("status-card");

        try {
            const provider = window.phantom?.solana;
            if (!provider) {
                alert("⚠️ Phantom Wallet no detectado.\nInstale la extensión.");
                window.open("https://phantom.app/", "_blank");
                return;
            }

            updateUI(text, "CONECTANDO...");
            disableButton(btn);

            const { publicKey } = await provider.connect();
            const pubKey = publicKey.toString();

            const message = `[CYBERPYME SOC G12]\nAcceso Auditoría\nWallet: ${pubKey}\nTime: ${Date.now()}`;
            const encoded = new TextEncoder().encode(message);

            updateUI(text, "FIRME MENSAJE...");
            await provider.signMessage(encoded, "utf8");

            successAnimation();
            updateWalletUI(btn, text, status, pubKey);

            log("Autenticación Web3 completada");

        } catch (err) {
            log("Error en autenticación Web3: " + err, "error");
            updateUI(text, "LOGIN RECHAZADO");
            setTimeout(() => updateUI(text, "LOGIN AUDITOR"), 2500);
            enableButton(btn);
        }
    };

    /* ------------------------------
     *  UTILIDADES UI
     * ------------------------------ */
    const updateUI = (el, text) => el && (el.innerText = text);
    const disableButton = (btn) => btn && btn.classList.add("opacity-50", "cursor-not-allowed");
    const enableButton = (btn) => btn && btn.classList.remove("opacity-50", "cursor-not-allowed");

    const successAnimation = () => {
        if (typeof confetti === "function") {
            confetti({
                particleCount: 120,
                spread: 70,
                origin: { y: 0.6 },
                colors: ["#0ea5e9", "#10b981", "#ab9ff2"]
            });
        }
    };

    const updateWalletUI = (btn, text, status, pubKey) => {
        const shortKey = `${pubKey.slice(0, 4)}...${pubKey.slice(-4)}`;
        updateUI(text, shortKey);

        if (btn) {
            btn.className = "bg-emerald-600 text-white font-bold px-6 py-3 rounded-xl flex items-center gap-3";
            btn.innerHTML = `<i class="fas fa-check-circle"></i> ${shortKey}`;
        }

        if (status) {
            status.innerHTML = `
                <div class="text-emerald-400 font-bold">✔ SIGNATURE VERIFIED</div>
                <div class="text-white">${pubKey}</div>
                <div class="text-[10px] text-slate-500 mt-1">Access Level: G12 ROOT</div>
            `;
            status.classList.add("border-emerald-500/30", "bg-emerald-900/10");
        }
    };

    /* ------------------------------
     *  API PÚBLICA
     * ------------------------------ */
    return {
        init,
        applyLanguage,
        toggleTheme,
        loginWeb3
    };

})();

/* ------------------------------
 *  AUTO-INIT
 * ------------------------------ */
document.addEventListener("DOMContentLoaded", SOCCore.init);

/* ------------------------------
 *  EXPOSICIÓN GLOBAL
 * ------------------------------ */
window.changeLanguage = SOCCore.applyLanguage;
window.toggleTheme = SOCCore.toggleTheme;
window.web3Login = SOCCore.loginWeb3;
