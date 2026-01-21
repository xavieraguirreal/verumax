<?php
/**
 * Detección Automática de Institución - CERTIFICATUM
 * Detecta la institución basándose en el subdominio actual
 *
 * Uso: require_once 'autodetect.php'; al inicio de archivos proxy
 */

// Cargar config si no está cargado (necesario para InstitutionService)
if (!class_exists('VERUMax\Services\InstitutionService')) {
    require_once __DIR__ . '/config.php';
}

use VERUMax\Services\InstitutionService;

/**
 * Detecta automáticamente la institución basándose en el subdominio
 * Consulta BD dinámicamente, con fallback a lista hardcodeada
 *
 * @return string|null Código de la institución o null si es dominio principal
 */
function detectarInstitucion() {
    $host = $_SERVER['HTTP_HOST'] ?? '';

    // Dominios principales (no son instituciones) - siempre hardcodeado
    $dominios_principales = [
        'www.verumax.com',
        'verumax.com',
        'certificatum.verumax.com',
        'localhost',
        '127.0.0.1',
    ];

    if (in_array($host, $dominios_principales)) {
        return null;
    }

    // Intentar obtener mapeo dinámico de BD
    try {
        $instituciones = InstitutionService::listAll();
        if (!empty($instituciones)) {
            foreach ($instituciones as $inst) {
                $slug = $inst['slug'];
                // Verificar dominio personalizado
                if (!empty($inst['dominio']) && $inst['dominio'] === $host) {
                    return $slug;
                }
                // Verificar patrón subdominio.verumax.com
                if ($host === $slug . '.verumax.com') {
                    return $slug;
                }
                // Verificar patrón subdominio.verumax.local (desarrollo)
                if ($host === $slug . '.verumax.local') {
                    return $slug;
                }
            }
        }
    } catch (\Exception $e) {
        error_log("detectarInstitucion: Error consultando BD - " . $e->getMessage());
    }

    // Fallback: mapeo hardcodeado (solo si falla BD)
    $mapeo_fallback = [
        'sajur.verumax.com' => 'sajur',
        'sajur.verumax.local' => 'sajur',
    ];

    return $mapeo_fallback[$host] ?? null;
}

/**
 * Obtiene la URL base de la institución actual
 *
 * @return string URL base (ej: https://sajur.verumax.com)
 */
function obtenerURLBaseInstitucion() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . $host;
}

/**
 * Verifica si la petición actual es de una institución
 *
 * @return bool True si es un subdominio de institución
 */
function esSubdominioInstitucion() {
    return detectarInstitucion() !== null;
}

// Auto-configurar institución si viene de subdominio
// y no se ha especificado manualmente en GET/POST
$institucion_auto = detectarInstitucion();

if ($institucion_auto && !isset($_GET['institutio']) && !isset($_POST['institutio'])) {
    $_GET['institutio'] = $institucion_auto;
    $_POST['institutio'] = $institucion_auto;
}

// Definir constantes útiles
if ($institucion_auto) {
    define('INSTITUCION_ACTUAL', $institucion_auto);
    define('URL_BASE_INSTITUCION', obtenerURLBaseInstitucion());
}
?>
