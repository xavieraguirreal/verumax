<?php
/**
 * Creare Content - Genera solo el HTML del documento (para PDF)
 * Sistema CERTIFICATUM - VERUMax
 *
 * Este archivo genera únicamente el contenido del documento sin header/footer
 * de navegación, ideal para conversión a PDF con mPDF.
 */

// Este archivo debe ser incluido desde creare_pdf.php
// Las variables ya deben estar definidas: $institucion, $dni, $curso_id, etc.

if (!defined('PDF_MODE')) {
    die('Acceso directo no permitido.');
}

/**
 * Determina si un curso es de día único o rango de fechas
 * @param string|null $fecha_inicio
 * @param string|null $fecha_fin
 * @return bool|null true=día único, false=rango, null=sin fechas
 */
function esCursoDiaUnico($fecha_inicio, $fecha_fin) {
    if (!$fecha_inicio || !$fecha_fin) return null;
    return $fecha_inicio === $fecha_fin;
}

// Cargar configuración si no está cargada
if (!class_exists('VERUMax\Services\StudentService')) {
    require_once __DIR__ . '/config.php';
}

use VERUMax\Services\StudentService;
use VERUMax\Services\CertificateService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\QRCodeService;

// Obtener parámetros (pueden venir de $_GET o estar ya definidos)
$institucion = $institucion ?? $_GET['institutio'] ?? null;
$dni = $dni ?? $_GET['documentum'] ?? null;
$curso_id = $curso_id ?? $_GET['cursus'] ?? null;
$participacion_id = $participacion_id ?? $_GET['participacion'] ?? null;
$tipo_documento = $tipo_documento ?? $_GET['genus'] ?? 'analyticum';

if (!$institucion) {
    die('Error: Institución no especificada.');
}

// Inicializar idioma si no está inicializado
$instance_config = InstitutionService::getConfig($institucion);
$lang_request = $_GET['lang'] ?? null;
LanguageService::init($institucion, $lang_request);
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Detectar si es instancia de prueba (plan = 'test')
$es_instancia_test = ($instance_config['plan'] ?? '') === 'test';

// Validar y buscar datos
// Aceptar certificatum_docente como alias de certificatum_doctoris (compatibilidad)
$es_certificado_docente = (in_array($tipo_documento, ['certificatum_doctoris', 'certificatum_docente']) && $participacion_id);

if ($es_certificado_docente) {
    $datos = StudentService::getParticipacionDocente($institucion, $dni, (int)$participacion_id);
    if (!$datos) {
        die("Error: Datos de participación docente no encontrados.");
    }
    $docente = [
        'nombre_completo' => $datos['nombre_completo'],
        'dni' => $datos['dni'],
        'especialidad' => $datos['especialidad'] ?? '',
        'titulo' => $datos['titulo'] ?? ''
    ];
    $participacion = $datos['participacion'];
    $alumno = ['nombre_completo' => $datos['nombre_completo']];
    $curso = [
        'nombre_curso' => $participacion['nombre_curso'],
        'carga_horaria' => $participacion['carga_horaria_dictada'] ?? $participacion['carga_horaria'],
        'fecha_finalizacion' => $participacion['fecha_fin'] ? date('d/m/Y', strtotime($participacion['fecha_fin'])) : date('d/m/Y'),
        'fecha_finalizacion_raw' => $participacion['fecha_fin'] ?? date('Y-m-d'), // Para lugar_fecha
        'fecha_inicio' => $participacion['fecha_inicio'] ? date('d/m/Y', strtotime($participacion['fecha_inicio'])) : null,
        // Campos para lugar_fecha y fecha_curso
        'ciudad_emision' => $participacion['ciudad_emision'] ?? null,
        'curso_fecha_inicio_raw' => $participacion['curso_fecha_inicio'] ?? null,
        'curso_fecha_fin_raw' => $participacion['curso_fecha_fin'] ?? null
    ];
    $curso_id = $participacion['codigo_curso'];
} else {
    $datos = StudentService::getCourse($institucion, $dni, $curso_id);
    if (!$datos) {
        die("Error: Datos no encontrados para generar el documento.");
    }
    $alumno = ['nombre_completo' => $datos['nombre_completo']];
    $curso = $datos['curso'];
}

// Preparar variables comunes
// Convertir nombre a formato título (primera letra mayúscula de cada palabra)
$nombre_alumno = htmlspecialchars(mb_convert_case(strtolower($alumno['nombre_completo']), MB_CASE_TITLE, 'UTF-8'));
$nombre_curso = htmlspecialchars($curso['nombre_curso']);

// Calcular fecha_curso (período del curso: día único o rango)
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
    $fecha_curso = $t('certificatum.dictado_del_al', [
        'fecha_inicio' => LanguageService::formatDate($curso_fecha_inicio, true),
        'fecha_fin' => LanguageService::formatDate($curso_fecha_fin, false)
    ], 'dictado del ' . LanguageService::formatDate($curso_fecha_inicio, true) . ' al ' . LanguageService::formatDate($curso_fecha_fin, false));
} else {
    // Sin fechas definidas en el curso
    $fecha_curso = '';
}

// Calcular lugar_fecha (frase con o sin ciudad)
$ciudad_emision = $curso['ciudad_emision'] ?? null;
// Usar fecha de finalización si existe, sino fecha actual
$fecha_raw_doc = !empty($curso['fecha_finalizacion_raw']) ? $curso['fecha_finalizacion_raw'] : date('Y-m-d');
$fecha_ts_doc = strtotime($fecha_raw_doc);
// Fallback a fecha actual si strtotime falla
if ($fecha_ts_doc === false) {
    $fecha_ts_doc = time();
}
$dia_num = date('j', $fecha_ts_doc);
$mes_nombre = LanguageService::getMonthName((int)date('m', $fecha_ts_doc));
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

// Generar o recuperar código de validación
if ($es_certificado_docente) {
    $codigo_unico_validacion = $participacion['codigo_validacion']
        ?? CertificateService::getValidationCode($institucion, $dni, $curso_id . '_docente_' . $participacion_id, $tipo_documento);
} else {
    $codigo_unico_validacion = CertificateService::getValidationCode($institucion, $dni, $curso_id, $tipo_documento);
}

// Incluir sistema de detección automática de institución
require_once __DIR__ . '/autodetect.php';

// URL de validación
if (esSubdominioInstitucion()) {
    $url_validacion = obtenerURLBaseInstitucion() . "/validare.php?codigo=" . $codigo_unico_validacion;
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $domain = $_SERVER['HTTP_HOST'] ?? 'www.verumax.com';
    $base = $protocol . $domain;
    $url_validacion = $base . "/certificatum/validare.php?codigo=" . $codigo_unico_validacion;
}

// Generar URL del código QR
$qr_url = QRCodeService::generate($url_validacion, 100);

// Obtener branding de la configuración
$logo_url = $instance_config['logo_url'] ?? 'https://placehold.co/100x100/3b82f6/ffffff?text=' . strtoupper(substr($institucion, 0, 2));
$logo_url_small = $instance_config['logo_url'] ?? 'https://placehold.co/80x80/3b82f6/ffffff?text=' . strtoupper(substr($institucion, 0, 2));
$nombre_institucion = $instance_config['nombre'] ?? 'Institución Educativa';
$color_primario = $instance_config['color_primario'] ?? '#2E7D32';
$color_secundario = $instance_config['color_secundario'] ?? '#ad5425';

// Mapeo de roles para mostrar
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

// Detectar si existe un template de imagen para esta institución
$template_imagen_path = __DIR__ . '/../assets/templates/certificados/' . $institucion . '/template_clasico.jpg';
$template_imagen_url = null;
if (file_exists($template_imagen_path)) {
    $imageData = base64_encode(file_get_contents($template_imagen_path));
    $imageType = 'image/jpeg';
    $template_imagen_url = 'data:' . $imageType . ';base64,' . $imageData;
}

// Ruta de la firma (si existe)
$firma_filename = $institucion . '_firma.png';
$firma_path_absolute = __DIR__ . '/../assets/images/firmas/' . $firma_filename;
$firma_url = null;
if (file_exists($firma_path_absolute)) {
    $firmaData = base64_encode(file_get_contents($firma_path_absolute));
    $firma_url = 'data:image/png;base64,' . $firmaData;
}

// Cargar logo como base64 para PDF
$logo_base64 = null;
if (!empty($instance_config['logo_url'])) {
    $logo_path = __DIR__ . '/../' . ltrim($instance_config['logo_url'], '/');
    if (file_exists($logo_path)) {
        $logoData = base64_encode(file_get_contents($logo_path));
        $ext = pathinfo($logo_path, PATHINFO_EXTENSION);
        $mimeTypes = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'svg' => 'image/svg+xml'];
        $logoMime = $mimeTypes[$ext] ?? 'image/png';
        $logo_base64 = 'data:' . $logoMime . ';base64,' . $logoData;
    }
}
$logo_url_pdf = $logo_base64 ?? $logo_url;

// --- GENERAR HTML DEL DOCUMENTO SEGÚN TIPO ---

// CERTIFICADO CON IMAGEN DE FONDO
if (($tipo_documento == 'certificatum_approbationis' || in_array($tipo_documento, ['certificatum_doctoris', 'certificatum_docente'])) && $template_imagen_url) {
    $es_cert_docente = (in_array($tipo_documento, ['certificatum_doctoris', 'certificatum_docente']) && $es_certificado_docente);
    $tipo_cert_texto = $es_cert_docente
        ? $t('certificatum.cert_type_trainer', [], 'Certificado de Formador/a')
        : $t('certificatum.cert_type_approval', [], 'Certificado de Aprobación');

    $fecha_formateada = LanguageService::formatDate(date('Y-m-d'), true);

    if ($es_cert_docente) {
        $texto_descripcion = $t('certificatum.cert_desc_trainer', [
            'fecha' => $fecha_formateada,
            'nombre' => '<strong>' . $nombre_alumno . '</strong>',
            'dni' => '<strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong>',
            'nombre_curso' => '<strong>' . $nombre_curso . '</strong>'
        ], 'El día ' . $fecha_formateada . ' se certifica que <strong>' . $nombre_alumno . '</strong> con DNI <strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong> ha desempeñado una destacada labor como formador/a de "<strong>' . $nombre_curso . '</strong>", impartiendo sus conocimientos con un alto nivel de competencia.');
    } else {
        $texto_descripcion = $t('certificatum.cert_desc_approval', [
            'fecha' => $fecha_formateada,
            'nombre' => '<strong>' . $nombre_alumno . '</strong>',
            'dni' => '<strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong>',
            'nombre_curso' => '<strong>' . $nombre_curso . '</strong>',
            'carga_horaria' => '<strong>' . $curso['carga_horaria'] . '</strong>'
        ], 'El día ' . $fecha_formateada . ' se certifica que <strong>' . $nombre_alumno . '</strong> con DNI <strong>' . number_format((float)str_replace('.', '', $dni), 0, ',', '.') . '</strong> ha completado y aprobado satisfactoriamente el curso "<strong>' . $nombre_curso . '</strong>" con una carga horaria de <strong>' . $curso['carga_horaria'] . ' horas</strong>.');
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 297mm 210mm landscape;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
        }
        .certificado-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            background-image: url('<?php echo $template_imagen_url; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        /* Nombre del curso - debajo de "Sociedad Argentina de Justicia Restaurativa" */
        .cert-curso {
            position: absolute;
            top: 19mm;
            left: 0;
            right: 0;
            text-align: center;
        }
        .cert-curso p {
            font-size: 13pt;
            font-style: italic;
            font-weight: 600;
            color: #b8860b;
            margin: 0;
        }
        /* Tipo de certificado - debajo de la línea dorada de CERTIFICADO */
        .cert-tipo {
            position: absolute;
            top: 68mm;
            left: 0;
            right: 0;
            text-align: center;
        }
        .cert-tipo .intro {
            font-size: 10pt;
            color: #555;
            margin: 0 0 1mm 0;
        }
        .cert-tipo .tipo-cert {
            font-size: 11pt;
            color: #1a5276;
            font-weight: bold;
            margin: 0;
        }
        /* Nombre del destinatario - sobre la línea horizontal */
        .cert-nombre {
            position: absolute;
            top: 88mm;
            left: 50mm;
            right: 50mm;
            text-align: center;
        }
        .cert-nombre-texto {
            font-size: 28pt;
            font-style: italic;
            color: #2c3e50;
            margin: 0;
        }
        /* Descripción - debajo de la línea horizontal */
        .cert-descripcion {
            position: absolute;
            top: 115mm;
            left: 30mm;
            right: 30mm;
            text-align: center;
        }
        .cert-descripcion p {
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
            margin: 0;
        }
        /* QR - centro inferior (entre logo SAJuR y firma) */
        .cert-qr {
            position: absolute;
            bottom: 15mm;
            left: 50%;
            margin-left: -10mm;
            text-align: center;
        }
        .cert-qr img {
            width: 20mm;
            height: 20mm;
        }
        .cert-qr-text {
            font-size: 7pt;
            color: #666;
            margin-top: 1mm;
        }
        /* Marca de agua para instancias test */
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
            z-index: 1000;
        }
        .watermark-no-valido::before {
            content: 'NO VÁLIDO';
            position: absolute;
            font-size: 60pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.25);
            transform: rotate(-35deg);
            white-space: nowrap;
            letter-spacing: 5mm;
            text-transform: uppercase;
            font-family: DejaVu Sans, sans-serif;
        }
        .watermark-no-valido::after {
            content: 'DOCUMENTO DE PRUEBA';
            position: absolute;
            font-size: 12pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.35);
            transform: rotate(-35deg);
            margin-top: 40mm;
            font-family: DejaVu Sans, sans-serif;
        }
    </style>
</head>
<body>
    <div class="certificado-container">
        <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
        <div class="cert-curso">
            <p><?php echo $nombre_curso; ?></p>
        </div>
        <div class="cert-tipo">
            <p class="intro"><?php echo $t('certificatum.cert_hereby_grants', ['institucion' => $nombre_institucion], 'Por la presente, ' . $nombre_institucion . ' otorga el presente'); ?></p>
            <p class="tipo-cert"><?php echo $tipo_cert_texto; ?></p>
        </div>
        <div class="cert-nombre">
            <p class="cert-nombre-texto"><?php echo $nombre_alumno; ?></p>
        </div>
        <div class="cert-descripcion">
            <p><?php echo $texto_descripcion; ?></p>
        </div>
        <div class="cert-qr">
            <img src="<?php echo $qr_url; ?>" alt="QR">
            <p class="cert-qr-text"><?php echo $t('certificatum.validate_certificate', [], 'Validar certificado'); ?></p>
        </div>
    </div>
</body>
</html>
<?php

// CERTIFICADO DE APROBACIÓN (diseño moderno, sin imagen de fondo)
} elseif ($tipo_documento == 'certificatum_approbationis') {
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 297mm 210mm landscape;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            background: linear-gradient(135deg, #fefefe 0%, #f8f9fa 50%, #f0f1f3 100%);
        }
        .certificado-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            box-sizing: border-box;
            padding: 8mm;
        }
        .borde-superior {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4mm;
            background: linear-gradient(90deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 100%);
        }
        .borde-inferior {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4mm;
            background: linear-gradient(90deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 100%);
        }
        .marco {
            border: 2px solid <?php echo $color_primario; ?>;
            border-radius: 4mm;
            margin: 5mm;
            padding: 8mm;
            height: calc(210mm - 26mm);
            box-sizing: border-box;
            text-align: center;
        }
        .header {
            margin-bottom: 8mm;
        }
        .logo {
            height: 18mm;
            margin-bottom: 3mm;
        }
        .institucion {
            font-size: 16pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 0;
        }
        .subtitulo {
            font-size: 9pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 2pt;
            margin: 2mm 0 0 0;
        }
        .linea-decorativa {
            height: 1.5mm;
            background: linear-gradient(90deg, transparent 0%, <?php echo $color_primario; ?> 20%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 80%, transparent 100%);
            margin: 6mm auto;
            width: 70%;
        }
        .titulo-certificado {
            font-size: 32pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 4mm 0;
        }
        .texto-otorga {
            font-size: 12pt;
            color: #666;
            margin: 6mm 0 2mm 0;
        }
        .nombre-persona {
            font-size: 26pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 2mm 0;
        }
        .texto-curso {
            font-size: 12pt;
            color: #666;
            margin: 6mm 0 2mm 0;
        }
        .nombre-curso {
            font-size: 20pt;
            font-weight: bold;
            color: #333;
            margin: 2mm 0;
        }
        .detalles {
            margin: 8mm 0;
        }
        .detalle-item {
            display: inline-block;
            padding: 3mm 6mm;
            margin: 0 3mm;
            background: <?php echo $color_primario; ?>15;
            border-radius: 2mm;
        }
        .detalle-label {
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
            margin: 0;
        }
        .detalle-valor {
            font-size: 14pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 1mm 0 0 0;
        }
        .footer {
            position: absolute;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            display: table;
            width: calc(100% - 30mm);
        }
        .firma-section, .qr-section {
            display: table-cell;
            vertical-align: bottom;
        }
        .firma-section {
            width: 60mm;
            text-align: center;
        }
        .qr-section {
            width: 60mm;
            text-align: center;
        }
        .firma-img {
            height: 15mm;
            margin-bottom: 2mm;
        }
        .firma-linea {
            border-top: 1.5px solid <?php echo $color_primario; ?>;
            padding-top: 2mm;
        }
        .firma-texto {
            font-size: 9pt;
            font-weight: bold;
            color: #444;
            margin: 0;
        }
        .firma-subtexto {
            font-size: 7pt;
            color: #666;
            margin: 1mm 0 0 0;
        }
        .qr-img {
            width: 22mm;
            height: 22mm;
        }
        .qr-texto {
            font-size: 7pt;
            color: #666;
            margin: 1mm 0 0 0;
        }
        .qr-codigo {
            font-size: 6pt;
            font-family: monospace;
            color: #888;
            margin: 1mm 0 0 0;
        }
        /* Marca de agua para instancias test */
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
            z-index: 1000;
        }
        .watermark-no-valido::before {
            content: 'NO VÁLIDO';
            position: absolute;
            font-size: 60pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.25);
            transform: rotate(-35deg);
            white-space: nowrap;
            letter-spacing: 5mm;
            font-family: DejaVu Sans, sans-serif;
        }
        .watermark-no-valido::after {
            content: 'DOCUMENTO DE PRUEBA';
            position: absolute;
            font-size: 12pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.35);
            transform: rotate(-35deg);
            margin-top: 40mm;
            font-family: DejaVu Sans, sans-serif;
        }
    </style>
</head>
<body>
    <div class="certificado-container">
        <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
        <div class="borde-superior"></div>
        <div class="borde-inferior"></div>
        <div class="marco">
            <div class="header">
                <?php if ($logo_url_pdf): ?>
                <img src="<?php echo $logo_url_pdf; ?>" class="logo" alt="Logo">
                <?php endif; ?>
                <p class="institucion"><?php echo $nombre_institucion; ?></p>
                <p class="subtitulo"><?php echo $t('certificatum.academic_certification', [], 'Certificación Académica'); ?></p>
            </div>
            <div class="linea-decorativa"></div>
            <p class="titulo-certificado"><?php echo $t('certificatum.cert_type_approval', [], 'Certificado de Aprobación'); ?></p>
            <p class="texto-otorga"><?php echo $t('certificatum.cert_granted_to', [], 'Se otorga el presente certificado a'); ?></p>
            <p class="nombre-persona"><?php echo $nombre_alumno; ?></p>
            <p class="texto-curso"><?php echo $t('certificatum.cert_for_completing', [], 'por haber completado y aprobado satisfactoriamente la formación'); ?></p>
            <p class="nombre-curso"><?php echo $nombre_curso; ?></p>
            <div class="detalles">
                <span class="detalle-item">
                    <p class="detalle-label"><?php echo $t('certificatum.workload', [], 'Carga Horaria'); ?></p>
                    <p class="detalle-valor"><?php echo $curso['carga_horaria']; ?> <?php echo $t('certificatum.hours', [], 'horas'); ?></p>
                </span>
                <span class="detalle-item">
                    <p class="detalle-label"><?php echo $t('certificatum.completion_date', [], 'Fecha de Finalización'); ?></p>
                    <p class="detalle-valor"><?php echo $curso['fecha_finalizacion']; ?></p>
                </span>
            </div>
        </div>
        <div class="footer">
            <div class="firma-section">
                <?php if ($firma_url): ?>
                <img src="<?php echo $firma_url; ?>" class="firma-img" alt="Firma">
                <?php endif; ?>
                <div class="firma-linea">
                    <p class="firma-texto"><?php echo $t('certificatum.authorized_signature', [], 'Firma Autorizada'); ?></p>
                    <p class="firma-subtexto"><?php echo $nombre_institucion; ?></p>
                </div>
            </div>
            <div class="qr-section">
                <img src="<?php echo $qr_url; ?>" class="qr-img" alt="QR">
                <p class="qr-texto"><?php echo $t('certificatum.verify_at', [], 'Verifica este certificado en'); ?> verumax.com</p>
                <p class="qr-codigo"><?php echo $codigo_unico_validacion; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
<?php

// CERTIFICADO DE DOCENTE/INSTRUCTOR
} elseif (in_array($tipo_documento, ['certificatum_doctoris', 'certificatum_docente']) && $es_certificado_docente) {
    $rol = $participacion['rol'] ?? 'docente';
    $genero_docente_cert = $docente['genero'] ?? 'Prefiero no especificar';

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
        $rol_texto = LanguageService::getGenderedText($genero_docente_cert, $roles_con_genero_cert[$rol], 'sufijo_or');
    } elseif (isset($roles_neutros_cert[$rol])) {
        $rol_texto = $roles_neutros_cert[$rol];
    } else {
        $rol_texto = $roles_display[$rol] ?? ucfirst($rol);
    }

    $titulo_participacion = $participacion['titulo_participacion'] ?? null;
    $cohorte_nombre = $participacion['nombre_cohorte'] ?? null;
    $carga_dictada = $participacion['carga_horaria_dictada'] ?? $participacion['carga_horaria'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: 297mm 210mm landscape;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            background: linear-gradient(135deg, #fefefe 0%, #f8f4fa 50%, #f3eef5 100%);
        }
        .certificado-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            box-sizing: border-box;
            padding: 8mm;
        }
        .borde-superior {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4mm;
            background: linear-gradient(90deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 100%);
        }
        .borde-inferior {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4mm;
            background: linear-gradient(90deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 100%);
        }
        .marco {
            border: 2px solid <?php echo $color_primario; ?>;
            border-radius: 4mm;
            margin: 5mm;
            padding: 6mm;
            height: calc(210mm - 26mm);
            box-sizing: border-box;
            text-align: center;
        }
        .header {
            margin-bottom: 5mm;
        }
        .logo {
            height: 16mm;
            margin-bottom: 2mm;
        }
        .institucion {
            font-size: 14pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 0;
        }
        .subtitulo {
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 2pt;
            margin: 1mm 0 0 0;
        }
        .linea-decorativa {
            height: 1mm;
            background: linear-gradient(90deg, transparent 0%, <?php echo $color_primario; ?> 20%, <?php echo $color_secundario; ?> 50%, <?php echo $color_primario; ?> 80%, transparent 100%);
            margin: 4mm auto;
            width: 70%;
        }
        .titulo-certificado {
            font-size: 26pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 3mm 0;
        }
        .badge-rol {
            display: inline-block;
            padding: 2mm 6mm;
            background: <?php echo $color_primario; ?>20;
            color: <?php echo $color_primario; ?>;
            border-radius: 10mm;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1pt;
            margin: 3mm 0;
        }
        .texto-certifica {
            font-size: 11pt;
            color: #666;
            margin: 4mm 0 1mm 0;
        }
        .nombre-persona {
            font-size: 24pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 1mm 0;
        }
        .titulo-persona {
            font-size: 11pt;
            font-style: italic;
            color: #666;
            margin: 1mm 0;
        }
        .texto-participo {
            font-size: 11pt;
            color: #666;
            margin: 4mm 0 1mm 0;
        }
        .nombre-curso {
            font-size: 18pt;
            font-weight: bold;
            color: #333;
            margin: 1mm 0;
        }
        .titulo-participacion {
            font-size: 12pt;
            font-style: italic;
            color: #555;
            margin: 2mm 0;
        }
        .cohorte {
            font-size: 10pt;
            color: #666;
            margin: 2mm 0;
        }
        .detalles {
            margin: 5mm 0;
        }
        .detalle-item {
            display: inline-block;
            padding: 2mm 5mm;
            margin: 0 2mm;
            background: <?php echo $color_primario; ?>12;
            border-radius: 2mm;
        }
        .detalle-label {
            font-size: 7pt;
            color: #666;
            text-transform: uppercase;
            margin: 0;
        }
        .detalle-valor {
            font-size: 12pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 1mm 0 0 0;
        }
        .footer {
            position: absolute;
            bottom: 12mm;
            left: 15mm;
            right: 15mm;
            display: table;
            width: calc(100% - 30mm);
        }
        .firma-section, .qr-section {
            display: table-cell;
            vertical-align: bottom;
        }
        .firma-section {
            width: 55mm;
            text-align: center;
        }
        .qr-section {
            width: 55mm;
            text-align: center;
        }
        .firma-img {
            height: 14mm;
            margin-bottom: 1mm;
        }
        .firma-linea {
            border-top: 1.5px solid <?php echo $color_primario; ?>;
            padding-top: 1mm;
        }
        .firma-texto {
            font-size: 8pt;
            font-weight: bold;
            color: #444;
            margin: 0;
        }
        .firma-subtexto {
            font-size: 6pt;
            color: #666;
            margin: 0;
        }
        .qr-img {
            width: 20mm;
            height: 20mm;
        }
        .qr-texto {
            font-size: 6pt;
            color: #666;
            margin: 1mm 0 0 0;
        }
        .qr-codigo {
            font-size: 5pt;
            font-family: monospace;
            color: #888;
            margin: 0;
        }
        /* Marca de agua para instancias test */
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
            z-index: 1000;
        }
        .watermark-no-valido::before {
            content: 'NO VÁLIDO';
            position: absolute;
            font-size: 60pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.25);
            transform: rotate(-35deg);
            white-space: nowrap;
            letter-spacing: 5mm;
            font-family: DejaVu Sans, sans-serif;
        }
        .watermark-no-valido::after {
            content: 'DOCUMENTO DE PRUEBA';
            position: absolute;
            font-size: 12pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.35);
            transform: rotate(-35deg);
            margin-top: 40mm;
            font-family: DejaVu Sans, sans-serif;
        }
    </style>
</head>
<body>
    <div class="certificado-container">
        <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
        <div class="borde-superior"></div>
        <div class="borde-inferior"></div>
        <div class="marco">
            <div class="header">
                <?php if ($logo_url_pdf): ?>
                <img src="<?php echo $logo_url_pdf; ?>" class="logo" alt="Logo">
                <?php endif; ?>
                <p class="institucion"><?php echo $nombre_institucion; ?></p>
                <p class="subtitulo"><?php echo $t('certificatum.teacher_certification', [], 'Certificación Docente'); ?></p>
            </div>
            <div class="linea-decorativa"></div>
            <p class="titulo-certificado"><?php echo $t('certificatum.participation_certificate', [], 'Certificado de Participación'); ?></p>
            <div class="badge-rol"><?php echo $rol_texto; ?></div>
            <p class="texto-certifica"><?php echo $t('certificatum.it_is_certified_that', [], 'Se certifica que'); ?></p>
            <p class="nombre-persona"><?php echo $nombre_alumno; ?></p>
            <?php if (!empty($docente['titulo'])): ?>
            <p class="titulo-persona"><?php echo $docente['titulo']; ?></p>
            <?php endif; ?>
            <p class="texto-participo"><?php echo $t('certificatum.participated_as_simple', ['rol' => $rol_texto], 'participó como ' . $rol_texto . ' en'); ?></p>
            <p class="nombre-curso"><?php echo $nombre_curso; ?></p>
            <?php if ($titulo_participacion): ?>
            <p class="titulo-participacion">"<?php echo $titulo_participacion; ?>"</p>
            <?php endif; ?>
            <?php if ($cohorte_nombre): ?>
            <p class="cohorte"><?php echo $t('certificatum.cohort', [], 'Cohorte'); ?>: <strong><?php echo $cohorte_nombre; ?></strong></p>
            <?php endif; ?>
            <div class="detalles">
                <?php if ($carga_dictada): ?>
                <span class="detalle-item">
                    <p class="detalle-label"><?php echo $t('certificatum.workload', [], 'Carga Horaria'); ?></p>
                    <p class="detalle-valor"><?php echo $carga_dictada; ?> <?php echo $t('certificatum.hours', [], 'horas'); ?></p>
                </span>
                <?php endif; ?>
                <?php if (!empty($curso['fecha_inicio']) && !empty($curso['fecha_finalizacion'])): ?>
                <span class="detalle-item">
                    <p class="detalle-label"><?php echo $t('certificatum.period', [], 'Período'); ?></p>
                    <p class="detalle-valor"><?php echo $curso['fecha_inicio']; ?> <?php echo $t('certificatum.to', [], 'al'); ?> <?php echo $curso['fecha_finalizacion']; ?></p>
                </span>
                <?php elseif (!empty($curso['fecha_finalizacion'])): ?>
                <span class="detalle-item">
                    <p class="detalle-label"><?php echo $t('certificatum.completion_date', [], 'Fecha'); ?></p>
                    <p class="detalle-valor"><?php echo $curso['fecha_finalizacion']; ?></p>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer">
            <div class="firma-section">
                <?php if ($firma_url): ?>
                <img src="<?php echo $firma_url; ?>" class="firma-img" alt="Firma">
                <?php endif; ?>
                <div class="firma-linea">
                    <p class="firma-texto"><?php echo $t('certificatum.authorized_signature', [], 'Firma Autorizada'); ?></p>
                    <p class="firma-subtexto"><?php echo $nombre_institucion; ?></p>
                </div>
            </div>
            <div class="qr-section">
                <img src="<?php echo $qr_url; ?>" class="qr-img" alt="QR">
                <p class="qr-texto"><?php echo $t('certificatum.verify_at', [], 'Verifica en'); ?> verumax.com</p>
                <p class="qr-codigo"><?php echo $codigo_unico_validacion; ?></p>
            </div>
        </div>
    </div>
</body>
</html>
<?php

// CONSTANCIAS (VERTICAL)
} elseif (in_array($tipo_documento, ['testimonium_regulare', 'testimonium_completionis', 'testimonium_inscriptionis', 'testimonium_doctoris'])) {
    $titulo_constancia = $t('certificatum.constancy', [], 'Constancia');
    $cuerpo_constancia = "";
    $es_constancia_docente_pdf = ($tipo_documento === 'testimonium_doctoris');

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
            $cuerpo_constancia = $t('certificatum.constancy_body_enrollment', [], 'se encuentra inscripto/a para comenzar la formación:');
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
                $rol_texto = $roles_display[$rol_base] ?? ucfirst($rol_base);
            }

            // Determinar estado de la participación
            $estado_participacion = $participacion['estado'] ?? 'Asignado';
            $es_constancia_asignacion_pdf = ($estado_participacion === 'Asignado');

            // Obtener texto con género para "asignado/asignada"
            $assigned_root = $t('certificatum.assigned_root', [], 'asignad');
            $asignado_texto = LanguageService::getGenderedText($genero_docente, $assigned_root, 'sufijo_o');

            if ($es_constancia_asignacion_pdf) {
                $titulo_constancia = $t('certificatum.assignment_certificate', [], 'Constancia de Asignación');
                $cuerpo_constancia = $t('certificatum.has_been_assigned_body', ['asignado' => $asignado_texto, 'rol' => "<strong>{$rol_texto}</strong>"], "ha sido {$asignado_texto} como <strong>{$rol_texto}</strong> en la formación:");
            } else {
                $titulo_constancia = $t('certificatum.provisional_certificate', [], 'Constancia de Participación');
                $cuerpo_constancia = $t('certificatum.is_participating_body', ['rol' => "<strong>{$rol_texto}</strong>"], "está participando como <strong>{$rol_texto}</strong> en la formación:");
            }
            break;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12pt;
            line-height: 1.6;
        }
        .constancia-container {
            padding: 10mm;
        }
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid <?php echo $color_primario; ?>;
            padding-bottom: 5mm;
            margin-bottom: 15mm;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .institucion {
            font-size: 18pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 0;
        }
        .tipo-documento {
            font-size: 11pt;
            color: #666;
            margin: 2mm 0 0 0;
        }
        .logo {
            height: 18mm;
        }
        .titulo {
            text-align: center;
            font-size: 22pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            margin: 10mm 0 15mm 0;
            text-transform: uppercase;
            letter-spacing: 2pt;
        }
        .contenido {
            text-align: justify;
            margin: 10mm 0;
        }
        .contenido p {
            margin: 5mm 0;
        }
        .nombre-curso {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            background: #f5f5f5;
            padding: 5mm;
            border-radius: 2mm;
            margin: 8mm 0;
        }
        .cierre {
            margin-top: 15mm;
        }
        .lugar-fecha {
            margin-top: 8mm;
            text-align: right;
            font-style: italic;
            color: #4a5568;
        }
        .footer {
            position: fixed;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 5mm;
        }
        .qr-img {
            width: 25mm;
            height: 25mm;
        }
        .qr-texto {
            font-size: 8pt;
            color: #666;
            margin: 2mm 0 0 0;
        }
        .codigo {
            font-size: 7pt;
            font-family: monospace;
            color: #888;
        }
        /* Marca de agua para instancias test */
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
            z-index: 1000;
        }
        .watermark-no-valido::before {
            content: 'NO VÁLIDO';
            position: absolute;
            font-size: 50pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.25);
            transform: rotate(-45deg);
            white-space: nowrap;
            letter-spacing: 3mm;
            font-family: DejaVu Sans, sans-serif;
        }
        .watermark-no-valido::after {
            content: 'DOCUMENTO DE PRUEBA';
            position: absolute;
            font-size: 10pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.35);
            transform: rotate(-45deg);
            margin-top: 35mm;
            font-family: DejaVu Sans, sans-serif;
        }
    </style>
</head>
<body>
    <div class="constancia-container" style="position: relative;">
        <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
        <div class="header">
            <div class="header-left">
                <p class="institucion"><?php echo $nombre_institucion; ?></p>
                <p class="tipo-documento"><?php echo $titulo_constancia; ?></p>
            </div>
            <div class="header-right">
                <?php if ($logo_url_pdf): ?>
                <img src="<?php echo $logo_url_pdf; ?>" class="logo" alt="Logo">
                <?php endif; ?>
            </div>
        </div>

        <p class="titulo"><?php echo $titulo_constancia; ?></p>

        <div class="contenido">
            <p><?php echo $t('certificatum.constancy_intro', [], 'Por medio de la presente, se deja constancia que'); ?> <strong><?php echo $nombre_alumno; ?></strong>, <?php echo $t('certificatum.dni_label', [], 'D.N.I. N°'); ?> <strong><?php echo $dni; ?></strong>, <?php echo $cuerpo_constancia; ?></p>

            <p class="nombre-curso"><?php echo $nombre_curso; ?></p>

            <?php if($tipo_documento == 'testimonium_inscriptionis' && !empty($curso['fecha_inicio'])): ?>
            <p><?php echo $t('certificatum.start_date_scheduled', ['fecha_inicio' => $curso['fecha_inicio']], 'La fecha de inicio estipulada es el ' . $curso['fecha_inicio'] . '.'); ?></p>
            <?php endif; ?>

            <p class="cierre"><?php echo $t('certificatum.constancy_closing', [], 'Se extiende la presente constancia a los fines que estime corresponder.'); ?></p>
            <p class="lugar-fecha"><?php echo htmlspecialchars($lugar_fecha); ?>.</p>
        </div>

        <div class="footer">
            <img src="<?php echo $qr_url; ?>" class="qr-img" alt="QR">
            <p class="qr-texto"><?php echo $t('certificatum.scan_qr_to_verify', [], 'Para verificar la validez de este documento, escanee el código QR.'); ?></p>
            <p class="codigo"><?php echo $codigo_unico_validacion; ?></p>
        </div>
    </div>
</body>
</html>
<?php

// ANALÍTICO ACADÉMICO (default)
} else {
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
        }
        .analitico-container {
            padding: 5mm;
        }
        .header {
            background: #f8f9fa;
            padding: 5mm;
            border-radius: 2mm;
            margin-bottom: 5mm;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .titulo-seccion {
            font-size: 9pt;
            font-weight: bold;
            color: <?php echo $color_primario; ?>;
            text-transform: uppercase;
            margin: 0;
        }
        .nombre-curso {
            font-size: 18pt;
            font-weight: bold;
            color: #333;
            margin: 2mm 0 0 0;
        }
        .estudiante {
            font-size: 10pt;
            color: #666;
            margin: 2mm 0 0 0;
        }
        .logo {
            height: 14mm;
        }
        .seccion {
            margin: 5mm 0;
        }
        .seccion-titulo {
            font-size: 12pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid #ddd;
        }
        .timeline {
            padding-left: 5mm;
            border-left: 2px solid #ddd;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 4mm;
            padding-left: 5mm;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -7.5mm;
            top: 1.5mm;
            width: 3mm;
            height: 3mm;
            background: <?php echo $color_primario; ?>;
            border-radius: 50%;
        }
        .evento-nombre {
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .evento-fecha {
            font-size: 9pt;
            color: #666;
            margin: 0;
        }
        .evento-detalle {
            font-size: 9pt;
            color: <?php echo $color_primario; ?>;
            font-weight: bold;
            margin: 1mm 0 0 0;
        }
        .resumen-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 2mm;
            padding: 4mm;
            margin-bottom: 4mm;
        }
        .resumen-titulo {
            font-size: 11pt;
            font-weight: bold;
            color: #333;
            margin: 0 0 3mm 0;
        }
        .resumen-item {
            display: table;
            width: 100%;
            margin: 1mm 0;
        }
        .resumen-label {
            display: table-cell;
            color: #666;
        }
        .resumen-valor {
            display: table-cell;
            text-align: right;
            font-weight: bold;
            color: #333;
        }
        .competencias {
            margin-top: 3mm;
        }
        .competencia-tag {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 1mm 3mm;
            border-radius: 2mm;
            font-size: 8pt;
            font-weight: bold;
            margin: 1mm;
        }
        .footer {
            margin-top: 8mm;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 4mm;
        }
        .qr-img {
            width: 22mm;
            height: 22mm;
        }
        .footer-texto {
            font-size: 8pt;
            color: #666;
            margin: 2mm 0 0 0;
        }
        .codigo {
            font-size: 7pt;
            font-family: monospace;
            color: #888;
        }
        .columnas {
            display: table;
            width: 100%;
        }
        .col-izq {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 5mm;
        }
        .col-der {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        /* Marca de agua para instancias test */
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
            z-index: 1000;
        }
        .watermark-no-valido::before {
            content: 'NO VÁLIDO';
            position: absolute;
            font-size: 50pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.25);
            transform: rotate(-45deg);
            white-space: nowrap;
            letter-spacing: 3mm;
            font-family: DejaVu Sans, sans-serif;
        }
        .watermark-no-valido::after {
            content: 'DOCUMENTO DE PRUEBA';
            position: absolute;
            font-size: 10pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.35);
            transform: rotate(-45deg);
            margin-top: 35mm;
            font-family: DejaVu Sans, sans-serif;
        }
    </style>
</head>
<body>
    <div class="analitico-container" style="position: relative;">
        <?php if ($es_instancia_test): ?><div class="watermark-no-valido"></div><?php endif; ?>
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <p class="titulo-seccion"><?php echo $t('certificatum.academic_trajectory', [], 'Trayectoria Académica'); ?></p>
                    <p class="nombre-curso"><?php echo $nombre_curso; ?></p>
                    <p class="estudiante"><?php echo $t('certificatum.student', [], 'Estudiante'); ?>: <?php echo $nombre_alumno; ?> (<?php echo $t('certificatum.dni_short', [], 'DNI'); ?>: <?php echo $dni; ?>)</p>
                </div>
                <div class="header-right">
                    <?php if ($logo_url_pdf): ?>
                    <img src="<?php echo $logo_url_pdf; ?>" class="logo" alt="Logo">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="columnas">
            <div class="col-izq">
                <div class="seccion">
                    <p class="seccion-titulo"><?php echo $t('certificatum.course_timeline', [], 'Línea de Tiempo del Curso'); ?></p>
                    <div class="timeline">
                        <?php if (isset($curso['trayectoria']) && is_array($curso['trayectoria'])): ?>
                            <?php foreach($curso['trayectoria'] as $item): ?>
                            <div class="timeline-item">
                                <p class="evento-nombre"><?php echo htmlspecialchars($item['evento']); ?></p>
                                <p class="evento-fecha"><?php echo htmlspecialchars($item['fecha'] ?? ''); ?></p>
                                <?php if(!empty($item['detalle'])): ?>
                                <p class="evento-detalle"><?php echo htmlspecialchars($item['detalle']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="timeline-item">
                                <p class="evento-nombre"><?php echo $t('certificatum.no_timeline', [], 'Sin información de trayectoria'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-der">
                <div class="resumen-box">
                    <p class="resumen-titulo"><?php echo $t('certificatum.summary', [], 'Resumen'); ?></p>
                    <div class="resumen-item">
                        <span class="resumen-label"><?php echo $t('certificatum.final_grade', [], 'Nota Final'); ?></span>
                        <span class="resumen-valor"><?php echo htmlspecialchars($curso['nota_final'] ?? '-'); ?></span>
                    </div>
                    <div class="resumen-item">
                        <span class="resumen-label"><?php echo $t('certificatum.attendance', [], 'Asistencia'); ?></span>
                        <span class="resumen-valor"><?php echo htmlspecialchars($curso['asistencia'] ?? '-'); ?></span>
                    </div>
                    <div class="resumen-item">
                        <span class="resumen-label"><?php echo $t('certificatum.workload', [], 'Carga Horaria'); ?></span>
                        <span class="resumen-valor"><?php echo htmlspecialchars($curso['carga_horaria'] ?? '-'); ?> <?php echo $t('certificatum.hours_short', [], 'hs.'); ?></span>
                    </div>
                    <div class="resumen-item">
                        <span class="resumen-label"><?php echo $t('certificatum.completion', [], 'Finalización'); ?></span>
                        <span class="resumen-valor"><?php echo htmlspecialchars($curso['fecha_finalizacion'] ?? '-'); ?></span>
                    </div>
                </div>

                <?php if (isset($curso['competencias']) && is_array($curso['competencias']) && !empty($curso['competencias'])): ?>
                <div class="resumen-box">
                    <p class="resumen-titulo"><?php echo $t('certificatum.competencies', [], 'Competencias'); ?></p>
                    <div class="competencias">
                        <?php foreach ($curso['competencias'] as $c): ?>
                        <span class="competencia-tag"><?php echo htmlspecialchars($c); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            <img src="<?php echo $qr_url; ?>" class="qr-img" alt="QR">
            <p class="footer-texto"><?php echo $t('certificatum.scan_qr_to_verify', [], 'Para verificar la validez de este documento, escanee el código QR.'); ?></p>
            <p class="codigo"><?php echo $codigo_unico_validacion; ?></p>
        </div>
    </div>
</body>
</html>
<?php } ?>
