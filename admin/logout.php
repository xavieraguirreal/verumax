<?php
/**
 * ADMIN UNIFICADO - Logout
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiar toda la sesión
session_destroy();

// Redirigir al login
header('Location: login.php');
exit;
