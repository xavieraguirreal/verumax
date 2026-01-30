<?php
/**
 * Verumax - Configuración Global
 *
 * Define constantes y configuraciones compartidas entre todas las páginas.
 */

// Versión del sistema (Semantic Versioning: MAJOR.MINOR.PATCH)
define('APP_VERSION', '2.0.0');
define('APP_NAME', 'Verumax');
define('APP_YEAR', '2025');

// Versión de assets para cache busting (cambiar cuando se modifiquen CSS/JS/imágenes)
define('ASSET_VERSION', '2.0.0');

// Rutas de assets (URLs absolutas para compatibilidad con subdominios)
define('CSS_PATH', 'https://verumax.com/assets/css/');
define('JS_PATH', 'https://verumax.com/assets/js/');
define('IMG_PATH', 'https://verumax.com/assets/images/');

// =============================================================================
// Configuracion de Inteligencia Artificial (servicio interno de VERUMax)
// =============================================================================
define('VERUMAX_IA_API_KEY', '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');
define('VERUMAX_IA_MODEL', 'gpt-4.1-nano-2025-04-14');

/**
 * Guía de versionado:
 *
 * MAJOR (1.x.x): Cambios incompatibles con versiones anteriores
 *                Ejemplo: Reestructuración completa del sistema
 *
 * MINOR (x.1.x): Nuevas funcionalidades compatibles con versiones anteriores
 *                Ejemplo: Agregar nueva página, nueva sección importante
 *
 * PATCH (x.x.1): Correcciones de bugs y mejoras menores
 *                Ejemplo: Corregir estilos, mejorar traducciones, fix de bugs
 */
?>
