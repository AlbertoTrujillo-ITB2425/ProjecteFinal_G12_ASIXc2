<?php
session_start();
require 'db_conn.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {

        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Credenciales incorrectas";
        }

    } else {
        $error = "Faltan datos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login | CyberPYME SOC</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-950 flex items-center justify-center h-screen">

<div class="w-[420px] bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-xl">

    <h1 class="text-3xl font-bold text-sky-400 text-center mb-6">
        CyberPYME SOC
    </h1>

    <p class="text-slate-400 text-center mb-6 text-sm">
        Acceso al sistema de auditoría y monitoreo
    </p>

    <?php if($error): ?>
        <div class="bg-red-500/10 border border-red-500 text-red-400 p-2 rounded mb-4 text-sm text-center">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input type="email" name="email" placeholder="Email"
            class="w-full mb-3 p-3 bg-slate-800 border border-slate-700 rounded-lg text-white">

        <input type="password" name="password" placeholder="Password"
            class="w-full mb-4 p-3 bg-slate-800 border border-slate-700 rounded-lg text-white">

        <button class="w-full bg-sky-500 hover:bg-sky-600 transition p-3 rounded-lg font-bold">
            Iniciar sesión
        </button>

    </form>

    <a href="signup.php"
       class="block text-center text-sm text-slate-400 mt-4 hover:text-sky-400">
        Crear cuenta
    </a>

</div>

</body>
</html>
