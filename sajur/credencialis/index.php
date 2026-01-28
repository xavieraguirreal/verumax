<?php
/**
 * SAJuR - Portal de Credenciales (Credencialis)
 * Versión: 2.0
 *
 * Este archivo muestra el portal de acceso a credenciales para SAJuR
 * usando el template compartido de Credencialis.
 */

// Definir slug de esta instancia
$slug = 'sajur';

// Incluir configuración core VERUMax
require_once dirname(dirname(__DIR__)) . '/verumax/config.php';

// Cargar servicio de idiomas
use VERUMax\Services\LanguageService;

// Obtener configuración de la instancia
$instance_config = getInstanceConfig($slug);

if (!$instance_config) {
    die('Error: Institución no encontrada');
}

// Inicializar idioma
$lang_request = $_GET['lang'] ?? null;
LanguageService::init($slug, $lang_request);

// Preparar variables para el template
$instance = $instance_config;
$credencialis_config = [
    'modo' => $instance_config['credencialis_modo'] ?? 'pagina',
    'titulo' => $instance_config['credencialis_titulo'] ?? 'Credencial Digital',
];

// Incluir el template del portal de credenciales
$template_path = dirname(dirname(__DIR__)) . '/credencialis/templates/solo.php';

if (file_exists($template_path)) {
    include $template_path;
} else {
    echo "<!-- ERROR: Template solo.php no encontrado en: $template_path -->";
    echo "<h1>Error: Template de credenciales no encontrado</h1>";
}
