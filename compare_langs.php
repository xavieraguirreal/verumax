<?php
// Read es_AR.php and extract all keys
$es_ar_content = file_get_contents('E:\appVerumax\lang\es_AR.php');

// Extract all key-value pairs using regex
preg_match_all("/\s*'([a-z_0-9]+)'\s*=>/", $es_ar_content, $matches_es_ar);
$keys_es_ar = array_unique($matches_es_ar[1]);

// Read eu_ES.php and extract all keys
$eu_es_content = file_get_contents('E:\appVerumax\lang\eu_ES.php');
preg_match_all("/\s*'([a-z_0-9]+)'\s*=>/", $eu_es_content, $matches_eu_es);
$keys_eu_es = array_unique($matches_eu_es[1]);

// Find missing keys
$missing = array_diff($keys_es_ar, $keys_eu_es);

// Keys to be deleted (as per instruction)
$to_delete = [
    'hero_title',
    'privacidad_seccion3_p1_antes_strong',
    'privacidad_seccion3_p1_despues_strong',
    'privacidad_seccion6_p2_antes_email',
    'privacidad_seccion6_p2_despues_email',
    'privacidad_seccion11_email_label',
    'privacidad_seccion12_p1_despues_strong'
];

echo "ANÁLISIS DE SINCRONIZACIÓN DE IDIOMAS\n";
echo "=====================================\n\n";
echo "Archivo Base: es_AR.php\n";
echo "Total de claves en es_AR: " . count($keys_es_ar) . "\n";
echo "Total de claves en eu_ES: " . count($keys_eu_es) . "\n";
echo "Claves faltantes: " . count($missing) . "\n";
echo "Claves a eliminar: " . count($to_delete) . "\n\n";

echo "PRIMERAS 30 CLAVES FALTANTES:\n";
echo "=============================\n";
$missing_sorted = sort($missing);
$count = 0;
foreach ($missing as $key) {
    if ($count++ < 30) {
        echo "  - " . $key . "\n";
    }
}
echo "\n...se muestran las primeras 30 de " . count($missing) . " claves faltantes\n";

// Save missing keys to file
$missing_array = array_values($missing);
sort($missing_array);
file_put_contents('E:\appVerumax\missing_keys_list.txt', implode("\n", $missing_array));
echo "\nClaves faltantes guardadas en missing_keys_list.txt\n";
