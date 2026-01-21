<?php
$file = 'E:/appVerumax/certificatum/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// 1. Agregar función helper después de cargar TCPDF
$search1 = "// Cargar TCPDF
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

// Parámetros de entrada";

$replace1 = "// Cargar TCPDF
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

/**
 * Convierte marcadores de formato a HTML
 * **texto** -> <b>texto</b>
 * *texto* -> <i>texto</i>
 */
function applyTextFormatting(\$text) {
    if (empty(\$text)) return \$text;

    // Negrita: **texto** -> <b>texto</b>
    \$result = preg_replace('/\\*\\*(.+?)\\*\\*/s', '<b>\$1</b>', \$text);

    // Italica: *texto* -> <i>texto</i> (solo asteriscos simples, no dobles)
    \$result = preg_replace('/(?<!\\*)\\*([^*]+)\\*(?!\\*)/s', '<i>\$1</i>', \$result);

    return \$result;
}

/**
 * Verifica si el texto tiene marcadores de formato
 */
function hasFormatMarkers(\$text) {
    return preg_match('/\\*\\*.+?\\*\\*|(?<!\\*)\\*[^*]+\\*(?!\\*)/', \$text);
}

// Parámetros de entrada";

if (strpos($content, $search1) !== false) {
    $content = str_replace($search1, $replace1, $content);
    echo "Funciones helper agregadas\n";
} else {
    echo "No se encontro patron para funciones helper\n";
}

// 2. Modificar la sección que renderiza texto para usar writeHTMLCell cuando hay formato
$search2 = "                // Posicionar y escribir
                // Usar height basado en font-size para mejor posicionamiento
                // El height del JSON escalado puede causar problemas en TCPDF
                \$line_height = \$size * 0.35;  // Aproximadamente 1pt = 0.35mm
                \$pdf->SetXY(\$x, \$y);
                if (\$type === 'paragraph' || strlen(\$texto) > 80) {
                    \$pdf->MultiCell(\$width, \$line_height, \$texto, 0, \$align, false, 1);
                } else {
                    \$pdf->Cell(\$width, \$line_height, \$texto, 0, 0, \$align);
                }";

$replace2 = "                // Posicionar y escribir
                // Usar height basado en font-size para mejor posicionamiento
                // El height del JSON escalado puede causar problemas en TCPDF
                \$line_height = \$size * 0.35;  // Aproximadamente 1pt = 0.35mm
                \$pdf->SetXY(\$x, \$y);

                // Verificar si el texto tiene marcadores de formato
                if (hasFormatMarkers(\$texto)) {
                    // Convertir marcadores a HTML y usar writeHTMLCell
                    \$texto_html = applyTextFormatting(\$texto);
                    // Construir HTML con estilos
                    \$color_hex = !empty(\$element['color']) ? \$element['color'] : '#000000';
                    \$font_style = '';
                    if (strpos(\$style, 'B') !== false) \$font_style .= 'font-weight:bold;';
                    if (strpos(\$style, 'I') !== false) \$font_style .= 'font-style:italic;';
                    \$html = '<div style=\"font-family:' . \$tcpdf_font . ';font-size:' . \$size . 'pt;color:' . \$color_hex . ';text-align:' . strtolower(\$element['align'] ?? 'left') . ';' . \$font_style . '\">' . \$texto_html . '</div>';
                    \$pdf->writeHTMLCell(\$width, \$height, \$x, \$y, \$html, 0, 1, false, true, strtoupper(substr(\$element['align'] ?? 'L', 0, 1)));
                } else {
                    // Sin formato - usar método tradicional (más rápido)
                    if (\$type === 'paragraph' || strlen(\$texto) > 80) {
                        \$pdf->MultiCell(\$width, \$line_height, \$texto, 0, \$align, false, 1);
                    } else {
                        \$pdf->Cell(\$width, \$line_height, \$texto, 0, 0, \$align);
                    }
                }";

if (strpos($content, $search2) !== false) {
    $content = str_replace($search2, $replace2, $content);
    echo "Renderizado de texto modificado para soportar formato\n";
} else {
    echo "No se encontro patron para modificar renderizado\n";
}

file_put_contents($file, $content);
echo "Modificaciones TCPDF completadas\n";
