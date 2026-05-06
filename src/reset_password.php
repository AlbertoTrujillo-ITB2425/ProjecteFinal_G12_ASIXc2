<?php
session_start();
// Asegúrate de que esta ruta sea correcta en tu servidor
require_once __DIR__ . '/core/db.php'; 

$message_status = "";
$step = "request"; // 'request' para pedir email, 'reset' para nueva password
$token = $_GET['token'] ?? null;

// --- VERIFICACIÓN DE TOKEN ---
if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $step = "reset";
    } else {
        $message_status = "error_token"; // Token inválido o expirado
        $step = "request";
    }
}

// --- PROCESAMIENTO DE FORMULARIOS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. SOLICITUD DE RESTABLECIMIENTO (Paso inicial)
    if (isset($_POST['email']) && $step == "request") {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $gen_token = bin2hex(random_bytes(32));
            $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

            // Guardar en DB
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $stmt->execute([$gen_token, $expires, $email]);

            // --- CONFIGURACIÓN CRÍTICA DE CORREO ---
            // Forzamos a PHP a usar el contenedor s10_postfix
            ini_set("SMTP", "s10_postfix"); 
            ini_set("smtp_port", "25");
            ini_set("sendmail_from", "no-reply@cyberpyme.es");

            $subject = "=?UTF-8?B?" . base64_encode("SOC G12: Acceso de Emergencia") . "?=";
            $reset_link = "http://172.31.87.122/reset_password.php?token=" . $gen_token;
            
            $message = "SISTEMA DE GESTIÓN DE IDENTIDADES SOC\n";
            $message .= "====================================\n";
            $message .= "Se ha solicitado una recuperación de clave.\n";
            $message .= "Pulse en el enlace para autorizar:\n\n";
            $message .= $reset_link . "\n\n";
            $message .= "Si no has sido tú, ignora este mensaje. El enlace expira en 1 hora.";

            $headers = "From: CyberPyme SOC <no-reply@cyberpyme.es>\r\n" .
                       "Reply-To: soporte@cyberpyme.es\r\n" .
                       "Content-Type: text/plain; charset=UTF-8\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            // Enviamos con el parámetro '-f' para validar el remitente ante el servidor Postfix
            if (mail($email, $subject, $message, $headers, "-f no-reply@cyberpyme.es")) {
                $message_status = "success";
            } else {
                $message_status = "error_mail";
            }
        } else {
            // Por seguridad, no revelamos si el email existe
            $message_status = "success";
        }
    }

    // 2. ACTUALIZACIÓN DE CONTRASEÑA (Paso final)
    if (isset($_POST['new_password']) && $token && $step == "reset") {
        $new_pass = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        
        // Actualizar password y limpiar el token usado
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        if ($stmt->execute([$new_pass, $token])) {
            $message_status = "pass_changed";
            $step = "done";
        } else {
            $message_status = "error_db";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación SOC | G12 Identity</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #020617; }
        .glass-panel { 
            background: rgba(15, 23, 42, 0.7); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(14, 165, 233, 0.2); 
        }
        .soc-input { 
            background: rgba(0, 0, 0, 0.2); 
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .soc-input:focus { 
            border-color: #0ea5e9; 
            box-shadow: 0 0 15px rgba(14, 165, 233, 0.1);
        }
    </style>
</head>
<body class="text-slate-300 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full glass-panel p-10 rounded-[2.5rem] shadow-2xl relative overflow-hidden">
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-sky-500/10 rounded-full blur-3xl"></div>

        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-500/10 mb-6">
                <i class="fas fa-user-shield text-3xl text-sky-500"></i>
            </div>
            <h2 class="text-2xl font-black text-white uppercase tracking-tight">Recuperación <span class="text-sky-500">SOC</span></h2>
            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.3em] mt-2">CyberPyme Security Protocol</p>
        </div>

        <?php if ($message_status == "success"): ?>
            <div class="mb-8 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs text-center animate-pulse">
                <i class="fas fa-satellite-dish mr-2"></i> Petición procesada. Revisa tu buzón de auditoría.
            </div>
        <?php elseif ($message_status == "error_mail"): ?>
            <div class="mb-8 p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs text-center">
                <i class="fas fa-bug mr-2"></i> Error: No se pudo contactar con el servidor s10_postfix.
            </div>
        <?php elseif ($message_status == "error_token"): ?>
            <div class="mb-8 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs text-center">
                <i class="fas fa-clock mr-2"></i> El enlace ha expirado o ya no es válido.
            </div>
        <?php elseif ($message_status == "pass_changed"): ?>
            <div class="mb-8 p-4 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 text-xs text-center">
                <i class="fas fa-check-double mr-2"></i> Credenciales actualizadas. Proceda al login.
            </div>
        <?php endif; ?>

        <?php if ($step == "request" && $message_status != "success"): ?>
            <form method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Email Registrado</label>
                    <input type="email" name="email" required 
                           class="w-full soc-input rounded-2xl px-5 py-4 text-white outline-none" 
                           placeholder="auditor@cyberpyme.es">
                </div>
                <button type="submit" 
                        class="w-full bg-sky-600 hover:bg-sky-500 text-white font-black py-4 rounded-2xl text-[11px] uppercase tracking-widest transition-all shadow-xl shadow-sky-500/20 active:scale-95">
                    Mandar Enlace de Acceso
                </button>
            </form>
        <?php endif; ?>

        <?php if ($step == "reset"): ?>
            <form method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Nueva Clave Maestra</label>
                    <input type="password" name="new_password" required minlength="6"
                           class="w-full soc-input rounded-2xl px-5 py-4 text-white outline-none" 
                           placeholder="••••••••">
                </div>
                <button type="submit" 
                        class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-4 rounded-2xl text-[11px] uppercase tracking-widest transition-all shadow-xl shadow-emerald-500/20 active:scale-95">
                    Confirmar Cambio de Identidad
                </button>
            </form>
        <?php endif; ?>

        <div class="mt-10 pt-6 border-t border-white/5 text-center">
            <a href="auth.php" class="text-[10px] font-black text-slate-500 hover:text-sky-500 uppercase tracking-widest transition-all">
                <i class="fas fa-terminal mr-2"></i> Volver al Login
            </a>
        </div>
    </div>

</body>
</html>
