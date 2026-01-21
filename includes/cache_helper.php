<?php
/**
 * VERUMAX - Sistema de Caché
 * Helper functions para caché de páginas y fragmentos
 */

// =====================================
// CONFIGURACIÓN
// =====================================
define('CACHE_ENABLED', false); // Activar/desactivar caché globalmente - ACTIVADO PARA PRODUCCIÓN
define('CACHE_DIR', __DIR__ . '/../cache/');
define('CACHE_PAGES_DIR', CACHE_DIR . 'pages/');
define('CACHE_FRAGMENTS_DIR', CACHE_DIR . 'fragments/');

// TTL por defecto (Time To Live en segundos)
define('CACHE_DEFAULT_TTL', 3600); // 1 hora
define('CACHE_PAGE_TTL', 3600);    // 1 hora para páginas completas
define('CACHE_FRAGMENT_TTL', 7200); // 2 horas para fragmentos

// =====================================
// INICIALIZACIÓN
// =====================================
/**
 * Inicializa las carpetas de caché
 */
function init_cache_directories() {
    $dirs = [CACHE_DIR, CACHE_PAGES_DIR, CACHE_FRAGMENTS_DIR];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// =====================================
// CACHÉ DE PÁGINA COMPLETA
// =====================================
/**
 * Obtiene una página cacheada
 * @param string $cache_key Clave única del caché
 * @param int $ttl Tiempo de vida en segundos (opcional)
 * @return string|false Contenido HTML o false si no existe/expiró
 */
function get_cached_page($cache_key, $ttl = null) {
    if (!CACHE_ENABLED) return false;

    // Bypass de caché con ?nocache=1 (para desarrollo/debug)
    if (isset($_GET['nocache']) && $_GET['nocache'] === '1') {
        return false;
    }

    init_cache_directories();
    $ttl = $ttl ?? CACHE_PAGE_TTL;
    $cache_file = CACHE_PAGES_DIR . md5($cache_key) . '.html';

    // Verificar si existe y no expiró
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $ttl) {
        return file_get_contents($cache_file);
    }

    return false;
}

/**
 * Guarda una página en caché
 * @param string $cache_key Clave única del caché
 * @param string $content Contenido HTML a cachear
 * @return bool True si se guardó correctamente
 */
function save_cached_page($cache_key, $content) {
    if (!CACHE_ENABLED) return false;

    // No guardar en caché si se pasó nocache=1
    if (isset($_GET['nocache']) && $_GET['nocache'] === '1') {
        return false;
    }

    init_cache_directories();
    $cache_file = CACHE_PAGES_DIR . md5($cache_key) . '.html';

    // Agregar comentario con información de caché
    $cache_info = "<!-- Cached: " . date('Y-m-d H:i:s') . " | Key: $cache_key -->\n";
    $content = $cache_info . $content;

    return file_put_contents($cache_file, $content) !== false;
}

/**
 * Wrapper para cachear una página completa automáticamente
 * @param string $cache_key Clave única del caché
 * @param int $ttl Tiempo de vida en segundos
 * @param callable $callback Función que genera el contenido
 */
function cached_page($cache_key, $ttl = null, $callback = null) {
    // Si se llama sin callback, retornar el caché si existe
    if ($callback === null) {
        $cached = get_cached_page($cache_key, $ttl);
        if ($cached) {
            echo $cached;
            exit;
        }
        ob_start();
        return;
    }

    // Con callback, ejecutar y cachear
    $cached = get_cached_page($cache_key, $ttl);
    if ($cached) {
        echo $cached;
        return;
    }

    ob_start();
    $callback();
    $output = ob_get_clean();
    save_cached_page($cache_key, $output);
    echo $output;
}

// =====================================
// CACHÉ DE FRAGMENTOS
// =====================================
/**
 * Cachea un fragmento de HTML
 * @param string $fragment_name Nombre único del fragmento
 * @param int $ttl Tiempo de vida en segundos
 * @param callable $callback Función que genera el fragmento
 */
function cache_fragment($fragment_name, $ttl, $callback) {
    if (!CACHE_ENABLED) {
        $callback();
        return;
    }

    init_cache_directories();
    $cache_file = CACHE_FRAGMENTS_DIR . md5($fragment_name) . '.html';

    // Si existe y no expiró, mostrar desde caché
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $ttl) {
        include $cache_file;
        return;
    }

    // Generar contenido nuevo
    ob_start();
    $callback();
    $output = ob_get_clean();

    // Guardar en caché
    file_put_contents($cache_file, $output);

    echo $output;
}

/**
 * Inicia el cacheo de un fragmento (uso alternativo)
 * @param string $fragment_name Nombre único del fragmento
 * @param int $ttl Tiempo de vida en segundos
 * @return bool True si se debe generar contenido, false si viene de caché
 */
function start_cache_fragment($fragment_name, $ttl = null) {
    if (!CACHE_ENABLED) {
        ob_start();
        return true;
    }

    init_cache_directories();
    $ttl = $ttl ?? CACHE_FRAGMENT_TTL;
    $cache_file = CACHE_FRAGMENTS_DIR . md5($fragment_name) . '.html';

    // Si existe y no expiró, mostrar desde caché
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $ttl) {
        include $cache_file;
        return false;
    }

    // Iniciar buffer para nuevo contenido
    ob_start();
    $GLOBALS['__cache_fragment_file'] = $cache_file;
    return true;
}

/**
 * Finaliza el cacheo de un fragmento
 */
function end_cache_fragment() {
    if (!CACHE_ENABLED || !isset($GLOBALS['__cache_fragment_file'])) {
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        return;
    }

    $output = ob_get_clean();
    $cache_file = $GLOBALS['__cache_fragment_file'];
    unset($GLOBALS['__cache_fragment_file']);

    // Guardar en caché
    file_put_contents($cache_file, $output);

    echo $output;
}

// =====================================
// LIMPIEZA Y MANTENIMIENTO
// =====================================
/**
 * Limpia todo el caché
 * @param string $type Tipo de caché a limpiar: 'all', 'pages', 'fragments'
 * @return int Número de archivos eliminados
 */
function clear_cache($type = 'all') {
    init_cache_directories();
    $count = 0;

    $dirs = [];
    if ($type === 'all' || $type === 'pages') {
        $dirs[] = CACHE_PAGES_DIR;
    }
    if ($type === 'all' || $type === 'fragments') {
        $dirs[] = CACHE_FRAGMENTS_DIR;
    }

    foreach ($dirs as $dir) {
        $files = glob($dir . '*.html');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
    }

    return $count;
}

/**
 * Elimina caché expirado
 * @param int $max_age Edad máxima en segundos (por defecto 24 horas)
 * @return int Número de archivos eliminados
 */
function clean_expired_cache($max_age = 86400) {
    init_cache_directories();
    $count = 0;
    $now = time();

    $dirs = [CACHE_PAGES_DIR, CACHE_FRAGMENTS_DIR];

    foreach ($dirs as $dir) {
        $files = glob($dir . '*.html');
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $max_age) {
                unlink($file);
                $count++;
            }
        }
    }

    return $count;
}

/**
 * Invalida caché específico por patrón
 * @param string $pattern Patrón de búsqueda (ej: 'certificatum_*')
 * @return int Número de archivos eliminados
 */
function invalidate_cache($pattern) {
    init_cache_directories();
    $count = 0;

    $dirs = [CACHE_PAGES_DIR, CACHE_FRAGMENTS_DIR];

    foreach ($dirs as $dir) {
        $files = glob($dir . '*.html');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, $pattern) !== false) {
                unlink($file);
                $count++;
            }
        }
    }

    return $count;
}

// =====================================
// UTILIDADES
// =====================================
/**
 * Obtiene estadísticas del caché
 * @return array Información sobre el caché
 */
function get_cache_stats() {
    init_cache_directories();

    $pages_files = glob(CACHE_PAGES_DIR . '*.html');
    $fragments_files = glob(CACHE_FRAGMENTS_DIR . '*.html');

    $pages_size = 0;
    foreach ($pages_files as $file) {
        $pages_size += filesize($file);
    }

    $fragments_size = 0;
    foreach ($fragments_files as $file) {
        $fragments_size += filesize($file);
    }

    return [
        'enabled' => CACHE_ENABLED,
        'pages_count' => count($pages_files),
        'pages_size' => $pages_size,
        'pages_size_mb' => round($pages_size / 1024 / 1024, 2),
        'fragments_count' => count($fragments_files),
        'fragments_size' => $fragments_size,
        'fragments_size_mb' => round($fragments_size / 1024 / 1024, 2),
        'total_files' => count($pages_files) + count($fragments_files),
        'total_size' => $pages_size + $fragments_size,
        'total_size_mb' => round(($pages_size + $fragments_size) / 1024 / 1024, 2)
    ];
}

/**
 * Genera una clave de caché basada en parámetros
 * @param string $base Nombre base
 * @param array $params Parámetros adicionales
 * @return string Clave de caché única
 */
function generate_cache_key($base, $params = []) {
    $key = $base;
    foreach ($params as $k => $v) {
        $key .= '_' . $k . '_' . $v;
    }
    return $key;
}
