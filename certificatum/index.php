<?php
/**
 * Verumax - Página de Solución Académica
 * Versión multi-idioma (MODULAR)
 */
require_once '../config.php';

// Definir módulos de idioma a cargar (modo modular)
$lang_modules = ['common', 'land_certificatum'];
require_once '../lang_config.php';

// Sobreescribir idiomas disponibles con nombres regionales
$available_languages = [
    'es_AR' => 'Español (Argentina)',
    'es_BO' => 'Español (Bolivia)',
    'es_CL' => 'Español (Chile)',
    'es_EC' => 'Español (Ecuador)',
    'es_ES' => 'Español (España)',
    'es_PY' => 'Español (Paraguay)',
    'es_UY' => 'Español (Uruguay)',
    'pt_BR' => 'Português (Brasil)',
    'pt_PT' => 'Português (Portugal)',
    'en_US' => 'English (US)',
    'ca_ES' => 'Català',
    'eu_ES' => 'Euskara',
    'el_GR' => 'Ελληνικά'
];

require_once '../includes/currency_converter.php';
require_once '../includes/pricing_config.php';
require_once '../includes/cache_helper.php';

// =====================================
// SISTEMA DE CACHÉ
// =====================================
// No usar caché en modo debug o con nocache=1
$skip_cache = $lang_debug_mode || (isset($_GET['nocache']) && $_GET['nocache'] === '1');
$cache_key = 'certificatum_' . $current_language;

if (!$skip_cache) {
    $cached_page = get_cached_page($cache_key, 3600); // 1 hora de caché
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
    <title><?php echo $lang['acad_meta_title']; ?></title>
    <meta name="description" content="<?php echo $lang['acad_meta_description']; ?>">
    <meta name="keywords" content="<?php echo $lang['acad_meta_keywords']; ?>">
    <meta name="author" content="<?php echo $lang['meta_author']; ?>">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">

    <!-- Canonical URL (evita contenido duplicado con parámetros de idioma) -->
    <link rel="canonical" href="https://<?php echo $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?'); ?>">

    <!-- Metadatos adicionales para SEO -->
    <meta name="language" content="<?php echo substr($current_language, 0, 2); ?>">
    <meta name="revisit-after" content="7 days">
    <meta name="rating" content="general">
    <meta name="distribution" content="global">

    <!-- Hreflang para versiones de idioma (SEO internacional) -->
    <link rel="alternate" hreflang="es-ar" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=es_AR">
    <link rel="alternate" hreflang="es-bo" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=es_BO">
    <link rel="alternate" hreflang="es-cl" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=es_CL">
    <link rel="alternate" hreflang="es-ec" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=es_EC">
    <link rel="alternate" hreflang="es-es" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=es_ES">
    <link rel="alternate" hreflang="es-py" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=es_PY">
    <link rel="alternate" hreflang="es-uy" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=es_UY">
    <link rel="alternate" hreflang="pt-br" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=pt_BR">
    <link rel="alternate" hreflang="pt-pt" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=pt_PT">
    <link rel="alternate" hreflang="en-us" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=en_US">
    <link rel="alternate" hreflang="ca" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=ca_ES">
    <link rel="alternate" hreflang="eu" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=eu_ES">
    <link rel="alternate" hreflang="el" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?lang=el_GR">
    <link rel="alternate" hreflang="x-default" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/">

    <!-- Geo Tags para Argentina -->
    <meta name="geo.region" content="AR">
    <meta name="geo.placename" content="Argentina">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo $lang['acad_meta_og_title']; ?>">
    <meta property="og:description" content="<?php echo $lang['acad_meta_og_description']; ?>">
    <meta property="og:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/og-image-certificatum.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Certificatum - Certificados Digitales Profesionales con Validación QR">
    <meta property="og:locale" content="<?php echo str_replace('_', '_', $current_language); ?>">
    <meta property="og:site_name" content="Verumax Certificatum">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta name="twitter:title" content="<?php echo $lang['acad_meta_twitter_title']; ?>">
    <meta name="twitter:description" content="<?php echo $lang['acad_meta_twitter_description']; ?>">
    <meta name="twitter:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/og-image-certificatum.png">
    <meta name="twitter:image:alt" content="Certificatum - Certificados Digitales Profesionales">

    <!-- Favicon - Escudo Verumax -->
    <link rel="icon" type="image/png" sizes="32x32" href="https://verumax.com/assets/images/logo-verumax-escudo.png">
    <link rel="apple-touch-icon" sizes="180x180" href="https://verumax.com/assets/images/logo-verumax-escudo.png">

    <!-- Preconnect para recursos externos (mejora rendimiento) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <!-- Preload recursos críticos (mejora LCP - Largest Contentful Paint) -->
    <link rel="preload" href="https://verumax.com/assets/css/tailwind.min.css" as="style">
    <link rel="preload" href="<?php echo CSS_PATH; ?>styles.css" as="style">
    <link rel="preload" href="https://verumax.com/assets/images/logo-verumax-escudo.png" as="image">

    <!-- Google Fonts - Inter (carga no bloqueante) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>

    <!-- Flag Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" media="print" onload="this.media='all'">

    <!-- Tailwind CSS Compilado (producción) -->
    <link rel="stylesheet" href="https://verumax.com/assets/css/tailwind.min.css">

    <!-- Estilos Compartidos -->
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>styles.css">

    <!-- Schema.org Structured Data - EducationalOrganization + FAQPage + Product -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "SoftwareApplication",
                "name": "Certificatum",
                "applicationCategory": "BusinessApplication",
                "operatingSystem": "Web",
                "offers": {
                    "@type": "AggregateOffer",
                    "priceCurrency": "ARS",
                    "lowPrice": "0",
                    "highPrice": "999999",
                    "offerCount": "4"
                },
                "aggregateRating": {
                    "@type": "AggregateRating",
                    "ratingValue": "4.8",
                    "ratingCount": "127",
                    "bestRating": "5",
                    "worstRating": "1"
                },
                "description": "<?php echo htmlspecialchars($lang['acad_meta_description']); ?>",
                "applicationSubCategory": "Gestión de Certificados Académicos",
                "featureList": [
                    "Generación automática de certificados",
                    "Portal personalizado para estudiantes",
                    "Validación con código QR",
                    "Emisión masiva de documentos",
                    "Notificaciones automáticas por email",
                    "Personalización de identidad visual institucional"
                ]
            },
            {
                "@type": "Organization",
                "name": "Verumax",
                "url": "https://<?php echo $_SERVER['HTTP_HOST']; ?>",
                "logo": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/assets/images/logo-verumax-escudo.png",
                "contactPoint": {
                    "@type": "ContactPoint",
                    "contactType": "Ventas",
                    "areaServed": "AR",
                    "availableLanguage": ["es", "pt", "en"]
                }
            },
            {
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "<?php echo htmlspecialchars($lang['acad_faq_cargo_titulo']); ?>",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "<?php echo htmlspecialchars($lang['acad_faq_cargo_desc']); ?>"
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "<?php echo htmlspecialchars($lang['acad_faq_validez_titulo']); ?>",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "<?php echo htmlspecialchars($lang['acad_faq_validez_desc']); ?>"
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "<?php echo htmlspecialchars($lang['acad_faq_diseno_titulo']); ?>",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "<?php echo htmlspecialchars($lang['acad_faq_diseno_desc']); ?>"
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "<?php echo htmlspecialchars($lang['acad_faq_validacion_titulo']); ?>",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "<?php echo htmlspecialchars($lang['acad_faq_validacion_desc']); ?>"
                        }
                    }
                ]
            },
            {
                "@type": "Service",
                "serviceType": "Gestión de Certificados Académicos Digitales",
                "provider": {
                    "@type": "Organization",
                    "name": "Verumax"
                },
                "areaServed": {
                    "@type": "Country",
                    "name": "Argentina"
                },
                "audience": {
                    "@type": "EducationalAudience",
                    "educationalRole": "Instituciones Educativas"
                }
            },
            {
                "@type": "BreadcrumbList",
                "itemListElement": [
                    {
                        "@type": "ListItem",
                        "position": 1,
                        "name": "Inicio",
                        "item": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/"
                    },
                    {
                        "@type": "ListItem",
                        "position": 2,
                        "name": "Certificatum",
                        "item": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/"
                    }
                ]
            },
            {
                "@type": "WebPage",
                "name": "Certificatum - Sistema de Certificados Digitales",
                "description": "<?php echo htmlspecialchars($lang['acad_meta_description']); ?>",
                "url": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/",
                "inLanguage": "<?php echo str_replace('_', '-', $current_language); ?>",
                "isPartOf": {
                    "@type": "WebSite",
                    "name": "Verumax",
                    "url": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/"
                },
                "about": {
                    "@type": "Thing",
                    "name": "Certificados Académicos Digitales",
                    "description": "Plataforma para la emisión, gestión y validación de certificados académicos digitales"
                },
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/certificatum/?q={search_term_string}",
                    "query-input": "required name=search_term_string"
                }
            }
        ]
    }
    </script>
</head>
<body class="bg-black text-white">

    <!-- Banner de debug de idioma (solo visible con ?lang_debug=1) -->
    <?php echo get_lang_debug_banner(); ?>

    <!-- Loader de carga -->
    <div id="page-loader" class="fixed inset-0 bg-black z-[9999] flex items-center justify-center transition-opacity duration-300">
        <div class="text-center">
            <!-- Logo/Icono -->
            <div class="relative mb-6">
                <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-metallic-green-dark via-metallic-green to-metallic-green-light opacity-20 animate-ping"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-12 h-12 text-metallic-green-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <!-- Texto -->
            <p class="text-metallic-green-light text-sm font-medium tracking-wider">CERTIFICATUM</p>
            <!-- Barra de progreso -->
            <div class="mt-4 w-48 h-1 bg-gray-800 rounded-full overflow-hidden mx-auto">
                <div class="h-full bg-gradient-to-r from-metallic-green-dark via-metallic-green-light to-metallic-green-dark rounded-full animate-loading-bar"></div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-black border-b border-metallic-green/20 sticky top-0 z-50 backdrop-blur-sm bg-black/90" role="navigation" aria-label="Navegación principal">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="https://verumax.com/assets/images/logo-verumax-escudo.png" alt="Logotipo escudo de Verumax - Sistema de certificados digitales" class="h-10 w-10" width="40" height="40" fetchpriority="high">
                    <div>
                        <a href="/?lang=<?php echo $current_language; ?>" class="flex items-center" aria-label="Certificatum">
                            <img src="https://verumax.com/assets/images/logo-verumax-texto.png" alt="Verumax - Plataforma de gestión educativa" class="h-8" width="120" height="32" fetchpriority="high">
                        </a>
                        <p class="text-xs text-gray-400">Certificatum</p>
                    </div>
                </div>
                <!-- Links de navegación (visibles en md+) -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="https://verumax.com/?lang=<?php echo $current_language; ?>" class="text-gray-300 hover:text-metallic-green-light transition-colors text-sm flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <?php echo $lang['nav_inicio'] ?? 'Inicio'; ?>
                    </a>
                    <a href="#beneficios" class="text-gray-300 hover:text-metallic-green-light transition-colors text-sm"><?php echo $lang['acad_nav_beneficios']; ?></a>
                    <a href="#funcionalidades" class="text-gray-300 hover:text-metallic-green-light transition-colors text-sm"><?php echo $lang['acad_nav_funcionalidades']; ?></a>
                    <a href="#casos-exito" class="text-gray-300 hover:text-metallic-green-light transition-colors text-sm"><?php echo $lang['acad_nav_casos']; ?></a>
                    <a href="#planes" class="text-gray-300 hover:text-metallic-green-light transition-colors text-sm"><?php echo $lang['acad_nav_planes']; ?></a>
                    <a href="#faq" class="text-gray-300 hover:text-metallic-green-light transition-colors text-sm"><?php echo $lang['acad_nav_faq']; ?></a>
                </div>

                <!-- Controles (idioma, demo, hamburguesa) -->
                <div class="flex items-center gap-2 md:gap-3">

                    <!-- Selector de Idioma (siempre visible) -->
                    <div class="relative">
                        <button id="langToggle" class="flex items-center gap-1 px-2 py-2 rounded-lg hover:bg-gray-800 transition-colors" aria-label="Seleccionar idioma" aria-haspopup="true" aria-expanded="false">
                            <?php echo get_flag_emoji($current_language); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="langMenu" class="hidden absolute right-0 mt-2 w-48 bg-gray-900 border border-metallic-green/20 rounded-lg shadow-lg overflow-hidden z-50" role="menu" aria-labelledby="langToggle">
                            <?php foreach ($available_languages as $code => $name): ?>
                                <a href="?lang=<?php echo $code; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 transition-colors <?php echo $current_language === $code ? 'bg-gray-800' : ''; ?>" role="menuitem" aria-label="Cambiar idioma a <?php echo $name; ?>">
                                    <?php echo get_flag_emoji($code); ?>
                                    <span class="text-sm"><?php echo $name; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Toggle Tema (visible en tablet+) -->
                    <button id="themeToggle" class="hidden md:flex px-3 py-2 border border-gray-700 rounded-lg hover:border-metallic-green/50 hover:bg-gray-800 transition-colors" title="<?php echo $lang['nav_tema_claro']; ?>" aria-label="Cambiar tema de color">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden dark-mode-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 light-mode-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Botón Demo (visible en tablet+) -->
                    <a href="#contacto" class="hidden md:flex px-4 py-2 green-metallic-btn metallic-shine text-white font-semibold rounded-lg hover:opacity-90 transition-all text-sm"><?php echo $lang['acad_nav_solicitar_demo']; ?></a>

                    <!-- Botón Hamburger (visible solo en móvil, oculto en md+) -->
                    <button id="mobileMenuBtn" class="md:hidden flex flex-col justify-center items-center rounded-lg hover:bg-gray-800 transition-colors" style="width: 44px; height: 44px;" aria-label="Abrir menú" aria-expanded="false">
                        <span class="hamburger-line rounded transition-all duration-300" style="width: 24px; height: 3px; background: white;"></span>
                        <span class="hamburger-line rounded transition-all duration-300" style="width: 24px; height: 3px; background: white; margin-top: 5px;"></span>
                        <span class="hamburger-line rounded transition-all duration-300" style="width: 24px; height: 3px; background: white; margin-top: 5px;"></span>
                    </button>
                </div>
            </div>

            <!-- Menú Móvil (oculto en md+) -->
            <div id="mobileMenu" class="hidden md:hidden mt-4 pb-4 border-t border-metallic-green/20 pt-4">
                <div class="flex flex-col gap-3">
                    <a href="https://verumax.com/?lang=<?php echo $current_language; ?>" class="text-gray-300 hover:text-metallic-green-light transition-colors flex items-center gap-2 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <?php echo $lang['nav_volver_inicio']; ?>
                    </a>
                    <a href="#beneficios" class="text-gray-300 hover:text-metallic-green-light transition-colors py-2"><?php echo $lang['acad_nav_beneficios']; ?></a>
                    <a href="#funcionalidades" class="text-gray-300 hover:text-metallic-green-light transition-colors py-2"><?php echo $lang['acad_nav_funcionalidades']; ?></a>
                    <a href="#casos-exito" class="text-gray-300 hover:text-metallic-green-light transition-colors py-2"><?php echo $lang['acad_nav_casos']; ?></a>
                    <a href="#planes" class="text-gray-300 hover:text-metallic-green-light transition-colors py-2"><?php echo $lang['acad_nav_planes']; ?></a>
                    <a href="#faq" class="text-gray-300 hover:text-metallic-green-light transition-colors py-2"><?php echo $lang['acad_nav_faq']; ?></a>

                    <!-- Selector de idioma móvil -->
                    <div class="flex items-center gap-3 py-2 border-t border-gray-800 mt-2 pt-4">
                        <?php foreach ($available_languages as $code => $name): ?>
                            <a href="?lang=<?php echo $code; ?>" class="flex items-center gap-2 px-3 py-2 rounded-lg <?php echo $current_language === $code ? 'bg-metallic-green/20 border border-metallic-green/30' : 'bg-gray-800'; ?> transition-colors">
                                <?php echo get_flag_emoji($code); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <a href="#contacto" class="mt-2 px-6 py-3 green-metallic-btn metallic-shine text-white font-semibold rounded-lg text-center"><?php echo $lang['acad_nav_solicitar_demo']; ?></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 hero-bg border-b border-metallic-green/20" role="banner" aria-label="Sección principal de presentación">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-block px-4 py-2 bg-metallic-green/20 border border-metallic-green/30 rounded-full mb-6">
                    <span class="text-metallic-green-light text-sm font-semibold"><?php echo $lang['acad_hero_badge']; ?></span>
                </div>

                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    <?php echo $lang['acad_hero_title']; ?> <span class="green-gradient-text"><?php echo $lang['acad_hero_title_highlight']; ?></span>
                </h1>

                <!-- Propuesta de Valor Corta -->
                <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-2xl mx-auto">
                    <?php echo $lang['acad_hero_propuesta_valor'] ?? 'El sistema hace todo. Usted solo carga los datos una vez.'; ?>
                </p>

                <!-- Beneficios destacados con íconos -->
                <div class="flex flex-wrap justify-center gap-3 mb-8 max-w-4xl mx-auto">
                    <div class="flex items-center gap-2 bg-metallic-green/10 border border-metallic-green/30 px-4 py-2 rounded-full">
                        <svg class="w-5 h-5 text-metallic-green-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="text-gray-200 text-sm"><?php echo $lang['acad_hero_benefit_1'] ?? 'Generación automática'; ?></span>
                    </div>
                    <div class="flex items-center gap-2 bg-metallic-green/10 border border-metallic-green/30 px-4 py-2 rounded-full">
                        <svg class="w-5 h-5 text-metallic-green-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-gray-200 text-sm"><?php echo $lang['acad_hero_benefit_2'] ?? 'Notificación por email'; ?></span>
                    </div>
                    <div class="flex items-center gap-2 bg-metallic-green/10 border border-metallic-green/30 px-4 py-2 rounded-full">
                        <svg class="w-5 h-5 text-metallic-green-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <span class="text-gray-200 text-sm"><?php echo $lang['acad_hero_benefit_3'] ?? 'QR infalsificable'; ?></span>
                    </div>
                    <div class="flex items-center gap-2 bg-metallic-green/10 border border-metallic-green/30 px-4 py-2 rounded-full">
                        <svg class="w-5 h-5 text-metallic-green-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span class="text-gray-200 text-sm"><?php echo $lang['acad_hero_benefit_4'] ?? 'Descarga 24/7'; ?></span>
                    </div>
                </div>

                <p class="text-xl text-gray-300 mb-8 shimmer-text">
                    <?php echo $lang['acad_hero_subtitle']; ?> <strong class="text-metallic-green-light"><?php echo $lang['acad_hero_instituciones']; ?></strong>, <strong class="text-metallic-green-light"><?php echo $lang['acad_hero_centros_formacion']; ?></strong>, <strong class="text-metallic-green-light"><?php echo $lang['acad_hero_academias']; ?></strong> y <strong class="text-metallic-green-light"><?php echo $lang['acad_hero_formadores']; ?></strong>
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="#contacto" class="px-8 py-4 green-metallic-btn metallic-shine text-white font-bold rounded-lg transition-all text-lg">
                        <?php echo $lang['acad_cta_solicitar_demo']; ?>
                    </a>
                    <a href="#como-funciona" class="px-8 py-4 bg-gray-800 border border-metallic-green/30 text-metallic-green-light font-semibold rounded-lg hover:bg-gray-700 transition-colors text-lg">
                        <?php echo $lang['acad_cta_ver_como_funciona']; ?>
                    </a>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="grid grid-cols-3 gap-6 mt-16 max-w-2xl mx-auto">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-metallic-green-light">90%</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_hero_stat_tiempo']; ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-metallic-green-light">24/7</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_hero_stat_portal']; ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-metallic-green-light">0%</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_hero_stat_falsificaciones']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problemas que Resuelve -->
    <section class="py-20 bg-gray-950" aria-labelledby="problemas-titulo">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 id="problemas-titulo" class="text-3xl md:text-4xl font-bold text-metallic-green-light mb-4"><?php echo $lang['acad_problemas_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['acad_problemas_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <div class="bg-gray-900 border border-metallic-red/30 rounded-xl p-6 hover:border-metallic-red/50 transition-all">
                    <div class="w-12 h-12 bg-metallic-red/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-red-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-metallic-red-light mb-2"><?php echo $lang['acad_problema_trabajo_manual']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_problema_trabajo_manual_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-red/30 rounded-xl p-6 hover:border-metallic-red/50 transition-all">
                    <div class="w-12 h-12 bg-metallic-red/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-red-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-metallic-red-light mb-2"><?php echo $lang['acad_problema_perdidos']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_problema_perdidos_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-red/30 rounded-xl p-6 hover:border-metallic-red/50 transition-all">
                    <div class="w-12 h-12 bg-metallic-red/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-red-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-metallic-red-light mb-2"><?php echo $lang['acad_problema_falsificaciones']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_problema_falsificaciones_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-red/30 rounded-xl p-6 hover:border-metallic-red/50 transition-all">
                    <div class="w-12 h-12 bg-metallic-red/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-red-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-metallic-red-light mb-2"><?php echo $lang['acad_problema_trazabilidad']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_problema_trazabilidad_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-red/30 rounded-xl p-6 hover:border-metallic-red/50 transition-all">
                    <div class="w-12 h-12 bg-metallic-red/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-red-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-metallic-red-light mb-2"><?php echo $lang['acad_problema_costos']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_problema_costos_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-red/30 rounded-xl p-6 hover:border-metallic-red/50 transition-all">
                    <div class="w-12 h-12 bg-metallic-red/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-red-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-metallic-red-light mb-2"><?php echo $lang['acad_problema_archivos']; ?></h3>
                    <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_problema_archivos_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cómo Funciona -->
    <section id="como-funciona" class="py-20 bg-black border-y border-metallic-green/20" aria-labelledby="como-funciona-titulo">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 id="como-funciona-titulo" class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_como_funciona_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_como_funciona_subtitulo']; ?></p>
            </div>
            <div class="grid md:grid-cols-3 gap-10 max-w-5xl mx-auto">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto flex items-center justify-center green-gradient text-black rounded-full text-3xl font-bold shadow-lg shadow-metallic-green/30">1</div>
                    <h3 class="mt-6 text-xl font-bold text-metallic-green-light"><?php echo $lang['acad_como_funciona_paso1_titulo']; ?></h3>
                    <p class="mt-2 text-gray-300"><?php echo $lang['acad_como_funciona_paso1_desc']; ?></p>
                    <p class="mt-3 text-sm text-metallic-green-light font-semibold italic"><?php echo $lang['acad_concierge_delegar'] ?? '¿Prefiere delegar? Nuestro equipo se encarga de la configuración por usted.'; ?></p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto flex items-center justify-center bg-metallic-green text-white rounded-full text-3xl font-bold shadow-lg shadow-metallic-green/30 metallic-shine">2</div>
                    <h3 class="mt-6 text-xl font-bold text-metallic-green-light"><?php echo $lang['acad_como_funciona_paso2_titulo']; ?></h3>
                    <p class="mt-2 text-gray-300"><?php echo $lang['acad_como_funciona_paso2_desc']; ?></p>
                    <p class="mt-3 text-sm text-metallic-green-light font-semibold italic"><?php echo $lang['acad_como_funciona_paso2_alt'] ?? 'O simplemente envíenos sus listados y nuestro equipo los importa por usted.'; ?></p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto flex items-center justify-center green-gradient text-black rounded-full text-3xl font-bold shadow-lg shadow-metallic-green/30">3</div>
                    <h3 class="mt-6 text-xl font-bold text-metallic-green-light"><?php echo $lang['acad_como_funciona_paso3_titulo']; ?></h3>
                    <p class="mt-2 text-gray-300"><?php echo $lang['acad_como_funciona_paso3_desc']; ?></p>
                    <p class="mt-3 text-sm text-metallic-green-light font-semibold italic"><?php echo $lang['acad_como_funciona_paso3_alt'] ?? 'También puede solicitar la emisión a nuestro equipo y solo encargarse de compartir.'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tipos de Documentos Académicos -->
    <section id="documentos" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_documentos_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_documentos_subtitulo']; ?></p>
            </div>

            <!-- Documentos Principales -->
            <div class="mb-12">
                <h3 class="text-2xl font-bold text-metallic-green-light mb-6 text-center"><?php echo $lang['acad_documentos_principales']; ?></h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 green-gradient rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_certificado_aprobacion']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_certificado_aprobacion_desc'] ?? 'Certificado que acredita la aprobación de un curso, materia o programa completo.'; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 green-gradient rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_diplomas']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_diplomas_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 green-gradient rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_analiticos']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_analiticos_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_constancia_regular']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_constancia_regular_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_constancia_inscripcion']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_constancia_inscripcion_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_constancia_finalizacion']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_constancia_finalizacion_desc']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Documentos Complementarios -->
            <div>
                <h3 class="text-2xl font-bold text-metallic-green-light mb-6 text-center"><?php echo $lang['acad_documentos_complementarios']; ?></h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_asistencia']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_asistencia_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_reconocimientos']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_reconocimientos_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_competencias']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_competencias_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_practica']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_practica_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_congresos']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_congresos_desc']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portal del Estudiante -->
    <section id="portal-estudiante" class="py-20 bg-black border-y border-metallic-green/20">
        <div class="container mx-auto px-6">
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="inline-block px-4 py-2 bg-metallic-green/20 border border-metallic-green/30 rounded-full mb-6">
                            <span class="text-metallic-green-light text-sm font-semibold"><?php echo $lang['acad_portal_badge']; ?></span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light mb-6"><?php echo $lang['acad_portal_titulo']; ?></h2>
                        <p class="text-lg text-gray-300 mb-8 leading-relaxed">
                            <?php echo $lang['acad_portal_subtitulo']; ?>
                        </p>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-metallic-green/20 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-white mb-1"><?php echo $lang['acad_portal_acceso_titulo']; ?></h4>
                                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_portal_acceso_desc']; ?></p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-metallic-green/20 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-white mb-1"><?php echo $lang['acad_portal_descarga_titulo']; ?></h4>
                                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_portal_descarga_desc']; ?></p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-metallic-green/20 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-white mb-1"><?php echo $lang['acad_portal_timeline_titulo']; ?></h4>
                                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_portal_timeline_desc']; ?></p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-metallic-green/20 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-white mb-1"><?php echo $lang['acad_portal_qr_titulo']; ?></h4>
                                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_portal_qr_desc']; ?></p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-metallic-green/20 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-white mb-1"><?php echo $lang['acad_portal_linkedin_titulo']; ?></h4>
                                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_portal_linkedin_desc']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 border border-metallic-green/20 rounded-2xl p-8 shadow-2xl">
                        <div class="bg-gray-800 rounded-lg p-6 mb-4">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 green-gradient rounded-full flex items-center justify-center text-black font-bold">
                                    JD
                                </div>
                                <div>
                                    <h4 class="font-bold text-white"><?php echo $lang['acad_portal_demo_nombre']; ?></h4>
                                    <p class="text-sm text-gray-400"><?php echo $lang['acad_portal_demo_dni']; ?></p>
                                </div>
                            </div>
                            <div class="h-2 bg-gray-700 rounded-full mb-2">
                                <div class="h-2 bg-metallic-green rounded-full" style="width: 75%"></div>
                            </div>
                            <p class="text-xs text-gray-400">3 <?php echo $lang['acad_portal_demo_progreso']; ?></p>
                        </div>

                        <div class="space-y-3">
                            <div class="bg-gray-800 rounded-lg p-4 border-l-4 border-metallic-green">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold text-white text-sm"><?php echo $lang['acad_portal_demo_diploma']; ?></p>
                                        <p class="text-xs text-gray-400 mt-1"><?php echo $lang['acad_portal_demo_diploma_fecha']; ?></p>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="bg-gray-800 rounded-lg p-4 border-l-4 border-metallic-green">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold text-white text-sm"><?php echo $lang['acad_portal_demo_certificado']; ?></p>
                                        <p class="text-xs text-gray-400 mt-1"><?php echo $lang['acad_portal_demo_certificado_fecha']; ?></p>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="bg-gray-800 rounded-lg p-4 border-l-4 border-blue-500">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold text-white text-sm"><?php echo $lang['acad_portal_demo_curso_progreso']; ?></p>
                                        <p class="text-xs text-gray-400 mt-1"><?php echo $lang['acad_portal_demo_curso_nombre']; ?></p>
                                    </div>
                                    <div class="text-xs text-blue-400 font-semibold">60%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Casos de Uso e Instituciones -->
    <section id="casos-uso" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_casos_uso_titulo'] ?? '¿Para Quién es Certificatum?'; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_casos_uso_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all text-center">
                    <div class="w-16 h-16 green-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_caso_universidades']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_caso_universidades_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all text-center">
                    <div class="w-16 h-16 bg-metallic-green rounded-full flex items-center justify-center mx-auto mb-4 metallic-shine">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_caso_centros']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_caso_centros_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all text-center">
                    <div class="w-16 h-16 green-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_caso_idiomas']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_caso_idiomas_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all text-center">
                    <div class="w-16 h-16 bg-metallic-green rounded-full flex items-center justify-center mx-auto mb-4 metallic-shine">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_caso_tecnicos']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_caso_tecnicos_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all text-center">
                    <div class="w-16 h-16 green-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_caso_laboral']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_caso_laboral_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all text-center">
                    <div class="w-16 h-16 bg-metallic-green rounded-full flex items-center justify-center mx-auto mb-4 metallic-shine">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_caso_continua']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_caso_continua_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all text-center">
                    <div class="w-16 h-16 green-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_caso_organizadores']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_caso_organizadores_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 hover:border-metallic-green/50 transition-all text-center">
                    <div class="w-16 h-16 bg-metallic-green rounded-full flex items-center justify-center mx-auto mb-4 metallic-shine">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_caso_particulares']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_caso_particulares_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Funcionalidades Específicas -->
    <section id="funcionalidades" class="py-20 bg-black border-y border-metallic-green/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_func_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_func_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <div class="w-12 h-12 bg-metallic-green/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['acad_func_cohortes']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_func_cohortes_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <div class="w-12 h-12 bg-metallic-green/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['acad_func_emision_masiva']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_func_emision_masiva_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <div class="w-12 h-12 bg-metallic-green/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['acad_func_notificaciones']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_func_notificaciones_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <div class="w-12 h-12 bg-metallic-green/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['acad_func_branding']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_func_branding_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <div class="w-12 h-12 bg-metallic-green/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['acad_func_trayectoria']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_func_trayectoria_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <div class="w-12 h-12 bg-metallic-green/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['acad_func_validacion']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_func_validacion_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Beneficios Cuantificables -->
    <section id="beneficios" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_beneficios_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_beneficios_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">
                <div class="text-center">
                    <div class="w-20 h-20 green-gradient rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold text-black">
                        90%
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['acad_beneficio_tiempo_titulo']; ?></h3>
                    <p class="text-gray-400"><?php echo $lang['acad_beneficio_tiempo_desc']; ?></p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-metallic-green rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold text-white metallic-shine">
                        85%
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['acad_beneficio_ahorro_titulo']; ?></h3>
                    <p class="text-gray-400"><?php echo $lang['acad_beneficio_ahorro_desc']; ?></p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 green-gradient rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold text-black">
                        0%
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['acad_beneficio_falsificaciones_titulo']; ?></h3>
                    <p class="text-gray-400"><?php echo $lang['acad_beneficio_falsificaciones_desc']; ?></p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-metallic-green rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold text-white metallic-shine">
                        24/7
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['acad_beneficio_acceso_titulo']; ?></h3>
                    <p class="text-gray-400"><?php echo $lang['acad_beneficio_acceso_desc']; ?></p>
                </div>
            </div>

            <div class="mt-16 max-w-4xl mx-auto bg-gradient-to-r from-metallic-green/10 to-metallic-green/10 border border-metallic-green/20 rounded-2xl p-8">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-metallic-green-light mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-2xl font-bold text-white mb-3"><?php echo $lang['acad_beneficio_trazabilidad_titulo']; ?></h3>
                    <p class="text-gray-300 leading-relaxed"><?php echo $lang['acad_beneficio_trazabilidad_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección ROI - Retorno de Inversión -->
    <section class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_roi_titulo'] ?? 'Retorno de Inversión Comprobado'; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_roi_subtitulo'] ?? 'Resultados reales de instituciones que ya usan Certificatum'; ?></p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
                <div class="text-center p-6 bg-gray-900/50 rounded-xl border border-metallic-green/20">
                    <p class="text-4xl md:text-5xl font-bold text-metallic-green-light"><?php echo $lang['acad_roi_stat_impresion'] ?? '$800'; ?></p>
                    <p class="text-gray-400 mt-2"><?php echo $lang['acad_roi_ahorro_impresion'] ?? 'Ahorro mensual en impresión'; ?></p>
                </div>
                <div class="text-center p-6 bg-gray-900/50 rounded-xl border border-metallic-green/20">
                    <p class="text-4xl md:text-5xl font-bold text-metallic-green-light"><?php echo $lang['acad_roi_stat_tiempo'] ?? '40hs'; ?></p>
                    <p class="text-gray-400 mt-2"><?php echo $lang['acad_roi_ahorro_tiempo'] ?? 'Ahorro en tiempo administrativo'; ?></p>
                </div>
                <div class="text-center p-6 bg-gray-900/50 rounded-xl border border-metallic-green/20">
                    <p class="text-4xl md:text-5xl font-bold text-metallic-green-light"><?php echo $lang['acad_roi_stat_consultas'] ?? '80%'; ?></p>
                    <p class="text-gray-400 mt-2"><?php echo $lang['acad_roi_reduccion_consultas'] ?? 'Reducción de consultas telefónicas'; ?></p>
                </div>
                <div class="text-center p-6 bg-gray-900/50 rounded-xl border border-metallic-green/20">
                    <p class="text-4xl md:text-5xl font-bold text-metallic-green-light"><?php echo $lang['acad_roi_stat_payback'] ?? '2 meses'; ?></p>
                    <p class="text-gray-400 mt-2"><?php echo $lang['acad_roi_payback'] ?? 'Tiempo de Payback'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tabla Comparativa -->
    <section class="py-20 bg-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_vs_titulo'] ?? 'Certificatum vs Alternativas'; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_vs_subtitulo'] ?? 'Compará las opciones del mercado'; ?></p>
            </div>

            <div class="max-w-5xl mx-auto overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-metallic-green/30">
                            <th class="text-left py-4 px-4 text-gray-400 font-semibold"><?php echo $lang['acad_vs_aspecto'] ?? 'Aspecto'; ?></th>
                            <th class="text-center py-4 px-4 text-gray-400 font-semibold"><?php echo $lang['acad_vs_papel'] ?? 'Papel Tradicional'; ?></th>
                            <th class="text-center py-4 px-4 text-gray-400 font-semibold"><?php echo $lang['acad_vs_plataformas'] ?? 'Plataformas Globales'; ?></th>
                            <th class="text-center py-4 px-4 bg-metallic-green/10 rounded-t-lg text-metallic-green-light font-bold"><?php echo $lang['acad_vs_certificatum'] ?? 'Certificatum'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-800">
                            <td class="py-4 px-4 text-gray-300"><?php echo $lang['acad_vs_costo_emision'] ?? 'Costo de emisión'; ?></td>
                            <td class="py-4 px-4 text-center text-red-400"><?php echo $lang['acad_vs_costo_alto'] ?? 'Alto (impresión, firmas)'; ?></td>
                            <td class="py-4 px-4 text-center text-yellow-400"><?php echo $lang['acad_vs_costo_porcentaje'] ?? '% de ventas'; ?></td>
                            <td class="py-4 px-4 text-center bg-metallic-green/10 text-metallic-green-light font-semibold"><?php echo $lang['acad_vs_costo_cero'] ?? 'Cero marginal'; ?></td>
                        </tr>
                        <tr class="border-b border-gray-800">
                            <td class="py-4 px-4 text-gray-300"><?php echo $lang['acad_vs_validacion'] ?? 'Validación'; ?></td>
                            <td class="py-4 px-4 text-center text-red-400"><?php echo $lang['acad_vs_validacion_tel'] ?? 'Llamada telefónica'; ?></td>
                            <td class="py-4 px-4 text-center text-yellow-400"><?php echo $lang['acad_vs_validacion_generica'] ?? 'Genérica'; ?></td>
                            <td class="py-4 px-4 text-center bg-metallic-green/10 text-metallic-green-light font-semibold"><?php echo $lang['acad_vs_validacion_qr'] ?? 'QR Instantáneo 24/7'; ?></td>
                        </tr>
                        <tr class="border-b border-gray-800">
                            <td class="py-4 px-4 text-gray-300"><?php echo $lang['acad_vs_branding'] ?? 'Branding'; ?></td>
                            <td class="py-4 px-4 text-center text-green-400"><?php echo $lang['acad_vs_branding_suyo'] ?? '100% suyo'; ?></td>
                            <td class="py-4 px-4 text-center text-red-400"><?php echo $lang['acad_vs_branding_limitado'] ?? 'Limitado'; ?></td>
                            <td class="py-4 px-4 text-center bg-metallic-green/10 text-metallic-green-light font-semibold"><?php echo $lang['acad_vs_branding_total'] ?? '100% personalizado'; ?></td>
                        </tr>
                        <tr class="border-b border-gray-800">
                            <td class="py-4 px-4 text-gray-300"><?php echo $lang['acad_vs_falsificacion'] ?? 'Falsificación'; ?></td>
                            <td class="py-4 px-4 text-center text-red-400"><?php echo $lang['acad_vs_falsificacion_facil'] ?? 'Fácil'; ?></td>
                            <td class="py-4 px-4 text-center text-yellow-400"><?php echo $lang['acad_vs_falsificacion_posible'] ?? 'Posible'; ?></td>
                            <td class="py-4 px-4 text-center bg-metallic-green/10 text-metallic-green-light font-semibold"><?php echo $lang['acad_vs_falsificacion_imposible'] ?? 'Imposible'; ?></td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 text-gray-300"><?php echo $lang['acad_vs_datos'] ?? 'Datos'; ?></td>
                            <td class="py-4 px-4 text-center text-yellow-400"><?php echo $lang['acad_vs_datos_fisico'] ?? 'Archivo físico'; ?></td>
                            <td class="py-4 px-4 text-center text-red-400"><?php echo $lang['acad_vs_datos_ellos'] ?? 'Son de ellos'; ?></td>
                            <td class="py-4 px-4 text-center bg-metallic-green/10 rounded-b-lg text-metallic-green-light font-semibold"><?php echo $lang['acad_vs_datos_suyos'] ?? 'Son 100% suyos'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Panel de Administración Destacado -->
    <section class="py-20 bg-gradient-to-b from-black to-gray-950">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto">
                <div class="bg-metallic-green/10 border border-metallic-green/30 rounded-2xl p-8 md:p-12">
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 green-gradient rounded-2xl flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h3 class="text-2xl md:text-3xl font-bold text-metallic-green-light mb-4"><?php echo $lang['acad_panel_titulo'] ?? 'Panel de Administración Completo'; ?></h3>
                            <p class="text-gray-400 mb-6"><?php echo $lang['acad_panel_subtitulo'] ?? 'Todo lo que necesitás para gestionar tu institución'; ?></p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 text-left">
                                <li class="flex items-center gap-2 text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <?php echo $lang['acad_panel_carga_masiva'] ?? 'Carga masiva desde Excel/CSV'; ?>
                                </li>
                                <li class="flex items-center gap-2 text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <?php echo $lang['acad_panel_crud'] ?? 'CRUD completo de estudiantes y cursos'; ?>
                                </li>
                                <li class="flex items-center gap-2 text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <?php echo $lang['acad_panel_dashboard'] ?? 'Dashboard con analytics en tiempo real'; ?>
                                </li>
                                <li class="flex items-center gap-2 text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    <?php echo $lang['acad_panel_multiusuario'] ?? 'Gestión multi-usuario con roles'; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Casos de Éxito -->
    <section id="casos-exito" class="py-20 bg-black border-y border-metallic-green/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_casos_exito_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_casos_exito_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- SAJuR -->
                <div class="bg-gray-900 border border-metallic-green/30 rounded-xl p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-metallic-green rounded-full flex items-center justify-center text-white font-bold text-xl metallic-shine">
                            SAJuR
                        </div>
                        <div>
                            <h4 class="font-bold text-metallic-green-light text-lg"><?php echo $lang['acad_caso_sajur_nombre']; ?></h4>
                            <p class="text-sm text-gray-400"><?php echo $lang['acad_caso_sajur_tipo']; ?></p>
                        </div>
                    </div>
                    <p class="text-gray-300 italic leading-relaxed mb-4"><?php echo $lang['acad_caso_sajur_testimonio']; ?></p>
                    <div class="flex items-center gap-1 text-metallic-green-light">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                </div>

                <!-- Liberté -->
                <div class="bg-gray-900 border border-metallic-green/30 rounded-xl p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 green-gradient rounded-full flex items-center justify-center text-black font-bold text-xl">
                            Liberté
                        </div>
                        <div>
                            <h4 class="font-bold text-metallic-green-light text-lg"><?php echo $lang['acad_caso_liberte_nombre']; ?></h4>
                            <p class="text-sm text-gray-400"><?php echo $lang['acad_caso_liberte_tipo']; ?></p>
                        </div>
                    </div>
                    <p class="text-gray-300 italic leading-relaxed mb-4"><?php echo $lang['acad_caso_liberte_testimonio']; ?></p>
                    <div class="flex items-center gap-1 text-metallic-green-light">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Antes vs Después -->
    <section class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_antes_despues_titulo'] ?? 'Antes vs Después de Certificatum'; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_antes_despues_subtitulo']; ?></p>
            </div>
            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Antes -->
                <div class="bg-gray-900 border-2 border-metallic-red/50 rounded-xl p-8">
                    <div class="flex items-center gap-2 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-metallic-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-2xl font-bold text-metallic-red-light"><?php echo $lang['acad_antes_titulo']; ?></h3>
                    </div>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-red mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="text-gray-300"><?php echo $lang['acad_antes_trabajo_manual']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-red mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="text-gray-300"><?php echo $lang['acad_antes_costos']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-red mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="text-gray-300"><?php echo $lang['acad_antes_falsificacion']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-red mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="text-gray-300"><?php echo $lang['acad_antes_presencial']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-red mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="text-gray-300"><?php echo $lang['acad_antes_almacenamiento']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-red mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="text-gray-300"><?php echo $lang['acad_antes_estadisticas']; ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Después -->
                <div class="bg-gray-900 border-2 border-metallic-green/50 rounded-xl p-8">
                    <div class="flex items-center gap-2 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-2xl font-bold text-metallic-green-light"><?php echo $lang['acad_despues_titulo']; ?></h3>
                    </div>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><strong class="text-metallic-green-light"><?php echo $lang['acad_despues_emision']; ?></strong> <?php echo $lang['acad_despues_emision_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><strong class="text-metallic-green-light"><?php echo $lang['acad_despues_ahorro']; ?></strong> <?php echo $lang['acad_despues_ahorro_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><strong class="text-metallic-green-light"><?php echo $lang['acad_despues_validacion']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><strong class="text-metallic-green-light"><?php echo $lang['acad_despues_acceso']; ?></strong> <?php echo $lang['acad_despues_acceso_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><strong class="text-metallic-green-light"><?php echo $lang['acad_despues_digital']; ?></strong> <?php echo $lang['acad_despues_digital_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><?php echo $lang['acad_despues_dashboards']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Integraciones -->
    <section class="py-20 bg-black border-y border-metallic-green/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_integraciones_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_integraciones_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-5xl mx-auto">
                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-metallic-green/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-white mb-2"><?php echo $lang['acad_integ_excel']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_integ_excel_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-metallic-green/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-white mb-2"><?php echo $lang['acad_integ_api']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_integ_api_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-metallic-green/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-white mb-2"><?php echo $lang['acad_integ_email']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_integ_email_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-metallic-green/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-white mb-2"><?php echo $lang['acad_integ_subdominios']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_integ_subdominios_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes y Precios Section -->
    <section id="planes" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-5xl font-bold text-metallic-green-light mb-4"><?php echo $lang['acad_planes_titulo']; ?></h2>
                <p class="text-lg text-gray-400 mb-3"><?php echo $lang['acad_planes_subtitulo']; ?></p>
                <?php if (is_certificatum_promo_active()): ?>
                <?php $alta_prices = display_alta_with_savings($CERTIFICATUM_ALTA_USD, $current_language, $CERTIFICATUM_DISCOUNT); ?>
                <div class="inline-block mt-4 px-8 py-4 bg-gray-900 border-2 border-red-600 rounded-lg shadow-lg">
                    <p class="text-red-500 font-bold text-lg mb-3 flex items-center justify-center gap-2">
                        <span class="text-2xl">🔥</span>
                        <?php echo $lang['acad_promo_titulo'] ?? 'PROMO LANZAMIENTO'; ?>
                    </p>
                    <div class="text-center">
                        <p class="text-gray-500 text-sm mb-1">
                            <?php echo $lang['acad_promo_alta'] ?? 'Alta:'; ?> <span class="line-through"><?php echo $alta_prices['original']; ?></span>
                        </p>
                        <p class="text-white text-lg font-bold mb-1">
                            <?php echo $lang['acad_promo_alta_bonificada'] ?? 'Alta Bonificada:'; ?> <span class="text-metallic-green-light"><?php echo $alta_prices['discounted']; ?></span>
                        </p>
                        <p class="text-green-400 text-sm">(<?php echo $lang['price_you_save'] ?? 'Ahorrás'; ?> <?php echo $alta_prices['savings']; ?>)</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Banner de descuento para planes -->
            <?php if (is_certificatum_promo_active()): ?>
            <div class="text-center mb-8">
                <p class="text-red-500 font-bold text-2xl flex items-center justify-center gap-2">
                    <span>🔥</span>
                    <?php echo $CERTIFICATUM_DISCOUNT; ?>% <?php echo $lang['acad_descuento_banner'] ?? 'DE DESCUENTO en planes - Solo por tiempo limitado'; ?>
                    <span>🔥</span>
                </p>
            </div>
            <?php endif; ?>

            <!-- Plan Singularis (Pago por Certificado) - Separado arriba -->
            <div class="max-w-md mx-auto mb-8">
                <div class="bg-gray-900 border border-amber-500/30 rounded-xl p-6">
                    <div class="text-center">
                        <span class="inline-block px-3 py-1 bg-amber-500/20 text-amber-400 text-xs font-semibold rounded-full mb-3"><?php echo $lang['acad_plan_sin_suscripcion'] ?? 'SIN SUSCRIPCIÓN'; ?></span>
                        <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['acad_plan_singularis_titulo']; ?></h3>
                        <p class="text-gray-400 text-xs mb-4"><?php echo $lang['acad_plan_singularis_desc']; ?></p>
                        <div class="mb-4">
                            <?php
                            $singularis_price = get_localized_price($CERTIFICATUM_PRICING['singularis'], $current_language);
                            ?>
                            <span class="text-amber-400 text-3xl font-bold"><?php echo $singularis_price['local_formatted']; ?></span>
                            <span class="text-gray-400 text-sm ml-1"><?php echo $lang['acad_plan_singularis_precio_label']; ?></span>
                        </div>
                        <ul class="space-y-2 mb-6 text-gray-300 text-xs text-left max-w-xs mx-auto">
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['acad_plan_singularis_feat1']; ?></span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['acad_plan_singularis_feat2']; ?></span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['acad_plan_singularis_feat3']; ?></span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['acad_plan_singularis_feat4']; ?></span>
                            </li>
                            <li class="flex items-start gap-1.5">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><?php echo $lang['acad_plan_singularis_feat5']; ?></span>
                            </li>
                        </ul>
                        <a href="#contacto" class="w-full inline-block text-center px-4 py-2.5 bg-amber-500/20 border border-amber-500/30 text-amber-400 font-semibold rounded-lg hover:bg-amber-500/30 transition-colors text-sm">
                            <?php echo $lang['acad_plan_singularis_cta']; ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Planes con Suscripción -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <!-- Plan Essentialis -->
                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['acad_plan_essentialis_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['acad_plan_essentialis_desc']; ?></p>
                    <div class="mb-4">
                        <?php echo display_price_with_savings($CERTIFICATUM_PRICING['essentialis'], $current_language, $CERTIFICATUM_DISCOUNT, $lang['price_you_save'] ?? 'Ahorrás'); ?>
                        <p class="text-xs text-gray-400 mt-2"><?php echo $lang['acad_plan_pago_mensual'] ?? 'Pago mensual'; ?></p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat1']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat7']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat8']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat9']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_essentialis_feat10']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-metallic-green/30 text-metallic-green-light font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <?php echo $lang['acad_plan_essentialis_cta']; ?>
                    </a>
                </div>

                <!-- Plan Premium Académico (Destacado) -->
                <div class="bg-gray-900 border-2 border-metallic-green rounded-xl p-6 flex flex-col relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 green-gradient text-black text-xs font-bold rounded-full uppercase">
                        <?php echo $lang['acad_plan_premium_badge']; ?>
                    </div>
                    <h3 class="text-xl font-bold text-metallic-green-light mb-2 mt-2"><?php echo $lang['acad_plan_premium_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['acad_plan_premium_desc']; ?></p>
                    <div class="mb-4">
                        <?php echo display_price_with_savings($CERTIFICATUM_PRICING['premium'], $current_language, $CERTIFICATUM_DISCOUNT, $lang['price_you_save'] ?? 'Ahorrás'); ?>
                        <p class="text-xs text-gray-400 mt-2"><?php echo $lang['acad_plan_pago_mensual'] ?? 'Pago mensual'; ?></p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><strong><?php echo $lang['acad_plan_premium_feat1_strong']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_premium_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_premium_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_premium_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_premium_feat5']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 green-metallic-btn metallic-shine text-white font-bold rounded-lg transition-all text-sm">
                        <?php echo $lang['acad_plan_premium_cta']; ?>
                    </a>
                </div>

                <!-- Plan Excellens Académico -->
                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['acad_plan_excellens_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['acad_plan_excellens_desc']; ?></p>
                    <div class="mb-4">
                        <?php echo display_price_with_savings($CERTIFICATUM_PRICING['excellens'], $current_language, $CERTIFICATUM_DISCOUNT, $lang['price_you_save'] ?? 'Ahorrás'); ?>
                        <p class="text-xs text-gray-400 mt-2"><?php echo $lang['acad_plan_pago_mensual'] ?? 'Pago mensual'; ?></p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><strong><?php echo $lang['acad_plan_excellens_feat1_strong']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_excellens_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_excellens_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_excellens_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_excellens_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_excellens_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_excellens_feat7']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-metallic-green/30 text-metallic-green-light font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <?php echo $lang['acad_plan_excellens_cta']; ?>
                    </a>
                </div>

                <!-- Plan Supremus Académico -->
                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['acad_plan_supremus_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['acad_plan_supremus_desc']; ?></p>
                    <div class="mb-4">
                        <?php echo display_price_with_savings($CERTIFICATUM_PRICING['supremus'], $current_language, $CERTIFICATUM_DISCOUNT, $lang['price_you_save'] ?? 'Ahorrás'); ?>
                        <p class="text-xs text-gray-400 mt-2"><?php echo $lang['acad_plan_pago_mensual'] ?? 'Pago mensual'; ?></p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><strong><?php echo $lang['acad_plan_supremus_feat1_strong']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_supremus_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_supremus_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_supremus_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_supremus_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_supremus_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_supremus_feat7']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['acad_plan_supremus_feat8']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-metallic-green/30 text-metallic-green-light font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <?php echo $lang['acad_plan_supremus_cta']; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-metallic-green-light"><?php echo $lang['acad_faq_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_faq_subtitulo']; ?></p>
            </div>
            <div class="space-y-6">
                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-metallic-green-light mb-3"><?php echo $lang['acad_faq_cargo_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_cargo_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-metallic-green-light mb-3"><?php echo $lang['acad_faq_validez_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_validez_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-metallic-green-light mb-3"><?php echo $lang['acad_faq_diseno_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_diseno_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-metallic-green-light mb-3"><?php echo $lang['acad_faq_acceso_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_acceso_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-metallic-green-light mb-3"><?php echo $lang['acad_faq_validacion_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_validacion_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-metallic-green-light mb-3"><?php echo $lang['acad_faq_retroactivos_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_retroactivos_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-metallic-green-light mb-3"><?php echo $lang['acad_faq_limite_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_limite_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-metallic-green/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-metallic-green-light mb-3"><?php echo $lang['acad_faq_tecnico_titulo'] ?? '¿Necesito conocimientos técnicos?'; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_tecnico_resp'] ?? 'No. La plataforma es muy intuitiva, pero si prefiere no tocar nada, nuestro <strong class="text-metallic-green-light">Servicio Concierge</strong> se encarga de la configuración y carga de datos por usted. Solo nos envía la información y nosotros hacemos el resto.'; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section id="contacto" class="py-20 bg-gradient-to-br from-gray-900 via-gray-800 to-black border-y border-metallic-green/20 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-72 h-72 bg-metallic-greenrounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-metallic-green rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    <?php echo $lang['acad_cta_final_titulo']; ?>
                </h2>
                <p class="text-xl text-gray-300 mb-8 leading-relaxed">
                    <?php echo $lang['acad_cta_final_desc']; ?>
                    <span class="block mt-4 text-metallic-green-light"><?php echo $lang['acad_cta_final_equipo'] ?? 'Nuestro equipo de expertos y expertas se encarga de la configuración inicial para que pueda empezar a emitir certificados en minutos.'; ?></span>
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    <a href="mailto:contacto@verumax.com" class="px-8 py-4 green-metallic-btn metallic-shine text-white font-bold rounded-lg transition-all text-lg">
                        <?php echo $lang['acad_cta_final_demo']; ?>
                    </a>
                    <a href="tel:+5491112345678" class="px-8 py-4 bg-gray-800 border-2 border-metallic-green text-metallic-green-light font-semibold rounded-lg hover:bg-gray-700 transition-colors text-lg">
                        <?php echo $lang['acad_cta_final_contactar']; ?>
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-8 max-w-2xl mx-auto pt-8 border-t border-metallic-green/20">
                    <div>
                        <p class="text-3xl font-bold text-metallic-green-light"><?php echo $lang['acad_cta_final_implementacion']; ?></p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_cta_final_implementacion_desc']; ?></p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-metallic-green-light">90%</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_cta_final_menos_tiempo']; ?></p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-metallic-green-light">∞</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_cta_final_certificados']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script defer>
        // Ocultar loader cuando la página esté lista
        window.addEventListener('load', function() {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.classList.add('loader-hidden');
                // Remover del DOM después de la animación
                setTimeout(() => loader.remove(), 300);
            }
        });

        // Inicialización cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle del menú de idiomas
            const langToggle = document.getElementById('langToggle');
            const langMenu = document.getElementById('langMenu');

            if (langToggle && langMenu) {
                langToggle.addEventListener('click', function() {
                    langMenu.classList.toggle('hidden');
                });
            }

            // Cerrar menú al hacer clic fuera
            document.addEventListener('click', function(event) {
                if (langToggle && langMenu && !langToggle.contains(event.target) && !langMenu.contains(event.target)) {
                    langMenu.classList.add('hidden');
                }
            });

            // Menú móvil hamburger
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenuBtn.classList.toggle('active');
                    mobileMenu.classList.toggle('hidden');

                    // Actualizar aria-expanded
                    const isExpanded = !mobileMenu.classList.contains('hidden');
                    mobileMenuBtn.setAttribute('aria-expanded', isExpanded);
                });

                // Cerrar menú al hacer clic en un enlace
                mobileMenu.querySelectorAll('a[href^="#"]').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuBtn.classList.remove('active');
                        mobileMenu.classList.add('hidden');
                        mobileMenuBtn.setAttribute('aria-expanded', 'false');
                    });
                });
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
            body.classList.add('light-mode');
            darkModeIcon.classList.remove('hidden');
            lightModeIcon.classList.add('hidden');
        }
        */

        themeToggle.addEventListener('click', function() {
            const isLight = body.classList.contains('light-mode');

            if (isLight) {
                // Cambiar a oscuro
                body.classList.remove('light-mode');
                darkModeIcon.classList.add('hidden');
                lightModeIcon.classList.remove('hidden');
                themeToggle.setAttribute('title', '<?php echo $lang['nav_tema_claro']; ?>');
                localStorage.setItem('theme', 'dark');
            } else {
                // Cambiar a claro
                body.classList.add('light-mode');
                darkModeIcon.classList.remove('hidden');
                lightModeIcon.classList.add('hidden');
                themeToggle.setAttribute('title', '<?php echo $lang['nav_tema_oscuro']; ?>');
                localStorage.setItem('theme', 'light');
            }
        });

    </script>

    <!-- Modal de Servicio en Desarrollo -->
    <?php include '../includes/modal-en-desarrollo.php'; ?>

</body>
</html>
<?php
// Guardar página en caché
$output = ob_get_clean();
save_cached_page($cache_key, $output);
echo $output;
?>
