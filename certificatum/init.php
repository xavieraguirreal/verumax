<?php
/**
 * Inicialización Común de Certificatum
 * Sistema CERTIFICATUM - VERUMax
 * Versión: 1.0
 *
 * Este archivo centraliza toda la lógica de inicialización común:
 * - Validación de institución (whitelist)
 * - Carga de configuración
 * - Inicialización de idioma
 * - Función helper de traducción
 *
 * Uso en cada archivo:
 *   require_once __DIR__ . '/init.php';
 *   $ctx = initCertificatum();
 *   extract($ctx); // Extrae: $institucion, $instance_config, $current_lang, $t
 */

// Cargar configuración base y autoloader
require_once __DIR__ . '/config.php';

use VERUMax\Services\InstitutionService;
use VERUMax\Services\LanguageService;

/**
 * Lista blanca de instituciones válidas
 * Obtiene dinámicamente de la BD, con fallback a lista hardcodeada
 *
 * @return array Lista de slugs de instituciones válidas
 */
function getInstituciones(): array
{
    static $cached = null;

    if ($cached !== null) {
        return $cached;
    }

    // Intentar obtener de BD
    try {
        $list = InstitutionService::listAll();
        if (!empty($list)) {
            $cached = array_column($list, 'slug');
            return $cached;
        }
    } catch (\Exception $e) {
        error_log("getInstituciones: Error consultando BD - " . $e->getMessage());
    }

    // Fallback: lista hardcodeada (solo si falla BD)
    $cached = [
        'sajur',        // Sociedad Argentina de Justicia Restaurativa
    ];
    return $cached;
}

/**
 * Valida que la institución sea válida (whitelist)
 *
 * @param string|null $institucion Slug de la institución
 * @return bool True si es válida
 */
function isInstitucionValida(?string $institucion): bool
{
    if (empty($institucion)) {
        return false;
    }
    return in_array($institucion, getInstituciones(), true);
}

/**
 * Inicializa el contexto común de Certificatum
 *
 * @param bool $requireInstitucion Si es true, termina con error si no hay institución válida
 * @return array Contexto con: institucion, instance_config, current_lang, t, color_primario
 */
function initCertificatum(bool $requireInstitucion = true): array
{
    // Obtener institución de POST o GET
    $institucion = $_POST['institutio'] ?? $_GET['institutio'] ?? null;

    // Validar con whitelist
    if (!isInstitucionValida($institucion)) {
        if ($requireInstitucion) {
            http_response_code(400);
            die('Error: Institución no especificada o no válida.');
        }
        return [
            'institucion' => null,
            'instance_config' => null,
            'current_lang' => 'es_AR',
            't' => fn($key, $params = [], $default = null) => $default ?? $key,
            'color_primario' => '#2E7D32'
        ];
    }

    // Cargar configuración de la institución
    $instance_config = InstitutionService::getConfig($institucion);
    if (!$instance_config) {
        if ($requireInstitucion) {
            http_response_code(500);
            die('Error: Configuración de institución no encontrada.');
        }
        return [
            'institucion' => $institucion,
            'instance_config' => null,
            'current_lang' => 'es_AR',
            't' => fn($key, $params = [], $default = null) => $default ?? $key,
            'color_primario' => '#2E7D32'
        ];
    }

    // Inicializar servicio de idioma
    $lang_request = $_GET['lang'] ?? null;
    LanguageService::init($institucion, $lang_request);
    $current_lang = LanguageService::getCurrentLang();

    // Función helper de traducción
    $t = fn($key, $params = [], $default = null) =>
        LanguageService::get($key, $params, $default);

    // Color primario (usado frecuentemente)
    $color_primario = $instance_config['color_primario'] ?? '#2E7D32';

    return compact(
        'institucion',
        'instance_config',
        'current_lang',
        'lang_request',
        't',
        'color_primario'
    );
}

/**
 * Obtiene clases CSS dinámicas basadas en el color primario
 *
 * @param string $color_primario Color en formato hex (#RRGGBB)
 * @return array Clases CSS dinámicas
 */
function getCssClasses(string $color_primario): array
{
    return [
        'color_primary_bg' => 'bg-[' . $color_primario . ']',
        'color_primary_text' => 'text-[' . $color_primario . ']',
        'color_primary_border' => 'border-[' . $color_primario . ']',
        'color_primary_hover' => 'hover:bg-[' . $color_primario . ']/90',
        'color_dot' => 'bg-[' . $color_primario . ']'
    ];
}

/**
 * Construye URL preservando el idioma actual
 *
 * @param string $path Ruta base (ej: 'cursus.php')
 * @param array $params Parámetros adicionales
 * @param string $current_lang Idioma actual
 * @return string URL completa
 */
function buildUrl(string $path, array $params, string $current_lang): string
{
    $params['lang'] = $current_lang;
    return $path . '?' . http_build_query($params);
}
