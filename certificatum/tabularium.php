<?php
/**
 * Tabularium - Vista de Trayectoria Académica / Participación Docente
 * Sistema CERTIFICATUM - VERUMax
 * Versión: 4.0 - Rediseño visual completo
 */

// Cargar inicialización común (validación whitelist, idioma, config)
require_once __DIR__ . '/init.php';
extract(initCertificatum());

use VERUMax\Services\StudentService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;

$dni = $_GET['documentum'] ?? null;
$curso_id = $_GET['cursus'] ?? null;
$tipo = $_GET['tipo'] ?? 'estudiante';
$participacion_id = $_GET['participacion'] ?? null;

// 4. Obtener datos según tipo (estudiante o docente)
$es_vista_docente = ($tipo === 'docente' && $participacion_id);

if ($es_vista_docente) {
    // Vista de participación docente
    $datos = StudentService::getDocenteParticipacion($institucion, $dni, $participacion_id);
    if (!$datos) {
        header('Location: cursus.php?institutio=' . $institucion . '&documentum=' . urlencode($dni) . '&lang=' . urlencode($current_lang) . '&error=participacion_not_found');
        exit;
    }
    // Convertir nombre a formato título
    $nombre_alumno = htmlspecialchars(mb_convert_case(strtolower($datos['nombre_completo']), MB_CASE_TITLE, 'UTF-8'));
    $nombre_curso = htmlspecialchars($datos['participacion']['nombre_curso']);
    $participacion = $datos['participacion'];
    $rol_display = $participacion['rol_display'] ?? ucfirst($participacion['rol']);
} else {
    // Vista de estudiante (original)
    $datos = StudentService::getCourse($institucion, $dni, $curso_id);
    if (!$datos) {
        header('Location: cursus.php?institutio=' . $institucion . '&documentum=' . urlencode($dni) . '&lang=' . urlencode($current_lang) . '&error=course_not_found');
        exit;
    }
    $alumno = ['nombre_completo' => $datos['nombre_completo']];
    $curso = $datos['curso'];
    // Convertir nombre a formato título
    $nombre_alumno = htmlspecialchars(mb_convert_case(strtolower($alumno['nombre_completo']), MB_CASE_TITLE, 'UTF-8'));
    $nombre_curso = htmlspecialchars($curso['nombre_curso']);
}

// Obtener branding de la configuración
$nombre_inst = $instance_config['nombre'] ?? 'Institución';
$color_primario = $instance_config['color_primario'] ?? '#2E7D32';
$color_secundario = $instance_config['color_secundario'] ?? '#1B5E20';

// Calcular color más claro para fondos (15% opacidad)
$color_primario_light = $color_primario . '15';
$color_primario_medium = $color_primario . '25';

// URL de retorno a la lista de cursos (preservando idioma)
$cursus_url = 'cursus.php?institutio=' . urlencode($institucion) . '&documentum=' . urlencode($dni) . '&lang=' . urlencode($current_lang);

// Obtener iniciales para avatar
$nombre_parts = explode(' ', $nombre_alumno);
$iniciales = '';
if (count($nombre_parts) >= 2) {
    $iniciales = strtoupper(substr($nombre_parts[0], 0, 1) . substr($nombre_parts[count($nombre_parts)-1], 0, 1));
} else {
    $iniciales = strtoupper(substr($nombre_alumno, 0, 2));
}

// Determinar estado y progreso del curso (solo para estudiantes)
if (!$es_vista_docente) {
    $estado_curso = $curso['estado'] ?? 'En Curso';
    $progreso = 0;
    $estado_config = [
        'Aprobado' => ['progreso' => 100, 'color' => 'emerald', 'icono' => 'check-circle', 'texto' => $t('certificatum.status_approved', [], 'Aprobado')],
        'Finalizado' => ['progreso' => 100, 'color' => 'blue', 'icono' => 'flag', 'texto' => $t('certificatum.status_completed', [], 'Finalizado')],
        'En Curso' => ['progreso' => 60, 'color' => 'amber', 'icono' => 'play-circle', 'texto' => $t('certificatum.status_in_progress', [], 'En Curso')],
        'Inscrito' => ['progreso' => 30, 'color' => 'sky', 'icono' => 'user-check', 'texto' => $t('certificatum.status_enrolled', [], 'Inscrito')],
        'Por Iniciar' => ['progreso' => 10, 'color' => 'slate', 'icono' => 'clock', 'texto' => $t('certificatum.status_pending', [], 'Por Iniciar')],
        'Preinscrito' => ['progreso' => 5, 'color' => 'gray', 'icono' => 'edit', 'texto' => $t('certificatum.status_preregistered', [], 'Preinscrito')],
    ];
    $estado_info = $estado_config[$estado_curso] ?? $estado_config['En Curso'];
}

// Preparar variables para el header compartido
$instance = $instance_config;
$page_type = 'certificatum';
$page_title = $es_vista_docente
    ? 'Participación: ' . $nombre_curso
    : 'Analítico: ' . $nombre_curso;

// Incluir header compartido
include __DIR__ . '/../templates/shared/header.php';
?>

<!-- Botón Volver -->
<div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
    <div class="container mx-auto px-4 sm:px-6 py-3">
        <a href="<?php echo htmlspecialchars($cursus_url); ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all font-medium text-sm group">
            <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
            <span class="hidden sm:inline"><?php echo $t('certificatum.back_to_my_courses', [], 'Volver a mis cursos'); ?></span>
            <span class="sm:hidden"><?php echo $t('common.btn_back', [], 'Volver'); ?></span>
        </a>
    </div>
</div>

<!-- Contenido principal -->
<main class="min-h-[70vh] bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">

<?php if ($es_vista_docente): ?>
    <!-- ============================================== -->
    <!-- VISTA DE PARTICIPACIÓN DOCENTE -->
    <!-- ============================================== -->

    <!-- Hero Card Docente -->
    <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden mb-8 fade-in-up">
        <!-- Fondo decorativo -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full" style="background: <?php echo $color_primario; ?>;"></div>
            <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full" style="background: <?php echo $color_secundario; ?>;"></div>
        </div>

        <!-- Header con gradiente -->
        <div class="relative bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 p-8 sm:p-10">
            <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-2xl sm:text-3xl font-bold shadow-lg border-2 border-white/30">
                        <?php echo $iniciales; ?>
                    </div>
                </div>

                <!-- Info principal -->
                <div class="flex-1 text-white">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        <span class="px-4 py-1.5 bg-white/20 backdrop-blur-sm rounded-full text-xs font-bold uppercase tracking-wider">
                            <?php echo $t('certificatum.teacher_participation_badge', [], 'Participación Docente'); ?>
                        </span>
                        <span class="px-4 py-1.5 bg-white/30 backdrop-blur-sm rounded-full text-xs font-bold capitalize">
                            <?php echo htmlspecialchars($rol_display); ?>
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-3 leading-tight"><?php echo $nombre_curso; ?></h1>
                    <div class="flex flex-wrap items-center gap-4 text-white/90">
                        <div class="flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span class="font-medium"><?php echo $nombre_alumno; ?></span>
                        </div>
                        <div class="flex items-center gap-2 bg-white/10 px-3 py-1 rounded-lg">
                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                            <span class="font-mono text-sm">DNI: <?php echo htmlspecialchars($dni); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid de información docente -->
        <div class="relative p-6 sm:p-8 grid lg:grid-cols-3 gap-8">
            <!-- Columna Izquierda: Detalles -->
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-lg">
                        <i data-lucide="briefcase" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-bold text-2xl text-gray-900 dark:text-white"><?php echo $t('certificatum.participation_details', [], 'Detalles de la Participación'); ?></h3>
                </div>

                <?php if (!empty($participacion['descripcion'])): ?>
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 rounded-2xl p-6 border-l-4 border-purple-500">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-purple-600"></i>
                        <?php echo $t('certificatum.description', [], 'Descripción'); ?>
                    </h4>
                    <p class="text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($participacion['descripcion']); ?></p>
                </div>
                <?php endif; ?>

                <!-- Info cards grid -->
                <div class="grid sm:grid-cols-2 gap-4">
                    <?php if (!empty($participacion['carga_horaria_dictada']) || !empty($participacion['carga_horaria'])): ?>
                    <div class="group bg-white dark:bg-gray-700 p-5 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-600 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 text-sm"><?php echo $t('certificatum.workload', [], 'Carga Horaria'); ?></span>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($participacion['carga_horaria_dictada'] ?? $participacion['carga_horaria']); ?>
                            <span class="text-lg text-gray-500">hs</span>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($participacion['fecha_inicio'])): ?>
                    <div class="group bg-white dark:bg-gray-700 p-5 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-600 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                                <i data-lucide="calendar" class="w-5 h-5"></i>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 text-sm"><?php echo $t('certificatum.period', [], 'Período'); ?></span>
                        </div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($participacion['fecha_inicio']); ?>
                            <?php if (!empty($participacion['fecha_fin'])): ?>
                                <span class="text-gray-400 mx-2">→</span>
                                <?php echo htmlspecialchars($participacion['fecha_fin']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($participacion['nombre_cohorte'])): ?>
                    <div class="group bg-white dark:bg-gray-700 p-5 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-600 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 text-sm"><?php echo $t('certificatum.cohort', [], 'Cohorte'); ?></span>
                        </div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($participacion['nombre_cohorte']); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="group bg-white dark:bg-gray-700 p-5 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-600 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
                                <i data-lucide="award" class="w-5 h-5"></i>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 text-sm"><?php echo $t('certificatum.role', [], 'Rol'); ?></span>
                        </div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white capitalize"><?php echo htmlspecialchars($rol_display); ?></p>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Documentos -->
            <div class="space-y-6 slide-in-right">
                <div class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 p-6 rounded-2xl border border-purple-100 dark:border-gray-600">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2 rounded-xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white shadow-md">
                            <i data-lucide="folder-open" class="w-5 h-5"></i>
                        </div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white"><?php echo $t('certificatum.available_documents', [], 'Documentos'); ?></h3>
                    </div>

                    <?php
                        $creare_path = defined('PROXY_MODE') ? 'creare.php' : 'creare.php';
                        $pdf_path = defined('PROXY_MODE') ? 'creare_pdf.php' : 'creare_pdf.php';
                        $current_lang = LanguageService::getCurrentLang();
                        $base_url = $creare_path . "?institutio=" . $institucion . "&documentum=" . urlencode($dni) . "&participacion=" . urlencode($participacion_id) . "&lang=" . $current_lang;
                        $pdf_url_docente = $pdf_path . "?institutio=" . $institucion . "&documentum=" . urlencode($dni) . "&participacion=" . urlencode($participacion_id) . "&lang=" . $current_lang;
                        $estado_participacion = $participacion['estado'] ?? 'Asignado';
                    ?>

                    <div class="space-y-4">
                        <!-- Constancia de Participación -->
                        <div class="document-card bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-all">
                            <div class="p-4 flex items-center gap-4">
                                <div class="p-3 rounded-xl bg-purple-100 dark:bg-purple-900/30">
                                    <i data-lucide="file-check" class="w-6 h-6 text-purple-600 dark:text-purple-400"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white"><?php echo $t('certificatum.participation_constancy', [], 'Constancia'); ?></h4>
                                    <p class="text-xs text-gray-500"><?php echo $t('certificatum.provisional_document', [], 'Documento provisional'); ?></p>
                                </div>
                            </div>
                            <div class="px-4 pb-4 flex gap-2">
                                <a href="<?php echo $base_url; ?>&genus=testimonium_doctoris"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all text-sm font-medium">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <?php echo $t('certificatum.view', [], 'Ver'); ?>
                                </a>
                                <a href="<?php echo $pdf_url_docente; ?>&genus=testimonium_doctoris" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-white rounded-xl hover:opacity-90 transition-all text-sm font-medium shadow-md"
                                   style="background: linear-gradient(135deg, <?php echo $color_primario; ?>, <?php echo $color_secundario; ?>);">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    PDF
                                </a>
                            </div>
                        </div>

                        <?php if ($estado_participacion === 'Completado'): ?>
                        <!-- Certificado Disponible -->
                        <div class="document-card bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-gray-800 dark:to-gray-800 rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-all border-2 border-amber-200 dark:border-amber-600">
                            <div class="p-4 flex items-center gap-4">
                                <div class="p-3 rounded-xl bg-gradient-to-br from-amber-400 to-yellow-500 shadow-md">
                                    <i data-lucide="award" class="w-6 h-6 text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-gray-900 dark:text-white"><?php echo $t('certificatum.certificate', [], 'Certificado'); ?></h4>
                                        <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs rounded-full font-medium flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                                            <?php echo $t('certificatum.available', [], 'Disponible'); ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500"><?php echo $t('certificatum.official_document', [], 'Documento oficial'); ?></p>
                                </div>
                            </div>
                            <div class="px-4 pb-4 flex gap-2">
                                <a href="<?php echo $base_url; ?>&genus=certificatum_doctoris"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-100 dark:bg-gray-700 text-amber-800 dark:text-amber-400 rounded-xl hover:bg-amber-200 dark:hover:bg-gray-600 transition-all text-sm font-medium">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <?php echo $t('certificatum.view', [], 'Ver'); ?>
                                </a>
                                <a href="<?php echo $pdf_url_docente; ?>&genus=certificatum_doctoris" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-white rounded-xl hover:from-amber-600 hover:to-yellow-600 transition-all text-sm font-medium shadow-md">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    PDF
                                </a>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Certificado Pendiente -->
                        <div class="document-card bg-gray-50 dark:bg-gray-800 rounded-xl overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-600 opacity-60">
                            <div class="p-4 flex items-center gap-4">
                                <div class="p-3 rounded-xl bg-gray-200 dark:bg-gray-700">
                                    <i data-lucide="award" class="w-6 h-6 text-gray-400"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-medium text-gray-500 dark:text-gray-400"><?php echo $t('certificatum.certificate', [], 'Certificado'); ?></h4>
                                        <span class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-500 text-xs rounded-full font-medium flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <?php echo $t('certificatum.pending', [], 'Pendiente'); ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400"><?php echo $t('certificatum.available_when_completed', [], 'Disponible al completar'); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ============================================== -->
    <!-- VISTA DE ESTUDIANTE - REDISEÑO COMPLETO -->
    <!-- ============================================== -->

    <!-- Hero Card con Avatar y Progreso -->
    <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden mb-8 fade-in-up">
        <!-- Fondo decorativo -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full" style="background: <?php echo $color_primario; ?>;"></div>
            <div class="absolute -bottom-24 -left-24 w-72 h-72 rounded-full" style="background: <?php echo $color_secundario; ?>;"></div>
        </div>

        <!-- Header con gradiente institucional -->
        <div class="relative p-8 sm:p-10" style="background: linear-gradient(135deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 100%);">
            <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center">
                <!-- Avatar con iniciales -->
                <div class="flex-shrink-0">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-2xl sm:text-3xl font-bold shadow-lg border-2 border-white/30 hover:scale-105 transition-transform">
                        <?php echo $iniciales; ?>
                    </div>
                </div>

                <!-- Info principal -->
                <div class="flex-1 text-white">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        <span class="px-4 py-1.5 bg-white/20 backdrop-blur-sm rounded-full text-xs font-bold uppercase tracking-wider">
                            <?php echo $t('certificatum.academic_trajectory', [], 'Trayectoria Académica'); ?>
                        </span>
                        <!-- Badge de estado -->
                        <span class="px-4 py-1.5 bg-<?php echo $estado_info['color']; ?>-500/30 backdrop-blur-sm rounded-full text-xs font-bold flex items-center gap-1.5">
                            <i data-lucide="<?php echo $estado_info['icono']; ?>" class="w-3 h-3"></i>
                            <?php echo $estado_info['texto']; ?>
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold mb-3 leading-tight"><?php echo $nombre_curso; ?></h1>
                    <div class="flex flex-wrap items-center gap-4 text-white/90">
                        <div class="flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span class="font-medium"><?php echo $nombre_alumno; ?></span>
                        </div>
                        <div class="flex items-center gap-2 bg-white/10 px-3 py-1 rounded-lg">
                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                            <span class="font-mono text-sm">DNI: <?php echo htmlspecialchars($dni); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Indicador de progreso circular -->
                <div class="flex-shrink-0 hidden lg:block">
                    <div class="relative w-28 h-28">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-white/20" stroke-width="3" fill="none" stroke="currentColor"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="text-white progress-ring" stroke-width="3" fill="none" stroke="currentColor"
                                stroke-linecap="round"
                                stroke-dasharray="<?php echo $estado_info['progreso']; ?>, 100"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-white">
                            <span class="text-2xl font-bold"><?php echo $estado_info['progreso']; ?>%</span>
                            <span class="text-xs opacity-80"><?php echo $t('certificatum.progress', [], 'Progreso'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de progreso móvil -->
            <div class="lg:hidden mt-6">
                <div class="flex items-center justify-between text-white/80 text-sm mb-2">
                    <span><?php echo $t('certificatum.progress', [], 'Progreso'); ?></span>
                    <span class="font-bold"><?php echo $estado_info['progreso']; ?>%</span>
                </div>
                <div class="h-2 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full transition-all duration-1000 progress-bar"
                         style="width: <?php echo $estado_info['progreso']; ?>%;"></div>
                </div>
            </div>
        </div>

        <!-- Grid principal -->
        <div class="relative p-6 sm:p-8 grid lg:grid-cols-3 gap-8">

            <!-- Columna Izquierda: Timeline -->
            <div class="lg:col-span-2 space-y-6 slide-in-left">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 rounded-xl shadow-lg text-white" style="background: linear-gradient(135deg, <?php echo $color_primario; ?>, <?php echo $color_secundario; ?>);">
                        <i data-lucide="git-branch" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-2xl text-gray-900 dark:text-white"><?php echo $t('certificatum.course_timeline', [], 'Línea de Tiempo'); ?></h3>
                        <p class="text-sm text-gray-500"><?php echo $t('certificatum.timeline_subtitle', [], 'Tu recorrido en el curso'); ?></p>
                    </div>
                </div>

                <?php if (empty($curso['trayectoria'])): ?>
                <!-- Estado vacío de timeline -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                        <i data-lucide="calendar-clock" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h4 class="font-bold text-gray-700 dark:text-gray-300 mb-2"><?php echo $t('certificatum.no_events_yet', [], 'Sin eventos registrados'); ?></h4>
                    <p class="text-gray-500 dark:text-gray-400 text-sm"><?php echo $t('certificatum.events_will_appear', [], 'Los eventos del curso aparecerán aquí'); ?></p>
                </div>
                <?php else: ?>
                <!-- Timeline con eventos -->
                <div class="relative">
                    <!-- Línea vertical -->
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-transparent via-gray-200 dark:via-gray-600 to-transparent"></div>

                    <div class="space-y-6">
                        <?php
                        $delay = 0;
                        $iconos_eventos = [
                            'Inscripción' => 'user-plus',
                            'Inicio' => 'play',
                            'Examen' => 'file-text',
                            'Evaluación' => 'clipboard-check',
                            'Aprobación' => 'check-circle',
                            'Finalización' => 'flag',
                            'Certificación' => 'award',
                        ];

                        // Mapeo de eventos para traducción
                        $traducciones_eventos = [
                            'Inicio del curso' => 'event_inicio_del_curso',
                            'Fecha de finalización registrada' => 'event_fecha_finalizacion_registrada',
                            'Inscripción al curso' => 'event_inscripcion_al_curso',
                            'Finalización del curso' => 'event_finalizacion_del_curso',
                        ];

                        // Función para traducir evento
                        $traducirEvento = function($evento) use ($t, $traducciones_eventos) {
                            // Si hay una clave de traducción para este evento, usarla
                            if (isset($traducciones_eventos[$evento])) {
                                return $t('certificatum.' . $traducciones_eventos[$evento], [], $evento);
                            }
                            // Si no hay traducción específica, devolver el texto original
                            return $evento;
                        };

                        foreach($curso['trayectoria'] as $index => $item):
                            $delay += 100;
                            $is_last = ($index === count($curso['trayectoria']) - 1);
                            // Buscar icono según el evento
                            $icono = 'circle';
                            foreach ($iconos_eventos as $key => $icon) {
                                if (stripos($item['evento'], $key) !== false) {
                                    $icono = $icon;
                                    break;
                                }
                            }
                        ?>
                        <div class="timeline-item relative pl-16 fade-in-up" style="animation-delay: <?php echo $delay; ?>ms;">
                            <!-- Dot con icono -->
                            <div class="timeline-dot absolute left-0 top-0 w-12 h-12 rounded-xl flex items-center justify-center shadow-lg text-white transition-all duration-300"
                                 style="background: linear-gradient(135deg, <?php echo $color_primario; ?>, <?php echo $color_secundario; ?>);">
                                <i data-lucide="<?php echo $icono; ?>" class="w-5 h-5"></i>
                            </div>

                            <!-- Card del evento -->
                            <div class="bg-white dark:bg-gray-700 p-5 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-600 hover:-translate-y-1">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                                    <h4 class="font-bold text-gray-900 dark:text-white text-lg"><?php echo htmlspecialchars($traducirEvento($item['evento'])); ?></h4>
                                    <?php if(!empty($item['fecha'])): ?>
                                    <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 px-3 py-1.5 rounded-full">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        <?php echo htmlspecialchars($item['fecha']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <?php if(!empty($item['detalle'])): ?>
                                <div class="mt-3 flex items-start gap-3 p-3 rounded-xl" style="background-color: <?php echo $color_primario; ?>10;">
                                    <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5" style="color: <?php echo $color_primario; ?>;"></i>
                                    <p class="text-sm font-medium" style="color: <?php echo $color_primario; ?>;"><?php echo htmlspecialchars($item['detalle']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Columna Derecha: Resumen, Competencias, Documentos -->
            <div class="space-y-6 slide-in-right">

                <!-- Tarjeta de Resumen -->
                <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-700 p-6 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-600">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2 rounded-xl shadow-md text-white" style="background: linear-gradient(135deg, <?php echo $color_primario; ?>, <?php echo $color_secundario; ?>);">
                            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        </div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white"><?php echo $t('certificatum.summary', [], 'Resumen'); ?></h3>
                    </div>

                    <div class="space-y-4">
                        <!-- Nota Final -->
                        <div class="group p-4 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 hover:border-gray-200 dark:hover:border-gray-500 transition-all">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
                                        <i data-lucide="trophy" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm"><?php echo $t('certificatum.final_grade', [], 'Nota Final'); ?></span>
                                </div>
                                <?php if ($curso['nota_final'] && $curso['nota_final'] !== 'N/A'): ?>
                                <span class="text-2xl font-bold" style="color: <?php echo $color_primario; ?>;"><?php echo htmlspecialchars($curso['nota_final']); ?></span>
                                <?php else: ?>
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-600 text-gray-400 rounded-full text-sm"><?php echo $t('certificatum.pending', [], 'Pendiente'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Asistencia -->
                        <div class="group p-4 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 hover:border-gray-200 dark:hover:border-gray-500 transition-all">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                                        <i data-lucide="users" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm"><?php echo $t('certificatum.attendance', [], 'Asistencia'); ?></span>
                                </div>
                                <?php if ($curso['asistencia'] && $curso['asistencia'] !== 'N/A'): ?>
                                <span class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($curso['asistencia']); ?></span>
                                <?php else: ?>
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-600 text-gray-400 rounded-full text-sm"><?php echo $t('certificatum.pending', [], 'Pendiente'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Carga Horaria -->
                        <div class="group p-4 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 hover:border-gray-200 dark:hover:border-gray-500 transition-all">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
                                        <i data-lucide="clock" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm"><?php echo $t('certificatum.workload', [], 'Carga Horaria'); ?></span>
                                </div>
                                <span class="text-xl font-bold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($curso['carga_horaria']); ?>
                                    <span class="text-sm text-gray-500 font-normal"><?php echo $t('certificatum.hours_short', [], 'hs'); ?></span>
                                </span>
                            </div>
                        </div>

                        <!-- Fecha de Inicio -->
                        <div class="group p-4 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 hover:border-gray-200 dark:hover:border-gray-500 transition-all">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                                        <i data-lucide="calendar" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm"><?php echo $t('certificatum.start_date', [], 'Inicio'); ?></span>
                                </div>
                                <?php if (!empty($curso['fecha_inicio'])): ?>
                                <span class="text-base font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($curso['fecha_inicio']); ?></span>
                                <?php else: ?>
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-600 text-gray-400 rounded-full text-sm"><?php echo $t('certificatum.not_defined', [], 'No definido'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Finalización -->
                        <div class="group p-4 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600 hover:border-gray-200 dark:hover:border-gray-500 transition-all">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                                        <i data-lucide="calendar-check" class="w-4 h-4"></i>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm"><?php echo $t('certificatum.completion', [], 'Finalización'); ?></span>
                                </div>
                                <?php if ($curso['fecha_finalizacion'] && $curso['fecha_finalizacion'] !== 'N/A'): ?>
                                <span class="text-base font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($curso['fecha_finalizacion']); ?></span>
                                <?php else: ?>
                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-600 text-gray-400 rounded-full text-sm"><?php echo $t('certificatum.pending', [], 'Pendiente'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Competencias -->
                <div class="bg-gradient-to-br from-white to-emerald-50 dark:from-gray-800 dark:to-gray-700 p-6 rounded-2xl shadow-xl border border-emerald-100 dark:border-gray-600">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white"><?php echo $t('certificatum.competencies', [], 'Competencias'); ?></h3>
                    </div>

                    <?php if (empty($curso['competencias'])): ?>
                    <!-- Estado vacío -->
                    <div class="text-center py-6">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-600 flex items-center justify-center">
                            <i data-lucide="puzzle" class="w-6 h-6 text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm"><?php echo $t('certificatum.no_competencies', [], 'Sin competencias asignadas'); ?></p>
                    </div>
                    <?php else: ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($curso['competencias'] as $competencia):
                            $competencia_key = 'certificatum.competency_' . strtolower(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'], ['_', 'a', 'e', 'i', 'o', 'u', 'n'], $competencia));
                            $competencia_traducida = $t($competencia_key, [], $competencia);
                        ?>
                        <span class="inline-flex items-center gap-1.5 bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-400 text-sm font-medium px-4 py-2 rounded-full border-2 border-emerald-200 dark:border-emerald-600 hover:border-emerald-400 dark:hover:border-emerald-500 hover:shadow-md transition-all cursor-default">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <?php echo htmlspecialchars($competencia_traducida); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Documentos -->
                <div class="bg-gradient-to-br from-white to-indigo-50 dark:from-gray-800 dark:to-gray-700 p-6 rounded-2xl shadow-xl border border-indigo-100 dark:border-gray-600">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-md">
                            <i data-lucide="folder-open" class="w-5 h-5"></i>
                        </div>
                        <h3 class="font-bold text-xl text-gray-900 dark:text-white"><?php echo $t('certificatum.available_documents', [], 'Documentos'); ?></h3>
                    </div>

                    <?php
                        $creare_path = defined('PROXY_MODE') ? 'creare.php' : 'creare.php';
                        $pdf_path = defined('PROXY_MODE') ? 'creare_pdf.php' : 'creare_pdf.php';
                        $current_lang = LanguageService::getCurrentLang();
                        $base_url = $creare_path . "?institutio=" . $institucion . "&documentum=" . urlencode($dni) . "&cursus=" . urlencode($curso_id) . "&lang=" . $current_lang;
                        $pdf_url = $pdf_path . "?institutio=" . $institucion . "&documentum=" . urlencode($dni) . "&cursus=" . urlencode($curso_id) . "&lang=" . $current_lang;
                    ?>

                    <div class="space-y-3">
                        <!-- Analítico (siempre disponible) -->
                        <div class="document-card bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-all">
                            <div class="p-4 flex items-center gap-4">
                                <div class="p-3 rounded-xl" style="background-color: <?php echo $color_primario; ?>20;">
                                    <i data-lucide="file-text" class="w-6 h-6" style="color: <?php echo $color_primario; ?>;"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white"><?php echo $t('certificatum.analytical', [], 'Analítico'); ?></h4>
                                    <p class="text-xs text-gray-500"><?php echo $t('certificatum.complete_academic_record', [], 'Registro académico completo'); ?></p>
                                </div>
                            </div>
                            <div class="px-4 pb-4 flex gap-2">
                                <a href="<?php echo $base_url; ?>&genus=analyticum"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-500 transition-all text-sm font-medium">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <?php echo $t('certificatum.view', [], 'Ver'); ?>
                                </a>
                                <a href="<?php echo $pdf_url; ?>&genus=analyticum" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-white rounded-xl hover:opacity-90 transition-all text-sm font-medium shadow-md"
                                   style="background: linear-gradient(135deg, <?php echo $color_primario; ?>, <?php echo $color_secundario; ?>);">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    PDF
                                </a>
                            </div>
                        </div>

                        <?php
                        // Documentos según estado
                        // Calcular disponibilidad del certificado en tiempo real
                        $cert_bloqueado = false;
                        $cert_fecha_disponible = null;
                        if ($curso['estado'] == 'Aprobado' && !empty($curso['fecha_finalizacion_raw'])) {
                            $disponibilidad_cert = InstitutionService::calcularDisponibilidadCertificado(
                                $curso['fecha_finalizacion_raw'],
                                $curso,
                                $instance_config
                            );
                            if (!$disponibilidad_cert['disponible']) {
                                $cert_bloqueado = true;
                                $cert_fecha_disponible = $disponibilidad_cert['fecha_disponible']->format('d/m/Y H:i');
                            }
                        }

                        switch($curso['estado']):
                            case 'Aprobado':
                                if ($cert_bloqueado):
                        ?>
                        <!-- Certificado Bloqueado por demora -->
                        <div class="document-card bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 rounded-xl overflow-hidden shadow-md border-2 border-blue-200 dark:border-blue-600">
                            <div class="p-4 flex items-center gap-4">
                                <div class="p-3 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 shadow-md">
                                    <i data-lucide="clock" class="w-6 h-6 text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-gray-900 dark:text-white"><?php echo $t('certificatum.approval_certificate', [], 'Certificado'); ?></h4>
                                        <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs rounded-full font-medium flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <?php echo $t('certificatum.in_process', [], 'En proceso'); ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500"><?php echo $t('certificatum.available_on', [], 'Disponible el'); ?> <?php echo $cert_fecha_disponible; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Certificado de Aprobación Disponible -->
                        <div class="document-card bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-gray-700 dark:to-gray-700 rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-all border-2 border-amber-200 dark:border-amber-600">
                            <div class="p-4 flex items-center gap-4">
                                <div class="p-3 rounded-xl bg-gradient-to-br from-amber-400 to-yellow-500 shadow-md">
                                    <i data-lucide="award" class="w-6 h-6 text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-gray-900 dark:text-white"><?php echo $t('certificatum.approval_certificate', [], 'Certificado'); ?></h4>
                                        <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs rounded-full font-medium flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i>
                                            <?php echo $t('certificatum.available', [], 'Disponible'); ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500"><?php echo $t('certificatum.official_approval', [], 'Documento oficial de aprobación'); ?></p>
                                </div>
                            </div>
                            <div class="px-4 pb-4 flex gap-2">
                                <a href="<?php echo $base_url; ?>&genus=certificatum_approbationis"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-100 dark:bg-gray-600 text-amber-800 dark:text-amber-400 rounded-xl hover:bg-amber-200 dark:hover:bg-gray-500 transition-all text-sm font-medium">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <?php echo $t('certificatum.view', [], 'Ver'); ?>
                                </a>
                                <a href="<?php echo $pdf_url; ?>&genus=certificatum_approbationis" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-white rounded-xl hover:from-amber-600 hover:to-yellow-600 transition-all text-sm font-medium shadow-md">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    PDF
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php
                                break;

                            case 'En Curso':
                            case 'Inscrito':
                        ?>
                        <!-- Constancia de Alumno Regular -->
                        <div class="document-card bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-all">
                            <div class="p-4 flex items-center gap-4">
                                <div class="p-3 rounded-xl bg-blue-100 dark:bg-blue-900/30">
                                    <i data-lucide="file-check" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white"><?php echo $t('certificatum.regular_student_constancy', [], 'Constancia de Alumno Regular'); ?></h4>
                                    <p class="text-xs text-gray-500"><?php echo $t('certificatum.active_enrollment_proof', [], 'Comprobante de inscripción activa'); ?></p>
                                </div>
                            </div>
                            <div class="px-4 pb-4 flex gap-2">
                                <a href="<?php echo $base_url; ?>&genus=testimonium_regulare"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-500 transition-all text-sm font-medium">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <?php echo $t('certificatum.view', [], 'Ver'); ?>
                                </a>
                                <a href="<?php echo $pdf_url; ?>&genus=testimonium_regulare" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all text-sm font-medium shadow-md">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    PDF
                                </a>
                            </div>
                        </div>
                        <?php
                                break;

                            case 'Finalizado':
                        ?>
                        <!-- Constancia de Finalización -->
                        <div class="document-card bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-all">
                            <div class="p-4 flex items-center gap-4">
                                <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-900/30">
                                    <i data-lucide="file-check" class="w-6 h-6 text-slate-600 dark:text-slate-400"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white"><?php echo $t('certificatum.completion_constancy', [], 'Constancia de Finalización'); ?></h4>
                                    <p class="text-xs text-gray-500"><?php echo $t('certificatum.course_completed_proof', [], 'Comprobante de curso completado'); ?></p>
                                </div>
                            </div>
                            <div class="px-4 pb-4 flex gap-2">
                                <a href="<?php echo $base_url; ?>&genus=testimonium_completionis"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-500 transition-all text-sm font-medium">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <?php echo $t('certificatum.view', [], 'Ver'); ?>
                                </a>
                                <a href="<?php echo $pdf_url; ?>&genus=testimonium_completionis" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-600 text-white rounded-xl hover:bg-slate-700 transition-all text-sm font-medium shadow-md">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    PDF
                                </a>
                            </div>
                        </div>
                        <?php
                                break;

                            case 'Por Iniciar':
                            case 'Preinscrito':
                        ?>
                        <!-- Constancia de Inscripción -->
                        <div class="document-card bg-white dark:bg-gray-700 rounded-xl overflow-hidden shadow-md hover:shadow-lg transition-all">
                            <div class="p-4 flex items-center gap-4">
                                <div class="p-3 rounded-xl bg-sky-100 dark:bg-sky-900/30">
                                    <i data-lucide="file-plus" class="w-6 h-6 text-sky-600 dark:text-sky-400"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white"><?php echo $t('certificatum.enrollment_constancy', [], 'Constancia de Inscripción'); ?></h4>
                                    <p class="text-xs text-gray-500"><?php echo $t('certificatum.enrollment_proof', [], 'Comprobante de inscripción'); ?></p>
                                </div>
                            </div>
                            <div class="px-4 pb-4 flex gap-2">
                                <a href="<?php echo $base_url; ?>&genus=testimonium_inscriptionis"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-500 transition-all text-sm font-medium">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <?php echo $t('certificatum.view', [], 'Ver'); ?>
                                </a>
                                <a href="<?php echo $pdf_url; ?>&genus=testimonium_inscriptionis" target="_blank"
                                   class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-sky-600 text-white rounded-xl hover:bg-sky-700 transition-all text-sm font-medium shadow-md">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    PDF
                                </a>
                            </div>
                        </div>
                        <?php
                                break;
                        endswitch;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>
</div>
</main>

<!-- Estilos para animaciones y efectos -->
<style>
    /* Animaciones de entrada */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .slide-in-left {
        animation: slideInLeft 0.6s ease-out forwards;
    }

    .slide-in-right {
        animation: slideInRight 0.6s ease-out forwards;
        animation-delay: 0.2s;
        opacity: 0;
    }

    /* Efecto hover en timeline */
    .timeline-item:hover .timeline-dot {
        transform: scale(1.1);
        box-shadow: 0 0 0 8px rgba(34, 197, 94, 0.15);
    }

    /* Animación del anillo de progreso */
    .progress-ring {
        transition: stroke-dasharray 1s ease-out;
    }

    /* Animación de la barra de progreso */
    .progress-bar {
        animation: progressGrow 1.5s ease-out;
    }

    @keyframes progressGrow {
        from {
            width: 0%;
        }
    }

    /* Efectos en tarjetas de documentos */
    .document-card {
        transition: all 0.3s ease;
    }

    .document-card:hover {
        transform: translateY(-2px);
    }

    /* Efecto glassmorphism para badges */
    .backdrop-blur-sm {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    /* Transición suave para iconos */
    [data-lucide] {
        transition: transform 0.2s ease;
    }

    /* Hover en grupos */
    .group:hover [data-lucide] {
        transform: scale(1.1);
    }
</style>

<?php
// Incluir footer compartido
include __DIR__ . '/../templates/shared/footer.php';
?>
