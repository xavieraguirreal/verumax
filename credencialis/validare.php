<?php
/**
 * CREDENCIALIS - Validación de Códigos QR
 * Sistema VERUMax
 * Versión: 1.0
 *
 * Recibe un código de validación y muestra la información verificada de la credencial.
 * Reutiliza el sistema de validación de Certificatum.
 */

// Cargar configuración
require_once __DIR__ . '/config.php';

use VERUMax\Services\CertificateService;
use VERUMax\Services\InstitutionService;
use VERUMax\Services\LanguageService;
use VERUMax\Services\MemberService;

// ============================================================================
// OBTENER CÓDIGO DE VALIDACIÓN
// ============================================================================

$codigo = $_GET['codigo'] ?? null;

if (!$codigo) {
    // Mostrar formulario de ingreso de código
    include __DIR__ . '/../certificatum/templates/validare_form.php';
    exit;
}

// ============================================================================
// BUSCAR VALIDACIÓN EN BASE DE DATOS
// ============================================================================

$validacion = CertificateService::findByValidationCode($codigo);

if (!$validacion) {
    // Código no encontrado
    $error = 'not_found';
    include __DIR__ . '/../certificatum/templates/verificatio_error.php';
    exit;
}

// Registrar consulta
CertificateService::logValidation($codigo);

// ============================================================================
// OBTENER DATOS COMPLETOS
// ============================================================================

$institucion = $validacion['institucion'];
$dni = $validacion['dni'];
$tipo_documento = $validacion['tipo_documento'];

// Si no es una credencial, redirigir a certificatum
if ($tipo_documento !== 'credentialis') {
    header('Location: ../certificatum/validare.php?codigo=' . urlencode($codigo));
    exit;
}

// Cargar configuración de institución
$instance_config = InstitutionService::getConfig($institucion);

if (!$instance_config) {
    $error = 'institution_not_found';
    include __DIR__ . '/../certificatum/templates/verificatio_error.php';
    exit;
}

// Inicializar idioma
$lang = $_GET['lang'] ?? null;
LanguageService::init($institucion, $lang);

// Obtener datos del miembro
$miembro = MemberService::getCredentialData($institucion, $dni);

if (!$miembro) {
    $error = 'member_not_found';
    include __DIR__ . '/../certificatum/templates/verificatio_error.php';
    exit;
}

// Redirigir a la página de verificación pública
header('Location: verificatio.php?codigo=' . urlencode($codigo) . '&lang=' . urlencode(LanguageService::getCurrentLanguage()));
exit;
