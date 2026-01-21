<?php
/**
 * Script temporal para explorar estructura de DB
 * ELIMINAR después de crear los modelos Eloquent
 */

require_once __DIR__ . '/config.php';

use VERUMax\Services\DatabaseService;

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = DatabaseService::getConnection();

    echo "=== ESTRUCTURA DE BASE DE DATOS ===\n";
    echo "Database: verumax_certifi\n";
    echo "==========================================\n\n";

    // Obtener todas las tablas
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Total de tablas: " . count($tables) . "\n\n";

    foreach ($tables as $table) {
        echo "\n--- TABLA: $table ---\n";

        // Obtener estructura de cada tabla
        $stmt = $db->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo sprintf("%-30s %-20s %-10s %-10s %-20s\n",
            "Campo", "Tipo", "Null", "Key", "Extra");
        echo str_repeat("-", 90) . "\n";

        foreach ($columns as $column) {
            echo sprintf("%-30s %-20s %-10s %-10s %-20s\n",
                $column['Field'],
                $column['Type'],
                $column['Null'],
                $column['Key'],
                $column['Extra']
            );
        }

        // Contar registros
        $stmt = $db->query("SELECT COUNT(*) as total FROM `$table`");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "\nTotal registros: $count\n";
    }

    echo "\n\n=== FIN EXPLORACIÓN ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
