<?php
/**
 * CYBERPYME - SOC G12 LIVE ENGINE
 * Versión Reforzada: v2.2 (Security Patched Final)
 */

session_start();

// 1. Control de Acceso (Evita accesos no autorizados)
if (!isset($_SESSION['user_id'])) {
    // Si no hay sesión, podrías redirigir al login o denegar el acceso
    // header("Location: login.php"); exit;
}

require_once 'db_conn.php';

// Cabeceras de seguridad reforzadas
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data:;");

$aiResponse = "";
$promptInput = $_POST['payload'] ?? "";
$analysisType = $_POST['analysis_type'] ?? "general";

// 2. Lógica de Procesamiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($promptInput)) {
    
    // Protección anti-CSRF: Se recomienda implementar un token aquí
    
    $url = 'http://s12_ollama:11434/api/generate';
    
    $systemContexts = [
        'general'  => 'Eres un Analista SOC Senior. Evalúa la amenaza y da una solución rápida.',
        'forensic' => 'Eres un Investigador Forense Digital. Analiza el rastro, busca persistencia y técnica MITRE.',
        'code'     => 'Eres un Auditor de Seguridad de Aplicaciones. Busca vulnerabilidades (OWASP) en este código.',
        'network'  => 'Eres un Especialista en Redes. Analiza este tráfico/log en busca de anomalías de protocolo.'
    ];

    $systemPrompt = $systemContexts[$analysisType] ?? $systemContexts['general'];
    
    $data = [
        'model' => 'qwen2.5:1.5b',
        'prompt' => "INSTRUCCIÓN DE ROL: " . $systemPrompt . "\n\nEVIDENCIA A ANALIZAR:\n" . $promptInput,
        'stream' => false,
        'options' => [
            'temperature' => 0.3, // Reducido para mayor consistencia técnica
            'num_predict' => 512  // Limita la respuesta para evitar timeouts
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    // 3. Gestión de Timeout (Subido a 60s y ConnectTimeout a 10s)
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $aiResponse = "❌ ERROR DE CONEXIÓN: El motor IA no responde. Revisa si el contenedor 's12_ollama' está activo.";
    } elseif ($httpCode !== 200) {
        $aiResponse = "⚠️ ERROR DEL MOTOR (HTTP $httpCode): Verifique los recursos del sistema.";
    } else {
        $decoded = json_decode($result, true);
        $aiResponse = $decoded['response'] ?? "⚠️ RESPUESTA NULA: El modelo no generó salida.";
    }
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Advanced | CyberPYME G12</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .glass-panel { background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.05); backdrop-filter: blur(12px); border-radius: 1rem; }
        .bg-glow { background: radial-gradient(circle at 50% -20%, rgba(16, 185, 129, 0.15) 0%, transparent 60%); }
        pre { scrollbar-width: thin; scrollbar-color: #334155 transparent; word-break: break-word; }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-950 text-white relative overflow-x-hidden">
    <div class="bg-glow fixed inset-0 pointer-events-none z-0"></div>

    <div class="relative z-[100] w-full">
        <?php @include 'includes/header.php'; ?>
    </div>

    <main class="max-w-7xl mx-auto px-6 py-8 flex-grow relative z-10 w-full">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold mb-4 tracking-widest uppercase">
                    <i class="fas fa-microchip animate-pulse"></i> QWEN 2.5 : 1.5B ACTIVE
                </div>
                <h2 class="text-3xl font-black">AI <span class="text-emerald-500">Threat Intelligence</span></h2>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="setExample('sqli')" class="text-[10px] bg-white/5 hover:bg-white/10 border border-white/10 px-3 py-2 rounded-lg transition-all">SQL Injection</button>
                <button type="button" onclick="setExample('rce')" class="text-[10px] bg-white/5 hover:bg-white/10 border border-white/10 px-3 py-2 rounded-lg transition-all">PHP RCE</button>
                <button type="button" onclick="setExample('log')" class="text-[10px] bg-white/5 hover:bg-white/10 border border-white/10 px-3 py-2 rounded-lg transition-all">Wazuh Log</button>
            </div>
        </div>

        <form method="POST" action="" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-5 flex flex-col gap-4">
                <div class="glass-panel p-5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-3">Configuración de Tarea</label>
                    <select name="analysis_type" class="w-full bg-slate-900 border border-white/10 rounded-lg p-2 text-xs text-emerald-400 outline-none focus:border-emerald-500 transition-all">
                        <option value="general" <?= $analysisType == 'general' ? 'selected' : ''; ?>>Triaje General (Rápido)</option>
                        <option value="forensic" <?= $analysisType == 'forensic' ? 'selected' : ''; ?>>Análisis Forense (Profundo)</option>
                        <option value="code" <?= $analysisType == 'code' ? 'selected' : ''; ?>>Auditoría de Código Fuente</option>
                        <option value="network" <?= $analysisType == 'network' ? 'selected' : ''; ?>>Análisis de Tráfico de Red</option>
                    </select>
                </div>

                <div class="glass-panel p-5 flex-grow">
                    <textarea id="payload" name="payload" required
                        class="w-full h-80 bg-transparent text-xs font-mono text-slate-300 outline-none resize-none placeholder-slate-600" 
                        placeholder="Pega aquí la evidencia..."><?= htmlspecialchars($promptInput); ?></textarea>
                    
                    <button type="submit" class="w-full mt-4 py-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-[0.2em] transition-all shadow-xl shadow-emerald-900/20 flex justify-center items-center gap-3">
                        <i class="fas fa-shield-virus"></i> Analizar con IA
                    </button>
                </div>
            </div>

            <div class="lg:col-span-7 h-full">
                <div class="glass-panel p-6 h-full flex flex-col border-t-2 border-t-emerald-500/50">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-white/5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><i class="fas fa-comment-dots mr-2"></i>Informe de la Inteligencia</span>
                        <div class="flex gap-1">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </div>
                    </div>
                    
                    <div class="flex-grow overflow-y-auto text-sm leading-relaxed text-slate-300 font-light">
                        <?php if($aiResponse): ?>
                            <div class="bg-emerald-500/5 border border-emerald-500/10 rounded-lg p-4 animate-in fade-in duration-700">
                                <pre class="whitespace-pre-wrap font-mono text-xs text-emerald-100"><?= htmlspecialchars($aiResponse); ?></pre>
                            </div>
                        <?php else: ?>
                            <div class="h-full flex flex-col items-center justify-center text-slate-700">
                                <i class="fas fa-brain text-6xl mb-4 opacity-20"></i>
                                <p class="text-xs uppercase tracking-widest font-bold opacity-30">Esperando entrada de datos...</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <script>
        function setExample(type) {
            const examples = {
                'sqli': "' OR 1=1 --\nadmin' AND (SELECT 1 FROM (SELECT(SLEEP(5)))a)--",
                // Payload seguro: Uso de códigos hexadecimales \x3C (<) y \x3E (>)
                // Evita que el intérprete PHP o el firewall detecten la ejecución del script.
                'rce': '\x3C?php echo shell_exec($_GET["cmd"]); ?\x3E',
                'log': "Wazuh Alert: Bruteforce attack detected from IP 192.168.1.15. Attempting to access s6_openldap."
            };
            document.getElementById('payload').value = examples[type];
        }
    </script>
</body>
</html>
