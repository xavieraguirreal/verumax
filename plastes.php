<?php
/**
 * OriginalisDoc para Artistas Visuales - Solución Integral
 * Para pintores, escultores, ilustradores y artistas digitales
 */
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artistas Visuales - <?php echo APP_NAME; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Protege, exhibe y vende tu obra con confianza. Certificados de Autenticidad, portfolios profesionales, catálogos interactivos y gestión de ediciones limitadas.">
    <meta name="keywords" content="certificado autenticidad arte, portfolio artistas, vender arte, proteger obra, catálogo QR, ediciones limitadas">

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle fill='%23D4AF37' cx='50' cy='50' r='45'/><path fill='none' stroke='%23fff' stroke-width='8' stroke-linecap='round' stroke-linejoin='round' d='M30 50 L42 62 L70 38'/></svg>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'gold': {
                            DEFAULT: '#D4AF37',
                            light: '#F0D377',
                            dark: '#B8941E'
                        },
                        'artist-purple': {
                            DEFAULT: '#8B5CF6',
                            light: '#A78BFA',
                            dark: '#6D28D9'
                        },
                        'artist-pink': {
                            DEFAULT: '#ec4899',
                            light: '#f472b6',
                            dark: '#be185d'
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
            background: #0a0a0a;
        }
        .gold-gradient {
            background: linear-gradient(135deg, #D4AF37 0%, #F0D377 100%);
        }
        .artist-gradient {
            background: linear-gradient(135deg, #8B5CF6 0%, #ec4899 100%);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
    </style>
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>styles.css">
</head>
<body class="bg-black text-gray-100">

    <!-- Header -->
    <header class="bg-black/95 backdrop-blur-md border-b border-gold/20 sticky top-0 left-0 right-0 z-50">
        <div id="reading-progress" class="absolute top-0 left-0 h-1 gold-gradient transition-all duration-150" style="width: 0%"></div>

        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle fill="#D4AF37" cx="50" cy="50" r="45"/><path fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" d="M30 50 L42 62 L70 38"/></svg>
                <a href="index.php" class="text-2xl font-bold text-gold">OriginalisDoc</a>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-300 hover:text-gold font-medium transition-colors">Inicio</a>
                <a href="artifex.php" class="text-gray-300 hover:text-gold font-medium transition-colors">Sector Creativo</a>
                <a href="#soluciones" class="text-gray-300 hover:text-gold font-medium transition-colors">Soluciones</a>
                <a href="#planes" class="text-gray-300 hover:text-gold font-medium transition-colors">Planes</a>
                <a href="contactus.php" class="px-5 py-2 text-black font-semibold gold-gradient rounded-lg hover:opacity-90 transition-opacity">Contacto</a>
            </div>
            <button class="md:hidden text-gold">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-20 md:pt-32 pb-20 bg-gradient-to-b from-black via-gray-950 to-black overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-20 left-10 w-96 h-96 bg-artist-purple rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-artist-pink rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-artist-purple/20 border border-artist-purple/30 text-artist-purple-light rounded-full text-sm font-semibold mb-6">
                        <i data-lucide="paintbrush" class="w-4 h-4"></i>
                        <span>Solución para Artistas Visuales</span>
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
                        <span class="text-white">Protege, Exhibe y Vende</span><br>
                        <span class="text-transparent bg-clip-text artist-gradient">tu Obra con Confianza</span>
                    </h1>

                    <p class="text-lg md:text-xl text-gray-300 mb-8 leading-relaxed">
                        Certificados de autenticidad infalsificables, portfolios profesionales y herramientas para potenciar el valor de cada creación.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="#soluciones" class="px-8 py-3 text-white font-bold bg-artist-purple hover:bg-artist-purple-dark rounded-lg transition-all shadow-lg shadow-artist-purple/30">
                            Ver Soluciones
                        </a>
                        <a href="#planes" class="px-8 py-3 text-artist-purple-light font-bold bg-artist-purple/10 border border-artist-purple/30 rounded-lg hover:bg-artist-purple/20 transition-all">
                            Ver Planes
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="mt-12 grid grid-cols-3 gap-6">
                        <div>
                            <div class="text-3xl font-bold text-artist-purple-light">∞</div>
                            <div class="text-sm text-gray-400">Obras Protegidas</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-gold">100%</div>
                            <div class="text-sm text-gray-400">Verificable</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-artist-pink-light">24/7</div>
                            <div class="text-sm text-gray-400">Portfolio Online</div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual -->
                <div class="relative hidden md:block">
                    <div class="relative float-animation">
                        <!-- Paintbrush Icon -->
                        <div class="w-64 h-64 mx-auto bg-gradient-to-br from-artist-purple/20 to-artist-pink/20 rounded-3xl border-2 border-artist-purple/30 flex items-center justify-center backdrop-blur-sm">
                            <i data-lucide="paintbrush" class="w-32 h-32 text-artist-purple-light"></i>
                        </div>

                        <!-- Floating Cards -->
                        <div class="absolute -top-8 -right-8 bg-gray-900 border border-gold/30 rounded-xl p-4 shadow-xl">
                            <div class="flex items-center gap-3">
                                <i data-lucide="shield-check" class="w-6 h-6 text-gold"></i>
                                <div>
                                    <div class="text-xs text-gray-400">Certificado</div>
                                    <div class="text-sm font-bold text-white">Autenticidad</div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -bottom-8 -left-8 bg-gray-900 border border-artist-purple/30 rounded-xl p-4 shadow-xl">
                            <div class="flex items-center gap-3">
                                <i data-lucide="palette" class="w-6 h-6 text-artist-purple-light"></i>
                                <div>
                                    <div class="text-xs text-gray-400">Portfolio</div>
                                    <div class="text-sm font-bold text-white">Profesional</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problemas Comunes -->
    <section class="py-20 bg-black border-y border-red-500/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">El Desafío de Valorizar tu Arte</h2>
                <p class="text-gray-400 max-w-2xl mx-auto">Los obstáculos que enfrentan los artistas para proteger y comercializar su obra</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="copy" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"Miedo al Plagio"</h3>
                    <p class="text-sm text-gray-400">Copias no autorizadas en internet devalúan tu esfuerzo y confunden a coleccionistas.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="file-question" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"Procedencia Dudosa"</h3>
                    <p class="text-sm text-gray-400">Dificultad para probar el historial de una obra, afectando la confianza del comprador.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="phone-off" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"Contactos Perdidos"</h3>
                    <p class="text-sm text-gray-400">Perder interesados en exposiciones por falta de información accesible al instante.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="folder-x" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"Catálogo Desorganizado"</h3>
                    <p class="text-sm text-gray-400">Sin un lugar profesional para mostrar tu obra completa a galeristas y compradores.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Soluciones -->
    <section id="soluciones" class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                    Herramientas para el Artista Moderno
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Un ecosistema completo para proteger, gestionar y comercializar tu arte
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Certificados de Autenticidad -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gold/30 rounded-2xl p-8 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group">
                    <div class="w-16 h-16 gold-gradient rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="shield-check" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gold mb-3">Certificados de Autenticidad (CoA)</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Emite certificados digitales únicos con QR para cada obra. Incluye número de serie, detalles técnicos y validación pública infalsificable.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-gold"></i>
                            <span>QR único por obra</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-gold"></i>
                            <span>Validación pública online</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-gold"></i>
                            <span>Inmune a falsificaciones</span>
                        </li>
                    </ul>
                </div>

                <!-- Portfolio Digital -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-artist-purple/30 rounded-2xl p-8 hover:border-artist-purple hover:shadow-xl hover:shadow-artist-purple/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-artist-purple to-artist-purple-dark rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="palette" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-artist-purple-light mb-3">Portfolio Digital Profesional</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Tu galería online personal. Muestra tu cuerpo de obra completo a coleccionistas, curadores y galeristas de todo el mundo.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light"></i>
                            <span>Organización por serie</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light"></i>
                            <span>Alta resolución</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light"></i>
                            <span>Responsive design</span>
                        </li>
                    </ul>
                </div>

                <!-- Catálogo Interactivo -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-artist-pink/30 rounded-2xl p-8 hover:border-artist-pink hover:shadow-xl hover:shadow-artist-pink/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-artist-pink to-pink-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="qr-code" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-artist-pink-light mb-3">Catálogo Interactivo con QR</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Tu "vendedor silencioso" en galerías y ferias. QR junto a la obra lleva a micrositio con historia, proceso y botón de compra.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artist-pink-light"></i>
                            <span>QR por obra en exposición</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artist-pink-light"></i>
                            <span>Videos del proceso creativo</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artist-pink-light"></i>
                            <span>Botón de consulta WhatsApp</span>
                        </li>
                    </ul>
                </div>

                <!-- Registro de Procedencia -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-amber-500/30 rounded-2xl p-8 hover:border-amber-500 hover:shadow-xl hover:shadow-amber-500/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-600 to-amber-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="history" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-amber-400 mb-3">Registro de Procedencia</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Registra historial de ventas, exposiciones y transferencias. Aumenta el valor y confianza de tus obras con trazabilidad completa.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-amber-400"></i>
                            <span>Historial de propietarios</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-amber-400"></i>
                            <span>Registro de exposiciones</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-amber-400"></i>
                            <span>Transferencias verificables</span>
                        </li>
                    </ul>
                </div>

                <!-- Gestión de Ediciones -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-emerald-500/30 rounded-2xl p-8 hover:border-emerald-500 hover:shadow-xl hover:shadow-emerald-500/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-600 to-emerald-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="layers" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-400 mb-3">Gestión de Ediciones Limitadas</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Control transparente de series limitadas. Numeración automática, registro público y verificación de autenticidad para cada pieza.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Numeración automática</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Control de series</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Registro público</span>
                        </li>
                    </ul>
                </div>

                <!-- Tarjeta Digital -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-cyan-500/30 rounded-2xl p-8 hover:border-cyan-500 hover:shadow-xl hover:shadow-cyan-500/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-600 to-cyan-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="credit-card" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-cyan-400 mb-3">Tarjeta Digital de Contacto</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Networking profesional. Comparte portfolio, redes sociales y contacto con un QR. Deja de entregar tarjetas impresas.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>QR personalizado</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>Actualización instantánea</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>Guardado en contactos</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Beneficios -->
    <section class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Por Qué Elegir OriginalisDoc
                </h2>
                <p class="text-gray-400 max-w-2xl mx-auto">
                    La plataforma de confianza para artistas profesionales
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                    <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="shield-check" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="font-bold text-gold mb-2">Tecnología Blockchain</h3>
                    <p class="text-sm text-gray-400">Certificados inmutables y verificables.</p>
                </div>

                <div class="bg-gray-900/50 border border-artist-purple/20 rounded-xl p-6 text-center hover:border-artist-purple/50 transition-all">
                    <div class="w-12 h-12 bg-gradient-to-br from-artist-purple to-artist-purple-dark rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="users" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-bold text-artist-purple-light mb-2">Usado por Galerías</h3>
                    <p class="text-sm text-gray-400">Confianza de coleccionistas y galerías.</p>
                </div>

                <div class="bg-gray-900/50 border border-artist-pink/20 rounded-xl p-6 text-center hover:border-artist-pink/50 transition-all">
                    <div class="w-12 h-12 bg-gradient-to-br from-artist-pink to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="smartphone" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-bold text-artist-pink-light mb-2">100% Mobile</h3>
                    <p class="text-sm text-gray-400">Perfecto en cualquier dispositivo.</p>
                </div>

                <div class="bg-gray-900/50 border border-emerald-500/20 rounded-xl p-6 text-center hover:border-emerald-500/50 transition-all">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-600 to-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="headphones" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-bold text-emerald-400 mb-2">Soporte Experto</h3>
                    <p class="text-sm text-gray-400">Asistencia especializada en arte.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes -->
    <section id="planes" class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                    Planes para Cada Etapa de tu Carrera
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Desde artistas emergentes hasta consagrados
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <!-- Plan Basicum -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gray-700 rounded-2xl p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-white mb-2">Basicum</h3>
                        <p class="text-gray-400 text-xs">Para artistas emergentes</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">$XX</span>
                        <span class="text-gray-400 text-sm">/mes</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>Portfolio hasta 50 obras</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>5 CoAs/año</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>Catálogo con QR</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>Tarjeta digital</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>Soporte básico</span>
                        </li>
                    </ul>
                    <button class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                        Comenzar
                    </button>
                </div>

                <!-- Plan Premium -->
                <div class="bg-gradient-to-br from-artist-purple/10 to-artist-pink/10 border-2 border-gold rounded-2xl p-6 relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 gold-gradient text-black text-xs font-bold rounded-full">
                        MÁS POPULAR
                    </div>
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-gold mb-2">Premium</h3>
                        <p class="text-gray-300 text-xs">Para artistas profesionales</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">$XX</span>
                        <span class="text-gray-400 text-sm">/mes</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Obras ilimitadas</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>CoAs ilimitados</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Portfolio profesional</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Catálogo interactivo</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Analíticas avanzadas</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Soporte prioritario</span>
                        </li>
                    </ul>
                    <button class="w-full px-4 py-2.5 gold-gradient text-black text-sm font-bold rounded-lg hover:opacity-90 transition-opacity">
                        Elegir Plan
                    </button>
                </div>

                <!-- Plan Excellens -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-artist-purple/50 rounded-2xl p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-artist-purple-light mb-2">Excellens</h3>
                        <p class="text-gray-400 text-xs">Para artistas consolidados</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">$XX</span>
                        <span class="text-gray-400 text-sm">/mes</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>Todo de Premium +</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>Registro de procedencia</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>Gestión de ediciones</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>API para integraciones</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>Branding avanzado</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artist-purple-light flex-shrink-0"></i>
                            <span>Soporte premium</span>
                        </li>
                    </ul>
                    <button class="w-full px-4 py-2.5 bg-gradient-to-r from-artist-purple to-artist-purple-dark hover:from-artist-purple-dark hover:to-purple-800 text-white text-sm font-semibold rounded-lg transition-colors">
                        Elegir Plan
                    </button>
                </div>

                <!-- Plan Supremus -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gray-700 rounded-2xl p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-white mb-2">Supremus</h3>
                        <p class="text-gray-400 text-xs">Para galerías y estudios</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">Custom</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>Todo de Excellens +</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>Multi-artista ilimitado</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>Dominio personalizado</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>White-label completo</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>Agente IA coleccionistas</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>Soporte dedicado 24/7</span>
                        </li>
                    </ul>
                    <button class="w-full px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-400 hover:from-emerald-700 hover:to-emerald-500 text-white text-sm font-semibold rounded-lg transition-colors">
                        Contactar Ventas
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-20 bg-gradient-to-br from-gray-900 via-gray-800 to-black border-y border-gold/20">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-3xl md:text-5xl font-extrabold text-transparent bg-clip-text gold-gradient mb-6">
                Dale a tu Arte el Valor que Merece
            </h2>
            <p class="text-lg text-gray-300 max-w-2xl mx-auto mb-10">
                Únete a miles de artistas que ya protegen y valorizan su obra con OriginalisDoc
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="contactus.php?servicio=Artistas+Visuales" class="px-10 py-4 text-black font-bold gold-gradient rounded-lg hover:opacity-90 transition-opacity shadow-2xl shadow-gold/30">
                    Solicitar Demo
                </a>
                <a href="#planes" class="px-10 py-4 text-gold font-bold bg-gold/10 border border-gold/30 rounded-lg hover:bg-gold/20 transition-all">
                    Ver Planes
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Modal En Desarrollo -->
    <?php include 'includes/modal-en-desarrollo.php'; ?>

    <script>
        // Inicializar iconos Lucide
        lucide.createIcons();

        // Reading progress bar
        window.addEventListener('scroll', function() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById('reading-progress').style.width = scrolled + '%';
        });
    </script>
</body>
</html>
