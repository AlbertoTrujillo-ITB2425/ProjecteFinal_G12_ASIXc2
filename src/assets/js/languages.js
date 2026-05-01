/**
 * CYBERPYME SOC - Diccionario de Traducción G12 y Motor i18n
 */

const translations = {
    es: {
        // --- NAVBAR & GENERAL ---
        "nav_login": "LOGIN AUDITOR",
        "nav_active": "SOC G12 LIVE ENGINE",
        "footer_rights": "© 2026 CYBERPYME SOC G12. ASEGURANDO EL FUTURO.",

        // --- INDEX.PHP ---
        "hero_tag": "G12 NEXT-GEN SOC",
        "hero_title": "Auditoría <br><span class='text-sky-500 italic'>Inteligente.</span>",
        "hero_desc": "Defensa proactiva y monitorización de activos potenciada por AI-Engine Qwen2.5.",
        "card_audit_title": "AUDIT ENGINE",
        "card_audit_desc": "Mapeo de red y detección de vectores de ataque mediante IA.",
        "card_audit_btn": "INICIAR ESCÁNER",
        "card_threat_title": "THREAT INTEL",
        "card_threat_desc": "Detección de intrusiones y monitorización de tráfico en tiempo real.",
        "card_threat_btn": "MONITOR EN VIVO",
        "status_identity": "IDENTIDAD DEL AUDITOR",
        "status_awaiting": "Esperando autenticación Web3...",
        "status_health": "SALUD DE LA RED",

        // --- SCANNER.PHP (CAPTURA 2) ---
        "console_title": "G12 SECURITY CONSOLE",
        "params_title": "PARÁMETROS DE AUDITORÍA",
        "params_host": "HOST OBJETIVO",
        "params_profile": "PERFIL DE ESCANEO",
        "params_lang": "IDIOMA DE RESPUESTA",
        "btn_execute": "EJECUTAR AUDITORÍA",
        "task_progress": "PROGRESO DE LA TAREA",
        "task_status": "PROCESANDO PAQUETES DE RED...",
        "btn_pdf": "GENERAR PDF EJECUTIVO",
        "ai_title": "NEURAL INSIGHTS: QWEN2.5 SECURITY",
        "ai_resp_lang": "IDIOMA DE RESPUESTA:",
        "console_output_title": "SALIDA DE CONSOLA",
        "system_status": "ESTADO DEL SISTEMA: ACTIVO",
        
        // --- DINÁMICOS (JS) ---
        "audit_start": "[SISTEMA] Iniciando auditoría sobre:",
        "audit_analyzing": "[IA] Analizando vectores de ataque..."
    },
    en: {
        // --- NAVBAR & GENERAL ---
        "nav_login": "AUDITOR LOGIN",
        "nav_active": "SOC G12 LIVE ENGINE",
        "footer_rights": "© 2026 CYBERPYME SOC G12. SECURING THE FUTURE.",

        // --- INDEX.PHP ---
        "hero_tag": "G12 NEXT-GEN SOC",
        "hero_title": "Intelligent <br><span class='text-sky-500 italic'>Audit.</span>",
        "hero_desc": "Proactive defense and asset monitoring powered by AI-Engine Qwen2.5.",
        "card_audit_title": "AUDIT ENGINE",
        "card_audit_desc": "Network mapping and attack vector detection using AI.",
        "card_audit_btn": "LAUNCH SCANNER",
        "card_threat_title": "THREAT INTEL",
        "card_threat_desc": "Intrusion detection and real-time traffic monitoring.",
        "card_threat_btn": "LIVE MONITOR",
        "status_identity": "AUDITOR IDENTITY",
        "status_awaiting": "Awaiting Web3 Authentication...",
        "status_health": "NETWORK HEALTH",

        // --- SCANNER.PHP ---
        "console_title": "G12 SECURITY CONSOLE",
        "params_title": "AUDIT PARAMETERS",
        "params_host": "TARGET HOST",
        "params_profile": "SCAN PROFILE",
        "params_lang": "RESPONSE LANGUAGE",
        "btn_execute": "EXECUTE AUDIT",
        "task_progress": "TASK PROGRESS",
        "task_status": "PROCESSING NETWORK PACKETS...",
        "btn_pdf": "GENERATE EXECUTIVE PDF",
        "ai_title": "NEURAL INSIGHTS: QWEN2.5 SECURITY",
        "ai_resp_lang": "RESPONSE LANGUAGE:",
        "console_output_title": "CONSOLE OUTPUT",
        "system_status": "SYSTEM STATUS: ACTIVE",

        // --- DINÁMICOS (JS) ---
        "audit_start": "[SYSTEM] Starting audit on:",
        "audit_analyzing": "[AI] Analyzing attack vectors..."
    },
    ca: {
        // --- NAVBAR & GENERAL ---
        "nav_login": "ACCÉS AUDITOR",
        "nav_active": "SOC G12 LIVE ENGINE",
        "footer_rights": "© 2026 CYBERPYME SOC G12. ASSEGURANT EL FUTUR.",

        // --- INDEX.PHP ---
        "hero_tag": "G12 NEXT-GEN SOC",
        "hero_title": "Auditoria <br><span class='text-sky-500 italic'>Intel·ligent.</span>",
        "hero_desc": "Defensa proactiva i monitorització d'actius potenciada per AI-Engine Qwen2.5.",
        "card_audit_title": "AUDIT ENGINE",
        "card_audit_desc": "Mapatge de xarxa i detecció de vectors d'atac mitjançant IA.",
        "card_audit_btn": "INICIAR ESCÀNER",
        "card_threat_title": "THREAT INTEL",
        "card_threat_desc": "Detecció d'intrusions i monitorització de trànsit en temps real.",
        "card_threat_btn": "MONITOR EN VIU",
        "status_identity": "IDENTITAT DE L'AUDITOR",
        "status_awaiting": "Esperant autenticació Web3...",
        "status_health": "SALUT DE LA XARXA",

        // --- SCANNER.PHP ---
        "console_title": "G12 SECURITY CONSOLE",
        "params_title": "PARÀMETRES D'AUDITORIA",
        "params_host": "HOST OBJECTIU",
        "params_profile": "PERFIL D'ESCANEIG",
        "params_lang": "IDIOMA DE RESPOSTA",
        "btn_execute": "EXECUTAR AUDITORIA",
        "task_progress": "PROGRÉS DE LA TASCA",
        "task_status": "PROCESSANT PAQUETS DE XARXA...",
        "btn_pdf": "GENERAR PDF EXECUTIU",
        "ai_title": "NEURAL INSIGHTS: QWEN2.5 SECURITY",
        "ai_resp_lang": "IDIOMA DE RESPOSTA:",
        "console_output_title": "SORTIDA DE CONSOLA",
        "system_status": "ESTAT DEL SISTEMA: ACTIU",

        // --- DINÁMICOS (JS) ---
        "audit_start": "[SISTEMA] Iniciant auditoria sobre:",
        "audit_analyzing": "[IA] Analitzant vectors d'atac..."
    }
};

// ====== MOTOR DE TRADUCCIÓN ======

function changeLanguage(lang) {
    // 1. Guardar la preferencia del usuario en el navegador para que persista al recargar
    localStorage.setItem('soc_language', lang);

    // 2. Cambiar el texto del botón del menú desplegable (si existe)
    const langText = document.getElementById('current-lang-text');
    if (langText) {
        langText.innerText = lang.toUpperCase();
    }

    // 3. Buscar todos los elementos HTML que tengan el atributo data-i18n
    const elements = document.querySelectorAll('[data-i18n]');
    
    // 4. Reemplazar el texto de cada elemento usando la clave del diccionario
    elements.forEach(el => {
        const key = el.getAttribute('data-i18n');
        
        // Verificamos si el idioma y la clave existen en nuestro diccionario
        if (translations[lang] && translations[lang][key]) {
            // Usamos innerHTML para respetar etiquetas como <br> o <span class="...">
            el.innerHTML = translations[lang][key];
        }
    });

    console.log("Sistema traducido al: " + lang);
}

// ====== INICIALIZACIÓN AUTOMÁTICA ======
// Cuando la página termine de cargar, aplicamos el idioma guardado automáticamente
document.addEventListener('DOMContentLoaded', () => {
    // Obtiene el idioma guardado en localStorage o usa 'es' (español) por defecto
    const savedLang = localStorage.getItem('soc_language') || 'es';
    changeLanguage(savedLang);
});
