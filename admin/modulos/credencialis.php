<?php
/**
 * MÓDULO: CREDENCIALIS - Gestión de Credenciales de Membresía
 * Panel de administración para asignar y gestionar credenciales digitales
 *
 * NOTA: Este módulo se carga dentro de admin/index.php
 * Ya está autenticado y $admin está disponible
 */

// Ya estamos autenticados por index.php
// $admin ya está disponible
$slug = $admin['slug'];
$id_instancia = $admin['id_instancia'];

// Cargar configuraciones necesarias
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../general/config.php';
require_once __DIR__ . '/../../credencialis/config.php';

use VERUMax\Services\MemberService;
use VERUMax\Services\DatabaseService;
use VERUMax\Services\InstitutionService;

// Obtener configuración de la instancia
$instance_config = InstitutionService::getConfig($slug);
if (!$instance_config) {
    die('Error: Instancia no encontrada');
}

// Configuración de credenciales (JSON)
$credencial_config = json_decode($instance_config['credencial_config'] ?? '{}', true);

// Variables para mensajes
$mensaje = null;
$tipo_mensaje = 'success';
$active_tab = $_GET['tab'] ?? 'socios';

// Leer mensaje de redirect
if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];
    $tipo_mensaje = $_GET['msg_type'] ?? 'success';
}

// ============================================================================
// PROCESAR ACCIONES AJAX (POST)
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    // Acciones AJAX que devuelven JSON
    if ($accion === 'buscar_miembros_credencialis') {
        header('Content-Type: application/json');
        $buscar = $_POST['buscar'] ?? '';
        $solo_con_credencial = ($_POST['solo_con_credencial'] ?? '0') === '1';

        try {
            $conn = DatabaseService::get('nexus');

            $sql = "
                SELECT
                    m.id_miembro,
                    m.identificador_principal as dni,
                    m.nombre,
                    m.apellido,
                    COALESCE(m.nombre_completo, CONCAT(m.nombre, ' ', m.apellido)) as nombre_completo,
                    m.email,
                    m.estado,
                    m.genero,
                    m.numero_asociado,
                    m.tipo_asociado,
                    m.nombre_entidad,
                    m.categoria_servicio,
                    m.fecha_ingreso,
                    m.foto_url,
                    CASE WHEN m.numero_asociado IS NOT NULL AND m.numero_asociado != '' THEN 1 ELSE 0 END as tiene_credencial
                FROM miembros m
                WHERE m.id_instancia = :id_instancia
                AND m.estado = 'Activo'
            ";

            $params = [':id_instancia' => $id_instancia];

            if (!empty($buscar)) {
                $sql .= " AND (m.identificador_principal LIKE :buscar1
                          OR m.nombre LIKE :buscar2
                          OR m.apellido LIKE :buscar3
                          OR m.nombre_completo LIKE :buscar4
                          OR m.numero_asociado LIKE :buscar5)";
                $buscarParam = "%$buscar%";
                $params[':buscar1'] = $buscarParam;
                $params[':buscar2'] = $buscarParam;
                $params[':buscar3'] = $buscarParam;
                $params[':buscar4'] = $buscarParam;
                $params[':buscar5'] = $buscarParam;
            }

            if ($solo_con_credencial) {
                $sql .= " AND m.numero_asociado IS NOT NULL AND m.numero_asociado != ''";
            }

            $sql .= " ORDER BY m.numero_asociado DESC, m.fecha_alta DESC LIMIT 100";

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $miembros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'miembros' => $miembros]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // Asignar/Actualizar credencial
    if ($accion === 'guardar_credencial') {
        header('Content-Type: application/json');
        $id_miembro = (int)($_POST['id_miembro'] ?? 0);

        if ($id_miembro <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de miembro inválido']);
            exit;
        }

        $datos = [
            'numero_asociado' => trim($_POST['numero_asociado'] ?? ''),
            'tipo_asociado' => trim($_POST['tipo_asociado'] ?? ''),
            'nombre_entidad' => trim($_POST['nombre_entidad'] ?? ''),
            'categoria_servicio' => trim($_POST['categoria_servicio'] ?? ''),
            'fecha_ingreso' => $_POST['fecha_ingreso'] ?? null,
        ];

        // Manejar foto si se subió
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto_tmp = $_FILES['foto']['tmp_name'];
            $foto_name = basename($_FILES['foto']['name']);
            $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));

            if (in_array($foto_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $foto_dir = __DIR__ . '/../../uploads/fotos/' . $slug;
                if (!is_dir($foto_dir)) {
                    mkdir($foto_dir, 0755, true);
                }
                $foto_filename = $id_miembro . '_' . time() . '.' . $foto_ext;
                $foto_path = $foto_dir . '/' . $foto_filename;

                if (move_uploaded_file($foto_tmp, $foto_path)) {
                    $datos['foto_url'] = '/uploads/fotos/' . $slug . '/' . $foto_filename;
                }
            }
        }

        $result = MemberService::actualizarCredencial($id_miembro, $datos);
        echo json_encode($result);
        exit;
    }

    // Quitar credencial (limpiar campos)
    if ($accion === 'quitar_credencial') {
        header('Content-Type: application/json');
        $id_miembro = (int)($_POST['id_miembro'] ?? 0);

        if ($id_miembro <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de miembro inválido']);
            exit;
        }

        $datos = [
            'numero_asociado' => null,
            'tipo_asociado' => null,
            'nombre_entidad' => null,
            'categoria_servicio' => null,
            'fecha_ingreso' => null,
        ];

        $result = MemberService::actualizarCredencial($id_miembro, $datos);
        echo json_encode($result);
        exit;
    }

    // Guardar configuración de credenciales
    if ($accion === 'guardar_config_credencialis') {
        header('Content-Type: application/json');

        $config = [
            'texto_superior' => trim($_POST['texto_superior'] ?? 'CREDENCIAL DE SOCIO'),
            'texto_inferior' => trim($_POST['texto_inferior'] ?? ''),
            'mostrar_foto' => ($_POST['mostrar_foto'] ?? '0') === '1',
        ];

        // Manejar template de fondo si se subió
        if (isset($_FILES['template_fondo']) && $_FILES['template_fondo']['error'] === UPLOAD_ERR_OK) {
            $tpl_tmp = $_FILES['template_fondo']['tmp_name'];
            $tpl_ext = strtolower(pathinfo($_FILES['template_fondo']['name'], PATHINFO_EXTENSION));

            if (in_array($tpl_ext, ['jpg', 'jpeg', 'png'])) {
                $tpl_dir = __DIR__ . '/../../uploads/templates/' . $slug;
                if (!is_dir($tpl_dir)) {
                    mkdir($tpl_dir, 0755, true);
                }
                $tpl_filename = 'credencial_template_' . time() . '.' . $tpl_ext;
                $tpl_path = $tpl_dir . '/' . $tpl_filename;

                if (move_uploaded_file($tpl_tmp, $tpl_path)) {
                    // URL absoluta para que funcione desde cualquier página
                    $config['template_url'] = 'https://' . $slug . '.verumax.com/uploads/templates/' . $slug . '/' . $tpl_filename;
                }
            }
        } else {
            // Mantener template existente
            $config['template_url'] = $credencial_config['template_url'] ?? null;
        }

        try {
            $pdo_general = DatabaseService::get('general');
            $stmt = $pdo_general->prepare("
                UPDATE instances
                SET credencial_config = :config
                WHERE id_instancia = :id_instancia
            ");
            $stmt->execute([
                ':config' => json_encode($config),
                ':id_instancia' => $id_instancia
            ]);

            echo json_encode(['success' => true, 'mensaje' => 'Configuración guardada']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // Obtener siguiente número de asociado
    if ($accion === 'obtener_siguiente_numero') {
        header('Content-Type: application/json');

        try {
            $conn = DatabaseService::get('nexus');
            $stmt = $conn->prepare("
                SELECT MAX(CAST(numero_asociado AS UNSIGNED)) as max_num
                FROM miembros
                WHERE id_instancia = :id_instancia
                AND numero_asociado REGEXP '^[0-9]+$'
            ");
            $stmt->execute([':id_instancia' => $id_instancia]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $siguiente = ($row['max_num'] ?? 0) + 1;
            echo json_encode(['success' => true, 'siguiente' => $siguiente]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'siguiente' => 1]);
        }
        exit;
    }

    // Importar miembros con credencial
    if ($accion === 'importar_miembros_credencialis') {
        header('Content-Type: application/json');

        $datos_json = $_POST['datos'] ?? '[]';
        $registros = json_decode($datos_json, true);

        if (empty($registros)) {
            echo json_encode(['success' => false, 'error' => 'No hay datos para importar']);
            exit;
        }

        $resultados = [
            'insertados' => 0,
            'actualizados' => 0,
            'errores' => []
        ];

        foreach ($registros as $index => $reg) {
            $dni = preg_replace('/[^0-9A-Za-z]/', '', $reg['dni'] ?? '');
            if (empty($dni)) {
                $resultados['errores'][] = "Fila " . ($index + 1) . ": DNI vacío";
                continue;
            }

            // Verificar si existe
            $existe = MemberService::getByIdentificador($id_instancia, $dni);

            if ($existe) {
                // Actualizar credencial
                $datos_cred = [
                    'numero_asociado' => $reg['numero_asociado'] ?? null,
                    'tipo_asociado' => $reg['tipo_asociado'] ?? null,
                    'categoria_servicio' => $reg['categoria_servicio'] ?? null,
                    'fecha_ingreso' => $reg['fecha_ingreso'] ?? null,
                ];
                $result = MemberService::actualizarCredencial($existe['id_miembro'], $datos_cred);
                if ($result['success']) {
                    $resultados['actualizados']++;
                } else {
                    $resultados['errores'][] = "Fila " . ($index + 1) . ": " . $result['mensaje'];
                }
            } else {
                // Crear miembro con credencial
                $result = MemberService::crear([
                    'id_instancia' => $id_instancia,
                    'identificador_principal' => $dni,
                    'nombre' => $reg['nombre'] ?? '',
                    'apellido' => $reg['apellido'] ?? '',
                    'email' => $reg['email'] ?? null,
                    'telefono' => $reg['telefono'] ?? null,
                    'genero' => $reg['genero'] ?? 'No especifica',
                    'tipo_miembro' => 'Socio'
                ]);

                if ($result['success']) {
                    // Ahora asignar credencial
                    $datos_cred = [
                        'numero_asociado' => $reg['numero_asociado'] ?? null,
                        'tipo_asociado' => $reg['tipo_asociado'] ?? null,
                        'categoria_servicio' => $reg['categoria_servicio'] ?? null,
                        'fecha_ingreso' => $reg['fecha_ingreso'] ?? null,
                    ];
                    MemberService::actualizarCredencial($result['id_miembro'], $datos_cred);
                    $resultados['insertados']++;
                } else {
                    $resultados['errores'][] = "Fila " . ($index + 1) . ": " . $result['mensaje'];
                }
            }
        }

        $resultados['success'] = true;
        echo json_encode($resultados);
        exit;
    }
}

// ============================================================================
// OBTENER DATOS PARA LA VISTA
// ============================================================================

// Estadísticas rápidas
try {
    $conn = DatabaseService::get('nexus');

    // Total miembros activos
    $stmt = $conn->prepare("SELECT COUNT(*) FROM miembros WHERE id_instancia = :id AND estado = 'Activo'");
    $stmt->execute([':id' => $id_instancia]);
    $total_miembros = $stmt->fetchColumn();

    // Con credencial asignada
    $stmt = $conn->prepare("SELECT COUNT(*) FROM miembros WHERE id_instancia = :id AND estado = 'Activo' AND numero_asociado IS NOT NULL AND numero_asociado != ''");
    $stmt->execute([':id' => $id_instancia]);
    $con_credencial = $stmt->fetchColumn();

    // Sin credencial
    $sin_credencial = $total_miembros - $con_credencial;

} catch (Exception $e) {
    $total_miembros = 0;
    $con_credencial = 0;
    $sin_credencial = 0;
}

// Tipos de asociado disponibles
$tipos_asociado = ['TITULAR', 'ADHERENTE', 'INST.', 'HONORARIO', 'ESTUDIANTIL'];

// Categorías de servicio
$categorias_servicio = ['SERVICIO BÁSICO', 'SERVICIO PREMIUM', 'SERVICIO VIP', 'GRATUITO'];

?>

<!-- Resumen de Credencialis -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <i data-lucide="id-card" class="w-6 h-6 text-green-600"></i>
            <h2 class="text-xl font-bold text-gray-900">Resumen de Credencialis</h2>
        </div>
        <span class="text-sm text-gray-500">Vista rápida</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Miembros Activos</p>
                    <p class="text-2xl font-bold text-blue-600"><?php echo number_format($total_miembros); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i data-lucide="badge-check" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Con Credencial</p>
                    <p class="text-2xl font-bold text-green-600"><?php echo number_format($con_credencial); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 rounded-lg">
                    <i data-lucide="user-x" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Sin Credencial</p>
                    <p class="text-2xl font-bold text-amber-600"><?php echo number_format($sin_credencial); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mensaje de éxito/error -->
<?php if ($mensaje): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $tipo_mensaje === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'; ?>">
        <div class="flex items-center gap-2 <?php echo $tipo_mensaje === 'success' ? 'text-green-800' : 'text-red-800'; ?>">
            <i data-lucide="<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5"></i>
            <span><?php echo htmlspecialchars($mensaje); ?></span>
        </div>
    </div>
<?php endif; ?>

<!-- Tabs de contenido -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Tab headers -->
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button onclick="switchTab('socios')" id="tab-btn-socios"
                    class="tab-btn px-6 py-4 text-sm font-medium border-b-2 <?php echo $active_tab === 'socios' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                <i data-lucide="users" class="w-4 h-4 inline mr-2"></i>
                Socios (<?php echo $total_miembros; ?>)
            </button>
            <button onclick="switchTab('configuracion')" id="tab-btn-configuracion"
                    class="tab-btn px-6 py-4 text-sm font-medium border-b-2 <?php echo $active_tab === 'configuracion' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                <i data-lucide="settings" class="w-4 h-4 inline mr-2"></i>
                Configuración
            </button>
            <button onclick="switchTab('importar')" id="tab-btn-importar"
                    class="tab-btn px-6 py-4 text-sm font-medium border-b-2 <?php echo $active_tab === 'importar' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?>">
                <i data-lucide="upload" class="w-4 h-4 inline mr-2"></i>
                Importar
            </button>
        </nav>
    </div>

    <!-- Tab: Socios -->
    <div id="tab-socios" class="tab-content p-6 <?php echo $active_tab !== 'socios' ? 'hidden' : ''; ?>">
        <!-- Buscador -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" id="buscar-socio" placeholder="Buscar por DNI, nombre o número de asociado..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <i data-lucide="search" class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" id="filtro-con-credencial" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    Solo con credencial
                </label>
                <button onclick="buscarSocios()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    Buscar
                </button>
            </div>
        </div>

        <!-- Tabla de socios -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">DNI</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Asociado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-socios" class="divide-y divide-gray-200">
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <i data-lucide="search" class="w-8 h-8 mx-auto mb-2 text-gray-400"></i>
                            <p>Usa el buscador para encontrar socios</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Configuración -->
    <div id="tab-configuracion" class="tab-content p-6 <?php echo $active_tab !== 'configuracion' ? 'hidden' : ''; ?>">
        <form id="form-config" class="space-y-6" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Texto Superior (Banner)</label>
                    <input type="text" name="texto_superior" value="<?php echo htmlspecialchars($credencial_config['texto_superior'] ?? 'CREDENCIAL DE SOCIO'); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Ej: CREDENCIAL DE SOCIO">
                    <p class="text-xs text-gray-500 mt-1">Texto que aparece en el banner de la credencial</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Texto Inferior (Footer)</label>
                    <input type="text" name="texto_inferior" value="<?php echo htmlspecialchars($credencial_config['texto_inferior'] ?? ''); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Ej: Válido hasta 31/12/2026">
                    <p class="text-xs text-gray-500 mt-1">Texto opcional en la parte inferior</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="mostrar_foto" id="mostrar_foto" value="1"
                       <?php echo ($credencial_config['mostrar_foto'] ?? false) ? 'checked' : ''; ?>
                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                <label for="mostrar_foto" class="text-sm text-gray-700">Mostrar foto del socio en la credencial</label>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Template de Fondo (Opcional)</label>
                <div class="flex items-center gap-4">
                    <?php if (!empty($credencial_config['template_url'])): ?>
                        <img src="<?php echo htmlspecialchars($credencial_config['template_url']); ?>" alt="Template actual" class="h-20 rounded border">
                    <?php endif; ?>
                    <input type="file" name="template_fondo" accept="image/jpeg,image/png"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                </div>
                <p class="text-xs text-gray-500 mt-1">Imagen JPG/PNG de fondo para la credencial (500x300 px recomendado)</p>
            </div>

            <div class="pt-4 border-t">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Guardar Configuración
                </button>
            </div>
        </form>

        <!-- Vista previa -->
        <div class="mt-8 pt-8 border-t">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Vista Previa</h3>
            <p class="text-sm text-gray-600 mb-4">Así se verá la credencial con la configuración actual:</p>
            <div class="bg-gray-100 p-6 rounded-lg">
                <a href="../<?php echo $slug; ?>/credencialis/?preview=1" target="_blank"
                   class="inline-flex items-center gap-2 text-green-600 hover:text-green-800">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    Ver portal de credenciales
                </a>
            </div>
        </div>
    </div>

    <!-- Tab: Importar -->
    <div id="tab-importar" class="tab-content p-6 <?php echo $active_tab !== 'importar' ? 'hidden' : ''; ?>">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div>
                    <h4 class="font-medium text-blue-900">Formato de importación</h4>
                    <p class="text-sm text-blue-800 mt-1">
                        Pega datos en formato CSV con las columnas:<br>
                        <code class="bg-blue-100 px-1 rounded">DNI, Nombre, Apellido, Email, Teléfono, N° Asociado, Tipo, Categoría, Fecha Ingreso</code>
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Datos a importar</label>
                <textarea id="datos-importar" rows="10"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono text-sm"
                          placeholder="12345678, Juan, Pérez, juan@email.com, 1155667788, 001, TITULAR, SERVICIO BÁSICO, 2025-01-15
23456789, María, González, maria@email.com, 1144556677, 002, TITULAR, SERVICIO PREMIUM, 2025-01-20"></textarea>
            </div>

            <div id="preview-importar" class="hidden">
                <h4 class="font-medium text-gray-900 mb-2">Vista previa (<span id="preview-count">0</span> registros)</h4>
                <div class="max-h-64 overflow-auto border rounded-lg">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left">DNI</th>
                                <th class="px-3 py-2 text-left">Nombre</th>
                                <th class="px-3 py-2 text-left">N° Asociado</th>
                                <th class="px-3 py-2 text-left">Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="preview-tbody" class="divide-y"></tbody>
                    </table>
                </div>
            </div>

            <div class="flex gap-4">
                <button onclick="previsualizarImport()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition inline-flex items-center gap-2">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                    Previsualizar
                </button>
                <button onclick="ejecutarImport()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    Importar Socios
                </button>
            </div>

            <div id="resultado-importar" class="hidden mt-4 p-4 rounded-lg"></div>
        </div>
    </div>
</div>

<!-- Modal para editar credencial -->
<div id="modal-credencial" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900" id="modal-titulo">Asignar Credencial</h3>
                <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        <form id="form-credencial" class="p-6 space-y-4">
            <input type="hidden" name="id_miembro" id="cred-id-miembro">

            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="font-medium text-gray-900" id="cred-nombre-display"></p>
                <p class="text-sm text-gray-600" id="cred-dni-display"></p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">N° de Asociado *</label>
                    <div class="flex gap-2">
                        <input type="text" name="numero_asociado" id="cred-numero" required
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <button type="button" onclick="sugerirNumero()" class="px-3 py-2 bg-gray-100 rounded-lg hover:bg-gray-200" title="Sugerir siguiente número">
                            <i data-lucide="wand-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Asociado</label>
                    <select name="tipo_asociado" id="cred-tipo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Sin especificar</option>
                        <?php foreach ($tipos_asociado as $tipo): ?>
                            <option value="<?php echo $tipo; ?>"><?php echo $tipo; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría de Servicio</label>
                <select name="categoria_servicio" id="cred-categoria"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <option value="">Sin especificar</option>
                    <?php foreach ($categorias_servicio as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Entidad (si aplica)</label>
                <input type="text" name="nombre_entidad" id="cred-entidad"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="Solo para asociados institucionales">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Ingreso</label>
                <input type="date" name="fecha_ingreso" id="cred-fecha"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Guardar Credencial
                </button>
                <button type="button" onclick="cerrarModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Datos globales
let miembrosData = [];

// Cambiar tab
function switchTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('border-green-500', 'text-green-600');
        el.classList.add('border-transparent', 'text-gray-500');
    });

    document.getElementById('tab-' + tab).classList.remove('hidden');
    document.getElementById('tab-btn-' + tab).classList.add('border-green-500', 'text-green-600');
    document.getElementById('tab-btn-' + tab).classList.remove('border-transparent', 'text-gray-500');

    // Actualizar URL sin recargar
    history.replaceState(null, '', '?modulo=credencialis&tab=' + tab);
}

// Buscar socios
async function buscarSocios() {
    const buscar = document.getElementById('buscar-socio').value;
    const soloCredencial = document.getElementById('filtro-con-credencial').checked;

    const formData = new FormData();
    formData.append('accion', 'buscar_miembros_credencialis');
    formData.append('buscar', buscar);
    formData.append('solo_con_credencial', soloCredencial ? '1' : '0');

    try {
        const resp = await fetch('?modulo=credencialis', {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();

        if (data.success) {
            miembrosData = data.miembros;
            renderTabla(data.miembros);
        } else {
            alert('Error: ' + data.error);
        }
    } catch (e) {
        alert('Error de conexión');
    }
}

// Renderizar tabla de socios
function renderTabla(miembros) {
    const tbody = document.getElementById('tabla-socios');

    if (miembros.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-gray-400"></i>
                    <p>No se encontraron socios</p>
                </td>
            </tr>
        `;
        lucide.createIcons();
        return;
    }

    tbody.innerHTML = miembros.map(m => `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm font-mono">${m.dni}</td>
            <td class="px-4 py-3">
                <div class="font-medium text-gray-900">${m.nombre_completo || (m.nombre + ' ' + m.apellido)}</div>
                ${m.email ? `<div class="text-xs text-gray-500">${m.email}</div>` : ''}
            </td>
            <td class="px-4 py-3">
                ${m.numero_asociado ?
                    `<span class="font-semibold text-green-700">${m.numero_asociado}</span>` :
                    `<span class="text-gray-400">-</span>`
                }
            </td>
            <td class="px-4 py-3 text-sm">${m.tipo_asociado || '-'}</td>
            <td class="px-4 py-3 text-sm">${m.categoria_servicio || '-'}</td>
            <td class="px-4 py-3">
                ${m.tiene_credencial == 1 ?
                    `<span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                        <i data-lucide="badge-check" class="w-3 h-3"></i> Asignada
                    </span>` :
                    `<span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                        <i data-lucide="minus" class="w-3 h-3"></i> Pendiente
                    </span>`
                }
            </td>
            <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="editarCredencial(${m.id_miembro})"
                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar credencial">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </button>
                    ${m.tiene_credencial == 1 ? `
                        <a href="../${<?php echo json_encode($slug); ?>}/credencialis/creare.php?documentum=${m.dni}&institutio=${<?php echo json_encode($slug); ?>}"
                           target="_blank" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition" title="Ver credencial">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </a>
                        <button onclick="quitarCredencial(${m.id_miembro}, '${m.nombre_completo || m.nombre}')"
                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition" title="Quitar credencial">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');

    lucide.createIcons();
}

// Editar credencial
function editarCredencial(idMiembro) {
    const miembro = miembrosData.find(m => m.id_miembro == idMiembro);
    if (!miembro) return;

    document.getElementById('modal-titulo').textContent = miembro.numero_asociado ? 'Editar Credencial' : 'Asignar Credencial';
    document.getElementById('cred-id-miembro').value = idMiembro;
    document.getElementById('cred-nombre-display').textContent = miembro.nombre_completo || (miembro.nombre + ' ' + miembro.apellido);
    document.getElementById('cred-dni-display').textContent = 'DNI: ' + miembro.dni;

    document.getElementById('cred-numero').value = miembro.numero_asociado || '';
    document.getElementById('cred-tipo').value = miembro.tipo_asociado || '';
    document.getElementById('cred-categoria').value = miembro.categoria_servicio || '';
    document.getElementById('cred-entidad').value = miembro.nombre_entidad || '';
    document.getElementById('cred-fecha').value = miembro.fecha_ingreso || '';

    document.getElementById('modal-credencial').classList.remove('hidden');
    document.getElementById('modal-credencial').classList.add('flex');
    lucide.createIcons();
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('modal-credencial').classList.add('hidden');
    document.getElementById('modal-credencial').classList.remove('flex');
}

// Sugerir número de asociado
async function sugerirNumero() {
    const formData = new FormData();
    formData.append('accion', 'obtener_siguiente_numero');

    try {
        const resp = await fetch('?modulo=credencialis', { method: 'POST', body: formData });
        const data = await resp.json();
        if (data.success) {
            document.getElementById('cred-numero').value = String(data.siguiente).padStart(5, '0');
        }
    } catch (e) {}
}

// Guardar credencial
document.getElementById('form-credencial').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('accion', 'guardar_credencial');

    try {
        const resp = await fetch('?modulo=credencialis', { method: 'POST', body: formData });
        const data = await resp.json();

        if (data.success) {
            cerrarModal();
            buscarSocios();
        } else {
            alert('Error: ' + (data.error || data.mensaje));
        }
    } catch (e) {
        alert('Error de conexión');
    }
});

// Quitar credencial
async function quitarCredencial(idMiembro, nombre) {
    if (!confirm(`¿Seguro que deseas quitar la credencial de ${nombre}?`)) return;

    const formData = new FormData();
    formData.append('accion', 'quitar_credencial');
    formData.append('id_miembro', idMiembro);

    try {
        const resp = await fetch('?modulo=credencialis', { method: 'POST', body: formData });
        const data = await resp.json();

        if (data.success) {
            buscarSocios();
        } else {
            alert('Error: ' + (data.error || data.mensaje));
        }
    } catch (e) {
        alert('Error de conexión');
    }
}

// Guardar configuración
document.getElementById('form-config').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('accion', 'guardar_config_credencialis');

    try {
        const resp = await fetch('?modulo=credencialis', { method: 'POST', body: formData });
        const data = await resp.json();

        if (data.success) {
            alert('Configuración guardada correctamente');
        } else {
            alert('Error: ' + (data.error || data.mensaje));
        }
    } catch (e) {
        alert('Error de conexión');
    }
});

// Previsualizar importación
function previsualizarImport() {
    const texto = document.getElementById('datos-importar').value.trim();
    if (!texto) {
        alert('Ingresa datos para importar');
        return;
    }

    const lineas = texto.split('\n').filter(l => l.trim());
    const registros = lineas.map(linea => {
        const partes = linea.split(',').map(p => p.trim());
        return {
            dni: partes[0] || '',
            nombre: partes[1] || '',
            apellido: partes[2] || '',
            email: partes[3] || '',
            telefono: partes[4] || '',
            numero_asociado: partes[5] || '',
            tipo_asociado: partes[6] || '',
            categoria_servicio: partes[7] || '',
            fecha_ingreso: partes[8] || ''
        };
    });

    document.getElementById('preview-count').textContent = registros.length;
    document.getElementById('preview-tbody').innerHTML = registros.map(r => `
        <tr>
            <td class="px-3 py-2">${r.dni}</td>
            <td class="px-3 py-2">${r.nombre} ${r.apellido}</td>
            <td class="px-3 py-2">${r.numero_asociado || '-'}</td>
            <td class="px-3 py-2">${r.tipo_asociado || '-'}</td>
        </tr>
    `).join('');

    document.getElementById('preview-importar').classList.remove('hidden');
}

// Ejecutar importación
async function ejecutarImport() {
    const texto = document.getElementById('datos-importar').value.trim();
    if (!texto) {
        alert('Ingresa datos para importar');
        return;
    }

    const lineas = texto.split('\n').filter(l => l.trim());
    const registros = lineas.map(linea => {
        const partes = linea.split(',').map(p => p.trim());
        return {
            dni: partes[0] || '',
            nombre: partes[1] || '',
            apellido: partes[2] || '',
            email: partes[3] || '',
            telefono: partes[4] || '',
            numero_asociado: partes[5] || '',
            tipo_asociado: partes[6] || '',
            categoria_servicio: partes[7] || '',
            fecha_ingreso: partes[8] || ''
        };
    });

    if (!confirm(`¿Importar ${registros.length} registros?`)) return;

    const formData = new FormData();
    formData.append('accion', 'importar_miembros_credencialis');
    formData.append('datos', JSON.stringify(registros));

    try {
        const resp = await fetch('?modulo=credencialis', { method: 'POST', body: formData });
        const data = await resp.json();

        const resultDiv = document.getElementById('resultado-importar');
        resultDiv.classList.remove('hidden');

        if (data.success) {
            resultDiv.className = 'mt-4 p-4 rounded-lg bg-green-50 border border-green-200';
            resultDiv.innerHTML = `
                <div class="flex items-center gap-2 text-green-800">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span>Importación completada: ${data.insertados} insertados, ${data.actualizados} actualizados</span>
                </div>
                ${data.errores.length > 0 ? `
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        ${data.errores.map(e => `<li>${e}</li>`).join('')}
                    </ul>
                ` : ''}
            `;
        } else {
            resultDiv.className = 'mt-4 p-4 rounded-lg bg-red-50 border border-red-200';
            resultDiv.innerHTML = `
                <div class="flex items-center gap-2 text-red-800">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span>Error: ${data.error}</span>
                </div>
            `;
        }

        lucide.createIcons();
    } catch (e) {
        alert('Error de conexión');
    }
}

// Buscar al presionar Enter
document.getElementById('buscar-socio').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        buscarSocios();
    }
});

// Inicializar iconos
lucide.createIcons();

// Cargar socios al inicio si hay búsqueda previa
<?php if ($active_tab === 'socios'): ?>
buscarSocios();
<?php endif; ?>
</script>
