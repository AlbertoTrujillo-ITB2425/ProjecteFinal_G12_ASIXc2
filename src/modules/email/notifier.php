    /**
     * Enviar email usando mail() de PHP (Postfix en el sistema)
     */
    private static function sendMail(string $to, string $subject, string $bodyHtml, string $bodyText = ''): bool {
        $from = self::$config['from_email'] ?? 'noreply@cyberpyme.es';
        $fromName = self::$config['from_name'] ?? 'CYBERPYME SOC';
        
        // Si no hay versión texto, creamos una básica quitando tags HTML
        if (empty($bodyText)) {
            $bodyText = strip_tags($bodyHtml);
        }

        // Generar boundary único
        $boundary = md5(time() . rand());

        $headers = [
            "From: {$fromName} <{$from}>",
            "Reply-To: {$from}",
            "X-Mailer: CYBERPYME-SOC/1.0",
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\""
        ];
        
        $headerStr = implode("\r\n", $headers);
        
        // Cuerpo Multiparte (Texto + HTML)
        $message = "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $bodyText . "\r\n\r\n";
        
        $message .= "--{$boundary}\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= $bodyHtml . "\r\n\r\n";
        
        $message .= "--{$boundary}--";

        $result = @mail($to, $subject, $message, $headerStr);
        
        if (getenv('APP_DEBUG') === 'true') {
            error_log("[SOC_EMAIL] To:{$to} Subject:{$subject} Result:" . ($result ? 'OK' : 'FAIL'));
        }
        
        return $result !== false;
    }

    /**
     * 📧 NOTIFICACIÓN: Login de usuario
     */
    public static function notifyLogin(array $userData): bool {
        if (!(self::$config['enabled']['login'] ?? true)) return true;
        // ... (mantén tu lógica de rate limit) ...
        if (!self::checkRateLimit('login')) return false;
        
        self::init();
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');
        
        $subject = "[SOC] Login: " . ($userData['success'] ? 'EXITO' : 'FALLO') . " - " . ($userData['email'] ?? 'unknown');
        
        // Usamos la plantilla HTML existente
        $bodyHtml = self::renderTemplate('login', [
            'user' => $userData['username'] ?? 'unknown',
            'email' => $userData['email'] ?? 'N/A',
            'ip' => $ip,
            'user_agent' => 'Web Browser',
            'timestamp' => $timestamp,
            'success' => $userData['success'] ?? true,
        ]);

        // IMPORTANTE: Enviar SIEMPRE al admin/local para que socemail.php lo capture
        // No enviar al usuario final aquí si quieres centralizar logs en root
        $adminEmail = self::$config['admin_emails'][0] ?? 'root@localhost';
        
        return self::sendMail($adminEmail, $subject, $bodyHtml);
    }
