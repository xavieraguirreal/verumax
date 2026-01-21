<?php
/**
 * Proxy Admin para Institución
 * Carga el panel de administración central con contexto de la institución
 */

// Detectar la institución desde el subdominio o la carpeta
$host = $_SERVER['HTTP_HOST'] ?? '';
$institucion = null;

// Intentar detectar desde subdominio (sajur.verumax.local)
if (preg_match('/^([a-z0-9-]+)\.verumax\.(local|com)$/i', $host, $matches)) {
    $institucion = $matches[1];
}

// Si no se detectó, intentar desde la ruta
if (!$institucion) {
    $path = dirname($_SERVER['SCRIPT_NAME']);
    if (preg_match('/\/([a-z0-9-]+)\/admin/i', $path, $matches)) {
        $institucion = $matches[1];
    }
}

// Establecer la institución para el admin (la sesión se inicia en el archivo central)
if ($institucion) {
    $_GET['inst'] = $institucion;
    // Definir constante para que el admin central sepa la institución
    define('ADMIN_INSTITUCION', $institucion);
}

// Incluir el admin central
require_once __DIR__ . '/../../admin/index.php';
