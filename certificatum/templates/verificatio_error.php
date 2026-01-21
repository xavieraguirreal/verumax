<?php
/**
 * Template de Error Informativo para Verificación de Certificados
 * Muestra mensajes detallados según el motivo de la invalidez
 *
 * Variables disponibles:
 * - $diagnostico: array con 'valido', 'motivo', 'mensaje', 'datos'
 * - $nombre_inst: nombre de la institución
 * - $logo_url: URL del logo de la institución
 * - $institucion: slug de la institución
 */

// Configurar mensajes y estilos según el motivo
$config_error = [
    'curso_deshabilitado' => [
        'icono' => 'archive',
        'color' => 'amber',
        'titulo' => 'Curso Deshabilitado',
        'descripcion' => 'Este certificado corresponde a un curso que ha sido deshabilitado por la institución.'
    ],
    'inscripcion_cancelada' => [
        'icono' => 'user-x',
        'color' => 'orange',
        'titulo' => 'Inscripción Cancelada',
        'descripcion' => 'La inscripción del estudiante en este curso ha sido cancelada por la institución.'
    ],
    'estudiante_no_encontrado' => [
        'icono' => 'user-search',
        'color' => 'red',
        'titulo' => 'Estudiante No Registrado',
        'descripcion' => 'El estudiante asociado a este certificado no está registrado en el sistema.'
    ],
    'curso_no_encontrado' => [
        'icono' => 'book-x',
        'color' => 'red',
        'titulo' => 'Curso No Encontrado',
        'descripcion' => 'El curso asociado a este certificado no existe en el sistema.'
    ],
    'inscripcion_no_encontrada' => [
        'icono' => 'file-question',
        'color' => 'red',
        'titulo' => 'Inscripción No Encontrada',
        'descripcion' => 'No se encontró registro de inscripción para este estudiante en el curso indicado.'
    ],
    'error_sistema' => [
        'icono' => 'alert-triangle',
        'color' => 'red',
        'titulo' => 'Error del Sistema',
        'descripcion' => 'Ocurrió un error al verificar el certificado. Por favor, intente nuevamente.'
    ]
];

$motivo = $diagnostico['motivo'] ?? 'error_sistema';
$cfg = $config_error[$motivo] ?? $config_error['error_sistema'];

// Colores según el tipo de error
$colores = [
    'amber' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-500', 'text' => 'text-amber-800', 'icon_bg' => 'bg-amber-100', 'icon' => 'text-amber-600'],
    'orange' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-500', 'text' => 'text-orange-800', 'icon_bg' => 'bg-orange-100', 'icon' => 'text-orange-600'],
    'red' => ['bg' => 'bg-red-50', 'border' => 'border-red-500', 'text' => 'text-red-800', 'icon_bg' => 'bg-red-100', 'icon' => 'text-red-600']
];
$c = $colores[$cfg['color']] ?? $colores['red'];

$base_url = esSubdominioInstitucion() ? '/' : '../' . $institucion . '/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado No Válido - <?php echo htmlspecialchars($nombre_inst); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.4s ease-out; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo" class="h-10 w-auto">
                <span class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($nombre_inst); ?></span>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-8">
        <div class="max-w-2xl w-full fade-in">

            <!-- Card de Error -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

                <!-- Header del error -->
                <div class="<?php echo $c['bg']; ?> border-b-4 <?php echo $c['border']; ?> p-8 text-center">
                    <div class="<?php echo $c['icon_bg']; ?> w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="<?php echo $cfg['icono']; ?>" class="w-10 h-10 <?php echo $c['icon']; ?>"></i>
                    </div>
                    <h1 class="text-2xl font-bold <?php echo $c['text']; ?> mb-2"><?php echo $cfg['titulo']; ?></h1>
                    <p class="<?php echo $c['text']; ?> opacity-80"><?php echo $cfg['descripcion']; ?></p>
                </div>

                <!-- Detalles -->
                <div class="p-8">

                    <?php if (!empty($diagnostico['datos'])): ?>
                    <!-- Información del certificado -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Información del Documento</h3>

                        <?php if (!empty($diagnostico['datos']['nombre_estudiante'])): ?>
                        <div class="flex justify-between py-2 border-b border-gray-200">
                            <span class="text-gray-600">Estudiante</span>
                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars(mb_convert_case(strtolower($diagnostico['datos']['nombre_estudiante']), MB_CASE_TITLE, 'UTF-8')); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($diagnostico['datos']['nombre_curso'])): ?>
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600">Curso</span>
                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars($diagnostico['datos']['nombre_curso']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Mensaje específico según el motivo -->
                    <?php if ($motivo === 'curso_deshabilitado' || $motivo === 'inscripcion_cancelada'): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <div class="flex items-start">
                            <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Nota importante</p>
                                <p>Si considera que esto es un error, contacte directamente a <strong><?php echo htmlspecialchars($nombre_inst); ?></strong> para obtener más información sobre el estado de este documento.</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Acciones -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="javascript:history.back()"
                           class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Volver
                        </a>
                        <a href="<?php echo $base_url; ?>index.php"
                           class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                            Ir a <?php echo htmlspecialchars($nombre_inst); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer info -->
            <p class="text-center text-gray-500 text-sm mt-6">
                Sistema de Verificación de Certificados - Certificatum by VERUMax
            </p>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
