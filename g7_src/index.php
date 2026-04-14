<?php
session_start();
if (!isset($_SESSION['visits'])) $_SESSION['visits'] = 0;
$_SESSION['visits']++;

echo "<html><head><script src='https://cdn.tailwindcss.com'></script></head>";
echo "<body class='bg-slate-900 text-white flex items-center justify-center h-screen'>";
echo "<div class='p-8 bg-slate-800 rounded-xl shadow-2xl border border-blue-500'>";
echo "<h1 class='text-3xl font-bold text-blue-400 mb-4'>CyberAudit / G7 Status</h1>";
echo "<p class='text-lg'>Sesión en Redis activa. Visitas en esta sesión: <span class='text-yellow-400'>" . $_SESSION['visits'] . "</span></p>";
echo "<div class='mt-4 text-sm text-slate-400 italic font-mono'>Stack: Nginx + PHP-FPM + Redis + MariaDB + LDAP</div>";
echo "</div></body></html>";
