<?php
/**
 * StudentService
 *
 * Servicio para gestionar estudiantes y sus cursos.
 * Encapsula toda la lógica de acceso a datos de estudiantes.
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

use PDO;
use PDOException;

class StudentService
{
    /**
     * Nombre de la base de datos de Certificatum
     */
    private const DB_NAME = 'certificatum';

    /**
     * Obtiene la conexión a la base de datos de Certificatum
     *
     * @return PDO
     */
    private static function db(): PDO
    {
        return DatabaseService::get(self::DB_NAME);
    }

    /**
     * Obtiene la conexión a la base de datos de Nexus
     *
     * @return PDO
     */
    private static function nexusDb(): PDO
    {
        return DatabaseService::get('nexus');
    }

    /**
     * Busca un estudiante por institución y DNI
     * ACTUALIZADO: Ahora busca en verumax_nexus.miembros
     *
     * @param string $institution Código de la institución (slug)
     * @param string $dni DNI del estudiante
     * @return array|null Datos del estudiante o null
     */
    public static function findByDni(string $institution, string $dni): ?array
    {
        try {
            // Obtener id_instancia desde el slug
            $id_instancia = self::getIdInstancia($institution);
            if (!$id_instancia) {
                return null;
            }

            $stmt = self::nexusDb()->prepare("
                SELECT
                    id_miembro,
                    id_miembro as id_estudiante,  -- Alias para compatibilidad
                    identificador_principal as dni,
                    nombre,
                    apellido,
                    CONCAT(nombre, ' ', apellido) as nombre_completo,
                    email,
                    telefono,
                    estado,
                    tipo_miembro,
                    genero,
                    fecha_alta as fecha_registro
                FROM miembros
                WHERE id_instancia = :id_instancia
                AND identificador_principal = :dni
                LIMIT 1
            ");
            $stmt->execute([
                ':id_instancia' => $id_instancia,
                ':dni' => $dni
            ]);

            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            return $student ?: null;

        } catch (PDOException $e) {
            error_log("Error buscando estudiante: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene id_instancia desde un slug de institución
     *
     * @param string $slug
     * @return int|null
     */
    private static function getIdInstancia(string $slug): ?int
    {
        static $cache = [];

        if (isset($cache[$slug])) {
            return $cache[$slug];
        }

        try {
            $conn = DatabaseService::get('general');
            $stmt = $conn->prepare("SELECT id_instancia FROM instances WHERE slug = :slug");
            $stmt->execute([':slug' => $slug]);
            $id = $stmt->fetchColumn();
            $cache[$slug] = $id ?: null;
            return $cache[$slug];
        } catch (PDOException $e) {
            error_log("Error obteniendo id_instancia: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene todos los cursos de un estudiante Y sus participaciones como docente
     *
     * @param string $institution Código de la institución
     * @param string $dni DNI del estudiante/docente
     * @return array|null Array con nombre, cursos como estudiante y participaciones como docente
     */
    public static function getCourses(string $institution, string $dni): ?array
    {
        try {
            // Buscar como estudiante
            $student = self::findByDni($institution, $dni);

            // Buscar como docente
            $docente = self::findDocenteByDni($institution, $dni);

            // Si no existe ni como estudiante ni como docente, retornar null
            if (!$student && !$docente) {
                return null;
            }

            $courses = [];
            $participaciones = [];
            $nombre_completo = '';
            $genero = 'Prefiero no especificar';

            // Obtener cursos como estudiante
            if ($student) {
                $courses = self::fetchStudentCourses($student['id_estudiante']);
                $nombre_completo = $student['nombre_completo'];
                $genero = $student['genero'] ?? 'Prefiero no especificar';
            }

            // Obtener participaciones como docente
            if ($docente) {
                $participaciones = self::fetchDocenteParticipaciones($docente['id_miembro']);
                // Si no tenemos nombre del estudiante, usar el del docente
                if (empty($nombre_completo)) {
                    $nombre_completo = $docente['nombre_completo'];
                }
                // Si no tenemos género del estudiante, usar el del docente
                if ($genero === 'Prefiero no especificar' && !empty($docente['genero'])) {
                    $genero = $docente['genero'];
                }
            }

            return [
                'nombre_completo' => $nombre_completo,
                'genero' => $genero,
                'cursos' => $courses,
                'participaciones_docente' => $participaciones,
                'es_estudiante' => $student !== null,
                'es_docente' => $docente !== null,
                'id_estudiante' => $student['id_estudiante'] ?? null,
                'id_docente' => $docente['id_miembro'] ?? null
            ];

        } catch (PDOException $e) {
            error_log("Error obteniendo cursos del estudiante/docente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un curso específico de un estudiante
     *
     * @param string $institution Código de la institución
     * @param string $dni DNI del estudiante
     * @param string $courseCode Código del curso
     * @return array|null Datos del curso o null
     */
    public static function getCourse(string $institution, string $dni, string $courseCode): ?array
    {
        $data = self::getCourses($institution, $dni);

        if (!$data || !isset($data['cursos'][$courseCode])) {
            return null;
        }

        return [
            'nombre_completo' => $data['nombre_completo'],
            'genero' => $data['genero'] ?? 'Prefiero no especificar',
            'curso' => $data['cursos'][$courseCode]
        ];
    }

    /**
     * Diagnostica por qué un certificado no es válido
     *
     * Retorna información detallada sobre el estado de la inscripción/curso
     *
     * @param string $institution Código de la institución
     * @param string $dni DNI del estudiante
     * @param string $courseCode Código del curso
     * @return array ['valido' => bool, 'motivo' => string, 'datos' => array|null]
     */
    public static function diagnosticarCertificado(string $institution, string $dni, string $courseCode): array
    {
        try {
            $db = self::db();

            // 1. Verificar si el estudiante existe
            $student = self::findByDni($institution, $dni);
            if (!$student) {
                return [
                    'valido' => false,
                    'motivo' => 'estudiante_no_encontrado',
                    'mensaje' => 'El estudiante no está registrado en el sistema',
                    'datos' => null
                ];
            }

            // 2. Buscar el curso (incluyendo inactivos)
            $pdo_academi = DatabaseService::get('academicus');
            $id_instancia = self::getIdInstancia($institution);

            $stmtCurso = $pdo_academi->prepare("
                SELECT id_curso, codigo_curso, nombre_curso, activo
                FROM cursos
                WHERE id_instancia = :id_instancia AND codigo_curso = :codigo_curso
            ");
            $stmtCurso->execute([
                ':id_instancia' => $id_instancia,
                ':codigo_curso' => $courseCode
            ]);
            $curso = $stmtCurso->fetch(\PDO::FETCH_ASSOC);

            if (!$curso) {
                return [
                    'valido' => false,
                    'motivo' => 'curso_no_encontrado',
                    'mensaje' => 'El curso no existe en el sistema',
                    'datos' => null
                ];
            }

            // 3. Verificar si el curso está activo
            if (!$curso['activo']) {
                return [
                    'valido' => false,
                    'motivo' => 'curso_deshabilitado',
                    'mensaje' => 'Este curso ha sido deshabilitado por la institución',
                    'datos' => [
                        'nombre_curso' => $curso['nombre_curso'],
                        'nombre_estudiante' => $student['nombre_completo']
                    ]
                ];
            }

            // 4. Buscar la inscripción (incluyendo inactivas)
            $stmtInsc = $pdo_academi->prepare("
                SELECT i.*, c.nombre_curso
                FROM inscripciones i
                INNER JOIN cursos c ON i.id_curso = c.id_curso
                WHERE i.id_miembro = :id_miembro AND i.id_curso = :id_curso
            ");
            $stmtInsc->execute([
                ':id_miembro' => $student['id_estudiante'],
                ':id_curso' => $curso['id_curso']
            ]);
            $inscripcion = $stmtInsc->fetch(\PDO::FETCH_ASSOC);

            if (!$inscripcion) {
                return [
                    'valido' => false,
                    'motivo' => 'inscripcion_no_encontrada',
                    'mensaje' => 'El estudiante no está inscrito en este curso',
                    'datos' => [
                        'nombre_curso' => $curso['nombre_curso'],
                        'nombre_estudiante' => $student['nombre_completo']
                    ]
                ];
            }

            // 5. Verificar si la inscripción está activa
            if (!$inscripcion['activo']) {
                return [
                    'valido' => false,
                    'motivo' => 'inscripcion_cancelada',
                    'mensaje' => 'La inscripción del estudiante en este curso ha sido cancelada',
                    'datos' => [
                        'nombre_curso' => $curso['nombre_curso'],
                        'nombre_estudiante' => $student['nombre_completo']
                    ]
                ];
            }

            // 6. Todo válido
            return [
                'valido' => true,
                'motivo' => 'valido',
                'mensaje' => 'Certificado válido',
                'datos' => [
                    'nombre_curso' => $curso['nombre_curso'],
                    'nombre_estudiante' => $student['nombre_completo'],
                    'estado_inscripcion' => $inscripcion['estado']
                ]
            ];

        } catch (\PDOException $e) {
            error_log("Error diagnosticando certificado: " . $e->getMessage());
            return [
                'valido' => false,
                'motivo' => 'error_sistema',
                'mensaje' => 'Error al verificar el certificado',
                'datos' => null
            ];
        }
    }

    /**
     * Verifica si un estudiante existe
     *
     * @param string $institution Código de la institución
     * @param string $dni DNI del estudiante
     * @return bool
     */
    public static function exists(string $institution, string $dni): bool
    {
        return self::findByDni($institution, $dni) !== null;
    }

    /**
     * Obtiene el nombre completo de un estudiante
     *
     * @param string $institution Código de la institución
     * @param string $dni DNI del estudiante
     * @return string|null
     */
    public static function getName(string $institution, string $dni): ?string
    {
        $student = self::findByDni($institution, $dni);
        return $student ? $student['nombre_completo'] : null;
    }

    /**
     * Cuenta los cursos de un estudiante
     *
     * @param string $institution Código de la institución
     * @param string $dni DNI del estudiante
     * @return int
     */
    public static function countCourses(string $institution, string $dni): int
    {
        $data = self::getCourses($institution, $dni);
        return $data ? count($data['cursos']) : 0;
    }

    /**
     * Cuenta cursos aprobados de un estudiante
     *
     * @param string $institution Código de la institución
     * @param string $dni DNI del estudiante
     * @return int
     */
    public static function countApprovedCourses(string $institution, string $dni): int
    {
        $data = self::getCourses($institution, $dni);
        if (!$data) return 0;

        $count = 0;
        foreach ($data['cursos'] as $course) {
            if ($course['estado'] === 'Aprobado') {
                $count++;
            }
        }
        return $count;
    }

    // =========================================================================
    // MÉTODOS PRIVADOS
    // =========================================================================

    /**
     * Obtiene los cursos de un estudiante por su ID
     * ACTUALIZADO: Usa id_miembro en lugar de id_estudiante
     *
     * @param int $studentId ID del miembro (id_miembro)
     * @return array Cursos indexados por código
     */
    private static function fetchStudentCourses(int $studentId): array
    {
        $db = self::db();

        // ACTUALIZADO: inscripciones y cursos ahora están en verumax_academi
        // NOTA: No filtramos por c.activo para permitir ver certificados de cursos desactivados
        // Los cursos desactivados solo se ocultan para nuevas inscripciones, no para historial
        $stmt = $db->prepare("
            SELECT
                c.id_curso,
                c.codigo_curso,
                c.nombre_curso,
                c.carga_horaria,
                c.fecha_inicio as curso_fecha_inicio,
                c.fecha_fin as curso_fecha_fin,
                c.firmante_1_nombre,
                c.firmante_1_cargo,
                c.firmante_2_nombre,
                c.firmante_2_cargo,
                c.usar_firmante_1,
                c.usar_firmante_2,
                c.firmante_1_firma_url,
                c.firmante_2_firma_url,
                c.usar_demora_global,
                c.demora_certificado_horas,
                c.demora_tipo,
                c.demora_fecha,
                c.ciudad_emision,
                i.id_inscripcion,
                i.estado,
                i.fecha_inscripcion,
                i.fecha_inicio,
                i.fecha_finalizacion,
                i.nota_final,
                i.asistencia_porcentaje as asistencia,
                i.certificado_disponible_desde,
                i.certificado_emitido,
                i.fecha_emision_certificado
            FROM verumax_academi.inscripciones i
            INNER JOIN verumax_academi.cursos c ON i.id_curso = c.id_curso
            WHERE i.id_miembro = :id_miembro
            AND i.activo = 1
            ORDER BY i.fecha_inscripcion DESC
        ");
        $stmt->execute([':id_miembro' => $studentId]);

        $courses = [];
        while ($course = $stmt->fetch()) {
            $courseCode = $course['codigo_curso'];
            $enrollmentId = $course['id_inscripcion'];
            $courseId = $course['id_curso'];

            // Obtener competencias (de inscripción o del curso)
            $competencies = self::fetchCompetencies($enrollmentId, $courseId);

            // Obtener trayectoria
            $timeline = self::fetchTimeline($enrollmentId);

            // Formatear fechas (usar fechas de inscripción, fallback a fechas del curso)
            $fechaInicioRaw = !empty($course['fecha_inicio'])
                ? $course['fecha_inicio']
                : $course['curso_fecha_inicio'];
            $fechaFinRaw = !empty($course['fecha_finalizacion'])
                ? $course['fecha_finalizacion']
                : $course['curso_fecha_fin'];

            $endDate = self::formatEndDate($fechaFinRaw, $course['estado']);
            $startDate = !empty($fechaInicioRaw)
                ? date('d/m/Y', strtotime($fechaInicioRaw))
                : null;

            $courses[$courseCode] = [
                'id_inscripcion' => $enrollmentId,
                'nombre_curso' => $course['nombre_curso'],
                'estado' => $course['estado'],
                'carga_horaria' => $course['carga_horaria'],
                'fecha_inicio' => $startDate,
                'fecha_finalizacion' => $endDate,
                'nota_final' => $course['nota_final']
                    ? number_format($course['nota_final'], 2)
                    : 'N/A',
                'asistencia' => $course['asistencia'] ?? 'N/A',
                'competencias' => $competencies,
                'trayectoria' => $timeline,
                'certificado_disponible_desde' => $course['certificado_disponible_desde'] ?? null,
                'firmante_1_nombre' => $course['firmante_1_nombre'] ?? null,
                'firmante_1_cargo' => $course['firmante_1_cargo'] ?? null,
                'firmante_2_nombre' => $course['firmante_2_nombre'] ?? null,
                'firmante_2_cargo' => $course['firmante_2_cargo'] ?? null,
                'usar_firmante_1' => $course['usar_firmante_1'] ?? 1,
                'usar_firmante_2' => $course['usar_firmante_2'] ?? 1,
                'firmante_1_firma_url' => $course['firmante_1_firma_url'] ?? null,
                'firmante_2_firma_url' => $course['firmante_2_firma_url'] ?? null,
                // Campos para cálculo de demora en tiempo real
                'usar_demora_global' => $course['usar_demora_global'] ?? 1,
                'demora_certificado_horas' => $course['demora_certificado_horas'] ?? null,
                'demora_tipo' => $course['demora_tipo'] ?? 'inmediato',
                'demora_fecha' => $course['demora_fecha'] ?? null,
                // Fechas raw para documentos (formato Y-m-d)
                // Fechas de inscripción (cuando el estudiante cursó)
                'fecha_finalizacion_raw' => $fechaFinRaw ?? null,
                'fecha_inscripcion_raw' => $course['fecha_inscripcion'] ?? null,
                'fecha_inicio_raw' => $fechaInicioRaw ?? null,
                // Fechas del curso (cuando se dictó oficialmente)
                'curso_fecha_inicio_raw' => $course['curso_fecha_inicio'] ?? null,
                'curso_fecha_fin_raw' => $course['curso_fecha_fin'] ?? null,
                // Ciudad de emisión (para lugar_fecha)
                'ciudad_emision' => $course['ciudad_emision'] ?? null,
                // Estado de emisión del certificado
                'certificado_emitido' => $course['certificado_emitido'] ?? 0,
                'fecha_emision_certificado' => $course['fecha_emision_certificado'] ?? null
            ];
        }

        return $courses;
    }

    /**
     * Obtiene las competencias de una inscripción
     * Primero intenta de competencias_inscripcion, si no existe busca en competencias del curso
     *
     * @param int $enrollmentId ID de inscripción
     * @param int $courseId ID del curso (para buscar competencias del curso)
     * @return array
     */
    private static function fetchCompetencies(int $enrollmentId, ?int $courseId = null): array
    {
        $db = self::db();

        // ACTUALIZADO: competencias ahora están en verumax_academi
        // Primero intentar obtener competencias específicas de la inscripción
        try {
            $stmt = $db->prepare("
                SELECT competencia FROM verumax_academi.competencias_inscripcion
                WHERE id_inscripcion = :id_inscripcion
                ORDER BY orden ASC
            ");
            $stmt->execute([':id_inscripcion' => $enrollmentId]);
            $competencias = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($competencias)) {
                return $competencias;
            }
        } catch (PDOException $e) {
            // Tabla puede no existir todavía, continuar
        }

        // Si no hay competencias de inscripción, buscar las del curso
        if ($courseId) {
            try {
                $stmt = $db->prepare("
                    SELECT competencia FROM verumax_academi.competencias
                    WHERE id_curso = :id_curso AND activo = 1
                    ORDER BY orden ASC
                ");
                $stmt->execute([':id_curso' => $courseId]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                // Tabla puede no existir todavía
            }
        }

        return [];
    }

    /**
     * Obtiene la trayectoria de una inscripción
     * Si no hay eventos manuales, genera eventos automáticos basados en las fechas de la inscripción
     *
     * @param int $enrollmentId ID de inscripción
     * @return array
     */
    private static function fetchTimeline(int $enrollmentId): array
    {
        // ACTUALIZADO: trayectoria ahora está en verumax_academi
        // Envuelto en try-catch porque la tabla puede no existir
        $manualTimeline = [];

        try {
            $stmt = self::db()->prepare("
                SELECT fecha, evento, detalle FROM verumax_academi.trayectoria
                WHERE id_inscripcion = :id_inscripcion
                ORDER BY orden ASC, fecha ASC
            ");
            $stmt->execute([':id_inscripcion' => $enrollmentId]);

            while ($event = $stmt->fetch()) {
                $manualTimeline[] = [
                    'fecha' => $event['fecha']
                        ? date('d/m/Y', strtotime($event['fecha']))
                        : '',
                    'evento' => $event['evento'],
                    'detalle' => $event['detalle']
                ];
            }
        } catch (PDOException $e) {
            // Tabla puede no existir todavía
        }

        // Siempre generar eventos automáticos
        $autoTimeline = self::generateAutoTimeline($enrollmentId);

        // Si no hay eventos manuales, retornar solo automáticos
        if (empty($manualTimeline)) {
            return $autoTimeline;
        }

        // Combinar: eventos automáticos + manuales (evitando duplicados)
        // Crear lista de tipos de eventos manuales para evitar duplicar
        $eventosManualTipos = [];
        foreach ($manualTimeline as $evento) {
            // Normalizar nombres de eventos para comparación
            $tipoNormalizado = self::normalizarTipoEvento($evento['evento']);
            $eventosManualTipos[$tipoNormalizado] = true;
        }

        // Agregar eventos automáticos que no estén ya en los manuales
        $combinedTimeline = [];
        foreach ($autoTimeline as $evento) {
            $tipoNormalizado = self::normalizarTipoEvento($evento['evento']);
            if (!isset($eventosManualTipos[$tipoNormalizado])) {
                $combinedTimeline[] = $evento;
            }
        }

        // Agregar eventos manuales
        foreach ($manualTimeline as $evento) {
            $combinedTimeline[] = $evento;
        }

        // Ordenar por fecha
        usort($combinedTimeline, function($a, $b) {
            $fechaA = !empty($a['fecha']) ? strtotime(str_replace('/', '-', $a['fecha'])) : 0;
            $fechaB = !empty($b['fecha']) ? strtotime(str_replace('/', '-', $b['fecha'])) : 0;
            return $fechaA - $fechaB;
        });

        return $combinedTimeline;
    }

    /**
     * Normaliza el tipo de evento para comparación
     */
    private static function normalizarTipoEvento(string $evento): string
    {
        $evento = mb_strtolower($evento, 'UTF-8');
        // Mapear variantes al mismo tipo
        $mapeo = [
            'inicio' => 'inicio',
            'inicio del curso' => 'inicio',
            'fecha de inicio' => 'inicio',
            'finalización' => 'finalizacion',
            'finalización del curso' => 'finalizacion',
            'fecha de finalización' => 'finalizacion',
            'fecha de finalización registrada' => 'finalizacion',
            'inscripción' => 'inscripcion',
            'inscripción al curso' => 'inscripcion',
            'preinscripción' => 'preinscripcion',
            'preinscripción registrada' => 'preinscripcion',
            'aprobación' => 'aprobacion',
            'aprobación del curso' => 'aprobacion',
            'certificado' => 'certificado',
            'emisión de certificado' => 'certificado',
        ];

        return $mapeo[$evento] ?? $evento;
    }

    /**
     * Genera eventos automáticos de timeline basados en los datos de la inscripción
     * Se usa como fallback cuando no hay eventos manuales registrados
     *
     * @param int $enrollmentId ID de inscripción
     * @return array
     */
    private static function generateAutoTimeline(int $enrollmentId): array
    {
        $timeline = [];

        try {
            // Obtener datos de la inscripción
            $stmt = self::db()->prepare("
                SELECT
                    i.fecha_preinscripcion,
                    i.fecha_inscripcion,
                    i.fecha_inicio,
                    i.fecha_finalizacion,
                    i.estado,
                    i.nota_final,
                    i.certificado_emitido,
                    i.fecha_emision_certificado,
                    c.fecha_inicio as curso_fecha_inicio,
                    c.fecha_fin as curso_fecha_fin
                FROM verumax_academi.inscripciones i
                INNER JOIN verumax_academi.cursos c ON i.id_curso = c.id_curso
                WHERE i.id_inscripcion = :id_inscripcion
            ");
            $stmt->execute([':id_inscripcion' => $enrollmentId]);
            $inscripcion = $stmt->fetch();

            if (!$inscripcion) {
                return [];
            }

            // 1. Evento de preinscripción (si existe)
            if (!empty($inscripcion['fecha_preinscripcion'])) {
                $timeline[] = [
                    'fecha' => date('d/m/Y', strtotime($inscripcion['fecha_preinscripcion'])),
                    'evento' => 'Preinscripción registrada',
                    'detalle' => null
                ];
            }

            // 2. Evento de inscripción
            if (!empty($inscripcion['fecha_inscripcion'])) {
                $timeline[] = [
                    'fecha' => date('d/m/Y', strtotime($inscripcion['fecha_inscripcion'])),
                    'evento' => 'Inscripción al curso',
                    'detalle' => null
                ];
            }

            // 3. Evento de inicio (de la inscripción o del curso)
            $fecha_inicio = !empty($inscripcion['fecha_inicio'])
                ? $inscripcion['fecha_inicio']
                : $inscripcion['curso_fecha_inicio'];
            if (!empty($fecha_inicio)) {
                $timeline[] = [
                    'fecha' => date('d/m/Y', strtotime($fecha_inicio)),
                    'evento' => 'Inicio del curso',
                    'detalle' => null
                ];
            }

            // 4. Evento de finalización
            if (!empty($inscripcion['fecha_finalizacion'])) {
                $timeline[] = [
                    'fecha' => date('d/m/Y', strtotime($inscripcion['fecha_finalizacion'])),
                    'evento' => 'Finalización del curso',
                    'detalle' => null
                ];
            }

            // 5. Evento de aprobación/desaprobación (basado en estado)
            if ($inscripcion['estado'] === 'Aprobado') {
                $detalle = null;
                if (!empty($inscripcion['nota_final'])) {
                    $detalle = 'Nota final: ' . number_format($inscripcion['nota_final'], 2);
                }
                $timeline[] = [
                    'fecha' => !empty($inscripcion['fecha_finalizacion'])
                        ? date('d/m/Y', strtotime($inscripcion['fecha_finalizacion']))
                        : '',
                    'evento' => 'Aprobación del curso',
                    'detalle' => $detalle
                ];
            } elseif ($inscripcion['estado'] === 'Desaprobado') {
                $detalle = null;
                if (!empty($inscripcion['nota_final'])) {
                    $detalle = 'Nota obtenida: ' . number_format($inscripcion['nota_final'], 2);
                }
                $timeline[] = [
                    'fecha' => !empty($inscripcion['fecha_finalizacion'])
                        ? date('d/m/Y', strtotime($inscripcion['fecha_finalizacion']))
                        : '',
                    'evento' => 'Curso no aprobado',
                    'detalle' => $detalle
                ];
            }

            // 6. Evento de certificación (si corresponde)
            if (!empty($inscripcion['certificado_emitido']) && $inscripcion['certificado_emitido'] == 1) {
                $timeline[] = [
                    'fecha' => !empty($inscripcion['fecha_emision_certificado'])
                        ? date('d/m/Y', strtotime($inscripcion['fecha_emision_certificado']))
                        : '',
                    'evento' => 'Certificado emitido',
                    'detalle' => null
                ];
            }

            // Ordenar por fecha
            usort($timeline, function($a, $b) {
                $dateA = !empty($a['fecha']) ? strtotime(str_replace('/', '-', $a['fecha'])) : 0;
                $dateB = !empty($b['fecha']) ? strtotime(str_replace('/', '-', $b['fecha'])) : 0;
                return $dateA - $dateB;
            });

        } catch (PDOException $e) {
            error_log("StudentService::generateAutoTimeline error: " . $e->getMessage());
        }

        return $timeline;
    }

    /**
     * Formatea la fecha de finalización
     *
     * @param string|null $date Fecha de la BD
     * @param string $status Estado del curso
     * @return string
     */
    private static function formatEndDate(?string $date, string $status): string
    {
        if ($date) {
            return date('d/m/Y', strtotime($date));
        }

        if ($status === 'En Curso') {
            return 'En curso';
        }

        return 'N/A';
    }

    // =========================================================================
    // MÉTODOS PARA DOCENTES
    // =========================================================================

    /**
     * Busca un docente por institución y DNI
     * ACTUALIZADO: Ahora busca en verumax_nexus.miembros con tipo_miembro IN ('Docente', 'ambos')
     *
     * @param string $institution Código de la institución (slug)
     * @param string $dni DNI del docente
     * @return array|null Datos del docente o null
     */
    public static function findDocenteByDni(string $institution, string $dni): ?array
    {
        try {
            // Obtener id_instancia desde el slug
            $id_instancia = self::getIdInstancia($institution);
            if (!$id_instancia) {
                return null;
            }

            // ACTUALIZADO: Buscar usando miembro_roles para consistencia con MemberService
            // Busca miembros que tengan rol 'Docente' activo en miembro_roles
            // O que tengan tipo_miembro IN ('Docente', 'ambos') como fallback
            $stmt = self::nexusDb()->prepare("
                SELECT
                    m.id_miembro as id_docente,
                    m.id_miembro,
                    m.id_instancia,
                    m.identificador_principal as dni,
                    m.nombre,
                    m.apellido,
                    m.nombre_completo,
                    m.email,
                    m.telefono,
                    m.genero,
                    m.campo_texto_1 as especialidad,
                    m.campo_texto_2 as titulo,
                    m.estado,
                    m.tipo_miembro
                FROM miembros m
                WHERE m.id_instancia = :id_instancia
                AND m.identificador_principal = :dni
                AND m.estado = 'Activo'
                AND (
                    EXISTS (
                        SELECT 1 FROM miembro_roles mr
                        WHERE mr.id_miembro = m.id_miembro
                        AND mr.rol = 'Docente'
                        AND mr.activo = 1
                    )
                    OR m.tipo_miembro IN ('Docente', 'ambos')
                )
                LIMIT 1
            ");
            $stmt->execute([
                ':id_instancia' => $id_instancia,
                ':dni' => $dni
            ]);

            $docente = $stmt->fetch(PDO::FETCH_ASSOC);
            return $docente ?: null;

        } catch (PDOException $e) {
            error_log("Error buscando docente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene las participaciones de un docente en cursos
     *
     * @param int $docenteId ID del docente
     * @return array Participaciones indexadas por código de curso
     */
    private static function fetchDocenteParticipaciones(int $docenteId): array
    {
        // Envuelto en try-catch porque las tablas pueden no existir
        try {
            $db = self::db();

            // Solo campos que existen en la tabla remota
            // NOTA: participaciones_docente NO tiene id_cohorte, se quitó el JOIN
            $stmt = $db->prepare("
                SELECT
                    p.id_participacion,
                    p.rol,
                    p.estado,
                    p.titulo_participacion,
                    p.fecha_inicio,
                    p.fecha_fin,
                    p.certificado_emitido,
                    p.fecha_creacion,
                    c.id_curso,
                    c.codigo_curso,
                    c.nombre_curso,
                    c.carga_horaria
                FROM verumax_certifi.participaciones_docente p
                LEFT JOIN verumax_academi.cursos c ON p.id_curso = c.id_curso
                WHERE p.id_miembro = :id_miembro
                AND p.activo = 1
                ORDER BY p.fecha_inicio DESC, p.fecha_creacion DESC
            ");
            $stmt->execute([':id_miembro' => $docenteId]);

            $participaciones = [];
            while ($row = $stmt->fetch()) {
            // Usar id_participacion como key para evitar colisiones
            $key = $row['codigo_curso'] . '_' . $row['id_participacion'];

            // Formatear rol para mostrar
            $roles_display = [
                'docente' => 'Docente',
                'instructor' => 'Instructor',
                'orador' => 'Orador',
                'conferencista' => 'Conferencista',
                'facilitador' => 'Facilitador',
                'tutor' => 'Tutor',
                'coordinador' => 'Coordinador'
            ];

            $participaciones[$key] = [
                'id_participacion' => $row['id_participacion'],
                'nombre_curso' => $row['nombre_curso'],
                'codigo_curso' => $row['codigo_curso'],
                'rol' => $row['rol'],
                'rol_display' => $roles_display[$row['rol']] ?? ucfirst($row['rol']),
                'estado' => $row['estado'] ?? 'Asignado',
                'titulo_participacion' => $row['titulo_participacion'],
                'carga_horaria' => $row['carga_horaria'],
                'fecha_inicio' => $row['fecha_inicio'] ? date('d/m/Y', strtotime($row['fecha_inicio'])) : null,
                'fecha_fin' => $row['fecha_fin'] ? date('d/m/Y', strtotime($row['fecha_fin'])) : null,
                'certificado_emitido' => $row['certificado_emitido'],
                'tipo' => 'docente' // Para identificar en la vista
            ];
        }

            return $participaciones;
        } catch (PDOException $e) {
            // Tabla puede no existir o error de query
            error_log("fetchDocenteParticipaciones error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una participación específica de docente
     *
     * @param string $institution Código de la institución
     * @param string $dni DNI del docente
     * @param int $participacionId ID de la participación
     * @return array|null Datos de la participación o null
     */
    public static function getParticipacionDocente(string $institution, string $dni, int $participacionId): ?array
    {
        try {
            $docente = self::findDocenteByDni($institution, $dni);
            if (!$docente) {
                return null;
            }

            // ACTUALIZADO: Incluye campo estado para controlar tipo de documento
            // ACTUALIZADO: Incluye campos de firmantes del curso
            $stmt = self::db()->prepare("
                SELECT
                    p.id_participacion,
                    p.id_curso,
                    p.rol,
                    p.estado,
                    p.titulo_participacion,
                    p.fecha_inicio,
                    p.fecha_fin,
                    p.certificado_emitido,
                    p.fecha_certificado,
                    c.codigo_curso,
                    c.nombre_curso,
                    c.carga_horaria,
                    c.firmante_1_nombre,
                    c.firmante_1_cargo,
                    c.firmante_2_nombre,
                    c.firmante_2_cargo,
                    c.usar_firmante_1,
                    c.usar_firmante_2,
                    c.firmante_1_firma_url,
                    c.firmante_2_firma_url,
                    c.ciudad_emision,
                    c.fecha_inicio AS curso_fecha_inicio,
                    c.fecha_fin AS curso_fecha_fin
                FROM verumax_certifi.participaciones_docente p
                LEFT JOIN verumax_academi.cursos c ON p.id_curso = c.id_curso
                WHERE p.id_participacion = :id_participacion
                AND p.id_miembro = :id_miembro
                AND p.activo = 1
            ");
            $stmt->execute([
                ':id_participacion' => $participacionId,
                ':id_miembro' => $docente['id_miembro']
            ]);

            $participacion = $stmt->fetch();
            if (!$participacion) {
                return null;
            }

            // Mapeo de roles para display
            $roles_display = [
                'docente' => 'Docente',
                'instructor' => 'Instructor',
                'orador' => 'Orador',
                'conferencista' => 'Conferencista',
                'facilitador' => 'Facilitador',
                'tutor' => 'Tutor',
                'coordinador' => 'Coordinador'
            ];

            return [
                'nombre_completo' => $docente['nombre_completo'],
                'dni' => $docente['dni'],
                'genero' => $docente['genero'] ?? 'Prefiero no especificar',
                'especialidad' => $docente['especialidad'] ?? '',
                'titulo' => $docente['titulo'] ?? '',
                'participacion' => [
                    'id_participacion' => $participacion['id_participacion'],
                    'id_curso' => $participacion['id_curso'],
                    'codigo_curso' => $participacion['codigo_curso'],
                    'nombre_curso' => $participacion['nombre_curso'],
                    'rol' => $participacion['rol'],
                    'rol_display' => $roles_display[$participacion['rol']] ?? ucfirst($participacion['rol']),
                    'estado' => $participacion['estado'] ?? 'Asignado',
                    'titulo_participacion' => $participacion['titulo_participacion'],
                    'carga_horaria' => $participacion['carga_horaria'],
                    'fecha_inicio' => $participacion['fecha_inicio'] ? date('d/m/Y', strtotime($participacion['fecha_inicio'])) : null,
                    'fecha_inicio_raw' => $participacion['fecha_inicio'] ?? null,
                    'fecha_fin' => $participacion['fecha_fin'] ? date('d/m/Y', strtotime($participacion['fecha_fin'])) : null,
                    'fecha_fin_raw' => $participacion['fecha_fin'] ?? null,
                    // Fecha unificada: usa fecha de participación si existe, sino fecha del curso
                    'fecha_finalizacion_raw' => !empty($participacion['fecha_fin'])
                        ? $participacion['fecha_fin']
                        : ($participacion['curso_fecha_fin'] ?? null),
                    'certificado_emitido' => $participacion['certificado_emitido'],
                    'fecha_certificado' => $participacion['fecha_certificado'] ?? null,
                    'firmante_1_nombre' => $participacion['firmante_1_nombre'] ?? null,
                    'firmante_1_cargo' => $participacion['firmante_1_cargo'] ?? null,
                    'firmante_2_nombre' => $participacion['firmante_2_nombre'] ?? null,
                    'firmante_2_cargo' => $participacion['firmante_2_cargo'] ?? null,
                    'usar_firmante_1' => $participacion['usar_firmante_1'] ?? 1,
                    'usar_firmante_2' => $participacion['usar_firmante_2'] ?? 1,
                    'firmante_1_firma_url' => $participacion['firmante_1_firma_url'] ?? null,
                    'firmante_2_firma_url' => $participacion['firmante_2_firma_url'] ?? null,
                    // Ciudad de emisión y fechas del curso (para {{lugar_fecha}} y {{fecha_curso}})
                    'ciudad_emision' => $participacion['ciudad_emision'] ?? null,
                    'curso_fecha_inicio_raw' => $participacion['curso_fecha_inicio'] ?? null,
                    'curso_fecha_fin_raw' => $participacion['curso_fecha_fin'] ?? null
                ]
            ];

        } catch (PDOException $e) {
            error_log("Error obteniendo participación docente: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Alias de getParticipacionDocente para compatibilidad
     */
    public static function getDocenteParticipacion(string $institution, string $dni, $participacionId): ?array
    {
        return self::getParticipacionDocente($institution, $dni, (int)$participacionId);
    }

    /**
     * Marca un certificado de estudiante como emitido (si no lo estaba)
     * Registra la fecha de emisión y devuelve dicha fecha
     *
     * @param int $id_inscripcion ID de la inscripción
     * @return string|null Fecha de emisión (Y-m-d H:i:s) o null si error
     */
    public static function marcarCertificadoEmitidoEstudiante(int $id_inscripcion): ?string
    {
        try {
            $conn = DatabaseService::get('academicus');

            // Verificar estado actual
            $stmt = $conn->prepare("
                SELECT certificado_emitido, fecha_emision_certificado
                FROM inscripciones
                WHERE id_inscripcion = :id
            ");
            $stmt->execute([':id' => $id_inscripcion]);
            $row = $stmt->fetch();

            if (!$row) {
                return null;
            }

            // Si ya está emitido, devolver la fecha existente
            if ($row['certificado_emitido'] == 1 && !empty($row['fecha_emision_certificado'])) {
                return $row['fecha_emision_certificado'];
            }

            // Marcar como emitido con fecha actual
            $fecha_emision = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("
                UPDATE inscripciones
                SET certificado_emitido = 1,
                    fecha_emision_certificado = :fecha
                WHERE id_inscripcion = :id
            ");
            $stmt->execute([
                ':fecha' => $fecha_emision,
                ':id' => $id_inscripcion
            ]);

            // Registrar evento en trayectoria usando InscripcionService si está disponible
            if (class_exists('VERUMax\Services\InscripcionService')) {
                \VERUMax\Services\InscripcionService::agregarEvento($id_inscripcion, [
                    'tipo_evento' => 'Certificación',
                    'evento' => 'Certificado emitido',
                    'detalle' => null,
                    'fecha' => date('Y-m-d')
                ]);
            }

            return $fecha_emision;

        } catch (\PDOException $e) {
            error_log("Error marcando certificado emitido (estudiante): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Marca un certificado de docente como emitido (si no lo estaba)
     * Registra la fecha de emisión y devuelve dicha fecha
     *
     * @param int $id_participacion ID de la participación docente
     * @return string|null Fecha de emisión (Y-m-d H:i:s) o null si error
     */
    public static function marcarCertificadoEmitidoDocente(int $id_participacion): ?string
    {
        try {
            $conn = DatabaseService::get('certificatum');

            // Verificar estado actual
            $stmt = $conn->prepare("
                SELECT certificado_emitido, fecha_certificado
                FROM participaciones_docente
                WHERE id_participacion = :id
            ");
            $stmt->execute([':id' => $id_participacion]);
            $row = $stmt->fetch();

            if (!$row) {
                return null;
            }

            // Si ya está emitido, devolver la fecha existente
            if ($row['certificado_emitido'] == 1 && !empty($row['fecha_certificado'])) {
                return $row['fecha_certificado'];
            }

            // Marcar como emitido con fecha actual
            $fecha_emision = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("
                UPDATE participaciones_docente
                SET certificado_emitido = 1,
                    fecha_certificado = :fecha
                WHERE id_participacion = :id
            ");
            $stmt->execute([
                ':fecha' => $fecha_emision,
                ':id' => $id_participacion
            ]);

            return $fecha_emision;

        } catch (\PDOException $e) {
            error_log("Error marcando certificado emitido (docente): " . $e->getMessage());
            return null;
        }
    }
}
