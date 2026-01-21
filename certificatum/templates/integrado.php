<?php
/**
 * IDENTITAS - Template Certificatum (Página Independiente)
 * Diseño moderno y configurable desde el administrador
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Preparar datos configurables
$config = $instance['config'] ?? [];

// Detectar modo de integración
$es_pagina_independiente = (isset($certificatum_config['certificatum_modo']) && $certificatum_config['certificatum_modo'] === 'pagina');

// Descripción - leer de instance_translations según idioma actual
$descripcion_default = $t('certificatum.page_subtitle', [], 'Accede a tus certificados, constancias y registro académico completo. Descarga tus documentos en formato PDF con código QR de validación.');
$id_instancia = $instance['id_instancia'] ?? $instance['id'] ?? 0;
$descripcion = LanguageService::getContent($id_instancia, 'certificatum_descripcion', null, $descripcion_default);

// CTA - leer de instance_translations según idioma actual
$cta_default = $t('certificatum.search_button', [], 'Ver mis certificados');
$cta_texto = LanguageService::getContent($id_instancia, 'certificatum_cta_texto', null, $cta_default);

// Colores - Usar paleta propia de Certificatum o paleta general
if (isset($instance['certificatum_usar_paleta_general']) && $instance['certificatum_usar_paleta_general'] == 0) {
    // Usa paleta propia de Certificatum
    $color_primario = $instance['certificatum_color_primario_propio'] ?? '#2E7D32';
    $color_secundario = $instance['certificatum_color_secundario_propio'] ?? '#1B5E20';
    $color_acento = $instance['certificatum_color_acento_propio'] ?? '#66BB6A';
} else {
    // Usa paleta general (default)
    $color_primario = $instance['color_primario'] ?? '#2E7D32';
    $color_secundario = $instance['color_secundario'] ?? '#1B5E20';
    $color_acento = $instance['color_acento'] ?? '#66BB6A';
}

// Redes sociales
$redes = [];
if (!empty($instance['redes_sociales'])) {
    $redes = json_decode($instance['redes_sociales'], true) ?: [];
}

// Estadísticas
$stats = [];
$mostrar_stats = $instance['certificatum_mostrar_stats'] ?? true;
if ($mostrar_stats && !empty($instance['certificatum_estadisticas'])) {
    $stats = json_decode($instance['certificatum_estadisticas'], true) ?: [];
}

// Si viene con DNI, delegar a cursus.php
if (!empty($_POST['documentum']) || !empty($_GET['documentum'])) {
    if (!defined('PROXY_MODE')) define('PROXY_MODE', true);
    if (!defined('INSTITUCION_SLUG')) define('INSTITUCION_SLUG', $instance['slug']);
    if (!defined('INSTITUCION_PATH')) define('INSTITUCION_PATH', ROOT_PATH . '/' . $instance['slug'] . '/');

    $_GET['institutio'] = $instance['slug'];
    $_POST['institutio'] = $instance['slug'];

    $cursus_path = ROOT_PATH . '/certificatum/cursus.php';
    if (file_exists($cursus_path)) {
        chdir(ROOT_PATH . '/certificatum');
        ob_start();
        include $cursus_path;
        $certificatum_content = ob_get_clean();
        echo $certificatum_content;
        return;
    }
}
?>

<!-- CSS Variables dinámicas -->
<style>
:root {
    --color-primario: <?php echo $color_primario; ?>;
    --color-secundario: <?php echo $color_secundario; ?>;
    --color-acento: <?php echo $color_acento; ?>;
}
</style>

<!-- Hero Section Ultra Moderno -->
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <!-- Decorative Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-30">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full blur-3xl" style="background: var(--color-primario);"></div>
        <div class="absolute top-1/3 -left-20 w-80 h-80 rounded-full blur-3xl" style="background: var(--color-acento);"></div>
        <div class="absolute -bottom-40 right-1/4 w-96 h-96 rounded-full blur-3xl" style="background: var(--color-secundario);"></div>
    </div>

    <div class="container mx-auto px-6 py-16 relative z-10">
        <div class="max-w-5xl mx-auto">
            <!-- Título Principal -->
            <div class="text-center mb-12 animate-fade-in-down">
                <h1 class="text-5xl md:text-6xl font-black text-gray-900 dark:text-white leading-tight mb-6 bg-clip-text text-transparent bg-gradient-to-r from-gray-900 via-gray-700 to-gray-900 dark:from-white dark:via-gray-300 dark:to-white">
                    <?php echo htmlspecialchars($certificatum_config['titulo'] ?? 'Certificados'); ?>
                </h1>

                <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                    <?php echo htmlspecialchars($descripcion); ?>
                </p>
            </div>

            <!-- Formulario Principal con diseño ultra moderno -->
            <div class="max-w-2xl mx-auto mb-16 animate-fade-in-up">
                <div class="relative">
                    <!-- Glow effect background -->
                    <div class="absolute inset-0 rounded-3xl blur-2xl opacity-20" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));"></div>

                    <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 md:p-12 border border-gray-200 dark:border-gray-700">
                        <form method="POST" action="?page=certificados" class="space-y-8">
                            <div>
                                <label for="documentum" class="block text-lg font-bold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                                    <i data-lucide="fingerprint" class="w-6 h-6"></i>
                                    <?php echo $t('certificatum.search_enter_document', [], 'Ingresa tu número de documento'); ?>
                                </label>
                                <input
                                    type="text"
                                    id="documentum"
                                    name="documentum"
                                    placeholder="<?php echo $t('certificatum.search_example', [], 'Ej: 12345678'); ?>"
                                    required
                                    pattern="[0-9]{7,15}"
                                    class="w-full px-8 py-5 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-2xl focus:ring-4 focus:ring-opacity-50 text-2xl text-center font-bold tracking-wider transition-all shadow-lg hover:shadow-xl"
                                    style="focus:ring-color: var(--color-primario); focus:border-color: var(--color-primario);"
                                >
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 text-center flex items-center justify-center gap-2">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                                    <?php echo $t('certificatum.search_help', [], 'Solo números, sin puntos ni espacios'); ?>
                                </p>
                            </div>

                            <button
                                type="submit"
                                class="group relative w-full text-white font-black py-6 rounded-2xl transition-all shadow-2xl hover:shadow-3xl transform hover:-translate-y-1 text-xl flex items-center justify-center gap-3 overflow-hidden"
                                style="background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-secundario) 100%);">
                                <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-30 transform -skew-x-12 group-hover:translate-x-full transition-transform duration-1000"></span>
                                <i data-lucide="arrow-right-circle" class="w-7 h-7 relative z-10 group-hover:translate-x-1 transition-transform"></i>
                                <span class="relative z-10"><?php echo htmlspecialchars($cta_texto); ?></span>
                            </button>
                        </form>

                        <?php if (isset($_GET['error']) && $_GET['error'] === 'not_found'): ?>
                            <div class="mt-8 bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-800 rounded-2xl p-6">
                                <div class="flex items-start gap-4">
                                    <i data-lucide="alert-triangle" class="w-7 h-7 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="font-bold text-red-900 dark:text-red-300 text-lg"><?php echo $t('certificatum.search_not_found', [], 'No se encontraron certificados'); ?></p>
                                        <p class="text-red-700 dark:text-red-400 mt-2">
                                            <?php echo $t('certificatum.search_not_found_desc', [], 'No se encontraron certificados asociados al documento ingresado. Verifica que el número sea correcto o contacta a la institución.'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Características/Beneficios -->
            <div class="grid md:grid-cols-3 gap-8 mb-16 animate-fade-in">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-2 border border-gray-200 dark:border-gray-700">
                    <div class="w-16 h-16 rounded-xl mb-6 flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                        <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3"><?php echo $t('certificatum.feature_verifiable', [], 'Certificados Verificables'); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400"><?php echo $t('certificatum.feature_verifiable_desc', [], 'Con código QR de validación única y segura'); ?></p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-2 border border-gray-200 dark:border-gray-700">
                    <div class="w-16 h-16 rounded-xl mb-6 flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                        <i data-lucide="clock" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3"><?php echo $t('certificatum.feature_access', [], 'Acceso 24/7'); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400"><?php echo $t('certificatum.feature_access_desc', [], 'Disponible en cualquier momento y desde cualquier lugar'); ?></p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-2 border border-gray-200 dark:border-gray-700">
                    <div class="w-16 h-16 rounded-xl mb-6 flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                        <i data-lucide="download" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3"><?php echo $t('certificatum.feature_download', [], 'Descarga Inmediata'); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400"><?php echo $t('certificatum.feature_download_desc', [], 'PDF de alta calidad listo para imprimir'); ?></p>
                </div>
            </div>

            <!-- Estadísticas (solo en sección integrada, NO en página independiente) -->
            <?php if (!$es_pagina_independiente && $mostrar_stats && !empty($stats)): ?>
                <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-3xl p-12 shadow-2xl border border-gray-200 dark:border-gray-700 mb-16">
                    <div class="grid md:grid-cols-3 gap-8 text-center">
                        <?php if (!empty($stats['certificados_emitidos'])): ?>
                            <div class="animate-fade-in">
                                <div class="text-5xl md:text-6xl font-black mb-3 bg-clip-text text-transparent bg-gradient-to-r" style="background-image: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                                    <?php echo htmlspecialchars($stats['certificados_emitidos']); ?>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 font-semibold text-lg"><?php echo $t('identitas.stat_certificates_issued', [], 'Certificados Emitidos'); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($stats['estudiantes'])): ?>
                            <div class="animate-fade-in animation-delay-100">
                                <div class="text-5xl md:text-6xl font-black mb-3 bg-clip-text text-transparent bg-gradient-to-r" style="background-image: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                                    <?php echo htmlspecialchars($stats['estudiantes']); ?>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 font-semibold text-lg"><?php echo $t('identitas.stat_students', [], 'Estudiantes'); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($stats['cursos'])): ?>
                            <div class="animate-fade-in animation-delay-200">
                                <div class="text-5xl md:text-6xl font-black mb-3 bg-clip-text text-transparent bg-gradient-to-r" style="background-image: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                                    <?php echo htmlspecialchars($stats['cursos']); ?>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 font-semibold text-lg"><?php echo $t('identitas.stat_courses', [], 'Cursos Disponibles'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Redes Sociales (solo en sección integrada, NO en página independiente) -->
            <?php
            $redes_visibles = array_filter($redes, function($url) {
                return !empty($url);
            });
            if (!$es_pagina_independiente && !empty($redes_visibles)): ?>
                <div class="text-center animate-fade-in-up">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6"><?php echo $t('identitas.follow_us', [], 'Síguenos en redes sociales'); ?></h3>
                    <div class="flex justify-center items-center gap-4 flex-wrap">
                        <?php if (!empty($redes['instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['instagram']); ?>" target="_blank"
                               class="w-14 h-14 rounded-xl flex items-center justify-center text-white hover:scale-110 transition-transform shadow-lg"
                               style="background: linear-gradient(135deg, #E1306C, #C13584);">
                                <i data-lucide="instagram" class="w-7 h-7"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($redes['facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['facebook']); ?>" target="_blank"
                               class="w-14 h-14 rounded-xl flex items-center justify-center text-white hover:scale-110 transition-transform shadow-lg"
                               style="background: linear-gradient(135deg, #1877F2, #0C63D4);">
                                <i data-lucide="facebook" class="w-7 h-7"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($redes['linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['linkedin']); ?>" target="_blank"
                               class="w-14 h-14 rounded-xl flex items-center justify-center text-white hover:scale-110 transition-transform shadow-lg"
                               style="background: linear-gradient(135deg, #0077B5, #005E93);">
                                <i data-lucide="linkedin" class="w-7 h-7"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($redes['whatsapp'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['whatsapp']); ?>" target="_blank"
                               class="w-14 h-14 rounded-xl flex items-center justify-center text-white hover:scale-110 transition-transform shadow-lg"
                               style="background: linear-gradient(135deg, #25D366, #20BA5A);">
                                <i data-lucide="message-circle" class="w-7 h-7"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($redes['twitter'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['twitter']); ?>" target="_blank"
                               class="w-14 h-14 rounded-xl flex items-center justify-center text-white hover:scale-110 transition-transform shadow-lg"
                               style="background: linear-gradient(135deg, #1DA1F2, #0C85D0);">
                                <i data-lucide="twitter" class="w-7 h-7"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($redes['youtube'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['youtube']); ?>" target="_blank"
                               class="w-14 h-14 rounded-xl flex items-center justify-center text-white hover:scale-110 transition-transform shadow-lg"
                               style="background: linear-gradient(135deg, #FF0000, #CC0000);">
                                <i data-lucide="youtube" class="w-7 h-7"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Animaciones CSS -->
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.8s ease-out;
}

.animate-fade-in-down {
    animation: fadeInDown 0.8s ease-out;
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}

.animation-delay-100 {
    animation-delay: 0.1s;
}

.animation-delay-200 {
    animation-delay: 0.2s;
}

.animate-pulse-slow {
    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .7; }
}
</style>

<!-- Inicializar iconos -->
<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
