<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Control de Acceso
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

require_once "includes/components.php";
include "includes/header.php";

// 2. Lógica de Telemetría Real
$infrastructure = getInfrastructure();
$nodeStatusList = getNodeStatus($infrastructure);

$activeNodesCount = count(array_filter($nodeStatusList, fn($n) => $n['status'] === 'Online'));
$totalNodes = count($nodeStatusList);
$sysHealth = calculateHealth($activeNodesCount, $totalNodes);

// 3. Detección dinámica para Grafana
$host = $_SERVER['HTTP_HOST'];
$cleanHost = explode(':', $host)[0];
$grafanaUrl = "http://" . $cleanHost . ":3000";
?>

<main class="p-6 md:p-10 max-w-7xl mx-auto space-y-8 animate-fade-in text-slate-900 dark:text-white">
    
    <header class="mb-8 border-b border-slate-200 dark:border-white/10 pb-5 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse shadow-[0_0_8px_#3b82f6]"></div>
                <h1 class="text-3xl font-black tracking-tighter uppercase italic">
                    SOC<span class="text-blue-500">COMMAND</span> <span class="text-slate-400 dark:text-slate-500 font-thin">v3.0</span>
                </h1>
            </div>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-[0.4em] mt-2 font-black opacity-80">
                Data Center: <span class="text-slate-700 dark:text-slate-300">AWS-EC2-US-EAST-1</span> // Auditor: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Root') ?>
            </p>
        </div>
        <div class="flex gap-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-4 py-2 rounded-xl flex items-center gap-2 backdrop-blur-md shadow-lg">
                <i class="fas fa-shield-check"></i>
                Firewall Activo
            </span>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?= panelMetric("Nodos Críticos", "$activeNodesCount / $totalNodes", "fa-network-wired", "text-blue-500") ?>
        <?= panelMetric("Integridad SOC", "$sysHealth%", "fa-heart-pulse", $sysHealth > 80 ? "text-emerald-500" : "text-amber-500") ?>
        <?= panelMetric("IA Engine (Ollama)", checkContainer('s12_ollama', 11434) ? "READY" : "OFFLINE", "fa-microchip", "text-purple-500") ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="<?= $grafanaUrl ?>" target="_blank" class="block group">
                    <div class="glass-panel p-6 rounded-3xl border border-slate-200 dark:border-white/5 bg-white/50 dark:bg-white/5 hover:bg-blue-50 dark:hover:bg-white/10 transition-all duration-300 shadow-xl">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-2xl flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                                <i class="fas fa-chart-line text-xl"></i>
                            </div>
                            <i class="fas fa-external-link-alt text-[10px] text-slate-400"></i>
                        </div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-white">Métricas Grafana</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Visualización de telemetría en puerto 3000.</p>
                    </div>
                </a>
                <?= panelLink("Threat Intelligence", "Scanner de red y detección de intrusos.", "modules/scanner/scanner.php", "fa-user-secret") ?>
            </div>
            
            <div class="glass-panel rounded-3xl overflow-hidden border border-slate-200 dark:border-white/5 bg-white/50 dark:bg-white/5 shadow-2xl">
                <div class="p-6 border-b border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 flex justify-between items-center">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Estado de Infraestructura Docker</h3>
                    <span class="text-[9px] bg-blue-500/10 text-blue-600 dark:text-blue-400 px-2 py-1 rounded-md font-bold uppercase">Real-time Data</span>
                </div>
                <div class="overflow-x-auto">
                    <?= panelTable("", $nodeStatusList) ?>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="glass-panel p-6 rounded-3xl border border-slate-200 dark:border-white/5 bg-white/50 dark:bg-white/5 shadow-2xl">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6 italic">Diagnóstico de Salud</h3>
                <?= panelHealth($sysHealth) ?>
            </div>
            
            <div class="glass-panel p-6 rounded-3xl border border-slate-200 dark:border-white/5 bg-white/50 dark:bg-white/5 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 italic">Logs del Sistema</h3>
                    <i class="fas fa-circle-notch fa-spin text-blue-500 text-[10px]"></i>
                </div>
                <div class="space-y-2">
                    <?= panelEvents() ?>
                </div>
            </div>
        </aside>

    </div>
</main>

<?php include "includes/chatbot.php"; ?>
<?php include "includes/footer.php"; ?>

<style>
    /* Soporte para modo claro/oscuro en el glass-panel */
    .glass-panel {
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }
    
    .animate-fade-in {
        animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Scrollbar minimalista adaptativa */
    .overflow-x-auto::-webkit-scrollbar { height: 4px; }
    .overflow-x-auto::-webkit-scrollbar-thumb { 
        background: rgba(100, 100, 100, 0.2); 
        border-radius: 10px; 
    }
</style>
