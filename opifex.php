<?php
/**
 * OriginalisDoc para Artesanos y Diseñadores - Solución Integral
 */
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artesanos - <?php echo APP_NAME; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Solución integral para artesanos y diseñadores: portfolios con storytelling, certificados de origen, catálogos digitales y gestión profesional de tu obra.">
    <meta name="keywords" content="artesanos, diseñadores, portfolio artesanal, certificados origen, catálogo digital">

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
                        'artisan-brown': {
                            DEFAULT: '#a16207',
                            light: '#ca8a04',
                            dark: '#854d0e'
                        },
                        'artisan-amber': {
                            DEFAULT: '#f59e0b',
                            light: '#fbbf24',
                            dark: '#d97706'
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
        .artisan-gradient {
            background: linear-gradient(135deg, #a16207 0%, #f59e0b 100%);
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
            <div class="absolute top-20 left-10 w-96 h-96 bg-artisan-brown rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-artisan-amber rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-artisan-brown/20 border border-artisan-brown/30 text-artisan-brown-light rounded-full text-sm font-semibold mb-6">
                        <i data-lucide="gem" class="w-4 h-4"></i>
                        <span>Solución para Artesanos y Diseñadores</span>
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
                        <span class="text-white">El Valor de lo Único,</span><br>
                        <span class="text-transparent bg-clip-text artisan-gradient">Certificado</span>
                    </h1>

                    <p class="text-lg md:text-xl text-gray-300 mb-8 leading-relaxed">
                        Portfolio profesional con storytelling, certificados de origen artesanal y catálogos digitales. Eleva tu trabajo del taller al mundo digital.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="#soluciones" class="px-8 py-3 text-white font-bold bg-artisan-brown hover:bg-artisan-brown-dark rounded-lg transition-all shadow-lg shadow-artisan-brown/30">
                            Ver Soluciones
                        </a>
                        <a href="#planes" class="px-8 py-3 text-artisan-brown-light font-bold bg-artisan-brown/10 border border-artisan-brown/30 rounded-lg hover:bg-artisan-brown/20 transition-all">
                            Ver Planes
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="mt-12 grid grid-cols-3 gap-6">
                        <div>
                            <div class="text-3xl font-bold text-artisan-brown-light">100%</div>
                            <div class="text-sm text-gray-400">Hecho a Mano</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-gold">24/7</div>
                            <div class="text-sm text-gray-400">Catálogo Online</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-artisan-amber-light">∞</div>
                            <div class="text-sm text-gray-400">Piezas Únicas</div>
                        </div>
                    </div>
                </div>

                <!-- Right Visual -->
                <div class="relative hidden md:block">
                    <div class="relative float-animation">
                        <!-- Gem Icon -->
                        <div class="w-64 h-64 mx-auto bg-gradient-to-br from-artisan-brown/20 to-artisan-amber/20 rounded-3xl border-2 border-artisan-brown/30 flex items-center justify-center backdrop-blur-sm">
                            <i data-lucide="gem" class="w-32 h-32 text-artisan-brown-light"></i>
                        </div>

                        <!-- Floating Cards -->
                        <div class="absolute -top-8 -right-8 bg-gray-900 border border-gold/30 rounded-xl p-4 shadow-xl">
                            <div class="flex items-center gap-3">
                                <i data-lucide="scroll-text" class="w-6 h-6 text-gold"></i>
                                <div>
                                    <div class="text-xs text-gray-400">Certificado</div>
                                    <div class="text-sm font-bold text-white">Pieza Original</div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -bottom-8 -left-8 bg-gray-900 border border-artisan-brown/30 rounded-xl p-4 shadow-xl">
                            <div class="flex items-center gap-3">
                                <i data-lucide="book-open" class="w-6 h-6 text-artisan-brown-light"></i>
                                <div>
                                    <div class="text-xs text-gray-400">Portfolio</div>
                                    <div class="text-sm font-bold text-white">Con Storytelling</div>
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
                <p class="text-gray-400 max-w-2xl mx-auto">Los desafíos más comunes del artesano en el mundo digital</p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="store-x" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"Competir con lo Industrial"</h3>
                    <p class="text-sm text-gray-400">Difícil destacar tu pieza única frente a productos masivos en un mercado saturado.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="folder-x" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"Sin Catálogo Profesional"</h3>
                    <p class="text-sm text-gray-400">Fotos en tu celular, no tienes un lugar profesional para mostrar tu trabajo.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/20 p-6 rounded-xl hover:border-red-500/40 transition-all">
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="book-x" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-red-400 mb-2">"La Historia se Pierde"</h3>
                    <p class="text-sm text-gray-400">El valor del proceso creativo y los materiales se pierde una vez que vendes la pieza.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Soluciones -->
    <section id="soluciones" class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                    Herramientas para el Artífice Moderno
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Soluciones profesionales que cuentan la historia detrás de cada pieza
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Portfolio con Storytelling -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-artisan-brown/30 rounded-2xl p-8 hover:border-artisan-brown hover:shadow-xl hover:shadow-artisan-brown/20 transition-all group">
                    <div class="w-16 h-16 artisan-gradient rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="book-open" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-artisan-brown-light mb-3">Portfolio con Storytelling</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Cada pieza cuenta su historia. Muestra el proceso creativo, materiales nobles y las horas de trabajo que hacen única cada creación.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-brown-light"></i>
                            <span>Galerías por colección</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-brown-light"></i>
                            <span>Proceso creativo visible</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-brown-light"></i>
                            <span>Videos del taller</span>
                        </li>
                    </ul>
                </div>

                <!-- Certificados de Origen -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gold/30 rounded-2xl p-8 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group">
                    <div class="w-16 h-16 gold-gradient rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="scroll-text" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gold mb-3">Certificados de Origen Artesanal</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Certifica autenticidad, materiales, año de creación y que es una pieza única o de edición limitada. Aumenta el valor percibido.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-gold"></i>
                            <span>Código QR verificable</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-gold"></i>
                            <span>Materiales certificados</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-gold"></i>
                            <span>Pieza única numerada</span>
                        </li>
                    </ul>
                </div>

                <!-- Catálogo Digital con QR -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-artisan-amber/30 rounded-2xl p-8 hover:border-artisan-amber hover:shadow-xl hover:shadow-artisan-amber/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-artisan-amber to-amber-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="qr-code" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-artisan-amber-light mb-3">Catálogo Digital con QR</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Perfecto para ferias. Los visitantes escanean y ven tu catálogo completo con precios, disponibilidad y opciones de contacto.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-amber-light"></i>
                            <span>QR imprimible</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-amber-light"></i>
                            <span>Catálogo siempre actualizado</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-amber-light"></i>
                            <span>Contacto directo</span>
                        </li>
                    </ul>
                </div>

                <!-- Tarjeta Digital -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-purple-500/30 rounded-2xl p-8 hover:border-purple-500 hover:shadow-xl hover:shadow-purple-500/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-purple-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="credit-card" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-purple-400 mb-3">Tarjeta Digital de Contacto</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Networking profesional. Comparte tu portfolio, redes sociales y datos de contacto con un QR. Deja de entregar tarjetas impresas.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-purple-400"></i>
                            <span>QR personalizado</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-purple-400"></i>
                            <span>Actualización instantánea</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-purple-400"></i>
                            <span>Guardado en contactos</span>
                        </li>
                    </ul>
                </div>

                <!-- Tienda Online Integrada -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-emerald-500/30 rounded-2xl p-8 hover:border-emerald-500 hover:shadow-xl hover:shadow-emerald-500/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-600 to-emerald-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="shopping-bag" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-400 mb-3">Tienda Online Simple</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Vende directo desde tu portfolio. Gestión de stock, precios, consultas y pedidos. Sin comisiones de terceros.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Gestión de inventario</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Formulario de pedidos</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                            <span>Sin comisiones</span>
                        </li>
                    </ul>
                </div>

                <!-- Sistema de Consultas -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-cyan-500/30 rounded-2xl p-8 hover:border-cyan-500 hover:shadow-xl hover:shadow-cyan-500/20 transition-all group">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-600 to-cyan-400 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i data-lucide="message-circle" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-cyan-400 mb-3">Sistema de Consultas</h3>
                    <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                        Los clientes consultan sobre piezas o solicitan trabajos personalizados. Recibe notificaciones y gestiona pedidos desde un solo lugar.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>Formularios personalizados</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>Notificaciones por email</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-cyan-400"></i>
                            <span>Historial de consultas</span>
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
                    Más que una herramienta, una plataforma completa para tu negocio artesanal
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 text-center hover:border-gold/50 transition-all">
                    <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="sparkles" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="font-bold text-gold mb-2">Sin Conocimientos Técnicos</h3>
                    <p class="text-sm text-gray-400">Fácil de usar. Sube fotos y textos. Listo.</p>
                </div>

                <div class="bg-gray-900/50 border border-artisan-brown/20 rounded-xl p-6 text-center hover:border-artisan-brown/50 transition-all">
                    <div class="w-12 h-12 artisan-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="palette" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="font-bold text-artisan-brown-light mb-2">Tu Marca, Tu Estilo</h3>
                    <p class="text-sm text-gray-400">Personaliza colores, logo y diseño.</p>
                </div>

                <div class="bg-gray-900/50 border border-artisan-amber/20 rounded-xl p-6 text-center hover:border-artisan-amber/50 transition-all">
                    <div class="w-12 h-12 bg-gradient-to-br from-artisan-amber to-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="smartphone" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-bold text-artisan-amber-light mb-2">Mobile First</h3>
                    <p class="text-sm text-gray-400">Perfecto en cualquier dispositivo.</p>
                </div>

                <div class="bg-gray-900/50 border border-emerald-500/20 rounded-xl p-6 text-center hover:border-emerald-500/50 transition-all">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-600 to-emerald-400 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="headphones" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="font-bold text-emerald-400 mb-2">Soporte Humano</h3>
                    <p class="text-sm text-gray-400">Asistencia real cuando la necesites.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes -->
    <section id="planes" class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">
                    Planes para Cada Artesano
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Desde emprendedores hasta talleres establecidos
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
                            <i data-lucide="check" class="w-4 h-4 text-artisan-brown-light flex-shrink-0"></i>
                            <span>30 piezas en portfolio</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-brown-light flex-shrink-0"></i>
                            <span>10GB almacenamiento</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-brown-light flex-shrink-0"></i>
                            <span>Catálogo con QR</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-brown-light flex-shrink-0"></i>
                            <span>Tarjeta digital</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-brown-light flex-shrink-0"></i>
                            <span>Soporte básico</span>
                        </li>
                    </ul>
                    <button class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 text-white text-sm font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                        Comenzar
                    </button>
                </div>

                <!-- Plan Premium -->
                <div class="bg-gradient-to-br from-artisan-brown/10 to-artisan-amber/10 border-2 border-gold rounded-2xl p-6 relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 gold-gradient text-black text-xs font-bold rounded-full">
                        MÁS POPULAR
                    </div>
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-gold mb-2">Premium</h3>
                        <p class="text-gray-300 text-xs">Para el artesano profesional</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">$XX</span>
                        <span class="text-gray-400 text-sm">/mes</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Piezas ilimitadas</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>100GB almacenamiento</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Portfolio con storytelling</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Certificados de origen</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-gold flex-shrink-0"></i>
                            <span>Tienda online básica</span>
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
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-artisan-amber/50 rounded-2xl p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-artisan-amber-light mb-2">Excellens</h3>
                        <p class="text-gray-400 text-xs">Para talleres en crecimiento</p>
                    </div>
                    <div class="mb-4">
                        <span class="text-3xl font-bold text-white">$XX</span>
                        <span class="text-gray-400 text-sm">/mes</span>
                    </div>
                    <ul class="space-y-2 mb-6">
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-amber-light flex-shrink-0"></i>
                            <span>Todo de Premium +</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-amber-light flex-shrink-0"></i>
                            <span>500GB almacenamiento</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-amber-light flex-shrink-0"></i>
                            <span>Tienda con carrito</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-amber-light flex-shrink-0"></i>
                            <span>Sistema de consultas</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-amber-light flex-shrink-0"></i>
                            <span>Análisis de ventas</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-gray-300">
                            <i data-lucide="check" class="w-4 h-4 text-artisan-amber-light flex-shrink-0"></i>
                            <span>Soporte premium</span>
                        </li>
                    </ul>
                    <button class="w-full px-4 py-2.5 bg-gradient-to-r from-artisan-amber to-amber-600 hover:from-artisan-amber-dark hover:to-amber-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        Elegir Plan
                    </button>
                </div>

                <!-- Plan Supremus -->
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gray-700 rounded-2xl p-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-white mb-2">Supremus</h3>
                        <p class="text-gray-400 text-xs">Para talleres consolidados</p>
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
                Lleva Tu Taller al Mundo Digital
            </h2>
            <p class="text-lg text-gray-300 max-w-2xl mx-auto mb-10">
                Únete a cientos de artesanos que ya están mostrando el valor de su trabajo con OriginalisDoc
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="contactus.php?servicio=Artesanos" class="px-10 py-4 text-black font-bold gold-gradient rounded-lg hover:opacity-90 transition-opacity shadow-2xl shadow-gold/30">
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
