<?php
/**
 * VERUMAX - Administrador de Caché
 * Utilidad para limpiar y gestionar el caché del sistema
 */

require_once 'includes/cache_helper.php';

// Seguridad: Solo permitir desde localhost o con clave
$allowed_ips = ['127.0.0.1', '::1'];
$secret_key = 'verumax2025'; // CAMBIAR EN PRODUCCIÓN por seguridad

$is_local = in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowed_ips);
$has_key = isset($_GET['key']) && $_GET['key'] === $secret_key;

if (!$is_local && !$has_key) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso Denegado - Verumax Cache</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
        <div class="max-w-md mx-auto p-8 bg-gray-800 rounded-lg shadow-xl border border-red-500">
            <div class="text-center mb-6">
                <div class="text-6xl mb-4">🔒</div>
                <h1 class="text-2xl font-bold text-red-500 mb-2">Acceso Denegado</h1>
                <p class="text-gray-400 text-sm">Se requiere clave de seguridad</p>
            </div>

            <div class="bg-gray-900 p-4 rounded-lg mb-6">
                <p class="text-sm text-gray-300 mb-3">Para acceder desde ubicación remota, agrega la clave:</p>
                <code class="block bg-black p-3 rounded text-xs text-green-400 break-all">
                    <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>&key=<?php echo $secret_key; ?>
                </code>
            </div>

            <div class="text-xs text-gray-500 space-y-2">
                <p><strong>Tu IP:</strong> <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Desconocida'); ?></p>
                <p><strong>Método de acceso:</strong>
                    <?php if (isset($_GET['key'])): ?>
                        <span class="text-red-400">Clave incorrecta</span>
                    <?php else: ?>
                        <span class="text-yellow-400">Clave no proporcionada</span>
                    <?php endif; ?>
                </p>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-700 text-center">
                <a href="/" class="text-blue-400 hover:text-blue-300 text-sm">← Volver al sitio</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// =====================================
// FUNCIÓN PARA REGENERAR CACHÉ
// =====================================
function regenerate_all_cache() {
    // Detectar el dominio actual
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . '://' . $host;

    // Lista de páginas a cachear
    $pages = [
        'index.php',
        'identitas.php',
        'certificatum.php'
    ];

    // Lista de idiomas
    $languages = ['es_AR', 'es_CL', 'pt_BR'];

    $results = [];
    $success_count = 0;
    $error_count = 0;

    // Generar caché para cada página en cada idioma
    foreach ($pages as $page) {
        foreach ($languages as $lang) {
            $url = $base_url . '/' . $page . '?lang=' . $lang;

            // Hacer request HTTP
            $start_time = microtime(true);
            $context = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'ignore_errors' => true
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            $end_time = microtime(true);
            $time_taken = round(($end_time - $start_time) * 1000, 2);

            // Verificar si fue exitoso
            if ($response !== false && strpos($http_response_header[0], '200') !== false) {
                $results[] = [
                    'url' => $page . '?lang=' . $lang,
                    'status' => 'success',
                    'time' => $time_taken,
                    'size' => strlen($response)
                ];
                $success_count++;
            } else {
                $results[] = [
                    'url' => $page . '?lang=' . $lang,
                    'status' => 'error',
                    'time' => $time_taken
                ];
                $error_count++;
            }
        }
    }

    return [
        'results' => $results,
        'success' => $success_count,
        'errors' => $error_count,
        'total' => count($results)
    ];
}

// Acción a realizar
$action = $_GET['action'] ?? 'stats';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador de Caché - Verumax</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-8 text-gray-800">⚡ Gestor de Caché - Verumax</h1>

            <?php
            // Ejecutar acción
            $message = '';
            $type = 'info';

            // Variable para resultados detallados
            $regenerate_results = null;

            switch ($action) {
                case 'regenerate_all':
                    // Limpiar caché primero
                    $count = clear_cache('all');

                    // Regenerar todas las páginas
                    $regenerate_results = regenerate_all_cache();

                    $message = "🔄 Caché regenerado: {$regenerate_results['success']} páginas cacheadas exitosamente";
                    if ($regenerate_results['errors'] > 0) {
                        $message .= " ({$regenerate_results['errors']} errores)";
                        $type = 'warning';
                    } else {
                        $type = 'success';
                    }
                    break;

                case 'clear_all':
                    $count = clear_cache('all');
                    $message = "✅ Se eliminaron $count archivos de caché (páginas y fragmentos)";
                    $type = 'success';
                    break;

                case 'clear_pages':
                    $count = clear_cache('pages');
                    $message = "✅ Se eliminaron $count páginas del caché";
                    $type = 'success';
                    break;

                case 'clear_fragments':
                    $count = clear_cache('fragments');
                    $message = "✅ Se eliminaron $count fragmentos del caché";
                    $type = 'success';
                    break;

                case 'clean_expired':
                    $count = clean_expired_cache(86400); // 24 horas
                    $message = "✅ Se eliminaron $count archivos expirados (más de 24 horas)";
                    $type = 'success';
                    break;
            }

            // Mostrar mensaje
            if ($message) {
                $colors = [
                    'success' => 'bg-green-100 border-green-400 text-green-700',
                    'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
                    'error' => 'bg-red-100 border-red-400 text-red-700',
                    'info' => 'bg-blue-100 border-blue-400 text-blue-700'
                ];
                $color = $colors[$type] ?? $colors['info'];
                echo "<div class='border-l-4 $color p-4 mb-6' role='alert'>
                        <p class='font-bold'>$message</p>
                      </div>";
            }

            // Obtener estadísticas
            $stats = get_cache_stats();
            ?>

            <!-- Estadísticas -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4 text-gray-700">📊 Estadísticas del Caché</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-3xl font-bold text-blue-600"><?php echo $stats['pages_count']; ?></div>
                        <div class="text-sm text-gray-600">Páginas cacheadas</div>
                        <div class="text-xs text-gray-500 mt-1"><?php echo $stats['pages_size_mb']; ?> MB</div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-3xl font-bold text-green-600"><?php echo $stats['fragments_count']; ?></div>
                        <div class="text-sm text-gray-600">Fragmentos cacheados</div>
                        <div class="text-xs text-gray-500 mt-1"><?php echo $stats['fragments_size_mb']; ?> MB</div>
                    </div>

                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-3xl font-bold text-purple-600"><?php echo $stats['total_files']; ?></div>
                        <div class="text-sm text-gray-600">Total archivos</div>
                        <div class="text-xs text-gray-500 mt-1"><?php echo $stats['total_size_mb']; ?> MB</div>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="flex-1">
                        <span class="text-sm font-semibold text-gray-600">Estado del caché:</span>
                        <?php if ($stats['enabled']): ?>
                            <span class="inline-block bg-green-500 text-white px-3 py-1 rounded-full text-xs ml-2">✓ Activo</span>
                        <?php else: ?>
                            <span class="inline-block bg-red-500 text-white px-3 py-1 rounded-full text-xs ml-2">✗ Desactivado</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($regenerate_results !== null): ?>
            <!-- Resultados de Regeneración -->
            <div class="bg-white shadow-md rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold mb-4 text-gray-700">📋 Resultados de Regeneración</h2>

                <div class="mb-4">
                    <div class="flex gap-4 text-sm">
                        <span class="text-green-600 font-semibold">✓ Exitosos: <?php echo $regenerate_results['success']; ?></span>
                        <?php if ($regenerate_results['errors'] > 0): ?>
                        <span class="text-red-600 font-semibold">✗ Errores: <?php echo $regenerate_results['errors']; ?></span>
                        <?php endif; ?>
                        <span class="text-gray-600">Total: <?php echo $regenerate_results['total']; ?> páginas</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Página</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamaño</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($regenerate_results['results'] as $result): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <code class="text-xs"><?php echo htmlspecialchars($result['url']); ?></code>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <?php if ($result['status'] === 'success'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            ✓ Cacheado
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            ✗ Error
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo $result['time']; ?> ms
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo isset($result['size']) ? number_format($result['size'] / 1024, 2) . ' KB' : '-'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Acciones -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-700">⚙️ Acciones</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="?action=regenerate_all<?php echo $has_key ? '&key=' . $secret_key : ''; ?>"
                       onclick="return confirm('¿Regenerar caché de todas las páginas? Esto puede tomar unos segundos.')"
                       class="block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg text-center transition-colors">
                        🔄 Regenerar Caché Completo
                    </a>

                    <a href="?action=clear_all<?php echo $has_key ? '&key=' . $secret_key : ''; ?>"
                       onclick="return confirm('¿Estás seguro de eliminar TODO el caché?')"
                       class="block bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg text-center transition-colors">
                        🗑️ Limpiar TODO el Caché
                    </a>

                    <a href="?action=clear_pages<?php echo $has_key ? '&key=' . $secret_key : ''; ?>"
                       onclick="return confirm('¿Eliminar caché de páginas?')"
                       class="block bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg text-center transition-colors">
                        📄 Limpiar Páginas
                    </a>

                    <a href="?action=clear_fragments<?php echo $has_key ? '&key=' . $secret_key : ''; ?>"
                       onclick="return confirm('¿Eliminar caché de fragmentos?')"
                       class="block bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-4 rounded-lg text-center transition-colors">
                        🧩 Limpiar Fragmentos
                    </a>

                    <a href="?action=clean_expired<?php echo $has_key ? '&key=' . $secret_key : ''; ?>"
                       class="block bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg text-center transition-colors">
                        🧹 Limpiar Expirados (>24h)
                    </a>
                </div>
            </div>

            <!-- Información -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-6">
                <h3 class="text-lg font-bold mb-3 text-gray-700">ℹ️ Información</h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li><strong>TTL de páginas:</strong> <?php echo CACHE_PAGE_TTL; ?> segundos (<?php echo round(CACHE_PAGE_TTL / 3600, 1); ?> horas)</li>
                    <li><strong>TTL de fragmentos:</strong> <?php echo CACHE_FRAGMENT_TTL; ?> segundos (<?php echo round(CACHE_FRAGMENT_TTL / 3600, 1); ?> horas)</li>
                    <li><strong>Directorio de caché:</strong> <code><?php echo CACHE_DIR; ?></code></li>
                    <li><strong>Páginas cacheadas:</strong>
                        <ul class="ml-4 mt-1">
                            <li>• index_es_AR, index_es_CL, index_pt_BR</li>
                            <li>• identitas_es_AR, identitas_es_CL, identitas_pt_BR</li>
                            <li>• certificatum_es_AR</li>
                        </ul>
                    </li>
                </ul>
            </div>

            <!-- Notas de seguridad -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-6">
                <h3 class="text-sm font-bold text-yellow-800 mb-2">⚠️ Notas de Seguridad</h3>
                <ul class="text-xs text-yellow-700 space-y-1">
                    <li>• Este archivo solo funciona desde localhost o con la clave secreta</li>
                    <li>• En producción, considera eliminar este archivo o restringir el acceso</li>
                    <li>• Cambia la variable <code>$secret_key</code> para mayor seguridad</li>
                </ul>
            </div>

            <div class="mt-6 text-center">
                <a href="index.php" class="text-blue-600 hover:underline">← Volver al sitio</a>
            </div>
        </div>
    </div>
</body>
</html>
