<?php
/**
 * PROBATIO - Guardado de Progreso (salvare.php)
 *
 * Endpoint para auto-save del progreso.
 * Actualiza la fecha de última actividad.
 *
 * Método: POST
 * Content-Type: application/json
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método no permitido'], 405);
}

$data = getJsonBody();
$id_sessio = (int) ($data['id_sessio'] ?? 0);

if (!$id_sessio) {
    jsonResponse(['error' => 'ID de sesión requerido'], 400);
}

$sesion = obtenerSesion($id_sessio);
if (!$sesion) {
    jsonResponse(['error' => 'Sesión no encontrada'], 404);
}

// La fecha_ultima_actividad se actualiza automáticamente con ON UPDATE
// pero podemos forzar un UPDATE para asegurar
$pdo = getAcademiConnection();
$stmt = $pdo->prepare("
    UPDATE sessiones_probatio
    SET fecha_ultima_actividad = CURRENT_TIMESTAMP
    WHERE id_sessio = :id
");
$stmt->execute(['id' => $id_sessio]);

jsonResponse([
    'success' => true,
    'saved_at' => date('Y-m-d H:i:s')
]);
