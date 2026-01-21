<?php
/**
 * Verificación de Certificados - VERUMax
 * Proxy al motor central de Certificatum
 *
 * URL: verumax.com/verificatio.php
 */

// Cambiar al directorio del motor central para que las rutas relativas funcionen
$certificatum_path = __DIR__ . '/certificatum';
chdir($certificatum_path);

// Incluir el motor central de verificación
require_once $certificatum_path . '/verificatio.php';
