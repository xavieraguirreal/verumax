<?php
/**
 * Bloque: Grid de Servicios con Iconos
 * Template: Grid para Servicios
 *
 * Variables disponibles:
 * - $contenido: array con el contenido del bloque
 * - $config: array con la configuración (cols, con_iconos)
 * - $colores: array con colores de la instancia
 */

// Normalizado: usar 'titulo' en todos los bloques
$titulo = $contenido['titulo'] ?? $contenido['titulo_seccion'] ?? 'Nuestros Servicios';
$subtitulo = $contenido['subtitulo'] ?? '';
$items = $contenido['items'] ?? [];
$cols = $config['cols'] ?? 3;

// Clase de columnas según configuración
$colsClass = match($cols) {
    2 => 'md:grid-cols-2',
    3 => 'md:grid-cols-3',
    4 => 'md:grid-cols-4',
    default => 'md:grid-cols-3'
};

// Colores para iconos (rotan)
$iconColors = [
    ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-600 dark:text-green-400'],
    ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-600 dark:text-blue-400'],
    ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-600 dark:text-purple-400'],
    ['bg' => 'bg-orange-100 dark:bg-orange-900/30', 'text' => 'text-orange-600 dark:text-orange-400'],
    ['bg' => 'bg-pink-100 dark:bg-pink-900/30', 'text' => 'text-pink-600 dark:text-pink-400'],
    ['bg' => 'bg-indigo-100 dark:bg-indigo-900/30', 'text' => 'text-indigo-600 dark:text-indigo-400'],
];
?>

<div class="text-center mb-12">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>
    <?php if ($subtitulo): ?>
    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
        <?php echo htmlspecialchars($subtitulo); ?>
    </p>
    <?php endif; ?>
</div>

<div class="grid <?php echo $colsClass; ?> gap-8">
    <?php foreach ($items as $index => $item):
        $colorIndex = $index % count($iconColors);
        $iconColor = $iconColors[$colorIndex];
        $icono = $item['icono'] ?? 'star';
    ?>
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700 hover:shadow-md transition-shadow">
        <?php if ($config['con_iconos'] ?? true): ?>
        <div class="w-12 h-12 <?php echo $iconColor['bg']; ?> rounded-lg flex items-center justify-center mb-4">
            <i data-lucide="<?php echo htmlspecialchars($icono); ?>" class="w-6 h-6 <?php echo $iconColor['text']; ?>"></i>
        </div>
        <?php endif; ?>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
            <?php echo htmlspecialchars($item['titulo'] ?? ''); ?>
        </h3>
        <p class="text-gray-600 dark:text-gray-400">
            <?php echo htmlspecialchars($item['texto'] ?? ''); ?>
        </p>
    </div>
    <?php endforeach; ?>
</div>
