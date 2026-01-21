<?php
/**
 * Cursus - Lista de Cursos del Estudiante
 * Sistema CERTIFICATUM - VERUMax
 * Versión: 3.2 - Inicialización consolidada
 */

// Cargar inicialización común (validación whitelist, idioma, config)
require_once __DIR__ . '/init.php';
extract(initCertificatum());

use VERUMax\Services\StudentService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;

$dni_ingresado = $_POST['documentum'] ?? $_GET['documentum'] ?? null;

// Asegurar que documentum esté en $_GET para que el selector de idioma lo preserve
if ($dni_ingresado && !isset($_GET['documentum'])) {
    $_GET['documentum'] = $dni_ingresado;
}

// 4. Obtener datos del estudiante usando StudentService
$alumno = StudentService::getCourses($institucion, $dni_ingresado);

if (!$alumno) {
    // Determinar URL de redirección según si estamos en PROXY_MODE
    if (defined('PROXY_MODE') && PROXY_MODE) {
        // En PROXY_MODE (subdominio), redirigir a la raíz con error
        $redirect_url = '/?error=not_found#certificados';
    } else {
        // Modo normal, redirigir a la carpeta de la institución
        $redirect_url = '../' . $institucion . '/index.php?error=not_found';
    }

    error_log('[CERTIFICATUM] DNI no encontrado: ' . ($dni_ingresado ?? 'N/A') . ' - Institución: ' . $institucion);
    header('Location: ' . $redirect_url);
    exit;
}

// Convertir nombre a formato título (primera letra mayúscula de cada palabra)
$nombre_alumno = htmlspecialchars(mb_convert_case(strtolower($alumno['nombre_completo']), MB_CASE_TITLE, 'UTF-8'));
$cursos_alumno = $alumno['cursos'] ?? [];
$participaciones_docente = $alumno['participaciones_docente'] ?? [];
$es_estudiante = $alumno['es_estudiante'] ?? false;
$es_docente = $alumno['es_docente'] ?? false;

// Obtener branding de la configuración
$nombre_inst = $instance_config['nombre'] ?? 'Institución';
$color_primario = $instance_config['color_primario'] ?? '#2E7D32';

// URL del portal (home de identitas o certificatum solo)
$portal_url = defined('PROXY_MODE') && PROXY_MODE ? '/' : 'https://' . $institucion . '.verumax.com/';

// Preparar variables para el header compartido
$instance = $instance_config;
$page_type = 'certificatum';
$page_title = 'Mis Cursos - ' . $nombre_alumno;

// Incluir header compartido
include __DIR__ . '/../templates/shared/header.php';
?>

<!-- Contenido de la página de Cursos -->
<main>
<div class="container mx-auto p-4 sm:px-6 lg:px-8 max-w-6xl min-h-[60vh] py-8">

    <!-- Header mejorado con gradiente -->
    <div class="mb-8 fade-in-up">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transition-colors duration-300">
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-700 dark:to-gray-700 p-8 transition-colors duration-300">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-md"
                         style="background-color: <?php echo htmlspecialchars($color_primario); ?>;">
                        <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white"><?php echo $t('certificatum.my_courses_title', [], 'Mis Cursos'); ?></h1>
                        <p class="text-lg text-gray-600 dark:text-gray-300 mt-1"><?php echo $nombre_alumno; ?></p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-4">
                    <?php if ($es_estudiante): ?>
                    <div class="bg-white dark:bg-gray-600 px-4 py-2 rounded-lg shadow-sm transition-colors duration-300">
                        <p class="text-xs text-gray-500 dark:text-gray-300"><?php echo $t('certificatum.courses_as_student_count', [], 'Cursos como estudiante'); ?></p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo count($cursos_alumno); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php
                    $aprobados = 0;
                    $en_curso = 0;
                    foreach ($cursos_alumno as $c) {
                        if ($c['estado'] == 'Aprobado') $aprobados++;
                        if ($c['estado'] == 'En Curso') $en_curso++;
                    }
                    ?>
                    <?php if ($aprobados > 0): ?>
                    <div class="bg-white dark:bg-gray-600 px-4 py-2 rounded-lg shadow-sm transition-colors duration-300">
                        <p class="text-xs text-gray-500 dark:text-gray-300"><?php echo $t('certificatum.approved_count', [], 'Aprobados'); ?></p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo $aprobados; ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($en_curso > 0): ?>
                    <div class="bg-white dark:bg-gray-600 px-4 py-2 rounded-lg shadow-sm transition-colors duration-300">
                        <p class="text-xs text-gray-500 dark:text-gray-300"><?php echo $t('certificatum.in_progress_count', [], 'En curso'); ?></p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo $en_curso; ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($es_docente && count($participaciones_docente) > 0): ?>
                    <div class="bg-white dark:bg-gray-600 px-4 py-2 rounded-lg shadow-sm transition-colors duration-300">
                        <p class="text-xs text-gray-500 dark:text-gray-300"><?php echo $t('certificatum.as_teacher_count', [], 'Como docente/instructor'); ?></p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400"><?php echo count($participaciones_docente); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Cursos como Estudiante -->
    <?php if ($es_estudiante && !empty($cursos_alumno)): ?>
    <div class="mb-8 fade-in-up" style="animation-delay: 100ms;">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i data-lucide="graduation-cap" class="w-6 h-6" style="color: <?php echo htmlspecialchars($color_primario); ?>;"></i>
            <?php echo $t('certificatum.my_courses_as_student', [], 'Mis Cursos como Estudiante'); ?>
        </h2>
    </div>
    <?php endif; ?>

    <!-- Lista de cursos modernizada -->
    <div class="grid md:grid-cols-2 gap-6">
        <?php if (empty($cursos_alumno) && empty($participaciones_docente)): ?>
            <div class="col-span-2 bg-white dark:bg-gray-800 p-12 rounded-2xl shadow-lg text-center fade-in-up">
                <i data-lucide="inbox" class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4"></i>
                <p class="text-gray-600 dark:text-gray-400 text-lg"><?php echo $t('certificatum.no_courses_message', [], 'No tienes cursos asociados a tu DNI.'); ?></p>
            </div>
        <?php elseif (empty($cursos_alumno) && !empty($participaciones_docente)): ?>
            <!-- No mostrar nada aquí, los cursos de docente se muestran abajo -->
        <?php else: ?>
            <?php
            $delay = 0;
            foreach ($cursos_alumno as $id_curso => $curso):
                $estado_texto = htmlspecialchars($curso['estado']);

                // Determinar colores e iconos según estado
                if ($estado_texto == 'Aprobado') {
                    $badge_class = 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-700';
                    $icon = 'check-circle';
                    $icon_color = 'text-green-600 dark:text-green-400';
                    $border_color = 'border-l-green-500';
                } elseif ($estado_texto == 'En Curso') {
                    $badge_class = 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-700';
                    $icon = 'play-circle';
                    $icon_color = 'text-blue-600 dark:text-blue-400';
                    $border_color = 'border-l-blue-500';
                } elseif ($estado_texto == 'Por Iniciar') {
                    $badge_class = 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-700';
                    $icon = 'clock';
                    $icon_color = 'text-yellow-600 dark:text-yellow-400';
                    $border_color = 'border-l-yellow-500';
                } elseif ($estado_texto == 'Finalizado') {
                    $badge_class = 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-700';
                    $icon = 'check-circle';
                    $icon_color = 'text-emerald-600 dark:text-emerald-400';
                    $border_color = 'border-l-emerald-500';
                } else {
                    $badge_class = 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600';
                    $icon = 'file-text';
                    $icon_color = 'text-gray-600 dark:text-gray-400';
                    $border_color = 'border-l-gray-500';
                }

                $delay += 100;
            ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 border-l-4 <?php echo $border_color; ?> fade-in-up" style="animation-delay: <?php echo $delay; ?>ms;">
                    <div class="p-6">
                        <!-- Header del curso -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-start gap-3">
                                <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded-lg transition-colors duration-300">
                                    <i data-lucide="book-open" class="w-6 h-6 <?php echo $icon_color; ?>"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900 dark:text-white leading-tight"><?php echo htmlspecialchars($curso['nombre_curso']); ?></h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-mono"><?php echo htmlspecialchars($id_curso); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Badge de estado -->
                        <div class="mb-4">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-full border <?php echo $badge_class; ?>">
                                <i data-lucide="<?php echo $icon; ?>" class="w-4 h-4"></i>
                                <?php echo $estado_texto == 'Por Iniciar' ? 'Inicia el ' . htmlspecialchars($curso['fecha_inicio']) : $estado_texto; ?>
                            </span>
                        </div>

                        <!-- Información adicional -->
                        <div class="grid grid-cols-2 gap-4 mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <?php if (isset($curso['carga_horaria'])): ?>
                            <div class="flex items-center gap-2">
                                <i data-lucide="clock" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo $t('certificatum.workload', [], 'Carga horaria'); ?></p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($curso['carga_horaria']); ?> <?php echo $t('certificatum.hours_short', [], 'hs'); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (isset($curso['nota_final']) && $curso['nota_final'] != 'N/A'): ?>
                            <div class="flex items-center gap-2">
                                <i data-lucide="award" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo $t('certificatum.final_grade', [], 'Nota final'); ?></p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($curso['nota_final']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (isset($curso['asistencia']) && $curso['asistencia'] != 'N/A'): ?>
                            <div class="flex items-center gap-2">
                                <i data-lucide="users" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo $t('certificatum.attendance', [], 'Asistencia'); ?></p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($curso['asistencia']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if (isset($curso['fecha_finalizacion']) && $curso['fecha_finalizacion'] != 'N/A' && $curso['fecha_finalizacion'] != 'En curso'): ?>
                            <div class="flex items-center gap-2">
                                <i data-lucide="calendar-check" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo $t('certificatum.completion', [], 'Finalización'); ?></p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($curso['fecha_finalizacion']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Botones de acción -->
                        <div class="space-y-2">
                            <!-- Ver trayectoria -->
                            <?php
                            $tabularium_path = defined('PROXY_MODE') ? 'tabularium.php' : 'tabularium.php';
                            $creare_path = defined('PROXY_MODE') ? 'creare.php' : 'creare.php';
                            $current_lang = LanguageService::getCurrentLang();
                            ?>
                            <a href="<?php echo $tabularium_path; ?>?institutio=<?php echo $institucion; ?>&documentum=<?php echo urlencode($dni_ingresado); ?>&cursus=<?php echo urlencode($id_curso); ?>&lang=<?php echo $current_lang; ?>"
                               class="block w-full text-center px-6 py-3 text-sm font-bold text-white rounded-lg transition-all duration-300 hover:scale-105 inline-flex items-center justify-center gap-2 group"
                               style="background-color: <?php echo htmlspecialchars($color_primario); ?>;">
                                <span><?php echo $t('certificatum.view_full_trajectory', [], 'Ver Trayectoria Completa'); ?></span>
                                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                            </a>

                            <!-- Botones PDF -->
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Analítico PDF -->
                                <a href="<?php echo $creare_path; ?>?institutio=<?php echo $institucion; ?>&documentum=<?php echo urlencode($dni_ingresado); ?>&cursus=<?php echo urlencode($id_curso); ?>&genus=analyticum&lang=<?php echo $current_lang; ?>"
                                   class="flex items-center justify-center gap-1 px-3 py-2 text-xs font-semibold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300 group">
                                    <i data-lucide="file-text" class="w-3 h-3"></i>
                                    <span><?php echo $t('certificatum.pdf_analytical', [], 'PDF Analítico'); ?></span>
                                </a>

                                <!-- Certificado PDF (solo si está aprobado) o Constancia (si está finalizado) -->
                                <?php
                                // Calcular disponibilidad en tiempo real basado en config actual
                                $certificado_bloqueado = false;
                                $ts_disponibilidad = null;
                                $fecha_disponibilidad_obj = null;

                                if ($estado_texto == 'Aprobado' && !empty($curso['fecha_finalizacion_raw'])) {
                                    $disponibilidad = InstitutionService::calcularDisponibilidadCertificado(
                                        $curso['fecha_finalizacion_raw'],
                                        $curso,
                                        $instance_config
                                    );

                                    if (!$disponibilidad['disponible']) {
                                        $certificado_bloqueado = true;
                                        $fecha_disponibilidad_obj = $disponibilidad['fecha_disponible'];
                                        $ts_disponibilidad = $fecha_disponibilidad_obj->getTimestamp();
                                    }
                                }
                                ?>

                                <?php if ($estado_texto == 'Aprobado' && !$certificado_bloqueado): ?>
                                <a href="<?php echo $creare_path; ?>?institutio=<?php echo $institucion; ?>&documentum=<?php echo urlencode($dni_ingresado); ?>&cursus=<?php echo urlencode($id_curso); ?>&genus=certificatum_approbationis&lang=<?php echo $current_lang; ?>"
                                   class="flex items-center justify-center gap-1 px-3 py-2 text-xs font-semibold text-white bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg hover:from-yellow-600 hover:to-orange-600 transition-all duration-300 group">
                                    <i data-lucide="award" class="w-3 h-3"></i>
                                    <span><?php echo $t('certificatum.certificate', [], 'Certificado'); ?></span>
                                </a>
                                <?php elseif ($estado_texto == 'Aprobado' && $certificado_bloqueado): ?>
                                <!-- Certificado bloqueado - mostrar fecha de disponibilidad -->
                                <?php
                                    $fecha_disp_formateada = $fecha_disponibilidad_obj->format('d/m/Y');
                                ?>
                                <div class="flex flex-col items-center justify-center px-3 py-2 text-xs bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-lg cursor-not-allowed"
                                     title="Disponible el <?php echo $fecha_disp_formateada; ?>">
                                    <div class="flex items-center gap-1 mb-1">
                                        <i data-lucide="lock" class="w-3 h-3"></i>
                                        <span class="font-semibold">Certificado</span>
                                    </div>
                                    <span class="text-xs">
                                        Disponible el <?php echo $fecha_disp_formateada; ?>
                                    </span>
                                </div>
                                <?php elseif ($estado_texto == 'Finalizado'): ?>
                                <a href="<?php echo $creare_path; ?>?institutio=<?php echo $institucion; ?>&documentum=<?php echo urlencode($dni_ingresado); ?>&cursus=<?php echo urlencode($id_curso); ?>&genus=certificatum_completionis&lang=<?php echo $current_lang; ?>"
                                   class="flex items-center justify-center gap-1 px-3 py-2 text-xs font-semibold text-white bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg hover:from-yellow-600 hover:to-orange-600 transition-all duration-300 group">
                                    <i data-lucide="award" class="w-3 h-3"></i>
                                    <span><?php echo $t('certificatum.completion_certificate', [], 'Certificado'); ?></span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Sección de Participaciones como Docente/Instructor -->
    <?php if ($es_docente && !empty($participaciones_docente)): ?>
    <div class="mt-12 mb-8 fade-in-up">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i data-lucide="user-check" class="w-6 h-6 text-purple-600 dark:text-purple-400"></i>
            Mis Participaciones como Docente/Instructor
        </h2>
        <p class="text-gray-600 dark:text-gray-400">Cursos en los que participaste como docente, instructor, orador u otro rol.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <?php
        $delay_docente = 0;
        foreach ($participaciones_docente as $key => $participacion):
            $rol_display = htmlspecialchars($participacion['rol_display'] ?? ucfirst($participacion['rol']));

            // Colores según rol
            $rol_colors = [
                'docente' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-800 dark:text-purple-400', 'border' => 'border-purple-200 dark:border-purple-700', 'border_l' => 'border-l-purple-500'],
                'instructor' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-800 dark:text-blue-400', 'border' => 'border-blue-200 dark:border-blue-700', 'border_l' => 'border-l-blue-500'],
                'orador' => ['bg' => 'bg-orange-100 dark:bg-orange-900/30', 'text' => 'text-orange-800 dark:text-orange-400', 'border' => 'border-orange-200 dark:border-orange-700', 'border_l' => 'border-l-orange-500'],
                'expositor' => ['bg' => 'bg-cyan-100 dark:bg-cyan-900/30', 'text' => 'text-cyan-800 dark:text-cyan-400', 'border' => 'border-cyan-200 dark:border-cyan-700', 'border_l' => 'border-l-cyan-500'],
                'conferencista' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-800 dark:text-amber-400', 'border' => 'border-amber-200 dark:border-amber-700', 'border_l' => 'border-l-amber-500'],
                'facilitador' => ['bg' => 'bg-teal-100 dark:bg-teal-900/30', 'text' => 'text-teal-800 dark:text-teal-400', 'border' => 'border-teal-200 dark:border-teal-700', 'border_l' => 'border-l-teal-500'],
                'tutor' => ['bg' => 'bg-indigo-100 dark:bg-indigo-900/30', 'text' => 'text-indigo-800 dark:text-indigo-400', 'border' => 'border-indigo-200 dark:border-indigo-700', 'border_l' => 'border-l-indigo-500'],
                'coordinador' => ['bg' => 'bg-rose-100 dark:bg-rose-900/30', 'text' => 'text-rose-800 dark:text-rose-400', 'border' => 'border-rose-200 dark:border-rose-700', 'border_l' => 'border-l-rose-500'],
            ];
            $colors = $rol_colors[$participacion['rol']] ?? $rol_colors['docente'];

            $delay_docente += 100;
        ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 border-l-4 <?php echo $colors['border_l']; ?> fade-in-up" style="animation-delay: <?php echo $delay_docente; ?>ms;">
                <div class="p-6">
                    <!-- Header del curso -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-start gap-3">
                            <div class="bg-purple-100 dark:bg-purple-900/30 p-2 rounded-lg transition-colors duration-300">
                                <i data-lucide="presentation" class="w-6 h-6 text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white leading-tight"><?php echo htmlspecialchars($participacion['nombre_curso']); ?></h2>
                                <?php if (!empty($participacion['titulo_participacion'])): ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1"><?php echo htmlspecialchars($participacion['titulo_participacion']); ?></p>
                                <?php endif; ?>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-mono"><?php echo htmlspecialchars($participacion['codigo_curso']); ?><?php echo !empty($participacion['cohorte']) ? ' - ' . htmlspecialchars($participacion['cohorte']) : ''; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Badge de rol -->
                    <div class="mb-4">
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold rounded-full border <?php echo $colors['bg'] . ' ' . $colors['text'] . ' ' . $colors['border']; ?>">
                            <i data-lucide="award" class="w-4 h-4"></i>
                            <?php echo $rol_display; ?>
                        </span>
                    </div>

                    <!-- Información adicional -->
                    <div class="grid grid-cols-2 gap-4 mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <?php if (!empty($participacion['carga_horaria_dictada'])): ?>
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Horas dictadas</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($participacion['carga_horaria_dictada']); ?> hs</p>
                            </div>
                        </div>
                        <?php elseif (!empty($participacion['carga_horaria'])): ?>
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Carga horaria</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($participacion['carga_horaria']); ?> hs</p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($participacion['fecha_inicio'])): ?>
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Período</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($participacion['fecha_inicio']); ?>
                                    <?php if (!empty($participacion['fecha_fin'])): ?>
                                        - <?php echo htmlspecialchars($participacion['fecha_fin']); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($participacion['nombre_cohorte'])): ?>
                        <div class="flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Cohorte</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($participacion['nombre_cohorte']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($participacion['descripcion'])): ?>
                    <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($participacion['descripcion']); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Botones de acción para docente (similar a estudiantes) -->
                    <div class="space-y-2">
                        <?php
                        $id_participacion = $participacion['id_participacion'];
                        ?>
                        <!-- Ver trayectoria como docente -->
                        <a href="<?php echo $tabularium_path; ?>?institutio=<?php echo $institucion; ?>&documentum=<?php echo urlencode($dni_ingresado); ?>&participacion=<?php echo urlencode($id_participacion); ?>&tipo=docente&lang=<?php echo $current_lang; ?>"
                           class="block w-full text-center px-6 py-3 text-sm font-bold text-white rounded-lg transition-all duration-300 hover:scale-105 inline-flex items-center justify-center gap-2 group"
                           style="background-color: <?php echo htmlspecialchars($color_primario); ?>;">
                            <span><?php echo $t('certificatum.view_full_participation', [], 'Ver Participación Completa'); ?></span>
                            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                        </a>

                        <!-- Botones PDF -->
                        <?php $estado_part = $participacion['estado'] ?? 'Asignado'; ?>
                        <div class="grid <?php echo $estado_part === 'Completado' ? 'grid-cols-2' : 'grid-cols-1'; ?> gap-2">
                            <!-- Constancia de participación PDF -->
                            <a href="<?php echo $creare_path; ?>?institutio=<?php echo $institucion; ?>&documentum=<?php echo urlencode($dni_ingresado); ?>&participacion=<?php echo urlencode($id_participacion); ?>&genus=testimonium_doctoris&lang=<?php echo $current_lang; ?>"
                               class="flex items-center justify-center gap-1 px-3 py-2 text-xs font-semibold text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300 group">
                                <i data-lucide="file-text" class="w-3 h-3"></i>
                                <span><?php echo $t('certificatum.constancy', [], 'Constancia'); ?></span>
                            </a>

                            <?php if ($estado_part === 'Completado'): ?>
                            <!-- Certificado de docente (solo si completó) -->
                            <a href="<?php echo $creare_path; ?>?institutio=<?php echo $institucion; ?>&documentum=<?php echo urlencode($dni_ingresado); ?>&participacion=<?php echo urlencode($id_participacion); ?>&genus=certificatum_doctoris&lang=<?php echo $current_lang; ?>"
                               class="flex items-center justify-center gap-1 px-3 py-2 text-xs font-semibold text-white bg-gradient-to-r from-purple-500 to-indigo-500 rounded-lg hover:from-purple-600 hover:to-indigo-600 transition-all duration-300 group">
                                <i data-lucide="award" class="w-3 h-3"></i>
                                <span><?php echo $t('certificatum.certificate', [], 'Certificado'); ?></span>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
</main>

<?php
// Incluir footer compartido
include __DIR__ . '/../templates/shared/footer.php';
?>
