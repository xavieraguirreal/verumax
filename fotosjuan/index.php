<?php
// Definimos el título de esta página en particular
$page_title = 'FotosJuan Photography - Portfolio Profesional';

// Cargar datos de Lumen para integrar portfolio
require_once __DIR__ . '/../lumen_datos.php';
$portfolio_lumen = obtenerPortfolioLumen('fotosjuan');

// Incluimos la cabecera
require_once 'header.php';
?>

<!-- Hero Section - Presentación del Fotógrafo -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-black">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black z-10"></div>
        <!-- Placeholder for hero image - Replace with actual photo -->
        <div class="w-full h-full bg-gradient-to-br from-gray-900 via-gray-800 to-black"></div>
    </div>

    <!-- Hero Content -->
    <div class="container mx-auto px-4 sm:px-6 relative z-20 text-center">
        <div class="max-w-4xl mx-auto">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full text-sm font-semibold text-white mb-6 animate-fade-in">
                <i data-lucide="camera" class="w-4 h-4"></i>
                <span>Fotografía Profesional</span>
            </div>

            <!-- Main Title -->
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-tight mb-6">
                Juan Martínez
                <span class="block text-transparent bg-clip-text fotosjuan-gradient mt-2">Photography</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-lg sm:text-xl md:text-2xl text-gray-300 mb-8 max-w-2xl mx-auto">
                Capturando momentos únicos con más de 10 años de experiencia en bodas, eventos y retratos
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-wrap justify-center gap-4 mb-12">
                <a href="#portfolio" class="px-8 py-3 text-white font-bold fotosjuan-blue-dark rounded-lg fotosjuan-blue-hover transition-all shadow-lg hover:shadow-xl hover:scale-105">
                    Ver Portfolio
                </a>
                <a href="#contacto" class="px-8 py-3 text-white font-bold bg-white/10 backdrop-blur-sm border border-white/30 rounded-lg hover:bg-white/20 transition-all">
                    Contactar
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                    <div class="text-3xl font-bold text-fj-blue-light">+10</div>
                    <div class="text-sm text-gray-400">Años</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                    <div class="text-3xl font-bold text-purple-400">+500</div>
                    <div class="text-sm text-gray-400">Eventos</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                    <div class="text-3xl font-bold text-fj-gold">+300</div>
                    <div class="text-sm text-gray-400">Bodas</div>
                </div>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                    <div class="text-3xl font-bold text-green-400">100%</div>
                    <div class="text-sm text-gray-400">Satisfacción</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 animate-bounce">
        <a href="#portfolio" class="flex flex-col items-center text-white/60 hover:text-white transition-colors">
            <span class="text-xs mb-2">Scroll</span>
            <i data-lucide="chevron-down" class="w-6 h-6"></i>
        </a>
    </div>
</section>

<!-- About Section -->
<section id="sobre-mi" class="py-20 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="grid md:grid-cols-2 gap-12 items-center max-w-6xl mx-auto">
            <!-- Image -->
            <div class="order-2 md:order-1">
                <div class="aspect-[3/4] rounded-2xl bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 shadow-2xl overflow-hidden">
                    <!-- Placeholder for photographer portrait -->
                    <div class="w-full h-full flex items-center justify-center">
                        <i data-lucide="user" class="w-32 h-32 text-gray-400 dark:text-gray-600"></i>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="order-1 md:order-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xs font-semibold mb-4">
                    <i data-lucide="user" class="w-3 h-3"></i>
                    <span>Sobre Mí</span>
                </div>

                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                    Juan Martínez
                    <span class="block text-xl text-fj-blue mt-2">Fotógrafo Profesional</span>
                </h2>

                <div class="space-y-4 text-gray-600 dark:text-gray-400 leading-relaxed">
                    <p>
                        Con más de 10 años de experiencia en fotografía profesional, he tenido el privilegio de capturar momentos únicos para cientos de clientes en toda Argentina.
                    </p>
                    <p>
                        Mi especialidad son las bodas y eventos, donde combino técnica fotográfica con sensibilidad artística para contar historias visuales que perduran en el tiempo.
                    </p>
                    <p>
                        Cada proyecto es una oportunidad para crear algo especial. Trabajo con equipo de última generación y ofrezco galerías privadas en alta resolución para que mis clientes puedan disfrutar y compartir sus momentos.
                    </p>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-2xl font-bold fotosjuan-blue-text dark:text-blue-400">+300</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Bodas Cubiertas</div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">+500</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Eventos Totales</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="servicios" class="py-20 bg-white dark:bg-gray-900 transition-colors duration-300">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">Servicios</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Soluciones fotográficas profesionales para cada necesidad
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <!-- Service Cards -->
            <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-fj-blue dark:hover:border-fj-blue transition-all">
                <div class="w-14 h-14 fotosjuan-blue-dark rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="heart" class="w-7 h-7 text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Bodas</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Cobertura completa de tu día especial. Pre-boda, ceremonia, fiesta y entrega de galería privada en alta resolución.</p>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-fj-blue"></i>
                        <span>Cobertura 8-10 horas</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-fj-blue"></i>
                        <span>+400 fotos editadas</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-fj-blue"></i>
                        <span>Galería privada online</span>
                    </li>
                </ul>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-500 transition-all">
                <div class="w-14 h-14 bg-purple-600 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="briefcase" class="w-7 h-7 text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Eventos Corporativos</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Fotografía profesional para lanzamientos, conferencias y eventos empresariales.</p>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-purple-500"></i>
                        <span>Cobertura personalizada</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-purple-500"></i>
                        <span>Entrega exprés disponible</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-purple-500"></i>
                        <span>Para redes sociales</span>
                    </li>
                </ul>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-fj-gold dark:hover:border-fj-gold transition-all">
                <div class="w-14 h-14 fotosjuan-gold rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="user-circle" class="w-7 h-7 text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Sesiones Personales</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Retratos individuales, familiares o corporativos con estilo único.</p>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-fj-gold"></i>
                        <span>Sesión 1-2 horas</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-fj-gold"></i>
                        <span>Estudio o locación</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4 text-fj-gold"></i>
                        <span>Retoque profesional</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Section - Integrado desde Lumen -->
<section id="portfolio" class="py-20 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
    <div class="container mx-auto px-4 sm:px-6">
        <!-- Section Header -->
        <div class="text-center mb-12">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-4">Portfolio</h2>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Mis trabajos más recientes - Galería profesional powered by Lumen
            </p>
        </div>

        <!-- Masonry Grid con fotos reales de Lumen -->
        <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 max-w-7xl mx-auto space-y-6">
            <?php if ($portfolio_lumen && isset($portfolio_lumen['galerias'])): ?>
                <?php
                $foto_index = 0;
                foreach ($portfolio_lumen['galerias'] as $galeria_id => $galeria): ?>
                    <?php if ($galeria['publica']): ?>
                        <?php foreach ($galeria['fotos'] as $foto): ?>
                            <div class="break-inside-avoid group cursor-pointer" onclick="openLightbox(<?php echo $foto_index; ?>)">
                                <div class="relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300">
                                    <!-- Imagen real -->
                                    <?php $imagen_path = "../lumen/uploads/fotosjuan/{$galeria_id}/{$foto['archivo_original']}"; ?>
                                    <img
                                        src="<?php echo $imagen_path; ?>"
                                        alt="<?php echo htmlspecialchars($foto['titulo']); ?>"
                                        class="w-full h-auto object-cover lightbox-image"
                                        loading="lazy"
                                        data-src="<?php echo $imagen_path; ?>"
                                        data-titulo="<?php echo htmlspecialchars($foto['titulo']); ?>"
                                        data-descripcion="<?php echo htmlspecialchars($foto['descripcion']); ?>"
                                        data-galeria="<?php echo htmlspecialchars($galeria['nombre']); ?>"
                                        data-color="<?php echo $galeria['color']; ?>"
                                    >

                                    <!-- Overlay con info -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-6 pointer-events-none">
                                        <div class="mb-2">
                                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full" style="background-color: <?php echo $galeria['color']; ?>20; color: <?php echo $galeria['color']; ?>;">
                                                <i data-lucide="zoom-in" class="w-3 h-3 inline mr-1"></i>
                                                <?php echo $galeria['nombre']; ?>
                                            </span>
                                        </div>
                                        <h3 class="text-white text-xl font-bold mb-2"><?php echo $foto['titulo']; ?></h3>
                                        <?php if ($foto['descripcion']): ?>
                                            <p class="text-gray-200 text-sm"><?php echo $foto['descripcion']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php $foto_index++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contacto" class="py-20 bg-gray-900 dark:bg-black text-white transition-colors duration-300">
    <div class="container mx-auto px-4 sm:px-6 text-center">
        <div class="max-w-3xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">¿Listo para capturar tus momentos?</h2>
            <p class="text-lg text-gray-300 mb-8">
                Conversemos sobre tu proyecto. Ya sea una boda, evento corporativo o sesión personal, estoy aquí para ayudarte.
            </p>

            <!-- Contact Methods -->
            <div class="grid md:grid-cols-3 gap-6 mb-10">
                <a href="mailto:info@fotosjuan.com" class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 hover:bg-white/10 transition-all group">
                    <i data-lucide="mail" class="w-8 h-8 text-fj-blue mx-auto mb-3 group-hover:scale-110 transition-transform"></i>
                    <div class="font-semibold mb-1">Email</div>
                    <div class="text-sm text-gray-400">info@fotosjuan.com</div>
                </a>

                <a href="https://wa.me/541155551234" target="_blank" class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 hover:bg-white/10 transition-all group">
                    <i data-lucide="message-circle" class="w-8 h-8 text-green-400 mx-auto mb-3 group-hover:scale-110 transition-transform"></i>
                    <div class="font-semibold mb-1">WhatsApp</div>
                    <div class="text-sm text-gray-400">+54 11 5555-1234</div>
                </a>

                <a href="https://instagram.com/fotosjuan" target="_blank" class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 hover:bg-white/10 transition-all group">
                    <i data-lucide="instagram" class="w-8 h-8 text-pink-400 mx-auto mb-3 group-hover:scale-110 transition-transform"></i>
                    <div class="font-semibold mb-1">Instagram</div>
                    <div class="text-sm text-gray-400">@fotosjuan</div>
                </a>
            </div>

            <!-- CTA Button -->
            <a href="https://wa.me/541155551234?text=Hola%20Juan!%20Me%20interesa%20consultar%20por%20tus%20servicios%20fotográficos" target="_blank" class="inline-flex items-center gap-2 px-8 py-4 fotosjuan-blue-dark text-white font-bold rounded-lg fotosjuan-blue-hover transition-all shadow-2xl hover:scale-105">
                <i data-lucide="send" class="w-5 h-5"></i>
                <span>Enviar Consulta</span>
            </a>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="hidden fixed inset-0 z-[9999] bg-black/95 backdrop-blur-lg items-center justify-center">
    <!-- Close Button -->
    <button onclick="closeLightbox()" class="absolute top-5 right-5 w-12 h-12 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full flex items-center justify-center hover:bg-white/20 transition-all" aria-label="Cerrar">
        <i data-lucide="x" class="w-6 h-6 text-white"></i>
    </button>

    <!-- Previous Button -->
    <button onclick="previousImage()" class="absolute top-1/2 -translate-y-1/2 left-5 w-12 h-12 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full flex items-center justify-center hover:bg-white/20 hover:scale-110 transition-all" aria-label="Anterior">
        <i data-lucide="chevron-left" class="w-6 h-6 text-white"></i>
    </button>

    <!-- Image Container -->
    <div class="flex flex-col items-center justify-center w-full h-full p-4">
        <img id="lightbox-image" src="" alt="" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg shadow-2xl">

        <!-- Info Box -->
        <div class="absolute bottom-0 left-0 right-0 p-8 bg-gradient-to-t from-black via-black/80 to-transparent">
            <div class="max-w-4xl mx-auto">
                <div class="mb-2">
                    <span id="lightbox-badge" class="inline-block px-3 py-1 text-xs font-semibold rounded-full">
                        <span id="lightbox-galeria"></span>
                    </span>
                </div>
                <h3 id="lightbox-titulo" class="text-white text-2xl font-bold mb-2"></h3>
                <p id="lightbox-descripcion" class="text-gray-200"></p>
                <div class="mt-3 text-sm text-gray-400">
                    <span id="lightbox-counter"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Button -->
    <button onclick="nextImage()" class="absolute top-1/2 -translate-y-1/2 right-5 w-12 h-12 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full flex items-center justify-center hover:bg-white/20 hover:scale-110 transition-all" aria-label="Siguiente">
        <i data-lucide="chevron-right" class="w-6 h-6 text-white"></i>
    </button>
</div>

<!-- Script específico para esta página -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Lightbox functionality
    let currentImageIndex = 0;
    const images = document.querySelectorAll('.lightbox-image');
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxTitulo = document.getElementById('lightbox-titulo');
    const lightboxDescripcion = document.getElementById('lightbox-descripcion');
    const lightboxGaleria = document.getElementById('lightbox-galeria');
    const lightboxBadge = document.getElementById('lightbox-badge');
    const lightboxCounter = document.getElementById('lightbox-counter');

    window.openLightbox = function(index) {
        currentImageIndex = index;
        updateLightboxImage();
        lightbox.classList.remove('hidden');
        lightbox.classList.add('flex');
        document.body.style.overflow = 'hidden';
        lucide.createIcons();
    };

    window.closeLightbox = function() {
        lightbox.classList.add('hidden');
        lightbox.classList.remove('flex');
        document.body.style.overflow = 'auto';
    };

    window.nextImage = function() {
        currentImageIndex = (currentImageIndex + 1) % images.length;
        updateLightboxImage();
    };

    window.previousImage = function() {
        currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
        updateLightboxImage();
    };

    function updateLightboxImage() {
        const img = images[currentImageIndex];
        lightboxImage.src = img.dataset.src;
        lightboxImage.alt = img.dataset.titulo;
        lightboxTitulo.textContent = img.dataset.titulo;
        lightboxDescripcion.textContent = img.dataset.descripcion;
        lightboxGaleria.textContent = img.dataset.galeria;
        lightboxCounter.textContent = `Foto ${currentImageIndex + 1} de ${images.length}`;

        // Update badge color
        const color = img.dataset.color;
        lightboxBadge.style.backgroundColor = color + '20';
        lightboxBadge.style.color = color;

        lucide.createIcons();
    }

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (lightbox.classList.contains('hidden')) return;

        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === 'ArrowRight') {
            nextImage();
        } else if (e.key === 'ArrowLeft') {
            previousImage();
        }
    });

    // Click outside to close
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });
});
</script>

<?php
// Incluimos el pie de página
require_once 'footer.php';
?>
