<?php
/**
 * MIGRACIÓN: Crear tablas docentes y cohortes
 * Preparación para Academicus/Nexus
 *
 * Ejecutar: C:\xampp\php\php.exe migracion_docentes_cohortes.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "===========================================\n";
echo "MIGRACIÓN: Crear tablas docentes y cohortes\n";
echo "===========================================\n\n";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=verumax_certifi;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[OK] Conectado a verumax_certifi\n\n";

    // =========================================================================
    // 1. CREAR TABLA DOCENTES
    // =========================================================================
    echo "--- PASO 1: Crear tabla docentes ---\n";

    $stmt = $pdo->query("SHOW TABLES LIKE 'docentes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE docentes (
                id_docente INT AUTO_INCREMENT PRIMARY KEY,
                id_instancia INT UNSIGNED NULL COMMENT 'FK a verumax_general.instances',
                dni VARCHAR(20) NOT NULL,
                nombre_completo VARCHAR(255) NOT NULL,
                email VARCHAR(255) NULL,
                telefono VARCHAR(50) NULL,
                especialidad VARCHAR(255) NULL COMMENT 'Área de especialización',
                titulo VARCHAR(255) NULL COMMENT 'Título académico',
                bio TEXT NULL COMMENT 'Biografía corta',
                foto_url VARCHAR(500) NULL,
                activo TINYINT(1) DEFAULT 1,
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                INDEX idx_docentes_instancia (id_instancia),
                INDEX idx_docentes_dni (dni),
                INDEX idx_docentes_activo (activo),
                UNIQUE KEY uk_docente_instancia_dni (id_instancia, dni)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Tabla de docentes - Preparada para Nexus'
        ");
        echo "[OK] Tabla docentes creada\n";
    } else {
        echo "[SKIP] Tabla docentes ya existe\n";
    }

    // =========================================================================
    // 2. CREAR TABLA COHORTES
    // =========================================================================
    echo "\n--- PASO 2: Crear tabla cohortes ---\n";

    $stmt = $pdo->query("SHOW TABLES LIKE 'cohortes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE cohortes (
                id_cohorte INT AUTO_INCREMENT PRIMARY KEY,
                id_curso INT NOT NULL COMMENT 'FK a cursos',
                id_instancia INT UNSIGNED NULL COMMENT 'FK a verumax_general.instances',
                codigo_cohorte VARCHAR(50) NOT NULL COMMENT 'Ej: 2024-A, 2024-B',
                nombre_cohorte VARCHAR(255) NULL COMMENT 'Nombre descriptivo opcional',
                id_docente_titular INT NULL COMMENT 'FK a docentes (docente principal)',
                fecha_inicio DATE NULL,
                fecha_fin DATE NULL,
                cupo_maximo INT UNSIGNED NULL COMMENT 'Límite de inscripciones',
                modalidad ENUM('presencial', 'virtual', 'hibrido') DEFAULT 'presencial',
                horario VARCHAR(255) NULL COMMENT 'Descripción del horario',
                ubicacion VARCHAR(255) NULL COMMENT 'Aula o link de clase virtual',
                estado ENUM('programada', 'en_curso', 'finalizada', 'cancelada') DEFAULT 'programada',
                activo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                INDEX idx_cohortes_curso (id_curso),
                INDEX idx_cohortes_instancia (id_instancia),
                INDEX idx_cohortes_docente (id_docente_titular),
                INDEX idx_cohortes_estado (estado),
                INDEX idx_cohortes_fechas (fecha_inicio, fecha_fin),
                UNIQUE KEY uk_cohorte_curso_codigo (id_curso, codigo_cohorte)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Tabla de cohortes (ediciones de cursos) - Preparada para Academicus'
        ");
        echo "[OK] Tabla cohortes creada\n";
    } else {
        echo "[SKIP] Tabla cohortes ya existe\n";
    }

    // =========================================================================
    // 3. CREAR TABLA COHORTE_DOCENTES (relación muchos a muchos)
    // =========================================================================
    echo "\n--- PASO 3: Crear tabla cohorte_docentes ---\n";

    $stmt = $pdo->query("SHOW TABLES LIKE 'cohorte_docentes'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE cohorte_docentes (
                id_cohorte INT NOT NULL,
                id_docente INT NOT NULL,
                rol ENUM('titular', 'adjunto', 'invitado', 'tutor') DEFAULT 'adjunto',
                fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                PRIMARY KEY (id_cohorte, id_docente),
                INDEX idx_cd_docente (id_docente)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Relación docentes-cohortes (permite múltiples docentes por cohorte)'
        ");
        echo "[OK] Tabla cohorte_docentes creada\n";
    } else {
        echo "[SKIP] Tabla cohorte_docentes ya existe\n";
    }

    // =========================================================================
    // 4. AGREGAR COLUMNA id_cohorte A INSCRIPCIONES (preparación futura)
    // =========================================================================
    echo "\n--- PASO 4: Agregar id_cohorte a inscripciones ---\n";

    $stmt = $pdo->query("SHOW COLUMNS FROM inscripciones LIKE 'id_cohorte'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE inscripciones ADD COLUMN id_cohorte INT NULL AFTER id_curso COMMENT 'FK a cohortes (opcional, para migración futura)'");
        $pdo->exec("ALTER TABLE inscripciones ADD INDEX idx_inscripciones_cohorte (id_cohorte)");
        echo "[OK] Columna id_cohorte agregada a inscripciones\n";
    } else {
        echo "[SKIP] Columna id_cohorte ya existe en inscripciones\n";
    }

    // =========================================================================
    // 5. VERIFICACIÓN FINAL
    // =========================================================================
    echo "\n--- VERIFICACIÓN FINAL ---\n";

    $tablas = ['docentes', 'cohortes', 'cohorte_docentes'];
    foreach ($tablas as $tabla) {
        echo "\nEstructura de $tabla:\n";
        $stmt = $pdo->query("DESCRIBE $tabla");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
            $null = $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $col['Default'] ? " DEFAULT '{$col['Default']}'" : '';
            echo "  - {$col['Field']} ({$col['Type']}) $null$default\n";
        }
    }

    echo "\n===========================================\n";
    echo "MIGRACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "===========================================\n";

} catch (PDOException $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
