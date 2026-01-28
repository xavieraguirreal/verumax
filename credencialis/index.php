<?php
/**
 * CREDENCIALIS - Punto de Entrada Principal
 * Sistema VERUMax - Credenciales de Membresía Verificables
 * Versión: 1.0
 *
 * Este archivo recibe el parámetro institutio y muestra el portal
 * de acceso a credenciales para esa institución.
 */

// Cargar configuración core
require_once __DIR__ . '/../verumax/config.php';

use VERUMax\Services\LanguageService;

// Obtener slug de institución
$slug = $_GET['institutio'] ?? $_POST['institutio'] ?? null;

if (!$slug) {
    // Si no hay institución, mostrar error
    die('Error: Institución no especificada. Use ?institutio=slug');
}

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

// Incluir el template del portal
include __DIR__ . '/templates/solo.php';
