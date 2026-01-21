<?php
/**
 * VERUMax - Configuración de Modo Mantenimiento
 *
 * Para activar modo mantenimiento: cambiar $MAINTENANCE_MODE = true
 * Para desactivar: cambiar $MAINTENANCE_MODE = false
 *
 * Acceso durante mantenimiento: agregar ?admin a la URL
 * Ejemplo: https://verumax.com/index.php?admin
 */

// ========================================
// CONFIGURACIÓN PRINCIPAL
// ========================================

// true = Sitio en mantenimiento | false = Sitio normal
// SOLO para verumax.com (no subdominios)
$MAINTENANCE_MODE = true;

// Clave secreta para acceso (sin usar por ahora, solo ?admin)
$ADMIN_ACCESS_KEY = 'admin';

// ========================================
// FUNCIÓN DE VERIFICACIÓN
// ========================================

/**
 * Verifica si el sitio está en modo mantenimiento
 * y si el usuario tiene acceso de admin
 */
function check_maintenance_mode() {
    global $MAINTENANCE_MODE, $ADMIN_ACCESS_KEY;

    // EXCEPCIÓN: NO aplicar mantenimiento a subdominios (sajur.verumax.com, etc)
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $is_subdomain = strpos($host, '.verumax.com') !== false && $host !== 'verumax.com' && $host !== 'www.verumax.com';

    if ($is_subdomain) {
        return; // Permitir acceso a subdominios
    }

    // Si no está en mantenimiento, continuar normal
    if (!$MAINTENANCE_MODE) {
        return;
    }

    // Verificar si tiene acceso admin via GET
    if (isset($_GET['admin'])) {
        // Guardar en sesión para no tener que poner ?admin en cada página
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['admin_access'] = true;
        return;
    }

    // Verificar si ya tiene sesión de admin
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['admin_access']) && $_SESSION['admin_access'] === true) {
        return;
    }

    // Si llegó aquí, está en mantenimiento y no tiene acceso
    // Redirigir a página de mantenimiento
    header('Location: maintenance.php');
    exit;
}
?>
