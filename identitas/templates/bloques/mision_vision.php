<?php
/**
 * Bloque: Misión, Visión y Valores
 * Template: Corporativo para Sobre Nosotros
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

$mision_titulo = $contenido['mision_titulo'] ?? $t('identitas.about_mission', [], 'Misión');
$mision_texto = $contenido['mision_texto'] ?? '';
$vision_titulo = $contenido['vision_titulo'] ?? $t('identitas.about_vision', [], 'Visión');
$vision_texto = $contenido['vision_texto'] ?? '';
$valores_titulo = $contenido['valores_titulo'] ?? $t('identitas.about_values', [], 'Valores');
$valores = $contenido['valores'] ?? [];
?>

<div class="py-12">
    <!-- Misión y Visión en 2 columnas -->
    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <!-- Misión -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-8 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background: var(--color-primario);">
                    <i data-lucide="target" class="w-6 h-6 text-white"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <?php echo htmlspecialchars($mision_titulo); ?>
                </h3>
            </div>
            <div class="text-gray-600 dark:text-gray-400 prose dark:prose-invert">
                <?php echo $mision_texto; ?>
            </div>
        </div>

        <!-- Visión -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-8 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background: var(--color-secundario);">
                    <i data-lucide="eye" class="w-6 h-6 text-white"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <?php echo htmlspecialchars($vision_titulo); ?>
                </h3>
            </div>
            <div class="text-gray-600 dark:text-gray-400 prose dark:prose-invert">
                <?php echo $vision_texto; ?>
            </div>
        </div>
    </div>

    <!-- Valores -->
    <?php if (!empty($valores)): ?>
    <div class="mt-12">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-8">
            <?php echo htmlspecialchars($valores_titulo); ?>
        </h3>
        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($valores as $valor): ?>
            <div class="text-center p-6 bg-gray-50 dark:bg-gray-800 rounded-xl hover:shadow-md transition">
                <div class="w-14 h-14 mx-auto rounded-full flex items-center justify-center mb-4" style="background: var(--color-acento); opacity: 0.2;">
                    <i data-lucide="<?php echo htmlspecialchars($valor['icono'] ?? 'star'); ?>" class="w-7 h-7" style="color: var(--color-primario);"></i>
                </div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">
                    <?php echo htmlspecialchars($valor['nombre'] ?? ''); ?>
                </h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <?php echo htmlspecialchars($valor['descripcion'] ?? ''); ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
