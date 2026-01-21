<?php
/**
 * CARGADOR DE CONFIGURACIÓN DE ENTORNO
 *
 * Este archivo lee el archivo .env y configura automáticamente
 * todas las conexiones de base de datos.
 *
 * USO:
 * 1. Renombrar .env.local a .env para trabajar en local
 * 2. Renombrar .env.remoto a .env para trabajar en remoto
 * 3. Incluir este archivo en tus scripts: require_once 'env_loader.php';
 */

// Cargar bootstrap de servicios
require_once __DIR__ . '/src/bootstrap.php';

use VERUMax\Services\DatabaseService;

/**
 * Carga las variables de entorno desde archivo .env
 *
 * @return array Variables de entorno
 */
function loadEnv($file = '.env') {
    $path = __DIR__ . '/' . $file;

    if (!file_exists($path)) {
        throw new RuntimeException(
            "Archivo .env no encontrado en: {$path}\n\n" .
            "INSTRUCCIONES:\n" .
            "1. Para trabajar en LOCAL: renombrar .env.local a .env\n" .
            "2. Para trabajar en REMOTO: renombrar .env.remoto a .env\n"
        );
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parsear línea
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remover comillas si existen
            $value = trim($value, '"\'');

            $env[$key] = $value;
        }
    }

    return $env;
}

// Cargar variables de entorno
$env = loadEnv();

// Mostrar modo de trabajo (solo en modo debug)
if (isset($env['APP_DEBUG']) && $env['APP_DEBUG'] === 'true') {
    $mode = strtoupper($env['APP_ENV'] ?? 'unknown');
    error_log("VERUMax - Modo: {$mode}");
}

// =============================================================================
// CONFIGURAR CONEXIONES DE BASE DE DATOS
// =============================================================================

// Conexión: verumax_general
DatabaseService::configure('general', [
    'host' => $env['GENERAL_DB_HOST'] ?? 'localhost',
    'user' => $env['GENERAL_DB_USER'] ?? 'root',
    'password' => $env['GENERAL_DB_PASS'] ?? '',
    'database' => $env['GENERAL_DB_NAME'] ?? 'verumax_general',
]);

// Conexión: verumax_certifi (Certificatum)
DatabaseService::configure('certificatum', [
    'host' => $env['CERTIFI_DB_HOST'] ?? 'localhost',
    'user' => $env['CERTIFI_DB_USER'] ?? 'root',
    'password' => $env['CERTIFI_DB_PASS'] ?? '',
    'database' => $env['CERTIFI_DB_NAME'] ?? 'verumax_certifi',
]);

// Conexión: verumax_identi (Identitas)
DatabaseService::configure('identitas', [
    'host' => $env['IDENTI_DB_HOST'] ?? 'localhost',
    'user' => $env['IDENTI_DB_USER'] ?? 'root',
    'password' => $env['IDENTI_DB_PASS'] ?? '',
    'database' => $env['IDENTI_DB_NAME'] ?? 'verumax_identi',
]);

// Conexión: verumax_nexus (Gestor Nexus - MMS)
DatabaseService::configure('nexus', [
    'host' => $env['NEXUS_DB_HOST'] ?? 'localhost',
    'user' => $env['NEXUS_DB_USER'] ?? 'root',
    'password' => $env['NEXUS_DB_PASS'] ?? '',
    'database' => $env['NEXUS_DB_NAME'] ?? 'verumax_nexus',
]);

// Conexión: verumax_academi (Academicus - Gestión de cursos)
DatabaseService::configure('academicus', [
    'host' => $env['ACADEMI_DB_HOST'] ?? 'localhost',
    'user' => $env['ACADEMI_DB_USER'] ?? 'root',
    'password' => $env['ACADEMI_DB_PASS'] ?? '',
    'database' => $env['ACADEMI_DB_NAME'] ?? 'verumax_academi',
]);

// =============================================================================
// FUNCIONES HELPER PARA ACCESO A VARIABLES DE ENTORNO
// =============================================================================

/**
 * Obtiene una variable de entorno
 *
 * @param string $key Clave
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function env($key, $default = null) {
    global $env;
    return $env[$key] ?? $default;
}

/**
 * Verifica si estamos en entorno local
 *
 * @return bool
 */
function isLocal() {
    return env('APP_ENV') === 'local';
}

/**
 * Verifica si estamos en producción
 *
 * @return bool
 */
function isProduction() {
    return env('APP_ENV') === 'production';
}

/**
 * Verifica si el modo debug está activo
 *
 * @return bool
 */
function isDebug() {
    return env('APP_DEBUG') === 'true';
}

// =====================================================
// CARGAR HELPERS DE URL
// =====================================================
require_once __DIR__ . '/includes/url_helper.php';

?>
