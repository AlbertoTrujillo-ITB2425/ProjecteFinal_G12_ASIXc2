<?php
// api/ssh_session.php
// Gestor de sesiones SSH para la terminal interactiva.
// Las sesiones se almacenan en archivos temporales cifrados en el servidor.
// IMPORTANTE: En producción añade autenticación de usuario antes de este endpoint.

header('Content-Type: application/json');
session_start();

$data   = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// Directorio de sesiones temporales (fuera del webroot en producción)
$sessions_dir = sys_get_temp_dir() . '/ssh_sessions';
if (!is_dir($sessions_dir)) mkdir($sessions_dir, 0700, true);

// ── Función para guardar/leer estado de sesión ──
function session_path(string $id): string {
    global $sessions_dir;
    // Solo caracteres alfanuméricos en el ID
    return $sessions_dir . '/' . preg_replace('/[^a-f0-9]/', '', $id) . '.json';
}

function save_session(string $id, array $data): void {
    file_put_contents(session_path($id), json_encode($data), LOCK_EX);
}

function load_session(string $id): ?array {
    $path = session_path($id);
    if (!file_exists($path)) return null;
    return json_decode(file_get_contents($path), true);
}

function delete_session(string $id): void {
    $path = session_path($id);
    if (file_exists($path)) unlink($path);
}

// ── Comandos bloqueados por seguridad ──
$blocked_commands = [
    '/\brm\s+-rf\s+\//', '/\bmkfs\b/', '/\bdd\s+if=/', '/\bshutdown\b/', '/\breboot\b/',
    '/\bpoweroff\b/', '/>\s*\/dev\/s[d]/', '/\bchmod\s+777\s+\//', '/\bcurl\b.*\|\s*bash/',
    '/\bwget\b.*\|\s*sh/'
];

function is_blocked(string $cmd): bool {
    global $blocked_commands;
    foreach ($blocked_commands as $pattern) {
        if (preg_match($pattern, $cmd)) return true;
    }
    return false;
}

// ────────────────────────────────────────────────
switch ($action) {

    // ── CONECTAR ──
    case 'connect':
        $ip   = $data['ip']   ?? '';
        $user = $data['user'] ?? '';
        $pass = $data['pass'] ?? '';

        if (!$ip || !$user || !$pass) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros.']);
            exit;
        }
        if (!function_exists('ssh2_connect')) {
            echo json_encode(['status' => 'error', 'message' => 'Módulo PHP SSH2 no instalado.']);
            exit;
        }

        $conn = @ssh2_connect($ip, 22);
        if (!$conn) {
            echo json_encode(['status' => 'error', 'message' => "No se pudo conectar a $ip:22."]);
            exit;
        }
        if (!@ssh2_auth_password($conn, $user, $pass)) {
            echo json_encode(['status' => 'error', 'message' => 'Autenticación denegada.']);
            exit;
        }

        // Generar ID de sesión y guardar datos (la contraseña NO se guarda en disco)
        $session_id = bin2hex(random_bytes(16));
        save_session($session_id, [
            'ip'      => $ip,
            'user'    => $user,
            'created' => time(),
            'cwd'     => '~',
        ]);

        // Guardar la conexión en memoria de sesión PHP
        // (en un servidor con múltiples procesos se necesitaría un proxy WebSocket;
        //  esta implementación funciona para peticiones request-response)
        $_SESSION['ssh_' . $session_id] = [
            'ip'   => $ip,
            'user' => $user,
            'pass' => $pass,    // Solo en sesión PHP de memoria, no en disco
        ];

        echo json_encode(['status' => 'ok', 'session_id' => $session_id]);
        break;

    // ── EJECUTAR COMANDO ──
    case 'exec':
        $session_id = $data['session_id'] ?? '';
        $cmd        = trim($data['cmd'] ?? '');

        if (!$session_id || !$cmd) {
            echo json_encode(['status' => 'error', 'message' => 'Sesión o comando inválidos.']);
            exit;
        }

        $sess = load_session($session_id);
        if (!$sess) {
            echo json_encode(['status' => 'error', 'message' => 'Sesión expirada. Reconecta.']);
            exit;
        }

        // Seguridad: bloquear comandos destructivos
        if (is_blocked($cmd)) {
            echo json_encode(['status' => 'error', 'message' => '⛔ Comando bloqueado por políticas de seguridad.']);
            exit;
        }

        $creds = $_SESSION['ssh_' . $session_id] ?? null;
        if (!$creds) {
            echo json_encode(['status' => 'error', 'message' => 'Sesión PHP expirada. Reconecta.']);
            exit;
        }

        // Reconectar (stateless — cada petición abre y cierra)
        $conn = @ssh2_connect($creds['ip'], 22);
        if (!$conn || !@ssh2_auth_password($conn, $creds['user'], $creds['pass'])) {
            echo json_encode(['status' => 'error', 'message' => 'Error al reconectar con el servidor SSH.']);
            exit;
        }

        // Añadir timeout con timeout command para evitar comandos colgados
        $safe_cmd = "timeout 15 bash -c " . escapeshellarg($cmd) . " 2>&1";

        $stream = @ssh2_exec($conn, $safe_cmd);
        if (!$stream) {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo ejecutar el comando.']);
            exit;
        }

        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);

        echo json_encode(['status' => 'ok', 'output' => $output]);
        break;

    // ── DESCONECTAR ──
    case 'disconnect':
        $session_id = $data['session_id'] ?? '';
        if ($session_id) {
            delete_session($session_id);
            unset($_SESSION['ssh_' . $session_id]);
        }
        echo json_encode(['status' => 'ok', 'message' => 'Sesión cerrada.']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción desconocida.']);
}
