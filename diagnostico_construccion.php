<?php
/**
 * SCRIPT DE DIAGNÓSTICO - Modo Construcción
 *
 * Este script muestra el valor actual de sitio_en_construccion en la base de datos
 */

require_once __DIR__ . '/identitas/config.php';

$slug = 'sajur';

try {
    $instance = getInstanceConfig($slug);

    echo "<h1>Diagnóstico de Modo Construcción</h1>";
    echo "<h2>Instancia: " . htmlspecialchars($slug) . "</h2>";
    echo "<hr>";

    echo "<h3>Estado actual en Base de Datos:</h3>";
    echo "<pre>";
    echo "sitio_en_construccion: " . var_export($instance['sitio_en_construccion'] ?? 'NO DEFINIDO', true) . "\n";
    echo "mensaje_construccion: " . var_export($instance['mensaje_construccion'] ?? 'NO DEFINIDO', true) . "\n";
    echo "</pre>";

    echo "<h3>Tipo de dato:</h3>";
    echo "<pre>";
    echo "Tipo: " . gettype($instance['sitio_en_construccion'] ?? null) . "\n";
    echo "</pre>";

    echo "<h3>Evaluación de condición:</h3>";
    echo "<pre>";
    $valor = $instance['sitio_en_construccion'] ?? null;
    echo "¿Es == 1? " . ($valor == 1 ? 'SÍ (redirige)' : 'NO (no redirige)') . "\n";
    echo "¿Es === 1? " . ($valor === 1 ? 'SÍ' : 'NO') . "\n";
    echo "¿Es truthy? " . ($valor ? 'SÍ' : 'NO') . "\n";
    echo "</pre>";

    echo "<h3>Configuración completa:</h3>";
    echo "<pre>";
    print_r($instance);
    echo "</pre>";

} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
