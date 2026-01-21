<?php
/**
 * EmailService - Servicio de envío de emails via SendGrid
 *
 * Soporta envío individual y masivo usando Personalizations.
 * Base para el futuro módulo Communica.
 *
 * @package VERUMax\Services
 * @version 1.0.0
 */

namespace VERUMax\Services;

use PDO;
use PDOException;

// Sin dependencias externas - usa conexión PDO directa

class EmailService
{
    /**
     * Conexión PDO cacheada
     */
    private static ?PDO $pdoGeneral = null;

    /**
     * Obtiene conexión a verumax_general
     */
    private static function getPDO(): PDO
    {
        if (self::$pdoGeneral === null) {
            // Usar DatabaseService si está disponible
            if (class_exists('VERUMax\Services\DatabaseService')) {
                self::$pdoGeneral = DatabaseService::get('general');
            } else {
                // Fallback con env()
                self::$pdoGeneral = new PDO(
                    "mysql:host=" . env('GENERAL_DB_HOST', 'localhost') . ";dbname=" . env('GENERAL_DB_NAME', 'verumax_general') . ";charset=utf8mb4",
                    env('GENERAL_DB_USER', 'root'),
                    env('GENERAL_DB_PASS', ''),
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            }
        }
        return self::$pdoGeneral;
    }

    /**
     * URL base de la API de SendGrid
     */
    private const SENDGRID_API_URL = 'https://api.sendgrid.com/v3/mail/send';

    /**
     * API Key global de VERUMax (fallback)
     * En producción, esto debería venir de variables de entorno
     */
    private const GLOBAL_API_KEY_ENV = 'SENDGRID_API_KEY';

    /**
     * Máximo de personalizaciones por request (límite SendGrid: 1000)
     */
    private const MAX_PERSONALIZATIONS = 1000;

    /**
     * Tipos de email soportados
     */
    public const TYPE_CERTIFICADO = 'certificado_disponible';
    public const TYPE_CONSTANCIA = 'constancia_disponible';
    public const TYPE_BIENVENIDA = 'bienvenida_curso';
    public const TYPE_RECORDATORIO = 'recordatorio';
    public const TYPE_NOTIFICACION = 'notificacion_general';
    public const TYPE_CAMPANA = 'campana_marketing';
    public const TYPE_EVALUACION = 'evaluacion_disponible';
    public const TYPE_EVALUACION_APROBADA = 'evaluacion_aprobada';

    /**
     * Obtiene la configuración de email para una instancia
     *
     * @param int $idInstancia
     * @return array|null
     */
    public static function getConfig(int $idInstancia): ?array
    {
        try {
            $pdo = self::getPDO();
            $stmt = $pdo->prepare("
                SELECT ec.*, i.slug, i.nombre as nombre_instancia
                FROM email_config ec
                JOIN instances i ON ec.id_instancia = i.id_instancia
                WHERE ec.id_instancia = :id AND ec.activo = 1
            ");
            $stmt->execute([':id' => $idInstancia]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("EmailService::getConfig error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene la API key de SendGrid para una instancia
     *
     * @param int $idInstancia
     * @return string|null
     */
    private static function getApiKey(int $idInstancia): ?string
    {
        $config = self::getConfig($idInstancia);

        if (!$config) {
            return self::getGlobalApiKey();
        }

        // Si usa API key global o no tiene propia
        if ($config['usar_sendgrid_global'] || empty($config['sendgrid_api_key'])) {
            return self::getGlobalApiKey();
        }

        // Desencriptar API key propia (TODO: implementar encriptación)
        return $config['sendgrid_api_key'];
    }

    /**
     * Obtiene la API key global de VERUMax
     *
     * @return string|null
     */
    private static function getGlobalApiKey(): ?string
    {
        // Primero intentar variable de entorno
        $envKey = getenv(self::GLOBAL_API_KEY_ENV);
        if ($envKey) {
            return $envKey;
        }

        // Fallback: archivo de configuración
        $configFile = __DIR__ . '/../../../config/sendgrid.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
            return $config['api_key'] ?? null;
        }

        return null;
    }

    /**
     * Obtiene un template de email
     *
     * @param string $codigo Código del template
     * @param int|null $idInstancia ID de instancia (null = template del sistema)
     * @return array|null
     */
    public static function getTemplate(string $codigo, ?int $idInstancia = null): ?array
    {
        try {
            $pdo = self::getPDO();

            // Buscar template específico de la instancia, o del sistema
            $stmt = $pdo->prepare("
                SELECT * FROM email_templates
                WHERE codigo = :codigo
                AND (id_instancia = :id_instancia OR id_instancia IS NULL)
                AND activo = 1
                ORDER BY id_instancia DESC
                LIMIT 1
            ");
            $stmt->execute([
                ':codigo' => $codigo,
                ':id_instancia' => $idInstancia
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("EmailService::getTemplate error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Renderiza un template con variables
     *
     * @param string $template Contenido del template
     * @param array $variables Variables a reemplazar
     * @return string
     */
    public static function renderTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars($value ?? ''), $template);
        }
        return $template;
    }

    /**
     * Envía emails masivos usando SendGrid Personalizations
     *
     * @param int $idInstancia ID de la instancia
     * @param string $tipoEmail Tipo de email (constante TYPE_*)
     * @param array $destinatarios Array de destinatarios con sus variables
     *        [['email' => 'x@y.com', 'nombre' => 'X', 'variables' => [...]], ...]
     * @param string|null $templateCode Código del template a usar
     * @param string|null $asuntoOverride Asunto personalizado (override del template)
     * @param int|null $sendAt Timestamp UNIX para envío programado (max 72hs en el futuro)
     * @return array ['enviados' => int, 'errores' => array, 'batch_id' => string]
     */
    public static function enviarMasivo(
        int $idInstancia,
        string $tipoEmail,
        array $destinatarios,
        ?string $templateCode = null,
        ?string $asuntoOverride = null,
        ?int $sendAt = null
    ): array {
        $resultado = [
            'enviados' => 0,
            'errores' => [],
            'batch_id' => null,
            'sin_email' => 0
        ];

        // Validaciones
        if (empty($destinatarios)) {
            $resultado['errores'][] = 'No hay destinatarios';
            return $resultado;
        }

        // Obtener configuración
        $config = self::getConfig($idInstancia);
        $apiKey = self::getApiKey($idInstancia);

        if (!$apiKey) {
            $resultado['errores'][] = 'No hay API key de SendGrid configurada';
            return $resultado;
        }

        // Obtener template
        $template = self::getTemplate($templateCode ?? $tipoEmail, $idInstancia);
        if (!$template) {
            $resultado['errores'][] = "Template '{$templateCode}' no encontrado";
            return $resultado;
        }

        // Obtener info de la institución para variables globales
        $instInfo = self::getInstanceInfo($idInstancia);

        // Preparar variables globales
        $variablesGlobales = [
            'nombre_institucion' => $instInfo['nombre'] ?? 'VERUMax',
            'logo_url' => $instInfo['logo_url'] ?? 'https://verumax.com/img/logo.png',
            'color_primario' => $instInfo['color_primario'] ?? '#3b82f6',
            'url_portal' => self::getPortalUrl($idInstancia)
        ];

        // Configurar remitente - prioridad: email_config > instances.email_envio > fallback
        $fromEmail = $config['email_remitente']
            ?? $instInfo['email_envio']
            ?? 'notificaciones@verumax.com';
        $fromName = $config['nombre_remitente'] ?? $instInfo['nombre'] ?? 'VERUMax';

        // Generar batch ID único
        $batchId = 'batch_' . $idInstancia . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
        $resultado['batch_id'] = $batchId;

        // Filtrar destinatarios con email válido
        $destinatariosValidos = [];
        foreach ($destinatarios as $dest) {
            if (empty($dest['email']) || !filter_var($dest['email'], FILTER_VALIDATE_EMAIL)) {
                $resultado['sin_email']++;
                continue;
            }
            $destinatariosValidos[] = $dest;
        }

        if (empty($destinatariosValidos)) {
            $resultado['errores'][] = 'Ningún destinatario tiene email válido';
            return $resultado;
        }

        // Dividir en chunks si hay más de MAX_PERSONALIZATIONS
        $chunks = array_chunk($destinatariosValidos, self::MAX_PERSONALIZATIONS);

        foreach ($chunks as $chunkIndex => $chunk) {
            // Preparar personalizations
            $personalizations = [];

            foreach ($chunk as $dest) {
                // Combinar variables globales con las del destinatario
                $variables = array_merge($variablesGlobales, $dest['variables'] ?? []);
                $variables['nombre_estudiante'] = $dest['nombre'] ?? 'Estudiante';

                // Renderizar asunto con variables
                $asunto = $asuntoOverride ?? $template['asunto_default'];
                $asuntoRenderizado = self::renderTemplate($asunto, $variables);

                $personalizations[] = [
                    'to' => [['email' => $dest['email'], 'name' => $dest['nombre'] ?? '']],
                    'subject' => $asuntoRenderizado,
                    'substitutions' => self::prepareSubstitutions($variables)
                ];
            }

            // Para envíos masivos, NO renderizar en PHP - dejar los {{placeholders}}
            // para que SendGrid los reemplace individualmente por cada destinatario
            // usando las substitutions de cada personalization
            $htmlContent = $template['contenido_html'];
            $textContent = $template['contenido_texto'] ?? strip_tags($template['contenido_html']);

            // Construir payload para SendGrid
            $payload = [
                'personalizations' => $personalizations,
                'from' => [
                    'email' => $fromEmail,
                    'name' => $fromName
                ],
                'content' => [
                    ['type' => 'text/plain', 'value' => $textContent],
                    ['type' => 'text/html', 'value' => $htmlContent]
                ],
                'tracking_settings' => [
                    'click_tracking' => ['enable' => true],
                    'open_tracking' => ['enable' => true]
                ],
                'custom_args' => [
                    'batch_id' => $batchId,
                    'instancia' => (string)$idInstancia,
                    'tipo' => $tipoEmail
                ]
            ];

            // Agregar send_at si está definido y es en el futuro (máximo 72hs)
            if ($sendAt !== null && $sendAt > time()) {
                $maxFuturo = time() + (72 * 60 * 60); // 72 horas
                if ($sendAt > $maxFuturo) {
                    error_log("EmailService: send_at excede 72hs, ajustando a máximo permitido");
                    $sendAt = $maxFuturo;
                }
                $payload['send_at'] = $sendAt;
                error_log("EmailService: Email programado para " . date('Y-m-d H:i:s', $sendAt));
            }

            // DEBUG: Log detallado de cada personalization
            error_log("EmailService::enviarMasivo - Batch: {$batchId}, Tipo: {$tipoEmail}, Chunk: {$chunkIndex}");
            foreach ($personalizations as $idx => $pers) {
                $email = $pers['to'][0]['email'] ?? 'N/A';
                $nombre = $pers['to'][0]['name'] ?? 'N/A';
                $nombreSub = $pers['substitutions']['{{nombre_estudiante}}'] ?? 'N/A';
                error_log("  Personalization {$idx}: email={$email}, nombre={$nombre}, nombre_estudiante={$nombreSub}");
            }

            // Enviar a SendGrid
            $response = self::sendToSendGrid($apiKey, $payload);

            if ($response['success']) {
                $resultado['enviados'] += count($chunk);

                // Registrar en logs
                self::logEnvioMasivo($idInstancia, $tipoEmail, $chunk, $batchId, $template['codigo']);
            } else {
                $resultado['errores'][] = "Chunk {$chunkIndex}: " . $response['error'];

                // Registrar errores
                self::logErrorMasivo($idInstancia, $tipoEmail, $chunk, $batchId, $response['error']);
            }
        }

        // Actualizar contador mensual
        self::actualizarContador($idInstancia, $resultado['enviados']);

        return $resultado;
    }

    /**
     * Envía un email individual
     *
     * @param int $idInstancia
     * @param string $tipoEmail
     * @param string $emailDestino
     * @param string $nombreDestino
     * @param array $variables
     * @param string|null $templateCode
     * @param int|null $sendAt Timestamp UNIX para envío programado (max 72hs en el futuro)
     * @return array ['success' => bool, 'error' => string|null, 'message_id' => string|null]
     */
    public static function enviarIndividual(
        int $idInstancia,
        string $tipoEmail,
        string $emailDestino,
        string $nombreDestino,
        array $variables,
        ?string $templateCode = null,
        ?int $sendAt = null
    ): array {
        $resultado = self::enviarMasivo(
            $idInstancia,
            $tipoEmail,
            [['email' => $emailDestino, 'nombre' => $nombreDestino, 'variables' => $variables]],
            $templateCode,
            null,    // asuntoOverride
            $sendAt  // propagamos el sendAt
        );

        return [
            'success' => $resultado['enviados'] > 0,
            'error' => $resultado['errores'][0] ?? null,
            'batch_id' => $resultado['batch_id']
        ];
    }

    /**
     * Prepara sustituciones para SendGrid (formato -key-)
     */
    private static function prepareSubstitutions(array $variables): array
    {
        $subs = [];
        foreach ($variables as $key => $value) {
            $subs['{{' . $key . '}}'] = (string)($value ?? '');
        }
        return $subs;
    }

    /**
     * Envía request a la API de SendGrid
     */
    private static function sendToSendGrid(string $apiKey, array $payload): array
    {
        $ch = curl_init(self::SENDGRID_API_URL);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("SendGrid cURL error: " . $curlError);
            return ['success' => false, 'error' => 'Error de conexión: ' . $curlError];
        }

        // SendGrid retorna 202 para envío exitoso
        if ($httpCode === 202) {
            return ['success' => true, 'error' => null];
        }

        // Error
        $errorData = json_decode($response, true);
        $errorMsg = $errorData['errors'][0]['message'] ?? "HTTP {$httpCode}";

        error_log("SendGrid error [{$httpCode}]: " . $response);
        return ['success' => false, 'error' => $errorMsg];
    }

    /**
     * Registra envío masivo exitoso en logs
     */
    private static function logEnvioMasivo(
        int $idInstancia,
        string $tipoEmail,
        array $destinatarios,
        string $batchId,
        string $templateCode
    ): void {
        try {
            $pdo = self::getPDO();
            $stmt = $pdo->prepare("
                INSERT INTO email_logs
                (id_instancia, tipo_email, email_destino, nombre_destino, asunto, template_usado,
                 estado, sendgrid_batch_id, enviado_at, datos_json)
                VALUES
                (:id_instancia, :tipo_email, :email_destino, :nombre_destino, :asunto, :template,
                 'enviado', :batch_id, NOW(), :datos_json)
            ");

            foreach ($destinatarios as $dest) {
                $stmt->execute([
                    ':id_instancia' => $idInstancia,
                    ':tipo_email' => $tipoEmail,
                    ':email_destino' => $dest['email'],
                    ':nombre_destino' => $dest['nombre'] ?? null,
                    ':asunto' => 'Notificación', // Simplificado
                    ':template' => $templateCode,
                    ':batch_id' => $batchId,
                    ':datos_json' => json_encode($dest['variables'] ?? [])
                ]);
            }
        } catch (PDOException $e) {
            error_log("EmailService::logEnvioMasivo error: " . $e->getMessage());
        }
    }

    /**
     * Registra errores de envío masivo
     */
    private static function logErrorMasivo(
        int $idInstancia,
        string $tipoEmail,
        array $destinatarios,
        string $batchId,
        string $errorMsg
    ): void {
        try {
            $pdo = self::getPDO();
            $stmt = $pdo->prepare("
                INSERT INTO email_logs
                (id_instancia, tipo_email, email_destino, nombre_destino, asunto,
                 estado, sendgrid_batch_id, error_mensaje, datos_json)
                VALUES
                (:id_instancia, :tipo_email, :email_destino, :nombre_destino, 'Error',
                 'error', :batch_id, :error_msg, :datos_json)
            ");

            foreach ($destinatarios as $dest) {
                $stmt->execute([
                    ':id_instancia' => $idInstancia,
                    ':tipo_email' => $tipoEmail,
                    ':email_destino' => $dest['email'],
                    ':nombre_destino' => $dest['nombre'] ?? null,
                    ':batch_id' => $batchId,
                    ':error_msg' => $errorMsg,
                    ':datos_json' => json_encode($dest['variables'] ?? [])
                ]);
            }
        } catch (PDOException $e) {
            error_log("EmailService::logErrorMasivo error: " . $e->getMessage());
        }
    }

    /**
     * Actualiza contador mensual de emails
     */
    private static function actualizarContador(int $idInstancia, int $cantidad): void
    {
        try {
            $pdo = self::getPDO();
            $stmt = $pdo->prepare("
                UPDATE email_config
                SET emails_enviados_mes = emails_enviados_mes + :cantidad,
                    ultimo_envio_at = NOW()
                WHERE id_instancia = :id
            ");
            $stmt->execute([':cantidad' => $cantidad, ':id' => $idInstancia]);
        } catch (PDOException $e) {
            error_log("EmailService::actualizarContador error: " . $e->getMessage());
        }
    }

    /**
     * Obtiene información básica de una instancia
     */
    private static function getInstanceInfo(int $idInstancia): array
    {
        try {
            $pdo = self::getPDO();
            $stmt = $pdo->prepare("
                SELECT slug, nombre, logo_url, color_primario, email_envio
                FROM instances
                WHERE id_instancia = :id
            ");
            $stmt->execute([':id' => $idInstancia]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    private static function getSlugFromId(int $idInstancia): ?string
    {
        try {
            $pdo = self::getPDO();
            $stmt = $pdo->prepare("SELECT slug FROM instances WHERE id_instancia = :id");
            $stmt->execute([':id' => $idInstancia]);
            return $stmt->fetchColumn() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Obtiene la URL del portal para una instancia
     */
    private static function getPortalUrl(int $idInstancia): string
    {
        $slug = self::getSlugFromId($idInstancia);
        if (!$slug) {
            return 'https://verumax.com';
        }

        // Determinar si usamos subdominio o path
        $useSubdomain = true; // Configurar según necesidad

        if ($useSubdomain) {
            return "https://{$slug}.verumax.com";
        } else {
            return "https://verumax.com/{$slug}";
        }
    }

    /**
     * Verifica si el servicio de email está configurado para una instancia
     */
    public static function estaConfigurado(int $idInstancia): bool
    {
        return self::getApiKey($idInstancia) !== null;
    }

    /**
     * Obtiene estadísticas de emails de una instancia
     */
    public static function getEstadisticas(int $idInstancia, ?string $periodo = 'mes'): array
    {
        try {
            $pdo = self::getPDO();

            $whereDate = match($periodo) {
                'dia' => "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)",
                'semana' => "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)",
                'mes' => "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
                'anio' => "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)",
                default => ""
            };

            $stmt = $pdo->prepare("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'enviado' THEN 1 ELSE 0 END) as enviados,
                    SUM(CASE WHEN estado = 'error' THEN 1 ELSE 0 END) as errores,
                    SUM(CASE WHEN estado = 'abierto' THEN 1 ELSE 0 END) as abiertos,
                    COUNT(DISTINCT email_destino) as destinatarios_unicos
                FROM email_logs
                WHERE id_instancia = :id {$whereDate}
            ");
            $stmt->execute([':id' => $idInstancia]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'total' => 0,
                'enviados' => 0,
                'errores' => 0,
                'abiertos' => 0,
                'destinatarios_unicos' => 0
            ];
        } catch (PDOException $e) {
            error_log("EmailService::getEstadisticas error: " . $e->getMessage());
            return [];
        }
    }
}
