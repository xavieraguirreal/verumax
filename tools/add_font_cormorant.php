<?php
/**
 * Convierte Cormorant Garamond (Regular, Bold, Italic, BoldItalic) para TCPDF
 */
require_once __DIR__ . '/../vendor/autoload.php';

$fonts_dir = __DIR__ . '/../vendor/tecnickcom/tcpdf/fonts/';
$base_path = __DIR__ . '/../assets/fonts/Antic_Didone,Cormorant_Garamond,Playfair_Display,Roboto/Cormorant_Garamond/static/';

$fonts_to_convert = [
    'CormorantGaramond-Regular.ttf' => 'Regular',
    'CormorantGaramond-Bold.ttf' => 'Bold',
    'CormorantGaramond-Italic.ttf' => 'Italic',
    'CormorantGaramond-BoldItalic.ttf' => 'BoldItalic',
];

echo "=== Convirtiendo Cormorant Garamond para TCPDF ===\n\n";

foreach ($fonts_to_convert as $file => $style) {
    $ttf_file = $base_path . $file;

    if (!file_exists($ttf_file)) {
        echo "ERROR: No se encuentra $file\n";
        continue;
    }

    echo "Convirtiendo $style ($file)...\n";
    $font_name = TCPDF_FONTS::addTTFfont($ttf_file, 'TrueTypeUnicode', '', 96, $fonts_dir);

    if ($font_name) {
        echo "  OK: Convertida como '$font_name'\n";
    } else {
        echo "  ERROR al convertir $file\n";
    }
}

echo "\n=== Conversión completada ===\n";
echo "\nVerificando archivos generados:\n";

$expected_files = [
    'cormorantgaramond.php',
    'cormorantgaramondb.php',      // Bold
    'cormorantgaramondi.php',      // Italic
    'cormorantgaramondbi.php',     // BoldItalic
];

foreach ($expected_files as $expected) {
    $full_path = $fonts_dir . $expected;
    if (file_exists($full_path)) {
        echo "  [OK] $expected\n";
    } else {
        // Buscar variantes del nombre
        $pattern = str_replace('.php', '*.php', $expected);
        $found = glob($fonts_dir . 'cormorant*.php');
        if (!empty($found)) {
            echo "  [?] Buscando '$expected' - encontrados: " . implode(', ', array_map('basename', $found)) . "\n";
        } else {
            echo "  [MISSING] $expected\n";
        }
    }
}
