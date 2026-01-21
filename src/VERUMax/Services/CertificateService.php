<?php
/**
 * CertificateService
 *
 * Servicio para gestionar certificados y códigos de validación.
 * Encapsula la generación, almacenamiento y verificación de códigos.
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

use PDO;
use PDOException;
use DateTime;

class CertificateService
{
    /**
     * Nombre de la base de datos
     */
    private const DB_NAME = 'certificatum';

    /**
     * Tipos de documento válidos (genus en latín)
     */
    public const TYPE_CERTIFICATE = 'certificatum_approbationis';
    public const TYPE_CONSTANCIA_REGULAR = 'testimonium_regulare';
    public const TYPE_CONSTANCIA_FINALIZACION = 'testimonium_completionis';
    public const TYPE_CONSTANCIA_INSCRIPCION = 'testimonium_inscriptionis';
    public const TYPE_ANALITICO = 'analyticum';
    public const TYPE_CERTIFICADO_DOCENTE = 'certificatum_doctoris';
    public const TYPE_CONSTANCIA_DOCENTE = 'testimonium_doctoris';

    /**
     * Obtiene la conexión a la base de datos
     *
     * @return PDO
     */
    private static function db(): PDO
    {
        return DatabaseService::get(self::DB_NAME);
    }

    /**
     * Genera o recupera el código de validación para un documento
     *
     * @param string $institution Código de la institución
     * @param string $dni DNI del estudiante
     * @param string $courseCode Código del curso
     * @param string $documentType Tipo de documento
     * @return string Código de validación (formato: VALID-XXXXXXXXXXXX)
     */
    public static function getValidationCode(
        string $institution,
        string $dni,
        string $courseCode,
        string $documentType = self::TYPE_CERTIFICATE
    ): string {
        try {
            // Buscar código existente
            $existing = self::findExistingCode($institution, $dni, $courseCode, $documentType);
            if ($existing) {
                return $existing;
            }

            // Generar nuevo código
            $code = ValidationCodeService::generate($dni, $courseCode);

            // Guardar en base de datos
            self::storeCode($institution, $dni, $courseCode, $code, $documentType);

            return $code;

        } catch (PDOException $e) {
            error_log("Error generando código de validación: " . $e->getMessage());
            // Fallback: generar sin guardar
            return ValidationCodeService::generate($dni, $courseCode);
        }
    }

    /**
     * Registra una consulta de validación
     *
     * @param string $code Código que se consultó
     * @return bool True si se registró correctamente
     */
    public static function logValidation(string $code): bool
    {
        try {
            $stmt = self::db()->prepare("
                UPDATE codigos_validacion
                SET veces_consultado = veces_consultado + 1,
                    ultima_consulta = NOW()
                WHERE codigo_validacion = :codigo
            ");
            $stmt->execute([':codigo' => $code]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error registrando consulta de validación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene información de un código de validación
     *
     * @param string $code Código de validación
     * @return array|null Información del código o null
     */
    public static function getCodeInfo(string $code): ?array
    {
        try {
            $stmt = self::db()->prepare("
                SELECT
                    codigo_validacion,
                    institucion,
                    dni,
                    codigo_curso,
                    tipo_documento,
                    fecha_generacion as fecha_creacion,
                    veces_consultado,
                    ultima_consulta
                FROM codigos_validacion
                WHERE codigo_validacion = :codigo
            ");
            $stmt->execute([':codigo' => $code]);

            $result = $stmt->fetch();
            return $result ?: null;

        } catch (PDOException $e) {
            error_log("Error obteniendo info del código: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica si un código existe en la base de datos
     *
     * @param string $code Código de validación
     * @return bool
     */
    public static function codeExists(string $code): bool
    {
        return self::getCodeInfo($code) !== null;
    }

    /**
     * Obtiene estadísticas de un código
     *
     * @param string $code Código de validación
     * @return array ['consultas' => int, 'ultima' => string|null]
     */
    public static function getCodeStats(string $code): array
    {
        $info = self::getCodeInfo($code);

        return [
            'consultas' => $info ? (int) $info['veces_consultado'] : 0,
            'ultima' => $info ? $info['ultima_consulta'] : null
        ];
    }

    /**
     * Registra una consulta de validación con detalles completos
     * Guarda IP, user-agent, referer y otros datos del visitante
     *
     * @param string $code Código consultado
     * @param bool $exitoso Si el código fue válido o no
     * @param string|null $institucion Institución del certificado
     * @param string|null $tipoDocumento Tipo de documento
     * @return bool True si se registró correctamente
     */
    public static function logValidationDetailed(
        string $code,
        bool $exitoso = true,
        ?string $institucion = null,
        ?string $tipoDocumento = null
    ): bool {
        try {
            // Obtener datos del visitante de forma segura
            $ip = self::getClientIP();
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null;
            $referer = isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 500) : null;

            $stmt = self::db()->prepare("
                INSERT INTO log_validaciones
                (codigo_validacion, institucion, ip_address, user_agent, referer, tipo_documento, exitoso)
                VALUES (:codigo, :institucion, :ip, :user_agent, :referer, :tipo_documento, :exitoso)
            ");

            return $stmt->execute([
                ':codigo' => $code,
                ':institucion' => $institucion,
                ':ip' => $ip,
                ':user_agent' => $userAgent,
                ':referer' => $referer,
                ':tipo_documento' => $tipoDocumento,
                ':exitoso' => $exitoso ? 1 : 0
            ]);

        } catch (PDOException $e) {
            // Log silencioso para no afectar la experiencia del usuario
            error_log("Error en logValidationDetailed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la IP real del cliente, considerando proxies y CloudFlare
     *
     * @return string|null
     */
    private static function getClientIP(): ?string
    {
        // Orden de prioridad para detectar IP real
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // CloudFlare
            'HTTP_X_FORWARDED_FOR',      // Proxy estándar
            'HTTP_X_REAL_IP',            // Nginx proxy
            'HTTP_CLIENT_IP',            // Algunos proxies
            'REMOTE_ADDR'                // Conexión directa
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // X-Forwarded-For puede tener múltiples IPs, tomar la primera
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validar que sea una IP válida
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return null;
    }

    /**
     * Obtiene estadísticas detalladas de validaciones
     *
     * @param string|null $institucion Filtrar por institución
     * @param int $dias Últimos N días
     * @return array Estadísticas
     */
    public static function getValidationStats(?string $institucion = null, int $dias = 30): array
    {
        try {
            $sql = "
                SELECT
                    COUNT(*) as total_consultas,
                    SUM(CASE WHEN exitoso = 1 THEN 1 ELSE 0 END) as exitosas,
                    SUM(CASE WHEN exitoso = 0 THEN 1 ELSE 0 END) as fallidas,
                    COUNT(DISTINCT ip_address) as visitantes_unicos,
                    COUNT(DISTINCT codigo_validacion) as codigos_distintos
                FROM log_validaciones
                WHERE fecha_consulta >= DATE_SUB(NOW(), INTERVAL :dias DAY)
            ";

            $params = [':dias' => $dias];

            if ($institucion) {
                $sql .= " AND institucion = :institucion";
                $params[':institucion'] = $institucion;
            }

            $stmt = self::db()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetch() ?: [
                'total_consultas' => 0,
                'exitosas' => 0,
                'fallidas' => 0,
                'visitantes_unicos' => 0,
                'codigos_distintos' => 0
            ];

        } catch (PDOException $e) {
            error_log("Error en getValidationStats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Genera la URL de validación completa
     *
     * @param string $code Código de validación
     * @param string $baseUrl URL base (opcional)
     * @return string URL completa
     */
    public static function getValidationUrl(string $code, string $baseUrl = 'https://verumax.com'): string
    {
        return ValidationCodeService::getValidationUrl($code, $baseUrl);
    }

    /**
     * Genera la URL del QR para un código
     *
     * @param string $code Código de validación
     * @param int $size Tamaño del QR
     * @return string URL del QR
     */
    public static function getQRUrl(string $code, int $size = 100): string
    {
        return QRCodeService::forValidation($code, $size);
    }

    /**
     * Genera el HTML del QR para un código
     *
     * @param string $code Código de validación
     * @param int $size Tamaño del QR
     * @return string HTML <img>
     */
    public static function getQRHtml(string $code, int $size = 100): string
    {
        return QRCodeService::validationHtml($code, $size);
    }

    // =========================================================================
    // LOGGING DE ACCESOS A CERTIFICADOS (Vista pantalla / Descarga PDF)
    // =========================================================================

    /**
     * Tipos de acción para logging de accesos
     */
    public const ACTION_VIEW = 'vista_pantalla';
    public const ACTION_DOWNLOAD = 'descarga_pdf';

    /**
     * Registra un acceso a certificado (vista en pantalla o descarga PDF)
     *
     * @param string $institucion Slug de la institución
     * @param string $dni DNI del titular
     * @param string $tipoAccion 'vista_pantalla' o 'descarga_pdf'
     * @param string|null $tipoDocumento Tipo de documento (genus)
     * @param string|null $codigoCurso Código del curso
     * @param string|null $nombreCurso Nombre del curso
     * @param string $tipoUsuario 'estudiante' o 'docente'
     * @param int|null $idParticipacion ID de participación docente (si aplica)
     * @param string|null $nombrePersona Nombre completo de la persona
     * @param string|null $idioma Idioma seleccionado (es_AR, pt_BR, etc)
     * @return bool True si se registró correctamente
     */
    public static function logAccesoCertificado(
        string $institucion,
        string $dni,
        string $tipoAccion,
        ?string $tipoDocumento = null,
        ?string $codigoCurso = null,
        ?string $nombreCurso = null,
        string $tipoUsuario = 'estudiante',
        ?int $idParticipacion = null,
        ?string $nombrePersona = null,
        ?string $idioma = null
    ): bool {
        try {
            // Obtener datos del visitante
            $ip = self::getClientIP();
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null;
            $referer = isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 500) : null;
            $dispositivo = self::detectDevice($userAgent);

            $stmt = self::db()->prepare("
                INSERT INTO log_accesos_certificados
                (institucion, dni, nombre_persona, tipo_accion, tipo_documento, codigo_curso, nombre_curso,
                 tipo_usuario, id_participacion, idioma, ip_address, user_agent, dispositivo, referer)
                VALUES (:institucion, :dni, :nombre_persona, :tipo_accion, :tipo_documento, :codigo_curso, :nombre_curso,
                        :tipo_usuario, :id_participacion, :idioma, :ip, :user_agent, :dispositivo, :referer)
            ");

            return $stmt->execute([
                ':institucion' => $institucion,
                ':dni' => $dni,
                ':nombre_persona' => $nombrePersona ? substr($nombrePersona, 0, 255) : null,
                ':tipo_accion' => $tipoAccion,
                ':tipo_documento' => $tipoDocumento,
                ':codigo_curso' => $codigoCurso,
                ':nombre_curso' => $nombreCurso ? substr($nombreCurso, 0, 255) : null,
                ':tipo_usuario' => $tipoUsuario,
                ':id_participacion' => $idParticipacion,
                ':idioma' => $idioma,
                ':ip' => $ip,
                ':user_agent' => $userAgent,
                ':dispositivo' => $dispositivo,
                ':referer' => $referer
            ]);

        } catch (PDOException $e) {
            // Log silencioso para no afectar la experiencia del usuario
            error_log("Error en logAccesoCertificado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene estadísticas de accesos a certificados
     *
     * @param string|null $institucion Filtrar por institución
     * @param int $dias Últimos N días
     * @return array Estadísticas
     */
    public static function getAccesosStats(?string $institucion = null, int $dias = 30): array
    {
        try {
            $sql = "
                SELECT
                    COUNT(*) as total_accesos,
                    SUM(CASE WHEN tipo_accion = 'vista_pantalla' THEN 1 ELSE 0 END) as vistas_pantalla,
                    SUM(CASE WHEN tipo_accion = 'descarga_pdf' THEN 1 ELSE 0 END) as descargas_pdf,
                    COUNT(DISTINCT dni) as usuarios_unicos,
                    SUM(CASE WHEN tipo_usuario = 'estudiante' THEN 1 ELSE 0 END) as accesos_estudiantes,
                    SUM(CASE WHEN tipo_usuario = 'docente' THEN 1 ELSE 0 END) as accesos_docentes
                FROM log_accesos_certificados
                WHERE fecha_acceso >= DATE_SUB(NOW(), INTERVAL :dias DAY)
            ";

            $params = [':dias' => $dias];

            if ($institucion) {
                $sql .= " AND institucion = :institucion";
                $params[':institucion'] = $institucion;
            }

            $stmt = self::db()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetch() ?: [
                'total_accesos' => 0,
                'vistas_pantalla' => 0,
                'descargas_pdf' => 0,
                'usuarios_unicos' => 0,
                'accesos_estudiantes' => 0,
                'accesos_docentes' => 0
            ];

        } catch (PDOException $e) {
            error_log("Error en getAccesosStats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los últimos accesos a certificados (para el panel admin)
     *
     * @param string|null $institucion Filtrar por institución
     * @param int $limite Número de registros
     * @param int $dias Últimos N días
     * @return array Lista de accesos
     */
    public static function getUltimosAccesos(?string $institucion = null, int $limite = 50, int $dias = 30): array
    {
        try {
            $sql = "
                SELECT
                    fecha_acceso,
                    dni,
                    nombre_persona,
                    tipo_accion,
                    tipo_documento,
                    codigo_curso,
                    nombre_curso,
                    tipo_usuario,
                    idioma,
                    ip_address,
                    dispositivo
                FROM log_accesos_certificados
                WHERE fecha_acceso >= DATE_SUB(NOW(), INTERVAL :dias DAY)
            ";

            $params = [':dias' => $dias];

            if ($institucion) {
                $sql .= " AND institucion = :institucion";
                $params[':institucion'] = $institucion;
            }

            $sql .= " ORDER BY fecha_acceso DESC LIMIT " . (int)$limite;

            $stmt = self::db()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll() ?: [];

        } catch (PDOException $e) {
            error_log("Error en getUltimosAccesos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Detecta el tipo de dispositivo desde el User-Agent
     *
     * @param string|null $userAgent
     * @return string|null
     */
    private static function detectDevice(?string $userAgent): ?string
    {
        if (!$userAgent) return null;

        $ua = strtolower($userAgent);

        if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) {
            return 'iOS';
        }
        if (strpos($ua, 'android') !== false) {
            return 'Android';
        }
        if (strpos($ua, 'windows phone') !== false) {
            return 'Windows Phone';
        }
        if (strpos($ua, 'macintosh') !== false || strpos($ua, 'mac os') !== false) {
            return 'Mac';
        }
        if (strpos($ua, 'windows') !== false) {
            return 'Windows';
        }
        if (strpos($ua, 'linux') !== false) {
            return 'Linux';
        }

        return 'Otro';
    }

    // =========================================================================
    // MÉTODOS PRIVADOS
    // =========================================================================

    /**
     * Busca un código existente en la base de datos
     *
     * @param string $institution
     * @param string $dni
     * @param string $courseCode
     * @param string $documentType
     * @return string|null
     */
    private static function findExistingCode(
        string $institution,
        string $dni,
        string $courseCode,
        string $documentType
    ): ?string {
        $stmt = self::db()->prepare("
            SELECT codigo_validacion FROM codigos_validacion
            WHERE institucion = :institucion
            AND dni = :dni
            AND codigo_curso = :codigo_curso
            AND tipo_documento = :tipo_documento
            LIMIT 1
        ");

        $stmt->execute([
            ':institucion' => $institution,
            ':dni' => $dni,
            ':codigo_curso' => $courseCode,
            ':tipo_documento' => $documentType
        ]);

        $result = $stmt->fetchColumn();
        return $result ?: null;
    }

    /**
     * Almacena un nuevo código en la base de datos
     *
     * @param string $institution
     * @param string $dni
     * @param string $courseCode
     * @param string $code
     * @param string $documentType
     * @return bool
     */
    private static function storeCode(
        string $institution,
        string $dni,
        string $courseCode,
        string $code,
        string $documentType
    ): bool {
        $stmt = self::db()->prepare("
            INSERT INTO codigos_validacion
            (institucion, dni, codigo_curso, codigo_validacion, tipo_documento)
            VALUES (:institucion, :dni, :codigo_curso, :codigo_validacion, :tipo_documento)
        ");

        return $stmt->execute([
            ':institucion' => $institution,
            ':dni' => $dni,
            ':codigo_curso' => $courseCode,
            ':codigo_validacion' => $code,
            ':tipo_documento' => $documentType
        ]);
    }

    // =========================================================================
    // UTILIDADES DE FORMATO
    // =========================================================================

    /**
     * Formatea una fecha al formato argentino
     *
     * @param string|null $date Fecha en formato YYYY-MM-DD
     * @param string $default Valor por defecto
     * @return string Fecha en formato DD/MM/YYYY
     */
    public static function formatDate(?string $date, string $default = 'N/A'): string
    {
        if (!$date) {
            return $default;
        }

        try {
            $dateObj = new DateTime($date);
            return $dateObj->format('d/m/Y');
        } catch (\Exception $e) {
            return $default;
        }
    }
}
