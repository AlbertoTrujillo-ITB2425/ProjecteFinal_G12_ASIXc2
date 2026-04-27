<?php
/**
 * ============================================================================
 * CYBERPYME SOC G12 — API: SYS_TOOLS (Herramientas del Sistema)
 * ============================================================================
 * Herramientas de diagnóstico y monitoreo del servidor SOC y red local.
 *
 * GET /api/sys_tools.php?action=network_info    → IP local, pública, interfaces
 * GET /api/sys_tools.php?action=open_ports      → puertos locales abiertos
 * GET /api/sys_tools.php?action=processes       → top procesos por CPU/RAM
 * GET /api/sys_tools.php?action=disk            → uso de disco
 * GET /api/sys_tools.php?action=memory          → uso de memoria RAM
 * GET /api/sys_tools.php?action=docker          → contenedores Docker activos
 * GET /api/sys_tools.php?action=users           → usuarios conectados
 * GET /api/sys_tools.php?action=ping&target=X   → ping desde el servidor
 * GET /api/sys_tools.php?action=traceroute&target=X
 * GET /api/sys_tools.php?action=whois&target=X
 * GET /api/sys_tools.php?action=nslookup&target=X
 * GET /api/sys_tools.php?action=arp             → tabla ARP (hosts LAN detectados)
 * GET /api/sys_tools.php?action=routes          → tabla de rutas
 * GET /api/sys_tools.php?action=health          → resumen general del servidor
 * ============================================================================
 */
require_once __DIR__ . '/_core.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') soc_error('Solo GET', 405);

$action = $_GET['action'] ?? '';
$target = isset($_GET['target']) ? soc_sanitize_target($_GET['target']) : null;

match ($action) {
    'network_info' => sys_network_info(),
    'open_ports'   => sys_open_ports(),
    'processes'    => sys_processes(),
    'disk'         => sys_disk(),
    'memory'       => sys_memory(),
    'docker'       => sys_docker(),
    'users'        => sys_users(),
    'ping'         => sys_ping($target),
    'traceroute'   => sys_traceroute($target),
    'whois'        => sys_whois($target),
    'nslookup'     => sys_nslookup($target),
    'arp'          => sys_arp(),
    'routes'       => sys_routes(),
    'health'       => sys_health(),
    default        => soc_error("Acción '$action' no válida. Disponibles: network_info, open_ports, processes, disk, memory, docker, users, ping, traceroute, whois, nslookup, arp, routes, health"),
};

// ── RED ───────────────────────────────────────────────────────────────────────
function sys_network_info(): never {
    // IP pública
    $pub = @file_get_contents('https://api.ipify.org?format=json', false, stream_context_create(['http' => ['timeout' => 5]]));
    $pub_ip = $pub ? (json_decode($pub, true)['ip'] ?? 'unknown') : 'unknown';

    // Interfaces locales
    $res = soc_exec('ip addr show 2>/dev/null || ifconfig 2>/dev/null', 10);
    $ifaces_raw = $res['output'];

    // Parsear interfaces y sus IPs
    $interfaces = [];
    preg_match_all('/^\d+:\s+(\S+):.*?\n.*?inet ([\d.]+)/ms', $ifaces_raw, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $interfaces[] = ['name' => $m[1], 'ip' => $m[2]];
    }

    // Gateway
    $gw_res = soc_exec("ip route | grep default | awk '{print $3}' | head -1", 5);
    $gateway = trim($gw_res['output']) ?: 'unknown';

    // Hostname
    $hostname = gethostname();

    soc_ok([
        'hostname'   => $hostname,
        'public_ip'  => $pub_ip,
        'gateway'    => $gateway,
        'interfaces' => $interfaces,
        'raw'        => $ifaces_raw,
    ]);
}

// ── PUERTOS ABIERTOS LOCALES ──────────────────────────────────────────────────
function sys_open_ports(): never {
    $res = soc_exec('ss -tulpn 2>/dev/null || netstat -tulpn 2>/dev/null', 15);
    $raw = $res['output'];

    $ports = [];
    $lines = explode("\n", $raw);
    foreach ($lines as $line) {
        // ss: tcp    LISTEN  0  128  0.0.0.0:22  ...
        if (preg_match('/^(tcp|udp)\s+\S+\s+\d+\s+\d+\s+([\d.*:]+):(\d+)\s+/i', $line, $m)) {
            $ports[] = [
                'proto'   => strtolower($m[1]),
                'address' => $m[2],
                'port'    => (int)$m[3],
                'raw'     => trim($line),
            ];
        }
        // netstat: tcp  0  0  0.0.0.0:80  LISTEN
        if (preg_match('/^(tcp6?|udp6?)\s+\d+\s+\d+\s+([\d.]+):(\d+)\s+\S+\s+LISTEN/i', $line, $m)) {
            $ports[] = [
                'proto'   => strtolower($m[1]),
                'address' => $m[2],
                'port'    => (int)$m[3],
                'raw'     => trim($line),
            ];
        }
    }

    soc_ok(['ports' => $ports, 'count' => count($ports), 'raw' => $raw]);
}

// ── PROCESOS ──────────────────────────────────────────────────────────────────
function sys_processes(): never {
    $res  = soc_exec('ps aux --sort=-%cpu 2>/dev/null | head -30', 10);
    $raw  = $res['output'];
    $procs = [];

    $lines = explode("\n", $raw);
    array_shift($lines); // header
    foreach ($lines as $line) {
        $parts = preg_split('/\s+/', trim($line), 11);
        if (count($parts) >= 10) {
            $procs[] = [
                'user'    => $parts[0],
                'pid'     => (int)$parts[1],
                'cpu'     => (float)$parts[2],
                'mem'     => (float)$parts[3],
                'vsz'     => (int)$parts[4],
                'rss'     => (int)$parts[5],
                'stat'    => $parts[7],
                'command' => $parts[10] ?? '-',
            ];
        }
    }

    soc_ok(['processes' => $procs, 'raw' => $raw]);
}

// ── DISCO ─────────────────────────────────────────────────────────────────────
function sys_disk(): never {
    $res  = soc_exec('df -h 2>/dev/null', 10);
    $raw  = $res['output'];
    $disks = [];

    $lines = explode("\n", $raw);
    array_shift($lines);
    foreach ($lines as $line) {
        $parts = preg_split('/\s+/', trim($line));
        if (count($parts) >= 6) {
            $disks[] = [
                'filesystem' => $parts[0],
                'size'       => $parts[1],
                'used'       => $parts[2],
                'available'  => $parts[3],
                'use_pct'    => $parts[4],
                'mountpoint' => $parts[5],
            ];
        }
    }

    soc_ok(['disks' => $disks, 'raw' => $raw]);
}

// ── MEMORIA ───────────────────────────────────────────────────────────────────
function sys_memory(): never {
    $res  = soc_exec('free -m 2>/dev/null', 5);
    $raw  = $res['output'];
    $data = [];

    preg_match('/^Mem:\s+(\d+)\s+(\d+)\s+(\d+)/m', $raw, $m);
    if ($m) {
        $total  = (int)$m[1];
        $used   = (int)$m[2];
        $free   = (int)$m[3];
        $data   = [
            'total_mb'   => $total,
            'used_mb'    => $used,
            'free_mb'    => $free,
            'use_pct'    => $total > 0 ? round($used / $total * 100, 1) : 0,
        ];
    }

    preg_match('/^Swap:\s+(\d+)\s+(\d+)/m', $raw, $ms);
    if ($ms) {
        $data['swap_total_mb'] = (int)$ms[1];
        $data['swap_used_mb']  = (int)$ms[2];
    }

    // Uptime
    $up = soc_exec('uptime -p 2>/dev/null || uptime', 5);
    $data['uptime'] = trim($up['output']);

    soc_ok(['memory' => $data, 'raw' => $raw]);
}

// ── DOCKER ───────────────────────────────────────────────────────────────────
function sys_docker(): never {
    // Lista de contenedores
    $res  = soc_exec("docker ps -a --format '{{json .}}' 2>/dev/null", 15);
    $containers = [];
    foreach (explode("\n", $res['output']) as $line) {
        $line = trim($line);
        if (!$line) continue;
        $c = json_decode($line, true);
        if ($c) {
            $containers[] = [
                'id'      => substr($c['ID'] ?? '-', 0, 12),
                'name'    => $c['Names']   ?? '-',
                'image'   => $c['Image']   ?? '-',
                'status'  => $c['Status']  ?? '-',
                'ports'   => $c['Ports']   ?? '-',
                'created' => $c['CreatedAt'] ?? '-',
                'running' => str_contains(strtolower($c['Status'] ?? ''), 'up'),
            ];
        }
    }

    // Stats de recursos
    $stats_res  = soc_exec("docker stats --no-stream --format '{{json .}}' 2>/dev/null", 20);
    $stats = [];
    foreach (explode("\n", $stats_res['output']) as $line) {
        $line = trim($line);
        if (!$line) continue;
        $s = json_decode($line, true);
        if ($s) {
            $stats[substr($s['ID'] ?? '-', 0, 12)] = [
                'cpu_pct' => $s['CPUPerc']   ?? '-',
                'mem'     => $s['MemUsage']  ?? '-',
                'mem_pct' => $s['MemPerc']   ?? '-',
                'net_io'  => $s['NetIO']     ?? '-',
                'blk_io'  => $s['BlockIO']   ?? '-',
            ];
        }
    }

    // Unir stats con containers
    foreach ($containers as &$c) {
        $c['stats'] = $stats[$c['id']] ?? null;
    }

    $running = count(array_filter($containers, fn($c) => $c['running']));
    soc_ok(['containers' => $containers, 'running' => $running, 'total' => count($containers)]);
}

// ── USUARIOS CONECTADOS ───────────────────────────────────────────────────────
function sys_users(): never {
    $who   = soc_exec('who 2>/dev/null', 5);
    $last  = soc_exec('last -n 20 2>/dev/null', 10);
    $w     = soc_exec('w 2>/dev/null',  5);
    soc_ok(['who' => $who['output'], 'last_logins' => $last['output'], 'w' => $w['output']]);
}

// ── PING ──────────────────────────────────────────────────────────────────────
function sys_ping(?string $target): never {
    if (!$target) soc_error('target requerido');
    $res = soc_exec("ping -c 4 -W 2 " . escapeshellarg($target), 20);
    $raw = $res['output'];

    $stats = [];
    if (preg_match('/(\d+) packets transmitted, (\d+) received/', $raw, $m)) {
        $stats['transmitted'] = (int)$m[1];
        $stats['received']    = (int)$m[2];
        $stats['loss_pct']    = $m[1] > 0 ? round(($m[1] - $m[2]) / $m[1] * 100) : 100;
    }
    if (preg_match('/rtt min\/avg\/max\/mdev = ([\d.]+)\/([\d.]+)\/([\d.]+)\/([\d.]+)/', $raw, $m)) {
        $stats['rtt_min']  = (float)$m[1];
        $stats['rtt_avg']  = (float)$m[2];
        $stats['rtt_max']  = (float)$m[3];
        $stats['rtt_mdev'] = (float)$m[4];
    }

    soc_ok(['target' => $target, 'stats' => $stats, 'raw' => $raw, 'reachable' => ($stats['received'] ?? 0) > 0]);
}

// ── TRACEROUTE ────────────────────────────────────────────────────────────────
function sys_traceroute(?string $target): never {
    if (!$target) soc_error('target requerido');
    $res = soc_exec("traceroute -m 20 -w 2 " . escapeshellarg($target) . " 2>&1", 60);
    soc_ok(['target' => $target, 'raw' => $res['output']]);
}

// ── WHOIS ─────────────────────────────────────────────────────────────────────
function sys_whois(?string $target): never {
    if (!$target) soc_error('target requerido');
    $res = soc_exec("whois " . escapeshellarg($target) . " 2>&1", 20);
    soc_ok(['target' => $target, 'raw' => $res['output']]);
}

// ── NSLOOKUP / DIG ───────────────────────────────────────────────────────────
function sys_nslookup(?string $target): never {
    if (!$target) soc_error('target requerido');
    $res = soc_exec("dig +short " . escapeshellarg($target) . " 2>/dev/null || nslookup " . escapeshellarg($target), 10);
    soc_ok(['target' => $target, 'raw' => $res['output']]);
}

// ── ARP ───────────────────────────────────────────────────────────────────────
function sys_arp(): never {
    $res  = soc_exec('arp -n 2>/dev/null || ip neigh 2>/dev/null', 10);
    $raw  = $res['output'];
    $entries = [];

    foreach (explode("\n", $raw) as $line) {
        // ip neigh: 192.168.1.1 dev eth0 lladdr aa:bb:cc:dd:ee:ff REACHABLE
        if (preg_match('/^([\d.]+)\s+\S+\s+(\S+)\s+lladdr\s+([0-9a-f:]+)\s+(\S+)/i', $line, $m)) {
            $entries[] = ['ip' => $m[1], 'iface' => $m[2], 'mac' => $m[3], 'state' => $m[4]];
        }
        // arp -n: 192.168.1.1  ether  aa:bb:cc:dd:ee:ff  C  eth0
        elseif (preg_match('/^([\d.]+)\s+\S+\s+([0-9a-f:]+)\s+\S+\s+(\S+)/i', $line, $m)) {
            $entries[] = ['ip' => $m[1], 'mac' => $m[2], 'iface' => $m[3]];
        }
    }

    soc_ok(['entries' => $entries, 'count' => count($entries), 'raw' => $raw]);
}

// ── TABLA DE RUTAS ────────────────────────────────────────────────────────────
function sys_routes(): never {
    $res = soc_exec('ip route 2>/dev/null || route -n 2>/dev/null', 10);
    soc_ok(['raw' => $res['output']]);
}

// ── HEALTH GENERAL ────────────────────────────────────────────────────────────
function sys_health(): never {
    $load   = sys_getloadavg();
    $mem_r  = soc_exec('free -m | awk \'/^Mem:/{print $3,$2}\'', 5);
    [$used, $total] = array_map('intval', explode(' ', trim($mem_r['output'])));

    $disk_r = soc_exec('df / | awk \'NR==2{print $5}\'', 5);
    $disk_pct = (int)trim($disk_r['output']);

    $uptime_r  = soc_exec('uptime -p 2>/dev/null', 5);
    $docker_r  = soc_exec('docker ps -q 2>/dev/null | wc -l', 5);

    $score = 100;
    $alerts = [];

    if (($load[0] ?? 0) > 4)      { $score -= 20; $alerts[] = 'CARGA CPU ALTA: ' . $load[0]; }
    if ($total > 0 && $used / $total > 0.85) { $score -= 20; $alerts[] = 'RAM ALTA: ' . round($used/$total*100) . '%'; }
    if ($disk_pct > 85)            { $score -= 20; $alerts[] = "DISCO LLENO: $disk_pct%"; }

    soc_ok([
        'score'         => max(0, $score),
        'alerts'        => $alerts,
        'load_avg'      => $load,
        'ram_used_mb'   => $used,
        'ram_total_mb'  => $total,
        'ram_pct'       => $total > 0 ? round($used / $total * 100, 1) : 0,
        'disk_root_pct' => $disk_pct,
        'uptime'        => trim($uptime_r['output']),
        'docker_running'=> (int)trim($docker_r['output']),
        'php_version'   => PHP_VERSION,
        'server_time'   => date('Y-m-d H:i:s T'),
    ]);
}
