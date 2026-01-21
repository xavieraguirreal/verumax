<?php
/**
 * Migración: Expandir tabla estudiantes para compatibilidad con Nexus
 * Ejecutar una sola vez - Acceder via navegador: http://verumax.local/migracion_estudiantes_nexus.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Detectar si es web o CLI
$is_web = php_sapi_name() !== 'cli';

if ($is_web) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Migración Estudiantes</title>';
    echo '<style>body{font-family:monospace;background:#1a1a2e;color:#0f0;padding:20px;} .ok{color:#0f0;} .err{color:#f00;} .skip{color:#ff0;} .warn{color:#f90;}</style>';
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
        echo "<span class='$class'>" . htmlspecialchars($msg) . "</span>\n";
    } else {
        echo $msg . "\n";
    }
    flush();
}

logMsg("=== MIGRACIÓN: Expandir tabla estudiantes para Nexus ===");
logMsg("");

try {
    // Cargar configuración de entorno para obtener credenciales correctas
    require_once __DIR__ . '/env_loader.php';

    $db_host = env('CERTIFI_DB_HOST', 'localhost');
    $db_user = env('CERTIFI_DB_USER', 'root');
    $db_pass = env('CERTIFI_DB_PASS', '');
    $db_name = env('CERTIFI_DB_NAME', 'verumax_certifi');

    logMsg("Conectando a: $db_name@$db_host con usuario: $db_user");

    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    logMsg("[OK] Conexión establecida");
    logMsg("");

    // Verificar estructura actual
    logMsg("--- Estructura actual de 'estudiantes' ---");
    $stmt = $pdo->query("DESCRIBE estudiantes");
    $columnas_actuales = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $columnas_actuales[] = $row['Field'];
        logMsg("  - {$row['Field']} ({$row['Type']})");
    }
    logMsg("");

    // Lista de columnas a agregar
    $migraciones = [
        'nombre' => "ALTER TABLE estudiantes ADD COLUMN nombre VARCHAR(100) NULL AFTER nombre_completo",
        'apellido' => "ALTER TABLE estudiantes ADD COLUMN apellido VARCHAR(100) NULL AFTER nombre",
        'telefono' => "ALTER TABLE estudiantes ADD COLUMN telefono VARCHAR(30) NULL AFTER email",
        'fecha_nacimiento' => "ALTER TABLE estudiantes ADD COLUMN fecha_nacimiento DATE NULL AFTER telefono",
        'domicilio_calle' => "ALTER TABLE estudiantes ADD COLUMN domicilio_calle VARCHAR(200) NULL",
        'domicilio_ciudad' => "ALTER TABLE estudiantes ADD COLUMN domicilio_ciudad VARCHAR(100) NULL",
        'domicilio_provincia' => "ALTER TABLE estudiantes ADD COLUMN domicilio_provincia VARCHAR(100) NULL",
        'domicilio_pais' => "ALTER TABLE estudiantes ADD COLUMN domicilio_pais VARCHAR(100) DEFAULT 'Argentina'",
        'estado' => "ALTER TABLE estudiantes ADD COLUMN estado ENUM('Activo','Inactivo','Suspendido') DEFAULT 'Activo'",
    ];

    logMsg("--- Ejecutando migraciones ---");
    foreach ($migraciones as $columna => $sql) {
        if (in_array($columna, $columnas_actuales)) {
            logMsg("  [SKIP] Columna '$columna' ya existe, saltando...");
        } else {
            try {
                $pdo->exec($sql);
                logMsg("  [OK] Columna '$columna' agregada correctamente");
            } catch (PDOException $e) {
                logMsg("  [ERROR] agregando '$columna': " . $e->getMessage());
            }
        }
    }

    // Verificar si necesitamos cambiar 'institucion' a 'id_instancia'
    logMsg("");
    logMsg("--- Verificando columna institucion/id_instancia ---");
    if (in_array('institucion', $columnas_actuales) && !in_array('id_instancia', $columnas_actuales)) {
        // Primero agregar id_instancia como INT
        try {
            $pdo->exec("ALTER TABLE estudiantes ADD COLUMN id_instancia INT NULL AFTER id_estudiante");
            logMsg("  [OK] Columna 'id_instancia' agregada");

            // Migrar datos: buscar id_instancia por slug de institucion
            $stmt = $pdo->query("SELECT DISTINCT institucion FROM estudiantes WHERE institucion IS NOT NULL");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $slug = $row['institucion'];
                // Buscar en verumax_identitas.instancias
                try {
                    $id_host = env('IDENTITAS_DB_HOST', 'localhost');
                    $id_user = env('IDENTITAS_DB_USER', 'root');
                    $id_pass = env('IDENTITAS_DB_PASS', '');
                    $id_name = env('IDENTITAS_DB_NAME', 'verumax_identitas');

                    $pdo_identitas = new PDO(
                        "mysql:host=$id_host;dbname=$id_name;charset=utf8mb4",
                        $id_user,
                        $id_pass,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    $stmt2 = $pdo_identitas->prepare("SELECT id_instancia FROM instancias WHERE slug = ?");
                    $stmt2->execute([$slug]);
                    $instancia = $stmt2->fetch(PDO::FETCH_ASSOC);
                    if ($instancia) {
                        $pdo->prepare("UPDATE estudiantes SET id_instancia = ? WHERE institucion = ?")
                            ->execute([$instancia['id_instancia'], $slug]);
                        logMsg("  [OK] Migrados estudiantes de '$slug' a id_instancia={$instancia['id_instancia']}");
                    }
                } catch (PDOException $e) {
                    logMsg("  [WARN] No se pudo conectar a verumax_identitas: " . $e->getMessage());
                }
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                logMsg("  [SKIP] Columna 'id_instancia' ya existe");
            } else {
                logMsg("  [ERROR] " . $e->getMessage());
            }
        }
    } else {
        logMsg("  [SKIP] Ya tiene id_instancia o no tiene institucion");
    }

    // Poblar nombre y apellido desde nombre_completo existente
    logMsg("");
    logMsg("--- Poblando nombre/apellido desde nombre_completo ---");
    $stmt = $pdo->query("SELECT id_estudiante, nombre_completo FROM estudiantes WHERE nombre IS NULL OR apellido IS NULL");
    $actualizados = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $partes = explode(' ', trim($row['nombre_completo']), 2);
        $nombre = $partes[0] ?? '';
        $apellido = $partes[1] ?? '';

        $pdo->prepare("UPDATE estudiantes SET nombre = ?, apellido = ? WHERE id_estudiante = ?")
            ->execute([$nombre, $apellido, $row['id_estudiante']]);
        $actualizados++;
    }
    logMsg("  [OK] $actualizados estudiantes actualizados con nombre/apellido");

    // Mostrar estructura final
    logMsg("");
    logMsg("--- Estructura final de 'estudiantes' ---");
    $stmt = $pdo->query("DESCRIBE estudiantes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        logMsg("  - {$row['Field']} ({$row['Type']})" . ($row['Null'] === 'NO' ? ' NOT NULL' : ''));
    }

    logMsg("");
    logMsg("=== MIGRACION COMPLETADA ===");

} catch (PDOException $e) {
    logMsg("[ERROR] " . $e->getMessage());
    exit(1);
}

if ($is_web) {
    echo '</pre>';
    echo '<br><br><a href="/admin/index.php?modulo=certificatum" style="color:#0ff;font-size:18px;">→ Ir al módulo Certificatum</a>';
    echo '</body></html>';
}
