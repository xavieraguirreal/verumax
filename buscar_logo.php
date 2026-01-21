<?php
/**
 * BUSCADOR DE ARCHIVOS SUBIDOS
 * Busca archivos de logo en todas las ubicaciones posibles
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscador de Logos</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
        .card { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2E7D32; margin-top: 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔍 Buscador de Archivos de Logo</h1>

        <?php
        $base_dir = __DIR__;

        echo '<h2>📍 Rutas del Sistema</h2>';
        echo '<table>';
        echo '<tr><th>Variable</th><th>Valor</th></tr>';
        echo '<tr><td><strong>__DIR__</strong></td><td>' . __DIR__ . '</td></tr>';
        echo '<tr><td><strong>$_SERVER[\'DOCUMENT_ROOT\']</strong></td><td>' . $_SERVER['DOCUMENT_ROOT'] . '</td></tr>';
        echo '<tr><td><strong>getcwd()</strong></td><td>' . getcwd() . '</td></tr>';
        echo '</table>';

        // Buscar archivo específico
        $filename = 'sajur-logo-1763508666.png';

        echo '<h2>🔎 Buscando archivo: <code>' . $filename . '</code></h2>';

        $ubicaciones_posibles = [
            'uploads/logos/' => $base_dir . '/uploads/logos/' . $filename,
            'sajur/uploads/logos/' => $base_dir . '/sajur/uploads/logos/' . $filename,
            'admin/uploads/logos/' => $base_dir . '/admin/uploads/logos/' . $filename,
            '../uploads/logos/' => dirname($base_dir) . '/uploads/logos/' . $filename,
            '../../uploads/logos/' => dirname(dirname($base_dir)) . '/uploads/logos/' . $filename,
        ];

        $encontrado = false;

        echo '<table>';
        echo '<tr><th>Ubicación</th><th>Ruta completa</th><th>Existe</th></tr>';

        foreach ($ubicaciones_posibles as $nombre => $ruta) {
            $existe = file_exists($ruta);

            echo '<tr>';
            echo '<td><code>/' . $nombre . '</code></td>';
            echo '<td style="font-size: 11px;">' . $ruta . '</td>';

            if ($existe) {
                echo '<td><span style="color: green; font-weight: bold;">✅ ENCONTRADO</span></td>';
                $encontrado = $ruta;
            } else {
                echo '<td><span style="color: red;">❌ No existe</span></td>';
            }

            echo '</tr>';
        }

        echo '</table>';

        if ($encontrado) {
            echo '<div class="success">';
            echo '<strong>✅ ARCHIVO ENCONTRADO</strong><br><br>';
            echo '<strong>Ruta:</strong> ' . $encontrado . '<br>';
            echo '<strong>Tamaño:</strong> ' . number_format(filesize($encontrado) / 1024, 2) . ' KB<br>';
            echo '<strong>Permisos:</strong> ' . substr(sprintf('%o', fileperms($encontrado)), -4) . '<br>';
            echo '</div>';

            // Mostrar imagen
            $url_relativa = str_replace($base_dir, '', $encontrado);
            echo '<h3>Vista previa:</h3>';
            echo '<img src="' . $url_relativa . '" style="max-width: 300px; border: 1px solid #ddd; padding: 10px; background: white;">';

        } else {
            echo '<div class="error">';
            echo '<strong>❌ ARCHIVO NO ENCONTRADO</strong><br><br>';
            echo 'El archivo no existe en ninguna de las ubicaciones comunes.<br>';
            echo 'Esto significa que la subida falló pero no mostró error.';
            echo '</div>';
        }

        // Listar todos los archivos en uploads/logos/ si existe
        echo '<h2>📁 Contenido de /uploads/logos/</h2>';

        $logos_dir = $base_dir . '/uploads/logos/';

        if (is_dir($logos_dir)) {
            $archivos = scandir($logos_dir);
            $archivos = array_diff($archivos, ['.', '..']);

            if (empty($archivos)) {
                echo '<div class="info">La carpeta está vacía</div>';
            } else {
                echo '<table>';
                echo '<tr><th>Archivo</th><th>Tamaño</th><th>Fecha modificación</th></tr>';

                foreach ($archivos as $archivo) {
                    $ruta_completa = $logos_dir . $archivo;
                    if (is_file($ruta_completa)) {
                        echo '<tr>';
                        echo '<td>' . $archivo . '</td>';
                        echo '<td>' . number_format(filesize($ruta_completa) / 1024, 2) . ' KB</td>';
                        echo '<td>' . date('Y-m-d H:i:s', filemtime($ruta_completa)) . '</td>';
                        echo '</tr>';
                    }
                }

                echo '</table>';
            }
        } else {
            echo '<div class="error">La carpeta /uploads/logos/ no existe</div>';
        }

        // Verificar permisos de PHP
        echo '<h2>ℹ️ Información de Subida de Archivos</h2>';
        echo '<table>';
        echo '<tr><td><strong>upload_max_filesize</strong></td><td>' . ini_get('upload_max_filesize') . '</td></tr>';
        echo '<tr><td><strong>post_max_size</strong></td><td>' . ini_get('post_max_size') . '</td></tr>';
        echo '<tr><td><strong>upload_tmp_dir</strong></td><td>' . (ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) . '</td></tr>';
        echo '<tr><td><strong>max_file_uploads</strong></td><td>' . ini_get('max_file_uploads') . '</td></tr>';
        echo '</table>';

        ?>

        <div class="info" style="margin-top: 30px;">
            <strong>🗑️ ELIMINAR este archivo después de usarlo</strong>
        </div>
    </div>
</body>
</html>
