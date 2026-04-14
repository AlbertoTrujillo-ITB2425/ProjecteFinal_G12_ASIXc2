<?php
// firewall_exec.php
header('Content-Type: application/json');

// Recibimos los datos enviados por AJAX
$data = json_decode(file_get_contents('php://input'), true);

$host = $data['host'] ?? '';
$user = $data['user'] ?? '';
$pass = $data['pass'] ?? '';

if (!$host || !$user || !$pass) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan credenciales SSH.']);
    exit;
}

// Comandos UFW a ejecutar (puedes hacer esto dinámico en el futuro)
$comandos = [
    "ufw --force enable",
    "ufw default deny incoming",
    "ufw default allow outgoing",
    "ufw allow 80/tcp",
    "ufw allow 443/tcp",
    "ufw deny 3306/tcp",
    "ufw status"
];

$comando_final = implode(" && ", $comandos);

// Intentar conexión SSH usando la extensión ssh2 de PHP
if (!function_exists('ssh2_connect')) {
    echo json_encode(['status' => 'error', 'message' => 'La extensión PHP SSH2 no está instalada en el contenedor.']);
    exit;
}

$connection = @ssh2_connect($host, 22);
if (!$connection) {
    echo json_encode(['status' => 'error', 'message' => "No se pudo conectar a $host por el puerto 22."]);
    exit;
}

if (@ssh2_auth_password($connection, $user, $pass)) {
    // Si la conexión tiene éxito, ejecutamos el comando
    // Si el usuario no es root, le pasamos 'sudo -S' y la contraseña por la entrada estándar
    if ($user !== 'root') {
        $comando_final = "echo '$pass' | sudo -S sh -c '$comando_final'";
    }

    $stream = ssh2_exec($connection, $comando_final);
    stream_set_blocking($stream, true);
    $salida = stream_get_contents($stream);
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Reglas aplicadas correctamente.',
        'output' => $salida
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Autenticación SSH denegada (Usuario/Contraseña incorrectos).']);
}
?>
