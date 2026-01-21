<?php
/**
 * SCRIPT TEMPORAL - Actualizar URL del Logo
 * Convierte rutas relativas a URLs absolutas para soportar subdominios
 */

require_once __DIR__ . '/identitas/config.php';

$slug = 'sajur';
$dominio_principal = 'https://verumax.com';

try {
    $pdo_general = new PDO(
        "mysql:host=localhost;dbname=verumax_general;charset=utf8mb4",
        'verumax_general',
        '/hPfiYd6xH',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener configuración actual
    $stmt = $pdo_general->prepare("SELECT logo_url FROM instances WHERE slug = :slug");
    $stmt->execute(['slug' => $slug]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Actualizar URL del Logo</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 700px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .card { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2E7D32; margin-top: 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 4px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        td:first-child { font-weight: 600; width: 150px; }
    </style>
</head>
<body>
    <div class='card'>
        <h1>🔄 Actualizar URL del Logo</h1>";

    if ($current && !empty($current['logo_url'])) {
        $logo_url_actual = $current['logo_url'];

        echo "<h2>Estado Actual</h2>";
        echo "<table>";
        echo "<tr><td>URL actual:</td><td>" . htmlspecialchars($logo_url_actual) . "</td></tr>";

        // Verificar si ya es URL absoluta
        if (strpos($logo_url_actual, 'http') === 0) {
            echo "<tr><td>Tipo:</td><td>✅ URL absoluta (ya está correcto)</td></tr>";
            echo "</table>";

            echo "<div class='success'>";
            echo "<strong>✅ No se requiere actualización</strong><br><br>";
            echo "El logo ya usa URL absoluta y funcionará correctamente en subdominios.";
            echo "</div>";

        } else {
            // Es una ruta relativa, convertir a absoluta
            $nueva_url = $dominio_principal . $logo_url_actual;

            echo "<tr><td>Tipo:</td><td>❌ Ruta relativa (causa problemas en subdominios)</td></tr>";
            echo "<tr><td>Nueva URL:</td><td>" . htmlspecialchars($nueva_url) . "</td></tr>";
            echo "</table>";

            // Actualizar
            $stmt = $pdo_general->prepare("
                UPDATE instances
                SET logo_url = :nueva_url
                WHERE slug = :slug
            ");

            $stmt->execute([
                'nueva_url' => $nueva_url,
                'slug' => $slug
            ]);

            echo "<div class='success'>";
            echo "<strong>✅ URL actualizada correctamente</strong><br><br>";
            echo "De: <code>" . htmlspecialchars($logo_url_actual) . "</code><br>";
            echo "A: <code>" . htmlspecialchars($nueva_url) . "</code><br><br>";
            echo "El logo ahora funcionará correctamente en el subdominio sajur.verumax.com";
            echo "</div>";
        }

    } else {
        echo "<div class='info'>";
        echo "No hay logo configurado para la instancia '" . htmlspecialchars($slug) . "'";
        echo "</div>";
    }

    echo "<div class='info' style='margin-top: 30px;'>";
    echo "<strong>🗑️ ELIMINAR este archivo después de usarlo</strong><br>";
    echo "Archivo: /actualizar_logo_url.php";
    echo "</div>";

    echo "</div></body></html>";

} catch (PDOException $e) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Error</title></head><body>";
    echo "<h1>Error</h1>";
    echo "<p>Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</body></html>";
}
?>
