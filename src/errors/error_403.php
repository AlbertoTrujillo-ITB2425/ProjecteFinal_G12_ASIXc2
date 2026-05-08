<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Restringido - SOC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-950 text-slate-200 h-screen flex items-center justify-center font-mono">
    <div class="max-w-md w-full p-8 border border-red-500/30 bg-red-500/5 rounded-2xl shadow-2xl text-center">
        <i class="fas fa-user-shield text-6xl text-red-500 mb-6 animate-pulse"></i>
        <h1 class="text-2xl font-black uppercase tracking-tighter mb-2">403 - Access Denied</h1>
        <p class="text-slate-400 text-sm mb-6">
            Tu dirección IP <span class="text-red-400 font-bold"><?= $_SERVER['REMOTE_ADDR'] ?></span> 
            no está en la lista blanca de telemetría.
        </p>
        <div class="bg-black/40 p-4 rounded-lg border border-white/10 text-left text-[10px] mb-6">
            <code class="text-emerald-500">> [SISTEMA]: Intento de acceso no autorizado</code><br>
            <code class="text-emerald-500">> [ORIGEN]: <?= $_SERVER['HTTP_USER_AGENT'] ?></code><br>
            <code class="text-red-500">> [ESTADO]: Petición bloqueada por Firewall</code>
        </div>
        <a href="/" class="inline-block bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-full transition-all text-sm uppercase tracking-widest">
            Volver al Dashboard
        </a>
    </div>
</body>
</html>
