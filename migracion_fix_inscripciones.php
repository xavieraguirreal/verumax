<?php
/**
 * Fix: Agregar id_cohorte a inscripciones
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "===========================================\n";
echo "FIX: Agregar id_cohorte a inscripciones\n";
echo "===========================================\n\n";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=verumax_certifi;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[OK] Conectado a verumax_certifi\n\n";

    $stmt = $pdo->query("SHOW COLUMNS FROM inscripciones LIKE 'id_cohorte'");
    if ($stmt->rowCount() == 0) {
        // Sin COMMENT para evitar problemas de sintaxis con MariaDB
        $pdo->exec("ALTER TABLE inscripciones ADD COLUMN id_cohorte INT NULL AFTER id_curso");
        echo "[OK] Columna id_cohorte agregada\n";

        $pdo->exec("ALTER TABLE inscripciones ADD INDEX idx_inscripciones_cohorte (id_cohorte)");
        echo "[OK] Índice creado\n";
    } else {
        echo "[SKIP] Columna id_cohorte ya existe\n";
    }

    echo "\n[OK] Completado\n";

} catch (PDOException $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
