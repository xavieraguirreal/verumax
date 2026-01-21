<?php
/**
 * ADMINISTRARE PROCESADOR
 * Funciones para procesar archivos Excel, CSV y texto plano
 * Sistema CERTIFICATUM - VERUMax
 */

require_once 'config.php';

use VERUMax\Services\DatabaseService;
use VERUMax\Services\CursoService;

/**
 * Procesa un archivo Excel y carga los datos en la base de datos
 *
 * @param string $ruta_archivo Ruta temporal del archivo subido
 * @param string $institucion Código de la institución
 * @return array Resultado del procesamiento
 */
function procesarExcel($ruta_archivo, $institucion) {
    // Verificar si PHPSpreadsheet está disponible
    if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        // Intentar cargar con require
        $vendor_path = __DIR__ . '/vendor/autoload.php';
        if (file_exists($vendor_path)) {
            require_once $vendor_path;
        } else {
            return [
                'error' => true,
                'mensaje' => 'PHPSpreadsheet no está instalado. Por favor, ejecuta: composer require phpoffice/phpspreadsheet'
            ];
        }
    }

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($ruta_archivo);
        $hoja = $spreadsheet->getActiveSheet();
        $datos = $hoja->toArray(null, true, true, true);

        // La primera fila son los encabezados, la omitimos
        array_shift($datos);

        return procesarFilas($datos, $institucion);

    } catch (Exception $e) {
        return [
            'error' => true,
            'mensaje' => 'Error al procesar Excel: ' . $e->getMessage()
        ];
    }
}

/**
 * Procesa un archivo CSV y carga los datos en la base de datos
 *
 * @param string $ruta_archivo Ruta temporal del archivo subido
 * @param string $institucion Código de la institución
 * @return array Resultado del procesamiento
 */
function procesarCSV($ruta_archivo, $institucion) {
    try {
        $datos = [];
        $handle = fopen($ruta_archivo, 'r');

        if ($handle === false) {
            throw new Exception('No se pudo abrir el archivo CSV');
        }

        // Saltar la primera línea (encabezados)
        $primera_linea = fgetcsv($handle, 10000, ',');

        // Leer todas las filas
        while (($fila = fgetcsv($handle, 10000, ',')) !== false) {
            if (!empty($fila[0])) { // Solo procesar si hay DNI
                $datos[] = $fila;
            }
        }

        fclose($handle);

        // Convertir a formato con índices alfabéticos para compatibilidad
        $datos_indexados = [];
        foreach ($datos as $fila) {
            $fila_indexada = [];
            foreach ($fila as $index => $valor) {
                $letra = chr(65 + $index); // A, B, C, D...
                $fila_indexada[$letra] = $valor;
            }
            $datos_indexados[] = $fila_indexada;
        }

        return procesarFilas($datos_indexados, $institucion);

    } catch (Exception $e) {
        return [
            'error' => true,
            'mensaje' => 'Error al procesar CSV: ' . $e->getMessage()
        ];
    }
}

/**
 * Procesa texto plano en formato CSV
 *
 * @param string $texto Texto en formato CSV
 * @param string $institucion Código de la institución
 * @return array Resultado del procesamiento
 */
function procesarTextoPlano($texto, $institucion) {
    try {
        $lineas = explode("\n", $texto);
        $datos = [];

        // Saltar primera línea si parece ser encabezado
        $primera_linea = trim($lineas[0]);
        if (stripos($primera_linea, 'DNI') !== false || stripos($primera_linea, 'Nombre') !== false) {
            array_shift($lineas);
        }

        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if (empty($linea)) continue;

            $campos = str_getcsv($linea, ',', '"', '\\');
            if (!empty($campos[0])) {
                // Convertir a formato con índices alfabéticos
                $fila_indexada = [];
                foreach ($campos as $index => $valor) {
                    $letra = chr(65 + $index); // A, B, C, D...
                    $fila_indexada[$letra] = trim($valor);
                }
                $datos[] = $fila_indexada;
            }
        }

        return procesarFilas($datos, $institucion);

    } catch (Exception $e) {
        return [
            'error' => true,
            'mensaje' => 'Error al procesar texto: ' . $e->getMessage()
        ];
    }
}

/**
 * Procesa las filas de datos y las inserta en la base de datos
 *
 * @param array $filas Array de filas con datos
 * @param string $institucion Código de la institución
 * @return array Estadísticas del procesamiento
 */
function procesarFilas($filas, $institucion) {
    $estadisticas = [
        'estudiantes_insertados' => 0,
        'estudiantes_actualizados' => 0,
        'cursos_insertados' => 0,
        'cursos_actualizados' => 0,
        'inscripciones_insertadas' => 0,
        'inscripciones_actualizadas' => 0,
        'errores' => [],
        'debug' => []  // Para debugging temporal
    ];

    try {
        $conn = getCertDBConnection();
        $conn->beginTransaction();

        // Obtener id_instancia de la institución (OBLIGATORIO - no hay cursos globales)
        $id_instancia = null;
        if (is_numeric($institucion)) {
            $id_instancia = (int)$institucion;
        } else {
            $stmt_inst = $conn->prepare("SELECT id_instancia FROM verumax_general.instances WHERE slug = :slug");
            $stmt_inst->execute([':slug' => $institucion]);
            $id_instancia = $stmt_inst->fetchColumn();
        }

        if (!$id_instancia) {
            throw new Exception("No se encontró la institución: $institucion. No se pueden crear cursos sin institución.");
        }

        foreach ($filas as $index => $fila) {
            try {
                // Extraer datos de la fila
                $dni = limpiarDNI($fila['A'] ?? '');
                $nombre_completo = trim($fila['B'] ?? '');
                $codigo_curso = trim($fila['C'] ?? '');
                $nombre_curso = trim($fila['D'] ?? '');
                $estado = trim($fila['E'] ?? 'Por Iniciar');
                $carga_horaria = intval($fila['F'] ?? 0);
                $fecha_inicio = convertirFecha($fila['G'] ?? '');
                $fecha_finalizacion = convertirFecha($fila['H'] ?? '');
                $nota_final = !empty($fila['I']) ? floatval(str_replace(',', '.', $fila['I'])) : null;
                $asistencia = trim($fila['J'] ?? '');
                $competencias = !empty($fila['K']) ? explode('|', $fila['K']) : [];
                $trayectoria_raw = trim($fila['L'] ?? '');

                // Debug: registrar lo que se está procesando
                $estadisticas['debug'][] = "Fila " . ($index + 2) . ": DNI=$dni, Curso=$codigo_curso";

                // Validar datos mínimos
                if (empty($dni) || empty($nombre_completo) || empty($codigo_curso) || empty($nombre_curso)) {
                    $estadisticas['errores'][] = "Fila " . ($index + 2) . ": Datos incompletos (DNI='$dni', nombre='$nombre_completo', curso='$codigo_curso')";
                    continue;
                }

                // 1. Insertar o actualizar miembro en Nexus (retorna id_miembro)
                $id_miembro = insertarOActualizarEstudiante($conn, $institucion, $dni, $nombre_completo, $estadisticas);

                // 2. Insertar o actualizar curso (SIEMPRE con id_instancia - no hay cursos globales)
                $id_curso = insertarOActualizarCurso($conn, $codigo_curso, $nombre_curso, $carga_horaria, $estadisticas, $id_instancia);

                // 3. Insertar inscripción (usando id_miembro)
                $id_inscripcion = insertarInscripcion(
                    $conn,
                    $id_miembro,
                    $id_curso,
                    $estado,
                    $fecha_inicio,
                    $fecha_finalizacion,
                    $nota_final,
                    $asistencia,
                    $estadisticas
                );

                // 4. Insertar competencias
                if (!empty($competencias) && $id_inscripcion) {
                    insertarCompetencias($conn, $id_inscripcion, $competencias);
                }

                // 5. Insertar trayectoria
                if (!empty($trayectoria_raw) && $id_inscripcion) {
                    insertarTrayectoria($conn, $id_inscripcion, $trayectoria_raw);
                }

            } catch (Exception $e) {
                $estadisticas['errores'][] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $conn->commit();

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        $estadisticas['errores'][] = "Error general: " . $e->getMessage();
    }

    return $estadisticas;
}

/**
 * Inserta o actualiza un estudiante en la base de datos
 *
 * @param PDO $conn Conexión a la base de datos
 * @param int|string $id_instancia ID numérico de instancia o slug (legacy)
 * @param string $dni DNI del estudiante
 * @param string $nombre_completo Nombre completo
 * @param array &$estadisticas Referencia a estadísticas
 * @return int ID del estudiante
 */
function insertarOActualizarEstudiante($conn, $id_instancia, $dni, $nombre_completo, &$estadisticas) {
    // ACTUALIZADO: Ahora usa verumax_nexus.miembros en lugar de estudiantes
    // Retorna id_miembro en lugar de id_estudiante

    // Determinar si es id numérico o slug y obtener id_instancia numérico
    $es_id_numerico = is_numeric($id_instancia);
    $id_inst_numerico = $id_instancia;

    if (!$es_id_numerico) {
        // Obtener id_instancia desde slug
        $stmt_id = $conn->prepare("SELECT id_instancia FROM verumax_general.instances WHERE slug = :slug");
        $stmt_id->execute([':slug' => $id_instancia]);
        $id_inst_numerico = $stmt_id->fetchColumn();
        if (!$id_inst_numerico) {
            error_log("No se encontró instancia para slug: $id_instancia");
            return null;
        }
    }

    // Usar conexión a Nexus
    $nexus_conn = DatabaseService::get('nexus');

    // Verificar si el miembro ya existe en Nexus
    $stmt = $nexus_conn->prepare("
        SELECT id_miembro FROM miembros
        WHERE id_instancia = :id_instancia AND identificador_principal = :dni
        LIMIT 1
    ");
    $stmt->execute([':id_instancia' => $id_inst_numerico, ':dni' => $dni]);
    $existe = $stmt->fetch();

    // Separar nombre y apellido
    $partes = explode(' ', trim($nombre_completo), 2);
    $nombre = $partes[0] ?? '';
    $apellido = $partes[1] ?? '';

    if ($existe) {
        // Actualizar nombre si cambió
        $stmt_update = $nexus_conn->prepare("
            UPDATE miembros
            SET nombre = :nombre, apellido = :apellido
            WHERE id_miembro = :id
        ");
        $stmt_update->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':id' => $existe['id_miembro']
        ]);
        $estadisticas['estudiantes_actualizados']++;
        return $existe['id_miembro'];
    } else {
        // Insertar nuevo miembro en Nexus
        $stmt_insert = $nexus_conn->prepare("
            INSERT INTO miembros (id_instancia, identificador_principal, nombre, apellido, tipo_miembro, estado)
            VALUES (:id_instancia, :dni, :nombre, :apellido, 'Estudiante', 'Activo')
        ");
        $stmt_insert->execute([
            ':id_instancia' => $id_inst_numerico,
            ':dni' => $dni,
            ':nombre' => $nombre,
            ':apellido' => $apellido
        ]);
        $estadisticas['estudiantes_insertados']++;
        return $nexus_conn->lastInsertId();
    }
}

/**
 * Inserta o actualiza un curso en la base de datos
 *
 * @param PDO $conn Conexión a la base de datos
 * @param string $codigo_curso Código del curso
 * @param string $nombre_curso Nombre del curso
 * @param int $carga_horaria Carga horaria
 * @param array &$estadisticas Referencia a estadísticas
 * @param int $id_instancia ID de la instancia (OBLIGATORIO - no hay cursos globales)
 * @return int ID del curso
 * @throws Exception Si no se proporciona id_instancia
 */
function insertarOActualizarCurso($conn, $codigo_curso, $nombre_curso, $carga_horaria, &$estadisticas, $id_instancia) {
    // VALIDACIÓN: No se permiten cursos globales (sin institución)
    if ($id_instancia === null) {
        throw new Exception("No se puede crear un curso sin institución. id_instancia es obligatorio.");
    }

    // ACTUALIZADO: Usar CursoService de Academicus (verumax_academi.cursos)
    // Verificar si el curso ya existe via CursoService
    $existe = CursoService::getByCodigo((int)$id_instancia, $codigo_curso);

    if ($existe) {
        // Actualizar datos del curso Y ACTIVARLO via CursoService
        CursoService::actualizar($existe['id_curso'], [
            'nombre_curso' => $nombre_curso,
            'carga_horaria' => $carga_horaria,
            'activo' => 1
        ]);
        $estadisticas['cursos_actualizados']++;
        return $existe['id_curso'];
    } else {
        // Insertar nuevo curso via CursoService
        $resultado = CursoService::crear([
            'id_instancia' => $id_instancia,
            'codigo_curso' => $codigo_curso,
            'nombre_curso' => $nombre_curso,
            'carga_horaria' => $carga_horaria,
            'activo' => 1
        ]);

        if ($resultado['success']) {
            $estadisticas['cursos_insertados']++;
            return $resultado['id_curso'];
        } else {
            throw new Exception("Error creando curso: " . $resultado['mensaje']);
        }
    }
}

/**
 * Inserta una inscripción (o actualiza si ya existe)
 * ACTUALIZADO: Usa id_miembro en lugar de id_estudiante
 * @param int $id_miembro ID del miembro en verumax_nexus.miembros
 */
function insertarInscripcion($conn, $id_miembro, $id_curso, $estado, $fecha_inicio, $fecha_finalizacion, $nota_final, $asistencia, &$estadisticas) {
    // Verificar si ya existe la inscripción (usando id_miembro)
    $stmt = $conn->prepare("
        SELECT id_inscripcion FROM inscripciones
        WHERE id_miembro = :id_miembro AND id_curso = :id_curso
        LIMIT 1
    ");
    $stmt->execute([
        ':id_miembro' => $id_miembro,
        ':id_curso' => $id_curso
    ]);
    $existe = $stmt->fetch();

    if ($existe) {
        // Actualizar inscripción existente
        $stmt_update = $conn->prepare("
            UPDATE inscripciones
            SET estado = :estado,
                fecha_inicio = :fecha_inicio,
                fecha_finalizacion = :fecha_finalizacion,
                nota_final = :nota_final,
                asistencia = :asistencia
            WHERE id_inscripcion = :id
        ");
        $stmt_update->execute([
            ':estado' => $estado,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_finalizacion' => $fecha_finalizacion,
            ':nota_final' => $nota_final,
            ':asistencia' => $asistencia,
            ':id' => $existe['id_inscripcion']
        ]);
        $estadisticas['inscripciones_actualizadas']++;
        return $existe['id_inscripcion'];
    } else {
        // Insertar nueva inscripción con id_miembro
        $stmt_insert = $conn->prepare("
            INSERT INTO inscripciones
            (id_miembro, id_curso, estado, fecha_inscripcion, fecha_inicio, fecha_finalizacion, nota_final, asistencia)
            VALUES
            (:id_miembro, :id_curso, :estado, NOW(), :fecha_inicio, :fecha_finalizacion, :nota_final, :asistencia)
        ");
        $stmt_insert->execute([
            ':id_miembro' => $id_miembro,
            ':id_curso' => $id_curso,
            ':estado' => $estado,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_finalizacion' => $fecha_finalizacion,
            ':nota_final' => $nota_final,
            ':asistencia' => $asistencia
        ]);
        $estadisticas['inscripciones_insertadas']++;
        return $conn->lastInsertId();
    }
}

/**
 * Inserta competencias para una inscripción
 * NOTA: Usa tabla competencias_inscripcion (renombrada de competencias_curso)
 * Las competencias del curso ahora van en tabla 'competencias'
 */
function insertarCompetencias($conn, $id_inscripcion, $competencias) {
    // Eliminar competencias anteriores de la inscripción
    $stmt_delete = $conn->prepare("DELETE FROM competencias_inscripcion WHERE id_inscripcion = :id");
    $stmt_delete->execute([':id' => $id_inscripcion]);

    // Insertar nuevas competencias específicas de la inscripción
    $stmt_insert = $conn->prepare("
        INSERT INTO competencias_inscripcion (id_inscripcion, competencia, orden)
        VALUES (:id_inscripcion, :competencia, :orden)
    ");

    foreach ($competencias as $index => $competencia) {
        $competencia = trim($competencia);
        if (!empty($competencia)) {
            $stmt_insert->execute([
                ':id_inscripcion' => $id_inscripcion,
                ':competencia' => $competencia,
                ':orden' => $index + 1
            ]);
        }
    }
}

/**
 * Inserta competencias para un curso (nueva función)
 * Usa la nueva tabla 'competencias' separada
 */
function insertarCompetenciasCurso($conn, $id_curso, $competencias) {
    // Desactivar competencias anteriores (soft delete)
    $stmt_deactivate = $conn->prepare("UPDATE competencias SET activo = 0 WHERE id_curso = :id_curso");
    $stmt_deactivate->execute([':id_curso' => $id_curso]);

    // Insertar nuevas competencias
    $stmt_insert = $conn->prepare("
        INSERT INTO competencias (id_curso, competencia, orden, activo)
        VALUES (:id_curso, :competencia, :orden, 1)
    ");

    foreach ($competencias as $index => $competencia) {
        $competencia = trim($competencia);
        if (!empty($competencia)) {
            $stmt_insert->execute([
                ':id_curso' => $id_curso,
                ':competencia' => $competencia,
                ':orden' => $index + 1
            ]);
        }
    }
}

/**
 * Inserta trayectoria para una inscripción
 * Formato: "01/03/2024;Inicio del curso||15/04/2024;Módulo 1;Evaluación: 9/10"
 */
function insertarTrayectoria($conn, $id_inscripcion, $trayectoria_raw) {
    // Eliminar trayectoria anterior
    $stmt_delete = $conn->prepare("DELETE FROM trayectoria WHERE id_inscripcion = :id");
    $stmt_delete->execute([':id' => $id_inscripcion]);

    // Separar eventos (separados por ||)
    $eventos = explode('||', $trayectoria_raw);

    $stmt_insert = $conn->prepare("
        INSERT INTO trayectoria (id_inscripcion, fecha, evento, detalle, orden)
        VALUES (:id_inscripcion, :fecha, :evento, :detalle, :orden)
    ");

    foreach ($eventos as $index => $evento_raw) {
        $evento_raw = trim($evento_raw);
        if (empty($evento_raw)) continue;

        // Separar campos del evento (separados por ;)
        $partes = explode(';', $evento_raw);

        $fecha_evento = convertirFecha($partes[0] ?? '');
        $nombre_evento = trim($partes[1] ?? '');
        $detalle_evento = trim($partes[2] ?? '');

        if (!empty($nombre_evento)) {
            $stmt_insert->execute([
                ':id_inscripcion' => $id_inscripcion,
                ':fecha' => $fecha_evento,
                ':evento' => $nombre_evento,
                ':detalle' => $detalle_evento,
                ':orden' => $index + 1
            ]);
        }
    }
}

/**
 * Limpia el DNI removiendo puntos, espacios y guiones
 */
function limpiarDNI($dni) {
    return preg_replace('/[^0-9]/', '', $dni);
}

/**
 * Convierte fecha de DD/MM/YYYY a YYYY-MM-DD (formato MySQL)
 */
function convertirFecha($fecha) {
    if (empty($fecha)) return null;

    $fecha = trim($fecha);

    // Si ya está en formato YYYY-MM-DD, retornar
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return $fecha;
    }

    // Convertir de DD/MM/YYYY a YYYY-MM-DD
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $matches)) {
        $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $anio = $matches[3];
        return "$anio-$mes-$dia";
    }

    return null;
}

/**
 * ========================================
 * FUNCIONES DE CARGA ESPECÍFICA
 * ========================================
 */

/**
 * Procesa solo estudiantes
 * Formato CSV extendido: DNI, Nombre, Apellido, Email, Telefono, Ciudad, Provincia, CodPostal, Pais, LugarTrabajo, Cargo, Profesion
 * Formato CSV básico: DNI, Nombre, Apellido, Email (campos opcionales después de Apellido)
 * Formato CSV antiguo: DNI, Nombre Completo (para compatibilidad)
 */
function procesarSoloEstudiantes($texto, $institucion) {
    $lineas = explode("\n", $texto);
    $estadisticas = [
        'estudiantes_insertados' => 0,
        'estudiantes_actualizados' => 0,
        'errores' => []
    ];

    try {
        $conn = getCertDBConnection();
        $conn->beginTransaction();

        foreach ($lineas as $index => $linea) {
            $linea = trim($linea);
            if (empty($linea)) continue;

            $campos = str_getcsv($linea, ',', '"', '\\');
            if (count($campos) < 2) {
                $estadisticas['errores'][] = "Línea " . ($index + 1) . ": Formato incorrecto (se requieren al menos DNI y Nombre)";
                continue;
            }

            $dni = limpiarDNI($campos[0]);

            // Detectar formato: nuevo (DNI, Nombre, Apellido, ...) o antiguo (DNI, Nombre Completo)
            if (count($campos) >= 3) {
                // Formato nuevo: DNI, Nombre, Apellido, Email, Telefono, Ciudad, Provincia, CodPostal, Pais, LugarTrabajo, Cargo, Profesion
                $nombre = strtoupper(trim($campos[1]));
                $apellido = strtoupper(trim($campos[2]));
                $email = isset($campos[3]) && !empty(trim($campos[3])) ? trim($campos[3]) : '';
                $telefono = isset($campos[4]) && !empty(trim($campos[4])) ? trim($campos[4]) : '';
                $ciudad = isset($campos[5]) && !empty(trim($campos[5])) ? trim($campos[5]) : '';
                $provincia = isset($campos[6]) && !empty(trim($campos[6])) ? trim($campos[6]) : '';
                $codigo_postal = isset($campos[7]) && !empty(trim($campos[7])) ? trim($campos[7]) : '';
                $pais = isset($campos[8]) && !empty(trim($campos[8])) ? trim($campos[8]) : '';
                $lugar_trabajo = isset($campos[9]) && !empty(trim($campos[9])) ? trim($campos[9]) : '';
                $cargo = isset($campos[10]) && !empty(trim($campos[10])) ? trim($campos[10]) : '';
                $profesion = isset($campos[11]) && !empty(trim($campos[11])) ? trim($campos[11]) : '';
            } else {
                // Formato antiguo: DNI, Nombre Completo
                $nombre_completo = strtoupper(trim($campos[1]));
                $partes = explode(' ', $nombre_completo, 2);
                $nombre = $partes[0] ?? '';
                $apellido = $partes[1] ?? '';
                $email = '';
                $telefono = '';
                $ciudad = '';
                $provincia = '';
                $codigo_postal = '';
                $pais = '';
                $lugar_trabajo = '';
                $cargo = '';
                $profesion = '';
            }

            if (empty($dni) || empty($nombre)) {
                $estadisticas['errores'][] = "Línea " . ($index + 1) . ": DNI o nombre vacío";
                continue;
            }

            // Preparar datos adicionales
            $datos_extra = [
                'telefono' => $telefono,
                'domicilio_ciudad' => $ciudad,
                'domicilio_provincia' => $provincia,
                'domicilio_codigo_postal' => $codigo_postal,
                'domicilio_pais' => $pais,
                'lugar_trabajo' => $lugar_trabajo,
                'cargo' => $cargo,
                'profesion' => $profesion
            ];

            insertarOActualizarEstudianteCompleto($conn, $institucion, $dni, $nombre, $apellido, $email, $estadisticas, $datos_extra);
        }

        $conn->commit();
    } catch (Exception $e) {
        if (isset($conn)) $conn->rollBack();
        $estadisticas['errores'][] = "Error general: " . $e->getMessage();
    }

    return $estadisticas;
}

/**
 * Inserta o actualiza un estudiante con campos separados
 *
 * @param PDO $conn Conexión a la base de datos
 * @param string|int $id_instancia ID o slug de la instancia
 * @param string $dni DNI del estudiante
 * @param string $nombre Nombre
 * @param string $apellido Apellido
 * @param string $email Email (opcional)
 * @param array &$estadisticas Referencia a estadísticas
 * @param array $datos_extra Campos adicionales (telefono, domicilio_ciudad, etc.)
 * @return int|null ID del estudiante
 */
function insertarOActualizarEstudianteCompleto($conn, $id_instancia, $dni, $nombre, $apellido, $email, &$estadisticas, $datos_extra = []) {
    // Determinar si es id numérico o slug y obtener id_instancia numérico
    $es_id_numerico = is_numeric($id_instancia);
    $id_inst_numerico = $id_instancia;

    if (!$es_id_numerico) {
        // Obtener id_instancia desde slug
        $stmt_id = $conn->prepare("SELECT id_instancia FROM verumax_general.instances WHERE slug = :slug");
        $stmt_id->execute([':slug' => $id_instancia]);
        $id_inst_numerico = $stmt_id->fetchColumn();
        if (!$id_inst_numerico) {
            error_log("No se encontró instancia para slug: $id_instancia");
            return null;
        }
    }

    // Usar conexión a Nexus
    $nexus_conn = DatabaseService::get('nexus');

    // Campos adicionales permitidos
    $campos_extra_permitidos = [
        'telefono', 'domicilio_ciudad', 'domicilio_provincia',
        'domicilio_codigo_postal', 'domicilio_pais',
        'lugar_trabajo', 'cargo', 'profesion'
    ];

    // Verificar si el miembro ya existe en Nexus
    $stmt = $nexus_conn->prepare("
        SELECT id_miembro FROM miembros
        WHERE id_instancia = :id_instancia AND identificador_principal = :dni
        LIMIT 1
    ");
    $stmt->execute([':id_instancia' => $id_inst_numerico, ':dni' => $dni]);
    $existe = $stmt->fetch();

    if ($existe) {
        // Actualizar datos
        $sql = "UPDATE miembros SET nombre = :nombre, apellido = :apellido";
        $params = [
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':id' => $existe['id_miembro']
        ];

        // Solo actualizar email si se proporcionó
        if (!empty($email)) {
            $sql .= ", email = :email";
            $params[':email'] = $email;
        }

        // Agregar campos extra si tienen valor
        foreach ($campos_extra_permitidos as $campo) {
            if (!empty($datos_extra[$campo])) {
                $sql .= ", $campo = :$campo";
                $params[":$campo"] = $datos_extra[$campo];
            }
        }

        $sql .= " WHERE id_miembro = :id";

        $stmt_update = $nexus_conn->prepare($sql);
        $stmt_update->execute($params);

        // Asegurar que tenga rol de Estudiante en miembro_roles
        $stmt_rol = $nexus_conn->prepare("
            INSERT INTO miembro_roles (id_miembro, rol, activo)
            VALUES (:id_miembro, 'Estudiante', 1)
            ON DUPLICATE KEY UPDATE activo = 1
        ");
        $stmt_rol->execute([':id_miembro' => $existe['id_miembro']]);

        $estadisticas['estudiantes_actualizados']++;
        return $existe['id_miembro'];
    } else {
        // Insertar nuevo miembro en Nexus
        $columnas = ['id_instancia', 'identificador_principal', 'nombre', 'apellido', 'tipo_miembro', 'estado'];
        $placeholders = [':id_instancia', ':dni', ':nombre', ':apellido', "'Estudiante'", "'Activo'"];
        $params = [
            ':id_instancia' => $id_inst_numerico,
            ':dni' => $dni,
            ':nombre' => $nombre,
            ':apellido' => $apellido
        ];

        if (!empty($email)) {
            $columnas[] = 'email';
            $placeholders[] = ':email';
            $params[':email'] = $email;
        }

        // Agregar campos extra si tienen valor
        foreach ($campos_extra_permitidos as $campo) {
            if (!empty($datos_extra[$campo])) {
                $columnas[] = $campo;
                $placeholders[] = ":$campo";
                $params[":$campo"] = $datos_extra[$campo];
            }
        }

        $sql = "INSERT INTO miembros (" . implode(', ', $columnas) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $stmt_insert = $nexus_conn->prepare($sql);
        $stmt_insert->execute($params);
        $id_miembro = $nexus_conn->lastInsertId();

        // Insertar rol de Estudiante en miembro_roles
        $stmt_rol = $nexus_conn->prepare("
            INSERT INTO miembro_roles (id_miembro, rol, activo)
            VALUES (:id_miembro, 'Estudiante', 1)
            ON DUPLICATE KEY UPDATE activo = 1
        ");
        $stmt_rol->execute([':id_miembro' => $id_miembro]);

        $estadisticas['estudiantes_insertados']++;
        return $id_miembro;
    }
}

/**
 * Procesa solo cursos (Código, Nombre, Carga Horaria)
 * Formato CSV: Código Curso,Nombre Curso,Carga Horaria
 *
 * @param string $texto Texto CSV con los cursos
 * @param string|int $institucion Slug o ID de la institución (OBLIGATORIO - no hay cursos globales)
 */
function procesarSoloCursos($texto, $institucion) {
    $lineas = explode("\n", $texto);
    $estadisticas = [
        'cursos_insertados' => 0,
        'cursos_actualizados' => 0,
        'errores' => []
    ];

    try {
        $conn = getCertDBConnection();
        $conn->beginTransaction();

        // Obtener id_instancia de la institución (OBLIGATORIO - no hay cursos globales)
        $id_instancia = null;
        if (is_numeric($institucion)) {
            $id_instancia = (int)$institucion;
        } else {
            $stmt_inst = $conn->prepare("SELECT id_instancia FROM verumax_general.instances WHERE slug = :slug");
            $stmt_inst->execute([':slug' => $institucion]);
            $id_instancia = $stmt_inst->fetchColumn();
        }

        if (!$id_instancia) {
            throw new Exception("No se encontró la institución: $institucion. No se pueden crear cursos sin institución.");
        }

        foreach ($lineas as $index => $linea) {
            $linea = trim($linea);
            if (empty($linea)) continue;

            $campos = str_getcsv($linea, ',', '"', '\\');
            if (count($campos) < 3) {
                $estadisticas['errores'][] = "Línea " . ($index + 1) . ": Formato incorrecto (se requieren Código, Nombre, Horas)";
                continue;
            }

            $codigo = trim($campos[0]);
            $nombre = trim($campos[1]);
            $horas = intval($campos[2]);

            if (empty($codigo) || empty($nombre) || $horas <= 0) {
                $estadisticas['errores'][] = "Línea " . ($index + 1) . ": Datos inválidos";
                continue;
            }

            insertarOActualizarCurso($conn, $codigo, $nombre, $horas, $estadisticas, $id_instancia);
        }

        $conn->commit();
    } catch (Exception $e) {
        if (isset($conn)) $conn->rollBack();
        $estadisticas['errores'][] = "Error general: " . $e->getMessage();
    }

    return $estadisticas;
}

/**
 * Inscribe estudiantes a un curso específico
 * Formato CSV: DNI, Estado, Fecha Inicio, Nota Final, Asistencia
 * El estudiante debe existir previamente en el sistema (verumax_nexus.miembros)
 */
function procesarInscripcionesCurso($texto, $institucion, $id_curso) {
    $lineas = explode("\n", $texto);
    $estadisticas = [
        'estudiantes_insertados' => 0,
        'estudiantes_actualizados' => 0,
        'inscripciones_insertadas' => 0,
        'inscripciones_actualizadas' => 0,
        'errores' => [],
        'destinatarios_email' => [],
        'id_instancia' => null,
        'nombre_curso' => null
    ];

    try {
        $conn = getCertDBConnection();
        $conn->beginTransaction();

        // Obtener id_instancia desde la institución
        $stmt_inst = $conn->prepare("SELECT id_instancia FROM verumax_general.instances WHERE slug = :slug");
        $stmt_inst->execute([':slug' => $institucion]);
        $id_instancia = $stmt_inst->fetchColumn();

        if (!$id_instancia) {
            throw new Exception("Institución no encontrada: $institucion");
        }

        $estadisticas['id_instancia'] = $id_instancia;

        // Obtener nombre y tipo del curso
        $stmt_curso = $conn->prepare("SELECT nombre_curso, tipo_curso FROM verumax_academi.cursos WHERE id_curso = :id_curso");
        $stmt_curso->execute([':id_curso' => $id_curso]);
        $curso_info = $stmt_curso->fetch(PDO::FETCH_ASSOC);
        $nombre_curso = $curso_info['nombre_curso'] ?? '';
        $estadisticas['nombre_curso'] = $nombre_curso;
        $estadisticas['tipo_curso'] = $curso_info['tipo_curso'] ?? 'curso';

        foreach ($lineas as $index => $linea) {
            $linea = trim($linea);
            if (empty($linea)) continue;

            $campos = str_getcsv($linea, ',', '"', '\\');
            if (count($campos) < 1) {
                $estadisticas['errores'][] = "Línea " . ($index + 1) . ": Se requiere al menos el DNI";
                continue;
            }

            $dni = limpiarDNI($campos[0]);
            $estado = trim($campos[1] ?? 'Inscrito');
            $fecha_inicio = convertirFecha($campos[2] ?? '');
            $nota = !empty($campos[3]) ? floatval($campos[3]) : null;
            $asistencia = trim(str_replace('%', '', $campos[4] ?? ''));
            // Campo 5: fecha_fin (opcional, retrocompatible)
            $fecha_fin = !empty($campos[5]) ? convertirFecha($campos[5]) : null;

            if (empty($dni)) {
                $estadisticas['errores'][] = "Línea " . ($index + 1) . ": DNI vacío";
                continue;
            }

            // Buscar estudiante existente en verumax_nexus.miembros (incluyendo email)
            $stmt_est = $conn->prepare("
                SELECT id_miembro, nombre_completo, email
                FROM verumax_nexus.miembros
                WHERE identificador_principal = :dni AND id_instancia = :id_instancia
            ");
            $stmt_est->execute([':dni' => $dni, ':id_instancia' => $id_instancia]);
            $estudiante = $stmt_est->fetch(PDO::FETCH_ASSOC);

            if (!$estudiante) {
                $estadisticas['errores'][] = "Línea " . ($index + 1) . ": Estudiante con DNI '$dni' no encontrado. Debe cargarlo primero en la pestaña Estudiantes.";
                continue;
            }

            $id_miembro = $estudiante['id_miembro'];

            // Insertar inscripción en verumax_academi.inscripciones
            insertarInscripcionAcademicus($conn, $id_instancia, $id_miembro, $id_curso, $estado, $fecha_inicio, $fecha_fin, $nota, $asistencia, $estadisticas);

            // Determinar tipo de documento según estado (normalizado a minúsculas)
            $tipos_documento = [
                'preinscrito' => 'Constancia de Preinscripción',
                'inscrito' => 'Constancia de Inscripción',
                'inscripto' => 'Constancia de Inscripción',
                'en curso' => 'Constancia de Alumno Regular',
                'finalizado' => 'Constancia de Finalización',
                'aprobado' => 'Certificado de Aprobación',
                'completado' => 'Certificado',
                'desaprobado' => 'Constancia de Cursado',
                'abandonado' => 'Constancia de Cursado',
                'suspendido' => 'Constancia de Cursado'
            ];
            $estado_normalizado = strtolower(trim($estado));
            $tipo_documento = $tipos_documento[$estado_normalizado] ?? 'Constancia';

            // Agregar a lista de destinatarios para email
            $estadisticas['destinatarios_email'][] = [
                'email' => $estudiante['email'],
                'nombre' => $estudiante['nombre_completo'],
                'variables' => [
                    'nombre_curso' => $nombre_curso,
                    'estado_inscripcion' => $estado,
                    'tipo_documento' => $tipo_documento
                ]
            ];
        }

        $conn->commit();
    } catch (Exception $e) {
        if (isset($conn)) $conn->rollBack();
        $estadisticas['errores'][] = "Error general: " . $e->getMessage();
    }

    return $estadisticas;
}

/**
 * Inserta una inscripción en verumax_academi.inscripciones
 */
function insertarInscripcionAcademicus($conn, $id_instancia, $id_miembro, $id_curso, $estado, $fecha_inicio, $fecha_finalizacion, $nota, $asistencia, &$estadisticas) {
    // Verificar si ya existe la inscripción
    $stmt_check = $conn->prepare("
        SELECT id_inscripcion FROM verumax_academi.inscripciones
        WHERE id_miembro = :id_miembro AND id_curso = :id_curso
    ");
    $stmt_check->execute([':id_miembro' => $id_miembro, ':id_curso' => $id_curso]);
    $existe = $stmt_check->fetch();

    if ($existe) {
        // Actualizar inscripción existente (incluyendo reactivar si estaba soft-deleted)
        $stmt_update = $conn->prepare("
            UPDATE verumax_academi.inscripciones SET
                estado = :estado,
                fecha_inicio = :fecha_inicio,
                fecha_finalizacion = :fecha_finalizacion,
                nota_final = :nota,
                asistencia_porcentaje = :asistencia,
                activo = 1
            WHERE id_inscripcion = :id_inscripcion
        ");
        $stmt_update->execute([
            ':estado' => $estado,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_finalizacion' => $fecha_finalizacion,
            ':nota' => $nota,
            ':asistencia' => $asistencia ?: null,
            ':id_inscripcion' => $existe['id_inscripcion']
        ]);
        $estadisticas['inscripciones_actualizadas']++;
    } else {
        // Insertar nueva inscripción
        $stmt_insert = $conn->prepare("
            INSERT INTO verumax_academi.inscripciones
            (id_instancia, id_miembro, id_curso, estado, fecha_inicio, fecha_finalizacion, nota_final, asistencia_porcentaje, activo)
            VALUES (:id_instancia, :id_miembro, :id_curso, :estado, :fecha_inicio, :fecha_finalizacion, :nota, :asistencia, 1)
        ");
        $stmt_insert->execute([
            ':id_instancia' => $id_instancia,
            ':id_miembro' => $id_miembro,
            ':id_curso' => $id_curso,
            ':estado' => $estado,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_finalizacion' => $fecha_finalizacion,
            ':nota' => $nota,
            ':asistencia' => $asistencia ?: null
        ]);
        $estadisticas['inscripciones_insertadas']++;
    }
}
?>
