<?php
/**
 * ============================================================================
 * CYBERPYME SOC G12 — API CORE
 * Utilidades compartidas: .env, DB, autenticación, respuestas JSON
 * ============================================================================
 */

// ── 1. CABECERAS CORS / SECURITY ─────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Ajusta el origen si usas un dominio fijo
$allowed_origins = ['http://localhost', 'https://cyberpyme.local'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── 2. CARGADOR DE .env ───────────────────────────────────────────────────────
function soc_load_env(string $path = null): void {
    // Busca el .env subiendo directorios desde aquí
    $candidates = [
        $path,
        __DIR__ . '/../.env',
        __DIR__ . '/../../.env',
        '/var/www/html/.env',
    ];
    foreach ($candidates as $file) {
        if ($file && file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
                [$key, $val] = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val, " \t\n\r\0\x0B\"'");
                if (!getenv($key)) putenv("$key=$val");
                $_ENV[$key] = $val;
            }
            return;
        }
    }
}
soc_load_env();

// ── 3. RESPUESTAS JSON ────────────────────────────────────────────────────────
function soc_ok(mixed $data, string $message = 'OK', int $code = 200): never {
    http_response_code($code);
    echo json_encode(['status' => 'ok', 'message' => $message, 'data' => $data, 'ts' => time()], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function soc_error(string $message, int $code = 400, mixed $debug = null): never {
    http_response_code($code);
    $payload = ['status' => 'error', 'message' => $message, 'ts' => time()];
    if ($debug !== null && (bool)getenv('SOC_DEBUG')) $payload['debug'] = $debug;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ── 4. PARSEO DE BODY JSON ────────────────────────────────────────────────────
function soc_body(): array {
    $raw = file_get_contents('php://input');
    if (empty($raw)) return $_POST ?: [];
    $decoded = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) soc_error('JSON body inválido: ' . json_last_error_msg());
    return $decoded ?? [];
}

// ── 5. CONEXIÓN PDO (MySQL) ───────────────────────────────────────────────────
function soc_db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'cyberaudit';
    $user = getenv('DB_USER') ?: 'cyberuser';
    $pass = getenv('DB_PASSWORD') ?: '';

    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
            $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_TIMEOUT => 5]
        );
    } catch (PDOException $e) {
        soc_error('Error de conexión a base de datos', 503, $e->getMessage());
    }
    return $pdo;
}

// ── 6. ESQUEMA DE BASE DE DATOS (auto-migrate) ────────────────────────────────
function soc_migrate(): void {
    $db = soc_db();
    $db->exec("
        CREATE TABLE IF NOT EXISTS soc_hosts (
            id            INT AUTO_INCREMENT PRIMARY KEY,
            label         VARCHAR(100) NOT NULL,
            ip            VARCHAR(45)  NOT NULL,
            os_type       ENUM('linux','windows','unknown') DEFAULT 'unknown',
            ssh_user      VARCHAR(64)  DEFAULT NULL,
            ssh_key_path  VARCHAR(255) DEFAULT NULL,
            winrm_user    VARCHAR(64)  DEFAULT NULL,
            winrm_pass    VARCHAR(255) DEFAULT NULL,  -- en producción usar vault
            status        ENUM('online','offline','unknown') DEFAULT 'unknown',
            last_seen     DATETIME     DEFAULT NULL,
            tags          JSON         DEFAULT NULL,
            created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS soc_scans (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            host_id     INT          DEFAULT NULL,
            target      VARCHAR(255) NOT NULL,
            scope       ENUM('local','external') DEFAULT 'local',
            profile     VARCHAR(50)  DEFAULT 'quick',
            raw_output  LONGTEXT     DEFAULT NULL,
            parsed      JSON         DEFAULT NULL,
            started_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
            finished_at DATETIME     DEFAULT NULL,
            status      ENUM('pending','running','done','error') DEFAULT 'pending',
            FOREIGN KEY (host_id) REFERENCES soc_hosts(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS soc_events (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            host_id    INT          DEFAULT NULL,
            type       VARCHAR(50)  NOT NULL,
            severity   ENUM('info','warning','critical') DEFAULT 'info',
            message    TEXT         NOT NULL,
            payload    JSON         DEFAULT NULL,
            created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (host_id) REFERENCES soc_hosts(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

// ── 7. VALIDACIÓN / SANITIZACIÓN ─────────────────────────────────────────────
function soc_valid_ip(string $ip): bool {
    return filter_var($ip, FILTER_VALIDATE_IP) !== false;
}

function soc_valid_hostname(string $host): bool {
    // IP o nombre de host básico
    if (soc_valid_ip($host)) return true;
    return (bool) preg_match('/^[a-zA-Z0-9.\-]{1,253}$/', $host);
}

function soc_sanitize_target(string $t): string {
    // Solo IPs, CIDRs o hostnames válidos
    $t = trim($t);
    if (preg_match('/^[\d.\/a-zA-Z\-]{1,100}$/', $t)) return $t;
    soc_error('Target inválido');
}

// ── 8. EJECUCIÓN SEGURA DE COMANDOS ──────────────────────────────────────────
function soc_exec(string $cmd, int $timeout = 60): array {
    $output = []; $return_code = -1;
    // Timeout via timeout(1) de GNU coreutils
    $safe_cmd = "timeout $timeout " . $cmd . " 2>&1";
    exec($safe_cmd, $output, $return_code);
    return ['output' => implode("\n", $output), 'code' => $return_code];
}

// ── 9. LOG DE EVENTOS ────────────────────────────────────────────────────────
function soc_log_event(string $type, string $message, string $severity = 'info', ?int $host_id = null, ?array $payload = null): void {
    try {
        $db = soc_db();
        $st = $db->prepare("INSERT INTO soc_events (host_id, type, severity, message, payload) VALUES (?, ?, ?, ?, ?)");
        $st->execute([$host_id, $type, $severity, $message, $payload ? json_encode($payload) : null]);
    } catch (Throwable) { /* silencioso */ }
}

// ── 10. SHODAN API KEY ────────────────────────────────────────────────────────
function soc_shodan_key(): string {
    $key = getenv('SHODAN_API_KEY') ?: '';
    if (empty($key)) soc_error('SHODAN_API_KEY no configurada en .env', 503);
    return $key;
}
