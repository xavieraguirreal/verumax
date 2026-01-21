<?php
/**
 * Script para comparar archivos de traducción y actualizar ES_EC con claves faltantes
 * adaptadas al español ecuatoriano formal
 */

// Cargar archivos
$lang = [];
include 'E:\appVerumax\lang\es_AR.php';
$es_ar = $lang;

$lang = [];
include 'E:\appVerumax\lang\es_EC.php';
$es_ec = $lang;

// Encontrar claves faltantes
$keys_ar = array_keys($es_ar);
$keys_ec = array_keys($es_ec);
$missing = array_diff($keys_ar, $keys_ec);

echo "===== ANÁLISIS DE TRADUCCIÓN =====\n\n";
echo "Total claves en ES_AR: " . count($keys_ar) . "\n";
echo "Total claves en ES_EC: " . count($keys_ec) . "\n";
echo "Claves faltantes en ES_EC: " . count($missing) . "\n\n";

if (count($missing) > 0) {
    echo "===== CLAVES FALTANTES =====\n\n";
    foreach ($missing as $key) {
        $valor_ar = $es_ar[$key];
        echo "Clave: $key\n";
        echo "ES_AR: " . (strlen($valor_ar) > 100 ? substr($valor_ar, 0, 100) . '...' : $valor_ar) . "\n";
        echo "---\n";
    }
}

// Generar archivo de salida con todas las claves
$output = [];
foreach ($keys_ar as $key) {
    if (isset($es_ec[$key])) {
        $output[$key] = $es_ec[$key];
    } else {
        // Adaptar texto de ES_AR a ES_EC
        $texto = $es_ar[$key];

        // Conversiones de voseo a ustedeo/tuteo
        $texto = str_replace('tenés', 'tienes', $texto);
        $texto = str_replace('Tenés', 'Tienes', $texto);
        $texto = str_replace('querés', 'quieres', $texto);
        $texto = str_replace('Querés', 'Quieres', $texto);
        $texto = str_replace('podés', 'puedes', $texto);
        $texto = str_replace('Podés', 'Puedes', $texto);
        $texto = str_replace('sabés', 'sabes', $texto);
        $texto = str_replace('Sabés', 'Sabes', $texto);
        $texto = str_replace('estás', 'estás', $texto); // Ya correcto
        $texto = str_replace('vas', 'vas', $texto); // Ya correcto
        $texto = str_replace('sos', 'eres', $texto);
        $texto = str_replace('Sos', 'Eres', $texto);
        $texto = str_replace('vos', 'usted', $texto);
        $texto = str_replace('Vos', 'Usted', $texto);
        $texto = str_replace('tu ', 'su ', $texto);
        $texto = str_replace('Tu ', 'Su ', $texto);
        $texto = str_replace('tus ', 'sus ', $texto);
        $texto = str_replace('Tus ', 'Sus ', $texto);

        // Específicas argentinas
        $texto = str_replace('Elegí', 'Elija', $texto);
        $texto = str_replace('elegí', 'elija', $texto);
        $texto = str_replace('Comenzá', 'Comience', $texto);
        $texto = str_replace('comenzá', 'comience', $texto);
        $texto = str_replace('Contactanos', 'Contáctenos', $texto);
        $texto = str_replace('contactanos', 'contáctenos', $texto);
        $texto = str_replace('Contanos', 'Cuéntenos', $texto);
        $texto = str_replace('contanos', 'cuéntenos', $texto);
        $texto = str_replace('Descubrí', 'Descubra', $texto);
        $texto = str_replace('descubrí', 'descubra', $texto);
        $texto = str_replace('escribinos', 'escríbanos', $texto);
        $texto = str_replace('Escribinos', 'Escríbanos', $texto);
        $texto = str_replace('Ingresá', 'Ingrese', $texto);
        $texto = str_replace('ingresá', 'ingrese', $texto);

        // Regionalisms
        $texto = str_replace('Argentina', 'Ecuador', $texto);
        $texto = str_replace('argentino', 'ecuatoriano', $texto);
        $texto = str_replace('argentina', 'ecuatoriana', $texto);
        $texto = str_replace('DNI', 'Cédula', $texto);
        $texto = str_replace('pesos argentinos', 'dólares', $texto);

        $output[$key] = $texto;
    }
}

// Guardar claves faltantes para revisión manual
file_put_contents('E:\appVerumax\lang\missing_keys.txt', print_r($missing, true));

echo "\n\n===== ARCHIVO DE CLAVES FALTANTES GENERADO =====\n";
echo "Ubicación: E:\\appVerumax\\lang\\missing_keys.txt\n";
echo "\nTotal claves a procesar: " . count($output) . "\n";
