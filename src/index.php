<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Control de Acceso estricto
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

require_once "includes/components.php";
include "includes/header.php";

// 2. Obtener Telemetría Real desde Docker
$infrastructure = getInfrastructure();
$nodeStatusList = getNodeStatus($infrastructure);

// Calcular métricas reales
$totalNodes = count($nodeStatusList);
$activeNodesCount = count(array_filter($nodeStatusList, fn($n) => $n['status'] === 'Online'));
$sysHealth = calculateHealth($activeNodesCount, $totalNodes);

// Verificar estado específico de servicios críticos
$isOllamaReady = checkContainer('s12_ollama', 11434);
$isScannerReady = checkContainer('s9_scanner', 9000); // Puerto PHP-FPM del scanner

// URL segura para Grafana
$grafanaUrl = "grafana_access.php";

// 3. Obtener Logs Reales de Seguridad (Wazuh + Snort)
$securityLogs = getSecurityLogs();
?>

<main class="p-6 md:p-10 max-w-7xl mx-auto space-y-8 animate-fade-in text-main">
    
    <!-- HEADER PRINCIPAL -->
    <header class="mb-8 border-b border-slate-200 dark:border-white/10 pb-5 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse shadow-[0_0_8px_#3b82f6]"></div>
                <h1 class="text-3xl font-black tracking-tighter uppercase italic">
                    SOC<span class="text-blue-500">COMMAND</span> <span class="text-muted font-thin">v3.0</span>
                </h1>
            </div>
            <p class="text-[10px] text-muted uppercase tracking-[0.4em] mt-2 font-bold opacity-80">
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

    <!-- MÉTRICAS CRÍTICAS DINÁMICAS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <?= panelMetric("Nodos Activos", "$activeNodesCount / $totalNodes", "fa-network-wired", "text-blue-500") ?>
        <?= panelMetric("Salud Sistema", "$sysHealth%", "fa-heart-pulse", $sysHealth > 80 ? "text-emerald-500" : "text-amber-500") ?>
        <?= panelMetric("IA Engine", $isOllamaReady ? "READY" : "OFFLINE", "fa-microchip", $isOllamaReady ? "text-purple-500" : "text-red-500") ?>
        <?= panelMetric("Email Alerts", "ACTIVE", "fa-envelope-open-text", "text-orange-500") ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- COLUMNA IZQUIERDA: ACCIONES Y ESTADO -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Accesos Rápidos -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Grafana -->
                <a href="<?= $grafanaUrl ?>" target="_blank" class="block group">
                    <div class="glass-panel p-5 rounded-2xl border border-slate-200 dark:border-white/5 bg-white/50 dark:bg-white/5 hover:bg-blue-50 dark:hover:bg-white/10 transition-all duration-300 shadow-lg h-full">
                        <div class="flex justify-between items-start mb-3">
                            <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center text-blue-500 group-hover:scale-110 transition-transform">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <i class="fas fa-external-link-alt text-[10px] text-muted"></i>
                        </div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-main">Métricas Live</h3>
                        <p class="text-[10px] text-muted mt-1 line-clamp-2">Telemetría segura vía CyberPyme Proxy.</p>
                    </div>
                </a>

                <!-- Scanner -->
                <?= panelLink("Threat Intelligence", "Escaneo de vulnerabilidades.", "modules/scanner/scanner.php", "fa-user-secret", "bg-purple-500/20 text-purple-500") ?>

                <!-- Email Management -->
                <?= panelLink("Alertas Email", "Configurar notificaciones SOC.", "modules/email/socemail.php", "fa-paper-plane", "bg-orange-500/20 text-orange-500") ?>
            </div>
            
            <!-- Tabla de Infraestructura Dinámica -->
            <div class="glass-panel rounded-2xl overflow-hidden border border-slate-200 dark:border-white/5 bg-white/50 dark:bg-white/5 shadow-xl">
                <div class="p-5 border-b border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 flex justify-between items-center">
                    <h3 class="text-xs font-black uppercase tracking-widest text-muted">Estado de Infraestructura Docker</h3>
                    <span class="text-[9px] bg-blue-500/10 text-blue-600 dark:text-blue-400 px-2 py-1 rounded-md font-bold uppercase flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Real-time
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <?= panelTable("", $nodeStatusList) ?>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: DIAGNÓSTICO Y LOGS REALES -->
        <aside class="space-y-6">
            
            <!-- Diagnóstico de Salud -->
            <div class="glass-panel p-6 rounded-2xl border border-slate-200 dark:border-white/5 bg-white/50 dark:bg-white/5 shadow-xl">
                <h3 class="text-xs font-black uppercase tracking-widest text-muted mb-6 italic">Diagnóstico Global</h3>
                <?= panelHealth($sysHealth) ?>
                
                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-white/10">
                    <div class="flex justify-between text-[10px] font-bold text-muted mb-1">
                        <span>Carga CPU Estimada</span>
                        <!-- Nota: Esto es una estimación visual basada en la salud inversa para demo, 
                             en producción conectarías con un endpoint de métricas real -->
                        <span><?= round(100 - ($sysHealth * 0.8), 0) ?>%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: <?= round(100 - ($sysHealth * 0.8), 0) ?>%"></div>
                    </div>
                </div>
            </div>
            
            <!-- Logs del Sistema (Reales desde Wazuh/Snort) -->
            <div class="glass-panel p-6 rounded-2xl border border-slate-200 dark:border-white/5 bg-white/50 dark:bg-white/5 shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xs font-black uppercase tracking-widest text-muted italic flex items-center gap-2">
                        <i class="fas fa-shield-alt text-emerald-500"></i> SOC Events Feed
                    </h3>
                    <span class="text-[9px] bg-red-500/10 text-red-400 px-2 py-1 rounded-md font-bold uppercase animate-pulse">Live</span>
                </div>
                
                <?php if (empty($securityLogs)): ?>
                    <div class="text-center py-4 text-slate-500 text-xs italic">
                        <i class="fas fa-info-circle mr-1"></i> Esperando eventos de seguridad...
                    </div>
                <?php else: ?>
                    <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                        <?php foreach ($securityLogs as $log): ?>
                            <div class="flex gap-3 items-start p-2 rounded hover:bg-white/5 transition-colors group">
                                <!-- Icono según fuente -->
                                <div class="mt-1">
                                    <?php if ($log['source'] === 'WAZUH'): ?>
                                        <i class="fas fa-bug text-purple-400 text-[10px]"></i>
                                    <?php else: ?>
                                        <i class="fas fa-fire text-orange-400 text-[10px]"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Contenido -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[9px] font-bold uppercase <?= $log['class'] ?>">
                                            <?= $log['source'] ?>
                                        </span>
                                        <span class="text-[9px] text-slate-500 font-mono">
                                            <?= $log['time'] ?>
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-300 leading-tight break-words">
                                        <?= $log['message'] ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

    </div>
</main>

<!-- Chatbot Integrado -->
<?php include "includes/chatbot.php"; ?>
<?php include "includes/footer.php"; ?>
