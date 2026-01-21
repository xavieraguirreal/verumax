<?php
/**
 * Página de Verificación de Certificados - CERTIFICATUM
 * Muestra la información del certificado validado
 * Versión: 2.4 - Inicialización consolidada, sin hardcodeos
 */

// Cargar inicialización común (validación whitelist, idioma, config)
require_once __DIR__ . '/init.php';
extract(initCertificatum());

use VERUMax\Services\StudentService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\LanguageService;

// Incluir autodetect para detectar si estamos en subdominio
require_once 'autodetect.php';

// 3. Obtener los demás parámetros de la URL
$dni = $_GET['documentum'] ?? null;
$curso_id = $_GET['cursus'] ?? null;
$participacion_id = $_GET['participacion'] ?? null;

// Detectar si el curso tiene formato de docente (CODIGO_docente_ID)
// Esto puede venir de QRs generados con el formato antiguo
if (!$participacion_id && $curso_id && preg_match('/^(.+)_docente_(\d+)$/', $curso_id, $matches)) {
    $curso_id = $matches[1];  // Código del curso real
    $participacion_id = $matches[2];  // ID de participación
}

// 4. Validar y buscar datos desde MySQL usando StudentService
$es_docente = !empty($participacion_id);

if ($es_docente) {
    // Buscar datos de participación docente
    $datos = StudentService::getParticipacionDocente($institucion, $dni, (int)$participacion_id);
    if ($datos) {
        $alumno = ['nombre_completo' => $datos['nombre_completo']];
        $participacion = $datos['participacion'];

        // Crear trayectoria básica para docentes
        $trayectoria_docente = [];

        // Evento de asignación
        $trayectoria_docente[] = [
            'evento' => 'Asignación al curso',
            'fecha' => $participacion['fecha_inicio'] ?? 'Fecha no especificada',
            'detalle' => 'Rol: ' . ucfirst($participacion['rol'] ?? 'Docente')
        ];

        // Estado actual según el estado de la participación
        $estado_participacion = $participacion['estado'] ?? 'Asignado';
        if ($estado_participacion === 'En curso') {
            $trayectoria_docente[] = [
                'evento' => 'Participación activa',
                'fecha' => 'En progreso',
                'detalle' => null
            ];
        } elseif ($estado_participacion === 'Completado') {
            $trayectoria_docente[] = [
                'evento' => 'Participación completada',
                'fecha' => $participacion['fecha_fin'] ?? 'Finalizado',
                'detalle' => 'Estado: Completado'
            ];
        } elseif ($estado_participacion === 'Asignado') {
            $trayectoria_docente[] = [
                'evento' => 'Curso por iniciar',
                'fecha' => 'Pendiente',
                'detalle' => 'Estado: Asignado'
            ];
        }

        $curso = [
            'nombre_curso' => $participacion['nombre_curso'] ?? 'Curso',
            'codigo_curso' => $participacion['codigo_curso'] ?? $curso_id,
            'fecha_inicio' => $participacion['fecha_inicio'] ?? null,
            'fecha_finalizacion' => $participacion['fecha_fin'] ?? null,
            'estado' => $participacion['estado'] ?? 'Completado',
            'trayectoria' => $trayectoria_docente
        ];
    }
} else {
    // Buscar datos de estudiante
    $datos = StudentService::getCourse($institucion, $dni, $curso_id);
    if ($datos) {
        $alumno = ['nombre_completo' => $datos['nombre_completo']];
        $curso = $datos['curso'];
    }
}

if (!$datos) {
    // Diagnosticar el motivo del error
    $diagnostico = StudentService::diagnosticarCertificado($institucion, $dni, $curso_id);

    // Obtener info de la institución para la página de error
    $instance_config = InstitutionService::getConfig($institucion);
    $nombre_inst = $instance_config['nombre'] ?? ucfirst($institucion);
    $logo_url = $instance_config['logo_url'] ?? 'https://placehold.co/80x80/dc2626/ffffff?text=!';

    // Mostrar página de error informativa
    include 'templates/verificatio_error.php';
    exit;
}

// Convertir nombre a formato título (primera letra mayúscula de cada palabra)
$nombre_alumno = htmlspecialchars(mb_convert_case(strtolower($alumno['nombre_completo']), MB_CASE_TITLE, 'UTF-8'));
$nombre_curso = htmlspecialchars($curso['nombre_curso']);

// Para docentes, determinar el rol con género
$tipo_persona = 'Estudiante';
if ($es_docente && isset($participacion)) {
    $rol_base = $participacion['rol'] ?? 'docente';
    $genero_docente = $datos['genero'] ?? 'Prefiero no especificar';

    // Roles que cambian con género
    $roles_con_genero = [
        'instructor' => 'Instruct',
        'orador' => 'Orad',
        'expositor' => 'Exposit',
        'facilitador' => 'Facilitad',
        'tutor' => 'Tut',
        'coordinador' => 'Coordinad'
    ];
    $roles_neutros = ['docente' => 'Docente', 'conferencista' => 'Conferencista'];

    if (isset($roles_con_genero[$rol_base])) {
        $tipo_persona = LanguageService::getGenderedText($genero_docente, $roles_con_genero[$rol_base], 'sufijo_or');
    } elseif (isset($roles_neutros[$rol_base])) {
        $tipo_persona = $roles_neutros[$rol_base];
    } else {
        $tipo_persona = ucfirst($rol_base);
    }
}

// Obtener configuración de la institución desde InstitutionService
$instance_config = InstitutionService::getConfig($institucion);
$nombre_inst = $instance_config['nombre'] ?? ucfirst($institucion);
$logo_url = $instance_config['logo_url'] ?? 'https://placehold.co/80x80/3b82f6/ffffff?text=' . strtoupper(substr($institucion, 0, 2));
$logo_estilo = $instance_config['logo_estilo'] ?? 'rectangular';
$logo_mostrar_texto = $instance_config['logo_mostrar_texto'] ?? 1;

// Clase CSS según estilo del logo
$logo_class = 'h-10 w-auto max-w-[120px]';
if ($logo_estilo === 'circular') {
    $logo_class = 'h-10 w-10 rounded-full';
} elseif ($logo_estilo === 'rectangular-rounded') {
    $logo_class = 'h-10 w-auto max-w-[120px] rounded-lg';
} elseif ($logo_estilo === 'rectangular') {
    $logo_class = 'h-10 w-auto max-w-[120px]';
}

// Determinar la URL base para enlaces (subdominio vs certificatum)
$base_url = esSubdominioInstitucion() ? '/' : '../' . $institucion . '/';

// Clases CSS dinámicas usando color de la institución (sin hardcodeos)
$color_hex = $instance_config['color_primario'] ?? '#2563eb';
$color_primary_text = 'text-[' . $color_hex . ']';
$color_dot = 'bg-[' . $color_hex . ']';

// 5. Definimos la variable para activar el modo de validación
$page_title = 'Documento Validado - ' . $nombre_inst;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <!-- CSS específico de la institución -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>style.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up { animation: fadeInUp 0.6s ease-out; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Header simplificado -->
    <header class="bg-white shadow-sm sticky top-0 z-20">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="<?php echo $base_url; ?>index.php" class="flex items-center gap-3">
                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo <?php echo $nombre_inst; ?>" class="<?php echo $logo_class; ?>">
                <?php if ($logo_mostrar_texto): ?>
                    <span class="text-xl font-bold text-gray-800"><?php echo $nombre_inst; ?></span>
                <?php endif; ?>
            </a>
        </nav>
    </header>

    <main>

<!-- Contenido específico de la página de validación -->
<div class="container mx-auto p-4 sm:p-6 lg:p-8 max-w-5xl min-h-[60vh]">

    <!-- Banner de Confirmación de Validación -->
    <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8 shadow-sm fade-in-up">
        <div class="flex">
            <div class="flex-shrink-0">
                <i data-lucide="check-circle" class="h-8 w-8 text-green-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-xl font-bold text-green-800 mb-2">✓ Documento Validado Correctamente</h3>
                <div class="text-sm text-green-700">
                    <p class="mb-3">La información que se muestra a continuación ha sido verificada en los registros de <strong><?php echo $nombre_inst; ?></strong> y confirma la autenticidad del documento presentado.</p>

                    <!-- ENLACE AÑADIDO A LA PORTADA INSTITUCIONAL -->
                    <a href="<?php echo $base_url; ?>index.php" class="inline-flex items-center gap-1 font-semibold text-green-800 hover:text-green-900 hover:underline">
                        Conocer más sobre la institución certificadora
                        <i data-lucide="arrow-right" class="inline w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Vista del Analítico (sin enlaces de portal) -->
    <div class="bg-white rounded-xl shadow-md fade-in-up" style="animation-delay: 0.2s;">
        <header class="bg-gray-50 rounded-t-xl p-6 flex justify-between items-center border-b">
            <div>
                <p class="text-sm font-semibold <?php echo $color_primary_text; ?>">TRAYECTORIA ACADÉMICA VERIFICADA</p>
                <h1 class="text-3xl font-bold text-gray-800 mt-1"><?php echo $nombre_curso; ?></h1>
                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($tipo_persona); ?>: <?php echo $nombre_alumno; ?> (DNI: <?php echo htmlspecialchars($dni); ?>)</p>
            </div>
            <a href="<?php echo $base_url; ?>index.php" title="Ir al portal de la institución">
                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo <?php echo $nombre_inst; ?>" class="transition-transform hover:scale-105 h-16 w-auto max-w-[160px] <?php echo ($logo_estilo === 'circular') ? 'rounded-full' : (($logo_estilo === 'rectangular-rounded') ? 'rounded-lg' : ''); ?>">
            </a>
        </header>

        <div class="p-6 grid md:grid-cols-3 gap-8">
            <!-- Columna Izquierda: Línea de Tiempo -->
            <div class="md:col-span-2">
                <div class="bg-white p-6 rounded-xl border h-full">
                    <h3 class="font-bold text-lg text-gray-800 mb-6 flex items-center gap-2">
                        <i data-lucide="activity" class="w-5 h-5 text-green-600"></i>
                        Línea de Tiempo del Curso
                    </h3>
                    <div class="relative pl-4 border-l-2 border-gray-200">
                         <?php foreach($curso['trayectoria'] as $item): ?>
                        <div class="mb-8 relative">
                            <div class="absolute -left-[23px] top-1 bg-white p-1 rounded-full">
                                <div class="w-4 h-4 <?php echo $color_dot; ?> rounded-full"></div>
                            </div>
                            <p class="font-semibold text-gray-700"><?php echo htmlspecialchars($item['evento']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['fecha'] ?? ''); ?></p>
                            <?php if(!empty($item['detalle'])): ?>
                                <p class="text-sm <?php echo $color_primary_text; ?> font-medium mt-1"><?php echo htmlspecialchars($item['detalle']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Resumen y Competencias -->
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-xl border">
                    <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                        <i data-lucide="bar-chart-2" class="w-5 h-5 text-blue-600"></i>
                        Resumen
                    </h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-600">Nota Final</dt><dd class="font-semibold text-gray-900"><?php echo htmlspecialchars($curso['nota_final']); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Asistencia</dt><dd class="font-semibold text-gray-900"><?php echo htmlspecialchars($curso['asistencia']); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Carga Horaria</dt><dd class="font-semibold text-gray-900"><?php echo htmlspecialchars($curso['carga_horaria']); ?> hs.</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Finalización</dt><dd class="font-semibold text-gray-900"><?php echo htmlspecialchars($curso['fecha_finalizacion']); ?></dd></div>
                    </dl>
                </div>
                <div class="bg-white p-6 rounded-xl border">
                    <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-5 h-5 text-green-600"></i>
                        Competencias Adquiridas
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($curso['competencias'] as $competencia): ?>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1.5 rounded-full"><?php echo htmlspecialchars($competencia); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="container mx-auto px-6 py-10">
            <div class="text-center text-gray-500 text-sm">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $nombre_inst; ?>. Todos los derechos reservados.</p>
                <p class="mt-2 text-xs">Sistema Certificatum - Credenciales Verificadas by VERUMax</p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
