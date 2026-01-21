<?php
/**
 * IDENTITAS - Template Page
 * Página genérica personalizable con sistema de templates y bloques
 */

use VERUMax\Services\TemplateService;

// Obtener colores de la instancia
$colores = [
    'primario' => $instance['color_primario'] ?? '#D4AF37',
    'secundario' => $instance['color_secundario'] ?? '#2E7D32',
];

// Intentar renderizar con sistema de templates
$id_instancia = $instance['id_instancia'];
$page_slug = $pagina['slug'];

// Verificar si existe un template para esta página
$template_data = TemplateService::getPageRenderData($id_instancia, $page_slug);

if ($template_data['template'] && !empty($template_data['bloques'])) {
    // Renderizar usando sistema de templates y bloques
    echo TemplateService::renderPage($id_instancia, $page_slug, $colores);

} else {
    // Fallback: renderizar contenido HTML tradicional
    ?>
    <section class="py-16 bg-white dark:bg-gray-900">
        <div class="container mx-auto px-6 max-w-4xl">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">
                <?php echo htmlspecialchars($pagina['titulo']); ?>
            </h1>

            <div class="prose max-w-none dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-white prose-p:text-gray-600 dark:prose-p:text-gray-400">
                <?php echo $pagina['contenido']; ?>
            </div>

            <!-- Formulario de contacto si es la página de contacto -->
            <?php if ($pagina['slug'] == 'contacto'): ?>
                <div class="mt-12 max-w-2xl">
                    <form method="POST" action="?page=contacto&action=enviar" class="space-y-6 bg-gray-50 dark:bg-gray-800 p-8 rounded-lg">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Nombre completo *
                            </label>
                            <input type="text" name="nombre" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primario dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Email *
                            </label>
                            <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primario dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Teléfono
                            </label>
                            <input type="tel" name="telefono" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primario dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Asunto
                            </label>
                            <input type="text" name="asunto" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primario dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                Mensaje *
                            </label>
                            <textarea name="mensaje" rows="6" required class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-primario dark:bg-gray-700 dark:text-white"></textarea>
                        </div>

                        <button type="submit" class="w-full bg-primario text-white px-8 py-3 rounded-lg font-bold hover:opacity-90 transition inline-flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-5 h-5"></i>
                            Enviar mensaje
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}
?>
