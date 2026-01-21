<?php
/**
 * MIGRACIÓN: Preparar estructura para Academicus/Nexus
 *
 * Este script aplica los cambios necesarios en la base de datos
 * para que la estructura actual sea compatible con la migración futura.
 *
 * Ejecutar: php migracion_preparar_academicus.php
 * O abrir en navegador: http://localhost/appVerumax/migracion_preparar_academicus.php
 */

// Configuración
$host = 'localhost';
$user = 'root';
$pass = '';

echo "===========================================\n";
echo "MIGRACIÓN: Preparar estructura Academicus/Nexus\n";
echo "===========================================\n\n";

try {
    // Conexión a verumax_certifi
    $pdo_certifi = new PDO(
        "mysql:host=$host;dbname=verumax_certifi;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[OK] Conectado a verumax_certifi\n";

    // Conexión a verumax_general (para obtener id_instancia)
    $pdo_general = new PDO(
        "mysql:host=$host;dbname=verumax_general;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[OK] Conectado a verumax_general\n\n";

    // =========================================================================
    // 1. AGREGAR id_instancia A TABLA cursos
    // =========================================================================
    echo "--- PASO 1: Agregar id_instancia a tabla cursos ---\n";

    // Verificar si ya existe la columna
    $stmt = $pdo_certifi->query("SHOW COLUMNS FROM cursos LIKE 'id_instancia'");
    if ($stmt->rowCount() == 0) {
        $pdo_certifi->exec("ALTER TABLE cursos ADD COLUMN id_instancia INT UNSIGNED NULL AFTER id_curso");
        echo "[OK] Columna id_instancia agregada a cursos\n";

        $pdo_certifi->exec("ALTER TABLE cursos ADD INDEX idx_cursos_instancia (id_instancia)");
        echo "[OK] Índice idx_cursos_instancia creado\n";
    } else {
        echo "[SKIP] Columna id_instancia ya existe en cursos\n";
    }

    // =========================================================================
    // 2. AGREGAR id_instancia A TABLA estudiantes
    // =========================================================================
    echo "\n--- PASO 2: Agregar id_instancia a tabla estudiantes ---\n";

    $stmt = $pdo_certifi->query("SHOW COLUMNS FROM estudiantes LIKE 'id_instancia'");
    if ($stmt->rowCount() == 0) {
        $pdo_certifi->exec("ALTER TABLE estudiantes ADD COLUMN id_instancia INT UNSIGNED NULL AFTER id_estudiante");
        echo "[OK] Columna id_instancia agregada a estudiantes\n";

        $pdo_certifi->exec("ALTER TABLE estudiantes ADD INDEX idx_estudiantes_instancia (id_instancia)");
        echo "[OK] Índice idx_estudiantes_instancia creado\n";

        // Migrar datos existentes: convertir slug a id_instancia
        $stmt_instances = $pdo_general->query("SELECT id_instancia, slug FROM instances");
        $instances = $stmt_instances->fetchAll(PDO::FETCH_ASSOC);

        $actualizados = 0;
        foreach ($instances as $inst) {
            $stmt_update = $pdo_certifi->prepare("UPDATE estudiantes SET id_instancia = :id WHERE institucion = :slug");
            $stmt_update->execute(['id' => $inst['id_instancia'], 'slug' => $inst['slug']]);
            $actualizados += $stmt_update->rowCount();
        }
        echo "[OK] $actualizados estudiantes actualizados con id_instancia\n";
    } else {
        echo "[SKIP] Columna id_instancia ya existe en estudiantes\n";
    }

    // =========================================================================
    // 3. CREAR TABLA competencias (separada del curso)
    // =========================================================================
    echo "\n--- PASO 3: Crear tabla competencias ---\n";

    $stmt = $pdo_certifi->query("SHOW TABLES LIKE 'competencias'");
    if ($stmt->rowCount() == 0) {
        $pdo_certifi->exec("
            CREATE TABLE competencias (
                id_competencia INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                id_curso INT UNSIGNED NOT NULL,
                competencia VARCHAR(255) NOT NULL,
                descripcion TEXT NULL,
                orden TINYINT UNSIGNED DEFAULT 1,
                activo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_competencias_curso (id_curso),
                FOREIGN KEY (id_curso) REFERENCES cursos(id_curso) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "[OK] Tabla competencias creada\n";

        // Migrar competencias existentes de competencias_curso a competencias
        // Agrupando por curso (tomando competencias únicas)
        $stmt_migrate = $pdo_certifi->query("
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
            // Resetear orden cuando cambia el curso
            if ($comp['id_curso'] != $curso_anterior) {
                $orden = 0;
                $curso_anterior = $comp['id_curso'];
            }
            $orden++;

            // Verificar si ya existe esta competencia para este curso
            $stmt_check = $pdo_certifi->prepare("
                SELECT COUNT(*) FROM competencias
                WHERE id_curso = :id_curso AND competencia = :competencia
            ");
            $stmt_check->execute(['id_curso' => $comp['id_curso'], 'competencia' => $comp['competencia']]);

            if ($stmt_check->fetchColumn() == 0) {
                $stmt_insert = $pdo_certifi->prepare("
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
        echo "[OK] $insertadas competencias migradas a nueva tabla\n";

        // Renombrar tabla antigua
        $pdo_certifi->exec("RENAME TABLE competencias_curso TO competencias_inscripcion");
        echo "[OK] Tabla competencias_curso renombrada a competencias_inscripcion\n";
    } else {
        echo "[SKIP] Tabla competencias ya existe\n";
    }

    // =========================================================================
    // 4. VERIFICACIÓN FINAL
    // =========================================================================
    echo "\n--- VERIFICACIÓN FINAL ---\n";

    // Mostrar estructura de tablas
    echo "\nEstructura de cursos:\n";
    $stmt = $pdo_certifi->query("DESCRIBE cursos");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\nEstructura de estudiantes:\n";
    $stmt = $pdo_certifi->query("DESCRIBE estudiantes");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\nEstructura de competencias:\n";
    $stmt = $pdo_certifi->query("DESCRIBE competencias");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }

    echo "\n===========================================\n";
    echo "MIGRACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "===========================================\n";

} catch (PDOException $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
