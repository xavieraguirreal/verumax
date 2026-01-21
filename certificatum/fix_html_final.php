<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Agregar font-size al wrapper para mantener tamaño correcto
$old = "\$html = '<span style=\"color:' . \$color_hex . ';\">' . \$texto_html . '</span>';";

// Incluir font-size para que TCPDF respete el tamaño del texto
$new = "\$html = '<span style=\"font-size:' . \$size . 'pt;color:' . \$color_hex . ';\">' . \$texto_html . '</span>';";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "OK: Agregado font-size al wrapper HTML\n";
} else {
    echo "No encontrado. Contenido alrededor de línea 498:\n";
    $lines = explode("\n", $content);
    for ($i = 495; $i < 505; $i++) {
        echo "  [$i] " . ($lines[$i] ?? '') . "\n";
    }
}
