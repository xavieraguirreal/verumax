<?php
/**
 * Proxy a Certificatum - Creare (Generación de Documentos)
 * {{NOMBRE_INSTITUCION}}
 */

$_POST['institutio'] = $_GET['institutio'] = '{{SLUG}}';

define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

$certificatum_path = dirname(__DIR__) . '/certificatum';
chdir($certificatum_path);
require_once $certificatum_path . '/creare.php';
?>
