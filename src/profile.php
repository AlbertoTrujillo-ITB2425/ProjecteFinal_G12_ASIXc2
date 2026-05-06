<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/core/db.php';

// Redirección si no hay sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// 1. Obtener datos frescos del usuario
$stmt = $pdo->prepare("SELECT *, DATE_FORMAT(created_at, '%d %b %Y') as member_since FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// 2. Obtener historial de escaneos (Ajusta los nombres de columnas según tu DB)
// He añadido un try-catch por si la tabla 'scans' aún no existe
try {
    $stmtScans = $pdo->prepare("SELECT target, type, status, created_at FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT 6");
    $stmtScans->execute([$_SESSION['user_id']]);
    $lastScans = $stmtScans->fetchAll();
} catch (Exception $e) {
    $lastScans = []; // Si falla, mostramos lista vacía
}

$userName  = $user['name'] ?? 'Auditor';
$userEmail = $user['email'] ?? 'No disponible';
$userRole  = $user['role'] ?? 'user';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true&size=128";
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOC | Perfil de <?= htmlspecialchars($userName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .glass-panel { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); }
        .tab-btn.active { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border-left: 3px solid #0ea5e9; }
        .scan-row:hover { background: rgba(255,255,255,0.03); }
    </style>
</head>

<body class="bg-[#020617] text-slate-300 min-h-screen">

    <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-10">

        <div class="glass-panel rounded-[2.5rem] p-8 mb-8 flex flex-wrap items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="relative">
                    <img src="<?= $avatarUrl ?>" id="header-avatar" class="w-24 h-24 rounded-3xl border-2 border-sky-500/30">
                    <span class="absolute bottom-1 right-1 w-5 h-5 bg-green-500 border-4 border-[#020617] rounded-full"></span>
                </div>
                <div>
                    <h2 id="header-name" class="text-3xl font-black text-white uppercase tracking-tight"><?= htmlspecialchars($userName) ?></h2>
                    <p class="text-sky-500 text-[10px] font-black uppercase tracking-[0.2em] opacity-80">System Auditor // Tier 1</p>
                </div>
            </div>
            
            <div class="flex gap-4">
                <div class="bg-white/5 p-4 rounded-2xl text-center min-w-[100px]">
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Escaneos</p>
                    <p class="text-xl font-black text-white"><?= count($lastScans) ?></p>
                </div>
                <div class="bg-white/5 p-4 rounded-2xl text-center min-w-[100px]">
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Rol</p>
                    <p class="text-xl font-black text-sky-400 uppercase"><?= $userRole ?></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">

            <aside class="col-span-12 lg:col-span-3 space-y-4">
                <div class="glass-panel rounded-3xl p-3 flex flex-col gap-1">
                    <button data-tab="personal" class="tab-btn active w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all">
                        <i class="fas fa-user-circle text-lg"></i> Mi Perfil
                    </button>
                    <button data-tab="history" class="tab-btn w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all">
                        <i class="fas fa-radar text-lg"></i> Historial SOC
                    </button>
                    <button data-tab="security" class="tab-btn w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all">
                        <i class="fas fa-shield-halved text-lg"></i> Seguridad
                    </button>
                    <button data-tab="web3" class="tab-btn w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all text-purple-400">
                        <i class="fas fa-wallet text-lg"></i> Web3 Link
                    </button>
                </div>

                <a href="core/auth_handler.php?action=logout" class="block w-full text-center p-4 rounded-2xl bg-red-500/10 hover:bg-red-500/20 text-red-500 text-[10px] font-black uppercase tracking-widest transition-all border border-red-500/20">
                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                </a>
            </aside>

            <section class="col-span-12 lg:col-span-9">

                <div id="content-personal" class="tab-content glass-panel rounded-3xl p-10">
                    <h3 class="text-xl font-black text-white uppercase mb-8">Información General</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Nombre Público</label>
                            <input type="text" id="input-name" value="<?= htmlspecialchars($userName) ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-sky-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Dirección de Email</label>
                            <input type="text" value="<?= htmlspecialchars($userEmail) ?>" disabled class="w-full bg-black/40 border border-white/5 rounded-xl px-4 py-3 text-slate-600 cursor-not-allowed">
                        </div>
                    </div>
                    <button onclick="saveProfile()" id="btn-save-profile" class="mt-8 bg-sky-600 hover:bg-sky-500 text-white font-black px-10 py-4 rounded-2xl text-[10px] uppercase tracking-widest transition-all">
                        Guardar Cambios
                    </button>
                </div>

                <div id="content-history" class="tab-content hidden glass-panel rounded-3xl p-10">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-xl font-black text-white uppercase">Últimos Escaneos</h3>
                        <span class="text-[10px] font-bold text-slate-500 uppercase">Tiempo Real <i class="fas fa-circle text-green-500 animate-pulse ml-1"></i></span>
                    </div>
                    
                    <div class="space-y-3">
                        <?php if (!empty($lastScans)): ?>
                            <?php foreach ($lastScans as $scan): ?>
                                <div class="scan-row flex items-center justify-between p-4 rounded-2xl border border-white/5 bg-white/[0.02] transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-sky-500/10 flex items-center justify-center text-sky-500">
                                            <i class="fas fa-terminal"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-white"><?= htmlspecialchars($scan['target']) ?></p>
                                            <p class="text-[10px] text-slate-500 uppercase"><?= htmlspecialchars($scan['type']) ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-mono text-slate-400"><?= $scan['created_at'] ?></p>
                                        <span class="text-[9px] px-2 py-0.5 rounded bg-green-500/10 text-green-500 border border-green-500/20 uppercase font-bold">Success</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-10 opacity-50">
                                <i class="fas fa-folder-open text-4xl mb-4"></i>
                                <p class="text-xs uppercase font-bold">No hay registros en la base de datos</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="content-security" class="tab-content hidden glass-panel rounded-3xl p-10">
                    <h3 class="text-xl font-black text-white uppercase mb-8">Gestión de Credenciales</h3>
                    <div class="max-w-md space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Nueva Contraseña</label>
                            <input type="password" id="new-pass" placeholder="••••••••" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-red-500 transition-all">
                        </div>
                        <button onclick="updatePassword()" class="bg-white/10 hover:bg-red-500/20 hover:text-red-500 text-white font-black px-8 py-3 rounded-xl text-[10px] uppercase tracking-widest transition-all border border-white/10">
                            Actualizar Password
                        </button>
                    </div>
                </div>

                <div id="content-web3" class="tab-content hidden glass-panel rounded-[2.5rem] p-12 border-purple-500/20 text-center">
                    <img src="https://cryptologos.cc/logos/solana-sol-logo.png" class="w-16 h-16 mx-auto mb-6">
                    <h3 class="text-2xl font-black text-white uppercase mb-2">Web3 Identity</h3>
                    <p class="text-slate-500 text-sm mb-8">Vincula tu cuenta con Phantom Wallet para firma de auditorías.</p>
                    <button class="bg-purple-600 hover:bg-purple-500 text-white font-black px-10 py-4 rounded-2xl shadow-xl shadow-purple-500/20 transition-all uppercase text-xs tracking-widest">
                        Conectar Wallet
                    </button>
                </div>

            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // LÓGICA DE TABS
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const target = btn.getAttribute('data-tab');
                document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
                document.getElementById('content-' + target).classList.remove('hidden');
            });
        });

        // AJAX: GUARDAR NOMBRE
        async function saveProfile() {
            const nameInput = document.getElementById('input-name');
            const btn = document.getElementById('btn-save-profile');
            const originalText = btn.innerText;

            btn.innerText = "PROCESANDO...";
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'update_name');
            formData.append('name', nameInput.value);

            try {
                const response = await fetch('core/profile_actions.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    document.getElementById('header-name').innerText = nameInput.value;
                    alert("Perfil actualizado correctamente");
                } else {
                    alert("Error: " + result.message);
                }
            } catch (error) {
                alert("Error de conexión con el servidor");
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
