<?php
/**
 * Bloque: Hero Institucional
 * Template: Corporativo para Sobre Nosotros
 */

$titulo = $contenido['titulo'] ?? 'Bienvenidos';
$subtitulo = $contenido['subtitulo'] ?? '';
$imagen_fondo = $contenido['imagen_fondo'] ?? '';
$mostrar_logo = $contenido['mostrar_logo'] ?? false;
?>

<div class="relative py-20 px-6 rounded-xl overflow-hidden mb-12"
     style="background: <?php echo $imagen_fondo ? "url('{$imagen_fondo}') center/cover" : 'linear-gradient(135deg, var(--color-primario) 0%, var(--color-secundario) 100%)'; ?>">

    <!-- Overlay oscuro para mejor legibilidad -->
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 text-center text-white max-w-3xl mx-auto">
        <?php if ($mostrar_logo && !empty($colores['logo_url'] ?? '')): ?>
        <img src="<?php echo htmlspecialchars($colores['logo_url']); ?>"
             alt="Logo"
             class="h-20 mx-auto mb-6 bg-white/10 p-2 rounded-lg">
        <?php endif; ?>

        <h1 class="text-4xl md:text-5xl font-bold mb-4">
            <?php echo htmlspecialchars($titulo); ?>
        </h1>

        <?php if ($subtitulo): ?>
        <p class="text-xl text-white/90 leading-relaxed">
            <?php echo htmlspecialchars($subtitulo); ?>
        </p>
        <?php endif; ?>
    </div>
</div>
