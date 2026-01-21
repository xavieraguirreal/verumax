<?php
/**
 * OriginalisDoc - Hub para el Sector Creativo
 */
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OriginalisDoc - Soluciones para el Sector Creativo</title>
    <meta name="description" content="Descubre las herramientas digitales diseñadas por OriginalisDoc para fotógrafos, artistas visuales, artesanos y diseñadores. Protege y presenta tu trabajo.">

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle fill='%23D4AF37' cx='50' cy='50' r='45'/%3E%3Cpath fill='none' stroke='%23fff' stroke-width='8' stroke-linecap='round' stroke-linejoin='round' d='M30 50 L42 62 L70 38'/%3E%3C/svg%3E">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'gold': { DEFAULT: '#D4AF37', light: '#F0D377', dark: '#B8941E' },
                        'artist-purple': { DEFAULT: '#8B5CF6', light: '#A78BFA', dark: '#6D28D9' },
                        'photo-blue': { DEFAULT: '#3B82F6', light: '#60A5FA', dark: '#2563EB' },
                        'artisan-brown': { DEFAULT: '#a16207', light: '#ca8a04', dark: '#854d0e' }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; background: #0a0a0a; }
        .gold-gradient { background: linear-gradient(135deg, #D4AF37 0%, #F0D377 100%); }
    </style>
</head>
<body class="bg-black text-white">

    <!-- Header -->
    <header class="bg-black/95 backdrop-blur-md border-b border-gold/20 sticky top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle fill="#D4AF37" cx="50" cy="50" r="45"/><path fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" d="M30 50 L42 62 L70 38"/></svg>
                <a href="index.php" class="text-2xl font-bold text-gold">OriginalisDoc</a>
            </div>
            <nav class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-300 hover:text-gold font-medium transition-colors">Inicio</a>

                <!-- Soluciones por Sector Dropdown -->
                <div class="relative" id="dropdown-soluciones">
                    <button onclick="toggleDropdown('soluciones')" class="text-gray-300 hover:text-gold font-medium transition-colors flex items-center gap-1">
                        Soluciones
                        <i data-lucide="chevron-down" class="w-4 h-4" id="chevron-soluciones"></i>
                    </button>
                    <div id="menu-soluciones" class="absolute left-0 mt-2 w-56 bg-gray-900 border border-gold/30 rounded-lg shadow-xl opacity-0 invisible transition-all duration-200 z-50">
                        <a href="artifex.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 bg-gold/20 first:rounded-t-lg">
                            <i data-lucide="palette" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium">Sector Creativo</span>
                        </a>
                        <a href="photographis.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 pl-8">
                            <i data-lucide="camera" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium">Fotógrafos</span>
                        </a>
                        <a href="plastes.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 pl-8">
                            <i data-lucide="paintbrush" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium">Artistas Visuales</span>
                        </a>
                        <a href="opifex.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 pl-8">
                            <i data-lucide="gem" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium">Artesanos</span>
                        </a>
                        <a href="academicus.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300">
                            <i data-lucide="graduation-cap" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium">Académico</span>
                        </a>
                        <a href="mutua.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300">
                            <i data-lucide="heart-handshake" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium">Mutuales</span>
                        </a>
                        <a href="identitas.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 last:rounded-b-lg">
                            <i data-lucide="credit-card" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium">Tarjeta Digital</span>
                        </a>
                    </div>
                </div>

                <a href="#subsectores" class="text-gray-300 hover:text-gold font-medium transition-colors">Subsectores</a>
                <a href="contactus.php" class="px-5 py-2 text-black font-semibold gold-gradient rounded-lg hover:opacity-90 transition-opacity">Contactar</a>
            </nav>
            <button class="md:hidden text-gold">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 bg-gradient-to-b from-black via-gray-950 to-black">
            <div class="container mx-auto px-6 text-center relative z-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-artist-purple/20 border border-artist-purple/30 text-artist-purple-light rounded-full text-sm font-semibold mb-6">
                    <i data-lucide="palette" class="w-4 h-4"></i>
                    <span>Sector Creativo y Artístico</span>
                </div>
                <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">
                    <span class="text-white">Soluciones para Cada</span><br>
                    <span class="text-transparent bg-clip-text gold-gradient">Tipo de Creador</span>
                </h1>
                <p class="mt-6 text-lg md:text-xl text-gray-300 max-w-3xl mx-auto">
                    Sabemos que cada arte tiene sus propias reglas y desafíos. Elige tu disciplina para descubrir las herramientas digitales diseñadas específicamente para potenciar tu trabajo.
                </p>

                <!-- Stats -->
                <div class="mt-12 grid grid-cols-3 gap-6 max-w-2xl mx-auto">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-artist-purple-light">∞</div>
                        <div class="text-sm text-gray-400 mt-2">Obras Protegidas</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-gold">100%</div>
                        <div class="text-sm text-gray-400 mt-2">Verificable</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-photo-blue-light">24/7</div>
                        <div class="text-sm text-gray-400 mt-2">Portfolio Online</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Qué es OriginalisDoc -->
        <section class="py-20 bg-gray-950">
            <div class="container mx-auto px-6">
                <div class="max-w-4xl mx-auto">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">¿Qué es OriginalisDoc?</h2>
                        <p class="text-lg text-gray-400">La plataforma integral para creadores que quieren proteger, exhibir y monetizar su trabajo</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6">
                            <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="shield-check" class="w-6 h-6 text-gold"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gold mb-3">Protege tu Trabajo</h3>
                            <p class="text-gray-400 text-sm leading-relaxed">Certificados de autenticidad con QR infalsificable, portfolios seguros y registro de procedencia para garantizar la autoría y originalidad de cada pieza.</p>
                        </div>

                        <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6">
                            <div class="w-12 h-12 bg-artist-purple/20 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="monitor" class="w-6 h-6 text-artist-purple-light"></i>
                            </div>
                            <h3 class="text-xl font-bold text-artist-purple-light mb-3">Exhibe con Profesionalismo</h3>
                            <p class="text-gray-400 text-sm leading-relaxed">Catálogos digitales interactivos, galerías privadas para clientes, tarjetas digitales de contacto y portfolios que cuentan tu historia.</p>
                        </div>

                        <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6">
                            <div class="w-12 h-12 bg-photo-blue/20 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="trending-up" class="w-6 h-6 text-photo-blue-light"></i>
                            </div>
                            <h3 class="text-xl font-bold text-photo-blue-light mb-3">Monetiza tu Arte</h3>
                            <p class="text-gray-400 text-sm leading-relaxed">Transforma interés en ventas con tiendas online integradas, sistemas de consultas y herramientas para gestionar tu negocio creativo.</p>
                        </div>

                        <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6">
                            <div class="w-12 h-12 bg-artisan-brown/20 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="users" class="w-6 h-6 text-artisan-brown-light"></i>
                            </div>
                            <h3 class="text-xl font-bold text-artisan-brown-light mb-3">Conecta con tu Audiencia</h3>
                            <p class="text-gray-400 text-sm leading-relaxed">Acceso 24/7 para clientes, coleccionistas y galerías. Comparte tu trabajo de forma segura y profesional desde cualquier dispositivo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Fichas de Sub-Sectores -->
        <section id="subsectores" class="py-20 bg-black">
            <div class="container mx-auto px-6">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    
                    <a href="photographis.php" class="bg-gray-900/80 border-2 border-gray-700 p-8 rounded-2xl hover:border-gold hover:shadow-2xl hover:shadow-gold/20 transition-all group flex flex-col">
                        <div class="w-16 h-16 bg-gold/10 rounded-lg flex items-center justify-center mb-4 border border-gold/20">
                            <i data-lucide="camera" class="w-8 h-8 text-gold"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3 group-hover:text-gold transition-colors">Fotógrafos y Videógrafos</h3>
                        <p class="text-gray-400 text-sm flex-grow">La solución definitiva para la entrega profesional de tu trabajo. Impresiona a tus clientes con galerías privadas, seguras y con tu propia marca.</p>
                        <span class="mt-6 font-semibold text-gold inline-flex items-center">
                            Ver Solución <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                        </span>
                    </a>

                    <a href="plastes.php" class="bg-gray-900/80 border-2 border-gray-700 p-8 rounded-2xl hover:border-artist-purple hover:shadow-2xl hover:shadow-artist-purple/20 transition-all group flex flex-col">
                        <div class="w-16 h-16 bg-artist-purple/10 rounded-lg flex items-center justify-center mb-4 border border-artist-purple/20">
                            <i data-lucide="paintbrush" class="w-8 h-8 text-artist-purple-light"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3 group-hover:text-artist-purple-light transition-colors">Artistas Visuales</h3>
                        <p class="text-gray-400 text-sm flex-grow">Protege el valor y la autenticidad de cada obra. Emite Certificados de Autenticidad infalsificables y transforma el interés en ventas con catálogos interactivos.</p>
                         <span class="mt-6 font-semibold text-artist-purple-light inline-flex items-center">
                            Ver Solución <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                        </span>
                    </a>

                    <a href="opifex.php" class="bg-gray-900/80 border-2 border-gray-700 p-8 rounded-2xl hover:border-gold hover:shadow-2xl hover:shadow-gold/20 transition-all group flex flex-col">
                         <div class="w-16 h-16 bg-gold/10 rounded-lg flex items-center justify-center mb-4 border border-gold/20">
                            <i data-lucide="gem" class="w-8 h-8 text-gold"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3 group-hover:text-gold transition-colors">Artesanos y Diseñadores</h3>
                        <p class="text-gray-400 text-sm flex-grow">Exhibe la unicidad de tus creaciones. Construye un portfolio profesional que cuente la historia de tu trabajo y certifica la originalidad de cada pieza vendida.</p>
                         <span class="mt-6 font-semibold text-gold inline-flex items-center">
                            Ver Solución <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                        </span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Otras Soluciones Disponibles -->
        <section id="soluciones" class="py-20 bg-gray-950">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">Otras Soluciones Disponibles</h2>
                    <p class="text-lg text-gray-400 max-w-2xl mx-auto">Complementa tu ecosistema creativo con herramientas adicionales</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                    <!-- Tarjeta Digital -->
                    <a href="identitas.php" class="bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group">
                        <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="credit-card" class="w-6 h-6 text-gold"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">Tarjeta Digital</h3>
                        <p class="text-gray-400 text-sm">QR infalsificable con landing page personalizada</p>
                    </a>

                    <!-- Certificados Académicos -->
                    <a href="academicus.php" class="bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group">
                        <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="graduation-cap" class="w-6 h-6 text-gold"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">Certificados Académicos</h3>
                        <p class="text-gray-400 text-sm">Para talleres, cursos y formaciones que ofrezcas</p>
                    </a>

                    <!-- Mutuales -->
                    <a href="mutua.php" class="bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group">
                        <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="heart-handshake" class="w-6 h-6 text-gold"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">Asociaciones Creativas</h3>
                        <p class="text-gray-400 text-sm">Gestión de colectivos y asociaciones de artistas</p>
                    </a>

                    <!-- Soluciones Personalizadas -->
                    <div class="bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group cursor-pointer"
                         data-servicio="Soluciones Personalizadas"
                         data-descripcion="Desarrollamos herramientas a medida para tus necesidades específicas como creador. Desde integraciones con redes sociales hasta sistemas de gestión de inventario.">
                        <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="settings" class="w-6 h-6 text-gold"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">Soluciones Personalizadas</h3>
                        <p class="text-gray-400 text-sm">Desarrollos a medida para necesidades específicas</p>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <a href="index.php#productos" class="inline-flex items-center gap-2 text-gold hover:text-gold-light transition-colors font-semibold">
                        <span>Ver todas las soluciones de OriginalisDoc</span>
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- CTA Final -->
        <section class="py-20 bg-gradient-to-b from-black to-gray-950">
            <div class="container mx-auto px-6">
                <div class="max-w-4xl mx-auto text-center">
                    <h2 class="text-3xl md:text-5xl font-bold mb-6">
                        <span class="text-transparent bg-clip-text gold-gradient">Transforma tu Carrera Creativa Hoy</span>
                    </h2>
                    <p class="text-xl text-gray-300 max-w-2xl mx-auto mb-10">
                        Únete a la comunidad de creadores que ya protegen, exhiben y monetizan su trabajo con OriginalisDoc
                    </p>

                    <div class="grid md:grid-cols-3 gap-6 max-w-3xl mx-auto mb-12">
                        <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6">
                            <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="play-circle" class="w-6 h-6 text-black"></i>
                            </div>
                            <h3 class="font-bold text-gold mb-2">Demo Gratis</h3>
                            <p class="text-sm text-gray-400">Sin compromiso, 100% personalizada</p>
                        </div>

                        <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6">
                            <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="zap" class="w-6 h-6 text-black"></i>
                            </div>
                            <h3 class="font-bold text-gold mb-2">Setup Rápido</h3>
                            <p class="text-sm text-gray-400">Listo en 48 horas</p>
                        </div>

                        <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6">
                            <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-3">
                                <i data-lucide="headphones" class="w-6 h-6 text-black"></i>
                            </div>
                            <h3 class="font-bold text-gold mb-2">Soporte Incluido</h3>
                            <p class="text-sm text-gray-400">Asistencia técnica completa</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="contactus.php" class="px-8 py-4 text-black font-bold gold-gradient rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/50 text-lg">
                            Solicitar Demo Personalizada
                        </a>
                        <a href="index.php" class="px-8 py-4 text-gold font-bold bg-gold/10 border-2 border-gold/30 rounded-lg hover:bg-gold/20 transition-colors text-lg">
                            Explorar Todas las Soluciones
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/modal-en-desarrollo.php'; ?>

    <script>
        lucide.createIcons();

        // Función para toggle de dropdowns
        function toggleDropdown(menuName) {
            const menu = document.getElementById('menu-' + menuName);
            const chevron = document.getElementById('chevron-' + menuName);

            if (menu.classList.contains('opacity-0')) {
                menu.classList.remove('opacity-0', 'invisible');
                menu.classList.add('opacity-100', 'visible');
                chevron.style.transform = 'rotate(180deg)';
            } else {
                menu.classList.add('opacity-0', 'invisible');
                menu.classList.remove('opacity-100', 'visible');
                chevron.style.transform = 'rotate(0deg)';
            }
        }

        // Cerrar dropdowns al hacer clic fuera
        document.addEventListener('click', function(event) {
            const dropdowns = ['soluciones'];
            dropdowns.forEach(name => {
                const dropdown = document.getElementById('dropdown-' + name);
                const menu = document.getElementById('menu-' + name);
                const chevron = document.getElementById('chevron-' + name);

                if (dropdown && !dropdown.contains(event.target)) {
                    menu.classList.add('opacity-0', 'invisible');
                    menu.classList.remove('opacity-100', 'visible');
                    chevron.style.transform = 'rotate(0deg)';
                }
            });
        });
    </script>
</body>
</html>

