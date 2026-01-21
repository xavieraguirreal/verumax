<?php
/**
 * IDENTITAS - Panel de Administración
 * Permite al cliente gestionar su instancia sin tocar código
 */

session_start();

require_once __DIR__ . '/config.php';

// Verificar autenticación
if (!isset($_SESSION['admin_identitas'])) {
    header('Location: login.php');
    exit;
}

$slug = $_SESSION['admin_identitas']['slug'];
$pdo = getDBConnection();

// Obtener configuración actual
$instance = getInstanceConfig($slug);
if (!$instance) {
    die('Instancia no encontrada');
}

// Obtener páginas
$stmt = $pdo->prepare("
    SELECT * FROM identitas_paginas
    WHERE id_instancia = :id_instancia
    ORDER BY orden
");
$stmt->execute(['id_instancia' => $instance['id_instancia']]);
$paginas = $stmt->fetchAll();

// Obtener contactos recientes
$stmt = $pdo->prepare("
    SELECT * FROM identitas_contactos
    WHERE id_instancia = :id_instancia
    ORDER BY fecha_contacto DESC
    LIMIT 10
");
$stmt->execute(['id_instancia' => $instance['id_instancia']]);
$contactos = $stmt->fetchAll();

// Manejar acciones
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'actualizar_instancia':
            try {
                $stmt = $pdo->prepare("
                    UPDATE identitas_instances
                    SET nombre = :nombre,
                        nombre_completo = :nombre_completo,
                        color_primario = :color_primario,
                        logo_url = :logo_url,
                        configuracion = :configuracion
                    WHERE id_instancia = :id_instancia
                ");

                $config = json_encode([
                    'sitio_web_oficial' => $_POST['sitio_web_oficial'] ?? '',
                    'email_contacto' => $_POST['email_contacto'] ?? '',
                    'mision' => $_POST['mision'] ?? ''
                ]);

                $stmt->execute([
                    'nombre' => $_POST['nombre'],
                    'nombre_completo' => $_POST['nombre_completo'],
                    'color_primario' => $_POST['color_primario'],
                    'logo_url' => $_POST['logo_url'] ?? '',
                    'configuracion' => $config,
                    'id_instancia' => $instance['id_instancia']
                ]);

                $mensaje = 'Configuración actualizada correctamente';
                $tipo_mensaje = 'success';

                // Recargar instancia
                $instance = getInstanceConfig($slug);

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'actualizar_pagina':
            try {
                $stmt = $pdo->prepare("
                    UPDATE identitas_paginas
                    SET titulo = :titulo,
                        contenido = :contenido,
                        visible_menu = :visible_menu
                    WHERE id_pagina = :id_pagina
                    AND id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'titulo' => $_POST['titulo'],
                    'contenido' => $_POST['contenido'],
                    'visible_menu' => isset($_POST['visible_menu']) ? 1 : 0,
                    'id_pagina' => $_POST['id_pagina'],
                    'id_instancia' => $instance['id_instancia']
                ]);

                $mensaje = 'Página actualizada correctamente';
                $tipo_mensaje = 'success';

                // Recargar páginas
                $stmt = $pdo->prepare("
                    SELECT * FROM identitas_paginas
                    WHERE id_instancia = :id_instancia
                    ORDER BY orden
                ");
                $stmt->execute(['id_instancia' => $instance['id_instancia']]);
                $paginas = $stmt->fetchAll();

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar página: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'marcar_leido':
            try {
                $stmt = $pdo->prepare("
                    UPDATE identitas_contactos
                    SET leido = 1
                    WHERE id_contacto = :id_contacto
                    AND id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'id_contacto' => $_POST['id_contacto'],
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Recargar contactos
                $stmt = $pdo->prepare("
                    SELECT * FROM identitas_contactos
                    WHERE id_instancia = :id_instancia
                    ORDER BY fecha_contacto DESC
                    LIMIT 10
                ");
                $stmt->execute(['id_instancia' => $instance['id_instancia']]);
                $contactos = $stmt->fetchAll();

            } catch (PDOException $e) {
                $mensaje = 'Error al marcar como leído: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - <?php echo htmlspecialchars($instance['nombre']); ?> Identitas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- CKEditor 5 Classic -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        /* Estilos para CKEditor */
        .ck-editor__editable {
            min-height: 300px;
            max-height: 500px;
        }
        .ck-editor__editable:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3) !important;
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Panel de Administración
                    </h1>
                    <span class="text-gray-500">|</span>
                    <span class="text-lg text-gray-600"><?php echo htmlspecialchars($instance['nombre']); ?></span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="https://<?php echo htmlspecialchars($slug); ?>.verumax.com/" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 inline-flex items-center gap-1">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        Ver sitio
                    </a>
                    <a href="logout.php" class="text-sm text-red-600 hover:text-red-800 inline-flex items-center gap-1">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Salir
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'; ?>">
                <div class="flex items-center gap-2">
                    <i data-lucide="<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5"></i>
                    <span><?php echo htmlspecialchars($mensaje); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="switchTab('configuracion')" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                        <i data-lucide="settings" class="w-4 h-4 inline mr-2"></i>
                        Configuración
                    </button>
                    <button onclick="switchTab('paginas')" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        <i data-lucide="file-text" class="w-4 h-4 inline mr-2"></i>
                        Páginas
                    </button>
                    <button onclick="switchTab('contactos')" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        <i data-lucide="mail" class="w-4 h-4 inline mr-2"></i>
                        Contactos
                        <?php
                        $no_leidos = array_filter($contactos, fn($c) => !$c['leido']);
                        if (count($no_leidos) > 0):
                        ?>
                            <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo count($no_leidos); ?></span>
                        <?php endif; ?>
                    </button>
                </nav>
            </div>

            <!-- Tab: Configuración -->
            <div id="tab-configuracion" class="tab-content active p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Configuración General</h2>

                <form method="POST" class="space-y-6">
                    <input type="hidden" name="accion" value="actualizar_instancia">

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre corto</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($instance['nombre']); ?>" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-sm text-gray-500">Ej: SAJuR, Liberté</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre completo</label>
                            <input type="text" name="nombre_completo" value="<?php echo htmlspecialchars($instance['nombre_completo']); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-sm text-gray-500">Nombre completo de la institución</p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color primario (Hex)</label>
                            <div class="flex gap-2">
                                <input type="color" name="color_primario" value="<?php echo htmlspecialchars($instance['color_primario']); ?>"
                                       class="h-10 w-20 border border-gray-300 rounded-lg">
                                <input type="text" value="<?php echo htmlspecialchars($instance['color_primario']); ?>" readonly
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Color principal del sitio</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">URL del logo (opcional)</label>
                            <input type="url" name="logo_url" value="<?php echo htmlspecialchars($instance['logo_url'] ?? ''); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-sm text-gray-500">Ej: https://ejemplo.com/logo.png</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sitio web oficial</label>
                        <input type="url" name="sitio_web_oficial" value="<?php echo htmlspecialchars($instance['config']['sitio_web_oficial'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email de contacto</label>
                        <input type="email" name="email_contacto" value="<?php echo htmlspecialchars($instance['config']['email_contacto'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Misión / Descripción</label>
                        <textarea name="mision" rows="4"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($instance['config']['mision'] ?? ''); ?></textarea>
                        <p class="mt-1 text-sm text-gray-500">Se muestra en el hero de la página principal</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition inline-flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab: Páginas -->
            <div id="tab-paginas" class="tab-content p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Editar Páginas</h2>

                <div class="space-y-6">
                    <?php foreach ($paginas as $pagina): ?>
                        <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                            <form method="POST">
                                <input type="hidden" name="accion" value="actualizar_pagina">
                                <input type="hidden" name="id_pagina" value="<?php echo $pagina['id_pagina']; ?>">

                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        Página: <?php echo htmlspecialchars($pagina['slug']); ?>
                                    </h3>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="visible_menu" <?php echo $pagina['visible_menu'] ? 'checked' : ''; ?>
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-600">Visible en menú</span>
                                    </label>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
                                        <input type="text" name="titulo" value="<?php echo htmlspecialchars($pagina['titulo']); ?>" required
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Contenido</label>
                                        <textarea name="contenido" id="editor-<?php echo htmlspecialchars($pagina['slug']); ?>"
                                                  class="ckeditor-contenido w-full"><?php echo htmlspecialchars($pagina['contenido']); ?></textarea>
                                        <p class="mt-2 text-sm text-gray-500">
                                            <i data-lucide="info" class="w-4 h-4 inline"></i>
                                            Use el editor para dar formato. Las clases de Tailwind CSS están disponibles en el HTML generado.
                                        </p>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition inline-flex items-center gap-2">
                                            <i data-lucide="save" class="w-4 h-4"></i>
                                            Guardar página
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tab: Contactos -->
            <div id="tab-contactos" class="tab-content p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Mensajes de Contacto</h2>

                <?php if (empty($contactos)): ?>
                    <p class="text-gray-500 text-center py-8">No hay mensajes de contacto</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($contactos as $contacto): ?>
                            <div class="border border-gray-200 rounded-lg p-4 <?php echo $contacto['leido'] ? 'bg-white' : 'bg-blue-50 border-blue-300'; ?>">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($contacto['nombre']); ?></h3>
                                        <p class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($contacto['email']); ?>
                                            <?php if ($contacto['telefono']): ?>
                                                | <?php echo htmlspecialchars($contacto['telefono']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($contacto['fecha_contacto'])); ?></p>
                                        <?php if (!$contacto['leido']): ?>
                                            <form method="POST" class="mt-2">
                                                <input type="hidden" name="accion" value="marcar_leido">
                                                <input type="hidden" name="id_contacto" value="<?php echo $contacto['id_contacto']; ?>">
                                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">Marcar como leído</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($contacto['asunto']): ?>
                                    <p class="text-sm font-medium text-gray-700 mb-2">
                                        Asunto: <?php echo htmlspecialchars($contacto['asunto']); ?>
                                    </p>
                                <?php endif; ?>

                                <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($contacto['mensaje']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
        lucide.createIcons();

        function switchTab(tabName) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Remover estilo activo de todos los botones
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            // Mostrar tab seleccionado
            document.getElementById('tab-' + tabName).classList.add('active');

            // Activar botón seleccionado
            event.target.closest('.tab-button').classList.remove('border-transparent', 'text-gray-500');
            event.target.closest('.tab-button').classList.add('border-blue-500', 'text-blue-600');
        }

        // Sincronizar color picker con input de texto
        document.querySelector('input[type="color"]').addEventListener('input', function(e) {
            this.nextElementSibling.value = e.target.value;
        });

        // ============================================
        // CKEditor 5 - Inicialización para editores de contenido
        // ============================================
        const editorInstances = {};

        document.querySelectorAll('.ckeditor-contenido').forEach(textarea => {
            ClassicEditor
                .create(textarea, {
                    toolbar: {
                        items: [
                            'heading', '|',
                            'bold', 'italic', 'underline', '|',
                            'link', '|',
                            'bulletedList', 'numberedList', '|',
                            'indent', 'outdent', '|',
                            'blockQuote', '|',
                            'undo', 'redo'
                        ]
                    },
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Párrafo', class: 'ck-heading_paragraph' },
                            { model: 'heading2', view: 'h2', title: 'Encabezado 2', class: 'ck-heading_heading2' },
                            { model: 'heading3', view: 'h3', title: 'Encabezado 3', class: 'ck-heading_heading3' },
                            { model: 'heading4', view: 'h4', title: 'Encabezado 4', class: 'ck-heading_heading4' }
                        ]
                    },
                    language: 'es',
                    placeholder: 'Escriba aquí el contenido de la página...'
                })
                .then(editor => {
                    editorInstances[textarea.id] = editor;
                    console.log('CKEditor inicializado para:', textarea.id);
                })
                .catch(error => {
                    console.error('Error al inicializar CKEditor para ' + textarea.id + ':', error);
                });
        });
    </script>
</body>
</html>
