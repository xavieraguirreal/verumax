<?php
/**
 * Proxy a Certificatum - Validare (Validación de Certificados)
 * SAJuR - Sociedad Argentina de Justicia Restaurativa
 *
 * Este archivo actúa como puente transparente al motor central de Certificatum
 * manteniendo la URL bajo sajur.verumax.com para branding consistente.
 *
 * URLs de validación:
 * - Institucional: https://sajur.verumax.com/validare.php?codigo=VALID-XXXX
 * - Global: https://www.verumax.com/certificatum/validare.php?codigo=VALID-XXXX
 */

// Auto-configuración de institución
$_POST['institutio'] = $_GET['institutio'] = 'sajur';

// Definir rutas base para que el motor central use rutas absolutas en HTML
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir($certificatum_path);

// Incluir el motor central de validación
require_once $certificatum_path . '/validare.php';
?>
