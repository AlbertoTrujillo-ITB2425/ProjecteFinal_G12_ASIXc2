<?php
/**
 * CYBERPYME SOC - Professional Edition v6.5.0
 * Engine: Optimizado, Libre de Bugs, Monitorización Real de Docker
 */
session_start();

// Cabeceras de seguridad
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

// Identidad del Auditor
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Auditor SOC';
$userRole = $_SESSION['user_role'] ?? 'Analista Nivel 1';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true";

// =========================================================================
// MONITORIZACIÓN REAL DE INFRAESTRUCTURA (DOCKER)
// =========================================================================

// Función para comprobar si un puerto de un contenedor está abierto
function checkContainer($host, $port, $timeout = 1) {
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

// Lista de contenedores reales de tu 'docker ps'
$infrastructure = [
    ['host' => 's1_nginx', 'port' => 80, 'name' => 'Nginx Reverse Proxy', 'os' => 'Alpine'],
    ['host' => 's4_mariadb', 'port' => 3306, 'name' => 'MariaDB Database', 'os' => 'Debian'],
    ['host' => 's5_redis', 'port' => 6379, 'name' => 'Redis Cache', 'os' => 'Alpine'],
    ['host' => 's12_ollama', 'port' => 11434, 'name' => 'Ollama AI Engine', 'os' => 'Ubuntu'],
    ['host' => 's8_grafana', 'port' => 3000, 'name' => 'Grafana Dashboards', 'os' => 'Alpine'],
    ['host' => 's6_openldap', 'port' => 389, 'name' => 'OpenLDAP Auth', 'os' => 'Debian'],
    ['host' => 's7_wazuh', 'port' => 55000, 'name' => 'Wazuh Manager', 'os' => 'CentOS'],
];

$activeNodesCount = 0;
$nodeStatusList = [];

foreach ($infrastructure as $node) {
    $isOnline = checkContainer($node['host'], $node['port']);
    if ($isOnline) $activeNodesCount++;
    
    $nodeStatusList[] = [
        'name' => $node['name'],
        'host' => $node['host'],
        'os' => $node['os'],
        'status' => $isOnline ? 'Online' : 'Offline',
        'color' => $isOnline ? 'text-emerald-400' : 'text-red-500',
        'icon' => $isOnline ? 'fa-check-circle' : 'fa-times-circle'
    ];
}

$totalNodes = count($infrastructure);
$sysHealth = $totalNodes > 0 ? round(($activeNodesCount / $totalNodes) * 100) : 0;

// Para los logs y amenazas, idealmente aquí harías una consulta real a Wazuh o a tu base de datos.
// Por ahora, dejamos valores placeholder si no hay conexión directa a la API de Wazuh implementada aún.
$activeThreats = 0; // Aquí iría: wazuh_api_get_active_threats()
$blockedIPs = 0;    // Aquí iría: snort_get_blocked_ips()
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SOC | CyberPYME G12</title>
    
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛡️</text></svg>">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body.light-mode { background-color: #f8fafc !important; color: #0f172a !important; }
        body.light-mode .text-white { color: #0f172a !important; }
        body.light-mode .text-slate-400 { color: #475569 !important; }
        
        .glass-panel { 
            background: rgba(15, 23, 42, 0.7); 
            border: 1px solid rgba(255, 255, 255, 0.05); 
            backdrop-filter: blur(12px); 
            border-radius: 1rem; 
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        body.light-mode .glass-panel { 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
        }
        
        .glass-panel:hover { border-color: rgba(14, 165, 233, 0.3); }
        .bg-glow { background: radial-gradient(circle at 50% -20%, rgba(14, 165, 233, 0.15) 0%, transparent 60%); }
        
        @keyframes scanline {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }
        .scanner-line {
            position: absolute; top: 0; left: 0; width: 100%; height: 2px;
            background: linear-gradient(to right, transparent, #0ea5e9, transparent);
            animation: scanline 2s linear infinite; opacity: 0.5;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col transition-colors duration-300 bg-slate-950 text-white relative overflow-x-hidden">

    <div class="fixed inset-0 pointer-events-none opacity-[0.03] bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
    <div class="bg-glow fixed inset-0 pointer-events-none z-0"></div>

    <div class="relative z-[100] w-full">
        <?php include 'includes/header.php'; ?>
    </div>

    <main class="max-w-7xl mx-auto px-6 py-8 flex-grow flex flex-col relative z-10 w-full">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold mb-4 tracking-widest uppercase shadow-lg shadow-emerald-500/5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    SYSTEM ONLINE
                </div>
                <div class="text-sky-500 font-bold tracking-widest text-xs mb-1" data-i18n="hero_tag">G12 NEXT-GEN SOC</div>
                <h2 class="text-4xl md:text-5xl font-black leading-tight tracking-tight text-white" data-i18n="hero_title">
                    Auditoría <br><span class="text-sky-500 italic">Inteligente.</span>
                </h2>
                <p class="text-slate-400 text-sm mt-2 font-light" data-i18n="hero_desc">
                    Defensa proactiva y monitorización de activos potenciada por AI-Engine Qwen2.5.
                </p>
            </div>
            
            <?php if ($isLoggedIn): ?>
            <div class="glass-panel p-3 px-5 flex items-center gap-4 bg-slate-900/50">
                <img src="<?php echo $avatarUrl; ?>" class="w-10 h-10 rounded-lg border border-sky-500/30" alt="Avatar">
                <div class="flex flex-col">
                    <span class="text-xs font-black text-sky-400 uppercase tracking-wide"><?php echo htmlspecialchars($userName); ?></span>
                    <span class="text-[10px] text-slate-500 font-mono"><?php echo $userRole; ?> | ID: #<?php echo $_SESSION['user_id'] ?? '001'; ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 relative z-20">
            <div class="glass-panel p-5 border-l-4 border-l-sky-500 relative overflow-hidden">
                <i class="fas fa-network-wired absolute right-[-10px] bottom-[-10px] text-5xl opacity-5 text-sky-500"></i>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Nodos Activos</p>
                <p class="text-3xl font-black"><?php echo $activeNodesCount; ?>/<?php echo $totalNodes; ?></p>
            </div>
            <div class="glass-panel p-5 border-l-4 border-l-red-500 relative overflow-hidden">
                <i class="fas fa-bug absolute right-[-10px] bottom-[-10px] text-5xl opacity-5 text-red-500"></i>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Amenazas Críticas</p>
                <p class="text-3xl font-black text-red-400"><?php echo $activeThreats; ?></p>
            </div>
            <div class="glass-panel p-5 border-l-4 border-l-amber-500 relative overflow-hidden">
                <i class="fas fa-shield-halved absolute right-[-10px] bottom-[-10px] text-5xl opacity-5 text-amber-500"></i>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Ataques Bloqueados</p>
                <p class="text-3xl font-black text-amber-400"><?php echo $blockedIPs; ?></p>
            </div>
            <div class="glass-panel p-5 border-l-4 border-l-emerald-500 relative overflow-hidden">
                <i class="fas fa-heartbeat absolute right-[-10px] bottom-[-10px] text-5xl opacity-5 text-emerald-500"></i>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1" data-i18n="status_health">Salud Red</p>
                <p class="text-3xl font-black text-emerald-400"><?php echo $sysHealth; ?>%</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 relative z-20"> 
            <div class="lg:col-span-2 flex flex-col gap-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="glass-panel p-8 group relative overflow-hidden flex flex-col pointer-events-auto">
                        <div class="scanner-line hidden group-hover:block pointer-events-none"></div>
                        <div class="w-12 h-12 bg-sky-500/10 rounded-xl flex items-center justify-center border border-sky-500/20 mb-4 group-hover:bg-sky-500/20 transition-colors">
                            <i class="fas fa-radar text-sky-400 text-xl group-hover:animate-pulse"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2 uppercase tracking-tight" data-i18n="card_audit_title">Audit Engine</h3>
                        <p class="text-slate-500 text-sm mb-6 flex-grow" data-i18n="card_audit_desc">Mapeo de red Nmap y análisis de vulnerabilidades CVE automatizado.</p>
                        
                        <a href="scanner.php" class="w-full py-3 rounded-lg bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs uppercase tracking-widest transition-all flex justify-center items-center gap-2 shadow-lg shadow-sky-600/20 cursor-pointer text-center">
                            <i class="fas fa-rocket"></i> <span data-i18n="card_audit_btn">Iniciar Escáner</span>
                        </a>
                    </div>

                    <div class="glass-panel p-8 group flex flex-col pointer-events-auto">
                        <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center border border-amber-500/20 mb-4 group-hover:bg-amber-500/20 transition-colors">
                            <i class="fas fa-brain text-amber-400 text-xl group-hover:scale-110 transition-transform"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2 uppercase tracking-tight" data-i18n="card_threat_title">Threat Intel</h3>
                        <p class="text-slate-500 text-sm mb-6 flex-grow" data-i18n="card_threat_desc">Detección de intrusiones y monitorización de tráfico en tiempo real.</p>
                        
                        <a href="http://<?php echo $_SERVER['SERVER_NAME']; ?>:3000" target="_blank" class="w-full py-3 rounded-lg bg-slate-800 border border-amber-500/30 hover:bg-slate-700 text-amber-400 font-bold text-xs uppercase tracking-widest transition-all flex justify-center items-center gap-2 cursor-pointer text-center">
                            <i class="fas fa-terminal"></i> <span data-i18n="card_threat_btn">Monitor Grafana</span>
                        </a>
                    </div>
                </div>

                <div class="glass-panel p-6 pointer-events-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest"><i class="fas fa-server mr-2"></i>Nodos Docker Monitorizados</h3>
                        <span class="text-[9px] bg-emerald-500/20 text-emerald-400 px-2 py-1 rounded border border-emerald-500/30">Live Sync</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-400">
                            <thead class="text-[10px] uppercase bg-white/5 border-b border-white/10">
                                <tr>
                                    <th class="px-4 py-2">Hostname</th>
                                    <th class="px-4 py-2">Servicio</th>
                                    <th class="px-4 py-2">OS</th>
                                    <th class="px-4 py-2 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs font-mono">
                                <?php foreach ($nodeStatusList as $node): ?>
                                <tr class="border-b border-white/5 hover:bg-white/5">
                                    <td class="px-4 py-3 text-white"><?php echo $node['host']; ?></td>
                                    <td class="px-4 py-3"><?php echo $node['name']; ?></td>
                                    <td class="px-4 py-3"><i class="fab fa-linux text-slate-500 mr-1"></i> <?php echo $node['os']; ?></td>
                                    <td class="px-4 py-3 text-right <?php echo $node['color']; ?>"><i class="fas <?php echo $node['icon']; ?> text-[10px] mr-1"></i><?php echo $node['status']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="flex flex-col gap-6">
                
                <div class="glass-panel p-6 pointer-events-auto">
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-5 border-b border-white/5 pb-2">Recursos SOC</h3>
                    
                    <div class="mb-4">
                        <div class="flex justify-between text-xs mb-1">
                            <span>Sys Health Index</span>
                            <span class="text-sky-400 font-mono"><?php echo $sysHealth; ?>%</span>
                        </div>
                        <div class="w-full bg-slate-800 rounded-full h-1.5">
                            <div class="bg-sky-500 h-1.5 rounded-full transition-all duration-1000" style="width: <?php echo $sysHealth; ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="glass-panel p-6 flex-grow flex flex-col pointer-events-auto">
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4 border-b border-white/5 pb-2">Log de Eventos (Simulado)</h3>
                    
                    <ul class="space-y-4 flex-grow">
                        <li class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded bg-emerald-500/10 flex items-center justify-center shrink-0 border border-emerald-500/20 mt-0.5">
                                <i class="fas fa-check text-emerald-400 text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-xs text-white">Docker Network Check</p>
                                <p class="text-[9px] text-slate-500 font-mono">Justo ahora • Escaneo interno</p>
                            </div>
                        </li>
                    </ul>
                    
                    <a href="http://<?php echo $_SERVER['SERVER_NAME']; ?>:3000" target="_blank" class="w-full mt-4 py-2 border border-white/10 rounded text-[10px] uppercase font-bold text-slate-400 hover:text-white hover:bg-white/5 transition-colors cursor-pointer text-center block">
                        Ver Todos los Logs en Grafana
                    </a>
                </div>

            </div>
        </div>
    </main>

    <script src="assets/js/languages.js"></script>
    <script src="assets/js/main.js"></script>
    
</body>
</html>
