<?php
/**
 * Verumax - Tarjeta Digital Profesional
 * Página enfocada en Tarjeta Digital con QR Infalsificable como servicio principal
 */
require_once 'config.php';
require_once 'lang_config.php';
require_once 'maintenance_config.php';
require_once 'includes/currency_converter.php';
require_once 'includes/pricing_config.php';
require_once 'includes/cache_helper.php';

// =====================================
// MODO MANTENIMIENTO
// =====================================
check_maintenance_mode();

// =====================================
// SISTEMA DE CACHÉ
// =====================================
$cache_key = 'identitas_' . $current_language;
$cached_page = get_cached_page($cache_key, 3600); // 1 hora de caché

if ($cached_page) {
    echo $cached_page;
    exit;
}

ob_start();
?>
<!DOCTYPE html>
<html lang="<?php echo substr($current_language, 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['prof_meta_title']; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $lang['prof_meta_description']; ?>">
    <meta name="keywords" content="<?php echo $lang['prof_meta_keywords']; ?>">
    <meta name="author" content="<?php echo $lang['meta_author']; ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $lang['prof_meta_og_title']; ?>">
    <meta property="og:description" content="<?php echo $lang['prof_meta_og_description']; ?>">
    <meta property="og:type" content="website">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $lang['prof_meta_twitter_title']; ?>">
    <meta name="twitter:description" content="<?php echo $lang['prof_meta_twitter_description']; ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cdefs%3E%3ClinearGradient id='grad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23D4AF37;stop-opacity:1'/%3E%3Cstop offset='100%25' style='stop-color:%23F0D377;stop-opacity:1'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='100' height='100' fill='%230a0a0a'/%3E%3Cpath fill='url(%23grad)' d='M50 20L30 35v20l20 15 20-15V35L50 20zm0 8l12 9v14l-12 9-12-9V37l12-9z'/%3E%3Cpath fill='%232E7D32' d='M42 48l4 4 8-8' stroke='%23ffffff' stroke-width='2' fill='none'/%3E%3C/svg%3E">

    <!-- Flag Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'gold': { DEFAULT: '#D4AF37', light: '#F0D377', dark: '#B8941E' },
                        'metallic-green': { DEFAULT: '#2E7D32', light: '#4CAF50', dark: '#1B5E20' },
                        'metallic-red': { DEFAULT: '#C62828', light: '#E53935', dark: '#8E0000' }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gold-gradient { background: linear-gradient(135deg, #D4AF37 0%, #F0D377 100%); }
        .hero-bg {
            background-image: linear-gradient(135deg, rgba(10, 10, 10, 0.95) 0%, rgba(26, 26, 26, 0.9) 100%),
                              repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(212, 175, 55, 0.03) 10px, rgba(212, 175, 55, 0.03) 20px);
        }
        .card-mockup {
            box-shadow: 0 20px 60px rgba(212, 175, 55, 0.3), 0 0 40px rgba(212, 175, 55, 0.1);
        }

        /* Theme Variables */
        :root {
            --bg-primary: #000000;
            --bg-secondary: #0a0a0a;
            --bg-tertiary: #1a1a1a;
            --text-primary: #ffffff;
            --text-secondary: #d1d5db;
            --text-tertiary: #9ca3af;
            --border-color: rgba(212, 175, 55, 0.2);
        }

        body.light-mode {
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --bg-tertiary: #f3f4f6;
            --text-primary: #111827;
            --text-secondary: #374151;
            --text-tertiary: #6b7280;
            --border-color: rgba(212, 175, 55, 0.3);
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .bg-black { background-color: var(--bg-primary) !important; }
        .bg-gray-950 { background-color: var(--bg-secondary) !important; }
        .bg-gray-900 { background-color: var(--bg-tertiary) !important; }
        .text-white { color: var(--text-primary) !important; }
        .text-gray-300 { color: var(--text-secondary) !important; }
        .text-gray-400 { color: var(--text-tertiary) !important; }
        .border-gold\/20 { border-color: var(--border-color) !important; }

        /* Dark mode icons visibility */
        body:not(.light-mode) .light-mode-icon { display: block; }
        body:not(.light-mode) .dark-mode-icon { display: none; }
        body.light-mode .light-mode-icon { display: none; }
        body.light-mode .dark-mode-icon { display: block; }
    </style>

    <!-- Estilos Compartidos -->
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>styles.css">
</head>
<body class="bg-black text-white">

    <!-- Navigation -->
    <nav class="bg-black border-b border-gold/20 sticky top-0 z-50 backdrop-blur-sm bg-black/90">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </div>
                    <div>
                        <a href="index.php?lang=<?php echo $current_language; ?>" class="flex items-center">
                            <img src="assets/images/logo-verumax-texto.png" alt="Verumax" class="h-6">
                        </a>
                        <p class="text-xs text-gray-400"><?php echo $lang['prof_nav_perfiles']; ?></p>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="index.php?lang=<?php echo $current_language; ?>" class="text-gray-300 hover:text-gold transition-colors flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <?php echo $lang['nav_volver_inicio']; ?>
                    </a>
                    <a href="#como-funciona" class="text-gray-300 hover:text-gold transition-colors"><?php echo $lang['prof_nav_como_funciona']; ?></a>
                    <a href="#caracteristicas" class="text-gray-300 hover:text-gold transition-colors"><?php echo $lang['prof_nav_caracteristicas']; ?></a>
                    <a href="#planes" class="text-gray-300 hover:text-gold transition-colors"><?php echo $lang['prof_nav_planes']; ?></a>

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

                    <a href="#planes" class="px-6 py-2 gold-gradient text-black font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        <?php echo $lang['prof_nav_empezar']; ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 hero-bg border-b border-gold/20">
        <div class="container mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-12 items-center max-w-7xl mx-auto">
                <!-- Left: Content -->
                <div>
                    <div class="inline-block px-4 py-2 bg-metallic-green/20 border border-metallic-green/30 rounded-full mb-6">
                        <span class="text-metallic-green-light text-sm font-semibold"><?php echo $lang['prof_hero_badge']; ?></span>
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        <?php echo $lang['prof_hero_title']; ?><br>
                        <span class="gold-gradient bg-clip-text text-transparent"><?php echo $lang['prof_hero_title_highlight']; ?></span>
                    </h1>
                    <p class="text-xl text-gray-300 mb-6 leading-relaxed">
                        <?php echo $lang['prof_hero_subtitle']; ?>
                    </p>

                    <!-- URL Example -->
                    <div class="bg-gray-900 border border-gold/20 rounded-lg p-4 mb-8 inline-block">
                        <p class="text-sm text-gray-400 mb-1"><?php echo $lang['prof_hero_url_ejemplo']; ?></p>
                        <p class="text-lg font-mono font-semibold">
                            <span class="text-white"><?php echo $lang['prof_hero_url_ejemplo_subdomain']; ?></span><span class="text-gold"><?php echo $lang['prof_hero_url_ejemplo_domain']; ?></span>
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#planes" class="px-8 py-4 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/30 text-center">
                            <?php echo $lang['prof_cta_empezar']; ?>
                        </a>
                        <a href="#como-funciona" class="px-8 py-4 bg-gray-800 border border-gold/30 text-gold font-semibold rounded-lg hover:bg-gray-700 transition-colors text-center">
                            <?php echo $lang['prof_cta_secundario']; ?>
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 mt-12">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gold mb-1">✓</div>
                            <p class="text-sm text-white font-semibold"><?php echo $lang['prof_stat_qr']; ?></p>
                            <p class="text-xs text-gray-400"><?php echo $lang['prof_stat_qr_desc']; ?></p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gold mb-1">⚡</div>
                            <p class="text-sm text-white font-semibold"><?php echo $lang['prof_stat_actualizacion']; ?></p>
                            <p class="text-xs text-gray-400"><?php echo $lang['prof_stat_actualizacion_desc']; ?></p>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gold mb-1">🌐</div>
                            <p class="text-sm text-white font-semibold"><?php echo $lang['prof_stat_landing']; ?></p>
                            <p class="text-xs text-gray-400"><?php echo $lang['prof_stat_landing_desc']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Right: Card Mockup -->
                <div class="relative">
                    <div class="card-mockup bg-gradient-to-br from-gray-900 to-black border-2 border-gold/30 rounded-2xl p-8 relative overflow-hidden">
                        <!-- Card Design Mock -->
                        <div class="absolute top-0 right-0 w-32 h-32 gold-gradient opacity-10 rounded-full blur-3xl"></div>
                        <div class="relative z-10">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-20 h-20 bg-gold/20 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Juan Pérez</h3>
                                    <p class="text-gray-400 text-sm">Arquitecto</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-6">
                                <p class="text-gray-300 text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    +54 11 5555-1234
                                </p>
                                <p class="text-gray-300 text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    juan@estudio.com
                                </p>
                            </div>
                            <!-- QR Mock -->
                            <div class="flex justify-center">
                                <div class="w-32 h-32 bg-white rounded-lg p-2">
                                    <div class="w-full h-full bg-black rounded grid grid-cols-4 gap-1 p-2">
                                        <div class="bg-white rounded-sm"></div>
                                        <div class="bg-black"></div>
                                        <div class="bg-white rounded-sm"></div>
                                        <div class="bg-white rounded-sm"></div>
                                        <div class="bg-black"></div>
                                        <div class="bg-white rounded-sm"></div>
                                        <div class="bg-black"></div>
                                        <div class="bg-white rounded-sm"></div>
                                        <div class="bg-white rounded-sm"></div>
                                        <div class="bg-black"></div>
                                        <div class="bg-white rounded-sm"></div>
                                        <div class="bg-black"></div>
                                        <div class="bg-white rounded-sm"></div>
                                        <div class="bg-white rounded-sm"></div>
                                        <div class="bg-black"></div>
                                        <div class="bg-white rounded-sm"></div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-center text-gold text-xs mt-2 font-semibold">Escanéame</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Qué es la Tarjeta Digital Section -->
    <section class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="max-w-5xl mx-auto text-center mb-12">
                <h2 class="text-3xl md:text-5xl font-bold text-gold mb-4"><?php echo $lang['prof_que_es_titulo']; ?></h2>
                <p class="text-lg text-gray-400 mb-6"><?php echo $lang['prof_que_es_subtitulo']; ?></p>
                <p class="text-xl text-gray-300 leading-relaxed">
                    <?php echo $lang['prof_que_es_desc']; ?>
                </p>
            </div>

            <!-- 4 Features Cards -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['prof_tarjeta_feature1_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_tarjeta_feature1_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['prof_tarjeta_feature2_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_tarjeta_feature2_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['prof_tarjeta_feature3_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_tarjeta_feature3_desc']; ?></p>
                </div>

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2"><?php echo $lang['prof_tarjeta_feature4_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_tarjeta_feature4_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cómo Funciona Section -->
    <section id="como-funciona" class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-bold text-gold mb-4"><?php echo $lang['prof_como_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['prof_como_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Paso 1 -->
                <div class="relative">
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 gold-gradient rounded-full flex items-center justify-center text-black font-bold text-xl">1</div>
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 pt-12 text-center h-full">
                        <div class="w-16 h-16 bg-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['prof_como_paso1']; ?></h3>
                        <p class="text-gray-400 text-sm leading-relaxed"><?php echo $lang['prof_como_paso1_desc']; ?></p>
                    </div>
                </div>

                <!-- Paso 2 -->
                <div class="relative">
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 gold-gradient rounded-full flex items-center justify-center text-black font-bold text-xl">2</div>
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 pt-12 text-center h-full">
                        <div class="w-16 h-16 bg-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['prof_como_paso2']; ?></h3>
                        <p class="text-gray-400 text-sm leading-relaxed"><?php echo $lang['prof_como_paso2_desc']; ?></p>
                    </div>
                </div>

                <!-- Paso 3 -->
                <div class="relative">
                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 gold-gradient rounded-full flex items-center justify-center text-black font-bold text-xl">3</div>
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 pt-12 text-center h-full">
                        <div class="w-16 h-16 bg-gold/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['prof_como_paso3']; ?></h3>
                        <p class="text-gray-400 text-sm leading-relaxed"><?php echo $lang['prof_como_paso3_desc']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Características Únicas Section -->
    <section id="caracteristicas" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-bold text-gold mb-4"><?php echo $lang['prof_caracteristicas_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['prof_caracteristicas_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                <!-- QR Infalsificable -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 hover:border-gold/50 transition-all">
                    <div class="w-14 h-14 gold-gradient rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['prof_caract_qr_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed"><?php echo $lang['prof_caract_qr_desc']; ?></p>
                </div>

                <!-- Panel de Administración -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 hover:border-gold/50 transition-all">
                    <div class="w-14 h-14 gold-gradient rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['prof_caract_panel_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed"><?php echo $lang['prof_caract_panel_desc']; ?></p>
                </div>

                <!-- Paletas de Colores -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 hover:border-gold/50 transition-all">
                    <div class="w-14 h-14 gold-gradient rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['prof_caract_paletas_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed"><?php echo $lang['prof_caract_paletas_desc']; ?></p>
                </div>

                <!-- Landing Page -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 hover:border-gold/50 transition-all">
                    <div class="w-14 h-14 gold-gradient rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['prof_caract_landing_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed"><?php echo $lang['prof_caract_landing_desc']; ?></p>
                </div>

                <!-- Múltiples Formatos -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 hover:border-gold/50 transition-all">
                    <div class="w-14 h-14 gold-gradient rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['prof_caract_formatos_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed"><?php echo $lang['prof_caract_formatos_desc']; ?></p>
                </div>

                <!-- Seguridad -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 hover:border-gold/50 transition-all">
                    <div class="w-14 h-14 gold-gradient rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['prof_caract_seguridad_titulo']; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed"><?php echo $lang['prof_caract_seguridad_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Casos de Uso Section -->
    <section class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-bold text-gold mb-4"><?php echo $lang['prof_casos_titulo']; ?></h2>
                <p class="text-lg text-gray-400"><?php echo $lang['prof_casos_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <h3 class="text-lg font-bold text-gold mb-2"><?php echo $lang['prof_caso_abogados']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_caso_abogados_desc']; ?></p>
                </div>
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <h3 class="text-lg font-bold text-gold mb-2"><?php echo $lang['prof_caso_medicos']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_caso_medicos_desc']; ?></p>
                </div>
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <h3 class="text-lg font-bold text-gold mb-2"><?php echo $lang['prof_caso_arquitectos']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_caso_arquitectos_desc']; ?></p>
                </div>
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <h3 class="text-lg font-bold text-gold mb-2"><?php echo $lang['prof_caso_comercios']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_caso_comercios_desc']; ?></p>
                </div>
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <h3 class="text-lg font-bold text-gold mb-2"><?php echo $lang['prof_caso_coaches']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_caso_coaches_desc']; ?></p>
                </div>
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <h3 class="text-lg font-bold text-gold mb-2"><?php echo $lang['prof_caso_freelancers']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_caso_freelancers_desc']; ?></p>
                </div>
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <h3 class="text-lg font-bold text-gold mb-2"><?php echo $lang['prof_caso_productores']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_caso_productores_desc']; ?></p>
                </div>
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <h3 class="text-lg font-bold text-gold mb-2"><?php echo $lang['prof_caso_artesanos']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_caso_artesanos_desc']; ?></p>
                </div>
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <h3 class="text-lg font-bold text-gold mb-2"><?php echo $lang['prof_caso_trainers']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['prof_caso_trainers_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes y Precios Section -->
    <section id="planes" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-5xl font-bold text-gold mb-4"><?php echo $lang['prof_planes_titulo']; ?></h2>
                <p class="text-lg text-gray-400 mb-3"><?php echo $lang['prof_planes_subtitulo']; ?></p>
                <?php if (in_array($current_language, ['es_AR', 'es_CL', 'pt_BR']) && is_promo_active()): ?>
                <div class="inline-block mt-4 px-8 py-4 bg-gray-900 border-2 border-red-600 rounded-lg shadow-lg">
                    <p class="text-red-500 font-bold text-lg mb-2 flex items-center gap-2">
                        <span class="text-2xl">🔥</span>
                        <?php echo $lang['prof_planes_promo']; ?>
                    </p>
                    <p class="text-gray-300 text-sm mb-1">
                        <?php echo $lang['prof_planes_alta_texto']; ?> <?php echo get_alta_price_formatted($ALTA_PRICE_USD, $current_language); ?> (bonificado)
                    </p>
                    <p class="text-gold font-semibold text-base"><?php echo $lang['prof_planes_descuento']; ?></p>
                </div>
                <?php endif; ?>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <!-- Plan Basicum -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['prof_plan_esencial_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['prof_plan_esencial_desc']; ?></p>
                    <div class="mb-4">
                        <div class="text-3xl font-bold text-white mb-2">
                            <?php echo display_price($PRICING['basicum'], $current_language, true, $DISCOUNT_PERCENTAGE); ?>
                        </div>
                        <p class="text-xs text-gold font-semibold">Pago único anual</p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat1']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat7']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat8']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat9']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat10']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_esencial_feat11']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-gold/30 text-gold font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <?php echo $lang['prof_plan_esencial_cta']; ?>
                    </a>
                </div>

                <!-- Plan Premium (Destacado) -->
                <div class="bg-gray-900 border-2 border-gold rounded-xl p-6 flex flex-col relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 gold-gradient text-black text-xs font-bold rounded-full uppercase">
                        <?php echo $lang['prof_plan_plus_badge']; ?>
                    </div>
                    <h3 class="text-xl font-bold text-gold mb-2 mt-2"><?php echo $lang['prof_plan_plus_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['prof_plan_plus_desc']; ?></p>
                    <div class="mb-4">
                        <div class="text-3xl font-bold text-white mb-2">
                            <?php echo display_price($PRICING['premium'], $current_language, true, $DISCOUNT_PERCENTAGE); ?>
                        </div>
                        <p class="text-xs text-gold font-semibold">Pago único anual</p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><strong><?php echo $lang['prof_plan_plus_feat1_strong']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_plus_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_plus_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_plus_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_plus_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_plus_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_plus_feat7']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_plus_feat8']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_plus_feat9']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_plus_feat10']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity text-sm">
                        <?php echo $lang['prof_plan_plus_cta']; ?>
                    </a>
                </div>

                <!-- Plan Excellens -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['prof_plan_pro_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['prof_plan_pro_desc']; ?></p>
                    <div class="mb-4">
                        <div class="text-3xl font-bold text-white mb-2">
                            <?php echo display_price($PRICING['excellens'], $current_language, true, $DISCOUNT_PERCENTAGE); ?>
                        </div>
                        <p class="text-xs text-gold font-semibold">Pago único anual</p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><strong><?php echo $lang['prof_plan_pro_feat1_strong']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_pro_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_pro_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_pro_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_pro_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_pro_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_pro_feat7']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_pro_feat8']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-gold/30 text-gold font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <?php echo $lang['prof_plan_pro_cta']; ?>
                    </a>
                </div>

                <!-- Plan Supremus -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['prof_plan_elite_titulo']; ?></h3>
                    <p class="text-gray-400 text-xs mb-4"><?php echo $lang['prof_plan_elite_desc']; ?></p>
                    <div class="mb-4">
                        <div class="text-3xl font-bold text-white mb-2">
                            <?php echo display_price($PRICING['supremus'], $current_language, true, $DISCOUNT_PERCENTAGE); ?>
                        </div>
                        <p class="text-xs text-gold font-semibold">Pago único anual</p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><strong><?php echo $lang['prof_plan_elite_feat1_strong']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_elite_feat2']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_elite_feat3']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_elite_feat4']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_elite_feat5']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_elite_feat6']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_elite_feat7']; ?></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo $lang['prof_plan_elite_feat8']; ?></span>
                        </li>
                    </ul>
                    <a href="#contacto" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-gold/30 text-gold font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <?php echo $lang['prof_plan_elite_cta']; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Formas de Pago Section -->
    <section class="py-16 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold mb-3"><?php echo $lang['prof_pagos_titulo']; ?></h2>
                <p class="text-gray-400"><?php echo $lang['prof_pagos_subtitulo']; ?></p>
            </div>

            <?php if ($current_language === 'es_AR'): ?>
                <!-- Formas de Pago para Argentina -->
                <div class="max-w-5xl mx-auto">
                    <h3 class="text-xl font-semibold text-white mb-6 text-center"><?php echo $lang['prof_pagos_argentina']; ?></h3>
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- MercadoPago -->
                        <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                            <div class="w-16 h-16 mx-auto mb-4 bg-blue-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2"><?php echo $lang['prof_pago_mercadopago']; ?></h4>
                            <p class="text-gray-400 text-sm"><?php echo $lang['prof_pago_mercadopago_desc']; ?></p>
                        </div>

                        <!-- PagoFácil -->
                        <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                            <div class="w-16 h-16 mx-auto mb-4 bg-red-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2"><?php echo $lang['prof_pago_pagofacil']; ?></h4>
                            <p class="text-gray-400 text-sm"><?php echo $lang['prof_pago_pagofacil_desc']; ?></p>
                        </div>

                        <!-- Transferencia -->
                        <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                            <div class="w-16 h-16 mx-auto mb-4 bg-green-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2"><?php echo $lang['prof_pago_transferencia']; ?></h4>
                            <p class="text-gray-400 text-sm"><?php echo $lang['prof_pago_transferencia_desc']; ?></p>
                        </div>

                        <!-- PayPal -->
                        <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                            <div class="w-16 h-16 mx-auto mb-4 bg-blue-600/20 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.067 8.478c.492.88.556 2.014.3 3.327-.74 3.806-3.276 5.12-6.514 5.12h-.5a.805.805 0 00-.794.68l-.04.22-.63 3.993-.028.15a.805.805 0 01-.793.68H8.032c-.45 0-.77-.421-.67-.863l1.774-11.24a.956.956 0 01.944-.806h5.102c1.59 0 2.726.332 3.386.99.344.343.574.744.674 1.204.105.49.097 1.046-.031 1.667z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2"><?php echo $lang['prof_pago_paypal']; ?></h4>
                            <p class="text-gray-400 text-sm"><?php echo $lang['prof_pago_paypal_desc']; ?></p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Pago Internacional (solo PayPal) -->
                <div class="max-w-md mx-auto">
                    <h3 class="text-xl font-semibold text-white mb-6 text-center"><?php echo $lang['prof_pagos_internacional']; ?></h3>
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-8 text-center hover:border-gold/50 transition-all">
                        <div class="w-20 h-20 mx-auto mb-4 bg-blue-600/20 rounded-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.067 8.478c.492.88.556 2.014.3 3.327-.74 3.806-3.276 5.12-6.514 5.12h-.5a.805.805 0 00-.794.68l-.04.22-.63 3.993-.028.15a.805.805 0 01-.793.68H8.032c-.45 0-.77-.421-.67-.863l1.774-11.24a.956.956 0 01.944-.806h5.102c1.59 0 2.726.332 3.386.99.344.343.574.744.674 1.204.105.49.097 1.046-.031 1.667z"></path>
                            </svg>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-3"><?php echo $lang['prof_pago_paypal']; ?></h4>
                        <p class="text-gray-400"><?php echo $lang['prof_pago_paypal_desc']; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Final Section -->
    <section class="py-20 bg-black border-t border-gold/20">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">
                    <?php echo $lang['prof_cta_final_titulo']; ?>
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    <?php echo $lang['prof_cta_final_desc']; ?>
                </p>
                <a href="#planes" class="inline-block px-12 py-4 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/30 text-lg mb-6">
                    <?php echo $lang['prof_cta_final_btn']; ?>
                </a>
                <p class="text-gray-400 text-sm">
                    <?php echo $lang['prof_cta_final_garantia']; ?>
                </p>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Toggle del menú de idiomas
        document.getElementById('langToggle').addEventListener('click', function() {
            document.getElementById('langMenu').classList.toggle('hidden');
        });

        document.addEventListener('click', function(event) {
            const langToggle = document.getElementById('langToggle');
            const langMenu = document.getElementById('langMenu');
            if (!langToggle.contains(event.target) && !langMenu.contains(event.target)) {
                langMenu.classList.add('hidden');
            }
        });

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;

        // Check for saved theme preference or default to dark mode
        const currentTheme = localStorage.getItem('theme') || 'dark';
        if (currentTheme === 'light') {
            body.classList.add('light-mode');
        }

        themeToggle.addEventListener('click', function() {
            body.classList.toggle('light-mode');

            // Update tooltip
            if (body.classList.contains('light-mode')) {
                themeToggle.setAttribute('title', '<?php echo $lang['nav_tema_oscuro']; ?>');
            } else {
                themeToggle.setAttribute('title', '<?php echo $lang['nav_tema_claro']; ?>');
            }

            // Save preference
            const theme = body.classList.contains('light-mode') ? 'light' : 'dark';
            localStorage.setItem('theme', theme);
        });

        // Smooth scroll para anclas
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

    </script>

    <!-- Modal de Servicio en Desarrollo -->
    <?php include 'includes/modal-en-desarrollo.php'; ?>

</body>
</html>
<?php
// Guardar página en caché
$output = ob_get_clean();
save_cached_page($cache_key, $output);
echo $output;
?>
