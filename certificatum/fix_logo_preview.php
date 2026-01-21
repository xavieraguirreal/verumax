<?php
$file = __DIR__ . '/creare.php';
$content = file_get_contents($file);

$search = "} elseif (\$variable === '{{logo_verumax}}') {
                        // Logo Verumax - URL fija global
                        \$img_src = '/assets/images/logo-verumax-escudo.png';";

$replace = "} elseif (\$variable === '{{logo_verumax}}') {
                        // Logo Verumax - usar base64 para evitar problemas de ruta
                        \$logo_verumax_path = __DIR__ . '/../assets/images/logo-verumax-escudo.png';
                        if (file_exists(\$logo_verumax_path)) {
                            \$img_data = base64_encode(file_get_contents(\$logo_verumax_path));
                            \$img_src = 'data:image/png;base64,' . \$img_data;
                        }";

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "OK - Logo Verumax ahora usa base64\n";
} else {
    echo "ERROR - Patron no encontrado\n";
    // Buscar patron alternativo
    if (strpos($content, 'logo-verumax-escudo.png') !== false) {
        echo "El archivo contiene logo-verumax-escudo.png pero con formato diferente\n";
    }
}
