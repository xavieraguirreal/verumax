<?php
/**
 * MÓDULO: ACTIVIDAD
 * Centro unificado de actividad: emails, validaciones QR, logs
 *
 * @version 1.0.0
 * @date 2026-01-12
 */

require_once __DIR__ . '/../../env_loader.php';

use VERUMax\Services\DatabaseService;
use VERUMax\Services\InstitutionService;

$slug = $admin['slug'];
$pdoGeneral = DatabaseService::get('general');

$instance = InstitutionService::getConfig($slug);
if (!$instance) {
    die('Error: Instancia no encontrada');
}

$id_instancia = $instance['id_instancia'];
$colorPrimario = $instance['color_primario'] ?? '#3b82f6';

// =====================================================
// TABS
// =====================================================
$active_tab = $_GET['tab'] ?? 'dashboard';

// =====================================================
// MANEJO DE ACCIONES POST
// =====================================================

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'guardar_config_notificaciones':
            try {
                $notificar = isset($_POST['notificar_estadisticas']) ? 1 : 0;
                $email_notif = trim($_POST['notificar_email'] ?? '');
                $frecuencia = $_POST['notificar_frecuencia'] ?? 'nunca';
                $alertas_rebotes = isset($_POST['notificar_rebotes_alta']) ? 1 : 0;

                if ($notificar && !filter_var($email_notif, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email de notificación inválido');
                }

                $stmt = $pdoGeneral->prepare("SELECT id_config FROM email_config WHERE id_instancia = ?");
                $stmt->execute([$id_instancia]);
                $exists = $stmt->fetch();

                if ($exists) {
                    $stmt = $pdoGeneral->prepare("
                        UPDATE email_config SET
                            notificar_estadisticas = ?,
                            notificar_email = ?,
                            notificar_frecuencia = ?,
                            notificar_rebotes_alta = ?
                        WHERE id_instancia = ?
                    ");
                    $stmt->execute([$notificar, $email_notif, $frecuencia, $alertas_rebotes, $id_instancia]);
                } else {
                    $stmt = $pdoGeneral->prepare("
                        INSERT INTO email_config
                        (id_instancia, notificar_estadisticas, notificar_email, notificar_frecuencia, notificar_rebotes_alta)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$id_instancia, $notificar, $email_notif, $frecuencia, $alertas_rebotes]);
                }

                $mensaje = 'Configuración guardada correctamente';
                $tipo_mensaje = 'success';

            } catch (Exception $e) {
                $mensaje = 'Error: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;
    }
}

// =====================================================
// OBTENER DATOS
// =====================================================

// Período para estadísticas
$periodo = $_GET['periodo'] ?? '30';

// Paginación y búsqueda
$items_por_pagina = 25;
$pagina_emails = max(1, intval($_GET['pag_emails'] ?? 1));
$pagina_valid = max(1, intval($_GET['pag_valid'] ?? 1));
$pagina_accesos = max(1, intval($_GET['pag_accesos'] ?? 1));
$buscar = trim($_GET['buscar'] ?? '');
$filtro_estado = $_GET['estado'] ?? '';
$filtro_tipo = $_GET['tipo'] ?? '';

// --- ESTADÍSTICAS DE EMAILS ---
$stats_emails = ['total_enviados' => 0, 'entregados' => 0, 'abiertos' => 0, 'rebotes' => 0];
try {
    $stmt = $pdoGeneral->prepare("
        SELECT
            COUNT(*) as total_enviados,
            SUM(CASE WHEN estado IN ('enviado', 'abierto', 'click') THEN 1 ELSE 0 END) as entregados,
            SUM(CASE WHEN estado IN ('abierto', 'click') THEN 1 ELSE 0 END) as abiertos,
            SUM(CASE WHEN estado = 'rebotado' THEN 1 ELSE 0 END) as rebotes
        FROM email_logs
        WHERE id_instancia = ?
        AND enviado_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    ");
    $stmt->execute([$id_instancia, $periodo]);
    $stats_emails = $stmt->fetch(PDO::FETCH_ASSOC) ?: $stats_emails;
} catch (Exception $e) {
    // Tabla puede no existir
}

// Emails con paginación, búsqueda y filtros
$ultimosEmails = [];
$total_emails_filtrados = 0;
try {
    // Construir WHERE dinámico
    $where_emails = "WHERE id_instancia = ?";
    $params_emails = [$id_instancia];

    if ($buscar !== '' && $active_tab === 'comunicaciones') {
        $where_emails .= " AND (email_destino LIKE ? OR nombre_destino LIKE ? OR asunto LIKE ?)";
        $buscar_like = "%{$buscar}%";
        $params_emails[] = $buscar_like;
        $params_emails[] = $buscar_like;
        $params_emails[] = $buscar_like;
    }

    if ($filtro_estado !== '' && $active_tab === 'comunicaciones') {
        $where_emails .= " AND estado = ?";
        $params_emails[] = $filtro_estado;
    }

    if ($filtro_tipo !== '' && $active_tab === 'comunicaciones') {
        $where_emails .= " AND tipo_email = ?";
        $params_emails[] = $filtro_tipo;
    }

    // Contar total para paginación
    $stmt = $pdoGeneral->prepare("SELECT COUNT(*) FROM email_logs {$where_emails}");
    $stmt->execute($params_emails);
    $total_emails_filtrados = $stmt->fetchColumn();

    // Obtener emails paginados
    $offset_emails = ($pagina_emails - 1) * $items_por_pagina;
    $stmt = $pdoGeneral->prepare("
        SELECT id_log, tipo_email, email_destino, nombre_destino, asunto, estado, enviado_at
        FROM email_logs
        {$where_emails}
        ORDER BY enviado_at DESC
        LIMIT {$items_por_pagina} OFFSET {$offset_emails}
    ");
    $stmt->execute($params_emails);
    $ultimosEmails = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_paginas_emails = ceil($total_emails_filtrados / $items_por_pagina);
} catch (Exception $e) {
    $total_paginas_emails = 1;
}

// --- ESTADÍSTICAS DE VALIDACIONES QR ---
// La tabla log_validaciones está en la base de datos 'certificatum', no 'general'
$stats_validaciones = ['total_consultas' => 0, 'exitosas' => 0, 'fallidas' => 0, 'visitantes_unicos' => 0, 'codigos_distintos' => 0];
$logs_validaciones = [];
$total_validaciones_filtradas = 0;
$total_paginas_valid = 1;
try {
    $pdoCert = DatabaseService::get('certificatum');

    // Stats usando CertificateService (misma lógica que certificatum.php)
    $stats_validaciones = \VERUMax\Services\CertificateService::getValidationStats($slug, $periodo);

    // Construir WHERE dinámico para validaciones
    $where_valid = "WHERE institucion = ?";
    $params_valid = [$slug];

    if ($buscar !== '' && $active_tab === 'validaciones') {
        $where_valid .= " AND codigo_validacion LIKE ?";
        $params_valid[] = "%{$buscar}%";
    }

    if ($filtro_estado !== '' && $active_tab === 'validaciones') {
        $where_valid .= " AND exitoso = ?";
        $params_valid[] = ($filtro_estado === 'exitoso') ? 1 : 0;
    }

    // Contar total para paginación
    $stmt = $pdoCert->prepare("SELECT COUNT(*) FROM log_validaciones {$where_valid}");
    $stmt->execute($params_valid);
    $total_validaciones_filtradas = $stmt->fetchColumn();
    $total_paginas_valid = max(1, ceil($total_validaciones_filtradas / $items_por_pagina));

    // Obtener validaciones paginadas
    $offset_valid = ($pagina_valid - 1) * $items_por_pagina;
    $stmt = $pdoCert->prepare("
        SELECT id_log, codigo_validacion, exitoso, tipo_documento, ip_address, user_agent, referer, fecha_consulta
        FROM log_validaciones
        {$where_valid}
        ORDER BY fecha_consulta DESC
        LIMIT {$items_por_pagina} OFFSET {$offset_valid}
    ");
    $stmt->execute($params_valid);
    $logs_validaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Si la tabla no existe o hay error, dejamos los valores por defecto
    error_log("Actividad - Log validaciones: " . $e->getMessage());
}

// --- LOGS DE ACCESOS A CERTIFICADOS (Vistas y Descargas) ---
$logs_accesos = [];
$stats_accesos = ['total_accesos' => 0, 'vistas' => 0, 'descargas' => 0, 'usuarios_unicos' => 0, 'estudiantes' => 0, 'docentes' => 0];
$total_accesos_filtrados = 0;
$total_paginas_accesos = 1;
try {
    // Stats desde el servicio
    $stats_accesos = \VERUMax\Services\CertificateService::getAccesosStats($slug, $periodo);

    // Query de accesos con paginación y búsqueda
    $where_accesos = "WHERE institucion = ? AND fecha_acceso >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params_accesos = [$slug, $periodo];

    if ($buscar !== '' && $active_tab === 'accesos') {
        $where_accesos .= " AND (nombre_persona LIKE ? OR dni LIKE ? OR nombre_curso LIKE ?)";
        $buscar_like = "%{$buscar}%";
        $params_accesos[] = $buscar_like;
        $params_accesos[] = $buscar_like;
        $params_accesos[] = $buscar_like;
    }

    if ($filtro_tipo !== '' && $active_tab === 'accesos') {
        $where_accesos .= " AND tipo_accion = ?";
        $params_accesos[] = $filtro_tipo;
    }

    // Contar total para paginación
    $stmt = $pdoCert->prepare("SELECT COUNT(*) FROM log_accesos_certificados {$where_accesos}");
    $stmt->execute($params_accesos);
    $total_accesos_filtrados = $stmt->fetchColumn();
    $total_paginas_accesos = max(1, ceil($total_accesos_filtrados / $items_por_pagina));

    // Obtener accesos paginados
    $offset_accesos = ($pagina_accesos - 1) * $items_por_pagina;
    $stmt = $pdoCert->prepare("
        SELECT fecha_acceso, dni, nombre_persona as nombre_completo, tipo_accion as tipo_acceso,
               tipo_documento, codigo_curso, nombre_curso, tipo_usuario, idioma, ip_address, dispositivo
        FROM log_accesos_certificados
        {$where_accesos}
        ORDER BY fecha_acceso DESC
        LIMIT {$items_por_pagina} OFFSET {$offset_accesos}
    ");
    $stmt->execute($params_accesos);
    $logs_accesos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Actividad - Log accesos: " . $e->getMessage());
}

// --- CONFIGURACIÓN DE NOTIFICACIONES ---
$config_notif = [
    'notificar_estadisticas' => 0,
    'notificar_email' => '',
    'notificar_frecuencia' => 'nunca',
    'notificar_rebotes_alta' => 0
];
try {
    $stmt = $pdoGeneral->prepare("
        SELECT notificar_estadisticas, notificar_email, notificar_frecuencia, notificar_rebotes_alta
        FROM email_config WHERE id_instancia = ?
    ");
    $stmt->execute([$id_instancia]);
    $config_notif = $stmt->fetch(PDO::FETCH_ASSOC) ?: $config_notif;
} catch (Exception $e) {}

// Calcular tasas
$total_emails = $stats_emails['total_enviados'] ?: 1;
$tasaApertura = round(($stats_emails['abiertos'] / $total_emails) * 100, 1);
$total_valid = $stats_validaciones['total_consultas'] ?? 0;
$tasaExito = $total_valid > 0 ? round(($stats_validaciones['exitosas'] / $total_valid) * 100, 1) : 0;
?>

<!-- Toast de mensajes -->
<?php if ($mensaje): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof showToast === 'function') {
        showToast('<?php echo addslashes($mensaje); ?>', '<?php echo $tipo_mensaje; ?>');
    }
});
</script>
<?php endif; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                <i data-lucide="activity" class="w-7 h-7" style="color: <?php echo $colorPrimario; ?>"></i>
                Centro de Actividad
            </h1>
            <p class="text-gray-500 text-sm mt-1">Emails, validaciones QR y logs del sistema</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Período:</span>
            <select onchange="window.location.href='?modulo=actividad&tab=<?php echo $active_tab; ?>&periodo='+this.value"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="7" <?php echo $periodo == '7' ? 'selected' : ''; ?>>Últimos 7 días</option>
                <option value="30" <?php echo $periodo == '30' ? 'selected' : ''; ?>>Últimos 30 días</option>
                <option value="90" <?php echo $periodo == '90' ? 'selected' : ''; ?>>Últimos 90 días</option>
            </select>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex gap-4 overflow-x-auto">
            <a href="?modulo=actividad&tab=dashboard&periodo=<?php echo $periodo; ?>"
               class="px-4 py-3 text-sm font-medium border-b-2 <?php echo $active_tab === 'dashboard' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> transition whitespace-nowrap flex items-center gap-2">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
            </a>
            <a href="?modulo=actividad&tab=comunicaciones&periodo=<?php echo $periodo; ?>"
               class="px-4 py-3 text-sm font-medium border-b-2 <?php echo $active_tab === 'comunicaciones' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> transition whitespace-nowrap flex items-center gap-2">
                <i data-lucide="mail" class="w-4 h-4"></i> Comunicaciones
                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs"><?php echo $stats_emails['total_enviados']; ?></span>
            </a>
            <a href="?modulo=actividad&tab=validaciones&periodo=<?php echo $periodo; ?>"
               class="px-4 py-3 text-sm font-medium border-b-2 <?php echo $active_tab === 'validaciones' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> transition whitespace-nowrap flex items-center gap-2">
                <i data-lucide="qr-code" class="w-4 h-4"></i> Validaciones QR
                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs"><?php echo $stats_validaciones['total_consultas'] ?? 0; ?></span>
            </a>
            <a href="?modulo=actividad&tab=accesos&periodo=<?php echo $periodo; ?>"
               class="px-4 py-3 text-sm font-medium border-b-2 <?php echo $active_tab === 'accesos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> transition whitespace-nowrap flex items-center gap-2">
                <i data-lucide="eye" class="w-4 h-4"></i> Accesos
                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs"><?php echo $stats_accesos['total_accesos'] ?? 0; ?></span>
            </a>
            <a href="?modulo=actividad&tab=configuracion&periodo=<?php echo $periodo; ?>"
               class="px-4 py-3 text-sm font-medium border-b-2 <?php echo $active_tab === 'configuracion' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'; ?> transition whitespace-nowrap flex items-center gap-2">
                <i data-lucide="settings" class="w-4 h-4"></i> Configuración
            </a>
        </nav>
    </div>

    <!-- ================================================================ -->
    <!-- TAB: DASHBOARD -->
    <!-- ================================================================ -->
    <?php if ($active_tab === 'dashboard'): ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Emails Enviados -->
        <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i data-lucide="send" class="w-5 h-5 text-blue-600"></i>
                </div>
                <span class="text-sm text-gray-500">Emails Enviados</span>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats_emails['total_enviados']); ?></p>
            <p class="text-xs text-gray-400 mt-1">Últimos <?php echo $periodo; ?> días</p>
        </div>

        <!-- Tasa de Apertura -->
        <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i data-lucide="mail-open" class="w-5 h-5 text-green-600"></i>
                </div>
                <span class="text-sm text-gray-500">Tasa Apertura</span>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?php echo $tasaApertura; ?>%</p>
            <p class="text-xs text-gray-400 mt-1"><?php echo $stats_emails['abiertos']; ?> abiertos</p>
        </div>

        <!-- Validaciones QR -->
        <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i data-lucide="qr-code" class="w-5 h-5 text-purple-600"></i>
                </div>
                <span class="text-sm text-gray-500">Validaciones QR</span>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats_validaciones['total_consultas'] ?? 0); ?></p>
            <p class="text-xs text-gray-400 mt-1"><?php echo $tasaExito; ?>% exitosas</p>
        </div>

        <!-- Rebotes -->
        <div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                </div>
                <span class="text-sm text-gray-500">Rebotes</span>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($stats_emails['rebotes']); ?></p>
            <p class="text-xs text-gray-400 mt-1">Emails no entregados</p>
        </div>
    </div>

    <!-- Resumen rápido -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Últimos Emails -->
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i data-lucide="mail" class="w-4 h-4 text-blue-500"></i>
                    Últimos Emails
                </h3>
                <a href="?modulo=actividad&tab=comunicaciones" class="text-sm text-blue-600 hover:underline">Ver todos</a>
            </div>
            <div class="divide-y max-h-64 overflow-y-auto">
                <?php if (empty($ultimosEmails)): ?>
                    <div class="p-4 text-center text-gray-500 text-sm">Sin emails registrados</div>
                <?php else: ?>
                    <?php foreach (array_slice($ultimosEmails, 0, 5) as $email): ?>
                    <div class="p-3 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($email['email_destino']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($email['asunto'] ?? $email['tipo_email']); ?></p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full <?php echo $email['estado'] === 'enviado' ? 'bg-green-100 text-green-700' : ($email['estado'] === 'error' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'); ?>">
                                <?php echo ucfirst($email['estado']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Últimas Validaciones -->
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i data-lucide="qr-code" class="w-4 h-4 text-purple-500"></i>
                    Últimas Validaciones QR
                </h3>
                <a href="?modulo=actividad&tab=validaciones" class="text-sm text-blue-600 hover:underline">Ver todas</a>
            </div>
            <div class="divide-y max-h-64 overflow-y-auto">
                <?php if (empty($logs_validaciones)): ?>
                    <div class="p-4 text-center text-gray-500 text-sm">Sin validaciones registradas</div>
                <?php else: ?>
                    <?php foreach (array_slice($logs_validaciones, 0, 5) as $log): ?>
                    <div class="p-3 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($log['codigo_validacion']); ?></code>
                                <p class="text-xs text-gray-500 mt-1"><?php echo date('d/m/Y H:i', strtotime($log['fecha_consulta'])); ?></p>
                            </div>
                            <?php if ($log['exitoso']): ?>
                                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Válido
                                </span>
                            <?php else: ?>
                                <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 flex items-center gap-1">
                                    <i data-lucide="x-circle" class="w-3 h-3"></i> No encontrado
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- TAB: COMUNICACIONES (EMAILS) -->
    <!-- ================================================================ -->
    <?php if ($active_tab === 'comunicaciones'): ?>
    <!-- Barra de búsqueda y filtros -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="modulo" value="actividad">
            <input type="hidden" name="tab" value="comunicaciones">
            <input type="hidden" name="periodo" value="<?php echo $periodo; ?>">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Buscar</label>
                <input type="text" name="buscar" value="<?php echo htmlspecialchars($buscar); ?>"
                       placeholder="Email, nombre o asunto..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="w-32">
                <label class="block text-xs text-gray-500 mb-1">Estado</label>
                <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="enviado" <?php echo $filtro_estado === 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                    <option value="abierto" <?php echo $filtro_estado === 'abierto' ? 'selected' : ''; ?>>Abierto</option>
                    <option value="rebotado" <?php echo $filtro_estado === 'rebotado' ? 'selected' : ''; ?>>Rebotado</option>
                    <option value="error" <?php echo $filtro_estado === 'error' ? 'selected' : ''; ?>>Error</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                <i data-lucide="search" class="w-4 h-4 inline"></i> Buscar
            </button>
            <?php if ($buscar !== '' || $filtro_estado !== ''): ?>
            <a href="?modulo=actividad&tab=comunicaciones&periodo=<?php echo $periodo; ?>" class="text-gray-500 hover:text-gray-700 text-sm">
                Limpiar filtros
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i data-lucide="mail" class="w-5 h-5 text-blue-500"></i>
                Emails Enviados
                <span class="text-sm font-normal text-gray-500">(<?php echo $total_emails_filtrados; ?> registros)</span>
            </h3>
            <button onclick="exportarEmailsCSV()" class="bg-gray-600 text-white px-3 py-1.5 rounded-lg hover:bg-gray-700 text-sm flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i> Exportar
            </button>
        </div>

        <?php if (empty($ultimosEmails)): ?>
            <div class="p-12 text-center">
                <i data-lucide="inbox" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Sin emails registrados</h3>
                <p class="text-gray-500">Los emails enviados aparecerán aquí.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="tabla-emails">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Destinatario</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Tipo</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Asunto</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" id="tbody-emails">
                        <?php foreach ($ultimosEmails as $email): ?>
                        <tr class="hover:bg-gray-50 email-row">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-gray-900"><?php echo date('d/m/Y', strtotime($email['enviado_at'])); ?></div>
                                <div class="text-xs text-gray-500"><?php echo date('H:i:s', strtotime($email['enviado_at'])); ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($email['nombre_destino'] ?? '-'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($email['email_destino']); ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs"><?php echo htmlspecialchars($email['tipo_email']); ?></span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 max-w-xs truncate"><?php echo htmlspecialchars($email['asunto'] ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                $estado_class = match($email['estado']) {
                                    'enviado' => 'bg-green-100 text-green-700',
                                    'abierto' => 'bg-blue-100 text-blue-700',
                                    'click' => 'bg-purple-100 text-purple-700',
                                    'rebotado' => 'bg-red-100 text-red-700',
                                    'error' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-600'
                                };
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs <?php echo $estado_class; ?>">
                                    <?php echo ucfirst($email['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación Emails -->
            <?php if ($total_paginas_emails > 1): ?>
            <div class="px-4 py-3 bg-gray-50 border-t flex justify-between items-center">
                <span class="text-sm text-gray-600">
                    Mostrando <?php echo (($pagina_emails - 1) * $items_por_pagina) + 1; ?>-<?php echo min($pagina_emails * $items_por_pagina, $total_emails_filtrados); ?> de <?php echo $total_emails_filtrados; ?>
                </span>
                <div class="flex gap-1">
                    <?php if ($pagina_emails > 1): ?>
                    <a href="?modulo=actividad&tab=comunicaciones&periodo=<?php echo $periodo; ?>&pag_emails=1&buscar=<?php echo urlencode($buscar); ?>&estado=<?php echo $filtro_estado; ?>"
                       class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Primera</a>
                    <a href="?modulo=actividad&tab=comunicaciones&periodo=<?php echo $periodo; ?>&pag_emails=<?php echo $pagina_emails - 1; ?>&buscar=<?php echo urlencode($buscar); ?>&estado=<?php echo $filtro_estado; ?>"
                       class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Anterior</a>
                    <?php endif; ?>
                    <span class="px-3 py-1 bg-blue-600 text-white rounded text-sm"><?php echo $pagina_emails; ?></span>
                    <?php if ($pagina_emails < $total_paginas_emails): ?>
                    <a href="?modulo=actividad&tab=comunicaciones&periodo=<?php echo $periodo; ?>&pag_emails=<?php echo $pagina_emails + 1; ?>&buscar=<?php echo urlencode($buscar); ?>&estado=<?php echo $filtro_estado; ?>"
                       class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Siguiente</a>
                    <a href="?modulo=actividad&tab=comunicaciones&periodo=<?php echo $periodo; ?>&pag_emails=<?php echo $total_paginas_emails; ?>&buscar=<?php echo urlencode($buscar); ?>&estado=<?php echo $filtro_estado; ?>"
                       class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Última</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- TAB: VALIDACIONES QR -->
    <!-- ================================================================ -->
    <?php if ($active_tab === 'validaciones'): ?>
    <!-- Barra de búsqueda y filtros -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="modulo" value="actividad">
            <input type="hidden" name="tab" value="validaciones">
            <input type="hidden" name="periodo" value="<?php echo $periodo; ?>">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Buscar código</label>
                <input type="text" name="buscar" value="<?php echo htmlspecialchars($buscar); ?>"
                       placeholder="Código de validación..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="w-32">
                <label class="block text-xs text-gray-500 mb-1">Estado</label>
                <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="exitoso" <?php echo $filtro_estado === 'exitoso' ? 'selected' : ''; ?>>Exitoso</option>
                    <option value="fallido" <?php echo $filtro_estado === 'fallido' ? 'selected' : ''; ?>>No encontrado</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                <i data-lucide="search" class="w-4 h-4 inline"></i> Buscar
            </button>
            <?php if ($buscar !== '' || $filtro_estado !== ''): ?>
            <a href="?modulo=actividad&tab=validaciones&periodo=<?php echo $periodo; ?>" class="text-gray-500 hover:text-gray-700 text-sm">
                Limpiar filtros
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <i data-lucide="qr-code" class="w-5 h-5 text-purple-500"></i>
                Validaciones de Códigos QR
                <span class="text-sm font-normal text-gray-500">(<?php echo $total_validaciones_filtradas; ?> registros)</span>
            </h3>
            <button onclick="exportarValidacionesCSV()" class="bg-gray-600 text-white px-3 py-1.5 rounded-lg hover:bg-gray-700 text-sm flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i> Exportar
            </button>
        </div>

        <?php if (empty($logs_validaciones)): ?>
            <div class="p-12 text-center">
                <i data-lucide="qr-code" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Sin validaciones registradas</h3>
                <p class="text-gray-500">Los escaneos de códigos QR aparecerán aquí.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="tabla-validaciones">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha/Hora</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Código</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700">Estado</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Tipo Doc.</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">IP</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Dispositivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" id="tbody-validaciones">
                        <?php foreach ($logs_validaciones as $log): ?>
                        <tr class="hover:bg-gray-50 validacion-row">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-gray-900"><?php echo date('d/m/Y', strtotime($log['fecha_consulta'])); ?></div>
                                <div class="text-xs text-gray-500"><?php echo date('H:i:s', strtotime($log['fecha_consulta'])); ?></div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono"><?php echo htmlspecialchars($log['codigo_validacion']); ?></code>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($log['exitoso']): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Válido
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i> No encontrado
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($log['tipo_documento'] ?? '-'); ?></td>
                            <td class="px-4 py-3 text-gray-600 font-mono text-xs"><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></td>
                            <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate" title="<?php echo htmlspecialchars($log['user_agent'] ?? ''); ?>">
                                <?php
                                $ua = $log['user_agent'] ?? '';
                                if (stripos($ua, 'mobile') !== false || stripos($ua, 'android') !== false || stripos($ua, 'iphone') !== false) {
                                    echo '<i data-lucide="smartphone" class="w-4 h-4 inline text-gray-400"></i> Móvil';
                                } else {
                                    echo '<i data-lucide="monitor" class="w-4 h-4 inline text-gray-400"></i> Desktop';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación Validaciones -->
            <?php if ($total_paginas_valid > 1): ?>
            <div class="px-4 py-3 bg-gray-50 border-t flex justify-between items-center">
                <span class="text-sm text-gray-600">
                    Mostrando <?php echo (($pagina_valid - 1) * $items_por_pagina) + 1; ?>-<?php echo min($pagina_valid * $items_por_pagina, $total_validaciones_filtradas); ?> de <?php echo $total_validaciones_filtradas; ?>
                </span>
                <div class="flex gap-1">
                    <?php if ($pagina_valid > 1): ?>
                    <a href="?modulo=actividad&tab=validaciones&periodo=<?php echo $periodo; ?>&pag_valid=1&buscar=<?php echo urlencode($buscar); ?>&estado=<?php echo $filtro_estado; ?>"
                       class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Primera</a>
                    <a href="?modulo=actividad&tab=validaciones&periodo=<?php echo $periodo; ?>&pag_valid=<?php echo $pagina_valid - 1; ?>&buscar=<?php echo urlencode($buscar); ?>&estado=<?php echo $filtro_estado; ?>"
                       class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Anterior</a>
                    <?php endif; ?>
                    <span class="px-3 py-1 bg-blue-600 text-white rounded text-sm"><?php echo $pagina_valid; ?></span>
                    <?php if ($pagina_valid < $total_paginas_valid): ?>
                    <a href="?modulo=actividad&tab=validaciones&periodo=<?php echo $periodo; ?>&pag_valid=<?php echo $pagina_valid + 1; ?>&buscar=<?php echo urlencode($buscar); ?>&estado=<?php echo $filtro_estado; ?>"
                       class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Siguiente</a>
                    <a href="?modulo=actividad&tab=validaciones&periodo=<?php echo $periodo; ?>&pag_valid=<?php echo $total_paginas_valid; ?>&buscar=<?php echo urlencode($buscar); ?>&estado=<?php echo $filtro_estado; ?>"
                       class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Última</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- TAB: ACCESOS A CERTIFICADOS -->
    <!-- ================================================================ -->
    <?php if ($active_tab === 'accesos'): ?>
    <!-- Barra de búsqueda y filtros -->
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="modulo" value="actividad">
            <input type="hidden" name="tab" value="accesos">
            <input type="hidden" name="periodo" value="<?php echo $periodo; ?>">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Buscar</label>
                <input type="text" name="buscar" value="<?php echo htmlspecialchars($buscar); ?>"
                       placeholder="Nombre, DNI o curso..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="w-32">
                <label class="block text-xs text-gray-500 mb-1">Tipo</label>
                <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="vista" <?php echo $filtro_tipo === 'vista' ? 'selected' : ''; ?>>Vista</option>
                    <option value="descarga" <?php echo $filtro_tipo === 'descarga' ? 'selected' : ''; ?>>Descarga</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                <i data-lucide="search" class="w-4 h-4 inline"></i> Buscar
            </button>
            <?php if ($buscar !== '' || $filtro_tipo !== ''): ?>
            <a href="?modulo=actividad&tab=accesos&periodo=<?php echo $periodo; ?>" class="text-gray-500 hover:text-gray-700 text-sm">
                Limpiar filtros
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="space-y-6">
        <!-- Estadísticas de Accesos -->
        <?php if (!empty($stats_accesos) && ($stats_accesos['total_accesos'] ?? 0) > 0): ?>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl p-4 border border-indigo-200">
                <div class="text-2xl font-bold text-indigo-700"><?php echo number_format($stats_accesos['total_accesos'] ?? 0); ?></div>
                <div class="text-sm text-indigo-600">Total (<?php echo $periodo; ?> días)</div>
            </div>
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl p-4 border border-emerald-200">
                <div class="text-2xl font-bold text-emerald-700"><?php echo number_format($stats_accesos['vistas'] ?? 0); ?></div>
                <div class="text-sm text-emerald-600">Vistas Pantalla</div>
            </div>
            <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-xl p-4 border border-cyan-200">
                <div class="text-2xl font-bold text-cyan-700"><?php echo number_format($stats_accesos['descargas'] ?? 0); ?></div>
                <div class="text-sm text-cyan-600">Descargas PDF</div>
            </div>
            <div class="bg-gradient-to-br from-violet-50 to-violet-100 rounded-xl p-4 border border-violet-200">
                <div class="text-2xl font-bold text-violet-700"><?php echo number_format($stats_accesos['usuarios_unicos'] ?? 0); ?></div>
                <div class="text-sm text-violet-600">Usuarios Únicos</div>
            </div>
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="text-2xl font-bold text-blue-700"><?php echo number_format($stats_accesos['estudiantes'] ?? 0); ?></div>
                <div class="text-sm text-blue-600">Estudiantes</div>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200">
                <div class="text-2xl font-bold text-amber-700"><?php echo number_format($stats_accesos['docentes'] ?? 0); ?></div>
                <div class="text-sm text-amber-600">Docentes</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla de Accesos -->
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-4 border-b flex justify-between items-center">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i data-lucide="eye" class="w-5 h-5 text-indigo-500"></i>
                    Accesos a Certificados
                    <span class="text-sm font-normal text-gray-500">(<?php echo $total_accesos_filtrados; ?> registros)</span>
                </h3>
                <button onclick="exportarAccesosCSV()" class="bg-gray-600 text-white px-3 py-1.5 rounded-lg hover:bg-gray-700 text-sm flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i> Exportar
                </button>
            </div>

            <?php if (empty($logs_accesos)): ?>
                <div class="p-12 text-center">
                    <i data-lucide="eye-off" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">Sin accesos registrados</h3>
                    <p class="text-gray-500">Los accesos a certificados aparecerán aquí.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="tabla-accesos">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha/Hora</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Persona</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-700">Acción</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tipo Doc.</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Curso</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Idioma</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" id="tbody-accesos">
                            <?php foreach ($logs_accesos as $acceso): ?>
                            <tr class="hover:bg-gray-50 acceso-row">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-gray-900"><?php echo date('d/m/Y', strtotime($acceso['fecha_acceso'])); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('H:i:s', strtotime($acceso['fecha_acceso'])); ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($acceso['nombre_completo'] ?? '-'); ?></div>
                                    <div class="text-xs text-gray-500">DNI: <?php echo htmlspecialchars($acceso['dni'] ?? '-'); ?></div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if (($acceso['tipo_acceso'] ?? '') === 'descarga'): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-cyan-100 text-cyan-800 rounded-full text-xs font-medium">
                                            <i data-lucide="download" class="w-3 h-3"></i> PDF
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-medium">
                                            <i data-lucide="eye" class="w-3 h-3"></i> Vista
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($acceso['tipo_documento'] ?? '-'); ?></td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate"><?php echo htmlspecialchars($acceso['nombre_curso'] ?? '-'); ?></td>
                                <td class="px-4 py-3">
                                    <?php
                                    $lang = $acceso['idioma'] ?? 'es_AR';
                                    $flag = match(substr($lang, 0, 2)) {
                                        'pt' => '🇧🇷',
                                        'en' => '🇺🇸',
                                        default => '🇦🇷'
                                    };
                                    ?>
                                    <span class="text-xs"><?php echo $flag; ?> <?php echo $lang; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación Accesos -->
                <?php if ($total_paginas_accesos > 1): ?>
                <div class="px-4 py-3 bg-gray-50 border-t flex justify-between items-center">
                    <span class="text-sm text-gray-600">
                        Mostrando <?php echo (($pagina_accesos - 1) * $items_por_pagina) + 1; ?>-<?php echo min($pagina_accesos * $items_por_pagina, $total_accesos_filtrados); ?> de <?php echo $total_accesos_filtrados; ?>
                    </span>
                    <div class="flex gap-1">
                        <?php if ($pagina_accesos > 1): ?>
                        <a href="?modulo=actividad&tab=accesos&periodo=<?php echo $periodo; ?>&pag_accesos=1&buscar=<?php echo urlencode($buscar); ?>&tipo=<?php echo $filtro_tipo; ?>"
                           class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Primera</a>
                        <a href="?modulo=actividad&tab=accesos&periodo=<?php echo $periodo; ?>&pag_accesos=<?php echo $pagina_accesos - 1; ?>&buscar=<?php echo urlencode($buscar); ?>&tipo=<?php echo $filtro_tipo; ?>"
                           class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Anterior</a>
                        <?php endif; ?>
                        <span class="px-3 py-1 bg-blue-600 text-white rounded text-sm"><?php echo $pagina_accesos; ?></span>
                        <?php if ($pagina_accesos < $total_paginas_accesos): ?>
                        <a href="?modulo=actividad&tab=accesos&periodo=<?php echo $periodo; ?>&pag_accesos=<?php echo $pagina_accesos + 1; ?>&buscar=<?php echo urlencode($buscar); ?>&tipo=<?php echo $filtro_tipo; ?>"
                           class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Siguiente</a>
                        <a href="?modulo=actividad&tab=accesos&periodo=<?php echo $periodo; ?>&pag_accesos=<?php echo $total_paginas_accesos; ?>&buscar=<?php echo urlencode($buscar); ?>&tipo=<?php echo $filtro_tipo; ?>"
                           class="px-3 py-1 border rounded text-sm hover:bg-gray-100">Última</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- TAB: CONFIGURACIÓN -->
    <!-- ================================================================ -->
    <?php if ($active_tab === 'configuracion'): ?>
    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-4 border-b">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i data-lucide="bell" class="w-5 h-5 text-amber-500"></i>
                    Configuración de Notificaciones
                </h3>
            </div>
            <form method="POST" class="p-6 space-y-6">
                <input type="hidden" name="accion" value="guardar_config_notificaciones">

                <div class="flex items-start gap-3">
                    <input type="checkbox" name="notificar_estadisticas" id="notificar_estadisticas"
                           <?php echo $config_notif['notificar_estadisticas'] ? 'checked' : ''; ?>
                           class="mt-1 w-4 h-4 text-blue-600 rounded">
                    <div>
                        <label for="notificar_estadisticas" class="font-medium text-gray-800">Recibir resumen de estadísticas</label>
                        <p class="text-sm text-gray-500">Recibí un email con el resumen de actividad periódicamente.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email de notificación</label>
                    <input type="email" name="notificar_email" value="<?php echo htmlspecialchars($config_notif['notificar_email']); ?>"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="admin@ejemplo.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frecuencia</label>
                    <select name="notificar_frecuencia" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="nunca" <?php echo $config_notif['notificar_frecuencia'] === 'nunca' ? 'selected' : ''; ?>>Nunca</option>
                        <option value="diario" <?php echo $config_notif['notificar_frecuencia'] === 'diario' ? 'selected' : ''; ?>>Diario</option>
                        <option value="semanal" <?php echo $config_notif['notificar_frecuencia'] === 'semanal' ? 'selected' : ''; ?>>Semanal</option>
                        <option value="mensual" <?php echo $config_notif['notificar_frecuencia'] === 'mensual' ? 'selected' : ''; ?>>Mensual</option>
                    </select>
                </div>

                <div class="flex items-start gap-3">
                    <input type="checkbox" name="notificar_rebotes_alta" id="notificar_rebotes_alta"
                           <?php echo $config_notif['notificar_rebotes_alta'] ? 'checked' : ''; ?>
                           class="mt-1 w-4 h-4 text-blue-600 rounded">
                    <div>
                        <label for="notificar_rebotes_alta" class="font-medium text-gray-800">Alertas de rebotes altos</label>
                        <p class="text-sm text-gray-500">Recibí una alerta si la tasa de rebotes supera el 5%.</p>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-medium">
                        <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                        Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ============================================================================ -->
<!-- PANEL DE AYUDA LATERAL -->
<!-- ============================================================================ -->

<!-- Botón flotante de ayuda -->
<button id="btn-ayuda-flotante" onclick="togglePanelAyuda()"
        class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center z-40 transition-all hover:scale-110 group"
        title="Centro de Ayuda (F1)">
    <i data-lucide="help-circle" class="w-7 h-7"></i>
    <span class="absolute -top-1 -right-1 bg-gray-800 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm opacity-80 group-hover:opacity-100">F1</span>
</button>

<!-- Panel lateral de ayuda -->
<div id="panel-ayuda" class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
    <!-- Header del panel -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 flex-shrink-0">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold flex items-center gap-2">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                Centro de Ayuda
            </h2>
            <button onclick="togglePanelAyuda()" class="p-1 hover:bg-blue-500 rounded transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <!-- Búsqueda -->
        <div class="mt-3 relative">
            <input type="text" id="busqueda-ayuda" placeholder="Buscar en la ayuda..."
                   onkeyup="filtrarAyuda(this.value)"
                   class="w-full px-4 py-2 rounded-lg text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
            <i data-lucide="search" class="w-4 h-4 absolute right-3 top-2.5 text-gray-400"></i>
        </div>
    </div>

    <!-- Indicador de contexto -->
    <div id="ayuda-contexto" class="px-4 py-2 bg-blue-50 border-b text-sm text-blue-700 flex items-center gap-2 flex-shrink-0">
        <i data-lucide="info" class="w-4 h-4"></i>
        <span id="ayuda-contexto-texto">Ayuda general</span>
    </div>

    <!-- Contenido de ayuda (scrolleable) -->
    <div id="ayuda-contenido" class="flex-1 overflow-y-auto p-4">
        <!-- Contenido dinámico según tab activo -->
    </div>

    <!-- Recursos Globales - Siempre visibles -->
    <div class="p-3 bg-gradient-to-r from-slate-50 to-gray-100 border-t flex-shrink-0">
        <p class="text-xs text-gray-500 mb-2 font-medium flex items-center gap-1">
            <i data-lucide="library" class="w-3 h-3"></i> Recursos de ayuda
        </p>
        <div class="grid grid-cols-3 gap-2">
            <button onclick="mostrarAyudaSeccion('faq-actividad')" class="flex flex-col items-center gap-1 p-2 bg-white rounded-lg border hover:bg-amber-50 hover:border-amber-300 transition group">
                <i data-lucide="help-circle" class="w-4 h-4 text-amber-500 group-hover:text-amber-600"></i>
                <span class="text-xs text-gray-600 group-hover:text-amber-700">FAQ</span>
            </button>
            <button onclick="mostrarAyudaSeccion('glosario-actividad')" class="flex flex-col items-center gap-1 p-2 bg-white rounded-lg border hover:bg-blue-50 hover:border-blue-300 transition group">
                <i data-lucide="book-open" class="w-4 h-4 text-blue-500 group-hover:text-blue-600"></i>
                <span class="text-xs text-gray-600 group-hover:text-blue-700">Glosario</span>
            </button>
            <button onclick="mostrarAyudaSeccion('errores-actividad')" class="flex flex-col items-center gap-1 p-2 bg-white rounded-lg border hover:bg-red-50 hover:border-red-300 transition group">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 group-hover:text-red-600"></i>
                <span class="text-xs text-gray-600 group-hover:text-red-700">Errores</span>
            </button>
        </div>
    </div>
    <div class="px-4 py-2 bg-gray-50 border-t flex-shrink-0">
        <a href="mailto:soporte@verumax.com" class="text-xs text-blue-600 hover:underline flex items-center justify-center gap-1">
            <i data-lucide="headphones" class="w-3 h-3"></i> Contactar soporte
        </a>
    </div>
</div>

<!-- Overlay para cerrar panel -->
<div id="overlay-ayuda" onclick="togglePanelAyuda()"
     class="fixed inset-0 bg-black bg-opacity-30 z-40 hidden"></div>

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

<script>
// Inicializar iconos
if (typeof lucide !== 'undefined') lucide.createIcons();

// Toast
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.style.opacity = '0', 3000);
    setTimeout(() => toast.remove(), 3500);
}

// Exportar Emails
function exportarEmailsCSV() {
    const filas = document.querySelectorAll('#tbody-emails tr.email-row');
    if (filas.length === 0) {
        showToast('No hay datos para exportar', 'error');
        return;
    }

    const cabeceras = ['Fecha', 'Hora', 'Destinatario', 'Email', 'Tipo', 'Asunto', 'Estado'];
    const datos = Array.from(filas).map(fila => {
        const celdas = fila.querySelectorAll('td');
        return [
            celdas[0]?.querySelector('div')?.textContent?.trim() || '',
            celdas[0]?.querySelector('.text-xs')?.textContent?.trim() || '',
            celdas[1]?.querySelector('.font-medium')?.textContent?.trim() || '',
            celdas[1]?.querySelector('.text-xs')?.textContent?.trim() || '',
            celdas[2]?.textContent?.trim() || '',
            celdas[3]?.textContent?.trim() || '',
            celdas[4]?.textContent?.trim() || ''
        ].map(c => `"${c.replace(/"/g, '""')}"`).join(',');
    });

    const csv = '\uFEFF' + cabeceras.join(',') + '\n' + datos.join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `emails_${new Date().toISOString().slice(0,10)}.csv`;
    link.click();
    showToast(`${filas.length} emails exportados`, 'success');
}

// Exportar Validaciones
function exportarValidacionesCSV() {
    const filas = document.querySelectorAll('#tbody-validaciones tr.validacion-row');
    if (filas.length === 0) {
        showToast('No hay datos para exportar', 'error');
        return;
    }

    const cabeceras = ['Fecha', 'Hora', 'Código', 'Estado', 'Tipo Documento', 'IP'];
    const datos = Array.from(filas).map(fila => {
        const celdas = fila.querySelectorAll('td');
        return [
            celdas[0]?.querySelector('div')?.textContent?.trim() || '',
            celdas[0]?.querySelector('.text-xs')?.textContent?.trim() || '',
            celdas[1]?.querySelector('code')?.textContent?.trim() || '',
            celdas[2]?.textContent?.trim()?.replace(/\s+/g, ' ') || '',
            celdas[3]?.textContent?.trim() || '',
            celdas[4]?.textContent?.trim() || ''
        ].map(c => `"${c.replace(/"/g, '""')}"`).join(',');
    });

    const csv = '\uFEFF' + cabeceras.join(',') + '\n' + datos.join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `validaciones_qr_${new Date().toISOString().slice(0,10)}.csv`;
    link.click();
    showToast(`${filas.length} validaciones exportadas`, 'success');
}

// Exportar Accesos a Certificados
function exportarAccesosCSV() {
    const filas = document.querySelectorAll('#tbody-accesos tr.acceso-row');
    if (filas.length === 0) {
        showToast('No hay datos para exportar', 'error');
        return;
    }

    const cabeceras = ['Fecha', 'Hora', 'Nombre', 'DNI', 'Acción', 'Tipo Documento', 'Curso', 'Idioma'];
    const datos = Array.from(filas).map(fila => {
        const celdas = fila.querySelectorAll('td');
        return [
            celdas[0]?.querySelector('div')?.textContent?.trim() || '',
            celdas[0]?.querySelector('.text-xs')?.textContent?.trim() || '',
            celdas[1]?.querySelector('.font-medium')?.textContent?.trim() || '',
            celdas[1]?.querySelector('.text-xs')?.textContent?.replace('DNI:', '')?.trim() || '',
            celdas[2]?.textContent?.trim()?.replace(/\s+/g, ' ') || '',
            celdas[3]?.textContent?.trim() || '',
            celdas[4]?.textContent?.trim() || '',
            celdas[5]?.textContent?.trim() || ''
        ].map(c => `"${c.replace(/"/g, '""')}"`).join(',');
    });

    const csv = '\uFEFF' + cabeceras.join(',') + '\n' + datos.join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `accesos_certificados_${new Date().toISOString().slice(0,10)}.csv`;
    link.click();
    showToast(`${filas.length} accesos exportados`, 'success');
}

// ============================================================================
// PANEL DE AYUDA
// ============================================================================

const contenidoAyuda = {
    'dashboard': {
        titulo: 'Dashboard de Actividad',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="dashboard resumen actividad métricas estadísticas">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="activity" class="w-4 h-4"></i> ¿Qué es el Dashboard?
                    </h3>
                    <p class="text-sm text-blue-700">Vista general de toda la actividad de tu institución: emails enviados, validaciones QR y accesos a certificados.</p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="mail" class="w-4 h-4"></i> Emails Enviados
                    </h3>
                    <p class="text-sm text-green-700">Conteo de notificaciones enviadas por el sistema (inscripciones, certificados disponibles, evaluaciones).</p>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="qr-code" class="w-4 h-4"></i> Validaciones QR
                    </h3>
                    <p class="text-sm text-purple-700">Cada vez que alguien escanea un código QR de certificado para verificar su autenticidad.</p>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="calendar" class="w-4 h-4"></i> Período
                    </h3>
                    <p class="text-sm text-amber-700">Usá el selector de período para ver estadísticas de los últimos 7, 30 o 90 días.</p>
                </div>
                <button onclick="abrirTutorial('entender-dashboard')" class="mt-3 w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                    Ver paso a paso
                </button>
            </div>
        `
    },
    'comunicaciones': {
        titulo: 'Comunicaciones (Emails)',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="emails comunicaciones sendgrid notificaciones enviados rebotes">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="mail" class="w-4 h-4"></i> Registro de Emails
                    </h3>
                    <p class="text-sm text-blue-700">Historial completo de todos los emails enviados por la plataforma (vía SendGrid).</p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Estados de Email
                    </h3>
                    <ul class="text-sm text-green-700 space-y-1 mt-2">
                        <li><strong>Enviado:</strong> Email enviado correctamente a SendGrid</li>
                        <li><strong>Entregado:</strong> SendGrid confirmó la entrega</li>
                        <li><strong>Abierto:</strong> El destinatario abrió el email</li>
                        <li><strong>Rebotado:</strong> No se pudo entregar</li>
                    </ul>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="tag" class="w-4 h-4"></i> Tipos de Email
                    </h3>
                    <ul class="text-sm text-purple-700 space-y-1 mt-2">
                        <li><strong>Inscripción:</strong> Bienvenida al curso</li>
                        <li><strong>Certificado:</strong> Aviso de certificado disponible</li>
                        <li><strong>Evaluación:</strong> Invitación a rendir examen</li>
                        <li><strong>Docente:</strong> Notificaciones a formadores</li>
                    </ul>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                        <i data-lucide="download" class="w-4 h-4"></i> Exportar
                    </h3>
                    <p class="text-sm text-gray-700">Descargá el historial de emails en formato CSV para análisis externo.</p>
                </div>
                <button onclick="abrirTutorial('ver-comunicaciones')" class="mt-3 w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                    Ver paso a paso
                </button>
            </div>
        `
    },
    'validaciones': {
        titulo: 'Validaciones QR',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="validaciones qr códigos escáner verificación autenticidad">
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="qr-code" class="w-4 h-4"></i> ¿Qué son las Validaciones?
                    </h3>
                    <p class="text-sm text-purple-700">Registro de cada vez que alguien escanea un código QR de un certificado para verificar su autenticidad.</p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="shield-check" class="w-4 h-4"></i> Información Registrada
                    </h3>
                    <ul class="text-sm text-green-700 space-y-1 mt-2">
                        <li><strong>Código:</strong> Identificador único del certificado</li>
                        <li><strong>Fecha/Hora:</strong> Momento exacto del escaneo</li>
                        <li><strong>IP:</strong> Dirección IP del dispositivo</li>
                        <li><strong>Resultado:</strong> Válido o Inválido</li>
                    </ul>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="eye" class="w-4 h-4"></i> Accesos a Certificados
                    </h3>
                    <p class="text-sm text-blue-700">También se registra cuando alguien visualiza o descarga un certificado desde la plataforma.</p>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="lightbulb" class="w-4 h-4"></i> Tip de Seguridad
                    </h3>
                    <p class="text-sm text-amber-700">Si ves muchas validaciones fallidas desde la misma IP, podría ser un intento de fraude.</p>
                </div>
                <button onclick="abrirTutorial('ver-validaciones')" class="mt-3 w-full px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                    Ver paso a paso
                </button>
            </div>
        `
    },
    'configuracion': {
        titulo: 'Configuración de Notificaciones',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="configuración notificaciones alertas email frecuencia">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="bell" class="w-4 h-4"></i> Notificaciones por Email
                    </h3>
                    <p class="text-sm text-blue-700">Recibí reportes periódicos con estadísticas de actividad de tu institución.</p>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="clock" class="w-4 h-4"></i> Frecuencia
                    </h3>
                    <ul class="text-sm text-green-700 space-y-1 mt-2">
                        <li><strong>Diario:</strong> Resumen cada mañana</li>
                        <li><strong>Semanal:</strong> Resumen los lunes</li>
                        <li><strong>Mensual:</strong> Resumen el día 1</li>
                        <li><strong>Nunca:</strong> Sin notificaciones automáticas</li>
                    </ul>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i> Alertas de Rebotes
                    </h3>
                    <p class="text-sm text-amber-700">Activá alertas cuando la tasa de rebotes de emails sea alta (más del 5%).</p>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="mail" class="w-4 h-4"></i> Email de Destino
                    </h3>
                    <p class="text-sm text-purple-700">Configurá el email donde querés recibir los reportes y alertas del sistema.</p>
                </div>
                <button onclick="abrirTutorial('configurar-notificaciones')" class="mt-3 w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                    Ver paso a paso
                </button>
            </div>
        `
    },
    'general': {
        titulo: 'Bienvenido a Actividad',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="actividad centro emails validaciones logs">
                <div class="bg-gradient-to-br from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="activity" class="w-4 h-4"></i> Centro de Actividad
                    </h3>
                    <p class="text-sm text-gray-700">Monitoreo centralizado de todas las comunicaciones y validaciones de tu institución.</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Secciones disponibles:</h3>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-center gap-2"><i data-lucide="layout-dashboard" class="w-4 h-4 text-blue-400"></i> <strong>Dashboard:</strong> Resumen general</li>
                        <li class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-green-400"></i> <strong>Comunicaciones:</strong> Historial de emails</li>
                        <li class="flex items-center gap-2"><i data-lucide="qr-code" class="w-4 h-4 text-purple-400"></i> <strong>Validaciones:</strong> Escaneos de QR</li>
                        <li class="flex items-center gap-2"><i data-lucide="settings" class="w-4 h-4 text-gray-400"></i> <strong>Configuración:</strong> Alertas y reportes</li>
                    </ul>
                </div>

                <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-300 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="book-open" class="w-4 h-4"></i> Recursos de Ayuda
                    </h3>
                    <div class="space-y-2 mt-3">
                        <button onclick="mostrarAyudaSeccion('guia-interpretar-metricas')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                            <i data-lucide="bar-chart-2" class="w-4 h-4 text-blue-600"></i>
                            Interpretar métricas
                        </button>
                        <button onclick="mostrarAyudaSeccion('guia-solucionar-emails')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                            <i data-lucide="mail-x" class="w-4 h-4 text-red-600"></i>
                            Solucionar problemas de emails
                        </button>
                        <button onclick="mostrarAyudaSeccion('faq-actividad')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                            <i data-lucide="help-circle" class="w-4 h-4 text-amber-600"></i>
                            Preguntas frecuentes
                        </button>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="lightbulb" class="w-4 h-4"></i> Tip
                    </h3>
                    <p class="text-sm text-amber-700">Usá los filtros de fecha y el selector de período para analizar tendencias en el tiempo.</p>
                </div>
            </div>
        `
    },
    'guia-interpretar-metricas': {
        titulo: 'Interpretar Métricas',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="métricas estadísticas números tendencias analizar">
                <div class="bg-gradient-to-r from-blue-100 to-indigo-100 border border-blue-300 rounded-lg p-4">
                    <h3 class="font-bold text-blue-800 text-lg mb-1">Guía de Métricas</h3>
                    <p class="text-sm text-blue-700">Entendé qué significa cada número del dashboard.</p>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="font-bold text-green-800 mb-3 flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4"></i> Emails Enviados
                    </h4>
                    <div class="space-y-2">
                        <div class="p-2 bg-gray-50 rounded">
                            <p class="text-xs text-gray-600"><strong>¿Qué mide?</strong> Cantidad de notificaciones enviadas por SendGrid.</p>
                        </div>
                        <div class="p-2 bg-green-50 rounded">
                            <p class="text-xs text-green-700"><strong>Valor ideal:</strong> Que coincida con las acciones realizadas (inscripciones, certificados emitidos).</p>
                        </div>
                        <div class="p-2 bg-red-50 rounded">
                            <p class="text-xs text-red-700"><strong>Alerta si:</strong> Es 0 cuando deberían haberse enviado emails (problema de configuración).</p>
                        </div>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="font-bold text-blue-800 mb-3 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> Tasa de Apertura
                    </h4>
                    <div class="space-y-2">
                        <div class="p-2 bg-gray-50 rounded">
                            <p class="text-xs text-gray-600"><strong>¿Qué mide?</strong> Porcentaje de emails abiertos vs enviados.</p>
                        </div>
                        <div class="p-2 bg-green-50 rounded">
                            <p class="text-xs text-green-700"><strong>Valor ideal:</strong> Mayor al 50%. Las notificaciones de certificados suelen tener alta apertura.</p>
                        </div>
                        <div class="p-2 bg-amber-50 rounded">
                            <p class="text-xs text-amber-700"><strong>Si es bajo:</strong> Revisá si los emails caen en spam o si los asuntos son claros.</p>
                        </div>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="font-bold text-purple-800 mb-3 flex items-center gap-2">
                        <i data-lucide="qr-code" class="w-4 h-4"></i> Validaciones QR
                    </h4>
                    <div class="space-y-2">
                        <div class="p-2 bg-gray-50 rounded">
                            <p class="text-xs text-gray-600"><strong>¿Qué mide?</strong> Cantidad de veces que se escanearon QR de certificados.</p>
                        </div>
                        <div class="p-2 bg-green-50 rounded">
                            <p class="text-xs text-green-700"><strong>Buena señal:</strong> Validaciones consistentes indican que los certificados están siendo usados y verificados.</p>
                        </div>
                        <div class="p-2 bg-red-50 rounded">
                            <p class="text-xs text-red-700"><strong>Alerta si:</strong> Muchas validaciones fallidas desde la misma IP (posible intento de fraude).</p>
                        </div>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="font-bold text-red-800 mb-3 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i> Rebotes
                    </h4>
                    <div class="space-y-2">
                        <div class="p-2 bg-gray-50 rounded">
                            <p class="text-xs text-gray-600"><strong>¿Qué mide?</strong> Emails que no pudieron ser entregados.</p>
                        </div>
                        <div class="p-2 bg-green-50 rounded">
                            <p class="text-xs text-green-700"><strong>Valor ideal:</strong> Menor al 2%. Algunos rebotes son normales.</p>
                        </div>
                        <div class="p-2 bg-red-50 rounded">
                            <p class="text-xs text-red-700"><strong>Alerta si:</strong> Mayor al 5%. Revisá la calidad de los emails cargados.</p>
                        </div>
                    </div>
                </div>
            </div>
        `
    },
    'guia-solucionar-emails': {
        titulo: 'Solucionar Problemas de Emails',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="emails problemas rebotes no llegan solucionar sendgrid">
                <div class="bg-gradient-to-r from-red-100 to-orange-100 border border-red-300 rounded-lg p-4">
                    <h3 class="font-bold text-red-800 text-lg mb-1">Troubleshooting de Emails</h3>
                    <p class="text-sm text-red-700">Guía para resolver los problemas más comunes con el envío de emails.</p>
                </div>

                <!-- Problema 1 -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm">El email aparece como "Enviado" pero el destinatario no lo recibió</p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-xs text-gray-600"><strong>Posibles causas:</strong></p>
                        <ul class="text-xs text-gray-600 ml-4 list-disc space-y-1">
                            <li>El email cayó en la carpeta de spam/correo no deseado</li>
                            <li>El filtro corporativo del destinatario lo bloqueó</li>
                            <li>Hubo un retraso en la entrega (puede tardar hasta 15 minutos)</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> Pedile al destinatario que revise spam. Si usa email corporativo, que pida al área de IT que agregue verumax.com a la whitelist.</p>
                        </div>
                    </div>
                </div>

                <!-- Problema 2 -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm">El email aparece como "Rebotado"</p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-xs text-gray-600"><strong>Tipos de rebote:</strong></p>
                        <ul class="text-xs text-gray-600 ml-4 list-disc space-y-1">
                            <li><strong>Hard bounce:</strong> La dirección no existe o es inválida</li>
                            <li><strong>Soft bounce:</strong> Buzón lleno o servidor temporalmente no disponible</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> Verificá que la dirección esté bien escrita. Si es hard bounce, pedí al estudiante un email alternativo.</p>
                        </div>
                    </div>
                </div>

                <!-- Problema 3 -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm">No se envía ningún email (todos quedan en 0)</p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-xs text-gray-600"><strong>Posibles causas:</strong></p>
                        <ul class="text-xs text-gray-600 ml-4 list-disc space-y-1">
                            <li>SendGrid no está configurado correctamente</li>
                            <li>La API key de SendGrid expiró o fue desactivada</li>
                            <li>Se alcanzó el límite de emails del plan</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> Contactá al soporte técnico de VERUMax para verificar la configuración de SendGrid.</p>
                        </div>
                    </div>
                </div>

                <!-- Problema 4 -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm">Alta tasa de rebotes (mayor a 5%)</p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-xs text-gray-600"><strong>Esto puede indicar:</strong></p>
                        <ul class="text-xs text-gray-600 ml-4 list-disc space-y-1">
                            <li>Los emails se cargaron con errores de tipeo</li>
                            <li>Los datos son antiguos y muchas cuentas ya no existen</li>
                            <li>Se están usando emails temporales o descartables</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> Revisá y limpiá tu base de datos de estudiantes. Verificá los emails antes de importar masivamente.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-3">
                    <h4 class="font-semibold text-blue-800 flex items-center gap-2 mb-1 text-sm">
                        <i data-lucide="lightbulb" class="w-4 h-4"></i> Tip preventivo
                    </h4>
                    <p class="text-xs text-blue-700">Antes de enviar emails masivos, hacé una prueba con tu propio email para verificar que todo funcione correctamente.</p>
                </div>
            </div>
        `
    },
    'faq-actividad': {
        titulo: 'Preguntas Frecuentes - Actividad',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="faq preguntas frecuentes actividad emails validaciones">
                <div class="bg-gradient-to-r from-amber-100 to-yellow-100 border border-amber-300 rounded-lg p-4">
                    <h3 class="font-bold text-amber-800 text-lg mb-1">FAQ del módulo Actividad</h3>
                    <p class="text-sm text-amber-700">Respuestas rápidas a las dudas más comunes.</p>
                </div>

                <div class="divide-y border rounded-lg overflow-hidden">
                    <div class="p-3 hover:bg-gray-50">
                        <p class="font-medium text-gray-800 text-sm mb-1">¿Por qué la tasa de apertura es menor al 100%?</p>
                        <p class="text-xs text-gray-600">No todos los destinatarios abren los emails. Además, algunos clientes de email bloquean el tracking de apertura por privacidad. Una tasa del 50-70% es normal.</p>
                    </div>
                    <div class="p-3 hover:bg-gray-50">
                        <p class="font-medium text-gray-800 text-sm mb-1">¿Puedo reenviar un email que rebotó?</p>
                        <p class="text-xs text-gray-600">Sí, pero primero corregí la dirección de email del estudiante. Luego podés volver a enviar la notificación desde Certificatum → Inscripciones.</p>
                    </div>
                    <div class="p-3 hover:bg-gray-50">
                        <p class="font-medium text-gray-800 text-sm mb-1">¿Qué significa cuando hay muchas validaciones del mismo certificado?</p>
                        <p class="text-xs text-gray-600">Puede ser normal (el certificado fue verificado en varias ocasiones). Pero si son cientos desde la misma IP, podría ser un intento de ataque.</p>
                    </div>
                    <div class="p-3 hover:bg-gray-50">
                        <p class="font-medium text-gray-800 text-sm mb-1">¿Cada cuánto se actualizan las estadísticas?</p>
                        <p class="text-xs text-gray-600">Las estadísticas se actualizan en tiempo real. Los webhooks de SendGrid notifican inmediatamente cada evento (envío, apertura, rebote).</p>
                    </div>
                    <div class="p-3 hover:bg-gray-50">
                        <p class="font-medium text-gray-800 text-sm mb-1">¿Se pueden exportar los datos de actividad?</p>
                        <p class="text-xs text-gray-600">Sí, en cada sección (Comunicaciones, Validaciones) hay un botón "Exportar CSV" que descarga todos los registros filtrados.</p>
                    </div>
                    <div class="p-3 hover:bg-gray-50">
                        <p class="font-medium text-gray-800 text-sm mb-1">¿Cuánto tiempo se guardan los logs de actividad?</p>
                        <p class="text-xs text-gray-600">Los registros se mantienen por 12 meses. Después se archivan pero pueden solicitarse al soporte si se necesitan.</p>
                    </div>
                    <div class="p-3 hover:bg-gray-50">
                        <p class="font-medium text-gray-800 text-sm mb-1">¿Qué pasa si un código QR es inválido?</p>
                        <p class="text-xs text-gray-600">Se muestra un mensaje de "Certificado no encontrado". Esto puede pasar si el código fue alterado o si el certificado fue eliminado.</p>
                    </div>
                </div>
            </div>
        `
    },
    'glosario-actividad': {
        titulo: 'Glosario - Actividad',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="glosario términos actividad definiciones emails validaciones">
                <div class="bg-gradient-to-r from-blue-100 to-indigo-100 border border-blue-300 rounded-lg p-4">
                    <h3 class="font-bold text-blue-800 text-lg mb-1">Glosario del módulo Actividad</h3>
                    <p class="text-sm text-blue-700">Términos específicos de comunicaciones y validaciones.</p>
                </div>

                <div class="space-y-3">
                    <div class="border rounded-lg p-3">
                        <p class="font-semibold text-green-700 text-sm">Estados de Email</p>
                        <div class="text-xs text-gray-600 mt-1 space-y-1">
                            <p>• <strong>Enviado:</strong> El email fue enviado a SendGrid correctamente</p>
                            <p>• <strong>Entregado:</strong> SendGrid confirmó que llegó al servidor destino</p>
                            <p>• <strong>Abierto:</strong> El destinatario abrió el email</p>
                            <p>• <strong>Click:</strong> El destinatario hizo clic en un enlace</p>
                            <p>• <strong>Rebotado:</strong> El email no pudo ser entregado</p>
                            <p>• <strong>Spam:</strong> El destinatario marcó como spam</p>
                        </div>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="font-semibold text-purple-700 text-sm">Tipos de Rebote</p>
                        <div class="text-xs text-gray-600 mt-1 space-y-1">
                            <p>• <strong>Hard Bounce:</strong> Fallo permanente - la dirección no existe</p>
                            <p>• <strong>Soft Bounce:</strong> Fallo temporal - buzón lleno o servidor caído</p>
                            <p>• <strong>Blocked:</strong> El servidor rechazó el mensaje</p>
                        </div>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="font-semibold text-amber-700 text-sm">Métricas de Email</p>
                        <div class="text-xs text-gray-600 mt-1 space-y-1">
                            <p>• <strong>Tasa de Apertura:</strong> % de emails abiertos vs enviados</p>
                            <p>• <strong>Tasa de Rebote:</strong> % de emails rechazados vs enviados</p>
                            <p>• <strong>CTR (Click-Through Rate):</strong> % de clics vs abiertos</p>
                        </div>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="font-semibold text-blue-700 text-sm">Validaciones QR</p>
                        <div class="text-xs text-gray-600 mt-1 space-y-1">
                            <p>• <strong>Validación:</strong> Escaneo de código QR para verificar certificado</p>
                            <p>• <strong>IP:</strong> Dirección de internet del dispositivo que escaneó</p>
                            <p>• <strong>User Agent:</strong> Información del navegador/app usado</p>
                            <p>• <strong>Código:</strong> Identificador único del certificado (VALID-XXXXXXXX)</p>
                        </div>
                    </div>
                    <div class="border rounded-lg p-3">
                        <p class="font-semibold text-red-700 text-sm">Términos SendGrid</p>
                        <div class="text-xs text-gray-600 mt-1 space-y-1">
                            <p>• <strong>SendGrid:</strong> Servicio externo de envío de emails</p>
                            <p>• <strong>Webhook:</strong> Notificación automática de eventos</p>
                            <p>• <strong>API Key:</strong> Credencial para enviar emails</p>
                            <p>• <strong>Suppression List:</strong> Lista de emails bloqueados</p>
                        </div>
                    </div>
                </div>
            </div>
        `
    },
    'errores-actividad': {
        titulo: 'Errores Comunes - Actividad',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="errores problemas soluciones actividad emails sendgrid">
                <div class="bg-gradient-to-r from-red-100 to-orange-100 border border-red-300 rounded-lg p-4">
                    <h3 class="font-bold text-red-800 text-lg mb-1">Solución de Problemas</h3>
                    <p class="text-sm text-red-700">Errores comunes del módulo Actividad y cómo resolverlos.</p>
                </div>

                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            El dashboard muestra 0 emails enviados
                        </p>
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-gray-600 mb-2">No hay registro de emails en el período seleccionado.</p>
                        <div class="bg-green-50 border border-green-200 rounded p-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> 1) Verificá el filtro de período (7/30/90 días), 2) Comprobá que SendGrid esté configurado, 3) Contactá soporte si el problema persiste.</p>
                        </div>
                    </div>
                </div>

                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Alta tasa de rebotes (mayor al 5%)
                        </p>
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-gray-600 mb-2">Muchos emails no están siendo entregados.</p>
                        <div class="bg-green-50 border border-green-200 rounded p-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> 1) Revisá la calidad de los emails en tu base de datos, 2) Limpiá direcciones con errores de tipeo, 3) Eliminá emails temporales o descartables.</p>
                        </div>
                    </div>
                </div>

                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Emails enviados pero no aparecen como "Abiertos"
                        </p>
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-gray-600 mb-2">La tasa de apertura es muy baja o cero.</p>
                        <div class="bg-green-50 border border-green-200 rounded p-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> Esto es normal - algunos clientes de email bloquean el tracking por privacidad. Gmail, Outlook y Apple Mail tienen protecciones que impiden detectar aperturas.</p>
                        </div>
                    </div>
                </div>

                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Muchas validaciones fallidas desde la misma IP
                        </p>
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-gray-600 mb-2">Posible intento de ataque o fraude.</p>
                        <div class="bg-green-50 border border-green-200 rounded p-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> 1) Anotá la IP sospechosa, 2) Verificá si los códigos son reales o inventados, 3) Contactá soporte si detectás un patrón de ataque.</p>
                        </div>
                    </div>
                </div>

                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            No recibo las notificaciones de actividad
                        </p>
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-gray-600 mb-2">Los reportes automáticos no llegan.</p>
                        <div class="bg-green-50 border border-green-200 rounded p-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> 1) Verificá la configuración en Actividad → Configuración, 2) Comprobá que tu email esté bien escrito, 3) Revisá la carpeta de spam.</p>
                        </div>
                    </div>
                </div>

                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 text-sm flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            El CSV exportado está vacío
                        </p>
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-gray-600 mb-2">La exportación no contiene datos.</p>
                        <div class="bg-green-50 border border-green-200 rounded p-2">
                            <p class="text-xs text-green-700"><strong>Solución:</strong> Verificá que el filtro de búsqueda tenga resultados antes de exportar. Si la tabla está vacía, el CSV también lo estará.</p>
                        </div>
                    </div>
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
        lucide.createIcons();
    } else {
        panel.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        btnFlotante.classList.remove('hidden');
    }
}

// Atajo de teclado F1 para abrir ayuda
document.addEventListener('keydown', function(e) {
    if (e.key === 'F1') {
        e.preventDefault(); // Evitar ayuda del navegador
        togglePanelAyuda();
    }
    // Escape para cerrar
    if (e.key === 'Escape' && panelAyudaAbierto) {
        togglePanelAyuda();
    }
});

function actualizarAyudaContextual() {
    // Detectar tab activo desde URL
    const urlParams = new URLSearchParams(window.location.search);
    let contexto = urlParams.get('tab') || 'dashboard';

    // Si el tab no existe en contenido, usar general
    if (!contenidoAyuda[contexto]) {
        contexto = 'general';
    }

    // Actualizar indicador de contexto
    const contextoTexto = document.getElementById('ayuda-contexto-texto');
    contextoTexto.textContent = contenidoAyuda[contexto].titulo;

    // Actualizar contenido
    const contenedor = document.getElementById('ayuda-contenido');
    contenedor.innerHTML = contenidoAyuda[contexto].contenido;

    // Recrear iconos
    lucide.createIcons();
}

function filtrarAyuda(termino) {
    const secciones = document.querySelectorAll('.ayuda-seccion');
    const terminoLower = termino.toLowerCase();

    if (!termino) {
        secciones.forEach(s => s.classList.remove('hidden'));
        return;
    }

    secciones.forEach(seccion => {
        const keywords = seccion.dataset.keywords || '';
        const texto = seccion.textContent.toLowerCase();
        const match = keywords.toLowerCase().includes(terminoLower) || texto.includes(terminoLower);
        seccion.classList.toggle('hidden', !match);
    });
}

function mostrarAyudaSeccion(seccion) {
    if (!contenidoAyuda[seccion]) return;
    document.getElementById('busqueda-ayuda').value = '';
    document.getElementById('ayuda-contexto-texto').textContent = contenidoAyuda[seccion].titulo;
    document.getElementById('ayuda-contenido').innerHTML = contenidoAyuda[seccion].contenido;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ============================================================================
// SISTEMA DE TUTORIALES PASO A PASO
// ============================================================================

const tutorialesActividad = {
    'ver-comunicaciones': {
        titulo: 'Cómo ver comunicaciones',
        pasos: [
            {
                titulo: 'Paso 1: Ir a Comunicaciones',
                icono: 'mail',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Accedé a la sección de emails enviados:</p>
                    <div class="flex gap-2 mb-4 flex-wrap">
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Dashboard</span>
                        <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">Comunicaciones</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Validaciones</span>
                    </div>
                    <p class="text-sm text-gray-500">Hacé clic en la pestaña "Comunicaciones".</p>
                `
            },
            {
                titulo: 'Paso 2: Filtrar emails',
                icono: 'filter',
                color: 'purple',
                contenido: `
                    <p class="text-gray-600 mb-4">Usá los filtros para encontrar emails:</p>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-sm">Buscar por email o asunto</span>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">Todos</span>
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">Enviados</span>
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">Fallidos</span>
                        </div>
                    </div>
                `
            },
            {
                titulo: 'Paso 3: Ver detalles',
                icono: 'eye',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Cada email muestra:</p>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded">Enviado</span>
                            <span class="text-sm">Estado del envío</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                            <i data-lucide="clock" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-sm">Fecha y hora del envío</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                            <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-sm">Destinatario</span>
                        </div>
                    </div>
                `
            },
            {
                titulo: 'Paso 4: Exportar datos',
                icono: 'download',
                color: 'amber',
                contenido: `
                    <p class="text-gray-600 mb-4">Descargá un reporte en CSV:</p>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4 text-center">
                        <button class="px-4 py-2 bg-amber-600 text-white rounded-lg font-medium">
                            <i data-lucide="download" class="w-4 h-4 inline mr-2"></i>
                            Exportar CSV
                        </button>
                    </div>
                    <p class="text-sm text-gray-500">El archivo incluye todos los emails filtrados.</p>
                `
            }
        ]
    },
    'ver-validaciones': {
        titulo: 'Cómo ver validaciones QR',
        pasos: [
            {
                titulo: 'Paso 1: Ir a Validaciones',
                icono: 'qr-code',
                color: 'purple',
                contenido: `
                    <p class="text-gray-600 mb-4">Accedé al registro de validaciones:</p>
                    <div class="flex gap-2 mb-4 flex-wrap">
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Dashboard</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Comunicaciones</span>
                        <span class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm font-medium">Validaciones</span>
                    </div>
                `
            },
            {
                titulo: 'Paso 2: Entender los datos',
                icono: 'info',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Cada validación registra:</p>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                            <i data-lucide="calendar" class="w-4 h-4 text-blue-500"></i>
                            <span class="text-sm">Fecha y hora del escaneo</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                            <i data-lucide="file-text" class="w-4 h-4 text-green-500"></i>
                            <span class="text-sm">Documento validado</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                            <i data-lucide="map-pin" class="w-4 h-4 text-purple-500"></i>
                            <span class="text-sm">IP y ubicación aproximada</span>
                        </div>
                    </div>
                `
            },
            {
                titulo: 'Paso 3: Seguridad',
                icono: 'shield',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Este registro te ayuda a:</p>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-3 p-2 bg-green-50 rounded-lg">
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                            <span class="text-sm">Verificar uso legítimo de certificados</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-green-50 rounded-lg">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                            <span class="text-sm">Detectar actividad sospechosa</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-green-50 rounded-lg">
                            <i data-lucide="bar-chart" class="w-4 h-4 text-blue-600"></i>
                            <span class="text-sm">Analizar tendencias de uso</span>
                        </div>
                    </div>
                `
            }
        ]
    },
    'configurar-notificaciones': {
        titulo: 'Cómo configurar notificaciones',
        pasos: [
            {
                titulo: 'Paso 1: Ir a Configuración',
                icono: 'settings',
                color: 'gray',
                contenido: `
                    <p class="text-gray-600 mb-4">Accedé a las opciones de notificaciones:</p>
                    <div class="flex gap-2 mb-4 flex-wrap">
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Dashboard</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Comunicaciones</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Validaciones</span>
                        <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">Configuración</span>
                    </div>
                `
            },
            {
                titulo: 'Paso 2: Elegir frecuencia',
                icono: 'clock',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Seleccioná cada cuánto recibir reportes:</p>
                    <div class="space-y-2 mb-4">
                        <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer">
                            <input type="radio" name="freq"> <span class="text-sm">Diario</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer">
                            <input type="radio" name="freq" checked> <span class="text-sm">Semanal</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 border rounded-lg cursor-pointer">
                            <input type="radio" name="freq"> <span class="text-sm">Mensual</span>
                        </label>
                    </div>
                `
            },
            {
                titulo: 'Paso 3: Configurar alertas',
                icono: 'bell',
                color: 'amber',
                contenido: `
                    <p class="text-gray-600 mb-4">Activá las alertas importantes:</p>
                    <div class="space-y-2 mb-4">
                        <label class="flex items-center justify-between p-2 border rounded-lg">
                            <span class="text-sm">Errores de envío</span>
                            <div class="w-10 h-6 bg-green-500 rounded-full relative">
                                <div class="w-4 h-4 bg-white rounded-full absolute right-1 top-1"></div>
                            </div>
                        </label>
                        <label class="flex items-center justify-between p-2 border rounded-lg">
                            <span class="text-sm">Validaciones sospechosas</span>
                            <div class="w-10 h-6 bg-green-500 rounded-full relative">
                                <div class="w-4 h-4 bg-white rounded-full absolute right-1 top-1"></div>
                            </div>
                        </label>
                    </div>
                `
            },
            {
                titulo: 'Paso 4: Guardar',
                icono: 'save',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Guardá la configuración:</p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 text-center">
                        <button class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium">
                            <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                            Guardar Configuración
                        </button>
                    </div>
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm">
                        <p class="text-green-700"><i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i> Las notificaciones se enviarán según tu configuración.</p>
                    </div>
                `
            }
        ]
    },
    'entender-dashboard': {
        titulo: 'Cómo usar el Dashboard',
        pasos: [
            {
                titulo: 'Vista general de actividad',
                icono: 'activity',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">El Dashboard muestra un resumen de toda la actividad de tu institución:</p>
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="text-center p-3 bg-blue-50 rounded-lg">
                            <i data-lucide="mail" class="w-6 h-6 mx-auto text-blue-600 mb-1"></i>
                            <p class="text-xs text-gray-600">Emails</p>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded-lg">
                            <i data-lucide="qr-code" class="w-6 h-6 mx-auto text-purple-600 mb-1"></i>
                            <p class="text-xs text-gray-600">Validaciones</p>
                        </div>
                        <div class="text-center p-3 bg-amber-50 rounded-lg">
                            <i data-lucide="eye" class="w-6 h-6 mx-auto text-amber-600 mb-1"></i>
                            <p class="text-xs text-gray-600">Accesos</p>
                        </div>
                    </div>
                `
            },
            {
                titulo: 'Seleccionar período',
                icono: 'calendar',
                color: 'amber',
                contenido: `
                    <p class="text-gray-600 mb-4">Elegí el rango de tiempo que querés analizar:</p>
                    <div class="flex gap-2 mb-4 flex-wrap justify-center">
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">7 días</span>
                        <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">30 días</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">90 días</span>
                    </div>
                    <p class="text-sm text-gray-500">Las estadísticas se actualizan automáticamente según el período seleccionado.</p>
                `
            },
            {
                titulo: 'Interpretar las métricas',
                icono: 'bar-chart-2',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Cada tarjeta muestra información importante:</p>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-3 p-2 bg-green-50 rounded-lg">
                            <i data-lucide="trending-up" class="w-4 h-4 text-green-600"></i>
                            <span class="text-sm">Tendencia: comparativa con período anterior</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 bg-blue-50 rounded-lg">
                            <i data-lucide="hash" class="w-4 h-4 text-blue-600"></i>
                            <span class="text-sm">Total: cantidad acumulada en el período</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Hacé clic en cada tarjeta para ver el detalle.</p>
                `
            }
        ]
    }
};

let tutorialActual = null;
let pasoActual = 0;

function abrirTutorial(tutorialId) {
    const tutorial = tutorialesActividad[tutorialId];
    if (!tutorial) return;
    tutorialActual = tutorial;
    pasoActual = 0;
    document.getElementById('modal-tutorial').classList.remove('hidden');
    document.getElementById('tutorial-titulo').textContent = tutorial.titulo;
    const dotsContainer = document.getElementById('tutorial-dots');
    dotsContainer.innerHTML = tutorial.pasos.map((_, i) =>
        `<button onclick="irAPaso(${i})" class="w-2 h-2 rounded-full transition-all ${i === 0 ? 'bg-blue-600 w-4' : 'bg-gray-300 hover:bg-gray-400'}"></button>`
    ).join('');
    mostrarPasoTutorial(0);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function cerrarTutorial() {
    document.getElementById('modal-tutorial').classList.add('hidden');
    tutorialActual = null;
    pasoActual = 0;
}

function mostrarPasoTutorial(index) {
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

function tutorialAnterior() { if (pasoActual > 0) mostrarPasoTutorial(pasoActual - 1); }
function tutorialSiguiente() { if (tutorialActual && pasoActual < tutorialActual.pasos.length - 1) mostrarPasoTutorial(pasoActual + 1); }
function irAPaso(index) { mostrarPasoTutorial(index); }
</script>
