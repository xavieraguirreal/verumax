<?php
/**
 * Bloque: Servicios en Acordeón
 * Template: Lista Detallada para Servicios
 */

$items = $contenido['items'] ?? [];
?>

<div class="py-8 space-y-4" x-data="{ open: 0 }">
    <?php foreach ($items as $index => $item): ?>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header del acordeón -->
        <button class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                onclick="this.parentElement.classList.toggle('accordion-open')">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--color-primario); opacity: 0.1;">
                    <i data-lucide="<?php echo htmlspecialchars($item['icono'] ?? 'briefcase'); ?>" class="w-5 h-5" style="color: var(--color-primario);"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($item['titulo'] ?? ''); ?>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <?php echo htmlspecialchars($item['descripcion_corta'] ?? ''); ?>
                    </p>
                </div>
            </div>
            <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 accordion-icon transition-transform"></i>
        </button>

        <!-- Contenido del acordeón -->
        <div class="accordion-content px-6 pb-6 hidden">
            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 prose dark:prose-invert max-w-none">
                <?php echo $item['contenido'] ?? ''; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
.accordion-open .accordion-content {
    display: block !important;
}
.accordion-open .accordion-icon {
    transform: rotate(180deg);
}
</style>

<script>
// Inicializar primer item abierto
document.addEventListener('DOMContentLoaded', function() {
    const firstAccordion = document.querySelector('.servicios_accordion .bg-white');
    if (firstAccordion) {
        firstAccordion.classList.add('accordion-open');
    }
});
</script>
