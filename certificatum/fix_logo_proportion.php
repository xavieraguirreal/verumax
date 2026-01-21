<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Para logo_1: usar width y dejar que TCPDF calcule height (0 = proporcional)
$old1 = "} elseif (\$variable === '{{logo}}' || \$variable === '{{logo_1}}') {
                    // Logo institucional
                    if (isValidImagePath(\$logo_path)) {
                        \$pdf->Image(\$logo_path, \$x, \$y, \$width, \$height, '', '', '', false, 300, '', false, false, 0);
                    }
                }";

$new1 = "} elseif (\$variable === '{{logo}}' || \$variable === '{{logo_1}}') {
                    // Logo institucional - mantener proporción
                    if (isValidImagePath(\$logo_path)) {
                        // Usar width como límite, height=0 para mantener proporción
                        \$pdf->Image(\$logo_path, \$x, \$y, \$width, 0, '', '', '', false, 300, '', false, false, 0);
                    }
                }";

if (strpos($content, $old1) !== false) {
    $content = str_replace($old1, $new1, $content);
    echo "OK: Logo 1 actualizado para mantener proporción\n";
} else {
    echo "Logo 1: buscando alternativa...\n";
    // Intentar reemplazo más simple
    $content = str_replace(
        "\$pdf->Image(\$logo_path, \$x, \$y, \$width, \$height, '', '', '', false, 300, '', false, false, 0);\n                    }\n                } elseif (\$variable === '{{logo_2}}')",
        "\$pdf->Image(\$logo_path, \$x, \$y, \$width, 0, '', '', '', false, 300, '', false, false, 0);\n                    }\n                } elseif (\$variable === '{{logo_2}}')",
        $content,
        $count
    );
    echo "Logo 1: $count reemplazos\n";
}

// También para logo_verumax
$content = str_replace(
    "\$pdf->Image(\$logo_verumax_path, \$x, \$y, \$width, \$height,",
    "\$pdf->Image(\$logo_verumax_path, \$x, \$y, \$width, 0,",
    $content,
    $count2
);
echo "Logo Verumax: $count2 reemplazos\n";

file_put_contents($file, $content);
echo "Archivo guardado\n";
