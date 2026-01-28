<?php
/**
 * MemberService - Servicio de gestión de miembros (Nexus)
 *
 * Este servicio es la fuente de verdad para personas en el ecosistema VERUMax.
 * Certificatum, Academicus y otros módulos consumen de aquí.
 *
 * @package VERUMax\Services
 * @version 1.0.0
 */

namespace VERUMax\Services;

use PDO;
use PDOException;
use VERUMax\Services\DatabaseService;

class MemberService
{
    /**
     * Obtiene conexión a verumax_nexus
     */
    private static function getConnection(): PDO
    {
        return DatabaseService::get('nexus');
    }

    /**
     * Obtiene todos los miembros de una instancia
     *
     * @param int $id_instancia ID de la instancia
     * @param array $filtros Filtros opcionales ['buscar', 'estado', 'tipo_miembro']
     * @return array Lista de miembros
     */
    public static function getAll(int $id_instancia, array $filtros = []): array
    {
        // DEBUG: Log de entrada
        error_log("MemberService::getAll - id_instancia: $id_instancia, filtros: " . json_encode($filtros));

        try {
            $conn = self::getConnection();

            $sql = "
                SELECT
                    m.id_miembro,
                    m.id_instancia,
                    m.identificador_principal,
                    m.tipo_identificador,
                    m.nombre,
                    m.apellido,
                    m.nombre_completo,
                    m.email,
                    m.telefono,
                    m.fecha_nacimiento,
                    m.genero,
                    m.domicilio_calle,
                    m.domicilio_numero,
                    m.domicilio_ciudad,
                    m.domicilio_provincia,
                    m.domicilio_pais,
                    m.estado,
                    m.tipo_miembro,
                    m.fecha_alta,
                    m.fecha_modificacion
                FROM miembros m
                WHERE m.id_instancia = :id_instancia
            ";

            $params = [':id_instancia' => $id_instancia];

            // Filtro por búsqueda
            if (!empty($filtros['buscar'])) {
                $sql .= " AND (m.identificador_principal LIKE :buscar1
                          OR m.nombre LIKE :buscar2
                          OR m.apellido LIKE :buscar3
                          OR m.nombre_completo LIKE :buscar4
                          OR m.email LIKE :buscar5)";
                $buscarParam = "%{$filtros['buscar']}%";
                $params[':buscar1'] = $buscarParam;
                $params[':buscar2'] = $buscarParam;
                $params[':buscar3'] = $buscarParam;
                $params[':buscar4'] = $buscarParam;
                $params[':buscar5'] = $buscarParam;
            }

            // Filtro por estado
            if (!empty($filtros['estado'])) {
                $sql .= " AND m.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }

            // Filtro por tipo de miembro
            if (!empty($filtros['tipo_miembro'])) {
                $sql .= " AND m.tipo_miembro = :tipo_miembro";
                $params[':tipo_miembro'] = $filtros['tipo_miembro'];
            }

            $sql .= " ORDER BY m.fecha_alta DESC";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("MemberService::getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un miembro por ID
     *
     * @param int $id_miembro
     * @return array|null
     */
    public static function getById(int $id_miembro): ?array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("SELECT * FROM miembros WHERE id_miembro = :id");
            $stmt->execute([':id' => $id_miembro]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("MemberService::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un miembro por identificador (DNI) e instancia
     *
     * @param int $id_instancia
     * @param string $identificador DNI u otro identificador
     * @return array|null
     */
    public static function getByIdentificador(int $id_instancia, string $identificador): ?array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                SELECT * FROM miembros
                WHERE id_instancia = :id_instancia
                AND identificador_principal = :identificador
            ");
            $stmt->execute([
                ':id_instancia' => $id_instancia,
                ':identificador' => $identificador
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("MemberService::getByIdentificador error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo miembro
     *
     * @param array $datos Datos del miembro
     * @return array ['success' => bool, 'id_miembro' => int|null, 'mensaje' => string]
     */
    public static function crear(array $datos): array
    {
        try {
            $conn = self::getConnection();

            // Validar campos requeridos
            if (empty($datos['id_instancia']) || empty($datos['identificador_principal']) ||
                empty($datos['nombre']) || empty($datos['apellido'])) {
                return ['success' => false, 'mensaje' => 'Faltan campos requeridos (id_instancia, identificador, nombre, apellido)'];
            }

            // Limpiar identificador
            $datos['identificador_principal'] = preg_replace('/[^0-9A-Za-z]/', '', $datos['identificador_principal']);

            // Verificar si ya existe
            $existe = self::getByIdentificador($datos['id_instancia'], $datos['identificador_principal']);
            if ($existe) {
                return ['success' => false, 'mensaje' => 'Ya existe un miembro con ese identificador'];
            }

            $sql = "
                INSERT INTO miembros (
                    id_instancia, identificador_principal, tipo_identificador,
                    nombre, apellido, email, telefono, fecha_nacimiento, genero,
                    domicilio_calle, domicilio_numero, domicilio_ciudad,
                    domicilio_provincia, domicilio_pais, estado, tipo_miembro, notas
                ) VALUES (
                    :id_instancia, :identificador_principal, :tipo_identificador,
                    :nombre, :apellido, :email, :telefono, :fecha_nacimiento, :genero,
                    :domicilio_calle, :domicilio_numero, :domicilio_ciudad,
                    :domicilio_provincia, :domicilio_pais, :estado, :tipo_miembro, :notas
                )
            ";

            $stmt = $conn->prepare($sql);
            $tipo_miembro = $datos['tipo_miembro'] ?? 'Estudiante';
            $stmt->execute([
                ':id_instancia' => $datos['id_instancia'],
                ':identificador_principal' => $datos['identificador_principal'],
                ':tipo_identificador' => $datos['tipo_identificador'] ?? 'DNI',
                ':nombre' => trim($datos['nombre']),
                ':apellido' => trim($datos['apellido']),
                ':email' => $datos['email'] ?? null,
                ':telefono' => $datos['telefono'] ?? null,
                ':fecha_nacimiento' => !empty($datos['fecha_nacimiento']) ? $datos['fecha_nacimiento'] : null,
                ':genero' => $datos['genero'] ?? 'No especifica',
                ':domicilio_calle' => $datos['domicilio_calle'] ?? null,
                ':domicilio_numero' => $datos['domicilio_numero'] ?? null,
                ':domicilio_ciudad' => $datos['domicilio_ciudad'] ?? null,
                ':domicilio_provincia' => $datos['domicilio_provincia'] ?? null,
                ':domicilio_pais' => $datos['domicilio_pais'] ?? 'Argentina',
                ':estado' => $datos['estado'] ?? 'Activo',
                ':tipo_miembro' => $tipo_miembro,
                ':notas' => $datos['notas'] ?? null
            ]);

            $id_miembro = $conn->lastInsertId();

            // Agregar rol a la tabla miembro_roles
            self::agregarRol((int)$id_miembro, (int)$datos['id_instancia'], $tipo_miembro);

            return [
                'success' => true,
                'id_miembro' => $id_miembro,
                'mensaje' => 'Miembro creado correctamente'
            ];

        } catch (PDOException $e) {
            error_log("MemberService::crear error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Actualiza un miembro existente
     *
     * @param int $id_miembro
     * @param array $datos Datos a actualizar
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function actualizar(int $id_miembro, array $datos): array
    {
        try {
            $conn = self::getConnection();

            // Construir SET dinámico solo con campos proporcionados
            $campos_permitidos = [
                'identificador_principal', 'tipo_identificador', 'nombre', 'apellido',
                'email', 'telefono', 'fecha_nacimiento', 'genero',
                'domicilio_calle', 'domicilio_numero', 'domicilio_ciudad',
                'domicilio_provincia', 'domicilio_pais', 'estado', 'tipo_miembro', 'notas'
            ];

            $sets = [];
            $params = [':id' => $id_miembro];

            foreach ($campos_permitidos as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $sets[] = "$campo = :$campo";
                    $params[":$campo"] = $datos[$campo];
                }
            }

            if (empty($sets)) {
                return ['success' => false, 'mensaje' => 'No hay campos para actualizar'];
            }

            $sql = "UPDATE miembros SET " . implode(', ', $sets) . " WHERE id_miembro = :id";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            return ['success' => true, 'mensaje' => 'Miembro actualizado correctamente'];

        } catch (PDOException $e) {
            error_log("MemberService::actualizar error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Elimina un miembro (verifica dependencias primero)
     *
     * @param int $id_miembro
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function eliminar(int $id_miembro): array
    {
        try {
            $conn = self::getConnection();

            // Verificar si tiene inscripciones en Certificatum
            // ACTUALIZADO: Usa id_miembro directamente
            try {
                $stmt_check = $conn->prepare("
                    SELECT COUNT(*) FROM verumax_academi.inscripciones
                    WHERE id_miembro = :id
                ");
                $stmt_check->execute([':id' => $id_miembro]);
                $tiene_inscripciones = $stmt_check->fetchColumn() > 0;

                if ($tiene_inscripciones) {
                    return [
                        'success' => false,
                        'mensaje' => 'No se puede eliminar: el miembro tiene inscripciones activas'
                    ];
                }
            } catch (PDOException $e) {
                // Si falla la consulta cross-db, permitir eliminar
                error_log("Advertencia verificando inscripciones: " . $e->getMessage());
            }

            $stmt = $conn->prepare("DELETE FROM miembros WHERE id_miembro = :id");
            $stmt->execute([':id' => $id_miembro]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'mensaje' => 'Miembro no encontrado'];
            }

            return ['success' => true, 'mensaje' => 'Miembro eliminado correctamente'];

        } catch (PDOException $e) {
            error_log("MemberService::eliminar error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene miembros con conteo de inscripciones (para vista de Certificatum)
     *
     * @param int $id_instancia
     * @param string $buscar
     * @return array
     */
    public static function getConInscripciones(int $id_instancia, string $buscar = ''): array
    {
        // DEBUG: Log de entrada
        error_log("MemberService::getConInscripciones - id_instancia: $id_instancia, buscar: '$buscar'");

        try {
            $conn = self::getConnection();

            // Query que une miembros con inscripciones de certifi
            // ACTUALIZADO: Usa miembro_roles para filtrar por rol 'Estudiante'
            $sql = "
                SELECT
                    m.id_miembro,
                    m.id_instancia,
                    m.identificador_principal as dni,
                    m.nombre,
                    m.apellido,
                    m.nombre_completo,
                    m.email,
                    m.telefono,
                    m.estado,
                    m.genero,
                    m.fecha_nacimiento,
                    m.domicilio_ciudad,
                    m.domicilio_provincia,
                    m.domicilio_codigo_postal,
                    m.domicilio_pais,
                    m.profesion,
                    m.lugar_trabajo,
                    m.cargo,
                    mr.rol as tipo_miembro,
                    m.fecha_alta,
                    COALESCE(stats.total_cursos, 0) as total_cursos,
                    COALESCE(stats.cursos_aprobados, 0) as cursos_aprobados,
                    COALESCE(stats.cursos_en_curso, 0) as cursos_en_curso,
                    (SELECT GROUP_CONCAT(mr2.rol SEPARATOR ', ')
                     FROM miembro_roles mr2
                     WHERE mr2.id_miembro = m.id_miembro AND mr2.activo = 1) as todos_los_roles
                FROM miembros m
                INNER JOIN miembro_roles mr ON m.id_miembro = mr.id_miembro
                    AND mr.rol = 'Estudiante' AND mr.activo = 1
                LEFT JOIN (
                    SELECT
                        i.id_miembro,
                        COUNT(DISTINCT i.id_inscripcion) as total_cursos,
                        SUM(CASE WHEN i.estado = 'Aprobado' THEN 1 ELSE 0 END) as cursos_aprobados,
                        SUM(CASE WHEN i.estado = 'En Curso' THEN 1 ELSE 0 END) as cursos_en_curso
                    FROM verumax_academi.inscripciones i
                    WHERE i.id_miembro IS NOT NULL
                    GROUP BY i.id_miembro
                ) stats ON m.id_miembro = stats.id_miembro
                WHERE m.id_instancia = :id_instancia
                AND m.estado = 'Activo'
            ";

            $params = [':id_instancia' => $id_instancia];

            if (!empty($buscar)) {
                $sql .= " AND (m.identificador_principal LIKE :buscar1
                          OR m.nombre LIKE :buscar2
                          OR m.apellido LIKE :buscar3
                          OR m.nombre_completo LIKE :buscar4)";
                $buscarParam = "%$buscar%";
                $params[':buscar1'] = $buscarParam;
                $params[':buscar2'] = $buscarParam;
                $params[':buscar3'] = $buscarParam;
                $params[':buscar4'] = $buscarParam;
            }

            $sql .= " ORDER BY m.fecha_alta DESC";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // DEBUG: Log de resultados
            error_log("MemberService::getConInscripciones - Encontrados: " . count($results) . " registros");

            return $results;

        } catch (PDOException $e) {
            error_log("MemberService::getConInscripciones ERROR: " . $e->getMessage());
            error_log("MemberService::getConInscripciones - Intentando fallback getAll...");
            // Fallback: solo miembros sin stats
            $fallback = self::getAll($id_instancia, ['buscar' => $buscar]);
            error_log("MemberService::getConInscripciones - Fallback encontró: " . count($fallback) . " registros");
            return $fallback;
        }
    }

    /**
     * Importa miembros desde texto CSV
     *
     * @param int $id_instancia
     * @param string $texto Contenido CSV
     * @param string $tipo_miembro Tipo por defecto
     * @return array Estadísticas
     */
    public static function importarDesdeTexto(int $id_instancia, string $texto, string $tipo_miembro = 'Estudiante'): array
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

            // Formato esperado: DNI, Nombre, Apellido [, Email] [, Telefono]
            $partes = array_map('trim', str_getcsv($linea));

            if (count($partes) < 2) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": formato inválido";
                continue;
            }

            $dni = preg_replace('/[^0-9]/', '', $partes[0]);
            if (empty($dni)) {
                $stats['errores'][] = "Línea " . ($num + 1) . ": DNI inválido";
                continue;
            }

            // Si solo hay 2 campos, asumir nombre_completo
            if (count($partes) == 2) {
                $nombre_partes = explode(' ', $partes[1], 2);
                $nombre = $nombre_partes[0];
                $apellido = $nombre_partes[1] ?? '';
            } else {
                $nombre = $partes[1];
                $apellido = $partes[2] ?? '';
            }

            $email = $partes[3] ?? null;
            $telefono = $partes[4] ?? null;

            // Verificar si existe
            $existe = self::getByIdentificador($id_instancia, $dni);

            if ($existe) {
                // Actualizar
                $result = self::actualizar($existe['id_miembro'], [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'email' => $email,
                    'telefono' => $telefono
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
                    'identificador_principal' => $dni,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'email' => $email,
                    'telefono' => $telefono,
                    'tipo_miembro' => $tipo_miembro
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

    // ==================== GESTIÓN DE ROLES ====================

    /**
     * Agrega un rol a un miembro
     *
     * @param int $id_miembro
     * @param int $id_instancia
     * @param string $rol
     * @return array
     */
    public static function agregarRol(int $id_miembro, int $id_instancia, string $rol): array
    {
        try {
            $conn = self::getConnection();

            // Verificar si ya tiene el rol
            $stmt = $conn->prepare("
                SELECT id_miembro_rol, activo FROM miembro_roles
                WHERE id_miembro = :id_miembro AND rol = :rol AND id_instancia = :id_instancia
            ");
            $stmt->execute([
                ':id_miembro' => $id_miembro,
                ':rol' => $rol,
                ':id_instancia' => $id_instancia
            ]);
            $existente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                // Si existe pero está inactivo, reactivar
                if (!$existente['activo']) {
                    $stmt = $conn->prepare("
                        UPDATE miembro_roles SET activo = 1, fecha_desde = CURRENT_DATE
                        WHERE id_miembro_rol = :id
                    ");
                    $stmt->execute([':id' => $existente['id_miembro_rol']]);
                    return ['success' => true, 'mensaje' => 'Rol reactivado'];
                }
                return ['success' => true, 'mensaje' => 'El miembro ya tiene este rol'];
            }

            // Insertar nuevo rol
            $stmt = $conn->prepare("
                INSERT INTO miembro_roles (id_miembro, id_instancia, rol, activo, fecha_desde)
                VALUES (:id_miembro, :id_instancia, :rol, 1, CURRENT_DATE)
            ");
            $stmt->execute([
                ':id_miembro' => $id_miembro,
                ':id_instancia' => $id_instancia,
                ':rol' => $rol
            ]);

            return ['success' => true, 'mensaje' => 'Rol agregado', 'id_miembro_rol' => $conn->lastInsertId()];

        } catch (PDOException $e) {
            error_log("MemberService::agregarRol error: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Verifica si un miembro tiene un rol específico
     *
     * @param int $id_miembro
     * @param string $rol
     * @return bool
     */
    public static function tieneRol(int $id_miembro, string $rol): bool
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                SELECT 1 FROM miembro_roles
                WHERE id_miembro = :id_miembro AND rol = :rol AND activo = 1
            ");
            $stmt->execute([':id_miembro' => $id_miembro, ':rol' => $rol]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtiene todos los roles activos de un miembro
     *
     * @param int $id_miembro
     * @return array
     */
    public static function getRoles(int $id_miembro): array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                SELECT rol, fecha_desde, fecha_hasta
                FROM miembro_roles
                WHERE id_miembro = :id_miembro AND activo = 1
            ");
            $stmt->execute([':id_miembro' => $id_miembro]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Desactiva un rol de un miembro
     *
     * @param int $id_miembro
     * @param string $rol
     * @return array
     */
    public static function quitarRol(int $id_miembro, string $rol): array
    {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare("
                UPDATE miembro_roles
                SET activo = 0, fecha_hasta = CURRENT_DATE
                WHERE id_miembro = :id_miembro AND rol = :rol AND activo = 1
            ");
            $stmt->execute([':id_miembro' => $id_miembro, ':rol' => $rol]);

            return ['success' => true, 'mensaje' => 'Rol desactivado'];
        } catch (PDOException $e) {
            return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }
}
