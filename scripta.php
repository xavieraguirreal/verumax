<?php
/**
 * VERUMax Scripta - Blog Profesional
 * Página de landing para el producto Scripta (Content Marketing)
 */
require_once 'config.php';
require_once 'maintenance_config.php';
require_once 'includes/pricing_config.php';
require_once 'includes/currency_converter.php';

// =====================================
// MODO MANTENIMIENTO
// =====================================
check_maintenance_mode();

// Por ahora hardcodeado en español argentino
$current_language = 'es_AR';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VERUMax Scripta - Tus Artículos. Tu Legado Digital Verificado.</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Blog profesional integrado con tu presencia digital. Publicá artículos, posicionáte como experto y mejorá tu SEO con VERUMax Scripta.">
    <meta name="keywords" content="blog profesional, content marketing, SEO, artículos verificados, presencia digital, marketing de contenidos">
    <meta name="author" content="VERUMax">

    <!-- Favicon - Escudo Verumax -->
    <link rel="icon" type="image/png" href="assets/images/logo-verumax-escudo.png">

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
                        'metallic-blue': {
                            DEFAULT: '#1976D2',
                            light: '#42A5F5',
                            dark: '#0D47A1'
                        }
                    }
                }
            }
        }
    </script>
    <!-- Lucide Icons -->
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
        .blue-gradient {
            background: linear-gradient(135deg, #1976D2 0%, #42A5F5 100%);
        }
        .metallic-shine {
            position: relative;
            overflow: hidden;
        }
        .metallic-shine::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shine 3s infinite;
        }
        @keyframes shine {
            to { left: 100%; }
        }
    </style>
</head>
<body class="bg-black text-gray-100">

    <!-- Header -->
    <header class="bg-black/95 backdrop-blur-md border-b border-metallic-blue/20 sticky top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <img src="assets/images/logo-verumax-escudo.png" alt="Verumax Escudo" class="h-10 w-10">
                <a href="index.php" class="flex items-center">
                    <img src="assets/images/logo-verumax-texto.png" alt="Verumax" class="h-8">
                </a>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-300 hover:text-metallic-blue-light font-medium transition-colors">Volver al Inicio</a>
                <a href="contactus.php" class="px-5 py-2 text-black font-semibold blue-gradient rounded-lg hover:opacity-90 transition-opacity">Solicitar Demo</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="py-20 md:py-32 bg-gradient-to-b from-black via-gray-900 to-black relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-metallic-blue rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gold rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-metallic-blue/20 border border-metallic-blue/30 text-metallic-blue-light rounded-full text-sm font-semibold mb-6 metallic-shine">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Content Marketing Profesional</span>
                </div>

                <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">
                    <span class="text-transparent bg-clip-text blue-gradient">Tus Artículos.</span><br>
                    <span class="text-white">Tu Legado Digital Verificado.</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto mb-10">
                    Publicá contenido de valor, posicionáte como experto en tu sector y mejorá el SEO de tu presencia digital. <span class="text-metallic-blue-light font-bold">VERUMax Scripta</span> es tu blog profesional integrado.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="#caracteristicas" class="px-8 py-3 text-black font-bold blue-gradient rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-metallic-blue/50">Conocer Scripta</a>
                    <a href="contactus.php" class="px-8 py-3 text-metallic-blue-light font-bold bg-metallic-blue/10 border border-metallic-blue/30 rounded-lg hover:bg-metallic-blue/20 transition-colors flex items-center gap-2">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                        Solicitar Demo
                    </a>
                </div>

                <!-- Stats -->
                <div class="mt-16 grid grid-cols-2 md:grid-cols-3 gap-8 max-w-3xl mx-auto">
                    <div class="bg-gray-900/50 border border-metallic-blue/20 rounded-xl p-4 hover:border-metallic-blue/50 transition-all">
                        <div class="text-3xl md:text-4xl font-bold text-metallic-blue-light">SEO</div>
                        <div class="text-sm text-gray-400 mt-1">Optimizado</div>
                    </div>
                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-4 hover:border-gold/50 transition-all">
                        <div class="text-3xl md:text-4xl font-bold text-gold">2 Modos</div>
                        <div class="text-sm text-gray-400 mt-1">De Publicación</div>
                    </div>
                    <div class="bg-gray-900/50 border border-metallic-blue/20 rounded-xl p-4 hover:border-metallic-blue/50 transition-all col-span-2 md:col-span-1">
                        <div class="text-3xl md:text-4xl font-bold text-metallic-blue-light">100%</div>
                        <div class="text-sm text-gray-400 mt-1">Integrado</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modalidad Dual Section -->
    <section class="py-20 bg-gradient-to-b from-black to-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text blue-gradient mb-4">
                    Dos Formas de Publicar
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Elegís cómo querés crear tu contenido: rápido y simple, o completo y profesional
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-6xl mx-auto">
                <!-- Publicación Rápida -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-metallic-blue/30 rounded-2xl p-8 hover:border-metallic-blue hover:shadow-xl hover:shadow-metallic-blue/20 transition-all duration-300">
                    <div class="w-16 h-16 blue-gradient rounded-xl flex items-center justify-center mb-6">
                        <i data-lucide="zap" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-metallic-blue-light mb-4">Publicación Rápida</h3>
                    <p class="text-gray-400 mb-6">La vía express para publicar contenido al instante</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-metallic-blue-light flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Pegás tu texto en un solo campo</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-metallic-blue-light flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Primera línea = título automático</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-metallic-blue-light flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Publicación instantánea en texto plano</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-metallic-blue-light flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Botones de compartir en redes sociales</span>
                        </li>
                    </ul>
                    <div class="bg-metallic-blue/10 border border-metallic-blue/30 rounded-lg p-4">
                        <p class="text-xs text-metallic-blue-light font-semibold">Perfecto para: Noticias rápidas, actualizaciones, anuncios</p>
                    </div>
                </div>

                <!-- Artículo Completo -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gold/30 rounded-2xl p-8 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all duration-300">
                    <div class="w-16 h-16 gold-gradient rounded-xl flex items-center justify-center mb-6">
                        <i data-lucide="edit" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gold mb-4">Artículo Completo</h3>
                    <p class="text-gray-400 mb-6">Editor profesional para contenido de alto impacto</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-gold flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Editor visual WYSIWYG completo</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-gold flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Formato avanzado (negritas, listas, citas)</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-gold flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Imagen de portada destacada</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check" class="w-5 h-5 text-gold flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Categorías, slug personalizado y extracto</span>
                        </li>
                    </ul>
                    <div class="bg-gold/10 border border-gold/30 rounded-lg p-4">
                        <p class="text-xs text-gold font-semibold">Perfecto para: Artículos largos, estudios de caso, tutoriales</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Características Section -->
    <section id="caracteristicas" class="py-20 bg-black border-y border-metallic-blue/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text blue-gradient mb-4">
                    Características Principales
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Todo lo que necesitás para construir tu autoridad digital
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <!-- Feature 1 -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-metallic-blue/30 rounded-2xl p-6 hover:border-metallic-blue hover:shadow-xl hover:shadow-metallic-blue/20 transition-all duration-300">
                    <div class="w-14 h-14 blue-gradient rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="layout" class="w-7 h-7 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-metallic-blue-light mb-3">Integrado con tu Identitas</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">Tu landing page se actualiza automáticamente con "Últimos Artículos", transformándola de estática a dinámica.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-metallic-blue/30 rounded-2xl p-6 hover:border-metallic-blue hover:shadow-xl hover:shadow-metallic-blue/20 transition-all duration-300">
                    <div class="w-14 h-14 blue-gradient rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="search" class="w-7 h-7 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-metallic-blue-light mb-3">SEO Optimizado</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">URLs amigables, meta descripciones, extractos automáticos. Todo pensado para mejorar tu posicionamiento.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-metallic-blue/30 rounded-2xl p-6 hover:border-metallic-blue hover:shadow-xl hover:shadow-metallic-blue/20 transition-all duration-300">
                    <div class="w-14 h-14 blue-gradient rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="share-2" class="w-7 h-7 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-metallic-blue-light mb-3">Compartir Instantáneo</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">Botones directos para compartir en WhatsApp, LinkedIn y Twitter apenas publicás.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gold/30 rounded-2xl p-6 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all duration-300">
                    <div class="w-14 h-14 gold-gradient rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="folder" class="w-7 h-7 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gold mb-3">Categorías Personalizadas</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">Organizá tu contenido por temas. Tus lectores encuentran fácilmente lo que buscan.</p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gold/30 rounded-2xl p-6 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all duration-300">
                    <div class="w-14 h-14 gold-gradient rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="image" class="w-7 h-7 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gold mb-3">Imágenes de Portada</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">Cada artículo con su imagen destacada. Contenido más atractivo y profesional.</p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gold/30 rounded-2xl p-6 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all duration-300">
                    <div class="w-14 h-14 gold-gradient rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="save" class="w-7 h-7 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gold mb-3">Borradores</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">Guardá tu trabajo en progreso. Publicá cuando esté listo, sin apuros.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Integración Ecosistema Section -->
    <section class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text blue-gradient mb-4">
                    Integración Total con el Ecosistema VERUMax
                </h2>
                <p class="text-lg text-gray-400 max-w-3xl mx-auto">
                    Scripta no es solo un blog. Es un motor de contenido que potencia todas tus otras soluciones VERUMax
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <!-- Integración 1: Identitas -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border border-metallic-blue/30 rounded-xl p-6 hover:border-metallic-blue/50 transition-all">
                    <div class="w-12 h-12 blue-gradient rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="credit-card" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="text-lg font-bold text-metallic-blue-light mb-2">Identitas</h3>
                    <p class="text-sm text-gray-400">Tu landing page muestra automáticamente tus últimos artículos</p>
                </div>

                <!-- Integración 2: Vitae -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border border-metallic-blue/30 rounded-xl p-6 hover:border-metallic-blue/50 transition-all">
                    <div class="w-12 h-12 blue-gradient rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="file-text" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="text-lg font-bold text-metallic-blue-light mb-2">Vitae</h3>
                    <p class="text-sm text-gray-400">Añadí tus mejores artículos a tu CV en la sección "Publicaciones"</p>
                </div>

                <!-- Integración 3: Communica -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gold/30 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="mail" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gold mb-2">Communica</h3>
                    <p class="text-sm text-gray-400">Notificá automáticamente a tus suscriptores cuando publiques</p>
                </div>

                <!-- Integración 4: Cognita -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gold/30 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="bot" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gold mb-2">Cognita</h3>
                    <p class="text-sm text-gray-400">Tu Agente de IA aprende de tus artículos y responde en base a ellos</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Modalidad Dual: Autogestión vs Servicio Section -->
    <section class="py-20 bg-black border-y border-metallic-blue/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Elegí Cómo Querés Usarlo
                </h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Tenés conocimientos digitales o preferís que nuestro equipo se encargue de todo
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Opción 1: Autogestión -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-metallic-blue/30 rounded-2xl p-8 hover:border-metallic-blue hover:shadow-xl hover:shadow-metallic-blue/20 transition-all duration-300">
                    <div class="w-16 h-16 blue-gradient rounded-xl flex items-center justify-center mb-6">
                        <i data-lucide="layout-dashboard" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-metallic-blue-light mb-4">Autogestión</h3>
                    <p class="text-gray-400 mb-6">Vos mismo publicás y gestionás tu contenido desde el dashboard</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-metallic-blue-light flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Acceso completo al dashboard de Scripta</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-metallic-blue-light flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Ambos flujos de publicación disponibles</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-metallic-blue-light flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Publicá cuando quieras, sin límites</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-metallic-blue-light flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Soporte técnico incluido</span>
                        </li>
                    </ul>
                    <div class="bg-metallic-blue/10 border border-metallic-blue/30 rounded-lg p-4">
                        <p class="text-sm text-metallic-blue-light font-semibold">Incluido en: Identitas Plus y VERUMax Praxis</p>
                    </div>
                </div>

                <!-- Opción 2: Servicio Asistido -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gold/30 rounded-2xl p-8 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all duration-300">
                    <div class="w-16 h-16 gold-gradient rounded-xl flex items-center justify-center mb-6">
                        <i data-lucide="users" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gold mb-4">Servicio Asistido</h3>
                    <p class="text-gray-400 mb-6">Nuestro equipo humano escribe, formatea y publica por vos</p>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-gold flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Enviás tu contenido en bruto (Word, audio, ideas)</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-gold flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Nuestro equipo formatea y optimiza para SEO</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-gold flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Seleccionamos imagen de portada profesional</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i data-lucide="check-circle" class="w-5 h-5 text-gold flex-shrink-0 mt-0.5"></i>
                            <span class="text-sm text-gray-300">Publicamos y te notificamos</span>
                        </li>
                    </ul>
                    <div class="bg-gold/10 border border-gold/30 rounded-lg p-4">
                        <p class="text-sm text-gold font-semibold">Plan de Contenidos Mensual: desde 4 artículos/mes</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes y Precios Section -->
    <section id="planes" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-5xl font-bold text-transparent bg-clip-text blue-gradient mb-4">
                    Scripta Incluido en Todos los Planes Identitas
                </h2>
                <p class="text-lg text-gray-400 mb-3">
                    Blog profesional integrado desde el plan más básico hasta el más completo
                </p>
                <?php if (in_array($current_language, ['es_AR', 'es_CL', 'pt_BR']) && is_promo_active()): ?>
                <div class="inline-block mt-4 px-8 py-4 bg-gray-900 border-2 border-red-600 rounded-lg shadow-lg">
                    <p class="text-red-500 font-bold text-lg mb-2 flex items-center gap-2">
                        <span class="text-2xl">🔥</span>
                        PROMO LANZAMIENTO
                    </p>
                    <p class="text-gray-300 text-sm mb-1">
                        Alta bonificada: <?php echo get_alta_price_formatted($ALTA_PRICE_USD, $current_language); ?> (bonificado)
                    </p>
                    <p class="text-gold font-semibold text-base">50% DE DESCUENTO - Solo por tiempo limitado</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <!-- Plan Basicum -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2">Basicum</h3>
                    <p class="text-gray-400 text-xs mb-4">Blog básico incluido</p>
                    <div class="mb-4">
                        <div class="text-3xl font-bold text-white mb-2">
                            <?php echo display_price($PRICING['basicum'], $current_language, true, $DISCOUNT_PERCENTAGE); ?>
                        </div>
                        <p class="text-xs text-gold font-semibold">Pago único anual</p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Publicación Rápida</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>10 artículos/mes</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>SEO básico</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Soporte humano</span>
                        </li>
                    </ul>
                    <a href="identitas.php#planes" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-gold/30 text-gold font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        Ver Plan Completo
                    </a>
                </div>

                <!-- Plan Premium -->
                <div class="bg-gray-900 border-2 border-gold rounded-xl p-6 flex flex-col relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 gold-gradient text-black text-xs font-bold rounded-full uppercase">
                        Popular
                    </div>
                    <h3 class="text-xl font-bold text-gold mb-2 mt-2">Premium</h3>
                    <p class="text-gray-400 text-xs mb-4">Editor completo WYSIWYG</p>
                    <div class="mb-4">
                        <div class="text-3xl font-bold text-white mb-2">
                            <?php echo display_price($PRICING['premium'], $current_language, true, $DISCOUNT_PERCENTAGE); ?>
                        </div>
                        <p class="text-xs text-gold font-semibold">Pago único anual</p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span><strong>TODO Basicum +</strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Artículos ilimitados</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Imágenes de portada</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Categorías</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>SEO avanzado</span>
                        </li>
                    </ul>
                    <a href="identitas.php#planes" class="w-full text-center px-4 py-2.5 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity text-sm">
                        Ver Plan Completo
                    </a>
                </div>

                <!-- Plan Excellens -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2">Excellens</h3>
                    <p class="text-gray-400 text-xs mb-4">Integración completa</p>
                    <div class="mb-4">
                        <div class="text-3xl font-bold text-white mb-2">
                            <?php echo display_price($PRICING['excellens'], $current_language, true, $DISCOUNT_PERCENTAGE); ?>
                        </div>
                        <p class="text-xs text-gold font-semibold">Pago único anual</p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><strong>TODO Premium +</strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Integración Communica</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Integración Cognita IA</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Soporte prioritario</span>
                        </li>
                    </ul>
                    <a href="identitas.php#planes" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-gold/30 text-gold font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        Ver Plan Completo
                    </a>
                </div>

                <!-- Plan Supremus -->
                <div class="bg-gray-900 border border-gold/20 rounded-xl p-6 flex flex-col">
                    <h3 class="text-xl font-bold text-white mb-2">Supremus</h3>
                    <p class="text-gray-400 text-xs mb-4">Plan empresarial completo</p>
                    <div class="mb-4">
                        <div class="text-3xl font-bold text-white mb-2">
                            <?php echo display_price($PRICING['supremus'], $current_language, true, $DISCOUNT_PERCENTAGE); ?>
                        </div>
                        <p class="text-xs text-gold font-semibold">Pago único anual</p>
                    </div>
                    <ul class="space-y-2 mb-6 text-gray-300 text-xs flex-grow">
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><strong>TODO Excellens +</strong></span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Features premium</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Soporte VIP</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Acceso anticipado</span>
                        </li>
                    </ul>
                    <a href="identitas.php#planes" class="w-full text-center px-4 py-2.5 bg-gray-800 border border-gold/30 text-gold font-semibold rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        Ver Plan Completo
                    </a>
                </div>
            </div>

            <!-- Separador -->
            <div class="my-16 max-w-2xl mx-auto">
                <div class="h-px bg-gradient-to-r from-transparent via-gold/30 to-transparent"></div>
            </div>

            <!-- Servicio Asistido - Plan Adicional -->
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-8">
                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-2">
                        ¿Preferís que Nuestro Equipo Escriba por Vos?
                    </h3>
                    <p class="text-gray-400">
                        Agregá un plan de Servicio Asistido mensual a cualquier plan Identitas
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    <!-- 4 Artículos/mes -->
                    <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-metallic-blue/30 rounded-xl p-6 hover:border-metallic-blue hover:shadow-xl hover:shadow-metallic-blue/20 transition-all">
                        <div class="text-center mb-4">
                            <div class="text-3xl font-bold text-white mb-1">USD <?php echo $SCRIPTA_PRICING['servicio_asistido_4']; ?></div>
                            <p class="text-sm text-gray-400">por mes</p>
                        </div>
                        <ul class="space-y-2 mb-6 text-gray-300 text-sm">
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><strong>4 artículos profesionales/mes</strong></span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Equipo humano escribe por vos</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Optimización SEO completa</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Imágenes profesionales</span>
                            </li>
                        </ul>
                        <a href="contactus.php" class="w-full block text-center px-4 py-2.5 bg-gray-800 border border-metallic-blue/30 text-metallic-blue-light font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                            Contratar
                        </a>
                    </div>

                    <!-- 8 Artículos/mes -->
                    <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gold rounded-xl p-6 hover:shadow-xl hover:shadow-gold/20 transition-all relative">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 gold-gradient text-black text-xs font-bold rounded-full uppercase">
                            Más Popular
                        </div>
                        <div class="text-center mb-4 mt-2">
                            <div class="text-3xl font-bold text-white mb-1">USD <?php echo $SCRIPTA_PRICING['servicio_asistido_8']; ?></div>
                            <p class="text-sm text-gray-400">por mes</p>
                        </div>
                        <ul class="space-y-2 mb-6 text-gray-300 text-sm">
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>8 artículos profesionales/mes</strong></span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Equipo humano escribe por vos</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Optimización SEO completa</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-gold flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Estrategia de contenidos</span>
                            </li>
                        </ul>
                        <a href="contactus.php" class="w-full block text-center px-4 py-2.5 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity">
                            Contratar
                        </a>
                    </div>

                    <!-- 12 Artículos/mes -->
                    <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-metallic-blue/30 rounded-xl p-6 hover:border-metallic-blue hover:shadow-xl hover:shadow-metallic-blue/20 transition-all">
                        <div class="text-center mb-4">
                            <div class="text-3xl font-bold text-white mb-1">USD <?php echo $SCRIPTA_PRICING['servicio_asistido_12']; ?></div>
                            <p class="text-sm text-gray-400">por mes</p>
                        </div>
                        <ul class="space-y-2 mb-6 text-gray-300 text-sm">
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span><strong>12 artículos profesionales/mes</strong></span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Equipo humano escribe por vos</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Optimización SEO completa</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-metallic-green-light flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Máxima frecuencia de publicación</span>
                            </li>
                        </ul>
                        <a href="contactus.php" class="w-full block text-center px-4 py-2.5 bg-gray-800 border border-metallic-blue/30 text-metallic-blue-light font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                            Contratar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Nota adicional -->
            <div class="mt-12 text-center max-w-2xl mx-auto">
                <p class="text-gray-400 text-sm">
                    <i data-lucide="info" class="w-4 h-4 inline-block mr-1"></i>
                    Todos los planes Identitas incluyen Scripta. El Servicio Asistido es opcional y se suma a cualquier plan.
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Final Section -->
    <section class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">
                    Empezá a Construir tu <span class="text-transparent bg-clip-text blue-gradient">Autoridad Digital</span>
                </h2>
                <p class="text-lg text-gray-400 mb-10 max-w-2xl mx-auto">
                    Posicionáte como experto, mejorá tu SEO y nutriá a tu comunidad con contenido de valor. VERUMax Scripta te da las herramientas, vos o nuestro equipo crean el contenido.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="contactus.php" class="px-8 py-4 text-black font-bold blue-gradient rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-metallic-blue/50 text-lg">
                        Solicitar Demo Gratuita
                    </a>
                    <a href="index.php" class="px-8 py-4 text-metallic-blue-light font-bold bg-metallic-blue/10 border border-metallic-blue/30 rounded-lg hover:bg-metallic-blue/20 transition-colors text-lg">
                        Ver Más Soluciones
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-950 text-gray-300 border-t border-gold/10">
        <div class="container mx-auto px-6 py-12">
            <!-- Company Info & Links -->
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="h-8 w-8 text-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="text-xl font-bold text-gold">VERUMax</span>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-400">
                        Plataforma profesional de presencia digital verificada con soluciones integradas para profesionales e instituciones.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-gold font-bold mb-4">Enlaces Rápidos</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="index.php" class="hover:text-gold transition-colors">Inicio</a></li>
                        <li><a href="certificatum.php" class="hover:text-gold transition-colors">Solución Académica</a></li>
                        <li><a href="identitas.php" class="hover:text-gold transition-colors">Identitas</a></li>
                        <li><a href="scripta.php" class="hover:text-metallic-blue-light transition-colors">Scripta</a></li>
                        <li><a href="contactus.php" class="hover:text-gold transition-colors">Contacto</a></li>
                    </ul>
                </div>

                <!-- Legal Links -->
                <div>
                    <h4 class="text-gold font-bold mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="stipulationes.php" target="_blank" class="hover:text-gold transition-colors">Términos y Condiciones</a></li>
                        <li><a href="secretum.php" target="_blank" class="hover:text-gold transition-colors">Política de Privacidad</a></li>
                        <li><a href="mailto:contacto@verumax.com" class="hover:text-gold transition-colors">contacto@verumax.com</a></li>
                    </ul>
                </div>
            </div>

            <!-- Security & Trust Badges -->
            <div class="mb-12 pt-8 border-t border-gray-800">
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-500 mb-4">Tecnología y Seguridad de Nivel Empresarial</p>
                </div>
                <div class="flex flex-wrap justify-center items-center gap-4 md:gap-6">
                    <!-- SSL Secure -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold">SSL</div>
                            <div class="text-xs text-gray-400">Encriptación 256-bit</div>
                        </div>
                    </div>

                    <!-- HTTPS -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold">HTTPS</div>
                            <div class="text-xs text-gray-400">Conexión Segura</div>
                        </div>
                    </div>

                    <!-- Privacy -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold">Privacidad</div>
                            <div class="text-xs text-gray-400">Datos Protegidos</div>
                        </div>
                    </div>

                    <!-- Backup -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold">Backup</div>
                            <div class="text-xs text-gray-400">Automático Diario</div>
                        </div>
                    </div>

                    <!-- Uptime -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold">Uptime 99.9%</div>
                            <div class="text-xs text-gray-400">Disponibilidad</div>
                        </div>
                    </div>

                    <!-- Support -->
                    <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <div class="text-left">
                            <div class="text-xs font-bold text-gold">Soporte</div>
                            <div class="text-xs text-gray-400">Técnico 24/7</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center text-sm">
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <p class="text-gray-500">&copy; 2025 VERUMax. Todos los derechos reservados.</p>
                    <p class="mt-1 text-xs text-gray-600 opacity-60">v1.0.0</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Botón Chat Veritas (IA) -->
    <button id="veritasChatBtn" class="fixed bottom-24 right-8 w-14 h-14 bg-gradient-to-br from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 z-50 flex items-center justify-center group">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <span class="absolute right-16 bg-gray-900 text-white px-3 py-2 rounded-lg text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
            Chat con Veritas IA
        </span>
    </button>

    <!-- Botón Scroll to Top -->
    <button id="scrollToTop" class="fixed bottom-8 right-8 w-12 h-12 bg-gold hover:bg-gold-light text-black rounded-full shadow-lg hover:shadow-xl transition-all duration-300 opacity-0 pointer-events-none z-50 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <!-- Modal de Veritas -->
    <div id="veritasModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-purple-500/30 rounded-2xl max-w-md w-full p-8 relative animate-fade-in">
            <!-- Close button -->
            <button id="closeVeritas" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Icon -->
            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </div>

            <!-- Content -->
            <h2 class="text-2xl font-bold text-center mb-3 bg-gradient-to-r from-purple-400 to-indigo-400 bg-clip-text text-transparent">
                Veritas IA
            </h2>
            <p class="text-gray-300 text-center mb-2">
                Nuestro Agente de Inteligencia Artificial Especializado
            </p>
            <p class="text-gold text-center text-lg font-semibold mb-6">
                ¡Próximamente!
            </p>

            <div class="bg-gray-800/50 border border-purple-500/20 rounded-xl p-4 mb-6">
                <p class="text-sm text-gray-400 text-center leading-relaxed">
                    Veritas estará disponible muy pronto para ayudarte con consultas sobre certificados, validaciones y más.
                </p>
            </div>

            <button id="closeVeritasBtn" class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-lg transition-all duration-200">
                Entendido
            </button>
        </div>
    </div>

    <style>
    .metallic-green-light {
        color: #4CAF50;
    }

    @keyframes fade-in {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }
    </style>

    <script>
    // Botón Scroll to Top
    const scrollToTopBtn = document.getElementById('scrollToTop');

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.style.opacity = '1';
            scrollToTopBtn.style.pointerEvents = 'auto';
        } else {
            scrollToTopBtn.style.opacity = '0';
            scrollToTopBtn.style.pointerEvents = 'none';
        }
    });

    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Botón de Veritas IA
    const veritasChatBtn = document.getElementById('veritasChatBtn');
    const veritasModal = document.getElementById('veritasModal');
    const closeVeritas = document.getElementById('closeVeritas');
    const closeVeritasBtn = document.getElementById('closeVeritasBtn');

    veritasChatBtn.addEventListener('click', function() {
        veritasModal.classList.remove('hidden');
    });

    closeVeritas.addEventListener('click', function() {
        veritasModal.classList.add('hidden');
    });

    closeVeritasBtn.addEventListener('click', function() {
        veritasModal.classList.add('hidden');
    });

    // Cerrar al hacer clic fuera del modal
    veritasModal.addEventListener('click', function(e) {
        if (e.target === veritasModal) {
            veritasModal.classList.add('hidden');
        }
    });

    // Lucide Icons Init
    lucide.createIcons();
    </script>

</body>
</html>
