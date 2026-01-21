<?php
require_once __DIR__ . '/../vendor/autoload.php';

$fonts_dir = __DIR__ . '/../vendor/tecnickcom/tcpdf/fonts/';
$ttf_file = __DIR__ . '/../assets/fonts/AnticDidone-Regular.ttf';

if (!file_exists($ttf_file)) {
    echo "ERROR: No se encuentra $ttf_file\n";
    exit(1);
}

echo "Convirtiendo Antic Didone para TCPDF...\n";
$font_name = TCPDF_FONTS::addTTFfont($ttf_file, 'TrueTypeUnicode', '', 96, $fonts_dir);

if ($font_name) {
    echo "OK: Fuente convertida como '$font_name'\n";
} else {
    echo "ERROR al convertir\n";
}
