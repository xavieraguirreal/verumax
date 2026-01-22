<?php
error_reporting(E_ALL);
  ini_set('display_errors', 1);
/**
 * MÓDULO: CERTIFICATUM - INTEGRACIÓN COMPLETA
 * Gestión de estudiantes, cursos, inscripciones y configuración
 *
 * NOTA: Este módulo se carga dentro de admin/index.php
 * Ya está autenticado y $admin está disponible
 */

// Ya estamos autenticados por index.php
// $admin ya está disponible
$slug = $admin['slug'];

// Cargar configuración global (contiene VERUMAX_IA_API_KEY)
require_once __DIR__ . '/../../config.php';

// Cargar configuración de Identitas (para poder actualizar configuración)
require_once __DIR__ . '/../../identitas/config.php';

// Cargar configuración de General (para getGeneralDBConnection)
require_once __DIR__ . '/../../general/config.php';

// Cargar configuración y funciones de Certificatum
require_once __DIR__ . '/../../certificatum/config.php';
require_once __DIR__ . '/../../certificatum/administrare_procesador.php';
require_once __DIR__ . '/../../certificatum/administrare_gestionar.php';

// Cargar servicio de email para notificaciones
require_once __DIR__ . '/../../src/VERUMax/Services/EmailService.php';

// Cargar lista de países normalizada
$paises = include __DIR__ . '/../../config/paises.php';

// Obtener conexión PDO e instancia
$pdo = getDBConnection();
$instance = getInstanceConfig($slug);
if (!$instance) {
    die('Error: Instancia no encontrada');
}

// Cargar traducciones existentes de instance_translations
$idiomas_habilitados = explode(',', $instance['idiomas_habilitados'] ?? 'es_AR');
$idioma_default = $instance['idioma_default'] ?? 'es_AR';
$traducciones_certificatum = [];

try {
    $pdo_general = getGeneralDBConnection();
    $stmt_trad = $pdo_general->prepare("
        SELECT campo, idioma, contenido
        FROM instance_translations
        WHERE id_instancia = :id_instancia
        AND campo IN ('certificatum_descripcion', 'certificatum_cta_texto')
    ");
    $stmt_trad->execute(['id_instancia' => $instance['id_instancia']]);
    while ($row = $stmt_trad->fetch(PDO::FETCH_ASSOC)) {
        $traducciones_certificatum[$row['campo']][$row['idioma']] = $row['contenido'];
    }
} catch (Exception $e) {
    error_log("Error cargando traducciones: " . $e->getMessage());
}

// Mapeo de códigos de idioma a nombres
$nombres_idiomas = [
    'es_AR' => 'Español (Argentina)',
    'pt_BR' => 'Português (Brasil)',
    'en_US' => 'English (US)',
    'el_GR' => 'Ελληνικά'
];

// La institución viene de la sesión
$institucion = $slug;

// Procesar acciones
$mensaje = null;
$tipo_mensaje = 'success';
$scroll_to = null;
$active_tab = null;
$resultado = null;
$errores = [];

// Leer mensaje de redirect (POST-GET pattern)
if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];
    $tipo_mensaje = $_GET['msg_type'] ?? 'success';
}
if (isset($_GET['tab'])) {
    $active_tab = $_GET['tab'];
}

// DEBUG: Ver si llega el POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST recibido en certificatum - accion: " . ($_POST['accion'] ?? 'NO DEFINIDA'));
    error_log("GET modulo: " . ($_GET['modulo'] ?? 'NO DEFINIDO'));
}

$redirect_to_preguntas = 0; // Variable para redirect después de acciones en preguntas

// Manejar peticiones GET AJAX (obtener datos)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && isset($_GET['modulo']) && $_GET['modulo'] === 'certificatum') {
    switch ($_GET['accion']) {
        case 'obtener_template_config':
            header('Content-Type: application/json');
            $id_template = isset($_GET['id_template']) ? (int)$_GET['id_template'] : 0;

            if ($id_template <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID de template inválido']);
                exit;
            }

            try {
                $template = \VERUMax\Services\CertificateTemplateService::getById($id_template);
                if ($template) {
                    echo json_encode([
                        'success' => true,
                        'config' => $template['config'] ?? '',
                        'nombre' => $template['nombre'] ?? '',
                        'has_config' => !empty($template['config'])
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Template no encontrado']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
            }
            exit;

        case 'obtener_firma_base64':
            header('Content-Type: application/json');
            $path = $_GET['path'] ?? '';

            if (empty($path)) {
                echo json_encode(['success' => false, 'error' => 'Path vacío']);
                exit;
            }

            // Construir ruta al archivo
            $filepath = __DIR__ . '/../../' . ltrim($path, '/');

            if (!file_exists($filepath)) {
                echo json_encode(['success' => false, 'error' => 'Archivo no encontrado', 'path' => $filepath]);
                exit;
            }

            // Obtener extensión y mime type
            $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
            $mimeTypes = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif'];
            $mime = $mimeTypes[$ext] ?? 'image/png';

            // Convertir a base64
            $data = base64_encode(file_get_contents($filepath));
            $base64 = 'data:' . $mime . ';base64,' . $data;

            echo json_encode(['success' => true, 'base64' => $base64]);
            exit;
    }
}

// Interceptar peticiones AJAX JSON (wizard_importar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['accion']) && $_GET['accion'] === 'wizard_importar' && isset($_GET['modulo']) && $_GET['modulo'] === 'certificatum') {
    header('Content-Type: application/json');

    // Recibir datos JSON del body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
        exit;
    }

    $tipo = $data['tipo'] ?? '';
    $registros = $data['datos'] ?? [];
    $opciones = $data['opciones'] ?? [];

    if (empty($registros)) {
        echo json_encode(['success' => false, 'error' => 'No hay registros para importar']);
        exit;
    }

    $resultados = [
        'success' => true,
        'insertados' => 0,
        'actualizados' => 0,
        'errores' => [],
        'detalles' => []
    ];

    $id_instancia = $instance['id_instancia'];
    $actualizar_existentes = $opciones['actualizar_existentes'] ?? false;

    try {
        switch ($tipo) {
            case 'estudiantes':
                foreach ($registros as $index => $registro) {
                    $dni = preg_replace('/[^0-9A-Za-z]/', '', $registro['dni'] ?? '');
                    if (empty($dni)) {
                        $resultados['errores'][] = "Fila " . ($index + 1) . ": DNI vacío";
                        continue;
                    }

                    $existe = \VERUMax\Services\MemberService::getByIdentificador($id_instancia, $dni);

                    if ($existe && $actualizar_existentes) {
                        $datosUpdate = array_filter([
                            'nombre' => $registro['nombre'] ?? null,
                            'apellido' => $registro['apellido'] ?? null,
                            'email' => $registro['email'] ?? null,
                            'telefono' => $registro['telefono'] ?? null,
                            'domicilio_ciudad' => $registro['ciudad'] ?? null,
                            'domicilio_provincia' => $registro['provincia'] ?? null,
                            'domicilio_pais' => $registro['pais'] ?? null,
                            'genero' => $registro['genero'] ?? null
                        ], fn($v) => $v !== null && $v !== '');

                        if (!empty($datosUpdate)) {
                            $res = \VERUMax\Services\MemberService::actualizar($existe['id_miembro'], $datosUpdate);
                            if ($res['success']) {
                                $resultados['actualizados']++;
                            } else {
                                $resultados['errores'][] = "Fila " . ($index + 1) . ": " . $res['mensaje'];
                            }
                        }
                    } elseif ($existe) {
                        $resultados['detalles'][] = "Omitido (ya existe): DNI {$dni}";
                    } else {
                        $datosNuevo = [
                            'id_instancia' => $id_instancia,
                            'identificador_principal' => $dni,
                            'tipo_identificador' => 'DNI',
                            'nombre' => $registro['nombre'] ?? '',
                            'apellido' => $registro['apellido'] ?? '',
                            'email' => $registro['email'] ?? null,
                            'telefono' => $registro['telefono'] ?? null,
                            'domicilio_ciudad' => $registro['ciudad'] ?? null,
                            'domicilio_provincia' => $registro['provincia'] ?? null,
                            'domicilio_pais' => $registro['pais'] ?? 'Argentina',
                            'genero' => $registro['genero'] ?? 'No especifica',
                            'tipo_miembro' => 'Estudiante',
                            'estado' => 'Activo'
                        ];

                        $res = \VERUMax\Services\MemberService::crear($datosNuevo);
                        if ($res['success']) {
                            $resultados['insertados']++;
                        } else {
                            $resultados['errores'][] = "Fila " . ($index + 1) . ": " . $res['mensaje'];
                        }
                    }
                }
                break;

            case 'docentes':
                foreach ($registros as $index => $registro) {
                    $dni = preg_replace('/[^0-9A-Za-z]/', '', $registro['dni'] ?? '');
                    if (empty($dni)) {
                        $resultados['errores'][] = "Fila " . ($index + 1) . ": DNI vacío";
                        continue;
                    }

                    $existe = \VERUMax\Services\MemberService::getByIdentificador($id_instancia, $dni);

                    if ($existe && $actualizar_existentes) {
                        $datosUpdate = array_filter([
                            'nombre' => $registro['nombre'] ?? null,
                            'apellido' => $registro['apellido'] ?? null,
                            'email' => $registro['email'] ?? null,
                            'telefono' => $registro['telefono'] ?? null,
                            'genero' => $registro['genero'] ?? null,
                            'tipo_miembro' => 'Docente'
                        ], fn($v) => $v !== null && $v !== '');

                        $res = \VERUMax\Services\MemberService::actualizar($existe['id_miembro'], $datosUpdate);
                        if ($res['success']) {
                            $resultados['actualizados']++;
                        } else {
                            $resultados['errores'][] = "Fila " . ($index + 1) . ": " . $res['mensaje'];
                        }
                    } elseif ($existe) {
                        $resultados['detalles'][] = "Omitido (ya existe): DNI {$dni}";
                    } else {
                        $datosNuevo = [
                            'id_instancia' => $id_instancia,
                            'identificador_principal' => $dni,
                            'tipo_identificador' => 'DNI',
                            'nombre' => $registro['nombre'] ?? '',
                            'apellido' => $registro['apellido'] ?? '',
                            'email' => $registro['email'] ?? null,
                            'telefono' => $registro['telefono'] ?? null,
                            'genero' => $registro['genero'] ?? 'No especifica',
                            'tipo_miembro' => 'Docente',
                            'estado' => 'Activo'
                        ];

                        $res = \VERUMax\Services\MemberService::crear($datosNuevo);
                        if ($res['success']) {
                            $resultados['insertados']++;
                        } else {
                            $resultados['errores'][] = "Fila " . ($index + 1) . ": " . $res['mensaje'];
                        }
                    }
                }
                break;

            case 'inscripciones':
                $id_curso_fijo = $opciones['id_curso'] ?? null;

                foreach ($registros as $index => $registro) {
                    $dni = preg_replace('/[^0-9A-Za-z]/', '', $registro['dni'] ?? '');
                    if (empty($dni)) {
                        $resultados['errores'][] = "Fila " . ($index + 1) . ": DNI vacío";
                        continue;
                    }

                    $miembro = \VERUMax\Services\MemberService::getByIdentificador($id_instancia, $dni);
                    if (!$miembro) {
                        $resultados['errores'][] = "Fila " . ($index + 1) . ": No existe estudiante con DNI {$dni}";
                        continue;
                    }

                    $id_curso = $id_curso_fijo;
                    if (!$id_curso && !empty($registro['codigo_curso'])) {
                        $curso = \VERUMax\Services\CursoService::getByCodigo($id_instancia, $registro['codigo_curso']);
                        if ($curso) {
                            $id_curso = $curso['id_curso'];
                        }
                    }

                    if (!$id_curso) {
                        $resultados['errores'][] = "Fila " . ($index + 1) . ": No se pudo determinar el curso";
                        continue;
                    }

                    $inscripcionExiste = \VERUMax\Services\InscripcionService::getByMiembroCurso(
                        $miembro['id_miembro'],
                        $id_curso
                    );

                    if ($inscripcionExiste && $actualizar_existentes) {
                        $datosUpdate = array_filter([
                            'estado' => $registro['estado'] ?? null,
                            'nota_final' => $registro['nota_final'] ?? null,
                            'asistencia_porcentaje' => $registro['asistencia'] ?? null,
                            'fecha_finalizacion' => $registro['fecha_finalizacion'] ?? null
                        ], fn($v) => $v !== null && $v !== '');

                        if (!empty($datosUpdate)) {
                            $res = \VERUMax\Services\InscripcionService::actualizar(
                                $inscripcionExiste['id_inscripcion'],
                                $datosUpdate
                            );
                            if ($res['success']) {
                                $resultados['actualizados']++;
                            } else {
                                $resultados['errores'][] = "Fila " . ($index + 1) . ": " . $res['mensaje'];
                            }
                        }
                    } elseif ($inscripcionExiste) {
                        $resultados['detalles'][] = "Omitido (ya inscrito): DNI {$dni}";
                    } else {
                        $datosNuevo = [
                            'id_instancia' => $id_instancia,
                            'id_miembro' => $miembro['id_miembro'],
                            'id_curso' => $id_curso,
                            'estado' => $registro['estado'] ?? 'Inscrito',
                            'fecha_inscripcion' => date('Y-m-d'),
                            'nota_final' => $registro['nota_final'] ?? null,
                            'asistencia_porcentaje' => $registro['asistencia'] ?? null
                        ];

                        $res = \VERUMax\Services\InscripcionService::crear($datosNuevo);
                        if ($res['success']) {
                            $resultados['insertados']++;
                        } else {
                            $resultados['errores'][] = "Fila " . ($index + 1) . ": " . $res['mensaje'];
                        }
                    }
                }
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Tipo de importación no válido']);
                exit;
        }

        $resultados['success'] = ($resultados['insertados'] > 0 || $resultados['actualizados'] > 0) ||
                                  (count($resultados['errores']) === 0);

        echo json_encode($resultados);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error: ' . $e->getMessage(),
            'insertados' => $resultados['insertados'],
            'actualizados' => $resultados['actualizados'],
            'errores' => $resultados['errores']
        ]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && isset($_GET['modulo']) && $_GET['modulo'] === 'certificatum') {
    // DEBUG: Log de acción recibida
    error_log("CERTIFICATUM POST - Accion recibida: " . $_POST['accion']);

    // Si es una petición AJAX para obtener config, responder JSON inmediatamente
    // === ACCIÓN: Traducir con IA ===
    if ($_POST['accion'] === 'traducir_ia') {
        header('Content-Type: application/json');

        $texto = $_POST['texto'] ?? '';
        $idiomaOrigen = $_POST['idioma_origen'] ?? 'es_AR';
        $idiomaDestino = $_POST['idioma_destino'] ?? 'pt_BR';

        if (empty($texto)) {
            echo json_encode(['success' => false, 'error' => 'Texto vacío']);
            exit;
        }

        // Verificar si IA está habilitada
        if (!class_exists('VERUMax\\Services\\OpenAIService')) {
            require_once __DIR__ . '/../../src/VERUMax/Services/OpenAIService.php';
        }

        if (!\VERUMax\Services\OpenAIService::isEnabledForInstitution($slug)) {
            echo json_encode([
                'success' => false,
                'error' => 'La IA no está habilitada. Active la IA en Ajustes > General > Integraciones.',
                'code' => 'NOT_ENABLED'
            ]);
            exit;
        }

        // Mapeo de códigos a nombres de idioma
        $nombresIdiomas = [
            'es_AR' => 'español argentino',
            'pt_BR' => 'portugués brasileño',
            'en_US' => 'inglés estadounidense',
            'el_GR' => 'griego'
        ];

        $nombreOrigen = $nombresIdiomas[$idiomaOrigen] ?? $idiomaOrigen;
        $nombreDestino = $nombresIdiomas[$idiomaDestino] ?? $idiomaDestino;

        $prompt = "Traduce el siguiente texto de {$nombreOrigen} a {$nombreDestino}.
Mantén el mismo tono y estilo. Si es texto institucional/formal, mantén la formalidad.
Devuelve SOLO la traducción, sin explicaciones adicionales.

Texto a traducir:
{$texto}";

        try {
            $result = \VERUMax\Services\OpenAIService::chat($prompt, [
                'max_tokens' => 500,
                'temperature' => 0.3
            ]);

            if ($result['success'] && !empty($result['content'])) {
                echo json_encode([
                    'success' => true,
                    'traduccion' => trim($result['content'])
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => $result['error'] ?? 'No se pudo generar la traducción']);
            }
        } catch (Exception $e) {
            error_log("Error en traducción IA: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    // === ACCIÓN: Autocompletar descripción con IA ===
    if ($_POST['accion'] === 'autocompletar_ia') {
        header('Content-Type: application/json');

        $fieldName = $_POST['field_name'] ?? '';
        $idioma = $_POST['idioma'] ?? 'es_AR';

        if (!class_exists('VERUMax\\Services\\OpenAIService')) {
            require_once __DIR__ . '/../../src/VERUMax/Services/OpenAIService.php';
        }

        if (!\VERUMax\Services\OpenAIService::isEnabledForInstitution($slug)) {
            echo json_encode(['success' => false, 'error' => 'IA no habilitada', 'code' => 'NOT_ENABLED']);
            exit;
        }

        $nombresIdiomas = [
            'es_AR' => 'español argentino',
            'pt_BR' => 'portugués brasileño',
            'en_US' => 'inglés estadounidense',
            'el_GR' => 'griego'
        ];
        $nombreIdioma = $nombresIdiomas[$idioma] ?? 'español';

        $prompt = "Genera una descripción breve y profesional para el portal de certificados de una institución educativa.
El texto debe estar en {$nombreIdioma}.
Debe ser de 1-2 oraciones, invitando al usuario a consultar sus certificados y documentos académicos.
Devuelve SOLO el texto, sin comillas ni explicaciones.";

        try {
            $result = \VERUMax\Services\OpenAIService::chat($prompt, ['max_tokens' => 150, 'temperature' => 0.7]);
            if ($result['success'] && !empty($result['content'])) {
                echo json_encode(['success' => true, 'content' => trim($result['content'])]);
            } else {
                echo json_encode(['success' => false, 'error' => $result['error'] ?? 'No se pudo generar el contenido']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // === ACCIÓN: Generar estadísticas con IA ===
    if ($_POST['accion'] === 'generar_stats_ia') {
        header('Content-Type: application/json');

        if (!class_exists('VERUMax\\Services\\OpenAIService')) {
            require_once __DIR__ . '/../../src/VERUMax/Services/OpenAIService.php';
        }

        if (!\VERUMax\Services\OpenAIService::isEnabledForInstitution($slug)) {
            echo json_encode(['success' => false, 'error' => 'IA no habilitada', 'code' => 'NOT_ENABLED']);
            exit;
        }

        // Generar números realistas para estadísticas
        $fields = [
            'stats_certificados' => rand(150, 500) . '+',
            'stats_estudiantes' => rand(80, 300) . '+',
            'stats_cursos' => rand(5, 20) . '+'
        ];

        echo json_encode(['success' => true, 'fields' => $fields]);
        exit;
    }

    if ($_POST['accion'] === 'obtener_template_config') {
        header('Content-Type: application/json');
        $id_template = isset($_POST['id_template']) ? (int)$_POST['id_template'] : 0;

        if ($id_template <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de template inválido']);
            exit;
        }

        try {
            $template = \VERUMax\Services\CertificateTemplateService::getById($id_template);
            if ($template) {
                echo json_encode([
                    'success' => true,
                    'config' => $template['config'] ?? '',
                    'nombre' => $template['nombre'] ?? '',
                    'has_config' => !empty($template['config'])
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Template no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }

    switch ($_POST['accion']) {
        // CONFIGURACIÓN DE DISEÑO (Contenido específico de Certificatum)
        case 'actualizar_diseno':
            error_log("Entrando a actualizar_diseno");
            error_log("id_instancia: " . $instance['id_instancia']);
            try {
                // Procesar estadísticas
                $stats = json_encode([
                    'certificados_emitidos' => $_POST['stats_certificados'] ?? '',
                    'estudiantes' => $_POST['stats_estudiantes'] ?? '',
                    'cursos' => $_POST['stats_cursos'] ?? ''
                ]);

                // Determinar si usa paleta general o propia
                $usar_paleta_general = isset($_POST['certificatum_usar_paleta_general']) ? 1 : 0;

                // Obtener descripción y CTA del idioma default para guardar en certificatum_config
                $idioma_default_post = $instance['idioma_default'] ?? 'es_AR';
                $descripcion_default = $_POST['certificatum_descripcion'][$idioma_default_post] ?? '';
                $cta_default = $_POST['certificatum_cta_texto'][$idioma_default_post] ?? 'Ver mis certificados';

                // Actualizar en verumax_certifi.certificatum_config (valores del idioma default)
                $pdo_certifi = getCertDBConnection();

                $stmt = $pdo_certifi->prepare("
                    UPDATE certificatum_config
                    SET certificatum_descripcion = :descripcion,
                        certificatum_cta_texto = :cta_texto,
                        certificatum_estadisticas = :estadisticas,
                        certificatum_mostrar_stats = :mostrar_stats,
                        certificatum_usar_paleta_general = :certificatum_usar_paleta_general,
                        certificatum_paleta_colores_propia = :certificatum_paleta_colores_propia,
                        certificatum_color_primario_propio = :certificatum_color_primario_propio,
                        certificatum_color_secundario_propio = :certificatum_color_secundario_propio,
                        certificatum_color_acento_propio = :certificatum_color_acento_propio
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'descripcion' => $descripcion_default,
                    'cta_texto' => $cta_default,
                    'estadisticas' => $stats,
                    'mostrar_stats' => isset($_POST['mostrar_stats']) ? 1 : 0,
                    'certificatum_usar_paleta_general' => $usar_paleta_general,
                    'certificatum_paleta_colores_propia' => $usar_paleta_general ? null : ($_POST['certificatum_paleta_colores_propia'] ?? null),
                    'certificatum_color_primario_propio' => $usar_paleta_general ? null : ($_POST['certificatum_color_primario_propio'] ?? null),
                    'certificatum_color_secundario_propio' => $usar_paleta_general ? null : ($_POST['certificatum_color_secundario_propio'] ?? null),
                    'certificatum_color_acento_propio' => $usar_paleta_general ? null : ($_POST['certificatum_color_acento_propio'] ?? null),
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Guardar traducciones en instance_translations (verumax_general)
                $pdo_general = getGeneralDBConnection();
                $campos_traducibles = ['certificatum_descripcion', 'certificatum_cta_texto'];

                foreach ($campos_traducibles as $campo) {
                    if (isset($_POST[$campo]) && is_array($_POST[$campo])) {
                        foreach ($_POST[$campo] as $idioma => $contenido) {
                            $contenido = trim($contenido);
                            if (!empty($contenido)) {
                                // Si hay contenido, insertar o actualizar
                                $stmt_trans = $pdo_general->prepare("
                                    INSERT INTO instance_translations (id_instancia, campo, idioma, contenido)
                                    VALUES (:id_instancia, :campo, :idioma, :contenido)
                                    ON DUPLICATE KEY UPDATE contenido = :contenido2
                                ");
                                $stmt_trans->execute([
                                    'id_instancia' => $instance['id_instancia'],
                                    'campo' => $campo,
                                    'idioma' => $idioma,
                                    'contenido' => $contenido,
                                    'contenido2' => $contenido
                                ]);
                            } else {
                                // Si está vacío, eliminar para que se use el texto del archivo de idioma
                                $stmt_delete = $pdo_general->prepare("
                                    DELETE FROM instance_translations
                                    WHERE id_instancia = :id_instancia AND campo = :campo AND idioma = :idioma
                                ");
                                $stmt_delete->execute([
                                    'id_instancia' => $instance['id_instancia'],
                                    'campo' => $campo,
                                    'idioma' => $idioma
                                ]);
                            }
                        }
                    }
                }

                // Guardar demora_certificado_horas en certificatum_config (verumax_certifi)
                $demora_horas = isset($_POST['demora_certificado_horas']) ? (int)$_POST['demora_certificado_horas'] : 24;
                // Validar rango: 0-72 horas (límite de SendGrid)
                $demora_horas = max(0, min(72, $demora_horas));

                $stmt_demora = $pdo_certifi->prepare("
                    UPDATE certificatum_config
                    SET demora_certificado_horas = :demora
                    WHERE id_instancia = :id_instancia
                ");
                $stmt_demora->execute([
                    'demora' => $demora_horas,
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Verificar mostrar_stats
                $check_stats = $pdo_certifi->prepare("SELECT certificatum_mostrar_stats FROM certificatum_config WHERE id_instancia = :id");
                $check_stats->execute(['id' => $instance['id_instancia']]);
                $stats_result = $check_stats->fetch(PDO::FETCH_ASSOC);

                $mostrar_stats_guardado = $stats_result['certificatum_mostrar_stats'] ?? 'NULL';
                $mensaje = 'Configuración guardada correctamente. Mostrar stats: ' . ($mostrar_stats_guardado ? 'Sí' : 'No');
                $tipo_mensaje = 'success';
                $scroll_to = 'tab-cert-configuracion';
                $active_tab = 'configuracion';

                // Limpiar cache y recargar instancia
                \VERUMax\Services\InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

                // Recargar traducciones para mostrar valores actualizados
                $traducciones_certificatum = [];
                $stmt_trad_reload = $pdo_general->prepare("
                    SELECT campo, idioma, contenido
                    FROM instance_translations
                    WHERE id_instancia = :id_instancia
                    AND campo IN ('certificatum_descripcion', 'certificatum_cta_texto')
                ");
                $stmt_trad_reload->execute(['id_instancia' => $instance['id_instancia']]);
                while ($row = $stmt_trad_reload->fetch(PDO::FETCH_ASSOC)) {
                    $traducciones_certificatum[$row['campo']][$row['idioma']] = $row['contenido'];
                }

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

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
                $resultado = \VERUMax\Services\CursoService::importarDesdeTexto(
                    $instance['id_instancia'],
                    $_POST['texto_cursos']
                );
                $mensaje = "Cursos cargados: {$resultado['insertados']} nuevos, {$resultado['actualizados']} actualizados";
                if (!empty($resultado['errores'])) {
                    $mensaje .= ". Errores: " . count($resultado['errores']);
                    $tipo_mensaje = 'warning';
                }
                $active_tab = 'cursos';
            } else {
                $errores[] = 'El texto está vacío';
            }
            break;

        case 'inscribir_curso':
            if (!empty($_POST['texto_inscripciones']) && !empty($_POST['id_curso_inscribir'])) {
                $resultado = procesarInscripcionesCurso($_POST['texto_inscripciones'], $institucion, $_POST['id_curso_inscribir']);

                // Enviar emails si el checkbox está marcado y hay destinatarios
                if (!empty($_POST['notificar_email']) && !empty($resultado['destinatarios_email']) && $resultado['id_instancia']) {
                    // Determinar tipo de email según tipo de curso
                    $tipos_certificado = ['diplomatura', 'certificacion', 'especializacion', 'posgrado', 'maestria'];
                    $tipo_curso = $resultado['tipo_curso'] ?? 'curso';
                    $es_certificado = in_array(strtolower($tipo_curso), $tipos_certificado);

                    $emailType = $es_certificado
                        ? \VERUMax\Services\EmailService::TYPE_CERTIFICADO
                        : \VERUMax\Services\EmailService::TYPE_CONSTANCIA;
                    $emailTemplate = $es_certificado ? 'certificado_disponible' : 'constancia_disponible';

                    $emailResult = \VERUMax\Services\EmailService::enviarMasivo(
                        $resultado['id_instancia'],
                        $emailType,
                        $resultado['destinatarios_email'],
                        $emailTemplate
                    );

                    // Agregar info de emails al resultado
                    $resultado['emails_enviados'] = $emailResult['enviados'];
                    $resultado['emails_sin_email'] = $emailResult['sin_email'];
                    if (!empty($emailResult['errores'])) {
                        foreach ($emailResult['errores'] as $err) {
                            $resultado['errores'][] = "Email: " . $err;
                        }
                    }
                }
            } else {
                $errores[] = 'Datos incompletos';
            }
            break;

        // Gestión de estudiantes (ahora usa verumax_nexus.miembros)
        case 'crear_estudiante':
            // Validar campos requeridos
            $dni = $_POST['dni'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $apellido = $_POST['apellido'] ?? '';

            if (empty($dni) || empty($nombre)) {
                $mensaje = 'DNI y Nombre son campos requeridos';
                $tipo_mensaje = 'error';
                $active_tab = 'estudiantes';
                break;
            }

            // Validar formato de DNI (solo A-Z, 0-9, guion, punto)
            $dni = strtoupper(preg_replace('/[^A-Za-z0-9.\-]/', '', $dni));
            if (empty($dni) || strlen($dni) > 20) {
                $mensaje = 'DNI inválido. Solo se permiten letras, números, guion y punto (máx 20 caracteres)';
                $tipo_mensaje = 'error';
                $active_tab = 'estudiantes';
                break;
            }

            // Preparar datos adicionales
            $extras = [
                'email' => $_POST['email'] ?? null,
                'telefono' => $_POST['telefono'] ?? null,
                'estado' => $_POST['estado'] ?? 'Activo',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
                'genero' => $_POST['genero'] ?? 'Prefiero no especificar',
                'domicilio_ciudad' => $_POST['domicilio_ciudad'] ?? null,
                'domicilio_provincia' => $_POST['domicilio_provincia'] ?? null,
                'domicilio_codigo_postal' => $_POST['domicilio_codigo_postal'] ?? null,
                'domicilio_pais' => $_POST['domicilio_pais'] ?? 'Argentina',
                'profesion' => $_POST['profesion'] ?? null,
                'lugar_trabajo' => $_POST['lugar_trabajo'] ?? null,
                'cargo' => $_POST['cargo'] ?? null,
            ];
            $res_crear = crearEstudiante(
                $institucion,
                $dni,
                $nombre,
                $apellido,
                $extras
            );
            if (is_array($res_crear) && isset($res_crear['success'])) {
                $mensaje = 'Estudiante creado correctamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = $res_crear;
                $tipo_mensaje = 'error';
            }
            $active_tab = 'estudiantes';
            break;

        case 'actualizar_estudiante':
            // Validar campos requeridos
            $id_estudiante = $_POST['id_estudiante'] ?? '';
            $dni = $_POST['dni'] ?? '';
            $nombre = $_POST['nombre'] ?? '';

            if (empty($id_estudiante) || empty($dni) || empty($nombre)) {
                $mensaje = 'ID, DNI y Nombre son campos requeridos';
                $tipo_mensaje = 'error';
                $active_tab = 'estudiantes';
                break;
            }

            // Validar formato de DNI (solo A-Z, 0-9, guion, punto)
            $dni = strtoupper(preg_replace('/[^A-Za-z0-9.\-]/', '', $dni));
            if (empty($dni) || strlen($dni) > 20) {
                $mensaje = 'DNI inválido. Solo se permiten letras, números, guion y punto (máx 20 caracteres)';
                $tipo_mensaje = 'error';
                $active_tab = 'estudiantes';
                break;
            }

            // Preparar array de datos a actualizar
            $datos_actualizar = [
                'identificador_principal' => $dni,
                'nombre' => $nombre,
                'apellido' => $_POST['apellido'] ?? '',
                'email' => $_POST['email'] ?? null,
                'telefono' => $_POST['telefono'] ?? null,
                'estado' => $_POST['estado'] ?? 'Activo',
            ];
            // Campos opcionales solo si vienen
            if (!empty($_POST['fecha_nacimiento'])) {
                $datos_actualizar['fecha_nacimiento'] = $_POST['fecha_nacimiento'];
            }
            // Género siempre se actualiza (incluso si es "Prefiero no especificar")
            if (isset($_POST['genero'])) {
                $datos_actualizar['genero'] = $_POST['genero'];
            }
            if (!empty($_POST['domicilio_ciudad'])) {
                $datos_actualizar['domicilio_ciudad'] = $_POST['domicilio_ciudad'];
            }
            if (!empty($_POST['domicilio_provincia'])) {
                $datos_actualizar['domicilio_provincia'] = $_POST['domicilio_provincia'];
            }
            if (!empty($_POST['domicilio_codigo_postal'])) {
                $datos_actualizar['domicilio_codigo_postal'] = $_POST['domicilio_codigo_postal'];
            }
            if (!empty($_POST['domicilio_pais'])) {
                $datos_actualizar['domicilio_pais'] = $_POST['domicilio_pais'];
            }
            // Campos laborales - siempre actualizar (pueden vaciarse)
            $datos_actualizar['profesion'] = $_POST['profesion'] ?? null;
            $datos_actualizar['lugar_trabajo'] = $_POST['lugar_trabajo'] ?? null;
            $datos_actualizar['cargo'] = $_POST['cargo'] ?? null;

            $resultado_update = actualizarEstudiante($id_estudiante, $datos_actualizar);
            if (is_array($resultado_update) && $resultado_update['success']) {
                $mensaje = 'Estudiante actualizado correctamente';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = is_array($resultado_update) ? $resultado_update['mensaje'] : $resultado_update;
                $tipo_mensaje = 'error';
            }
            $active_tab = 'estudiantes';
            break;

        case 'eliminar_estudiante':
            $resultado_eliminar = eliminarEstudiante($_POST['id_estudiante']);
            // eliminarEstudiante retorna array ['success' => bool, 'mensaje' => string]
            $msg_type = $resultado_eliminar['success'] ? 'success' : 'error';
            $msg_texto = $resultado_eliminar['mensaje'] ?? 'Operación completada';
            // Usar JavaScript para redirección ya que headers ya fueron enviados por index.php
            $redirect_url = "?modulo=certificatum&tab=estudiantes&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;
            break;

        case 'eliminar_estudiantes_masivo':
            $ids = $_POST['ids'] ?? [];
            $eliminados = 0;
            $errores = 0;
            foreach ($ids as $id) {
                $resultado = eliminarEstudiante((int)$id);
                if ($resultado['success']) {
                    $eliminados++;
                } else {
                    $errores++;
                }
            }
            $msg_type = $errores === 0 ? 'success' : ($eliminados > 0 ? 'warning' : 'error');
            $msg_texto = "Eliminados: $eliminados estudiante(s)";
            if ($errores > 0) {
                $msg_texto .= ", Errores: $errores";
            }
            $redirect_url = "?modulo=certificatum&tab=estudiantes&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;
            break;

        case 'eliminar_docentes_masivo':
            $ids = $_POST['ids'] ?? [];
            $eliminados = 0;
            $errores = 0;
            foreach ($ids as $id) {
                $resultado = eliminarDocente((int)$id);
                if ($resultado['success']) {
                    $eliminados++;
                } else {
                    $errores++;
                }
            }
            $msg_type = $errores === 0 ? 'success' : ($eliminados > 0 ? 'warning' : 'error');
            $msg_texto = "Eliminados: $eliminados docente(s)";
            if ($errores > 0) {
                $msg_texto .= ", Errores: $errores";
            }
            $redirect_url = "?modulo=certificatum&tab=docentes&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;
            break;

        case 'eliminar_inscripciones_masivo':
            $ids = $_POST['ids'] ?? [];
            $eliminados = 0;
            $errores = 0;
            foreach ($ids as $id) {
                $resultado = eliminarInscripcion((int)$id);
                if ($resultado['success']) {
                    $eliminados++;
                } else {
                    $errores++;
                }
            }
            $msg_type = $errores === 0 ? 'success' : ($eliminados > 0 ? 'warning' : 'error');
            $msg_texto = "Eliminadas: $eliminados inscripción(es)";
            if ($errores > 0) {
                $msg_texto .= ", Errores: $errores";
            }
            $redirect_url = "?modulo=certificatum&tab=inscripciones&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;
            break;

        case 'eliminar_asignaciones_masivo':
            $ids = $_POST['ids'] ?? [];
            $eliminados = 0;
            $errores = 0;
            foreach ($ids as $id) {
                $resultado = eliminarParticipacionDocente((int)$id);
                if ($resultado['success']) {
                    $eliminados++;
                } else {
                    $errores++;
                }
            }
            $msg_type = $errores === 0 ? 'success' : ($eliminados > 0 ? 'warning' : 'error');
            $msg_texto = "Eliminadas: $eliminados asignación(es)";
            if ($errores > 0) {
                $msg_texto .= ", Errores: $errores";
            }
            $redirect_url = "?modulo=certificatum&tab=asignaciones&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;
            break;

        case 'cargar_estudiantes_archivo':
            if (isset($_FILES['archivo_estudiantes']) && $_FILES['archivo_estudiantes']['error'] === UPLOAD_ERR_OK) {
                $resultado = procesarArchivoEstudiantes($_FILES['archivo_estudiantes'], $institucion);
                $mensaje = "Estudiantes cargados: {$resultado['estudiantes_insertados']} nuevos, {$resultado['estudiantes_actualizados']} actualizados";
                if (!empty($resultado['errores'])) {
                    $mensaje .= ". Errores: " . count($resultado['errores']);
                    $tipo_mensaje = 'warning';
                } else {
                    $tipo_mensaje = 'success';
                }
            } else {
                $mensaje = 'Error al subir el archivo';
                $tipo_mensaje = 'error';
            }
            $active_tab = 'estudiantes';
            break;

        // Gestión de cursos
        case 'crear_curso':
            // Si el código está vacío, generar automáticamente
            $codigo_curso = trim($_POST['codigo_curso'] ?? '');
            if (empty($codigo_curso)) {
                $tipo_curso = $_POST['tipo_curso'] ?? 'Curso';
                $codigo_curso = \VERUMax\Services\CursoService::generarCodigoSugerido(
                    $instance['id_instancia'],
                    $tipo_curso
                );
            }

            // Validar formato del código
            $validacion_codigo = \VERUMax\Services\CursoService::validarFormatoCodigo($codigo_curso);
            if (!$validacion_codigo['valido']) {
                $mensaje = $validacion_codigo['mensaje'];
                $tipo_mensaje = 'error';
                $active_tab = 'cursos';
                break;
            }

            $resultado_curso = \VERUMax\Services\CursoService::crear([
                'id_instancia' => $instance['id_instancia'],
                'codigo_curso' => $codigo_curso,
                'nombre_curso' => $_POST['nombre_curso'],
                'descripcion' => $_POST['descripcion'] ?? null,
                'categoria' => $_POST['categoria'] ?? null,
                'tipo_curso' => $_POST['tipo_curso'] ?? 'Curso',
                'modalidad' => $_POST['modalidad'] ?? 'Virtual',
                'nivel' => $_POST['nivel'] ?? 'Todos los niveles',
                'carga_horaria' => $_POST['carga_horaria'] ?? null,
                'duracion_semanas' => $_POST['duracion_semanas'] ?? null,
                'cupo_maximo' => $_POST['cupo_maximo'] ?? null,
                'fecha_inicio' => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
                'fecha_fin' => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null,
                'ciudad_emision' => !empty($_POST['ciudad_emision']) ? trim($_POST['ciudad_emision']) : null,
                'activo' => $_POST['activo'] ?? 1,
                'firmante_1_nombre' => !empty($_POST['firmante_1_nombre']) ? $_POST['firmante_1_nombre'] : null,
                'firmante_1_cargo' => !empty($_POST['firmante_1_cargo']) ? $_POST['firmante_1_cargo'] : null,
                'firmante_2_nombre' => !empty($_POST['firmante_2_nombre']) ? $_POST['firmante_2_nombre'] : null,
                'firmante_2_cargo' => !empty($_POST['firmante_2_cargo']) ? $_POST['firmante_2_cargo'] : null,
                'usar_firmante_1' => isset($_POST['usar_firmante_1']) ? 1 : 0,
                'usar_firmante_2' => isset($_POST['usar_firmante_2']) ? 1 : 0,
                'usar_demora_global' => isset($_POST['usar_demora_global']) && $_POST['usar_demora_global'] == '1' ? 1 : 0,
                'demora_tipo' => $_POST['usar_demora_global'] == '0' ? ($_POST['demora_tipo'] ?? 'inmediato') : 'inmediato',
                'demora_certificado_horas' => $_POST['usar_demora_global'] == '0' && in_array($_POST['demora_tipo'] ?? '', ['horas', 'dias', 'meses']) ? (int)$_POST['demora_valor'] : null,
                'demora_fecha' => $_POST['usar_demora_global'] == '0' && ($_POST['demora_tipo'] ?? '') == 'fecha' && !empty($_POST['demora_fecha']) ? $_POST['demora_fecha'] : null
            ]);

            // Guardar competencias si se creó el curso exitosamente
            if ($resultado_curso['success'] && !empty($_POST['competencias_json'])) {
                $competencias = json_decode($_POST['competencias_json'], true);
                if (is_array($competencias) && !empty($competencias)) {
                    \VERUMax\Services\CursoService::guardarCompetencias(
                        $resultado_curso['id_curso'],
                        $competencias
                    );
                }
            }

            $mensaje = $resultado_curso['mensaje'];
            $tipo_mensaje = $resultado_curso['success'] ? 'success' : 'error';
            $active_tab = 'cursos';
            break;

        case 'actualizar_curso':
            // Validar formato del código
            $validacion_codigo = \VERUMax\Services\CursoService::validarFormatoCodigo($_POST['codigo_curso']);
            if (!$validacion_codigo['valido']) {
                $mensaje = $validacion_codigo['mensaje'];
                $tipo_mensaje = 'error';
                $active_tab = 'cursos';
                break;
            }

            // Verificar si el curso tiene certificados emitidos antes de cambiar el código
            $curso_actual = \VERUMax\Services\CursoService::getById((int)$_POST['id_curso']);
            if ($curso_actual && $curso_actual['codigo_curso'] !== strtoupper(trim($_POST['codigo_curso']))) {
                // El código cambió, verificar si tiene certificados
                if (\VERUMax\Services\CursoService::tieneCertificadosEmitidos((int)$_POST['id_curso'])) {
                    $mensaje = 'No se puede cambiar el código: este curso tiene certificados emitidos. Cambiar el código invalidaría esos certificados.';
                    $tipo_mensaje = 'error';
                    $active_tab = 'cursos';
                    break;
                }
            }

            // id_template: vacío = null (usar sistema actual), número = template específico
            $id_template = isset($_POST['id_template']) && $_POST['id_template'] !== ''
                ? (int) $_POST['id_template']
                : null;

            // URLs de firmas (pueden venir del hidden input si ya existen)
            $firmante_1_firma_url = $_POST['firmante_1_firma_url'] ?? '';
            $firmante_2_firma_url = $_POST['firmante_2_firma_url'] ?? '';

            // Procesar upload de firma 1 si se subió archivo
            // Guardar en la misma carpeta que firmas generales (sin subcarpeta cursos/)
            if (isset($_FILES['firmante_1_firma']) && $_FILES['firmante_1_firma']['error'] === UPLOAD_ERR_OK) {
                $firmas_dir = __DIR__ . '/../../assets/images/firmas/';
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
                        $firmante_1_firma_url = 'assets/images/firmas/' . $filename;
                    }
                }
            }

            // Procesar upload de firma 2 si se subió archivo
            // Guardar en la misma carpeta que firmas generales (sin subcarpeta cursos/)
            if (isset($_FILES['firmante_2_firma']) && $_FILES['firmante_2_firma']['error'] === UPLOAD_ERR_OK) {
                $firmas_dir = __DIR__ . '/../../assets/images/firmas/';
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
                        $firmante_2_firma_url = 'assets/images/firmas/' . $filename;
                    }
                }
            }

            $resultado_curso = \VERUMax\Services\CursoService::actualizar((int)$_POST['id_curso'], [
                'codigo_curso' => $_POST['codigo_curso'],
                'nombre_curso' => $_POST['nombre_curso'],
                'descripcion' => $_POST['descripcion'] ?? null,
                'categoria' => $_POST['categoria'] ?? null,
                'tipo_curso' => $_POST['tipo_curso'] ?? 'Curso',
                'modalidad' => $_POST['modalidad'] ?? 'Virtual',
                'nivel' => $_POST['nivel'] ?? 'Todos los niveles',
                'carga_horaria' => $_POST['carga_horaria'] ?? null,
                'duracion_semanas' => $_POST['duracion_semanas'] ?? null,
                'cupo_maximo' => $_POST['cupo_maximo'] ?? null,
                'fecha_inicio' => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
                'fecha_fin' => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null,
                'ciudad_emision' => !empty($_POST['ciudad_emision']) ? trim($_POST['ciudad_emision']) : null,
                'activo' => $_POST['activo'] ?? 1,
                'id_template' => $id_template,
                'firmante_1_nombre' => !empty($_POST['firmante_1_nombre']) ? $_POST['firmante_1_nombre'] : null,
                'firmante_1_cargo' => !empty($_POST['firmante_1_cargo']) ? $_POST['firmante_1_cargo'] : null,
                'firmante_2_nombre' => !empty($_POST['firmante_2_nombre']) ? $_POST['firmante_2_nombre'] : null,
                'firmante_2_cargo' => !empty($_POST['firmante_2_cargo']) ? $_POST['firmante_2_cargo'] : null,
                'usar_firmante_1' => isset($_POST['usar_firmante_1']) ? 1 : 0,
                'usar_firmante_2' => isset($_POST['usar_firmante_2']) ? 1 : 0,
                'firmante_1_firma_url' => $firmante_1_firma_url ?: null,
                'firmante_2_firma_url' => $firmante_2_firma_url ?: null,
                'usar_demora_global' => isset($_POST['usar_demora_global']) && $_POST['usar_demora_global'] == '1' ? 1 : 0,
                'demora_tipo' => $_POST['usar_demora_global'] == '0' ? ($_POST['demora_tipo'] ?? 'inmediato') : 'inmediato',
                'demora_certificado_horas' => $_POST['usar_demora_global'] == '0' && in_array($_POST['demora_tipo'] ?? '', ['horas', 'dias', 'meses']) ? (int)$_POST['demora_valor'] : null,
                'demora_fecha' => $_POST['usar_demora_global'] == '0' && ($_POST['demora_tipo'] ?? '') == 'fecha' && !empty($_POST['demora_fecha']) ? $_POST['demora_fecha'] : null
            ]);

            // Guardar competencias (siempre, incluso si está vacío para permitir eliminar todas)
            if ($resultado_curso['success'] && isset($_POST['competencias_json'])) {
                $competencias = json_decode($_POST['competencias_json'], true);
                if (is_array($competencias)) {
                    \VERUMax\Services\CursoService::guardarCompetencias(
                        (int)$_POST['id_curso'],
                        $competencias
                    );
                }
            }

            $mensaje = $resultado_curso['mensaje'];
            $tipo_mensaje = $resultado_curso['success'] ? 'success' : 'error';
            $active_tab = 'cursos';
            break;

        // Obtener código sugerido (AJAX)
        case 'obtener_codigo_sugerido':
            header('Content-Type: application/json');
            $tipo_curso = $_POST['tipo_curso'] ?? 'Curso';
            $codigo = \VERUMax\Services\CursoService::generarCodigoSugerido(
                $instance['id_instancia'],
                $tipo_curso
            );
            echo json_encode(['codigo' => $codigo]);
            exit;

        // Obtener competencias de un curso (AJAX)
        case 'obtener_competencias':
            header('Content-Type: application/json');
            $id_curso = (int)($_POST['id_curso'] ?? 0);
            if ($id_curso > 0) {
                $competencias = \VERUMax\Services\CursoService::getCompetencias($id_curso);
                echo json_encode(['success' => true, 'competencias' => $competencias]);
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'ID de curso inválido']);
            }
            exit;

        // ============ ENDPOINTS DE TEMPLATES DE CERTIFICADOS ============
        case 'obtener_templates':
            header('Content-Type: application/json');
            try {
                $templates = \VERUMax\Services\CertificateTemplateService::getAll($slug);
                $response = [
                    'success' => true,
                    'templates' => array_merge(
                        [
                            [
                                'id_template' => null,
                                'slug' => 'default',
                                'nombre' => 'Predeterminado',
                                'descripcion' => 'Usa el sistema de certificados actual',
                                'preview_url' => null,
                                'is_default' => true
                            ]
                        ],
                        $templates
                    )
                ];
                echo json_encode($response);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;

        case 'obtener_template_curso':
            header('Content-Type: application/json');
            $id_curso = (int)($_POST['id_curso'] ?? 0);
            if ($id_curso > 0) {
                try {
                    $template = \VERUMax\Services\CertificateTemplateService::getForCursoById($id_curso);
                    echo json_encode([
                        'success' => true,
                        'id_curso' => $id_curso,
                        'template' => $template,
                        'uses_default' => $template === null
                    ]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'ID de curso inválido']);
            }
            exit;

        case 'actualizar_template_config':
            header('Content-Type: application/json');
            $id_template = (int)($_POST['id_template'] ?? 0);
            $config_json = $_POST['config'] ?? '';

            if ($id_template <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID de template inválido']);
                exit;
            }

            // Validar que sea JSON válido SOLO si no está vacío
            // Config vacío es válido (significa "eliminar configuración")
            if (!empty($config_json)) {
                $config_decoded = json_decode($config_json, true);
                if ($config_decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                    echo json_encode(['success' => false, 'error' => 'JSON inválido: ' . json_last_error_msg()]);
                    exit;
                }
            }

            try {
                $result = \VERUMax\Services\CertificateTemplateService::updateConfig($id_template, $config_json);
                if ($result) {
                    echo json_encode(['success' => true, 'mensaje' => 'Config actualizado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'No se encontró el template con ID ' . $id_template . '. ¿Ejecutaste el SQL de migración?']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
            }
            exit;

        case 'obtener_template_config':
            header('Content-Type: application/json');
            $id_template = isset($_POST['id_template']) ? (int)$_POST['id_template'] : 0;

            if ($id_template <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID de template inválido']);
                exit;
            }

            try {
                $template = \VERUMax\Services\CertificateTemplateService::getById($id_template);
                if ($template) {
                    echo json_encode([
                        'success' => true,
                        'config' => $template['config'] ?? '',
                        'nombre' => $template['nombre'] ?? '',
                        'has_config' => !empty($template['config'])
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Template no encontrado']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
            }
            exit;

        case 'eliminar_curso':
            $resultado_curso = eliminarCurso($_POST['id_curso']);
            $mensaje = $resultado_curso['mensaje'];
            $tipo_mensaje = $resultado_curso['success'] ? 'success' : 'error';
            $active_tab = 'cursos';
            break;

        case 'cargar_cursos_csv':
            if (isset($_FILES['archivo_cursos_csv']) && $_FILES['archivo_cursos_csv']['error'] === UPLOAD_ERR_OK) {
                $resultado = \VERUMax\Services\CursoService::importarDesdeArchivo(
                    $instance['id_instancia'],
                    $_FILES['archivo_cursos_csv']['tmp_name']
                );
                $mensaje = "Cursos cargados: {$resultado['insertados']} nuevos, {$resultado['actualizados']} actualizados";
                if (!empty($resultado['errores'])) {
                    $mensaje .= ". Errores: " . count($resultado['errores']);
                    $tipo_mensaje = 'warning';
                }
            } else {
                $mensaje = 'Error al subir el archivo CSV';
                $tipo_mensaje = 'error';
            }
            $active_tab = 'cursos';
            break;

        case 'cargar_cursos_excel':
            if (isset($_FILES['archivo_cursos_excel']) && $_FILES['archivo_cursos_excel']['error'] === UPLOAD_ERR_OK) {
                // Por ahora Excel no está soportado, convertir a CSV primero
                $mensaje = 'Para cargar desde Excel, primero exporte a CSV (Archivo > Guardar como > CSV)';
                $tipo_mensaje = 'warning';
            } else {
                $mensaje = 'Error al subir el archivo Excel';
                $tipo_mensaje = 'error';
            }
            $active_tab = 'cursos';
            break;

        // Gestión de inscripciones
        case 'crear_inscripcion':
            $id_miembro = $_POST['id_miembro'] ?? 0;
            $id_curso = $_POST['id_curso'] ?? 0;
            $estado = $_POST['estado'] ?? 'Inscrito';
            $notificar = isset($_POST['notificar_estudiante']) && $_POST['notificar_estudiante'] == '1';

            if (empty($id_miembro) || empty($id_curso)) {
                $mensaje = 'Debe seleccionar estudiante y curso';
                $tipo_mensaje = 'error';
                $active_tab = 'inscripciones';
                break;
            }

            $extras = [
                'id_instancia' => $instance['id_instancia'],  // Requerido por InscripcionService
                'fecha_inicio' => $_POST['fecha_inicio'] ?: null,
                'fecha_finalizacion' => $_POST['fecha_finalizacion'] ?: null,
                'nota_final' => $_POST['nota_final'] ?: null,
                'asistencia' => $_POST['asistencia'] ?: null,
                'estado_pago' => $_POST['estado_pago'] ?? 'Pendiente',
                'observaciones' => $_POST['observaciones'] ?? null,
            ];

            $resultado_insc = crearInscripcion($id_miembro, $id_curso, $estado, $extras);
            $mensaje = $resultado_insc['mensaje'];
            $tipo_mensaje = $resultado_insc['success'] ? 'success' : 'error';

            // Enviar notificación por email si fue exitoso y está habilitado
            if ($resultado_insc['success'] && $notificar) {
                try {
                    // Usar la conexión de certificatum (puede acceder a otras BDs)
                    $pdo_certifi = getCertDBConnection();

                    // Estudiantes están en verumax_nexus
                    $stmt_est = $pdo_certifi->prepare("
                        SELECT nombre_completo, email, identificador_principal as dni
                        FROM verumax_nexus.miembros
                        WHERE id_miembro = :id_miembro
                    ");
                    $stmt_est->execute([':id_miembro' => $id_miembro]);
                    $estudiante = $stmt_est->fetch(PDO::FETCH_ASSOC);

                    // Cursos están en verumax_academi
                    $stmt_curso = $pdo_certifi->prepare("
                        SELECT nombre_curso as nombre, codigo_curso, tipo_curso
                        FROM verumax_academi.cursos
                        WHERE id_curso = :id_curso
                    ");
                    $stmt_curso->execute([':id_curso' => $id_curso]);
                    $curso = $stmt_curso->fetch(PDO::FETCH_ASSOC);

                    if ($estudiante && $curso && !empty($estudiante['email'])) {
                        // Determinar tipo de email y documento según estado
                        $tipoEmail = ($estado === 'Completado' || $estado === 'Aprobado')
                            ? \VERUMax\Services\EmailService::TYPE_CERTIFICADO
                            : \VERUMax\Services\EmailService::TYPE_CONSTANCIA;

                        // Determinar nombre del tipo de documento según estado (normalizado)
                        $tipos_documento = [
                            'preinscrito' => 'Constancia de Preinscripción',
                            'inscrito' => 'Constancia de Inscripción',
                            'inscripto' => 'Constancia de Inscripción',
                            'en curso' => 'Constancia de Alumno Regular',
                            'finalizado' => 'Certificado de Finalización',
                            'aprobado' => 'Certificado de Aprobación',
                            'completado' => 'Certificado',
                            'desaprobado' => 'Constancia de Cursado',
                            'abandonado' => 'Constancia de Cursado',
                            'suspendido' => 'Constancia de Cursado'
                        ];
                        $estado_normalizado = strtolower(trim($estado));
                        $tipo_documento = $tipos_documento[$estado_normalizado] ?? 'Constancia';

                        // Construir URL del portal institucional
                        $urlPortal = "https://{$slug}.verumax.com/";

                        // Variables para el template
                        $variables = [
                            'nombre_curso' => $curso['nombre'],
                            'url_portal' => $urlPortal,
                            'tipo_documento' => $tipo_documento
                        ];

                        // Enviar email
                        $resultEmail = \VERUMax\Services\EmailService::enviarIndividual(
                            $instance['id_instancia'],
                            $tipoEmail,
                            $estudiante['email'],
                            $estudiante['nombre_completo'],
                            $variables
                        );

                        if ($resultEmail['success']) {
                            $mensaje .= ' | Email enviado correctamente';
                        } else {
                            $mensaje .= ' | Error al enviar email: ' . ($resultEmail['error'] ?? 'desconocido');
                            $tipo_mensaje = 'warning';
                        }
                    } elseif ($estudiante && empty($estudiante['email'])) {
                        $mensaje .= ' | No se envió email: el estudiante no tiene email registrado';
                        $tipo_mensaje = 'warning';
                    }
                } catch (\Exception $e) {
                    error_log("Error al enviar notificación de inscripción: " . $e->getMessage());
                    $mensaje .= ' | Error email: ' . $e->getMessage();
                    $tipo_mensaje = 'warning';
                }
            }

            // Notificar evaluación si está marcado el checkbox
            $notificar_eval = isset($_POST['notificar_evaluacion']) && $_POST['notificar_evaluacion'] == '1';
            $id_evaluatio = $_POST['id_evaluatio_notificar'] ?? 0;
            if ($resultado_insc['success'] && $notificar_eval && $id_evaluatio) {
                $id_inscripcion_nueva = $resultado_insc['id_inscripcion'] ?? 0;
                if ($id_inscripcion_nueva) {
                    $resultado_eval_notif = notificarEvaluacionDisponible(
                        $id_evaluatio,
                        $instance['id_instancia'],
                        [$id_inscripcion_nueva]
                    );
                    if ($resultado_eval_notif['success']) {
                        $mensaje .= ' | Evaluación notificada';
                    } else {
                        $mensaje .= ' | Error notificando evaluación: ' . $resultado_eval_notif['mensaje'];
                        $tipo_mensaje = 'warning';
                    }
                }
            }

            $active_tab = 'inscripciones';
            break;

        case 'actualizar_inscripcion':
            $resultado_insc = actualizarInscripcion(
                $_POST['id_inscripcion'],
                $_POST['estado'],
                $_POST['fecha_inicio'],
                $_POST['fecha_finalizacion'],
                $_POST['nota_final'],
                $_POST['asistencia']
            );
            $mensaje = $resultado_insc['mensaje'];
            $tipo_mensaje = $resultado_insc['success'] ? 'success' : 'error';

            // Enviar notificación si el checkbox está marcado y la actualización fue exitosa
            $notificar = isset($_POST['notificar_estudiante']) && $_POST['notificar_estudiante'] == '1';
            if ($resultado_insc['success'] && $notificar) {
                try {
                    $pdo_certifi = getCertDBConnection();

                    // Obtener datos de la inscripción actualizada
                    $stmt = $pdo_certifi->prepare("
                        SELECT i.*, m.nombre_completo, m.email, m.identificador_principal as dni,
                               c.nombre_curso, c.tipo_curso, c.codigo_curso
                        FROM verumax_academi.inscripciones i
                        JOIN verumax_nexus.miembros m ON i.id_miembro = m.id_miembro
                        JOIN verumax_academi.cursos c ON i.id_curso = c.id_curso
                        WHERE i.id_inscripcion = :id_inscripcion
                    ");
                    $stmt->execute([':id_inscripcion' => $_POST['id_inscripcion']]);
                    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($inscripcion && !empty($inscripcion['email'])) {
                        // Determinar tipo de email según estado
                        $estado = $_POST['estado'];
                        $tipos_certificado = ['diplomatura', 'certificacion', 'especializacion', 'posgrado', 'maestria'];
                        $es_tipo_certificado = in_array(strtolower($inscripcion['tipo_curso'] ?? ''), $tipos_certificado);

                        // Aprobado = Certificado, otros estados = Constancia
                        if ($estado === 'Aprobado' || $estado === 'Completado') {
                            $tipoEmail = \VERUMax\Services\EmailService::TYPE_CERTIFICADO;
                            $emailTemplate = 'certificado_disponible';
                        } else {
                            $tipoEmail = \VERUMax\Services\EmailService::TYPE_CONSTANCIA;
                            $emailTemplate = 'constancia_disponible';
                        }

                        // Determinar nombre del tipo de documento según estado (normalizado)
                        $tipos_documento = [
                            'preinscrito' => 'Constancia de Preinscripción',
                            'inscrito' => 'Constancia de Inscripción',
                            'inscripto' => 'Constancia de Inscripción',
                            'en curso' => 'Constancia de Alumno Regular',
                            'finalizado' => 'Certificado de Finalización',
                            'aprobado' => 'Certificado de Aprobación',
                            'completado' => 'Certificado',
                            'desaprobado' => 'Constancia de Cursado',
                            'abandonado' => 'Constancia de Cursado',
                            'suspendido' => 'Constancia de Cursado'
                        ];
                        $estado_normalizado = strtolower(trim($estado));
                        $tipo_documento = $tipos_documento[$estado_normalizado] ?? 'Constancia';

                        // Construir URL del portal
                        $urlPortal = 'https://' . $institucion . '.verumax.com/';

                        $variables = [
                            'nombre_estudiante' => $inscripcion['nombre_completo'],
                            'nombre_curso' => $inscripcion['nombre_curso'],
                            'url_portal' => $urlPortal,
                            'tipo_documento' => $tipo_documento
                        ];

                        $resultEmail = \VERUMax\Services\EmailService::enviarIndividual(
                            $instance['id_instancia'],
                            $tipoEmail,
                            $inscripcion['email'],
                            $inscripcion['nombre_completo'],
                            $variables
                        );

                        if ($resultEmail['success']) {
                            $mensaje .= ' | Email enviado correctamente';
                        } else {
                            $mensaje .= ' | Error al enviar email: ' . ($resultEmail['error'] ?? 'desconocido');
                            $tipo_mensaje = 'warning';
                        }
                    } elseif ($inscripcion && empty($inscripcion['email'])) {
                        $mensaje .= ' | No se envió email: el estudiante no tiene email registrado';
                        $tipo_mensaje = 'warning';
                    }
                } catch (\Exception $e) {
                    $mensaje .= ' | Error al enviar email: ' . $e->getMessage();
                    $tipo_mensaje = 'warning';
                }
            }

            // Notificar evaluación si está marcado el checkbox
            $notificar_eval = isset($_POST['notificar_evaluacion']) && $_POST['notificar_evaluacion'] == '1';
            $id_evaluatio = $_POST['id_evaluatio_notificar'] ?? 0;
            if ($resultado_insc['success'] && $notificar_eval && $id_evaluatio) {
                $resultado_eval_notif = notificarEvaluacionDisponible(
                    $id_evaluatio,
                    $instance['id_instancia'],
                    [$_POST['id_inscripcion']]
                );
                if ($resultado_eval_notif['success']) {
                    $mensaje .= ' | Evaluación notificada';
                } else {
                    $mensaje .= ' | Error notificando evaluación: ' . $resultado_eval_notif['mensaje'];
                    $tipo_mensaje = 'warning';
                }
            }

            $active_tab = 'inscripciones';
            break;

        case 'eliminar_inscripcion':
            $resultado_insc = eliminarInscripcion($_POST['id_inscripcion']);
            $msg_type = $resultado_insc['success'] ? 'success' : 'error';
            $msg_texto = $resultado_insc['mensaje'] ?? 'Operación completada';
            $redirect_url = "?modulo=certificatum&tab=inscripciones&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;

        case 'reenviar_notificacion':
            $id_inscripcion = $_POST['id_inscripcion'] ?? 0;
            $msg_texto = 'Error: inscripción no encontrada';
            $msg_type = 'error';

            if ($id_inscripcion) {
                try {
                    // Usar la conexión de certificatum
                    $pdo_certifi = getCertDBConnection();

                    // Obtener datos de la inscripción con estudiante y curso (tablas en diferentes BDs)
                    $stmt = $pdo_certifi->prepare("
                        SELECT i.*, m.nombre_completo, m.email, m.identificador_principal as dni, c.nombre_curso, c.codigo_curso, c.tipo_curso
                        FROM verumax_academi.inscripciones i
                        JOIN verumax_nexus.miembros m ON i.id_miembro = m.id_miembro
                        JOIN verumax_academi.cursos c ON i.id_curso = c.id_curso
                        WHERE i.id_inscripcion = :id
                    ");
                    $stmt->execute([':id' => $id_inscripcion]);
                    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($inscripcion) {
                        if (empty($inscripcion['email'])) {
                            $msg_texto = 'El estudiante no tiene email registrado';
                            $msg_type = 'warning';
                        } else {
                            // Determinar tipo de email y documento según estado
                            $tipoEmail = ($inscripcion['estado'] === 'Completado' || $inscripcion['estado'] === 'Aprobado')
                                ? \VERUMax\Services\EmailService::TYPE_CERTIFICADO
                                : \VERUMax\Services\EmailService::TYPE_CONSTANCIA;

                            // Determinar nombre del tipo de documento según estado (normalizado)
                            $tipos_documento = [
                                'preinscrito' => 'Constancia de Preinscripción',
                                'inscrito' => 'Constancia de Inscripción',
                                'inscripto' => 'Constancia de Inscripción',
                                'en curso' => 'Constancia de Alumno Regular',
                                'finalizado' => 'Certificado de Finalización',
                                'aprobado' => 'Certificado de Aprobación',
                                'completado' => 'Certificado',
                                'desaprobado' => 'Constancia de Cursado',
                                'abandonado' => 'Constancia de Cursado',
                                'suspendido' => 'Constancia de Cursado'
                            ];
                            $estado_normalizado = strtolower(trim($inscripcion['estado']));
                            $tipo_documento = $tipos_documento[$estado_normalizado] ?? 'Constancia';

                            // Construir URL del portal institucional
                            $urlPortal = "https://{$slug}.verumax.com/";

                            $variables = [
                                'nombre_curso' => $inscripcion['nombre_curso'],
                                'url_portal' => $urlPortal,
                                'tipo_documento' => $tipo_documento
                            ];

                            $resultEmail = \VERUMax\Services\EmailService::enviarIndividual(
                                $instance['id_instancia'],
                                $tipoEmail,
                                $inscripcion['email'],
                                $inscripcion['nombre_completo'],
                                $variables
                            );

                            if ($resultEmail['success']) {
                                $msg_texto = 'Notificación reenviada correctamente a ' . $inscripcion['email'];
                                $msg_type = 'success';
                            } else {
                                $msg_texto = 'Error al enviar: ' . ($resultEmail['error'] ?? 'desconocido');
                                $msg_type = 'error';
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $msg_texto = 'Error: ' . $e->getMessage();
                    $msg_type = 'error';
                }
            }

            $redirect_url = "?modulo=certificatum&tab=inscripciones&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;

        case 'notificar_evaluacion_estudiante':
            $id_inscripcion = $_POST['id_inscripcion'] ?? 0;
            $id_evaluatio = $_POST['id_evaluatio'] ?? 0;
            $msg_texto = 'Error: datos incompletos';
            $msg_type = 'error';

            if ($id_inscripcion && $id_evaluatio) {
                $resultado_notif = notificarEvaluacionDisponible(
                    $id_evaluatio,
                    $instance['id_instancia'],
                    [$id_inscripcion]
                );

                if ($resultado_notif['success']) {
                    $msg_texto = $resultado_notif['mensaje'];
                    $msg_type = 'success';
                } else {
                    $msg_texto = $resultado_notif['mensaje'];
                    $msg_type = 'error';
                }
            }

            $redirect_url = "?modulo=certificatum&tab=inscripciones&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;

        case 'notificar_evaluacion_masivo':
            // Envío masivo de notificación de evaluación usando SendGrid personalizations
            $id_evaluatio = $_POST['id_evaluatio'] ?? 0;
            $ids_inscripciones_raw = $_POST['ids_inscripciones'] ?? '';
            $msg_texto = 'Error: datos incompletos';
            $msg_type = 'error';

            if ($id_evaluatio && !empty($ids_inscripciones_raw)) {
                // Parsear IDs de inscripciones (vienen separados por coma)
                $ids_inscripciones = array_filter(array_map('intval', explode(',', $ids_inscripciones_raw)));

                if (!empty($ids_inscripciones)) {
                    $resultado_notif = notificarEvaluacionDisponible(
                        $id_evaluatio,
                        $instance['id_instancia'],
                        $ids_inscripciones
                    );

                    if ($resultado_notif['success']) {
                        $enviados = $resultado_notif['enviados'] ?? count($ids_inscripciones);
                        $sin_email = $resultado_notif['sin_email'] ?? 0;
                        $msg_texto = "Notificación enviada a {$enviados} estudiante(s)";
                        if ($sin_email > 0) {
                            $msg_texto .= " ({$sin_email} sin email)";
                        }
                        $msg_type = 'success';
                    } else {
                        $msg_texto = $resultado_notif['mensaje'] ?? 'Error al enviar notificaciones';
                        $msg_type = 'error';
                    }
                } else {
                    $msg_texto = 'No se seleccionaron inscripciones válidas';
                }
            }

            $redirect_url = "?modulo=certificatum&tab=inscripciones&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;

        case 'notificar_certificado_disponible':
            $id_inscripcion = $_POST['id_inscripcion'] ?? 0;
            $msg_texto = 'Error: inscripción no encontrada';
            $msg_type = 'error';

            if ($id_inscripcion) {
                try {
                    $pdo_certifi = getCertDBConnection();

                    // Obtener datos de la inscripción
                    $stmt = $pdo_certifi->prepare("
                        SELECT i.*, m.nombre_completo, m.email, m.identificador_principal as dni, c.nombre_curso, c.codigo_curso
                        FROM verumax_academi.inscripciones i
                        JOIN verumax_nexus.miembros m ON i.id_miembro = m.id_miembro
                        JOIN verumax_academi.cursos c ON i.id_curso = c.id_curso
                        WHERE i.id_inscripcion = :id AND i.estado = 'Aprobado'
                    ");
                    $stmt->execute([':id' => $id_inscripcion]);
                    $inscripcion = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$inscripcion) {
                        $msg_texto = 'La inscripción no existe o no está en estado Aprobado';
                        $msg_type = 'error';
                    } elseif (empty($inscripcion['email'])) {
                        $msg_texto = 'El estudiante no tiene email registrado';
                        $msg_type = 'warning';
                    } else {
                        // Construir URL del portal
                        $urlPortal = "https://{$slug}.verumax.com/";

                        $variables = [
                            'nombre_curso' => $inscripcion['nombre_curso'],
                            'url_portal' => $urlPortal,
                            'tipo_documento' => 'Certificado de Aprobación'
                        ];

                        $resultEmail = \VERUMax\Services\EmailService::enviarIndividual(
                            $instance['id_instancia'],
                            \VERUMax\Services\EmailService::TYPE_CERTIFICADO,
                            $inscripcion['email'],
                            $inscripcion['nombre_completo'],
                            $variables,
                            'certificado_disponible'
                        );

                        if ($resultEmail['success']) {
                            $msg_texto = 'Notificación de certificado enviada a ' . $inscripcion['email'];
                            $msg_type = 'success';
                        } else {
                            $msg_texto = 'Error al enviar: ' . ($resultEmail['error'] ?? 'desconocido');
                            $msg_type = 'error';
                        }
                    }
                } catch (\Exception $e) {
                    $msg_texto = 'Error: ' . $e->getMessage();
                    $msg_type = 'error';
                }
            }

            $redirect_url = "?modulo=certificatum&tab=inscripciones&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;

        case 'cargar_inscripciones_csv':
            if (isset($_FILES['archivo_inscripciones_csv']) && $_FILES['archivo_inscripciones_csv']['error'] === UPLOAD_ERR_OK) {
                $resultado = procesarArchivoInscripciones($_FILES['archivo_inscripciones_csv'], $instance['id_instancia']);
                $mensaje = "Inscripciones cargadas: {$resultado['inscripciones_insertadas']} nuevas, {$resultado['inscripciones_actualizadas']} actualizadas";
                if (!empty($resultado['errores'])) {
                    $mensaje .= ". Errores: " . count($resultado['errores']);
                    $tipo_mensaje = 'warning';
                }
            } else {
                $mensaje = 'Error al subir el archivo CSV';
                $tipo_mensaje = 'error';
            }
            $active_tab = 'inscripciones';
            break;

        case 'cargar_inscripciones_texto':
            if (!empty($_POST['texto_inscripciones_csv'])) {
                $resultado = \VERUMax\Services\InscripcionService::importarDesdeTexto(
                    $instance['id_instancia'],
                    $_POST['texto_inscripciones_csv']
                );
                $mensaje = "Inscripciones cargadas: {$resultado['insertados']} nuevas, {$resultado['actualizados']} actualizadas";
                if (!empty($resultado['errores'])) {
                    $mensaje .= ". Errores: " . count($resultado['errores']);
                    $tipo_mensaje = 'warning';
                }
            } else {
                $errores[] = 'El texto está vacío';
            }
            $active_tab = 'inscripciones';
            break;

        // ============== GESTIÓN DE DOCENTES ==============
        case 'crear_docente':
            $extras_docente = [
                'domicilio_ciudad' => $_POST['domicilio_ciudad'] ?? null,
                'domicilio_provincia' => $_POST['domicilio_provincia'] ?? null,
                'domicilio_codigo_postal' => $_POST['domicilio_codigo_postal'] ?? null,
                'profesion' => $_POST['profesion'] ?? null,
                'lugar_trabajo' => $_POST['lugar_trabajo'] ?? null,
                'cargo' => $_POST['cargo'] ?? null,
            ];
            $resultado_doc = crearDocente(
                $instance['id_instancia'],
                $_POST['dni'],
                $_POST['nombre'] ?? '',
                $_POST['apellido'] ?? '',
                $_POST['email'] ?? '',
                $_POST['pais'] ?? 'AR',
                $_POST['especialidad'] ?? '',
                $_POST['titulo'] ?? '',
                $_POST['genero'] ?? 'Prefiero no especificar',
                $extras_docente
            );

            // Si requiere confirmación por diferencias, mostrar modal
            if (!empty($resultado_doc['requiere_confirmacion'])) {
                $mostrar_modal_diferencias = true;
                $datos_diferencias = $resultado_doc;
                $active_tab = 'docentes';
                break; // No redirigir, mostrar modal
            }

            if ($resultado_doc['success']) {
                $msg_texto = $resultado_doc['mensaje'];
                $msg_type = 'success';
            } else {
                $msg_texto = $resultado_doc['mensaje'];
                $msg_type = 'error';
            }
            $redirect_url = "?modulo=certificatum&tab=docentes&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;

        case 'confirmar_merge_docente':
            $valores_seleccionados = [];
            // Recoger valores seleccionados (pueden ser 'existente' o 'nuevo')
            foreach (['nombre', 'apellido', 'email', 'pais'] as $campo) {
                $seleccion = $_POST["seleccion_$campo"] ?? 'existente';
                if ($seleccion === 'nuevo') {
                    $valores_seleccionados[$campo] = $_POST["nuevo_$campo"] ?? '';
                }
                // Si es 'existente', no lo agregamos y se mantiene el valor actual
            }

            $resultado_merge = confirmarMergeEstudianteDocente(
                $_POST['id_miembro'],
                $valores_seleccionados,
                $_POST['especialidad'] ?? '',
                $_POST['titulo'] ?? ''
            );
            $msg_texto = $resultado_merge['mensaje'];
            $msg_type = $resultado_merge['success'] ? 'success' : 'error';
            $redirect_url = "?modulo=certificatum&tab=docentes&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;

        case 'editar_docente':
            $extras_docente = [
                'domicilio_ciudad' => $_POST['domicilio_ciudad'] ?? null,
                'domicilio_provincia' => $_POST['domicilio_provincia'] ?? null,
                'domicilio_codigo_postal' => $_POST['domicilio_codigo_postal'] ?? null,
                'profesion' => $_POST['profesion'] ?? null,
                'lugar_trabajo' => $_POST['lugar_trabajo'] ?? null,
                'cargo' => $_POST['cargo'] ?? null,
            ];
            $resultado_doc = actualizarDocente(
                $_POST['id_docente'],
                $_POST['dni'],
                $_POST['nombre'] ?? '',
                $_POST['apellido'] ?? '',
                $_POST['email'] ?? '',
                $_POST['pais'] ?? 'AR',
                $_POST['especialidad'] ?? '',
                $_POST['titulo'] ?? '',
                $_POST['genero'] ?? 'Prefiero no especificar',
                $extras_docente
            );
            $msg_texto = $resultado_doc['mensaje'];
            $msg_type = $resultado_doc['success'] ? 'success' : 'error';
            $redirect_url = "?modulo=certificatum&tab=docentes&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;

        case 'eliminar_docente':
            $resultado_doc = eliminarDocente($_POST['id_docente']);
            $msg_texto = $resultado_doc['mensaje'] ?? 'Operación completada';
            $msg_type = $resultado_doc['success'] ? 'success' : 'error';
            $redirect_url = "?modulo=certificatum&tab=docentes&msg=" . urlencode($msg_texto) . "&msg_type=" . $msg_type;
            echo "<script>window.location.href = " . json_encode($redirect_url) . ";</script>";
            exit;

        case 'cargar_docentes':
            if (!empty($_POST['texto_docentes'])) {
                $resultado = procesarSoloDocentes($_POST['texto_docentes'], $instance['id_instancia']);
                $mensaje = "Docentes cargados: {$resultado['docentes_insertados']} nuevos, {$resultado['docentes_actualizados']} actualizados";
                if (!empty($resultado['errores'])) {
                    $mensaje .= ". Errores: " . count($resultado['errores']);
                    $tipo_mensaje = 'warning';
                }
            } else {
                $mensaje = 'El texto está vacío';
                $tipo_mensaje = 'error';
            }
            $active_tab = 'docentes';
            break;

        case 'cargar_docentes_archivo':
            if (isset($_FILES['archivo_docentes']) && $_FILES['archivo_docentes']['error'] === UPLOAD_ERR_OK) {
                $resultado = procesarArchivoDocentes($_FILES['archivo_docentes'], $instance['id_instancia']);
                $mensaje = "Docentes cargados: {$resultado['docentes_insertados']} nuevos, {$resultado['docentes_actualizados']} actualizados";
                if (!empty($resultado['errores'])) {
                    $mensaje .= ". Errores: " . count($resultado['errores']);
                    $tipo_mensaje = 'warning';
                } else {
                    $tipo_mensaje = 'success';
                }
            } else {
                $mensaje = 'Error al subir el archivo';
                $tipo_mensaje = 'error';
            }
            $active_tab = 'docentes';
            break;

        // ============== GESTIÓN DE PARTICIPACIONES DOCENTE ==============
        case 'crear_participacion':
            if (!empty($_POST['id_docente_participacion']) && !empty($_POST['id_curso_participacion'])) {
                $notificar = isset($_POST['notificar_docente']) && $_POST['notificar_docente'] == '1';
                $resultado_part = crearParticipacionDocente(
                    $_POST['id_docente_participacion'],
                    $_POST['id_curso_participacion'],
                    $_POST['rol_participacion'] ?? 'docente',
                    $_POST['titulo_participacion'] ?? '',
                    $_POST['fecha_inicio_participacion'] ?? null,
                    $_POST['fecha_fin_participacion'] ?? null,
                    $instance['id_instancia'],
                    $notificar
                );
                $mensaje = $resultado_part['mensaje'];
                $tipo_mensaje = $resultado_part['success'] ? 'success' : 'error';
            } else {
                $mensaje = 'Debe seleccionar un docente y un curso';
                $tipo_mensaje = 'error';
            }
            $active_tab = 'docentes';
            break;

        case 'actualizar_participacion':
            if (!empty($_POST['id_participacion'])) {
                $notificar = isset($_POST['notificar_docente']) && $_POST['notificar_docente'] == '1';
                $datos = [
                    'rol' => $_POST['rol_participacion'] ?? 'docente',
                    'estado' => $_POST['estado_participacion'] ?? 'Asignado',
                    'titulo_participacion' => $_POST['titulo_participacion'] ?? '',
                    'fecha_inicio' => $_POST['fecha_inicio_participacion'] ?? null,
                    'fecha_fin' => $_POST['fecha_fin_participacion'] ?? null
                ];
                $resultado_part = actualizarParticipacionDocente(
                    $_POST['id_participacion'],
                    $datos,
                    $notificar
                );
                $mensaje = $resultado_part['mensaje'];
                $tipo_mensaje = $resultado_part['success'] ? 'success' : 'error';
            } else {
                $mensaje = 'ID de participación no válido';
                $tipo_mensaje = 'error';
            }
            $active_tab = 'docentes';
            break;

        case 'eliminar_participacion':
            $resultado_part = eliminarParticipacionDocente($_POST['id_participacion']);
            $mensaje = $resultado_part['mensaje'];
            $tipo_mensaje = $resultado_part['success'] ? 'success' : 'error';
            $active_tab = 'asignaciones';
            break;

        case 'reenviar_notificacion_docente':
            if (!empty($_POST['id_participacion'])) {
                $tipo_email = $_POST['tipo_email'] ?? 'asignado'; // 'asignado' o 'completado'

                if ($tipo_email === 'completado') {
                    $resultado = enviarEmailCertificadoDocente($_POST['id_participacion'], $instance['id_instancia']);
                } else {
                    $resultado = enviarEmailDocenteAsignado($_POST['id_participacion'], $instance['id_instancia']);
                }

                if ($resultado['success']) {
                    $mensaje = 'Notificación enviada correctamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = 'Error al enviar notificación: ' . ($resultado['error'] ?? 'desconocido');
                    $tipo_mensaje = 'error';
                }
            } else {
                $mensaje = 'ID de participación no válido';
                $tipo_mensaje = 'error';
            }
            $active_tab = 'asignaciones';
            break;

        // =============================================
        // Gestión de Evaluaciones (Probatio)
        // =============================================

        case 'crear_evaluacion':
            $resultado_eval = crearEvaluacion($instance['id_instancia'], $_POST);
            $mensaje = $resultado_eval['mensaje'];
            $tipo_mensaje = $resultado_eval['success'] ? 'success' : 'error';
            $active_tab = 'evaluaciones';
            break;

        case 'actualizar_evaluacion':
            $resultado_eval = actualizarEvaluacion($_POST['id_evaluatio'], $_POST);
            $mensaje = $resultado_eval['mensaje'];
            $tipo_mensaje = $resultado_eval['success'] ? 'success' : 'error';

            // Si la actualización fue exitosa y se pidió notificar
            if ($resultado_eval['success'] && !empty($_POST['notificar_estudiantes'])) {
                $resultado_notif = notificarEvaluacionDisponible(
                    $_POST['id_evaluatio'],
                    $instance['id_instancia']
                );
                if ($resultado_notif['success']) {
                    $mensaje .= ' | ' . $resultado_notif['mensaje'];
                } else {
                    $mensaje .= ' | Error al notificar: ' . $resultado_notif['mensaje'];
                    $tipo_mensaje = 'warning';
                }
            }

            $active_tab = 'evaluaciones';
            break;

        case 'eliminar_evaluacion':
            $resultado_eval = eliminarEvaluacion($_POST['id_evaluatio']);
            $mensaje = $resultado_eval['mensaje'];
            $tipo_mensaje = $resultado_eval['success'] ? 'success' : 'error';
            $active_tab = 'evaluaciones';
            break;

        case 'duplicar_evaluacion':
            $resultado_eval = duplicarEvaluacion($_POST['id_evaluatio']);
            $mensaje = $resultado_eval['mensaje'];
            $tipo_mensaje = $resultado_eval['success'] ? 'success' : 'error';
            $active_tab = 'evaluaciones';
            break;

        // Gestión de Preguntas
        case 'crear_pregunta':
            $opciones = [];
            if (isset($_POST['opcion_letra']) && is_array($_POST['opcion_letra'])) {
                foreach ($_POST['opcion_letra'] as $i => $letra) {
                    $opciones[] = [
                        'letra' => $letra,
                        'texto' => $_POST['opcion_texto'][$i] ?? '',
                        'es_correcta' => in_array($letra, $_POST['opcion_correcta'] ?? []),
                        'feedback' => $_POST['opcion_feedback'][$i] ?? ''
                    ];
                }
            }
            $_POST['opciones'] = $opciones;
            $resultado_preg = crearPregunta($_POST['id_evaluatio'], $_POST);
            $mensaje = $resultado_preg['mensaje'];
            $tipo_mensaje = $resultado_preg['success'] ? 'success' : 'error';
            // Guardar redirect para después
            $redirect_to_preguntas = $_POST['id_evaluatio'] ?? 0;
            $active_tab = 'evaluaciones';
            break;

        case 'actualizar_pregunta':
            $opciones = [];
            if (isset($_POST['opcion_letra']) && is_array($_POST['opcion_letra'])) {
                foreach ($_POST['opcion_letra'] as $i => $letra) {
                    $opciones[] = [
                        'letra' => $letra,
                        'texto' => $_POST['opcion_texto'][$i] ?? '',
                        'es_correcta' => in_array($letra, $_POST['opcion_correcta'] ?? []),
                        'feedback' => $_POST['opcion_feedback'][$i] ?? ''
                    ];
                }
            }
            $_POST['opciones'] = $opciones;
            $resultado_preg = actualizarPregunta($_POST['id_quaestio'], $_POST);
            $mensaje = $resultado_preg['mensaje'];
            $tipo_mensaje = $resultado_preg['success'] ? 'success' : 'error';
            // Guardar redirect para después
            $redirect_to_preguntas = $_POST['id_evaluatio'] ?? 0;
            $active_tab = 'evaluaciones';
            break;

        case 'eliminar_pregunta':
            $resultado_preg = eliminarPregunta($_POST['id_quaestio']);
            $mensaje = $resultado_preg['mensaje'];
            $tipo_mensaje = $resultado_preg['success'] ? 'success' : 'error';
            // Guardar redirect para después
            $redirect_to_preguntas = $_POST['id_evaluatio'] ?? 0;
            $active_tab = 'evaluaciones';
            break;

        case 'resetear_sesion':
            $resultado_reset = resetearSesion($_POST['id_sessio']);
            $mensaje = $resultado_reset['mensaje'];
            $tipo_mensaje = $resultado_reset['success'] ? 'success' : 'error';
            $active_tab = 'evaluaciones';
            break;

        case 'eliminar_sesion':
            $resultado_del = eliminarSesion($_POST['id_sessio']);
            $mensaje = $resultado_del['mensaje'];
            $tipo_mensaje = $resultado_del['success'] ? 'success' : 'error';
            $active_tab = 'evaluaciones';
            break;
    }
}

// Variables para modal de diferencias (inicializar si no existen)
$mostrar_modal_diferencias = $mostrar_modal_diferencias ?? false;
$datos_diferencias = $datos_diferencias ?? [];

// Obtener datos para las pestañas de gestión
$buscar = $_GET['buscar'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

$estudiantes = obtenerEstudiantes($instance['id_instancia'], $buscar);
$docentes = obtenerDocentes($instance['id_instancia'], $buscar);
$cursos = obtenerCursos($instance['id_instancia'], false, $buscar);
$inscripciones = obtenerInscripciones($instance['id_instancia'], $filtro_estado, $buscar);

// Calcular disponibilidad de certificados para cada inscripción aprobada
$cert_config_global = \VERUMax\Services\InstitutionService::getConfig($instance['slug'] ?? '');
foreach ($inscripciones as &$insc) {
    $insc['certificado_disponible_ahora'] = false;
    if ($insc['estado'] === 'Aprobado' && !empty($insc['fecha_finalizacion'])) {
        $disponibilidad = \VERUMax\Services\InstitutionService::calcularDisponibilidadCertificado(
            $insc['fecha_finalizacion'],
            $insc,
            $cert_config_global
        );
        $insc['certificado_disponible_ahora'] = $disponibilidad['disponible'];
    }
}
unset($insc); // Romper referencia

$filtro_estado_asignacion = $_GET['estado_asignacion'] ?? '';
$asignaciones_docentes = obtenerParticipacionesDocentes($instance['id_instancia'], $filtro_estado_asignacion, $buscar);

// Evaluaciones (Probatio)
$filtro_estado_eval = $_GET['estado_eval'] ?? '';
$evaluaciones = obtenerEvaluaciones($instance['id_instancia'], $filtro_estado_eval, $buscar);

// Logs de validaciones QR
$logs_validaciones = [];
$stats_validaciones = [];
try {
    $pdo_cert = \VERUMax\Services\DatabaseService::get('certificatum');

    // Obtener últimos 100 logs de esta institución
    $stmt_logs = $pdo_cert->prepare("
        SELECT
            id_log,
            codigo_validacion,
            ip_address,
            user_agent,
            referer,
            fecha_consulta,
            tipo_documento,
            exitoso
        FROM log_validaciones
        WHERE institucion = :institucion
        ORDER BY fecha_consulta DESC
        LIMIT 100
    ");
    $stmt_logs->execute(['institucion' => $slug]);
    $logs_validaciones = $stmt_logs->fetchAll(PDO::FETCH_ASSOC);

    // Obtener estadísticas
    $stats_validaciones = \VERUMax\Services\CertificateService::getValidationStats($slug, 30);
} catch (Exception $e) {
    // Si la tabla no existe aún, no hay logs
    error_log("Log validaciones: " . $e->getMessage());
}

// Logs de accesos a certificados (vistas y descargas)
$logs_accesos = [];
$stats_accesos = [];
try {
    $logs_accesos = \VERUMax\Services\CertificateService::getUltimosAccesos($slug, 100, 30);
    $stats_accesos = \VERUMax\Services\CertificateService::getAccesosStats($slug, 30);
} catch (Exception $e) {
    // Si la tabla no existe aún, no hay logs
    error_log("Log accesos: " . $e->getMessage());
}
$ver_preguntas = isset($_GET['preguntas']) ? (int)$_GET['preguntas'] : 0;
$evaluacion_actual = null;
$preguntas_actual = [];
if ($ver_preguntas > 0) {
    $active_tab = 'evaluaciones'; // Mantener la solapa activa
    $evaluacion_actual = obtenerEvaluacionPorId($ver_preguntas);
    if ($evaluacion_actual) {
        $preguntas_actual = obtenerPreguntasAdmin($ver_preguntas);
    }
}

// Sesiones de evaluación
$ver_sesiones = isset($_GET['sesiones']) ? (int)$_GET['sesiones'] : 0;
$evaluacion_sesiones = null;
$sesiones_lista = [];
if ($ver_sesiones > 0) {
    $active_tab = 'evaluaciones'; // Mantener la solapa activa
    $evaluacion_sesiones = obtenerEvaluacionPorId($ver_sesiones);
    if ($evaluacion_sesiones) {
        $sesiones_lista = obtenerSesionesEvaluacion($ver_sesiones);
    }
}

// Estadísticas de evaluación
$ver_estadisticas = isset($_GET['estadisticas']) ? (int)$_GET['estadisticas'] : 0;
$evaluacion_stats = null;
$estadisticas_data = [];
$reflexiones_lista = [];
$preguntas_abiertas = [];
$respuestas_abiertas = [];
if ($ver_estadisticas > 0) {
    $active_tab = 'evaluaciones'; // Mantener la solapa activa
    $evaluacion_stats = obtenerEvaluacionPorId($ver_estadisticas);
    if ($evaluacion_stats) {
        // Obtener sesiones para calcular estadísticas
        $sesiones_stats = obtenerSesionesEvaluacion($ver_estadisticas);

        // Calcular estadísticas
        $total_sesiones = count($sesiones_stats);
        $completadas = 0;
        $en_progreso = 0;
        $aprobados = 0;
        $desaprobados = 0;

        // Mapear sesiones por id para lookup rápido
        $sesiones_map = [];
        foreach ($sesiones_stats as $ses) {
            $sesiones_map[$ses['id_sessio']] = $ses;
            if ($ses['estado'] === 'completada') {
                $completadas++;
                if ($ses['aprobado']) {
                    $aprobados++;
                } else {
                    $desaprobados++;
                }
            } else {
                $en_progreso++;
            }

            // Recolectar reflexiones (cierre cualitativo)
            if (!empty($ses['reflexion_final'])) {
                $reflexiones_lista[] = [
                    'nombre' => $ses['nombre_completo'],
                    'email' => $ses['email'],
                    'reflexion' => $ses['reflexion_final'],
                    'fecha' => $ses['fecha_finalizacion'],
                    'aprobado' => $ses['aprobado'],
                    'porcentaje' => $ses['porcentaje'] ?? 0
                ];
            }
        }

        $estadisticas_data = [
            'total_sesiones' => $total_sesiones,
            'completadas' => $completadas,
            'en_progreso' => $en_progreso,
            'aprobados' => $aprobados,
            'desaprobados' => $desaprobados,
            'tasa_completacion' => $total_sesiones > 0 ? round(($completadas / $total_sesiones) * 100, 1) : 0,
            'tasa_aprobacion' => $completadas > 0 ? round(($aprobados / $completadas) * 100, 1) : 0
        ];

        // Cargar preguntas abiertas y sus respuestas
        try {
            $pdo_certifi = getCertDBConnection();

            // Obtener preguntas abiertas de la evaluación
            $stmtPreguntas = $pdo_certifi->prepare("
                SELECT id_quaestio, orden, enunciado
                FROM verumax_academi.quaestiones
                WHERE id_evaluatio = :id_eval AND tipo = 'abierta'
                ORDER BY orden
            ");
            $stmtPreguntas->execute([':id_eval' => $ver_estadisticas]);
            $preguntas_abiertas = $stmtPreguntas->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($preguntas_abiertas)) {
                $ids_preguntas = array_column($preguntas_abiertas, 'id_quaestio');
                $placeholders = implode(',', array_fill(0, count($ids_preguntas), '?'));

                // Obtener respuestas a preguntas abiertas
                $stmtRespuestas = $pdo_certifi->prepare("
                    SELECT r.id_sessio, r.id_quaestio, r.respuestas_seleccionadas, r.created_at
                    FROM verumax_academi.responsa r
                    WHERE r.id_quaestio IN ({$placeholders})
                    ORDER BY r.id_quaestio, r.created_at DESC
                ");
                $stmtRespuestas->execute($ids_preguntas);

                while ($resp = $stmtRespuestas->fetch(PDO::FETCH_ASSOC)) {
                    $sesion = $sesiones_map[$resp['id_sessio']] ?? null;
                    if ($sesion) {
                        $texto = json_decode($resp['respuestas_seleccionadas'], true);
                        $respuestas_abiertas[$resp['id_quaestio']][] = [
                            'nombre' => $sesion['nombre_completo'],
                            'email' => $sesion['email'],
                            'texto' => is_array($texto) ? ($texto[0] ?? '') : $texto,
                            'fecha' => $resp['created_at'],
                            'aprobado' => $sesion['aprobado'] ?? false
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error cargando preguntas abiertas: " . $e->getMessage());
        }
    }
}

// Generar mapa de evaluaciones activas por curso (para inscripciones)
$evaluaciones_activas_por_curso = [];
foreach ($evaluaciones as $eval) {
    if ($eval['estado'] === 'activa') {
        $evaluaciones_activas_por_curso[$eval['id_curso']] = [
            'id_evaluatio' => $eval['id_evaluatio'],
            'nombre' => $eval['nombre'],
            'codigo' => $eval['codigo'],
            'fecha_fin' => $eval['fecha_fin']
        ];
    }
}

// Decodificar redes sociales y estadísticas
$redes = [];
if (!empty($instance['redes_sociales'])) {
    $redes = json_decode($instance['redes_sociales'], true) ?: [];
}

$stats = [];
if (!empty($instance['certificatum_estadisticas'])) {
    $stats = json_decode($instance['certificatum_estadisticas'], true) ?: [];
}
?>

<style>
    .tab-content-cert { display: none; }
    .tab-content-cert.active { display: block; }
    .tab-button-cert.active { border-bottom: 3px solid #3b82f6; color: #3b82f6; font-weight: 600; }
    .editable-row { transition: background-color 0.2s; }
    .editable-row:hover { background-color: #f9fafb; }
    .preview-mode { display: none; }
    .preview-mode.active { display: block; }
</style>

<!-- Sistema de Toast Notification -->
<?php if ($mensaje): ?>
    <script>
        console.log('🔵 Certificatum page message detected');
        window.certificatumPageMessage = {
            mensaje: '<?php echo addslashes($mensaje); ?>',
            tipo: '<?php echo $tipo_mensaje; ?>',
            scrollTo: '<?php echo $scroll_to; ?>'
        };
        console.log('🔵 certificatumPageMessage:', window.certificatumPageMessage);
    </script>
<?php endif; ?>

<!-- Mensajes de resultado (para cargas masivas) -->
<?php if ($resultado && isset($resultado['estudiantes_insertados'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6">
        <h3 class="font-bold mb-2">✓ Datos cargados exitosamente</h3>
        <ul class="text-sm space-y-1">
            <li>Estudiantes procesados: <?php echo ($resultado['estudiantes_insertados'] ?? 0) + ($resultado['estudiantes_actualizados'] ?? 0); ?> (nuevos: <?php echo $resultado['estudiantes_insertados'] ?? 0; ?>, actualizados: <?php echo $resultado['estudiantes_actualizados'] ?? 0; ?>)</li>
            <li>Cursos procesados: <?php echo ($resultado['cursos_insertados'] ?? 0) + ($resultado['cursos_actualizados'] ?? 0); ?> (nuevos: <?php echo $resultado['cursos_insertados'] ?? 0; ?>, actualizados: <?php echo $resultado['cursos_actualizados'] ?? 0; ?>)</li>
            <li>Inscripciones procesadas: <?php echo ($resultado['inscripciones_insertadas'] ?? 0) + ($resultado['inscripciones_actualizadas'] ?? 0); ?> (nuevas: <?php echo $resultado['inscripciones_insertadas'] ?? 0; ?>, actualizadas: <?php echo $resultado['inscripciones_actualizadas'] ?? 0; ?>)</li>
            <?php if (isset($resultado['emails_enviados'])): ?>
            <li>Emails enviados: <?php echo $resultado['emails_enviados']; ?><?php if ($resultado['emails_sin_email'] > 0): ?> (<?php echo $resultado['emails_sin_email']; ?> estudiantes sin email)<?php endif; ?></li>
            <?php endif; ?>
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

<!-- Dashboard de Métricas -->
<?php
// Calcular métricas para el dashboard
$total_estudiantes = count($estudiantes);
$total_docentes = count($docentes);
$total_cursos = count($cursos);
$total_inscripciones = count($inscripciones);
$total_asignaciones = count($asignaciones_docentes);

// Contar estados de inscripciones
$inscripciones_aprobadas = 0;
$inscripciones_en_curso = 0;
$inscripciones_finalizadas = 0;
foreach ($inscripciones as $insc) {
    if (($insc['estado'] ?? '') === 'Aprobado') $inscripciones_aprobadas++;
    if (($insc['estado'] ?? '') === 'En Curso') $inscripciones_en_curso++;
    if (($insc['estado'] ?? '') === 'Finalizado') $inscripciones_finalizadas++;
}
?>
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
            Resumen de Certificatum
        </h2>
        <span class="text-xs text-gray-500">Vista rápida</span>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <!-- Estudiantes -->
        <button onclick="cambiarTabCert('estudiantes')" class="p-4 rounded-lg border-2 border-green-200 bg-green-50 hover:bg-green-100 transition text-left">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="users" class="w-5 h-5 text-green-600"></i>
                <span class="text-sm font-medium text-gray-700">Estudiantes</span>
            </div>
            <p class="text-2xl font-bold text-green-700"><?php echo $total_estudiantes; ?></p>
        </button>

        <!-- Docentes -->
        <button onclick="cambiarTabCert('docentes')" class="p-4 rounded-lg border-2 border-purple-200 bg-purple-50 hover:bg-purple-100 transition text-left">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="graduation-cap" class="w-5 h-5 text-purple-600"></i>
                <span class="text-sm font-medium text-gray-700">Docentes</span>
            </div>
            <p class="text-2xl font-bold text-purple-700"><?php echo $total_docentes; ?></p>
        </button>

        <!-- Cursos -->
        <button onclick="cambiarTabCert('cursos')" class="p-4 rounded-lg border-2 border-blue-200 bg-blue-50 hover:bg-blue-100 transition text-left">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="book-open" class="w-5 h-5 text-blue-600"></i>
                <span class="text-sm font-medium text-gray-700">Cursos</span>
            </div>
            <p class="text-2xl font-bold text-blue-700"><?php echo $total_cursos; ?></p>
        </button>

        <!-- Inscripciones -->
        <button onclick="cambiarTabCert('inscripciones')" class="p-4 rounded-lg border-2 border-orange-200 bg-orange-50 hover:bg-orange-100 transition text-left">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="file-check" class="w-5 h-5 text-orange-600"></i>
                <span class="text-sm font-medium text-gray-700">Inscripciones</span>
            </div>
            <p class="text-2xl font-bold text-orange-700"><?php echo $total_inscripciones; ?></p>
            <p class="text-xs text-orange-600 mt-1"><?php echo $inscripciones_en_curso; ?> en curso</p>
        </button>

        <!-- Aprobados -->
        <button onclick="verAprobados()" class="p-4 rounded-lg border-2 border-emerald-200 bg-emerald-50 hover:bg-emerald-100 transition text-left">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="award" class="w-5 h-5 text-emerald-600"></i>
                <span class="text-sm font-medium text-gray-700">Aprobados</span>
            </div>
            <p class="text-2xl font-bold text-emerald-700"><?php echo $inscripciones_aprobadas; ?></p>
            <p class="text-xs text-emerald-600 mt-1">certificados</p>
        </button>

        <!-- Asignaciones -->
        <button onclick="cambiarTabCert('asignaciones')" class="p-4 rounded-lg border-2 border-indigo-200 bg-indigo-50 hover:bg-indigo-100 transition text-left">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="briefcase" class="w-5 h-5 text-indigo-600"></i>
                <span class="text-sm font-medium text-gray-700">Asignaciones</span>
            </div>
            <p class="text-2xl font-bold text-indigo-700"><?php echo $total_asignaciones; ?></p>
            <p class="text-xs text-indigo-600 mt-1">docentes</p>
        </button>
    </div>
</div>

<!-- Tabs Navigation (5 tabs principales) -->
<div class="bg-white rounded-lg shadow-sm mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex space-x-8 px-6 overflow-x-auto">
            <button onclick="cambiarTabCert('configuracion', this)" class="tab-button-cert active py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                <i data-lucide="settings" class="w-5 h-5 inline mr-2"></i>
                Configuración
            </button>
            <button onclick="cambiarTabCert('personas', this)" class="tab-button-cert py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                <i data-lucide="users" class="w-5 h-5 inline mr-2"></i>
                Personas (<?php echo count($estudiantes) + count($docentes); ?>)
            </button>
            <button onclick="cambiarTabCert('cursos', this)" class="tab-button-cert py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                <i data-lucide="book-open" class="w-5 h-5 inline mr-2"></i>
                Cursos (<?php echo count($cursos); ?>)
            </button>
            <button onclick="cambiarTabCert('matriculas', this)" class="tab-button-cert py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                <i data-lucide="file-check" class="w-5 h-5 inline mr-2"></i>
                Matrículas (<?php echo count($inscripciones) + count($asignaciones_docentes); ?>)
            </button>
            <button onclick="cambiarTabCert('evaluaciones', this)" class="tab-button-cert py-4 text-gray-600 hover:text-blue-600 whitespace-nowrap">
                <i data-lucide="clipboard-check" class="w-5 h-5 inline mr-2"></i>
                Evaluaciones (<?php echo count($evaluaciones); ?>)
            </button>
        </nav>
    </div>

    <!-- Tab Content: Configuración (ex Diseño) -->
    <div id="tab-cert-configuracion" class="tab-content-cert active p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">
            <i data-lucide="file-text" class="w-6 h-6 inline mr-2"></i>
            Contenido de Certificatum
        </h2>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-2">Configuración de branding institucional</p>
                    <p>Las <strong>redes sociales</strong> se configuran en la pestaña <strong>IDENTITAS</strong>, ya que son parte del branding general de tu institución.</p>
                    <p class="mt-2">Aquí configurás el contenido específico del portal de certificados y opcionalmente una paleta de colores propia.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=certificatum" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_diseno">

            <!-- Paleta de Colores -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="palette" class="w-5 h-5"></i>
                    Paleta de Colores de Certificatum
                </h3>

                <!-- Selector: Paleta General vs Propia -->
                <div class="mb-6">
                    <label class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="checkbox" name="certificatum_usar_paleta_general" id="certificatum-usar-paleta-general"
                               value="1"
                               <?php echo ($instance['certificatum_usar_paleta_general'] ?? 1) == 1 ? 'checked' : ''; ?>
                               class="w-5 h-5 text-blue-600 rounded">
                        <div>
                            <div class="font-semibold text-gray-900">
                                <i data-lucide="link" class="w-4 h-4 inline mr-1"></i>
                                Usar paleta general (configurada en GENERAL)
                            </div>
                            <div class="text-sm text-gray-600 mt-1">Los colores se heredan de la configuración global</div>
                        </div>
                    </label>
                </div>

                <!-- Paleta Propia de Certificatum (solo visible si NO usa paleta general) -->
                <div id="certificatum-paleta-propia-section" style="display: <?php echo ($instance['certificatum_usar_paleta_general'] ?? 1) == 1 ? 'none' : 'block'; ?>;">
                    <div class="border-2 border-green-200 bg-green-50 rounded-lg p-6">
                        <h4 class="font-semibold text-green-900 mb-4 flex items-center gap-2">
                            <i data-lucide="paintbrush" class="w-5 h-5"></i>
                            Paleta Propia de Certificatum
                        </h4>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Paleta predefinida</label>
                            <select name="certificatum_paleta_colores_propia" id="certificatum-paleta-select"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                <option value="verde-elegante" <?php echo ($instance['certificatum_paleta_colores_propia'] ?? '') === 'verde-elegante' ? 'selected' : ''; ?>>
                                    🌿 Verde Elegante (Educación, Salud)
                                </option>
                                <option value="azul-profesional" <?php echo ($instance['certificatum_paleta_colores_propia'] ?? '') === 'azul-profesional' ? 'selected' : ''; ?>>
                                    💼 Azul Profesional (Corporativo, Tecnología)
                                </option>
                                <option value="morado-creativo" <?php echo ($instance['certificatum_paleta_colores_propia'] ?? '') === 'morado-creativo' ? 'selected' : ''; ?>>
                                    🎨 Morado Creativo (Arte, Diseño)
                                </option>
                                <option value="naranja-energetico" <?php echo ($instance['certificatum_paleta_colores_propia'] ?? '') === 'naranja-energetico' ? 'selected' : ''; ?>>
                                    ⚡ Naranja Energético (Deportes, Juventud)
                                </option>
                                <option value="rojo-institucional" <?php echo ($instance['certificatum_paleta_colores_propia'] ?? '') === 'rojo-institucional' ? 'selected' : ''; ?>>
                                    🏛️ Rojo Institucional (Gobierno, Legal)
                                </option>
                                <option value="gris-minimalista" <?php echo ($instance['certificatum_paleta_colores_propia'] ?? '') === 'gris-minimalista' ? 'selected' : ''; ?>>
                                    ⬛ Gris Minimalista (Arquitectura, Lujo)
                                </option>
                                <option value="personalizado" <?php echo ($instance['certificatum_paleta_colores_propia'] ?? 'personalizado') === 'personalizado' ? 'selected' : ''; ?>>
                                    🎯 Personalizado
                                </option>
                            </select>
                        </div>

                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color Primario</label>
                                <div class="flex gap-2">
                                    <input type="color" name="certificatum_color_primario_propio" id="certificatum-color-primario"
                                           value="<?php echo htmlspecialchars($instance['certificatum_color_primario_propio'] ?? '#2E7D32'); ?>"
                                           class="h-10 w-20 border border-gray-300 rounded-lg">
                                    <input type="text" id="certificatum-color-primario-text"
                                           value="<?php echo htmlspecialchars($instance['certificatum_color_primario_propio'] ?? '#2E7D32'); ?>" readonly
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color Secundario</label>
                                <div class="flex gap-2">
                                    <input type="color" name="certificatum_color_secundario_propio" id="certificatum-color-secundario"
                                           value="<?php echo htmlspecialchars($instance['certificatum_color_secundario_propio'] ?? '#1B5E20'); ?>"
                                           class="h-10 w-20 border border-gray-300 rounded-lg">
                                    <input type="text" id="certificatum-color-secundario-text"
                                           value="<?php echo htmlspecialchars($instance['certificatum_color_secundario_propio'] ?? '#1B5E20'); ?>" readonly
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color de Acento</label>
                                <div class="flex gap-2">
                                    <input type="color" name="certificatum_color_acento_propio" id="certificatum-color-acento"
                                           value="<?php echo htmlspecialchars($instance['certificatum_color_acento_propio'] ?? '#66BB6A'); ?>"
                                           class="h-10 w-20 border border-gray-300 rounded-lg">
                                    <input type="text" id="certificatum-color-acento-text"
                                           value="<?php echo htmlspecialchars($instance['certificatum_color_acento_propio'] ?? '#66BB6A'); ?>" readonly
                                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido principal con tabs de idioma -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-900 mb-4">Contenido Principal (Traducible)</h3>

                <?php
                // Determinar el idioma activo (de POST después de guardar, o el primero por defecto)
                $idioma_tab_activo = $_POST['idioma_tab_activo'] ?? $idiomas_habilitados[0] ?? 'es_AR';
                ?>
                <input type="hidden" name="idioma_tab_activo" id="idioma_tab_activo" value="<?php echo htmlspecialchars($idioma_tab_activo); ?>">

                <?php if (count($idiomas_habilitados) > 1): ?>
                <!-- Tabs de idioma -->
                <div class="border-b border-gray-200 mb-4">
                    <nav class="flex gap-2" aria-label="Tabs de idioma">
                        <?php foreach ($idiomas_habilitados as $idx => $codigo_idioma): ?>
                            <button type="button"
                                    onclick="cambiarTabIdiomaCert('<?php echo $codigo_idioma; ?>')"
                                    id="tab-btn-<?php echo $codigo_idioma; ?>"
                                    class="tab-btn-idioma px-4 py-2 text-sm font-medium rounded-t-lg border-b-2 transition <?php echo $codigo_idioma === $idioma_tab_activo ? 'border-blue-500 text-blue-600 bg-blue-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>">
                                <?php echo $nombres_idiomas[$codigo_idioma] ?? $codigo_idioma; ?>
                                <?php if ($codigo_idioma === $idioma_default): ?>
                                    <span class="ml-1 text-xs text-gray-400">(default)</span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </nav>
                </div>
                <?php endif; ?>

                <!-- Contenido por idioma -->
                <?php foreach ($idiomas_habilitados as $idx => $codigo_idioma):
                    $descripcion_valor = $traducciones_certificatum['certificatum_descripcion'][$codigo_idioma]
                        ?? ($codigo_idioma === $idioma_default ? ($instance['certificatum_descripcion'] ?? '') : '');
                    $cta_valor = $traducciones_certificatum['certificatum_cta_texto'][$codigo_idioma]
                        ?? ($codigo_idioma === $idioma_default ? ($instance['certificatum_cta_texto'] ?? 'Ver mis certificados') : '');
                ?>
                <div id="tab-content-<?php echo $codigo_idioma; ?>"
                     class="tab-content-idioma space-y-4 <?php echo $codigo_idioma === $idioma_tab_activo ? '' : 'hidden'; ?>">

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Descripción del portal
                                <span class="text-xs text-gray-400 ml-1">(<?php echo $codigo_idioma; ?>)</span>
                            </label>
                            <div class="flex gap-2">
                                <?php if ($instance['ia_habilitada'] ?? 0): ?>
                                    <?php if ($codigo_idioma === $idioma_default): ?>
                                        <button type="button"
                                                onclick="autocompletarDescripcionCertificatum('<?php echo $codigo_idioma; ?>')"
                                                class="px-2 py-1 text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 rounded border border-purple-300 transition"
                                                title="Generar con IA">
                                            <i data-lucide="sparkles" class="w-3 h-3 inline"></i> Generar
                                        </button>
                                    <?php else: ?>
                                        <button type="button"
                                                onclick="traducirCampoCertificatum('certificatum_descripcion', '<?php echo $idioma_default; ?>', '<?php echo $codigo_idioma; ?>')"
                                                class="px-2 py-1 text-xs bg-green-100 hover:bg-green-200 text-green-700 rounded border border-green-300 transition"
                                                title="Traducir desde <?php echo $nombres_idiomas[$idioma_default] ?? $idioma_default; ?>">
                                            <i data-lucide="languages" class="w-3 h-3 inline"></i> Traducir
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <textarea name="certificatum_descripcion[<?php echo $codigo_idioma; ?>]"
                                  id="certificatum_descripcion_<?php echo $codigo_idioma; ?>"
                                  rows="3"
                                  placeholder="<?php echo $codigo_idioma === 'pt_BR' ? 'Ex: Acesse seus certificados, constâncias e registro acadêmico completo...' : 'Ej: Accede a tus certificados, constancias y registro académico completo...'; ?>"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($descripcion_valor); ?></textarea>
                        <p class="mt-1 text-sm text-gray-500">Texto que aparece debajo del título</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Texto del botón
                                <span class="text-xs text-gray-400 ml-1">(<?php echo $codigo_idioma; ?>)</span>
                            </label>
                            <?php if (($instance['ia_habilitada'] ?? 0) && $codigo_idioma !== $idioma_default): ?>
                                <button type="button"
                                        onclick="traducirCampoCertificatum('certificatum_cta_texto', '<?php echo $idioma_default; ?>', '<?php echo $codigo_idioma; ?>')"
                                        class="px-2 py-1 text-xs bg-green-100 hover:bg-green-200 text-green-700 rounded border border-green-300 transition"
                                        title="Traducir desde <?php echo $nombres_idiomas[$idioma_default] ?? $idioma_default; ?>">
                                    <i data-lucide="languages" class="w-3 h-3 inline"></i> Traducir
                                </button>
                            <?php endif; ?>
                        </div>
                        <input type="text"
                               name="certificatum_cta_texto[<?php echo $codigo_idioma; ?>]"
                               id="certificatum_cta_texto_<?php echo $codigo_idioma; ?>"
                               value="<?php echo htmlspecialchars($cta_valor); ?>"
                               placeholder="<?php echo $codigo_idioma === 'pt_BR' ? 'Ex: Ver meus certificados' : 'Ej: Ver mis certificados'; ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>


            <!-- Estadísticas -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <h3 class="font-semibold text-gray-900">Estadísticas</h3>
                        <?php if ($instance['ia_habilitada'] ?? 0): ?>
                            <button type="button"
                                    onclick="generarStatsCertificatumConIA()"
                                    class="px-2 py-1 text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 rounded border border-purple-300 transition"
                                    title="Generar todas las estadísticas con IA">
                                <i data-lucide="sparkles" class="w-3 h-3 inline"></i> IA (grupo)
                            </button>
                        <?php endif; ?>
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="mostrar_stats" <?php echo ($instance['certificatum_mostrar_stats'] ?? 1) ? 'checked' : ''; ?>
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Mostrar estadísticas</span>
                    </label>
                </div>

                <p class="text-sm text-gray-600 mb-4">Números impactantes que aparecen en la página de certificados</p>

                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Certificados Emitidos</label>
                        <input type="text" name="stats_certificados" id="stats_certificados"
                               value="<?php echo htmlspecialchars($stats['certificados_emitidos'] ?? ''); ?>"
                               placeholder="Ej: 500+"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estudiantes</label>
                        <input type="text" name="stats_estudiantes" id="stats_estudiantes"
                               value="<?php echo htmlspecialchars($stats['estudiantes'] ?? ''); ?>"
                               placeholder="Ej: 300+"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cursos</label>
                        <input type="text" name="stats_cursos" id="stats_cursos"
                               value="<?php echo htmlspecialchars($stats['cursos'] ?? ''); ?>"
                               placeholder="Ej: 15+"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Configuración de Certificados Post-Evaluación -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                    Configuración de Certificados Post-Evaluación
                </h3>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 mt-0.5"></i>
                        <div class="text-sm text-amber-800">
                            <p>Cuando un estudiante aprueba una evaluación, podés configurar un período de espera antes de que el certificado esté disponible.</p>
                            <p class="mt-1">Durante este período, el estudiante verá un mensaje de "revisión pedagógica en curso" y un countdown en vivo.</p>
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Demora para disponibilidad del certificado
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="demora_certificado_horas"
                                   value="<?php echo htmlspecialchars($instance['demora_certificado_horas'] ?? 24); ?>"
                                   min="0" max="168" step="1"
                                   class="w-24 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-center">
                            <span class="text-gray-600">horas</span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">
                            <strong>0</strong> = Certificado disponible inmediatamente después de aprobar<br>
                            <strong>24</strong> = Certificado disponible 24 horas después (recomendado)<br>
                            <strong>Máximo:</strong> 72 horas (límite de SendGrid para emails programados)
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Comportamiento actual:</p>
                        <?php
                        $demora_actual = $instance['demora_certificado_horas'] ?? 24;
                        if ($demora_actual == 0): ?>
                            <div class="flex items-center gap-2 text-green-700">
                                <i data-lucide="zap" class="w-5 h-5"></i>
                                <span>Certificados disponibles <strong>inmediatamente</strong></span>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-2 text-blue-700">
                                <i data-lucide="hourglass" class="w-5 h-5"></i>
                                <span>Certificados disponibles <strong><?php echo $demora_actual; ?> horas</strong> después de aprobar</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Diseño de Certificatum
                </button>
            </div>
        </form>

    </div>

    <!-- Tab Content: PERSONAS (Estudiantes + Docentes) -->
    <div id="tab-cert-personas" class="tab-content-cert p-6">
        <!-- Sub-tabs de Personas -->
        <div class="flex gap-4 mb-6 border-b">
            <button onclick="cambiarSubTabPersonas('estudiantes')" class="sub-tab-personas active px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                <i data-lucide="users" class="w-4 h-4 inline mr-1"></i>
                Estudiantes (<?php echo count($estudiantes); ?>)
            </button>
            <button onclick="cambiarSubTabPersonas('docentes')" class="sub-tab-personas px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                <i data-lucide="graduation-cap" class="w-4 h-4 inline mr-1"></i>
                Docentes (<?php echo count($docentes); ?>)
            </button>
        </div>

        <!-- Sub-contenido: Estudiantes -->
        <div id="sub-personas-estudiantes" class="sub-content-personas">
        <!-- Header con búsqueda y botones de acción -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i data-lucide="users" class="w-6 h-6 text-green-600"></i>
                Gestionar Estudiantes
                <span class="text-sm font-normal text-gray-500">(<?php echo count($estudiantes); ?> registros)</span>
            </h2>
            <div class="flex flex-wrap gap-2">
                <form method="GET" class="flex gap-2">
                    <input type="hidden" name="modulo" value="certificatum">
                    <input type="text" name="buscar" placeholder="Buscar por DNI o nombre..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-48">
                    <button type="submit" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-200 text-sm">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </form>
                <button onclick="abrirModalNuevoEstudiante()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Nuevo Estudiante
                </button>
                <button onclick="abrirWizardImport('estudiantes')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    Importar
                </button>
                <button onclick="exportarEstudiantesCSV()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Panel de Filtros Avanzados (colapsable) -->
        <div id="panel-filtros-estudiantes" class="hidden mb-4">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-700 flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Filtros Avanzados
                    </h4>
                    <button onclick="limpiarFiltrosEstudiantes()" class="text-sm text-blue-600 hover:text-blue-800">
                        Limpiar filtros
                    </button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                        <select id="filtro-est-estado" onchange="aplicarFiltrosEstudiantes()" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="suspendido">Suspendido</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Cursos inscritos</label>
                        <select id="filtro-est-cursos" onchange="aplicarFiltrosEstudiantes()" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="">Todos</option>
                            <option value="0">Sin cursos</option>
                            <option value="1-3">1 a 3 cursos</option>
                            <option value="4+">4 o más cursos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Progreso</label>
                        <select id="filtro-est-progreso" onchange="aplicarFiltrosEstudiantes()" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="">Todos</option>
                            <option value="aprobados">Con cursos aprobados</option>
                            <option value="en-curso">Con cursos en curso</option>
                            <option value="sin-actividad">Sin actividad</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ordenar por</label>
                        <select id="filtro-est-orden" onchange="aplicarFiltrosEstudiantes()" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="nombre">Nombre A-Z</option>
                            <option value="nombre-desc">Nombre Z-A</option>
                            <option value="cursos">Más cursos</option>
                            <option value="cursos-asc">Menos cursos</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    Mostrando <span id="filtro-est-count"><?php echo count($estudiantes); ?></span> de <?php echo count($estudiantes); ?> estudiantes
                </div>
            </div>
        </div>

        <!-- Botón toggle filtros -->
        <div class="mb-4">
            <button onclick="toggleFiltrosEstudiantes()" id="btn-toggle-filtros-est" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                <span id="btn-filtros-est-text">Mostrar filtros avanzados</span>
                <i data-lucide="chevron-down" class="w-4 h-4" id="btn-filtros-est-icon"></i>
            </button>
        </div>

        <!-- Panel de Importación (colapsable) -->
        <div id="panel-importar-estudiantes" class="hidden mb-6">
            <div class="bg-gradient-to-r from-blue-50 to-green-50 border border-blue-200 rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5 text-blue-600"></i>
                        Importar Estudiantes
                    </h3>
                    <button onclick="toggleImportarEstudiantes()" class="text-gray-500 hover:text-gray-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Tabs de importación -->
                <div class="flex border-b border-gray-200 mb-4">
                    <button onclick="cambiarTabImport('texto')" id="tab-import-texto" class="tab-import-btn active px-4 py-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600">
                        <i data-lucide="type" class="w-4 h-4 inline mr-1"></i> Texto/CSV
                    </button>
                    <button onclick="cambiarTabImport('archivo')" id="tab-import-archivo" class="tab-import-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                        <i data-lucide="file-up" class="w-4 h-4 inline mr-1"></i> Archivo Excel/CSV
                    </button>
                </div>

                <!-- Tab: Texto/CSV -->
                <div id="content-import-texto" class="tab-import-content">
                    <form method="POST" action="?modulo=certificatum" class="space-y-4">
                        <input type="hidden" name="accion" value="cargar_estudiantes">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pega los datos en formato CSV (un estudiante por línea)
                            </label>
                            <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
                                <p class="text-xs text-gray-500 mb-1"><strong>Formato básico:</strong> DNI, Nombre, Apellido, Email</p>
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded block mb-2">25123456, Juan, Pérez, juan@email.com</code>
                                <p class="text-xs text-gray-500 mb-1"><strong>Formato extendido:</strong> DNI, Nombre, Apellido, Email, Teléfono, Ciudad, Provincia, CódPostal, País, LugarTrabajo, Cargo, Profesión</p>
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded block">25123456, Juan, Pérez, juan@email.com, 1155667788, CABA, Buenos Aires, 1425, Argentina, Empresa SA, Gerente, Contador</code>
                                <p class="text-xs text-gray-400 mt-2 italic">Nota: Todos los campos después de Apellido son opcionales. Puedes dejar campos vacíos con comas consecutivas.</p>
                            </div>
                            <textarea name="texto_estudiantes" rows="6" class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="25123456, Juan, Pérez, juan@email.com&#10;30987654, María, Gómez, maria@email.com, 1144556677, Córdoba&#10;42555888, Carlos, López, , , Rosario, Santa Fe, 2000, Argentina, Hospital Central, Médico, Medicina" required></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="toggleImportarEstudiantes()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-medium flex items-center gap-2">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Importar Estudiantes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Archivo -->
                <div id="content-import-archivo" class="tab-import-content hidden">
                    <form method="POST" action="?modulo=certificatum" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="accion" value="cargar_estudiantes_archivo">
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition-colors" id="dropzone-estudiantes">
                            <i data-lucide="upload-cloud" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                            <p class="text-gray-600 mb-2">Arrastra un archivo aquí o</p>
                            <label class="cursor-pointer">
                                <span class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 inline-block">Seleccionar archivo</span>
                                <input type="file" name="archivo_estudiantes" accept=".csv,.xlsx,.xls" class="hidden" onchange="mostrarNombreArchivo(this, 'nombre-archivo-est')">
                            </label>
                            <p class="text-xs text-gray-500 mt-3">Formatos: CSV, Excel (.xlsx, .xls)</p>
                            <p id="nombre-archivo-est" class="text-sm text-green-600 mt-2 hidden"></p>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <p class="text-sm text-amber-800 mb-1">
                                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                                <strong>Columnas requeridas:</strong> DNI, Nombre, Apellido
                            </p>
                            <p class="text-xs text-amber-700">
                                <strong>Columnas opcionales:</strong> Email, Teléfono, Ciudad, Provincia, CódPostal, País, LugarTrabajo, Cargo, Profesión
                            </p>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="toggleImportarEstudiantes()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-medium flex items-center gap-2">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Subir Archivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Barra de Acciones Masivas - Estudiantes -->
        <div id="barra-acciones-estudiantes" class="hidden bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-3 rounded-t-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span class="font-medium">
                    <span id="count-seleccionados-est">0</span> seleccionado(s)
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="accionMasivaEstudiantes('exportar')" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium flex items-center gap-1 transition">
                    <i data-lucide="download" class="w-4 h-4"></i> Exportar
                </button>
                <button onclick="accionMasivaEstudiantes('email')" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium flex items-center gap-1 transition">
                    <i data-lucide="mail" class="w-4 h-4"></i> Email
                </button>
                <button onclick="accionMasivaEstudiantes('eliminar')" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 rounded-lg text-sm font-medium flex items-center gap-1 transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar
                </button>
            </div>
        </div>

        <!-- Tabla de Estudiantes -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" id="contenedor-tabla-estudiantes">
            <div class="overflow-x-auto">
                <table class="w-full" id="tabla-estudiantes">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-3 text-center w-10">
                                <input type="checkbox" id="select-all-estudiantes" onchange="toggleSelectAllEstudiantes()"
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estudiante</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Cursos</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="tbody-estudiantes">
                        <?php foreach ($estudiantes as $est): ?>
                        <tr class="hover:bg-gray-50 transition-colors estudiante-row"
                            data-id="<?php echo $est['id_estudiante']; ?>"
                            data-nombre="<?php echo strtolower(htmlspecialchars($est['nombre_completo'])); ?>"
                            data-estado="<?php echo strtolower($est['estado'] ?? 'activo'); ?>"
                            data-total-cursos="<?php echo (int)($est['total_cursos'] ?? 0); ?>"
                            data-cursos-aprobados="<?php echo (int)($est['cursos_aprobados'] ?? 0); ?>"
                            data-cursos-en-curso="<?php echo (int)($est['cursos_en_curso'] ?? 0); ?>">
                            <td class="px-3 py-4 text-center">
                                <input type="checkbox" name="estudiantes_seleccionados[]" value="<?php echo $est['id_estudiante']; ?>"
                                       class="checkbox-estudiante w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer"
                                       onchange="updateSelectionEstudiantes()"
                                       data-nombre="<?php echo htmlspecialchars($est['nombre_completo']); ?>"
                                       data-dni="<?php echo htmlspecialchars($est['dni']); ?>"
                                       data-email="<?php echo htmlspecialchars($est['email'] ?? ''); ?>">
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <span class="text-green-700 font-semibold text-sm"><?php echo strtoupper(substr($est['nombre_completo'], 0, 2)); ?></span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($est['nombre_completo']); ?>
                                            <?php
                                            // Mostrar otros roles que tenga el estudiante
                                            if (!empty($est['todos_los_roles'])) {
                                                $roles = explode(', ', $est['todos_los_roles']);
                                                foreach ($roles as $rol) {
                                                    if ($rol !== 'Estudiante') {
                                                        echo '<span class="ml-1 text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">+' . htmlspecialchars($rol) . '</span>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="text-sm text-gray-500 font-mono"><?php echo htmlspecialchars($est['dni']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    <?php echo $est['total_cursos']; ?> cursos
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-center gap-2">
                                    <?php if ($est['cursos_aprobados'] > 0): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-700">
                                        <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i><?php echo $est['cursos_aprobados']; ?> aprobados
                                    </span>
                                    <?php endif; ?>
                                    <?php if ($est['cursos_en_curso'] > 0): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                        <i data-lucide="play-circle" class="w-3 h-3 mr-1"></i><?php echo $est['cursos_en_curso']; ?> en curso
                                    </span>
                                    <?php endif; ?>
                                    <?php if ($est['total_cursos'] == 0): ?>
                                    <span class="text-xs text-gray-400">Sin inscripciones</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-center gap-1">
                                    <button onclick="abrirModalEditarEstudiante(<?php echo htmlspecialchars(json_encode($est), ENT_QUOTES, 'UTF-8'); ?>)"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Editar">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="verInscripcionesEstudiante(<?php echo $est['id_estudiante']; ?>, '<?php echo addslashes($est['nombre_completo']); ?>')"
                                            class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Ver inscripciones">
                                        <i data-lucide="book-open" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="confirmarEliminarEstudianteModal(<?php echo $est['id_estudiante']; ?>, '<?php echo addslashes($est['nombre_completo']); ?>')"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($estudiantes)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <i data-lucide="users" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-gray-500 mb-2">No hay estudiantes registrados</p>
                                <button onclick="abrirModalNuevoEstudiante()" class="text-green-600 hover:text-green-700 font-medium">
                                    + Agregar primer estudiante
                                </button>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Paginación Estudiantes -->
            <div id="paginacion-estudiantes" class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-sm text-gray-600">
                    Mostrando <span id="estudiantes-desde">1</span>-<span id="estudiantes-hasta">25</span> de <span id="estudiantes-total"><?php echo count($estudiantes); ?></span> estudiantes
                </div>
                <div class="flex items-center gap-1">
                    <button onclick="paginaEstudiantes('primera')" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" id="est-btn-primera">
                        <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                    </button>
                    <button onclick="paginaEstudiantes('anterior')" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" id="est-btn-anterior">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <span class="px-3 py-1.5 text-sm">
                        Página <span id="estudiantes-pagina-actual">1</span> de <span id="estudiantes-paginas-total">1</span>
                    </span>
                    <button onclick="paginaEstudiantes('siguiente')" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" id="est-btn-siguiente">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                    <button onclick="paginaEstudiantes('ultima')" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" id="est-btn-ultima">
                        <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Nuevo/Editar Estudiante (Compatible con Nexus) -->
    <div id="modal-estudiante" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all my-8">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-green-50 to-blue-50 rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 id="modal-estudiante-titulo" class="text-xl font-bold text-gray-900">Nuevo Estudiante</h3>
                        <p class="text-sm text-gray-500 mt-1">Los campos con * son obligatorios</p>
                    </div>
                    <button onclick="cerrarModalEstudiante()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            <form id="form-modal-estudiante" method="POST" action="?modulo=certificatum" class="p-6">
                <input type="hidden" name="accion" id="modal-estudiante-accion" value="crear_estudiante">
                <input type="hidden" name="id_estudiante" id="modal-estudiante-id" value="">

                <!-- Sección: Identificación -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="id-card" class="w-4 h-4"></i> Identificación
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">DNI / Documento *</label>
                            <input type="text" name="dni" id="modal-estudiante-dni" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase"
                                   placeholder="Ej: 25123456"
                                   pattern="[A-Za-z0-9.\-]+"
                                   title="Solo letras, números, guion (-) y punto (.)"
                                   oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9.\-]/g, '')"
                                   maxlength="20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="estado" id="modal-estudiante-estado"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                                <option value="Suspendido">Suspendido</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sección: Datos Personales -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4"></i> Datos Personales
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                            <input type="text" name="nombre" id="modal-estudiante-nombre" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase"
                                   placeholder="Ej: JUAN CARLOS"
                                   oninput="this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑÜÀÈÌÒÙÂÊÎÔÛÃÕÇ '\-]/g, '')"
                                   maxlength="100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apellido *</label>
                            <input type="text" name="apellido" id="modal-estudiante-apellido" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 uppercase"
                                   placeholder="Ej: PÉREZ GARCÍA"
                                   oninput="this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑÜÀÈÌÒÙÂÊÎÔÛÃÕÇ '\-]/g, '')"
                                   maxlength="100">
                        </div>
                    </div>
                </div>

                <!-- Sección: Contacto -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4"></i> Contacto
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="modal-estudiante-email"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Ej: juan@email.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="tel" name="telefono" id="modal-estudiante-telefono"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Ej: +54 11 1234-5678">
                        </div>
                    </div>
                </div>

                <!-- Sección: Información Adicional (colapsable) -->
                <div class="mb-6">
                    <button type="button" onclick="toggleInfoAdicional()" class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2 hover:text-gray-700">
                        <i data-lucide="chevron-down" id="icon-info-adicional" class="w-4 h-4 transition-transform"></i>
                        Información Adicional (opcional)
                    </button>
                    <div id="info-adicional" class="hidden">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="modal-estudiante-fecha-nac"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                                <select name="genero" id="modal-estudiante-genero"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <option value="Prefiero no especificar">Prefiero no especificar</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="No binario">No binario</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                                <input type="text" name="domicilio_ciudad" id="modal-estudiante-ciudad"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Ej: Buenos Aires">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Provincia / Estado</label>
                                <input type="text" name="domicilio_provincia" id="modal-estudiante-provincia"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Ej: Buenos Aires">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código Postal</label>
                                <input type="text" name="domicilio_codigo_postal" id="modal-estudiante-cp"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Ej: C1425">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                                <select name="domicilio_pais" id="modal-estudiante-pais"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <?php foreach ($paises as $codigo => $nombre): ?>
                                        <option value="<?php echo $codigo; ?>" <?php echo $codigo === 'AR' ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($nombre); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!-- Datos laborales -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Profesión / Oficio</label>
                                <input type="text" name="profesion" id="modal-estudiante-profesion"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Ej: Abogado, Contador">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lugar de Trabajo</label>
                                <input type="text" name="lugar_trabajo" id="modal-estudiante-trabajo"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Ej: Empresa, Institución">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                                <input type="text" name="cargo" id="modal-estudiante-cargo"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="Ej: Gerente, Analista">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="cerrarModalEstudiante()" class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-green-600 text-white px-6 py-2.5 rounded-lg hover:bg-green-700 font-medium flex items-center gap-2 shadow-sm">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span id="modal-estudiante-btn-texto">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación -->
    <div id="modal-confirmar-eliminar" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="alert-triangle" class="w-8 h-8 text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">¿Eliminar estudiante?</h3>
                <p id="modal-eliminar-mensaje" class="text-gray-600 mb-6">Esta acción no se puede deshacer.</p>
                <form id="form-eliminar-estudiante" method="POST" action="?modulo=certificatum">
                    <input type="hidden" name="accion" value="eliminar_estudiante">
                    <input type="hidden" name="id_estudiante" id="modal-eliminar-id" value="">
                    <div class="flex justify-center gap-3">
                        <button type="button" onclick="cerrarModalEliminar()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                            Sí, eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        </div><!-- Fin sub-personas-estudiantes -->

        <!-- Sub-contenido: Docentes -->
        <div id="sub-personas-docentes" class="sub-content-personas hidden">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                Gestionar Docentes
            </h2>
            <div class="flex gap-2">
                <form method="GET" class="flex gap-2">
                    <input type="hidden" name="modulo" value="certificatum">
                    <input type="text" name="buscar" placeholder="Buscar por DNI, nombre o email..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-64">
                    <button type="submit" onclick="setTimeout(function(){cambiarTabCert('docentes')}, 100)" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">Buscar</button>
                </form>
                <button onclick="abrirWizardImport('docentes')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    Importar
                </button>
                <button onclick="exportarDocentesCSV()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Exportar Todo
                </button>
                <button onclick="toggleAsignarCurso()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="book-plus" class="w-4 h-4"></i>
                    Asignar a Curso
                </button>
                <button onclick="abrirModalDocente()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Nuevo Docente
                </button>
            </div>
        </div>

        <!-- Panel de Asignar Docente a Curso (oculto por defecto) -->
        <div id="panel-asignar-curso" class="hidden mb-6">
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-semibold text-purple-900 flex items-center gap-2">
                            <i data-lucide="book-plus" class="w-5 h-5"></i>
                            Asignar Docente a Curso
                        </h3>
                        <p class="text-sm text-purple-700 mt-1">Seleccioná un docente y un curso para crear la participación</p>
                    </div>
                    <button onclick="toggleAsignarCurso()" class="text-gray-500 hover:text-gray-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form method="POST" action="?modulo=certificatum" class="space-y-4">
                    <input type="hidden" name="accion" value="crear_participacion">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-purple-900 mb-1">Docente *</label>
                            <select name="id_docente_participacion" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm" required>
                                <option value="">-- Seleccionar docente --</option>
                                <?php foreach ($docentes as $d): ?>
                                    <option value="<?php echo $d['id_miembro']; ?>">
                                        <?php echo htmlspecialchars($d['dni'] . ' - ' . ($d['nombre_completo'] ?: $d['nombre'] . ' ' . $d['apellido'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-purple-900 mb-1">Curso *</label>
                            <select name="id_curso_participacion" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm" required>
                                <option value="">-- Seleccionar curso --</option>
                                <?php foreach ($cursos as $c): ?>
                                    <?php if ($c['activo']): ?>
                                    <option value="<?php echo $c['id_curso']; ?>">
                                        <?php echo htmlspecialchars($c['codigo_curso'] . ' - ' . $c['nombre_curso']); ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-purple-900 mb-1">Rol</label>
                            <select name="rol_participacion" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm">
                                <option value="docente">Docente</option>
                                <option value="instructor">Instructor/a</option>
                                <option value="orador">Orador/a</option>
                                <option value="expositor">Expositor/a</option>
                                <option value="conferencista">Conferencista</option>
                                <option value="facilitador">Facilitador/a</option>
                                <option value="tutor">Tutor/a</option>
                                <option value="coordinador">Coordinador/a</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-purple-900 mb-1">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio_participacion" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-purple-900 mb-1">Fecha Fin</label>
                            <input type="date" name="fecha_fin_participacion" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-purple-900 mb-1">Título de la Participación (opcional)</label>
                        <input type="text" name="titulo_participacion" class="w-full border border-purple-300 rounded-lg px-3 py-2 text-sm" placeholder="Ej: Docente titular del módulo de Mediación">
                    </div>
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="notificar_docente" value="1" checked
                                   class="w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500 mt-0.5">
                            <div>
                                <span class="font-medium text-blue-900">Notificar al docente por email</span>
                                <p class="text-xs text-blue-700 mt-0.5">Se enviará un email informando que ha sido asignado al curso</p>
                            </div>
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" onclick="toggleAsignarCurso()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 font-medium">
                            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i>
                            Asignar Docente
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel de Importación (oculto por defecto) -->
        <div id="panel-importar-docentes" class="hidden mb-6">
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5 text-amber-600"></i>
                        Importar Docentes
                    </h3>
                    <button onclick="toggleImportarDocentes()" class="text-gray-500 hover:text-gray-700">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Tabs de importación -->
                <div class="flex border-b border-gray-200 mb-4">
                    <button onclick="cambiarTabImportDocentes('texto')" id="tab-import-docentes-texto" class="tab-import-docentes-btn active px-4 py-2 text-sm font-medium border-b-2 border-amber-600 text-amber-600">
                        <i data-lucide="type" class="w-4 h-4 inline mr-1"></i> Texto/CSV
                    </button>
                    <button onclick="cambiarTabImportDocentes('archivo')" id="tab-import-docentes-archivo" class="tab-import-docentes-btn px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                        <i data-lucide="file-up" class="w-4 h-4 inline mr-1"></i> Archivo Excel/CSV
                    </button>
                </div>

                <!-- Tab: Texto/CSV -->
                <div id="content-import-docentes-texto" class="tab-import-docentes-content">
                    <form method="POST" action="?modulo=certificatum" class="space-y-4">
                        <input type="hidden" name="accion" value="cargar_docentes">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pega los datos en formato CSV (un docente por línea)
                            </label>
                            <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
                                <p class="text-xs text-gray-500 mb-1"><strong>Formato básico:</strong> DNI, Nombre, Apellido, Email, Especialidad, Título</p>
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded block mb-2">12345678, Juan, Pérez, juan@email.com, Derecho Penal, Dr. en Derecho</code>
                                <p class="text-xs text-gray-500 mb-1"><strong>Formato extendido:</strong> DNI, Nombre, Apellido, Email, Especialidad, Título, Teléfono, Ciudad, Provincia, CódPostal, País, LugarTrabajo, Cargo, Profesión</p>
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded block">12345678, Juan, Pérez, juan@email.com, Derecho Penal, Dr., 1155667788, CABA, Buenos Aires, 1425, Argentina, Universidad, Profesor, Abogado</code>
                                <p class="text-xs text-gray-400 mt-2 italic">Nota: Todos los campos después de Título son opcionales. Puedes dejar campos vacíos con comas consecutivas.</p>
                            </div>
                            <textarea name="texto_docentes" rows="6" class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500" placeholder="12345678, Juan, Pérez, juan@email.com, Derecho Penal, Dr. en Derecho&#10;87654321, María, García, maria@email.com, Mediación, Lic., 1144556677, Córdoba&#10;55667788, Carlos, López, carlos@email.com, Justicia Restaurativa, Mag., , , , , Argentina, UBA, Titular, Abogado" required></textarea>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="toggleImportarDocentes()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                            <button type="submit" class="bg-amber-600 text-white px-6 py-2 rounded-lg hover:bg-amber-700 font-medium flex items-center gap-2">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Importar Docentes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Archivo -->
                <div id="content-import-docentes-archivo" class="tab-import-docentes-content hidden">
                    <form method="POST" action="?modulo=certificatum" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="accion" value="cargar_docentes_archivo">
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-amber-400 transition-colors" id="dropzone-docentes">
                            <i data-lucide="upload-cloud" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                            <p class="text-gray-600 mb-2">Arrastra un archivo aquí o</p>
                            <label class="cursor-pointer">
                                <span class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 inline-block">Seleccionar archivo</span>
                                <input type="file" name="archivo_docentes" accept=".csv,.xlsx,.xls" class="hidden" onchange="mostrarNombreArchivo(this, 'nombre-archivo-doc')">
                            </label>
                            <p class="text-xs text-gray-500 mt-3">Formatos: CSV, Excel (.xlsx, .xls)</p>
                            <p id="nombre-archivo-doc" class="text-sm text-green-600 mt-2 hidden"></p>
                        </div>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <p class="text-sm text-amber-800 mb-1">
                                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                                <strong>Columnas requeridas:</strong> DNI, Nombre, Apellido
                            </p>
                            <p class="text-xs text-amber-700">
                                <strong>Columnas opcionales:</strong> Email, Especialidad, Título, Teléfono, Ciudad, Provincia, CódPostal, País, LugarTrabajo, Cargo, Profesión
                            </p>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="toggleImportarDocentes()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                            <button type="submit" class="bg-amber-600 text-white px-6 py-2 rounded-lg hover:bg-amber-700 font-medium flex items-center gap-2">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Subir Archivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Barra de Acciones Masivas - Docentes -->
        <div id="barra-acciones-docentes" class="hidden bg-gradient-to-r from-purple-600 to-purple-700 text-white px-4 py-3 rounded-t-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span class="font-medium">
                    <span id="count-seleccionados-doc">0</span> seleccionado(s)
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="accionMasivaDocentes('exportar')" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium flex items-center gap-1 transition">
                    <i data-lucide="download" class="w-4 h-4"></i> Exportar
                </button>
                <button onclick="accionMasivaDocentes('email')" class="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium flex items-center gap-1 transition">
                    <i data-lucide="mail" class="w-4 h-4"></i> Email
                </button>
                <button onclick="accionMasivaDocentes('eliminar')" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 rounded-lg text-sm font-medium flex items-center gap-1 transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar
                </button>
            </div>
        </div>

        <!-- Tabla de Docentes -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden" id="contenedor-tabla-docentes">
            <?php if (empty($docentes)): ?>
                <div class="p-12 text-center">
                    <i data-lucide="graduation-cap" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">No hay docentes registrados</h3>
                    <p class="text-gray-500 mb-4">Agregá docentes manualmente o importá desde un listado</p>
                    <button onclick="abrirModalDocente()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-medium">
                        <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i>
                        Agregar primer docente
                    </button>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full" id="tabla-docentes">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-3 py-3 text-center w-10">
                                    <input type="checkbox" id="select-all-docentes" onchange="toggleSelectAllDocentes()"
                                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 cursor-pointer">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">DNI</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Especialidad</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Título</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Partic.</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="tbody-docentes">
                            <?php foreach ($docentes as $docente): ?>
                                <tr class="hover:bg-gray-50 editable-row" data-id="<?php echo $docente['id_miembro']; ?>">
                                    <td class="px-3 py-3 text-center">
                                        <input type="checkbox" name="docentes_seleccionados[]" value="<?php echo $docente['id_miembro']; ?>"
                                               class="checkbox-docente w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500 cursor-pointer"
                                               onchange="updateSelectionDocentes()"
                                               data-nombre="<?php echo htmlspecialchars($docente['nombre_completo'] ?: $docente['nombre'] . ' ' . $docente['apellido']); ?>"
                                               data-dni="<?php echo htmlspecialchars($docente['dni']); ?>"
                                               data-email="<?php echo htmlspecialchars($docente['email'] ?? ''); ?>">
                                    </td>
                                    <td class="px-4 py-3 text-sm font-mono"><?php echo htmlspecialchars($docente['dni']); ?></td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($docente['nombre_completo'] ?: $docente['nombre'] . ' ' . $docente['apellido']); ?>
                                        <?php
                                        // Mostrar otros roles que tenga el docente
                                        $otros_roles = [];
                                        if (!empty($docente['todos_los_roles'])) {
                                            $roles = explode(', ', $docente['todos_los_roles']);
                                            foreach ($roles as $rol) {
                                                if ($rol !== 'Docente') {
                                                    $otros_roles[] = $rol;
                                                }
                                            }
                                        }
                                        foreach ($otros_roles as $rol): ?>
                                            <span class="ml-1 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">+<?php echo htmlspecialchars($rol); ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($docente['email'] ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($docente['especialidad'] ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($docente['titulo'] ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <span class="bg-amber-100 text-amber-700 px-2 py-1 rounded-full text-xs font-medium">
                                            <?php echo (int)($docente['total_participaciones'] ?? 0); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center gap-1">
                                            <button onclick="editarDocente(<?php echo htmlspecialchars(json_encode($docente)); ?>)"
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="Editar">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>
                                            <button onclick="confirmarEliminarDocente(<?php echo $docente['id_miembro']; ?>, '<?php echo htmlspecialchars($docente['nombre_completo'] ?: $docente['nombre']); ?>')"
                                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Eliminar">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 text-sm text-gray-600">
                    Total: <?php echo count($docentes); ?> docente<?php echo count($docentes) !== 1 ? 's' : ''; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal: Nuevo/Editar Docente -->
    <div id="modal-docente" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all my-8 max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50 rounded-t-2xl flex-shrink-0">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 id="modal-docente-titulo" class="text-xl font-bold text-gray-900">Nuevo Docente</h3>
                        <p class="text-sm text-gray-500 mt-1">Los campos con * son obligatorios</p>
                    </div>
                    <button onclick="cerrarModalDocente()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            <form id="form-modal-docente" method="POST" action="?modulo=certificatum" class="flex flex-col flex-1 overflow-hidden">
                <div class="p-6 overflow-y-auto flex-1">
                <input type="hidden" name="accion" id="modal-docente-accion" value="crear_docente">
                <input type="hidden" name="id_docente" id="modal-docente-id" value="">

                <!-- Sección: Identificación -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="id-card" class="w-4 h-4"></i> Identificación
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">DNI / Documento *</label>
                            <input type="text" name="dni" id="modal-docente-dni" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 uppercase"
                                   placeholder="Ej: 25123456"
                                   oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9.\-]/g, '')"
                                   maxlength="20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="modal-docente-email"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ej: docente@email.com">
                        </div>
                    </div>
                </div>

                <!-- Sección: Datos Personales -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4"></i> Datos Personales
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                            <input type="text" name="nombre" id="modal-docente-nombre" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 uppercase"
                                   placeholder="Ej: JUAN CARLOS"
                                   oninput="this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑÜÀÈÌÒÙÂÊÎÔÛÃÕÇ '\-]/g, '')">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apellido *</label>
                            <input type="text" name="apellido" id="modal-docente-apellido" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 uppercase"
                                   placeholder="Ej: PÉREZ GARCÍA"
                                   oninput="this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑÜÀÈÌÒÙÂÊÎÔÛÃÕÇ '\-]/g, '')">
                        </div>
                    </div>
                </div>

                <!-- Sección: Ubicación -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4"></i> Ubicación
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                            <input type="text" name="domicilio_ciudad" id="modal-docente-ciudad"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ej: Buenos Aires">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provincia / Estado</label>
                            <input type="text" name="domicilio_provincia" id="modal-docente-provincia"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ej: Buenos Aires">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código Postal</label>
                            <input type="text" name="domicilio_codigo_postal" id="modal-docente-cp"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ej: C1425">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                        <select name="pais" id="modal-docente-pais"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                            <?php foreach ($paises as $codigo => $nombre_pais): ?>
                                <option value="<?php echo $codigo; ?>" <?php echo $codigo === 'AR' ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nombre_pais); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Sección: Datos Laborales -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="briefcase" class="w-4 h-4"></i> Datos Laborales
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Profesión</label>
                            <input type="text" name="profesion" id="modal-docente-profesion"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ej: Abogado, Psicólogo">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lugar de Trabajo</label>
                            <input type="text" name="lugar_trabajo" id="modal-docente-trabajo"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ej: Universidad, Empresa">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                            <input type="text" name="cargo" id="modal-docente-cargo"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ej: Director, Coordinador">
                        </div>
                    </div>
                </div>

                <!-- Sección: Información Académica -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <i data-lucide="award" class="w-4 h-4"></i> Información Académica
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                            <input type="text" name="especialidad" id="modal-docente-especialidad"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ej: Derecho Penal, Mediación">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Título Académico</label>
                            <input type="text" name="titulo" id="modal-docente-titulo"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                   placeholder="Ej: Dr. en Derecho, Lic. en Psicología">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                            <select name="genero" id="modal-docente-genero"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white">
                                <option value="Prefiero no especificar">Prefiero no especificar</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="No binario">No binario</option>
                            </select>
                        </div>
                    </div>
                </div>
                </div><!-- Cierre del div scrolleable -->

                <div class="flex justify-end gap-3 p-6 pt-4 border-t border-gray-100 bg-white rounded-b-2xl flex-shrink-0">
                    <button type="button" onclick="cerrarModalDocente()" class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-amber-600 text-white px-6 py-2.5 rounded-lg hover:bg-amber-700 font-medium flex items-center gap-2 shadow-sm">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span id="modal-docente-btn-texto">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación Docente -->
    <div id="modal-confirmar-eliminar-docente" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="alert-triangle" class="w-8 h-8 text-red-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">¿Eliminar docente?</h3>
                <p id="modal-eliminar-docente-mensaje" class="text-gray-600 mb-6">Esta acción no se puede deshacer.</p>
                <form id="form-eliminar-docente" method="POST" action="?modulo=certificatum">
                    <input type="hidden" name="accion" value="eliminar_docente">
                    <input type="hidden" name="id_docente" id="modal-eliminar-docente-id" value="">
                    <div class="flex justify-center gap-3">
                        <button type="button" onclick="cerrarModalEliminarDocente()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                            Sí, eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Confirmar Diferencias Estudiante/Docente -->
    <?php if ($mostrar_modal_diferencias && !empty($datos_diferencias['diferencias'])): ?>
    <div id="modal-diferencias-docente" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-8">
            <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-t-2xl">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="alert-circle" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Estudiante existente con datos diferentes</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            El DNI ya existe como estudiante. Seleccioná qué datos querés mantener para cada campo.
                        </p>
                    </div>
                </div>
            </div>
            <form method="POST" action="?modulo=certificatum" class="p-6">
                <input type="hidden" name="accion" value="confirmar_merge_docente">
                <input type="hidden" name="id_miembro" value="<?php echo htmlspecialchars($datos_diferencias['id_miembro']); ?>">
                <input type="hidden" name="especialidad" value="<?php echo htmlspecialchars($datos_diferencias['datos_docente']['especialidad'] ?? ''); ?>">
                <input type="hidden" name="titulo" value="<?php echo htmlspecialchars($datos_diferencias['datos_docente']['titulo'] ?? ''); ?>">

                <table class="w-full mb-6">
                    <thead>
                        <tr class="text-left text-sm text-gray-500 uppercase tracking-wider">
                            <th class="pb-3 font-semibold">Campo</th>
                            <th class="pb-3 font-semibold">Valor Estudiante</th>
                            <th class="pb-3 font-semibold">Valor Docente (nuevo)</th>
                            <th class="pb-3 font-semibold text-center">Usar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php
                        $campos_label = [
                            'nombre' => 'Nombre',
                            'apellido' => 'Apellido',
                            'email' => 'Email',
                            'pais' => 'País'
                        ];
                        foreach ($datos_diferencias['diferencias'] as $campo => $valores):
                            $label = $campos_label[$campo] ?? ucfirst($campo);
                            $valor_existente = $valores['existente'];
                            $valor_nuevo = $valores['nuevo'];
                            // Para país, mostrar nombre en lugar de código
                            if ($campo === 'pais') {
                                $valor_existente_display = $paises[$valor_existente] ?? $valor_existente;
                                $valor_nuevo_display = $paises[$valor_nuevo] ?? $valor_nuevo;
                            } else {
                                $valor_existente_display = $valor_existente;
                                $valor_nuevo_display = $valor_nuevo;
                            }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 font-medium text-gray-900"><?php echo $label; ?></td>
                            <td class="py-3">
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-sm">
                                    <?php echo htmlspecialchars($valor_existente_display ?: '(vacío)'); ?>
                                </span>
                                <input type="hidden" name="existente_<?php echo $campo; ?>" value="<?php echo htmlspecialchars($valor_existente); ?>">
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-1 bg-amber-50 text-amber-700 rounded text-sm">
                                    <?php echo htmlspecialchars($valor_nuevo_display ?: '(vacío)'); ?>
                                </span>
                                <input type="hidden" name="nuevo_<?php echo $campo; ?>" value="<?php echo htmlspecialchars($valor_nuevo); ?>">
                            </td>
                            <td class="py-3 text-center">
                                <div class="flex justify-center gap-4">
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="seleccion_<?php echo $campo; ?>" value="existente" checked
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-blue-600">Estudiante</span>
                                    </label>
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="radio" name="seleccion_<?php echo $campo; ?>" value="nuevo"
                                               class="text-amber-600 focus:ring-amber-500">
                                        <span class="text-sm text-amber-600">Docente</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-6">
                    <p class="text-sm text-green-800">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                        Al confirmar, la persona quedará registrada como <strong>Estudiante + Docente</strong> con los datos seleccionados.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="?modulo=certificatum&tab=docentes" class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-amber-600 text-white px-6 py-2.5 rounded-lg hover:bg-amber-700 font-medium flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Confirmar y Crear Docente
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Mostrar modal automáticamente y crear iconos
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
    <?php endif; ?>

        </div><!-- Fin sub-personas-docentes -->
    </div><!-- Fin tab-cert-personas -->

    <!-- Tab Content: Cursos -->
    <div id="tab-cert-cursos" class="tab-content-cert p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Gestionar Cursos</h2>
            <div class="flex gap-2">
                <form method="GET" class="flex gap-2">
                    <input type="hidden" name="modulo" value="certificatum">
                    <input type="text" name="buscar" placeholder="Buscar por código o nombre..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded px-3 py-2 text-sm">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Buscar</button>
                </form>
                <button onclick="abrirModalCurso()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm flex items-center gap-1">
                    <i data-lucide="plus" class="w-4 h-4"></i> Nuevo Curso
                </button>
                <button onclick="exportarCursosCSV()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm flex items-center gap-1">
                    <i data-lucide="download" class="w-4 h-4"></i> Exportar
                </button>
            </div>
        </div>

        <!-- Panel de Filtros Avanzados - Cursos (colapsable) -->
        <div id="panel-filtros-cursos" class="hidden mb-4">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-700 flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        Filtros Avanzados
                    </h4>
                    <button onclick="limpiarFiltrosCursos()" class="text-sm text-blue-600 hover:text-blue-800">
                        Limpiar filtros
                    </button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                        <select id="filtro-cur-estado" onchange="aplicarFiltrosCursos()" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                        <select id="filtro-cur-tipo" onchange="aplicarFiltrosCursos()" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="">Todos</option>
                            <option value="curso">Curso</option>
                            <option value="diplomatura">Diplomatura</option>
                            <option value="taller">Taller</option>
                            <option value="seminario">Seminario</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Modalidad</label>
                        <select id="filtro-cur-modalidad" onchange="aplicarFiltrosCursos()" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="">Todas</option>
                            <option value="virtual">Virtual</option>
                            <option value="presencial">Presencial</option>
                            <option value="hibrido">Híbrido</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ordenar por</label>
                        <select id="filtro-cur-orden" onchange="aplicarFiltrosCursos()" class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm">
                            <option value="nombre">Nombre A-Z</option>
                            <option value="nombre-desc">Nombre Z-A</option>
                            <option value="inscripciones">Más inscripciones</option>
                            <option value="horas">Más horas</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    Mostrando <span id="filtro-cur-count"><?php echo count($cursos); ?></span> de <?php echo count($cursos); ?> cursos
                </div>
            </div>
        </div>

        <!-- Botón toggle filtros cursos -->
        <div class="mb-4">
            <button onclick="toggleFiltrosCursos()" id="btn-toggle-filtros-cur" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                <span id="btn-filtros-cur-text">Mostrar filtros avanzados</span>
                <i data-lucide="chevron-down" class="w-4 h-4" id="btn-filtros-cur-icon"></i>
            </button>
        </div>

        <!-- Opciones de carga masiva -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Carga desde texto/CSV -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <h3 class="font-bold text-purple-900 mb-2 flex items-center">
                    <i data-lucide="file-text" class="w-5 h-5 mr-2"></i>
                    Cargar desde Texto
                </h3>
                <p class="text-sm text-purple-800 mb-3">Pegar datos en formato CSV</p>
                <button onclick="document.getElementById('form-cursos-texto').style.display='block'" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm w-full">
                    Cargar Texto/CSV
                </button>
            </div>

            <!-- Carga desde archivo CSV -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                <h3 class="font-bold text-indigo-900 mb-2 flex items-center">
                    <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                    Cargar Archivo CSV
                </h3>
                <p class="text-sm text-indigo-800 mb-3">Subir archivo .csv</p>
                <button onclick="document.getElementById('form-cursos-csv').style.display='block'" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm w-full">
                    Subir CSV
                </button>
            </div>

            <!-- Carga desde Excel -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h3 class="font-bold text-green-900 mb-2 flex items-center">
                    <i data-lucide="table" class="w-5 h-5 mr-2"></i>
                    Cargar desde Excel
                </h3>
                <p class="text-sm text-green-800 mb-3">Subir archivo .xlsx</p>
                <button onclick="document.getElementById('form-cursos-excel').style.display='block'" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm w-full">
                    Subir Excel
                </button>
            </div>
        </div>

        <!-- Form: Texto/CSV -->
        <div id="form-cursos-texto" style="display: none;" class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
            <form method="POST" action="?modulo=certificatum" class="space-y-3">
                <input type="hidden" name="accion" value="cargar_cursos">
                <div>
                    <label class="block text-sm font-semibold text-purple-900 mb-2">
                        Formato: Código, Nombre, Carga Horaria [, Tipo] [, Categoría] (un curso por línea)
                    </label>
                    <textarea name="texto_cursos" rows="6" class="w-full border border-purple-300 rounded px-3 py-2 font-mono text-sm" placeholder="SJ-DPA-2024, Diplomatura en Prácticas de Justicia Restaurativa, 90, Diplomatura, Derecho&#10;SJ-MED-2024, Curso de Mediación Comunitaria, 60, Curso, Mediación" required></textarea>
                    <p class="text-xs text-purple-700 mt-1">Tipos: Curso, Diplomatura, Taller, Seminario, Conversatorio, Capacitación, Certificación</p>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 font-semibold">Cargar Cursos</button>
                    <button type="button" onclick="document.getElementById('form-cursos-texto').style.display='none'" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- Form: CSV -->
        <div id="form-cursos-csv" style="display: none;" class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
            <form method="POST" action="?modulo=certificatum" enctype="multipart/form-data" class="space-y-3">
                <input type="hidden" name="accion" value="cargar_cursos_csv">
                <div>
                    <label class="block text-sm font-semibold text-indigo-900 mb-2">Archivo CSV</label>
                    <input type="file" name="archivo_cursos_csv" accept=".csv" class="w-full border border-indigo-300 rounded px-3 py-2 text-sm bg-white" required>
                    <p class="text-xs text-indigo-700 mt-1">Columnas: codigo_curso, nombre_curso, carga_horaria, tipo_curso (opcional), categoria (opcional)</p>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 font-semibold">Cargar CSV</button>
                    <button type="button" onclick="document.getElementById('form-cursos-csv').style.display='none'" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- Form: Excel -->
        <div id="form-cursos-excel" style="display: none;" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <form method="POST" action="?modulo=certificatum" enctype="multipart/form-data" class="space-y-3">
                <input type="hidden" name="accion" value="cargar_cursos_excel">
                <div>
                    <label class="block text-sm font-semibold text-green-900 mb-2">Archivo Excel</label>
                    <input type="file" name="archivo_cursos_excel" accept=".xlsx,.xls" class="w-full border border-green-300 rounded px-3 py-2 text-sm bg-white" required>
                    <p class="text-xs text-green-700 mt-1">Columnas: codigo_curso, nombre_curso, carga_horaria, tipo_curso (opcional), categoria (opcional)</p>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 font-semibold">Cargar Excel</button>
                    <button type="button" onclick="document.getElementById('form-cursos-excel').style.display='none'" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="tabla-cursos">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Código</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Nombre del Curso</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Tipo</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Modalidad</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Horas</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Inscr.</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Estado</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-cursos">
                    <?php foreach ($cursos as $curso): ?>
                    <tr class="editable-row border-b hover:bg-gray-50 curso-row"
                        data-nombre="<?php echo strtolower(htmlspecialchars($curso['nombre_curso'])); ?>"
                        data-activo="<?php echo $curso['activo'] ? '1' : '0'; ?>"
                        data-tipo="<?php echo strtolower($curso['tipo_curso'] ?? 'curso'); ?>"
                        data-modalidad="<?php echo strtolower($curso['modalidad'] ?? 'virtual'); ?>"
                        data-horas="<?php echo (int)($curso['carga_horaria'] ?? 0); ?>"
                        data-inscripciones="<?php echo (int)($curso['total_inscripciones'] ?? 0); ?>">
                        <td class="px-4 py-3 text-sm font-mono"><?php echo htmlspecialchars($curso['codigo_curso']); ?></td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium"><?php echo htmlspecialchars($curso['nombre_curso']); ?></div>
                            <?php if (!empty($curso['categoria'])): ?>
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($curso['categoria']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs"><?php echo htmlspecialchars($curso['tipo_curso'] ?? 'Curso'); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <span class="text-gray-600 text-xs"><?php echo htmlspecialchars($curso['modalidad'] ?? 'Virtual'); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm"><?php echo $curso['carga_horaria'] ?? '-'; ?></td>
                        <td class="px-4 py-3 text-center text-sm"><?php echo $curso['total_inscripciones'] ?? 0; ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="<?php echo $curso['activo'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> px-2 py-1 rounded text-xs">
                                <?php echo $curso['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="editarCurso(<?php echo htmlspecialchars(json_encode($curso)); ?>)" class="text-blue-600 hover:text-blue-800 text-sm mr-2" title="Editar">
                                <i data-lucide="edit" class="w-4 h-4 inline"></i>
                            </button>
                            <button onclick="confirmarEliminarCurso(<?php echo $curso['id_curso']; ?>)" class="text-red-600 hover:text-red-800 text-sm" title="Eliminar">
                                <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cursos)): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">No hay cursos registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Paginación Cursos -->
        <div id="paginacion-cursos" class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-sm text-gray-600">
                Mostrando <span id="cursos-desde">1</span>-<span id="cursos-hasta">25</span> de <span id="cursos-total"><?php echo count($cursos); ?></span> cursos
            </div>
            <div class="flex items-center gap-1">
                <button onclick="paginaCursos('primera')" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" id="cur-btn-primera">
                    <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                </button>
                <button onclick="paginaCursos('anterior')" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" id="cur-btn-anterior">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>
                <span class="px-3 py-1.5 text-sm">
                    Página <span id="cursos-pagina-actual">1</span> de <span id="cursos-paginas-total">1</span>
                </span>
                <button onclick="paginaCursos('siguiente')" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" id="cur-btn-siguiente">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
                <button onclick="paginaCursos('ultima')" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" id="cur-btn-ultima">
                    <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Content: MATRÍCULAS (Inscripciones + Asignaciones) -->
    <div id="tab-cert-matriculas" class="tab-content-cert p-6">
        <!-- Sub-tabs de Matrículas -->
        <div class="flex gap-4 mb-6 border-b">
            <button onclick="cambiarSubTabMatriculas('inscripciones')" class="sub-tab-matriculas active px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                <i data-lucide="file-check" class="w-4 h-4 inline mr-1"></i>
                Inscripciones (<?php echo count($inscripciones); ?>)
            </button>
            <button onclick="cambiarSubTabMatriculas('asignaciones')" class="sub-tab-matriculas px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                <i data-lucide="briefcase" class="w-4 h-4 inline mr-1"></i>
                Asignaciones (<?php echo count($asignaciones_docentes); ?>)
            </button>
        </div>

        <!-- Sub-contenido: Inscripciones -->
        <div id="sub-matriculas-inscripciones" class="sub-content-matriculas">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">Gestionar Inscripciones</h2>
            <div class="flex gap-2">
                <form method="GET" class="flex gap-2">
                    <input type="hidden" name="modulo" value="certificatum">
                    <input type="hidden" name="tab" value="inscripciones">
                    <select name="estado" class="border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="">Todos los estados</option>
                        <option value="Preinscrito" <?php echo $filtro_estado === 'Preinscrito' ? 'selected' : ''; ?>>Preinscrito</option>
                        <option value="Inscrito" <?php echo $filtro_estado === 'Inscrito' ? 'selected' : ''; ?>>Inscrito</option>
                        <option value="En Curso" <?php echo $filtro_estado === 'En Curso' ? 'selected' : ''; ?>>En Curso</option>
                        <option value="Finalizado" <?php echo $filtro_estado === 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                        <option value="Aprobado" <?php echo $filtro_estado === 'Aprobado' ? 'selected' : ''; ?>>Aprobado</option>
                        <option value="Desaprobado" <?php echo $filtro_estado === 'Desaprobado' ? 'selected' : ''; ?>>Desaprobado</option>
                        <option value="Abandonado" <?php echo $filtro_estado === 'Abandonado' ? 'selected' : ''; ?>>Abandonado</option>
                    </select>
                    <input type="text" name="buscar" placeholder="Buscar..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded px-3 py-2 text-sm">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Filtrar</button>
                </form>
                <button onclick="abrirWizardImport('inscripciones')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-semibold flex items-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4"></i> Importar
                </button>
                <button onclick="exportarInscripcionesCSV()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm font-semibold flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i> Exportar
                </button>
                <button onclick="abrirModalNuevaInscripcion()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm font-semibold">
                    <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i> Nueva Inscripción
                </button>
            </div>
        </div>

        <!-- Sección de carga masiva -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Opción 1: Texto/CSV -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <h3 class="font-bold text-orange-900 mb-2">
                    <i data-lucide="file-text" class="w-5 h-5 inline mr-2"></i>
                    Cargar desde Texto
                </h3>
                <p class="text-sm text-orange-800 mb-3">Pega datos CSV directamente</p>
                <button onclick="document.getElementById('form-inscripciones-texto').classList.toggle('hidden')" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 text-sm w-full">
                    Cargar Texto/CSV
                </button>
            </div>

            <!-- Opción 2: Archivo CSV -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-bold text-blue-900 mb-2">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5 inline mr-2"></i>
                    Cargar Archivo CSV
                </h3>
                <p class="text-sm text-blue-800 mb-3">Sube un archivo .csv</p>
                <form method="POST" action="?modulo=certificatum" enctype="multipart/form-data" class="space-y-2">
                    <input type="hidden" name="accion" value="cargar_inscripciones_csv">
                    <input type="file" name="archivo_inscripciones_csv" accept=".csv" required class="text-sm w-full">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm w-full">Subir CSV</button>
                </form>
            </div>

            <!-- Opción 3: Inscripción a curso específico -->
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <h3 class="font-bold text-purple-900 mb-2">
                    <i data-lucide="users" class="w-5 h-5 inline mr-2"></i>
                    Inscripción Masiva a Curso
                </h3>
                <p class="text-sm text-purple-800 mb-3">Inscribe varios estudiantes a un curso</p>
                <button onclick="document.getElementById('form-inscripcion-masiva').classList.toggle('hidden')" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm w-full">
                    Inscripción Masiva
                </button>
            </div>
        </div>

        <!-- Formulario de texto/CSV (oculto por defecto) -->
        <div id="form-inscripciones-texto" class="hidden bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
            <form method="POST" action="?modulo=certificatum" class="space-y-3">
                <input type="hidden" name="accion" value="cargar_inscripciones_texto">
                <label class="block text-sm font-semibold text-orange-900 mb-2">
                    Formato: DNI, Código Curso, Estado, Fecha Inicio, Fecha Fin, Nota, Asistencia
                </label>
                <textarea name="texto_inscripciones_csv" rows="8" class="w-full border border-orange-300 rounded px-3 py-2 font-mono text-sm" placeholder="25123456, SJ-DPA-2024, En Curso, 15/03/2024, , , &#10;30987654, SJ-DPA-2024, Aprobado, 10/01/2024, 15/06/2024, 8.5, 95%"></textarea>
                <p class="text-xs text-orange-700">Estados: Inscrito, En Curso, Finalizado, Aprobado, Desaprobado, Abandonado</p>
                <div class="flex gap-2">
                    <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded hover:bg-orange-700 font-semibold">Cargar Inscripciones</button>
                    <button type="button" onclick="document.getElementById('form-inscripciones-texto').classList.add('hidden')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- Formulario de inscripción masiva a curso (oculto por defecto) -->
        <div id="form-inscripcion-masiva" class="hidden bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
            <form method="POST" action="?modulo=certificatum" class="space-y-3">
                <input type="hidden" name="accion" value="inscribir_curso">
                <div>
                    <label class="block text-sm font-semibold text-purple-900 mb-2">Seleccionar Curso</label>
                    <select name="id_curso_inscribir" class="w-full border border-purple-300 rounded px-3 py-2 text-sm" required>
                        <option value="">-- Seleccionar curso --</option>
                        <?php foreach ($cursos as $c): ?>
                            <?php if ($c['activo']): // Solo mostrar cursos activos ?>
                            <option value="<?php echo $c['id_curso']; ?>"><?php echo htmlspecialchars($c['codigo_curso'] . ' - ' . $c['nombre_curso']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-purple-900 mb-2">
                        Formato: DNI, Estado, Fecha Inicio, Nota Final, Asistencia (un estudiante por línea)
                    </label>
                    <textarea name="texto_inscripciones" rows="8" class="w-full border border-purple-300 rounded px-3 py-2 font-mono text-sm" placeholder="25123456, En Curso, 15/03/2024, , &#10;30987654, Aprobado, 10/01/2024, 8.5, 95" required></textarea>
                    <div class="mt-2 text-xs text-purple-700 space-y-1">
                        <p><strong>Estados:</strong> Inscrito, En Curso, Finalizado, Aprobado, Desaprobado, Abandonado, Suspendido</p>
                        <p><strong>Fecha:</strong> DD/MM/AAAA (ej: 15/03/2024) - dejar vacío si no aplica</p>
                        <p><strong>Nota:</strong> número del 1 al 10 (ej: 8.5) - dejar vacío si no aplica</p>
                        <p><strong>Asistencia:</strong> número sin % (ej: 95) - dejar vacío si no aplica</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 py-2">
                    <input type="checkbox" name="notificar_email" id="notificar_email_masivo" value="1" class="w-4 h-4 text-purple-600 rounded">
                    <label for="notificar_email_masivo" class="text-sm text-purple-900">Notificar a los estudiantes por email</label>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 font-semibold">Inscribir Estudiantes</button>
                    <button type="button" onclick="document.getElementById('form-inscripcion-masiva').classList.add('hidden')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                </div>
            </form>
        </div>

        <!-- Barra de acciones masivas (oculta por defecto) -->
        <div id="barra-acciones-masivas" class="hidden bg-gradient-to-r from-orange-600 to-orange-700 text-white px-4 py-3 rounded-xl mb-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-square" class="w-5 h-5"></i>
                    <span class="font-medium">
                        <span id="contador-seleccionados">0</span> inscripción(es) seleccionada(s)
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Acciones básicas -->
                    <button type="button" onclick="accionMasivaInscripciones('exportar')" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                        <i data-lucide="download" class="w-4 h-4"></i> Exportar
                    </button>
                    <button type="button" onclick="accionMasivaInscripciones('email')" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                        <i data-lucide="mail" class="w-4 h-4"></i> Email
                    </button>
                    <div class="w-px h-6 bg-white/30 mx-1"></div>
                    <!-- Selector de evaluación -->
                    <select id="select-evaluacion-masiva" class="border-0 rounded px-3 py-2 text-sm bg-white/20 text-white">
                        <option value="" class="text-gray-800">-- Evaluación --</option>
                        <?php foreach ($evaluaciones as $eval): ?>
                            <?php if ($eval['estado'] === 'activa'): ?>
                            <option value="<?php echo $eval['id_evaluatio']; ?>" data-curso="<?php echo $eval['id_curso']; ?>" class="text-gray-800">
                                <?php echo htmlspecialchars($eval['nombre']); ?>
                            </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" onclick="enviarNotificacionEvaluacionMasiva()" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                        <i data-lucide="send" class="w-4 h-4"></i> Notificar
                    </button>
                    <div class="w-px h-6 bg-white/30 mx-1"></div>
                    <button type="button" onclick="accionMasivaInscripciones('eliminar')" class="bg-red-500/80 hover:bg-red-500 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar
                    </button>
                    <button type="button" onclick="deseleccionarTodos()" class="bg-white/10 hover:bg-white/20 px-3 py-2 rounded-lg text-sm transition">
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de inscripciones -->
        <div class="overflow-x-auto">
            <table class="w-full" id="tabla-inscripciones">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-2 py-3 text-center w-10">
                            <input type="checkbox" id="checkbox-todos-inscripciones" onchange="toggleSeleccionarTodos(this)" class="w-4 h-4 text-purple-600 rounded cursor-pointer" title="Seleccionar todos">
                        </th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Estudiante</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Curso</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Estado</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Fecha Inicio</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Fecha Fin</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Nota</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Asistencia</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-inscripciones">
                    <?php foreach ($inscripciones as $insc): ?>
                    <tr class="editable-row border-b inscripcion-row" data-id-curso="<?php echo $insc['id_curso']; ?>">
                        <td class="px-2 py-3 text-center">
                            <input type="checkbox" class="checkbox-inscripcion w-4 h-4 text-purple-600 rounded cursor-pointer"
                                   value="<?php echo $insc['id_inscripcion']; ?>"
                                   data-id-curso="<?php echo $insc['id_curso']; ?>"
                                   data-email="<?php echo htmlspecialchars($insc['email'] ?? ''); ?>"
                                   data-dni="<?php echo htmlspecialchars($insc['dni']); ?>"
                                   data-nombre="<?php echo htmlspecialchars($insc['nombre_completo']); ?>"
                                   data-curso="<?php echo htmlspecialchars($insc['nombre_curso']); ?>"
                                   data-codigo-curso="<?php echo htmlspecialchars($insc['codigo_curso']); ?>"
                                   data-estado="<?php echo htmlspecialchars($insc['estado']); ?>"
                                   data-nota="<?php echo $insc['nota_final'] ? number_format($insc['nota_final'], 2) : ''; ?>"
                                   onchange="actualizarSeleccion()">
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium"><?php echo htmlspecialchars($insc['nombre_completo']); ?></div>
                            <div class="text-gray-500 text-xs"><?php echo htmlspecialchars($insc['dni']); ?></div>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium"><?php echo htmlspecialchars($insc['nombre_curso']); ?></div>
                            <div class="text-gray-500 text-xs font-mono"><?php echo htmlspecialchars($insc['codigo_curso']); ?></div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php
                            $estado_class = match($insc['estado']) {
                                'Aprobado' => 'bg-green-100 text-green-800',
                                'En Curso' => 'bg-blue-100 text-blue-800',
                                'Inscrito' => 'bg-cyan-100 text-cyan-800',
                                'Preinscrito' => 'bg-yellow-100 text-yellow-800',
                                'Finalizado' => 'bg-gray-100 text-gray-800',
                                'Desaprobado' => 'bg-red-100 text-red-800',
                                'Abandonado' => 'bg-orange-100 text-orange-800',
                                'Suspendido' => 'bg-pink-100 text-pink-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            ?>
                            <span class="<?php echo $estado_class; ?> px-2 py-1 rounded text-xs font-medium">
                                <?php echo htmlspecialchars($insc['estado']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <?php echo $insc['fecha_inicio'] ? date('d/m/Y', strtotime($insc['fecha_inicio'])) : '-'; ?>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <?php echo $insc['fecha_finalizacion'] ? date('d/m/Y', strtotime($insc['fecha_finalizacion'])) : '-'; ?>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-medium">
                            <?php echo $insc['nota_final'] ? number_format($insc['nota_final'], 2) : '-'; ?>
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            <?php echo $insc['asistencia'] ?: '-'; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="abrirModalEditarInscripcion(<?php echo htmlspecialchars(json_encode($insc)); ?>)" class="text-blue-600 hover:text-blue-800 text-sm mr-1" title="Editar">
                                <i data-lucide="edit" class="w-4 h-4 inline"></i>
                            </button>
                            <button onclick="reenviarNotificacion(<?php echo $insc['id_inscripcion']; ?>, '<?php echo htmlspecialchars($insc['nombre_completo']); ?>')" class="text-green-600 hover:text-green-800 text-sm mr-1" title="Reenviar notificación por email">
                                <i data-lucide="mail" class="w-4 h-4 inline"></i>
                            </button>
                            <?php if (isset($evaluaciones_activas_por_curso[$insc['id_curso']])): ?>
                            <button onclick="notificarEvaluacionAEstudiante(<?php echo $insc['id_inscripcion']; ?>, <?php echo $evaluaciones_activas_por_curso[$insc['id_curso']]['id_evaluatio']; ?>, '<?php echo htmlspecialchars($insc['nombre_completo']); ?>', '<?php echo htmlspecialchars($evaluaciones_activas_por_curso[$insc['id_curso']]['nombre']); ?>')" class="text-purple-600 hover:text-purple-800 text-sm mr-1" title="Notificar evaluación disponible">
                                <i data-lucide="clipboard-check" class="w-4 h-4 inline"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($insc['estado'] === 'Aprobado' && !empty($insc['certificado_disponible_ahora'])): ?>
                            <button onclick="notificarCertificadoDisponible(<?php echo $insc['id_inscripcion']; ?>, '<?php echo htmlspecialchars($insc['nombre_completo']); ?>', '<?php echo htmlspecialchars($insc['nombre_curso']); ?>')" class="text-amber-600 hover:text-amber-800 text-sm mr-1" title="Notificar certificado disponible">
                                <i data-lucide="award" class="w-4 h-4 inline"></i>
                            </button>
                            <?php endif; ?>
                            <button onclick="confirmarEliminarInscripcion(<?php echo $insc['id_inscripcion']; ?>)" class="text-red-600 hover:text-red-800 text-sm" title="Eliminar">
                                <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($inscripciones)): ?>
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No hay inscripciones registradas</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para crear/editar inscripción -->
    <div id="modal-inscripcion" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 id="modal-inscripcion-titulo" class="text-lg font-bold">Nueva Inscripción</h3>
                <button onclick="cerrarModalInscripcion()" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form method="POST" action="?modulo=certificatum" class="p-4 space-y-4">
                <input type="hidden" name="accion" id="modal-inscripcion-accion" value="crear_inscripcion">
                <input type="hidden" name="id_inscripcion" id="modal-inscripcion-id" value="">

                <div class="grid grid-cols-2 gap-4">
                    <!-- Estudiante -->
                    <div class="col-span-2" id="campo-estudiante">
                        <label class="block text-sm font-semibold mb-1">Estudiante *</label>
                        <select name="id_miembro" id="modal-inscripcion-miembro" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required onchange="verificarEmailEstudiante(this)">
                            <option value="">-- Seleccionar estudiante --</option>
                            <?php foreach ($estudiantes as $est): ?>
                                <option value="<?php echo $est['id_miembro']; ?>" data-email="<?php echo htmlspecialchars($est['email'] ?? ''); ?>"><?php echo htmlspecialchars($est['dni'] . ' - ' . $est['nombre_completo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Info estudiante (solo en modo edición) -->
                    <div class="col-span-2 hidden" id="info-estudiante-edicion">
                        <label class="block text-sm font-semibold mb-1">Estudiante</label>
                        <div id="texto-estudiante-edicion" class="bg-gray-100 px-3 py-2 rounded text-sm"></div>
                    </div>

                    <!-- Curso -->
                    <div class="col-span-2" id="campo-curso">
                        <label class="block text-sm font-semibold mb-1">Curso *</label>
                        <select name="id_curso" id="modal-inscripcion-curso" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required onchange="verificarEvaluacionActivaEnCurso(this.value)">
                            <option value="">-- Seleccionar curso --</option>
                            <?php foreach ($cursos as $c): ?>
                                <option value="<?php echo $c['id_curso']; ?>"><?php echo htmlspecialchars($c['codigo_curso'] . ' - ' . $c['nombre_curso']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Info curso (solo en modo edición) -->
                    <div class="col-span-2 hidden" id="info-curso-edicion">
                        <label class="block text-sm font-semibold mb-1">Curso</label>
                        <div id="texto-curso-edicion" class="bg-gray-100 px-3 py-2 rounded text-sm"></div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-semibold mb-1">Estado *</label>
                        <select name="estado" id="modal-inscripcion-estado" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
                            <option value="Inscrito" selected>Inscrito</option>
                            <option value="En Curso">En Curso</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Aprobado">Aprobado</option>
                            <option value="Desaprobado">Desaprobado</option>
                            <option value="Abandonado">Abandonado</option>
                            <option value="Suspendido">Suspendido</option>
                        </select>
                    </div>

                    <!-- Estado Pago -->
                    <div>
                        <label class="block text-sm font-semibold mb-1">Estado Pago</label>
                        <select name="estado_pago" id="modal-inscripcion-pago" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="Pendiente">Pendiente</option>
                            <option value="Parcial">Parcial</option>
                            <option value="Pagado">Pagado</option>
                            <option value="Becado">Becado</option>
                            <option value="Exento">Exento</option>
                        </select>
                    </div>

                    <!-- Fecha Inicio -->
                    <div>
                        <label class="block text-sm font-semibold mb-1">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="modal-inscripcion-fecha-inicio" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>

                    <!-- Fecha Fin -->
                    <div>
                        <label class="block text-sm font-semibold mb-1">Fecha Finalización</label>
                        <input type="date" name="fecha_finalizacion" id="modal-inscripcion-fecha-fin" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>

                    <!-- Nota Final -->
                    <div>
                        <label class="block text-sm font-semibold mb-1">Nota Final</label>
                        <input type="number" name="nota_final" id="modal-inscripcion-nota" step="0.01" min="0" max="10" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="0.00 - 10.00">
                    </div>

                    <!-- Asistencia -->
                    <div>
                        <label class="block text-sm font-semibold mb-1">Asistencia</label>
                        <input type="text" name="asistencia" id="modal-inscripcion-asistencia" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="ej: 95%">
                    </div>

                    <!-- Observaciones -->
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold mb-1">Observaciones</label>
                        <textarea name="observaciones" id="modal-inscripcion-observaciones" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Notas adicionales..."></textarea>
                    </div>

                    <!-- Notificación por email -->
                    <div class="col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-3 mt-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="notificar_estudiante" id="modal-inscripcion-notificar" value="1" checked
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div>
                                <span class="font-medium text-blue-900">Notificar al estudiante por email</span>
                                <p class="text-xs text-blue-700 mt-0.5">Se enviará un email informando que su certificado/constancia está disponible</p>
                            </div>
                        </label>
                        <p id="notificar-sin-email-warning" class="text-xs text-amber-600 mt-2 hidden">
                            <i data-lucide="alert-triangle" class="w-3 h-3 inline"></i>
                            El estudiante seleccionado no tiene email registrado
                        </p>
                    </div>

                    <!-- Notificación de evaluación activa -->
                    <div id="notificar-evaluacion-inscripcion-container" class="col-span-2 bg-purple-50 border border-purple-200 rounded-lg p-3 hidden">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="notificar_evaluacion" id="modal-inscripcion-notificar-eval" value="1"
                                   class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <div>
                                <span class="font-medium text-purple-900">Incluir notificación de evaluación disponible</span>
                                <p id="eval-activa-info" class="text-xs text-purple-700 mt-0.5"></p>
                            </div>
                        </label>
                        <input type="hidden" name="id_evaluatio_notificar" id="modal-inscripcion-id-eval">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="button" onclick="cerrarModalInscripcion()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">Guardar</button>
                </div>
            </form>
        </div>
        </div><!-- Fin sub-matriculas-inscripciones -->

        <!-- Sub-contenido: Asignaciones -->
        <div id="sub-matriculas-asignaciones" class="sub-content-matriculas hidden">
        <!-- Header con búsqueda y filtros -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i data-lucide="briefcase" class="w-6 h-6 text-purple-600"></i>
                Asignaciones Docentes
                <span class="text-sm font-normal text-gray-500">(<?php echo count($asignaciones_docentes); ?> registros)</span>
            </h2>
            <div class="flex flex-wrap gap-2">
                <form method="GET" class="flex gap-2">
                    <input type="hidden" name="modulo" value="certificatum">
                    <select name="estado_asignacion" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Todos los estados</option>
                        <option value="Asignado" <?php echo $filtro_estado_asignacion === 'Asignado' ? 'selected' : ''; ?>>Asignado</option>
                        <option value="En curso" <?php echo $filtro_estado_asignacion === 'En curso' ? 'selected' : ''; ?>>En curso</option>
                        <option value="Completado" <?php echo $filtro_estado_asignacion === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                    </select>
                    <input type="text" name="buscar" placeholder="Buscar..." value="<?php echo htmlspecialchars($buscar); ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40">
                    <button type="submit" class="bg-gray-100 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-200 text-sm">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </form>
                <button onclick="exportarAsignacionesCSV()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm font-medium flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Exportar
                </button>
            </div>
        </div>

        <!-- Barra de acciones masivas asignaciones (oculta por defecto) -->
        <div id="barra-acciones-asignaciones" class="hidden bg-gradient-to-r from-teal-600 to-teal-700 text-white px-4 py-3 rounded-xl mb-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-square" class="w-5 h-5"></i>
                    <span class="font-medium">
                        <span id="count-seleccionados-asig">0</span> asignación(es) seleccionada(s)
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="accionMasivaAsignaciones('exportar')" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                        <i data-lucide="download" class="w-4 h-4"></i> Exportar
                    </button>
                    <button type="button" onclick="accionMasivaAsignaciones('email')" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                        <i data-lucide="mail" class="w-4 h-4"></i> Email
                    </button>
                    <div class="w-px h-6 bg-white/30 mx-1"></div>
                    <button type="button" onclick="accionMasivaAsignaciones('eliminar')" class="bg-red-500/80 hover:bg-red-500 px-3 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar
                    </button>
                    <button type="button" onclick="deseleccionarTodosAsignaciones()" class="bg-white/10 hover:bg-white/20 px-3 py-2 rounded-lg text-sm transition">
                        ✕
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de Asignaciones -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden" id="contenedor-tabla-asignaciones">
            <?php if (empty($asignaciones_docentes)): ?>
                <div class="p-12 text-center">
                    <i data-lucide="briefcase" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">No hay asignaciones de docentes</h3>
                    <p class="text-gray-500 mb-4">Asigná docentes a cursos desde la pestaña "Docentes"</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full" id="tabla-asignaciones">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-center w-10">
                                    <input type="checkbox" id="select-all-asignaciones" onchange="toggleSelectAllAsignaciones()"
                                           class="w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 cursor-pointer">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Docente</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Curso</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Rol</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Documento</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Período</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="tbody-asignaciones">
                            <?php foreach ($asignaciones_docentes as $asig): ?>
                                <?php
                                    $estado_class = match($asig['estado']) {
                                        'Asignado' => 'bg-blue-100 text-blue-700',
                                        'En curso' => 'bg-amber-100 text-amber-700',
                                        'Completado' => 'bg-green-100 text-green-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                    $roles_display = [
                                        'docente' => 'Docente',
                                        'instructor' => 'Instructor/a',
                                        'orador' => 'Orador/a',
                                        'expositor' => 'Expositor/a',
                                        'conferencista' => 'Conferencista',
                                        'facilitador' => 'Facilitador/a',
                                        'tutor' => 'Tutor/a',
                                        'coordinador' => 'Coordinador/a'
                                    ];
                                    $rol_display = $roles_display[$asig['rol']] ?? ucfirst($asig['rol']);
                                ?>
                                <tr class="editable-row">
                                    <td class="px-3 py-3 text-center">
                                        <input type="checkbox" name="asignaciones_seleccionadas[]" value="<?php echo $asig['id_participacion']; ?>"
                                               class="checkbox-asignacion w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500 cursor-pointer"
                                               onchange="updateSelectionAsignaciones()"
                                               data-nombre="<?php echo htmlspecialchars($asig['nombre_completo']); ?>"
                                               data-dni="<?php echo htmlspecialchars($asig['dni']); ?>"
                                               data-email="<?php echo htmlspecialchars($asig['email'] ?? ''); ?>"
                                               data-curso="<?php echo htmlspecialchars($asig['nombre_curso']); ?>"
                                               data-codigo-curso="<?php echo htmlspecialchars($asig['codigo_curso']); ?>"
                                               data-rol="<?php echo htmlspecialchars($rol_display); ?>"
                                               data-estado="<?php echo htmlspecialchars($asig['estado']); ?>">
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($asig['nombre_completo']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($asig['dni']); ?></div>
                                        <?php if (!empty($asig['email'])): ?>
                                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($asig['email']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($asig['nombre_curso']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($asig['codigo_curso']); ?></div>
                                        <?php if (!empty($asig['titulo_participacion'])): ?>
                                            <div class="text-xs text-purple-600 italic"><?php echo htmlspecialchars($asig['titulo_participacion']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                            <?php echo htmlspecialchars($rol_display); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $estado_class; ?>">
                                            <?php echo htmlspecialchars($asig['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php
                                        // Mostrar tipo de documento según estado
                                        $doc_tipo = match($asig['estado']) {
                                            'Asignado' => ['texto' => 'Constancia Asignación', 'class' => 'bg-blue-50 text-blue-600 border-blue-200', 'icon' => 'file-text'],
                                            'En curso' => ['texto' => 'Constancia Participación', 'class' => 'bg-amber-50 text-amber-600 border-amber-200', 'icon' => 'file-text'],
                                            'Completado' => ['texto' => 'Certificado', 'class' => 'bg-green-50 text-green-600 border-green-200', 'icon' => 'award'],
                                            default => ['texto' => '-', 'class' => 'bg-gray-50 text-gray-500', 'icon' => 'file']
                                        };
                                        ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded border text-xs font-medium <?php echo $doc_tipo['class']; ?>">
                                            <i data-lucide="<?php echo $doc_tipo['icon']; ?>" class="w-3 h-3"></i>
                                            <?php echo $doc_tipo['texto']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-600">
                                        <?php if ($asig['fecha_inicio'] || $asig['fecha_fin']): ?>
                                            <?php echo $asig['fecha_inicio'] ? date('d/m/Y', strtotime($asig['fecha_inicio'])) : '?'; ?>
                                            -
                                            <?php echo $asig['fecha_fin'] ? date('d/m/Y', strtotime($asig['fecha_fin'])) : '?'; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">Sin definir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center gap-1">
                                            <button onclick="abrirModalEditarAsignacion(<?php echo htmlspecialchars(json_encode($asig)); ?>)"
                                                    class="text-blue-600 hover:text-blue-800 text-sm" title="Editar asignación">
                                                <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                            </button>
                                            <?php if (!empty($asig['email'])): ?>
                                            <button onclick="reenviarNotificacionDocente(<?php echo $asig['id_participacion']; ?>, '<?php echo htmlspecialchars($asig['nombre_completo']); ?>', '<?php echo htmlspecialchars($asig['estado']); ?>')"
                                                    class="text-green-600 hover:text-green-800 text-sm" title="Reenviar notificación por email">
                                                <i data-lucide="mail" class="w-4 h-4 inline"></i>
                                            </button>
                                            <?php else: ?>
                                            <span class="text-gray-300 text-sm" title="Sin email">
                                                <i data-lucide="mail-x" class="w-4 h-4 inline"></i>
                                            </span>
                                            <?php endif; ?>
                                            <button onclick="confirmarEliminarAsignacion(<?php echo $asig['id_participacion']; ?>, '<?php echo htmlspecialchars($asig['nombre_completo']); ?>')"
                                                    class="text-red-600 hover:text-red-800 text-sm" title="Eliminar asignación">
                                                <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        </div><!-- Fin sub-matriculas-asignaciones -->
    </div><!-- Fin tab-cert-matriculas -->

    <!-- Modal: Editar Asignación Docente -->
    <div id="modal-asignacion" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
            <div class="flex justify-between items-center p-5 border-b">
                <h3 class="text-xl font-bold text-gray-900">Editar Asignación</h3>
                <button onclick="cerrarModalAsignacion()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form id="form-modal-asignacion" method="POST" action="?modulo=certificatum" class="p-5">
                <input type="hidden" name="accion" value="actualizar_participacion">
                <input type="hidden" name="id_participacion" id="modal-asignacion-id">

                <div class="space-y-4">
                    <!-- Info de docente y curso (solo lectura) -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-sm text-gray-600">Docente: <strong id="modal-asignacion-docente"></strong></p>
                        <p class="text-sm text-gray-600">Curso: <strong id="modal-asignacion-curso"></strong></p>
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <select name="rol_participacion" id="modal-asignacion-rol" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="docente">Docente</option>
                            <option value="instructor">Instructor/a</option>
                            <option value="orador">Orador/a</option>
                            <option value="expositor">Expositor/a</option>
                            <option value="conferencista">Conferencista</option>
                            <option value="facilitador">Facilitador/a</option>
                            <option value="tutor">Tutor/a</option>
                            <option value="coordinador">Coordinador/a</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                        <select name="estado_participacion" id="modal-asignacion-estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                            <option value="Asignado">Asignado</option>
                            <option value="En curso">En curso</option>
                            <option value="Completado">Completado</option>
                        </select>
                    </div>

                    <!-- Título de la participación -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título de la Participación</label>
                        <input type="text" name="titulo_participacion" id="modal-asignacion-titulo"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                               placeholder="Ej: Docente titular del módulo de Mediación">
                    </div>

                    <!-- Fechas -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio_participacion" id="modal-asignacion-fecha-inicio"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                            <input type="date" name="fecha_fin_participacion" id="modal-asignacion-fecha-fin"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>

                    <!-- Notificar -->
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="notificar_docente" value="1" id="modal-asignacion-notificar"
                                   class="w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500 mt-0.5">
                            <div>
                                <span class="font-medium text-blue-900">Notificar al docente por email</span>
                                <p class="text-xs text-blue-700 mt-0.5">Se enviará email si cambia a "Completado"</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t mt-4">
                    <button type="button" onclick="cerrarModalAsignacion()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab Content: Evaluaciones (Probatio) -->
    <div id="tab-cert-evaluaciones" class="tab-content-cert p-6">
        <?php if ($ver_estadisticas > 0 && $evaluacion_stats): ?>
            <!-- Subvista: Estadísticas de la evaluación -->
            <div class="mb-4">
                <a href="?modulo=certificatum&tab=evaluaciones" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 text-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver a Evaluaciones
                </a>
            </div>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold">Estadísticas: <?php echo htmlspecialchars($evaluacion_stats['nombre']); ?></h2>
                    <p class="text-sm text-gray-500">
                        Código: <?php echo htmlspecialchars($evaluacion_stats['codigo']); ?>
                    </p>
                </div>
                <a href="?modulo=certificatum&sesiones=<?php echo $ver_estadisticas; ?>" class="bg-amber-500 text-white px-4 py-2 rounded hover:bg-amber-600 text-sm flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4"></i> Ver todas las sesiones
                </a>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-blue-500">
                    <p class="text-3xl font-bold text-blue-600"><?php echo $estadisticas_data['total_sesiones']; ?></p>
                    <p class="text-sm text-gray-600">Iniciaron</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-yellow-500">
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $estadisticas_data['en_progreso']; ?></p>
                    <p class="text-sm text-gray-600">En progreso</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-green-500">
                    <p class="text-3xl font-bold text-green-600"><?php echo $estadisticas_data['completadas']; ?></p>
                    <p class="text-sm text-gray-600">Completaron</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-emerald-500">
                    <p class="text-3xl font-bold text-emerald-600"><?php echo $estadisticas_data['aprobados']; ?></p>
                    <p class="text-sm text-gray-600">Aprobados</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-red-500">
                    <p class="text-3xl font-bold text-red-600"><?php echo $estadisticas_data['desaprobados']; ?></p>
                    <p class="text-sm text-gray-600">Desaprobados</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-indigo-500">
                    <p class="text-3xl font-bold text-indigo-600"><?php echo $estadisticas_data['tasa_completacion']; ?>%</p>
                    <p class="text-sm text-gray-600">Tasa completación</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center border-l-4 border-purple-500">
                    <p class="text-3xl font-bold text-purple-600"><?php echo $estadisticas_data['tasa_aprobacion']; ?>%</p>
                    <p class="text-sm text-gray-600">Tasa aprobación</p>
                </div>
            </div>

            <?php if ($evaluacion_stats['requiere_cierre_cualitativo']): ?>
            <!-- Reflexiones / Cierre Cualitativo -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold">Reflexiones Finales (Cierre Cualitativo)</h3>
                        <p class="text-sm text-gray-500"><?php echo count($reflexiones_lista); ?> respuestas recibidas</p>
                    </div>
                </div>

                <?php if (empty($reflexiones_lista)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i data-lucide="message-square" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>Aún no hay reflexiones finales registradas.</p>
                </div>
                <?php else: ?>
                <div class="divide-y max-h-[600px] overflow-y-auto">
                    <?php foreach ($reflexiones_lista as $idx => $ref): ?>
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($ref['nombre']); ?></span>
                                <span class="text-sm text-gray-500 ml-2"><?php echo htmlspecialchars($ref['email']); ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="<?php echo $ref['aprobado'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> px-2 py-1 rounded text-xs">
                                    <?php echo number_format($ref['porcentaje'], 0); ?>% - <?php echo $ref['aprobado'] ? 'Aprobado' : 'Desaprobado'; ?>
                                </span>
                                <?php if ($ref['fecha']): ?>
                                <span class="text-xs text-gray-400"><?php echo date('d/m/Y H:i', strtotime($ref['fecha'])); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($ref['reflexion']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center mb-6">
                <i data-lucide="info" class="w-6 h-6 text-yellow-600 mx-auto mb-2"></i>
                <p class="text-yellow-800">Esta evaluación no tiene cierre cualitativo habilitado.</p>
                <p class="text-sm text-yellow-600 mt-1">Podés habilitarlo editando la evaluación.</p>
            </div>
            <?php endif; ?>

            <?php if (!empty($preguntas_abiertas)): ?>
            <!-- Respuestas a Preguntas Abiertas -->
            <div class="bg-white rounded-lg shadow mt-6">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold">Respuestas a Preguntas Abiertas</h3>
                    <p class="text-sm text-gray-500"><?php echo count($preguntas_abiertas); ?> preguntas abiertas en esta evaluación</p>
                </div>

                <div class="divide-y">
                    <?php foreach ($preguntas_abiertas as $pregunta): ?>
                    <div class="p-4">
                        <div class="mb-3">
                            <span class="inline-block bg-purple-100 text-purple-800 text-xs font-medium px-2 py-1 rounded mr-2">
                                Pregunta <?php echo $pregunta['orden']; ?>
                            </span>
                            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($pregunta['enunciado']); ?></span>
                        </div>

                        <?php
                        $respuestas_pregunta = $respuestas_abiertas[$pregunta['id_quaestio']] ?? [];
                        ?>

                        <?php if (empty($respuestas_pregunta)): ?>
                        <p class="text-gray-400 text-sm italic ml-4">Sin respuestas aún</p>
                        <?php else: ?>
                        <div class="ml-4 space-y-3 max-h-[400px] overflow-y-auto">
                            <?php foreach ($respuestas_pregunta as $resp): ?>
                            <div class="bg-gray-50 rounded-lg p-3 border-l-4 <?php echo $resp['aprobado'] ? 'border-green-400' : 'border-gray-300'; ?>">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="font-medium text-gray-800"><?php echo htmlspecialchars($resp['nombre']); ?></span>
                                        <span class="text-xs text-gray-500 ml-2"><?php echo htmlspecialchars($resp['email']); ?></span>
                                    </div>
                                    <?php if ($resp['fecha']): ?>
                                    <span class="text-xs text-gray-400"><?php echo date('d/m/Y H:i', strtotime($resp['fecha'])); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($resp['texto']); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2 ml-4"><?php echo count($respuestas_pregunta); ?> respuesta(s)</p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php elseif ($ver_sesiones > 0 && $evaluacion_sesiones): ?>
            <!-- Subvista: Sesiones de la evaluación -->
            <div class="mb-4">
                <a href="?modulo=certificatum&tab=evaluaciones" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 text-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver a Evaluaciones
                </a>
            </div>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold">Sesiones: <?php echo htmlspecialchars($evaluacion_sesiones['nombre']); ?></h2>
                    <p class="text-sm text-gray-500">
                        Código: <?php echo htmlspecialchars($evaluacion_sesiones['codigo']); ?>
                        <span class="ml-4">Total: <?php echo count($sesiones_lista); ?> sesiones</span>
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Estudiante</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Email</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Estado</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Progreso</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Resultado</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold">Fecha</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sesiones_lista as $ses): ?>
                        <tr class="editable-row border-b">
                            <td class="px-4 py-3">
                                <span class="font-medium"><?php echo htmlspecialchars($ses['nombre_completo']); ?></span>
                                <br><span class="text-xs text-gray-500">DNI: <?php echo htmlspecialchars($ses['dni']); ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($ses['email']); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                $estado_ses_colors = [
                                    'iniciada' => 'bg-gray-100 text-gray-800',
                                    'en_progreso' => 'bg-blue-100 text-blue-800',
                                    'completada' => 'bg-green-100 text-green-800',
                                    'abandonada' => 'bg-red-100 text-red-800'
                                ];
                                ?>
                                <span class="<?php echo $estado_ses_colors[$ses['estado']] ?? 'bg-gray-100'; ?> px-2 py-1 rounded text-xs">
                                    <?php echo ucfirst(str_replace('_', ' ', $ses['estado'])); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">
                                <span class="text-gray-600"><?php echo $ses['total_respuestas']; ?> resp.</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($ses['estado'] === 'completada'): ?>
                                    <span class="<?php echo $ses['aprobado'] ? 'text-green-600' : 'text-red-600'; ?> font-semibold">
                                        <?php echo number_format($ses['porcentaje'], 0); ?>%
                                        <?php if ($ses['aprobado']): ?>
                                            <i data-lucide="check-circle" class="w-4 h-4 inline"></i>
                                        <?php else: ?>
                                            <i data-lucide="x-circle" class="w-4 h-4 inline"></i>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?php echo date('d/m/Y H:i', strtotime($ses['fecha_inicio'])); ?>
                                <?php if ($ses['fecha_finalizacion']): ?>
                                    <br><span class="text-xs text-gray-500">Fin: <?php echo date('d/m/Y H:i', strtotime($ses['fecha_finalizacion'])); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="confirmarResetearSesion(<?php echo $ses['id_sessio']; ?>, <?php echo $ver_sesiones; ?>, '<?php echo htmlspecialchars(addslashes($ses['nombre_completo'])); ?>')" class="text-amber-600 hover:text-amber-800 text-sm mr-1" title="Resetear (permite reintentar)">
                                    <i data-lucide="refresh-cw" class="w-4 h-4 inline"></i>
                                </button>
                                <button onclick="confirmarEliminarSesion(<?php echo $ses['id_sessio']; ?>, <?php echo $ver_sesiones; ?>, '<?php echo htmlspecialchars(addslashes($ses['nombre_completo'])); ?>')" class="text-red-600 hover:text-red-800 text-sm" title="Eliminar sesion">
                                    <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($sesiones_lista)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i data-lucide="users" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p>No hay sesiones registradas para esta evaluacion.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Formularios ocultos para acciones de sesión -->
            <form id="form-resetear-sesion" method="POST" class="hidden">
                <input type="hidden" name="accion" value="resetear_sesion">
                <input type="hidden" name="id_sessio" id="reset_id_sessio">
                <input type="hidden" name="id_evaluatio" value="<?php echo $ver_sesiones; ?>">
            </form>
            <form id="form-eliminar-sesion" method="POST" class="hidden">
                <input type="hidden" name="accion" value="eliminar_sesion">
                <input type="hidden" name="id_sessio" id="delete_id_sessio">
                <input type="hidden" name="id_evaluatio" value="<?php echo $ver_sesiones; ?>">
            </form>

        <?php elseif ($ver_preguntas > 0 && $evaluacion_actual): ?>
            <!-- Subvista: Preguntas de la evaluación -->
            <div class="mb-4">
                <a href="?modulo=certificatum&tab=evaluaciones" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 text-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver a Evaluaciones
                </a>
            </div>

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold">Preguntas: <?php echo htmlspecialchars($evaluacion_actual['nombre']); ?></h2>
                    <p class="text-sm text-gray-500">Código: <?php echo htmlspecialchars($evaluacion_actual['codigo']); ?></p>
                </div>
                <button onclick="mostrarModalPregunta(<?php echo $ver_preguntas; ?>)" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                    <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i> Nueva Pregunta
                </button>
            </div>

            <div class="space-y-4">
                <?php foreach ($preguntas_actual as $pregunta): ?>
                <div class="bg-white border rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-bold">
                                    #<?php echo $pregunta['orden']; ?>
                                </span>
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
                                    <?php echo htmlspecialchars($op['letra']); ?>: <?php echo htmlspecialchars(substr($op['texto'], 0, 30)); ?>
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
                        <input type="hidden" name="modulo" value="certificatum">
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
                                <button onclick="copiarEnlaceEval('<?php echo htmlspecialchars($eval['codigo']); ?>')" class="text-gray-400 hover:text-blue-600 ml-2" title="Copiar enlace">
                                    <i data-lucide="copy" class="w-4 h-4 inline"></i>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($eval['nombre']); ?></td>
                            <td class="px-4 py-3 text-sm text-gray-500"><?php echo htmlspecialchars($eval['nombre_curso'] ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center">
                                <a href="?modulo=certificatum&preguntas=<?php echo $eval['id_evaluatio']; ?>" class="text-blue-600 hover:underline">
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
                                <a href="?modulo=certificatum&preguntas=<?php echo $eval['id_evaluatio']; ?>" class="text-purple-600 hover:text-purple-800 text-sm mr-1" title="Preguntas">
                                    <i data-lucide="list" class="w-4 h-4 inline"></i>
                                </a>
                                <button onclick="duplicarEvaluacion(<?php echo $eval['id_evaluatio']; ?>)" class="text-green-600 hover:text-green-800 text-sm mr-1" title="Duplicar">
                                    <i data-lucide="copy" class="w-4 h-4 inline"></i>
                                </button>
                                <?php if ($eval['total_sesiones'] > 0): ?>
                                <a href="?modulo=certificatum&estadisticas=<?php echo $eval['id_evaluatio']; ?>" class="text-indigo-600 hover:text-indigo-800 text-sm mr-1" title="Estadísticas e informes">
                                    <i data-lucide="bar-chart-3" class="w-4 h-4 inline"></i>
                                </a>
                                <a href="?modulo=certificatum&sesiones=<?php echo $eval['id_evaluatio']; ?>" class="text-amber-600 hover:text-amber-800 text-sm mr-1" title="Ver sesiones (<?php echo $eval['total_sesiones']; ?>)">
                                    <i data-lucide="users" class="w-4 h-4 inline"></i>
                                </a>
                                <?php endif; ?>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <label class="block">
                        <span class="text-sm font-semibold">Fecha de inicio</span>
                        <input type="datetime-local" name="fecha_inicio" id="eval_fecha_inicio" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                        <span class="text-xs text-gray-500">Opcional. Antes de esta fecha no se puede acceder.</span>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Fecha límite</span>
                        <input type="datetime-local" name="fecha_fin" id="eval_fecha_fin" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
                        <span class="text-xs text-gray-500">Opcional. Después de esta fecha se cierra automáticamente.</span>
                    </label>
                </div>

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

                <div class="mb-4 p-3 bg-gray-50 rounded border">
                    <label class="block">
                        <span class="text-sm font-semibold">Mínimo de caracteres para preguntas abiertas</span>
                        <p class="text-xs text-gray-500 mb-1">Cantidad mínima de caracteres que deben escribir en las respuestas de desarrollo. Usar 0 para no requerir mínimo.</p>
                        <input type="number" name="minimo_caracteres_abierta" id="eval_min_caracteres_abierta" min="0" class="mt-1 block w-32 border border-gray-300 rounded px-3 py-2" value="50">
                    </label>
                </div>

                <div id="edit-estado-container" class="hidden mb-4">
                    <label class="block">
                        <span class="text-sm font-semibold">Estado</span>
                        <select name="estado" id="eval_estado" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" onchange="toggleNotificarEvaluacion()">
                            <option value="borrador">Borrador</option>
                            <option value="activa">Activa</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                    </label>
                </div>

                <div id="notificar-evaluacion-container" class="hidden mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="notificar_estudiantes" id="eval_notificar" value="1"
                               class="w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500 mt-0.5">
                        <div>
                            <span class="font-medium text-blue-900">Notificar a los estudiantes inscriptos</span>
                            <p class="text-xs text-blue-700 mt-0.5">Se enviará un email informando que la evaluación está disponible</p>
                            <p id="eval_inscriptos_count" class="text-xs text-blue-600 mt-1"></p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="cerrarModalEvaluacion()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
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
                    <span class="text-sm font-semibold">Contexto <span class="text-gray-400 text-xs font-normal">(opcional)</span></span>
                    <textarea name="contexto" id="preg_contexto" rows="3" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Describe la situacion o escenario previo a la pregunta..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Situacion, caso practico o escenario que contextualiza la pregunta.</p>
                </label>

                <label class="block mb-4">
                    <span class="text-sm font-semibold">Consigna <span class="text-red-500">*</span></span>
                    <textarea name="enunciado" id="preg_enunciado" rows="3" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" required minlength="10" placeholder="La pregunta que debe responder el estudiante..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">La pregunta o instruccion que el estudiante debe responder.</p>
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


                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="cerrarModalPregunta()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div><!-- Fin tab-cert-evaluaciones -->

<!-- ============================================================================ -->
<!-- MODAL: NUEVO/EDITAR CURSO (fuera de tabs para visibilidad global) -->
<!-- ============================================================================ -->
<div id="modal-curso" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all my-8 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-t-2xl">
            <div class="flex justify-between items-center">
                <div>
                    <h3 id="modal-curso-titulo" class="text-xl font-bold text-gray-900">Nuevo Curso</h3>
                    <p class="text-sm text-gray-500 mt-1">Los campos con * son obligatorios</p>
                </div>
                <button onclick="cerrarModalCurso()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        <form id="form-modal-curso" method="POST" action="?modulo=certificatum" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="accion" id="modal-curso-accion" value="crear_curso">
            <input type="hidden" name="id_curso" id="modal-curso-id" value="">

            <!-- Sección: Identificación -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i data-lucide="hash" class="w-4 h-4"></i> Identificación
                </h4>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Curso</label>
                    <select name="tipo_curso" id="modal-curso-tipo" onchange="actualizarCodigoSugerido()"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="Curso">Curso</option>
                        <option value="Diplomatura">Diplomatura</option>
                        <option value="Taller">Taller</option>
                        <option value="Seminario">Seminario</option>
                        <option value="Conversatorio">Conversatorio</option>
                        <option value="Capacitación">Capacitación</option>
                        <option value="Certificación">Certificación</option>
                    </select>
                </div>
                <div class="space-y-3">
                    <div id="codigo-sugerido-container" class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-purple-800">Código Sugerido</label>
                            <span id="codigo-sugerido-valor" class="font-mono font-bold text-purple-900 text-lg">--</span>
                        </div>
                        <p class="text-xs text-purple-600 mb-3">Este código se genera automáticamente siguiendo el formato: INST-TIPO-AÑO-SEC</p>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="usar-codigo-personalizado" onchange="toggleCodigoPersonalizado()"
                                   class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <span class="text-sm text-gray-700">Usar código personalizado</span>
                        </label>
                    </div>
                    <div id="codigo-personalizado-container" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código Personalizado *</label>
                        <input type="text" name="codigo_curso" id="modal-curso-codigo"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 uppercase font-mono"
                               placeholder="Ej: SJ-DPA-2024" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g, ''); validarCodigoCurso()" maxlength="50">
                        <p id="codigo-error" class="text-xs text-red-600 mt-1 hidden"></p>
                    </div>
                    <input type="hidden" id="modal-curso-codigo-hidden" name="codigo_curso_sugerido" value="">
                </div>
            </div>

            <!-- Sección: Información General -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Información General
                </h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Curso *</label>
                        <input type="text" name="nombre_curso" id="modal-curso-nombre" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                               placeholder="Ej: Diplomatura en Prácticas de Justicia Restaurativa">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" id="modal-curso-descripcion" rows="3"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                  placeholder="Descripción breve del curso..."></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <input type="text" name="categoria" id="modal-curso-categoria"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Ej: Derecho, Mediación">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad</label>
                            <select name="modalidad" id="modal-curso-modalidad"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="Virtual">Virtual</option>
                                <option value="Presencial">Presencial</option>
                                <option value="Híbrido">Híbrido</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nivel</label>
                            <select name="nivel" id="modal-curso-nivel"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="Todos los niveles">Todos los niveles</option>
                                <option value="Inicial">Inicial</option>
                                <option value="Intermedio">Intermedio</option>
                                <option value="Avanzado">Avanzado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Duración y Estado -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4"></i> Duración y Estado
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Carga Horaria</label>
                        <input type="number" name="carga_horaria" id="modal-curso-horas" min="1" max="9999"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Ej: 90">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duración (semanas)</label>
                        <input type="number" name="duracion_semanas" id="modal-curso-semanas" min="1" max="999"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Ej: 12">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cupo Máximo</label>
                        <input type="number" name="cupo_maximo" id="modal-curso-cupo" min="1"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Sin límite">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select name="activo" id="modal-curso-activo"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" id="modal-curso-fecha-inicio"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fin</label>
                        <input type="date" name="fecha_fin" id="modal-curso-fecha-fin"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad de Emisión <span class="text-xs text-gray-400">(opcional)</span></label>
                    <input type="text" name="ciudad_emision" id="modal-curso-ciudad"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Ej: Buenos Aires, Mar del Plata">
                </div>
            </div>

            <!-- Sección: Template de Certificado -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4"></i> Template de Certificado
                </h4>
                <div id="template-selector" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    <label class="template-option cursor-pointer">
                        <input type="radio" name="id_template" value="" class="hidden" checked>
                        <div class="border-2 border-purple-500 bg-purple-50 rounded-lg p-3 hover:border-purple-400 transition-colors template-card text-center" data-template="">
                            <div class="h-16 bg-gray-100 rounded flex items-center justify-center text-gray-400 mb-2">
                                <i data-lucide="file-check" class="w-8 h-8"></i>
                            </div>
                            <p class="text-xs font-medium text-gray-700">Predeterminado</p>
                        </div>
                    </label>
                </div>
                <div id="template-preview-container" class="mt-4 hidden">
                    <div class="border rounded-lg overflow-hidden bg-gray-100" style="height: 300px;">
                        <iframe id="template-preview-iframe" src="" class="w-full h-full border-0" style="transform: scale(0.5); transform-origin: top left; width: 200%; height: 200%;"></iframe>
                    </div>
                </div>
            </div>

            <!-- Sección: Firmantes del Certificado -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i data-lucide="pen-tool" class="w-4 h-4"></i> Firmantes del Certificado
                    <span class="text-xs font-normal text-gray-500">(Dejar vacío para usar los de la institución)</span>
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Firmante 1 -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-gray-600">Firmante 1 (Principal)</p>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="usar_firmante_1" id="modal-curso-usar-firmante1" value="1" checked
                                       class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                       onchange="toggleFirmanteFields(1, this.checked)">
                                <span class="text-xs text-gray-600">Usar</span>
                            </label>
                        </div>
                        <div id="firmante1-fields" class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Nombre</label>
                                <input type="text" name="firmante_1_nombre" id="modal-curso-firmante1-nombre"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                                       placeholder="Vacío = usar default">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Cargo</label>
                                <input type="text" name="firmante_1_cargo" id="modal-curso-firmante1-cargo"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                                       placeholder="Vacío = usar default">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Firma (opcional)</label>
                                <div id="firma1-preview" class="hidden mb-2 p-2 bg-white rounded border inline-flex items-center gap-2">
                                    <img id="firma1-preview-img" src="" alt="Firma 1" class="max-h-10 max-w-24">
                                    <button type="button" onclick="eliminarFirmaCurso(1)" class="text-red-500 hover:text-red-700"><i data-lucide="x" class="w-4 h-4"></i></button>
                                </div>
                                <input type="file" name="firmante_1_firma" id="modal-curso-firma1" accept="image/png,image/jpeg" class="w-full text-xs">
                                <input type="hidden" name="firmante_1_firma_url" id="modal-curso-firma1-url" value="">
                            </div>
                        </div>
                    </div>
                    <!-- Firmante 2 -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-gray-600">Firmante 2 (Secundario)</p>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="usar_firmante_2" id="modal-curso-usar-firmante2" value="1" checked
                                       class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                       onchange="toggleFirmanteFields(2, this.checked)">
                                <span class="text-xs text-gray-600">Usar</span>
                            </label>
                        </div>
                        <div id="firmante2-fields" class="space-y-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Nombre</label>
                                <input type="text" name="firmante_2_nombre" id="modal-curso-firmante2-nombre"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                                       placeholder="Vacío = usar default">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Cargo</label>
                                <input type="text" name="firmante_2_cargo" id="modal-curso-firmante2-cargo"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                                       placeholder="Vacío = usar default">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Firma (opcional)</label>
                                <div id="firma2-preview" class="hidden mb-2 p-2 bg-white rounded border inline-flex items-center gap-2">
                                    <img id="firma2-preview-img" src="" alt="Firma 2" class="max-h-10 max-w-24">
                                    <button type="button" onclick="eliminarFirmaCurso(2)" class="text-red-500 hover:text-red-700"><i data-lucide="x" class="w-4 h-4"></i></button>
                                </div>
                                <input type="file" name="firmante_2_firma" id="modal-curso-firma2" accept="image/png,image/jpeg" class="w-full text-xs">
                                <input type="hidden" name="firmante_2_firma_url" id="modal-curso-firma2-url" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Demora de Certificado -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4"></i> Disponibilidad del Certificado
                </h4>
                <?php $demora_global = $instance['demora_certificado_horas'] ?? 24; ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center gap-4 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="usar_demora_global" id="modal-curso-demora-global" value="1" checked
                                   class="w-4 h-4 text-purple-600" onchange="toggleDemoraFields('global')">
                            <span class="text-sm text-gray-700">Usar configuración global (<?php echo $demora_global; ?> horas)</span>
                        </label>
                    </div>
                    <div class="flex items-center gap-4 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="usar_demora_global" id="modal-curso-demora-propia" value="0"
                                   class="w-4 h-4 text-purple-600" onchange="toggleDemoraFields('propia')">
                            <span class="text-sm text-gray-700">Configuración específica para este curso</span>
                        </label>
                    </div>
                    <div id="demora-propia-fields" class="hidden mt-3 pl-6 border-l-2 border-purple-200 space-y-3">
                        <select name="demora_tipo" id="modal-curso-demora-tipo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="toggleDemoraInputs()">
                            <option value="inmediato">Inmediatamente al aprobar</option>
                            <option value="horas">Después de X horas</option>
                            <option value="dias">Después de X días</option>
                            <option value="meses">Después de X meses</option>
                            <option value="fecha">En una fecha específica</option>
                        </select>
                        <div id="demora-valor-container" class="hidden">
                            <input type="number" name="demora_valor" id="modal-curso-demora-valor" value="1" min="1" max="365"
                                   class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm text-center">
                            <span id="demora-valor-unidad" class="text-sm text-gray-600">horas</span>
                        </div>
                        <div id="demora-fecha-container" class="hidden">
                            <input type="date" name="demora_fecha" id="modal-curso-demora-fecha"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Competencias -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4"></i> Competencias
                </h4>
                <div id="competencias-container" class="space-y-2 mb-3"></div>
                <div class="flex gap-2">
                    <input type="text" id="nueva-competencia-input"
                           class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="Ej: Mediación de conflictos..."
                           onkeypress="if(event.key === 'Enter') { event.preventDefault(); agregarCompetencia(); }">
                    <button type="button" onclick="agregarCompetencia()"
                            class="px-4 py-2.5 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 font-medium">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </button>
                </div>
                <input type="hidden" name="competencias_json" id="competencias-json" value="[]">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="cerrarModalCurso()" class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium">Cancelar</button>
                <button type="submit" class="bg-purple-600 text-white px-6 py-2.5 rounded-lg hover:bg-purple-700 font-medium flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span id="modal-curso-btn-texto">Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div><!-- Fin modal-curso -->

    <!-- Tab Content: Ayuda -->
    <div id="tab-cert-ayuda" class="tab-content-cert p-6">
        <h2 class="text-xl font-bold mb-4">Ayuda y Documentación</h2>

        <!-- Estados y Documentos Disponibles -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-lg p-5 mb-6">
            <h3 class="font-bold text-purple-900 mb-4 flex items-center gap-2">
                <i data-lucide="file-badge" class="w-5 h-5"></i>
                Estados de Inscripción y Documentos Disponibles
            </h3>
            <p class="text-sm text-purple-800 mb-4">Cada estado de inscripción determina qué documentos puede descargar el estudiante:</p>

            <div class="grid md:grid-cols-2 gap-4">
                <!-- Preinscrito -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-yellow-400">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-bold">PREINSCRITO</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Estudiante registrado pero aún no confirmado.</p>
                    <div class="text-xs text-gray-500">
                        <strong>Documentos:</strong> Constancia de Inscripción
                    </div>
                </div>

                <!-- Inscrito -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-cyan-400">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-cyan-100 text-cyan-800 px-2 py-1 rounded text-xs font-bold">INSCRITO</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Inscripción confirmada, curso aún no comenzado.</p>
                    <div class="text-xs text-gray-500">
                        <strong>Documentos:</strong> Constancia de Alumno Regular
                    </div>
                </div>

                <!-- En Curso -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold">EN CURSO</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Estudiante cursando activamente.</p>
                    <div class="text-xs text-gray-500">
                        <strong>Documentos:</strong> Analítico + Constancia de Alumno Regular
                    </div>
                </div>

                <!-- Finalizado -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-gray-400">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-bold">FINALIZADO</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Curso terminado, pendiente de evaluación final.</p>
                    <div class="text-xs text-gray-500">
                        <strong>Documentos:</strong> Analítico + Constancia de Finalización
                    </div>
                </div>

                <!-- Aprobado -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-green-500">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-bold">APROBADO</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Estudiante aprobó el curso con nota suficiente.</p>
                    <div class="text-xs text-gray-500">
                        <strong>Documentos:</strong> Analítico + <strong class="text-green-700">Certificado de Aprobación</strong>
                    </div>
                </div>

                <!-- Desaprobado -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-red-500">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-bold">DESAPROBADO</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Estudiante no alcanzó los requisitos de aprobación.</p>
                    <div class="text-xs text-gray-500">
                        <strong>Documentos:</strong> Analítico (sin certificado)
                    </div>
                </div>

                <!-- Abandonado -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-orange-500">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-bold">ABANDONADO</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Estudiante abandonó el curso.</p>
                    <div class="text-xs text-gray-500">
                        <strong>Documentos:</strong> Ninguno disponible
                    </div>
                </div>

                <!-- Suspendido -->
                <div class="bg-white rounded-lg p-4 border-l-4 border-pink-500">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-pink-100 text-pink-800 px-2 py-1 rounded text-xs font-bold">SUSPENDIDO</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Inscripción temporalmente suspendida.</p>
                    <div class="text-xs text-gray-500">
                        <strong>Documentos:</strong> Ninguno disponible
                    </div>
                </div>
            </div>
        </div>

        <!-- Formato CSV -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 mb-6">
            <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                Formato CSV para Carga Masiva de Inscripciones
            </h3>
            <p class="text-sm text-blue-800 mb-3">Formato: DNI, Código Curso, Estado, Fecha Inicio, Fecha Fin, Nota, Asistencia</p>
            <div class="bg-white rounded p-3 font-mono text-xs text-gray-700 mb-3">
                25123456, SJ-DPA-2024, En Curso, 15/03/2024, , , <br>
                30987654, SJ-DPA-2024, Aprobado, 10/01/2024, 15/06/2024, 8.5, 95%
            </div>
            <ul class="text-sm text-blue-700 space-y-1 list-disc ml-5">
                <li><strong>DNI:</strong> Sin puntos ni espacios</li>
                <li><strong>Código Curso:</strong> Debe existir en el sistema</li>
                <li><strong>Estado:</strong> Inscrito, En Curso, Finalizado, Aprobado, Desaprobado, Abandonado</li>
                <li><strong>Fechas:</strong> Formato DD/MM/YYYY (opcional)</li>
                <li><strong>Nota:</strong> Número decimal, ej: 8.5 (opcional)</li>
                <li><strong>Asistencia:</strong> Porcentaje, ej: 95% (opcional)</li>
            </ul>
        </div>

        <!-- Formato CSV Cursos -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-5">
            <h3 class="font-bold text-green-900 mb-3 flex items-center gap-2">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                Formato CSV para Carga de Cursos
            </h3>
            <p class="text-sm text-green-800 mb-3">Formato: Código, Nombre, Carga Horaria, Tipo, Categoría</p>
            <div class="bg-white rounded p-3 font-mono text-xs text-gray-700 mb-3">
                SJ-DPA-2024, Diplomatura en Prácticas de Justicia Restaurativa, 90, Diplomatura, Derecho<br>
                SJ-CM-2024, Contratos Modernos, 40, Curso, Derecho
            </div>
            <ul class="text-sm text-green-700 space-y-1 list-disc ml-5">
                <li><strong>Código:</strong> Identificador único del curso (se convierte a mayúsculas)</li>
                <li><strong>Nombre:</strong> Nombre completo del curso</li>
                <li><strong>Carga Horaria:</strong> Número de horas (opcional)</li>
                <li><strong>Tipo:</strong> Curso, Diplomatura, Taller, Seminario, Conversatorio, Capacitación, Certificación (opcional)</li>
                <li><strong>Categoría:</strong> Área temática (opcional)</li>
            </ul>
        </div>
    </div>
</div>

    <!-- Tab Content: Logs de Validaciones -->
    <div id="tab-cert-logs" class="tab-content-cert p-6">
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <i data-lucide="activity" class="w-6 h-6"></i>
            Logs de Validaciones QR
        </h2>

        <!-- Estadísticas Rápidas -->
        <?php if (!empty($stats_validaciones)): ?>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="text-2xl font-bold text-blue-700"><?php echo number_format($stats_validaciones['total_consultas'] ?? 0); ?></div>
                <div class="text-sm text-blue-600">Consultas (30 días)</div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="text-2xl font-bold text-green-700"><?php echo number_format($stats_validaciones['exitosas'] ?? 0); ?></div>
                <div class="text-sm text-green-600">Exitosas</div>
            </div>
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                <div class="text-2xl font-bold text-red-700"><?php echo number_format($stats_validaciones['fallidas'] ?? 0); ?></div>
                <div class="text-sm text-red-600">Fallidas</div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="text-2xl font-bold text-purple-700"><?php echo number_format($stats_validaciones['visitantes_unicos'] ?? 0); ?></div>
                <div class="text-sm text-purple-600">Visitantes Únicos</div>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200">
                <div class="text-2xl font-bold text-amber-700"><?php echo number_format($stats_validaciones['codigos_distintos'] ?? 0); ?></div>
                <div class="text-sm text-amber-600">Códigos Distintos</div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($logs_validaciones)): ?>
            <div class="bg-gray-50 rounded-xl p-8 text-center">
                <i data-lucide="inbox" class="w-16 h-16 mx-auto text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Sin registros aún</h3>
                <p class="text-gray-500">Los logs de validación aparecerán aquí cuando alguien escanee un código QR.</p>
                <p class="text-sm text-gray-400 mt-2">Asegurate de ejecutar el SQL para crear la tabla <code class="bg-gray-200 px-1 rounded">log_validaciones</code>.</p>
            </div>
        <?php else: ?>
            <!-- Tabla de Logs -->
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha/Hora</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Código</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tipo Doc.</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">IP</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Dispositivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($logs_validaciones as $log): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-gray-900"><?php echo date('d/m/Y', strtotime($log['fecha_consulta'])); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('H:i:s', strtotime($log['fecha_consulta'])); ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono"><?php echo htmlspecialchars($log['codigo_validacion']); ?></code>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($log['exitoso']): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i> Válido
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i> No encontrado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <?php
                                    $tipos_display = [
                                        'certificatum_approbationis' => 'Certificado',
                                        'certificatum_doctoris' => 'Cert. Docente',
                                        'testimonium_doctoris' => 'Const. Docente',
                                        'testimonium_regulare' => 'Const. Regular',
                                        'testimonium_completionis' => 'Const. Final',
                                        'testimonium_inscriptionis' => 'Const. Inscripción',
                                        'analyticum' => 'Analítico'
                                    ];
                                    echo $tipos_display[$log['tipo_documento'] ?? ''] ?? ($log['tipo_documento'] ?? '-');
                                    ?>
                                </td>
                                <td class="px-4 py-3">
                                    <code class="text-xs text-gray-600"><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></code>
                                </td>
                                <td class="px-4 py-3">
                                    <?php
                                    $ua = $log['user_agent'] ?? '';
                                    $dispositivo = 'Desconocido';
                                    $icono = 'monitor';
                                    if (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
                                        $dispositivo = 'iOS';
                                        $icono = 'smartphone';
                                    } elseif (stripos($ua, 'Android') !== false) {
                                        $dispositivo = 'Android';
                                        $icono = 'smartphone';
                                    } elseif (stripos($ua, 'Windows') !== false) {
                                        $dispositivo = 'Windows';
                                        $icono = 'monitor';
                                    } elseif (stripos($ua, 'Mac') !== false) {
                                        $dispositivo = 'Mac';
                                        $icono = 'monitor';
                                    } elseif (stripos($ua, 'Linux') !== false) {
                                        $dispositivo = 'Linux';
                                        $icono = 'monitor';
                                    }
                                    ?>
                                    <span class="inline-flex items-center gap-1 text-gray-600" title="<?php echo htmlspecialchars(substr($ua, 0, 100)); ?>">
                                        <i data-lucide="<?php echo $icono; ?>" class="w-4 h-4"></i>
                                        <?php echo $dispositivo; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 bg-gray-50 border-t text-sm text-gray-500">
                    Mostrando últimos <?php echo count($logs_validaciones); ?> registros
                </div>
            </div>
        <?php endif; ?>

        <!-- SEPARADOR -->
        <div class="border-t border-gray-200 my-8"></div>

        <!-- Logs de Accesos a Certificados (Vistas y Descargas) -->
        <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
            <i data-lucide="eye" class="w-6 h-6"></i>
            Accesos a Certificados
        </h2>
        <p class="text-gray-600 mb-4">Registro de quién visualizó o descargó certificados.</p>

        <!-- Estadísticas de Accesos -->
        <?php if (!empty($stats_accesos) && ($stats_accesos['total_accesos'] ?? 0) > 0): ?>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-4 border border-indigo-200">
                <div class="text-2xl font-bold text-indigo-700"><?php echo number_format($stats_accesos['total_accesos'] ?? 0); ?></div>
                <div class="text-sm text-indigo-600">Total (30 días)</div>
            </div>
            <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-xl p-4 border border-cyan-200">
                <div class="text-2xl font-bold text-cyan-700"><?php echo number_format($stats_accesos['vistas_pantalla'] ?? 0); ?></div>
                <div class="text-sm text-cyan-600">Vistas Pantalla</div>
            </div>
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-4 border border-emerald-200">
                <div class="text-2xl font-bold text-emerald-700"><?php echo number_format($stats_accesos['descargas_pdf'] ?? 0); ?></div>
                <div class="text-sm text-emerald-600">Descargas PDF</div>
            </div>
            <div class="bg-gradient-to-br from-violet-50 to-violet-100 rounded-xl p-4 border border-violet-200">
                <div class="text-2xl font-bold text-violet-700"><?php echo number_format($stats_accesos['usuarios_unicos'] ?? 0); ?></div>
                <div class="text-sm text-violet-600">Usuarios Únicos</div>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="text-2xl font-bold text-blue-700"><?php echo number_format($stats_accesos['accesos_estudiantes'] ?? 0); ?></div>
                <div class="text-sm text-blue-600">Estudiantes</div>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="text-2xl font-bold text-orange-700"><?php echo number_format($stats_accesos['accesos_docentes'] ?? 0); ?></div>
                <div class="text-sm text-orange-600">Docentes</div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($logs_accesos)): ?>
            <div class="bg-gray-50 rounded-xl p-8 text-center">
                <i data-lucide="inbox" class="w-16 h-16 mx-auto text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Sin registros aún</h3>
                <p class="text-gray-500">Los logs de acceso aparecerán aquí cuando alguien vea o descargue un certificado.</p>
                <p class="text-sm text-gray-400 mt-2">Asegurate de ejecutar el SQL para crear la tabla <code class="bg-gray-200 px-1 rounded">log_accesos_certificados</code>.</p>
            </div>
        <?php else: ?>
            <!-- Tabla de Accesos -->
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha/Hora</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Persona</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Acción</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tipo Doc.</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Curso</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Idioma</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Dispositivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($logs_accesos as $acceso): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-gray-900"><?php echo date('d/m/Y', strtotime($acceso['fecha_acceso'])); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('H:i:s', strtotime($acceso['fecha_acceso'])); ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($acceso['nombre_persona'] ?? '-'); ?></div>
                                    <div class="text-xs text-gray-500">
                                        DNI: <?php echo htmlspecialchars($acceso['dni']); ?>
                                        <?php if ($acceso['tipo_usuario'] === 'docente'): ?>
                                            <span class="ml-1 text-purple-600">(Docente)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($acceso['tipo_accion'] === 'vista_pantalla'): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-cyan-100 text-cyan-800 rounded-full text-xs font-medium">
                                            <i data-lucide="eye" class="w-3 h-3"></i> Vista
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-medium">
                                            <i data-lucide="download" class="w-3 h-3"></i> PDF
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <?php
                                    $tipos_display_acceso = [
                                        'certificatum_approbationis' => 'Certificado',
                                        'certificatum_completionis' => 'Cert. Finalización',
                                        'certificatum_doctoris' => 'Cert. Docente',
                                        'testimonium_doctoris' => 'Const. Docente',
                                        'testimonium_regulare' => 'Const. Regular',
                                        'testimonium_completionis' => 'Const. Final',
                                        'testimonium_inscriptionis' => 'Const. Inscripción',
                                        'analyticum' => 'Analítico'
                                    ];
                                    echo $tipos_display_acceso[$acceso['tipo_documento'] ?? ''] ?? ($acceso['tipo_documento'] ?? '-');
                                    ?>
                                </td>
                                <td class="px-4 py-3 max-w-xs truncate" title="<?php echo htmlspecialchars($acceso['nombre_curso'] ?? ''); ?>">
                                    <?php echo htmlspecialchars(substr($acceso['nombre_curso'] ?? '-', 0, 30)) . (strlen($acceso['nombre_curso'] ?? '') > 30 ? '...' : ''); ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php
                                    $idiomas_display = [
                                        'es_AR' => ['🇦🇷', 'Español'],
                                        'pt_BR' => ['🇧🇷', 'Português'],
                                        'en_US' => ['🇺🇸', 'English'],
                                        'el_GR' => ['🇬🇷', 'Ελληνικά']
                                    ];
                                    $idioma = $acceso['idioma'] ?? 'es_AR';
                                    $idioma_info = $idiomas_display[$idioma] ?? ['🌐', $idioma];
                                    ?>
                                    <span class="inline-flex items-center gap-1 text-gray-600" title="<?php echo $idioma_info[1]; ?>">
                                        <span class="text-base"><?php echo $idioma_info[0]; ?></span>
                                        <span class="text-xs"><?php echo $idioma; ?></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php
                                    $dispositivo_acceso = $acceso['dispositivo'] ?? 'Otro';
                                    $icono_acceso = 'monitor';
                                    if (in_array($dispositivo_acceso, ['iOS', 'Android', 'Windows Phone'])) {
                                        $icono_acceso = 'smartphone';
                                    }
                                    ?>
                                    <span class="inline-flex items-center gap-1 text-gray-600">
                                        <i data-lucide="<?php echo $icono_acceso; ?>" class="w-4 h-4"></i>
                                        <?php echo $dispositivo_acceso; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 bg-gray-50 border-t text-sm text-gray-500">
                    Mostrando últimos <?php echo count($logs_accesos); ?> registros
                </div>
            </div>
        <?php endif; ?>
    </div>

<!-- ============================================================================ -->
<!-- PANEL DE AYUDA LATERAL (C5) -->
<!-- ============================================================================ -->

<!-- Botón flotante de ayuda -->
<button id="btn-ayuda-flotante" onclick="togglePanelAyuda()"
        class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center z-40 transition-all hover:scale-110 group"
        title="Centro de Ayuda (F1)">
    <i data-lucide="help-circle" class="w-7 h-7"></i>
    <span class="absolute -top-1 -right-1 bg-gray-800 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm opacity-80 group-hover:opacity-100">F1</span>
</button>

<!-- Panel lateral de ayuda -->
<div id="panel-ayuda" class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
    <!-- Header del panel -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 flex-shrink-0">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold flex items-center gap-2">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                Centro de Ayuda
            </h2>
            <button onclick="togglePanelAyuda()" class="p-1 hover:bg-blue-500 rounded transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <!-- Búsqueda -->
        <div class="mt-3 relative">
            <input type="text" id="busqueda-ayuda" placeholder="Buscar en la ayuda..."
                   onkeyup="filtrarAyuda(this.value)"
                   class="w-full px-4 py-2 rounded-lg text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
            <i data-lucide="search" class="w-4 h-4 absolute right-3 top-2.5 text-gray-400"></i>
        </div>
        <!-- Navegación rápida -->
        <div class="mt-3 flex gap-2">
            <button onclick="mostrarSeccionAyuda('general')" class="flex-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 rounded-lg text-xs font-medium transition flex items-center justify-center gap-1">
                <i data-lucide="rocket" class="w-3 h-3"></i>
                Guías
            </button>
            <button onclick="mostrarAyudaContextual()" class="flex-1 px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-medium transition flex items-center justify-center gap-1">
                <i data-lucide="help-circle" class="w-3 h-3"></i>
                Ayuda contextual
            </button>
        </div>
    </div>

    <!-- Indicador de contexto -->
    <div id="ayuda-contexto" class="px-4 py-2 bg-blue-50 border-b text-sm text-blue-700 flex items-center gap-2 flex-shrink-0">
        <i data-lucide="info" class="w-4 h-4"></i>
        <span id="ayuda-contexto-texto">Ayuda general</span>
    </div>

    <!-- Contenido de ayuda (scrolleable) -->
    <div id="ayuda-contenido" class="flex-1 overflow-y-auto p-4">
        <!-- Contenido dinámico según tab activo -->
    </div>

    <!-- Recursos Globales - Siempre visibles -->
    <div class="p-3 bg-gradient-to-r from-slate-50 to-gray-100 border-t flex-shrink-0">
        <p class="text-xs text-gray-500 mb-2 font-medium flex items-center gap-1">
            <i data-lucide="library" class="w-3 h-3"></i> Recursos de ayuda
        </p>
        <div class="grid grid-cols-3 gap-2">
            <button onclick="mostrarAyudaSeccion('faq-certificatum')" class="flex flex-col items-center gap-1 p-2 bg-white rounded-lg border hover:bg-amber-50 hover:border-amber-300 transition group">
                <i data-lucide="help-circle" class="w-4 h-4 text-amber-500 group-hover:text-amber-600"></i>
                <span class="text-xs text-gray-600 group-hover:text-amber-700">FAQ</span>
            </button>
            <button onclick="mostrarAyudaSeccion('glosario-certificatum')" class="flex flex-col items-center gap-1 p-2 bg-white rounded-lg border hover:bg-blue-50 hover:border-blue-300 transition group">
                <i data-lucide="book-open" class="w-4 h-4 text-blue-500 group-hover:text-blue-600"></i>
                <span class="text-xs text-gray-600 group-hover:text-blue-700">Glosario</span>
            </button>
            <button onclick="mostrarAyudaSeccion('errores-certificatum')" class="flex flex-col items-center gap-1 p-2 bg-white rounded-lg border hover:bg-red-50 hover:border-red-300 transition group">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 group-hover:text-red-600"></i>
                <span class="text-xs text-gray-600 group-hover:text-red-700">Errores</span>
            </button>
        </div>
    </div>
    <div class="px-4 py-2 bg-gray-50 border-t flex-shrink-0">
        <a href="mailto:soporte@verumax.com" class="text-xs text-blue-600 hover:underline flex items-center justify-center gap-1">
            <i data-lucide="headphones" class="w-3 h-3"></i> Contactar soporte
        </a>
    </div>
</div>

<!-- Overlay para cerrar panel -->
<div id="overlay-ayuda" onclick="togglePanelAyuda()"
     class="fixed inset-0 bg-black bg-opacity-30 z-40 hidden"></div>

<!-- ============================================================================ -->
<!-- MODAL DE TUTORIAL PASO A PASO -->
<!-- ============================================================================ -->
<div id="modal-tutorial" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="cerrarTutorial()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-6 py-4 rounded-t-2xl">
            <div class="flex justify-between items-center">
                <h3 id="tutorial-titulo" class="text-lg font-bold">Tutorial</h3>
                <button onclick="cerrarTutorial()" class="p-1 hover:bg-white/20 rounded transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <!-- Barra de progreso -->
            <div class="mt-3 bg-white/20 rounded-full h-2">
                <div id="tutorial-progreso" class="bg-white rounded-full h-2 transition-all duration-300" style="width: 0%"></div>
            </div>
            <p id="tutorial-progreso-texto" class="text-xs text-emerald-100 mt-1">Paso 1 de 4</p>
        </div>

        <!-- Contenido del paso -->
        <div id="tutorial-contenido" class="flex-1 overflow-y-auto p-6">
            <!-- Contenido dinámico -->
        </div>

        <!-- Footer con navegación -->
        <div class="px-6 py-4 bg-gray-50 rounded-b-2xl border-t flex justify-between items-center">
            <button id="tutorial-btn-anterior" onclick="tutorialAnterior()" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium transition flex items-center gap-1">
                <i data-lucide="chevron-left" class="w-4 h-4"></i> Anterior
            </button>

            <!-- Dots de navegación -->
            <div id="tutorial-dots" class="flex gap-2">
                <!-- Dots dinámicos -->
            </div>

            <button id="tutorial-btn-siguiente" onclick="tutorialSiguiente()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition flex items-center gap-1">
                Siguiente <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>

<!-- ============================================================================ -->
<!-- WIZARD DE IMPORTACIÓN -->
<!-- ============================================================================ -->
<div id="modal-wizard-import" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        <!-- Header del Wizard -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-6 py-4 flex-shrink-0">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold" id="wizard-titulo">Importar Estudiantes</h2>
                    <p class="text-emerald-100 text-sm" id="wizard-subtitulo">Asistente de importación paso a paso</p>
                </div>
                <button onclick="cerrarWizardImport()" class="text-white/80 hover:text-white p-2 rounded-lg hover:bg-white/10 transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <!-- Indicador de pasos -->
            <div class="flex items-center justify-center mt-4 gap-2">
                <div class="wizard-step-indicator active" data-step="1">
                    <div class="step-circle">1</div>
                    <span class="step-label">Archivo</span>
                </div>
                <div class="step-line"></div>
                <div class="wizard-step-indicator" data-step="2">
                    <div class="step-circle">2</div>
                    <span class="step-label">Mapear</span>
                </div>
                <div class="step-line"></div>
                <div class="wizard-step-indicator" data-step="3">
                    <div class="step-circle">3</div>
                    <span class="step-label">Validar</span>
                </div>
                <div class="step-line"></div>
                <div class="wizard-step-indicator" data-step="4">
                    <div class="step-circle">4</div>
                    <span class="step-label">Importar</span>
                </div>
            </div>
        </div>

        <!-- Contenido de pasos -->
        <div class="flex-1 overflow-y-auto p-6" id="wizard-content">
            <!-- PASO 1: Seleccionar Archivo -->
            <div class="wizard-paso" id="wizard-paso-1">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Paso 1: Seleccionar fuente de datos</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Opción: Subir archivo -->
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-emerald-400 hover:bg-emerald-50/50 transition-all cursor-pointer" id="wizard-dropzone" onclick="document.getElementById('wizard-file-input').click()">
                        <i data-lucide="upload-cloud" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                        <p class="text-gray-700 font-medium mb-1">Subir archivo</p>
                        <p class="text-gray-500 text-sm mb-3">CSV o Excel (.xlsx)</p>
                        <input type="file" id="wizard-file-input" accept=".csv,.xlsx,.xls" class="hidden" onchange="wizardArchivoSeleccionado(this)">
                        <p id="wizard-nombre-archivo" class="text-sm text-emerald-600 font-medium hidden"></p>
                    </div>

                    <!-- Opción: Pegar texto -->
                    <div class="border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all" onclick="wizardMostrarTexto()">
                        <i data-lucide="clipboard-paste" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                        <p class="text-gray-700 font-medium mb-1">Pegar datos</p>
                        <p class="text-gray-500 text-sm">Texto CSV copiado</p>
                    </div>
                </div>

                <!-- Área de texto (oculta inicialmente) -->
                <div id="wizard-texto-area" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pega los datos (CSV, separado por comas o tabs)</label>
                    <textarea id="wizard-texto-input" rows="8" class="w-full border border-gray-300 rounded-lg px-4 py-3 font-mono text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="DNI, Nombre, Apellido, Email&#10;25123456, Juan, Pérez, juan@email.com&#10;30987654, María, Gómez, maria@email.com"></textarea>
                    <p class="text-xs text-gray-500 mt-2">Tip: La primera fila puede contener los nombres de las columnas</p>
                </div>

                <!-- Info de formato -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="lightbulb" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-sm text-amber-800 font-medium">Formato esperado</p>
                            <p class="text-xs text-amber-700 mt-1" id="wizard-formato-info">
                                <strong>Requerido:</strong> DNI, Nombre, Apellido<br>
                                <strong>Opcional:</strong> Email, Teléfono, Ciudad, Provincia, País
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: Mapear Columnas -->
            <div class="wizard-paso hidden" id="wizard-paso-2">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Paso 2: Mapear columnas</h3>
                <p class="text-gray-600 mb-4">Asocia las columnas de tu archivo con los campos del sistema</p>

                <!-- Preview de datos -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4 overflow-x-auto">
                    <p class="text-xs text-gray-500 mb-2 font-medium">Vista previa de datos:</p>
                    <table class="w-full text-sm" id="wizard-preview-table">
                        <thead id="wizard-preview-thead" class="bg-gray-100"></thead>
                        <tbody id="wizard-preview-tbody"></tbody>
                    </table>
                </div>

                <!-- Mapeo de columnas -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700 mb-3">Mapeo de columnas:</p>
                    <div id="wizard-mapeo-container" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-4 text-sm text-gray-600">
                    <input type="checkbox" id="wizard-primera-fila-header" checked class="w-4 h-4 text-emerald-600 rounded">
                    <label for="wizard-primera-fila-header">La primera fila contiene encabezados (no son datos)</label>
                </div>
            </div>

            <!-- PASO 3: Validar -->
            <div class="wizard-paso hidden" id="wizard-paso-3">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Paso 3: Validar datos</h3>

                <!-- Resumen de validación -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-emerald-600" id="wizard-validos">0</p>
                        <p class="text-sm text-emerald-700">Registros válidos</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-amber-600" id="wizard-warnings">0</p>
                        <p class="text-sm text-amber-700">Con advertencias</p>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-red-600" id="wizard-errores">0</p>
                        <p class="text-sm text-red-700">Con errores</p>
                    </div>
                </div>

                <!-- Lista de problemas -->
                <div id="wizard-problemas-container" class="hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">Problemas detectados:</p>
                    <div class="max-h-48 overflow-y-auto bg-gray-50 rounded-lg p-3" id="wizard-problemas-lista">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>

                <!-- Preview de datos validados -->
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Vista previa de datos a importar:</p>
                    <div class="max-h-64 overflow-auto bg-white border border-gray-200 rounded-lg">
                        <table class="w-full text-sm" id="wizard-validacion-table">
                            <thead class="bg-gray-50 sticky top-0" id="wizard-validacion-thead"></thead>
                            <tbody id="wizard-validacion-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PASO 4: Confirmar e Importar -->
            <div class="wizard-paso hidden" id="wizard-paso-4">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Paso 4: Confirmar importación</h3>

                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-6 text-center mb-6">
                    <i data-lucide="check-circle" class="w-16 h-16 text-emerald-500 mx-auto mb-4"></i>
                    <p class="text-xl font-bold text-emerald-800 mb-2">Listo para importar</p>
                    <p class="text-emerald-700">
                        <span id="wizard-resumen-total" class="font-bold">0</span> registros serán procesados
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <p class="text-sm font-medium text-gray-700 mb-3">Resumen de la importación:</p>
                    <ul class="space-y-2 text-sm text-gray-600" id="wizard-resumen-lista">
                        <!-- Se llena dinámicamente -->
                    </ul>
                </div>

                <!-- Selector de curso (solo para inscripciones) -->
                <div id="wizard-opciones-inscripciones" class="hidden mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i data-lucide="book-open" class="w-4 h-4 inline mr-1"></i>
                        Curso para inscribir
                    </label>
                    <select id="wizard-curso-inscripcion" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
                        <option value="">-- Detectar de columna "codigo_curso" --</option>
                        <?php foreach ($cursos as $c): ?>
                            <?php if ($c['activo']): ?>
                            <option value="<?php echo $c['id_curso']; ?>"><?php echo htmlspecialchars($c['codigo_curso'] . ' - ' . $c['nombre_curso']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Si seleccionas un curso, todos los registros se inscribirán a ese curso. Si dejas vacío, usará la columna "codigo_curso" de tus datos.</p>
                </div>

                <!-- Opciones finales -->
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" id="wizard-actualizar-existentes" checked class="w-4 h-4 text-emerald-600 rounded">
                        <div>
                            <p class="font-medium text-gray-800">Actualizar registros existentes</p>
                            <p class="text-xs text-gray-500">Si el DNI ya existe, actualiza los datos</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" id="wizard-omitir-vacios" checked class="w-4 h-4 text-emerald-600 rounded">
                        <div>
                            <p class="font-medium text-gray-800">Omitir campos vacíos</p>
                            <p class="text-xs text-gray-500">No sobrescribir datos existentes con valores vacíos</p>
                        </div>
                    </label>
                </div>

                <!-- Barra de progreso (oculta hasta que se ejecute) -->
                <div id="wizard-progreso-container" class="hidden mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-700">Importando...</p>
                        <p class="text-sm text-gray-500" id="wizard-progreso-texto">0%</p>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-emerald-600 h-3 rounded-full transition-all duration-300" id="wizard-progreso-bar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer con botones de navegación -->
        <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center bg-gray-50 flex-shrink-0">
            <button onclick="wizardPasoAnterior()" id="wizard-btn-anterior" class="px-4 py-2 text-gray-600 hover:text-gray-800 flex items-center gap-2 disabled:opacity-50" disabled>
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Anterior
            </button>
            <div class="flex gap-2">
                <button onclick="cerrarWizardImport()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                    Cancelar
                </button>
                <button onclick="wizardPasoSiguiente()" id="wizard-btn-siguiente" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 flex items-center gap-2 font-medium disabled:opacity-50">
                    Siguiente
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
                <button onclick="wizardEjecutarImport()" id="wizard-btn-importar" class="hidden px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 flex items-center gap-2 font-medium">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    Importar Ahora
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos del Wizard */
    .wizard-step-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .wizard-step-indicator .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s;
    }
    .wizard-step-indicator.active .step-circle {
        background: white;
        color: #059669;
    }
    .wizard-step-indicator.completed .step-circle {
        background: #10b981;
        color: white;
    }
    .wizard-step-indicator .step-label {
        font-size: 11px;
        opacity: 0.7;
    }
    .wizard-step-indicator.active .step-label,
    .wizard-step-indicator.completed .step-label {
        opacity: 1;
    }
    .step-line {
        flex: 1;
        height: 2px;
        background: rgba(255,255,255,0.3);
        margin: 0 8px;
        margin-bottom: 20px;
    }

    /* Tabla de mapeo */
    .mapeo-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px;
        background: #f9fafb;
        border-radius: 8px;
    }
    .mapeo-row .columna-archivo {
        flex: 1;
        font-family: monospace;
        font-size: 13px;
        color: #374151;
    }
    .mapeo-row select {
        flex: 1;
    }
</style>

<script>
    // ============================================================================
    // DATOS DE EVALUACIONES ACTIVAS POR CURSO
    // ============================================================================
    const evaluacionesActivasPorCurso = <?php echo json_encode($evaluaciones_activas_por_curso); ?>;

    // ============================================================================
    // DATOS DE FIRMAS DEL GENERAL (para mostrar como heredadas) - en base64
    // ============================================================================
    <?php
    // Función para convertir imagen a base64 data URI
    function getImageBase64($relativePath) {
        if (empty($relativePath)) return '';
        $filepath = __DIR__ . '/../../' . ltrim($relativePath, '/');
        if (!file_exists($filepath)) return '';
        $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $mimeTypes = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif'];
        $mime = $mimeTypes[$ext] ?? 'image/png';
        $data = base64_encode(file_get_contents($filepath));
        return 'data:' . $mime . ';base64,' . $data;
    }
    ?>
    const firmasGenerales = {
        firma1: <?php echo json_encode($instance['firmante_1_firma_url'] ?? ''); ?>,
        firma2: <?php echo json_encode($instance['firmante_2_firma_url'] ?? ''); ?>,
        firma1Base64: <?php echo json_encode(getImageBase64($instance['firmante_1_firma_url'] ?? '')); ?>,
        firma2Base64: <?php echo json_encode(getImageBase64($instance['firmante_2_firma_url'] ?? '')); ?>
    };

    // ============================================================================
    // SISTEMA DE TOAST Y SCROLL
    // ============================================================================

    function showToast(mensaje, tipo = 'success') {
        console.log('🟢 Mostrando toast:', mensaje);
        console.log('🟢 Ejecutando showToast...');

        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 z-50 transition-all duration-300 ${
            tipo === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
        }`;

        const icon = tipo === 'success'
            ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';

        toast.innerHTML = `${icon}<span class="font-semibold">${mensaje}</span>`;
        document.body.appendChild(toast);

        setTimeout(() => toast.style.opacity = '0', 3000);
        setTimeout(() => toast.remove(), 3500);
    }

    function cambiarTabCert(tabId, clickedBtn = null) {
        console.log('🔵 Cambiando a tab:', tabId);

        // Mapeo de aliases a tabs reales y sus índices de botón
        const aliasToTab = {
            'estudiantes': 'personas',
            'docentes': 'personas',
            'inscripciones': 'matriculas',
            'asignaciones': 'matriculas'
        };

        const tabButtonIndex = {
            'configuracion': 0,
            'personas': 1,
            'cursos': 2,
            'matriculas': 3,
            'evaluaciones': 4
        };

        // Determinar el tab real (si es alias, obtener el padre)
        const realTabId = aliasToTab[tabId] || tabId;
        const originalTabId = tabId; // Guardar para activar sub-tab después

        // Ocultar todos los tabs y desactivar botones
        document.querySelectorAll('.tab-content-cert').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-button-cert').forEach(btn => btn.classList.remove('active'));

        // IMPORTANTE: Ocultar TODOS los sub-contenidos de personas y matrículas
        // para evitar que queden visibles al cambiar de tab
        document.querySelectorAll('.sub-content-personas').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.sub-content-matriculas').forEach(el => el.classList.add('hidden'));

        // Mostrar el tab real
        const tabElement = document.getElementById('tab-cert-' + realTabId);
        if (tabElement) {
            tabElement.classList.add('active');
        } else {
            console.error('❌ No se encontró tab-cert-' + realTabId);
            return;
        }

        // Activar el botón correspondiente
        if (clickedBtn) {
            clickedBtn.classList.add('active');
        } else {
            const index = tabButtonIndex[realTabId];
            if (index !== undefined) {
                const buttons = document.querySelectorAll('.tab-button-cert');
                if (buttons[index]) {
                    buttons[index].classList.add('active');
                }
            }
        }

        // Mostrar el sub-contenido por defecto del tab activo
        if (realTabId === 'personas') {
            // Si es alias (estudiantes/docentes), mostrar ese sub-tab
            // Si es 'personas' directo, mostrar estudiantes por defecto
            const subTab = (originalTabId === 'estudiantes' || originalTabId === 'docentes') ? originalTabId : 'estudiantes';
            cambiarSubTabPersonas(subTab);
        } else if (realTabId === 'matriculas') {
            // Si es alias (inscripciones/asignaciones), mostrar ese sub-tab
            // Si es 'matriculas' directo, mostrar inscripciones por defecto
            const subTab = (originalTabId === 'inscripciones' || originalTabId === 'asignaciones') ? originalTabId : 'inscripciones';
            cambiarSubTabMatriculas(subTab);
        }

        lucide.createIcons();
    }

    // =============================================
    // Función para ver Aprobados (navegar con filtro)
    // =============================================
    function verAprobados() {
        // Redirigir a inscripciones con filtro de estado Aprobado
        window.location.href = '?modulo=certificatum&tab=inscripciones&estado=Aprobado';
    }

    // =============================================
    // ACCIONES MASIVAS - ESTUDIANTES (C4)
    // =============================================

    function toggleSelectAllEstudiantes() {
        const selectAll = document.getElementById('select-all-estudiantes');
        const isChecked = selectAll.checked;

        // Aplicar a todos los checkboxes de estudiantes
        document.querySelectorAll('.checkbox-estudiante').forEach(cb => {
            cb.checked = isChecked;
        });

        updateSelectionEstudiantes();
    }

    function updateSelectionEstudiantes() {
        const checkboxes = document.querySelectorAll('.checkbox-estudiante:checked');
        const count = checkboxes.length;
        const barra = document.getElementById('barra-acciones-estudiantes');
        const countSpan = document.getElementById('count-seleccionados-est');
        const contenedor = document.getElementById('contenedor-tabla-estudiantes');

        countSpan.textContent = count;

        if (count > 0) {
            barra.classList.remove('hidden');
            contenedor.classList.remove('rounded-t-xl');
            contenedor.classList.add('rounded-b-xl', 'border-t-0');
        } else {
            barra.classList.add('hidden');
            contenedor.classList.add('rounded-t-xl');
            contenedor.classList.remove('rounded-b-xl', 'border-t-0');
        }

        // Actualizar estado del checkbox "select all"
        const totalVisibles = document.querySelectorAll('.checkbox-estudiante').length;
        const selectAll = document.getElementById('select-all-estudiantes');
        if (selectAll) {
            selectAll.checked = count > 0 && count === totalVisibles;
            selectAll.indeterminate = count > 0 && count < totalVisibles;
        }

        lucide.createIcons();
    }

    function deseleccionarTodosEstudiantes() {
        document.querySelectorAll('.checkbox-estudiante').forEach(cb => cb.checked = false);
        document.getElementById('select-all-estudiantes').checked = false;
        updateSelectionEstudiantes();
    }

    function getEstudiantesSeleccionados() {
        const seleccionados = [];
        document.querySelectorAll('.checkbox-estudiante:checked').forEach(cb => {
            seleccionados.push({
                id: cb.value,
                nombre: cb.dataset.nombre,
                dni: cb.dataset.dni,
                email: cb.dataset.email
            });
        });
        return seleccionados;
    }

    function accionMasivaEstudiantes(accion) {
        const seleccionados = getEstudiantesSeleccionados();
        if (seleccionados.length === 0) {
            showToast('No hay estudiantes seleccionados', 'error');
            return;
        }

        switch (accion) {
            case 'exportar':
                exportarEstudiantesSeleccionados(seleccionados);
                break;
            case 'email':
                prepararEmailMasivo(seleccionados);
                break;
            case 'eliminar':
                confirmarEliminacionMasiva(seleccionados);
                break;
        }
    }

    function exportarEstudiantesSeleccionados(seleccionados) {
        // Crear CSV con los estudiantes seleccionados
        // BOM UTF-8 para que Excel interprete correctamente los caracteres especiales
        const BOM = '\uFEFF';
        let csv = BOM + 'DNI,Nombre,Email\n';
        seleccionados.forEach(est => {
            csv += `"${est.dni}","${est.nombre}","${est.email}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `estudiantes_seleccionados_${new Date().toISOString().slice(0,10)}.csv`;
        link.click();
        URL.revokeObjectURL(url);

        showToast(`${seleccionados.length} estudiante(s) exportados`, 'success');
    }

    function prepararEmailMasivo(seleccionados) {
        const emails = seleccionados.map(e => e.email).filter(e => e);
        if (emails.length === 0) {
            showToast('Los estudiantes seleccionados no tienen email registrado', 'error');
            return;
        }

        const emailsText = emails.join('; ');

        // Mostrar modal con opciones
        const contenido = `
            <div class="text-left">
                <p class="mb-3 text-gray-600">${emails.length} email(s) encontrado(s):</p>
                <textarea id="emails-para-copiar" class="w-full p-3 border rounded-lg text-sm font-mono bg-gray-50" rows="4" readonly>${emailsText}</textarea>
                <div class="flex gap-2 mt-4">
                    <button onclick="copiarEmailsPortapapeles()" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
                        <i data-lucide="copy" class="w-4 h-4"></i> Copiar emails
                    </button>
                    <button onclick="cerrarModalEmails()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cerrar
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-3">💡 Próximamente: Envío masivo via SendGrid</p>
            </div>
        `;

        mostrarModalGenerico('Enviar Email Masivo', contenido);
    }

    function copiarEmailsPortapapeles() {
        const textarea = document.getElementById('emails-para-copiar');
        textarea.select();
        document.execCommand('copy');
        showToast('Emails copiados al portapapeles', 'success');
    }

    function mostrarModalGenerico(titulo, contenido) {
        // Crear modal dinámico
        const modal = document.createElement('div');
        modal.id = 'modal-generico';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-bold">${titulo}</h3>
                    <button onclick="cerrarModalEmails()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="p-4">
                    ${contenido}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        lucide.createIcons();
    }

    function cerrarModalEmails() {
        const modal = document.getElementById('modal-generico');
        if (modal) modal.remove();
    }

    function confirmarEliminacionMasiva(seleccionados) {
        const nombres = seleccionados.slice(0, 3).map(e => e.nombre).join(', ');
        const mensaje = seleccionados.length > 3
            ? `${nombres} y ${seleccionados.length - 3} más`
            : nombres;

        if (confirm(`¿Estás seguro de eliminar ${seleccionados.length} estudiante(s)?\n\n${mensaje}\n\nEsta acción no se puede deshacer.`)) {
            // Crear formulario para enviar los IDs
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?modulo=certificatum';

            const accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'eliminar_estudiantes_masivo';
            form.appendChild(accionInput);

            seleccionados.forEach(est => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = est.id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    }

    // =============================================
    // Acciones Masivas - DOCENTES
    // =============================================

    function toggleSelectAllDocentes() {
        const selectAll = document.getElementById('select-all-docentes');
        const isChecked = selectAll.checked;
        document.querySelectorAll('.checkbox-docente').forEach(cb => {
            cb.checked = isChecked;
        });
        updateSelectionDocentes();
    }

    function updateSelectionDocentes() {
        const checkboxes = document.querySelectorAll('.checkbox-docente:checked');
        const count = checkboxes.length;
        const barra = document.getElementById('barra-acciones-docentes');
        const countSpan = document.getElementById('count-seleccionados-doc');

        if (count > 0) {
            barra.classList.remove('hidden');
            countSpan.textContent = count;
        } else {
            barra.classList.add('hidden');
        }

        // Actualizar estado del checkbox "seleccionar todos"
        const totalCheckboxes = document.querySelectorAll('.checkbox-docente').length;
        const selectAll = document.getElementById('select-all-docentes');
        if (selectAll) {
            selectAll.checked = count === totalCheckboxes && totalCheckboxes > 0;
            selectAll.indeterminate = count > 0 && count < totalCheckboxes;
        }
    }

    function accionMasivaDocentes(accion) {
        const checkboxes = document.querySelectorAll('.checkbox-docente:checked');
        if (checkboxes.length === 0) {
            showToast('No hay docentes seleccionados', 'error');
            return;
        }

        const seleccionados = Array.from(checkboxes).map(cb => ({
            id: cb.value,
            nombre: cb.dataset.nombre,
            dni: cb.dataset.dni,
            email: cb.dataset.email
        }));

        switch (accion) {
            case 'exportar':
                exportarDocentesSeleccionados(seleccionados);
                break;
            case 'email':
                prepararEmailMasivoDocentes(seleccionados);
                break;
            case 'eliminar':
                confirmarEliminacionMasivaDocentes(seleccionados);
                break;
        }
    }

    function exportarDocentesSeleccionados(seleccionados) {
        const BOM = '\uFEFF';
        let csv = BOM + 'DNI,Nombre,Email\n';
        seleccionados.forEach(doc => {
            csv += `"${doc.dni}","${doc.nombre}","${doc.email}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `docentes_seleccionados_${new Date().toISOString().slice(0,10)}.csv`;
        a.click();
        URL.revokeObjectURL(url);
        showToast(`${seleccionados.length} docente(s) exportado(s)`, 'success');
    }

    function prepararEmailMasivoDocentes(seleccionados) {
        const emailsValidos = seleccionados.filter(doc => doc.email && doc.email.trim() !== '');

        if (emailsValidos.length === 0) {
            showToast('Ninguno de los docentes seleccionados tiene email', 'error');
            return;
        }

        const emailsList = emailsValidos.map(doc => doc.email).join(', ');

        const contenido = `
            <div class="space-y-4">
                <p class="text-sm text-gray-600">${emailsValidos.length} email(s) de ${seleccionados.length} docente(s) seleccionado(s)</p>
                <textarea id="emails-docentes-para-copiar" readonly
                          class="w-full h-32 p-3 border rounded-lg bg-gray-50 text-sm font-mono">${emailsList}</textarea>
                <div class="flex gap-2">
                    <button onclick="copiarEmailsDocentesPortapapeles()"
                            class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center justify-center gap-2">
                        <i data-lucide="copy" class="w-4 h-4"></i> Copiar emails
                    </button>
                    <button onclick="cerrarModalEmails()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cerrar
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-3">💡 Próximamente: Envío masivo via SendGrid</p>
            </div>
        `;

        mostrarModalGenerico('Enviar Email a Docentes', contenido);
    }

    function copiarEmailsDocentesPortapapeles() {
        const textarea = document.getElementById('emails-docentes-para-copiar');
        textarea.select();
        document.execCommand('copy');
        showToast('Emails copiados al portapapeles', 'success');
    }

    function confirmarEliminacionMasivaDocentes(seleccionados) {
        const nombres = seleccionados.slice(0, 3).map(d => d.nombre).join(', ');
        const mensaje = seleccionados.length > 3
            ? `${nombres} y ${seleccionados.length - 3} más`
            : nombres;

        if (confirm(`¿Estás seguro de eliminar ${seleccionados.length} docente(s)?\n\n${mensaje}\n\nEsta acción no se puede deshacer.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?modulo=certificatum';

            const accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'eliminar_docentes_masivo';
            form.appendChild(accionInput);

            seleccionados.forEach(doc => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = doc.id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    }

    // =============================================
    // Acciones Masivas - ASIGNACIONES
    // =============================================

    function toggleSelectAllAsignaciones() {
        const selectAll = document.getElementById('select-all-asignaciones');
        const isChecked = selectAll.checked;
        document.querySelectorAll('.checkbox-asignacion').forEach(cb => {
            cb.checked = isChecked;
        });
        updateSelectionAsignaciones();
    }

    function updateSelectionAsignaciones() {
        const checkboxes = document.querySelectorAll('.checkbox-asignacion:checked');
        const count = checkboxes.length;
        const barra = document.getElementById('barra-acciones-asignaciones');
        const countSpan = document.getElementById('count-seleccionados-asig');

        if (count > 0) {
            barra.classList.remove('hidden');
            countSpan.textContent = count;
        } else {
            barra.classList.add('hidden');
        }

        // Actualizar estado del checkbox "seleccionar todos"
        const totalCheckboxes = document.querySelectorAll('.checkbox-asignacion').length;
        const selectAll = document.getElementById('select-all-asignaciones');
        if (selectAll) {
            selectAll.checked = count === totalCheckboxes && totalCheckboxes > 0;
            selectAll.indeterminate = count > 0 && count < totalCheckboxes;
        }
    }

    function deseleccionarTodosAsignaciones() {
        document.querySelectorAll('.checkbox-asignacion').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('select-all-asignaciones');
        if (selectAll) selectAll.checked = false;
        updateSelectionAsignaciones();
    }

    function accionMasivaAsignaciones(accion) {
        const checkboxes = document.querySelectorAll('.checkbox-asignacion:checked');
        if (checkboxes.length === 0) {
            showToast('No hay asignaciones seleccionadas', 'error');
            return;
        }

        const seleccionados = Array.from(checkboxes).map(cb => ({
            id: cb.value,
            dni: cb.dataset.dni,
            nombre: cb.dataset.nombre,
            email: cb.dataset.email,
            curso: cb.dataset.curso,
            codigoCurso: cb.dataset.codigoCurso,
            rol: cb.dataset.rol,
            estado: cb.dataset.estado
        }));

        switch (accion) {
            case 'exportar':
                exportarAsignacionesSeleccionados(seleccionados);
                break;
            case 'email':
                prepararEmailMasivoAsignaciones(seleccionados);
                break;
            case 'eliminar':
                confirmarEliminacionMasivaAsignaciones(seleccionados);
                break;
        }
    }

    function exportarAsignacionesSeleccionados(seleccionados) {
        const BOM = '\uFEFF';
        let csv = BOM + 'DNI,Nombre,Email,Curso,Código Curso,Rol,Estado\n';
        seleccionados.forEach(a => {
            csv += `"${a.dni}","${a.nombre}","${a.email}","${a.curso}","${a.codigoCurso}","${a.rol}","${a.estado}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const elem = document.createElement('a');
        elem.href = url;
        elem.download = `asignaciones_seleccionadas_${new Date().toISOString().slice(0,10)}.csv`;
        elem.click();
        URL.revokeObjectURL(url);
        showToast(`${seleccionados.length} asignación(es) exportada(s)`, 'success');
    }

    function prepararEmailMasivoAsignaciones(seleccionados) {
        const emailsValidos = seleccionados.filter(a => a.email && a.email.trim() !== '');

        if (emailsValidos.length === 0) {
            showToast('Ninguna asignación seleccionada tiene email', 'error');
            return;
        }

        const emailsList = emailsValidos.map(a => a.email).join(', ');

        const contenido = `
            <div class="space-y-4">
                <p class="text-sm text-gray-600">${emailsValidos.length} email(s) de ${seleccionados.length} asignación(es) seleccionada(s)</p>
                <textarea id="emails-asignaciones-para-copiar" readonly
                          class="w-full h-32 p-3 border rounded-lg bg-gray-50 text-sm font-mono">${emailsList}</textarea>
                <div class="flex gap-2">
                    <button onclick="copiarEmailsAsignacionesPortapapeles()"
                            class="flex-1 bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 flex items-center justify-center gap-2">
                        <i data-lucide="copy" class="w-4 h-4"></i> Copiar emails
                    </button>
                    <button onclick="cerrarModalEmails()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cerrar
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-3">💡 Próximamente: Envío masivo via SendGrid</p>
            </div>
        `;

        mostrarModalGenerico('Enviar Email a Docentes Asignados', contenido);
    }

    function copiarEmailsAsignacionesPortapapeles() {
        const textarea = document.getElementById('emails-asignaciones-para-copiar');
        textarea.select();
        document.execCommand('copy');
        showToast('Emails copiados al portapapeles', 'success');
    }

    function confirmarEliminacionMasivaAsignaciones(seleccionados) {
        const nombres = seleccionados.slice(0, 3).map(a => `${a.nombre} → ${a.codigoCurso}`).join(', ');
        const mensaje = seleccionados.length > 3
            ? `${nombres} y ${seleccionados.length - 3} más`
            : nombres;

        if (confirm(`¿Estás seguro de eliminar ${seleccionados.length} asignación(es)?\n\n${mensaje}\n\nEsta acción no se puede deshacer.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?modulo=certificatum';

            const accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'eliminar_asignaciones_masivo';
            form.appendChild(accionInput);

            seleccionados.forEach(a => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = a.id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    }

    // =============================================
    // Funciones para Sub-tabs
    // =============================================

    function cambiarSubTabPersonas(subTabId) {
        console.log('🔵 Cambiando sub-tab Personas a:', subTabId);
        // Ocultar todos los sub-contenidos
        document.querySelectorAll('.sub-content-personas').forEach(el => el.classList.add('hidden'));
        // Desactivar todos los sub-tabs
        document.querySelectorAll('.sub-tab-personas').forEach(btn => {
            btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        // Mostrar el sub-contenido seleccionado
        const subContent = document.getElementById('sub-personas-' + subTabId);
        if (subContent) {
            subContent.classList.remove('hidden');
        }
        // Activar el sub-tab correspondiente
        document.querySelectorAll('.sub-tab-personas').forEach(btn => {
            if (btn.onclick && btn.onclick.toString().includes(subTabId)) {
                btn.classList.add('active', 'border-blue-500', 'text-blue-600');
                btn.classList.remove('border-transparent', 'text-gray-500');
            }
        });
        lucide.createIcons();
    }

    function cambiarSubTabMatriculas(subTabId) {
        console.log('🔵 Cambiando sub-tab Matrículas a:', subTabId);
        // Ocultar todos los sub-contenidos
        document.querySelectorAll('.sub-content-matriculas').forEach(el => el.classList.add('hidden'));
        // Desactivar todos los sub-tabs
        document.querySelectorAll('.sub-tab-matriculas').forEach(btn => {
            btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        // Mostrar el sub-contenido seleccionado
        const subContent = document.getElementById('sub-matriculas-' + subTabId);
        if (subContent) {
            subContent.classList.remove('hidden');
        }
        // Activar el sub-tab correspondiente
        document.querySelectorAll('.sub-tab-matriculas').forEach(btn => {
            if (btn.onclick && btn.onclick.toString().includes(subTabId)) {
                btn.classList.add('active', 'border-blue-500', 'text-blue-600');
                btn.classList.remove('border-transparent', 'text-gray-500');
            }
        });
        lucide.createIcons();
    }

    // =============================================
    // Funciones para Evaluaciones (Probatio)
    // =============================================

    // Variable para guardar el estado anterior de la evaluación
    let evalEstadoAnterior = '';

    function mostrarModalEvaluacion(idCurso = '') {
        document.getElementById('form-evaluacion').reset();
        document.getElementById('eval_id').value = '';
        document.getElementById('eval_accion').value = 'crear_evaluacion';
        document.getElementById('eval_titulo').textContent = 'Nueva Evaluación';
        document.getElementById('edit-estado-container').classList.add('hidden');
        document.getElementById('notificar-evaluacion-container').classList.add('hidden');
        document.getElementById('cierre-options').style.display = 'none';
        document.getElementById('eval_min_caracteres_abierta').value = 50; // Valor por defecto
        evalEstadoAnterior = '';
        if (idCurso) {
            document.getElementById('eval_id_curso').value = idCurso;
        }
        document.getElementById('modal-evaluacion').classList.remove('hidden');
    }

    function editarEvaluacion(evalData) {
        const data = typeof evalData === 'object' ? evalData : {};
        document.getElementById('eval_id').value = data.id_evaluatio || '';
        document.getElementById('eval_accion').value = 'actualizar_evaluacion';
        document.getElementById('eval_id_curso').value = data.id_curso || '';
        document.getElementById('eval_nombre').value = data.nombre || '';
        document.getElementById('eval_descripcion').value = data.descripcion || '';

        // Fechas (convertir de MySQL datetime a formato datetime-local)
        if (data.fecha_inicio) {
            document.getElementById('eval_fecha_inicio').value = data.fecha_inicio.replace(' ', 'T').slice(0, 16);
        } else {
            document.getElementById('eval_fecha_inicio').value = '';
        }
        if (data.fecha_fin) {
            document.getElementById('eval_fecha_fin').value = data.fecha_fin.replace(' ', 'T').slice(0, 16);
        } else {
            document.getElementById('eval_fecha_fin').value = '';
        }

        document.getElementById('eval_muestra_respuestas').checked = data.muestra_respuestas_correctas == 1;
        document.getElementById('eval_requiere_cierre').checked = data.requiere_cierre_cualitativo == 1;
        document.getElementById('eval_texto_cierre').value = data.texto_cierre_cualitativo || '';
        document.getElementById('eval_min_caracteres_cierre').value = data.minimo_caracteres_cierre || 100;
        document.getElementById('eval_min_caracteres_abierta').value = data.minimo_caracteres_abierta ?? 50;
        document.getElementById('eval_msg_bienvenida').value = data.mensaje_bienvenida || '';
        document.getElementById('eval_msg_finalizacion').value = data.mensaje_finalizacion || '';
        document.getElementById('edit-estado-container').classList.remove('hidden');
        document.getElementById('eval_estado').value = data.estado || 'borrador';

        // Guardar estado anterior y configurar notificación
        evalEstadoAnterior = data.estado || 'borrador';
        document.getElementById('eval_notificar').checked = false;
        toggleNotificarEvaluacion();

        toggleCierreOptions();
        document.getElementById('eval_titulo').textContent = 'Editar Evaluación';
        document.getElementById('modal-evaluacion').classList.remove('hidden');
    }

    function toggleNotificarEvaluacion() {
        const estadoActual = document.getElementById('eval_estado').value;
        const container = document.getElementById('notificar-evaluacion-container');
        const checkbox = document.getElementById('eval_notificar');

        // Mostrar solo si se está activando (cambiando a 'activa' o ya está activa y quiere renotificar)
        if (estadoActual === 'activa') {
            container.classList.remove('hidden');
            // Si está cambiando de otro estado a activa, marcar por defecto
            if (evalEstadoAnterior !== 'activa') {
                checkbox.checked = true;
            }
        } else {
            container.classList.add('hidden');
            checkbox.checked = false;
        }
    }

    function cerrarModalEvaluacion() {
        document.getElementById('modal-evaluacion').classList.add('hidden');
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

    function copiarEnlaceEval(codigo) {
        const enlace = 'https://<?= $institucion ?>.verumax.com/probatio/' + codigo;
        navigator.clipboard.writeText(enlace).then(function() {
            alert('Enlace copiado: ' + enlace);
        }).catch(function() {
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
        document.getElementById('form-pregunta').reset();
        document.getElementById('preg_id').value = '';
        document.getElementById('preg_accion').value = 'crear_pregunta';
        document.getElementById('preg_id_evaluatio').value = idEvaluatio;
        document.getElementById('preg_titulo').textContent = 'Nueva Pregunta';
        document.getElementById('preg_obligatoria').checked = true;
        document.getElementById('preg_contexto').value = '';
        opcionIndex = 0;
        const container = document.getElementById('opciones-container');
        container.innerHTML = '';
        // 2 opciones por defecto (ahora con feedback cada una ocupa mas espacio)
        agregarOpcion();
        agregarOpcion();
        toggleOpcionesContainer();
        document.getElementById('modal-pregunta').classList.remove('hidden');
    }

    function editarPregunta(pregData) {
        const data = typeof pregData === 'object' ? pregData : {};
        document.getElementById('preg_id').value = data.id_quaestio || '';
        document.getElementById('preg_accion').value = 'actualizar_pregunta';
        document.getElementById('preg_id_evaluatio').value = data.id_evaluatio || '';
        document.getElementById('preg_tipo').value = data.tipo || 'multiple_choice';
        document.getElementById('preg_contexto').value = data.contexto || '';
        document.getElementById('preg_enunciado').value = data.enunciado || '';
        document.getElementById('preg_puntos').value = data.puntos || 1;
        document.getElementById('preg_obligatoria').checked = data.es_obligatoria == 1;
        opcionIndex = 0;
        const container = document.getElementById('opciones-container');
        container.innerHTML = '';
        const tipo = data.tipo || 'multiple_choice';
        const opciones = data.opciones;
        if (opciones && tipo !== 'abierta') {
            try {
                const opts = Array.isArray(opciones) ? opciones : JSON.parse(opciones);
                opts.forEach(function(opt) {
                    agregarOpcion(opt.letra, opt.texto, opt.es_correcta, opt.feedback || '');
                });
            } catch (e) {
                console.error('Error parsing opciones:', e);
                agregarOpcion();
                agregarOpcion();
            }
        } else if (tipo !== 'abierta') {
            agregarOpcion();
            agregarOpcion();
        }
        toggleOpcionesContainer();
        document.getElementById('preg_titulo').textContent = 'Editar Pregunta';
        document.getElementById('modal-pregunta').classList.remove('hidden');
    }

    function cerrarModalPregunta() {
        document.getElementById('modal-pregunta').classList.add('hidden');
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

    function confirmarResetearSesion(idSessio, idEvaluatio, nombreEstudiante) {
        if (confirm('¿Resetear la sesión de ' + nombreEstudiante + '?\n\nEsto eliminará todas sus respuestas y le permitirá volver a realizar la evaluación desde el inicio.')) {
            document.getElementById('reset_id_sessio').value = idSessio;
            document.getElementById('form-resetear-sesion').submit();
        }
    }

    function confirmarEliminarSesion(idSessio, idEvaluatio, nombreEstudiante) {
        if (confirm('¿Eliminar completamente la sesión de ' + nombreEstudiante + '?\n\nEsta acción eliminará la sesión y todas sus respuestas. El estudiante podrá iniciar una nueva sesión.')) {
            document.getElementById('delete_id_sessio').value = idSessio;
            document.getElementById('form-eliminar-sesion').submit();
        }
    }

    function toggleOpcionesContainer() {
        const tipo = document.getElementById('preg_tipo').value;
        const container = document.getElementById('opciones-section');
        const esAbierta = tipo === 'abierta';

        if (container) {
            container.style.display = esAbierta ? 'none' : 'block';

            // Quitar/agregar required a los inputs de opciones
            const inputs = container.querySelectorAll('input[name="opcion_texto[]"]');
            inputs.forEach(input => {
                if (esAbierta) {
                    input.removeAttribute('required');
                } else {
                    input.setAttribute('required', 'required');
                }
            });
        }
    }

    function agregarOpcion(letra = '', texto = '', esCorrecta = false, feedback = '') {
        const container = document.getElementById('opciones-container');
        const letras = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        const letraActual = letra || letras[opcionIndex] || String.fromCharCode(65 + opcionIndex);
        const textoEscapado = texto.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const feedbackEscapado = feedback.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const div = document.createElement('div');
        div.className = 'p-3 bg-gray-50 rounded border border-gray-200 mb-2';
        div.innerHTML = `
            <div class="flex items-center gap-2 mb-2">
                <span class="font-bold text-gray-600 w-6 text-lg">${letraActual}</span>
                <input type="hidden" name="opcion_letra[]" value="${letraActual}">
                <input type="text" name="opcion_texto[]" value="${textoEscapado}" class="flex-1 border border-gray-300 rounded px-2 py-1" placeholder="Texto de la opcion ${letraActual}..." required>
                <label class="flex items-center gap-1 text-sm whitespace-nowrap">
                    <input type="checkbox" name="opcion_correcta[]" value="${letraActual}" ${esCorrecta ? 'checked' : ''}>
                    <span>Correcta</span>
                </label>
                <button type="button" onclick="eliminarOpcion(this)" class="text-red-500 hover:text-red-700 p-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="ml-8">
                <textarea name="opcion_feedback[]" rows="2" class="w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Feedback para esta opcion (explicacion de por que es correcta o incorrecta)...">${feedbackEscapado}</textarea>
            </div>
        `;
        container.appendChild(div);
        opcionIndex++;
        lucide.createIcons();
    }

    function eliminarOpcion(btn) {
        const container = document.getElementById('opciones-container');
        if (container.children.length > 2) {
            // Buscar el contenedor padre de la opcion (el div con clase p-3)
            const opcionDiv = btn.closest('.p-3');
            if (opcionDiv) {
                opcionDiv.remove();
            }
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
            const span = opciones[i].querySelector('span.font-bold');
            const hiddenInput = opciones[i].querySelector('input[name="opcion_letra[]"]');
            const checkbox = opciones[i].querySelector('input[name="opcion_correcta[]"]');
            const textInput = opciones[i].querySelector('input[name="opcion_texto[]"]');
            if (span) span.textContent = letras[i];
            if (hiddenInput) hiddenInput.value = letras[i];
            if (checkbox) checkbox.value = letras[i];
            if (textInput) textInput.placeholder = 'Texto de la opcion ' + letras[i] + '...';
        }
        opcionIndex = opciones.length;
    }

    // DOMContentLoaded: Inicialización completa
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔵 DOMContentLoaded ejecutado en Certificatum');

        // Activar tab si viene en $active_tab
        <?php if ($active_tab): ?>
            console.log('🔵 Active tab detectado:', '<?php echo $active_tab; ?>');
            cambiarTabCert('<?php echo $active_tab; ?>');
        <?php endif; ?>

        // Activar tab de evaluaciones si estamos viendo preguntas
        <?php if ($ver_preguntas > 0): ?>
            console.log('🔵 Vista de preguntas, activando tab evaluaciones');
            cambiarTabCert('evaluaciones');
        <?php endif; ?>

        // Activar tab de evaluaciones si estamos viendo sesiones
        <?php if ($ver_sesiones > 0): ?>
            console.log('🔵 Vista de sesiones, activando tab evaluaciones');
            cambiarTabCert('evaluaciones');
        <?php endif; ?>

        // Redirect por JavaScript si hay redirect pendiente (para preguntas)
        <?php if (!empty($redirect_to_preguntas)): ?>
            window.location.href = '?modulo=certificatum&preguntas=<?php echo $redirect_to_preguntas; ?>';
        <?php endif; ?>

        // Inicializar change detection
        initCertificatumFormChangeDetection();

        // Mostrar toast y scroll si hay mensaje
        if (window.certificatumPageMessage) {
            const msg = window.certificatumPageMessage;
            console.log('🟢 Mensaje detectado:', msg);

            setTimeout(() => {
                showToast(msg.mensaje, msg.tipo);
            }, 100);

            if (msg.scrollTo) {
                setTimeout(() => {
                    console.log('🟢 Scroll to:', msg.scrollTo);
                    const elemento = document.getElementById(msg.scrollTo);
                    if (elemento) {
                        console.log('🟢 Elemento encontrado, haciendo scroll');
                        elemento.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        // Agregar highlight temporal
                        elemento.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
                        setTimeout(() => {
                            elemento.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
                        }, 2000);
                    } else {
                        console.log('🔴 Elemento no encontrado:', msg.scrollTo);
                    }
                }, 600);
            }
        }

        // Inicializar paginación de tablas
        initPaginacion();
    });

    // ============================================================================
    // PAGINACIÓN DE TABLAS
    // ============================================================================

    const ITEMS_POR_PAGINA = 25;
    let paginaActualEstudiantes = 1;
    let paginaActualCursos = 1;

    function initPaginacion() {
        // Paginación de Estudiantes
        const tbodyEst = document.getElementById('tbody-estudiantes');
        if (tbodyEst) {
            const filas = tbodyEst.querySelectorAll('tr:not([colspan])');
            const totalEstudiantes = filas.length;

            if (totalEstudiantes > ITEMS_POR_PAGINA) {
                actualizarPaginacionEstudiantes();
            } else {
                // Ocultar paginación si hay pocos elementos
                const paginacionDiv = document.getElementById('paginacion-estudiantes');
                if (paginacionDiv && totalEstudiantes <= ITEMS_POR_PAGINA) {
                    document.getElementById('estudiantes-desde').textContent = totalEstudiantes > 0 ? '1' : '0';
                    document.getElementById('estudiantes-hasta').textContent = totalEstudiantes;
                    document.getElementById('estudiantes-total').textContent = totalEstudiantes;
                    document.getElementById('estudiantes-paginas-total').textContent = '1';
                    // Deshabilitar botones
                    document.getElementById('est-btn-primera').disabled = true;
                    document.getElementById('est-btn-anterior').disabled = true;
                    document.getElementById('est-btn-siguiente').disabled = true;
                    document.getElementById('est-btn-ultima').disabled = true;
                }
            }
        }

        // Paginación de Cursos
        const tbodyCursos = document.getElementById('tbody-cursos');
        if (tbodyCursos) {
            const filasCursos = tbodyCursos.querySelectorAll('tr:not([colspan])');
            const totalCursos = filasCursos.length;

            if (totalCursos > ITEMS_POR_PAGINA) {
                actualizarPaginacionCursos();
            } else {
                const paginacionDiv = document.getElementById('paginacion-cursos');
                if (paginacionDiv && totalCursos <= ITEMS_POR_PAGINA) {
                    document.getElementById('cursos-desde').textContent = totalCursos > 0 ? '1' : '0';
                    document.getElementById('cursos-hasta').textContent = totalCursos;
                    document.getElementById('cursos-total').textContent = totalCursos;
                    document.getElementById('cursos-paginas-total').textContent = '1';
                    document.getElementById('cur-btn-primera').disabled = true;
                    document.getElementById('cur-btn-anterior').disabled = true;
                    document.getElementById('cur-btn-siguiente').disabled = true;
                    document.getElementById('cur-btn-ultima').disabled = true;
                }
            }
        }
    }

    function paginaEstudiantes(accion) {
        const tbody = document.getElementById('tbody-estudiantes');
        if (!tbody) return;

        const filas = Array.from(tbody.querySelectorAll('tr:not([colspan])'));
        const totalFilas = filas.length;
        const totalPaginas = Math.ceil(totalFilas / ITEMS_POR_PAGINA);

        switch(accion) {
            case 'primera': paginaActualEstudiantes = 1; break;
            case 'anterior': paginaActualEstudiantes = Math.max(1, paginaActualEstudiantes - 1); break;
            case 'siguiente': paginaActualEstudiantes = Math.min(totalPaginas, paginaActualEstudiantes + 1); break;
            case 'ultima': paginaActualEstudiantes = totalPaginas; break;
        }

        actualizarPaginacionEstudiantes();
    }

    function actualizarPaginacionEstudiantes() {
        const tbody = document.getElementById('tbody-estudiantes');
        if (!tbody) return;

        const filas = Array.from(tbody.querySelectorAll('tr:not([colspan])'));
        const totalFilas = filas.length;
        const totalPaginas = Math.ceil(totalFilas / ITEMS_POR_PAGINA);

        const inicio = (paginaActualEstudiantes - 1) * ITEMS_POR_PAGINA;
        const fin = Math.min(inicio + ITEMS_POR_PAGINA, totalFilas);

        // Mostrar/ocultar filas
        filas.forEach((fila, index) => {
            fila.style.display = (index >= inicio && index < fin) ? '' : 'none';
        });

        // Actualizar indicadores
        document.getElementById('estudiantes-desde').textContent = totalFilas > 0 ? inicio + 1 : 0;
        document.getElementById('estudiantes-hasta').textContent = fin;
        document.getElementById('estudiantes-total').textContent = totalFilas;
        document.getElementById('estudiantes-pagina-actual').textContent = paginaActualEstudiantes;
        document.getElementById('estudiantes-paginas-total').textContent = totalPaginas;

        // Habilitar/deshabilitar botones
        document.getElementById('est-btn-primera').disabled = paginaActualEstudiantes === 1;
        document.getElementById('est-btn-anterior').disabled = paginaActualEstudiantes === 1;
        document.getElementById('est-btn-siguiente').disabled = paginaActualEstudiantes === totalPaginas;
        document.getElementById('est-btn-ultima').disabled = paginaActualEstudiantes === totalPaginas;
    }

    function paginaCursos(accion) {
        const tbody = document.getElementById('tbody-cursos');
        if (!tbody) return;

        const filas = Array.from(tbody.querySelectorAll('tr:not([colspan])'));
        const totalFilas = filas.length;
        const totalPaginas = Math.ceil(totalFilas / ITEMS_POR_PAGINA);

        switch(accion) {
            case 'primera': paginaActualCursos = 1; break;
            case 'anterior': paginaActualCursos = Math.max(1, paginaActualCursos - 1); break;
            case 'siguiente': paginaActualCursos = Math.min(totalPaginas, paginaActualCursos + 1); break;
            case 'ultima': paginaActualCursos = totalPaginas; break;
        }

        actualizarPaginacionCursos();
    }

    function actualizarPaginacionCursos() {
        const tbody = document.getElementById('tbody-cursos');
        if (!tbody) return;

        const filas = Array.from(tbody.querySelectorAll('tr:not([colspan])'));
        const totalFilas = filas.length;
        const totalPaginas = Math.ceil(totalFilas / ITEMS_POR_PAGINA);

        const inicio = (paginaActualCursos - 1) * ITEMS_POR_PAGINA;
        const fin = Math.min(inicio + ITEMS_POR_PAGINA, totalFilas);

        filas.forEach((fila, index) => {
            fila.style.display = (index >= inicio && index < fin) ? '' : 'none';
        });

        document.getElementById('cursos-desde').textContent = totalFilas > 0 ? inicio + 1 : 0;
        document.getElementById('cursos-hasta').textContent = fin;
        document.getElementById('cursos-total').textContent = totalFilas;
        document.getElementById('cursos-pagina-actual').textContent = paginaActualCursos;
        document.getElementById('cursos-paginas-total').textContent = totalPaginas;

        document.getElementById('cur-btn-primera').disabled = paginaActualCursos === 1;
        document.getElementById('cur-btn-anterior').disabled = paginaActualCursos === 1;
        document.getElementById('cur-btn-siguiente').disabled = paginaActualCursos === totalPaginas;
        document.getElementById('cur-btn-ultima').disabled = paginaActualCursos === totalPaginas;
    }

    // Exponer funciones globalmente
    window.paginaEstudiantes = paginaEstudiantes;
    window.paginaCursos = paginaCursos;

    // ============================================================================
    // FILTROS AVANZADOS - ESTUDIANTES
    // ============================================================================

    function toggleFiltrosEstudiantes() {
        const panel = document.getElementById('panel-filtros-estudiantes');
        const btnText = document.getElementById('btn-filtros-est-text');
        const btnIcon = document.getElementById('btn-filtros-est-icon');

        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            btnText.textContent = 'Ocultar filtros avanzados';
            btnIcon.style.transform = 'rotate(180deg)';
        } else {
            panel.classList.add('hidden');
            btnText.textContent = 'Mostrar filtros avanzados';
            btnIcon.style.transform = 'rotate(0deg)';
        }
        lucide.createIcons();
    }

    function aplicarFiltrosEstudiantes() {
        const tbody = document.getElementById('tbody-estudiantes');
        if (!tbody) return;

        const filtroEstado = document.getElementById('filtro-est-estado').value;
        const filtroCursos = document.getElementById('filtro-est-cursos').value;
        const filtroProgreso = document.getElementById('filtro-est-progreso').value;
        const filtroOrden = document.getElementById('filtro-est-orden').value;

        const filas = Array.from(tbody.querySelectorAll('tr.estudiante-row'));
        let filasVisibles = [];

        filas.forEach(fila => {
            let visible = true;

            // Filtrar por estado
            if (filtroEstado && fila.dataset.estado !== filtroEstado) {
                visible = false;
            }

            // Filtrar por número de cursos
            if (filtroCursos && visible) {
                const totalCursos = parseInt(fila.dataset.totalCursos) || 0;
                if (filtroCursos === '0' && totalCursos !== 0) visible = false;
                if (filtroCursos === '1-3' && (totalCursos < 1 || totalCursos > 3)) visible = false;
                if (filtroCursos === '4+' && totalCursos < 4) visible = false;
            }

            // Filtrar por progreso
            if (filtroProgreso && visible) {
                const aprobados = parseInt(fila.dataset.cursosAprobados) || 0;
                const enCurso = parseInt(fila.dataset.cursosEnCurso) || 0;
                const totalCursos = parseInt(fila.dataset.totalCursos) || 0;

                if (filtroProgreso === 'aprobados' && aprobados === 0) visible = false;
                if (filtroProgreso === 'en-curso' && enCurso === 0) visible = false;
                if (filtroProgreso === 'sin-actividad' && totalCursos > 0) visible = false;
            }

            // Marcar visibilidad
            fila.dataset.filtrado = visible ? 'visible' : 'oculto';
            if (visible) {
                filasVisibles.push(fila);
            }
        });

        // Ordenar filas visibles
        filasVisibles.sort((a, b) => {
            switch(filtroOrden) {
                case 'nombre':
                    return a.dataset.nombre.localeCompare(b.dataset.nombre);
                case 'nombre-desc':
                    return b.dataset.nombre.localeCompare(a.dataset.nombre);
                case 'cursos':
                    return (parseInt(b.dataset.totalCursos) || 0) - (parseInt(a.dataset.totalCursos) || 0);
                case 'cursos-asc':
                    return (parseInt(a.dataset.totalCursos) || 0) - (parseInt(b.dataset.totalCursos) || 0);
                default:
                    return 0;
            }
        });

        // Reordenar en el DOM
        filasVisibles.forEach(fila => tbody.appendChild(fila));

        // Aplicar visibilidad y actualizar paginación
        filas.forEach(fila => {
            if (fila.dataset.filtrado === 'oculto') {
                fila.style.display = 'none';
            }
        });

        // Actualizar contador de filtros
        document.getElementById('filtro-est-count').textContent = filasVisibles.length;

        // Resetear a página 1 y actualizar paginación con filas filtradas
        paginaActualEstudiantes = 1;
        actualizarPaginacionEstudiantesFiltrada(filasVisibles);
    }

    function actualizarPaginacionEstudiantesFiltrada(filasVisibles) {
        const totalFilas = filasVisibles.length;
        const totalPaginas = Math.max(1, Math.ceil(totalFilas / ITEMS_POR_PAGINA));

        const inicio = (paginaActualEstudiantes - 1) * ITEMS_POR_PAGINA;
        const fin = Math.min(inicio + ITEMS_POR_PAGINA, totalFilas);

        filasVisibles.forEach((fila, index) => {
            fila.style.display = (index >= inicio && index < fin) ? '' : 'none';
        });

        document.getElementById('estudiantes-desde').textContent = totalFilas > 0 ? inicio + 1 : 0;
        document.getElementById('estudiantes-hasta').textContent = fin;
        document.getElementById('estudiantes-total').textContent = totalFilas;
        document.getElementById('estudiantes-pagina-actual').textContent = paginaActualEstudiantes;
        document.getElementById('estudiantes-paginas-total').textContent = totalPaginas;

        document.getElementById('est-btn-primera').disabled = paginaActualEstudiantes === 1;
        document.getElementById('est-btn-anterior').disabled = paginaActualEstudiantes === 1;
        document.getElementById('est-btn-siguiente').disabled = paginaActualEstudiantes >= totalPaginas;
        document.getElementById('est-btn-ultima').disabled = paginaActualEstudiantes >= totalPaginas;
    }

    function limpiarFiltrosEstudiantes() {
        document.getElementById('filtro-est-estado').value = '';
        document.getElementById('filtro-est-cursos').value = '';
        document.getElementById('filtro-est-progreso').value = '';
        document.getElementById('filtro-est-orden').value = 'nombre';

        // Mostrar todas las filas y resetear paginación
        const tbody = document.getElementById('tbody-estudiantes');
        const filas = Array.from(tbody.querySelectorAll('tr.estudiante-row'));

        filas.forEach(fila => {
            fila.dataset.filtrado = 'visible';
        });

        paginaActualEstudiantes = 1;
        actualizarPaginacionEstudiantes();

        // Actualizar contador
        document.getElementById('filtro-est-count').textContent = filas.length;
    }

    // Exponer funciones de filtros globalmente
    window.toggleFiltrosEstudiantes = toggleFiltrosEstudiantes;
    window.aplicarFiltrosEstudiantes = aplicarFiltrosEstudiantes;
    window.limpiarFiltrosEstudiantes = limpiarFiltrosEstudiantes;

    // ============================================================================
    // FILTROS AVANZADOS - CURSOS
    // ============================================================================

    function toggleFiltrosCursos() {
        const panel = document.getElementById('panel-filtros-cursos');
        const btnText = document.getElementById('btn-filtros-cur-text');
        const btnIcon = document.getElementById('btn-filtros-cur-icon');

        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            btnText.textContent = 'Ocultar filtros avanzados';
            btnIcon.style.transform = 'rotate(180deg)';
        } else {
            panel.classList.add('hidden');
            btnText.textContent = 'Mostrar filtros avanzados';
            btnIcon.style.transform = 'rotate(0deg)';
        }
        lucide.createIcons();
    }

    function aplicarFiltrosCursos() {
        const tbody = document.getElementById('tbody-cursos');
        if (!tbody) return;

        const filtroEstado = document.getElementById('filtro-cur-estado').value;
        const filtroTipo = document.getElementById('filtro-cur-tipo').value;
        const filtroModalidad = document.getElementById('filtro-cur-modalidad').value;
        const filtroOrden = document.getElementById('filtro-cur-orden').value;

        const filas = Array.from(tbody.querySelectorAll('tr.curso-row'));
        let filasVisibles = [];

        filas.forEach(fila => {
            let visible = true;

            if (filtroEstado) {
                const activo = fila.dataset.activo === '1';
                if (filtroEstado === 'activo' && !activo) visible = false;
                if (filtroEstado === 'inactivo' && activo) visible = false;
            }

            if (filtroTipo && visible) {
                if (fila.dataset.tipo !== filtroTipo) visible = false;
            }

            if (filtroModalidad && visible) {
                if (fila.dataset.modalidad !== filtroModalidad) visible = false;
            }

            fila.dataset.filtrado = visible ? 'visible' : 'oculto';
            if (visible) filasVisibles.push(fila);
        });

        // Ordenar
        filasVisibles.sort((a, b) => {
            switch(filtroOrden) {
                case 'nombre':
                    return a.dataset.nombre.localeCompare(b.dataset.nombre);
                case 'nombre-desc':
                    return b.dataset.nombre.localeCompare(a.dataset.nombre);
                case 'inscripciones':
                    return (parseInt(b.dataset.inscripciones) || 0) - (parseInt(a.dataset.inscripciones) || 0);
                case 'horas':
                    return (parseInt(b.dataset.horas) || 0) - (parseInt(a.dataset.horas) || 0);
                default:
                    return 0;
            }
        });

        filasVisibles.forEach(fila => tbody.appendChild(fila));

        filas.forEach(fila => {
            if (fila.dataset.filtrado === 'oculto') fila.style.display = 'none';
        });

        document.getElementById('filtro-cur-count').textContent = filasVisibles.length;

        paginaActualCursos = 1;
        actualizarPaginacionCursosFiltrada(filasVisibles);
    }

    function actualizarPaginacionCursosFiltrada(filasVisibles) {
        const totalFilas = filasVisibles.length;
        const totalPaginas = Math.max(1, Math.ceil(totalFilas / ITEMS_POR_PAGINA));

        const inicio = (paginaActualCursos - 1) * ITEMS_POR_PAGINA;
        const fin = Math.min(inicio + ITEMS_POR_PAGINA, totalFilas);

        filasVisibles.forEach((fila, index) => {
            fila.style.display = (index >= inicio && index < fin) ? '' : 'none';
        });

        document.getElementById('cursos-desde').textContent = totalFilas > 0 ? inicio + 1 : 0;
        document.getElementById('cursos-hasta').textContent = fin;
        document.getElementById('cursos-total').textContent = totalFilas;
        document.getElementById('cursos-pagina-actual').textContent = paginaActualCursos;
        document.getElementById('cursos-paginas-total').textContent = totalPaginas;

        document.getElementById('cur-btn-primera').disabled = paginaActualCursos === 1;
        document.getElementById('cur-btn-anterior').disabled = paginaActualCursos === 1;
        document.getElementById('cur-btn-siguiente').disabled = paginaActualCursos >= totalPaginas;
        document.getElementById('cur-btn-ultima').disabled = paginaActualCursos >= totalPaginas;
    }

    function limpiarFiltrosCursos() {
        document.getElementById('filtro-cur-estado').value = '';
        document.getElementById('filtro-cur-tipo').value = '';
        document.getElementById('filtro-cur-modalidad').value = '';
        document.getElementById('filtro-cur-orden').value = 'nombre';

        const tbody = document.getElementById('tbody-cursos');
        const filas = Array.from(tbody.querySelectorAll('tr.curso-row'));

        filas.forEach(fila => {
            fila.dataset.filtrado = 'visible';
        });

        paginaActualCursos = 1;
        actualizarPaginacionCursos();

        document.getElementById('filtro-cur-count').textContent = filas.length;
    }

    window.toggleFiltrosCursos = toggleFiltrosCursos;
    window.aplicarFiltrosCursos = aplicarFiltrosCursos;
    window.limpiarFiltrosCursos = limpiarFiltrosCursos;

    // ============================================================================
    // EXPORTAR CSV
    // ============================================================================

    function exportarEstudiantesCSV() {
        const tbody = document.getElementById('tbody-estudiantes');
        if (!tbody) {
            showToast('No se encontró la tabla de estudiantes', 'error');
            return;
        }

        const filas = Array.from(tbody.querySelectorAll('tr.estudiante-row'));
        const filasVisibles = filas.filter(fila => fila.dataset.filtrado !== 'oculto');

        if (filasVisibles.length === 0) {
            showToast('No hay estudiantes para exportar', 'warning');
            return;
        }

        // Cabeceras del CSV
        const cabeceras = ['Nombre', 'DNI', 'Email', 'Estado', 'Cursos Inscritos', 'Cursos Aprobados', 'Cursos en Curso'];

        // Construir filas de datos
        const datosCSV = filasVisibles.map(fila => {
            const celdas = fila.querySelectorAll('td');
            const nombre = fila.dataset.nombre || '';
            const dni = celdas[1]?.textContent?.trim() || '';
            const email = celdas[2]?.textContent?.trim() || '';
            const estado = fila.dataset.estado || '';
            const totalCursos = fila.dataset.totalCursos || '0';
            const cursosAprobados = fila.dataset.cursosAprobados || '0';
            const cursosEnCurso = fila.dataset.cursosEnCurso || '0';

            // Escapar campos que puedan contener comas o comillas
            return [nombre, dni, email, estado, totalCursos, cursosAprobados, cursosEnCurso]
                .map(campo => `"${String(campo).replace(/"/g, '""')}"`)
                .join(',');
        });

        // Generar contenido CSV con BOM para Excel
        const bom = '\uFEFF';
        const contenidoCSV = bom + cabeceras.join(',') + '\n' + datosCSV.join('\n');

        // Crear y descargar archivo
        const blob = new Blob([contenidoCSV], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `estudiantes_${new Date().toISOString().slice(0,10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        showToast(`${filasVisibles.length} estudiantes exportados correctamente`, 'success');
    }

    function exportarCursosCSV() {
        const tbody = document.getElementById('tbody-cursos');
        if (!tbody) {
            showToast('No se encontró la tabla de cursos', 'error');
            return;
        }

        const filas = Array.from(tbody.querySelectorAll('tr.curso-row'));
        const filasVisibles = filas.filter(fila => fila.dataset.filtrado !== 'oculto');

        if (filasVisibles.length === 0) {
            showToast('No hay cursos para exportar', 'warning');
            return;
        }

        // Cabeceras del CSV
        const cabeceras = ['Código', 'Nombre', 'Tipo', 'Modalidad', 'Carga Horaria', 'Estado', 'Inscritos'];

        // Construir filas de datos
        const datosCSV = filasVisibles.map(fila => {
            const celdas = fila.querySelectorAll('td');
            const codigo = celdas[0]?.textContent?.trim() || '';
            const nombre = fila.dataset.nombre || '';
            const tipo = fila.dataset.tipo || '';
            const modalidad = fila.dataset.modalidad || '';
            const cargaHoraria = celdas[4]?.textContent?.trim()?.replace(' hs', '') || '';
            const estado = fila.dataset.estado || '';
            const inscritos = fila.dataset.inscritos || '0';

            // Escapar campos que puedan contener comas o comillas
            return [codigo, nombre, tipo, modalidad, cargaHoraria, estado, inscritos]
                .map(campo => `"${String(campo).replace(/"/g, '""')}"`)
                .join(',');
        });

        // Generar contenido CSV con BOM para Excel
        const bom = '\uFEFF';
        const contenidoCSV = bom + cabeceras.join(',') + '\n' + datosCSV.join('\n');

        // Crear y descargar archivo
        const blob = new Blob([contenidoCSV], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `cursos_${new Date().toISOString().slice(0,10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        showToast(`${filasVisibles.length} cursos exportados correctamente`, 'success');
    }

    window.exportarEstudiantesCSV = exportarEstudiantesCSV;
    window.exportarCursosCSV = exportarCursosCSV;

    function exportarDocentesCSV() {
        const tbody = document.getElementById('tbody-docentes');
        if (!tbody) {
            showToast('No se encontró la tabla de docentes', 'error');
            return;
        }

        const filas = Array.from(tbody.querySelectorAll('tr'));

        if (filas.length === 0) {
            showToast('No hay docentes para exportar', 'warning');
            return;
        }

        // Cabeceras del CSV
        const cabeceras = ['DNI', 'Nombre', 'Email', 'Especialidad', 'Título', 'Participaciones'];

        // Construir filas de datos
        const datosCSV = filas.map(fila => {
            const celdas = fila.querySelectorAll('td');
            const dni = celdas[0]?.textContent?.trim() || '';
            const nombre = celdas[1]?.textContent?.trim().split('\n')[0]?.trim() || '';
            const email = celdas[2]?.textContent?.trim() || '';
            const especialidad = celdas[3]?.textContent?.trim() || '';
            const titulo = celdas[4]?.textContent?.trim() || '';
            const participaciones = celdas[5]?.textContent?.trim()?.replace(/\s+/g, ' ') || '';

            return [dni, nombre, email, especialidad, titulo, participaciones]
                .map(campo => `"${String(campo).replace(/"/g, '""')}"`)
                .join(',');
        });

        // Generar contenido CSV con BOM para Excel
        const bom = '\uFEFF';
        const contenidoCSV = bom + cabeceras.join(',') + '\n' + datosCSV.join('\n');

        // Crear y descargar archivo
        const blob = new Blob([contenidoCSV], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `docentes_${new Date().toISOString().slice(0,10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        showToast(`${filas.length} docentes exportados correctamente`, 'success');
    }

    function exportarInscripcionesCSV() {
        const tbody = document.getElementById('tbody-inscripciones');
        if (!tbody) {
            showToast('No se encontró la tabla de inscripciones', 'error');
            return;
        }

        const filas = Array.from(tbody.querySelectorAll('tr.inscripcion-row'));

        if (filas.length === 0) {
            showToast('No hay inscripciones para exportar', 'warning');
            return;
        }

        // Cabeceras del CSV
        const cabeceras = ['Estudiante', 'DNI', 'Curso', 'Estado', 'Fecha Inicio', 'Fecha Fin', 'Nota', 'Asistencia'];

        // Construir filas de datos
        const datosCSV = filas.map(fila => {
            const celdas = fila.querySelectorAll('td');
            // Celda 0 es checkbox, datos empiezan en celda 1
            const estudianteCell = celdas[1];
            const nombre = estudianteCell?.querySelector('.font-medium')?.textContent?.trim() || '';
            const dni = estudianteCell?.querySelector('.text-gray-500')?.textContent?.trim() || '';
            const curso = celdas[2]?.textContent?.trim().split('\n')[0]?.trim() || '';
            const estado = celdas[3]?.textContent?.trim() || '';
            const fechaInicio = celdas[4]?.textContent?.trim() || '';
            const fechaFin = celdas[5]?.textContent?.trim() || '';
            const nota = celdas[6]?.textContent?.trim() || '';
            const asistencia = celdas[7]?.textContent?.trim() || '';

            return [nombre, dni, curso, estado, fechaInicio, fechaFin, nota, asistencia]
                .map(campo => `"${String(campo).replace(/"/g, '""')}"`)
                .join(',');
        });

        // Generar contenido CSV con BOM para Excel
        const bom = '\uFEFF';
        const contenidoCSV = bom + cabeceras.join(',') + '\n' + datosCSV.join('\n');

        // Crear y descargar archivo
        const blob = new Blob([contenidoCSV], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `inscripciones_${new Date().toISOString().slice(0,10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        showToast(`${filas.length} inscripciones exportadas correctamente`, 'success');
    }

    function exportarAsignacionesCSV() {
        const tbody = document.getElementById('tbody-asignaciones');
        if (!tbody) {
            showToast('No se encontró la tabla de asignaciones', 'error');
            return;
        }

        const filas = Array.from(tbody.querySelectorAll('tr'));

        if (filas.length === 0) {
            showToast('No hay asignaciones para exportar', 'warning');
            return;
        }

        // Cabeceras del CSV
        const cabeceras = ['Docente', 'DNI', 'Curso', 'Rol', 'Estado', 'Período'];

        // Construir filas de datos
        const datosCSV = filas.map(fila => {
            const celdas = fila.querySelectorAll('td');
            const docenteCell = celdas[0];
            const nombre = docenteCell?.querySelector('.font-medium')?.textContent?.trim() || '';
            const dni = docenteCell?.querySelector('.text-gray-500')?.textContent?.trim()?.replace('DNI: ', '') || '';
            const curso = celdas[1]?.textContent?.trim().split('\n')[0]?.trim() || '';
            const rol = celdas[2]?.textContent?.trim() || '';
            const estado = celdas[3]?.textContent?.trim() || '';
            const periodo = celdas[5]?.textContent?.trim()?.replace(/\s+/g, ' ') || '';

            return [nombre, dni, curso, rol, estado, periodo]
                .map(campo => `"${String(campo).replace(/"/g, '""')}"`)
                .join(',');
        });

        // Generar contenido CSV con BOM para Excel
        const bom = '\uFEFF';
        const contenidoCSV = bom + cabeceras.join(',') + '\n' + datosCSV.join('\n');

        // Crear y descargar archivo
        const blob = new Blob([contenidoCSV], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `asignaciones_docentes_${new Date().toISOString().slice(0,10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        showToast(`${filas.length} asignaciones exportadas correctamente`, 'success');
    }

    window.exportarDocentesCSV = exportarDocentesCSV;
    window.exportarInscripcionesCSV = exportarInscripcionesCSV;
    window.exportarAsignacionesCSV = exportarAsignacionesCSV;

    // ============================================================================
    // PANEL DE AYUDA (C5)
    // ============================================================================

    const contenidoAyuda = {
        'configuracion': {
            titulo: 'Configuración de Certificatum',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="configuración colores paleta branding diseño firmas">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="palette" class="w-4 h-4"></i> Paleta de Colores
                        </h3>
                        <p class="text-sm text-green-700">Podés usar la paleta general de la institución o definir colores específicos para los certificados.</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="pen-tool" class="w-4 h-4"></i> Firmas Digitales
                        </h3>
                        <p class="text-sm text-blue-700">Configurá hasta 2 firmantes. Podés heredar las firmas del módulo General o subir firmas específicas para certificados.</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                            <i data-lucide="lightbulb" class="w-4 h-4"></i> Tip
                        </h3>
                        <p class="text-sm text-amber-700">Los cambios en la configuración se reflejan inmediatamente en los certificados generados.</p>
                    </div>
                    <button onclick="abrirTutorial('configurar-certificados')" class="mt-3 w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
            `
        },
        'estudiantes': {
            titulo: 'Gestión de Estudiantes',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="estudiantes alumnos importar exportar dni email">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="user-plus" class="w-4 h-4"></i> Agregar Estudiantes
                        </h3>
                        <p class="text-sm text-green-700">Podés agregar estudiantes manualmente o importarlos masivamente desde un archivo CSV.</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="upload" class="w-4 h-4"></i> Importación Masiva
                        </h3>
                        <p class="text-sm text-blue-700">Formato: DNI, Nombre, Apellido, Email (uno por línea, separado por comas o tabulaciones).</p>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                            <i data-lucide="download" class="w-4 h-4"></i> Exportar
                        </h3>
                        <p class="text-sm text-purple-700">El botón "Exportar" descarga un CSV con los estudiantes visibles (respeta los filtros aplicados).</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i data-lucide="filter" class="w-4 h-4"></i> Filtros
                        </h3>
                        <p class="text-sm text-gray-700">Usá los filtros avanzados para buscar por estado, cantidad de cursos o progreso.</p>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button onclick="abrirTutorial('agregar-estudiante')" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                            Agregar estudiante
                        </button>
                        <button onclick="abrirTutorial('importar-datos')" class="flex-1 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            Importar
                        </button>
                    </div>
                </div>
            `
        },
        'docentes': {
            titulo: 'Gestión de Docentes',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="docentes profesores formadores participaciones asignar curso">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="user-plus" class="w-4 h-4"></i> Agregar Docentes
                        </h3>
                        <p class="text-sm text-green-700">Registrá docentes con su DNI, nombre, email, especialidad y título académico.</p>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                            <i data-lucide="book-plus" class="w-4 h-4"></i> Asignar a Cursos
                        </h3>
                        <p class="text-sm text-purple-700">Usá el botón "Asignar a Curso" para vincular un docente con un curso específico y asignarle un rol.</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                            <i data-lucide="award" class="w-4 h-4"></i> Roles Disponibles
                        </h3>
                        <p class="text-sm text-amber-700">Docente, Instructor/a, Orador/a, Conferencista, Facilitador/a, Tutor/a, Coordinador/a.</p>
                    </div>
                    <button onclick="abrirTutorial('agregar-docente')" class="mt-3 w-full px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
            `
        },
        'cursos': {
            titulo: 'Gestión de Cursos',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="cursos capacitaciones talleres modalidad carga horaria">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Crear Cursos
                        </h3>
                        <p class="text-sm text-green-700">Definí código único, nombre, tipo (curso/taller/diplomatura), modalidad y carga horaria.</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="calendar" class="w-4 h-4"></i> Fechas
                        </h3>
                        <p class="text-sm text-blue-700">Las fechas de inicio y fin determinan automáticamente el estado del curso.</p>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                            <i data-lucide="clock" class="w-4 h-4"></i> Carga Horaria
                        </h3>
                        <p class="text-sm text-purple-700">La carga horaria aparece en los certificados y analíticos generados.</p>
                    </div>
                    <button onclick="abrirTutorial('crear-curso')" class="mt-3 w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
            `
        },
        'inscripciones': {
            titulo: 'Gestión de Inscripciones',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="inscripciones matrículas notas asistencia estado aprobado">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="user-check" class="w-4 h-4"></i> Inscribir Estudiantes
                        </h3>
                        <p class="text-sm text-green-700">Vinculá estudiantes a cursos. Podés hacerlo individual o masivamente.</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="clipboard-check" class="w-4 h-4"></i> Estados
                        </h3>
                        <p class="text-sm text-blue-700"><strong>Inscrito</strong> → <strong>Cursando</strong> → <strong>Aprobado</strong>/<strong>Desaprobado</strong>/<strong>Abandonado</strong></p>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                            <i data-lucide="star" class="w-4 h-4"></i> Notas y Asistencia
                        </h3>
                        <p class="text-sm text-amber-700">Registrá la nota final (0-10) y porcentaje de asistencia para cada inscripción.</p>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                            <i data-lucide="mail" class="w-4 h-4"></i> Notificaciones
                        </h3>
                        <p class="text-sm text-purple-700">Seleccioná inscripciones y enviá notificaciones de evaluación masivamente.</p>
                    </div>
                    <button onclick="abrirTutorial('inscribir-estudiante')" class="mt-3 w-full px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
            `
        },
        'asignaciones': {
            titulo: 'Asignaciones de Docentes',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="asignaciones docentes participaciones certificados constancias">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="briefcase" class="w-4 h-4"></i> Estados de Participación
                        </h3>
                        <p class="text-sm text-blue-700"><strong>Asignado</strong> → <strong>En curso</strong> → <strong>Completado</strong></p>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="file-text" class="w-4 h-4"></i> Documentos
                        </h3>
                        <p class="text-sm text-green-700">
                            <strong>Asignado:</strong> Constancia de Asignación<br>
                            <strong>En curso:</strong> Constancia de Participación<br>
                            <strong>Completado:</strong> Certificado de Participación
                        </p>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                            <i data-lucide="lightbulb" class="w-4 h-4"></i> Tip
                        </h3>
                        <p class="text-sm text-amber-700">El certificado final solo está disponible cuando el estado es "Completado".</p>
                    </div>
                    <button onclick="abrirTutorial('asignar-docente')" class="mt-3 w-full px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
            `
        },
        'evaluaciones': {
            titulo: 'Sistema de Evaluaciones',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="evaluaciones exámenes preguntas sesiones resultados">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="file-question" class="w-4 h-4"></i> Crear Evaluaciones
                        </h3>
                        <p class="text-sm text-green-700">Definí evaluaciones con preguntas de opción múltiple vinculadas a cursos.</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="link" class="w-4 h-4"></i> Compartir Enlace
                        </h3>
                        <p class="text-sm text-blue-700">Copiá el código de evaluación y compartilo con los estudiantes para que rindan online.</p>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                            <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Resultados
                        </h3>
                        <p class="text-sm text-purple-700">Visualizá sesiones, progreso y resultados de cada estudiante.</p>
                    </div>
                    <div class="space-y-2 mt-3">
                        <button onclick="abrirTutorial('crear-evaluacion')" class="w-full px-3 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                            Paso a paso: Crear evaluación
                        </button>
                        <button onclick="abrirTutorial('notificar-estudiantes')" class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                            Paso a paso: Notificar estudiantes
                        </button>
                    </div>
                </div>
            `
        },
        'actividad': {
            titulo: 'Logs de Actividad',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="logs actividad validaciones qr accesos auditoría">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="qr-code" class="w-4 h-4"></i> Validaciones QR
                        </h3>
                        <p class="text-sm text-blue-700">Registro de cada vez que alguien escanea un código QR de certificado.</p>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="shield-check" class="w-4 h-4"></i> Auditoría
                        </h3>
                        <p class="text-sm text-green-700">Los logs incluyen IP, dispositivo, fecha/hora y resultado de la validación.</p>
                    </div>
                </div>
            `
        },
        'general': {
            titulo: 'Bienvenido a Certificatum',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="certificatum certificados inicio bienvenido">
                    <div class="bg-gradient-to-br from-green-50 to-blue-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="award" class="w-4 h-4"></i> ¿Qué es Certificatum?
                        </h3>
                        <p class="text-sm text-gray-700">Sistema de gestión de certificados académicos digitales con validación QR.</p>
                    </div>

                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-300 rounded-lg p-4">
                        <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                            <i data-lucide="rocket" class="w-4 h-4"></i> Guías de Inicio
                        </h3>
                        <div class="space-y-2 mt-3">
                            <button onclick="mostrarAyudaSeccion('guia-primer-certificado')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                                <i data-lucide="target" class="w-4 h-4 text-green-600"></i>
                                Crear mi primer certificado
                            </button>
                            <button onclick="mostrarAyudaSeccion('guia-importacion')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                                <i data-lucide="upload" class="w-4 h-4 text-blue-600"></i>
                                Importar estudiantes masivamente
                            </button>
                            <button onclick="mostrarAyudaSeccion('guia-inscripciones')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                                <i data-lucide="user-plus" class="w-4 h-4 text-purple-600"></i>
                                Formas de inscribir estudiantes
                            </button>
                            <button onclick="mostrarAyudaSeccion('guia-emails')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                                <i data-lucide="mail" class="w-4 h-4 text-indigo-600"></i>
                                Enviar certificados por email
                            </button>
                            <button onclick="mostrarAyudaSeccion('guia-docentes')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                                <i data-lucide="graduation-cap" class="w-4 h-4 text-amber-600"></i>
                                Certificados para docentes
                            </button>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-300 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="book-open" class="w-4 h-4"></i> Recursos de Ayuda
                        </h3>
                        <div class="space-y-2 mt-3">
                            <button onclick="mostrarAyudaSeccion('faq-certificatum')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-blue-50 text-sm transition flex items-center gap-2">
                                <i data-lucide="help-circle" class="w-4 h-4 text-amber-600"></i>
                                Preguntas Frecuentes (FAQ)
                            </button>
                            <button onclick="mostrarAyudaSeccion('glosario-certificatum')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-blue-50 text-sm transition flex items-center gap-2">
                                <i data-lucide="book" class="w-4 h-4 text-blue-600"></i>
                                Glosario de Términos
                            </button>
                            <button onclick="mostrarAyudaSeccion('errores-certificatum')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-blue-50 text-sm transition flex items-center gap-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600"></i>
                                Errores Comunes y Soluciones
                            </button>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-3">Navegación rápida:</h3>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li class="flex items-center gap-2"><i data-lucide="settings" class="w-4 h-4 text-gray-400"></i> <strong>Configuración:</strong> Colores y firmas</li>
                            <li class="flex items-center gap-2"><i data-lucide="users" class="w-4 h-4 text-blue-400"></i> <strong>Estudiantes:</strong> Gestión de alumnos</li>
                            <li class="flex items-center gap-2"><i data-lucide="graduation-cap" class="w-4 h-4 text-purple-400"></i> <strong>Docentes:</strong> Formadores y roles</li>
                            <li class="flex items-center gap-2"><i data-lucide="book-open" class="w-4 h-4 text-green-400"></i> <strong>Cursos:</strong> Capacitaciones</li>
                            <li class="flex items-center gap-2"><i data-lucide="clipboard-list" class="w-4 h-4 text-amber-400"></i> <strong>Inscripciones:</strong> Matrículas</li>
                            <li class="flex items-center gap-2"><i data-lucide="file-check" class="w-4 h-4 text-teal-400"></i> <strong>Evaluaciones:</strong> Exámenes online</li>
                        </ul>
                    </div>
                </div>
            `
        },
        'guia-primer-certificado': {
            titulo: '🎯 Crear mi Primer Certificado',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="primer certificado tutorial inicio comenzar paso flujo">
                    <div class="bg-gradient-to-r from-green-100 to-emerald-100 border border-green-300 rounded-lg p-4">
                        <h3 class="font-bold text-green-800 text-lg mb-1">Guía Completa</h3>
                        <p class="text-sm text-green-700">Seguí estos pasos para generar tu primer certificado de aprobación.</p>
                    </div>

                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <h4 class="font-bold text-blue-800">Paso 1: Crear un Curso</h4>
                        <p class="text-sm text-gray-600 mt-1">Andá a <strong>Cursos</strong> → <strong>Nuevo Curso</strong></p>
                        <ul class="text-xs text-gray-500 mt-2 space-y-1">
                            <li>• Ingresá un código único (ej: CURSO-001)</li>
                            <li>• Nombre del curso</li>
                            <li>• Tipo: Curso, Taller, Diplomatura, etc.</li>
                            <li>• Carga horaria (aparece en el certificado)</li>
                            <li>• Fechas de inicio y fin</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-purple-500 pl-4 py-2">
                        <h4 class="font-bold text-purple-800">Paso 2: Agregar un Estudiante</h4>
                        <p class="text-sm text-gray-600 mt-1">Andá a <strong>Personas</strong> → <strong>Estudiantes</strong> → <strong>Nuevo</strong></p>
                        <ul class="text-xs text-gray-500 mt-2 space-y-1">
                            <li>• DNI (identificador único)</li>
                            <li>• Nombre y Apellido</li>
                            <li>• Email (para enviar certificado)</li>
                            <li>• Género (para textos con género gramatical)</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-amber-500 pl-4 py-2">
                        <h4 class="font-bold text-amber-800">Paso 3: Inscribir al Estudiante</h4>
                        <p class="text-sm text-gray-600 mt-1">Andá a <strong>Matrículas</strong> → <strong>Inscripciones</strong> → <strong>Nueva</strong></p>
                        <ul class="text-xs text-gray-500 mt-2 space-y-1">
                            <li>• Seleccioná el estudiante</li>
                            <li>• Seleccioná el curso</li>
                            <li>• Estado inicial: "Inscrito"</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-green-500 pl-4 py-2">
                        <h4 class="font-bold text-green-800">Paso 4: Aprobar al Estudiante</h4>
                        <p class="text-sm text-gray-600 mt-1">En la inscripción, hacé clic en <strong>Editar</strong></p>
                        <ul class="text-xs text-gray-500 mt-2 space-y-1">
                            <li>• Cambiá estado a <strong>"Aprobado"</strong></li>
                            <li>• Ingresá nota final (ej: 8)</li>
                            <li>• Ingresá % asistencia (ej: 90)</li>
                            <li>• Guardá los cambios</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-teal-500 pl-4 py-2">
                        <h4 class="font-bold text-teal-800">Paso 5: Ver/Descargar Certificado</h4>
                        <p class="text-sm text-gray-600 mt-1">En la inscripción aprobada:</p>
                        <ul class="text-xs text-gray-500 mt-2 space-y-1">
                            <li>• Clic en <strong>"Ver Certificado"</strong> para previsualizar</li>
                            <li>• Clic en <strong>"Descargar PDF"</strong></li>
                            <li>• El certificado incluye código QR de validación</li>
                        </ul>
                    </div>

                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 mt-4">
                        <p class="text-sm text-emerald-800"><strong>¡Listo!</strong> Ya generaste tu primer certificado. El estudiante puede validarlo escaneando el QR.</p>
                    </div>
                </div>
            `
        },
        'guia-importacion': {
            titulo: '📥 Importar Estudiantes Masivamente',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="importar masivo csv excel estudiantes carga bulk">
                    <div class="bg-gradient-to-r from-blue-100 to-indigo-100 border border-blue-300 rounded-lg p-4">
                        <h3 class="font-bold text-blue-800 text-lg mb-1">Importación Masiva</h3>
                        <p class="text-sm text-blue-700">Cargá cientos de estudiantes en segundos desde un archivo.</p>
                    </div>

                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h4 class="font-bold text-gray-800 mb-2">📋 Formato del Archivo</h4>
                        <p class="text-xs text-gray-600 mb-2">CSV o texto con columnas separadas por coma o tabulación:</p>
                        <code class="block bg-gray-800 text-green-400 p-3 rounded text-xs overflow-x-auto">
DNI,Nombre,Apellido,Email<br>
12345678,Juan,Pérez,juan@email.com<br>
87654321,María,García,maria@email.com
                        </code>
                    </div>

                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <h4 class="font-bold text-blue-800">Paso 1: Preparar el Archivo</h4>
                        <ul class="text-xs text-gray-500 mt-2 space-y-1">
                            <li>• Abrí Excel o Google Sheets</li>
                            <li>• Columnas: DNI, Nombre, Apellido, Email</li>
                            <li>• Guardá como CSV (separado por comas)</li>
                            <li>• O copiá directamente las celdas</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-purple-500 pl-4 py-2">
                        <h4 class="font-bold text-purple-800">Paso 2: Importar</h4>
                        <ul class="text-xs text-gray-500 mt-2 space-y-1">
                            <li>• Andá a <strong>Personas</strong> → <strong>Estudiantes</strong></li>
                            <li>• Clic en <strong>"Importar"</strong></li>
                            <li>• Pegá el contenido del CSV</li>
                            <li>• O subí el archivo directamente</li>
                        </ul>
                    </div>

                    <div class="border-l-4 border-green-500 pl-4 py-2">
                        <h4 class="font-bold text-green-800">Paso 3: Verificar</h4>
                        <ul class="text-xs text-gray-500 mt-2 space-y-1">
                            <li>• El sistema muestra preview de datos</li>
                            <li>• Detecta duplicados por DNI</li>
                            <li>• Confirmar para importar</li>
                        </ul>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <h4 class="font-semibold text-amber-800 text-sm">⚠️ Tips Importantes</h4>
                        <ul class="text-xs text-amber-700 mt-1 space-y-1">
                            <li>• El DNI debe ser único (no se duplican)</li>
                            <li>• Si el DNI existe, se actualizan los datos</li>
                            <li>• El email es opcional pero recomendado</li>
                        </ul>
                    </div>
                </div>
            `
        },
        'guia-inscripciones': {
            titulo: '✍️ Formas de Inscribir Estudiantes',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="inscribir matricular formas metodos estudiantes curso">
                    <div class="bg-gradient-to-r from-amber-100 to-yellow-100 border border-amber-300 rounded-lg p-4">
                        <h3 class="font-bold text-amber-800 text-lg mb-1">Métodos de Inscripción</h3>
                        <p class="text-sm text-amber-700">Hay varias formas de inscribir estudiantes a cursos.</p>
                    </div>

                    <div class="border rounded-lg p-4 bg-blue-50">
                        <h4 class="font-bold text-blue-800 mb-2">🔹 Método 1: Desde Matrículas</h4>
                        <p class="text-xs text-gray-600 mb-2">El método más común:</p>
                        <ol class="text-xs text-gray-600 space-y-1 list-decimal ml-4">
                            <li>Ir a <strong>Matrículas</strong> → <strong>Inscripciones</strong></li>
                            <li>Clic en <strong>"Nueva Inscripción"</strong></li>
                            <li>Seleccionar estudiante y curso</li>
                            <li>Guardar</li>
                        </ol>
                    </div>

                    <div class="border rounded-lg p-4 bg-purple-50">
                        <h4 class="font-bold text-purple-800 mb-2">🔹 Método 2: Desde el Estudiante</h4>
                        <p class="text-xs text-gray-600 mb-2">Inscribir desde la ficha del estudiante:</p>
                        <ol class="text-xs text-gray-600 space-y-1 list-decimal ml-4">
                            <li>Ir a <strong>Personas</strong> → <strong>Estudiantes</strong></li>
                            <li>Buscar y abrir el estudiante</li>
                            <li>En acciones: <strong>"Inscribir a Curso"</strong></li>
                            <li>Seleccionar el curso</li>
                        </ol>
                    </div>

                    <div class="border rounded-lg p-4 bg-green-50">
                        <h4 class="font-bold text-green-800 mb-2">🔹 Método 3: Desde el Curso</h4>
                        <p class="text-xs text-gray-600 mb-2">Inscribir varios al mismo curso:</p>
                        <ol class="text-xs text-gray-600 space-y-1 list-decimal ml-4">
                            <li>Ir a <strong>Cursos</strong></li>
                            <li>Abrir el curso deseado</li>
                            <li>Pestaña <strong>"Inscriptos"</strong></li>
                            <li>Clic en <strong>"Agregar Estudiantes"</strong></li>
                            <li>Seleccionar múltiples estudiantes</li>
                        </ol>
                    </div>

                    <div class="border rounded-lg p-4 bg-teal-50">
                        <h4 class="font-bold text-teal-800 mb-2">🔹 Método 4: Importación Masiva</h4>
                        <p class="text-xs text-gray-600 mb-2">Para inscribir muchos de una vez:</p>
                        <ol class="text-xs text-gray-600 space-y-1 list-decimal ml-4">
                            <li>En <strong>Inscripciones</strong> → <strong>"Importar"</strong></li>
                            <li>Formato: DNI, Código_Curso</li>
                            <li>Pegar lista y confirmar</li>
                        </ol>
                    </div>

                    <div class="bg-gray-100 border rounded-lg p-3 mt-2">
                        <p class="text-xs text-gray-600"><strong>💡 Recomendación:</strong> Usá el Método 3 cuando tengas que inscribir varios estudiantes al mismo curso. Usá el Método 2 cuando quieras inscribir un estudiante a varios cursos.</p>
                    </div>
                </div>
            `
        },
        'guia-emails': {
            titulo: '📧 Enviar Certificados por Email',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="email enviar certificado notificar estudiante correo">
                    <div class="bg-gradient-to-r from-indigo-100 to-purple-100 border border-indigo-300 rounded-lg p-4">
                        <h3 class="font-bold text-indigo-800 text-lg mb-1">Envío de Certificados</h3>
                        <p class="text-sm text-indigo-700">Notificá a los estudiantes que su certificado está disponible.</p>
                    </div>

                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <h4 class="font-bold text-blue-800">Envío Individual</h4>
                        <ol class="text-xs text-gray-600 mt-2 space-y-1 list-decimal ml-4">
                            <li>Buscá la inscripción aprobada</li>
                            <li>Clic en el menú de acciones (⋮)</li>
                            <li>Seleccioná <strong>"Enviar por Email"</strong></li>
                            <li>El estudiante recibe email con enlace al certificado</li>
                        </ol>
                    </div>

                    <div class="border-l-4 border-purple-500 pl-4 py-2">
                        <h4 class="font-bold text-purple-800">Envío Masivo</h4>
                        <ol class="text-xs text-gray-600 mt-2 space-y-1 list-decimal ml-4">
                            <li>En <strong>Inscripciones</strong>, filtrá por estado "Aprobado"</li>
                            <li>Seleccioná las inscripciones (checkbox)</li>
                            <li>Clic en <strong>"Acciones"</strong> → <strong>"Enviar Notificaciones"</strong></li>
                            <li>Confirmá el envío masivo</li>
                        </ol>
                    </div>

                    <div class="border rounded-lg p-4 bg-green-50">
                        <h4 class="font-bold text-green-800 mb-2">📬 ¿Qué recibe el estudiante?</h4>
                        <ul class="text-xs text-gray-600 space-y-1">
                            <li>• Email personalizado con su nombre</li>
                            <li>• Enlace directo para ver el certificado online</li>
                            <li>• Botón para descargar PDF</li>
                            <li>• Código QR de validación</li>
                        </ul>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <h4 class="font-semibold text-amber-800 text-sm">⚠️ Requisitos</h4>
                        <ul class="text-xs text-amber-700 mt-1 space-y-1">
                            <li>• El estudiante debe tener email cargado</li>
                            <li>• La inscripción debe estar en estado "Aprobado"</li>
                            <li>• SendGrid debe estar configurado (ver módulo General)</li>
                        </ul>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <h4 class="font-semibold text-blue-800 text-sm">📊 Tracking de Emails</h4>
                        <p class="text-xs text-blue-700">Podés ver el estado de los emails enviados en el módulo <strong>Actividad</strong> → <strong>Email Stats</strong>: si fue entregado, abierto, o si hubo errores.</p>
                    </div>
                </div>
            `
        },
        'guia-docentes': {
            titulo: '🎓 Certificados para Docentes',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="docentes certificado participacion formador instructor tutor">
                    <div class="bg-gradient-to-r from-purple-100 to-pink-100 border border-purple-300 rounded-lg p-4">
                        <h3 class="font-bold text-purple-800 text-lg mb-1">Certificados de Participación Docente</h3>
                        <p class="text-sm text-purple-700">Los docentes también pueden recibir certificados por su participación en cursos.</p>
                    </div>

                    <div class="border-l-4 border-blue-500 pl-4 py-2">
                        <h4 class="font-bold text-blue-800">Paso 1: Crear el Docente</h4>
                        <ol class="text-xs text-gray-600 mt-2 space-y-1 list-decimal ml-4">
                            <li>Ir a <strong>Personas</strong> → <strong>Docentes</strong></li>
                            <li>Clic en <strong>"Nuevo Docente"</strong></li>
                            <li>Completar: DNI, Nombre, Email</li>
                            <li>Opcional: Especialidad, Título académico</li>
                        </ol>
                    </div>

                    <div class="border-l-4 border-purple-500 pl-4 py-2">
                        <h4 class="font-bold text-purple-800">Paso 2: Asignar a un Curso</h4>
                        <ol class="text-xs text-gray-600 mt-2 space-y-1 list-decimal ml-4">
                            <li>En la ficha del docente, clic en <strong>"Asignar a Curso"</strong></li>
                            <li>Seleccionar el curso</li>
                            <li>Elegir el <strong>Rol</strong>: Docente, Instructor, Tutor, Coordinador, etc.</li>
                            <li>Estado inicial: "Asignado"</li>
                        </ol>
                    </div>

                    <div class="border-l-4 border-green-500 pl-4 py-2">
                        <h4 class="font-bold text-green-800">Paso 3: Completar la Participación</h4>
                        <ol class="text-xs text-gray-600 mt-2 space-y-1 list-decimal ml-4">
                            <li>Ir a <strong>Matrículas</strong> → <strong>Asignaciones Docentes</strong></li>
                            <li>Buscar la asignación</li>
                            <li>Cambiar estado a <strong>"Completado"</strong></li>
                            <li>El certificado queda disponible</li>
                        </ol>
                    </div>

                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h4 class="font-bold text-gray-800 mb-2">📄 Tipos de Documentos según Estado</h4>
                        <table class="w-full text-xs">
                            <tr class="border-b">
                                <td class="py-1 font-medium">Asignado</td>
                                <td class="py-1">→ Constancia de Asignación</td>
                            </tr>
                            <tr class="border-b">
                                <td class="py-1 font-medium">En curso</td>
                                <td class="py-1">→ Constancia de Participación</td>
                            </tr>
                            <tr>
                                <td class="py-1 font-medium text-green-700">Completado</td>
                                <td class="py-1 text-green-700">→ <strong>Certificado de Participación</strong></td>
                            </tr>
                        </table>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <h4 class="font-semibold text-amber-800 text-sm">💡 Roles Disponibles</h4>
                        <p class="text-xs text-amber-700 mt-1">Docente, Instructor/a, Orador/a, Conferencista, Facilitador/a, Tutor/a, Coordinador/a. El rol aparece en el certificado.</p>
                    </div>
                </div>
            `
        },
        'dashboard': {
            titulo: 'Dashboard de Certificatum',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="dashboard resumen estadísticas métricas certificados">
                    <div class="bg-gradient-to-br from-emerald-50 to-blue-50 border border-emerald-200 rounded-lg p-4">
                        <h3 class="font-semibold text-emerald-800 flex items-center gap-2 mb-2">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Vista General
                        </h3>
                        <p class="text-sm text-gray-700">El dashboard muestra un resumen de estudiantes, docentes, cursos, inscripciones y certificados emitidos.</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="mouse-pointer-click" class="w-4 h-4"></i> Cards Interactivas
                        </h3>
                        <p class="text-sm text-blue-700">Hacé clic en cualquier card para ir directamente a esa sección y ver el detalle.</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                            <i data-lucide="lightbulb" class="w-4 h-4"></i> Tip
                        </h3>
                        <p class="text-sm text-amber-700">Los números se actualizan en tiempo real. Podés usar las cards como acceso rápido a cada sección.</p>
                    </div>
                    <button onclick="abrirTutorial('usar-dashboard')" class="mt-3 w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
            `
        },
        'personas': {
            titulo: 'Gestión de Personas',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="personas estudiantes docentes usuarios miembros">
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                            <i data-lucide="users" class="w-4 h-4"></i> ¿Qué incluye Personas?
                        </h3>
                        <p class="text-sm text-gray-700">Esta sección agrupa la gestión de <strong>Estudiantes</strong> y <strong>Docentes</strong> en sub-pestañas.</p>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                            <i data-lucide="user" class="w-4 h-4"></i> Estudiantes
                        </h3>
                        <p class="text-sm text-green-700">Personas que se inscriben a cursos y reciben certificados de aprobación.</p>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i> Docentes
                        </h3>
                        <p class="text-sm text-purple-700">Formadores que dictan cursos y reciben certificados de participación docente.</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i data-lucide="info" class="w-4 h-4"></i> Diferencia clave
                        </h3>
                        <p class="text-sm text-gray-700">Una persona puede ser estudiante en un curso y docente en otro. Se gestionan por separado.</p>
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button onclick="abrirTutorial('agregar-estudiante')" class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            Agregar estudiante
                        </button>
                        <button onclick="abrirTutorial('agregar-docente')" class="flex-1 px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            Agregar docente
                        </button>
                    </div>
                </div>
            `
        },
        'matriculas': {
            titulo: 'Gestión de Matrículas',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="matrículas inscripciones asignaciones vincular estudiantes docentes cursos">
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-lg p-4">
                        <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i> ¿Qué incluye Matrículas?
                        </h3>
                        <p class="text-sm text-gray-700">Esta sección agrupa <strong>Inscripciones</strong> (estudiantes) y <strong>Asignaciones</strong> (docentes) a cursos.</p>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <h3 class="font-semibold text-orange-800 flex items-center gap-2 mb-2">
                            <i data-lucide="user-check" class="w-4 h-4"></i> Inscripciones
                        </h3>
                        <p class="text-sm text-orange-700">Vinculan estudiantes con cursos. Incluyen estado, nota y asistencia.</p>
                    </div>
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <h3 class="font-semibold text-indigo-800 flex items-center gap-2 mb-2">
                            <i data-lucide="briefcase" class="w-4 h-4"></i> Asignaciones
                        </h3>
                        <p class="text-sm text-indigo-700">Vinculan docentes con cursos. Incluyen rol y estado de participación.</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i data-lucide="workflow" class="w-4 h-4"></i> Flujo típico
                        </h3>
                        <p class="text-sm text-gray-700">1. Crear curso → 2. Inscribir estudiantes → 3. Asignar docentes → 4. Marcar estados</p>
                    </div>
                    <div class="space-y-2 mt-3">
                        <button onclick="abrirTutorial('inscribir-estudiante')" class="w-full px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                            Paso a paso: Inscribir estudiante
                        </button>
                        <button onclick="abrirTutorial('asignar-docente')" class="w-full px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                            Paso a paso: Asignar docente
                        </button>
                    </div>
                </div>
            `
        },
        'faq-certificatum': {
            titulo: 'Preguntas Frecuentes - Certificatum',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="faq preguntas frecuentes certificados estudiantes cursos">
                    <div class="bg-gradient-to-r from-amber-100 to-yellow-100 border border-amber-300 rounded-lg p-4">
                        <h3 class="font-bold text-amber-800 text-lg mb-1">FAQ de Certificatum</h3>
                        <p class="text-sm text-amber-700">Respuestas a las consultas más comunes sobre certificados.</p>
                    </div>

                    <div class="divide-y border rounded-lg overflow-hidden">
                        <div class="p-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Por qué un estudiante no puede descargar su certificado?</p>
                            <p class="text-xs text-gray-600">El estudiante debe estar en estado <strong>"Aprobado"</strong> para generar el certificado de aprobación. Si está "Cursando" o "Inscrito", solo puede descargar constancias.</p>
                        </div>
                        <div class="p-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Cómo cambio la nota de un estudiante?</p>
                            <p class="text-xs text-gray-600">Andá a <strong>Matrículas → Inscripciones</strong>, buscá la inscripción, hacé clic en el ícono de editar y modificá la nota final.</p>
                        </div>
                        <div class="p-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Se puede eliminar un certificado ya emitido?</p>
                            <p class="text-xs text-gray-600">Los certificados son inmutables por seguridad. Si necesitás anular uno, cambiá el estado del estudiante a "Reprobado" o "Abandonado" - el certificado dejará de ser válido.</p>
                        </div>
                        <div class="p-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Qué diferencia hay entre Certificado y Constancia?</p>
                            <p class="text-xs text-gray-600"><strong>Certificado:</strong> Documento final que acredita aprobación. <strong>Constancia:</strong> Documento temporal que acredita inscripción o cursado.</p>
                        </div>
                        <div class="p-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Puedo crear varios cursos con el mismo nombre?</p>
                            <p class="text-xs text-gray-600">Sí, pero necesitan códigos diferentes. Usá cohortes: "DIPLO-2024-A" y "DIPLO-2024-B" para diferenciar ediciones.</p>
                        </div>
                        <div class="p-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Un docente puede estar en varios cursos?</p>
                            <p class="text-xs text-gray-600">Sí, podés asignar el mismo docente a múltiples cursos con diferentes roles (docente, tutor, coordinador, etc.).</p>
                        </div>
                        <div class="p-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Cómo cambio el firmante de los certificados?</p>
                            <p class="text-xs text-gray-600">Andá a <strong>General → Configuración → Firmantes</strong>. Los nuevos certificados usarán el firmante actualizado.</p>
                        </div>
                        <div class="p-3 hover:bg-gray-50">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Qué pasa si un estudiante aparece con género "Otro"?</p>
                            <p class="text-xs text-gray-600">El sistema usa lenguaje inclusivo. Los textos usarán formas neutras como "ha completado" en lugar de "ha completado/a".</p>
                        </div>
                    </div>
                </div>
            `
        },
        'glosario-certificatum': {
            titulo: 'Glosario - Certificatum',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="glosario términos certificatum definiciones">
                    <div class="bg-gradient-to-r from-blue-100 to-indigo-100 border border-blue-300 rounded-lg p-4">
                        <h3 class="font-bold text-blue-800 text-lg mb-1">Glosario de Certificatum</h3>
                        <p class="text-sm text-blue-700">Términos específicos del módulo de certificados.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="border rounded-lg p-3">
                            <p class="font-semibold text-green-700 text-sm">Estado de Inscripción</p>
                            <div class="text-xs text-gray-600 mt-1 space-y-1">
                                <p>• <strong>Inscrito:</strong> Estudiante registrado, curso no iniciado</p>
                                <p>• <strong>Cursando:</strong> Estudiante activo, curso en progreso</p>
                                <p>• <strong>Aprobado:</strong> Cumplió requisitos, puede obtener certificado</p>
                                <p>• <strong>Reprobado:</strong> No cumplió requisitos mínimos</p>
                                <p>• <strong>Abandonado:</strong> Dejó el curso antes de finalizar</p>
                            </div>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="font-semibold text-purple-700 text-sm">Tipos de Documento</p>
                            <div class="text-xs text-gray-600 mt-1 space-y-1">
                                <p>• <strong>Certificado de Aprobación:</strong> Documento final, requiere estado "Aprobado"</p>
                                <p>• <strong>Analítico Académico:</strong> Historial detallado con timeline y notas</p>
                                <p>• <strong>Constancia de Inscripción:</strong> Prueba de que se inscribió</p>
                                <p>• <strong>Constancia de Alumno Regular:</strong> Prueba de cursado activo</p>
                                <p>• <strong>Constancia de Finalización:</strong> Completó curso sin nota</p>
                            </div>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="font-semibold text-amber-700 text-sm">Roles de Docente</p>
                            <div class="text-xs text-gray-600 mt-1 space-y-1">
                                <p>• <strong>Docente:</strong> Responsable principal de dictar el curso</p>
                                <p>• <strong>Tutor:</strong> Acompaña y asesora a estudiantes</p>
                                <p>• <strong>Instructor:</strong> Dicta prácticas o talleres</p>
                                <p>• <strong>Orador:</strong> Participa como expositor invitado</p>
                                <p>• <strong>Coordinador:</strong> Organiza y supervisa el curso</p>
                                <p>• <strong>Facilitador:</strong> Modera actividades grupales</p>
                            </div>
                        </div>
                        <div class="border rounded-lg p-3">
                            <p class="font-semibold text-blue-700 text-sm">Tipo de Curso</p>
                            <div class="text-xs text-gray-600 mt-1 space-y-1">
                                <p>• <strong>Curso:</strong> Formación estándar con carga horaria definida</p>
                                <p>• <strong>Taller:</strong> Formación práctica intensiva</p>
                                <p>• <strong>Diplomatura:</strong> Programa extenso con múltiples módulos</p>
                                <p>• <strong>Seminario:</strong> Sesión especializada de corta duración</p>
                                <p>• <strong>Capacitación:</strong> Formación empresarial o institucional</p>
                                <p>• <strong>Conferencia:</strong> Evento de difusión con ponentes</p>
                            </div>
                        </div>
                    </div>
                </div>
            `
        },
        'errores-certificatum': {
            titulo: 'Errores Comunes - Certificatum',
            contenido: `
                <div class="space-y-4 ayuda-seccion" data-keywords="errores problemas soluciones certificatum">
                    <div class="bg-gradient-to-r from-red-100 to-orange-100 border border-red-300 rounded-lg p-4">
                        <h3 class="font-bold text-red-800 text-lg mb-1">Solución de Problemas</h3>
                        <p class="text-sm text-red-700">Errores comunes en Certificatum y cómo resolverlos.</p>
                    </div>

                    <div class="border border-red-200 rounded-lg overflow-hidden">
                        <div class="bg-red-50 p-3">
                            <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                "El estudiante ya está inscripto en este curso"
                            </p>
                        </div>
                        <div class="p-3">
                            <p class="text-xs text-gray-600 mb-2">No se puede crear una inscripción duplicada.</p>
                            <div class="bg-green-50 border border-green-200 rounded p-2">
                                <p class="text-xs text-green-700"><strong>Solución:</strong> Buscá la inscripción existente en Matrículas → Inscripciones y editala en lugar de crear una nueva.</p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-red-200 rounded-lg overflow-hidden">
                        <div class="bg-red-50 p-3">
                            <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                "El código de curso ya existe"
                            </p>
                        </div>
                        <div class="p-3">
                            <p class="text-xs text-gray-600 mb-2">Los códigos de curso deben ser únicos en tu institución.</p>
                            <div class="bg-green-50 border border-green-200 rounded p-2">
                                <p class="text-xs text-green-700"><strong>Solución:</strong> Usá un código diferente. Podés agregar año o número de cohorte: CURSO-2024 o CURSO-001-A.</p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-red-200 rounded-lg overflow-hidden">
                        <div class="bg-red-50 p-3">
                            <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                El certificado muestra datos incorrectos
                            </p>
                        </div>
                        <div class="p-3">
                            <p class="text-xs text-gray-600 mb-2">El certificado refleja los datos guardados al momento de generarlo.</p>
                            <div class="bg-green-50 border border-green-200 rounded p-2">
                                <p class="text-xs text-green-700"><strong>Solución:</strong> Corregí los datos del estudiante/curso y volvé a generar el certificado. Los PDFs se generan dinámicamente.</p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-red-200 rounded-lg overflow-hidden">
                        <div class="bg-red-50 p-3">
                            <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                "No se puede eliminar: tiene inscripciones asociadas"
                            </p>
                        </div>
                        <div class="p-3">
                            <p class="text-xs text-gray-600 mb-2">No se puede eliminar un estudiante o curso con datos vinculados.</p>
                            <div class="bg-green-50 border border-green-200 rounded p-2">
                                <p class="text-xs text-green-700"><strong>Solución:</strong> Primero eliminá o desvinculá todas las inscripciones asociadas. Luego podrás eliminar el registro.</p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-red-200 rounded-lg overflow-hidden">
                        <div class="bg-red-50 p-3">
                            <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                La nota no aparece en el certificado
                            </p>
                        </div>
                        <div class="p-3">
                            <p class="text-xs text-gray-600 mb-2">La nota final debe estar cargada en la inscripción.</p>
                            <div class="bg-green-50 border border-green-200 rounded p-2">
                                <p class="text-xs text-green-700"><strong>Solución:</strong> Editá la inscripción y completá el campo "Nota Final". También verificá que el curso tenga escala de notas configurada.</p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-red-200 rounded-lg overflow-hidden">
                        <div class="bg-red-50 p-3">
                            <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                El docente no puede descargar su certificado
                            </p>
                        </div>
                        <div class="p-3">
                            <p class="text-xs text-gray-600 mb-2">Los docentes solo pueden descargar certificado cuando su asignación está en estado "Completado".</p>
                            <div class="bg-green-50 border border-green-200 rounded p-2">
                                <p class="text-xs text-green-700"><strong>Solución:</strong> Andá a Matrículas → Asignaciones, buscá la asignación del docente y cambiá el estado a "Completado".</p>
                            </div>
                        </div>
                    </div>
                </div>
            `
        }
    };

    // ============================================================================
    // TUTORIALES PASO A PASO
    // ============================================================================
    const tutorialesCertificatum = {
        'usar-dashboard': {
            titulo: 'Cómo usar el Dashboard',
            pasos: [
                {
                    titulo: 'Vista general',
                    icono: 'layout-dashboard',
                    color: 'emerald',
                    contenido: `
                        <p class="mb-3">El <strong>Dashboard</strong> es la vista inicial de Certificatum. Muestra un resumen de toda la actividad:</p>
                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <div class="p-2 bg-green-50 rounded-lg text-center">
                                <i data-lucide="users" class="w-5 h-5 mx-auto text-green-600 mb-1"></i>
                                <p class="text-xs">Estudiantes</p>
                            </div>
                            <div class="p-2 bg-purple-50 rounded-lg text-center">
                                <i data-lucide="graduation-cap" class="w-5 h-5 mx-auto text-purple-600 mb-1"></i>
                                <p class="text-xs">Docentes</p>
                            </div>
                            <div class="p-2 bg-blue-50 rounded-lg text-center">
                                <i data-lucide="book-open" class="w-5 h-5 mx-auto text-blue-600 mb-1"></i>
                                <p class="text-xs">Cursos</p>
                            </div>
                        </div>
                    `
                },
                {
                    titulo: 'Cards interactivas',
                    icono: 'mouse-pointer-click',
                    color: 'blue',
                    contenido: `
                        <p class="mb-3">Cada <strong>card</strong> es clickeable:</p>
                        <div class="space-y-2 mb-3">
                            <div class="flex items-center gap-3 p-2 bg-blue-50 rounded-lg">
                                <i data-lucide="pointer" class="w-4 h-4 text-blue-600"></i>
                                <span class="text-sm">Hacé clic en una card para ir a esa sección</span>
                            </div>
                            <div class="flex items-center gap-3 p-2 bg-blue-50 rounded-lg">
                                <i data-lucide="hash" class="w-4 h-4 text-blue-600"></i>
                                <span class="text-sm">El número indica la cantidad de registros</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">Es la forma más rápida de navegar entre secciones.</p>
                    `
                },
                {
                    titulo: 'Métricas importantes',
                    icono: 'bar-chart-2',
                    color: 'amber',
                    contenido: `
                        <p class="mb-3">Las cards muestran métricas clave:</p>
                        <div class="space-y-2 mb-3">
                            <div class="p-2 bg-amber-50 rounded-lg">
                                <p class="font-medium text-amber-800 text-sm">Inscripciones</p>
                                <p class="text-xs text-gray-600">Total de estudiantes inscriptos a cursos</p>
                            </div>
                            <div class="p-2 bg-amber-50 rounded-lg">
                                <p class="font-medium text-amber-800 text-sm">Certificados</p>
                                <p class="text-xs text-gray-600">Documentos generados y descargados</p>
                            </div>
                        </div>
                    `
                },
                {
                    titulo: 'Navegación por tabs',
                    icono: 'layout',
                    color: 'teal',
                    contenido: `
                        <p class="mb-3">Usá las <strong>pestañas superiores</strong> para navegar:</p>
                        <div class="flex gap-2 mb-4 flex-wrap">
                            <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Configuración</span>
                            <span class="px-3 py-1.5 bg-teal-600 text-white rounded-lg text-sm font-medium">Personas</span>
                            <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Cursos</span>
                            <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Matrículas</span>
                        </div>
                        <p class="text-sm text-gray-500">Cada tab tiene sub-secciones para organizar mejor el contenido.</p>
                    `
                }
            ]
        },
        'agregar-estudiante': {
            titulo: 'Cómo agregar un estudiante',
            pasos: [
                {
                    titulo: 'Ir a la pestaña Personas',
                    icono: 'users',
                    color: 'blue',
                    contenido: 'Hacé clic en la pestaña <strong>"Personas"</strong> en la barra de navegación de Certificatum. Esta pestaña agrupa estudiantes y docentes.'
                },
                {
                    titulo: 'Seleccionar sub-pestaña Estudiantes',
                    icono: 'user',
                    color: 'green',
                    contenido: 'Dentro de Personas, asegurate de estar en la sub-pestaña <strong>"Estudiantes"</strong> (es la primera opción).'
                },
                {
                    titulo: 'Clic en "Agregar"',
                    icono: 'plus-circle',
                    color: 'emerald',
                    contenido: 'Hacé clic en el botón verde <strong>"Agregar"</strong> en la esquina superior derecha de la tabla.'
                },
                {
                    titulo: 'Completar el formulario',
                    icono: 'edit',
                    color: 'purple',
                    contenido: 'Completá los datos: <strong>DNI</strong> (obligatorio), <strong>Nombre</strong>, <strong>Apellido</strong>, <strong>Email</strong> (opcional pero recomendado para notificaciones).'
                },
                {
                    titulo: 'Guardar',
                    icono: 'save',
                    color: 'teal',
                    contenido: 'Hacé clic en <strong>"Guardar"</strong>. El estudiante aparecerá en la lista y podrás inscribirlo en cursos.'
                }
            ]
        },
        'agregar-docente': {
            titulo: 'Cómo agregar un docente',
            pasos: [
                {
                    titulo: 'Ir a Personas > Docentes',
                    icono: 'graduation-cap',
                    color: 'purple',
                    contenido: 'Hacé clic en <strong>"Personas"</strong> y luego en la sub-pestaña <strong>"Docentes"</strong>.'
                },
                {
                    titulo: 'Clic en "Agregar"',
                    icono: 'plus-circle',
                    color: 'emerald',
                    contenido: 'Hacé clic en el botón <strong>"Agregar"</strong> para abrir el formulario de nuevo docente.'
                },
                {
                    titulo: 'Completar datos del docente',
                    icono: 'edit',
                    color: 'blue',
                    contenido: 'Ingresá: <strong>DNI</strong>, <strong>Nombre</strong>, <strong>Apellido</strong>, <strong>Email</strong>, y opcionalmente <strong>Especialidad</strong>.'
                },
                {
                    titulo: 'Guardar docente',
                    icono: 'save',
                    color: 'teal',
                    contenido: 'Hacé clic en <strong>"Guardar"</strong>. Luego podrás asignarlo a cursos desde la pestaña Matrículas > Asignaciones.'
                }
            ]
        },
        'crear-curso': {
            titulo: 'Cómo crear un curso',
            pasos: [
                {
                    titulo: 'Ir a la pestaña Cursos',
                    icono: 'book-open',
                    color: 'green',
                    contenido: 'Hacé clic en la pestaña <strong>"Cursos"</strong> en la navegación principal de Certificatum.'
                },
                {
                    titulo: 'Clic en "Agregar"',
                    icono: 'plus-circle',
                    color: 'emerald',
                    contenido: 'Hacé clic en el botón verde <strong>"Agregar"</strong> en la esquina superior derecha para abrir el formulario de nuevo curso.'
                },
                {
                    titulo: 'Código único del curso',
                    icono: 'hash',
                    color: 'blue',
                    contenido: `
                        <p class="mb-3">El <strong>código</strong> es un identificador único para el curso. Ejemplos:</p>
                        <div class="space-y-2 mb-3">
                            <div class="flex items-center gap-2 p-2 bg-blue-50 rounded-lg text-sm">
                                <code class="bg-blue-100 px-2 py-0.5 rounded">SA-CUR-2026-001</code>
                                <span class="text-gray-500">→ SAJuR Curso 2026 #1</span>
                            </div>
                            <div class="flex items-center gap-2 p-2 bg-blue-50 rounded-lg text-sm">
                                <code class="bg-blue-100 px-2 py-0.5 rounded">DIPLO-JR-01</code>
                                <span class="text-gray-500">→ Diplomatura JR #1</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">El código aparece en los certificados y sirve para identificar el curso.</p>
                    `
                },
                {
                    titulo: 'Nombre y descripción',
                    icono: 'file-text',
                    color: 'purple',
                    contenido: `
                        <p class="mb-3">Completá la información del curso:</p>
                        <div class="space-y-2 mb-3">
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <p class="font-medium text-purple-800 text-sm">Nombre del curso</p>
                                <p class="text-xs text-gray-600">Ej: "Diplomatura en Justicia Restaurativa"</p>
                            </div>
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <p class="font-medium text-purple-800 text-sm">Descripción</p>
                                <p class="text-xs text-gray-600">Breve resumen del contenido (aparece en el analítico)</p>
                            </div>
                        </div>
                    `
                },
                {
                    titulo: 'Tipo de curso',
                    icono: 'tag',
                    color: 'amber',
                    contenido: `
                        <p class="mb-3">Seleccioná el <strong>tipo de capacitación</strong>:</p>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="p-2 bg-amber-50 rounded-lg text-center">
                                <i data-lucide="book" class="w-5 h-5 mx-auto text-amber-600 mb-1"></i>
                                <p class="text-xs font-medium">Curso</p>
                            </div>
                            <div class="p-2 bg-amber-50 rounded-lg text-center">
                                <i data-lucide="hammer" class="w-5 h-5 mx-auto text-amber-600 mb-1"></i>
                                <p class="text-xs font-medium">Taller</p>
                            </div>
                            <div class="p-2 bg-amber-50 rounded-lg text-center">
                                <i data-lucide="graduation-cap" class="w-5 h-5 mx-auto text-amber-600 mb-1"></i>
                                <p class="text-xs font-medium">Diplomatura</p>
                            </div>
                            <div class="p-2 bg-amber-50 rounded-lg text-center">
                                <i data-lucide="mic" class="w-5 h-5 mx-auto text-amber-600 mb-1"></i>
                                <p class="text-xs font-medium">Seminario</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">El tipo aparece en el certificado ("Certificado del Curso...", "Certificado del Taller...").</p>
                    `
                },
                {
                    titulo: 'Modalidad y carga horaria',
                    icono: 'clock',
                    color: 'teal',
                    contenido: `
                        <p class="mb-3">Configurá la <strong>modalidad</strong> y <strong>duración</strong>:</p>
                        <div class="space-y-2 mb-3">
                            <div class="p-2 bg-teal-50 rounded-lg">
                                <p class="font-medium text-teal-800 text-sm">Modalidad</p>
                                <div class="flex gap-2 mt-1">
                                    <span class="px-2 py-0.5 bg-teal-100 text-teal-700 rounded text-xs">Presencial</span>
                                    <span class="px-2 py-0.5 bg-teal-100 text-teal-700 rounded text-xs">Virtual</span>
                                    <span class="px-2 py-0.5 bg-teal-100 text-teal-700 rounded text-xs">Híbrido</span>
                                </div>
                            </div>
                            <div class="p-2 bg-teal-50 rounded-lg">
                                <p class="font-medium text-teal-800 text-sm">Carga horaria</p>
                                <p class="text-xs text-gray-600">Duración total en horas (ej: 40 hs, 120 hs)</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">Ambos datos aparecen en los certificados y analíticos.</p>
                    `
                },
                {
                    titulo: 'Fechas del curso',
                    icono: 'calendar',
                    color: 'blue',
                    contenido: `
                        <p class="mb-3">Definí el <strong>período</strong> del curso:</p>
                        <div class="flex gap-3 mb-3">
                            <div class="flex-1 p-2 bg-blue-50 rounded-lg text-center">
                                <i data-lucide="calendar-plus" class="w-5 h-5 mx-auto text-blue-600 mb-1"></i>
                                <p class="text-xs font-medium">Fecha inicio</p>
                            </div>
                            <div class="flex-1 p-2 bg-blue-50 rounded-lg text-center">
                                <i data-lucide="calendar-check" class="w-5 h-5 mx-auto text-blue-600 mb-1"></i>
                                <p class="text-xs font-medium">Fecha fin</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">Las fechas determinan automáticamente el estado: Próximo, En curso, o Finalizado.</p>
                    `
                },
                {
                    titulo: 'Guardar curso',
                    icono: 'save',
                    color: 'green',
                    contenido: `
                        <p class="mb-3">Hacé clic en <strong>"Guardar"</strong> para crear el curso.</p>
                        <div class="p-3 bg-green-50 border border-green-200 rounded-lg mb-3">
                            <p class="text-sm text-green-700"><i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i> El curso aparecerá en la lista y estará listo para:</p>
                            <ul class="text-xs text-green-600 mt-2 ml-5 list-disc">
                                <li>Inscribir estudiantes</li>
                                <li>Asignar docentes</li>
                                <li>Crear evaluaciones</li>
                            </ul>
                        </div>
                    `
                }
            ]
        },
        'inscribir-estudiante': {
            titulo: 'Cómo inscribir un estudiante',
            pasos: [
                {
                    titulo: 'Ir a Matrículas > Inscripciones',
                    icono: 'clipboard-list',
                    color: 'amber',
                    contenido: 'Hacé clic en <strong>"Matrículas"</strong> y seleccioná la sub-pestaña <strong>"Inscripciones"</strong>.'
                },
                {
                    titulo: 'Clic en "Agregar"',
                    icono: 'plus-circle',
                    color: 'emerald',
                    contenido: 'Hacé clic en <strong>"Agregar"</strong> para crear una nueva inscripción.'
                },
                {
                    titulo: 'Seleccionar estudiante',
                    icono: 'user',
                    color: 'blue',
                    contenido: 'Buscá y seleccioná al <strong>estudiante</strong> por nombre o DNI en el campo de búsqueda.'
                },
                {
                    titulo: 'Seleccionar curso',
                    icono: 'book-open',
                    color: 'green',
                    contenido: 'Elegí el <strong>curso</strong> en el que querés inscribir al estudiante.'
                },
                {
                    titulo: 'Confirmar inscripción',
                    icono: 'check-circle',
                    color: 'teal',
                    contenido: 'Hacé clic en <strong>"Guardar"</strong>. El estudiante quedará inscripto y podrá acceder a sus certificados cuando complete el curso.'
                }
            ]
        },
        'asignar-docente': {
            titulo: 'Cómo asignar un docente a un curso',
            pasos: [
                {
                    titulo: 'Ir a Matrículas > Asignaciones',
                    icono: 'user-check',
                    color: 'purple',
                    contenido: 'Hacé clic en <strong>"Matrículas"</strong> y seleccioná la sub-pestaña <strong>"Asignaciones"</strong>.'
                },
                {
                    titulo: 'Clic en "Agregar"',
                    icono: 'plus-circle',
                    color: 'emerald',
                    contenido: 'Hacé clic en <strong>"Agregar"</strong> para crear una nueva asignación docente.'
                },
                {
                    titulo: 'Seleccionar docente',
                    icono: 'graduation-cap',
                    color: 'blue',
                    contenido: 'Buscá y seleccioná al <strong>docente</strong> que vas a asignar.'
                },
                {
                    titulo: 'Seleccionar curso y rol',
                    icono: 'book-open',
                    color: 'green',
                    contenido: 'Elegí el <strong>curso</strong> y el <strong>rol</strong> del docente (Docente, Instructor, Coordinador, etc.).'
                },
                {
                    titulo: 'Guardar asignación',
                    icono: 'save',
                    color: 'teal',
                    contenido: 'Hacé clic en <strong>"Guardar"</strong>. El docente podrá recibir su certificado de participación cuando el curso finalice.'
                }
            ]
        },
        'crear-evaluacion': {
            titulo: 'Cómo crear una evaluación',
            pasos: [
                {
                    titulo: 'Ir a la pestaña Evaluaciones',
                    icono: 'file-check',
                    color: 'teal',
                    contenido: `
                        <p class="mb-3">Hacé clic en la pestaña <strong>"Evaluaciones"</strong> en la navegación de Certificatum.</p>
                        <div class="flex gap-2 mb-3 flex-wrap">
                            <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Configuración</span>
                            <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Personas</span>
                            <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Cursos</span>
                            <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Matrículas</span>
                            <span class="px-3 py-1.5 bg-teal-600 text-white rounded-lg text-sm font-medium">Evaluaciones</span>
                        </div>
                    `
                },
                {
                    titulo: 'Crear nueva evaluación',
                    icono: 'plus-circle',
                    color: 'emerald',
                    contenido: `
                        <p class="mb-3">Hacé clic en el botón <strong>"Nueva Evaluación"</strong>.</p>
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg mb-3 text-center">
                            <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium">
                                <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i>
                                Nueva Evaluación
                            </button>
                        </div>
                    `
                },
                {
                    titulo: 'Datos básicos de la evaluación',
                    icono: 'file-text',
                    color: 'blue',
                    contenido: `
                        <p class="mb-3">Completá la información principal:</p>
                        <div class="space-y-2 mb-3">
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <p class="font-medium text-blue-800 text-sm">Nombre de la evaluación</p>
                                <p class="text-xs text-gray-600">Ej: "Examen Final - Módulo 1"</p>
                            </div>
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <p class="font-medium text-blue-800 text-sm">Curso asociado</p>
                                <p class="text-xs text-gray-600">Seleccioná el curso al que pertenece</p>
                            </div>
                        </div>
                    `
                },
                {
                    titulo: 'Configurar parámetros',
                    icono: 'settings',
                    color: 'amber',
                    contenido: `
                        <p class="mb-3">Definí las reglas de la evaluación:</p>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="p-2 bg-amber-50 rounded-lg text-center">
                                <i data-lucide="clock" class="w-5 h-5 mx-auto text-amber-600 mb-1"></i>
                                <p class="text-xs font-medium">Duración</p>
                                <p class="text-xs text-gray-500">Ej: 60 minutos</p>
                            </div>
                            <div class="p-2 bg-amber-50 rounded-lg text-center">
                                <i data-lucide="target" class="w-5 h-5 mx-auto text-amber-600 mb-1"></i>
                                <p class="text-xs font-medium">Nota mínima</p>
                                <p class="text-xs text-gray-500">Ej: 6 (de 10)</p>
                            </div>
                            <div class="p-2 bg-amber-50 rounded-lg text-center">
                                <i data-lucide="refresh-cw" class="w-5 h-5 mx-auto text-amber-600 mb-1"></i>
                                <p class="text-xs font-medium">Intentos</p>
                                <p class="text-xs text-gray-500">Ej: 2 intentos</p>
                            </div>
                            <div class="p-2 bg-amber-50 rounded-lg text-center">
                                <i data-lucide="shuffle" class="w-5 h-5 mx-auto text-amber-600 mb-1"></i>
                                <p class="text-xs font-medium">Aleatorizar</p>
                                <p class="text-xs text-gray-500">Mezclar preguntas</p>
                            </div>
                        </div>
                    `
                },
                {
                    titulo: 'Agregar preguntas',
                    icono: 'list',
                    color: 'purple',
                    contenido: `
                        <p class="mb-3">Creá las preguntas de la evaluación:</p>
                        <div class="space-y-2 mb-3">
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <p class="font-medium text-purple-800 text-sm flex items-center gap-2">
                                    <i data-lucide="check-square" class="w-4 h-4"></i> Opción múltiple
                                </p>
                                <p class="text-xs text-gray-600">Varias opciones, una correcta</p>
                            </div>
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <p class="font-medium text-purple-800 text-sm flex items-center gap-2">
                                    <i data-lucide="toggle-left" class="w-4 h-4"></i> Verdadero/Falso
                                </p>
                                <p class="text-xs text-gray-600">Afirmaciones a validar</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">Marcá la respuesta correcta para cada pregunta.</p>
                    `
                },
                {
                    titulo: 'Guardar y activar',
                    icono: 'power',
                    color: 'green',
                    contenido: `
                        <p class="mb-3">Guardá la evaluación y activala:</p>
                        <div class="space-y-2 mb-3">
                            <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                    <i data-lucide="save" class="w-4 h-4 text-gray-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Guardar borrador</p>
                                    <p class="text-xs text-gray-500">Guardá sin activar todavía</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-2 bg-green-50 rounded-lg">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <i data-lucide="power" class="w-4 h-4 text-white"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-green-800">Activar evaluación</p>
                                    <p class="text-xs text-green-600">Lista para que los estudiantes rindan</p>
                                </div>
                            </div>
                        </div>
                    `
                },
                {
                    titulo: 'Compartir con estudiantes',
                    icono: 'share-2',
                    color: 'blue',
                    contenido: `
                        <p class="mb-3">Una vez activa, compartí la evaluación:</p>
                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg mb-3">
                            <p class="text-sm text-blue-800 mb-2"><strong>Código de evaluación:</strong></p>
                            <div class="flex items-center gap-2">
                                <code class="flex-1 px-3 py-2 bg-white rounded border text-sm">EVAL-ABC123</code>
                                <button class="px-3 py-2 bg-blue-600 text-white rounded text-sm">
                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">Los estudiantes usan este código para acceder a la evaluación.</p>
                    `
                }
            ]
        },
        'notificar-estudiantes': {
            titulo: 'Cómo notificar a estudiantes',
            pasos: [
                {
                    titulo: 'Ir a Matrículas > Inscripciones',
                    icono: 'clipboard-list',
                    color: 'amber',
                    contenido: `
                        <p class="mb-3">Las notificaciones se envían desde la sección de <strong>Inscripciones</strong>.</p>
                        <div class="flex gap-2 mb-3 flex-wrap">
                            <span class="px-3 py-1.5 bg-amber-600 text-white rounded-lg text-sm font-medium">Matrículas</span>
                            <span class="px-2 py-1 text-gray-400">→</span>
                            <span class="px-3 py-1.5 bg-orange-100 text-orange-700 rounded-lg text-sm">Inscripciones</span>
                        </div>
                    `
                },
                {
                    titulo: 'Seleccionar estudiantes',
                    icono: 'check-square',
                    color: 'blue',
                    contenido: `
                        <p class="mb-3">Usá los <strong>checkboxes</strong> para seleccionar a quién notificar:</p>
                        <div class="space-y-2 mb-3">
                            <div class="flex items-center gap-3 p-2 bg-blue-50 rounded-lg">
                                <input type="checkbox" checked class="w-4 h-4">
                                <span class="text-sm">Juan Pérez - Curso ABC</span>
                            </div>
                            <div class="flex items-center gap-3 p-2 bg-blue-50 rounded-lg">
                                <input type="checkbox" checked class="w-4 h-4">
                                <span class="text-sm">María García - Curso ABC</span>
                            </div>
                            <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                                <input type="checkbox" class="w-4 h-4">
                                <span class="text-sm text-gray-500">Pedro López - Curso XYZ</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">Podés usar "Seleccionar todos" para marcar toda la página.</p>
                    `
                },
                {
                    titulo: 'Abrir acciones masivas',
                    icono: 'menu',
                    color: 'purple',
                    contenido: `
                        <p class="mb-3">Al seleccionar estudiantes, aparece la <strong>barra de acciones</strong>:</p>
                        <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg mb-3">
                            <p class="text-sm text-purple-800 mb-2">3 seleccionados</p>
                            <div class="flex gap-2 flex-wrap">
                                <span class="px-2 py-1 bg-white rounded text-xs">Cambiar estado</span>
                                <span class="px-2 py-1 bg-blue-600 text-white rounded text-xs font-medium">Enviar email</span>
                                <span class="px-2 py-1 bg-white rounded text-xs">Exportar</span>
                            </div>
                        </div>
                    `
                },
                {
                    titulo: 'Elegir tipo de notificación',
                    icono: 'mail',
                    color: 'green',
                    contenido: `
                        <p class="mb-3">Hacé clic en <strong>"Enviar email"</strong> y elegí el tipo:</p>
                        <div class="space-y-2 mb-3">
                            <div class="p-2 bg-green-50 rounded-lg">
                                <p class="font-medium text-green-800 text-sm">📋 Notificación de evaluación</p>
                                <p class="text-xs text-gray-600">Avisa que hay una evaluación disponible</p>
                            </div>
                            <div class="p-2 bg-green-50 rounded-lg">
                                <p class="font-medium text-green-800 text-sm">🎓 Certificado disponible</p>
                                <p class="text-xs text-gray-600">Avisa que pueden descargar su certificado</p>
                            </div>
                            <div class="p-2 bg-green-50 rounded-lg">
                                <p class="font-medium text-green-800 text-sm">📝 Recordatorio general</p>
                                <p class="text-xs text-gray-600">Mensaje personalizado</p>
                            </div>
                        </div>
                    `
                },
                {
                    titulo: 'Confirmar envío',
                    icono: 'send',
                    color: 'teal',
                    contenido: `
                        <p class="mb-3">Revisá y confirmá el envío:</p>
                        <div class="p-3 bg-teal-50 border border-teal-200 rounded-lg mb-3">
                            <p class="text-sm text-teal-800 mb-2"><strong>Resumen:</strong></p>
                            <ul class="text-xs text-teal-700 space-y-1 ml-4 list-disc">
                                <li>Destinatarios: 3 estudiantes</li>
                                <li>Tipo: Notificación de evaluación</li>
                                <li>Curso: Diplomatura en JR</li>
                            </ul>
                        </div>
                        <div class="p-2 bg-green-100 rounded-lg text-center">
                            <button class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-medium">
                                <i data-lucide="send" class="w-4 h-4 inline mr-1"></i>
                                Enviar notificaciones
                            </button>
                        </div>
                    `
                },
                {
                    titulo: 'Verificar en Actividad',
                    icono: 'check-circle',
                    color: 'emerald',
                    contenido: `
                        <p class="mb-3">Los emails enviados aparecen en el módulo <strong>Actividad</strong>:</p>
                        <div class="space-y-2 mb-3">
                            <div class="flex items-center gap-3 p-2 bg-emerald-50 rounded-lg">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                                <span class="text-sm">Email enviado a juan@email.com</span>
                            </div>
                            <div class="flex items-center gap-3 p-2 bg-emerald-50 rounded-lg">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                                <span class="text-sm">Email enviado a maria@email.com</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500">Podés ver el estado de entrega en Actividad > Comunicaciones.</p>
                    `
                }
            ]
        },
        'configurar-certificados': {
            titulo: 'Cómo configurar los certificados',
            pasos: [
                {
                    titulo: 'Ir a Configuración',
                    icono: 'settings',
                    color: 'gray',
                    contenido: 'Hacé clic en la pestaña <strong>"Configuración"</strong> (primera pestaña de Certificatum).'
                },
                {
                    titulo: 'Paleta de colores',
                    icono: 'palette',
                    color: 'blue',
                    contenido: 'Podés usar la <strong>paleta heredada</strong> de General o definir colores <strong>específicos para certificados</strong>.'
                },
                {
                    titulo: 'Configurar firmas',
                    icono: 'pen-tool',
                    color: 'purple',
                    contenido: 'Subí las <strong>firmas digitales</strong> que aparecerán en los certificados. Podés tener hasta 2 firmantes.'
                },
                {
                    titulo: 'Guardar cambios',
                    icono: 'save',
                    color: 'emerald',
                    contenido: 'Hacé clic en <strong>"Guardar"</strong>. Los cambios se aplicarán a todos los certificados generados.'
                }
            ]
        },
        'importar-datos': {
            titulo: 'Cómo importar datos masivamente',
            pasos: [
                {
                    titulo: 'Ubicar botón Importar',
                    icono: 'upload',
                    color: 'blue',
                    contenido: 'En las pestañas de <strong>Estudiantes</strong>, <strong>Docentes</strong> o <strong>Inscripciones</strong>, buscá el botón <strong>"Importar"</strong>.'
                },
                {
                    titulo: 'Seleccionar archivo o pegar datos',
                    icono: 'file',
                    color: 'green',
                    contenido: 'Podés <strong>subir un archivo CSV/Excel</strong> o <strong>pegar datos</strong> directamente desde una planilla.'
                },
                {
                    titulo: 'Mapear columnas',
                    icono: 'columns',
                    color: 'purple',
                    contenido: 'El sistema detecta las columnas automáticamente. Verificá que cada columna esté <strong>mapeada correctamente</strong>.'
                },
                {
                    titulo: 'Validar datos',
                    icono: 'check-circle',
                    color: 'amber',
                    contenido: 'Revisá los <strong>errores y advertencias</strong>. Corregí los datos problemáticos antes de importar.'
                },
                {
                    titulo: 'Ejecutar importación',
                    icono: 'play',
                    color: 'emerald',
                    contenido: 'Hacé clic en <strong>"Importar"</strong>. Los registros se crearán y verás un resumen del resultado.'
                }
            ]
        },
        'exportar-datos': {
            titulo: 'Cómo exportar datos',
            pasos: [
                {
                    titulo: 'Aplicar filtros (opcional)',
                    icono: 'filter',
                    color: 'blue',
                    contenido: 'Si querés exportar solo algunos registros, primero <strong>aplicá filtros</strong> (por estado, fecha, etc.).'
                },
                {
                    titulo: 'Clic en "Exportar"',
                    icono: 'download',
                    color: 'green',
                    contenido: 'Hacé clic en el botón <strong>"Exportar"</strong> ubicado junto al botón Importar.'
                },
                {
                    titulo: 'Descargar archivo',
                    icono: 'file-spreadsheet',
                    color: 'emerald',
                    contenido: 'Se descargará un archivo <strong>CSV</strong> con los datos visibles. Podés abrirlo en Excel o Google Sheets.'
                }
            ]
        }
    };

    let tutorialActual = null;
    let pasoActual = 0;

    function abrirTutorial(tutorialId) {
        const tutorial = tutorialesCertificatum[tutorialId];
        if (!tutorial) return;

        tutorialActual = tutorial;
        pasoActual = 0;

        document.getElementById('tutorial-titulo').textContent = tutorial.titulo;
        renderizarDots();
        mostrarPasoTutorial(0);
        document.getElementById('modal-tutorial').classList.remove('hidden');
        lucide.createIcons();
    }

    function cerrarTutorial() {
        document.getElementById('modal-tutorial').classList.add('hidden');
        tutorialActual = null;
        pasoActual = 0;
    }

    function renderizarDots() {
        const dotsContainer = document.getElementById('tutorial-dots');
        dotsContainer.innerHTML = tutorialActual.pasos.map((_, i) => `
            <button onclick="irAPasoTutorial(${i})"
                    class="w-2.5 h-2.5 rounded-full transition-all ${i === pasoActual ? 'bg-emerald-600 w-6' : 'bg-gray-300 hover:bg-gray-400'}">
            </button>
        `).join('');
    }

    function mostrarPasoTutorial(index) {
        if (!tutorialActual) return;

        pasoActual = index;
        const paso = tutorialActual.pasos[index];
        const total = tutorialActual.pasos.length;
        const progreso = ((index + 1) / total) * 100;

        document.getElementById('tutorial-progreso').style.width = progreso + '%';
        document.getElementById('tutorial-progreso-texto').textContent = `Paso ${index + 1} de ${total}`;

        const colores = {
            blue: 'bg-blue-100 text-blue-600',
            green: 'bg-green-100 text-green-600',
            purple: 'bg-purple-100 text-purple-600',
            amber: 'bg-amber-100 text-amber-600',
            teal: 'bg-teal-100 text-teal-600',
            emerald: 'bg-emerald-100 text-emerald-600',
            gray: 'bg-gray-100 text-gray-600'
        };

        const colorClasses = colores[paso.color] || colores.blue;

        document.getElementById('tutorial-contenido').innerHTML = `
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 ${colorClasses} rounded-full mb-4">
                    <i data-lucide="${paso.icono}" class="w-8 h-8"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-800">${paso.titulo}</h4>
            </div>
            <div class="bg-gray-50 rounded-xl p-5 text-gray-700 leading-relaxed">
                ${paso.contenido}
            </div>
            <div class="mt-4 flex justify-center gap-1">
                ${tutorialActual.pasos.map((p, i) => `
                    <div class="w-2 h-2 rounded-full ${i === index ? 'bg-emerald-500' : i < index ? 'bg-emerald-300' : 'bg-gray-200'}"></div>
                `).join('')}
            </div>
        `;

        // Actualizar botones
        document.getElementById('tutorial-btn-anterior').style.visibility = index === 0 ? 'hidden' : 'visible';

        const btnSiguiente = document.getElementById('tutorial-btn-siguiente');
        if (index === total - 1) {
            btnSiguiente.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Finalizar';
            btnSiguiente.onclick = cerrarTutorial;
        } else {
            btnSiguiente.innerHTML = 'Siguiente <i data-lucide="chevron-right" class="w-4 h-4"></i>';
            btnSiguiente.onclick = tutorialSiguiente;
        }

        renderizarDots();
        lucide.createIcons();
    }

    function tutorialAnterior() {
        if (pasoActual > 0) {
            mostrarPasoTutorial(pasoActual - 1);
        }
    }

    function tutorialSiguiente() {
        if (tutorialActual && pasoActual < tutorialActual.pasos.length - 1) {
            mostrarPasoTutorial(pasoActual + 1);
        }
    }

    function irAPasoTutorial(index) {
        mostrarPasoTutorial(index);
    }

    let panelAyudaAbierto = false;

    function togglePanelAyuda() {
        const panel = document.getElementById('panel-ayuda');
        const overlay = document.getElementById('overlay-ayuda');
        const btnFlotante = document.getElementById('btn-ayuda-flotante');

        panelAyudaAbierto = !panelAyudaAbierto;

        if (panelAyudaAbierto) {
            panel.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
            btnFlotante.classList.add('hidden');
            actualizarAyudaContextual();
            lucide.createIcons();
        } else {
            panel.classList.add('translate-x-full');
            overlay.classList.add('hidden');
            btnFlotante.classList.remove('hidden');
        }
    }

    // Atajo de teclado F1 para abrir ayuda
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F1') {
            e.preventDefault(); // Evitar ayuda del navegador
            togglePanelAyuda();
        }
        // Escape para cerrar
        if (e.key === 'Escape' && panelAyudaAbierto) {
            togglePanelAyuda();
        }
    });

    function actualizarAyudaContextual() {
        // Detectar tab activo buscando el botón con clase active
        const tabActivo = document.querySelector('.tab-button-cert.active');
        let contexto = 'general';

        if (tabActivo) {
            const onclickAttr = tabActivo.getAttribute('onclick') || '';
            const match = onclickAttr.match(/cambiarTabCert\('(\w+)'/);
            if (match) {
                let tabId = match[1];
                // Mapear nombres de tab a secciones de ayuda
                const mapeoTabs = {
                    'diseno': 'configuracion',
                    'logs': 'actividad',
                    'ayuda': 'general',
                    'configuracion': 'configuracion',
                    'personas': 'personas',
                    'matriculas': 'matriculas',
                    'cursos': 'cursos',
                    'evaluaciones': 'evaluaciones'
                };
                tabId = mapeoTabs[tabId] || tabId;
                if (contenidoAyuda[tabId]) {
                    contexto = tabId;
                }
            }
        }

        // Actualizar indicador de contexto
        const contextoTexto = document.getElementById('ayuda-contexto-texto');
        contextoTexto.textContent = contenidoAyuda[contexto].titulo;

        // Actualizar contenido
        const contenedor = document.getElementById('ayuda-contenido');
        contenedor.innerHTML = contenidoAyuda[contexto].contenido;

        // Recrear iconos
        lucide.createIcons();
    }

    function mostrarSeccionAyuda(seccion) {
        // Mostrar una sección específica de ayuda
        if (!contenidoAyuda[seccion]) return;

        const contextoTexto = document.getElementById('ayuda-contexto-texto');
        contextoTexto.textContent = contenidoAyuda[seccion].titulo;

        const contenedor = document.getElementById('ayuda-contenido');
        contenedor.innerHTML = contenidoAyuda[seccion].contenido;

        lucide.createIcons();
    }

    function mostrarAyudaContextual() {
        // Volver a la ayuda contextual según el tab activo
        actualizarAyudaContextual();
    }

    function filtrarAyuda(termino) {
        termino = termino.toLowerCase().trim();
        const contenedor = document.getElementById('ayuda-contenido');

        if (!termino) {
            actualizarAyudaContextual();
            return;
        }

        // Buscar en todos los contenidos
        let resultados = '';
        let encontrados = 0;

        for (const [key, data] of Object.entries(contenidoAyuda)) {
            const keywords = data.contenido.toLowerCase();
            if (keywords.includes(termino) || data.titulo.toLowerCase().includes(termino)) {
                encontrados++;
                resultados += `
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100 transition"
                         onclick="mostrarAyudaSeccion('${key}')">
                        <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-blue-500"></i>
                            ${data.titulo}
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">Click para ver</p>
                    </div>
                `;
            }
        }

        if (encontrados === 0) {
            resultados = `
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                    <p>No se encontraron resultados para "<strong>${termino}</strong>"</p>
                </div>
            `;
        } else {
            resultados = `<p class="text-sm text-gray-500 mb-3">${encontrados} resultado(s) encontrado(s):</p>` + resultados;
        }

        contenedor.innerHTML = resultados;
        document.getElementById('ayuda-contexto-texto').textContent = 'Resultados de búsqueda';
        lucide.createIcons();
    }

    function mostrarAyudaSeccion(seccion) {
        document.getElementById('busqueda-ayuda').value = '';
        document.getElementById('ayuda-contexto-texto').textContent = contenidoAyuda[seccion].titulo;
        document.getElementById('ayuda-contenido').innerHTML = contenidoAyuda[seccion].contenido;
        lucide.createIcons();
    }

    window.togglePanelAyuda = togglePanelAyuda;
    window.filtrarAyuda = filtrarAyuda;
    window.mostrarAyudaSeccion = mostrarAyudaSeccion;

    // ============================================================================
    // CHANGE DETECTION
    // ============================================================================

    function initCertificatumFormChangeDetection() {
        document.querySelectorAll('form[action*="modulo=certificatum"]').forEach(form => {
            const submitBtn = form.querySelector('button.save-button[type="submit"]');
            if (!submitBtn) return;

            const formData = new FormData(form);
            const originalData = {};
            for (let [key, value] of formData.entries()) {
                originalData[key] = value;
            }

            const originalBtnHTML = submitBtn.innerHTML;

            // Estado inicial: botón en gris (sin cambios)
            submitBtn.className = 'px-6 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed transition font-semibold flex items-center gap-2 save-button';
            submitBtn.disabled = true;

            const checkChanges = () => {
                const currentData = new FormData(form);
                let hasChanges = false;

                // Verificar cambios en valores existentes y nuevas keys
                for (let [key, value] of currentData.entries()) {
                    if (key !== 'active_tab' && key !== 'accion') {
                        if (!(key in originalData) || originalData[key] !== value) {
                            hasChanges = true;
                            break;
                        }
                    }
                }

                // Verificar keys que desaparecieron (checkboxes desmarcados)
                if (!hasChanges) {
                    for (let key in originalData) {
                        if (key !== 'active_tab' && key !== 'accion' && !currentData.has(key)) {
                            hasChanges = true;
                            break;
                        }
                    }
                }

                if (hasChanges) {
                    submitBtn.disabled = false;
                    submitBtn.className = 'px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button';
                } else {
                    submitBtn.disabled = true;
                    submitBtn.className = 'px-6 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed transition font-semibold flex items-center gap-2 save-button';
                }
            };

            form.querySelectorAll('input, select, textarea').forEach(field => {
                field.addEventListener('input', checkChanges);
                field.addEventListener('change', checkChanges);
            });

            form.addEventListener('submit', function(e) {
                submitBtn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 inline mr-2 animate-spin"></i>Guardando...';
                submitBtn.disabled = true;
                lucide.createIcons();
            });
        });
    }

    // Inicializar iconos
    lucide.createIcons();

    // ============================================================================
    // PALETA PROPIA DE CERTIFICATUM
    // ============================================================================

    // Paletas predefinidas
    const paletas = {
        'verde-elegante': { primario: '#2E7D32', secundario: '#1B5E20', acento: '#66BB6A' },
        'azul-profesional': { primario: '#1976D2', secundario: '#0D47A1', acento: '#42A5F5' },
        'morado-creativo': { primario: '#7B1FA2', secundario: '#4A148C', acento: '#BA68C8' },
        'naranja-energetico': { primario: '#F57C00', secundario: '#E65100', acento: '#FFB74D' },
        'rojo-institucional': { primario: '#C62828', secundario: '#B71C1C', acento: '#EF5350' },
        'gris-minimalista': { primario: '#424242', secundario: '#212121', acento: '#757575' }
    };

    // Mostrar/ocultar sección de paleta propia según checkbox
    const certificatumUsarPaletaGeneralCheckbox = document.getElementById('certificatum-usar-paleta-general');
    const certificatumPaletaPropiaSection = document.getElementById('certificatum-paleta-propia-section');

    certificatumUsarPaletaGeneralCheckbox?.addEventListener('change', function() {
        if (this.checked) {
            certificatumPaletaPropiaSection.style.display = 'none';
        } else {
            certificatumPaletaPropiaSection.style.display = 'block';
        }
    });

    // Paleta propia de Certificatum
    const certificatumPaletaSelect = document.getElementById('certificatum-paleta-select');
    const certificatumColorPrimario = document.getElementById('certificatum-color-primario');
    const certificatumColorSecundario = document.getElementById('certificatum-color-secundario');
    const certificatumColorAcento = document.getElementById('certificatum-color-acento');
    const certificatumColorPrimarioText = document.getElementById('certificatum-color-primario-text');
    const certificatumColorSecundarioText = document.getElementById('certificatum-color-secundario-text');
    const certificatumColorAcentoText = document.getElementById('certificatum-color-acento-text');

    certificatumPaletaSelect?.addEventListener('change', function() {
        const paleta = this.value;
        if (paleta !== 'personalizado' && paletas[paleta]) {
            certificatumColorPrimario.value = paletas[paleta].primario;
            certificatumColorSecundario.value = paletas[paleta].secundario;
            certificatumColorAcento.value = paletas[paleta].acento;
            certificatumColorPrimarioText.value = paletas[paleta].primario;
            certificatumColorSecundarioText.value = paletas[paleta].secundario;
            certificatumColorAcentoText.value = paletas[paleta].acento;
        }
    });

    // Sync color pickers con text fields (paleta propia)
    certificatumColorPrimario?.addEventListener('input', (e) => { certificatumColorPrimarioText.value = e.target.value; });
    certificatumColorSecundario?.addEventListener('input', (e) => { certificatumColorSecundarioText.value = e.target.value; });
    certificatumColorAcento?.addEventListener('input', (e) => { certificatumColorAcentoText.value = e.target.value; });

    // ============================================================================
    // FUNCIONES DE IA - CERTIFICATUM
    // ============================================================================


    /**
     * Cambiar tab de idioma en Certificatum
     */
    function cambiarTabIdiomaCert(codigo) {
        // Ocultar todos los contenidos
        document.querySelectorAll('.tab-content-idioma').forEach(el => el.classList.add('hidden'));
        // Desactivar todos los botones
        document.querySelectorAll('.tab-btn-idioma').forEach(el => {
            el.classList.remove('border-blue-500', 'text-blue-600', 'bg-blue-50');
            el.classList.add('border-transparent', 'text-gray-500');
        });
        // Mostrar el contenido seleccionado
        const content = document.getElementById('tab-content-' + codigo);
        if (content) content.classList.remove('hidden');
        // Activar el botón seleccionado
        const btn = document.getElementById('tab-btn-' + codigo);
        if (btn) {
            btn.classList.remove('border-transparent', 'text-gray-500');
            btn.classList.add('border-blue-500', 'text-blue-600', 'bg-blue-50');
        }
        // Guardar el idioma activo en el campo hidden
        const hiddenField = document.getElementById('idioma_tab_activo');
        if (hiddenField) hiddenField.value = codigo;
    }

    /**
     * Traducir campo con IA
     */
    async function traducirCampoCertificatum(campo, idiomaOrigen, idiomaDestino) {
        const fieldOrigen = document.getElementById(campo + '_' + idiomaOrigen);
        const fieldDestino = document.getElementById(campo + '_' + idiomaDestino);

        if (!fieldOrigen || !fieldDestino) {
            showToastCertificatum('Error: campos no encontrados', 'error');
            return;
        }

        const textoOrigen = fieldOrigen.value.trim();
        if (!textoOrigen) {
            showToastCertificatum('Primero ingresa el texto en el idioma original', 'warning');
            return;
        }

        const btn = event.target.closest('button');
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader" class="w-3 h-3 inline animate-spin"></i>';
        btn.disabled = true;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            // Usar el mismo endpoint del módulo con acción
            const response = await fetch('index.php?modulo=certificatum', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    accion: 'traducir_ia',
                    texto: textoOrigen,
                    idioma_origen: idiomaOrigen,
                    idioma_destino: idiomaDestino
                })
            });

            const data = await response.json();

            if (data.success && data.traduccion) {
                fieldDestino.value = data.traduccion;
                fieldDestino.dispatchEvent(new Event('input', { bubbles: true }));
                showToastCertificatum('Traducido correctamente', 'success');
            } else {
                showToastCertificatum(data.error || 'Error al traducir', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToastCertificatum('Error de conexión', 'error');
        } finally {
            btn.innerHTML = originalContent;
            btn.disabled = false;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    /**
     * Autocompletar descripción del portal con IA
     */
    async function autocompletarDescripcionCertificatum(codigoIdioma) {
        const field = document.getElementById('certificatum_descripcion_' + codigoIdioma);
        if (!field) return;

        const btn = event.target.closest('button');
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader" class="w-3 h-3 inline animate-spin"></i>';
        btn.disabled = true;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const response = await fetch('index.php?modulo=certificatum', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    accion: 'autocompletar_ia',
                    field_name: 'certificatum_descripcion',
                    idioma: codigoIdioma
                })
            });

            const data = await response.json();

            if (data.success && data.content) {
                field.value = data.content;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                showToastCertificatum('Descripción generada con IA', 'success');
            } else {
                showToastCertificatum(data.error || 'Error al generar', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToastCertificatum('Error de conexión', 'error');
        } finally {
            btn.innerHTML = originalContent;
            btn.disabled = false;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    /**
     * Generar estadísticas de Certificatum con IA (grupo)
     */
    async function generarStatsCertificatumConIA() {
        const btn = event.target.closest('button');
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader" class="w-3 h-3 inline animate-spin"></i> Generando...';
        btn.disabled = true;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        try {
            const response = await fetch('index.php?modulo=certificatum', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    accion: 'generar_stats_ia'
                })
            });

            const data = await response.json();

            if (data.success && data.fields) {
                let generados = 0;
                for (let fieldId in data.fields) {
                    const field = document.getElementById(fieldId);
                    if (field && data.fields[fieldId]) {
                        field.value = data.fields[fieldId];
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                        generados++;
                    }
                }
                showToastCertificatum(`${generados} estadísticas generadas con IA ✨`, 'success');
            } else {
                showToastCertificatum(data.error || 'Error al generar', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToastCertificatum('Error de conexión', 'error');
        } finally {
            btn.innerHTML = originalContent;
            btn.disabled = false;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    /**
     * Toast notification para Certificatum
     */
    function showToastCertificatum(message, type = 'success') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'fixed top-4 right-4 z-50 flex flex-col gap-2';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
        const icon = type === 'success' ? 'check-circle' : 'alert-circle';

        toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] transform transition-all duration-300 translate-x-full`;
        toast.innerHTML = `
            <i data-lucide="${icon}" class="w-5 h-5 flex-shrink-0"></i>
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        `;

        toastContainer.appendChild(toast);
        lucide.createIcons();

        setTimeout(() => { toast.classList.remove('translate-x-full'); }, 10);
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // Hacer funciones globales
    window.autocompletarDescripcionCertificatum = autocompletarDescripcionCertificatum;
    window.generarStatsCertificatumConIA = generarStatsCertificatumConIA;

    // ============================================================================
    // FUNCIONES DE EDICIÓN Y ELIMINACIÓN
    // ============================================================================

    // --- ESTUDIANTES ---
    function editarEstudiante(id, dni, nombre) {
        const nuevoNombre = prompt('Nombre completo:', nombre);
        if (nuevoNombre === null) return;
        const nuevoDni = prompt('DNI:', dni);
        if (nuevoDni === null) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="actualizar_estudiante">
            <input type="hidden" name="id_estudiante" value="${id}">
            <input type="hidden" name="dni" value="${nuevoDni}">
            <input type="hidden" name="nombre_completo" value="${nuevoNombre}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function confirmarEliminarEstudiante(id) {
        if (!confirm('¿Eliminar este estudiante y todas sus inscripciones?')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="eliminar_estudiante">
            <input type="hidden" name="id_estudiante" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // --- CURSOS ---
    let codigoSugeridoActual = '';
    let modoEdicionCurso = false;

    // --- TEMPLATES DE CERTIFICADOS ---
    let templatesCache = null;

    async function cargarTemplatesCertificado() {
        if (templatesCache) return templatesCache;
        try {
            const response = await fetch('?modulo=certificatum', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'accion=obtener_templates'
            });
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
        if (!container) return;

        let html = '';

        templates.forEach(t => {
            const isSelected = t.is_default ? !selectedId : selectedId == t.id_template;
            const borderClass = isSelected ? 'border-purple-500 bg-purple-50' : 'border-gray-200';
            // Tooltip según si tiene template o no
            const tooltipText = t.is_default
                ? 'Diseño estándar del sistema'
                : 'Clic para ver vista previa';

            html += `
                <label class="template-option cursor-pointer" title="${tooltipText}">
                    <input type="radio" name="id_template" value="${t.id_template || ''}" class="hidden" ${isSelected ? 'checked' : ''}>
                    <div class="border-2 ${borderClass} rounded-lg p-3 hover:border-purple-400 transition-colors template-card text-center" data-template="${t.id_template || ''}">
                        <div class="h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded flex items-center justify-center text-gray-500 mb-2">
                            ${t.is_default
                                ? '<i data-lucide="file-check" class="w-8 h-8"></i>'
                                : (t.preview_url
                                    ? `<img src="${t.preview_url}" alt="${t.nombre}" class="h-full w-full object-cover rounded" onerror="this.parentElement.innerHTML='<i data-lucide=\\'image\\' class=\\'w-6 h-6\\'></i>'">`
                                    : '<i data-lucide="layout-template" class="w-6 h-6"></i>'
                                )
                            }
                        </div>
                        <p class="text-xs font-medium text-gray-700 truncate" title="${t.nombre}">${t.nombre}</p>
                        ${!t.is_default ? '<p class="text-[10px] text-purple-500 mt-0.5"><i data-lucide="eye" class="w-2.5 h-2.5 inline"></i> Ver preview</p>' : ''}
                    </div>
                </label>
            `;
        });

        container.innerHTML = html;

        // Event listeners para selección visual
        container.querySelectorAll('.template-option').forEach(option => {
            option.addEventListener('click', function() {
                container.querySelectorAll('.template-card').forEach(card => {
                    card.classList.remove('border-purple-500', 'bg-purple-50');
                    card.classList.add('border-gray-200');
                });
                const card = this.querySelector('.template-card');
                card.classList.remove('border-gray-200');
                card.classList.add('border-purple-500', 'bg-purple-50');

                // Mostrar preview del template seleccionado
                const templateId = card.dataset.template;
                mostrarTemplatePreview(templateId);
            });
        });

        lucide.createIcons();

        // Si hay un template seleccionado (no predeterminado), mostrar preview automáticamente
        if (selectedId) {
            // Pequeño delay para que el DOM se actualice
            setTimeout(() => {
                mostrarTemplatePreview(selectedId);
            }, 100);
        }
    }

    // Mostrar preview dinámico del template
    function mostrarTemplatePreview(templateId) {
        const container = document.getElementById('template-preview-container');
        const iframe = document.getElementById('template-preview-iframe');

        if (!templateId || templateId === '') {
            // Template predeterminado - ocultar preview
            container.classList.add('hidden');
            iframe.src = '';
            return;
        }

        // Obtener nombre del curso desde el formulario (si está disponible)
        const cursoNombreInput = document.getElementById('modal-curso-nombre');
        const cursoNombre = cursoNombreInput ? cursoNombreInput.value.trim() : '';

        // Construir URL de preview (raíz del subdominio multi-tenant)
        const institutio = '<?php echo $instance['slug'] ?? 'sajur'; ?>';
        let previewUrl = `/creare.php?preview=1&template_id=${templateId}&institutio=${institutio}&lang=es_AR`;

        // Agregar nombre del curso si está disponible
        if (cursoNombre) {
            previewUrl += `&curso_nombre=${encodeURIComponent(cursoNombre)}`;
        }

        iframe.src = previewUrl;
        container.classList.remove('hidden');
    }

    // Toggle para mostrar/ocultar preview
    function toggleTemplatePreview() {
        const container = document.getElementById('template-preview-container');
        container.classList.add('hidden');
        document.getElementById('template-preview-iframe').src = '';
    }

    async function cargarTemplateCurso(idCurso) {
        try {
            const response = await fetch('?modulo=certificatum', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_template_curso&id_curso=${idCurso}`
            });
            const data = await response.json();
            if (data.success && data.template) {
                return data.template.id_template;
            }
        } catch (e) {
            console.error('Error obteniendo template del curso:', e);
        }
        return null;
    }

    // Función para mostrar/ocultar campos de firmantes
    function toggleFirmanteFields(num, enabled) {
        const fieldsDiv = document.getElementById('firmante' + num + '-fields');
        if (fieldsDiv) {
            fieldsDiv.style.opacity = enabled ? '1' : '0.4';
            fieldsDiv.querySelectorAll('input').forEach(input => {
                input.disabled = !enabled;
            });
        }
    }

    // Función para mostrar/ocultar campos de demora propia
    function toggleDemoraFields(modo) {
        const fieldsDiv = document.getElementById('demora-propia-fields');
        const tipoSelect = document.getElementById('modal-curso-demora-tipo');
        if (fieldsDiv) {
            if (modo === 'global') {
                fieldsDiv.classList.add('hidden');
            } else {
                fieldsDiv.classList.remove('hidden');
                // Si el tipo es 'inmediato' y estamos activando demora propia,
                // cambiar a 'horas' por defecto para que tenga sentido
                if (tipoSelect.value === 'inmediato') {
                    tipoSelect.value = 'horas';
                }
                toggleDemoraInputs(); // Actualizar inputs según tipo seleccionado
            }
        }
    }

    // Función para mostrar/ocultar inputs según tipo de demora
    function toggleDemoraInputs() {
        const tipo = document.getElementById('modal-curso-demora-tipo').value;
        const valorContainer = document.getElementById('demora-valor-container');
        const fechaContainer = document.getElementById('demora-fecha-container');
        const unidadSpan = document.getElementById('demora-valor-unidad');
        const descripcion = document.getElementById('demora-descripcion');

        // Ocultar ambos por defecto
        valorContainer.classList.add('hidden');
        fechaContainer.classList.add('hidden');

        switch(tipo) {
            case 'inmediato':
                descripcion.textContent = 'El certificado estará disponible inmediatamente al aprobar.';
                break;
            case 'horas':
                valorContainer.classList.remove('hidden');
                unidadSpan.textContent = 'horas';
                descripcion.textContent = 'El certificado estará disponible X horas después de aprobar.';
                break;
            case 'dias':
                valorContainer.classList.remove('hidden');
                unidadSpan.textContent = 'días';
                descripcion.textContent = 'El certificado estará disponible X días después de aprobar.';
                break;
            case 'meses':
                valorContainer.classList.remove('hidden');
                unidadSpan.textContent = 'meses';
                descripcion.textContent = 'El certificado estará disponible X meses después de aprobar.';
                break;
            case 'fecha':
                fechaContainer.classList.remove('hidden');
                descripcion.textContent = 'El certificado estará disponible a partir de la fecha indicada.';
                break;
        }
    }

    // Función para eliminar firma de curso
    function eliminarFirmaCurso(num) {
        // Limpiar la URL guardada
        document.getElementById('modal-curso-firma' + num + '-url').value = '';
        // Limpiar el input de archivo
        document.getElementById('modal-curso-firma' + num).value = '';

        // Si hay firma heredada del general, mostrarla con opacidad
        const preview = document.getElementById('firma' + num + '-preview');
        const img = document.getElementById('firma' + num + '-preview-img');
        const firmaGeneral = num === 1 ? firmasGenerales.firma1 : firmasGenerales.firma2;

        if (firmaGeneral) {
            // Usar proxy PHP para obtener imagen
            const filename = firmaGeneral.split('/').pop();
            img.src = 'firma-imagen.php?file=' + encodeURIComponent(filename);
            img.title = 'Firma heredada del general (subir una nueva para reemplazar)';
            preview.classList.remove('hidden');
            preview.classList.add('opacity-50');
        } else {
            preview.classList.add('hidden');
        }
    }

    // Preview instantáneo cuando se selecciona archivo de firma
    function setupFirmaPreview(num) {
        const input = document.getElementById('modal-curso-firma' + num);
        if (input) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const img = document.getElementById('firma' + num + '-preview-img');
                        const preview = document.getElementById('firma' + num + '-preview');
                        img.src = event.target.result;
                        img.title = 'Nueva firma (se guardará al guardar el curso)';
                        preview.classList.remove('hidden', 'opacity-50');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    // Inicializar previews cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        setupFirmaPreview(1);
        setupFirmaPreview(2);
    });

    async function abrirModalCurso() {
        modoEdicionCurso = false;
        document.getElementById('modal-curso-titulo').textContent = 'Nuevo Curso';
        document.getElementById('modal-curso-accion').value = 'crear_curso';
        document.getElementById('modal-curso-btn-texto').textContent = 'Crear Curso';
        document.getElementById('form-modal-curso').reset();
        document.getElementById('modal-curso-id').value = '';

        // Mostrar selector de código sugerido, ocultar personalizado
        document.getElementById('codigo-sugerido-container').classList.remove('hidden');
        document.getElementById('codigo-personalizado-container').classList.add('hidden');
        document.getElementById('usar-codigo-personalizado').checked = false;

        // Resetear checkboxes de firmantes a checked
        document.getElementById('modal-curso-usar-firmante1').checked = true;
        document.getElementById('modal-curso-usar-firmante2').checked = true;
        toggleFirmanteFields(1, true);
        toggleFirmanteFields(2, true);

        // Limpiar previews y URLs de firmas
        document.getElementById('firma1-preview').classList.add('hidden');
        document.getElementById('firma2-preview').classList.add('hidden');
        document.getElementById('modal-curso-firma1-url').value = '';
        document.getElementById('modal-curso-firma2-url').value = '';

        // Limpiar competencias
        limpiarCompetencias();

        // Obtener código sugerido inicial
        actualizarCodigoSugerido();

        // Cargar templates (seleccionar predeterminado)
        const templates = await cargarTemplatesCertificado();
        renderTemplateSelector(templates, null);

        document.getElementById('modal-curso').classList.remove('hidden');
        lucide.createIcons();
    }

    function cerrarModalCurso() {
        document.getElementById('modal-curso').classList.add('hidden');
    }

    async function editarCurso(cursoData) {
        modoEdicionCurso = true;
        // cursoData es un objeto JSON con todos los datos del curso
        document.getElementById('modal-curso-titulo').textContent = 'Editar Curso';
        document.getElementById('modal-curso-accion').value = 'actualizar_curso';
        document.getElementById('modal-curso-btn-texto').textContent = 'Guardar Cambios';

        // En modo edición, ocultar código sugerido y mostrar campo directo
        document.getElementById('codigo-sugerido-container').classList.add('hidden');
        document.getElementById('codigo-personalizado-container').classList.remove('hidden');
        document.getElementById('modal-curso-codigo').required = true;

        // Llenar campos
        document.getElementById('modal-curso-id').value = cursoData.id_curso || '';
        document.getElementById('modal-curso-codigo').value = cursoData.codigo_curso || '';
        document.getElementById('modal-curso-nombre').value = cursoData.nombre_curso || '';
        document.getElementById('modal-curso-descripcion').value = cursoData.descripcion || '';
        document.getElementById('modal-curso-categoria').value = cursoData.categoria || '';
        document.getElementById('modal-curso-tipo').value = cursoData.tipo_curso || 'Curso';
        document.getElementById('modal-curso-modalidad').value = cursoData.modalidad || 'Virtual';
        document.getElementById('modal-curso-nivel').value = cursoData.nivel || 'Todos los niveles';
        document.getElementById('modal-curso-horas').value = cursoData.carga_horaria || '';
        document.getElementById('modal-curso-semanas').value = cursoData.duracion_semanas || '';
        document.getElementById('modal-curso-cupo').value = cursoData.cupo_maximo || '';
        document.getElementById('modal-curso-activo').value = cursoData.activo ? '1' : '0';

        // Fechas del curso
        document.getElementById('modal-curso-fecha-inicio').value = cursoData.fecha_inicio || '';
        document.getElementById('modal-curso-fecha-fin').value = cursoData.fecha_fin || '';

        // Ciudad de emisión
        document.getElementById('modal-curso-ciudad').value = cursoData.ciudad_emision || '';

        // Firmantes del certificado
        document.getElementById('modal-curso-firmante1-nombre').value = cursoData.firmante_1_nombre || '';
        document.getElementById('modal-curso-firmante1-cargo').value = cursoData.firmante_1_cargo || '';
        document.getElementById('modal-curso-firmante2-nombre').value = cursoData.firmante_2_nombre || '';
        document.getElementById('modal-curso-firmante2-cargo').value = cursoData.firmante_2_cargo || '';

        // Checkboxes de usar firmantes (default true si no está definido)
        const usarFirmante1 = cursoData.usar_firmante_1 !== 0 && cursoData.usar_firmante_1 !== '0';
        const usarFirmante2 = cursoData.usar_firmante_2 !== 0 && cursoData.usar_firmante_2 !== '0';
        document.getElementById('modal-curso-usar-firmante1').checked = usarFirmante1;
        document.getElementById('modal-curso-usar-firmante2').checked = usarFirmante2;
        toggleFirmanteFields(1, usarFirmante1);
        toggleFirmanteFields(2, usarFirmante2);

        // Configuración de demora del certificado
        const usarDemoraGlobal = cursoData.usar_demora_global !== 0 && cursoData.usar_demora_global !== '0' && cursoData.usar_demora_global !== false;
        document.getElementById('modal-curso-demora-global').checked = usarDemoraGlobal;
        document.getElementById('modal-curso-demora-propia').checked = !usarDemoraGlobal;

        // Determinar tipo de demora: si hay valor en horas pero tipo es inmediato, asumir 'horas'
        let demoraValor = cursoData.demora_certificado_horas || 1;
        let demoraTipo = cursoData.demora_tipo || 'inmediato';
        if (demoraTipo === 'inmediato' && demoraValor > 0 && !usarDemoraGlobal) {
            demoraTipo = 'horas'; // Corregir estado inconsistente
        }

        document.getElementById('modal-curso-demora-tipo').value = demoraTipo;
        document.getElementById('modal-curso-demora-valor').value = demoraValor;
        document.getElementById('modal-curso-demora-fecha').value = cursoData.demora_fecha || '';
        toggleDemoraFields(usarDemoraGlobal ? 'global' : 'propia');

        // Firmas del curso (URLs de imágenes)
        document.getElementById('modal-curso-firma1-url').value = cursoData.firmante_1_firma_url || '';
        document.getElementById('modal-curso-firma2-url').value = cursoData.firmante_2_firma_url || '';

        // Función para cargar firma del curso via AJAX (devuelve base64)
        async function cargarFirmaCursoBase64(firmaPath) {
            if (!firmaPath) return null;
            try {
                const response = await fetch('?modulo=certificatum&accion=obtener_firma_base64&path=' + encodeURIComponent(firmaPath));
                const data = await response.json();
                return data.success ? data.base64 : null;
            } catch (e) {
                console.error('Error cargando firma:', e);
                return null;
            }
        }

        // Mostrar preview de firma 1 (propia del curso o heredada del general)
        const preview1 = document.getElementById('firma1-preview');
        const img1 = document.getElementById('firma1-preview-img');
        if (cursoData.firmante_1_firma_url) {
            // Cargar firma del curso via AJAX
            const base64 = await cargarFirmaCursoBase64(cursoData.firmante_1_firma_url);
            if (base64) {
                img1.src = base64;
                img1.title = 'Firma propia del curso';
                preview1.classList.remove('hidden', 'opacity-50');
            } else {
                preview1.classList.add('hidden');
            }
        } else if (firmasGenerales.firma1Base64) {
            img1.src = firmasGenerales.firma1Base64;
            img1.title = 'Firma heredada del general (subir una nueva para reemplazar)';
            preview1.classList.remove('hidden');
            preview1.classList.add('opacity-50');
        } else {
            preview1.classList.add('hidden');
        }

        // Mostrar preview de firma 2 (propia del curso o heredada del general)
        const preview2 = document.getElementById('firma2-preview');
        const img2 = document.getElementById('firma2-preview-img');
        if (cursoData.firmante_2_firma_url) {
            // Cargar firma del curso via AJAX
            const base64 = await cargarFirmaCursoBase64(cursoData.firmante_2_firma_url);
            if (base64) {
                img2.src = base64;
                img2.title = 'Firma propia del curso';
                preview2.classList.remove('hidden', 'opacity-50');
            } else {
                preview2.classList.add('hidden');
            }
        } else if (firmasGenerales.firma2Base64) {
            img2.src = firmasGenerales.firma2Base64;
            img2.title = 'Firma heredada del general (subir una nueva para reemplazar)';
            preview2.classList.remove('hidden');
            preview2.classList.add('opacity-50');
        } else {
            preview2.classList.add('hidden');
        }

        // Limpiar inputs de archivo (no se pueden pre-cargar)
        document.getElementById('modal-curso-firma1').value = '';
        document.getElementById('modal-curso-firma2').value = '';

        // Cargar competencias del curso
        cargarCompetenciasCurso(cursoData.id_curso);

        // Cargar templates y seleccionar el del curso
        const templates = await cargarTemplatesCertificado();
        const currentTemplateId = cursoData.id_template || null;
        renderTemplateSelector(templates, currentTemplateId);

        document.getElementById('modal-curso').classList.remove('hidden');
        lucide.createIcons();
    }

    // ============================================================================
    // FUNCIONES DE COMPETENCIAS
    // ============================================================================

    let competenciasActuales = [];

    // Limpiar todas las competencias del modal
    function limpiarCompetencias() {
        competenciasActuales = [];
        renderizarCompetencias();
        document.getElementById('nueva-competencia-input').value = '';
    }

    // Cargar competencias existentes de un curso
    async function cargarCompetenciasCurso(idCurso) {
        limpiarCompetencias();
        console.log('Cargando competencias para curso:', idCurso);
        try {
            const response = await fetch('?modulo=certificatum', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_competencias&id_curso=${idCurso}`
            });
            const text = await response.text();
            console.log('Respuesta raw:', text);
            try {
                const data = JSON.parse(text);
                console.log('Datos parseados:', data);
                if (data.success && data.competencias) {
                    competenciasActuales = data.competencias.map(c => c.competencia);
                    renderizarCompetencias();
                    console.log('Competencias cargadas:', competenciasActuales);
                }
            } catch (parseError) {
                console.error('Error parseando JSON:', parseError, 'Texto recibido:', text);
            }
        } catch (error) {
            console.error('Error cargando competencias:', error);
        }
    }

    // Agregar una nueva competencia
    function agregarCompetencia() {
        const input = document.getElementById('nueva-competencia-input');
        const valor = input.value.trim();

        if (!valor) {
            input.focus();
            return;
        }

        // Evitar duplicados
        if (competenciasActuales.includes(valor)) {
            alert('Esta competencia ya está agregada');
            return;
        }

        competenciasActuales.push(valor);
        renderizarCompetencias();
        input.value = '';
        input.focus();
    }

    // Eliminar una competencia por índice
    function eliminarCompetencia(index) {
        competenciasActuales.splice(index, 1);
        renderizarCompetencias();
    }

    // Renderizar la lista de competencias
    function renderizarCompetencias() {
        const container = document.getElementById('competencias-container');
        const jsonInput = document.getElementById('competencias-json');

        if (competenciasActuales.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-gray-400 text-sm border-2 border-dashed border-gray-200 rounded-lg">
                    <i data-lucide="sparkles" class="w-5 h-5 mx-auto mb-1 opacity-50"></i>
                    <p>No hay competencias agregadas</p>
                </div>
            `;
        } else {
            container.innerHTML = competenciasActuales.map((comp, index) => `
                <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2.5 group">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                    <span class="flex-1 text-gray-800">${escapeHtml(comp)}</span>
                    <button type="button" onclick="eliminarCompetencia(${index})"
                            class="text-gray-400 hover:text-red-600 transition-colors opacity-0 group-hover:opacity-100">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            `).join('');
        }

        // Actualizar campo oculto con JSON
        jsonInput.value = JSON.stringify(competenciasActuales);
        lucide.createIcons();
    }

    // Helper para escapar HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Obtener código sugerido del servidor
    async function actualizarCodigoSugerido() {
        const tipoCurso = document.getElementById('modal-curso-tipo').value;
        try {
            const response = await fetch('?modulo=certificatum', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener_codigo_sugerido&tipo_curso=${encodeURIComponent(tipoCurso)}`
            });
            const data = await response.json();
            codigoSugeridoActual = data.codigo;
            document.getElementById('codigo-sugerido-valor').textContent = data.codigo;
            document.getElementById('modal-curso-codigo-hidden').value = data.codigo;
        } catch (error) {
            console.error('Error obteniendo código sugerido:', error);
        }
    }

    // Toggle entre código sugerido y personalizado
    function toggleCodigoPersonalizado() {
        const usarPersonalizado = document.getElementById('usar-codigo-personalizado').checked;
        const containerPersonalizado = document.getElementById('codigo-personalizado-container');
        const inputCodigo = document.getElementById('modal-curso-codigo');
        const inputHidden = document.getElementById('modal-curso-codigo-hidden');

        if (usarPersonalizado) {
            containerPersonalizado.classList.remove('hidden');
            inputCodigo.required = true;
            inputCodigo.name = 'codigo_curso';
            inputHidden.name = '';
            inputCodigo.focus();
        } else {
            containerPersonalizado.classList.add('hidden');
            inputCodigo.required = false;
            inputCodigo.name = '';
            inputHidden.name = 'codigo_curso';
            inputHidden.value = codigoSugeridoActual;
        }
    }

    // Validar formato del código en tiempo real
    function validarCodigoCurso() {
        const input = document.getElementById('modal-curso-codigo');
        const errorEl = document.getElementById('codigo-error');
        const codigo = input.value.trim();

        let error = null;

        if (codigo.length > 0 && codigo.length < 3) {
            error = 'El código debe tener al menos 3 caracteres';
        } else if (codigo.startsWith('-') || codigo.endsWith('-')) {
            error = 'No puede empezar ni terminar con guion';
        } else if (codigo.includes('--')) {
            error = 'No puede tener guiones consecutivos';
        }

        if (error) {
            errorEl.textContent = error;
            errorEl.classList.remove('hidden');
            input.classList.add('border-red-500');
        } else {
            errorEl.classList.add('hidden');
            input.classList.remove('border-red-500');
        }
    }

    // Antes de enviar el formulario de curso
    document.getElementById('form-modal-curso')?.addEventListener('submit', function(e) {
        const usarPersonalizado = document.getElementById('usar-codigo-personalizado').checked;
        const inputHidden = document.getElementById('modal-curso-codigo-hidden');
        const inputCodigo = document.getElementById('modal-curso-codigo');

        // Si no usa código personalizado y es modo crear, usar el sugerido
        if (!modoEdicionCurso && !usarPersonalizado) {
            inputHidden.name = 'codigo_curso';
            inputCodigo.name = '';
        }
    });

    function confirmarEliminarCurso(id) {
        if (!confirm('¿Eliminar este curso? Si tiene inscripciones, será desactivado en lugar de eliminado.')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="eliminar_curso">
            <input type="hidden" name="id_curso" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // --- INSCRIPCIONES ---
    // ============================================================================
    // FUNCIONES PARA MODAL DE INSCRIPCIONES
    // ============================================================================

    function abrirModalNuevaInscripcion() {
        // Limpiar modal
        document.getElementById('modal-inscripcion-titulo').textContent = 'Nueva Inscripción';
        document.getElementById('modal-inscripcion-accion').value = 'crear_inscripcion';
        document.getElementById('modal-inscripcion-id').value = '';
        document.getElementById('modal-inscripcion-miembro').value = '';
        document.getElementById('modal-inscripcion-curso').value = '';
        document.getElementById('modal-inscripcion-estado').value = 'Inscrito';
        document.getElementById('modal-inscripcion-pago').value = 'Pendiente';
        document.getElementById('modal-inscripcion-fecha-inicio').value = '';
        document.getElementById('modal-inscripcion-fecha-fin').value = '';
        document.getElementById('modal-inscripcion-nota').value = '';
        document.getElementById('modal-inscripcion-asistencia').value = '';
        document.getElementById('modal-inscripcion-observaciones').value = '';

        // Mostrar campos de selección, ocultar campos de solo lectura
        document.getElementById('campo-estudiante').classList.remove('hidden');
        document.getElementById('campo-curso').classList.remove('hidden');
        document.getElementById('info-estudiante-edicion').classList.add('hidden');
        document.getElementById('info-curso-edicion').classList.add('hidden');

        // Habilitar required en selects
        document.getElementById('modal-inscripcion-miembro').required = true;
        document.getElementById('modal-inscripcion-curso').required = true;

        // Marcar checkbox de notificar por defecto en creación
        document.getElementById('modal-inscripcion-notificar').checked = true;
        document.getElementById('notificar-sin-email-warning').classList.add('hidden');

        // Ocultar checkbox de evaluación hasta que se seleccione curso
        document.getElementById('notificar-evaluacion-inscripcion-container').classList.add('hidden');
        document.getElementById('modal-inscripcion-notificar-eval').checked = false;
        document.getElementById('modal-inscripcion-id-eval').value = '';

        // Mostrar modal
        document.getElementById('modal-inscripcion').classList.remove('hidden');
        lucide.createIcons();
    }

    // Verificar si hay evaluación activa en el curso seleccionado
    function verificarEvaluacionActivaEnCurso(idCurso) {
        const container = document.getElementById('notificar-evaluacion-inscripcion-container');
        const checkbox = document.getElementById('modal-inscripcion-notificar-eval');
        const hiddenInput = document.getElementById('modal-inscripcion-id-eval');
        const infoText = document.getElementById('eval-activa-info');

        if (idCurso && evaluacionesActivasPorCurso[idCurso]) {
            const eval = evaluacionesActivasPorCurso[idCurso];
            container.classList.remove('hidden');
            hiddenInput.value = eval.id_evaluatio;

            let info = `Evaluación: ${eval.nombre}`;
            if (eval.fecha_fin) {
                const fecha = new Date(eval.fecha_fin);
                info += ` (hasta ${fecha.toLocaleDateString('es-AR')})`;
            }
            infoText.textContent = info;

            // Por defecto NO marcar (el usuario decidió que default sea NO)
            checkbox.checked = false;
        } else {
            container.classList.add('hidden');
            checkbox.checked = false;
            hiddenInput.value = '';
            infoText.textContent = '';
        }
    }

    // Verificar si el estudiante seleccionado tiene email
    function verificarEmailEstudiante(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const email = selectedOption.getAttribute('data-email');
        const warningEl = document.getElementById('notificar-sin-email-warning');

        if (!email || email.trim() === '') {
            warningEl.classList.remove('hidden');
        } else {
            warningEl.classList.add('hidden');
        }
    }

    function abrirModalEditarInscripcion(insc) {
        // Configurar modal para edición
        document.getElementById('modal-inscripcion-titulo').textContent = 'Editar Inscripción';
        document.getElementById('modal-inscripcion-accion').value = 'actualizar_inscripcion';
        document.getElementById('modal-inscripcion-id').value = insc.id_inscripcion;

        // Ocultar selects, mostrar info de solo lectura
        document.getElementById('campo-estudiante').classList.add('hidden');
        document.getElementById('campo-curso').classList.add('hidden');
        document.getElementById('info-estudiante-edicion').classList.remove('hidden');
        document.getElementById('info-curso-edicion').classList.remove('hidden');

        // Deshabilitar required en selects ocultos
        document.getElementById('modal-inscripcion-miembro').required = false;
        document.getElementById('modal-inscripcion-curso').required = false;

        // Mostrar info de estudiante y curso
        document.getElementById('texto-estudiante-edicion').textContent = `${insc.dni} - ${insc.nombre_completo}`;
        document.getElementById('texto-curso-edicion').textContent = `${insc.codigo_curso} - ${insc.nombre_curso}`;

        // Llenar campos
        document.getElementById('modal-inscripcion-estado').value = insc.estado || 'Inscrito';
        document.getElementById('modal-inscripcion-pago').value = insc.estado_pago || 'Pendiente';
        document.getElementById('modal-inscripcion-fecha-inicio').value = insc.fecha_inicio || '';
        document.getElementById('modal-inscripcion-fecha-fin').value = insc.fecha_finalizacion || '';
        document.getElementById('modal-inscripcion-nota').value = insc.nota_final || '';
        document.getElementById('modal-inscripcion-asistencia').value = insc.asistencia || '';
        document.getElementById('modal-inscripcion-observaciones').value = insc.observaciones || '';

        // Desmarcar checkbox de notificar (por defecto no notificar al editar)
        document.getElementById('modal-inscripcion-notificar').checked = false;

        // Actualizar texto del checkbox según si tiene email
        const warningEl = document.getElementById('notificar-sin-email-warning');
        if (!insc.email || insc.email.trim() === '') {
            warningEl.classList.remove('hidden');
        } else {
            warningEl.classList.add('hidden');
        }

        // Verificar si hay evaluación activa para este curso (en modo edición)
        const idCurso = insc.id_curso;
        if (idCurso && evaluacionesActivasPorCurso[idCurso]) {
            const evalData = evaluacionesActivasPorCurso[idCurso];
            const container = document.getElementById('notificar-evaluacion-inscripcion-container');
            const infoText = document.getElementById('eval-activa-info');

            container.classList.remove('hidden');
            document.getElementById('modal-inscripcion-id-eval').value = evalData.id_evaluatio;
            document.getElementById('modal-inscripcion-notificar-eval').checked = false;

            let info = `Evaluación: ${evalData.nombre}`;
            if (evalData.fecha_fin) {
                const fecha = new Date(evalData.fecha_fin);
                info += ` (hasta ${fecha.toLocaleDateString('es-AR')})`;
            }
            infoText.textContent = info;
        } else {
            document.getElementById('notificar-evaluacion-inscripcion-container').classList.add('hidden');
            document.getElementById('modal-inscripcion-notificar-eval').checked = false;
            document.getElementById('modal-inscripcion-id-eval').value = '';
        }

        // Mostrar modal
        document.getElementById('modal-inscripcion').classList.remove('hidden');
        lucide.createIcons();
    }

    function cerrarModalInscripcion() {
        document.getElementById('modal-inscripcion').classList.add('hidden');
    }

    function confirmarEliminarInscripcion(id) {
        if (!confirm('¿Está seguro de eliminar esta inscripción? Esta acción no se puede deshacer.')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="eliminar_inscripcion">
            <input type="hidden" name="id_inscripcion" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function reenviarNotificacion(id, nombre) {
        if (!confirm(`¿Reenviar notificación por email a ${nombre}?`)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="reenviar_notificacion">
            <input type="hidden" name="id_inscripcion" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function notificarEvaluacionAEstudiante(idInscripcion, idEvaluatio, nombreEstudiante, nombreEvaluacion) {
        if (!confirm(`¿Enviar notificación de evaluación "${nombreEvaluacion}" a ${nombreEstudiante}?`)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="notificar_evaluacion_estudiante">
            <input type="hidden" name="id_inscripcion" value="${idInscripcion}">
            <input type="hidden" name="id_evaluatio" value="${idEvaluatio}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function notificarCertificadoDisponible(idInscripcion, nombreEstudiante, nombreCurso) {
        if (!confirm(`¿Enviar notificación de certificado disponible a ${nombreEstudiante} para el curso "${nombreCurso}"?`)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="notificar_certificado_disponible">
            <input type="hidden" name="id_inscripcion" value="${idInscripcion}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById('modal-inscripcion')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalInscripcion();
        }
    });

    // ============================================================================
    // FUNCIONES PARA SELECCIÓN MASIVA Y ENVÍO DE NOTIFICACIONES
    // ============================================================================

    // Actualiza el contador y muestra/oculta la barra de acciones masivas
    function actualizarSeleccion() {
        const checkboxes = document.querySelectorAll('.checkbox-inscripcion:checked');
        const contador = checkboxes.length;
        const barra = document.getElementById('barra-acciones-masivas');
        const contadorSpan = document.getElementById('contador-seleccionados');
        const checkboxTodos = document.getElementById('checkbox-todos-inscripciones');

        contadorSpan.textContent = contador;

        if (contador > 0) {
            barra.classList.remove('hidden');
            // Refrescar iconos de Lucide
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } else {
            barra.classList.add('hidden');
        }

        // Actualizar estado del checkbox "todos"
        const todosCheckboxes = document.querySelectorAll('.checkbox-inscripcion');
        if (checkboxTodos) {
            checkboxTodos.checked = todosCheckboxes.length > 0 && checkboxes.length === todosCheckboxes.length;
            checkboxTodos.indeterminate = checkboxes.length > 0 && checkboxes.length < todosCheckboxes.length;
        }
    }

    // Seleccionar/deseleccionar todos los checkboxes
    function toggleSeleccionarTodos(checkboxTodos) {
        const checkboxes = document.querySelectorAll('.checkbox-inscripcion');
        checkboxes.forEach(cb => cb.checked = checkboxTodos.checked);
        actualizarSeleccion();
    }

    // Deseleccionar todos
    function deseleccionarTodos() {
        const checkboxes = document.querySelectorAll('.checkbox-inscripcion');
        checkboxes.forEach(cb => cb.checked = false);
        const checkboxTodos = document.getElementById('checkbox-todos-inscripciones');
        if (checkboxTodos) checkboxTodos.checked = false;
        actualizarSeleccion();
    }

    // Acciones masivas para Inscripciones
    function accionMasivaInscripciones(accion) {
        const checkboxes = document.querySelectorAll('.checkbox-inscripcion:checked');
        if (checkboxes.length === 0) {
            showToast('No hay inscripciones seleccionadas', 'error');
            return;
        }

        const seleccionados = Array.from(checkboxes).map(cb => ({
            id: cb.value,
            dni: cb.dataset.dni,
            nombre: cb.dataset.nombre,
            email: cb.dataset.email,
            curso: cb.dataset.curso,
            codigoCurso: cb.dataset.codigoCurso,
            estado: cb.dataset.estado,
            nota: cb.dataset.nota
        }));

        switch (accion) {
            case 'exportar':
                exportarInscripcionesSeleccionados(seleccionados);
                break;
            case 'email':
                prepararEmailMasivoInscripciones(seleccionados);
                break;
            case 'eliminar':
                confirmarEliminacionMasivaInscripciones(seleccionados);
                break;
        }
    }

    function exportarInscripcionesSeleccionados(seleccionados) {
        const BOM = '\uFEFF';
        let csv = BOM + 'DNI,Nombre,Email,Curso,Código Curso,Estado,Nota\n';
        seleccionados.forEach(i => {
            csv += `"${i.dni}","${i.nombre}","${i.email}","${i.curso}","${i.codigoCurso}","${i.estado}","${i.nota}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `inscripciones_seleccionadas_${new Date().toISOString().slice(0,10)}.csv`;
        a.click();
        URL.revokeObjectURL(url);
        showToast(`${seleccionados.length} inscripción(es) exportada(s)`, 'success');
    }

    function prepararEmailMasivoInscripciones(seleccionados) {
        const emailsValidos = seleccionados.filter(i => i.email && i.email.trim() !== '');

        if (emailsValidos.length === 0) {
            showToast('Ninguna inscripción seleccionada tiene email', 'error');
            return;
        }

        const emailsList = emailsValidos.map(i => i.email).join(', ');

        const contenido = `
            <div class="space-y-4">
                <p class="text-sm text-gray-600">${emailsValidos.length} email(s) de ${seleccionados.length} inscripción(es) seleccionada(s)</p>
                <textarea id="emails-inscripciones-para-copiar" readonly
                          class="w-full h-32 p-3 border rounded-lg bg-gray-50 text-sm font-mono">${emailsList}</textarea>
                <div class="flex gap-2">
                    <button onclick="copiarEmailsInscripcionesPortapapeles()"
                            class="flex-1 bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 flex items-center justify-center gap-2">
                        <i data-lucide="copy" class="w-4 h-4"></i> Copiar emails
                    </button>
                    <button onclick="cerrarModalEmails()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cerrar
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-3">💡 Próximamente: Envío masivo via SendGrid</p>
            </div>
        `;

        mostrarModalGenerico('Enviar Email a Estudiantes Inscriptos', contenido);
    }

    function copiarEmailsInscripcionesPortapapeles() {
        const textarea = document.getElementById('emails-inscripciones-para-copiar');
        textarea.select();
        document.execCommand('copy');
        showToast('Emails copiados al portapapeles', 'success');
    }

    function confirmarEliminacionMasivaInscripciones(seleccionados) {
        const nombres = seleccionados.slice(0, 3).map(i => `${i.nombre} (${i.codigoCurso})`).join(', ');
        const mensaje = seleccionados.length > 3
            ? `${nombres} y ${seleccionados.length - 3} más`
            : nombres;

        if (confirm(`¿Estás seguro de eliminar ${seleccionados.length} inscripción(es)?\n\n${mensaje}\n\nEsta acción no se puede deshacer.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '?modulo=certificatum';

            const accionInput = document.createElement('input');
            accionInput.type = 'hidden';
            accionInput.name = 'accion';
            accionInput.value = 'eliminar_inscripciones_masivo';
            form.appendChild(accionInput);

            seleccionados.forEach(i => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = i.id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }
    }

    // Enviar notificación de evaluación masiva
    function enviarNotificacionEvaluacionMasiva() {
        const selectEval = document.getElementById('select-evaluacion-masiva');
        const idEvaluatio = selectEval.value;

        if (!idEvaluatio) {
            showToast('Seleccioná una evaluación primero', 'error');
            return;
        }

        // Obtener el id_curso de la evaluación seleccionada
        const selectedOption = selectEval.options[selectEval.selectedIndex];
        const cursoEvaluacion = selectedOption.dataset.curso;
        const evalNombre = selectedOption.text;

        // Obtener TODAS las inscripciones seleccionadas
        const todasCheckboxes = document.querySelectorAll('.checkbox-inscripcion:checked');
        if (todasCheckboxes.length === 0) {
            showToast('No hay inscripciones seleccionadas', 'error');
            return;
        }

        // Filtrar solo las inscripciones que corresponden al curso de la evaluación
        const checkboxesDelCurso = Array.from(todasCheckboxes).filter(cb => cb.dataset.idCurso == cursoEvaluacion);
        const checkboxesOtroCurso = todasCheckboxes.length - checkboxesDelCurso.length;

        if (checkboxesDelCurso.length === 0) {
            showToast(`Ninguna inscripción seleccionada corresponde al curso de "${evalNombre}"`, 'error');
            return;
        }

        // Verificar cuántos tienen email
        let sinEmail = 0;
        checkboxesDelCurso.forEach(cb => {
            if (!cb.dataset.email || cb.dataset.email === '') sinEmail++;
        });

        // Construir mensaje de confirmación
        let mensaje = `📧 Enviar notificación de evaluación:\n"${evalNombre}"\n\n`;
        mensaje += `✅ ${checkboxesDelCurso.length} estudiante(s) del curso recibirán la notificación`;

        if (checkboxesOtroCurso > 0) {
            mensaje += `\n⚠️ ${checkboxesOtroCurso} seleccionado(s) son de otros cursos y NO se notificarán`;
        }
        if (sinEmail > 0) {
            mensaje += `\n📭 ${sinEmail} no tienen email registrado`;
        }

        mensaje += `\n\n¿Continuar?`;

        if (!confirm(mensaje)) return;

        // Recolectar solo IDs del curso correspondiente
        const ids = checkboxesDelCurso.map(cb => cb.value).join(',');

        // Crear y enviar formulario
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="notificar_evaluacion_masivo">
            <input type="hidden" name="id_evaluatio" value="${idEvaluatio}">
            <input type="hidden" name="ids_inscripciones" value="${ids}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Exponer funciones globalmente
    window.abrirModalNuevaInscripcion = abrirModalNuevaInscripcion;
    window.abrirModalEditarInscripcion = abrirModalEditarInscripcion;
    window.cerrarModalInscripcion = cerrarModalInscripcion;
    window.confirmarEliminarInscripcion = confirmarEliminarInscripcion;
    window.notificarCertificadoDisponible = notificarCertificadoDisponible;
    window.actualizarSeleccion = actualizarSeleccion;
    window.toggleSeleccionarTodos = toggleSeleccionarTodos;
    window.deseleccionarTodos = deseleccionarTodos;
    window.enviarNotificacionEvaluacionMasiva = enviarNotificacionEvaluacionMasiva;

    // ============================================================================
    // FUNCIONES PARA ASIGNACIONES DOCENTES
    // ============================================================================

    function abrirModalEditarAsignacion(asig) {
        document.getElementById('modal-asignacion-id').value = asig.id_participacion;
        document.getElementById('modal-asignacion-docente').textContent = asig.dni + ' - ' + asig.nombre_completo;
        document.getElementById('modal-asignacion-curso').textContent = asig.codigo_curso + ' - ' + asig.nombre_curso;
        document.getElementById('modal-asignacion-rol').value = asig.rol || 'docente';
        document.getElementById('modal-asignacion-estado').value = asig.estado || 'Asignado';
        document.getElementById('modal-asignacion-titulo').value = asig.titulo_participacion || '';
        document.getElementById('modal-asignacion-fecha-inicio').value = asig.fecha_inicio || '';
        document.getElementById('modal-asignacion-fecha-fin').value = asig.fecha_fin || '';
        document.getElementById('modal-asignacion-notificar').checked = false;

        document.getElementById('modal-asignacion').classList.remove('hidden');
        lucide.createIcons();
    }

    function cerrarModalAsignacion() {
        document.getElementById('modal-asignacion').classList.add('hidden');
    }

    function reenviarNotificacionDocente(idParticipacion, nombreDocente, estado) {
        let tipoEmail = estado === 'Completado' ? 'completado' : 'asignado';
        let mensaje = estado === 'Completado'
            ? `¿Reenviar email de certificado disponible a ${nombreDocente}?`
            : `¿Reenviar email de asignación al curso a ${nombreDocente}?`;

        if (!confirm(mensaje)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="reenviar_notificacion_docente">
            <input type="hidden" name="id_participacion" value="${idParticipacion}">
            <input type="hidden" name="tipo_email" value="${tipoEmail}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function confirmarEliminarAsignacion(idParticipacion, nombreDocente) {
        if (!confirm(`¿Está seguro de eliminar la asignación de ${nombreDocente}? Esta acción no se puede deshacer.`)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=certificatum';
        form.innerHTML = `
            <input type="hidden" name="accion" value="eliminar_participacion">
            <input type="hidden" name="id_participacion" value="${idParticipacion}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById('modal-asignacion')?.addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalAsignacion();
        }
    });

    // Exponer funciones de asignaciones globalmente
    window.abrirModalEditarAsignacion = abrirModalEditarAsignacion;
    window.cerrarModalAsignacion = cerrarModalAsignacion;
    window.reenviarNotificacionDocente = reenviarNotificacionDocente;
    window.confirmarEliminarAsignacion = confirmarEliminarAsignacion;

    // ============================================================================
    // FUNCIONES PARA MODALES DE ESTUDIANTES (Nueva UI - Compatible con Nexus)
    // ============================================================================

    // Limpiar todos los campos del modal
    function limpiarModalEstudiante() {
        document.getElementById('modal-estudiante-id').value = '';
        document.getElementById('modal-estudiante-dni').value = '';
        document.getElementById('modal-estudiante-nombre').value = '';
        document.getElementById('modal-estudiante-apellido').value = '';
        document.getElementById('modal-estudiante-email').value = '';
        document.getElementById('modal-estudiante-telefono').value = '';
        document.getElementById('modal-estudiante-estado').value = 'Activo';
        document.getElementById('modal-estudiante-fecha-nac').value = '';
        document.getElementById('modal-estudiante-genero').value = 'Prefiero no especificar';
        document.getElementById('modal-estudiante-ciudad').value = '';
        document.getElementById('modal-estudiante-provincia').value = '';
        document.getElementById('modal-estudiante-cp').value = '';
        document.getElementById('modal-estudiante-pais').value = 'AR';
        document.getElementById('modal-estudiante-profesion').value = '';
        document.getElementById('modal-estudiante-trabajo').value = '';
        document.getElementById('modal-estudiante-cargo').value = '';
        // Ocultar info adicional
        document.getElementById('info-adicional').classList.add('hidden');
        document.getElementById('icon-info-adicional').style.transform = '';
    }

    // Abrir modal para nuevo estudiante
    function abrirModalNuevoEstudiante() {
        limpiarModalEstudiante();
        document.getElementById('modal-estudiante-titulo').textContent = 'Nuevo Estudiante';
        document.getElementById('modal-estudiante-accion').value = 'crear_estudiante';
        document.getElementById('modal-estudiante-btn-texto').textContent = 'Guardar';
        document.getElementById('modal-estudiante-dni').removeAttribute('readonly');
        document.getElementById('modal-estudiante').classList.remove('hidden');
        lucide.createIcons();
        document.getElementById('modal-estudiante-dni').focus();
    }

    // Abrir modal para editar estudiante (recibe objeto con todos los datos)
    function abrirModalEditarEstudiante(est) {
        limpiarModalEstudiante();
        document.getElementById('modal-estudiante-titulo').textContent = 'Editar Estudiante';
        document.getElementById('modal-estudiante-accion').value = 'actualizar_estudiante';
        document.getElementById('modal-estudiante-id').value = est.id_miembro || est.id || '';
        document.getElementById('modal-estudiante-dni').value = est.dni || est.identificador_principal || '';
        document.getElementById('modal-estudiante-nombre').value = est.nombre || '';
        document.getElementById('modal-estudiante-apellido').value = est.apellido || '';
        document.getElementById('modal-estudiante-email').value = est.email || '';
        document.getElementById('modal-estudiante-telefono').value = est.telefono || '';
        document.getElementById('modal-estudiante-estado').value = est.estado || 'Activo';
        document.getElementById('modal-estudiante-genero').value = est.genero || 'Prefiero no especificar';
        document.getElementById('modal-estudiante-fecha-nac').value = est.fecha_nacimiento || '';
        document.getElementById('modal-estudiante-ciudad').value = est.domicilio_ciudad || '';
        document.getElementById('modal-estudiante-provincia').value = est.domicilio_provincia || '';
        document.getElementById('modal-estudiante-cp').value = est.domicilio_codigo_postal || '';
        document.getElementById('modal-estudiante-pais').value = est.domicilio_pais || 'AR';
        document.getElementById('modal-estudiante-profesion').value = est.profesion || '';
        document.getElementById('modal-estudiante-trabajo').value = est.lugar_trabajo || '';
        document.getElementById('modal-estudiante-cargo').value = est.cargo || '';
        document.getElementById('modal-estudiante-btn-texto').textContent = 'Actualizar';
        document.getElementById('modal-estudiante').classList.remove('hidden');
        lucide.createIcons();
        document.getElementById('modal-estudiante-nombre').focus();
    }

    // Cerrar modal de estudiante
    function cerrarModalEstudiante() {
        document.getElementById('modal-estudiante').classList.add('hidden');
    }

    // Toggle información adicional
    function toggleInfoAdicional() {
        const div = document.getElementById('info-adicional');
        const icon = document.getElementById('icon-info-adicional');
        if (div.classList.contains('hidden')) {
            div.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            div.classList.add('hidden');
            icon.style.transform = '';
        }
    }

    // Abrir modal de confirmación de eliminación (con modal bonito en lugar de confirm)
    function confirmarEliminarEstudianteModal(id, nombre) {
        document.getElementById('modal-eliminar-id').value = id;
        document.getElementById('modal-eliminar-mensaje').textContent = `¿Estás seguro de eliminar a "${nombre}"? Esta acción eliminará todas sus inscripciones y no se puede deshacer.`;
        document.getElementById('modal-confirmar-eliminar').classList.remove('hidden');
        lucide.createIcons();
    }

    // Cerrar modal de eliminación
    function cerrarModalEliminar() {
        document.getElementById('modal-confirmar-eliminar').classList.add('hidden');
    }

    // Toggle panel de importación
    function toggleImportarEstudiantes() {
        const panel = document.getElementById('panel-importar-estudiantes');
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            lucide.createIcons();
        } else {
            panel.classList.add('hidden');
        }
    }

    // Cambiar tab de importación (Estudiantes)
    function cambiarTabImport(tab) {
        // Actualizar botones
        document.querySelectorAll('.tab-import-btn').forEach(btn => {
            btn.classList.remove('active', 'border-b-2', 'border-blue-600', 'text-blue-600');
            btn.classList.add('text-gray-500');
        });
        const activeBtn = document.getElementById('tab-import-' + tab);
        activeBtn.classList.add('active', 'border-b-2', 'border-blue-600', 'text-blue-600');
        activeBtn.classList.remove('text-gray-500');

        // Mostrar contenido
        document.querySelectorAll('.tab-import-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById('content-import-' + tab).classList.remove('hidden');
    }

    // Cambiar tab de importación (Docentes)
    function cambiarTabImportDocentes(tab) {
        // Actualizar botones
        document.querySelectorAll('.tab-import-docentes-btn').forEach(btn => {
            btn.classList.remove('active', 'border-b-2', 'border-amber-600', 'text-amber-600');
            btn.classList.add('text-gray-500');
        });
        const activeBtn = document.getElementById('tab-import-docentes-' + tab);
        activeBtn.classList.add('active', 'border-b-2', 'border-amber-600', 'text-amber-600');
        activeBtn.classList.remove('text-gray-500');

        // Mostrar contenido
        document.querySelectorAll('.tab-import-docentes-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById('content-import-docentes-' + tab).classList.remove('hidden');
    }

    // Mostrar nombre del archivo seleccionado
    function mostrarNombreArchivo(input, targetId) {
        const target = document.getElementById(targetId);
        if (input.files && input.files[0]) {
            target.textContent = '📄 ' + input.files[0].name;
            target.classList.remove('hidden');
        } else {
            target.classList.add('hidden');
        }
    }

    // Ver inscripciones de un estudiante (cambia a tab inscripciones con filtro)
    function verInscripcionesEstudiante(id, nombre) {
        // Cambiar al tab de inscripciones
        document.querySelectorAll('.tab-btn-cert').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-content-cert').forEach(content => content.classList.add('hidden'));

        document.querySelector('[data-tab="cert-inscripciones"]').classList.add('active');
        document.getElementById('tab-cert-inscripciones').classList.remove('hidden');

        // Opcional: podríamos filtrar las inscripciones por estudiante
        // Por ahora solo cambiamos el tab
        alert('Mostrando inscripciones. Para filtrar por "' + nombre + '", use el buscador arriba.');
    }

    // Hacer funciones globales
    window.abrirModalNuevoEstudiante = abrirModalNuevoEstudiante;
    window.abrirModalEditarEstudiante = abrirModalEditarEstudiante;
    window.cerrarModalEstudiante = cerrarModalEstudiante;
    window.confirmarEliminarEstudianteModal = confirmarEliminarEstudianteModal;
    window.cerrarModalEliminar = cerrarModalEliminar;
    window.toggleImportarEstudiantes = toggleImportarEstudiantes;
    window.cambiarTabImport = cambiarTabImport;
    window.cambiarTabImportDocentes = cambiarTabImportDocentes;
    window.mostrarNombreArchivo = mostrarNombreArchivo;
    window.verInscripcionesEstudiante = verInscripcionesEstudiante;

    // También hacer globales las funciones antiguas (por compatibilidad)
    window.editarEstudiante = editarEstudiante;
    window.confirmarEliminarEstudiante = confirmarEliminarEstudiante;
    window.editarCurso = editarCurso;
    window.confirmarEliminarCurso = confirmarEliminarCurso;
    window.confirmarEliminarInscripcion = confirmarEliminarInscripcion;
    window.mostrarTemplatePreview = mostrarTemplatePreview;
    window.toggleTemplatePreview = toggleTemplatePreview;

    // Cerrar modales con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalEstudiante();
            cerrarModalEliminar();
        }
    });

    // Cerrar modales al hacer clic fuera
    document.getElementById('modal-estudiante')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarModalEstudiante();
    });
    document.getElementById('modal-confirmar-eliminar')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarModalEliminar();
    });

    // ============== FUNCIONES PARA DOCENTES ==============

    function toggleImportarDocentes() {
        const panel = document.getElementById('panel-importar-docentes');
        panel.classList.toggle('hidden');
        // Cerrar el otro panel si está abierto
        document.getElementById('panel-asignar-curso')?.classList.add('hidden');
    }

    function toggleAsignarCurso() {
        const panel = document.getElementById('panel-asignar-curso');
        panel.classList.toggle('hidden');
        // Cerrar el otro panel si está abierto
        document.getElementById('panel-importar-docentes')?.classList.add('hidden');
    }

    function abrirModalDocente(docente = null) {
        const modal = document.getElementById('modal-docente');
        const titulo = document.getElementById('modal-docente-titulo');
        const accion = document.getElementById('modal-docente-accion');
        const id = document.getElementById('modal-docente-id');
        const btnTexto = document.getElementById('modal-docente-btn-texto');

        // Reset form
        document.getElementById('form-modal-docente').reset();

        if (docente) {
            // Modo edición
            titulo.textContent = 'Editar Docente';
            accion.value = 'editar_docente';
            id.value = docente.id_miembro;
            btnTexto.textContent = 'Actualizar';

            document.getElementById('modal-docente-dni').value = docente.dni || docente.identificador_principal || '';
            document.getElementById('modal-docente-nombre').value = docente.nombre || '';
            document.getElementById('modal-docente-apellido').value = docente.apellido || '';
            document.getElementById('modal-docente-email').value = docente.email || '';
            document.getElementById('modal-docente-ciudad').value = docente.domicilio_ciudad || '';
            document.getElementById('modal-docente-provincia').value = docente.domicilio_provincia || '';
            document.getElementById('modal-docente-cp').value = docente.domicilio_codigo_postal || '';
            document.getElementById('modal-docente-pais').value = docente.domicilio_pais || docente.pais || 'AR';
            document.getElementById('modal-docente-profesion').value = docente.profesion || '';
            document.getElementById('modal-docente-trabajo').value = docente.lugar_trabajo || '';
            document.getElementById('modal-docente-cargo').value = docente.cargo || '';
            document.getElementById('modal-docente-especialidad').value = docente.especialidad || '';
            document.getElementById('modal-docente-titulo').value = docente.titulo || '';
            document.getElementById('modal-docente-genero').value = docente.genero || 'Prefiero no especificar';
        } else {
            // Modo creación
            titulo.textContent = 'Nuevo Docente';
            accion.value = 'crear_docente';
            id.value = '';
            btnTexto.textContent = 'Guardar';
            // Defaults
            document.getElementById('modal-docente-pais').value = 'AR';
            document.getElementById('modal-docente-genero').value = 'Prefiero no especificar';
        }

        modal.classList.remove('hidden');
        lucide.createIcons();
    }

    function cerrarModalDocente() {
        document.getElementById('modal-docente').classList.add('hidden');
    }

    function editarDocente(docente) {
        abrirModalDocente(docente);
    }

    function confirmarEliminarDocente(id, nombre) {
        const modal = document.getElementById('modal-confirmar-eliminar-docente');
        const mensaje = document.getElementById('modal-eliminar-docente-mensaje');
        const inputId = document.getElementById('modal-eliminar-docente-id');

        mensaje.textContent = '¿Estás seguro de eliminar a "' + nombre + '"? Esta acción no se puede deshacer.';
        inputId.value = id;
        modal.classList.remove('hidden');
        lucide.createIcons();
    }

    function cerrarModalEliminarDocente() {
        document.getElementById('modal-confirmar-eliminar-docente').classList.add('hidden');
    }

    // Hacer funciones de docentes globales
    window.toggleImportarDocentes = toggleImportarDocentes;
    window.abrirModalDocente = abrirModalDocente;
    window.cerrarModalDocente = cerrarModalDocente;
    window.editarDocente = editarDocente;
    window.confirmarEliminarDocente = confirmarEliminarDocente;
    window.cerrarModalEliminarDocente = cerrarModalEliminarDocente;

    // Event listeners para modales de docentes
    document.getElementById('modal-docente')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarModalDocente();
    });
    document.getElementById('modal-confirmar-eliminar-docente')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarModalEliminarDocente();
    });

    // Extender Escape para cerrar modales de docentes
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalDocente();
            cerrarModalEliminarDocente();
            cerrarWizardImport();
        }
    });

    // ============================================================================
    // WIZARD DE IMPORTACIÓN - JavaScript
    // ============================================================================

    // Estado del wizard
    const wizardState = {
        tipo: 'estudiantes', // estudiantes, docentes, inscripciones
        pasoActual: 1,
        datos: [],
        columnas: [],
        mapeo: {},
        datosValidados: [],
        errores: [],
        warnings: []
    };

    // Configuración de campos por tipo
    const wizardCampos = {
        estudiantes: {
            titulo: 'Importar Estudiantes',
            requeridos: ['dni', 'nombre', 'apellido'],
            opcionales: ['email', 'telefono', 'ciudad', 'provincia', 'codigo_postal', 'pais', 'lugar_trabajo', 'cargo', 'profesion'],
            formatoInfo: '<strong>Requerido:</strong> DNI, Nombre, Apellido<br><strong>Opcional:</strong> Email, Teléfono, Ciudad, Provincia, CódPostal, País, LugarTrabajo, Cargo, Profesión'
        },
        docentes: {
            titulo: 'Importar Docentes',
            requeridos: ['dni', 'nombre', 'apellido'],
            opcionales: ['email', 'telefono', 'ciudad', 'provincia', 'codigo_postal', 'pais', 'especialidad', 'titulo', 'lugar_trabajo', 'cargo', 'profesion'],
            formatoInfo: '<strong>Requerido:</strong> DNI, Nombre, Apellido<br><strong>Opcional:</strong> Email, Teléfono, Especialidad, Título, Ciudad, Provincia'
        },
        inscripciones: {
            titulo: 'Importar Inscripciones',
            requeridos: ['dni', 'codigo_curso'],
            opcionales: ['estado', 'fecha_inicio', 'fecha_fin', 'nota', 'asistencia'],
            formatoInfo: '<strong>Requerido:</strong> DNI, Código Curso<br><strong>Opcional:</strong> Estado, Fecha Inicio, Fecha Fin, Nota, Asistencia'
        }
    };

    // Sinónimos para auto-detección de columnas
    const sinonimosColumnas = {
        'dni': ['dni', 'documento', 'doc', 'id', 'identificador', 'cedula', 'rut', 'cpf'],
        'nombre': ['nombre', 'name', 'nombres', 'first_name', 'firstname', 'primer_nombre'],
        'apellido': ['apellido', 'apellidos', 'surname', 'last_name', 'lastname'],
        'email': ['email', 'correo', 'mail', 'e-mail', 'correo_electronico'],
        'telefono': ['telefono', 'tel', 'phone', 'celular', 'movil', 'whatsapp'],
        'ciudad': ['ciudad', 'city', 'localidad'],
        'provincia': ['provincia', 'state', 'estado', 'departamento', 'region'],
        'codigo_postal': ['codigo_postal', 'cp', 'zip', 'postal', 'cod_postal'],
        'pais': ['pais', 'country', 'nacionalidad'],
        'lugar_trabajo': ['lugar_trabajo', 'trabajo', 'empresa', 'institucion', 'organizacion'],
        'cargo': ['cargo', 'puesto', 'position', 'rol'],
        'profesion': ['profesion', 'ocupacion', 'profession'],
        'especialidad': ['especialidad', 'specialty', 'area'],
        'titulo': ['titulo', 'title', 'grado', 'degree'],
        'codigo_curso': ['codigo_curso', 'curso', 'course', 'cod_curso', 'codigo'],
        'estado': ['estado', 'status', 'situacion'],
        'fecha_inicio': ['fecha_inicio', 'inicio', 'start', 'fecha_inscripcion'],
        'fecha_fin': ['fecha_fin', 'fin', 'end', 'fecha_finalizacion'],
        'nota': ['nota', 'calificacion', 'grade', 'score', 'nota_final'],
        'asistencia': ['asistencia', 'attendance', 'porcentaje']
    };

    // Abrir el wizard
    function abrirWizardImport(tipo = 'estudiantes') {
        wizardState.tipo = tipo;
        wizardState.pasoActual = 1;
        wizardState.datos = [];
        wizardState.columnas = [];
        wizardState.mapeo = {};
        wizardState.datosValidados = [];
        wizardState.errores = [];
        wizardState.warnings = [];

        const config = wizardCampos[tipo];
        document.getElementById('wizard-titulo').textContent = config.titulo;
        document.getElementById('wizard-formato-info').innerHTML = config.formatoInfo;

        // Reset UI
        document.getElementById('wizard-file-input').value = '';
        document.getElementById('wizard-nombre-archivo').classList.add('hidden');
        document.getElementById('wizard-texto-area').classList.add('hidden');
        document.getElementById('wizard-texto-input').value = '';
        document.getElementById('wizard-dropzone').classList.remove('border-emerald-500', 'bg-emerald-50');

        wizardMostrarPaso(1);
        document.getElementById('modal-wizard-import').classList.remove('hidden');
        lucide.createIcons();
    }

    // Cerrar el wizard
    function cerrarWizardImport() {
        document.getElementById('modal-wizard-import').classList.add('hidden');
    }

    // Mostrar paso específico
    function wizardMostrarPaso(paso) {
        wizardState.pasoActual = paso;

        // Ocultar todos los pasos
        document.querySelectorAll('.wizard-paso').forEach(p => p.classList.add('hidden'));
        // Mostrar paso actual
        document.getElementById('wizard-paso-' + paso).classList.remove('hidden');

        // Actualizar indicadores
        document.querySelectorAll('.wizard-step-indicator').forEach(ind => {
            const stepNum = parseInt(ind.dataset.step);
            ind.classList.remove('active', 'completed');
            if (stepNum < paso) ind.classList.add('completed');
            if (stepNum === paso) ind.classList.add('active');
        });

        // Actualizar botones
        document.getElementById('wizard-btn-anterior').disabled = paso === 1;
        document.getElementById('wizard-btn-siguiente').classList.toggle('hidden', paso === 4);
        document.getElementById('wizard-btn-importar').classList.toggle('hidden', paso !== 4);

        lucide.createIcons();
    }

    // Navegación entre pasos
    function wizardPasoAnterior() {
        if (wizardState.pasoActual > 1) {
            wizardMostrarPaso(wizardState.pasoActual - 1);
        }
    }

    function wizardPasoSiguiente() {
        const pasoActual = wizardState.pasoActual;

        // Validar paso actual antes de avanzar
        if (pasoActual === 1) {
            if (!wizardValidarPaso1()) return;
            wizardProcesarDatos();
            wizardGenerarMapeo();
        } else if (pasoActual === 2) {
            if (!wizardValidarPaso2()) return;
            wizardValidarDatos();
        } else if (pasoActual === 3) {
            wizardPrepararResumen();
        }

        if (pasoActual < 4) {
            wizardMostrarPaso(pasoActual + 1);
        }
    }

    // Mostrar área de texto
    function wizardMostrarTexto() {
        document.getElementById('wizard-texto-area').classList.remove('hidden');
        document.getElementById('wizard-texto-input').focus();
    }

    // Archivo seleccionado
    function wizardArchivoSeleccionado(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('wizard-nombre-archivo').textContent = '📄 ' + file.name;
            document.getElementById('wizard-nombre-archivo').classList.remove('hidden');
            document.getElementById('wizard-dropzone').classList.add('border-emerald-500', 'bg-emerald-50');
            document.getElementById('wizard-texto-area').classList.add('hidden');
        }
    }

    // Validar paso 1
    function wizardValidarPaso1() {
        const fileInput = document.getElementById('wizard-file-input');
        const textoInput = document.getElementById('wizard-texto-input').value.trim();

        if (!fileInput.files.length && !textoInput) {
            showToast('Seleccioná un archivo o pegá los datos', 'error');
            return false;
        }
        return true;
    }

    // Procesar datos (archivo o texto)
    function wizardProcesarDatos() {
        const fileInput = document.getElementById('wizard-file-input');
        const textoInput = document.getElementById('wizard-texto-input').value.trim();

        if (textoInput) {
            // Procesar texto pegado
            wizardParsearTexto(textoInput);
        } else if (fileInput.files.length) {
            // Procesar archivo
            wizardParsearArchivo(fileInput.files[0]);
        }
    }

    // Parsear texto CSV
    function wizardParsearTexto(texto) {
        const lineas = texto.split(/\r?\n/).filter(l => l.trim());
        if (lineas.length === 0) return;

        // Detectar separador (coma, punto y coma, o tab)
        const primeraLinea = lineas[0];
        let separador = ',';
        if (primeraLinea.includes('\t')) separador = '\t';
        else if (primeraLinea.includes(';')) separador = ';';

        // Parsear todas las líneas
        wizardState.datos = lineas.map(linea => {
            return linea.split(separador).map(cel => cel.trim().replace(/^["']|["']$/g, ''));
        });

        // Primera fila como nombres de columnas
        wizardState.columnas = wizardState.datos[0].map((col, i) => col || `Columna ${i+1}`);
    }

    // Parsear archivo (simplificado - solo CSV por ahora)
    function wizardParsearArchivo(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            wizardParsearTexto(e.target.result);
            wizardGenerarMapeo();
            wizardMostrarPaso(2);
        };
        reader.readAsText(file);
    }

    // Generar mapeo de columnas
    function wizardGenerarMapeo() {
        const config = wizardCampos[wizardState.tipo];
        const todosCampos = [...config.requeridos, ...config.opcionales];

        // Auto-detectar mapeo
        wizardState.mapeo = {};
        wizardState.columnas.forEach((col, idx) => {
            const colNorm = col.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/g, '');
            for (const [campo, sinonimos] of Object.entries(sinonimosColumnas)) {
                if (todosCampos.includes(campo)) {
                    for (const sin of sinonimos) {
                        if (colNorm.includes(sin) || sin.includes(colNorm)) {
                            if (!Object.values(wizardState.mapeo).includes(campo)) {
                                wizardState.mapeo[idx] = campo;
                                break;
                            }
                        }
                    }
                }
            }
        });

        // Generar preview
        wizardGenerarPreview();
        // Generar UI de mapeo
        wizardGenerarMapeoUI(todosCampos);
    }

    // Generar preview de datos
    function wizardGenerarPreview() {
        const thead = document.getElementById('wizard-preview-thead');
        const tbody = document.getElementById('wizard-preview-tbody');

        thead.innerHTML = '<tr>' + wizardState.columnas.map((col, i) =>
            `<th class="px-2 py-1 text-left text-xs font-medium text-gray-600 border-b">${col}</th>`
        ).join('') + '</tr>';

        const datosPreview = wizardState.datos.slice(1, 4); // Mostrar 3 filas
        tbody.innerHTML = datosPreview.map(fila =>
            '<tr>' + fila.map(cel => `<td class="px-2 py-1 text-xs border-b">${cel || '-'}</td>`).join('') + '</tr>'
        ).join('');
    }

    // Generar UI de mapeo
    function wizardGenerarMapeoUI(todosCampos) {
        const container = document.getElementById('wizard-mapeo-container');
        const config = wizardCampos[wizardState.tipo];

        container.innerHTML = wizardState.columnas.map((col, idx) => {
            const mapeoActual = wizardState.mapeo[idx] || '';
            const opciones = todosCampos.map(campo => {
                const esRequerido = config.requeridos.includes(campo);
                const label = campo.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                return `<option value="${campo}" ${mapeoActual === campo ? 'selected' : ''}>${label}${esRequerido ? ' *' : ''}</option>`;
            }).join('');

            return `
                <div class="mapeo-row">
                    <span class="columna-archivo" title="${col}">${col.substring(0, 20)}${col.length > 20 ? '...' : ''}</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-gray-400"></i>
                    <select class="mapeo-select border border-gray-300 rounded px-2 py-1 text-sm" data-col-idx="${idx}" onchange="wizardActualizarMapeo(${idx}, this.value)">
                        <option value="">-- Ignorar --</option>
                        ${opciones}
                    </select>
                </div>
            `;
        }).join('');

        lucide.createIcons();
    }

    // Actualizar mapeo
    function wizardActualizarMapeo(idx, campo) {
        // Limpiar mapeo anterior de este campo
        Object.keys(wizardState.mapeo).forEach(k => {
            if (wizardState.mapeo[k] === campo && k != idx) {
                delete wizardState.mapeo[k];
                document.querySelector(`.mapeo-select[data-col-idx="${k}"]`).value = '';
            }
        });

        if (campo) {
            wizardState.mapeo[idx] = campo;
        } else {
            delete wizardState.mapeo[idx];
        }
    }

    // Validar paso 2
    function wizardValidarPaso2() {
        const config = wizardCampos[wizardState.tipo];
        const camposMapeados = Object.values(wizardState.mapeo);

        for (const req of config.requeridos) {
            if (!camposMapeados.includes(req)) {
                const label = req.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                showToast(`Falta mapear campo requerido: ${label}`, 'error');
                return false;
            }
        }
        return true;
    }

    // Validar datos
    function wizardValidarDatos() {
        const config = wizardCampos[wizardState.tipo];
        const primeraFilaEsHeader = document.getElementById('wizard-primera-fila-header').checked;
        const datosAProcesar = primeraFilaEsHeader ? wizardState.datos.slice(1) : wizardState.datos;

        wizardState.datosValidados = [];
        wizardState.errores = [];
        wizardState.warnings = [];

        const dnisVistos = new Set();

        datosAProcesar.forEach((fila, idx) => {
            const registro = {};
            let tieneError = false;
            let tieneWarning = false;
            const erroresFila = [];

            // Mapear datos
            Object.entries(wizardState.mapeo).forEach(([colIdx, campo]) => {
                registro[campo] = (fila[colIdx] || '').trim();
            });

            // Validar requeridos
            config.requeridos.forEach(campo => {
                if (!registro[campo]) {
                    tieneError = true;
                    erroresFila.push(`Falta ${campo}`);
                }
            });

            // Validar DNI duplicado
            if (registro.dni) {
                if (dnisVistos.has(registro.dni)) {
                    tieneWarning = true;
                    wizardState.warnings.push(`Fila ${idx + 1}: DNI duplicado (${registro.dni})`);
                }
                dnisVistos.add(registro.dni);
            }

            // Validar email
            if (registro.email && !registro.email.includes('@')) {
                tieneWarning = true;
                wizardState.warnings.push(`Fila ${idx + 1}: Email inválido`);
            }

            if (tieneError) {
                wizardState.errores.push(`Fila ${idx + 1}: ${erroresFila.join(', ')}`);
            }

            registro._fila = idx + 1;
            registro._error = tieneError;
            registro._warning = tieneWarning;
            wizardState.datosValidados.push(registro);
        });

        wizardMostrarResultadosValidacion();
    }

    // Mostrar resultados de validación
    function wizardMostrarResultadosValidacion() {
        const validos = wizardState.datosValidados.filter(r => !r._error).length;
        const warnings = wizardState.datosValidados.filter(r => r._warning && !r._error).length;
        const errores = wizardState.datosValidados.filter(r => r._error).length;

        document.getElementById('wizard-validos').textContent = validos;
        document.getElementById('wizard-warnings').textContent = warnings;
        document.getElementById('wizard-errores').textContent = errores;

        // Mostrar lista de problemas
        const problemasContainer = document.getElementById('wizard-problemas-container');
        const problemasLista = document.getElementById('wizard-problemas-lista');

        if (wizardState.errores.length || wizardState.warnings.length) {
            problemasContainer.classList.remove('hidden');
            problemasLista.innerHTML = [
                ...wizardState.errores.map(e => `<div class="text-red-600 text-sm mb-1">❌ ${e}</div>`),
                ...wizardState.warnings.map(w => `<div class="text-amber-600 text-sm mb-1">⚠️ ${w}</div>`)
            ].join('');
        } else {
            problemasContainer.classList.add('hidden');
        }

        // Preview de datos validados
        wizardGenerarPreviewValidacion();
    }

    // Preview de validación
    function wizardGenerarPreviewValidacion() {
        const config = wizardCampos[wizardState.tipo];
        const campos = [...config.requeridos, ...config.opcionales.slice(0, 3)]; // Mostrar solo algunos

        const thead = document.getElementById('wizard-validacion-thead');
        const tbody = document.getElementById('wizard-validacion-tbody');

        thead.innerHTML = '<tr><th class="px-2 py-2 text-left text-xs font-medium">#</th>' +
            campos.map(c => `<th class="px-2 py-2 text-left text-xs font-medium">${c.replace(/_/g, ' ')}</th>`).join('') +
            '<th class="px-2 py-2 text-center text-xs font-medium">Estado</th></tr>';

        tbody.innerHTML = wizardState.datosValidados.slice(0, 10).map(reg => {
            const rowClass = reg._error ? 'bg-red-50' : (reg._warning ? 'bg-amber-50' : '');
            const estado = reg._error ? '❌' : (reg._warning ? '⚠️' : '✅');
            return `<tr class="${rowClass}">
                <td class="px-2 py-1 text-xs text-gray-500">${reg._fila}</td>
                ${campos.map(c => `<td class="px-2 py-1 text-xs">${reg[c] || '-'}</td>`).join('')}
                <td class="px-2 py-1 text-center">${estado}</td>
            </tr>`;
        }).join('');
    }

    // Preparar resumen final
    function wizardPrepararResumen() {
        const validos = wizardState.datosValidados.filter(r => !r._error);
        document.getElementById('wizard-resumen-total').textContent = validos.length;

        const config = wizardCampos[wizardState.tipo];
        const camposMapeados = Object.values(wizardState.mapeo);

        // Mostrar/ocultar selector de curso para inscripciones
        const opcionesInscripciones = document.getElementById('wizard-opciones-inscripciones');
        if (wizardState.tipo === 'inscripciones') {
            opcionesInscripciones.classList.remove('hidden');
        } else {
            opcionesInscripciones.classList.add('hidden');
        }

        document.getElementById('wizard-resumen-lista').innerHTML = `
            <li class="flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i> Tipo: <strong>${config.titulo}</strong></li>
            <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Registros válidos: <strong>${validos.length}</strong></li>
            <li class="flex items-center gap-2"><i data-lucide="columns" class="w-4 h-4 text-gray-400"></i> Campos mapeados: <strong>${camposMapeados.length}</strong></li>
            ${wizardState.errores.length ? `<li class="flex items-center gap-2 text-red-600"><i data-lucide="x-circle" class="w-4 h-4"></i> Registros con error (se omitirán): <strong>${wizardState.errores.length}</strong></li>` : ''}
        `;
        lucide.createIcons();
    }

    // Ejecutar importación
    function wizardEjecutarImport() {
        const validos = wizardState.datosValidados.filter(r => !r._error);
        if (validos.length === 0) {
            showToast('No hay registros válidos para importar', 'error');
            return;
        }

        const actualizarExistentes = document.getElementById('wizard-actualizar-existentes').checked;
        const omitirVacios = document.getElementById('wizard-omitir-vacios').checked;

        // Mostrar progreso
        document.getElementById('wizard-progreso-container').classList.remove('hidden');
        document.getElementById('wizard-btn-importar').disabled = true;
        document.getElementById('wizard-btn-anterior').disabled = true;

        // Preparar datos para enviar
        const datosLimpios = validos.map(r => {
            const clean = {...r};
            delete clean._fila;
            delete clean._error;
            delete clean._warning;
            return clean;
        });

        // Preparar opciones
        const opciones = {
            actualizar_existentes: actualizarExistentes,
            omitir_vacios: omitirVacios
        };

        // Para inscripciones, agregar el curso seleccionado si hay uno
        if (wizardState.tipo === 'inscripciones') {
            const cursoSelect = document.getElementById('wizard-curso-inscripcion');
            if (cursoSelect && cursoSelect.value) {
                opciones.id_curso = parseInt(cursoSelect.value);
            }
        }

        // Enviar via AJAX (JSON)
        fetch('?modulo=certificatum&accion=wizard_importar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tipo: wizardState.tipo,
                datos: datosLimpios,
                opciones: opciones
            })
        })
        .then(response => response.json())
        .then(result => {
            document.getElementById('wizard-progreso-bar').style.width = '100%';
            document.getElementById('wizard-progreso-texto').textContent = '100%';

            setTimeout(() => {
                cerrarWizardImport();
                if (result.success) {
                    showToast(`Importación completada: ${result.insertados} nuevos, ${result.actualizados} actualizados`, 'success');
                    // Recargar página para ver cambios
                    setTimeout(() => location.reload(), 1500);
                } else {
                    const errMsg = result.error || result.mensaje || 'Error desconocido';
                    showToast('Error: ' + errMsg, 'error');
                    if (result.errores && result.errores.length > 0) {
                        console.log('Errores de importación:', result.errores);
                    }
                }
            }, 500);
        })
        .catch(error => {
            showToast('Error de conexión: ' + error.message, 'error');
            document.getElementById('wizard-btn-importar').disabled = false;
            document.getElementById('wizard-btn-anterior').disabled = false;
        });
    }

    // Exponer funciones globalmente
    window.abrirWizardImport = abrirWizardImport;
    window.cerrarWizardImport = cerrarWizardImport;
    window.wizardMostrarTexto = wizardMostrarTexto;
    window.wizardArchivoSeleccionado = wizardArchivoSeleccionado;
    window.wizardPasoAnterior = wizardPasoAnterior;
    window.wizardPasoSiguiente = wizardPasoSiguiente;
    window.wizardActualizarMapeo = wizardActualizarMapeo;
    window.wizardEjecutarImport = wizardEjecutarImport;

    // ============================================================================
    // GESTIÓN DE TEMPLATES - REMOVIDO (Acceso solo via script interno)
    // Las funciones de gestión de JSON de templates se movieron a:
    // tools/template-manager/index.php (uso interno)
    // ============================================================================
</script>
