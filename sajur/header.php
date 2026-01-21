<?php
/**
 * SAJuR Header - Wrapper al Header Centralizado
 * Versión: 2.0
 *
 * Este archivo es un wrapper de retrocompatibilidad que incluye el header centralizado.
 * Para nuevos desarrollos, usar directamente: templates/shared/header.php
 *
 * Variables esperadas (opcionales):
 * - $page_title: Título de la página
 * - $is_validation_view: Si es vista de validación (oculta menú)
 * - $instance_config o $instance: Configuración de la institución
 */

// Si no hay configuración cargada, cargarla
// Migrado de identitas/config.php a verumax/config.php (2026-01-05)
if (!isset($instance) && !isset($instance_config)) {
    require_once __DIR__ . '/../verumax/config.php';
    $instance_config = getInstanceConfig('sajur');
}

// Asegurar que $instance esté definido (el header centralizado usa $instance)
if (!isset($instance)) {
    $instance = $instance_config ?? [];
}

// Establecer tipo de página si no está definido
$page_type = $page_type ?? 'certificatum';

// Incluir el header centralizado
include __DIR__ . '/../templates/shared/header.php';