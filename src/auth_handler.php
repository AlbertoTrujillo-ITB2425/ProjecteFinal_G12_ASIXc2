<?php
/**
 * CYBERPYME SOC - Motor de Autenticación Unificado
 * Versión Optimizada: Uso de sesiones para Callback limpio y compatibilidad con Docker.
 */
session_start();

// 1. CARGA DE CONEXIÓN A BASE DE DATOS
require_once __DIR__ . '/db_conn.php';

/**
 * Función de carga del .env con soporte para rutas Docker y Host
 */
function getSOCConfig($key, $default = null) {
    static $config = null;
    if ($config === null) {
        $paths = [
            dirname(__DIR__) . '/.env',   // Raíz del proyecto
            __DIR__ . '/.env',            // Carpeta src
            '/var/www/html/.env'          // Ruta estándar en contenedores
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $config = parse_ini_file($path);
                if ($config) break;
            }
        }

        if (!$config) {
            error_log("[SOC] CRÍTICO: No se pudo cargar el archivo .env desde ninguna ruta.");
            $config = [];
        }
    }
    return $config[$key] ?? $default;
}

// 2. CAPTURAR PETICIONES GET (SSO Y LOGOUT)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // A. LOGOUT
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        session_destroy();
        header("Location: auth.php");
        exit;
    }

    // B. INICIO DE FLUJO SSO
    if (isset($_GET['provider']) && !isset($_GET['code'])) {
        $provider = $_GET['provider'];
        $_SESSION['sso_provider'] = $provider; // Guardamos quién inició la petición

        if ($provider === 'microsoft') {
            $clientId = getSOCConfig('MS_CLIENT_ID');
            $tenantId = getSOCConfig('MS_TENANT_ID', 'common');
            $redirect = getSOCConfig('MS_REDIRECT_URI');

            $params = [
                'client_id'     => $clientId,
                'response_type' => 'code',
                'redirect_uri'  => $redirect,
                'scope'         => 'openid profile email User.Read',
                'response_mode' => 'query'
            ];
            $url = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?" . http_build_query($params);
            header("Location: $url");
            exit;
        }

        if ($provider === 'google') {
            $clientId = getSOCConfig('GOOGLE_CLIENT_ID');
            $redirect = getSOCConfig('GOOGLE_REDIRECT_URI');

            $params = [
                'client_id'     => $clientId,
                'redirect_uri'  => $redirect,
                'response_type' => 'code',
                'scope'         => 'openid profile email',
                'access_type'   => 'offline',
                'prompt'        => 'select_account'
            ];
            $url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
            header("Location: $url");
            exit;
        }
    }

    // C. CALLBACK DE SSO (Retorno de Google/Microsoft)
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $provider = $_SESSION['sso_provider'] ?? null;

        if (!$provider) {
            error_log("[SOC] ERROR: Callback recibido sin proveedor en sesión.");
            header("Location: auth.php?error=session_lost");
            exit;
        }

        // Configuración según el proveedor recuperado de la sesión
        if ($provider === 'google') {
            $token_url   = 'https://oauth2.googleapis.com/token';
            $profile_url = 'https://www.googleapis.com/oauth2/v3/userinfo';
            $cid = getSOCConfig('GOOGLE_CLIENT_ID');
            $sec = getSOCConfig('GOOGLE_CLIENT_SECRET');
            $red = getSOCConfig('GOOGLE_REDIRECT_URI');
        } else {
            $tenantId    = getSOCConfig('MS_TENANT_ID', 'common');
            $token_url   = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
            $profile_url = 'https://graph.microsoft.com/v1.0/me';
            $cid = getSOCConfig('MS_CLIENT_ID');
            $sec = getSOCConfig('MS_CLIENT_SECRET');
            $red = getSOCConfig('MS_REDIRECT_URI');
        }

        // Intercambio de código por token
        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $cid,
            'client_secret' => $sec,
            'redirect_uri'  => $red
        ]));
        
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($res['access_token'])) {
            error_log("[SOC] Error en Token Exchange: " . json_encode($res));
            header("Location: auth.php?error=oauth_fail");
            exit;
        }

        // Obtener datos del perfil
        $ch = curl_init($profile_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $res['access_token']]);
        $profile = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $email = $profile['email'] ?? $profile['mail'] ?? $profile['userPrincipalName'] ?? null;
        $name  = $profile['name'] ?? $profile['displayName'] ?? 'Auditor SOC';

        if ($email) {
            unset($_SESSION['sso_provider']); // Limpiamos rastro del proveedor
            loginOrRegisterSocial($email, $name, $provider, $pdo);
            header("Location: index.php");
            exit;
        }

        header("Location: auth.php?error=profile_fail");
        exit;
    }
}

// 3. CAPTURAR PETICIONES POST (LOGIN MANUAL)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email  = trim($_POST['email'] ?? $_POST['username'] ?? '');
    $pass   = $_POST['password'] ?? null;

    if ($action === 'login_standard') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            createSession($user, $pdo);
            header("Location: index.php");
        } else {
            header("Location: auth.php?error=invalid_credentials");
        }
        exit;
    }
}

// 4. FUNCIONES AUXILIARES

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
        $sql = "INSERT INTO users (name, email, provider, role, status, created_at) 
                VALUES (?, ?, ?, 'user', 'active', NOW())";
        $pdo->prepare($sql)->execute([$name, $email, $provider]);
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
    }
    createSession($user, $pdo);
}

// Si llega aquí sin nada, al login
header("Location: auth.php");
exit;
