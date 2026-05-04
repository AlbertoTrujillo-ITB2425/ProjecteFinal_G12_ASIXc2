<?php
/**
 * CYBERPYME SOC - Portal de Autenticación G12
 * Integra: Local (MariaDB), OpenLDAP y OAuth2 (Google, MS, Amazon)
 */
session_start();

// Si el usuario ya está logueado, lo mandamos directo al dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Capturar errores enviados por auth_handler.php
$errorMsg = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_credentials':
            $errorMsg = 'Credenciales incorrectas o acceso denegado por políticas de red.';
            break;
        case 'ldap_failed':
            $errorMsg = 'Error de conexión con el servidor LDAP (s6_openldap).';
            break;
        case 'oauth_error':
            $errorMsg = 'Error en la validación del proveedor SSO.';
            break;
        default:
            $errorMsg = 'Error desconocido en el motor de autenticación.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Auditor | CYBERPYME SOC G12</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .glass-panel { 
            background: rgba(15, 23, 42, 0.7); 
            border: 1px solid rgba(14, 165, 233, 0.2); 
            backdrop-filter: blur(16px); 
            border-radius: 1rem; 
        }
        .bg-glow { 
            background: radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.15) 0%, #020617 100%); 
        }
        .input-soc {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        .input-soc:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 10px rgba(14, 165, 233, 0.2);
            outline: none;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-950 text-white relative overflow-hidden">

    <div class="fixed inset-0 pointer-events-none opacity-[0.03] bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
    <div class="bg-glow absolute inset-0 z-0"></div>

    <div class="glass-panel p-8 md:p-10 w-full max-w-md relative z-10 shadow-2xl shadow-sky-900/20 transform transition-all">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-sky-500/10 border border-sky-500/30 mb-4">
                <i class="fas fa-shield-halved text-3xl text-sky-400"></i>
            </div>
            <h1 class="text-2xl font-black tracking-widest">CYBERPYME <span class="text-sky-500">G12</span></h1>
            <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-2 border-b border-white/5 pb-4">Terminal de Autenticación SOC</p>
        </div>

        <?php if ($errorMsg): ?>
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded mb-6 flex items-start gap-2">
            <i class="fas fa-exclamation-triangle mt-0.5"></i>
            <span><?php echo htmlspecialchars($errorMsg); ?></span>
        </div>
        <?php endif; ?>

        <form action="auth_handler.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Identidad (LDAP / Local)</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-3 text-slate-500 text-sm"></i>
                    <input type="text" name="username" class="input-soc w-full rounded py-2 pl-10 pr-3 text-sm" placeholder="ID de Auditor o Email" required autocomplete="username">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Clave Cifrada</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-3 text-slate-500 text-sm"></i>
                    <input type="password" name="password" class="input-soc w-full rounded py-2 pl-10 pr-3 text-sm" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <input type="hidden" name="action" value="login_standard">

            <button type="submit" class="w-full py-3 mt-2 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs uppercase tracking-widest rounded transition-all shadow-[0_0_15px_rgba(14,165,233,0.4)] flex justify-center items-center gap-2">
                <i class="fas fa-terminal"></i> Iniciar Conexión Segura
            </button>
        </form>

        <div class="mt-8 relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-700"></div>
            </div>
            <div class="relative flex justify-center text-[10px] uppercase tracking-widest">
                <span class="bg-[#0b1120] px-3 text-slate-500">SSO & Web3</span>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-3 gap-3">
            <a href="auth_handler.php?provider=google" class="flex justify-center items-center py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-500 rounded transition-colors text-white group" title="Google Workspace">
                <i class="fab fa-google group-hover:text-red-400 transition-colors"></i>
            </a>
            <a href="auth_handler.php?provider=microsoft" class="flex justify-center items-center py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-500 rounded transition-colors text-white group" title="Microsoft Entra ID">
                <i class="fab fa-windows group-hover:text-sky-400 transition-colors"></i>
            </a>
            <a href="auth_handler.php?provider=amazon" class="flex justify-center items-center py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-500 rounded transition-colors text-white group" title="AWS Cognito">
                <i class="fab fa-amazon group-hover:text-amber-400 transition-colors"></i>
            </a>
        </div>
        
        <div class="mt-3">
            <button type="button" onclick="alert('Inicializando puente MetaMask...')" class="w-full py-2 bg-slate-800/50 hover:bg-emerald-900/30 border border-emerald-500/20 hover:border-emerald-500/50 text-emerald-500/70 hover:text-emerald-400 rounded transition-all text-xs font-bold uppercase tracking-widest flex justify-center items-center gap-2">
                <i class="fab fa-ethereum"></i> Autenticación Wallet Web3
            </button>
        </div>

    </div>

    <div class="absolute bottom-4 w-full text-center pointer-events-none text-[10px] text-slate-600 font-mono">
        &copy; 2026 CYBERPYME SOC • ENTORNO DE ALTA SEGURIDAD
    </div>

</body>
</html>
