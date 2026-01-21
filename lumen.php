<?php
/**
 * LUMEN - Portfolio Público (Frontend)
 * Vista pública del portfolio fotográfico del artista
 *
 * Uso: lumen.php?id=fotosjuan&plantilla=masonry
 */

// Cargar datos
require_once 'lumen_datos.php';

// Obtener parámetros
$cliente_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;
$plantilla_override = isset($_GET['plantilla']) ? htmlspecialchars($_GET['plantilla']) : null;

// Si no hay ID, redirigir a la portada pública de Lumen
if (!$cliente_id) {
    header('Location: lumen/index.php');
    exit;
}

$portfolio = obtenerPortfolioLumen($cliente_id);

if (!$portfolio) {
    die('Error: Portfolio no encontrado');
}

// Determinar plantilla a usar
$plantilla = $plantilla_override ?? $portfolio['plantilla'];

// Datos para la vista
$nombre_marca = $portfolio['nombre_marca'];
$nombre_artista = $portfolio['nombre_artista'];
$tagline = $portfolio['tagline'];
$biografia = $portfolio['biografia'];
$tema_color = $portfolio['tema_color'];
$tema_secundario = $portfolio['tema_secundario'];
$dark_mode = $portfolio['dark_mode_default'];
$redes = $portfolio['redes'];
$galerias = $portfolio['galerias'];
$seo = $portfolio['seo'];
?>
<!DOCTYPE html>
<html lang="es" class="<?php echo $dark_mode ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO -->
    <title><?php echo $seo['titulo']; ?></title>
    <meta name="description" content="<?php echo $seo['descripcion']; ?>">
    <meta name="keywords" content="<?php echo $seo['keywords']; ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $nombre_marca; ?>">
    <meta property="og:description" content="<?php echo $tagline; ?>">
    <meta property="og:type" content="website">

    <!-- Favicon dinámico -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle fill='<?php echo urlencode($tema_color); ?>' cx='50' cy='50' r='45'/><path fill='%23fff' stroke='%23fff' stroke-width='4' d='M35 40h30v20h-30z M30 35h5v5h-5z M45 50h10v5h-10z'/></svg>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'brand': '<?php echo $tema_color; ?>',
                        'brand-secondary': '<?php echo $tema_secundario; ?>'
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }

        /* Protección anti-descarga */
        <?php if ($portfolio['configuracion']['proteccion_descarga']): ?>
        img {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        <?php endif; ?>

        /* Masonry Grid */
        .masonry-grid {
            column-count: 1;
            column-gap: 1.5rem;
        }
        @media (min-width: 640px) {
            .masonry-grid {
                column-count: 2;
            }
        }
        @media (min-width: 1024px) {
            .masonry-grid {
                column-count: 3;
            }
        }
        .masonry-item {
            break-inside: avoid;
            margin-bottom: 1.5rem;
        }

        /* Lightbox */
        #lightbox {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background-color: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
        }
        #lightbox.active {
            display: flex;
        }
        #lightbox img {
            max-width: 90vw;
            max-height: 90vh;
            object-fit: contain;
        }
        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .lightbox-nav:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-50%) scale(1.1);
        }
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .lightbox-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }
    </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
        <div class="container mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <svg class="h-8 w-8 sm:h-10 sm:w-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                    <circle fill="<?php echo $tema_color; ?>" cx="50" cy="50" r="45"/>
                    <path fill="#fff" stroke="#fff" stroke-width="4" d="M35 40h30v20h-30z M30 35h5v5h-5z M45 50h10v5h-10z"/>
                </svg>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold"><?php echo $nombre_artista; ?></h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Photography</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex items-center space-x-6">
                <a href="#home" class="text-gray-700 dark:text-gray-300 hover:text-brand transition-colors">Inicio</a>
                <a href="#portfolio" class="text-gray-700 dark:text-gray-300 hover:text-brand transition-colors">Portfolio</a>
                <a href="#sobre-mi" class="text-gray-700 dark:text-gray-300 hover:text-brand transition-colors">Sobre Mí</a>
                <a href="#contacto" class="text-gray-700 dark:text-gray-300 hover:text-brand transition-colors">Contacto</a>
            </nav>

            <!-- Dark Mode Toggle -->
            <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block text-yellow-400"></i>
                <i data-lucide="moon" class="w-5 h-5 block dark:hidden text-gray-700"></i>
            </button>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="relative min-h-screen flex items-center justify-center bg-black pt-20">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black z-10"></div>
            <div class="w-full h-full bg-gradient-to-br from-gray-900 via-gray-800 to-black"></div>
        </div>

        <div class="container mx-auto px-4 sm:px-6 relative z-20 text-center">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-tight mb-6">
                    <?php echo $nombre_artista; ?>
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-[<?php echo $tema_color; ?>] to-[<?php echo $tema_secundario; ?>] mt-2">Photography</span>
                </h1>
                <p class="text-lg sm:text-xl md:text-2xl text-gray-300 mb-8">
                    <?php echo $tagline; ?>
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="#portfolio" class="px-8 py-3 text-white font-bold rounded-lg transition-all shadow-lg hover:shadow-xl hover:scale-105" style="background-color: <?php echo $tema_color; ?>;">
                        Ver Portfolio
                    </a>
                    <a href="#contacto" class="px-8 py-3 text-white font-bold bg-white/10 backdrop-blur-sm border border-white/30 rounded-lg hover:bg-white/20 transition-all">
                        Contactar
                    </a>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto mt-12">
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                        <div class="text-3xl font-bold" style="color: <?php echo $tema_color; ?>;">+10</div>
                        <div class="text-sm text-gray-400">Años</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                        <div class="text-3xl font-bold text-purple-400">+500</div>
                        <div class="text-sm text-gray-400">Eventos</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                        <div class="text-3xl font-bold text-amber-400">+300</div>
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

    <!-- Portfolio Masonry Grid -->
    <section id="portfolio" class="py-20 bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="container mx-auto px-4 sm:px-6">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">Portfolio</h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Una selección de mis trabajos más recientes
                </p>
            </div>

            <!-- Masonry Grid -->
            <div class="masonry-grid max-w-7xl mx-auto">
                <?php
                $foto_index = 0;
                foreach ($galerias as $galeria_id => $galeria): ?>
                    <?php if ($galeria['publica']): ?>
                        <?php foreach ($galeria['fotos'] as $foto): ?>
                            <div class="masonry-item group cursor-pointer"
                                 onclick="openLightbox(<?php echo $foto_index; ?>)">
                                <div class="relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300">
                                    <!-- Imagen real -->
                                    <?php
                                        $imagen_path = "lumen/uploads/fotosjuan/{$galeria_id}/{$foto['archivo_original']}";
                                    ?>
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
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="sobre-mi" class="py-20 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid md:grid-cols-2 gap-12 items-center max-w-6xl mx-auto">
                <div class="order-2 md:order-1">
                    <div class="aspect-[3/4] rounded-2xl bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800 shadow-2xl overflow-hidden">
                        <div class="w-full h-full flex items-center justify-center">
                            <i data-lucide="user" class="w-32 h-32 text-gray-400 dark:text-gray-600"></i>
                        </div>
                    </div>
                </div>

                <div class="order-1 md:order-2">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-6">
                        <?php echo $nombre_artista; ?>
                        <span class="block text-xl mt-2" style="color: <?php echo $tema_color; ?>;">Fotógrafo Profesional</span>
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                        <?php echo $biografia; ?>
                    </p>

                    <!-- Redes Sociales -->
                    <div class="flex gap-4">
                        <?php if ($redes['instagram']): ?>
                            <a href="<?php echo $redes['instagram_url']; ?>" target="_blank" class="w-12 h-12 rounded-full flex items-center justify-center transition-all hover:scale-110" style="background-color: <?php echo $tema_color; ?>20;">
                                <i data-lucide="instagram" class="w-6 h-6" style="color: <?php echo $tema_color; ?>;"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($redes['facebook']): ?>
                            <a href="<?php echo $redes['facebook_url']; ?>" target="_blank" class="w-12 h-12 rounded-full flex items-center justify-center transition-all hover:scale-110" style="background-color: <?php echo $tema_color; ?>20;">
                                <i data-lucide="facebook" class="w-6 h-6" style="color: <?php echo $tema_color; ?>;"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($redes['behance']): ?>
                            <a href="<?php echo $redes['behance_url']; ?>" target="_blank" class="w-12 h-12 rounded-full flex items-center justify-center transition-all hover:scale-110" style="background-color: <?php echo $tema_color; ?>20;">
                                <i data-lucide="award" class="w-6 h-6" style="color: <?php echo $tema_color; ?>;"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contacto" class="py-20 bg-gray-900 dark:bg-black text-white">
        <div class="container mx-auto px-4 sm:px-6 text-center">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">¿Listo para capturar tus momentos?</h2>
                <p class="text-lg text-gray-300 mb-8">
                    Conversemos sobre tu proyecto
                </p>

                <!-- Contact Methods -->
                <div class="grid md:grid-cols-3 gap-6 mb-10">
                    <a href="mailto:<?php echo $portfolio['email']; ?>" class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 hover:bg-white/10 transition-all group">
                        <i data-lucide="mail" class="w-8 h-8 mx-auto mb-3 group-hover:scale-110 transition-transform" style="color: <?php echo $tema_color; ?>;"></i>
                        <div class="font-semibold mb-1">Email</div>
                        <div class="text-sm text-gray-400"><?php echo $portfolio['email']; ?></div>
                    </a>

                    <?php if ($portfolio['telefono']): ?>
                    <a href="https://wa.me/<?php echo $redes['whatsapp']; ?>" target="_blank" class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 hover:bg-white/10 transition-all group">
                        <i data-lucide="message-circle" class="w-8 h-8 text-green-400 mx-auto mb-3 group-hover:scale-110 transition-transform"></i>
                        <div class="font-semibold mb-1">WhatsApp</div>
                        <div class="text-sm text-gray-400"><?php echo $portfolio['telefono']; ?></div>
                    </a>
                    <?php endif; ?>

                    <?php if ($redes['instagram']): ?>
                    <a href="<?php echo $redes['instagram_url']; ?>" target="_blank" class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-6 hover:bg-white/10 transition-all group">
                        <i data-lucide="instagram" class="w-8 h-8 text-pink-400 mx-auto mb-3 group-hover:scale-110 transition-transform"></i>
                        <div class="font-semibold mb-1">Instagram</div>
                        <div class="text-sm text-gray-400">@<?php echo $redes['instagram']; ?></div>
                    </a>
                    <?php endif; ?>
                </div>

                <a href="https://wa.me/<?php echo $redes['whatsapp']; ?>?text=Hola!%20Me%20interesa%20consultar%20por%20tus%20servicios%20fotográficos" target="_blank" class="inline-flex items-center gap-2 px-8 py-4 text-white font-bold rounded-lg transition-all shadow-2xl hover:scale-105" style="background-color: <?php echo $tema_color; ?>;">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span>Enviar Consulta</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="items-center justify-center">
        <!-- Close Button -->
        <button onclick="closeLightbox()" class="lightbox-close" aria-label="Cerrar">
            <i data-lucide="x" class="w-6 h-6 text-white"></i>
        </button>

        <!-- Previous Button -->
        <button onclick="previousImage()" class="lightbox-nav" style="left: 20px;" aria-label="Anterior">
            <i data-lucide="chevron-left" class="w-6 h-6 text-white"></i>
        </button>

        <!-- Image Container -->
        <div class="flex flex-col items-center justify-center w-full h-full p-4">
            <img id="lightbox-image" src="" alt="" class="rounded-lg shadow-2xl">

            <!-- Info Box -->
            <div id="lightbox-info" class="absolute bottom-0 left-0 right-0 p-8 bg-gradient-to-t from-black via-black/80 to-transparent">
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
        <button onclick="nextImage()" class="lightbox-nav" style="right: 20px;" aria-label="Siguiente">
            <i data-lucide="chevron-right" class="w-6 h-6 text-white"></i>
        </button>
    </div>

    <!-- Footer -->
    <footer class="bg-black text-gray-400 py-8 border-t border-gray-800">
        <div class="container mx-auto px-6 text-center">
            <p class="text-sm mb-2">&copy; <?php echo date('Y'); ?> <?php echo $nombre_marca; ?>. Todos los derechos reservados.</p>
            <p class="text-xs">
                Powered by
                <a href="../index.php" class="hover:text-white transition-colors" style="color: <?php echo $tema_color; ?>;">
                    OriginalisDoc
                </a>
                 | Lumen
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Inicializar Lucide Icons
        lucide.createIcons();

        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;

        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            lucide.createIcons();
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        <?php if ($portfolio['configuracion']['proteccion_descarga']): ?>
        // Protección anti-descarga
        document.addEventListener('contextmenu', (e) => {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
            }
        });

        document.addEventListener('dragstart', (e) => {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
            }
        });
        <?php endif; ?>

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

        function openLightbox(index) {
            currentImageIndex = index;
            updateLightboxImage();
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
            lucide.createIcons();
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % images.length;
            updateLightboxImage();
        }

        function previousImage() {
            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
            updateLightboxImage();
        }

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
            if (!lightbox.classList.contains('active')) return;

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
    </script>
</body>
</html>

<?php
// Función auxiliar para gradientes por categoría
function getGradientForCategory($categoria) {
    $gradients = [
        'bodas' => 'from-pink-100 to-purple-100 dark:from-pink-900/20 dark:to-purple-900/20',
        'eventos' => 'from-blue-100 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20',
        'retratos' => 'from-amber-100 to-orange-100 dark:from-amber-900/20 dark:to-orange-900/20',
        'familiar' => 'from-green-100 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/20',
    ];

    return $gradients[$categoria] ?? 'from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900';
}
?>
