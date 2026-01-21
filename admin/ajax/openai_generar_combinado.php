<?php
/**
 * AJAX Endpoint: Generar campos combinados con IA
 *
 * Genera contenido para grupos de campos relacionados (cita+autor, titulo+subtitulo, etc.)
 * La API key se obtiene de config.php (servicio centralizado de VERUMax)
 *
 * POST Parameters:
 * - group_type: Tipo de grupo (cita_autor, titulo_subtitulo, stat, etc.)
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
$groupType = $_POST['group_type'] ?? '';

if (empty($groupType)) {
    echo json_encode(['success' => false, 'error' => 'Parametros incompletos: group_type requerido']);
    exit;
}

// Obtener valores existentes (para evitar duplicados)
$existingValues = $_POST['existing_values'] ?? '';
$existingValuesArray = !empty($existingValues) ? explode('|', $existingValues) : [];

// Preparar contexto de la institucion
$context = [
    'nombre' => $instance['nombre'] ?? '',
    'nombre_completo' => $instance['nombre_completo'] ?? '',
    'tipo' => 'institucion educativa',
    'email' => $instance['email_contacto'] ?? '',
    'sitio_web' => $instance['sitio_web_oficial'] ?? '',
    'existing_values' => $existingValuesArray // Valores ya usados en otros items
];

// Generar contenido combinado con IA
$result = OpenAIService::generarCamposCombinados($groupType, $context);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'fields' => $result['fields'],
        'field_names' => $result['field_names'] ?? [],
        'source' => 'ia'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Error al generar contenido con IA'
    ]);
}
