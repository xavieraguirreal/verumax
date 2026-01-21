<?php
/**
 * Bloque: CTA de Servicios
 * Call to Action para la página de Servicios
 */

$titulo = $contenido['titulo'] ?? '¿Listo para empezar?';
$texto = $contenido['texto'] ?? '';
$boton_texto = $contenido['boton_texto'] ?? 'Contactanos';
$boton_url = $contenido['boton_url'] ?? '/contacto';
$estilo = $contenido['estilo'] ?? $config['style'] ?? 'banner';
?>

<?php if ($estilo === 'banner'): ?>
<!-- Estilo Banner -->
<div class="py-12 mt-8">
    <div class="rounded-2xl p-10 text-center text-white relative overflow-hidden"
         style="background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));">
        <!-- Patrón decorativo -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-40 h-40 rounded-full bg-white transform -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-60 h-60 rounded-full bg-white transform translate-x-1/2 translate-y-1/2"></div>
        </div>

        <div class="relative z-10 max-w-2xl mx-auto">
            <h3 class="text-3xl font-bold mb-4">
                <?php echo htmlspecialchars($titulo); ?>
            </h3>
            <?php if ($texto): ?>
            <p class="text-lg text-white/90 mb-6">
                <?php echo htmlspecialchars($texto); ?>
            </p>
            <?php endif; ?>
            <a href="<?php echo htmlspecialchars($boton_url); ?>"
               class="inline-flex items-center gap-2 px-8 py-4 bg-white font-bold rounded-xl hover:bg-gray-100 transition"
               style="color: var(--color-primario);">
                <?php echo htmlspecialchars($boton_texto); ?>
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </a>
        </div>
    </div>
</div>

<?php elseif ($estilo === 'card'): ?>
<!-- Estilo Card -->
<div class="py-8 mt-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-8 shadow-lg border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                <?php echo htmlspecialchars($titulo); ?>
            </h3>
            <?php if ($texto): ?>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                <?php echo htmlspecialchars($texto); ?>
            </p>
            <?php endif; ?>
        </div>
        <a href="<?php echo htmlspecialchars($boton_url); ?>"
           class="flex-shrink-0 inline-flex items-center gap-2 px-6 py-3 text-white font-bold rounded-lg hover:opacity-90 transition"
           style="background: var(--color-primario);">
            <?php echo htmlspecialchars($boton_texto); ?>
            <i data-lucide="arrow-right" class="w-5 h-5"></i>
        </a>
    </div>
</div>

<?php else: ?>
<!-- Estilo Minimal -->
<div class="py-8 mt-8 text-center border-t border-gray-200 dark:border-gray-700">
    <p class="text-gray-600 dark:text-gray-400 mb-4">
        <?php echo htmlspecialchars($texto ?: $titulo); ?>
    </p>
    <a href="<?php echo htmlspecialchars($boton_url); ?>"
       class="inline-flex items-center gap-2 font-medium hover:underline"
       style="color: var(--color-primario);">
        <?php echo htmlspecialchars($boton_texto); ?>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
    </a>
</div>
<?php endif; ?>
