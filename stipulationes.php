<?php
/**
 * OriginalisDoc - Términos de Servicio
 * Página legal multi-idioma
 */
$lang_modules = ['common', 'page_terminos'];
require_once 'lang_config.php';
?>
<!DOCTYPE html>
<html lang="<?php echo substr($current_language, 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['terminos_title']; ?> - Verumax</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $lang['terminos_meta_description']; ?>">
    <meta name="author" content="<?php echo $lang['meta_author']; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://verumax.com/terminos">
    <meta property="og:title" content="<?php echo $lang['terminos_meta_og_title']; ?>">
    <meta property="og:description" content="<?php echo $lang['terminos_meta_og_description']; ?>">
    <meta property="og:image" content="https://verumax.com/og-image.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://verumax.com/terminos">
    <meta property="twitter:title" content="<?php echo $lang['terminos_meta_twitter_title']; ?>">
    <meta property="twitter:description" content="<?php echo $lang['terminos_meta_twitter_description']; ?>">
    <meta property="twitter:image" content="https://verumax.com/og-image.png">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo-verumax-escudo.png">

    <!-- Flag Icons CSS - Banderas SVG de alta calidad -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .gold-gradient {
            background: linear-gradient(135deg, #D4AF37 0%, #F4E5A1 100%);
        }

        .text-gold {
            color: #D4AF37;
        }

        .border-gold {
            border-color: #D4AF37;
        }
    </style>
</head>
<body class="bg-black text-gray-300">
    <?php echo get_lang_debug_banner(); ?>
    <!-- Header -->
    <nav class="bg-black border-b border-gold/20 sticky top-0 z-50 backdrop-blur-sm bg-black/90">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="index.php?lang=<?php echo $current_language; ?>" class="flex items-center gap-3">
                    <img src="assets/images/logo-verumax-escudo.png" alt="Verumax" class="h-10 w-10">
                    <img src="assets/images/logo-verumax-texto.png" alt="Verumax" class="h-8 hidden sm:block">
                </a>

                <div class="flex items-center gap-4">
                    <!-- Selector de Idioma -->
                    <div class="relative">
                        <button id="langToggle" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                            <span class="text-xl"><?php echo get_flag_emoji($current_language); ?></span>
                            <span class="text-sm font-medium"><?php echo get_lang_short_name($current_language); ?></span>
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </button>
                        <div id="langMenu" class="hidden absolute right-0 mt-2 w-48 bg-gray-900 border border-gold/20 rounded-lg shadow-lg overflow-hidden z-50">
                            <?php foreach ($available_languages as $code => $name): ?>
                                <a href="?lang=<?php echo $code; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 transition-colors <?php echo $current_language === $code ? 'bg-gray-800' : ''; ?>">
                                    <span class="text-xl"><?php echo get_flag_emoji($code); ?></span>
                                    <span class="text-sm"><?php echo $name; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Toggle Tema - DESACTIVADO -->
                    <button id="themeToggle" class="p-2 rounded-lg hover:bg-gray-800 transition-colors" style="display:none">
                        <i data-lucide="sun" class="w-5 h-5 hidden dark-mode-icon"></i>
                        <i data-lucide="moon" class="w-5 h-5 light-mode-icon"></i>
                    </button>

                    <!-- Botón Cerrar -->
                    <button onclick="window.close(); return false;" class="text-gray-300 hover:text-gold transition-colors flex items-center gap-2">
                        <i data-lucide="x" class="w-5 h-5"></i>
                        <span class="hidden sm:inline"><?php echo $lang['nav_cerrar'] ?? 'Cerrar'; ?></span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-16 max-w-4xl">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                <?php echo $lang['terminos_title']; ?>
            </h1>
            <p class="text-gray-400"><?php echo $lang['terminos_updated']; ?>: <?php echo format_date(25, 12, 2025, $current_language); ?></p>
        </div>

        <div class="bg-gray-950 border border-gold/20 rounded-2xl p-8 md:p-12 space-y-8">
            <!-- Sección 1 -->
            <section>
                <h2 class="text-2xl font-bold text-gold mb-4"><?php echo $lang['terminos_seccion1_titulo']; ?></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?php echo $lang['terminos_seccion1_p1']; ?>
                </p>
                <p class="text-gray-300 leading-relaxed mt-3">
                    <?php echo $lang['terminos_seccion1_p2']; ?>
                </p>
            </section>

            <!-- Sección 2 -->
            <section>
                <h2 class="text-2xl font-bold text-gold mb-4"><?php echo $lang['terminos_seccion2_titulo']; ?></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?php echo $lang['terminos_seccion2_p1']; ?>
                </p>
                <p class="text-gray-300 leading-relaxed mt-3">
                    <?php echo $lang['terminos_seccion2_p2']; ?>
                </p>
                <ul class="list-disc list-inside text-gray-300 mt-3 space-y-2 ml-4">
                    <li><?php echo $lang['terminos_seccion2_li1']; ?></li>
                    <li><?php echo $lang['terminos_seccion2_li2']; ?></li>
                    <li><?php echo $lang['terminos_seccion2_li3']; ?></li>
                    <li><?php echo $lang['terminos_seccion2_li4']; ?></li>
                    <li><?php echo $lang['terminos_seccion2_li5']; ?></li>
                    <li><?php echo $lang['terminos_seccion2_li6']; ?></li>
                    <li><?php echo $lang['terminos_seccion2_li7']; ?></li>
                </ul>
            </section>

            <!-- Sección 3 -->
            <section>
                <h2 class="text-2xl font-bold text-gold mb-4"><?php echo $lang['terminos_seccion3_titulo']; ?></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?php echo $lang['terminos_seccion3_p1']; ?>
                </p>
                <ul class="list-disc list-inside text-gray-300 mt-3 space-y-2 ml-4">
                    <li><?php echo $lang['terminos_seccion3_li1']; ?></li>
                    <li><?php echo $lang['terminos_seccion3_li2']; ?></li>
                    <li><?php echo $lang['terminos_seccion3_li3']; ?></li>
                    <li><?php echo $lang['terminos_seccion3_li4']; ?></li>
                </ul>
                <p class="text-gray-300 leading-relaxed mt-3">
                    <?php echo $lang['terminos_seccion3_p2']; ?>
                </p>
            </section>

            <!-- Sección 4 -->
            <section>
                <h2 class="text-2xl font-bold text-gold mb-4"><?php echo $lang['terminos_seccion4_titulo']; ?></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?php echo $lang['terminos_seccion4_p1']; ?>
                </p>
            </section>

            <!-- Sección 5 -->
            <section>
                <h2 class="text-2xl font-bold text-gold mb-4"><?php echo $lang['terminos_seccion5_titulo']; ?></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?php echo $lang['terminos_seccion5_p1']; ?>
                </p>
            </section>

            <!-- Sección 6 -->
            <section>
                <h2 class="text-2xl font-bold text-gold mb-4"><?php echo $lang['terminos_seccion6_titulo']; ?></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?php echo $lang['terminos_seccion6_p1']; ?>
                </p>
            </section>

            <!-- Sección 7: Privacidad -->
            <section>
                <h2 class="text-2xl font-bold text-gold mb-4"><?php echo $lang['terminos_seccion7_titulo']; ?></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?php echo $lang['terminos_seccion7_p1']; ?>
                </p>
            </section>

            <!-- Sección 8: Limitación de Responsabilidad -->
            <section>
                <h2 class="text-2xl font-bold text-gold mb-4"><?php echo $lang['terminos_seccion8_titulo']; ?></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?php echo $lang['terminos_seccion8_p1']; ?>
                </p>
            </section>

            <!-- Sección 9: Contacto -->
            <section>
                <h2 class="text-2xl font-bold text-gold mb-4"><?php echo $lang['terminos_seccion9_titulo']; ?></h2>
                <p class="text-gray-300 leading-relaxed">
                    <?php echo $lang['terminos_seccion9_p1']; ?> <a href="mailto:contacto@verumax.com" class="text-gold hover:underline">contacto@verumax.com</a>
                </p>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-black border-t border-gold/20 py-8 mt-16">
        <div class="container mx-auto px-6 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Verumax. <?php echo $lang['footer_copyright'] ?? 'Todos los derechos reservados.'; ?></p>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        // Toggle del menú de idiomas
        document.getElementById('langToggle').addEventListener('click', function() {
            document.getElementById('langMenu').classList.toggle('hidden');
        });

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(event) {
            const langToggle = document.getElementById('langToggle');
            const langMenu = document.getElementById('langMenu');
            if (!langToggle.contains(event.target) && !langMenu.contains(event.target)) {
                langMenu.classList.add('hidden');
            }
        });

        // Toggle de tema claro/oscuro
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const darkModeIcon = document.querySelector('.dark-mode-icon');
        const lightModeIcon = document.querySelector('.light-mode-icon');

        // Toggle de tema DESACTIVADO - siempre modo oscuro
        /*
        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'light') {
            body.classList.remove('bg-black', 'text-gray-300');
            body.classList.add('bg-white', 'text-gray-900');
            darkModeIcon.classList.remove('hidden');
            lightModeIcon.classList.add('hidden');

            document.querySelectorAll('.bg-gray-950').forEach(el => {
                el.classList.remove('bg-gray-950');
                el.classList.add('bg-gray-50');
            });
            document.querySelectorAll('.text-gray-300').forEach(el => {
                el.classList.remove('text-gray-300');
                el.classList.add('text-gray-700');
            });
            document.querySelectorAll('.text-gray-400').forEach(el => {
                el.classList.remove('text-gray-400');
                el.classList.add('text-gray-600');
            });
        }
        */

        themeToggle.addEventListener('click', function() {
            const isLight = body.classList.contains('bg-white');

            if (isLight) {
                // Cambiar a oscuro
                body.classList.remove('bg-white', 'text-gray-900');
                body.classList.add('bg-black', 'text-gray-300');
                darkModeIcon.classList.add('hidden');
                lightModeIcon.classList.remove('hidden');
                localStorage.setItem('theme', 'dark');

                document.querySelectorAll('.bg-gray-50').forEach(el => {
                    el.classList.remove('bg-gray-50');
                    el.classList.add('bg-gray-950');
                });
                document.querySelectorAll('.text-gray-700').forEach(el => {
                    el.classList.remove('text-gray-700');
                    el.classList.add('text-gray-300');
                });
                document.querySelectorAll('.text-gray-600').forEach(el => {
                    el.classList.remove('text-gray-600');
                    el.classList.add('text-gray-400');
                });
            } else {
                // Cambiar a claro
                body.classList.remove('bg-black', 'text-gray-300');
                body.classList.add('bg-white', 'text-gray-900');
                darkModeIcon.classList.remove('hidden');
                lightModeIcon.classList.add('hidden');
                localStorage.setItem('theme', 'light');

                document.querySelectorAll('.bg-gray-950').forEach(el => {
                    el.classList.remove('bg-gray-950');
                    el.classList.add('bg-gray-50');
                });
                document.querySelectorAll('.text-gray-300').forEach(el => {
                    el.classList.remove('text-gray-300');
                    el.classList.add('text-gray-700');
                });
                document.querySelectorAll('.text-gray-400').forEach(el => {
                    el.classList.remove('text-gray-400');
                    el.classList.add('text-gray-600');
                });
            }
        });
    </script>
</body>
</html>
