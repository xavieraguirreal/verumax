<?php
/**
 * PROBATIO - Configuración del Módulo de Evaluaciones
 * Parte de Academicus (VERUMax)
 *
 * Este archivo configura las conexiones y funciones helper
 * para el sistema de evaluaciones.
 */

// Cargar configuración global y servicios
require_once __DIR__ . '/../env_loader.php';

use VERUMax\Services\DatabaseService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;

// =====================================================
// FUNCIONES DE CONEXIÓN
// =====================================================

/**
 * Obtiene conexión PDO a verumax_academi (evaluaciones, cursos, inscripciones)
 */
function getAcademiConnection(): PDO {
    return DatabaseService::get('academicus');
}

/**
 * Obtiene conexión PDO a verumax_nexus (miembros/estudiantes)
 */
function getNexusConnection(): PDO {
    return DatabaseService::get('nexus');
}

/**
 * Obtiene conexión PDO a verumax_general (instancias, configuración global)
 */
function getGeneralConnection(): PDO {
    return DatabaseService::get('general');
}

// =====================================================
// FUNCIONES DE EVALUACIÓN
// =====================================================

/**
 * Obtiene una evaluación por su código
 *
 * @param string $codigo Código de la evaluación (ej: 'EVAL-SAJUR-CORR-2025')
 * @param int|null $id_instancia Filtrar por institución (opcional)
 * @return array|null
 */
function obtenerEvaluacion(string $codigo, ?int $id_instancia = null): ?array {
    $pdo = getAcademiConnection();

    $sql = "SELECT * FROM evaluationes WHERE codigo = :codigo";
    $params = ['codigo' => $codigo];

    if ($id_instancia !== null) {
        $sql .= " AND id_instancia = :id_instancia";
        $params['id_instancia'] = $id_instancia;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ?: null;
}

/**
 * Obtiene una evaluación por ID
 */
function obtenerEvaluacionPorId(int $id_evaluatio): ?array {
    $pdo = getAcademiConnection();
    $stmt = $pdo->prepare("SELECT * FROM evaluationes WHERE id_evaluatio = :id");
    $stmt->execute(['id' => $id_evaluatio]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Obtiene las preguntas de una evaluación ordenadas
 */
function obtenerPreguntas(int $id_evaluatio): array {
    $pdo = getAcademiConnection();
    $stmt = $pdo->prepare("
        SELECT * FROM quaestiones
        WHERE id_evaluatio = :id_evaluatio
        ORDER BY orden ASC
    ");
    $stmt->execute(['id_evaluatio' => $id_evaluatio]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene una pregunta específica
 */
function obtenerPregunta(int $id_quaestio): ?array {
    $pdo = getAcademiConnection();
    $stmt = $pdo->prepare("SELECT * FROM quaestiones WHERE id_quaestio = :id");
    $stmt->execute(['id' => $id_quaestio]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// =====================================================
// FUNCIONES DE SESIÓN DE EVALUACIÓN
// =====================================================

/**
 * Busca o crea una sesión de evaluación para un estudiante
 *
 * @param int $id_evaluatio
 * @param int $id_miembro
 * @param int|null $id_inscripcion
 * @return array Sesión existente o nueva
 */
function obtenerOCrearSesion(int $id_evaluatio, int $id_miembro, ?int $id_inscripcion = null): array {
    $pdo = getAcademiConnection();

    // Buscar sesión existente
    $stmt = $pdo->prepare("
        SELECT * FROM sessiones_probatio
        WHERE id_evaluatio = :id_evaluatio AND id_miembro = :id_miembro
    ");
    $stmt->execute([
        'id_evaluatio' => $id_evaluatio,
        'id_miembro' => $id_miembro
    ]);
    $sesion = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sesion) {
        return $sesion;
    }

    // Contar preguntas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quaestiones WHERE id_evaluatio = :id");
    $stmt->execute(['id' => $id_evaluatio]);
    $total_preguntas = (int) $stmt->fetchColumn();

    // Crear nueva sesión
    $stmt = $pdo->prepare("
        INSERT INTO sessiones_probatio
        (id_evaluatio, id_miembro, id_inscripcion, total_preguntas, ip_address, user_agent)
        VALUES (:id_evaluatio, :id_miembro, :id_inscripcion, :total_preguntas, :ip, :ua)
    ");
    $stmt->execute([
        'id_evaluatio' => $id_evaluatio,
        'id_miembro' => $id_miembro,
        'id_inscripcion' => $id_inscripcion,
        'total_preguntas' => $total_preguntas,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);

    $id_sessio = $pdo->lastInsertId();

    // Retornar la sesión recién creada
    $stmt = $pdo->prepare("SELECT * FROM sessiones_probatio WHERE id_sessio = :id");
    $stmt->execute(['id' => $id_sessio]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Obtiene una sesión por ID
 */
function obtenerSesion(int $id_sessio): ?array {
    $pdo = getAcademiConnection();
    $stmt = $pdo->prepare("SELECT * FROM sessiones_probatio WHERE id_sessio = :id");
    $stmt->execute(['id' => $id_sessio]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Actualiza el progreso de una sesión
 */
function actualizarProgresoSesion(int $id_sessio, array $datos): bool {
    $pdo = getAcademiConnection();

    $campos = [];
    $params = ['id' => $id_sessio];

    $campos_permitidos = [
        'pregunta_actual', 'preguntas_completadas', 'progreso_json',
        'estado', 'puntaje_obtenido', 'puntaje_maximo', 'porcentaje',
        'aprobado', 'reflexion_final', 'fecha_finalizacion'
    ];

    foreach ($datos as $campo => $valor) {
        if (in_array($campo, $campos_permitidos)) {
            $campos[] = "{$campo} = :{$campo}";
            $params[$campo] = $valor;
        }
    }

    if (empty($campos)) {
        return false;
    }

    $sql = "UPDATE sessiones_probatio SET " . implode(', ', $campos) . " WHERE id_sessio = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

// =====================================================
// FUNCIONES DE RESPUESTAS
// =====================================================

/**
 * Registra una respuesta/intento
 */
function registrarRespuesta(
    int $id_sessio,
    int $id_quaestio,
    array $respuestas_seleccionadas,
    bool $es_correcta,
    ?int $tiempo_segundos = null
): int {
    $pdo = getAcademiConnection();

    // Obtener número de intento
    $stmt = $pdo->prepare("
        SELECT COALESCE(MAX(intento_numero), 0) + 1
        FROM responsa
        WHERE id_sessio = :id_sessio AND id_quaestio = :id_quaestio
    ");
    $stmt->execute(['id_sessio' => $id_sessio, 'id_quaestio' => $id_quaestio]);
    $intento = (int) $stmt->fetchColumn();

    // Insertar respuesta
    $stmt = $pdo->prepare("
        INSERT INTO responsa
        (id_sessio, id_quaestio, intento_numero, respuestas_seleccionadas, es_correcta, tiempo_respuesta_segundos)
        VALUES (:id_sessio, :id_quaestio, :intento, :respuestas, :correcta, :tiempo)
    ");
    $stmt->execute([
        'id_sessio' => $id_sessio,
        'id_quaestio' => $id_quaestio,
        'intento' => $intento,
        'respuestas' => json_encode($respuestas_seleccionadas),
        'correcta' => $es_correcta ? 1 : 0,
        'tiempo' => $tiempo_segundos
    ]);

    return (int) $pdo->lastInsertId();
}

/**
 * Obtiene el historial de respuestas para una pregunta en una sesión
 */
function obtenerRespuestasPregunta(int $id_sessio, int $id_quaestio): array {
    $pdo = getAcademiConnection();
    $stmt = $pdo->prepare("
        SELECT * FROM responsa
        WHERE id_sessio = :id_sessio AND id_quaestio = :id_quaestio
        ORDER BY intento_numero ASC
    ");
    $stmt->execute(['id_sessio' => $id_sessio, 'id_quaestio' => $id_quaestio]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// =====================================================
// FUNCIONES DE VALIDACIÓN DE ESTUDIANTE
// =====================================================

/**
 * Busca un estudiante por DNI
 *
 * @param string $dni
 * @param int|null $id_instancia Filtrar por institución
 * @return array|null Datos del miembro o null
 */
function buscarEstudiantePorDNI(string $dni, ?int $id_instancia = null): ?array {
    $pdo = getNexusConnection();

    $sql = "SELECT * FROM miembros WHERE identificador_principal = :dni";
    $params = ['dni' => $dni];

    if ($id_instancia !== null) {
        $sql .= " AND id_instancia = :id_instancia";
        $params['id_instancia'] = $id_instancia;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Verifica si un estudiante está inscripto en un curso
 *
 * @param int $id_miembro
 * @param int $id_curso
 * @return array|null Inscripción o null
 */
function verificarInscripcion(int $id_miembro, int $id_curso): ?array {
    $pdo = getAcademiConnection();
    $stmt = $pdo->prepare("
        SELECT * FROM inscripciones
        WHERE id_miembro = :id_miembro AND id_curso = :id_curso
    ");
    $stmt->execute(['id_miembro' => $id_miembro, 'id_curso' => $id_curso]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// =====================================================
// FUNCIONES DE VERIFICACIÓN DE RESPUESTAS
// =====================================================

/**
 * Verifica si las respuestas seleccionadas son correctas
 *
 * @param array $opciones JSON decodificado de opciones
 * @param array $seleccionadas Letras seleccionadas (ej: ['A', 'C'])
 * @return bool True si todas las correctas están seleccionadas y ninguna incorrecta
 */
function verificarRespuestas(array $opciones, array $seleccionadas): bool {
    $correctas = [];
    $seleccionadas_set = array_flip(array_map('strtoupper', $seleccionadas));

    foreach ($opciones as $opcion) {
        $letra = strtoupper($opcion['letra']);
        $es_correcta = $opcion['es_correcta'] ?? false;

        if ($es_correcta) {
            $correctas[] = $letra;
        }
    }

    // Verificar que seleccionó exactamente las correctas
    $correctas_set = array_flip($correctas);

    // Debe haber seleccionado todas las correctas
    foreach ($correctas as $c) {
        if (!isset($seleccionadas_set[$c])) {
            return false;
        }
    }

    // No debe haber seleccionado ninguna incorrecta
    foreach ($seleccionadas as $s) {
        $s = strtoupper($s);
        if (!isset($correctas_set[$s])) {
            return false;
        }
    }

    return true;
}

// =====================================================
// FUNCIONES HELPER
// =====================================================

/**
 * Obtiene el ID de instancia por código de institución
 * Consulta la tabla instances en verumax_general
 */
function obtenerIdInstancia(string $institucion): ?int {
    static $cache = [];

    $slug = strtolower($institucion);

    // Verificar cache
    if (isset($cache[$slug])) {
        return $cache[$slug];
    }

    try {
        $pdo = getGeneralConnection();
        $stmt = $pdo->prepare("SELECT id_instancia FROM instances WHERE slug = :slug AND activo = 1 LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $id = $row ? (int)$row['id_instancia'] : null;
        $cache[$slug] = $id;

        return $id;
    } catch (PDOException $e) {
        // Si falla la conexión, usar fallback hardcodeado
        $fallback = [
            'sajur' => 1,
            'liberte' => 2,
            'fotosjuan' => 3,
        ];
        return $fallback[$slug] ?? null;
    }
}

/**
 * Responde con JSON y termina la ejecución
 */
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Obtiene parámetro GET sanitizado
 */
function getParam(string $key, $default = null) {
    return isset($_GET[$key]) ? htmlspecialchars(trim($_GET[$key]), ENT_QUOTES, 'UTF-8') : $default;
}

/**
 * Obtiene parámetro POST sanitizado
 */
function postParam(string $key, $default = null) {
    return isset($_POST[$key]) ? htmlspecialchars(trim($_POST[$key]), ENT_QUOTES, 'UTF-8') : $default;
}

/**
 * Obtiene JSON del body de la request
 */
function getJsonBody(): array {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}
