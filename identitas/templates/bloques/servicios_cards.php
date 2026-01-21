<?php
/**
 * Bloque: Servicios en Cards
 * Template: Cards con Iconos para Servicios
 */

$items = $contenido['items'] ?? [];
$columns = $config['columns'] ?? 3;
$hover_effect = $config['hover_effect'] ?? 'lift';
$icon_size = $config['icon_size'] ?? 'large';

$iconSizeClass = $icon_size === 'large' ? 'w-12 h-12' : 'w-8 h-8';
$iconContainerClass = $icon_size === 'large' ? 'w-16 h-16' : 'w-12 h-12';
?>

<div class="grid md:grid-cols-2 lg:grid-cols-<?php echo $columns; ?> gap-6 py-8">
    <?php foreach ($items as $item): ?>
    <?php $destacado = !empty($item['destacado']); ?>
    <div class="relative bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border
                <?php echo $destacado ? 'border-2' : 'border-gray-100 dark:border-gray-700'; ?>
                <?php echo $hover_effect === 'lift' ? 'hover:-translate-y-2 hover:shadow-xl' : 'hover:shadow-lg'; ?>
                transition-all duration-300 group"
         <?php if ($destacado): ?>style="border-color: var(--color-primario);"<?php endif; ?>>

        <?php if ($destacado): ?>
        <span class="absolute -top-3 right-4 px-3 py-1 text-xs font-bold text-white rounded-full" style="background: var(--color-primario);">
            Destacado
        </span>
        <?php endif; ?>

        <!-- Icono -->
        <div class="<?php echo $iconContainerClass; ?> rounded-xl flex items-center justify-center mb-4 transition group-hover:scale-110"
             style="background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));">
            <i data-lucide="<?php echo htmlspecialchars($item['icono'] ?? 'star'); ?>" class="<?php echo $iconSizeClass; ?> text-white"></i>
        </div>

        <!-- Título -->
        <h3 class="font-bold text-xl text-gray-900 dark:text-white mb-3">
            <?php echo htmlspecialchars($item['titulo'] ?? ''); ?>
        </h3>

        <!-- Descripción -->
        <p class="text-gray-600 dark:text-gray-400 mb-4">
            <?php echo htmlspecialchars($item['descripcion'] ?? ''); ?>
        </p>

        <!-- Link -->
        <?php if (!empty($item['link'])): ?>
        <a href="<?php echo htmlspecialchars($item['link']); ?>"
           class="inline-flex items-center gap-2 font-medium hover:gap-3 transition-all"
           style="color: var(--color-primario);">
            Más información
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
