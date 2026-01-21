<?php
/**
 * Template Editor Integrado - VERUMax
 *
 * Wrapper que carga el editor completo y lo conecta con la BD
 *
 * @version 2.0
 */

require_once __DIR__ . '/../../env_loader.php';

use VERUMax\Services\DatabaseService;

$pdo_certifi = DatabaseService::get('certificatum');

// =============================================
// API ENDPOINTS
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // Guardar JSON del template
    if ($action === 'save_template') {
        try {
            $id = (int)($_POST['id_template'] ?? 0);
            $json_config = $_POST['config'] ?? '';

            if ($id <= 0) {
                throw new Exception('ID de template inválido');
            }

            // Validar JSON
            if (!empty($json_config)) {
                $decoded = json_decode($json_config, true);
                if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('JSON inválido: ' . json_last_error_msg());
                }
            }

            $stmt = $pdo_certifi->prepare("UPDATE certificatum_templates SET config = ? WHERE id_template = ?");
            $stmt->execute([$json_config ?: null, $id]);

            echo json_encode(['success' => true, 'message' => 'Template guardado correctamente']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // Subir imagen de fondo
    if ($action === 'upload_background') {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                throw new Exception('No se recibieron datos');
            }

            $slug = $input['slug'] ?? '';
            $image_data = $input['image'] ?? '';
            $filename = $input['filename'] ?? 'fondo.jpg';

            if (empty($slug)) {
                throw new Exception('Slug del template requerido');
            }

            if (!preg_match('/^[a-z0-9_-]+$/i', $slug)) {
                throw new Exception('Slug inválido');
            }

            if (empty($image_data)) {
                throw new Exception('Imagen requerida');
            }

            if (!preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $image_data)) {
                throw new Exception('Formato de imagen inválido');
            }

            // Extraer datos
            $image_parts = explode(',', $image_data);
            $image_base64 = $image_parts[1] ?? '';
            $image_binary = base64_decode($image_base64);

            if ($image_binary === false) {
                throw new Exception('Error al decodificar la imagen');
            }

            // Sanitizar filename
            $filename = preg_replace('/[^a-z0-9_.-]/i', '_', $filename);

            // Directorio destino
            $base_dir = __DIR__ . '/../../assets/templates/certificados/';
            $dest_dir = $base_dir . $slug . '/';
            $dest_path = $dest_dir . $filename;

            if (!is_dir($dest_dir)) {
                if (!mkdir($dest_dir, 0755, true)) {
                    throw new Exception('No se pudo crear el directorio');
                }
            }

            $bytes = file_put_contents($dest_path, $image_binary);

            if ($bytes === false) {
                throw new Exception('Error al guardar la imagen');
            }

            echo json_encode([
                'success' => true,
                'path' => '/assets/templates/certificados/' . $slug . '/' . $filename,
                'filename' => $filename
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}

// =============================================
// CARGAR TEMPLATE
// =============================================
$id_template = (int)($_GET['id'] ?? 0);
$template = null;
$config = null;

if ($id_template > 0) {
    $stmt = $pdo_certifi->prepare("SELECT * FROM certificatum_templates WHERE id_template = ?");
    $stmt->execute([$id_template]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($template && !empty($template['config'])) {
        $config = json_decode($template['config'], true);
    }
}

if (!$template) {
    header('Location: index.php');
    exit;
}

// Preparar estado inicial para el editor
// Detectar orientación (puede ser 'horizontal', 'landscape', 'vertical', 'portrait')
$orientacion_db = strtolower($template['orientacion'] ?? 'landscape');
$orientation = in_array($orientacion_db, ['horizontal', 'landscape']) ? 'landscape' : 'portrait';

$initial_state = [
    'templateName' => $template['nombre'],
    'templateSlug' => $template['slug'],
    'orientation' => $orientation,
    'background' => null,
    'backgroundFilename' => null,
    'elements' => [],
    'guides' => [],
    'nextId' => 1,
    'isNewFormat' => false,
    'isProjectFormat' => false,  // _format === "project" (valores en px)
    'isExportFormat' => false    // formato export (valores en mm/pt)
];

// Detectar formato del config guardado
$is_project_format = $config && isset($config['_format']) && $config['_format'] === 'project';
$is_export_format = $config && isset($config['canvas']) && isset($config['elements']);
$is_new_format = $is_project_format || $is_export_format;

if ($is_project_format) {
    // FORMATO PROJECT: Guardado con valores en px (el que usamos ahora)
    $initial_state['isNewFormat'] = true;
    $initial_state['isProjectFormat'] = true;
    $initial_state['elements'] = $config['elements'] ?? [];
    $initial_state['guides'] = $config['guides'] ?? [];
    $initial_state['nextId'] = $config['nextId'] ?? (count($config['elements'] ?? []) + 1);

    // Orientación directa
    if (!empty($config['orientation'])) {
        $initial_state['orientation'] = $config['orientation'];
    }

    // Background filename
    if (!empty($config['backgroundFilename'])) {
        $initial_state['backgroundFilename'] = $config['backgroundFilename'];

        // Cargar imagen de fondo como base64
        $bg_path = __DIR__ . '/../../assets/templates/certificados/' . $template['slug'] . '/' . $config['backgroundFilename'];
        if (file_exists($bg_path)) {
            $ext = strtolower(pathinfo($bg_path, PATHINFO_EXTENSION));
            $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
            $initial_state['background'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($bg_path));
        }
    }
} elseif ($is_export_format) {
    // FORMATO EXPORT: Guardado con valores en mm/pt (formato viejo del manager)
    $initial_state['isNewFormat'] = true;
    $initial_state['isExportFormat'] = true;
    $initial_state['elements'] = $config['elements'] ?? [];
    $initial_state['guides'] = $config['guides'] ?? [];

    // Orientación del canvas
    if (!empty($config['canvas']['orientation'])) {
        $initial_state['orientation'] = $config['canvas']['orientation'];
    }

    if (!empty($config['canvas']['background'])) {
        $initial_state['backgroundFilename'] = $config['canvas']['background'];

        // Cargar imagen de fondo como base64
        $bg_path = __DIR__ . '/../../assets/templates/certificados/' . $template['slug'] . '/' . $config['canvas']['background'];
        if (file_exists($bg_path)) {
            $ext = strtolower(pathinfo($bg_path, PATHINFO_EXTENSION));
            $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
            $initial_state['background'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($bg_path));
        }
    }
} else {
    // Template con formato viejo (sin elements) - intentar cargar fondo si existe
    $posibles_fondos = ['fondo.jpg', 'fondo.png', 'background.jpg', 'template.jpg'];
    $bg_dir = __DIR__ . '/../../assets/templates/certificados/' . $template['slug'] . '/';

    foreach ($posibles_fondos as $fondo) {
        if (file_exists($bg_dir . $fondo)) {
            $ext = strtolower(pathinfo($fondo, PATHINFO_EXTENSION));
            $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
            $initial_state['background'] = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($bg_dir . $fondo));
            $initial_state['backgroundFilename'] = $fondo;
            break;
        }
    }
}

// =============================================
// LEER EL EDITOR ORIGINAL Y MODIFICARLO
// =============================================
$editor_html = file_get_contents(__DIR__ . '/../template-editor/index.html');

// =============================================
// PREPARAR ESTADO INICIAL PARA REEMPLAZAR EL DEL EDITOR
// =============================================
// Convertir elementos de mm/pt a px si es formato EXPORT
$is_project_format = $initial_state['isProjectFormat'];
$elements_for_editor = [];

// Funciones de conversión
$mmToPx = function($mm) { return round($mm / 0.264583); };
$ptToPx = function($pt) { return round($pt / 0.75); };

if (!empty($initial_state['elements'])) {
    foreach ($initial_state['elements'] as $idx => $el) {
        if ($is_project_format) {
            // Ya está en px, solo asegurar que tiene id
            $el['id'] = $el['id'] ?? ($idx + 1);
        } else {
            // Convertir de mm a px
            $el['id'] = $idx + 1;
            $el['x'] = $mmToPx($el['x']);
            $el['y'] = $mmToPx($el['y']);
            $el['width'] = $mmToPx($el['width']);
            $el['height'] = $mmToPx($el['height']);
            if (isset($el['size'])) {
                $el['size'] = $ptToPx($el['size']);
            }
        }

        // Manejar text: null - usar el label o variable como texto de muestra
        if (array_key_exists('text', $el) && $el['text'] === null) {
            if (!empty($el['variable'])) {
                $el['text'] = $el['variable'];  // Mostrar la variable como placeholder
            } elseif (!empty($el['label'])) {
                $el['text'] = $el['label'];
            }
        }

        $elements_for_editor[] = $el;
    }
}

// Convertir guías de mm a px si es formato EXPORT
$guides_for_editor = [];
if (!empty($initial_state['guides'])) {
    foreach ($initial_state['guides'] as $guide) {
        if (!$is_project_format && isset($guide['position_mm'])) {
            // Convertir de mm a px
            $guide['position'] = $mmToPx($guide['position_mm']);
            unset($guide['position_mm']);  // Eliminar el campo en mm
        } elseif (!isset($guide['position']) && isset($guide['position_mm'])) {
            // Caso por si acaso: tiene position_mm pero no position
            $guide['position'] = $mmToPx($guide['position_mm']);
            unset($guide['position_mm']);
        }
        $guides_for_editor[] = $guide;
    }
}

// Estado preparado para el editor (en px)
$editor_state = [
    'templateName' => $initial_state['templateName'],
    'templateSlug' => $initial_state['templateSlug'],
    'orientation' => $initial_state['orientation'],
    'background' => $initial_state['background'],
    'backgroundFilename' => $initial_state['backgroundFilename'],
    'elements' => $elements_for_editor,
    'selectedId' => null,
    'selectedIds' => [],
    'history' => [],
    'historyIndex' => -1,
    'gridVisible' => false,
    'snapEnabled' => false,
    'snapSize' => 20,
    'nextId' => count($elements_for_editor) + 1,
    'groups' => (object)[],
    'nextGroupId' => 1,
    'rulersVisible' => false,
    'guides' => $guides_for_editor,
    'snapToGuides' => false,
    'guideSnapDistance' => 8
];

// Inyectar código para sobrescribir state justo después de su definición
// Usamos regex para ser más flexibles con la indentación
$state_override_code = '
        // =============================================
        // ESTADO PRECARGADO DESDE MANAGER
        // =============================================
        state.templateName = ' . json_encode($editor_state['templateName']) . ';
        state.templateSlug = ' . json_encode($editor_state['templateSlug']) . ';
        state.orientation = ' . json_encode($editor_state['orientation']) . ';
        state.background = ' . json_encode($editor_state['background']) . ';
        state.backgroundFilename = ' . json_encode($editor_state['backgroundFilename']) . ';
        state.elements = ' . json_encode($editor_state['elements']) . ';
        state.nextId = ' . json_encode($editor_state['nextId']) . ';
        state.guides = ' . json_encode($editor_state['guides']) . ';
        console.log("State precargado desde Manager:", state.templateName, state.orientation, state.elements.length + " elementos");
        // FIN ESTADO PRECARGADO

';

// Buscar el patrón con regex flexible para espacios
$editor_html = preg_replace(
    '/(\s*\/\/\s*=+\s*\n\s*\/\/\s*DOM ELEMENTS)/i',
    $state_override_code . '$1',
    $editor_html,
    1
);

// Inyectar constantes y funciones del manager
$inject_script = '
<script>
// =============================================
// MODO INTEGRADO CON MANAGER
// =============================================
const MANAGER_MODE = true;
const TEMPLATE_ID = ' . $id_template . ';
const TEMPLATE_SLUG = "' . addslashes($template['slug']) . '";

// Al cargar, renderizar el estado precargado
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        // Aplicar fondo si existe
        if (state.background) {
            const bgUrl = "url(" + state.background + ")";
            document.getElementById("canvas").style.backgroundImage = bgUrl;
            if (document.getElementById("canvas-no-rulers")) {
                document.getElementById("canvas-no-rulers").style.backgroundImage = bgUrl;
            }
        }

        // Actualizar canvas y elementos
        if (typeof updateCanvasOrientation === "function") updateCanvasOrientation();
        if (typeof renderElements === "function") renderElements();
        if (typeof renderGuides === "function") renderGuides();

        console.log("Editor cargado en modo Manager con template:", TEMPLATE_SLUG);
        console.log("Orientación:", state.orientation);
        console.log("Elementos:", state.elements.length);
        console.log("Fondo:", state.backgroundFilename);
    }, 100);
});

// Sobrescribir función de upload de fondo para usar endpoint del manager
const originalUploadBackground = typeof uploadBackgroundToServer === "function" ? uploadBackgroundToServer : null;
window.uploadBackgroundToServer = async function(imageData, slug, filename) {
    try {
        showToast("Subiendo fondo al servidor...");

        const response = await fetch("editor.php?action=upload_background", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                slug: slug || TEMPLATE_SLUG,
                image: imageData,
                filename: filename
            })
        });

        const result = await response.json();

        if (result.success) {
            state.backgroundFilename = result.filename;
            showToast("Fondo guardado: " + result.path);
        } else {
            showToast("Error: " + result.error, "error");
        }
    } catch (error) {
        console.error("Error uploading background:", error);
        showToast("Error al subir el fondo", "error");
    }
};

// Agregar botón de guardar en manager
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        // Agregar botón "Guardar en Manager" al header
        const headerActions = document.querySelector(".header-actions");
        if (headerActions) {
            const saveBtn = document.createElement("button");
            saveBtn.className = "btn btn-success";
            saveBtn.innerHTML = \'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg> Guardar Template\';
            saveBtn.onclick = saveToManager;
            headerActions.insertBefore(saveBtn, headerActions.firstChild);

            // Agregar botón "Volver al Manager"
            const backBtn = document.createElement("a");
            backBtn.href = "index.php?editar=" + TEMPLATE_ID;
            backBtn.className = "btn btn-outline";
            backBtn.innerHTML = \'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Volver\';
            headerActions.insertBefore(backBtn, headerActions.firstChild);
        }

        // Actualizar título
        const headerTitle = document.querySelector(".header h1");
        if (headerTitle) {
            headerTitle.innerHTML = \'<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg> Editando: \' + state.templateName + \' <small style="opacity:0.6;font-weight:normal">[\' + TEMPLATE_SLUG + \']</small>\';
        }
    }, 100);
});

// Función para guardar en el Manager
// IMPORTANTE: Guardamos en FORMATO PROJECT (px) para que se cargue sin conversión
async function saveToManager() {
    showToast("Guardando template...");

    // Guardar en formato PROJECT (valores en px, igual que el estado interno del editor)
    const config = {
        _format: "project",  // Marcador para identificar el formato
        name: state.templateName,
        slug: state.templateSlug || TEMPLATE_SLUG,
        orientation: state.orientation,
        backgroundFilename: state.backgroundFilename || null,
        elements: state.elements.map(el => ({...el})),  // Copia directa del estado
        guides: state.guides || [],
        nextId: state.nextId || (state.elements.length + 1)
    };

    try {
        const formData = new FormData();
        formData.append("action", "save_template");
        formData.append("id_template", TEMPLATE_ID);
        formData.append("config", JSON.stringify(config, null, 2));

        const response = await fetch("editor.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast("Template guardado correctamente", "success");
        } else {
            showToast("Error: " + result.error, "error");
        }
    } catch (error) {
        console.error("Error saving:", error);
        showToast("Error al guardar", "error");
    }
}
</script>
';

// Insertar el script justo antes del cierre del body
$editor_html = str_replace('</body>', $inject_script . '</body>', $editor_html);

// Mostrar el editor modificado
echo $editor_html;
