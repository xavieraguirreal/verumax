<?php
/**
 * ADMIN UNIFICADO - Dashboard Principal
 * Panel con pestañas para todos los módulos activos
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['admin_verumax'])) {
    header('Location: login.php');
    exit;
}

$admin = $_SESSION['admin_verumax'];
$modulo_activo = $_GET['modulo'] ?? 'general'; // Por defecto General

// Validar que el módulo solicitado esté activo
// Módulos siempre disponibles: general, identitas, identitas_templates, actividad
$modulos_siempre_disponibles = ['general', 'identitas', 'identitas_templates', 'actividad'];
if (!in_array($modulo_activo, $modulos_siempre_disponibles) && !isset($admin['modulos'][$modulo_activo])) {
    $modulo_activo = 'general';
}

// ============================================================================
// INTERCEPTAR PETICIONES AJAX ANTES DE ENVIAR HTML
// ============================================================================

// GET AJAX (obtener_firma_base64, etc)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion'])) {
    $acciones_ajax_get = ['obtener_firma_base64', 'obtener_template_config'];
    if (in_array($_GET['accion'], $acciones_ajax_get)) {
        $modulo_file = __DIR__ . "/modulos/{$modulo_activo}.php";
        if (file_exists($modulo_file)) { include $modulo_file; }
        exit;
    }
}

// POST AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    // Lista de acciones que devuelven JSON (AJAX)
    $acciones_ajax = [
        'obtener_codigo_sugerido',
        'obtener_competencias',
        'verificar_email_disponible',
        'obtener_evaluacion_preguntas',
        'buscar_miembros_ajax',
        'buscar_cursos_ajax',
        'verificar_evaluacion_activa',
        'obtener_templates',
        'obtener_template_curso',
        'actualizar_template_config',
        'traducir_ia',
        'autocompletar_ia',
        'generar_stats_ia',
        'generar_combinado_ia',
        'generar_imagen_ia',
        // Credencialis
        'buscar_miembros_credencialis',
        'guardar_credencial',
        'quitar_credencial',
        'guardar_config_credencialis',
        'obtener_siguiente_numero',
        'importar_miembros_credencialis'
    ];

    if (in_array($_POST['accion'], $acciones_ajax)) {
        // Cargar el módulo sin HTML para procesar AJAX
        $modulo_file = __DIR__ . "/modulos/{$modulo_activo}.php";
        if (file_exists($modulo_file)) {
            // El módulo procesará la acción y hará exit
            include $modulo_file;
        }
        exit;
    }
}
// ============================================================================

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin - <?php echo htmlspecialchars($admin['nombre']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .module-content { display: none; }
        .module-content.active { display: block; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">VERUMax Admin</h1>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($admin['nombre']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="demos/"
                       class="text-sm text-green-600 hover:text-green-800 inline-flex items-center gap-1 transition"
                       title="Tutoriales Interactivos">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Tutoriales
                    </a>
                    <a href="manual.php"
                       class="text-sm text-purple-600 hover:text-purple-800 inline-flex items-center gap-1 transition"
                       title="Manual de Usuario">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        Manual
                    </a>
                    <a href="https://<?php echo htmlspecialchars($admin['slug']); ?>.verumax.com/" target="_blank"
                       class="text-sm text-blue-600 hover:text-blue-800 inline-flex items-center gap-1 transition">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        Ver sitio
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <div class="text-sm text-gray-600">
                        <i data-lucide="user" class="w-4 h-4 inline"></i>
                        <?php echo htmlspecialchars($admin['usuario']); ?>
                    </div>
                    <a href="logout.php" class="text-sm text-red-600 hover:text-red-800 inline-flex items-center gap-1 transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Salir
                    </a>
                </div>
            </div>

            <!-- Tabs de módulos -->
            <nav class="flex -mb-px overflow-x-auto">
                <!-- General (siempre visible) -->
                <a href="?modulo=general"
                   class="tab-button <?php echo $modulo_activo === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>
                          px-6 py-3 text-sm font-medium border-b-2 inline-flex items-center gap-2 transition whitespace-nowrap">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    General
                </a>

                <!-- Identitas (siempre visible) -->
                <a href="?modulo=identitas"
                   class="tab-button <?php echo $modulo_activo === 'identitas' || $modulo_activo === 'identitas_templates' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>
                          px-6 py-3 text-sm font-medium border-b-2 inline-flex items-center gap-2 transition whitespace-nowrap">
                    <i data-lucide="layout" class="w-4 h-4"></i>
                    Identitas
                </a>

                <!-- Certificatum -->
                <?php if ($admin['modulos']['certificatum']): ?>
                    <a href="?modulo=certificatum"
                       class="tab-button <?php echo $modulo_activo === 'certificatum' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>
                              px-6 py-3 text-sm font-medium border-b-2 inline-flex items-center gap-2 transition whitespace-nowrap">
                        <i data-lucide="award" class="w-4 h-4"></i>
                        Certificatum
                    </a>
                <?php endif; ?>

                <!-- Scripta -->
                <?php if ($admin['modulos']['scripta']): ?>
                    <a href="?modulo=scripta"
                       class="tab-button <?php echo $modulo_activo === 'scripta' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>
                              px-6 py-3 text-sm font-medium border-b-2 inline-flex items-center gap-2 transition whitespace-nowrap">
                        <i data-lucide="pen-tool" class="w-4 h-4"></i>
                        Scripta
                    </a>
                <?php endif; ?>

                <!-- Nexus -->
                <?php if ($admin['modulos']['nexus']): ?>
                    <a href="?modulo=nexus"
                       class="tab-button <?php echo $modulo_activo === 'nexus' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>
                              px-6 py-3 text-sm font-medium border-b-2 inline-flex items-center gap-2 transition whitespace-nowrap">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        Nexus
                    </a>
                <?php endif; ?>

                <!-- Credencialis -->
                <?php if ($admin['modulos']['credencialis'] ?? false): ?>
                    <a href="?modulo=credencialis"
                       class="tab-button <?php echo $modulo_activo === 'credencialis' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>
                              px-6 py-3 text-sm font-medium border-b-2 inline-flex items-center gap-2 transition whitespace-nowrap">
                        <i data-lucide="id-card" class="w-4 h-4"></i>
                        Credencialis
                    </a>
                <?php endif; ?>

                <!-- Actividad (siempre disponible) -->
                <a href="?modulo=actividad"
                   class="tab-button <?php echo $modulo_activo === 'actividad' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>
                          px-6 py-3 text-sm font-medium border-b-2 inline-flex items-center gap-2 transition whitespace-nowrap">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    Actividad
                </a>

                <!-- Más módulos futuros aquí -->
            </nav>
        </div>
    </header>

    <!-- Contenido del módulo -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">
        <?php
        // Cargar el módulo correspondiente
        $modulo_file = __DIR__ . "/modulos/{$modulo_activo}.php";

        if (file_exists($modulo_file)) {
            include $modulo_file;
        } else {
            ?>
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <i data-lucide="construction" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Módulo en construcción</h2>
                <p class="text-gray-600">
                    El módulo <strong><?php echo htmlspecialchars($modulo_activo); ?></strong>
                    estará disponible próximamente.
                </p>
            </div>
            <?php
        }
        ?>
    </main>

    <!-- Footer Admin -->
    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm text-center md:text-left">
                    &copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($admin['nombre']); ?>.
                    Todos los derechos reservados.
                </p>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span>Potenciado por</span>
                    <a href="https://verumax.com" target="_blank" class="font-semibold text-gray-600 hover:text-blue-600 transition-colors">
                        VERUMax
                    </a>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="shield-check" class="w-3 h-3"></i>
                        Panel de Administración
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
