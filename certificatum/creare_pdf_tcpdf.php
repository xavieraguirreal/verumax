<?php
/**
 * Generador de Certificados PDF con TCPDF
 * Sistema CERTIFICATUM - VERUMax
 *
 * Usa TCPDF para generar PDFs de forma nativa, dibujando sobre imagen de fondo
 *
 * SISTEMA DE TEMPLATES:
 * - Si curso tiene id_template → usa config JSON del template
 * - Si id_template es NULL → usa sistema actual (fallback)
 */

// Iniciar buffer de salida para PDF
ob_start();

// Cargar configuración y servicios
require_once __DIR__ . '/config.php';

use VERUMax\Services\StudentService;
use VERUMax\Services\CertificateService;
use VERUMax\Services\CertificateTemplateService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\QRCodeService;

// Cargar TCPDF
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

/**
 * Convierte marcadores de formato a HTML
 * **texto** -> <b>texto</b>
 * *texto* -> <i>texto</i>
 */
function applyTextFormatting($text) {
    if (empty($text)) return $text;

    // Negrita: **texto** -> <b>texto</b> (TCPDF maneja mejor tags nativos que spans)
    $result = preg_replace('/\*\*(.+?)\*\*/s', '<b>$1</b>', $text);

    // Italica: *texto* -> <i>texto</i>
    $result = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/s', '<i>$1</i>', $result);

    return $result;
}

/**
 * Verifica si el texto tiene marcadores de formato
 */
function hasFormatMarkers($text) {
    return preg_match('/\*\*.+?\*\*|(?<!\*)\*[^*]+\*(?!\*)/', $text);
}
/**
 * Verifica si path es válido (archivo local o URL)
 */
function isValidImagePath($path) {
    if (empty($path)) return false;
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return true; // Es URL, TCPDF puede cargarla
    }
    return file_exists($path);
}

/**
 * Determina si un curso es de día único o rango de fechas
 * @param string|null $fecha_inicio Fecha de inicio (Y-m-d)
 * @param string|null $fecha_fin Fecha de fin (Y-m-d)
 * @return bool|null true=día único, false=rango, null=sin fechas
 */
function esCursoDiaUnico($fecha_inicio, $fecha_fin) {
    if (!$fecha_inicio || !$fecha_fin) return null;
    return $fecha_inicio === $fecha_fin;
}

/**
 * Dibuja una imagen centrada dentro de un contenedor (simula object-fit: contain de CSS)
 * @param TCPDF $pdf Instancia de TCPDF
 * @param string $img_path Ruta de la imagen
 * @param float $x Posición X del contenedor
 * @param float $y Posición Y del contenedor
 * @param float $container_w Ancho del contenedor
 * @param float $container_h Alto del contenedor
 */
function drawImageCentered($pdf, $img_path, $x, $y, $container_w, $container_h) {
    // Verificar si es archivo local o URL
    $is_url = (strpos($img_path, 'http://') === 0 || strpos($img_path, 'https://') === 0);

    if (!$is_url && !file_exists($img_path)) return;

    $img_info = @getimagesize($img_path);
    if (!$img_info) {
        // Fallback: dibujar sin centrar (TCPDF puede manejar URLs directamente)
        try {
            $pdf->Image($img_path, $x, $y, $container_w, 0, '', '', '', false, 300, '', false, false, 0);
        } catch (Exception $e) {
            // Silenciar errores
        }
        return;
    }

    $img_w = $img_info[0];
    $img_h = $img_info[1];
    $img_ratio = $img_w / $img_h;
    $container_ratio = $container_w / max(0.1, $container_h);  // Evitar división por cero

    // Calcular tamaño final manteniendo proporción (object-fit: contain)
    if ($img_ratio > $container_ratio) {
        // Imagen más ancha: ajustar al ancho del contenedor
        $final_w = $container_w;
        $final_h = $container_w / $img_ratio;
    } else {
        // Imagen más alta: ajustar a la altura del contenedor
        $final_h = $container_h;
        $final_w = $container_h * $img_ratio;
    }

    // Centrar dentro del contenedor
    $offset_x = ($container_w - $final_w) / 2;
    $offset_y = ($container_h - $final_h) / 2;

    try {
        $pdf->Image($img_path, $x + $offset_x, $y + $offset_y, $final_w, $final_h, '', '', '', false, 300, '', false, false, 0);
    } catch (Exception $e) {
        // Fallback sin centrar
        $pdf->Image($img_path, $x, $y, $container_w, 0, '', '', '', false, 300, '', false, false, 0);
    }
}

/**
 * Genera PDF de credencial de socio/miembro
 * @param array $miembro Datos del miembro
 * @param array $instance_config Configuración de la institución
 * @param string $codigo_validacion Código único para QR
 * @param bool $es_instancia_test Si es instancia de prueba
 * @param string $formato 'individual' o 'a4_multiple'
 */
function generarPDFCredencial($miembro, $instance_config, $codigo_validacion, $es_instancia_test, $formato = 'individual') {
    // Extraer datos del miembro
    $nombre_completo = $miembro['nombre_completo'] ?? ($miembro['nombre'] . ' ' . $miembro['apellido']);
    $dni = $miembro['identificador_principal'] ?? '';
    $numero_asociado = $miembro['numero_asociado'] ?? '';
    $tipo_asociado = $miembro['tipo_asociado'] ?? '';
    $nombre_entidad = $miembro['nombre_entidad'] ?? '';
    $categoria_servicio = $miembro['categoria_servicio'] ?? '';
    $fecha_ingreso = $miembro['fecha_ingreso'] ?? '';

    // Formatear DNI
    $dni_formateado = number_format((int)preg_replace('/[^0-9]/', '', $dni), 0, '', '.');

    // Formatear fecha
    $fecha_ingreso_fmt = $fecha_ingreso ? date('d/m/Y', strtotime($fecha_ingreso)) : '';

    // Datos institución
    $nombre_institucion = $instance_config['nombre_completo'] ?? $instance_config['nombre'] ?? 'Institución';
    $logo_url = $instance_config['logo_url'] ?? '';
    $logo_secundario_url = $instance_config['logo_secundario_url'] ?? '';
    $color_primario = $instance_config['color_primario'] ?? '#2E7D32';

    // Config de credencial
    $credencial_config = json_decode($instance_config['credencial_config'] ?? '{}', true);
    $texto_superior = $credencial_config['texto_superior'] ?? 'CREDENCIAL DE SOCIO';
    $texto_inferior = $credencial_config['texto_inferior'] ?? '';
    $template_url = $credencial_config['template_url'] ?? null;

    // Dimensiones de credencial (tamaño tarjeta de crédito en mm)
    $card_width = 85.6;
    $card_height = 54;

    // Crear PDF
    if ($formato === 'a4_multiple') {
        // A4 vertical con múltiples credenciales
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $page_width = 210;
        $page_height = 297;
        $margin = 10;
        $cards_per_row = 2;
        $cards_per_col = 5;
        $gap_x = 5;
        $gap_y = 3;
    } else {
        // Individual - tamaño tarjeta
        $pdf = new TCPDF('L', 'mm', array($card_width, $card_height), true, 'UTF-8', false);
    }

    $pdf->SetCreator('VERUMax');
    $pdf->SetAuthor($nombre_institucion);
    $pdf->SetTitle('Credencial - ' . $nombre_completo);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false, 0);

    // Convertir color hex a RGB
    $color_rgb = sscanf($color_primario, "#%02x%02x%02x");

    // Generar QR
    $qrFile = tempnam(sys_get_temp_dir(), 'qr_cred_') . '.png';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $domain = $_SERVER['HTTP_HOST'] ?? 'verumax.com';
    $qr_url = $protocol . $domain . '/validare.php?codigo=' . $codigo_validacion;

    QRCodeService::generateFile($qr_url, $qrFile, 150);

    // Función para dibujar una credencial
    $drawCredencial = function($pdf, $x, $y, $w, $h) use (
        $nombre_completo, $dni_formateado, $numero_asociado, $tipo_asociado,
        $categoria_servicio, $fecha_ingreso_fmt, $nombre_entidad,
        $logo_url, $logo_secundario_url, $color_rgb, $texto_superior, $texto_inferior,
        $template_url, $qrFile, $codigo_validacion, $es_instancia_test, $miembro
    ) {
        // Si hay template de fondo
        if ($template_url && file_exists($template_url)) {
            $pdf->Image($template_url, $x, $y, $w, $h, '', '', '', false, 300, '', false, false, 0);
        } else {
            // Fondo blanco con borde
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Rect($x, $y, $w, $h, 'F');
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Rect($x, $y, $w, $h, 'D');

            // Header con color
            $header_h = 12;
            $pdf->SetFillColor($color_rgb[0], $color_rgb[1], $color_rgb[2]);
            $pdf->Rect($x, $y, $w, $header_h, 'F');

            // Logos en header
            if ($logo_url && isValidImagePath($logo_url)) {
                $pdf->Image($logo_url, $x + 3, $y + 2, 0, $header_h - 4, '', '', '', false, 300, '', false, false, 0);
            }
            if ($logo_secundario_url && isValidImagePath($logo_secundario_url)) {
                $pdf->Image($logo_secundario_url, $x + $w - 28, $y + 2, 0, $header_h - 4, '', '', '', false, 300, '', false, false, 0);
            }

            // Banner con texto
            if ($texto_superior) {
                $banner_y = $y + $header_h;
                $banner_h = 6;
                $pdf->SetFillColor($color_rgb[0], $color_rgb[1], $color_rgb[2]);
                $pdf->Rect($x, $banner_y, $w, $banner_h, 'F');
                $pdf->SetFont('helvetica', 'B', 7);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetXY($x, $banner_y + 1);
                $pdf->Cell($w, $banner_h - 2, $texto_superior, 0, 0, 'C');
            }

            // Datos del socio
            $content_y = $y + $header_h + 8;
            $pdf->SetTextColor(0, 0, 0);

            // Nombre
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetXY($x + 3, $content_y);
            $pdf->Cell($w - 25, 5, mb_strtoupper($nombre_completo, 'UTF-8'), 0, 1, 'L');

            // DNI
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetXY($x + 3, $content_y + 5);
            $pdf->Cell($w - 25, 4, 'DNI ' . $dni_formateado, 0, 1, 'L');

            // Número asociado
            if ($numero_asociado) {
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetTextColor($color_rgb[0], $color_rgb[1], $color_rgb[2]);
                $pdf->SetXY($x + 3, $content_y + 11);
                $genero = $miembro['genero'] ?? '';
                $prefijo = ($genero === 'Femenino' || $nombre_entidad) ? 'ASOCIADA' : 'ASOCIADO';
                $texto_asociado = $prefijo . ' ' . $numero_asociado;
                if ($tipo_asociado) $texto_asociado .= ' ' . $tipo_asociado;
                $pdf->Cell($w - 25, 4, $texto_asociado, 0, 1, 'L');
            }

            // Categoría servicio
            if ($categoria_servicio) {
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetXY($x + 3, $content_y + 16);
                $pdf->Cell($w - 25, 4, $categoria_servicio, 0, 1, 'L');
            }

            // Fecha ingreso
            if ($fecha_ingreso_fmt) {
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetXY($x + 3, $content_y + 21);
                $pdf->Cell($w - 25, 4, 'INGRESO ' . $fecha_ingreso_fmt, 0, 1, 'L');
            }

            // QR
            $qr_size = 18;
            $qr_x = $x + $w - $qr_size - 3;
            $qr_y = $content_y;
            if (file_exists($qrFile)) {
                $pdf->Image($qrFile, $qr_x, $qr_y, $qr_size, $qr_size, 'PNG');
            }

            // Footer
            if ($texto_inferior) {
                $footer_y = $y + $h - 6;
                $pdf->SetFillColor($color_rgb[0], $color_rgb[1], $color_rgb[2]);
                $pdf->Rect($x, $footer_y, $w, 6, 'F');
                $pdf->SetFont('helvetica', '', 6);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetXY($x, $footer_y + 1);
                $pdf->Cell($w, 4, $texto_inferior, 0, 0, 'C');
            }
        }

        // Watermark para test
        if ($es_instancia_test) {
            $pdf->SetAlpha(0.3);
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->SetTextColor(220, 38, 38);
            $pdf->StartTransform();
            $pdf->Rotate(-25, $x + $w/2, $y + $h/2);
            $pdf->SetXY($x, $y + $h/2 - 5);
            $pdf->Cell($w, 10, 'PRUEBA', 0, 0, 'C');
            $pdf->StopTransform();
            $pdf->SetAlpha(1);
        }
    };

    // Generar según formato
    if ($formato === 'a4_multiple') {
        $pdf->AddPage();
        $start_x = $margin;
        $start_y = $margin;

        for ($row = 0; $row < $cards_per_col; $row++) {
            for ($col = 0; $col < $cards_per_row; $col++) {
                $cx = $start_x + $col * ($card_width + $gap_x);
                $cy = $start_y + $row * ($card_height + $gap_y);
                $drawCredencial($pdf, $cx, $cy, $card_width, $card_height);
            }
        }
    } else {
        $pdf->AddPage();
        $drawCredencial($pdf, 0, 0, $card_width, $card_height);
    }

    // Limpiar QR temporal
    if (file_exists($qrFile)) {
        @unlink($qrFile);
    }

    // Limpiar buffer y enviar PDF
    while (ob_get_level()) {
        ob_end_clean();
    }

    $nombre_archivo = 'Credencial_' . preg_replace('/[^A-Za-z0-9]/', '_', $nombre_completo) . '.pdf';
    $pdf->Output($nombre_archivo, 'I');
}

// Parámetros de entrada
$institucion = $_GET['institutio'] ?? null;
$dni = $_GET['documentum'] ?? null;
$curso_id = $_GET['cursus'] ?? null;
$participacion_id = $_GET['participacion'] ?? null;
$tipo_documento = $_GET['genus'] ?? 'certificatum_approbationis';
$lang_request = $_GET['lang'] ?? null;
$formato_salida = $_GET['formato'] ?? 'individual'; // individual, a4_multiple

// ============================================================
// CREDENCIALES: Flujo especial sin curso
// ============================================================
if ($tipo_documento === 'credentialis') {
    if (!$institucion || !$dni) {
        die('Parámetros inválidos para credencial');
    }

    // Cargar MemberService
    require_once __DIR__ . '/../src/VERUMax/Services/MemberService.php';

    $instance_config = InstitutionService::getConfig($institucion);
    LanguageService::init($institucion, $lang_request);

    $id_instancia = $instance_config['id_instancia'] ?? null;
    if (!$id_instancia) {
        die('Error: Institución no configurada');
    }

    $miembro = \VERUMax\Services\MemberService::getByIdentificador($id_instancia, $dni);
    if (!$miembro) {
        die('Error: Miembro no encontrado');
    }

    // Generar código de validación
    $codigo_validacion = CertificateService::getValidationCode(
        $institucion,
        $dni,
        'credencial_' . $miembro['id_miembro'],
        'credentialis'
    );
    if (strpos($codigo_validacion, 'CERT-') === 0) {
        $codigo_validacion = 'CRED-' . substr($codigo_validacion, 5);
    }

    // Detectar modo test
    $es_instancia_test = ($instance_config['plan'] ?? '') === 'test';

    // Generar PDF de credencial
    generarPDFCredencial($miembro, $instance_config, $codigo_validacion, $es_instancia_test, $formato_salida);
    exit;
}

if (!$institucion || !$dni || !$curso_id) {
    die('Parámetros inválidos');
}

// Inicializar idioma
$instance_config = InstitutionService::getConfig($institucion);
LanguageService::init($institucion, $lang_request);
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Detectar si es instancia de prueba (plan = 'test')
$es_instancia_test = ($instance_config['plan'] ?? '') === 'test';

// Obtener datos
$es_certificado_docente = ($tipo_documento === 'certificatum_doctoris' && $participacion_id);
$es_certificado_finalizacion = ($tipo_documento === 'certificatum_completionis');

if ($es_certificado_docente) {
    $datos = StudentService::getParticipacionDocente($institucion, $dni, (int)$participacion_id);
    if (!$datos) {
        die("Error: Datos de participación docente no encontrados.");
    }
    // Convertir nombre a Title Case para consistencia con la vista web
    $nombre_completo = mb_convert_case(mb_strtolower($datos['nombre_completo'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    $nombre_curso = $datos['participacion']['nombre_curso'];
    $carga_horaria = $datos['participacion']['carga_horaria_dictada'] ?? $datos['participacion']['carga_horaria'];
    $rol = $datos['participacion']['rol'] ?? 'docente';
    // Usar participacion como curso para firmantes
    $curso = $datos['participacion'];

    // Obtener template del curso para docentes (mismo template que estudiantes)
    $id_template = null;
    try {
        // Intentar obtener el código del curso desde la participación
        $codigo_curso_docente = $datos['participacion']['codigo_curso'] ?? $curso_id;
        $template_data = CertificateTemplateService::getForCurso(
            $instance_config['id_instancia'] ?? 0,
            $codigo_curso_docente
        );
        $id_template = $template_data ? $template_data['id_template'] : null;
    } catch (Exception $e) {
        $id_template = null;
    } catch (Error $e) {
        $id_template = null;
    }
} else {
    $datos = StudentService::getCourse($institucion, $dni, $curso_id);
    if (!$datos) {
        die("Error: Datos no encontrados.");
    }
    // Convertir nombre a Title Case para consistencia con la vista web
    $nombre_completo = mb_convert_case(mb_strtolower($datos['nombre_completo'], 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    $nombre_curso = $datos['curso']['nombre_curso'];
    $carga_horaria = $datos['curso']['carga_horaria'] ?? '';
    // Asignar curso para firmantes
    $curso = $datos['curso'];

    // Obtener id_template de forma segura (no falla si columna no existe)
    $id_template = null;
    $debug_error = null;
    try {
        $template_data = CertificateTemplateService::getForCurso(
            $instance_config['id_instancia'] ?? 0,
            $curso_id
        );
        $id_template = $template_data ? $template_data['id_template'] : null;
    } catch (Exception $e) {
        // Si falla (columna no existe), usar fallback
        $id_template = null;
        $debug_error = $e->getMessage();
    } catch (Error $e) {
        // Capturar errores fatales también
        $id_template = null;
        $debug_error = "Error: " . $e->getMessage();
    }

    // DEBUG TEMPORAL - Eliminar después de probar
    if (isset($_GET['debug'])) {
        header('Content-Type: text/html; charset=utf-8');
        echo "<h2>DEBUG Template</h2>";
        echo "<p>id_instancia: " . ($instance_config['id_instancia'] ?? 'NULL') . "</p>";
        echo "<p>curso_id: $curso_id</p>";
        echo "<p>id_template obtenido: " . ($id_template ?? 'NULL') . "</p>";
        echo "<p>template_data: <pre>" . print_r($template_data ?? 'NULL', true) . "</pre></p>";
        if ($debug_error) {
            echo "<p style='color:red;'>ERROR CAPTURADO: $debug_error</p>";
        }
        echo "<h3>Debug Info del Service:</h3>";
        echo "<pre>" . print_r(CertificateTemplateService::getDebugInfo(), true) . "</pre>";

        // Verificar si la clase tiene el método
        echo "<h3>Verificación de clase:</h3>";
        echo "<p>Clase existe: " . (class_exists('VERUMax\Services\CertificateTemplateService') ? 'SI' : 'NO') . "</p>";
        echo "<p>Método getDebugInfo existe: " . (method_exists('VERUMax\Services\CertificateTemplateService', 'getDebugInfo') ? 'SI' : 'NO') . "</p>";
        exit;
    }
}

// ============================================================================
// MARCADO AUTOMÁTICO DE CERTIFICADO EMITIDO
// Solo para tipos certificatum_* (no constancias ni analíticos)
// ============================================================================
$fecha_emision_real = null;
$es_tipo_certificado = strpos($tipo_documento, 'certificatum_') === 0;

if ($es_tipo_certificado) {
    if ($es_certificado_docente) {
        $id_participacion = (int)$participacion_id;
        $fecha_emision_real = StudentService::marcarCertificadoEmitidoDocente($id_participacion);
    } else {
        $id_inscripcion = $curso['id_inscripcion'] ?? null;
        if ($id_inscripcion) {
            $fecha_emision_real = StudentService::marcarCertificadoEmitidoEstudiante((int)$id_inscripcion);
        }
    }
}

// LOGGING DE ACCESO: registrar descarga de PDF (TCPDF)
CertificateService::logAccesoCertificado(
    $institucion,
    $dni,
    CertificateService::ACTION_DOWNLOAD,
    $tipo_documento,
    $curso_id,
    $curso['nombre_curso'] ?? null,
    $es_certificado_docente ? 'docente' : 'estudiante',
    $es_certificado_docente ? (int)$participacion_id : null,
    $nombre_completo ?? null,
    LanguageService::getCurrentLang()
);

// Generar código de validación
$codigo_validacion = CertificateService::getValidationCode($institucion, $dni, $curso_id, $tipo_documento);

// URL de validación para QR (unificada con creare.php)
// Incluir autodetect si no está ya cargado
if (!function_exists('esSubdominioInstitucion')) {
    require_once __DIR__ . '/autodetect.php';
}

if (esSubdominioInstitucion()) {
    // Subdominio institucional: sajur.verumax.com/validare.php
    $url_validacion = obtenerURLBaseInstitucion() . "/validare.php?codigo=" . $codigo_validacion;
} else {
    // Acceso directo por certificatum: verumax.com/certificatum/validare.php
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $domain = $_SERVER['HTTP_HOST'] ?? 'verumax.com';
    $url_validacion = $protocol . $domain . "/certificatum/validare.php?codigo=" . $codigo_validacion;
}

// Configuración de la institución
$nombre_institucion = $instance_config['nombre'] ?? 'Institución';
$color_primario = $instance_config['color_primario'] ?? '#1a5276';

// Convertir color hex a RGB
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

// CRITERIO UNIFICADO PARA FECHA DEL DOCUMENTO:
// 1. Certificados: fecha de emisión (primera vez que se generó) o fecha_finalizacion_raw
// 2. Constancias: según tipo de documento
// Nota: fecha_finalizacion_raw ya viene unificada (inscripción/participación > curso > actual)
$fecha_para_documento = null;

if ($es_tipo_certificado) {
    // Para CERTIFICADOS: fecha de emisión si existe, sino fecha de finalización unificada
    if ($fecha_emision_real) {
        $fecha_para_documento = $fecha_emision_real;
    } elseif ($es_certificado_docente) {
        // Docentes: usar fecha_finalizacion_raw unificada (participación > curso)
        $fecha_para_documento = $datos['participacion']['fecha_finalizacion_raw'] ?? null;
    } else {
        // Estudiantes: usar fecha_finalizacion_raw unificada (inscripción > curso)
        $fecha_para_documento = $datos['curso']['fecha_finalizacion_raw'] ?? null;
    }
} else {
    // Para CONSTANCIAS: según tipo de documento
    switch ($tipo_documento) {
        case 'testimonium_completionis':
        case 'testimonium_doctoris':
            // Constancia de finalización/participación: fecha de finalización unificada
            $fecha_para_documento = $es_certificado_docente
                ? ($datos['participacion']['fecha_finalizacion_raw'] ?? null)
                : ($datos['curso']['fecha_finalizacion_raw'] ?? null);
            break;
        case 'testimonium_inscriptionis':
            // Constancia de inscripción: fecha de inscripción (o inicio como fallback)
            $fecha_para_documento = $datos['curso']['fecha_inscripcion_raw'] ?? $datos['curso']['fecha_inicio_raw'] ?? null;
            break;
        case 'testimonium_regulare':
            // Constancia de alumno regular: fecha actual
            $fecha_para_documento = date('Y-m-d');
            break;
        default:
            $fecha_para_documento = $es_certificado_docente
                ? ($datos['participacion']['fecha_finalizacion_raw'] ?? null)
                : ($datos['curso']['fecha_finalizacion_raw'] ?? null);
    }
}
$fecha_formateada = LanguageService::formatDate($fecha_para_documento ?? date('Y-m-d'), true);

// Calcular fecha_curso (período del curso: día único o rango)
// Usar fechas OFICIALES del curso, no de la inscripción individual
$curso_fecha_inicio = $curso['curso_fecha_inicio_raw'] ?? $curso['fecha_inicio_raw'] ?? $curso['fecha_inicio'] ?? null;
$curso_fecha_fin = $curso['curso_fecha_fin_raw'] ?? $curso['fecha_fin_raw'] ?? $curso['fecha_fin'] ?? null;
$es_dia_unico = esCursoDiaUnico($curso_fecha_inicio, $curso_fecha_fin);

if ($es_dia_unico === true) {
    // Día único: "dictado el Jueves, 15 de Noviembre de 2025"
    $fecha_curso = $t('certificatum.dictado_el', [
        'fecha' => LanguageService::formatDate($curso_fecha_inicio, true)
    ], 'dictado el ' . LanguageService::formatDate($curso_fecha_inicio, true));
} elseif ($es_dia_unico === false) {
    // Rango: "dictado del Jueves, 1 de Noviembre al 15 de Noviembre de 2025"
    // fecha_fin sin día de semana para evitar repetición
    $fecha_curso = $t('certificatum.dictado_del_al', [
        'fecha_inicio' => LanguageService::formatDate($curso_fecha_inicio, true),
        'fecha_fin' => LanguageService::formatDate($curso_fecha_fin, false)
    ], 'dictado del ' . LanguageService::formatDate($curso_fecha_inicio, true) . ' al ' . LanguageService::formatDate($curso_fecha_fin, false));
} else {
    // Sin fechas definidas en el curso
    $fecha_curso = '';
}

// Calcular lugar_fecha (frase con o sin ciudad)
// Formato: "En la ciudad de X, a los DD días del mes de MM de AAAA" o "A los DD días..."
$ciudad_emision = $curso['ciudad_emision'] ?? null;
$fecha_raw_doc = $fecha_para_documento ?? date('Y-m-d');
$fecha_ts_doc = strtotime($fecha_raw_doc);
$dia_num = date('j', $fecha_ts_doc);
$mes_nombre = LanguageService::getMonthName((int)date('n', $fecha_ts_doc));
$anio_num = date('Y', $fecha_ts_doc);

if (!empty($ciudad_emision)) {
    // Con ciudad
    $lugar_fecha = $t('certificatum.lugar_fecha_con_ciudad', [
        'ciudad' => $ciudad_emision,
        'dia' => $dia_num,
        'mes' => $mes_nombre,
        'anio' => $anio_num
    ], "En la ciudad de {$ciudad_emision}, a los {$dia_num} días del mes de {$mes_nombre} de {$anio_num}");
} else {
    // Sin ciudad
    $lugar_fecha = $t('certificatum.lugar_fecha_sin_ciudad', [
        'dia' => $dia_num,
        'mes' => $mes_nombre,
        'anio' => $anio_num
    ], "A los {$dia_num} días del mes de {$mes_nombre} de {$anio_num}");
}

// Formatear DNI con puntos
$dni_formateado = number_format((float)str_replace('.', '', $dni), 0, ',', '.');

// Variables disponibles para templates
// Verificar si usar firmantes (default: true)
$usar_firmante_1 = !isset($curso['usar_firmante_1']) || $curso['usar_firmante_1'] == 1;
$usar_firmante_2 = !isset($curso['usar_firmante_2']) || $curso['usar_firmante_2'] == 1;

// Firmantes: Prioridad curso > institución (solo si está habilitado)
$firmante_nombre = '';
$firmante_cargo = '';
if ($usar_firmante_1) {
    $firmante_nombre = !empty($curso['firmante_1_nombre'])
        ? $curso['firmante_1_nombre']
        : ($instance_config['firmante_nombre'] ?? '');
    $firmante_cargo = !empty($curso['firmante_1_cargo'])
        ? $curso['firmante_1_cargo']
        : ($instance_config['firmante_cargo'] ?? '');
}

$firmante_2_nombre = '';
$firmante_2_cargo = '';
if ($usar_firmante_2) {
    $firmante_2_nombre = !empty($curso['firmante_2_nombre'])
        ? $curso['firmante_2_nombre']
        : ($instance_config['firmante_2_nombre'] ?? '');
    $firmante_2_cargo = !empty($curso['firmante_2_cargo'])
        ? $curso['firmante_2_cargo']
        : ($instance_config['firmante_2_cargo'] ?? '');

    // Si no hay datos de firmante 2 (ni curso ni institución), desactivarlo
    if (empty($firmante_2_nombre) && empty($firmante_2_cargo)) {
        $usar_firmante_2 = false;
    }
}
$nombre_institucion_completo = $instance_config['nombre_completo'] ?? $instance_config['nombre'] ?? $nombre_institucion;

// Ruta de firma 1: prioridad curso > institución
$firma_1_path = null;
if ($usar_firmante_1) {
    // Primero intentar firma del curso
    if (!empty($curso['firmante_1_firma_url'])) {
        $curso_firma_path = __DIR__ . '/../' . ltrim($curso['firmante_1_firma_url'], '/');
        if (file_exists($curso_firma_path)) {
            $firma_1_path = $curso_firma_path;
        }
    }
    // Fallback a firma de institución
    if (!$firma_1_path) {
        $inst_firma_path = __DIR__ . '/../assets/images/firmas/' . $institucion . '_firma.png';
        if (file_exists($inst_firma_path)) {
            $firma_1_path = $inst_firma_path;
        }
    }
}

// Ruta de firma 2: prioridad curso > institución (solo si firmante 2 está habilitado)
$firma_2_path = null;
if ($usar_firmante_2) {
    // Primero intentar firma del curso
    if (!empty($curso['firmante_2_firma_url'])) {
        $curso_firma_2_path = __DIR__ . '/../' . ltrim($curso['firmante_2_firma_url'], '/');
        if (file_exists($curso_firma_2_path)) {
            $firma_2_path = $curso_firma_2_path;
        }
    }
    // Fallback a firma de institución
    if (!$firma_2_path) {
        $inst_firma_2_path = __DIR__ . '/../assets/images/firmas/' . $institucion . '_firma_2.png';
        if (file_exists($inst_firma_2_path)) {
            $firma_2_path = $inst_firma_2_path;
        }
    }
}

// Fecha formato sello (corto): "18 DIC 2025"
$meses_cortos = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
$fecha_ts = strtotime($fecha_para_documento ?? date('Y-m-d'));
$fecha_sello = date('d', $fecha_ts) . ' ' . $meses_cortos[(int)date('m', $fecha_ts) - 1] . ' ' . date('Y', $fecha_ts);

$variables = [
    'nombre_completo' => $nombre_completo,
    'dni' => $dni_formateado,
    'nombre_curso' => $nombre_curso,
    'fecha' => $fecha_formateada,
    'fecha_sello' => $fecha_sello,
    'carga_horaria' => $carga_horaria,
    'codigo_validacion' => $codigo_validacion,
    'nombre_institucion' => $nombre_institucion_completo,
    'url_validacion' => $url_validacion,
    'firmante_1_nombre' => $firmante_nombre,
    'firmante_1_cargo' => $firmante_cargo,
    'firmante_2_nombre' => $firmante_2_nombre,
    'firmante_2_cargo' => $firmante_2_cargo,
    'usar_firmante_1' => $usar_firmante_1,
    'usar_firmante_2' => $usar_firmante_2,
    'rol' => $es_certificado_docente ? ucfirst($rol ?? 'docente') : '',  // Variable para docentes
    // Nuevas variables de fechas (v2) - usan fechas OFICIALES del curso
    'fecha_curso' => $fecha_curso,
    'fecha_inicio' => $curso_fecha_inicio ? LanguageService::formatDate($curso_fecha_inicio, true) : '',
    'fecha_fin' => $curso_fecha_fin ? LanguageService::formatDate($curso_fecha_fin, false) : '',
    // Variable inteligente de lugar y fecha
    'lugar_fecha' => $lugar_fecha,
    'ciudad' => $ciudad_emision ?? '',
];

// Texto por defecto para párrafos (diferente para estudiantes y docentes)
if ($es_certificado_docente) {
    $rol_display = ucfirst($rol ?? 'docente');
    $parrafo_default = "El día {$fecha_formateada} se certifica que **{$nombre_completo}** con DNI **{$dni_formateado}** ha desempeñado una destacada labor como {$rol_display} del curso **{$nombre_curso}**, impartiendo sus conocimientos con un alto nivel de competencia.";
} else {
    $parrafo_default = "El día {$fecha_formateada} se certifica que **{$nombre_completo}** con DNI **{$dni_formateado}** ha completado y aprobado satisfactoriamente el curso **{$nombre_curso}** con una carga horaria de **{$carga_horaria}**.";
}

// ============================================================================
// DECIDIR: ¿Usar template dinámico o sistema actual (fallback)?
// ============================================================================

$usar_template_dinamico = false;
$template_config = null;

if ($id_template !== null) {
    // Intentar cargar el template
    $template = CertificateTemplateService::getById($id_template);
    if ($template && !empty($template['config'])) {
        $template_config = json_decode($template['config'], true);
        if ($template_config) {
            $usar_template_dinamico = true;
        }
    }
}

// ============================================================================
// SISTEMA DE TEMPLATES DINÁMICOS (si hay template asignado)
// ============================================================================

if ($usar_template_dinamico) {

    // Orientación del canvas
    $orientacion = $template_config['canvas']['orientation'] ?? 'landscape';
    $ancho_pagina = $orientacion === 'landscape' ? 297 : 210;
    $alto_pagina = $orientacion === 'landscape' ? 210 : 297;

    // Crear PDF
    $pdf = new TCPDF($orientacion === 'landscape' ? 'L' : 'P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('VERUMax Certificatum');
    $pdf->SetAuthor($nombre_institucion);
    $pdf->SetTitle('Certificado - ' . $nombre_completo);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->AddPage($orientacion === 'landscape' ? 'L' : 'P');

    // Imagen de fondo SOLO si el JSON lo especifica explícitamente
    // NO usar fallbacks - si el JSON no tiene background, se usa fondo blanco
    if (!empty($template_config['canvas']['background'])) {
        $bg_slug = $template['slug'] ?? 'custom';
        $bg_file = $template_config['canvas']['background'];

        // Intentar encontrar el archivo de fondo especificado
        $posibles_rutas = [
            __DIR__ . '/../assets/templates/certificados/' . $bg_slug . '/' . $bg_file,
            __DIR__ . '/../assets/templates/certificados/' . $institucion . '/' . $bg_file,
        ];

        foreach ($posibles_rutas as $bg_path) {
            if (file_exists($bg_path)) {
                $pdf->Image($bg_path, 0, 0, $ancho_pagina, $alto_pagina);
                break;
            }
        }
    }

    // Generar QR temporal
    $tempDir = sys_get_temp_dir();
    $qrFile = $tempDir . DIRECTORY_SEPARATOR . 'qr_cert_' . md5($url_validacion) . '.png';
    $phpqrcode_path = __DIR__ . '/../vendor/phpqrcode/qrlib.php';
    if (file_exists($phpqrcode_path)) {
        require_once $phpqrcode_path;
        QRcode::png($url_validacion, $qrFile, QR_ECLEVEL_L, 4);
    } else {
        $qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($url_validacion);
        $qr_content = @file_get_contents($qr_api_url);
        if ($qr_content) {
            file_put_contents($qrFile, $qr_content);
        }
    }

    // Rutas de recursos institucionales
    $logo_path_local = __DIR__ . '/../assets/images/logos/' . $institucion . '_logo.png';
    $logo_path = file_exists($logo_path_local) ? $logo_path_local : ($instance_config['logo_url'] ?? null);
    // Firma path ya definido arriba como $firma_1_path (prioridad curso > institución)
    $firma_path = $firma_1_path;

    // Factor de corrección para coordenadas
    // El editor tiene canvas de 842x595px (landscape) o 595x842px (portrait)
    // y exporta con factor 0.264583 mm/px (96dpi).
    // Para convertir a mm reales de A4, usamos la proporción correcta:
    $canvas_editor_w = $orientacion === 'landscape' ? 842 : 595;
    $canvas_editor_h = $orientacion === 'landscape' ? 595 : 842;

    // Factores de conversión separados para X e Y (por si el canvas no es proporcional)
    $px_to_mm = 1 / 0.264583;  // Convierte "mm a 96dpi" de vuelta a px del editor
    $scale_x = $ancho_pagina / $canvas_editor_w;   // 297/842 para landscape
    $scale_y = $alto_pagina / $canvas_editor_h;     // 210/595 para landscape

    // Factor combinado (px del editor -> mm del PDF)
    $coord_scale_x = $px_to_mm * $scale_x;  // ≈ 1.333 para landscape
    $coord_scale_y = $px_to_mm * $scale_y;  // ≈ 1.333 para landscape

    // Renderizar cada elemento del template
    foreach ($template_config['elements'] as $element) {
        // Verificar si el elemento pertenece a un firmante desactivado
        $element_var = $element['variable'] ?? '';
        $element_id = $element['id'] ?? '';
        $element_group = $element['group'] ?? '';

        // Saltar elementos de firmante 1 si está desactivado
        if (!$usar_firmante_1) {
            if (strpos($element_var, 'firmante_1') !== false ||
                strpos($element_var, 'firma_1') !== false ||
                strpos($element_var, '{{firma}}') !== false ||
                strpos($element_id, 'firmante-1') !== false ||
                strpos($element_id, 'firma-1') !== false ||
                $element_group === 'firmante_1') {
                continue;
            }
        }

        // Saltar elementos de firmante 2 si está desactivado
        if (!$usar_firmante_2) {
            $element_label = strtolower($element['label'] ?? '');
            $element_x = $element['x'] ?? 0;

            // Detectar por variable, id, group o label
            if (strpos($element_var, 'firmante_2') !== false ||
                strpos($element_var, 'firma_2') !== false ||
                strpos($element_id, 'firmante-2') !== false ||
                strpos($element_id, 'firma-2') !== false ||
                strpos($element_label, 'firmante 2') !== false ||
                strpos($element_label, 'firma 2') !== false ||
                $element_group === 'firmante_2') {
                continue;
            }
            // Para elementos de firma sin identificador, usar posición (firmante 2 está a la izquierda, x < 100)
            // Solo para tipos específicos de firma - no para 'image' genérico que podría ser logo u ornamento
            $tipos_firma = ['line-firma', 'firma', 'signature'];
            if (in_array($element['type'], $tipos_firma) && $element_x < 100) {
                continue;
            }
            // Para imágenes, verificar si es una imagen de firma basándonos en el label o src
            if ($element['type'] === 'image' && $element_x < 100) {
                $src = strtolower($element['src'] ?? '');
                if (strpos($element_label, 'firma') !== false || strpos($src, 'firma') !== false) {
                    continue;
                }
            }
        }

        // Aplicar factor de corrección a las coordenadas (pero NO al font-size)
        // Usar factores separados para X/width e Y/height para mantener proporciones exactas
        $x = ($element['x'] ?? 0) * $coord_scale_x;
        $y = ($element['y'] ?? 0) * $coord_scale_y;
        $width = ($element['width'] ?? 100) * $coord_scale_x;
        $height = ($element['height'] ?? 20) * $coord_scale_y;

        $type = $element['type'] ?? 'text';

        switch ($type) {
            case 'text':
            case 'title':
            case 'text-custom':
            case 'paragraph':
                // Determinar el texto a mostrar
                $texto = '';

                // Soporte i18n: obtener text_key si existe
                $text_key = $element['text_key'] ?? null;
                $text_key_docente = $element['text_key_docente'] ?? null;
                $text_key_finalizacion = $element['text_key_finalizacion'] ?? null;

                // Para párrafos y text-custom, usar texto según tipo de usuario (estudiante/docente)
                if ($type === 'paragraph' || $type === 'text-custom') {
                    // Determinar qué texto/clave usar según el tipo de certificado
                    $texto_base = null;
                    $effective_key = null;

                    if ($es_certificado_docente) {
                        // Docente: prioridad text_key_docente > text_key > text_docente > text
                        $effective_key = $text_key_docente ?: $text_key;
                        if ($effective_key) {
                            $texto_base = $t($effective_key, [], $element['text_docente'] ?? $element['text'] ?? '');
                        } elseif (!empty($element['text_docente'])) {
                            $texto_base = $element['text_docente'];
                        } elseif (!empty($element['text'])) {
                            $texto_base = $element['text'];
                        }
                    } elseif ($es_certificado_finalizacion) {
                        // Finalización: prioridad text_key_finalizacion > text_key > text_finalizacion > text
                        $effective_key = $text_key_finalizacion ?: $text_key;
                        if ($effective_key) {
                            $texto_base = $t($effective_key, [], $element['text_finalizacion'] ?? $element['text'] ?? '');
                        } elseif (!empty($element['text_finalizacion'])) {
                            $texto_base = $element['text_finalizacion'];
                        } elseif (!empty($element['text'])) {
                            $texto_base = $element['text'];
                        }
                    } else {
                        // Aprobación: prioridad text_key > text
                        if ($text_key) {
                            $texto_base = $t($text_key, [], $element['text'] ?? '');
                        } elseif (!empty($element['text'])) {
                            $texto_base = $element['text'];
                        }
                    }

                    if ($texto_base) {
                        // Reemplazar variables en el texto
                        $texto = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($variables) {
                            return $variables[$matches[1]] ?? $matches[0];
                        }, $texto_base);
                    } elseif ($type === 'paragraph') {
                        // Solo para párrafos: usar párrafo por defecto (ya diferenciado para estudiante/docente)
                        $texto = $parrafo_default;
                    } else {
                        // Para text-custom sin texto, dejar vacío
                        $texto = '';
                    }
                } elseif (!empty($element['variable'])) {
                    // Reemplazar variables {{variable}}
                    $texto = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($variables) {
                        return $variables[$matches[1]] ?? $matches[0];
                    }, $element['variable']);
                } else {
                    // Soporte i18n para elementos text/title simples
                    if ($text_key) {
                        $texto = $t($text_key, [], $element['text'] ?? '');
                    } else {
                        $texto = $element['text'] ?? '';
                    }
                    // También reemplazar variables en texto fijo
                    $texto = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($variables) {
                        return $variables[$matches[1]] ?? $matches[0];
                    }, $texto);
                }

                // Configurar fuente
                $font = $element['font'] ?? 'helvetica';
                // El editor exporta size en pt (px * 0.75)
                // Para mantener la misma proporción visual, escalar con el factor X
                $size_from_json = $element['size'] ?? 12;
                $size = $size_from_json * $coord_scale_x;  // Escalar para proporción correcta
                $style = '';
                if (!empty($element['style'])) {
                    if (strpos($element['style'], 'bold') !== false) $style .= 'B';
                    if (strpos($element['style'], 'italic') !== false) $style .= 'I';
                }

                // Mapear fuentes del editor a fuentes TCPDF
                $font_map = [
                    'Great Vibes' => 'greatvibes',
                    'Playfair Display' => 'playfairdisplay',
                    'Antic Didone' => 'anticdidone',
                    'Montserrat' => 'helvetica',
                    'Open Sans' => 'helvetica',
                    'Lora' => 'times',
                    'Josefin Sans' => 'helvetica',
                    'Source Sans 3' => 'helvetica',
                    'Cormorant Garamond' => 'cormorantgaramond',
                    'Crimson Text' => 'times',
                    'Poppins' => 'helvetica',
                    'Raleway' => 'helvetica',
                    'Roboto' => 'helvetica'
                ];
                $tcpdf_font = $font_map[$font] ?? 'helvetica';

                // Fuentes con variantes bold/italic disponibles en TCPDF estándar
                // playfairdisplay tiene variantes instaladas: playfairdisplayb, playfairdisplayi, playfairdisplaybi
                $fuentes_con_variantes = ['helvetica', 'times', 'courier', 'dejavusans', 'freesans', 'playfairdisplay'];

                // Si se necesita bold/italic y la fuente no lo soporta, usar helvetica como fallback
                $font_final = $tcpdf_font;
                if (!empty($style) && !in_array($tcpdf_font, $fuentes_con_variantes)) {
                    $font_final = 'helvetica';
                }

                $pdf->SetFont($font_final, $style, $size);

                // Color
                if (!empty($element['color'])) {
                    $rgb = hexToRgb($element['color']);
                    $pdf->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
                } else {
                    $pdf->SetTextColor(0, 0, 0);
                }

                // Alineación
                $align = 'L';
                if (!empty($element['align'])) {
                    $align = strtoupper(substr($element['align'], 0, 1));
                }

                // Posicionar y escribir
                // Usar height basado en font-size para mejor posicionamiento
                // El height del JSON escalado puede causar problemas en TCPDF
                $line_height = $size * 0.35;  // Aproximadamente 1pt = 0.35mm
                $pdf->SetXY($x, $y);

                // Verificar si el texto tiene marcadores de formato
                if (hasFormatMarkers($texto)) {
                    // Convertir marcadores a HTML y usar writeHTMLCell
                    $texto_html = applyTextFormatting($texto);

                    // Fuentes con variantes bold/italic disponibles en TCPDF
                    $fuentes_con_bold = ['helvetica', 'times', 'courier', 'dejavusans', 'freesans'];

                    // Si la fuente no tiene variante bold, usar helvetica para el HTML con negritas
                    $font_para_html = in_array($tcpdf_font, $fuentes_con_bold) ? $tcpdf_font : 'helvetica';

                    $pdf->SetFont($font_para_html, $style, $size);

                    // Color del texto
                    if (!empty($element['color'])) {
                        $rgb = hexToRgb($element['color']);
                        $pdf->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
                    }

                    // writeHTMLCell usa la fuente/color ya configurados
                    $pdf->writeHTMLCell($width, $height, $x, $y, $texto_html, 0, 1, false, true, strtoupper(substr($element['align'] ?? 'L', 0, 1)));
                } else {
                    // Sin formato - usar método tradicional (más rápido)
                    if ($type === 'paragraph' || strlen($texto) > 80) {
                        $pdf->MultiCell($width, $line_height, $texto, 0, $align, false, 1);
                    } else {
                        $pdf->Cell($width, $line_height, $texto, 0, 0, $align);
                    }
                }
                break;

            case 'qr':
                if (file_exists($qrFile)) {
                    $qr_size = min($width, $height);
                    $pdf->Image($qrFile, $x, $y, $qr_size, $qr_size, 'PNG');
                }
                break;

            case 'logo':
                // isValidImagePath ya maneja URLs y archivos locales
                if (isValidImagePath($logo_path)) {
                    drawImageCentered($pdf, $logo_path, $x, $y, $width, $height);
                }
                break;

            case 'firma':
            case 'signature':
                if (file_exists($firma_path)) {
                    $pdf->Image($firma_path, $x, $y, $width, $height, '', '', '', false, 300, '', false, false, 0);
                }
                break;

            case 'image':
                // Imagen - puede ser con src o con variable
                $variable = $element['variable'] ?? null;

                if ($variable === '{{firma_1}}' || $variable === '{{firma}}') {
                    // Firma institucional 1
                    if (file_exists($firma_path)) {
                        $pdf->Image($firma_path, $x, $y, $width, $height, '', '', '', false, 300, '', false, false, 0);
                    }
                } elseif ($variable === '{{firma_2}}') {
                    // Firma institucional 2
                    if (file_exists($firma_2_path)) {
                        $pdf->Image($firma_2_path, $x, $y, $width, $height, '', '', '', false, 300, '', false, false, 0);
                    }
                } elseif ($variable === '{{logo}}' || $variable === '{{logo_1}}') {
                    // Logo institucional - centrar como object-fit: contain
                    // isValidImagePath ya maneja URLs y archivos locales
                    if (isValidImagePath($logo_path)) {
                        drawImageCentered($pdf, $logo_path, $x, $y, $width, $height);
                    }
                } elseif ($variable === '{{logo_2}}') {
                    // Logo institucional 2 - centrar como object-fit: contain
                    $logo_2_path = __DIR__ . '/../assets/images/logos/' . $institucion . '_logo_2.png';
                    if (file_exists($logo_2_path)) {
                        drawImageCentered($pdf, $logo_2_path, $x, $y, $width, $height);
                    }
                } elseif ($variable === '{{logo_verumax}}') {
                    // Logo Verumax - centrar como object-fit: contain
                    $logo_verumax_path = __DIR__ . '/../assets/images/logo-verumax-escudo.png';
                    if (file_exists($logo_verumax_path)) {
                        drawImageCentered($pdf, $logo_verumax_path, $x, $y, $width, $height);
                    }
                } elseif (!empty($element['src'])) {
                    // Imagen con ruta específica - centrar como object-fit: contain
                    $img_path = __DIR__ . '/../' . ltrim($element['src'], '/');
                    if (file_exists($img_path)) {
                        $img_rotation = $element['rotation'] ?? 0;
                        $img_opacity = $element['opacity'] ?? 1;

                        // Aplicar opacidad si es menor a 1
                        if ($img_opacity < 1) {
                            $pdf->SetAlpha($img_opacity);
                        }

                        // Aplicar rotación si no es 0
                        if ($img_rotation != 0) {
                            $pdf->StartTransform();
                            $center_x = $x + $width / 2;
                            $center_y = $y + $height / 2;
                            $pdf->Rotate($img_rotation * -1, $center_x, $center_y);
                        }

                        drawImageCentered($pdf, $img_path, $x, $y, $width, $height);

                        // Restaurar transformaciones
                        if ($img_rotation != 0) {
                            $pdf->StopTransform();
                        }
                        if ($img_opacity < 1) {
                            $pdf->SetAlpha(1);
                        }
                    }
                }
                break;

            case 'line':
            case 'decorative-line':
                // Línea decorativa
                $color = hexToRgb($element['color'] ?? '#000000');
                $pdf->SetDrawColor($color[0], $color[1], $color[2]);
                $line_width = $element['thickness'] ?? $element['lineWidth'] ?? 0.5;
                $pdf->SetLineWidth($line_width);
                $pdf->Line($x, $y + ($height / 2), $x + $width, $y + ($height / 2));
                break;

            case 'line-firma':
                // Línea de firma - línea horizontal simple
                $color = hexToRgb($element['color'] ?? '#333333');
                $pdf->SetDrawColor($color[0], $color[1], $color[2]);
                $line_width = ($element['thickness'] ?? 1) * 0.264583;  // Convertir a mm
                $pdf->SetLineWidth($line_width);
                // La línea va en la parte inferior del área asignada
                $pdf->Line($x, $y + $height - 1, $x + $width, $y + $height - 1);
                break;

            case 'decorative-image':
                // Imagen decorativa (ornamentos, separadores) - centrar como object-fit: contain
                if (!empty($element['src'])) {
                    $img_path = __DIR__ . '/../assets/templates/certificados/' . ltrim($element['src'], '/');
                    if (file_exists($img_path)) {
                        drawImageCentered($pdf, $img_path, $x, $y, $width, $height);
                    }
                }
                break;

            case 'stamp':
                // Sello con texto tipo máquina de escribir
                $stamp_text = $element['text'] ?? '{{fecha_sello}}';
                $stamp_color = $element['color'] ?? '#1e40af';
                $stamp_size = ($element['size'] ?? 9) * $coord_scale_x;
                $stamp_rotation = $element['rotation'] ?? -3;
                $stamp_opacity = $element['opacity'] ?? 0.85;
                $stamp_border_px = $element['borderWidth'] ?? 2;
                $stamp_border = $stamp_border_px * 0.264583;  // Convertir a mm

                // Reemplazar variables en el texto
                $stamp_display = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($variables) {
                    return $variables[$matches[1]] ?? $matches[0];
                }, $stamp_text);

                // Aplicar color
                $color_rgb = hexToRgb($stamp_color);
                $pdf->SetDrawColor($color_rgb[0], $color_rgb[1], $color_rgb[2]);
                $pdf->SetTextColor($color_rgb[0], $color_rgb[1], $color_rgb[2]);

                // Guardar posición y aplicar rotación
                $pdf->StartTransform();
                // Rotar alrededor del centro del elemento
                $center_x = $x + $width / 2;
                $center_y = $y + $height / 2;
                $pdf->Rotate($stamp_rotation * -1, $center_x, $center_y);

                // Dibujar borde del sello solo si borderWidth > 0
                if ($stamp_border_px > 0) {
                    $pdf->SetLineWidth($stamp_border);
                    $pdf->Rect($x, $y, $width, $height, 'D');
                }

                // Dibujar texto centrado con fuente tipo sello
                $pdf->SetFont('specialelite', '', $stamp_size);
                $pdf->SetXY($x, $y);
                $pdf->Cell($width, $height, strtoupper($stamp_display), 0, 0, 'C', false, '', 0, false, 'T', 'C');

                $pdf->StopTransform();

                // Restaurar color negro por defecto
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetDrawColor(0, 0, 0);
                break;

            case 'rect':
                // Rectángulo
                $color = hexToRgb($element['color'] ?? '#000000');
                $pdf->SetFillColor($color[0], $color[1], $color[2]);
                $pdf->Rect($x, $y, $width, $height, 'F');
                break;
        }
    }

    // Limpiar QR temporal
    if (file_exists($qrFile)) {
        @unlink($qrFile);
    }

} else {

    // ============================================================================
    // SISTEMA ACTUAL - FALLBACK (código original sin cambios)
    // ============================================================================

    $color_rgb = hexToRgb($color_primario);

    // Rutas de archivos
    $template_path = __DIR__ . '/../assets/templates/certificados/' . $institucion . '/template_clasico.jpg';
    // Firma path ya definido arriba como $firma_1_path (prioridad curso > institución)
    $firma_path = $firma_1_path;
    $logo_path_local = __DIR__ . '/../assets/images/logos/' . $institucion . '_logo.png';
    $logo_path = file_exists($logo_path_local) ? $logo_path_local : ($instance_config['logo_url'] ?? null);

    // Crear PDF en orientación horizontal (landscape)
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('VERUMax Certificatum');
    $pdf->SetAuthor($nombre_institucion);
    $pdf->SetTitle('Certificado - ' . $nombre_completo);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->AddPage('L');

    // Dimensiones de página A4 landscape
    $ancho_pagina = 297; // mm
    $alto_pagina = 210;  // mm

    // Colocar imagen de fondo si existe
    if (file_exists($template_path)) {
        $pdf->Image($template_path, 0, 0, $ancho_pagina, $alto_pagina, 'JPG');
    }

    // --- TEXTOS DINÁMICOS ---
    // Conversión desde CSS de la versión web
    // Web: 1122px x 793px -> PDF: 297mm x 210mm
    // Factor posición: ~0.265 mm/px
    //
    // CSS Web (referencia):
    //   .cert-curso: top: 158px, font: 22px italic 600, color: #1a5276
    //   .cert-tipo: top: 330px, intro: 15px #333, tipo: 16px 600 #1a5276
    //   .cert-nombre: top: 415px, font: Great Vibes 52px, color: #7d6608
    //   .cert-descripcion: top: 510px, font: 17px, color: #333
    //   .cert-qr: bottom: 70px, size: 90px

    // Nombre del curso (debajo de "Sociedad Argentina de Justicia Restaurativa")
    // CSS: top: 158px -> 42mm, font: 22px italic
    $pdf->SetFont('times', 'BI', 12);
    $pdf->SetTextColor(26, 82, 118); // #1a5276
    $pdf->SetXY(0, 42);
    $pdf->Cell($ancho_pagina, 6, $nombre_curso, 0, 1, 'C');

    // Tipo de certificado (debajo de línea dorada de "CERTIFICADO")
    // CSS: top: 330px -> 87mm
    if ($es_certificado_docente) {
        $tipo_cert_texto = $t('certificatum.cert_type_trainer', [], 'Certificado de Formador/a');
    } elseif ($es_certificado_finalizacion) {
        $tipo_cert_texto = $t('certificatum.cert_type_completion', [], 'Certificado de Finalización');
    } else {
        $tipo_cert_texto = $t('certificatum.cert_type_approval', [], 'Certificado de Aprobación');
    }

    // "Por la presente..." - CSS: font-size: 15px, color: #333
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(51, 51, 51); // #333
    $pdf->SetXY(0, 87);
    $intro_texto = $t('certificatum.cert_hereby_grants', ['institucion' => $nombre_institucion], 'Por la presente, ' . $nombre_institucion . ' otorga el presente');
    $pdf->Cell($ancho_pagina, 5, $intro_texto, 0, 1, 'C');

    // "Certificado de Aprobación" - CSS: font-size: 16px, font-weight: 600, color: #1a5276
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(26, 82, 118); // #1a5276
    $pdf->SetXY(0, 93);
    $pdf->Cell($ancho_pagina, 5, $tipo_cert_texto, 0, 1, 'C');

    // Nombre del destinatario
    // CSS: top: 415px -> 110mm, font: Great Vibes 52px, color: #7d6608
    // Usar fuente Great Vibes (ya convertida con TCPDF_FONTS::addTTFfont)
    $pdf->SetFont('greatvibes', '', 40);
    $pdf->SetTextColor(125, 102, 8); // #7d6608 dorado
    $pdf->SetXY(0, 112);
    $pdf->Cell($ancho_pagina, 18, $nombre_completo, 0, 1, 'C');

    // Descripción - usar traducciones
    // CSS: top: 510px -> 135mm, font-size: 17px, color: #333
    $desc_params = [
        'fecha' => $fecha_formateada,
        'nombre' => $nombre_completo,
        'dni' => $dni_formateado,
        'nombre_curso' => $nombre_curso,
        'carga_horaria' => $carga_horaria,
        'formador' => $rol ?? 'Formador/a'
    ];

    if ($es_certificado_docente) {
        $texto_descripcion = $t('certificatum.cert_desc_trainer', $desc_params,
            "El día " . $fecha_formateada . " se certifica que " . $nombre_completo . " con DNI " . $dni_formateado . " ha desempeñado una destacada labor como formador/a de \"" . $nombre_curso . "\", impartiendo sus conocimientos con un alto nivel de competencia.");
    } elseif ($es_certificado_finalizacion) {
        $texto_descripcion = $t('certificatum.cert_desc_completion', $desc_params,
            "El día " . $fecha_formateada . " se certifica que " . $nombre_completo . " con DNI " . $dni_formateado . " ha completado satisfactoriamente el curso \"" . $nombre_curso . "\" con una carga horaria de " . $carga_horaria . " horas.");
    } else {
        $texto_descripcion = $t('certificatum.cert_desc_approval', $desc_params,
            "El día " . $fecha_formateada . " se certifica que " . $nombre_completo . " con DNI " . $dni_formateado . " ha completado y aprobado satisfactoriamente el curso \"" . $nombre_curso . "\" con una carga horaria de " . $carga_horaria . " horas.");
    }

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(51, 51, 51); // #333
    $pdf->SetXY(21, 135); // left: 80px -> 21mm
    $pdf->MultiCell($ancho_pagina - 42, 5, $texto_descripcion, 0, 'C', false, 1);

    // Generar QR temporal
    $tempDir = sys_get_temp_dir();
    $qrFile = $tempDir . DIRECTORY_SEPARATOR . 'qr_cert_' . md5($url_validacion) . '.png';

    // Usar QRCodeService o biblioteca externa para generar QR
    // Si tienes phpqrcode:
    $phpqrcode_path = __DIR__ . '/../vendor/phpqrcode/qrlib.php';
    if (file_exists($phpqrcode_path)) {
        require_once $phpqrcode_path;
        QRcode::png($url_validacion, $qrFile, QR_ECLEVEL_L, 4);
    } else {
        // Alternativa: usar API externa para QR
        $qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($url_validacion);
        $qr_content = @file_get_contents($qr_api_url);
        if ($qr_content) {
            file_put_contents($qrFile, $qr_content);
        }
    }

    // Colocar QR en el centro inferior (entre logo y firma)
    // CSS: bottom: 70px, size: 90px
    if (file_exists($qrFile)) {
        $qr_size = 22; // mm
        $qr_x = ($ancho_pagina / 2) - ($qr_size / 2);
        $qr_y = 158; // Ajustado visualmente
        $pdf->Image($qrFile, $qr_x, $qr_y, $qr_size, $qr_size, 'PNG');

        // Texto debajo del QR - CSS: font-size: 8px, color: #666
        $pdf->SetFont('helvetica', '', 6);
        $pdf->SetTextColor(102, 102, 102); // #666
        $pdf->SetXY(($ancho_pagina / 2) - 18, $qr_y + $qr_size + 1);
        $pdf->Cell(36, 3, $t('certificatum.validate_certificate', [], 'Validar certificado'), 0, 1, 'C');
    }

    // Limpiar archivo QR temporal
    if (file_exists($qrFile)) {
        @unlink($qrFile);
    }
}

// ============================================================================
// MARCA DE AGUA PARA INSTANCIAS TEST
// ============================================================================

if ($es_instancia_test) {
    // Guardar estado actual
    $pdf->SetAlpha(0.25);
    $pdf->SetFont('helvetica', 'B', 60);
    $pdf->SetTextColor(220, 38, 38); // Rojo

    // Calcular posición central del documento
    $pageWidth = $pdf->getPageWidth();
    $pageHeight = $pdf->getPageHeight();

    // Rotar y dibujar "NO VÁLIDO"
    $pdf->StartTransform();
    $pdf->Rotate(-35, $pageWidth / 2, $pageHeight / 2);
    $pdf->SetXY(0, $pageHeight / 2 - 15);
    $pdf->Cell($pageWidth, 30, 'NO VÁLIDO', 0, 0, 'C');
    $pdf->StopTransform();

    // Texto secundario más pequeño
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetAlpha(0.35);
    $pdf->StartTransform();
    $pdf->Rotate(-35, $pageWidth / 2, $pageHeight / 2 + 25);
    $pdf->SetXY(0, $pageHeight / 2 + 10);
    $pdf->Cell($pageWidth, 20, 'DOCUMENTO DE PRUEBA', 0, 0, 'C');
    $pdf->StopTransform();

    // Restaurar estado
    $pdf->SetAlpha(1);
    $pdf->SetTextColor(0, 0, 0);
}

// ============================================================================
// SALIDA DEL PDF
// ============================================================================

// Limpiar buffer y enviar PDF
while (ob_get_level()) {
    ob_end_clean();
}

// Generar nombre del archivo
$nombre_archivo = 'Certificado_' . preg_replace('/[^A-Za-z0-9]/', '_', $nombre_completo) . '.pdf';

$pdf->Output($nombre_archivo, 'I'); // 'I' = inline (mostrar en navegador), 'D' = download
