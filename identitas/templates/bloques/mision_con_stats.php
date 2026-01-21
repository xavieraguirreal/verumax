<?php
/**
 * Bloque: Misión + Estadísticas (Layout 2 columnas)
 * Template: Clásico para Sobre Nosotros
 *
 * Variables disponibles:
 * - $contenido: array con el contenido del bloque
 * - $config: array con la configuración del bloque
 * - $colores: array con colores de la instancia
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// El contenido puede venir de dos formas:
// 1. Como un objeto combinado $contenido['mision'] y $contenido['stats']
// 2. Como contenido directo con keys 'titulo', 'texto', etc.

$mision_fallback = $t('identitas.about_mission', [], 'Misión');

// Intentar obtener de la estructura combinada primero
if (isset($contenido['mision'])) {
    // Estructura combinada
    $mision = $contenido['mision'];
    $mision_titulo = $mision['titulo'] ?? $mision_fallback;
    $mision_texto = $mision['texto'] ?? '';
    $mision_link_texto = $mision['link_texto'] ?? '';
    $mision_link_url = $mision['link_url'] ?? '';
} else {
    // Estructura directa (viene directo del contenido guardado)
    $mision_titulo = $contenido['titulo'] ?? $mision_fallback;
    $mision_texto = $contenido['texto'] ?? '';
    $mision_link_texto = $contenido['link_texto'] ?? '';
    $mision_link_url = $contenido['link_url'] ?? '';
}

// Obtener estadísticas (también puede venir de dos formas)
if (isset($contenido['stats']['items'])) {
    $stats = $contenido['stats']['items'];
} elseif (isset($contenido['items'])) {
    $stats = $contenido['items'];
} else {
    $stats = [];
}
?>

<div class="grid md:grid-cols-2 gap-12 items-center">
    <!-- Columna izquierda: Misión -->
    <div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
            <?php echo htmlspecialchars($mision_titulo); ?>
        </h2>
        <div class="mt-4 text-base text-gray-600 dark:text-gray-400 leading-relaxed prose dark:prose-invert">
            <?php echo $mision_texto; // Ya viene con HTML del editor ?>
        </div>
        <?php if ($mision_link_texto && $mision_link_url): ?>
        <a href="<?php echo htmlspecialchars($mision_link_url); ?>"
           target="_blank"
           class="mt-6 inline-flex items-center gap-1 font-semibold hover:underline"
           style="color: var(--color-primario);">
            <?php echo htmlspecialchars($mision_link_texto); ?>
            <i data-lucide="arrow-right" class="inline w-4 h-4"></i>
        </a>
        <?php endif; ?>
    </div>

    <!-- Columna derecha: Estadísticas 2x2 -->
    <div class="relative">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-8 border dark:border-gray-700">
            <div class="grid grid-cols-2 gap-8 text-center">
                <?php foreach ($stats as $stat): ?>
                <div>
                    <span class="text-4xl font-bold" style="color: var(--color-primario);">
                        <?php echo htmlspecialchars($stat['titulo'] ?? ''); ?>
                    </span>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                        <?php echo htmlspecialchars($stat['texto'] ?? ''); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
