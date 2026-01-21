<?php
/**
 * Bloque: Testimonios de Servicios
 * Carrusel de testimonios para la página de Servicios
 */

$titulo = $contenido['titulo'] ?? 'Lo que dicen nuestros clientes';
$items = $contenido['items'] ?? [];
$layout = $config['layout'] ?? 'carousel';
?>

<div class="py-12 bg-gray-50 dark:bg-gray-900 rounded-xl px-6">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-10">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>

    <?php if ($layout === 'carousel'): ?>
    <!-- Layout Carrusel -->
    <div class="relative overflow-hidden" id="testimonios-carousel">
        <div class="flex transition-transform duration-500" id="testimonios-track">
            <?php foreach ($items as $index => $item): ?>
            <div class="w-full flex-shrink-0 px-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-8 shadow-sm max-w-2xl mx-auto">
                    <!-- Comillas -->
                    <i data-lucide="quote" class="w-10 h-10 mb-4" style="color: var(--color-primario); opacity: 0.3;"></i>

                    <!-- Texto -->
                    <p class="text-lg text-gray-700 dark:text-gray-300 italic mb-6">
                        "<?php echo htmlspecialchars($item['texto'] ?? ''); ?>"
                    </p>

                    <!-- Autor -->
                    <div class="flex items-center gap-4">
                        <?php if (!empty($item['foto'])): ?>
                        <img src="<?php echo htmlspecialchars($item['foto']); ?>"
                             alt="<?php echo htmlspecialchars($item['nombre'] ?? ''); ?>"
                             class="w-14 h-14 rounded-full object-cover">
                        <?php else: ?>
                        <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background: var(--color-primario);">
                            <span class="text-white font-bold text-xl"><?php echo strtoupper(substr($item['nombre'] ?? 'A', 0, 1)); ?></span>
                        </div>
                        <?php endif; ?>
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($item['nombre'] ?? ''); ?>
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <?php echo htmlspecialchars($item['cargo'] ?? ''); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Controles -->
        <?php if (count($items) > 1): ?>
        <div class="flex justify-center gap-2 mt-6">
            <?php foreach ($items as $index => $item): ?>
            <button onclick="goToSlide(<?php echo $index; ?>)"
                    class="w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 carousel-dot <?php echo $index === 0 ? 'active' : ''; ?>"
                    data-index="<?php echo $index; ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
    let currentSlide = 0;
    const totalSlides = <?php echo count($items); ?>;

    function goToSlide(index) {
        currentSlide = index;
        const track = document.getElementById('testimonios-track');
        track.style.transform = `translateX(-${index * 100}%)`;

        // Actualizar dots
        document.querySelectorAll('.carousel-dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === index);
        });
    }

    // Auto-play
    setInterval(() => {
        goToSlide((currentSlide + 1) % totalSlides);
    }, 5000);
    </script>

    <style>
    .carousel-dot.active {
        background: var(--color-primario) !important;
    }
    </style>

    <?php else: ?>
    <!-- Layout Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($items as $item): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
            <i data-lucide="quote" class="w-8 h-8 mb-3" style="color: var(--color-primario); opacity: 0.3;"></i>
            <p class="text-gray-700 dark:text-gray-300 italic mb-4">
                "<?php echo htmlspecialchars($item['texto'] ?? ''); ?>"
            </p>
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                <?php if (!empty($item['foto'])): ?>
                <img src="<?php echo htmlspecialchars($item['foto']); ?>" class="w-10 h-10 rounded-full object-cover">
                <?php endif; ?>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white text-sm"><?php echo htmlspecialchars($item['nombre'] ?? ''); ?></p>
                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($item['cargo'] ?? ''); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
