<?php
/**
 * OriginalisDoc - Página de Solución Académica
 * Versión multi-idioma
 */
require_once 'config.php';
require_once 'lang_config.php';
?>
<!DOCTYPE html>
<html lang="<?php echo substr($current_language, 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['acad_meta_title']; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $lang['acad_meta_description']; ?>">
    <meta name="keywords" content="<?php echo $lang['acad_meta_keywords']; ?>">
    <meta name="author" content="<?php echo $lang['meta_author']; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://validarcert.com/academico">
    <meta property="og:title" content="<?php echo $lang['acad_meta_og_title']; ?>">
    <meta property="og:description" content="<?php echo $lang['acad_meta_og_description']; ?>">
    <meta property="og:image" content="https://validarcert.com/og-image-academico.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://validarcert.com/academico">
    <meta property="twitter:title" content="<?php echo $lang['acad_meta_twitter_title']; ?>">
    <meta property="twitter:description" content="<?php echo $lang['acad_meta_twitter_description']; ?>">
    <meta property="twitter:image" content="https://validarcert.com/og-image-academico.png">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cdefs%3E%3ClinearGradient id='grad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' style='stop-color:%23D4AF37;stop-opacity:1'/%3E%3Cstop offset='100%25' style='stop-color:%23F0D377;stop-opacity:1'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='100' height='100' fill='%230a0a0a'/%3E%3Cpath fill='url(%23grad)' d='M50 20L30 35v20l20 15 20-15V35L50 20zm0 8l12 9v14l-12 9-12-9V37l12-9z'/%3E%3Cpath fill='%232E7D32' d='M42 48l4 4 8-8' stroke='%23ffffff' stroke-width='2' fill='none'/%3E%3C/svg%3E">

    <!-- Flag Icons CSS -->
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

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
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
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent 30%,
                rgba(255, 255, 255, 0.1) 50%,
                transparent 70%
            );
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .hero-bg {
            background-image:
                linear-gradient(135deg, rgba(10, 10, 10, 0.95) 0%, rgba(26, 26, 26, 0.9) 100%),
                repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(212, 175, 55, 0.03) 10px, rgba(212, 175, 55, 0.03) 20px);
        }
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <a href="index.php?lang=<?php echo $current_language; ?>" class="text-xl font-bold gold-gradient bg-clip-text text-transparent">OriginalisDoc</a>
                        <p class="text-xs text-gray-400"><?php echo $lang['acad_nav_academico']; ?></p>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="index.php?lang=<?php echo $current_language; ?>" class="text-gray-300 hover:text-gold transition-colors flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <?php echo $lang['nav_volver_inicio']; ?>
                    </a>
                    <a href="#beneficios" class="text-gray-300 hover:text-gold transition-colors"><?php echo $lang['acad_nav_beneficios']; ?></a>
                    <a href="#funcionalidades" class="text-gray-300 hover:text-gold transition-colors"><?php echo $lang['acad_nav_funcionalidades']; ?></a>
                    <a href="#casos-exito" class="text-gray-300 hover:text-gold transition-colors"><?php echo $lang['acad_nav_casos_exito']; ?></a>
                    <a href="#faq" class="text-gray-300 hover:text-gold transition-colors"><?php echo $lang['acad_nav_faq']; ?></a>

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

                    <a href="#contacto" class="px-6 py-2 gold-gradient text-black font-semibold rounded-lg hover:opacity-90 transition-opacity"><?php echo $lang['acad_nav_solicitar_demo']; ?></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20 hero-bg border-b border-gold/20">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-block px-4 py-2 bg-metallic-green/20 border border-metallic-green/30 rounded-full mb-6">
                    <span class="text-metallic-green-light text-sm font-semibold"><?php echo $lang['acad_hero_badge']; ?></span>
                </div>
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    <?php echo $lang['acad_hero_title']; ?> <span class="gold-gradient bg-clip-text text-transparent"><?php echo $lang['acad_hero_title_highlight']; ?></span>
                </h1>
                <p class="text-xl text-gray-300 mb-8 leading-relaxed">
                    <?php echo $lang['acad_hero_subtitle']; ?> <strong class="text-gold"><?php echo $lang['acad_hero_instituciones']; ?></strong>, <strong class="text-gold"><?php echo $lang['acad_hero_centros_formacion']; ?></strong>, <strong class="text-gold"><?php echo $lang['acad_hero_academias']; ?></strong> y <strong class="text-gold"><?php echo $lang['acad_hero_formadores']; ?></strong>
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="#contacto" class="px-8 py-4 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/30 text-lg">
                        <?php echo $lang['acad_cta_solicitar_demo']; ?>
                    </a>
                    <a href="#como-funciona" class="px-8 py-4 bg-gray-800 border border-gold/30 text-gold font-semibold rounded-lg hover:bg-gray-700 transition-colors text-lg">
                        <?php echo $lang['acad_cta_ver_como_funciona']; ?>
                    </a>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="grid grid-cols-3 gap-6 mt-16 max-w-2xl mx-auto">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gold">90%</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_hero_stat_tiempo']; ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-metallic-green-light">24/7</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_hero_stat_portal']; ?></p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-gold">0%</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_hero_stat_falsificaciones']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problemas que Resuelve -->
    <section class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold mb-4"><?php echo $lang['acad_problemas_titulo']; ?></h2>
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
    <section id="como-funciona" class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['acad_como_funciona_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_como_funciona_subtitulo']; ?></p>
            </div>
            <div class="grid md:grid-cols-3 gap-10 max-w-5xl mx-auto">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto flex items-center justify-center gold-gradient text-black rounded-full text-3xl font-bold shadow-lg shadow-gold/30">1</div>
                    <h3 class="mt-6 text-xl font-bold text-gold"><?php echo $lang['acad_como_funciona_paso1_titulo']; ?></h3>
                    <p class="mt-2 text-gray-300"><?php echo $lang['acad_como_funciona_paso1_desc']; ?></p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto flex items-center justify-center bg-metallic-green text-white rounded-full text-3xl font-bold shadow-lg shadow-metallic-green/30 metallic-shine">2</div>
                    <h3 class="mt-6 text-xl font-bold text-metallic-green-light"><?php echo $lang['acad_como_funciona_paso2_titulo']; ?></h3>
                    <p class="mt-2 text-gray-300"><?php echo $lang['acad_como_funciona_paso2_desc']; ?></p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto flex items-center justify-center gold-gradient text-black rounded-full text-3xl font-bold shadow-lg shadow-gold/30">3</div>
                    <h3 class="mt-6 text-xl font-bold text-gold"><?php echo $lang['acad_como_funciona_paso3_titulo']; ?></h3>
                    <p class="mt-2 text-gray-300"><?php echo $lang['acad_como_funciona_paso3_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tipos de Documentos Académicos -->
    <section id="documentos" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['acad_documentos_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_documentos_subtitulo']; ?></p>
            </div>

            <!-- Documentos Principales -->
            <div class="mb-12">
                <h3 class="text-2xl font-bold text-metallic-green-light mb-6 text-center"><?php echo $lang['acad_documentos_principales']; ?></h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2"><?php echo $lang['acad_doc_certificado_aprobacion']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_certificado_aprobacion_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2"><?php echo $lang['acad_doc_diplomas']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_diplomas_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 gold-gradient rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2"><?php echo $lang['acad_doc_analiticos']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_analiticos_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_constancia_regular']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_constancia_regular_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                        <div class="w-10 h-10 bg-metallic-green rounded-lg flex items-center justify-center mb-4 metallic-shine">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-metallic-green-light mb-2"><?php echo $lang['acad_doc_constancia_inscripcion']; ?></h4>
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo $lang['acad_doc_constancia_inscripcion_desc']; ?></p>
                    </div>

                    <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
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
                        <div class="w-10 h-10 bg-gold rounded-lg flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-gold mb-2"><?php echo $lang['acad_doc_reconocimientos']; ?></h4>
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
    <section id="portal-estudiante" class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="inline-block px-4 py-2 bg-metallic-green/20 border border-metallic-green/30 rounded-full mb-6">
                            <span class="text-metallic-green-light text-sm font-semibold"><?php echo $lang['acad_portal_badge']; ?></span>
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold text-gold mb-6"><?php echo $lang['acad_portal_titulo']; ?></h2>
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
                                <div class="w-8 h-8 bg-gold/20 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                <div class="w-8 h-8 bg-gold/20 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                    <div class="bg-gray-900 border border-gold/20 rounded-2xl p-8 shadow-2xl">
                        <div class="bg-gray-800 rounded-lg p-6 mb-4">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center text-black font-bold">
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

                            <div class="bg-gray-800 rounded-lg p-4 border-l-4 border-gold">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold text-white text-sm"><?php echo $lang['acad_portal_demo_certificado']; ?></p>
                                        <p class="text-xs text-gray-400 mt-1"><?php echo $lang['acad_portal_demo_certificado_fecha']; ?></p>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['acad_casos_uso_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_casos_uso_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all text-center">
                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gold mb-2"><?php echo $lang['acad_caso_universidades']; ?></h3>
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

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all text-center">
                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gold mb-2"><?php echo $lang['acad_caso_idiomas']; ?></h3>
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

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all text-center">
                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gold mb-2"><?php echo $lang['acad_caso_laboral']; ?></h3>
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

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all text-center">
                    <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-gold mb-2"><?php echo $lang['acad_caso_organizadores']; ?></h3>
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
    <section id="funcionalidades" class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['acad_func_titulo']; ?></h2>
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

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['acad_beneficios_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_beneficios_subtitulo']; ?></p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">
                <div class="text-center">
                    <div class="w-20 h-20 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold text-black">
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
                    <div class="w-20 h-20 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4 text-3xl font-bold text-black">
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

            <div class="mt-16 max-w-4xl mx-auto bg-gradient-to-r from-metallic-green/10 to-gold/10 border border-gold/20 rounded-2xl p-8">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gold mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-2xl font-bold text-white mb-3"><?php echo $lang['acad_beneficio_trazabilidad_titulo']; ?></h3>
                    <p class="text-gray-300 leading-relaxed"><?php echo $lang['acad_beneficio_trazabilidad_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Casos de Éxito -->
    <section id="casos-exito" class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['acad_casos_exito_titulo']; ?></h2>
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
                            <h4 class="font-bold text-gold text-lg"><?php echo $lang['acad_caso_sajur_nombre']; ?></h4>
                            <p class="text-sm text-gray-400"><?php echo $lang['acad_caso_sajur_tipo']; ?></p>
                        </div>
                    </div>
                    <p class="text-gray-300 italic leading-relaxed mb-4"><?php echo $lang['acad_caso_sajur_testimonio']; ?></p>
                    <div class="flex items-center gap-1 text-gold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                </div>

                <!-- Liberté -->
                <div class="bg-gray-900 border border-gold/30 rounded-xl p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center text-black font-bold text-xl">
                            Liberté
                        </div>
                        <div>
                            <h4 class="font-bold text-gold text-lg"><?php echo $lang['acad_caso_liberte_nombre']; ?></h4>
                            <p class="text-sm text-gray-400"><?php echo $lang['acad_caso_liberte_tipo']; ?></p>
                        </div>
                    </div>
                    <p class="text-gray-300 italic leading-relaxed mb-4"><?php echo $lang['acad_caso_liberte_testimonio']; ?></p>
                    <div class="flex items-center gap-1 text-gold">
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
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['acad_antes_despues_titulo']; ?></h2>
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
                            <span class="text-gray-300"><strong class="text-gold"><?php echo $lang['acad_despues_emision']; ?></strong> <?php echo $lang['acad_despues_emision_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><strong class="text-gold"><?php echo $lang['acad_despues_ahorro']; ?></strong> <?php echo $lang['acad_despues_ahorro_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><strong class="text-gold"><?php echo $lang['acad_despues_validacion']; ?></strong></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><strong class="text-gold"><?php echo $lang['acad_despues_acceso']; ?></strong> <?php echo $lang['acad_despues_acceso_highlight']; ?></span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-300"><strong class="text-gold"><?php echo $lang['acad_despues_digital']; ?></strong> <?php echo $lang['acad_despues_digital_highlight']; ?></span>
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
    <section class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['acad_integraciones_titulo']; ?></h2>
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

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-gold/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 text-center">
                    <div class="w-16 h-16 bg-gold/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-white mb-2"><?php echo $lang['acad_integ_subdominios']; ?></h3>
                    <p class="text-gray-400 text-sm"><?php echo $lang['acad_integ_subdominios_desc']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gold"><?php echo $lang['acad_faq_titulo']; ?></h2>
                <p class="mt-2 text-lg text-gray-400"><?php echo $lang['acad_faq_subtitulo']; ?></p>
            </div>
            <div class="space-y-6">
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-gold mb-3"><?php echo $lang['acad_faq_cargo_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_cargo_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-gold mb-3"><?php echo $lang['acad_faq_validez_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_validez_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-gold mb-3"><?php echo $lang['acad_faq_diseno_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_diseno_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-gold mb-3"><?php echo $lang['acad_faq_acceso_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_acceso_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-gold mb-3"><?php echo $lang['acad_faq_validacion_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_validacion_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-gold mb-3"><?php echo $lang['acad_faq_retroactivos_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_retroactivos_resp']; ?></p>
                </div>

                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6">
                    <h3 class="text-lg font-bold text-gold mb-3"><?php echo $lang['acad_faq_limite_titulo']; ?></h3>
                    <p class="text-gray-300"><?php echo $lang['acad_faq_limite_resp']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section id="contacto" class="py-20 bg-gradient-to-br from-gray-900 via-gray-800 to-black border-y border-gold/20 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-72 h-72 bg-gold rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-metallic-green rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    <?php echo $lang['acad_cta_final_titulo']; ?>
                </h2>
                <p class="text-xl text-gray-300 mb-8 leading-relaxed">
                    <?php echo $lang['acad_cta_final_desc']; ?>
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    <a href="mailto:contacto@verumax.com" class="px-8 py-4 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/30 text-lg">
                        <?php echo $lang['acad_cta_final_demo']; ?>
                    </a>
                    <a href="tel:+5491112345678" class="px-8 py-4 bg-gray-800 border-2 border-gold text-gold font-semibold rounded-lg hover:bg-gray-700 transition-colors text-lg">
                        <?php echo $lang['acad_cta_final_contactar']; ?>
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-8 max-w-2xl mx-auto pt-8 border-t border-gold/20">
                    <div>
                        <p class="text-3xl font-bold text-gold"><?php echo $lang['acad_cta_final_implementacion']; ?></p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_cta_final_implementacion_desc']; ?></p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-metallic-green-light">90%</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_cta_final_menos_tiempo']; ?></p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold text-gold">∞</p>
                        <p class="text-sm text-gray-400 mt-1"><?php echo $lang['acad_cta_final_certificados']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest"></script>
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

        // Verificar tema guardado
        const savedTheme = localStorage.getItem('theme') || 'dark';
        if (savedTheme === 'light') {
            body.classList.remove('bg-black', 'text-white');
            body.classList.add('bg-white', 'text-gray-900');
            darkModeIcon.classList.remove('hidden');
            lightModeIcon.classList.add('hidden');

            // Ajustar otros elementos
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

        themeToggle.addEventListener('click', function() {
            const isLight = body.classList.contains('bg-white');

            if (isLight) {
                // Cambiar a oscuro
                body.classList.remove('bg-white', 'text-gray-900');
                body.classList.add('bg-black', 'text-white');
                darkModeIcon.classList.add('hidden');
                lightModeIcon.classList.remove('hidden');
                themeToggle.setAttribute('title', '<?php echo $lang['nav_tema_claro']; ?>');
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
                body.classList.remove('bg-black', 'text-white');
                body.classList.add('bg-white', 'text-gray-900');
                darkModeIcon.classList.remove('hidden');
                lightModeIcon.classList.add('hidden');
                themeToggle.setAttribute('title', '<?php echo $lang['nav_tema_oscuro']; ?>');
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

    <!-- Modal de Servicio en Desarrollo -->
    <?php include 'includes/modal-en-desarrollo.php'; ?>

</body>
</html>