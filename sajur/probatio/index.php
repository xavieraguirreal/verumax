<?php
/**
 * SAJuR - Proxy para Probatio
 *
 * Redirige al motor central de evaluaciones.
 * URL: sajur.verumax.com/probatio/EVAL-SAJUR-CORR-2025
 */

// Configuración de esta institución
define('PROXY_MODE', true);
define('INSTITUCION_SLUG', 'sajur');

// Extraer código de evaluación de la URL
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/probatio/';

$path_after_base = '';
if (($pos = strpos($request_uri, $base_path)) !== false) {
    $path_after_base = substr($request_uri, $pos + strlen($base_path));
    if (($qpos = strpos($path_after_base, '?')) !== false) {
        $path_after_base = substr($path_after_base, 0, $qpos);
    }
}

// Parsear: CODIGO/archivo.php o directamente archivo.php
$path_parts = explode('/', trim($path_after_base, '/'));
$primer_parte = $path_parts[0] ?? '';

// Lista de archivos PHP conocidos
$archivos_php = ['accedere.php', 'respondere.php', 'verificare.php', 'salvare.php', 'clausura.php', 'resultatum.php', 'index.php', 'test.php'];

// Si la primera parte es un archivo PHP, no es un código de evaluación
if (in_array($primer_parte, $archivos_php)) {
    $codigo_evaluacion = '';
    $archivo_solicitado = $primer_parte;
} else {
    $codigo_evaluacion = $primer_parte;
    $archivo_solicitado = $path_parts[1] ?? '';
}

// Si no hay código en URL, verificar query param
if (empty($codigo_evaluacion)) {
    $codigo_evaluacion = $_GET['codigo'] ?? $_GET['evaluatio'] ?? '';
}

// Si hay sessio, no necesitamos el código (la sesión ya tiene la evaluación)
$tiene_sessio = isset($_GET['sessio']) && !empty($_GET['sessio']);

// Si no hay código ni sessio, mostrar error
if (empty($codigo_evaluacion) && !$tiene_sessio) {
    http_response_code(400);
    die('Error: Código de evaluación no especificado.');
}

// Mapear archivos permitidos
$archivos_permitidos = [
    '' => 'accedere.php',
    'index.php' => 'accedere.php',
    'accedere.php' => 'accedere.php',
    'respondere.php' => 'respondere.php',
    'verificare.php' => 'verificare.php',
    'salvare.php' => 'salvare.php',
    'clausura.php' => 'clausura.php',
    'resultatum.php' => 'resultatum.php',
    'test.php' => 'test.php',
];

$archivo = $archivos_permitidos[$archivo_solicitado] ?? 'accedere.php';
$probatio_path = __DIR__ . '/../../probatio/' . $archivo;

// Verificar que existe
if (!file_exists($probatio_path)) {
    http_response_code(500);
    die('Error: Sistema de evaluaciones no disponible.');
}

// Pasar parámetros a Probatio
$_GET['institutio'] = INSTITUCION_SLUG;
if (!empty($codigo_evaluacion)) {
    $_GET['evaluatio'] = $codigo_evaluacion;
}

if (!isset($_GET['lang'])) {
    $_GET['lang'] = 'es_AR';
}

// Cambiar directorio de trabajo para rutas relativas
chdir(__DIR__ . '/../../probatio');

// Incluir el archivo de Probatio
require_once $probatio_path;
