<?php
/**
 * Bloque: Estadísticas Horizontales (4 columnas)
 * Template: Moderno para Sobre Nosotros
 *
 * Variables disponibles:
 * - $contenido: array con el contenido del bloque
 * - $config: array con la configuración del bloque
 * - $colores: array con colores de la instancia
 */

// Obtener estadísticas
$stats = $contenido['items'] ?? [];
$cols = $config['cols'] ?? 4;

// Definir clases de columnas según cantidad
$col_classes = [
    2 => 'grid-cols-1 md:grid-cols-2',
    3 => 'grid-cols-1 md:grid-cols-3',
    4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    6 => 'grid-cols-2 md:grid-cols-3 lg:grid-cols-6'
];
$grid_class = $col_classes[$cols] ?? 'grid-cols-1 md:grid-cols-4';
?>

<div class="mb-16">
    <div class="grid <?php echo $grid_class; ?> gap-8">
        <?php foreach ($stats as $stat): ?>
        <div class="text-center">
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 border border-gray-200 dark:border-gray-700">
                <h3 class="text-2xl font-bold mb-3" style="color: var(--color-primario);">
                    <?php echo htmlspecialchars($stat['titulo'] ?? ''); ?>
                </h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                    <?php echo htmlspecialchars($stat['texto'] ?? ''); ?>
                </p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
