<?php
/**
 * Bloque: Introducción Historia
 * Template: Timeline para Sobre Nosotros
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Buscar traducciones: primero BD, luego contenido original, luego fallback
$titulo_default = $contenido['titulo'] ?? $t('identitas.our_history', [], 'Nuestra Historia');
$texto_default = $contenido['texto'] ?? '';

$titulo = LanguageService::getContent($idInstancia, 'intro_historia_titulo', null, $titulo_default);
$texto = LanguageService::getContent($idInstancia, 'intro_historia_texto', null, $texto_default);
$anio_fundacion = $contenido['anio_fundacion'] ?? '';
?>

<div class="py-12 text-center max-w-3xl mx-auto">
    <?php if ($anio_fundacion): ?>
    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full mb-6" style="background: var(--color-primario); opacity: 0.1;">
        <i data-lucide="calendar" class="w-5 h-5" style="color: var(--color-primario);"></i>
        <span class="font-semibold" style="color: var(--color-primario);"><?php echo $t('identitas.since_year', [], 'Desde'); ?> <?php echo htmlspecialchars($anio_fundacion); ?></span>
    </div>
    <?php endif; ?>

    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-6">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>

    <div class="text-lg text-gray-600 dark:text-gray-400 prose dark:prose-invert prose-lg mx-auto">
        <?php echo $texto; ?>
    </div>
</div>
