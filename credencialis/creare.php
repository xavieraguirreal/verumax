<?php
/**
 * CREDENCIALIS - Generación de Credenciales de Membresía
 * Sistema VERUMax
 * Versión: 1.1
 */

// Habilitar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cargar configuración
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../certificatum/autodetect.php';

use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\MemberService;
use VERUMax\Services\QRCodeService;
use VERUMax\Services\CertificateService;

try {

    // Obtener parámetros
    $institucion = $_GET['institutio'] ?? $_POST['institutio'] ?? null;
    $dni = $_GET['documentum'] ?? $_POST['documentum'] ?? null;
    $lang = $_GET['lang'] ?? $_POST['lang'] ?? null;

    // Validar institución
    if (!$institucion) {
        throw new Exception('Institución no especificada');
    }

    // Validar DNI
    if (!$dni) {
        header('Location: index.php?institutio=' . urlencode($institucion));
        exit;
    }

    // Cargar configuración de institución
    $instance_config = InstitutionService::getConfig($institucion);
    if (!$instance_config) {
        throw new Exception('Institución no encontrada');
    }

    // Inicializar idioma
    LanguageService::init($institucion, $lang);
    $t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

    // Obtener datos del miembro
    $miembro = MemberService::getCredentialData($institucion, $dni);
    if (!$miembro || empty($miembro['tiene_credencial'])) {
        header('Location: index.php?error=not_found&institutio=' . urlencode($institucion));
        exit;
    }

    // Generar código de validación y QR
    $codigo_validacion = CertificateService::getValidationCode($institucion, $dni, '', 'credentialis');
    $url_validacion = obtenerURLBaseInstitucion() . "/credencialis/validare.php?codigo=" . $codigo_validacion;
    $qr_url = QRCodeService::generate($url_validacion, 150);

    // Verificar si es instancia de prueba
    $es_instancia_test = ($instance_config['en_produccion'] ?? 1) == 0;

} catch (Throwable $e) {
    die('Error: ' . $e->getMessage() . '<br><pre>' . $e->getTraceAsString() . '</pre>');
}

// Si llegamos aquí, todo está OK. Renderizar HTML.
?>
<!DOCTYPE html>
<html lang="<?php echo LanguageService::getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($t('credencialis.title', [], 'Credencial Digital')); ?> - <?php echo htmlspecialchars($instance_config['nombre']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primario: <?php echo $instance_config['color_primario'] ?? '#2E7D32'; ?>;
            --color-secundario: <?php echo $instance_config['color_secundario'] ?? '#1B5E20'; ?>;
        }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-white to-gray-100 min-h-screen">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <?php if (!empty($instance_config['logo_url'])): ?>
                    <img src="<?php echo htmlspecialchars($instance_config['logo_url']); ?>" alt="<?php echo htmlspecialchars($instance_config['nombre']); ?>" class="h-10 w-auto">
                <?php endif; ?>
                <span class="font-bold text-gray-800"><?php echo htmlspecialchars($instance_config['nombre']); ?></span>
            </div>
            <a href="index.php?institutio=<?php echo urlencode($institucion); ?>" class="text-sm text-gray-600 hover:text-gray-900 flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <?php echo $t('credencialis.back', [], 'Volver'); ?>
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                <?php echo $t('credencialis.your_credential', [], 'Tu Credencial Digital'); ?>
            </h1>
            <p class="text-gray-600">
                <?php echo $t('credencialis.credential_desc', [], 'Presentá este código QR para validar tu membresía'); ?>
            </p>
        </div>

        <div class="flex justify-center mb-8">
            <?php include __DIR__ . '/../certificatum/templates/credencial.php'; ?>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            <a href="creare_pdf.php?institutio=<?php echo urlencode($institucion); ?>&documentum=<?php echo urlencode($dni); ?>&genus=credentialis&lang=<?php echo urlencode(LanguageService::getCurrentLanguage()); ?>"
               class="inline-flex items-center gap-2 px-6 py-3 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all"
               style="background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));">
                <i data-lucide="download" class="w-5 h-5"></i>
                <?php echo $t('credencialis.download_pdf', [], 'Descargar PDF'); ?>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 hover:bg-gray-50 transition-all">
                <i data-lucide="printer" class="w-5 h-5"></i>
                <?php echo $t('credencialis.print', [], 'Imprimir'); ?>
            </button>
        </div>

        <div class="mt-8 p-4 bg-gray-100 rounded-xl text-center">
            <p class="text-sm text-gray-600">
                <i data-lucide="shield-check" class="w-4 h-4 inline mr-1"></i>
                <?php echo $t('credencialis.validation_info', [], 'Esta credencial puede ser verificada escaneando el código QR'); ?>
            </p>
            <p class="text-xs text-gray-500 mt-1">
                <?php echo $t('credencialis.code', [], 'Código'); ?>: <span class="font-mono"><?php echo htmlspecialchars($codigo_validacion); ?></span>
            </p>
        </div>
    </main>

    <footer class="mt-auto py-6 text-center text-sm text-gray-500">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($instance_config['nombre']); ?></p>
        <p class="mt-1">Powered by <a href="https://verumax.com" target="_blank" class="hover:underline" style="color: var(--color-primario);">VERUMax</a></p>
    </footer>

    <script>lucide.createIcons();</script>
</body>
</html>
