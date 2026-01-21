<?php
$file = 'E:/appVerumax/certificatum/creare.php';
$content = file_get_contents($file);

// 1. Agregar función helper después de los use statements
$search1 = "use VERUMax\\Services\\LanguageService;

// 3. Obtener parámetros";

$replace1 = "use VERUMax\\Services\\LanguageService;

/**
 * Convierte marcadores de formato a HTML
 * **texto** -> <b>texto</b>
 * *texto* -> <i>texto</i>
 */
function applyTextFormatting(\$text) {
    if (empty(\$text)) return \$text;
    // Primero escapar HTML para seguridad
    \$result = htmlspecialchars(\$text);
    // Negrita: **texto** -> <b>texto</b>
    \$result = preg_replace('/\\*\\*(.+?)\\*\\*/s', '<b>\$1</b>', \$result);
    // Italica: *texto* -> <i>texto</i>
    \$result = preg_replace('/(?<!\\*)\\*([^*]+)\\*(?!\\*)/s', '<i>\$1</i>', \$result);
    return \$result;
}

// 3. Obtener parámetros";

if (strpos($content, $search1) !== false) {
    $content = str_replace($search1, $replace1, $content);
    echo "Funcion applyTextFormatting() agregada\n";
} else {
    echo "No se encontro patron para agregar funcion\n";
}

// 2. Modificar renderizado de texto (línea ~571) - texto normal
$search2 = "overflow: hidden;\"><?php echo htmlspecialchars(\$content); ?></span>";
$replace2 = "overflow: hidden;\"><?php echo applyTextFormatting(\$content); ?></span>";

if (strpos($content, $search2) !== false) {
    $content = str_replace($search2, $replace2, $content);
    echo "Renderizado de texto normal modificado\n";
} else {
    echo "No se encontro patron para texto normal\n";
}

// 3. Modificar renderizado de párrafo (línea ~596)
$search3 = "overflow: hidden;\"><?php echo htmlspecialchars(\$parrafo_default); ?></span>";
$replace3 = "overflow: hidden;\"><?php echo applyTextFormatting(\$parrafo_default); ?></span>";

if (strpos($content, $search3) !== false) {
    $content = str_replace($search3, $replace3, $content);
    echo "Renderizado de parrafo modificado\n";
} else {
    echo "No se encontro patron para parrafo\n";
}

file_put_contents($file, $content);
echo "Modificaciones creare.php completadas\n";
