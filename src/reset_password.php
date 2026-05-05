<?php
session_start();
require_once 'db_conn.php'; // Asegúrate de que este archivo tiene tu conexión PDO

$message_status = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // 1. Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // 2. Guardar token en la DB
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([$token, $expires, $email]);

        // 3. Configuración de envío
        $subject = "=?UTF-8?B?" . base64_encode("Restablecer Clave - CyberPyme SOC") . "?=";
        $reset_link = "http://172.31.87.122/reset_password.php?token=" . $token;
        
        $message = "SISTEMA DE GESTIÓN DE IDENTIDADES SOC\n";
        $message .= "------------------------------------\n";
        $message .= "Se ha solicitado un restablecimiento de contraseña.\n";
        $message .= "Enlace seguro: " . $reset_link . "\n\n";
        $message .= "Si no has sido tú, ignora este mensaje. El enlace expira en 1 hora.";

        // Cabeceras profesionales para evitar SPAM
        $headers = [
            "From" => "CyberPyme SOC <no-reply@cyberpyme.es>",
            "Reply-To" => "soporte@cyberpyme.es",
            "MIME-Version" => "1.0",
            "Content-Type" => "text/plain; charset=UTF-8",
            "X-Priority" => "1 (Highest)",
            "X-Mailer" => "PHP/" . phpversion()
        ];

        // Forzamos la configuración de Postfix interno
        ini_set("SMTP", "s10_postfix");
        ini_set("smtp_port", "25");

        // Enviamos el correo con el parámetro -f (Envelope Sender)
        // Esto es CRUCIAL para que los servidores de Google no lo rechacen
        if (mail($email, $subject, $message, $headers, "-f no-reply@cyberpyme.es")) {
            $message_status = "success";
        } else {
            $message_status = "error_mail";
        }
    } else {
        // Por seguridad, no decimos si el email existe o no
        $message_status = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperación | CyberPyme SOC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-slate-900 border border-sky-500/30 p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-6">
            <i class="fas fa-shield-halved text-4xl text-sky-500 mb-4"></i>
            <h2 class="text-2xl font-bold">CyberPyme <span class="text-sky-500">SOC</span></h2>
            <p class="text-slate-400 text-sm">Gestión de Acceso Crítico</p>
        </div>

        <?php if ($message_status == "success"): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-lg text-sm mb-6">
                <i class="fas fa-check-circle mr-2"></i> Si el correo existe, recibirás un enlace pronto.
            </div>
        <?php elseif ($message_status == "error_mail"): ?>
            <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-lg text-sm mb-6">
                <i class="fas fa-bolt mr-2"></i> Error en el motor de correo. Revisa logs.
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Email del Auditor</label>
                <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-800 rounded-lg py-3 px-4 outline-none focus:border-sky-500 transition" placeholder="auditor@itb.cat">
            </div>
            <button type="submit" class="w-full bg-sky-600 hover:bg-sky-500 py-3 rounded-lg font-bold transition">
                Enviar Enlace de Recuperación
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="auth.php" class="text-slate-500 hover:text-white text-xs"><i class="fas fa-arrow-left mr-2"></i>Volver al Terminal</a>
        </div>
    </div>
</body>
</html>
