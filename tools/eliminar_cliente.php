<?php
/**
 * Script de Eliminación de Cliente/Institución
 *
 * USO: php eliminar_cliente.php <slug>
 *
 * IMPORTANTE: Este script elimina TODOS los datos de una institución.
 * Los clientes en producción están protegidos y NO pueden eliminarse.
 *
 * Muestra DETALLE COMPLETO de todo lo que se eliminará antes de confirmar.
 */

// ============================================================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================================================

// Clientes PROTEGIDOS - NO se pueden eliminar
$CLIENTES_PROTEGIDOS = [
    'sajur',
    'liberte',
    'fotosjuan'
];

// Modo CLI únicamente
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde línea de comandos.');
}

// Verificar argumento
if ($argc < 2) {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║           ELIMINADOR DE CLIENTE/INSTITUCIÓN                      ║\n";
    echo "╠══════════════════════════════════════════════════════════════════╣\n";
    echo "║ Uso: php eliminar_cliente.php <slug>                             ║\n";
    echo "║                                                                  ║\n";
    echo "║ Ejemplo: php eliminar_cliente.php pampaformacion                 ║\n";
    echo "║                                                                  ║\n";
    echo "║ CLIENTES PROTEGIDOS (no eliminables):                            ║\n";
    foreach ($CLIENTES_PROTEGIDOS as $protegido) {
        echo "║   - " . str_pad($protegido, 55) . "║\n";
    }
    echo "╚══════════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    exit(1);
}

$slug = strtolower(trim($argv[1]));

// ============================================================================
// VERIFICACIONES DE SEGURIDAD
// ============================================================================

// Verificar cliente protegido
if (in_array($slug, $CLIENTES_PROTEGIDOS)) {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║  ⛔ ERROR: CLIENTE PROTEGIDO                                     ║\n";
    echo "╠══════════════════════════════════════════════════════════════════╣\n";
    echo "║  El cliente '$slug' está en la lista de clientes protegidos.    \n";
    echo "║  NO puede ser eliminado por seguridad.                           ║\n";
    echo "║                                                                  ║\n";
    echo "║  Si realmente necesita eliminarlo, edite este script y          ║\n";
    echo "║  quite el cliente de la lista \$CLIENTES_PROTEGIDOS.             ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    exit(1);
}

// Validar formato de slug
if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
    echo "\n⛔ ERROR: Slug inválido. Solo se permiten letras minúsculas, números, guiones y guiones bajos.\n\n";
    exit(1);
}

// ============================================================================
// CARGAR CONFIGURACIÓN
// ============================================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use VERUMax\Services\DatabaseService;

// ============================================================================
// FUNCIONES AUXILIARES
// ============================================================================

function listarArchivosRecursivo($dir, $prefijo = '') {
    $archivos = [];
    if (!is_dir($dir)) return $archivos;

    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $ruta = "$dir/$item";
        $rutaMostrar = $prefijo . $item;

        if (is_dir($ruta)) {
            $archivos[] = ['tipo' => 'DIR', 'ruta' => $rutaMostrar . '/'];
            $archivos = array_merge($archivos, listarArchivosRecursivo($ruta, $rutaMostrar . '/'));
        } else {
            $size = filesize($ruta);
            $sizeStr = $size < 1024 ? "{$size} B" : round($size/1024, 1) . " KB";
            $archivos[] = ['tipo' => 'FILE', 'ruta' => $rutaMostrar, 'size' => $sizeStr];
        }
    }
    return $archivos;
}

function truncar($str, $len = 40) {
    if (strlen($str) <= $len) return $str;
    return substr($str, 0, $len - 3) . '...';
}

// ============================================================================
// OBTENER INFORMACIÓN DEL CLIENTE
// ============================================================================

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║  ANÁLISIS COMPLETO DE DATOS A ELIMINAR                                       ║\n";
echo "║  Cliente: " . str_pad($slug, 67) . "║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";

$datos_a_eliminar = [];
$id_instancia = null;

try {
    $db = DatabaseService::get('general');
    $dbNexus = DatabaseService::get('nexus');
    $dbAcademi = DatabaseService::get('academicus');
    $dbCertifi = DatabaseService::get('certificatum');

    // =========================================================================
    // 1. INSTANCIA
    // =========================================================================
    echo "\n┌─────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ 1. TABLA: verumax_general.instancias                                        │\n";
    echo "└─────────────────────────────────────────────────────────────────────────────┘\n";

    $stmt = $db->prepare("SELECT * FROM instancias WHERE slug = :slug");
    $stmt->execute([':slug' => $slug]);
    $instancia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($instancia) {
        $id_instancia = $instancia['id_instancia'];
        $datos_a_eliminar['instancias'] = [$instancia];

        echo "  ┌────┬──────────────────────────────────────────────────────────────────────┐\n";
        echo "  │ ID │ " . str_pad($instancia['id_instancia'], 68) . " │\n";
        echo "  ├────┼──────────────────────────────────────────────────────────────────────┤\n";
        echo "  │    │ Nombre: " . str_pad(truncar($instancia['nombre'], 58), 60) . " │\n";
        echo "  │    │ Slug: " . str_pad($instancia['slug'], 62) . " │\n";
        echo "  │    │ Email: " . str_pad(truncar($instancia['email_contacto'] ?? 'N/A', 58), 61) . " │\n";
        echo "  │    │ Creado: " . str_pad($instancia['created_at'] ?? 'N/A', 60) . " │\n";
        echo "  └────┴──────────────────────────────────────────────────────────────────────┘\n";
        echo "  TOTAL: 1 registro\n";
    } else {
        echo "  (No se encontró instancia con slug '$slug')\n";
        echo "  TOTAL: 0 registros\n";
    }

    // =========================================================================
    // 2. ADMINISTRADORES
    // =========================================================================
    echo "\n┌─────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ 2. TABLA: verumax_general.admins                                            │\n";
    echo "└─────────────────────────────────────────────────────────────────────────────┘\n";

    if ($id_instancia) {
        $stmt = $db->prepare("SELECT id_admin, usuario, nombre, email, created_at FROM admins WHERE id_instancia = :id");
        $stmt->execute([':id' => $id_instancia]);
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $datos_a_eliminar['admins'] = $admins;

        if (count($admins) > 0) {
            echo "  ┌────────┬────────────────────┬────────────────────────────┬──────────────────┐\n";
            echo "  │ ID     │ Usuario            │ Nombre                     │ Email            │\n";
            echo "  ├────────┼────────────────────┼────────────────────────────┼──────────────────┤\n";
            foreach ($admins as $admin) {
                printf("  │ %-6s │ %-18s │ %-26s │ %-16s │\n",
                    $admin['id_admin'],
                    truncar($admin['usuario'], 18),
                    truncar($admin['nombre'], 26),
                    truncar($admin['email'] ?? 'N/A', 16)
                );
            }
            echo "  └────────┴────────────────────┴────────────────────────────┴──────────────────┘\n";
        }
        echo "  TOTAL: " . count($admins) . " registros\n";
    } else {
        echo "  (Sin instancia, no hay admins que eliminar)\n";
    }

    // =========================================================================
    // 3. MIEMBROS (estudiantes/docentes)
    // =========================================================================
    echo "\n┌─────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ 3. TABLA: verumax_nexus.miembros                                            │\n";
    echo "└─────────────────────────────────────────────────────────────────────────────┘\n";

    $stmt = $dbNexus->prepare("SELECT id_miembro, dni, nombre, apellido, email, tipo FROM miembros WHERE institucion = :slug ORDER BY tipo, apellido");
    $stmt->execute([':slug' => $slug]);
    $miembros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $datos_a_eliminar['miembros'] = $miembros;

    if (count($miembros) > 0) {
        echo "  ┌────────┬─────────────┬────────────────────────────────┬─────────────┬───────────┐\n";
        echo "  │ ID     │ DNI         │ Nombre Completo                │ Tipo        │ Email     │\n";
        echo "  ├────────┼─────────────┼────────────────────────────────┼─────────────┼───────────┤\n";
        foreach ($miembros as $m) {
            $nombreCompleto = truncar(($m['nombre'] ?? '') . ' ' . ($m['apellido'] ?? ''), 30);
            printf("  │ %-6s │ %-11s │ %-30s │ %-11s │ %-9s │\n",
                $m['id_miembro'],
                $m['dni'] ?? 'N/A',
                $nombreCompleto,
                $m['tipo'] ?? 'N/A',
                truncar($m['email'] ?? 'N/A', 9)
            );
        }
        echo "  └────────┴─────────────┴────────────────────────────────┴─────────────┴───────────┘\n";
    }
    echo "  TOTAL: " . count($miembros) . " registros\n";

    // =========================================================================
    // 4. CURSOS
    // =========================================================================
    echo "\n┌─────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ 4. TABLA: verumax_academi.cursos                                            │\n";
    echo "└─────────────────────────────────────────────────────────────────────────────┘\n";

    if ($id_instancia) {
        $stmt = $dbAcademi->prepare("SELECT id_curso, codigo_curso, nombre_curso, fecha_inicio, estado FROM cursos WHERE id_instancia = :id ORDER BY fecha_inicio DESC");
        $stmt->execute([':id' => $id_instancia]);
        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $datos_a_eliminar['cursos'] = $cursos;

        if (count($cursos) > 0) {
            echo "  ┌────────┬───────────────────┬────────────────────────────────┬────────────┬──────────┐\n";
            echo "  │ ID     │ Código            │ Nombre                         │ Inicio     │ Estado   │\n";
            echo "  ├────────┼───────────────────┼────────────────────────────────┼────────────┼──────────┤\n";
            foreach ($cursos as $c) {
                printf("  │ %-6s │ %-17s │ %-30s │ %-10s │ %-8s │\n",
                    $c['id_curso'],
                    truncar($c['codigo_curso'], 17),
                    truncar($c['nombre_curso'], 30),
                    $c['fecha_inicio'] ?? 'N/A',
                    truncar($c['estado'] ?? 'N/A', 8)
                );
            }
            echo "  └────────┴───────────────────┴────────────────────────────────┴────────────┴──────────┘\n";
        }
        echo "  TOTAL: " . count($cursos) . " registros\n";
    } else {
        echo "  (Sin instancia, no hay cursos que eliminar)\n";
    }

    // =========================================================================
    // 5. INSCRIPCIONES
    // =========================================================================
    echo "\n┌─────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ 5. TABLA: verumax_academi.inscripciones                                     │\n";
    echo "└─────────────────────────────────────────────────────────────────────────────┘\n";

    if ($id_instancia) {
        $stmt = $dbAcademi->prepare("
            SELECT i.id_inscripcion, i.id_curso, i.id_miembro, i.fecha_inscripcion, i.estado,
                   c.codigo_curso, m.dni, m.nombre, m.apellido
            FROM inscripciones i
            LEFT JOIN cursos c ON i.id_curso = c.id_curso
            LEFT JOIN verumax_nexus.miembros m ON i.id_miembro = m.id_miembro
            WHERE i.id_instancia = :id
            ORDER BY i.fecha_inscripcion DESC
        ");
        $stmt->execute([':id' => $id_instancia]);
        $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $datos_a_eliminar['inscripciones'] = $inscripciones;

        if (count($inscripciones) > 0) {
            echo "  ┌────────┬───────────────────┬─────────────┬────────────────────────┬──────────┐\n";
            echo "  │ ID     │ Código Curso      │ DNI         │ Estudiante             │ Estado   │\n";
            echo "  ├────────┼───────────────────┼─────────────┼────────────────────────┼──────────┤\n";
            foreach ($inscripciones as $ins) {
                $nombreEst = truncar(($ins['nombre'] ?? '') . ' ' . ($ins['apellido'] ?? ''), 22);
                printf("  │ %-6s │ %-17s │ %-11s │ %-22s │ %-8s │\n",
                    $ins['id_inscripcion'],
                    truncar($ins['codigo_curso'] ?? 'N/A', 17),
                    $ins['dni'] ?? 'N/A',
                    $nombreEst,
                    truncar($ins['estado'] ?? 'N/A', 8)
                );
            }
            echo "  └────────┴───────────────────┴─────────────┴────────────────────────┴──────────┘\n";
        }
        echo "  TOTAL: " . count($inscripciones) . " registros\n";
    } else {
        echo "  (Sin instancia, no hay inscripciones que eliminar)\n";
    }

    // =========================================================================
    // 6. PARTICIPACIONES DOCENTES
    // =========================================================================
    echo "\n┌─────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ 6. TABLA: verumax_certifi.participaciones_docentes                          │\n";
    echo "└─────────────────────────────────────────────────────────────────────────────┘\n";

    if ($id_instancia) {
        $stmt = $dbCertifi->prepare("
            SELECT p.id_participacion, p.id_curso, p.id_miembro, p.rol, p.estado,
                   c.codigo_curso, m.dni, m.nombre, m.apellido
            FROM participaciones_docentes p
            LEFT JOIN verumax_academi.cursos c ON p.id_curso = c.id_curso
            LEFT JOIN verumax_nexus.miembros m ON p.id_miembro = m.id_miembro
            WHERE p.id_instancia = :id
        ");
        $stmt->execute([':id' => $id_instancia]);
        $participaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $datos_a_eliminar['participaciones_docentes'] = $participaciones;

        if (count($participaciones) > 0) {
            echo "  ┌────────┬───────────────────┬─────────────┬──────────────────────┬────────────┐\n";
            echo "  │ ID     │ Código Curso      │ DNI         │ Docente              │ Rol        │\n";
            echo "  ├────────┼───────────────────┼─────────────┼──────────────────────┼────────────┤\n";
            foreach ($participaciones as $p) {
                $nombreDoc = truncar(($p['nombre'] ?? '') . ' ' . ($p['apellido'] ?? ''), 20);
                printf("  │ %-6s │ %-17s │ %-11s │ %-20s │ %-10s │\n",
                    $p['id_participacion'],
                    truncar($p['codigo_curso'] ?? 'N/A', 17),
                    $p['dni'] ?? 'N/A',
                    $nombreDoc,
                    truncar($p['rol'] ?? 'N/A', 10)
                );
            }
            echo "  └────────┴───────────────────┴─────────────┴──────────────────────┴────────────┘\n";
        }
        echo "  TOTAL: " . count($participaciones) . " registros\n";
    } else {
        echo "  (Sin instancia, no hay participaciones que eliminar)\n";
    }

    // =========================================================================
    // 7. CERTIFICADOS EMITIDOS
    // =========================================================================
    echo "\n┌─────────────────────────────────────────────────────────────────────────────┐\n";
    echo "│ 7. TABLA: verumax_certifi.certificados_emitidos                             │\n";
    echo "└─────────────────────────────────────────────────────────────────────────────┘\n";

    if ($id_instancia) {
        $stmt = $dbCertifi->prepare("
            SELECT id_certificado, codigo_validacion, tipo_documento, fecha_emision, dni_beneficiario
            FROM certificados_emitidos
            WHERE id_instancia = :id
            ORDER BY fecha_emision DESC
        ");
        $stmt->execute([':id' => $id_instancia]);
        $certificados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $datos_a_eliminar['certificados_emitidos'] = $certificados;

        if (count($certificados) > 0) {
            echo "  ┌────────┬──────────────────────┬────────────────────────┬─────────────┬────────────┐\n";
            echo "  │ ID     │ Código Validación    │ Tipo Documento         │ DNI         │ Fecha      │\n";
            echo "  ├────────┼──────────────────────┼────────────────────────┼─────────────┼────────────┤\n";
            foreach ($certificados as $cert) {
                printf("  │ %-6s │ %-20s │ %-22s │ %-11s │ %-10s │\n",
                    $cert['id_certificado'],
                    truncar($cert['codigo_validacion'] ?? 'N/A', 20),
                    truncar($cert['tipo_documento'] ?? 'N/A', 22),
                    $cert['dni_beneficiario'] ?? 'N/A',
                    substr($cert['fecha_emision'] ?? 'N/A', 0, 10)
                );
            }
            echo "  └────────┴──────────────────────┴────────────────────────┴─────────────┴────────────┘\n";
        }
        echo "  TOTAL: " . count($certificados) . " registros\n";
    } else {
        echo "  (Sin instancia, no hay certificados que eliminar)\n";
    }

} catch (Exception $e) {
    echo "\n⛔ ERROR de conexión: " . $e->getMessage() . "\n\n";
    exit(1);
}

// =========================================================================
// 8. ARCHIVOS Y CARPETAS
// =========================================================================
echo "\n┌─────────────────────────────────────────────────────────────────────────────┐\n";
echo "│ 8. CARPETA FÍSICA: /$slug/                                                  \n";
echo "└─────────────────────────────────────────────────────────────────────────────┘\n";

$carpeta = __DIR__ . '/../' . $slug;
$carpeta_existe = is_dir($carpeta);

if ($carpeta_existe) {
    $archivos = listarArchivosRecursivo($carpeta);
    $datos_a_eliminar['archivos'] = $archivos;

    $totalArchivos = 0;
    $totalCarpetas = 0;

    echo "  ┌──────┬─────────────────────────────────────────────────────────────────────┐\n";
    echo "  │ Tipo │ Ruta                                                                │\n";
    echo "  ├──────┼─────────────────────────────────────────────────────────────────────┤\n";

    foreach ($archivos as $archivo) {
        if ($archivo['tipo'] === 'DIR') {
            $totalCarpetas++;
            printf("  │ 📁   │ %-67s │\n", truncar($archivo['ruta'], 67));
        } else {
            $totalArchivos++;
            $info = truncar($archivo['ruta'], 55) . ' (' . $archivo['size'] . ')';
            printf("  │ 📄   │ %-67s │\n", truncar($info, 67));
        }
    }
    echo "  └──────┴─────────────────────────────────────────────────────────────────────┘\n";
    echo "  TOTAL: $totalArchivos archivos en $totalCarpetas carpetas\n";
} else {
    echo "  (La carpeta no existe)\n";
    echo "  TOTAL: 0 archivos\n";
}

// ============================================================================
// RESUMEN TOTAL
// ============================================================================

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║  RESUMEN TOTAL DE ELIMINACIÓN                                                ║\n";
echo "╠══════════════════════════════════════════════════════════════════════════════╣\n";

$totalRegistros = 0;
$resumen = [
    'instancias' => count($datos_a_eliminar['instancias'] ?? []),
    'admins' => count($datos_a_eliminar['admins'] ?? []),
    'miembros' => count($datos_a_eliminar['miembros'] ?? []),
    'cursos' => count($datos_a_eliminar['cursos'] ?? []),
    'inscripciones' => count($datos_a_eliminar['inscripciones'] ?? []),
    'participaciones_docentes' => count($datos_a_eliminar['participaciones_docentes'] ?? []),
    'certificados_emitidos' => count($datos_a_eliminar['certificados_emitidos'] ?? []),
];

foreach ($resumen as $tabla => $cantidad) {
    $totalRegistros += $cantidad;
    $icon = $cantidad > 0 ? '⚠️ ' : '   ';
    echo "║  {$icon}" . str_pad($tabla, 35) . str_pad($cantidad . " registros", 38) . "║\n";
}

echo "╠══════════════════════════════════════════════════════════════════════════════╣\n";
echo "║  TOTAL BD: " . str_pad($totalRegistros . " registros a eliminar", 65) . "║\n";

if ($carpeta_existe) {
    $totalArchivos = count(array_filter($datos_a_eliminar['archivos'] ?? [], fn($a) => $a['tipo'] === 'FILE'));
    echo "║  TOTAL FS: " . str_pad($totalArchivos . " archivos a eliminar", 65) . "║\n";
}

echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";

// ============================================================================
// CONFIRMACIÓN
// ============================================================================

if ($totalRegistros === 0 && !$carpeta_existe) {
    echo "\n  ℹ️  No hay nada que eliminar para '$slug'.\n\n";
    exit(0);
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║  ⚠️  ADVERTENCIA: ESTA ACCIÓN ES IRREVERSIBLE                                ║\n";
echo "║                                                                              ║\n";
echo "║  Todos los datos mostrados arriba serán ELIMINADOS PERMANENTEMENTE.          ║\n";
echo "║  NO hay forma de recuperarlos después de confirmar.                          ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "  Para confirmar, escriba exactamente: ELIMINAR $slug\n";
echo "  (o presione Enter para cancelar)\n";
echo "  > ";

$handle = fopen("php://stdin", "r");
$confirmacion = trim(fgets($handle));

if ($confirmacion !== "ELIMINAR $slug") {
    echo "\n  ❌ Confirmación incorrecta. Operación cancelada.\n\n";
    exit(0);
}

echo "\n  Segunda confirmación: Escriba 'SI ELIMINAR' para proceder\n";
echo "  > ";
$confirmacion2 = trim(fgets($handle));

if ($confirmacion2 !== 'SI ELIMINAR') {
    echo "\n  ❌ Operación cancelada.\n\n";
    exit(0);
}

// ============================================================================
// ELIMINACIÓN
// ============================================================================

echo "\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";
echo "  Iniciando eliminación...\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";

$errores = [];

if ($id_instancia) {
    try {
        // 1. Eliminar certificados emitidos
        echo "\n  [1/7] Eliminando certificados_emitidos...";
        $stmt = $dbCertifi->prepare("DELETE FROM certificados_emitidos WHERE id_instancia = :id");
        $stmt->execute([':id' => $id_instancia]);
        echo " ✓ ({$stmt->rowCount()} registros)\n";

        // 2. Eliminar participaciones docentes
        echo "  [2/7] Eliminando participaciones_docentes...";
        $stmt = $dbCertifi->prepare("DELETE FROM participaciones_docentes WHERE id_instancia = :id");
        $stmt->execute([':id' => $id_instancia]);
        echo " ✓ ({$stmt->rowCount()} registros)\n";

        // 3. Eliminar inscripciones
        echo "  [3/7] Eliminando inscripciones...";
        $stmt = $dbAcademi->prepare("DELETE FROM inscripciones WHERE id_instancia = :id");
        $stmt->execute([':id' => $id_instancia]);
        echo " ✓ ({$stmt->rowCount()} registros)\n";

        // 4. Eliminar cursos
        echo "  [4/7] Eliminando cursos...";
        $stmt = $dbAcademi->prepare("DELETE FROM cursos WHERE id_instancia = :id");
        $stmt->execute([':id' => $id_instancia]);
        echo " ✓ ({$stmt->rowCount()} registros)\n";

        // 5. Eliminar miembros
        echo "  [5/7] Eliminando miembros...";
        $stmt = $dbNexus->prepare("DELETE FROM miembros WHERE institucion = :slug");
        $stmt->execute([':slug' => $slug]);
        echo " ✓ ({$stmt->rowCount()} registros)\n";

        // 6. Eliminar admins
        echo "  [6/7] Eliminando admins...";
        $stmt = $db->prepare("DELETE FROM admins WHERE id_instancia = :id");
        $stmt->execute([':id' => $id_instancia]);
        echo " ✓ ({$stmt->rowCount()} registros)\n";

        // 7. Eliminar instancia
        echo "  [7/7] Eliminando instancia...";
        $stmt = $db->prepare("DELETE FROM instancias WHERE id_instancia = :id");
        $stmt->execute([':id' => $id_instancia]);
        echo " ✓\n";

    } catch (Exception $e) {
        $errores[] = "Error eliminando datos de BD: " . $e->getMessage();
        echo " ✗\n";
    }
}

// Eliminar carpeta
if ($carpeta_existe) {
    echo "\n  [8/8] Eliminando carpeta física...";

    function eliminarDirectorio($dir) {
        if (!is_dir($dir)) return false;

        $archivos = array_diff(scandir($dir), ['.', '..']);
        foreach ($archivos as $archivo) {
            $ruta = "$dir/$archivo";
            if (is_dir($ruta)) {
                eliminarDirectorio($ruta);
            } else {
                unlink($ruta);
            }
        }
        return rmdir($dir);
    }

    if (eliminarDirectorio($carpeta)) {
        echo " ✓\n";
    } else {
        $errores[] = "No se pudo eliminar la carpeta $carpeta";
        echo " ✗\n";
    }
}

// ============================================================================
// RESUMEN FINAL
// ============================================================================

echo "\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";

if (empty($errores)) {
    echo "  ✅ ELIMINACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "════════════════════════════════════════════════════════════════════════════════\n";
    echo "  El cliente '$slug' ha sido eliminado completamente.\n";
} else {
    echo "  ⚠️  ELIMINACIÓN COMPLETADA CON ERRORES\n";
    echo "════════════════════════════════════════════════════════════════════════════════\n";
    foreach ($errores as $error) {
        echo "  - $error\n";
    }
}

echo "\n";
?>
