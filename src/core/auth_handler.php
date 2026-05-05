<?php
/**
 * CYBERPYME SOC - Motor de Autenticación Unificado
 * Versión Optimizada y Corregida (Rutas relativas)
 */
session_start();

require_once __DIR__ . '/db.php';

// 1. CARGA DE CONFIGURACIÓN
function getSOCConfig($key, $default = null) {
    static $config = null;
    if ($config === null) {
        $paths = [dirname(__DIR__) . '/.env', __DIR__ . '/.env', '/var/www/html/.env'];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $config = parse_ini_file($path);
                break;
            }
        }
        if (!$config) $config = [];
    }
    return $config[$key] ?? $default;
}

// 2. RUTAS BASE
$LOGIN_URL = "../auth.php";
$DASHBOARD_URL = "../index.php";

// 3. CAPTURAR PETICIONES GET (SSO Y LOGOUT)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // A. LOGOUT
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        session_destroy();
        header("Location: $LOGIN_URL");
        exit;
    }

    // B. INICIO DE FLUJO SSO
    if (isset($_GET['provider']) && !isset($_GET['code'])) {
        $provider = $_GET['provider'];
        $_SESSION['sso_provider'] = $provider;

        if ($provider === 'microsoft') {
            $tenantId = 'common';
            $params = [
                'client_id'     => getSOCConfig('MS_CLIENT_ID'),
                'response_type' => 'code',
                'redirect_uri'  => getSOCConfig('MS_REDIRECT_URI'),
                'scope'         => 'openid profile email User.Read',
                'response_mode' => 'query'
            ];
            header("Location: https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?" . http_build_query($params));
            exit;
        }

        if ($provider === 'google') {
            $params = [
                'client_id'     => getSOCConfig('GOOGLE_CLIENT_ID'),
                'redirect_uri'  => getSOCConfig('GOOGLE_REDIRECT_URI'),
                'response_type' => 'code',
                'scope'         => 'openid profile email',
                'access_type'   => 'offline',
                'prompt'        => 'select_account'
            ];
            header("Location: https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params));
            exit;
        }
    }

    // C. CALLBACK DE SSO
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $provider = $_SESSION['sso_provider'] ?? null;

        if (!$provider) {
            header("Location: $LOGIN_URL?error=session_lost");
            exit;
        }

        if ($provider === 'google') {
            $token_url   = 'https://oauth2.googleapis.com/token';
            $profile_url = 'https://www.googleapis.com/oauth2/v3/userinfo';
            $cid = getSOCConfig('GOOGLE_CLIENT_ID');
            $sec = getSOCConfig('GOOGLE_CLIENT_SECRET');
            $red = getSOCConfig('GOOGLE_REDIRECT_URI');
        } else {
            $tenantId = 'common';
            $token_url   = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
            $profile_url = 'https://graph.microsoft.com/v1.0/me';
            $cid = getSOCConfig('MS_CLIENT_ID');
            $sec = getSOCConfig('MS_CLIENT_SECRET');
            $red = getSOCConfig('MS_REDIRECT_URI');
        }

        // Obtener Token
        $ch = curl_init($token_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'client_id'     => $cid,
                'client_secret' => $sec,
                'redirect_uri'  => $red
            ])
        ]);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($res['access_token'])) {
            header("Location: $LOGIN_URL?error=oauth_fail");
            exit;
        }

        // Obtener Perfil
        $ch = curl_init($profile_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $res['access_token']]
        ]);
        $profile = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $email = $profile['email'] ?? $profile['mail'] ?? $profile['userPrincipalName'] ?? null;
        $name  = $profile['name'] ?? $profile['displayName'] ?? 'Auditor SOC';

        if ($email) {
            unset($_SESSION['sso_provider']);
            loginOrRegisterSocial($email, $name, $provider, $pdo);
            header("Location: $DASHBOARD_URL");
            exit;
        }

        header("Location: $LOGIN_URL?error=profile_fail");
        exit;
    }
}

// 4. CAPTURAR PETICIONES POST (LOGIN Y REGISTRO MANUAL)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // LOGIN
    if ($action === 'login_standard') {
        $email = trim($_POST['username'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            createSession($user, $pdo);
            header("Location: $DASHBOARD_URL");
        } else {
            header("Location: $LOGIN_URL?error=invalid_credentials");
        }
        exit;
    }

    // REGISTRO (Faltaba en tu código original)
    if ($action === 'register_user') {
        $name  = trim($_POST['name'] ?? '') . ' ' . trim($_POST['lastname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (strlen($pass) < 8) {
            header("Location: $LOGIN_URL?error=weak_password");
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header("Location: $LOGIN_URL?error=user_exists");
            exit;
        }

        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'user', 'active', NOW())";
        $pdo->prepare($sql)->execute([trim($name), $email, $hashed]);

        header("Location: $LOGIN_URL?success=registered");
        exit;
    }
}

// 5. FUNCIONES AUXILIARES
function createSession($user, $pdo) {
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'] ?? 'user';

    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
}

function loginOrRegisterSocial($email, $name, $provider, $pdo) {
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
    createSession($user, $pdo);
}

// Fallback final
header("Location: $LOGIN_URL");
exit;
