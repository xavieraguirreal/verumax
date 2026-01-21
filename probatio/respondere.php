<?php
/**
 * PROBATIO - Formulario de Evaluación (respondere.php)
 *
 * Muestra las preguntas de la evaluación con navegación libre.
 * Permite re-responder preguntas y navegar entre ellas.
 */

// Evitar caché
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once __DIR__ . '/config.php';

use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;

// =====================================================
// OBTENER Y VALIDAR SESIÓN
// =====================================================
$id_sessio = (int) getParam('sessio', 0);
$institucion = getParam('institutio');
$lang = getParam('lang', 'es_AR');
$pregunta_solicitada = (int) getParam('q', 0); // Navegación directa a pregunta

if (!$id_sessio || !$institucion) {
    die('Error: Parámetros incompletos.');
}

// Cargar sesión
$sesion = obtenerSesion($id_sessio);
if (!$sesion) {
    die('Error: Sesión no encontrada.');
}

// Verificar estado de la sesión
$modo_lectura = ($sesion['estado'] === 'completada');
$permitir_ver_completada = isset($_GET['ver']); // Parámetro para ver en modo lectura

// Si está completada y no pidió ver, redirigir a resultados
if ($modo_lectura && !$permitir_ver_completada) {
    header('Location: resultatum.php?' . http_build_query([
        'sessio' => $id_sessio,
        'institutio' => $institucion,
        'lang' => $lang
    ]));
    exit;
}

// Cargar evaluación
$evaluacion = obtenerEvaluacionPorId($sesion['id_evaluatio']);
if (!$evaluacion || $evaluacion['estado'] !== 'activa') {
    die('Error: Evaluación no disponible.');
}

// Configuración de mínimo de caracteres para preguntas abiertas
$minimo_caracteres_abierta = (int)($evaluacion['minimo_caracteres_abierta'] ?? 50);

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

// Cargar preguntas
$preguntas = obtenerPreguntas($evaluacion['id_evaluatio']);
$total_preguntas = count($preguntas);

if ($total_preguntas === 0) {
    die('Error: Esta evaluación no tiene preguntas configuradas.');
}

// Cargar historial de respuestas para TODAS las preguntas (para indicadores)
// NOTA: Movido aquí antes para poder validar acceso a preguntas
$pdo = getAcademiConnection();
$stmt = $pdo->prepare("
    SELECT r.id_quaestio, r.es_correcta, r.intento_numero
    FROM responsa r
    WHERE r.id_sessio = :id_sessio
    ORDER BY r.id_quaestio, r.intento_numero DESC
");
$stmt->execute(['id_sessio' => $id_sessio]);
$todas_respuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por pregunta para saber estado de cada una
$estado_preguntas = [];
foreach ($preguntas as $idx => $p) {
    $estado_preguntas[$p['id_quaestio']] = [
        'respondida' => false,
        'correcta' => false,
        'intentos' => 0,
        'orden' => $idx + 1
    ];
}
foreach ($todas_respuestas as $r) {
    $qid = $r['id_quaestio'];
    if (isset($estado_preguntas[$qid])) {
        $estado_preguntas[$qid]['respondida'] = true;
        $estado_preguntas[$qid]['intentos'] = max($estado_preguntas[$qid]['intentos'], $r['intento_numero']);
        if ($r['es_correcta']) {
            $estado_preguntas[$qid]['correcta'] = true;
        }
    }
}

// Calcular la pregunta más avanzada permitida
// (la primera pregunta no respondida correctamente)
$pregunta_max_permitida = 1;
foreach ($preguntas as $idx => $p) {
    $orden = $idx + 1;
    $estado = $estado_preguntas[$p['id_quaestio']];
    if ($estado['correcta']) {
        $pregunta_max_permitida = $orden + 1;
    } else {
        break;
    }
}
$pregunta_max_permitida = min($pregunta_max_permitida, $total_preguntas);

// Determinar pregunta actual (por parámetro q o por sesión)
if ($pregunta_solicitada > 0 && $pregunta_solicitada <= $total_preguntas) {
    // VALIDACIÓN DEL LADO DEL SERVIDOR:
    // Solo puede acceder si está en modo lectura O si la pregunta está dentro del rango permitido
    if ($modo_lectura || $pregunta_solicitada <= $pregunta_max_permitida) {
        $pregunta_actual_num = $pregunta_solicitada;
    } else {
        // Redirigir a la pregunta máxima permitida
        header('Location: respondere.php?' . http_build_query([
            'sessio' => $id_sessio,
            'institutio' => $institucion,
            'lang' => $lang,
            'q' => $pregunta_max_permitida
        ]));
        exit;
    }
} else {
    $pregunta_actual_num = max(1, min($sesion['pregunta_actual'], $total_preguntas));
}

$pregunta = $preguntas[$pregunta_actual_num - 1];
$opciones = json_decode($pregunta['opciones'], true) ?? [];

// Respuestas de la pregunta actual
$respuestas_pregunta_actual = obtenerRespuestasPregunta($id_sessio, $pregunta['id_quaestio']);
$intentos_actual = count($respuestas_pregunta_actual);

// Obtener la última respuesta para mostrar qué seleccionó
$ultima_respuesta = null;
$respuestas_seleccionadas_previas = [];
if (!empty($respuestas_pregunta_actual)) {
    $ultima_respuesta = end($respuestas_pregunta_actual);
    $respuestas_seleccionadas_previas = json_decode($ultima_respuesta['respuestas_seleccionadas'], true) ?? [];
}

// =====================================================
// CONFIGURACIÓN INSTITUCIONAL
// =====================================================
$instance_config = InstitutionService::getConfig($institucion);
LanguageService::init($institucion, $lang);

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

// Cargar datos del estudiante
$pdo_nexus = getNexusConnection();
$stmt = $pdo_nexus->prepare("SELECT nombre, apellido FROM miembros WHERE id_miembro = :id");
$stmt->execute(['id' => $sesion['id_miembro']]);
$estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
$nombre_estudiante = trim(($estudiante['nombre'] ?? '') . ' ' . ($estudiante['apellido'] ?? ''));

// Contar preguntas respondidas correctamente
$preguntas_correctas = 0;
foreach ($estado_preguntas as $ep) {
    if ($ep['correcta']) $preguntas_correctas++;
}
$progreso_porcentaje = round(($preguntas_correctas / $total_preguntas) * 100);

// Verificar si puede finalizar (metodología afirmacion requiere todas correctas)
$metodologia = $evaluacion['metodologia'] ?? 'tradicional';
$todas_correctas = ($preguntas_correctas === $total_preguntas);
$puede_finalizar = ($metodologia === 'tradicional') || $todas_correctas;

// Variables para header SAJuR
$page_title = "Pregunta {$pregunta_actual_num} - " . htmlspecialchars($evaluacion['nombre_display']);
$is_validation_view = true; // Ocultar menú de navegación principal
?>
<!DOCTYPE html>
<html lang="<?= substr($lang, 0, 2) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>

    <!-- Meta tags para compartir -->
    <meta name="description" content="<?= htmlspecialchars($evaluacion['descripcion'] ?? 'Evaluacion de ' . $instance_config['nombre']) ?>">

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn, etc.) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($evaluacion['nombre_display']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($evaluacion['descripcion'] ?? 'Evaluacion educativa de ' . $instance_config['nombre']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($instance_config['logo_url'] ?? 'https://verumax.com/assets/images/verumax-og.png') ?>">
    <meta property="og:url" content="https://<?= htmlspecialchars($institucion) ?>.verumax.com/probatio/<?= htmlspecialchars($evaluacion['codigo']) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($instance_config['nombre']) ?>">
    <meta property="og:locale" content="<?= htmlspecialchars($lang) ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= htmlspecialchars($evaluacion['nombre_display']) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($evaluacion['descripcion'] ?? 'Evaluacion educativa de ' . $instance_config['nombre']) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($instance_config['logo_url'] ?? 'https://verumax.com/assets/images/verumax-og.png') ?>">

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
        .color-primario { color: <?= $color_primario ?>; }
        .bg-primario { background-color: <?= $color_primario ?>; }
        .border-primario { border-color: <?= $color_primario ?>; }
        .hover-primario:hover { background-color: <?= $color_primario ?>; }

        .option-card { transition: all 0.2s ease; }
        .option-card:hover:not(.disabled) {
            border-color: <?= $color_primario ?>;
            background-color: <?= $color_primario ?>08;
        }
        .option-card.selected {
            border-color: <?= $color_primario ?>;
            background-color: <?= $color_primario ?>15;
        }
        .option-card.correct { border-color: #10b981; background-color: #d1fae5; }
        .option-card.incorrect { border-color: #ef4444; background-color: #fee2e2; }

        .nav-dot { transition: all 0.2s ease; }
        .nav-dot:hover { transform: scale(1.1); }
        .nav-dot.current { box-shadow: 0 0 0 3px <?= $color_primario ?>40; }

        .feedback-panel { animation: slideIn 0.3s ease; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen transition-colors duration-300">
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
                    <p class="text-xs text-gray-500 dark:text-gray-400">Evaluación en curso</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($nombre_estudiante) ?></p>
                    <p class="text-xs text-gray-500"><?= $preguntas_correctas ?>/<?= $total_preguntas ?> completadas</p>
                </div>

                <!-- Toggle de modo oscuro -->
                <button id="dark-mode-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" aria-label="Alternar modo oscuro">
                    <i data-lucide="moon" class="w-5 h-5 text-gray-600 dark:hidden"></i>
                    <i data-lucide="sun" class="w-5 h-5 text-yellow-400 hidden dark:block"></i>
                </button>
            </div>
        </nav>

        <!-- Barra de progreso -->
        <div class="h-1 bg-gray-200 dark:bg-gray-700">
            <div class="h-full bg-green-500 transition-all duration-500" style="width: <?= $progreso_porcentaje ?>%"></div>
        </div>
    </header>

    <!-- Main -->
    <main class="container mx-auto px-4 py-6 max-w-3xl">
        <?php if ($modo_lectura): ?>
        <!-- Banner modo lectura -->
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl flex items-center gap-3">
            <i data-lucide="eye" class="w-6 h-6 text-blue-500 flex-shrink-0"></i>
            <div class="flex-1">
                <p class="font-semibold text-blue-800 dark:text-blue-200">Modo lectura</p>
                <p class="text-sm text-blue-600 dark:text-blue-300">Esta evaluación ya fue completada. Podés recorrer las preguntas pero no modificar respuestas.</p>
            </div>
            <a href="resultatum.php?<?= http_build_query(['sessio' => $id_sessio, 'institutio' => $institucion, 'lang' => $lang]) ?>"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                Ver resultado
            </a>
        </div>
        <?php endif; ?>

        <!-- Navegación de preguntas -->
        <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Navegación</h2>
                <span class="text-xs text-gray-500"><?= $progreso_porcentaje ?>% completado</span>
            </div>
            <div class="flex justify-center gap-2 flex-wrap">
                <?php foreach ($preguntas as $i => $p):
                    $num = $i + 1;
                    $estado = $estado_preguntas[$p['id_quaestio']];
                    $is_current = ($num === $pregunta_actual_num);

                    // Color según estado (siempre visible)
                    if ($estado['correcta']) {
                        $dot_class = 'bg-green-500 text-white';
                    } elseif ($estado['respondida']) {
                        $dot_class = 'bg-orange-400 text-white';
                    } else {
                        $dot_class = 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300';
                    }

                    // Indicador de pregunta actual: ring
                    $current_indicator = $is_current ? 'ring-2 ring-offset-2 ring-gray-800 dark:ring-white' : '';

                    // Navegación híbrida:
                    // - Puede ir hacia atrás siempre
                    // - Para avanzar, debe haber respondido CORRECTAMENTE la pregunta actual
                    $pregunta_actual_correcta = $estado_preguntas[$pregunta['id_quaestio']]['correcta'] ?? false;
                    $puede_navegar = $modo_lectura || $num <= $pregunta_actual_num || $pregunta_actual_correcta;

                    $nav_dot_params = [
                        'sessio' => $id_sessio,
                        'institutio' => $institucion,
                        'lang' => $lang,
                        'q' => $num,
                        '_t' => time()
                    ];
                    if ($modo_lectura) $nav_dot_params['ver'] = 1;
                    $params = http_build_query($nav_dot_params);
                ?>
                    <?php if ($puede_navegar): ?>
                        <a href="respondere.php?<?= $params ?>"
                           class="nav-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-medium <?= $dot_class ?> <?= $current_indicator ?>"
                           title="Pregunta <?= $num ?><?= $estado['correcta'] ? ' ✓' : ($estado['respondida'] ? ' (respondida)' : '') ?>">
                            <?= $num ?>
                        </a>
                    <?php else: ?>
                        <span class="nav-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-medium <?= $dot_class ?> <?= $current_indicator ?> opacity-50 cursor-not-allowed"
                              title="Respondé correctamente la pregunta actual para avanzar">
                            <?= $num ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="flex justify-center gap-4 mt-3 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500"></span> Correcta</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-orange-400"></span> Respondida</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-gray-300"></span> Pendiente</span>
            </div>
        </div>

        <!-- Tarjeta de pregunta -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <!-- Enunciado -->
            <div class="px-6 py-5 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primario text-white flex items-center justify-center font-bold"
                         style="background-color: <?= $color_primario ?>">
                        <?= $pregunta_actual_num ?>
                    </div>
                    <div class="flex-1">
                        <?php if (!empty($pregunta['contexto'])): ?>
                            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 rounded">
                                <p class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-1">
                                    <i data-lucide="book-open" class="w-4 h-4 inline mr-1"></i>
                                    Contexto:
                                </p>
                                <p class="text-sm text-blue-800 dark:text-blue-100 leading-relaxed">
                                    <?= nl2br(htmlspecialchars($pregunta['contexto'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Consigna:</p>
                        <p class="text-lg text-gray-800 dark:text-gray-100 leading-relaxed">
                            <?= nl2br(htmlspecialchars($pregunta['enunciado'])) ?>
                        </p>
                        <?php if ($pregunta['tipo'] === 'multiple_answer'): ?>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <i data-lucide="info" class="w-4 h-4"></i>
                                Seleccioná todas las opciones que correspondan
                            </p>
                        <?php endif; ?>
                        <?php if ($intentos_actual > 0): ?>
                            <p class="mt-2 text-sm text-blue-600 dark:text-blue-400">
                                Ya respondiste esta pregunta (<?= $intentos_actual ?> intento<?= $intentos_actual > 1 ? 's' : '' ?>)
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Opciones -->
            <form id="formRespuesta" class="px-6 py-5">
                <input type="hidden" name="id_sessio" value="<?= $id_sessio ?>">
                <input type="hidden" name="id_quaestio" value="<?= $pregunta['id_quaestio'] ?>">

                <?php
                // Verificar si esta pregunta ya fue respondida correctamente
                $pregunta_ya_correcta = $estado_preguntas[$pregunta['id_quaestio']]['correcta'] ?? false;

                // Las preguntas abiertas se pueden editar hasta finalizar (no se bloquean)
                $es_pregunta_abierta = ($pregunta['tipo'] === 'abierta');
                $pregunta_bloqueada = $modo_lectura || ($pregunta_ya_correcta && !$es_pregunta_abierta);
                ?>

                <?php if ($pregunta['tipo'] === 'abierta'): ?>
                    <!-- Pregunta de texto libre -->
                    <div class="space-y-3">
                        <textarea
                            id="respuestaTexto"
                            name="respuesta_texto"
                            rows="6"
                            class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   resize-none transition-colors"
                            placeholder="Escribí tu respuesta aquí<?= $minimo_caracteres_abierta > 0 ? ' (mínimo ' . $minimo_caracteres_abierta . ' caracteres)' : '' ?>..."
                            <?= $modo_lectura ? 'disabled' : '' ?>
                        ><?php
                            // Mostrar respuesta previa si existe
                            if ($ultima_respuesta && !empty($respuestas_seleccionadas_previas[0])) {
                                echo htmlspecialchars($respuestas_seleccionadas_previas[0]);
                            }
                        ?></textarea>

                        <!-- Contador de caracteres -->
                        <?php if (!$modo_lectura): ?>
                        <div class="flex justify-between items-center text-sm">
                            <span id="contadorCaracteres" class="text-gray-500 dark:text-gray-400">
                                <span id="numCaracteres">0</span><?= $minimo_caracteres_abierta > 0 ? ' / ' . $minimo_caracteres_abierta . ' caracteres mínimos' : ' caracteres' ?>
                            </span>
                            <span id="estadoCaracteres" class="text-red-500">
                                <i data-lucide="alert-circle" class="w-4 h-4 inline"></i> Faltan caracteres
                            </span>
                        </div>
                        <?php endif; ?>

                        <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-900 dark:text-blue-100">
                                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                                Esta es una pregunta abierta. Tu respuesta es una oportunidad para reflexionar y profundizar en el tema.
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Preguntas de opción múltiple -->
                    <div class="space-y-3" id="opciones">
                    <?php
                    $mostrar_resultado = $modo_lectura || $pregunta_ya_correcta;
                    $inputs_deshabilitados = $modo_lectura || $pregunta_ya_correcta;
                    ?>
                    <?php foreach ($opciones as $opcion): ?>
                        <?php
                        $letra = $opcion['letra'];
                        $input_type = ($pregunta['tipo'] === 'multiple_choice') ? 'radio' : 'checkbox';
                        $es_correcta = $opcion['es_correcta'] ?? false;
                        $fue_seleccionada = in_array($letra, $respuestas_seleccionadas_previas);
                        $feedback_opcion = $opcion['feedback'] ?? '';

                        // Determinar clase visual según el estado
                        $opcion_class = 'border-gray-200 dark:border-gray-600';
                        $badge_html = '';

                        if ($mostrar_resultado) {
                            // Mostrar resultado: verde correcta, rojo incorrectas
                            if ($es_correcta) {
                                $opcion_class = 'border-green-400 bg-green-50 dark:bg-green-900/30 dark:border-green-600';
                                $badge_html = '<span class="ml-2 text-green-600 dark:text-green-400 text-sm font-medium">✓ Correcta</span>';
                            } else {
                                $opcion_class = 'border-red-300 bg-red-50 dark:bg-red-900/20 dark:border-red-600';
                                $badge_html = '<span class="ml-2 text-red-500 dark:text-red-400 text-sm font-medium">✗</span>';
                            }
                        }
                        ?>
                        <div class="option-card block cursor-pointer border-2 <?= $opcion_class ?> rounded-lg p-4 hover:shadow-sm dark:bg-gray-750"
                             data-letra="<?= htmlspecialchars($letra) ?>"
                             data-correcta="<?= $es_correcta ? '1' : '0' ?>"
                             data-feedback="<?= htmlspecialchars($feedback_opcion) ?>"
                             data-modo="<?= $mostrar_resultado ? 'resultado' : 'responder' ?>">
                            <div class="flex items-start gap-3">
                                <input
                                    type="<?= $input_type ?>"
                                    name="respuestas[]"
                                    value="<?= htmlspecialchars($letra) ?>"
                                    class="mt-1 h-5 w-5 text-blue-600 focus:ring-blue-500 pointer-events-none"
                                    <?= $inputs_deshabilitados ? 'disabled' : '' ?>
                                >
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($letra) ?>.</span>
                                    <span class="text-gray-800 dark:text-gray-200"><?= htmlspecialchars($opcion['texto']) ?></span>
                                    <?= $badge_html ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Botones de acción -->
                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <?php
                    // En modo lectura, agregar parámetro 'ver' a los enlaces de navegación
                    $nav_params_base = ['sessio' => $id_sessio, 'institutio' => $institucion, 'lang' => $lang];
                    if ($modo_lectura) $nav_params_base['ver'] = 1;
                    ?>

                    <!-- Anterior -->
                    <?php if ($pregunta_actual_num > 1):
                        $params_ant = http_build_query(array_merge($nav_params_base, ['q' => $pregunta_actual_num - 1, '_t' => time()]));
                    ?>
                        <a href="respondere.php?<?= $params_ant ?>"
                           class="flex-1 text-center border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-3 px-4 rounded-lg font-semibold hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i> Anterior
                        </a>
                    <?php endif; ?>

                    <?php if (!$modo_lectura && !$pregunta_bloqueada): ?>
                    <!-- Verificar/Guardar (solo si no está en modo lectura y no bloqueada) -->
                    <button
                        type="button"
                        id="btnVerificar"
                        class="flex-1 bg-primario text-white py-3 px-6 rounded-lg font-semibold
                               hover:opacity-90 transition-opacity focus:outline-none focus:ring-2
                               focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        style="background-color: <?= $color_primario ?>"
                    >
                        <?php if ($es_pregunta_abierta): ?>
                        <i data-lucide="save" class="w-5 h-5"></i>
                        <?= $pregunta_ya_correcta ? 'Actualizar respuesta' : 'Guardar respuesta' ?>
                        <?php else: ?>
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        Verificar respuesta
                        <?php endif; ?>
                    </button>
                    <?php elseif ($pregunta_bloqueada && !$modo_lectura): ?>
                    <?php if ($es_pregunta_abierta): ?>
                    <!-- Esto no debería pasar porque abiertas no se bloquean, pero por si acaso -->
                    <div class="flex-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 py-3 px-6 rounded-lg font-semibold flex items-center justify-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        Respuesta guardada
                    </div>
                    <?php else: ?>
                    <!-- Botón para ver devolución de pregunta ya correcta -->
                    <?php
                    // Obtener el feedback de la opción correcta
                    $feedback_correcta = '';
                    foreach ($opciones as $op) {
                        if ($op['es_correcta'] ?? false) {
                            $feedback_correcta = $op['feedback'] ?? '';
                            break;
                        }
                    }
                    ?>
                    <button
                        type="button"
                        id="btnVerDevolucion"
                        data-feedback="<?= htmlspecialchars($feedback_correcta) ?>"
                        class="flex-1 bg-green-600 text-white py-3 px-6 rounded-lg font-semibold
                               hover:bg-green-700 transition-colors focus:outline-none focus:ring-2
                               focus:ring-offset-2 flex items-center justify-center gap-2"
                    >
                        <i data-lucide="message-square-text" class="w-5 h-5"></i>
                        Ver devolución
                    </button>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- Siguiente / Finalizar / Ver resultado -->
                    <?php if ($pregunta_actual_num < $total_preguntas): ?>
                        <?php
                        $params_sig = http_build_query(array_merge($nav_params_base, ['q' => $pregunta_actual_num + 1, '_t' => time()]));
                        $pregunta_correcta = $estado_preguntas[$pregunta['id_quaestio']]['correcta'] ?? false;
                        $btn_habilitado = $modo_lectura || $pregunta_correcta;
                        ?>
                        <a href="respondere.php?<?= $params_sig ?>"
                           id="btnSiguiente"
                           class="flex-1 text-center bg-green-600 text-white py-3 px-4 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2 <?= $btn_habilitado ? 'hover:bg-green-700' : 'opacity-50 pointer-events-none' ?>"
                           <?= $btn_habilitado ? '' : 'data-disabled="true"' ?>
                           title="<?= $btn_habilitado ? '' : 'Respondé correctamente para continuar' ?>">
                            Siguiente <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    <?php elseif ($modo_lectura): ?>
                        <!-- En modo lectura, última pregunta muestra "Ver resultado" -->
                        <a href="resultatum.php?<?= http_build_query(['sessio' => $id_sessio, 'institutio' => $institucion, 'lang' => $lang]) ?>"
                           class="flex-1 text-center bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="award" class="w-4 h-4"></i> Ver resultado
                        </a>
                    <?php else: ?>
                        <?php if ($puede_finalizar): ?>
                            <?php $params_fin = http_build_query(['sessio' => $id_sessio, 'institutio' => $institucion, 'lang' => $lang]); ?>
                            <button type="button"
                                    id="btnFinalizar"
                                    data-url="clausura.php?<?= $params_fin ?>"
                                    class="flex-1 text-center bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                                Finalizar <i data-lucide="flag" class="w-4 h-4"></i>
                            </button>
                        <?php else: ?>
                            <button type="button"
                                    disabled
                                    class="flex-1 text-center bg-gray-400 text-white py-3 px-4 rounded-lg font-semibold cursor-not-allowed flex items-center justify-center gap-2"
                                    title="Debés responder correctamente todas las preguntas para finalizar">
                                <i data-lucide="lock" class="w-4 h-4"></i> Faltan <?= $total_preguntas - $preguntas_correctas ?> correctas
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Info adicional -->
        <div class="mt-4 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>Podés navegar entre preguntas y modificar tus respuestas en cualquier momento.</p>
            <p class="mt-1">Todos tus intentos quedan registrados.</p>
        </div>
    </main>

    <!-- Modal de confirmación de finalización -->
    <div id="modalFinalizar" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-900/75 transition-opacity" onclick="cerrarModal()"></div>

            <!-- Modal -->
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-auto transform transition-all">
                <div class="px-6 py-5 border-b dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-600 dark:text-amber-400"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">¿Finalizar evaluación?</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Esta acción no se puede deshacer</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <div class="text-left space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <p class="flex items-start gap-2">
                            <i data-lucide="check" class="w-5 h-5 text-green-500 flex-shrink-0"></i>
                            <span>Tu nota quedará registrada como <strong>aprobado</strong></span>
                        </p>
                        <p class="flex items-start gap-2">
                            <i data-lucide="eye" class="w-5 h-5 text-blue-500 flex-shrink-0"></i>
                            <span>Podrás recorrer las preguntas en <strong>modo lectura</strong></span>
                        </p>
                        <p class="flex items-start gap-2">
                            <i data-lucide="lock" class="w-5 h-5 text-red-500 flex-shrink-0"></i>
                            <span><strong>No podrás</strong> modificar respuestas ni registrar nuevos intentos</span>
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-750 flex gap-3">
                    <button type="button"
                            onclick="cerrarModal()"
                            class="flex-1 py-2.5 px-4 border border-gray-300 dark:border-gray-600 rounded-lg font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button type="button"
                            id="btnConfirmarFinalizar"
                            class="flex-1 py-2.5 px-4 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Sí, finalizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Feedback -->
    <div id="modalFeedback" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-900/75 transition-opacity" onclick="cerrarModalFeedback()"></div>

            <!-- Modal -->
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-auto transform transition-all">
                <div id="feedbackHeader" class="px-6 py-5 border-b dark:border-gray-700">
                    <!-- Se llenará dinámicamente -->
                </div>

                <div id="feedbackBody" class="px-6 py-6">
                    <!-- Se llenará dinámicamente -->
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-750">
                    <button type="button"
                            onclick="cerrarModalFeedback()"
                            id="btnCerrarFeedback"
                            class="w-full py-2.5 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 dark:bg-gray-950 text-white mt-8 transition-colors duration-300">
        <div class="container mx-auto px-6 py-4 text-center text-sm text-gray-400">
            <p>&copy; <?= date("Y") ?> <?= htmlspecialchars($nombre_institucion) ?> - Todos los derechos reservados.</p>
            <p class="mt-1">Plataforma de evaluaciones <strong>Probatio</strong> por <a href="https://verumax.com" class="text-indigo-400 hover:underline">VERUMax</a></p>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        const config = {
            sessio: <?= $id_sessio ?>,
            quaestio: <?= $pregunta['id_quaestio'] ?>,
            preguntaNum: <?= $pregunta_actual_num ?>,
            totalPreguntas: <?= $total_preguntas ?>,
            institutio: '<?= htmlspecialchars($institucion) ?>',
            lang: '<?= htmlspecialchars($lang) ?>',
            tipoPregunta: '<?= htmlspecialchars($pregunta['tipo']) ?>'
        };

        const form = document.getElementById('formRespuesta');
        const btnVerificar = document.getElementById('btnVerificar');
        const modalFeedback = document.getElementById('modalFeedback');
        const feedbackHeader = document.getElementById('feedbackHeader');
        const feedbackBody = document.getElementById('feedbackBody');
        const btnSiguiente = document.getElementById('btnSiguiente');
        const opciones = document.querySelectorAll('.option-card');

        // Variable para trackear si la última respuesta fue correcta
        let ultimaRespuestaCorrecta = false;

        // Contador de caracteres para preguntas abiertas
        const textarea = document.getElementById('respuestaTexto');
        const numCaracteres = document.getElementById('numCaracteres');
        const estadoCaracteres = document.getElementById('estadoCaracteres');
        const MIN_CARACTERES = <?= $minimo_caracteres_abierta ?>;

        // Guardar texto original para detectar cambios
        const textoOriginal = textarea ? textarea.value : '';
        const tieneRespuestaPrevia = textoOriginal.length > 0;

        if (textarea && numCaracteres) {
            function actualizarContador() {
                const longitud = textarea.value.length;
                const textoActual = textarea.value;
                const fueModificado = textoActual !== textoOriginal;
                numCaracteres.textContent = longitud;

                if (MIN_CARACTERES === 0 || longitud >= MIN_CARACTERES) {
                    if (tieneRespuestaPrevia && !fueModificado) {
                        estadoCaracteres.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4 inline"></i> Guardada';
                        estadoCaracteres.className = 'text-green-600 dark:text-green-400';
                    } else if (longitud > 0) {
                        estadoCaracteres.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4 inline"></i> Listo para enviar';
                        estadoCaracteres.className = 'text-green-600 dark:text-green-400';
                    } else {
                        estadoCaracteres.innerHTML = '';
                        estadoCaracteres.className = 'text-gray-500';
                    }
                } else {
                    const faltan = MIN_CARACTERES - longitud;
                    estadoCaracteres.innerHTML = `<i data-lucide="alert-circle" class="w-4 h-4 inline"></i> Faltan ${faltan} caracteres`;
                    estadoCaracteres.className = 'text-red-500';
                }

                // Habilitar/deshabilitar botón según si hay cambios
                if (btnVerificar && config.tipoPregunta === 'abierta' && tieneRespuestaPrevia) {
                    if (fueModificado && (MIN_CARACTERES === 0 || longitud >= MIN_CARACTERES)) {
                        btnVerificar.disabled = false;
                        btnVerificar.classList.remove('opacity-50', 'cursor-not-allowed');
                    } else if (!fueModificado) {
                        btnVerificar.disabled = true;
                        btnVerificar.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                }

                lucide.createIcons();
            }

            textarea.addEventListener('input', actualizarContador);
            // Actualizar al cargar si hay texto previo
            actualizarContador();

            // Si ya tiene respuesta, deshabilitar botón inicialmente
            if (tieneRespuestaPrevia && btnVerificar && config.tipoPregunta === 'abierta') {
                btnVerificar.disabled = true;
                btnVerificar.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // Manejar clic en opciones
        opciones.forEach(card => {
            card.addEventListener('click', function(e) {
                const modo = this.dataset.modo;
                const input = this.querySelector('input');

                if (modo === 'resultado') {
                    // En modo resultado, mostrar feedback de esta opción
                    e.preventDefault();
                    const letra = this.dataset.letra;
                    const esCorrecta = this.dataset.correcta === '1';
                    const feedbackRaw = this.dataset.feedback || '';
                    const feedback = feedbackRaw.replace(/\\n/g, '<br>').replace(/\n/g, '<br>');

                    const iconClass = esCorrecta ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                    const bgClass = esCorrecta ? 'bg-green-100 dark:bg-green-900/50' : 'bg-red-100 dark:bg-red-900/50';
                    const icon = esCorrecta ? 'check-circle-2' : 'x-circle';
                    const titulo = esCorrecta ? '¡Respuesta Correcta!' : 'Respuesta Incorrecta';
                    const textClass = esCorrecta ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200';

                    feedbackHeader.innerHTML = `
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full ${bgClass} flex items-center justify-center">
                                <i data-lucide="${icon}" class="w-7 h-7 ${iconClass}"></i>
                            </div>
                            <div class="text-left">
                                <h3 class="text-lg font-bold ${textClass}">Opción ${letra}: ${titulo}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Retroalimentación</p>
                            </div>
                        </div>
                    `;

                    feedbackBody.innerHTML = `
                        <div class="text-left space-y-3">
                            ${feedback ? `
                                <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                                    <p class="text-sm text-blue-900 dark:text-blue-100">${feedback}</p>
                                </div>
                            ` : '<p class="text-gray-600 dark:text-gray-400">No hay devolución disponible para esta opción.</p>'}
                        </div>
                    `;

                    modalFeedback.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    lucide.createIcons();
                } else {
                    // En modo responder, seleccionar/deseleccionar
                    if (input && !input.disabled) {
                        if (input.type === 'radio') {
                            opciones.forEach(c => c.classList.remove('selected'));
                            input.checked = true;
                            this.classList.add('selected');
                        } else {
                            input.checked = !input.checked;
                            this.classList.toggle('selected', input.checked);
                        }
                    }
                }
            });
        });

        // Verificar respuesta (solo si el botón existe)
        if (btnVerificar) {
        btnVerificar.addEventListener('click', async function() {
            let seleccionadas;

            if (config.tipoPregunta === 'abierta') {
                // Pregunta de texto
                const textarea = document.getElementById('respuestaTexto');
                const texto = textarea.value.trim();

                if (texto.length === 0) {
                    alert('Por favor, escribí tu respuesta');
                    return;
                }

                seleccionadas = [texto];
                btnVerificar.innerHTML = '<i data-lucide="loader" class="w-5 h-5 animate-spin"></i> Guardando...';
            } else {
                // Pregunta de opción múltiple
                seleccionadas = Array.from(form.querySelectorAll('input[name="respuestas[]"]:checked'))
                    .map(i => i.value);

                if (seleccionadas.length === 0) {
                    alert('Seleccioná al menos una opción');
                    return;
                }

                btnVerificar.innerHTML = '<i data-lucide="loader" class="w-5 h-5 animate-spin"></i> Verificando...';
            }

            btnVerificar.disabled = true;
            lucide.createIcons();

            try {
                const response = await fetch('verificare.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_sessio: config.sessio,
                        id_quaestio: config.quaestio,
                        respuestas: seleccionadas,
                        tipo: config.tipoPregunta
                    })
                });

                const result = await response.json();

                if (result.error) {
                    alert(result.error);
                    btnVerificar.disabled = false;
                    btnVerificar.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5"></i> Verificar respuesta';
                    lucide.createIcons();
                    return;
                }

                // Mostrar feedback
                mostrarFeedback(result);

                // Para preguntas abiertas, recargar la página al cerrar el modal
                // para actualizar el estado de los botones (Finalizar, etc.)
                if (config.tipoPregunta === 'abierta') {
                    // Marcar que debe recargar al cerrar el modal
                    window.recargarAlCerrarModal = true;
                }

                // Restaurar botón
                btnVerificar.disabled = false;
                if (config.tipoPregunta === 'abierta') {
                    btnVerificar.innerHTML = '<i data-lucide="save" class="w-5 h-5"></i> Actualizar respuesta';
                } else {
                    btnVerificar.innerHTML = '<i data-lucide="refresh-cw" class="w-5 h-5"></i> Intentar de nuevo';
                }
                lucide.createIcons();

            } catch (err) {
                console.error(err);
                alert('Error al verificar. Intentá de nuevo.');
                btnVerificar.disabled = false;
                btnVerificar.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5"></i> Verificar respuesta';
                lucide.createIcons();
            }
        });
        } // fin if (btnVerificar)

        function mostrarFeedback(result) {
            let headerBgClass, iconBgClass, iconColorClass, textClass, icon, titulo, mensaje;

            // Guardar si la respuesta fue correcta para usar en cerrarModalFeedback
            ultimaRespuestaCorrecta = result.es_correcta || config.tipoPregunta === 'abierta';

            if (config.tipoPregunta === 'abierta') {
                // Feedback para pregunta de texto
                headerBgClass = 'bg-blue-50 dark:bg-blue-900/50';
                iconBgClass = 'bg-blue-100 dark:bg-blue-900/50';
                iconColorClass = 'text-blue-600 dark:text-blue-400';
                textClass = 'text-blue-800 dark:text-blue-200';
                icon = 'save';
                titulo = '¡Respuesta Guardada!';
                mensaje = 'Tu respuesta fue registrada correctamente';
            } else {
                // Feedback para pregunta de opción múltiple
                const isCorrect = result.es_correcta;
                headerBgClass = isCorrect ? 'bg-green-50 dark:bg-green-900/50' : 'bg-red-50 dark:bg-red-900/50';
                iconBgClass = isCorrect ? 'bg-green-100 dark:bg-green-900/50' : 'bg-red-100 dark:bg-red-900/50';
                iconColorClass = isCorrect ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                textClass = isCorrect ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200';
                icon = isCorrect ? 'check-circle-2' : 'x-circle';
                titulo = isCorrect ? '¡Respuesta Correcta!' : 'Respuesta Incorrecta';
                mensaje = isCorrect
                    ? 'Excelente trabajo, seguí así'
                    : 'No te preocupes, podés intentarlo de nuevo';
            }

            feedbackHeader.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full ${iconBgClass} flex items-center justify-center">
                        <i data-lucide="${icon}" class="w-7 h-7 ${iconColorClass}"></i>
                    </div>
                    <div class="text-left">
                        <h3 class="text-lg font-bold ${textClass}">${titulo}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">${mensaje}</p>
                    </div>
                </div>
            `;

            feedbackBody.innerHTML = `
                <div class="text-left space-y-3">
                    ${result.explicacion ? `
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="flex items-start gap-2">
                                <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"></i>
                                <p class="text-sm text-blue-900 dark:text-blue-100">${result.explicacion}</p>
                            </div>
                        </div>
                    ` : ''}
                    <div class="text-center text-xs text-gray-500 dark:text-gray-400">
                        Intento #${result.intento}
                    </div>
                </div>
            `;

            modalFeedback.classList.remove('hidden');
            lucide.createIcons();

            // Si la respuesta es correcta, aplicar cambios permanentes
            if (result.es_correcta && result.opciones_correctas) {
                // Marcar TODAS las opciones con colores verde/rojo
                opciones.forEach(card => {
                    const input = card.querySelector('input');
                    const letra = input.value;
                    const esCorrecta = result.opciones_correctas.includes(letra);

                    card.classList.remove('selected');

                    // Marcar todas las opciones
                    if (esCorrecta) {
                        card.classList.add('correct');
                    } else {
                        card.classList.add('incorrect');
                    }

                    // Deshabilitar el input
                    if (input) {
                        input.disabled = true;
                    }

                    // Cambiar modo a 'resultado' para que al hacer clic muestre feedback
                    card.dataset.modo = 'resultado';
                });

                // Ocultar botón verificar
                if (btnVerificar) {
                    btnVerificar.style.display = 'none';
                }

                // Habilitar botón Siguiente
                const btnSiguiente = document.getElementById('btnSiguiente');
                if (btnSiguiente) {
                    btnSiguiente.classList.remove('opacity-50', 'pointer-events-none');
                    btnSiguiente.classList.add('hover:bg-green-700');
                    btnSiguiente.removeAttribute('data-disabled');
                    btnSiguiente.removeAttribute('title');
                }

                // Si es la última pregunta, actualizar botón finalizar
                const btnFinalizarDisabled = document.querySelector('button[disabled][title*="Debés responder"]');
                if (btnFinalizarDisabled) {
                    // Recargar para actualizar el estado del botón finalizar
                    window.recargarAlCerrarModal = true;
                }

            } else if (result.opciones_correctas) {
                // Respuesta incorrecta: solo marcar las seleccionadas
                opciones.forEach(card => {
                    const input = card.querySelector('input');
                    const letra = input.value;
                    const esCorrecta = result.opciones_correctas.includes(letra);
                    const fueSeleccionada = input.checked;

                    card.classList.remove('selected');

                    // Solo marcar visualmente las opciones que fueron seleccionadas
                    if (fueSeleccionada) {
                        if (esCorrecta) {
                            card.classList.add('correct');
                        } else {
                            card.classList.add('incorrect');
                        }
                    }
                });
            }
        }

        // Toggle de modo oscuro
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
            });
        }

        // Auto-save del progreso
        setInterval(() => {
            fetch('salvare.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_sessio: config.sessio })
            }).catch(() => {});
        }, 30000);

        // Modal de finalización
        const modal = document.getElementById('modalFinalizar');
        const btnFinalizar = document.getElementById('btnFinalizar');
        const btnConfirmar = document.getElementById('btnConfirmarFinalizar');

        function abrirModal() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            lucide.createIcons();
        }

        function cerrarModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function cerrarModalFeedback() {
            if (modalFeedback) {
                modalFeedback.classList.add('hidden');
                document.body.style.overflow = '';

                // Si es pregunta abierta y se guardó, o si es la última pregunta correcta, recargar para actualizar estado
                if (window.recargarAlCerrarModal) {
                    window.recargarAlCerrarModal = false;
                    // Recargar la misma página para actualizar botones
                    window.location.reload();
                    return;
                }

                // Si la respuesta fue INCORRECTA, limpiar colores para reintentar
                if (!ultimaRespuestaCorrecta) {
                    opciones.forEach(card => {
                        card.classList.remove('correct', 'incorrect', 'selected');
                        // Desmarcar los inputs
                        const input = card.querySelector('input');
                        if (input) input.checked = false;
                    });
                }
                // Si fue CORRECTA, los colores y estados ya se aplicaron en mostrarFeedback
            }
        }

        // Hacer la función disponible globalmente para el onclick
        window.cerrarModalFeedback = cerrarModalFeedback;

        // Botón "Ver devolución" para preguntas ya respondidas correctamente
        const btnVerDevolucion = document.getElementById('btnVerDevolucion');
        if (btnVerDevolucion) {
            btnVerDevolucion.addEventListener('click', function() {
                // Convertir saltos de línea a <br> para mostrar correctamente
                const feedbackRaw = this.dataset.feedback || '';
                const feedback = feedbackRaw.replace(/\\n/g, '<br>').replace(/\n/g, '<br>');

                feedbackHeader.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                            <i data-lucide="check-circle-2" class="w-7 h-7 text-green-600 dark:text-green-400"></i>
                        </div>
                        <div class="text-left">
                            <h3 class="text-lg font-bold text-green-800 dark:text-green-200">¡Respuesta Correcta!</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Ya respondiste esta pregunta correctamente</p>
                        </div>
                    </div>
                `;

                feedbackBody.innerHTML = `
                    <div class="text-left space-y-3">
                        ${feedback ? `
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"></i>
                                    <p class="text-sm text-blue-900 dark:text-blue-100">${feedback}</p>
                                </div>
                            </div>
                        ` : '<p class="text-gray-600 dark:text-gray-400">No hay devolución disponible para esta pregunta.</p>'}
                    </div>
                `;

                modalFeedback.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                lucide.createIcons();
            });
        }

        if (btnFinalizar) {
            btnFinalizar.addEventListener('click', abrirModal);
        }

        if (btnConfirmar) {
            btnConfirmar.addEventListener('click', function() {
                const url = btnFinalizar.getAttribute('data-url');
                window.location.href = url;
            });
        }

        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                cerrarModal();
            }
            // Modal instructivo
            const modalInst = document.getElementById('modalInstructivo');
            if (e.key === 'Escape' && modalInst && !modalInst.classList.contains('hidden')) {
                if (typeof window.cerrarModalInstructivo === 'function') {
                    window.cerrarModalInstructivo();
                }
            }
        });

    </script>

    <!-- Script para Modal de Instructivo (debe ir después del HTML del modal) -->
    <script>
        // Esperar a que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            const modalInstructivo = document.getElementById('modalInstructivo');
            const btnAyuda = document.getElementById('btnAyudaFlotante');
            const btnComenzar = document.getElementById('btnComenzarEvaluacion');

            if (!modalInstructivo) {
                console.error('Modal instructivo no encontrado');
                return;
            }

            window.abrirModalInstructivo = function() {
                modalInstructivo.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                lucide.createIcons();
            };

            window.cerrarModalInstructivo = function() {
                modalInstructivo.classList.add('hidden');
                document.body.style.overflow = '';
                // Guardar que ya vio el instructivo
                localStorage.setItem('probatio_instructivo_visto_' + <?= $id_sessio ?>, 'true');
            };

            // Mostrar instructivo al inicio si es la primera vez o si la sesión fue reseteada
            <?php if (!$modo_lectura): ?>
            const tieneRespuestas = <?php echo count($todas_respuestas) > 0 ? 'true' : 'false'; ?>;
            const yaVioInstructivo = localStorage.getItem('probatio_instructivo_visto_' + <?= $id_sessio ?>);

            // Mostrar si: nunca lo vio O si la sesión fue reseteada (no tiene respuestas)
            if (!yaVioInstructivo || !tieneRespuestas) {
                window.abrirModalInstructivo();
                // Si fue reseteada, limpiar el flag para que se guarde de nuevo al cerrar
                if (!tieneRespuestas) {
                    localStorage.removeItem('probatio_instructivo_visto_' + <?= $id_sessio ?>);
                }
            }
            <?php endif; ?>

            // Botón de ayuda flotante
            if (btnAyuda) {
                btnAyuda.addEventListener('click', window.abrirModalInstructivo);
            }

            // Botón comenzar
            if (btnComenzar) {
                btnComenzar.addEventListener('click', window.cerrarModalInstructivo);
            }
        });
    </script>

    <!-- Modal de Instructivo (optimizado para movil) -->
    <div id="modalInstructivo" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="min-h-screen px-2 sm:px-4 py-4 flex items-start sm:items-center justify-center">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" onclick="window.cerrarModalInstructivo()"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl sm:rounded-2xl shadow-2xl max-w-lg w-full mx-auto overflow-hidden my-auto">
                <!-- Header compacto -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3 sm:px-5 sm:py-4 text-white">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold">Instructivo</h2>
                            <p class="text-blue-100 text-xs sm:text-sm">Lee antes de comenzar</p>
                        </div>
                    </div>
                </div>

                <!-- Contenido compacto -->
                <div class="px-4 py-3 sm:px-5 sm:py-4 max-h-[65vh] overflow-y-auto">
                    <div class="space-y-3 sm:space-y-4">
                        <!-- Navegacion -->
                        <div class="flex gap-3 items-start">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Navegacion libre</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                                    Usa los <strong>numeros</strong> arriba o los botones para moverte entre preguntas.
                                </p>
                            </div>
                        </div>

                        <!-- Verificar -->
                        <div class="flex gap-3 items-start">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Verificar respuesta</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                                    Despues de seleccionar, toca <strong>Verificar</strong> para ver si es correcta.
                                </p>
                            </div>
                        </div>

                        <!-- Intentos -->
                        <div class="flex gap-3 items-start">
                            <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Multiples intentos</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                                    Si es incorrecta, podes <strong>reintentar</strong> hasta acertar.
                                </p>
                            </div>
                        </div>

                        <!-- Avanzar -->
                        <div class="flex gap-3 items-start">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Avanzar</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                                    Solo avanzas cuando respondes <strong>correctamente</strong>. Las correctas se ven en <span class="text-green-600 font-medium">verde</span>.
                                </p>
                            </div>
                        </div>

                        <!-- Finalizar -->
                        <div class="flex gap-3 items-start">
                            <div class="w-8 h-8 bg-rose-100 dark:bg-rose-900/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Finalizar</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                                    Al completar todo, toca <strong>Finalizar</strong> para guardar tu resultado.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer compacto -->
                <div class="px-4 py-3 sm:px-5 sm:py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-600">
                    <button id="btnComenzarEvaluacion" class="w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-500/25 text-sm sm:text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Comenzar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Boton de ayuda flotante (con SVG inline) -->
    <?php if (!$modo_lectura): ?>
    <button id="btnAyudaFlotante" class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 w-12 h-12 sm:w-14 sm:h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all flex items-center justify-center z-40" title="Ver instructivo">
        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </button>
    <?php endif; ?>
</body>
</html>
