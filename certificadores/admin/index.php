<?php
/**
 * Admin Proxy - Redirige al panel de administración central
 * Generado automáticamente por VERUMax Super Admin
 *
 * Permite acceder al admin desde: certificadores.verumax.com/admin/
 */

// Definir la institución para el admin
$_SESSION['admin_institucion'] = 'certificadores';

// Redirigir al admin central
require_once __DIR__ . '/../../admin/index.php';
