<?php
/**
 * Desarrollo - Router Principal
 * Redirige a la landing de servicios de desarrollo
 */

// Preservar parámetros de URL
$params = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';

// Redirigir a landing
header('Location: landing.php' . $params);
exit;
