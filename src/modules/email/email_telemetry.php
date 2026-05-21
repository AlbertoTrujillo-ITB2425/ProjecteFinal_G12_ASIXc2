<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

header('Content-Type: application/json');

$MAIL_MBOX_PATH   = getenv('MAIL_MBOX_PATH') ?: '/var/mail/root';
$SNORT_ALERT_PATH = getenv('SNORT_ALERT_PATH') ?: '/var/log/snort/alert';

function safeReadFile(string $path): string {
    if (!file_exists($path) || !is_readable($path)) return '';
    return file_get_contents($path) ?: '';
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
    return $str;
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
        return ['class' => 'bg-red-500/10 text-red-400 border-red-500/10', 'label' => 'ALERTA'];
    if (str_contains($text, 'warn') || str_contains($text, 'warning'))
        return ['class' => 'bg-amber-500/10 text-amber-400 border-amber-500/10', 'label' => 'WARN'];
    if (str_contains($text, 'snort') || str_contains($text, 'ids') || str_contains($text, 'scan'))
        return ['class' => 'bg-sky-500/10 text-sky-400 border-sky-500/10', 'label' => 'IDS'];
    return ['class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/10', 'label' => 'INFO'];
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
    return preg_split("/\r?\n/", $data) ?: [];
}

function priorityToSev(int $pri): string {
    return match($pri) { 1 => 'HIGH', 2 => 'MEDIUM', 3 => 'LOW', default => 'INFO' };
}

function getSevColor(string $sev): string {
    return match($sev) {
        'HIGH' => 'bg-red-500/10 text-red-400 border-red-500/10',
        'MEDIUM' => 'bg-amber-500/10 text-amber-400 border-amber-500/10',
        'LOW' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/10',
        default => 'bg-sky-500/10 text-sky-400 border-sky-500/10'
    };
}

function parseSnortAlerts(string $path, int $limit = 100): array {
    $lines = readLastLines($path, $limit * 5);
    $logs = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || !preg_match('/^\d{2}\/\d{2}-\d{2}:\d{2}:\d{2}/', $line)) continue;
        $sev = priorityToSev(preg_match('/\[Priority:\s*(\d+)\]/', $line, $p) ? (int)$p[1] : 3);
        $logs[] = [
            'ts' => preg_match('/^(\d{2}\/\d{2}-\d{2}:\d{2}:\d{2})/', $line, $m) ? date('Y') . '-' . str_replace(['/', '-'], ['-', ' '], $m[1]) : '',
            'sev' => $sev,
            'sevColor' => getSevColor($sev),
            'msg' => preg_match('/\[\*\*\]\s+(?:\[\d+:\d+:\d+\]\s+)?(.+?)\s+\[\*\*\]/', $line, $mm) ? trim($mm[1]) : 'Alerta Snort',
        ];
    }
    return array_reverse(array_slice($logs, 0, $limit));
}

$mails = parseMboxFile($MAIL_MBOX_PATH);
$snortLogs = parseSnortAlerts($SNORT_ALERT_PATH, 100);

echo json_encode([
    'unreadCount' => count($mails),
    'alertCount' => count(array_filter($snortLogs, fn($l) => in_array($l['sev'], ['HIGH', 'MEDIUM'], true))),
    'mails' => $mails,
    'snortLogs' => $snortLogs
]);
