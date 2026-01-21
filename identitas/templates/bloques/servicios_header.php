<?php
/**
 * Bloque: Header de Servicios
 * Compartido por varios templates de Servicios
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

$titulo = $contenido['titulo'] ?? $t('identitas.our_services', [], 'Nuestros Servicios');
$subtitulo = $contenido['subtitulo'] ?? '';
$imagen = $contenido['imagen'] ?? '';
$style = $config['style'] ?? 'centered';
?>

<?php if ($style === 'minimal'): ?>
<!-- Estilo Minimal -->
<div class="py-8 border-b border-gray-200 dark:border-gray-700 mb-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        <?php echo htmlspecialchars($titulo); ?>
    </h1>
    <?php if ($subtitulo): ?>
    <p class="mt-2 text-gray-600 dark:text-gray-400">
        <?php echo htmlspecialchars($subtitulo); ?>
    </p>
    <?php endif; ?>
</div>

<?php elseif ($style === 'left-aligned'): ?>
<!-- Estilo Alineado Izquierda -->
<div class="py-12 mb-8">
    <div class="max-w-2xl">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">
            <?php echo htmlspecialchars($titulo); ?>
        </h1>
        <?php if ($subtitulo): ?>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
            <?php echo htmlspecialchars($subtitulo); ?>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<!-- Estilo Centrado (default) -->
<div class="py-16 text-center mb-8 rounded-xl <?php echo $imagen ? '' : 'bg-gray-50 dark:bg-gray-800'; ?>"
     <?php if ($imagen): ?>style="background: url('<?php echo htmlspecialchars($imagen); ?>') center/cover; position: relative;"<?php endif; ?>>

    <?php if ($imagen): ?>
    <div class="absolute inset-0 bg-black/50 rounded-xl"></div>
    <?php endif; ?>

    <div class="relative z-10 max-w-3xl mx-auto px-6">
        <h1 class="text-4xl md:text-5xl font-bold <?php echo $imagen ? 'text-white' : 'text-gray-900 dark:text-white'; ?>">
            <?php echo htmlspecialchars($titulo); ?>
        </h1>
        <?php if ($subtitulo): ?>
        <p class="mt-4 text-lg <?php echo $imagen ? 'text-white/90' : 'text-gray-600 dark:text-gray-400'; ?>">
            <?php echo htmlspecialchars($subtitulo); ?>
        </p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
