<?php
/**
 * AJAX Endpoint: Generar Imagen con IA
 *
 * Genera imágenes usando DALL-E 3 basadas en contexto del evento histórico.
 * La API key se obtiene de config.php (servicio centralizado de VERUMax)
 *
 * POST Parameters:
 * - anio: Año del evento
 * - titulo: Título del evento
 * - descripcion: Descripción del evento
 */

header('Content-Type: application/json; charset=utf-8');

// Verificar metodo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo no permitido']);
    exit;
}

// Cargar configuracion global (incluye VERUMAX_IA_API_KEY)
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../identitas/config.php';

use VERUMax\Services\OpenAIService;

// Verificar sesion de admin
session_start();
if (!isset($_SESSION['admin_verumax'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$admin = $_SESSION['admin_verumax'];
$slug = $admin['slug'];

// Obtener datos de la institucion
$instance = getInstanceConfig($slug);
if (!$instance) {
    echo json_encode(['success' => false, 'error' => 'Institucion no encontrada']);
    exit;
}

// Verificar si IA esta habilitada para esta institucion
if (!OpenAIService::isEnabledForInstitution($slug)) {
    echo json_encode([
        'success' => false,
        'error' => 'La IA no esta habilitada. Active la IA en Ajustes > General > Integraciones.',
        'code' => 'NOT_ENABLED'
    ]);
    exit;
}

// Obtener parametros
$anio = $_POST['anio'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$size = $_POST['size'] ?? '1024x1024'; // Cuadrado por defecto

if (empty($titulo) && empty($descripcion)) {
    echo json_encode(['success' => false, 'error' => 'Se requiere al menos titulo o descripcion']);
    exit;
}

// Preparar contexto
$context = [
    'nombre_institucion' => $instance['nombre'] ?? $instance['nombre_completo'] ?? '',
    'anio' => $anio,
    'titulo' => $titulo,
    'descripcion' => $descripcion,
    'size' => $size
];

// Generar imagen con IA
$result = OpenAIService::generarImagen($context);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'image_url' => $result['image_url'],
        'source' => 'ia'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Error al generar imagen con IA'
    ]);
}
