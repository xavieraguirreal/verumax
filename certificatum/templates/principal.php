<?php
/**
 * TEMPLATE: Certificatum como Página Principal
 * Muestra el portal de certificados como homepage de la institución
 */
?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <!-- Logo y título -->
        <div class="text-center mb-8">
            <?php if (!empty($instance['logo_url'])): ?>
                <img src="<?php echo htmlspecialchars($instance['logo_url']); ?>"
                     alt="Logo <?php echo htmlspecialchars($instance['nombre']); ?>"
                     class="<?php echo getLogoClasses($instance['logo_estilo'] ?? 'rectangular', 'h-24'); ?> mx-auto mb-4 shadow-lg">
            <?php else: ?>
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-2xl mb-4 shadow-lg"
                     style="background: linear-gradient(135deg, <?php echo htmlspecialchars($instance['color_primario']); ?> 0%, <?php echo htmlspecialchars($instance['color_primario']); ?>dd 100%);">
                    <i data-lucide="award" class="w-16 h-16 text-white"></i>
                </div>
            <?php endif; ?>

            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                <?php echo htmlspecialchars($instance['nombre']); ?>
            </h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">
                Portal de Certificados
            </h2>
            <?php if ($instance['nombre_completo']): ?>
                <p class="text-gray-600"><?php echo htmlspecialchars($instance['nombre_completo']); ?></p>
            <?php endif; ?>
        </div>

        <!-- Formulario de DNI -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-200">
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="documentum" class="block text-lg font-semibold text-gray-700 mb-3">
                        <i data-lucide="user" class="w-5 h-5 inline mr-2"></i>
                        Ingrese su número de DNI
                    </label>
                    <input
                        type="text"
                        id="documentum"
                        name="documentum"
                        placeholder="Ej: 25123456"
                        required
                        pattern="[0-9]{7,15}"
                        autofocus
                        class="w-full px-6 py-4 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-opacity-50 focus:border-transparent text-xl text-center font-semibold tracking-wider transition-all"
                        style="focus:ring-color: <?php echo htmlspecialchars($instance['color_primario']); ?>;"
                    >
                    <p class="mt-3 text-sm text-gray-500 text-center">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                        Solo números, sin puntos ni espacios
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full text-white font-bold py-4 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-lg"
                    style="background: linear-gradient(135deg, <?php echo htmlspecialchars($instance['color_primario']); ?> 0%, <?php echo htmlspecialchars($instance['color_primario']); ?>dd 100%);">
                    <i data-lucide="search" class="w-6 h-6 inline mr-2"></i>
                    Ver mis certificados
                </button>
            </form>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'not_found'): ?>
                <div class="mt-6 bg-red-50 border-2 border-red-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="alert-circle" class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-red-800">No se encontraron certificados</p>
                            <p class="text-sm text-red-600 mt-1">
                                No se encontraron certificados asociados al DNI ingresado.
                                Por favor, verifique que el número sea correcto o contacte a la institución.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Enlaces adicionales -->
        <div class="mt-8 text-center space-y-3">
            <p class="text-sm text-gray-600">
                <i data-lucide="shield-check" class="w-4 h-4 inline mr-1"></i>
                Portal seguro de certificados digitales verificables
            </p>

            <div class="flex justify-center gap-4 text-sm">
                <?php if ($instance['config']['sitio_web_oficial']): ?>
                    <a href="<?php echo htmlspecialchars($instance['config']['sitio_web_oficial']); ?>"
                       target="_blank"
                       class="text-gray-700 hover:text-gray-900 font-medium">
                        <i data-lucide="external-link" class="w-4 h-4 inline mr-1"></i>
                        Sitio oficial
                    </a>
                <?php endif; ?>

                <?php if (!empty($paginas)): ?>
                    <a href="?page=sobre-nosotros"
                       class="text-gray-700 hover:text-gray-900 font-medium">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                        Sobre nosotros
                    </a>
                <?php endif; ?>

                <?php if ($instance['config']['email_contacto']): ?>
                    <a href="mailto:<?php echo htmlspecialchars($instance['config']['email_contacto']); ?>"
                       class="text-gray-700 hover:text-gray-900 font-medium">
                        <i data-lucide="mail" class="w-4 h-4 inline mr-1"></i>
                        Contacto
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información adicional -->
        <?php if ($instance['config']['mision']): ?>
            <div class="mt-8 bg-white rounded-xl p-6 shadow-md">
                <p class="text-gray-700 text-center leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($instance['config']['mision'])); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    lucide.createIcons();

    // Auto-focus en el campo DNI
    document.getElementById('documentum').focus();
</script>
