<?php
/**
 * Bloque: Misión Centrada
 * Template: Moderno/Minimal para Sobre Nosotros
 *
 * Variables disponibles:
 * - $contenido: array con el contenido del bloque
 * - $config: array con la configuración del bloque
 * - $colores: array con colores de la instancia
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Obtener contenido
$titulo = $contenido['titulo'] ?? $t('identitas.about_mission', [], 'Misión');
$texto = $contenido['texto'] ?? '';
$link_texto = $contenido['link_texto'] ?? '';
$link_url = $contenido['link_url'] ?? '';

// Configuración de ancho
$ancho = $config['ancho'] ?? 'full';
$container_class = $ancho === 'narrow' ? 'max-w-3xl' : 'max-w-5xl';
?>

<div class="<?php echo $container_class; ?> mx-auto text-center">
    <h2 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white mb-6">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>

    <div class="text-lg md:text-xl text-gray-600 dark:text-gray-400 leading-relaxed space-y-4 mb-8">
        <?php echo nl2br(htmlspecialchars($texto)); ?>
    </div>

    <?php if ($link_texto && $link_url): ?>
    <a href="<?php echo htmlspecialchars($link_url); ?>"
       target="_blank"
       class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl font-bold text-white shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-1"
       style="background: linear-gradient(135deg, var(--color-primario) 0%, var(--color-secundario) 100%);">
        <?php echo htmlspecialchars($link_texto); ?>
        <i data-lucide="external-link" class="w-5 h-5"></i>
    </a>
    <?php endif; ?>
</div>
