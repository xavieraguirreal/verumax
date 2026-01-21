<?php
/**
 * Sistema Multi-idioma para OriginalisDoc
 * Detecta idioma del navegador, permite cambio manual y persiste con cookies
 */

// Idiomas disponibles
$available_languages = [
    'es_AR' => 'Español (Argentina)',
    'es_BO' => 'Español (Bolivia)',
    'es_PY' => 'Español (Paraguay)',
    'es_CL' => 'Español (Chile)',
    'es_EC' => 'Español (Ecuador)',
    'es_ES' => 'Español (España)',
    'es_UY' => 'Español (Uruguay)',
    'ca_ES' => 'Català (Espanya)',
    'eu_ES' => 'Euskara (Euskadi)',
    'en_US' => 'English (United States)',
    'pt_BR' => 'Português (Brasil)',
    'pt_PT' => 'Português (Portugal)',
    'el_GR' => 'Ελληνικά (Ελλάδα)'
];

// Idioma por defecto
$default_language = 'es_AR';

// Variable para almacenar el idioma seleccionado
$current_language = $default_language;

// 1. Verificar si hay cambio manual de idioma via GET
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $available_languages)) {
    $current_language = $_GET['lang'];
    // Guardar en cookie por 30 días
    setcookie('lang', $current_language, time() + (30 * 24 * 60 * 60), '/');
}
// 2. Si no hay GET, verificar cookie
elseif (isset($_COOKIE['lang']) && array_key_exists($_COOKIE['lang'], $available_languages)) {
    $current_language = $_COOKIE['lang'];
}
// 3. Si no hay cookie, detectar del navegador
else {
    // Obtener idiomas aceptados por el navegador
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

        foreach ($browser_languages as $lang) {
            // Limpiar el idioma (ej: "pt-BR;q=0.9" -> "pt-BR")
            $lang = strtok($lang, ';');
            $lang = str_replace('-', '_', trim($lang));

            // Verificar si tenemos ese idioma disponible
            if (array_key_exists($lang, $available_languages)) {
                $current_language = $lang;
                break;
            }

            // Intentar con solo el código de idioma (ej: "pt" -> buscar "pt_BR")
            $lang_code = substr($lang, 0, 2);
            foreach ($available_languages as $key => $value) {
                if (substr($key, 0, 2) === $lang_code) {
                    $current_language = $key;
                    break 2;
                }
            }
        }
    }

    // Guardar el idioma detectado en cookie
    setcookie('lang', $current_language, time() + (30 * 24 * 60 * 60), '/');
}

// =====================================
// SISTEMA DE FALLBACK DE TRADUCCIONES
// =====================================
// Soporta dos modos:
// 1. MODULAR: Si la página define $lang_modules antes de incluir este archivo
//    Ej: $lang_modules = ['common', 'land_certificatum'];
// 2. MONOLÍTICO (legacy): Carga el archivo único lang/{idioma}.php

$lang = []; // Inicializar array de traducciones
$lang_debug_mode = isset($_GET['lang_debug']) && $_GET['lang_debug'] === '1';

// Mapeo de idiomas a códigos visuales para debug
$lang_debug_codes = [
    'es_AR' => '🇦🇷',
    'es_BO' => '🇧🇴',
    'es_PY' => '🇵🇾',
    'es_CL' => '🇨🇱',
    'es_EC' => '🇪🇨',
    'es_ES' => '🇪🇸',
    'es_UY' => '🇺🇾',
    'ca_ES' => '🐱', // Gato para Cataluña (visible)
    'eu_ES' => '🔵', // Círculo azul para Euskadi
    'en_US' => '🇺🇸',
    'pt_BR' => '🇧🇷',
    'pt_PT' => '🇵🇹',
    'el_GR' => '🇬🇷'
];

// Mapeo alternativo con códigos de texto si emojis no funcionan
$lang_debug_text = [
    'es_AR' => '[AR]',
    'es_BO' => '[BO]',
    'es_PY' => '[PY]',
    'es_CL' => '[CL]',
    'es_EC' => '[EC]',
    'es_ES' => '[ES]',
    'es_UY' => '[UY]',
    'ca_ES' => '[CA]',
    'eu_ES' => '[EU]',
    'en_US' => '[US]',
    'pt_BR' => '[BR]',
    'pt_PT' => '[PT]',
    'el_GR' => '[GR]'
];

// Prefijo para traducciones faltantes (fallback a es_AR)
$LANG_DEBUG_PREFIX_MISSING = '⚠️[AR] ';
// Prefijo para traducciones OK (se asigna después de determinar idioma)
$LANG_DEBUG_PREFIX_OK = '';

// =====================================
// MODO MODULAR (nuevo)
// =====================================
// Si la página definió $lang_modules, usar sistema modular
if (isset($lang_modules) && is_array($lang_modules) && !empty($lang_modules)) {

    // Función helper para cargar un módulo
    $load_module = function($module, $lang_code) {
        $file = __DIR__ . '/lang/' . $lang_code . '/' . $module . '.php';
        if (file_exists($file)) {
            return require $file;
        }
        return [];
    };

    // Cargar módulos del idioma base (es_AR) como fallback
    $lang_base = [];
    foreach ($lang_modules as $module) {
        $lang_base = array_merge($lang_base, $load_module($module, $default_language));
    }

    // Si es otro idioma, cargar y hacer merge
    if ($current_language !== $default_language) {
        $lang_current = [];
        foreach ($lang_modules as $module) {
            $lang_current = array_merge($lang_current, $load_module($module, $current_language));
        }

        // Merge: idioma actual sobrescribe al base
        $lang = array_merge($lang_base, $lang_current);

        // Modo debug: marcar claves
        if ($lang_debug_mode) {
            $code = isset($lang_debug_text[$current_language])
                ? $lang_debug_text[$current_language]
                : '[' . strtoupper(substr($current_language, 3, 2)) . ']';
            $LANG_DEBUG_PREFIX_OK = '✅' . $code . ' ';

            foreach ($lang as $key => $value) {
                if (!isset($lang_current[$key])) {
                    $lang[$key] = $LANG_DEBUG_PREFIX_MISSING . $value;
                } else {
                    $lang[$key] = $LANG_DEBUG_PREFIX_OK . $value;
                }
            }
        }

        unset($lang_current);
    } else {
        // Idioma actual es el mismo que el base (modular)
        $lang = $lang_base;

        // Modo debug: marcar todas las claves como OK
        if ($lang_debug_mode) {
            $code = isset($lang_debug_text[$current_language])
                ? $lang_debug_text[$current_language]
                : '[' . strtoupper(substr($current_language, 3, 2)) . ']';
            $LANG_DEBUG_PREFIX_OK = '✅' . $code . ' ';

            foreach ($lang as $key => $value) {
                $lang[$key] = $LANG_DEBUG_PREFIX_OK . $value;
            }
        }
    }

    unset($lang_base);

// =====================================
// MODO MONOLÍTICO (legacy/fallback)
// =====================================
} else {
    // Paso 1: Cargar siempre el idioma base (es_AR) como fallback
    $base_lang_file = __DIR__ . '/lang/' . $default_language . '.php';
    if (file_exists($base_lang_file)) {
        require_once $base_lang_file;
        $lang_base = $lang; // Guardar copia del idioma base
    } else {
        $lang_base = [];
    }

    // Paso 2: Si el idioma seleccionado es diferente al base, cargar y hacer merge
    if ($current_language !== $default_language) {
        $lang_file = __DIR__ . '/lang/' . $current_language . '.php';

        if (file_exists($lang_file)) {
            $lang = []; // Resetear para cargar el nuevo idioma
            require_once $lang_file;
            $lang_current = $lang; // Guardar claves del idioma actual

            // Merge: el idioma seleccionado sobrescribe al base
            // Las claves que falten en el idioma seleccionado se mantienen del base
            $lang = array_merge($lang_base, $lang);

            // Modo debug: marcar TODAS las claves con indicador visual
            if ($lang_debug_mode) {
                // Asignar código del idioma actual (ej: ✅[BR] para portugués Brasil)
                $code = isset($lang_debug_text[$current_language])
                    ? $lang_debug_text[$current_language]
                    : '[' . strtoupper(substr($current_language, 3, 2)) . ']';
                $LANG_DEBUG_PREFIX_OK = '✅' . $code . ' ';

                foreach ($lang as $key => $value) {
                    // Si la clave NO existe en el idioma actual, viene del fallback (🇦🇷)
                    if (!isset($lang_current[$key])) {
                        $lang[$key] = $LANG_DEBUG_PREFIX_MISSING . $value;
                    } else {
                        // Si la clave SÍ existe en el idioma actual, mostrar bandera del idioma
                        $lang[$key] = $LANG_DEBUG_PREFIX_OK . $value;
                    }
                }
            }

            unset($lang_current);
        } else {
            // Si no existe el archivo, usar el idioma base
            $lang = $lang_base;
            $current_language = $default_language;
        }
    } else {
        // Idioma actual es el mismo que el base
        $lang = $lang_base;

        // Modo debug: marcar todas las claves como OK (están en el idioma base)
        if ($lang_debug_mode) {
            $code = isset($lang_debug_text[$current_language])
                ? $lang_debug_text[$current_language]
                : '[' . strtoupper(substr($current_language, 3, 2)) . ']';
            $LANG_DEBUG_PREFIX_OK = '✅' . $code . ' ';

            foreach ($lang as $key => $value) {
                $lang[$key] = $LANG_DEBUG_PREFIX_OK . $value;
            }
        }
    }

    // Liberar memoria
    unset($lang_base);
}

// Manejador de errores para claves de traducción faltantes (solo como respaldo)
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Solo manejar errores de claves de array indefinidas
    if (strpos($errstr, 'Undefined array key') !== false || strpos($errstr, 'Undefined index') !== false) {
        // Extraer el nombre de la clave del mensaje de error
        preg_match('/["\']([^"\']+)["\']/', $errstr, $matches);
        $missing_key = isset($matches[1]) ? $matches[1] : 'desconocida';

        // En producción: mostrar advertencia discreta solo en desarrollo
        // Para producción, simplemente retornar vacío silenciosamente
        if (defined('VERUMAX_DEBUG') && VERUMAX_DEBUG === true) {
            echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 8px; margin: 5px; font-family: monospace; font-size: 12px;'>";
            echo "<strong style='color: #856404;'>⚠ Traducción faltante:</strong> ";
            echo "<code>\$lang['{$missing_key}']</code> en <strong>{$GLOBALS['current_language']}</strong>";
            echo "</div>";
        }

        return true; // Evita que PHP muestre su propio error
    }

    return false; // Dejar que PHP maneje otros tipos de errores
});

// Función helper para obtener bandera como HTML con flag-icons CSS
function get_flag_emoji($lang_code) {
    $flags = [
        'es_AR' => 'ar',
        'es_BO' => 'bo',
        'es_PY' => 'py',
        'es_CL' => 'cl',
        'es_EC' => 'ec',
        'es_ES' => 'es',
        'es_UY' => 'uy',
        'ca_ES' => 'es-ct',
        'eu_ES' => 'es-pv',
        'en_US' => 'us',
        'pt_BR' => 'br',
        'pt_PT' => 'pt',
        'el_GR' => 'gr'
    ];
    $country_code = isset($flags[$lang_code]) ? $flags[$lang_code] : 'un';
    return '<span class="fi fi-' . $country_code . '" style="font-size: 1.1em; border-radius: 2px;"></span>';
}

// Función helper para obtener nombre corto del idioma
function get_lang_short_name($lang_code) {
    $names = [
        'es_AR' => 'ES-AR',
        'es_BO' => 'ES-BO',
        'es_PY' => 'ES-PY',
        'es_CL' => 'ES-CL',
        'es_EC' => 'ES-EC',
        'es_ES' => 'ES-ES',
        'es_UY' => 'ES-UY',
        'ca_ES' => 'CA',
        'eu_ES' => 'EU',
        'en_US' => 'EN',
        'pt_BR' => 'PT-BR',
        'pt_PT' => 'PT-PT',
        'el_GR' => 'EL'
    ];
    return isset($names[$lang_code]) ? $names[$lang_code] : 'EN';
}

// Función para formatear fechas según el idioma
function format_date($day, $month, $year, $lang_code) {
    $months = [
        'es_AR' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'es_BO' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'es_PY' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'es_CL' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'es_EC' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'es_ES' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'es_UY' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'ca_ES' => ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'],
        'eu_ES' => ['urtarrila', 'otsaila', 'martxoa', 'apirila', 'maiatza', 'ekaina', 'uztaila', 'abuztua', 'iraila', 'urria', 'azaroa', 'abendua'],
        'en_US' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        'pt_BR' => ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'],
        'pt_PT' => ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'],
        'el_GR' => ['Ιανουαρίου', 'Φεβρουαρίου', 'Μαρτίου', 'Απριλίου', 'Μαΐου', 'Ιουνίου', 'Ιουλίου', 'Αυγούστου', 'Σεπτεμβρίου', 'Οκτωβρίου', 'Νοεμβρίου', 'Δεκεμβρίου']
    ];

    $month_name = isset($months[$lang_code][$month - 1]) ? $months[$lang_code][$month - 1] : $months['es_AR'][$month - 1];

    // Formato según idioma
    if ($lang_code === 'en_US') {
        return "$month_name $day, $year";
    } elseif ($lang_code === 'pt_BR' || $lang_code === 'pt_PT') {
        return "$day de $month_name de $year";
    } elseif ($lang_code === 'el_GR') {
        return "$day $month_name $year";
    } else {
        return "$day de $month_name de $year";
    }
}

/**
 * Genera el banner de debug con información del idioma actual
 * Incluir justo después de <body> con: <?php echo get_lang_debug_banner(); ?>
 */
function get_lang_debug_banner() {
    global $lang_debug_mode, $current_language, $available_languages;

    if (!$lang_debug_mode) {
        return '';
    }

    $lang_name = isset($available_languages[$current_language]) ? $available_languages[$current_language] : $current_language;
    $flag_html = get_flag_emoji($current_language);

    $html = '<div id="lang-debug-banner" style="position:fixed;top:0;left:0;right:0;background:linear-gradient(90deg,#1e3a5f 0%,#2d5a87 100%);color:white;padding:10px 20px;font-family:system-ui,-apple-system,sans-serif;font-size:14px;z-index:99999;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 10px rgba(0,0,0,0.4);flex-wrap:wrap;gap:10px;">';
    $html .= '<div style="display:flex;align-items:center;gap:16px;">';
    $html .= '<span style="font-weight:bold;background:#ff6b6b;padding:4px 10px;border-radius:4px;">🔧 LANG DEBUG</span>';
    $html .= '<span style="background:rgba(255,255,255,0.2);padding:6px 14px;border-radius:6px;display:flex;align-items:center;gap:8px;">';
    $html .= $flag_html;
    $html .= '<strong style="font-size:16px;">' . htmlspecialchars($current_language) . '</strong>';
    $html .= '<span style="opacity:0.8;">(' . htmlspecialchars($lang_name) . ')</span>';
    $html .= '</span></div>';
    // Obtener código del idioma actual para la leyenda
    global $lang_debug_text;
    $current_code = isset($lang_debug_text[$current_language])
        ? $lang_debug_text[$current_language]
        : '[' . strtoupper(substr($current_language, 3, 2)) . ']';

    $html .= '<div style="display:flex;align-items:center;gap:20px;font-size:13px;">';
    $html .= '<span style="display:flex;align-items:center;gap:6px;"><span style="font-size:16px;">✅' . $current_code . '</span> = OK (' . htmlspecialchars($current_language) . ')</span>';
    $html .= '<span style="display:flex;align-items:center;gap:6px;"><span style="font-size:16px;">⚠️[AR]</span> = Fallback (es_AR)</span>';
    $html .= '<button onclick="document.getElementById(\'lang-debug-banner\').style.display=\'none\'" style="background:rgba(255,255,255,0.2);border:none;color:white;padding:6px 12px;border-radius:4px;cursor:pointer;font-size:13px;">✕ Cerrar</button>';
    $html .= '</div></div>';
    $html .= '<div style="height:52px;"></div>';

    return $html;
}
?>
