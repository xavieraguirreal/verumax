<?php
/**
 * VERUMAX SUPER ADMIN - Login 2FA (Paso 2)
 *
 * Verificación del código TOTP.
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
$userId = $_SESSION['superadmin_id'] ?? null;

// Obtener datos del usuario para verificar si tiene 2FA configurado
$user = Database::queryOne(
    "SELECT id, username, nombre, totp_secret, totp_habilitado FROM super_admins WHERE id = ?",
    [$userId]
);

if (!$user) {
    // Usuario no existe, limpiar sesión
    Auth::logout();
    redirect('login.php');
}

// Si no tiene 2FA habilitado, redirigir a configuración
if (!$user['totp_habilitado'] || empty($user['totp_secret'])) {
    redirect('setup_2fa.php');
}

// Procesar verificación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Sesión expirada. Recargue la página.';
    } else {
        $code = preg_replace('/\s+/', '', $_POST['code'] ?? '');

        if (empty($code) || strlen($code) !== 6) {
            $error = 'Ingrese un código de 6 dígitos.';
        } else {
            if (Auth::validateTOTP($user['totp_secret'], $code)) {
                // 2FA verificado exitosamente
                Auth::mark2FAVerified();
                redirect('index.php');
            } else {
                $error = 'Código incorrecto. Intente nuevamente.';
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
    <title>Verificación 2FA - <?= e(VERUMAX_ADMIN_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Estilo para inputs de código */
        .code-input {
            letter-spacing: 0.5em;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-600 rounded-2xl mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white"><?= e(VERUMAX_ADMIN_NAME) ?></h1>
            <p class="text-purple-300 mt-1">Verificación de Seguridad</p>
        </div>

        <!-- 2FA Card -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 shadow-2xl border border-white/20">
            <div class="text-center mb-6">
                <h2 class="text-xl font-semibold text-white">Autenticación de Dos Factores</h2>
                <p class="text-purple-200/70 mt-2 text-sm">
                    Ingrese el código de 6 dígitos de su aplicación autenticadora
                </p>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-lg mb-6">
                <?= e($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <?= csrf_field() ?>

                <div>
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
                        autofocus
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition duration-200 flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Verificar</span>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-white/10">
                <a href="login.php?logout=1" class="flex items-center justify-center gap-2 text-purple-300/70 hover:text-purple-200 text-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                    </svg>
                    <span>Volver al inicio</span>
                </a>
            </div>

            <p class="text-center text-purple-300/60 text-sm mt-4">
                Paso 2 de 2 - Verificación 2FA
            </p>
        </div>

        <!-- Help text -->
        <div class="text-center mt-6">
            <p class="text-purple-400/50 text-xs">
                Use Google Authenticator, Authy u otra app compatible con TOTP
            </p>
        </div>
    </div>

    <script>
        // Auto-submit cuando se ingresan 6 dígitos
        document.querySelector('input[name="code"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>

</body>
</html>
