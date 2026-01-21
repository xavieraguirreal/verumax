<?php
// Fix 1: Codigo de validacion - usar nombre correcto de variable
$file = __DIR__ . '/creare.php';
$content = file_get_contents($file);

$search1 = "'{{codigo_validacion}}' => \$codigo_validacion ?? ''";
$replace1 = "'{{codigo_validacion}}' => \$codigo_unico_validacion ?? ''";

if (strpos($content, $search1) !== false) {
    $content = str_replace($search1, $replace1, $content);
    echo "1. Variable codigo_validacion corregida\n";
} else {
    echo "1. Patron no encontrado (puede estar ya corregido)\n";
}

file_put_contents($file, $content);
echo "\nArchivo creare.php actualizado\n";
