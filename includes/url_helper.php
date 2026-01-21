<?php
/**
 * URL Helper - Generación de URLs dinámicas
 *
 * Genera URLs correctas tanto en local como en remoto
 */

/**
 * Obtiene la URL base del dominio actual
 *
 * @return string URL base (ej: http://verumax.local o https://verumax.com)
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host;
}

/**
 * Obtiene la URL del dominio principal
 * En local: verumax.local
 * En remoto: verumax.com
 *
 * @return string URL del dominio principal
 */
function getMainDomainUrl() {
    $currentHost = $_SERVER['HTTP_HOST'];

    // Si estamos en local (.local), usar verumax.local
    if (strpos($currentHost, '.local') !== false) {
        $protocol = 'http'; // Local siempre HTTP
        return $protocol . '://verumax.local';
    }

    // Si estamos en remoto, usar verumax.com
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://verumax.com';
}

/**
 * Genera URL absoluta para una ruta
 *
 * @param string $path Ruta (ej: /admin/index.php)
 * @return string URL completa
 */
function url($path = '') {
    $baseUrl = getMainDomainUrl();
    $path = ltrim($path, '/');
    return $baseUrl . '/' . $path;
}

/**
 * Genera URL del admin
 *
 * @param string $path Ruta dentro del admin (ej: index.php)
 * @return string URL del admin
 */
function adminUrl($path = '') {
    return url('admin/' . ltrim($path, '/'));
}

/**
 * Redirige a una URL
 *
 * @param string $url URL a redirigir
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Redirige al admin
 *
 * @param string $path Ruta dentro del admin
 */
function redirectToAdmin($path = '') {
    redirect(adminUrl($path));
}

/**
 * Verifica si estamos en entorno local
 *
 * @return bool
 */
function isLocalEnvironment() {
    return strpos($_SERVER['HTTP_HOST'], '.local') !== false ||
           $_SERVER['HTTP_HOST'] === 'localhost';
}

/**
 * Obtiene URL del subdominio de una institución
 *
 * @param string $slug Slug de la institución (ej: sajur)
 * @return string URL del subdominio
 */
function getInstitutionUrl($slug) {
    $currentHost = $_SERVER['HTTP_HOST'];

    // Si estamos en local
    if (isLocalEnvironment()) {
        return 'http://' . $slug . '.verumax.local';
    }

    // Si estamos en remoto
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $slug . '.verumax.com';
}
