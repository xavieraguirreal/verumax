<?php
// Cambiar ruta del logo verumax

// 1. creare_pdf_tcpdf.php
$file1 = __DIR__ . '/creare_pdf_tcpdf.php';
$c1 = file_get_contents($file1);
$c1 = str_replace(
    "assets/images/logos/verumax_logo.png",
    "assets/images/logo-verumax-escudo.png",
    $c1
);
file_put_contents($file1, $c1);
echo "1. creare_pdf_tcpdf.php actualizado\n";

// 2. creare.php
$file2 = __DIR__ . '/creare.php';
$c2 = file_get_contents($file2);
$c2 = str_replace(
    "assets/images/logos/verumax_logo.png",
    "assets/images/logo-verumax-escudo.png",
    $c2
);
file_put_contents($file2, $c2);
echo "2. creare.php actualizado\n";

echo "\nListo!\n";
