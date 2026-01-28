<?php
/**
 * CREDENCIALIS - Generación de PDF de Credenciales
 * Sistema VERUMax
 * Versión: 1.0
 *
 * Reutiliza el motor TCPDF de certificatum para generar PDFs de credenciales.
 */

// Auto-configuración de institución
$_GET['genus'] = 'credentialis';

// Usar path absoluto al motor de certificatum
$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas funcionen
chdir($certificatum_path);

// Usar TCPDF para credenciales (como los certificados con imagen)
require_once $certificatum_path . '/creare_pdf_tcpdf.php';
