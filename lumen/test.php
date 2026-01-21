<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test 1: PHP funciona<br>";

session_start();
echo "Test 2: Sesiones funcionan<br>";

// Simular login
$_SESSION['lumen_logged_in'] = true;
$_SESSION['lumen_nombre'] = 'Juan Test';
$_SESSION['lumen_cliente_id'] = 'fotosjuan';

echo "Test 3: Sesión configurada<br>";
echo "Cliente ID: " . $_SESSION['lumen_cliente_id'] . "<br>";

// Intentar cargar lumen_datos.php
echo "Test 4: Intentando cargar lumen_datos.php<br>";
$ruta = __DIR__ . '/../lumen_datos.php';
echo "Ruta: " . $ruta . "<br>";
echo "Archivo existe: " . (file_exists($ruta) ? 'SI' : 'NO') . "<br>";

if (file_exists($ruta)) {
    require_once $ruta;
    echo "Test 5: lumen_datos.php cargado<br>";

    // Verificar función
    if (function_exists('obtenerPortfolioLumen')) {
        echo "Test 6: Función obtenerPortfolioLumen existe<br>";

        $portfolio = obtenerPortfolioLumen('fotosjuan');
        if ($portfolio) {
            echo "Test 7: Portfolio cargado exitosamente<br>";
            echo "Nombre: " . $portfolio['nombre_marca'] . "<br>";
            echo "Galerías: " . count($portfolio['galerias']) . "<br>";
        } else {
            echo "Test 7: ERROR - Portfolio no encontrado<br>";
        }
    } else {
        echo "Test 6: ERROR - Función no existe<br>";
    }
} else {
    echo "Test 5: ERROR - Archivo no existe<br>";
}

echo "<br><a href='dashboard.php'>Ir al Dashboard</a>";
?>
