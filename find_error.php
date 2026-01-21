<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Buscando error de sintaxis en es_AR.php</h1>";

$file_content = file_get_contents('lang/es_AR.php');
$lines = explode("\n", $file_content);

echo "<p>Total de líneas: " . count($lines) . "</p>";

// Intentar evaluar el archivo
ob_start();
$result = @include('lang/es_AR.php');
$output = ob_get_clean();

if ($result === false || !isset($lang) || !is_array($lang)) {
    echo "<p style='color:red;'><strong>ERROR: El archivo no carga correctamente</strong></p>";

    // Mostrar el último error de PHP
    $error = error_get_last();
    if ($error) {
        echo "<pre>";
        print_r($error);
        echo "</pre>";
    }
} else {
    echo "<p style='color:green;'>✓ Archivo cargado correctamente</p>";
    echo "<p>Claves en \$lang: " . count($lang) . "</p>";
}

// Buscar problemas comunes
echo "<hr><h2>Revisando estructura...</h2>";

// Contar comillas simples por línea
$problematic_lines = [];
foreach ($lines as $line_num => $line) {
    $actual_line = $line_num + 1;

    // Ignorar comentarios
    if (preg_match('/^\s*\/\//', $line)) {
        continue;
    }

    // Si la línea contiene '=>' debe terminar en coma
    if (strpos($line, '=>') !== false) {
        if (!preg_match('/,\s*$/', $line) && !preg_match('/\[\s*$/', $line)) {
            $problematic_lines[] = "Línea $actual_line: Falta coma al final: " . substr($line, 0, 80);
        }
    }

    // Contar comillas simples (debe ser par)
    $single_quotes = substr_count($line, "'");
    if ($single_quotes > 0 && $single_quotes % 2 != 0) {
        // Verificar si no es un apóstrofe escapado o dentro de comillas
        if (strpos($line, '=>') !== false) {
            $problematic_lines[] = "Línea $actual_line: Número impar de comillas simples ($single_quotes): " . htmlspecialchars(substr($line, 0, 100));
        }
    }
}

if (count($problematic_lines) > 0) {
    echo "<h3 style='color:red;'>Líneas problemáticas encontradas:</h3>";
    echo "<ol>";
    foreach ($problematic_lines as $problem) {
        echo "<li>" . htmlspecialchars($problem) . "</li>";
    }
    echo "</ol>";
} else {
    echo "<p style='color:green;'>No se encontraron problemas obvios de estructura</p>";
}
