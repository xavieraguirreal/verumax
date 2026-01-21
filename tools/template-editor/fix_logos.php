<?php
$file = 'E:/appVerumax/certificatum/creare.php';
$content = file_get_contents($file);

// Reemplazar la sección de logos con fallback a archivos locales
$search = "// Obtener branding de la configuración
\$logo_url = \$instance_config['logo_url'] ?? 'https://placehold.co/100x100/3b82f6/ffffff?text=' . strtoupper(substr(\$institucion, 0, 2));
\$logo_url_small = \$instance_config['logo_url'] ?? 'https://placehold.co/80x80/3b82f6/ffffff?text=' . strtoupper(substr(\$institucion, 0, 2));";

$replace = "// Obtener branding de la configuración (con fallback a archivos locales)
\$logo_url = null;
\$logo_2_url = null;

// Logo principal: BD -> archivo local -> placeholder
if (!empty(\$instance_config['logo_url'])) {
    \$logo_url = \$instance_config['logo_url'];
} else {
    // Buscar archivo local
    \$logo_local_path = __DIR__ . '/../assets/images/logos/' . \$institucion . '_logo.png';
    if (file_exists(\$logo_local_path)) {
        \$logo_url = 'data:image/png;base64,' . base64_encode(file_get_contents(\$logo_local_path));
    } else {
        \$logo_url = 'https://placehold.co/100x100/3b82f6/ffffff?text=' . strtoupper(substr(\$institucion, 0, 2));
    }
}
\$logo_url_small = \$logo_url;

// Logo secundario: BD -> archivo local
if (!empty(\$instance_config['logo_2_url'])) {
    \$logo_2_url = \$instance_config['logo_2_url'];
} else {
    \$logo_2_local_path = __DIR__ . '/../assets/images/logos/' . \$institucion . '_logo_2.png';
    if (file_exists(\$logo_2_local_path)) {
        \$logo_2_url = 'data:image/png;base64,' . base64_encode(file_get_contents(\$logo_2_local_path));
    }
}";

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "Logos con fallback a archivos locales agregado\n";
} else {
    echo "No se encontro el patron\n";
}
