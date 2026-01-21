<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Cambiar la función applyTextFormatting para usar strong/em en lugar de b/i
// TCPDF maneja mejor las etiquetas semánticas HTML5
$old = "function applyTextFormatting(\$text) {
    if (empty(\$text)) return \$text;

    // Negrita: **texto** -> <b>texto</b>
    \$result = preg_replace('/\*\*(.+?)\*\*/s', '<b>\$1</b>', \$text);

    // Italica: *texto* -> <i>texto</i> (solo asteriscos simples, no dobles)
    \$result = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/s', '<i>\$1</i>', \$result);

    return \$result;
}";

$new = "function applyTextFormatting(\$text) {
    if (empty(\$text)) return \$text;

    // Negrita: **texto** -> <strong>texto</strong> (TCPDF maneja mejor strong)
    \$result = preg_replace('/\*\*(.+?)\*\*/s', '<strong>\\1</strong>', \$text);

    // Italica: *texto* -> <em>texto</em> (solo asteriscos simples, no dobles)
    \$result = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/s', '<em>\\1</em>', \$result);

    return \$result;
}";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "OK: Función applyTextFormatting actualizada (b->strong, i->em)\n";
} else {
    echo "No se encontró el bloque exacto. Buscando alternativa...\n";

    // Buscar y reemplazar líneas específicas
    $content = str_replace(
        "// Negrita: **texto** -> <b>texto</b>\n    \$result = preg_replace('/\*\*(.+?)\*\*/s', '<b>\$1</b>', \$text);",
        "// Negrita: **texto** -> <strong>texto</strong> (TCPDF maneja mejor strong)\n    \$result = preg_replace('/\*\*(.+?)\*\*/s', '<strong>\\1</strong>', \$text);",
        $content
    );

    $content = str_replace(
        "// Italica: *texto* -> <i>texto</i> (solo asteriscos simples, no dobles)\n    \$result = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/s', '<i>\$1</i>', \$result);",
        "// Italica: *texto* -> <em>texto</em> (solo asteriscos simples, no dobles)\n    \$result = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/s', '<em>\\1</em>', \$result);",
        $content
    );

    file_put_contents($file, $content);
    echo "Reemplazo de líneas específicas aplicado.\n";
}
