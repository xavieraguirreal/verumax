<?php
/**
 * VERUMAX SUPER ADMIN - Configuración inicial 2FA
 *
 * Permite configurar 2FA por primera vez.
 */

require_once __DIR__ . '/config.php';
require_once VERUMAX_ADMIN_PATH . '/includes/auth.php';

use VERUMaxAdmin\Auth;
use VERUMaxAdmin\Database;

// Si ya está autenticado completamente, ir al dashboard
if (is_authenticated()) {
    redirect('index.php');
}

// Si no pasó paso 1, volver al login
if (!is_login_step1_complete()) {
    redirect('login.php');
}

$error = '';
$success = false;
$userId = $_SESSION['superadmin_id'] ?? null;
$username = $_SESSION['superadmin_username'] ?? '';

// Obtener usuario
$user = Database::queryOne(
    "SELECT id, username, nombre, totp_secret, totp_habilitado FROM super_admins WHERE id = ?",
    [$userId]
);

if (!$user) {
    Auth::logout();
    redirect('login.php');
}

// Si ya tiene 2FA, ir a verificación
if ($user['totp_habilitado'] && !empty($user['totp_secret'])) {
    redirect('login_2fa.php');
}

// Generar o recuperar secret temporal de la sesión
if (!isset($_SESSION['temp_totp_secret'])) {
    $_SESSION['temp_totp_secret'] = Auth::generateSecret();
}
$secret = $_SESSION['temp_totp_secret'];

// Generar QR
$label = TOTP_ISSUER . ':' . $username;
$qrUrl = Auth::getQRCodeUrl($label, $secret);

// Procesar verificación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Sesión expirada. Recargue la página.';
    } else {
        $code = preg_replace('/\s+/', '', $_POST['code'] ?? '');

        if (empty($code) || strlen($code) !== 6) {
            $error = 'Ingrese un código de 6 dígitos.';
        } else {
            if (Auth::validateTOTP($secret, $code)) {
                // Guardar secret y habilitar 2FA
                Database::execute(
                    "UPDATE super_admins SET totp_secret = ?, totp_habilitado = 1 WHERE id = ?",
                    [$secret, $userId]
                );

                // Limpiar secret temporal
                unset($_SESSION['temp_totp_secret']);

                // Marcar 2FA como verificado
                Auth::mark2FAVerified();

                $success = true;
            } else {
                $error = 'Código incorrecto. Verifique su aplicación e intente nuevamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar 2FA - <?= e(VERUMAX_ADMIN_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .code-input {
            letter-spacing: 0.5em;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex items-center justify-center p-4">

    <div class="w-full max-w-lg">

        <?php if ($success): ?>
        <!-- Success Message -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-green-600 rounded-full mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">2FA Configurado</h1>
            <p class="text-purple-200 mb-8">Su autenticación de dos factores ha sido activada correctamente.</p>
            <a href="index.php" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition">
                <span>Ir al Dashboard</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>

        <?php else: ?>
        <!-- Setup Form -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-600 rounded-2xl mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Configurar 2FA</h1>
            <p class="text-purple-300 mt-1">Proteja su cuenta con autenticación de dos factores</p>
        </div>

        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 shadow-2xl border border-white/20">

            <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-lg mb-6">
                <?= e($error) ?>
            </div>
            <?php endif; ?>

            <!-- Instructions -->
            <div class="mb-6">
                <h3 class="text-white font-medium mb-3">Instrucciones:</h3>
                <ol class="text-purple-200/80 text-sm space-y-2 list-decimal list-inside">
                    <li>Instale una app autenticadora (Google Authenticator, Authy, etc.)</li>
                    <li>Escanee el código QR con la aplicación</li>
                    <li>Ingrese el código de 6 dígitos que aparece</li>
                </ol>
            </div>

            <!-- QR Code -->
            <div class="bg-white rounded-xl p-4 mb-6 flex justify-center">
                <img src="<?= e($qrUrl) ?>" alt="Código QR para 2FA" class="w-48 h-48">
            </div>

            <!-- Manual Entry -->
            <div class="bg-white/5 rounded-lg p-4 mb-6">
                <p class="text-purple-200/70 text-xs mb-2">Si no puede escanear, ingrese este código manualmente:</p>
                <code class="block text-center text-white font-mono text-lg tracking-widest bg-white/10 rounded px-4 py-2">
                    <?= e(chunk_split($secret, 4, ' ')) ?>
                </code>
            </div>

            <!-- Verification Form -->
            <form method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-sm font-medium text-purple-200 mb-2">
                        Código de verificación
                    </label>
                    <input
                        type="text"
                        name="code"
                        required
                        autocomplete="one-time-code"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        class="code-input w-full px-4 py-4 bg-white/10 border border-white/20 rounded-lg text-white placeholder-purple-300/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        placeholder="000000"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition duration-200 flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>Activar 2FA</span>
                </button>
            </form>
        </div>

        <p class="text-center text-purple-400/50 text-xs mt-6">
            El 2FA es obligatorio para acceder al Super Admin
        </p>
        <?php endif; ?>
    </div>

    <script>
        document.querySelector('input[name="code"]')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    </script>

</body>
</html>
