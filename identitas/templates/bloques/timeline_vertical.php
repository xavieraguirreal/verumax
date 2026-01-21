<?php
/**
 * Bloque: Timeline Vertical
 * Template: Timeline para Sobre Nosotros
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Buscar traducción del título en BD
$titulo_default = $contenido['titulo'] ?? $t('identitas.evolution_achievements', [], 'Evolución y Logros');
$titulo = LanguageService::getContent($idInstancia, 'timeline_titulo', null, $titulo_default);
$eventos = $contenido['eventos'] ?? [];
?>

<div class="py-12">
    <?php if ($titulo): ?>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-12">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>
    <?php endif; ?>

    <div class="relative">
        <!-- Línea central -->
        <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-1 rounded-full" style="background: var(--color-primario); opacity: 0.2;"></div>

        <div class="space-y-12">
            <?php foreach ($eventos as $index => $evento): ?>
            <?php $esImpar = $index % 2 === 0; ?>
            <div class="relative flex items-center <?php echo $esImpar ? 'justify-start' : 'justify-end'; ?>">
                <!-- Punto en la línea -->
                <div class="absolute left-1/2 transform -translate-x-1/2 w-5 h-5 rounded-full border-4 border-white dark:border-gray-900 z-10" style="background: var(--color-primario);"></div>

                <!-- Contenido -->
                <div class="w-5/12 <?php echo $esImpar ? 'pr-8 text-right' : 'pl-8 text-left'; ?>">
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg border border-gray-100 dark:border-gray-700 hover:shadow-xl transition">
                        <!-- Año -->
                        <span class="inline-block px-3 py-1 text-sm font-bold rounded-full mb-3" style="background: var(--color-primario); color: white;">
                            <?php echo htmlspecialchars($evento['anio'] ?? ''); ?>
                        </span>

                        <!-- Título -->
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">
                            <?php echo htmlspecialchars($evento['titulo'] ?? ''); ?>
                        </h3>

                        <!-- Descripción -->
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <?php echo htmlspecialchars($evento['descripcion'] ?? ''); ?>
                        </p>

                        <!-- Imagen -->
                        <?php if (!empty($evento['imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($evento['imagen']); ?>"
                             alt="<?php echo htmlspecialchars($evento['titulo'] ?? ''); ?>"
                             class="mt-4 rounded-lg w-full h-32 object-cover">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .timeline_vertical .relative > div:not(.absolute) {
        width: 100% !important;
        padding-left: 2rem !important;
        padding-right: 0 !important;
        text-align: left !important;
        justify-content: flex-start !important;
    }
    .timeline_vertical .absolute.left-1\/2 {
        left: 0 !important;
        transform: none !important;
    }
}
</style>
