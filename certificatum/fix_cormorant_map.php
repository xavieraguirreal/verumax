<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Actualizar mapeo de Cormorant Garamond
$content = str_replace(
    "'Cormorant Garamond' => 'times'",
    "'Cormorant Garamond' => 'cormorantgaramond'",
    $content
);

file_put_contents($file, $content);
echo "OK: Mapeo actualizado - Cormorant Garamond => cormorantgaramond\n";
