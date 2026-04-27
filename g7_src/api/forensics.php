<?php
/**
 * ============================================================================
 * CYBERPYME SOC G12 — API: FORENSICS (Logs y Análisis del Sistema)
 * ============================================================================
 * Lee logs reales del servidor SOC y de hosts remotos.
 *
 * GET  /api/forensics.php?action=sources          → logs disponibles en el servidor
 * GET  /api/forensics.php?action=read&log=auth&lines=100
 * GET  /api/forensics.php?action=read&log=apache_access
 * GET  /api/forensics.php?action=events           → eventos SOC de la BD
 * GET  /api/forensics.php?action=events&severity=critical
 * POST /api/forensics.php?action=remote           → logs de host remoto
 *      Body: { host_id: X, log: "auth" }
 * GET  /api/forensics.php?action=stats            → resumen estadístico de eventos
 * ============================================================================
 */
require_once __DIR__ . '/_core.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'sources';

match (true) {
    $action === 'sources'                        => forensics_sources(),
    $action === 'read'                           => forensics_read(),
    $action === 'events'                         => forensics_events(),
    $action === 'stats'                          => forensics_stats(),
    $action === 'remote' && $method === 'POST'   => forensics_remote(),
    default                                      => soc_error("Acción '$action' no válida. Disponibles: sources, read, events, remote, stats"),
};

// ── FUENTES DE LOG DISPONIBLES ────────────────────────────────────────────────
function forensics_sources(): never {
    $LOG_SOURCES = [
        'auth'           => [
            'paths'       => ['/var/log/auth.log', '/var/log/secure'],
            'desc'        => 'Autenticaciones SSH, sudo, PAM',
            'os'          => 'linux',
        ],
        'syslog'         => [
            'paths'       => ['/var/log/syslog', '/var/log/messages'],
            'desc'        => 'Log del sistema general',
            'os'          => 'linux',
        ],
        'apache_access'  => [
            'paths'       => ['/var/log/apache2/access.log', '/var/log/httpd/access_log'],
            'desc'        => 'Peticiones HTTP de Apache',
            'os'          => 'linux',
        ],
        'apache_error'   => [
            'paths'       => ['/var/log/apache2/error.log', '/var/log/httpd/error_log'],
            'desc'        => 'Errores Apache',
            'os'          => 'linux',
        ],
        'nginx_access'   => [
            'paths'       => ['/var/log/nginx/access.log'],
            'desc'        => 'Peticiones HTTP de Nginx',
            'os'          => 'linux',
        ],
        'nginx_error'    => [
            'paths'       => ['/var/log/nginx/error.log'],
            'desc'        => 'Errores Nginx',
            'os'          => 'linux',
        ],
        'kern'           => [
            'paths'       => ['/var/log/kern.log'],
            'desc'        => 'Mensajes del kernel Linux',
            'os'          => 'linux',
        ],
        'dpkg'           => [
            'paths'       => ['/var/log/dpkg.log'],
            'desc'        => 'Instalaciones de paquetes Debian/Ubuntu',
            'os'          => 'linux',
        ],
        'fail2ban'       => [
            'paths'       => ['/var/log/fail2ban.log'],
            'desc'        => 'IPs bloqueadas por Fail2ban',
            'os'          => 'linux',
        ],
        'snort'          => [
            'paths'       => ['/var/log/snort/alert', '/home/ubuntu/ProjecteFinal_G7/snort_logs/alert'],
            'desc'        => 'Alertas del IDS Snort',
            'os'          => 'linux',
        ],
        'docker'         => [
            'paths'       => [], // usa `docker logs` en su lugar
            'desc'        => 'Logs de contenedores Docker (via journalctl)',
            'os'          => 'linux',
            'cmd'         => 'journalctl -u docker -n 200 --no-pager 2>/dev/null',
        ],
        'journal'        => [
            'paths'       => [],
            'desc'        => 'Journal del sistema (systemd)',
            'os'          => 'linux',
            'cmd'         => 'journalctl -n 300 --no-pager -p warning..emerg 2>/dev/null',
        ],
    ];

    $available = [];
    foreach ($LOG_SOURCES as $name => $info) {
        $found_path = null;
        foreach ($info['paths'] as $p) {
            if (file_exists($p) && is_readable($p)) { $found_path = $p; break; }
        }
        $available[] = [
            'name'       => $name,
            'desc'       => $info['desc'],
            'available'  => $found_path !== null || !empty($info['cmd']),
            'path'       => $found_path,
            'has_cmd'    => !empty($info['cmd']),
        ];
    }

    soc_ok($available, count(array_filter($available, fn($a) => $a['available'])) . ' fuentes disponibles');
}

// ── LEER LOG LOCAL ─────────────────────────────────────────────────────────────
function forensics_read(): never {
    $log_name = $_GET['log']   ?? '';
    $lines    = min((int)($_GET['lines'] ?? 100), 2000);
    $filter   = trim($_GET['filter'] ?? ''); // grep opcional

    $LOGS = [
        'auth'          => ['/var/log/auth.log',  '/var/log/secure'],
        'syslog'        => ['/var/log/syslog',    '/var/log/messages'],
        'apache_access' => ['/var/log/apache2/access.log', '/var/log/httpd/access_log'],
        'apache_error'  => ['/var/log/apache2/error.log',  '/var/log/httpd/error_log'],
        'nginx_access'  => ['/var/log/nginx/access.log'],
        'nginx_error'   => ['/var/log/nginx/error.log'],
        'kern'          => ['/var/log/kern.log'],
        'dpkg'          => ['/var/log/dpkg.log'],
        'fail2ban'      => ['/var/log/fail2ban.log'],
        'snort'         => ['/var/log/snort/alert', '/home/ubuntu/ProjecteFinal_G7/snort_logs/alert'],
    ];

    $CMD_LOGS = [
        'docker'  => "journalctl -u docker -n $lines --no-pager 2>/dev/null",
        'journal' => "journalctl -n $lines --no-pager -p warning..emerg 2>/dev/null",
    ];

    if (empty($log_name)) soc_error('log es requerido');

    // CMD-based logs
    if (isset($CMD_LOGS[$log_name])) {
        $cmd = $CMD_LOGS[$log_name];
        if ($filter) $cmd .= ' | grep -i ' . escapeshellarg($filter);
        $res = soc_exec($cmd, 30);
        soc_ok(['source' => $log_name, 'lines' => $lines, 'filter' => $filter, 'raw' => $res['output'], 'parsed' => log_analyze($res['output'], $log_name)]);
    }

    if (!isset($LOGS[$log_name])) soc_error("Log '$log_name' no reconocido");

    // Buscar archivo
    $path = null;
    foreach ($LOGS[$log_name] as $p) {
        if (file_exists($p) && is_readable($p)) { $path = $p; break; }
    }
    if (!$path) soc_error("Log '$log_name' no encontrado en el sistema (¿permisos?)", 404);

    // Leer con tail + grep opcional
    $cmd = "tail -n $lines " . escapeshellarg($path);
    if ($filter) $cmd .= " | grep -i " . escapeshellarg($filter);
    $res = soc_exec($cmd, 15);

    $raw    = $res['output'];
    $parsed = log_analyze($raw, $log_name);

    soc_ok([
        'source'   => $log_name,
        'path'     => $path,
        'lines'    => $lines,
        'filter'   => $filter ?: null,
        'raw'      => $raw,
        'parsed'   => $parsed,
    ]);
}

// ── EVENTOS SOC DE LA BD ──────────────────────────────────────────────────────
function forensics_events(): never {
    soc_migrate();
    $severity = $_GET['severity'] ?? null;
    $limit    = min((int)($_GET['limit'] ?? 100), 1000);
    $host_id  = isset($_GET['host_id']) ? (int)$_GET['host_id'] : null;

    $where = []; $params = [];
    if ($severity) { $where[] = 'severity=?'; $params[] = $severity; }
    if ($host_id)  { $where[] = 'host_id=?';  $params[] = $host_id; }

    $sql = "SELECT e.*, h.label as host_label, h.ip as host_ip 
            FROM soc_events e 
            LEFT JOIN soc_hosts h ON e.host_id = h.id";
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY e.created_at DESC LIMIT $limit";

    $st = soc_db()->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();

    foreach ($rows as &$r) {
        $r['payload'] = $r['payload'] ? json_decode($r['payload'], true) : null;
    }

    soc_ok($rows, count($rows) . " eventos encontrados");
}

// ── ESTADÍSTICAS ──────────────────────────────────────────────────────────────
function forensics_stats(): never {
    soc_migrate();
    $db = soc_db();

    $stats = [
        'total_events'    => (int)$db->query("SELECT COUNT(*) FROM soc_events")->fetchColumn(),
        'critical_events' => (int)$db->query("SELECT COUNT(*) FROM soc_events WHERE severity='critical'")->fetchColumn(),
        'warning_events'  => (int)$db->query("SELECT COUNT(*) FROM soc_events WHERE severity='warning'")->fetchColumn(),
        'total_scans'     => (int)$db->query("SELECT COUNT(*) FROM soc_scans")->fetchColumn(),
        'total_hosts'     => (int)$db->query("SELECT COUNT(*) FROM soc_hosts")->fetchColumn(),
        'hosts_online'    => (int)$db->query("SELECT COUNT(*) FROM soc_hosts WHERE status='online'")->fetchColumn(),
        'events_by_type'  => [],
        'recent_critical' => [],
    ];

    // Eventos por tipo
    $rows = $db->query("SELECT type, COUNT(*) as cnt FROM soc_events GROUP BY type ORDER BY cnt DESC LIMIT 10")->fetchAll();
    foreach ($rows as $r) $stats['events_by_type'][$r['type']] = (int)$r['cnt'];

    // Últimos críticos
    $stats['recent_critical'] = $db->query(
        "SELECT message, created_at, host_id FROM soc_events WHERE severity='critical' ORDER BY created_at DESC LIMIT 5"
    )->fetchAll();

    soc_ok($stats);
}

// ── LOGS DE HOST REMOTO ───────────────────────────────────────────────────────
function forensics_remote(): never {
    $b       = soc_body();
    $host_id = (int)($b['host_id'] ?? 0);
    $log     = $b['log']     ?? 'auth';
    $lines   = min((int)($b['lines'] ?? 100), 500);

    if (!$host_id) soc_error('host_id requerido');

    $st = soc_db()->prepare("SELECT * FROM soc_hosts WHERE id=?");
    $st->execute([$host_id]);
    $host = $st->fetch();
    if (!$host) soc_error("Host #$host_id no encontrado", 404);

    // Reutilizamos ssh_session internamente
    $log_cmds_linux = [
        'auth'    => 'tail -100 /var/log/auth.log 2>/dev/null || journalctl -u ssh -n 100 --no-pager 2>/dev/null',
        'syslog'  => 'tail -100 /var/log/syslog 2>/dev/null || journalctl -n 100 --no-pager 2>/dev/null',
        'apache'  => 'tail -100 /var/log/apache2/access.log 2>/dev/null || tail -100 /var/log/httpd/access_log 2>/dev/null',
        'nginx'   => 'tail -100 /var/log/nginx/access.log 2>/dev/null',
        'kern'    => 'tail -100 /var/log/kern.log 2>/dev/null',
        'ps'      => 'ps aux --sort=-%cpu | head -20',
        'netstat' => 'ss -tulpn 2>/dev/null',
        'disk'    => 'df -h',
    ];
    $log_cmds_win = [
        'auth'   => 'Get-WinEvent -LogName Security -MaxEvents 50 | Where-Object {$_.Id -in 4624,4625} | Format-Table TimeCreated,Id,Message -Wrap',
        'syslog' => 'Get-WinEvent -LogName System -MaxEvents 50 | Format-Table TimeCreated,LevelDisplayName,Message -Wrap',
        'ps'     => 'Get-Process | Sort-Object CPU -Descending | Select-Object -First 20 | Format-Table',
        'netstat'=> 'Get-NetTCPConnection | Format-Table',
        'disk'   => 'Get-PSDrive -PSProvider FileSystem | Format-Table',
    ];

    $is_win = ($host['os_type'] === 'windows');
    $map    = $is_win ? $log_cmds_win : $log_cmds_linux;

    if (!isset($map[$log])) soc_error("Log '$log' no disponible para OS " . $host['os_type'] . ". Disponibles: " . implode(', ', array_keys($map)));

    // Incluir el módulo SSH
    $cmd  = $map[$log];
    // Llamar directamente a la función de ejecución
    require_once __DIR__ . '/ssh_session.php';
    // Como ssh_session.php ya incluyó _core y tiene funciones, las llamamos
    $result = $is_win ? exec_windows($host, $cmd, 60) : exec_linux($host, $cmd, 60);

    soc_ok([
        'host'   => ['id' => $host['id'], 'label' => $host['label'], 'ip' => $host['ip']],
        'log'    => $log,
        'raw'    => $result['output'],
        'method' => $result['method'],
    ]);
}

// ── ANALIZADOR DE LOGS ────────────────────────────────────────────────────────
function log_analyze(string $raw, string $type): array {
    $analysis = [
        'line_count'     => substr_count($raw, "\n"),
        'failed_logins'  => [],
        'blocked_ips'    => [],
        'errors'         => [],
        'warnings'       => [],
        'severity_count' => ['critical' => 0, 'warning' => 0, 'info' => 0],
    ];

    $lines = explode("\n", $raw);

    foreach ($lines as $line) {
        $lower = strtolower($line);

        // Failed SSH logins
        if (preg_match('/failed password for(?: invalid user)? (\S+) from ([\d.]+)/i', $line, $m)) {
            $analysis['failed_logins'][] = ['user' => $m[1], 'ip' => $m[2], 'raw' => trim($line)];
            $analysis['severity_count']['warning']++;
        }

        // IPs bloqueadas por fail2ban / iptables
        if (preg_match('/ban\s+([\d.]+)/i', $line, $m) || preg_match('/blocked.*?([\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3})/i', $line, $m)) {
            $analysis['blocked_ips'][] = $m[1];
            $analysis['severity_count']['warning']++;
        }

        // Errores
        if (str_contains($lower, 'error') || str_contains($lower, '[error]') || str_contains($lower, 'critical')) {
            $analysis['errors'][] = trim($line);
            $analysis['severity_count']['critical']++;
        }

        // Warnings
        if (str_contains($lower, 'warn') || str_contains($lower, '[warn]')) {
            $analysis['warnings'][] = trim($line);
            $analysis['severity_count']['warning']++;
        }
    }

    // Deduplicar IPs bloqueadas
    $analysis['blocked_ips']   = array_unique($analysis['blocked_ips']);
    // Truncar listas largas
    $analysis['failed_logins'] = array_slice($analysis['failed_logins'], 0, 50);
    $analysis['errors']        = array_slice($analysis['errors'],        0, 30);
    $analysis['warnings']      = array_slice($analysis['warnings'],      0, 30);

    return $analysis;
}
