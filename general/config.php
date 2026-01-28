<?php
/**
 * VERUMAX GENERAL - Configuración Global
 *
 * Este archivo maneja la conexión a la base de datos verumax_general
 * y proporciona funciones para obtener la configuración general de instancias
 *
 * Base de datos: verumax_general
 * Tabla principal: instances
 */

// Evitar ejecución directa
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Importar servicios necesarios
use VERUMax\Services\DatabaseService;

/**
 * Obtiene la conexión PDO a la base de datos verumax_general
 * REFACTORIZADO: Usa DatabaseService que lee credenciales desde .env
 *
 * @return PDO Conexión a la base de datos
 */
function getGeneralDBConnection() {
    // Cargar env_loader si no está cargado
    if (!function_exists('env')) {
        require_once __DIR__ . '/../env_loader.php';
    }

    return DatabaseService::get('general');
}

/**
 * Obtiene la configuración general de una instancia
 *
 * @param string $slug Slug de la instancia (ej: 'sajur')
 * @return array|false Array con la configuración o false si no existe
 */
function getGeneralInstance($slug) {
    $pdo = getGeneralDBConnection();

    $stmt = $pdo->prepare("
        SELECT * FROM instances
        WHERE slug = :slug
        LIMIT 1
    ");

    $stmt->execute(['slug' => $slug]);
    $instance = $stmt->fetch();

    return $instance;
}

/**
 * Obtiene la configuración general de una instancia por ID
 *
 * @param int $id_instancia ID de la instancia
 * @return array|false Array con la configuración o false si no existe
 */
function getGeneralInstanceById($id_instancia) {
    $pdo = getGeneralDBConnection();

    $stmt = $pdo->prepare("
        SELECT * FROM instances
        WHERE id_instancia = :id_instancia
        LIMIT 1
    ");

    $stmt->execute(['id_instancia' => $id_instancia]);
    $instance = $stmt->fetch();

    return $instance;
}

/**
 * Obtiene todas las instancias activas
 *
 * @return array Array de instancias
 */
function getAllActiveInstances() {
    $pdo = getGeneralDBConnection();

    $stmt = $pdo->query("
        SELECT * FROM instances
        WHERE identitas_activo = 1
        ORDER BY nombre
    ");

    return $stmt->fetchAll();
}

/**
 * Verifica si un módulo está activo para una instancia
 *
 * @param string $slug Slug de la instancia
 * @param string $modulo Nombre del módulo ('identitas', 'certificatum', etc)
 * @return bool True si está activo, false si no
 */
function isModuloActivo($slug, $modulo) {
    $instance = getGeneralInstance($slug);

    if (!$instance) {
        return false;
    }

    $campo_activo = $modulo . '_activo';

    return isset($instance[$campo_activo]) && $instance[$campo_activo] == 1;
}

/**
 * Obtiene la paleta de colores de una instancia
 * (colores globales que heredan todos los módulos por default)
 *
 * @param string $slug Slug de la instancia
 * @return array Array con color_primario, color_secundario, color_acento
 */
function getGeneralPaleta($slug) {
    $instance = getGeneralInstance($slug);

    if (!$instance) {
        return [
            'color_primario' => '#2E7D32',
            'color_secundario' => '#1B5E20',
            'color_acento' => '#66BB6A'
        ];
    }

    return [
        'color_primario' => $instance['color_primario'] ?? '#2E7D32',
        'color_secundario' => $instance['color_secundario'] ?? '#1B5E20',
        'color_acento' => $instance['color_acento'] ?? '#66BB6A',
        'paleta_colores' => $instance['paleta_colores'] ?? 'verde-elegante'
    ];
}
