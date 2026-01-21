<?php
/**
 * LanguageService
 *
 * Servicio para gestionar traducciones multiidioma.
 * Carga archivos de idioma y traducciones de contenido desde BD.
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

use PDO;
use PDOException;

class LanguageService
{
    /**
     * Idioma actual
     */
    private static string $currentLang = 'es_AR';

    /**
     * Cache de traducciones cargadas
     */
    private static array $translations = [];

    /**
     * Cache de traducciones de contenido (BD)
     */
    private static array $contentTranslations = [];

    /**
     * Modo debug: muestra indicador visual cuando falta traducción
     * Activar con LanguageService::setDebugMode(true) o ?lang_debug=1 en URL
     */
    private static bool $debugMode = false;

    /**
     * Prefijo para indicar traducción faltante (usado en modo debug)
     */
    private const MISSING_TRANSLATION_PREFIX = '🔴 ';

    /**
     * Prefijo para indicar traducción OK del idioma actual (usado en modo debug)
     */
    private const OK_TRANSLATION_PREFIX = '🟢 ';

    /**
     * Idiomas disponibles con sus banderas
     */
    public const AVAILABLE_LANGUAGES = [
        // Español - Latinoamérica
        'es_AR' => [
            'name' => 'Español (Argentina)',
            'flag' => '🇦🇷',
            'flag_svg' => 'ar',
        ],
        'es_BO' => [
            'name' => 'Español (Bolivia)',
            'flag' => '🇧🇴',
            'flag_svg' => 'bo',
        ],
        'es_CL' => [
            'name' => 'Español (Chile)',
            'flag' => '🇨🇱',
            'flag_svg' => 'cl',
        ],
        'es_EC' => [
            'name' => 'Español (Ecuador)',
            'flag' => '🇪🇨',
            'flag_svg' => 'ec',
        ],
        'es_PY' => [
            'name' => 'Español (Paraguay)',
            'flag' => '🇵🇾',
            'flag_svg' => 'py',
        ],
        'es_UY' => [
            'name' => 'Español (Uruguay)',
            'flag' => '🇺🇾',
            'flag_svg' => 'uy',
        ],
        // Español - España
        'es_ES' => [
            'name' => 'Español (España)',
            'flag' => '🇪🇸',
            'flag_svg' => 'es',
        ],
        // Lenguas cooficiales España
        'ca_ES' => [
            'name' => 'Català (España)',
            'flag' => '🇪🇸',
            'flag_svg' => 'es',
        ],
        'eu_ES' => [
            'name' => 'Euskara (España)',
            'flag' => '🇪🇸',
            'flag_svg' => 'es',
        ],
        // Portugués
        'pt_BR' => [
            'name' => 'Português (Brasil)',
            'flag' => '🇧🇷',
            'flag_svg' => 'br',
        ],
        'pt_PT' => [
            'name' => 'Português (Portugal)',
            'flag' => '🇵🇹',
            'flag_svg' => 'pt',
        ],
        // Inglés
        'en_US' => [
            'name' => 'English (US)',
            'flag' => '🇺🇸',
            'flag_svg' => 'us',
        ],
        // Griego
        'el_GR' => [
            'name' => 'Ελληνικά (Greece)',
            'flag' => '🇬🇷',
            'flag_svg' => 'gr',
        ],
    ];

    /**
     * Ruta base de archivos de idioma
     */
    private const LANG_PATH = __DIR__ . '/../../../lang';

    /**
     * Inicializa el servicio de idioma
     *
     * @param string $institution Slug de la institución
     * @param string|null $forceLang Idioma a forzar (opcional)
     * @return string Idioma actual
     */
    public static function init(string $institution, ?string $forceLang = null): string
    {
        // 1. Obtener idiomas habilitados para la institución
        $config = InstitutionService::getConfig($institution);
        $defaultLang = $config['idioma_default'] ?? 'es_AR';
        $enabledLangs = array_map('trim', explode(',', $config['idiomas_habilitados'] ?? 'es_AR'));

        // 2. Determinar idioma a usar
        // Prioridad: forzado > cookie > sesión > navegador > default institución
        $previousLang = self::$currentLang;

        if ($forceLang && in_array($forceLang, $enabledLangs)) {
            // Parámetro GET ?lang=xx_XX - el usuario eligió manualmente
            self::$currentLang = $forceLang;
        } elseif (isset($_COOKIE['verumax_lang']) && in_array($_COOKIE['verumax_lang'], $enabledLangs)) {
            // Cookie guardada - el usuario ya visitó antes
            self::$currentLang = $_COOKIE['verumax_lang'];
        } elseif (isset($_SESSION['verumax_lang']) && in_array($_SESSION['verumax_lang'], $enabledLangs)) {
            // Sesión activa
            self::$currentLang = $_SESSION['verumax_lang'];
        } else {
            // Primera visita: detectar idioma del navegador
            $browserLang = self::detectBrowserLanguage($enabledLangs);
            self::$currentLang = $browserLang ?? $defaultLang;
        }

        // Limpiar cache si cambió el idioma
        if ($previousLang !== self::$currentLang) {
            self::$translations = [];
            self::$contentTranslations = [];
        }

        // 3. Guardar en sesión y cookie
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['verumax_lang'] = self::$currentLang;
        }

        if (!headers_sent()) {
            setcookie('verumax_lang', self::$currentLang, time() + (365 * 24 * 60 * 60), '/');
        }

        // 4. Activar modo debug si se pasa parámetro en URL
        if (isset($_GET['lang_debug']) && $_GET['lang_debug'] === '1') {
            self::$debugMode = true;
        }

        return self::$currentLang;
    }

    /**
     * Detecta el idioma preferido del navegador y lo mapea a idiomas disponibles
     *
     * @param array $enabledLangs Lista de idiomas habilitados para la institución
     * @return string|null Código de idioma detectado o null si no hay match
     */
    private static function detectBrowserLanguage(array $enabledLangs): ?string
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        // Parsear Accept-Language header (ej: "es-AR,es;q=0.9,en;q=0.8,pt;q=0.7")
        $browserLangs = [];
        $parts = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

        foreach ($parts as $part) {
            $part = trim($part);
            // Separar idioma de calidad (q=0.9)
            if (strpos($part, ';') !== false) {
                [$lang, $q] = explode(';', $part, 2);
                $quality = (float) str_replace('q=', '', $q);
            } else {
                $lang = $part;
                $quality = 1.0;
            }

            // Normalizar: es-AR → es_AR
            $lang = str_replace('-', '_', trim($lang));
            $browserLangs[$lang] = $quality;
        }

        // Ordenar por calidad descendente
        arsort($browserLangs);

        // Buscar match en idiomas habilitados
        foreach (array_keys($browserLangs) as $browserLang) {
            // Match exacto (es_AR)
            if (in_array($browserLang, $enabledLangs)) {
                return $browserLang;
            }

            // Match por código de idioma base (es → buscar es_XX)
            $baseLang = substr($browserLang, 0, 2);
            foreach ($enabledLangs as $enabled) {
                if (strpos($enabled, $baseLang . '_') === 0) {
                    return $enabled;
                }
            }
        }

        return null;
    }

    /**
     * Activa o desactiva el modo debug de traducciones
     *
     * @param bool $enabled
     * @return void
     */
    public static function setDebugMode(bool $enabled): void
    {
        self::$debugMode = $enabled;
    }

    /**
     * Verifica si el modo debug está activo
     *
     * @return bool
     */
    public static function isDebugMode(): bool
    {
        return self::$debugMode;
    }

    /**
     * Cambia el idioma actual
     *
     * @param string $lang Código de idioma (es_AR, pt_BR, etc.)
     * @return bool
     */
    public static function setLanguage(string $lang): bool
    {
        if (!isset(self::AVAILABLE_LANGUAGES[$lang])) {
            return false;
        }

        self::$currentLang = $lang;
        self::$translations = []; // Limpiar cache

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['verumax_lang'] = $lang;
        }

        if (!headers_sent()) {
            setcookie('verumax_lang', $lang, time() + (365 * 24 * 60 * 60), '/');
        }

        return true;
    }

    /**
     * Obtiene el idioma actual
     *
     * @return string
     */
    public static function getCurrentLang(): string
    {
        return self::$currentLang;
    }

    /**
     * Obtiene información del idioma actual
     *
     * @return array
     */
    public static function getCurrentLangInfo(): array
    {
        return self::AVAILABLE_LANGUAGES[self::$currentLang] ?? self::AVAILABLE_LANGUAGES['es_AR'];
    }

    /**
     * Carga un archivo de traducciones
     *
     * @param string $module Módulo (common, certificatum, admin, etc.)
     * @param string|null $lang Idioma (null = actual)
     * @return array
     */
    public static function load(string $module, ?string $lang = null): array
    {
        $lang = $lang ?? self::$currentLang;
        $cacheKey = "{$lang}_{$module}";

        if (isset(self::$translations[$cacheKey])) {
            return self::$translations[$cacheKey];
        }

        $filePath = self::LANG_PATH . "/{$lang}/{$module}.php";

        if (file_exists($filePath)) {
            self::$translations[$cacheKey] = require $filePath;
        } else {
            // Si el archivo no existe, retornar array vacío
            // El fallback a es_AR se maneja en get() para detectar traducciones faltantes
            self::$translations[$cacheKey] = [];
        }

        return self::$translations[$cacheKey];
    }

    /**
     * Carga múltiples módulos y los combina en un array $lang global
     *
     * Uso típico:
     *   $lang = LanguageService::loadMultiple(['common', 'land_certificatum']);
     *   // Luego usar: echo $lang['nav_inicio'];
     *
     * @param array $modules Lista de módulos a cargar (ej: ['common', 'land_home'])
     * @param string|null $forceLang Idioma a forzar (null = actual)
     * @return array Array combinado con todas las traducciones
     */
    public static function loadMultiple(array $modules, ?string $forceLang = null): array
    {
        $lang = $forceLang ?? self::$currentLang;
        $combined = [];

        foreach ($modules as $module) {
            // Cargar del idioma actual
            $translations = self::load($module, $lang);

            // Si no hay traducciones y no es es_AR, cargar fallback
            if (empty($translations) && $lang !== 'es_AR') {
                $translations = self::load($module, 'es_AR');
            }

            // Combinar (las claves posteriores sobrescriben las anteriores)
            $combined = array_merge($combined, $translations);
        }

        return $combined;
    }

    /**
     * Carga múltiples módulos con soporte de fallback por clave individual
     *
     * Similar a loadMultiple pero marca cada clave si usó fallback (para debug)
     * Retorna el array $lang y opcionalmente un array de claves que usaron fallback
     *
     * @param array $modules Lista de módulos a cargar
     * @param string|null $forceLang Idioma a forzar
     * @return array Array con 'lang' => traducciones, 'fallbacks' => claves que usaron fallback
     */
    public static function loadMultipleWithFallback(array $modules, ?string $forceLang = null): array
    {
        $lang = $forceLang ?? self::$currentLang;
        $combined = [];
        $fallbacks = [];

        foreach ($modules as $module) {
            // Cargar traducciones del idioma actual
            $currentTranslations = self::load($module, $lang);

            // Cargar traducciones de es_AR para fallback
            $fallbackTranslations = ($lang !== 'es_AR') ? self::load($module, 'es_AR') : [];

            // Combinar: usar traducción actual si existe, sino fallback
            foreach ($fallbackTranslations as $key => $value) {
                if (isset($currentTranslations[$key])) {
                    $combined[$key] = $currentTranslations[$key];
                } else {
                    $combined[$key] = $value;
                    $fallbacks[] = $key;
                }
            }

            // Agregar claves que solo existen en el idioma actual (no en fallback)
            foreach ($currentTranslations as $key => $value) {
                if (!isset($combined[$key])) {
                    $combined[$key] = $value;
                }
            }
        }

        // Si está en modo debug, agregar prefijos visuales
        if (self::$debugMode) {
            foreach ($combined as $key => $value) {
                if (in_array($key, $fallbacks)) {
                    $combined[$key] = self::MISSING_TRANSLATION_PREFIX . $value;
                } else {
                    $combined[$key] = self::OK_TRANSLATION_PREFIX . $value;
                }
            }
        }

        return [
            'lang' => $combined,
            'fallbacks' => $fallbacks
        ];
    }

    /**
     * Método helper para cargar idioma en páginas (común + página específica)
     *
     * Uso:
     *   $lang = LanguageService::forPage('land_certificatum');
     *   // Carga automáticamente: common + land_certificatum
     *
     * @param string $page Página específica (land_home, land_certificatum, etc.)
     * @return array Array $lang listo para usar
     */
    public static function forPage(string $page): array
    {
        $result = self::loadMultipleWithFallback(['common', $page]);
        return $result['lang'];
    }

    /**
     * Obtiene una traducción específica
     *
     * @param string $key Clave de traducción (ej: 'btn_save' o 'certificatum.cert_title')
     * @param array $params Parámetros para reemplazar (ej: [':name' => 'Juan'])
     * @param string|null $default Valor por defecto si no existe
     * @return string
     */
    public static function get(string $key, array $params = [], ?string $default = null): string
    {
        $usedFallback = false;
        $value = null;

        // Detectar si es clave con módulo (certificatum.cert_title)
        if (strpos($key, '.') !== false) {
            [$module, $subKey] = explode('.', $key, 2);

            // Cargar traducciones del idioma actual
            $translations = self::load($module, self::$currentLang);

            // Verificar si existe la traducción en el idioma actual
            if (isset($translations[$subKey])) {
                $value = $translations[$subKey];
            } elseif (self::$currentLang !== 'es_AR') {
                // Buscar en fallback (es_AR) si no es el idioma actual
                $fallbackTranslations = self::load($module, 'es_AR');
                if (isset($fallbackTranslations[$subKey])) {
                    $value = $fallbackTranslations[$subKey];
                    $usedFallback = true;
                }
            }

            // Si aún no hay valor, usar default o key
            if ($value === null) {
                $value = $default ?? $key;
            }
        } else {
            // Buscar en common
            $common = self::load('common', self::$currentLang);

            if (isset($common[$key])) {
                $value = $common[$key];
            } elseif (self::$currentLang !== 'es_AR') {
                // Buscar en fallback (es_AR)
                $fallbackCommon = self::load('common', 'es_AR');
                if (isset($fallbackCommon[$key])) {
                    $value = $fallbackCommon[$key];
                    $usedFallback = true;
                }
            }

            if ($value === null) {
                $value = $default ?? $key;
            }
        }

        // Reemplazar parámetros (soporta formato {param})
        if (!empty($params)) {
            foreach ($params as $param => $replacement) {
                // Reemplazar solo formato {param} para evitar conflictos
                // (ej: {nombre} no debe afectar {nombre_curso})
                $value = str_replace('{' . $param . '}', $replacement, $value);
            }
        }

        // En modo debug, agregar indicador visual en TODAS las traducciones
        if (self::$debugMode) {
            if ($usedFallback) {
                // 🔴 = Faltante en idioma actual, usando fallback (es_AR)
                $value = self::MISSING_TRANSLATION_PREFIX . $value;
            } else {
                // 🟢 = Traducción encontrada en el idioma actual
                $value = self::OK_TRANSLATION_PREFIX . $value;
            }
        }

        return $value;
    }

    /**
     * Alias corto para get()
     *
     * @param string $key
     * @param array $params
     * @param string|null $default
     * @return string
     */
    public static function t(string $key, array $params = [], ?string $default = null): string
    {
        return self::get($key, $params, $default);
    }

    /**
     * Obtiene traducción de contenido desde BD
     *
     * @param int $instanceId ID de la institución
     * @param string $field Campo (mision, vision, etc.)
     * @param string|null $lang Idioma (null = actual)
     * @param string|null $fallback Valor si no hay traducción
     * @param bool $isRecursiveCall Indica si es llamada recursiva (interno)
     * @return string|null
     */
    public static function getContent(int $instanceId, string $field, ?string $lang = null, ?string $fallback = null, bool $isRecursiveCall = false): ?string
    {
        $lang = $lang ?? self::$currentLang;
        $cacheKey = "{$instanceId}_{$field}_{$lang}";

        if (isset(self::$contentTranslations[$cacheKey])) {
            $value = self::$contentTranslations[$cacheKey];
            // Si es llamada recursiva (fallback) y está en modo debug, marcar
            if ($isRecursiveCall && self::$debugMode && $value !== null) {
                return self::MISSING_TRANSLATION_PREFIX . $value;
            }
            return $value;
        }

        try {
            $pdo = DatabaseService::get('general');
            $stmt = $pdo->prepare("
                SELECT contenido FROM instance_translations
                WHERE id_instancia = :id AND campo = :campo AND idioma = :idioma
            ");
            $stmt->execute([
                ':id' => $instanceId,
                ':campo' => $field,
                ':idioma' => $lang
            ]);

            $result = $stmt->fetchColumn();

            if ($result) {
                self::$contentTranslations[$cacheKey] = $result;
                return $result;
            }

            // Si no hay traducción personalizada para este idioma, usar el fallback
            // (texto del archivo de idioma) en lugar de hacer fallback a es_AR
            // Esto permite que cada idioma muestre su traducción correcta
            self::$contentTranslations[$cacheKey] = null; // Cache para evitar queries repetidas
            return $fallback;

        } catch (PDOException $e) {
            error_log("Error obteniendo traducción de contenido: " . $e->getMessage());
            return $fallback;
        }
    }

    /**
     * Guarda traducción de contenido en BD
     *
     * @param int $instanceId
     * @param string $field
     * @param string $content
     * @param string|null $lang
     * @return bool
     */
    public static function setContent(int $instanceId, string $field, string $content, ?string $lang = null): bool
    {
        $lang = $lang ?? self::$currentLang;

        try {
            $pdo = DatabaseService::get('general');
            $stmt = $pdo->prepare("
                INSERT INTO instance_translations (id_instancia, campo, idioma, contenido)
                VALUES (:id, :campo, :idioma, :contenido)
                ON DUPLICATE KEY UPDATE contenido = :contenido2, fecha_actualizacion = NOW()
            ");

            $result = $stmt->execute([
                ':id' => $instanceId,
                ':campo' => $field,
                ':idioma' => $lang,
                ':contenido' => $content,
                ':contenido2' => $content
            ]);

            // Limpiar cache
            $cacheKey = "{$instanceId}_{$field}_{$lang}";
            unset(self::$contentTranslations[$cacheKey]);

            return $result;

        } catch (PDOException $e) {
            error_log("Error guardando traducción de contenido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los idiomas habilitados para una institución
     *
     * @param string $institution Slug de la institución
     * @return array Lista de idiomas con su info
     */
    public static function getEnabledLanguages(string $institution): array
    {
        $config = InstitutionService::getConfig($institution);
        $enabledCodes = explode(',', $config['idiomas_habilitados'] ?? 'es_AR');

        $enabled = [];
        foreach ($enabledCodes as $code) {
            $code = trim($code);
            if (isset(self::AVAILABLE_LANGUAGES[$code])) {
                $enabled[$code] = self::AVAILABLE_LANGUAGES[$code];
                $enabled[$code]['is_current'] = ($code === self::$currentLang);
            }
        }

        return $enabled;
    }

    /**
     * Formatea una fecha según el idioma actual
     *
     * @param string $date Fecha en formato Y-m-d o timestamp
     * @param bool $includeDay Incluir nombre del día
     * @return string
     */
    public static function formatDate(string $date, bool $includeDay = false): string
    {
        $common = self::load('common');
        $months = $common['months'] ?? [];
        $days = $common['days'] ?? [];

        $timestamp = strtotime($date);
        $day = (int) date('j', $timestamp);
        $monthIndex = (int) date('n', $timestamp) - 1;
        $year = date('Y', $timestamp);
        $dayOfWeek = (int) date('w', $timestamp);

        $monthName = $months[$monthIndex] ?? date('F', $timestamp);

        if ($includeDay && isset($days[$dayOfWeek])) {
            return "{$days[$dayOfWeek]}, {$day} de {$monthName} de {$year}";
        }

        return "{$day} de {$monthName} de {$year}";
    }

    /**
     * Obtiene el nombre del mes según el idioma actual
     *
     * @param int $monthNumber Número del mes (1-12)
     * @return string Nombre del mes
     */
    public static function getMonthName(int $monthNumber): string
    {
        $common = self::load('common');
        $months = $common['months'] ?? [];
        $monthIndex = $monthNumber - 1;

        return $months[$monthIndex] ?? date('F', mktime(0, 0, 0, $monthNumber, 1));
    }

    /**
     * Limpia el cache de traducciones
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$translations = [];
        self::$contentTranslations = [];
    }

    /**
     * Genera un banner HTML con información del idioma actual (para modo debug)
     *
     * Incluir en las páginas con: echo LanguageService::getDebugBanner();
     *
     * @return string HTML del banner debug (vacío si no está en modo debug)
     */
    public static function getDebugBanner(): string
    {
        if (!self::$debugMode) {
            return '';
        }

        $langInfo = self::AVAILABLE_LANGUAGES[self::$currentLang] ?? [
            'name' => self::$currentLang,
            'flag' => '🏳️',
            'flag_svg' => ''
        ];

        $langCode = self::$currentLang;
        $langName = $langInfo['name'];
        $langFlag = $langInfo['flag'];

        return <<<HTML
        <div id="lang-debug-banner" style="
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #1e3a5f 0%, #2d5a87 100%);
            color: white;
            padding: 8px 16px;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 14px;
            z-index: 99999;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        ">
            <div style="display: flex; align-items: center; gap: 16px;">
                <span style="font-weight: bold;">🔧 DEBUG MODE</span>
                <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 4px;">
                    {$langFlag} <strong>{$langCode}</strong> - {$langName}
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 16px; font-size: 12px;">
                <span>🟢 = Traducción OK</span>
                <span>🔴 = Fallback (es_AR)</span>
                <button onclick="document.getElementById('lang-debug-banner').style.display='none'"
                    style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                    ✕ Cerrar
                </button>
            </div>
        </div>
        <div style="height: 44px;"></div>
HTML;
    }

    /**
     * Obtiene texto con género gramatical según el género de la persona
     *
     * Ejemplos:
     *   getGenderedText('Masculino', 'aprobad') => 'aprobado'
     *   getGenderedText('Femenino', 'aprobad') => 'aprobada'
     *   getGenderedText('No binario', 'aprobad') => 'aprobade'
     *   getGenderedText('Prefiero no especificar', 'aprobad') => 'aprobado/a'
     *
     * @param string|null $genero Género de la persona (Masculino, Femenino, No binario, Prefiero no especificar)
     * @param string $base Raíz de la palabra (ej: 'aprobad', 'formador', 'el/la')
     * @param string $tipo Tipo de palabra: 'sufijo_o' (o/a/e), 'sufijo_or' (or/ora/ore), 'articulo' (el/la/le)
     * @return string Texto con el género apropiado
     */
    public static function getGenderedText(?string $genero, string $base, string $tipo = 'sufijo_o'): string
    {
        // Normalizar género
        $genero = $genero ?? 'Prefiero no especificar';

        // Mapeo de sufijos según tipo y género
        $sufijos = [
            'sufijo_o' => [  // Para palabras que terminan en o/a (aprobado, certificado)
                'Masculino' => 'o',
                'Femenino' => 'a',
                'No binario' => 'e',
                'Prefiero no especificar' => 'o/a',
                'M' => 'o',  // Compatibilidad con valores antiguos
                'F' => 'a',
                'Otro' => 'e',
                'No especifica' => 'o/a'
            ],
            'sufijo_or' => [  // Para palabras que terminan en or/ora (formador, instructor)
                'Masculino' => 'or',
                'Femenino' => 'ora',
                'No binario' => 'ore',
                'Prefiero no especificar' => 'or/a',
                'M' => 'or',
                'F' => 'ora',
                'Otro' => 'ore',
                'No especifica' => 'or/a'
            ],
            'articulo' => [  // Para artículos (el/la)
                'Masculino' => 'el',
                'Femenino' => 'la',
                'No binario' => 'le',
                'Prefiero no especificar' => 'el/la',
                'M' => 'el',
                'F' => 'la',
                'Otro' => 'le',
                'No especifica' => 'el/la'
            ],
            'articulo_mayus' => [  // Para artículos en mayúscula
                'Masculino' => 'El',
                'Femenino' => 'La',
                'No binario' => 'Le',
                'Prefiero no especificar' => 'El/La',
                'M' => 'El',
                'F' => 'La',
                'Otro' => 'Le',
                'No especifica' => 'El/La'
            ]
        ];

        // Si el tipo no existe, usar sufijo_o por defecto
        if (!isset($sufijos[$tipo])) {
            $tipo = 'sufijo_o';
        }

        // Obtener sufijo según género
        $sufijo = $sufijos[$tipo][$genero] ?? $sufijos[$tipo]['Prefiero no especificar'];

        // Si es artículo, retornar solo el artículo
        if (str_starts_with($tipo, 'articulo')) {
            return $sufijo;
        }

        // Retornar base + sufijo
        return $base . $sufijo;
    }

    /**
     * Obtiene título con género (ej: "Formador" -> "Formadora")
     *
     * @param string|null $genero Género de la persona
     * @param string $titulo Título base en masculino (ej: 'Formador', 'Instructor')
     * @return string Título con género apropiado
     */
    public static function getGenderedTitle(?string $genero, string $titulo): string
    {
        // Detectar tipo de palabra según terminación
        if (preg_match('/(or|dor|tor|sor)$/i', $titulo)) {
            // Palabras que terminan en -or (Formador, Instructor, etc.)
            $base = preg_replace('/(or|dor|tor|sor)$/i', '', $titulo);
            $terminacion = strtolower(preg_match('/(or|dor|tor|sor)$/i', $titulo, $m) ? $m[1] : 'or');

            // Reconstruir con la terminación correcta
            $baseConTerminacion = $base . substr($terminacion, 0, -2); // Quitar 'or' del final
            return self::getGenderedText($genero, $base . substr($terminacion, 0, -2), 'sufijo_or');
        }

        // Para otras palabras, asumir sufijo_o
        if (preg_match('/[oa]$/i', $titulo)) {
            $base = substr($titulo, 0, -1);
            return self::getGenderedText($genero, $base, 'sufijo_o');
        }

        // Si no coincide con ningún patrón, retornar tal cual
        return $titulo;
    }
}
