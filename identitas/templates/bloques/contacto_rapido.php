<?php
/**
 * Bloque: Contacto Rápido
 * Template: Botones de acción rápida para contactar y agendar
 *
 * Variables disponibles:
 * - $instance: array con la configuración de la instancia
 * - $contenido: array con el contenido del bloque (opcional)
 * - $colores: array con colores de la instancia
 *
 * Acciones incluidas:
 * - WhatsApp (chat directo)
 * - Email (mailto:)
 * - Teléfono (tel:)
 * - Agregar contacto (descarga vCard)
 */

use VERUMax\Services\LanguageService;
$t = fn($key, $params = [], $default = null) => LanguageService::get($key, $params, $default);

// Obtener datos de contacto
$config = [];
if (!empty($instance['config'])) {
    if (is_string($instance['config'])) {
        $config = json_decode($instance['config'], true) ?: [];
    } else {
        $config = $instance['config'];
    }
}

// Datos de contacto
$telefono = $instance['telefono'] ?? $config['telefono'] ?? $contenido['telefono'] ?? '';
$email = $instance['email_contacto'] ?? $config['email_contacto'] ?? $contenido['email'] ?? '';
$whatsapp = $instance['whatsapp'] ?? $config['whatsapp'] ?? $contenido['whatsapp'] ?? $telefono;

// Slug para vCard
$slug = $instance['slug'] ?? '';

// Título personalizable
$titulo = $contenido['titulo'] ?? $t('identitas.quick_contact_title', [], 'Contacto Rápido');
$subtitulo = $contenido['subtitulo'] ?? $t('identitas.quick_contact_subtitle', [], 'Comunicate con nosotros de forma directa');

// Verificar si hay al menos un dato de contacto
$hayContacto = !empty($telefono) || !empty($email) || !empty($whatsapp) || !empty($slug);

if (!$hayContacto) {
    // Si no hay datos de contacto, no mostrar el bloque
    return;
}

// Limpiar número de WhatsApp para URL
$whatsappNumero = preg_replace('/[^0-9]/', '', $whatsapp);

// Mensaje predeterminado para WhatsApp
$whatsappMensaje = $contenido['whatsapp_mensaje']
    ?? $t('identitas.whatsapp_default_message', ['institucion' => $instance['nombre'] ?? ''], 'Hola, me contacto desde el sitio web');
$whatsappMensajeEncoded = urlencode($whatsappMensaje);

// Estilo de diseño
$estilo = $contenido['estilo'] ?? 'horizontal'; // horizontal | grid | floating

?>

<?php if ($estilo === 'floating'): ?>
<!-- Estilo Flotante (para header o fixed) -->
<div class="fixed bottom-6 right-6 z-50 flex flex-col gap-3">
    <?php if (!empty($whatsappNumero)): ?>
    <a href="https://wa.me/<?php echo htmlspecialchars($whatsappNumero); ?>?text=<?php echo $whatsappMensajeEncoded; ?>"
       target="_blank"
       rel="noopener noreferrer"
       class="w-14 h-14 rounded-full bg-green-500 hover:bg-green-600 text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110"
       title="<?php echo $t('identitas.contact_whatsapp', [], 'Escribinos por WhatsApp'); ?>">
        <i data-lucide="message-circle" class="w-6 h-6"></i>
    </a>
    <?php endif; ?>

    <?php if (!empty($slug)): ?>
    <a href="/identitas/descargar_vcard.php?institutio=<?php echo urlencode($slug); ?>"
       class="w-14 h-14 rounded-full text-white flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110"
       style="background: var(--color-primario);"
       title="<?php echo $t('identitas.contact_add', [], 'Agregar a Contactos'); ?>">
        <i data-lucide="user-plus" class="w-6 h-6"></i>
    </a>
    <?php endif; ?>
</div>

<?php elseif ($estilo === 'grid'): ?>
<!-- Estilo Grid (cuadrícula) -->
<section id="contacto-rapido" class="py-16 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white mb-4">
                <?php echo htmlspecialchars($titulo); ?>
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-400">
                <?php echo htmlspecialchars($subtitulo); ?>
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
            <?php if (!empty($whatsappNumero)): ?>
            <a href="https://wa.me/<?php echo htmlspecialchars($whatsappNumero); ?>?text=<?php echo $whatsappMensajeEncoded; ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="group flex flex-col items-center gap-4 p-6 rounded-2xl bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 transition-all duration-300 hover:-translate-y-1">
                <div class="w-16 h-16 rounded-full bg-green-500 flex items-center justify-center text-white shadow-lg group-hover:shadow-xl transition-shadow">
                    <i data-lucide="message-circle" class="w-8 h-8"></i>
                </div>
                <div class="text-center">
                    <span class="font-bold text-gray-900 dark:text-white block">WhatsApp</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo $t('identitas.contact_chat', [], 'Chat directo'); ?></span>
                </div>
            </a>
            <?php endif; ?>

            <?php if (!empty($email)): ?>
            <a href="mailto:<?php echo htmlspecialchars($email); ?>"
               class="group flex flex-col items-center gap-4 p-6 rounded-2xl bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-all duration-300 hover:-translate-y-1">
                <div class="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white shadow-lg group-hover:shadow-xl transition-shadow">
                    <i data-lucide="mail" class="w-8 h-8"></i>
                </div>
                <div class="text-center">
                    <span class="font-bold text-gray-900 dark:text-white block">Email</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo $t('identitas.contact_write', [], 'Escribinos'); ?></span>
                </div>
            </a>
            <?php endif; ?>

            <?php if (!empty($telefono)): ?>
            <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $telefono)); ?>"
               class="group flex flex-col items-center gap-4 p-6 rounded-2xl bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-all duration-300 hover:-translate-y-1">
                <div class="w-16 h-16 rounded-full bg-purple-500 flex items-center justify-center text-white shadow-lg group-hover:shadow-xl transition-shadow">
                    <i data-lucide="phone" class="w-8 h-8"></i>
                </div>
                <div class="text-center">
                    <span class="font-bold text-gray-900 dark:text-white block"><?php echo $t('identitas.contact_phone', [], 'Teléfono'); ?></span>
                    <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo $t('identitas.contact_call', [], 'Llamanos'); ?></span>
                </div>
            </a>
            <?php endif; ?>

            <?php if (!empty($slug)): ?>
            <a href="/identitas/descargar_vcard.php?institutio=<?php echo urlencode($slug); ?>"
               class="group flex flex-col items-center gap-4 p-6 rounded-2xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-300 hover:-translate-y-1"
               style="background-color: color-mix(in srgb, var(--color-primario) 10%, transparent);">
                <div class="w-16 h-16 rounded-full flex items-center justify-center text-white shadow-lg group-hover:shadow-xl transition-shadow"
                     style="background: var(--color-primario);">
                    <i data-lucide="user-plus" class="w-8 h-8"></i>
                </div>
                <div class="text-center">
                    <span class="font-bold text-gray-900 dark:text-white block"><?php echo $t('identitas.contact_add', [], 'Agregar'); ?></span>
                    <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo $t('identitas.contact_save', [], 'Guardar contacto'); ?></span>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php else: ?>
<!-- Estilo Horizontal (barra) -->
<section id="contacto-rapido" class="py-8 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 border-y border-gray-200 dark:border-gray-700">
    <div class="container mx-auto px-6">
        <div class="flex flex-wrap items-center justify-center gap-4 md:gap-6">
            <?php if (!empty($whatsappNumero)): ?>
            <a href="https://wa.me/<?php echo htmlspecialchars($whatsappNumero); ?>?text=<?php echo $whatsappMensajeEncoded; ?>"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center gap-3 px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-full font-bold shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
                <span>WhatsApp</span>
            </a>
            <?php endif; ?>

            <?php if (!empty($email)): ?>
            <a href="mailto:<?php echo htmlspecialchars($email); ?>"
               class="inline-flex items-center gap-3 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-full font-bold shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
                <i data-lucide="mail" class="w-5 h-5"></i>
                <span>Email</span>
            </a>
            <?php endif; ?>

            <?php if (!empty($telefono)): ?>
            <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^0-9+]/', '', $telefono)); ?>"
               class="inline-flex items-center gap-3 px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white rounded-full font-bold shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
                <i data-lucide="phone" class="w-5 h-5"></i>
                <span><?php echo $t('identitas.contact_phone', [], 'Teléfono'); ?></span>
            </a>
            <?php endif; ?>

            <?php if (!empty($slug)): ?>
            <a href="/identitas/descargar_vcard.php?institutio=<?php echo urlencode($slug); ?>"
               class="inline-flex items-center gap-3 px-6 py-3 text-white rounded-full font-bold shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5"
               style="background: linear-gradient(135deg, var(--color-primario), var(--color-secundario));">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                <span><?php echo $t('identitas.contact_add_short', [], 'Agregar Contacto'); ?></span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
