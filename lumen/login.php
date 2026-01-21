<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Credenciales hardcodeadas (temporal - migrar a BD)
$usuarios_lumen = [
    'fotosjuan' => [
        'usuario' => 'juan@fotosjuan.com',
        'password' => password_hash('fotosjuan2025', PASSWORD_DEFAULT),
        'nombre' => 'Juan Martínez',
        'cliente_id' => 'fotosjuan'
    ]
    // Agregar más usuarios aquí según sea necesario
];

$error_login = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Buscar usuario
    $usuario_encontrado = null;
    foreach ($usuarios_lumen as $cliente_id => $datos) {
        if ($datos['usuario'] === $email) {
            $usuario_encontrado = $datos;
            break;
        }
    }

    // Verificar credenciales
    if ($usuario_encontrado && password_verify($password, $usuario_encontrado['password'])) {
        // Login exitoso
        $_SESSION['lumen_logged_in'] = true;
        $_SESSION['lumen_user'] = $usuario_encontrado['usuario'];
        $_SESSION['lumen_nombre'] = $usuario_encontrado['nombre'];
        $_SESSION['lumen_cliente_id'] = $usuario_encontrado['cliente_id'];

        // Debug
        error_log("Login exitoso para: " . $usuario_encontrado['nombre']);

        header('Location: dashboard.php');
        exit;
    } else {
        $error_login = 'Credenciales incorrectas';
        error_log("Login fallido para email: " . $email);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceder a Lumen | OriginalisDoc</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2 {
            font-family: 'Playfair Display', serif;
        }

        .gradient-lumen {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="index.php" class="inline-flex items-center gap-2 text-purple-700 hover:text-purple-900 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span class="text-sm font-medium">Volver a Lumen</span>
            </a>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="gradient-lumen p-8 text-white text-center">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="sparkles" class="w-8 h-8"></i>
                </div>
                <h1 class="text-2xl font-bold mb-2">Bienvenido a Lumen</h1>
                <p class="text-purple-100 text-sm">Accede a tu dashboard para gestionar tu portfolio</p>
            </div>

            <!-- Form -->
            <div class="p-8">
                <?php if ($error_login): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm text-red-800"><?php echo htmlspecialchars($error_login); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                                placeholder="tu@email.com"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
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
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                                placeholder="••••••••"
                            >
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        name="login"
                        class="w-full py-3 gradient-lumen text-white font-bold rounded-lg hover:opacity-90 transition-all shadow-lg hover:shadow-xl"
                    >
                        Acceder a Dashboard
                    </button>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-8 p-4 bg-purple-50 border border-purple-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm">
                            <div class="font-semibold text-purple-900 mb-2">Credenciales de Demo:</div>
                            <div class="space-y-1 text-purple-700">
                                <div><strong>Email:</strong> juan@fotosjuan.com</div>
                                <div><strong>Contraseña:</strong> fotosjuan2025</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                ¿No tienes cuenta?
                <a href="../index.html" class="text-purple-700 font-semibold hover:text-purple-900 transition-colors">
                    Contratar Lumen
                </a>
            </p>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
