<?php
/**
 * Bloque: FAQ de Servicios
 * Preguntas frecuentes para la página de Servicios
 */

$titulo = $contenido['titulo'] ?? 'Preguntas Frecuentes';
$preguntas = $contenido['preguntas'] ?? [];
?>

<div class="py-12">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-10">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>

    <div class="max-w-3xl mx-auto space-y-4">
        <?php foreach ($preguntas as $index => $faq): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button class="w-full px-6 py-5 flex items-center justify-between text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    onclick="this.parentElement.classList.toggle('faq-open')">
                <span class="font-semibold text-gray-900 dark:text-white pr-4">
                    <?php echo htmlspecialchars($faq['pregunta'] ?? ''); ?>
                </span>
                <i data-lucide="plus" class="w-5 h-5 text-gray-400 flex-shrink-0 faq-icon-plus"></i>
                <i data-lucide="minus" class="w-5 h-5 text-gray-400 flex-shrink-0 faq-icon-minus hidden"></i>
            </button>

            <div class="faq-answer hidden px-6 pb-6">
                <div class="pt-2 text-gray-600 dark:text-gray-400 prose dark:prose-invert max-w-none">
                    <?php echo $faq['respuesta'] ?? ''; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.faq-open .faq-answer {
    display: block !important;
}
.faq-open .faq-icon-plus {
    display: none !important;
}
.faq-open .faq-icon-minus {
    display: block !important;
}
</style>
