<?php
/**
 * Bloque: Introducción Académica
 * Template: Académico para Sobre Nosotros
 */

$titulo = $contenido['titulo'] ?? 'Sobre Nosotros';
$texto = $contenido['texto'] ?? '';
$imagen = $contenido['imagen'] ?? '';
$cita = $contenido['cita'] ?? '';
$autor_cita = $contenido['autor_cita'] ?? '';
?>

<div class="py-12">
    <div class="grid md:grid-cols-2 gap-12 items-center">
        <!-- Texto -->
        <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                <?php echo htmlspecialchars($titulo); ?>
            </h2>
            <div class="text-gray-600 dark:text-gray-400 prose dark:prose-invert prose-lg">
                <?php echo $texto; ?>
            </div>
        </div>

        <!-- Imagen + Cita -->
        <div class="space-y-6">
            <?php if ($imagen): ?>
            <div class="rounded-xl overflow-hidden shadow-lg">
                <img src="<?php echo htmlspecialchars($imagen); ?>"
                     alt="<?php echo htmlspecialchars($titulo); ?>"
                     class="w-full h-auto">
            </div>
            <?php endif; ?>

            <?php if ($cita): ?>
            <blockquote class="border-l-4 pl-6 py-4 bg-gray-50 dark:bg-gray-800 rounded-r-xl" style="border-color: var(--color-primario);">
                <p class="text-lg italic text-gray-700 dark:text-gray-300">
                    "<?php echo htmlspecialchars($cita); ?>"
                </p>
                <?php if ($autor_cita): ?>
                <cite class="mt-3 block text-sm font-medium" style="color: var(--color-primario);">
                    — <?php echo htmlspecialchars($autor_cita); ?>
                </cite>
                <?php endif; ?>
            </blockquote>
            <?php endif; ?>
        </div>
    </div>
</div>
