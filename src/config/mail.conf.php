<?php
/**
 * CYBERPYME SOC - Configuración de Correo y Notificaciones
 * Ubicación: src/config/mail.conf.php
 * 
 * IMPORTANTE: 
 * - 'admin_emails' debe apuntar a un usuario LOCAL del sistema Linux 
 *   (ej: 'root@localhost' o 'ubuntu@localhost') para que Postfix 
 *   guarde el correo en /var/mail/USUARIO y socemail.php pueda leerlo.
 */

return [
    // --- ESTADO GLOBAL ---
    'enabled' => [
        'login'         => true,   // Notificar cada login (éxito/fallo)
        'user_create'   => true,   // Notificar nuevos registros
        'vulnerability' => true,   // Notificar alertas de vulnerabilidades
        'system_alert'  => true,   // Alertas críticas del sistema
    ],

    // --- DESTINATARIOS ---
    // Usa '@localhost' para entrega local vía Postfix (MBOX)
    'admin_emails' => [
        'root@localhost', 
        // Si tu usuario de ubuntu es diferente y quieres recibirlos ahí:
        // 'ubuntu@localhost', 
    ],

    // --- REMITENTE ---
    'from_email' => 'noreply@cyberpyme.es',
    'from_name'  => 'CYBERPYME SOC System',

    // --- ASUNTOS PERSONALIZADOS ---
    'subjects' => [
        'login'       => '[SOC ALERT] Evento de Autenticación',
        'user_create' => '[SOC INFO] Nuevo Usuario Registrado',
        'vuln_low'    => '[SOC LOW] Vulnerabilidad Detectada',
        'vuln_medium' => '[SOC MEDIUM] Vulnerabilidad Moderada',
        'vuln_high'   => '[SOC HIGH] VULNERABILIDAD CRÍTICA',
        'vuln_critical' => '[SOC CRITICAL] COMPROMISO INMINENTE',
    ],

    // --- RATE LIMITING (Anti-Spam) ---
    // Máximo de emails por tipo por hora. 
    // Pon 0 para ilimitado (no recomendado en producción).
    'rate_limit' => [
        'login'       => 60,  // Máx 60 notificaciones de login por hora
        'user_create' => 10,  // Máx 10 registros por hora
        'vulnerability' => 0, // Sin límite para vulnerabilidades (crítico)
    ],

    // --- RUTAS Y DEBUG ---
    // Ruta al archivo temporal para contar rate limits
    'rate_limit_file' => '/tmp/soc_email_ratelimit.json',
    
    // Si 'APP_DEBUG' es 'true', se escribirán detalles en error_log de Apache
    'debug_mode' => (getenv('APP_DEBUG') === 'true'),
];
