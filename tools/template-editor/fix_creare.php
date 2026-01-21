<?php
$file = 'E:/appVerumax/certificatum/creare.php';
$content = file_get_contents($file);

// 1. Agregar /moderno/ como ruta de búsqueda para fondos
$search1 = "\$posibles_rutas = [
                __DIR__ . '/../assets/templates/certificados/' . (\$template_json_data['slug'] ?? 'default') . '/' . \$canvas['background'],
                __DIR__ . '/../assets/templates/certificados/' . \$institucion . '/' . \$canvas['background'],
            ];";

$replace1 = "\$posibles_rutas = [
                __DIR__ . '/../assets/templates/certificados/' . (\$template_json_data['slug'] ?? 'default') . '/' . \$canvas['background'],
                __DIR__ . '/../assets/templates/certificados/' . \$institucion . '/' . \$canvas['background'],
                __DIR__ . '/../assets/templates/certificados/moderno/' . \$canvas['background'],
            ];";

if (strpos($content, $search1) !== false) {
    $content = str_replace($search1, $replace1, $content);
    echo "Ruta /moderno/ agregada para fondos\n";
} else {
    echo "No se encontro patron para rutas de fondo\n";
}

file_put_contents($file, $content);
echo "Modificacion completada\n";
