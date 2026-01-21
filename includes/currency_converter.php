<?php
/**
 * Sistema de Conversión de Monedas con Cache
 * Usa ExchangeRate-API (gratuita, 1500 requests/mes)
 */

// Cargar variables de entorno
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env_lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_lines as $line) {
        if (strpos($line, '#') === 0) continue; // Ignorar comentarios
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!defined($key) && $key === 'EXCHANGE_API_KEY') {
                define('EXCHANGE_API_KEY', $value);
            }
        }
    }
}

// Fallback si no existe en .env
if (!defined('EXCHANGE_API_KEY')) {
    define('EXCHANGE_API_KEY', '');
}

// Configuración
define('CACHE_DURATION', 86400); // 24 horas en segundos
define('CACHE_FILE', __DIR__ . '/../cache/exchange_rates.json');
define('CACHE_LOCK_FILE', __DIR__ . '/../cache/exchange_rates.lock');

/**
 * Obtiene las tasas de cambio desde la API o desde cache
 * Usa lock para evitar múltiples llamadas a la API cuando el cache vence
 * @return array Tasas de cambio con USD como base
 */
function get_exchange_rates() {
    $cached_rates = null;

    // Intentar leer cache existente
    if (file_exists(CACHE_FILE)) {
        $cached_data = json_decode(file_get_contents(CACHE_FILE), true);
        if ($cached_data && isset($cached_data['rates'])) {
            $cached_rates = $cached_data['rates'];

            // Si el cache tiene menos de 24 horas, usarlo directamente
            $cache_age = time() - filemtime(CACHE_FILE);
            if ($cache_age < CACHE_DURATION) {
                return $cached_rates;
            }
        }
    }

    // Cache vencido o no existe - intentar obtener lock exclusivo
    $lock = @fopen(CACHE_LOCK_FILE, 'c');
    if ($lock) {
        // Intentar lock no-bloqueante (LOCK_NB)
        if (flock($lock, LOCK_EX | LOCK_NB)) {
            // Tenemos el lock - verificar de nuevo el cache (otro proceso pudo actualizarlo)
            if (file_exists(CACHE_FILE)) {
                $cache_age = time() - filemtime(CACHE_FILE);
                if ($cache_age < CACHE_DURATION) {
                    $cached_data = json_decode(file_get_contents(CACHE_FILE), true);
                    if ($cached_data && isset($cached_data['rates'])) {
                        flock($lock, LOCK_UN);
                        fclose($lock);
                        return $cached_data['rates'];
                    }
                }
            }

            // Realmente necesitamos actualizar - llamar a la API
            try {
                $api_url = 'https://v6.exchangerate-api.com/v6/' . EXCHANGE_API_KEY . '/latest/USD';

                $context = stream_context_create([
                    'http' => [
                        'timeout' => 5,
                        'ignore_errors' => true
                    ]
                ]);

                $response = @file_get_contents($api_url, false, $context);

                if ($response !== false) {
                    $data = json_decode($response, true);

                    if ($data && isset($data['rates'])) {
                        // Guardar en cache
                        $cache_data = [
                            'timestamp' => time(),
                            'rates' => $data['rates']
                        ];
                        @file_put_contents(CACHE_FILE, json_encode($cache_data, JSON_PRETTY_PRINT));

                        flock($lock, LOCK_UN);
                        fclose($lock);
                        return $data['rates'];
                    }
                }
            } catch (Exception $e) {
                // Silenciar error
            }

            flock($lock, LOCK_UN);
        }
        fclose($lock);
    }

    // API falló o no obtuvimos lock: usar cache viejo si existe
    if ($cached_rates !== null) {
        return $cached_rates;
    }

    // Último recurso: fallback hardcodeado (solo si no hay cache)
    return get_fallback_rates();
}

/**
 * Tasas de fallback en caso de que la API falle
 * Última actualización: Diciembre 2025
 */
function get_fallback_rates() {
    return [
        'ARS' => 1450.00,  // Peso Argentino (actualizado dic 2025)
        'BOB' => 6.91,     // Boliviano (actualizado dic 2025)
        'PYG' => 7350.00,  // Guaraní Paraguayo (actualizado dic 2025)
        'USD' => 1.00,
        'EUR' => 0.85,     // Euro (actualizado dic 2025)
        'CLP' => 910.00,   // Peso Chileno (actualizado dic 2025)
        'UYU' => 40.00,    // Peso Uruguayo (actualizado dic 2025)
        'BRL' => 5.50,     // Real Brasileño (actualizado dic 2025)
        'MXN' => 18.00,    // Peso Mexicano (actualizado dic 2025)
    ];
}

/**
 * Convierte un precio de USD a otra moneda
 * @param float $price_usd Precio en dólares
 * @param string $currency_code Código de moneda (ARS, EUR, etc.)
 * @return float Precio convertido
 */
function convert_currency($price_usd, $currency_code = 'USD') {
    if ($currency_code === 'USD') {
        return $price_usd;
    }

    $rates = get_exchange_rates();

    if (!isset($rates[$currency_code])) {
        // Si no existe la moneda, retornar en USD
        return $price_usd;
    }

    return $price_usd * $rates[$currency_code];
}

/**
 * Formatea un precio según la moneda
 * @param float $amount Monto
 * @param string $currency_code Código de moneda
 * @param bool $round_to_thousands Redondear a miles (para ARS)
 * @return string Precio formateado
 */
function format_price($amount, $currency_code, $round_to_thousands = false) {
    $symbols = [
        'USD' => '$',
        'ARS' => 'AR$',
        'BOB' => 'Bs.',
        'PYG' => 'Gs.',
        'EUR' => '€',
        'CLP' => '$',
        'UYU' => '$',
        'BRL' => 'R$',
        'MXN' => '$',
    ];

    $symbol = isset($symbols[$currency_code]) ? $symbols[$currency_code] : $currency_code;

    // Para ARS, CLP y PYG, redondear a miles cuando se solicita
    if (in_array($currency_code, ['ARS', 'CLP', 'PYG']) && $round_to_thousands) {
        // Redondear hacia arriba al siguiente millar
        $amount = ceil($amount / 1000) * 1000;
    }

    // Para ARS, CLP, UYU y PYG, redondear (monedas sin centavos importantes)
    if (in_array($currency_code, ['ARS', 'CLP', 'UYU', 'PYG'])) {
        $formatted = number_format(round($amount, 0), 0, '.', '.');

        // Para CLP, agregar sufijo después del número
        if ($currency_code === 'CLP') {
            return $symbol . ' ' . $formatted . ' CLP';
        }

        // Para PYG (Guaraní), el símbolo va antes del número
        if ($currency_code === 'PYG') {
            return $symbol . ' ' . $formatted;
        }
    } else {
        $formatted = number_format($amount, 2, ',', '.');
    }

    return $symbol . ' ' . $formatted;
}

/**
 * Obtiene el precio en moneda local según el idioma
 * @param float $price_usd Precio en USD
 * @param string $lang_code Código de idioma (es_AR, es_CL, etc.)
 * @return array ['local' => precio_local, 'local_formatted' => formato, 'usd' => precio_usd, 'usd_formatted' => formato]
 */
function get_localized_price($price_usd, $lang_code) {
    // Mapeo de idioma a moneda
    $lang_to_currency = [
        'es_AR' => 'ARS',
        'es_BO' => 'BOB',
        'es_PY' => 'PYG',
        'es_CL' => 'CLP',
        'es_UY' => 'UYU',
        'pt_BR' => 'BRL',
        'es_MX' => 'MXN',
        'es_ES' => 'EUR',
        'ca_ES' => 'EUR',
        'eu_ES' => 'EUR',
        'pt_PT' => 'EUR',
        'el_GR' => 'EUR',
        'en_US' => 'USD',
    ];

    $currency = isset($lang_to_currency[$lang_code]) ? $lang_to_currency[$lang_code] : 'USD';
    $local_price = convert_currency($price_usd, $currency);

    // Redondear a miles para ARS, CLP y PYG (no BOB, tiene decimales)
    $round_to_thousands = in_array($currency, ['ARS', 'CLP', 'PYG']);

    return [
        'local' => $local_price,
        'local_formatted' => format_price($local_price, $currency, $round_to_thousands),
        'local_currency' => $currency,
        'usd' => $price_usd,
        'usd_formatted' => format_price($price_usd, 'USD'),
    ];
}

/**
 * Muestra el precio formateado con moneda local y descuento
 * @param float $price_usd Precio base en USD
 * @param string $lang_code Código de idioma
 * @param bool $show_both Ignorado (para compatibilidad)
 * @param int $discount_percentage Porcentaje de descuento (0-100)
 * @return string HTML con precio formateado
 */
function display_price($price_usd, $lang_code, $show_both = true, $discount_percentage = 0) {
    // Para países con moneda local (Argentina, Bolivia, Chile, Paraguay) con descuento
    if (in_array($lang_code, ['es_AR', 'es_BO', 'es_CL', 'es_PY']) && $discount_percentage > 0) {
        // Precio original (sin descuento)
        $original_data = get_localized_price($price_usd, $lang_code);

        // Precio con descuento
        $discounted_usd = $price_usd * (1 - ($discount_percentage / 100));
        $discounted_data = get_localized_price($discounted_usd, $lang_code);

        $html = '<div class="flex flex-col">';
        $html .= '<span class="text-gray-500 line-through text-lg">' . $original_data['local_formatted'] . '</span>';
        $html .= '<span class="price-main text-gold text-2xl font-bold">' . $discounted_data['local_formatted'] . '</span>';
        $html .= '</div>';

        return $html;
    }

    // Para países con moneda local sin descuento
    if (in_array($lang_code, ['es_AR', 'es_BO', 'es_CL', 'es_PY'])) {
        $price_data = get_localized_price($price_usd, $lang_code);
        return '<span class="price-main">' . $price_data['local_formatted'] . '</span>';
    }

    // Para otros países (USD)
    if ($discount_percentage > 0) {
        $discounted_usd = $price_usd * (1 - ($discount_percentage / 100));
        $html = '<div class="flex flex-col">';
        $html .= '<span class="text-gray-500 line-through text-lg">$' . number_format($price_usd, 2) . ' USD</span>';
        $html .= '<span class="price-main text-gold text-2xl font-bold">$' . number_format($discounted_usd, 2) . ' USD</span>';
        $html .= '</div>';
        return $html;
    }

    return '<span class="price-main">$' . number_format($price_usd, 2) . ' USD</span>';
}

/**
 * Obtiene el precio de alta formateado según el idioma
 * @param float $alta_price_usd Precio de alta en USD
 * @param string $lang_code Código de idioma (es_AR, es_CL, etc.)
 * @return string Precio de alta formateado con moneda local
 */
function get_alta_price_formatted($alta_price_usd, $lang_code) {
    $price_data = get_localized_price($alta_price_usd, $lang_code);

    // Redondear a miles para ARS y CLP
    $round_to_thousands = in_array($price_data['local_currency'], ['ARS', 'CLP']);

    return format_price($price_data['local'], $price_data['local_currency'], $round_to_thousands);
}

/**
 * Muestra el precio del alta con descuento y ahorro
 * @param float $alta_price_usd Precio del alta en USD
 * @param string $lang_code Código de idioma
 * @param int $discount_percentage Porcentaje de descuento
 * @return string HTML con precio original tachado, precio con descuento y ahorro
 */
function display_alta_with_savings($alta_price_usd, $lang_code, $discount_percentage) {
    $original_data = get_localized_price($alta_price_usd, $lang_code);

    $discounted_usd = $alta_price_usd * (1 - ($discount_percentage / 100));
    $discounted_data = get_localized_price($discounted_usd, $lang_code);

    $savings_usd = $alta_price_usd - $discounted_usd;
    $savings_data = get_localized_price($savings_usd, $lang_code);

    $round_to_thousands = in_array($original_data['local_currency'], ['ARS', 'CLP']);

    $original_formatted = format_price($original_data['local'], $original_data['local_currency'], $round_to_thousands);
    $discounted_formatted = format_price($discounted_data['local'], $discounted_data['local_currency'], $round_to_thousands);
    $savings_formatted = format_price($savings_data['local'], $savings_data['local_currency'], $round_to_thousands);

    return [
        'original' => $original_formatted,
        'discounted' => $discounted_formatted,
        'savings' => $savings_formatted,
    ];
}

/**
 * Muestra el precio de un plan con precio original, descuento y ahorro
 * @param float $price_usd Precio en USD
 * @param string $lang_code Código de idioma
 * @param int $discount_percentage Porcentaje de descuento
 * @param string $savings_text Texto traducido para "Ahorrás" (usar $lang['price_you_save'])
 * @return string HTML formateado con los precios
 */
function display_price_with_savings($price_usd, $lang_code, $discount_percentage, $savings_text = 'Ahorrás') {
    $original_data = get_localized_price($price_usd, $lang_code);

    $discounted_usd = $price_usd * (1 - ($discount_percentage / 100));
    $discounted_data = get_localized_price($discounted_usd, $lang_code);

    $savings_usd = $price_usd - $discounted_usd;
    $savings_data = get_localized_price($savings_usd, $lang_code);

    $round_to_thousands = in_array($original_data['local_currency'], ['ARS', 'CLP']);

    $original_formatted = format_price($original_data['local'], $original_data['local_currency'], $round_to_thousands);
    $discounted_formatted = format_price($discounted_data['local'], $discounted_data['local_currency'], $round_to_thousands);
    $savings_formatted = format_price($savings_data['local'], $savings_data['local_currency'], $round_to_thousands);

    $html = '<div class="flex flex-col">';
    $html .= '<span class="text-gray-500 line-through text-base">' . $original_formatted . '</span>';
    $html .= '<span class="text-metallic-green-light text-3xl font-bold">' . $discounted_formatted . '</span>';
    $html .= '<span class="text-green-400 text-xs">(' . htmlspecialchars($savings_text) . ' ' . $savings_formatted . ')</span>';
    $html .= '</div>';

    return $html;
}
?>
