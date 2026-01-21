<?php
/**
 * SCRIPT TEMPORAL - Limpiador de Caché Opcache
 *
 * Este archivo limpia el caché de Opcache del servidor.
 * Usar solo cuando se actualizan archivos PHP y el servidor sigue
 * ejecutando versiones antiguas.
 *
 * IMPORTANTE: ELIMINAR después de usarlo por seguridad.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpiador de Caché Opcache</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2E7D32;
            margin-top: 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔧 Limpiador de Caché Opcache</h1>

        <?php
        // Verificar si Opcache está habilitado
        if (!function_exists('opcache_reset')) {
            echo '<div class="error">';
            echo '<strong>❌ Opcache no está habilitado</strong><br>';
            echo 'El servidor no tiene Opcache activo o no está disponible desde PHP.';
            echo '</div>';
        } else {
            // Mostrar estado antes de limpiar
            echo '<div class="info">';
            echo '<strong>📊 Estado de Opcache antes de limpiar:</strong><br><br>';

            $status = opcache_get_status();
            if ($status) {
                echo '<pre>';
                echo "Memoria usada: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
                echo "Memoria libre: " . round($status['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB\n";
                echo "Archivos en caché: " . $status['opcache_statistics']['num_cached_scripts'] . "\n";
                echo "Hits: " . number_format($status['opcache_statistics']['hits']) . "\n";
                echo "Misses: " . number_format($status['opcache_statistics']['misses']) . "\n";
                echo '</pre>';
            }
            echo '</div>';

            // Limpiar el caché
            $resultado = opcache_reset();

            if ($resultado) {
                echo '<div class="success">';
                echo '<strong>✅ Caché de Opcache limpiado correctamente</strong><br><br>';
                echo 'Todos los archivos PHP en caché han sido eliminados.<br>';
                echo 'El servidor ahora cargará las versiones más recientes de los archivos.';
                echo '</div>';

                // Mostrar estado después de limpiar
                echo '<div class="info">';
                echo '<strong>📊 Estado de Opcache después de limpiar:</strong><br><br>';

                // Esperar un momento para que se actualice el estado
                usleep(100000); // 0.1 segundos

                $status_after = opcache_get_status();
                if ($status_after) {
                    echo '<pre>';
                    echo "Memoria usada: " . round($status_after['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
                    echo "Memoria libre: " . round($status_after['memory_usage']['free_memory'] / 1024 / 1024, 2) . " MB\n";
                    echo "Archivos en caché: " . $status_after['opcache_statistics']['num_cached_scripts'] . "\n";
                    echo '</pre>';
                }
                echo '</div>';

            } else {
                echo '<div class="error">';
                echo '<strong>❌ Error al limpiar el caché</strong><br>';
                echo 'No se pudo limpiar el caché de Opcache. Puede que no tengas permisos suficientes.';
                echo '</div>';
            }
        }
        ?>

        <div class="warning">
            <strong>⚠️ IMPORTANTE - SEGURIDAD</strong><br><br>
            Por seguridad, <strong>DEBES ELIMINAR</strong> este archivo después de usarlo.<br>
            No dejes archivos de diagnóstico accesibles en producción.<br><br>
            <strong>Eliminar:</strong> /limpiar_cache.php
        </div>

        <div class="info">
            <strong>📝 Próximos pasos:</strong><br><br>
            1. ✅ Caché limpiado (si fue exitoso)<br>
            2. 🗑️ Eliminar este archivo (limpiar_cache.php)<br>
            3. 🔄 Recargar tu sitio con Ctrl + Shift + R<br>
            4. ✅ Verificar que los cambios se apliquen correctamente
        </div>

        <p style="text-align: center; color: #666; margin-top: 40px;">
            <small>VERUMax - Sistema de Gestión Multi-tenant</small>
        </p>
    </div>
</body>
</html>
