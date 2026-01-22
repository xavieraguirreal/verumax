<?php
/**
 * VERUMAX SUPER ADMIN - Gestión de Clientes
 */

require_once __DIR__ . '/config.php';
require_once VERUMAX_ADMIN_PATH . '/includes/auth.php';
require_once VERUMAX_ADMIN_PATH . '/classes/ClientSetup.php';

use VERUMaxAdmin\Database;
use VERUMaxAdmin\ClientSetup;

$page_title = 'Clientes';
$action = $_GET['action'] ?? 'list';

// Clientes protegidos - NO se pueden eliminar
$CLIENTES_PROTEGIDOS = ['sajur', 'liberte', 'fotosjuan'];

// Planes disponibles
$planes_disponibles = [
    'test' => 'Test (pruebas internas)',
    'basicum' => 'Basicum ($12/mes)',
    'premium' => 'Premium ($24/mes)',
    'excellens' => 'Excellens ($40/mes)',
    'supremus' => 'Supremus ($80/mes)',
];

// ============================================================================
// AJAX: Guardar estado del checklist
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save_checklist') {
    header('Content-Type: application/json');

    $id = (int)($_POST['id'] ?? 0);
    $checklist = $_POST['checklist'] ?? '{}';

    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID inválido']);
        exit;
    }

    try {
        Database::execute(
            "UPDATE instances SET setup_checklist = ? WHERE id_instancia = ?",
            [$checklist, $id]
        );
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Procesar eliminación de cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_confirm') {
    if (!csrf_validate($_POST[CSRF_TOKEN_NAME] ?? '')) {
        flash('error', 'Sesión expirada');
        redirect('clientes.php');
    }

    $id = (int)($_POST['id'] ?? 0);
    $codigo = trim($_POST['codigo'] ?? '');

    // Verificar cliente protegido
    if (in_array($codigo, $CLIENTES_PROTEGIDOS)) {
        flash('error', 'Este cliente está protegido y no puede eliminarse');
        redirect('clientes.php');
    }

    // Verificar que existe
    $cliente = Database::queryOne("SELECT id_instancia, slug FROM instances WHERE id_instancia = ?", [$id]);
    if (!$cliente || $cliente['slug'] !== $codigo) {
        flash('error', 'Cliente no encontrado');
        redirect('clientes.php');
    }

    try {
        // Eliminar en orden (por foreign keys)
        $eliminados = [];

        // 1. Certificados emitidos
        try {
            $count = Database::executeCertifi("DELETE FROM certificados_emitidos WHERE id_instancia = ?", [$id]);
            $eliminados['certificados_emitidos'] = $count;
        } catch (Exception $e) { $eliminados['certificados_emitidos'] = 0; }

        // 2. Participaciones docentes
        try {
            $count = Database::executeCertifi("DELETE FROM participaciones_docentes WHERE id_instancia = ?", [$id]);
            $eliminados['participaciones_docentes'] = $count;
        } catch (Exception $e) { $eliminados['participaciones_docentes'] = 0; }

        // 3. Certificatum config
        try {
            $count = Database::executeCertifi("DELETE FROM certificatum_config WHERE id_instancia = ?", [$id]);
            $eliminados['certificatum_config'] = $count;
        } catch (Exception $e) { $eliminados['certificatum_config'] = 0; }

        // 4. Inscripciones
        try {
            $count = Database::executeAcademi("DELETE FROM inscripciones WHERE id_instancia = ?", [$id]);
            $eliminados['inscripciones'] = $count;
        } catch (Exception $e) { $eliminados['inscripciones'] = 0; }

        // 5. Cursos
        try {
            $count = Database::executeAcademi("DELETE FROM cursos WHERE id_instancia = ?", [$id]);
            $eliminados['cursos'] = $count;
        } catch (Exception $e) { $eliminados['cursos'] = 0; }

        // 6. Miembros
        try {
            $count = Database::executeNexus("DELETE FROM miembros WHERE institucion = ?", [$codigo]);
            $eliminados['miembros'] = $count;
        } catch (Exception $e) { $eliminados['miembros'] = 0; }

        // 7. Identitas config
        try {
            $count = Database::executeIdenti("DELETE FROM identitas_config WHERE id_instancia = ?", [$id]);
            $eliminados['identitas_config'] = $count;
        } catch (Exception $e) { $eliminados['identitas_config'] = 0; }

        // 8. Instancia (incluye el admin)
        Database::execute("DELETE FROM instances WHERE id_instancia = ?", [$id]);
        $eliminados['instances'] = 1;

        // 10. Eliminar carpeta física
        $carpeta = VERUMAX_ROOT_PATH . '/' . $codigo;
        $archivos_eliminados = 0;
        if (is_dir($carpeta)) {
            $archivos_eliminados = eliminarDirectorioRecursivo($carpeta);
        }

        flash('success', "Cliente '$codigo' eliminado correctamente. Registros BD: " . array_sum($eliminados) . ", Archivos: $archivos_eliminados");
        redirect('clientes.php');

    } catch (Exception $e) {
        flash('error', 'Error al eliminar: ' . $e->getMessage());
        redirect('clientes.php');
    }
}

// Función para eliminar directorio recursivamente
function eliminarDirectorioRecursivo($dir) {
    $count = 0;
    if (!is_dir($dir)) return 0;

    $archivos = array_diff(scandir($dir), ['.', '..']);
    foreach ($archivos as $archivo) {
        $ruta = "$dir/$archivo";
        if (is_dir($ruta)) {
            $count += eliminarDirectorioRecursivo($ruta);
        } else {
            unlink($ruta);
            $count++;
        }
    }
    rmdir($dir);
    return $count;
}

// Función para listar archivos recursivamente
function listarArchivosRecursivo($dir, $prefijo = '') {
    $archivos = [];
    if (!is_dir($dir)) return $archivos;

    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $ruta = "$dir/$item";
        $rutaMostrar = $prefijo . $item;

        if (is_dir($ruta)) {
            $archivos[] = ['tipo' => 'DIR', 'ruta' => $rutaMostrar . '/'];
            $archivos = array_merge($archivos, listarArchivosRecursivo($ruta, $rutaMostrar . '/'));
        } else {
            $size = filesize($ruta);
            $sizeStr = $size < 1024 ? "{$size} B" : round($size/1024, 1) . " KB";
            $archivos[] = ['tipo' => 'FILE', 'ruta' => $rutaMostrar, 'size' => $sizeStr];
        }
    }
    return $archivos;
}

// Procesar formulario de nuevo cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'new') {
    if (!csrf_validate($_POST[CSRF_TOKEN_NAME] ?? '')) {
        flash('error', 'Sesión expirada');
        redirect('clientes.php?action=new');
    }

    $codigo = strtolower(trim($_POST['codigo'] ?? ''));
    $nombre = trim($_POST['nombre'] ?? '');
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plan = $_POST['plan'] ?? 'test';
    $tipo_email = $_POST['tipo_email'] ?? 'verumax';
    $email_envio_propio = trim($_POST['email_envio_propio'] ?? '');

    // Determinar email de envío
    if ($tipo_email === 'verumax') {
        $email_envio = $codigo . '@verumax.com';
    } else {
        $email_envio = $email_envio_propio;
    }

    // Validaciones
    $errors = [];
    if (empty($codigo)) $errors[] = 'El código es requerido';
    if (!preg_match('/^[a-z0-9_]+$/', $codigo)) $errors[] = 'El código solo puede contener letras minúsculas, números y guiones bajos';
    if (empty($nombre)) $errors[] = 'El nombre es requerido';
    if (empty($email)) $errors[] = 'El email es requerido';
    if ($tipo_email === 'propio' && empty($email_envio_propio)) $errors[] = 'El email de envío propio es requerido';

    // Verificar código único
    $existe = Database::queryOne("SELECT id_instancia FROM instances WHERE slug = ?", [$codigo]);
    if ($existe) $errors[] = 'Ya existe un cliente con ese código';

    if (empty($errors)) {
        try {
            Database::beginTransaction();

            // Generar credenciales de admin para el cliente
            $admin_usuario = $codigo . '_admin';
            $admin_password_plain = substr(str_shuffle('abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 12);
            $admin_password_hash = password_hash($admin_password_plain, PASSWORD_BCRYPT);

            // Colores por defecto
            $color_primario = '#2E7D32';
            $color_secundario = '#1B5E20';
            $color_acento = '#66BB6A';
            $dominio = $codigo . '.verumax.com';

            // Insertar en tabla instances (la que usa InstitutionService)
            // identitas_activo = 0 (solo certificados), modulo_certificatum = 1
            $id_instancia = Database::insert(
                "INSERT INTO instances (slug, nombre, nombre_completo, email_contacto, plan, activo,
                 identitas_activo, modulo_certificatum, admin_usuario, admin_password, admin_password_plain, admin_email,
                 color_primario, color_secundario, color_acento, dominio, paleta_colores,
                 tipo_email_envio, email_envio)
                 VALUES (?, ?, ?, ?, ?, 1, 0, 1, ?, ?, ?, ?, ?, ?, ?, ?, 'verde-elegante', ?, ?)",
                [$codigo, $nombre, $nombre_completo, $email, $plan, $admin_usuario, $admin_password_hash, $admin_password_plain, $email,
                 $color_primario, $color_secundario, $color_acento, $dominio, $tipo_email, $email_envio]
            );

            // Crear configuración inicial de Certificatum
            Database::insertCertifi(
                "INSERT INTO certificatum_config (id_instancia, certificatum_usar_paleta_general, certificatum_modo,
                 certificatum_titulo, certificatum_icono, certificatum_posicion, certificatum_descripcion,
                 certificatum_cta_texto, certificatum_mostrar_stats)
                 VALUES (?, 1, 'pagina', 'Certificados', 'award', 99,
                 'Accede a tus certificados, constancias y registro académico completo.',
                 'Ingresar con mi DNI', 1)",
                [$id_instancia]
            );

            // Crear estructura de carpetas y archivos
            $setup = new ClientSetup($codigo, $nombre);
            if (!$setup->create()) {
                throw new Exception("Error creando archivos: " . implode(', ', $setup->getErrors()));
            }

            Database::commit();

            // Guardar datos para mostrar en resumen
            $_SESSION['cliente_creado'] = [
                'id' => $id_instancia,
                'codigo' => $codigo,
                'nombre' => $nombre,
                'email' => $email,
                'plan' => $plan,
                'admin_usuario' => $admin_usuario,
                'admin_password' => $admin_password_plain,
                'url_landing' => 'https://' . $codigo . '.verumax.com/',
                'url_landing_alt' => 'https://verumax.com/' . $codigo . '/',
                'url_admin' => 'https://' . $codigo . '.verumax.com/admin/',
                'url_certificatum' => 'https://' . $codigo . '.verumax.com/certificatum/',
                'tipo_email' => $tipo_email,
                'email_envio' => $email_envio,
            ];

            redirect('clientes.php?action=created');
        } catch (Exception $e) {
            Database::rollback();
            flash('error', 'Error al crear cliente: ' . $e->getMessage());
            redirect('clientes.php?action=new');
        }
    } else {
        foreach ($errors as $err) {
            flash('error', $err);
        }
        redirect('clientes.php?action=new');
    }
}

// ============================================================================
// Acción: Enviar email de bienvenida
// ============================================================================
if ($action === 'send_welcome') {
    $id = (int)($_GET['id'] ?? 0);

    // Obtener datos del cliente
    try {
        $cliente = Database::queryOne(
            "SELECT id_instancia as id, slug as codigo, nombre, nombre_completo,
                    email_contacto,
                    COALESCE(admin_usuario, 'admin') as admin_usuario,
                    admin_email,
                    plan as plan_codigo, modulo_certificatum
             FROM instances
             WHERE id_instancia = ?",
            [$id]
        );
    } catch (Exception $e) {
        flash('error', 'Error al obtener cliente: ' . $e->getMessage());
        redirect('clientes.php');
    }

    if (!$cliente) {
        flash('error', 'Cliente no encontrado');
        redirect('clientes.php');
    }

    // Generar URLs
    $base_url = "https://verumax.com/{$cliente['codigo']}";
    $admin_url = "{$base_url}/admin/";
    $estudiantes_url = "{$base_url}/certificatum/";
    $manual_url = "{$base_url}/admin/manual.php";

    // Procesar envío si es POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_validate($_POST[CSRF_TOKEN_NAME] ?? '')) {
            flash('error', 'Sesión expirada');
            redirect("clientes.php?action=send_welcome&id={$id}");
        }

        $email_destino = trim($_POST['email_destino'] ?? '');
        $password_plano = trim($_POST['password_plano'] ?? '');

        if (empty($email_destino)) {
            flash('error', 'Debe ingresar un email de destino');
            redirect("clientes.php?action=send_welcome&id={$id}");
        }

        // Construir contenido del email
        $asunto = "Bienvenido/a a VERUMax - Datos de acceso para {$cliente['nombre']}";

        $contenido_html = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 0;'>Bienvenido/a a VERUMax</h1>
            </div>

            <div style='padding: 30px; background: #f9fafb;'>
                <p style='font-size: 16px; color: #374151;'>Hola,</p>

                <p style='font-size: 16px; color: #374151;'>
                    Tu cuenta de <strong>{$cliente['nombre']}</strong> en VERUMax está lista.
                    A continuación encontrarás toda la información necesaria para comenzar:
                </p>

                <div style='background: white; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e5e7eb;'>
                    <h3 style='color: #7c3aed; margin-top: 0;'>Datos de Acceso al Panel de Administración</h3>
                    <table style='width: 100%;'>
                        <tr>
                            <td style='padding: 8px 0; color: #6b7280;'>URL Admin:</td>
                            <td style='padding: 8px 0;'><a href='{$admin_url}' style='color: #7c3aed;'>{$admin_url}</a></td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #6b7280;'>Usuario:</td>
                            <td style='padding: 8px 0;'><strong>{$cliente['admin_usuario']}</strong></td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #6b7280;'>Contraseña:</td>
                            <td style='padding: 8px 0;'><strong>{$password_plano}</strong></td>
                        </tr>
                    </table>
                </div>

                <div style='background: white; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e5e7eb;'>
                    <h3 style='color: #7c3aed; margin-top: 0;'>Enlaces Importantes</h3>
                    <ul style='list-style: none; padding: 0; margin: 0;'>
                        <li style='padding: 10px 0; border-bottom: 1px solid #f3f4f6;'>
                            <strong>Portal para Estudiantes:</strong><br>
                            <a href='{$estudiantes_url}' style='color: #7c3aed;'>{$estudiantes_url}</a>
                        </li>
                        <li style='padding: 10px 0; border-bottom: 1px solid #f3f4f6;'>
                            <strong>Manual de Usuario:</strong><br>
                            <a href='{$manual_url}' style='color: #7c3aed;'>{$manual_url}</a>
                        </li>
                        <li style='padding: 10px 0;'>
                            <strong>Soporte:</strong><br>
                            <a href='mailto:soporte@verumax.com' style='color: #7c3aed;'>soporte@verumax.com</a>
                        </li>
                    </ul>
                </div>

                <div style='background: #fef3c7; border-radius: 8px; padding: 15px; margin: 20px 0; border-left: 4px solid #f59e0b;'>
                    <p style='margin: 0; color: #92400e; font-size: 14px;'>
                        <strong>Importante:</strong> Te recomendamos cambiar la contraseña después del primer inicio de sesión.
                    </p>
                </div>

                <p style='font-size: 16px; color: #374151;'>
                    Si tenés alguna consulta, no dudes en contactarnos.
                </p>

                <p style='font-size: 16px; color: #374151;'>
                    Saludos,<br>
                    <strong>Equipo VERUMax</strong>
                </p>
            </div>

            <div style='background: #1f2937; padding: 20px; text-align: center;'>
                <p style='color: #9ca3af; margin: 0; font-size: 12px;'>
                    VERUMax - Plataforma de Gestión de Certificados Académicos
                </p>
            </div>
        </div>
        ";

        // Enviar email usando PHPMailer o función simple
        $enviado = enviarEmailBienvenida($email_destino, $asunto, $contenido_html);

        if ($enviado) {
            // Registrar en audit_log
            Database::execute(
                "INSERT INTO audit_log (id_superadmin, accion, entidad, id_entidad, datos_nuevos, ip, created_at)
                 VALUES (?, 'enviar_bienvenida', 'cliente', ?, ?, ?, NOW())",
                [
                    $_SESSION['superadmin_id'] ?? null,
                    $id,
                    json_encode(['email_destino' => $email_destino]),
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]
            );

            flash('success', "Email de bienvenida enviado correctamente a {$email_destino}");
        } else {
            flash('error', 'Error al enviar el email. Verificá la configuración de SendGrid.');
        }

        redirect('clientes.php');
    }
}

// Función para enviar email de bienvenida
function enviarEmailBienvenida($to, $subject, $htmlContent) {
    // Intentar usar SendGrid
    $apiKey = getenv('SENDGRID_API_KEY');

    if (!$apiKey) {
        // Fallback: intentar leer de config
        $configFile = VERUMAX_ROOT_PATH . '/config/sendgrid.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
            $apiKey = $config['api_key'] ?? null;
        }
    }

    if (!$apiKey) {
        error_log("enviarEmailBienvenida: No hay API key de SendGrid");
        return false;
    }

    $payload = [
        'personalizations' => [[
            'to' => [['email' => $to]],
            'subject' => $subject
        ]],
        'from' => [
            'email' => 'notificaciones@verumax.com',
            'name' => 'VERUMax'
        ],
        'content' => [
            ['type' => 'text/html', 'value' => $htmlContent]
        ]
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
    curl_close($ch);

    if ($httpCode === 202) {
        return true;
    }

    error_log("SendGrid error [{$httpCode}]: {$response}");
    return false;
}

// Obtener lista de clientes desde tabla instances
$clientes = Database::query(
    "SELECT id_instancia as id, slug as codigo, nombre, nombre_completo, email_contacto,
            admin_usuario, admin_email,
            plan as plan_codigo, activo, fecha_creacion as created_at,
            modulo_certificatum
     FROM instances
     ORDER BY fecha_creacion DESC"
);

include VERUMAX_ADMIN_PATH . '/includes/header.php';
?>

<?php if ($action === 'list'): ?>
<!-- Lista de Clientes -->
<div class="flex justify-between items-center mb-6">
    <div>
        <p class="text-gray-600">Total: <?= count($clientes) ?> clientes</p>
    </div>
    <a href="?action=new" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <span>Nuevo Cliente</span>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Certificatum</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (empty($clientes)): ?>
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    No hay clientes registrados
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($clientes as $cliente): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="font-medium text-gray-900"><?= e($cliente['nombre']) ?></div>
                    <div class="text-sm text-gray-500"><?= e($cliente['email_contacto'] ?? '-') ?></div>
                </td>
                <td class="px-6 py-4">
                    <code class="px-2 py-1 bg-gray-100 rounded text-sm"><?= e($cliente['codigo']) ?></code>
                </td>
                <td class="px-6 py-4">
                    <?php if ($cliente['plan_codigo']): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <?= e(ucfirst($cliente['plan_codigo'])) ?>
                    </span>
                    <?php else: ?>
                    <span class="text-gray-400 text-sm">Sin plan</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                    <?php if ($cliente['modulo_certificatum']): ?>
                    <span class="inline-flex items-center gap-1 text-green-600 text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Activo
                    </span>
                    <?php else: ?>
                    <span class="text-gray-400 text-sm">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                    <?php if ($cliente['activo']): ?>
                    <span class="inline-flex items-center gap-1 text-green-600 text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Activo
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center gap-1 text-red-600 text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        Inactivo
                    </span>
                    <?php endif; ?>
                </td>
                <td class="px-6 py-4 text-right space-x-3">
                    <a href="?action=send_welcome&id=<?= $cliente['id'] ?>" class="text-green-600 hover:text-green-800 text-sm font-medium" title="Enviar email de bienvenida">
                        Enviar
                    </a>
                    <a href="?action=setup&id=<?= $cliente['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium" title="Ver instrucciones de configuración">
                        Setup
                    </a>
                    <a href="?action=edit&id=<?= $cliente['id'] ?>" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                        Editar
                    </a>
                    <?php if (!in_array($cliente['codigo'], $CLIENTES_PROTEGIDOS)): ?>
                    <a href="?action=delete&id=<?= $cliente['id'] ?>" class="text-red-600 hover:text-red-800 text-sm font-medium">
                        Eliminar
                    </a>
                    <?php else: ?>
                    <span class="text-gray-400 text-sm cursor-not-allowed" title="Cliente protegido">
                        Protegido
                    </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php elseif ($action === 'send_welcome'): ?>
<!-- Enviar Email de Bienvenida -->
<div class="max-w-3xl">
    <div class="mb-6">
        <a href="clientes.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Volver a la lista</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Enviar Email de Bienvenida</h2>
                <p class="text-sm text-gray-500">Cliente: <?= e($cliente['nombre']) ?> (<?= e($cliente['codigo']) ?>)</p>
            </div>
        </div>

        <!-- Info del cliente -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Datos que se incluirán en el email:</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Usuario admin:</span>
                    <span class="font-medium ml-2"><?= e($cliente['admin_usuario'] ?? 'admin') ?></span>
                </div>
                <div>
                    <span class="text-gray-500">Email admin:</span>
                    <span class="font-medium ml-2"><?= e($cliente['admin_email'] ?? '-') ?></span>
                </div>
                <div>
                    <span class="text-gray-500">URL Admin:</span>
                    <span class="font-mono text-purple-600 ml-2"><?= e($admin_url) ?></span>
                </div>
                <div>
                    <span class="text-gray-500">URL Estudiantes:</span>
                    <span class="font-mono text-purple-600 ml-2"><?= e($estudiantes_url) ?></span>
                </div>
            </div>
        </div>

        <form method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email de destino *</label>
                <input type="email" name="email_destino" required
                       value="<?= e($cliente['email_contacto'] ?? $cliente['admin_email'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="cliente@ejemplo.com">
                <p class="mt-1 text-xs text-gray-500">Email donde se enviará la información de acceso</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contraseña (texto plano) *</label>
                <div class="relative">
                    <input type="text" name="password_plano" required id="password_plano"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono"
                           placeholder="Ingresá la contraseña para incluir en el email">
                    <button type="button" onclick="generarPassword()" class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-600">
                        Generar
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500">Esta contraseña se incluirá en el email. Si es un cliente nuevo, generá una nueva.</p>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-sm text-amber-800">
                        <p class="font-medium">Importante:</p>
                        <ul class="mt-1 list-disc list-inside space-y-1">
                            <li>El email incluirá la contraseña en texto plano</li>
                            <li>Si generás una nueva contraseña, asegurate de actualizarla en el sistema</li>
                            <li>El cliente debería cambiar la contraseña después del primer acceso</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 inline-flex justify-center items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Enviar Email de Bienvenida
                </button>
                <a href="clientes.php" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition font-medium">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

    <!-- Preview del email -->
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-sm font-medium text-gray-700 mb-4">Vista previa del email:</h3>
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            <div style="background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); padding: 20px; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 1.25rem;">Bienvenido/a a VERUMax</h1>
            </div>
            <div class="p-6 bg-gray-50 text-sm">
                <p class="text-gray-700">Hola,</p>
                <p class="text-gray-700 mt-2">Tu cuenta de <strong><?= e($cliente['nombre']) ?></strong> en VERUMax está lista.</p>

                <div class="bg-white rounded-lg p-4 my-4 border border-gray-200">
                    <h4 class="text-purple-700 font-medium mb-2">Datos de Acceso al Panel de Administración</h4>
                    <table class="w-full text-sm">
                        <tr>
                            <td class="py-1 text-gray-500">URL Admin:</td>
                            <td class="py-1"><a href="<?= e($admin_url) ?>" class="text-purple-600"><?= e($admin_url) ?></a></td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-500">Usuario:</td>
                            <td class="py-1 font-medium"><?= e($cliente['admin_usuario'] ?? 'admin') ?></td>
                        </tr>
                        <tr>
                            <td class="py-1 text-gray-500">Contraseña:</td>
                            <td class="py-1 font-medium text-gray-400">[La que ingreses arriba]</td>
                        </tr>
                    </table>
                </div>

                <div class="bg-white rounded-lg p-4 my-4 border border-gray-200">
                    <h4 class="text-purple-700 font-medium mb-2">Enlaces Importantes</h4>
                    <ul class="space-y-2">
                        <li><strong>Portal Estudiantes:</strong> <a href="<?= e($estudiantes_url) ?>" class="text-purple-600"><?= e($estudiantes_url) ?></a></li>
                        <li><strong>Manual:</strong> <a href="<?= e($manual_url) ?>" class="text-purple-600"><?= e($manual_url) ?></a></li>
                        <li><strong>Soporte:</strong> <a href="mailto:soporte@verumax.com" class="text-purple-600">soporte@verumax.com</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generarPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
    let password = '';
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('password_plano').value = password;
}
</script>

<?php elseif ($action === 'new'): ?>
<!-- Formulario Nuevo Cliente -->
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="clientes.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Volver a la lista</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Nuevo Cliente</h2>

        <form method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Código *</label>
                    <input type="text" name="codigo" required pattern="[a-z0-9_]+"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="pampaformacion">
                    <p class="mt-1 text-xs text-gray-500">Slug para URLs y carpeta. Solo minúsculas, números y _</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Corto *</label>
                    <input type="text" name="nombre" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Pampa Formación">
                    <p class="mt-1 text-xs text-gray-500">Nombre para mostrar en la interfaz</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Legal / Razón Social</label>
                <input type="text" name="nombre_completo"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Pampa Formación S.A.">
                <p class="mt-1 text-xs text-gray-500">Nombre completo para documentos oficiales (opcional)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email de Contacto *</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="contacto@cliente.com">
                <p class="mt-1 text-xs text-gray-500">Email del cliente para comunicaciones</p>
            </div>

            <!-- Tipo de Email para envíos -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-3">Email para Envío de Notificaciones *</label>
                <div class="space-y-3">
                    <label class="flex items-start gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-purple-300 transition">
                        <input type="radio" name="tipo_email" value="verumax" checked
                               class="mt-1 text-purple-600 focus:ring-purple-500" onchange="toggleEmailFields()">
                        <div>
                            <span class="font-medium text-gray-800">Usar email de VERUMax</span>
                            <p class="text-xs text-gray-500 mt-1">
                                Se usará <code class="bg-gray-100 px-1 rounded">[codigo]@verumax.com</code><br>
                                Solo requiere crear Sender en SendGrid (más rápido)
                            </p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 bg-white rounded-lg border border-gray-200 cursor-pointer hover:border-purple-300 transition">
                        <input type="radio" name="tipo_email" value="propio"
                               class="mt-1 text-purple-600 focus:ring-purple-500" onchange="toggleEmailFields()">
                        <div>
                            <span class="font-medium text-gray-800">Usar email propio del cliente</span>
                            <p class="text-xs text-gray-500 mt-1">
                                El cliente tiene su propio dominio (ej: info@suempresa.com)<br>
                                Requiere validar dominio en SendGrid + configurar DNS
                            </p>
                        </div>
                    </label>
                </div>

                <!-- Campo para email propio (oculto por defecto) -->
                <div id="email_propio_fields" class="hidden mt-4 p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <label class="block text-sm font-medium text-amber-800 mb-2">Email del Cliente para Envíos *</label>
                    <input type="email" name="email_envio_propio" id="email_envio_propio"
                           class="w-full px-4 py-2 border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           placeholder="notificaciones@suempresa.com">
                    <p class="mt-2 text-xs text-amber-700">
                        Este email se usará como remitente de las notificaciones.<br>
                        El cliente deberá agregar registros DNS para validar su dominio en SendGrid.
                    </p>
                </div>
            </div>

            <script>
            function toggleEmailFields() {
                const tipoEmail = document.querySelector('input[name="tipo_email"]:checked').value;
                const emailPropioFields = document.getElementById('email_propio_fields');
                const emailPropioInput = document.getElementById('email_envio_propio');

                if (tipoEmail === 'propio') {
                    emailPropioFields.classList.remove('hidden');
                    emailPropioInput.required = true;
                } else {
                    emailPropioFields.classList.add('hidden');
                    emailPropioInput.required = false;
                    emailPropioInput.value = '';
                }
            }
            </script>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Plan</label>
                <select name="plan"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="test" selected>Test (pruebas internas)</option>
                    <option value="basicum">Basicum ($12/mes)</option>
                    <option value="premium">Premium ($24/mes)</option>
                    <option value="excellens">Excellens ($40/mes)</option>
                    <option value="supremus">Supremus ($80/mes)</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="clientes.php" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                    Crear Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<?php elseif ($action === 'created'): ?>
<?php
$cliente = $_SESSION['cliente_creado'] ?? null;
if (!$cliente) {
    redirect('clientes.php');
}
unset($_SESSION['cliente_creado']);
?>
<!-- Resumen de Cliente Creado -->
<div class="max-w-3xl mx-auto">
    <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-3 mb-2">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-xl font-bold text-green-800">Cliente Creado Exitosamente</h2>
        </div>
        <p class="text-green-700">El cliente <strong><?= e($cliente['nombre']) ?></strong> ha sido creado con toda su configuración inicial.</p>
    </div>

    <!-- Información del Cliente -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Cliente</h3>
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500">Código</dt>
                <dd class="font-medium"><code class="px-2 py-1 bg-gray-100 rounded"><?= e($cliente['codigo']) ?></code></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Nombre</dt>
                <dd class="font-medium"><?= e($cliente['nombre']) ?></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Email</dt>
                <dd class="font-medium"><?= e($cliente['email']) ?></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Plan</dt>
                <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><?= e(ucfirst($cliente['plan'])) ?></span></dd>
            </div>
        </dl>
    </div>

    <!-- Credenciales de Admin -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            <h3 class="text-lg font-semibold text-amber-800">Credenciales de Administrador</h3>
        </div>
        <p class="text-amber-700 text-sm mb-4">Guarde estas credenciales en un lugar seguro. La contraseña no se puede recuperar.</p>
        <div class="bg-white rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Usuario:</span>
                <code class="px-3 py-1 bg-gray-100 rounded font-mono"><?= e($cliente['admin_usuario']) ?></code>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Contraseña:</span>
                <code class="px-3 py-1 bg-amber-100 rounded font-mono text-amber-800"><?= e($cliente['admin_password']) ?></code>
            </div>
        </div>
    </div>

    <!-- URLs de Acceso -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">URLs de Acceso</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm text-gray-500">Landing Page (Subdominio - Recomendado)</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="<?= e($cliente['url_landing']) ?>"
                           class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono">
                    <a href="<?= e($cliente['url_landing']) ?>" target="_blank"
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
                <p class="text-xs text-gray-400 mt-1">Alternativa: <?= e($cliente['url_landing_alt']) ?></p>
            </div>
            <div>
                <label class="text-sm text-gray-500">Panel de Administración del Cliente</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="<?= e($cliente['url_admin']) ?>"
                           class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono">
                    <a href="<?= e($cliente['url_admin']) ?>" target="_blank"
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Certificatum</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="<?= e($cliente['url_certificatum']) ?>"
                           class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono">
                    <a href="<?= e($cliente['url_certificatum']) ?>" target="_blank"
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Acción Requerida: DNS -->
    <!-- PASO 1: Crear Subdominio en cPanel -->
    <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex items-center justify-center w-7 h-7 bg-red-600 text-white rounded-full text-sm font-bold">1</span>
            <h3 class="text-lg font-semibold text-red-800">Crear Subdominio en cPanel</h3>
        </div>
        <p class="text-red-700 text-sm mb-3">Crear el subdominio en cPanel para que el cliente pueda acceder:</p>
        <div class="bg-white rounded-lg p-4 font-mono text-sm space-y-1">
            <p><strong>Subdominio:</strong> <?= e($cliente['codigo']) ?></p>
            <p><strong>Dominio:</strong> verumax.com</p>
            <p><strong>Raíz del documento:</strong> /home/verumax/public_html/<?= e($cliente['codigo']) ?></p>
        </div>
        <p class="text-red-600 text-xs mt-3">⚠️ Sin este paso, el cliente no podrá acceder a su sitio.</p>
    </div>

    <!-- PASO 2: Configurar SendGrid -->
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex items-center justify-center w-7 h-7 bg-orange-600 text-white rounded-full text-sm font-bold">2</span>
            <h3 class="text-lg font-semibold text-orange-800">Configurar SendGrid (Envío de Emails)</h3>
        </div>

        <?php if (($cliente['tipo_email'] ?? 'verumax') === 'verumax'): ?>
        <!-- Instrucciones para email de VERUMax -->
        <p class="text-orange-700 text-sm mb-3">
            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium mb-2">
                ✓ Email de VERUMax
            </span><br>
            Solo hay que crear un Sender Identity (proceso rápido):
        </p>
        <div class="bg-white rounded-lg p-4 text-sm space-y-4">
            <div>
                <p class="font-semibold text-gray-700 mb-2">Crear Sender Identity:</p>
                <ol class="list-decimal list-inside text-gray-600 space-y-2">
                    <li>Ir a <a href="https://app.sendgrid.com/settings/sender_auth/senders" target="_blank" class="text-blue-600 hover:underline font-medium">SendGrid → Sender Authentication</a></li>
                    <li>Clic en <strong>"Create New Sender"</strong></li>
                    <li>Completar el formulario con estos datos:</li>
                </ol>
            </div>

            <!-- Tabla de campos SendGrid -->
            <div class="bg-gray-50 rounded-lg p-4 space-y-3 border border-gray-200">
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">From Name *</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['nombre']) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">From Email *</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['email_envio'] ?? $cliente['codigo'] . '@verumax.com') ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">Reply To *</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['email_envio'] ?? $cliente['codigo'] . '@verumax.com') ?></code>
                </div>
                <div class="border-t border-gray-300 pt-3 mt-3">
                    <p class="text-xs text-gray-500 mb-2">Datos de empresa (usar datos de VERUMax):</p>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Company Address *</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Av. Corrientes 1234</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">City *</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Buenos Aires</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Country *</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Argentina</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Nickname *</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['codigo']) ?></code>
                </div>
            </div>

            <ol class="list-decimal list-inside text-gray-600 space-y-1" start="4">
                <li>Clic en <strong>"Create"</strong></li>
                <li>Verificar el email de confirmación que llega a <code class="bg-gray-100 px-1 rounded text-xs"><?= e($cliente['email_envio'] ?? $cliente['codigo'] . '@verumax.com') ?></code></li>
            </ol>
        </div>

        <?php else: ?>
        <!-- Instrucciones para email propio (dominio del cliente) -->
        <?php
        $email_propio = $cliente['email_envio'] ?? '';
        $dominio_propio = substr(strrchr($email_propio, "@"), 1);
        ?>
        <p class="text-orange-700 text-sm mb-3">
            <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-100 text-amber-800 rounded text-xs font-medium mb-2">
                ⚠️ Email Propio - Requiere validación de dominio
            </span><br>
            El cliente usará su propio dominio. Requiere 2 pasos: <strong>Autenticar dominio</strong> + <strong>Crear Sender</strong>.
        </p>
        <div class="bg-white rounded-lg p-4 text-sm space-y-4">
            <!-- Paso 2.1: Autenticar dominio -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="font-semibold text-red-800 mb-2">Paso 2.1: Autenticar Dominio (DNS)</p>
                <ol class="list-decimal list-inside text-gray-600 space-y-2">
                    <li>Ir a <a href="https://app.sendgrid.com/settings/sender_auth" target="_blank" class="text-blue-600 hover:underline font-medium">SendGrid → Settings → Sender Authentication</a></li>
                    <li>Clic en <strong>"Authenticate Your Domain"</strong></li>
                    <li>Seleccionar DNS host (ej: Cloudflare, GoDaddy, etc.)</li>
                    <li>Ingresar el dominio: <code class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs"><?= e($dominio_propio) ?></code></li>
                    <li>SendGrid generará registros DNS (CNAME o TXT)</li>
                    <li><strong>El cliente debe agregar estos registros en su panel de DNS</strong></li>
                    <li>Esperar propagación DNS (puede tardar hasta 48 horas)</li>
                    <li>Verificar en SendGrid que el dominio esté autenticado</li>
                </ol>
                <p class="text-red-600 text-xs mt-3">⚠️ Sin autenticar el dominio, los emails podrían ir a spam o ser rechazados.</p>
            </div>

            <!-- Paso 2.2: Crear Sender -->
            <div>
                <p class="font-semibold text-gray-700 mb-2">Paso 2.2: Crear Sender Identity (después de autenticar dominio):</p>
                <ol class="list-decimal list-inside text-gray-600 space-y-2">
                    <li>Ir a <a href="https://app.sendgrid.com/settings/sender_auth/senders" target="_blank" class="text-blue-600 hover:underline font-medium">SendGrid → Sender Authentication</a></li>
                    <li>Clic en <strong>"Create New Sender"</strong></li>
                    <li>Completar el formulario:</li>
                </ol>
            </div>

            <!-- Tabla de campos SendGrid para email propio -->
            <div class="bg-gray-50 rounded-lg p-4 space-y-3 border border-gray-200">
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">From Name *</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['nombre']) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">From Email *</span>
                    <code class="col-span-2 bg-amber-100 text-amber-800 px-2 py-1 rounded text-xs"><?= e($email_propio) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">Reply To *</span>
                    <code class="col-span-2 bg-amber-100 text-amber-800 px-2 py-1 rounded text-xs"><?= e($email_propio) ?></code>
                </div>
                <div class="border-t border-gray-300 pt-3 mt-3">
                    <p class="text-xs text-gray-500 mb-2">Datos de empresa (usar datos del cliente):</p>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Company Address *</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Dirección del cliente</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">City *</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Ciudad del cliente</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Country *</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Argentina</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Nickname *</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['codigo']) ?></code>
                </div>
            </div>

            <ol class="list-decimal list-inside text-gray-600 space-y-1" start="4">
                <li>Clic en <strong>"Create"</strong></li>
                <li>Verificar el email de confirmación que llega a <code class="bg-gray-100 px-1 rounded text-xs"><?= e($email_propio) ?></code></li>
            </ol>
        </div>
        <?php endif; ?>

        <p class="text-orange-600 text-xs mt-3">📧 Sin esto, los emails del cliente no se enviarán correctamente.</p>
    </div>

    <?php if (($cliente['tipo_email'] ?? 'verumax') === 'verumax'): ?>
    <!-- PASO 3: Crear Forwarder de Email (solo para email VERUMax) -->
    <div class="bg-cyan-50 border border-cyan-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex items-center justify-center w-7 h-7 bg-cyan-600 text-white rounded-full text-sm font-bold">3</span>
            <h3 class="text-lg font-semibold text-cyan-800">Crear Forwarder de Email</h3>
        </div>
        <p class="text-cyan-700 text-sm mb-3">Para que el cliente reciba las respuestas a los emails de notificación:</p>
        <div class="bg-white rounded-lg p-4 text-sm space-y-4">
            <div>
                <p class="font-semibold text-gray-700 mb-2">Crear Forwarder en cPanel:</p>
                <ol class="list-decimal list-inside text-gray-600 space-y-2">
                    <li>Ir a <a href="https://verumax.com:2083" target="_blank" class="text-blue-600 hover:underline font-medium">cPanel</a> → <strong>Email</strong> → <strong>Forwarders</strong></li>
                    <li>Clic en <strong>"Add Forwarder"</strong></li>
                    <li>Completar:</li>
                </ol>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 space-y-3 border border-gray-200">
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">Address to Forward</span>
                    <code class="col-span-2 bg-cyan-100 text-cyan-800 px-2 py-1 rounded text-xs"><?= e($cliente['email_envio'] ?? $cliente['codigo'] . '@verumax.com') ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">Forward to Email</span>
                    <code class="col-span-2 bg-cyan-100 text-cyan-800 px-2 py-1 rounded text-xs"><?= e($cliente['email']) ?></code>
                </div>
            </div>

            <ol class="list-decimal list-inside text-gray-600 space-y-1" start="4">
                <li>Clic en <strong>"Add Forwarder"</strong></li>
            </ol>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-3">
                <p class="text-blue-800 text-xs font-medium mb-1">💡 Acceso directo al webmail (casos especiales):</p>
                <p class="text-blue-700 text-xs">Si el cliente necesita acceder directamente al buzón:</p>
                <p class="text-blue-600 text-xs mt-1">
                    <strong>URL:</strong> <a href="https://webmail.verumax.com" target="_blank" class="hover:underline">webmail.verumax.com</a><br>
                    <strong>Usuario:</strong> <?= e($cliente['email_envio'] ?? $cliente['codigo'] . '@verumax.com') ?>
                </p>
                <p class="text-blue-500 text-xs mt-1">⚠️ Requiere crear cuenta de email en cPanel (no solo forwarder)</p>
            </div>
        </div>
        <p class="text-cyan-600 text-xs mt-3">📬 Con el forwarder, las respuestas llegarán automáticamente al email del cliente.</p>
    </div>
    <?php endif; ?>

    <!-- PASO <?= (($cliente['tipo_email'] ?? 'verumax') === 'verumax') ? '4' : '3' ?>: SSL (Automático) -->
    <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex items-center justify-center w-7 h-7 bg-green-600 text-white rounded-full text-sm font-bold"><?= (($cliente['tipo_email'] ?? 'verumax') === 'verumax') ? '4' : '3' ?></span>
            <h3 class="text-lg font-semibold text-green-800">Certificado SSL (Automático)</h3>
        </div>
        <p class="text-green-700 text-sm">El certificado SSL se genera automáticamente con Let's Encrypt después de crear el subdominio.</p>
        <div class="bg-white rounded-lg p-4 text-sm mt-3">
            <p class="text-gray-600">Puede tardar hasta <strong>24 horas</strong> en activarse. Si después de ese tiempo el sitio muestra advertencia de seguridad:</p>
            <ol class="list-decimal list-inside text-gray-600 mt-2 space-y-1">
                <li>Ir a cPanel → SSL/TLS Status</li>
                <li>Seleccionar el subdominio <?= e($cliente['codigo']) ?>.verumax.com</li>
                <li>Clic en "Run AutoSSL"</li>
            </ol>
        </div>
    </div>

    <!-- PASO <?= (($cliente['tipo_email'] ?? 'verumax') === 'verumax') ? '5' : '4' ?>: Verificar Acceso -->
    <div class="bg-purple-50 border border-purple-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex items-center justify-center w-7 h-7 bg-purple-600 text-white rounded-full text-sm font-bold"><?= (($cliente['tipo_email'] ?? 'verumax') === 'verumax') ? '5' : '4' ?></span>
            <h3 class="text-lg font-semibold text-purple-800">Verificar Acceso</h3>
        </div>
        <p class="text-purple-700 text-sm mb-3">Una vez creado el subdominio, verificar que todo funcione:</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <a href="<?= e($cliente['url_landing']) ?>" target="_blank" class="flex items-center gap-2 bg-white rounded-lg p-3 hover:bg-purple-100 transition">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-sm text-purple-700">Landing Page</span>
            </a>
            <a href="<?= e($cliente['url_admin']) ?>" target="_blank" class="flex items-center gap-2 bg-white rounded-lg p-3 hover:bg-purple-100 transition">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="text-sm text-purple-700">Panel Admin</span>
            </a>
            <a href="<?= e($cliente['url_certificatum']) ?>" target="_blank" class="flex items-center gap-2 bg-white rounded-lg p-3 hover:bg-purple-100 transition">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                <span class="text-sm text-purple-700">Certificatum</span>
            </a>
        </div>
    </div>

    <!-- Checklist Final Funcional -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6" id="checklist-container" data-client-id="<?= $cliente['id'] ?>">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-blue-800">✅ Checklist de Configuración</h3>
            <div id="checklist-status" class="text-sm"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <!-- Tareas Admin -->
            <div class="space-y-3">
                <p class="font-semibold text-blue-700 border-b border-blue-200 pb-2">Tareas del Administrador VERUMax:</p>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="subdominio" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>Subdominio creado en cPanel</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="sendgrid" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>Sender configurado en SendGrid</span>
                </label>
                <?php if (($cliente['tipo_email'] ?? 'verumax') === 'verumax'): ?>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="forwarder" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>Forwarder de email creado</span>
                </label>
                <?php endif; ?>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="ssl" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>SSL activo (verificar https)</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="urls_verificadas" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>Acceso verificado a las 3 URLs</span>
                </label>
            </div>

            <!-- Tareas Cliente -->
            <div class="space-y-3">
                <p class="font-semibold text-blue-700 border-b border-blue-200 pb-2">Tareas del Cliente (post-entrega):</p>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="cliente_acceso" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>Acceder al Panel de Admin</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="cliente_branding" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>Configurar logo y colores</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="cliente_datos" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>Cargar cursos y estudiantes</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="cliente_test" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    <span>Probar emisión de certificados</span>
                </label>
            </div>
        </div>

        <!-- Indicador de progreso -->
        <div class="mt-6 pt-4 border-t border-blue-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-blue-700">Progreso de configuración:</span>
                <span id="progress-text" class="text-sm font-medium text-blue-800">0/8 completado</span>
            </div>
            <div class="w-full bg-blue-200 rounded-full h-3">
                <div id="progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        <!-- Mensaje de listo para entregar -->
        <div id="ready-message" class="hidden mt-4 p-4 bg-green-100 border border-green-300 rounded-lg">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold text-green-800">¡Listo para entregar al cliente!</p>
                    <p class="text-sm text-green-700">Todas las tareas de configuración inicial están completadas.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between">
        <a href="clientes.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">
            ← Volver a la lista
        </a>
        <a href="clientes.php?action=new" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
            Crear otro cliente
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('checklist-container');
    if (!container) return;

    const clientId = container.dataset.clientId;
    const checkboxes = document.querySelectorAll('.checklist-item');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const readyMessage = document.getElementById('ready-message');
    const statusDiv = document.getElementById('checklist-status');

    // Tareas requeridas para poder entregar (las 4 del admin)
    const requiredTasks = ['subdominio', 'sendgrid', 'ssl', 'urls_verificadas'];

    function updateProgress() {
        const total = checkboxes.length;
        const checked = document.querySelectorAll('.checklist-item:checked').length;
        const percentage = Math.round((checked / total) * 100);

        progressBar.style.width = percentage + '%';
        progressText.textContent = checked + '/' + total + ' completado';

        // Verificar si las tareas requeridas están completas
        const requiredComplete = requiredTasks.every(task => {
            const cb = document.querySelector(`[data-task="${task}"]`);
            return cb && cb.checked;
        });

        if (requiredComplete) {
            readyMessage.classList.remove('hidden');
            progressBar.classList.remove('bg-blue-600');
            progressBar.classList.add('bg-green-600');
        } else {
            readyMessage.classList.add('hidden');
            progressBar.classList.remove('bg-green-600');
            progressBar.classList.add('bg-blue-600');
        }
    }

    function saveChecklist() {
        const data = {};
        checkboxes.forEach(cb => {
            data[cb.dataset.task] = cb.checked;
        });

        statusDiv.innerHTML = '<span class="text-gray-500">Guardando...</span>';

        fetch('clientes.php?action=save_checklist', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + clientId + '&checklist=' + encodeURIComponent(JSON.stringify(data))
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                statusDiv.innerHTML = '<span class="text-green-600">✓ Guardado</span>';
                setTimeout(() => { statusDiv.innerHTML = ''; }, 2000);
            } else {
                statusDiv.innerHTML = '<span class="text-red-600">Error al guardar</span>';
            }
        })
        .catch(() => {
            statusDiv.innerHTML = '<span class="text-red-600">Error de conexión</span>';
        });
    }

    // Event listeners
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateProgress();
            saveChecklist();
        });
    });

    // Inicializar progreso
    updateProgress();
});
</script>

<?php elseif ($action === 'setup'): ?>
<?php
// Cargar datos del cliente existente
$id = (int)($_GET['id'] ?? 0);
$cliente_db = Database::queryOne("SELECT * FROM instances WHERE id_instancia = ?", [$id]);

if (!$cliente_db) {
    flash('error', 'Cliente no encontrado');
    redirect('clientes.php');
}

// Preparar datos en formato compatible con la vista
$cliente = [
    'id' => $cliente_db['id_instancia'],
    'codigo' => $cliente_db['slug'],
    'nombre' => $cliente_db['nombre'],
    'email' => $cliente_db['email_contacto'],
    'plan' => $cliente_db['plan'],
    'admin_usuario' => $cliente_db['admin_usuario'],
    'url_landing' => 'https://' . $cliente_db['slug'] . '.verumax.com/',
    'url_landing_alt' => 'https://verumax.com/' . $cliente_db['slug'] . '/',
    'url_admin' => 'https://' . $cliente_db['slug'] . '.verumax.com/admin/',
    'url_certificatum' => 'https://' . $cliente_db['slug'] . '.verumax.com/certificatum/',
    'tipo_email' => $cliente_db['tipo_email_envio'] ?? 'verumax',
    'email_envio' => $cliente_db['email_envio'] ?? $cliente_db['slug'] . '@verumax.com',
];

// Cargar estado del checklist
$checklist_json = $cliente_db['setup_checklist'] ?? '{}';
$checklist = json_decode($checklist_json, true) ?: [];
?>

<!-- Vista Setup de Cliente Existente -->
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="clientes.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Volver a la lista</span>
        </a>
    </div>

    <!-- Header -->
    <div class="bg-purple-50 border border-purple-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-3 mb-2">
            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h2 class="text-xl font-bold text-purple-800">Setup: <?= e($cliente['nombre']) ?></h2>
        </div>
        <p class="text-purple-700">Instrucciones de configuración y checklist de progreso.</p>
    </div>

    <!-- Información del Cliente -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Información del Cliente</h3>
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500">Código</dt>
                <dd class="font-medium"><code class="px-2 py-1 bg-gray-100 rounded"><?= e($cliente['codigo']) ?></code></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Nombre</dt>
                <dd class="font-medium"><?= e($cliente['nombre']) ?></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Email</dt>
                <dd class="font-medium"><?= e($cliente['email']) ?></dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Plan</dt>
                <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><?= e(ucfirst($cliente['plan'])) ?></span></dd>
            </div>
        </dl>
    </div>

    <!-- Credenciales -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            <h3 class="text-lg font-semibold text-amber-800">Credenciales de Administrador</h3>
        </div>
        <div class="bg-white rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Usuario:</span>
                <code class="px-3 py-1 bg-gray-100 rounded font-mono"><?= e($cliente['admin_usuario']) ?></code>
            </div>
            <?php if (!empty($cliente['admin_password_plain'])): ?>
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Contraseña:</span>
                <code class="px-3 py-1 bg-amber-100 rounded font-mono text-amber-800"><?= e($cliente['admin_password_plain']) ?></code>
            </div>
            <?php else: ?>
            <p class="text-xs text-amber-600 mt-2">Cliente creado antes de guardar contraseñas. Se debe resetear desde la BD.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- URLs de Acceso -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">URLs de Acceso</h3>
        <div class="space-y-4">
            <div>
                <label class="text-sm text-gray-500">Landing Page</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="<?= e($cliente['url_landing']) ?>"
                           class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono">
                    <a href="<?= e($cliente['url_landing']) ?>" target="_blank"
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Panel de Administración</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="<?= e($cliente['url_admin']) ?>"
                           class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono">
                    <a href="<?= e($cliente['url_admin']) ?>" target="_blank"
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-500">Certificatum</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="<?= e($cliente['url_certificatum']) ?>"
                           class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono">
                    <a href="<?= e($cliente['url_certificatum']) ?>" target="_blank"
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- PASO 1: Subdominio -->
    <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex items-center justify-center w-7 h-7 bg-red-600 text-white rounded-full text-sm font-bold">1</span>
            <h3 class="text-lg font-semibold text-red-800">Crear Subdominio en cPanel</h3>
        </div>
        <div class="bg-white rounded-lg p-4 font-mono text-sm space-y-1">
            <p><strong>Subdominio:</strong> <?= e($cliente['codigo']) ?></p>
            <p><strong>Dominio:</strong> verumax.com</p>
            <p><strong>Raíz del documento:</strong> /home/verumax/public_html/<?= e($cliente['codigo']) ?></p>
        </div>
    </div>

    <!-- PASO 2: SendGrid -->
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex items-center justify-center w-7 h-7 bg-orange-600 text-white rounded-full text-sm font-bold">2</span>
            <h3 class="text-lg font-semibold text-orange-800">Configurar SendGrid</h3>
        </div>

        <?php if (($cliente['tipo_email'] ?? 'verumax') === 'verumax'): ?>
        <!-- Instrucciones para email de VERUMax -->
        <p class="text-orange-700 text-sm mb-3">
            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">
                ✓ Email de VERUMax
            </span>
        </p>
        <div class="bg-white rounded-lg p-4 text-sm">
            <p class="mb-3">Ir a <a href="https://app.sendgrid.com/settings/sender_auth/senders" target="_blank" class="text-blue-600 hover:underline font-medium">SendGrid → Create New Sender</a></p>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2 border border-gray-200">
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">From Name</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['nombre']) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">From Email</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['email_envio']) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">Reply To</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['email_envio']) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Company Address</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Av. Corrientes 1234</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">City / Country</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Buenos Aires, Argentina</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Nickname</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['codigo']) ?></code>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- Instrucciones para email propio -->
        <?php
        $email_propio_setup = $cliente['email_envio'] ?? '';
        $dominio_propio_setup = substr(strrchr($email_propio_setup, "@"), 1);
        ?>
        <p class="text-orange-700 text-sm mb-3">
            <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-100 text-amber-800 rounded text-xs font-medium">
                ⚠️ Email Propio - Requiere validación de dominio
            </span>
        </p>
        <div class="bg-white rounded-lg p-4 text-sm space-y-4">
            <!-- Paso 2.1: Autenticar dominio -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                <p class="font-semibold text-red-800 mb-2 text-sm">Paso 2.1: Autenticar Dominio</p>
                <ol class="list-decimal list-inside text-gray-600 space-y-1 text-xs">
                    <li>Ir a <a href="https://app.sendgrid.com/settings/sender_auth" target="_blank" class="text-blue-600 hover:underline">SendGrid → Sender Authentication</a></li>
                    <li>Clic en "Authenticate Your Domain"</li>
                    <li>Dominio: <code class="bg-red-100 text-red-800 px-1 rounded"><?= e($dominio_propio_setup) ?></code></li>
                    <li>Agregar registros DNS generados</li>
                </ol>
            </div>

            <!-- Paso 2.2: Crear Sender -->
            <div>
                <p class="font-semibold text-gray-700 mb-2 text-sm">Paso 2.2: Crear Sender</p>
                <p class="mb-2 text-xs">Ir a <a href="https://app.sendgrid.com/settings/sender_auth/senders" target="_blank" class="text-blue-600 hover:underline font-medium">SendGrid → Create New Sender</a></p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2 border border-gray-200">
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">From Name</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['nombre']) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">From Email</span>
                    <code class="col-span-2 bg-amber-100 text-amber-800 px-2 py-1 rounded text-xs"><?= e($email_propio_setup) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium">Reply To</span>
                    <code class="col-span-2 bg-amber-100 text-amber-800 px-2 py-1 rounded text-xs"><?= e($email_propio_setup) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Company Address</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Dirección del cliente</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">City / Country</span>
                    <code class="col-span-2 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">Ciudad, Argentina</code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600">Nickname</span>
                    <code class="col-span-2 bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs"><?= e($cliente['codigo']) ?></code>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (($cliente['tipo_email'] ?? 'verumax') === 'verumax'): ?>
    <!-- PASO 3: Crear Forwarder de Email (solo para email VERUMax) -->
    <div class="bg-cyan-50 border border-cyan-200 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <span class="flex items-center justify-center w-7 h-7 bg-cyan-600 text-white rounded-full text-sm font-bold">3</span>
            <h3 class="text-lg font-semibold text-cyan-800">Crear Forwarder de Email</h3>
        </div>
        <p class="text-cyan-700 text-sm mb-3">Para que el cliente reciba las respuestas a los emails de notificación:</p>
        <div class="bg-white rounded-lg p-4 text-sm space-y-3">
            <ol class="list-decimal list-inside text-gray-600 space-y-2">
                <li>Ir a <a href="https://verumax.com:2083" target="_blank" class="text-blue-600 hover:underline font-medium">cPanel</a> → <strong>Email</strong> → <strong>Forwarders</strong></li>
                <li>Clic en <strong>"Add Forwarder"</strong></li>
            </ol>
            <div class="bg-gray-50 rounded-lg p-3 space-y-2 border border-gray-200">
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium text-xs">Address to Forward</span>
                    <code class="col-span-2 bg-cyan-100 text-cyan-800 px-2 py-1 rounded text-xs"><?= e($cliente['email_envio']) ?></code>
                </div>
                <div class="grid grid-cols-3 gap-2 items-center">
                    <span class="text-gray-600 font-medium text-xs">Forward to Email</span>
                    <code class="col-span-2 bg-cyan-100 text-cyan-800 px-2 py-1 rounded text-xs"><?= e($cliente['email']) ?></code>
                </div>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 text-xs">
                <p class="text-blue-800 font-medium">💡 Webmail (casos especiales):</p>
                <p class="text-blue-600"><strong>URL:</strong> webmail.verumax.com | <strong>Usuario:</strong> <?= e($cliente['email_envio']) ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Checklist Funcional -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6" id="checklist-container" data-client-id="<?= $cliente['id'] ?>">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-blue-800">✅ Checklist de Configuración</h3>
            <div id="checklist-status" class="text-sm"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div class="space-y-3">
                <p class="font-semibold text-blue-700 border-b border-blue-200 pb-2">Tareas del Administrador VERUMax:</p>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="subdominio" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600" <?= !empty($checklist['subdominio']) ? 'checked' : '' ?>>
                    <span>Subdominio creado en cPanel</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="sendgrid" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600" <?= !empty($checklist['sendgrid']) ? 'checked' : '' ?>>
                    <span>Sender configurado en SendGrid</span>
                </label>
                <?php if (($cliente['tipo_email'] ?? 'verumax') === 'verumax'): ?>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="forwarder" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600" <?= !empty($checklist['forwarder']) ? 'checked' : '' ?>>
                    <span>Forwarder de email creado</span>
                </label>
                <?php endif; ?>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="ssl" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600" <?= !empty($checklist['ssl']) ? 'checked' : '' ?>>
                    <span>SSL activo (verificar https)</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="urls_verificadas" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600" <?= !empty($checklist['urls_verificadas']) ? 'checked' : '' ?>>
                    <span>Acceso verificado a las 3 URLs</span>
                </label>
            </div>

            <div class="space-y-3">
                <p class="font-semibold text-blue-700 border-b border-blue-200 pb-2">Tareas del Cliente (post-entrega):</p>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="cliente_acceso" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600" <?= !empty($checklist['cliente_acceso']) ? 'checked' : '' ?>>
                    <span>Acceder al Panel de Admin</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="cliente_branding" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600" <?= !empty($checklist['cliente_branding']) ? 'checked' : '' ?>>
                    <span>Configurar logo y colores</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="cliente_datos" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600" <?= !empty($checklist['cliente_datos']) ? 'checked' : '' ?>>
                    <span>Cargar cursos y estudiantes</span>
                </label>
                <label class="flex items-center gap-3 p-2 rounded hover:bg-blue-100 cursor-pointer transition">
                    <input type="checkbox" data-task="cliente_test" class="checklist-item w-5 h-5 rounded border-blue-300 text-blue-600" <?= !empty($checklist['cliente_test']) ? 'checked' : '' ?>>
                    <span>Probar emisión de certificados</span>
                </label>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-blue-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-blue-700">Progreso de configuración:</span>
                <span id="progress-text" class="text-sm font-medium text-blue-800">0/8 completado</span>
            </div>
            <div class="w-full bg-blue-200 rounded-full h-3">
                <div id="progress-bar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        <div id="ready-message" class="hidden mt-4 p-4 bg-green-100 border border-green-300 rounded-lg">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold text-green-800">¡Listo para entregar al cliente!</p>
                    <p class="text-sm text-green-700">Todas las tareas de configuración inicial están completadas.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between">
        <a href="clientes.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">
            ← Volver a la lista
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('checklist-container');
    if (!container) return;

    const clientId = container.dataset.clientId;
    const checkboxes = document.querySelectorAll('.checklist-item');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const readyMessage = document.getElementById('ready-message');
    const statusDiv = document.getElementById('checklist-status');
    const requiredTasks = ['subdominio', 'sendgrid', 'ssl', 'urls_verificadas'];

    function updateProgress() {
        const total = checkboxes.length;
        const checked = document.querySelectorAll('.checklist-item:checked').length;
        const percentage = Math.round((checked / total) * 100);

        progressBar.style.width = percentage + '%';
        progressText.textContent = checked + '/' + total + ' completado';

        const requiredComplete = requiredTasks.every(task => {
            const cb = document.querySelector(`[data-task="${task}"]`);
            return cb && cb.checked;
        });

        if (requiredComplete) {
            readyMessage.classList.remove('hidden');
            progressBar.classList.remove('bg-blue-600');
            progressBar.classList.add('bg-green-600');
        } else {
            readyMessage.classList.add('hidden');
            progressBar.classList.remove('bg-green-600');
            progressBar.classList.add('bg-blue-600');
        }
    }

    function saveChecklist() {
        const data = {};
        checkboxes.forEach(cb => { data[cb.dataset.task] = cb.checked; });

        statusDiv.innerHTML = '<span class="text-gray-500">Guardando...</span>';

        fetch('clientes.php?action=save_checklist', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + clientId + '&checklist=' + encodeURIComponent(JSON.stringify(data))
        })
        .then(r => r.json())
        .then(result => {
            statusDiv.innerHTML = result.success
                ? '<span class="text-green-600">✓ Guardado</span>'
                : '<span class="text-red-600">Error al guardar</span>';
            if (result.success) setTimeout(() => { statusDiv.innerHTML = ''; }, 2000);
        })
        .catch(() => { statusDiv.innerHTML = '<span class="text-red-600">Error de conexión</span>'; });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateProgress();
            saveChecklist();
        });
    });

    updateProgress();
});
</script>

<?php elseif ($action === 'delete'): ?>
<?php
try {
    // Obtener cliente a eliminar
    $id = (int)($_GET['id'] ?? 0);
    $cliente_delete = Database::queryOne("SELECT * FROM instances WHERE id_instancia = ?", [$id]);

    if (!$cliente_delete) {
        flash('error', 'Cliente no encontrado');
        redirect('clientes.php');
    }

$codigo = $cliente_delete['slug'];

// Verificar si está protegido
if (in_array($codigo, $CLIENTES_PROTEGIDOS)) {
    flash('error', 'Este cliente está protegido y no puede eliminarse');
    redirect('clientes.php');
}

// Recopilar datos a eliminar
$datos = [];

// Admin (está en la misma tabla instances, no en tabla separada)
$datos['admin'] = [
    'usuario' => $cliente_delete['admin_usuario'] ?? null,
    'email' => $cliente_delete['admin_email'] ?? null
];

// Miembros
try {
    $datos['miembros'] = Database::queryNexus("SELECT * FROM miembros WHERE institucion = ?", [$codigo]);
} catch (Exception $e) {
    $datos['miembros'] = [];
}

// Cursos
try {
    $datos['cursos'] = Database::queryAcademi("SELECT * FROM cursos WHERE id_instancia = ?", [$id]);
} catch (Exception $e) {
    $datos['cursos'] = [];
}

// Inscripciones
try {
    $datos['inscripciones'] = Database::queryAcademi("SELECT * FROM inscripciones WHERE id_instancia = ?", [$id]);
} catch (Exception $e) {
    $datos['inscripciones'] = [];
}

// Participaciones docentes
try {
    $datos['participaciones'] = Database::queryCertifi("SELECT * FROM participaciones_docentes WHERE id_instancia = ?", [$id]);
} catch (Exception $e) {
    $datos['participaciones'] = [];
}

// Certificados emitidos
try {
    $datos['certificados'] = Database::queryCertifi("SELECT * FROM certificados_emitidos WHERE id_instancia = ?", [$id]);
} catch (Exception $e) {
    $datos['certificados'] = [];
}

// Archivos
$carpeta = VERUMAX_ROOT_PATH . '/' . $codigo;
$archivos = is_dir($carpeta) ? listarArchivosRecursivo($carpeta) : [];
$totalArchivos = count(array_filter($archivos, fn($a) => $a['tipo'] === 'FILE'));
$totalCarpetas = count(array_filter($archivos, fn($a) => $a['tipo'] === 'DIR'));

} catch (Exception $e) {
    // Mostrar error si hay problema con la base de datos
    ?>
    <div class="max-w-2xl mx-auto">
        <div class="bg-red-50 border border-red-300 rounded-xl p-6">
            <h2 class="text-lg font-bold text-red-800 mb-2">Error al cargar datos</h2>
            <p class="text-red-700"><?= e($e->getMessage()) ?></p>
            <a href="clientes.php" class="inline-block mt-4 text-red-600 hover:text-red-800">← Volver a la lista</a>
        </div>
    </div>
    <?php
    include VERUMAX_ADMIN_PATH . '/includes/footer.php';
    exit;
}
?>

<!-- Vista de Confirmación de Eliminación -->
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="clientes.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Volver a la lista</span>
        </a>
    </div>

    <!-- Alerta de peligro -->
    <div class="bg-red-50 border-2 border-red-300 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-3 mb-2">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h2 class="text-xl font-bold text-red-800">Eliminar Cliente: <?= e($cliente_delete['nombre']) ?></h2>
        </div>
        <p class="text-red-700">Esta acción es <strong>IRREVERSIBLE</strong>. Se eliminarán permanentemente todos los datos mostrados a continuación.</p>
    </div>

    <!-- Info del cliente -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Instancia (1 registro)
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-500">ID:</span> <strong><?= $id ?></strong></div>
            <div><span class="text-gray-500">Código:</span> <code class="px-2 py-0.5 bg-gray-100 rounded"><?= e($codigo) ?></code></div>
            <div><span class="text-gray-500">Nombre:</span> <strong><?= e($cliente_delete['nombre']) ?></strong></div>
            <div><span class="text-gray-500">Email:</span> <?= e($cliente_delete['email_contacto'] ?? 'N/A') ?></div>
        </div>
    </div>

    <!-- Admin -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Administrador del Cliente
        </h3>
        <?php if ($datos['admin']['usuario']): ?>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Usuario:</span> <code class="px-2 py-0.5 bg-gray-100 rounded"><?= e($datos['admin']['usuario']) ?></code></div>
            <div><span class="text-gray-500">Email:</span> <?= e($datos['admin']['email'] ?? 'N/A') ?></div>
        </div>
        <?php else: ?>
        <p class="text-gray-500 text-sm">Sin administrador configurado</p>
        <?php endif; ?>
    </div>

    <!-- Miembros -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Miembros (<?= count($datos['miembros']) ?> registros)
        </h3>
        <?php if (count($datos['miembros']) > 0): ?>
        <div class="overflow-x-auto max-h-64 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">ID</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">DNI</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Nombre</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Tipo</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Email</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($datos['miembros'] as $m): ?>
                    <?php $doc = $m['dni'] ?? $m['documento'] ?? $m['numero_documento'] ?? 'N/A'; ?>
                    <tr>
                        <td class="px-3 py-2"><?= $m['id_miembro'] ?? $m['id'] ?? 'N/A' ?></td>
                        <td class="px-3 py-2"><code><?= e($doc) ?></code></td>
                        <td class="px-3 py-2"><?= e(($m['nombre'] ?? '') . ' ' . ($m['apellido'] ?? '')) ?></td>
                        <td class="px-3 py-2">
                            <span class="px-2 py-0.5 rounded-full text-xs <?= ($m['tipo'] ?? '') === 'docente' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                <?= e($m['tipo'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td class="px-3 py-2 truncate max-w-[150px]"><?= e($m['email'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-gray-500 text-sm">Sin registros</p>
        <?php endif; ?>
    </div>

    <!-- Cursos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Cursos (<?= count($datos['cursos']) ?> registros)
        </h3>
        <?php if (count($datos['cursos']) > 0): ?>
        <div class="overflow-x-auto max-h-64 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">ID</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Código</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Nombre</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Inicio</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($datos['cursos'] as $c): ?>
                    <tr>
                        <td class="px-3 py-2"><?= $c['id_curso'] ?? $c['id'] ?? 'N/A' ?></td>
                        <td class="px-3 py-2"><code><?= e($c['codigo_curso'] ?? $c['codigo'] ?? 'N/A') ?></code></td>
                        <td class="px-3 py-2"><?= e($c['nombre_curso'] ?? $c['nombre'] ?? 'N/A') ?></td>
                        <td class="px-3 py-2"><?= e($c['fecha_inicio'] ?? 'N/A') ?></td>
                        <td class="px-3 py-2"><?= e($c['estado'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-gray-500 text-sm">Sin registros</p>
        <?php endif; ?>
    </div>

    <!-- Inscripciones y Participaciones -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Inscripciones</h3>
            <p class="text-3xl font-bold text-blue-600"><?= count($datos['inscripciones']) ?></p>
            <p class="text-sm text-gray-500">registros a eliminar</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Participaciones Docentes</h3>
            <p class="text-3xl font-bold text-purple-600"><?= count($datos['participaciones']) ?></p>
            <p class="text-sm text-gray-500">registros a eliminar</p>
        </div>
    </div>

    <!-- Certificados -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            Certificados Emitidos (<?= count($datos['certificados']) ?> registros)
        </h3>
        <?php if (count($datos['certificados']) > 0): ?>
        <div class="overflow-x-auto max-h-48 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">ID</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Código</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Tipo</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">DNI</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($datos['certificados'] as $cert): ?>
                    <tr>
                        <td class="px-3 py-2"><?= $cert['id_certificado'] ?? $cert['id'] ?? 'N/A' ?></td>
                        <td class="px-3 py-2"><code class="text-xs"><?= e($cert['codigo_validacion'] ?? $cert['codigo'] ?? 'N/A') ?></code></td>
                        <td class="px-3 py-2"><?= e($cert['tipo_documento'] ?? $cert['tipo'] ?? 'N/A') ?></td>
                        <td class="px-3 py-2"><?= e($cert['dni_beneficiario'] ?? $cert['dni'] ?? 'N/A') ?></td>
                        <td class="px-3 py-2"><?= e(substr($cert['fecha_emision'] ?? $cert['fecha'] ?? '', 0, 10)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p class="text-gray-500 text-sm">Sin registros</p>
        <?php endif; ?>
    </div>

    <!-- Archivos -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            Carpeta Física: /<?= e($codigo) ?>/ (<?= $totalArchivos ?> archivos, <?= $totalCarpetas ?> carpetas)
        </h3>
        <?php if (count($archivos) > 0): ?>
        <div class="max-h-48 overflow-y-auto bg-gray-50 rounded-lg p-3 font-mono text-xs">
            <?php foreach ($archivos as $archivo): ?>
            <div class="flex items-center gap-2 py-0.5">
                <?php if ($archivo['tipo'] === 'DIR'): ?>
                <span class="text-yellow-600">📁</span>
                <span class="text-gray-700"><?= e($archivo['ruta']) ?></span>
                <?php else: ?>
                <span class="text-gray-400">📄</span>
                <span class="text-gray-600"><?= e($archivo['ruta']) ?></span>
                <span class="text-gray-400">(<?= $archivo['size'] ?>)</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-gray-500 text-sm">La carpeta no existe o está vacía</p>
        <?php endif; ?>
    </div>

    <!-- Resumen Total -->
    <div class="bg-red-50 border-2 border-red-200 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-bold text-red-800 mb-4">Resumen Total a Eliminar</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="bg-white rounded-lg p-3">
                <p class="text-2xl font-bold text-red-600"><?= count($datos['miembros']) ?></p>
                <p class="text-xs text-gray-500">Miembros</p>
            </div>
            <div class="bg-white rounded-lg p-3">
                <p class="text-2xl font-bold text-red-600"><?= count($datos['cursos']) ?></p>
                <p class="text-xs text-gray-500">Cursos</p>
            </div>
            <div class="bg-white rounded-lg p-3">
                <p class="text-2xl font-bold text-red-600"><?= count($datos['inscripciones']) ?></p>
                <p class="text-xs text-gray-500">Inscripciones</p>
            </div>
            <div class="bg-white rounded-lg p-3">
                <p class="text-2xl font-bold text-red-600"><?= count($datos['participaciones']) ?></p>
                <p class="text-xs text-gray-500">Participaciones</p>
            </div>
            <div class="bg-white rounded-lg p-3">
                <p class="text-2xl font-bold text-red-600"><?= count($datos['certificados']) ?></p>
                <p class="text-xs text-gray-500">Certificados</p>
            </div>
            <div class="bg-white rounded-lg p-3">
                <p class="text-2xl font-bold text-red-600"><?= $totalArchivos ?></p>
                <p class="text-xs text-gray-500">Archivos</p>
            </div>
            <div class="bg-white rounded-lg p-3">
                <p class="text-2xl font-bold text-red-600"><?= $totalCarpetas ?></p>
                <p class="text-xs text-gray-500">Carpetas</p>
            </div>
        </div>
    </div>

    <!-- Formulario de confirmación -->
    <div class="bg-white rounded-xl shadow-sm border-2 border-red-300 p-6">
        <form method="POST" action="?action=delete_confirm" onsubmit="return confirmarEliminacion()">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="codigo" value="<?= e($codigo) ?>">

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Para confirmar, escriba el código del cliente: <strong class="text-red-600"><?= e($codigo) ?></strong>
                </label>
                <input type="text" id="confirmacion_codigo" required
                       class="w-full max-w-xs px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                       placeholder="Escriba el código aquí">
            </div>

            <div class="flex justify-between items-center">
                <a href="clientes.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium">
                    Eliminar Cliente Permanentemente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmarEliminacion() {
    const codigo = '<?= e($codigo) ?>';
    const input = document.getElementById('confirmacion_codigo').value.trim();

    if (input !== codigo) {
        alert('El código ingresado no coincide. Escriba exactamente: ' + codigo);
        return false;
    }

    return confirm('¿Está ABSOLUTAMENTE seguro? Esta acción NO se puede deshacer.');
}
</script>

<?php endif; ?>

<?php include VERUMAX_ADMIN_PATH . '/includes/footer.php'; ?>
