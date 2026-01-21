<?php
/**
 * SETUP DE BASES DE DATOS LOCALES
 *
 * Este script crea las 3 bases de datos vacías en MySQL local
 * para luego importar los datos desde phpMyAdmin.
 *
 * EJECUTAR: http://localhost/setup_local_databases.php
 */

echo "<h1>Setup de Bases de Datos Locales - VERUMax</h1>";
echo "<p>Este script crea las 3 bases de datos vacías en tu MySQL local.</p>";
echo "<hr>";

// Configuración de conexión local (sin especificar base de datos)
$host = 'localhost';
$usuario = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    // Conectar sin especificar base de datos
    $pdo = new PDO("mysql:host=$host;charset=$charset", $usuario, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p style='color: green;'>✅ Conexión exitosa a MySQL local</p>";

    // Array de bases de datos a crear
    $databases = [
        'verumax_general' => 'Base de datos general (configuración de instancias)',
        'verumax_certifi' => 'Base de datos Certificatum (certificados educativos)',
        'verumax_identi' => 'Base de datos Identitas (presencia digital)'
    ];

    echo "<h2>Creando bases de datos...</h2>";
    echo "<ul>";

    foreach ($databases as $db_name => $descripcion) {
        try {
            // Crear base de datos si no existe
            $sql = "CREATE DATABASE IF NOT EXISTS `{$db_name}`
                    CHARACTER SET utf8mb4
                    COLLATE utf8mb4_unicode_ci";

            $pdo->exec($sql);
            echo "<li style='color: green;'>✅ <strong>{$db_name}</strong> - {$descripcion}</li>";

        } catch (PDOException $e) {
            echo "<li style='color: red;'>❌ <strong>{$db_name}</strong> - Error: " . $e->getMessage() . "</li>";
        }
    }

    echo "</ul>";

    // Verificar bases de datos creadas
    echo "<h2>Bases de datos disponibles:</h2>";
    echo "<ul>";
    $stmt = $pdo->query("SHOW DATABASES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        if (strpos($row[0], 'verumax_') === 0) {
            echo "<li><strong>{$row[0]}</strong></li>";
        }
    }
    echo "</ul>";

    echo "<hr>";
    echo "<h2>Próximos pasos:</h2>";
    echo "<ol>";
    echo "<li><strong>Exportar desde servidor remoto:</strong><br>";
    echo "   Accede a tu phpMyAdmin remoto y exporta las 3 bases de datos en formato SQL:<br>";
    echo "   <ul>";
    echo "     <li>verumax_general.sql</li>";
    echo "     <li>verumax_certifi.sql</li>";
    echo "     <li>verumax_identi.sql</li>";
    echo "   </ul>";
    echo "</li>";
    echo "<li><strong>Importar en local:</strong><br>";
    echo "   Accede a <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin local</a><br>";
    echo "   Selecciona cada base de datos e importa el archivo .sql correspondiente";
    echo "</li>";
    echo "<li><strong>Actualizar archivos de configuración:</strong><br>";
    echo "   Los archivos config.php actuales apuntan al servidor remoto.<br>";
    echo "   Usa <code>config_local.php</code> para desarrollo local.";
    echo "</li>";
    echo "</ol>";

    echo "<hr>";
    echo "<h2>Guía de exportación/importación:</h2>";
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "<h3>En phpMyAdmin REMOTO:</h3>";
    echo "<ol>";
    echo "<li>Selecciona la base de datos verumax_general</li>";
    echo "<li>Haz clic en la pestaña 'Exportar'</li>";
    echo "<li>Método: Rápido</li>";
    echo "<li>Formato: SQL</li>";
    echo "<li>Haz clic en 'Continuar' y guarda el archivo</li>";
    echo "<li>Repite para verumax_certifi y verumax_identi</li>";
    echo "</ol>";

    echo "<h3>En phpMyAdmin LOCAL (http://localhost/phpmyadmin):</h3>";
    echo "<ol>";
    echo "<li>Selecciona la base de datos verumax_general</li>";
    echo "<li>Haz clic en la pestaña 'Importar'</li>";
    echo "<li>Haz clic en 'Seleccionar archivo' y elige verumax_general.sql</li>";
    echo "<li>Haz clic en 'Continuar'</li>";
    echo "<li>Repite para las otras 2 bases de datos</li>";
    echo "</ol>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    echo "<p><strong>Verifica que:</strong></p>";
    echo "<ul>";
    echo "<li>MySQL esté iniciado en XAMPP Control Panel</li>";
    echo "<li>El puerto 3306 no esté ocupado</li>";
    echo "<li>Las credenciales sean correctas (usuario: root, password: vacío)</li>";
    echo "</ul>";
}

?>
