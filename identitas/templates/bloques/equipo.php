<?php
/**
 * Bloque: Equipo
 * Template: Corporativo para Sobre Nosotros
 */

$titulo = $contenido['titulo'] ?? 'Nuestro Equipo';
$subtitulo = $contenido['subtitulo'] ?? '';
$miembros = $contenido['miembros'] ?? [];
?>

<div class="py-12">
    <div class="text-center mb-10">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
            <?php echo htmlspecialchars($titulo); ?>
        </h2>
        <?php if ($subtitulo): ?>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            <?php echo htmlspecialchars($subtitulo); ?>
        </p>
        <?php endif; ?>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php foreach ($miembros as $miembro): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition group">
            <!-- Foto -->
            <div class="aspect-square bg-gray-100 dark:bg-gray-700 overflow-hidden">
                <?php if (!empty($miembro['foto'])): ?>
                <img src="<?php echo htmlspecialchars($miembro['foto']); ?>"
                     alt="<?php echo htmlspecialchars($miembro['nombre'] ?? ''); ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center">
                    <i data-lucide="user" class="w-20 h-20 text-gray-300 dark:text-gray-600"></i>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="p-6">
                <h3 class="font-bold text-lg text-gray-900 dark:text-white">
                    <?php echo htmlspecialchars($miembro['nombre'] ?? ''); ?>
                </h3>
                <p class="text-sm font-medium mt-1" style="color: var(--color-primario);">
                    <?php echo htmlspecialchars($miembro['cargo'] ?? ''); ?>
                </p>
                <?php if (!empty($miembro['bio'])): ?>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                    <?php echo htmlspecialchars($miembro['bio']); ?>
                </p>
                <?php endif; ?>

                <?php if (!empty($miembro['linkedin'])): ?>
                <a href="<?php echo htmlspecialchars($miembro['linkedin']); ?>"
                   target="_blank"
                   class="mt-4 inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800">
                    <i data-lucide="linkedin" class="w-4 h-4"></i>
                    LinkedIn
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
