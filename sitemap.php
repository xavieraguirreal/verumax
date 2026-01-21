<?php
/**
 * VERUMAX - Sitemap.xml Dinámico
 *
 * Este archivo genera el contenido de sitemap.xml dinámicamente
 * basándose en la configuración de cada institución.
 *
 * Se accede mediante: /sitemap.xml (configurado en .htaccess)
 */

// Cargar configuración centralizada (incluye conexiones de BD)
require_once __DIR__ . '/env_loader.php';

use VERUMax\Services\DatabaseService;

// Determinar qué institución está solicitando (por dominio o subdirectorio)
$slug = null;
$host = $_SERVER['HTTP_HOST'] ?? '';

// Opción 1: Por subdominio (ej: sajur.verumax.com)
if (preg_match('/^([a-z0-9-]+)\.verumax\.com$/i', $host, $matches)) {
    $slug = $matches[1];
}

// Opción 2: Por dominio personalizado
if (!$slug && $host !== 'verumax.com' && $host !== 'www.verumax.com' && $host !== 'localhost') {
    try {
        $pdo = DatabaseService::connection('general');

        $stmt = $pdo->prepare("SELECT slug FROM instances WHERE dominio = :dominio AND activo = 1");
        $stmt->execute(['dominio' => $host]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $slug = $result['slug'];
        }
    } catch (PDOException $e) {
        // Silenciar error
    }
}

// Opción 3: Desarrollo local - usar sajur por defecto o parámetro
if (!$slug) {
    $slug = $_GET['inst'] ?? 'sajur';
}

// Si no hay slug válido, mostrar error
if (!$slug) {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: text/plain');
    echo "Sitemap no disponible";
    exit;
}

// Conectar a BD
try {
    $pdo = DatabaseService::connection('general');

    // Obtener información de la instancia
    $stmt = $pdo->prepare("
        SELECT slug, nombre, dominio, identitas_activo, modulo_certificatum,
               robots_noindex, fecha_actualizacion
        FROM instances
        WHERE slug = :slug AND activo = 1
    ");
    $stmt->execute(['slug' => $slug]);
    $instance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$instance) {
        header('HTTP/1.1 404 Not Found');
        header('Content-Type: text/plain');
        echo "Institución no encontrada";
        exit;
    }

    // Si robots_noindex está activo, no generar sitemap (o generar uno vacío)
    if ($instance['robots_noindex']) {
        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<!-- Sitemap deshabilitado: indexación bloqueada por configuración -->' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        exit;
    }

    // Determinar URL base
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    if (!empty($instance['dominio'])) {
        $base_url = $protocol . $instance['dominio'];
    } else {
        $base_url = $protocol . $slug . '.verumax.com';
    }

    // Fecha de última modificación
    $last_mod = date('Y-m-d', strtotime($instance['fecha_actualizacion']));

    // Construir URLs del sitemap
    $urls = [];

    // Página principal
    $urls[] = [
        'loc' => $base_url . '/',
        'lastmod' => $last_mod,
        'changefreq' => 'weekly',
        'priority' => '1.0'
    ];

    // Si Identitas está activo, agregar páginas
    if ($instance['identitas_activo']) {
        // Conectar a BD de Identitas para obtener páginas
        try {
            $pdo_identi = DatabaseService::connection('identitas');

            // Obtener id_instancia
            $stmt = $pdo_identi->prepare("SELECT id_instancia FROM identitas_instancias WHERE slug = :slug");
            $stmt->execute(['slug' => $slug]);
            $inst_identi = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($inst_identi) {
                // Obtener páginas visibles
                $stmt = $pdo_identi->prepare("
                    SELECT slug, titulo, fecha_actualizacion
                    FROM identitas_paginas
                    WHERE id_instancia = :id_instancia
                    AND activo = 1
                    ORDER BY orden
                ");
                $stmt->execute(['id_instancia' => $inst_identi['id_instancia']]);
                $paginas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($paginas as $pag) {
                    if ($pag['slug'] !== 'inicio') {
                        $urls[] = [
                            'loc' => $base_url . '/#' . $pag['slug'],
                            'lastmod' => date('Y-m-d', strtotime($pag['fecha_actualizacion'] ?? $last_mod)),
                            'changefreq' => 'monthly',
                            'priority' => '0.7'
                        ];
                    }
                }
            }
        } catch (PDOException $e) {
            // Si falla, agregar secciones por defecto
            $default_sections = ['sobre-nosotros', 'servicios', 'contacto'];
            foreach ($default_sections as $section) {
                $urls[] = [
                    'loc' => $base_url . '/#' . $section,
                    'lastmod' => $last_mod,
                    'changefreq' => 'monthly',
                    'priority' => '0.7'
                ];
            }
        }
    }

    // Si Certificatum está activo
    if ($instance['modulo_certificatum']) {
        $urls[] = [
            'loc' => $base_url . '/#certificados',
            'lastmod' => $last_mod,
            'changefreq' => 'weekly',
            'priority' => '0.9'
        ];

        $urls[] = [
            'loc' => $base_url . '/validare.php',
            'lastmod' => $last_mod,
            'changefreq' => 'monthly',
            'priority' => '0.8'
        ];
    }

} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/plain');
    // En desarrollo mostrar el error, en producción ocultarlo
    if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '.local') !== false) {
        echo "Error al generar sitemap: " . $e->getMessage();
    } else {
        echo "Error al generar sitemap";
    }
    exit;
}

// Establecer content-type como XML
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex'); // El sitemap no necesita indexarse en sí mismo

// Generar XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<!-- Sitemap generado dinámicamente por VERUMax -->' . "\n";
echo '<!-- Institución: ' . htmlspecialchars($instance['nombre']) . ' -->' . "\n";
echo '<!-- Generado: ' . date('Y-m-d H:i:s') . ' -->' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
echo '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
echo '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

foreach ($urls as $url) {
    echo "    <url>\n";
    echo "        <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
    echo "        <lastmod>" . $url['lastmod'] . "</lastmod>\n";
    echo "        <changefreq>" . $url['changefreq'] . "</changefreq>\n";
    echo "        <priority>" . $url['priority'] . "</priority>\n";
    echo "    </url>\n";
}

echo "</urlset>\n";
