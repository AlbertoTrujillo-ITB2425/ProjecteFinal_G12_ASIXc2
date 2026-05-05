<?php
// Sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos
require_once __DIR__ . '/core/db.php';

// Redirección si no hay login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// Datos del usuario
$userName  = $_SESSION['user_name'] ?? 'Auditor';
$userEmail = $_SESSION['user_email'] ?? 'No disponible';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true&size=128";
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Auditor | SOC G12</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Global -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- JS Global -->
    <script src="assets/js/main.js" defer></script>
    <script src="assets/js/profile.js" defer></script>
</head>

<body class="bg-darkBg text-slate-300 min-h-screen">

    <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-12">

        <!-- Perfil -->
        <div class="flex items-center gap-6 mb-12">
            <div class="relative group">
                <img src="<?= $avatarUrl ?>" id="avatar-preview" class="w-24 h-24 rounded-3xl border-4 border-sky-500/20 shadow-2xl">
                <input type="file" id="avatar-input" class="hidden">
            </div>

            <div>
                <h2 class="text-3xl font-black text-white uppercase tracking-tight"><?= $userName ?></h2>
                <p class="text-sky-500 font-bold uppercase text-[10px] tracking-widest">G12 Root Auditor</p>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">

            <!-- Sidebar -->
            <aside class="col-span-12 lg:col-span-3 space-y-3">
                <div class="glass-panel rounded-3xl p-4 flex flex-col gap-2">

                    <button data-tab="personal" class="tab-btn active w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all">
                        <i class="fas fa-id-card"></i> Datos Personales
                    </button>

                    <button data-tab="solana" class="tab-btn w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all text-purple-400">
                        <i class="fas fa-wallet"></i> Web3 Pay
                    </button>

                    <button onclick="toggleTheme()" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all mt-4">
                        <i id="theme-icon" class="fas fa-sun text-amber-400"></i> Cambiar Tema
                    </button>

                </div>
            </aside>

            <!-- Contenido -->
            <section class="col-span-12 lg:col-span-9">

                <!-- Datos personales -->
                <div id="content-personal" class="tab-content glass-panel rounded-3xl p-10">
                    <h3 class="text-xl font-black text-white uppercase mb-8">Datos del Auditor</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Nombre</label>
                            <input type="text" value="<?= $userName ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-sky-500 transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Email</label>
                            <input type="text" value="<?= $userEmail ?>" disabled class="w-full bg-black/40 border border-white/5 rounded-xl px-4 py-3 text-slate-500 cursor-not-allowed">
                        </div>

                    </div>
                </div>

                <!-- Web3 -->
                <div id="content-solana" class="tab-content hidden glass-panel rounded-3xl p-10 border-purple-500/20">

                    <div class="flex justify-between items-center mb-10">
                        <h3 class="text-xl font-black text-white uppercase">Web3 Authentication</h3>
                        <img src="https://cryptologos.cc/logos/solana-sol-logo.png" class="w-8 h-8">
                    </div>

                    <div class="bg-black/30 p-8 rounded-3xl border border-white/5 text-center">
                        <div id="status-card" class="text-xs text-slate-500 mb-6 italic">
                            Esperando firma de wallet...
                        </div>

                        <button id="wallet-btn" onclick="web3Login()" class="bg-purple-600 hover:bg-purple-500 text-white font-black px-10 py-4 rounded-2xl shadow-xl shadow-purple-500/20 transition-all uppercase text-xs tracking-widest flex items-center gap-3 mx-auto">
                            <i class="fas fa-key"></i> <span id="wallet-text">LOGIN CON PHANTOM</span>
                        </button>
                    </div>

                </div>

            </section>

        </div>

    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
