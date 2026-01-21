<?php
/**
 * VERUMAX - Robots.txt Dinámico
 *
 * Este archivo genera el contenido de robots.txt dinámicamente
 * basándose en la configuración de cada institución (robots_noindex).
 *
 * Se accede mediante: /robots.txt (configurado en .htaccess)
 */

// Cargar configuración centralizada (incluye conexiones de BD)
require_once __DIR__ . '/env_loader.php';

use VERUMax\Services\DatabaseService;

// Determinar qué institución está solicitando (por dominio o subdirectorio)
$slug = null;

// Opción 1: Por subdominio (ej: sajur.verumax.com)
$host = $_SERVER['HTTP_HOST'] ?? '';
if (preg_match('/^([a-z0-9-]+)\.verumax\.com$/i', $host, $matches)) {
    $slug = $matches[1];
}

// Opción 2: Por dominio personalizado (buscar en BD)
if (!$slug && $host !== 'verumax.com' && $host !== 'www.verumax.com' && $host !== 'localhost') {
    // Buscar por dominio personalizado
    try {
        $pdo = DatabaseService::connection('general');

        $stmt = $pdo->prepare("SELECT slug FROM instances WHERE dominio = :dominio AND activo = 1");
        $stmt->execute(['dominio' => $host]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $slug = $result['slug'];
        }
    } catch (PDOException $e) {
        // Silenciar error, usar configuración por defecto
    }
}

// Opción 3: Por referer o path (para desarrollo local)
if (!$slug) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('/^\/([a-z0-9-]+)\/robots\.txt/i', $uri, $matches)) {
        $slug = $matches[1];
    }
}

// Opción 4: Parámetro GET para desarrollo local
if (!$slug && isset($_GET['inst'])) {
    $slug = $_GET['inst'];
}

// Configuración por defecto
$allow_indexing = false;
$sitemap_url = null;
$instance_name = 'VERUMax';

// Si tenemos un slug, buscar configuración en BD
if ($slug) {
    try {
        $pdo = DatabaseService::connection('general');

        $stmt = $pdo->prepare("
            SELECT slug, nombre, robots_noindex, dominio, activo
            FROM instances
            WHERE slug = :slug AND activo = 1
        ");
        $stmt->execute(['slug' => $slug]);
        $instance = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($instance) {
            // robots_noindex = 1 significa NO indexar
            // robots_noindex = 0 significa SÍ indexar
            $allow_indexing = !$instance['robots_noindex'];
            $instance_name = $instance['nombre'];

            // Determinar URL del sitemap
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            if (!empty($instance['dominio'])) {
                $sitemap_url = $protocol . $instance['dominio'] . '/sitemap.xml';
            } else {
                $sitemap_url = $protocol . $slug . '.verumax.com/sitemap.xml';
            }
        }
    } catch (PDOException $e) {
        // Silenciar error, usar configuración por defecto (no indexar)
    }
}

// Establecer content-type como texto plano
header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex'); // El archivo robots.txt no necesita indexarse

// Generar contenido de robots.txt
echo "# " . $instance_name . " - Robots.txt\n";
echo "# Generado dinámicamente por VERUMax\n";
echo "# Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "\n";

if ($allow_indexing) {
    // Permitir indexación
    echo "# Estado: INDEXACIÓN PERMITIDA\n";
    echo "\n";
    echo "User-agent: *\n";
    echo "Allow: /\n";
    echo "\n";
    echo "# Bloquear áreas administrativas y privadas\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /administrare.php\n";
    echo "Disallow: /backup/\n";
    echo "Disallow: /.claude/\n";
    echo "Disallow: /asistencias/\n";
    echo "Disallow: /src/\n";
    echo "Disallow: /vendor/\n";
    echo "Disallow: /lang/\n";
    echo "Disallow: /*.sql$\n";
    echo "Disallow: /*.log$\n";
    echo "Disallow: /*.bak$\n";
    echo "\n";
    echo "# Permitir recursos estáticos\n";
    echo "Allow: /*.css$\n";
    echo "Allow: /*.js$\n";
    echo "Allow: /*.jpg$\n";
    echo "Allow: /*.jpeg$\n";
    echo "Allow: /*.png$\n";
    echo "Allow: /*.gif$\n";
    echo "Allow: /*.webp$\n";
    echo "Allow: /*.svg$\n";
    echo "\n";
    echo "# Delay de rastreo (cortesía)\n";
    echo "Crawl-delay: 1\n";
    echo "\n";

    if ($sitemap_url) {
        echo "# Sitemap\n";
        echo "Sitemap: " . $sitemap_url . "\n";
    }
} else {
    // Bloquear indexación
    echo "# Estado: INDEXACIÓN BLOQUEADA\n";
    echo "# El administrador ha configurado este sitio para NO ser indexado.\n";
    echo "# Para habilitar la indexación, acceda al panel de administración.\n";
    echo "\n";
    echo "User-agent: *\n";
    echo "Disallow: /\n";
    echo "\n";
    echo "# Bloquear todos los rastreadores principales\n";
    echo "User-agent: Googlebot\n";
    echo "Disallow: /\n";
    echo "\n";
    echo "User-agent: Bingbot\n";
    echo "Disallow: /\n";
    echo "\n";
    echo "User-agent: Slurp\n";
    echo "Disallow: /\n";
    echo "\n";
    echo "User-agent: DuckDuckBot\n";
    echo "Disallow: /\n";
    echo "\n";
    echo "User-agent: Baiduspider\n";
    echo "Disallow: /\n";
    echo "\n";
    echo "User-agent: YandexBot\n";
    echo "Disallow: /\n";
}
