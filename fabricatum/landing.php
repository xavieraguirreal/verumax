<?php
/**
 * Landing Page - Desarrollo de Software
 * Estilo: Cyberpunk / Tech
 * @version 1.0.0
 */

// Configuración
$base_path = dirname(__DIR__);
require_once $base_path . '/config.php';

// Sistema de idiomas
$lang_config = require $base_path . '/lang/lang_config.php';
$current_lang = $_GET['lang'] ?? $_COOKIE['verumax_lang'] ?? 'es_AR';
if (!isset($lang_config['languages'][$current_lang])) {
    $current_lang = 'es_AR';
}

// Cargar traducciones
$lang_modules = ['land_desarrollo'];
$t = [];
foreach ($lang_modules as $module) {
    $lang_file = $base_path . "/lang/{$current_lang}/{$module}.php";
    if (file_exists($lang_file)) {
        $t = array_merge($t, require $lang_file);
    }
}

// Helper de traducción
function __($key, $default = '') {
    global $t;
    return $t[$key] ?? $default;
}

// Obtener idiomas disponibles
$available_languages = $lang_config['languages'];
?>
<!DOCTYPE html>
<html lang="<?= substr($current_lang, 0, 2) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('meta_title', 'Desarrollo de Software | Verumax') ?></title>
    <meta name="description" content="<?= __('meta_description') ?>">
    <meta name="keywords" content="<?= __('meta_keywords') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= __('meta_og_title') ?>">
    <meta property="og:description" content="<?= __('meta_og_description') ?>">
    <meta property="og:type" content="website">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cyber-black': '#0a0a0f',
                        'cyber-dark': '#12121a',
                        'cyber-gray': '#1a1a25',
                        'neon-cyan': '#00fff2',
                        'neon-magenta': '#ff00ff',
                        'neon-blue': '#00a0ff',
                        'neon-green': '#00ff88',
                        'neon-purple': '#8b5cf6',
                    },
                    fontFamily: {
                        'mono': ['JetBrains Mono', 'Fira Code', 'monospace'],
                        'display': ['Orbitron', 'sans-serif'],
                        'body': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
            color: #e5e5e5;
        }

        /* Neon glow effects */
        .neon-text-cyan {
            color: #00fff2;
            text-shadow: 0 0 10px #00fff2, 0 0 20px #00fff2, 0 0 40px #00fff2;
        }

        .neon-text-magenta {
            color: #ff00ff;
            text-shadow: 0 0 10px #ff00ff, 0 0 20px #ff00ff, 0 0 40px #ff00ff;
        }

        .neon-border-cyan {
            border-color: #00fff2;
            box-shadow: 0 0 10px #00fff244, inset 0 0 10px #00fff211;
        }

        .neon-border-magenta {
            border-color: #ff00ff;
            box-shadow: 0 0 10px #ff00ff44, inset 0 0 10px #ff00ff11;
        }

        /* Grid background */
        .grid-bg {
            background-image:
                linear-gradient(rgba(0, 255, 242, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 242, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        /* Scanlines */
        .scanlines::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0, 0, 0, 0.1) 2px,
                rgba(0, 0, 0, 0.1) 4px
            );
            pointer-events: none;
            z-index: 1;
        }

        /* Glitch effect */
        @keyframes glitch {
            0% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
            100% { transform: translate(0); }
        }

        .glitch:hover {
            animation: glitch 0.3s infinite;
        }

        /* Typing cursor */
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        .cursor {
            animation: blink 1s infinite;
        }

        /* Pulse glow */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px #00fff244; }
            50% { box-shadow: 0 0 40px #00fff288, 0 0 60px #00fff244; }
        }

        .pulse-glow {
            animation: pulse-glow 2s infinite;
        }

        /* Float animation */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .float {
            animation: float 3s ease-in-out infinite;
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(90deg, #00fff2, #ff00ff, #00fff2);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient-shift 3s linear infinite;
        }

        @keyframes gradient-shift {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        /* Circuit lines decoration */
        .circuit-line {
            position: absolute;
            background: linear-gradient(90deg, transparent, #00fff2, transparent);
            height: 1px;
            opacity: 0.3;
        }

        /* Card hover effect */
        .cyber-card {
            background: linear-gradient(135deg, #12121a 0%, #1a1a25 100%);
            border: 1px solid #2a2a35;
            transition: all 0.3s ease;
        }

        .cyber-card:hover {
            border-color: #00fff2;
            box-shadow: 0 0 30px rgba(0, 255, 242, 0.1);
            transform: translateY(-5px);
        }

        /* Button styles */
        .btn-cyber {
            position: relative;
            background: transparent;
            border: 2px solid #00fff2;
            color: #00fff2;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-cyber::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, #00fff233, transparent);
            transition: left 0.5s ease;
        }

        .btn-cyber:hover::before {
            left: 100%;
        }

        .btn-cyber:hover {
            background: #00fff2;
            color: #0a0a0f;
            box-shadow: 0 0 30px #00fff244;
        }

        .btn-cyber-fill {
            background: #00fff2;
            color: #0a0a0f;
            border: none;
        }

        .btn-cyber-fill:hover {
            background: #00fff2;
            box-shadow: 0 0 40px #00fff288;
        }

        /* Table styles */
        .cyber-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .cyber-table th {
            background: #1a1a25;
            border-bottom: 2px solid #00fff2;
        }

        .cyber-table td {
            border-bottom: 1px solid #2a2a35;
        }

        .cyber-table tr:hover td {
            background: #12121a;
        }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Code block */
        .code-block {
            background: #0d0d12;
            border: 1px solid #2a2a35;
            border-left: 3px solid #00fff2;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Process timeline */
        .timeline-line {
            background: linear-gradient(180deg, #00fff2, #ff00ff);
        }

        /* Floating particles effect (CSS only) */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #00fff2;
            border-radius: 50%;
            opacity: 0.3;
        }

        /* Navigation */
        nav {
            backdrop-filter: blur(10px);
            background: rgba(10, 10, 15, 0.8);
        }

        /* Language selector */
        .lang-dropdown {
            background: #12121a;
            border: 1px solid #2a2a35;
        }

        .lang-dropdown:hover {
            border-color: #00fff2;
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Service icon glow */
        .service-icon {
            transition: all 0.3s ease;
        }

        .cyber-card:hover .service-icon {
            filter: drop-shadow(0 0 10px currentColor);
        }
    </style>
</head>
<body class="bg-cyber-black min-h-screen">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-gray-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="/landing.php?lang=<?= $current_lang ?>" class="flex items-center gap-2 group">
                    <span class="text-xl font-display font-bold text-white group-hover:text-neon-cyan transition-colors">VERU</span>
                    <span class="text-xl font-display font-bold text-neon-cyan">MAX</span>
                    <span class="text-xs text-gray-500 ml-2 font-mono">/dev</span>
                </a>

                <!-- Nav Links (Desktop) -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#servicios" class="text-gray-400 hover:text-neon-cyan transition-colors font-mono text-sm">
                        <?= __('nav_servicios', 'Servicios') ?>
                    </a>
                    <a href="#proceso" class="text-gray-400 hover:text-neon-cyan transition-colors font-mono text-sm">
                        <?= __('nav_proceso', 'Proceso') ?>
                    </a>
                    <a href="#tech" class="text-gray-400 hover:text-neon-cyan transition-colors font-mono text-sm">
                        <?= __('nav_tech', 'Tech') ?>
                    </a>
                    <a href="#contacto" class="text-gray-400 hover:text-neon-cyan transition-colors font-mono text-sm">
                        <?= __('nav_contacto', 'Contacto') ?>
                    </a>
                </div>

                <!-- Language Selector + CTA -->
                <div class="flex items-center gap-4">
                    <!-- Language -->
                    <div class="relative">
                        <select id="langSelector" class="lang-dropdown appearance-none px-3 py-1.5 pr-8 rounded text-sm text-gray-300 cursor-pointer focus:outline-none focus:border-neon-cyan">
                            <?php foreach ($available_languages as $code => $lang): ?>
                            <option value="<?= $code ?>" <?= $code === $current_lang ? 'selected' : '' ?>>
                                <?= $lang['flag'] ?> <?= strtoupper(substr($code, 0, 2)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                    </div>

                    <!-- CTA -->
                    <a href="#contacto" class="hidden sm:block btn-cyber px-4 py-2 rounded font-mono text-sm">
                        <?= __('hero_cta_primary', 'Presupuesto') ?>
                    </a>

                    <!-- Mobile Menu -->
                    <button id="mobileMenuBtn" class="md:hidden p-2 text-gray-400 hover:text-neon-cyan">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Panel -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-gray-800/50 bg-cyber-dark/95">
            <div class="px-4 py-4 space-y-3">
                <a href="#servicios" class="block text-gray-300 hover:text-neon-cyan py-2 font-mono text-sm"><?= __('nav_servicios', 'Servicios') ?></a>
                <a href="#proceso" class="block text-gray-300 hover:text-neon-cyan py-2 font-mono text-sm"><?= __('nav_proceso', 'Proceso') ?></a>
                <a href="#tech" class="block text-gray-300 hover:text-neon-cyan py-2 font-mono text-sm"><?= __('nav_tech', 'Tech') ?></a>
                <a href="#contacto" class="block text-gray-300 hover:text-neon-cyan py-2 font-mono text-sm"><?= __('nav_contacto', 'Contacto') ?></a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center pt-16 overflow-hidden grid-bg">
        <!-- Background effects -->
        <div class="absolute inset-0 overflow-hidden">
            <!-- Gradient orbs -->
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-neon-cyan/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-neon-magenta/10 rounded-full blur-[100px]"></div>

            <!-- Circuit lines -->
            <div class="circuit-line w-1/3 top-1/4 left-0"></div>
            <div class="circuit-line w-1/4 top-1/2 right-0"></div>
            <div class="circuit-line w-1/2 bottom-1/3 left-1/4"></div>
        </div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-block mb-8">
                <span class="font-mono text-neon-cyan text-sm px-4 py-2 border border-neon-cyan/30 rounded-full bg-neon-cyan/5">
                    <?= __('hero_badge', '< Código Propio />') ?>
                </span>
            </div>

            <!-- Title with glitch effect -->
            <h1 class="font-display text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-black mb-6 tracking-tight">
                <span class="text-white"><?= __('hero_title_line1', '¿Tu Sitio Web') ?></span>
                <br>
                <span class="gradient-text"><?= __('hero_title_line2', 'Tiene Techo?') ?></span>
            </h1>

            <!-- Subtitle -->
            <p class="text-lg sm:text-xl text-gray-400 max-w-3xl mx-auto mb-10 leading-relaxed">
                <?= __('hero_subtitle', 'WordPress, Joomla, Wix, Shopify... <span class="text-red-500">todos tienen límites</span>. Nosotros programamos desde cero. <span class="text-neon-cyan">Sin techo. Sin restricciones.</span>') ?>
            </p>

            <!-- CTAs -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-16">
                <a href="#contacto" class="btn-cyber-fill px-8 py-4 rounded-lg font-display font-semibold text-lg pulse-glow">
                    <?= __('hero_cta_primary', 'Solicitar Presupuesto') ?>
                </a>
                <a href="#servicios" class="btn-cyber px-8 py-4 rounded-lg font-display font-semibold text-lg">
                    <?= __('hero_cta_secondary', 'Ver Servicios') ?>
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-8 max-w-2xl mx-auto">
                <div class="text-center">
                    <div class="font-display text-4xl sm:text-5xl font-bold neon-text-cyan mb-2">
                        <?= __('hero_stat1_value', '+15') ?>
                    </div>
                    <div class="text-gray-500 text-sm font-mono">
                        <?= __('hero_stat1_label', 'Años de Experiencia') ?>
                    </div>
                </div>
                <div class="text-center">
                    <div class="font-display text-4xl sm:text-5xl font-bold neon-text-magenta mb-2">
                        <?= __('hero_stat2_value', '100%') ?>
                    </div>
                    <div class="text-gray-500 text-sm font-mono">
                        <?= __('hero_stat2_label', 'Código Propio') ?>
                    </div>
                </div>
                <div class="text-center">
                    <div class="font-display text-4xl sm:text-5xl font-bold text-white mb-2">
                        <?= __('hero_stat3_value', '0') ?>
                    </div>
                    <div class="text-gray-500 text-sm font-mono">
                        <?= __('hero_stat3_label', 'Dependencias CMS') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll indicator -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 float">
            <div class="w-6 h-10 border-2 border-neon-cyan/50 rounded-full flex justify-center pt-2">
                <div class="w-1 h-3 bg-neon-cyan rounded-full animate-bounce"></div>
            </div>
        </div>
    </section>

    <!-- Problema Section -->
    <section class="py-24 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-4">
                    <?= __('problema_title', 'El Problema con los CMS') ?>
                </h2>
                <p class="text-gray-400 text-lg font-mono">
                    <?= __('problema_subtitle', 'Lo que las agencias no te cuentan') ?>
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $problemas = [
                    ['icon' => 'lock', 'color' => 'text-red-500'],
                    ['icon' => 'snail', 'color' => 'text-orange-500'],
                    ['icon' => 'shield-alert', 'color' => 'text-yellow-500'],
                    ['icon' => 'wallet', 'color' => 'text-pink-500'],
                ];
                for ($i = 1; $i <= 4; $i++):
                    $prob = $problemas[$i-1];
                ?>
                <div class="cyber-card rounded-xl p-6 reveal" style="animation-delay: <?= ($i-1) * 0.1 ?>s">
                    <div class="text-4xl mb-4"><?= __("problema_card{$i}_icon", '⚠️') ?></div>
                    <h3 class="font-display text-lg font-semibold text-white mb-3">
                        <?= __("problema_card{$i}_title", "Problema $i") ?>
                    </h3>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        <?= __("problema_card{$i}_desc", '') ?>
                    </p>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Solución Section -->
    <section class="py-24 relative bg-cyber-dark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-4">
                    <?= __('solucion_title', 'Nuestra Solución') ?>
                </h2>
                <p class="text-gray-400 text-lg font-mono">
                    <?= __('solucion_subtitle', 'Código escrito para vos, no para todos') ?>
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $iconos_solucion = ['puzzle', 'zap', 'shield-check', 'key'];
                for ($i = 1; $i <= 4; $i++):
                ?>
                <div class="cyber-card rounded-xl p-6 reveal neon-border-cyan border-2 border-transparent hover:border-neon-cyan" style="animation-delay: <?= ($i-1) * 0.1 ?>s">
                    <div class="w-12 h-12 rounded-lg bg-neon-cyan/10 flex items-center justify-center mb-4">
                        <i data-lucide="<?= $iconos_solucion[$i-1] ?>" class="w-6 h-6 text-neon-cyan service-icon"></i>
                    </div>
                    <h3 class="font-display text-lg font-semibold text-white mb-3">
                        <?= __("solucion_card{$i}_title", "Solución $i") ?>
                    </h3>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        <?= __("solucion_card{$i}_desc", '') ?>
                    </p>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Servicios Section -->
    <section id="servicios" class="py-24 relative grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-4">
                    <?= __('servicios_title', 'Servicios') ?>
                </h2>
                <p class="text-gray-400 text-lg font-mono">
                    <?= __('servicios_subtitle', 'Desarrollo integral para tu negocio digital') ?>
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $servicios = [
                    ['icon' => 'globe', 'color' => 'neon-cyan'],
                    ['icon' => 'shopping-cart', 'color' => 'neon-magenta'],
                    ['icon' => 'layout-dashboard', 'color' => 'neon-blue'],
                    ['icon' => 'smartphone', 'color' => 'neon-green'],
                    ['icon' => 'graduation-cap', 'color' => 'neon-purple'],
                    ['icon' => 'plug', 'color' => 'neon-cyan'],
                ];
                for ($i = 1; $i <= 6; $i++):
                    $serv = $servicios[$i-1];
                    $features = explode(',', __("servicio{$i}_features", ''));
                ?>
                <div class="cyber-card rounded-xl p-8 reveal group" style="animation-delay: <?= ($i-1) * 0.1 ?>s">
                    <!-- Icon -->
                    <div class="w-14 h-14 rounded-xl bg-<?= $serv['color'] ?>/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <i data-lucide="<?= $serv['icon'] ?>" class="w-7 h-7 text-<?= $serv['color'] ?> service-icon"></i>
                    </div>

                    <!-- Title -->
                    <h3 class="font-display text-xl font-bold text-white mb-1">
                        <?= __("servicio{$i}_title", "Servicio $i") ?>
                    </h3>
                    <p class="text-<?= $serv['color'] ?> text-sm font-mono mb-4">
                        <?= __("servicio{$i}_subtitle", '') ?>
                    </p>

                    <!-- Description -->
                    <p class="text-gray-400 text-sm leading-relaxed mb-6">
                        <?= __("servicio{$i}_desc", '') ?>
                    </p>

                    <!-- Features -->
                    <ul class="space-y-2">
                        <?php foreach ($features as $feat): if (trim($feat)): ?>
                        <li class="flex items-center gap-2 text-sm text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-<?= $serv['color'] ?>"></i>
                            <?= trim($feat) ?>
                        </li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- Comparativa Section -->
    <section class="py-24 relative bg-cyber-dark">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-4">
                    <?= __('comparativa_title', 'CMS vs Código Propio') ?>
                </h2>
                <p class="text-gray-400 text-lg font-mono">
                    <?= __('comparativa_subtitle', 'La diferencia está en los detalles') ?>
                </p>
            </div>

            <div class="overflow-x-auto reveal">
                <table class="cyber-table w-full text-left">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 font-display font-semibold text-white"><?= __('comp_header_aspecto', 'Aspecto') ?></th>
                            <th class="px-6 py-4 font-display font-semibold text-red-400"><?= __('comp_header_cms', 'CMS') ?></th>
                            <th class="px-6 py-4 font-display font-semibold text-neon-cyan"><?= __('comp_header_propio', 'Código Propio') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                        <tr>
                            <td class="px-6 py-4 text-gray-300 font-medium"><?= __("comp_row{$i}_aspecto", '') ?></td>
                            <td class="px-6 py-4 text-gray-500">
                                <span class="flex items-center gap-2">
                                    <i data-lucide="x" class="w-4 h-4 text-red-500"></i>
                                    <?= __("comp_row{$i}_cms", '') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-300">
                                <span class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-neon-cyan"></i>
                                    <?= __("comp_row{$i}_propio", '') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Proceso Section -->
    <section id="proceso" class="py-24 relative">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-4">
                    <?= __('proceso_title', 'Nuestro Proceso') ?>
                </h2>
                <p class="text-gray-400 text-lg font-mono">
                    <?= __('proceso_subtitle', 'De la idea al código funcionando') ?>
                </p>
            </div>

            <div class="relative">
                <!-- Timeline line -->
                <div class="hidden lg:block absolute left-1/2 top-0 bottom-0 w-0.5 timeline-line"></div>

                <div class="space-y-12 lg:space-y-0">
                    <?php
                    $iconos_proceso = ['search', 'pen-tool', 'code-2', 'bug', 'rocket', 'headphones'];
                    for ($i = 1; $i <= 6; $i++):
                        $isLeft = $i % 2 === 1;
                    ?>
                    <div class="relative lg:grid lg:grid-cols-2 lg:gap-8 reveal" style="animation-delay: <?= ($i-1) * 0.1 ?>s">
                        <!-- Content -->
                        <div class="<?= $isLeft ? 'lg:pr-12 lg:text-right' : 'lg:col-start-2 lg:pl-12' ?>">
                            <div class="cyber-card rounded-xl p-6 inline-block <?= $isLeft ? 'lg:ml-auto' : '' ?>">
                                <div class="flex items-center gap-4 <?= $isLeft ? 'lg:flex-row-reverse' : '' ?> mb-4">
                                    <div class="w-12 h-12 rounded-full bg-neon-cyan/10 flex items-center justify-center border border-neon-cyan/30">
                                        <i data-lucide="<?= $iconos_proceso[$i-1] ?>" class="w-5 h-5 text-neon-cyan"></i>
                                    </div>
                                    <div>
                                        <span class="font-mono text-neon-cyan text-sm">0<?= $i ?></span>
                                        <h3 class="font-display text-lg font-bold text-white">
                                            <?= __("proceso_step{$i}_title", "Paso $i") ?>
                                        </h3>
                                    </div>
                                </div>
                                <p class="text-gray-400 text-sm <?= $isLeft ? 'lg:text-right' : '' ?>">
                                    <?= __("proceso_step{$i}_desc", '') ?>
                                </p>
                            </div>
                        </div>

                        <!-- Timeline dot (desktop) -->
                        <div class="hidden lg:flex absolute left-1/2 top-6 -translate-x-1/2 w-4 h-4 rounded-full bg-neon-cyan border-4 border-cyber-black"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Stack Section -->
    <section id="tech" class="py-24 relative bg-cyber-dark grid-bg">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-4">
                    <?= __('tech_title', 'Stack Tecnológico') ?>
                </h2>
                <p class="text-gray-400 text-lg font-mono">
                    <?= __('tech_subtitle', 'Herramientas de última generación') ?>
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Backend -->
                <div class="cyber-card rounded-xl p-6 reveal">
                    <h3 class="font-mono text-neon-cyan text-sm mb-4"><?= __('tech_backend', 'Backend') ?></h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#777BB4] rounded flex items-center justify-center text-white font-bold text-xs">PHP</span>
                            PHP 8+
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#3776AB] rounded flex items-center justify-center text-white font-bold text-xs">Py</span>
                            Python
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#339933] rounded flex items-center justify-center text-white font-bold text-xs">N</span>
                            Node.js
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#00ADD8] rounded flex items-center justify-center text-white font-bold text-xs">Go</span>
                            Golang
                        </div>
                    </div>
                </div>

                <!-- Frontend -->
                <div class="cyber-card rounded-xl p-6 reveal" style="animation-delay: 0.1s">
                    <h3 class="font-mono text-neon-magenta text-sm mb-4"><?= __('tech_frontend', 'Frontend') ?></h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#61DAFB] rounded flex items-center justify-center text-black font-bold text-xs">R</span>
                            React
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#4FC08D] rounded flex items-center justify-center text-white font-bold text-xs">V</span>
                            Vue.js
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#06B6D4] rounded flex items-center justify-center text-white font-bold text-xs">T</span>
                            Tailwind
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#3178C6] rounded flex items-center justify-center text-white font-bold text-xs">TS</span>
                            TypeScript
                        </div>
                    </div>
                </div>

                <!-- Mobile -->
                <div class="cyber-card rounded-xl p-6 reveal" style="animation-delay: 0.2s">
                    <h3 class="font-mono text-neon-green text-sm mb-4"><?= __('tech_mobile', 'Mobile') ?></h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#02569B] rounded flex items-center justify-center text-white font-bold text-xs">F</span>
                            Flutter
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#61DAFB] rounded flex items-center justify-center text-black font-bold text-xs">RN</span>
                            React Native
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#3DDC84] rounded flex items-center justify-center text-white font-bold text-xs">A</span>
                            Android
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#000000] rounded flex items-center justify-center text-white font-bold text-xs">i</span>
                            iOS
                        </div>
                    </div>
                </div>

                <!-- Databases -->
                <div class="cyber-card rounded-xl p-6 reveal" style="animation-delay: 0.3s">
                    <h3 class="font-mono text-neon-blue text-sm mb-4"><?= __('tech_database', 'Bases de Datos') ?></h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#4479A1] rounded flex items-center justify-center text-white font-bold text-xs">My</span>
                            MySQL
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#336791] rounded flex items-center justify-center text-white font-bold text-xs">Pg</span>
                            PostgreSQL
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#47A248] rounded flex items-center justify-center text-white font-bold text-xs">M</span>
                            MongoDB
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#DC382D] rounded flex items-center justify-center text-white font-bold text-xs">R</span>
                            Redis
                        </div>
                    </div>
                </div>

                <!-- Cloud -->
                <div class="cyber-card rounded-xl p-6 reveal" style="animation-delay: 0.4s">
                    <h3 class="font-mono text-neon-purple text-sm mb-4"><?= __('tech_cloud', 'Cloud & DevOps') ?></h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#FF9900] rounded flex items-center justify-center text-white font-bold text-xs">AWS</span>
                            AWS
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#4285F4] rounded flex items-center justify-center text-white font-bold text-xs">G</span>
                            Google Cloud
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#2496ED] rounded flex items-center justify-center text-white font-bold text-xs">D</span>
                            Docker
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 text-sm">
                            <span class="w-8 h-8 bg-[#F05032] rounded flex items-center justify-center text-white font-bold text-xs">G</span>
                            Git
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-neon-cyan/10 via-neon-magenta/10 to-neon-cyan/10"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 reveal">
            <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-6">
                <?= __('cta_title', '¿Listo para Liberarte del CMS?') ?>
            </h2>
            <p class="text-gray-400 text-lg mb-10">
                <?= __('cta_subtitle', 'Contanos tu proyecto y te asesoramos sin compromiso') ?>
            </p>
            <a href="#contacto" class="btn-cyber-fill px-10 py-5 rounded-xl font-display font-bold text-xl pulse-glow inline-block">
                <?= __('cta_btn', 'Solicitar Presupuesto') ?>
            </a>
        </div>
    </section>

    <!-- Contacto Section -->
    <section id="contacto" class="py-24 relative bg-cyber-dark">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-4">
                    <?= __('contacto_title', 'Hablemos de tu Proyecto') ?>
                </h2>
                <p class="text-gray-400 text-lg font-mono">
                    <?= __('contacto_subtitle', 'Completá el formulario y nos comunicamos en 24hs') ?>
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-12">
                <!-- Form -->
                <div class="cyber-card rounded-xl p-8 reveal">
                    <form id="contactForm" class="space-y-6">
                        <input type="hidden" name="producto" value="Desarrollo Web">
                        <input type="hidden" name="lang" value="<?= $current_lang ?>">

                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-400 text-sm font-mono mb-2"><?= __('contacto_nombre', 'Nombre') ?> *</label>
                                <input type="text" name="nombre" required
                                    class="w-full bg-cyber-black border border-gray-700 rounded-lg px-4 py-3 text-white focus:border-neon-cyan focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-gray-400 text-sm font-mono mb-2"><?= __('contacto_email', 'Email') ?> *</label>
                                <input type="email" name="email" required
                                    class="w-full bg-cyber-black border border-gray-700 rounded-lg px-4 py-3 text-white focus:border-neon-cyan focus:outline-none transition-colors">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-400 text-sm font-mono mb-2"><?= __('contacto_telefono', 'Teléfono') ?></label>
                                <input type="tel" name="telefono"
                                    class="w-full bg-cyber-black border border-gray-700 rounded-lg px-4 py-3 text-white focus:border-neon-cyan focus:outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-gray-400 text-sm font-mono mb-2"><?= __('contacto_empresa', 'Empresa / Proyecto') ?> *</label>
                                <input type="text" name="organizacion" required
                                    class="w-full bg-cyber-black border border-gray-700 rounded-lg px-4 py-3 text-white focus:border-neon-cyan focus:outline-none transition-colors">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-400 text-sm font-mono mb-2"><?= __('contacto_tipo', 'Tipo de Proyecto') ?> *</label>
                            <select name="tipo" required
                                class="w-full bg-cyber-black border border-gray-700 rounded-lg px-4 py-3 text-white focus:border-neon-cyan focus:outline-none transition-colors">
                                <option value=""><?= __('contacto_tipo', 'Seleccionar...') ?></option>
                                <option value="web"><?= __('contacto_tipo_web', 'Sitio Web') ?></option>
                                <option value="ecommerce"><?= __('contacto_tipo_ecommerce', 'E-Commerce') ?></option>
                                <option value="webapp"><?= __('contacto_tipo_webapp', 'Aplicación Web') ?></option>
                                <option value="mobile"><?= __('contacto_tipo_mobile', 'App Móvil') ?></option>
                                <option value="lms"><?= __('contacto_tipo_lms', 'Plataforma Educativa') ?></option>
                                <option value="otro"><?= __('contacto_tipo_otro', 'Otro') ?></option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-400 text-sm font-mono mb-2"><?= __('contacto_mensaje', 'Mensaje') ?></label>
                            <textarea name="mensaje" rows="4"
                                class="w-full bg-cyber-black border border-gray-700 rounded-lg px-4 py-3 text-white focus:border-neon-cyan focus:outline-none transition-colors resize-none"
                                placeholder="<?= __('contacto_mensaje', 'Contanos sobre tu proyecto...') ?>"></textarea>
                        </div>

                        <button type="submit" class="w-full btn-cyber-fill py-4 rounded-lg font-display font-semibold text-lg flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-5 h-5"></i>
                            <?= __('contacto_btn', 'Enviar Consulta') ?>
                        </button>

                        <!-- Response message -->
                        <div id="formResponse" class="hidden text-center py-3 rounded-lg"></div>
                    </form>
                </div>

                <!-- Info -->
                <div class="space-y-8 reveal" style="animation-delay: 0.2s">
                    <!-- Code block decoration -->
                    <div class="code-block rounded-lg p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-gray-500 text-xs ml-2">contact.js</span>
                        </div>
                        <pre class="text-sm text-gray-300"><code><span class="text-neon-magenta">const</span> <span class="text-neon-cyan">contact</span> = {
  <span class="text-gray-500">// Respondemos en 24hs</span>
  email: <span class="text-neon-green">'contacto@verumax.com'</span>,
  whatsapp: <span class="text-neon-green">'+54 9 XXX XXX XXXX'</span>,
  location: <span class="text-neon-green">'Argentina 🇦🇷'</span>
};</code></pre>
                    </div>

                    <!-- WhatsApp -->
                    <a href="https://wa.me/549XXXXXXXXXX" target="_blank" rel="noopener"
                        class="flex items-center gap-4 p-6 cyber-card rounded-xl hover:border-green-500 transition-all group">
                        <div class="w-14 h-14 rounded-full bg-green-500/10 flex items-center justify-center group-hover:bg-green-500/20 transition-colors">
                            <i data-lucide="message-circle" class="w-7 h-7 text-green-500"></i>
                        </div>
                        <div>
                            <p class="text-white font-semibold"><?= __('contacto_whatsapp', 'Escribinos por WhatsApp') ?></p>
                            <p class="text-gray-500 text-sm font-mono">+54 9 XXX XXX XXXX</p>
                        </div>
                    </a>

                    <!-- Back to Verumax -->
                    <a href="/landing.php?lang=<?= $current_lang ?>" class="flex items-center gap-2 text-gray-400 hover:text-neon-cyan transition-colors font-mono text-sm">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <?= __('volver_verumax', '← Volver a Verumax') ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-display font-bold text-white">VERU</span>
                    <span class="font-display font-bold text-neon-cyan">MAX</span>
                    <span class="text-gray-500 text-sm ml-2"><?= __('footer_slogan', 'Programamos desde cero. Sin límites.') ?></span>
                </div>
                <p class="text-gray-500 text-sm font-mono">
                    <?= __('footer_copy', '© 2026 Verumax. Todos los derechos reservados.') ?>
                </p>
            </div>
        </div>
    </footer>

    <!-- Scroll to top button -->
    <button id="scrollTopBtn" class="fixed bottom-6 right-6 w-12 h-12 bg-neon-cyan text-cyber-black rounded-full shadow-lg opacity-0 invisible transition-all hover:scale-110 z-50 flex items-center justify-center"
        aria-label="Scroll to top">
        <i data-lucide="arrow-up" class="w-6 h-6"></i>
    </button>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        mobileMenuBtn?.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Close mobile menu on link click
        mobileMenu?.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });

        // Language selector
        document.getElementById('langSelector')?.addEventListener('change', function() {
            const hash = window.location.hash;
            window.location.href = '?lang=' + this.value + hash;
        });

        // Scroll reveal
        const revealElements = document.querySelectorAll('.reveal');
        const revealOnScroll = () => {
            revealElements.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) {
                    el.classList.add('visible');
                }
            });
        };
        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll();

        // Scroll to top button
        const scrollTopBtn = document.getElementById('scrollTopBtn');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                scrollTopBtn.classList.remove('opacity-0', 'invisible');
                scrollTopBtn.classList.add('opacity-100', 'visible');
            } else {
                scrollTopBtn.classList.add('opacity-0', 'invisible');
                scrollTopBtn.classList.remove('opacity-100', 'visible');
            }
        });

        scrollTopBtn?.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Contact form
        document.getElementById('contactForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const btn = form.querySelector('button[type="submit"]');
            const response = document.getElementById('formResponse');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Enviando...';
            lucide.createIcons();

            try {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                const res = await fetch('/api/contact.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await res.json();

                response.classList.remove('hidden', 'bg-red-500/20', 'text-red-400', 'bg-green-500/20', 'text-green-400');

                if (result.success) {
                    response.classList.add('bg-green-500/20', 'text-green-400');
                    response.textContent = '<?= __('contacto_success', '¡Mensaje enviado! Te contactamos pronto.') ?>';
                    form.reset();
                } else {
                    response.classList.add('bg-red-500/20', 'text-red-400');
                    response.textContent = result.error || '<?= __('contacto_error', 'Error al enviar. Intentá de nuevo.') ?>';
                }
            } catch (err) {
                response.classList.remove('hidden');
                response.classList.add('bg-red-500/20', 'text-red-400');
                response.textContent = '<?= __('contacto_error', 'Error al enviar. Intentá de nuevo.') ?>';
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
