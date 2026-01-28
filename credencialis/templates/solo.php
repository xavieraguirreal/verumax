<?php
/**
 * TEMPLATE: Credencialis Solo (Acceso Directo)
 * Portal de credenciales de membresía
 *
 * Similar a certificatum/templates/solo.php pero para credenciales
 */

use VERUMax\Services\LanguageService;

// Función helper para traducciones
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Si viene con DNI, buscar credencial y mostrar
if (!empty($_POST['documentum']) || !empty($_GET['documentum'])) {
    if (!defined('PROXY_MODE')) define('PROXY_MODE', true);
    if (!defined('INSTITUCION_SLUG')) define('INSTITUCION_SLUG', $instance['slug']);
    if (!defined('INSTITUCION_PATH')) define('INSTITUCION_PATH', ROOT_PATH . '/' . $instance['slug'] . '/');

    $_GET['institutio'] = $instance['slug'];
    $_POST['institutio'] = $instance['slug'];

    $creare_path = ROOT_PATH . '/credencialis/creare.php';
    if (file_exists($creare_path)) {
        chdir(ROOT_PATH . '/credencialis');
        ob_start();
        include $creare_path;
        $credencial_content = ob_get_clean();
        echo $credencial_content;
        return;
    }
}

// Preparar variables para el header compartido
$page_type = 'credencialis';
$page_title = ($credencialis_config['titulo'] ?? 'Credencial de Socio') . ' - ' . $instance['nombre'];

// Leer descripción personalizada de instance_translations (si existe)
$id_instancia = $instance['id_instancia'] ?? $instance['id'] ?? 0;
$descripcion_default = $t('credencialis.page_subtitle', [], 'Acceda a su credencial digital de membresía ingresando su número de documento');
$descripcion_personalizada = LanguageService::getContent($id_instancia, 'credencialis_descripcion', null, $descripcion_default);

// Leer CTA personalizado
$cta_default = $t('credencialis.search_button', [], 'Ver mi credencial');
$cta_personalizado = LanguageService::getContent($id_instancia, 'credencialis_cta_texto', null, $cta_default);

// Incluir header compartido
include __DIR__ . '/../../templates/shared/header.php';
?>

<!-- Contenido Principal -->
<main>
    <!-- Hero Section con Gradiente -->
    <section class="bg-gradient-to-br from-gray-50 via-white to-gray-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-20 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Título Principal -->
            <div class="text-center mb-12 animate-fade-in">
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white mb-4">
                    <?php echo htmlspecialchars($t('credencialis.page_title', [], 'Credencial Digital')); ?>
                </h1>

                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
                    <?php echo htmlspecialchars($descripcion_personalizada); ?>
                </p>
            </div>

            <!-- Formulario de Acceso -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 md:p-12 border border-gray-200 dark:border-gray-700 mb-12 animate-fade-in-up">
                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="documentum" class="block text-lg font-bold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                            <i data-lucide="id-card" class="w-6 h-6"></i>
                            <?php echo $t('credencialis.search_title', [], 'Número de Documento'); ?>
                        </label>
                        <input
                            type="text"
                            id="documentum"
                            name="documentum"
                            required
                            autofocus
                            maxlength="15"
                            placeholder="<?php echo $t('credencialis.search_example', [], 'Ej: 12345678'); ?>"
                            value="<?php echo htmlspecialchars($_POST['documentum'] ?? ''); ?>"
                            class="w-full px-6 py-4 text-xl text-center font-semibold tracking-wider border-2 border-gray-300 dark:border-gray-600 rounded-2xl focus:ring-4 focus:ring-opacity-50 focus:border-transparent transition-all bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            style="focus:ring-color: var(--color-primario);"
                        >
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 text-center flex items-center justify-center gap-2">
                            <i data-lucide="info" class="w-4 h-4"></i>
                            <?php echo $t('credencialis.search_help', [], 'Ingrese su número de documento sin puntos ni espacios'); ?>
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 mt-4 text-lg font-bold text-white rounded-2xl transition-all hover:shadow-xl hover:scale-[1.02] flex items-center justify-center gap-3 shadow-lg"
                        style="background: linear-gradient(135deg, var(--color-primario, #2E7D32), var(--color-secundario, #1B5E20));"
                    >
                        <i data-lucide="credit-card" class="w-6 h-6"></i>
                        <?php echo htmlspecialchars($cta_personalizado); ?>
                    </button>
                </form>

            </div>

            <!-- Features / Características -->
            <?php
            // Parsear features desde JSON
            $features_json = $instance['credencialis_features'] ?? null;
            $features = null;

            if ($features_json) {
                $features = is_string($features_json) ? json_decode($features_json, true) : $features_json;
            }

            // Valores por defecto si no hay features configurados - con traducciones
            if (!$features) {
                $features = [
                    'feature1' => [
                        'titulo' => $t('credencialis.feature_digital', [], 'Credencial Digital'),
                        'descripcion' => $t('credencialis.feature_digital_desc', [], 'Acceda a su credencial desde cualquier dispositivo'),
                        'icono' => 'smartphone'
                    ],
                    'feature2' => [
                        'titulo' => $t('credencialis.feature_verifiable', [], 'Verificación QR'),
                        'descripcion' => $t('credencialis.feature_verifiable_desc', [], 'Código QR único para validación instantánea'),
                        'icono' => 'qr-code'
                    ],
                    'feature3' => [
                        'titulo' => $t('credencialis.feature_updated', [], 'Siempre Actualizada'),
                        'descripcion' => $t('credencialis.feature_updated_desc', [], 'Datos de membresía actualizados en tiempo real'),
                        'icono' => 'refresh-cw'
                    ]
                ];
            }
            ?>

            <div class="grid md:grid-cols-3 gap-6">
                <?php foreach ($features as $feature): ?>
                    <?php if (isset($feature['titulo'])): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-shadow text-center border border-gray-200 dark:border-gray-700">
                            <div class="w-14 h-14 rounded-xl mb-4 flex items-center justify-center mx-auto shadow-md"
                                 style="background-color: <?php echo htmlspecialchars($instance['color_primario'] ?? '#2E7D32'); ?>;">
                                <i data-lucide="<?php echo htmlspecialchars($feature['icono'] ?? 'check-circle'); ?>" class="w-7 h-7 text-white"></i>
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2">
                                <?php echo htmlspecialchars($feature['titulo'] ?? 'Característica'); ?>
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <?php echo htmlspecialchars($feature['descripcion'] ?? ''); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php if (isset($_GET['error']) && $_GET['error'] === 'not_found'): ?>
<!-- Modal Miembro No Encontrado -->
<div id="modalMiembroNoEncontrado" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="cerrarModalError()"></div>

    <!-- Modal Content -->
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8 animate-fade-in-up">
        <!-- Icono -->
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600 dark:text-red-400"></i>
        </div>

        <!-- Título -->
        <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-3">
            <?php echo $t('credencialis.member_not_found_title', [], 'Miembro no encontrado'); ?>
        </h3>

        <!-- Mensaje -->
        <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
            <?php echo $t('credencialis.member_not_found_message', [], 'No encontramos un miembro registrado con el documento ingresado. Por favor, verificá que el número sea correcto e intentá nuevamente.'); ?>
        </p>

        <!-- Botón -->
        <button onclick="cerrarModalError()"
                class="w-full py-3 px-6 text-white font-semibold rounded-xl transition-all hover:opacity-90"
                style="background-color: <?php echo htmlspecialchars($instance['color_primario'] ?? '#2E7D32'); ?>;">
            Entendido
        </button>
    </div>
</div>

<script>
function cerrarModalError() {
    const modal = document.getElementById('modalMiembroNoEncontrado');
    if (modal) {
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.remove();
            // Limpiar URL sin recargar
            const url = new URL(window.location);
            url.searchParams.delete('error');
            url.hash = '';
            window.history.replaceState({}, '', url.pathname);
            // Poner foco en el campo DNI
            const inputDni = document.getElementById('documentum');
            if (inputDni) {
                inputDni.focus();
                inputDni.select();
            }
        }, 200);
    }
}

// Cerrar con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModalError();
});
</script>
<?php endif; ?>

<?php
// Incluir footer compartido
include __DIR__ . '/../../templates/shared/footer.php';
?>
