<?php
require 'auth.php';
require 'utils.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$ip   = $data['ip'] ?? '';
$user = $data['user'] ?? '';
$pass = $data['pass'] ?? '';

if (!$ip || !$user || !$pass) {
    echo json_encode(['status'=>'error','message'=>'Faltan parámetros']);
    exit;
}

if (!function_exists('ssh2_connect')) {
    echo json_encode(['status'=>'error','message'=>'SSH2 no instalado']);
    exit;
}

$tipo = esIPLocal($ip) ? 'local' : 'externa';

$conn = @ssh2_connect($ip, 22);

if (!$conn) {
    echo json_encode(['status'=>'error','message'=>'No conecta']);
    exit;
}

if (!@ssh2_auth_password($conn, $user, $pass)) {
    echo json_encode(['status'=>'error','message'=>'Auth incorrecta']);
    exit;
}

function execSSH($conn, $cmd) {
    $stream = ssh2_exec($conn, $cmd . " 2>&1");
    stream_set_blocking($stream, true);
    return stream_get_contents($stream);
}

if ($tipo === 'local') {
    $data = execSSH($conn, "ss -tun state established");
} else {
    $data = execSSH($conn, "netstat -tun");
}

echo json_encode([
    'status' => 'ok',
    'tipo' => $tipo,
    'resultado' => $data
]);
