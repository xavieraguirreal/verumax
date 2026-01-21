<?php
/**
 * COMPONENTE: FOOTER CON BADGES DE SEGURIDAD Y BRANDING
 *
 * Este componente muestra badges de seguridad SSL y branding VERUMax
 * Reutilizable en todos los templates
 *
 * Uso:
 * require_once __DIR__ . '/componentes/footer-badges.php';
 * renderFooterBadges($instance);
 */

function renderFooterBadges($instance) {
    $color_primario = $instance['color_primario'] ?? '#2E7D32';
    $nombre = $instance['nombre'] ?? 'Institución';
    $anio = date('Y');
    ?>

    <!-- Footer con Badges -->
    <footer class="bg-gray-50 border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">

                <!-- Badges de Seguridad -->
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <!-- Badge SSL -->
                    <div class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <i data-lucide="shield-check" class="w-5 h-5 text-green-600"></i>
                        <div class="text-left">
                            <div class="text-xs text-gray-500 leading-none">Conexión</div>
                            <div class="text-sm font-semibold text-gray-900 leading-tight">Segura SSL</div>
                        </div>
                    </div>

                    <!-- Badge HTTPS -->
                    <div class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <i data-lucide="lock" class="w-5 h-5 text-blue-600"></i>
                        <div class="text-left">
                            <div class="text-xs text-gray-500 leading-none">Protocolo</div>
                            <div class="text-sm font-semibold text-gray-900 leading-tight">HTTPS</div>
                        </div>
                    </div>

                    <!-- Badge Privacidad -->
                    <div class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <i data-lucide="shield" class="w-5 h-5 text-purple-600"></i>
                        <div class="text-left">
                            <div class="text-xs text-gray-500 leading-none">Datos</div>
                            <div class="text-sm font-semibold text-gray-900 leading-tight">Protegidos</div>
                        </div>
                    </div>
                </div>

                <!-- Branding VERUMax -->
                <div class="text-center md:text-right">
                    <div class="flex items-center justify-center md:justify-end gap-2 mb-2">
                        <span class="text-sm text-gray-600">Powered by</span>
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-white border-2 rounded-lg shadow-sm" style="border-color: <?php echo htmlspecialchars($color_primario); ?>">
                            <i data-lucide="zap" class="w-4 h-4" style="color: <?php echo htmlspecialchars($color_primario); ?>"></i>
                            <span class="font-bold text-sm" style="color: <?php echo htmlspecialchars($color_primario); ?>">VERUMax</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Presencia Digital Profesional</p>
                </div>
            </div>

            <!-- Copyright -->
            <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-600">
                    © <?php echo $anio; ?> <strong><?php echo htmlspecialchars($nombre); ?></strong>. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </footer>

    <?php
}
?>
