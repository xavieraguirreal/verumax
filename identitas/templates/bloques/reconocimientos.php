<?php
/**
 * Bloque: Reconocimientos
 * Template: Académico para Sobre Nosotros
 */

$titulo = $contenido['titulo'] ?? 'Reconocimientos';
$items = $contenido['items'] ?? [];
?>

<div class="py-12 bg-gray-50 dark:bg-gray-900 rounded-xl">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-10">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 px-6">
        <?php foreach ($items as $item): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm hover:shadow-md transition">
            <?php if (!empty($item['imagen'])): ?>
            <img src="<?php echo htmlspecialchars($item['imagen']); ?>"
                 alt="<?php echo htmlspecialchars($item['nombre'] ?? ''); ?>"
                 class="w-20 h-20 mx-auto mb-4 object-contain">
            <?php else: ?>
            <div class="w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center" style="background: var(--color-acento); opacity: 0.2;">
                <i data-lucide="award" class="w-10 h-10" style="color: var(--color-primario);"></i>
            </div>
            <?php endif; ?>

            <h3 class="font-bold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($item['nombre'] ?? ''); ?>
            </h3>

            <?php if (!empty($item['otorgante'])): ?>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                <?php echo htmlspecialchars($item['otorgante']); ?>
            </p>
            <?php endif; ?>

            <?php if (!empty($item['anio'])): ?>
            <span class="inline-block mt-3 px-3 py-1 text-xs font-medium rounded-full" style="background: var(--color-primario); color: white;">
                <?php echo htmlspecialchars($item['anio']); ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
