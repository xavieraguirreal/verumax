<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Cambiar el wrapper HTML para que no interfiera con los estilos inline
// El problema es que el div con font-family puede anular los estilos del span
$old = "\$html = '<div style=\"font-family:' . \$tcpdf_font . ';font-size:' . \$size . 'pt;color:' . \$color_hex . ';text-align:' . strtolower(\$element['align'] ?? 'left') . ';' . \$font_style . '\">' . \$texto_html . '</div>';";

// Simplificar: usar solo color y text-align, dejar que la fuente se maneje por SetFont
// y que los spans con font-weight:bold funcionen correctamente
$new = "\$html = '<span style=\"color:' . \$color_hex . ';\">' . \$texto_html . '</span>';";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "OK: Wrapper HTML simplificado\n";
} else {
    echo "No encontrado el bloque exacto. Buscando...\n";

    // Buscar la línea que tiene el $html =
    if (preg_match('/\$html = \'<div style="font-family:\' \. \$tcpdf_font/', $content)) {
        echo "La línea existe pero con formato diferente.\n";
    } else {
        echo "No se encontró la línea del wrapper HTML\n";
    }
}
