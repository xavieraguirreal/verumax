<?php
/**
 * InscripcionService - Servicio de gestión de inscripciones (Academicus)
 *
 * Este servicio gestiona las inscripciones de estudiantes a cursos.
 * Es parte del módulo Academicus de VERUMax.
 *
 * @package VERUMax\Services
 * @version 1.0.0
 */

namespace VERUMax\Services;

use PDO;
use PDOException;
use VERUMax\Services\DatabaseService;
use VERUMax\Services\MemberService;
use VERUMax\Services\CursoService;

class InscripcionService
{
    /**
     * Estados de inscripción disponibles
     */
    public const ESTADOS = [
        'Preinscrito',
        'Inscrito',
        'En Curso',
        'Finalizado',
        'Aprobado',
        'Desaprobado',
        'Abandonado',
        'Suspendido'
    ];

    /**
     * Estados de pago disponibles
     */
    public const ESTADOS_PAGO = [
        'Pendiente',
        'Parcial',
        'Completo',
        'Exento',
        'Becado'
    ];

    /**
     * Obtiene conexión a verumax_academi
     */
    private static function getConnection(): PDO
    {
        return DatabaseService::get('academicus');
    }

    /**
     * Obtiene todas las inscripciones de una instancia
     *
     * @param int $id_instancia ID de la instancia
     * @param array $filtros Filtros opcionales ['buscar', 'estado', 'id_curso', 'id_cohorte']
     * @return array Lista de inscripciones con datos de miembro y curso
     */
    public static function getAll(int $id_instancia, array $filtros = []): array
    {
        try {
            $conn = self::getConnection();

            $sql = "
                SELECT
                    i.*,
                    m.identificador_principal as dni,
                    m.nombre_completo,
                    m.email,
                    c.codigo_curso,
                    c.nombre_curso,
                    c.carga_horaria,
                    c.tipo_curso,
                    c.usar_demora_global,
                    c.demora_tipo,
                    c.demora_certificado_horas,
                    c.demora_fecha,
                    co.codigo_cohorte,
                    co.nombre_cohorte
                FROM inscripciones i
                INNER JOIN verumax_nexus.miembros m ON i.id_miembro = m.id_miembro
                INNER JOIN cursos c ON i.id_curso = c.id_curso
                LEFT JOIN cohortes co ON i.id_cohorte = co.id_cohorte
                WHERE i.id_instancia = :id_instancia
                AND i.activo = 1
            ";

            $params = [':id_instancia' => $id_instancia];

            // Filtro por búsqueda
            if (!empty($filtros['buscar'])) {
                $sql .= " AND (m.identificador_principal LIKE :buscar
                          OR m.nombre_completo LIKE :buscar
                          OR c.codigo_curso LIKE :buscar
                          OR c.nombre_curso LIKE :buscar)";
                $params[':buscar'] = "%{$filtros['buscar']}%";
            }

            // Filtro por estado
            if (!empty($filtros['estado'])) {
                $sql .= " AND i.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }

            // Filtro por curso
            if (!empty($filtros['id_curso'])) {
                $sql .= " AND i.id_curso = :id_curso";
                $params[':id_curso'] = $filtros['id_curso'];
            }

            // Filtro por cohorte
            if (!empty($filtros['id_cohorte'])) {
                $sql .= " AND i.id_cohorte = :id_cohorte";
                $params[':id_cohorte'] = $filtros['id_cohorte'];
            }

            $sql .= " ORDER BY i.created_at DESC";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("InscripcionService::getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una inscripción por ID
     *
     * @param int $id_inscripcion
     * @return array|null
     */
    public static function getById(int $id_inscripcion): ?array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                SELECT
                    i.*,
                    m.identificador_principal as dni,
                    m.nombre_completo,
                    m.email,
                    c.codigo_curso,
                    c.nombre_curso,
                    c.carga_horaria,
                    co.codigo_cohorte,
                    co.nombre_cohorte
                FROM inscripciones i
                INNER JOIN verumax_nexus.miembros m ON i.id_miembro = m.id_miembro
                INNER JOIN cursos c ON i.id_curso = c.id_curso
                LEFT JOIN cohortes co ON i.id_cohorte = co.id_cohorte
                WHERE i.id_inscripcion = :id
            ");
            $stmt->execute([':id' => $id_inscripcion]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("InscripcionService::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene inscripción por miembro y curso
     *
     * @param int $id_miembro
     * @param int $id_curso
     * @param int|null $id_cohorte
     * @return array|null
     */
    public static function getByMiembroCurso(int $id_miembro, int $id_curso, ?int $id_cohorte = null): ?array
    {
        try {
            $conn = self::getConnection();

            $sql = "SELECT * FROM inscripciones WHERE id_miembro = :id_miembro AND id_curso = :id_curso";
            $params = [':id_miembro' => $id_miembro, ':id_curso' => $id_curso];

            if ($id_cohorte !== null) {
                $sql .= " AND id_cohorte = :id_cohorte";
                $params[':id_cohorte'] = $id_cohorte;
            } else {
                $sql .= " AND id_cohorte IS NULL";
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("InscripcionService::getByMiembroCurso error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea una nueva inscripción
     *
     * @param array $datos Datos de la inscripción
     * @return array ['success' => bool, 'id_inscripcion' => int|null, 'mensaje' => string]
     */
    public static function crear(array $datos): array
    {
        try {
            $conn = self::getConnection();

            // Validar campos requeridos
            if (empty($datos['id_instancia']) || empty($datos['id_miembro']) || empty($datos['id_curso'])) {
                return ['success' => false, 'mensaje' => 'Faltan campos requeridos (id_instancia, id_miembro, id_curso)'];
            }

            // Verificar si ya existe
            $existe = self::getByMiembroCurso(
                (int)$datos['id_miembro'],
                (int)$datos['id_curso'],
                isset($datos['id_cohorte']) ? (int)$datos['id_cohorte'] : null
            );
            if ($existe) {
                return ['success' => false, 'mensaje' => 'El estudiante ya está inscrito en este curso'];
            }

            $sql = "
                INSERT INTO inscripciones (
                    id_instancia, id_miembro, id_curso, id_cohorte, estado,
                    fecha_preinscripcion, fecha_inscripcion, fecha_inicio, fecha_finalizacion,
                    nota_final, asistencia_porcentaje, observaciones
                ) VALUES (
                    :id_instancia, :id_miembro, :id_curso, :id_cohorte, :estado,
                    :fecha_preinscripcion, :fecha_inscripcion, :fecha_inicio, :fecha_finalizacion,
                    :nota_final, :asistencia_porcentaje, :observaciones
                )
            ";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id_instancia' => $datos['id_instancia'],
                ':id_miembro' => $datos['id_miembro'],
                ':id_curso' => $datos['id_curso'],
                ':id_cohorte' => $datos['id_cohorte'] ?? null,
                ':estado' => $datos['estado'] ?? 'Inscrito',
                ':fecha_preinscripcion' => $datos['fecha_preinscripcion'] ?? null,
                ':fecha_inscripcion' => $datos['fecha_inscripcion'] ?? date('Y-m-d'),
                ':fecha_inicio' => $datos['fecha_inicio'] ?? null,
                ':fecha_finalizacion' => $datos['fecha_finalizacion'] ?? null,
                ':nota_final' => $datos['nota_final'] ?? null,
                ':asistencia_porcentaje' => $datos['asistencia_porcentaje'] ?? null,
                ':observaciones' => $datos['observaciones'] ?? null
            ]);

            $id_inscripcion = $conn->lastInsertId();

            // Agregar evento de trayectoria
            self::agregarEvento($id_inscripcion, [
                'tipo_evento' => 'Inscripción',
                'evento' => 'Inscripción al curso',
                'fecha' => date('Y-m-d')
            ]);

            return [
                'success' => true,
                'id_inscripcion' => $id_inscripcion,
                'mensaje' => 'Inscripción creada correctamente'
            ];

        } catch (PDOException $e) {
            error_log("InscripcionService::crear error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Actualiza una inscripción existente
     * Genera eventos automáticos en la trayectoria cuando hay cambios significativos
     *
     * @param int $id_inscripcion
     * @param array $datos Datos a actualizar
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function actualizar(int $id_inscripcion, array $datos): array
    {
        try {
            $conn = self::getConnection();

            // Obtener datos actuales para detectar cambios
            $inscripcion_actual = self::getById($id_inscripcion);
            if (!$inscripcion_actual) {
                return ['success' => false, 'mensaje' => 'Inscripción no encontrada'];
            }

            $campos_permitidos = [
                'id_cohorte', 'estado', 'fecha_preinscripcion', 'fecha_inscripcion',
                'fecha_inicio', 'fecha_finalizacion', 'nota_final', 'nota_minima_aprobacion',
                'asistencia_porcentaje', 'asistencia_minima', 'certificado_emitido',
                'fecha_emision_certificado', 'codigo_certificado', 'monto_pagado',
                'estado_pago', 'observaciones', 'activo'
            ];

            $sets = [];
            $params = [':id' => $id_inscripcion];

            foreach ($campos_permitidos as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $sets[] = "$campo = :$campo";
                    $params[":$campo"] = $datos[$campo];
                }
            }

            if (empty($sets)) {
                return ['success' => false, 'mensaje' => 'No hay campos para actualizar'];
            }

            $sql = "UPDATE inscripciones SET " . implode(', ', $sets) . " WHERE id_inscripcion = :id";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            // Generar eventos automáticos según los cambios detectados
            self::generarEventosPorCambios($id_inscripcion, $inscripcion_actual, $datos);

            return ['success' => true, 'mensaje' => 'Inscripción actualizada correctamente'];

        } catch (PDOException $e) {
            error_log("InscripcionService::actualizar error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Genera eventos automáticos en la trayectoria según los cambios detectados
     *
     * @param int $id_inscripcion
     * @param array $datos_anteriores
     * @param array $datos_nuevos
     */
    private static function generarEventosPorCambios(int $id_inscripcion, array $datos_anteriores, array $datos_nuevos): void
    {
        // Mapeo de estados a eventos
        $eventos_por_estado = [
            'Preinscrito' => ['tipo' => 'Preinscripción', 'evento' => 'Preinscripción registrada'],
            'Inscrito' => ['tipo' => 'Inscripción', 'evento' => 'Inscripción confirmada'],
            'En Curso' => ['tipo' => 'Inicio', 'evento' => 'Inicio del curso'],
            'Finalizado' => ['tipo' => 'Finalización', 'evento' => 'Curso finalizado'],
            'Aprobado' => ['tipo' => 'Aprobación', 'evento' => 'Aprobación del curso'],
            'Desaprobado' => ['tipo' => 'Evaluación', 'evento' => 'Curso no aprobado'],
            'Abandonado' => ['tipo' => 'Baja', 'evento' => 'Abandono del curso'],
            'Suspendido' => ['tipo' => 'Suspensión', 'evento' => 'Suspensión de la inscripción']
        ];

        // 1. Detectar cambio de estado
        if (isset($datos_nuevos['estado']) && $datos_nuevos['estado'] !== $datos_anteriores['estado']) {
            $nuevo_estado = $datos_nuevos['estado'];
            if (isset($eventos_por_estado[$nuevo_estado])) {
                $evento_config = $eventos_por_estado[$nuevo_estado];
                $detalle = null;

                // Agregar detalle según el estado
                if ($nuevo_estado === 'Aprobado' && isset($datos_nuevos['nota_final'])) {
                    $detalle = 'Nota final: ' . $datos_nuevos['nota_final'];
                } elseif ($nuevo_estado === 'Desaprobado' && isset($datos_nuevos['nota_final'])) {
                    $detalle = 'Nota obtenida: ' . $datos_nuevos['nota_final'];
                }

                self::agregarEvento($id_inscripcion, [
                    'tipo_evento' => $evento_config['tipo'],
                    'evento' => $evento_config['evento'],
                    'detalle' => $detalle,
                    'fecha' => date('Y-m-d')
                ]);
            }
        }

        // 2. Detectar asignación de nota (si no hubo cambio de estado a Aprobado/Desaprobado)
        $estado_cambio_nota = isset($datos_nuevos['estado']) && in_array($datos_nuevos['estado'], ['Aprobado', 'Desaprobado']);
        if (!$estado_cambio_nota && isset($datos_nuevos['nota_final']) && $datos_nuevos['nota_final'] !== null) {
            $nota_anterior = $datos_anteriores['nota_final'];
            if ($nota_anterior === null || $nota_anterior === '') {
                self::agregarEvento($id_inscripcion, [
                    'tipo_evento' => 'Evaluación',
                    'evento' => 'Calificación asignada',
                    'detalle' => 'Nota: ' . $datos_nuevos['nota_final'],
                    'fecha' => date('Y-m-d')
                ]);
            }
        }

        // 3. Detectar emisión de certificado
        if (isset($datos_nuevos['certificado_emitido']) && $datos_nuevos['certificado_emitido'] == 1) {
            $cert_anterior = $datos_anteriores['certificado_emitido'] ?? 0;
            if ($cert_anterior != 1) {
                $codigo = $datos_nuevos['codigo_certificado'] ?? $datos_anteriores['codigo_certificado'] ?? null;
                self::agregarEvento($id_inscripcion, [
                    'tipo_evento' => 'Certificación',
                    'evento' => 'Certificado emitido',
                    'detalle' => $codigo ? 'Código: ' . $codigo : null,
                    'fecha' => $datos_nuevos['fecha_emision_certificado'] ?? date('Y-m-d')
                ]);
            }
        }

        // 4. Detectar cambio de fecha de inicio
        if (isset($datos_nuevos['fecha_inicio']) && $datos_nuevos['fecha_inicio'] !== null) {
            $fecha_anterior = $datos_anteriores['fecha_inicio'];
            if ($fecha_anterior === null || $fecha_anterior === '') {
                self::agregarEvento($id_inscripcion, [
                    'tipo_evento' => 'Inicio',
                    'evento' => 'Fecha de inicio establecida',
                    'detalle' => null,
                    'fecha' => $datos_nuevos['fecha_inicio']
                ]);
            }
        }

        // 5. Detectar cambio de fecha de finalización
        if (isset($datos_nuevos['fecha_finalizacion']) && $datos_nuevos['fecha_finalizacion'] !== null) {
            $fecha_anterior = $datos_anteriores['fecha_finalizacion'];
            if ($fecha_anterior === null || $fecha_anterior === '') {
                self::agregarEvento($id_inscripcion, [
                    'tipo_evento' => 'Finalización',
                    'evento' => 'Fecha de finalización registrada',
                    'detalle' => null,
                    'fecha' => $datos_nuevos['fecha_finalizacion']
                ]);
            }
        }
    }

    /**
     * Elimina una inscripción (soft delete)
     *
     * @param int $id_inscripcion
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function eliminar(int $id_inscripcion): array
    {
        try {
            $conn = self::getConnection();

            // Soft delete
            $stmt = $conn->prepare("UPDATE inscripciones SET activo = 0 WHERE id_inscripcion = :id");
            $stmt->execute([':id' => $id_inscripcion]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'mensaje' => 'Inscripción no encontrada'];
            }

            return ['success' => true, 'mensaje' => 'Inscripción eliminada correctamente'];

        } catch (PDOException $e) {
            error_log("InscripcionService::eliminar error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Agrega un evento a la trayectoria de una inscripción
     *
     * @param int $id_inscripcion
     * @param array $evento ['tipo_evento', 'evento', 'detalle', 'fecha']
     * @return bool
     */
    public static function agregarEvento(int $id_inscripcion, array $evento): bool
    {
        try {
            $conn = self::getConnection();

            // Obtener el siguiente orden
            $stmt = $conn->prepare("SELECT COALESCE(MAX(orden), 0) + 1 FROM trayectoria WHERE id_inscripcion = :id");
            $stmt->execute([':id' => $id_inscripcion]);
            $orden = $stmt->fetchColumn();

            $stmt = $conn->prepare("
                INSERT INTO trayectoria (id_inscripcion, fecha, tipo_evento, evento, detalle, orden)
                VALUES (:id_inscripcion, :fecha, :tipo_evento, :evento, :detalle, :orden)
            ");
            $stmt->execute([
                ':id_inscripcion' => $id_inscripcion,
                ':fecha' => $evento['fecha'] ?? date('Y-m-d'),
                ':tipo_evento' => $evento['tipo_evento'] ?? 'Otro',
                ':evento' => $evento['evento'],
                ':detalle' => $evento['detalle'] ?? null,
                ':orden' => $orden
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("InscripcionService::agregarEvento error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la trayectoria de una inscripción
     *
     * @param int $id_inscripcion
     * @return array
     */
    public static function getTrayectoria(int $id_inscripcion): array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                SELECT * FROM trayectoria
                WHERE id_inscripcion = :id
                ORDER BY fecha ASC, orden ASC
            ");
            $stmt->execute([':id' => $id_inscripcion]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("InscripcionService::getTrayectoria error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Importa inscripciones desde texto CSV
     *
     * Formato: DNI, Código Curso, Estado [, Fecha Inicio] [, Fecha Fin] [, Nota Final] [, Asistencia %]
     *
     * @param int $id_instancia
     * @param string $texto Contenido CSV
     * @return array Estadísticas
     */
    public static function importarDesdeTexto(int $id_instancia, string $texto): array
    {
        $stats = [
            'insertados' => 0,
            'actualizados' => 0,
            'errores' => []
        ];

        $lineas = explode("\n", trim($texto));

        foreach ($lineas as $num => $linea) {
            $linea = trim($linea);
            if (empty($linea)) continue;

            // Saltar encabezados
            if ($num === 0 && (stripos($linea, 'dni') !== false || stripos($linea, 'documento') !== false)) {
                continue;
            }

            $partes = array_map('trim', str_getcsv($linea));

            // Formato: DNI, Código Curso, Estado, Fecha Inicio, Fecha Fin, Nota, Asistencia
            if (count($partes) < 2) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": formato inválido (mínimo DNI y Código Curso)";
                continue;
            }

            // Limpiar DNI
            $dni = preg_replace('/[^A-Za-z0-9.\-]/', '', $partes[0]);
            if (empty($dni)) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": DNI inválido";
                continue;
            }

            // Código del curso
            $codigo_curso = strtoupper(trim($partes[1]));
            if (empty($codigo_curso)) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": Código de curso vacío";
                continue;
            }

            // Buscar miembro por DNI
            $miembro = MemberService::getByIdentificador($id_instancia, $dni);
            if (!$miembro) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": Estudiante no encontrado (DNI: $dni)";
                continue;
            }

            // Buscar curso por código
            $curso = CursoService::getByCodigo($id_instancia, $codigo_curso);
            if (!$curso) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": Curso no encontrado (Código: $codigo_curso)";
                continue;
            }

            $estado = isset($partes[2]) && in_array($partes[2], self::ESTADOS) ? $partes[2] : 'Inscrito';
            $fecha_inicio = isset($partes[3]) && !empty($partes[3]) ? self::parseFecha($partes[3]) : null;
            $fecha_fin = isset($partes[4]) && !empty($partes[4]) ? self::parseFecha($partes[4]) : null;
            $nota_final = isset($partes[5]) && is_numeric($partes[5]) ? (float)$partes[5] : null;
            $asistencia = isset($partes[6]) ? self::parseAsistencia($partes[6]) : null;

            // Verificar si ya existe
            $existe = self::getByMiembroCurso($miembro['id_miembro'], $curso['id_curso'], null);

            if ($existe) {
                // Actualizar
                $result = self::actualizar($existe['id_inscripcion'], [
                    'estado' => $estado,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_finalizacion' => $fecha_fin,
                    'nota_final' => $nota_final,
                    'asistencia_porcentaje' => $asistencia
                ]);
                if ($result['success']) {
                    $stats['actualizados']++;
                } else {
                    $stats['errores'][] = "Línea " . ($num + 1) . ": " . $result['mensaje'];
                }
            } else {
                // Crear
                $result = self::crear([
                    'id_instancia' => $id_instancia,
                    'id_miembro' => $miembro['id_miembro'],
                    'id_curso' => $curso['id_curso'],
                    'estado' => $estado,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_finalizacion' => $fecha_fin,
                    'nota_final' => $nota_final,
                    'asistencia_porcentaje' => $asistencia
                ]);
                if ($result['success']) {
                    $stats['insertados']++;
                } else {
                    $stats['errores'][] = "Línea " . ($num + 1) . ": " . $result['mensaje'];
                }
            }
        }

        return $stats;
    }

    /**
     * Parsea una fecha en varios formatos
     */
    private static function parseFecha(string $fecha): ?string
    {
        $fecha = trim($fecha);
        if (empty($fecha)) return null;

        // Formato DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        // Formato YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }
        return null;
    }

    /**
     * Parsea asistencia (puede venir como "95%" o "95")
     */
    private static function parseAsistencia(string $valor): ?float
    {
        $valor = trim(str_replace('%', '', $valor));
        return is_numeric($valor) ? (float)$valor : null;
    }

    /**
     * Obtiene estadísticas de inscripciones para una instancia
     *
     * @param int $id_instancia
     * @return array
     */
    public static function getEstadisticas(int $id_instancia): array
    {
        try {
            $conn = self::getConnection();

            $stmt = $conn->prepare("
                SELECT
                    COUNT(*) as total_inscripciones,
                    SUM(CASE WHEN estado = 'Aprobado' THEN 1 ELSE 0 END) as aprobados,
                    SUM(CASE WHEN estado = 'En Curso' THEN 1 ELSE 0 END) as en_curso,
                    SUM(CASE WHEN estado = 'Inscrito' THEN 1 ELSE 0 END) as inscriptos,
                    SUM(CASE WHEN estado = 'Desaprobado' THEN 1 ELSE 0 END) as desaprobados,
                    SUM(CASE WHEN certificado_emitido = 1 THEN 1 ELSE 0 END) as certificados_emitidos,
                    AVG(nota_final) as promedio_notas,
                    AVG(asistencia_porcentaje) as promedio_asistencia
                FROM inscripciones
                WHERE id_instancia = :id_instancia AND activo = 1
            ");
            $stmt->execute([':id_instancia' => $id_instancia]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log("InscripcionService::getEstadisticas error: " . $e->getMessage());
            return [];
        }
    }
}
