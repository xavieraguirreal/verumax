<?php
/**
 * Configuración de Base de Datos - Sistema CERTIFICATUM
 * VERUMax - Credenciales Verificadas
 * Versión: 1.0.0
 *
 * IMPORTANTE: Este archivo contiene las credenciales de acceso a la base de datos.
 * Base de datos dedicada para Certificatum (verumax_certifi).
 */

// =====================================================
// CARGAR SERVICIOS PSR-4 Y CONFIGURACIÓN DE ENTORNO
// =====================================================
require_once __DIR__ . '/../env_loader.php';

use VERUMax\Services\DatabaseService;
use VERUMax\Services\StudentService;
use VERUMax\Services\CertificateService;
use VERUMax\Services\LanguageService;

// =====================================================
// CONFIGURACIÓN DE BASE DE DATOS (ya configurada en env_loader.php)
// =====================================================
// NOTA: Las conexiones ya están configuradas en env_loader.php
// usando las credenciales del archivo .env (local o remoto)
/*
DatabaseService::configure('certificatum', [
    'host' => 'localhost',
    'user' => 'verumax_certifi',
    'password' => '/hPfiYd6xH',
    'database' => 'verumax_certifi',
]);
*/

// Constantes legacy (mantener para compatibilidad)
if (!defined('CERT_DB_HOST')) define('CERT_DB_HOST', env('CERTIFI_DB_HOST', 'localhost'));
if (!defined('CERT_DB_USER')) define('CERT_DB_USER', env('CERTIFI_DB_USER', 'root'));
if (!defined('CERT_DB_PASSWORD')) define('CERT_DB_PASSWORD', env('CERTIFI_DB_PASS', ''));
if (!defined('CERT_DB_NAME')) define('CERT_DB_NAME', env('CERTIFI_DB_NAME', 'verumax_certifi'));

// =====================================================
// FUNCIONES DE CONEXIÓN Y UTILIDAD
// =====================================================

/**
 * Obtiene una conexión PDO a la base de datos
 * REFACTORIZADO: Ahora usa DatabaseService internamente
 *
 * @return PDO Conexión a la base de datos
 * @throws Exception Si falla la conexión
 */
function getCertDBConnection() {
    return DatabaseService::get('certificatum');
}

/**
 * Obtiene todos los cursos de un estudiante por institución y DNI
 * REFACTORIZADO: Usa StudentService
 *
 * @param string $institucion Código de la institución (ej: 'sajur')
 * @param string $dni DNI del estudiante
 * @return array|null Array con los datos del estudiante y sus cursos
 */
function obtenerCursosEstudiante($institucion, $dni) {
    return StudentService::getCourses($institucion, $dni);
}

/**
 * Obtiene un curso específico de un estudiante
 * REFACTORIZADO: Usa StudentService
 *
 * @param string $institucion Código de la institución
 * @param string $dni DNI del estudiante
 * @param string $codigo_curso Código del curso
 * @return array|null Datos del curso o null si no existe
 */
function obtenerCursoEstudiante($institucion, $dni, $codigo_curso) {
    return StudentService::getCourse($institucion, $dni, $codigo_curso);
}

/**
 * Genera o recupera el código de validación para un certificado
 * REFACTORIZADO: Usa CertificateService
 *
 * @param string $institucion Código de la institución
 * @param string $dni DNI del estudiante
 * @param string $codigo_curso Código del curso
 * @param string $tipo_documento Tipo de documento (certificado_aprobacion, constancia, etc.)
 * @return string Código de validación (formato: VALID-xxxxxxxxxxxxx)
 */
function generarCodigoValidacion($institucion, $dni, $codigo_curso, $tipo_documento = 'certificado_aprobacion') {
    return CertificateService::getValidationCode($institucion, $dni, $codigo_curso, $tipo_documento);
}

/**
 * Registra una consulta de código de validación
 * REFACTORIZADO: Usa CertificateService
 *
 * @param string $codigo_validacion Código que se consultó
 */
function registrarConsultaValidacion($codigo_validacion) {
    CertificateService::logValidation($codigo_validacion);
}

/**
 * Formatea una fecha de formato YYYY-MM-DD a DD/MM/YYYY
 * REFACTORIZADO: Usa CertificateService
 *
 * @param string|null $fecha Fecha en formato YYYY-MM-DD
 * @return string Fecha formateada o texto por defecto
 */
function formatearFechaCert($fecha) {
    return CertificateService::formatDate($fecha);
}

?>
