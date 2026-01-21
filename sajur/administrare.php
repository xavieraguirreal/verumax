<?php
/**
 * Proxy a Certificatum - Administrare (Panel de Administración)
 * SAJuR - Sociedad Argentina de Justicia Restaurativa
 *
 * Este archivo actúa como puente transparente al panel de administración central
 * manteniendo la URL bajo sajur.verumax.com para branding consistente.
 */

// Auto-configuración de institución
$_POST['institutio'] = $_GET['institutio'] = 'sajur';

// Definir rutas base para que el motor central use rutas absolutas en HTML
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');  // Relativo a la URL actual del navegador
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir($certificatum_path);

// Incluir el panel de administración central
require_once $certificatum_path . '/administrare.php';
?>
