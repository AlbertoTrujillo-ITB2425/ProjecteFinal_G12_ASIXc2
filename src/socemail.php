<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  socmail.php — G7 SOC Mail & Snort Monitor                  ║
// ║  Lee correos reales de /var/mail/root y alertas de Snort    ║
// ╚══════════════════════════════════════════════════════════════╝
session_start();

// ── PROTECCIÓN: solo usuarios logueados ───────────────────────
if (!isset($_SESSION['userid'])) {
    header('Location: auth.php');
    exit;
}

// ══════════════════════════════════════════════════════════════
// SECCIÓN 1: LEER CORREOS REALES DE POSTFIX
// Postfix guarda los correos en formato mbox en /var/mail/root
// ══════════════════════════════════════════════════════════════
function parseMboxFile(string $path): array {
    if (!file_exists($path) || !is_readable($path)) {
        return [];
    }

    $content = file_get_contents($path);
    // Separar mensajes: cada uno empieza con "From " (sin :)
    $rawMessages = preg_split('/^From /m', $content);
    $messages = [];
    $id = 1;

    foreach ($rawMessages as $raw) {
        $raw = trim($raw);
        if (empty($raw)) continue;

        // Separar cabeceras del cuerpo
        $parts = preg_split('/\r?\n\r?\n/', $raw, 2);
        $headerBlock = $parts[0] ?? '';
        $body        = $parts[1] ?? '(sin contenido)';

        // Parsear cabeceras
        $from    = extractHeader($headerBlock, 'From');
        $subject = extractHeader($headerBlock, 'Subject');
        $date    = extractHeader($headerBlock, 'Date');
        $to      = extractHeader($headerBlock, 'To');

        // Decodificar MIME encoded-words (ej: =?UTF-8?Q?...?=)
        $subject = decodeMimeStr($subject);
        $from    = decodeMimeStr($from);

        // Limpiar cuerpo
        $body = cleanBody($body);

        // Clasificar tipo por asunto
        $tag = classifyTag($subject . ' ' . $from);

        $messages[] = [
            'id'      => $id++,
            'from'    => $from ?: 'desconocido@local',
            'to'      => $to,
            'subject' => $subject ?: '(sin asunto)',
            'date'    => formatMailDate($date),
            'tag'     => $tag['class'],
            'tagLabel'=> $tag['label'],
            'icon'    => $tag['icon'],
            'body'    => nl2br(htmlspecialchars($body, ENT_QUOTES, 'UTF-8')),
        ];
    }

    // Los más recientes primero
    return array_reverse($messages);
}

function extractHeader(string $headers, string $name): string {
    if (preg_match('/^' . preg_quote($name, '/') . ':\s*(.+?)(?:\r?\n(?!\s)|$)/ims', $headers, $m)) {
        return trim(preg_replace('/\r?\n\s+/', ' ', $m[1]));
    }
    return '';
}

function decodeMimeStr(string $str): string {
    if (function_exists('iconv_mime_decode')) {
        return iconv_mime_decode($str, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
    }
    // Fallback manual para =?UTF-8?Q?...?=
    $str = preg_replace_callback('/=\?([^?]+)\?([QB])\?([^?]*)\?=/i', function($m) {
        $charset  = $m[1];
        $encoding = strtoupper($m[2]);
        $encoded  = $m[3];
        $decoded  = $encoding === 'B' ? base64_decode($encoded) : quoted_printable_decode(str_replace('_', ' ', $encoded));
        return mb_convert_encoding($decoded, 'UTF-8', $charset);
    }, $str);
    return $str;
}

function cleanBody(string $body): string {
    // Quitar partes MIME y boundaries
    $body = preg_replace('/--[a-zA-Z0-9_\-]+(\r?\n|$).*?--[a-zA-Z0-9_\-]+--/s', '', $body);
    // Quitar cabeceras de parte (Content-Type: etc dentro del body)
    $body = preg_replace('/^Content-[^\r\n]+(\r?\n)+/m', '', $body);
    // Decodificar quoted-printable
    $body = quoted_printable_decode($body);
    return trim(substr($body, 0, 4000)); // máximo 4000 chars
}

function formatMailDate(string $date): string {
    if (!$date) return 'Sin fecha';
    try {
        $ts = strtotime($date);
        if (!$ts) return $date;
        $now   = time();
        $diff  = $now - $ts;
        if ($diff < 86400) return date('H:i', $ts);
        if ($diff < 604800) return date('D d/m', $ts);
        return date('d/m/Y', $ts);
    } catch (Exception $e) { return $date; }
}

function classifyTag(string $text): array {
    $text = strtolower($text);
    if (str_contains($text, 'alert') || str_contains($text, 'alerta') || str_contains($text, 'intrusion') || str_contains($text, 'attack'))
        return ['class'=>'badge-danger', 'label'=>'ALERTA', 'icon'=>'fa-triangle-exclamation'];
    if (str_contains($text, 'warn') || str_contains($text, 'warning'))
        return ['class'=>'badge-amber',  'label'=>'WARN',   'icon'=>'fa-circle-exclamation'];
    if (str_contains($text, 'snort') || str_contains($text, 'ids') || str_contains($text, 'scan'))
        return ['class'=>'badge-sky',    'label'=>'IDS',    'icon'=>'fa-shield-virus'];
    if (str_contains($text, 'wazuh') || str_contains($text, 'siem'))
        return ['class'=>'badge-amber',  'label'=>'SIEM',   'icon'=>'fa-magnifying-glass'];
    return ['class'=>'badge-green', 'label'=>'INFO', 'icon'=>'fa-envelope'];
}

// ══════════════════════════════════════════════════════════════
// SECCIÓN 2: LEER LOGS REALES DE SNORT
// Snort escribe en /var/log/snort/alert (modo console/fast)
// ══════════════════════════════════════════════════════════════
function parseSnortAlerts(string $path, int $limit = 200): array {
    if (!file_exists($path) || !is_readable($path)) {
        return [];
    }

    // Leer las últimas $limit líneas eficientemente
    $lines = readLastLines($path, $limit * 5);
    $logs  = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Formato "fast alert" de Snort:
        // 05/04-14:32:01.123456  [**] [1:1000001:1] SQL Injection [**] [Classification: ...] [Priority: 1] {TCP} 192.168.1.1:123 -> 10.0.0.1:80
        if (!preg_match('/^(\d{2}\/\d{2}-\d{2}:\d{2}:\d{2})/', $line)) continue;

        $ts  = extractSnortTimestamp($line);
        $msg = extractSnortMsg($line);
        $cls = extractSnortClassification($line);
        $pri = extractSnortPriority($line);
        $src = extractSnortSrc($line);
        $dst = extractSnortDst($line);
        $proto = extractSnortProto($line);
        $sev = priorityToSev($pri);

        $logs[] = [
            'ts'    => $ts,
            'sev'   => $sev,
            'cls'   => $cls ?: 'Unknown',
            'src'   => $src,
            'dst'   => $dst,
            'proto' => $proto,
            'msg'   => $msg,
        ];
    }

    return array_reverse(array_slice(array_reverse($logs), 0, $limit));
}

function readLastLines(string $file, int $n): array {
    $fp = fopen($file, 'r');
    if (!$fp) return [];
    fseek($fp, 0, SEEK_END);
    $size = ftell($fp);
    $chunk = min($size, $n * 200);
    fseek($fp, -$chunk, SEEK_END);
    $data = fread($fp, $chunk);
    fclose($fp);
    $lines = explode("\n", $data);
    return array_slice($lines, -$n);
}

function extractSnortTimestamp(string $line): string {
    if (preg_match('/^(\d{2}\/\d{2}-\d{2}:\d{2}:\d{2})/', $line, $m)) {
        $year = date('Y');
        return $year . '-' . str_replace(['/', '-'], ['-', ' '], $m[1]);
    }
    return '';
}

function extractSnortMsg(string $line): string {
    if (preg_match('/\[\*\*\]\s+\[\d+:\d+:\d+\]\s+(.+?)\s+\[\*\*\]/', $line, $m)) return trim($m[1]);
    if (preg_match('/\[\*\*\]\s+(.+?)\s+\[\*\*\]/', $line, $m)) return trim($m[1]);
    return 'Alerta Snort';
}

function extractSnortClassification(string $line): string {
    if (preg_match('/\[Classification:\s*([^\]]+)\]/', $line, $m)) return trim($m[1]);
    return '';
}

function extractSnortPriority(string $line): int {
    if (preg_match('/\[Priority:\s*(\d+)\]/', $line, $m)) return (int)$m[1];
    return 3;
}

function extractSnortProto(string $line): string {
    if (preg_match('/\{(TCP|UDP|ICMP|GRE|IP)\}/', $line, $m)) return $m[1];
    return 'TCP';
}

function extractSnortSrc(string $line): string {
    if (preg_match('/\}\s+([\d\.]+(?::\d+)?)\s*->/', $line, $m)) return trim($m[1]);
    return '';
}

function extractSnortDst(string $line): string {
    if (preg_match('/\->\s+([\d\.]+(?::\d+)?)/', $line, $m)) return trim($m[1]);
    return '';
}

function priorityToSev(int $pri): string {
    return match($pri) {
        1 => 'HIGH',
        2 => 'MEDIUM',
        3 => 'LOW',
        default => 'INFO',
    };
}

// ══════════════════════════════════════════════════════════════
// SECCIÓN 3: CARGAR DATOS REALES
// Ajusta las rutas según tu docker-compose (volúmenes montados)
// ══════════════════════════════════════════════════════════════

// Ruta al buzón de correo de Postfix (volumen ./mail_logs montado en s10)
// En docker-compose: volumes: - ./mail_logs:/var/log/mail
// Postfix también guarda buzón en /var/mail/root dentro del contenedor
// Si tienes acceso desde php en el mismo host, usa la ruta del volumen:
$MAIL_MBOX_PATH   = getenv('MAIL_MBOX_PATH')  ?: '/var/mail/root';
$SNORT_ALERT_PATH = getenv('SNORT_ALERT_PATH') ?: '/var/log/snort/alert';

$mails     = parseMboxFile($MAIL_MBOX_PATH);
$snortLogs = parseSnortAlerts($SNORT_ALERT_PATH, 100);

$unreadCount = count($mails); // todos son "nuevos" — puedes guardar estado en session
$alertCount  = count(array_filter($snortLogs, fn($l) => in_array($l['sev'], ['HIGH', 'MEDIUM'])));

// Para el visor de correo individual vía AJAX
if (isset($_GET['mail_id'])) {
    $id   = (int)$_GET['mail_id'];
    $mail = array_values(array_filter($mails, fn($m) => $m['id'] === $id))[0] ?? null;
    header('Content-Type: application/json');
    echo json_encode($mail);
    exit;
}

// Para los logs de Snort vía AJAX (con filtro)
if (isset($_GET['snort'])) {
    $sev = $_GET['sev'] ?? 'all';
    $filtered = $sev === 'all' ? $snortLogs
        : array_values(array_filter($snortLogs, fn($l) => $l['sev'] === $sev));
    header('Content-Type: application/json');
    echo json_encode($filtered);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SOC Mail &amp; Snort — G7</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
:root {
    --accent-primary: #0ea5e9; --accent-secondary: #10b981;
    --bg-main: #020617; --bg-nav: rgba(15,23,42,0.8);
    --text-main: #f8fafc; --text-muted: #94a3b8;
    --glass-bg: rgba(255,255,255,0.02); --glass-border: rgba(255,255,255,0.05);
    --grid-color: rgba(255,255,255,0.05); --dropdown-bg: #0f172a;
}
.light-mode {
    --bg-main: #f8fafc; --bg-nav: rgba(255,255,255,0.8);
    --text-main: #0f172a; --text-muted: #475569;
    --glass-bg: rgba(0,0,0,0.02); --glass-border: rgba(0,0,0,0.1);
    --grid-color: rgba(0,0,0,0.05); --dropdown-bg: #ffffff;
}
body { top:0!important; position:static!important; font-family:'Plus Jakarta Sans',sans-serif;
    background-color:var(--bg-main)!important; color:var(--text-main)!important;
    transition:background-color .4s ease,color .4s ease; overflow-x:hidden; }
h1,h2,h3,h4,h5,h6,p,span{transition:color .4s ease}
.glass-panel { background:var(--glass-bg); backdrop-filter:blur(16px);
    -webkit-backdrop-filter:blur(16px); border:1px solid var(--glass-border);
    border-radius:1.5rem; transition:all .4s cubic-bezier(.4,0,.2,1); position:relative; overflow:hidden; }
.light-mode .glass-panel{box-shadow:0 10px 30px -10px rgba(0,0,0,.1)}
.grid-overlay { position:fixed;inset:0;z-index:-1;
    background-image:linear-gradient(to right,var(--grid-color) 1px,transparent 1px),
                     linear-gradient(to bottom,var(--grid-color) 1px,transparent 1px);
    background-size:40px 40px; pointer-events:none;
    mask-image:linear-gradient(to bottom,black 40%,transparent 100%);
    -webkit-mask-image:linear-gradient(to bottom,black 40%,transparent 100%);}
.bg-glow{position:fixed;top:-10%;left:-10%;width:50vw;height:50vw;
    background:radial-gradient(circle,rgba(14,165,233,.05) 0%,rgba(0,0,0,0) 70%);z-index:-1;pointer-events:none;}
.mono-tech{font-family:'JetBrains Mono',monospace;}
nav{position:sticky;top:0;z-index:100;background:var(--bg-nav);
    border-bottom:1px solid var(--glass-border);backdrop-filter:blur(20px);}
.tab-btn{display:flex;align-items:center;gap:8px;padding:10px 20px;font-size:13px;
    font-weight:600;border:none;background:none;cursor:pointer;color:var(--text-muted);
    position:relative;transition:color .2s ease;letter-spacing:.02em;}
.tab-btn:hover{color:var(--text-main);}
.tab-btn.active{color:var(--accent-primary);}
.tab-btn.active::after{content:'';position:absolute;bottom:-1px;left:0;right:0;
    height:2px;background:var(--accent-primary);border-radius:2px 2px 0 0;}
.tab-bar{border-bottom:1px solid var(--glass-border);padding:0 24px;display:flex;gap:4px;}
.badge{font-size:10px;font-weight:700;padding:1px 7px;border-radius:9999px;
    font-family:'JetBrains Mono',monospace;letter-spacing:.04em;}
.badge-danger{background:rgba(239,68,68,.15);color:#ef4444;}
.badge-sky{background:rgba(14,165,233,.12);color:#0ea5e9;}
.badge-amber{background:rgba(245,158,11,.15);color:#f59e0b;}
.badge-green{background:rgba(16,185,129,.15);color:#10b981;}
.mail-layout{display:flex;height:calc(100dvh - 116px);overflow:hidden;}
.mail-sidebar{width:300px;flex-shrink:0;border-right:1px solid var(--glass-border);
    display:flex;flex-direction:column;overflow:hidden;}
.sidebar-head{padding:14px 18px;border-bottom:1px solid var(--glass-border);
    font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;
    color:var(--text-muted);display:flex;align-items:center;gap:8px;flex-shrink:0;}
.mail-list{flex:1;overflow-y:auto;}
.mail-item{padding:14px 18px;cursor:pointer;border-bottom:1px solid var(--glass-border);
    display:flex;flex-direction:column;gap:5px;transition:background .15s ease;position:relative;}
.mail-item:hover{background:rgba(14,165,233,.04);}
.mail-item.active{background:rgba(14,165,233,.07);border-left:2px solid var(--accent-primary);}
.mail-from{font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mail-subject{font-size:11px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mail-meta-row{display:flex;justify-content:space-between;align-items:center;}
.mail-time{font-size:10px;color:var(--text-muted);font-family:'JetBrains Mono',monospace;}
.mail-viewer{flex:1;overflow-y:auto;padding:32px 36px;display:flex;flex-direction:column;gap:24px;}
.viewer-empty{flex:1;display:flex;flex-direction:column;align-items:center;
    justify-content:center;gap:14px;color:var(--text-muted);text-align:center;}
.viewer-empty i{font-size:48px;opacity:.2;}
.email-subject-line{font-size:20px;font-weight:800;line-height:1.3;margin-bottom:14px;}
.email-divider{border:none;border-top:1px solid var(--glass-border);margin:12px 0;}
.email-meta{display:flex;flex-wrap:wrap;gap:12px;font-size:12px;color:var(--text-muted);margin-bottom:8px;}
.email-meta strong{color:var(--text-main);}
.email-body{font-size:13px;line-height:1.8;max-width:72ch;}
.email-body pre,.email-body code{font-family:'JetBrains Mono',monospace;font-size:11px;
    background:rgba(255,255,255,.03);border:1px solid var(--glass-border);
    border-radius:1rem;padding:16px 18px;overflow-x:auto;display:block;
    color:var(--text-muted);line-height:1.6;margin:14px 0;white-space:pre-wrap;word-break:break-all;}
.light-mode .email-body pre,.light-mode .email-body code{background:rgba(0,0,0,.04);}
.snort-layout{display:flex;flex-direction:column;height:calc(100dvh - 116px);overflow:hidden;}
.snort-toolbar{padding:12px 24px;border-bottom:1px solid var(--glass-border);
    display:flex;gap:8px;align-items:center;flex-shrink:0;flex-wrap:wrap;}
.filter-btn{padding:5px 14px;border-radius:9999px;font-size:11px;font-weight:600;
    border:1px solid var(--glass-border);background:none;cursor:pointer;color:var(--text-muted);
    transition:all .15s ease;font-family:'Plus Jakarta Sans',sans-serif;letter-spacing:.03em;}
.filter-btn:hover,.filter-btn.active{color:#fff;}
.filter-btn.f-all.active{background:var(--accent-primary);border-color:var(--accent-primary);}
.filter-btn.f-high.active{background:#ef4444;border-color:#ef4444;}
.filter-btn.f-med.active{background:#f59e0b;border-color:#f59e0b;}
.filter-btn.f-low.active{background:#10b981;border-color:#10b981;}
.filter-btn.f-info.active{background:var(--accent-primary);border-color:var(--accent-primary);}
.snort-body{flex:1;overflow-y:auto;}
.log-table{width:100%;border-collapse:collapse;}
.log-table thead th{position:sticky;top:0;z-index:10;background:var(--bg-main);
    padding:10px 16px;font-size:10px;font-weight:700;text-transform:uppercase;
    letter-spacing:.08em;color:var(--text-muted);text-align:left;
    border-bottom:1px solid var(--glass-border);white-space:nowrap;
    font-family:'JetBrains Mono',monospace;}
.log-table tbody tr{border-bottom:1px solid var(--glass-border);transition:background .1s ease;}
.log-table tbody tr:hover{background:rgba(14,165,233,.04);}
.log-table td{padding:10px 16px;font-size:11px;font-family:'JetBrains Mono',monospace;
    color:var(--text-muted);vertical-align:middle;}
.sev-badge{display:inline-flex;align-items:center;padding:2px 9px;border-radius:9999px;
    font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;white-space:nowrap;}
.sev-HIGH{background:rgba(239,68,68,.15);color:#ef4444;}
.sev-MEDIUM{background:rgba(245,158,11,.15);color:#f59e0b;}
.sev-LOW{background:rgba(16,185,129,.15);color:#10b981;}
.sev-INFO{background:rgba(14,165,233,.12);color:#0ea5e9;}
.icon-btn{width:38px;height:38px;border-radius:.75rem;border:1px solid var(--glass-border);
    background:var(--glass-bg);color:var(--text-muted);cursor:pointer;display:flex;
    align-items:center;justify-content:center;transition:all .2s ease;font-size:14px;}
.icon-btn:hover{border-color:var(--accent-primary);color:var(--accent-primary);}
.error-box{padding:20px 24px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);
    border-radius:1rem;color:#ef4444;font-size:12px;font-family:'JetBrains Mono',monospace;margin-bottom:12px;}
::-webkit-scrollbar{width:4px;height:4px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--glass-border);border-radius:9999px;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
</style>
</head>
<body>

<div class="grid-overlay"></div>
<div class="bg-glow"></div>

<!-- NAV -->
<nav>
  <div style="max-width:1400px;margin:0 auto;padding:0 24px;height:60px;display:flex;align-items:center;gap:14px;">
    <a href="index.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
      <div style="width:40px;height:40px;border-radius:.75rem;background:rgba(14,165,233,.1);border:1px solid rgba(14,165,233,.2);display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-shield-halved" style="color:var(--accent-primary);"></i>
      </div>
      <span style="font-weight:800;font-size:16px;letter-spacing:-.3px;">
        CYBER<span style="color:var(--accent-primary);">PYME</span>
        <span style="font-size:11px;font-weight:500;color:var(--text-muted);margin-left:6px;" class="mono-tech">SOC G7</span>
      </span>
    </a>
    <div style="height:24px;width:1px;background:var(--glass-border);"></div>
    <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);" class="mono-tech">
      <i class="fas fa-envelope" style="color:var(--accent-primary);margin-right:6px;"></i>
      Mail &amp; IDS Monitor
    </span>
    <div style="flex:1;"></div>
    <span class="badge badge-green mono-tech" style="display:flex;align-items:center;gap:5px;">
      <span style="width:6px;height:6px;border-radius:50%;background:#10b981;display:inline-block;animation:pulse 2s infinite;"></span>
      <?= htmlspecialchars($_SESSION['username'] ?? 'SOC User') ?>
    </span>
    <button class="icon-btn" id="theme-btn" onclick="toggleTheme()" title="Tema">
      <i class="fas fa-sun" id="theme-icon"></i>
    </button>
    <button class="icon-btn" onclick="location.reload()" title="Actualizar datos reales">
      <i class="fas fa-rotate-right"></i>
    </button>
    <a href="auth.php?logout=1" class="icon-btn" title="Cerrar sesión">
      <i class="fas fa-right-from-bracket"></i>
    </a>
  </div>
</nav>

<!-- TABS -->
<div class="tab-bar">
  <button class="tab-btn active" id="tab-mail" onclick="switchTab('mail')">
    <i class="fas fa-inbox"></i>
    Bandeja de entrada
    <span class="badge badge-danger" id="unread-count"><?= $unreadCount ?></span>
  </button>
  <button class="tab-btn" id="tab-snort" onclick="switchTab('snort')">
    <i class="fas fa-shield-virus"></i>
    Logs Snort / IDS
    <span class="badge badge-amber" id="alert-count"><?= $alertCount ?></span>
  </button>
</div>

<!-- MAIL PANEL -->
<div id="panel-mail" class="mail-layout">
  <aside class="mail-sidebar">
    <div class="sidebar-head">
      <i class="fas fa-inbox" style="color:var(--accent-primary);"></i>
      Bandeja — <?= $unreadCount ?> mensajes
    </div>
    <div class="mail-list" id="mail-list">
      <?php if (empty($mails)): ?>
        <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:12px;">
          <?php
            $MAIL_MBOX_PATH_HTML = htmlspecialchars($MAIL_MBOX_PATH);
            echo "<div class='error-box'><i class='fas fa-circle-info' style='margin-right:6px;'></i>No se encontraron correos en:<br><code>$MAIL_MBOX_PATH_HTML</code><br><br>Revisa el apartado <strong>Pasos para activarlo</strong> en la documentación.</div>";
          ?>
        </div>
      <?php else: ?>
        <?php foreach ($mails as $m): ?>
        <div class="mail-item" id="mail-<?= $m['id'] ?>" onclick="openMail(<?= $m['id'] ?>)">
          <div class="mail-meta-row">
            <span class="mail-from"><?= htmlspecialchars($m['from']) ?></span>
            <span class="mail-time"><?= htmlspecialchars($m['date']) ?></span>
          </div>
          <span class="mail-subject"><?= htmlspecialchars($m['subject']) ?></span>
          <span class="badge <?= $m['tag'] ?>" style="align-self:flex-start;margin-top:2px;">
            <i class="fas <?= $m['icon'] ?>" style="font-size:9px;margin-right:4px;"></i><?= $m['tagLabel'] ?>
          </span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </aside>

  <div class="mail-viewer" id="mail-viewer">
    <div class="viewer-empty" id="email-empty">
      <i class="fas fa-envelope-open-text"></i>
      <p style="font-size:15px;font-weight:600;">Selecciona un correo</p>
      <p style="font-size:13px;color:var(--text-muted);">Los mensajes de Postfix, Wazuh y Snort aparecerán aquí</p>
    </div>
    <div id="email-content" style="display:none;"></div>
  </div>
</div>

<!-- SNORT PANEL -->
<div id="panel-snort" class="snort-layout" style="display:none;">
  <div class="snort-toolbar">
    <span class="mono-tech" style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-right:4px;">Severidad:</span>
    <button class="filter-btn f-all active" onclick="filterSnort('all',this)">Todos</button>
    <button class="filter-btn f-high" onclick="filterSnort('HIGH',this)"><i class="fas fa-circle-xmark" style="font-size:9px;"></i> Alta</button>
    <button class="filter-btn f-med" onclick="filterSnort('MEDIUM',this)"><i class="fas fa-triangle-exclamation" style="font-size:9px;"></i> Media</button>
    <button class="filter-btn f-low" onclick="filterSnort('LOW',this)"><i class="fas fa-circle-check" style="font-size:9px;"></i> Baja</button>
    <button class="filter-btn f-info" onclick="filterSnort('INFO',this)"><i class="fas fa-info" style="font-size:9px;"></i> Info</button>
    <div style="flex:1;"></div>
    <span class="mono-tech" style="font-size:11px;color:var(--text-muted);" id="log-count"><?= count($snortLogs) ?> entradas</span>
    <span class="mono-tech" style="font-size:10px;color:var(--text-muted);">
      <i class="fas fa-circle" style="color:#10b981;font-size:8px;"></i> <?= htmlspecialchars($SNORT_ALERT_PATH) ?>
    </span>
  </div>

  <div class="snort-body">
    <?php if (empty($snortLogs)): ?>
      <div style="padding:32px;text-align:center;color:var(--text-muted);">
        <?php
          $SNORT_ALERT_PATH_HTML = htmlspecialchars($SNORT_ALERT_PATH);
          echo "<div class='error-box'><i class='fas fa-circle-info' style='margin-right:6px;'></i>No se encontraron alertas en:<br><code>$SNORT_ALERT_PATH_HTML</code><br><br>Verifica que Snort esté corriendo con <code>-A fast</code> o <code>-A console</code>.</div>";
        ?>
      </div>
    <?php else: ?>
    <table class="log-table">
      <thead>
        <tr>
          <th>Timestamp</th><th>Severidad</th><th>Clasificación</th>
          <th>IP Origen</th><th>IP Destino</th><th>Proto</th><th>Mensaje</th>
        </tr>
      </thead>
      <tbody id="snort-tbody">
        <?php foreach ($snortLogs as $l): ?>
        <tr data-sev="<?= $l['sev'] ?>">
          <td><?= htmlspecialchars($l['ts']) ?></td>
          <td><span class="sev-badge sev-<?= $l['sev'] ?>"><?= $l['sev'] ?></span></td>
          <td style="color:var(--text-main);"><?= htmlspecialchars($l['cls']) ?></td>
          <td><?= htmlspecialchars($l['src']) ?></td>
          <td><?= htmlspecialchars($l['dst']) ?></td>
          <td style="color:var(--accent-primary);"><?= $l['proto'] ?></td>
          <td style="color:var(--text-main);"><?= htmlspecialchars($l['msg']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
// ── DATOS PHP → JS ────────────────────────────────
const MAILS_PHP = <?= json_encode(array_column($mails, null, 'id'), JSON_UNESCAPED_UNICODE) ?>;

// ── THEME ─────────────────────────────────────────
function toggleTheme(){
  const isLight = document.body.classList.toggle('light-mode');
  document.getElementById('theme-icon').className = isLight ? 'fas fa-moon' : 'fas fa-sun';
}

// ── TABS ──────────────────────────────────────────
function switchTab(tab){
  document.getElementById('panel-mail').style.display  = tab==='mail'  ? 'flex' : 'none';
  document.getElementById('panel-snort').style.display = tab==='snort' ? 'flex' : 'none';
  document.getElementById('tab-mail').className  = 'tab-btn' + (tab==='mail'  ? ' active' : '');
  document.getElementById('tab-snort').className = 'tab-btn' + (tab==='snort' ? ' active' : '');
}

// ── ABRIR CORREO ──────────────────────────────────
function openMail(id){
  document.querySelectorAll('.mail-item').forEach(el=>el.classList.remove('active'));
  const el = document.getElementById('mail-'+id);
  if(el) el.classList.add('active');

  const mail = MAILS_PHP[id];
  if(!mail) return;

  document.getElementById('email-empty').style.display='none';
  const ec = document.getElementById('email-content');
  ec.style.display='block';
  ec.innerHTML = `
    <div>
      <div class="email-subject-line">${esc(mail.subject)}</div>
      <div class="email-meta">
        <span><strong>De:</strong> ${esc(mail.from)}</span>
        <span><strong>Para:</strong> ${esc(mail.to||'root@localhost')}</span>
        <span><strong>Hora:</strong> ${esc(mail.date)}</span>
        <span class="badge ${mail.tag}">
          <i class="fas ${mail.icon}" style="font-size:9px;margin-right:4px;"></i>${mail.tagLabel}
        </span>
      </div>
      <hr class="email-divider">
      <div class="email-body"><pre>${mail.body}</pre></div>
    </div>`;
}

// ── FILTRO SNORT ──────────────────────────────────
function filterSnort(sev, btn){
  document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
  if(btn) btn.classList.add('active');
  const rows = document.querySelectorAll('#snort-tbody tr');
  let count = 0;
  rows.forEach(row=>{
    const show = sev==='all' || row.dataset.sev===sev;
    row.style.display = show ? '' : 'none';
    if(show) count++;
  });
  document.getElementById('log-count').textContent = count + ' entradas';
}

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
</script>
</body>
</html>
