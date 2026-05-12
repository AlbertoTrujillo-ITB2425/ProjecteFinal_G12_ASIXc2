<?php

require __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   CONFIGURACIÓN E INPUT
========================= */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Forbidden');
}

$target  = htmlspecialchars($_POST['target'] ?? 'Unknown');
$auditor = htmlspecialchars($_POST['auditor'] ?? 'SOC Analyst');
$ai      = trim($_POST['ai_analysis'] ?? '');
$logs    = trim($_POST['logs'] ?? '');

$date = gmdate('Y-m-d H:i:s') . ' UTC';
$reportId = 'SOC-' . strtoupper(substr(sha1(uniqid('', true)), 0, 8));

/* =========================
   LÓGICA DE DATOS
========================= */

// Parsear puertos
preg_match_all('/(\d+)\/(tcp|udp)\s+open\s+([^\s]+)/i', $logs, $m, PREG_SET_ORDER);
$ports = [];
foreach ($m as $x) {
    $ports[] = [
        'port' => "{$x[1]}/{$x[2]}",
        'service' => htmlspecialchars($x[3])
    ];
}

// Cálculo de riesgo
$risk = count($ports) * 8;
if (stripos($logs, 'telnet') !== false) $risk += 40;
if (stripos($logs, 'ftp') !== false) $risk += 25;

if ($risk > 80) {
    $level = ['CRITICAL', '#dc2626']; // Rojo
    $riskBg = '#fef2f2';
} elseif ($risk > 50) {
    $level = ['HIGH', '#ea580c']; // Naranja
    $riskBg = '#fff7ed';
} elseif ($risk > 20) {
    $level = ['MEDIUM', '#ca8a04']; // Amarillo
    $riskBg = '#fefce8';
} else {
    $level = ['LOW', '#16a34a']; // Verde
    $riskBg = '#f0fdf4';
}

function safe($text) {
    return nl2br(htmlspecialchars($text));
}

$summaryText = "
The target <strong>{$target}</strong> has been scanned for external vulnerabilities. 
The analysis identified multiple open services contributing to an increased attack surface. 
Based on the severity of exposed protocols and potential exploitability, the overall risk posture is classified as <strong style='color:{$level[1]}'>{$level[0]}</strong>.
<br><br>
<strong>Recommendation:</strong> Immediate review of firewall rules and service hardening is advised for all critical findings.
";

/* =========================
   CARGAR CSS EXTERNO
========================= */
// Ruta al archivo CSS
$cssPath = __DIR__ . '/../../assets/css/pdf.css';
$cssContent = '';

if (file_exists($cssPath)) {
    $cssContent = file_get_contents($cssPath);
} else {
    // Fallback por si no encuentra el archivo (opcional)
    $cssContent = "body { font-family: Helvetica, Arial, sans-serif; }";
}

/* =========================
   GENERACIÓN HTML
========================= */

ob_start();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    /* Inyectamos el CSS cargado desde el archivo */
    <?= $cssContent ?>
    
    /* Estilos dinámicos que dependen de variables PHP */
    .risk-container {
        background-color: <?= $riskBg ?> !important;
        border-color: <?= $level[1] ?> !important;
    }
    .risk-score {
        color: <?= $level[1] ?> !important;
    }
</style>
</head>

<body>

    <!-- HEADER CON TABLA -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="logo-text">SECURITY REPORT</div>
                <div class="logo-subtitle">Automated Vulnerability Assessment</div>
            </td>
            <td class="header-right">
                <div><strong>Date:</strong> <?= $date ?></div>
                <div><strong>ID:</strong> <?= $reportId ?></div>
                <div><strong>Analyst:</strong> <?= $auditor ?></div>
            </td>
        </tr>
    </table>

    <!-- RISK LEVEL -->
    <div class="risk-container">
        <table class="risk-table">
            <tr>
                <td style="width: 70%;">
                    <span class="risk-label">Target Asset:</span> 
                    <span class="target-name"><?= $target ?></span>
                </td>
                <td style="width: 30%; text-align: right;">
                    <span class="risk-label">Risk Level:</span> 
                    <span class="risk-score"><?= $level[0] ?></span>
                </td>
            </tr>
        </table>
    </div>

    <!-- SUMMARY -->
    <div class="section-title">1. Executive Summary</div>
    <div class="content">
        <?= $summaryText ?>
    </div>

    <!-- PORTS TABLE -->
    <div class="section-title">2. Exposed Services</div>
    <div class="content">
        <?php if (!empty($ports)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Port / Protocol</th>
                        <th style="width: 75%;">Service / Banner</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ports as $p): ?>
                    <tr>
                        <td style="font-family: monospace; font-weight: bold;"><?= $p['port'] ?></td>
                        <td><?= $p['service'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No open ports detected in the provided logs.</p>
        <?php endif; ?>
    </div>

    <!-- AI ANALYSIS -->
    <?php if (!empty($ai)) { ?>
    <div class="section-title">3. AI Threat Analysis</div>
    <div class="content" style="background: #fffbeb; border-left: 4px solid #fbbf24; padding: 10px;">
        <?= safe($ai) ?>
    </div>
    <?php } ?>

    <!-- LOGS -->
    <?php if (!empty($logs)) { ?>
    <div class="section-title">4. Technical Evidence</div>
    <div class="terminal-window">
        <div class="terminal-header">ROOT@SOC-SCANNER:~# nmap -sV <?= $target ?></div>
<?= htmlspecialchars($logs) ?>
    </div>
    <?php } ?>

    <!-- FOOTER -->
    <div class="footer">
        SOC Operations Center &bull; Page {PAGE_NUM} of {PAGE_COUNT}
    </div>

</body>
</html>

<?php

$html = ob_get_clean();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("SOC_Report_{$target}.pdf", ["Attachment" => true]);
