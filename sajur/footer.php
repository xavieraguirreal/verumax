<?php
/**
 * SAJuR Footer - Wrapper al Footer Centralizado
 * Versión: 2.0
 *
 * Este archivo es un wrapper de retrocompatibilidad que incluye el footer centralizado.
 * Para nuevos desarrollos, usar directamente: templates/shared/footer.php
 *
 * Variables esperadas (opcionales):
 * - $instance_config o $instance: Configuración de la institución
 * - $page_type: Tipo de página (identitas, certificatum, home)
 */

// Si no hay configuración cargada, cargarla
// Migrado de identitas/config.php a verumax/config.php (2026-01-05)
if (!isset($instance) && !isset($instance_config)) {
    require_once __DIR__ . '/../verumax/config.php';
    $instance_config = getInstanceConfig('sajur');
}

// Asegurar que $instance esté definido (el footer centralizado usa $instance)
if (!isset($instance)) {
    $instance = $instance_config ?? [];
}

// Establecer tipo de página si no está definido
$page_type = $page_type ?? 'certificatum';

// Incluir el footer centralizado
include __DIR__ . '/../templates/shared/footer.php';