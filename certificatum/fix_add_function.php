<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Agregar función isValidImagePath si no existe
if (strpos($content, 'function isValidImagePath') === false) {
    $function = '
/**
 * Verifica si path es válido (archivo local o URL)
 */
function isValidImagePath($path) {
    if (empty($path)) return false;
    if (strpos($path, \'http://\') === 0 || strpos($path, \'https://\') === 0) {
        return true; // Es URL, TCPDF puede cargarla
    }
    return file_exists($path);
}
';

    // Insertar después de hasFormatMarkers
    $marker = "function hasFormatMarkers(\$text) {\n    return preg_match('/\*\*.+?\*\*|(?<!\*)\*[^*]+\*(?!\*)/', \$text);\n}";

    if (strpos($content, $marker) !== false) {
        $content = str_replace($marker, $marker . $function, $content);
        file_put_contents($file, $content);
        echo "OK: Función isValidImagePath agregada\n";
    } else {
        // Buscar alternativa
        $content = preg_replace(
            '/(function hasFormatMarkers\(\$text\) \{[^}]+\})/',
            '$1' . $function,
            $content,
            1,
            $count
        );
        if ($count > 0) {
            file_put_contents($file, $content);
            echo "OK: Función agregada (método alternativo)\n";
        } else {
            echo "ERROR: No se pudo agregar la función\n";
        }
    }
} else {
    echo "La función ya existe\n";
}
