<?php
/**
 * Configuración de SendGrid
 *
 * IMPORTANTE: En producción, usar variables de entorno en lugar de este archivo.
 * Este archivo NO debe subirse a repositorios públicos.
 *
 * Para configurar:
 * 1. Copia este archivo a sendgrid.local.php
 * 2. Edita sendgrid.local.php con tu API key real
 * 3. Agrega sendgrid.local.php a .gitignore
 */

// Intentar cargar configuración local primero
$localConfig = __DIR__ . '/sendgrid.local.php';
if (file_exists($localConfig)) {
    return include $localConfig;
}

// Configuración por defecto (desarrollo/testing)
return [
    /**
     * API Key de SendGrid
     * Obtener en: https://app.sendgrid.com/settings/api_keys
     *
     * En producción, usar variable de entorno:
     * export SENDGRID_API_KEY="SG.xxxxxxxxxxxx"
     */
    'api_key' => getenv('SENDGRID_API_KEY') ?: 'TU_API_KEY_AQUI',

    /**
     * Email remitente por defecto
     * Debe estar verificado en SendGrid
     */
    'default_from_email' => 'notificaciones@verumax.com',
    'default_from_name' => 'VERUMax',

    /**
     * Modo sandbox (para testing sin enviar emails reales)
     */
    'sandbox_mode' => false,

    /**
     * Habilitar logging detallado
     */
    'debug' => true,
];
