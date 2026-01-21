<?php
/**
 * Bloque: Estadísticas
 * Bloque de estadísticas reutilizable
 */

$items = $contenido['items'] ?? [];
$style = $config['style'] ?? 'boxes';
$prefix = $config['prefix'] ?? '';
?>

<?php if ($prefix): ?>
<p class="text-center text-gray-600 dark:text-gray-400 mb-6 font-medium">
    <?php echo htmlspecialchars($prefix); ?>
</p>
<?php endif; ?>

<?php if ($style === 'inline'): ?>
<!-- Estilo Inline -->
<div class="flex flex-wrap justify-center gap-8 py-8">
    <?php foreach ($items as $stat): ?>
    <div class="text-center">
        <span class="text-4xl font-bold" style="color: var(--color-primario);">
            <?php echo htmlspecialchars($stat['titulo'] ?? ''); ?>
        </span>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            <?php echo htmlspecialchars($stat['texto'] ?? ''); ?>
        </p>
    </div>
    <?php endforeach; ?>
</div>

<?php elseif ($style === 'animated'): ?>
<!-- Estilo Animado -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 py-8">
    <?php foreach ($items as $stat): ?>
    <div class="text-center p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition">
        <span class="text-5xl font-bold block mb-2" style="color: var(--color-primario);">
            <?php echo htmlspecialchars($stat['titulo'] ?? ''); ?>
        </span>
        <p class="text-gray-600 dark:text-gray-400">
            <?php echo htmlspecialchars($stat['texto'] ?? ''); ?>
        </p>
    </div>
    <?php endforeach; ?>
</div>

<?php else: ?>
<!-- Estilo Boxes (default) -->
<div class="py-12">
    <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <?php foreach ($items as $stat): ?>
            <div>
                <span class="text-4xl md:text-5xl font-bold" style="color: var(--color-primario);">
                    <?php echo htmlspecialchars($stat['titulo'] ?? ''); ?>
                </span>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    <?php echo htmlspecialchars($stat['texto'] ?? ''); ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
