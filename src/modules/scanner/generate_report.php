<?php

require __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   DEBUG (QUITAR EN PROD)
========================= */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================
   INPUT VALIDATION
========================= */

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
   PARSE PORTS
========================= */

preg_match_all('/(\d+)\/(tcp|udp)\s+open\s+([^\s]+)/i', $logs, $m, PREG_SET_ORDER);

$ports = [];
foreach ($m as $x) {
    $ports[] = "{$x[1]}/{$x[2]} {$x[3]}";
}

/* =========================
   RISK ENGINE
========================= */

$risk = count($ports) * 8;

if (stripos($logs, 'telnet') !== false) $risk += 40;
if (stripos($logs, 'ftp') !== false) $risk += 25;

if ($risk > 80) {
    $level = ['CRITICAL', 'CRITICAL'];
} elseif ($risk > 50) {
    $level = ['HIGH', 'HIGH'];
} elseif ($risk > 20) {
    $level = ['MEDIUM', 'MEDIUM'];
} else {
    $level = ['LOW', 'LOW'];
}

/* =========================
   SAFE TEXT
========================= */

function safe($text) {
    return nl2br(htmlspecialchars($text));
}

/* =========================
   PORT TAGS
========================= */

$portsHtml = '';
foreach ($ports as $p) {
    $portsHtml .= "<span class='tag'>{$p}</span>";
}

/* =========================
   EXEC SUMMARY
========================= */

$summary = "
The target {$target} exposes externally reachable services that increase the attack surface.
Based on detected open ports and service exposure, the system is classified as {$level[0]} risk.

Immediate remediation is recommended for unnecessary exposed services and insecure communication channels.
";

/* =========================
   HTML BUFFER
========================= */

ob_start();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>

/* =========================
   BASE
========================= */

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    margin: 0;
    background: #ffffff;
    color: #111827;
}

.page {
    padding: 18mm;
}

/* =========================
   HEADER (PALO ALTO STYLE SAFE)
========================= */

.header {
    background: #0b1220;
    color: #ffffff;
    padding: 18px;
    border-radius: 6px;
    margin-bottom: 16px;
    border-bottom: 3px solid #dc2626;
}

.title {
    font-size: 20pt;
    font-weight: 800;
}

.meta {
    font-size: 9pt;
    opacity: 0.9;
    margin-top: 4px;
}

/* =========================
   RISK BADGE
========================= */

.risk {
    display: inline-block;
    margin-top: 10px;
    padding: 6px 12px;
    font-weight: bold;
    font-size: 10pt;
    border-radius: 4px;
    color: #ffffff;
}

/* =========================
   SECTIONS
========================= */

.block {
    margin-top: 12px;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #ffffff;
}

.h {
    font-size: 10pt;
    font-weight: 700;
    color: #1e3a8a;
    margin-bottom: 8px;
    text-transform: uppercase;
}

/* =========================
   SUMMARY
========================= */

.summary {
    background: #f3f4f6;
    padding: 10px;
    border-radius: 6px;
    font-size: 10pt;
    line-height: 1.5;
}

/* =========================
   TAGS
========================= */

.tag {
    display: inline-block;
    background: #e5f0ff;
    color: #1d4ed8;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 9pt;
    margin: 2px;
}

/* =========================
   LOGS (SIEM STYLE SAFE)
========================= */

.logs {
    background: #0f172a;
    color: #22c55e;
    padding: 12px;
    border-radius: 6px;
    font-size: 8.5pt;
    font-family: "Courier New", monospace;
    white-space: pre-wrap;
}

/* =========================
   FOOTER
========================= */

.footer {
    margin-top: 16px;
    padding-top: 10px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
    font-size: 8pt;
    color: #6b7280;
}

/* =========================
   RISK COLORS
========================= */

.CRITICAL { background:#dc2626; }
.HIGH { background:#ef4444; }
.MEDIUM { background:#f59e0b; }
.LOW { background:#10b981; }

</style>

</head>

<body>

<div class="page">

    <!-- HEADER -->
    <div class="header">
        <div class="title">SECURITY POSTURE REPORT</div>

        <div class="meta">
            Target: <b><?= $target ?></b> · Analyst: <b><?= $auditor ?></b>
        </div>

        <div class="meta">
            Generated: <?= $date ?> · Report ID: <?= $reportId ?>
        </div>

        <div class="risk <?= $level[0] ?>">
            <?= $level[0] ?> RISK
        </div>
    </div>

    <!-- EXEC SUMMARY -->
    <div class="block">
        <div class="h">EXECUTIVE SUMMARY</div>
        <div class="summary"><?= $summary ?></div>
    </div>

    <!-- OPEN SERVICES -->
    <div class="block">
        <div class="h">EXPOSED SERVICES</div>
        <div><?= $portsHtml ?></div>
    </div>

    <!-- AI ANALYSIS -->
    <?php if (!empty($ai)) { ?>
    <div class="block">
        <div class="h">THREAT ANALYSIS</div>
        <div><?= safe($ai) ?></div>
    </div>
    <?php } ?>

    <!-- LOGS -->
    <?php if (!empty($logs)) { ?>
    <div class="block">
        <div class="h">TECHNICAL EVIDENCE (SIEM LOGS)</div>
        <div class="logs"><?= htmlspecialchars($logs) ?></div>
    </div>
    <?php } ?>

    <!-- FOOTER -->
    <div class="footer">
        SOC Operations Center · Automated Security Intelligence Report
    </div>

</div>

</body>
</html>

<?php

$html = ob_get_clean();

/* =========================
   DOMPDF
========================= */

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("SOC_Report_{$target}.pdf", ["Attachment" => true]);
