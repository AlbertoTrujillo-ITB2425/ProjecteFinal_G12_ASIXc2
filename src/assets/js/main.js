/**
 * CYBERPYME SOC - Main Logic v9.1 (Fixed Global Exposure)
 */

const SOCCore = (() => {
    const LANG_KEY = "soc_lang";
    const THEME_KEY = "soc_theme";

    // 1. Inicialización
    const init = () => {
        const savedLang = localStorage.getItem(LANG_KEY) || "es";
        const savedTheme = localStorage.getItem(THEME_KEY) || "dark"; 
        
        applyLanguage(savedLang);
        applyTheme(savedTheme);
    };

    // 2. Lógica de Idioma
    const applyLanguage = (lang) => {
        if (!['es', 'en', 'ca'].includes(lang)) lang = 'es';
        localStorage.setItem(LANG_KEY, lang);

        const btn = document.getElementById("current-lang-text");
        if (btn) btn.innerText = lang.toUpperCase();

        // Traducir elementos data-i18n
        document.querySelectorAll("[data-i18n]").forEach(el => {
            const key = el.dataset.i18n;
            // Asumimos que languages.js expone window.getTranslation
            const text = window.getTranslation ? window.getTranslation(key, lang) : key;
            if (text && text !== key) {
                if (el.tagName === 'INPUT') el.placeholder = text;
                else el.innerText = text;
            }
        });
    };

    // 3. Lógica de Tema (La clave del Sol/Luna)
    const applyTheme = (theme) => {
        const html = document.documentElement;
        const isDark = theme === 'dark';
        
        localStorage.setItem(THEME_KEY, theme);

        if (isDark) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    };

    // 4. Función que llama el botón onclick
    const toggleTheme = () => {
        const currentTheme = localStorage.getItem(THEME_KEY) || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
    };

    return {
        init,
        changeLanguage: applyLanguage,
        toggleTheme
    };
})();

// CRÍTICO: Exponer las funciones al objeto global window ANTES de que el usuario haga clic
window.changeLanguage = SOCCore.changeLanguage;
window.toggleTheme = SOCCore.toggleTheme;

// Iniciar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", SOCCore.init);
