<?php
/**
 * Bloque: Información de Contacto
 * Template: Formulario + Info para Contacto
 *
 * Variables disponibles:
 * - $contenido: array con el contenido del bloque
 * - $config: array con la configuración
 * - $colores: array con colores de la instancia
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

$titulo = $contenido['titulo'] ?? $t('identitas.contact_admin', [], 'Contacto');
$texto = $contenido['texto'] ?? '';
$email = $contenido['email'] ?? '';
$telefono = $contenido['telefono'] ?? '';
$web = $contenido['web'] ?? '';
?>

<div class="text-center mb-12">
    <i data-lucide="mail" class="w-12 h-12 mx-auto" style="color: var(--color-primario);"></i>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">
        <?php echo htmlspecialchars($titulo); ?>
    </h2>
    <?php if ($texto): ?>
    <div class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto prose dark:prose-invert">
        <?php echo $texto; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($email || $telefono || $web): ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-8 mb-8">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4"><?php echo $t('identitas.contact_info', [], 'Información de Contacto'); ?></h3>
        <div class="space-y-3">
            <?php if ($email): ?>
            <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                <i data-lucide="mail" class="w-5 h-5" style="color: var(--color-primario);"></i>
                <a href="mailto:<?php echo htmlspecialchars($email); ?>"
                   class="hover:underline transition">
                    <?php echo htmlspecialchars($email); ?>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($telefono): ?>
            <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                <i data-lucide="phone" class="w-5 h-5" style="color: var(--color-primario);"></i>
                <a href="tel:<?php echo htmlspecialchars($telefono); ?>"
                   class="hover:underline transition">
                    <?php echo htmlspecialchars($telefono); ?>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($web): ?>
            <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                <i data-lucide="globe" class="w-5 h-5" style="color: var(--color-primario);"></i>
                <a href="<?php echo htmlspecialchars($web); ?>"
                   target="_blank"
                   class="hover:underline transition">
                    <?php echo htmlspecialchars(preg_replace('#^https?://#', '', $web)); ?>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
