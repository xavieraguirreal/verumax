<?php
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

$mensaje_exito = '';
$mensaje_error = '';

// Procesar actualización de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_config'])) {
    // En implementación real, esto actualizaría lumen_datos.php
    $mensaje_exito = 'Funcionalidad en desarrollo: La configuración se guardaría en lumen_datos.php';
}

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración | Dashboard Lumen</title>

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
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg font-medium transition-colors">
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

                <a href="configuracion.php" class="flex items-center gap-3 px-4 py-3 bg-purple-600 rounded-lg font-medium transition-colors">
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
                    <h1 class="text-2xl font-bold text-gray-900">Configuración</h1>
                    <p class="text-sm text-gray-600">Personaliza tu portfolio y preferencias</p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-8 max-w-4xl">
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

            <form method="POST" class="space-y-6">
                <!-- Información del Portfolio -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        Información del Portfolio
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre de Marca</label>
                            <input type="text" value="<?php echo htmlspecialchars($portfolio['nombre_marca']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Artista</label>
                            <input type="text" value="<?php echo htmlspecialchars($portfolio['nombre_artista']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tagline</label>
                            <input type="text" value="<?php echo htmlspecialchars($portfolio['tagline']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Biografía</label>
                            <textarea rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"><?php echo htmlspecialchars($portfolio['biografia']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Colores y Tema -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="palette" class="w-5 h-5"></i>
                        Colores y Tema
                    </h2>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Color Principal</label>
                            <div class="flex gap-2">
                                <input type="color" value="<?php echo $portfolio['tema_color']; ?>" class="w-16 h-12 border border-gray-300 rounded-lg cursor-pointer">
                                <input type="text" value="<?php echo $portfolio['tema_color']; ?>" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Color Secundario</label>
                            <div class="flex gap-2">
                                <input type="color" value="<?php echo $portfolio['tema_secundario']; ?>" class="w-16 h-12 border border-gray-300 rounded-lg cursor-pointer">
                                <input type="text" value="<?php echo $portfolio['tema_secundario']; ?>" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" <?php echo $portfolio['dark_mode_default'] ? 'checked' : ''; ?> class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Modo oscuro por defecto</div>
                                <div class="text-xs text-gray-600">El portfolio se cargará en modo oscuro inicialmente</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Redes Sociales -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                        Redes Sociales
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i data-lucide="instagram" class="w-4 h-4 inline mr-1"></i>
                                Instagram
                            </label>
                            <input type="url" value="<?php echo htmlspecialchars($portfolio['redes']['instagram']); ?>" placeholder="https://instagram.com/tu_usuario" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i data-lucide="facebook" class="w-4 h-4 inline mr-1"></i>
                                Facebook
                            </label>
                            <input type="url" value="<?php echo htmlspecialchars($portfolio['redes']['facebook']); ?>" placeholder="https://facebook.com/tu_pagina" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i data-lucide="mail" class="w-4 h-4 inline mr-1"></i>
                                Email de Contacto
                            </label>
                            <input type="email" value="<?php echo htmlspecialchars($portfolio['redes']['email']); ?>" placeholder="tu@email.com" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i data-lucide="phone" class="w-4 h-4 inline mr-1"></i>
                                WhatsApp
                            </label>
                            <input type="tel" value="<?php echo htmlspecialchars($portfolio['redes']['whatsapp']); ?>" placeholder="+54 9 11 1234-5678" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Seguridad y Protección -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                        Seguridad y Protección
                    </h2>

                    <div class="space-y-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" <?php echo $portfolio['configuracion']['proteccion_descarga'] ? 'checked' : ''; ?> class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500 mt-1">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Protección anti-descarga</div>
                                <div class="text-xs text-gray-600">Previene el clic derecho y arrastrar imágenes</div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" <?php echo $portfolio['marca_agua']['activa'] ? 'checked' : ''; ?> class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500 mt-1">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Marca de agua</div>
                                <div class="text-xs text-gray-600">Aplica tu logo automáticamente a todas las fotos</div>
                            </div>
                        </label>

                        <?php if ($portfolio['marca_agua']['activa']): ?>
                            <div class="ml-8 space-y-3 pt-2">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Texto de marca de agua</label>
                                    <input type="text" value="<?php echo htmlspecialchars($portfolio['marca_agua']['texto']); ?>" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Posición</label>
                                    <select class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                        <option value="bottom-right" <?php echo $portfolio['marca_agua']['posicion'] === 'bottom-right' ? 'selected' : ''; ?>>Inferior Derecha</option>
                                        <option value="bottom-left">Inferior Izquierda</option>
                                        <option value="top-right">Superior Derecha</option>
                                        <option value="top-left">Superior Izquierda</option>
                                        <option value="center">Centro</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Opacidad: <?php echo $portfolio['marca_agua']['opacidad'] * 100; ?>%</label>
                                    <input type="range" min="0" max="100" value="<?php echo $portfolio['marca_agua']['opacidad'] * 100; ?>" class="w-full">
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- SEO -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="search" class="w-5 h-5"></i>
                        SEO y Metadatos
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Título SEO</label>
                            <input type="text" value="<?php echo htmlspecialchars($portfolio['seo']['titulo']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Máximo 60 caracteres recomendados</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                            <textarea rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"><?php echo htmlspecialchars($portfolio['seo']['descripcion']); ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">Máximo 160 caracteres recomendados</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Keywords (separadas por coma)</label>
                            <input type="text" value="<?php echo htmlspecialchars($portfolio['seo']['keywords']); ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" name="actualizar_config" class="flex-1 px-6 py-3 gradient-lumen text-white font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg hover:shadow-xl">
                        <i data-lucide="save" class="w-5 h-5 inline mr-2"></i>
                        Guardar Configuración
                    </button>
                    <a href="dashboard.php" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
