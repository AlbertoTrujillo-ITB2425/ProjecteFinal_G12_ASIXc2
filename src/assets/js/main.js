/**
 * CYBERPYME SOC - Main Logic v9.3 (Final Theme Fix)
 */

const SOCCore = (() => {
    const LANG_KEY = "soc_lang";
    const THEME_KEY = "soc_theme";

    // Inicialización
    const init = () => {
        // Recuperar preferencias guardadas o usar defecto
        const savedLang = localStorage.getItem(LANG_KEY) || "es";
        const savedTheme = localStorage.getItem(THEME_KEY) || "dark"; 
        
        applyLanguage(savedLang);
        applyTheme(savedTheme);
        
        console.log("[SOC CORE] Iniciado.");
    };

    // Lógica de Idioma
    const applyLanguage = (lang) => {
        if (!['es', 'en', 'ca'].includes(lang)) lang = 'es';
        localStorage.setItem(LANG_KEY, lang);

        // Actualizar botón del header
        const btn = document.getElementById("current-lang-text");
        if (btn) btn.innerText = lang.toUpperCase();

        // Traducir elementos con data-i18n
        document.querySelectorAll("[data-i18n]").forEach(el => {
            const key = el.dataset.i18n;
            // Verificamos si la función existe en languages.js
            if (typeof window.getTranslation === 'function') {
                const text = window.getTranslation(key, lang);
                if (text && text !== key) {
                    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                        el.placeholder = text;
                    } else {
                        el.innerText = text;
                    }
                }
            }
        });

        // Actualizar selects específicos si existen
        const modeSelect = document.getElementById('type');
        if (modeSelect && typeof window.getTranslation === 'function') {
            modeSelect.options[0].text = window.getTranslation('mode_quick', lang);
            modeSelect.options[1].text = window.getTranslation('mode_full', lang);
        }
    };

    // Lógica de Tema (Corregida)
    const applyTheme = (theme) => {
        const html = document.documentElement;
        const isDark = theme === 'dark';
        
        localStorage.setItem(THEME_KEY, theme);

        // Añadir o quitar la clase 'dark' del HTML
        if (isDark) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
        
        // Opcional: Log para depuración
        // console.log(`Tema aplicado: ${theme}. Clase dark presente: ${html.classList.contains('dark')}`);
    };

    // Función pública para cambiar tema
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

// Exponer funciones globalmente para que onclick="..." funcione
window.changeLanguage = SOCCore.changeLanguage;
window.toggleTheme = SOCCore.toggleTheme;

// Iniciar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", SOCCore.init);
