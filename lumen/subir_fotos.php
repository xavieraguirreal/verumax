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

// Procesar subida de fotos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_fotos'])) {
    require_once 'lumen_manager.php';

    $galeria_seleccionada = $_POST['galeria'];

    if (!isset($portfolio['galerias'][$galeria_seleccionada])) {
        $mensaje_error = 'Galería no encontrada';
    } elseif (!isset($_FILES['fotos']) || empty($_FILES['fotos']['name'][0])) {
        $mensaje_error = 'No se seleccionaron archivos';
    } else {
        $resultado = subirFotos($cliente_id, $galeria_seleccionada, $_FILES['fotos']);

        if ($resultado['exito']) {
            $mensaje_exito = $resultado['mensaje'];
            // Recargar portfolio
            $portfolio = obtenerPortfolioLumen($cliente_id);
        } else {
            $mensaje_error = $resultado['mensaje'];
        }
    }
}

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$galerias = $portfolio['galerias'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Fotos | Dashboard Lumen</title>

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

        #drop-zone {
            transition: all 0.3s ease;
        }

        #drop-zone.drag-over {
            border-color: #667eea;
            background-color: #f3f4f6;
            transform: scale(1.02);
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

                <a href="subir_fotos.php" class="flex items-center gap-3 px-4 py-3 bg-purple-600 rounded-lg font-medium transition-colors">
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
                    <h1 class="text-2xl font-bold text-gray-900">Subir Fotos</h1>
                    <p class="text-sm text-gray-600">Añade nuevas imágenes a tus galerías</p>
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

            <!-- Upload Form -->
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Seleccionar Galería -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i data-lucide="folder" class="w-4 h-4 inline mr-2"></i>
                        Seleccionar Galería
                    </label>
                    <select name="galeria" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        <option value="">-- Selecciona una galería --</option>
                        <?php foreach ($galerias as $galeria_id => $galeria): ?>
                            <option value="<?php echo $galeria_id; ?>">
                                <?php echo htmlspecialchars($galeria['nombre']); ?>
                                (<?php echo count($galeria['fotos']); ?> fotos)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Drop Zone -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                        <i data-lucide="upload" class="w-4 h-4 inline mr-2"></i>
                        Seleccionar Fotos
                    </label>

                    <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all">
                        <input type="file" id="file-input" name="fotos[]" multiple accept="image/jpeg,image/jpg,image/png" class="hidden">

                        <div class="mb-4">
                            <div class="w-20 h-20 gradient-lumen rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="image-plus" class="w-10 h-10 text-white"></i>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            Arrastra tus fotos aquí
                        </h3>
                        <p class="text-gray-600 mb-4">
                            o haz clic para seleccionar archivos
                        </p>

                        <div class="text-sm text-gray-500">
                            <p><strong>Formatos aceptados:</strong> JPG, PNG, TIFF, BMP, GIF, WebP</p>
                            <p><strong>Tamaño máximo:</strong> 50 MB por foto</p>
                            <p class="text-xs mt-1 text-gray-400">* TIFF, BMP, GIF y WebP se convierten automáticamente a JPG</p>
                            <p class="text-xs text-gray-400">* Imágenes grandes se optimizan automáticamente</p>
                        </div>
                    </div>

                    <!-- Preview Area -->
                    <div id="preview-area" class="mt-6 hidden">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Archivos seleccionados:</h4>
                        <div id="preview-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"></div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-3">
                    <button type="submit" name="subir_fotos" class="flex-1 px-6 py-3 gradient-lumen text-white font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg hover:shadow-xl">
                        <i data-lucide="upload-cloud" class="w-5 h-5 inline mr-2"></i>
                        Subir Fotos
                    </button>
                    <a href="galerias.php" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition-colors">
                        Cancelar
                    </a>
                </div>
            </form>

            <!-- Info Cards -->
            <div class="mt-8 grid md:grid-cols-3 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm text-blue-900">
                            <div class="font-semibold mb-1">Optimización Automática</div>
                            <div class="text-blue-700">Tus fotos se optimizan automáticamente para web manteniendo la calidad</div>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="shield-check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm text-green-900">
                            <div class="font-semibold mb-1">Protección Incluida</div>
                            <div class="text-green-700">Marca de agua y protección anti-descarga aplicadas automáticamente</div>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="hard-drive" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm text-purple-900">
                            <div class="font-semibold mb-1">Almacenamiento</div>
                            <div class="text-purple-700">2.4 GB usados de 50 GB disponibles</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // File input handling
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const previewArea = document.getElementById('preview-area');
        const previewGrid = document.getElementById('preview-grid');

        // Click to select
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        // Drag and drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            fileInput.files = e.dataTransfer.files;
            handleFiles(e.dataTransfer.files);
        });

        // File selection
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            if (files.length === 0) return;

            previewArea.classList.remove('hidden');
            previewGrid.innerHTML = '';

            Array.from(files).forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = (e) => {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'relative group';
                    previewItem.innerHTML = `
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                            <img src="${e.target.result}" alt="${file.name}" class="w-full h-full object-cover">
                        </div>
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                            <div class="text-white text-xs text-center px-2">
                                <div class="font-semibold truncate">${file.name}</div>
                                <div class="text-gray-300">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                            </div>
                        </div>
                    `;
                    previewGrid.appendChild(previewItem);
                };

                reader.readAsDataURL(file);
            });
        }
    </script>
</body>
</html>
