<?php
/**
 * CYBERPYME SOC - Authentication Hooks
 * Ubicación: includes/auth_hooks.php
 * 
 * Incluir este archivo en auth.php, login.php, register.php
 */

require_once __DIR__ . '/../modules/email/notifier.php';

/**
 * Hook: Después de login exitoso
 * Llamar desde tu función de validación de login
 */
function onLoginSuccess(array $userData): void {
    // $userData debe contener: username, email, id, role
    SOCNotifier::notifyLogin([
        'username' => $userData['username'] ?? '',
        'email' => $userData['email'] ?? '',
        'success' => true,
    ]);
}

/**
 * Hook: Después de login fallido (opcional - puede generar spam)
 */
function onLoginFailed(string $username, string $ip = null): void {
    $config = require __DIR__ . '/../config/mail.conf.php';
    if (!($config['enabled']['login'] ?? true)) return;
    
    // Solo notificar fallos si hay muchos (posible brute force)
    // Implementar contador en Redis para producción
    SOCNotifier::notifyLogin([
        'username' => $username,
        'email' => '',
        'success' => false,
    ]);
}

/**
 * Hook: Después de crear usuario
 * Llamar desde tu función de registro/creación de usuarios
 */
function onUserCreated(array $userData): void {
    // $userData debe contener: username, email, role
    SOCNotifier::notifyUserCreated([
        'username' => $userData['username'] ?? '',
        'email' => $userData['email'] ?? '',
        'role' => $userData['role'] ?? 'user',
    ]);
}
