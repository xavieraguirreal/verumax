<?php
/**
 * CursoService - Servicio de gestión de cursos (Academicus)
 *
 * Este servicio es la fuente de verdad para cursos en el ecosistema VERUMax.
 * Certificatum, y futuros módulos académicos consumen de aquí.
 *
 * @package VERUMax\Services
 * @version 1.0.0
 */

namespace VERUMax\Services;

use PDO;
use PDOException;
use VERUMax\Services\DatabaseService;

class CursoService
{
    /**
     * Tipos de curso disponibles
     */
    public const TIPOS_CURSO = [
        'Curso',
        'Diplomatura',
        'Taller',
        'Seminario',
        'Conversatorio',
        'Capacitación',
        'Certificación'
    ];

    /**
     * Niveles disponibles
     */
    public const NIVELES = [
        'Inicial',
        'Intermedio',
        'Avanzado',
        'Todos los niveles'
    ];

    /**
     * Modalidades disponibles
     */
    public const MODALIDADES = [
        'Presencial',
        'Virtual',
        'Híbrido'
    ];

    /**
     * Obtiene conexión a verumax_academi
     */
    private static function getConnection(): PDO
    {
        return DatabaseService::get('academicus');
    }

    /**
     * Obtiene todos los cursos de una instancia
     *
     * @param int $id_instancia ID de la instancia
     * @param array $filtros Filtros opcionales ['buscar', 'activo', 'tipo_curso', 'categoria']
     * @return array Lista de cursos
     */
    public static function getAll(int $id_instancia, array $filtros = []): array
    {
        try {
            $conn = self::getConnection();

            $sql = "
                SELECT
                    c.*,
                    (SELECT COUNT(*) FROM inscripciones i WHERE i.id_curso = c.id_curso AND i.activo = 1) as total_inscripciones
                FROM cursos c
                WHERE c.id_instancia = :id_instancia
            ";

            $params = [':id_instancia' => $id_instancia];

            // Filtro por búsqueda
            if (!empty($filtros['buscar'])) {
                $sql .= " AND (c.codigo_curso LIKE :buscar
                          OR c.nombre_curso LIKE :buscar
                          OR c.descripcion LIKE :buscar
                          OR c.categoria LIKE :buscar)";
                $params[':buscar'] = "%{$filtros['buscar']}%";
            }

            // Filtro por estado activo
            if (isset($filtros['activo'])) {
                $sql .= " AND c.activo = :activo";
                $params[':activo'] = $filtros['activo'];
            }

            // Filtro por tipo de curso
            if (!empty($filtros['tipo_curso'])) {
                $sql .= " AND c.tipo_curso = :tipo_curso";
                $params[':tipo_curso'] = $filtros['tipo_curso'];
            }

            // Filtro por categoría
            if (!empty($filtros['categoria'])) {
                $sql .= " AND c.categoria = :categoria";
                $params[':categoria'] = $filtros['categoria'];
            }

            $sql .= " ORDER BY c.fecha_creacion DESC";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("CursoService::getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un curso por ID
     *
     * @param int $id_curso
     * @return array|null
     */
    public static function getById(int $id_curso): ?array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("SELECT * FROM cursos WHERE id_curso = :id");
            $stmt->execute([':id' => $id_curso]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("CursoService::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un curso por código e instancia
     *
     * @param int $id_instancia
     * @param string $codigo_curso
     * @return array|null
     */
    public static function getByCodigo(int $id_instancia, string $codigo_curso): ?array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                SELECT * FROM cursos
                WHERE id_instancia = :id_instancia
                AND codigo_curso = :codigo_curso
            ");
            $stmt->execute([
                ':id_instancia' => $id_instancia,
                ':codigo_curso' => $codigo_curso
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("CursoService::getByCodigo error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo curso
     *
     * @param array $datos Datos del curso
     * @return array ['success' => bool, 'id_curso' => int|null, 'mensaje' => string]
     */
    public static function crear(array $datos): array
    {
        try {
            $conn = self::getConnection();

            // Validar campos requeridos
            if (empty($datos['id_instancia']) || empty($datos['codigo_curso']) || empty($datos['nombre_curso'])) {
                return ['success' => false, 'mensaje' => 'Faltan campos requeridos (id_instancia, codigo_curso, nombre_curso)'];
            }

            // Limpiar código de curso
            $datos['codigo_curso'] = strtoupper(trim($datos['codigo_curso']));

            // Verificar si ya existe
            $existe = self::getByCodigo($datos['id_instancia'], $datos['codigo_curso']);
            if ($existe) {
                return ['success' => false, 'mensaje' => 'Ya existe un curso con ese código'];
            }

            $sql = "
                INSERT INTO cursos (
                    id_instancia, codigo_curso, nombre_curso, descripcion,
                    categoria, tipo_curso, nivel, modalidad,
                    carga_horaria, duracion_semanas, fecha_inicio, fecha_fin,
                    cupo_maximo, requisitos_previos, emite_certificado, tipo_certificado,
                    activo, visible_catalogo, ciudad_emision,
                    firmante_1_nombre, firmante_1_cargo, firmante_2_nombre, firmante_2_cargo,
                    usar_firmante_1, usar_firmante_2,
                    usar_demora_global, demora_certificado_horas, demora_tipo, demora_fecha
                ) VALUES (
                    :id_instancia, :codigo_curso, :nombre_curso, :descripcion,
                    :categoria, :tipo_curso, :nivel, :modalidad,
                    :carga_horaria, :duracion_semanas, :fecha_inicio, :fecha_fin,
                    :cupo_maximo, :requisitos_previos, :emite_certificado, :tipo_certificado,
                    :activo, :visible_catalogo, :ciudad_emision,
                    :firmante_1_nombre, :firmante_1_cargo, :firmante_2_nombre, :firmante_2_cargo,
                    :usar_firmante_1, :usar_firmante_2,
                    :usar_demora_global, :demora_certificado_horas, :demora_tipo, :demora_fecha
                )
            ";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id_instancia' => $datos['id_instancia'],
                ':codigo_curso' => $datos['codigo_curso'],
                ':nombre_curso' => trim($datos['nombre_curso']),
                ':descripcion' => $datos['descripcion'] ?? null,
                ':categoria' => $datos['categoria'] ?? null,
                ':tipo_curso' => $datos['tipo_curso'] ?? 'Curso',
                ':nivel' => $datos['nivel'] ?? 'Todos los niveles',
                ':modalidad' => $datos['modalidad'] ?? 'Virtual',
                ':carga_horaria' => !empty($datos['carga_horaria']) ? (int)$datos['carga_horaria'] : null,
                ':duracion_semanas' => !empty($datos['duracion_semanas']) ? (int)$datos['duracion_semanas'] : null,
                ':fecha_inicio' => !empty($datos['fecha_inicio']) ? $datos['fecha_inicio'] : null,
                ':fecha_fin' => !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null,
                ':cupo_maximo' => !empty($datos['cupo_maximo']) ? (int)$datos['cupo_maximo'] : null,
                ':requisitos_previos' => $datos['requisitos_previos'] ?? null,
                ':emite_certificado' => isset($datos['emite_certificado']) ? (int)$datos['emite_certificado'] : 1,
                ':tipo_certificado' => $datos['tipo_certificado'] ?? 'Certificado de Aprobación',
                ':activo' => isset($datos['activo']) ? (int)$datos['activo'] : 1,
                ':visible_catalogo' => isset($datos['visible_catalogo']) ? (int)$datos['visible_catalogo'] : 1,
                ':ciudad_emision' => $datos['ciudad_emision'] ?? null,
                ':firmante_1_nombre' => $datos['firmante_1_nombre'] ?? null,
                ':firmante_1_cargo' => $datos['firmante_1_cargo'] ?? null,
                ':firmante_2_nombre' => $datos['firmante_2_nombre'] ?? null,
                ':firmante_2_cargo' => $datos['firmante_2_cargo'] ?? null,
                ':usar_firmante_1' => isset($datos['usar_firmante_1']) ? (int)$datos['usar_firmante_1'] : 1,
                ':usar_firmante_2' => isset($datos['usar_firmante_2']) ? (int)$datos['usar_firmante_2'] : 1,
                ':usar_demora_global' => isset($datos['usar_demora_global']) ? (int)$datos['usar_demora_global'] : 1,
                ':demora_certificado_horas' => $datos['demora_certificado_horas'] ?? null,
                ':demora_tipo' => $datos['demora_tipo'] ?? 'inmediato',
                ':demora_fecha' => !empty($datos['demora_fecha']) ? $datos['demora_fecha'] : null
            ]);

            return [
                'success' => true,
                'id_curso' => $conn->lastInsertId(),
                'mensaje' => 'Curso creado correctamente'
            ];

        } catch (PDOException $e) {
            error_log("CursoService::crear error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Actualiza un curso existente
     *
     * @param int $id_curso
     * @param array $datos Datos a actualizar
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function actualizar(int $id_curso, array $datos): array
    {
        try {
            $conn = self::getConnection();

            // Construir SET dinámico solo con campos proporcionados
            $campos_permitidos = [
                'codigo_curso', 'nombre_curso', 'descripcion', 'categoria',
                'tipo_curso', 'nivel', 'modalidad', 'carga_horaria',
                'duracion_semanas', 'fecha_inicio', 'fecha_fin', 'cupo_maximo',
                'requisitos_previos', 'emite_certificado', 'tipo_certificado',
                'activo', 'visible_catalogo', 'id_template', 'ciudad_emision',
                'firmante_1_nombre', 'firmante_1_cargo',
                'firmante_2_nombre', 'firmante_2_cargo',
                'usar_firmante_1', 'usar_firmante_2',
                'firmante_1_firma_url', 'firmante_2_firma_url',
                'usar_demora_global', 'demora_certificado_horas', 'demora_tipo', 'demora_fecha'
            ];

            $sets = [];
            $params = [':id' => $id_curso];

            foreach ($campos_permitidos as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $sets[] = "$campo = :$campo";
                    // Convertir código a mayúsculas
                    if ($campo === 'codigo_curso') {
                        $datos[$campo] = strtoupper(trim($datos[$campo]));
                    }
                    $params[":$campo"] = $datos[$campo];
                }
            }

            if (empty($sets)) {
                return ['success' => false, 'mensaje' => 'No hay campos para actualizar'];
            }

            $sql = "UPDATE cursos SET " . implode(', ', $sets) . " WHERE id_curso = :id";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'mensaje' => 'Curso actualizado correctamente'];

        } catch (PDOException $e) {
            error_log("CursoService::actualizar error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Elimina un curso (verifica dependencias primero)
     *
     * @param int $id_curso
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function eliminar(int $id_curso): array
    {
        try {
            $conn = self::getConnection();

            // Verificar si tiene inscripciones (ahora en verumax_academi)
            $stmt_check = $conn->prepare("
                SELECT COUNT(*) FROM inscripciones
                WHERE id_curso = :id
            ");
            $stmt_check->execute([':id' => $id_curso]);
            $tiene_inscripciones = $stmt_check->fetchColumn() > 0;

            if ($tiene_inscripciones) {
                // Desactivar en lugar de eliminar para preservar historial
                $stmt = $conn->prepare("UPDATE cursos SET activo = 0 WHERE id_curso = :id");
                $stmt->execute([':id' => $id_curso]);

                if ($stmt->rowCount() === 0) {
                    return ['success' => false, 'mensaje' => 'Curso no encontrado'];
                }

                return [
                    'success' => true,
                    'mensaje' => 'Curso desactivado (tiene inscripciones asociadas, se preserva el historial)'
                ];
            }

            // Sin inscripciones: eliminar definitivamente
            $stmt = $conn->prepare("DELETE FROM cursos WHERE id_curso = :id");
            $stmt->execute([':id' => $id_curso]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'mensaje' => 'Curso no encontrado'];
            }

            return ['success' => true, 'mensaje' => 'Curso eliminado correctamente'];

        } catch (PDOException $e) {
            error_log("CursoService::eliminar error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene las categorías únicas de cursos de una instancia
     *
     * @param int $id_instancia
     * @return array Lista de categorías
     */
    public static function getCategorias(int $id_instancia): array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                SELECT DISTINCT categoria
                FROM cursos
                WHERE id_instancia = :id_instancia
                AND categoria IS NOT NULL
                AND categoria != ''
                ORDER BY categoria
            ");
            $stmt->execute([':id_instancia' => $id_instancia]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("CursoService::getCategorias error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Importa cursos desde texto CSV
     *
     * Formato esperado: codigo_curso, nombre_curso [, carga_horaria] [, tipo_curso] [, categoria]
     *
     * @param int $id_instancia
     * @param string $texto Contenido CSV
     * @return array Estadísticas ['insertados', 'actualizados', 'errores']
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

            // Saltar encabezados si los hay
            if ($num === 0 && (stripos($linea, 'codigo') !== false || stripos($linea, 'nombre') !== false)) {
                continue;
            }

            // Formato esperado: codigo_curso, nombre_curso [, carga_horaria] [, tipo_curso] [, categoria]
            $partes = array_map('trim', str_getcsv($linea));

            if (count($partes) < 2) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": formato inválido (se requiere código y nombre)";
                continue;
            }

            $codigo_curso = strtoupper(trim($partes[0]));
            if (empty($codigo_curso)) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": código de curso vacío";
                continue;
            }

            $nombre_curso = trim($partes[1]);
            if (empty($nombre_curso)) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": nombre de curso vacío";
                continue;
            }

            $carga_horaria = isset($partes[2]) && is_numeric($partes[2]) ? (int)$partes[2] : null;
            $tipo_curso = isset($partes[3]) && in_array($partes[3], self::TIPOS_CURSO) ? $partes[3] : 'Curso';
            $categoria = $partes[4] ?? null;

            // Verificar si existe
            $existe = self::getByCodigo($id_instancia, $codigo_curso);

            if ($existe) {
                // Actualizar
                $result = self::actualizar($existe['id_curso'], [
                    'nombre_curso' => $nombre_curso,
                    'carga_horaria' => $carga_horaria,
                    'tipo_curso' => $tipo_curso,
                    'categoria' => $categoria
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
                    'codigo_curso' => $codigo_curso,
                    'nombre_curso' => $nombre_curso,
                    'carga_horaria' => $carga_horaria,
                    'tipo_curso' => $tipo_curso,
                    'categoria' => $categoria
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
     * Importa cursos desde archivo Excel (usando PhpSpreadsheet o similar)
     * Por ahora parsea CSV que puede exportarse desde Excel
     *
     * @param int $id_instancia
     * @param string $filepath Ruta al archivo
     * @return array Estadísticas
     */
    public static function importarDesdeArchivo(int $id_instancia, string $filepath): array
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            $contenido = file_get_contents($filepath);
            return self::importarDesdeTexto($id_instancia, $contenido);
        }

        // Para Excel (.xlsx, .xls) se necesitaría PhpSpreadsheet
        // Por ahora retornamos error
        return [
            'insertados' => 0,
            'actualizados' => 0,
            'errores' => ['Formato de archivo no soportado. Use CSV o exporte a CSV desde Excel.']
        ];
    }

    /**
     * Prefijos por tipo de curso
     */
    private const PREFIJOS_TIPO = [
        'Curso' => 'CUR',
        'Diplomatura' => 'DIP',
        'Taller' => 'TAL',
        'Seminario' => 'SEM',
        'Conversatorio' => 'CON',
        'Capacitación' => 'CAP',
        'Certificación' => 'CER'
    ];

    /**
     * Genera un código de curso sugerido
     *
     * Formato: [PREFIJO_INST]-[TIPO]-[AÑO]-[SECUENCIAL]
     * Ejemplo: SJ-DIP-2024-001
     *
     * @param int $id_instancia ID de la instancia
     * @param string $tipo_curso Tipo de curso (Curso, Diplomatura, etc.)
     * @param string|null $prefijo_institucion Prefijo de la institución (ej: SJ, LIB)
     * @return string Código sugerido
     */
    public static function generarCodigoSugerido(int $id_instancia, string $tipo_curso = 'Curso', ?string $prefijo_institucion = null): string
    {
        try {
            $conn = self::getConnection();

            // Obtener prefijo de institución si no se proporciona
            if (!$prefijo_institucion) {
                $pdo_general = DatabaseService::get('general');
                $stmt = $pdo_general->prepare("SELECT slug FROM instances WHERE id_instancia = :id");
                $stmt->execute([':id' => $id_instancia]);
                $slug = $stmt->fetchColumn();
                $prefijo_institucion = strtoupper(substr($slug ?: 'XX', 0, 2));
            }

            // Prefijo según tipo de curso
            $prefijo_tipo = self::PREFIJOS_TIPO[$tipo_curso] ?? 'CUR';

            // Año actual
            $anio = date('Y');

            // Contar cursos existentes con este prefijo para el secuencial
            $patron = $prefijo_institucion . '-' . $prefijo_tipo . '-' . $anio . '-%';
            $stmt = $conn->prepare("
                SELECT COUNT(*) + 1 as siguiente
                FROM cursos
                WHERE id_instancia = :id_instancia
                AND codigo_curso LIKE :patron
            ");
            $stmt->execute([
                ':id_instancia' => $id_instancia,
                ':patron' => $patron
            ]);
            $siguiente = $stmt->fetchColumn() ?: 1;

            // Formatear secuencial con ceros a la izquierda
            $secuencial = str_pad($siguiente, 3, '0', STR_PAD_LEFT);

            return "{$prefijo_institucion}-{$prefijo_tipo}-{$anio}-{$secuencial}";

        } catch (PDOException $e) {
            error_log("CursoService::generarCodigoSugerido error: " . $e->getMessage());
            // Fallback con timestamp
            return strtoupper(substr($prefijo_institucion ?? 'XX', 0, 2)) . '-' . date('YmdHis');
        }
    }

    /**
     * Valida el formato de un código de curso
     *
     * Reglas:
     * - Solo letras mayúsculas, números y guiones
     * - Mínimo 3 caracteres, máximo 50
     * - No puede empezar ni terminar con guion
     * - No puede tener guiones consecutivos
     *
     * @param string $codigo Código a validar
     * @return array ['valido' => bool, 'mensaje' => string|null]
     */
    public static function validarFormatoCodigo(string $codigo): array
    {
        $codigo = trim($codigo);

        if (strlen($codigo) < 3) {
            return ['valido' => false, 'mensaje' => 'El código debe tener al menos 3 caracteres'];
        }

        if (strlen($codigo) > 50) {
            return ['valido' => false, 'mensaje' => 'El código no puede exceder 50 caracteres'];
        }

        // Solo letras mayúsculas, números y guiones
        if (!preg_match('/^[A-Z0-9\-]+$/', strtoupper($codigo))) {
            return ['valido' => false, 'mensaje' => 'El código solo puede contener letras, números y guiones'];
        }

        // No empezar ni terminar con guion
        if (str_starts_with($codigo, '-') || str_ends_with($codigo, '-')) {
            return ['valido' => false, 'mensaje' => 'El código no puede empezar ni terminar con guion'];
        }

        // No guiones consecutivos
        if (strpos($codigo, '--') !== false) {
            return ['valido' => false, 'mensaje' => 'El código no puede tener guiones consecutivos'];
        }

        return ['valido' => true, 'mensaje' => null];
    }

    /**
     * Verifica si un curso tiene certificados emitidos (códigos de validación)
     *
     * @param int $id_curso
     * @return bool
     */
    public static function tieneCertificadosEmitidos(int $id_curso): bool
    {
        try {
            $curso = self::getById($id_curso);
            if (!$curso) return false;

            $conn = DatabaseService::get('certificatum');
            $stmt = $conn->prepare("
                SELECT COUNT(*) FROM codigos_validacion
                WHERE codigo_curso = :codigo_curso
            ");
            $stmt->execute([':codigo_curso' => $curso['codigo_curso']]);
            return $stmt->fetchColumn() > 0;

        } catch (PDOException $e) {
            error_log("CursoService::tieneCertificadosEmitidos error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información extendida de un curso por código
     * Incluye estado y razón si está inactivo
     *
     * @param int $id_instancia
     * @param string $codigo_curso
     * @param bool $incluir_inactivos Si es true, busca también cursos inactivos
     * @return array|null
     */
    public static function getByCodigoExtendido(int $id_instancia, string $codigo_curso, bool $incluir_inactivos = true): ?array
    {
        try {
            $conn = self::getConnection();
            $sql = "SELECT * FROM cursos WHERE id_instancia = :id_instancia AND codigo_curso = :codigo_curso";

            if (!$incluir_inactivos) {
                $sql .= " AND activo = 1";
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id_instancia' => $id_instancia,
                ':codigo_curso' => $codigo_curso
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return null;
            }

            // Agregar información de estado
            $result['estado_info'] = [
                'activo' => (bool)$result['activo'],
                'mensaje' => $result['activo'] ? null : 'Este curso ha sido deshabilitado por la institución'
            ];

            return $result;

        } catch (PDOException $e) {
            error_log("CursoService::getByCodigoExtendido error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene estadísticas de cursos para una instancia
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
                    COUNT(*) as total_cursos,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as cursos_activos,
                    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as cursos_inactivos,
                    COUNT(DISTINCT tipo_curso) as tipos_distintos,
                    COUNT(DISTINCT categoria) as categorias_distintas
                FROM cursos
                WHERE id_instancia = :id_instancia
            ");
            $stmt->execute([':id_instancia' => $id_instancia]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'total_cursos' => 0,
                'cursos_activos' => 0,
                'cursos_inactivos' => 0,
                'tipos_distintos' => 0,
                'categorias_distintas' => 0
            ];

        } catch (PDOException $e) {
            error_log("CursoService::getEstadisticas error: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // MÉTODOS PARA COMPETENCIAS
    // =========================================================================

    /**
     * Obtiene las competencias de un curso
     *
     * @param int $id_curso ID del curso
     * @return array Lista de competencias ordenadas
     */
    public static function getCompetencias(int $id_curso): array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                SELECT id_competencia, competencia, orden
                FROM competencias
                WHERE id_curso = :id_curso AND activo = 1
                ORDER BY orden ASC
            ");
            $stmt->execute([':id_curso' => $id_curso]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("CursoService::getCompetencias error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guarda las competencias de un curso (reemplaza las existentes)
     *
     * @param int $id_curso ID del curso
     * @param array $competencias Lista de strings con las competencias
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function guardarCompetencias(int $id_curso, array $competencias): array
    {
        try {
            $conn = self::getConnection();

            // Iniciar transacción
            $conn->beginTransaction();

            // Desactivar competencias actuales (soft delete para preservar histórico)
            $stmt = $conn->prepare("UPDATE competencias SET activo = 0 WHERE id_curso = :id_curso");
            $stmt->execute([':id_curso' => $id_curso]);

            // Insertar nuevas competencias
            $orden = 1;
            foreach ($competencias as $competencia) {
                $competencia = trim($competencia);
                if (empty($competencia)) continue;

                // Verificar si ya existe (inactiva) para reactivarla
                $stmt = $conn->prepare("
                    SELECT id_competencia FROM competencias
                    WHERE id_curso = :id_curso AND competencia = :competencia
                ");
                $stmt->execute([':id_curso' => $id_curso, ':competencia' => $competencia]);
                $existente = $stmt->fetchColumn();

                if ($existente) {
                    // Reactivar y actualizar orden
                    $stmt = $conn->prepare("
                        UPDATE competencias SET activo = 1, orden = :orden
                        WHERE id_competencia = :id
                    ");
                    $stmt->execute([':orden' => $orden, ':id' => $existente]);
                } else {
                    // Insertar nueva
                    $stmt = $conn->prepare("
                        INSERT INTO competencias (id_curso, competencia, orden, activo)
                        VALUES (:id_curso, :competencia, :orden, 1)
                    ");
                    $stmt->execute([
                        ':id_curso' => $id_curso,
                        ':competencia' => $competencia,
                        ':orden' => $orden
                    ]);
                }
                $orden++;
            }

            $conn->commit();

            return [
                'success' => true,
                'mensaje' => 'Competencias guardadas correctamente',
                'total' => $orden - 1
            ];

        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("CursoService::guardarCompetencias error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Agrega una competencia a un curso
     *
     * @param int $id_curso ID del curso
     * @param string $competencia Texto de la competencia
     * @return array ['success' => bool, 'id_competencia' => int|null, 'mensaje' => string]
     */
    public static function agregarCompetencia(int $id_curso, string $competencia): array
    {
        try {
            $conn = self::getConnection();
            $competencia = trim($competencia);

            if (empty($competencia)) {
                return ['success' => false, 'mensaje' => 'La competencia no puede estar vacía'];
            }

            // Obtener siguiente orden
            $stmt = $conn->prepare("
                SELECT COALESCE(MAX(orden), 0) + 1 FROM competencias WHERE id_curso = :id_curso
            ");
            $stmt->execute([':id_curso' => $id_curso]);
            $orden = $stmt->fetchColumn();

            // Insertar
            $stmt = $conn->prepare("
                INSERT INTO competencias (id_curso, competencia, orden, activo)
                VALUES (:id_curso, :competencia, :orden, 1)
            ");
            $stmt->execute([
                ':id_curso' => $id_curso,
                ':competencia' => $competencia,
                ':orden' => $orden
            ]);

            return [
                'success' => true,
                'id_competencia' => $conn->lastInsertId(),
                'mensaje' => 'Competencia agregada correctamente'
            ];

        } catch (PDOException $e) {
            error_log("CursoService::agregarCompetencia error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Elimina una competencia (soft delete)
     *
     * @param int $id_competencia ID de la competencia
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function eliminarCompetencia(int $id_competencia): array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("UPDATE competencias SET activo = 0 WHERE id_competencia = :id");
            $stmt->execute([':id' => $id_competencia]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'mensaje' => 'Competencia no encontrada'];
            }

            return ['success' => true, 'mensaje' => 'Competencia eliminada correctamente'];

        } catch (PDOException $e) {
            error_log("CursoService::eliminarCompetencia error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }
}
