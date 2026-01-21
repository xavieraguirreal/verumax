<?php
// Activar reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Verificar autenticación
if (!isset($_SESSION['lumen_logged_in']) || !$_SESSION['lumen_logged_in']) {
    header('Location: login.php');
    exit;
}

$usuario_nombre = $_SESSION['lumen_nombre'];
$cliente_id = $_SESSION['lumen_cliente_id'];

// Cargar datos del portfolio
require_once __DIR__ . '/../lumen_datos.php';
$portfolio = obtenerPortfolioLumen($cliente_id);

if (!$portfolio) {
    die('Error: Portfolio no encontrado');
}

// Procesar acciones
$mensaje_exito = '';
$mensaje_error = '';

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Contar fotos y galerías
$total_galerias = count($portfolio['galerias']);
$total_fotos = 0;
$galerias_publicas = 0;

foreach ($portfolio['galerias'] as $galeria) {
    if ($galeria['publica']) {
        $galerias_publicas++;
    }
    $total_fotos += count($galeria['fotos']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Lumen | <?php echo htmlspecialchars($usuario_nombre); ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .gradient-lumen {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white z-30">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 gradient-lumen rounded-xl flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="font-bold text-lg">Lumen</div>
                    <div class="text-xs text-gray-400">Dashboard</div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="p-4">
            <div class="space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 bg-purple-600 rounded-lg font-medium transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span>Inicio</span>
                </a>

                <a href="galerias.php" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg font-medium transition-colors">
                    <i data-lucide="folder" class="w-5 h-5"></i>
                    <span>Mis Galerías</span>
                </a>

                <a href="subir_fotos.php" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg font-medium transition-colors">
                    <i data-lucide="upload" class="w-5 h-5"></i>
                    <span>Subir Fotos</span>
                </a>

                <a href="configuracion.php" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg font-medium transition-colors">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                    <span>Configuración</span>
                </a>

                <div class="border-t border-gray-800 my-4"></div>

                <a href="../<?php echo $cliente_id; ?>/dashboard.php" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg font-medium transition-colors">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    <span>Dashboard Principal</span>
                </a>
            </div>
        </nav>

        <!-- User -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center font-bold">
                    <?php echo strtoupper(substr($usuario_nombre, 0, 1)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm truncate"><?php echo htmlspecialchars($usuario_nombre); ?></div>
                    <div class="text-xs text-gray-400">Cliente Lumen</div>
                </div>
            </div>
            <a href="?logout" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64 min-h-screen">
        <!-- Top Bar -->
        <div class="bg-white border-b border-gray-200 px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-sm text-gray-600">Gestiona tu portfolio profesional</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="../lumen.php?id=<?php echo $cliente_id; ?>" target="_blank" class="flex items-center gap-2 px-4 py-2 text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-lg font-medium transition-colors">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>Ver Portfolio</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-8">
            <!-- Messages -->
            <?php if ($mensaje_exito): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                    <div class="text-sm text-green-800"><?php echo htmlspecialchars($mensaje_exito); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($mensaje_error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                    <div class="text-sm text-red-800"><?php echo htmlspecialchars($mensaje_error); ?></div>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <!-- Total Galerías -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 gradient-lumen rounded-xl flex items-center justify-center">
                            <i data-lucide="folder" class="w-6 h-6 text-white"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_galerias; ?></div>
                    <div class="text-sm text-gray-600">Galerías Totales</div>
                </div>

                <!-- Galerías Públicas -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="eye" class="w-6 h-6 text-white"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $galerias_publicas; ?></div>
                    <div class="text-sm text-gray-600">Galerías Públicas</div>
                </div>

                <!-- Total Fotos -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="images" class="w-6 h-6 text-white"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1"><?php echo $total_fotos; ?></div>
                    <div class="text-sm text-gray-600">Fotos Subidas</div>
                </div>

                <!-- Almacenamiento -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="hard-drive" class="w-6 h-6 text-white"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">2.4 GB</div>
                    <div class="text-sm text-gray-600">de 50 GB usados</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Acciones Rápidas</h2>
                <div class="grid md:grid-cols-3 gap-4">
                    <a href="subir_fotos.php" class="flex items-center gap-4 p-4 border-2 border-purple-200 rounded-lg hover:border-purple-400 hover:bg-purple-50 transition-all group">
                        <div class="w-12 h-12 gradient-lumen rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="upload" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Subir Fotos</div>
                            <div class="text-sm text-gray-600">Añade nuevas imágenes</div>
                        </div>
                    </a>

                    <a href="galerias.php?accion=nueva" class="flex items-center gap-4 p-4 border-2 border-green-200 rounded-lg hover:border-green-400 hover:bg-green-50 transition-all group">
                        <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="folder-plus" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Nueva Galería</div>
                            <div class="text-sm text-gray-600">Crea una categoría</div>
                        </div>
                    </a>

                    <a href="../lumen.php?id=<?php echo $cliente_id; ?>" target="_blank" class="flex items-center gap-4 p-4 border-2 border-blue-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all group">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="external-link" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Ver Portfolio</div>
                            <div class="text-sm text-gray-600">Vista pública</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Galerías Overview -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900">Mis Galerías</h2>
                    <a href="galerias.php" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                        Ver todas →
                    </a>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <?php foreach ($portfolio['galerias'] as $galeria_id => $galeria): ?>
                        <div class="border border-gray-200 rounded-xl overflow-hidden hover:border-purple-300 hover:shadow-lg transition-all">
                            <!-- Preview Image -->
                            <div class="aspect-video bg-gradient-to-br from-purple-100 to-pink-100 relative">
                                <?php if (!empty($galeria['fotos'])): ?>
                                    <?php
                                    $primera_foto = $galeria['fotos'][0];
                                    $imagen_path = "uploads/{$cliente_id}/{$galeria_id}/{$primera_foto['archivo_original']}";
                                    ?>
                                    <img src="<?php echo $imagen_path; ?>" alt="Preview" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i data-lucide="image" class="w-12 h-12 text-gray-400"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Badge -->
                                <div class="absolute top-3 right-3">
                                    <?php if ($galeria['publica']): ?>
                                        <span class="px-2 py-1 bg-green-500 text-white text-xs font-semibold rounded-full">
                                            Pública
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-gray-500 text-white text-xs font-semibold rounded-full">
                                            Privada
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: <?php echo $galeria['color']; ?>"></div>
                                    <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($galeria['nombre']); ?></h3>
                                </div>
                                <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($galeria['descripcion']); ?></p>

                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">
                                        <i data-lucide="image" class="w-4 h-4 inline"></i>
                                        <?php echo count($galeria['fotos']); ?> fotos
                                    </span>
                                    <a href="galerias.php?galeria=<?php echo $galeria_id; ?>" class="text-purple-600 hover:text-purple-700 font-medium">
                                        Gestionar →
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
