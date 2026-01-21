<?php
// ====================================================
// DIAGNÓSTICO DE FAVICONS
// Verifica ubicación, existencia y permisos
// ====================================================

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico Favicons</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        .info { color: #0ff; }
        pre { background: #000; padding: 10px; border: 1px solid #333; }
        h2 { color: #fff; border-bottom: 2px solid #333; }
    </style>
</head>
<body>
    <h1>🔍 DIAGNÓSTICO DE FAVICONS</h1>

    <?php
    echo "<h2>1. Información del servidor</h2>";
    echo "<pre>";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "Script Filename: " . __FILE__ . "\n";
    echo "Current Directory: " . getcwd() . "\n";
    echo "Host: " . $_SERVER['HTTP_HOST'] . "\n";
    echo "</pre>";

    echo "<h2>2. Verificando directorio /identitas/favicons/</h2>";

    // Rutas posibles
    $rutas_posibles = [
        __DIR__ . '/identitas/favicons',
        $_SERVER['DOCUMENT_ROOT'] . '/identitas/favicons',
        dirname(__DIR__) . '/identitas/favicons',
        'D:/appVerumax/identitas/favicons'
    ];

    $directorio_encontrado = null;

    foreach ($rutas_posibles as $ruta) {
        echo "<div style='margin: 10px 0;'>";
        echo "<strong>Probando:</strong> $ruta<br>";

        if (file_exists($ruta)) {
            if (is_dir($ruta)) {
                echo "<span class='success'>✅ Directorio EXISTE</span><br>";
                $directorio_encontrado = $ruta;

                // Permisos
                $perms = fileperms($ruta);
                echo "Permisos: " . substr(sprintf('%o', $perms), -4) . "<br>";

                // Verificar si es legible
                if (is_readable($ruta)) {
                    echo "<span class='success'>✅ Es LEGIBLE</span><br>";
                } else {
                    echo "<span class='error'>❌ NO es legible</span><br>";
                }

                // Verificar si es escribible
                if (is_writable($ruta)) {
                    echo "<span class='success'>✅ Es ESCRIBIBLE</span><br>";
                } else {
                    echo "<span class='warning'>⚠️ NO es escribible</span><br>";
                }

                break;
            } else {
                echo "<span class='error'>❌ Existe pero NO es un directorio</span><br>";
            }
        } else {
            echo "<span class='error'>❌ NO existe</span><br>";
        }
        echo "</div>";
    }

    if (!$directorio_encontrado) {
        echo "<div class='error'><h3>❌ ERROR: No se encontró el directorio de favicons</h3></div>";

        // Intentar crear el directorio
        $ruta_crear = __DIR__ . '/identitas/favicons';
        echo "<div class='info'>";
        echo "<p>Intentando crear directorio en: $ruta_crear</p>";

        if (!file_exists(__DIR__ . '/identitas')) {
            echo "<p>Creando directorio padre /identitas...</p>";
            @mkdir(__DIR__ . '/identitas', 0755, true);
        }

        if (@mkdir($ruta_crear, 0755, true)) {
            echo "<span class='success'>✅ Directorio creado exitosamente</span>";
            $directorio_encontrado = $ruta_crear;
        } else {
            echo "<span class='error'>❌ No se pudo crear el directorio</span>";
        }
        echo "</div>";
    }

    if ($directorio_encontrado) {
        echo "<h2>3. Archivos en el directorio de favicons</h2>";
        echo "<pre>";

        $archivos = @scandir($directorio_encontrado);

        if ($archivos === false) {
            echo "<span class='error'>❌ No se pudo leer el contenido del directorio</span>\n";
        } else {
            $archivos = array_diff($archivos, ['.', '..']);

            if (empty($archivos)) {
                echo "<span class='warning'>⚠️ El directorio está VACÍO</span>\n";
            } else {
                echo "Total de archivos: " . count($archivos) . "\n\n";

                foreach ($archivos as $archivo) {
                    $ruta_completa = $directorio_encontrado . '/' . $archivo;
                    $size = filesize($ruta_completa);
                    $perms = fileperms($ruta_completa);
                    $perms_str = substr(sprintf('%o', $perms), -4);

                    echo "📄 $archivo\n";
                    echo "   Tamaño: " . number_format($size) . " bytes\n";
                    echo "   Permisos: $perms_str\n";
                    echo "   Legible: " . (is_readable($ruta_completa) ? '✅' : '❌') . "\n";
                    echo "\n";
                }
            }
        }

        echo "</pre>";

        echo "<h2>4. Verificando archivos específicos de SAJuR</h2>";

        $archivos_esperados = [
            'sajur-favicon-16x16.png',
            'sajur-favicon-32x32.png',
            'sajur-favicon-192x192.png',
            'sajur-favicon-512x512.png',
            'sajur-apple-touch-icon.png'
        ];

        echo "<pre>";
        foreach ($archivos_esperados as $archivo) {
            $ruta_archivo = $directorio_encontrado . '/' . $archivo;
            echo "Buscando: $archivo ... ";

            if (file_exists($ruta_archivo)) {
                $size = filesize($ruta_archivo);
                echo "<span class='success'>✅ EXISTE</span> (" . number_format($size) . " bytes)\n";
            } else {
                echo "<span class='error'>❌ NO EXISTE</span>\n";
            }
        }
        echo "</pre>";

        echo "<h2>5. URLs de acceso</h2>";
        echo "<p>Los favicons deberían ser accesibles en:</p>";
        echo "<pre>";

        $base_url = 'https://sajur.verumax.com/identitas/favicons/';

        foreach ($archivos_esperados as $archivo) {
            $url = $base_url . $archivo;
            echo '<a href="' . $url . '" target="_blank" style="color: #0ff;">' . $url . '</a>' . "\n";
        }

        echo "</pre>";
    }

    echo "<h2>6. Verificando archivo header.php</h2>";

    $header_paths = [
        __DIR__ . '/identitas/templates/header.php',
        __DIR__ . '/sajur/header.php'
    ];

    foreach ($header_paths as $header_path) {
        if (file_exists($header_path)) {
            echo "<div style='margin: 10px 0;'>";
            echo "<strong>Archivo:</strong> $header_path<br>";
            echo "<span class='success'>✅ Existe</span><br>";

            $contenido = file_get_contents($header_path);

            // Buscar referencias a favicon
            if (strpos($contenido, 'favicon') !== false) {
                echo "<span class='success'>✅ Contiene referencias a favicon</span><br>";

                // Extraer las líneas con favicon
                $lineas = explode("\n", $contenido);
                echo "<pre>";
                foreach ($lineas as $num => $linea) {
                    if (stripos($linea, 'favicon') !== false || stripos($linea, 'apple-touch-icon') !== false) {
                        echo "Línea " . ($num + 1) . ": " . htmlspecialchars(trim($linea)) . "\n";
                    }
                }
                echo "</pre>";
            } else {
                echo "<span class='warning'>⚠️ NO contiene referencias a favicon</span><br>";
            }
            echo "</div>";
        }
    }

    echo "<h2>7. Solución recomendada</h2>";
    echo "<div class='info'>";
    echo "<p>Si los archivos NO existen, necesitas:</p>";
    echo "<ol>";
    echo "<li>Ir a: <a href='https://sajur.verumax.com/admin/?modulo=general' style='color: #0ff;'>Admin > General</a></li>";
    echo "<li>Hacer clic en 'Generar Favicon' en la sección Logo y Favicon</li>";
    echo "<li>Verificar que se creen los archivos en: " . ($directorio_encontrado ?? '/identitas/favicons/') . "</li>";
    echo "</ol>";
    echo "</div>";

    ?>
</body>
</html>
