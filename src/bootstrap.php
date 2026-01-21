<?php
/**
 * Bootstrap - Inicialización de VERUMax Services
 *
 * Incluir este archivo para acceder a los servicios PSR-4.
 * Uso: require_once __DIR__ . '/src/bootstrap.php';
 *
 * Este archivo:
 * 1. Carga el autoloader de Composer
 * 2. Proporciona funciones helper para compatibilidad con código legacy
 *
 * @package VERUMax
 */

// Evitar inclusión múltiple
if (defined('VERUMAX_BOOTSTRAP_LOADED')) {
    return;
}
define('VERUMAX_BOOTSTRAP_LOADED', true);

// Cargar autoloader de Composer
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    throw new RuntimeException(
        'Composer autoloader no encontrado. Ejecutar: composer install'
    );
}
require_once $autoloader;

// Importar servicios para uso global
use VERUMax\Services\ValidationCodeService;
use VERUMax\Services\QRCodeService;
use VERUMax\Services\DatabaseService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\StudentService;
use VERUMax\Services\CertificateService;
use VERUMax\Core\Config;

// Configurar zona horaria
DatabaseService::setTimezone('America/Argentina/Buenos_Aires');

// ============================================================================
// FUNCIONES HELPER PARA COMPATIBILIDAD CON CÓDIGO LEGACY
// ============================================================================

/**
 * Genera código de validación (reemplazo directo del código inline)
 *
 * Antes:
 *   $codigo = "VALID-" . strtoupper(substr(md5($dni . $curso_id), 0, 12));
 *
 * Ahora:
 *   $codigo = verumax_generate_code($dni, $curso_id);
 *
 * @param string $dni DNI del estudiante
 * @param string $courseId ID del curso
 * @return string Código de validación
 */
function verumax_generate_code(string $dni, string $courseId): string
{
    return ValidationCodeService::generate($dni, $courseId);
}

/**
 * Genera URL de código QR para validación
 *
 * Antes:
 *   $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($url);
 *
 * Ahora:
 *   $qr_url = verumax_qr_url($codigo_validacion);
 *
 * @param string $validationCode Código de validación
 * @param int $size Tamaño del QR (default 100)
 * @return string URL del QR
 */
function verumax_qr_url(string $validationCode, int $size = 100): string
{
    return QRCodeService::forValidation($validationCode, $size);
}

/**
 * Genera URL de QR directamente desde DNI y curso
 *
 * @param string $dni DNI del estudiante
 * @param string $courseId ID del curso
 * @param int $size Tamaño del QR
 * @return string URL del QR
 */
function verumax_qr_for_certificate(string $dni, string $courseId, int $size = 100): string
{
    return QRCodeService::forCertificate($dni, $courseId, $size);
}

/**
 * Genera tag HTML de QR para validación
 *
 * Antes:
 *   <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo urlencode($url); ?>" alt="Código QR">
 *
 * Ahora:
 *   <?php echo verumax_qr_html($codigo_validacion); ?>
 *
 * @param string $validationCode Código de validación
 * @param int $size Tamaño del QR
 * @return string Tag HTML <img>
 */
function verumax_qr_html(string $validationCode, int $size = 100): string
{
    return QRCodeService::validationHtml($validationCode, $size);
}

/**
 * Valida el formato de un código
 *
 * @param string $code Código a validar
 * @return bool True si el formato es válido
 */
function verumax_is_valid_code(string $code): bool
{
    return ValidationCodeService::isValidFormat($code);
}

/**
 * Sanitiza un código ingresado por usuario
 *
 * @param string $code Código a sanitizar
 * @return string Código limpio y en mayúsculas
 */
function verumax_sanitize_code(string $code): string
{
    return ValidationCodeService::sanitize($code);
}

/**
 * Obtiene la URL de validación completa
 *
 * @param string $code Código de validación
 * @return string URL completa
 */
function verumax_validation_url(string $code): string
{
    return ValidationCodeService::getValidationUrl($code);
}

// ============================================================================
// FUNCIONES HELPER PARA BASE DE DATOS
// ============================================================================

/**
 * Configura una conexión de base de datos
 *
 * @param string $name Nombre identificador
 * @param string $host Host del servidor
 * @param string $user Usuario
 * @param string $password Contraseña
 * @param string $database Nombre de la base de datos
 * @return void
 */
function verumax_db_configure(string $name, string $host, string $user, string $password, string $database): void
{
    DatabaseService::configure($name, [
        'host' => $host,
        'user' => $user,
        'password' => $password,
        'database' => $database,
    ]);
}

/**
 * Obtiene una conexión PDO
 *
 * @param string $name Nombre de la BD configurada
 * @return PDO
 */
function verumax_db(string $name = 'default'): PDO
{
    return DatabaseService::get($name);
}

/**
 * Ejecuta SELECT y retorna todos los resultados
 *
 * @param string $name Nombre de la BD
 * @param string $sql Consulta SQL
 * @param array $params Parámetros
 * @return array
 */
function verumax_db_fetch_all(string $name, string $sql, array $params = []): array
{
    return DatabaseService::fetchAll($name, $sql, $params);
}

/**
 * Ejecuta SELECT y retorna un registro
 *
 * @param string $name Nombre de la BD
 * @param string $sql Consulta SQL
 * @param array $params Parámetros
 * @return array|null
 */
function verumax_db_fetch_one(string $name, string $sql, array $params = []): ?array
{
    return DatabaseService::fetchOne($name, $sql, $params);
}

/**
 * Ejecuta INSERT/UPDATE/DELETE
 *
 * @param string $name Nombre de la BD
 * @param string $sql Consulta SQL
 * @param array $params Parámetros
 * @return int Filas afectadas
 */
function verumax_db_execute(string $name, string $sql, array $params = []): int
{
    return DatabaseService::execute($name, $sql, $params);
}

// ============================================================================
// FUNCIONES HELPER PARA INSTITUCIONES
// ============================================================================

/**
 * Obtiene la configuración completa de una institución
 *
 * @param string $slug Slug de la institución
 * @return array|null
 */
function verumax_institution_config(string $slug): ?array
{
    return InstitutionService::getConfig($slug);
}

/**
 * Obtiene un valor específico de configuración de institución
 *
 * @param string $slug Slug de la institución
 * @param string $key Clave de configuración
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function verumax_institution_get(string $slug, string $key, $default = null)
{
    return InstitutionService::get($slug, $key, $default);
}

/**
 * Verifica si Identitas está activo para una institución
 *
 * @param string $slug Slug de la institución
 * @return bool
 */
function verumax_is_identitas_active(string $slug): bool
{
    return InstitutionService::isIdentitasActive($slug);
}

/**
 * Verifica si Certificatum está activo para una institución
 *
 * @param string $slug Slug de la institución
 * @return bool
 */
function verumax_is_certificatum_active(string $slug): bool
{
    return InstitutionService::isCertificatumActive($slug);
}

/**
 * Obtiene los colores de una institución
 *
 * @param string $slug Slug de la institución
 * @return array ['primario', 'secundario', 'acento']
 */
function verumax_institution_colors(string $slug): array
{
    return InstitutionService::getColors($slug);
}

// ============================================================================
// FUNCIONES HELPER PARA ESTUDIANTES
// ============================================================================

/**
 * Obtiene los cursos de un estudiante
 *
 * @param string $institution Código de institución
 * @param string $dni DNI del estudiante
 * @return array|null
 */
function verumax_student_courses(string $institution, string $dni): ?array
{
    return StudentService::getCourses($institution, $dni);
}

/**
 * Obtiene un curso específico de un estudiante
 *
 * @param string $institution Código de institución
 * @param string $dni DNI del estudiante
 * @param string $courseCode Código del curso
 * @return array|null
 */
function verumax_student_course(string $institution, string $dni, string $courseCode): ?array
{
    return StudentService::getCourse($institution, $dni, $courseCode);
}

/**
 * Verifica si un estudiante existe
 *
 * @param string $institution Código de institución
 * @param string $dni DNI del estudiante
 * @return bool
 */
function verumax_student_exists(string $institution, string $dni): bool
{
    return StudentService::exists($institution, $dni);
}

// ============================================================================
// FUNCIONES HELPER PARA CERTIFICADOS
// ============================================================================

/**
 * Genera o recupera código de validación de un certificado
 *
 * @param string $institution Código de institución
 * @param string $dni DNI del estudiante
 * @param string $courseCode Código del curso
 * @param string $documentType Tipo de documento
 * @return string Código de validación
 */
function verumax_certificate_code(
    string $institution,
    string $dni,
    string $courseCode,
    string $documentType = 'certificado_aprobacion'
): string {
    return CertificateService::getValidationCode($institution, $dni, $courseCode, $documentType);
}

/**
 * Registra una consulta de validación
 *
 * @param string $code Código consultado
 * @return bool
 */
function verumax_log_validation(string $code): bool
{
    return CertificateService::logValidation($code);
}

/**
 * Formatea fecha al formato argentino DD/MM/YYYY
 *
 * @param string|null $date Fecha en formato YYYY-MM-DD
 * @param string $default Valor por defecto
 * @return string
 */
function verumax_format_date(?string $date, string $default = 'N/A'): string
{
    return CertificateService::formatDate($date, $default);
}
