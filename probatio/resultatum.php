<?php
/**
 * PROBATIO - Resultados (resultatum.php)
 *
 * Pantalla final mostrando el resultado de la evaluación.
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

// Si la sesión no está completada y requiere cierre, redirigir
if ($sesion['estado'] !== 'completada') {
    // Verificar si completó todas las preguntas
    $preguntas = obtenerPreguntas($evaluacion['id_evaluatio']);
    $total = count($preguntas);

    if ($sesion['preguntas_completadas'] < $total) {
        // Volver a la evaluación
        header('Location: respondere.php?' . http_build_query([
            'sessio' => $id_sessio,
            'institutio' => $institucion,
            'lang' => $lang
        ]));
        exit;
    }

    // Completó preguntas pero falta cierre
    if ($evaluacion['requiere_cierre_cualitativo'] && empty($sesion['reflexion_final'])) {
        header('Location: clausura.php?' . http_build_query([
            'sessio' => $id_sessio,
            'institutio' => $institucion,
            'lang' => $lang
        ]));
        exit;
    }

    // Marcar como completada si no tiene cierre cualitativo
    if (!$evaluacion['requiere_cierre_cualitativo']) {
        $preguntas = obtenerPreguntas($evaluacion['id_evaluatio']);
        $total_preguntas = count($preguntas);

        // Calcular puntaje
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

        actualizarProgresoSesion($id_sessio, [
            'estado' => 'completada',
            'fecha_finalizacion' => date('Y-m-d H:i:s'),
            'puntaje_obtenido' => $correctas,
            'puntaje_maximo' => $total_preguntas,
            'porcentaje' => $porcentaje,
            'aprobado' => 1 // Siempre aprobado al completar
        ]);

        // Actualizar estado de inscripción - siempre aprobado con nota 10 y asistencia 100%
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
                // Obtener datos del estudiante para el email
                $pdo_nexus = getNexusConnection();
                $stmtEst = $pdo_nexus->prepare("SELECT nombre, apellido, email, identificador_principal FROM miembros WHERE id_miembro = :id");
                $stmtEst->execute(['id' => $sesion['id_miembro']]);
                $estData = $stmtEst->fetch(PDO::FETCH_ASSOC);

                $emailEstudiante = $estData['email'] ?? '';
                $nombreEstudiante = trim(($estData['nombre'] ?? '') . ' ' . ($estData['apellido'] ?? ''));

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
                                $nombreEstudiante,
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
                                $nombreEstudiante,
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

        // Recargar sesión
        $sesion = obtenerSesion($id_sessio);
    }
}

// Configuración institucional
$instance_config = InstitutionService::getConfig($institucion);
$color_primario = $instance_config['color_primario'] ?? '#0F52BA';
$logo_url = $instance_config['logo_url'] ?? '';
$nombre_institucion = $instance_config['nombre'] ?? $institucion;
$logo_estilo = $instance_config['logo_estilo'] ?? 'rectangular';
$logo_mostrar_texto = $instance_config['logo_mostrar_texto'] ?? 1;
// Calcular disponibilidad del certificado EN TIEMPO REAL
$fecha_disponibilidad_cert = null;
$certificado_ya_disponible = true;
if ($evaluacion['id_curso'] && $sesion['id_miembro']) {
    $pdo_check = getAcademiConnection();

    // Obtener fecha de finalización y config de demora del curso
    $stmtCheck = $pdo_check->prepare("
        SELECT i.fecha_finalizacion,
               c.usar_demora_global, c.demora_certificado_horas, c.demora_tipo, c.demora_fecha
        FROM inscripciones i
        INNER JOIN cursos c ON i.id_curso = c.id_curso
        WHERE i.id_curso = :id_curso AND i.id_miembro = :id_miembro
    ");
    $stmtCheck->execute([
        'id_curso' => $evaluacion['id_curso'],
        'id_miembro' => $sesion['id_miembro']
    ]);
    $inscData = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($inscData && $inscData['fecha_finalizacion']) {
        // Calcular disponibilidad en tiempo real
        $disponibilidad_display = InstitutionService::calcularDisponibilidadCertificado(
            $inscData['fecha_finalizacion'],
            $inscData,
            $instance_config
        );

        if (!$disponibilidad_display['disponible']) {
            $fecha_disponibilidad_cert = $disponibilidad_display['fecha_disponible']->format('d/m/Y \a \l\a\s H:i');
            $certificado_ya_disponible = false;
        }
    }
}

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

// Datos del estudiante
$pdo_nexus = getNexusConnection();
$stmt = $pdo_nexus->prepare("SELECT nombre, apellido, identificador_principal FROM miembros WHERE id_miembro = :id");
$stmt->execute(['id' => $sesion['id_miembro']]);
$estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre_estudiante = trim(($estudiante['nombre'] ?? '') . ' ' . ($estudiante['apellido'] ?? ''));
$dni = $estudiante['identificador_principal'] ?? '';

// Resultados
$puntaje = $sesion['puntaje_obtenido'] ?? 0;
$maximo = $sesion['puntaje_maximo'] ?? 0;
$porcentaje = $sesion['porcentaje'] ?? 0;

$fecha_inicio = $sesion['fecha_inicio'] ? date('d/m/Y H:i', strtotime($sesion['fecha_inicio'])) : '-';
$fecha_fin = $sesion['fecha_finalizacion'] ? date('d/m/Y H:i', strtotime($sesion['fecha_finalizacion'])) : '-';

// Mensaje de finalización
$mensaje_final = $evaluacion['mensaje_finalizacion'] ?? '';

// Obtener estadísticas de intentos por pregunta
$pdo = getAcademiConnection();
$stmt = $pdo->prepare("
    SELECT q.orden, q.tipo, COUNT(r.id_responsum) as intentos
    FROM quaestiones q
    LEFT JOIN responsa r ON q.id_quaestio = r.id_quaestio AND r.id_sessio = :id_sessio
    WHERE q.id_evaluatio = :id_evaluatio
    GROUP BY q.id_quaestio
    ORDER BY q.orden
");
$stmt->execute([
    'id_sessio' => $id_sessio,
    'id_evaluatio' => $evaluacion['id_evaluatio']
]);
$intentos_por_pregunta = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_intentos = array_sum(array_column($intentos_por_pregunta, 'intentos'));

// URL al portal de cursos del estudiante
$url_portal = "/cursus.php?" . http_build_query([
    'institutio' => $institucion,
    'documentum' => $dni,
    'lang' => $lang
]);

$page_title = "Resultado - " . htmlspecialchars($evaluacion['nombre_display']);
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

        @keyframes confetti {
            0% { transform: translateY(-100%) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            top: -10px;
            animation: confetti 3s ease-out forwards;
            z-index: 50;
        }

        @keyframes bounce-in {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        .bounce-in { animation: bounce-in 0.5s ease-out; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen transition-colors duration-300">
    <!-- Confetti -->
    <div id="confetti-container"></div>

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
                    <p class="text-xs text-gray-500 dark:text-gray-400">Evaluación completada</p>
                </div>
                <?php endif; ?>
            </div>

            <button id="dark-mode-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" aria-label="Alternar modo oscuro">
                <i data-lucide="moon" class="w-5 h-5 text-gray-600 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 text-yellow-400 hidden dark:block"></i>
            </button>
        </nav>
    </header>

    <!-- Main -->
    <main class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <!-- Encabezado con resultado -->
            <div class="bg-green-500 px-6 py-8 text-white text-center">
                <div class="bounce-in">
                    <i data-lucide="award" class="w-16 h-16 mx-auto mb-4"></i>
                    <h2 class="text-2xl font-bold">¡Felicitaciones!</h2>
                    <p class="mt-1 text-white/90">Completaste la evaluación exitosamente</p>
                </div>
            </div>

            <!-- Datos del estudiante -->
            <div class="px-6 py-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Estudiante</p>
                        <p class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($nombre_estudiante) ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">DNI: <?= htmlspecialchars($dni) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Evaluación</p>
                        <p class="font-medium text-gray-800 dark:text-white text-sm"><?= htmlspecialchars($evaluacion['nombre_display']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Resultados -->
            <div class="px-6 py-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                    Resultados
                </h3>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold color-primario"><?= $puntaje ?>/<?= $maximo ?></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Respuestas correctas</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-green-600">10</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nota final</p>
                    </div>
                </div>

                <div class="border-t dark:border-gray-700 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4"></i> Fecha de inicio
                        </span>
                        <span class="font-medium"><?= $fecha_inicio ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <i data-lucide="calendar-check" class="w-4 h-4"></i> Fecha de finalización
                        </span>
                        <span class="font-medium"><?= $fecha_fin ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span class="flex items-center gap-2">
                            <i data-lucide="percent" class="w-4 h-4"></i> Asistencia
                        </span>
                        <span class="font-medium text-green-600">100%</span>
                    </div>
                </div>
            </div>

            <!-- Mensaje final -->
            <?php if ($mensaje_final): ?>
                <div class="px-6 py-4 bg-blue-50 dark:bg-blue-900/20 border-t border-blue-100 dark:border-blue-800">
                    <div class="text-sm text-blue-800 dark:text-blue-200 flex items-start gap-2">
                        <i data-lucide="info" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
                        <div><?= $mensaje_final ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Resumen de intentos -->
            <div class="px-6 py-4 border-t dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Resumen de intentos</h3>
                <div class="flex flex-wrap justify-center gap-2 mb-3">
                    <?php foreach ($intentos_por_pregunta as $ip): ?>
                    <div class="text-center p-3 bg-gray-100 dark:bg-gray-700 rounded-lg min-w-[70px]">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">P<?= $ip['orden'] ?></div>
                        <div class="text-xl font-bold <?= $ip['intentos'] == 1 ? 'text-green-600 dark:text-green-400' : 'text-orange-500 dark:text-orange-400' ?>"><?= $ip['intentos'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                    Total: <strong><?= $total_intentos ?></strong> intento<?= $total_intentos != 1 ? 's' : '' ?> en <?= count($intentos_por_pregunta) ?> preguntas
                </p>
            </div>

            <!-- Acciones -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-750 border-t dark:border-gray-700">
                <div class="flex flex-col sm:flex-row gap-3">
                    <a
                        href="<?= htmlspecialchars($url_portal) ?>"
                        class="flex-1 text-center bg-primario text-white py-3 px-4 rounded-lg font-semibold hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
                    >
                        <i data-lucide="file-badge" class="w-5 h-5"></i>
                        Ir al portal de certificados
                    </a>
                    <a
                        href="respondere.php?<?= http_build_query(['sessio' => $id_sessio, 'institutio' => $institucion, 'lang' => $lang, 'ver' => 1, 'q' => 1]) ?>"
                        class="flex-1 text-center border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-semibold hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2"
                    >
                        <i data-lucide="eye" class="w-5 h-5"></i>
                        Recorrer preguntas
                    </a>
                </div>
            </div>
        </div>

        <!-- Nota -->
        <?php if ($fecha_disponibilidad_cert): ?>
        <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4 mt-6 text-center">
            <p class="text-sm text-blue-800 dark:text-blue-200 flex items-center justify-center gap-2 mb-2">
                <i data-lucide="clock" class="w-4 h-4"></i>
                Tu resultado ha sido registrado.
            </p>
            <p class="text-sm font-semibold text-blue-800 dark:text-blue-200 mt-2">
                Tu certificado estará disponible el <?= $fecha_disponibilidad_cert ?>
            </p>
        </div>
        <?php else: ?>
        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-6 flex items-center justify-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
            Tu resultado ha sido registrado. Ya podes acceder a tu certificado.
        </p>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 dark:bg-gray-950 text-white mt-8 transition-colors duration-300">
        <div class="container mx-auto px-6 py-4 text-center text-sm text-gray-400">
            <p>&copy; <?= date("Y") ?> <?= htmlspecialchars($nombre_institucion) ?> - Todos los derechos reservados.</p>
            <p class="mt-1">Plataforma de evaluaciones <strong>Probatio</strong> por <a href="https://verumax.com" class="text-indigo-400 hover:underline">VERUMax</a></p>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        // Confetti
        const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'];
        const container = document.getElementById('confetti-container');

        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 2 + 's';
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                container.appendChild(confetti);

                setTimeout(() => confetti.remove(), 5000);
            }, i * 50);
        }

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
