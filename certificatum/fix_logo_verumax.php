<?php
// Script para agregar soporte logo_verumax en creare.php

$file = __DIR__ . '/creare.php';
$content = file_get_contents($file);

$search = "} elseif (\$variable === '{{logo_2}}') {
                        \$img_src = \$instance_config['logo_2_url'] ?? null;
                    } elseif (!empty(\$element['src']))";

$replace = "} elseif (\$variable === '{{logo_2}}') {
                        \$img_src = \$instance_config['logo_2_url'] ?? null;
                    } elseif (\$variable === '{{logo_verumax}}') {
                        // Logo Verumax - URL fija global
                        \$img_src = '/assets/images/logos/verumax_logo.png';
                    } elseif (!empty(\$element['src']))";

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "OK - Logo Verumax agregado a creare.php\n";
} elseif (strpos($content, '{{logo_verumax}}') !== false) {
    echo "Ya existe soporte para logo_verumax en creare.php\n";
} else {
    echo "ERROR - Patron no encontrado en creare.php\n";
}
