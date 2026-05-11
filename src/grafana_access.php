<?php
/**
 * SOC CYBERPYME - GATEKEEPER v3.1
 * Gestión de acceso seguro a la telemetría de Grafana.
 */

session_start();

// --- 1. VALIDACIÓN DE SESIÓN ---
// Evita que bots o usuarios no logueados siquiera lleguen a la comprobación de IP
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?error=session_expired");
    exit();
}

// --- 2. DETECCIÓN DE IP REAL (Soporte Multicapa: Cloudflare + Proxy) ---
function getClientIP() {
    // Prioridad 1: Cabecera específica de Cloudflare
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    // Prioridad 2: Cabecera estándar de Proxy (X-Forwarded-For)
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($forwardedIps[0]);
    }
    // Prioridad 3: Conexión directa
    return $_SERVER['REMOTE_ADDR'];
}

$userIp = getClientIP();

// --- 3. CARGA Y VALIDACIÓN DE WHITELIST ---
$whitelistFile = __DIR__ . '/whitelist.txt';
$isAllowed = false;

if (file_exists($whitelistFile)) {
    // Leemos el archivo omitiendo líneas vacías y comentarios (si usas #)
    $allowedIps = file($whitelistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    // Limpiamos espacios en blanco y filtramos posibles comentarios manuales
    foreach ($allowedIps as $line) {
        $cleanIp = trim(explode('#', $line)[0]);
        if ($userIp === $cleanIp) {
            $isAllowed = true;
            break;
        }
    }
} else {
    // Si el archivo no existe, por seguridad, denegamos todo y avisamos al log
    error_log("CRITICAL: Whitelist file missing at $whitelistFile");
}

// --- 4. MANEJO DE ACCESO DENEGADO ---
if (!$isAllowed) {
    // Registramos el evento en el log de errores de PHP/Nginx para auditoría
    error_log("SOC SECURITY ALERT: Unauthorized Grafana access attempt. User: {$_SESSION['user_name']}, IP: $userIp");
    
    include "includes/header.php";
    ?>
    <main class="flex items-center justify-center min-h-[70vh] p-4">
        <div class="glass-panel p-10 rounded-3xl border border-red-500/20 bg-red-500/5 max-w-lg w-full text-center shadow-2xl">
            <div class="w-20 h-20 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-shield-alt text-3xl text-red-500"></i>
            </div>
            <h2 class="text-2xl font-black text-red-500 uppercase tracking-tighter mb-2 italic">
                Acceso Bloqueado
            </h2>
            <p class="text-slate-400 text-sm mb-8 leading-relaxed">
                Tu dirección IP <span class="text-white font-mono bg-white/10 px-2 py-1 rounded"><?= htmlspecialchars($userIp) ?></span> 
                no figura en los registros de acceso autorizado para telemetría crítica.
            </p>
            <div class="flex flex-col gap-3">
                <a href="index.php" class="px-8 py-3 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl transition-all font-bold text-xs uppercase tracking-widest">
                    Regresar al Dashboard
                </a>
                <p class="text-[9px] text-slate-500 uppercase tracking-widest mt-4">
                    Ref ID: <?= md5($userIp . time()) ?>
                </p>
            </div>
        </div>
    </main>
    <?php
    include "includes/footer.php";
    exit();
}

// --- 5. REDIRECCIÓN EXITOSA ---
// Usamos una ruta absoluta interna para evitar que el navegador se confunda con el protocolo.
// Si Nginx está bien configurado, esto entrará por HTTPS automáticamente.
header("Location: /grafana/");
exit();
