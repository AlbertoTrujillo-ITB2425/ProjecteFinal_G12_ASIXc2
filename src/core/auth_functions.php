<?php
/**
 * CYBERPYME SOC - Librería de Funciones de Autenticación
 */
require_once __DIR__ . '/db.php';

// 1. UTILIDADES DE CONFIGURACIÓN
function getSOCConfig($key, $default = null) {
    static $config = null;
    if ($config === null) {
        $paths = [dirname(__DIR__) . '/.env', __DIR__ . '/.env', '/var/www/html/.env'];
        foreach ($paths as $path) {
            if (file_exists($path)) { $config = parse_ini_file($path); break; }
        }
        $config = $config ?: [];
    }
    return $config[$key] ?? $default;
}

// 2. GESTIÓN DE SESIONES
function createSOCSession($user, $pdo) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'] ?? 'user';

    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
}

// 3. LÓGICA DE REGISTRO/LOGIN SOCIAL (SSO)
function processSocialLogin($email, $name, $provider, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $sql = "INSERT INTO users (name, email, provider, role, status, created_at) VALUES (?, ?, ?, 'user', 'active', NOW())";
        $pdo->prepare($sql)->execute([$name, $email, $provider]);
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    }
    createSOCSession($user, $pdo);
}

// 4. PETICIONES CURL PARA OAUTH
function fetchOAuthData($url, $postData = null, $token = null) {
    $ch = curl_init($url);
    $options = [CURLOPT_RETURNTRANSFER => true];
    
    if ($postData) {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($postData);
    }
    if ($token) {
        $options[CURLOPT_HTTPHEADER] = ['Authorization: Bearer ' . $token];
    }
    
    curl_setopt_array($ch, $options);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}
