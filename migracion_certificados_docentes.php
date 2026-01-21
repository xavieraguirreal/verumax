<?php
/**
 * MIGRACIÓN: Sistema de certificados para docentes
 *
 * Crea las estructuras necesarias para emitir certificados a docentes/instructores/oradores
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "===========================================\n";
echo "MIGRACIÓN: Certificados para Docentes\n";
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
    // 1. CREAR TABLA participaciones_docente
    // Registra las participaciones de docentes en cursos/cohortes
    // =========================================================================
    echo "--- PASO 1: Crear tabla participaciones_docente ---\n";

    $stmt = $pdo->query("SHOW TABLES LIKE 'participaciones_docente'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("
            CREATE TABLE participaciones_docente (
                id_participacion INT AUTO_INCREMENT PRIMARY KEY,
                id_docente INT NOT NULL,
                id_curso INT NOT NULL,
                id_cohorte INT NULL,
                rol ENUM('docente', 'instructor', 'orador', 'conferencista', 'facilitador', 'tutor', 'coordinador') NOT NULL DEFAULT 'docente',
                titulo_participacion VARCHAR(255) NULL,
                descripcion TEXT NULL,
                fecha_inicio DATE NULL,
                fecha_fin DATE NULL,
                carga_horaria_dictada INT NULL,
                certificado_emitido TINYINT(1) DEFAULT 0,
                fecha_emision_certificado DATETIME NULL,
                codigo_validacion VARCHAR(50) NULL,
                activo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                INDEX idx_part_docente (id_docente),
                INDEX idx_part_curso (id_curso),
                INDEX idx_part_cohorte (id_cohorte),
                INDEX idx_part_codigo (codigo_validacion),
                UNIQUE KEY uk_docente_curso_cohorte (id_docente, id_curso, id_cohorte, rol)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "[OK] Tabla participaciones_docente creada\n";
    } else {
        echo "[SKIP] Tabla participaciones_docente ya existe\n";
    }

    // =========================================================================
    // 2. ACTUALIZAR ENUM de cohorte_docentes para incluir más roles
    // =========================================================================
    echo "\n--- PASO 2: Actualizar roles en cohorte_docentes ---\n";

    try {
        $pdo->exec("
            ALTER TABLE cohorte_docentes
            MODIFY COLUMN rol ENUM('titular', 'adjunto', 'invitado', 'tutor', 'orador', 'conferencista', 'facilitador', 'coordinador') DEFAULT 'adjunto'
        ");
        echo "[OK] Roles actualizados en cohorte_docentes\n";
    } catch (PDOException $e) {
        echo "[INFO] " . $e->getMessage() . "\n";
    }

    // =========================================================================
    // 3. AGREGAR campos de certificado a tabla docentes
    // =========================================================================
    echo "\n--- PASO 3: Verificar campos adicionales en docentes ---\n";

    // Verificar si existe columna institucion (para búsqueda por slug legacy)
    $stmt = $pdo->query("SHOW COLUMNS FROM docentes LIKE 'institucion'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE docentes ADD COLUMN institucion VARCHAR(100) NULL AFTER id_instancia");
        echo "[OK] Columna institucion agregada a docentes\n";
    } else {
        echo "[SKIP] Columna institucion ya existe en docentes\n";
    }

    // =========================================================================
    // 4. VERIFICACIÓN FINAL
    // =========================================================================
    echo "\n--- VERIFICACIÓN FINAL ---\n";

    echo "\nEstructura de participaciones_docente:\n";
    $stmt = $pdo->query("DESCRIBE participaciones_docente");
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
