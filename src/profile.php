<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Importamos la lógica de extracción de datos (Asegúrate de que la ruta sea correcta)
require_once __DIR__ . '/core/user/profile_fetch.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOC | Auditor Dashboard</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    
    <script>
        // Aplicar modo oscuro por defecto o según sistema
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
        tailwind.config = { darkMode: 'class' }
    </script>
</head>

<body class="min-h-screen transition-colors duration-300">

    <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-10">

        <div class="glass-panel rounded-[2.5rem] p-8 mb-8 flex flex-wrap items-center justify-between gap-6 shadow-2xl">
            <div class="flex items-center gap-6">
                <div class="relative group">
                    <img src="<?= $avatarUrl ?>" id="header-avatar" class="w-24 h-24 rounded-3xl border-2 border-sky-500/30 object-cover shadow-lg">
                    <div class="absolute inset-0 bg-black/40 rounded-3xl opacity-0 group-hover:opacity-100 flex items-center justify-center cursor-pointer transition-all">
                        <i class="fas fa-sync-alt text-white animate-spin-slow"></i>
                    </div>
                    <span class="absolute bottom-1 right-1 w-5 h-5 bg-green-500 border-4 border-white dark:border-[#020617] rounded-full"></span>
                </div>
                <div>
                    <h2 id="header-name-display" class="text-3xl font-black uppercase tracking-tight"><?= htmlspecialchars($userName) ?></h2>
                    <p class="text-sky-500 text-[10px] font-black uppercase tracking-[0.2em] opacity-80 italic">
                        <?= htmlspecialchars($userRole) ?> // Auditor Tier 1
                    </p>
                </div>
            </div>
            
            <div class="flex gap-4">
                <div class="glass-panel bg-opacity-10 p-4 rounded-2xl text-center min-w-[110px]">
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Cloud Logs</p>
                    <p class="text-xl font-black" id="stat-scans"><?= count($lastScans) ?></p>
                </div>
                <div class="glass-panel bg-opacity-10 p-4 rounded-2xl text-center min-w-[110px]">
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Registro</p>
                    <p class="text-xs font-black text-sky-400 uppercase"><?= $user['member_since'] ?? '2024' ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">
            
            <aside class="col-span-12 lg:col-span-3 space-y-4">
                <div class="glass-panel rounded-3xl p-3 flex flex-col gap-1">
                    <button data-tab="personal" class="tab-btn active">
                        <i class="fas fa-id-badge text-lg mr-4"></i> Identidad
                    </button>
                    <button data-tab="history" class="tab-btn">
                        <i class="fas fa-database text-lg mr-4"></i> SOC Cloud
                    </button>
                    <button data-tab="security" class="tab-btn">
                        <i class="fas fa-user-lock text-lg mr-4"></i> Seguridad
                    </button>
                </div>

                <button onclick="confirmLogout()" class="w-full flex items-center justify-center gap-3 p-4 rounded-2xl bg-red-500/10 hover:bg-red-600 hover:text-white text-red-500 text-[10px] font-black uppercase tracking-widest transition-all border border-red-500/20">
                    <i class="fas fa-power-off"></i> Cerrar Sesión
                </button>
            </aside>

            <section class="col-span-12 lg:col-span-9">

                <div id="content-personal" class="tab-content glass-panel rounded-3xl p-10">
                    <h3 class="text-xl font-black uppercase mb-8 border-b border-white/5 pb-4">Ajustes de Identidad</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Nombre Público</label>
                            <input type="text" id="input-name" value="<?= htmlspecialchars($userName) ?>" class="input-field dynamic-input">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Email Corporativo</label>
                            <input type="text" value="<?= htmlspecialchars($userEmail) ?>" disabled class="input-field bg-black/30 opacity-50 cursor-not-allowed">
                        </div>
                    </div>
                    <button onclick="updateProfileAction('update_name')" class="mt-8 bg-sky-600 hover:bg-sky-500 text-white font-black px-10 py-4 rounded-2xl text-[10px] uppercase tracking-widest transition-all shadow-lg shadow-sky-500/20">
                        Sincronizar Cambios
                    </button>
                </div>

                <div id="content-history" class="tab-content hidden glass-panel rounded-3xl p-10">
                    <div class="flex justify-between items-center mb-8 border-b border-white/5 pb-4">
                        <h3 class="text-xl font-black uppercase">Historial de Escaneos</h3>
                        <button onclick="clearCloudData()" class="text-[9px] font-black uppercase text-orange-500 hover:text-orange-400">
                            <i class="fas fa-eraser mr-1"></i> Purgar Nube
                        </button>
                    </div>
                    <div class="space-y-3">
                        <?php if (!empty($lastScans)): ?>
                            <?php foreach ($lastScans as $scan): ?>
                                <div class="scan-row flex items-center justify-between p-4 rounded-2xl border border-white/5 bg-white/[0.02] transition-all hover:translate-x-1">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-sky-500/10 flex items-center justify-center text-sky-500">
                                            <i class="fas fa-shield-virus"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-white"><?= htmlspecialchars($scan['target']) ?></p>
                                            <p class="text-[9px] uppercase text-slate-500"><?= htmlspecialchars($scan['type']) ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-mono text-slate-400"><?= $scan['created_at'] ?></p>
                                        <span class="text-[8px] px-2 py-0.5 rounded bg-green-500/20 text-green-500 border border-green-500/20 font-bold uppercase">Encrypted</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-20 opacity-30">
                                <i class="fas fa-database text-4xl mb-4"></i>
                                <p class="text-xs font-black uppercase">No hay registros en el SOC Cloud</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="content-security" class="tab-content hidden glass-panel rounded-3xl p-10">
                    <h3 class="text-xl font-black uppercase mb-8 border-b border-white/5 pb-4 text-red-500">Protocolos de Seguridad</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Nueva Credencial</label>
                                <input type="password" id="new-pass" placeholder="••••••••" class="input-field dynamic-input focus:border-red-500/50">
                            </div>
                            <button onclick="updateProfileAction('update_password')" class="bg-white/5 hover:bg-white/10 text-white font-black px-8 py-3 rounded-xl text-[10px] uppercase border border-white/10 transition-all">
                                Actualizar Contraseña
                            </button>
                        </div>

                        <div class="bg-red-500/5 border border-red-500/20 p-6 rounded-2xl">
                            <h4 class="text-[10px] font-black uppercase text-red-500 mb-4">Acciones Críticas</h4>
                            <div class="space-y-3">
                                <button onclick="updateProfileAction('logout_devices')" class="w-full flex items-center justify-between p-3 bg-black/20 rounded-xl hover:bg-black/40 transition-all group">
                                    <span class="text-[9px] font-bold uppercase">Matar otras sesiones</span>
                                    <i class="fas fa-broadcast-tower text-yellow-500 opacity-50 group-hover:opacity-100"></i>
                                </button>
                                <button onclick="deleteAccount()" class="w-full flex items-center justify-between p-3 bg-red-500/20 rounded-xl hover:bg-red-600 hover:text-white transition-all group">
                                    <span class="text-[9px] font-bold uppercase">Auto-destruir Cuenta</span>
                                    <i class="fas fa-user-slash opacity-50 group-hover:opacity-100"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // 1. GESTIÓN DE TABS
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const target = btn.getAttribute('data-tab');
                document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
                document.getElementById('content-' + target).classList.remove('hidden');
            });
        });

        // 2. CONTROLADOR AJAX (Conecta con core/user/profile_updates.php)
        async function updateProfileAction(action) {
            const formData = new FormData();
            formData.append('action', action);

            if(action === 'update_name') {
                const nameInput = document.getElementById('input-name');
                if(nameInput.value.length < 2) return alert("Nombre demasiado corto");
                formData.append('name', nameInput.value);
            }
            if(action === 'update_password') {
                const passInput = document.getElementById('new-pass');
                if(passInput.value.length < 4) return alert("Mínimo 4 caracteres");
                formData.append('password', passInput.value);
            }

            try {
                const response = await fetch('core/user/profile_updates.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if(res.status === 'success') {
                    if(action === 'update_name') {
                        // Actualización visual inmediata de Nombre y Avatar
                        document.getElementById('header-name-display').innerText = res.newName;
                        const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(res.newName)}&background=0ea5e9&color=fff&bold=true&size=128&v=${Date.now()}`;
                        document.getElementById('header-avatar').src = avatarUrl;
                    }
                    if(action === 'update_password') document.getElementById('new-pass').value = '';
                    
                    alert("SOC Core: Operación autorizada.");
                } else {
                    alert("Error: " + res.message);
                }
            } catch (e) {
                alert("Fallo de conexión con el controlador.");
            }
        }

        // 3. FUNCIONES DE ELIMINACIÓN
        function clearCloudData() {
            if(confirm("¿Purgar todos los registros de escaneo de la nube? Esta acción no se puede deshacer.")) {
                updateProfileAction('clear_data').then(() => location.reload());
            }
        }

        function deleteAccount() {
            if(confirm("☢ ALERTA CRÍTICA: Se borrarán todos tus datos de auditor y tu acceso. ¿Confirmar auto-destrucción?")) {
                updateProfileAction('delete_account').then(() => {
                    window.location.href = 'auth.php';
                });
            }
        }

        function confirmLogout() {
            if(confirm("¿Finalizar sesión actual?")) {
                window.location.href = 'core/auth_handler.php?action=logout';
            }
        }
    </script>
</body>
</html>
