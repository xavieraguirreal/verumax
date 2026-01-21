<?php
/**
 * PROBATIO - Verificación de Respuestas (verificare.php)
 */

// Configurar para JSON API
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

// Capturar errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo json_encode(['error' => 'Error interno: ' . $error['message']], JSON_UNESCAPED_UNICODE);
    }
});

require_once __DIR__ . '/config.php';

// Usar jsonResponse de config.php (renombrar para evitar conflictos)
function responder(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(['error' => 'Método no permitido'], 405);
}

// Obtener datos del body
$data = getJsonBody();

$id_sessio = (int) ($data['id_sessio'] ?? 0);
$id_quaestio = (int) ($data['id_quaestio'] ?? 0);
$respuestas = $data['respuestas'] ?? [];

// Validar datos
if (!$id_sessio || !$id_quaestio) {
    responder(['error' => 'Datos incompletos'], 400);
}

if (!is_array($respuestas) || empty($respuestas)) {
    responder(['error' => 'Debés seleccionar al menos una opción'], 400);
}

// Verificar sesión
$sesion = obtenerSesion($id_sessio);
if (!$sesion) {
    responder(['error' => 'Sesión no encontrada'], 404);
}

if ($sesion['estado'] === 'completada') {
    responder(['error' => 'Esta evaluación ya fue completada'], 400);
}

// Obtener pregunta
$pregunta = obtenerPregunta($id_quaestio);
if (!$pregunta) {
    responder(['error' => 'Pregunta no encontrada'], 404);
}

// Verificar que la pregunta pertenece a la evaluación de la sesión
if ((int)$pregunta['id_evaluatio'] !== (int)$sesion['id_evaluatio']) {
    responder(['error' => 'Pregunta no corresponde a esta evaluación'], 400);
}

// Obtener opciones de la pregunta
$opciones = json_decode($pregunta['opciones'], true) ?? [];

// Manejar preguntas de tipo "abierta" (texto libre)
$es_pregunta_abierta = ($pregunta['tipo'] === 'abierta');

if ($es_pregunta_abierta) {
    // Obtener mínimo de caracteres de la configuración de la evaluación
    $evaluacion_config = obtenerEvaluacionPorId($sesion['id_evaluatio']);
    $minimo_caracteres = (int)($evaluacion_config['minimo_caracteres_abierta'] ?? 50);

    $texto_respuesta = trim($respuestas[0] ?? '');
    $longitud = mb_strlen($texto_respuesta);

    if ($minimo_caracteres > 0 && $longitud < $minimo_caracteres) {
        responder([
            'error' => 'Tu respuesta debe tener al menos ' . $minimo_caracteres . ' caracteres. Actualmente tiene ' . $longitud . '.',
            'longitud_actual' => $longitud,
            'longitud_minima' => $minimo_caracteres
        ], 400);
    }

    // Preguntas abiertas: se consideran "correctas" al ser respondidas con suficiente contenido
    // (son de reflexión personal, no requieren evaluación)
    $es_correcta = true;
    $opciones_correctas = [];
} else {
    // Preguntas de opción múltiple
    if (empty($opciones)) {
        responder(['error' => 'Pregunta sin opciones configuradas'], 500);
    }

    // Verificar respuestas
    $es_correcta = verificarRespuestas($opciones, $respuestas);

    // Obtener opciones correctas para feedback visual
    $opciones_correctas = [];
    foreach ($opciones as $op) {
        if ($op['es_correcta'] ?? false) {
            $opciones_correctas[] = strtoupper($op['letra']);
        }
    }
}

// Registrar el intento
$id_responsum = registrarRespuesta(
    $id_sessio,
    $id_quaestio,
    $respuestas,
    $es_correcta,
    null // tiempo (se podría calcular en frontend)
);

// Obtener número de intento actual
$respuestas_previas = obtenerRespuestasPregunta($id_sessio, $id_quaestio);
$intento = count($respuestas_previas);

// Determinar explicación a mostrar
$explicacion = '';

if ($es_pregunta_abierta) {
    // Para preguntas abiertas, usar mensaje de confirmacion
    $explicacion = 'Tu respuesta ha sido registrada. Gracias por tu reflexión.';
} else {
    // Para preguntas de opcion, obtener feedback de las opciones seleccionadas
    $feedbacks = [];
    if (is_array($opciones)) {
        foreach ($respuestas as $letra_seleccionada) {
            if (!is_string($letra_seleccionada)) continue;
            $letra_seleccionada = strtoupper($letra_seleccionada);
            foreach ($opciones as $op) {
                if (!is_array($op)) continue;
                $op_letra = isset($op['letra']) ? strtoupper($op['letra']) : '';
                $op_feedback = $op['feedback'] ?? '';
                if ($op_letra === $letra_seleccionada && !empty($op_feedback)) {
                    $feedbacks[] = '<strong>' . htmlspecialchars($op_letra) . ':</strong> ' . $op_feedback;
                    break;
                }
            }
        }
    }

    // Combinar feedback de todas las opciones seleccionadas
    if (!empty($feedbacks)) {
        $explicacion = implode('<br><br>', $feedbacks);
    } else {
        // Sin feedback por opcion, mensaje generico
        $explicacion = $es_correcta
            ? 'Respuesta correcta.'
            : 'Respuesta incorrecta. Intenta nuevamente.';
    }
}

// Obtener metodología de la evaluación
$evaluacion = obtenerEvaluacionPorId($sesion['id_evaluatio']);
$metodologia = $evaluacion['metodologia'] ?? 'tradicional';

// Determinar si puede avanzar
$puede_avanzar = $es_correcta || $metodologia === 'tradicional';

// Si puede avanzar, actualizar progreso de la sesión
// Solo si la pregunta actual coincide con pregunta_actual de la sesión
if ($puede_avanzar) {
    // Obtener el orden de la pregunta actual
    $orden_pregunta = $pregunta['orden'];

    // Solo actualizar si estamos en la pregunta correcta (no ya avanzamos)
    if ($sesion['pregunta_actual'] == $orden_pregunta) {
        $preguntas = obtenerPreguntas($sesion['id_evaluatio']);
        $total_preguntas = count($preguntas);

        $nueva_pregunta = $orden_pregunta + 1;
        $nuevas_completadas = $sesion['preguntas_completadas'] + 1;

        actualizarProgresoSesion($id_sessio, [
            'pregunta_actual' => min($nueva_pregunta, $total_preguntas + 1),
            'preguntas_completadas' => min($nuevas_completadas, $total_preguntas),
            'estado' => 'en_progreso'
        ]);
    }
}

// Responder
responder([
    'es_correcta' => $es_correcta,
    'explicacion' => $explicacion,
    'intento' => $intento,
    'puede_avanzar' => $puede_avanzar,
    'opciones_correctas' => $opciones_correctas
]);

} catch (Exception $e) {
    responder(['error' => 'Error: ' . $e->getMessage()], 500);
} catch (Error $e) {
    responder(['error' => 'Error fatal: ' . $e->getMessage()], 500);
}
