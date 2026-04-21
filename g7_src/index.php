<?php
// Incluimos la conexión a la DB y el header con Tailwind/FontAwesome
include 'db_conn.php';
include 'views/header.php';
?>

<style>
    .glass-card {
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(51, 65, 85, 0.5);
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        border-color: rgba(14, 165, 233, 0.5);
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
    }
</style>

<main class="max-w-7xl mx-auto py-12 px-4 min-h-screen">
    
    <div class="text-center mb-16">
        <div class="inline-block px-4 py-1.5 mb-4 rounded-full border border-sky-500/30 bg-sky-500/10 text-sky-400 text-xs font-bold uppercase tracking-widest">
            v3.0 Flash Edition
        </div>
        <h1 class="text-5xl md:text-6xl font-black text-white mb-6 tracking-tighter">
            CyberAudit <span class="text-sky-500">Hub</span>
        </h1>
        <p class="text-slate-400 text-lg max-w-2xl mx-auto leading-relaxed">
            Plataforma integral de ciberseguridad para auditoría de activos, análisis forense en vivo y administración remota de infraestructuras.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="glass-card rounded-2xl p-8 group">
            <div class="w-14 h-14 bg-sky-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-sky-500/20 transition-colors">
                <i class="fas fa-satellite-dish text-2xl text-sky-500"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-3 tracking-tight">Auditoría Externa</h3>
            <p class="text-slate-400 text-sm mb-8 leading-relaxed">
                Escaneo de puertos asíncrono con Nmap, detección de servicios y motor de recomendaciones de mitigación inteligente.
            </p>
            <a href="scanner.php" class="flex items-center justify-between group/link text-sky-500 font-bold text-sm uppercase tracking-wider">
                <span>Lanzar Escáner</span>
                <i class="fas fa-arrow-right transform group-hover/link:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="glass-card rounded-2xl p-8 group">
            <div class="w-14 h-14 bg-purple-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-purple-500/20 transition-colors">
                <i class="fas fa-microscope text-2xl text-purple-500"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-3 tracking-tight">Forense SSH</h3>
            <p class="text-slate-400 text-sm mb-8 leading-relaxed">
                Inspección profunda de <code>auth.log</code>, detección de intrusiones por fuerza bruta y monitoreo de conexiones activas.
            </p>
            <a href="scanner.php" class="flex items-center justify-between group/link text-purple-500 font-bold text-sm uppercase tracking-wider">
                <span>Inspección en Vivo</span>
                <i class="fas fa-arrow-right transform group-hover/link:translate-x-1 transition-transform"></i>
            </a>
        </div>

        <div class="glass-card rounded-2xl p-8 group">
            <div class="w-14 h-14 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-emerald-500/20 transition-colors">
                <i class="fas fa-terminal text-2xl text-emerald-500"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-3 tracking-tight">Terminal Remota</h3>
            <p class="text-slate-400 text-sm mb-8 leading-relaxed">
                Consola interactiva SSH segura y asistente de reglas Firewall UFW para hardening inmediato de servidores.
            </p>
            <a href="scanner.php" class="flex items-center justify-between group/link text-emerald-500 font-bold text-sm uppercase tracking-wider">
                <span>Abrir Consola</span>
                <i class="fas fa-arrow-right transform group-hover/link:translate-x-1 transition-transform"></i>
            </a>
        </div>

    </div>

    <div class="mt-16 bg-slate-900/50 border border-slate-800 rounded-xl p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-slate-500 text-[10px] uppercase font-bold tracking-widest mb-1">Motor Nmap</p>
                <p class="text-emerald-400 text-xs font-mono"><i class="fas fa-check-circle mr-1"></i> Operativo</p>
            </div>
            <div>
                <p class="text-slate-500 text-[10px] uppercase font-bold tracking-widest mb-1">Módulo SSH2</p>
                <p class="text-emerald-400 text-xs font-mono"><i class="fas fa-check-circle mr-1"></i> Cargado</p>
            </div>
            <div>
                <p class="text-slate-500 text-[10px] uppercase font-bold tracking-widest mb-1">Sesiones Activas</p>
                <p class="text-white text-xs font-mono"><?php echo count($_SESSION); ?> en memoria</p>
            </div>
            <div>
                <p class="text-slate-500 text-[10px] uppercase font-bold tracking-widest mb-1">Seguridad</p>
                <p class="text-sky-400 text-xs font-mono"><i class="fas fa-shield-alt mr-1"></i> AES-256 Enabled</p>
            </div>
        </div>
    </div>

</main>

<?php include 'views/footer.php'; ?>
