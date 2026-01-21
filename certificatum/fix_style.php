<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Agregar $font_style al wrapper HTML
$old = "\$html = '<span style=\"font-family:' . \$tcpdf_font . ';font-size:' . \$size . 'pt;color:' . \$color_hex . ';\">' . \$texto_html . '</span>';";

$new = "\$html = '<span style=\"font-family:' . \$tcpdf_font . ';font-size:' . \$size . 'pt;color:' . \$color_hex . ';' . \$font_style . '\">' . \$texto_html . '</span>';";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "OK: Agregado font_style (italic/bold base) al wrapper HTML\n";
} else {
    echo "No encontrado. Verificando...\n";
    // Mostrar línea 498
    $lines = explode("\n", $content);
    echo "Linea 497: " . trim($lines[497] ?? '') . "\n";
    echo "Linea 498: " . trim($lines[498] ?? '') . "\n";
}
