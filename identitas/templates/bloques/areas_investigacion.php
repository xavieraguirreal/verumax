<?php
/**
 * Bloque: Áreas de Investigación
 * Template: Académico para Sobre Nosotros
 */

$titulo = $contenido['titulo'] ?? 'Áreas de Investigación';
$areas = $contenido['areas'] ?? [];
?>

<div class="py-12">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-10">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($areas as $area): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-lg transition border border-gray-100 dark:border-gray-700 group">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-4 transition group-hover:scale-110"
                 style="background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));">
                <i data-lucide="<?php echo htmlspecialchars($area['icono'] ?? 'book-open'); ?>" class="w-7 h-7 text-white"></i>
            </div>

            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">
                <?php echo htmlspecialchars($area['nombre'] ?? ''); ?>
            </h3>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                <?php echo htmlspecialchars($area['descripcion'] ?? ''); ?>
            </p>

            <?php if (!empty($area['link'])): ?>
            <a href="<?php echo htmlspecialchars($area['link']); ?>"
               class="inline-flex items-center gap-1 text-sm font-medium hover:underline"
               style="color: var(--color-primario);">
                Saber más
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
