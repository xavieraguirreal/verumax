<?php
/**
 * Creare - Generación de Documentos PDF
 * Sistema CERTIFICATUM - VERUMax
 * Versión: 3.2 - Inicialización consolidada
 */

// Cargar inicialización común (validación whitelist, idioma, config)
require_once __DIR__ . '/init.php';
extract(initCertificatum());

use VERUMax\Services\StudentService;
use VERUMax\Services\CertificateService;
use VERUMax\Services\CertificateTemplateService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\QRCodeService;

/**
 * Convierte marcadores de formato a HTML
 * **texto** -> <b>texto</b>
 * *texto* -> <i>texto</i>
 */
function applyTextFormatting($text) {
    if (empty($text)) return $text;
    // Primero escapar HTML para seguridad
    $result = htmlspecialchars($text);
    // Negrita: **texto** -> <b>texto</b>
    $result = preg_replace('/\*\*(.+?)\*\*/s', '<b>$1</b>', $result);
    // Italica: *texto* -> <i>texto</i>
    $result = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/s', '<i>$1</i>', $result);
    return $result;
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

// 3. Obtener parámetros de la URL
$dni = $_GET['documentum'] ?? null;
$curso_id = $_GET['cursus'] ?? null;
$participacion_id = $_GET['participacion'] ?? null;
$tipo_documento = $_GET['genus'] ?? 'analyticum';

// ============================================================
// MODO PREVIEW: Generar certificado con datos ficticios
// Uso: ?preview=1&template_id=X&institutio=sajur
// ============================================================
$es_modo_preview = isset($_GET['preview']) && $_GET['preview'] == '1';
$preview_template_id = $_GET['template_id'] ?? null;

if ($es_modo_preview && $preview_template_id) {
    // Obtener el template
    $template_json_data = CertificateTemplateService::getById((int)$preview_template_id);

    if (!$template_json_data || empty($template_json_data['config'])) {
        die("Error: Template no encontrado o sin configuración.");
    }

    // Nombre del curso (desde parámetro o genérico)
    $preview_curso_nombre = $_GET['curso_nombre'] ?? 'Nombre del Curso de Ejemplo';

    // Datos ficticios para preview
    $alumno = [
        'nombre_completo' => 'Nombre del Participante',
        'genero' => 'Femenino'
    ];

    $curso = [
        'nombre_curso' => $preview_curso_nombre,
        'carga_horaria' => '40',
        'fecha_finalizacion_raw' => date('Y-m-d'),
        'fecha_inscripcion_raw' => date('Y-m-d', strtotime('-30 days')),
        'fecha_inicio_raw' => date('Y-m-d', strtotime('-30 days')),
        'curso_fecha_inicio_raw' => date('Y-m-d', strtotime('-30 days')),
        'curso_fecha_fin_raw' => date('Y-m-d'),
        'ciudad_emision' => $instance_config['ciudad'] ?? 'Ciudad de Ejemplo',
        'estado' => 'Aprobado',
        'nota_final' => '9.50',
        'asistencia' => '95%',
        'id_inscripcion' => 0,
        'firmante_1_nombre' => $instance_config['firmante_nombre'] ?? 'Nombre del Firmante',
        'firmante_1_cargo' => $instance_config['firmante_cargo'] ?? 'Cargo del Firmante',
        'firmante_2_nombre' => $instance_config['firmante_2_nombre'] ?? '',
        'firmante_2_cargo' => $instance_config['firmante_2_cargo'] ?? '',
        'usar_firmante_1' => 1,
        'usar_firmante_2' => !empty($instance_config['firmante_2_nombre']) ? 1 : 0,
    ];

    $dni = '00.000.000';
    $curso_id = 'PREVIEW-001';
    $tipo_documento = 'certificatum_approbationis';
    $es_certificado_docente = false;
    $participacion = null;
    $docente = null;

    // Variables de género para preview
    $genero_persona = $alumno['genero'] ?? 'Femenino';
    $currentLang = LanguageService::getCurrentLang();
    if ($currentLang === 'pt_BR') {
        $aprobado_texto = LanguageService::getGenderedText($genero_persona, 'aprovad', 'sufijo_o');
        $inscripto_texto = LanguageService::getGenderedText($genero_persona, 'matriculad', 'sufijo_o');
    } else {
        $aprobado_texto = LanguageService::getGenderedText($genero_persona, 'aprobad', 'sufijo_o');
        $inscripto_texto = LanguageService::getGenderedText($genero_persona, 'inscript', 'sufijo_o');
    }

    // Firma de institución para preview
    $firma_url = null;
    $firma_path_absolute = __DIR__ . '/../assets/images/firmas/' . $institucion . '_firma.png';
    if (file_exists($firma_path_absolute)) {
        $firma_url = 'data:image/png;base64,' . base64_encode(file_get_contents($firma_path_absolute));
    }

    // Código de validación ficticio para preview
    $codigo_unico_validacion = 'PREVIEW-0000';

    // Flag para ocultar elementos no deseados en preview
    $es_preview_mode = true;

    // QR genérico para preview - apunta a verumax.com
    $url_validacion = 'https://verumax.com';
    $qr_url = QRCodeService::generate($url_validacion, 100);

    // =====================================================
    // VARIABLES NECESARIAS PARA EL ARRAY $variables
    // (Estas variables se definen normalmente en el flujo regular,
    // pero el goto las salta, así que las definimos aquí)
    // =====================================================

    // Nombre formateado del alumno y curso
    $nombre_alumno = htmlspecialchars(mb_convert_case(strtolower($alumno['nombre_completo']), MB_CASE_TITLE, 'UTF-8'));
    $nombre_curso = htmlspecialchars($curso['nombre_curso']);

    // DNI formateado con puntos
    $dni_formateado = '00.000.000';

    // Nombre de la institución
    $nombre_institucion_completo = $instance_config['nombre_completo'] ?? $instance_config['nombre'] ?? 'Institución';

    // Fecha para el documento (fecha actual para preview)
    $fecha_para_documento = date('Y-m-d');
    $fecha_formateada = LanguageService::formatDate($fecha_para_documento, true);

    // Fecha sello (formato corto: "10 ENE 2026")
    $meses_cortos = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
    $fecha_ts = strtotime($fecha_para_documento);
    $fecha_sello = date('d', $fecha_ts) . ' ' . $meses_cortos[(int)date('m', $fecha_ts) - 1] . ' ' . date('Y', $fecha_ts);

    // Fechas del curso (para {{fecha_curso}})
    $curso_fecha_inicio = $curso['curso_fecha_inicio_raw'];
    $curso_fecha_fin = $curso['curso_fecha_fin_raw'];

    if ($curso_fecha_inicio && $curso_fecha_fin && $curso_fecha_inicio === $curso_fecha_fin) {
        // Curso de un solo día
        $fecha_curso = $t('certificatum.dictado_el', [
            'fecha' => LanguageService::formatDate($curso_fecha_inicio, true)
        ], 'dictado el ' . LanguageService::formatDate($curso_fecha_inicio, true));
    } elseif ($curso_fecha_inicio && $curso_fecha_fin) {
        // Curso con rango de fechas
        $fecha_curso = $t('certificatum.dictado_del_al', [
            'fecha_inicio' => LanguageService::formatDate($curso_fecha_inicio, true),
            'fecha_fin' => LanguageService::formatDate($curso_fecha_fin, false)
        ], 'dictado del ' . LanguageService::formatDate($curso_fecha_inicio, true) . ' al ' . LanguageService::formatDate($curso_fecha_fin, false));
    } else {
        $fecha_curso = '';
    }

    // Ciudad de emisión y lugar_fecha
    $ciudad_emision = $curso['ciudad_emision'] ?? null;
    $fecha_ts_lugar = strtotime($fecha_para_documento);
    $dia_lugar = date('d', $fecha_ts_lugar);
    $mes_lugar = LanguageService::getMonthName((int)date('m', $fecha_ts_lugar));
    $anio_lugar = date('Y', $fecha_ts_lugar);

    if (!empty($ciudad_emision)) {
        $lugar_fecha = $t('certificatum.lugar_fecha_con_ciudad', [
            'ciudad' => $ciudad_emision,
            'dia' => $dia_lugar,
            'mes' => $mes_lugar,
            'anio' => $anio_lugar
        ], "En la ciudad de {$ciudad_emision}, a los {$dia_lugar} días del mes de {$mes_lugar} de {$anio_lugar}");
    } else {
        $lugar_fecha = $t('certificatum.lugar_fecha_sin_ciudad', [
            'dia' => $dia_lugar,
            'mes' => $mes_lugar,
            'anio' => $anio_lugar
        ], "A los {$dia_lugar} días del mes de {$mes_lugar} de {$anio_lugar}");
    }

    // Firmantes
    $usar_firmante_1 = true;
    $usar_firmante_2 = !empty($instance_config['firmante_2_nombre']);
    $firmante_nombre = $curso['firmante_1_nombre'] ?? $instance_config['firmante_nombre'] ?? 'Nombre del Firmante';
    $firmante_cargo = $curso['firmante_1_cargo'] ?? $instance_config['firmante_cargo'] ?? 'Cargo del Firmante';
    $firmante_2_nombre = $curso['firmante_2_nombre'] ?? $instance_config['firmante_2_nombre'] ?? '';
    $firmante_2_cargo = $curso['firmante_2_cargo'] ?? $instance_config['firmante_2_cargo'] ?? '';

    // Firma 2 URL (si existe)
    $firma_2_url = null;
    $firma_2_path = __DIR__ . '/../assets/images/firmas/' . $institucion . '_firma_2.png';
    if (file_exists($firma_2_path)) {
        $firma_2_url = 'data:image/png;base64,' . base64_encode(file_get_contents($firma_2_path));
    }

    // Saltar a la generación del certificado (ir directamente al bloque de template JSON)
    goto render_certificate;
}

// 4. Validar y buscar datos
// Tipos de documentos para docentes: certificatum (certificado) y testimonium (constancia)
$tipos_documento_docente = ['certificatum_doctoris', 'certificatum_docente', 'testimonium_doctoris'];
$es_certificado_docente = (in_array($tipo_documento, $tipos_documento_docente) && $participacion_id);

if ($es_certificado_docente) {
    // Obtener datos de participación docente
    $datos = StudentService::getParticipacionDocente($institucion, $dni, (int)$participacion_id);
    if (!$datos) {
        die("Error: Datos de participación docente no encontrados.");
    }
    $docente = [
        'nombre_completo' => $datos['nombre_completo'],
        'dni' => $datos['dni'],
        'genero' => $datos['genero'] ?? 'Prefiero no especificar',
        'especialidad' => $datos['especialidad'] ?? '',
        'titulo' => $datos['titulo'] ?? ''
    ];
    $participacion = $datos['participacion'];

    // Verificar estado de la participación
    $estado_participacion = $participacion['estado'] ?? 'Asignado';

    // Bloquear acceso a certificado si no está completado
    $es_tipo_certificado = in_array($tipo_documento, ['certificatum_doctoris', 'certificatum_docente']);
    if ($es_tipo_certificado && $estado_participacion !== 'Completado') {
        // Redirigir a la constancia en lugar del certificado
        $mensaje_estado = match($estado_participacion) {
            'Asignado' => 'El certificado estará disponible cuando se complete la participación. Por ahora puede acceder a su Constancia de Asignación.',
            'En curso' => 'El certificado estará disponible al finalizar el curso. Por ahora puede acceder a su Constancia de Participación.',
            default => 'El certificado no está disponible aún.'
        };
        die("
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Certificado No Disponible</title>
            <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-gray-100 min-h-screen flex items-center justify-center p-4'>
            <div class='bg-white rounded-2xl shadow-xl max-w-md w-full p-8 text-center'>
                <div class='w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4'>
                    <svg class='w-8 h-8 text-amber-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path>
                    </svg>
                </div>
                <h1 class='text-xl font-bold text-gray-900 mb-2'>Certificado No Disponible</h1>
                <p class='text-gray-600 mb-6'>$mensaje_estado</p>
                <div class='flex gap-3 justify-center'>
                    <a href='javascript:history.back()' class='px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition'>
                        Volver
                    </a>
                    <a href='creare.php?institutio=$institucion&documentum=$dni&participacion=$participacion_id&genus=testimonium_doctoris'
                       class='px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition'>
                        Ver Constancia
                    </a>
                </div>
            </div>
        </body>
        </html>
        ");
    }

    // Determinar tipo de documento según estado:
    // - Asignado: Constancia de Asignación
    // - En curso: Constancia de Participación (provisional)
    // - Completado: Certificado de Participación (final)
    $es_constancia_asignacion = ($estado_participacion === 'Asignado');
    $es_constancia_en_curso = ($estado_participacion === 'En curso');
    $es_constancia_provisional = ($estado_participacion !== 'Completado'); // Asignado o En curso

    $alumno = ['nombre_completo' => $datos['nombre_completo'], 'genero' => $docente['genero']]; // Para compatibilidad
    // Fecha unificada: participación > curso > actual
    $fecha_fin_unificada = $participacion['fecha_finalizacion_raw'] ?? date('Y-m-d');

    $curso = [
        'nombre_curso' => $participacion['nombre_curso'],
        'carga_horaria' => $participacion['carga_horaria_dictada'] ?? $participacion['carga_horaria'],
        'fecha_finalizacion' => $fecha_fin_unificada ? date('d/m/Y', strtotime($fecha_fin_unificada)) : date('d/m/Y'),
        'fecha_finalizacion_raw' => $fecha_fin_unificada,
        'fecha_inicio' => $participacion['fecha_inicio'] ? date('d/m/Y', strtotime($participacion['fecha_inicio'])) : null,
        'firmante_1_nombre' => $participacion['firmante_1_nombre'] ?? null,
        'firmante_1_cargo' => $participacion['firmante_1_cargo'] ?? null,
        'firmante_2_nombre' => $participacion['firmante_2_nombre'] ?? null,
        'firmante_2_cargo' => $participacion['firmante_2_cargo'] ?? null,
        'usar_firmante_1' => $participacion['usar_firmante_1'] ?? 1,
        'usar_firmante_2' => $participacion['usar_firmante_2'] ?? 1,
        // Ciudad de emisión y fechas del curso (para {{lugar_fecha}} y {{fecha_curso}})
        'ciudad_emision' => $participacion['ciudad_emision'] ?? null,
        'curso_fecha_inicio_raw' => $participacion['curso_fecha_inicio_raw'] ?? null,
        'curso_fecha_fin_raw' => $participacion['curso_fecha_fin_raw'] ?? null
    ];
    $curso_id = $participacion['codigo_curso'];

    // Obtener template JSON del curso para docentes (mismo que estudiantes)
    $template_json_data = null;
    try {
        $template_json_data = CertificateTemplateService::getForCurso(
            $instance_config['id_instancia'] ?? 0,
            $curso_id
        );
    } catch (Exception $e) {
        $template_json_data = null;
    }
} else {
    // Flujo normal para estudiantes
    $datos = StudentService::getCourse($institucion, $dni, $curso_id);

    if (!$datos) {
        die("Error: Datos no encontrados para generar el documento.");
    }

    $alumno = [
        'nombre_completo' => $datos['nombre_completo'],
        'genero' => $datos['genero'] ?? 'Prefiero no especificar'
    ];
    $curso = $datos['curso'];
    $es_constancia_provisional = false; // Solo aplica para docentes

    // Obtener template JSON del curso (si existe)
    $template_json_data = null;
    try {
        $template_json_data = CertificateTemplateService::getForCurso(
            $instance_config['id_instancia'] ?? 0,
            $curso_id
        );
    } catch (Exception $e) {
        // Si falla, usar fallback
        $template_json_data = null;
    }

    // Validar disponibilidad del certificado en tiempo real (para estudiantes)
    if ($tipo_documento === 'certificatum_approbationis') {
        // Calcular disponibilidad basada en config actual del curso
        $fecha_finalizacion_raw = $curso['fecha_finalizacion_raw'] ?? null;
        if ($fecha_finalizacion_raw) {
            $disponibilidad = InstitutionService::calcularDisponibilidadCertificado(
                $fecha_finalizacion_raw,
                $curso,
                $instance_config
            );

            if (!$disponibilidad['disponible']) {
                $fecha_formateada = $disponibilidad['fecha_disponible']->format('d/m/Y \a \l\a\s H:i');
                die("
                <!DOCTYPE html>
                <html lang='es'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Certificado No Disponible</title>
                    <script src='https://cdn.tailwindcss.com'></script>
                </head>
                <body class='bg-gray-100 min-h-screen flex items-center justify-center p-4'>
                    <div class='bg-white rounded-2xl shadow-xl max-w-md w-full p-8 text-center'>
                        <div class='w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4'>
                            <svg class='w-8 h-8 text-blue-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'></path>
                            </svg>
                        </div>
                        <h1 class='text-xl font-bold text-gray-900 mb-2'>Certificado en Proceso</h1>
                        <p class='text-gray-600 mb-4'>Tu certificado esta siendo procesado por el equipo docente.</p>
                        <p class='text-gray-700 font-semibold mb-6'>Estara disponible el <br><span class='text-blue-600'>$fecha_formateada</span></p>
                        <p class='text-sm text-gray-500 mb-6'>Cuando llegue la fecha indicada, podras ingresar al portal para descargarlo.</p>
                        <a href='javascript:history.back()' class='inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition'>
                            Volver
                        </a>
                    </div>
                </body>
                </html>
                ");
            }
        }
    }
}

// 5. Preparar variables comunes
// Convertir nombre a formato título (primera letra mayúscula de cada palabra)
$nombre_alumno = htmlspecialchars(mb_convert_case(strtolower($alumno['nombre_completo']), MB_CASE_TITLE, 'UTF-8'));
$nombre_curso = htmlspecialchars($curso['nombre_curso']);

// Generar o recuperar código de validación usando CertificateService
if ($es_certificado_docente) {
    // Para docentes, usar el código de validación guardado o generar uno nuevo
    $codigo_unico_validacion = $participacion['codigo_validacion']
        ?? CertificateService::getValidationCode($institucion, $dni, $curso_id . '_docente_' . $participacion_id, $tipo_documento);
} else {
    $codigo_unico_validacion = CertificateService::getValidationCode($institucion, $dni, $curso_id, $tipo_documento);
}

// Incluir sistema de detección automática de institución
require_once 'autodetect.php';

// URL de validación DINÁMICA según el subdominio
// Si viene de sajur.verumax.com → valida en sajur.verumax.com/validare.php
// Si viene de certificatum → valida en certificatum/validare.php
if (esSubdominioInstitucion()) {
    // Validación institucional (branding propio)
    $url_validacion = obtenerURLBaseInstitucion() . "/validare.php?codigo=" . $codigo_unico_validacion;
} else {
    // Validación global (desde certificatum o www)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $domain = $_SERVER['HTTP_HOST'] ?? 'www.verumax.com';
    $base = $protocol . $domain;
    $url_validacion = $base . "/certificatum/validare.php?codigo=" . $codigo_unico_validacion;
}

// Generar URL del código QR usando el servicio PSR-4
$qr_url = QRCodeService::generate($url_validacion, 100);

// 6. Verificar configuración de institución (ya obtenida arriba)
if (!$instance_config) {
    die('Error: Configuración de institución no encontrada.');
}

// Obtener branding de la configuración
$logo_url = $instance_config['logo_url'] ?? 'https://placehold.co/100x100/3b82f6/ffffff?text=' . strtoupper(substr($institucion, 0, 2));
$logo_url_small = $instance_config['logo_url'] ?? 'https://placehold.co/80x80/3b82f6/ffffff?text=' . strtoupper(substr($institucion, 0, 2));
$nombre_institucion = $instance_config['nombre'] ?? 'Institución Educativa';
$color_primario = $instance_config['color_primario'] ?? '#2E7D32';

// Datos del firmante autorizado
$firmante_nombre = $instance_config['firmante_nombre'] ?? null;
$firmante_cargo = $instance_config['firmante_cargo'] ?? null;

// Clases CSS dinámicas según color primario de la institución
$color_primary_bg = 'bg-[' . $color_primario . ']';
$color_primary_hover = 'hover:bg-opacity-90';
$color_primary_text = 'text-[' . $color_primario . ']';
$color_dot = 'bg-[' . $color_primario . ']';

// URL de retorno a la trayectoria académica
$current_lang = LanguageService::getCurrentLang();
if ($es_certificado_docente && $participacion_id) {
    // Para docentes: volver a tabularium con participacion y tipo=docente
    $tabularium_url = 'tabularium.php?institutio=' . urlencode($institucion)
                    . '&documentum=' . urlencode($dni)
                    . '&participacion=' . urlencode($participacion_id)
                    . '&tipo=docente'
                    . '&lang=' . urlencode($current_lang);
} else {
    // Para estudiantes: volver a tabularium con cursus
    $tabularium_url = 'tabularium.php?institutio=' . urlencode($institucion)
                    . '&documentum=' . urlencode($dni)
                    . '&cursus=' . urlencode($curso_id)
                    . '&lang=' . urlencode($current_lang);
}

// Mapeo de títulos de documentos (genus en latín) - con traducciones
$titulos_documentos = [
    'analyticum' => $t('certificatum.doc_analyticum', [], 'Analítico Académico'),
    'certificatum_approbationis' => $t('certificatum.doc_certificatum_approbationis', [], 'Certificado de Aprobación'),
    'certificatum_completionis' => $t('certificatum.doc_certificatum_completionis', [], 'Certificado de Finalización'),
    'testimonium_regulare' => $t('certificatum.doc_testimonium_regulare', [], 'Constancia de Alumno Regular'),
    'testimonium_completionis' => $t('certificatum.doc_testimonium_completionis', [], 'Constancia de Finalización'),
    'testimonium_inscriptionis' => $t('certificatum.doc_testimonium_inscriptionis', [], 'Constancia de Inscripción'),
    'certificatum_doctoris' => $t('certificatum.doc_certificatum_doctoris', [], 'Certificado de Docente/Instructor'),
    'certificatum_docente' => $t('certificatum.doc_certificatum_doctoris', [], 'Certificado de Docente/Instructor'),  // Alias
    'testimonium_doctoris' => $t('certificatum.doc_testimonium_doctoris', [], 'Constancia de Participación Docente')
];

// Mapeo de roles para mostrar - con traducciones
$roles_display = [
    'docente' => $t('certificatum.role_docente', [], 'Docente'),
    'instructor' => $t('certificatum.role_instructor', [], 'Instructor/a'),
    'orador' => $t('certificatum.role_orador', [], 'Orador/a'),
    'expositor' => $t('certificatum.role_expositor', [], 'Expositor/a'),
    'conferencista' => $t('certificatum.role_conferencista', [], 'Conferencista'),
    'facilitador' => $t('certificatum.role_facilitador', [], 'Facilitador/a'),
    'tutor' => $t('certificatum.role_tutor', [], 'Tutor/a'),
    'coordinador' => $t('certificatum.role_coordinador', [], 'Coordinador/a')
];

// Preparar variables para el header compartido
$instance = $instance_config;
$page_type = 'certificatum';
$page_title = ($titulos_documentos[$tipo_documento] ?? 'Documento') . ' - ' . $nombre_curso;

// Incluir header compartido
include __DIR__ . '/../templates/shared/header.php';
?>

<!-- Estilos adicionales para impresión -->
<style>
    /* Estilos generales para impresión */
    body { font-family: 'Inter', sans-serif; -webkit-print-color-adjust: exact; }
    .printable-area-vertical { max-width: 800px; margin: auto; }
    .printable-area-horizontal { max-width: 1120px; margin: auto; }
    .font-serif { font-family: 'Merriweather', serif; }
    @media print {
        body { background-color: white; padding: 0; }
        .no-print { display: none !important; }
        .printable-area-vertical, .printable-area-horizontal { box-shadow: none; margin: 0; max-width: 100%;}
        header, footer, nav { display: none !important; }
    }

    /* Estilos para marca de agua "NO VÁLIDO" en instancias Test */
    .watermark-no-valido {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        overflow: hidden;
        z-index: 1000;
    }
    .watermark-no-valido::before {
        content: 'NO VÁLIDO';
        position: absolute;
        font-size: 80px;
        font-weight: bold;
        color: rgba(220, 38, 38, 0.25);
        transform: rotate(-35deg);
        white-space: nowrap;
        letter-spacing: 10px;
        text-transform: uppercase;
        font-family: Arial, sans-serif;
    }
    .watermark-no-valido::after {
        content: 'DOCUMENTO DE PRUEBA - NO VÁLIDO - DOCUMENTO DE PRUEBA - NO VÁLIDO';
        position: absolute;
        font-size: 14px;
        font-weight: bold;
        color: rgba(220, 38, 38, 0.35);
        transform: rotate(-35deg);
        white-space: nowrap;
        letter-spacing: 3px;
        margin-top: 150px;
        font-family: Arial, sans-serif;
    }
    .watermark-container {
        position: relative;
    }
    @media print {
        .watermark-no-valido {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .watermark-no-valido::before {
            color: rgba(220, 38, 38, 0.3) !important;
        }
    }
</style>

<?php
// Variable para detectar si es instancia de prueba (plan = 'test')
$es_instancia_test = ($instance_config['plan'] ?? '') === 'test';
?>

<!-- Fuente adicional para certificados -->
<link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&display=swap" rel="stylesheet">

<!-- Botón adicional: Volver a trayectoria -->
<div class="no-print bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
    <div class="container mx-auto px-6 py-3">
        <a href="<?php echo htmlspecialchars($tabularium_url); ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors font-medium text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span class="hidden sm:inline"><?php echo $t('certificatum.back_to_trajectory', [], 'Volver a la trayectoria'); ?></span>
            <span class="sm:hidden"><?php echo $t('common.btn_back', [], 'Volver'); ?></span>
        </a>
    </div>
</div>

<main class="bg-gray-100 dark:bg-gray-900 p-4 sm:p-8 min-h-screen transition-colors duration-300">

    <!-- Barra de acciones (Descargar PDF / Imprimir) -->
    <?php
    // Construir URL para descarga de PDF
    // Siempre usar ruta relativa, ya que cada institución tiene su proxy creare_pdf.php
    $pdf_url = 'creare_pdf.php?institutio=' . urlencode($institucion)
             . '&documentum=' . urlencode($dni)
             . '&cursus=' . urlencode($curso_id)
             . '&genus=' . urlencode($tipo_documento);
    if ($participacion_id) {
        $pdf_url .= '&participacion=' . urlencode($participacion_id);
    }
    if ($lang_request) {
        $pdf_url .= '&lang=' . urlencode($lang_request);
    }
    ?>
    <div class="no-print text-center mb-6 max-w-2xl mx-auto">
        <div class="flex justify-center items-center gap-3 bg-white dark:bg-gray-800 p-3 rounded-xl shadow-sm">
            <!-- Botón principal: Descargar PDF -->
            <a href="<?php echo htmlspecialchars($pdf_url); ?>" target="_blank"
               class="text-white px-6 py-3 rounded-lg hover:opacity-90 inline-flex items-center font-semibold text-sm flex-grow justify-center transition-all"
               style="background-color: <?php echo htmlspecialchars($color_primario); ?>;">
                <i data-lucide="download" class="w-5 h-5 mr-2"></i> <?php echo $t('certificatum.btn_download_pdf', [], 'Descargar PDF'); ?>
            </a>
        </div>
    </div>

    <?php
    // --- SELECCIÓN DINÁMICA DE PLANTILLA ---

    // Detectar si existe un template de imagen para esta institución
    $template_imagen_path = __DIR__ . '/../assets/templates/certificados/' . $institucion . '/template_clasico.jpg';

    // Usar data URI (base64) para evitar problemas de rutas
    $template_imagen_url = null;
    if (file_exists($template_imagen_path)) {
        $imageData = base64_encode(file_get_contents($template_imagen_path));
        $imageType = 'image/jpeg';
        $template_imagen_url = 'data:' . $imageType . ';base64,' . $imageData;
    }

    // Ruta de la firma 1 (prioridad: curso > institución) - como base64
    $firma_url = null;
    // Primero intentar firma del curso
    if (!empty($curso['firmante_1_firma_url'])) {
        $curso_firma_path = __DIR__ . '/../' . ltrim($curso['firmante_1_firma_url'], '/');
        if (file_exists($curso_firma_path)) {
            $firma_url = 'data:image/png;base64,' . base64_encode(file_get_contents($curso_firma_path));
        }
    }
    // Fallback a firma de institución
    if (!$firma_url) {
        $firma_path_absolute = __DIR__ . '/../assets/images/firmas/' . $institucion . '_firma.png';
        if (file_exists($firma_path_absolute)) {
            $firma_url = 'data:image/png;base64,' . base64_encode(file_get_contents($firma_path_absolute));
        }
    }

    // --- VARIABLES DE GÉNERO (disponibles para todos los tipos de certificado) ---
    $genero_persona = $alumno['genero'] ?? 'Prefiero no especificar';
    $currentLang = LanguageService::getCurrentLang();
    if ($currentLang === 'pt_BR') {
        $aprobado_texto = LanguageService::getGenderedText($genero_persona, 'aprovad', 'sufijo_o');
        $inscripto_texto = LanguageService::getGenderedText($genero_persona, 'matriculad', 'sufijo_o');
    } else {
        $aprobado_texto = LanguageService::getGenderedText($genero_persona, 'aprobad', 'sufijo_o');
        $inscripto_texto = LanguageService::getGenderedText($genero_persona, 'inscript', 'sufijo_o');
    }

    // --- PLANTILLA JSON PERSONALIZADA (si el curso tiene template asignado) ---
    // El template JSON define posición, fuentes, colores de cada elemento

    // Etiqueta para modo preview (goto desde inicio del archivo)
    render_certificate:

    // Tipos de certificado que usan template JSON
    $tipos_certificado_estudiante = ['certificatum_approbationis', 'certificatum_completionis'];
    $tipos_certificado_docente = ['certificatum_doctoris', 'certificatum_docente'];
    $es_certificado_finalizacion = ($tipo_documento === 'certificatum_completionis');

    $usar_template_json = (
        $template_json_data !== null &&
        !empty($template_json_data['config']) &&
        (in_array($tipo_documento, $tipos_certificado_estudiante) || in_array($tipo_documento, $tipos_certificado_docente))
    );

    if ($usar_template_json) {
        // Parsear configuración JSON
        $template_config = json_decode($template_json_data['config'], true);
        $canvas = $template_config['canvas'] ?? ['width' => 297, 'height' => 210, 'orientation' => 'landscape'];
        $elements = $template_config['elements'] ?? [];

        // El editor usa un canvas FIJO de 842x595px para A4 landscape
        // y convierte px a mm con factor 0.264583 al exportar.
        // Para recrear las mismas posiciones, usamos el mismo canvas fijo
        // y el factor inverso para convertir mm -> px
        $scale = 1 / 0.264583;  // = 3.7795 px por mm (inverso del factor de exportación)

        // Canvas FIJO igual que el editor (NO calculado)
        $orientation = $canvas['orientation'] ?? 'landscape';
        if ($orientation === 'landscape') {
            $canvas_width_px = 842;   // A4 landscape igual que el editor
            $canvas_height_px = 595;
        } else {
            $canvas_width_px = 595;   // A4 portrait
            $canvas_height_px = 842;
        }

        // Preparar variables dinámicas
        // Determinar fecha según tipo de documento
        // Para CERTIFICADOS: usar fecha de emisión si existe (cuando se generó el PDF por primera vez)
        // Para CONSTANCIAS: usar fecha según contexto
        $fecha_para_documento = null;
        $es_tipo_certificado = strpos($tipo_documento, 'certificatum_') === 0;

        // MARCADO AUTOMÁTICO: marcar certificado como emitido en primer acceso (pantalla o PDF)
        if ($es_tipo_certificado) {
            if ($es_certificado_docente) {
                $fecha_emision_marcada = StudentService::marcarCertificadoEmitidoDocente((int)$participacion_id);
                if ($fecha_emision_marcada) {
                    $participacion['fecha_certificado'] = $fecha_emision_marcada;
                }
            } else {
                $id_inscripcion = $curso['id_inscripcion'] ?? null;
                if ($id_inscripcion) {
                    $fecha_emision_marcada = StudentService::marcarCertificadoEmitidoEstudiante((int)$id_inscripcion);
                    if ($fecha_emision_marcada) {
                        $curso['fecha_emision_certificado'] = $fecha_emision_marcada;
                    }
                }
            }
        }

        // LOGGING DE ACCESO: registrar vista en pantalla del certificado
        CertificateService::logAccesoCertificado(
            $institucion,
            $dni,
            CertificateService::ACTION_VIEW,
            $tipo_documento,
            $curso_id,
            $curso['nombre_curso'] ?? $template_json_data['curso']['nombre'] ?? null,
            $es_certificado_docente ? 'docente' : 'estudiante',
            $es_certificado_docente ? (int)$participacion_id : null,
            $alumno['nombre_completo'] ?? $docente['nombre_completo'] ?? null,
            LanguageService::getCurrentLang()
        );

        // CRITERIO UNIFICADO PARA FECHA DEL DOCUMENTO:
        // 1. Certificados: fecha de emisión (primera vez que se generó) o fecha_finalizacion_raw
        // 2. Constancias: según tipo de documento
        // Nota: fecha_finalizacion_raw ya viene unificada (inscripción/participación > curso > actual)

        if ($es_tipo_certificado) {
            // Para CERTIFICADOS: fecha de emisión si existe, sino fecha de finalización unificada
            if ($es_certificado_docente) {
                $fecha_para_documento = $participacion['fecha_certificado'] ?? $curso['fecha_finalizacion_raw'] ?? null;
            } else {
                $fecha_para_documento = $curso['fecha_emision_certificado'] ?? $curso['fecha_finalizacion_raw'] ?? null;
            }
        } else {
            // Para CONSTANCIAS: según tipo de documento
            switch ($tipo_documento) {
                case 'testimonium_completionis':
                case 'testimonium_doctoris':
                    // Constancia de finalización/participación: fecha de finalización unificada
                    $fecha_para_documento = $curso['fecha_finalizacion_raw'] ?? null;
                    break;
                case 'testimonium_inscriptionis':
                    // Constancia de inscripción: fecha de inscripción (o inicio como fallback)
                    $fecha_para_documento = $curso['fecha_inscripcion_raw'] ?? $curso['fecha_inicio_raw'] ?? null;
                    break;
                case 'testimonium_regulare':
                    // Constancia de alumno regular: fecha actual (es una constancia de estado presente)
                    $fecha_para_documento = date('Y-m-d');
                    break;
                default:
                    // Por defecto: fecha de finalización unificada
                    $fecha_para_documento = $curso['fecha_finalizacion_raw'] ?? null;
            }
        }
        // Si no hay fecha, usar la fecha actual como fallback
        $fecha_formateada = LanguageService::formatDate($fecha_para_documento ?? date('Y-m-d'), true);
        $dni_formateado = number_format((float)str_replace('.', '', $dni), 0, ',', '.');
        $nombre_institucion_completo = $instance_config['nombre_completo'] ?? $instance_config['nombre'] ?? 'Institución';

        // Calcular fecha_curso (período del curso: día único o rango)
        // Usar fechas OFICIALES del curso, no de la inscripción individual
        $curso_fecha_inicio = $curso['curso_fecha_inicio_raw'] ?? null;
        $curso_fecha_fin = $curso['curso_fecha_fin_raw'] ?? null;
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

        // Verificar si usar firmantes (default: true)
        $usar_firmante_1 = !isset($curso['usar_firmante_1']) || $curso['usar_firmante_1'] == 1;
        $usar_firmante_2 = !isset($curso['usar_firmante_2']) || $curso['usar_firmante_2'] == 1;

        // Firmantes: Prioridad curso > institución (solo si está habilitado)
        // Firmante 1
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

        // Firmante 2
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

        // DEBUG: Mostrar valores de firmantes si se pasa &debug=1
        if (isset($_GET['debug'])) {
            echo '<div style="background:#ff0;color:#000;padding:20px;margin:20px;border:3px solid red;font-family:monospace;font-size:14px;">';
            echo '<h3 style="margin:0 0 10px 0;">🔍 DEBUG FIRMANTES</h3>';
            echo '<p><strong>Curso firmante_1_nombre:</strong> ' . htmlspecialchars($curso['firmante_1_nombre'] ?? 'NO DEFINIDO') . '</p>';
            echo '<p><strong>Curso firmante_1_cargo:</strong> ' . htmlspecialchars($curso['firmante_1_cargo'] ?? 'NO DEFINIDO') . '</p>';
            echo '<p><strong>Curso firmante_2_nombre:</strong> ' . htmlspecialchars($curso['firmante_2_nombre'] ?? 'NO DEFINIDO') . '</p>';
            echo '<p><strong>Curso firmante_2_cargo:</strong> ' . htmlspecialchars($curso['firmante_2_cargo'] ?? 'NO DEFINIDO') . '</p>';
            echo '<hr>';
            echo '<p><strong>Institución firmante_nombre:</strong> ' . htmlspecialchars($instance_config['firmante_nombre'] ?? 'NO EXISTE') . '</p>';
            echo '<p><strong>Institución firmante_2_nombre:</strong> ' . htmlspecialchars($instance_config['firmante_2_nombre'] ?? 'NO EXISTE') . '</p>';
            echo '<hr>';
            echo '<p style="color:green;"><strong>RESULTADO firmante_1:</strong> ' . htmlspecialchars($firmante_nombre) . ' - ' . htmlspecialchars($firmante_cargo) . '</p>';
            echo '<p style="color:green;"><strong>RESULTADO firmante_2:</strong> ' . htmlspecialchars($firmante_2_nombre) . ' - ' . htmlspecialchars($firmante_2_cargo) . '</p>';
            echo '</div>';
        }

        // Obtener URL de firma 2 (prioridad: curso > institución) si firmante está habilitado
        $firma_2_url = null;
        if ($usar_firmante_2) {
            // Primero intentar firma del curso
            if (!empty($curso['firmante_2_firma_url'])) {
                $curso_firma_2_path = __DIR__ . '/../' . ltrim($curso['firmante_2_firma_url'], '/');
                if (file_exists($curso_firma_2_path)) {
                    $firma_2_url = 'data:image/png;base64,' . base64_encode(file_get_contents($curso_firma_2_path));
                }
            }
            // Fallback a firma de institución
            if (!$firma_2_url) {
                $firma_2_path = __DIR__ . '/../assets/images/firmas/' . $institucion . '_firma_2.png';
                if (file_exists($firma_2_path)) {
                    $firma_2_url = 'data:image/png;base64,' . base64_encode(file_get_contents($firma_2_path));
                }
            }
        }

        // Fecha formato sello (corto): "18 DIC 2025"
        $meses_cortos = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
        $fecha_raw = $fecha_para_documento ?? date('Y-m-d');
        $fecha_ts = strtotime($fecha_raw);
        $fecha_sello = date('d', $fecha_ts) . ' ' . $meses_cortos[(int)date('m', $fecha_ts) - 1] . ' ' . date('Y', $fecha_ts);

        $variables = [
            '{{nombre_completo}}' => $nombre_alumno,
            '{{dni}}' => $dni_formateado,
            '{{nombre_curso}}' => $nombre_curso,
            '{{nombre_institucion}}' => $nombre_institucion_completo,
            '{{fecha}}' => $fecha_formateada,
            '{{fecha_sello}}' => $fecha_sello,
            '{{carga_horaria}}' => $curso['carga_horaria'] ?? '',
            '{{firmante_1_nombre}}' => $firmante_nombre,
            '{{firmante_1_cargo}}' => $firmante_cargo,
            '{{firmante_2_nombre}}' => $firmante_2_nombre,
            '{{firmante_2_cargo}}' => $firmante_2_cargo,
            '{{qr}}' => $qr_url,
            '{{firma_1}}' => $firma_url,
            '{{firma}}' => $firma_url,  // Alias
            '{{firma_2}}' => $firma_2_url,
            '{{codigo_validacion}}' => $codigo_unico_validacion ?? '',
            // Nuevas variables de fechas (v2) - usan fechas OFICIALES del curso
            '{{fecha_curso}}' => $fecha_curso,
            '{{fecha_inicio}}' => $curso_fecha_inicio ? LanguageService::formatDate($curso_fecha_inicio, true) : '',
            '{{fecha_fin}}' => $curso_fecha_fin ? LanguageService::formatDate($curso_fecha_fin, false) : '',
            // Variable inteligente de lugar y fecha
            '{{lugar_fecha}}' => $lugar_fecha,
            '{{ciudad}}' => $ciudad_emision ?? '',
        ];

        // Calcular rol con género para docentes
        if ($es_certificado_docente) {
            $rol_base = strtolower($participacion['rol'] ?? 'docente');
            $genero_docente = $docente['genero'] ?? 'Prefiero no especificar';

            // Roles que varían según género (raíz + sufijo_or: -or/-ora)
            $roles_con_genero = [
                'expositor' => 'Exposit',
                'instructor' => 'Instruct',
                'tutor' => 'Tut',
                'coordinador' => 'Coordinad',
                'facilitador' => 'Facilitad',
                'orador' => 'Orad',
            ];
            // Roles neutros (no cambian)
            $roles_neutros = ['docente' => 'Docente', 'conferencista' => 'Conferencista'];

            if (isset($roles_con_genero[$rol_base])) {
                $rol_display = LanguageService::getGenderedText($genero_docente, $roles_con_genero[$rol_base], 'sufijo_or');
            } elseif (isset($roles_neutros[$rol_base])) {
                $rol_display = $roles_neutros[$rol_base];
            } else {
                $rol_display = ucfirst($rol_base);
            }
        } else {
            $rol_display = '';
        }

        // Agregar variable de rol con género aplicado
        $variables['{{rol}}'] = $rol_display;

        // Texto para párrafos con variables múltiples (diferente para estudiantes y docentes)
        $carga_horaria = $curso['carga_horaria'] ?? '';
        if ($es_certificado_docente) {
            $parrafo_default = "El día {$fecha_formateada} se certifica que **{$nombre_alumno}** con DNI **{$dni_formateado}** ha desempeñado una destacada labor como {$rol_display} del curso **{$nombre_curso}**, impartiendo sus conocimientos con un alto nivel de competencia.";
        } else {
            $parrafo_default = "El día {$fecha_formateada} se certifica que **{$nombre_alumno}** con DNI **{$dni_formateado}** ha completado y aprobado satisfactoriamente el curso **{$nombre_curso}** con una carga horaria de **{$carga_horaria}** horas.";
        }

        // Cargar imagen de fondo SOLO si el JSON lo especifica explícitamente
        // NO usar fallbacks - si el JSON no tiene background, se usa fondo blanco
        $fondo_url = null;
        if (!empty($canvas['background'])) {
            // Intentar encontrar el archivo de fondo especificado
            $posibles_rutas = [
                __DIR__ . '/../assets/templates/certificados/' . ($template_json_data['slug'] ?? 'default') . '/' . $canvas['background'],
                __DIR__ . '/../assets/templates/certificados/' . $institucion . '/' . $canvas['background'],
                __DIR__ . '/../assets/templates/certificados/moderno/' . $canvas['background'],
            ];
            foreach ($posibles_rutas as $ruta) {
                if (file_exists($ruta)) {
                    $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
                    $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                    $fondo_url = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($ruta));
                    break;
                }
            }
        }
    ?>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Antic+Didone&family=Cormorant+Garamond:wght@400;600;700&family=Special+Elite&family=Playfair+Display:wght@400;500;600;700&display=swap');

            .certificado-json-wrapper {
                width: 100%;
                display: flex;
                justify-content: center;
                padding: 0;
                margin: 0;
                /* Responsive: ajustar altura del contenedor según escala */
                overflow: hidden;
            }

            .certificado-json {
                width: <?php echo $canvas_width_px; ?>px;
                height: <?php echo $canvas_height_px; ?>px;
                position: relative;
                <?php if ($fondo_url): ?>
                background-image: url('<?php echo $fondo_url; ?>');
                background-size: 100% 100%;
                background-position: center;
                background-repeat: no-repeat;
                <?php else: ?>
                background: #ffffff;
                <?php endif; ?>
                font-family: Arial, sans-serif;
                overflow: hidden;
                transform-origin: top center;
                flex-shrink: 0;
            }

            .template-element {
                position: absolute;
                box-sizing: border-box;
                margin: 0;
                padding: 4px;  /* Igual que el editor */
            }
        </style>

        <!-- DEBUG: Canvas size = <?php echo $canvas_width_px; ?>x<?php echo $canvas_height_px; ?>px, Scale = <?php echo $scale; ?> -->
        <div class="certificado-json-wrapper">
        <div class="certificado-json shadow-2xl watermark-container">
            <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
            <?php foreach ($elements as $element):
                // Variables para detectar elementos de firmantes
                $element_var = $element['variable'] ?? '';
                $element_id = $element['id'] ?? '';
                $element_label = strtolower($element['label'] ?? '');
                $element_group = $element['group'] ?? '';
                $element_x = $element['x'] ?? 0;
                $element_type = $element['type'] ?? 'text';

                // Saltar elementos de firmante 1 si está desactivado
                if (!$usar_firmante_1) {
                    if (strpos($element_var, 'firmante_1') !== false ||
                        strpos($element_var, 'firma_1') !== false ||
                        strpos($element_var, '{{firma}}') !== false ||
                        strpos($element_id, 'firmante-1') !== false ||
                        $element_group === 'firmante_1') {
                        continue;
                    }
                }

                // Saltar elementos de firmante 2 si está desactivado
                if (!$usar_firmante_2) {
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
                    $tipos_firma = ['line-firma', 'firma', 'signature'];
                    if (in_array($element_type, $tipos_firma) && $element_x < 100) {
                        continue;
                    }
                    // Para imágenes, verificar si es una imagen de firma basándonos en el label o src
                    if ($element_type === 'image' && $element_x < 100) {
                        $src = strtolower($element['src'] ?? '');
                        if (strpos($element_label, 'firma') !== false || strpos($src, 'firma') !== false) {
                            continue;
                        }
                    }
                }

                $x_px = ($element['x'] ?? 0) * $scale;
                $y_px = ($element['y'] ?? 0) * $scale;
                $w_px = ($element['width'] ?? 100) * $scale;
                $h_px = ($element['height'] ?? 20) * $scale;
                $font = $element['font'] ?? 'Arial';
                // El editor exporta size en PUNTOS (px * 0.75), convertir de vuelta a px
                $size_pt = $element['size'] ?? 12;
                $size = $size_pt / 0.75;  // pt to px
                $color = $element['color'] ?? '#333333';
                $align = $element['align'] ?? 'left';
                $style = $element['style'] ?? 'normal';
                $type = $element['type'] ?? 'text';
                $variable = $element['variable'] ?? null;
                $text = $element['text'] ?? '';
                $text_key = $element['text_key'] ?? null;

                // Si existe text_key, buscar traducción (con text como fallback)
                // Esto permite internacionalización de textos estáticos del template
                if ($text_key && !$variable) {
                    $text = $t($text_key, [], $text);
                }

                // Reemplazar variables
                // Para text-custom, soportar text_docente y text_finalizacion según tipo de certificado
                // IMPORTANTE: Usar text_key como fallback si no hay clave específica
                if ($type === 'text-custom') {
                    if ($es_certificado_docente && !empty($element['text_docente'])) {
                        // Soporte i18n: primero text_key_docente, fallback a text_key
                        $text_key_docente = $element['text_key_docente'] ?? null;
                        $effective_key = $text_key_docente ?: $text_key;
                        $content = $effective_key
                            ? $t($effective_key, [], $element['text_docente'])
                            : $element['text_docente'];
                    } elseif ($es_certificado_finalizacion && !empty($element['text_finalizacion'])) {
                        // Soporte i18n: primero text_key_finalizacion, fallback a text_key
                        $text_key_finalizacion = $element['text_key_finalizacion'] ?? null;
                        $effective_key = $text_key_finalizacion ?: $text_key;
                        $content = $effective_key
                            ? $t($effective_key, [], $element['text_finalizacion'])
                            : $element['text_finalizacion'];
                    } else {
                        // Para aprobación: $text ya tiene traducción aplicada si había text_key
                        $content = $variable ? ($variables[$variable] ?? $variable) : $text;
                    }
                } else {
                    $content = $variable ? ($variables[$variable] ?? $variable) : $text;
                }

                // Estilos de fuente
                $font_weight = ($style === 'bold') ? 'bold' : 'normal';
                $font_style_css = ($style === 'italic') ? 'italic' : 'normal';
            ?>
                <?php if ($type === 'text' || $type === 'title' || $type === 'text-custom'): ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;">
                    <span style="font-family: '<?php echo $font; ?>', sans-serif;
                            font-size: <?php echo $size; ?>px;
                            color: <?php echo $color; ?>;
                            text-align: <?php echo $align; ?>;
                            font-weight: <?php echo $font_weight; ?>;
                            font-style: <?php echo $font_style_css; ?>;
                            display: block;
                            width: 100%;
                            line-height: 1.2;
                            white-space: normal; word-wrap: break-word;
                            overflow: visible;"><?php echo applyTextFormatting($content); ?></span>
                </div>
                <?php elseif ($type === 'paragraph'): ?>
                <?php
                    // Párrafo usa valores por defecto si no están definidos
                    $p_font = !empty($element['font']) ? $element['font'] : 'Antic Didone';
                    // El editor exporta size en PUNTOS (px * 0.75), convertir de vuelta a px
                    $p_size_pt = !empty($element['size']) ? $element['size'] : 10;
                    $p_size = $p_size_pt / 0.75;  // pt to px
                    $p_color = !empty($element['color']) ? $element['color'] : '#333333';
                    $p_align = !empty($element['align']) ? $element['align'] : 'left';

                    // Usar texto según tipo de usuario (estudiante/docente)
                    $p_texto = $parrafo_default;
                    $texto_base = null;

                    // Determinar qué texto usar según el tipo de certificado
                    // Prioridad: text_key específico > text_key general > text literal > $parrafo_default
                    $text_key_general = $element['text_key'] ?? null;

                    if ($es_certificado_docente) {
                        // Docente: primero text_key_docente, fallback a text_key general
                        $text_key_docente = $element['text_key_docente'] ?? null;
                        $effective_key = $text_key_docente ?: $text_key_general;
                        if ($effective_key) {
                            $texto_base = $t($effective_key, [], $element['text_docente'] ?? '');
                        } elseif (!empty($element['text_docente'])) {
                            $texto_base = $element['text_docente'];
                        }
                    } elseif ($es_certificado_finalizacion) {
                        // Finalización: primero text_key_finalizacion, fallback a text_key general
                        $text_key_finalizacion = $element['text_key_finalizacion'] ?? null;
                        $effective_key = $text_key_finalizacion ?: $text_key_general;
                        if ($effective_key) {
                            $texto_base = $t($effective_key, [], $element['text_finalizacion'] ?? '');
                        } elseif (!empty($element['text_finalizacion'])) {
                            $texto_base = $element['text_finalizacion'];
                        }
                    } else {
                        // Aprobación: usar text_key general
                        if ($text_key_general) {
                            $texto_base = $t($text_key_general, [], $element['text'] ?? '');
                        } elseif (!empty($element['text'])) {
                            $texto_base = $element['text'];
                        }
                    }
                    // Si no hay texto específico, $texto_base queda null y se usa $parrafo_default

                    if ($texto_base) {
                        // Reemplazar variables en el texto (ya sea de traducción o literal)
                        $p_texto = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($variables) {
                            $key = '{{' . $matches[1] . '}}';
                            return $variables[$key] ?? $matches[0];
                        }, $texto_base);
                    }
                ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;">
                    <span style="font-family: '<?php echo $p_font; ?>', sans-serif;
                            font-size: <?php echo $p_size; ?>px;
                            color: <?php echo $p_color; ?>;
                            text-align: <?php echo $p_align; ?>;
                            display: block;
                            width: 100%;
                            line-height: 1.4;
                            white-space: normal;
                            overflow: hidden;"><?php echo applyTextFormatting($p_texto); ?></span>
                </div>
                <?php elseif ($type === 'qr' && $variable === '{{qr}}'): ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;">
                    <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="QR"
                         style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <?php elseif ($type === 'image'): ?>
                <?php
                    // Determinar qué imagen mostrar según la variable
                    $img_src = null;
                    if ($variable === '{{firma_1}}' || $variable === '{{firma}}') {
                        $img_src = $firma_url;
                    } elseif ($variable === '{{firma_2}}') {
                        $img_src = $firma_2_url;
                    } elseif ($variable === '{{logo}}' || $variable === '{{logo_1}}') {
                        $img_src = $instance_config['logo_url'] ?? null;
                    } elseif ($variable === '{{logo_2}}') {
                        $img_src = $instance_config['logo_2_url'] ?? null;
                    } elseif ($variable === '{{logo_verumax}}') {
                        // Logo Verumax - usar base64 para evitar problemas de ruta
                        $logo_verumax_path = __DIR__ . '/../assets/images/logo-verumax-escudo.png';
                        if (file_exists($logo_verumax_path)) {
                            $img_data = base64_encode(file_get_contents($logo_verumax_path));
                            $img_src = 'data:image/png;base64,' . $img_data;
                        }
                    } elseif (!empty($element['src'])) {
                        // Imagen personalizada con ruta src - convertir a base64 como el PDF
                        // Normalizar barras invertidas (Windows) a barras normales (Linux)
                        $src_normalized = str_replace('\\', '/', $element['src']);
                        $custom_img_path = __DIR__ . '/../' . ltrim($src_normalized, '/');
                        if (file_exists($custom_img_path)) {
                            $img_ext = strtolower(pathinfo($custom_img_path, PATHINFO_EXTENSION));
                            $mime_type = ($img_ext === 'png') ? 'image/png' : (($img_ext === 'jpg' || $img_ext === 'jpeg') ? 'image/jpeg' : 'image/png');
                            $img_data = base64_encode(file_get_contents($custom_img_path));
                            $img_src = 'data:' . $mime_type . ';base64,' . $img_data;
                        }
                    }
                ?>
                <?php if ($img_src):
                    // Soporte para rotación y opacidad en imagen personalizada
                    $img_rotation = $element['rotation'] ?? 0;
                    $img_opacity = $element['opacity'] ?? 1;
                ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;
                            <?php if ($img_rotation != 0): ?>transform: rotate(<?php echo $img_rotation; ?>deg);<?php endif; ?>
                            <?php if ($img_opacity < 1): ?>opacity: <?php echo $img_opacity; ?>;<?php endif; ?>">
                    <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Imagen"
                         style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <?php endif; ?>
                <?php elseif ($type === 'line' || $type === 'decorative-line'): ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;
                            display: flex;
                            align-items: center;">
                    <div style="width: 100%; height: <?php echo ($element['thickness'] ?? $element['lineWidth'] ?? 1); ?>px; background-color: <?php echo $color; ?>;"></div>
                </div>
                <?php elseif ($type === 'line-firma'): ?>
                <?php
                    // Línea de firma - línea horizontal simple para firmas
                    $line_color = $element['color'] ?? '#333333';
                    $line_thickness = $element['thickness'] ?? 1;
                ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;
                            display: flex;
                            align-items: flex-end;">
                    <div style="width: 100%; height: <?php echo $line_thickness; ?>px; background-color: <?php echo $line_color; ?>;"></div>
                </div>
                <?php elseif ($type === 'decorative-image'): ?>
                <?php if (!empty($element['src'])): ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;">
                    <img src="<?php echo htmlspecialchars($element['src']); ?>" alt="Decorativo"
                         style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <?php endif; ?>
                <?php elseif ($type === 'stamp'): ?>
                <?php
                    $stamp_text = $element['text'] ?? '{{fecha_sello}}';
                    $stamp_color = $element['color'] ?? '#1e40af';
                    // El editor exporta size en PUNTOS (px * 0.75), convertir de vuelta a px
                    $stamp_size = ($element['size'] ?? 9) / 0.75;
                    $stamp_rotation = $element['rotation'] ?? -3;
                    $stamp_opacity = $element['opacity'] ?? 0.85;
                    $stamp_border = $element['borderWidth'] ?? 2;

                    // Reemplazar variables en el texto del sello
                    $stamp_display = preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) use ($variables) {
                        $key = '{{' . $matches[1] . '}}';
                        return $variables[$key] ?? $matches[0];
                    }, $stamp_text);
                ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;
                            transform: rotate(<?php echo $stamp_rotation; ?>deg);
                            opacity: <?php echo $stamp_opacity; ?>;">
                    <div style="width: 100%;
                                height: 100%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                <?php if ($stamp_border > 0): ?>border: <?php echo $stamp_border; ?>px solid <?php echo $stamp_color; ?>;<?php endif; ?>
                                color: <?php echo $stamp_color; ?>;
                                font-family: 'Special Elite', 'Courier New', monospace;
                                font-size: <?php echo $stamp_size; ?>px;
                                text-transform: uppercase;
                                letter-spacing: 2px;
                                padding: 4px 8px;
                                box-sizing: border-box;">
                        <?php echo htmlspecialchars($stamp_display); ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        </div><!-- /certificado-json-wrapper -->

    <?php
    // --- PLANTILLA CON IMAGEN DE FONDO (FALLBACK si NO hay template JSON) ---
    } elseif ((in_array($tipo_documento, ['certificatum_approbationis', 'certificatum_completionis']) || in_array($tipo_documento, ['certificatum_doctoris', 'certificatum_docente'])) && $template_imagen_url) {

        // Determinar si es certificado de docente o de alumno
        $es_cert_docente = (in_array($tipo_documento, ['certificatum_doctoris', 'certificatum_docente']) && $es_certificado_docente);

        // Si es docente, actualizar género con datos del docente
        if ($es_cert_docente) {
            $genero_persona = $docente['genero'] ?? 'Prefiero no especificar';
            // Regenerar textos con el género del docente
            if ($currentLang === 'pt_BR') {
                $aprobado_texto = LanguageService::getGenderedText($genero_persona, 'aprovad', 'sufijo_o');
                $inscripto_texto = LanguageService::getGenderedText($genero_persona, 'matriculad', 'sufijo_o');
            } else {
                $aprobado_texto = LanguageService::getGenderedText($genero_persona, 'aprobad', 'sufijo_o');
                $inscripto_texto = LanguageService::getGenderedText($genero_persona, 'inscript', 'sufijo_o');
            }
        }

        // Generar texto con género apropiado para formador
        $formador_texto = LanguageService::getGenderedText($genero_persona, 'Formad', 'sufijo_or');

        if ($es_cert_docente) {
            $tipo_cert_texto = $t('certificatum.cert_type_trainer', ['formador' => $formador_texto], 'Certificado de ' . $formador_texto);
        } elseif ($es_certificado_finalizacion) {
            $tipo_cert_texto = $t('certificatum.cert_type_completion', [], 'Certificado de Finalización');
        } else {
            $tipo_cert_texto = $t('certificatum.cert_type_approval', [], 'Certificado de Aprobación');
        }

        // Formatear fecha con el servicio de idiomas según tipo de documento
        // Para CERTIFICADOS: usar fecha de emisión si existe
        // Para CONSTANCIAS: usar fecha según contexto
        $fecha_para_documento = null;
        $es_tipo_certificado_legacy = strpos($tipo_documento, 'certificatum_') === 0;

        // MARCADO AUTOMÁTICO: marcar certificado como emitido en primer acceso
        if ($es_tipo_certificado_legacy) {
            if ($es_cert_docente) {
                $fecha_emision_marcada = StudentService::marcarCertificadoEmitidoDocente((int)$participacion_id);
                if ($fecha_emision_marcada) {
                    $participacion['fecha_certificado'] = $fecha_emision_marcada;
                }
            } else {
                $id_inscripcion = $curso['id_inscripcion'] ?? null;
                if ($id_inscripcion) {
                    $fecha_emision_marcada = StudentService::marcarCertificadoEmitidoEstudiante((int)$id_inscripcion);
                    if ($fecha_emision_marcada) {
                        $curso['fecha_emision_certificado'] = $fecha_emision_marcada;
                    }
                }
            }
        }

        // LOGGING DE ACCESO: registrar vista en pantalla del certificado (legacy)
        CertificateService::logAccesoCertificado(
            $institucion,
            $dni,
            CertificateService::ACTION_VIEW,
            $tipo_documento,
            $curso_id,
            $curso['nombre_curso'] ?? null,
            $es_cert_docente ? 'docente' : 'estudiante',
            $es_cert_docente ? (int)$participacion_id : null,
            $nombre_alumno ?? null,
            LanguageService::getCurrentLang()
        );

        // CRITERIO UNIFICADO PARA FECHA DEL DOCUMENTO (legacy):
        // Nota: fecha_finalizacion_raw ya viene unificada (inscripción/participación > curso > actual)

        if ($es_tipo_certificado_legacy) {
            // Para CERTIFICADOS: fecha de emisión si existe, sino fecha de finalización unificada
            if ($es_cert_docente) {
                $fecha_para_documento = $participacion['fecha_certificado'] ?? $curso['fecha_finalizacion_raw'] ?? null;
            } else {
                $fecha_para_documento = $curso['fecha_emision_certificado'] ?? $curso['fecha_finalizacion_raw'] ?? null;
            }
        } else {
            // Para CONSTANCIAS: según tipo de documento
            switch ($tipo_documento) {
                case 'testimonium_completionis':
                case 'testimonium_doctoris':
                    $fecha_para_documento = $curso['fecha_finalizacion_raw'] ?? null;
                    break;
                case 'testimonium_inscriptionis':
                    $fecha_para_documento = $curso['fecha_inscripcion_raw'] ?? $curso['fecha_inicio_raw'] ?? null;
                    break;
                case 'testimonium_regulare':
                    $fecha_para_documento = date('Y-m-d');
                    break;
                default:
                    $fecha_para_documento = $curso['fecha_finalizacion_raw'] ?? null;
            }
        }
        $fecha_formateada = LanguageService::formatDate($fecha_para_documento ?? date('Y-m-d'), true);

        // Texto descriptivo según tipo
        if ($es_cert_docente) {
            $texto_descripcion = $t('certificatum.cert_desc_trainer', [
                'fecha' => $fecha_formateada,
                'nombre' => '<strong>' . $nombre_alumno . '</strong>',
                'dni' => '<strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong>',
                'nombre_curso' => '<strong>' . $nombre_curso . '</strong>',
                'formador' => strtolower($formador_texto)
            ], 'El día ' . $fecha_formateada . ' se certifica que <strong>' . $nombre_alumno . '</strong> con DNI <strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong> ha desempeñado una destacada labor como ' . strtolower($formador_texto) . ' de "<strong>' . $nombre_curso . '</strong>", impartiendo sus conocimientos con un alto nivel de competencia.');
        } elseif ($es_certificado_finalizacion) {
            $texto_descripcion = $t('certificatum.cert_desc_completion', [
                'fecha' => $fecha_formateada,
                'nombre' => '<strong>' . $nombre_alumno . '</strong>',
                'dni' => '<strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong>',
                'nombre_curso' => '<strong>' . $nombre_curso . '</strong>',
                'carga_horaria' => '<strong>' . $curso['carga_horaria'] . '</strong>'
            ], 'El día ' . $fecha_formateada . ' se certifica que <strong>' . $nombre_alumno . '</strong> con DNI <strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong> ha completado satisfactoriamente el curso "<strong>' . $nombre_curso . '</strong>" con una carga horaria de <strong>' . $curso['carga_horaria'] . ' horas</strong>.');
        } else {
            $texto_descripcion = $t('certificatum.cert_desc_approval', [
                'fecha' => $fecha_formateada,
                'nombre' => '<strong>' . $nombre_alumno . '</strong>',
                'dni' => '<strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong>',
                'nombre_curso' => '<strong>' . $nombre_curso . '</strong>',
                'carga_horaria' => '<strong>' . $curso['carga_horaria'] . '</strong>',
                'aprovado' => $aprobado_texto  // Para portugués (foi aprovado/aprovada)
            ], 'El día ' . $fecha_formateada . ' se certifica que <strong>' . $nombre_alumno . '</strong> con DNI <strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong> ha completado y aprobado satisfactoriamente el curso "<strong>' . $nombre_curso . '</strong>" con una carga horaria de <strong>' . $curso['carga_horaria'] . ' horas</strong>.');
        }
    ?>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Great+Vibes&display=swap');

            .certificado-imagen {
                width: 1122px;
                height: 793px;
                position: relative;
                background-image: url('<?php echo $template_imagen_url; ?>');
                background-size: cover;
                background-position: center;
                font-family: 'Cormorant Garamond', serif;
            }

            /* Solo textos dinámicos - el resto ya está en la imagen JPG */

            /* Nombre del curso/formación (debajo de "Sociedad Argentina de Justicia Restaurativa") */
            .cert-curso {
                position: absolute;
                top: 158px;
                left: 0;
                right: 0;
                text-align: center;
            }
            .cert-curso p {
                font-size: 22px;
                font-style: italic;
                font-weight: 600;
                color: #1a5276;
                margin: 0;
            }

            /* Tipo de certificado con "Por la presente..." (debajo de línea dorada) */
            .cert-tipo {
                position: absolute;
                top: 330px;
                left: 0;
                right: 0;
                text-align: center;
            }
            .cert-tipo .intro {
                font-size: 15px;
                color: #333;
                margin: 0 0 3px 0;
            }
            .cert-tipo .tipo-cert {
                font-size: 16px;
                color: #1a5276;
                font-weight: 600;
                margin: 0;
            }

            /* Nombre del destinatario en cursiva elegante */
            .cert-nombre {
                position: absolute;
                top: 415px;
                left: 100px;
                right: 100px;
                text-align: center;
            }
            .cert-nombre-texto {
                font-family: 'Great Vibes', cursive;
                font-size: 52px;
                color: #7d6608;
                line-height: 1.2;
                margin: 0;
            }

            /* Texto descriptivo (debajo de la línea decorativa inferior) */
            .cert-descripcion {
                position: absolute;
                top: 510px;
                left: 80px;
                right: 80px;
                text-align: center;
            }
            .cert-descripcion p {
                font-size: 17px;
                color: #333;
                line-height: 1.6;
                margin: 0;
            }

            /* Código QR de validación - centrado entre logo y firma */
            .cert-qr {
                position: absolute;
                bottom: 70px;
                left: 50%;
                transform: translateX(-50%);
                text-align: center;
            }
            .cert-qr img {
                width: 90px;
                height: 90px;
            }
            .cert-qr-text {
                font-size: 8px;
                color: #666;
                margin-top: 3px;
            }

            @media print {
                .certificado-imagen {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                    box-shadow: none;
                }
            }
        </style>

        <div class="printable-area-horizontal certificado-imagen shadow-2xl mx-auto watermark-container">
            <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
            <!-- Nombre del curso/formación -->
            <div class="cert-curso">
                <p><?php echo htmlspecialchars($nombre_curso); ?></p>
            </div>

            <!-- Tipo de certificado con introducción -->
            <div class="cert-tipo">
                <p class="intro"><?php echo $t('certificatum.cert_hereby_grants', ['institucion' => $nombre_institucion], 'Por la presente, ' . $nombre_institucion . ' otorga el presente'); ?></p>
                <p class="tipo-cert"><?php echo $tipo_cert_texto; ?></p>
            </div>

            <!-- Nombre del destinatario -->
            <div class="cert-nombre">
                <p class="cert-nombre-texto"><?php echo $nombre_alumno; ?></p>
            </div>

            <!-- Descripción -->
            <div class="cert-descripcion">
                <p><?php echo $texto_descripcion; ?></p>
            </div>

            <!-- Código QR de validación -->
            <div class="cert-qr">
                <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="<?php echo $t('certificatum.qr_code', [], 'Código QR'); ?>">
                <p class="cert-qr-text"><?php echo $t('certificatum.validate_certificate', [], 'Validar certificado'); ?></p>
            </div>
        </div>

    <?php
    // --- PLANTILLA DEL CERTIFICADO DE APROBACIÓN (HORIZONTAL) - DISEÑO MODERNO (sin imagen) ---
    } elseif ($tipo_documento == 'certificatum_approbationis') {
        // Obtener color secundario para degradados
        $color_secundario = $instance_config['color_secundario'] ?? '#ad5425';
    ?>
        <style>
            .certificado-moderno {
                background: linear-gradient(135deg, #fefefe 0%, #f8f9fa 50%, #f0f1f3 100%);
                position: relative;
                overflow: hidden;
            }
            .certificado-moderno::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 12px;
                background: linear-gradient(90deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 100%);
            }
            .certificado-moderno::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 12px;
                background: linear-gradient(90deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 100%);
            }
            .marco-decorativo {
                border: 3px solid <?php echo $color_primario; ?>;
                border-radius: 8px;
                position: relative;
            }
            .marco-decorativo::before {
                content: '';
                position: absolute;
                top: 8px;
                left: 8px;
                right: 8px;
                bottom: 8px;
                border: 1px solid <?php echo $color_secundario; ?>40;
                border-radius: 4px;
                pointer-events: none;
            }
            .esquina-decorativa {
                position: absolute;
                width: 60px;
                height: 60px;
                opacity: 0.15;
            }
            .esquina-tl { top: 20px; left: 20px; border-top: 4px solid <?php echo $color_primario; ?>; border-left: 4px solid <?php echo $color_primario; ?>; }
            .esquina-tr { top: 20px; right: 20px; border-top: 4px solid <?php echo $color_primario; ?>; border-right: 4px solid <?php echo $color_primario; ?>; }
            .esquina-bl { bottom: 20px; left: 20px; border-bottom: 4px solid <?php echo $color_primario; ?>; border-left: 4px solid <?php echo $color_primario; ?>; }
            .esquina-br { bottom: 20px; right: 20px; border-bottom: 4px solid <?php echo $color_primario; ?>; border-right: 4px solid <?php echo $color_primario; ?>; }
            .titulo-certificado {
                background: linear-gradient(135deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .linea-decorativa {
                height: 2px;
                background: linear-gradient(90deg, transparent 0%, <?php echo $color_primario; ?> 20%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 80%, transparent 100%);
            }
            .sello-institucional {
                position: absolute;
                right: 80px;
                bottom: 100px;
                width: 120px;
                height: 120px;
                border: 3px solid <?php echo $color_primario; ?>30;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0.6;
            }
            .sello-institucional::before {
                content: '';
                position: absolute;
                width: 100px;
                height: 100px;
                border: 1px dashed <?php echo $color_primario; ?>50;
                border-radius: 50%;
            }
            @media print {
                .certificado-moderno { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>

        <div class="printable-area-horizontal certificado-moderno shadow-2xl watermark-container">
            <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
            <!-- Esquinas decorativas -->
            <div class="esquina-decorativa esquina-tl"></div>
            <div class="esquina-decorativa esquina-tr"></div>
            <div class="esquina-decorativa esquina-bl"></div>
            <div class="esquina-decorativa esquina-br"></div>

            <div class="marco-decorativo m-6 p-8 text-center h-[720px] flex flex-col justify-between relative">
                <!-- Contenido principal -->
                <div class="pt-4">
                    <!-- Logo y nombre institución -->
                    <div class="flex items-center justify-center gap-4 mb-6">
                        <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo <?php echo htmlspecialchars($nombre_institucion); ?>" class="h-20 w-auto">
                        <div class="text-left">
                            <h1 class="text-2xl font-bold tracking-wide" style="color: <?php echo $color_primario; ?>;"><?php echo htmlspecialchars($nombre_institucion); ?></h1>
                            <p class="text-sm text-gray-500 tracking-widest uppercase"><?php echo $t('certificatum.academic_certification', [], 'Certificación Académica'); ?></p>
                        </div>
                    </div>

                    <!-- Línea decorativa -->
                    <div class="linea-decorativa mx-auto w-3/4 mb-8"></div>

                    <!-- Título del certificado -->
                    <h2 class="font-serif text-5xl font-bold titulo-certificado mb-2"><?php echo $t('certificatum.cert_type_approval', [], 'Certificado de Aprobación'); ?></h2>

                    <!-- Contenido -->
                    <p class="mt-8 text-lg text-gray-600"><?php echo $t('certificatum.cert_granted_to', [], 'Se otorga el presente certificado a'); ?></p>
                    <p class="font-serif text-4xl mt-4 font-semibold" style="color: <?php echo $color_primario; ?>;"><?php echo $nombre_alumno; ?></p>

                    <p class="mt-8 text-lg text-gray-600"><?php echo $t('certificatum.cert_for_completing', ['aprovado' => $aprobado_texto], 'por haber completado y aprobado satisfactoriamente la formación'); ?></p>
                    <p class="font-serif text-3xl mt-4 font-semibold text-gray-800"><?php echo $nombre_curso; ?></p>

                    <!-- Detalles en tarjetas -->
                    <div class="flex justify-center gap-8 mt-8">
                        <div class="px-6 py-3 rounded-lg" style="background-color: <?php echo $color_primario; ?>15;">
                            <p class="text-sm text-gray-500 uppercase tracking-wide"><?php echo $t('certificatum.workload', [], 'Carga Horaria'); ?></p>
                            <p class="text-xl font-bold" style="color: <?php echo $color_primario; ?>;"><?php echo htmlspecialchars($curso['carga_horaria']); ?> <?php echo $t('certificatum.hours', [], 'horas'); ?></p>
                        </div>
                        <div class="px-6 py-3 rounded-lg" style="background-color: <?php echo $color_secundario; ?>15;">
                            <p class="text-sm text-gray-500 uppercase tracking-wide"><?php echo $t('certificatum.completion_date', [], 'Fecha de Finalización'); ?></p>
                            <p class="text-xl font-bold" style="color: <?php echo $color_secundario; ?>;"><?php echo htmlspecialchars($curso['fecha_finalizacion']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Sello institucional decorativo -->
                <div class="sello-institucional">
                    <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Sello" class="w-16 h-16 opacity-50">
                </div>

                <!-- Footer con firma y QR -->
                <div class="flex justify-between items-end mt-6 px-4">
                    <!-- Firma -->
                    <div class="text-center w-64">
                        <?php if ($firma_url): ?>
                            <img src="<?php echo htmlspecialchars($firma_url); ?>" alt="<?php echo $t('certificatum.signature', [], 'Firma'); ?>" class="mx-auto h-16 mb-1">
                        <?php else: ?>
                            <div class="h-16 mb-1"></div>
                        <?php endif; ?>
                        <?php if ($firmante_nombre): ?>
                            <p class="text-sm font-semibold text-gray-700 uppercase"><?php echo htmlspecialchars($firmante_nombre); ?></p>
                            <p class="text-xs text-gray-500 uppercase"><?php echo htmlspecialchars($firmante_cargo ?? $nombre_institucion); ?></p>
                        <?php else: ?>
                            <div class="border-t-2 pt-2" style="border-color: <?php echo $color_primario; ?>;">
                                <p class="text-sm font-semibold text-gray-700"><?php echo $t('certificatum.authorized_signature', [], 'Firma Autorizada'); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($nombre_institucion); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Código QR -->
                    <div class="text-center">
                        <div class="p-2 rounded-lg inline-block" style="background-color: <?php echo $color_primario; ?>08; border: 1px solid <?php echo $color_primario; ?>20;">
                            <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="<?php echo $t('certificatum.qr_code', [], 'Código QR'); ?>" class="mx-auto">
                        </div>
                        <p class="text-xs mt-2 text-gray-500"><?php echo $t('certificatum.verify_at', [], 'Verifica este certificado en'); ?></p>
                        <p class="text-xs font-semibold" style="color: <?php echo $color_primario; ?>;">verumax.com</p>
                        <p class="text-xs font-mono text-gray-400 mt-1"><?php echo htmlspecialchars($codigo_unico_validacion); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php

    // --- PLANTILLA DEL CERTIFICADO DE DOCENTE/INSTRUCTOR (HORIZONTAL) - DISEÑO MODERNO ---
    } elseif (in_array($tipo_documento, ['certificatum_doctoris', 'certificatum_docente']) && $es_certificado_docente) {
        $rol = $participacion['rol'] ?? 'docente';
        $genero_docente = $docente['genero'] ?? 'Prefiero no especificar';

        // Roles que cambian con género (terminan en -or)
        $roles_con_genero_cert = [
            'instructor' => 'Instruct',
            'orador' => 'Orad',
            'expositor' => 'Exposit',
            'facilitador' => 'Facilitad',
            'tutor' => 'Tut',
            'coordinador' => 'Coordinad'
        ];

        // Roles neutros (no cambian)
        $roles_neutros_cert = ['docente' => 'Docente', 'conferencista' => 'Conferencista'];

        if (isset($roles_con_genero_cert[$rol])) {
            $rol_texto = LanguageService::getGenderedText($genero_docente, $roles_con_genero_cert[$rol], 'sufijo_or');
        } elseif (isset($roles_neutros_cert[$rol])) {
            $rol_texto = $roles_neutros_cert[$rol];
        } else {
            $rol_texto = ucfirst($rol);
        }

        $titulo_participacion = $participacion['titulo_participacion'] ?? null;
        $carga_dictada = $participacion['carga_horaria'] ?? null;
        // Obtener color secundario para degradados
        $color_secundario = $instance_config['color_secundario'] ?? '#ad5425';
        // Ruta de la firma (si existe) - usar ruta absoluta para file_exists
        $firma_filename = $institucion . '_firma.png';
        $firma_path_absolute = __DIR__ . '/../assets/images/firmas/' . $firma_filename;
        $firma_url = file_exists($firma_path_absolute) ? '/assets/images/firmas/' . $firma_filename : null;
    ?>
        <style>
            .certificado-docente {
                background: linear-gradient(135deg, #fefefe 0%, #f8f4fa 50%, #f3eef5 100%);
                position: relative;
                overflow: hidden;
            }
            .certificado-docente::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 12px;
                background: linear-gradient(90deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 100%);
            }
            .certificado-docente::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 12px;
                background: linear-gradient(90deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 100%);
            }
            .marco-docente {
                border: 3px solid <?php echo $color_primario; ?>;
                border-radius: 8px;
                position: relative;
            }
            .marco-docente::before {
                content: '';
                position: absolute;
                top: 8px;
                left: 8px;
                right: 8px;
                bottom: 8px;
                border: 1px solid <?php echo $color_secundario; ?>40;
                border-radius: 4px;
                pointer-events: none;
            }
            .titulo-docente {
                background: linear-gradient(135deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .badge-rol {
                display: inline-block;
                padding: 0.5rem 1.5rem;
                border-radius: 9999px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.1em;
                font-size: 0.875rem;
            }
            .linea-docente {
                height: 2px;
                background: linear-gradient(90deg, transparent 0%, <?php echo $color_primario; ?> 20%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 80%, transparent 100%);
            }
            .sello-docente {
                position: absolute;
                right: 80px;
                bottom: 100px;
                width: 120px;
                height: 120px;
                border: 3px solid <?php echo $color_primario; ?>30;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0.6;
            }
            .sello-docente::before {
                content: '';
                position: absolute;
                width: 100px;
                height: 100px;
                border: 1px dashed <?php echo $color_primario; ?>50;
                border-radius: 50%;
            }
            @media print {
                .certificado-docente { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>

        <div class="printable-area-horizontal certificado-docente shadow-2xl watermark-container">
            <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
            <!-- Esquinas decorativas -->
            <div class="esquina-decorativa esquina-tl" style="border-color: <?php echo $color_primario; ?>;"></div>
            <div class="esquina-decorativa esquina-tr" style="border-color: <?php echo $color_primario; ?>;"></div>
            <div class="esquina-decorativa esquina-bl" style="border-color: <?php echo $color_primario; ?>;"></div>
            <div class="esquina-decorativa esquina-br" style="border-color: <?php echo $color_primario; ?>;"></div>

            <div class="marco-docente m-6 p-8 text-center h-[720px] flex flex-col justify-between relative">
                <!-- Contenido principal -->
                <div class="pt-4">
                    <!-- Logo y nombre institución -->
                    <div class="flex items-center justify-center gap-4 mb-4">
                        <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo <?php echo htmlspecialchars($nombre_institucion); ?>" class="h-20 w-auto">
                        <div class="text-left">
                            <h1 class="text-2xl font-bold tracking-wide" style="color: <?php echo $color_primario; ?>;"><?php echo htmlspecialchars($nombre_institucion); ?></h1>
                            <p class="text-sm text-gray-500 tracking-widest uppercase"><?php echo $t('certificatum.teacher_certification', [], 'Certificación Docente'); ?></p>
                        </div>
                    </div>

                    <!-- Línea decorativa -->
                    <div class="linea-docente mx-auto w-3/4 mb-6"></div>

                    <!-- Título del certificado según estado -->
                    <?php if ($es_constancia_asignacion): ?>
                        <h2 class="font-serif text-4xl font-bold titulo-docente mb-2"><?php echo $t('certificatum.assignment_certificate', [], 'Constancia de Asignación'); ?></h2>
                        <p class="text-sm text-blue-600 mb-2"><i><?php echo $t('certificatum.assignment_note', [], 'Curso por iniciar'); ?></i></p>
                    <?php elseif ($es_constancia_en_curso): ?>
                        <h2 class="font-serif text-4xl font-bold titulo-docente mb-2"><?php echo $t('certificatum.provisional_certificate', [], 'Constancia de Participación'); ?></h2>
                        <p class="text-sm text-amber-600 mb-2"><i><?php echo $t('certificatum.provisional_note', [], 'Documento provisional - Curso en progreso'); ?></i></p>
                    <?php else: ?>
                        <h2 class="font-serif text-4xl font-bold titulo-docente mb-2"><?php echo $t('certificatum.participation_certificate', [], 'Certificado de Participación'); ?></h2>
                    <?php endif; ?>

                    <!-- Badge del rol -->
                    <div class="badge-rol mt-3" style="background-color: <?php echo $color_primario; ?>20; color: <?php echo $color_primario; ?>;">
                        <?php echo htmlspecialchars($rol_texto); ?>
                    </div>

                    <!-- Contenido -->
                    <p class="mt-6 text-lg text-gray-600"><?php echo $t('certificatum.it_is_certified_that', [], 'Se certifica que'); ?></p>
                    <p class="font-serif text-4xl mt-3 font-semibold" style="color: <?php echo $color_primario; ?>;"><?php echo $nombre_alumno; ?></p>
                    <?php if (!empty($docente['titulo'])): ?>
                        <p class="text-lg text-gray-500 mt-1 italic"><?php echo htmlspecialchars($docente['titulo']); ?></p>
                    <?php endif; ?>

                    <?php
                    $rol_html = '<strong style="color: ' . $color_primario . ';">' . htmlspecialchars($rol_texto) . '</strong>';
                    // Obtener raíz traducida para "asignado/designado" y generar texto con género
                    $assigned_root = $t('certificatum.assigned_root', [], 'asignad');
                    $asignado_texto = LanguageService::getGenderedText($docente['genero'] ?? '', $assigned_root, 'sufijo_o');
                    if ($es_constancia_asignacion): ?>
                        <p class="mt-6 text-lg text-gray-600"><?php echo $t('certificatum.has_been_assigned_as', ['rol' => $rol_html, 'asignado' => $asignado_texto], "ha sido {$asignado_texto} como {$rol_html} en"); ?></p>
                    <?php elseif ($es_constancia_en_curso): ?>
                        <p class="mt-6 text-lg text-gray-600"><?php echo $t('certificatum.is_participating_as', ['rol' => $rol_html], "está participando como {$rol_html} en"); ?></p>
                    <?php else: ?>
                        <p class="mt-6 text-lg text-gray-600"><?php echo $t('certificatum.participated_as', ['rol' => $rol_html], "participó como {$rol_html} en"); ?></p>
                    <?php endif; ?>
                    <p class="font-serif text-3xl mt-3 font-semibold text-gray-800"><?php echo $nombre_curso; ?></p>
                    <?php if ($titulo_participacion): ?>
                        <p class="mt-2 text-xl text-gray-600 italic">"<?php echo htmlspecialchars($titulo_participacion); ?>"</p>
                    <?php endif; ?>
                    <?php if ($cohorte_nombre): ?>
                        <p class="mt-2 text-gray-500"><?php echo $t('certificatum.cohort', [], 'Cohorte'); ?>: <strong><?php echo htmlspecialchars($cohorte_nombre); ?></strong></p>
                    <?php endif; ?>

                    <!-- Detalles en tarjetas -->
                    <div class="flex justify-center gap-6 mt-6">
                        <?php if ($carga_dictada): ?>
                        <div class="px-5 py-2 rounded-lg" style="background-color: <?php echo $color_primario; ?>15;">
                            <p class="text-xs text-gray-500 uppercase tracking-wide"><?php echo $t('certificatum.workload', [], 'Carga Horaria'); ?></p>
                            <p class="text-lg font-bold" style="color: <?php echo $color_primario; ?>;"><?php echo htmlspecialchars($carga_dictada); ?> <?php echo $t('certificatum.hours', [], 'horas'); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($curso['fecha_inicio']) && !empty($curso['fecha_finalizacion'])): ?>
                        <div class="px-5 py-2 rounded-lg" style="background-color: <?php echo $color_secundario; ?>15;">
                            <p class="text-xs text-gray-500 uppercase tracking-wide"><?php echo $t('certificatum.period', [], 'Período'); ?></p>
                            <p class="text-lg font-bold" style="color: <?php echo $color_secundario; ?>;"><?php echo htmlspecialchars($curso['fecha_inicio']); ?> <?php echo $t('certificatum.to', [], 'al'); ?> <?php echo htmlspecialchars($curso['fecha_finalizacion']); ?></p>
                        </div>
                        <?php elseif (!empty($curso['fecha_finalizacion'])): ?>
                        <div class="px-5 py-2 rounded-lg" style="background-color: <?php echo $color_secundario; ?>15;">
                            <p class="text-xs text-gray-500 uppercase tracking-wide"><?php echo $t('certificatum.completion_date', [], 'Fecha de Finalización'); ?></p>
                            <p class="text-lg font-bold" style="color: <?php echo $color_secundario; ?>;"><?php echo htmlspecialchars($curso['fecha_finalizacion']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sello institucional decorativo -->
                <div class="sello-docente">
                    <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Sello" class="w-16 h-16 opacity-50">
                </div>

                <!-- Footer con firma y QR -->
                <div class="flex justify-between items-end mt-4 px-4">
                    <!-- Firma -->
                    <div class="text-center w-64">
                        <?php if ($firma_url): ?>
                            <img src="<?php echo htmlspecialchars($firma_url); ?>" alt="<?php echo $t('certificatum.signature', [], 'Firma'); ?>" class="mx-auto h-16 mb-1">
                        <?php else: ?>
                            <div class="h-16 mb-1"></div>
                        <?php endif; ?>
                        <?php if ($firmante_nombre): ?>
                            <p class="text-sm font-semibold text-gray-700 uppercase"><?php echo htmlspecialchars($firmante_nombre); ?></p>
                            <p class="text-xs text-gray-500 uppercase"><?php echo htmlspecialchars($firmante_cargo ?? $nombre_institucion); ?></p>
                        <?php else: ?>
                            <div class="border-t-2 pt-2" style="border-color: <?php echo $color_primario; ?>;">
                                <p class="text-sm font-semibold text-gray-700"><?php echo $t('certificatum.authorized_signature', [], 'Firma Autorizada'); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($nombre_institucion); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Código QR -->
                    <div class="text-center">
                        <div class="p-2 rounded-lg inline-block" style="background-color: <?php echo $color_primario; ?>08; border: 1px solid <?php echo $color_primario; ?>20;">
                            <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="<?php echo $t('certificatum.qr_code', [], 'Código QR'); ?>" class="mx-auto">
                        </div>
                        <p class="text-xs mt-2 text-gray-500"><?php echo $t('certificatum.verify_at', [], 'Verifica este certificado en'); ?></p>
                        <p class="text-xs font-semibold" style="color: <?php echo $color_primario; ?>;">verumax.com</p>
                        <p class="text-xs font-mono text-gray-400 mt-1"><?php echo htmlspecialchars($codigo_unico_validacion); ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php

    // --- PLANTILLAS DE CONSTANCIAS (VERTICAL) ---
    } elseif (in_array($tipo_documento, ['testimonium_regulare', 'testimonium_completionis', 'testimonium_inscriptionis', 'testimonium_doctoris'])) {
        $titulo_constancia = $t('certificatum.constancy', [], 'Constancia');
        $cuerpo_constancia = "";
        $es_constancia_docente = ($tipo_documento === 'testimonium_doctoris');

        switch($tipo_documento){
            case 'testimonium_regulare':
                $titulo_constancia = $t('certificatum.constancy_regular_student', [], 'Constancia de Alumno Regular');
                $cuerpo_constancia = $t('certificatum.constancy_body_regular', [], 'se encuentra cursando activamente la formación:');
                break;
            case 'testimonium_completionis':
                $titulo_constancia = $t('certificatum.constancy_completion', [], 'Constancia de Finalización');
                $cuerpo_constancia = $t('certificatum.constancy_body_completion', [], 'ha finalizado la cursada de la formación:');
                break;
            case 'testimonium_inscriptionis':
                $titulo_constancia = $t('certificatum.constancy_enrollment', [], 'Constancia de Inscripción');
                $cuerpo_constancia = $t('certificatum.constancy_body_enrollment', ['inscripto' => $inscripto_texto], 'se encuentra ' . $inscripto_texto . ' para comenzar la formación:');
                break;
            case 'testimonium_doctoris':
                // Constancia de docente según estado
                $rol_base = $participacion['rol'] ?? 'docente';
                $genero_docente = $docente['genero'] ?? 'Prefiero no especificar';

                // Roles que cambian con género (terminan en -or)
                $roles_con_genero = [
                    'instructor' => 'Instruct',
                    'orador' => 'Orad',
                    'expositor' => 'Exposit',
                    'facilitador' => 'Facilitad',
                    'tutor' => 'Tut',
                    'coordinador' => 'Coordinad'
                ];

                // Roles neutros (no cambian)
                $roles_neutros = ['docente' => 'Docente', 'conferencista' => 'Conferencista'];

                if (isset($roles_con_genero[$rol_base])) {
                    $rol_texto = LanguageService::getGenderedText($genero_docente, $roles_con_genero[$rol_base], 'sufijo_or');
                } elseif (isset($roles_neutros[$rol_base])) {
                    $rol_texto = $roles_neutros[$rol_base];
                } else {
                    $rol_texto = $participacion['rol_display'] ?? ucfirst($rol_base);
                }

                // Obtener raíz traducida para "asignado/designado"
                $assigned_root = $t('certificatum.assigned_root', [], 'asignad');
                $asignado_texto = LanguageService::getGenderedText($genero_docente, $assigned_root, 'sufijo_o');

                if ($es_constancia_asignacion) {
                    $titulo_constancia = $t('certificatum.assignment_certificate', [], 'Constancia de Asignación');
                    $cuerpo_constancia = $t('certificatum.has_been_assigned_body', ['asignado' => $asignado_texto, 'rol' => "<strong>{$rol_texto}</strong>"], "ha sido {$asignado_texto} como <strong>{$rol_texto}</strong> en la formación:");
                } else {
                    $titulo_constancia = $t('certificatum.provisional_certificate', [], 'Constancia de Participación');
                    $cuerpo_constancia = $t('certificatum.is_participating_body', ['rol' => "<strong>{$rol_texto}</strong>"], "está participando como <strong>{$rol_texto}</strong> en la formación:");
                }
                break;
        }

        // Calcular lugar_fecha para constancias
        $ciudad_emision_const = $curso['ciudad_emision'] ?? null;
        // Usar fecha de finalización si existe, sino fecha actual
        $fecha_raw_const = !empty($curso['fecha_finalizacion_raw']) ? $curso['fecha_finalizacion_raw'] : date('Y-m-d');
        $fecha_ts_const = strtotime($fecha_raw_const);
        // Fallback a fecha actual si strtotime falla
        if ($fecha_ts_const === false) {
            $fecha_ts_const = time();
        }
        $dia_const = date('j', $fecha_ts_const);
        $mes_const = LanguageService::getMonthName((int)date('m', $fecha_ts_const));
        $anio_const = date('Y', $fecha_ts_const);

        if (!empty($ciudad_emision_const)) {
            $lugar_fecha_const = $t('certificatum.lugar_fecha_con_ciudad', [
                'ciudad' => $ciudad_emision_const,
                'dia' => $dia_const,
                'mes' => $mes_const,
                'anio' => $anio_const
            ], "En la ciudad de {$ciudad_emision_const}, a los {$dia_const} días del mes de {$mes_const} de {$anio_const}");
        } else {
            $lugar_fecha_const = $t('certificatum.lugar_fecha_sin_ciudad', [
                'dia' => $dia_const,
                'mes' => $mes_const,
                'anio' => $anio_const
            ], "A los {$dia_const} días del mes de {$mes_const} de {$anio_const}");
        }
    ?>
        <div class="printable-area-vertical bg-white p-10 shadow-lg watermark-container">
            <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
            <div class="h-[1050px] flex flex-col">
                <header class="flex justify-between items-center pb-4 border-b">
                    <div>
                        <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($nombre_institucion); ?></h1>
                        <p class="text-gray-600"><?php echo htmlspecialchars($titulo_constancia); ?></p>
                    </div>
                    <img src="<?php echo htmlspecialchars($logo_url_small); ?>" alt="Logo <?php echo htmlspecialchars($nombre_institucion); ?>" class="h-20 w-auto">
                </header>
                <main class="flex-grow pt-16 text-lg leading-relaxed">
                    <p class="mb-8"><?php echo $t('certificatum.constancy_intro', [], 'Por medio de la presente, se deja constancia que'); ?> <strong><?php echo $nombre_alumno; ?></strong>, <?php echo $t('certificatum.dni_label', [], 'D.N.I. N°'); ?> <strong><?php echo htmlspecialchars($dni); ?></strong>, <?php echo $cuerpo_constancia; ?></p>
                    <p class="text-center text-2xl font-bold my-8 bg-gray-100 p-4 rounded-lg"><?php echo $nombre_curso; ?></p>
                    <?php if($tipo_documento == 'testimonium_inscriptionis'): ?>
                        <p><?php echo $t('certificatum.start_date_scheduled', ['fecha_inicio' => htmlspecialchars($curso['fecha_inicio'])], 'La fecha de inicio estipulada es el ' . htmlspecialchars($curso['fecha_inicio']) . '.'); ?></p>
                    <?php endif; ?>
                    <?php if($es_constancia_docente && $es_constancia_asignacion): ?>
                        <p class="text-sm text-blue-600 italic mt-4"><?php echo $t('certificatum.status_assigned', [], 'Estado: Asignado - Curso por iniciar'); ?></p>
                    <?php elseif($es_constancia_docente && $es_constancia_en_curso): ?>
                        <p class="text-sm text-amber-600 italic mt-4"><?php echo $t('certificatum.status_in_progress', [], 'Estado: En curso'); ?></p>
                    <?php endif; ?>
                    <p class="mt-12"><?php echo $t('certificatum.constancy_closing', [], 'Se extiende la presente constancia a los fines que estime corresponder.'); ?></p>
                    <p class="mt-8 text-right text-gray-600 italic"><?php echo htmlspecialchars($lugar_fecha_const); ?>.</p>
                </main>
                <footer class="text-center pt-8 border-t border-gray-200">
                    <!-- Firma -->
                    <div class="mb-4">
                        <?php if ($firma_url): ?>
                            <img src="<?php echo htmlspecialchars($firma_url); ?>" alt="<?php echo $t('certificatum.signature', [], 'Firma'); ?>" class="mx-auto h-28">
                        <?php else: ?>
                            <div class="h-28"></div>
                        <?php endif; ?>
                        <?php if ($firmante_nombre): ?>
                            <div class="w-48 border-t border-gray-400 mx-auto mb-1"></div>
                            <p class="text-sm font-semibold text-gray-700 uppercase"><?php echo htmlspecialchars($firmante_nombre); ?></p>
                            <?php if ($firmante_cargo): ?>
                                <p class="text-xs text-gray-500 uppercase"><?php echo htmlspecialchars($firmante_cargo); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="w-48 border-t border-gray-400 mx-auto"></div>
                            <p class="text-sm font-semibold text-gray-700"><?php echo $t('certificatum.authorized_signature', [], 'Firma Autorizada'); ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- QR -->
                    <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="<?php echo $t('certificatum.qr_code', [], 'Código QR'); ?>" class="mx-auto h-20">
                    <p class="text-xs mt-1"><?php echo $t('certificatum.scan_qr_to_verify', [], 'Para verificar la validez de este documento, escanee el código QR.'); ?></p>
                    <p class="text-xs font-mono"><?php echo htmlspecialchars($codigo_unico_validacion); ?></p>
                </footer>
            </div>
        </div>
    <?php

    // --- PLANTILLA DEL ANALÍTICO (VERTICAL) ---
    } else { // 'analyticum' es el default
    ?>
        <div class="printable-area-vertical bg-white p-6 sm:p-10 shadow-lg rounded-xl watermark-container">
            <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
            <header class="bg-gray-50 rounded-t-xl p-6 flex justify-between items-center">
                <div>
                    <p class="text-sm font-semibold" style="color: <?php echo htmlspecialchars($color_primario); ?>;"><?php echo strtoupper($t('certificatum.academic_trajectory', [], 'TRAYECTORIA ACADÉMICA')); ?></p>
                    <h1 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $nombre_curso; ?></h1>
                    <p class="text-gray-600 mt-1"><?php echo $t('certificatum.student', [], 'Estudiante'); ?>: <?php echo $nombre_alumno; ?> (<?php echo $t('certificatum.dni_short', [], 'DNI'); ?>: <?php echo htmlspecialchars($dni); ?>)</p>
                </div>
                <img src="<?php echo htmlspecialchars($logo_url_small); ?>" alt="Logo <?php echo htmlspecialchars($nombre_institucion); ?>" class="h-16 w-auto rounded-lg">
            </header>
            <div class="p-6 grid md:grid-cols-3 gap-8">
                <div class="md:col-span-2">
                    <h3 class="font-bold text-lg text-gray-800 mb-6"><?php echo $t('certificatum.course_timeline', [], 'Línea de Tiempo del Curso'); ?></h3>
                    <div class="relative pl-4 border-l-2 border-gray-200">
                        <?php foreach($curso['trayectoria'] as $item): ?>
                        <div class="mb-8 relative">
                            <div class="absolute -left-[23px] top-1 bg-white p-1 rounded-full">
                                <div class="w-4 h-4 rounded-full" style="background-color: <?php echo htmlspecialchars($color_primario); ?>;"></div>
                            </div>
                            <p class="font-semibold text-gray-700"><?php echo htmlspecialchars($item['evento']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['fecha'] ?? ''); ?></p>
                            <?php if(!empty($item['detalle'])): ?>
                                <p class="text-sm font-medium mt-1" style="color: <?php echo htmlspecialchars($color_primario); ?>;"><?php echo htmlspecialchars($item['detalle']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <h3 class="font-bold text-lg text-gray-800 mb-4"><?php echo $t('certificatum.summary', [], 'Resumen'); ?></h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600"><?php echo $t('certificatum.final_grade', [], 'Nota Final'); ?></dt>
                                <dd class="font-semibold text-gray-900"><?php echo htmlspecialchars($curso['nota_final']); ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600"><?php echo $t('certificatum.attendance', [], 'Asistencia'); ?></dt>
                                <dd class="font-semibold text-gray-900"><?php echo htmlspecialchars($curso['asistencia']); ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600"><?php echo $t('certificatum.workload', [], 'Carga Horaria'); ?></dt>
                                <dd class="font-semibold text-gray-900"><?php echo htmlspecialchars($curso['carga_horaria']); ?> <?php echo $t('certificatum.hours_short', [], 'hs.'); ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600"><?php echo $t('certificatum.completion', [], 'Finalización'); ?></dt>
                                <dd class="font-semibold text-gray-900"><?php echo htmlspecialchars($curso['fecha_finalizacion']); ?></dd>
                            </div>
                        </dl>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-sm border">
                        <h3 class="font-bold text-lg text-gray-800 mb-4"><?php echo $t('certificatum.competencies', [], 'Competencias'); ?></h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($curso['competencias'] as $c):
                                // Generar clave de traducción desde el texto de la competencia
                                $c_key = 'certificatum.competency_' . strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'], ['_', 'a', 'e', 'i', 'o', 'u', 'n'], $c));
                                $c_traducida = $t($c_key, [], $c);
                            ?>
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1.5 rounded-full"><?php echo htmlspecialchars($c_traducida); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="text-center mt-8 pt-4 border-t border-gray-200">
                 <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="<?php echo $t('certificatum.qr_code', [], 'Código QR'); ?>" class="mx-auto">
                 <p class="text-xs mt-2 text-gray-600"><?php echo $t('certificatum.scan_qr_to_verify', [], 'Para verificar la validez de este documento, escanee el código QR.'); ?></p>
                 <p class="text-xs font-mono mt-1 text-gray-800"><?php echo htmlspecialchars($codigo_unico_validacion); ?></p>
            </footer>
        </div>
    <?php } ?>

<!-- Script para responsive de certificados -->
<script>
(function() {
    function scaleCertificates() {
        // Buscar todos los certificados (cualquier tipo)
        const selectors = '.certificado-json, .certificado-imagen, .certificado-moderno, .certificado-docente, .printable-area-horizontal';
        const certificates = document.querySelectorAll(selectors);

        certificates.forEach(cert => {
            const certWidth = cert.scrollWidth || cert.offsetWidth;
            const certHeight = cert.scrollHeight || cert.offsetHeight;
            const viewportWidth = window.innerWidth;
            const padding = 16;
            const availableWidth = viewportWidth - (padding * 2);

            // Solo escalar si el certificado es más ancho que el viewport
            if (viewportWidth <= 900 && certWidth > availableWidth) {
                const scale = availableWidth / certWidth;

                // Crear wrapper si no existe
                let wrapper = cert.parentElement;
                if (!wrapper.classList.contains('cert-responsive-wrapper')) {
                    wrapper = document.createElement('div');
                    wrapper.className = 'cert-responsive-wrapper';
                    wrapper.style.cssText = 'overflow:hidden;width:100%;display:flex;justify-content:center;';
                    cert.parentNode.insertBefore(wrapper, cert);
                    wrapper.appendChild(cert);
                }

                cert.style.transform = 'scale(' + scale + ')';
                cert.style.transformOrigin = 'top center';
                wrapper.style.height = Math.ceil(certHeight * scale) + 'px';
            } else {
                cert.style.transform = '';
                const wrapper = cert.parentElement;
                if (wrapper && wrapper.classList.contains('cert-responsive-wrapper')) {
                    wrapper.style.height = '';
                }
            }
        });
    }

    // Ejecutar al cargar
    window.addEventListener('load', scaleCertificates);

    // Redimensionar con debounce
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(scaleCertificates, 100);
    });
})();
</script>

</main>


<?php
// Incluir footer compartido (no se imprime) - ocultar en modo preview
if (empty($es_preview_mode)):
    include __DIR__ . '/../templates/shared/footer.php';
endif;
?>
