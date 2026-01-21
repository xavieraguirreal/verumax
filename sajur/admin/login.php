<?php
/**
 * Proxy Login para Institución
 */

$host = $_SERVER['HTTP_HOST'] ?? '';
$institucion = null;

if (preg_match('/^([a-z0-9-]+)\.verumax\.(local|com)$/i', $host, $matches)) {
    $institucion = $matches[1];
}

if (!$institucion) {
    $path = dirname($_SERVER['SCRIPT_NAME']);
    if (preg_match('/\/([a-z0-9-]+)\/admin/i', $path, $matches)) {
        $institucion = $matches[1];
    }
}

if ($institucion) {
    $_GET['inst'] = $institucion;
    define('ADMIN_INSTITUCION', $institucion);
}

require_once __DIR__ . '/../../admin/login.php';
