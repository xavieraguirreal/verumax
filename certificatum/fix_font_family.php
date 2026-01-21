<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Agregar font-family al wrapper HTML para que TCPDF use la fuente correcta con bold
$old = "\$html = '<span style=\"font-size:' . \$size . 'pt;color:' . \$color_hex . ';\">' . \$texto_html . '</span>';";
$new = "\$html = '<span style=\"font-family:' . \$tcpdf_font . ';font-size:' . \$size . 'pt;color:' . \$color_hex . ';\">' . \$texto_html . '</span>';";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "OK: Agregado font-family al wrapper HTML\n";
} else {
    echo "No encontrado exactamente. Verificando contenido actual...\n";
    if (strpos($content, 'font-family:') !== false && strpos($content, '$texto_html') !== false) {
        echo "Ya tiene font-family\n";
    }
}
