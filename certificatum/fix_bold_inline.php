<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Cambiar strong a span con estilo inline explícito para mejor compatibilidad TCPDF
$old = "function applyTextFormatting(\$text) {
    if (empty(\$text)) return \$text;

    // Negrita: **texto** -> <strong>texto</strong> (TCPDF maneja mejor strong)
    \$result = preg_replace('/\*\*(.+?)\*\*/s', '<strong>\\1</strong>', \$text);

    // Italica: *texto* -> <em>texto</em> (solo asteriscos simples, no dobles)
    \$result = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/s', '<em>\\1</em>', \$result);

    return \$result;
}";

$new = "function applyTextFormatting(\$text) {
    if (empty(\$text)) return \$text;

    // Negrita: **texto** -> span con font-weight:bold (más robusto en TCPDF)
    \$result = preg_replace('/\*\*(.+?)\*\*/s', '<span style=\"font-weight:bold;\">\\1</span>', \$text);

    // Italica: *texto* -> span con font-style:italic
    \$result = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/s', '<span style=\"font-style:italic;\">\\1</span>', \$result);

    return \$result;
}";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "OK: Cambiado a span con estilos inline\n";
} else {
    echo "ERROR: No se encontró el bloque a reemplazar\n";
    // Mostrar lo que hay actualmente
    preg_match('/function applyTextFormatting\(\$text\) \{.+?\n\}/s', $content, $m);
    echo "Función actual:\n" . ($m[0] ?? 'No encontrada') . "\n";
}
