<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Agregar Antic Didone al mapeo (usar playfairdisplay por ahora)
$search = "'Playfair Display' => 'playfairdisplay',";
$replace = "'Playfair Display' => 'playfairdisplay',
                    'Antic Didone' => 'playfairdisplay',";

if (strpos($content, "'Antic Didone'") !== false) {
    echo "Antic Didone ya existe en el mapeo\n";
} elseif (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "Antic Didone agregado al mapeo (usando playfairdisplay)\n";
} else {
    echo "ERROR - Patron no encontrado\n";
}
