<?php
/**
 * Migración: Mover feedback global a feedback por opción
 *
 * Este script toma explicacion_correcta y explicacion_incorrecta de cada pregunta
 * y los asigna como feedback a las opciones correspondientes.
 */

require_once __DIR__ . '/../probatio/config.php';

echo "=== Migracion de Feedback a Opciones ===\n\n";

try {
    $pdo = getAcademiConnection();

    // Obtener todas las preguntas que tienen feedback global
    $stmt = $pdo->query("
        SELECT id_quaestio, tipo, opciones, explicacion_correcta, explicacion_incorrecta
        FROM quaestiones
        WHERE (explicacion_correcta IS NOT NULL AND explicacion_correcta != '')
           OR (explicacion_incorrecta IS NOT NULL AND explicacion_incorrecta != '')
    ");

    $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Preguntas a migrar: " . count($preguntas) . "\n\n";

    $migradas = 0;
    $errores = 0;

    foreach ($preguntas as $pregunta) {
        $id = $pregunta['id_quaestio'];
        $tipo = $pregunta['tipo'];

        // Saltar preguntas abiertas (no tienen opciones)
        if ($tipo === 'abierta') {
            echo "[$id] Saltando pregunta abierta\n";
            continue;
        }

        // Parsear opciones
        $opciones = json_decode($pregunta['opciones'], true);
        if (!is_array($opciones) || empty($opciones)) {
            echo "[$id] ERROR: No tiene opciones válidas\n";
            $errores++;
            continue;
        }

        $feedback_correcta = $pregunta['explicacion_correcta'] ?? '';
        $feedback_incorrecta = $pregunta['explicacion_incorrecta'] ?? '';

        // Asignar feedback a cada opción
        $modificada = false;
        foreach ($opciones as &$opcion) {
            // Solo asignar si no tiene feedback ya
            if (empty($opcion['feedback'])) {
                if (!empty($opcion['es_correcta']) && !empty($feedback_correcta)) {
                    $opcion['feedback'] = $feedback_correcta;
                    $modificada = true;
                } elseif (empty($opcion['es_correcta']) && !empty($feedback_incorrecta)) {
                    $opcion['feedback'] = $feedback_incorrecta;
                    $modificada = true;
                }
            }
        }
        unset($opcion);

        if ($modificada) {
            // Actualizar en la base de datos
            $stmtUpdate = $pdo->prepare("UPDATE quaestiones SET opciones = :opciones WHERE id_quaestio = :id");
            $stmtUpdate->execute([
                ':opciones' => json_encode($opciones, JSON_UNESCAPED_UNICODE),
                ':id' => $id
            ]);
            echo "[$id] Migrada correctamente\n";
            $migradas++;
        } else {
            echo "[$id] Sin cambios necesarios\n";
        }
    }

    echo "\n=== Resumen ===\n";
    echo "Migradas: $migradas\n";
    echo "Errores: $errores\n";
    echo "Total procesadas: " . count($preguntas) . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
