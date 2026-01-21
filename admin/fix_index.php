<?php
// Script temporal para agregar interceptor GET AJAX

$file = __DIR__ . '/index.php';
$content = file_get_contents($file);

$search = '// ============================================================================
// INTERCEPTAR PETICIONES AJAX ANTES DE ENVIAR HTML
// ============================================================================
if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\' && isset($_POST[\'accion\']))';

$replace = '// ============================================================================
// INTERCEPTAR PETICIONES AJAX ANTES DE ENVIAR HTML
// ============================================================================

// GET AJAX (obtener_firma_base64, etc)
if ($_SERVER[\'REQUEST_METHOD\'] === \'GET\' && isset($_GET[\'accion\'])) {
    $acciones_ajax_get = [\'obtener_firma_base64\', \'obtener_template_config\'];
    if (in_array($_GET[\'accion\'], $acciones_ajax_get)) {
        $modulo_file = __DIR__ . "/modulos/{$modulo_activo}.php";
        if (file_exists($modulo_file)) { include $modulo_file; }
        exit;
    }
}

// POST AJAX
if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\' && isset($_POST[\'accion\']))';

if (strpos($content, $search) !== false) {
    $new_content = str_replace($search, $replace, $content);
    file_put_contents($file, $new_content);
    echo "OK - Archivo modificado\n";
} else {
    echo "ERROR - Patron no encontrado\n";
}
