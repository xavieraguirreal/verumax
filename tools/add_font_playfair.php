<?php
/**
 * Script para agregar Playfair Display a TCPDF
 *
 * Pasos:
 * 1. Descargar TTF de Google Fonts
 * 2. Convertir a formato TCPDF
 * 3. Copiar archivos al directorio de fuentes
 */

require_once __DIR__ . '/../vendor/autoload.php';

$fonts_dir = __DIR__ . '/../vendor/tecnickcom/tcpdf/fonts/';
$assets_fonts = __DIR__ . '/../assets/fonts/';

// Verificar si ya existe
if (file_exists($fonts_dir . 'playfairdisplay.php')) {
    echo "La fuente Playfair Display ya existe en TCPDF\n";
    exit;
}

// Verificar si tenemos el TTF
$ttf_file = $assets_fonts . 'PlayfairDisplay-Regular.ttf';
$ttf_italic = $assets_fonts . 'PlayfairDisplay-Italic.ttf';
$ttf_bold = $assets_fonts . 'PlayfairDisplay-Bold.ttf';
$ttf_bolditalic = $assets_fonts . 'PlayfairDisplay-BoldItalic.ttf';

if (!file_exists($ttf_file)) {
    echo "INSTRUCCIONES:\n";
    echo "==============\n\n";
    echo "1. Descargar Playfair Display de Google Fonts:\n";
    echo "   https://fonts.google.com/specimen/Playfair+Display\n\n";
    echo "2. Extraer los archivos TTF y copiarlos a:\n";
    echo "   $assets_fonts\n\n";
    echo "   Archivos necesarios:\n";
    echo "   - PlayfairDisplay-Regular.ttf\n";
    echo "   - PlayfairDisplay-Italic.ttf (opcional)\n";
    echo "   - PlayfairDisplay-Bold.ttf (opcional)\n";
    echo "   - PlayfairDisplay-BoldItalic.ttf (opcional)\n\n";
    echo "3. Ejecutar este script nuevamente\n";
    exit(1);
}

echo "Convirtiendo Playfair Display para TCPDF...\n\n";

// Convertir fuente Regular
echo "1. Convirtiendo Regular...\n";
$font_name = TCPDF_FONTS::addTTFfont($ttf_file, 'TrueTypeUnicode', '', 96, $fonts_dir);
if ($font_name) {
    echo "   OK: $font_name\n";
} else {
    echo "   ERROR al convertir Regular\n";
}

// Convertir Italic si existe
if (file_exists($ttf_italic)) {
    echo "2. Convirtiendo Italic...\n";
    $font_name_i = TCPDF_FONTS::addTTFfont($ttf_italic, 'TrueTypeUnicode', '', 96, $fonts_dir);
    if ($font_name_i) {
        echo "   OK: $font_name_i\n";
    }
}

// Convertir Bold si existe
if (file_exists($ttf_bold)) {
    echo "3. Convirtiendo Bold...\n";
    $font_name_b = TCPDF_FONTS::addTTFfont($ttf_bold, 'TrueTypeUnicode', '', 96, $fonts_dir);
    if ($font_name_b) {
        echo "   OK: $font_name_b\n";
    }
}

// Convertir BoldItalic si existe
if (file_exists($ttf_bolditalic)) {
    echo "4. Convirtiendo BoldItalic...\n";
    $font_name_bi = TCPDF_FONTS::addTTFfont($ttf_bolditalic, 'TrueTypeUnicode', '', 96, $fonts_dir);
    if ($font_name_bi) {
        echo "   OK: $font_name_bi\n";
    }
}

echo "\n";
echo "===========================================\n";
echo "Fuente agregada exitosamente!\n";
echo "===========================================\n\n";
echo "Ahora actualiza el mapeo en creare_pdf_tcpdf.php:\n";
echo "  'Playfair Display' => 'playfairdisplay',\n";
