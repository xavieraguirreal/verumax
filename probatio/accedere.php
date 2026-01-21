<?php
 error_reporting(E_ALL);
  ini_set('display_errors', 1);

/**
 * PROBATIO - Acceso a Evaluación (accedere.php)
 *
 * Pantalla de ingreso con DNI para acceder a una evaluación.
 * Valida que el estudiante esté inscripto al curso vinculado.
 *
 * Parámetros esperados:
 * - institutio: Código de institución (ej: 'sajur')
 * - evaluatio: Código de evaluación (ej: 'EVAL-SAJUR-CORR-2025')
 * - lang: Idioma (opcional, default es_AR)
 */

require_once __DIR__ . '/config.php';

use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;

// =====================================================
// OBTENER PARÁMETROS
// =====================================================
$institucion = getParam('institutio');
$codigo_evaluacion = getParam('evaluatio');
$lang = getParam('lang', 'es_AR');

// Validar parámetros requeridos
if (!$institucion || !$codigo_evaluacion) {
    die('Error: Parámetros incompletos. Se requiere institutio y evaluatio.');
}

// Obtener ID de instancia
$id_instancia = obtenerIdInstancia($institucion);
if (!$id_instancia) {
    die('Error: Institución no válida.');
}

// =====================================================
// CARGAR EVALUACIÓN
// =====================================================
$evaluacion = obtenerEvaluacion($codigo_evaluacion, $id_instancia);

if (!$evaluacion) {
    die('Error: Evaluación no encontrada.');
}

if ($evaluacion['estado'] !== 'activa') {
    $mensaje = match($evaluacion['estado']) {
        'borrador' => 'Esta evaluación aún no está disponible.',
        'cerrada' => 'Esta evaluación ya ha cerrado.',
        'archivada' => 'Esta evaluación ya no está disponible.',
        default => 'Esta evaluación no está activa.'
    };
    die($mensaje);
}

// Verificar fechas si están definidas
$ahora = new DateTime();
if ($evaluacion['fecha_inicio'] && new DateTime($evaluacion['fecha_inicio']) > $ahora) {
    die('Esta evaluación aún no ha comenzado. Inicia el ' . date('d/m/Y H:i', strtotime($evaluacion['fecha_inicio'])));
}
if ($evaluacion['fecha_fin'] && new DateTime($evaluacion['fecha_fin']) < $ahora) {
    die('Esta evaluación ha finalizado.');
}

// Si la evaluación está vinculada a un curso, usar el nombre del curso
if ($evaluacion['id_curso']) {
    $pdo = getAcademiConnection();
    $stmt = $pdo->prepare("SELECT nombre_curso FROM cursos WHERE id_curso = :id_curso");
    $stmt->execute(['id_curso' => $evaluacion['id_curso']]);
    $curso = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($curso && $curso['nombre_curso']) {
        $evaluacion['nombre_display'] = 'Evaluación Final - ' . $curso['nombre_curso'];
    } else {
        $evaluacion['nombre_display'] = $evaluacion['nombre'];
    }
} else {
    $evaluacion['nombre_display'] = $evaluacion['nombre'];
}

// =====================================================
// CARGAR CONFIGURACIÓN INSTITUCIONAL
// =====================================================
$instance_config = InstitutionService::getConfig($institucion);
LanguageService::init($institucion, $lang);
$t = fn($key, $params = [], $default = '') => LanguageService::get($key, $params, $default);

// Colores institucionales
$color_primario = $instance_config['color_primario'] ?? '#0F52BA';
$color_secundario = $instance_config['color_secundario'] ?? '#0a3d8f';
$logo_url = $instance_config['logo_url'] ?? '';
$nombre_institucion = $instance_config['nombre'] ?? $institucion;
$logo_estilo = $instance_config['logo_estilo'] ?? 'rectangular';
$logo_mostrar_texto = $instance_config['logo_mostrar_texto'] ?? 1;

// =====================================================
// PROCESAR FORMULARIO (POST)
// =====================================================
$error = null;
$dni_ingresado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni_ingresado = postParam('dni', '');

    if (empty($dni_ingresado)) {
        $error = 'Por favor, ingresá tu número de documento.';
    } else {
        // Buscar estudiante
        $estudiante = buscarEstudiantePorDNI($dni_ingresado, $id_instancia);

        if (!$estudiante) {
            $error = $evaluacion['mensaje_error_no_inscripto']
                ?? 'No se encontró un estudiante con ese documento. Verificá que esté correctamente escrito.';
        } else {
            // Verificar inscripción al curso (si la evaluación tiene curso vinculado)
            $inscripcion = null;
            if ($evaluacion['id_curso']) {
                $inscripcion = verificarInscripcion($estudiante['id_miembro'], $evaluacion['id_curso']);
                if (!$inscripcion) {
                    $error = 'No estás inscripto en el curso asociado a esta evaluación.';
                }
            }

            if (!$error) {
                // Crear o recuperar sesión
                $sesion = obtenerOCrearSesion(
                    $evaluacion['id_evaluatio'],
                    $estudiante['id_miembro'],
                    $inscripcion['id_inscripcion'] ?? null
                );

                // Redirigir a la evaluación
                $redirect_url = 'respondere.php?' . http_build_query([
                    'sessio' => $sesion['id_sessio'],
                    'institutio' => $institucion,
                    'lang' => $lang
                ]);
                header('Location: ' . $redirect_url);
                exit;
            }
        }
    }
}

// Contar preguntas
$preguntas = obtenerPreguntas($evaluacion['id_evaluatio']);
$total_preguntas = count($preguntas);

// Configurar variables para el header de SAJUR
$page_title = htmlspecialchars($evaluacion['nombre_display']) . ' - ' . htmlspecialchars($nombre_institucion);
$is_validation_view = true; // No mostrar el menú de navegación en evaluaciones

// Meta tags para compartir
$og_title = $evaluacion['nombre_display'];
$og_description = $evaluacion['descripcion'] ?? 'Evaluacion educativa de ' . $nombre_institucion;
$og_image = $logo_url ?: 'https://verumax.com/assets/images/verumax-og.png';
$og_url = "https://{$institucion}.verumax.com/probatio/{$evaluacion['codigo']}";
$og_site_name = $nombre_institucion;

// Incluir header de SAJUR
require_once __DIR__ . '/../sajur/header.php';
?>
    <style>
        .color-primario { color: <?= $color_primario ?>; }
        .bg-primario { background-color: <?= $color_primario ?>; }
        .border-primario { border-color: <?= $color_primario ?>; }
        .bg-primario-hover:hover { background-color: <?= $color_secundario ?>; }
        .ring-primario:focus { --tw-ring-color: <?= $color_primario ?>; }
    </style>

    <!-- Main Content -->
    <main class="flex flex-col">
    <div class="flex-1 flex items-center justify-center p-4 bg-gray-50">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Encabezado de la evaluación -->
                <div class="bg-primario px-6 py-8 text-white text-center">
                    <h2 class="text-xl font-bold mb-2"><?= htmlspecialchars($evaluacion['nombre_display']) ?></h2>
                    <?php if ($evaluacion['descripcion']): ?>
                        <p class="text-white/80 text-sm"><?= htmlspecialchars($evaluacion['descripcion']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Mensaje de bienvenida -->
                <?php if ($evaluacion['mensaje_bienvenida']): ?>
                    <div class="px-6 py-4 bg-gray-50 border-b text-sm text-gray-600">
                        <?= $evaluacion['mensaje_bienvenida'] ?>
                    </div>
                <?php endif; ?>

                <!-- Info de la evaluación -->
                <div class="px-6 py-4 border-b">
                    <div class="flex items-center justify-center gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span><?= $total_preguntas ?> preguntas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><?php
                                if ($evaluacion['fecha_fin']) {
                                    $fecha_limite = new DateTime($evaluacion['fecha_fin']);
                                    echo 'Hasta el ' . $fecha_limite->format('d/m/Y');
                                } else {
                                    echo 'Sin límite de tiempo';
                                }
                            ?></span>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <form method="POST" class="px-6 py-6">
                    <?php if ($error): ?>
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-6">
                        <label for="dni" class="block text-sm font-medium text-gray-700 mb-2">
                            Número de Documento (DNI)
                        </label>
                        <input
                            type="text"
                            id="dni"
                            name="dni"
                            value="<?= htmlspecialchars($dni_ingresado) ?>"
                            placeholder="Ej: 12345678"
                            required
                            autofocus
                            inputmode="numeric"
                            pattern="[0-9]*"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-lg text-center
                                   focus:outline-none focus:ring-2 ring-primario focus:border-transparent
                                   transition-all"
                        >
                        <p class="mt-2 text-xs text-gray-500 text-center">
                            Ingresá solo números, sin puntos ni guiones
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-primario text-white py-3 px-4 rounded-lg font-semibold
                               bg-primario-hover transition-colors focus:outline-none focus:ring-2
                               focus:ring-offset-2 ring-primario"
                    >
                        Ingresar a la evaluación
                    </button>
                </form>
            </div>

            <!-- Nota al pie -->
            <p class="text-center text-xs text-gray-400 mt-6">
                Tu progreso se guardará automáticamente. Podés continuar en cualquier momento.
            </p>
        </div>
    </div>
    </main>
<?php
// Incluir footer de SAJUR
require_once __DIR__ . '/../sajur/footer.php';
?>
