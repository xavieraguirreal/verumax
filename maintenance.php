<?php
/**
 * Página de Mantenimiento / En Desarrollo
 * Estilo consistente con landing de Certificatum
 */

// Autoloader PSR-4
require_once __DIR__ . '/vendor/autoload.php';

use VERUMax\Services\LanguageService;

// Constantes requeridas por el footer
define('APP_NAME', 'VERUMax');
define('APP_VERSION', '1.0.0');
define('APP_YEAR', date('Y'));

// Idiomas disponibles
$availableLangs = [
    'es_AR', 'es_BO', 'es_CL', 'es_EC', 'es_ES', 'es_PY', 'es_UY',
    'ca_ES', 'eu_ES', 'el_GR',
    'en_US',
    'pt_BR', 'pt_PT'
];
$defaultLang = 'es_AR';

// Info de idiomas para el selector
$langInfo = [
    'es_AR' => ['name' => 'Español (Argentina)', 'flag' => 'ar'],
    'es_BO' => ['name' => 'Español (Bolivia)', 'flag' => 'bo'],
    'es_CL' => ['name' => 'Español (Chile)', 'flag' => 'cl'],
    'es_EC' => ['name' => 'Español (Ecuador)', 'flag' => 'ec'],
    'es_ES' => ['name' => 'Español (España)', 'flag' => 'es'],
    'es_PY' => ['name' => 'Español (Paraguay)', 'flag' => 'py'],
    'es_UY' => ['name' => 'Español (Uruguay)', 'flag' => 'uy'],
    'ca_ES' => ['name' => 'Català (España)', 'flag' => 'es'],
    'eu_ES' => ['name' => 'Euskara (España)', 'flag' => 'es'],
    'el_GR' => ['name' => 'Ελληνικά (Greece)', 'flag' => 'gr'],
    'en_US' => ['name' => 'English (US)', 'flag' => 'us'],
    'pt_BR' => ['name' => 'Português (Brasil)', 'flag' => 'br'],
    'pt_PT' => ['name' => 'Português (Portugal)', 'flag' => 'pt'],
];

/**
 * Detecta el idioma preferido del navegador
 */
function detectBrowserLanguage(array $enabledLangs): ?string
{
    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return null;
    }

    $browserLangs = [];
    $parts = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

    foreach ($parts as $part) {
        $part = trim($part);
        if (strpos($part, ';') !== false) {
            [$lang, $q] = explode(';', $part, 2);
            $quality = (float) str_replace('q=', '', $q);
        } else {
            $lang = $part;
            $quality = 1.0;
        }
        $lang = str_replace('-', '_', trim($lang));
        $browserLangs[$lang] = $quality;
    }

    arsort($browserLangs);

    foreach (array_keys($browserLangs) as $browserLang) {
        if (in_array($browserLang, $enabledLangs)) {
            return $browserLang;
        }
        $baseLang = substr($browserLang, 0, 2);
        foreach ($enabledLangs as $enabled) {
            if (strpos($enabled, $baseLang . '_') === 0) {
                return $enabled;
            }
        }
    }

    return null;
}

// Determinar idioma: GET > cookie > navegador > default
$current_language = $defaultLang;

if (isset($_GET['lang']) && in_array($_GET['lang'], $availableLangs)) {
    $current_language = $_GET['lang'];
} elseif (isset($_COOKIE['verumax_lang']) && in_array($_COOKIE['verumax_lang'], $availableLangs)) {
    $current_language = $_COOKIE['verumax_lang'];
} else {
    $browserLang = detectBrowserLanguage($availableLangs);
    if ($browserLang) {
        $current_language = $browserLang;
    }
}

// Guardar en cookie
if (!headers_sent()) {
    setcookie('verumax_lang', $current_language, time() + (365 * 24 * 60 * 60), '/');
}

// Inicializar LanguageService
LanguageService::setLanguage($current_language);

// Cargar traducciones (común + maintenance)
$lang = LanguageService::forPage('land_maintenance');

// Función helper
$t = function($key, $default = '') use ($lang) {
    return $lang[$key] ?? $default;
};

// HTML lang attribute
$html_lang = explode('_', $current_language)[0];
?>
<!DOCTYPE html>
<html lang="<?= $html_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t('meta_title', 'VERUMax - En Desarrollo')) ?></title>

    <!-- SEO: Bloquear indexación durante desarrollo -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="googlebot" content="noindex, nofollow">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/logo-verumax-escudo.png">

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
                        }
                    }
                }
            }
        }
    </script>

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }

        .hero-bg {
            background-image:
                linear-gradient(135deg, rgba(10, 10, 10, 0.95) 0%, rgba(26, 26, 26, 0.9) 100%),
                repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(212, 175, 55, 0.03) 10px, rgba(212, 175, 55, 0.03) 20px);
        }
        .gold-gradient-bg {
            background: linear-gradient(135deg, #D4AF37 0%, #F0D377 100%);
        }
        .gold-gradient-text {
            background: linear-gradient(135deg, #D4AF37 0%, #F0D377 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float { animation: float 6s ease-in-out infinite; }
        .glow {
            box-shadow: 0 0 40px rgba(212, 175, 55, 0.3),
                        0 0 80px rgba(212, 175, 55, 0.1);
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen flex flex-col">

    <!-- Header con selector de idioma -->
    <header class="bg-black/80 backdrop-blur-md border-b border-gold/10 sticky top-0 z-50">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3">
                    <img src="/assets/images/logo-verumax-escudo.png" alt="VERUMax" class="h-10 w-auto">
                    <span class="text-xl font-bold text-gold">VERUMax</span>
                </a>

                <!-- Selector de idioma -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-800 transition-colors text-sm border border-gold/20">
                        <img src="https://flagcdn.com/w40/<?= $langInfo[$current_language]['flag'] ?>.png"
                             srcset="https://flagcdn.com/w80/<?= $langInfo[$current_language]['flag'] ?>.png 2x"
                             width="20" height="15"
                             alt="<?= $langInfo[$current_language]['name'] ?>"
                             class="rounded-sm">
                        <span class="text-gray-300 hidden sm:inline"><?= $langInfo[$current_language]['name'] ?></span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-56 bg-gray-900 rounded-lg shadow-lg border border-gold/20 py-1 z-50">
                        <?php foreach ($availableLangs as $code):
                            $info = $langInfo[$code];
                            $is_current = ($code === $current_language);
                        ?>
                            <a href="?lang=<?= $code ?>"
                               class="flex items-center gap-3 px-4 py-2 hover:bg-gray-800 transition-colors <?= $is_current ? 'bg-gray-800' : '' ?>">
                                <img src="https://flagcdn.com/w40/<?= $info['flag'] ?>.png"
                                     srcset="https://flagcdn.com/w80/<?= $info['flag'] ?>.png 2x"
                                     width="20" height="15"
                                     alt="<?= htmlspecialchars($info['name']) ?>"
                                     class="rounded-sm">
                                <span class="text-sm text-gray-200"><?= $info['name'] ?></span>
                                <?php if ($is_current): ?>
                                    <svg class="w-4 h-4 text-gold ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="flex-1 flex items-center justify-center hero-bg py-12">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">

                <!-- Logo / Escudo Animado -->
                <div class="mb-8 flex justify-center">
                    <div class="relative float">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-32 h-32 glow" viewBox="0 0 100 100">
                            <defs>
                                <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#D4AF37;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#F0D377;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                            <rect width="100" height="100" fill="#0a0a0a"/>
                            <path fill="url(#grad)" d="M50 20L30 35v20l20 15 20-15V35L50 20zm0 8l12 9v14l-12 9-12-9V37l12-9z"/>
                            <path fill="#2E7D32" d="M42 48l4 4 8-8" stroke="#ffffff" stroke-width="2" fill="none"/>
                        </svg>
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-40 h-40 border-2 border-gold/20 rounded-full pulse-slow"></div>
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-48 border border-gold/10 rounded-full pulse-slow" style="animation-delay: 1s;"></div>
                    </div>
                </div>

                <!-- Badge -->
                <div class="inline-block mb-6">
                    <span class="px-4 py-2 gold-gradient-bg text-black text-sm font-bold rounded-full uppercase tracking-wider">
                        <?= htmlspecialchars($t('badge_development', 'En Desarrollo')) ?>
                    </span>
                </div>

                <!-- Título -->
                <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight text-white">
                    <?= htmlspecialchars($t('hero_title_line1', 'Estamos Construyendo Algo')) ?>
                    <span class="block gold-gradient-text"><?= htmlspecialchars($t('hero_title_line2', 'Extraordinario')) ?></span>
                </h1>

                <!-- Descripción -->
                <p class="text-xl text-gray-300 mb-8 leading-relaxed max-w-2xl mx-auto">
                    <?= $t('hero_description', 'El ecosistema de soluciones VERUMax está en desarrollo activo.') ?>
                </p>

                <!-- Features -->
                <div class="grid md:grid-cols-4 gap-5 mb-12 max-w-4xl mx-auto">
                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 backdrop-blur">
                        <div class="text-3xl mb-3">📜</div>
                        <h3 class="text-lg font-bold text-gold mb-2"><?= htmlspecialchars($t('feature_certificatum_title', 'Certificatum')) ?></h3>
                        <p class="text-sm text-gray-400"><?= htmlspecialchars($t('feature_certificatum_desc', 'Certificados digitales verificables con QR')) ?></p>
                    </div>

                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 backdrop-blur">
                        <div class="text-3xl mb-3">✓</div>
                        <h3 class="text-lg font-bold text-gold mb-2"><?= htmlspecialchars($t('feature_credenciales_title', 'Credenciales Verificadas')) ?></h3>
                        <p class="text-sm text-gray-400"><?= htmlspecialchars($t('feature_credenciales_desc', 'Documentos infalsificables para tu institución')) ?></p>
                    </div>

                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 backdrop-blur">
                        <div class="text-3xl mb-3">🌐</div>
                        <h3 class="text-lg font-bold text-gold mb-2"><?= htmlspecialchars($t('feature_presencia_title', 'Presencia Digital')) ?></h3>
                        <p class="text-sm text-gray-400"><?= htmlspecialchars($t('feature_presencia_desc', 'Tu sitio web profesional completo')) ?></p>
                    </div>

                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 backdrop-blur">
                        <div class="text-3xl mb-3">🤖</div>
                        <h3 class="text-lg font-bold text-gold mb-2"><?= htmlspecialchars($t('feature_ia_title', 'IA Especializada')) ?></h3>
                        <p class="text-sm text-gray-400"><?= htmlspecialchars($t('feature_ia_desc', 'Asistentes inteligentes para tu negocio')) ?></p>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-gradient-to-r from-transparent via-gold/10 to-transparent border-y border-gold/20 py-6 mb-8">
                    <p class="text-lg text-gray-300 mb-2">
                        <span class="text-gold font-bold"><?= htmlspecialchars($t('launch_label', 'Lanzamiento estimado:')) ?></span> <?= htmlspecialchars($t('launch_date', 'Próximamente')) ?>
                    </p>
                    <p class="text-sm text-gray-500">
                        <?= htmlspecialchars($t('launch_description', 'Estamos puliendo cada detalle para ofrecerte la mejor experiencia')) ?>
                    </p>
                </div>

                <!-- Newsletter -->
                <div id="newsletter" class="max-w-md mx-auto mb-8">
                    <h3 class="text-lg font-semibold mb-3 text-gray-300"><?= htmlspecialchars($t('newsletter_title', '¿Querés que te avisemos cuando lancemos?')) ?></h3>

                    <?php if (isset($_GET['success'])): ?>
                    <div class="mb-4 p-4 bg-green-900/30 border border-green-500/50 rounded-lg">
                        <p class="text-green-400 font-semibold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <?= htmlspecialchars($t('newsletter_success', '¡Listo! Te avisaremos cuando lancemos.')) ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                    <div class="mb-4 p-4 bg-red-900/30 border border-red-500/50 rounded-lg">
                        <p class="text-red-400 font-semibold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <?php
                            $error = $_GET['error'];
                            if ($error === 'invalid') echo htmlspecialchars($t('newsletter_error_invalid', 'Email inválido. Verificá que esté bien escrito.'));
                            elseif ($error === 'empty') echo htmlspecialchars($t('newsletter_error_empty', 'Por favor ingresá tu email.'));
                            else echo htmlspecialchars($t('newsletter_error_generic', 'Hubo un error. Intentá nuevamente.'));
                            ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <form class="flex flex-col sm:flex-row gap-3" action="newsletter_signup.php" method="post">
                        <input type="hidden" name="lang" value="<?= htmlspecialchars($current_language) ?>">
                        <input
                            type="email"
                            name="email"
                            placeholder="<?= htmlspecialchars($t('newsletter_placeholder', 'tu@email.com')) ?>"
                            class="flex-1 px-4 py-3 bg-gray-900 border border-gold/30 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-gold"
                            required
                        >
                        <button
                            type="submit"
                            class="px-6 py-3 gold-gradient-bg text-black font-bold rounded-lg hover:opacity-90 transition-opacity whitespace-nowrap"
                        >
                            <?= htmlspecialchars($t('newsletter_btn', 'Avisarme')) ?>
                        </button>
                    </form>
                    <p class="text-xs text-gray-500 mt-2"><?= htmlspecialchars($t('newsletter_disclaimer', 'No spam. Solo el aviso de lanzamiento.')) ?></p>
                </div>

                <!-- Admin mode -->
                <?php if (isset($_GET['admin'])): ?>
                <div class="mt-8 p-4 bg-green-900/20 border border-green-500/30 rounded-lg">
                    <p class="text-green-400 font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <?= htmlspecialchars($t('admin_mode_active', 'Modo Admin Activo')) ?>
                    </p>
                    <p class="text-sm text-gray-400 mt-1"><?= htmlspecialchars($t('admin_mode_description', 'Estás viendo el sitio en modo desarrollo. Los visitantes normales ven esta página.')) ?></p>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <!-- Footer de Certificatum -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- Alpine.js para el dropdown -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
