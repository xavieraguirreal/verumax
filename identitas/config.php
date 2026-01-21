<?php
/**
 * IDENTITAS - Configuración Base
 * Sistema VERUMax - Presencia Digital Profesional
 *
 * Configuración de conexión a base de datos para el motor Identitas
 * REFACTORIZADO: Usa DatabaseService para conexiones
 */

// =====================================================
// CARGAR SERVICIOS PSR-4 Y CONFIGURACIÓN DE ENTORNO
// =====================================================
require_once __DIR__ . '/../env_loader.php';

use VERUMax\Services\DatabaseService;
use VERUMax\Services\InstitutionService;

// =====================================================
// CONFIGURACIÓN DE BASES DE DATOS (ya configuradas en env_loader.php)
// =====================================================
// NOTA: Las conexiones ya están configuradas en env_loader.php
// usando las credenciales del archivo .env (local o remoto)
// Las siguientes líneas están comentadas para no sobrescribir la configuración:
/*
DatabaseService::configure('identitas', [
    'host' => 'localhost',
    'user' => 'verumax_identi',
    'password' => '/hPfiYd6xH',
    'database' => 'verumax_identi',
]);

DatabaseService::configure('general', [
    'host' => 'localhost',
    'user' => 'verumax_general',
    'password' => '/hPfiYd6xH',
    'database' => 'verumax_general',
]);
*/

// Constantes legacy (mantener para compatibilidad)
// NOTA: Usar valores desde env() cuando sea posible
if (!defined('DB_HOST')) define('DB_HOST', env('IDENTI_DB_HOST', 'localhost'));
if (!defined('DB_NAME')) define('DB_NAME', env('IDENTI_DB_NAME', 'verumax_identi'));
if (!defined('DB_USER')) define('DB_USER', env('IDENTI_DB_USER', 'root'));
if (!defined('DB_PASS')) define('DB_PASS', env('IDENTI_DB_PASS', ''));
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Configuración de rutas
if (!defined('IDENTITAS_PATH')) define('IDENTITAS_PATH', __DIR__);
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));

// Configuración de sesiones (solo si la sesión no está activa)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 3600);
    session_set_cookie_params(3600);
    session_start();
}

// =====================================================
// FUNCIONES DE CONEXIÓN (refactorizadas)
// =====================================================

/**
 * Obtiene conexión PDO a base de datos Identitas
 * REFACTORIZADO: Usa DatabaseService internamente
 *
 * @return PDO
 */
if (!function_exists('getDBConnection')) {
    function getDBConnection() {
        return DatabaseService::get('identitas');
    }
}

/**
 * Obtiene configuración de una instancia
 * REFACTORIZADO: Usa InstitutionService internamente
 *
 * @param string $slug Slug de la institución
 * @return array|null Configuración combinada o null
 */
if (!function_exists('getInstanceConfig')) {
    function getInstanceConfig($slug) {
        // Usar InstitutionService que ya tiene toda la lógica de 3 tablas
        $config = InstitutionService::getConfig($slug);

        if (!$config) {
            return null;
        }

        // Agregar array 'config' para compatibilidad con código existente
        $config['config'] = [];
        if (!empty($config['sitio_web_oficial'])) {
            $config['config']['sitio_web_oficial'] = $config['sitio_web_oficial'];
        }
        if (!empty($config['email_contacto'])) {
            $config['config']['email_contacto'] = $config['email_contacto'];
        }
        if (!empty($config['mision'])) {
            $config['config']['mision'] = $config['mision'];
        }

        return $config;
    }
}

/**
 * Obtiene las clases CSS apropiadas para el logo según el estilo configurado
 *
 * @param string $logo_estilo El estilo del logo (rectangular, cuadrado, circular, etc.)
 * @param string $size_class Clase de altura (por defecto 'h-12' para 48px)
 * @return string Clases CSS para aplicar al tag <img>
 */
if (!function_exists('getLogoClasses')) {
    function getLogoClasses($logo_estilo = 'rectangular', $size_class = 'h-12') {
        $base_classes = $size_class . ' w-auto object-contain';

        $style_classes = [
            'rectangular' => '',
            'rectangular-rounded' => 'rounded-lg',
            'cuadrado' => 'aspect-square object-cover',
            'cuadrado-rounded' => 'aspect-square object-cover rounded-lg',
            'circular' => 'aspect-square object-cover rounded-full'
        ];

        $style = $style_classes[$logo_estilo] ?? $style_classes['rectangular'];

        return trim($base_classes . ' ' . $style);
    }
}
