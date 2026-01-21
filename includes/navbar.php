<?php
/**
 * OriginalisDoc - Navbar Compartido
 * Version: 1.0.0
 *
 * Parámetros esperados:
 * - $page_icon: SVG del icono de la página (string)
 * - $page_subtitle: Subtítulo de la página (string desde $lang)
 * - $nav_links: Array de links de navegación [['href' => '#section', 'label' => 'Label']]
 * - $show_back_home: Boolean para mostrar link "Volver al Inicio"
 * - $cta_link: Link del botón CTA (opcional)
 * - $cta_label: Label del botón CTA (opcional)
 */

// Valores por defecto si no están definidos
$show_back_home = $show_back_home ?? false;
$nav_links = $nav_links ?? [];
?>

<!-- Navigation -->
<nav class="bg-black border-b border-gold/20 sticky top-0 z-50 backdrop-blur-sm bg-black/90">
    <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
            <!-- Logo y Título -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center">
                    <?php echo $page_icon; ?>
                </div>
                <div>
                    <a href="index.php?lang=<?php echo $current_language; ?>" class="text-xl font-bold gold-gradient bg-clip-text text-transparent">OriginalisDoc</a>
                    <p class="text-xs text-gray-400"><?php echo $page_subtitle; ?></p>
                </div>
            </div>

            <!-- Navegación Desktop -->
            <div class="hidden md:flex items-center gap-6">
                <?php if ($show_back_home): ?>
                    <a href="index.php?lang=<?php echo $current_language; ?>" class="text-gray-300 hover:text-gold transition-colors flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <?php echo $lang['nav_volver_inicio']; ?>
                    </a>
                <?php endif; ?>

                <?php foreach ($nav_links as $link): ?>
                    <a href="<?php echo $link['href']; ?>" class="text-gray-300 hover:text-gold transition-colors">
                        <?php echo $link['label']; ?>
                    </a>
                <?php endforeach; ?>

                <!-- Selector de Idioma -->
                <div class="relative">
                    <button id="langToggle" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                        <?php echo get_flag_emoji($current_language); ?>
                        <span class="text-sm font-medium"><?php echo get_lang_short_name($current_language); ?></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="langMenu" class="hidden absolute right-0 mt-2 w-48 bg-gray-900 border border-gold/20 rounded-lg shadow-lg overflow-hidden z-50">
                        <?php foreach ($available_languages as $code => $name): ?>
                            <a href="?lang=<?php echo $code; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 transition-colors <?php echo $current_language === $code ? 'bg-gray-800' : ''; ?>">
                                <?php echo get_flag_emoji($code); ?>
                                <span class="text-sm"><?php echo $name; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Toggle Tema -->
                <button id="themeToggle" class="px-3 py-2 border border-gray-700 rounded-lg hover:border-gold/50 hover:bg-gray-800 transition-colors" title="<?php echo $lang['nav_tema_claro']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden dark-mode-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 light-mode-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <?php if (isset($cta_link) && isset($cta_label)): ?>
                    <a href="<?php echo $cta_link; ?>" class="px-6 py-2 gold-gradient text-black font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <?php echo $cta_label; ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
