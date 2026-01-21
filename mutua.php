<?php
/**
 * OriginalisDoc para Mutuales - Landing Page
 * Solución completa para mutuales y asociaciones mutualistas
 */
require_once 'config.php';
require_once 'lang_config.php';
?>
<!DOCTYPE html>
<html lang="<?php echo substr($current_language, 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OriginalisDoc para Mutuales - Gestión Digital Integral</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Solución completa para mutuales y asociaciones mutualistas. Credenciales digitales, gestión de beneficios, portal del asociado y comunicación comunitaria.">
    <meta name="keywords" content="mutuales, asociaciones mutualistas, credenciales digitales, gestión de asociados, beneficios mutuales">

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle fill='%23D4AF37' cx='50' cy='50' r='45'/><path fill='none' stroke='%23fff' stroke-width='8' stroke-linecap='round' stroke-linejoin='round' d='M30 50 L42 62 L70 38'/></svg>">

    <!-- Flag Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css">

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
                        'mutual-blue': {
                            DEFAULT: '#1E40AF',
                            light: '#3B82F6',
                            dark: '#1E3A8A'
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
            background: #0a0a0a;
        }
        .gold-gradient {
            background: linear-gradient(135deg, #D4AF37 0%, #F0D377 100%);
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            animation: fadeIn 0.3s ease;
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-center;
        }
        .modal-content {
            background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
            padding: 2rem;
            border-radius: 1rem;
            border: 2px solid #D4AF37;
            max-width: 500px;
            margin: 20px;
            animation: slideIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
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
            <div class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-300 hover:text-gold font-medium transition-colors">Inicio</a>

                <!-- Soluciones por Sector Dropdown -->
                <div class="relative" id="dropdown-soluciones">
                    <button onclick="toggleDropdown('soluciones')" class="text-gray-300 hover:text-gold font-medium transition-colors flex items-center gap-1">
                        Soluciones
                        <i data-lucide="chevron-down" class="w-4 h-4" id="chevron-soluciones"></i>
                    </button>
                    <div id="menu-soluciones" class="absolute left-0 mt-2 w-56 bg-gray-900 border border-gold/30 rounded-lg shadow-xl opacity-0 invisible transition-all duration-200 z-50">
                        <a href="academicus.php?lang=<?php echo $current_language; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 first:rounded-t-lg">
                            <i data-lucide="graduation-cap" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium">Académico</span>
                        </a>
                        <a href="mutua.php?lang=<?php echo $current_language; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors text-gray-300 bg-gold/20">
                            <i data-lucide="heart-handshake" class="w-4 h-4 text-gold"></i>
                            <span class="text-sm font-medium">Mutuales</span>
                        </a>
                        <div class="px-4 py-3 hover:bg-gold/10 transition-colors text-gray-500 cursor-not-allowed flex items-center gap-3">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                            <span class="text-sm font-medium">Profesional</span>
                            <span class="ml-auto text-xs">Próximamente</span>
                        </div>
                        <div class="px-4 py-3 hover:bg-gold/10 transition-colors text-gray-500 cursor-not-allowed flex items-center gap-3">
                            <i data-lucide="ticket" class="w-4 h-4"></i>
                            <span class="text-sm font-medium">Eventos</span>
                            <span class="ml-auto text-xs">Próximamente</span>
                        </div>
                        <div class="px-4 py-3 hover:bg-gold/10 transition-colors text-gray-500 cursor-not-allowed flex items-center gap-3 last:rounded-b-lg">
                            <i data-lucide="building" class="w-4 h-4"></i>
                            <span class="text-sm font-medium">Empresarial</span>
                            <span class="ml-auto text-xs">Próximamente</span>
                        </div>
                    </div>
                </div>

                <a href="#pilares" class="text-gray-300 hover:text-gold font-medium transition-colors">Pilares</a>
                <a href="#planes" class="text-gray-300 hover:text-gold font-medium transition-colors">Planes</a>
                <a href="#faq" class="text-gray-300 hover:text-gold font-medium transition-colors">FAQ</a>

                <!-- Language Selector -->
                <div class="relative" id="lang-selector">
                    <button onclick="toggleLangMenu()" class="text-gray-300 hover:text-gold transition-colors px-3 py-2 flex items-center gap-2 border border-gray-700 rounded-lg hover:border-gold/50">
                        <?php echo get_flag_emoji($current_language); ?>
                        <span class="text-sm font-medium"><?php echo get_lang_short_name($current_language); ?></span>
                        <i data-lucide="chevron-down" class="w-4 h-4" id="lang-chevron"></i>
                    </button>
                    <div id="lang-menu" class="absolute right-0 mt-2 w-48 bg-gray-900 border border-gold/30 rounded-lg shadow-xl opacity-0 invisible transition-all duration-200 z-50">
                        <?php foreach ($available_languages as $code => $name): ?>
                        <a href="?lang=<?php echo $code; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gold/10 transition-colors <?php echo $current_language === $code ? 'bg-gold/20 text-gold' : 'text-gray-300'; ?> first:rounded-t-lg last:rounded-b-lg">
                            <?php echo get_flag_emoji($code); ?>
                            <span class="text-sm font-medium"><?php echo $name; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <a href="#contacto" class="px-5 py-2 text-black font-semibold gold-gradient rounded-lg hover:opacity-90 transition-opacity">Solicitar Demo</a>
            </div>
            <button class="md:hidden text-gold">
                <i data-lucide="menu"></i>
            </button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 bg-gradient-to-b from-black via-gray-950 to-black overflow-hidden">
        <div class="container mx-auto px-6 text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-gold/20 border border-gold/30 text-gold rounded-full text-sm font-semibold mb-6">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>✓ Solución Integral para Mutuales</span>
            </div>

            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6">
                <span class="text-transparent bg-clip-text gold-gradient">Gestión Digital</span><br>
                <span class="text-white">para Mutuales</span>
            </h1>

            <p class="mt-6 text-lg md:text-xl text-gray-300 max-w-3xl mx-auto mb-10">
                Un ecosistema completo para modernizar la gestión, potenciar los beneficios y fortalecer el sentido de comunidad de tu mutual
            </p>

            <div class="flex flex-wrap justify-center gap-4 mb-12">
                <a href="#planes" class="px-8 py-3 text-black font-bold gold-gradient rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/50">Solicitar Demo Personalizada</a>
                <a href="#pilares" class="px-8 py-3 text-gold font-bold bg-gold/10 border border-gold/30 rounded-lg hover:bg-gold/20 transition-colors">Ver Cómo Funciona</a>
            </div>

            <!-- Stats con Animación -->
            <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <div class="text-3xl md:text-4xl font-bold text-gold mb-2">
                        <span class="counter" data-target="90">0</span>%
                    </div>
                    <div class="text-sm text-gray-400">Menos tiempo administrativo</div>
                </div>
                <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <div class="text-3xl md:text-4xl font-bold text-gold mb-2">24/7</div>
                    <div class="text-sm text-gray-400">Portal del asociado</div>
                </div>
                <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 hover:border-gold/50 transition-all">
                    <div class="text-3xl md:text-4xl font-bold text-gold mb-2">
                        <span class="counter" data-target="100">0</span>%
                    </div>
                    <div class="text-sm text-gray-400">Gestión de beneficios en tiempo real</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Problemas Section -->
    <section class="py-20 bg-gradient-to-b from-black to-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">¿Te suena familiar?</h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">Los desafíos que enfrentan las mutuales tradicionales</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-gray-900/80 border-2 border-red-500/30 p-6 rounded-2xl">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="credit-card" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-red-400 mb-3">Credenciales de Plástico Obsoletas</h3>
                    <p class="text-gray-400 text-sm">Costos de impresión, extravíos constantes y dificultad para verificar el estado del asociado.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/30 p-6 rounded-2xl">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="ticket" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-red-400 mb-3">Gestión Manual de Beneficios</h3>
                    <p class="text-gray-400 text-sm">Cupones de papel, fraudes por doble uso y comercios que no pueden verificar descuentos.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/30 p-6 rounded-2xl">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="megaphone" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-red-400 mb-3">Comunicación Ineficiente</h3>
                    <p class="text-gray-400 text-sm">Emails que no se leen, carteleras físicas desactualizadas y dificultad para llegar a todos los asociados.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/30 p-6 rounded-2xl">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="users" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-red-400 mb-3">Baja Participación en Asambleas</h3>
                    <p class="text-gray-400 text-sm">Control manual de quórum, listados en papel y falta de herramientas para fomentar la participación.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/30 p-6 rounded-2xl">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="file-spreadsheet" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-red-400 mb-3">Gestión Administrativa Compleja</h3>
                    <p class="text-gray-400 text-sm">Planillas Excel, falta de trazabilidad y dificultad para generar reportes.</p>
                </div>

                <div class="bg-gray-900/80 border-2 border-red-500/30 p-6 rounded-2xl">
                    <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="trending-down" class="w-6 h-6 text-red-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-red-400 mb-3">Imagen Anticuada</h3>
                    <p class="text-gray-400 text-sm">Dificultad para atraer nuevos asociados jóvenes con procesos tradicionales y sin presencia digital.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pilares Section -->
    <section id="pilares" class="py-20 bg-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">Los 3 Pilares de Nuestra Solución</h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">Un ecosistema completo para transformar la gestión de tu mutual</p>
            </div>

            <!-- Pilar 1: Identidad y Beneficios del Asociado -->
            <div class="mb-20">
                <div class="bg-gradient-to-r from-gold/10 to-transparent border-l-4 border-gold p-6 rounded-r-xl mb-8">
                    <h3 class="text-2xl font-bold text-gold mb-2">Pilar 1: Identidad y Beneficios del Asociado</h3>
                    <p class="text-gray-300">El "Carnet Inteligente" - Credencial digital dinámica con validación en tiempo real</p>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-gray-900/80 border-2 border-gold/30 p-6 rounded-2xl hover:border-gold transition-all">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 gold-gradient rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="smartphone" class="w-7 h-7 text-black"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gold mb-2">Credencial Digital con QR</h4>
                                <p class="text-gray-400 text-sm mb-3">Un carnet digital infalsificable, accesible desde el celular, que reemplaza al plástico.</p>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Validación de estado en tiempo real (Activo, Suspendido, Vitalicio)</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Imposible de falsificar o clonar</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Siempre disponible en el celular del asociado</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900/80 border-2 border-gold/30 p-6 rounded-2xl hover:border-gold transition-all">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 gold-gradient rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="gift" class="w-7 h-7 text-black"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gold mb-2">Pasaporte de Beneficios</h4>
                                <p class="text-gray-400 text-sm mb-3">El QR de la credencial es la llave de acceso a toda la red de beneficios.</p>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Comercios escanean y ven descuentos aplicables al instante</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Elimina confusiones y fraudes</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Ejemplo: "Socio Activo - 20% en esta farmacia"</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900/80 border-2 border-gold/30 p-6 rounded-2xl hover:border-gold transition-all md:col-span-2">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 gold-gradient rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="ticket" class="w-7 h-7 text-black"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-gold mb-2">Vouchers y Cupones Digitales</h4>
                                <p class="text-gray-400 text-sm mb-3">Emití cupones de un solo uso para beneficios especiales.</p>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Ejemplo: "Un 2x1 en nuestro campo de deportes este mes"</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>El voucher aparece en el portal del asociado con su propio QR</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Se escanea y marca como "utilizado" automáticamente</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Evita doble uso y elimina costos de impresión</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pilar 2: Comunicación y Participación -->
            <div class="mb-20">
                <div class="bg-gradient-to-r from-mutual-blue/10 to-transparent border-l-4 border-mutual-blue p-6 rounded-r-xl mb-8">
                    <h3 class="text-2xl font-bold text-mutual-blue-light mb-2">Pilar 2: Comunicación y Participación Comunitaria</h3>
                    <p class="text-gray-300">El portal del asociado como verdadero centro de comunicación</p>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-gray-900/80 border-2 border-mutual-blue/30 p-6 rounded-2xl hover:border-mutual-blue transition-all">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-mutual-blue to-mutual-blue-light rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="megaphone" class="w-7 h-7 text-white"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-mutual-blue-light mb-2">Cartelera de Novedades Digital</h4>
                                <p class="text-gray-400 text-sm mb-3">Comunicación oficial centralizada, más efectiva que email o redes sociales.</p>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Anuncios importantes destacados al ingresar al portal</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Fechas de asambleas, nuevos convenios, vencimientos</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900/80 border-2 border-mutual-blue/30 p-6 rounded-2xl hover:border-mutual-blue transition-all">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-mutual-blue to-mutual-blue-light rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="calendar-check" class="w-7 h-7 text-white"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-mutual-blue-light mb-2">Gestión de Asambleas y Eventos</h4>
                                <p class="text-gray-400 text-sm mb-3">Simplifica organización y validación de quórum en asambleas.</p>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Confirmación de asistencia (RSVP) digital</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Pase con QR generado automáticamente</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Control de quórum en tiempo real al escanear pases</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900/80 border-2 border-mutual-blue/30 p-6 rounded-2xl hover:border-mutual-blue transition-all md:col-span-2">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-mutual-blue to-mutual-blue-light rounded-xl flex items-center justify-center flex-shrink-0">
                                <i data-lucide="clipboard-list" class="w-7 h-7 text-white"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-mutual-blue-light mb-2">Encuestas y Votaciones Simples</h4>
                                <p class="text-gray-400 text-sm mb-3">Fomenta la participación y toma el pulso de la comunidad.</p>
                                <ul class="space-y-2">
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Encuestas de satisfacción y votaciones no vinculantes</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Solo asociados activos pueden participar (seguridad garantizada)</span>
                                    </li>
                                    <li class="flex items-start gap-2 text-sm text-gray-300">
                                        <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                                        <span>Resultados en tiempo real para la comisión directiva</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pilar 3: Plataforma de Gestión y Crecimiento -->
            <div>
                <div class="bg-gradient-to-r from-green-500/10 to-transparent border-l-4 border-green-500 p-6 rounded-r-xl mb-8">
                    <h3 class="text-2xl font-bold text-green-400 mb-2">Pilar 3: Plataforma de Gestión y Crecimiento</h3>
                    <p class="text-gray-300">Servicios que le solucionan la vida a la administración de la mutual</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-900/80 border-2 border-green-500/30 p-6 rounded-2xl hover:border-green-500 transition-all">
                        <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="users-round" class="w-6 h-6 text-green-400"></i>
                        </div>
                        <h4 class="text-xl font-bold text-green-400 mb-2">Gestión Integral de Asociados</h4>
                        <p class="text-gray-400 text-sm">Panel de control central para administrar todo el ciclo de vida del socio. Alta instantánea, modificación de estados y generación de listados filtrados.</p>
                    </div>

                    <div class="bg-gray-900/80 border-2 border-green-500/30 p-6 rounded-2xl hover:border-green-500 transition-all">
                        <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="graduation-cap" class="w-6 h-6 text-green-400"></i>
                        </div>
                        <h4 class="text-xl font-bold text-green-400 mb-2">Plataforma de Formación</h4>
                        <p class="text-gray-400 text-sm">Si tu mutual ofrece cursos o capacitaciones, utiliza nuestro módulo académico para emitir certificados, analíticos y constancias.</p>
                    </div>

                    <div class="bg-gray-900/80 border-2 border-green-500/30 p-6 rounded-2xl hover:border-green-500 transition-all">
                        <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mb-4">
                            <i data-lucide="globe" class="w-6 h-6 text-green-400"></i>
                        </div>
                        <h4 class="text-xl font-bold text-green-400 mb-2">Landing Page Institucional</h4>
                        <p class="text-gray-400 text-sm">Tu presencia digital profesional para atraer nuevos miembros, con enlaces a redes sociales y formulario de afiliación.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Otras Soluciones Disponibles -->
    <section id="soluciones" class="py-20 bg-gradient-to-b from-black to-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">Otras Soluciones Disponibles</h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">Expandí las capacidades de tu mutual con nuestro ecosistema completo de herramientas</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
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
                    <p class="text-gray-400 text-sm">Diplomas y analíticos digitales verificables</p>
                </a>

                <!-- Landing Pages Institucionales -->
                <div class="solution-card bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group cursor-pointer"
                     data-solution="Landing Pages Institucionales"
                     data-description="Presencia web profesional para tu mutual con diseño personalizado, formularios de afiliación y enlaces a redes sociales.">
                    <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="globe" class="w-6 h-6 text-gold"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">Landing Pages Institucionales</h3>
                    <p class="text-gray-400 text-sm">Presencia web profesional para tu mutual</p>
                </div>

                <!-- Agentes de IA -->
                <div class="solution-card bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group cursor-pointer"
                     data-solution="Agentes de IA Especializados"
                     data-description="Asistentes inteligentes personalizados para atención al asociado, consultas frecuentes y gestión automatizada.">
                    <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="bot" class="w-6 h-6 text-gold"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">Agentes de IA Especializados</h3>
                    <p class="text-gray-400 text-sm">Asistentes inteligentes personalizados</p>
                </div>

                <!-- Portfolios Evolucionados -->
                <div class="solution-card bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group cursor-pointer"
                     data-solution="Portfolios Evolucionados"
                     data-description="Muestra proyectos, servicios y logros de tu mutual con validación profesional y diseño interactivo.">
                    <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="briefcase" class="w-6 h-6 text-gold"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">Portfolios Evolucionados</h3>
                    <p class="text-gray-400 text-sm">Muestra tu trabajo con validación profesional</p>
                </div>

                <!-- CV Inteligentes -->
                <div class="solution-card bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group cursor-pointer"
                     data-solution="CV Inteligentes"
                     data-description="Currículum verificable y actualizable para tus asociados con certificaciones validadas y trayectoria profesional.">
                    <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="file-text" class="w-6 h-6 text-gold"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">CV Inteligentes</h3>
                    <p class="text-gray-400 text-sm">Currículum verificable y actualizable</p>
                </div>

                <!-- Certificados de Autenticidad -->
                <div class="solution-card bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group cursor-pointer"
                     data-solution="Certificados de Autenticidad"
                     data-description="Para productos, obras de arte, artesanías y más. Validación con QR infalsificable para garantizar autenticidad.">
                    <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="badge-check" class="w-6 h-6 text-gold"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">Certificados de Autenticidad</h3>
                    <p class="text-gray-400 text-sm">Para productos, obras de arte y más</p>
                </div>

                <!-- Impresión Premium -->
                <div class="solution-card bg-gray-900/80 border-2 border-gray-700 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all group cursor-pointer"
                     data-solution="Impresión Premium"
                     data-description="Documentos físicos de alta calidad con hologramas de seguridad, laminado premium y envío a domicilio.">
                    <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center mb-4">
                        <i data-lucide="printer" class="w-6 h-6 text-gold"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 group-hover:text-gold transition-colors">Impresión Premium</h3>
                    <p class="text-gray-400 text-sm">Documentos físicos de alta calidad</p>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="index.php#productos" class="inline-flex items-center gap-2 text-gold hover:text-gold-light transition-colors font-semibold">
                    <span>Ver todas las soluciones</span>
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Servicios Principales -->
    <section class="py-20 bg-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">Nuestros Servicios Principales</h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">Funcionalidades esenciales que todas nuestras mutuales disfrutan, sin importar el plan</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Seguridad Anti-Fraude -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-gold/40 p-6 rounded-2xl hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all">
                    <div class="w-16 h-16 gold-gradient rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="shield-check" class="w-8 h-8 text-black"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gold text-center mb-3">Seguridad Anti-Fraude Total</h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center">Credenciales con QR único e imposible de falsificar que garantizan la autenticidad de cada asociado.</p>
                </div>

                <!-- Portal 24/7 -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-emerald-500/40 p-6 rounded-2xl hover:border-emerald-500 hover:shadow-xl hover:shadow-emerald-500/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-600 to-emerald-400 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="clock" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-400 text-center mb-3">Portal 24/7</h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center">Acceso permanente para asociados desde cualquier dispositivo, en cualquier momento.</p>
                </div>

                <!-- Branding Personalizado -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-purple-500/40 p-6 rounded-2xl hover:border-purple-500 hover:shadow-xl hover:shadow-purple-500/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-purple-400 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="palette" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-purple-400 text-center mb-3">Branding Personalizado</h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center">Portal y credenciales con tu logo, colores y diseño institucional único.</p>
                </div>

                <!-- Gestión Simplificada -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-blue-500/40 p-6 rounded-2xl hover:border-blue-500 hover:shadow-xl hover:shadow-blue-500/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-400 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="users-round" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blue-400 text-center mb-3">Gestión Simplificada</h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center">Panel de administración intuitivo para gestionar asociados, beneficios y comunicación.</p>
                </div>

                <!-- Actualizaciones en Tiempo Real -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-orange-500/40 p-6 rounded-2xl hover:border-orange-500 hover:shadow-xl hover:shadow-orange-500/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-600 to-orange-400 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="zap" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-orange-400 text-center mb-3">Actualizaciones Instantáneas</h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center">Cambios de estado, beneficios y novedades se reflejan al instante en el portal del asociado.</p>
                </div>

                <!-- Validación Pública -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 border-2 border-green-500/40 p-6 rounded-2xl hover:border-green-500 hover:shadow-xl hover:shadow-green-500/20 transition-all">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-green-400 rounded-2xl flex items-center justify-center mb-4 mx-auto">
                        <i data-lucide="search-check" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-green-400 text-center mb-3">Validación Pública</h3>
                    <p class="text-gray-300 text-sm leading-relaxed text-center">Comercios y terceros verifican la credencial y estado del asociado al instante.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes y Precios -->
    <section id="planes" class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">Elegí el Plan que Mejor se Adapta a tu Mutual</h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">Todos los planes incluyen credenciales digitales con QR infalsificable y portal del asociado 24/7</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <!-- Plan Basicum -->
                <div class="bg-gray-900/80 border-2 border-gray-700 rounded-2xl p-6 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all">
                    <h3 class="text-2xl font-bold text-white mb-2">Basicum</h3>
                    <p class="text-gray-400 text-sm mb-4">Para mutuales pequeñas que comienzan su transformación digital</p>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gold">$49 USD</span>
                        <span class="text-gray-400"> / mes</span>
                    </div>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Hasta 100 asociados</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Credenciales digitales con QR</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Portal del asociado básico</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Validación de estado en tiempo real</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Cartelera de novedades</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Soporte por email</span>
                        </li>
                    </ul>
                    <button class="w-full px-6 py-3 bg-gray-700 text-white font-bold rounded-lg hover:bg-gray-600 transition-colors">Comenzar con Basicum</button>
                </div>

                <!-- Plan Premium -->
                <div class="bg-gray-900/80 border-2 border-gold rounded-2xl p-6 relative hover:shadow-2xl hover:shadow-gold/30 transition-all transform hover:scale-105">
                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 px-4 py-1 gold-gradient text-black text-xs font-bold rounded-full">
                        Más Popular
                    </div>
                    <h3 class="text-2xl font-bold text-gold mb-2">Premium</h3>
                    <p class="text-gray-400 text-sm mb-4">Todo lo de Basicum más gestión avanzada de beneficios</p>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gold">$99 USD</span>
                        <span class="text-gray-400"> / mes</span>
                    </div>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span class="font-semibold">Todo lo del plan Basicum, más:</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Hasta 500 asociados</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Pasaporte de Beneficios</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Vouchers y cupones digitales</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Gestión de eventos y asambleas</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Encuestas y votaciones</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Branding personalizado</span>
                        </li>
                    </ul>
                    <button class="w-full px-6 py-3 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity">Obtener Plan Premium</button>
                </div>

                <!-- Plan Excellens -->
                <div class="bg-gray-900/80 border-2 border-gray-700 rounded-2xl p-6 hover:border-gold hover:shadow-xl hover:shadow-gold/20 transition-all">
                    <h3 class="text-2xl font-bold text-white mb-2">Excellens</h3>
                    <p class="text-gray-400 text-sm mb-4">Para mutuales que necesitan herramientas completas de gestión</p>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gold">$199 USD</span>
                        <span class="text-gray-400"> / mes</span>
                    </div>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span class="font-semibold">Todo lo del plan Premium, más:</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Hasta 2000 asociados</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Gestión integral de asociados</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Plataforma de formación incluida</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Reportes y analíticas avanzadas</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Integraciones API</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Soporte prioritario 24/7</span>
                        </li>
                    </ul>
                    <button class="w-full px-6 py-3 bg-gray-700 text-white font-bold rounded-lg hover:bg-gray-600 transition-colors">Obtener Plan Excellens</button>
                </div>

                <!-- Plan Supremus -->
                <div class="bg-gray-900/80 border-2 border-purple-500 rounded-2xl p-6 hover:shadow-xl hover:shadow-purple-500/20 transition-all">
                    <div class="px-3 py-1 bg-purple-500/20 text-purple-400 text-xs font-bold rounded-full inline-block mb-2">
                        Empresarial
                    </div>
                    <h3 class="text-2xl font-bold text-purple-400 mb-2">Supremus</h3>
                    <p class="text-gray-400 text-sm mb-4">Solución definitiva con personalización total</p>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-purple-400">Custom</span>
                    </div>
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span class="font-semibold">Todo lo del plan Excellens, más:</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Asociados ilimitados</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Dominio propio incluido</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Diseño 100% personalizado</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Multi-tenancy para filiales</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>Gerente de cuenta dedicado</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm">
                            <i data-lucide="check" class="w-4 h-4 text-green-400 flex-shrink-0 mt-1"></i>
                            <span>SLA empresarial</span>
                        </li>
                    </ul>
                    <button class="w-full px-6 py-3 bg-purple-500 text-white font-bold rounded-lg hover:bg-purple-600 transition-colors">Contactar Ventas</button>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-20 bg-black">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-transparent bg-clip-text gold-gradient mb-4">Preguntas Frecuentes</h2>
                <p class="text-lg text-gray-400">Todo lo que necesitas saber sobre OriginalisDoc para Mutuales</p>
            </div>

            <div class="space-y-4">
                <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-gold mb-3">¿Cuánto tiempo lleva implementar el sistema?</h3>
                    <p class="text-gray-400">Menos de 48 horas. Te ayudamos a configurar tu branding, cargar los datos de tus asociados y comenzar a emitir credenciales digitales. Incluye onboarding guiado paso a paso.</p>
                </div>

                <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-gold mb-3">¿Los asociados necesitan instalar algo?</h3>
                    <p class="text-gray-400">No. La credencial digital funciona desde cualquier navegador web en su celular. No requiere instalación de apps. Solo acceden con su DNI a su portal personal.</p>
                </div>

                <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-gold mb-3">¿Cómo funcionan los vouchers digitales?</h3>
                    <p class="text-gray-400">Desde el panel de administración, emitís vouchers con fecha de vencimiento y restricciones. El asociado lo ve en su portal, presenta el QR, se escanea y queda marcado como usado. Imposible reutilizarlo.</p>
                </div>

                <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-gold mb-3">¿Puedo migrar datos desde mi sistema actual?</h3>
                    <p class="text-gray-400">Sí. Aceptamos archivos Excel/CSV con tus asociados actuales. Te proporcionamos una plantilla para facilitar la migración. También ofrecemos servicio de migración asistida en planes Excellens y Supremus.</p>
                </div>

                <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-gold mb-3">¿Qué pasa si un asociado pierde acceso a su portal?</h3>
                    <p class="text-gray-400">Puede recuperarlo usando su DNI y verificación por email. Como administrador, también podés regenerar el acceso en cualquier momento desde el panel de gestión.</p>
                </div>

                <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-gold mb-3">¿Puedo tener múltiples administradores?</h3>
                    <p class="text-gray-400">Sí. En todos los planes podés crear usuarios administradores con diferentes roles y permisos. Por ejemplo: uno para gestión de asociados, otro para comunicación, etc.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section id="contacto" class="py-20 bg-gradient-to-b from-gray-950 to-black">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-5xl font-bold mb-6">
                    <span class="text-transparent bg-clip-text gold-gradient">Moderniza tu Mutual Hoy</span>
                </h2>
                <p class="text-xl text-gray-300 max-w-2xl mx-auto mb-10">
                    Contactanos para una demostración personalizada y descubrí cómo OriginalisDoc puede transformar tu gestión documental
                </p>
            </div>

            <!-- Beneficios Grid -->
            <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto mb-12">
                <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 text-center">
                    <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="play-circle" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="font-bold text-gold mb-2">Demo gratis sin compromiso</h3>
                    <p class="text-sm text-gray-400">Prueba todas las funciones</p>
                </div>

                <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 text-center">
                    <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="zap" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="font-bold text-gold mb-2">Implementación en 48hs</h3>
                    <p class="text-sm text-gray-400">Comienza a emitir de inmediato</p>
                </div>

                <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 text-center">
                    <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="headphones" class="w-6 h-6 text-black"></i>
                    </div>
                    <h3 class="font-bold text-gold mb-2">Soporte técnico incluido</h3>
                    <p class="text-sm text-gray-400">Asistencia completa</p>
                </div>
            </div>

            <!-- CTAs -->
            <div class="flex flex-wrap justify-center gap-4 mb-8">
                <a href="#planes" class="px-8 py-4 text-black font-bold gold-gradient rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/50 text-lg">Solicitar Demo Gratis</a>
                <a href="mailto:contacto@validarcert.com" class="px-8 py-4 text-gold font-bold bg-gold/10 border-2 border-gold/30 rounded-lg hover:bg-gold/20 transition-colors text-lg inline-flex items-center gap-2">
                    <span>O escríbenos a</span>
                    <span class="text-gold-light">contacto@validarcert.com</span>
                </a>
            </div>

            <!-- Social Proof -->
            <div class="text-center">
                <p class="text-gray-500 text-sm mb-4">Instituciones y mutuales que ya confían en OriginalisDoc</p>
                <div class="flex flex-wrap justify-center gap-8 items-center opacity-50">
                    <div class="text-gray-600 font-semibold">SAJuR</div>
                    <div class="text-gray-600 font-semibold">Liberté</div>
                    <div class="text-gray-600 font-semibold">+ más organizaciones</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>

    <!-- Modal para soluciones en desarrollo -->
    <div id="solutionModal" class="modal">
        <div class="modal-content">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-12 h-12 bg-gold/20 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i data-lucide="construction" class="w-6 h-6 text-gold"></i>
                </div>
                <div class="flex-1">
                    <h3 id="modalTitle" class="text-2xl font-bold text-gold mb-2"></h3>
                    <p id="modalDescription" class="text-gray-300 text-sm mb-4"></p>
                </div>
            </div>

            <div class="bg-gold/10 border border-gold/30 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2 text-gold mb-2">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                    <span class="font-semibold">En Fase de Desarrollo</span>
                </div>
                <p class="text-gray-400 text-sm">Esta solución estará disponible próximamente. Contactanos para más información y ser notificado cuando esté lista.</p>
            </div>

            <div class="flex gap-3">
                <button onclick="closeModal()" class="flex-1 px-6 py-3 bg-gray-700 text-white font-bold rounded-lg hover:bg-gray-600 transition-colors">
                    Cerrar
                </button>
                <a href="mailto:contacto@validarcert.com?subject=Consulta sobre solución" class="flex-1 px-6 py-3 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity text-center">
                    Contactar
                </a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Animación de contadores
        function animateCounter(element) {
            const target = parseInt(element.getAttribute('data-target'));
            const duration = 2000; // 2 segundos
            const step = target / (duration / 16); // 60fps
            let current = 0;

            const updateCounter = () => {
                current += step;
                if (current < target) {
                    element.textContent = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target;
                }
            };

            updateCounter();
        }

        // Intersection Observer para activar animación cuando es visible
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                    entry.target.classList.add('animated');
                    animateCounter(entry.target);
                }
            });
        }, observerOptions);

        // Observar todos los contadores
        document.addEventListener('DOMContentLoaded', () => {
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => observer.observe(counter));

            // Event listeners para tarjetas de soluciones en desarrollo
            const solutionCards = document.querySelectorAll('.solution-card');
            solutionCards.forEach(card => {
                card.addEventListener('click', () => {
                    const title = card.getAttribute('data-solution');
                    const description = card.getAttribute('data-description');
                    showModal(title, description);
                });
            });
        });

        // Funciones del modal
        function showModal(title, description) {
            const modal = document.getElementById('solutionModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalDescription = document.getElementById('modalDescription');

            modalTitle.textContent = title;
            modalDescription.textContent = description;
            modal.classList.add('active');

            // Recrear iconos de lucide dentro del modal
            lucide.createIcons();
        }

        function closeModal() {
            const modal = document.getElementById('solutionModal');
            modal.classList.remove('active');
        }

        // Cerrar modal al hacer clic fuera de él
        document.getElementById('solutionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Función para toggle de dropdowns (Soluciones)
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

        // Función para toggle del menú de idiomas
        function toggleLangMenu() {
            const menu = document.getElementById('lang-menu');
            const chevron = document.getElementById('lang-chevron');

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

            // Cerrar menú de idiomas
            const langSelector = document.getElementById('lang-selector');
            const langMenu = document.getElementById('lang-menu');
            const langChevron = document.getElementById('lang-chevron');

            if (langSelector && !langSelector.contains(event.target)) {
                langMenu.classList.add('opacity-0', 'invisible');
                langMenu.classList.remove('opacity-100', 'visible');
                langChevron.style.transform = 'rotate(0deg)';
            }
        });
    </script>
</body>
</html>
