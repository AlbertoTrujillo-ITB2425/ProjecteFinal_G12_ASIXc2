<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

require_once "includes/components.php";
include "includes/header.php";

$infrastructure = getInfrastructure();
$nodeStatusList = getNodeStatus($infrastructure);

$activeNodesCount = count(array_filter($nodeStatusList, fn($n) => $n['status'] === 'Online'));
$totalNodes = count($nodeStatusList);
$sysHealth = calculateHealth($activeNodesCount, $totalNodes);
?>

<main class="p-6 md:p-10 max-w-7xl mx-auto space-y-8">
    
    <!-- Header del Dashboard -->
    <header class="mb-8 border-b border-glass pb-5 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-widest uppercase">
                CYBER<span class="text-blue-500">PYME</span>
            </h1>
            <p class="text-[11px] text-muted uppercase tracking-[0.3em] mt-2 font-bold">Panel de Telemetría Global</p>
        </div>
        <div class="flex gap-3">
            <span class="text-[10px] uppercase tracking-widest text-emerald-400 bg-glass border border-emerald-500/30 px-4 py-2 rounded-lg flex items-center gap-2 shadow-lg">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Sistema Operativo
            </span>
        </div>
    </header>

    <!-- Métricas principales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?= panelMetric("Nodos Activos", "$activeNodesCount / $totalNodes", "fa-network-wired", "text-blue-400") ?>
        <?= panelMetric("Salud del Sistema", "$sysHealth%", "fa-heart-pulse", $sysHealth > 80 ? "text-emerald-400" : "text-amber-400") ?>
        <?= panelMetric("Motor IA (Ollama)", checkContainer('s12_ollama', 11434) ? "OPERATIVO" : "EN ESPERA", "fa-microchip", "text-purple-400") ?>
    </div>

    <!-- Contenido principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?= panelLink("Métricas Grafana", "Análisis de rendimiento en tiempo real.", "http://{$_SERVER['SERVER_NAME']}:3000", "fa-chart-pie") ?>
                <?= panelLink("Threat Intelligence", "Escaneo de red y monitorización de intrusiones.", "modules/scanner/scanner.php", "fa-radar") ?>
            </div>
            <?= panelTable("Estado de Infraestructura Docker", $nodeStatusList) ?>
        </div>

        <div class="space-y-6">
            <?= panelHealth($sysHealth) ?>
            <?= panelEvents() ?>
        </div>
    </div>
</main>

<?php include "includes/chatbot.php"; ?>
<?php include "includes/footer.php"; ?>
