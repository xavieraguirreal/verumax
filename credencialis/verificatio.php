<?php
/**
 * CREDENCIALIS - Página de Verificación Pública
 * Sistema VERUMax
 * Versión: 1.0
 *
 * Muestra la información verificada de una credencial de forma pública.
 */

// Cargar configuración
require_once __DIR__ . '/config.php';

use VERUMax\Services\CertificateService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\MemberService;

// ============================================================================
// OBTENER CÓDIGO DE VALIDACIÓN
// ============================================================================

$codigo = $_GET['codigo'] ?? null;

if (!$codigo) {
    header('Location: validare.php');
    exit;
}

// ============================================================================
// BUSCAR VALIDACIÓN EN BASE DE DATOS
// ============================================================================

$validacion = CertificateService::findByValidationCode($codigo);

if (!$validacion) {
    $error = 'not_found';
    include __DIR__ . '/../certificatum/templates/verificatio_error.php';
    exit;
}

// ============================================================================
// OBTENER DATOS COMPLETOS
// ============================================================================

$institucion = $validacion['institucion'];
$dni = $validacion['dni'];
$tipo_documento = $validacion['tipo_documento'];

// Cargar configuración de institución
$instance_config = InstitutionService::getConfig($institucion);

if (!$instance_config) {
    $error = 'institution_not_found';
    include __DIR__ . '/../certificatum/templates/verificatio_error.php';
    exit;
}

// Inicializar idioma
$lang = $_GET['lang'] ?? null;
LanguageService::init($institucion, $lang);
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Obtener datos del miembro
$miembro = MemberService::getCredentialData($institucion, $dni);

if (!$miembro) {
    $error = 'member_not_found';
    include __DIR__ . '/../certificatum/templates/verificatio_error.php';
    exit;
}

// Extraer datos
$nombre_completo = $miembro['nombre_completo'] ?? ($miembro['nombre'] . ' ' . $miembro['apellido']);
$numero_asociado = $miembro['numero_asociado'] ?? '';
$tipo_asociado = $miembro['tipo_asociado'] ?? '';
$categoria_servicio = $miembro['categoria_servicio'] ?? '';
$fecha_ingreso = $miembro['fecha_ingreso'] ?? '';

// Formatear DNI
$dni_formateado = number_format((int)preg_replace('/[^0-9]/', '', $dni), 0, '', '.');

// Formatear fecha
$fecha_ingreso_fmt = $fecha_ingreso ? date('d/m/Y', strtotime($fecha_ingreso)) : '';

// Colores
$color_primario = $instance_config['color_primario'] ?? '#2E7D32';
$color_secundario = $instance_config['color_secundario'] ?? '#1B5E20';
?>
<!DOCTYPE html>
<html lang="<?php echo LanguageService::getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t('credencialis.verification_title', [], 'Verificación de Credencial'); ?> - <?php echo htmlspecialchars($instance_config['nombre']); ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-primario: <?php echo $color_primario; ?>;
            --color-secundario: <?php echo $color_secundario; ?>;
        }
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-white to-gray-100 min-h-screen">

    <div class="max-w-2xl mx-auto px-4 py-12">
        <!-- Tarjeta de verificación -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header verde de éxito -->
            <div class="p-6 text-white text-center"
                 style="background: linear-gradient(135deg, <?php echo $color_primario; ?>, <?php echo $color_secundario; ?>);">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="shield-check" class="w-8 h-8"></i>
                </div>
                <h1 class="text-2xl font-bold mb-1">
                    <?php echo $t('credencialis.verified', [], 'Credencial Verificada'); ?>
                </h1>
                <p class="text-white/80 text-sm">
                    <?php echo $t('credencialis.verified_desc', [], 'Esta credencial es auténtica y válida'); ?>
                </p>
            </div>

            <!-- Logo institución -->
            <div class="flex justify-center -mt-6 relative z-10">
                <div class="bg-white rounded-xl shadow-lg p-3">
                    <?php if (!empty($instance_config['logo_url'])): ?>
                        <img src="<?php echo htmlspecialchars($instance_config['logo_url']); ?>"
                             alt="<?php echo htmlspecialchars($instance_config['nombre']); ?>"
                             class="h-12 w-auto">
                    <?php else: ?>
                        <div class="h-12 w-32 bg-gray-200 rounded flex items-center justify-center text-gray-500 text-sm">
                            <?php echo htmlspecialchars($instance_config['nombre']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Datos del miembro -->
            <div class="p-6">
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-500 uppercase tracking-wider mb-1">
                        <?php echo $t('credencialis.member_name', [], 'Nombre del Socio'); ?>
                    </p>
                    <h2 class="text-2xl font-bold text-gray-900">
                        <?php echo htmlspecialchars($nombre_completo); ?>
                    </h2>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- DNI -->
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">
                            <?php echo $t('credencialis.document', [], 'Documento'); ?>
                        </p>
                        <p class="text-lg font-bold text-gray-900">
                            <?php echo htmlspecialchars($dni_formateado); ?>
                        </p>
                    </div>

                    <!-- Número de socio -->
                    <?php if ($numero_asociado): ?>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">
                            <?php echo $t('credencialis.member_number', [], 'N° de Socio'); ?>
                        </p>
                        <p class="text-lg font-bold" style="color: <?php echo $color_primario; ?>;">
                            <?php echo htmlspecialchars($numero_asociado); ?>
                            <?php if ($tipo_asociado): ?>
                                <span class="text-sm font-normal"><?php echo htmlspecialchars($tipo_asociado); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Categoría de servicio -->
                    <?php if ($categoria_servicio): ?>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">
                            <?php echo $t('credencialis.service_category', [], 'Categoría'); ?>
                        </p>
                        <p class="text-lg font-semibold" style="color: <?php echo $color_primario; ?>;">
                            <?php echo htmlspecialchars($categoria_servicio); ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Fecha de ingreso -->
                    <?php if ($fecha_ingreso_fmt): ?>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">
                            <?php echo $t('credencialis.member_since', [], 'Socio desde'); ?>
                        </p>
                        <p class="text-lg font-semibold text-gray-900">
                            <?php echo $fecha_ingreso_fmt; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Institución -->
                <div class="mt-6 pt-6 border-t text-center">
                    <p class="text-sm text-gray-500 mb-1">
                        <?php echo $t('credencialis.issued_by', [], 'Credencial emitida por'); ?>
                    </p>
                    <p class="font-semibold text-gray-900">
                        <?php echo htmlspecialchars($instance_config['nombre_completo'] ?? $instance_config['nombre']); ?>
                    </p>
                </div>

                <!-- Código de validación -->
                <div class="mt-4 text-center">
                    <p class="text-xs text-gray-400">
                        <?php echo $t('credencialis.validation_code', [], 'Código de validación'); ?>:
                        <span class="font-mono"><?php echo htmlspecialchars($codigo); ?></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                <?php echo $t('credencialis.verified_by', [], 'Verificado por'); ?>
                <a href="https://verumax.com" target="_blank" class="font-semibold hover:underline" style="color: <?php echo $color_primario; ?>;">
                    VERUMax
                </a>
            </p>
            <p class="text-xs text-gray-400 mt-2">
                <?php echo date('d/m/Y H:i'); ?>
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
