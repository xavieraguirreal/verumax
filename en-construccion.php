<?php
/**
 * PÁGINA: SITIO EN CONSTRUCCIÓN
 *
 * Esta página se muestra cuando el sitio está en modo construcción
 */

// Obtener el slug del parámetro GET
$slug = $_GET['institucion'] ?? 'sajur';

// Cargar configuración (ajuste de ruta para soportar subdominios)
if (file_exists(__DIR__ . '/identitas/config.php')) {
    // Estamos en la raíz
    require_once __DIR__ . '/identitas/config.php';
} else {
    // Estamos en una carpeta de cliente (subdominio)
    require_once __DIR__ . '/../identitas/config.php';
}

$instance = getInstanceConfig($slug);

if (!$instance) {
    die('Error: Instancia no encontrada');
}

// Obtener colores de la paleta
$color_primario = $instance['color_primario'] ?? '#2E7D32';
$color_secundario = $instance['color_secundario'] ?? '#1B5E20';
$color_acento = $instance['color_acento'] ?? '#66BB6A';

// Mensaje personalizado o por defecto
$mensaje = $instance['mensaje_construccion'] ?? 'Estamos trabajando en mejorar tu experiencia. Pronto volveremos con novedades.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Sitio en Construcción - <?php echo htmlspecialchars($instance['nombre']); ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Favicon -->
    <?php if (!empty($instance['favicon_url'])): ?>
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo htmlspecialchars($instance['favicon_url']); ?>">
        <link rel="apple-touch-icon" href="<?php echo htmlspecialchars($instance['favicon_url']); ?>">
    <?php endif; ?>

    <style>
        .animated-gradient {
            background: linear-gradient(-45deg, <?php echo $color_primario; ?>, <?php echo $color_secundario; ?>, <?php echo $color_acento; ?>, <?php echo $color_primario; ?>);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .bounce-slow {
            animation: bounce 3s infinite;
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.95);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.7;
            }
            100% {
                transform: scale(0.95);
                opacity: 1;
            }
        }

        .pulse-ring {
            animation: pulse-ring 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center animated-gradient">
    <div class="max-w-4xl mx-auto px-4 py-16">
        <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12 text-center">

            <!-- Logo -->
            <?php if (!empty($instance['logo_url'])): ?>
                <div class="flex justify-center mb-8">
                    <img src="<?php echo htmlspecialchars($instance['logo_url']); ?>"
                         alt="Logo <?php echo htmlspecialchars($instance['nombre']); ?>"
                         class="h-24 md:h-32 w-auto object-contain pulse-ring">
                </div>
            <?php endif; ?>

            <!-- Icono de Construcción -->
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <i data-lucide="hard-hat" class="w-24 h-24 bounce-slow" style="color: <?php echo $color_primario; ?>"></i>
                </div>
            </div>

            <!-- Título -->
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                Sitio en Construcción
            </h1>

            <!-- Nombre de la institución -->
            <h2 class="text-2xl md:text-3xl font-semibold mb-6" style="color: <?php echo $color_primario; ?>">
                <?php echo htmlspecialchars($instance['nombre_completo'] ?? $instance['nombre']); ?>
            </h2>

            <!-- Mensaje personalizado -->
            <div class="max-w-2xl mx-auto mb-8">
                <p class="text-lg md:text-xl text-gray-700 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($mensaje)); ?>
                </p>
            </div>

            <!-- Progreso visual -->
            <div class="max-w-md mx-auto mb-8">
                <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div class="h-full rounded-full animated-gradient" style="width: 75%;"></div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Estamos trabajando duro para volver pronto</p>
            </div>

            <!-- Información de contacto -->
            <?php if (!empty($instance['email_contacto']) || !empty($instance['sitio_web_oficial'])): ?>
                <div class="border-t border-gray-200 pt-8 mt-8">
                    <p class="text-sm text-gray-600 mb-4">¿Necesitás comunicarte con nosotros?</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <?php if (!empty($instance['email_contacto'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($instance['email_contacto']); ?>"
                               class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-white hover:opacity-90 transition"
                               style="background-color: <?php echo $color_primario; ?>">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                                Enviar email
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($instance['sitio_web_oficial'])): ?>
                            <a href="<?php echo htmlspecialchars($instance['sitio_web_oficial']); ?>"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-6 py-3 border-2 rounded-lg hover:bg-gray-50 transition"
                               style="border-color: <?php echo $color_primario; ?>; color: <?php echo $color_primario; ?>">
                                <i data-lucide="external-link" class="w-5 h-5"></i>
                                Sitio web oficial
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Redes sociales -->
            <?php
            $redes = [];
            if (!empty($instance['redes_sociales'])) {
                $redes = json_decode($instance['redes_sociales'], true) ?: [];
            }
            $redes_activas = array_filter($redes, function($url) { return !empty($url); });
            ?>

            <?php if (!empty($redes_activas)): ?>
                <div class="border-t border-gray-200 pt-8 mt-8">
                    <p class="text-sm text-gray-600 mb-4">Seguinos en nuestras redes</p>
                    <div class="flex gap-4 justify-center">
                        <?php if (!empty($redes['facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['facebook']); ?>" target="_blank"
                               class="w-12 h-12 rounded-full flex items-center justify-center hover:opacity-80 transition"
                               style="background-color: <?php echo $color_primario; ?>">
                                <i data-lucide="facebook" class="w-6 h-6 text-white"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($redes['instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['instagram']); ?>" target="_blank"
                               class="w-12 h-12 rounded-full flex items-center justify-center hover:opacity-80 transition"
                               style="background-color: <?php echo $color_primario; ?>">
                                <i data-lucide="instagram" class="w-6 h-6 text-white"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($redes['linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['linkedin']); ?>" target="_blank"
                               class="w-12 h-12 rounded-full flex items-center justify-center hover:opacity-80 transition"
                               style="background-color: <?php echo $color_primario; ?>">
                                <i data-lucide="linkedin" class="w-6 h-6 text-white"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($redes['twitter'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['twitter']); ?>" target="_blank"
                               class="w-12 h-12 rounded-full flex items-center justify-center hover:opacity-80 transition"
                               style="background-color: <?php echo $color_primario; ?>">
                                <i data-lucide="twitter" class="w-6 h-6 text-white"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($redes['youtube'])): ?>
                            <a href="<?php echo htmlspecialchars($redes['youtube']); ?>" target="_blank"
                               class="w-12 h-12 rounded-full flex items-center justify-center hover:opacity-80 transition"
                               style="background-color: <?php echo $color_primario; ?>">
                                <i data-lucide="youtube" class="w-6 h-6 text-white"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-green-600"></i>
                        <span>Conexión Segura SSL</span>
                    </div>
                    <div class="hidden sm:block text-gray-300">|</div>
                    <div class="flex items-center gap-2">
                        <span>Powered by</span>
                        <strong style="color: <?php echo $color_primario; ?>">VERUMax</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inicializar iconos
        lucide.createIcons();
    </script>
</body>
</html>
