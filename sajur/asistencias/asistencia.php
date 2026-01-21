<?php
require_once 'config.php';

// Configuración PHPMailer (LOCAL - en la misma carpeta)
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = getDBConnection();

// Variables de control
$codigo_formacion = isset($_GET['formacion']) ? trim($_GET['formacion']) : null;
$mostrarFormulario = true;
$registroExitoso = false;
$error = '';
$formacion = null;
$nombreCompleto = '';

// Verificar que se proporcionó código de formación
if (!$codigo_formacion) {
    $error = 'No se especificó el código de la formación. Por favor, accede mediante el enlace proporcionado.';
    $mostrarFormulario = false;
} else {
    // Buscar la formación
    try {
        $sql = "SELECT
                    f.id_formacion,
                    f.codigo_formacion,
                    f.nombre_formacion,
                    f.descripcion,
                    f.fecha_inicio,
                    f.hora_inicio,
                    f.hora_fin,
                    f.modalidad,
                    f.duracion
                FROM formaciones f
                WHERE f.codigo_formacion = :codigo
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':codigo' => $codigo_formacion]);
        $formacion = $stmt->fetch();

        if (!$formacion) {
            $error = 'La formación especificada no existe o no está disponible.';
            $mostrarFormulario = false;
        } else {
            // Verificar ventana de tiempo
            $disponibilidad = verificarDisponibilidadAsistencia($formacion);

            if (!$disponibilidad['disponible']) {
                $error = $disponibilidad['mensaje'];
                $mostrarFormulario = false;
            }
        }

    } catch (PDOException $e) {
        error_log("Error al buscar formación: " . $e->getMessage());
        $error = 'Error al cargar la formación. Por favor, intenta nuevamente.';
        $mostrarFormulario = false;
    }
}

// VERIFICACIÓN AJAX DE DNI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verificar_dni_ajax']) && $formacion) {
    header('Content-Type: application/json');

    $dni_verificar = limpiarDNI($_POST['dni'] ?? '');
    $resultado_verificacion = verificarAsistenciaDuplicada($formacion['id_formacion'], $dni_verificar);

    if ($resultado_verificacion['existe']) {
        $datos_previos = $resultado_verificacion['datos'];
        echo json_encode([
            'existe' => true,
            'datos' => [
                'nombres' => $datos_previos['nombres'],
                'apellidos' => $datos_previos['apellidos'],
                'dni' => $datos_previos['dni'],
                'email' => $datos_previos['correo_electronico'],
                'fecha_registro' => date('d/m/Y H:i', strtotime($datos_previos['fecha_registro']))
            ]
        ]);
    } else {
        echo json_encode(['existe' => false, 'datos' => null]);
    }

    exit;
}

// PROCESAMIENTO DEL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmacion']) && $mostrarFormulario && $formacion) {
    try {
        // Obtener y limpiar datos
        $nombres = limpiarNombreApellido($_POST['nombres'] ?? '');
        $apellidos = limpiarNombreApellido($_POST['apellidos'] ?? '');
        $dni = limpiarDNI($_POST['dni'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $confirmacion = isset($_POST['confirmacion']) && $_POST['confirmacion'] === '1';

        // Validaciones
        if (empty($nombres) || empty($apellidos) || empty($dni) || empty($email)) {
            throw new Exception('Todos los campos son obligatorios.');
        }

        if (!validarEmail($email)) {
            throw new Exception('El formato del correo electrónico no es válido.');
        }

        if (!$confirmacion) {
            throw new Exception('Debes confirmar que tus datos son correctos.');
        }

        if (mb_strlen($nombres) < 2 || mb_strlen($apellidos) < 2) {
            throw new Exception('Los nombres y apellidos deben tener al menos 2 caracteres.');
        }

        if (strlen($dni) < 5) {
            throw new Exception('El DNI/documento debe tener al menos 5 caracteres.');
        }

        // Verificar asistencia duplicada
        $verificacion = verificarAsistenciaDuplicada($formacion['id_formacion'], $dni);
        if ($verificacion['existe']) {
            throw new Exception('Ya registraste tu asistencia a esta formación anteriormente.');
        }

        // Registrar asistencia
        $resultado_registro = registrarAsistencia(
            $formacion['id_formacion'],
            $nombres,
            $apellidos,
            $dni,
            $email
        );

        if ($resultado_registro['exito']) {
            $nombreCompleto = $nombres . ' ' . $apellidos;
            $registroExitoso = true;
            $mostrarFormulario = false;

            // ENVIAR EMAIL DE CONFIRMACIÓN
            try {
                $mail = new PHPMailer(true);

                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host = MAIL_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = MAIL_USER;
                $mail->Password = MAIL_PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = MAIL_PORT;
                $mail->CharSet = 'UTF-8';

                // Destinatarios
                $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
                $mail->addAddress($email, $nombreCompleto);
                $mail->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);

                // Contenido del email
                $mail->isHTML(true);
                $mail->Subject = 'Confirmación de Asistencia - SAJUR';

                $html = "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; }
                        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                        .content { padding: 20px; }
                        .info-box { background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #7c3aed; }
                        .footer { background: #f8f9fa; padding: 15px; text-align: center; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h1>✓ Asistencia Confirmada</h1>
                        <p>SAJUR - Formación</p>
                    </div>

                    <div class='content'>
                        <p>Estimado/a <strong>{$nombreCompleto}</strong>,</p>

                        <p>Tu asistencia a la formación ha sido registrada exitosamente.</p>

                        <div class='info-box'>
                            <h3 style='color: #7c3aed; margin-top: 0;'>📋 Detalles de la Formación:</h3>
                            <p><strong>Título:</strong> " . htmlspecialchars($formacion['nombre_formacion']) . "</p>
                            <p><strong>Fecha:</strong> " . formatearFecha($formacion['fecha_inicio'], 'd/m/Y') . "</p>
                            <p><strong>Horario:</strong> " . formatearHora($formacion['hora_inicio']) . " - " . formatearHora($formacion['hora_fin']) . "</p>
                            <p><strong>Modalidad:</strong> " . htmlspecialchars($formacion['modalidad']) . "</p>
                        </div>

                        <div class='info-box' style='background: #e0f2fe; border-left-color: #0284c7;'>
                            <h3 style='color: #0284c7; margin-top: 0;'>👤 Datos Registrados:</h3>
                            <p><strong>Nombre completo:</strong> {$nombreCompleto}</p>
                            <p><strong>DNI:</strong> {$dni}</p>
                            <p><strong>Email:</strong> {$email}</p>
                            <p><strong>Hora de registro:</strong> " . date('d/m/Y H:i') . " hs</p>
                        </div>

                        <div class='info-box' style='background: #fef3c7; border-left-color: #f59e0b;'>
                            <h3 style='color: #d97706; margin-top: 0;'>⚠️ Importante:</h3>
                            <ul style='margin: 10px 0;'>
                                <li>Verifica que tus datos sean correctos</li>
                                <li>Si detectas algún error, contacta a: <strong>formacion@sajur.org</strong></li>
                                <li>Guarda este correo como comprobante de tu registro</li>
                            </ul>
                        </div>

                        <div class='info-box' style='background: #d1fae5; border-left-color: #10b981;'>
                            <h3 style='color: #059669; margin-top: 0;'>🎓 Certificado de Asistencia</h3>
                            <p style='margin: 10px 0; color: #065f46;'>
                                En los próximos días recibirás por correo electrónico las instrucciones para descargar tu certificado de asistencia a esta formación.
                            </p>
                        </div>

                        <p style='margin-top: 30px;'>¡Gracias por tu participación!</p>

                        <p style='margin-top: 20px;'>
                            <strong>SAJUR - Formación</strong><br>
                            Sociedad Argentina de Justicia Restaurativa
                        </p>
                    </div>

                    <div class='footer'>
                        <p>Este correo fue enviado automáticamente. Para consultas, contacta: formacion@sajur.org</p>
                        <p>© " . date('Y') . " SAJUR - Sociedad Argentina de Justicia Restaurativa</p>
                    </div>
                </body>
                </html>";

                $mail->Body = $html;
                $mail->send();

            } catch (Exception $e) {
                error_log("Error enviando email de confirmación de asistencia: " . $e->getMessage());
                // No fallar el registro si el email falla
            }

        } else {
            throw new Exception($resultado_registro['mensaje']);
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Error en registro de asistencia: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia - SAJUR</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .form-container { background: white; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .field-uppercase { text-transform: uppercase; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-content { background: white; border-radius: 16px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); animation: modalSlideIn 0.3s ease; }
        @keyframes modalSlideIn { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .loader { border: 3px solid #f3f3f3; border-top: 3px solid #7c3aed; border-radius: 50%; width: 20px; height: 20px; animation: spin 1s linear infinite; display: inline-block; margin-left: 8px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .btn-loading { opacity: 0.7; pointer-events: none; cursor: wait !important; }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <?php if ($registroExitoso): ?>
            <!-- Mensaje de éxito -->
            <div class="max-w-2xl mx-auto">
                <div class="form-container rounded-xl p-8">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-12 h-12 text-green-600 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h2 class="text-2xl font-bold text-green-800">¡Asistencia Registrada!</h2>
                                <p class="text-green-700 mt-1">Tu participación ha sido confirmada exitosamente</p>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-lg border border-green-200 mb-4">
                            <h3 class="font-semibold text-green-800 mb-4 text-lg">Datos registrados:</h3>
                            <div class="space-y-2 text-green-700">
                                <p><strong>Nombre completo:</strong> <?= htmlspecialchars($nombreCompleto) ?></p>
                                <p><strong>Formación:</strong> <?= htmlspecialchars($formacion['nombre_formacion']) ?></p>
                                <p><strong>Fecha:</strong> <?= formatearFecha($formacion['fecha_inicio'], 'd/m/Y') ?></p>
                                <p><strong>Hora de registro:</strong> <?= date('H:i') ?> hs</p>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-blue-800 mb-2">📧 Email de Confirmación</h4>
                            <p class="text-blue-700 text-sm">Hemos enviado un email de confirmación con todos los detalles a tu correo electrónico.</p>
                        </div>

                        <div class="text-center mt-6">
                            <a href="https://www.sajur.org" class="inline-block bg-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-700 transition duration-300">
                                Volver a SAJUR
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif (!$mostrarFormulario): ?>
            <!-- Mensaje de error -->
            <div class="max-w-2xl mx-auto">
                <div class="form-container rounded-xl p-8">
                    <?php if ($formacion): ?>
                        <!-- Si hay datos de la formación, mostrarla de forma amigable -->
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-600 text-white rounded-full mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-800 mb-2">Registro de Asistencia</h1>

                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6 mt-4">
                                <h3 class="font-semibold text-purple-800 text-lg mb-2"><?= htmlspecialchars($formacion['nombre_formacion']) ?></h3>
                                <div class="text-purple-700 text-sm space-y-1">
                                    <p>📅 <?= formatearFecha($formacion['fecha_inicio'], 'd/m/Y') ?></p>
                                    <?php if (!empty($formacion['hora_inicio']) && !empty($formacion['hora_fin'])): ?>
                                        <p>🕐 <?= formatearHora($formacion['hora_inicio']) ?> - <?= formatearHora($formacion['hora_fin']) ?></p>
                                    <?php endif; ?>
                                    <p>📍 <?= ucfirst(htmlspecialchars($formacion['modalidad'])) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-6">
                        <div class="flex items-start mb-4">
                            <svg class="w-12 h-12 text-amber-600 mr-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h2 class="text-xl font-bold text-amber-800 mb-2">Registro No Disponible</h2>
                                <p class="text-amber-700 leading-relaxed"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>

                        <div class="bg-white border border-amber-200 rounded-lg p-4 mt-4">
                            <h4 class="font-semibold text-amber-800 mb-2">💡 Información importante:</h4>
                            <ul class="text-amber-700 text-sm space-y-1 list-disc list-inside">
                                <li>El registro se abre en el horario de inicio de la formación</li>
                                <li>Tendrás hasta 1 hora después de la finalización para registrarte</li>
                                <li>Guarda este enlace para acceder cuando esté disponible</li>
                            </ul>
                        </div>

                        <div class="text-center mt-6">
                            <a href="https://www.sajur.org" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition duration-300">
                                Ir a SAJUR
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Formulario de asistencia -->
            <div class="max-w-2xl mx-auto">
                <div class="form-container rounded-xl p-8">
                    <!-- Información de la Formación -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-600 text-white rounded-full mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">Registro de Asistencia</h1>
                        <p class="text-gray-600 mb-4">Confirma tu participación en la formación</p>

                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                            <h3 class="font-semibold text-purple-800 text-lg mb-2"><?= htmlspecialchars($formacion['nombre_formacion']) ?></h3>
                            <div class="text-purple-700 text-sm space-y-1">
                                <p>📅 <?= formatearFecha($formacion['fecha_inicio'], 'd/m/Y') ?></p>
                                <p>🕐 <?= formatearHora($formacion['hora_inicio']) ?> - <?= formatearHora($formacion['hora_fin']) ?></p>
                                <p>📍 <?= htmlspecialchars($formacion['modalidad']) ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form id="asistenciaForm" method="POST" class="space-y-6">
                        <!-- Nombres -->
                        <div>
                            <label for="nombres" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombres <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="nombres" name="nombres" required
                                   class="field-uppercase w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="JUAN CARLOS" maxlength="100">
                            <p class="text-xs text-gray-500 mt-1">Solo letras y espacios. Se guardará en MAYÚSCULAS.</p>
                        </div>

                        <!-- Apellidos -->
                        <div>
                            <label for="apellidos" class="block text-sm font-medium text-gray-700 mb-2">
                                Apellidos <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="apellidos" name="apellidos" required
                                   class="field-uppercase w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="PÉREZ GÓMEZ" maxlength="100">
                            <p class="text-xs text-gray-500 mt-1">Solo letras y espacios. Se guardará en MAYÚSCULAS.</p>
                        </div>

                        <!-- DNI -->
                        <div>
                            <label for="dni" class="block text-sm font-medium text-gray-700 mb-2">
                                DNI / Documento <span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="dni" name="dni" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="12345678 o 12345678-9" maxlength="50">
                            <p class="text-xs text-gray-500 mt-1">Sin puntos ni espacios. Solo números y guiones.</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Correo Electrónico <span class="text-red-600">*</span>
                            </label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                   placeholder="tu@email.com" maxlength="150">
                            <p class="text-xs text-gray-500 mt-1">Recibirás confirmación en este correo.</p>
                        </div>

                        <!-- Aviso importante -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <div class="text-yellow-800 text-sm">
                                    <p class="font-semibold mb-1">⚠️ Información Importante</p>
                                    <ul class="space-y-1">
                                        <li>• Asegúrate de que tu nombre y apellido estén escritos <strong>correctamente</strong></li>
                                        <li>• No podrás modificar los datos después del registro</li>
                                        <li>• Recibirás un email de confirmación</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de envío -->
                        <div class="text-center">
                            <button type="button" id="submitBtn" onclick="mostrarModalConfirmacion()"
                                    class="bg-purple-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-purple-700 transition duration-300">
                                <span id="submitBtnText">Registrar Asistencia</span>
                            </button>
                            <p class="text-gray-500 text-sm mt-4">
                                <span class="text-red-600">*</span> Campos obligatorios
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de asistencia previa -->
    <div id="modalAsistenciaPrevia" class="modal-overlay">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 bg-blue-500 text-white rounded-full mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h3 class="text-2xl font-bold text-gray-800 text-center mb-4">Ya Registraste tu Asistencia</h3>

                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <p class="text-gray-700 mb-3 font-medium">Tu participación ya fue confirmada anteriormente:</p>
                    <div class="space-y-2 text-gray-800" id="datosAsistenciaPrevia"></div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-yellow-800 text-sm font-medium mb-2">⚠️ ¿Detectaste algún error?</p>
                    <p class="text-yellow-700 text-xs">
                        Si los datos registrados no son correctos, contacta a: <strong>formacion@sajur.org</strong>
                    </p>
                </div>

                <div class="text-center">
                    <button type="button" onclick="cerrarModalAsistenciaPrevia()"
                            class="bg-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-700 transition duration-300">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div id="modalConfirmacion" class="modal-overlay">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 bg-orange-500 text-white rounded-full mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>

                <h3 class="text-2xl font-bold text-gray-800 text-center mb-4">Confirma tus Datos</h3>

                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <p class="text-gray-700 mb-3 font-medium">Por favor, verifica que tus datos sean correctos:</p>
                    <div class="space-y-2 text-gray-800">
                        <p><strong>Nombres:</strong> <span id="modal-nombres" class="text-purple-700"></span></p>
                        <p><strong>Apellidos:</strong> <span id="modal-apellidos" class="text-purple-700"></span></p>
                        <p><strong>DNI:</strong> <span id="modal-dni" class="text-purple-700"></span></p>
                        <p><strong>Email:</strong> <span id="modal-email" class="text-purple-700"></span></p>
                    </div>
                </div>

                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                    <p class="text-orange-800 text-sm font-medium mb-2">⚠️ Verificación Final</p>
                    <ul class="text-orange-700 text-xs space-y-1">
                        <li>✓ Asegúrate de que tu nombre esté escrito correctamente</li>
                        <li>✓ No podrás modificar los datos después del registro</li>
                    </ul>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button" id="btnRevisar" onclick="cerrarModal()"
                            class="flex-1 bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-semibold hover:bg-gray-400 transition duration-300">
                        Revisar Datos
                    </button>
                    <button type="button" id="btnConfirmar" onclick="confirmarYEnviar()"
                            class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-300">
                        <span id="btnConfirmarText">Confirmar y Registrar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validación en tiempo real
        document.getElementById('nombres').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑÜ\s]/gi, '').replace(/\s+/g, ' ');
        });

        document.getElementById('apellidos').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑÜ\s]/gi, '').replace(/\s+/g, ' ');
        });

        document.getElementById('dni').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9\-]/g, '').toUpperCase();
        });

        function validarCampos() {
            const nombres = document.getElementById('nombres').value.trim();
            const apellidos = document.getElementById('apellidos').value.trim();
            const dni = document.getElementById('dni').value.trim();
            const email = document.getElementById('email').value.trim();
            const errores = [];

            if (nombres.length < 2) errores.push('El nombre debe tener al menos 2 caracteres');
            if (apellidos.length < 2) errores.push('El apellido debe tener al menos 2 caracteres');
            if (dni.length < 5) errores.push('El DNI debe tener al menos 5 caracteres');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errores.push('El formato del email no es válido');

            return { valido: errores.length === 0, errores: errores };
        }

        function mostrarModalConfirmacion() {
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const validacion = validarCampos();

            if (!validacion.valido) {
                alert('Por favor corrige los siguientes errores:\n\n' + validacion.errores.join('\n'));
                return;
            }

            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
            submitBtnText.innerHTML = 'Verificando... <span class="loader"></span>';

            const dni = document.getElementById('dni').value.trim();
            const formData = new FormData();
            formData.append('verificar_dni_ajax', '1');
            formData.append('dni', dni);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
                submitBtnText.textContent = 'Registrar Asistencia';

                if (data.existe) {
                    mostrarModalAsistenciaPrevia(data.datos);
                } else {
                    document.getElementById('modal-nombres').textContent = document.getElementById('nombres').value;
                    document.getElementById('modal-apellidos').textContent = document.getElementById('apellidos').value;
                    document.getElementById('modal-dni').textContent = document.getElementById('dni').value;
                    document.getElementById('modal-email').textContent = document.getElementById('email').value;
                    document.getElementById('modalConfirmacion').classList.add('active');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
                submitBtnText.textContent = 'Registrar Asistencia';

                document.getElementById('modal-nombres').textContent = document.getElementById('nombres').value;
                document.getElementById('modal-apellidos').textContent = document.getElementById('apellidos').value;
                document.getElementById('modal-dni').textContent = document.getElementById('dni').value;
                document.getElementById('modal-email').textContent = document.getElementById('email').value;
                document.getElementById('modalConfirmacion').classList.add('active');
            });
        }

        function mostrarModalAsistenciaPrevia(datos) {
            document.getElementById('datosAsistenciaPrevia').innerHTML = `
                <p><strong>Nombre completo:</strong> ${datos.nombres} ${datos.apellidos}</p>
                <p><strong>DNI:</strong> ${datos.dni}</p>
                <p><strong>Email:</strong> ${datos.email}</p>
                <p><strong>Fecha de registro:</strong> ${datos.fecha_registro} hs</p>
            `;
            document.getElementById('modalAsistenciaPrevia').classList.add('active');
        }

        function cerrarModalAsistenciaPrevia() {
            document.getElementById('modalAsistenciaPrevia').classList.remove('active');
        }

        function cerrarModal() {
            document.getElementById('modalConfirmacion').classList.remove('active');
        }

        function confirmarYEnviar() {
            const btnConfirmar = document.getElementById('btnConfirmar');
            const btnRevisar = document.getElementById('btnRevisar');
            const btnConfirmarText = document.getElementById('btnConfirmarText');

            btnConfirmar.classList.add('btn-loading');
            btnConfirmar.disabled = true;
            btnRevisar.disabled = true;
            btnConfirmarText.innerHTML = 'Registrando... <span class="loader"></span>';

            const form = document.getElementById('asistenciaForm');
            const confirmacionInput = document.createElement('input');
            confirmacionInput.type = 'hidden';
            confirmacionInput.name = 'confirmacion';
            confirmacionInput.value = '1';
            form.appendChild(confirmacionInput);

            setTimeout(() => form.submit(), 300);
        }

        document.getElementById('modalConfirmacion').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });

        document.getElementById('modalAsistenciaPrevia').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalAsistenciaPrevia();
        });
    </script>
</body>
</html>
