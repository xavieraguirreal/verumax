<?php
/**
 * VERUMAX SUPER ADMIN - Configuración
 *
 * Panel de administración interno para gestionar toda la plataforma.
 * Requiere autenticación con 2FA.
 */

// Prevenir acceso directo
if (basename($_SERVER['PHP_SELF']) === 'config.php') {
    http_response_code(403);
    exit('Acceso denegado');
}

// ============================================================================
// CONFIGURACIÓN GENERAL
// ============================================================================

define('VERUMAX_ADMIN_VERSION', '1.0.0');
define('VERUMAX_ADMIN_NAME', 'VERUMax Super Admin');
define('VERUMAX_ADMIN_PATH', __DIR__);
define('VERUMAX_ROOT_PATH', dirname(__DIR__));

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Modo debug (desactivar en producción)
define('VERUMAX_DEBUG', false);

if (VERUMAX_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================================================

define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');

// Base de datos principal (instances, planes, super_admins)
define('DB_GENERAL_NAME', 'verumax_general');
define('DB_GENERAL_USER', 'verumax_admin');
define('DB_GENERAL_PASS', '9BD121wk36210270');

// ============================================================================
// CONFIGURACIÓN DE SESIÓN
// ============================================================================

define('SESSION_NAME', 'verumax_superadmin');
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_SECURE', true);   // Solo HTTPS
define('SESSION_HTTPONLY', true); // No accesible desde JavaScript

// ============================================================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================================================

// Intentos de login antes de bloqueo
define('LOGIN_MAX_ATTEMPTS', 3);
define('LOGIN_LOCKOUT_MINUTES', 15);

// 2FA
define('TOTP_ISSUER', 'VERUMax Admin');
define('TOTP_DIGITS', 6);
define('TOTP_PERIOD', 30);

// CSRF Token
define('CSRF_TOKEN_NAME', 'verumax_csrf');

// ============================================================================
// FUNCIONES HELPER
// ============================================================================

/**
 * Genera un token CSRF
 */
function csrf_token(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Valida token CSRF
 */
function csrf_validate(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) &&
           hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Genera campo hidden con CSRF token
 */
function csrf_field(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . csrf_token() . '">';
}

/**
 * Redirige a una URL
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Muestra mensaje flash
 */
function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Obtiene y limpia mensajes flash
 */
function get_flash(): array {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/**
 * Escapa HTML
 */
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Verifica si el usuario está autenticado completamente (login + 2FA)
 */
function is_authenticated(): bool {
    return isset($_SESSION['superadmin_id']) &&
           isset($_SESSION['superadmin_2fa_verified']) &&
           $_SESSION['superadmin_2fa_verified'] === true;
}

/**
 * Verifica si pasó el primer paso del login (usuario/contraseña)
 */
function is_login_step1_complete(): bool {
    return isset($_SESSION['superadmin_pending_2fa']) &&
           $_SESSION['superadmin_pending_2fa'] === true;
}

/**
 * Requiere autenticación completa
 */
function require_auth(): void {
    if (!is_authenticated()) {
        redirect('login.php');
    }
}

// ============================================================================
// INICIALIZACIÓN
// ============================================================================

// Iniciar sesión con configuración segura
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/verumax-admin/',
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Cargar autoloader de Composer si existe
$composer_autoload = VERUMAX_ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
}

// Cargar clase Database
require_once VERUMAX_ADMIN_PATH . '/classes/Database.php';
