<?php
$file = __DIR__ . '/creare.php';
$content = file_get_contents($file);

// Cambiar white-space: nowrap a normal para permitir texto multilínea
$old = "white-space: nowrap;\n                            overflow: hidden;";
$new = "white-space: normal;\n                            overflow: visible;\n                            word-wrap: break-word;";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "OK: Cambiado white-space a normal para permitir texto multilínea\n";
} else {
    echo "No encontrado. Verificando...\n";
    if (strpos($content, 'white-space: nowrap') !== false) {
        echo "Hay 'white-space: nowrap' pero con formato diferente\n";
    }
}
