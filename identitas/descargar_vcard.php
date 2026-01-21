<?php
/**
 * Descargar vCard - Endpoint para descargar archivo de contacto
 *
 * Permite a los usuarios descargar un archivo .vcf con los datos de contacto
 * de la institución para agregarlo a sus dispositivos.
 *
 * Uso:
 *   GET /identitas/descargar_vcard.php?institutio=sajur
 *   GET /sajur/descargar_vcard.php (si se crea proxy)
 *
 * @package VERUMax\Identitas
 */

// Cargar configuración central
require_once __DIR__ . '/../verumax/config.php';

use VERUMax\Services\VCardService;

// Obtener parámetros
$institucion = $_GET['institutio'] ?? $_GET['slug'] ?? null;

// Validar institución requerida
if (empty($institucion)) {
    http_response_code(400);
    die('Error: Parámetro institutio requerido');
}

// Sanitizar slug
$institucion = preg_replace('/[^a-zA-Z0-9_-]/', '', $institucion);

// Obtener configuración de la instancia
$instanceConfig = getInstanceConfig($institucion);

if (!$instanceConfig) {
    http_response_code(404);
    die('Error: Institución no encontrada');
}

// Verificar que Identitas esté activo para esta instancia
if (empty($instanceConfig['identitas_activo'])) {
    http_response_code(403);
    die('Error: Servicio no disponible para esta institución');
}

// Generar contenido vCard
try {
    $vcardContent = VCardService::generateFromInstance($instanceConfig);
} catch (Exception $e) {
    error_log('[IDENTITAS] Error generando vCard: ' . $e->getMessage());
    http_response_code(500);
    die('Error: No se pudo generar el archivo de contacto');
}

// Determinar nombre del archivo
$filename = $instanceConfig['slug'] ?? 'contacto';

// Enviar headers para descarga
VCardService::sendDownloadHeaders($filename);

// Enviar contenido
echo $vcardContent;
exit;
