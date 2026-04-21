<?php
// api/scan_async.php
header('Content-Type: application/json');

$ip = $_GET['ip'] ?? '';

// Validación básica
if (!$ip || !preg_match('/^[a-zA-Z0-9.\-]+$/', $ip)) {
    echo json_encode(['status' => 'error', 'message' => 'IP inválida.']);
    exit;
}

// Bloquear IPs locales para evitar escaneo interno no deseado
// (Elimina este bloque si necesitas escanear tu propia red)
$private = ['/^127\./', '/^10\./', '/^192\.168\./', '/^172\.(1[6-9]|2\d|3[01])\./'];
foreach ($private as $pattern) {
    if (preg_match($pattern, $ip)) {
        // Permitido solo para redes internas — avisa al cliente
        // Si quieres bloquearlo descomenta:
        // echo json_encode(['status' => 'error', 'message' => 'Escaneo de IPs privadas no permitido.']); exit;
    }
}

$cmd = "nmap -F -sV --open " . escapeshellarg($ip) . " 2>&1";
$nmap_result = shell_exec($cmd) ?? 'No se pudo ejecutar nmap. Verifica que esté instalado.';

// Generador de recomendaciones basado en el output
$recomendaciones = [];

if (strpos($nmap_result, '21/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'ALTO',     'msg' => 'Puerto FTP (21) abierto. El tráfico no está cifrado. Migra a SFTP/FTPS o cierra el puerto.'];
}
if (strpos($nmap_result, '22/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'MEDIO',    'msg' => 'SSH (22) expuesto. Cambia el puerto por defecto y activa Fail2Ban para mitigar fuerza bruta.'];
}
if (strpos($nmap_result, '23/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'CRÍTICO',  'msg' => 'Telnet (23) abierto. Protocolo sin cifrado obsoleto. Ciérralo inmediatamente y usa SSH.'];
}
if (strpos($nmap_result, '25/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'ALTO',     'msg' => 'SMTP (25) abierto. Puede ser usado para relay de spam si no está protegido. Revisa la configuración.'];
}
if (strpos($nmap_result, '80/tcp') !== false && strpos($nmap_result, '443/tcp') === false) {
    $recomendaciones[] = ['riesgo' => 'MEDIO',    'msg' => 'HTTP (80) abierto sin HTTPS. Instala un certificado SSL/TLS (Let\'s Encrypt) y redirige a 443.'];
}
if (strpos($nmap_result, '3306/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'CRÍTICO',  'msg' => 'MySQL (3306) expuesto al exterior. Bloquea el puerto en el firewall inmediatamente.'];
}
if (strpos($nmap_result, '5432/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'CRÍTICO',  'msg' => 'PostgreSQL (5432) expuesto. Restringe el acceso a localhost o a IPs de confianza.'];
}
if (strpos($nmap_result, '6379/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'CRÍTICO',  'msg' => 'Redis (6379) expuesto sin autenticación probable. Bloquea el puerto o configura requirepass.'];
}
if (strpos($nmap_result, '27017/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'CRÍTICO',  'msg' => 'MongoDB (27017) expuesto. Sin auth por defecto. Restringe a localhost y activa autenticación.'];
}
if (strpos($nmap_result, '8080/tcp') !== false || strpos($nmap_result, '8443/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'MEDIO',    'msg' => 'Puerto alternativo web (8080/8443) abierto. Asegúrate de que no sea un panel de administración expuesto.'];
}
if (strpos($nmap_result, '445/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'ALTO',     'msg' => 'SMB (445) expuesto. Vulnerable a ataques tipo EternalBlue. Bloquea si no es necesario.'];
}
if (strpos($nmap_result, '3389/tcp') !== false) {
    $recomendaciones[] = ['riesgo' => 'ALTO',     'msg' => 'RDP (3389) expuesto. Objetivo frecuente de fuerza bruta. Cambia el puerto o usa una VPN.'];
}

if (empty($recomendaciones)) {
    $recomendaciones[] = ['riesgo' => 'BAJO', 'msg' => 'No se han detectado puertos críticos expuestos en este escaneo rápido. Mantén el sistema actualizado.'];
}

echo json_encode([
    'status'          => 'ok',
    'nmap'            => $nmap_result,
    'recomendaciones' => $recomendaciones,
]);
