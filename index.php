<?php
/**
 * Verumax - Landing Page Principal
 * "Del Código C a la Nube" - Filosofía de Programación
 * @version 1.0.0
 */

// Definir módulos de idioma ANTES de cargar lang_config
$lang_modules = ['common', 'land_verumax'];

require_once 'config.php';
require_once 'lang_config.php';
require_once 'maintenance_config.php';

// Idiomas agrupados por familia lingüística
$language_groups = [
    'Español' => [
        'es_AR' => 'Argentina',
        'es_BO' => 'Bolivia',
        'es_CL' => 'Chile',
        'es_EC' => 'Ecuador',
        'es_ES' => 'España',
        'es_PY' => 'Paraguay',
        'es_UY' => 'Uruguay'
    ],
    'Português' => [
        'pt_BR' => 'Brasil',
        'pt_PT' => 'Portugal'
    ],
    'Other' => [
        'en_US' => 'English',
        'ca_ES' => 'Català',
        'eu_ES' => 'Euskara',
        'el_GR' => 'Ελληνικά'
    ]
];

// Array plano para compatibilidad
$available_languages = [];
foreach ($language_groups as $group => $langs) {
    foreach ($langs as $code => $name) {
        $available_languages[$code] = $name;
    }
}

// NO activar maintenance para esta página de desarrollo
// check_maintenance_mode();

ob_start();
?>
<!DOCTYPE html>
<html lang="<?php echo substr($current_language, 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['meta_title'] ?? 'Verumax - Del Código C a la Nube'; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $lang['meta_description'] ?? ''; ?>">
    <meta name="keywords" content="<?php echo $lang['meta_keywords'] ?? ''; ?>">
    <meta name="author" content="Verumax">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="language" content="<?php echo substr($current_language, 0, 2); ?>">
    <meta name="revisit-after" content="7 days">
    <meta name="geo.region" content="AR">
    <meta name="geo.placename" content="Argentina">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://verumax.com/?lang=<?php echo $current_language; ?>">
    <meta property="og:title" content="<?php echo $lang['meta_og_title'] ?? 'Verumax'; ?>">
    <meta property="og:description" content="<?php echo $lang['meta_og_description'] ?? ''; ?>">
    <meta property="og:image" content="https://verumax.com/og-image-verumax.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Verumax - Del Código C a la Nube">
    <meta property="og:locale" content="<?php echo str_replace('_', '_', $current_language); ?>">
    <meta property="og:site_name" content="Verumax">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://verumax.com/?lang=<?php echo $current_language; ?>">
    <meta name="twitter:title" content="<?php echo $lang['meta_og_title'] ?? 'Verumax'; ?>">
    <meta name="twitter:description" content="<?php echo $lang['meta_og_description'] ?? ''; ?>">
    <meta name="twitter:image" content="https://verumax.com/og-image-verumax.png">
    <meta name="twitter:image:alt" content="Verumax - Desarrollo de Software a Medida">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo-verumax-escudo.png">

    <!-- Flag Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">

    <!-- Tailwind CSS -->
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
                        'terminal': {
                            green: '#4ade80',
                            dark: '#0d1117',
                            border: '#30363d'
                        }
                    },
                    fontFamily: {
                        'mono': ['JetBrains Mono', 'Fira Code', 'monospace'],
                        'display': ['Space Grotesk', 'sans-serif'],
                        'body': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --gold: #D4AF37;
            --gold-light: #F0D377;
            --terminal-green: #4ade80;
            --bg-dark: #050505;
            --bg-card: #0a0a0a;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: var(--gold) #1a1a1a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: #e5e5e5;
            overflow-x: hidden;
        }

        /* Terminal Animation */
        .terminal-window {
            background: linear-gradient(145deg, #0d1117 0%, #161b22 100%);
            border: 1px solid #30363d;
            border-radius: 12px;
            box-shadow:
                0 0 0 1px rgba(212, 175, 55, 0.1),
                0 25px 50px -12px rgba(0, 0, 0, 0.8),
                0 0 100px rgba(212, 175, 55, 0.05);
        }

        .terminal-header {
            background: linear-gradient(90deg, #1a1f26 0%, #21262d 100%);
            border-bottom: 1px solid #30363d;
            padding: 12px 16px;
            border-radius: 12px 12px 0 0;
        }

        .terminal-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .terminal-body {
            padding: 24px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            line-height: 1.8;
            min-height: 280px;
        }

        /* Typing animation */
        .typing-line {
            opacity: 0;
            animation: fadeInLine 0.3s ease forwards;
        }

        @keyframes fadeInLine {
            to { opacity: 1; }
        }

        .cursor {
            display: inline-block;
            width: 10px;
            height: 20px;
            background: var(--terminal-green);
            animation: blink 1s infinite;
            vertical-align: middle;
            margin-left: 2px;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        /* Code syntax colors */
        .code-keyword { color: #ff79c6; }
        .code-type { color: #8be9fd; }
        .code-string { color: #f1fa8c; }
        .code-function { color: #50fa7b; }
        .code-comment { color: #6272a4; }
        .code-number { color: #bd93f9; }
        .code-operator { color: #ff79c6; }

        /* Gold gradient text */
        .gold-text {
            background: linear-gradient(135deg, #D4AF37 0%, #F0D377 50%, #D4AF37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Animated background grid */
        .grid-bg {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(212, 175, 55, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(212, 175, 55, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            mask-image: radial-gradient(ellipse at center, black 30%, transparent 70%);
        }

        /* Floating code snippets */
        .floating-code {
            position: absolute;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: rgba(212, 175, 55, 0.15);
            white-space: pre;
            pointer-events: none;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }

        /* Solution cards */
        .solution-card {
            background: linear-gradient(145deg, #0a0a0a 0%, #111111 100%);
            border: 1px solid #1a1a1a;
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .solution-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .solution-card:hover::before {
            transform: scaleX(1);
        }

        .solution-card:hover {
            border-color: rgba(212, 175, 55, 0.3);
            transform: translateY(-8px);
            box-shadow:
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(212, 175, 55, 0.1);
        }

        /* Philosophy cards */
        .philosophy-card {
            background: #0a0a0a;
            border: 1px solid #1a1a1a;
            border-radius: 12px;
            padding: 32px;
            position: relative;
            transition: all 0.3s ease;
        }

        .philosophy-card:hover {
            border-color: rgba(212, 175, 55, 0.4);
            background: linear-gradient(145deg, #0a0a0a 0%, #0f0f0f 100%);
        }

        .philosophy-card .icon-box {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.15) 0%, rgba(212, 175, 55, 0.05) 100%);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        /* Anti-CMS banner */
        .anticms-banner {
            background: linear-gradient(135deg, #0a0a0a 0%, #111 50%, #0a0a0a 100%);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 24px;
            position: relative;
            overflow: hidden;
        }

        .anticms-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(212, 175, 55, 0.1), transparent 30%);
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            to { transform: rotate(360deg); }
        }

        /* Tech logos */
        .tech-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 16px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }

        .tech-item:hover {
            background: rgba(212, 175, 55, 0.05);
            border-color: rgba(212, 175, 55, 0.2);
            transform: scale(1.05);
        }

        /* Shine effect */
        .shine {
            position: relative;
            overflow: hidden;
        }

        .shine::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shine 3s ease-in-out infinite;
        }

        @keyframes shine {
            0% { left: -100%; }
            50%, 100% { left: 150%; }
        }

        /* CTA Button */
        .cta-button {
            background: linear-gradient(135deg, #D4AF37 0%, #B8941E 100%);
            color: #000;
            font-weight: 600;
            padding: 16px 32px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(212, 175, 55, 0.3);
        }

        .cta-button-secondary {
            background: transparent;
            border: 2px solid rgba(212, 175, 55, 0.5);
            color: #D4AF37;
            font-weight: 600;
            padding: 14px 30px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .cta-button-secondary:hover {
            background: rgba(212, 175, 55, 0.1);
            border-color: #D4AF37;
        }

        /* Scroll animations */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Proximamente badge */
        .badge-proximamente {
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            color: #9ca3af;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Language selector */
        #lang-chevron {
            transition: transform 0.2s ease;
        }

        #lang-menu {
            scrollbar-width: thin;
            scrollbar-color: var(--gold) #1a1a1a;
        }

        /* Solutions Slideshow */
        .slideshow-container {
            position: relative;
        }

        .slide {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }

        .slide.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-dot.active {
            background-color: #D4AF37;
            transform: scale(1.3);
        }

        .slide-banner {
            min-height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Floating buttons */
        #scrollToTop {
            transition: all 0.3s ease;
        }

        #scrollToTop.visible {
            opacity: 1;
            pointer-events: auto;
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.2s ease-out;
        }
    </style>
</head>
<body>
    <!-- Floating code background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="floating-code" style="top: 10%; left: 5%; animation-delay: 0s;">
#include &lt;stdio.h&gt;
int main() {
    printf("Hello");
    return 0;
}
        </div>
        <div class="floating-code" style="top: 30%; right: 8%; animation-delay: -5s;">
function deploy() {
    build();
    test();
    ship();
}
        </div>
        <div class="floating-code" style="bottom: 20%; left: 10%; animation-delay: -10s;">
class Verumax {
    public function create()
    {
        return $this;
    }
}
        </div>
        <div class="floating-code" style="top: 60%; right: 5%; animation-delay: -15s;">
SELECT * FROM dreams
WHERE limits = NULL;
        </div>
    </div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-black/80 backdrop-blur-xl border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="#" class="flex items-center gap-3">
                    <img src="assets/images/logo-verumax-escudo.png" alt="Verumax" class="h-10 w-10">
                    <img src="assets/images/logo-verumax-texto.png" alt="Verumax" class="h-7 hidden sm:block">
                </a>

                <!-- Navigation -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#filosofia" class="text-gray-400 hover:text-gold transition-colors text-sm font-medium"><?php echo $lang['nav_nosotros'] ?? 'Nosotros'; ?></a>
                    <a href="#soluciones" class="text-gray-400 hover:text-gold transition-colors text-sm font-medium"><?php echo $lang['nav_soluciones'] ?? 'Soluciones'; ?></a>
                    <a href="#tecnologias" class="text-gray-400 hover:text-gold transition-colors text-sm font-medium">Stack</a>
                    <a href="#contacto" class="text-gray-400 hover:text-gold transition-colors text-sm font-medium"><?php echo $lang['nav_contacto'] ?? 'Contacto'; ?></a>
                </nav>

                <div class="flex items-center gap-4">
                    <!-- Language Selector -->
                    <div class="relative" id="lang-selector">
                        <button onclick="toggleLangMenu()" class="text-gray-400 hover:text-gold transition-colors px-3 py-2 flex items-center gap-2 border border-white/10 rounded-lg hover:border-gold/50">
                            <?php echo get_flag_emoji($current_language); ?>
                            <i data-lucide="chevron-down" class="w-4 h-4" id="lang-chevron"></i>
                        </button>
                        <div id="lang-menu" class="absolute right-0 mt-2 w-80 bg-gray-900 border border-gold/30 rounded-lg shadow-xl opacity-0 invisible transition-all duration-200 z-50 max-h-96 overflow-y-auto p-3">
                            <?php foreach ($language_groups as $group_name => $langs): ?>
                            <div class="mb-3 last:mb-0">
                                <div class="text-xs text-gray-500 uppercase tracking-wider mb-2 px-1"><?php echo $group_name; ?></div>
                                <div class="grid grid-cols-2 gap-1">
                                    <?php foreach ($langs as $code => $name): ?>
                                    <a href="#" onclick="changeLanguage('<?php echo $code; ?>')" class="flex items-center gap-2 px-2 py-2 rounded hover:bg-gold/10 transition-colors <?php echo $current_language === $code ? 'bg-gold/20 text-gold border border-gold/30' : 'text-gray-300'; ?>">
                                        <?php echo get_flag_emoji($code); ?>
                                        <span class="text-sm truncate"><?php echo $name; ?></span>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- CTA -->
                    <a href="#contacto" class="cta-button text-sm hidden sm:inline-flex">
                        <?php echo $lang['hero_cta_secondary'] ?? 'Contactanos'; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center pt-20">
        <div class="grid-bg"></div>

        <div class="max-w-7xl mx-auto px-6 py-20 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Left: Text -->
                <div class="space-y-8">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-gold/10 border border-gold/30 rounded-full">
                        <span class="w-2 h-2 bg-terminal-green rounded-full animate-pulse"></span>
                        <span class="text-gold text-sm font-mono"><?php echo $lang['hero_badge'] ?? 'Desarrolladores de raíz'; ?></span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-5xl md:text-7xl font-bold font-display leading-tight">
                        <span class="text-white"><?php echo $lang['hero_title_line1'] ?? 'Del Código C'; ?></span>
                        <br>
                        <span class="gold-text"><?php echo $lang['hero_title_line2'] ?? 'a la Nube'; ?></span>
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-xl text-gray-400 leading-relaxed max-w-lg">
                        <?php echo $lang['hero_subtitle'] ?? 'Nacimos programando en <span class="text-gold font-bold">lenguaje C</span>. Conocemos el código desde adentro. <span class="text-gold font-bold">Programar no es nuestro trabajo</span>, es nuestra pasión.'; ?>
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-wrap gap-4 pt-4">
                        <a href="#soluciones" class="cta-button inline-flex items-center gap-2">
                            <?php echo $lang['hero_cta_primary'] ?? 'Ver Soluciones'; ?>
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                        <a href="#contacto" class="cta-button-secondary inline-flex items-center gap-2">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                            <?php echo $lang['hero_cta_secondary'] ?? 'Contactanos'; ?>
                        </a>
                    </div>
                </div>

                <!-- Right: Terminal -->
                <div class="terminal-window" id="terminal">
                    <div class="terminal-header flex items-center gap-3">
                        <div class="flex gap-2">
                            <div class="terminal-dot bg-red-500"></div>
                            <div class="terminal-dot bg-yellow-500"></div>
                            <div class="terminal-dot bg-green-500"></div>
                        </div>
                        <span class="text-gray-500 text-sm font-mono ml-4">verumax.c</span>
                    </div>
                    <div class="terminal-body" id="terminal-content">
                        <!-- Content will be typed by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll indicator -->
        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-gray-500">
            <span class="text-xs font-mono">scroll</span>
            <div class="w-6 h-10 border-2 border-gray-600 rounded-full flex justify-center pt-2">
                <div class="w-1 h-2 bg-gold rounded-full animate-bounce"></div>
            </div>
        </div>
    </section>

    <!-- Solutions Slideshow -->
    <section class="py-8 relative overflow-hidden" id="slideshow">
        <div class="slideshow-container max-w-5xl mx-auto px-6">

            <!-- Slide 1: Desarrollo Web / Anti-CMS -->
            <div class="slide active">
                <div class="slide-banner bg-gradient-to-br from-black via-gray-900 to-black border border-gold/20 rounded-3xl p-12 md:p-16 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-t from-gold/10 via-transparent to-transparent"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-gold/10 border border-gold/30 rounded-full mb-6">
                            <i data-lucide="code-2" class="w-5 h-5 text-gold"></i>
                            <span class="text-gold text-sm font-medium"><?php echo $lang['slide_desarrollo_badge'] ?? 'Desarrollo a Medida'; ?></span>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-bold text-white mb-4"><?php echo $lang['slide_desarrollo_title'] ?? '¿Su sitio web depende de un CMS?'; ?></h3>
                        <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
                            <?php echo $lang['slide_desarrollo_desc'] ?? 'WordPress, Joomla, Wix, Shopify... todos tienen límites. Desarrollamos sitios web, apps Android e iOS desde cero.'; ?>
                        </p>
                        <a href="https://fabricatum.verumax.com/?lang=<?php echo $current_language; ?>" class="inline-flex items-center gap-2 px-8 py-4 bg-gold hover:bg-gold-light text-black font-bold rounded-xl transition-all hover:scale-105">
                            <?php echo $lang['slide_desarrollo_cta'] ?? 'Descubra cómo'; ?> →
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 2: Certificatum - Color: Metallic Green #2E7D32 -->
            <div class="slide">
                <div class="slide-banner bg-gradient-to-br from-black via-green-950/30 to-black border border-green-600/20 rounded-3xl p-12 md:p-16 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-t from-green-600/10 via-transparent to-transparent"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-600/10 border border-green-600/30 rounded-full mb-6">
                            <i data-lucide="graduation-cap" class="w-5 h-5 text-green-500"></i>
                            <span class="text-green-500 text-sm font-medium">Certificatum</span>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-bold text-white mb-4"><?php echo $lang['slide_certificatum_title'] ?? 'Certificados digitales infalsificables'; ?></h3>
                        <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
                            <?php echo $lang['slide_certificatum_desc'] ?? 'Diplomas, analíticos y constancias con código QR verificable al instante.'; ?>
                        </p>
                        <a href="https://certificatum.verumax.com/?lang=<?php echo $current_language; ?>" class="inline-flex items-center gap-2 px-8 py-4 bg-green-700 hover:bg-green-600 text-white font-bold rounded-xl transition-all hover:scale-105">
                            <?php echo $lang['slide_certificatum_cta'] ?? 'Conocer Certificatum'; ?>
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 3: Credencialis -->
            <div class="slide">
                <div class="slide-banner bg-gradient-to-br from-black via-teal-950/30 to-black border border-teal-500/20 rounded-3xl p-12 md:p-16 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-t from-teal-500/10 via-transparent to-transparent"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-teal-500/10 border border-teal-500/30 rounded-full mb-6">
                            <i data-lucide="id-card" class="w-5 h-5 text-teal-400"></i>
                            <span class="text-teal-400 text-sm font-medium">Credencialis</span>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-bold text-white mb-4"><?php echo $lang['slide_credencialis_title'] ?? 'Credenciales digitales para su organización'; ?></h3>
                        <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
                            <?php echo $lang['slide_credencialis_desc'] ?? 'Carnets de socios/as, membresías y credenciales con QR verificable.'; ?>
                        </p>
                        <a href="https://credencialis.verumax.com/?lang=<?php echo $current_language; ?>" class="inline-flex items-center gap-2 px-8 py-4 bg-teal-600 hover:bg-teal-500 text-white font-bold rounded-xl transition-all hover:scale-105">
                            <?php echo $lang['slide_credencialis_cta'] ?? 'Conocer Credencialis'; ?>
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 4: Hosting -->
            <div class="slide">
                <div class="slide-banner bg-gradient-to-br from-black via-blue-950/30 to-black border border-blue-500/20 rounded-3xl p-12 md:p-16 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-500/10 via-transparent to-transparent"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500/10 border border-blue-500/30 rounded-full mb-6">
                            <i data-lucide="server" class="w-5 h-5 text-blue-400"></i>
                            <span class="text-blue-400 text-sm font-medium">Hosting</span>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-bold text-white mb-4"><?php echo $lang['slide_hosting_title'] ?? 'Hosting configurado por desarrolladores/as'; ?></h3>
                        <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
                            <?php echo $lang['slide_hosting_desc'] ?? 'Servidores optimizados para máximo rendimiento y seguridad.'; ?>
                        </p>
                        <span class="inline-flex items-center gap-2 px-8 py-4 bg-gray-700 text-gray-300 font-bold rounded-xl cursor-not-allowed">
                            <?php echo $lang['proximamente'] ?? 'Próximamente'; ?>
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Slide 5: Academicus - Color: Azul Zafiro #0F52BA -->
            <div class="slide">
                <div class="slide-banner bg-gradient-to-br from-black via-blue-950/40 to-black border border-blue-600/20 rounded-3xl p-12 md:p-16 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-t from-blue-600/10 via-transparent to-transparent"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600/10 border border-blue-500/30 rounded-full mb-6">
                            <i data-lucide="book-open" class="w-5 h-5 text-blue-400"></i>
                            <span class="text-blue-400 text-sm font-medium">Academicus</span>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-bold text-white mb-4"><?php echo $lang['slide_academicus_title'] ?? 'Su aula virtual, sin las limitaciones de Moodle'; ?></h3>
                        <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
                            <?php echo $lang['slide_academicus_desc'] ?? 'Plataforma de gestión académica 100% personalizada.'; ?>
                        </p>
                        <span class="inline-flex items-center gap-2 px-8 py-4 bg-gray-700 text-gray-300 font-bold rounded-xl cursor-not-allowed">
                            <?php echo $lang['proximamente'] ?? 'Próximamente'; ?>
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Slide 6: Emporium - Color: Rojo/Granate -->
            <div class="slide">
                <div class="slide-banner bg-gradient-to-br from-black via-red-950/30 to-black border border-red-600/20 rounded-3xl p-12 md:p-16 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-t from-red-600/10 via-transparent to-transparent"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-600/10 border border-red-500/30 rounded-full mb-6">
                            <i data-lucide="shopping-cart" class="w-5 h-5 text-red-400"></i>
                            <span class="text-red-400 text-sm font-medium">Emporium</span>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-bold text-white mb-4"><?php echo $lang['slide_emporium_title'] ?? 'Su tienda online profesional, sin comisiones ocultas'; ?></h3>
                        <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">
                            <?php echo $lang['slide_emporium_desc'] ?? 'E-commerce a medida, sin depender de Shopify o WooCommerce.'; ?>
                        </p>
                        <span class="inline-flex items-center gap-2 px-8 py-4 bg-gray-700 text-gray-300 font-bold rounded-xl cursor-not-allowed">
                            <?php echo $lang['proximamente'] ?? 'Próximamente'; ?>
                            <i data-lucide="clock" class="w-5 h-5"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Navigation dots + pause button -->
            <div class="flex justify-center items-center gap-3 mt-8">
                <button class="slide-dot active w-3 h-3 rounded-full bg-gold/30 hover:bg-gold transition-all" data-slide="0"></button>
                <button class="slide-dot w-3 h-3 rounded-full bg-gold/30 hover:bg-gold transition-all" data-slide="1"></button>
                <button class="slide-dot w-3 h-3 rounded-full bg-gold/30 hover:bg-gold transition-all" data-slide="2"></button>
                <button class="slide-dot w-3 h-3 rounded-full bg-gold/30 hover:bg-gold transition-all" data-slide="3"></button>
                <button class="slide-dot w-3 h-3 rounded-full bg-gold/30 hover:bg-gold transition-all" data-slide="4"></button>
                <button class="slide-dot w-3 h-3 rounded-full bg-gold/30 hover:bg-gold transition-all" data-slide="5"></button>
                <button id="pauseSlideshow" class="ml-4 w-8 h-8 rounded-full bg-white/10 hover:bg-gold/20 border border-white/20 hover:border-gold/50 flex items-center justify-center transition-all" title="Pausar/Reanudar">
                    <i data-lucide="pause" class="w-4 h-4 text-white" id="pauseIcon"></i>
                </button>
            </div>

            <!-- Arrow navigation -->
            <button id="prevSlide" class="absolute left-2 md:left-8 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/5 hover:bg-gold/20 border border-white/10 hover:border-gold/50 rounded-full flex items-center justify-center transition-all z-10">
                <i data-lucide="chevron-left" class="w-6 h-6 text-white"></i>
            </button>
            <button id="nextSlide" class="absolute right-2 md:right-8 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/5 hover:bg-gold/20 border border-white/10 hover:border-gold/50 rounded-full flex items-center justify-center transition-all z-10">
                <i data-lucide="chevron-right" class="w-6 h-6 text-white"></i>
            </button>
        </div>
    </section>

    <!-- Philosophy Section -->
    <section id="filosofia" class="py-32 relative">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Section header -->
            <div class="text-center mb-20 reveal">
                <h2 class="text-4xl md:text-5xl font-bold font-display mb-6">
                    <span class="gold-text"><?php echo $lang['filosofia_title'] ?? 'Por Qué Código Propio'; ?></span>
                </h2>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                    <?php echo $lang['filosofia_subtitle'] ?? 'Donde otros ponen plugins, nosotros escribimos código'; ?>
                </p>
            </div>

            <!-- Cards grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1: Sin Límites -->
                <div class="philosophy-card reveal" style="animation-delay: 0.1s;">
                    <div class="icon-box">
                        <i data-lucide="infinity" class="w-7 h-7 text-gold"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['filosofia_card1_title'] ?? 'Sin Límites'; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        <?php echo $lang['filosofia_card1_desc'] ?? 'Los CMS tienen techo. Nosotros no. Tu software crece con tu negocio.'; ?>
                    </p>
                </div>

                <!-- Card 2: Rendimiento -->
                <div class="philosophy-card reveal" style="animation-delay: 0.2s;">
                    <div class="icon-box">
                        <i data-lucide="zap" class="w-7 h-7 text-gold"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['filosofia_card2_title'] ?? 'Rendimiento Real'; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        <?php echo $lang['filosofia_card2_desc'] ?? 'Código optimizado desde cero. Sin plugins innecesarios.'; ?>
                    </p>
                </div>

                <!-- Card 3: Seguridad -->
                <div class="philosophy-card reveal" style="animation-delay: 0.3s;">
                    <div class="icon-box">
                        <i data-lucide="shield-check" class="w-7 h-7 text-gold"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['filosofia_card3_title'] ?? 'Seguridad Controlada'; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        <?php echo $lang['filosofia_card3_desc'] ?? 'Sabemos exactamente qué hace cada línea de código.'; ?>
                    </p>
                </div>

                <!-- Card 4: Propiedad -->
                <div class="philosophy-card reveal" style="animation-delay: 0.4s;">
                    <div class="icon-box">
                        <i data-lucide="key" class="w-7 h-7 text-gold"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3"><?php echo $lang['filosofia_card4_title'] ?? 'Propiedad Total'; ?></h3>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        <?php echo $lang['filosofia_card4_desc'] ?? 'El código es tuyo. Sin dependencias de licencias.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Solutions Section -->
    <section id="soluciones" class="py-32 relative">
        <div class="max-w-7xl mx-auto px-6">
            <!-- Section header -->
            <div class="text-center mb-20 reveal">
                <h2 class="text-4xl md:text-5xl font-bold font-display mb-6 text-white">
                    <?php echo $lang['soluciones_title'] ?? 'Nuestras Soluciones'; ?>
                </h2>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                    <?php echo $lang['soluciones_subtitle'] ?? 'Ecosistema de herramientas desarrolladas con código propio'; ?>
                </p>
            </div>

            <!-- Solutions grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Certificatum -->
                <a href="https://certificatum.verumax.com/?lang=<?php echo $current_language; ?>" class="solution-card p-8 block reveal">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500/20 to-purple-600/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="graduation-cap" class="w-7 h-7 text-purple-400"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['banner_certificatum_title'] ?? 'Certificatum'; ?></h3>
                    <p class="text-gold text-sm font-medium mb-3"><?php echo $lang['banner_certificatum_subtitle'] ?? 'Certificados Digitales'; ?></p>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">
                        <?php echo $lang['banner_certificatum_desc'] ?? 'Diplomas y analíticos verificables con QR infalsificable.'; ?>
                    </p>
                    <span class="text-gold text-sm font-medium inline-flex items-center gap-1 group-hover:gap-2 transition-all">
                        <?php echo $lang['banner_certificatum_cta'] ?? 'Conocer más'; ?>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                </a>

                <!-- Credencialis -->
                <a href="https://credencialis.verumax.com/?lang=<?php echo $current_language; ?>" class="solution-card p-8 block reveal" style="animation-delay: 0.1s;">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-teal-500/20 to-teal-600/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="id-card" class="w-7 h-7 text-teal-400"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['banner_credencialis_title'] ?? 'Credencialis'; ?></h3>
                    <p class="text-gold text-sm font-medium mb-3"><?php echo $lang['banner_credencialis_subtitle'] ?? 'Credenciales de Membresía'; ?></p>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">
                        <?php echo $lang['banner_credencialis_desc'] ?? 'Carnets digitales para tu organización.'; ?>
                    </p>
                    <span class="text-gold text-sm font-medium inline-flex items-center gap-1">
                        <?php echo $lang['banner_credencialis_cta'] ?? 'Conocer más'; ?>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                </a>

                <!-- Desarrollo Web -->
                <a href="https://fabricatum.verumax.com/?lang=<?php echo $current_language; ?>" class="solution-card p-8 block reveal" style="animation-delay: 0.2s;">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-gold/20 to-gold-dark/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="code-2" class="w-7 h-7 text-gold"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['banner_desarrollo_title'] ?? 'Desarrollo Web'; ?></h3>
                    <p class="text-gold text-sm font-medium mb-3"><?php echo $lang['banner_desarrollo_subtitle'] ?? 'Sitios Sin Límites'; ?></p>
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">
                        <?php echo $lang['banner_desarrollo_desc'] ?? '¿Cansado de WordPress? Sitios web a medida.'; ?>
                    </p>
                    <span class="text-gold text-sm font-medium inline-flex items-center gap-1">
                        <?php echo $lang['banner_desarrollo_cta'] ?? 'Ver servicios'; ?>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                </a>

                <!-- Hosting -->
                <div class="solution-card p-8 reveal opacity-70" style="animation-delay: 0.3s;">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500/20 to-blue-600/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="server" class="w-7 h-7 text-blue-400"></i>
                        </div>
                        <span class="badge-proximamente"><?php echo $lang['proximamente'] ?? 'Próximamente'; ?></span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['banner_hosting_title'] ?? 'Hosting'; ?></h3>
                    <p class="text-gold text-sm font-medium mb-3"><?php echo $lang['banner_hosting_subtitle'] ?? 'Servidores Optimizados'; ?></p>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        <?php echo $lang['banner_hosting_desc'] ?? 'Hosting configurado por desarrolladores.'; ?>
                    </p>
                </div>

                <!-- Academicus -->
                <div class="solution-card p-8 reveal opacity-70" style="animation-delay: 0.4s;">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-500/20 to-orange-600/10 rounded-xl flex items-center justify-center">
                            <i data-lucide="book-open" class="w-7 h-7 text-orange-400"></i>
                        </div>
                        <span class="badge-proximamente"><?php echo $lang['proximamente'] ?? 'Próximamente'; ?></span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2"><?php echo $lang['banner_edumax_title'] ?? 'Academicus'; ?></h3>
                    <p class="text-gold text-sm font-medium mb-3"><?php echo $lang['banner_edumax_subtitle'] ?? 'Plataformas Educativas'; ?></p>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        <?php echo $lang['banner_edumax_desc'] ?? 'Tu aula virtual a medida. LMS personalizado.'; ?>
                    </p>
                </div>

                <!-- More coming -->
                <div class="solution-card p-8 flex items-center justify-center reveal border-dashed" style="animation-delay: 0.5s;">
                    <div class="text-center">
                        <div class="w-14 h-14 bg-white/5 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="plus" class="w-7 h-7 text-gray-500"></i>
                        </div>
                        <p class="text-gray-500 text-sm">Más soluciones en desarrollo</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technologies Section -->
    <section id="tecnologias" class="py-32 relative bg-gradient-to-b from-transparent via-gold/5 to-transparent">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16 reveal">
                <h2 class="text-4xl md:text-5xl font-bold font-display mb-6 text-white">
                    <?php echo $lang['tech_title'] ?? 'Tecnologías que Dominamos'; ?>
                </h2>
                <p class="text-xl text-gray-400">
                    <?php echo $lang['tech_subtitle'] ?? 'Más de 15 años de experiencia en desarrollo'; ?>
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 reveal">
                <!-- Languages -->
                <div class="tech-item">
                    <span class="text-3xl">🔧</span>
                    <span class="text-sm text-gray-300 font-mono">C</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">🐘</span>
                    <span class="text-sm text-gray-300 font-mono">PHP</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">🐍</span>
                    <span class="text-sm text-gray-300 font-mono">Python</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">⚡</span>
                    <span class="text-sm text-gray-300 font-mono">JavaScript</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">💎</span>
                    <span class="text-sm text-gray-300 font-mono">TypeScript</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">🦫</span>
                    <span class="text-sm text-gray-300 font-mono">Go</span>
                </div>

                <!-- Frameworks -->
                <div class="tech-item">
                    <span class="text-3xl">🎨</span>
                    <span class="text-sm text-gray-300 font-mono">Laravel</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">⚛️</span>
                    <span class="text-sm text-gray-300 font-mono">React</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">💚</span>
                    <span class="text-sm text-gray-300 font-mono">Vue.js</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">🟢</span>
                    <span class="text-sm text-gray-300 font-mono">Node.js</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">🌬️</span>
                    <span class="text-sm text-gray-300 font-mono">Tailwind</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">▲</span>
                    <span class="text-sm text-gray-300 font-mono">Next.js</span>
                </div>

                <!-- Databases & Cloud -->
                <div class="tech-item">
                    <span class="text-3xl">🐬</span>
                    <span class="text-sm text-gray-300 font-mono">MySQL</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">🐘</span>
                    <span class="text-sm text-gray-300 font-mono">PostgreSQL</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">🍃</span>
                    <span class="text-sm text-gray-300 font-mono">MongoDB</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">☁️</span>
                    <span class="text-sm text-gray-300 font-mono">AWS</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">🌊</span>
                    <span class="text-sm text-gray-300 font-mono">DigitalOcean</span>
                </div>
                <div class="tech-item">
                    <span class="text-3xl">🐳</span>
                    <span class="text-sm text-gray-300 font-mono">Docker</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contacto" class="py-32 relative">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-16 reveal">
                <h2 class="text-4xl md:text-5xl font-bold font-display mb-6">
                    <span class="gold-text"><?php echo $lang['contacto_title'] ?? 'Hablemos de tu Proyecto'; ?></span>
                </h2>
                <p class="text-xl text-gray-400">
                    <?php echo $lang['contacto_subtitle'] ?? 'Contanos tu idea y te asesoramos sin compromiso'; ?>
                </p>
            </div>

            <div class="bg-gradient-to-b from-white/5 to-transparent border border-white/10 rounded-2xl p-8 md:p-12 reveal">
                <form id="contactForm" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2"><?php echo $lang['contacto_nombre'] ?? 'Nombre'; ?> *</label>
                            <input type="text" name="nombre" required class="w-full bg-black/50 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-gold focus:outline-none transition-colors" placeholder="Tu nombre">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2"><?php echo $lang['contacto_email'] ?? 'Email'; ?> *</label>
                            <input type="email" name="email" required class="w-full bg-black/50 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-gold focus:outline-none transition-colors" placeholder="tu@email.com">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Empresa / Organización *</label>
                        <input type="text" name="organizacion" required class="w-full bg-black/50 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-gold focus:outline-none transition-colors" placeholder="Nombre de su empresa u organización">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">¿Qué te interesa?</label>
                        <select name="tipo" class="w-full bg-black/50 border border-white/10 rounded-lg px-4 py-3 text-white focus:border-gold focus:outline-none transition-colors">
                            <option value="desarrollo">Desarrollo Web / Apps</option>
                            <option value="certificatum">Certificatum - Certificados Digitales</option>
                            <option value="credencialis">Credencialis - Credenciales de Membresía</option>
                            <option value="academicus">Academicus - Plataforma Educativa</option>
                            <option value="emporium">Emporium - Tienda Online</option>
                            <option value="hosting">Hosting</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2"><?php echo $lang['contacto_mensaje'] ?? 'Mensaje'; ?></label>
                        <textarea name="mensaje" rows="4" class="w-full bg-black/50 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-gold focus:outline-none transition-colors resize-none" placeholder="Cuéntenos sobre su proyecto..."></textarea>
                    </div>
                    <div id="formMessage" class="hidden p-4 rounded-lg text-center"></div>
                    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                        <button type="submit" id="submitBtn" class="cta-button w-full sm:w-auto inline-flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-5 h-5" id="sendIcon"></i>
                            <span id="btnText"><?php echo $lang['contacto_btn'] ?? 'Enviar Mensaje'; ?></span>
                        </button>
                        <a href="https://wa.me/5491123456789" target="_blank" class="text-gray-400 hover:text-terminal-green transition-colors inline-flex items-center gap-2 text-sm">
                            <i data-lucide="message-circle" class="w-5 h-5"></i>
                            <?php echo $lang['contacto_whatsapp'] ?? 'O escribinos por WhatsApp'; ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer (incluye botones Veritas y Scroll-to-top) -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Language selector functions
        function toggleLangMenu() {
            const menu = document.getElementById('lang-menu');
            const chevron = document.getElementById('lang-chevron');
            if (menu.classList.contains('invisible')) {
                menu.classList.remove('opacity-0', 'invisible');
                menu.classList.add('opacity-100', 'visible');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                menu.classList.add('opacity-0', 'invisible');
                menu.classList.remove('opacity-100', 'visible');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        function changeLanguage(langCode) {
            // Preservar el hash actual al cambiar de idioma
            const currentHash = window.location.hash;
            const newUrl = window.location.pathname + '?lang=' + langCode + currentHash;
            window.location.href = newUrl;
        }

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(e) {
            const langSelector = document.getElementById('lang-selector');
            if (langSelector && !langSelector.contains(e.target)) {
                const menu = document.getElementById('lang-menu');
                const chevron = document.getElementById('lang-chevron');
                if (menu) {
                    menu.classList.add('opacity-0', 'invisible');
                    menu.classList.remove('opacity-100', 'visible');
                }
                if (chevron) {
                    chevron.style.transform = 'rotate(0deg)';
                }
            }
        });

        // Terminal typing animation
        const terminalContent = document.getElementById('terminal-content');
        const codeLines = [
            { text: '<span class="code-comment">/* Del código C a la nube */</span>', delay: 0 },
            { text: '<span class="code-keyword">#include</span> <span class="code-string">&lt;verumax.h&gt;</span>', delay: 300 },
            { text: '', delay: 600 },
            { text: '<span class="code-type">int</span> <span class="code-function">main</span>() {', delay: 900 },
            { text: '    <span class="code-type">Software</span> *proyecto = <span class="code-function">crear</span>();', delay: 1200 },
            { text: '    ', delay: 1500 },
            { text: '    proyecto<span class="code-operator">-></span>limites = <span class="code-number">NULL</span>;', delay: 1800 },
            { text: '    proyecto<span class="code-operator">-></span>pasion = <span class="code-number">∞</span>;', delay: 2100 },
            { text: '    ', delay: 2400 },
            { text: '    <span class="code-keyword">while</span> (<span class="code-number">true</span>) {', delay: 2700 },
            { text: '        <span class="code-function">programar</span>();', delay: 3000 },
            { text: '        <span class="code-function">innovar</span>();', delay: 3300 },
            { text: '        <span class="code-function">entregar</span>();', delay: 3600 },
            { text: '    }', delay: 3900 },
            { text: '    ', delay: 4200 },
            { text: '    <span class="code-keyword">return</span> <span class="code-number">EXITO</span>;', delay: 4500 },
            { text: '}<span class="cursor"></span>', delay: 4800 }
        ];

        function typeTerminal() {
            codeLines.forEach((line, index) => {
                setTimeout(() => {
                    const lineElement = document.createElement('div');
                    lineElement.className = 'typing-line';
                    lineElement.innerHTML = line.text || '&nbsp;';
                    lineElement.style.animationDelay = `${index * 0.05}s`;
                    terminalContent.appendChild(lineElement);
                }, line.delay);
            });
        }

        // Start typing when terminal is visible
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    typeTerminal();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        observer.observe(document.getElementById('terminal'));

        // Reveal animations on scroll
        const revealElements = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        revealElements.forEach(el => revealObserver.observe(el));

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Scroll a la sección si hay hash en la URL (después de cambiar idioma)
        window.addEventListener('load', function() {
            if (window.location.hash) {
                const target = document.querySelector(window.location.hash);
                if (target) {
                    setTimeout(() => {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                }
            }
        });

        // Slideshow
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slide-dot');
        const prevBtn = document.getElementById('prevSlide');
        const nextBtn = document.getElementById('nextSlide');
        const pauseBtn = document.getElementById('pauseSlideshow');
        const pauseIcon = document.getElementById('pauseIcon');
        let currentSlide = 0;
        let slideInterval;
        let isPaused = false;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            currentSlide = index;
            if (currentSlide >= slides.length) currentSlide = 0;
            if (currentSlide < 0) currentSlide = slides.length - 1;

            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function prevSlide() {
            showSlide(currentSlide - 1);
        }

        function startSlideshow() {
            if (!isPaused) {
                slideInterval = setInterval(nextSlide, 6000); // Cambia cada 6 segundos
            }
        }

        function stopSlideshow() {
            clearInterval(slideInterval);
        }

        function resetSlideshow() {
            stopSlideshow();
            startSlideshow();
        }

        function togglePause() {
            isPaused = !isPaused;
            if (isPaused) {
                stopSlideshow();
                pauseIcon.setAttribute('data-lucide', 'play');
                pauseBtn.title = 'Reanudar';
            } else {
                startSlideshow();
                pauseIcon.setAttribute('data-lucide', 'pause');
                pauseBtn.title = 'Pausar';
            }
            lucide.createIcons();
        }

        // Event listeners
        prevBtn.addEventListener('click', () => { prevSlide(); resetSlideshow(); });
        nextBtn.addEventListener('click', () => { nextSlide(); resetSlideshow(); });
        pauseBtn.addEventListener('click', togglePause);

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => { showSlide(index); resetSlideshow(); });
        });

        // Iniciar slideshow automático
        startSlideshow();

        // Formulario de contacto
        const contactForm = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const formMessage = document.getElementById('formMessage');

        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Deshabilitar botón y mostrar loading
            submitBtn.disabled = true;
            btnText.textContent = 'Enviando...';

            const formData = new FormData(contactForm);
            const data = {
                nombre: formData.get('nombre'),
                email: formData.get('email'),
                organizacion: formData.get('organizacion'),
                tipo: formData.get('tipo'),
                mensaje: formData.get('mensaje'),
                producto: 'VERUMax Landing',
                socios: 'No especificado',
                lang: '<?php echo $current_language; ?>'
            };

            try {
                const response = await fetch('api/contact.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                formMessage.classList.remove('hidden');
                if (result.success) {
                    formMessage.className = 'p-4 rounded-lg text-center bg-green-500/20 border border-green-500/30 text-green-400';
                    formMessage.textContent = result.message || '¡Gracias! Nos comunicaremos pronto.';
                    contactForm.reset();
                } else {
                    formMessage.className = 'p-4 rounded-lg text-center bg-red-500/20 border border-red-500/30 text-red-400';
                    formMessage.textContent = result.error || 'Error al enviar. Intentá de nuevo.';
                }
            } catch (error) {
                formMessage.classList.remove('hidden');
                formMessage.className = 'p-4 rounded-lg text-center bg-red-500/20 border border-red-500/30 text-red-400';
                formMessage.textContent = 'Error de conexión. Intentá de nuevo.';
            }

            // Restaurar botón
            submitBtn.disabled = false;
            btnText.textContent = '<?php echo $lang['contacto_btn'] ?? 'Enviar Mensaje'; ?>';

            // Ocultar mensaje después de 5 segundos
            setTimeout(() => {
                formMessage.classList.add('hidden');
            }, 5000);
        });
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>
