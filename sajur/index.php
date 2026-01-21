<?php
/**
 * SAJuR - Punto de Entrada Principal
 * Versión: 3.0
 *
 * Este archivo maneja la lógica de módulos para SAJuR:
 * - CASO 1: Solo Certificatum activo → Muestra portal de certificados
 * - CASO 2: Ningún módulo activo → Muestra página de construcción
 * - CASO 3: Identitas activo → Usa motor Identitas para landing page
 */

// Definir slug de esta instancia
$slug = 'sajur';

// Incluir configuración core VERUMax
// Migrado de identitas/config.php a verumax/config.php (2026-01-05)
// Nota: IdentitasEngine sigue usándose cuando el módulo está activo (línea ~97)
require_once __DIR__ . '/../verumax/config.php';

// Cargar servicio de idiomas
use VERUMax\Services\LanguageService;

// VERIFICAR SI EL SITIO ESTÁ EN CONSTRUCCIÓN
// TEMPORAL: Comentado para debugging
// require_once __DIR__ . '/../verificar_construccion.php';

// Obtener configuración de la instancia
$instance_config = getInstanceConfig($slug);

// Inicializar idioma (procesar cambio si viene por GET)
$lang_request = $_GET['lang'] ?? null;
LanguageService::init($slug, $lang_request);

// ============================================================================
// LÓGICA DE MÓDULOS: Manejar diferentes combinaciones de activación
// ============================================================================

// CASO 1: Identitas desactivado + Certificatum activo → Solo portal de certificados
if (!$instance_config['identitas_activo'] && $instance_config['modulo_certificatum']) {

    // Preparar variables para el template standalone
    $instance = $instance_config;
    $certificatum_config = [
        'modo' => $instance_config['certificatum_modo'] ?? 'pagina',
        'titulo' => $instance_config['certificatum_titulo'] ?? 'Certificados',
    ];

    // Incluir directamente el template standalone desde la carpeta del módulo
    // (certificatum/templates/solo.php es un documento HTML completo)
    $template_path = __DIR__ . '/../certificatum/templates/solo.php';

    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo "<!-- ERROR: Template solo.php no encontrado en: $template_path -->";
        echo "<h1>Error: Template no encontrado</h1>";
    }
    exit;
}

// CASO 2: Ambos módulos desactivados → Sitio en construcción o mensaje
if (!$instance_config['identitas_activo'] && !$instance_config['modulo_certificatum']) {
    // Mostrar mensaje de sitio sin módulos activos
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($instance_config['nombre']); ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
    </head>
    <body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center">
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="construction" class="w-10 h-10 text-blue-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-4">
                <?php echo htmlspecialchars($instance_config['nombre']); ?>
            </h1>
            <p class="text-gray-600 mb-6">
                Estamos trabajando en nuestro sitio web. Pronto tendremos novedades.
            </p>
            <?php if (!empty($instance_config['email_contacto'])): ?>
                <a href="mailto:<?php echo htmlspecialchars($instance_config['email_contacto']); ?>"
                   class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                    Contacto
                </a>
            <?php endif; ?>
        </div>
        <script>lucide.createIcons();</script>
    </body>
    </html>
    <?php
    exit;
}

// CASO 3: Identitas activo → Funciona normal (con o sin Certificatum)
// Incluir el motor Identitas
require_once __DIR__ . '/../identitas/identitas_engine.php';

// Crear instancia del motor para SAJuR
$identitas = new IdentitasEngine($slug);

// Manejar envío de formulario de contacto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'enviar') {
    $resultado = $identitas->procesarContacto($_POST);
    if ($resultado['success']) {
        header('Location: ?enviado=1#contacto');
        exit;
    } else {
        header('Location: ?error=envio#contacto');
        exit;
    }
}

// Renderizar página solicitada o homepage
if (isset($_GET['page'])) {
    $identitas->renderPage($_GET['page']);
} else {
    $identitas->renderHome();
}
