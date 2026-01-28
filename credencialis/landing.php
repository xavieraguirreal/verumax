<?php
/**
 * Credencialis - Landing Page de Marketing
 * Sistema de Credenciales Digitales para Organizaciones
 * Versión multi-idioma (MODULAR)
 * Color: Teal/Cyan (#0891b2)
 */
require_once '../config.php';

// Definir módulos de idioma a cargar (modo modular)
$lang_modules = ['common', 'land_credencialis'];
require_once '../lang_config.php';
require_once '../includes/currency_converter.php';
require_once '../includes/pricing_config.php';
require_once '../includes/cache_helper.php';

// =====================================
// SISTEMA DE CACHÉ
// =====================================
$skip_cache = $lang_debug_mode || (isset($_GET['nocache']) && $_GET['nocache'] === '1');
$cache_key = 'credencialis_' . $current_language;

if (!$skip_cache) {
    $cached_page = get_cached_page($cache_key, 3600);
    if ($cached_page) {
        echo $cached_page;
        exit;
    }
}

ob_start();
?>
<!DOCTYPE html>
<html lang="<?php echo substr($current_language, 0, 2); ?>" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <title><?php echo $lang['cred_meta_title']; ?></title>
    <meta name="description" content="<?php echo $lang['cred_meta_description']; ?>">
    <meta name="keywords" content="<?php echo $lang['cred_meta_keywords']; ?>">
    <meta name="author" content="<?php echo $lang['meta_author'] ?? 'Verumax'; ?>">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://<?php echo $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>">

    <!-- Metadatos adicionales -->
    <meta name="language" content="<?php echo substr($current_language, 0, 2); ?>">
    <meta name="revisit-after" content="7 days">

    <!-- Hreflang para versiones de idioma -->
    <link rel="alternate" hreflang="es-ar" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/credencialis/?lang=es_AR">
    <link rel="alternate" hreflang="pt-br" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/credencialis/?lang=pt_BR">
    <link rel="alternate" hreflang="x-default" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/credencialis/">

    <!-- Geo Tags -->
    <meta name="geo.region" content="AR">
    <meta name="geo.placename" content="Argentina">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo $lang['cred_meta_og_title']; ?>">
    <meta property="og:description" content="<?php echo $lang['cred_meta_og_description']; ?>">
    <meta property="og:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/og-image-credencialis.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="<?php echo str_replace('_', '_', $current_language); ?>">
    <meta property="og:site_name" content="Verumax Credencialis">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta name="twitter:title" content="<?php echo $lang['cred_meta_twitter_title']; ?>">
    <meta name="twitter:description" content="<?php echo $lang['cred_meta_twitter_description']; ?>">
    <meta name="twitter:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/og-image-credencialis.png">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/images/logo-verumax-escudo.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/logo-verumax-escudo.png">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <!-- Preload -->
    <link rel="preload" href="/assets/css/tailwind.min.css" as="style">
    <link rel="preload" href="<?php echo CSS_PATH; ?>styles.css" as="style">

    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>

    <!-- Flag Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" media="print" onload="this.media='all'">

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="/assets/css/tailwind.min.css">

    <!-- Estilos Compartidos -->
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>styles.css">

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "SoftwareApplication",
                "name": "Credencialis",
                "applicationCategory": "BusinessApplication",
                "operatingSystem": "Web",
                "description": "<?php echo htmlspecialchars($lang['cred_meta_description']); ?>",
                "applicationSubCategory": "Gestión de Credenciales Digitales",
                "featureList": [
                    "Credenciales digitales verificables",
                    "Validación QR instantánea",
                    "Acceso 24/7 desde el celular",
                    "Actualización automática de datos",
                    "Carga masiva desde Excel",
                    "Personalización de branding institucional"
                ]
            },
            {
                "@type": "Organization",
                "name": "Verumax",
                "url": "https://<?php echo $_SERVER['HTTP_HOST']; ?>",
                "logo": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/assets/images/logo-verumax-escudo.png"
            },
            {
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "<?php echo htmlspecialchars($lang['cred_faq_acceso_titulo']); ?>",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "<?php echo htmlspecialchars($lang['cred_faq_acceso_desc']); ?>"
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "<?php echo htmlspecialchars($lang['cred_faq_validar_titulo']); ?>",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "<?php echo htmlspecialchars($lang['cred_faq_validar_desc']); ?>"
                        }
                    }
                ]
            }
        ]
    }
    </script>

    <style>
        /* Colores Credencialis - Teal/Cyan (#0891b2) */
        .credencialis-gradient {
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 50%, #06b6d4 100%);
        }
        .credencialis-text {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .credencialis-btn {
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 100%);
        }
        .credencialis-btn:hover {
            background: linear-gradient(135deg, #155e75 0%, #0e7490 100%);
        }
        .credencialis-border {
            border-color: rgba(8, 145, 178, 0.3);
        }
        .credencialis-border:hover {
            border-color: rgba(8, 145, 178, 0.5);
        }
        .credencialis-bg {
            background-color: rgba(8, 145, 178, 0.1);
        }
        .credencialis-bg-dark {
            background-color: rgba(8, 145, 178, 0.2);
        }
    </style>
</head>
<body class="bg-black text-white">

    <!-- Banner de debug de idioma -->
    <?php echo get_lang_debug_banner(); ?>

    <!-- Loader -->
    <div id="page-loader" class="fixed inset-0 bg-black z-[9999] flex items-center justify-center transition-opacity duration-300">
        <div class="text-center">
            <div class="relative mb-6">
                <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-cyan-800 via-cyan-600 to-cyan-400 opacity-20 animate-ping"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-12 h-12 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                </div>
            </div>
            <p class="text-cyan-500 text-sm font-medium tracking-wider">CREDENCIALIS</p>
            <div class="mt-4 w-48 h-1 bg-gray-800 rounded-full overflow-hidden mx-auto">
                <div class="h-full bg-gradient-to-r from-cyan-800 via-cyan-500 to-cyan-800 rounded-full animate-loading-bar"></div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-black border-b border-cyan-600/20 sticky top-0 z-50 backdrop-blur-sm bg-black/90">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="../assets/images/logo-verumax-escudo.png" alt="Verumax" class="h-10 w-10" width="40" height="40">
                    <div>
                        <a href="../?lang=<?php echo $current_language; ?>" class="flex items-center">
                            <img src="../assets/images/logo-verumax-texto.png" alt="Verumax" class="h-8" width="120" height="32">
                        </a>
                        <p class="text-xs text-gray-400">Credencialis</p>
                    </div>
                </div>

                <!-- Links de navegación (desktop) -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="../?lang=<?php echo $current_language; ?>" class="text-gray-300 hover:text-cyan-400 transition-colors text-sm flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <?php echo $lang['nav_inicio'] ?? 'Inicio'; ?>
                    </a>
                    <a href="#beneficios" class="text-gray-300 hover:text-cyan-400 transition-colors text-sm"><?php echo $lang['cred_nav_beneficios']; ?></a>
                    <a href="#funcionalidades" class="text-gray-300 hover:text-cyan-400 transition-colors text-sm"><?php echo $lang['cred_nav_funcionalidades']; ?></a>
                    <a href="#casos-exito" class="text-gray-300 hover:text-cyan-400 transition-colors text-sm"><?php echo $lang['cred_nav_casos']; ?></a>
                    <a href="#planes" class="text-gray-300 hover:text-cyan-400 transition-colors text-sm"><?php echo $lang['cred_nav_planes']; ?></a>
                    <a href="#faq" class="text-gray-300 hover:text-cyan-400 transition-colors text-sm"><?php echo $lang['cred_nav_faq']; ?></a>
                </div>

                <!-- Controles -->
                <div class="flex items-center gap-2 md:gap-3">
                    <!-- Selector de Idioma -->
                    <div class="relative">
                        <button id="langToggle" class="flex items-center gap-1 px-2 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                            <?php echo get_flag_emoji($current_language); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="langMenu" class="hidden absolute right-0 mt-2 w-48 bg-gray-900 border border-cyan-600/20 rounded-lg shadow-lg overflow-hidden z-50">
                            <?php
                            // Solo mostrar es_AR y pt_BR para Credencialis
                            $credencialis_languages = ['es_AR' => 'Español (Argentina)', 'pt_BR' => 'Português (Brasil)'];
                            foreach ($credencialis_languages as $code => $name): ?>
                                <a href="?lang=<?php echo $code; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 transition-colors <?php echo $current_language === $code ? 'bg-gray-800' : ''; ?>">
                                    <?php echo get_flag_emoji($code); ?>
                                    <span class="text-sm"><?php echo $name; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Botón Demo (desktop) -->
                    <a href="#contacto" class="hidden md:flex px-4 py-2 credencialis-btn text-white font-semibold rounded-lg hover:opacity-90 transition-all text-sm"><?php echo $lang['cred_nav_solicitar_demo']; ?></a>

                    <!-- Hamburger (móvil) -->
                    <button id="mobileMenuBtn" class="md:hidden flex flex-col justify-center items-center rounded-lg hover:bg-gray-800 transition-colors" style="width: 44px; height: 44px;">
                        <span class="hamburger-line rounded transition-all duration-300" style="width: 24px; height: 3px; background: white;"></span>
                        <span class="hamburger-line rounded transition-all duration-300" style="width: 24px; height: 3px; background: white; margin-top: 5px;"></span>
                        <span class="hamburger-line rounded transition-all duration-300" style="width: 24px; height: 3px; background: white; margin-top: 5px;"></span>
                    </button>
                </div>
            </div>

            <!-- Menú Móvil -->
            <div id="mobileMenu" class="hidden md:hidden mt-4 pb-4 border-t border-cyan-600/20 pt-4">
                <div class="flex flex-col gap-3">
                    <a href="../?lang=<?php echo $current_language; ?>" class="text-gray-300 hover:text-cyan-400 transition-colors flex items-center gap-2 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <?php echo $lang['nav_volver_inicio'] ?? 'Volver al inicio'; ?>
                    </a>
                    <a href="#beneficios" class="text-gray-300 hover:text-cyan-400 transition-colors py-2"><?php echo $lang['cred_nav_beneficios']; ?></a>
                    <a href="#funcionalidades" class="text-gray-300 hover:text-cyan-400 transition-colors py-2"><?php echo $lang['cred_nav_funcionalidades']; ?></a>
                    <a href="#casos-exito" class="text-gray-300 hover:text-cyan-400 transition-colors py-2"><?php echo $lang['cred_nav_casos']; ?></a>
                    <a href="#planes" class="text-gray-300 hover:text-cyan-400 transition-colors py-2"><?php echo $lang['cred_nav_planes']; ?></a>
                    <a href="#faq" class="text-gray-300 hover:text-cyan-400 transition-colors py-2"><?php echo $lang['cred_nav_faq']; ?></a>

                    <div class="flex items-center gap-3 py-2 border-t border-gray-800 mt-2 pt-4">
                        <?php foreach ($credencialis_languages as $code => $name): ?>
                            <a href="?lang=<?php echo $code; ?>" class="flex items-center gap-2 px-3 py-2 rounded-lg <?php echo $current_language === $code ? 'bg-cyan-600/20 border border-cyan-600/30' : 'bg-gray-800'; ?> transition-colors">
                                <?php echo get_flag_emoji($code); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <a href="#contacto" class="mt-2 px-6 py-3 credencialis-btn text-white font-semibold rounded-lg text-center"><?php echo $lang['cred_nav_solicitar_demo']; ?></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 hero-bg border-b border-cyan-600/20">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-block px-4 py-2 bg-cyan-600/20 border border-cyan-600/30 rounded-full mb-6">
                    <span class="text-cyan-400 text-sm font-semibold"><?php echo $lang['cred_hero_badge']; ?></span>
                </div>

                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    <?php echo $lang['cred_hero_title']; ?> <span class="credencialis-text"><?php echo $lang['cred_hero_title_highlight']; ?></span>
                </h1>

                <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-2xl mx-auto">
                    <?php echo $lang['cred_hero_propuesta_valor']; ?>
                </p>

                <!-- Beneficios destacados -->
                <div class="flex flex-wrap justify-center gap-3 mb-8 max-w-4xl mx-auto">
                    <div class="flex items-center gap-2 bg-cyan-600/10 border border-cyan-600/30 px-4 py-2 rounded-full">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-gray-200 text-sm"><?php echo $lang['cred_hero_benefit_1']; ?></span>
                    </div>
                    <div class="flex items-center gap-2 bg-cyan-600/10 border border-cyan-600/30 px-4 py-2 rounded-full">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <span class="text-gray-200 text-sm"><?php echo $lang['cred_hero_benefit_2']; ?></span>
                    </div>
                    <div class="flex items-center gap-2 bg-cyan-600/10 border border-cyan-600/30 px-4 py-2 rounded-full">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-gray-200 text-sm"><?php echo $lang['cred_hero_benefit_3']; ?></span>
                    </div>
                    <div class="flex items-center gap-2 bg-cyan-600/10 border border-cyan-600/30 px-4 py-2 rounded-full">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span class="text-gray-200 text-sm"><?php echo $lang['cred_hero_benefit_4']; ?></span>
                    </div>
                </div>

                <p class="text-xl text-gray-300 mb-8 shimmer-text">
                    <?php echo $lang['cred_hero_subtitle']; ?> <strong class="text-cyan-400"><?php echo $lang['cred_hero_clubes']; ?></strong>, <strong class="text-cyan-400"><?php echo $lang['cred_hero_cooperativas']; ?></strong>, <strong class="text-cyan-400"><?php echo $lang['cred_hero_mutuales']; ?></strong> y <strong class="text-cyan-400"><?php echo $lang['cred_hero_asociaciones']; ?></strong>
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="#contacto" class="px-8 py-4 credencialis-btn text-white font-bold rounded-lg transition-all text-lg">
                        <?php echo $lang['cred_cta_solicitar_demo']; ?>
                    </a>
                    <a href="#como-funciona" class="px-8 py-4 bg-gray-800 border border-cyan-600/30 text-cyan-400 font-semibold rounded-lg hover:bg-gray-700 transition-colors text-lg">
                        <?php echo $lang['cred_cta_ver_como_funciona']; ?>
                    </a>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-3 gap-6 mt-16 max-w-2xl mx-auto">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-cyan-400">100%</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['cred_hero_stat_ahorro']; ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-cyan-400">24/7</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['cred_hero_stat_acceso']; ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-cyan-400">&lt;3s</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['cred_hero_stat_validacion']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problemas que Resuelve -->
    <section class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_problemas_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_problemas_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <!-- Problema 1: Costos de Impresión -->
                <div class="bg-gray-900 border border-red-500/30 rounded-xl p-6 hover:border-red-500/50 transition-all">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-red-400 mb-2"><?php echo $lang['cred_problema_impresion']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['cred_problema_impresion_desc']; ?></p>
                </div>

                <!-- Problema 2: Carnets Perdidos -->
                <div class="bg-gray-900 border border-red-500/30 rounded-xl p-6 hover:border-red-500/50 transition-all">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-red-400 mb-2"><?php echo $lang['cred_problema_perdidos']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['cred_problema_perdidos_desc']; ?></p>
                </div>

                <!-- Problema 3: Falsificaciones -->
                <div class="bg-gray-900 border border-red-500/30 rounded-xl p-6 hover:border-red-500/50 transition-all">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-red-400 mb-2"><?php echo $lang['cred_problema_falsificaciones']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['cred_problema_falsificaciones_desc']; ?></p>
                </div>

                <!-- Problema 4: Actualización Manual -->
                <div class="bg-gray-900 border border-red-500/30 rounded-xl p-6 hover:border-red-500/50 transition-all">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-red-400 mb-2"><?php echo $lang['cred_problema_actualizacion']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['cred_problema_actualizacion_desc']; ?></p>
                </div>

                <!-- Problema 5: Control de Acceso Lento -->
                <div class="bg-gray-900 border border-red-500/30 rounded-xl p-6 hover:border-red-500/50 transition-all">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-red-400 mb-2"><?php echo $lang['cred_problema_acceso']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['cred_problema_acceso_desc']; ?></p>
                </div>

                <!-- Problema 6: Registros Desorganizados -->
                <div class="bg-gray-900 border border-red-500/30 rounded-xl p-6 hover:border-red-500/50 transition-all">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-red-400 mb-2"><?php echo $lang['cred_problema_archivos']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['cred_problema_archivos_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cómo Funciona -->
    <section id="como-funciona" class="py-20 bg-black border-y border-cyan-600/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_como_funciona_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_como_funciona_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Paso 1 -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-cyan-600/20 border border-cyan-600/30 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                        <span class="text-3xl font-bold text-cyan-400">1</span>
                        <div class="absolute -right-4 top-1/2 transform -translate-y-1/2 hidden md:block">
                            <svg class="w-8 h-8 text-cyan-600/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['cred_como_funciona_paso1_titulo']; ?></h3>
                    <p class="text-gray-400 mb-3"><?php echo $lang['cred_como_funciona_paso1_desc']; ?></p>
                    <p class="text-sm text-cyan-400 italic"><?php echo $lang['cred_como_funciona_paso1_alt']; ?></p>
                </div>

                <!-- Paso 2 -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-cyan-600/20 border border-cyan-600/30 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                        <span class="text-3xl font-bold text-cyan-400">2</span>
                        <div class="absolute -right-4 top-1/2 transform -translate-y-1/2 hidden md:block">
                            <svg class="w-8 h-8 text-cyan-600/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['cred_como_funciona_paso2_titulo']; ?></h3>
                    <p class="text-gray-400 mb-3"><?php echo $lang['cred_como_funciona_paso2_desc']; ?></p>
                    <p class="text-sm text-cyan-400 italic"><?php echo $lang['cred_como_funciona_paso2_alt']; ?></p>
                </div>

                <!-- Paso 3 -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-cyan-600/20 border border-cyan-600/30 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-3xl font-bold text-cyan-400">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['cred_como_funciona_paso3_titulo']; ?></h3>
                    <p class="text-gray-400 mb-3"><?php echo $lang['cred_como_funciona_paso3_desc']; ?></p>
                    <p class="text-sm text-cyan-400 italic"><?php echo $lang['cred_como_funciona_paso3_alt']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tipos de Organizaciones -->
    <section class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_organizaciones_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_organizaciones_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <!-- Clubes -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all group">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-600/30 transition-all">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_org_clubes']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_org_clubes_desc']; ?></p>
                </div>

                <!-- Cooperativas -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all group">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-600/30 transition-all">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_org_cooperativas']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_org_cooperativas_desc']; ?></p>
                </div>

                <!-- Mutuales -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all group">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-600/30 transition-all">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_org_mutuales']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_org_mutuales_desc']; ?></p>
                </div>

                <!-- Colegios Profesionales -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all group">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-600/30 transition-all">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_org_colegios']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_org_colegios_desc']; ?></p>
                </div>

                <!-- Asociaciones -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all group">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-600/30 transition-all">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_org_asociaciones']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_org_asociaciones_desc']; ?></p>
                </div>

                <!-- Eventos -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all group">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-600/30 transition-all">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_org_eventos']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_org_eventos_desc']; ?></p>
                </div>

                <!-- Gimnasios -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all group">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-600/30 transition-all">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_org_gimnasios']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_org_gimnasios_desc']; ?></p>
                </div>

                <!-- Bibliotecas -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all group">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mb-4 group-hover:bg-cyan-600/30 transition-all">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_org_bibliotecas']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_org_bibliotecas_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Funcionalidades -->
    <section id="funcionalidades" class="py-20 bg-black border-y border-cyan-600/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_func_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_func_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- QR -->
                <div class="bg-gray-900/50 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all">
                    <div class="w-12 h-12 bg-cyan-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_func_qr']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_func_qr_desc']; ?></p>
                </div>

                <!-- Actualización -->
                <div class="bg-gray-900/50 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all">
                    <div class="w-12 h-12 bg-cyan-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_func_actualizacion']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_func_actualizacion_desc']; ?></p>
                </div>

                <!-- Branding -->
                <div class="bg-gray-900/50 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all">
                    <div class="w-12 h-12 bg-cyan-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_func_branding']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_func_branding_desc']; ?></p>
                </div>

                <!-- Acceso 24/7 -->
                <div class="bg-gray-900/50 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all">
                    <div class="w-12 h-12 bg-cyan-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_func_acceso']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_func_acceso_desc']; ?></p>
                </div>

                <!-- Compartir -->
                <div class="bg-gray-900/50 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all">
                    <div class="w-12 h-12 bg-cyan-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_func_compartir']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_func_compartir_desc']; ?></p>
                </div>

                <!-- Carga Masiva -->
                <div class="bg-gray-900/50 border border-cyan-600/20 rounded-xl p-6 hover:border-cyan-600/40 transition-all">
                    <div class="w-12 h-12 bg-cyan-600/20 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_func_masivo']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_func_masivo_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Beneficios -->
    <section id="beneficios" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_beneficios_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_beneficios_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <!-- Ahorro -->
                <div class="bg-gradient-to-br from-cyan-600/10 to-cyan-700/5 border border-cyan-600/30 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-cyan-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_beneficio_ahorro_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_beneficio_ahorro_desc']; ?></p>
                </div>

                <!-- Tiempo -->
                <div class="bg-gradient-to-br from-cyan-600/10 to-cyan-700/5 border border-cyan-600/30 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-cyan-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_beneficio_tiempo_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_beneficio_tiempo_desc']; ?></p>
                </div>

                <!-- Seguridad -->
                <div class="bg-gradient-to-br from-cyan-600/10 to-cyan-700/5 border border-cyan-600/30 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-cyan-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_beneficio_seguridad_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_beneficio_seguridad_desc']; ?></p>
                </div>

                <!-- Imagen -->
                <div class="bg-gradient-to-br from-cyan-600/10 to-cyan-700/5 border border-cyan-600/30 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-cyan-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['cred_beneficio_imagen_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_beneficio_imagen_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Panel de Administración -->
    <section class="py-16 bg-black">
        <div class="container mx-auto px-6">
            <div class="bg-gradient-to-br from-gray-900 to-gray-950 border border-cyan-600/30 rounded-2xl p-8 md:p-12 max-w-5xl mx-auto">
                <div class="flex flex-col md:flex-row items-start gap-8">
                    <div class="w-20 h-20 bg-gradient-to-br from-cyan-600 to-cyan-700 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl md:text-3xl font-bold text-cyan-400 mb-2"><?php echo $lang['cred_panel_titulo']; ?></h2>
                        <p class="text-gray-400 mb-6"><?php echo $lang['cred_panel_subtitulo']; ?></p>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-300"><?php echo $lang['cred_panel_carga_masiva']; ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-300"><?php echo $lang['cred_panel_crud']; ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-300"><?php echo $lang['cred_panel_dashboard']; ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-300"><?php echo $lang['cred_panel_multiusuario']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Antes vs Después -->
    <section class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_antes_despues_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_antes_despues_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Antes -->
                <div class="bg-gray-900 border border-red-500/30 rounded-2xl p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-red-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-red-400"><?php echo $lang['cred_antes_titulo']; ?></h3>
                    </div>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="text-gray-400"><?php echo $lang['cred_antes_impresion']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="text-gray-400"><?php echo $lang['cred_antes_perdidos']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="text-gray-400"><?php echo $lang['cred_antes_falsificacion']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="text-gray-400"><?php echo $lang['cred_antes_actualizacion']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="text-gray-400"><?php echo $lang['cred_antes_control']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="text-gray-400"><?php echo $lang['cred_antes_planillas']; ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Después -->
                <div class="bg-gray-900 border border-cyan-500/30 rounded-2xl p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-cyan-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-cyan-400"><?php echo $lang['cred_despues_titulo']; ?></h3>
                    </div>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-300"><span class="text-cyan-400 font-semibold"><?php echo $lang['cred_despues_ahorro']; ?></span> <?php echo $lang['cred_despues_ahorro_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-300"><span class="text-cyan-400 font-semibold"><?php echo $lang['cred_despues_digital']; ?></span> <?php echo $lang['cred_despues_digital_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-300"><?php echo $lang['cred_despues_validacion']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-300"><span class="text-cyan-400 font-semibold"><?php echo $lang['cred_despues_acceso']; ?></span> <?php echo $lang['cred_despues_acceso_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-300"><span class="text-cyan-400 font-semibold"><?php echo $lang['cred_despues_centralizado']; ?></span> <?php echo $lang['cred_despues_centralizado_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-300"><?php echo $lang['cred_despues_control']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Integraciones -->
    <section class="py-20 bg-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_integraciones_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_integraciones_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-4 gap-6 max-w-5xl mx-auto">
                <!-- Excel -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 text-center hover:border-cyan-600/50 transition-colors">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-cyan-400 mb-2"><?php echo $lang['cred_integracion_excel']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_integracion_excel_desc']; ?></p>
                </div>

                <!-- API -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 text-center hover:border-cyan-600/50 transition-colors">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-cyan-400 mb-2"><?php echo $lang['cred_integracion_api']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_integracion_api_desc']; ?></p>
                </div>

                <!-- Email -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 text-center hover:border-cyan-600/50 transition-colors">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-cyan-400 mb-2"><?php echo $lang['cred_integracion_email']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_integracion_email_desc']; ?></p>
                </div>

                <!-- Subdominios -->
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 text-center hover:border-cyan-600/50 transition-colors">
                    <div class="w-14 h-14 bg-cyan-600/20 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-cyan-400 mb-2"><?php echo $lang['cred_integracion_subdominio']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['cred_integracion_subdominio_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Casos de Éxito -->
    <section id="casos-exito" class="py-20 bg-gray-950 border-y border-cyan-600/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_casos_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_casos_subtitulo']; ?></p>
            </div>

            <div class="max-w-5xl mx-auto grid md:grid-cols-2 gap-8">
                <!-- SAJuR -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-950 border border-cyan-600/30 rounded-2xl p-8">
                    <div class="flex flex-col md:flex-row items-center gap-6 mb-6">
                        <div class="w-16 h-16 bg-cyan-600/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white"><?php echo $lang['cred_caso_sajur_nombre']; ?></h3>
                            <p class="text-cyan-400 text-sm"><?php echo $lang['cred_caso_sajur_tipo']; ?></p>
                        </div>
                    </div>
                    <blockquote class="text-gray-300 italic leading-relaxed">
                        <?php echo $lang['cred_caso_sajur_testimonio']; ?>
                    </blockquote>
                </div>

                <!-- Liberté -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-950 border border-cyan-600/30 rounded-2xl p-8">
                    <div class="flex flex-col md:flex-row items-center gap-6 mb-6">
                        <div class="w-16 h-16 bg-cyan-600/20 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white"><?php echo $lang['cred_caso_liberte_nombre']; ?></h3>
                            <p class="text-cyan-400 text-sm"><?php echo $lang['cred_caso_liberte_tipo']; ?></p>
                        </div>
                    </div>
                    <blockquote class="text-gray-300 italic leading-relaxed">
                        <?php echo $lang['cred_caso_liberte_testimonio']; ?>
                    </blockquote>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_faq_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_faq_subtitulo']; ?></p>
            </div>

            <div class="max-w-3xl mx-auto space-y-4">
                <?php
                $faqs = [
                    ['cred_faq_acceso_titulo', 'cred_faq_acceso_desc'],
                    ['cred_faq_imprimir_titulo', 'cred_faq_imprimir_desc'],
                    ['cred_faq_vencimiento_titulo', 'cred_faq_vencimiento_desc'],
                    ['cred_faq_actualizar_titulo', 'cred_faq_actualizar_desc'],
                    ['cred_faq_validar_titulo', 'cred_faq_validar_desc'],
                    ['cred_faq_existentes_titulo', 'cred_faq_existentes_desc'],
                ];
                foreach ($faqs as $faq): ?>
                <details class="group bg-gray-900 border border-cyan-600/20 rounded-xl overflow-hidden">
                    <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-gray-800/50 transition-colors">
                        <h3 class="text-lg font-semibold text-white pr-4"><?php echo $lang[$faq[0]]; ?></h3>
                        <svg class="w-5 h-5 text-cyan-400 transform group-open:rotate-180 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="px-6 pb-6 text-gray-400">
                        <?php echo $lang[$faq[1]]; ?>
                    </div>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Planes y Precios -->
    <?php
    // Calcular precios con descuento
    $hay_promo = is_credencialis_promo_active();
    ?>
    <section id="planes" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-5xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_planes_titulo']; ?></h2>
                <p class="text-lg text-gray-400 mb-3"><?php echo $lang['cred_planes_subtitulo']; ?></p>
                <?php if ($hay_promo): ?>
                <?php $alta_prices = display_alta_with_savings($CREDENCIALIS_ALTA_USD, $current_language, $CREDENCIALIS_DISCOUNT); ?>
                <div class="inline-block mt-4 px-8 py-4 bg-gray-900 border-2 border-red-600 rounded-lg shadow-lg">
                    <p class="text-red-500 font-bold text-lg mb-3 flex items-center justify-center gap-2">
                        <span class="text-2xl">🔥</span>
                        <?php echo $lang['cred_promo_titulo'] ?? 'PROMO LANZAMIENTO'; ?>
                    </p>
                    <div class="text-center">
                        <p class="text-gray-500 text-sm mb-1">
                            <?php echo $lang['cred_promo_alta'] ?? 'Alta:'; ?> <span class="line-through"><?php echo $alta_prices['original']; ?></span>
                        </p>
                        <p class="text-white text-lg font-bold mb-1">
                            <?php echo $lang['cred_promo_alta_bonificada'] ?? 'Alta Bonificada:'; ?> <span class="text-cyan-400"><?php echo $alta_prices['discounted']; ?></span>
                        </p>
                        <p class="text-green-400 text-sm">(<?php echo $lang['price_you_save'] ?? 'Ahorrás'; ?> <?php echo $alta_prices['savings']; ?>)</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Banner de descuento para planes -->
            <?php if ($hay_promo): ?>
            <div class="text-center mb-8">
                <p class="text-red-500 font-bold text-2xl flex items-center justify-center gap-2">
                    <span>🔥</span>
                    <?php echo $CREDENCIALIS_DISCOUNT; ?>% <?php echo $lang['cred_descuento_banner'] ?? 'DE DESCUENTO en planes - Solo por tiempo limitado'; ?>
                    <span>🔥</span>
                </p>
            </div>
            <?php endif; ?>

            <!-- Plan Singularis (Pago por Credencial) - Separado arriba -->
            <div class="max-w-md mx-auto mb-8">
                <div class="bg-gray-900 border border-amber-500/30 rounded-xl p-6">
                    <div class="text-center">
                        <span class="inline-block px-3 py-1 bg-amber-500/20 text-amber-400 text-xs font-semibold rounded-full mb-3"><?php echo $lang['cred_plan_sin_suscripcion'] ?? 'SIN SUSCRIPCIÓN'; ?></span>
                        <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['cred_plan_singularis_titulo']; ?></h3>
                        <p class="text-gray-400 text-xs mb-4"><?php echo $lang['cred_plan_singularis_desc']; ?></p>
                        <div class="mb-4">
                            <?php
                            $singularis_price = get_localized_price($CREDENCIALIS_PRICING['singularis'], $current_language);
                            ?>
                            <span class="text-amber-400 text-3xl font-bold"><?php echo $singularis_price['local_formatted']; ?></span>
                            <span class="text-gray-400 text-sm ml-1"><?php echo $lang['cred_plan_singularis_precio_label']; ?></span>
                        </div>
                        <ul class="space-y-2 mb-6 text-gray-300 text-xs text-left max-w-xs mx-auto">
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['cred_plan_singularis_feat1']; ?></span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['cred_plan_singularis_feat2']; ?></span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['cred_plan_singularis_feat3']; ?></span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['cred_plan_singularis_feat4']; ?></span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['cred_plan_singularis_feat5']; ?></span>
                            </li>
                        </ul>
                        <a href="#contacto" class="w-full inline-block text-center px-4 py-2.5 bg-amber-500/20 border border-amber-500/30 text-amber-400 font-semibold rounded-lg hover:bg-amber-500/30 transition-colors text-sm">
                            <?php echo $lang['cred_plan_singularis_cta']; ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Planes con Suscripción -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <!-- Plan Essentialis -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['cred_plan_essentialis_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['cred_plan_essentialis_desc']; ?></p>
                    <div class="mb-4">
                        <?php echo display_price_with_savings_cyan($CREDENCIALIS_PRICING['essentialis'], $current_language, $CREDENCIALIS_DISCOUNT, $lang['price_you_save'] ?? 'Ahorrás'); ?>
                        <p class="text-xs text-gray-400 mt-2"><?php echo $lang['cred_plan_pago_mensual'] ?? 'Pago mensual'; ?></p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_essentialis_feat1']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_essentialis_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_essentialis_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_essentialis_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_essentialis_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_essentialis_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_essentialis_feat7']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_essentialis_feat8']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-cyan-600/30 text-cyan-400 font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <?php echo $lang['cred_plan_essentialis_cta']; ?>
                    </a>
                </div>

                <!-- Plan Premium (Destacado) -->
                <div class="bg-gray-900 border-2 border-cyan-500 rounded-xl p-6 flex flex-col relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-gradient-to-r from-cyan-500 to-cyan-600 text-white text-xs font-bold rounded-full uppercase">
                        <?php echo $lang['cred_plan_premium_badge']; ?>
                    </div>
                    <h3 class="text-xl font-bold text-cyan-400 mb-2 mt-2"><?php echo $lang['cred_plan_premium_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['cred_plan_premium_desc']; ?></p>
                    <div class="mb-4">
                        <?php echo display_price_with_savings_cyan($CREDENCIALIS_PRICING['premium'], $current_language, $CREDENCIALIS_DISCOUNT, $lang['price_you_save'] ?? 'Ahorrás'); ?>
                        <p class="text-xs text-gray-400 mt-2"><?php echo $lang['cred_plan_pago_mensual'] ?? 'Pago mensual'; ?></p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><strong><?php echo $lang['cred_plan_premium_feat1_strong']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_premium_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_premium_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_premium_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_premium_feat5']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 credencialis-btn text-white font-bold rounded-lg transition-all text-sm">
                        <?php echo $lang['cred_plan_premium_cta']; ?>
                    </a>
                </div>

                <!-- Plan Excellens -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['cred_plan_excellens_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['cred_plan_excellens_desc']; ?></p>
                    <div class="mb-4">
                        <?php echo display_price_with_savings_cyan($CREDENCIALIS_PRICING['excellens'], $current_language, $CREDENCIALIS_DISCOUNT, $lang['price_you_save'] ?? 'Ahorrás'); ?>
                        <p class="text-xs text-gray-400 mt-2"><?php echo $lang['cred_plan_pago_mensual'] ?? 'Pago mensual'; ?></p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><strong><?php echo $lang['cred_plan_excellens_feat1_strong']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_excellens_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_excellens_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_excellens_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_excellens_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_excellens_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_excellens_feat7']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-cyan-600/30 text-cyan-400 font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <?php echo $lang['cred_plan_excellens_cta']; ?>
                    </a>
                </div>

                <!-- Plan Supremus -->
                <div class="bg-gray-900 border border-cyan-600/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['cred_plan_supremus_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['cred_plan_supremus_desc']; ?></p>
                    <div class="mb-4">
                        <?php echo display_price_with_savings_cyan($CREDENCIALIS_PRICING['supremus'], $current_language, $CREDENCIALIS_DISCOUNT, $lang['price_you_save'] ?? 'Ahorrás'); ?>
                        <p class="text-xs text-gray-400 mt-2"><?php echo $lang['cred_plan_pago_mensual'] ?? 'Pago mensual'; ?></p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><strong><?php echo $lang['cred_plan_supremus_feat1_strong']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_supremus_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_supremus_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_supremus_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_supremus_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_supremus_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['cred_plan_supremus_feat7']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-cyan-600/30 text-cyan-400 font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <?php echo $lang['cred_plan_supremus_cta']; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4"><?php echo $lang['cred_cta_final_titulo']; ?></h2>
            <p class="text-lg text-gray-400 max-w-3xl mx-auto mb-4"><?php echo $lang['cred_cta_final_desc']; ?></p>
            <p class="text-cyan-400 max-w-2xl mx-auto mb-8"><?php echo $lang['cred_cta_final_equipo']; ?></p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                <a href="#contacto" class="credencialis-btn px-8 py-4 text-white font-bold rounded-lg hover:opacity-90 transition-all">
                    <?php echo $lang['cred_cta_final_demo']; ?>
                </a>
                <a href="#contacto" class="border border-cyan-500 px-8 py-4 text-cyan-400 font-bold rounded-lg hover:bg-cyan-600/10 transition-all">
                    <?php echo $lang['cred_cta_final_contactar']; ?>
                </a>
            </div>
            <div class="flex flex-wrap justify-center gap-12 text-center">
                <div>
                    <p class="text-4xl font-bold text-cyan-400"><?php echo $lang['cred_cta_final_implementacion']; ?></p>
                    <p class="text-gray-500 text-sm"><?php echo $lang['cred_cta_final_implementacion_desc']; ?></p>
                </div>
                <div>
                    <p class="text-4xl font-bold text-cyan-400"><?php echo $lang['cred_cta_final_ahorro']; ?></p>
                    <p class="text-gray-500 text-sm"><?php echo $lang['cred_cta_final_ahorro_desc']; ?></p>
                </div>
                <div>
                    <p class="text-4xl font-bold text-cyan-400"><?php echo $lang['cred_cta_final_socios']; ?></p>
                    <p class="text-gray-500 text-sm"><?php echo $lang['cred_cta_final_socios_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contacto -->
    <section id="contacto" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="max-w-2xl mx-auto text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-cyan-400 mb-4"><?php echo $lang['cred_contacto_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['cred_contacto_subtitulo']; ?></p>
            </div>

            <div class="max-w-xl mx-auto">
                <form id="contactForm" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2"><?php echo $lang['cred_contacto_nombre']; ?></label>
                        <input type="text" name="nombre" required class="w-full px-4 py-3 bg-gray-900 border border-cyan-600/20 rounded-lg text-white focus:outline-none focus:border-cyan-500 transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2"><?php echo $lang['cred_contacto_email']; ?></label>
                        <input type="email" name="email" required class="w-full px-4 py-3 bg-gray-900 border border-cyan-600/20 rounded-lg text-white focus:outline-none focus:border-cyan-500 transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2"><?php echo $lang['cred_contacto_organizacion']; ?></label>
                        <input type="text" name="organizacion" required class="w-full px-4 py-3 bg-gray-900 border border-cyan-600/20 rounded-lg text-white focus:outline-none focus:border-cyan-500 transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2"><?php echo $lang['cred_contacto_tipo']; ?></label>
                        <select name="tipo" required class="w-full px-4 py-3 bg-gray-900 border border-cyan-600/20 rounded-lg text-white focus:outline-none focus:border-cyan-500 transition-colors">
                            <option value=""><?php echo $lang['cred_contacto_tipo']; ?>...</option>
                            <option value="club"><?php echo $lang['cred_contacto_tipo_club']; ?></option>
                            <option value="cooperativa"><?php echo $lang['cred_contacto_tipo_cooperativa']; ?></option>
                            <option value="mutual"><?php echo $lang['cred_contacto_tipo_mutual']; ?></option>
                            <option value="asociacion"><?php echo $lang['cred_contacto_tipo_asociacion']; ?></option>
                            <option value="colegio"><?php echo $lang['cred_contacto_tipo_colegio']; ?></option>
                            <option value="evento"><?php echo $lang['cred_contacto_tipo_evento']; ?></option>
                            <option value="otro"><?php echo $lang['cred_contacto_tipo_otro']; ?></option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2"><?php echo $lang['cred_contacto_socios']; ?></label>
                        <select name="socios" class="w-full px-4 py-3 bg-gray-900 border border-cyan-600/20 rounded-lg text-white focus:outline-none focus:border-cyan-500 transition-colors">
                            <option value="1-50">1 - 50</option>
                            <option value="51-200">51 - 200</option>
                            <option value="201-500">201 - 500</option>
                            <option value="501-1000">501 - 1.000</option>
                            <option value="1001+">1.001+</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2"><?php echo $lang['cred_contacto_mensaje']; ?></label>
                        <textarea name="mensaje" rows="4" class="w-full px-4 py-3 bg-gray-900 border border-cyan-600/20 rounded-lg text-white focus:outline-none focus:border-cyan-500 transition-colors resize-none"></textarea>
                    </div>

                    <input type="hidden" name="producto" value="credencialis">
                    <input type="hidden" name="lang" value="<?php echo $current_language; ?>">

                    <button type="submit" class="w-full py-4 credencialis-btn text-white font-bold rounded-lg hover:opacity-90 transition-all text-lg">
                        <?php echo $lang['cred_contacto_enviar']; ?>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <!-- Scripts -->
    <script>
        // Ocultar loader
        window.addEventListener('load', function() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => loader.style.display = 'none', 300);
            }
        });

        // Toggle menú idioma
        document.getElementById('langToggle')?.addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('langMenu').classList.toggle('hidden');
        });

        // Cerrar menú idioma al hacer clic fuera
        document.addEventListener('click', function() {
            document.getElementById('langMenu')?.classList.add('hidden');
        });

        // Toggle menú móvil
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });

        // Smooth scroll para enlaces de navegación
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    document.getElementById('mobileMenu')?.classList.add('hidden');
                }
            });
        });

        // Formulario de contacto
        document.getElementById('contactForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.textContent = '<?php echo $lang['cred_contacto_enviando']; ?>';
            btn.disabled = true;

            setTimeout(() => {
                alert('Gracias por contactarnos. Nos comunicaremos pronto.');
                btn.textContent = originalText;
                btn.disabled = false;
                this.reset();
            }, 1500);
        });
    </script>

<?php
$page_content = ob_get_clean();
echo $page_content;

// Guardar en caché si corresponde
if (!$skip_cache) {
    save_page_cache($cache_key, $page_content);
}
?>
</body>
</html>
