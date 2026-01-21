<?php
/**
 * VERIFICADOR DE PERMISOS - Carpeta Uploads
 *
 * Este script verifica y crea las carpetas necesarias con permisos correctos
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificador de Permisos - Uploads</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #2E7D32; margin-top: 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔍 Verificador de Permisos - Carpeta Uploads</h1>

        <?php
        $base_dir = __DIR__;
        $resultados = [];

        // Carpetas a verificar/crear
        $carpetas = [
            'uploads' => $base_dir . '/uploads',
            'uploads/logos' => $base_dir . '/uploads/logos',
        ];

        echo '<h2>📁 Verificación de Carpetas</h2>';
        echo '<table>';
        echo '<tr><th>Carpeta</th><th>Estado</th><th>Permisos</th><th>Escribible</th></tr>';

        foreach ($carpetas as $nombre => $ruta) {
            $existe = is_dir($ruta);
            $permisos = $existe ? substr(sprintf('%o', fileperms($ruta)), -4) : 'N/A';
            $escribible = $existe && is_writable($ruta);

            echo '<tr>';
            echo '<td><code>/' . $nombre . '</code></td>';

            if ($existe) {
                echo '<td><span style="color: green;">✅ Existe</span></td>';
            } else {
                echo '<td><span style="color: red;">❌ No existe</span></td>';
            }

            echo '<td>' . $permisos . '</td>';

            if ($escribible) {
                echo '<td><span style="color: green;">✅ Escribible</span></td>';
            } else {
                echo '<td><span style="color: red;">❌ No escribible</span></td>';
            }

            echo '</tr>';

            $resultados[$nombre] = [
                'existe' => $existe,
                'escribible' => $escribible,
                'ruta' => $ruta
            ];
        }

        echo '</table>';

        // Intentar crear carpetas faltantes
        echo '<h2>🔧 Corrección Automática</h2>';

        $carpetas_creadas = [];
        $errores_creacion = [];

        foreach ($carpetas as $nombre => $ruta) {
            if (!$resultados[$nombre]['existe']) {
                if (@mkdir($ruta, 0755, true)) {
                    $carpetas_creadas[] = $nombre;
                    echo '<div class="success">✅ Carpeta <strong>/' . $nombre . '</strong> creada correctamente con permisos 0755</div>';
                } else {
                    $errores_creacion[] = $nombre;
                    echo '<div class="error">❌ No se pudo crear <strong>/' . $nombre . '</strong>. Crear manualmente con FileZilla.</div>';
                }
            } elseif (!$resultados[$nombre]['escribible']) {
                if (@chmod($ruta, 0755)) {
                    echo '<div class="success">✅ Permisos de <strong>/' . $nombre . '</strong> actualizados a 0755</div>';
                } else {
                    echo '<div class="error">❌ No se pudieron cambiar permisos de <strong>/' . $nombre . '</strong>. Cambiar manualmente a 0755 con FileZilla.</div>';
                }
            }
        }

        // Verificar archivo .htaccess en uploads
        echo '<h2>🛡️ Seguridad - .htaccess en /uploads/</h2>';

        $htaccess_path = $base_dir . '/uploads/.htaccess';
        $htaccess_existe = file_exists($htaccess_path);

        if ($htaccess_existe) {
            echo '<div class="success">✅ Archivo de seguridad <strong>/uploads/.htaccess</strong> existe</div>';
            echo '<h3>Contenido actual:</h3>';
            echo '<pre>' . htmlspecialchars(file_get_contents($htaccess_path)) . '</pre>';
        } else {
            echo '<div class="warning">⚠️ Archivo <strong>/uploads/.htaccess</strong> NO existe</div>';

            $htaccess_content = "# Prevenir ejecución de PHP en uploads\nphp_flag engine off\n\n# Permitir solo imágenes\n<FilesMatch \"\\.(jpg|jpeg|png|gif|svg|webp|ico)$\">\n    Order Allow,Deny\n    Allow from all\n</FilesMatch>\n\n# Denegar todo lo demás\n<FilesMatch \"^.*$\">\n    Order Deny,Allow\n    Deny from all\n</FilesMatch>";

            if (is_dir($base_dir . '/uploads') && is_writable($base_dir . '/uploads')) {
                if (@file_put_contents($htaccess_path, $htaccess_content)) {
                    echo '<div class="success">✅ Archivo .htaccess creado correctamente en /uploads/</div>';
                } else {
                    echo '<div class="error">❌ No se pudo crear .htaccess. Crear manualmente.</div>';
                }
            }
        }

        // Probar escritura
        echo '<h2>✍️ Prueba de Escritura</h2>';

        $test_file = $base_dir . '/uploads/logos/test-' . time() . '.txt';
        $puede_escribir = false;

        if (is_dir($base_dir . '/uploads/logos') && is_writable($base_dir . '/uploads/logos')) {
            if (@file_put_contents($test_file, 'Test de escritura')) {
                $puede_escribir = true;
                echo '<div class="success">✅ Prueba de escritura EXITOSA en /uploads/logos/</div>';

                // Limpiar archivo de prueba
                @unlink($test_file);
            } else {
                echo '<div class="error">❌ No se puede escribir en /uploads/logos/</div>';
            }
        } else {
            echo '<div class="error">❌ La carpeta /uploads/logos/ no existe o no es escribible</div>';
        }

        // Información del sistema
        echo '<h2>ℹ️ Información del Sistema</h2>';
        echo '<table>';
        echo '<tr><td><strong>Usuario PHP</strong></td><td>' . get_current_user() . '</td></tr>';
        echo '<tr><td><strong>UID</strong></td><td>' . getmyuid() . '</td></tr>';
        echo '<tr><td><strong>GID</strong></td><td>' . getmygid() . '</td></tr>';
        echo '<tr><td><strong>Ruta base</strong></td><td>' . $base_dir . '</td></tr>';
        echo '<tr><td><strong>Upload max filesize</strong></td><td>' . ini_get('upload_max_filesize') . '</td></tr>';
        echo '<tr><td><strong>Post max size</strong></td><td>' . ini_get('post_max_size') . '</td></tr>';
        echo '<tr><td><strong>Temp dir</strong></td><td>' . sys_get_temp_dir() . '</td></tr>';
        echo '</table>';

        // Resumen final
        echo '<h2>📋 Resumen y Próximos Pasos</h2>';

        $todo_ok = true;
        foreach ($resultados as $nombre => $info) {
            if (!$info['existe'] || !$info['escribible']) {
                $todo_ok = false;
                break;
            }
        }

        if ($todo_ok && $puede_escribir) {
            echo '<div class="success">';
            echo '<strong>✅ TODO LISTO</strong><br><br>';
            echo '1. ✅ Todas las carpetas existen<br>';
            echo '2. ✅ Permisos correctos (0755)<br>';
            echo '3. ✅ Carpetas escribibles<br>';
            echo '4. ✅ Prueba de escritura exitosa<br><br>';
            echo '<strong>Puedes subir logos desde el admin sin problemas.</strong>';
            echo '</div>';
        } else {
            echo '<div class="warning">';
            echo '<strong>⚠️ ACCIÓN REQUERIDA</strong><br><br>';

            if (!empty($errores_creacion)) {
                echo '<strong>Crear manualmente con FileZilla:</strong><br>';
                foreach ($errores_creacion as $carpeta) {
                    echo '• /' . $carpeta . ' (permisos: 0755)<br>';
                }
                echo '<br>';
            }

            echo '<strong>Pasos para crear carpetas con FileZilla:</strong><br>';
            echo '1. Conectarse al servidor<br>';
            echo '2. Navegar a la raíz del sitio<br>';
            echo '3. Click derecho > Crear directorio > "uploads"<br>';
            echo '4. Entrar a /uploads/<br>';
            echo '5. Click derecho > Crear directorio > "logos"<br>';
            echo '6. Click derecho en cada carpeta > Permisos > 0755<br>';
            echo '</div>';
        }
        ?>

        <div class="warning" style="margin-top: 30px;">
            <strong>🗑️ ELIMINAR este archivo después de usarlo</strong><br>
            No dejar archivos de diagnóstico en producción.<br><br>
            <strong>Eliminar:</strong> /verificar_permisos_uploads.php
        </div>
    </div>
</body>
</html>
