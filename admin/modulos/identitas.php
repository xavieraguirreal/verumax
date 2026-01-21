<?php
/**
 * MÓDULO: IDENTITAS
 * Gestión de configuración del sitio, páginas y contactos
 */

// Ya estamos autenticados por index.php
// $admin ya está disponible

require_once __DIR__ . '/../../identitas/config.php';

$slug = $admin['slug'];
$pdo = getDBConnection();

// Obtener configuración actual
$instance = getInstanceConfig($slug);
if (!$instance) {
    die('Error: Instancia no encontrada');
}

// Obtener páginas
$stmt = $pdo->prepare("
    SELECT * FROM identitas_paginas
    WHERE id_instancia = :id_instancia
    ORDER BY orden
");
$stmt->execute(['id_instancia' => $instance['id_instancia']]);
$paginas = $stmt->fetchAll();

// Manejar mensaje de éxito desde redirect
$mensaje = '';
$tipo_mensaje = '';
$scroll_to = '';
$active_tab = $_GET['tab'] ?? 'configuracion'; // Tab activo por defecto

if (isset($_GET['success'])) {
    $tipo_mensaje = 'success';
    switch ($_GET['success']) {
        case '1':
            $mensaje = 'Página actualizada correctamente';
            $active_tab = 'paginas';
            break;
        case 'config':
            $mensaje = 'Configuración actualizada correctamente';
            $scroll_to = 'paleta-section';
            $active_tab = 'configuracion';
            break;
        case 'seo':
            $mensaje = 'Configuración SEO actualizada correctamente';
            $scroll_to = 'seo-section';
            $active_tab = 'configuracion';
            break;
        default:
            $mensaje = 'Cambios guardados correctamente';
    }
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['modulo']) && $_GET['modulo'] === 'identitas') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'actualizar_instancia':
            try {
                // Determinar si usa paleta general o propia
                $usar_paleta_general = isset($_POST['identitas_usar_paleta_general']) ? 1 : 0;

                // Actualizar datos ESPECÍFICOS de Identitas en verumax_identi.identitas_config
                $stmt_identi = $pdo->prepare("
                    UPDATE identitas_config
                    SET identitas_usar_paleta_general = :identitas_usar_paleta_general,
                        identitas_paleta_colores_propia = :identitas_paleta_colores_propia,
                        identitas_color_primario_propio = :identitas_color_primario_propio,
                        identitas_color_secundario_propio = :identitas_color_secundario_propio,
                        identitas_color_acento_propio = :identitas_color_acento_propio
                    WHERE id_instancia = :id_instancia
                ");

                $stmt_identi->execute([
                    'identitas_usar_paleta_general' => $usar_paleta_general,
                    'identitas_paleta_colores_propia' => $usar_paleta_general ? null : ($_POST['identitas_paleta_colores_propia'] ?? null),
                    'identitas_color_primario_propio' => $usar_paleta_general ? null : ($_POST['identitas_color_primario_propio'] ?? null),
                    'identitas_color_secundario_propio' => $usar_paleta_general ? null : ($_POST['identitas_color_secundario_propio'] ?? null),
                    'identitas_color_acento_propio' => $usar_paleta_general ? null : ($_POST['identitas_color_acento_propio'] ?? null),
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Mensaje de éxito (sin redirect porque estamos incluidos desde index.php)
                $mensaje = 'Configuración actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'paleta-section';
                $active_tab = 'configuracion'; // Mantener en la pestaña Configuración

                // Limpiar cache y recargar instancia con nuevos datos
                \VERUMax\Services\InstitutionService::clearCache($slug);
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

                // Mensaje de éxito
                $mensaje = 'Página actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'pagina-' . $_POST['id_pagina'];
                $active_tab = 'paginas'; // Mantener en la pestaña Páginas

                // Limpiar cache y recargar instancia y páginas
                \VERUMax\Services\InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);
                $stmt = $pdo->prepare("
                    SELECT * FROM identitas_paginas
                    WHERE id_instancia = :id_instancia
                    ORDER BY orden ASC
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

                // Mensaje de éxito
                $mensaje = 'Mensaje marcado como leído';
                $tipo_mensaje = 'success';
                $scroll_to = 'identitas-tab-contactos';
                $active_tab = 'contactos'; // Mantener en la pestaña Contactos

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

        case 'eliminar_contacto':
            try {
                $stmt = $pdo->prepare("
                    DELETE FROM identitas_contactos
                    WHERE id_contacto = :id_contacto
                    AND id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'id_contacto' => $_POST['id_contacto'],
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Mensaje de éxito
                $mensaje = 'Mensaje eliminado correctamente';
                $tipo_mensaje = 'success';
                $active_tab = 'contactos';

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
                $mensaje = 'Error al eliminar mensaje: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

    }
}
?>

<?php if ($mensaje): ?>
    <!-- Toast Notification -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('<?php echo addslashes($mensaje); ?>', '<?php echo $tipo_mensaje; ?>');

            <?php if ($scroll_to): ?>
            // Auto-scroll al elemento después de guardar
            setTimeout(function() {
                const elemento = document.getElementById('<?php echo $scroll_to; ?>');
                if (elemento) {
                    // Asegurar que el tab esté visible primero
                    const parentTab = elemento.closest('.identitas-tab-content');
                    if (parentTab) {
                        parentTab.style.display = 'block';
                    }

                    // Hacer scroll después de que el tab esté visible
                    setTimeout(() => {
                        elemento.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        // Agregar highlight temporal
                        elemento.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
                        setTimeout(() => {
                            elemento.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
                        }, 2000);
                    }, 100);
                }
            }, 500);
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<!-- Tabs internos del módulo -->
<div class="bg-white rounded-lg shadow-sm">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button onclick="switchIdentitasTab('configuracion')" class="identitas-tab-button px-6 py-4 text-sm font-medium border-b-2 <?php echo $active_tab === 'configuracion' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> transition">
                <i data-lucide="settings" class="w-4 h-4 inline mr-2"></i>
                Configuración
            </button>
            <button onclick="switchIdentitasTab('paginas')" class="identitas-tab-button px-6 py-4 text-sm font-medium border-b-2 <?php echo $active_tab === 'paginas' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> transition">
                <i data-lucide="file-text" class="w-4 h-4 inline mr-2"></i>
                Páginas (<?php echo count($paginas); ?>)
            </button>
            <button onclick="window.location.href='?modulo=identitas_templates'" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition">
                <i data-lucide="blocks" class="w-4 h-4 inline mr-2"></i>
                Templates
            </button>
            <button onclick="switchIdentitasTab('contactos')" class="identitas-tab-button px-6 py-4 text-sm font-medium border-b-2 <?php echo $active_tab === 'contactos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> transition">
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
    <div id="identitas-tab-configuracion" class="identitas-tab-content <?php echo $active_tab === 'configuracion' ? 'active' : ''; ?> p-6" style="display: <?php echo $active_tab === 'configuracion' ? 'block' : 'none'; ?>;">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Configuración de Identitas</h2>

        <form method="POST" action="?modulo=identitas" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_instancia">

            <!-- Paleta de Colores -->
            <hr class="my-6 border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i data-lucide="palette" class="w-5 h-5 inline mr-2"></i>
                Paleta de Colores de Identitas
            </h3>

            <!-- Mensaje informativo -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-2">Configuración de Paleta de Colores</p>
                        <p>Puedes elegir entre usar la <strong>paleta general</strong> (configurada en GENERAL) o una <strong>paleta propia</strong> específica para Identitas.</p>
                    </div>
                </div>
            </div>

            <!-- Selector: Paleta General vs Propia -->
            <div class="mb-6">
                <label class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="identitas_usar_paleta_general" id="identitas-usar-paleta-general"
                           value="1"
                           <?php echo ($instance['identitas_usar_paleta_general'] ?? 1) == 1 ? 'checked' : ''; ?>
                           class="w-5 h-5 text-blue-600 rounded">
                    <div>
                        <div class="font-semibold text-gray-900">
                            <i data-lucide="link" class="w-4 h-4 inline mr-1"></i>
                            Usar paleta general (configurada en GENERAL)
                        </div>
                        <div class="text-sm text-gray-600 mt-1">Los colores se heredan de la configuración global</div>
                    </div>
                </label>
            </div>

            <!-- Paleta Propia de Identitas (solo visible si NO usa paleta general) -->
            <div id="identitas-paleta-propia-section" style="display: <?php echo ($instance['identitas_usar_paleta_general'] ?? 1) == 1 ? 'none' : 'block'; ?>;">
                <div class="border-2 border-purple-200 bg-purple-50 rounded-lg p-6">
                    <h4 class="font-semibold text-purple-900 mb-4 flex items-center gap-2">
                        <i data-lucide="paintbrush" class="w-5 h-5"></i>
                        Paleta Propia de Identitas
                    </h4>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Paleta predefinida</label>
                        <select name="identitas_paleta_colores_propia" id="identitas-paleta-select"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="verde-elegante" <?php echo ($instance['identitas_paleta_colores_propia'] ?? '') === 'verde-elegante' ? 'selected' : ''; ?>>
                                🌿 Verde Elegante (Educación, Salud)
                            </option>
                            <option value="azul-profesional" <?php echo ($instance['identitas_paleta_colores_propia'] ?? '') === 'azul-profesional' ? 'selected' : ''; ?>>
                                💼 Azul Profesional (Corporativo, Tecnología)
                            </option>
                            <option value="morado-creativo" <?php echo ($instance['identitas_paleta_colores_propia'] ?? '') === 'morado-creativo' ? 'selected' : ''; ?>>
                                🎨 Morado Creativo (Arte, Diseño)
                            </option>
                            <option value="naranja-energetico" <?php echo ($instance['identitas_paleta_colores_propia'] ?? '') === 'naranja-energetico' ? 'selected' : ''; ?>>
                                ⚡ Naranja Energético (Deportes, Juventud)
                            </option>
                            <option value="rojo-institucional" <?php echo ($instance['identitas_paleta_colores_propia'] ?? '') === 'rojo-institucional' ? 'selected' : ''; ?>>
                                🏛️ Rojo Institucional (Gobierno, Legal)
                            </option>
                            <option value="gris-minimalista" <?php echo ($instance['identitas_paleta_colores_propia'] ?? '') === 'gris-minimalista' ? 'selected' : ''; ?>>
                                ⬛ Gris Minimalista (Arquitectura, Lujo)
                            </option>
                            <option value="personalizado" <?php echo ($instance['identitas_paleta_colores_propia'] ?? 'personalizado') === 'personalizado' ? 'selected' : ''; ?>>
                                🎯 Personalizado
                            </option>
                        </select>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color Primario</label>
                            <div class="flex gap-2">
                                <input type="color" name="identitas_color_primario_propio" id="identitas-color-primario"
                                       value="<?php echo htmlspecialchars($instance['identitas_color_primario_propio'] ?? '#2E7D32'); ?>"
                                       class="h-10 w-20 border border-gray-300 rounded-lg">
                                <input type="text" id="identitas-color-primario-text"
                                       value="<?php echo htmlspecialchars($instance['identitas_color_primario_propio'] ?? '#2E7D32'); ?>" readonly
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color Secundario</label>
                            <div class="flex gap-2">
                                <input type="color" name="identitas_color_secundario_propio" id="identitas-color-secundario"
                                       value="<?php echo htmlspecialchars($instance['identitas_color_secundario_propio'] ?? '#1B5E20'); ?>"
                                       class="h-10 w-20 border border-gray-300 rounded-lg">
                                <input type="text" id="identitas-color-secundario-text"
                                       value="<?php echo htmlspecialchars($instance['identitas_color_secundario_propio'] ?? '#1B5E20'); ?>" readonly
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color de Acento</label>
                            <div class="flex gap-2">
                                <input type="color" name="identitas_color_acento_propio" id="identitas-color-acento"
                                       value="<?php echo htmlspecialchars($instance['identitas_color_acento_propio'] ?? '#66BB6A'); ?>"
                                       class="h-10 w-20 border border-gray-300 rounded-lg">
                                <input type="text" id="identitas-color-acento-text"
                                       value="<?php echo htmlspecialchars($instance['identitas_color_acento_propio'] ?? '#66BB6A'); ?>" readonly
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>

    <!-- Tab: Páginas -->
    <div id="identitas-tab-paginas" class="identitas-tab-content p-6" style="display: none;">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Editar Páginas</h2>

        <div class="space-y-6">
            <?php foreach ($paginas as $pagina): ?>
                <div id="pagina-<?php echo $pagina['id_pagina']; ?>" class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                    <form method="POST" action="?modulo=identitas">
                        <input type="hidden" name="accion" value="actualizar_pagina">
                        <input type="hidden" name="id_pagina" value="<?php echo $pagina['id_pagina']; ?>">

                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($pagina['slug']); ?></h3>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="visible_menu" <?php echo $pagina['visible_menu'] ? 'checked' : ''; ?>>
                                <span class="text-sm">Visible en menú</span>
                            </label>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
                                <input type="text" name="titulo" value="<?php echo htmlspecialchars($pagina['titulo']); ?>" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Contenido
                                    <span class="text-xs text-gray-500 font-normal ml-2">(Editor visual - Usa los botones para dar formato)</span>
                                </label>
                                <textarea name="contenido" class="ckeditor-contenido w-full"><?php echo htmlspecialchars($pagina['contenido']); ?></textarea>
                                <p class="mt-2 text-sm text-gray-500">
                                    <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                                    Usa la barra de herramientas para dar formato (negrita, cursiva, listas, enlaces, etc.)
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                                    <i data-lucide="save" class="w-5 h-5"></i>
                                    Guardar Página
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tab: Contactos -->
    <div id="identitas-tab-contactos" class="identitas-tab-content <?php echo $active_tab === 'contactos' ? 'active' : ''; ?> p-6" style="display: <?php echo $active_tab === 'contactos' ? 'block' : 'none'; ?>;">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h2 class="text-xl font-bold text-gray-900">Mensajes de Contacto</h2>

            <!-- Barra de búsqueda y filtros -->
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Búsqueda -->
                <div class="relative">
                    <input type="text" id="contactos-busqueda" placeholder="Buscar..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm w-full sm:w-48"
                           onkeyup="filtrarContactos()">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>

                <!-- Filtros -->
                <div class="flex rounded-lg border border-gray-300 overflow-hidden">
                    <button type="button" onclick="filtrarPorEstado('todos')"
                            class="filtro-contactos px-3 py-2 text-sm font-medium bg-blue-600 text-white" data-filtro="todos">
                        Todos
                    </button>
                    <button type="button" onclick="filtrarPorEstado('no-leidos')"
                            class="filtro-contactos px-3 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 border-l" data-filtro="no-leidos">
                        No leídos
                        <?php $count_no_leidos = count(array_filter($contactos, fn($c) => !$c['leido'])); ?>
                        <?php if ($count_no_leidos > 0): ?>
                            <span class="ml-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo $count_no_leidos; ?></span>
                        <?php endif; ?>
                    </button>
                    <button type="button" onclick="filtrarPorEstado('leidos')"
                            class="filtro-contactos px-3 py-2 text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 border-l" data-filtro="leidos">
                        Leídos
                    </button>
                </div>
            </div>
        </div>

        <?php if (empty($contactos)): ?>
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                <p class="text-gray-500">No hay mensajes de contacto</p>
            </div>
        <?php else: ?>
            <div class="space-y-4" id="lista-contactos">
                <?php foreach ($contactos as $contacto): ?>
                    <div class="contacto-item border rounded-lg p-4 transition-all <?php echo $contacto['leido'] ? 'bg-white' : 'bg-blue-50 border-blue-300'; ?>"
                         data-leido="<?php echo $contacto['leido'] ? '1' : '0'; ?>"
                         data-nombre="<?php echo strtolower(htmlspecialchars($contacto['nombre'])); ?>"
                         data-email="<?php echo strtolower(htmlspecialchars($contacto['email'])); ?>"
                         data-asunto="<?php echo strtolower(htmlspecialchars($contacto['asunto'] ?? '')); ?>"
                         data-mensaje="<?php echo strtolower(htmlspecialchars(substr($contacto['mensaje'], 0, 200))); ?>">

                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <?php if (!$contacto['leido']): ?>
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    <?php endif; ?>
                                    <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($contacto['nombre']); ?></h3>
                                </div>
                                <p class="text-sm text-gray-600">
                                    <a href="mailto:<?php echo htmlspecialchars($contacto['email']); ?>" class="hover:text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars($contacto['email']); ?>
                                    </a>
                                    <?php if ($contacto['telefono']): ?>
                                        <span class="mx-2">•</span>
                                        <a href="tel:<?php echo htmlspecialchars($contacto['telefono']); ?>" class="hover:text-blue-600">
                                            <?php echo htmlspecialchars($contacto['telefono']); ?>
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($contacto['fecha_contacto'])); ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($contacto['asunto']): ?>
                            <p class="text-sm font-medium text-gray-800 mb-2">
                                <i data-lucide="tag" class="w-3 h-3 inline mr-1"></i>
                                <?php echo htmlspecialchars($contacto['asunto']); ?>
                            </p>
                        <?php endif; ?>

                        <p class="text-sm text-gray-700 whitespace-pre-wrap mb-4"><?php echo htmlspecialchars($contacto['mensaje']); ?></p>

                        <!-- Acciones -->
                        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-200">
                            <!-- Responder -->
                            <a href="mailto:<?php echo htmlspecialchars($contacto['email']); ?>?subject=Re: <?php echo htmlspecialchars($contacto['asunto'] ?? 'Tu mensaje'); ?>"
                               class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <i data-lucide="reply" class="w-4 h-4"></i>
                                Responder
                            </a>

                            <?php if (!$contacto['leido']): ?>
                                <!-- Marcar leído -->
                                <form method="POST" action="?modulo=identitas" class="inline">
                                    <input type="hidden" name="accion" value="marcar_leido">
                                    <input type="hidden" name="id_contacto" value="<?php echo $contacto['id_contacto']; ?>">
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                        Marcar leído
                                    </button>
                                </form>
                            <?php endif; ?>

                            <!-- Eliminar -->
                            <button type="button" onclick="confirmarEliminarContacto(<?php echo $contacto['id_contacto']; ?>, '<?php echo addslashes($contacto['nombre']); ?>')"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                Eliminar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Mensaje cuando no hay resultados de búsqueda -->
            <div id="contactos-sin-resultados" class="hidden text-center py-8 bg-gray-50 rounded-lg">
                <i data-lucide="search-x" class="w-10 h-10 text-gray-300 mx-auto mb-2"></i>
                <p class="text-gray-500">No se encontraron contactos</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function switchIdentitasTab(tabName) {
    document.querySelectorAll('.identitas-tab-content').forEach(tab => {
        tab.style.display = 'none';
    });

    document.querySelectorAll('.identitas-tab-button').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    document.getElementById('identitas-tab-' + tabName).style.display = 'block';

    // Actualizar el botón activo
    const buttons = document.querySelectorAll('.identitas-tab-button');
    buttons.forEach(btn => {
        const btnText = btn.textContent.trim().toLowerCase();
        if ((tabName === 'configuracion' && btnText.includes('configuración')) ||
            (tabName === 'paginas' && btnText.includes('páginas')) ||
            (tabName === 'contactos' && btnText.includes('contactos'))) {
            btn.classList.remove('border-transparent', 'text-gray-500');
            btn.classList.add('border-blue-500', 'text-blue-600');
        }
    });

    // Actualizar campos hidden en formularios con el tab activo
    updateIdentitasActiveTabFields(tabName);

    lucide.createIcons();
}

// Función para actualizar campos hidden de active_tab
function updateIdentitasActiveTabFields(tabName) {
    document.querySelectorAll('input[name="active_tab"]').forEach(input => {
        input.value = tabName;
    });
}

// Al cargar la página, verificar si hay un tab en la URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');

    if (tabParam) {
        switchIdentitasTab(tabParam);
    }

    // Agregar campos active_tab a todos los formularios al cargar
    const activeTabName = tabParam || 'configuracion';
    document.querySelectorAll('form[action*="modulo=identitas"]').forEach(form => {
        if (!form.querySelector('input[name="active_tab"]')) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'active_tab';
            hiddenInput.value = activeTabName;
            form.appendChild(hiddenInput);
        }
    });

    // Inicializar sistema de detección de cambios
    initIdentitasFormChangeDetection();

    lucide.createIcons();
});

// ============================================================================
// SISTEMA DE TOAST NOTIFICATIONS
// ============================================================================
function showToast(message, type = 'success') {
    // Crear contenedor de toasts si no existe
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed top-4 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(toastContainer);
    }

    // Crear toast
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    const icon = type === 'success' ? 'check-circle' : 'alert-circle';

    toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] transform transition-all duration-300 translate-x-full`;
    toast.innerHTML = `
        <i data-lucide="${icon}" class="w-5 h-5 flex-shrink-0"></i>
        <span class="flex-1">${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    `;

    toastContainer.appendChild(toast);
    lucide.createIcons();

    // Animación de entrada
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 10);

    // Auto-ocultar después de 4 segundos
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ============================================================================
// SISTEMA DE DETECCIÓN DE CAMBIOS EN FORMULARIOS
// ============================================================================
function initIdentitasFormChangeDetection() {
    document.querySelectorAll('form[action*="modulo=identitas"]').forEach(form => {
        const submitBtn = form.querySelector('button.save-button[type="submit"]');
        if (!submitBtn) return;

        // Guardar datos originales del formulario
        const formData = new FormData(form);
        const originalData = {};
        for (let [key, value] of formData.entries()) {
            originalData[key] = value;
        }

        // Guardar textos originales del botón
        const originalBtnHTML = submitBtn.innerHTML;

        // Estado inicial: botón en gris (sin cambios)
        submitBtn.className = 'px-6 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed transition font-semibold flex items-center gap-2 save-button';
        submitBtn.disabled = true;

        // Función para verificar cambios
        const checkChanges = () => {
            const currentData = new FormData(form);
            let hasChanges = false;

            // Verificar cambios en valores existentes y nuevas keys
            for (let [key, value] of currentData.entries()) {
                if (key !== 'active_tab' && key !== 'accion') {
                    if (!(key in originalData) || originalData[key] !== value) {
                        hasChanges = true;
                        break;
                    }
                }
            }

            // Verificar keys que desaparecieron (checkboxes desmarcados)
            if (!hasChanges) {
                for (let key in originalData) {
                    if (key !== 'active_tab' && key !== 'accion' && !currentData.has(key)) {
                        hasChanges = true;
                        break;
                    }
                }
            }

            // Actualizar estado del botón
            if (hasChanges) {
                submitBtn.disabled = false;
                submitBtn.className = 'px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button';
            } else {
                submitBtn.disabled = true;
                submitBtn.className = 'px-6 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed transition font-semibold flex items-center gap-2 save-button';
            }
        };

        // Escuchar cambios en todos los campos
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', checkChanges);
            field.addEventListener('change', checkChanges);
        });

        // Feedback al enviar
        form.addEventListener('submit', function(e) {
            submitBtn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 inline mr-2 animate-spin"></i>Guardando...';
            lucide.createIcons();
        });
    });
}

// Paletas predefinidas
const paletas = {
    'verde-elegante': { primario: '#2E7D32', secundario: '#1B5E20', acento: '#66BB6A' },
    'azul-profesional': { primario: '#1976D2', secundario: '#0D47A1', acento: '#42A5F5' },
    'morado-creativo': { primario: '#7B1FA2', secundario: '#4A148C', acento: '#BA68C8' },
    'naranja-energetico': { primario: '#F57C00', secundario: '#E65100', acento: '#FFB74D' },
    'rojo-institucional': { primario: '#C62828', secundario: '#B71C1C', acento: '#EF5350' },
    'gris-minimalista': { primario: '#424242', secundario: '#212121', acento: '#757575' }
};

const paletaSelect = document.getElementById('paleta-select');
const colorPrimario = document.getElementById('color-primario');
const colorSecundario = document.getElementById('color-secundario');
const colorAcento = document.getElementById('color-acento');
const colorPrimarioText = document.getElementById('color-primario-text');
const colorSecundarioText = document.getElementById('color-secundario-text');
const colorAcentoText = document.getElementById('color-acento-text');

paletaSelect?.addEventListener('change', function() {
    const paleta = this.value;
    if (paleta !== 'personalizado' && paletas[paleta]) {
        colorPrimario.value = paletas[paleta].primario;
        colorSecundario.value = paletas[paleta].secundario;
        colorAcento.value = paletas[paleta].acento;
        colorPrimarioText.value = paletas[paleta].primario;
        colorSecundarioText.value = paletas[paleta].secundario;
        colorAcentoText.value = paletas[paleta].acento;
    }
});

// Sync color pickers with text
colorPrimario?.addEventListener('input', (e) => { colorPrimarioText.value = e.target.value; });
colorSecundario?.addEventListener('input', (e) => { colorSecundarioText.value = e.target.value; });
colorAcento?.addEventListener('input', (e) => { colorAcentoText.value = e.target.value; });

// ============================================================================
// PALETA PROPIA DE IDENTITAS
// ============================================================================

// Mostrar/ocultar sección de paleta propia según checkbox
const usarPaletaGeneralCheckbox = document.getElementById('identitas-usar-paleta-general');
const paletaPropiaSection = document.getElementById('identitas-paleta-propia-section');

usarPaletaGeneralCheckbox?.addEventListener('change', function() {
    if (this.checked) {
        paletaPropiaSection.style.display = 'none';
    } else {
        paletaPropiaSection.style.display = 'block';
    }
});

// Paleta propia de Identitas
const identitasPaletaSelect = document.getElementById('identitas-paleta-select');
const identitasColorPrimario = document.getElementById('identitas-color-primario');
const identitasColorSecundario = document.getElementById('identitas-color-secundario');
const identitasColorAcento = document.getElementById('identitas-color-acento');
const identitasColorPrimarioText = document.getElementById('identitas-color-primario-text');
const identitasColorSecundarioText = document.getElementById('identitas-color-secundario-text');
const identitasColorAcentoText = document.getElementById('identitas-color-acento-text');

identitasPaletaSelect?.addEventListener('change', function() {
    const paleta = this.value;
    if (paleta !== 'personalizado' && paletas[paleta]) {
        identitasColorPrimario.value = paletas[paleta].primario;
        identitasColorSecundario.value = paletas[paleta].secundario;
        identitasColorAcento.value = paletas[paleta].acento;
        identitasColorPrimarioText.value = paletas[paleta].primario;
        identitasColorSecundarioText.value = paletas[paleta].secundario;
        identitasColorAcentoText.value = paletas[paleta].acento;
    }
});

// Sync color pickers con text fields (paleta propia)
identitasColorPrimario?.addEventListener('input', (e) => { identitasColorPrimarioText.value = e.target.value; });
identitasColorSecundario?.addEventListener('input', (e) => { identitasColorSecundarioText.value = e.target.value; });
identitasColorAcento?.addEventListener('input', (e) => { identitasColorAcentoText.value = e.target.value; });

// ============================================================================
// GESTIÓN DE CONTACTOS - Filtros, Búsqueda y Eliminación
// ============================================================================

let filtroActual = 'todos';

// Filtrar por estado (todos, no-leidos, leidos)
function filtrarPorEstado(estado) {
    filtroActual = estado;

    // Actualizar botones
    document.querySelectorAll('.filtro-contactos').forEach(btn => {
        if (btn.dataset.filtro === estado) {
            btn.classList.remove('bg-white', 'text-gray-700');
            btn.classList.add('bg-blue-600', 'text-white');
        } else {
            btn.classList.remove('bg-blue-600', 'text-white');
            btn.classList.add('bg-white', 'text-gray-700');
        }
    });

    aplicarFiltros();
}

// Filtrar por búsqueda de texto
function filtrarContactos() {
    aplicarFiltros();
}

// Aplicar ambos filtros
function aplicarFiltros() {
    const busqueda = (document.getElementById('contactos-busqueda')?.value || '').toLowerCase();
    const items = document.querySelectorAll('.contacto-item');
    let visibles = 0;

    items.forEach(item => {
        const leido = item.dataset.leido === '1';
        const nombre = item.dataset.nombre || '';
        const email = item.dataset.email || '';
        const asunto = item.dataset.asunto || '';
        const mensaje = item.dataset.mensaje || '';

        // Filtro por estado
        let pasaFiltroEstado = true;
        if (filtroActual === 'no-leidos' && leido) pasaFiltroEstado = false;
        if (filtroActual === 'leidos' && !leido) pasaFiltroEstado = false;

        // Filtro por búsqueda
        let pasaFiltroBusqueda = true;
        if (busqueda) {
            pasaFiltroBusqueda = nombre.includes(busqueda) ||
                                 email.includes(busqueda) ||
                                 asunto.includes(busqueda) ||
                                 mensaje.includes(busqueda);
        }

        if (pasaFiltroEstado && pasaFiltroBusqueda) {
            item.style.display = 'block';
            visibles++;
        } else {
            item.style.display = 'none';
        }
    });

    // Mostrar mensaje si no hay resultados
    const sinResultados = document.getElementById('contactos-sin-resultados');
    if (sinResultados) {
        sinResultados.classList.toggle('hidden', visibles > 0);
    }
}

// Confirmar eliminación de contacto
function confirmarEliminarContacto(id, nombre) {
    if (confirm(`¿Estás seguro de eliminar el mensaje de "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?modulo=identitas';

        const accionInput = document.createElement('input');
        accionInput.type = 'hidden';
        accionInput.name = 'accion';
        accionInput.value = 'eliminar_contacto';
        form.appendChild(accionInput);

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id_contacto';
        idInput.value = id;
        form.appendChild(idInput);

        const tabInput = document.createElement('input');
        tabInput.type = 'hidden';
        tabInput.name = 'active_tab';
        tabInput.value = 'contactos';
        form.appendChild(tabInput);

        document.body.appendChild(form);
        form.submit();
    }
}

// ============================================================================
// PANEL DE AYUDA
// ============================================================================

const contenidoAyudaIdentitas = {
    'configuracion': {
        titulo: 'Configuración de Identitas',
        contenido: `
            <div class="space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="globe" class="w-4 h-4"></i> Tu Sitio Web
                    </h3>
                    <p class="text-sm text-green-700">Identitas es tu presencia digital profesional. Configurá cómo se ve tu página pública.</p>
                    <p class="text-xs text-green-600 mt-1">Tu URL: <strong>tuinstitucion.verumax.com</strong></p>
                    <button onclick="abrirTutorial('configurar-sitio')" class="mt-3 w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="palette" class="w-4 h-4"></i> Paleta Propia
                    </h3>
                    <p class="text-sm text-blue-700">Podés usar los colores generales o definir una paleta específica.</p>
                    <div class="mt-2 text-xs text-blue-600 bg-blue-100 rounded p-2">
                        <strong>Tip:</strong> Si dejás la paleta vacía, se usan los colores de "General".
                    </div>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="award" class="w-4 h-4"></i> Integración con Certificatum
                    </h3>
                    <p class="text-sm text-purple-700">Elegí cómo mostrar los certificados en tu sitio:</p>
                    <ul class="text-xs text-purple-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Sección:</strong> Dentro de una página existente</li>
                        <li><strong>Página:</strong> Como página independiente en el menú</li>
                        <li><strong>Portal:</strong> Acceso completo con búsqueda</li>
                    </ul>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="eye" class="w-4 h-4"></i> Preview
                    </h3>
                    <p class="text-sm text-amber-700">Usá el botón "Ver sitio" en el header para ver cómo queda tu sitio público.</p>
                </div>
            </div>
        `
    },
    'paginas': {
        titulo: 'Gestión de Páginas',
        contenido: `
            <div class="space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="file-plus" class="w-4 h-4"></i> Crear Páginas
                    </h3>
                    <p class="text-sm text-green-700">Agregá páginas a tu sitio: Inicio, Nosotros, Servicios, Cursos, etc.</p>
                    <ul class="text-xs text-green-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Título:</strong> Nombre que aparece en el menú</li>
                        <li><strong>Slug:</strong> URL amigable (ej: "nosotros")</li>
                        <li><strong>Contenido:</strong> El texto de la página</li>
                    </ul>
                    <button onclick="abrirTutorial('crear-pagina')" class="mt-3 w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="edit-3" class="w-4 h-4"></i> Editor Visual (WYSIWYG)
                    </h3>
                    <p class="text-sm text-blue-700">Formateá tu contenido sin conocer HTML:</p>
                    <ul class="text-xs text-blue-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>B</strong> - Negrita | <strong>I</strong> - Cursiva</li>
                        <li>Listas con viñetas y numeradas</li>
                        <li>Enlaces a otras páginas o sitios externos</li>
                        <li>Imágenes y videos embebidos</li>
                    </ul>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="menu" class="w-4 h-4"></i> Orden en Menú
                    </h3>
                    <p class="text-sm text-amber-700">Cambiá el orden con el campo "Orden" (número menor = más a la izquierda).</p>
                    <div class="mt-2 text-xs text-amber-600 bg-amber-100 rounded p-2">
                        <strong>Tip:</strong> Usá números como 10, 20, 30 para poder insertar páginas intermedias después.
                    </div>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="eye-off" class="w-4 h-4"></i> Visibilidad
                    </h3>
                    <p class="text-sm text-purple-700">Las páginas pueden estar:</p>
                    <ul class="text-xs text-purple-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Publicada:</strong> Visible para todos</li>
                        <li><strong>Borrador:</strong> Solo visible en el admin</li>
                        <li><strong>En menú:</strong> Aparece en la navegación</li>
                    </ul>
                </div>
            </div>
        `
    },
    'templates': {
        titulo: 'Plantillas de Diseño',
        contenido: `
            <div class="space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="layout-template" class="w-4 h-4"></i> ¿Qué son las Plantillas?
                    </h3>
                    <p class="text-sm text-green-700">Las plantillas definen el diseño visual de los certificados y documentos.</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="image" class="w-4 h-4"></i> Imagen de Fondo
                    </h3>
                    <p class="text-sm text-blue-700">Subí una imagen A4 horizontal (1122x793px) como fondo del certificado.</p>
                    <ul class="text-xs text-blue-600 mt-2 space-y-1 ml-4 list-disc">
                        <li>Formato: JPG o PNG</li>
                        <li>Incluí bordes decorativos, logo y firma</li>
                        <li>Dejá espacio para el texto dinámico</li>
                    </ul>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="type" class="w-4 h-4"></i> Posicionamiento
                    </h3>
                    <p class="text-sm text-purple-700">Configurá dónde aparece cada elemento:</p>
                    <ul class="text-xs text-purple-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Nombre:</strong> Posición del nombre del estudiante</li>
                        <li><strong>Curso:</strong> Posición del nombre del curso</li>
                        <li><strong>Fecha:</strong> Posición de la fecha de emisión</li>
                        <li><strong>QR:</strong> Posición del código de validación</li>
                    </ul>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="copy" class="w-4 h-4"></i> Reutilización
                    </h3>
                    <p class="text-sm text-amber-700">Podés asignar la misma plantilla a varios cursos o crear una específica para cada uno.</p>
                </div>
            </div>
        `
    },
    'contactos': {
        titulo: 'Mensajes de Contacto',
        contenido: `
            <div class="space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="inbox" class="w-4 h-4"></i> Bandeja de Entrada
                    </h3>
                    <p class="text-sm text-green-700">Acá llegan los mensajes del formulario de contacto de tu sitio.</p>
                    <p class="text-xs text-green-600 mt-1">Los nuevos mensajes se marcan como "No leídos".</p>
                    <button onclick="abrirTutorial('gestionar-contactos')" class="mt-3 w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="filter" class="w-4 h-4"></i> Filtros y Búsqueda
                    </h3>
                    <p class="text-sm text-blue-700">Organizá tu bandeja de entrada:</p>
                    <ul class="text-xs text-blue-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Todos:</strong> Ver todos los mensajes</li>
                        <li><strong>No leídos:</strong> Mensajes pendientes de leer</li>
                        <li><strong>Archivados:</strong> Mensajes procesados</li>
                    </ul>
                    <p class="text-xs text-blue-600 mt-2">Usá la búsqueda para encontrar por nombre o email.</p>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="mail" class="w-4 h-4"></i> Acciones
                    </h3>
                    <p class="text-sm text-purple-700">Opciones disponibles para cada mensaje:</p>
                    <ul class="text-xs text-purple-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Responder:</strong> Abre tu cliente de email</li>
                        <li><strong>Archivar:</strong> Marca como procesado</li>
                        <li><strong>Eliminar:</strong> Borra permanentemente</li>
                    </ul>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="bell" class="w-4 h-4"></i> Notificaciones
                    </h3>
                    <p class="text-sm text-amber-700">Configurá alertas por email para nuevos mensajes desde el módulo Actividad.</p>
                </div>
            </div>
        `
    },
    'general': {
        titulo: 'Identitas - Presencia Digital',
        contenido: `
            <div class="space-y-4">
                <div class="bg-gradient-to-br from-green-50 to-blue-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="globe" class="w-4 h-4"></i> ¿Qué es Identitas?
                    </h3>
                    <p class="text-sm text-gray-700">Tu sitio web profesional integrado con todos los módulos de VERUMax.</p>
                    <p class="text-xs text-gray-600 mt-1">Sin necesidad de hosting ni conocimientos técnicos.</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Secciones disponibles:</h3>
                    <ul class="text-sm text-gray-600 space-y-3">
                        <li class="flex items-start gap-2">
                            <i data-lucide="settings" class="w-4 h-4 text-blue-400 mt-0.5"></i>
                            <div>
                                <strong>Configuración:</strong>
                                <p class="text-xs text-gray-500">Colores, integración con Certificatum</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-green-400 mt-0.5"></i>
                            <div>
                                <strong>Páginas:</strong>
                                <p class="text-xs text-gray-500">Contenido del sitio, menú de navegación</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="layout-template" class="w-4 h-4 text-purple-400 mt-0.5"></i>
                            <div>
                                <strong>Templates:</strong>
                                <p class="text-xs text-gray-500">Plantillas de certificados</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-amber-400 mt-0.5"></i>
                            <div>
                                <strong>Contactos:</strong>
                                <p class="text-xs text-gray-500">Mensajes del formulario de contacto</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="lightbulb" class="w-4 h-4"></i> Primeros Pasos
                    </h3>
                    <ul class="text-xs text-blue-700 space-y-1 ml-4 list-disc">
                        <li>Configurá los colores de tu sitio</li>
                        <li>Creá al menos una página de inicio</li>
                        <li>Definí la integración con Certificatum</li>
                        <li>Revisá el sitio con "Ver sitio" en el header</li>
                    </ul>
                </div>
            </div>
        `
    }
};

let panelAyudaAbierto = false;

function togglePanelAyuda() {
    const panel = document.getElementById('panel-ayuda');
    const overlay = document.getElementById('overlay-ayuda');
    const btnFlotante = document.getElementById('btn-ayuda-flotante');

    panelAyudaAbierto = !panelAyudaAbierto;

    if (panelAyudaAbierto) {
        panel.classList.remove('translate-x-full');
        overlay.classList.remove('hidden');
        btnFlotante.classList.add('hidden');
        actualizarAyudaContextual();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } else {
        panel.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        btnFlotante.classList.remove('hidden');
    }
}

function actualizarAyudaContextual() {
    const tabActivo = document.querySelector('.identitas-tab-button.border-blue-500');
    let contexto = 'general';

    if (tabActivo) {
        const onclickAttr = tabActivo.getAttribute('onclick') || '';
        const match = onclickAttr.match(/switchIdentitasTab\('(\w+)'/);
        if (match && contenidoAyudaIdentitas[match[1]]) {
            contexto = match[1];
        }
    }

    document.getElementById('ayuda-contexto-texto').textContent = contenidoAyudaIdentitas[contexto].titulo;
    document.getElementById('ayuda-contenido').innerHTML = contenidoAyudaIdentitas[contexto].contenido;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function filtrarAyuda(termino) {
    termino = termino.toLowerCase().trim();
    const contenedor = document.getElementById('ayuda-contenido');

    if (!termino) {
        actualizarAyudaContextual();
        return;
    }

    let resultados = '';
    let encontrados = 0;

    for (const [key, data] of Object.entries(contenidoAyudaIdentitas)) {
        if (data.contenido.toLowerCase().includes(termino) || data.titulo.toLowerCase().includes(termino)) {
            encontrados++;
            resultados += `<div class="mb-4 p-3 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100 transition" onclick="mostrarAyudaSeccion('${key}')"><h4 class="font-semibold text-gray-800">${data.titulo}</h4></div>`;
        }
    }

    if (encontrados === 0) {
        resultados = `<div class="text-center py-8 text-gray-500"><p>No se encontraron resultados</p></div>`;
    }

    contenedor.innerHTML = resultados;
    document.getElementById('ayuda-contexto-texto').textContent = 'Resultados de búsqueda';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function mostrarAyudaSeccion(seccion) {
    document.getElementById('busqueda-ayuda').value = '';
    document.getElementById('ayuda-contexto-texto').textContent = contenidoAyudaIdentitas[seccion].titulo;
    document.getElementById('ayuda-contenido').innerHTML = contenidoAyudaIdentitas[seccion].contenido;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ============================================================================
// SISTEMA DE TUTORIALES PASO A PASO
// ============================================================================

const tutorialesIdentitas = {
    'crear-pagina': {
        titulo: 'Cómo crear una página',
        pasos: [
            {
                titulo: 'Paso 1: Ir a Páginas',
                icono: 'file-text',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Accedé a la sección de páginas:</p>
                    <div class="flex gap-2 mb-4">
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Configuración</span>
                        <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">Páginas</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Contactos</span>
                    </div>
                    <p class="text-sm text-gray-500">Hacé clic en la pestaña "Páginas".</p>
                `
            },
            {
                titulo: 'Paso 2: Nueva página',
                icono: 'plus-circle',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Hacé clic en el botón para crear:</p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 text-center">
                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium">
                            <i data-lucide="plus" class="w-4 h-4 inline mr-2"></i>
                            Nueva Página
                        </button>
                    </div>
                    <p class="text-sm text-gray-500">Se abrirá el formulario de creación.</p>
                `
            },
            {
                titulo: 'Paso 3: Completar datos',
                icono: 'edit-3',
                color: 'purple',
                contenido: `
                    <p class="text-gray-600 mb-4">Completá la información de la página:</p>
                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="text-xs text-gray-500">Título</label>
                            <div class="border rounded-lg p-2 bg-gray-50 text-sm">Nosotros</div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Slug (URL)</label>
                            <div class="border rounded-lg p-2 bg-gray-50 text-sm">nosotros</div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Orden en menú</label>
                            <div class="border rounded-lg p-2 bg-gray-50 text-sm">10</div>
                        </div>
                    </div>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm">
                        <p class="text-amber-700"><i data-lucide="lightbulb" class="w-4 h-4 inline mr-1"></i> El slug se genera automáticamente desde el título.</p>
                    </div>
                `
            },
            {
                titulo: 'Paso 4: Escribir contenido',
                icono: 'type',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Usá el editor visual para escribir:</p>
                    <div class="border rounded-lg p-3 mb-4 bg-gray-50">
                        <div class="flex gap-1 pb-2 border-b mb-2">
                            <span class="px-2 py-1 bg-white border rounded text-xs font-bold">B</span>
                            <span class="px-2 py-1 bg-white border rounded text-xs italic">I</span>
                            <span class="px-2 py-1 bg-white border rounded text-xs">🔗</span>
                            <span class="px-2 py-1 bg-white border rounded text-xs">• Lista</span>
                        </div>
                        <p class="text-sm text-gray-400">Escribí tu contenido aquí...</p>
                    </div>
                    <p class="text-sm text-gray-500">Podés dar formato: negrita, cursiva, listas, enlaces.</p>
                `
            },
            {
                titulo: 'Paso 5: Guardar',
                icono: 'save',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Guardá la página para publicarla:</p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 text-center">
                        <button class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium">
                            <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                            Guardar Página
                        </button>
                    </div>
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm">
                        <p class="text-green-700"><i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i> ¡Listo! La página aparecerá en el menú de tu sitio.</p>
                    </div>
                `
            }
        ]
    },
    'gestionar-contactos': {
        titulo: 'Cómo gestionar contactos',
        pasos: [
            {
                titulo: 'Paso 1: Ver mensajes',
                icono: 'inbox',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Accedé a la bandeja de contactos:</p>
                    <div class="flex gap-2 mb-4">
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Configuración</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Páginas</span>
                        <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">Contactos</span>
                    </div>
                    <p class="text-sm text-gray-500">Los mensajes nuevos aparecen marcados como "No leído".</p>
                `
            },
            {
                titulo: 'Paso 2: Filtrar mensajes',
                icono: 'filter',
                color: 'purple',
                contenido: `
                    <p class="text-gray-600 mb-4">Usá los filtros para organizar:</p>
                    <div class="flex gap-2 mb-4">
                        <span class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Todos</span>
                        <span class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-full text-xs">No leídos</span>
                        <span class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-full text-xs">Archivados</span>
                    </div>
                    <p class="text-sm text-gray-500">También podés buscar por nombre o email.</p>
                `
            },
            {
                titulo: 'Paso 3: Responder',
                icono: 'mail',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Hacé clic en "Responder" para contestar:</p>
                    <div class="border rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium">Juan Pérez</p>
                                <p class="text-sm text-gray-500">juan@email.com</p>
                            </div>
                            <button class="px-3 py-1.5 bg-blue-600 text-white text-xs rounded-lg">
                                <i data-lucide="reply" class="w-3 h-3 inline mr-1"></i> Responder
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Se abrirá tu cliente de email con el destinatario listo.</p>
                `
            },
            {
                titulo: 'Paso 4: Archivar o eliminar',
                icono: 'archive',
                color: 'amber',
                contenido: `
                    <p class="text-gray-600 mb-4">Organizá los mensajes procesados:</p>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                            <i data-lucide="archive" class="w-5 h-5 text-amber-500"></i>
                            <span class="text-sm"><strong>Archivar:</strong> Mueve a archivados</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                            <i data-lucide="trash-2" class="w-5 h-5 text-red-500"></i>
                            <span class="text-sm"><strong>Eliminar:</strong> Borra permanentemente</span>
                        </div>
                    </div>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm">
                        <p class="text-amber-700"><i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i> La eliminación no se puede deshacer.</p>
                    </div>
                `
            }
        ]
    },
    'configurar-sitio': {
        titulo: 'Cómo configurar tu sitio',
        pasos: [
            {
                titulo: 'Paso 1: Ir a Configuración',
                icono: 'settings',
                color: 'gray',
                contenido: `
                    <p class="text-gray-600 mb-4">Accedé a la configuración de Identitas:</p>
                    <div class="flex gap-2 mb-4">
                        <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">Configuración</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Páginas</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Contactos</span>
                    </div>
                `
            },
            {
                titulo: 'Paso 2: Elegir colores',
                icono: 'palette',
                color: 'purple',
                contenido: `
                    <p class="text-gray-600 mb-4">Definí una paleta propia o usá la general:</p>
                    <div class="space-y-2 mb-4">
                        <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer">
                            <input type="radio" name="paleta" checked>
                            <span class="text-sm">Usar colores de General</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer">
                            <input type="radio" name="paleta">
                            <span class="text-sm">Definir paleta propia</span>
                        </label>
                    </div>
                    <p class="text-sm text-gray-500">Si elegís paleta propia, aparecerán los selectores de color.</p>
                `
            },
            {
                titulo: 'Paso 3: Integración Certificatum',
                icono: 'award',
                color: 'amber',
                contenido: `
                    <p class="text-gray-600 mb-4">Elegí cómo mostrar los certificados:</p>
                    <div class="space-y-2 mb-4">
                        <div class="p-3 border rounded-lg">
                            <p class="font-medium text-sm">Sección</p>
                            <p class="text-xs text-gray-500">Dentro de una página existente</p>
                        </div>
                        <div class="p-3 border rounded-lg">
                            <p class="font-medium text-sm">Página</p>
                            <p class="text-xs text-gray-500">Página independiente en el menú</p>
                        </div>
                        <div class="p-3 border rounded-lg">
                            <p class="font-medium text-sm">Portal</p>
                            <p class="text-xs text-gray-500">Acceso completo con búsqueda</p>
                        </div>
                    </div>
                `
            },
            {
                titulo: 'Paso 4: Guardar',
                icono: 'save',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Aplicá los cambios:</p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 text-center">
                        <button class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium">
                            <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                            Guardar Configuración
                        </button>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                        <p class="text-blue-700"><i data-lucide="external-link" class="w-4 h-4 inline mr-1"></i> Usá "Ver sitio" en el header para ver los cambios.</p>
                    </div>
                `
            }
        ]
    }
};

let tutorialActual = null;
let pasoActual = 0;

function abrirTutorial(tutorialId) {
    const tutorial = tutorialesIdentitas[tutorialId];
    if (!tutorial) return;
    tutorialActual = tutorial;
    pasoActual = 0;
    document.getElementById('modal-tutorial').classList.remove('hidden');
    document.getElementById('tutorial-titulo').textContent = tutorial.titulo;
    const dotsContainer = document.getElementById('tutorial-dots');
    dotsContainer.innerHTML = tutorial.pasos.map((_, i) =>
        `<button onclick="irAPaso(${i})" class="w-2 h-2 rounded-full transition-all ${i === 0 ? 'bg-blue-600 w-4' : 'bg-gray-300 hover:bg-gray-400'}"></button>`
    ).join('');
    mostrarPaso(0);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function cerrarTutorial() {
    document.getElementById('modal-tutorial').classList.add('hidden');
    tutorialActual = null;
    pasoActual = 0;
}

function mostrarPaso(index) {
    if (!tutorialActual) return;
    pasoActual = index;
    const paso = tutorialActual.pasos[index];
    const total = tutorialActual.pasos.length;
    const progreso = ((index + 1) / total) * 100;
    document.getElementById('tutorial-progreso').style.width = `${progreso}%`;
    document.getElementById('tutorial-contador').textContent = `${index + 1}/${total}`;
    const colores = { blue: 'bg-blue-100 text-blue-600', green: 'bg-green-100 text-green-600', purple: 'bg-purple-100 text-purple-600', amber: 'bg-amber-100 text-amber-600', gray: 'bg-gray-100 text-gray-600' };
    document.getElementById('tutorial-contenido').innerHTML = `
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 ${colores[paso.color] || colores.blue} rounded-xl flex items-center justify-center">
                <i data-lucide="${paso.icono}" class="w-6 h-6"></i>
            </div>
            <h4 class="text-lg font-bold text-gray-800">${paso.titulo}</h4>
        </div>
        <div class="text-gray-600">${paso.contenido}</div>
    `;
    document.querySelectorAll('#tutorial-dots button').forEach((dot, i) => {
        dot.className = i === index ? 'w-4 h-2 rounded-full bg-blue-600 transition-all' : (i < index ? 'w-2 h-2 rounded-full bg-blue-400 transition-all hover:bg-blue-500' : 'w-2 h-2 rounded-full bg-gray-300 transition-all hover:bg-gray-400');
    });
    const btnAnterior = document.getElementById('btn-tutorial-anterior');
    const btnSiguiente = document.getElementById('btn-tutorial-siguiente');
    btnAnterior.disabled = index === 0;
    if (index === total - 1) {
        btnSiguiente.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Finalizar';
        btnSiguiente.onclick = cerrarTutorial;
    } else {
        btnSiguiente.innerHTML = 'Siguiente <i data-lucide="chevron-right" class="w-4 h-4"></i>';
        btnSiguiente.onclick = tutorialSiguiente;
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function tutorialAnterior() { if (pasoActual > 0) mostrarPaso(pasoActual - 1); }
function tutorialSiguiente() { if (tutorialActual && pasoActual < tutorialActual.pasos.length - 1) mostrarPaso(pasoActual + 1); }
function irAPaso(index) { mostrarPaso(index); }
</script>

<!-- Panel de Ayuda Lateral -->
<button id="btn-ayuda-flotante" onclick="togglePanelAyuda()"
        class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center z-40 transition-all hover:scale-110"
        title="Ayuda">
    <i data-lucide="help-circle" class="w-7 h-7"></i>
</button>
<div id="panel-ayuda" class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 flex-shrink-0">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold flex items-center gap-2"><i data-lucide="book-open" class="w-5 h-5"></i> Centro de Ayuda</h2>
            <button onclick="togglePanelAyuda()" class="p-1 hover:bg-blue-500 rounded transition"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="mt-3 relative">
            <input type="text" id="busqueda-ayuda" placeholder="Buscar..." onkeyup="filtrarAyuda(this.value)" class="w-full px-4 py-2 rounded-lg text-gray-800 text-sm">
            <i data-lucide="search" class="w-4 h-4 absolute right-3 top-2.5 text-gray-400"></i>
        </div>
    </div>
    <div id="ayuda-contexto" class="px-4 py-2 bg-blue-50 border-b text-sm text-blue-700 flex items-center gap-2 flex-shrink-0">
        <i data-lucide="info" class="w-4 h-4"></i><span id="ayuda-contexto-texto">Identitas</span>
    </div>
    <div id="ayuda-contenido" class="flex-1 overflow-y-auto p-4"></div>
    <div class="p-4 bg-gray-50 border-t flex-shrink-0">
        <a href="mailto:soporte@verumax.com" class="text-xs text-blue-600 hover:underline"><i data-lucide="mail" class="w-3 h-3 inline"></i> Contactar soporte</a>
    </div>
</div>
<div id="overlay-ayuda" onclick="togglePanelAyuda()" class="fixed inset-0 bg-black bg-opacity-30 z-40 hidden"></div>

<!-- Modal Tutorial Paso a Paso -->
<div id="modal-tutorial" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="cerrarTutorial()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-5 rounded-t-2xl flex-shrink-0">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-blue-200 text-xs uppercase tracking-wide mb-1">Tutorial paso a paso</p>
                    <h3 id="tutorial-titulo" class="text-xl font-bold">Título del Tutorial</h3>
                </div>
                <button onclick="cerrarTutorial()" class="p-1 hover:bg-white/20 rounded-lg transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <div class="flex-1 h-2 bg-white/30 rounded-full overflow-hidden">
                    <div id="tutorial-progreso" class="h-full bg-white rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <span id="tutorial-contador" class="text-sm font-medium">1/4</span>
            </div>
        </div>
        <div id="tutorial-contenido" class="flex-1 overflow-y-auto p-6"></div>
        <div class="p-4 border-t bg-gray-50 rounded-b-2xl flex justify-between items-center flex-shrink-0">
            <button id="btn-tutorial-anterior" onclick="tutorialAnterior()"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-lg transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i data-lucide="chevron-left" class="w-4 h-4"></i> Anterior
            </button>
            <div class="flex gap-1" id="tutorial-dots"></div>
            <button id="btn-tutorial-siguiente" onclick="tutorialSiguiente()"
                    class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition flex items-center gap-2">
                Siguiente <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>

<!-- CKEditor 5 - Editor WYSIWYG (sin API key) -->
<style>
    .ck-editor__editable {
        min-height: 350px;
        max-height: 500px;
    }
    .ck-editor__editable:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3) !important;
    }
</style>
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
<script>
// Inicializar CKEditor 5 en todos los textareas de contenido
const editorInstances = {};

document.querySelectorAll('textarea.ckeditor-contenido').forEach(textarea => {
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
            editorInstances[textarea.id || textarea.name] = editor;

            // Sincronizar cambios con textarea para detección de cambios
            editor.model.document.on('change:data', () => {
                textarea.value = editor.getData();
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
            });

            console.log('CKEditor inicializado para:', textarea.name || textarea.id);
        })
        .catch(error => {
            console.error('Error al inicializar CKEditor:', error);
        });
});
</script>
