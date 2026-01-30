<?php
/**
 * ADMIN UNIFICADO - Login
 * Un solo login para todos los módulos (Identitas, Certificatum, Scripta, etc.)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../env_loader.php';

use VERUMax\Services\DatabaseService;

$error = '';

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['admin_verumax'])) {
    header('Location: index.php');
    exit;
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        try {
            // Usar verumax_general.instances como fuente única de verdad
            $pdo = DatabaseService::get('general');

            $stmt = $pdo->prepare("
                SELECT
                    id_instancia, slug, nombre, nombre_completo,
                    admin_usuario, admin_password, admin_email,
                    modulo_certificatum, modulo_scripta, modulo_nexus,
                    modulo_vitae, modulo_lumen, modulo_opera, modulo_credencialis
                FROM instances
                WHERE admin_usuario = :usuario
                AND activo = 1
            ");

            $stmt->execute(['usuario' => $usuario]);
            $instance = $stmt->fetch();

            if ($instance && password_verify($password, $instance['admin_password'])) {
                // Login exitoso - Guardar datos en sesión
                $_SESSION['admin_verumax'] = [
                    'id_instancia' => $instance['id_instancia'],
                    'slug' => $instance['slug'],
                    'nombre' => $instance['nombre'],
                    'nombre_completo' => $instance['nombre_completo'],
                    'usuario' => $instance['admin_usuario'],
                    'email' => $instance['admin_email'],
                    'modulos' => [
                        'certificatum' => (bool)$instance['modulo_certificatum'],
                        'scripta' => (bool)$instance['modulo_scripta'],
                        'nexus' => (bool)$instance['modulo_nexus'],
                        'vitae' => (bool)$instance['modulo_vitae'],
                        'lumen' => (bool)$instance['modulo_lumen'],
                        'opera' => (bool)$instance['modulo_opera'],
                        'credencialis' => (bool)($instance['modulo_credencialis'] ?? 0),
                    ]
                ];

                // También configurar sesión legacy para Certificatum
                $_SESSION['admin_authenticated'] = true;
                $_SESSION['admin_institucion'] = $instance['slug'];

                header('Location: index.php');
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            $error = 'Error de conexión. Intente nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin - VERUMax</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <!-- Logo / Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="shield-check" class="w-10 h-10 text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">VERUMax Admin</h1>
            <p class="text-gray-600 mt-2">Panel de Administración Unificado</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Iniciar sesión</h2>

            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center gap-2 text-red-800">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                        <span class="text-sm"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="usuario" class="block text-sm font-medium text-gray-700 mb-2">
                        Usuario
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input
                            type="text"
                            id="usuario"
                            name="usuario"
                            required
                            autofocus
                            autocomplete="username"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="admin@cliente"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="••••••••"
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all inline-flex items-center justify-center gap-2 shadow-lg"
                >
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    Ingresar al Panel
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-center gap-2 text-xs text-gray-500">
                    <i data-lucide="info" class="w-3 h-3"></i>
                    <span>Un login para todos tus módulos</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-sm text-gray-600">
                Powered by <strong class="text-gray-900">VERUMax</strong>
            </p>
            <p class="text-xs text-gray-500 mt-2">
                Sistema de gestión empresarial integral
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
