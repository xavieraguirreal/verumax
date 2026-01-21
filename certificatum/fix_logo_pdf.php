<?php
$file = __DIR__ . '/creare_pdf_tcpdf.php';
$content = file_get_contents($file);

// Reemplazar la definición del logo_path para usar instance_config
$old = "    // Rutas de recursos institucionales\n    \$logo_path = __DIR__ . '/../assets/images/logos/' . \$institucion . '_logo.png';";

$new = "    // Rutas de recursos institucionales
    // Primero intentar archivo local, sino usar URL de la configuración
    \$logo_path_local = __DIR__ . '/../assets/images/logos/' . \$institucion . '_logo.png';
    if (file_exists(\$logo_path_local)) {
        \$logo_path = \$logo_path_local;
    } else {
        // Usar URL de instance_config (TCPDF puede cargar desde URLs)
        \$logo_path = \$instance_config['logo_url'] ?? null;
    }";

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "OK: Logo path actualizado para usar instance_config['logo_url']\n";
} else {
    echo "No encontrado exacto. Buscando alternativa...\n";

    // Intentar encontrar solo la línea del logo
    if (strpos($content, "\$logo_path = __DIR__ . '/../assets/images/logos/' . \$institucion . '_logo.png';") !== false) {
        $content = str_replace(
            "\$logo_path = __DIR__ . '/../assets/images/logos/' . \$institucion . '_logo.png';",
            "\$logo_path_local = __DIR__ . '/../assets/images/logos/' . \$institucion . '_logo.png';\n    \$logo_path = file_exists(\$logo_path_local) ? \$logo_path_local : (\$instance_config['logo_url'] ?? null);",
            $content
        );
        file_put_contents($file, $content);
        echo "OK: Reemplazo alternativo aplicado\n";
    } else {
        echo "ERROR: No se encontró el código a reemplazar\n";
    }
}
