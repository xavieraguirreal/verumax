<?php
/**
 * Script para comparar traducciones entre es_AR y es_CL
 */

$ar = include __DIR__ . '/lang/es_AR.php';
$cl = include __DIR__ . '/lang/es_CL.php';

$ar_keys = array_keys($ar);
$cl_keys = array_keys($cl);

$missing_in_cl = array_diff($ar_keys, $cl_keys);
$only_in_cl = array_diff($cl_keys, $ar_keys);
$common_keys = array_intersect($ar_keys, $cl_keys);

echo "=== REPORTE DE COMPARACIÓN DE TRADUCCIONES ===\n\n";

echo "Total de claves en es_AR: " . count($ar_keys) . "\n";
echo "Total de claves en es_CL: " . count($cl_keys) . "\n";
echo "Claves comunes: " . count($common_keys) . "\n";
echo "Faltantes en es_CL: " . count($missing_in_cl) . "\n";
echo "Solo en es_CL: " . count($only_in_cl) . "\n\n";

echo "=== CLAVES FALTANTES EN es_CL.php ===\n";
foreach ($missing_in_cl as $key) {
    echo "  - '$key'\n";
}

if (count($only_in_cl) > 0) {
    echo "\n=== CLAVES QUE SOLO EXISTEN EN es_CL (no en es_AR) ===\n";
    foreach ($only_in_cl as $key) {
        echo "  - '$key'\n";
    }
}

echo "\n=== DIFERENCIAS EN VALORES (primeros 50) ===\n";
$differences = [];
foreach ($common_keys as $key) {
    if ($ar[$key] !== $cl[$key]) {
        $differences[] = $key;
    }
}

echo "Total de valores diferentes: " . count($differences) . "\n\n";
foreach (array_slice($differences, 0, 50) as $key) {
    echo "Clave: '$key'\n";
    echo "  AR: " . mb_substr($ar[$key], 0, 80) . (mb_strlen($ar[$key]) > 80 ? '...' : '') . "\n";
    echo "  CL: " . mb_substr($cl[$key], 0, 80) . (mb_strlen($cl[$key]) > 80 ? '...' : '') . "\n\n";
}
