<?php
/**
 * Certificadores - Punto de Entrada Principal
 * Generado automáticamente por VERUMax Super Admin
 *
 * Este archivo maneja la lógica de módulos:
 * - PRIORIDAD: Modo construcción → Muestra página de mantenimiento
 * - CASO 1: Solo Certificatum activo → Muestra portal de certificados
 * - CASO 2: Ningún módulo activo → Muestra página de construcción
 * - CASO 3: Identitas activo → Usa motor Identitas para landing page
 */

// Definir slug de esta instancia
$slug = 'certificadores';

// Incluir configuración core VERUMax
require_once __DIR__ . '/../verumax/config.php';

// Cargar servicio de idiomas
use VERUMax\Services\LanguageService;

// Obtener configuración de la instancia
$instance_config = getInstanceConfig($slug);

if (!$instance_config) {
    die('Error: Institución no configurada');
}

// Inicializar idioma
$lang_request = $_GET['lang'] ?? null;
LanguageService::init($slug, $lang_request);

// ============================================================================
// VERIFICACIÓN DE MODO CONSTRUCCIÓN
// ============================================================================

// Si sitio_en_construccion está activo, mostrar página de mantenimiento
if (!empty($instance_config['sitio_en_construccion'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($instance_config['nombre']); ?> - En Construcción</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>body { font-family: 'Inter', sans-serif; }</style>
    </head>
    <body class="bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 min-h-screen flex items-center justify-center p-6">
        <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl p-8 text-center border border-amber-100">
            <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-3">
                <?php echo htmlspecialchars($instance_config['nombre']); ?>
            </h1>
            <p class="text-lg text-amber-700 font-medium mb-4">Sitio en Construcción</p>
            <p class="text-gray-600 mb-6">
                Estamos trabajando para mejorar tu experiencia. Pronto estaremos de vuelta con novedades.
            </p>
            <div class="inline-flex items-center gap-2 text-sm text-gray-500 bg-gray-50 px-4 py-2 rounded-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Vuelve pronto</span>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================================
// LÓGICA DE MÓDULOS
// ============================================================================

// CASO 1: Identitas desactivado + Certificatum activo → Solo portal de certificados
if (!$instance_config['identitas_activo'] && $instance_config['modulo_certificatum']) {
    $instance = $instance_config;
    $certificatum_config = [
        'modo' => $instance_config['certificatum_modo'] ?? 'pagina',
        'titulo' => $instance_config['certificatum_titulo'] ?? 'Certificados',
    ];
    $template_path = __DIR__ . '/../certificatum/templates/solo.php';
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo '<h1>Portal de Certificados</h1>';
        echo '<p>Acceda a <a href="certificatum/">certificatum/</a></p>';
    }
    exit;
}

// CASO 2: Ambos módulos desactivados → Sitio en construcción
if (!$instance_config['identitas_activo'] && !$instance_config['modulo_certificatum']) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($instance_config['nombre']); ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">
                <?php echo htmlspecialchars($instance_config['nombre']); ?>
            </h1>
            <p class="text-gray-600 mb-6">
                Estamos trabajando en nuestro sitio web. Pronto tendremos novedades.
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// CASO 3: Identitas activo → Funciona normal
require_once __DIR__ . '/../identitas/identitas_engine.php';
$identitas = new IdentitasEngine($slug);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'enviar') {
    $resultado = $identitas->procesarContacto($_POST);
    header('Location: ?' . ($resultado['success'] ? 'enviado=1' : 'error=envio') . '#contacto');
    exit;
}

if (isset($_GET['page'])) {
    $identitas->renderPage($_GET['page']);
} else {
    $identitas->renderHome();
}
