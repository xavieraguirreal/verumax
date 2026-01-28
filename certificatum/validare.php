<?php
/**
 * Sistema de Validación de Certificados - CERTIFICATUM
 * Valida códigos QR y redirige a la página de verificación
 * Versión: 2.2 - Agregado logging detallado (IP, user-agent, referer)
 */

// 1. Cargar configuración y servicios PSR-4
require_once 'config.php';

use VERUMax\Services\CertificateService;

// 2. Obtener código de validación
$codigo_recibido = $_POST['codigo'] ?? $_GET['codigo'] ?? null;

// Si no hay código, mostrar formulario de ingreso
if (!$codigo_recibido) {
    include __DIR__ . '/templates/validare_form.php';
    exit;
}

// Sanitizar código usando el servicio
$codigo_recibido = verumax_sanitize_code($codigo_recibido);

// 3. Buscar el código usando CertificateService
$resultado = CertificateService::getCodeInfo($codigo_recibido);

if ($resultado) {
    // Código encontrado - registrar la consulta (método legacy)
    CertificateService::logValidation($codigo_recibido);

    // Registrar con detalles completos (IP, user-agent, etc.)
    CertificateService::logValidationDetailed(
        $codigo_recibido,
        true,  // exitoso
        $resultado['institucion'],
        $resultado['tipo_documento'] ?? null
    );

    // Detectar tipo de documento
    $codigo_curso = $resultado['codigo_curso'];
    $participacion_id = null;
    $es_credencial = false;
    $id_miembro = null;

    // Detectar si es credencial (formato: credencial_ID)
    if (preg_match('/^credencial_(\d+)$/', $codigo_curso, $matches)) {
        $es_credencial = true;
        $id_miembro = $matches[1];
    }
    // Detectar si es documento de docente (formato: CODIGO_docente_ID)
    elseif (preg_match('/^(.+)_docente_(\d+)$/', $codigo_curso, $matches)) {
        $codigo_curso = $matches[1];  // Código del curso real
        $participacion_id = $matches[2];  // ID de participación
    }

    // Redirigir según tipo de documento
    if ($es_credencial) {
        // Credencial de socio/miembro
        $redirect_url = 'verificatio.php?institutio=' . urlencode($resultado['institucion']) .
               '&documentum=' . urlencode($resultado['dni']) .
               '&genus=credentialis' .
               '&id_miembro=' . urlencode($id_miembro);
    } else {
        // Certificado o constancia tradicional
        $redirect_url = 'verificatio.php?institutio=' . urlencode($resultado['institucion']) .
               '&documentum=' . urlencode($resultado['dni']) .
               '&cursus=' . urlencode($codigo_curso);

        // Agregar participacion si es documento de docente
        if ($participacion_id) {
            $redirect_url .= '&participacion=' . urlencode($participacion_id);
        }
    }

    header('Location: ' . $redirect_url);
    exit;
}

// 4. Si no se encontró el código, registrar intento fallido y mostrar error
CertificateService::logValidationDetailed(
    $codigo_recibido,
    false,  // no exitoso
    null,   // institución desconocida
    null    // tipo desconocido
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error de Validación - Certificatum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="text-center bg-white p-8 sm:p-12 rounded-2xl shadow-2xl max-w-md mx-auto">
        <!-- Icono de error -->
        <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-6">
            <i data-lucide="alert-circle" class="w-12 h-12 text-red-600"></i>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-red-600 mb-3">Documento No Válido</h1>
        <p class="text-gray-700 text-base sm:text-lg leading-relaxed mb-6">
            El código de validación no fue encontrado en nuestros registros o es incorrecto.
        </p>

        <!-- Código ingresado -->
        <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4 mb-6">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Código ingresado:</p>
            <p class="font-mono text-lg font-bold text-gray-800"><?php echo htmlspecialchars($codigo_recibido); ?></p>
        </div>

        <!-- Información adicional -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 text-left">
            <div class="flex items-start">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-1">Posibles causas:</p>
                    <ul class="list-disc list-inside space-y-1 text-blue-700">
                        <li>El código fue escrito incorrectamente</li>
                        <li>El certificado no ha sido emitido</li>
                        <li>El código ha expirado o fue revocado</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="javascript:history.back()"
               class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Volver
            </a>
            <a href="/certificatum/"
               class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold">
                <i data-lucide="home" class="w-4 h-4"></i>
                Inicio
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
<?php
?>
