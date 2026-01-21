<?php
$file = 'E:/appVerumax/certificatum/creare.php';
$content = file_get_contents($file);

// Corregir el reemplazo de variables - buscar con llaves completas
$search = "                        \$p_texto = preg_replace_callback('/\\{\\{(\\w+)\\}\\}/', function(\$matches) use (\$variables) {
                            return \$variables[\$matches[1]] ?? \$matches[0];
                        }, \$element['text']);";

$replace = "                        \$p_texto = preg_replace_callback('/\\{\\{(\\w+)\\}\\}/', function(\$matches) use (\$variables) {
                            \$key = '{{' . \$matches[1] . '}}';
                            return \$variables[\$key] ?? \$matches[0];
                        }, \$element['text']);";

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "Reemplazo de variables corregido\n";
} else {
    echo "No se encontro el patron\n";
}
