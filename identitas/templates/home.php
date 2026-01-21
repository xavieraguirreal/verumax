<?php
/**
 * IDENTITAS - Template Home Ultra Moderno
 * Página principal con diseño moderno y atractivo
 *
 * Soporta:
 * - Sistema legacy de páginas (identitas_paginas)
 * - Nuevo sistema de templates + bloques (identitas_templates)
 */

use VERUMax\Services\TemplateService;
use VERUMax\Services\LanguageService;

// Helper de traducción
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Helper para verificar si el sistema de templates está disponible y tiene contenido
function useTemplateSystem(int $idInstancia, string $pagina): ?array {
    try {
        $data = TemplateService::getPageRenderData($idInstancia, $pagina);
        // Solo usar templates si hay contenido configurado
        if (!empty($data['template']) && !empty($data['contenido'])) {
            return $data;
        }
    } catch (\Exception $e) {
        // Si hay error (tabla no existe, etc), usar sistema legacy
    }
    return null;
}

// Colores - Usar paleta propia de Identitas o paleta general
if (isset($instance['identitas_usar_paleta_general']) && $instance['identitas_usar_paleta_general'] == 0) {
    // Usa paleta propia de Identitas
    $color_primario = $instance['identitas_color_primario_propio'] ?? '#2E7D32';
    $color_secundario = $instance['identitas_color_secundario_propio'] ?? '#1B5E20';
    $color_acento = $instance['identitas_color_acento_propio'] ?? '#66BB6A';
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
<section id="inicio" class="relative min-h-screen overflow-hidden bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-20 md:py-32">
    <!-- Elementos decorativos de fondo con blur -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-30">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full blur-3xl" style="background: var(--color-primario);"></div>
        <div class="absolute top-1/3 -left-20 w-80 h-80 rounded-full blur-3xl" style="background: var(--color-acento);"></div>
        <div class="absolute -bottom-40 right-1/4 w-96 h-96 rounded-full blur-3xl" style="background: var(--color-secundario);"></div>
    </div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-5xl mx-auto text-center">
            <!-- Logo con efecto glow -->
            <?php if (!empty($instance['logo_url'])): ?>
                <div class="mb-8 animate-fade-in-down">
                    <div class="inline-block relative">
                        <?php
                        $logo_estilo = $instance['logo_estilo'] ?? 'rectangular';
                        // Solo mostrar blur circular si el logo es circular
                        if ($logo_estilo === 'circular'):
                        ?>
                        <div class="absolute inset-0 rounded-full blur-xl opacity-50" style="background: var(--color-primario);"></div>
                        <?php endif; ?>
                        <img src="<?php echo htmlspecialchars($instance['logo_url']); ?>"
                             alt="Logo <?php echo htmlspecialchars($instance['nombre']); ?>"
                             class="<?php echo getLogoClasses($logo_estilo, 'h-32 md:h-40'); ?> mx-auto relative z-10 ring-4 ring-white dark:ring-gray-800 shadow-2xl">
                    </div>
                </div>
            <?php endif; ?>

            <!-- Título con efecto gradiente -->
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-black text-gray-900 dark:text-white leading-tight mb-6 animate-fade-in bg-clip-text text-transparent bg-gradient-to-r from-gray-900 via-gray-700 to-gray-900 dark:from-white dark:via-gray-300 dark:to-white">
                <?php echo htmlspecialchars($instance['nombre_completo'] ?: $instance['nombre']); ?>
            </h1>

            <?php
            // Obtener misión traducida (primero de BD, fallback a config)
            $mision_default = $instance['config']['mision'] ?? null;
            $mision = LanguageService::getContent($instance['id_instancia'], 'mision', null, $mision_default);
            if ($mision):
            ?>
                <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed mb-12 animate-fade-in-up">
                    <?php echo nl2br(htmlspecialchars($mision)); ?>
                </p>
            <?php endif; ?>

            <!-- CTA Buttons ultra modernos -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16 animate-fade-in-up">
                <?php if ($modulos_activos['certificatum'] && (!isset($certificatum_config) || $certificatum_config['modo'] !== 'pagina_principal')): ?>
                    <a href="https://verumax.com/certificatum/cursus.php?institutio=<?php echo urlencode($instance['slug']); ?>&amp;lang=<?php echo urlencode(LanguageService::getCurrentLang()); ?>"
                       class="group relative px-8 py-5 text-white rounded-2xl font-black text-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 flex items-center gap-3 overflow-hidden shadow-xl"
                       style="background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-secundario) 100%);">
                        <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-30 transform -skew-x-12 group-hover:translate-x-full transition-transform duration-1000"></span>
                        <i data-lucide="award" class="w-6 h-6 relative z-10"></i>
                        <span class="relative z-10"><?php echo $t('identitas.footer_certificates_portal', [], 'Portal de Certificados'); ?></span>
                    </a>
                <?php endif; ?>

                <a href="#contacto"
                   class="px-8 py-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-2 border-gray-300 dark:border-gray-600 rounded-2xl font-bold text-lg hover:border-primario transition-all duration-300 transform hover:-translate-y-1 flex items-center gap-3 shadow-lg hover:shadow-xl"
                   style="hover:border-color: var(--color-primario); hover:color: var(--color-primario);">
                    <i data-lucide="mail" class="w-6 h-6"></i>
                    <span><?php echo $t('identitas.contact_title', [], 'Contacto'); ?></span>
                </a>
            </div>

            <!-- Características destacadas -->
            <?php if ($modulos_activos['certificatum']): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-2 border border-gray-200 dark:border-gray-700">
                        <div class="w-16 h-16 rounded-xl mb-6 flex items-center justify-center shadow-lg mx-auto" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                            <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3"><?php echo $t('certificatum.feature_verifiable', [], 'Certificados Verificables'); ?></h3>
                        <p class="text-gray-600 dark:text-gray-400"><?php echo $t('certificatum.feature_verifiable_desc', [], 'Con código QR de validación única y segura'); ?></p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-2 border border-gray-200 dark:border-gray-700">
                        <div class="w-16 h-16 rounded-xl mb-6 flex items-center justify-center shadow-lg mx-auto" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                            <i data-lucide="clock" class="w-8 h-8 text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3"><?php echo $t('certificatum.feature_access', [], 'Acceso 24/7'); ?></h3>
                        <p class="text-gray-600 dark:text-gray-400"><?php echo $t('certificatum.feature_access_desc', [], 'Disponible en cualquier momento y desde cualquier lugar'); ?></p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-2 border border-gray-200 dark:border-gray-700">
                        <div class="w-16 h-16 rounded-xl mb-6 flex items-center justify-center shadow-lg mx-auto" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                            <i data-lucide="download" class="w-8 h-8 text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3"><?php echo $t('certificatum.feature_download', [], 'Descarga Inmediata'); ?></h3>
                        <p class="text-gray-600 dark:text-gray-400"><?php echo $t('certificatum.feature_download_desc', [], 'PDF de alta calidad listo para imprimir'); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Bloque: Contacto Rápido (barra horizontal después del Hero) -->
<?php
// Cargar bloque de contacto rápido en estilo horizontal
$contenido = ['estilo' => 'horizontal'];
include IDENTITAS_PATH . '/templates/bloques/contacto_rapido.php';
?>

<!-- Sección Certificatum (solo si está en modo "seccion") -->
<?php if ($certificatum_config && $certificatum_config['modo'] === 'seccion'): ?>
<section id="certificados" class="py-20 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
        <div class="absolute top-20 right-20 w-64 h-64 rounded-full blur-3xl" style="background: var(--color-acento);"></div>
    </div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-6 shadow-lg animate-pulse-slow" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                <i data-lucide="<?php echo htmlspecialchars($certificatum_config['icono']); ?>" class="w-10 h-10 text-white"></i>
            </div>
            <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white mb-4">
                <?php echo $t('certificatum.page_title', [], $certificatum_config['titulo']); ?>
            </h2>
            <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                <?php echo $t('certificatum.page_subtitle', [], 'Accedé a tus certificados, constancias y analítico académico'); ?>
            </p>
        </div>

        <!-- Formulario de DNI con efecto glow -->
        <div class="max-w-2xl mx-auto">
            <div class="relative">
                <!-- Glow effect background -->
                <div class="absolute inset-0 rounded-3xl blur-2xl opacity-20" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 md:p-12 border border-gray-200 dark:border-gray-700">
                    <form method="POST" action="" class="space-y-8">
                        <div>
                            <label for="documentum" class="block text-lg font-bold text-gray-700 dark:text-gray-200 mb-4 flex items-center justify-center gap-2">
                                <i data-lucide="fingerprint" class="w-6 h-6"></i>
                                <?php echo $t('certificatum.search_title', [], 'Número de Documento'); ?>
                            </label>
                            <input
                                type="text"
                                id="documentum"
                                name="documentum"
                                placeholder="<?php echo $t('certificatum.search_placeholder', [], 'Ingresá tu documento (solo números)'); ?>"
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
                            <span class="relative z-10"><?php echo $t('certificatum.search_button', [], 'Ver mis certificados'); ?></span>
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
    </div>
</section>
<?php endif; ?>

<!-- Sección Sobre Nosotros -->
<?php
// Intentar usar nuevo sistema de templates
$id_instancia_actual = $instance['id_instancia'] ?? $instance['id'] ?? 0;
$sobreNosotrosTemplate = useTemplateSystem($id_instancia_actual, 'sobre-nosotros');

if ($sobreNosotrosTemplate):
    // NUEVO SISTEMA: Renderizar con templates + bloques
?>
<section id="sobre-nosotros" class="py-20 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-6">
        <div class="max-w-4xl mx-auto">
            <?php
            $colores = [
                'primario' => $color_primario,
                'secundario' => $color_secundario,
                'acento' => $color_acento
            ];
            echo TemplateService::renderPage($id_instancia_actual, 'sobre-nosotros', $colores);
            ?>
        </div>
    </div>
</section>
<?php
else:
    // FALLBACK: Sistema legacy
    $sobre_nosotros = null;
    foreach ($paginas as $pag) {
        if ($pag['slug'] == 'sobre-nosotros') {
            $sobre_nosotros = $pag;
            break;
        }
    }

    if ($sobre_nosotros && !empty($sobre_nosotros['contenido'])):
?>
<section id="sobre-nosotros" class="py-20 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-6">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white mb-4"><?php echo $t('identitas.about_title', [], 'Sobre Nosotros'); ?></h2>
                <div class="w-20 h-1.5 rounded-full mx-auto" style="background: linear-gradient(90deg, var(--color-primario), var(--color-acento));"></div>
            </div>

            <div class="prose prose-lg dark:prose-invert max-w-none bg-gray-50 dark:bg-gray-800 rounded-2xl p-8 shadow-lg">
                <?php echo $sobre_nosotros['contenido']; ?>
            </div>

            <?php if (!empty($instance['config']['sitio_web_oficial'])): ?>
                <div class="mt-8 text-center">
                    <a href="<?php echo htmlspecialchars($instance['config']['sitio_web_oficial']); ?>"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 text-gray-900 dark:text-white rounded-xl hover:shadow-xl font-bold transition-all transform hover:-translate-y-1 shadow-lg">
                        <?php echo $t('identitas.visit_website', [], 'Visitar sitio web oficial'); ?>
                        <i data-lucide="external-link" class="w-5 h-5"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
    endif;
endif;
?>

<!-- Sección Servicios -->
<?php
// Intentar usar nuevo sistema de templates
$serviciosTemplate = useTemplateSystem($id_instancia_actual, 'servicios');

if ($serviciosTemplate):
    // NUEVO SISTEMA: Renderizar con templates + bloques
?>
<section id="servicios" class="py-20 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
    <div class="container mx-auto px-6">
        <div class="max-w-6xl mx-auto">
            <?php
            $colores = [
                'primario' => $color_primario,
                'secundario' => $color_secundario,
                'acento' => $color_acento
            ];
            echo TemplateService::renderPage($id_instancia_actual, 'servicios', $colores);
            ?>
        </div>
    </div>
</section>
<?php
else:
    // FALLBACK: Sistema legacy
    $servicios = null;
    foreach ($paginas as $pag) {
        if ($pag['slug'] == 'servicios') {
            $servicios = $pag;
            break;
        }
    }

    if ($servicios && !empty($servicios['contenido'])):
?>
<section id="servicios" class="py-20 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
    <div class="container mx-auto px-6">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white mb-4"><?php echo $t('identitas.our_services', [], 'Nuestros Servicios'); ?></h2>
                <div class="w-20 h-1.5 rounded-full mx-auto" style="background: linear-gradient(90deg, var(--color-primario), var(--color-acento));"></div>
            </div>

            <div class="prose prose-lg dark:prose-invert max-w-none bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg">
                <?php echo $servicios['contenido']; ?>
            </div>
        </div>
    </div>
</section>
<?php
    endif;
endif;
?>

<!-- Sección Contacto -->
<?php
// Intentar usar nuevo sistema de templates
$contactoTemplate = useTemplateSystem($id_instancia_actual, 'contacto');

// También buscar en sistema legacy
$contacto_page = null;
foreach ($paginas as $pag) {
    if ($pag['slug'] == 'contacto') {
        $contacto_page = $pag;
        break;
    }
}

// Mostrar sección si hay template nuevo o página legacy
if ($contactoTemplate || $contacto_page):
?>
<section id="contacto" class="py-20 bg-white dark:bg-gray-900 relative overflow-hidden">
    <!-- Decorative elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
        <div class="absolute bottom-20 left-20 w-64 h-64 rounded-full blur-3xl" style="background: var(--color-primario);"></div>
    </div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-4xl mx-auto">
            <?php if ($contactoTemplate): ?>
                <!-- NUEVO SISTEMA: Renderizar info de contacto con bloques -->
                <?php
                $colores = [
                    'primario' => $color_primario,
                    'secundario' => $color_secundario,
                    'acento' => $color_acento
                ];
                echo TemplateService::renderPage($id_instancia_actual, 'contacto', $colores);
                ?>
            <?php else: ?>
                <!-- FALLBACK: Sistema legacy -->
                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white mb-4"><?php echo $t('identitas.contact_us', [], 'Contáctanos'); ?></h2>
                    <div class="w-20 h-1.5 rounded-full mx-auto" style="background: linear-gradient(90deg, var(--color-primario), var(--color-acento));"></div>
                </div>

                <div class="prose prose-lg dark:prose-invert max-w-none mb-12 text-center">
                    <?php echo $contacto_page['contenido']; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de contacto con diseño moderno -->
            <div class="relative">
                <div class="absolute inset-0 rounded-3xl blur-2xl opacity-10" style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));"></div>

                <div class="relative bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-3xl p-8 md:p-12 shadow-2xl border border-gray-200 dark:border-gray-700">
                    <?php if (isset($_GET['enviado']) && $_GET['enviado'] == '1'): ?>
                        <div class="border-2 p-6 rounded-2xl mb-6 text-gray-900 dark:text-white" style="background-color: var(--color-primario); opacity: 0.9; border-color: var(--color-primario);">
                            <div class="flex items-center gap-3">
                                <i data-lucide="check-circle" class="w-6 h-6 text-white"></i>
                                <p class="font-bold text-lg text-white"><?php echo $t('identitas.message_sent', [], '¡Mensaje enviado!'); ?></p>
                            </div>
                            <p class="mt-2 ml-9 text-white opacity-90"><?php echo $t('identitas.message_sent_desc', [], 'Gracias por contactarnos. Te responderemos pronto.'); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error']) && $_GET['error'] == 'envio'): ?>
                        <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-800 text-red-700 dark:text-red-300 p-6 rounded-2xl mb-6">
                            <div class="flex items-center gap-3">
                                <i data-lucide="alert-circle" class="w-6 h-6"></i>
                                <p class="font-bold text-lg"><?php echo $t('identitas.message_error', [], 'Error al enviar'); ?></p>
                            </div>
                            <p class="mt-2 ml-9"><?php echo $t('identitas.message_error_desc', [], 'Por favor, intenta nuevamente o contáctanos por email.'); ?></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="?action=enviar#contacto" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="nombre" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                    <?php echo $t('identitas.contact_form_name', [], 'Nombre'); ?> *
                                </label>
                                <input type="text" id="nombre" name="nombre" required
                                       class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-opacity-50 transition-all"
                                       style="focus:ring-color: var(--color-primario); focus:border-color: var(--color-primario);">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                    <?php echo $t('identitas.contact_form_email', [], 'E-mail'); ?> *
                                </label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-opacity-50 transition-all"
                                       style="focus:ring-color: var(--color-primario); focus:border-color: var(--color-primario);">
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="telefono" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                    <?php echo $t('identitas.contact_phone', [], 'Teléfono'); ?>
                                </label>
                                <input type="tel" id="telefono" name="telefono"
                                       class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-opacity-50 transition-all"
                                       style="focus:ring-color: var(--color-primario); focus:border-color: var(--color-primario);">
                            </div>

                            <div>
                                <label for="asunto" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                    <?php echo $t('identitas.contact_form_subject', [], 'Asunto'); ?> *
                                </label>
                                <input type="text" id="asunto" name="asunto" required
                                       class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-opacity-50 transition-all"
                                       style="focus:ring-color: var(--color-primario); focus:border-color: var(--color-primario);">
                            </div>
                        </div>

                        <div>
                            <label for="mensaje" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                <?php echo $t('identitas.contact_form_message', [], 'Mensaje'); ?> *
                            </label>
                            <textarea id="mensaje" name="mensaje" rows="6" required
                                      class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-opacity-50 transition-all"
                                      style="focus:ring-color: var(--color-primario); focus:border-color: var(--color-primario);"></textarea>
                        </div>

                        <button type="submit"
                                class="group relative w-full md:w-auto px-10 py-5 text-white rounded-2xl font-black text-lg hover:shadow-2xl transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3 shadow-xl overflow-hidden"
                                style="background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-secundario) 100%);">
                            <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-30 transform -skew-x-12 group-hover:translate-x-full transition-transform duration-1000"></span>
                            <i data-lucide="send" class="w-6 h-6 relative z-10"></i>
                            <span class="relative z-10"><?php echo $t('identitas.contact_form_send', [], 'Enviar mensaje'); ?></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Bloque: Redes Sociales (nuevo sistema de bloques) -->
<?php
// Verificar si hay redes sociales configuradas
$redes_visibles = array_filter($redes, function($url) {
    return !empty($url);
});

if (!empty($redes_visibles)):
    // Cargar bloque mejorado de redes sociales
    $contenido = ['estilo' => 'circles']; // circles | cards
    $colores = [
        'primario' => $color_primario,
        'secundario' => $color_secundario,
        'acento' => $color_acento
    ];
    include IDENTITAS_PATH . '/templates/bloques/redes_sociales_cta.php';
endif;
?>

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

.animate-pulse-slow {
    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .7; }
}

/* Smooth scroll para navegación */
html {
    scroll-behavior: smooth;
}
</style>

<!-- Script adicional -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar iconos Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Si hay hash en URL al cargar, scroll a esa sección
    if (window.location.hash) {
        setTimeout(() => {
            const target = document.querySelector(window.location.hash);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }, 100);
    }
});
</script>
