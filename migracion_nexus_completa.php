<?php
/**
 * MIGRACIÓN COMPLETA: Crear verumax_nexus con tabla miembros
 * y migrar datos desde verumax_certifi.estudiantes
 *
 * Ejecutar via navegador: http://verumax.local/migracion_nexus_completa.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$is_web = php_sapi_name() !== 'cli';

if ($is_web) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Migración Nexus Completa</title>';
    echo '<style>body{font-family:monospace;background:#1a1a2e;color:#eee;padding:20px;line-height:1.6;}
          .ok{color:#0f0;} .err{color:#f00;} .skip{color:#ff0;} .warn{color:#f90;} .info{color:#0ff;}
          h2{color:#0ff;border-bottom:1px solid #0ff;padding-bottom:5px;margin-top:30px;}</style>';
    echo '</head><body><pre>';
}

function logMsg($msg, $type = 'normal') {
    global $is_web;
    if ($is_web) {
        $class = $type;
        if (strpos($msg, '[OK]') !== false) $class = 'ok';
        if (strpos($msg, '[ERROR]') !== false) $class = 'err';
        if (strpos($msg, '[SKIP]') !== false) $class = 'skip';
        if (strpos($msg, '[WARN]') !== false) $class = 'warn';
        if (strpos($msg, '[INFO]') !== false) $class = 'info';
        if (strpos($msg, '===') !== false) $class = 'info';
        echo "<span class='$class'>" . htmlspecialchars($msg) . "</span>\n";
    } else {
        echo $msg . "\n";
    }
    flush();
}

logMsg("╔══════════════════════════════════════════════════════════════╗");
logMsg("║  MIGRACIÓN NEXUS: Crear verumax_nexus con tabla miembros    ║");
logMsg("╚══════════════════════════════════════════════════════════════╝");
logMsg("");

try {
    // Cargar configuración de entorno
    require_once __DIR__ . '/env_loader.php';

    $db_host = env('CERTIFI_DB_HOST', 'localhost');
    $db_user = env('CERTIFI_DB_USER', 'root');
    $db_pass = env('CERTIFI_DB_PASS', '');

    // Conectar sin base de datos específica para poder crear la nueva
    $pdo_root = new PDO(
        "mysql:host=$db_host;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    logMsg("[OK] Conexión establecida como $db_user@$db_host");
    logMsg("");

    // =========================================================================
    // PASO 1: Crear base de datos verumax_nexus
    // =========================================================================
    logMsg("=== PASO 1: Crear base de datos verumax_nexus ===");

    $pdo_root->exec("CREATE DATABASE IF NOT EXISTS verumax_nexus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    logMsg("[OK] Base de datos 'verumax_nexus' creada/verificada");

    // Conectar a la nueva base
    $pdo_nexus = new PDO(
        "mysql:host=$db_host;dbname=verumax_nexus;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    logMsg("[OK] Conectado a verumax_nexus");
    logMsg("");

    // =========================================================================
    // PASO 2: Crear tabla miembros con estructura Nexus
    // =========================================================================
    logMsg("=== PASO 2: Crear tabla miembros ===");

    $sql_miembros = "
    CREATE TABLE IF NOT EXISTS miembros (
        id_miembro INT PRIMARY KEY AUTO_INCREMENT,
        id_instancia INT NOT NULL COMMENT 'FK a verumax_identitas.instancias',

        -- Identificación
        identificador_principal VARCHAR(50) NOT NULL COMMENT 'DNI, CUIT, Pasaporte, etc.',
        tipo_identificador ENUM('DNI', 'CUIT', 'CUIL', 'Pasaporte', 'Otro') DEFAULT 'DNI',

        -- Datos personales
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        nombre_completo VARCHAR(200) GENERATED ALWAYS AS (CONCAT(nombre, ' ', apellido)) STORED,
        email VARCHAR(150),
        telefono VARCHAR(30),
        fecha_nacimiento DATE,
        genero ENUM('M', 'F', 'Otro', 'No especifica') DEFAULT 'No especifica',

        -- Domicilio
        domicilio_calle VARCHAR(200),
        domicilio_numero VARCHAR(20),
        domicilio_piso VARCHAR(10),
        domicilio_depto VARCHAR(10),
        domicilio_ciudad VARCHAR(100),
        domicilio_provincia VARCHAR(100),
        domicilio_codigo_postal VARCHAR(20),
        domicilio_pais VARCHAR(100) DEFAULT 'Argentina',

        -- Estado y tipo
        estado ENUM('Activo', 'Inactivo', 'Suspendido', 'Pendiente') DEFAULT 'Activo',
        tipo_miembro ENUM('Estudiante', 'Docente', 'Socio', 'Cliente', 'Otro') DEFAULT 'Estudiante',

        -- Campos personalizables (para flexibilidad tipo Nexus)
        campo_texto_1 VARCHAR(255),
        campo_texto_2 VARCHAR(255),
        campo_texto_3 VARCHAR(255),
        campo_numero_1 DECIMAL(10,2),
        campo_numero_2 DECIMAL(10,2),
        campo_fecha_1 DATE,
        campo_fecha_2 DATE,
        campo_booleano_1 BOOLEAN DEFAULT FALSE,

        -- Metadata
        fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        notas TEXT,

        -- Índices
        UNIQUE KEY uk_instancia_identificador (id_instancia, identificador_principal),
        INDEX idx_instancia (id_instancia),
        INDEX idx_estado (estado),
        INDEX idx_nombre (apellido, nombre),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    COMMENT='Tabla central de miembros - Gestor Nexus';
    ";

    $pdo_nexus->exec($sql_miembros);
    logMsg("[OK] Tabla 'miembros' creada/verificada");
    logMsg("");

    // =========================================================================
    // PASO 3: Crear tabla de configuración por organización
    // =========================================================================
    logMsg("=== PASO 3: Crear tabla configuracion_nexus ===");

    $sql_config = "
    CREATE TABLE IF NOT EXISTS configuracion_nexus (
        id_config INT PRIMARY KEY AUTO_INCREMENT,
        id_instancia INT NOT NULL UNIQUE,

        -- Terminología personalizada
        etiqueta_miembro VARCHAR(50) DEFAULT 'Miembro' COMMENT 'Estudiante, Socio, Cliente, etc.',
        etiqueta_identificador VARCHAR(50) DEFAULT 'DNI',

        -- Campos activos (JSON)
        campos_activos JSON COMMENT 'Lista de campos habilitados para esta organización',
        campos_requeridos JSON COMMENT 'Lista de campos obligatorios',
        etiquetas_personalizadas JSON COMMENT 'Etiquetas custom para campos',

        -- Configuración visual
        color_primario VARCHAR(7) DEFAULT '#0F52BA',

        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo_nexus->exec($sql_config);
    logMsg("[OK] Tabla 'configuracion_nexus' creada/verificada");
    logMsg("");

    // =========================================================================
    // PASO 4: Migrar datos desde verumax_certifi.estudiantes
    // =========================================================================
    logMsg("=== PASO 4: Migrar datos desde estudiantes ===");

    // Conectar a certifi
    $pdo_certifi = new PDO(
        "mysql:host=$db_host;dbname=verumax_certifi;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Contar estudiantes existentes
    $total = $pdo_certifi->query("SELECT COUNT(*) FROM estudiantes")->fetchColumn();
    logMsg("[INFO] Encontrados $total estudiantes para migrar");

    if ($total > 0) {
        // Verificar si ya hay miembros migrados
        $existentes = $pdo_nexus->query("SELECT COUNT(*) FROM miembros")->fetchColumn();

        if ($existentes > 0) {
            logMsg("[WARN] Ya existen $existentes miembros en la tabla. Migrando solo nuevos...");
        }

        // Migrar estudiantes que no existan ya
        $sql_migrar = "
        INSERT INTO verumax_nexus.miembros
            (id_instancia, identificador_principal, tipo_identificador, nombre, apellido,
             email, telefono, fecha_nacimiento, domicilio_calle, domicilio_ciudad,
             domicilio_provincia, domicilio_pais, estado, tipo_miembro, fecha_alta)
        SELECT
            e.id_instancia,
            e.dni,
            'DNI',
            COALESCE(e.nombre, SUBSTRING_INDEX(e.nombre_completo, ' ', 1)),
            COALESCE(e.apellido, SUBSTRING_INDEX(e.nombre_completo, ' ', -1)),
            e.email,
            e.telefono,
            e.fecha_nacimiento,
            e.domicilio_calle,
            e.domicilio_ciudad,
            e.domicilio_provincia,
            COALESCE(e.domicilio_pais, 'Argentina'),
            COALESCE(e.estado, 'Activo'),
            'Estudiante',
            e.fecha_registro
        FROM verumax_certifi.estudiantes e
        WHERE NOT EXISTS (
            SELECT 1 FROM verumax_nexus.miembros m
            WHERE m.id_instancia = e.id_instancia
            AND m.identificador_principal = e.dni
        )
        ";

        $migrados = $pdo_nexus->exec($sql_migrar);
        logMsg("[OK] $migrados estudiantes migrados a miembros");
    } else {
        logMsg("[INFO] No hay estudiantes para migrar");
    }
    logMsg("");

    // =========================================================================
    // PASO 5: Crear configuración por defecto para instancias existentes
    // =========================================================================
    logMsg("=== PASO 5: Crear configuración por defecto ===");

    $instancias = $pdo_nexus->query("SELECT DISTINCT id_instancia FROM miembros")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($instancias as $id_inst) {
        $stmt = $pdo_nexus->prepare("
            INSERT IGNORE INTO configuracion_nexus (id_instancia, etiqueta_miembro, campos_activos, campos_requeridos)
            VALUES (?, 'Estudiante',
                    '[\"nombre\", \"apellido\", \"identificador_principal\", \"email\", \"telefono\"]',
                    '[\"nombre\", \"apellido\", \"identificador_principal\"]')
        ");
        $stmt->execute([$id_inst]);
    }
    logMsg("[OK] Configuración creada para " . count($instancias) . " instancia(s)");
    logMsg("");

    // =========================================================================
    // PASO 6: Mostrar estructura final
    // =========================================================================
    logMsg("=== PASO 6: Estructura final de miembros ===");
    $stmt = $pdo_nexus->query("DESCRIBE miembros");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $extra = $row['Extra'] ? " [{$row['Extra']}]" : '';
        logMsg("  - {$row['Field']} ({$row['Type']})" . ($row['Null'] === 'NO' ? ' NOT NULL' : '') . $extra);
    }
    logMsg("");

    // Mostrar datos migrados
    logMsg("=== Datos migrados ===");
    $stmt = $pdo_nexus->query("SELECT id_miembro, id_instancia, identificador_principal, nombre_completo, email, estado FROM miembros LIMIT 10");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        logMsg("  #{$row['id_miembro']} | Inst:{$row['id_instancia']} | {$row['identificador_principal']} | {$row['nombre_completo']} | {$row['estado']}");
    }
    logMsg("");

    logMsg("╔══════════════════════════════════════════════════════════════╗");
    logMsg("║          MIGRACIÓN NEXUS COMPLETADA EXITOSAMENTE            ║");
    logMsg("╚══════════════════════════════════════════════════════════════╝");
    logMsg("");
    logMsg("[INFO] Próximos pasos:");
    logMsg("  1. Actualizar Certificatum para leer de verumax_nexus.miembros");
    logMsg("  2. Actualizar el modal de gestión con los nuevos campos");
    logMsg("  3. Configurar .env con credenciales de NEXUS_DB_*");

} catch (PDOException $e) {
    logMsg("[ERROR] " . $e->getMessage());
    exit(1);
}

if ($is_web) {
    echo '</pre>';
    echo '<br><br><a href="/admin/index.php?modulo=certificatum" style="color:#0ff;font-size:18px;">→ Ir al módulo Certificatum</a>';
    echo '</body></html>';
}
