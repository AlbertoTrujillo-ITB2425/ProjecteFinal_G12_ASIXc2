<?php 
// 1. Conexión a la Base de Datos y Sesiones
include 'db_conn.php'; 

// Opcional: Validación de sesión de usuario (Descomentar cuando el login esté 100% activo)
// if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// 2. Cabecera HTML y Tailwind CSS
include 'views/header.php'; 
?>

<main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white tracking-tight">CyberAudit Hub</h1>
            <p class="text-sm text-slate-400 mt-1">Centro de Operaciones de Seguridad (SOC) para <span class="text-sky-500 font-mono"><?php echo $_SERVER['SERVER_NAME']; ?></span></p>
        </div>
        <div class="flex gap-4">
            <div class="bg-slate-900 border border-slate-800 rounded-lg px-4 py-2 flex items-center gap-3 shadow-lg">
                <div class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Estado Global</p>
                    <p class="text-sm font-bold text-emerald-500">Protección Activa</p>
                </div>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-lg px-4 py-2 shadow-lg hidden sm:block">
                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Amenazas Bloqueadas</p>
                <p class="text-sm font-bold text-white">1,248 <span class="text-emerald-500 text-xs">↑ 12%</span></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <div class="xl:col-span-2 space-y-6">
            
            <section class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-sky-500/5 rounded-full blur-3xl group-hover:bg-sky-500/10 transition-all duration-500"></div>
                <h3 class="text-xl font-bold text-white mb-2 flex items-center gap-3">
                    <i class="fas fa-radar text-sky-500"></i> Auditoría de Superficie de Ataque
                </h3>
                <p class="text-sm text-slate-400 mb-5">Descubre puertos abiertos y vulnerabilidades (CVEs) expuestas a internet en segundos.</p>
                <form action="scanner.php" method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <i class="fas fa-globe absolute left-4 top-3.5 text-slate-500"></i>
                        <input type="text" name="ip" placeholder="Ej: dominio.com, 8.8.8.8 o s4_mariadb" 
                               class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-white focus:ring-2 focus:ring-sky-500 outline-none transition-all placeholder:text-slate-600">
                    </div>
                    <button class="bg-sky-600 hover:bg-sky-500 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-sky-500/20 transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                        <i class="fas fa-crosshairs"></i> Lanzar Escaneo
                    </button>
                </form>
            </section>

            <section class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl flex flex-col h-80">
                <div class="bg-slate-800/50 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="font-bold text-white flex items-center gap-2">
                        <i class="fas fa-chart-network text-purple-500"></i> Telemetría SIEM (Live)
                    </h3>
                    <span class="text-[10px] bg-purple-500/20 text-purple-400 border border-purple-500/30 px-2 py-1 rounded font-mono">Conectando a Wazuh S7...</span>
                </div>
                <div class="flex-1 bg-black p-4 font-mono text-[11px] overflow-y-auto" id="siem-console">
                    <div class="text-slate-500">> Inicializando recolector de logs... OK</div>
                    <div class="text-slate-500">> Escuchando en puerto 1514...</div>
                </div>
            </section>

            <section class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-shield-halved text-orange-500"></i> Asistente de Firewall
                        </h3>
                        <p class="text-xs text-slate-400 mt-1">Configuración UFW sugerida basada en tus servicios actuales.</p>
                    </div>
                    <span class="text-xs font-mono text-slate-400 bg-slate-950 px-3 py-1 rounded border border-slate-800">Motor: IPTables/UFW</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs text-slate-500 font-bold tracking-wider">SCRIPT DE MITIGACIÓN</span>
                            <i class="fas fa-copy text-slate-600 hover:text-white cursor-pointer transition-colors" title="Copiar al portapapeles"></i>
                        </div>
                        <code class="text-emerald-400 text-sm leading-relaxed block">
                            ufw default deny incoming<br>
                            ufw allow 80/tcp <span class="text-slate-600"># HTTP Web</span><br>
                            ufw allow 443/tcp <span class="text-slate-600"># HTTPS Seguro</span><br>
                            ufw deny 3306/tcp <span class="text-orange-500"># Aislar Base de Datos</span><br>
                            ufw enable
                        </code>
                    </div>
                    <div class="flex flex-col justify-center gap-3">
                        <button class="bg-orange-600/10 hover:bg-orange-600/20 text-orange-500 border border-orange-500/30 hover:border-orange-500 py-3 rounded-xl font-bold transition-all flex justify-center items-center gap-2">
                            <i class="fas fa-bolt"></i> Aplicar Reglas al Host
                        </button>
                        <p class="text-[10px] text-slate-500 text-center leading-tight">La aplicación automática requiere que el contenedor S2 tenga acceso SSH o sockets montados en el host.</p>
                    </div>
                </div>
            </section>
            
        </div>

        <div class="space-y-6">
            
            <section class="bg-black border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="bg-slate-800 px-4 py-3 border-b border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-terminal text-slate-400 text-xs"></i>
                        <span class="text-xs font-mono text-slate-300">Terminal Web</span>
                    </div>
                    <span class="text-[9px] uppercase tracking-wider text-emerald-500 font-bold"><i class="fas fa-circle text-[6px] align-middle mb-[1px]"></i> SSH Ready</span>
                </div>
                <div class="p-4 h-48 font-mono text-[11px] text-sky-400 overflow-y-auto" style="scrollbar-width: thin; scrollbar-color: #334155 transparent;">
                    <p class="text-emerald-500">Welcome to CyberAudit Node S2 (Alpine Linux)</p>
                    <p class="text-slate-500 mb-2">Authenticated via WebConsole.</p>
                    <p>~ # <span class="animate-pulse">_</span></p>
                </div>
                <div class="p-2 bg-slate-900 border-t border-slate-800 flex gap-2">
                    <span class="text-emerald-500 font-mono text-xs pl-2 py-1">~ #</span>
                    <input type="text" class="flex-1 bg-transparent border-none focus:ring-0 text-white font-mono text-xs outline-none" placeholder="Comando rápido (ej. top)">
                </div>
            </section>

            <section class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl text-center relative overflow-hidden">
                <div class="absolute -right-4 -top-4 text-slate-800 opacity-20">
                    <i class="fas fa-fingerprint text-9xl"></i>
                </div>
                <div class="relative z-10">
                    <div class="bg-slate-800 border border-slate-700 w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                        <i class="fab fa-google text-2xl text-white"></i>
                    </div>
                    <h4 class="text-white font-bold mb-1">Doble Factor (2FA)</h4>
                    <p class="text-sm text-slate-400 mb-4">Protección de identidad activa</p>
                    <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 px-3 py-1 rounded-full text-xs font-bold">
                        <i class="fas fa-shield-check"></i> Sincronizado
                    </div>
                </div>
            </section>

<section class="bg-gradient-to-b from-indigo-950 to-slate-900 border border-indigo-500/30 rounded-2xl p-6 shadow-xl relative overflow-hidden">
    <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-3xl"></div>
    
    <div class="relative z-10">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                CyberAudit <span class="bg-indigo-500 text-white text-[10px] px-2 py-0.5 rounded uppercase tracking-wider">Pro</span>
            </h3>
            <img src="https://cryptologos.cc/logos/usd-coin-usdc-logo.png" class="w-6 h-6" alt="USDC">
        </div>
        
        <div class="my-4 flex items-baseline gap-1">
            <span class="text-3xl font-black text-white">29.99</span>
            <span class="text-sm font-bold text-indigo-400">USDC / mes</span>
        </div>
        
        <p class="text-xs text-slate-400 mb-6 text-balance">Desbloquea escaneos automatizados diarios, reportes forenses en PDF y remediación inteligente de firewall.</p>
        
        <button id="phantom-btn" onclick="payWithPhantom()" class="w-full bg-[#AB9FF2] hover:bg-[#8A78F0] text-slate-900 py-3 rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all text-sm flex items-center justify-center gap-3">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a7/Phantom_icon.svg/1024px-Phantom_icon.svg.png" class="w-5 h-5" alt="Phantom">
            <span id="phantom-btn-text">Pagar Suscripción con Phantom</span>
        </button>
        
        <div class="mt-4 text-center">
            <p class="text-[10px] text-slate-500 font-mono" id="wallet-status">Red: Solana Devnet (Pruebas)</p>
        </div>
    </div>
</section>

        </div>
    </div>
</main>

<script>
    const siemConsole = document.getElementById('siem-console');
    const fakeLogs = [
        "<span class='text-emerald-500'>[OK]</span> Conexión TCP aceptada desde 192.168.1.45:49201",
        "<span class='text-amber-500'>[WARN]</span> Nivel de RAM en nodo S4_MariaDB superando 75%",
        "<span class='text-emerald-500'>[OK]</span> Sincronización de reglas LDAP completada",
        "<span class='text-sky-500'>[INFO]</span> Escaneo de Nmap finalizado sin anomalías",
        "<span class='text-red-500'>[ALERT]</span> Intento de autenticación SSH fallido (root) desde 45.33.22.11",
        "<span class='text-purple-500'>[WAZUH]</span> Regla 5710 detectada: Posible escaneo de puertos externo",
        "<span class='text-emerald-500'>[OK]</span> Backup de base de datos cifrado y almacenado."
    ];

    function injectLog() {
        const log = fakeLogs[Math.floor(Math.random() * fakeLogs.length)];
        const timestamp = new Date().toISOString().split('T')[1].slice(0, -1);
        const logLine = document.createElement('div');
        logLine.className = "mb-1 border-b border-slate-800/50 pb-1";
        logLine.innerHTML = `<span class='text-slate-600'>[${timestamp}]</span> ${log}`;
        
        siemConsole.appendChild(logLine);
        
        // Auto-scroll hacia abajo
        if(siemConsole.childElementCount > 50) siemConsole.removeChild(siemConsole.firstChild);
        siemConsole.scrollTop = siemConsole.scrollHeight;

        // Intervalo aleatorio entre 1.5 y 4 segundos
        setTimeout(injectLog, Math.random() * 2500 + 1500);
    }

    setTimeout(injectLog, 2000);
</script>

<?php include 'views/footer.php'; ?>
