<?php
/**
 * Template Manager - Herramienta Interna
 * VERUMax - Gestión de Templates de Certificados
 *
 * USO INTERNO - No accesible para clientes
 * Permite: Listar, crear, editar JSON de templates
 *
 * @version 1.0
 */

// Cargar configuración
require_once __DIR__ . '/../../env_loader.php';

use VERUMax\Services\DatabaseService;
use VERUMax\Services\CertificateTemplateService;

// Conexiones
$pdo_certifi = DatabaseService::get('certificatum');
$pdo_general = DatabaseService::get('general');

// Obtener instituciones
$instituciones = $pdo_general->query("SELECT id_instancia, slug, nombre FROM instances ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Mensajes
$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'crear_template':
            try {
                $nombre = trim($_POST['nombre'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $institucion = trim($_POST['institucion'] ?? '');
                $orientacion = $_POST['orientacion'] ?? 'horizontal';
                $tipo_generador = $_POST['tipo_generador'] ?? 'tcpdf';

                if (empty($nombre) || empty($slug)) {
                    throw new Exception('Nombre y slug son requeridos');
                }

                // Verificar slug único
                $check = $pdo_certifi->prepare("SELECT id_template FROM certificatum_templates WHERE slug = ?");
                $check->execute([$slug]);
                if ($check->fetch()) {
                    throw new Exception('El slug ya existe');
                }

                $stmt = $pdo_certifi->prepare("
                    INSERT INTO certificatum_templates
                    (slug, nombre, descripcion, institucion, orientacion, tipo_generador, activo, orden)
                    VALUES (?, ?, ?, ?, ?, ?, 1, 99)
                ");
                $stmt->execute([$slug, $nombre, $descripcion, $institucion ?: null, $orientacion, $tipo_generador]);

                // Crear carpeta para assets del template
                $template_dir = __DIR__ . '/../../assets/templates/certificados/' . $slug;
                if (!is_dir($template_dir)) {
                    mkdir($template_dir, 0755, true);
                }

                $mensaje = "Template '$nombre' creado correctamente (carpeta creada)";
                $tipo_mensaje = 'success';
            } catch (Exception $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'guardar_json':
            try {
                $id_template = (int)($_POST['id_template'] ?? 0);
                $config_json = $_POST['config'] ?? '';

                if ($id_template <= 0) {
                    throw new Exception('ID de template inválido');
                }

                // Validar JSON si no está vacío
                if (!empty($config_json)) {
                    $decoded = json_decode($config_json, true);
                    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                        throw new Exception('JSON inválido: ' . json_last_error_msg());
                    }
                }

                $stmt = $pdo_certifi->prepare("UPDATE certificatum_templates SET config = ? WHERE id_template = ?");
                $stmt->execute([$config_json ?: null, $id_template]);

                $mensaje = "JSON guardado correctamente";
                $tipo_mensaje = 'success';
            } catch (Exception $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'subir_preview':
            try {
                $id_template = (int)($_POST['id_template'] ?? 0);

                if ($id_template <= 0) {
                    throw new Exception('ID de template inválido');
                }

                if (!isset($_FILES['preview']) || $_FILES['preview']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Error al subir la imagen');
                }

                $file = $_FILES['preview'];
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];

                if (!in_array($file['type'], $allowed)) {
                    throw new Exception('Formato no permitido. Use JPG, PNG o WebP');
                }

                // Obtener slug del template
                $stmt = $pdo_certifi->prepare("SELECT slug FROM certificatum_templates WHERE id_template = ?");
                $stmt->execute([$id_template]);
                $slug = $stmt->fetchColumn();

                if (!$slug) {
                    throw new Exception('Template no encontrado');
                }

                // Determinar extensión
                $ext = match($file['type']) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => 'jpg'
                };

                $filename = $slug . '_preview.' . $ext;
                $dest_dir = __DIR__ . '/../../assets/templates/previews/';
                $dest_path = $dest_dir . $filename;

                // Crear carpeta si no existe
                if (!is_dir($dest_dir)) {
                    if (!mkdir($dest_dir, 0755, true)) {
                        throw new Exception('No se pudo crear la carpeta de previews');
                    }
                }

                // Verificar permisos de escritura
                if (!is_writable($dest_dir)) {
                    throw new Exception('La carpeta de previews no tiene permisos de escritura');
                }

                // Mover archivo
                if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
                    throw new Exception('Error al mover el archivo. Verifique permisos del servidor.');
                }

                // Actualizar BD con URL absoluta (para multi-tenant)
                // Usar dominio principal para que funcione desde cualquier subdominio
                $base_url = 'https://verumax.com';
                $preview_url = $base_url . '/assets/templates/previews/' . $filename;
                $stmt = $pdo_certifi->prepare("UPDATE certificatum_templates SET preview_url = ? WHERE id_template = ?");
                $stmt->execute([$preview_url, $id_template]);

                $mensaje = "Preview subido correctamente";
                $tipo_mensaje = 'success';
            } catch (Exception $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'eliminar_preview':
            try {
                $id_template = (int)($_POST['id_template'] ?? 0);

                if ($id_template <= 0) {
                    throw new Exception('ID de template inválido');
                }

                // Obtener preview actual
                $stmt = $pdo_certifi->prepare("SELECT preview_url FROM certificatum_templates WHERE id_template = ?");
                $stmt->execute([$id_template]);
                $preview_url = $stmt->fetchColumn();

                if ($preview_url) {
                    // Eliminar archivo físico
                    $file_path = __DIR__ . '/../../' . ltrim($preview_url, '/');
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }

                    // Limpiar BD
                    $stmt = $pdo_certifi->prepare("UPDATE certificatum_templates SET preview_url = NULL WHERE id_template = ?");
                    $stmt->execute([$id_template]);
                }

                $mensaje = "Preview eliminado";
                $tipo_mensaje = 'success';
            } catch (Exception $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'eliminar_template':
            try {
                $id_template = (int)($_POST['id_template'] ?? 0);

                if ($id_template <= 0) {
                    throw new Exception('ID de template inválido');
                }

                // Verificar que no esté en uso (cursos está en verumax_academi)
                $check = $pdo_certifi->prepare("SELECT COUNT(*) FROM verumax_academi.cursos WHERE id_template = ?");
                $check->execute([$id_template]);
                if ($check->fetchColumn() > 0) {
                    throw new Exception('El template está en uso por uno o más cursos');
                }

                $stmt = $pdo_certifi->prepare("DELETE FROM certificatum_templates WHERE id_template = ?");
                $stmt->execute([$id_template]);

                $mensaje = "Template eliminado";
                $tipo_mensaje = 'success';
            } catch (Exception $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;
    }
}

// Obtener templates
$templates = $pdo_certifi->query("
    SELECT
        t.*,
        (SELECT COUNT(*) FROM verumax_academi.cursos c WHERE c.id_template = t.id_template) as cursos_usando
    FROM certificatum_templates t
    ORDER BY t.orden, t.nombre
")->fetchAll(PDO::FETCH_ASSOC);

// Template seleccionado para editar
$template_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = (int)$_GET['editar'];
    foreach ($templates as $t) {
        if ($t['id_template'] == $id_editar) {
            $template_editar = $t;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template Manager - VERUMax Internal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .json-editor {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 12px;
            line-height: 1.5;
            tab-size: 2;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-gradient-to-r from-purple-700 to-indigo-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i data-lucide="layout-template" class="w-8 h-8"></i>
                    <div>
                        <h1 class="text-xl font-bold">Template Manager</h1>
                        <p class="text-purple-200 text-sm">Herramienta Interna - VERUMax</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="/tools/template-editor/index.html" target="_blank"
                       class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium flex items-center gap-2">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                        Editor Visual
                    </a>
                    <a href="/admin/" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium flex items-center gap-2">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Volver al Admin
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <?php if ($mensaje): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
            <div class="flex items-center gap-2">
                <i data-lucide="<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5"></i>
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Lista de Templates -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-4 border-b flex items-center justify-between">
                        <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i data-lucide="layers" class="w-5 h-5 text-purple-600"></i>
                            Templates (<?php echo count($templates); ?>)
                        </h2>
                        <button onclick="document.getElementById('modal-crear').classList.remove('hidden')"
                                class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 flex items-center gap-1">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Nuevo
                        </button>
                    </div>
                    <div class="divide-y max-h-[600px] overflow-y-auto">
                        <?php foreach ($templates as $t): ?>
                        <?php
                            $hasConfig = !empty($t['config']);
                            $isSelected = $template_editar && $template_editar['id_template'] == $t['id_template'];
                        ?>
                        <div class="flex items-center p-4 hover:bg-gray-50 <?php echo $isSelected ? 'bg-purple-50 border-l-4 border-purple-600' : ''; ?>">
                            <a href="?editar=<?php echo $t['id_template']; ?>" class="flex-1 min-w-0">
                                <h3 class="font-medium text-gray-900 truncate"><?php echo htmlspecialchars($t['nombre']); ?></h3>
                                <p class="text-xs text-gray-500 mt-0.5"><?php echo htmlspecialchars($t['slug']); ?></p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="px-2 py-0.5 rounded text-xs <?php echo $hasConfig ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                        <?php echo $hasConfig ? 'Con JSON' : 'Sin JSON'; ?>
                                    </span>
                                    <?php if ($t['cursos_usando'] > 0): ?>
                                    <span class="px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-700">
                                        <?php echo $t['cursos_usando']; ?> curso(s)
                                    </span>
                                    <?php endif; ?>
                                    <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                                        <?php echo $t['orientacion']; ?>
                                    </span>
                                </div>
                            </a>
                            <div class="flex items-center gap-2 ml-2">
                                <a href="editor.php?id=<?php echo $t['id_template']; ?>"
                                   class="p-2 text-purple-600 hover:bg-purple-50 rounded" title="Editor Visual">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                                <?php if ($t['cursos_usando'] == 0): ?>
                                <form method="POST" onsubmit="return confirm('¿Eliminar template <?php echo htmlspecialchars($t['nombre']); ?>?');" class="inline">
                                    <input type="hidden" name="accion" value="eliminar_template">
                                    <input type="hidden" name="id_template" value="<?php echo $t['id_template']; ?>">
                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded" title="Eliminar">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="p-2 text-gray-300 cursor-not-allowed" title="En uso por <?php echo $t['cursos_usando']; ?> curso(s)">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (empty($templates)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                            <p>No hay templates</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Editor de Template -->
            <div class="lg:col-span-2">
                <?php if ($template_editar): ?>
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-4 border-b">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="font-semibold text-gray-800"><?php echo htmlspecialchars($template_editar['nombre']); ?></h2>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($template_editar['slug']); ?></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="editor.php?id=<?php echo $template_editar['id_template']; ?>"
                                   class="px-4 py-1.5 bg-purple-600 text-white hover:bg-purple-700 rounded-lg text-sm flex items-center gap-1">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    Editor Visual
                                </a>
                                <?php if ($template_editar['cursos_usando'] == 0): ?>
                                <form method="POST" onsubmit="return confirm('¿Eliminar este template?');" class="inline">
                                    <input type="hidden" name="accion" value="eliminar_template">
                                    <input type="hidden" name="id_template" value="<?php echo $template_editar['id_template']; ?>">
                                    <button type="submit" class="px-3 py-1.5 text-red-600 hover:bg-red-50 rounded-lg text-sm flex items-center gap-1">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        Eliminar
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Preview del Template -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-start gap-4">
                                <div class="w-32 h-24 bg-white rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden">
                                    <?php if ($template_editar['preview_url']): ?>
                                        <img src="<?php echo htmlspecialchars($template_editar['preview_url']); ?>"
                                             alt="Preview"
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i data-lucide="image" class="w-8 h-8 text-gray-400"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Imagen de Preview</p>
                                    <p class="text-xs text-gray-500 mb-3">Se muestra al cliente cuando elige el template. Recomendado: 320x200px</p>
                                    <div class="flex items-center gap-2">
                                        <form method="POST" enctype="multipart/form-data" class="inline-flex items-center gap-2">
                                            <input type="hidden" name="accion" value="subir_preview">
                                            <input type="hidden" name="id_template" value="<?php echo $template_editar['id_template']; ?>">
                                            <input type="file" name="preview" accept="image/jpeg,image/png,image/webp"
                                                   class="text-xs file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-purple-600 file:text-white file:cursor-pointer">
                                            <button type="submit" class="px-3 py-1 bg-purple-600 text-white rounded text-xs hover:bg-purple-700">
                                                Subir
                                            </button>
                                        </form>
                                        <?php if ($template_editar['preview_url']): ?>
                                        <form method="POST" class="inline" onsubmit="return confirm('¿Eliminar el preview?');">
                                            <input type="hidden" name="accion" value="eliminar_preview">
                                            <input type="hidden" name="id_template" value="<?php echo $template_editar['id_template']; ?>">
                                            <button type="submit" class="px-3 py-1 text-red-600 hover:bg-red-50 rounded text-xs">
                                                Eliminar
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info del template -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Orientación</p>
                                <p class="font-medium"><?php echo ucfirst($template_editar['orientacion']); ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Generador</p>
                                <p class="font-medium"><?php echo strtoupper($template_editar['tipo_generador']); ?></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Institución</p>
                                <p class="font-medium <?php echo $template_editar['institucion'] ? 'text-purple-600' : 'text-gray-600'; ?>">
                                    <?php echo $template_editar['institucion'] ? strtoupper($template_editar['institucion']) . ' (exclusivo)' : 'Global'; ?>
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Cursos usando</p>
                                <p class="font-medium"><?php echo $template_editar['cursos_usando']; ?></p>
                            </div>
                        </div>

                        <!-- Editor JSON -->
                        <form method="POST">
                            <input type="hidden" name="accion" value="guardar_json">
                            <input type="hidden" name="id_template" value="<?php echo $template_editar['id_template']; ?>">

                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        Configuración JSON
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="formatearJson()" class="text-sm text-purple-600 hover:text-purple-800">
                                            Formatear
                                        </button>
                                        <button type="button" onclick="limpiarJson()" class="text-sm text-red-600 hover:text-red-800">
                                            Limpiar
                                        </button>
                                    </div>
                                </div>
                                <textarea name="config" id="json-editor" rows="20"
                                    class="json-editor w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-gray-900 text-green-400"
                                    placeholder='{"name":"Template","canvas":{"width":1122,"height":793},"elements":[...]}'
                                ><?php echo htmlspecialchars($template_editar['config'] ?? ''); ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    Pegá el JSON exportado desde el <a href="/tools/template-editor/index.html" target="_blank" class="text-purple-600 hover:underline">Editor Visual</a>
                                </p>
                            </div>

                            <div class="flex justify-end gap-3">
                                <a href="?" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                    Cancelar
                                </a>
                                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center gap-2">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                    Guardar JSON
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <i data-lucide="mouse-pointer-click" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Seleccioná un template</h3>
                    <p class="text-gray-500">Hacé clic en un template de la lista para editar su configuración JSON</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal: Crear Template -->
    <div id="modal-crear" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-lg">Crear Nuevo Template</h3>
                <button onclick="document.getElementById('modal-crear').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" class="p-4 space-y-4">
                <input type="hidden" name="accion" value="crear_template">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="nombre" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="Ej: Certificado Elegante">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                    <input type="text" name="slug" required pattern="[a-z0-9_-]+"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="Ej: certificado_elegante">
                    <p class="text-xs text-gray-500 mt-1">Solo minúsculas, números, guiones y guiones bajos</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <input type="text" name="descripcion"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="Descripción breve del template">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Orientación</label>
                        <select name="orientacion" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="horizontal">Horizontal (A4 apaisado)</option>
                            <option value="vertical">Vertical (A4 normal)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Generador</label>
                        <select name="tipo_generador" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="tcpdf">TCPDF (con imagen fondo)</option>
                            <option value="mpdf">mPDF (HTML/CSS)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Institución (opcional)</label>
                    <select name="institucion" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Global (todas las instituciones)</option>
                        <?php foreach ($instituciones as $inst): ?>
                        <option value="<?php echo htmlspecialchars($inst['slug']); ?>">
                            <?php echo htmlspecialchars($inst['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="document.getElementById('modal-crear').classList.add('hidden')"
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Crear Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function formatearJson() {
            const textarea = document.getElementById('json-editor');
            try {
                const parsed = JSON.parse(textarea.value);
                textarea.value = JSON.stringify(parsed, null, 2);
            } catch (e) {
                alert('JSON inválido: ' + e.message);
            }
        }

        function limpiarJson() {
            if (confirm('¿Limpiar el JSON? Esta acción no se puede deshacer.')) {
                document.getElementById('json-editor').value = '';
            }
        }

        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('modal-crear').classList.add('hidden');
            }
        });

        // Cerrar modal haciendo clic afuera
        document.getElementById('modal-crear').addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    </script>
</body>
</html>
