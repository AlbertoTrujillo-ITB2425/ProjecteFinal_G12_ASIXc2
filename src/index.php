<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirección si no hay sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

require_once "includes/components.php";
include "includes/header.php";

// Lógica de Telemetría
$infrastructure = getInfrastructure();
$nodeStatusList = getNodeStatus($infrastructure);

$activeNodesCount = count(array_filter($nodeStatusList, fn($n) => $n['status'] === 'Online'));
$totalNodes = count($nodeStatusList);
$sysHealth = calculateHealth($activeNodesCount, $totalNodes);

// Detección dinámica de la URL para Grafana
// Usamos HTTP_HOST para que funcione tanto en local como en la IP pública de AWS
$host = $_SERVER['HTTP_HOST'];
// Si el host ya incluye un puerto (ej: :8080), lo limpiamos para el enlace de Grafana
$cleanHost = explode(':', $host)[0];
$grafanaUrl = "http://" . $cleanHost . ":3000";
?>

<main class="p-6 md:p-10 max-w-7xl mx-auto space-y-8 animate-fade-in">
    
    <header class="mb-8 border-b border-white/10 pb-5 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                <h1 class="text-3xl font-black tracking-tighter uppercase italic">
                    SOC<span class="text-blue-500">COMMAND</span> <span class="text-slate-500 font-thin">v3.0</span>
                </h1>
            </div>
            <p class="text-[10px] text-slate-500 uppercase tracking-[0.4em] mt-2 font-black opacity-70">
                Data Center: <span class="text-slate-300">AWS-EC2-US-EAST-1</span> // Auditor: <?= htmlspecialchars($_SESSION['user_name'] ?? 'Root') ?>
            </p>
        </div>
        <div class="flex gap-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400 bg-emerald-500/5 border border-emerald-500/20 px-4 py-2 rounded-xl flex items-center gap-2 backdrop-blur-md shadow-xl">
                <i class="fas fa-shield-check"></i>
                Firewall Activo
            </span>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?= panelMetric("Nodos Críticos", "$activeNodesCount / $totalNodes", "fa-network-wired", "text-blue-400") ?>
        <?= panelMetric("Integridad SOC", "$sysHealth%", "fa-heart-pulse", $sysHealth > 80 ? "text-emerald-400" : "text-amber-400") ?>
        <?= panelMetric("IA Engine (Ollama)", checkContainer('s12_ollama', 11434) ? "READY" : "OFFLINE", "fa-microchip", checkContainer('s12_ollama', 11434) ? "text-purple-400" : "text-red-500") ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?= panelLink("Métricas Grafana", "Visualización de telemetría en puerto 3000.", $grafanaUrl, "fa-chart-line") ?>
                <?= panelLink("Threat Intelligence", "Scanner de red y detección de intrusos.", "modules/scanner/scanner.php", "fa-user-secret") ?>
            </div>
            
            <div class="glass-panel rounded-3xl overflow-hidden border border-white/5 shadow-2xl">
                <div class="p-6 border-b border-white/5 bg-white/5 flex justify-between items-center">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Estado de Infraestructura Docker</h3>
                    <span class="text-[9px] bg-blue-500/20 text-blue-400 px-2 py-1 rounded-md font-bold uppercase">Real-time</span>
                </div>
                <div class="overflow-x-auto">
                    <?= panelTable("", $nodeStatusList) ?>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="glass-panel p-6 rounded-3xl border border-white/5 shadow-2xl">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-6">Diagnóstico de Salud</h3>
                <?= panelHealth($sysHealth) ?>
            </div>
            
            <div class="glass-panel p-6 rounded-3xl border border-white/5 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Logs del Sistema</h3>
                    <i class="fas fa-circle-notch fa-spin text-blue-500 text-[10px]"></i>
                </div>
                <?= panelEvents() ?>
            </div>
        </aside>

    </div>
</main>

<?php include "includes/chatbot.php"; ?>
<?php include "includes/footer.php"; ?>

<style>
    /* Efecto de entrada suave */
    .animate-fade-in {
        animation: fadeIn 0.6s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Personalización del scroll de la tabla si es necesario */
    .overflow-x-auto::-webkit-scrollbar {
        height: 4px;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
    }
</style>
