<?php
// auth_handler.php
session_start();
require_once __DIR__ . '/db_conn.php';

/**
 * CARGA DINÁMICA DE CONFIGURACIÓN (.env)
 */
function getSOCEnv($key, $default = null) {
    $path = realpath(__DIR__ . '/.env') ?: realpath(dirname(__DIR__) . '/.env');
    if (!$path || !file_exists($path)) return $default;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue; 
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            if (trim($name) == $key) {
                return trim($value, " \t\n\r\0\x0B\"'");
            }
        }
    }
    return $default;
}

// ================================================================
// BLOQUE GET: RETORNO OAUTH (GOOGLE, MICROSOFT, AMAZON)
// ================================================================
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $app_url = getSOCEnv('APP_URL', 'https://cyberpyme.es');
    $redirect_uri = $app_url . '/auth_handler.php';

    // 1. DETECCIÓN DEL PROVEEDOR
    if (isset($_GET['scope']) && strpos($_GET['scope'], 'google') !== false) {
        $provider = 'google';
    } elseif (isset($_GET['session_state']) || isset($_GET['client_info']) || (isset($_GET['state']) && !isset($_GET['scope']))) {
        $provider = 'microsoft';
    } else {
        $provider = 'amazon';
    }

    // 2. CONFIGURACIÓN DE ENDPOINTS SEGÚN PROVEEDOR
    if ($provider === 'google') {
        $token_url     = 'https://oauth2.googleapis.com/token';
        $profile_url   = 'https://www.googleapis.com/oauth2/v3/userinfo';
        $client_id     = getSOCEnv('GOOGLE_CLIENT_ID');
        $client_secret = getSOCEnv('GOOGLE_CLIENT_SECRET');
    } elseif ($provider === 'microsoft') {
        $token_url     = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
        $profile_url   = 'https://graph.microsoft.com/v1.0/me';
        $client_id     = getSOCEnv('MICROSOFT_CLIENT_ID');
        $client_secret = getSOCEnv('MICROSOFT_CLIENT_SECRET');
    } else {
        $token_url     = 'https://api.amazon.com/auth/o2/token';
        $profile_url   = 'https://api.amazon.com/user/profile';
        $client_id     = getSOCEnv('AMAZON_CLIENT_ID');
        $client_secret = getSOCEnv('AMAZON_CLIENT_SECRET');
    }

    // Validación de seguridad básica
    if (!$client_id || !$client_secret) {
        die("Error crítico: Faltan credenciales (CLIENT_ID o CLIENT_SECRET) en el archivo .env para: $provider.");
    }

    // 3. INTERCAMBIO DE CÓDIGO POR TOKEN
    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri'  => $redirect_uri
    ]));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $token_data = json_decode($response, true);
    $access_token = $token_data['access_token'] ?? null;

    if ($access_token) {
        // 4. OBTENER PERFIL DEL USUARIO
        $ch = curl_init($profile_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Accept: application/json'
        ]);
        $profile_response = curl_exec($ch);
        curl_close($ch);

        $profile = json_decode($profile_response, true);

        // Mapeo unificado de campos (soporta los 3 proveedores)
        $email = $profile['email'] ?? $profile['mail'] ?? $profile['userPrincipalName'] ?? null;
        $name  = $profile['name'] ?? $profile['displayName'] ?? $profile['given_name'] ?? 'Usuario SOC';

        if ($email) {
            // 5. REGISTRO / LOGIN EN BASE DE DATOS
            loginOrRegisterSocial($email, $name, $provider, $pdo);
            
            // 6. REDIRECCIÓN Y CIERRE DE POPUP
            echo "<script>
                if (window.opener && !window.opener.closed) {
                    window.opener.location.href = 'index.php';
                    window.close();
                } else {
                    window.location.href = 'index.php';
                }
            </script>";
            exit;
        } else {
            die("Error: No se pudo obtener el email del perfil. Respuesta del proveedor: " . htmlspecialchars($profile_response));
        }
    } 
    
    // 7. MANEJO DE ERRORES DE API
    die("<h3>Error de autenticación con $provider</h3>
         <p><strong>HTTP Code:</strong> $http_code</p>
         <p><strong>Respuesta de la API:</strong> " . htmlspecialchars($response) . "</p>
         <p><em>Revisa la URI de redirección, los IDs y Secretos en tu panel de desarrollador.</em></p>");
}

// ================================================================
// BLOQUE POST: LOGIN / REGISTRO MANUAL (LOCAL)
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    
    $email    = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    $name     = $data['name'] ?? null;
    $provider = $data['provider'] ?? 'local';

    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Email requerido']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Login usuario existente
            if ($provider === 'local') {
                if (password_verify($password, $user['password'])) {
                    createSession($user, $pdo);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
                }
            } else {
                // Ya existía pero inicia con red social
                createSession($user, $pdo);
                echo json_encode(['success' => true]);
            }
        } else if ($name && $password) { 
            // Registro nuevo usuario local
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, provider, role, status, created_at) VALUES (?, ?, ?, ?, 'user', 'active', NOW())";
            $pdo->prepare($sql)->execute([$name, $email, $hashed_pass, $provider]);
            
            $newId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$newId]);
            createSession($stmt->fetch(), $pdo);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado o datos incompletos para registro']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error interno de base de datos']);
    }
    exit;
}

// ================================================================
// HELPERS
// ================================================================

function createSession($user, $pdo) {
    try {
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
    } catch (Exception $e) {} // Ignorar error de actualización de fecha
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
}

function loginOrRegisterSocial($email, $name, $provider, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        createSession($user, $pdo);
    } else {
        $pass = bin2hex(random_bytes(16));
        $sql = "INSERT INTO users (name, email, password, provider, role, status, created_at) VALUES (?, ?, ?, ?, 'user', 'active', NOW())";
        $pdo->prepare($sql)->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $provider]);
        
        $newId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$newId]);
        createSession($stmt->fetch(), $pdo);
    }
}
?>
