<?php
/**
 * Configuración Centralizada de Precios
 * Todos los precios base en USD
 */

// Precios base anuales en USD
$PRICING = [
    'essentialis' => 18,
    'premium' => 38,
    'excellens' => 98,
    'supremus' => 198,
];

// Precio de alta (setup fee) en USD
$ALTA_PRICE_USD = 100;

// Descuento actual (en porcentaje)
// 100 = 100% de descuento
$DISCOUNT_PERCENTAGE = 100;

// ============================================
// CERTIFICATUM - Certificaciones Académicas
// ============================================

// Precios de Certificatum (mensuales en USD) - PVP Lista
$CERTIFICATUM_PRICING = [
    'singularis' => 2,      // Por certificado (sin suscripción)
    'essentialis' => 30,    // 50 certificados/mes
    'premium' => 60,        // 200 certificados/mes
    'excellens' => 100,     // 1,000 certificados/mes
    'supremus' => 200,      // Certificados ilimitados
];

// Precio de alta Certificatum (setup fee) en USD
$CERTIFICATUM_ALTA_USD = 50;

// Descuento actual Certificatum (en porcentaje)
// Promo Web = 50% OFF sobre PVP Lista
// Ver certificatum/PRICING_STRATEGY.md para detalles
$CERTIFICATUM_DISCOUNT = 50;

// ============================================
// CREDENCIALIS - Credenciales de Membresía
// ============================================

// Precios de Credencialis (mensuales en USD) - PVP Lista
$CREDENCIALIS_PRICING = [
    'singularis' => 1,       // Por credencial (sin suscripción)
    'essentialis' => 20,     // 100 socios/as
    'premium' => 40,         // 500 socios/as
    'excellens' => 80,       // 2,000 socios/as
    'supremus' => 150,       // Socios/as ilimitados
];

// Precio de alta Credencialis (setup fee) en USD
$CREDENCIALIS_ALTA_USD = 30;

// Descuento actual Credencialis (en porcentaje)
// Promo Lanzamiento = 50% OFF sobre PVP Lista
$CREDENCIALIS_DISCOUNT = 50;

// ============================================
// SCRIPTA - Blog Profesional
// ============================================

// Precios de Scripta (Blog Profesional)
// Los planes Essentialis, Premium, Excellens y Supremus están incluidos en Identitas
// El Servicio Asistido es un plan adicional mensual
$SCRIPTA_PRICING = [
    'essentialis' => 'included',   // Incluido en Identitas Essentialis
    'premium' => 'included',       // Incluido en Identitas Premium
    'excellens' => 'included',     // Incluido en Identitas Excellens
    'supremus' => 'included',      // Incluido en Identitas Supremus
    'servicio_asistido_4' => 99,   // USD 99/mes (4 artículos profesionales)
    'servicio_asistido_8' => 179,  // USD 179/mes (8 artículos profesionales)
    'servicio_asistido_12' => 249  // USD 249/mes (12 artículos profesionales)
];

/**
 * Obtiene el precio con descuento aplicado (global)
 * @param float $base_price Precio base en USD
 * @return float Precio con descuento
 */
function get_discounted_price($base_price) {
    global $DISCOUNT_PERCENTAGE;
    return $base_price * (1 - ($DISCOUNT_PERCENTAGE / 100));
}

/**
 * Verifica si hay una promoción activa (global)
 * @return bool True si hay descuento activo
 */
function is_promo_active() {
    global $DISCOUNT_PERCENTAGE;
    return $DISCOUNT_PERCENTAGE > 0;
}

// ============================================
// FUNCIONES HELPER POR SOLUCIÓN
// ============================================

/**
 * Obtiene el precio con descuento para Certificatum
 * @param float $base_price Precio base en USD
 * @return float Precio con descuento
 */
function get_certificatum_discounted_price($base_price) {
    global $CERTIFICATUM_DISCOUNT;
    return $base_price * (1 - ($CERTIFICATUM_DISCOUNT / 100));
}

/**
 * Verifica si hay promoción activa en Certificatum
 * @return bool True si hay descuento activo
 */
function is_certificatum_promo_active() {
    global $CERTIFICATUM_DISCOUNT;
    return $CERTIFICATUM_DISCOUNT > 0;
}

/**
 * Obtiene el precio con descuento para Credencialis
 * @param float $base_price Precio base en USD
 * @return float Precio con descuento
 */
function get_credencialis_discounted_price($base_price) {
    global $CREDENCIALIS_DISCOUNT;
    return $base_price * (1 - ($CREDENCIALIS_DISCOUNT / 100));
}

/**
 * Verifica si hay promoción activa en Credencialis
 * @return bool True si hay descuento activo
 */
function is_credencialis_promo_active() {
    global $CREDENCIALIS_DISCOUNT;
    return $CREDENCIALIS_DISCOUNT > 0;
}

/**
 * Obtiene la configuración completa de precios para una solución
 * @param string $solution Nombre de la solución (certificatum, scripta, etc.)
 * @return array Configuración de precios
 */
function get_solution_pricing($solution) {
    global $PRICING, $ALTA_PRICE_USD, $DISCOUNT_PERCENTAGE;
    global $CERTIFICATUM_PRICING, $CERTIFICATUM_ALTA_USD, $CERTIFICATUM_DISCOUNT;
    global $CREDENCIALIS_PRICING, $CREDENCIALIS_ALTA_USD, $CREDENCIALIS_DISCOUNT;
    global $SCRIPTA_PRICING;

    switch (strtolower($solution)) {
        case 'certificatum':
            return [
                'pricing' => $CERTIFICATUM_PRICING,
                'alta' => $CERTIFICATUM_ALTA_USD,
                'discount' => $CERTIFICATUM_DISCOUNT,
            ];
        case 'credencialis':
            return [
                'pricing' => $CREDENCIALIS_PRICING,
                'alta' => $CREDENCIALIS_ALTA_USD,
                'discount' => $CREDENCIALIS_DISCOUNT,
            ];
        case 'scripta':
            return [
                'pricing' => $SCRIPTA_PRICING,
                'alta' => $ALTA_PRICE_USD,
                'discount' => $DISCOUNT_PERCENTAGE,
            ];
        default:
            return [
                'pricing' => $PRICING,
                'alta' => $ALTA_PRICE_USD,
                'discount' => $DISCOUNT_PERCENTAGE,
            ];
    }
}
?>
