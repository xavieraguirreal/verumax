<?php
/**
 * VERIFICADOR DE TABLAS EN BASES DE DATOS
 *
 * Este script muestra todas las tablas que existen en cada base de datos
 */

require_once __DIR__ . '/env_loader.php';
use VERUMax\Services\DatabaseService;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificador de Tablas - VERUMax</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #2E7D32; }
        .db-section { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 6px; }
        .db-section h2 { color: #1B5E20; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2E7D32; color: white; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verificador de Tablas - VERUMax</h1>

        <?php
        $databases = [
            'general' => 'verumax_general',
            'certificatum' => 'verumax_certifi',
            'identitas' => 'verumax_identi'
        ];

        foreach ($databases as $key => $db_name) {
            echo "<div class='db-section'>";
            echo "<h2>📊 Base de datos: {$db_name}</h2>";

            try {
                $pdo = DatabaseService::get($key);

                // Obtener todas las tablas
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (count($tables) > 0) {
                    echo "<p class='success'>✅ <strong>" . count($tables) . " tablas encontradas</strong></p>";
                    echo "<table>";
                    echo "<thead><tr><th>Tabla</th><th>Registros</th></tr></thead>";
                    echo "<tbody>";

                    foreach ($tables as $table) {
                        try {
                            $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM `{$table}`");
                            $count = $count_stmt->fetch()['total'];
                            echo "<tr><td>{$table}</td><td>{$count}</td></tr>";
                        } catch (Exception $e) {
                            echo "<tr><td>{$table}</td><td class='error'>Error al contar</td></tr>";
                        }
                    }

                    echo "</tbody></table>";
                } else {
                    echo "<p class='warning'>⚠️ <strong>La base de datos está vacía (sin tablas)</strong></p>";
                    echo "<p>Esto significa que la importación no se realizó o falló.</p>";
                    echo "<p><strong>Solución:</strong></p>";
                    echo "<ol>";
                    echo "<li>Abre <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin local</a></li>";
                    echo "<li>Selecciona la base de datos <code>{$db_name}</code></li>";
                    echo "<li>Ve a la pestaña 'Importar'</li>";
                    echo "<li>Selecciona el archivo SQL correspondiente</li>";
                    echo "<li>Haz clic en 'Continuar'</li>";
                    echo "</ol>";
                }

            } catch (Exception $e) {
                echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
            }

            echo "</div>";
        }
        ?>

        <div class='db-section'>
            <h2>📝 Resumen de archivos SQL necesarios</h2>
            <p>Deberías tener estos 3 archivos exportados desde el servidor remoto:</p>
            <ol>
                <li><code>verumax_general.sql</code></li>
                <li><code>verumax_certifi.sql</code></li>
                <li><code>verumax_identi.sql</code></li>
            </ol>

            <p><strong>Para exportar desde phpMyAdmin remoto:</strong></p>
            <ol>
                <li>Selecciona cada base de datos</li>
                <li>Pestaña "Exportar"</li>
                <li>Método: <strong>Rápido</strong></li>
                <li>Formato: <strong>SQL</strong></li>
                <li>Descargar</li>
            </ol>
        </div>
    </div>
</body>
</html>
