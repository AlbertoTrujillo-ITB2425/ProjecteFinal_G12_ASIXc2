/**
 * CYBERPYME SOC - Language Dictionary Module
 * Responsabilidad única: Almacenar y devolver traducciones.
 */

const translations = {
    es: {
        "sys_status_waiting": "ESPERANDO INPUT...",
        "label_target": "Target Asset (IP / Domain)",
        "mode_quick": "⚡ Quick (Puertos Comunes)",
        "mode_full": "🛡️ Full (Detección Versiones)",
        "btn_start": "INICIAR ESCANEO",
        "stat_last_target": "Último Objetivo",
        "stat_risk": "Riesgo Detectado",
        "stat_time": "Tiempo Real",
        "card_security_posture": "Postura de Seguridad Actual",
        "panel_recommendations": "Acciones Recomendadas",
        "panel_ai_title": "Análisis de Inteligencia Artificial",
        "console_live": "Live Output Stream",
        "console_timer": "TIEMPO REAL:",
        "btn_pdf": "GENERAR INFORME PDF",
        "btn_reset": "REINICIAR SISTEMA",
        "nav_login": "LOGIN AUDITOR"
    },
    en: {
        "sys_status_waiting": "WAITING FOR INPUT...",
        "label_target": "Target Asset (IP / Domain)",
        "mode_quick": "⚡ Quick (Common Ports)",
        "mode_full": "🛡️ Full (Version Detection)",
        "btn_start": "LAUNCH AUDIT",
        "stat_last_target": "Last Target",
        "stat_risk": "Detected Risk",
        "stat_time": "Real Time",
        "card_security_posture": "Current Security Posture",
        "panel_recommendations": "Recommended Actions",
        "panel_ai_title": "AI Intelligence Analysis",
        "console_live": "Live Output Stream",
        "console_timer": "REAL TIME:",
        "btn_pdf": "GENERATE PDF REPORT",
        "btn_reset": "RESET SYSTEM",
        "nav_login": "AUDITOR LOGIN"
    },
    ca: {
        "sys_status_waiting": "ESPERANT ENTRADA...",
        "label_target": "Actiu Objectiu (IP / Domini)",
        "mode_quick": "⚡ Ràpid (Ports Comuns)",
        "mode_full": "🛡️ Complet (Detecció Versió)",
        "btn_start": "INICIAR ESCANEIG",
        "stat_last_target": "Últim Objectiu",
        "stat_risk": "Risc Detectat",
        "stat_time": "Temps Real",
        "card_security_posture": "Postura de Seguretat Actual",
        "panel_recommendations": "Accions Recomanades",
        "panel_ai_title": "Anàlisi d'Intel·ligència Artificial",
        "console_live": "Sortida en Viu",
        "console_timer": "TEMPS REAL:",
        "btn_pdf": "GENERAR INFORME PDF",
        "btn_reset": "REINICIAR SISTEMA",
        "nav_login": "ACCÉS AUDITOR"
    }
};

// Función pública para obtener traducciones
function getTranslation(key, lang = 'es') {
    if (!translations[lang]) lang = 'es';
    return translations[lang][key] || key; // Devuelve la clave si no encuentra traducción
}

// Exponer solo lo necesario globalmente si se requiere acceso directo desde HTML inline
window.getTranslation = getTranslation;
window.translations = translations; 
