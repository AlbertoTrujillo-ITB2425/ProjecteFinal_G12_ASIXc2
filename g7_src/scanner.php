<?php
include 'db_conn.php';
include 'views/header.php';

$ip = $_REQUEST['ip'] ?? '';
$nmap_result = "";
$recomendaciones = [];
$forensics = null;
$forensics_error = null;

// MOTOR 1: ESCANEO EXTERNO (NMAP)
if ($ip && $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Escaneo rápido de puertos y versiones de servicios
    $cmd = "nmap -F -sV " . escapeshellarg($ip);
    $nmap_result = shell_exec($cmd);

    // Generador Inteligente de Recomendaciones
    if (strpos($nmap_result, '21/tcp') !== false) {
        $recomendaciones[] = ['riesgo' => 'ALTO', 'msg' => 'Puerto FTP (21) abierto. El tráfico no está cifrado. Usa SFTP en su lugar.', 'color' => 'text-red-500'];
    }
    if (strpos($nmap_result, '22/tcp') !== false) {
        $recomendaciones[] = ['riesgo' => 'MEDIO', 'msg' => 'Puerto SSH (22) abierto. Recomendable cambiar el puerto por defecto e instalar Fail2Ban para evitar fuerza bruta.', 'color' => 'text-orange-500'];
    }
    if (strpos($nmap_result, '80/tcp') !== false && strpos($nmap_result, '443/tcp') === false) {
        $recomendaciones[] = ['riesgo' => 'MEDIO', 'msg' => 'Puerto HTTP (80) abierto sin HTTPS detectado. Despliega un certificado SSL/TLS (Let\'s Encrypt).', 'color' => 'text-orange-500'];
    }
    if (strpos($nmap_result, '3306/tcp') !== false) {
        $recomendaciones[] = ['riesgo' => 'CRÍTICO', 'msg' => 'Base de datos MySQL (3306) expuesta al exterior. Bloquea este puerto en el firewall inmediatamente.', 'color' => 'text-red-500'];
    }
    if (empty($recomendaciones)) {
        $recomendaciones[] = ['riesgo' => 'BAJO', 'msg' => 'No se han detectado puertos críticos expuestos en el escaneo rápido.', 'color' => 'text-emerald-500'];
    }
}

// MOTOR 2: EXTRACCIÓN FORENSE VIA SSH
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ssh_user'])) {
    $ip = $_POST['ip'];
    $user = $_POST['ssh_user'];
    $pass = $_POST['ssh_pass'];

    if (function_exists('ssh2_connect')) {
        $connection = @ssh2_connect($ip, 22);
        if ($connection && @ssh2_auth_password($connection, $user, $pass)) {
            $forensics = [];
            
            // Función auxiliar para ejecutar comandos
            $exec_ssh = function($cmd) use ($connection, $user, $pass) {
                if ($user !== 'root' && strpos($cmd, 'sudo') !== false) {
                    $cmd = "echo '$pass' | sudo -S " . str_replace('sudo ', '', $cmd);
                }
                $stream = ssh2_exec($connection, $cmd);
                stream_set_blocking($stream, true);
                return stream_get_contents($stream);
            };

            // 1. Conexiones Establecidas (ss o netstat)
            $forensics['conexiones'] = $exec_ssh("ss -tun state established");
            
            // 2. Usuarios Activos
            $forensics['usuarios'] = $exec_ssh("w");
            
            // 3. Ataques de Fuerza Bruta (Logs)
            $forensics['ataques'] = $exec_ssh("sudo grep 'Failed password' /var/log/auth.log | tail -n 15");

        } else {
            $forensics_error = "Credenciales SSH denegadas o puerto cerrado.";
        }
    } else {
        $forensics_error = "El módulo SSH2 no está activo en el contenedor PHP.";
    }
}
?>

<main class="max-w-7xl mx-auto py-8 px-4">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white"><i class="fas fa-radar text-sky-500 mr-2"></i> Auditoría Avanzada</h1>
            <p class="text-slate-400 text-sm mt-1">Superficie de ataque externa e inspección forense interna.</p>
        </div>
        <a href="index.php" class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition-all"><i class="fas fa-arrow-left mr-2"></i> Volver al Hub</a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-8 shadow-lg">
        <form action="scanner.php" method="GET" class="flex gap-4">
            <input type="text" name="ip" value="<?php echo htmlspecialchars($ip); ?>" placeholder="Introduce IP o Dominio..." required
                   class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-sky-500 outline-none">
            <button class="bg-sky-600 hover:bg-sky-500 text-white px-8 py-2 rounded-lg font-bold shadow-lg shadow-sky-500/20">Escanear Exterior</button>
        </form>
    </div>

    <?php if ($ip): ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg">
                <div class="bg-slate-800 px-4 py-3 flex justify-between items-center">
                    <h3 class="font-bold text-white text-sm"><i class="fas fa-network-wired text-sky-500 mr-2"></i> Reconocimiento Nmap (Caja Negra)</h3>
                </div>
                <div class="p-4 bg-black font-mono text-xs text-emerald-400 whitespace-pre-wrap overflow-x-auto h-64"><?php echo htmlspecialchars($nmap_result ?: "Iniciando escaneo...\nNo se pudo obtener respuesta."); ?></div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-lg">
                <h3 class="font-bold text-white mb-4 text-sm"><i class="fas fa-clipboard-check text-emerald-500 mr-2"></i> Recomendaciones de Mitigación</h3>
                <div class="space-y-3">
                    <?php foreach($recomendaciones as $rec): ?>
                    <div class="bg-slate-950 p-3 rounded border border-slate-800 flex items-start gap-3">
                        <span class="text-xs font-bold px-2 py-1 rounded bg-slate-800 <?php echo $rec['color']; ?>"><?php echo $rec['riesgo']; ?></span>
                        <p class="text-sm text-slate-300"><?php echo $rec['msg']; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-lg p-6 relative overflow-hidden">
                <h3 class="font-bold text-white text-lg mb-2"><i class="fas fa-microscope text-purple-500 mr-2"></i> Análisis Forense en Vivo</h3>
                <p class="text-xs text-slate-400 mb-6">Requiere acceso SSH para inspeccionar las tripas del servidor y extraer conexiones activas y ataques.</p>

                <?php if (!$forensics): ?>
                <form action="scanner.php" method="POST" class="space-y-4 bg-slate-950 p-4 rounded-xl border border-slate-800">
                    <input type="hidden" name="ip" value="<?php echo htmlspecialchars($ip); ?>">
                    <?php if($forensics_error): ?>
                        <div class="text-xs text-red-500 bg-red-500/10 p-2 rounded border border-red-500/20"><i class="fas fa-triangle-exclamation"></i> <?php echo $forensics_error; ?></div>
                    <?php endif; ?>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1 uppercase">Usuario Linux</label>
                            <input type="text" name="ssh_user" value="root" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1 uppercase">Contraseña</label>
                            <input type="password" name="ssh_pass" required class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white text-sm outline-none">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-500 text-white font-bold py-2 rounded-lg text-sm shadow-lg shadow-purple-500/20 transition-all flex justify-center items-center gap-2">
                        <i class="fas fa-key"></i> Autorizar Inspección Profunda
                    </button>
                </form>
                <?php else: ?>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-red-400 font-bold uppercase tracking-wider">Ataques Recientes (auth.log)</span>
                        </div>
                        <div class="bg-black p-3 rounded border border-red-500/30 text-[10px] font-mono text-red-400 h-24 overflow-y-auto whitespace-pre-wrap"><?php echo htmlspecialchars($forensics['ataques'] ?: "No se detectaron intentos de fuerza bruta recientes."); ?></div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-sky-400 font-bold uppercase tracking-wider">Conexiones Establecidas</span>
                        </div>
                        <div class="bg-black p-3 rounded border border-sky-500/30 text-[10px] font-mono text-sky-300 h-24 overflow-y-auto whitespace-pre-wrap"><?php echo htmlspecialchars($forensics['conexiones'] ?: "No hay conexiones establecidas."); ?></div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-emerald-400 font-bold uppercase tracking-wider">Usuarios Autenticados</span>
                        </div>
                        <div class="bg-black p-3 rounded border border-emerald-500/30 text-[10px] font-mono text-emerald-300 h-20 overflow-y-auto whitespace-pre-wrap"><?php echo htmlspecialchars($forensics['usuarios'] ?: "No hay usuarios logueados."); ?></div>
                    </div>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php include 'views/footer.php'; ?>
