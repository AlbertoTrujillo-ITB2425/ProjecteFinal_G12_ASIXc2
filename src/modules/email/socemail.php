<?php
/**
 * CYBERPYME SOC - Mail & Snort Monitor
 * Ubicación: modules/email/socemail.php
 */
session_start();

// BLOQUEO DE SEGURIDAD
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth.php");
    exit;
}

$MAIL_MBOX_PATH   = getenv('MAIL_MBOX_PATH') ?: '/var/mail/root';
$SNORT_ALERT_PATH = getenv('SNORT_ALERT_PATH') ?: '/var/log/snort/alert';

// --- FUNCIONES CORE ---
function safeReadFile(string $path): string {
    if (!file_exists($path) || !is_readable($path)) return '';
    $data = file_get_contents($path);
    return is_string($data) ? $data : '';
}

function extractHeader(string $headers, string $name): string {
    if (preg_match('/^' . preg_quote($name, '/') . ':\s*(.+?)(?:\r?\n(?!\s)|$)/ims', $headers, $m)) {
        return trim(preg_replace('/\r?\n\s+/', ' ', $m[1]));
    }
    return '';
}

function decodeMimeStr(string $str): string {
    if ($str === '') return '';
    if (function_exists('iconv_mime_decode')) {
        $decoded = iconv_mime_decode($str, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        if ($decoded !== false) return $decoded;
    }
    return preg_replace_callback('/=\?([^?]+)\?([QB])\?([^?]*)\?=/i', function($m) {
        $charset = $m[1]; $encoding = strtoupper($m[2]); $encoded = $m[3];
        $decoded = $encoding === 'B' ? base64_decode($encoded) : quoted_printable_decode(str_replace('_', ' ', $encoded));
        return mb_convert_encoding($decoded, 'UTF-8', $charset);
    }, $str);
}

function formatMailDate(string $date): string {
    if ($date === '') return 'Sin fecha';
    $ts = strtotime($date);
    if (!$ts) return $date;
    $diff = time() - $ts;
    if ($diff < 86400) return date('H:i', $ts);
    if ($diff < 604800) return date('D d/m', $ts);
    return date('d/m/Y', $ts);
}

function classifyTag(string $text): array {
    $text = strtolower($text);
    if (str_contains($text, 'alert') || str_contains($text, 'alerta') || str_contains($text, 'intrusion') || str_contains($text, 'attack'))
        return ['class' => 'bg-red-500/10 text-red-400 border-red-500/30', 'label' => 'ALERTA'];
    if (str_contains($text, 'warn') || str_contains($text, 'warning'))
        return ['class' => 'bg-amber-500/10 text-amber-400 border-amber-500/30', 'label' => 'WARN'];
    if (str_contains($text, 'snort') || str_contains($text, 'ids') || str_contains($text, 'scan'))
        return ['class' => 'bg-sky-500/10 text-sky-400 border-sky-500/30', 'label' => 'IDS'];
    return ['class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30', 'label' => 'INFO'];
}

function parseMboxFile(string $path): array {
    $content = safeReadFile($path);
    if ($content === '') return [];

    $rawMessages = preg_split('/^From /m', $content);
    $messages = [];

    foreach ($rawMessages as $raw) {
        $raw = trim($raw);
        if ($raw === '') continue;

        $parts = preg_split('/\r?\n\r?\n/', $raw, 2);
        $from = decodeMimeStr(extractHeader($parts[0] ?? '', 'From'));
        $subject = decodeMimeStr(extractHeader($parts[0] ?? '', 'Subject'));
        $date = extractHeader($parts[0] ?? '', 'Date');
        $tag = classifyTag($subject . ' ' . $from);

        $messages[] = [
            'from' => $from ?: 'desconocido@local',
            'subject' => $subject ?: '(sin asunto)',
            'date' => formatMailDate($date),
            'tagClass' => $tag['class'],
            'tagLabel' => $tag['label'],
        ];
    }
    return array_reverse($messages);
}

function readLastLines(string $file, int $n): array {
    if (!file_exists($file) || !is_readable($file)) return [];
    $fp = fopen($file, 'r');
    if (!$fp) return [];
    fseek($fp, 0, SEEK_END);
    $chunk = min(ftell($fp), $n * 250);
    fseek($fp, -$chunk, SEEK_END);
    $data = fread($fp, $chunk);
    fclose($fp);
    return preg_split("/\r?\n/", $data);
}

function priorityToSev(int $pri): string {
    return match($pri) { 1 => 'HIGH', 2 => 'MEDIUM', 3 => 'LOW', default => 'INFO' };
}

function getSevColor(string $sev): string {
    return match($sev) {
        'HIGH' => 'bg-red-500/10 text-red-400 border-red-500/30',
        'MEDIUM' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
        'LOW' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
        default => 'bg-sky-500/10 text-sky-400 border-sky-500/30'
    };
}

function parseSnortAlerts(string $path, int $limit = 100): array {
    $lines = readLastLines($path, $limit * 5);
    $logs = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || !preg_match('/^\d{2}\/\d{2}-\d{2}:\d{2}:\d{2}/', $line)) continue;
        $logs[] = [
            'ts' => preg_match('/^(\d{2}\/\d{2}-\d{2}:\d{2}:\d{2})/', $line, $m) ? date('Y') . '-' . str_replace(['/', '-'], ['-', ' '], $m[1]) : '',
            'sev' => priorityToSev(preg_match('/\[Priority:\s*(\d+)\]/', $line, $p) ? (int)$p[1] : 3),
            'msg' => preg_match('/\[\*\*\]\s+(?:\[\d+:\d+:\d+\]\s+)?(.+?)\s+\[\*\*\]/', $line, $mm) ? trim($mm[1]) : 'Alerta Snort',
        ];
    }
    return array_reverse(array_slice($logs, 0, $limit));
}

$mails = parseMboxFile($MAIL_MBOX_PATH);
$snortLogs = parseSnortAlerts($SNORT_ALERT_PATH, 100);

$unreadCount = count($mails);
$alertCount = count(array_filter($snortLogs, fn($l) => in_array($l['sev'], ['HIGH', 'MEDIUM'], true)));

// HEADER DEL PROYECTO (Ruta relativa corregida)
include '../../includes/header.php';
?>

<main class="max-w-7xl mx-auto px-6 py-10 space-y-8 flex-grow w-full relative z-10 text-slate-200">
    
    <!-- Título y Estado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 border-b border-glass pb-5">
        <div>
            <h1 class="text-3xl font-black tracking-widest uppercase text-white">
                Auditoría <span class="text-blue-500 italic">Inteligente.</span>
            </h1>
            <p class="text-[11px] text-muted uppercase tracking-[0.3em] mt-2 font-bold">Monitor IDS & Bandeja de Postfix</p>
        </div>
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold tracking-widest uppercase shadow-lg">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            SYSTEM ONLINE
        </div>
    </div>

    <!-- Indicadores -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-glass border border-glass rounded-xl p-6 shadow-lg hover:border-blue-500/50 transition-all">
            <p class="text-[10px] text-muted font-bold uppercase tracking-widest mb-2"><i class="fas fa-inbox text-blue-500 mr-2"></i>Correos</p>
            <p class="text-3xl font-black text-blue-400"><?= $unreadCount ?></p>
        </div>
        <div class="bg-glass border border-glass rounded-xl p-6 shadow-lg hover:border-amber-500/50 transition-all">
            <p class="text-[10px] text-muted font-bold uppercase tracking-widest mb-2"><i class="fas fa-shield-virus text-amber-500 mr-2"></i>Alertas IDS</p>
            <p class="text-3xl font-black text-amber-400"><?= $alertCount ?></p>
        </div>
        <div class="bg-glass border border-glass rounded-xl p-6 shadow-lg col-span-2 md:col-span-1">
            <p class="text-[10px] text-muted font-bold uppercase tracking-widest mb-2"><i class="fas fa-folder text-emerald-500 mr-2"></i>Ruta Mail</p>
            <p class="text-[10px] font-mono text-slate-400 break-all"><?= htmlspecialchars($MAIL_MBOX_PATH) ?></p>
        </div>
        <div class="bg-glass border border-glass rounded-xl p-6 shadow-lg col-span-2 md:col-span-1">
            <p class="text-[10px] text-muted font-bold uppercase tracking-widest mb-2"><i class="fas fa-folder-open text-purple-500 mr-2"></i>Ruta Snort</p>
            <p class="text-[10px] font-mono text-slate-400 break-all"><?= htmlspecialchars($SNORT_ALERT_PATH) ?></p>
        </div>
    </div>

    <!-- Paneles de Datos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Panel Correos -->
        <div class="bg-glass border border-glass rounded-2xl shadow-xl flex flex-col h-[600px]">
            <div class="p-6 border-b border-glass bg-nav rounded-t-2xl">
                <h3 class="text-xs font-black uppercase tracking-widest text-blue-400"><i class="fas fa-envelope mr-2"></i>Bandeja de Entrada</h3>
            </div>
            <div class="overflow-y-auto p-2">
                <?php if (empty($mails)): ?>
                    <p class="p-6 text-muted text-xs text-center font-mono">No hay correos en el sistema.</p>
                <?php else: ?>
                    <?php foreach ($mails as $m): ?>
                        <div class="p-4 border-b border-glass hover:bg-white/5 transition-colors cursor-pointer">
                            <div class="flex justify-between items-start mb-2">
                                <p class="text-sm font-bold text-slate-200 truncate pr-4"><?= htmlspecialchars($m['from']) ?></p>
                                <p class="text-[10px] font-mono text-muted whitespace-nowrap"><?= htmlspecialchars($m['date']) ?></p>
                            </div>
                            <p class="text-xs text-muted mb-3 truncate"><?= htmlspecialchars($m['subject']) ?></p>
                            <span class="text-[9px] font-black tracking-widest uppercase px-2 py-1 rounded border <?= $m['tagClass'] ?>">
                                <?= htmlspecialchars($m['tagLabel']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Panel Snort Logs -->
        <div class="bg-glass border border-glass rounded-2xl shadow-xl flex flex-col h-[600px]">
            <div class="p-6 border-b border-glass bg-nav rounded-t-2xl">
                <h3 class="text-xs font-black uppercase tracking-widest text-amber-400"><i class="fas fa-satellite-dish mr-2"></i>Monitor Snort / IDS</h3>
            </div>
            <div class="overflow-y-auto p-2">
                <?php if (empty($snortLogs)): ?>
                    <p class="p-6 text-muted text-xs text-center font-mono">No hay alertas de IDS.</p>
                <?php else: ?>
                    <?php foreach ($snortLogs as $l): ?>
                        <div class="p-4 border-b border-glass hover:bg-white/5 transition-colors">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] font-mono text-muted"><?= htmlspecialchars($l['ts']) ?></span>
                                <span class="text-[9px] font-black tracking-widest uppercase px-2 py-1 rounded border <?= getSevColor($l['sev']) ?>">
                                    <?= htmlspecialchars($l['sev']) ?>
                                </span>
                            </div>
                            <div class="text-xs text-slate-300 font-mono break-words leading-relaxed">
                                <?= htmlspecialchars($l['msg']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
