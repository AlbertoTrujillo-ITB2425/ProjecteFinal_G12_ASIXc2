/**
 * CYBERPYME SOC - Diccionario de Traducción Centralizado
 */

const translations = {
    es: {
        // Navegación y General
        "nav_login": "LOGIN AUDITOR",
        "nav_active": "SOC G12 LIVE ENGINE",
        "footer_rights": "© 2026 CYBERPYME SOC G12. ASEGURANDO EL FUTURO.",
        
        // Scanner Específico
        "sys_status_waiting": "ESPERANDO INPUT...",
        "sys_status_scanning": "ESCANEANDO RED...",
        "sys_status_complete": "ESCANEO COMPLETADO",
        "sys_status_error": "ERROR DE CONEXIÓN",
        
        "label_target": "Target Asset (IP / Domain)",
        "label_mode": "Modo de Escaneo",
        "mode_quick": "⚡ Quick (Puertos Comunes)",
        "mode_full": "🛡️ Full (Detección Versiones)",
        
        "btn_start": "INICIAR ESCANEO",
        "btn_scanning": "ESCANEANDO...",
        "btn_clear": "LIMPIAR",
        "btn_pdf": "GENERAR INFORME PDF",
        "btn_reset": "REINICIAR SISTEMA",
        
        "stat_last_target": "Último Objetivo",
        "stat_risk": "Riesgo Detectado",
        "stat_time": "Tiempo Real",
        
        "card_security_posture": "Postura de Seguridad Actual",
        "card_analyzing": "ANALIZANDO...",
        "card_waiting_summary": "Esperando resultados del escáner...",
        "card_ip_resolved": "IP Resuelta",
        
        "panel_recommendations": "Acciones Recomendadas",
        "panel_ai_title": "Análisis de Inteligencia Artificial",
        
        "console_live": "Live Output Stream",
        "console_timer": "TIEMPO REAL:",
        
        "alert_no_target": "Introduce un objetivo válido.",
        "alert_scan_failed": "FALLO CRÍTICO:",
        "alert_ai_error": "Error conectando con la IA."
    },
    en: {
        "nav_login": "AUDITOR LOGIN",
        "nav_active": "SOC G12 LIVE ENGINE",
        "footer_rights": "© 2026 CYBERPYME SOC G12. SECURING THE FUTURE.",
        
        "sys_status_waiting": "WAITING FOR INPUT...",
        "sys_status_scanning": "SCANNING NETWORK...",
        "sys_status_complete": "SCAN COMPLETED",
        "sys_status_error": "CONNECTION ERROR",
        
        "label_target": "Target Asset (IP / Domain)",
        "label_mode": "Scan Mode",
        "mode_quick": "⚡ Quick (Common Ports)",
        "mode_full": "🛡️ Full (Version Detection)",
        
        "btn_start": "LAUNCH AUDIT",
        "btn_scanning": "SCANNING...",
        "btn_clear": "CLEAR",
        "btn_pdf": "GENERATE PDF REPORT",
        "btn_reset": "RESET SYSTEM",
        
        "stat_last_target": "Last Target",
        "stat_risk": "Detected Risk",
        "stat_time": "Real Time",
        
        "card_security_posture": "Current Security Posture",
        "card_analyzing": "ANALYZING...",
        "card_waiting_summary": "Waiting for scanner results...",
        "card_ip_resolved": "Resolved IP",
        
        "panel_recommendations": "Recommended Actions",
        "panel_ai_title": "AI Intelligence Analysis",
        
        "console_live": "Live Output Stream",
        "console_timer": "REAL TIME:",
        
        "alert_no_target": "Please enter a valid target.",
        "alert_scan_failed": "CRITICAL FAILURE:",
        "alert_ai_error": "Error connecting to AI."
    },
    ca: {
        "nav_login": "ACCÉS AUDITOR",
        "nav_active": "SOC G12 LIVE ENGINE",
        "footer_rights": "© 2026 CYBERPYME SOC G12. ASSEGURANT EL FUTUR.",
        
        "sys_status_waiting": "ESPERANT ENTRADA...",
        "sys_status_scanning": "ESCANEJANT XARXA...",
        "sys_status_complete": "ESCANEI COMPLETAT",
        "sys_status_error": "ERROR DE CONNEXIÓ",
        
        "label_target": "Actiu Objectiu (IP / Domini)",
        "label_mode": "Mode d'Escaneig",
        "mode_quick": "⚡ Ràpid (Ports Comuns)",
        "mode_full": "🛡️ Complet (Detecció Versió)",
        
        "btn_start": "INICIAR ESCANEIG",
        "btn_scanning": "ESCANEJANT...",
        "btn_clear": "NETEJAR",
        "btn_pdf": "GENERAR INFORME PDF",
        "btn_reset": "REINICIAR SISTEMA",
        
        "stat_last_target": "Últim Objectiu",
        "stat_risk": "Risc Detectat",
        "stat_time": "Temps Real",
        
        "card_security_posture": "Postura de Seguretat Actual",
        "card_analyzing": "ANALITZANT...",
        "card_waiting_summary": "Esperant resultats de l'escàner...",
        "card_ip_resolved": "IP Resolta",
        
        "panel_recommendations": "Accions Recomanades",
        "panel_ai_title": "Anàlisi d'Intel·ligència Artificial",
        
        "console_live": "Sortida en Viu",
        "console_timer": "TEMPS REAL:",
        
        "alert_no_target": "Introdueix un objectiu vàlid.",
        "alert_scan_failed": "FALLADA CRÍTICA:",
        "alert_ai_error": "Error connectant amb la IA."
    }
};

// Función global para cambiar idioma
function setLanguage(lang) {
    if (!translations[lang]) lang = 'es'; // Fallback a español
    
    // Guardar preferencia
    localStorage.setItem('preferred_lang', lang);
    
    // Aplicar traducciones
    document.querySelectorAll('[data-i18n]').forEach(element => {
        const key = element.getAttribute('data-i18n');
        if (translations[lang][key]) {
            // Si es un input o placeholder, cambia eso, si no, el texto interno
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                if (element.hasAttribute('placeholder')) {
                    element.placeholder = translations[lang][key];
                } else {
                    element.value = translations[lang][key];
                }
            } else {
                element.innerText = translations[lang][key];
            }
        }
    });

    // Actualizar selects específicos si existen
    const modeSelect = document.getElementById('type');
    if (modeSelect) {
        modeSelect.options[0].text = translations[lang]['mode_quick'];
        modeSelect.options[1].text = translations[lang]['mode_full'];
    }
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem('preferred_lang') || 'es';
    setLanguage(savedLang);
});
