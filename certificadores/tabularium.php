<?php
/**
 * Proxy a Certificatum - Tabularium (Trayectoria Académica)
 * Certificadores
 *
 * Este archivo actúa como puente transparente al motor central de Certificatum
 * manteniendo la URL bajo certificadores.verumax.com para branding consistente.
 */

// Auto-configuración de institución
$_POST['institutio'] = $_GET['institutio'] = 'certificadores';

// Definir rutas base para que el motor central use rutas absolutas en HTML
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir($certificatum_path);

// Incluir el motor central de Certificatum
require_once $certificatum_path . '/tabularium.php';
