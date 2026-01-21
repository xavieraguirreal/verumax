<?php
/**
 * HEADER COMPARTIDO MULTITENANT
 *
 * Header único que se usa en todos los módulos (Identitas, Certificatum, etc.)
 * Se personaliza automáticamente según la configuración de la institución.
 *
 * Variables esperadas:
 * - $instance: Array con configuración de la institución (desde InstitutionService)
 * - $page_type: Tipo de página ('identitas' | 'certificatum' | 'home') [opcional]
 * - $page_title: Título de la página [opcional]
 * - $paginas: Array de páginas del menú (solo para Identitas) [opcional]
 *
 * Incluye optimizaciones SEO:
 * - Canonical URL, og:url
 * - Schema.org JSON-LD
 * - Control dinámico de robots
 * - Hreflang para multiidioma
 */

// Valores por defecto
$page_type = $page_type ?? 'home';
$nombre = $instance['nombre'] ?? 'Institución';
$color_primario = $instance['color_primario'] ?? '#2E7D32';
$color_secundario = $instance['color_secundario'] ?? '#1B5E20';
$color_acento = $instance['color_acento'] ?? '#66BB6A';

// SEO: Determinar URL canónica y protocolo
$seo_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$seo_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$seo_uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$seo_canonical_url = $seo_protocol . $seo_host . $seo_uri;
$seo_current_url = $seo_protocol . $seo_host . ($_SERVER['REQUEST_URI'] ?? '/');

// SEO: Obtener idioma actual para atributo lang
$html_lang = 'es';
if (class_exists('\VERUMax\Services\LanguageService')) {
    $current_lang_code = \VERUMax\Services\LanguageService::getCurrentLang() ?: 'es_AR';
    $html_lang = explode('_', $current_lang_code)[0];
}

// Tema: Determinar si el tema por defecto es oscuro
$tema_default = $instance['tema_default'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($html_lang); ?>" data-tema-default="<?php echo $tema_default; ?>"<?php echo $tema_default === 'dark' ? ' class="dark"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($page_title ?? ($nombre . ' - Portal Digital')); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($instance['seo_description'] ?? ('Portal oficial de ' . ($instance['nombre_completo'] ?: $nombre))); ?>">
    <?php if (!empty($instance['seo_keywords'])): ?>
    <meta name="keywords" content="<?php echo htmlspecialchars($instance['seo_keywords']); ?>">
    <?php endif; ?>

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title ?? $nombre); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($instance['seo_description'] ?? ('Portal oficial de ' . ($instance['nombre_completo'] ?: $nombre))); ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($instance['logo_url'])): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($instance['logo_url']); ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title ?? $nombre); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($instance['seo_description'] ?? ('Portal oficial de ' . ($instance['nombre_completo'] ?: $nombre))); ?>">
    <?php if (!empty($instance['logo_url'])): ?>
    <meta name="twitter:image" content="<?php echo htmlspecialchars($instance['logo_url']); ?>">
    <?php endif; ?>

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($seo_canonical_url); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($seo_current_url); ?>">

    <!-- Favicon -->
    <?php if (!empty($instance['favicon_url'])): ?>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo htmlspecialchars($instance['favicon_url']); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo str_replace('32x32', '16x16', htmlspecialchars($instance['favicon_url'])); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo str_replace('favicon-32x32', 'apple-touch-icon', htmlspecialchars($instance['favicon_url'])); ?>">
    <?php endif; ?>

    <!-- Control de indexación (robots) -->
    <?php if ($instance['robots_noindex'] ?? true): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php else: ?>
    <meta name="robots" content="index, follow">
    <?php endif; ?>

    <!-- Hreflang para multiidioma -->
    <?php
    $idiomas_habilitados_seo = explode(',', $instance['idiomas_habilitados'] ?? 'es_AR');
    if (count($idiomas_habilitados_seo) > 1):
        foreach ($idiomas_habilitados_seo as $codigo_seo):
            $codigo_seo = trim($codigo_seo);
            $hreflang_tag = str_replace('_', '-', $codigo_seo);
            $hreflang_url = $seo_canonical_url . '?lang=' . urlencode($codigo_seo);
    ?>
    <link rel="alternate" hreflang="<?php echo $hreflang_tag; ?>" href="<?php echo htmlspecialchars($hreflang_url); ?>">
    <?php
        endforeach;
    ?>
    <link rel="alternate" hreflang="x-default" href="<?php echo htmlspecialchars($seo_canonical_url); ?>">
    <?php endif; ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primario: 'var(--color-primario)',
                        secundario: 'var(--color-secundario)',
                        acento: 'var(--color-acento)',
                    }
                }
            }
        }
    </script>
    <style>
        /* Colores dinámicos de la institución */
        :root {
            --color-primario: <?php echo $color_primario; ?>;
            --color-secundario: <?php echo $color_secundario; ?>;
            --color-acento: <?php echo $color_acento; ?>;
        }
        .bg-primario { background-color: var(--color-primario); }
        .bg-secundario { background-color: var(--color-secundario); }
        .bg-acento { background-color: var(--color-acento); }
        .text-primario { color: var(--color-primario); }
        .text-secundario { color: var(--color-secundario); }
        .text-acento { color: var(--color-acento); }
        .border-primario { border-color: var(--color-primario); }
        .border-secundario { border-color: var(--color-secundario); }
        .border-acento { border-color: var(--color-acento); }
        .hover\:bg-primario:hover { background-color: var(--color-primario); }
        .hover\:bg-secundario:hover { background-color: var(--color-secundario); }
        .hover\:text-primario:hover { color: var(--color-primario); }
        .hover\:text-secundario:hover { color: var(--color-secundario); }
        .ring-primario { --tw-ring-color: var(--color-primario); }
        .focus\:ring-primario:focus { --tw-ring-color: var(--color-primario); }
    </style>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js para interactividad -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">

    <?php
    // CSS personalizado del motor (si está disponible)
    if (isset($engine)) {
        echo $engine->getCustomCSS();
    } else {
        // CSS inline básico con color primario y secundario
        $color_secundario = $instance['color_secundario'] ?? $color_primario;
        echo '<style>:root { --color-primario: ' . htmlspecialchars($color_primario) . '; --color-secundario: ' . htmlspecialchars($color_secundario) . '; }</style>';
    }
    ?>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        html { scroll-behavior: smooth; }
        /* El contenido principal debe ocupar el espacio disponible */
        body > main { flex: 1; }

        /* Ajustar scroll para compensar header sticky */
        section[id] {
            scroll-margin-top: 80px;
        }

        /* Animación fade-in-up para Certificatum */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        /* Animación shake para errores */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }

        /* Animación fade-in */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
    </style>

    <!-- Schema.org JSON-LD (Datos Estructurados para SEO) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "EducationalOrganization",
        "name": "<?php echo htmlspecialchars($instance['nombre_completo'] ?: $nombre, ENT_QUOTES); ?>",
        "url": "<?php echo htmlspecialchars($seo_protocol . $seo_host, ENT_QUOTES); ?>",
        <?php if (!empty($instance['logo_url'])): ?>
        "logo": "<?php echo htmlspecialchars($instance['logo_url'], ENT_QUOTES); ?>",
        <?php endif; ?>
        <?php if (!empty($instance['seo_description'])): ?>
        "description": "<?php echo htmlspecialchars($instance['seo_description'], ENT_QUOTES); ?>",
        <?php endif; ?>
        <?php if (!empty($instance['email_contacto'])): ?>
        "email": "<?php echo htmlspecialchars($instance['email_contacto'], ENT_QUOTES); ?>",
        <?php endif; ?>
        <?php if (!empty($instance['sitio_web_oficial'])): ?>
        "sameAs": "<?php echo htmlspecialchars($instance['sitio_web_oficial'], ENT_QUOTES); ?>",
        <?php endif; ?>
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "AR"
        }
    }
    </script>
    <?php if ($instance['modulo_certificatum'] ?? false): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Portal de Certificados Digitales",
        "provider": {
            "@type": "EducationalOrganization",
            "name": "<?php echo htmlspecialchars($nombre, ENT_QUOTES); ?>"
        },
        "description": "Validación y descarga de certificados académicos verificables con código QR",
        "serviceType": "Certificación Digital",
        "areaServed": "AR"
    }
    </script>
    <?php endif; ?>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors duration-300">

    <!-- Header / Navbar -->
    <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-20 transition-colors duration-300 <?php echo $page_type === 'certificatum' ? 'border-b border-gray-200 dark:border-gray-700' : ''; ?>">
        <?php
        // Detectar si estamos en una página secundaria (solo relevante para Identitas)
        $es_pagina_secundaria = ($page_type === 'identitas') && isset($_GET['page']) && !empty($_GET['page']);
        $base_url = $es_pagina_secundaria ? './' : '';
        ?>
        <nav class="<?php echo $page_type === 'certificatum' ? 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' : 'container mx-auto px-6 py-4'; ?>">
            <div class="flex <?php echo $page_type === 'certificatum' ? 'items-center justify-between h-16' : 'justify-between items-center'; ?>">
                <!-- Logo -->
                <a href="<?php echo $page_type === 'identitas' ? ($es_pagina_secundaria ? './' : '#inicio') : './'; ?>" class="flex items-center gap-3">
                    <?php if (!empty($instance['logo_url'])): ?>
                        <img src="<?php echo htmlspecialchars($instance['logo_url']); ?>"
                             alt="Logo <?php echo htmlspecialchars($nombre); ?>"
                             class="<?php
                                $logo_estilo = $instance['logo_estilo'] ?? 'rectangular';
                                $classes = 'h-10 w-auto object-contain';
                                if ($logo_estilo === 'rectangular-rounded') $classes .= ' rounded-lg';
                                if ($logo_estilo === 'cuadrado') $classes .= ' aspect-square object-cover';
                                if ($logo_estilo === 'cuadrado-rounded') $classes .= ' aspect-square object-cover rounded-lg';
                                if ($logo_estilo === 'circular') $classes .= ' aspect-square object-cover rounded-full';
                                echo $classes;
                             ?>">
                    <?php else: ?>
                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold" style="background-color: <?php echo $color_primario; ?>">
                            <?php echo strtoupper(substr($nombre, 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!isset($instance['logo_mostrar_texto']) || $instance['logo_mostrar_texto'] == 1): ?>
                        <span class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($nombre); ?></span>
                    <?php endif; ?>
                </a>

                <!-- Menú -->
                <div class="flex items-center gap-4">
                    <?php if ($page_type === 'identitas' && isset($paginas)): ?>
                        <!-- Menú de navegación de Identitas -->
                        <div class="hidden md:flex items-center space-x-6">
                            <a href="<?php echo $base_url; ?>#inicio" class="nav-link text-gray-600 dark:text-gray-300 hover:text-primario font-medium transition-colors"><?php echo \VERUMax\Services\LanguageService::get('nav_home', [], 'Inicio'); ?></a>

                            <?php foreach ($paginas as $pag): ?>
                                <?php if ($pag['visible_menu'] && $pag['slug'] !== 'inicio'): ?>
                                    <a href="<?php echo $base_url; ?>#<?php echo urlencode($pag['slug']); ?>" class="nav-link text-gray-600 dark:text-gray-300 hover:text-primario font-medium transition-colors">
                                        <?php echo htmlspecialchars($pag['titulo']); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php if ($instance['modulo_certificatum'] ?? false): ?>
                                <a href="<?php echo $base_url; ?>#certificados" class="nav-link text-gray-600 dark:text-gray-300 hover:text-primario font-medium transition-colors">
                                    <?php echo \VERUMax\Services\LanguageService::get('nav_certificates', [], 'Certificados'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($page_type === 'certificatum'): ?>
                        <!-- Menú simplificado para Certificatum -->
                        <?php
                        // Determinar URL del inicio según si Identitas está activo
                        $identitas_activo = $instance['identitas_activo'] ?? false;
                        // Obtener slug desde $instance o desde la variable global $institucion si existe
                        $institucion_slug = $instance['slug'] ?? (isset($institucion) ? $institucion : '');
                        if (defined('PROXY_MODE') && PROXY_MODE) {
                            // En modo proxy, siempre ir a la raíz
                            $inicio_url = '/';
                        } elseif ($identitas_activo) {
                            // Identitas activo: ir al portal completo
                            $inicio_url = '/';
                        } else {
                            // Solo Certificatum: ir al index.php de la institución
                            $inicio_url = '../' . $institucion_slug . '/';
                        }
                        ?>
                        <div class="hidden md:flex items-center space-x-6">
                            <a href="<?php echo $inicio_url; ?>" class="nav-link text-gray-600 dark:text-gray-300 hover:text-primario font-medium transition-colors flex items-center gap-1">
                                <i data-lucide="home" class="w-4 h-4"></i>
                                <?php echo \VERUMax\Services\LanguageService::get('nav_home', [], 'Inicio'); ?>
                            </a>
                            <?php if ($identitas_activo): ?>
                                <!-- Si Identitas está activo, mostrar más opciones -->
                                <a href="<?php echo $inicio_url; ?>#sobre-nosotros" class="nav-link text-gray-600 dark:text-gray-300 hover:text-primario font-medium transition-colors">
                                    <?php echo \VERUMax\Services\LanguageService::get('nav_about', [], 'Sobre Nosotros'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Selector de Idioma -->
                    <?php
                    $idiomas_habilitados = explode(',', $instance['idiomas_habilitados'] ?? 'es_AR');
                    if (count($idiomas_habilitados) > 1):
                        $idioma_actual = \VERUMax\Services\LanguageService::getCurrentLang();
                        $idiomas_info = \VERUMax\Services\LanguageService::AVAILABLE_LANGUAGES;

                        // Construir URL preservando parámetros existentes
                        $current_params = $_GET;
                        unset($current_params['lang']); // Eliminar lang actual para reemplazarlo
                    ?>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-sm"
                                    aria-label="Cambiar idioma">
                                <?php
                                $flag_code = $idiomas_info[$idioma_actual]['flag_svg'] ?? 'un';
                                ?>
                                <img src="https://flagcdn.com/w40/<?php echo $flag_code; ?>.png"
                                     srcset="https://flagcdn.com/w80/<?php echo $flag_code; ?>.png 2x"
                                     width="20" height="15"
                                     alt="<?php echo $idiomas_info[$idioma_actual]['name'] ?? 'Idioma'; ?>"
                                     class="rounded-sm">
                                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-500"></i>
                            </button>
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                                <?php foreach ($idiomas_habilitados as $codigo):
                                    $codigo = trim($codigo);
                                    if (!isset($idiomas_info[$codigo])) continue;
                                    $info = $idiomas_info[$codigo];
                                    $is_current = ($codigo === $idioma_actual);
                                    // Construir URL con lang + parámetros existentes
                                    $lang_params = array_merge($current_params, ['lang' => $codigo]);
                                    $lang_url = '?' . http_build_query($lang_params);
                                ?>
                                    <a href="<?php echo htmlspecialchars($lang_url); ?>"
                                       class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors <?php echo $is_current ? 'bg-gray-50 dark:bg-gray-700' : ''; ?>">
                                        <img src="https://flagcdn.com/w40/<?php echo $info['flag_svg'] ?? 'un'; ?>.png"
                                             srcset="https://flagcdn.com/w80/<?php echo $info['flag_svg'] ?? 'un'; ?>.png 2x"
                                             width="20" height="15"
                                             alt="<?php echo htmlspecialchars($info['name']); ?>"
                                             class="rounded-sm">
                                        <span class="text-sm text-gray-700 dark:text-gray-200"><?php echo $info['name']; ?></span>
                                        <?php if ($is_current): ?>
                                            <i data-lucide="check" class="w-4 h-4 text-green-500 ml-auto"></i>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Botón Admin -->
                    <?php if (!empty($instance['admin_usuario'])): ?>
                        <a href="/admin/"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            <span class="hidden sm:inline"><?php echo \VERUMax\Services\LanguageService::get('common.nav_admin', [], 'Admin'); ?></span>
                        </a>
                    <?php endif; ?>

                    <!-- Toggle Modo Oscuro -->
                    <button id="dark-mode-toggle"
                            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            aria-label="Alternar modo oscuro">
                        <i data-lucide="moon" class="w-5 h-5 text-gray-600 dark:hidden"></i>
                        <i data-lucide="sun" class="w-5 h-5 text-gray-400 hidden dark:inline"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>
