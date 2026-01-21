<?php
/**
 * MIGRACIÓN PASO 3: Crear tabla competencias
 * Corregido para coincidir con tipo de id_curso
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "===========================================\n";
echo "MIGRACIÓN PASO 3: Crear tabla competencias\n";
echo "===========================================\n\n";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=verumax_certifi;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[OK] Conectado a verumax_certifi\n\n";

    // Verificar tipo de id_curso en tabla cursos
    echo "--- Verificando estructura de cursos ---\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM cursos WHERE Field = 'id_curso'");
    $col_info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Tipo de id_curso: {$col_info['Type']}\n\n";

    // Verificar si tabla competencias ya existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'competencias'");
    if ($stmt->rowCount() > 0) {
        echo "[SKIP] Tabla competencias ya existe\n";
        exit(0);
    }

    // Crear tabla SIN foreign key primero (más compatible)
    echo "--- Creando tabla competencias ---\n";
    $pdo->exec("
        CREATE TABLE competencias (
            id_competencia INT AUTO_INCREMENT PRIMARY KEY,
            id_curso INT NOT NULL,
            competencia VARCHAR(255) NOT NULL,
            descripcion TEXT NULL,
            orden TINYINT UNSIGNED DEFAULT 1,
            activo TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_competencias_curso (id_curso)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "[OK] Tabla competencias creada\n";

    // Verificar si existe tabla competencias_curso
    $stmt = $pdo->query("SHOW TABLES LIKE 'competencias_curso'");
    if ($stmt->rowCount() > 0) {
        // Migrar competencias existentes
        echo "\n--- Migrando competencias existentes ---\n";
        $stmt_migrate = $pdo->query("
            SELECT DISTINCT i.id_curso, cc.competencia, cc.orden
            FROM competencias_curso cc
            INNER JOIN inscripciones i ON cc.id_inscripcion = i.id_inscripcion
            ORDER BY i.id_curso, cc.orden
        ");
        $competencias_existentes = $stmt_migrate->fetchAll(PDO::FETCH_ASSOC);

        $insertadas = 0;
        $curso_anterior = null;
        $orden = 0;

        foreach ($competencias_existentes as $comp) {
            if ($comp['id_curso'] != $curso_anterior) {
                $orden = 0;
                $curso_anterior = $comp['id_curso'];
            }
            $orden++;

            // Verificar si ya existe
            $stmt_check = $pdo->prepare("
                SELECT COUNT(*) FROM competencias
                WHERE id_curso = :id_curso AND competencia = :competencia
            ");
            $stmt_check->execute(['id_curso' => $comp['id_curso'], 'competencia' => $comp['competencia']]);

            if ($stmt_check->fetchColumn() == 0) {
                $stmt_insert = $pdo->prepare("
                    INSERT INTO competencias (id_curso, competencia, orden)
                    VALUES (:id_curso, :competencia, :orden)
                ");
                $stmt_insert->execute([
                    'id_curso' => $comp['id_curso'],
                    'competencia' => $comp['competencia'],
                    'orden' => $orden
                ]);
                $insertadas++;
            }
        }
        echo "[OK] $insertadas competencias migradas\n";

        // Renombrar tabla antigua
        $pdo->exec("RENAME TABLE competencias_curso TO competencias_inscripcion");
        echo "[OK] Tabla competencias_curso renombrada a competencias_inscripcion\n";
    } else {
        echo "[INFO] No existe tabla competencias_curso para migrar\n";
    }

    // Verificación final
    echo "\n--- Estructura de competencias ---\n";
    $stmt = $pdo->query("DESCRIBE competencias");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\n===========================================\n";
    echo "PASO 3 COMPLETADO EXITOSAMENTE\n";
    echo "===========================================\n";

} catch (PDOException $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
