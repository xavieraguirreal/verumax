<?php
/**
 * TEST SIMPLE: Verificar paso a paso
 */

// Mostrar TODOS los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "TEST SIMPLE - Paso a paso<br><hr>";

// Paso 1: Verificar ruta
echo "1. Verificar rutas<br>";
echo "DIR actual: " . __DIR__ . "<br>";
echo "Archivo config esperado: " . __DIR__ . '/../identitas/config.php<br>';
echo "¿Existe config.php? " . (file_exists(__DIR__ . '/../identitas/config.php') ? 'SÍ' : 'NO') . "<br>";
echo "¿Existe bootstrap.php? " . (file_exists(__DIR__ . '/../src/bootstrap.php') ? 'SÍ' : 'NO') . "<br>";
echo "<hr>";

// Paso 2: Intentar cargar bootstrap directamente
echo "2. Cargar bootstrap.php directamente<br>";
try {
    require_once __DIR__ . '/../src/bootstrap.php';
    echo "✓ Bootstrap cargado<br>";
} catch (Exception $e) {
    echo "✗ Error en bootstrap: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
    exit;
} catch (Error $e) {
    echo "✗ Error fatal en bootstrap: " . $e->getMessage() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}
echo "<hr>";

// Paso 3: Verificar clases
echo "3. Verificar clases disponibles<br>";
echo "DatabaseService existe? " . (class_exists('VERUMax\Services\DatabaseService') ? 'SÍ' : 'NO') . "<br>";
echo "TemplateService existe? " . (class_exists('VERUMax\Services\TemplateService') ? 'SÍ' : 'NO') . "<br>";
echo "<hr>";

// Paso 4: Configurar base de datos manualmente
echo "4. Configurar base de datos manualmente<br>";
try {
    \VERUMax\Services\DatabaseService::configure('identitas', [
        'host' => 'localhost',
        'user' => 'verumax_identi',
        'password' => '/hPfiYd6xH',
        'database' => 'verumax_identi',
    ]);
    echo "✓ Base de datos configurada<br>";
} catch (Exception $e) {
    echo "✗ Error configurando: " . $e->getMessage() . "<br>";
    exit;
}
echo "<hr>";

// Paso 5: Probar conexión
echo "5. Probar conexión<br>";
try {
    $pdo = \VERUMax\Services\DatabaseService::get('identitas');
    echo "✓ Conexión exitosa<br>";
    echo "Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";
} catch (Exception $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "<br>";
    exit;
}
echo "<hr>";

// Paso 6: Probar TemplateService
echo "6. Probar TemplateService::getTemplatesForPage()<br>";
try {
    $templates = \VERUMax\Services\TemplateService::getTemplatesForPage('sobre-nosotros');
    echo "✓ TemplateService funciona - " . count($templates) . " templates encontrados<br>";

    if (count($templates) > 0) {
        echo "<pre>";
        print_r($templates[0]);
        echo "</pre>";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "FIN DEL TEST";
?>
