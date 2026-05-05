<?php
/**
 * CYBERPYME SOC - Sistema Integral de Gestión de Identidades (v3.0)
 * Módulos: Acceso, Registro, Recuperación y SSO
 */
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errorMsg = '';
$successMsg = '';

if (isset($_GET['error'])) {
    $errorMsg = match($_GET['error']) {
        'invalid_credentials' => 'Credenciales no autorizadas en el dominio SOC.',
        'user_exists'         => 'El identificador ya existe en la base de datos.',
        'ldap_failed'         => 'Fallo crítico de comunicación con s6_openldap.',
        'oauth_error'         => 'Error en la validación del proveedor SSO.',
        'weak_password'       => 'La clave no cumple los requisitos (min. 8 caracteres).',
        'mail_failed'         => 'Error en el servicio de correo s10_postfix.',
        default               => 'Error en el motor de autenticación.'
    };
}

if (isset($_GET['success'])) {
    $successMsg = match($_GET['success']) {
        'registered'       => 'Cuenta de auditor creada. Pendiente de activación.',
        'reset_sent'       => 'Instrucciones de recuperación enviadas vía s10_postfix.',
        'password_updated' => 'Clave actualizada correctamente. Ya puede conectar.',
        default            => 'Operación completada con éxito.'
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOC Gateway | CYBERPYME G12</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #020617; }
        .glass-panel { 
            background: rgba(15, 23, 42, 0.7); 
            border: 1px solid rgba(14, 165, 233, 0.2); 
            backdrop-filter: blur(16px); 
            border-radius: 1.5rem; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .bg-glow { 
            background: radial-gradient(circle at top, rgba(14, 165, 233, 0.15), transparent 60%); 
        }
        .input-soc { 
            background: rgba(2, 6, 23, 0.5); 
            border: 1px solid rgba(51, 65, 85, 0.5); 
            color: #f8fafc; 
            transition: all 0.3s ease; 
        }
        .input-soc:focus { 
            border-color: #0ea5e9; 
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.2); 
            outline: none; 
            background: rgba(2, 6, 23, 0.8);
        }
        .form-section { transition: opacity 0.4s ease, transform 0.4s ease; }
        .hidden-mode { display: none; opacity: 0; transform: translateY(10px); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center text-slate-200 p-4 relative overflow-hidden">

    <div class="bg-glow absolute inset-0 z-0 pointer-events-none"></div>

    <div class="glass-panel p-8 w-full max-w-md relative z-10">

        <!-- Header -->
        <div class="text-center mb-8">
            <div id="header-icon" class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-900 border border-sky-500/30 mb-5 transition-colors duration-500 shadow-lg shadow-sky-500/20">
                <i class="fas fa-fingerprint text-3xl text-sky-400"></i>
            </div>
            <h1 class="text-2xl font-black tracking-widest uppercase text-white">CyberPyme <span class="text-sky-500">SOC</span></h1>
            <p id="mode-title" class="text-xs text-slate-400 uppercase tracking-[0.3em] mt-2 font-semibold">Gateway de Acceso</p>
        </div>

        <!-- Alertas -->
        <?php if ($errorMsg): ?>
            <div class="bg-red-950/50 border border-red-500/30 text-red-400 text-xs p-3.5 rounded-lg mb-6 flex items-center gap-3 animate-pulse">
                <i class="fas fa-triangle-exclamation"></i><span><?= $errorMsg ?></span>
            </div>
        <?php endif; ?>
        <?php if ($successMsg): ?>
            <div class="bg-emerald-950/50 border border-emerald-500/30 text-emerald-400 text-xs p-3.5 rounded-lg mb-6 flex items-center gap-3">
                <i class="fas fa-shield-check"></i><span><?= $successMsg ?></span>
            </div>
        <?php endif; ?>

        <!-- Formulario Login -->
        <form id="form-login" action="core/auth_handler.php" method="POST" class="space-y-5 form-section">
            <input type="hidden" name="action" value="login_standard">
            
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Identificador</label>
                <div class="relative">
                    <i class="fas fa-user-astronaut absolute left-3.5 top-3.5 text-slate-500 text-sm"></i>
                    <input type="text" name="username" class="input-soc w-full rounded-xl py-3 pl-10 pr-4 text-sm" placeholder="auditor@cyberpyme.es" required>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-1.5 ml-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Clave Maestra</label>
                    <a href="reset_password.php" class="text-[10px] text-sky-400 hover:text-sky-300 transition-colors">¿Recuperar acceso?</a>
                </div>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3.5 top-3.5 text-slate-500 text-sm"></i>
                    <input type="password" name="password" class="input-soc w-full rounded-xl py-3 pl-10 pr-4 text-sm" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-sky-600 hover:bg-sky-500 text-white font-black text-xs uppercase tracking-[0.2em] rounded-xl shadow-lg shadow-sky-600/30 transition-all hover:scale-[1.02] active:scale-95 mt-2">
                Autorizar Conexión
            </button>
        </form>

        <!-- Formulario Registro -->
        <form id="form-register" action="core/auth_handler.php" method="POST" class="space-y-5 form-section hidden-mode">
            <input type="hidden" name="action" value="register_user">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nombre</label>
                    <input type="text" name="name" class="input-soc w-full rounded-xl py-3 px-4 text-sm" placeholder="John" required>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Apellidos</label>
                    <input type="text" name="lastname" class="input-soc w-full rounded-xl py-3 px-4 text-sm" placeholder="Doe" required>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Email Corporativo</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3.5 top-3.5 text-slate-500 text-sm"></i>
                    <input type="email" name="email" class="input-soc w-full rounded-xl py-3 pl-10 pr-4 text-sm" placeholder="j.doe@cyberpyme.es" required>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Credencial de Acceso</label>
                <div class="relative">
                    <i class="fas fa-key absolute left-3.5 top-3.5 text-slate-500 text-sm"></i>
                    <input type="password" name="password" class="input-soc w-full rounded-xl py-3 pl-10 pr-4 text-sm" placeholder="Mínimo 8 caracteres" required>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-[0.2em] rounded-xl shadow-lg shadow-emerald-600/30 transition-all hover:scale-[1.02] active:scale-95 mt-2">
                Registrar Agente
            </button>
        </form>

        <!-- SSO Section -->
        <div class="mt-8 pt-2">
            <div class="relative flex justify-center text-[10px] uppercase tracking-[0.2em] mb-5">
                <span class="bg-slate-900/80 px-4 text-slate-500 font-bold relative z-10 rounded-full border border-slate-800">Proveedores SSO</span>
                <div class="absolute inset-0 top-1/2 border-t border-slate-800/80"></div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <a href="core/auth_handler.php?provider=google" class="group flex justify-center py-3 bg-slate-900/50 border border-slate-700/50 rounded-xl hover:border-sky-500/50 hover:bg-slate-800 transition-all">
                    <i class="fab fa-google text-slate-400 group-hover:text-white transition-colors"></i>
                </a>
                <a href="core/auth_handler.php?provider=microsoft" class="group flex justify-center py-3 bg-slate-900/50 border border-slate-700/50 rounded-xl hover:border-sky-500/50 hover:bg-slate-800 transition-all">
                    <i class="fab fa-windows text-slate-400 group-hover:text-white transition-colors"></i>
                </a>
                <a href="core/auth_handler.php?provider=amazon" class="group flex justify-center py-3 bg-slate-900/50 border border-slate-700/50 rounded-xl hover:border-sky-500/50 hover:bg-slate-800 transition-all">
                    <i class="fab fa-amazon text-slate-400 group-hover:text-white transition-colors"></i>
                </a>
            </div>
        </div>

        <!-- Toggle Switch -->
        <div class="mt-8 pt-5 border-t border-slate-800 text-center">
            <p class="text-xs text-slate-400">
                <span id="switch-text">¿No dispone de credenciales?</span>
                <button type="button" onclick="toggleMainMode()" id="switch-btn" class="text-sky-400 font-bold ml-2 hover:text-sky-300 transition-colors">Solicitar Registro</button>
            </p>
        </div>
    </div>

    <script>
        function toggleMainMode() {
            const loginForm = document.getElementById('form-login');
            const regForm = document.getElementById('form-register');
            const isLogin = !loginForm.classList.contains('hidden-mode');

            const btn = document.getElementById('switch-btn');
            const text = document.getElementById('switch-text');
            const modeTitle = document.getElementById('mode-title');
            const headerIcon = document.getElementById('header-icon');

            if(isLogin) {
                // Cambiar a Registro
                loginForm.style.opacity = '0';
                setTimeout(() => {
                    loginForm.classList.add('hidden-mode');
                    regForm.classList.remove('hidden-mode');
                    setTimeout(() => regForm.style.opacity = '1', 50);
                }, 300);

                btn.innerText = 'Iniciar Sesión';
                text.innerText = '¿Ya dispone de credenciales?';
                modeTitle.innerText = 'Alta de Nuevo Agente';
                
                headerIcon.classList.replace('border-sky-500/30', 'border-emerald-500/30');
                headerIcon.classList.replace('shadow-sky-500/20', 'shadow-emerald-500/20');
                headerIcon.innerHTML = '<i class="fas fa-user-shield text-3xl text-emerald-400"></i>';
            } else {
                // Cambiar a Login
                regForm.style.opacity = '0';
                setTimeout(() => {
                    regForm.classList.add('hidden-mode');
                    loginForm.classList.remove('hidden-mode');
                    setTimeout(() => loginForm.style.opacity = '1', 50);
                }, 300);

                btn.innerText = 'Solicitar Registro';
                text.innerText = '¿No dispone de credenciales?';
                modeTitle.innerText = 'Gateway de Acceso';
                
                headerIcon.classList.replace('border-emerald-500/30', 'border-sky-500/30');
                headerIcon.classList.replace('shadow-emerald-500/20', 'shadow-sky-500/20');
                headerIcon.innerHTML = '<i class="fas fa-fingerprint text-3xl text-sky-400"></i>';
            }
        }
    </script>
</body>
</html>
