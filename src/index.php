<?php
/**
 * CYBERPYME SOC - Professional Edition v6.5.0
 * HUB Central de Operaciones G12
 */
session_start();
require_once 'db_conn.php'; 

// Cabeceras de seguridad
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

// Identidad del Auditor
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Auditor SOC';
$userRole = $_SESSION['user_role'] ?? 'Analista G12';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true";

/**
 * MONITORIZACIÓN DE NODOS DOCKER
 */
function checkContainer($host, $port, $timeout = 0.5) {
    $start = microtime(true);
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    $end = microtime(true);
    if ($fp) {
        fclose($fp);
        return ['online' => true, 'latency' => round(($end - $start) * 1000, 2)];
    }
    return ['online' => false, 'latency' => 0];
}

$infrastructureList = [
    ['host' => 's1_nginx',    'port' => 80,    'name' => 'Edge Proxy',      'os' => 'Alpine'],
    ['host' => 's2_node',     'port' => 9000,  'name' => 'App Engine',      'os' => 'Alpine'],
    ['host' => 's4_mariadb',  'port' => 3306,  'name' => 'SQL Master',      'os' => 'Debian'],
    ['host' => 's7_wazuh',    'port' => 1514,  'name' => 'Wazuh SIEM',      'os' => 'CentOS'],
    ['host' => 's12_ollama',  'port' => 11434, 'name' => 'AI Qwen2.5',      'os' => 'Ubuntu'],
];

$activeNodesCount = 0;
$nodeStatusList = [];
foreach ($infrastructureList as $node) {
    $check = checkContainer($node['host'], $node['port']);
    if ($check['online']) $activeNodesCount++;
    $nodeStatusList[] = array_merge($node, $check);
}

// Estadísticas de Base de Datos
try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalScans = $pdo->query("SELECT COUNT(*) FROM scans")->fetchColumn();
} catch (Exception $e) { $totalUsers = 0; $totalScans = 0; }

$sysHealth = round(($activeNodesCount / count($infrastructureList)) * 100);
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOC HUB | G12 CyberPyme</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #020617; font-family: 'Inter', sans-serif; }
        .glass-panel { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .tool-card:hover { border-color: #0ea5e9; transform: translateY(-4px); }
        .status-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
    </style>
</head>
<body class="text-slate-300 min-h-screen flex flex-col">

    <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-10 w-full flex-grow">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-6">
            <div>
                <h1 class="text-4xl font-black text-white tracking-tighter uppercase">Security <span class="text-sky-500">Operations</span> Center</h1>
                <p class="text-slate-500 mt-1 tracking-widest text-xs font-bold uppercase">Consola de Mando G12 Next-Gen</p>
            </div>
            <div class="flex gap-4">
                <div class="glass-panel px-6 py-3 rounded-2xl text-center">
                    <p class="text-[10px] font-black text-sky-500 uppercase">Salud Sistema</p>
                    <p class="text-xl font-bold text-white"><?= $sysHealth ?>%</p>
                </div>
                <div class="glass-panel px-6 py-3 rounded-2xl text-center">
                    <p class="text-[10px] font-black text-emerald-500 uppercase">Auditores</p>
                    <p class="text-xl font-bold text-white"><?= $totalUsers ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            
            <a href="scanner.php" class="glass-panel p-6 tool-card transition-all group">
                <div class="w-12 h-12 bg-sky-500/10 rounded-xl flex items-center justify-center mb-4 border border-sky-500/20 group-hover:bg-sky-500 group-hover:text-white transition-colors">
                    <i class="fas fa-radar text-xl"></i>
                </div>
                <h3 class="text-white font-bold uppercase text-sm mb-2" data-i18n="card_audit_title">Audit Engine</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Escaneo de vulnerabilidades Nmap y mapeo de red en tiempo real.</p>
            </a>

            <a href="forensics.php" class="glass-panel p-6 tool-card transition-all group border-l-purple-500/30">
                <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center mb-4 border border-purple-500/20 group-hover:bg-purple-500 group-hover:text-white transition-colors text-purple-400">
                    <i class="fas fa-fingerprint text-xl"></i>
                </div>
                <h3 class="text-white font-bold uppercase text-sm mb-2">Digital Forensics</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Investigación de incidentes y análisis de logs de seguridad avanzados.</p>
            </a>

            <a href="utils.php" class="glass-panel p-6 tool-card transition-all group border-l-emerald-500/30">
                <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-4 border border-emerald-500/20 group-hover:bg-emerald-500 group-hover:text-white transition-colors text-emerald-400">
                    <i class="fas fa-robot text-xl"></i>
                </div>
                <h3 class="text-white font-bold uppercase text-sm mb-2">AI Cyber Assistant</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Soporte mediante LLM Qwen2.5 para triaje de amenazas y payloads.</p>
            </a>

            <a href="socemail.php" class="glass-panel p-6 tool-card transition-all group border-l-amber-500/30">
                <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center mb-4 border border-amber-500/20 group-hover:bg-amber-500 group-hover:text-white transition-colors text-amber-400">
                    <i class="fas fa-envelope-open-text text-xl"></i>
                </div>
                <h3 class="text-white font-bold uppercase text-sm mb-2">Secure Mail</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Gestión de alertas y comunicaciones cifradas del centro de seguridad.</p>
            </a>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 glass-panel p-8 rounded-[2rem]">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-black text-white uppercase tracking-tighter"><i class="fas fa-server mr-3 text-sky-500"></i>Estado de Infraestructura</h2>
                    <span class="text-[10px] bg-sky-500/10 text-sky-400 px-3 py-1 rounded-full border border-sky-500/20 font-mono">12 NODOS TOTALES</span>
                </div>
                <div class="space-y-3">
                    <?php foreach ($nodeStatusList as $node): ?>
                    <div class="flex items-center justify-between p-4 bg-slate-900/40 rounded-xl border border-white/5">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full <?= $node['online'] ? 'bg-emerald-500 status-pulse' : 'bg-red-500' ?>"></div>
                            <div>
                                <p class="text-xs font-bold text-white uppercase"><?= $node['name'] ?></p>
                                <p class="text-[10px] text-slate-500 font-mono"><?= $node['host'] ?> (<?= $node['os'] ?>)</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black <?= $node['online'] ? 'text-emerald-400' : 'text-red-500' ?>"><?= $node['online'] ? 'ACTIVO' : 'DOWN' ?></p>
                            <p class="text-[10px] text-slate-600 font-mono"><?= $node['latency'] ?> ms</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="http://<?= $_SERVER['SERVER_NAME'] ?>:3000" target="_blank" class="block w-full mt-6 py-3 bg-white/5 hover:bg-white/10 rounded-xl text-center text-[10px] font-black uppercase tracking-widest transition-all">
                    Abrir Monitor Completo (Grafana)
                </a>
            </div>

            <div class="space-y-6">
                <div class="glass-panel p-8 rounded-[2rem] text-center">
                    <img src="<?= $avatarUrl ?>" class="w-20 h-20 rounded-2xl mx-auto border-2 border-sky-500/20 mb-4 shadow-xl">
                    <h3 class="text-white font-bold uppercase"><?= $userName ?></h3>
                    <p class="text-[10px] text-sky-500 font-black mb-6"><?= $userRole ?></p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="profile.php" class="py-2 bg-sky-500/10 hover:bg-sky-500 text-sky-500 hover:text-white rounded-lg text-[9px] font-black uppercase transition-all border border-sky-500/20">Ajustes</a>
                        <a href="logout.php" class="py-2 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-lg text-[9px] font-black uppercase transition-all border border-red-500/20">Salir</a>
                    </div>
                </div>

                <div class="glass-panel p-6 rounded-[2.5rem]">
                    <h4 class="text-[10px] font-black text-slate-500 uppercase mb-4 tracking-widest">Utilidades de Sistema</h4>
                    <div class="space-y-2">
                        <a href="test_env.php" class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl transition-all group">
                            <i class="fas fa-vial text-slate-500 group-hover:text-sky-400 text-xs"></i>
                            <span class="text-[11px] font-bold">Diagnóstico Entorno</span>
                        </a>
                        <a href="translator.php" class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl transition-all group">
                            <i class="fas fa-language text-slate-500 group-hover:text-emerald-400 text-xs"></i>
                            <span class="text-[11px] font-bold">Gestor Traducciones</span>
                        </a>
                        <a href="reset_password.php" class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl transition-all group">
                            <i class="fas fa-key text-slate-500 group-hover:text-amber-400 text-xs"></i>
                            <span class="text-[11px] font-bold">Seguridad Credenciales</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <footer class="py-8 border-t border-white/5 text-center">
        <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.4em]" data-i18n="footer_rights">© 2026 CYBERPYME SOC G12. ASEGURANDO EL FUTURO.</p>
    </footer>

    <script src="assets/js/languages.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
