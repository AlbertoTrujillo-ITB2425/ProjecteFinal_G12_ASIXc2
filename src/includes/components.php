<?php

/**
 * Obtiene la carga de la CPU (Muestra en crudo para cálculo diferencial)
 */
function getRealCPUUsage() {
    if (file_exists('/proc/stat')) {
        $str = file_get_contents('/proc/stat');
        $lines = explode("\n", $str);
        $stats = explode(" ", preg_replace("/\s+/", " ", trim($lines[0])));
        $iron = array_slice($stats, 1, 4);
        $total = array_sum($iron);
        $idle = $iron[3];
        
        return ["total" => $total, "idle" => $idle];
    }
    return ["total" => 0, "idle" => 0];
}

/**
 * Verifica si un contenedor está activo conectando a su puerto TCP
 */
function checkContainer($host, $port, $timeout = 0.5) {
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) { 
        fclose($fp); 
        return true; 
    }
    return false;
}

/**
 * Define la infraestructura conocida del SOC. 
 */
function getInfrastructure() {
    return [
        ['host' => 's1_nginx', 'port' => 80, 'name' => 'Nginx Reverse Proxy', 'os' => 'Alpine'],
        ['host' => 's4_mariadb', 'port' => 3306, 'name' => 'MariaDB Database', 'os' => 'Debian'],
        ['host' => 's5_redis', 'port' => 6379, 'name' => 'Redis Cache', 'os' => 'Alpine'],
        ['host' => 's12_ollama', 'port' => 11434, 'name' => 'Ollama AI Engine', 'os' => 'Ubuntu'],
        ['host' => 's8_grafana', 'port' => 3000, 'name' => 'Grafana Dashboards', 'os' => 'Alpine'],
        ['host' => 's6_openldap', 'port' => 389, 'name' => 'OpenLDAP Auth', 'os' => 'Debian'],
        ['host' => 's7_wazuh', 'port' => 1514, 'name' => 'Wazuh Manager', 'os' => 'CentOS'],
        ['host' => 's9_scanner', 'port' => 9000, 'name' => 'Network Scanner', 'os' => 'PHP-Alpine'],
        ['host' => 's10_postfix', 'port' => 25, 'name' => 'Mail Server', 'os' => 'Ubuntu'],
        ['host' => 's11_snort', 'port' => 0, 'name' => 'IDS Snort', 'os' => 'Debian'] // Puerto corregido a 0 (Monitoreo exclusivo por API de Docker)
    ];
}

/**
 * Obtiene el estado real de cada nodo utilizando sockets TCP o la API nativa de Docker
 */
function getNodeStatus($infrastructure) {
    $list = [];
    foreach ($infrastructure as $node) {
        $online = false;

        // Forzar chequeo por Socket de Docker para servicios críticos o pasivos (Snort y OpenLDAP)
        if ($node['host'] === 's11_snort' || $node['host'] === 's6_openldap') {
            $socket = @fsockopen("unix:///var/run/docker.sock", -1, $errno, $errstr, 0.5);
            
            if ($socket) {
                // Usamos HTTP/1.1 y forzamos el cierre de conexión para asegurar una lectura limpia de la API
                $request = "GET /containers/{$node['host']}/json HTTP/1.1\r\n";
                $request .= "Host: localhost\r\n";
                $request .= "Connection: close\r\n\r\n";
                fwrite($socket, $request);
                
                $response = "";
                while (!feof($socket)) {
                    $response .= fgets($socket, 1024);
                }
                fclose($socket);
                
                // Regex mejorada: busca tanto el estado estructurado como las llaves planas del estado de Docker
                if (preg_match('/"Running"\s*:\s*true/i', $response) || preg_match('/"status"\s*:\s*"running"/i', $response)) {
                    $online = true;
                }
            } else {
                // FALLBACK DE SEGURIDAD: Si el socket de Docker no tiene permisos de lectura de manera temporal,
                // intentamos un chequeo de red tradicional para OpenLDAP en lugar de dar Offline directamente.
                if ($node['host'] === 's6_openldap') {
                    $online = checkContainer($node['host'], 389);
                }
            }
        } else {
            // Validación por puertos tradicional para el resto de la infraestructura
            $online = checkContainer($node['host'], $node['port']);
        }
        
        $list[] = [
            "host"   => $node['host'],
            "name"   => $node['name'],
            "os"     => $node['os'],
            "status" => $online ? "Online" : "Offline",
            "color"  => $online ? "text-emerald-400 bg-emerald-400/10" : "text-red-400 bg-red-400/10",
            "dot"    => $online ? "bg-emerald-400" : "bg-red-400"
        ];
    }
    return $list;
}

/**
 * Calcula la integridad del sistema basada en nodos activos
 */
function calculateHealth($active, $total) {
    return $total > 0 ? round(($active / $total) * 100) : 0;
}

/* ---- COMPONENTES HTML REUTILIZABLES (Estilo Glass Dashboard) ---- */
$glassClass = "bg-glass border border-glass rounded-xl shadow-lg";

function panelMetric($title, $value, $icon = "fa-server", $colorClass = "text-blue-400") {
    global $glassClass;
    return "
    <div class='$glassClass p-6 relative overflow-hidden group hover:border-blue-500/50 transition-all'>
        <div class='absolute -right-4 -top-4 opacity-5 text-7xl group-hover:scale-110 transition-transform'>
            <i class='fas $icon $colorClass'></i>
        </div>
        <h3 class='text-[10px] text-muted font-bold uppercase tracking-[0.2em] mb-2'>$title</h3>
        <p class='text-3xl font-black $colorClass tracking-tight'>$value</p>
    </div>";
}

function panelLink($title, $desc, $url, $icon = "fa-link", $iconBg = "bg-nav") {
    global $glassClass;
    return "
    <a href='$url' target='_blank' class='block $glassClass p-6 hover:border-blue-500/50 transition-all group relative overflow-hidden'>
        <div class='flex items-start gap-4'>
            <div class='p-3 $iconBg rounded-lg border border-glass group-hover:text-white transition-colors'>
                <i class='fas $icon text-xl text-blue-400'></i>
            </div>
            <div>
                <h3 class='text-sm font-bold mb-1 group-hover:text-blue-400 transition-colors'>$title</h3>
                <p class='text-xs text-muted leading-relaxed'>$desc</p>
            </div>
        </div>
    </a>";
}

function panelTable($title, $nodes) {
    global $glassClass;
    $html = "<div class='$glassClass p-6 flex flex-col h-full'>";
    if (!empty($title)) {
        $html .= "<h3 class='text-[11px] text-muted font-bold uppercase tracking-[0.2em] mb-4 flex items-center gap-2'><i class='fas fa-network-wired text-blue-500'></i> $title</h3>";
    }
    $html .= "<div class='overflow-x-auto rounded-lg border border-glass bg-nav'><table class='w-full text-left border-collapse'>";
    $html .= "<thead><tr class='border-b border-glass text-[10px] uppercase tracking-wider text-muted'>
                <th class='p-3 font-semibold'>Host</th>
                <th class='p-3 font-semibold'>Servicio</th>
                <th class='p-3 font-semibold'>OS</th>
                <th class='p-3 font-semibold text-right'>Estado</th>
              </tr></thead><tbody class='text-xs divide-y divide-glass'>";
    
    foreach ($nodes as $n) {
        $html .= "<tr class='hover:bg-glass transition-colors'>
                    <td class='p-3 font-mono text-muted'>{$n['host']}</td>
                    <td class='p-3 font-medium'>{$n['name']}</td>
                    <td class='p-3 text-muted'>{$n['os']}</td>
                    <td class='p-3 text-right'>
                        <span class='inline-flex items-center justify-center min-w-[70px] gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {$n['color']}'>
                            <span class='w-1.5 h-1.5 rounded-full {$n['dot']}'></span>{$n['status']}
                        </span>
                    </td>
                  </tr>";
    }
    $html .= "</tbody></table></div></div>";
    return $html;
}

function panelHealth($health) {
    global $glassClass;
    $color = $health > 80 ? 'bg-emerald-500' : ($health > 50 ? 'bg-amber-500' : 'bg-red-500');
    return "
    <div class='$glassClass p-6'>
        <h3 class='text-[11px] text-muted font-bold uppercase tracking-[0.2em] mb-4'><i class='fas fa-shield-halved text-blue-500 mr-2'></i>Integridad Global</h3>
        <div class='flex items-end justify-between mb-2'>
            <span class='text-3xl font-black'>{$health}%</span>
        </div>
        <div class='w-full bg-nav rounded-full h-2.5 border border-glass overflow-hidden'>
            <div class='$color h-2.5 rounded-full transition-all duration-1000' style='width: {$health}%'></div>
        </div>
    </div>";
}

/**
 * OBTENER LOGS REALES DE WAZUH Y SNORT (Estructura de Datos Limpia para JSON)
 */
function getSecurityLogs() {
    $logs = [];
    
    // 1. Obtener logs de Wazuh desde su API por Socket
    try {
        $socket = @fsockopen("unix:///var/run/docker.sock", -1, $errno, $errstr, 0.5);
        if ($socket) {
            $request = "GET /containers/s7_wazuh/logs?stdout=true&tail=5 HTTP/1.0\r\n\r\n";
            fwrite($socket, $request);
            $wazuhOutput = "";
            while (!feof($socket)) { $wazuhOutput .= fgets($socket, 1024); }
            fclose($socket);
            
            if (!empty($wazuhOutput)) {
                $lines = explode("\n", $wazuhOutput);
                foreach ($lines as $line) {
                    $startPos = strpos($line, '{"timestamp"');
                    if ($startPos !== false) {
                        $jsonStr = substr($line, $startPos);
                        $data = json_decode(trim($jsonStr), true);
                        if ($data && isset($data['rule']['description'])) {
                            $logs[] = [
                                'source' => 'WAZUH',
                                'message' => htmlspecialchars($data['rule']['description']),
                                'time' => isset($data['timestamp']) ? date('H:i:s', strtotime($data['timestamp'])) : date('H:i:s'),
                                'class' => 'text-yellow-400',
                                'icon' => 'fa-bug'
                            ];
                        }
                    }
                }
            }
        }
    } catch (\Exception $e) {}

    // 2. Obtener logs de Snort locales mapeados de forma limpia
    try {
        $snortLogPath = '/var/log/snort/alert';
        if (file_exists($snortLogPath)) {
            $fileLines = file($snortLogPath);
            $lastLines = array_slice($fileLines, -5);
            foreach ($lastLines as $line) {
                if (!empty(trim($line))) {
                    $time = date('H:i:s');
                    if (preg_match('/(\d{2}:\d{2}:\d{2})/', $line, $matches)) { $time = $matches[1]; }
                    $cleanMessage = preg_replace('/\[\*\*\]|\[\d+:\d+:\d+\]/', '', $line);
                    $logs[] = [
                        'source' => 'SNORT',
                        'message' => htmlspecialchars(substr(trim($cleanMessage), 0, 90)),
                        'time' => $time,
                        'class' => 'text-red-400',
                        'icon' => 'fa-fire'
                    ];
                }
            }
        }
    } catch (\Exception $e) {}

    return array_reverse($logs);
}
?>
