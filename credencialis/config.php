<?php
/**
 * Configuración de Base de Datos - Sistema CREDENCIALIS
 * VERUMax - Credenciales de Membresía Verificables
 * Versión: 1.0.0
 *
 * NOTA: Credencialis usa el mismo motor de certificatum para:
 * - Generación de QR
 * - Validación de códigos
 * - Almacenamiento de validaciones
 *
 * La diferencia es que los datos de credenciales vienen de la tabla miembros
 * en verumax_nexus, no de cursos/estudiantes.
 */

// =====================================================
// CARGAR SERVICIOS PSR-4 Y CONFIGURACIÓN DE ENTORNO
// =====================================================
require_once __DIR__ . '/../env_loader.php';

use VERUMax\Services\DatabaseService;
use VERUMax\Services\MemberService;
use VERUMax\Services\CertificateService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\QRCodeService;

// =====================================================
// FUNCIONES DE CONEXIÓN Y UTILIDAD PARA CREDENCIALES
// =====================================================

/**
 * Obtiene una conexión PDO a la base de datos de miembros (nexus)
 *
 * @return PDO Conexión a la base de datos
 * @throws Exception Si falla la conexión
 */
function getCredencialisDBConnection() {
    return DatabaseService::get('nexus');
}

/**
 * Obtiene los datos de credencial de un miembro por institución y DNI
 *
 * @param string $institucion Código de la institución (ej: 'sajur')
 * @param string $dni Identificador principal del miembro
 * @return array|null Array con los datos del miembro y credencial
 */
function obtenerCredencialMiembro($institucion, $dni) {
    return MemberService::getCredentialData($institucion, $dni);
}

/**
 * Genera o recupera el código de validación para una credencial
 *
 * @param string $institucion Código de la institución
 * @param string $dni Identificador del miembro
 * @param string $tipo_credencial Tipo de credencial (credentialis)
 * @return string Código de validación (formato: VALID-xxxxxxxxxxxxx)
 */
function generarCodigoValidacionCredencial($institucion, $dni, $tipo_credencial = 'credentialis') {
    // Usa el mismo servicio de certificados pero con curso vacío
    return CertificateService::getValidationCode($institucion, $dni, '', $tipo_credencial);
}

/**
 * Registra una consulta de código de validación de credencial
 *
 * @param string $codigo_validacion Código que se consultó
 */
function registrarConsultaValidacionCredencial($codigo_validacion) {
    CertificateService::logValidation($codigo_validacion);
}

/**
 * Formatea una fecha de formato YYYY-MM-DD a DD/MM/YYYY
 *
 * @param string|null $fecha Fecha en formato YYYY-MM-DD
 * @return string Fecha formateada o texto por defecto
 */
function formatearFechaCredencial($fecha) {
    return CertificateService::formatDate($fecha);
}
?>
