<?php
session_start();
require_once 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$userName  = $_SESSION['user_name'] ?? 'Auditor';
$userEmail = $_SESSION['user_email'] ?? 'No disponible';
$avatarUrl = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=0ea5e9&color=fff&bold=true&size=128";
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración | SOC G12</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        darkBg: '#020617',
                        glass: 'rgba(15, 23, 42, 0.7)'
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.3s ease; }
        
        /* Estilos para compatibilidad con tu main.js */
        body.light-mode { background-color: #f8fafc; color: #1e293b; }
        .light-mode .glass-panel { background: white; border-color: #e2e8f0; color: #1e293b; }
        .light-mode h2, .light-mode h3, .light-mode label { color: #0f172a; }

        .glass-panel { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
        .tab-btn.active { background: rgba(14, 165, 233, 0.15); color: #0ea5e9; border-color: rgba(14, 165, 233, 0.3); }
        .tab-content.hidden { display: none; }
    </style>
</head>
<body class="bg-darkBg text-slate-400 min-h-screen">

    <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-12">
        
        <div class="flex items-center gap-6 mb-12">
            <div class="relative group">
                <img src="<?= $avatarUrl ?>" id="avatar-preview" class="w-24 h-24 rounded-3xl border-4 border-sky-500/20 shadow-2xl">
                <input type="file" id="avatar-input" class="hidden">
            </div>
            <div>
                <h2 class="text-3xl font-black text-white uppercase tracking-tighter" data-i18n="status_identity"><?= $userName ?></h2>
                <p class="text-sky-500 font-bold uppercase text-[10px] tracking-widest">G12 Root Auditor</p>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">
            <aside class="col-span-12 lg:col-span-3 space-y-3">
                <div class="glass-panel rounded-[2rem] p-4 flex flex-col gap-2">
                    <button data-tab="personal" class="tab-btn active w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all border border-transparent">
                        <i class="fas fa-id-card"></i> Personal
                    </button>
                    <button data-tab="solana" class="tab-btn w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all border border-transparent text-purple-500">
                        <i class="fas fa-wallet"></i> Web3 Pay
                    </button>
                    <button id="theme-toggle-btn" onclick="toggleTheme()" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl text-xs font-black uppercase transition-all border border-slate-700/50 mt-4">
                        <i id="theme-icon" class="fas fa-sun text-amber-400"></i> <span data-i18n="params_profile">Cambiar Tema</span>
                    </button>
                </div>
            </aside>

            <section class="col-span-12 lg:col-span-9">
                
                <div id="content-personal" class="tab-content glass-panel rounded-[3rem] p-10">
                    <h3 class="text-xl font-black text-white uppercase mb-8" data-i18n="params_title">Datos del Auditor</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest" data-i18n="params_host">Nombre</label>
                            <input type="text" value="<?= $userName ?>" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white outline-none focus:border-sky-500 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Email</label>
                            <input type="text" value="<?= $userEmail ?>" disabled class="w-full bg-black/40 border border-white/5 rounded-xl px-4 py-3 text-slate-500 cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <div id="content-solana" class="tab-content hidden glass-panel rounded-[3rem] p-10 border-purple-500/20">
                    <div class="flex justify-between items-center mb-10">
                        <h3 class="text-xl font-black text-white uppercase">Web3 Authentication</h3>
                        <img src="https://cryptologos.cc/logos/solana-sol-logo.png" class="w-8 h-8">
                    </div>
                    
                    <div class="bg-black/30 p-8 rounded-3xl border border-white/5 text-center">
                        <div id="status-card" class="text-xs text-slate-500 mb-6 italic" data-i18n="status_awaiting">
                            Esperando firma de wallet...
                        </div>
                        <button id="wallet-btn" onclick="web3Login()" class="bg-purple-600 hover:bg-purple-500 text-white font-black px-10 py-4 rounded-2xl shadow-xl shadow-purple-500/20 transition-all uppercase text-xs tracking-widest flex items-center gap-3 mx-auto">
                            <i class="fas fa-key"></i> <span id="wallet-text" data-i18n="nav_login">LOGIN CON PHANTOM</span>
                        </button>
                    </div>
                </div>

            </section>
        </div>
    </main>

    <footer class="mt-20 py-10 border-t border-white/5 text-center">
        <p class="text-[10px] font-black text-slate-600 uppercase tracking-[0.3em]" data-i18n="footer_rights">
            © 2026 CYBERPYME SOC G12. ASEGURANDO EL FUTURO.
        </p>
    </footer>

    <script src="assets/js/languages.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/profile.js"></script>
</body>
</html>
