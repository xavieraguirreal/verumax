<?php
/**
 * Creare PDF - Generación de Documentos PDF con mPDF
 * Sistema CERTIFICATUM - VERUMax
 * Versión: 1.0
 *
 * Este archivo captura el HTML generado por creare_content.php y lo convierte a PDF
 */

// Limpiar cualquier output previo y desactivar display de errores
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Evitar que errores se muestren en el output (irían al PDF y lo corromperían)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 1. Cargar configuración y servicios PSR-4
require_once 'config.php';

// Limpiar cualquier output generado por la carga de config
ob_end_clean();
ob_start();

use VERUMax\Services\PDFService;
use VERUMax\Services\StudentService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\CertificateService;

// 2. Identificar la institución
$institucion = $_GET['institutio'] ?? null;
if (!$institucion || !is_dir('../' . $institucion)) {
    die('Error: Institución no especificada o no válida.');
}

// 3. Inicializar idioma
$instance_config = InstitutionService::getConfig($institucion);
$lang_request = $_GET['lang'] ?? null;
LanguageService::init($institucion, $lang_request);
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// 4. Obtener parámetros
$dni = $_GET['documentum'] ?? null;
$curso_id = $_GET['cursus'] ?? null;
$participacion_id = $_GET['participacion'] ?? null;
$tipo_documento = $_GET['genus'] ?? 'analyticum';

// 5. Determinar tipo de documento para configuración de PDF
// Aceptar certificatum_docente como alias de certificatum_doctoris (compatibilidad)
$es_certificado = in_array($tipo_documento, ['certificatum_approbationis', 'certificatum_doctoris', 'certificatum_docente']);
$pdf_type = $es_certificado ? 'certificate' : 'constancy';

// Para analíticos usar configuración específica
if ($tipo_documento === 'analyticum') {
    $pdf_type = 'analytical';
}

// 6. Obtener datos para el nombre del archivo
$es_certificado_docente = (in_array($tipo_documento, ['certificatum_doctoris', 'certificatum_docente']) && $participacion_id);

if ($es_certificado_docente) {
    $datos = StudentService::getParticipacionDocente($institucion, $dni, (int)$participacion_id);
    $nombre_persona = $datos['nombre_completo'] ?? 'Documento';
    $nombre_curso = $datos['participacion']['nombre_curso'] ?? '';
} else {
    $datos = StudentService::getCourse($institucion, $dni, $curso_id);
    $nombre_persona = $datos['nombre_completo'] ?? 'Documento';
    $nombre_curso = $datos['curso']['nombre_curso'] ?? '';
}

// 6b. MARCADO AUTOMÁTICO DE CERTIFICADO EMITIDO
// Solo para tipos certificatum_* (no constancias ni analíticos)
$es_tipo_certificado = strpos($tipo_documento, 'certificatum_') === 0;

if ($es_tipo_certificado) {
    if ($es_certificado_docente) {
        StudentService::marcarCertificadoEmitidoDocente((int)$participacion_id);
    } else {
        $id_inscripcion = $datos['curso']['id_inscripcion'] ?? null;
        if ($id_inscripcion) {
            StudentService::marcarCertificadoEmitidoEstudiante((int)$id_inscripcion);
        }
    }
}

// 6c. LOGGING DE ACCESO: registrar descarga de PDF (mPDF)
CertificateService::logAccesoCertificado(
    $institucion,
    $dni,
    CertificateService::ACTION_DOWNLOAD,
    $tipo_documento,
    $curso_id,
    $nombre_curso,
    $es_certificado_docente ? 'docente' : 'estudiante',
    $es_certificado_docente ? (int)$participacion_id : null,
    $nombre_persona,
    LanguageService::getCurrentLang()
);

// 7. Generar nombre del archivo
$titulos_cortos = [
    'analyticum' => 'Analitico',
    'certificatum_approbationis' => 'Certificado',
    'certificatum_completionis' => 'Certificado_Finalizacion',
    'testimonium_regulare' => 'Constancia_Regular',
    'testimonium_completionis' => 'Constancia_Finalizacion',
    'testimonium_inscriptionis' => 'Constancia_Inscripcion',
    'certificatum_doctoris' => 'Certificado_Docente',
    'certificatum_docente' => 'Certificado_Docente',  // Alias para compatibilidad
    'testimonium_doctoris' => 'Constancia_Docente'
];

$tipo_corto = $titulos_cortos[$tipo_documento] ?? 'Documento';
$filename = $tipo_corto . '_' . $nombre_persona . '_' . substr($nombre_curso, 0, 30);

// 8. Capturar el HTML generado por creare_content.php
ob_start();

// Definir que estamos en modo PDF para que creare_content.php sepa que puede ejecutarse
define('PDF_MODE', true);

// Incluir creare_content.php para obtener el HTML del documento
include __DIR__ . '/creare_content.php';

$html_content = ob_get_clean();

// 9. Limpiar cualquier output residual antes de enviar el PDF
while (ob_get_level()) {
    ob_end_clean();
}

// 10. Generar y enviar el PDF
try {
    PDFService::generateFromHtml($html_content, $filename, $pdf_type, true);
} catch (Exception $e) {
    error_log("Error generando PDF: " . $e->getMessage());
    // Enviar error como texto plano
    header('Content-Type: text/plain; charset=utf-8');
    die("Error al generar el PDF: " . $e->getMessage());
}
