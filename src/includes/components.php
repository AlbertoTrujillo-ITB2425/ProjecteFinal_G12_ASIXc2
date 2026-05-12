<?php

/**
 * Verifica si un contenedor está activo conectando a su puerto
 */
function checkContainer($host, $port, $timeout = 0.5) {
    // En entornos Docker con red bridge, a veces 'localhost' no funciona.
    // Si esto falla, puede ser necesario usar la IP interna del contenedor o el nombre de host si están en la misma red.
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) { 
        fclose($fp); 
        return true; 
    }
    return false;
}

/**
 * Define la infraestructura conocida. 
 * NOTA: Esta lista debe coincidir con tus nombres de contenedores en docker-compose.yml
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
        ['host' => 's11_snort', 'port' => 25, 'name' => 'IDS Snort', 'os' => 'Debian']
    ];
}

/**
 * Obtiene el estado real de cada nodo
 */
function getNodeStatus($infrastructure) {
    $list = [];
    foreach ($infrastructure as $node) {
        // Intentamos conectar al puerto específico
        $online = checkContainer($node['host'], $node['port']);
        
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
 * Calcula el porcentaje de salud basado en nodos activos
 */
function calculateHealth($active, $total) {
    return $total > 0 ? round(($active / $total) * 100) : 0;
}

/* ---- COMPONENTES HTML REUTILIZABLES (Estilo Glass Global) ---- */
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
    $html .= "<h3 class='text-[11px] text-muted font-bold uppercase tracking-[0.2em] mb-4 flex items-center gap-2'><i class='fas fa-network-wired text-blue-500'></i> $title</h3>";
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
 * OBTENER LOGS REALES DE WAHU Y SNORT
 * Reemplaza los eventos estáticos por datos reales de los contenedores
 */
function getSecurityLogs() {
    $logs = [];
    
    // 1. Leer últimos alertas de Wazuh (JSON)
    try {
        // Ejecutamos tail en el contenedor s7_wazuh
        $wazuhCmd = "docker exec s7_wazuh tail -n 5 /var/ossec/logs/alerts/alerts.json 2>/dev/null";
        $wazuhOutput = shell_exec($wazuhCmd);
        
        if ($wazuhOutput && !empty(trim($wazuhOutput))) {
            $lines = explode("\n", trim($wazuhOutput));
            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if ($data && isset($data['rule']['description'])) {
                    $logs[] = [
                        'source' => 'WAZUH',
                        'message' => htmlspecialchars($data['rule']['description']),
                        'time' => isset($data['timestamp']) ? date('H:i:s', strtotime($data['timestamp'])) : '--:--:--',
                        'level' => isset($data['rule']['level']) ? $data['rule']['level'] : '0',
                        'class' => 'text-yellow-400'
                    ];
                }
            }
        }
    } catch (\Exception $e) { /* Silenciar errores */ }

    // 2. Leer últimas alertas de Snort
    try {
        $snortCmd = "docker exec s11_snort tail -n 5 /var/log/snort/alert 2>/dev/null";
        $snortOutput = shell_exec($snortCmd);
        
        if ($snortOutput && !empty(trim($snortOutput))) {
            $lines = explode("\n", trim($snortOutput));
            foreach ($lines as $line) {
                if (!empty($line)) {
                    $time = '--:--:--';
                    // Intentar extraer timestamp simple si existe
                    if (preg_match('/\[(.*?)\]/', $line, $matches)) {
                         $time = substr($matches[1], -8, 8); 
                    }
                    
                    $logs[] = [
                        'source' => 'SNORT',
                        'message' => htmlspecialchars(substr($line, 0, 80)) . '...',
                        'time' => $time,
                        'level' => 'HIGH',
                        'class' => 'text-red-400'
                    ];
                }
            }
        }
    } catch (\Exception $e) { /* Silenciar errores */ }

    // Ordenar: más recientes primero (asumiendo que tail trae los últimos, invertimos para mostrar el último arriba si queremos cronológico inverso)
    // Pero como tail -n 5 trae los últimos 5, el último elemento del array es el más reciente.
    // Para mostrarlo arriba en la UI, invertimos el array.
    return array_reverse($logs);
}

/**
 * Renderiza los eventos del sistema usando datos reales
 */
function panelEvents() {
    global $glassClass;
    
    $securityLogs = getSecurityLogs();
    
    $html = "
    <div class='$glassClass p-6'>
        <h3 class='text-[11px] text-muted font-bold uppercase tracking-[0.2em] mb-4'><i class='fas fa-terminal text-blue-500 mr-2'></i>SOC Events Feed</h3>";
        
    if (empty($securityLogs)) {
        $html .= "
        <div class='space-y-3 font-mono text-[10px] text-center py-4 text-slate-500 italic'>
            <i class='fas fa-info-circle mr-1'></i> Esperando eventos de seguridad...
        </div>";
    } else {
        $html .= "<div class='space-y-2 max-h-[200px] overflow-y-auto pr-2 custom-scrollbar'>";
        foreach ($securityLogs as $log) {
            $icon = $log['source'] === 'WAZUH' ? 'fa-bug text-purple-400' : 'fa-fire text-orange-400';
            $html .= "
            <div class='flex gap-3 items-start p-2 rounded bg-nav border border-glass hover:bg-glass/80 transition-colors'>
                <div class='mt-0.5'>
                    <i class='fas $icon text-[10px]'></i>
                </div>
                <div class='flex-1 min-w-0'>
                    <div class='flex justify-between items-center mb-1'>
                        <span class='text-[9px] font-bold uppercase {$log['class']}'>{$log['source']}</span>
                        <span class='text-[9px] text-slate-500 font-mono'>{$log['time']}</span>
                    </div>
                    <p class='text-[10px] text-slate-300 leading-tight break-words'>{$log['message']}</p>
                </div>
            </div>";
        }
        $html .= "</div>";
    }
    
    $html .= "</div>";
    return $html;
}
?>
