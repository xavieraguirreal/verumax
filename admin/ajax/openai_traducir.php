<?php
/**
 * AJAX Endpoint: Traducir texto con IA
 *
 * Traduce texto de un idioma a otro usando OpenAI.
 *
 * POST Parameters:
 * - texto: Texto a traducir
 * - idioma_origen: Código del idioma origen (ej: es_AR)
 * - idioma_destino: Código del idioma destino (ej: pt_BR)
 */

header('Content-Type: application/json; charset=utf-8');

// Verificar metodo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo no permitido']);
    exit;
}

// Cargar configuracion global
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
$texto = $_POST['texto'] ?? '';
$idiomaOrigen = $_POST['idioma_origen'] ?? 'es_AR';
$idiomaDestino = $_POST['idioma_destino'] ?? 'pt_BR';

if (empty($texto)) {
    echo json_encode(['success' => false, 'error' => 'Texto vacio']);
    exit;
}

// Mapeo de códigos a nombres de idioma
$nombresIdiomas = [
    'es_AR' => 'español argentino',
    'pt_BR' => 'portugués brasileño',
    'en_US' => 'inglés estadounidense',
    'el_GR' => 'griego'
];

$nombreOrigen = $nombresIdiomas[$idiomaOrigen] ?? $idiomaOrigen;
$nombreDestino = $nombresIdiomas[$idiomaDestino] ?? $idiomaDestino;

// Construir el prompt
$prompt = "Traduce el siguiente texto de {$nombreOrigen} a {$nombreDestino}.
Mantén el mismo tono y estilo. Si es texto institucional/formal, mantén la formalidad.
Devuelve SOLO la traducción, sin explicaciones adicionales.

Texto a traducir:
{$texto}";

try {
    // Usar OpenAIService para la traducción
    $traduccion = OpenAIService::generateContent($prompt, [
        'max_tokens' => 500,
        'temperature' => 0.3 // Baja temperatura para traducciones más precisas
    ]);

    if ($traduccion) {
        echo json_encode([
            'success' => true,
            'traduccion' => trim($traduccion),
            'idioma_origen' => $idiomaOrigen,
            'idioma_destino' => $idiomaDestino
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No se pudo generar la traducción'
        ]);
    }
} catch (Exception $e) {
    error_log("Error en traducción IA: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al traducir: ' . $e->getMessage()
    ]);
}
