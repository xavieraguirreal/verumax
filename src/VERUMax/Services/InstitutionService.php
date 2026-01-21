<?php
/**
 * InstitutionService
 *
 * Servicio para gestionar configuración de instituciones.
 * Centraliza acceso a datos de instituciones desde múltiples bases de datos.
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

use PDO;
use PDOException;

class InstitutionService
{
    /**
     * Cache de configuraciones de instituciones
     */
    private static array $cache = [];

    /**
     * Nombres de las bases de datos
     */
    private const DB_GENERAL = 'general';
    private const DB_IDENTITAS = 'identitas';
    private const DB_CERTIFICATUM = 'certificatum';

    /**
     * Obtiene conexión a una base de datos específica
     * REFACTORIZADO: Usa DatabaseService para respetar configuración de .env
     *
     * @param string $db Nombre de la BD (general, identitas, certificatum)
     * @return PDO
     */
    private static function getConnection(string $db): PDO
    {
        return DatabaseService::get($db);
    }

    /**
     * Obtiene la configuración completa de una institución por slug
     *
     * @param string $slug Slug de la institución (ej: 'sajur')
     * @return array|null Configuración combinada o null si no existe
     */
    public static function getConfig(string $slug): ?array
    {
        // Verificar cache
        if (isset(self::$cache[$slug])) {
            return self::$cache[$slug];
        }

        try {
            // 1. Obtener datos generales de la instancia
            $pdo = self::getConnection(self::DB_GENERAL);
            $stmt = $pdo->prepare("
                SELECT * FROM instances
                WHERE slug = :slug AND activo = 1
            ");
            $stmt->execute(['slug' => $slug]);
            $instance = $stmt->fetch();

            if (!$instance) {
                return null;
            }

            $config = $instance;

            // 2. Obtener configuración de Identitas
            try {
                $pdo_identi = self::getConnection(self::DB_IDENTITAS);
                $stmt = $pdo_identi->prepare("
                    SELECT * FROM identitas_config
                    WHERE id_instancia = :id_instancia
                ");
                $stmt->execute(['id_instancia' => $instance['id_instancia']]);
                $identitas_config = $stmt->fetch();

                if ($identitas_config) {
                    $config = array_merge($config, $identitas_config);
                }
            } catch (PDOException $e) {
                error_log("Error obteniendo config Identitas: " . $e->getMessage());
            }

            // 3. Obtener configuración de Certificatum
            try {
                $pdo_cert = self::getConnection(self::DB_CERTIFICATUM);
                $stmt = $pdo_cert->prepare("
                    SELECT * FROM certificatum_config
                    WHERE id_instancia = :id_instancia
                ");
                $stmt->execute(['id_instancia' => $instance['id_instancia']]);
                $cert_config = $stmt->fetch();

                if ($cert_config) {
                    // Los campos en certificatum_config ya tienen el prefijo
                    $config['certificatum_modo'] = $cert_config['certificatum_modo'] ?? 'pagina';
                    $config['certificatum_titulo'] = $cert_config['certificatum_titulo'] ?? 'Certificados';
                    $config['certificatum_icono'] = $cert_config['certificatum_icono'] ?? 'award';
                    $config['certificatum_posicion'] = $cert_config['certificatum_posicion'] ?? 99;

                    // Campos de contenido
                    $config['certificatum_descripcion'] = $cert_config['certificatum_descripcion'] ?? '';
                    $config['certificatum_cta_texto'] = $cert_config['certificatum_cta_texto'] ?? 'Ver mis certificados';
                    $config['certificatum_estadisticas'] = $cert_config['certificatum_estadisticas'] ?? null;
                    $config['certificatum_mostrar_stats'] = $cert_config['certificatum_mostrar_stats'] ?? 1;
                    $config['certificatum_features'] = $cert_config['certificatum_features'] ?? null;

                    // Campos de paleta propia
                    $config['certificatum_usar_paleta_general'] = $cert_config['certificatum_usar_paleta_general'] ?? 1;
                    $config['certificatum_paleta_colores_propia'] = $cert_config['certificatum_paleta_colores_propia'] ?? null;
                    $config['certificatum_color_primario_propio'] = $cert_config['certificatum_color_primario_propio'] ?? null;
                    $config['certificatum_color_secundario_propio'] = $cert_config['certificatum_color_secundario_propio'] ?? null;
                    $config['certificatum_color_acento_propio'] = $cert_config['certificatum_color_acento_propio'] ?? null;

                    // Configuración de demora de certificados (migrado desde instances)
                    $config['demora_certificado_horas'] = $cert_config['demora_certificado_horas'] ?? 24;

                    // modulo_certificatum ya viene de instances, no sobrescribir
                }
            } catch (PDOException $e) {
                error_log("Error obteniendo config Certificatum: " . $e->getMessage());
            }

            // Guardar en cache
            self::$cache[$slug] = $config;

            return $config;

        } catch (PDOException $e) {
            error_log("Error obteniendo configuración de institución: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un valor específico de la configuración
     *
     * @param string $slug Slug de la institución
     * @param string $key Clave de configuración
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public static function get(string $slug, string $key, $default = null)
    {
        $config = self::getConfig($slug);
        return $config[$key] ?? $default;
    }

    /**
     * Verifica si una institución existe y está activa
     *
     * @param string $slug Slug de la institución
     * @return bool
     */
    public static function exists(string $slug): bool
    {
        return self::getConfig($slug) !== null;
    }

    /**
     * Verifica si Identitas está activo para una institución
     *
     * @param string $slug Slug de la institución
     * @return bool
     */
    public static function isIdentitasActive(string $slug): bool
    {
        return (bool) self::get($slug, 'identitas_activo', false);
    }

    /**
     * Verifica si Certificatum está activo para una institución
     *
     * @param string $slug Slug de la institución
     * @return bool
     */
    public static function isCertificatumActive(string $slug): bool
    {
        return (bool) self::get($slug, 'modulo_certificatum', false);
    }

    /**
     * Obtiene el nombre de la institución
     *
     * @param string $slug Slug de la institución
     * @return string
     */
    public static function getName(string $slug): string
    {
        return self::get($slug, 'nombre', 'Institución');
    }

    /**
     * Obtiene los colores de la institución
     *
     * @param string $slug Slug de la institución
     * @return array ['primario', 'secundario', 'acento']
     */
    public static function getColors(string $slug): array
    {
        $config = self::getConfig($slug);
        return [
            'primario' => $config['color_primario'] ?? '#2E7D32',
            'secundario' => $config['color_secundario'] ?? '#1B5E20',
            'acento' => $config['color_acento'] ?? '#66BB6A',
        ];
    }

    /**
     * Obtiene la URL del logo
     *
     * @param string $slug Slug de la institución
     * @return string|null
     */
    public static function getLogoUrl(string $slug): ?string
    {
        return self::get($slug, 'logo_url');
    }

    /**
     * Lista todas las instituciones activas
     *
     * @return array Lista de instituciones con slug y nombre
     */
    public static function listAll(): array
    {
        try {
            $pdo = self::getConnection(self::DB_GENERAL);
            $stmt = $pdo->query("
                SELECT slug, nombre, logo_url
                FROM instances
                WHERE activo = 1
                ORDER BY nombre
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error listando instituciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Limpia el cache de configuraciones
     *
     * @param string|null $slug Slug específico o null para limpiar todo
     * @return void
     */
    public static function clearCache(?string $slug = null): void
    {
        if ($slug) {
            unset(self::$cache[$slug]);
        } else {
            self::$cache = [];
        }
    }

    /**
     * Cierra todas las conexiones
     *
     * @return void
     */
    public static function closeConnections(): void
    {
        self::$connections = [];
    }

    /**
     * Calcula la fecha de disponibilidad del certificado basándose en la configuración
     * del curso o la configuración global de la institución.
     *
     * @param string $fecha_finalizacion Fecha de finalización/aprobación (Y-m-d H:i:s o Y-m-d)
     * @param array $curso Datos del curso con campos de demora
     * @param array $config_global Configuración global de la institución
     * @return array ['disponible' => bool, 'fecha_disponible' => DateTime|null, 'mensaje' => string]
     */
    public static function calcularDisponibilidadCertificado(
        string $fecha_finalizacion,
        array $curso,
        array $config_global
    ): array {
        $ahora = new \DateTime();
        $fecha_fin = new \DateTime($fecha_finalizacion);

        // Determinar si usa configuración global o propia del curso
        $usar_global = ($curso['usar_demora_global'] ?? 1) == 1;

        if ($usar_global) {
            // Usar demora global de la institución (en horas)
            $demora_horas = $config_global['demora_certificado_horas'] ?? 24;
            if ($demora_horas == 0) {
                return [
                    'disponible' => true,
                    'fecha_disponible' => $fecha_fin,
                    'mensaje' => 'Disponible inmediatamente'
                ];
            }
            $fecha_disponible = clone $fecha_fin;
            $fecha_disponible->modify("+{$demora_horas} hours");
        } else {
            // Usar configuración propia del curso
            $demora_tipo = $curso['demora_tipo'] ?? 'inmediato';
            $demora_valor = $curso['demora_certificado_horas'] ?? 0;
            $demora_fecha = $curso['demora_fecha'] ?? null;

            switch ($demora_tipo) {
                case 'inmediato':
                    return [
                        'disponible' => true,
                        'fecha_disponible' => $fecha_fin,
                        'mensaje' => 'Disponible inmediatamente'
                    ];

                case 'horas':
                    $fecha_disponible = clone $fecha_fin;
                    $fecha_disponible->modify("+{$demora_valor} hours");
                    break;

                case 'dias':
                    $fecha_disponible = clone $fecha_fin;
                    $fecha_disponible->modify("+{$demora_valor} days");
                    break;

                case 'meses':
                    $fecha_disponible = clone $fecha_fin;
                    $fecha_disponible->modify("+{$demora_valor} months");
                    break;

                case 'fecha':
                    if (!$demora_fecha) {
                        return [
                            'disponible' => true,
                            'fecha_disponible' => $fecha_fin,
                            'mensaje' => 'Disponible inmediatamente (fecha no configurada)'
                        ];
                    }
                    $fecha_disponible = new \DateTime($demora_fecha);
                    break;

                default:
                    return [
                        'disponible' => true,
                        'fecha_disponible' => $fecha_fin,
                        'mensaje' => 'Disponible inmediatamente'
                    ];
            }
        }

        $disponible = $ahora >= $fecha_disponible;

        return [
            'disponible' => $disponible,
            'fecha_disponible' => $fecha_disponible,
            'mensaje' => $disponible ? 'Disponible' : 'Estará disponible el ' . $fecha_disponible->format('d/m/Y H:i')
        ];
    }
}
