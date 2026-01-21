<?php
/**
 * VERUMAX - Configuración Core
 * Sistema VERUMax - Plataforma Principal
 *
 * Este archivo contiene las funciones core compartidas por todos los módulos.
 * Los módulos opcionales (Identitas, Certificatum) pueden usar este archivo
 * en lugar de depender unos de otros.
 *
 * Versión: 1.0
 */

// =====================================================
// CARGAR SERVICIOS PSR-4 Y CONFIGURACIÓN DE ENTORNO
// =====================================================
require_once __DIR__ . '/../env_loader.php';

use VERUMax\Services\InstitutionService;

// Configuración de rutas
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
if (!defined('VERUMAX_PATH')) define('VERUMAX_PATH', __DIR__);

// Configuración de sesiones (solo si la sesión no está activa)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 3600);
    session_set_cookie_params(3600);
    session_start();
}

// =====================================================
// FUNCIONES CORE DE VERUMAX
// =====================================================

/**
 * Obtiene configuración de una instancia/institución
 * Función core que usa InstitutionService internamente
 *
 * @param string $slug Slug de la institución (ej: 'sajur')
 * @return array|null Configuración combinada o null si no existe
 */
function getInstanceConfig($slug) {
    // Usar InstitutionService que ya tiene toda la lógica
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

/**
 * Obtiene las clases CSS apropiadas para el logo según el estilo configurado
 *
 * @param string $logo_estilo El estilo del logo (rectangular, cuadrado, circular, etc.)
 * @param string $size_class Clase de altura (por defecto 'h-12' para 48px)
 * @return string Clases CSS para aplicar al tag <img>
 */
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
