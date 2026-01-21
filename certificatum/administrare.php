<?php
/**
 * ADMINISTRARE V2.1 - Panel de Administración Completo
 * Sistema CERTIFICATUM - VERUMax
 * Versión: 2.1 - Eliminados hardcodeos institucionales
 *
 * Panel multi-tenant para:
 * - Cargar datos (Excel, CSV, texto)
 * - Gestionar estudiantes
 * - Gestionar cursos
 * - Gestionar inscripciones
 */

session_start();
require_once 'config.php';
require_once 'administrare_procesador.php';
require_once 'administrare_gestionar.php';

use VERUMax\Services\InstitutionService;

// =====================================================
// AUTENTICACIÓN UNIFICADA CON ADMIN VERUMAX
// =====================================================
// Verificar que el usuario está autenticado desde el admin unificado
if (!isset($_SESSION['admin_verumax'])) {
    // No autenticado, redirigir al login unificado
    header('Location: https://verumax.com/admin/login.php');
    exit;
}

$admin = $_SESSION['admin_verumax'];

// Verificar que tiene acceso al módulo Certificatum
if (!$admin['modulos']['certificatum']) {
    die('Error: No tiene acceso al módulo Certificatum. Contacte al administrador.');
}

// Configurar institución desde la sesión unificada
$institucion = $admin['slug'];
$id_instancia = obtenerIdInstanciaPorSlug($institucion);

// Manejar logout (redirigir al logout unificado)
if (isset($_GET['logout'])) {
    header('Location: https://verumax.com/admin/logout.php');
    exit;
}

// Procesar acciones
$mensaje = null;
$resultado = null;
$errores = [];

// Obtener mensaje desde redirect (query string)
if (isset($_GET['msg'])) {
    $msg_data = json_decode($_GET['msg'], true);
    if ($msg_data && isset($msg_data['mensaje'])) {
        $mensaje = $msg_data;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    switch ($_POST['accion']) {
        // Cargar datos
        case 'cargar_excel':
            if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] === UPLOAD_ERR_OK) {
                $resultado = procesarExcel($_FILES['archivo_excel']['tmp_name'], $institucion);
            } else {
                $errores[] = 'Error al subir el archivo Excel';
            }
            break;

        case 'cargar_csv':
            if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] === UPLOAD_ERR_OK) {
                $resultado = procesarCSV($_FILES['archivo_csv']['tmp_name'], $institucion);
            } else {
                $errores[] = 'Error al subir el archivo CSV';
            }
            break;

        case 'cargar_texto':
            if (!empty($_POST['texto_csv'])) {
                $resultado = procesarTextoPlano($_POST['texto_csv'], $institucion);
            } else {
                $errores[] = 'El texto está vacío';
            }
            break;

        // Cargas específicas
        case 'cargar_estudiantes':
            if (!empty($_POST['texto_estudiantes'])) {
                $resultado = procesarSoloEstudiantes($_POST['texto_estudiantes'], $institucion);
            } else {
                $errores[] = 'El texto está vacío';
            }
            break;

        case 'cargar_cursos':
            if (!empty($_POST['texto_cursos'])) {
                $resultado = procesarSoloCursos($_POST['texto_cursos'], $institucion);
            } else {
                $errores[] = 'El texto está vacío';
            }
            break;

        case 'inscribir_curso':
            if (!empty($_POST['texto_inscripciones']) && !empty($_POST['id_curso_inscribir'])) {
                $resultado = procesarInscripcionesCurso($_POST['texto_inscripciones'], $institucion, $_POST['id_curso_inscribir']);
            } else {
                $errores[] = 'Datos incompletos';
            }
            break;

        // Gestión de estudiantes
        case 'actualizar_estudiante':
            $mensaje = actualizarEstudiante($_POST['id_estudiante'], $_POST['dni'], $_POST['nombre_completo']);
            break;

        case 'eliminar_estudiante':
            $mensaje = eliminarEstudiante($_POST['id_estudiante']);
            break;

        // Gestión de cursos
        case 'actualizar_curso':
            // id_template puede ser string vacío (usar null) o un ID numérico
            $id_template = isset($_POST['id_template']) && $_POST['id_template'] !== ''
                ? (int) $_POST['id_template']
                : null;

            // Campos de firmantes - checkboxes: si no están, es 0
            $usar_firmante_1 = isset($_POST['usar_firmante_1']) ? 1 : 0;
            $usar_firmante_2 = isset($_POST['usar_firmante_2']) ? 1 : 0;

            // Datos de firmantes (vacío = usar los de la institución)
            $firmante_1_nombre = $_POST['firmante_1_nombre'] ?? '';
            $firmante_1_cargo = $_POST['firmante_1_cargo'] ?? '';
            $firmante_2_nombre = $_POST['firmante_2_nombre'] ?? '';
            $firmante_2_cargo = $_POST['firmante_2_cargo'] ?? '';

            // URLs de firmas (pueden venir del hidden input si ya existen)
            $firmante_1_firma_url = $_POST['firmante_1_firma_url'] ?? '';
            $firmante_2_firma_url = $_POST['firmante_2_firma_url'] ?? '';

            // Procesar upload de firma 1 si se subió archivo
            if (isset($_FILES['firmante_1_firma']) && $_FILES['firmante_1_firma']['error'] === UPLOAD_ERR_OK) {
                $firmas_dir = __DIR__ . '/../assets/images/firmas/cursos/';
                if (!is_dir($firmas_dir)) {
                    mkdir($firmas_dir, 0755, true);
                }
                $ext = strtolower(pathinfo($_FILES['firmante_1_firma']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    $filename = 'curso_' . $_POST['id_curso'] . '_firma1.' . $ext;
                    $filepath = $firmas_dir . $filename;
                    // Eliminar archivo anterior si existe con diferente extensión
                    foreach (['png', 'jpg', 'jpeg'] as $old_ext) {
                        $old_file = $firmas_dir . 'curso_' . $_POST['id_curso'] . '_firma1.' . $old_ext;
                        if (file_exists($old_file) && $old_file !== $filepath) {
                            unlink($old_file);
                        }
                    }
                    if (move_uploaded_file($_FILES['firmante_1_firma']['tmp_name'], $filepath)) {
                        $firmante_1_firma_url = 'assets/images/firmas/cursos/' . $filename;
                    }
                }
            }

            // Procesar upload de firma 2 si se subió archivo
            if (isset($_FILES['firmante_2_firma']) && $_FILES['firmante_2_firma']['error'] === UPLOAD_ERR_OK) {
                $firmas_dir = __DIR__ . '/../assets/images/firmas/cursos/';
                if (!is_dir($firmas_dir)) {
                    mkdir($firmas_dir, 0755, true);
                }
                $ext = strtolower(pathinfo($_FILES['firmante_2_firma']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    $filename = 'curso_' . $_POST['id_curso'] . '_firma2.' . $ext;
                    $filepath = $firmas_dir . $filename;
                    // Eliminar archivo anterior si existe con diferente extensión
                    foreach (['png', 'jpg', 'jpeg'] as $old_ext) {
                        $old_file = $firmas_dir . 'curso_' . $_POST['id_curso'] . '_firma2.' . $old_ext;
                        if (file_exists($old_file) && $old_file !== $filepath) {
                            unlink($old_file);
                        }
                    }
                    if (move_uploaded_file($_FILES['firmante_2_firma']['tmp_name'], $filepath)) {
                        $firmante_2_firma_url = 'assets/images/firmas/cursos/' . $filename;
                    }
                }
            }

            $mensaje = actualizarCurso(
                $_POST['id_curso'],
                $_POST['codigo_curso'],
                $_POST['nombre_curso'],
                $_POST['carga_horaria'],
                $_POST['activo'],
                $id_template,
                $usar_firmante_1,
                $usar_firmante_2,
                $firmante_1_nombre,
                $firmante_1_cargo,
                $firmante_2_nombre,
                $firmante_2_cargo,
                $firmante_1_firma_url,
                $firmante_2_firma_url
            );
            break;

        case 'eliminar_curso':
            $mensaje = eliminarCurso($_POST['id_curso']);
            break;

        // Gestión de inscripciones
        case 'actualizar_inscripcion':
            $mensaje = actualizarInscripcion(
                $_POST['id_inscripcion'],
                $_POST['estado'],
                $_POST['fecha_inicio'],
                $_POST['fecha_finalizacion'],
                $_POST['nota_final'],
                $_POST['asistencia']
            );
            break;

        case 'eliminar_inscripcion':
            $mensaje = eliminarInscripcion($_POST['id_inscripcion']);
            break;

        // Gestión de docentes
        case 'cargar_docentes':
            if (!empty($_POST['texto_docentes'])) {
                $resultado = procesarSoloDocentes($_POST['texto_docentes'], $institucion);
            } else {
                $errores[] = 'El texto está vacío';
            }
            break;

        case 'crear_docente':
            $mensaje = crearDocente(
                $institucion,
                $_POST['dni'],
                $_POST['nombre_completo'],
                $_POST['email'] ?? '',
                $_POST['especialidad'] ?? '',
                $_POST['titulo'] ?? ''
            );
            break;

        case 'actualizar_docente':
            $mensaje = actualizarDocente(
                $_POST['id_docente'],
                $_POST['dni'],
                $_POST['nombre_completo'],
                $_POST['email'] ?? '',
                $_POST['especialidad'] ?? '',
                $_POST['titulo'] ?? ''
            );
            break;

        case 'eliminar_docente':
            $mensaje = eliminarDocente($_POST['id_docente']);
            break;

        // Gestión de participaciones docentes
        case 'crear_participacion':
            $mensaje = crearParticipacionDocente(
                $_POST['id_docente'],
                $_POST['id_curso'],
                $_POST['rol'] ?? 'docente',
                [
                    'titulo_participacion' => $_POST['titulo_participacion'] ?? null,
                    'descripcion' => $_POST['descripcion'] ?? null,
                    'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
                    'fecha_fin' => $_POST['fecha_fin'] ?? null,
                    'carga_horaria_dictada' => $_POST['carga_horaria_dictada'] ?? null
                ]
            );
            break;

        case 'actualizar_participacion':
            $mensaje = actualizarParticipacionDocente(
                $_POST['id_participacion'],
                [
                    'rol' => $_POST['rol'] ?? 'docente',
                    'titulo_participacion' => $_POST['titulo_participacion'] ?? null,
                    'descripcion' => $_POST['descripcion'] ?? null,
                    'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
                    'fecha_fin' => $_POST['fecha_fin'] ?? null,
                    'carga_horaria_dictada' => $_POST['carga_horaria_dictada'] ?? null
                ]
            );
            break;

        case 'eliminar_participacion':
            $mensaje = eliminarParticipacionDocente($_POST['id_participacion']);
            break;

        // Gestión de evaluaciones (Probatio)
        case 'crear_evaluacion':
            $mensaje = crearEvaluacion($id_instancia, $_POST);
            break;

        case 'actualizar_evaluacion':
            $mensaje = actualizarEvaluacion($_POST['id_evaluatio'], $_POST);
            break;

        case 'eliminar_evaluacion':
            $mensaje = eliminarEvaluacion($_POST['id_evaluatio']);
            break;

        case 'duplicar_evaluacion':
            $mensaje = duplicarEvaluacion($_POST['id_evaluatio']);
            break;

        // Gestión de preguntas
        case 'crear_pregunta':
            $opciones = [];
            if (isset($_POST['opcion_letra']) && is_array($_POST['opcion_letra'])) {
                foreach ($_POST['opcion_letra'] as $i => $letra) {
                    $opciones[] = [
                        'letra' => $letra,
                        'texto' => $_POST['opcion_texto'][$i] ?? '',
                        'es_correcta' => in_array($letra, $_POST['opcion_correcta'] ?? [])
                    ];
                }
            }
            $_POST['opciones'] = $opciones;
            $mensaje = crearPregunta($_POST['id_evaluatio'], $_POST);
            break;

        case 'actualizar_pregunta':
            $opciones = [];
            if (isset($_POST['opcion_letra']) && is_array($_POST['opcion_letra'])) {
                foreach ($_POST['opcion_letra'] as $i => $letra) {
                    $opciones[] = [
                        'letra' => $letra,
                        'texto' => $_POST['opcion_texto'][$i] ?? '',
                        'es_correcta' => in_array($letra, $_POST['opcion_correcta'] ?? [])
                    ];
                }
            }
            $_POST['opciones'] = $opciones;
            $mensaje = actualizarPregunta($_POST['id_quaestio'], $_POST);
            break;

        case 'eliminar_pregunta':
            $mensaje = eliminarPregunta($_POST['id_quaestio']);
            // Redirect para volver a la vista de preguntas
            $redirect_evaluatio = $_POST['id_evaluatio'] ?? 0;
            if ($redirect_evaluatio) {
                header('Location: ?preguntas=' . $redirect_evaluatio . '&msg=' . urlencode(json_encode($mensaje)));
                exit;
            }
            break;
    }

    // Redirect después de acciones de preguntas para mantener la vista
    if (in_array($_POST['accion'], ['crear_pregunta', 'actualizar_pregunta'])) {
        $redirect_evaluatio = $_POST['id_evaluatio'] ?? 0;
        if ($redirect_evaluatio) {
            header('Location: ?preguntas=' . $redirect_evaluatio . '&msg=' . urlencode(json_encode($mensaje)));
            exit;
        }
    }
}

// Obtener datos para las pestañas de gestión
$buscar = $_GET['buscar'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

$estudiantes = obtenerEstudiantes($institucion, $buscar);
$cursos = obtenerCursos($id_instancia, false, $buscar);
$inscripciones = obtenerInscripciones($institucion, $filtro_estado, $buscar);
$docentes = obtenerDocentes($institucion, $buscar);
$participaciones_docentes = obtenerParticipacionesDocentes($id_instancia, $buscar);

// Evaluaciones (Probatio)
$filtro_estado_eval = $_GET['estado_eval'] ?? '';
$evaluaciones = obtenerEvaluaciones($id_instancia, $filtro_estado_eval, $buscar);

// Subvista de preguntas
$ver_preguntas = isset($_GET['preguntas']) ? (int)$_GET['preguntas'] : 0;
$evaluacion_actual = null;
$preguntas_actual = [];
if ($ver_preguntas > 0) {
    $evaluacion_actual = obtenerEvaluacionPorId($ver_preguntas);
    $preguntas_actual = obtenerPreguntasAdmin($ver_preguntas);
}

// Configuración institucional usando InstitutionService (sin hardcodeos)
$style_path = defined('PROXY_MODE') ? INSTITUCION_PATH . 'style.css' : '../' . htmlspecialchars($institucion) . '/style.css';

// Obtener nombre de la institución desde la base de datos
$instance_config = InstitutionService::getConfig($institucion);
$nombre_institucion = $instance_config['nombre'] ?? ucfirst($institucion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Administrare - <?php echo htmlspecialchars($nombre_institucion); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $style_path; ?>">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-button.active { border-bottom: 3px solid #3b82f6; color: #3b82f6; font-weight: 600; }
        .editable-row { transition: background-color 0.2s; }
        .editable-row:hover { background-color: #f9fafb; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto p-6">
        <!-- Header -->
        <header class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Panel de Administración</h1>
                    <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($nombre_institucion); ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="https://verumax.com/admin/" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Admin
                    </a>
                    <a href="https://<?php echo htmlspecialchars($institucion); ?>.verumax.com/" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-sm font-medium">
                        <i data-lucide="external-link" class="w-4 h-4"></i> Ver Sitio
                    </a>
                    <a href="https://verumax.com/admin/logout.php" class="inline-flex items-center gap-2 px-4 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition text-sm font-medium">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Cerrar sesión
                    </a>
                </div>
            </div>
        </header>

        <!-- Mensajes de resultado -->
        <?php if ($resultado): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6">
                <h3 class="font-bold mb-2">✓ Datos cargados exitosamente</h3>
                <ul class="text-sm space-y-1">
                    <li>Estudiantes procesados: <?php echo $resultado['estudiantes_insertados'] + $resultado['estudiantes_actualizados']; ?> (nuevos: <?php echo $resultado['estudiantes_insertados']; ?>, actualizados: <?php echo $resultado['estudiantes_actualizados']; ?>)</li>
                    <li>Cursos procesados: <?php echo $resultado['cursos_insertados'] + $resultado['cursos_actualizados']; ?> (nuevos: <?php echo $resultado['cursos_insertados']; ?>, actualizados: <?php echo $resultado['cursos_actualizados']; ?>)</li>
                    <li>Inscripciones procesadas: <?php echo $resultado['inscripciones_insertadas'] + $resultado['inscripciones_actualizadas']; ?> (nuevas: <?php echo $resultado['inscripciones_insertadas']; ?>, actualizadas: <?php echo $resultado['inscripciones_actualizadas']; ?>)</li>
                </ul>
            </div>
        <?php endif; ?>

        <?php
        $todos_errores = $errores;
        if ($resultado && !empty($resultado['errores'])) {
            $todos_errores = array_merge($todos_errores, $resultado['errores']);
        }
        ?>
        <?php if (!empty($todos_errores)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6">
                <h3 class="font-bold mb-2">⚠ Errores encontrados:</h3>
                <ul class="text-sm space-y-1">
                    <?php foreach ($todos_errores as $error): ?>
                        <li>• <?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($mensaje): ?>
            <div class="<?php echo $mensaje['success'] ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-6 py-4 rounded-lg mb-6">
                <?php echo htmlspecialchars($mensaje['mensaje']); ?>
            </div>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6 overflow-x-auto">
                    <button onclick="cambiarTab('estudiantes')" class="tab-button active py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                        <i data-lucide="users" class="w-5 h-5 inline mr-2"></i>
                        Estudiantes (<?php echo count($estudiantes); ?>)
                    </button>
                    <button onclick="cambiarTab('cursos')" class="tab-button py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                        <i data-lucide="book-open" class="w-5 h-5 inline mr-2"></i>
                        Cursos (<?php echo count($cursos); ?>)
                    </button>
                    <button onclick="cambiarTab('inscripciones')" class="tab-button py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                        <i data-lucide="file-check" class="w-5 h-5 inline mr-2"></i>
                        Inscripciones (<?php echo count($inscripciones); ?>)
                    </button>
                    <button onclick="cambiarTab('docentes')" class="tab-button py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                        <i data-lucide="graduation-cap" class="w-5 h-5 inline mr-2"></i>
                        Docentes (<?php echo count($docentes); ?>)
                    </button>
                    <button onclick="cambiarTab('evaluaciones')" class="tab-button py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                        <i data-lucide="clipboard-check" class="w-5 h-5 inline mr-2"></i>
                        Evaluaciones (<?php echo count($evaluaciones); ?>)
                    </button>
                    <button onclick="cambiarTab('ayuda')" class="tab-button py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                        <i data-lucide="help-circle" class="w-5 h-5 inline mr-2"></i>
                        Ayuda
                    </button>
                </nav>
            </div>

            <!-- Tab Content: Estudiantes -->
            <div id="tab-estudiantes" class="tab-content active p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">Gestionar Estudiantes</h2>
                    <form method="GET" class="flex gap-2">
                        <input type="hidden" name="institutio" value="<?php echo htmlspecialchars($institucion); ?>">
                        <input type="text" name="buscar" placeholder="Buscar por DNI o nombre..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Buscar</button>
                    </form>
                </div>

                <!-- Opciones de Carga para Estudiantes -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-green-900">
                            <i data-lucide="upload" class="w-5 h-5 inline mr-2"></i>
                            Cargar Solo Estudiantes
                        </h3>
                        <button onclick="mostrarFormEstudiantes()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                            + Cargar Estudiantes
                        </button>
                    </div>
                    <p class="text-sm text-green-800 mb-2">Esta opción permite cargar únicamente datos de estudiantes (sin cursos ni inscripciones)</p>

                    <!-- Formulario de carga de estudiantes -->
                    <div id="form-estudiantes" style="display: none;" class="mt-4">
                        <form method="POST" class="space-y-3">
                            <input type="hidden" name="accion" value="cargar_estudiantes">
                            <div>
                                <label class="block text-sm font-semibold text-green-900 mb-2">
                                    Formato: DNI, Nombre Completo (un estudiante por línea)
                                </label>
                                <textarea name="texto_estudiantes" rows="8" class="w-full border border-green-300 rounded px-3 py-2 font-mono text-sm" placeholder="25123456, Alejandro Rodriguez&#10;30987654, Sofía Gómez&#10;42555888, Martín Lopez" required></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 font-semibold">
                                    Cargar Estudiantes
                                </button>
                                <button type="button" onclick="ocultarFormEstudiantes()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">DNI</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Nombre Completo</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Total Cursos</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Aprobados</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">En Curso</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $est): ?>
                            <tr class="editable-row border-b" id="est-<?php echo $est['id_estudiante']; ?>">
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($est['dni']); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($est['nombre_completo']); ?></td>
                                <td class="px-4 py-3 text-center text-sm"><?php echo $est['total_cursos']; ?></td>
                                <td class="px-4 py-3 text-center text-sm text-green-600"><?php echo $est['cursos_aprobados']; ?></td>
                                <td class="px-4 py-3 text-center text-sm text-blue-600"><?php echo $est['cursos_en_curso']; ?></td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editarEstudiante(<?php echo $est['id_estudiante']; ?>, '<?php echo addslashes($est['dni']); ?>', '<?php echo addslashes($est['nombre_completo']); ?>')" class="text-blue-600 hover:text-blue-800 text-sm mr-2">
                                        <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                    </button>
                                    <button onclick="confirmarEliminarEstudiante(<?php echo $est['id_estudiante']; ?>)" class="text-red-600 hover:text-red-800 text-sm">
                                        <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($estudiantes)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No hay estudiantes registrados</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Cursos -->
            <div id="tab-cursos" class="tab-content p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">Gestionar Cursos</h2>
                    <form method="GET" class="flex gap-2">
                        <input type="hidden" name="institutio" value="<?php echo htmlspecialchars($institucion); ?>">
                        <input type="text" name="buscar" placeholder="Buscar por código o nombre..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Buscar</button>
                    </form>
                </div>

                <!-- Opciones de Carga para Cursos -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-purple-900">
                            <i data-lucide="book-open" class="w-5 h-5 inline mr-2"></i>
                            Cargar Solo Cursos
                        </h3>
                        <button onclick="mostrarFormCursos()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">
                            + Cargar Cursos
                        </button>
                    </div>
                    <p class="text-sm text-purple-800 mb-2">Esta opción permite cargar únicamente datos de cursos (sin estudiantes ni inscripciones)</p>

                    <!-- Formulario de carga de cursos -->
                    <div id="form-cursos" style="display: none;" class="mt-4">
                        <form method="POST" class="space-y-3">
                            <input type="hidden" name="accion" value="cargar_cursos">
                            <div>
                                <label class="block text-sm font-semibold text-purple-900 mb-2">
                                    Formato: Código Curso, Nombre Curso, Carga Horaria (un curso por línea)
                                </label>
                                <textarea name="texto_cursos" rows="8" class="w-full border border-purple-300 rounded px-3 py-2 font-mono text-sm" placeholder="SJ-DPA-2024, Derecho Penal Adolescente, 90&#10;SJ-MR-2024, Mediación y Resolución de Conflictos, 120&#10;SJ-JP-2024, Justicia Penal Juvenil, 80" required></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 font-semibold">
                                    Cargar Cursos
                                </button>
                                <button type="button" onclick="ocultarFormCursos()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Código</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Nombre del Curso</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Carga Horaria</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Inscripciones</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Estado</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cursos as $curso): ?>
                            <tr class="editable-row border-b">
                                <td class="px-4 py-3 text-sm font-mono"><?php echo htmlspecialchars($curso['codigo_curso']); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($curso['nombre_curso']); ?></td>
                                <td class="px-4 py-3 text-center text-sm"><?php echo $curso['carga_horaria']; ?> hs</td>
                                <td class="px-4 py-3 text-center text-sm"><?php echo $curso['total_inscripciones']; ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="<?php echo $curso['activo'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> px-2 py-1 rounded text-xs">
                                        <?php echo $curso['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editarCurso(<?php echo $curso['id_curso']; ?>, '<?php echo addslashes($curso['codigo_curso']); ?>', '<?php echo addslashes($curso['nombre_curso']); ?>', <?php echo $curso['carga_horaria']; ?>, <?php echo $curso['activo']; ?>)" class="text-blue-600 hover:text-blue-800 text-sm mr-2">
                                        <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                    </button>
                                    <button onclick="confirmarEliminarCurso(<?php echo $curso['id_curso']; ?>)" class="text-red-600 hover:text-red-800 text-sm">
                                        <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($cursos)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No hay cursos registrados</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Inscripciones -->
            <div id="tab-inscripciones" class="tab-content p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">Gestionar Inscripciones</h2>
                    <div class="flex gap-2">
                        <form method="GET" class="flex gap-2">
                            <input type="hidden" name="institutio" value="<?php echo htmlspecialchars($institucion); ?>">
                            <select name="estado" class="border border-gray-300 rounded px-3 py-2 text-sm">
                                <option value="">Todos los estados</option>
                                <option value="Aprobado" <?php echo $filtro_estado == 'Aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                                <option value="En Curso" <?php echo $filtro_estado == 'En Curso' ? 'selected' : ''; ?>>En Curso</option>
                                <option value="Finalizado" <?php echo $filtro_estado == 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                                <option value="Por Iniciar" <?php echo $filtro_estado == 'Por Iniciar' ? 'selected' : ''; ?>>Por Iniciar</option>
                            </select>
                            <input type="text" name="buscar" placeholder="Buscar..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded px-3 py-2 text-sm">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Filtrar</button>
                        </form>
                    </div>
                </div>

                <!-- Opciones de Carga para Inscripciones -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Opción 1: Carga Completa -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-blue-900">
                                <i data-lucide="database" class="w-5 h-5 inline mr-2"></i>
                                Carga Completa de Datos
                            </h3>
                            <button onclick="mostrarFormularioTexto()" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 text-sm">
                                + Cargar Todo
                            </button>
                        </div>
                        <p class="text-sm text-blue-800">Carga estudiantes + cursos + inscripciones desde Excel, CSV o texto</p>

                        <!-- Formulario de carga completa -->
                        <div id="formulario-texto" style="display: none;" class="mt-4 space-y-3">
                            <!-- Opción Excel -->
                            <form method="POST" enctype="multipart/form-data" class="border-b border-blue-200 pb-3">
                                <input type="hidden" name="accion" value="cargar_excel">
                                <label class="block text-sm font-semibold text-blue-900 mb-2">Subir archivo Excel (.xlsx)</label>
                                <div class="flex gap-2">
                                    <input type="file" name="archivo_excel" accept=".xlsx" required class="text-sm flex-1">
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Subir Excel</button>
                                </div>
                            </form>

                            <!-- Opción CSV -->
                            <form method="POST" enctype="multipart/form-data" class="border-b border-blue-200 pb-3">
                                <input type="hidden" name="accion" value="cargar_csv">
                                <label class="block text-sm font-semibold text-blue-900 mb-2">Subir archivo CSV</label>
                                <div class="flex gap-2">
                                    <input type="file" name="archivo_csv" accept=".csv" required class="text-sm flex-1">
                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Subir CSV</button>
                                </div>
                            </form>

                            <!-- Opción Texto -->
                            <form method="POST">
                                <input type="hidden" name="accion" value="cargar_texto">
                                <label class="block text-sm font-semibold text-blue-900 mb-2">O pegar datos de texto (formato CSV)</label>
                                <textarea name="texto_csv" rows="6" class="w-full border border-blue-300 rounded px-3 py-2 font-mono text-sm" placeholder="DNI,Nombre,Código,Curso,Estado,Horas,Inicio,Fin,Nota,Asistencia,Competencias,Trayectoria" required></textarea>
                                <div class="flex gap-2 mt-2">
                                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-semibold text-sm">
                                        Cargar Datos
                                    </button>
                                    <button type="button" onclick="ocultarFormularioTexto()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 text-sm">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Opción 2: Inscribir a Curso -->
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-orange-900">
                                <i data-lucide="user-plus" class="w-5 h-5 inline mr-2"></i>
                                Inscribir a Curso Específico
                            </h3>
                            <button onclick="mostrarFormInscripciones()" class="bg-orange-600 text-white px-3 py-2 rounded hover:bg-orange-700 text-sm">
                                + Inscribir
                            </button>
                        </div>
                        <p class="text-sm text-orange-800">Inscribe múltiples estudiantes a un curso existente</p>

                        <!-- Formulario de inscripción a curso -->
                        <div id="form-inscripciones" style="display: none;" class="mt-4">
                            <form method="POST" class="space-y-3">
                                <input type="hidden" name="accion" value="inscribir_curso">
                                <div>
                                    <label class="block text-sm font-semibold text-orange-900 mb-2">Seleccionar Curso</label>
                                    <select name="id_curso_inscribir" class="w-full border border-orange-300 rounded px-3 py-2 text-sm" required>
                                        <option value="">-- Seleccione un curso --</option>
                                        <?php foreach ($cursos as $c): ?>
                                            <option value="<?php echo $c['id_curso']; ?>">
                                                <?php echo htmlspecialchars($c['codigo_curso']) . ' - ' . htmlspecialchars($c['nombre_curso']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-orange-900 mb-2">
                                        Lista de DNIs (un DNI por línea)
                                    </label>
                                    <textarea name="texto_inscripciones" rows="6" class="w-full border border-orange-300 rounded px-3 py-2 font-mono text-sm" placeholder="25123456&#10;30987654&#10;42555888" required></textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded hover:bg-orange-700 font-semibold text-sm">
                                        Inscribir Estudiantes
                                    </button>
                                    <button type="button" onclick="ocultarFormInscripciones()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 text-sm">
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Estudiante</th>
                                <th class="px-3 py-2 text-left font-semibold">Curso</th>
                                <th class="px-3 py-2 text-center font-semibold">Estado</th>
                                <th class="px-3 py-2 text-center font-semibold">Nota</th>
                                <th class="px-3 py-2 text-center font-semibold">Asistencia</th>
                                <th class="px-3 py-2 text-center font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones as $insc): ?>
                            <tr class="editable-row border-b">
                                <td class="px-3 py-2">
                                    <div class="font-semibold"><?php echo htmlspecialchars($insc['nombre_completo']); ?></div>
                                    <div class="text-xs text-gray-500">DNI: <?php echo htmlspecialchars($insc['dni']); ?></div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="font-semibold"><?php echo htmlspecialchars($insc['nombre_curso']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($insc['codigo_curso']); ?></div>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="px-2 py-1 rounded text-xs <?php
                                        echo $insc['estado'] == 'Aprobado' ? 'bg-green-100 text-green-800' :
                                             ($insc['estado'] == 'En Curso' ? 'bg-blue-100 text-blue-800' :
                                             ($insc['estado'] == 'Finalizado' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'));
                                    ?>">
                                        <?php echo htmlspecialchars($insc['estado']); ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center"><?php echo $insc['nota_final'] ?: '-'; ?></td>
                                <td class="px-3 py-2 text-center"><?php echo htmlspecialchars($insc['asistencia']) ?: '-'; ?></td>
                                <td class="px-3 py-2 text-center">
                                    <button onclick="editarInscripcion(<?php echo $insc['id_inscripcion']; ?>, '<?php echo addslashes($insc['estado']); ?>', '<?php echo $insc['fecha_inicio']; ?>', '<?php echo $insc['fecha_finalizacion']; ?>', '<?php echo $insc['nota_final']; ?>', '<?php echo addslashes($insc['asistencia']); ?>')" class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                    </button>
                                    <button onclick="confirmarEliminarInscripcion(<?php echo $insc['id_inscripcion']; ?>)" class="text-red-600 hover:text-red-800">
                                        <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($inscripciones)): ?>
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-500">No hay inscripciones registradas</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Docentes -->
            <div id="tab-docentes" class="tab-content p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">Gestionar Docentes y Formadores</h2>
                    <form method="GET" class="flex gap-2">
                        <input type="hidden" name="institutio" value="<?php echo htmlspecialchars($institucion); ?>">
                        <input type="text" name="buscar" placeholder="Buscar por DNI o nombre..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Buscar</button>
                    </form>
                </div>

                <!-- Opciones de Carga para Docentes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Carga masiva de docentes -->
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-indigo-900">
                                <i data-lucide="users" class="w-5 h-5 inline mr-2"></i>
                                Cargar Docentes
                            </h3>
                            <button onclick="mostrarFormDocentes()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">
                                + Cargar Docentes
                            </button>
                        </div>
                        <p class="text-sm text-indigo-800">Formato: DNI, Nombre Completo, Email, Especialidad, Título</p>

                        <div id="form-docentes" style="display: none;" class="mt-4">
                            <form method="POST" class="space-y-3">
                                <input type="hidden" name="accion" value="cargar_docentes">
                                <textarea name="texto_docentes" rows="6" class="w-full border border-indigo-300 rounded px-3 py-2 font-mono text-sm" placeholder="12345678, Diana Márquez, diana@email.com, Justicia Restaurativa, Mg. en Mediación&#10;98765432, Carlos López, carlos@email.com, Derecho Penal, Dr. en Derecho" required></textarea>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 font-semibold text-sm">Cargar</button>
                                    <button type="button" onclick="ocultarFormDocentes()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 text-sm">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Agregar participación -->
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-bold text-purple-900">
                                <i data-lucide="calendar-plus" class="w-5 h-5 inline mr-2"></i>
                                Asignar a Curso
                            </h3>
                            <button onclick="mostrarFormParticipacion()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">
                                + Asignar
                            </button>
                        </div>
                        <p class="text-sm text-purple-800">Asigna un docente existente a un curso</p>

                        <div id="form-participacion" style="display: none;" class="mt-4">
                            <form method="POST" class="space-y-3">
                                <input type="hidden" name="accion" value="crear_participacion">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-semibold text-purple-900 mb-1">Docente</label>
                                        <select name="id_docente" class="w-full border border-purple-300 rounded px-3 py-2 text-sm" required>
                                            <option value="">-- Seleccione --</option>
                                            <?php foreach ($docentes as $doc): ?>
                                                <option value="<?php echo $doc['id_miembro']; ?>">
                                                    <?php echo htmlspecialchars($doc['nombre_completo'] . ' (' . $doc['dni'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-purple-900 mb-1">Curso</label>
                                        <select name="id_curso" class="w-full border border-purple-300 rounded px-3 py-2 text-sm" required>
                                            <option value="">-- Seleccione --</option>
                                            <?php foreach ($cursos as $c): ?>
                                                <option value="<?php echo $c['id_curso']; ?>">
                                                    <?php echo htmlspecialchars($c['codigo_curso'] . ' - ' . $c['nombre_curso']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-sm font-semibold text-purple-900 mb-1">Rol</label>
                                        <select name="rol" class="w-full border border-purple-300 rounded px-3 py-2 text-sm">
                                            <option value="docente">Docente</option>
                                            <option value="instructor">Instructor</option>
                                            <option value="orador">Orador</option>
                                            <option value="conferencista">Conferencista</option>
                                            <option value="facilitador">Facilitador</option>
                                            <option value="tutor">Tutor</option>
                                            <option value="coordinador">Coordinador</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-purple-900 mb-1">Fecha Inicio</label>
                                        <input type="date" name="fecha_inicio" class="w-full border border-purple-300 rounded px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-purple-900 mb-1">Fecha Fin</label>
                                        <input type="date" name="fecha_fin" class="w-full border border-purple-300 rounded px-3 py-2 text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-purple-900 mb-1">Título de participación (opcional)</label>
                                    <input type="text" name="titulo_participacion" class="w-full border border-purple-300 rounded px-3 py-2 text-sm" placeholder="Ej: Módulo de Introducción">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 font-semibold text-sm">Asignar</button>
                                    <button type="button" onclick="ocultarFormParticipacion()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 text-sm">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Docentes -->
                <h3 class="text-lg font-semibold mb-3 text-gray-700">Listado de Docentes</h3>
                <div class="overflow-x-auto mb-8">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">DNI</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Nombre Completo</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Email</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Especialidad</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Participaciones</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($docentes as $doc): ?>
                            <tr class="editable-row border-b">
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($doc['dni']); ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold"><?php echo htmlspecialchars($doc['nombre_completo']); ?></div>
                                    <?php if (!empty($doc['titulo'])): ?>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($doc['titulo']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($doc['email'] ?? '-'); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($doc['especialidad'] ?? '-'); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs"><?php echo $doc['total_participaciones']; ?></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editarDocente(<?php echo $doc['id_miembro']; ?>, '<?php echo addslashes($doc['dni']); ?>', '<?php echo addslashes($doc['nombre_completo']); ?>', '<?php echo addslashes($doc['email'] ?? ''); ?>', '<?php echo addslashes($doc['especialidad'] ?? ''); ?>', '<?php echo addslashes($doc['titulo'] ?? ''); ?>')" class="text-blue-600 hover:text-blue-800 text-sm mr-2">
                                        <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                    </button>
                                    <button onclick="confirmarEliminarDocente(<?php echo $doc['id_miembro']; ?>)" class="text-red-600 hover:text-red-800 text-sm">
                                        <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($docentes)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No hay docentes registrados</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tabla de Participaciones -->
                <h3 class="text-lg font-semibold mb-3 text-gray-700">Participaciones en Cursos</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">Docente</th>
                                <th class="px-3 py-2 text-left font-semibold">Curso</th>
                                <th class="px-3 py-2 text-center font-semibold">Rol</th>
                                <th class="px-3 py-2 text-center font-semibold">Período</th>
                                <th class="px-3 py-2 text-center font-semibold">Certificado</th>
                                <th class="px-3 py-2 text-center font-semibold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participaciones_docentes as $part): ?>
                            <tr class="editable-row border-b">
                                <td class="px-3 py-2">
                                    <div class="font-semibold"><?php echo htmlspecialchars($part['nombre_completo']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($part['dni']); ?></div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="font-semibold"><?php echo htmlspecialchars($part['nombre_curso']); ?></div>
                                    <?php if ($part['titulo_participacion']): ?>
                                        <div class="text-xs text-gray-500 italic"><?php echo htmlspecialchars($part['titulo_participacion']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs capitalize"><?php echo htmlspecialchars($part['rol']); ?></span>
                                </td>
                                <td class="px-3 py-2 text-center text-xs">
                                    <?php if ($part['fecha_inicio'] && $part['fecha_fin']): ?>
                                        <?php echo date('d/m/Y', strtotime($part['fecha_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($part['fecha_fin'])); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <?php if ($part['certificado_emitido']): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Emitido</span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button onclick="editarParticipacion(<?php echo $part['id_participacion']; ?>, '<?php echo addslashes($part['rol']); ?>', '<?php echo addslashes($part['titulo_participacion'] ?? ''); ?>', '<?php echo $part['fecha_inicio']; ?>', '<?php echo $part['fecha_fin']; ?>', '<?php echo $part['carga_horaria_dictada'] ?? ''; ?>')" class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                    </button>
                                    <button onclick="confirmarEliminarParticipacion(<?php echo $part['id_participacion']; ?>)" class="text-red-600 hover:text-red-800">
                                        <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($participaciones_docentes)): ?>
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-500">No hay participaciones registradas</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Evaluaciones -->
            <div id="tab-evaluaciones" class="tab-content p-6">
                <?php if ($ver_preguntas > 0 && $evaluacion_actual): ?>
                    <!-- Subvista: Gestión de Preguntas -->
                    <div class="mb-6">
                        <a href="?#evaluaciones" onclick="cambiarTab('evaluaciones')" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i data-lucide="arrow-left" class="w-4 h-4 inline"></i> Volver a Evaluaciones
                        </a>
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-xl font-bold">Preguntas de: <?php echo htmlspecialchars($evaluacion_actual['nombre']); ?></h2>
                            <p class="text-sm text-gray-500">Código: <?php echo htmlspecialchars($evaluacion_actual['codigo']); ?></p>
                        </div>
                        <button onclick="mostrarModalPregunta()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                            <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i> Nueva Pregunta
                        </button>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($preguntas_actual as $pregunta): ?>
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-bold">P<?php echo $pregunta['orden']; ?></span>
                                        <?php
                                        $tipo_colors = [
                                            'multiple_choice' => 'bg-blue-100 text-blue-800',
                                            'multiple_answer' => 'bg-purple-100 text-purple-800',
                                            'verdadero_falso' => 'bg-yellow-100 text-yellow-800',
                                            'abierta' => 'bg-green-100 text-green-800'
                                        ];
                                        $tipo_labels = [
                                            'multiple_choice' => 'Opción única',
                                            'multiple_answer' => 'Opción múltiple',
                                            'verdadero_falso' => 'V/F',
                                            'abierta' => 'Abierta'
                                        ];
                                        ?>
                                        <span class="<?php echo $tipo_colors[$pregunta['tipo']] ?? 'bg-gray-100 text-gray-800'; ?> px-2 py-1 rounded text-xs">
                                            <?php echo $tipo_labels[$pregunta['tipo']] ?? $pregunta['tipo']; ?>
                                        </span>
                                        <span class="text-xs text-gray-500"><?php echo $pregunta['puntos']; ?> pts</span>
                                    </div>
                                    <p class="text-gray-800"><?php echo htmlspecialchars(substr($pregunta['enunciado'], 0, 200)) . (strlen($pregunta['enunciado']) > 200 ? '...' : ''); ?></p>

                                    <?php if (!empty($pregunta['opciones']) && $pregunta['tipo'] !== 'abierta'): ?>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <?php foreach ($pregunta['opciones'] as $op): ?>
                                        <span class="<?php echo $op['es_correcta'] ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-50 text-gray-600 border-gray-200'; ?> border px-2 py-1 rounded text-xs">
                                            <?php echo htmlspecialchars($op['letra']); ?>: <?php echo htmlspecialchars(substr($op['texto'], 0, 30)); ?>...
                                            <?php if ($op['es_correcta']): ?><i data-lucide="check" class="w-3 h-3 inline"></i><?php endif; ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex gap-2 ml-4">
                                    <button onclick='editarPregunta(<?php echo json_encode($pregunta, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="text-blue-600 hover:text-blue-800">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="confirmarEliminarPregunta(<?php echo $pregunta['id_quaestio']; ?>, <?php echo $ver_preguntas; ?>)" class="text-red-600 hover:text-red-800">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (empty($preguntas_actual)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="help-circle" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>No hay preguntas. Haz clic en "Nueva Pregunta" para agregar.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Vista principal: Lista de Evaluaciones -->
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">Gestionar Evaluaciones</h2>
                        <div class="flex gap-2">
                            <form method="GET" class="flex gap-2">
                                <select name="estado_eval" class="border border-gray-300 rounded px-3 py-2 text-sm">
                                    <option value="">Todos los estados</option>
                                    <option value="borrador" <?php echo $filtro_estado_eval == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                                    <option value="activa" <?php echo $filtro_estado_eval == 'activa' ? 'selected' : ''; ?>>Activa</option>
                                    <option value="cerrada" <?php echo $filtro_estado_eval == 'cerrada' ? 'selected' : ''; ?>>Cerrada</option>
                                </select>
                                <input type="text" name="buscar" placeholder="Buscar..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded px-3 py-2 text-sm">
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Filtrar</button>
                            </form>
                            <button onclick="mostrarModalEvaluacion()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                                <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i> Nueva Evaluación
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold">Código</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold">Nombre</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold">Curso</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold">Preguntas</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold">Sesiones</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold">Estado</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluaciones as $eval): ?>
                                <tr class="editable-row border-b">
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-sm"><?php echo htmlspecialchars($eval['codigo']); ?></span>
                                        <button onclick="copiarEnlace('<?php echo htmlspecialchars($eval['codigo']); ?>')" class="text-gray-400 hover:text-blue-600 ml-2" title="Copiar enlace">
                                            <i data-lucide="copy" class="w-4 h-4 inline"></i>
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($eval['nombre']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-500"><?php echo htmlspecialchars($eval['nombre_curso'] ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="?preguntas=<?php echo $eval['id_evaluatio']; ?>" class="text-blue-600 hover:underline">
                                            <?php echo $eval['total_preguntas']; ?> preguntas
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm">
                                        <span class="text-gray-600"><?php echo $eval['sesiones_completadas']; ?>/<?php echo $eval['total_sesiones']; ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php
                                        $estado_colors = [
                                            'borrador' => 'bg-gray-100 text-gray-800',
                                            'activa' => 'bg-green-100 text-green-800',
                                            'cerrada' => 'bg-red-100 text-red-800',
                                            'archivada' => 'bg-yellow-100 text-yellow-800'
                                        ];
                                        ?>
                                        <span class="<?php echo $estado_colors[$eval['estado']] ?? 'bg-gray-100 text-gray-800'; ?> px-2 py-1 rounded text-xs">
                                            <?php echo ucfirst($eval['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button onclick='editarEvaluacion(<?php echo json_encode($eval, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="text-blue-600 hover:text-blue-800 text-sm mr-1" title="Editar">
                                            <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                        </button>
                                        <a href="?preguntas=<?php echo $eval['id_evaluatio']; ?>" class="text-purple-600 hover:text-purple-800 text-sm mr-1" title="Preguntas">
                                            <i data-lucide="list" class="w-4 h-4 inline"></i>
                                        </a>
                                        <button onclick="duplicarEvaluacion(<?php echo $eval['id_evaluatio']; ?>)" class="text-green-600 hover:text-green-800 text-sm mr-1" title="Duplicar">
                                            <i data-lucide="copy" class="w-4 h-4 inline"></i>
                                        </button>
                                        <?php if ($eval['total_sesiones'] == 0): ?>
                                        <button onclick="confirmarEliminarEvaluacion(<?php echo $eval['id_evaluatio']; ?>)" class="text-red-600 hover:text-red-800 text-sm" title="Eliminar">
                                            <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($evaluaciones)): ?>
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        <i data-lucide="clipboard-check" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                        <p>No hay evaluaciones. Haz clic en "Nueva Evaluación" para crear una.</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab Content: Ayuda -->
            <div id="tab-ayuda" class="tab-content p-6">
                <h2 class="text-xl font-bold mb-4">Ayuda y Documentación</h2>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h3 class="font-bold text-blue-900 mb-2">Formato CSV requerido:</h3>
                    <ol class="text-sm text-blue-800 space-y-1 ml-5 list-decimal">
                        <li>DNI - Sin puntos</li>
                        <li>Nombre Completo</li>
                        <li>Código Curso</li>
                        <li>Nombre Curso</li>
                        <li>Estado (Aprobado/En Curso/Finalizado/Por Iniciar)</li>
                        <li>Carga Horaria</li>
                        <li>Fecha Inicio (DD/MM/YYYY)</li>
                        <li>Fecha Finalización (DD/MM/YYYY)</li>
                        <li>Nota Final</li>
                        <li>Asistencia</li>
                        <li>Competencias (separadas por |)</li>
                        <li>Trayectoria (eventos separados por ||)</li>
                    </ol>
                </div>
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <h3 class="font-bold text-indigo-900 mb-2">Formato para Docentes:</h3>
                    <p class="text-sm text-indigo-800">DNI, Nombre Completo, Email, Especialidad, Título</p>
                    <p class="text-xs text-indigo-600 mt-2">Ejemplo: 12345678, Diana Márquez, diana@email.com, Justicia Restaurativa, Mg. en Mediación</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales para edición -->
    <div id="modal-estudiante" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4">Editar Estudiante</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="actualizar_estudiante">
                <input type="hidden" name="id_estudiante" id="edit_est_id">
                <label class="block mb-3">
                    <span class="text-sm font-semibold">DNI:</span>
                    <input type="text" name="dni" id="edit_est_dni" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
                </label>
                <label class="block mb-4">
                    <span class="text-sm font-semibold">Nombre Completo:</span>
                    <input type="text" name="nombre_completo" id="edit_est_nombre" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
                    <button type="button" onclick="cerrarModal('modal-estudiante')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-curso" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4">Editar Curso</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="actualizar_curso">
                <input type="hidden" name="id_curso" id="edit_curso_id">
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Código:</span>
                    <input type="text" name="codigo_curso" id="edit_curso_codigo" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Nombre:</span>
                    <input type="text" name="nombre_curso" id="edit_curso_nombre" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Carga Horaria:</span>
                    <input type="number" name="carga_horaria" id="edit_curso_horas" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Estado:</span>
                    <select name="activo" id="edit_curso_activo" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </label>

                <!-- Selector de Template de Certificado -->
                <div class="mb-4">
                    <span class="text-sm font-semibold block mb-2">Template de Certificado:</span>
                    <div id="template-selector" class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 border border-gray-200 rounded">
                        <!-- Opción predeterminada -->
                        <label class="template-option cursor-pointer">
                            <input type="radio" name="id_template" value="" class="hidden" checked>
                            <div class="border-2 border-gray-200 rounded p-2 hover:border-blue-400 transition-colors template-card" data-template="">
                                <div class="h-16 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">
                                    Sistema actual
                                </div>
                                <p class="text-xs mt-1 text-center font-medium truncate">Predeterminado</p>
                            </div>
                        </label>
                        <!-- Templates dinámicos se cargan via JS -->
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Selecciona un template o usa el predeterminado</p>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
                    <button type="button" onclick="cerrarModal('modal-curso')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-inscripcion" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4">Editar Inscripción</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="actualizar_inscripcion">
                <input type="hidden" name="id_inscripcion" id="edit_insc_id">
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Estado:</span>
                    <select name="estado" id="edit_insc_estado" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                        <option value="Por Iniciar">Por Iniciar</option>
                        <option value="En Curso">En Curso</option>
                        <option value="Finalizado">Finalizado</option>
                        <option value="Aprobado">Aprobado</option>
                    </select>
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Fecha Inicio:</span>
                    <input type="date" name="fecha_inicio" id="edit_insc_inicio" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Fecha Finalización:</span>
                    <input type="date" name="fecha_finalizacion" id="edit_insc_fin" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Nota Final:</span>
                    <input type="number" step="0.01" name="nota_final" id="edit_insc_nota" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                </label>
                <label class="block mb-4">
                    <span class="text-sm font-semibold">Asistencia:</span>
                    <input type="text" name="asistencia" id="edit_insc_asistencia" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="ej: 95%">
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
                    <button type="button" onclick="cerrarModal('modal-inscripcion')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar Docente -->
    <div id="modal-docente" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4">Editar Docente</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="actualizar_docente">
                <input type="hidden" name="id_docente" id="edit_doc_id">
                <label class="block mb-3">
                    <span class="text-sm font-semibold">DNI:</span>
                    <input type="text" name="dni" id="edit_doc_dni" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Nombre Completo:</span>
                    <input type="text" name="nombre_completo" id="edit_doc_nombre" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Email:</span>
                    <input type="email" name="email" id="edit_doc_email" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Especialidad:</span>
                    <input type="text" name="especialidad" id="edit_doc_especialidad" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                </label>
                <label class="block mb-4">
                    <span class="text-sm font-semibold">Título Académico:</span>
                    <input type="text" name="titulo" id="edit_doc_titulo" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Ej: Mg. en Mediación">
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
                    <button type="button" onclick="cerrarModal('modal-docente')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar Participación -->
    <div id="modal-participacion" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-xl font-bold mb-4">Editar Participación</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="actualizar_participacion">
                <input type="hidden" name="id_participacion" id="edit_part_id">
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Rol:</span>
                    <select name="rol" id="edit_part_rol" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                        <option value="docente">Docente</option>
                        <option value="instructor">Instructor</option>
                        <option value="orador">Orador</option>
                        <option value="conferencista">Conferencista</option>
                        <option value="facilitador">Facilitador</option>
                        <option value="tutor">Tutor</option>
                        <option value="coordinador">Coordinador</option>
                    </select>
                </label>
                <label class="block mb-3">
                    <span class="text-sm font-semibold">Título de participación:</span>
                    <input type="text" name="titulo_participacion" id="edit_part_titulo" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                </label>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <label class="block">
                        <span class="text-sm font-semibold">Fecha Inicio:</span>
                        <input type="date" name="fecha_inicio" id="edit_part_inicio" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Fecha Fin:</span>
                        <input type="date" name="fecha_fin" id="edit_part_fin" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                    </label>
                </div>
                <label class="block mb-4">
                    <span class="text-sm font-semibold">Carga Horaria Dictada:</span>
                    <input type="number" name="carga_horaria_dictada" id="edit_part_carga" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
                    <button type="button" onclick="cerrarModal('modal-participacion')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Evaluación -->
    <div id="modal-evaluacion" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full my-8 mx-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4" id="eval_titulo">Nueva Evaluación</h3>
            <form method="POST" id="form-evaluacion">
                <input type="hidden" name="accion" id="eval_accion" value="crear_evaluacion">
                <input type="hidden" name="id_evaluatio" id="eval_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <label class="block">
                        <span class="text-sm font-semibold">Curso <span class="text-red-500">*</span></span>
                        <select name="id_curso" id="eval_id_curso" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="">Seleccionar curso...</option>
                            <?php foreach ($cursos as $c): ?>
                            <option value="<?php echo $c['id_curso']; ?>"><?php echo htmlspecialchars($c['codigo_curso'] . ' - ' . $c['nombre_curso']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Nombre</span>
                        <input type="text" name="nombre" id="eval_nombre" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Auto-generado desde curso">
                    </label>
                </div>

                <label class="block mb-4">
                    <span class="text-sm font-semibold">Descripción</span>
                    <textarea name="descripcion" id="eval_descripcion" rows="2" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2"></textarea>
                </label>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="muestra_respuestas_correctas" id="eval_muestra_respuestas" class="rounded">
                        <span class="text-sm">Mostrar respuestas</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="requiere_cierre_cualitativo" id="eval_requiere_cierre" class="rounded" onchange="toggleCierreOptions()">
                        <span class="text-sm">Cierre cualitativo</span>
                    </label>
                </div>

                <div id="cierre-options" style="display: none;" class="mb-4 bg-gray-50 p-3 rounded">
                    <label class="block mb-2">
                        <span class="text-sm font-semibold">Texto del cierre</span>
                        <textarea name="texto_cierre_cualitativo" id="eval_texto_cierre" rows="2" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Ej: Comparte tu reflexión final..."></textarea>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Mínimo caracteres</span>
                        <input type="number" name="minimo_caracteres_cierre" id="eval_min_caracteres_cierre" class="mt-1 block w-32 border border-gray-300 rounded px-3 py-2" value="100">
                    </label>
                </div>

                <label class="block mb-4">
                    <span class="text-sm font-semibold">Mensaje de bienvenida</span>
                    <textarea name="mensaje_bienvenida" id="eval_msg_bienvenida" rows="2" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Mensaje que verán los estudiantes al iniciar..."></textarea>
                </label>

                <label class="block mb-4">
                    <span class="text-sm font-semibold">Mensaje de finalización</span>
                    <textarea name="mensaje_finalizacion" id="eval_msg_finalizacion" rows="2" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Mensaje al completar la evaluación..."></textarea>
                </label>

                <div id="edit-estado-container" class="hidden mb-4">
                    <label class="block">
                        <span class="text-sm font-semibold">Estado</span>
                        <select name="estado" id="eval_estado" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                            <option value="borrador">Borrador</option>
                            <option value="activa">Activa</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                    </label>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="cerrarModal('modal-evaluacion')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Pregunta -->
    <div id="modal-pregunta" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-lg p-6 max-w-3xl w-full my-8 mx-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4" id="preg_titulo">Nueva Pregunta</h3>
            <form method="POST" id="form-pregunta">
                <input type="hidden" name="accion" id="preg_accion" value="crear_pregunta">
                <input type="hidden" name="id_evaluatio" id="preg_id_evaluatio" value="<?php echo $ver_preguntas; ?>">
                <input type="hidden" name="id_quaestio" id="preg_id">

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <label class="block">
                        <span class="text-sm font-semibold">Tipo <span class="text-red-500">*</span></span>
                        <select name="tipo" id="preg_tipo" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" onchange="toggleOpcionesContainer()" required>
                            <option value="multiple_choice">Opción única (radio)</option>
                            <option value="multiple_answer">Opción múltiple (checkbox)</option>
                            <option value="verdadero_falso">Verdadero/Falso</option>
                            <option value="abierta">Respuesta abierta</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Puntos</span>
                        <input type="number" name="puntos" id="preg_puntos" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" value="1" min="1">
                    </label>
                </div>

                <label class="block mb-4">
                    <span class="text-sm font-semibold">Enunciado <span class="text-red-500">*</span></span>
                    <textarea name="enunciado" id="preg_enunciado" rows="3" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required minlength="10"></textarea>
                </label>

                <label class="flex items-center gap-2 mb-4">
                    <input type="checkbox" name="es_obligatoria" id="preg_obligatoria" class="rounded" checked>
                    <span class="text-sm">Pregunta obligatoria</span>
                </label>

                <div id="opciones-section" class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold">Opciones de respuesta</span>
                        <button type="button" onclick="agregarOpcion()" class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                            <i data-lucide="plus" class="w-4 h-4"></i> Agregar opción
                        </button>
                    </div>
                    <div id="opciones-container" class="space-y-2">
                        <!-- Las opciones se agregan dinámicamente -->
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Marque las opciones correctas. Para opción única, solo una debe ser correcta.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <label class="block">
                        <span class="text-sm font-semibold">Feedback correcto</span>
                        <textarea name="explicacion_correcta" id="preg_exp_correcta" rows="3" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Mensaje cuando responde correctamente..."></textarea>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Feedback incorrecto</span>
                        <textarea name="explicacion_incorrecta" id="preg_exp_incorrecta" rows="3" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Mensaje cuando responde incorrectamente..."></textarea>
                    </label>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="cerrarModal('modal-pregunta')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-6 text-center text-sm text-gray-400">
            <p>&copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($nombre_institucion); ?> - Todos los derechos reservados.</p>
            <p class="mt-2">Plataforma de certificación proveída por <a href="https://verumax.com" target="_blank" class="font-semibold text-indigo-400 hover:underline">Certificatum - una solución de VERUMax</a>.</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        function cambiarTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            event.target.closest('.tab-button').classList.add('active');
        }

        function mostrarFormularioTexto() {
            document.getElementById('formulario-texto').style.display = 'block';
        }

        function ocultarFormularioTexto() {
            document.getElementById('formulario-texto').style.display = 'none';
        }

        // Funciones para cargas específicas
        function mostrarFormEstudiantes() {
            document.getElementById('form-estudiantes').style.display = 'block';
            document.getElementById('form-cursos').style.display = 'none';
            document.getElementById('form-inscripciones').style.display = 'none';
        }

        function ocultarFormEstudiantes() {
            document.getElementById('form-estudiantes').style.display = 'none';
        }

        function mostrarFormCursos() {
            document.getElementById('form-cursos').style.display = 'block';
            document.getElementById('form-estudiantes').style.display = 'none';
            document.getElementById('form-inscripciones').style.display = 'none';
        }

        function ocultarFormCursos() {
            document.getElementById('form-cursos').style.display = 'none';
        }

        function mostrarFormInscripciones() {
            document.getElementById('form-inscripciones').style.display = 'block';
            document.getElementById('form-estudiantes').style.display = 'none';
            document.getElementById('form-cursos').style.display = 'none';
        }

        function ocultarFormInscripciones() {
            document.getElementById('form-inscripciones').style.display = 'none';
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function editarEstudiante(id, dni, nombre) {
            document.getElementById('edit_est_id').value = id;
            document.getElementById('edit_est_dni').value = dni;
            document.getElementById('edit_est_nombre').value = nombre;
            document.getElementById('modal-estudiante').classList.remove('hidden');
        }

        function confirmarEliminarEstudiante(id) {
            if (confirm('¿Seguro que deseas eliminar este estudiante? Esta acción no se puede deshacer.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="eliminar_estudiante"><input type="hidden" name="id_estudiante" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Cache de templates
        let templatesCache = null;

        async function cargarTemplates() {
            if (templatesCache) return templatesCache;
            try {
                const response = await fetch('administrare_api.php?action=getTemplates');
                const data = await response.json();
                if (data.success) {
                    templatesCache = data.templates;
                    return templatesCache;
                }
            } catch (e) {
                console.error('Error cargando templates:', e);
            }
            return [];
        }

        function renderTemplateSelector(templates, selectedId) {
            const container = document.getElementById('template-selector');
            // Mantener la opción predeterminada
            let html = `
                <label class="template-option cursor-pointer">
                    <input type="radio" name="id_template" value="" class="hidden" ${!selectedId ? 'checked' : ''}>
                    <div class="border-2 ${!selectedId ? 'border-blue-500 bg-blue-50' : 'border-gray-200'} rounded p-2 hover:border-blue-400 transition-colors template-card" data-template="">
                        <div class="h-16 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">
                            Sistema actual
                        </div>
                        <p class="text-xs mt-1 text-center font-medium truncate">Predeterminado</p>
                    </div>
                </label>
            `;

            // Agregar templates dinámicos
            templates.filter(t => !t.is_default).forEach(t => {
                const isSelected = selectedId == t.id_template;
                html += `
                    <label class="template-option cursor-pointer relative">
                        <input type="radio" name="id_template" value="${t.id_template}" class="hidden" ${isSelected ? 'checked' : ''}>
                        <div class="border-2 ${isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-200'} rounded p-2 hover:border-blue-400 transition-colors template-card" data-template="${t.id_template}">
                            <div class="h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded flex items-center justify-center text-gray-500 text-xs">
                                ${t.preview_url ? `<img src="${t.preview_url}" alt="${t.nombre}" class="h-full w-full object-cover rounded" onerror="this.parentElement.innerHTML='Vista previa'">` : t.nombre}
                            </div>
                            <p class="text-xs mt-1 text-center font-medium truncate" title="${t.nombre}">${t.nombre}</p>
                        </div>
                        <button type="button"
                                onclick="event.preventDefault(); event.stopPropagation(); abrirEditorJson(${t.id_template}, '${t.slug}', '${t.nombre.replace(/'/g, "\\'")}')"
                                class="absolute top-1 right-1 bg-gray-700 bg-opacity-70 text-white p-1 rounded hover:bg-gray-900 transition-colors"
                                title="Editar configuración JSON">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </label>
                `;
            });

            container.innerHTML = html;

            // Event listeners para selección visual
            container.querySelectorAll('.template-option').forEach(option => {
                option.addEventListener('click', function() {
                    container.querySelectorAll('.template-card').forEach(card => {
                        card.classList.remove('border-blue-500', 'bg-blue-50');
                        card.classList.add('border-gray-200');
                    });
                    const card = this.querySelector('.template-card');
                    card.classList.remove('border-gray-200');
                    card.classList.add('border-blue-500', 'bg-blue-50');
                });
            });
        }

        async function editarCurso(id, codigo, nombre, horas, activo) {
            document.getElementById('edit_curso_id').value = id;
            document.getElementById('edit_curso_codigo').value = codigo;
            document.getElementById('edit_curso_nombre').value = nombre;
            document.getElementById('edit_curso_horas').value = horas;
            document.getElementById('edit_curso_activo').value = activo;

            // Cargar templates y template actual del curso
            const templates = await cargarTemplates();
            let currentTemplateId = null;

            try {
                const response = await fetch(`administrare_api.php?action=getCursoTemplate&id_curso=${id}`);
                const data = await response.json();
                if (data.success && data.template) {
                    currentTemplateId = data.template.id_template;
                }
            } catch (e) {
                console.error('Error obteniendo template del curso:', e);
            }

            renderTemplateSelector(templates, currentTemplateId);
            document.getElementById('modal-curso').classList.remove('hidden');
        }

        function confirmarEliminarCurso(id) {
            if (confirm('¿Seguro que deseas eliminar/desactivar este curso?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="eliminar_curso"><input type="hidden" name="id_curso" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function editarInscripcion(id, estado, inicio, fin, nota, asistencia) {
            document.getElementById('edit_insc_id').value = id;
            document.getElementById('edit_insc_estado').value = estado;
            document.getElementById('edit_insc_inicio').value = inicio || '';
            document.getElementById('edit_insc_fin').value = fin || '';
            document.getElementById('edit_insc_nota').value = nota || '';
            document.getElementById('edit_insc_asistencia').value = asistencia || '';
            document.getElementById('modal-inscripcion').classList.remove('hidden');
        }

        function confirmarEliminarInscripcion(id) {
            if (confirm('¿Seguro que deseas eliminar esta inscripción? Se eliminarán también las competencias y trayectoria asociadas.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="eliminar_inscripcion"><input type="hidden" name="id_inscripcion" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Funciones para Docentes
        function mostrarFormDocentes() {
            document.getElementById('form-docentes').style.display = 'block';
        }

        function ocultarFormDocentes() {
            document.getElementById('form-docentes').style.display = 'none';
        }

        function mostrarFormParticipacion() {
            document.getElementById('form-participacion').style.display = 'block';
        }

        function ocultarFormParticipacion() {
            document.getElementById('form-participacion').style.display = 'none';
        }

        function editarDocente(id, dni, nombre, email, especialidad, titulo) {
            document.getElementById('edit_doc_id').value = id;
            document.getElementById('edit_doc_dni').value = dni;
            document.getElementById('edit_doc_nombre').value = nombre;
            document.getElementById('edit_doc_email').value = email || '';
            document.getElementById('edit_doc_especialidad').value = especialidad || '';
            document.getElementById('edit_doc_titulo').value = titulo || '';
            document.getElementById('modal-docente').classList.remove('hidden');
        }

        function confirmarEliminarDocente(id) {
            if (confirm('¿Seguro que deseas eliminar este docente?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="eliminar_docente"><input type="hidden" name="id_docente" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function editarParticipacion(id, rol, titulo, inicio, fin, carga) {
            document.getElementById('edit_part_id').value = id;
            document.getElementById('edit_part_rol').value = rol;
            document.getElementById('edit_part_titulo').value = titulo || '';
            document.getElementById('edit_part_inicio').value = inicio || '';
            document.getElementById('edit_part_fin').value = fin || '';
            document.getElementById('edit_part_carga').value = carga || '';
            document.getElementById('modal-participacion').classList.remove('hidden');
        }

        function confirmarEliminarParticipacion(id) {
            if (confirm('¿Seguro que deseas eliminar esta participación?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="eliminar_participacion"><input type="hidden" name="id_participacion" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // =============================================
        // Funciones para Evaluaciones (Probatio)
        // =============================================

        function mostrarModalEvaluacion(idCurso = '') {
            // Limpiar formulario
            document.getElementById('form-evaluacion').reset();
            document.getElementById('eval_id').value = '';
            document.getElementById('eval_accion').value = 'crear_evaluacion';
            document.getElementById('eval_titulo').textContent = 'Nueva Evaluación';

            // Ocultar selector de estado (solo para edición)
            document.getElementById('edit-estado-container').classList.add('hidden');

            // Ocultar opciones de cierre
            document.getElementById('cierre-options').style.display = 'none';

            // Pre-seleccionar curso si se indica
            if (idCurso) {
                document.getElementById('eval_id_curso').value = idCurso;
            }

            // Mostrar modal
            document.getElementById('modal-evaluacion').classList.remove('hidden');
        }

        function editarEvaluacion(eval) {
            // Acepta objeto completo o parámetros individuales
            const data = typeof eval === 'object' ? eval : arguments;

            document.getElementById('eval_id').value = data.id_evaluatio || data[0];
            document.getElementById('eval_accion').value = 'actualizar_evaluacion';
            document.getElementById('eval_id_curso').value = data.id_curso || data[1];
            document.getElementById('eval_nombre').value = data.nombre || data[2] || '';
            document.getElementById('eval_descripcion').value = data.descripcion || data[3] || '';
            document.getElementById('eval_muestra_respuestas').checked = (data.muestra_respuestas_correctas == 1) || (data[4] == 1);
            document.getElementById('eval_requiere_cierre').checked = (data.requiere_cierre_cualitativo == 1) || (data[5] == 1);
            document.getElementById('eval_texto_cierre').value = data.texto_cierre_cualitativo || data[6] || '';
            document.getElementById('eval_min_caracteres_cierre').value = data.minimo_caracteres_cierre || data[7] || 100;
            document.getElementById('eval_msg_bienvenida').value = data.mensaje_bienvenida || data[8] || '';
            document.getElementById('eval_msg_finalizacion').value = data.mensaje_finalizacion || data[9] || '';

            // Mostrar selector de estado y setear valor
            document.getElementById('edit-estado-container').classList.remove('hidden');
            document.getElementById('eval_estado').value = data.estado || data[10] || 'borrador';

            // Mostrar/ocultar opciones de cierre
            toggleCierreOptions();

            document.getElementById('eval_titulo').textContent = 'Editar Evaluación';
            document.getElementById('modal-evaluacion').classList.remove('hidden');
        }

        function confirmarEliminarEvaluacion(id) {
            if (confirm('¿Seguro que deseas eliminar esta evaluación? Se eliminarán también todas las preguntas asociadas.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="eliminar_evaluacion"><input type="hidden" name="id_evaluatio" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function duplicarEvaluacion(id) {
            if (confirm('¿Deseas duplicar esta evaluación con todas sus preguntas?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="duplicar_evaluacion"><input type="hidden" name="id_evaluatio" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function copiarEnlace(codigo) {
            const baseUrl = window.location.origin;
            const enlace = baseUrl + '/probatio/' + codigo;

            navigator.clipboard.writeText(enlace).then(function() {
                // Mostrar feedback
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
                lucide.createIcons();
                btn.classList.remove('text-gray-500');
                btn.classList.add('text-green-600');

                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    lucide.createIcons();
                    btn.classList.remove('text-green-600');
                    btn.classList.add('text-gray-500');
                }, 2000);
            }).catch(function() {
                // Fallback para navegadores antiguos
                const input = document.createElement('input');
                input.value = enlace;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                alert('Enlace copiado: ' + enlace);
            });
        }

        function toggleCierreOptions() {
            const requiereCierre = document.getElementById('eval_requiere_cierre').checked;
            const container = document.getElementById('cierre-options');
            if (container) {
                container.style.display = requiereCierre ? 'block' : 'none';
            }
        }

        // =============================================
        // Funciones para Preguntas
        // =============================================

        let opcionIndex = 0;

        function mostrarModalPregunta(idEvaluatio) {
            // Limpiar formulario
            document.getElementById('form-pregunta').reset();
            document.getElementById('preg_id').value = '';
            document.getElementById('preg_accion').value = 'crear_pregunta';
            document.getElementById('preg_id_evaluatio').value = idEvaluatio;
            document.getElementById('preg_titulo').textContent = 'Nueva Pregunta';
            document.getElementById('preg_obligatoria').checked = true;

            // Resetear opciones
            opcionIndex = 0;
            const container = document.getElementById('opciones-container');
            container.innerHTML = '';

            // Agregar 4 opciones por defecto
            agregarOpcion();
            agregarOpcion();
            agregarOpcion();
            agregarOpcion();

            // Mostrar/ocultar según tipo
            toggleOpcionesContainer();

            document.getElementById('modal-pregunta').classList.remove('hidden');
        }

        function editarPregunta(preg) {
            // Acepta objeto completo
            const data = typeof preg === 'object' ? preg : {};

            document.getElementById('preg_id').value = data.id_quaestio || '';
            document.getElementById('preg_accion').value = 'actualizar_pregunta';
            document.getElementById('preg_id_evaluatio').value = data.id_evaluatio || '';
            document.getElementById('preg_tipo').value = data.tipo || 'multiple_choice';
            document.getElementById('preg_enunciado').value = data.enunciado || '';
            document.getElementById('preg_puntos').value = data.puntos || 1;
            document.getElementById('preg_obligatoria').checked = data.es_obligatoria == 1;
            document.getElementById('preg_exp_correcta').value = data.explicacion_correcta || '';
            document.getElementById('preg_exp_incorrecta').value = data.explicacion_incorrecta || '';

            // Cargar opciones
            opcionIndex = 0;
            const container = document.getElementById('opciones-container');
            container.innerHTML = '';

            const tipo = data.tipo || 'multiple_choice';
            const opciones = data.opciones;

            if (opciones && tipo !== 'abierta') {
                try {
                    const opts = Array.isArray(opciones) ? opciones : JSON.parse(opciones);
                    opts.forEach(function(opt) {
                        agregarOpcion(opt.letra, opt.texto, opt.es_correcta);
                    });
                } catch (e) {
                    // Si falla parsing, agregar opciones vacías
                    agregarOpcion();
                    agregarOpcion();
                }
            } else if (tipo !== 'abierta') {
                // Agregar opciones vacías si no hay
                agregarOpcion();
                agregarOpcion();
            }

            toggleOpcionesContainer();

            document.getElementById('preg_titulo').textContent = 'Editar Pregunta';
            document.getElementById('modal-pregunta').classList.remove('hidden');
        }

        function confirmarEliminarPregunta(id, idEvaluatio) {
            if (confirm('¿Seguro que deseas eliminar esta pregunta?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="eliminar_pregunta">' +
                    '<input type="hidden" name="id_quaestio" value="' + id + '">' +
                    '<input type="hidden" name="id_evaluatio" value="' + idEvaluatio + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function toggleOpcionesContainer() {
            const tipo = document.getElementById('preg_tipo').value;
            const container = document.getElementById('opciones-section');
            if (container) {
                container.style.display = tipo === 'abierta' ? 'none' : 'block';
            }
        }

        function agregarOpcion(letra = '', texto = '', esCorrecta = false) {
            const container = document.getElementById('opciones-container');
            const letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            const letraActual = letra || letras[opcionIndex] || String.fromCharCode(65 + opcionIndex);

            // Escapar texto para HTML
            const textoEscapado = texto.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            const div = document.createElement('div');
            div.className = 'flex items-center gap-2 p-2 bg-gray-50 rounded';
            div.innerHTML = `
                <span class="font-bold text-gray-600 w-6">${letraActual}</span>
                <input type="hidden" name="opcion_letra[]" value="${letraActual}">
                <input type="text" name="opcion_texto[]" value="${textoEscapado}" class="flex-1 border border-gray-300 rounded px-2 py-1" placeholder="Texto de la opción ${letraActual}..." required>
                <label class="flex items-center gap-1 text-sm">
                    <input type="checkbox" name="opcion_correcta[]" value="${letraActual}" ${esCorrecta ? 'checked' : ''}>
                    <span>Correcta</span>
                </label>
                <button type="button" onclick="eliminarOpcion(this)" class="text-red-500 hover:text-red-700 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            `;

            container.appendChild(div);
            opcionIndex++;
            lucide.createIcons();
        }

        function eliminarOpcion(btn) {
            const container = document.getElementById('opciones-container');
            if (container.children.length > 2) {
                btn.closest('div').remove();
                reindexarOpciones();
            } else {
                alert('Debe haber al menos 2 opciones');
            }
        }

        function reindexarOpciones() {
            const container = document.getElementById('opciones-container');
            const letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            const opciones = container.children;

            for (let i = 0; i < opciones.length; i++) {
                const span = opciones[i].querySelector('span');
                const hiddenInput = opciones[i].querySelector('input[name="opcion_letra[]"]');
                const checkbox = opciones[i].querySelector('input[name="opcion_correcta[]"]');
                const textInput = opciones[i].querySelector('input[name="opcion_texto[]"]');

                if (span) span.textContent = letras[i];
                if (hiddenInput) hiddenInput.value = letras[i];
                if (checkbox) checkbox.value = letras[i];  // Usar letra, no índice
                if (textInput) textInput.placeholder = 'Texto de la opción ' + letras[i] + '...';
            }
            opcionIndex = opciones.length;
        }

        // =============================================
        // Funciones para Editor de JSON de Templates
        // =============================================

        let currentTemplateId = null;

        async function abrirEditorJson(templateId, slug, nombre) {
            currentTemplateId = templateId;
            document.getElementById('json-editor-title').textContent = `Configuración: ${nombre}`;
            document.getElementById('json-editor-slug').textContent = `Slug: ${slug}`;
            document.getElementById('json-editor-loading').classList.remove('hidden');
            document.getElementById('json-editor-content').classList.add('hidden');
            document.getElementById('json-editor-error').classList.add('hidden');
            document.getElementById('modal-json-editor').classList.remove('hidden');

            try {
                const response = await fetch(`administrare_api.php?action=getTemplateConfig&id=${templateId}`);
                const data = await response.json();

                document.getElementById('json-editor-loading').classList.add('hidden');
                document.getElementById('json-editor-content').classList.remove('hidden');

                if (data.success) {
                    const jsonContent = data.config || '';
                    if (jsonContent) {
                        // Formatear JSON para mejor lectura
                        try {
                            const parsed = JSON.parse(jsonContent);
                            document.getElementById('json-editor-textarea').value = JSON.stringify(parsed, null, 2);
                        } catch {
                            document.getElementById('json-editor-textarea').value = jsonContent;
                        }
                        document.getElementById('json-editor-status').innerHTML = '<span class="text-green-600">✓ Configuración cargada</span>';
                    } else {
                        document.getElementById('json-editor-textarea').value = '';
                        document.getElementById('json-editor-status').innerHTML = '<span class="text-gray-500">Sin configuración (usa sistema predeterminado)</span>';
                    }
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            } catch (e) {
                document.getElementById('json-editor-loading').classList.add('hidden');
                document.getElementById('json-editor-error').classList.remove('hidden');
                document.getElementById('json-editor-error').textContent = 'Error: ' + e.message;
            }
        }

        function cerrarEditorJson() {
            document.getElementById('modal-json-editor').classList.add('hidden');
            currentTemplateId = null;
        }

        async function guardarConfigJson() {
            if (!currentTemplateId) return;

            const jsonContent = document.getElementById('json-editor-textarea').value.trim();

            // Validar JSON si no está vacío
            if (jsonContent) {
                try {
                    JSON.parse(jsonContent);
                } catch (e) {
                    alert('Error: El JSON no es válido.\n\n' + e.message);
                    return;
                }
            }

            document.getElementById('json-editor-save-btn').disabled = true;
            document.getElementById('json-editor-save-btn').textContent = 'Guardando...';

            try {
                const formData = new FormData();
                formData.append('action', 'updateTemplateConfig');
                formData.append('id_template', currentTemplateId);
                formData.append('config', jsonContent);

                const response = await fetch('administrare_api.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('json-editor-status').innerHTML = '<span class="text-green-600">✓ Guardado correctamente</span>';
                    setTimeout(() => {
                        if (jsonContent) {
                            document.getElementById('json-editor-status').innerHTML = '<span class="text-green-600">✓ Configuración cargada</span>';
                        } else {
                            document.getElementById('json-editor-status').innerHTML = '<span class="text-gray-500">Sin configuración (usa sistema predeterminado)</span>';
                        }
                    }, 2000);
                } else {
                    alert('Error al guardar: ' + (data.error || 'Error desconocido'));
                }
            } catch (e) {
                alert('Error de conexión: ' + e.message);
            }

            document.getElementById('json-editor-save-btn').disabled = false;
            document.getElementById('json-editor-save-btn').textContent = 'Guardar';
        }

        async function eliminarConfigJson() {
            if (!currentTemplateId) return;

            if (!confirm('¿Seguro que deseas eliminar la configuración JSON?\n\nEl template usará el sistema predeterminado.')) {
                return;
            }

            document.getElementById('json-editor-delete-btn').disabled = true;
            document.getElementById('json-editor-delete-btn').textContent = 'Eliminando...';

            try {
                const formData = new FormData();
                formData.append('action', 'deleteTemplateConfig');
                formData.append('id_template', currentTemplateId);

                const response = await fetch('administrare_api.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('json-editor-textarea').value = '';
                    document.getElementById('json-editor-status').innerHTML = '<span class="text-gray-500">Sin configuración (usa sistema predeterminado)</span>';
                } else {
                    alert('Error al eliminar: ' + (data.error || 'Error desconocido'));
                }
            } catch (e) {
                alert('Error de conexión: ' + e.message);
            }

            document.getElementById('json-editor-delete-btn').disabled = false;
            document.getElementById('json-editor-delete-btn').textContent = 'Eliminar Config';
        }

        function formatearJson() {
            const textarea = document.getElementById('json-editor-textarea');
            const content = textarea.value.trim();

            if (!content) return;

            try {
                const parsed = JSON.parse(content);
                textarea.value = JSON.stringify(parsed, null, 2);
                document.getElementById('json-editor-status').innerHTML = '<span class="text-green-600">✓ JSON formateado</span>';
            } catch (e) {
                alert('Error: El JSON no es válido.\n\n' + e.message);
            }
        }
    </script>

    <!-- Modal para Editor de JSON de Templates -->
    <div id="modal-json-editor" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]">
        <div class="bg-white rounded-lg p-6 max-w-3xl w-full mx-4 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 id="json-editor-title" class="text-xl font-bold">Configuración del Template</h3>
                    <p id="json-editor-slug" class="text-sm text-gray-500">Slug: </p>
                </div>
                <button type="button" onclick="cerrarEditorJson()" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Loading state -->
            <div id="json-editor-loading" class="py-12 text-center text-gray-500">
                <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Cargando configuración...
            </div>

            <!-- Error state -->
            <div id="json-editor-error" class="hidden py-4 text-center text-red-600 bg-red-50 rounded"></div>

            <!-- Content -->
            <div id="json-editor-content" class="hidden flex-1 flex flex-col min-h-0">
                <div class="flex justify-between items-center mb-2">
                    <p id="json-editor-status" class="text-sm"></p>
                    <button type="button" onclick="formatearJson()" class="text-sm text-blue-600 hover:text-blue-800">
                        Formatear JSON
                    </button>
                </div>

                <textarea id="json-editor-textarea"
                          class="flex-1 min-h-[300px] w-full border border-gray-300 rounded px-3 py-2 font-mono text-sm resize-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                          placeholder='Pega aquí el JSON del template generado por el Editor de Templates...

Ejemplo:
{
  "canvasWidth": 842,
  "canvasHeight": 595,
  "elements": [
    {
      "id": 1,
      "type": "title",
      "x": 100,
      "y": 50,
      "width": 200,
      "height": 40,
      "text": "CERTIFICADO"
    }
  ]
}'></textarea>

                <div class="flex gap-2 mt-4 pt-4 border-t">
                    <button type="button" id="json-editor-save-btn" onclick="guardarConfigJson()"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex-1">
                        Guardar
                    </button>
                    <button type="button" id="json-editor-delete-btn" onclick="eliminarConfigJson()"
                            class="bg-red-100 text-red-700 px-4 py-2 rounded hover:bg-red-200">
                        Eliminar Config
                    </button>
                    <button type="button" onclick="cerrarEditorJson()"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                        Cerrar
                    </button>
                </div>

                <p class="text-xs text-gray-500 mt-3">
                    💡 Genera configuraciones JSON usando el
                    <a href="/tools/template-editor/" target="_blank" class="text-blue-600 hover:underline">Editor de Templates</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
