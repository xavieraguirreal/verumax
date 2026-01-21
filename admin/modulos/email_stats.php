<?php
/**
 * MÓDULO: EMAIL STATS
 * Estadísticas de emails enviados y tracking de SendGrid
 *
 * @version 1.0.0
 * @date 2026-01-01
 */

// Ya estamos autenticados por index.php
// $admin ya está disponible

require_once __DIR__ . '/../../env_loader.php';

use VERUMax\Services\DatabaseService;
use VERUMax\Services\InstitutionService;

$slug = $admin['slug'];
$pdoGeneral = DatabaseService::get('general');

// Obtener configuración de la instancia
$instance = InstitutionService::getConfig($slug);
if (!$instance) {
    die('Error: Instancia no encontrada');
}

$id_instancia = $instance['id_instancia'];

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

                // Validar email si está habilitado
                if ($notificar && !filter_var($email_notif, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email de notificación inválido');
                }

                // Verificar si existe configuración
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

                $mensaje = 'Configuración de notificaciones guardada correctamente';
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

// Configuración de notificaciones actual
$stmt = $pdoGeneral->prepare("
    SELECT notificar_estadisticas, notificar_email, notificar_frecuencia, notificar_rebotes_alta
    FROM email_config WHERE id_instancia = ?
");
$stmt->execute([$id_instancia]);
$config_notif = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'notificar_estadisticas' => 0,
    'notificar_email' => '',
    'notificar_frecuencia' => 'nunca',
    'notificar_rebotes_alta' => 0
];

// Período seleccionado
$periodo = $_GET['periodo'] ?? '30';
$periodoLabel = [
    '7' => 'Últimos 7 días',
    '30' => 'Últimos 30 días',
    '90' => 'Últimos 90 días'
][$periodo] ?? 'Últimos 30 días';

// Estadísticas generales
$stmt = $pdoGeneral->prepare("
    SELECT
        COUNT(*) as total_enviados,
        SUM(CASE WHEN estado IN ('enviado', 'abierto', 'click') THEN 1 ELSE 0 END) as entregados,
        SUM(CASE WHEN estado IN ('abierto', 'click') THEN 1 ELSE 0 END) as abiertos,
        SUM(CASE WHEN estado = 'click' THEN 1 ELSE 0 END) as clicks,
        SUM(CASE WHEN estado = 'rebotado' THEN 1 ELSE 0 END) as rebotes,
        SUM(CASE WHEN estado = 'error' THEN 1 ELSE 0 END) as errores
    FROM email_logs
    WHERE id_instancia = ?
    AND enviado_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
");
$stmt->execute([$id_instancia, $periodo]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Calcular tasas
$total = $stats['total_enviados'] ?: 1; // Evitar división por cero
$tasaApertura = round(($stats['abiertos'] / $total) * 100, 1);
$tasaClicks = round(($stats['clicks'] / $total) * 100, 1);
$tasaRebotes = round(($stats['rebotes'] / $total) * 100, 1);

// Estadísticas por día (para gráfico)
$stmt = $pdoGeneral->prepare("
    SELECT
        DATE(enviado_at) as fecha,
        COUNT(*) as enviados,
        SUM(CASE WHEN estado IN ('abierto', 'click') THEN 1 ELSE 0 END) as abiertos,
        SUM(CASE WHEN estado = 'click' THEN 1 ELSE 0 END) as clicks
    FROM email_logs
    WHERE id_instancia = ?
    AND enviado_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY DATE(enviado_at)
    ORDER BY fecha ASC
");
$stmt->execute([$id_instancia, $periodo]);
$statsPorDia = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Últimos eventos de webhook (si existen)
$webhookEventsExist = false;
$totalWebhookEvents = 0;
try {
    // Contar total de eventos recibidos (para saber si webhook funciona)
    $stmtCount = $pdoGeneral->query("SELECT COUNT(*) FROM sendgrid_webhook_events");
    $totalWebhookEvents = (int)$stmtCount->fetchColumn();

    // Buscar últimos eventos (sin filtrar por instancia por ahora)
    $stmt = $pdoGeneral->prepare("
        SELECT event_type, email, event_datetime, url, instancia
        FROM sendgrid_webhook_events
        ORDER BY event_datetime DESC
        LIMIT 20
    ");
    $stmt->execute();
    $ultimosEventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $webhookEventsExist = $totalWebhookEvents > 0;
} catch (Exception $e) {
    $ultimosEventos = [];
    // Tabla aún no existe, es normal si no se ejecutó la migración
}

// Últimos emails enviados
$stmt = $pdoGeneral->prepare("
    SELECT
        id_log, tipo_email, email_destino, nombre_destino, asunto,
        estado, enviado_at, abierto_at, click_at, error_mensaje
    FROM email_logs
    WHERE id_instancia = ?
    ORDER BY enviado_at DESC
    LIMIT 20
");
$stmt->execute([$id_instancia]);
$ultimosEmails = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Colores de la instancia
$colorPrimario = $instance['color_primario'] ?? '#3b82f6';
$colorSecundario = $instance['color_secundario'] ?? '#1e40af';
?>

<!-- ESTILOS -->
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
    }
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.5rem;
    }
    .stat-trend {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        display: inline-block;
        margin-top: 0.5rem;
    }
    .trend-up { background: #dcfce7; color: #166534; }
    .trend-down { background: #fee2e2; color: #991b1b; }
    .trend-neutral { background: #f3f4f6; color: #4b5563; }

    .evento-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-weight: 500;
    }
    .badge-open { background: #dbeafe; color: #1e40af; }
    .badge-click { background: #dcfce7; color: #166534; }
    .badge-bounce { background: #fee2e2; color: #991b1b; }
    .badge-delivered { background: #e0e7ff; color: #3730a3; }
    .badge-dropped { background: #fef3c7; color: #92400e; }

    .estado-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 500;
    }
    .estado-enviado { background: #e0e7ff; color: #3730a3; }
    .estado-abierto { background: #dbeafe; color: #1e40af; }
    .estado-click { background: #dcfce7; color: #166534; }
    .estado-rebotado { background: #fee2e2; color: #991b1b; }
    .estado-error { background: #fef3c7; color: #92400e; }

    .webhook-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
    }
    .webhook-active {
        background: #dcfce7;
        color: #166534;
    }
    .webhook-pending {
        background: #fef3c7;
        color: #92400e;
    }
</style>

<!-- CONTENIDO -->
<div class="space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Estadísticas de Emails</h2>
            <p class="text-gray-500 mt-1">Tracking de emails enviados vía SendGrid</p>
        </div>
        <div class="flex gap-2">
            <select onchange="window.location.href='?modulo=email_stats&periodo='+this.value"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <option value="7" <?= $periodo == '7' ? 'selected' : '' ?>>Últimos 7 días</option>
                <option value="30" <?= $periodo == '30' ? 'selected' : '' ?>>Últimos 30 días</option>
                <option value="90" <?= $periodo == '90' ? 'selected' : '' ?>>Últimos 90 días</option>
            </select>
        </div>
    </div>

    <?php if ($mensaje): ?>
    <div class="p-4 rounded-lg <?= $tipo_mensaje === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
        <?= htmlspecialchars($mensaje) ?>
    </div>
    <?php endif; ?>

    <!-- Estado del Webhook -->
    <?php if ($webhookEventsExist && count($ultimosEventos) > 0): ?>
    <div class="webhook-status webhook-active">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span><strong>Webhook activo</strong> - Recibiendo eventos de SendGrid en tiempo real</span>
    </div>
    <?php else: ?>
    <div class="webhook-status webhook-pending">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span><strong>Webhook pendiente</strong> - Configurar en SendGrid para habilitar tracking de aperturas y clicks</span>
    </div>
    <?php endif; ?>

    <!-- Tarjetas de Estadísticas -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="stat-card">
            <div class="stat-number" style="color: <?= $colorPrimario ?>">
                <?= number_format($stats['total_enviados']) ?>
            </div>
            <div class="stat-label">Emails Enviados</div>
        </div>

        <div class="stat-card">
            <div class="stat-number text-blue-600">
                <?= number_format($stats['entregados']) ?>
            </div>
            <div class="stat-label">Entregados</div>
        </div>

        <div class="stat-card">
            <div class="stat-number text-green-600">
                <?= number_format($stats['abiertos']) ?>
            </div>
            <div class="stat-label">Abiertos</div>
            <div class="stat-trend <?= $tasaApertura > 20 ? 'trend-up' : ($tasaApertura < 10 ? 'trend-down' : 'trend-neutral') ?>">
                <?= $tasaApertura ?>% tasa
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number text-emerald-600">
                <?= number_format($stats['clicks']) ?>
            </div>
            <div class="stat-label">Clicks</div>
            <div class="stat-trend <?= $tasaClicks > 5 ? 'trend-up' : ($tasaClicks < 1 ? 'trend-down' : 'trend-neutral') ?>">
                <?= $tasaClicks ?>% tasa
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number text-red-600">
                <?= number_format($stats['rebotes']) ?>
            </div>
            <div class="stat-label">Rebotes</div>
            <div class="stat-trend <?= $tasaRebotes < 2 ? 'trend-up' : ($tasaRebotes > 5 ? 'trend-down' : 'trend-neutral') ?>">
                <?= $tasaRebotes ?>% tasa
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number text-amber-600">
                <?= number_format($stats['errores']) ?>
            </div>
            <div class="stat-label">Errores</div>
        </div>
    </div>

    <!-- Últimos Emails y Configuración -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Últimos Emails Enviados -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Últimos Emails Enviados</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destinatario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($ultimosEmails)): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                No hay emails enviados en este período
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($ultimosEmails as $email): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800"><?= htmlspecialchars($email['nombre_destino'] ?? '') ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($email['email_destino']) ?></div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <?= ucfirst(str_replace('_', ' ', $email['tipo_email'])) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="estado-badge estado-<?= $email['estado'] ?>">
                                    <?= ucfirst($email['estado']) ?>
                                </span>
                                <?php if ($email['abierto_at']): ?>
                                <div class="text-xs text-gray-400 mt-1">
                                    Abierto: <?= date('d/m H:i', strtotime($email['abierto_at'])) ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($email['click_at']): ?>
                                <div class="text-xs text-green-600 mt-1">
                                    Click: <?= date('d/m H:i', strtotime($email['click_at'])) ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <?= date('d/m/Y H:i', strtotime($email['enviado_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Configuración de Notificaciones -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Notificaciones</h3>
                <p class="text-xs text-gray-500 mt-1">Recibir reportes de estadísticas por email</p>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="accion" value="guardar_config_notificaciones">

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="notificar_estadisticas"
                               <?= $config_notif['notificar_estadisticas'] ? 'checked' : '' ?>
                               class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Habilitar notificaciones</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Email para notificaciones</label>
                    <input type="email" name="notificar_email"
                           value="<?= htmlspecialchars($config_notif['notificar_email'] ?? '') ?>"
                           placeholder="admin@ejemplo.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Frecuencia de reportes</label>
                    <select name="notificar_frecuencia"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="nunca" <?= $config_notif['notificar_frecuencia'] == 'nunca' ? 'selected' : '' ?>>Nunca</option>
                        <option value="diario" <?= $config_notif['notificar_frecuencia'] == 'diario' ? 'selected' : '' ?>>Diario</option>
                        <option value="semanal" <?= $config_notif['notificar_frecuencia'] == 'semanal' ? 'selected' : '' ?>>Semanal</option>
                        <option value="mensual" <?= $config_notif['notificar_frecuencia'] == 'mensual' ? 'selected' : '' ?>>Mensual</option>
                    </select>
                </div>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="notificar_rebotes_alta"
                               <?= $config_notif['notificar_rebotes_alta'] ? 'checked' : '' ?>
                               class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Alertar si tasa de rebote > 5%</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full px-4 py-2 text-white rounded-lg text-sm font-medium transition-colors"
                        style="background: <?= $colorPrimario ?>">
                    Guardar Configuración
                </button>
            </form>
        </div>
    </div>

    <?php if ($webhookEventsExist && count($ultimosEventos) > 0): ?>
    <!-- Últimos Eventos de Webhook -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Últimos Eventos de Webhook (Tiempo Real)</h3>
            <p class="text-xs text-gray-500 mt-1">Eventos recibidos directamente de SendGrid</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL (si aplica)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha/Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($ultimosEventos as $evento): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <?php
                            $badgeClass = match($evento['event_type']) {
                                'open' => 'badge-open',
                                'click' => 'badge-click',
                                'bounce', 'dropped' => 'badge-bounce',
                                'delivered' => 'badge-delivered',
                                default => 'badge-delivered'
                            };
                            ?>
                            <span class="evento-badge <?= $badgeClass ?>">
                                <?= strtoupper($evento['event_type']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <?= htmlspecialchars($evento['email']) ?>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">
                            <?= $evento['url'] ? htmlspecialchars($evento['url']) : '-' ?>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            <?= $evento['event_datetime'] ? date('d/m/Y H:i:s', strtotime($evento['event_datetime'])) : '-' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- PANEL DE AYUDA LATERAL -->
<?php /* E1 - Panel de Ayuda para Email Stats */ ?>

<!-- Botón flotante de ayuda -->
<button id="btn-ayuda-flotante" onclick="togglePanelAyuda()"
        class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-all z-40 flex items-center justify-center"
        title="Ayuda">
    <i data-lucide="help-circle" class="w-6 h-6"></i>
</button>

<!-- Panel lateral de ayuda -->
<div id="panel-ayuda" class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
    <div class="bg-blue-600 text-white p-4 flex-shrink-0">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold flex items-center gap-2">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                Centro de Ayuda
            </h2>
            <button onclick="togglePanelAyuda()" class="p-1 hover:bg-blue-500 rounded transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="mt-3">
            <input type="text" id="busqueda-ayuda" placeholder="Buscar en la ayuda..."
                   onkeyup="filtrarAyuda(this.value)"
                   class="w-full px-4 py-2 rounded-lg text-gray-800 text-sm">
        </div>
    </div>
    <div id="ayuda-contexto" class="px-4 py-2 bg-blue-50 border-b text-sm text-blue-700 flex items-center gap-2 flex-shrink-0">
        <i data-lucide="info" class="w-4 h-4"></i>
        <span id="ayuda-contexto-texto">Estadísticas de Emails</span>
    </div>
    <div id="ayuda-contenido" class="flex-1 overflow-y-auto p-4"></div>
    <div class="p-4 bg-gray-50 border-t flex-shrink-0">
        <p class="text-xs text-gray-500 mb-2">¿Necesitás más ayuda?</p>
        <a href="mailto:soporte@verumax.com" class="text-blue-600 text-sm hover:underline">Contactar soporte</a>
    </div>
</div>

<div id="overlay-ayuda" onclick="togglePanelAyuda()" class="fixed inset-0 bg-black bg-opacity-30 z-40 hidden"></div>

<script>
// PANEL DE AYUDA - EMAIL STATS
const contenidoAyudaEmailStats = {
    general: {
        titulo: 'Estadísticas de Emails',
        contenido: `
            <div class="space-y-4">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <h4 class="font-semibold text-blue-800">¿Qué es este módulo?</h4>
                    <p class="text-sm text-blue-700 mt-1">Monitoreo de emails enviados vía SendGrid con métricas de entrega, apertura y clicks.</p>
                </div>
                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">📊 Métricas</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• <strong>Enviados:</strong> Total de emails despachados</li>
                        <li>• <strong>Entregados:</strong> Llegaron al servidor destino</li>
                        <li>• <strong>Abiertos:</strong> El destinatario los abrió</li>
                        <li>• <strong>Clicks:</strong> Se hizo clic en enlaces</li>
                        <li>• <strong>Rebotes:</strong> No pudieron entregarse</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 bg-green-50">
                    <h4 class="font-semibold text-green-800 mb-2">✅ Tasas Saludables</h4>
                    <ul class="text-sm text-green-700 space-y-1">
                        <li>• Apertura: &gt; 20%</li>
                        <li>• Clicks: &gt; 5%</li>
                        <li>• Rebotes: &lt; 2%</li>
                    </ul>
                </div>
            </div>
        `
    },
    webhook: {
        titulo: 'Webhook SendGrid',
        contenido: `
            <div class="space-y-4">
                <div class="bg-purple-50 border-l-4 border-purple-400 p-4 rounded">
                    <h4 class="font-semibold text-purple-800">¿Qué es el Webhook?</h4>
                    <p class="text-sm text-purple-700 mt-1">Permite tracking en tiempo real de aperturas, clicks y rebotes.</p>
                </div>
                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">🔔 Eventos</h4>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li><span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">OPEN</span> Email abierto</li>
                        <li><span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs">CLICK</span> Click en enlace</li>
                        <li><span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs">BOUNCE</span> Rebote</li>
                    </ul>
                </div>
            </div>
        `
    },
    notificaciones: {
        titulo: 'Notificaciones',
        contenido: `
            <div class="space-y-4">
                <div class="border rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-2">📅 Frecuencias</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• <strong>Diario:</strong> Resumen cada mañana</li>
                        <li>• <strong>Semanal:</strong> Resumen los lunes</li>
                        <li>• <strong>Mensual:</strong> Día 1 de cada mes</li>
                    </ul>
                </div>
                <div class="border rounded-lg p-4 bg-red-50">
                    <h4 class="font-semibold text-red-800 mb-2">🚨 Alerta Rebotes</h4>
                    <p class="text-sm text-red-700">Notificación si rebotes &gt; 5%</p>
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
    } else {
        panel.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        btnFlotante.classList.remove('hidden');
    }
}

function actualizarAyudaContextual() {
    document.getElementById('ayuda-contexto-texto').textContent = contenidoAyudaEmailStats['general'].titulo;
    document.getElementById('ayuda-contenido').innerHTML = contenidoAyudaEmailStats['general'].contenido;
}

function filtrarAyuda(termino) {
    termino = termino.toLowerCase().trim();
    const contenedor = document.getElementById('ayuda-contenido');
    if (!termino) { actualizarAyudaContextual(); return; }
    let resultados = '';
    for (const [key, data] of Object.entries(contenidoAyudaEmailStats)) {
        if (data.contenido.toLowerCase().includes(termino) || data.titulo.toLowerCase().includes(termino)) {
            resultados += `<div class="mb-4 p-3 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100" onclick="mostrarAyudaSeccion('${key}')"><h4 class="font-semibold text-gray-800">${data.titulo}</h4></div>`;
        }
    }
    if (!resultados) resultados = '<div class="text-center py-8 text-gray-500">No se encontraron resultados</div>';
    contenedor.innerHTML = resultados;
    document.getElementById('ayuda-contexto-texto').textContent = 'Resultados';
}

function mostrarAyudaSeccion(seccion) {
    document.getElementById('busqueda-ayuda').value = '';
    document.getElementById('ayuda-contexto-texto').textContent = contenidoAyudaEmailStats[seccion].titulo;
    document.getElementById('ayuda-contenido').innerHTML = contenidoAyudaEmailStats[seccion].contenido;
}

document.addEventListener('DOMContentLoaded', function() { if (typeof lucide !== 'undefined') lucide.createIcons(); });
</script>
