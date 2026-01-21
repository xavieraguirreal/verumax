<?php
/**
 * Generador de estructura modular de idiomas
 * Uso: php generate_modular.php es_CL
 */

$target_lang = $argv[1] ?? null;

if (!$target_lang) {
    die("Uso: php generate_modular.php <codigo_idioma>\nEj: php generate_modular.php es_CL\n");
}

$lang_file = __DIR__ . '/' . $target_lang . '.php';
if (!file_exists($lang_file)) {
    die("Error: No existe el archivo $lang_file\n");
}

// Cargar el archivo de idioma
$lang = [];
include $lang_file;

if (empty($lang)) {
    die("Error: El archivo $lang_file no contiene traducciones\n");
}

// Distribución de claves por archivo
$distribution = [
    'common.php' => ['nav_', 'footer_', 'badge_', 'stat_', 'social_', 'benefit_'],
    'land_home.php' => ['hero_', 'cat_', 'servicios_', 'serv_', 'casos_', 'caso_', 'faq_', 'validar_', 'contacto_', 'meta_', 'equipo_', 'productos_', 'ecosol_', 'veritas_'],
    'land_certificatum.php' => ['acad_'],
    'land_identitas.php' => ['prof_'],
    'land_mutuales.php' => ['mut_'],
    'page_privacidad.php' => ['privacidad_'],
    'page_terminos.php' => ['terminos_'],
];

$results = [];

foreach ($lang as $key => $value) {
    foreach ($distribution as $file => $prefixes) {
        foreach ($prefixes as $prefix) {
            if (strpos($key, $prefix) === 0) {
                $results[$file][$key] = $value;
                break 2;
            }
        }
    }
}

// Crear directorio si no existe
$dir = __DIR__ . '/' . $target_lang;
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
    echo "Creado directorio: $dir\n";
}

// Generar cada archivo
$total_keys = 0;
foreach ($results as $filename => $keys) {
    $filepath = $dir . '/' . $filename;
    $name = ucfirst(str_replace(['land_', 'page_', '.php'], '', $filename));

    $content = "<?php\n";
    $content .= "/**\n";
    $content .= " * " . strtoupper($target_lang) . " - $name\n";
    $content .= " * Claves: " . count($keys) . "\n";
    $content .= " */\n\n";
    $content .= "return [\n";

    foreach ($keys as $k => $v) {
        // Escapar comillas simples
        $escaped = str_replace("'", "\\'", $v);
        $content .= "    '$k' => '$escaped',\n";
    }

    $content .= "];\n";

    file_put_contents($filepath, $content);
    echo "Creado: $filename (" . count($keys) . " claves)\n";
    $total_keys += count($keys);
}

echo "\n✅ Estructura modular creada para $target_lang\n";
echo "   Directorio: $dir\n";
echo "   Total: $total_keys claves en " . count($results) . " archivos\n";
