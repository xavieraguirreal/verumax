<?php
/**
 * API de Contacto - Landing Pages
 * Recibe formularios de contacto y envía emails via SendGrid
 *
 * @version 1.0.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Cargar configuración
require_once __DIR__ . '/../config.php';

// Cargar config de SendGrid
$sendgridConfig = include __DIR__ . '/../config/sendgrid.php';
$apiKey = $sendgridConfig['api_key'] ?? '';

if (empty($apiKey) || $apiKey === 'TU_API_KEY_AQUI') {
    error_log('API Contact: SendGrid API key not configured');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Configuración de email no disponible']);
    exit;
}

// Obtener datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Validar campos requeridos
$required = ['nombre', 'email', 'organizacion', 'tipo', 'producto'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Campo requerido: $field"]);
        exit;
    }
}

// Validar email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email inválido']);
    exit;
}

// Sanitizar datos
$nombre = htmlspecialchars(strip_tags($data['nombre']));
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$organizacion = htmlspecialchars(strip_tags($data['organizacion']));
$tipo = htmlspecialchars(strip_tags($data['tipo']));
$socios = htmlspecialchars(strip_tags($data['socios'] ?? 'No especificado'));
$mensaje = htmlspecialchars(strip_tags($data['mensaje'] ?? ''));
$producto = htmlspecialchars(strip_tags($data['producto']));
$lang = $data['lang'] ?? 'es_AR';

// Mapear tipos de organización
$tiposMap = [
    'club' => 'Club Deportivo/Social',
    'cooperativa' => 'Cooperativa',
    'mutual' => 'Mutual',
    'asociacion' => 'Asociación Civil',
    'colegio' => 'Colegio Profesional',
    'evento' => 'Organizador de Eventos',
    'otro' => 'Otro'
];
$tipoDisplay = $tiposMap[$tipo] ?? $tipo;

// Preparar email para el equipo VERUMax
$fechaHora = date('d/m/Y H:i');
$asuntoEquipo = "🎯 Nuevo Lead $producto: $organizacion";

$htmlEquipo = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
    <div style='background: linear-gradient(135deg, #0891b2, #0e7490); padding: 20px; text-align: center;'>
        <h1 style='color: white; margin: 0;'>Nuevo Lead - $producto</h1>
    </div>

    <div style='padding: 30px; background: #f8f9fa;'>
        <h2 style='color: #0891b2; margin-top: 0;'>Datos del Contacto</h2>

        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold; width: 40%;'>Nombre:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$nombre</td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Email:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'><a href='mailto:$email'>$email</a></td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Organización:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$organizacion</td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Tipo:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$tipoDisplay</td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Cantidad de Socios:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$socios</td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Producto:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'><strong>$producto</strong></td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Idioma:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$lang</td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Fecha/Hora:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$fechaHora</td>
            </tr>
        </table>

        " . ($mensaje ? "<h3 style='color: #0891b2; margin-top: 20px;'>Mensaje:</h3><p style='background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #0891b2;'>$mensaje</p>" : "") . "

        <div style='margin-top: 30px; text-align: center;'>
            <a href='mailto:$email?subject=Re: Consulta $producto - $organizacion' style='background: #0891b2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block;'>Responder al Lead</a>
        </div>
    </div>

    <div style='background: #1f2937; color: #9ca3af; padding: 15px; text-align: center; font-size: 12px;'>
        VERUMax - Sistema de Leads Automatizado
    </div>
</div>
";

// Preparar email de confirmación para el usuario
$asuntoUsuario = $lang === 'pt_BR'
    ? "Recebemos sua consulta - VERUMax $producto"
    : "Recibimos tu consulta - VERUMax $producto";

$htmlUsuario = $lang === 'pt_BR' ? "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
    <div style='background: linear-gradient(135deg, #0891b2, #0e7490); padding: 30px; text-align: center;'>
        <h1 style='color: white; margin: 0;'>Obrigado por entrar em contato!</h1>
    </div>

    <div style='padding: 30px; background: #f8f9fa;'>
        <p style='font-size: 16px;'>Olá <strong>$nombre</strong>,</p>

        <p>Recebemos sua consulta sobre <strong>$producto</strong> para <strong>$organizacion</strong>.</p>

        <p>Nossa equipe entrará em contato em breve para agendar uma demonstração personalizada e responder todas as suas perguntas.</p>

        <div style='background: white; border-radius: 8px; padding: 20px; margin: 20px 0; border-left: 4px solid #0891b2;'>
            <p style='margin: 0; color: #666;'><strong>Tempo de resposta estimado:</strong> 24-48 horas úteis</p>
        </div>

        <p>Enquanto isso, você pode conhecer mais sobre nossas soluções em <a href='https://verumax.com' style='color: #0891b2;'>verumax.com</a></p>

        <p style='margin-top: 30px;'>Atenciosamente,<br><strong>Equipe VERUMax</strong></p>
    </div>

    <div style='background: #1f2937; color: #9ca3af; padding: 15px; text-align: center; font-size: 12px;'>
        © " . date('Y') . " VERUMax - Soluções Digitais para Organizações
    </div>
</div>
" : "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
    <div style='background: linear-gradient(135deg, #0891b2, #0e7490); padding: 30px; text-align: center;'>
        <h1 style='color: white; margin: 0;'>¡Gracias por contactarnos!</h1>
    </div>

    <div style='padding: 30px; background: #f8f9fa;'>
        <p style='font-size: 16px;'>Hola <strong>$nombre</strong>,</p>

        <p>Recibimos tu consulta sobre <strong>$producto</strong> para <strong>$organizacion</strong>.</p>

        <p>Nuestro equipo se pondrá en contacto a la brevedad para coordinar una demo personalizada y responder todas tus preguntas.</p>

        <div style='background: white; border-radius: 8px; padding: 20px; margin: 20px 0; border-left: 4px solid #0891b2;'>
            <p style='margin: 0; color: #666;'><strong>Tiempo estimado de respuesta:</strong> 24-48 horas hábiles</p>
        </div>

        <p>Mientras tanto, podés conocer más sobre nuestras soluciones en <a href='https://verumax.com' style='color: #0891b2;'>verumax.com</a></p>

        <p style='margin-top: 30px;'>Saludos cordiales,<br><strong>Equipo VERUMax</strong></p>
    </div>

    <div style='background: #1f2937; color: #9ca3af; padding: 15px; text-align: center; font-size: 12px;'>
        © " . date('Y') . " VERUMax - Soluciones Digitales para Organizaciones
    </div>
</div>
";

// Función para enviar email via SendGrid
function sendEmail($apiKey, $to, $toName, $subject, $html, $from = 'notificaciones@verumax.com', $fromName = 'VERUMax') {
    $payload = [
        'personalizations' => [[
            'to' => [['email' => $to, 'name' => $toName]]
        ]],
        'from' => ['email' => $from, 'name' => $fromName],
        'subject' => $subject,
        'content' => [['type' => 'text/html', 'value' => $html]]
    ];

    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
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
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("SendGrid cURL error: $error");
        return false;
    }

    return $httpCode === 202;
}

// Enviar emails
$emailEquipo = sendEmail($apiKey, 'proyectos@verumax.com', 'VERUMax Proyectos', $asuntoEquipo, $htmlEquipo);
$emailUsuario = sendEmail($apiKey, $email, $nombre, $asuntoUsuario, $htmlUsuario);

if (!$emailEquipo) {
    error_log("API Contact: Error enviando email al equipo para lead $email");
}

if (!$emailUsuario) {
    error_log("API Contact: Error enviando confirmación a $email");
}

// Log del lead
error_log("API Contact: Lead recibido - $producto - $organizacion - $email");

// Respuesta exitosa (incluso si algún email falló, el lead fue registrado)
echo json_encode([
    'success' => true,
    'message' => $lang === 'pt_BR'
        ? 'Obrigado! Entraremos em contato em breve.'
        : '¡Gracias! Nos comunicaremos pronto.'
]);
