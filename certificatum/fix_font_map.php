<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

$content = str_replace(
    "'Playfair Display' => 'times'",
    "'Playfair Display' => 'playfairdisplay'",
    $content
);

file_put_contents($file, $content);
echo "Mapeo de fuentes actualizado\n";
