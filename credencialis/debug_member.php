<?php
/**
 * DEBUG: Verificar por qué no se encuentra el miembro
 * ELIMINAR DESPUÉS DE USAR
 */

require_once __DIR__ . '/config.php';

use VERUMax\Services\DatabaseService;

$institucion = $_GET['inst'] ?? 'sajur';
$dni = $_GET['dni'] ?? '21090771';

echo "<h2>Debug Credencialis - Buscar miembro</h2>";
echo "<p>Institución: <b>$institucion</b></p>";
echo "<p>DNI: <b>$dni</b></p>";
echo "<hr>";

// 1. Buscar id_instancia
echo "<h3>1. Buscar institución en 'instances'</h3>";
try {
    $connGeneral = DatabaseService::get('general');
    $stmt = $connGeneral->prepare("SELECT id_instancia, slug, nombre FROM instances WHERE slug = :slug");
    $stmt->execute([':slug' => $institucion]);
    $instancia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($instancia) {
        echo "<p style='color:green'>✓ Encontrada: id_instancia = <b>{$instancia['id_instancia']}</b>, nombre = {$instancia['nombre']}</p>";
        $id_instancia = $instancia['id_instancia'];
    } else {
        echo "<p style='color:red'>✗ No se encontró institución con slug '$institucion'</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Buscar miembro
echo "<h3>2. Buscar miembro en 'miembros'</h3>";
try {
    $conn = DatabaseService::get('nexus');

    // Primero sin filtro de estado
    $stmt = $conn->prepare("
        SELECT id_miembro, id_instancia, identificador_principal, nombre_completo,
               estado, numero_asociado, tipo_asociado, categoria_servicio, fecha_ingreso
        FROM miembros
        WHERE id_instancia = :id_instancia AND identificador_principal = :dni
    ");
    $stmt->execute([':id_instancia' => $id_instancia, ':dni' => $dni]);
    $miembro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($miembro) {
        echo "<p style='color:green'>✓ Miembro encontrado:</p>";
        echo "<pre>" . print_r($miembro, true) . "</pre>";

        if ($miembro['estado'] !== 'Activo') {
            echo "<p style='color:orange'>⚠ Estado no es 'Activo', es: <b>{$miembro['estado']}</b></p>";
        }

        if (empty($miembro['numero_asociado'])) {
            echo "<p style='color:orange'>⚠ numero_asociado está vacío</p>";
        } else {
            echo "<p style='color:green'>✓ numero_asociado = <b>{$miembro['numero_asociado']}</b></p>";
        }
    } else {
        echo "<p style='color:red'>✗ No se encontró miembro con DNI '$dni' en id_instancia $id_instancia</p>";

        // Ver si existe en otra instancia
        $stmt2 = $conn->prepare("SELECT id_miembro, id_instancia, nombre_completo FROM miembros WHERE identificador_principal = :dni");
        $stmt2->execute([':dni' => $dni]);
        $otros = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        if ($otros) {
            echo "<p>Existe en otras instancias:</p>";
            echo "<pre>" . print_r($otros, true) . "</pre>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

// 3. Test del método getCredentialData
echo "<h3>3. Test MemberService::getCredentialData()</h3>";
try {
    $result = \VERUMax\Services\MemberService::getCredentialData($institucion, $dni);
    if ($result) {
        echo "<p style='color:green'>✓ Método retornó datos:</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    } else {
        echo "<p style='color:red'>✗ Método retornó NULL</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error en método: " . $e->getMessage() . "</p>";
}

// 4. Test manual de la query exacta del método
echo "<h3>4. Test manual de la query del método</h3>";
try {
    $connNexus = DatabaseService::get('nexus');
    $connGeneral = DatabaseService::get('general');

    // Query 1: Buscar instancia
    $stmt1 = $connGeneral->prepare("SELECT id_instancia FROM instances WHERE slug = :slug");
    $stmt1->execute([':slug' => $institucion]);
    $inst = $stmt1->fetch(PDO::FETCH_ASSOC);
    echo "<p>Query instancia: " . ($inst ? "OK, id={$inst['id_instancia']}" : "FALLÓ") . "</p>";

    if ($inst) {
        // Query 2: Buscar miembro con la query exacta del método
        $sql = "
            SELECT
                m.id_miembro,
                m.id_instancia,
                m.identificador_principal,
                m.identificador_principal as dni,
                m.tipo_identificador,
                m.nombre,
                m.apellido,
                COALESCE(m.nombre_completo, CONCAT(m.nombre, ' ', m.apellido)) as nombre_completo,
                m.email,
                m.telefono,
                m.genero,
                m.estado,
                m.foto_url,
                m.numero_asociado,
                m.tipo_asociado,
                m.nombre_entidad,
                m.categoria_servicio,
                m.fecha_ingreso,
                CASE WHEN m.numero_asociado IS NOT NULL AND m.numero_asociado != ''
                     THEN 1 ELSE 0 END as tiene_credencial,
                m.fecha_alta,
                m.fecha_modificacion
            FROM miembros m
            WHERE m.id_instancia = :id_instancia
            AND m.identificador_principal = :dni
            AND m.estado = 'Activo'
        ";

        $stmt2 = $connNexus->prepare($sql);
        $stmt2->execute([':id_instancia' => $inst['id_instancia'], ':dni' => $dni]);
        $miembro = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($miembro) {
            echo "<p style='color:green'>✓ Query manual encontró miembro:</p>";
            echo "<pre>" . print_r($miembro, true) . "</pre>";
        } else {
            echo "<p style='color:red'>✗ Query manual no encontró miembro</p>";
            echo "<p>SQL: " . htmlspecialchars(substr($sql, 0, 200)) . "...</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error en query manual: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
