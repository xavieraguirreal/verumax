<?php
/**
 * Bloque: Publicaciones
 * Template: Académico para Sobre Nosotros
 */

$titulo = $contenido['titulo'] ?? 'Publicaciones';
$items = $contenido['items'] ?? [];
?>

<div class="py-12">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-10">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>

    <div class="space-y-4 max-w-4xl mx-auto">
        <?php foreach ($items as $pub): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-100 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center" style="background: var(--color-primario); opacity: 0.1;">
                    <i data-lucide="file-text" class="w-5 h-5" style="color: var(--color-primario);"></i>
                </div>

                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                        <?php if (!empty($pub['link'])): ?>
                        <a href="<?php echo htmlspecialchars($pub['link']); ?>"
                           target="_blank"
                           class="hover:underline"
                           style="color: var(--color-primario);">
                            <?php echo htmlspecialchars($pub['titulo'] ?? ''); ?>
                        </a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($pub['titulo'] ?? ''); ?>
                        <?php endif; ?>
                    </h3>

                    <?php if (!empty($pub['autores'])): ?>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        <?php echo htmlspecialchars($pub['autores']); ?>
                    </p>
                    <?php endif; ?>

                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-500 dark:text-gray-500">
                        <?php if (!empty($pub['revista'])): ?>
                        <span class="flex items-center gap-1">
                            <i data-lucide="book" class="w-4 h-4"></i>
                            <?php echo htmlspecialchars($pub['revista']); ?>
                        </span>
                        <?php endif; ?>

                        <?php if (!empty($pub['fecha'])): ?>
                        <span class="flex items-center gap-1">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            <?php echo htmlspecialchars($pub['fecha']); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
