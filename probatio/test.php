<?php
/**
 * PROBATIO - Test y Reset de Sesiones
 *
 * Uso:
 *   ?sessio=1           - Ver estado de la sesión
 *   ?sessio=1&reset=1   - Resetear sesión (eliminar respuestas, volver a pregunta 1)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';

$id_sessio = (int) ($_GET['sessio'] ?? 0);
$reset = isset($_GET['reset']) && $_GET['reset'] == '1';

if (!$id_sessio) {
    die('<h2>Error: Falta parámetro sessio</h2><p>Uso: ?sessio=ID&reset=1</p>');
}

$pdo = getAcademiConnection();

// Obtener sesión
$stmt = $pdo->prepare("SELECT * FROM sessiones_probatio WHERE id_sessio = :id");
$stmt->execute(['id' => $id_sessio]);
$sesion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sesion) {
    die('<h2>Error: Sesión no encontrada</h2>');
}

// Resetear si se solicita
if ($reset) {
    // Eliminar respuestas
    $stmt = $pdo->prepare("DELETE FROM responsa WHERE id_sessio = :id");
    $stmt->execute(['id' => $id_sessio]);
    $deleted = $stmt->rowCount();

    // Resetear estado de sesión
    $stmt = $pdo->prepare("
        UPDATE sessiones_probatio
        SET pregunta_actual = 1,
            estado = 'en_progreso',
            fecha_finalizacion = NULL,
            puntaje_obtenido = NULL,
            puntaje_maximo = NULL,
            porcentaje = NULL,
            aprobado = NULL,
            reflexion_final = NULL
        WHERE id_sessio = :id
    ");
    $stmt->execute(['id' => $id_sessio]);

    echo "<h2 style='color: green;'>Sesión reseteada</h2>";
    echo "<p>Respuestas eliminadas: {$deleted}</p>";
    echo "<hr>";

    // Recargar sesión
    $stmt = $pdo->prepare("SELECT * FROM sessiones_probatio WHERE id_sessio = :id");
    $stmt->execute(['id' => $id_sessio]);
    $sesion = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Mostrar estado
echo "<h2>Estado de Sesión #{$id_sessio}</h2>";
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
foreach ($sesion as $key => $value) {
    echo "<tr><td><strong>{$key}</strong></td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
}
echo "</table>";

// Contar respuestas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM responsa WHERE id_sessio = :id");
$stmt->execute(['id' => $id_sessio]);
$total_respuestas = $stmt->fetchColumn();

echo "<h3>Respuestas registradas: {$total_respuestas}</h3>";

// Mostrar respuestas
if ($total_respuestas > 0) {
    $stmt = $pdo->prepare("
        SELECT r.*, q.orden, q.tipo
        FROM responsa r
        JOIN quaestiones q ON r.id_quaestio = q.id_quaestio
        WHERE r.id_sessio = :id
        ORDER BY q.orden, r.intento_numero
    ");
    $stmt->execute(['id' => $id_sessio]);
    $respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; font-size: 12px;'>";
    echo "<tr><th>Pregunta</th><th>Tipo</th><th>Intento</th><th>Correcta</th><th>Respuestas</th><th>Fecha</th></tr>";
    foreach ($respuestas as $r) {
        $color = $r['es_correcta'] ? 'green' : 'red';
        echo "<tr>";
        echo "<td>{$r['orden']}</td>";
        echo "<td>{$r['tipo']}</td>";
        echo "<td>{$r['intento_numero']}</td>";
        echo "<td style='color:{$color};'>" . ($r['es_correcta'] ? 'Sí' : 'No') . "</td>";
        echo "<td>" . htmlspecialchars(substr($r['respuestas_seleccionadas'], 0, 50)) . "</td>";
        echo "<td>" . ($r['fecha_respuesta'] ?? '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<p><a href='?sessio={$id_sessio}&reset=1' onclick='return confirm(\"¿Resetear sesión?\")' style='color: red;'>Resetear esta sesión</a></p>";
