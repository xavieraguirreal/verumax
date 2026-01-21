<?php
/**
 * PROBATIO - Cierre Cualitativo (clausura.php)
 *
 * Pantalla de reflexión final antes de terminar la evaluación.
 * Solo se muestra si requiere_cierre_cualitativo = true.
 */

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/config.php';

use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\EmailService;

// =====================================================
// VALIDAR SESIÓN
// =====================================================
$id_sessio = (int) getParam('sessio', 0);
$institucion = getParam('institutio');
$lang = getParam('lang', 'es_AR');

if (!$id_sessio || !$institucion) {
    die('Error: Parámetros incompletos.');
}

$sesion = obtenerSesion($id_sessio);
if (!$sesion) {
    die('Error: Sesión no encontrada.');
}

$evaluacion = obtenerEvaluacionPorId($sesion['id_evaluatio']);
if (!$evaluacion) {
    die('Error: Evaluación no encontrada.');
}

// Si la evaluación está vinculada a un curso, usar el nombre del curso
if ($evaluacion['id_curso']) {
    $pdo_curso = getAcademiConnection();
    $stmt = $pdo_curso->prepare("SELECT nombre_curso FROM cursos WHERE id_curso = :id_curso");
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

// Si no requiere cierre cualitativo, redirigir a resultados
if (!$evaluacion['requiere_cierre_cualitativo']) {
    header('Location: resultatum.php?' . http_build_query([
        'sessio' => $id_sessio,
        'institutio' => $institucion,
        'lang' => $lang
    ]));
    exit;
}

// Configuración institucional
$instance_config = InstitutionService::getConfig($institucion);
$color_primario = $instance_config['color_primario'] ?? '#0F52BA';
$color_secundario = $instance_config['color_secundario'] ?? '#0a3d8f';
$logo_url = $instance_config['logo_url'] ?? '';
$nombre_institucion = $instance_config['nombre'] ?? $institucion;
$logo_estilo = $instance_config['logo_estilo'] ?? 'rectangular';
$logo_mostrar_texto = $instance_config['logo_mostrar_texto'] ?? 1;

// Determinar clases CSS del logo según el estilo configurado
$logo_class = 'h-10 w-auto object-contain';
if ($logo_estilo === 'circular') {
    $logo_class = 'h-10 w-10 rounded-full object-cover';
} elseif ($logo_estilo === 'cuadrado') {
    $logo_class = 'h-10 w-10 object-cover';
} elseif ($logo_estilo === 'rectangular-rounded') {
    $logo_class = 'h-10 w-auto object-contain rounded-lg';
} elseif ($logo_estilo === 'cuadrado-rounded') {
    $logo_class = 'h-10 w-10 object-cover rounded-lg';
}

$texto_cierre = $evaluacion['texto_cierre_cualitativo']
    ?? 'Compartí una reflexión, consulta o comentario sobre el curso:';
// Sin mínimo de caracteres para la reflexión final
$minimo_caracteres = 0;

// Datos del estudiante
$pdo_nexus = getNexusConnection();
$stmt = $pdo_nexus->prepare("SELECT nombre, apellido, email, identificador_principal FROM miembros WHERE id_miembro = :id");
$stmt->execute(['id' => $sesion['id_miembro']]);
$estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre_estudiante = trim(($estudiante['nombre'] ?? '') . ' ' . ($estudiante['apellido'] ?? ''));

// =====================================================
// PROCESAR ENVÍO
// =====================================================
$error = null;
$reflexion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reflexion = trim($_POST['reflexion'] ?? '');

    if ($minimo_caracteres > 0 && mb_strlen($reflexion) < $minimo_caracteres) {
        $error = "Tu reflexión debe tener al menos {$minimo_caracteres} caracteres. Actualmente tiene " . mb_strlen($reflexion) . ".";
    } else {
        // Guardar reflexión y marcar como completada
        $ahora = date('Y-m-d H:i:s');

        // Calcular puntaje
        $preguntas = obtenerPreguntas($evaluacion['id_evaluatio']);
        $total_preguntas = count($preguntas);

        $pdo = getAcademiConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT r.id_quaestio) as correctas
            FROM responsa r
            INNER JOIN (
                SELECT id_quaestio, MAX(id_responsum) as ultimo
                FROM responsa
                WHERE id_sessio = :id_sessio
                GROUP BY id_quaestio
            ) ultimos ON r.id_responsum = ultimos.ultimo
            WHERE r.es_correcta = 1
        ");
        $stmt->execute(['id_sessio' => $id_sessio]);
        $correctas = (int) $stmt->fetchColumn();

        $porcentaje = $total_preguntas > 0 ? round(($correctas / $total_preguntas) * 100, 2) : 0;

        // Este examen siempre aprueba al completarse
        $aprobado = true;

        actualizarProgresoSesion($id_sessio, [
            'reflexion_final' => $reflexion,
            'estado' => 'completada',
            'fecha_finalizacion' => $ahora,
            'puntaje_obtenido' => $correctas,
            'puntaje_maximo' => $total_preguntas,
            'porcentaje' => $porcentaje,
            'aprobado' => 1
        ]);

        // =====================================================
        // ACTUALIZAR ESTADO DE INSCRIPCIÓN DEL ESTUDIANTE
        // Siempre aprobado con nota 10 y asistencia 100%
        // =====================================================
        if ($evaluacion['id_curso']) {
            // Obtener configuración de la institución (para demora de certificado)
            $instance_config = InstitutionService::getConfig($institucion);

            // Obtener configuración de demora del curso
            $stmtCurso = $pdo->prepare("
                SELECT usar_demora_global, demora_certificado_horas, demora_tipo, demora_fecha
                FROM cursos
                WHERE id_curso = :id_curso
            ");
            $stmtCurso->execute(['id_curso' => $evaluacion['id_curso']]);
            $cursoDemora = $stmtCurso->fetch(PDO::FETCH_ASSOC);

            // Calcular disponibilidad usando el helper
            $fecha_finalizacion_actual = date('Y-m-d H:i:s');
            $disponibilidad = InstitutionService::calcularDisponibilidadCertificado(
                $fecha_finalizacion_actual,
                $cursoDemora ?? [],
                $instance_config
            );

            // La fecha_disponibilidad se guarda en BD solo como referencia histórica
            // El cálculo real siempre se hace en tiempo real
            $fecha_disponibilidad = $disponibilidad['fecha_disponible']->format('Y-m-d H:i:s');
            $hay_demora = !$disponibilidad['disponible'];

            $stmt = $pdo->prepare("
                UPDATE inscripciones
                SET estado = 'Aprobado',
                    nota_final = 10.00,
                    asistencia_porcentaje = 100.00,
                    fecha_finalizacion = :fecha,
                    certificado_disponible_desde = :fecha_disponibilidad
                WHERE id_miembro = :id_miembro
                  AND id_curso = :id_curso
            ");
            $stmt->execute([
                'fecha' => date('Y-m-d'),
                'fecha_disponibilidad' => $fecha_disponibilidad,
                'id_miembro' => $sesion['id_miembro'],
                'id_curso' => $evaluacion['id_curso']
            ]);

            // =====================================================
            // ENVIAR EMAILS DE NOTIFICACIÓN
            // =====================================================
            try {
                $emailEstudiante = $estudiante['email'] ?? '';

                if ($emailEstudiante && filter_var($emailEstudiante, FILTER_VALIDATE_EMAIL)) {
                    // Obtener id_instancia
                    $pdo_general = new PDO(
                        "mysql:host=" . env('GENERAL_DB_HOST', 'localhost') . ";dbname=verumax_general;charset=utf8mb4",
                        env('GENERAL_DB_USER', 'root'),
                        env('GENERAL_DB_PASS', ''),
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    $stmtInst = $pdo_general->prepare("SELECT id_instancia FROM instances WHERE slug = :slug");
                    $stmtInst->execute(['slug' => $institucion]);
                    $id_instancia = (int) $stmtInst->fetchColumn();

                    if ($id_instancia > 0) {
                        // Obtener nombre del curso
                        $stmtCurso = $pdo->prepare("SELECT nombre_curso FROM cursos WHERE id_curso = :id_curso");
                        $stmtCurso->execute(['id_curso' => $evaluacion['id_curso']]);
                        $nombre_curso = $stmtCurso->fetchColumn() ?: $evaluacion['nombre'];

                        // Preparar variables para el email
                        $url_portal = "https://{$institucion}.verumax.com/";

                        if ($hay_demora) {
                            // =====================================================
                            // CON DEMORA: Enviar 1 email informando fecha de disponibilidad
                            // (Ya NO se programa segundo email - el cálculo es en tiempo real)
                            // =====================================================
                            $fecha_disponibilidad_formateada = $disponibilidad['fecha_disponible']->format('d/m/Y \a \l\a\s H:i');
                            EmailService::enviarIndividual(
                                $id_instancia,
                                EmailService::TYPE_EVALUACION_APROBADA,
                                $emailEstudiante,
                                $nombre_estudiante,
                                [
                                    'nombre_curso' => $nombre_curso,
                                    'fecha_disponibilidad' => $fecha_disponibilidad_formateada,
                                    'url_portal' => $url_portal
                                ],
                                'evaluacion_aprobada'
                            );
                        } else {
                            // =====================================================
                            // SIN DEMORA: Enviar email de certificado disponible inmediatamente
                            // =====================================================
                            EmailService::enviarIndividual(
                                $id_instancia,
                                EmailService::TYPE_CERTIFICADO,
                                $emailEstudiante,
                                $nombre_estudiante,
                                [
                                    'nombre_curso' => $nombre_curso,
                                    'tipo_documento' => 'Certificado de Aprobación',
                                    'url_portal' => $url_portal
                                ],
                                'certificado_disponible'
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                // No bloquear el flujo si falla el email
                error_log("Error enviando email de certificado: " . $e->getMessage());
            }
        }

        // Redirigir a resultados
        header('Location: resultatum.php?' . http_build_query([
            'sessio' => $id_sessio,
            'institutio' => $institucion,
            'lang' => $lang
        ]));
        exit;
    }
}

$page_title = "Reflexión Final - " . htmlspecialchars($evaluacion['nombre_display']);
?>
<!DOCTYPE html>
<html lang="<?= substr($lang, 0, 2) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-primario { background-color: <?= $color_primario ?>; }
        .color-primario { color: <?= $color_primario ?>; }
        .border-primario { border-color: <?= $color_primario ?>; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex flex-col transition-colors duration-300">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-20 transition-colors duration-300">
        <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <?php if ($logo_url): ?>
                    <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo <?= htmlspecialchars($nombre_institucion) ?>" class="<?= $logo_class ?>">
                <?php else: ?>
                    <div class="h-10 w-10 rounded-full bg-primario flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($institucion, 0, 2)) ?>
                    </div>
                <?php endif; ?>
                <?php if ($logo_mostrar_texto): ?>
                <div>
                    <span class="text-lg font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($nombre_institucion) ?></span>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Reflexión final</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($nombre_estudiante) ?></p>
                    <p class="text-xs text-gray-500">Último paso</p>
                </div>

                <button id="dark-mode-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" aria-label="Alternar modo oscuro">
                    <i data-lucide="moon" class="w-5 h-5 text-gray-600 dark:hidden"></i>
                    <i data-lucide="sun" class="w-5 h-5 text-yellow-400 hidden dark:block"></i>
                </button>
            </div>
        </nav>
    </header>

    <!-- Main -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <!-- Encabezado -->
                <div class="bg-primario px-6 py-6 text-white text-center">
                    <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-3 opacity-90"></i>
                    <h2 class="text-xl font-bold">¡Completaste todas las preguntas!</h2>
                    <p class="mt-1 text-white/80">Solo falta un último paso</p>
                </div>

                <!-- Formulario -->
                <form method="POST" class="px-6 py-6">
                    <?php if ($error): ?>
                        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 text-sm flex items-start gap-2">
                            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="mb-6">
                        <label for="reflexion" class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                            <?= htmlspecialchars($texto_cierre) ?>
                        </label>
                        <textarea
                            id="reflexion"
                            name="reflexion"
                            rows="6"
                            <?= $minimo_caracteres > 0 ? 'minlength="' . $minimo_caracteres . '"' : '' ?>
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   resize-none transition-colors"
                            placeholder="Escribí tu reflexión aquí (opcional)..."
                        ><?= htmlspecialchars($reflexion) ?></textarea>
                        <div class="mt-2 flex justify-end text-sm">
                            <span id="contador" class="font-medium text-gray-500">0 caracteres</span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        id="btnEnviar"
                        class="w-full bg-primario text-white py-3 px-4 rounded-lg font-semibold
                               hover:opacity-90 transition-opacity focus:outline-none focus:ring-2
                               focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed
                               flex items-center justify-center gap-2"
                    >
                        <i data-lucide="send" class="w-5 h-5"></i>
                        Finalizar evaluación
                    </button>
                </form>
            </div>

            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                Tu reflexión será guardada junto con tus respuestas.
            </p>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 dark:bg-gray-950 text-white transition-colors duration-300">
        <div class="container mx-auto px-6 py-4 text-center text-sm text-gray-400">
            <p>&copy; <?= date("Y") ?> <?= htmlspecialchars($nombre_institucion) ?> - Todos los derechos reservados.</p>
            <p class="mt-1">Plataforma de evaluaciones <strong>Probatio</strong> por <a href="https://verumax.com" class="text-indigo-400 hover:underline">VERUMax</a></p>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        const textarea = document.getElementById('reflexion');
        const contador = document.getElementById('contador');
        const btnEnviar = document.getElementById('btnEnviar');
        const minimo = <?= $minimo_caracteres ?>;

        function actualizarContador() {
            const len = textarea.value.length;
            contador.textContent = len + ' caracteres';

            // Solo aplicar colores si hay un mínimo requerido
            if (minimo > 0) {
                if (len >= minimo) {
                    contador.classList.remove('text-red-500', 'text-gray-500');
                    contador.classList.add('text-green-600', 'dark:text-green-400');
                } else if (len > 0) {
                    contador.classList.remove('text-green-600', 'text-gray-500', 'dark:text-green-400');
                    contador.classList.add('text-red-500');
                } else {
                    contador.classList.remove('text-green-600', 'text-red-500', 'dark:text-green-400');
                    contador.classList.add('text-gray-500');
                }
                btnEnviar.disabled = len < minimo;
            }
            // Si no hay mínimo, el botón siempre está habilitado
        }

        textarea.addEventListener('input', actualizarContador);
        actualizarContador();

        // Toggle de modo oscuro
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
            });
        }
    </script>
</body>
</html>
