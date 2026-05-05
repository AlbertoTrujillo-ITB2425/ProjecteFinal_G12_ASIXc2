<?php

function checkContainer($host, $port, $timeout = 0.5) {
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) { fclose($fp); return true; }
    return false;
}

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

function getNodeStatus($infrastructure) {
    $list = [];
    foreach ($infrastructure as $node) {
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

function panelLink($title, $desc, $url, $icon = "fa-link") {
    global $glassClass;
    return "
    <a href='$url' target='_blank' class='block $glassClass p-6 hover:border-blue-500/50 transition-all group relative overflow-hidden'>
        <div class='flex items-start gap-4'>
            <div class='p-3 bg-nav rounded-lg border border-glass group-hover:text-white transition-colors'>
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

function panelEvents() {
    global $glassClass;
    return "
    <div class='$glassClass p-6'>
        <h3 class='text-[11px] text-muted font-bold uppercase tracking-[0.2em] mb-4'><i class='fas fa-terminal text-blue-500 mr-2'></i>Últimos Eventos</h3>
        <div class='space-y-3 font-mono text-[10px]'>
            <div class='flex gap-3 text-muted p-2 rounded bg-nav border border-glass'>
                <span class='text-blue-500'>[".date('H:i:s')."]</span>
                <span>Sesión iniciada correctamente.</span>
            </div>
            <div class='flex gap-3 text-muted p-2 rounded bg-nav border border-glass'>
                <span class='text-emerald-500'>[SYSTEM]</span>
                <span>Análisis de red en segundo plano...</span>
            </div>
        </div>
    </div>";
}
?>
