<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Función helper para verificar si es URL o archivo existente
$helper_function = "
// Helper: verificar si path es válido (archivo local o URL)
function isValidImagePath(\$path) {
    if (empty(\$path)) return false;
    if (strpos(\$path, 'http://') === 0 || strpos(\$path, 'https://') === 0) {
        return true; // Es URL, TCPDF puede cargarla
    }
    return file_exists(\$path);
}
";

// Agregar función después de las otras funciones helper (después de hasFormatMarkers)
$after_marker = "function hasFormatMarkers(\$text) {\n    return preg_match('/\*\*.+?\*\*|(?<!\*)\*[^*]+\*(?!\*)/', \$text);\n}";

if (strpos($content, $after_marker) !== false && strpos($content, 'isValidImagePath') === false) {
    $content = str_replace($after_marker, $after_marker . "\n" . $helper_function, $content);
}

// Reemplazar file_exists($logo_path) por isValidImagePath($logo_path)
$content = str_replace(
    'if (file_exists($logo_path)) {',
    'if (isValidImagePath($logo_path)) {',
    $content
);

file_put_contents($file, $content);
echo "OK: Agregada función isValidImagePath y actualizado check del logo\n";
