<?php
// Cargar ambos archivos de idioma
$lang_ar = include 'lang/es_AR.php';
$lang_uy = include 'lang/es_UY.php';

// Encontrar entradas que están en es_AR pero no en es_UY
$faltantes = [];
$diferentes = [];

foreach ($lang_ar as $key => $value) {
    if (!isset($lang_uy[$key])) {
        $faltantes[$key] = $value;
    } elseif ($lang_uy[$key] !== $value) {
        $diferentes[$key] = [
            'ar' => $value,
            'uy' => $lang_uy[$key]
        ];
    }
}

// Encontrar entradas que están en es_UY pero no en es_AR
$extra_uy = [];
foreach ($lang_uy as $key => $value) {
    if (!isset($lang_ar[$key])) {
        $extra_uy[$key] = $value;
    }
}

echo "=== ENTRADAS FALTANTES EN es_UY.php ===\n";
echo "Cantidad: " . count($faltantes) . "\n\n";

foreach ($faltantes as $key => $value) {
    echo "'{$key}' => '" . addslashes($value) . "',\n";
}

echo "\n\n=== ENTRADAS CON VALORES DIFERENTES ===\n";
echo "Cantidad: " . count($diferentes) . "\n\n";

foreach ($diferentes as $key => $values) {
    echo "Clave: {$key}\n";
    echo "  AR: " . $values['ar'] . "\n";
    echo "  UY: " . $values['uy'] . "\n\n";
}

echo "\n\n=== ENTRADAS EXTRAS EN es_UY.php ===\n";
echo "Cantidad: " . count($extra_uy) . "\n\n";

foreach ($extra_uy as $key => $value) {
    echo "'{$key}' => '" . addslashes($value) . "',\n";
}
?>