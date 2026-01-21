<?php
/**
 * VERUMAX SUPER ADMIN - Login (Paso 1)
 *
 * Autenticación de usuario y contraseña.
 * Si es exitoso, redirige a 2FA.
 */

require_once __DIR__ . '/config.php';
require_once VERUMAX_ADMIN_PATH . '/includes/auth.php';

use VERUMaxAdmin\Auth;

// Si viene de "volver al inicio", limpiar sesión
if (isset($_GET['logout'])) {
    Auth::logout();
    redirect('login.php');
}

// Si ya está autenticado, ir al dashboard
if (is_authenticated()) {
    redirect('index.php');
}

// Si pasó paso 1, ir a 2FA
if (is_login_step1_complete()) {
    redirect('login_2fa.php');
}

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!csrf_validate($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Sesión expirada. Recargue la página.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Complete todos los campos.';
        } else {
            $user = Auth::validateCredentials($username, $password);

            if ($user) {
                // Crear sesión pendiente de 2FA
                Auth::createSession($user, false);
                redirect('login_2fa.php');
            } else {
                $error = 'Usuario o contraseña incorrectos.';
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
    <title>Login - <?= e(VERUMAX_ADMIN_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-600 rounded-2xl mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white"><?= e(VERUMAX_ADMIN_NAME) ?></h1>
            <p class="text-purple-300 mt-1">Panel de Administración</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 shadow-2xl border border-white/20">
            <h2 class="text-xl font-semibold text-white mb-6">Iniciar Sesión</h2>

            <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-lg mb-6">
                <?= e($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-sm font-medium text-purple-200 mb-2">Usuario</label>
                    <input
                        type="text"
                        name="username"
                        required
                        autocomplete="username"
                        class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-purple-300/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        placeholder="Tu usuario"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-purple-200 mb-2">Contraseña</label>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-purple-300/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        placeholder="Tu contraseña"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition duration-200 flex items-center justify-center gap-2"
                >
                    <span>Continuar</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </form>

            <p class="text-center text-purple-300/60 text-sm mt-6">
                Paso 1 de 2 - Verificación de credenciales
            </p>
        </div>

        <!-- Footer -->
        <p class="text-center text-purple-400/50 text-sm mt-6">
            VERUMax v<?= VERUMAX_ADMIN_VERSION ?>
        </p>
    </div>

</body>
</html>
