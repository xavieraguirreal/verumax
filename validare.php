<?php
/**
 * Validación de Certificados - VERUMax
 * Proxy al motor central de Certificatum
 *
 * URL: verumax.com/validare.php
 * Acepta: GET ?codigo=VALID-XXX o POST con campo 'codigo'
 */

// Cambiar al directorio del motor central para que las rutas relativas funcionen
$certificatum_path = __DIR__ . '/certificatum';
chdir($certificatum_path);

// Incluir el motor central de validación
require_once $certificatum_path . '/validare.php';
