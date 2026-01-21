<?php
// Script para comparar archivos de idioma y encontrar claves faltantes o desactualizadas

// Cargar ambos archivos
$lang_ar = [];
include 'E:\appVerumax\lang\es_AR.php';
$lang_ar = $lang;

$lang_ec = [];
$lang = [];
include 'E:\appVerumax\lang\es_EC.php';
$lang_ec = $lang;

// Encontrar claves faltantes en EC
$missing_keys = array_diff_key($lang_ar, $lang_ec);

// Encontrar claves con valores diferentes (potencialmente desactualizadas)
$different_values = [];
foreach ($lang_ec as $key => $value_ec) {
    if (isset($lang_ar[$key]) && $lang_ar[$key] !== $value_ec) {
        // Solo incluir si el valor argentino cambió (no solo adaptación regional)
        $different_values[$key] = [
            'ar' => $lang_ar[$key],
            'ec' => $value_ec
        ];
    }
}

// Generar reporte
echo "=== REPORTE DE COMPARACIÓN ES_AR vs ES_EC ===\n\n";
echo "CLAVES FALTANTES EN ES_EC: " . count($missing_keys) . "\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($missing_keys as $key => $value) {
    echo "Clave: $key\n";
    echo "Valor AR: $value\n";
    echo "---\n\n";
}

echo "\n\n";
echo "CLAVES CON VALORES DIFERENTES: " . count($different_values) . "\n";
echo str_repeat("=", 80) . "\n\n";

foreach ($different_values as $key => $values) {
    echo "Clave: $key\n";
    echo "AR: {$values['ar']}\n";
    echo "EC: {$values['ec']}\n";
    echo "---\n\n";
}

// Generar archivo JSON con las claves faltantes para procesamiento
file_put_contents(
    'E:\appVerumax\lang\missing_keys.json',
    json_encode($missing_keys, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "\n\nArchivo JSON generado: missing_keys.json\n";
echo "Total de claves en AR: " . count($lang_ar) . "\n";
echo "Total de claves en EC: " . count($lang_ec) . "\n";
?>
