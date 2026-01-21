<?php
/**
 * AJAX Endpoint: Autocompletar con IA
 *
 * Genera contenido inteligente para campos de formulario usando IA.
 * La API key se obtiene de config.php (servicio centralizado de VERUMax)
 *
 * POST Parameters:
 * - field_name: Nombre del campo
 * - field_label: Etiqueta del campo
 * - field_type: Tipo de campo (text, textarea, editor)
 * - bloque: (opcional) Nombre del bloque para contexto adicional
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

// La API key se carga automaticamente desde config.php via initFromConfig()

// Obtener parametros
$fieldName = $_POST['field_name'] ?? '';
$fieldLabel = $_POST['field_label'] ?? '';
$fieldType = $_POST['field_type'] ?? 'text';
$bloque = $_POST['bloque'] ?? '';

if (empty($fieldLabel)) {
    echo json_encode(['success' => false, 'error' => 'Parametros incompletos']);
    exit;
}

// Obtener valores existentes (para evitar duplicados)
$existingValues = $_POST['existing_values'] ?? '';
$existingValuesArray = !empty($existingValues) ? explode('|', $existingValues) : [];

// Preparar contexto de la institucion
$context = [
    'nombre' => $instance['nombre'] ?? '',
    'nombre_completo' => $instance['nombre_completo'] ?? '',
    'tipo' => 'institucion educativa', // Se podria agregar un campo para esto
    'email' => $instance['email_contacto'] ?? '',
    'sitio_web' => $instance['sitio_web_oficial'] ?? '',
    'bloque' => $bloque, // Contexto del bloque (stats, equipo, valores, etc.)
    'existing_values' => $existingValuesArray // Valores ya usados en otros items
];

// Generar contenido con IA
$result = OpenAIService::autocompletarCampo($fieldName, $fieldLabel, $fieldType, $context);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'content' => $result['content'],
        'source' => 'ia'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Error al generar contenido con IA'
    ]);
}
