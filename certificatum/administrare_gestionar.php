<?php
/**
 * ADMINISTRARE GESTIONAR
 * Funciones para listar, editar y eliminar datos
 * Sistema CERTIFICATUM - VERUMax
 *
 * ACTUALIZADO: 2025-12-06 - Migración a verumax_nexus.miembros como fuente de verdad
 */

require_once 'config.php';

use VERUMax\Services\MemberService;
use VERUMax\Services\CursoService;
use VERUMax\Services\InscripcionService;
use VERUMax\Services\DatabaseService;

/**
 * Obtiene todos los estudiantes de una institución
 * ACTUALIZADO: Ahora lee de verumax_nexus.miembros via MemberService
 *
 * @param int|string $id_instancia ID numérico de la instancia (preferido) o slug (legacy)
 * @param string $buscar Término de búsqueda opcional
 * @return array Lista de estudiantes (con campos compatibles hacia atrás)
 */
function obtenerEstudiantes($id_instancia, $buscar = '') {
    try {
        // Convertir slug a id_instancia si es necesario
        if (!is_numeric($id_instancia)) {
            $id_instancia = obtenerIdInstanciaPorSlug($id_instancia);
            if (!$id_instancia) {
                return [];
            }
        }

        // Usar MemberService de Nexus
        $miembros = MemberService::getConInscripciones((int)$id_instancia, $buscar);

        // Mapear campos para compatibilidad con código existente
        return array_map(function($m) {
            return [
                'id_estudiante' => $m['id_miembro'],  // Alias para compatibilidad
                'id_miembro' => $m['id_miembro'],
                'id_instancia' => $m['id_instancia'],
                'dni' => $m['identificador_principal'] ?? $m['dni'] ?? '',
                'nombre' => $m['nombre'] ?? '',
                'apellido' => $m['apellido'] ?? '',
                'nombre_completo' => $m['nombre_completo'] ?? '',
                'email' => $m['email'] ?? '',
                'telefono' => $m['telefono'] ?? '',
                'estado' => $m['estado'] ?? 'Activo',
                'tipo_miembro' => $m['tipo_miembro'] ?? 'Estudiante',
                'todos_los_roles' => $m['todos_los_roles'] ?? '',
                'fecha_registro' => $m['fecha_alta'] ?? $m['created_at'] ?? null,
                'total_cursos' => $m['total_cursos'] ?? 0,
                'cursos_aprobados' => $m['cursos_aprobados'] ?? 0,
                'cursos_en_curso' => $m['cursos_en_curso'] ?? 0,
                // Campos adicionales para edición
                'genero' => $m['genero'] ?? 'Prefiero no especificar',
                'fecha_nacimiento' => $m['fecha_nacimiento'] ?? '',
                'domicilio_ciudad' => $m['domicilio_ciudad'] ?? '',
                'domicilio_provincia' => $m['domicilio_provincia'] ?? '',
                'domicilio_codigo_postal' => $m['domicilio_codigo_postal'] ?? '',
                'domicilio_pais' => $m['domicilio_pais'] ?? 'AR',
                // Campos laborales
                'profesion' => $m['profesion'] ?? '',
                'lugar_trabajo' => $m['lugar_trabajo'] ?? '',
                'cargo' => $m['cargo'] ?? '',
            ];
        }, $miembros);

    } catch (Exception $e) {
        error_log("Error obteniendo estudiantes: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene id_instancia a partir de un slug
 *
 * @param string $slug
 * @return int|null
 */
function obtenerIdInstanciaPorSlug($slug) {
    try {
        // La tabla instances está en verumax_general, no en identitas
        $conn = DatabaseService::get('general');
        $stmt = $conn->prepare("SELECT id_instancia FROM instances WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetchColumn() ?: null;
    } catch (PDOException $e) {
        error_log("Error obteniendo id_instancia: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtiene todos los cursos de una institución
 * ACTUALIZADO: Ahora lee de verumax_academi.cursos via CursoService
 *
 * @param int|null $id_instancia ID de la instancia (null = todos los cursos globales)
 * @param bool $soloActivos Filtrar solo cursos activos
 * @param string $buscar Término de búsqueda opcional
 * @return array Lista de cursos
 */
function obtenerCursos($id_instancia = null, $soloActivos = false, $buscar = '') {
    try {
        if ($id_instancia === null) {
            return []; // Requiere id_instancia
        }

        // Construir filtros para CursoService
        $filtros = [];
        if (!empty($buscar)) {
            $filtros['buscar'] = $buscar;
        }
        if ($soloActivos) {
            $filtros['activo'] = 1;
        }

        // Usar CursoService de Academicus
        $cursos = CursoService::getAll((int)$id_instancia, $filtros);

        // Mapear campos para compatibilidad con código existente
        return array_map(function($c) {
            return [
                'id_curso' => $c['id_curso'],
                'id_instancia' => $c['id_instancia'],
                'codigo_curso' => $c['codigo_curso'],
                'nombre_curso' => $c['nombre_curso'],
                'carga_horaria' => $c['carga_horaria'],
                'duracion_semanas' => $c['duracion_semanas'] ?? null,
                'descripcion' => $c['descripcion'] ?? null,
                'categoria' => $c['categoria'] ?? null,
                'tipo_curso' => $c['tipo_curso'] ?? 'Curso',
                'nivel' => $c['nivel'] ?? 'Todos los niveles',
                'modalidad' => $c['modalidad'] ?? 'Virtual',
                'fecha_inicio' => $c['fecha_inicio'] ?? null,
                'fecha_fin' => $c['fecha_fin'] ?? null,
                'ciudad_emision' => $c['ciudad_emision'] ?? null,
                'cupo_maximo' => $c['cupo_maximo'] ?? null,
                'activo' => $c['activo'],
                'total_inscripciones' => $c['total_inscripciones'] ?? 0,
                'id_template' => $c['id_template'] ?? null,
                'firmante_1_nombre' => $c['firmante_1_nombre'] ?? null,
                'firmante_1_cargo' => $c['firmante_1_cargo'] ?? null,
                'firmante_2_nombre' => $c['firmante_2_nombre'] ?? null,
                'firmante_2_cargo' => $c['firmante_2_cargo'] ?? null,
                'usar_firmante_1' => $c['usar_firmante_1'] ?? 1,
                'usar_firmante_2' => $c['usar_firmante_2'] ?? 1,
                'firmante_1_firma_url' => $c['firmante_1_firma_url'] ?? null,
                'firmante_2_firma_url' => $c['firmante_2_firma_url'] ?? null,
                'usar_demora_global' => $c['usar_demora_global'] ?? 1,
                'demora_certificado_horas' => $c['demora_certificado_horas'] ?? null,
                'demora_tipo' => $c['demora_tipo'] ?? 'inmediato',
                'demora_fecha' => $c['demora_fecha'] ?? null,
            ];
        }, $cursos);

    } catch (Exception $e) {
        error_log("Error obteniendo cursos: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene todas las inscripciones de una institución
 * ACTUALIZADO: Usa InscripcionService de Academicus
 *
 * @param int|string $id_instancia ID numérico de la instancia o slug
 * @param string $estado Filtrar por estado (opcional)
 * @param string $buscar Término de búsqueda opcional
 * @return array Lista de inscripciones
 */
function obtenerInscripciones($id_instancia, $estado = '', $buscar = '') {
    try {
        // Convertir slug a id_instancia si es necesario
        if (!is_numeric($id_instancia)) {
            $id_instancia = obtenerIdInstanciaPorSlug($id_instancia);
            if (!$id_instancia) {
                return [];
            }
        }

        // Construir filtros para InscripcionService
        $filtros = [];
        if (!empty($estado)) {
            $filtros['estado'] = $estado;
        }
        if (!empty($buscar)) {
            $filtros['buscar'] = $buscar;
        }

        // Usar InscripcionService de Academicus
        $inscripciones = InscripcionService::getAll((int)$id_instancia, $filtros);

        // Mapear campos para compatibilidad con código existente
        return array_map(function($i) {
            return [
                'id_inscripcion' => $i['id_inscripcion'],
                'id_estudiante' => $i['id_miembro'],  // Alias para compatibilidad
                'id_miembro' => $i['id_miembro'],
                'id_curso' => $i['id_curso'],
                'dni' => $i['dni'] ?? '',
                'nombre_completo' => $i['nombre_completo'] ?? '',
                'email' => $i['email'] ?? '',
                'codigo_curso' => $i['codigo_curso'] ?? '',
                'nombre_curso' => $i['nombre_curso'] ?? '',
                'carga_horaria' => $i['carga_horaria'] ?? 0,
                'estado' => $i['estado'] ?? 'Inscrito',
                'fecha_inscripcion' => $i['fecha_inscripcion'] ?? null,
                'fecha_inicio' => $i['fecha_inicio'] ?? null,
                'fecha_finalizacion' => $i['fecha_finalizacion'] ?? null,
                'nota_final' => $i['nota_final'] ?? null,
                'asistencia' => $i['asistencia_porcentaje'] ?? $i['asistencia'] ?? null,
                'estado_pago' => $i['estado_pago'] ?? 'Pendiente',
                'certificado_emitido' => $i['certificado_emitido'] ?? 0,
                'observaciones' => $i['observaciones'] ?? null,
                // Campos para cálculo de disponibilidad del certificado
                'usar_demora_global' => $i['usar_demora_global'] ?? 1,
                'demora_tipo' => $i['demora_tipo'] ?? 'inmediato',
                'demora_certificado_horas' => $i['demora_certificado_horas'] ?? null,
                'demora_fecha' => $i['demora_fecha'] ?? null,
            ];
        }, $inscripciones);

    } catch (Exception $e) {
        error_log("Error obteniendo inscripciones: " . $e->getMessage());
        return [];
    }
}

/**
 * Crea un nuevo estudiante/miembro
 * ACTUALIZADO: Ahora escribe en verumax_nexus.miembros via MemberService
 *
 * @param string|int $institucion Slug o ID de la institución
 * @param string $dni DNI del estudiante
 * @param string $nombre Nombre (o nombre_completo para compatibilidad)
 * @param string $apellido Apellido (opcional si se pasa nombre_completo)
 * @param array $extras Campos adicionales opcionales
 * @return array|string Array con ID si éxito, string con error si falla
 */
function crearEstudiante($institucion, $dni, $nombre, $apellido = '', $extras = []) {
    try {
        // Obtener id_instancia
        $id_instancia = is_numeric($institucion)
            ? (int)$institucion
            : obtenerIdInstanciaPorSlug($institucion);

        if (!$id_instancia) {
            return 'Institución no encontrada';
        }

        // Si no hay apellido, intentar separar de nombre_completo
        if (empty($apellido) && strpos($nombre, ' ') !== false) {
            $partes = explode(' ', trim($nombre), 2);
            $nombre = $partes[0];
            $apellido = $partes[1] ?? '';
        }

        // Preparar datos
        $datos = array_merge([
            'id_instancia' => $id_instancia,
            'identificador_principal' => $dni,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'tipo_miembro' => 'Estudiante'
        ], $extras);

        // Usar MemberService
        $resultado = MemberService::crear($datos);

        if ($resultado['success']) {
            return [
                'id_estudiante' => $resultado['id_miembro'],
                'id_miembro' => $resultado['id_miembro'],
                'success' => true
            ];
        } else {
            return $resultado['mensaje'];
        }

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

/**
 * Procesa archivo CSV/Excel de estudiantes
 * ACTUALIZADO: Usa MemberService para importar a Nexus
 *
 * @param array $archivo Datos del archivo subido ($_FILES)
 * @param string|int $institucion Slug o ID de la institución
 * @return array Estadísticas del procesamiento
 */
function procesarArchivoEstudiantes($archivo, $institucion) {
    $estadisticas = [
        'estudiantes_insertados' => 0,
        'estudiantes_actualizados' => 0,
        'errores' => []
    ];

    try {
        // Obtener id_instancia
        $id_instancia = is_numeric($institucion)
            ? (int)$institucion
            : obtenerIdInstanciaPorSlug($institucion);

        if (!$id_instancia) {
            $estadisticas['errores'][] = 'Institución no encontrada';
            return $estadisticas;
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            $contenido = file_get_contents($archivo['tmp_name']);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $estadisticas['errores'][] = 'Formato Excel no soportado aún. Por favor usa CSV.';
            return $estadisticas;
        } else {
            $estadisticas['errores'][] = 'Formato de archivo no soportado';
            return $estadisticas;
        }

        // Usar MemberService para importar
        $resultado = MemberService::importarDesdeTexto($id_instancia, $contenido, 'Estudiante');

        return [
            'estudiantes_insertados' => $resultado['insertados'],
            'estudiantes_actualizados' => $resultado['actualizados'],
            'errores' => $resultado['errores']
        ];

    } catch (Exception $e) {
        $estadisticas['errores'][] = 'Error: ' . $e->getMessage();
        return $estadisticas;
    }
}

/**
 * Procesa archivo CSV/Excel de docentes
 *
 * @param array $archivo Archivo subido ($_FILES)
 * @param string|int $institucion Slug o ID de la institución
 * @return array Estadísticas del procesamiento
 */
function procesarArchivoDocentes($archivo, $institucion) {
    $estadisticas = [
        'docentes_insertados' => 0,
        'docentes_actualizados' => 0,
        'errores' => []
    ];

    try {
        // Obtener id_instancia
        $id_instancia = is_numeric($institucion)
            ? (int)$institucion
            : obtenerIdInstanciaPorSlug($institucion);

        if (!$id_instancia) {
            $estadisticas['errores'][] = 'Institución no encontrada';
            return $estadisticas;
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            $contenido = file_get_contents($archivo['tmp_name']);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $estadisticas['errores'][] = 'Formato Excel no soportado aún. Por favor usa CSV.';
            return $estadisticas;
        } else {
            $estadisticas['errores'][] = 'Formato de archivo no soportado';
            return $estadisticas;
        }

        // Usar procesarSoloDocentes para procesar el contenido
        $resultado = procesarSoloDocentes($contenido, $id_instancia);

        return [
            'docentes_insertados' => $resultado['docentes_insertados'],
            'docentes_actualizados' => $resultado['docentes_actualizados'],
            'errores' => $resultado['errores']
        ];

    } catch (Exception $e) {
        $estadisticas['errores'][] = 'Error: ' . $e->getMessage();
        return $estadisticas;
    }
}

/**
 * Actualiza un estudiante/miembro
 * ACTUALIZADO: Ahora escribe en verumax_nexus.miembros via MemberService
 *
 * @param int $id_miembro ID del miembro (antes id_estudiante)
 * @param array|string $datos Array de datos o DNI (para compatibilidad)
 * @param string $nombre_completo Solo para compatibilidad hacia atrás
 * @return array
 */
function actualizarEstudiante($id_miembro, $datos, $nombre_completo = null) {
    try {
        // Compatibilidad hacia atrás: si $datos es string, es el DNI
        if (is_string($datos)) {
            $dni = $datos;
            $datos = ['identificador_principal' => $dni];

            if ($nombre_completo) {
                $partes = explode(' ', trim($nombre_completo), 2);
                $datos['nombre'] = $partes[0];
                $datos['apellido'] = $partes[1] ?? '';
            }
        }

        $resultado = MemberService::actualizar((int)$id_miembro, $datos);
        return $resultado;

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Elimina un estudiante/miembro
 * ACTUALIZADO: Ahora elimina de verumax_nexus.miembros via MemberService
 *
 * @param int $id_miembro ID del miembro
 * @return array
 */
function eliminarEstudiante($id_miembro) {
    try {
        $resultado = MemberService::eliminar((int)$id_miembro);
        return $resultado;
    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Actualiza un curso
 * ACTUALIZADO: Usa CursoService de Academicus
 * ACTUALIZADO 2025-12-26: Agregado soporte para id_template
 * ACTUALIZADO 2025-12-28: Agregado soporte para firmantes
 *
 * @param int $id_curso
 * @param string $codigo_curso
 * @param string $nombre_curso
 * @param int $carga_horaria
 * @param int $activo
 * @param int|null $id_template Template de certificado (NULL = usar sistema actual)
 * @param int $usar_firmante_1 Usar firmante 1 (1=si, 0=no)
 * @param int $usar_firmante_2 Usar firmante 2 (1=si, 0=no)
 * @param string $firmante_1_nombre Nombre del firmante 1 (vacío = usar institución)
 * @param string $firmante_1_cargo Cargo del firmante 1 (vacío = usar institución)
 * @param string $firmante_2_nombre Nombre del firmante 2 (vacío = usar institución)
 * @param string $firmante_2_cargo Cargo del firmante 2 (vacío = usar institución)
 * @param string $firmante_1_firma_url URL de la firma 1 del curso (opcional)
 * @param string $firmante_2_firma_url URL de la firma 2 del curso (opcional)
 */
function actualizarCurso($id_curso, $codigo_curso, $nombre_curso, $carga_horaria, $activo, $id_template = null,
    $usar_firmante_1 = 1, $usar_firmante_2 = 1,
    $firmante_1_nombre = '', $firmante_1_cargo = '',
    $firmante_2_nombre = '', $firmante_2_cargo = '',
    $firmante_1_firma_url = '', $firmante_2_firma_url = '') {

    $datos = [
        'codigo_curso' => $codigo_curso,
        'nombre_curso' => $nombre_curso,
        'carga_horaria' => $carga_horaria,
        'activo' => $activo,
        'usar_firmante_1' => $usar_firmante_1,
        'usar_firmante_2' => $usar_firmante_2,
        'firmante_1_nombre' => $firmante_1_nombre ?: null,
        'firmante_1_cargo' => $firmante_1_cargo ?: null,
        'firmante_2_nombre' => $firmante_2_nombre ?: null,
        'firmante_2_cargo' => $firmante_2_cargo ?: null,
        'firmante_1_firma_url' => $firmante_1_firma_url ?: null,
        'firmante_2_firma_url' => $firmante_2_firma_url ?: null
    ];

    // Solo incluir id_template si se proporciona explícitamente
    // (incluyendo null para desasignar)
    if (func_num_args() >= 6) {
        $datos['id_template'] = $id_template;
    }

    return CursoService::actualizar((int)$id_curso, $datos);
}

/**
 * Elimina un curso (solo si no tiene inscripciones)
 * ACTUALIZADO: Usa CursoService de Academicus
 */
function eliminarCurso($id_curso) {
    try {
        $conn = getCertDBConnection();

        // Verificar si tiene inscripciones
        $stmt_check = $conn->prepare("
            SELECT COUNT(*) FROM inscripciones WHERE id_curso = :id
        ");
        $stmt_check->execute([':id' => $id_curso]);
        $tiene_inscripciones = $stmt_check->fetchColumn() > 0;

        if ($tiene_inscripciones) {
            // En lugar de eliminar, desactivar via CursoService
            return CursoService::actualizar((int)$id_curso, ['activo' => 0]);
        }

        // Eliminar competencias del curso primero (siguen en certifi)
        $stmt_comp = $conn->prepare("DELETE FROM competencias WHERE id_curso = :id");
        $stmt_comp->execute([':id' => $id_curso]);

        // Eliminar curso via CursoService
        return CursoService::eliminar((int)$id_curso);

    } catch (PDOException $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Crea una nueva inscripción
 * ACTUALIZADO: Usa InscripcionService de Academicus
 *
 * @param int $id_miembro ID del miembro
 * @param int $id_curso ID del curso
 * @param string $estado Estado inicial
 * @param array $extras Campos adicionales opcionales
 * @return array Resultado
 */
function crearInscripcion($id_miembro, $id_curso, $estado = 'Inscrito', $extras = []) {
    $datos = array_merge([
        'id_miembro' => $id_miembro,
        'id_curso' => $id_curso,
        'estado' => $estado,
    ], $extras);

    return InscripcionService::crear($datos);
}

/**
 * Actualiza una inscripción
 * ACTUALIZADO: Usa InscripcionService de Academicus
 */
function actualizarInscripcion($id_inscripcion, $estado, $fecha_inicio, $fecha_finalizacion, $nota_final, $asistencia) {
    return InscripcionService::actualizar((int)$id_inscripcion, [
        'estado' => $estado,
        'fecha_inicio' => $fecha_inicio ?: null,
        'fecha_finalizacion' => $fecha_finalizacion ?: null,
        'nota_final' => $nota_final ?: null,
        'asistencia' => $asistencia ?: null,
    ]);
}

/**
 * Elimina una inscripción y sus datos relacionados
 * ACTUALIZADO: Usa InscripcionService de Academicus (soft delete)
 */
function eliminarInscripcion($id_inscripcion) {
    return InscripcionService::eliminar((int)$id_inscripcion);
}

/**
 * Obtiene la trayectoria de una inscripción
 * ACTUALIZADO: Usa InscripcionService de Academicus
 */
function obtenerTrayectoria($id_inscripcion) {
    return InscripcionService::getTrayectoria((int)$id_inscripcion);
}

/**
 * Agrega un evento a la trayectoria de una inscripción
 * ACTUALIZADO: Usa InscripcionService de Academicus
 */
function agregarEventoTrayectoria($id_inscripcion, $evento, $detalle = null, $fecha = null) {
    return InscripcionService::agregarEvento((int)$id_inscripcion, $evento, $detalle, $fecha);
}

/**
 * Procesa archivo CSV de inscripciones
 * ACTUALIZADO: Usa InscripcionService de Academicus
 *
 * @param array $archivo Datos del archivo subido ($_FILES)
 * @param int $id_instancia ID de la instancia
 * @return array Estadísticas del procesamiento
 */
function procesarArchivoInscripciones($archivo, $id_instancia) {
    $estadisticas = [
        'inscripciones_insertadas' => 0,
        'inscripciones_actualizadas' => 0,
        'errores' => []
    ];

    try {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            $contenido = file_get_contents($archivo['tmp_name']);
        } else {
            $estadisticas['errores'][] = 'Formato de archivo no soportado. Use CSV.';
            return $estadisticas;
        }

        $resultado = InscripcionService::importarDesdeTexto((int)$id_instancia, $contenido);

        return [
            'inscripciones_insertadas' => $resultado['insertados'],
            'inscripciones_actualizadas' => $resultado['actualizados'],
            'errores' => $resultado['errores']
        ];

    } catch (Exception $e) {
        $estadisticas['errores'][] = 'Error: ' . $e->getMessage();
        return $estadisticas;
    }
}

/**
 * Obtiene las competencias de un curso
 *
 * @param int $id_curso ID del curso
 * @return array Lista de competencias
 */
function obtenerCompetenciasCurso($id_curso) {
    try {
        $conn = getCertDBConnection();

        $stmt = $conn->prepare("
            SELECT id_competencia, competencia, descripcion, orden
            FROM competencias
            WHERE id_curso = :id_curso AND activo = 1
            ORDER BY orden ASC
        ");
        $stmt->execute([':id_curso' => $id_curso]);
        return $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log("Error obteniendo competencias: " . $e->getMessage());
        return [];
    }
}

/**
 * Guarda competencias de un curso
 *
 * @param int $id_curso ID del curso
 * @param array $competencias Lista de competencias ['texto1', 'texto2', ...]
 * @return array Resultado
 */
function guardarCompetenciasCurso($id_curso, $competencias) {
    try {
        $conn = getCertDBConnection();
        $conn->beginTransaction();

        // Desactivar competencias existentes (soft delete)
        $stmt_deactivate = $conn->prepare("UPDATE competencias SET activo = 0 WHERE id_curso = :id_curso");
        $stmt_deactivate->execute([':id_curso' => $id_curso]);

        // Insertar nuevas competencias
        $stmt_insert = $conn->prepare("
            INSERT INTO competencias (id_curso, competencia, orden, activo)
            VALUES (:id_curso, :competencia, :orden, 1)
        ");

        $orden = 0;
        foreach ($competencias as $competencia) {
            $competencia = trim($competencia);
            if (!empty($competencia)) {
                $orden++;
                $stmt_insert->execute([
                    ':id_curso' => $id_curso,
                    ':competencia' => $competencia,
                    ':orden' => $orden
                ]);
            }
        }

        $conn->commit();
        return ['success' => true, 'mensaje' => "$orden competencias guardadas"];

    } catch (PDOException $e) {
        if (isset($conn)) $conn->rollBack();
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

// =========================================================================
// FUNCIONES PARA DOCENTES/FORMADORES
// Los docentes se guardan en verumax_nexus.miembros con tipo_miembro='Docente'
// Sus participaciones se guardan en verumax_certifi.participaciones_docente
// =========================================================================

/**
 * Obtiene todos los docentes de una institución
 * Lee de verumax_nexus.miembros WHERE tipo_miembro IN ('Docente', 'ambos')
 *
 * @param int|string $id_instancia ID numérico de la instancia o slug
 * @param string $buscar Término de búsqueda opcional
 * @return array Lista de docentes
 */
function obtenerDocentes($id_instancia, $buscar = '') {
    try {
        // Convertir slug a id_instancia si es necesario
        if (!is_numeric($id_instancia)) {
            $id_instancia = obtenerIdInstanciaPorSlug($id_instancia);
            if (!$id_instancia) {
                return [];
            }
        }

        $conn = DatabaseService::get('nexus');

        // ACTUALIZADO: Usa miembro_roles para filtrar por rol 'Docente'
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
                m.domicilio_ciudad,
                m.domicilio_provincia,
                m.domicilio_codigo_postal,
                m.domicilio_pais,
                m.profesion,
                m.lugar_trabajo,
                m.cargo,
                m.genero,
                m.estado,
                mr.rol as tipo_miembro,
                m.campo_texto_1 as especialidad,
                m.campo_texto_2 as titulo,
                m.fecha_alta,
                (SELECT COUNT(*) FROM verumax_certifi.participaciones_docente pd
                 WHERE pd.id_miembro = m.id_miembro AND pd.activo = 1) as total_participaciones,
                (SELECT GROUP_CONCAT(mr2.rol SEPARATOR ', ')
                 FROM miembro_roles mr2
                 WHERE mr2.id_miembro = m.id_miembro AND mr2.activo = 1) as todos_los_roles
            FROM miembros m
            INNER JOIN miembro_roles mr ON m.id_miembro = mr.id_miembro
                AND mr.rol = 'Docente' AND mr.activo = 1
            WHERE m.id_instancia = :id_instancia
            AND m.estado = 'Activo'
        ";

        if (!empty($buscar)) {
            $sql .= " AND (m.identificador_principal LIKE :buscar
                      OR m.nombre_completo LIKE :buscar
                      OR m.email LIKE :buscar)";
        }

        $sql .= " ORDER BY m.apellido, m.nombre";

        $stmt = $conn->prepare($sql);
        $params = [':id_instancia' => $id_instancia];
        if (!empty($buscar)) {
            $params[':buscar'] = "%$buscar%";
        }

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error obteniendo docentes: " . $e->getMessage());
        return [];
    }
}

/**
 * Crea un nuevo docente en verumax_nexus.miembros
 *
 * @param int|string $institucion ID o slug de la institución
 * @param string $dni DNI del docente
 * @param string $nombre Nombre
 * @param string $apellido Apellido
 * @param string $email Email (opcional)
 * @param string $pais Código de país ISO (opcional, default 'AR')
 * @param string $especialidad Especialidad (opcional)
 * @param string $titulo Título académico (opcional)
 * @return array Resultado
 */
function crearDocente($institucion, $dni, $nombre, $apellido, $email = '', $pais = 'AR', $especialidad = '', $titulo = '', $genero = 'Prefiero no especificar', $extras = []) {
    try {
        $id_instancia = is_numeric($institucion)
            ? (int)$institucion
            : obtenerIdInstanciaPorSlug($institucion);

        if (!$id_instancia) {
            return ['success' => false, 'mensaje' => 'Institución no encontrada'];
        }

        // Limpiar y normalizar datos
        $nombre = strtoupper(trim($nombre));
        $apellido = strtoupper(trim($apellido));
        $dni = strtoupper(trim($dni));

        if (empty($nombre) || empty($apellido)) {
            return ['success' => false, 'mensaje' => 'Nombre y Apellido son requeridos'];
        }

        $conn = DatabaseService::get('nexus');

        // Verificar si ya existe el miembro
        $stmt_check = $conn->prepare("
            SELECT m.id_miembro, m.nombre, m.apellido, m.email, m.domicilio_pais as pais
            FROM miembros m
            WHERE m.id_instancia = :id_instancia AND m.identificador_principal = :dni
        ");
        $stmt_check->execute([':id_instancia' => $id_instancia, ':dni' => $dni]);
        $existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            // Verificar si ya tiene rol Docente
            $ya_es_docente = MemberService::tieneRol((int)$existente['id_miembro'], 'Docente');
            if ($ya_es_docente) {
                return ['success' => false, 'mensaje' => 'Ya existe un docente con ese DNI'];
            }

            // Existe pero no es docente - verificar diferencias con datos del formulario
            $diferencias = [];
            if (strtoupper(trim($existente['nombre'] ?? '')) !== $nombre) {
                $diferencias['nombre'] = [
                    'existente' => $existente['nombre'] ?? '',
                    'nuevo' => $nombre
                ];
            }
            if (strtoupper(trim($existente['apellido'] ?? '')) !== $apellido) {
                $diferencias['apellido'] = [
                    'existente' => $existente['apellido'] ?? '',
                    'nuevo' => $apellido
                ];
            }
            if (strtolower(trim($existente['email'] ?? '')) !== strtolower(trim($email))) {
                $diferencias['email'] = [
                    'existente' => $existente['email'] ?? '',
                    'nuevo' => $email
                ];
            }
            if (($existente['pais'] ?? 'AR') !== $pais) {
                $diferencias['pais'] = [
                    'existente' => $existente['pais'] ?? 'AR',
                    'nuevo' => $pais
                ];
            }

            // Si hay diferencias, retornar para confirmación
            if (!empty($diferencias)) {
                return [
                    'success' => false,
                    'requiere_confirmacion' => true,
                    'mensaje' => 'El DNI ya existe con datos diferentes',
                    'id_miembro' => $existente['id_miembro'],
                    'diferencias' => $diferencias,
                    'datos_docente' => [
                        'especialidad' => $especialidad,
                        'titulo' => $titulo
                    ]
                ];
            }

            // Sin diferencias: agregar rol Docente y actualizar campos de docente
            $stmt_update = $conn->prepare("
                UPDATE miembros SET
                    campo_texto_1 = :especialidad,
                    campo_texto_2 = :titulo
                WHERE id_miembro = :id_miembro
            ");
            $stmt_update->execute([
                ':id_miembro' => $existente['id_miembro'],
                ':especialidad' => $especialidad,
                ':titulo' => $titulo
            ]);

            // Agregar rol Docente
            MemberService::agregarRol((int)$existente['id_miembro'], $id_instancia, 'Docente');

            return ['success' => true, 'mensaje' => 'Rol Docente agregado al miembro existente', 'id_docente' => $existente['id_miembro']];
        }

        // Insertar nuevo miembro como docente
        $stmt = $conn->prepare("
            INSERT INTO miembros (id_instancia, identificador_principal, nombre, apellido, email, domicilio_pais,
                domicilio_ciudad, domicilio_provincia, domicilio_codigo_postal,
                profesion, lugar_trabajo, cargo,
                tipo_miembro, campo_texto_1, campo_texto_2, genero, estado)
            VALUES (:id_instancia, :dni, :nombre, :apellido, :email, :pais,
                :ciudad, :provincia, :cp,
                :profesion, :trabajo, :cargo,
                'Docente', :especialidad, :titulo, :genero, 'Activo')
        ");
        $stmt->execute([
            ':id_instancia' => $id_instancia,
            ':dni' => $dni,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':email' => $email,
            ':pais' => $pais,
            ':ciudad' => $extras['domicilio_ciudad'] ?? null,
            ':provincia' => $extras['domicilio_provincia'] ?? null,
            ':cp' => $extras['domicilio_codigo_postal'] ?? null,
            ':profesion' => $extras['profesion'] ?? null,
            ':trabajo' => $extras['lugar_trabajo'] ?? null,
            ':cargo' => $extras['cargo'] ?? null,
            ':especialidad' => $especialidad,
            ':titulo' => $titulo,
            ':genero' => $genero
        ]);

        $id_miembro = $conn->lastInsertId();

        // Agregar rol Docente
        MemberService::agregarRol((int)$id_miembro, $id_instancia, 'Docente');

        return ['success' => true, 'mensaje' => 'Docente creado exitosamente', 'id_docente' => $id_miembro];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Confirma la adición del rol Docente a un miembro existente con los valores seleccionados
 *
 * @param int $id_miembro ID del miembro existente
 * @param array $valores_seleccionados Valores elegidos por el usuario
 * @param string $especialidad Especialidad del docente
 * @param string $titulo Título académico
 * @return array Resultado
 */
function confirmarMergeEstudianteDocente($id_miembro, $valores_seleccionados, $especialidad = '', $titulo = '') {
    try {
        $conn = DatabaseService::get('nexus');

        // Obtener id_instancia del miembro
        $stmt_inst = $conn->prepare("SELECT id_instancia FROM miembros WHERE id_miembro = :id_miembro");
        $stmt_inst->execute([':id_miembro' => $id_miembro]);
        $row = $stmt_inst->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return ['success' => false, 'mensaje' => 'Miembro no encontrado'];
        }
        $id_instancia = $row['id_instancia'];

        // Construir el UPDATE dinámicamente con los valores seleccionados
        $campos_update = ['campo_texto_1 = :especialidad', 'campo_texto_2 = :titulo'];
        $params = [
            ':id_miembro' => $id_miembro,
            ':especialidad' => $especialidad,
            ':titulo' => $titulo
        ];

        // Agregar campos seleccionados
        if (isset($valores_seleccionados['nombre'])) {
            $campos_update[] = 'nombre = :nombre';
            $params[':nombre'] = strtoupper(trim($valores_seleccionados['nombre']));
        }
        if (isset($valores_seleccionados['apellido'])) {
            $campos_update[] = 'apellido = :apellido';
            $params[':apellido'] = strtoupper(trim($valores_seleccionados['apellido']));
        }
        if (isset($valores_seleccionados['email'])) {
            $campos_update[] = 'email = :email';
            $params[':email'] = trim($valores_seleccionados['email']);
        }
        if (isset($valores_seleccionados['pais'])) {
            $campos_update[] = 'domicilio_pais = :pais';
            $params[':pais'] = $valores_seleccionados['pais'];
        }

        $sql = "UPDATE miembros SET " . implode(', ', $campos_update) . " WHERE id_miembro = :id_miembro";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Agregar rol Docente usando MemberService
        MemberService::agregarRol((int)$id_miembro, (int)$id_instancia, 'Docente');

        return ['success' => true, 'mensaje' => 'Rol Docente agregado con los datos seleccionados', 'id_docente' => $id_miembro];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Actualiza un docente
 *
 * @param int $id_miembro ID del miembro
 * @param string $dni DNI
 * @param string $nombre Nombre
 * @param string $apellido Apellido
 * @param string $email Email
 * @param string $pais Código de país ISO
 * @param string $especialidad Especialidad
 * @param string $titulo Título académico
 * @return array
 */
function actualizarDocente($id_miembro, $dni, $nombre, $apellido, $email = '', $pais = 'AR', $especialidad = '', $titulo = '', $genero = 'Prefiero no especificar', $extras = []) {
    try {
        // Limpiar y normalizar datos
        $nombre = strtoupper(trim($nombre));
        $apellido = strtoupper(trim($apellido));
        $dni = strtoupper(trim($dni));

        if (empty($nombre) || empty($apellido)) {
            return ['success' => false, 'mensaje' => 'Nombre y Apellido son requeridos'];
        }

        $conn = DatabaseService::get('nexus');
        $stmt = $conn->prepare("
            UPDATE miembros SET
                identificador_principal = :dni,
                nombre = :nombre,
                apellido = :apellido,
                email = :email,
                domicilio_pais = :pais,
                domicilio_ciudad = :ciudad,
                domicilio_provincia = :provincia,
                domicilio_codigo_postal = :cp,
                profesion = :profesion,
                lugar_trabajo = :trabajo,
                cargo = :cargo,
                campo_texto_1 = :especialidad,
                campo_texto_2 = :titulo,
                genero = :genero
            WHERE id_miembro = :id_miembro
        ");
        $stmt->execute([
            ':id_miembro' => $id_miembro,
            ':dni' => $dni,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':email' => $email,
            ':pais' => $pais,
            ':ciudad' => $extras['domicilio_ciudad'] ?? null,
            ':provincia' => $extras['domicilio_provincia'] ?? null,
            ':cp' => $extras['domicilio_codigo_postal'] ?? null,
            ':profesion' => $extras['profesion'] ?? null,
            ':trabajo' => $extras['lugar_trabajo'] ?? null,
            ':cargo' => $extras['cargo'] ?? null,
            ':especialidad' => $especialidad,
            ':titulo' => $titulo,
            ':genero' => $genero
        ]);

        return ['success' => true, 'mensaje' => 'Docente actualizado exitosamente'];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Elimina (desactiva) un docente
 *
 * @param int $id_miembro ID del miembro
 * @return array
 */
function eliminarDocente($id_miembro) {
    try {
        $conn = DatabaseService::get('nexus');

        // Verificar si tiene participaciones activas
        $stmt_check = $conn->prepare("
            SELECT COUNT(*) FROM verumax_certifi.participaciones_docente
            WHERE id_miembro = :id_miembro AND activo = 1
        ");
        $stmt_check->execute([':id_miembro' => $id_miembro]);
        $tiene_participaciones = $stmt_check->fetchColumn() > 0;

        // Obtener tipo_miembro actual
        $stmt_tipo = $conn->prepare("SELECT tipo_miembro FROM miembros WHERE id_miembro = :id_miembro");
        $stmt_tipo->execute([':id_miembro' => $id_miembro]);
        $tipo = $stmt_tipo->fetchColumn();

        if ($tipo === 'ambos') {
            // Si es ambos, cambiar a solo Estudiante
            $stmt = $conn->prepare("UPDATE miembros SET tipo_miembro = 'Estudiante' WHERE id_miembro = :id_miembro");
            $stmt->execute([':id_miembro' => $id_miembro]);
            return ['success' => true, 'mensaje' => 'Rol de docente removido (sigue siendo estudiante)'];
        } elseif ($tiene_participaciones) {
            // Si tiene participaciones, solo desactivar
            $stmt = $conn->prepare("UPDATE miembros SET estado = 'Inactivo' WHERE id_miembro = :id_miembro");
            $stmt->execute([':id_miembro' => $id_miembro]);
            return ['success' => true, 'mensaje' => 'Docente desactivado (tiene participaciones registradas)'];
        } else {
            // Si no tiene participaciones, eliminar
            $stmt = $conn->prepare("DELETE FROM miembros WHERE id_miembro = :id_miembro");
            $stmt->execute([':id_miembro' => $id_miembro]);
            return ['success' => true, 'mensaje' => 'Docente eliminado exitosamente'];
        }

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Crea una participación de docente en un curso
 *
 * @param int $id_miembro ID del miembro (docente)
 * @param int $id_curso ID del curso
 * @param string $rol Rol (docente, instructor, orador, etc.)
 * @param string $titulo Título de la participación (opcional)
 * @param string|null $fecha_inicio Fecha de inicio
 * @param string|null $fecha_fin Fecha de fin
 * @return array Resultado
 */
function crearParticipacionDocente($id_miembro, $id_curso, $rol = 'docente', $titulo = '', $fecha_inicio = null, $fecha_fin = null, $id_instancia = null, $notificar = false) {
    try {
        $conn = DatabaseService::get('certificatum');

        // Verificar que no exista ya la participación
        $stmt_check = $conn->prepare("
            SELECT id_participacion FROM participaciones_docente
            WHERE id_miembro = :id_miembro AND id_curso = :id_curso AND activo = 1
        ");
        $stmt_check->execute([':id_miembro' => $id_miembro, ':id_curso' => $id_curso]);
        if ($stmt_check->fetch()) {
            return ['success' => false, 'mensaje' => 'El docente ya está asignado a este curso'];
        }

        // Obtener id_instancia del curso si no se proporcionó
        if (!$id_instancia) {
            $connAcad = DatabaseService::get('academicus');
            $stmtCurso = $connAcad->prepare("SELECT id_instancia FROM cursos WHERE id_curso = :id_curso");
            $stmtCurso->execute([':id_curso' => $id_curso]);
            $cursoData = $stmtCurso->fetch(PDO::FETCH_ASSOC);
            $id_instancia = $cursoData['id_instancia'] ?? null;
        }

        $stmt = $conn->prepare("
            INSERT INTO participaciones_docente
            (id_miembro, id_curso, id_instancia, rol, estado, titulo_participacion, fecha_inicio, fecha_fin, activo)
            VALUES (:id_miembro, :id_curso, :id_instancia, :rol, 'Asignado', :titulo, :fecha_inicio, :fecha_fin, 1)
        ");
        $stmt->execute([
            ':id_miembro' => $id_miembro,
            ':id_curso' => $id_curso,
            ':id_instancia' => $id_instancia,
            ':rol' => $rol,
            ':titulo' => $titulo ?: null,
            ':fecha_inicio' => $fecha_inicio ?: null,
            ':fecha_fin' => $fecha_fin ?: null
        ]);

        $id_participacion = $conn->lastInsertId();
        $mensaje = 'Docente asignado al curso exitosamente';

        // Enviar email de notificación si está habilitado
        if ($notificar && $id_instancia) {
            $emailResult = enviarEmailDocenteAsignado($id_participacion, $id_instancia);
            if ($emailResult['success']) {
                $mensaje .= ' | Email enviado correctamente';
            } else {
                $mensaje .= ' | Error al enviar email: ' . ($emailResult['error'] ?? 'desconocido');
            }
        }

        return ['success' => true, 'mensaje' => $mensaje, 'id_participacion' => $id_participacion];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Actualiza una participación de docente
 *
 * @param int $id_participacion ID de la participación
 * @param array $datos Datos a actualizar
 * @return array Resultado
 */
function actualizarParticipacionDocente($id_participacion, $datos, $notificar = false) {
    try {
        $conn = DatabaseService::get('certificatum');

        // Obtener estado actual para comparar
        $stmtActual = $conn->prepare("SELECT estado, id_instancia FROM participaciones_docente WHERE id_participacion = :id");
        $stmtActual->execute([':id' => $id_participacion]);
        $participacionActual = $stmtActual->fetch(PDO::FETCH_ASSOC);
        $estadoAnterior = $participacionActual['estado'] ?? 'Asignado';
        $id_instancia = $participacionActual['id_instancia'] ?? null;

        $nuevoEstado = $datos['estado'] ?? $estadoAnterior;

        $stmt = $conn->prepare("
            UPDATE participaciones_docente SET
                rol = :rol,
                estado = :estado,
                titulo_participacion = :titulo,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin
            WHERE id_participacion = :id_participacion
        ");
        $stmt->execute([
            ':id_participacion' => $id_participacion,
            ':rol' => $datos['rol'] ?? 'docente',
            ':estado' => $nuevoEstado,
            ':titulo' => $datos['titulo_participacion'] ?? null,
            ':fecha_inicio' => $datos['fecha_inicio'] ?? null,
            ':fecha_fin' => $datos['fecha_fin'] ?? null
        ]);

        $mensaje = 'Participación actualizada exitosamente';

        // Enviar email si cambió a Completado y se solicitó notificar
        if ($notificar && $nuevoEstado === 'Completado' && $estadoAnterior !== 'Completado' && $id_instancia) {
            $emailResult = enviarEmailCertificadoDocente($id_participacion, $id_instancia);
            if ($emailResult['success']) {
                $mensaje .= ' | Email enviado correctamente';
            } else {
                $mensaje .= ' | Error al enviar email: ' . ($emailResult['error'] ?? 'desconocido');
            }
        }

        return ['success' => true, 'mensaje' => $mensaje];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Elimina (desactiva) una participación de docente
 *
 * @param int $id_participacion ID de la participación
 * @return array Resultado
 */
function eliminarParticipacionDocente($id_participacion) {
    try {
        $conn = DatabaseService::get('certificatum');

        // Verificar si tiene certificado emitido
        $stmt_check = $conn->prepare("SELECT certificado_emitido FROM participaciones_docente WHERE id_participacion = :id");
        $stmt_check->execute([':id' => $id_participacion]);
        $part = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$part) {
            return ['success' => false, 'mensaje' => 'Participación no encontrada'];
        }

        if ($part['certificado_emitido']) {
            // Si tiene certificado, solo desactivar
            $stmt = $conn->prepare("UPDATE participaciones_docente SET activo = 0 WHERE id_participacion = :id");
            $stmt->execute([':id' => $id_participacion]);
            return ['success' => true, 'mensaje' => 'Participación desactivada (tenía certificado emitido)'];
        } else {
            // Si no tiene certificado, eliminar
            $stmt = $conn->prepare("DELETE FROM participaciones_docente WHERE id_participacion = :id");
            $stmt->execute([':id' => $id_participacion]);
            return ['success' => true, 'mensaje' => 'Participación eliminada exitosamente'];
        }

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Obtiene todas las participaciones docentes de una institución
 *
 * @param int $id_instancia ID de la instancia
 * @param string $filtro_estado Filtrar por estado (opcional)
 * @param string $buscar Término de búsqueda (opcional)
 * @return array Lista de participaciones
 */
function obtenerParticipacionesDocentes($id_instancia, $filtro_estado = '', $buscar = '') {
    try {
        $conn = DatabaseService::get('certificatum');

        $sql = "
            SELECT
                p.id_participacion,
                p.id_miembro,
                p.id_curso,
                p.id_instancia,
                p.rol,
                p.estado,
                p.titulo_participacion,
                p.fecha_inicio,
                p.fecha_fin,
                p.certificado_emitido,
                p.fecha_certificado,
                p.fecha_creacion,
                m.identificador_principal as dni,
                m.nombre_completo,
                m.email,
                c.codigo_curso,
                c.nombre_curso
            FROM participaciones_docente p
            INNER JOIN verumax_nexus.miembros m ON p.id_miembro = m.id_miembro
            INNER JOIN verumax_academi.cursos c ON p.id_curso = c.id_curso
            WHERE p.id_instancia = :id_instancia
            AND p.activo = 1
        ";

        $params = [':id_instancia' => $id_instancia];

        // Filtro por estado
        if (!empty($filtro_estado)) {
            $sql .= " AND p.estado = :estado";
            $params[':estado'] = $filtro_estado;
        }

        // Filtro por búsqueda
        if (!empty($buscar)) {
            $sql .= " AND (m.identificador_principal LIKE :buscar
                      OR m.nombre_completo LIKE :buscar
                      OR c.codigo_curso LIKE :buscar
                      OR c.nombre_curso LIKE :buscar)";
            $params[':buscar'] = "%{$buscar}%";
        }

        $sql .= " ORDER BY p.fecha_creacion DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error obteniendo participaciones docentes: " . $e->getMessage());
        return [];
    }
}

/**
 * Procesa texto con datos de docentes (carga masiva)
 * Formato extendido: DNI, Nombre, Apellido, Email, Especialidad, Título, Teléfono, Ciudad, Provincia, CódPostal, País, LugarTrabajo, Cargo, Profesión
 * Formato básico: DNI, Nombre, Apellido, Email, Especialidad, Título (campos opcionales después de Título)
 * Formato antiguo: DNI, Nombre Completo, Email, Especialidad, Título (para compatibilidad)
 *
 * @param string $texto Texto con datos
 * @param int|string $institucion ID o slug
 * @return array Estadísticas
 */
function procesarSoloDocentes($texto, $institucion) {
    $estadisticas = [
        'docentes_insertados' => 0,
        'docentes_actualizados' => 0,
        'errores' => []
    ];

    $lineas = explode("\n", trim($texto));

    foreach ($lineas as $i => $linea) {
        $linea = trim($linea);
        if (empty($linea)) continue;

        $campos = str_getcsv($linea);
        if (count($campos) < 2) {
            $estadisticas['errores'][] = "Línea " . ($i + 1) . ": formato incorrecto";
            continue;
        }

        $dni = strtoupper(trim($campos[0]));

        // Detectar si el formato es nuevo (6+ campos) o antiguo (5 campos)
        if (count($campos) >= 6) {
            // Formato nuevo: DNI, Nombre, Apellido, Email, Especialidad, Título, [Teléfono, Ciudad, Provincia, CódPostal, País, LugarTrabajo, Cargo, Profesión]
            $nombre = strtoupper(trim($campos[1]));
            $apellido = strtoupper(trim($campos[2]));
            $email = trim($campos[3] ?? '');
            $especialidad = trim($campos[4] ?? '');
            $titulo = trim($campos[5] ?? '');
            $telefono = isset($campos[6]) && !empty(trim($campos[6])) ? trim($campos[6]) : '';
            $ciudad = isset($campos[7]) && !empty(trim($campos[7])) ? trim($campos[7]) : '';
            $provincia = isset($campos[8]) && !empty(trim($campos[8])) ? trim($campos[8]) : '';
            $codigo_postal = isset($campos[9]) && !empty(trim($campos[9])) ? trim($campos[9]) : '';
            $pais = isset($campos[10]) && !empty(trim($campos[10])) ? trim($campos[10]) : '';
            $lugar_trabajo = isset($campos[11]) && !empty(trim($campos[11])) ? trim($campos[11]) : '';
            $cargo = isset($campos[12]) && !empty(trim($campos[12])) ? trim($campos[12]) : '';
            $profesion = isset($campos[13]) && !empty(trim($campos[13])) ? trim($campos[13]) : '';
        } else {
            // Formato antiguo: DNI, Nombre Completo, Email, Especialidad, Título
            // Separar nombre completo en nombre y apellido
            $nombre_completo = strtoupper(trim($campos[1]));
            $partes = explode(' ', $nombre_completo, 2);
            $nombre = $partes[0];
            $apellido = $partes[1] ?? '';
            $email = trim($campos[2] ?? '');
            $especialidad = trim($campos[3] ?? '');
            $titulo = trim($campos[4] ?? '');
            $telefono = '';
            $ciudad = '';
            $provincia = '';
            $codigo_postal = '';
            $pais = '';
            $lugar_trabajo = '';
            $cargo = '';
            $profesion = '';
        }

        // Preparar datos adicionales
        $extras = [
            'telefono' => $telefono,
            'domicilio_ciudad' => $ciudad,
            'domicilio_provincia' => $provincia,
            'domicilio_codigo_postal' => $codigo_postal,
            'domicilio_pais' => $pais,
            'lugar_trabajo' => $lugar_trabajo,
            'cargo' => $cargo,
            'profesion' => $profesion
        ];

        $resultado = crearDocente($institucion, $dni, $nombre, $apellido, $email, 'AR', $especialidad, $titulo, $extras);

        if ($resultado['success']) {
            if (strpos($resultado['mensaje'], 'actualizado') !== false) {
                $estadisticas['docentes_actualizados']++;
            } else {
                $estadisticas['docentes_insertados']++;
            }
        } else {
            $estadisticas['errores'][] = "Línea " . ($i + 1) . ": " . $resultado['mensaje'];
        }
    }

    return $estadisticas;
}

// ============================================================================
// FUNCIONES DE EMAIL PARA DOCENTES
// ============================================================================

/**
 * Envía email de notificación cuando un docente es asignado a un curso
 *
 * @param int $id_participacion ID de la participación
 * @param int $id_instancia ID de la instancia
 * @return array Resultado del envío
 */
function enviarEmailDocenteAsignado($id_participacion, $id_instancia) {
    try {
        $conn = DatabaseService::get('certificatum');
        $connNexus = DatabaseService::get('nexus');
        $connAcad = DatabaseService::get('academicus');
        $connGeneral = DatabaseService::get('general');

        // Obtener datos de la participación
        $stmt = $conn->prepare("
            SELECT p.*, m.nombre_completo, m.email, m.identificador_principal as dni
            FROM participaciones_docente p
            INNER JOIN verumax_nexus.miembros m ON p.id_miembro = m.id_miembro
            WHERE p.id_participacion = :id
        ");
        $stmt->execute([':id' => $id_participacion]);
        $participacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$participacion || empty($participacion['email'])) {
            return ['success' => false, 'error' => 'El docente no tiene email registrado'];
        }

        // Obtener datos del curso
        $stmtCurso = $connAcad->prepare("SELECT * FROM cursos WHERE id_curso = :id");
        $stmtCurso->execute([':id' => $participacion['id_curso']]);
        $curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);

        // Obtener datos de la institución
        $stmtInst = $connGeneral->prepare("SELECT * FROM instances WHERE id_instancia = :id");
        $stmtInst->execute([':id' => $id_instancia]);
        $institucion = $stmtInst->fetch(PDO::FETCH_ASSOC);

        $slug = $institucion['slug'] ?? 'verumax';
        $urlPortal = 'https://' . $slug . '.verumax.com/';

        // Preparar variables para el email
        $variables = [
            'nombre_docente' => $participacion['nombre_completo'],
            'nombre_curso' => $curso['nombre_curso'] ?? 'Curso',
            'rol' => ucfirst($participacion['rol'] ?? 'docente'),
            'fecha_inicio' => $participacion['fecha_inicio'] ? date('d/m/Y', strtotime($participacion['fecha_inicio'])) : 'Por definir',
            'fecha_fin' => $participacion['fecha_fin'] ? date('d/m/Y', strtotime($participacion['fecha_fin'])) : 'Por definir',
            'url_portal' => $urlPortal,
            'nombre_institucion' => $institucion['nombre'] ?? 'Institución',
            'logo_url' => $institucion['logo_url'] ?? ''
        ];

        // Enviar email usando EmailService
        return \VERUMax\Services\EmailService::enviarIndividual(
            $id_instancia,
            'docente_asignado',
            $participacion['email'],
            $participacion['nombre_completo'],
            $variables
        );

    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Envía email cuando el certificado del docente está disponible
 *
 * @param int $id_participacion ID de la participación
 * @param int $id_instancia ID de la instancia
 * @return array Resultado del envío
 */
function enviarEmailCertificadoDocente($id_participacion, $id_instancia) {
    try {
        $conn = DatabaseService::get('certificatum');
        $connAcad = DatabaseService::get('academicus');
        $connGeneral = DatabaseService::get('general');

        // Obtener datos de la participación
        $stmt = $conn->prepare("
            SELECT p.*, m.nombre_completo, m.email, m.identificador_principal as dni
            FROM participaciones_docente p
            INNER JOIN verumax_nexus.miembros m ON p.id_miembro = m.id_miembro
            WHERE p.id_participacion = :id
        ");
        $stmt->execute([':id' => $id_participacion]);
        $participacion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$participacion || empty($participacion['email'])) {
            return ['success' => false, 'error' => 'El docente no tiene email registrado'];
        }

        // Obtener datos del curso
        $stmtCurso = $connAcad->prepare("SELECT * FROM cursos WHERE id_curso = :id");
        $stmtCurso->execute([':id' => $participacion['id_curso']]);
        $curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);

        // Obtener datos de la institución
        $stmtInst = $connGeneral->prepare("SELECT * FROM instances WHERE id_instancia = :id");
        $stmtInst->execute([':id' => $id_instancia]);
        $institucion = $stmtInst->fetch(PDO::FETCH_ASSOC);

        $slug = $institucion['slug'] ?? 'verumax';
        $urlPortal = 'https://' . $slug . '.verumax.com/';

        // Preparar variables para el email
        $variables = [
            'nombre_docente' => $participacion['nombre_completo'],
            'nombre_curso' => $curso['nombre_curso'] ?? 'Curso',
            'rol' => ucfirst($participacion['rol'] ?? 'docente'),
            'url_portal' => $urlPortal,
            'nombre_institucion' => $institucion['nombre'] ?? 'Institución',
            'logo_url' => $institucion['logo_url'] ?? ''
        ];

        // Enviar email usando EmailService
        return \VERUMax\Services\EmailService::enviarIndividual(
            $id_instancia,
            'certificado_docente_disponible',
            $participacion['email'],
            $participacion['nombre_completo'],
            $variables
        );

    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// ============================================================================
// FUNCIONES PARA EVALUACIONES (PROBATIO)
// Las evaluaciones se guardan en verumax_academi.evaluationes
// Las preguntas se guardan en verumax_academi.quaestiones
// ============================================================================

/**
 * Obtiene todas las evaluaciones de una institución
 *
 * @param int $id_instancia ID de la instancia
 * @param string $estado Filtrar por estado (opcional)
 * @param string $buscar Término de búsqueda (opcional)
 * @return array Lista de evaluaciones
 */
function obtenerEvaluaciones($id_instancia, $estado = '', $buscar = '') {
    try {
        $conn = DatabaseService::get('academicus');

        $sql = "
            SELECT
                e.id_evaluatio,
                e.id_instancia,
                e.id_curso,
                e.codigo,
                e.nombre,
                e.descripcion,
                e.tipo,
                e.metodologia,
                e.estado,
                e.fecha_inicio,
                e.fecha_fin,
                e.permite_multiples_intentos,
                e.muestra_respuestas_correctas,
                e.requiere_cierre_cualitativo,
                e.texto_cierre_cualitativo,
                e.minimo_caracteres_cierre,
                e.minimo_caracteres_abierta,
                e.mensaje_bienvenida,
                e.mensaje_finalizacion,
                e.mensaje_error_no_inscripto,
                e.created_at,
                c.codigo_curso,
                c.nombre_curso,
                (SELECT COUNT(*) FROM quaestiones q WHERE q.id_evaluatio = e.id_evaluatio) as total_preguntas,
                (SELECT COUNT(*) FROM sessiones_probatio s WHERE s.id_evaluatio = e.id_evaluatio) as total_sesiones,
                (SELECT COUNT(*) FROM sessiones_probatio s WHERE s.id_evaluatio = e.id_evaluatio AND s.estado = 'completada') as sesiones_completadas
            FROM evaluationes e
            LEFT JOIN cursos c ON e.id_curso = c.id_curso
            WHERE e.id_instancia = :id_instancia
        ";

        $params = [':id_instancia' => $id_instancia];

        if (!empty($estado)) {
            $sql .= " AND e.estado = :estado";
            $params[':estado'] = $estado;
        }

        if (!empty($buscar)) {
            $sql .= " AND (e.codigo LIKE :buscar OR e.nombre LIKE :buscar OR c.nombre_curso LIKE :buscar)";
            $params[':buscar'] = "%{$buscar}%";
        }

        $sql .= " ORDER BY e.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error obteniendo evaluaciones: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene una evaluación por ID
 *
 * @param int $id_evaluatio ID de la evaluación
 * @return array|null Evaluación o null
 */
function obtenerEvaluacionPorId($id_evaluatio) {
    try {
        $conn = DatabaseService::get('academicus');

        $stmt = $conn->prepare("
            SELECT e.*, c.codigo_curso, c.nombre_curso
            FROM evaluationes e
            LEFT JOIN cursos c ON e.id_curso = c.id_curso
            WHERE e.id_evaluatio = :id
        ");
        $stmt->execute([':id' => $id_evaluatio]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    } catch (Exception $e) {
        error_log("Error obteniendo evaluación: " . $e->getMessage());
        return null;
    }
}

/**
 * Genera código de evaluación basado en curso e institución
 * Formato: PROB-{INST}-{CODIGO_CURSO}
 *
 * @param int $id_instancia ID de la instancia
 * @param int $id_curso ID del curso
 * @return string|null Código generado o null si error
 */
function generarCodigoEvaluacion($id_instancia, $id_curso) {
    try {
        $connGeneral = DatabaseService::get('general');
        $connAcad = DatabaseService::get('academicus');

        // Obtener slug de la institución
        $stmtInst = $connGeneral->prepare("SELECT slug FROM instances WHERE id_instancia = :id");
        $stmtInst->execute([':id' => $id_instancia]);
        $inst = $stmtInst->fetch(PDO::FETCH_ASSOC);
        $slug = strtoupper($inst['slug'] ?? 'INST');

        // Obtener código del curso
        $stmtCurso = $connAcad->prepare("SELECT codigo_curso FROM cursos WHERE id_curso = :id");
        $stmtCurso->execute([':id' => $id_curso]);
        $curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);
        $codigoCurso = strtoupper($curso['codigo_curso'] ?? 'CURSO');

        return "PROB-{$slug}-{$codigoCurso}";

    } catch (Exception $e) {
        error_log("Error generando código evaluación: " . $e->getMessage());
        return null;
    }
}

/**
 * Crea una nueva evaluación
 *
 * @param int $id_instancia ID de la instancia
 * @param array $datos Datos de la evaluación
 * @return array Resultado
 */
function crearEvaluacion($id_instancia, $datos) {
    try {
        $conn = DatabaseService::get('academicus');

        // Validar curso obligatorio
        $id_curso = (int)($datos['id_curso'] ?? 0);
        if (!$id_curso) {
            return ['success' => false, 'mensaje' => 'Debe seleccionar un curso'];
        }

        // Generar código automáticamente
        $codigo = generarCodigoEvaluacion($id_instancia, $id_curso);
        if (!$codigo) {
            return ['success' => false, 'mensaje' => 'Error generando código de evaluación'];
        }

        // Verificar que no exista el código
        $stmtCheck = $conn->prepare("SELECT id_evaluatio FROM evaluationes WHERE codigo = :codigo");
        $stmtCheck->execute([':codigo' => $codigo]);
        if ($stmtCheck->fetch()) {
            return ['success' => false, 'mensaje' => 'Ya existe una evaluación para este curso'];
        }

        // Obtener nombre del curso si no se proporcionó nombre
        if (empty($datos['nombre'])) {
            $stmtCurso = $conn->prepare("SELECT nombre_curso FROM cursos WHERE id_curso = :id");
            $stmtCurso->execute([':id' => $id_curso]);
            $curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);
            $datos['nombre'] = 'Evaluación - ' . ($curso['nombre_curso'] ?? 'Curso');
        }

        $stmt = $conn->prepare("
            INSERT INTO evaluationes (
                id_instancia, id_curso, codigo, nombre, descripcion,
                tipo, metodologia, estado,
                permite_multiples_intentos, muestra_respuestas_correctas,
                requiere_cierre_cualitativo, texto_cierre_cualitativo, minimo_caracteres_cierre,
                minimo_caracteres_abierta,
                mensaje_bienvenida, mensaje_finalizacion, mensaje_error_no_inscripto,
                fecha_inicio, fecha_fin
            ) VALUES (
                :id_instancia, :id_curso, :codigo, :nombre, :descripcion,
                :tipo, :metodologia, :estado,
                :permite_multiples, :muestra_respuestas,
                :requiere_cierre, :texto_cierre, :minimo_cierre,
                :minimo_abierta,
                :msg_bienvenida, :msg_finalizacion, :msg_no_inscripto,
                :fecha_inicio, :fecha_fin
            )
        ");

        $stmt->execute([
            ':id_instancia' => $id_instancia,
            ':id_curso' => $id_curso,
            ':codigo' => $codigo,
            ':nombre' => trim($datos['nombre']),
            ':descripcion' => trim($datos['descripcion'] ?? ''),
            ':tipo' => $datos['tipo'] ?? 'examen',
            ':metodologia' => 'afirmacion', // Fijo por ahora
            ':estado' => 'borrador',
            ':permite_multiples' => isset($datos['permite_multiples_intentos']) ? 1 : 0,
            ':muestra_respuestas' => isset($datos['muestra_respuestas_correctas']) ? 1 : 0,
            ':requiere_cierre' => isset($datos['requiere_cierre_cualitativo']) ? 1 : 0,
            ':texto_cierre' => trim($datos['texto_cierre_cualitativo'] ?? ''),
            ':minimo_cierre' => (int)($datos['minimo_caracteres_cierre'] ?? 0),
            ':minimo_abierta' => (int)($datos['minimo_caracteres_abierta'] ?? 50),
            ':msg_bienvenida' => trim($datos['mensaje_bienvenida'] ?? ''),
            ':msg_finalizacion' => trim($datos['mensaje_finalizacion'] ?? ''),
            ':msg_no_inscripto' => trim($datos['mensaje_error_no_inscripto'] ?? ''),
            ':fecha_inicio' => !empty($datos['fecha_inicio']) ? $datos['fecha_inicio'] : null,
            ':fecha_fin' => !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null,
        ]);

        $id_evaluatio = $conn->lastInsertId();

        return [
            'success' => true,
            'mensaje' => 'Evaluación creada exitosamente',
            'id_evaluatio' => $id_evaluatio,
            'codigo' => $codigo
        ];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Actualiza una evaluación
 *
 * @param int $id_evaluatio ID de la evaluación
 * @param array $datos Datos a actualizar
 * @return array Resultado
 */
function actualizarEvaluacion($id_evaluatio, $datos) {
    try {
        $conn = DatabaseService::get('academicus');

        // Verificar que existe
        $stmtCheck = $conn->prepare("SELECT estado FROM evaluationes WHERE id_evaluatio = :id");
        $stmtCheck->execute([':id' => $id_evaluatio]);
        $eval = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$eval) {
            return ['success' => false, 'mensaje' => 'Evaluación no encontrada'];
        }

        // Campos de checkbox (siempre se deben actualizar, 0 si no están presentes)
        $camposCheckbox = ['permite_multiples_intentos', 'muestra_respuestas_correctas', 'requiere_cierre_cualitativo'];

        // Campos de texto
        $camposTexto = [
            'nombre', 'descripcion', 'estado',
            'texto_cierre_cualitativo', 'minimo_caracteres_cierre', 'minimo_caracteres_abierta',
            'mensaje_bienvenida', 'mensaje_finalizacion', 'mensaje_error_no_inscripto',
            'fecha_inicio', 'fecha_fin'
        ];

        $campos = [];
        $params = [':id' => $id_evaluatio];

        // Procesar checkboxes (siempre se actualizan)
        foreach ($camposCheckbox as $campo) {
            $campos[] = "{$campo} = :{$campo}";
            $params[":{$campo}"] = isset($datos[$campo]) ? 1 : 0;
        }

        // Procesar campos de texto
        foreach ($camposTexto as $campo) {
            if (array_key_exists($campo, $datos)) {
                $campos[] = "{$campo} = :{$campo}";
                $valor = $datos[$campo];

                // Manejar fechas vacías
                if (in_array($campo, ['fecha_inicio', 'fecha_fin']) && empty($valor)) {
                    $valor = null;
                }

                // Trim para textos
                if (is_string($valor)) {
                    $valor = trim($valor);
                }

                $params[":{$campo}"] = $valor;
            }
        }

        if (empty($campos)) {
            return ['success' => false, 'mensaje' => 'No hay datos para actualizar'];
        }

        $sql = "UPDATE evaluationes SET " . implode(', ', $campos) . " WHERE id_evaluatio = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => true, 'mensaje' => 'Evaluación actualizada exitosamente'];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Elimina una evaluación (solo si no tiene sesiones)
 *
 * @param int $id_evaluatio ID de la evaluación
 * @return array Resultado
 */
function eliminarEvaluacion($id_evaluatio) {
    try {
        $conn = DatabaseService::get('academicus');

        // Verificar si tiene sesiones
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM sessiones_probatio WHERE id_evaluatio = :id");
        $stmtCheck->execute([':id' => $id_evaluatio]);
        $tieneSesiones = $stmtCheck->fetchColumn() > 0;

        if ($tieneSesiones) {
            return ['success' => false, 'mensaje' => 'No se puede eliminar: la evaluación tiene sesiones registradas'];
        }

        // Eliminar preguntas primero (CASCADE debería hacerlo, pero por seguridad)
        $stmtPreguntas = $conn->prepare("DELETE FROM quaestiones WHERE id_evaluatio = :id");
        $stmtPreguntas->execute([':id' => $id_evaluatio]);

        // Eliminar evaluación
        $stmt = $conn->prepare("DELETE FROM evaluationes WHERE id_evaluatio = :id");
        $stmt->execute([':id' => $id_evaluatio]);

        return ['success' => true, 'mensaje' => 'Evaluación eliminada exitosamente'];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Duplica una evaluación con nuevo código
 *
 * @param int $id_evaluatio ID de la evaluación original
 * @return array Resultado
 */
function duplicarEvaluacion($id_evaluatio) {
    try {
        $conn = DatabaseService::get('academicus');

        // Obtener evaluación original
        $eval = obtenerEvaluacionPorId($id_evaluatio);
        if (!$eval) {
            return ['success' => false, 'mensaje' => 'Evaluación no encontrada'];
        }

        // Generar nuevo código con sufijo numérico
        $codigoBase = $eval['codigo'];
        $contador = 2;
        $nuevoCodigo = "{$codigoBase}-{$contador}";

        while (true) {
            $stmtCheck = $conn->prepare("SELECT id_evaluatio FROM evaluationes WHERE codigo = :codigo");
            $stmtCheck->execute([':codigo' => $nuevoCodigo]);
            if (!$stmtCheck->fetch()) break;
            $contador++;
            $nuevoCodigo = "{$codigoBase}-{$contador}";
        }

        // Insertar copia
        $stmt = $conn->prepare("
            INSERT INTO evaluationes (
                id_instancia, id_curso, codigo, nombre, descripcion,
                tipo, metodologia, estado,
                permite_multiples_intentos, muestra_respuestas_correctas,
                requiere_cierre_cualitativo, texto_cierre_cualitativo, minimo_caracteres_cierre,
                mensaje_bienvenida, mensaje_finalizacion, mensaje_error_no_inscripto
            )
            SELECT
                id_instancia, id_curso, :codigo, CONCAT(nombre, ' (Copia)'), descripcion,
                tipo, metodologia, 'borrador',
                permite_multiples_intentos, muestra_respuestas_correctas,
                requiere_cierre_cualitativo, texto_cierre_cualitativo, minimo_caracteres_cierre,
                mensaje_bienvenida, mensaje_finalizacion, mensaje_error_no_inscripto
            FROM evaluationes WHERE id_evaluatio = :id
        ");
        $stmt->execute([':id' => $id_evaluatio, ':codigo' => $nuevoCodigo]);

        $nuevoId = $conn->lastInsertId();

        // Copiar preguntas (incluyendo contexto)
        $stmtPreguntas = $conn->prepare("
            INSERT INTO quaestiones (id_evaluatio, orden, tipo, enunciado, contexto, opciones, puntos, es_obligatoria)
            SELECT :nuevo_id, orden, tipo, enunciado, contexto, opciones, puntos, es_obligatoria
            FROM quaestiones WHERE id_evaluatio = :id_original
        ");
        $stmtPreguntas->execute([':nuevo_id' => $nuevoId, ':id_original' => $id_evaluatio]);

        return [
            'success' => true,
            'mensaje' => 'Evaluación duplicada exitosamente',
            'id_evaluatio' => $nuevoId,
            'codigo' => $nuevoCodigo
        ];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

// ============================================================================
// FUNCIONES PARA PREGUNTAS (QUAESTIONES)
// ============================================================================

/**
 * Obtiene todas las preguntas de una evaluación
 *
 * @param int $id_evaluatio ID de la evaluación
 * @return array Lista de preguntas
 */
function obtenerPreguntasAdmin($id_evaluatio) {
    try {
        $conn = DatabaseService::get('academicus');

        $stmt = $conn->prepare("
            SELECT
                q.*,
                (SELECT COUNT(*) FROM responsa r
                 INNER JOIN sessiones_probatio s ON r.id_sessio = s.id_sessio
                 WHERE r.id_quaestio = q.id_quaestio) as total_respuestas,
                (SELECT COUNT(*) FROM responsa r
                 INNER JOIN sessiones_probatio s ON r.id_sessio = s.id_sessio
                 WHERE r.id_quaestio = q.id_quaestio AND r.es_correcta = 1) as respuestas_correctas
            FROM quaestiones q
            WHERE q.id_evaluatio = :id_evaluatio
            ORDER BY q.orden ASC
        ");
        $stmt->execute([':id_evaluatio' => $id_evaluatio]);

        $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decodificar opciones JSON
        foreach ($preguntas as &$p) {
            $p['opciones'] = json_decode($p['opciones'] ?? '[]', true) ?: [];
        }

        return $preguntas;

    } catch (Exception $e) {
        error_log("Error obteniendo preguntas: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene una pregunta por ID
 *
 * @param int $id_quaestio ID de la pregunta
 * @return array|null Pregunta o null
 */
function obtenerPreguntaPorId($id_quaestio) {
    try {
        $conn = DatabaseService::get('academicus');

        $stmt = $conn->prepare("SELECT * FROM quaestiones WHERE id_quaestio = :id");
        $stmt->execute([':id' => $id_quaestio]);

        $pregunta = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pregunta) {
            $pregunta['opciones'] = json_decode($pregunta['opciones'] ?? '[]', true) ?: [];
        }

        return $pregunta ?: null;

    } catch (Exception $e) {
        error_log("Error obteniendo pregunta: " . $e->getMessage());
        return null;
    }
}

/**
 * Crea una nueva pregunta
 *
 * @param int $id_evaluatio ID de la evaluación
 * @param array $datos Datos de la pregunta
 * @return array Resultado
 */
function crearPregunta($id_evaluatio, $datos) {
    try {
        $conn = DatabaseService::get('academicus');

        // Verificar que la evaluación existe y está en borrador
        $stmtEval = $conn->prepare("SELECT estado FROM evaluationes WHERE id_evaluatio = :id");
        $stmtEval->execute([':id' => $id_evaluatio]);
        $eval = $stmtEval->fetch(PDO::FETCH_ASSOC);

        if (!$eval) {
            return ['success' => false, 'mensaje' => 'Evaluación no encontrada'];
        }

        // Obtener siguiente orden
        $stmtOrden = $conn->prepare("SELECT COALESCE(MAX(orden), 0) + 1 FROM quaestiones WHERE id_evaluatio = :id");
        $stmtOrden->execute([':id' => $id_evaluatio]);
        $orden = (int)$stmtOrden->fetchColumn();

        // Validar tipo
        $tipo = $datos['tipo'] ?? 'multiple_choice';
        $tiposValidos = ['multiple_choice', 'multiple_answer', 'verdadero_falso', 'abierta'];
        if (!in_array($tipo, $tiposValidos)) {
            return ['success' => false, 'mensaje' => 'Tipo de pregunta inválido'];
        }

        // Validar enunciado
        $enunciado = trim($datos['enunciado'] ?? '');
        if (strlen($enunciado) < 10) {
            return ['success' => false, 'mensaje' => 'El enunciado debe tener al menos 10 caracteres'];
        }

        // Procesar opciones para tipos con opciones
        $opciones = [];
        if ($tipo !== 'abierta') {
            $opciones = $datos['opciones'] ?? [];

            // Validar opciones
            if (count($opciones) < 2) {
                return ['success' => false, 'mensaje' => 'Debe haber al menos 2 opciones'];
            }

            $tieneCorrecta = false;
            $correctasCount = 0;
            foreach ($opciones as &$op) {
                if (!empty($op['es_correcta'])) {
                    $tieneCorrecta = true;
                    $correctasCount++;
                    $op['es_correcta'] = true;
                } else {
                    $op['es_correcta'] = false;
                }
            }

            if (!$tieneCorrecta) {
                return ['success' => false, 'mensaje' => 'Debe haber al menos una opción correcta'];
            }

            if ($tipo === 'multiple_choice' && $correctasCount > 1) {
                return ['success' => false, 'mensaje' => 'En preguntas de opción única solo puede haber una respuesta correcta'];
            }
        }

        // Contexto es opcional
        $contexto = trim($datos['contexto'] ?? '');

        $stmt = $conn->prepare("
            INSERT INTO quaestiones (
                id_evaluatio, orden, tipo, enunciado, contexto, opciones,
                puntos, es_obligatoria
            ) VALUES (
                :id_evaluatio, :orden, :tipo, :enunciado, :contexto, :opciones,
                :puntos, :es_obligatoria
            )
        ");

        $stmt->execute([
            ':id_evaluatio' => $id_evaluatio,
            ':orden' => $orden,
            ':tipo' => $tipo,
            ':enunciado' => $enunciado,
            ':contexto' => $contexto ?: null,
            ':opciones' => json_encode($opciones, JSON_UNESCAPED_UNICODE),
            ':puntos' => (int)($datos['puntos'] ?? 1),
            ':es_obligatoria' => isset($datos['es_obligatoria']) ? 1 : 1, // Default true
        ]);

        return [
            'success' => true,
            'mensaje' => 'Pregunta creada exitosamente',
            'id_quaestio' => $conn->lastInsertId(),
            'orden' => $orden
        ];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Actualiza una pregunta
 *
 * @param int $id_quaestio ID de la pregunta
 * @param array $datos Datos a actualizar
 * @return array Resultado
 */
function actualizarPregunta($id_quaestio, $datos) {
    try {
        $conn = DatabaseService::get('academicus');

        // Verificar que existe
        $pregunta = obtenerPreguntaPorId($id_quaestio);
        if (!$pregunta) {
            return ['success' => false, 'mensaje' => 'Pregunta no encontrada'];
        }

        // Validar tipo si se cambia
        $tipo = $datos['tipo'] ?? $pregunta['tipo'];
        $tiposValidos = ['multiple_choice', 'multiple_answer', 'verdadero_falso', 'abierta'];
        if (!in_array($tipo, $tiposValidos)) {
            return ['success' => false, 'mensaje' => 'Tipo de pregunta inválido'];
        }

        // Validar enunciado si se proporciona
        if (isset($datos['enunciado'])) {
            $enunciado = trim($datos['enunciado']);
            if (strlen($enunciado) < 10) {
                return ['success' => false, 'mensaje' => 'El enunciado debe tener al menos 10 caracteres'];
            }
        }

        // Procesar opciones para tipos con opciones
        $opciones = $datos['opciones'] ?? $pregunta['opciones'];
        if ($tipo !== 'abierta' && isset($datos['opciones'])) {
            if (count($opciones) < 2) {
                return ['success' => false, 'mensaje' => 'Debe haber al menos 2 opciones'];
            }

            $tieneCorrecta = false;
            $correctasCount = 0;
            foreach ($opciones as &$op) {
                if (!empty($op['es_correcta'])) {
                    $tieneCorrecta = true;
                    $correctasCount++;
                    $op['es_correcta'] = true;
                } else {
                    $op['es_correcta'] = false;
                }
            }

            if (!$tieneCorrecta) {
                return ['success' => false, 'mensaje' => 'Debe haber al menos una opción correcta'];
            }

            if ($tipo === 'multiple_choice' && $correctasCount > 1) {
                return ['success' => false, 'mensaje' => 'En preguntas de opción única solo puede haber una respuesta correcta'];
            }
        }

        // Contexto es opcional
        $contexto = isset($datos['contexto']) ? trim($datos['contexto']) : ($pregunta['contexto'] ?? '');

        $stmt = $conn->prepare("
            UPDATE quaestiones SET
                tipo = :tipo,
                enunciado = :enunciado,
                contexto = :contexto,
                opciones = :opciones,
                puntos = :puntos,
                es_obligatoria = :es_obligatoria
            WHERE id_quaestio = :id
        ");

        $stmt->execute([
            ':id' => $id_quaestio,
            ':tipo' => $tipo,
            ':enunciado' => trim($datos['enunciado'] ?? $pregunta['enunciado']),
            ':contexto' => $contexto ?: null,
            ':opciones' => json_encode($opciones, JSON_UNESCAPED_UNICODE),
            ':puntos' => (int)($datos['puntos'] ?? $pregunta['puntos'] ?? 1),
            ':es_obligatoria' => isset($datos['es_obligatoria']) ? ($datos['es_obligatoria'] ? 1 : 0) : ($pregunta['es_obligatoria'] ?? 1),
        ]);

        return ['success' => true, 'mensaje' => 'Pregunta actualizada exitosamente'];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Elimina una pregunta
 *
 * @param int $id_quaestio ID de la pregunta
 * @return array Resultado
 */
function eliminarPregunta($id_quaestio) {
    try {
        $conn = DatabaseService::get('academicus');

        // Verificar que existe
        $pregunta = obtenerPreguntaPorId($id_quaestio);
        if (!$pregunta) {
            return ['success' => false, 'mensaje' => 'Pregunta no encontrada'];
        }

        // Eliminar (las respuestas se eliminan por CASCADE)
        $stmt = $conn->prepare("DELETE FROM quaestiones WHERE id_quaestio = :id");
        $stmt->execute([':id' => $id_quaestio]);

        // Reordenar las preguntas restantes
        $stmtReorden = $conn->prepare("
            SET @orden := 0;
            UPDATE quaestiones
            SET orden = (@orden := @orden + 1)
            WHERE id_evaluatio = :id_evaluatio
            ORDER BY orden ASC
        ");
        // Nota: MySQL no permite SET en prepare, usar query separada
        $conn->exec("SET @orden := 0");
        $stmtReorden = $conn->prepare("
            UPDATE quaestiones
            SET orden = (@orden := @orden + 1)
            WHERE id_evaluatio = :id_evaluatio
            ORDER BY orden ASC
        ");
        $stmtReorden->execute([':id_evaluatio' => $pregunta['id_evaluatio']]);

        return ['success' => true, 'mensaje' => 'Pregunta eliminada exitosamente'];

    } catch (Exception $e) {
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Reordena las preguntas de una evaluación
 *
 * @param int $id_evaluatio ID de la evaluación
 * @param array $orden_array Array de IDs en el nuevo orden [id1, id2, id3, ...]
 * @return array Resultado
 */
function reordenarPreguntas($id_evaluatio, $orden_array) {
    try {
        $conn = DatabaseService::get('academicus');
        $conn->beginTransaction();

        $orden = 0;
        $stmt = $conn->prepare("UPDATE quaestiones SET orden = :orden WHERE id_quaestio = :id AND id_evaluatio = :id_evaluatio");

        foreach ($orden_array as $id_quaestio) {
            $orden++;
            $stmt->execute([
                ':orden' => $orden,
                ':id' => $id_quaestio,
                ':id_evaluatio' => $id_evaluatio
            ]);
        }

        $conn->commit();
        return ['success' => true, 'mensaje' => 'Orden actualizado exitosamente'];

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollBack();
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Obtiene estadísticas de una evaluación
 *
 * @param int $id_evaluatio ID de la evaluación
 * @return array Estadísticas
 */
function obtenerEstadisticasEvaluacion($id_evaluatio) {
    try {
        $conn = DatabaseService::get('academicus');

        $stmt = $conn->prepare("
            SELECT
                COUNT(*) as total_sesiones,
                SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as sesiones_completadas,
                SUM(CASE WHEN aprobado = 1 THEN 1 ELSE 0 END) as sesiones_aprobadas,
                AVG(porcentaje) as porcentaje_promedio,
                AVG(TIMESTAMPDIFF(MINUTE, fecha_inicio, fecha_finalizacion)) as tiempo_promedio_minutos
            FROM sessiones_probatio
            WHERE id_evaluatio = :id
        ");
        $stmt->execute([':id' => $id_evaluatio]);

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Pregunta con más errores
        $stmtPregunta = $conn->prepare("
            SELECT q.id_quaestio, q.orden, q.enunciado,
                   COUNT(r.id_responsum) as total_intentos,
                   SUM(CASE WHEN r.es_correcta = 0 THEN 1 ELSE 0 END) as intentos_fallidos
            FROM quaestiones q
            LEFT JOIN responsa r ON q.id_quaestio = r.id_quaestio
            WHERE q.id_evaluatio = :id
            GROUP BY q.id_quaestio
            ORDER BY intentos_fallidos DESC
            LIMIT 1
        ");
        $stmtPregunta->execute([':id' => $id_evaluatio]);
        $preguntaDificil = $stmtPregunta->fetch(PDO::FETCH_ASSOC);

        $stats['pregunta_mas_dificil'] = $preguntaDificil;

        return $stats;

    } catch (Exception $e) {
        error_log("Error obteniendo estadísticas: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene las sesiones de una evaluación con info del estudiante
 *
 * @param int $id_evaluatio ID de la evaluación
 * @return array Lista de sesiones con datos del miembro
 */
function obtenerSesionesEvaluacion($id_evaluatio) {
    try {
        $conn = DatabaseService::get('academicus');
        $connNexus = DatabaseService::get('nexus');

        // Obtener sesiones de la evaluación
        $stmt = $conn->prepare("
            SELECT s.id_sessio, s.id_miembro, s.estado, s.pregunta_actual,
                   s.puntaje_obtenido, s.puntaje_maximo, s.porcentaje, s.aprobado,
                   s.fecha_inicio, s.fecha_finalizacion, s.reflexion_final,
                   (SELECT COUNT(*) FROM responsa r WHERE r.id_sessio = s.id_sessio) as total_respuestas
            FROM sessiones_probatio s
            WHERE s.id_evaluatio = :id_evaluatio
            ORDER BY s.fecha_inicio DESC
        ");
        $stmt->execute([':id_evaluatio' => $id_evaluatio]);
        $sesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener info de miembros
        if (!empty($sesiones)) {
            $ids_miembros = array_column($sesiones, 'id_miembro');
            $placeholders = implode(',', array_fill(0, count($ids_miembros), '?'));

            $stmtMiembros = $connNexus->prepare("
                SELECT id_miembro, CONCAT(nombre, ' ', apellido) as nombre_completo,
                       email, identificador_principal as dni
                FROM miembros
                WHERE id_miembro IN ({$placeholders})
            ");
            $stmtMiembros->execute($ids_miembros);
            $miembros = [];
            while ($m = $stmtMiembros->fetch(PDO::FETCH_ASSOC)) {
                $miembros[$m['id_miembro']] = $m;
            }

            // Combinar datos
            foreach ($sesiones as &$s) {
                $m = $miembros[$s['id_miembro']] ?? [];
                $s['nombre_completo'] = $m['nombre_completo'] ?? 'Desconocido';
                $s['email'] = $m['email'] ?? '';
                $s['dni'] = $m['dni'] ?? '';
            }
        }

        return $sesiones;

    } catch (Exception $e) {
        error_log("Error obteniendo sesiones: " . $e->getMessage());
        return [];
    }
}

/**
 * Resetea una sesión de evaluación (elimina respuestas y vuelve al inicio)
 *
 * @param int $id_sessio ID de la sesión
 * @return array Resultado con success y mensaje
 */
function resetearSesion($id_sessio) {
    try {
        $conn = DatabaseService::get('academicus');
        $conn->beginTransaction();

        // Verificar que la sesión existe y obtener id_inscripcion
        $stmtCheck = $conn->prepare("SELECT id_sessio, id_inscripcion FROM sessiones_probatio WHERE id_sessio = :id");
        $stmtCheck->execute([':id' => $id_sessio]);
        $sesion = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$sesion) {
            return ['success' => false, 'mensaje' => 'Sesión no encontrada'];
        }

        // Eliminar respuestas
        $stmtDelete = $conn->prepare("DELETE FROM responsa WHERE id_sessio = :id");
        $stmtDelete->execute([':id' => $id_sessio]);
        $respuestasEliminadas = $stmtDelete->rowCount();

        // Resetear estado de la sesión
        $stmtReset = $conn->prepare("
            UPDATE sessiones_probatio
            SET pregunta_actual = 1,
                preguntas_completadas = 0,
                estado = 'en_progreso',
                fecha_finalizacion = NULL,
                puntaje_obtenido = NULL,
                puntaje_maximo = NULL,
                porcentaje = NULL,
                aprobado = NULL,
                reflexion_final = NULL
            WHERE id_sessio = :id
        ");
        $stmtReset->execute([':id' => $id_sessio]);

        // Resetear inscripción si existe (quita estado Aprobado y acceso al certificado)
        if (!empty($sesion['id_inscripcion'])) {
            $stmtInscripcion = $conn->prepare("
                UPDATE inscripciones
                SET estado = 'Inscrito',
                    nota_final = NULL,
                    fecha_finalizacion = NULL,
                    certificado_disponible_desde = NULL
                WHERE id_inscripcion = :id_inscripcion
            ");
            $stmtInscripcion->execute([':id_inscripcion' => $sesion['id_inscripcion']]);
        }

        $conn->commit();

        return [
            'success' => true,
            'mensaje' => "Sesión reseteada. {$respuestasEliminadas} respuestas eliminadas. Inscripción reseteada.",
            'respuestas_eliminadas' => $respuestasEliminadas
        ];

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollBack();
        error_log("Error reseteando sesión: " . $e->getMessage());
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Elimina completamente una sesión (sesión + respuestas)
 *
 * @param int $id_sessio ID de la sesión
 * @return array Resultado con success y mensaje
 */
function eliminarSesion($id_sessio) {
    try {
        $conn = DatabaseService::get('academicus');
        $conn->beginTransaction();

        // Obtener id_inscripcion antes de eliminar
        $stmtCheck = $conn->prepare("SELECT id_inscripcion FROM sessiones_probatio WHERE id_sessio = :id");
        $stmtCheck->execute([':id' => $id_sessio]);
        $sesion = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$sesion) {
            return ['success' => false, 'mensaje' => 'Sesión no encontrada'];
        }

        // Resetear inscripción si existe (quita estado Aprobado y acceso al certificado)
        if (!empty($sesion['id_inscripcion'])) {
            $stmtInscripcion = $conn->prepare("
                UPDATE inscripciones
                SET estado = 'Inscrito',
                    nota_final = NULL,
                    fecha_finalizacion = NULL,
                    certificado_disponible_desde = NULL
                WHERE id_inscripcion = :id_inscripcion
            ");
            $stmtInscripcion->execute([':id_inscripcion' => $sesion['id_inscripcion']]);
        }

        // Las respuestas se eliminan automáticamente por FK CASCADE
        $stmt = $conn->prepare("DELETE FROM sessiones_probatio WHERE id_sessio = :id");
        $stmt->execute([':id' => $id_sessio]);

        $conn->commit();

        return ['success' => true, 'mensaje' => 'Sesión eliminada correctamente. Inscripción reseteada.'];

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollBack();
        error_log("Error eliminando sesión: " . $e->getMessage());
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Obtiene los inscriptos de un curso con email para notificaciones
 *
 * @param int $id_curso ID del curso
 * @param int $id_instancia ID de la instancia
 * @return array Lista de inscriptos con email
 */
function obtenerInscriptosConEmail($id_curso, $id_instancia) {
    try {
        $conn = DatabaseService::get('academicus');

        $stmt = $conn->prepare("
            SELECT
                i.id_inscripcion,
                i.id_miembro,
                m.nombre_completo,
                m.email,
                m.identificador_principal as dni
            FROM inscripciones i
            INNER JOIN verumax_nexus.miembros m ON i.id_miembro = m.id_miembro
            WHERE i.id_curso = :id_curso
            AND i.id_instancia = :id_instancia
            AND m.email IS NOT NULL
            AND m.email != ''
            AND i.estado NOT IN ('Baja', 'Cancelado')
        ");
        $stmt->execute([
            ':id_curso' => $id_curso,
            ':id_instancia' => $id_instancia
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error obteniendo inscriptos con email: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene la evaluación activa de un curso (si existe)
 *
 * @param int $id_curso ID del curso
 * @return array|null Datos de la evaluación activa o null
 */
function obtenerEvaluacionActivaDelCurso($id_curso) {
    try {
        $conn = DatabaseService::get('academicus');

        $stmt = $conn->prepare("
            SELECT
                e.id_evaluatio,
                e.codigo,
                e.nombre,
                e.descripcion,
                e.tipo,
                e.fecha_inicio,
                e.fecha_fin
            FROM evaluationes e
            WHERE e.id_curso = :id_curso
            AND e.estado = 'activa'
            ORDER BY e.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([':id_curso' => $id_curso]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    } catch (Exception $e) {
        error_log("Error obteniendo evaluación activa: " . $e->getMessage());
        return null;
    }
}

/**
 * Notifica a estudiantes que hay una evaluación disponible
 *
 * @param int $id_evaluatio ID de la evaluación
 * @param int $id_instancia ID de la instancia
 * @param array|null $id_inscripciones IDs específicos de inscripciones (null = todos los inscriptos)
 * @return array Resultado con success, enviados, errores
 */
function notificarEvaluacionDisponible($id_evaluatio, $id_instancia, $id_inscripciones = null) {
    try {
        $connAcad = DatabaseService::get('academicus');
        $connCert = DatabaseService::get('certificatum');
        $connGeneral = DatabaseService::get('general');

        // Obtener datos de la evaluación
        $eval = obtenerEvaluacionPorId($id_evaluatio);
        if (!$eval) {
            return ['success' => false, 'mensaje' => 'Evaluación no encontrada'];
        }

        // Obtener datos del curso
        $stmtCurso = $connAcad->prepare("SELECT * FROM cursos WHERE id_curso = :id");
        $stmtCurso->execute([':id' => $eval['id_curso']]);
        $curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);

        if (!$curso) {
            return ['success' => false, 'mensaje' => 'Curso no encontrado'];
        }

        // Obtener datos de la institución
        $stmtInst = $connGeneral->prepare("SELECT * FROM instances WHERE id_instancia = :id");
        $stmtInst->execute([':id' => $id_instancia]);
        $institucion = $stmtInst->fetch(PDO::FETCH_ASSOC);

        $slug = $institucion['slug'] ?? 'verumax';
        $urlPortal = 'https://' . $slug . '.verumax.com/';
        $urlEvaluacion = $urlPortal . 'probatio/' . urlencode($eval['codigo']);

        // Obtener destinatarios
        if ($id_inscripciones !== null && is_array($id_inscripciones) && count($id_inscripciones) > 0) {
            // Destinatarios específicos (inscripciones está en verumax_academi)
            $placeholders = implode(',', array_fill(0, count($id_inscripciones), '?'));
            $stmt = $connAcad->prepare("
                SELECT
                    i.id_inscripcion,
                    m.nombre_completo,
                    m.email,
                    m.identificador_principal as dni
                FROM inscripciones i
                INNER JOIN verumax_nexus.miembros m ON i.id_miembro = m.id_miembro
                WHERE i.id_inscripcion IN ($placeholders)
                AND m.email IS NOT NULL
                AND m.email != ''
            ");
            $stmt->execute($id_inscripciones);
            $destinatarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Todos los inscriptos del curso con email
            $destinatarios = obtenerInscriptosConEmail($eval['id_curso'], $id_instancia);
        }

        if (empty($destinatarios)) {
            return [
                'success' => false,
                'mensaje' => 'No hay estudiantes con email para notificar',
                'enviados' => 0,
                'sin_email' => 0
            ];
        }

        // Preparar array para envío masivo
        $destinatariosEmail = [];
        foreach ($destinatarios as $dest) {
            $destinatariosEmail[] = [
                'email' => $dest['email'],
                'nombre' => $dest['nombre_completo'],
                'variables' => [
                    'nombre_estudiante' => $dest['nombre_completo'],
                    'dni' => $dest['dni'] ?? '',
                    'nombre_evaluacion' => $eval['nombre'],
                    'tipo_evaluacion' => ucfirst($eval['tipo'] ?? 'evaluación'),
                    'nombre_curso' => $curso['nombre_curso'] ?? 'Curso',
                    'fecha_limite' => $eval['fecha_fin'] ? date('d/m/Y H:i', strtotime($eval['fecha_fin'])) : 'Sin fecha límite',
                    'url_evaluacion' => $urlEvaluacion,
                    'url_portal' => $urlPortal,
                    'nombre_institucion' => $institucion['nombre'] ?? 'Institución',
                    'logo_url' => $institucion['logo_url'] ?? ''
                ]
            ];
        }

        // Enviar emails usando EmailService
        $resultado = \VERUMax\Services\EmailService::enviarMasivo(
            $id_instancia,
            \VERUMax\Services\EmailService::TYPE_EVALUACION,
            $destinatariosEmail
        );

        $totalDestinatarios = count($destinatarios);
        $enviados = $resultado['enviados'] ?? 0;
        $errores = $resultado['errores'] ?? [];

        if ($enviados > 0) {
            $mensaje = "Notificación enviada a {$enviados} estudiante(s)";
            if ($enviados < $totalDestinatarios) {
                $mensaje .= " de {$totalDestinatarios}";
            }
            return [
                'success' => true,
                'mensaje' => $mensaje,
                'enviados' => $enviados,
                'total' => $totalDestinatarios,
                'errores' => $errores
            ];
        } else {
            return [
                'success' => false,
                'mensaje' => 'No se pudo enviar ningún email: ' . implode(', ', $errores),
                'enviados' => 0,
                'errores' => $errores
            ];
        }

    } catch (Exception $e) {
        error_log("Error notificando evaluación disponible: " . $e->getMessage());
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Notifica evaluación a un estudiante específico por id_inscripcion
 *
 * @param int $id_inscripcion ID de la inscripción
 * @param int $id_evaluatio ID de la evaluación
 * @param int $id_instancia ID de la instancia
 * @return array Resultado
 */
function notificarEvaluacionAEstudiante($id_inscripcion, $id_evaluatio, $id_instancia) {
    return notificarEvaluacionDisponible($id_evaluatio, $id_instancia, [$id_inscripcion]);
}
?>
