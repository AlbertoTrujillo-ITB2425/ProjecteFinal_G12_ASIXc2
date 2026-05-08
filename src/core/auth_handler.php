<?php
/**
 * CYBERPYME SOC - Controlador de Acceso
 */
ob_start();
session_start();

require_once __DIR__ . '/auth_functions.php';

$LOGIN_URL = "../auth.php";
$DASHBOARD_URL = "../index.php";

// --- MANEJO DE ACCIONES GET ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 1. Logout
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        session_destroy();
        header("Location: $LOGIN_URL");
        exit;
    }

    // 2. Redirección a Proveedor SSO
    if (isset($_GET['provider']) && !isset($_GET['code'])) {
        $provider = $_GET['provider'];
        $_SESSION['sso_provider'] = $provider;

        $params = ($provider === 'google') ? [
            'client_id' => getSOCConfig('GOOGLE_CLIENT_ID'),
            'redirect_uri' => getSOCConfig('GOOGLE_REDIRECT_URI'),
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'prompt' => 'select_account'
        ] : [
            'client_id' => getSOCConfig('MS_CLIENT_ID'),
            'redirect_uri' => getSOCConfig('MS_REDIRECT_URI'),
            'response_type' => 'code',
            'scope' => 'openid profile email User.Read'
        ];

        $authUrl = ($provider === 'google') 
            ? "https://accounts.google.com/o/oauth2/v2/auth?" 
            : "https://login.microsoftonline.com/common/oauth2/v2.0/authorize?";

        header("Location: " . $authUrl . http_build_query($params));
        exit;
    }

    // 3. Callback de SSO (Retorno de Google/MS)
    if (isset($_GET['code'])) {
        $provider = $_SESSION['sso_provider'] ?? null;
        if (!$provider) { header("Location: $LOGIN_URL?error=session_lost"); exit; }

        $config = ($provider === 'google') ? [
            'url' => 'https://oauth2.googleapis.com/token',
            'profile' => 'https://www.googleapis.com/oauth2/v3/userinfo',
            'id' => getSOCConfig('GOOGLE_CLIENT_ID'),
            'sec' => getSOCConfig('GOOGLE_CLIENT_SECRET'),
            'red' => getSOCConfig('GOOGLE_REDIRECT_URI')
        ] : [
            'url' => "https://login.microsoftonline.com/common/oauth2/v2.0/token",
            'profile' => 'https://graph.microsoft.com/v1.0/me',
            'id' => getSOCConfig('MS_CLIENT_ID'),
            'sec' => getSOCConfig('MS_CLIENT_SECRET'),
            'red' => getSOCConfig('MS_REDIRECT_URI')
        ];

        $tokenData = fetchOAuthData($config['url'], [
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
            'client_id' => $config['id'],
            'client_secret' => $config['sec'],
            'redirect_uri' => $config['red']
        ]);

        if (!isset($tokenData['access_token'])) { header("Location: $LOGIN_URL?error=oauth_fail"); exit; }

        $profile = fetchOAuthData($config['profile'], null, $tokenData['access_token']);
        $email = $profile['email'] ?? $profile['mail'] ?? $profile['userPrincipalName'] ?? null;
        $name = $profile['name'] ?? $profile['displayName'] ?? 'Auditor SOC';

        if ($email) {
            processSocialLogin($email, $name, $provider, $pdo);
            header("Location: $DASHBOARD_URL");
        } else {
            header("Location: $LOGIN_URL?error=profile_fail");
        }
        exit;
    }
}

// --- MANEJO DE ACCIONES POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login_standard') {
        $email = trim($_POST['username'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && !empty($user['password']) && password_verify($pass, $user['password'])) {
            createSOCSession($user, $pdo);
            header("Location: $DASHBOARD_URL");
        } else {
            $err = ($user && empty($user['password'])) ? 'use_social' : 'invalid_credentials';
            header("Location: $LOGIN_URL?error=$err");
        }
        exit;
    }

    if ($action === 'register_user') {
        $name  = trim(($_POST['name'] ?? '') . ' ' . ($_POST['lastname'] ?? ''));
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if (strlen($pass) < 8) { header("Location: $LOGIN_URL?error=weak_password"); exit; }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) { header("Location: $LOGIN_URL?error=user_exists"); exit; }

        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'user', 'active', NOW())")
            ->execute([$name, $email, $hashed]);

        header("Location: $LOGIN_URL?success=registered");
        exit;
    }
}

header("Location: $LOGIN_URL");
ob_end_flush();
