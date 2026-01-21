<?php
/**
 * Proxy a Certificatum - Creare PDF (Generación de Documentos PDF)
 * Certificadores
 *
 * Este archivo actúa como puente transparente al motor central de Certificatum.
 * Usa TCPDF para certificados con imagen de fondo, mPDF para otros documentos.
 */

// Auto-configuración de institución
$_POST['institutio'] = $_GET['institutio'] = 'certificadores';

// Definir rutas base
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir($certificatum_path);

// Determinar qué generador usar según el tipo de documento
$tipo_documento = $_GET['genus'] ?? 'analyticum';

// TCPDF solo para certificados con imagen de fondo
$tipos_tcpdf = ['certificatum_approbationis', 'certificatum_completionis', 'certificatum_doctoris', 'certificatum_docente'];

if (in_array($tipo_documento, $tipos_tcpdf)) {
    // Usar TCPDF para certificados elegantes con imagen de fondo
    require_once $certificatum_path . '/creare_pdf_tcpdf.php';
} else {
    // Usar mPDF para analíticos, constancias y otros documentos
    require_once $certificatum_path . '/creare_pdf.php';
}
