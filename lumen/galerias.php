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

// Capturar mensaje de éxito desde redirect
if (isset($_GET['exito'])) {
    $mensaje_exito = $_GET['exito'];
}

// Procesar creación de galería
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_galeria'])) {
    require_once 'lumen_manager.php';

    $datos_galeria = [
        'id' => sanitizarNombre($_POST['nombre']),
        'nombre' => trim($_POST['nombre']),
        'descripcion' => trim($_POST['descripcion']),
        'color' => $_POST['color'],
        'publica' => isset($_POST['publica']),
        'icono' => $_POST['icono'] ?? 'folder'
    ];

    $resultado = crearGaleria($cliente_id, $datos_galeria);

    if ($resultado['exito']) {
        // Redirect para cerrar el modal y mostrar mensaje
        header('Location: galerias.php?exito=' . urlencode($resultado['mensaje']));
        exit;
    } else {
        $mensaje_error = $resultado['mensaje'];
    }
}

// Procesar subida de fotos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_fotos'])) {
    require_once 'lumen_manager.php';

    $galeria_seleccionada = $_POST['galeria_id'];

    if (!isset($portfolio['galerias'][$galeria_seleccionada])) {
        $mensaje_error = 'Galería no encontrada';
    } elseif (!isset($_FILES['fotos']) || empty($_FILES['fotos']['name'][0])) {
        $mensaje_error = 'No se seleccionaron archivos';
    } else {
        $resultado = subirFotos($cliente_id, $galeria_seleccionada, $_FILES['fotos']);

        if ($resultado['exito']) {
            // Redirect para cerrar modal y mostrar mensaje
            header('Location: galerias.php?galeria=' . urlencode($galeria_seleccionada) . '&exito=' . urlencode($resultado['mensaje']));
            exit;
        } else {
            $mensaje_error = $resultado['mensaje'];
        }
    }
}

// Procesar eliminación de foto
if (isset($_GET['eliminar_foto']) && isset($_GET['galeria'])) {
    require_once 'lumen_manager.php';

    $resultado = eliminarFoto($cliente_id, $_GET['galeria'], $_GET['eliminar_foto']);

    if ($resultado['exito']) {
        $mensaje_exito = $resultado['mensaje'];
        $portfolio = obtenerPortfolioLumen($cliente_id);
    } else {
        $mensaje_error = $resultado['mensaje'];
    }
}

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$galerias = $portfolio['galerias'];
$galeria_actual = isset($_GET['galeria']) ? $_GET['galeria'] : null;
$mostrar_modal = isset($_GET['accion']) && $_GET['accion'] === 'nueva';
$mostrar_modal_subir = isset($_GET['accion']) && $_GET['accion'] === 'subir' && $galeria_actual;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Galerías | Dashboard Lumen</title>

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

                <a href="galerias.php" class="flex items-center gap-3 px-4 py-3 bg-purple-600 rounded-lg font-medium transition-colors">
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
                    <h1 class="text-2xl font-bold text-gray-900">Mis Galerías</h1>
                    <p class="text-sm text-gray-600">Gestiona tus galerías y fotos</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="?accion=nueva" class="flex items-center gap-2 px-4 py-2 gradient-lumen text-white rounded-lg font-medium hover:opacity-90 transition-opacity">
                        <i data-lucide="folder-plus" class="w-4 h-4"></i>
                        <span>Nueva Galería</span>
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

            <?php if (!$galeria_actual): ?>
                <!-- Vista de Lista de Galerías -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($galerias as $galeria_id => $galeria): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:border-purple-300 hover:shadow-lg transition-all">
                            <!-- Header con Preview -->
                            <div class="aspect-video bg-gradient-to-br from-purple-100 to-pink-100 relative">
                                <?php if (!empty($galeria['fotos'])): ?>
                                    <?php
                                    $primera_foto = $galeria['fotos'][0];
                                    $imagen_path = "uploads/{$cliente_id}/{$galeria_id}/{$primera_foto['archivo_original']}";
                                    ?>
                                    <img src="<?php echo $imagen_path; ?>" alt="Preview" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i data-lucide="image" class="w-16 h-16 text-gray-400"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Badge de estado -->
                                <div class="absolute top-3 right-3">
                                    <?php if ($galeria['publica']): ?>
                                        <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full shadow-lg">
                                            <i data-lucide="eye" class="w-3 h-3 inline mr-1"></i>
                                            Pública
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-gray-500 text-white text-xs font-semibold rounded-full shadow-lg">
                                            <i data-lucide="eye-off" class="w-3 h-3 inline mr-1"></i>
                                            Privada
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Contador de fotos -->
                                <div class="absolute bottom-3 left-3">
                                    <span class="px-3 py-1 bg-black/60 backdrop-blur-sm text-white text-xs font-semibold rounded-full">
                                        <i data-lucide="images" class="w-3 h-3 inline mr-1"></i>
                                        <?php echo count($galeria['fotos']); ?> fotos
                                    </span>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-5">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: <?php echo $galeria['color']; ?>"></div>
                                    <h3 class="font-bold text-gray-900 text-lg"><?php echo htmlspecialchars($galeria['nombre']); ?></h3>
                                </div>

                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                    <?php echo htmlspecialchars($galeria['descripcion']); ?>
                                </p>

                                <!-- Fecha -->
                                <div class="text-xs text-gray-500 mb-4">
                                    <i data-lucide="calendar" class="w-3 h-3 inline mr-1"></i>
                                    Creada: <?php echo $galeria['fecha_creacion']; ?>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2">
                                    <a href="?galeria=<?php echo $galeria_id; ?>" class="flex-1 px-4 py-2 gradient-lumen text-white text-center rounded-lg font-medium hover:opacity-90 transition-opacity text-sm">
                                        Ver Galería
                                    </a>

                                    <button onclick="alert('Funcionalidad en desarrollo: Editar galería')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        <i data-lucide="settings" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Card para crear nueva galería -->
                    <a href="?accion=nueva" class="bg-white rounded-xl shadow-sm border-2 border-dashed border-gray-300 hover:border-purple-400 hover:bg-purple-50 transition-all p-8 flex flex-col items-center justify-center gap-4 min-h-[300px] group">
                        <div class="w-16 h-16 gradient-lumen rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i data-lucide="plus" class="w-8 h-8 text-white"></i>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-gray-900 mb-1">Nueva Galería</div>
                            <div class="text-sm text-gray-600">Crea una nueva categoría</div>
                        </div>
                    </a>
                </div>

            <?php else: ?>
                <!-- Vista de Detalle de Galería -->
                <?php
                if (!isset($galerias[$galeria_actual])) {
                    echo '<div class="text-center py-12"><p class="text-gray-600">Galería no encontrada</p></div>';
                } else {
                    $galeria = $galerias[$galeria_actual];
                ?>
                    <!-- Breadcrumb -->
                    <div class="mb-6">
                        <a href="galerias.php" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-700 font-medium">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Volver a Galerías</span>
                        </a>
                    </div>

                    <!-- Header de Galería -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center" style="background-color: <?php echo $galeria['color']; ?>20;">
                                    <i data-lucide="folder" class="w-8 h-8" style="color: <?php echo $galeria['color']; ?>"></i>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($galeria['nombre']); ?></h2>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($galeria['descripcion']); ?></p>
                                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                        <span>
                                            <i data-lucide="images" class="w-4 h-4 inline"></i>
                                            <?php echo count($galeria['fotos']); ?> fotos
                                        </span>
                                        <span>
                                            <i data-lucide="calendar" class="w-4 h-4 inline"></i>
                                            <?php echo $galeria['fecha_creacion']; ?>
                                        </span>
                                        <?php if ($galeria['publica']): ?>
                                            <span class="text-green-600 font-medium">
                                                <i data-lucide="eye" class="w-4 h-4 inline"></i>
                                                Pública
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-600 font-medium">
                                                <i data-lucide="eye-off" class="w-4 h-4 inline"></i>
                                                Privada
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="?galeria=<?php echo $galeria_actual; ?>&accion=subir" class="px-4 py-2 gradient-lumen text-white rounded-lg font-medium hover:opacity-90 transition-opacity">
                                    <i data-lucide="upload" class="w-4 h-4 inline mr-2"></i>
                                    Subir Fotos
                                </a>
                                <button onclick="alert('Funcionalidad en desarrollo: Configurar galería')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    <i data-lucide="settings" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Grid de Fotos -->
                    <?php if (empty($galeria['fotos'])): ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="image" class="w-10 h-10 text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">No hay fotos en esta galería</h3>
                            <p class="text-gray-600 mb-6">Comienza subiendo tus primeras imágenes</p>
                            <a href="?galeria=<?php echo $galeria_actual; ?>&accion=subir" class="inline-flex items-center gap-2 px-6 py-3 gradient-lumen text-white rounded-lg font-medium hover:opacity-90 transition-opacity">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Subir Fotos Ahora
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php foreach ($galeria['fotos'] as $index => $foto): ?>
                                <div class="group relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all">
                                    <!-- Imagen -->
                                    <div class="aspect-square bg-gray-100">
                                        <?php $imagen_path = "uploads/{$cliente_id}/{$galeria_actual}/{$foto['archivo_original']}"; ?>
                                        <img src="<?php echo $imagen_path; ?>" alt="<?php echo htmlspecialchars($foto['titulo']); ?>" class="w-full h-full object-cover">
                                    </div>

                                    <!-- Overlay con info -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-4">
                                        <h4 class="text-white font-semibold text-sm mb-1"><?php echo htmlspecialchars($foto['titulo']); ?></h4>
                                        <?php if ($foto['descripcion']): ?>
                                            <p class="text-gray-200 text-xs mb-3"><?php echo htmlspecialchars($foto['descripcion']); ?></p>
                                        <?php endif; ?>

                                        <div class="flex gap-2">
                                            <button onclick="alert('Funcionalidad en desarrollo: Editar foto')" class="flex-1 px-3 py-1.5 bg-white/20 backdrop-blur-sm text-white text-xs font-medium rounded hover:bg-white/30 transition-colors">
                                                <i data-lucide="edit" class="w-3 h-3 inline mr-1"></i>
                                                Editar
                                            </button>
                                            <button onclick="alert('Funcionalidad en desarrollo: Eliminar foto')" class="flex-1 px-3 py-1.5 bg-red-500/80 backdrop-blur-sm text-white text-xs font-medium rounded hover:bg-red-600 transition-colors">
                                                <i data-lucide="trash-2" class="w-3 h-3 inline mr-1"></i>
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Badge si es destacada -->
                                    <?php if ($foto['destacada']): ?>
                                        <div class="absolute top-2 right-2">
                                            <span class="px-2 py-1 bg-yellow-500 text-white text-xs font-semibold rounded-full shadow-lg">
                                                <i data-lucide="star" class="w-3 h-3 inline"></i>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php } ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Nueva Galería -->
    <div id="modal-nueva-galeria" class="<?php echo $mostrar_modal ? '' : 'hidden'; ?> fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <form method="POST" action="">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-bold">Nueva Galería</h3>
                        <button type="button" onclick="cerrarModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/20 transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-6">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Nombre de la Galería *
                        </label>
                        <input type="text" name="nombre" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Ej: Bodas 2025">
                        <p class="text-xs text-gray-500 mt-1">El ID se generará automáticamente desde el nombre</p>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea name="descripcion" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Describe el contenido de esta galería..."></textarea>
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Color Identificador
                        </label>
                        <div class="grid grid-cols-8 gap-2">
                            <?php
                            $colores = [
                                '#667eea' => 'Púrpura',
                                '#f56565' => 'Rojo',
                                '#ed8936' => 'Naranja',
                                '#ecc94b' => 'Amarillo',
                                '#48bb78' => 'Verde',
                                '#38b2ac' => 'Turquesa',
                                '#4299e1' => 'Azul',
                                '#9f7aea' => 'Violeta'
                            ];
                            foreach ($colores as $hex => $nombre): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="<?php echo $hex; ?>" <?php echo $hex === '#667eea' ? 'checked' : ''; ?> class="hidden peer">
                                    <div class="w-full aspect-square rounded-lg border-2 border-transparent peer-checked:border-gray-900 peer-checked:scale-110 transition-all" style="background-color: <?php echo $hex; ?>;" title="<?php echo $nombre; ?>"></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Pública -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="publica" checked class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-2 focus:ring-purple-500">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Galería Pública</div>
                                <div class="text-xs text-gray-600">Visible en tu portfolio público</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 p-6 flex gap-3">
                    <button type="button" onclick="cerrarModal()" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" name="crear_galeria" class="flex-1 px-6 py-3 gradient-lumen text-white font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg">
                        <i data-lucide="folder-plus" class="w-5 h-5 inline mr-2"></i>
                        Crear Galería
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Subir Fotos -->
    <?php if ($galeria_actual && isset($galerias[$galeria_actual])): ?>
    <div id="modal-subir-fotos" class="<?php echo $mostrar_modal_subir ? '' : 'hidden'; ?> fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <form method="POST" enctype="multipart/form-data" action="">
                <input type="hidden" name="galeria_id" value="<?php echo $galeria_actual; ?>">

                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold">Subir Fotos</h3>
                            <p class="text-purple-100 text-sm mt-1">
                                Galería: <?php echo htmlspecialchars($galerias[$galeria_actual]['nombre']); ?>
                            </p>
                        </div>
                        <button type="button" onclick="cerrarModalSubir()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/20 transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <!-- Drop Zone -->
                    <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all">
                        <input type="file" id="file-input" name="fotos[]" multiple accept="image/jpeg,image/jpg,image/png,image/tiff,image/bmp,image/gif,image/webp" class="hidden">

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

                <!-- Footer -->
                <div class="border-t border-gray-200 p-6 flex gap-3">
                    <button type="button" onclick="cerrarModalSubir()" class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" name="subir_fotos" class="flex-1 px-6 py-3 gradient-lumen text-white font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg">
                        <i data-lucide="upload-cloud" class="w-5 h-5 inline mr-2"></i>
                        Subir Fotos
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        lucide.createIcons();

        function cerrarModal() {
            document.getElementById('modal-nueva-galeria').classList.add('hidden');
            // Limpiar parámetro de URL
            const url = new URL(window.location);
            url.searchParams.delete('accion');
            window.history.replaceState({}, '', url);
        }

        function cerrarModalSubir() {
            const modal = document.getElementById('modal-subir-fotos');
            if (modal) {
                modal.classList.add('hidden');
                // Limpiar parámetro de URL
                const url = new URL(window.location);
                url.searchParams.delete('accion');
                window.history.replaceState({}, '', url);
            }
        }

        // Abrir modal si hay parámetro accion=nueva
        <?php if ($mostrar_modal): ?>
        document.getElementById('modal-nueva-galeria').classList.remove('hidden');
        <?php endif; ?>

        // Abrir modal subir si hay parámetro accion=subir
        <?php if ($mostrar_modal_subir): ?>
        const modalSubir = document.getElementById('modal-subir-fotos');
        if (modalSubir) {
            modalSubir.classList.remove('hidden');
        }
        <?php endif; ?>

        // File input handling para modal de subida
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const previewArea = document.getElementById('preview-area');
        const previewGrid = document.getElementById('preview-grid');

        if (dropZone && fileInput) {
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
        }

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
