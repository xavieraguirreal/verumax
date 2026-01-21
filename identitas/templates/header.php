<?php
/**
 * IDENTITAS - Header Template Base
 * Se personaliza automáticamente según la configuración de la instancia
 *
 * Incluye optimizaciones SEO:
 * - Meta tags Open Graph y Twitter Cards
 * - Canonical URL
 * - Schema.org JSON-LD
 * - Hreflang para multiidioma
 * - Control de indexación (robots)
 */

use VERUMax\Services\LanguageService;

// Inicializar idioma si no se ha hecho
$institucion_slug = $instance['slug'] ?? '';
if ($institucion_slug && !LanguageService::getCurrentLang()) {
    LanguageService::init($institucion_slug, $_GET['lang'] ?? null);
}

// Helper de traducción
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

$nombre = $instance['nombre'] ?? 'Identitas';
$color_primario = $instance['color_primario'] ?? '#D4AF37';

// SEO: Determinar URL canónica y protocolo
$seo_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$seo_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$seo_uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$seo_canonical_url = $seo_protocol . $seo_host . $seo_uri;
$seo_current_url = $seo_protocol . $seo_host . ($_SERVER['REQUEST_URI'] ?? '/');

// SEO: Obtener idioma actual para atributo lang
$current_lang_code = LanguageService::getCurrentLang() ?: 'es_AR';
$html_lang = explode('_', $current_lang_code)[0]; // es_AR -> es

// Tema: Determinar si el tema por defecto es oscuro
$tema_default = $instance['tema_default'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($html_lang); ?>" data-tema-default="<?php echo $tema_default; ?>"<?php echo $tema_default === 'dark' ? ' class="dark"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($instance['seo_title'] ?? ($nombre . ' - Presencia Digital Profesional')); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($instance['seo_description'] ?? ('Portal oficial de ' . ($instance['nombre_completo'] ?: $nombre))); ?>">
    <?php if (!empty($instance['seo_keywords'])): ?>
    <meta name="keywords" content="<?php echo htmlspecialchars($instance['seo_keywords']); ?>">
    <?php endif; ?>

    <!-- Open Graph Meta Tags (para compartir en redes sociales) -->
    <meta property="og:title" content="<?php echo htmlspecialchars($instance['seo_title'] ?? $nombre); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($instance['seo_description'] ?? ('Portal oficial de ' . ($instance['nombre_completo'] ?: $nombre))); ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($instance['logo_url'])): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($instance['logo_url']); ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($instance['seo_title'] ?? $nombre); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($instance['seo_description'] ?? ('Portal oficial de ' . ($instance['nombre_completo'] ?: $nombre))); ?>">
    <?php if (!empty($instance['logo_url'])): ?>
    <meta name="twitter:image" content="<?php echo htmlspecialchars($instance['logo_url']); ?>">
    <?php endif; ?>

    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($seo_canonical_url); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($seo_current_url); ?>">

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
            $hreflang_tag = str_replace('_', '-', $codigo_seo); // es_AR -> es-AR
            $hreflang_url = $seo_canonical_url . '?lang=' . urlencode($codigo_seo);
    ?>
    <link rel="alternate" hreflang="<?php echo $hreflang_tag; ?>" href="<?php echo htmlspecialchars($hreflang_url); ?>">
    <?php
        endforeach;
        // X-default para usuarios sin preferencia de idioma
    ?>
    <link rel="alternate" hreflang="x-default" href="<?php echo htmlspecialchars($seo_canonical_url); ?>">
    <?php endif; ?>

    <!-- Favicon -->
    <?php if (!empty($instance['favicon_url'])): ?>
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo htmlspecialchars($instance['favicon_url']); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo str_replace('32x32', '16x16', htmlspecialchars($instance['favicon_url'])); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo str_replace('favicon-32x32', 'apple-touch-icon', htmlspecialchars($instance['favicon_url'])); ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'primario': '<?php echo $color_primario; ?>'
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <?php
    // getCustomCSS solo está disponible si $engine existe
    if (isset($engine)) {
        echo $engine->getCustomCSS();
    } else {
        // CSS inline básico
        echo '<style>:root { --color-primario: ' . htmlspecialchars($color_primario) . '; }</style>';
    }
    ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }

        /* Ajustar scroll para compensar header sticky */
        section[id] {
            scroll-margin-top: 80px;
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

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-20 transition-colors duration-300">
        <?php
        // Detectar si estamos en una página secundaria
        $es_pagina_secundaria = isset($_GET['page']) && !empty($_GET['page']);

        // URL base: siempre usar la raíz relativa para evitar problemas con index.php
        // Esto hace que los enlaces sean "/" o "" con anclas, nunca "index.php#..."
        $base_url = $es_pagina_secundaria ? './' : '';
        ?>
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="<?php echo $es_pagina_secundaria ? './' : '#inicio'; ?>" class="flex items-center gap-3">
                <?php if (!empty($instance['logo_url'])): ?>
                    <img src="<?php echo htmlspecialchars($instance['logo_url']); ?>"
                         alt="Logo <?php echo htmlspecialchars($nombre); ?>"
                         class="<?php echo getLogoClasses($instance['logo_estilo'] ?? 'rectangular', 'h-10'); ?>">
                <?php else: ?>
                    <div class="h-10 w-10 rounded-full bg-primario flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($nombre, 0, 2)); ?>
                    </div>
                <?php endif; ?>
                <?php if (!isset($instance['logo_mostrar_texto']) || $instance['logo_mostrar_texto'] == 1): ?>
                    <span class="text-xl font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($nombre); ?></span>
                <?php endif; ?>
            </a>

            <div class="flex items-center gap-4">
                <!-- Menú de navegación de Identitas -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?php echo $base_url; ?>#inicio" class="nav-link text-gray-600 dark:text-gray-300 hover:text-primario font-medium transition-colors"><?php echo $t('nav_home', [], 'Inicio'); ?></a>

                    <?php foreach ($paginas as $pag): ?>
                        <?php if ($pag['visible_menu'] && $pag['slug'] !== 'inicio'): ?>
                            <a href="<?php echo $base_url; ?>#<?php echo urlencode($pag['slug']); ?>" class="nav-link text-gray-600 dark:text-gray-300 hover:text-primario font-medium transition-colors">
                                <?php echo $t('identitas.menu_' . $pag['slug'], [], $pag['titulo']); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Link a Certificatum (siempre visible si está activo) -->
                    <?php if (isset($certificatum_config) && $certificatum_config): ?>
                        <?php if ($certificatum_config['modo'] === 'seccion'): ?>
                            <!-- Modo Sección: scroll a #certificados -->
                            <a href="<?php echo $base_url; ?>#certificados" class="nav-link text-gray-600 dark:text-gray-300 hover:text-primario font-medium transition-colors flex items-center gap-1">
                                <i data-lucide="<?php echo htmlspecialchars($certificatum_config['icono']); ?>" class="w-4 h-4"></i>
                                <?php echo $t('nav_certificates', [], $certificatum_config['titulo']); ?>
                            </a>
                        <?php elseif ($certificatum_config['modo'] === 'pagina'): ?>
                            <!-- Modo Página: navegar a ?page=certificados -->
                            <a href="?page=certificados" class="text-gray-600 dark:text-gray-300 hover:text-primario font-medium transition-colors flex items-center gap-1">
                                <i data-lucide="<?php echo htmlspecialchars($certificatum_config['icono']); ?>" class="w-4 h-4"></i>
                                <?php echo $t('nav_certificates', [], $certificatum_config['titulo']); ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>


                <!-- Selector de Idioma -->
                <?php
                $idiomas_habilitados = explode(',', $instance['idiomas_habilitados'] ?? 'es_AR');
                if (count($idiomas_habilitados) > 1):
                    $idioma_actual = LanguageService::getCurrentLang();
                    $idiomas_info = LanguageService::AVAILABLE_LANGUAGES;
                    $current_params = $_GET;
                    unset($current_params['lang']);
                ?>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-sm"
                                aria-label="<?php echo $t('common.change_language', [], 'Cambiar idioma'); ?>">
                            <?php $flag_code = $idiomas_info[$idioma_actual]['flag_svg'] ?? 'un'; ?>
                            <img src="https://flagcdn.com/w40/<?php echo $flag_code; ?>.png"
                                 srcset="https://flagcdn.com/w80/<?php echo $flag_code; ?>.png 2x"
                                 width="20" height="15"
                                 alt="<?php echo $idiomas_info[$idioma_actual]['name'] ?? 'Idioma'; ?>"
                                 class="rounded-sm">
                            <i data-lucide="chevron-down" class="w-3 h-3 text-gray-500"></i>
                        </button>
                        <div x-show="open"
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                            <?php foreach ($idiomas_habilitados as $codigo):
                                $codigo = trim($codigo);
                                if (!isset($idiomas_info[$codigo])) continue;
                                $info = $idiomas_info[$codigo];
                                $is_current = ($codigo === $idioma_actual);
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

                <!-- Enlace al Administrador -->
                <?php if (!empty($instance['admin_usuario'])): ?>
                <a href="/admin/" class="hidden md:inline-flex items-center gap-2 px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white rounded-lg hover:bg-gray-900 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                    <?php echo $t('nav_admin', [], 'Admin'); ?>
                </a>
                <?php endif; ?>

                <!-- Toggle modo oscuro -->
                <button id="dark-mode-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" aria-label="<?php echo $t('common.toggle_dark_mode', [], 'Alternar modo oscuro'); ?>">
                    <i data-lucide="moon" class="w-5 h-5 text-gray-600 dark:hidden"></i>
                    <i data-lucide="sun" class="w-5 h-5 text-gray-400 hidden dark:inline"></i>
                </button>

                <!-- Menú móvil -->
                <button class="md:hidden text-gray-600 dark:text-gray-300">
                    <i data-lucide="menu"></i>
                </button>
            </div>
        </nav>
    </header>

    <main>

    <!-- Script para navegación activa en one-page -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar iconos de Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Destacar sección activa en el menú mientras se hace scroll
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');

        function highlightActiveSection() {
            let scrollPosition = window.scrollY + 100; // Offset para header sticky

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                const sectionId = section.getAttribute('id');

                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove('text-primario', 'font-bold');
                        link.classList.add('text-gray-600', 'dark:text-gray-300');

                        if (link.getAttribute('href') === '#' + sectionId) {
                            link.classList.remove('text-gray-600', 'dark:text-gray-300');
                            link.classList.add('text-primario', 'font-bold');
                        }
                    });
                }
            });
        }

        // Ejecutar al hacer scroll
        window.addEventListener('scroll', highlightActiveSection);

        // Ejecutar al cargar
        highlightActiveSection();

        // Modo oscuro toggle - La lógica está en footer.php para evitar duplicación
    });
    </script>
