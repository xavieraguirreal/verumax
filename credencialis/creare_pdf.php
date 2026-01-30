<?php
/**
 * CREDENCIALIS - Generación de PDF de Credenciales
 * Sistema VERUMax
 * Versión: 2.0
 *
 * Genera PDF de credenciales usando mPDF con el template HTML.
 */

// Cargar configuración
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../certificatum/autodetect.php';

use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\MemberService;
use VERUMax\Services\QRCodeService;
use VERUMax\Services\CertificateService;

// Cargar mPDF
require_once __DIR__ . '/../vendor/autoload.php';

try {
    // Obtener parámetros
    $institucion = $_GET['institutio'] ?? null;
    $dni = $_GET['documentum'] ?? null;
    $lang = $_GET['lang'] ?? null;

    if (!$institucion || !$dni) {
        throw new Exception('Parámetros faltantes: institutio y documentum son requeridos');
    }

    // Cargar configuración de institución
    $instance_config = InstitutionService::getConfig($institucion);
    if (!$instance_config) {
        throw new Exception('Institución no encontrada');
    }

    // Inicializar idioma
    LanguageService::init($institucion, $lang);
    $t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

    // Obtener datos del miembro
    $miembro = MemberService::getCredentialData($institucion, $dni);
    if (!$miembro || empty($miembro['tiene_credencial'])) {
        throw new Exception('Miembro no encontrado o sin credencial asignada');
    }

    // Generar código de validación y QR
    $codigo_validacion = CertificateService::getValidationCode($institucion, $dni, '', 'credentialis');
    $url_validacion = obtenerURLBaseInstitucion() . "/credencialis/validare.php?codigo=" . $codigo_validacion;
    $qr_url = QRCodeService::generate($url_validacion, 150);

    // Verificar si es instancia de prueba
    $es_instancia_test = ($instance_config['en_produccion'] ?? 1) == 0;

    // Capturar el HTML del template
    ob_start();
    include __DIR__ . '/../certificatum/templates/credencial.php';
    $html_credencial = ob_get_clean();

    // Configurar mPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [130, 82], // Tamaño tipo tarjeta en mm (proporcional a 500x315px)
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'default_font' => 'dejavusans',
    ]);

    // Estilos para el PDF
    $css = "
        <style>
            body {
                font-family: 'DejaVu Sans', Arial, sans-serif;
                margin: 0;
                padding: 0;
            }
            .credencial-template {
                width: 130mm;
                height: 82mm;
                position: relative;
                overflow: hidden;
            }
            .credencial-template-bg {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
            .credencial-template-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
            .cred-tpl-nombre {
                position: absolute;
                top: 39mm;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 14pt;
                font-weight: bold;
                color: #333;
            }
            .cred-tpl-dni {
                position: absolute;
                top: 46mm;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 12pt;
                font-weight: bold;
                color: #333;
            }
            .cred-tpl-asociado {
                position: absolute;
                top: 54mm;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 14pt;
                font-weight: bold;
                color: {$instance_config['color_primario']};
            }
            .cred-tpl-servicio {
                position: absolute;
                top: 61mm;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 14pt;
                font-weight: bold;
                color: {$instance_config['color_primario']};
            }
            .cred-tpl-ingreso {
                position: absolute;
                top: 68mm;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 14pt;
                font-weight: bold;
                color: {$instance_config['color_primario']};
            }
            .cred-tpl-qr {
                position: absolute;
                bottom: 12mm;
                right: 3mm;
            }
            .cred-tpl-qr img {
                width: 20mm;
                height: 20mm;
            }
        </style>
    ";

    // Construir HTML para PDF
    $credencial_config = json_decode($instance_config['credencial_config'] ?? '{}', true);
    $template_url = $credencial_config['template_url'] ?? null;

    $nombre_completo = $miembro['nombre_completo'] ?? ($miembro['nombre'] . ' ' . $miembro['apellido']);
    $dni_valor = $miembro['identificador_principal'] ?? $miembro['dni'] ?? '';
    $dni_formateado = number_format((int)preg_replace('/[^0-9]/', '', $dni_valor), 0, '', '.');
    $numero_asociado = $miembro['numero_asociado'] ?? '';
    $tipo_asociado = $miembro['tipo_asociado'] ?? '';
    $categoria_servicio = $miembro['categoria_servicio'] ?? '';
    $fecha_ingreso = $miembro['fecha_ingreso'] ?? '';
    $fecha_ingreso_fmt = $fecha_ingreso ? date('d/m/Y', strtotime($fecha_ingreso)) : '';

    $genero = $miembro['genero'] ?? '';
    $prefijo = ($genero === 'Femenino') ? 'ASOCIADA' : 'ASOCIADO';

    $html_pdf = $css . '
    <div class="credencial-template">
        ' . ($template_url ? '<img src="' . $template_url . '" class="credencial-template-bg">' : '') . '
        <div class="credencial-template-overlay">
            <div class="cred-tpl-nombre">' . htmlspecialchars(strtoupper($nombre_completo)) . '</div>
            <div class="cred-tpl-dni">DNI ' . htmlspecialchars($dni_formateado) . '</div>
            ' . ($numero_asociado ? '<div class="cred-tpl-asociado">' . $prefijo . ' ' . htmlspecialchars($numero_asociado) . ($tipo_asociado ? ' ' . htmlspecialchars($tipo_asociado) : '') . '</div>' : '') . '
            ' . ($categoria_servicio ? '<div class="cred-tpl-servicio">' . htmlspecialchars($categoria_servicio) . '</div>' : '') . '
            ' . ($fecha_ingreso_fmt ? '<div class="cred-tpl-ingreso">INGRESO ' . $fecha_ingreso_fmt . '</div>' : '') . '
            <div class="cred-tpl-qr"><img src="' . $qr_url . '"></div>
        </div>
    </div>';

    $mpdf->WriteHTML($html_pdf);

    // Nombre del archivo
    $filename = 'credencial_' . $institucion . '_' . $dni . '.pdf';

    // Enviar PDF al navegador
    $mpdf->Output($filename, 'I');

} catch (Throwable $e) {
    // Mostrar error
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
    echo '<h1>Error generando PDF</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    if (isset($_GET['debug'])) {
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
    echo '</body></html>';
}
