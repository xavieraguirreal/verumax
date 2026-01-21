<?php
/**
 * OriginalisDoc para Fotógrafos - Solución Integral
 */
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotógrafos - <?php echo APP_NAME; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Solución integral para fotógrafos profesionales: galerías privadas, portfolios, tarjetas digitales y certificados de autenticidad para tu trabajo.">
    <meta name="keywords" content="fotógrafos, galerías privadas, portfolio fotográfico, certificados de autenticidad, tarjeta digital">

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
                        'photo-blue': {
                            DEFAULT: '#0ea5e9',
                            light: '#38bdf8',
                            dark: '#0284c7'
                        },
                        'photo-purple': {
                            DEFAULT: '#8b5cf6',
                            light: '#a78bfa',
                            dark: '#7c3aed'
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
        .photo-gradient {
            background: linear-gradient(135deg, #0ea5e9 0%, #8b5cf6 100%);
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
            <div class="absolute top-20 left-10 w-96 h-96 bg-photo-blue rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-photo-purple rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-photo-blue/20 border border-photo-blue/30 text-photo-blue-light rounded-full text-sm font-semibold mb-6">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <span>Solución para Fotógrafos Profesionales</span>
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
                        <span class="text-white">Entrega tu Trabajo</span><br>
                        <span class="text-transparent bg-clip-text photo-gradient">Como un Verdadero Pro</span>
                    </h1>

                    <p class="text-lg md:text-xl text-gray-300 mb-8 leading-relaxed">
                        Galerías privadas con tu marca, portfolios profesionales y certificados de autenticidad. La plataforma que eleva tu negocio fotográfico.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="#soluciones" class="px-8 py-3 text-white font-bold bg-photo-blue hover:bg-photo-blue-dark rounded-lg transition-all shadow-lg shadow-photo-blue/30">
                            Ver Soluciones
                        </a>
                        <a href="#planes" class="px-8 py-3 text-photo-blue font-bold bg-photo-blue/10 border border-photo-blue/30 rounded-lg hover:bg-photo-blue/20 transition-all">
                            Ver Planes
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="mt-12 grid grid-cols-3 gap-6">
                        <div>
                            <div class="text-3xl font-bold text-photo-blue-light">∞</div>
                            <div class="text-sm text-gray-400">Almacenamiento</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-gold">24/7</div>
                            <div class="text-sm text-gray-400">Disponible</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-photo-purple-light">100%</div>
                            <div class="text-sm text-gray-400">Tu Marca</div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual -->
                <div class="relative hidden md:block">
                    <div class="relative float-animation">
                        <!-- Camera Icon -->
                        <div class="w-64 h-64 mx-auto bg-gradient-to-br from-photo-blue/20 to-photo-purple/20 rounded-3xl border-2 border-photo-blue/30 flex items-center justify-center backdrop-blur-sm">
                            <i data-lucide="camera" class="w-32 h-32 text-photo-blue-light"></i>
                        </div>

                        <!-- Floating Cards -->
                        <div class="absolute -top-8 -right-8 bg-gray-900 border border-gold/30 rounded-xl p-4 shadow-xl">
                            <div class="flex items-center gap-3">
                                <i data-lucide="image" class="w-6 h-6 text-gold"></i>
                                <div>
                                    <div class="text-xs text-gray-400">Galería Privada</div>
                                    <div class="text-sm font-bold text-white">Cliente Premium</div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -bottom-8 -left-8 bg-gray-900 border border-photo-blue/30 rounded-xl p-4 shadow-xl">
                            <div class="flex items-center gap-3">
                                <i data-lucide="shield-check" class="w-6 h-6 text-photo-blue-light"></i>
                                <div>
                                    <div class="text-xs text-gray-400">Certificado</div>
                                    <div class="text-sm font-bold text-white">Original Verificado</div>
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
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">¿Te Suena Familiar?</h2>
                <p class="text-gray-400 max-w-2xl mx-auto">Los problemas más comunes en la entrega de trabajos fotográficos</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="clock-alert" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"Links que Expiran"</h3>
                    <p class="text-sm text-gray-400">Clientes que reclaman porque no descargaron a tiempo. Reenvíos constantes.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="smartphone" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"¿Por WhatsApp?"</h3>
                    <p class="text-sm text-gray-400">Envíos poco profesionales que comprimen tus fotos y arruinan la calidad.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="folder-search" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"¿Dónde está mi foto?"</h3>
                    <p class="text-sm text-gray-400">Carpetas caóticas en Drive sin organización. Clientes frustrados buscando.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Soluciones -->
    <section id="soluciones" class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                    Soluciones Pensadas para Tu Flujo de Trabajo
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Herramientas profesionales que elevan tu negocio y mejoran la experiencia de tus clientes
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Galerías Privadas -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-photo-blue/30 rounded-2xl p-8 hover:border-photo-blue hover:shadow-xl hover:shadow-photo-blue/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-photo-blue to-photo-blue-dark rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="image" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-photo-blue-light mb-3">Galerías de Cliente Privadas</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Portal con contraseña y tu logo. Tus clientes pueden ver, seleccionar favoritas y descargar en alta resolución. Sin límites de tiempo ni espacio.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-photo-blue-light"></i>
                            <span>Acceso ilimitado para el cliente</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-photo-blue-light"></i>
                            <span>Branding personalizado</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-photo-blue-light"></i>
                            <span>Selección de favoritas</span>
                        </li>
                    </ul>
                </div>

                <!-- Portfolio Profesional -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gold/30 rounded-2xl p-8 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group">
                    <div class="w-16 h-16 gold-gradient rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="monitor" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gold mb-3">Portfolio Público Profesional</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Tu vidriera online. Muestra tus mejores trabajos organizados por categoría. Separado completamente de las galerías privadas de clientes.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-gold"></i>
                            <span>Organización por proyectos</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-gold"></i>
                            <span>Optimizado para SEO</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-gold"></i>
                            <span>Diseño responsive</span>
                        </li>
                    </ul>
                </div>

                <!-- Tarjeta Digital -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-photo-purple/30 rounded-2xl p-8 hover:border-photo-purple hover:shadow-xl hover:shadow-photo-purple/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-photo-purple to-photo-purple-dark rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="credit-card" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-photo-purple-light mb-3">Tarjeta de Contacto Digital</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Networking profesional. Comparte tu portfolio, redes sociales y datos de contacto con un simple código QR. Deja de entregar tarjetas impresas.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-photo-purple-light"></i>
                            <span>QR personalizado</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-photo-purple-light"></i>
                            <span>Actualización instantánea</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-photo-purple-light"></i>
                            <span>Guardado directo en contactos</span>
                        </li>
                    </ul>
                </div>

                <!-- Certificados de Autenticidad -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-amber-500/30 rounded-2xl p-8 hover:border-amber-500 hover:shadow-xl hover:shadow-amber-500/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-600 to-amber-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-amber-400 mb-3">Certificados de Autenticidad</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Protege tu trabajo de arte. Emite certificados verificables con código QR para tus fotografías impresas de edición limitada.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-amber-400"></i>
                            <span>Verificación instantánea</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-amber-400"></i>
                            <span>Control de ediciones</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-amber-400"></i>
                            <span>Aumenta el valor percibido</span>
                        </li>
                    </ul>
                </div>

                <!-- Sistema de Marca de Agua Digital -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-emerald-500/30 rounded-2xl p-8 hover:border-emerald-500 hover:shadow-xl hover:shadow-emerald-500/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-600 to-emerald-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="fingerprint" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-400 mb-3">Marca de Agua Inteligente</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Protección automática. Aplica tu marca de agua personalizada a las previews de galería. Las descargas finales sin marca.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Aplicación automática</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Personalizable</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Evita uso no autorizado</span>
                        </li>
                    </ul>
                </div>

                <!-- Sistema de Selección de Fotos -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-cyan-500/30 rounded-2xl p-8 hover:border-cyan-500 hover:shadow-xl hover:shadow-cyan-500/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-600 to-cyan-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="heart" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-cyan-400 mb-3">Sistema de Favoritos</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Facilita la selección. Tus clientes marcan sus fotos favoritas y tú recibes la lista completa. Sin confusiones por WhatsApp.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>Selección intuitiva</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>Exportación de lista</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>Agiliza post-producción</span>
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
                    Más que una herramienta, una solución completa para tu negocio
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                    <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="zap" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="font-bold text-gold mb-2">Configuración Rápida</h3>
                    <p class="text-sm text-gray-400">Sube tu logo y colores. Listo en 5 minutos.</p>
                </div>

                <div class="bg-gray-900/50 border border-photo-blue/20 rounded-xl p-6 text-center hover:border-photo-blue/50 transition-all">
                    <div class="w-12 h-12 bg-gradient-to-br from-photo-blue to-photo-blue-dark rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="smartphone" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-bold text-photo-blue-light mb-2">Mobile First</h3>
                    <p class="text-sm text-gray-400">Perfecto en cualquier dispositivo.</p>
                </div>

                <div class="bg-gray-900/50 border border-photo-purple/20 rounded-xl p-6 text-center hover:border-photo-purple/50 transition-all">
                    <div class="w-12 h-12 bg-gradient-to-br from-photo-purple to-photo-purple-dark rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="shield" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-bold text-photo-purple-light mb-2">Seguro y Privado</h3>
                    <p class="text-sm text-gray-400">Galerías protegidas con contraseña.</p>
                </div>

                <div class="bg-gray-900/50 border border-emerald-500/20 rounded-xl p-6 text-center hover:border-emerald-500/50 transition-all">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-600 to-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="headphones" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-bold text-emerald-400 mb-2">Soporte Premium</h3>
                    <p class="text-sm text-gray-400">Asistencia cuando la necesites.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes -->
    <section id="planes" class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                    Planes para Cada Necesidad
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Desde fotógrafos freelance hasta estudios establecidos
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <!-- Plan Basicum -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gray-700 rounded-2xl p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-white mb-2">Basicum</h3>
                        <p class="text-gray-400 text-xs">Perfecto para comenzar</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">$XX</span>
                        <span class="text-gray-400 text-sm">/mes</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-blue-light flex-shrink-0"></i>
                            <span>5 Galerías activas</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-blue-light flex-shrink-0"></i>
                            <span>50GB almacenamiento</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-blue-light flex-shrink-0"></i>
                            <span>Portfolio básico</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-blue-light flex-shrink-0"></i>
                            <span>Tarjeta digital</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-blue-light flex-shrink-0"></i>
                            <span>Soporte básico</span>
                        </li>
                    </ul>
                    <button class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                        Comenzar
                    </button>
                </div>

                <!-- Plan Premium -->
                <div class="bg-gradient-to-br from-photo-blue/10 to-photo-purple/10 border-2 border-gold rounded-2xl p-6 relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 gold-gradient text-black text-xs font-bold rounded-full">
                        MÁS POPULAR
                    </div>
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-gold mb-2">Premium</h3>
                        <p class="text-gray-300 text-xs">Para el profesional serio</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">$XX</span>
                        <span class="text-gray-400 text-sm">/mes</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Galerías ilimitadas</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>500GB almacenamiento</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Portfolio completo</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Certificados autenticidad</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Marca de agua automática</span>
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
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-photo-purple/50 rounded-2xl p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-photo-purple-light mb-2">Excellens</h3>
                        <p class="text-gray-400 text-xs">Para el fotógrafo avanzado</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">$XX</span>
                        <span class="text-gray-400 text-sm">/mes</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-purple-light flex-shrink-0"></i>
                            <span>Todo de Premium +</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-purple-light flex-shrink-0"></i>
                            <span>1TB almacenamiento</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-purple-light flex-shrink-0"></i>
                            <span>Tienda online integrada</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-purple-light flex-shrink-0"></i>
                            <span>Análisis avanzado</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-purple-light flex-shrink-0"></i>
                            <span>API personalizada</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-photo-purple-light flex-shrink-0"></i>
                            <span>Soporte premium</span>
                        </li>
                    </ul>
                    <button class="w-full px-4 py-2.5 bg-photo-purple hover:bg-photo-purple-dark text-white text-sm font-semibold rounded-lg transition-colors">
                        Elegir Plan
                    </button>
                </div>

                <!-- Plan Supremus -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gray-700 rounded-2xl p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-white mb-2">Supremus</h3>
                        <p class="text-gray-400 text-xs">Para equipos y estudios</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">$XX</span>
                        <span class="text-gray-400 text-sm">/mes</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>Todo de Excellens +</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>Almacenamiento ilimitado</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span>Multi-usuario ilimitado</span>
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
                Comienza a Entregar Como un Pro
            </h2>
            <p class="text-lg text-gray-300 max-w-2xl mx-auto mb-10">
                Únete a cientos de fotógrafos que ya están elevando su negocio con OriginalisDoc
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="contactus.php?servicio=Fotógrafos" class="px-10 py-4 text-black font-bold gold-gradient rounded-lg hover:opacity-90 transition-opacity shadow-2xl shadow-gold/30">
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
