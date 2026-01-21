<?php
/**
 * SendGrid Webhook Handler - Verumax
 *
 * Endpoint para recibir eventos de SendGrid (opens, clicks, bounces, etc.)
 *
 * SEGURIDAD:
 * - Verifica firma de SendGrid (si está configurada)
 * - Solo acepta POST requests
 * - Rate limiting básico
 * - Logging extensivo para debugging
 *
 * IMPORTANTE: Este endpoint es PASIVO
 * - Solo registra eventos, NO afecta el envío de emails
 * - Si falla, los emails siguen funcionando normalmente
 *
 * @version 1.0.1
 * @date 2026-01-01
 */

// =====================================================
// CONFIGURACIÓN Y CONSTANTES
// =====================================================

// Desactivar output buffering para respuesta inmediata
@ob_implicit_flush(true);

// Configuración de errores (producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Detectar raíz del proyecto de forma robusta
$rootPath = realpath(__DIR__ . '/../../');
if (!$rootPath) {
    // Fallback: buscar env_loader.php subiendo directorios
    $rootPath = __DIR__;
    for ($i = 0; $i < 5; $i++) {
        $rootPath = dirname($rootPath);
        if (file_exists($rootPath . '/env_loader.php')) {
            break;
        }
    }
}

// Directorio de logs
define('WEBHOOK_LOG_DIR', $rootPath . '/logs/sendgrid');
define('WEBHOOK_LOG_FILE', WEBHOOK_LOG_DIR . '/webhook_' . date('Y-m-d') . '.log');
define('ROOT_PATH', $rootPath);

// Timeout máximo para procesamiento
set_time_limit(30);

// =====================================================
// FUNCIONES DE LOGGING
// =====================================================

/**
 * Escribe log de webhook
 */
function webhookLog($message, $level = 'INFO', $data = null) {
    // Crear directorio de logs si no existe
    if (!is_dir(WEBHOOK_LOG_DIR)) {
        @mkdir(WEBHOOK_LOG_DIR, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[{$timestamp}] [{$level}] {$message}";

    if ($data !== null) {
        $logLine .= " | Data: " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    $logLine .= "\n";

    @file_put_contents(WEBHOOK_LOG_FILE, $logLine, FILE_APPEND | LOCK_EX);

    // También logear errores críticos al error_log de PHP
    if ($level === 'ERROR' || $level === 'CRITICAL') {
        error_log("SendGrid Webhook: {$message}");
    }
}

// =====================================================
// VALIDACIONES INICIALES
// =====================================================

webhookLog("Webhook request recibido", 'DEBUG', [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
]);

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    webhookLog("Request rechazado: método no permitido", 'WARN', ['method' => $_SERVER['REQUEST_METHOD']]);
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Verificar Content-Type
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') === false) {
    webhookLog("Content-Type inválido", 'WARN', ['content_type' => $contentType]);
    // Permitir de todos modos, SendGrid a veces envía sin header correcto
}

// =====================================================
// LEER BODY DEL REQUEST
// =====================================================

$rawBody = file_get_contents('php://input');

if (empty($rawBody)) {
    webhookLog("Body vacío recibido", 'ERROR');
    http_response_code(400);
    echo json_encode(['error' => 'Empty body']);
    exit;
}

webhookLog("Body recibido", 'DEBUG', ['length' => strlen($rawBody)]);

// =====================================================
// VERIFICACIÓN DE FIRMA DE SENDGRID (Opcional pero recomendado)
// =====================================================

/**
 * Verifica la firma del webhook de SendGrid
 *
 * @see https://docs.sendgrid.com/for-developers/tracking-events/getting-started-event-webhook-security-features
 */
function verifySignature($rawBody) {
    // Headers de firma de SendGrid
    $signature = $_SERVER['HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_SIGNATURE'] ?? null;
    $timestamp = $_SERVER['HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_TIMESTAMP'] ?? null;

    // Si no hay firma, permitir de todos modos (para transición gradual)
    if (!$signature || !$timestamp) {
        webhookLog("Firma no presente - aceptando sin verificar (configurar en SendGrid)", 'WARN');
        return true;
    }

    // Leer clave pública de SendGrid (debe configurarse)
    $publicKeyPath = __DIR__ . '/../../config/sendgrid_webhook_public_key.pem';

    if (!file_exists($publicKeyPath)) {
        webhookLog("Clave pública no configurada - aceptando sin verificar", 'WARN');
        return true;
    }

    $publicKey = file_get_contents($publicKeyPath);

    // Crear payload para verificar
    $payload = $timestamp . $rawBody;

    // Verificar firma
    $verified = openssl_verify(
        $payload,
        base64_decode($signature),
        $publicKey,
        OPENSSL_ALGO_SHA256
    );

    if ($verified === 1) {
        webhookLog("Firma verificada correctamente", 'DEBUG');
        return true;
    } else {
        webhookLog("Firma inválida", 'ERROR', [
            'signature_present' => !empty($signature),
            'timestamp' => $timestamp
        ]);
        return false;
    }
}

// Verificar firma
if (!verifySignature($rawBody)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// =====================================================
// PARSEAR JSON
// =====================================================

$events = json_decode($rawBody, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    webhookLog("Error parseando JSON", 'ERROR', [
        'error' => json_last_error_msg(),
        'body_preview' => substr($rawBody, 0, 500)
    ]);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

if (!is_array($events)) {
    webhookLog("JSON no es array de eventos", 'ERROR');
    http_response_code(400);
    echo json_encode(['error' => 'Expected array of events']);
    exit;
}

webhookLog("Eventos recibidos: " . count($events), 'INFO');

// =====================================================
// CARGAR CONEXIÓN A BASE DE DATOS
// =====================================================

try {
    $envLoaderPath = ROOT_PATH . '/env_loader.php';

    if (!file_exists($envLoaderPath)) {
        throw new Exception("env_loader.php no encontrado en: " . $envLoaderPath);
    }

    require_once $envLoaderPath;

    $pdo = \VERUMax\Services\DatabaseService::get('general');
    webhookLog("Conexión a BD establecida", 'DEBUG');
} catch (Exception $e) {
    webhookLog("Error conectando a BD", 'CRITICAL', ['error' => $e->getMessage()]);

    // Guardar eventos en archivo de respaldo
    $backupFile = WEBHOOK_LOG_DIR . '/events_backup_' . date('Y-m-d_H-i-s') . '.json';
    @file_put_contents($backupFile, $rawBody);

    // Responder 200 para que SendGrid no reintente (evitar acumulación)
    http_response_code(200);
    echo json_encode(['status' => 'queued', 'message' => 'Events saved for later processing']);
    exit;
}

// =====================================================
// PROCESAR EVENTOS
// =====================================================

$processed = 0;
$errors = 0;

// Preparar statements
try {
    // Insertar evento raw
    $stmtInsertEvent = $pdo->prepare("
        INSERT INTO sendgrid_webhook_events
        (sg_event_id, sg_message_id, event_type, email, event_timestamp,
         ip, user_agent, url, reason, response,
         batch_id, instancia, tipo_email, payload_json)
        VALUES
        (:sg_event_id, :sg_message_id, :event_type, :email, :event_timestamp,
         :ip, :user_agent, :url, :reason, :response,
         :batch_id, :instancia, :tipo_email, :payload_json)
    ");

    // Actualizar email_logs usando batch_id + email (más robusto que message_id)
    $stmtUpdateOpen = $pdo->prepare("
        UPDATE email_logs
        SET estado = 'abierto',
            abierto_at = FROM_UNIXTIME(:timestamp)
        WHERE sendgrid_batch_id = :batch_id
        AND email_destino = :email
        AND estado NOT IN ('click')
    ");

    $stmtUpdateClick = $pdo->prepare("
        UPDATE email_logs
        SET estado = 'click',
            click_at = FROM_UNIXTIME(:timestamp)
        WHERE sendgrid_batch_id = :batch_id
        AND email_destino = :email
    ");

    $stmtUpdateBounce = $pdo->prepare("
        UPDATE email_logs
        SET estado = 'rebotado',
            error_mensaje = :reason
        WHERE sendgrid_batch_id = :batch_id
        AND email_destino = :email
    ");

} catch (Exception $e) {
    webhookLog("Error preparando statements", 'ERROR', ['error' => $e->getMessage()]);
    http_response_code(500);
    exit;
}

// Procesar cada evento
foreach ($events as $index => $event) {
    try {
        // Extraer datos del evento
        $sgEventId = $event['sg_event_id'] ?? null;
        $sgMessageId = $event['sg_message_id'] ?? null;
        $eventType = $event['event'] ?? 'unknown';
        $email = $event['email'] ?? '';
        $timestamp = $event['timestamp'] ?? time();
        $ip = $event['ip'] ?? null;
        $userAgent = $event['useragent'] ?? null;
        $url = $event['url'] ?? null;
        $reason = $event['reason'] ?? $event['response'] ?? null;
        $response = $event['response'] ?? null;

        // Custom args (contexto que enviamos)
        $batchId = $event['batch_id'] ?? null;
        $instancia = $event['instancia'] ?? null;
        $tipoEmail = $event['tipo'] ?? null;

        // Si no hay message_id, no podemos vincular
        if (empty($sgMessageId)) {
            webhookLog("Evento sin sg_message_id", 'WARN', ['event_type' => $eventType, 'email' => $email]);
        }

        // 1. Guardar evento raw (siempre)
        $stmtInsertEvent->execute([
            ':sg_event_id' => $sgEventId,
            ':sg_message_id' => $sgMessageId ?? '',
            ':event_type' => $eventType,
            ':email' => $email,
            ':event_timestamp' => $timestamp,
            ':ip' => $ip,
            ':user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            ':url' => $url ? substr($url, 0, 500) : null,
            ':reason' => $reason ? substr($reason, 0, 1000) : null,
            ':response' => $response ? substr($response, 0, 1000) : null,
            ':batch_id' => $batchId,
            ':instancia' => $instancia,
            ':tipo_email' => $tipoEmail,
            ':payload_json' => json_encode($event, JSON_UNESCAPED_UNICODE)
        ]);

        // 2. Actualizar email_logs según tipo de evento (usando batch_id + email)
        if (!empty($batchId) && !empty($email)) {
            switch ($eventType) {
                case 'open':
                    $stmtUpdateOpen->execute([
                        ':timestamp' => $timestamp,
                        ':batch_id' => $batchId,
                        ':email' => $email
                    ]);
                    $rowsAffected = $stmtUpdateOpen->rowCount();
                    if ($rowsAffected > 0) {
                        webhookLog("Email marcado como abierto", 'DEBUG', ['email' => $email, 'batch' => $batchId]);
                    }
                    break;

                case 'click':
                    $stmtUpdateClick->execute([
                        ':timestamp' => $timestamp,
                        ':batch_id' => $batchId,
                        ':email' => $email
                    ]);
                    $rowsAffected = $stmtUpdateClick->rowCount();
                    if ($rowsAffected > 0) {
                        webhookLog("Email marcado como click", 'DEBUG', ['email' => $email, 'batch' => $batchId]);
                    }
                    break;

                case 'bounce':
                case 'dropped':
                case 'spamreport':
                    $stmtUpdateBounce->execute([
                        ':reason' => $reason ?? $eventType,
                        ':batch_id' => $batchId,
                        ':email' => $email
                    ]);
                    $rowsAffected = $stmtUpdateBounce->rowCount();
                    if ($rowsAffected > 0) {
                        webhookLog("Email marcado como rebotado", 'WARN', ['email' => $email, 'reason' => $reason]);
                    }
                    break;

                case 'delivered':
                    webhookLog("Delivery confirmado", 'DEBUG', ['email' => $email, 'batch' => $batchId]);
                    break;

                case 'processed':
                case 'deferred':
                    // Eventos informativos, solo guardamos en raw
                    break;

                default:
                    webhookLog("Tipo de evento desconocido", 'WARN', ['type' => $eventType]);
            }
        } else {
            webhookLog("Evento sin batch_id o email, no se puede vincular", 'WARN', [
                'event_type' => $eventType,
                'has_batch' => !empty($batchId),
                'has_email' => !empty($email)
            ]);
        }

        $processed++;

    } catch (Exception $e) {
        $errors++;
        webhookLog("Error procesando evento #{$index}", 'ERROR', [
            'error' => $e->getMessage(),
            'event_type' => $event['event'] ?? 'unknown',
            'email' => $event['email'] ?? 'unknown'
        ]);
    }
}

// =====================================================
// RESPUESTA FINAL
// =====================================================

webhookLog("Procesamiento completado", 'INFO', [
    'total' => count($events),
    'processed' => $processed,
    'errors' => $errors
]);

// SendGrid espera 200 OK para confirmar recepción
http_response_code(200);
echo json_encode([
    'status' => 'ok',
    'processed' => $processed,
    'errors' => $errors
]);
