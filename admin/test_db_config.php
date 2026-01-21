<?php
/**
 * TEST: Verificar configuración de DatabaseService para Identitas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Configuración DatabaseService</h1><hr>";

// Paso 1: Cargar config de Identitas
echo "<h2>1. Cargar identitas/config.php</h2>";
try {
    require_once __DIR__ . '/../identitas/config.php';
    echo "<p style='color: green;'>✓ Config cargado</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    exit;
}

// Paso 2: Verificar que DatabaseService existe
echo "<h2>2. Verificar DatabaseService</h2>";
if (class_exists('VERUMax\Services\DatabaseService')) {
    echo "<p style='color: green;'>✓ DatabaseService disponible</p>";
} else {
    echo "<p style='color: red;'>✗ DatabaseService NO disponible</p>";
    exit;
}

// Paso 3: Verificar que TemplateService existe
echo "<h2>3. Verificar TemplateService</h2>";
if (class_exists('VERUMax\Services\TemplateService')) {
    echo "<p style='color: green;'>✓ TemplateService disponible</p>";
} else {
    echo "<p style='color: red;'>✗ TemplateService NO disponible</p>";
    exit;
}

// Paso 4: Probar conexión usando getDBConnection() (legacy)
echo "<h2>4. Probar getDBConnection()</h2>";
try {
    $pdo = getDBConnection();
    echo "<p style='color: green;'>✓ Conexión OK via getDBConnection()</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Paso 5: Probar conexión directa via DatabaseService::get()
echo "<h2>5. Probar DatabaseService::get('identitas')</h2>";
try {
    $pdo2 = \VERUMax\Services\DatabaseService::get('identitas');
    echo "<p style='color: green;'>✓ Conexión OK via DatabaseService::get()</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Paso 6: Intentar usar TemplateService
echo "<h2>6. Probar TemplateService::getTemplatesForPage()</h2>";
try {
    use VERUMax\Services\TemplateService;

    $templates = TemplateService::getTemplatesForPage('sobre-nosotros');
    echo "<p style='color: green;'>✓ TemplateService funciona - " . count($templates) . " templates encontrados</p>";

    if (count($templates) > 0) {
        echo "<pre>";
        print_r($templates[0]);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>Resumen</h2>";
echo "<p>Si todos los pasos son ✓ verdes, el sistema está funcionando correctamente.</p>";
?>
