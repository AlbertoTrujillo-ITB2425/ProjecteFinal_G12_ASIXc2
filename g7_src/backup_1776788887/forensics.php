<?php
// api/forensics.php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$ip   = $data['ip']   ?? '';
$user = $data['user'] ?? '';
$pass = $data['pass'] ?? '';

if (!$ip || !$user || !$pass) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros (ip, user, pass).']);
    exit;
}

if (!function_exists('ssh2_connect')) {
    echo json_encode(['status' => 'error', 'message' => 'El módulo PHP SSH2 no está instalado en este servidor.']);
    exit;
}

$conn = @ssh2_connect($ip, 22);
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => "No se pudo conectar a $ip:22. Verifica que el puerto esté abierto."]);
    exit;
}

if (!@ssh2_auth_password($conn, $user, $pass)) {
    echo json_encode(['status' => 'error', 'message' => 'Autenticación denegada. Usuario o contraseña incorrectos.']);
    exit;
}

// Ejecutor de comandos SSH
$exec = function(string $cmd) use ($conn, $user, $pass): string {
    // Elevar con sudo si no es root
    if ($user !== 'root' && strpos($cmd, 'sudo') !== false) {
        $escaped = str_replace("'", "'\\''", $pass);
        $cmd = "echo '$escaped' | sudo -S " . ltrim(str_replace('sudo ', '', $cmd));
    }
    $stream = @ssh2_exec($conn, $cmd . ' 2>&1');
    if (!$stream) return '(sin respuesta)';
    stream_set_blocking($stream, true);
    return stream_get_contents($stream) ?: '(vacío)';
};

$result = [
    'status'     => 'ok',
    'conexiones' => $exec("ss -tun state established"),
    'usuarios'   => $exec("w"),
    'ataques'    => $exec("sudo grep 'Failed password' /var/log/auth.log 2>/dev/null | tail -n 20 || sudo grep 'Failed password' /var/log/secure 2>/dev/null | tail -n 20"),
    // Extras
    'procesos'   => $exec("ps aux --sort=-%cpu | head -n 10"),
    'disco'      => $exec("df -h"),
];

echo json_encode($result);
