<?php
/**
 * CertificateTemplateService
 *
 * Servicio para gestionar templates de certificados.
 * Permite seleccionar templates globales por curso con fallback
 * al sistema actual si no hay template asignado.
 *
 * @package VERUMax\Services
 * @version 1.0
 */

namespace VERUMax\Services;

use PDO;
use PDOException;

class CertificateTemplateService
{
    /**
     * Nombre de la base de datos de templates
     */
    private const DB_CERTIFI = 'certificatum';

    /**
     * Nombre de la base de datos académica
     */
    private const DB_ACADEMI = 'academicus';

    /**
     * Cache en memoria para templates
     * @var array
     */
    private static array $cache = [];

    /**
     * Obtiene la conexión a la base de datos de certificados
     *
     * @return PDO
     */
    private static function dbCertifi(): PDO
    {
        return DatabaseService::get(self::DB_CERTIFI);
    }

    /**
     * Obtiene la conexión a la base de datos académica
     *
     * @return PDO
     */
    private static function dbAcademi(): PDO
    {
        return DatabaseService::get(self::DB_ACADEMI);
    }

    // =========================================================================
    // MÉTODOS PÚBLICOS - CONSULTAS
    // =========================================================================

    /**
     * Obtiene todos los templates activos para una institución
     *
     * @param string|null $slugInstitucion Slug de la institución (null = solo globales)
     * @return array Lista de templates ordenados por 'orden'
     */
    public static function getAll(?string $slugInstitucion = null): array
    {
        try {
            // Mostrar templates globales (institucion IS NULL) + exclusivos de esta institución
            $sql = "
                SELECT
                    id_template,
                    slug,
                    nombre,
                    descripcion,
                    tipo_generador,
                    orientacion,
                    preview_url,
                    config,
                    tiene_imagen_fondo,
                    imagen_fondo_path,
                    institucion
                FROM certificatum_templates
                WHERE activo = 1
                AND (institucion IS NULL OR institucion = :slug)
                ORDER BY orden ASC
            ";

            $stmt = self::dbCertifi()->prepare($sql);
            $stmt->execute([':slug' => $slugInstitucion]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        } catch (PDOException $e) {
            error_log("CertificateTemplateService::getAll - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un template por su ID
     *
     * @param int $id ID del template
     * @return array|null Template o null si no existe
     */
    public static function getById(int $id): ?array
    {
        // Verificar cache
        $cacheKey = "template_{$id}";
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        try {
            $stmt = self::dbCertifi()->prepare("
                SELECT
                    id_template,
                    slug,
                    nombre,
                    descripcion,
                    tipo_generador,
                    orientacion,
                    preview_url,
                    config,
                    tiene_imagen_fondo,
                    imagen_fondo_path
                FROM certificatum_templates
                WHERE id_template = :id AND activo = 1
            ");
            $stmt->execute([':id' => $id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                self::$cache[$cacheKey] = $result;
                return $result;
            }

            return null;

        } catch (PDOException $e) {
            error_log("CertificateTemplateService::getById - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un template por su slug
     *
     * @param string $slug Slug del template
     * @return array|null Template o null si no existe
     */
    public static function getBySlug(string $slug): ?array
    {
        try {
            $stmt = self::dbCertifi()->prepare("
                SELECT
                    id_template,
                    slug,
                    nombre,
                    descripcion,
                    tipo_generador,
                    orientacion,
                    preview_url,
                    config,
                    tiene_imagen_fondo,
                    imagen_fondo_path
                FROM certificatum_templates
                WHERE slug = :slug AND activo = 1
            ");
            $stmt->execute([':slug' => $slug]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            error_log("CertificateTemplateService::getBySlug - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el template asignado a un curso
     *
     * @param int $idInstancia ID de la instancia/institución
     * @param string $codigoCurso Código del curso
     * @return array|null Template o NULL si usa sistema actual (fallback)
     */
    public static function getForCurso(int $idInstancia, string $codigoCurso): ?array
    {
        // DEBUG: almacenar info para diagnóstico
        self::$debug_info = [];

        try {
            // Verificar si la columna id_template existe antes de consultarla
            // Esto evita errores si la migración no se ha ejecutado
            $checkColumn = self::dbAcademi()->query("
                SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = 'verumax_academi'
                AND TABLE_NAME = 'cursos'
                AND COLUMN_NAME = 'id_template'
            ");
            $columnExists = (int)$checkColumn->fetchColumn() > 0;
            self::$debug_info['column_exists'] = $columnExists;

            if (!$columnExists) {
                // La columna no existe, usar fallback
                self::$debug_info['reason'] = 'column_not_exists';
                return null;
            }

            // Obtener id_template del curso
            $stmt = self::dbAcademi()->prepare("
                SELECT id_template
                FROM cursos
                WHERE id_instancia = :id_instancia
                AND codigo_curso = :codigo_curso
            ");
            $stmt->execute([
                ':id_instancia' => $idInstancia,
                ':codigo_curso' => $codigoCurso
            ]);

            $idTemplate = $stmt->fetchColumn();
            self::$debug_info['id_template_raw'] = $idTemplate;
            self::$debug_info['params'] = ['id_instancia' => $idInstancia, 'codigo_curso' => $codigoCurso];

            // Si no tiene template asignado, retornar NULL (fallback)
            if (!$idTemplate) {
                self::$debug_info['reason'] = 'no_template_assigned';
                return null;
            }

            // Obtener el template
            self::$debug_info['reason'] = 'template_found';
            return self::getById((int) $idTemplate);

        } catch (PDOException $e) {
            error_log("CertificateTemplateService::getForCurso - Error: " . $e->getMessage());
            self::$debug_info['reason'] = 'exception';
            self::$debug_info['error'] = $e->getMessage();
            return null;
        }
    }

    /**
     * Debug info del último getForCurso
     * @var array
     */
    private static array $debug_info = [];

    /**
     * Obtiene info de debug del último getForCurso
     * @return array
     */
    public static function getDebugInfo(): array
    {
        return self::$debug_info;
    }

    /**
     * Obtiene el template asignado a un curso por ID de curso
     *
     * @param int $idCurso ID del curso
     * @return array|null Template o NULL si usa sistema actual (fallback)
     */
    public static function getForCursoById(int $idCurso): ?array
    {
        try {
            $stmt = self::dbAcademi()->prepare("
                SELECT id_template
                FROM cursos
                WHERE id_curso = :id_curso
            ");
            $stmt->execute([':id_curso' => $idCurso]);

            $idTemplate = $stmt->fetchColumn();

            if (!$idTemplate) {
                return null;
            }

            return self::getById((int) $idTemplate);

        } catch (PDOException $e) {
            error_log("CertificateTemplateService::getForCursoById - Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene la configuración parseada de un template
     *
     * @param int $id ID del template
     * @return array Configuración como array asociativo
     */
    public static function getConfig(int $id): array
    {
        $template = self::getById($id);

        if (!$template || empty($template['config'])) {
            return [];
        }

        $config = json_decode($template['config'], true);

        return is_array($config) ? $config : [];
    }

    // =========================================================================
    // MÉTODOS PÚBLICOS - MODIFICACIÓN
    // =========================================================================

    /**
     * Actualiza el config JSON de un template
     *
     * @param int $idTemplate ID del template
     * @param string $configJson JSON de configuración del editor (vacío = NULL)
     * @return bool True si se actualizó correctamente
     */
    public static function updateConfig(int $idTemplate, string $configJson): bool
    {
        try {
            // Primero verificar que el template existe
            $check = self::dbCertifi()->prepare("SELECT id_template FROM certificatum_templates WHERE id_template = :id");
            $check->execute([':id' => $idTemplate]);
            if (!$check->fetch()) {
                throw new \Exception("Template ID $idTemplate no existe en la tabla");
            }

            // Si el JSON está vacío, guardar NULL (columna JSON no acepta string vacío)
            $configValue = empty(trim($configJson)) ? null : $configJson;

            $stmt = self::dbCertifi()->prepare("
                UPDATE certificatum_templates
                SET config = :config
                WHERE id_template = :id_template
            ");

            $result = $stmt->execute([
                ':config' => $configValue,
                ':id_template' => $idTemplate
            ]);

            // Limpiar cache
            self::clearCache();

            // Si execute fue exitoso, consideramos éxito (rowCount puede ser 0 si el valor no cambió)
            return $result;

        } catch (PDOException $e) {
            error_log("CertificateTemplateService::updateConfig - Error: " . $e->getMessage());
            throw new \Exception("Error BD: " . $e->getMessage());
        }
    }

    /**
     * Asigna un template a un curso
     *
     * @param int $idCurso ID del curso
     * @param int|null $idTemplate ID del template (NULL para usar sistema actual)
     * @return bool True si se asignó correctamente
     */
    public static function assignToCurso(int $idCurso, ?int $idTemplate): bool
    {
        try {
            $stmt = self::dbAcademi()->prepare("
                UPDATE cursos
                SET id_template = :id_template
                WHERE id_curso = :id_curso
            ");

            return $stmt->execute([
                ':id_template' => $idTemplate,
                ':id_curso' => $idCurso
            ]);

        } catch (PDOException $e) {
            error_log("CertificateTemplateService::assignToCurso - Error: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // MÉTODOS AUXILIARES
    // =========================================================================

    /**
     * Verifica si un curso tiene template asignado
     *
     * @param int $idCurso ID del curso
     * @return bool True si tiene template, False si usa fallback
     */
    public static function cursoHasTemplate(int $idCurso): bool
    {
        return self::getForCursoById($idCurso) !== null;
    }

    /**
     * Obtiene la ruta al archivo HTML del template
     *
     * @param string $slug Slug del template
     * @return string Ruta al archivo
     */
    public static function getTemplatePath(string $slug): string
    {
        return __DIR__ . '/../../../assets/templates/certificados/' . $slug . '/template.html';
    }

    /**
     * Obtiene la ruta al preview del template
     *
     * @param string $slug Slug del template
     * @return string Ruta relativa al preview
     */
    public static function getPreviewPath(string $slug): string
    {
        return '/assets/templates/certificados/' . $slug . '/preview.jpg';
    }

    /**
     * Verifica si un template existe y está activo
     *
     * @param int $id ID del template
     * @return bool
     */
    public static function exists(int $id): bool
    {
        return self::getById($id) !== null;
    }

    /**
     * Limpia el cache en memoria
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Obtiene el tipo de generador para un template
     *
     * @param int $id ID del template
     * @return string 'mpdf' o 'tcpdf'
     */
    public static function getGeneratorType(int $id): string
    {
        $template = self::getById($id);
        return $template['tipo_generador'] ?? 'mpdf';
    }

    /**
     * Obtiene la orientación de un template
     *
     * @param int $id ID del template
     * @return string 'landscape' o 'portrait'
     */
    public static function getOrientation(int $id): string
    {
        $template = self::getById($id);
        return $template['orientacion'] ?? 'landscape';
    }
}
