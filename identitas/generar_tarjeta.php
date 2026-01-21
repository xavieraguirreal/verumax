<?php
/**
 * Generar Tarjeta Digital - Endpoint para generar tarjeta JPG
 *
 * Permite generar una tarjeta digital con los datos de la institución
 * o de una persona específica.
 *
 * Uso:
 *   GET /identitas/generar_tarjeta.php?institutio=sajur
 *   GET /identitas/generar_tarjeta.php?institutio=sajur&template=moderno
 *   POST /identitas/generar_tarjeta.php (con datos de persona)
 *
 * Parámetros GET:
 *   - institutio: slug de la institución (requerido)
 *   - template: slug del template a usar (opcional, default: 'default')
 *   - download: 1 para descargar, 0 para mostrar URL (default: 1)
 *
 * Parámetros POST (para tarjeta personal):
 *   - nombre: nombre de la persona
 *   - cargo: cargo/título
 *   - telefono: teléfono
 *   - email: email
 *
 * @package VERUMax\Identitas
 */

// Cargar configuración central
require_once __DIR__ . '/../verumax/config.php';

use VERUMax\Services\CardImageService;

// Obtener parámetros
$institucion = $_GET['institutio'] ?? $_GET['slug'] ?? $_POST['institutio'] ?? null;
$template = $_GET['template'] ?? $_POST['template'] ?? 'default';
$download = isset($_GET['download']) ? (bool)$_GET['download'] : true;

// Validar institución requerida
if (empty($institucion)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Parámetro institutio requerido']);
    exit;
}

// Sanitizar slug
$institucion = preg_replace('/[^a-zA-Z0-9_-]/', '', $institucion);

// Obtener configuración de la instancia
$instanceConfig = getInstanceConfig($institucion);

if (!$instanceConfig) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Institución no encontrada']);
    exit;
}

// Verificar que Identitas esté activo para esta instancia
if (empty($instanceConfig['identitas_activo'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Servicio no disponible para esta institución']);
    exit;
}

// Preparar datos de persona (si vienen por POST)
$personaData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personaData = [
        'nombre' => $_POST['nombre'] ?? '',
        'cargo' => $_POST['cargo'] ?? '',
        'telefono' => $_POST['telefono'] ?? '',
        'email' => $_POST['email'] ?? '',
    ];
}

// Generar tarjeta
try {
    $result = CardImageService::generateFromInstance($instanceConfig, $personaData, $template);
} catch (Exception $e) {
    error_log('[IDENTITAS] Error generando tarjeta: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error interno al generar la tarjeta']);
    exit;
}

if (!$result['success']) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Si es descarga, enviar el archivo
if ($download && file_exists($result['path'])) {
    $filename = $result['filename'] ?? 'tarjeta_' . $institucion . '.jpg';

    header('Content-Type: image/jpeg');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($result['path']));
    header('Cache-Control: no-cache, no-store, must-revalidate');

    readfile($result['path']);
    exit;
}

// Si no es descarga, retornar JSON con URL
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'url' => $result['url'],
    'path' => $result['path'],
    'filename' => $result['filename']
]);
exit;
