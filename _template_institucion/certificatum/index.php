<?php
/**
 * Redirect a Cursus
 * {{NOMBRE_INSTITUCION}}
 */

$params = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
header('Location: ../cursus.php' . $params);
exit;
?>
