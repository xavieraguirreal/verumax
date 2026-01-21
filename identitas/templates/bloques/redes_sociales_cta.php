<?php
/**
 * Bloque: Redes Sociales CTA
 * Template: Botones prominentes para seguir en redes sociales
 *
 * Variables disponibles:
 * - $instance: array con la configuración de la instancia
 * - $contenido: array con el contenido del bloque (opcional)
 * - $colores: array con colores de la instancia
 *
 * Redes soportadas:
 * - instagram, facebook, linkedin, twitter, youtube, tiktok, whatsapp
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Obtener redes sociales de la instancia
$redesSociales = [];
if (!empty($instance['redes_sociales'])) {
    if (is_string($instance['redes_sociales'])) {
        $redesSociales = json_decode($instance['redes_sociales'], true) ?: [];
    } else {
        $redesSociales = $instance['redes_sociales'];
    }
}

// También revisar en contenido del bloque
if (!empty($contenido['redes_sociales'])) {
    $redesBloque = is_string($contenido['redes_sociales'])
        ? json_decode($contenido['redes_sociales'], true) ?: []
        : $contenido['redes_sociales'];
    $redesSociales = array_merge($redesSociales, $redesBloque);
}

// Si no hay redes, no mostrar el bloque
if (empty($redesSociales)) {
    return;
}

// Título personalizable
$titulo = $contenido['titulo'] ?? $t('identitas.social_follow_us', [], 'Seguinos');
$subtitulo = $contenido['subtitulo'] ?? $t('identitas.social_connect', [], 'Conectá con nosotros en redes sociales');

// Configuración de cada red social
$redesConfig = [
    'instagram' => [
        'icon' => 'instagram',
        'label' => 'Instagram',
        'color_bg' => 'bg-gradient-to-br from-purple-600 via-pink-500 to-orange-400',
        'color_hover' => 'hover:from-purple-700 hover:via-pink-600 hover:to-orange-500',
    ],
    'facebook' => [
        'icon' => 'facebook',
        'label' => 'Facebook',
        'color_bg' => 'bg-blue-600',
        'color_hover' => 'hover:bg-blue-700',
    ],
    'linkedin' => [
        'icon' => 'linkedin',
        'label' => 'LinkedIn',
        'color_bg' => 'bg-blue-700',
        'color_hover' => 'hover:bg-blue-800',
    ],
    'twitter' => [
        'icon' => 'twitter',
        'label' => 'X / Twitter',
        'color_bg' => 'bg-gray-900',
        'color_hover' => 'hover:bg-black',
    ],
    'x' => [
        'icon' => 'twitter',
        'label' => 'X',
        'color_bg' => 'bg-gray-900',
        'color_hover' => 'hover:bg-black',
    ],
    'youtube' => [
        'icon' => 'youtube',
        'label' => 'YouTube',
        'color_bg' => 'bg-red-600',
        'color_hover' => 'hover:bg-red-700',
    ],
    'tiktok' => [
        'icon' => 'music-2',
        'label' => 'TikTok',
        'color_bg' => 'bg-gray-900',
        'color_hover' => 'hover:bg-black',
    ],
    'whatsapp' => [
        'icon' => 'message-circle',
        'label' => 'WhatsApp',
        'color_bg' => 'bg-green-500',
        'color_hover' => 'hover:bg-green-600',
    ],
    'telegram' => [
        'icon' => 'send',
        'label' => 'Telegram',
        'color_bg' => 'bg-sky-500',
        'color_hover' => 'hover:bg-sky-600',
    ],
];

// Estilo de diseño (cards o circles)
$estilo = $contenido['estilo'] ?? 'circles'; // circles | cards

?>

<section id="redes-sociales" class="py-16 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <div class="container mx-auto px-6">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4 shadow-lg"
                 style="background: linear-gradient(135deg, var(--color-primario), var(--color-acento));">
                <i data-lucide="share-2" class="w-8 h-8 text-white"></i>
            </div>
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mb-4">
                <?php echo htmlspecialchars($titulo); ?>
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                <?php echo htmlspecialchars($subtitulo); ?>
            </p>
        </div>

        <!-- Botones de redes sociales -->
        <?php if ($estilo === 'circles'): ?>
        <!-- Estilo Círculos -->
        <div class="flex flex-wrap justify-center gap-6">
            <?php foreach ($redesSociales as $red => $url):
                if (empty($url)) continue;
                $redKey = strtolower($red);
                $config = $redesConfig[$redKey] ?? null;
                if (!$config) continue;
            ?>
            <a href="<?php echo htmlspecialchars($url); ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="group flex flex-col items-center gap-3 p-4 rounded-2xl transition-all duration-300 hover:scale-110"
               title="Seguinos en <?php echo htmlspecialchars($config['label']); ?>">
                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full flex items-center justify-center text-white shadow-lg transition-all duration-300 group-hover:shadow-xl <?php echo $config['color_bg']; ?> <?php echo $config['color_hover']; ?>">
                    <i data-lucide="<?php echo $config['icon']; ?>" class="w-8 h-8 md:w-10 md:h-10"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                    <?php echo htmlspecialchars($config['label']); ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <!-- Estilo Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <?php foreach ($redesSociales as $red => $url):
                if (empty($url)) continue;
                $redKey = strtolower($red);
                $config = $redesConfig[$redKey] ?? null;
                if (!$config) continue;
            ?>
            <a href="<?php echo htmlspecialchars($url); ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="group flex items-center gap-4 p-4 rounded-xl bg-white dark:bg-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-200 dark:border-gray-700"
               title="Seguinos en <?php echo htmlspecialchars($config['label']); ?>">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white flex-shrink-0 <?php echo $config['color_bg']; ?>">
                    <i data-lucide="<?php echo $config['icon']; ?>" class="w-6 h-6"></i>
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($config['label']); ?>
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        <?php echo $t('identitas.social_follow', [], 'Seguir'); ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>
