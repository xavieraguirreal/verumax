<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Cambiar el mapeo de Antic Didone a la fuente correcta
$content = str_replace(
    "'Antic Didone' => 'playfairdisplay'",
    "'Antic Didone' => 'anticdidone'",
    $content
);

file_put_contents($file, $content);
echo "Mapeo actualizado: Antic Didone => anticdidone\n";
