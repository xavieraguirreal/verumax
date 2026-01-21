<?php
/**
 * OriginalisDoc - Landing Page Multi-idioma
 * Español (Argentina), Português (Brasil), Ελληνικά (Ελλάδα)
 */
require_once 'config.php';
require_once 'lang_config.php';
?>
<!DOCTYPE html>
<html lang="<?php echo substr($current_language, 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['meta_title']; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $lang['meta_description']; ?>">
    <meta name="keywords" content="<?php echo $lang['meta_keywords']; ?>">
    <meta name="author" content="<?php echo $lang['meta_author']; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://validarcert.com/">
    <meta property="og:title" content="<?php echo $lang['meta_og_title']; ?>">
    <meta property="og:description" content="<?php echo $lang['meta_og_description']; ?>">
    <meta property="og:image" content="https://validarcert.com/og-image.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://validarcert.com/">
    <meta property="twitter:title" content="<?php echo $lang['meta_twitter_title']; ?>">
    <meta property="twitter:description" content="<?php echo $lang['meta_twitter_description']; ?>">
    <meta property="twitter:image" content="https://validarcert.com/og-image.png">

    <!-- Favicon - Checkmark dorado en círculo -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle fill='%23D4AF37' cx='50' cy='50' r='45'/><path fill='none' stroke='%23fff' stroke-width='8' stroke-linecap='round' stroke-linejoin='round' d='M30 50 L42 62 L70 38'/></svg>">

    <!-- Flag Icons CSS - Banderas SVG de alta calidad -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'gold': {
                            DEFAULT: '#D4AF37',
                            light: '#F0D377',
                            dark: '#B8941E'
                        },
                        'metallic-green': {
                            DEFAULT: '#2E7D32',
                            light: '#4CAF50',
                            dark: '#1B5E20'
                        },
                        'metallic-red': {
                            DEFAULT: '#C62828',
                            light: '#E53935',
                            dark: '#8E0000'
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
            background: #0a0a0a;
        }
        .gold-gradient {
            background: linear-gradient(135deg, #D4AF37 0%, #F0D377 100%);
        }
        .metallic-shine {
            position: relative;
            overflow: hidden;
        }
        .metallic-shine::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shine 3s infinite;
        }
        @keyframes shine {
            to { left: 100%; }
        }

        /* Light Mode Styles */
        body.light-mode {
            background: #f5f5f5 !important;
            color: #1a1a1a !important;
        }
        body.light-mode .bg-black,
        body.light-mode .bg-gray-900,
        body.light-mode .bg-gray-950,
        body.light-mode .from-black,
        body.light-mode .via-gray-900,
        body.light-mode .to-black {
            background: white !important;
            border-color: #e5e7eb !important;
        }
        body.light-mode .text-gray-300,
        body.light-mode .text-gray-400 {
            color: #4b5563 !important;
        }
        body.light-mode .border-gold\/20,
        body.light-mode .border-gold\/30 {
            border-color: rgba(212, 175, 55, 0.3) !important;
        }
        body.light-mode header {
            background: white !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>

    <!-- Estilos Compartidos -->
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>styles.css">
</head>
<body class="bg-black text-gray-100">

    <!-- Header -->
    <header class="bg-black/95 backdrop-blur-md border-b border-gold/20 sticky top-0 left-0 right-0 z-50">
        <!-- Reading Progress Bar -->
        <div id="reading-progress" class="absolute top-0 left-0 h-1 gold-gradient transition-all duration-150" style="width: 0%"></div>

        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                 <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle fill="#D4AF37" cx="50" cy="50" r="45"/><path fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" d="M30 50 L42 62 L70 38"/></svg>
                <a href="#" class="text-2xl font-bold text-gold">OriginalisDoc</a>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-300 hover:text-gold font-medium transition-colors"><?php echo $lang['nav_inicio']; ?></a>

                <!-- Soluciones por Sector Dropdown -->
                <div class="relative" id="dropdown-soluciones">
                    <button onclick="toggleDropdown('soluciones')" class="text-gray-300 hover:text-gold font-medium transition-colors flex items-center gap-1">
                        <?php echo $lang['nav_soluciones']; ?>
                        <i data-lucide="chevron-down" class="w-4 h-4" id="chevron-soluciones"></i>
                    </button>
                    <div id="menu-soluciones" class="absolute left-0 mt-2 w-56 bg-gray-900 border border-gold/30 rounded-lg shadow-xl opacity-0 invisible transition-all duration-200 z-50">
                        <a href="academico.php?lang=<?php echo $current_language; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 first:rounded-t-lg">
                            <i data-lucide="graduation-cap" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium"><?php echo $lang['nav_soluciones_academico']; ?></span>
                        </a>
                        <div class="px-4 py-3 hover:bg-gold/10 transition-colors text-gray-500 cursor-not-allowed flex items-center gap-3">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                            <span class="text-sm font-medium"><?php echo $lang['nav_soluciones_profesional']; ?></span>
                            <span class="ml-auto text-xs"><?php echo $lang['cat_proximamente']; ?></span>
                        </div>
                        <div class="px-4 py-3 hover:bg-gold/10 transition-colors text-gray-500 cursor-not-allowed flex items-center gap-3">
                            <i data-lucide="ticket" class="w-4 h-4"></i>
                            <span class="text-sm font-medium"><?php echo $lang['nav_soluciones_eventos']; ?></span>
                            <span class="ml-auto text-xs"><?php echo $lang['cat_proximamente']; ?></span>
                        </div>
                        <div class="px-4 py-3 hover:bg-gold/10 transition-colors text-gray-500 cursor-not-allowed flex items-center gap-3">
                            <i data-lucide="building" class="w-4 h-4"></i>
                            <span class="text-sm font-medium"><?php echo $lang['nav_soluciones_empresarial']; ?></span>
                            <span class="ml-auto text-xs"><?php echo $lang['cat_proximamente']; ?></span>
                        </div>
                        <div class="px-4 py-3 hover:bg-gold/10 transition-colors text-gray-500 cursor-not-allowed flex items-center gap-3">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            <span class="text-sm font-medium"><?php echo $lang['nav_soluciones_cooperativas']; ?></span>
                            <span class="ml-auto text-xs"><?php echo $lang['cat_proximamente']; ?></span>
                        </div>
                        <div class="px-4 py-3 hover:bg-gold/10 transition-colors text-gray-500 cursor-not-allowed flex items-center gap-3 last:rounded-b-lg">
                            <i data-lucide="heart-handshake" class="w-4 h-4"></i>
                            <span class="text-sm font-medium"><?php echo $lang['nav_soluciones_mutuales']; ?></span>
                            <span class="ml-auto text-xs"><?php echo $lang['cat_proximamente']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Productos Dropdown -->
                <div class="relative" id="dropdown-productos">
                    <button onclick="toggleDropdown('productos')" class="text-gray-300 hover:text-gold font-medium transition-colors flex items-center gap-1">
                        <?php echo $lang['nav_productos']; ?>
                        <i data-lucide="chevron-down" class="w-4 h-4" id="chevron-productos"></i>
                    </button>
                    <div id="menu-productos" class="absolute left-0 mt-2 w-56 bg-gray-900 border border-gold/30 rounded-lg shadow-xl opacity-0 invisible transition-all duration-200 z-50">
                        <a href="tarjetadigital.php?lang=<?php echo $current_language; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 first:rounded-t-lg">
                            <i data-lucide="credit-card" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium"><?php echo $lang['nav_productos_tarjeta_digital']; ?></span>
                        </a>
                        <a href="academico.php?lang=<?php echo $current_language; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 last:rounded-b-lg">
                            <i data-lucide="award" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium"><?php echo $lang['nav_productos_certificados']; ?></span>
                        </a>
                    </div>
                </div>

                <a href="#faq" class="text-gray-300 hover:text-gold font-medium transition-colors"><?php echo $lang['nav_faq']; ?></a>
                <a href="#validar" class="text-gray-300 hover:text-gold font-medium transition-colors"><?php echo $lang['nav_validar']; ?></a>

                <!-- Language Selector -->
                <div class="relative" id="lang-selector">
                    <button onclick="toggleLangMenu()" class="text-gray-300 hover:text-gold transition-colors px-3 py-2 flex items-center gap-2 border border-gray-700 rounded-lg hover:border-gold/50" title="<?php echo $lang['nav_demo']; ?>">
                        <?php echo get_flag_emoji($current_language); ?>
                        <span class="text-sm font-medium"><?php echo get_lang_short_name($current_language); ?></span>
                        <i data-lucide="chevron-down" class="w-4 h-4" id="lang-chevron"></i>
                    </button>
                    <div id="lang-menu" class="absolute right-0 mt-2 w-48 bg-gray-900 border border-gold/30 rounded-lg shadow-xl opacity-0 invisible transition-all duration-200 z-50">
                        <?php foreach ($available_languages as $code => $name): ?>
                        <a href="?lang=<?php echo $code; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors <?php echo $current_language === $code ? 'bg-gold/20 text-gold' : 'text-gray-300'; ?> first:rounded-t-lg last:rounded-b-lg">
                            <?php echo get_flag_emoji($code); ?>
                            <span class="text-sm font-medium"><?php echo $name; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button onclick="toggleDarkMode()" class="text-gray-300 hover:text-gold transition-colors px-3 py-2 border border-gray-700 rounded-lg hover:border-gold/50" id="theme-button" title="<?php echo $lang['nav_tema_claro']; ?>">
                    <i data-lucide="sun" id="theme-icon" class="w-5 h-5"></i>
                </button>
                <a href="#contacto" class="px-5 py-2 text-black font-semibold gold-gradient rounded-lg hover:opacity-90 transition-opacity"><?php echo $lang['nav_demo']; ?></a>
            </div>
            <button class="md:hidden text-gold">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="py-20 md:py-32 bg-gradient-to-b from-black via-gray-900 to-black relative overflow-hidden">
        <!-- Decorative elements with parallax -->
        <div class="absolute inset-0 opacity-20" id="parallax-container">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-gold rounded-full blur-3xl parallax-slow"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-metallic-green rounded-full blur-3xl parallax-fast"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-metallic-red/30 rounded-full blur-3xl parallax-medium"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center">
                <!-- Badge de confianza -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-gold/20 border border-gold/30 text-gold rounded-full text-sm font-semibold mb-6 metallic-shine">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span><?php echo $lang['hero_badge']; ?></span>
                </div>

                <h1 class="text-4xl md:text-6xl font-extrabold text-transparent bg-clip-text gold-gradient leading-tight">
                    <?php echo $lang['hero_title']; ?>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-gray-300 max-w-3xl mx-auto">
                    <?php echo $lang['hero_subtitle']; ?>
                </p>
                <div class="mt-10 flex flex-wrap justify-center gap-4">
                     <a href="#categorias" class="px-8 py-3 text-black font-bold gold-gradient rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/50"><?php echo $lang['hero_cta_primary']; ?></a>
                     <a href="#validar" class="px-8 py-3 text-gold font-bold bg-gold/10 border border-gold/30 rounded-lg hover:bg-gold/20 transition-colors flex items-center gap-2">
                        <i data-lucide="search-check" class="w-5 h-5"></i>
                        <?php echo $lang['hero_cta_secondary']; ?>
                     </a>
                </div>

                <!-- Mobile Preview Mockup -->
                <div class="mt-16 flex justify-center">
                    <div class="relative">
                        <!-- Phone Frame -->
                        <div class="relative w-72 h-[600px] bg-gray-950 rounded-[3rem] border-8 border-gray-800 shadow-2xl overflow-hidden">
                            <!-- Notch -->
                            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-40 h-6 bg-black rounded-b-3xl z-10"></div>

                            <!-- Screen Content -->
                            <div class="absolute inset-0 bg-gradient-to-br from-gray-900 to-black p-6 overflow-hidden">
                                <!-- Header -->
                                <div class="mt-8 flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 gold-gradient rounded-lg flex items-center justify-center">
                                            <i data-lucide="shield-check" class="w-5 h-5 text-black"></i>
                                        </div>
                                        <span class="text-gold font-bold">OriginalisDoc</span>
                                    </div>
                                    <i data-lucide="menu" class="w-6 h-6 text-gray-400"></i>
                                </div>

                                <!-- Certificate Card -->
                                <div class="bg-gradient-to-br from-gold/20 to-metallic-green/20 border border-gold/30 rounded-2xl p-4 mb-4">
                                    <div class="text-xs text-gray-400 mb-1"><?php echo $lang['hero_mockup_certificado']; ?></div>
                                    <div class="text-sm font-bold text-gold mb-2"><?php echo $lang['hero_mockup_curso']; ?></div>
                                    <div class="text-xs text-gray-300 mb-3"><?php echo $lang['hero_mockup_estudiante']; ?></div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-16 h-16 bg-white rounded-lg flex items-center justify-center">
                                            <div class="text-xs">QR</div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-xs text-metallic-green-light mb-1"><?php echo $lang['hero_mockup_verificado']; ?></div>
                                            <div class="text-xs text-gray-400"><?php echo $lang['hero_mockup_valido']; ?></div>
                                        </div>
                                    </div>
                                    <button class="w-full bg-gold/20 border border-gold/30 text-gold text-xs py-2 rounded-lg">
                                        <?php echo $lang['hero_mockup_ver_detalles']; ?>
                                    </button>
                                </div>

                                <!-- Quick Stats -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-gray-900/50 border border-gray-700 rounded-xl p-3">
                                        <div class="text-lg font-bold text-metallic-green-light">15</div>
                                        <div class="text-xs text-gray-400"><?php echo $lang['hero_mockup_certificados']; ?></div>
                                    </div>
                                    <div class="bg-gray-900/50 border border-gray-700 rounded-xl p-3">
                                        <div class="text-lg font-bold text-gold">3</div>
                                        <div class="text-xs text-gray-400"><?php echo $lang['hero_mockup_en_curso']; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Bar -->
                            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 w-32 h-1 bg-gray-700 rounded-full"></div>
                        </div>

                        <!-- Floating Elements -->
                        <div class="absolute -top-4 -right-4 w-20 h-20 bg-gold/20 rounded-full blur-2xl"></div>
                        <div class="absolute -bottom-4 -left-4 w-20 h-20 bg-metallic-green/20 rounded-full blur-2xl"></div>
                    </div>
                </div>

                <!-- Estadísticas de impacto con contadores animados -->
                <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-4 hover:border-gold/50 transition-all">
                        <div class="text-3xl md:text-4xl font-bold text-gold">
                            <span class="counter" data-target="99.9">0</span>%
                        </div>
                        <div class="text-sm text-gray-400 mt-1"><?php echo $lang['stat_uptime']; ?></div>
                    </div>
                    <div class="bg-gray-900/50 border border-metallic-green/20 rounded-xl p-4 hover:border-metallic-green/50 transition-all">
                        <div class="text-3xl md:text-4xl font-bold text-metallic-green-light">
                            <span class="counter" data-target="10000">0</span>+
                        </div>
                        <div class="text-sm text-gray-400 mt-1"><?php echo $lang['stat_documentos']; ?></div>
                    </div>
                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-4 hover:border-gold/50 transition-all">
                        <div class="text-3xl md:text-4xl font-bold text-gold">24/7</div>
                        <div class="text-sm text-gray-400 mt-1"><?php echo $lang['stat_acceso']; ?></div>
                    </div>
                    <div class="bg-gray-900/50 border border-metallic-green/20 rounded-xl p-4 hover:border-metallic-green/50 transition-all">
                        <div class="text-3xl md:text-4xl font-bold text-metallic-green-light">
                            <span class="counter" data-target="85">0</span>%
                        </div>
                        <div class="text-sm text-gray-400 mt-1"><?php echo $lang['stat_ahorro']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Selector de Sectores Section -->
    <section id="categorias" class="py-20 bg-gradient-to-b from-black to-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                    <?php echo $lang['cat_title']; ?>
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    <?php echo $lang['cat_subtitle']; ?>
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <!-- Sector Académico -->
                <a href="academico.php?lang=<?php echo $current_language; ?>" class="sector-card bg-gray-900/80 border-2 border-gold/30 rounded-2xl p-8 text-center hover:border-gold hover:bg-gray-900 hover:scale-105 transition-all duration-300 cursor-pointer group block">
                    <div class="w-20 h-20 gold-gradient rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gold mb-3"><?php echo $lang['cat_academico']; ?></h3>
                    <div class="text-sm text-gray-400 space-y-1 mb-4">
                        <div><?php echo $lang['cat_academico_1']; ?></div>
                        <div><?php echo $lang['cat_academico_2']; ?></div>
                        <div><?php echo $lang['cat_academico_3']; ?></div>
                    </div>
                    <div class="mt-4 px-4 py-2 bg-gold/10 border border-gold/30 rounded-lg">
                        <div class="text-xs text-gold font-semibold"><?php echo $lang['cat_academico_cta']; ?></div>
                    </div>
                </a>

                <!-- Sector Profesional -->
                <div class="sector-card bg-gray-900/80 border-2 border-metallic-green/30 rounded-2xl p-8 text-center hover:border-metallic-green hover:bg-gray-900 transition-all duration-300 cursor-not-allowed opacity-75">
                    <div class="w-20 h-20 bg-gradient-to-br from-metallic-green to-metallic-green-light rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-metallic-green-light mb-3"><?php echo $lang['cat_profesional']; ?></h3>
                    <div class="text-sm text-gray-400 space-y-1 mb-4">
                        <div>Salud y Bienestar</div>
                        <div>Ingeniería y Arquitectura</div>
                        <div>Todos los Profesionales</div>
                    </div>
                    <div class="mt-4 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg">
                        <div class="text-xs text-gray-500 font-semibold"><?php echo $lang['cat_proximamente']; ?></div>
                    </div>
                </div>

                <!-- Sector Eventos -->
                <div class="sector-card bg-gray-900/80 border-2 border-blue-500/30 rounded-2xl p-8 text-center hover:border-blue-500 hover:bg-gray-900 transition-all duration-300 cursor-not-allowed opacity-75">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-blue-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-blue-400 mb-3"><?php echo $lang['cat_eventos']; ?></h3>
                    <div class="text-sm text-gray-400 space-y-1 mb-4">
                        <div><?php echo $lang['cat_eventos_1']; ?></div>
                        <div><?php echo $lang['cat_eventos_2']; ?></div>
                        <div><?php echo $lang['cat_eventos_3']; ?></div>
                    </div>
                    <div class="mt-4 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg">
                        <div class="text-xs text-gray-500 font-semibold"><?php echo $lang['cat_proximamente']; ?></div>
                    </div>
                </div>

                <!-- Sector Empresarial -->
                <div class="sector-card bg-gray-900/80 border-2 border-purple-500/30 rounded-2xl p-8 text-center hover:border-purple-500 hover:bg-gray-900 transition-all duration-300 cursor-not-allowed opacity-75">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-purple-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-purple-400 mb-3"><?php echo $lang['cat_empresarial']; ?></h3>
                    <div class="text-sm text-gray-400 space-y-1 mb-4">
                        <div><?php echo $lang['cat_empresarial_1']; ?></div>
                        <div><?php echo $lang['cat_empresarial_2']; ?></div>
                        <div><?php echo $lang['cat_empresarial_3']; ?></div>
                    </div>
                    <div class="mt-4 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg">
                        <div class="text-xs text-gray-500 font-semibold"><?php echo $lang['cat_proximamente']; ?></div>
                    </div>
                </div>

                <!-- Sector Cooperativas -->
                <div class="sector-card bg-gray-900/80 border-2 border-orange-500/30 rounded-2xl p-8 text-center hover:border-orange-500 hover:bg-gray-900 transition-all duration-300 cursor-not-allowed opacity-75">
                    <div class="w-20 h-20 bg-gradient-to-br from-orange-600 to-orange-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-orange-400 mb-3"><?php echo $lang['cat_cooperativas']; ?></h3>
                    <div class="text-sm text-gray-400 space-y-1 mb-4">
                        <div><?php echo $lang['cat_cooperativas_1']; ?></div>
                        <div><?php echo $lang['cat_cooperativas_2']; ?></div>
                        <div><?php echo $lang['cat_cooperativas_3']; ?></div>
                    </div>
                    <div class="mt-4 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg">
                        <div class="text-xs text-gray-500 font-semibold"><?php echo $lang['cat_proximamente']; ?></div>
                    </div>
                </div>

                <!-- Sector Mutuales -->
                <div class="sector-card bg-gray-900/80 border-2 border-emerald-500/30 rounded-2xl p-8 text-center hover:border-emerald-500 hover:bg-gray-900 transition-all duration-300 cursor-not-allowed opacity-75">
                    <div class="w-20 h-20 bg-gradient-to-br from-emerald-600 to-emerald-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-emerald-400 mb-3"><?php echo $lang['cat_mutuales']; ?></h3>
                    <div class="text-sm text-gray-400 space-y-1 mb-4">
                        <div><?php echo $lang['cat_mutuales_1']; ?></div>
                        <div><?php echo $lang['cat_mutuales_2']; ?></div>
                        <div><?php echo $lang['cat_mutuales_3']; ?></div>
                    </div>
                    <div class="mt-4 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg">
                        <div class="text-xs text-gray-500 font-semibold"><?php echo $lang['cat_proximamente']; ?></div>
                    </div>
                </div>

                <!-- Sector Artistas -->
                <div class="sector-card bg-gray-900/80 border-2 border-purple-500/30 rounded-2xl p-8 text-center hover:border-purple-500 hover:bg-gray-900 transition-all duration-300 cursor-not-allowed opacity-75">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-pink-400 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-purple-400 mb-3"><?php echo $lang['cat_artistas']; ?></h3>
                    <div class="text-sm text-gray-400 space-y-1 mb-4">
                        <div><?php echo $lang['cat_artistas_1']; ?></div>
                        <div><?php echo $lang['cat_artistas_2']; ?></div>
                        <div><?php echo $lang['cat_artistas_3']; ?></div>
                    </div>
                    <div class="mt-4 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg">
                        <div class="text-xs text-gray-500 font-semibold"><?php echo $lang['cat_proximamente']; ?></div>
                    </div>
                </div>
            </div>

            <!-- Nota informativa -->
            <div class="text-center mt-12">
                <p class="text-gray-400 text-sm"><?php echo $lang['cat_footer']; ?> <a href="#contacto" class="text-gold hover:text-gold-light underline"><?php echo $lang['cat_footer_link']; ?></a> <?php echo $lang['cat_footer_text']; ?></p>
            </div>
        </div>
    </section>

    <!-- Productos Section -->
    <section id="productos" class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                    <?php echo $lang['productos_title']; ?>
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    <?php echo $lang['productos_subtitle']; ?>
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <!-- Tarjeta Digital -->
                <a href="tarjetadigital.php?lang=<?php echo $current_language; ?>" class="sector-card bg-gray-900/80 border-2 border-gold/30 rounded-2xl p-8 text-center hover:border-gold hover:bg-gray-900 hover:scale-105 transition-all duration-300 cursor-pointer group block">
                    <div class="w-20 h-20 gold-gradient rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gold mb-3"><?php echo $lang['cat_tarjeta_digital']; ?></h3>
                    <div class="text-sm text-gray-400 space-y-1 mb-4">
                        <div><?php echo $lang['cat_tarjeta_digital_1']; ?></div>
                        <div><?php echo $lang['cat_tarjeta_digital_2']; ?></div>
                        <div><?php echo $lang['cat_tarjeta_digital_3']; ?></div>
                    </div>
                    <div class="mt-4 px-4 py-2 bg-gold/10 border border-gold/30 rounded-lg">
                        <div class="text-xs text-gold font-semibold"><?php echo $lang['cat_tarjeta_digital_cta']; ?></div>
                    </div>
                </a>

                <!-- Certificados Académicos -->
                <a href="academico.php?lang=<?php echo $current_language; ?>" class="sector-card bg-gray-900/80 border-2 border-metallic-green/30 rounded-2xl p-8 text-center hover:border-metallic-green hover:bg-gray-900 hover:scale-105 transition-all duration-300 cursor-pointer group block">
                    <div class="w-20 h-20 bg-gradient-to-br from-metallic-green to-metallic-green-light rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-metallic-green-light mb-3"><?php echo $lang['nav_productos_certificados']; ?></h3>
                    <div class="text-sm text-gray-400 space-y-1 mb-4">
                        <div><?php echo $lang['cat_academico_1']; ?></div>
                        <div><?php echo $lang['cat_academico_2']; ?></div>
                        <div><?php echo $lang['cat_academico_3']; ?></div>
                    </div>
                    <div class="mt-4 px-4 py-2 bg-metallic-green/10 border border-metallic-green/30 rounded-lg">
                        <div class="text-xs text-metallic-green-light font-semibold"><?php echo $lang['cat_academico_cta']; ?></div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Beneficios Section -->
    <section id="beneficios" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <!-- Servicios Universales Destacados -->
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                    <?php echo $lang['servicios_title']; ?>
                </h2>
                <p class="text-lg text-gray-400 max-w-3xl mx-auto">
                    <?php echo $lang['servicios_subtitle']; ?>
                </p>
            </div>

            <!-- Grid de Servicios Universales -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-20">
                <!-- Seguridad Anti-Fraude -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gold/40 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all">
                    <div class="w-16 h-16 gold-gradient rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="shield-check" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gold text-center mb-3"><?php echo $lang['serv_antifraude']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center"><?php echo $lang['serv_antifraude_desc']; ?></p>
                </div>

                <!-- Portal 24/7 -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-metallic-green/40 p-6 rounded-2xl hover:border-metallic-green hover:shadow-xl hover:shadow-metallic-green/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-metallic-green to-metallic-green-light rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="clock" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-metallic-green-light text-center mb-3"><?php echo $lang['serv_portal']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center"><?php echo $lang['serv_portal_desc']; ?></p>
                </div>

                <!-- Impresión Premium -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-blue-500/40 p-6 rounded-2xl hover:border-blue-500 hover:shadow-xl hover:shadow-blue-500/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-400 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="printer" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blue-400 text-center mb-3"><?php echo $lang['serv_impresion']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center"><?php echo $lang['serv_impresion_desc']; ?></p>
                </div>

                <!-- Branding Personalizado -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-purple-500/40 p-6 rounded-2xl hover:border-purple-500 hover:shadow-xl hover:shadow-purple-500/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-purple-400 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="palette" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-purple-400 text-center mb-3"><?php echo $lang['serv_branding']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center"><?php echo $lang['serv_branding_desc']; ?></p>
                </div>

                <!-- Emisión Masiva -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-orange-500/40 p-6 rounded-2xl hover:border-orange-500 hover:shadow-xl hover:shadow-orange-500/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-600 to-orange-400 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="zap" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-orange-400 text-center mb-3"><?php echo $lang['serv_emision']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center"><?php echo $lang['serv_emision_desc']; ?></p>
                </div>

                <!-- Portal de Validación -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-emerald-500/40 p-6 rounded-2xl hover:border-emerald-500 hover:shadow-xl hover:shadow-emerald-500/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-600 to-emerald-400 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="search-check" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-400 text-center mb-3"><?php echo $lang['serv_validacion']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center"><?php echo $lang['serv_validacion_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Casos de Uso - Carousel -->
    <section class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['casos_title']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['casos_subtitle']; ?></p>
            </div>

            <!-- Carousel Container -->
            <div class="relative max-w-5xl mx-auto">
                <!-- Carousel Track -->
                <div class="overflow-hidden">
                    <div id="carousel-track" class="flex transition-transform duration-500 ease-in-out">
                        <!-- Slide 1: SAJuR -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-gray-900 border border-metallic-green/30 p-8 rounded-xl">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-16 h-16 bg-metallic-green rounded-full flex items-center justify-center text-white font-bold text-xl metallic-shine">
                                        SAJuR
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gold"><?php echo $lang['caso_sajur_org']; ?></h4>
                                        <p class="text-sm text-gray-400"><?php echo $lang['caso_sajur_tipo']; ?></p>
                                    </div>
                                </div>
                                <p class="text-gray-300 italic leading-relaxed"><?php echo $lang['caso_sajur_texto']; ?></p>
                                <div class="mt-4 flex items-center gap-1 text-gold">
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2: Liberté -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-gray-900 border border-gold/30 p-8 rounded-xl">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center text-black font-bold text-xl">
                                        Liberté
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gold"><?php echo $lang['caso_liberte_org']; ?></h4>
                                        <p class="text-sm text-gray-400"><?php echo $lang['caso_liberte_tipo']; ?></p>
                                    </div>
                                </div>
                                <p class="text-gray-300 italic leading-relaxed"><?php echo $lang['caso_liberte_texto']; ?></p>
                                <div class="mt-4 flex items-center gap-1 text-gold">
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3: Nutricionista -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-gray-900 border border-metallic-green/30 p-8 rounded-xl">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-16 h-16 bg-metallic-green rounded-full flex items-center justify-center text-white font-bold metallic-shine">
                                        <i data-lucide="apple" class="w-8 h-8"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gold"><?php echo $lang['caso_nutricion_org']; ?></h4>
                                        <p class="text-sm text-gray-400"><?php echo $lang['caso_nutricion_tipo']; ?></p>
                                    </div>
                                </div>
                                <p class="text-gray-300 italic leading-relaxed"><?php echo $lang['caso_nutricion_texto']; ?></p>
                                <div class="mt-4 flex items-center gap-1 text-gold">
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 4: Empresa Tech -->
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-gray-900 border border-gold/30 p-8 rounded-xl">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center text-black font-bold">
                                        <i data-lucide="briefcase" class="w-8 h-8"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gold"><?php echo $lang['caso_empresa_org']; ?></h4>
                                        <p class="text-sm text-gray-400"><?php echo $lang['caso_empresa_tipo']; ?></p>
                                    </div>
                                </div>
                                <p class="text-gray-300 italic leading-relaxed"><?php echo $lang['caso_empresa_texto']; ?></p>
                                <div class="mt-4 flex items-center gap-1 text-gold">
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <button onclick="previousSlide()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 w-12 h-12 bg-gray-900 border border-gold/30 rounded-full flex items-center justify-center text-gold hover:bg-gold hover:text-black transition-all">
                    <i data-lucide="chevron-left" class="w-6 h-6"></i>
                </button>
                <button onclick="nextSlide()" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 w-12 h-12 bg-gray-900 border border-gold/30 rounded-full flex items-center justify-center text-gold hover:bg-gold hover:text-black transition-all">
                    <i data-lucide="chevron-right" class="w-6 h-6"></i>
                </button>

                <!-- Indicators -->
                <div class="flex justify-center items-center gap-4 mt-8">
                    <div class="flex gap-2">
                        <button onclick="goToSlide(0)" class="carousel-indicator w-3 h-3 rounded-full bg-gold transition-all"></button>
                        <button onclick="goToSlide(1)" class="carousel-indicator w-3 h-3 rounded-full bg-gray-600 transition-all"></button>
                        <button onclick="goToSlide(2)" class="carousel-indicator w-3 h-3 rounded-full bg-gray-600 transition-all"></button>
                        <button onclick="goToSlide(3)" class="carousel-indicator w-3 h-3 rounded-full bg-gray-600 transition-all"></button>
                    </div>
                    <!-- Pause/Play Button -->
                    <button id="carousel-pause-btn" onclick="toggleCarouselPause()" class="w-8 h-8 bg-gray-900 border border-gold/30 rounded-full flex items-center justify-center text-gold hover:bg-gold hover:text-black transition-all" title="Pausar">
                        <i id="carousel-pause-icon" data-lucide="pause" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['faq_title']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['faq_subtitle']; ?></p>
            </div>
            <div class="space-y-6">
                <!-- FAQ 1 -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-colors">
                    <h3 class="text-lg font-bold text-gold flex items-start gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-gold mt-0.5 flex-shrink-0"></i>
                        <?php echo $lang['faq_1_q']; ?>
                    </h3>
                    <p class="mt-3 text-gray-300 ml-8"><?php echo $lang['faq_1_a']; ?></p>
                </div>

                <!-- FAQ 2 -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-colors">
                    <h3 class="text-lg font-bold text-gold flex items-start gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-gold mt-0.5 flex-shrink-0"></i>
                        <?php echo $lang['faq_2_q']; ?>
                    </h3>
                    <p class="mt-3 text-gray-300 ml-8"><?php echo $lang['faq_2_a']; ?></p>
                </div>

                <!-- FAQ 3 - Solo visible en versión Argentina del sitio -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-colors" data-region="AR">
                    <h3 class="text-lg font-bold text-gold flex items-start gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-gold mt-0.5 flex-shrink-0"></i>
                        <?php echo $lang['faq_3_q']; ?>
                    </h3>
                    <p class="mt-3 text-gray-300 ml-8"><?php echo $lang['faq_3_a']; ?></p>
                </div>

                <!-- FAQ 4 -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-colors">
                    <h3 class="text-lg font-bold text-gold flex items-start gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-gold mt-0.5 flex-shrink-0"></i>
                        <?php echo $lang['faq_4_q']; ?>
                    </h3>
                    <p class="mt-3 text-gray-300 ml-8"><?php echo $lang['faq_4_a']; ?></p>
                </div>

                <!-- FAQ 5 -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-colors">
                    <h3 class="text-lg font-bold text-gold flex items-start gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-gold mt-0.5 flex-shrink-0"></i>
                        <?php echo $lang['faq_5_q']; ?>
                    </h3>
                    <p class="mt-3 text-gray-300 ml-8"><?php echo $lang['faq_5_a']; ?></p>
                </div>

                <!-- FAQ 6 -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-colors">
                    <h3 class="text-lg font-bold text-gold flex items-start gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-gold mt-0.5 flex-shrink-0"></i>
                        <?php echo $lang['faq_6_q']; ?>
                    </h3>
                    <p class="mt-3 text-gray-300 ml-8"><?php echo $lang['faq_6_a']; ?></p>
                </div>

                <!-- FAQ 7 -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-colors">
                    <h3 class="text-lg font-bold text-gold flex items-start gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-gold mt-0.5 flex-shrink-0"></i>
                        <?php echo $lang['faq_7_q']; ?>
                    </h3>
                    <p class="mt-3 text-gray-300 ml-8"><?php echo $lang['faq_7_a']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- NUEVA SECCIÓN DE VALIDACIÓN -->
    <section id="validar" class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6 max-w-2xl text-center">
            <i data-lucide="search-check" class="w-12 h-12 text-gold mx-auto"></i>
            <h2 class="text-3xl md:text-4xl font-bold text-gold mt-4"><?php echo $lang['validar_title']; ?></h2>
            <p class="mt-4 text-lg text-gray-300">
                <?php echo $lang['validar_subtitle']; ?>
            </p>
            <form action="validar.php" method="POST" class="mt-8 flex flex-col sm:flex-row gap-4 items-center justify-center">
                <input type="text" name="codigo" placeholder="<?php echo $lang['validar_placeholder']; ?>" required class="w-full sm:w-80 px-5 py-3 text-white bg-gray-900 border border-gold/30 rounded-lg focus:outline-none focus:ring-2 focus:ring-gold text-center font-mono uppercase placeholder-gray-500">
                <button type="submit" class="w-full sm:w-auto px-8 py-3 text-black font-bold gold-gradient rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/30">
                    <?php echo $lang['validar_btn']; ?>
                </button>
            </form>
        </div>
    </section>

    <!-- Precios Section -->
    <section id="precios" class="py-20 bg-gray-50">
        <!-- Contenido de la sección de precios... (sin cambios) -->
    </section>

    <!-- CTA Section -->
    <section id="contacto" class="py-20 bg-gradient-to-br from-gray-900 via-gray-800 to-black text-white relative overflow-hidden border-y border-gold/20">
        <!-- Decorative elements -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-72 h-72 bg-gold rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-metallic-green rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 text-center relative z-10">
            <h2 class="text-3xl md:text-5xl font-extrabold text-transparent bg-clip-text gold-gradient"><?php echo $lang['contacto_title']; ?></h2>
            <p class="mt-6 text-lg md:text-xl text-gray-300 max-w-2xl mx-auto">
                <?php echo $lang['contacto_subtitle']; ?>
            </p>

            <!-- Benefits list -->
            <div class="mt-10 grid md:grid-cols-3 gap-6 max-w-4xl mx-auto text-left">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-metallic-green rounded-full flex items-center justify-center flex-shrink-0 mt-1 metallic-shine">
                        <i data-lucide="check" class="w-4 h-4 text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gold"><?php echo $lang['benefit_demo']; ?></p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['benefit_demo_desc']; ?></p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-metallic-green rounded-full flex items-center justify-center flex-shrink-0 mt-1 metallic-shine">
                        <i data-lucide="check" class="w-4 h-4 text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gold"><?php echo $lang['benefit_implementacion']; ?></p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['benefit_implementacion_desc']; ?></p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-metallic-green rounded-full flex items-center justify-center flex-shrink-0 mt-1 metallic-shine">
                        <i data-lucide="check" class="w-4 h-4 text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gold"><?php echo $lang['benefit_soporte']; ?></p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['benefit_soporte_desc']; ?></p>
                    </div>
                </div>
            </div>

            <div class="mt-12">
                <a href="mailto:contacto@validarcert.com" class="inline-block px-10 py-4 text-lg font-bold text-black gold-gradient rounded-lg hover:opacity-90 transition-opacity shadow-2xl shadow-gold/30">
                    <?php echo $lang['contacto_cta_btn']; ?>
                </a>
                <p class="mt-4 text-sm text-gray-400">
                    <?php echo $lang['contacto_cta_email']; ?> <a href="mailto:contacto@validarcert.com" class="underline hover:text-gold text-gray-300">contacto@validarcert.com</a>
                </p>
            </div>

            <!-- Social proof -->
            <div class="mt-16 pt-12 border-t border-gold/20">
                <p class="text-gray-400 mb-6"><?php echo $lang['social_confianza']; ?></p>
                <div class="flex flex-wrap justify-center items-center gap-8">
                    <div class="text-2xl font-bold text-white bg-metallic-green px-6 py-3 rounded-lg metallic-shine">SAJuR</div>
                    <div class="text-2xl font-bold text-white bg-metallic-green px-6 py-3 rounded-lg metallic-shine">Liberté</div>
                    <div class="text-sm text-gray-400 italic"><?php echo $lang['social_mas']; ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Modal -->
    <div id="video-modal" class="fixed inset-0 bg-black/95 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
        <div class="relative w-full max-w-4xl">
            <button onclick="closeVideoModal()" class="absolute -top-12 right-0 text-white hover:text-gold transition-colors">
                <i data-lucide="x" class="w-8 h-8"></i>
            </button>
            <div class="relative aspect-video bg-gray-900 rounded-xl overflow-hidden border-2 border-gold/30 shadow-2xl shadow-gold/20">
                <iframe id="video-iframe" class="w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-950 text-gray-300 border-t border-gold/10">
        <div class="container mx-auto px-6 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="h-8 w-8 text-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        <span class="text-xl font-bold text-gold">OriginalisDoc</span>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-400"><?php echo $lang['footer_descripcion']; ?></p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-gold font-bold mb-4"><?php echo $lang['footer_enlaces']; ?></h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#beneficios" class="hover:text-gold transition-colors"><?php echo $lang['footer_link_beneficios']; ?></a></li>
                        <li><a href="#como-funciona" class="hover:text-gold transition-colors"><?php echo $lang['footer_link_como']; ?></a></li>
                        <li><a href="#validar" class="hover:text-gold transition-colors"><?php echo $lang['footer_link_validar']; ?></a></li>
                        <li><a href="#contacto" class="hover:text-gold transition-colors"><?php echo $lang['footer_link_demo']; ?></a></li>
                    </ul>
                </div>

                <!-- Features -->
                <div>
                    <h4 class="text-gold font-bold mb-4"><?php echo $lang['footer_caracteristicas']; ?></h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-metallic-green-light"></i>
                            <span><?php echo $lang['footer_feat_qr']; ?></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-metallic-green-light"></i>
                            <span><?php echo $lang['footer_feat_portal']; ?></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-metallic-green-light"></i>
                            <span><?php echo $lang['footer_feat_impresion']; ?></span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-metallic-green-light"></i>
                            <span><?php echo $lang['footer_feat_personalizable']; ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-gold font-bold mb-4"><?php echo $lang['footer_contacto']; ?></h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-gold mt-0.5"></i>
                            <a href="mailto:contacto@validarcert.com" class="hover:text-gold transition-colors">contacto@validarcert.com</a>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-gold mt-0.5"></i>
                            <span>Argentina</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-gold mt-0.5"></i>
                            <span><?php echo $lang['footer_horario']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Security & Trust Badges -->
            <div class="mt-12 pt-8 border-t border-gray-800">
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-500 mb-4"><?php echo $lang['footer_seguridad']; ?></p>
                </div>
                <div class="flex flex-wrap justify-center items-center gap-6 md:gap-8">
                    <!-- SSL Secure -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <i data-lucide="shield-check" class="w-5 h-5 text-metallic-green-light"></i>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold"><?php echo $lang['badge_ssl']; ?></div>
                            <div class="text-xs text-gray-400"><?php echo $lang['badge_ssl_desc']; ?></div>
                        </div>
                    </div>

                    <!-- Data Protection -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <i data-lucide="lock" class="w-5 h-5 text-metallic-green-light"></i>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold"><?php echo $lang['badge_https']; ?></div>
                            <div class="text-xs text-gray-400"><?php echo $lang['badge_https_desc']; ?></div>
                        </div>
                    </div>

                    <!-- Privacy -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <i data-lucide="shield" class="w-5 h-5 text-metallic-green-light"></i>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold"><?php echo $lang['badge_privacidad']; ?></div>
                            <div class="text-xs text-gray-400"><?php echo $lang['badge_privacidad_desc']; ?></div>
                        </div>
                    </div>

                    <!-- Backup -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <i data-lucide="database" class="w-5 h-5 text-metallic-green-light"></i>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold"><?php echo $lang['badge_backup']; ?></div>
                            <div class="text-xs text-gray-400"><?php echo $lang['badge_backup_desc']; ?></div>
                        </div>
                    </div>

                    <!-- Uptime -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <i data-lucide="activity" class="w-5 h-5 text-metallic-green-light"></i>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold"><?php echo $lang['badge_uptime']; ?></div>
                            <div class="text-xs text-gray-400"><?php echo $lang['badge_uptime_desc']; ?></div>
                        </div>
                    </div>

                    <!-- Support -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <i data-lucide="headphones" class="w-5 h-5 text-metallic-green-light"></i>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold"><?php echo $lang['badge_soporte']; ?></div>
                            <div class="text-xs text-gray-400"><?php echo $lang['badge_soporte_desc']; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="mt-12 pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center text-sm">
                <p class="text-gray-500"><?php echo $lang['footer_copyright']; ?></p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <a href="terminos.php?lang=<?php echo $current_language; ?>" class="hover:text-gold transition-colors"><?php echo $lang['footer_terminos']; ?></a>
                    <a href="privacidad.php?lang=<?php echo $current_language; ?>" class="hover:text-gold transition-colors"><?php echo $lang['footer_privacidad']; ?></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/5491112345678?text=Hola!%20Quiero%20información%20sobre%20OriginalisDoc" target="_blank" class="fixed bottom-24 right-8 w-14 h-14 bg-metallic-green rounded-full shadow-2xl shadow-metallic-green/30 transition-all duration-300 flex items-center justify-center group hover:scale-110 z-50 metallic-shine">
        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        <span class="absolute -top-1 -right-1 w-3 h-3 bg-metallic-red rounded-full animate-pulse"></span>
    </a>

    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" class="fixed bottom-8 right-8 w-12 h-12 gold-gradient rounded-full shadow-2xl shadow-gold/30 opacity-0 pointer-events-none transition-all duration-300 flex items-center justify-center group hover:scale-110 z-50">
        <i data-lucide="arrow-up" class="w-6 h-6 text-black"></i>
    </button>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Reading Progress Bar
        const progressBar = document.getElementById('reading-progress');

        window.addEventListener('scroll', () => {
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight - windowHeight;
            const scrolled = window.pageYOffset;
            const progress = (scrolled / documentHeight) * 100;
            progressBar.style.width = progress + '%';
        });

        // Scroll to Top functionality
        const scrollBtn = document.getElementById('scroll-to-top');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollBtn.classList.remove('opacity-0', 'pointer-events-none');
                scrollBtn.classList.add('opacity-100', 'pointer-events-auto');
            } else {
                scrollBtn.classList.add('opacity-0', 'pointer-events-none');
                scrollBtn.classList.remove('opacity-100', 'pointer-events-auto');
            }
        });

        scrollBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Add smooth scroll to all anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animated Counters
        function animateCounter(element) {
            const target = parseFloat(element.getAttribute('data-target'));
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16); // 60fps
            let current = 0;

            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    // Format number based on size
                    if (target >= 1000) {
                        element.textContent = Math.floor(current).toLocaleString('es-AR');
                    } else if (target % 1 !== 0) {
                        element.textContent = current.toFixed(1);
                    } else {
                        element.textContent = Math.floor(current);
                    }
                    requestAnimationFrame(updateCounter);
                } else {
                    // Final value
                    if (target >= 1000) {
                        element.textContent = target.toLocaleString('es-AR');
                    } else if (target % 1 !== 0) {
                        element.textContent = target.toFixed(1);
                    } else {
                        element.textContent = target;
                    }
                }
            };

            updateCounter();
        }

        // Intersection Observer for counters
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    animateCounter(entry.target);
                    entry.target.classList.add('counted');
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.counter').forEach(counter => {
            counterObserver.observe(counter);
        });

        // Parallax Effect
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;

            const slowElements = document.querySelectorAll('.parallax-slow');
            const mediumElements = document.querySelectorAll('.parallax-medium');
            const fastElements = document.querySelectorAll('.parallax-fast');

            slowElements.forEach(el => {
                el.style.transform = `translateY(${scrolled * 0.1}px)`;
            });

            mediumElements.forEach(el => {
                el.style.transform = `translate(-50%, calc(-50% + ${scrolled * 0.3}px))`;
            });

            fastElements.forEach(el => {
                el.style.transform = `translateY(${scrolled * 0.5}px)`;
            });
        });

        // Video Modal Functions
        function openVideoModal() {
            const modal = document.getElementById('video-modal');
            const iframe = document.getElementById('video-iframe');
            // Reemplaza con tu URL de video de YouTube (ejemplo)
            iframe.src = 'https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            // Re-render icons in modal
            setTimeout(() => lucide.createIcons(), 100);
        }

        function closeVideoModal() {
            const modal = document.getElementById('video-modal');
            const iframe = document.getElementById('video-iframe');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            iframe.src = '';
            document.body.style.overflow = 'auto';
        }

        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeVideoModal();
            }
        });

        // Close modal on background click
        document.getElementById('video-modal').addEventListener('click', (e) => {
            if (e.target.id === 'video-modal') {
                closeVideoModal();
            }
        });

        // Carousel Functions
        let currentSlide = 0;
        const totalSlides = 4;
        const carouselTrack = document.getElementById('carousel-track');
        const indicators = document.querySelectorAll('.carousel-indicator');
        let carouselInterval;
        let isCarouselPaused = false;

        function updateCarousel() {
            carouselTrack.style.transform = `translateX(-${currentSlide * 100}%)`;

            // Update indicators
            indicators.forEach((indicator, index) => {
                if (index === currentSlide) {
                    indicator.classList.remove('bg-gray-600');
                    indicator.classList.add('bg-gold');
                } else {
                    indicator.classList.remove('bg-gold');
                    indicator.classList.add('bg-gray-600');
                }
            });

            // Re-render icons
            setTimeout(() => lucide.createIcons(), 100);
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateCarousel();
        }

        function previousSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateCarousel();
        }

        function toggleCarouselPause() {
            const pauseIcon = document.getElementById('carousel-pause-icon');
            const pauseBtn = document.getElementById('carousel-pause-btn');

            if (isCarouselPaused) {
                // Resume carousel
                carouselInterval = setInterval(nextSlide, 5000);
                pauseIcon.setAttribute('data-lucide', 'pause');
                pauseBtn.setAttribute('title', 'Pausar');
                isCarouselPaused = false;
            } else {
                // Pause carousel
                clearInterval(carouselInterval);
                pauseIcon.setAttribute('data-lucide', 'play');
                pauseBtn.setAttribute('title', 'Reproducir');
                isCarouselPaused = true;
            }

            // Re-render icons
            lucide.createIcons();
        }

        // Auto-advance carousel every 5 seconds
        carouselInterval = setInterval(nextSlide, 5000);

        // Dark Mode Toggle
        function toggleDarkMode() {
            const body = document.body;
            const icon = document.getElementById('theme-icon');
            const button = document.getElementById('theme-button');

            body.classList.toggle('light-mode');

            if (body.classList.contains('light-mode')) {
                icon.setAttribute('data-lucide', 'moon');
                button.setAttribute('title', '<?php echo $lang['nav_tema_oscuro']; ?>');
                localStorage.setItem('theme', 'light');
            } else {
                icon.setAttribute('data-lucide', 'sun');
                button.setAttribute('title', '<?php echo $lang['nav_tema_claro']; ?>');
                localStorage.setItem('theme', 'dark');
            }

            lucide.createIcons();
        }

        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.body.classList.add('light-mode');
            document.getElementById('theme-icon').setAttribute('data-lucide', 'moon');
            lucide.createIcons();
        }

        // Language Selector Toggle
        function toggleLangMenu() {
            const langMenu = document.getElementById('lang-menu');
            const chevron = document.getElementById('lang-chevron');

            if (langMenu.classList.contains('opacity-0')) {
                // Show menu
                langMenu.classList.remove('opacity-0', 'invisible');
                langMenu.classList.add('opacity-100', 'visible');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                // Hide menu
                langMenu.classList.add('opacity-0', 'invisible');
                langMenu.classList.remove('opacity-100', 'visible');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Close language menu when clicking outside
        document.addEventListener('click', (e) => {
            const langSelector = document.getElementById('lang-selector');
            const langMenu = document.getElementById('lang-menu');

            if (langSelector && !langSelector.contains(e.target)) {
                const chevron = document.getElementById('lang-chevron');
                langMenu.classList.add('opacity-0', 'invisible');
                langMenu.classList.remove('opacity-100', 'visible');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        });

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all benefit cards and service cards
        document.querySelectorAll('.bg-gray-900').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(card);
        });

        // Sector Selection Functionality
        let selectedSector = null;

        function selectSector(sector) {
            // Remove previous selection
            document.querySelectorAll('.sector-card').forEach(card => {
                card.classList.remove('ring-2', 'ring-gold', 'ring-metallic-green-light', 'ring-blue-400', 'ring-purple-400', 'ring-orange-400');
                card.classList.remove('bg-gray-800');
                card.classList.add('bg-gray-900/80');
            });

            // Add selection to clicked card
            const selectedCard = document.querySelector(`[data-sector="${sector}"]`);
            selectedCard.classList.remove('bg-gray-900/80');
            selectedCard.classList.add('bg-gray-800');
            
            // Add colored ring based on sector
            switch(sector) {
                case 'academico':
                    selectedCard.classList.add('ring-2', 'ring-gold');
                    break;
                case 'profesional':
                    selectedCard.classList.add('ring-2', 'ring-metallic-green-light');
                    break;
                case 'empresarial':
                    selectedCard.classList.add('ring-2', 'ring-blue-400');
                    break;
                case 'institucional':
                    selectedCard.classList.add('ring-2', 'ring-purple-400');
                    break;
                case 'cooperativas':
                    selectedCard.classList.add('ring-2', 'ring-orange-400');
                    break;
            }

            // Show indicator and update content
            const indicator = document.getElementById('sector-indicator');
            indicator.classList.remove('opacity-0');
            indicator.classList.add('opacity-100');

            // Update content based on sector
            updateContentBySector(sector);
            selectedSector = sector;

            // Smooth scroll to benefits section
            setTimeout(() => {
                document.getElementById('beneficios').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 300);
        }

        function updateContentBySector(sector) {
            const sectorTitle = document.getElementById('sector-title');
            const sectorSubtitle = document.getElementById('sector-subtitle');
            const sectorContent = document.getElementById('sector-content');

            switch(sector) {
                case 'academico':
                    sectorTitle.textContent = 'Soluciones para Instituciones Académicas';
                    sectorSubtitle.textContent = 'Digitaliza la gestión académica y enfócate en educar con certificados y analíticos infalsificables.';
                    sectorContent.innerHTML = getAcademicoTemplate();
                    break;
                case 'profesional':
                    sectorTitle.textContent = 'Herramientas para Profesionales Independientes';
                    sectorSubtitle.textContent = 'Digitaliza tu práctica profesional con recetas, planes y documentos verificables.';
                    sectorContent.innerHTML = getProfesionalTemplate();
                    break;
                case 'empresarial':
                    sectorTitle.textContent = 'Soluciones Corporativas y Empresariales';
                    sectorSubtitle.textContent = 'Gestiona capacitaciones, membresías y documentación empresarial con máxima eficiencia.';
                    sectorContent.innerHTML = getEmpresarialTemplate();
                    break;
                case 'institucional':
                    sectorTitle.textContent = 'Tecnología para Organismos Públicos';
                    sectorSubtitle.textContent = 'Moderniza la emisión de documentos oficiales con máxima seguridad y transparencia.';
                    sectorContent.innerHTML = getInstitucionalTemplate();
                    break;
                case 'cooperativas':
                    sectorTitle.textContent = 'Servicios para Cooperativas y Mutuales';
                    sectorSubtitle.textContent = 'Gestiona carnets, certificados y constancias para socios y miembros con validación digital.';
                    sectorContent.innerHTML = getCooperativasTemplate();
                    break;
            }
            
            // Update "Otros Servicios" section
            updateOtrosServicios(sector);
            
            // Re-initialize Lucide icons
            setTimeout(() => lucide.createIcons(), 100);
        }

        function getAcademicoTemplate() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-800/50 border border-gold/30 rounded-xl p-6 hover:border-gold/70 transition-all">
                        <div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="graduation-cap" class="w-6 h-6 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Certificados Académicos</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificados de finalización, aprobación y participación con trayectoria académica completa.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-metallic-green/30 rounded-xl p-6 hover:border-metallic-green/70 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-metallic-green to-metallic-green-light rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="file-text" class="w-6 h-6 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2">Analíticos Digitales</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Registros académicos completos con notas, asistencias y competencias desarrolladas.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-gold/30 rounded-xl p-6 hover:border-gold/70 transition-all">
                        <div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="users" class="w-6 h-6 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Portal de Estudiantes</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Acceso 24/7 para estudiantes a su trayectoria, documentos y progreso académico.</p>
                    </div>
                </div>
            `;
        }

        function getProfesionalTemplate() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-800/50 border border-metallic-green/30 rounded-xl p-6 hover:border-metallic-green/70 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-metallic-green to-metallic-green-light rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="file-plus" class="w-6 h-6 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2">Recetas Médicas Digitales</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Recetas infalsificables con validación instantánea en farmacias via código QR.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-gold/30 rounded-xl p-6 hover:border-gold/70 transition-all">
                        <div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="apple" class="w-6 h-6 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Planes Nutricionales y Ejercicios</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Planes personalizados para personal trainers y nutricionistas con seguimiento móvil.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-metallic-green/30 rounded-xl p-6 hover:border-metallic-green/70 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-metallic-green to-metallic-green-light rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="clipboard-check" class="w-6 h-6 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2">Peritajes e Informes</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Informes técnicos, peritajes y documentación legal con garantía anti-adulteración.</p>
                    </div>
                </div>
            `;
        }

        function getEmpresarialTemplate() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-800/50 border border-blue-500/30 rounded-xl p-6 hover:border-blue-500/70 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-blue-400 mb-2">Capacitación Corporativa</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificaciones internas para desarrollo profesional y cumplimiento normativo.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-gold/30 rounded-xl p-6 hover:border-gold/70 transition-all">
                        <div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="calendar" class="w-6 h-6 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Gestión de Eventos</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificados de participación en conferencias, workshops y seminarios empresariales.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-blue-500/30 rounded-xl p-6 hover:border-blue-500/70 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="shield" class="w-6 h-6 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-blue-400 mb-2">Garantías y Documentación</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificados de calidad, garantías de productos y documentación técnica empresarial.</p>
                    </div>
                </div>
            `;
        }

        function getInstitucionalTemplate() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-800/50 border border-purple-500/30 rounded-xl p-6 hover:border-purple-500/70 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="file-check" class="w-6 h-6 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-purple-400 mb-2">Documentos Oficiales</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificados gubernamentales y registros públicos con máxima seguridad.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-gold/30 rounded-xl p-6 hover:border-gold/70 transition-all">
                        <div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="credit-card" class="w-6 h-6 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Carnets de Identificación</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Credenciales oficiales para funcionarios y empleados públicos.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-purple-500/30 rounded-xl p-6 hover:border-purple-500/70 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="eye" class="w-6 h-6 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-purple-400 mb-2">Transparencia Ciudadana</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Portal público de validación para verificación ciudadana instantánea.</p>
                    </div>
                </div>
            `;
        }

        function getCooperativasTemplate() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-800/50 border border-orange-500/30 rounded-xl p-6 hover:border-orange-500/70 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-600 to-orange-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="credit-card" class="w-6 h-6 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-orange-400 mb-2">Carnets de Socios</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Credenciales digitales y físicas para socios de cooperativas y mutuales.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-gold/30 rounded-xl p-6 hover:border-gold/70 transition-all">
                        <div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="award" class="w-6 h-6 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Certificados de Participación</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificaciones de participación en asambleas y actividades cooperativas.</p>
                    </div>
                    <div class="bg-gray-800/50 border border-orange-500/30 rounded-xl p-6 hover:border-orange-500/70 transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-600 to-orange-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="file-text" class="w-6 h-6 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-orange-400 mb-2">Constancias de Aportes</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Comprobantes verificables de aportes y contribuciones a la organización.</p>
                    </div>
                </div>
            `;
        }

        function updateOtrosServicios(sector) {
            const otrosTitle = document.getElementById('otros-servicios-title');
            const otrosSubtitle = document.getElementById('otros-servicios-subtitle');
            const otrosContent = document.getElementById('otros-servicios-content');

            switch(sector) {
                case 'academico':
                    otrosTitle.textContent = 'Más Allá de lo Académico';
                    otrosSubtitle.textContent = 'También ofrecemos servicios profesionales y empresariales que pueden complementar tu institución';
                    otrosContent.innerHTML = getOtrosServiciosAcademico();
                    break;
                case 'profesional':
                    otrosTitle.textContent = 'Más Allá de lo Profesional';
                    otrosSubtitle.textContent = 'También ofrecemos servicios académicos y empresariales para expandir tu práctica';
                    otrosContent.innerHTML = getOtrosServiciosProfesional();
                    break;
                case 'empresarial':
                    otrosTitle.textContent = 'Más Allá de lo Empresarial';
                    otrosSubtitle.textContent = 'También ofrecemos servicios académicos y profesionales para diversificar tu oferta';
                    otrosContent.innerHTML = getOtrosServiciosEmpresarial();
                    break;
                case 'institucional':
                    otrosTitle.textContent = 'Más Allá de lo Institucional';
                    otrosSubtitle.textContent = 'También ofrecemos servicios para el sector privado y académico';
                    otrosContent.innerHTML = getOtrosServiciosInstitucional();
                    break;
                case 'cooperativas':
                    otrosTitle.textContent = 'Más Allá del Cooperativismo';
                    otrosSubtitle.textContent = 'También ofrecemos servicios profesionales y académicos para complementar tu cooperativa';
                    otrosContent.innerHTML = getOtrosServiciosCooperativas();
                    break;
                default:
                    otrosTitle.textContent = 'Más Allá de la Educación';
                    otrosSubtitle.textContent = 'OriginalisDoc se adapta a múltiples industrias y profesionales';
                    otrosContent.innerHTML = getOtrosServiciosDefault();
                    break;
            }
        }

        function getOtrosServiciosAcademico() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="file-plus" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2">Recetas y Planes</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Para profesores de educación física y nutrición que necesitan emitir planes verificables.</p>
                    </div>
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="credit-card" class="w-5 h-5 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Carnets Institucionales</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Credenciales para docentes, personal administrativo y estudiantes.</p>
                    </div>
                    <div class="bg-gray-900 border border-blue-500/20 rounded-xl p-6 hover:border-blue-500/50 transition-all">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="calendar" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-blue-400 mb-2">Eventos Académicos</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificados para conferencias, congresos y seminarios institucionales.</p>
                    </div>
                </div>
            `;
        }

        function getOtrosServiciosProfesional() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Cursos y Capacitaciones</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Emite certificados para los cursos y talleres que dictes en tu especialidad.</p>
                    </div>
                    <div class="bg-gray-900 border border-blue-500/20 rounded-xl p-6 hover:border-blue-500/50 transition-all">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-blue-400 mb-2">Capacitación Empresarial</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Servicios de consultoría y capacitación para empresas.</p>
                    </div>
                    <div class="bg-gray-900 border border-orange-500/20 rounded-xl p-6 hover:border-orange-500/50 transition-all">
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-600 to-orange-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="users" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-orange-400 mb-2">Membresías Profesionales</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Carnets para asociaciones y colegios profesionales.</p>
                    </div>
                </div>
            `;
        }

        function getOtrosServiciosEmpresarial() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Universidad Corporativa</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificados académicos para programas de formación interna.</p>
                    </div>
                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="file-plus" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2">Medicina del Trabajo</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Recetas y planes de salud ocupacional para empleados.</p>
                    </div>
                    <div class="bg-gray-900 border border-orange-500/20 rounded-xl p-6 hover:border-orange-500/50 transition-all">
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-600 to-orange-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="handshake" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-orange-400 mb-2">Cooperativas Empresariales</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Servicios para cooperativas de trabajo y mutual empresarial.</p>
                    </div>
                </div>
            `;
        }

        function getOtrosServiciosInstitucional() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Educación Pública</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificados para institutos y universidades públicas.</p>
                    </div>
                    <div class="bg-gray-900 border border-blue-500/20 rounded-xl p-6 hover:border-blue-500/50 transition-all">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="building" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-blue-400 mb-2">Licitaciones Públicas</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Documentación verificable para procesos de contratación.</p>
                    </div>
                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="clipboard-check" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2">Servicios Profesionales</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Validación de informes técnicos y peritajes oficiales.</p>
                    </div>
                </div>
            `;
        }

        function getOtrosServiciosCooperativas() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Capacitación de Socios</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificados de cursos y talleres para desarrollo cooperativo.</p>
                    </div>
                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="file-plus" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2">Servicios de Salud</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Recetas y planes para mutuales de salud y bienestar.</p>
                    </div>
                    <div class="bg-gray-900 border border-blue-500/20 rounded-xl p-6 hover:border-blue-500/50 transition-all">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="shield" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-blue-400 mb-2">Documentación Legal</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Garantías y documentos verificables para servicios cooperativos.</p>
                    </div>
                </div>
            `;
        }

        function getOtrosServiciosDefault() {
            return `
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-black"></i>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2">Sector Académico</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Certificados, analíticos y constancias académicas.</p>
                    </div>
                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="briefcase" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2">Sector Profesional</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Recetas, planes y documentación técnica.</p>
                    </div>
                    <div class="bg-gray-900 border border-blue-500/20 rounded-xl p-6 hover:border-blue-500/50 transition-all">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="building-2" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-blue-400 mb-2">Sector Empresarial</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Capacitaciones, eventos y documentación corporativa.</p>
                    </div>
                    <div class="bg-gray-900 border border-orange-500/20 rounded-xl p-6 hover:border-orange-500/50 transition-all">
                        <div class="w-10 h-10 bg-gradient-to-br from-orange-600 to-orange-400 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="handshake" class="w-5 h-5 text-white"></i>
                        </div>
                        <h4 class="text-lg font-bold text-orange-400 mb-2">Cooperativas</h4>
                        <p class="text-sm text-gray-300 leading-relaxed">Carnets, certificados y constancias para socios.</p>
                    </div>
                </div>
            `;
        }

        // Add hover effects to sector cards
        document.querySelectorAll('.sector-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                if (!this.classList.contains('bg-gray-800')) {
                    this.style.transform = 'translateY(-4px)';
                }
            });

            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('bg-gray-800')) {
                    this.style.transform = 'translateY(0)';
                }
            });
        });

        // Dropdown menu functionality
        let activeDropdown = null;

        function toggleDropdown(dropdownName) {
            const menu = document.getElementById('menu-' + dropdownName);
            const chevron = document.getElementById('chevron-' + dropdownName);

            // If clicking the same dropdown, close it
            if (activeDropdown === dropdownName) {
                menu.classList.remove('opacity-100', 'visible');
                menu.classList.add('opacity-0', 'invisible');
                chevron.style.transform = 'rotate(0deg)';
                activeDropdown = null;
            } else {
                // Close any open dropdown
                if (activeDropdown) {
                    const oldMenu = document.getElementById('menu-' + activeDropdown);
                    const oldChevron = document.getElementById('chevron-' + activeDropdown);
                    oldMenu.classList.remove('opacity-100', 'visible');
                    oldMenu.classList.add('opacity-0', 'invisible');
                    oldChevron.style.transform = 'rotate(0deg)';
                }

                // Open the new dropdown
                menu.classList.remove('opacity-0', 'invisible');
                menu.classList.add('opacity-100', 'visible');
                chevron.style.transform = 'rotate(180deg)';
                activeDropdown = dropdownName;
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (activeDropdown) {
                const dropdown = document.getElementById('dropdown-' + activeDropdown);
                if (dropdown && !dropdown.contains(event.target)) {
                    const menu = document.getElementById('menu-' + activeDropdown);
                    const chevron = document.getElementById('chevron-' + activeDropdown);
                    menu.classList.remove('opacity-100', 'visible');
                    menu.classList.add('opacity-0', 'invisible');
                    chevron.style.transform = 'rotate(0deg)';
                    activeDropdown = null;
                }
            }
        });
    </script>

    <!-- Version Badge -->
    <div class="version-badge">
        v<?php echo APP_VERSION; ?>
    </div>

</body>
</html>