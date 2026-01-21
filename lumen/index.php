<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumen - Portfolio Digital Profesional | OriginalisDoc</title>
    <meta name="description" content="Solución completa de portfolio digital para fotógrafos y creativos. Gestiona tu galería profesional con protección avanzada y entrega privada a clientes.">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

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
        h1, h2, h3, h4 {
            font-family: 'Playfair Display', serif;
        }

        .gradient-lumen {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gradient-lumen-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 bg-white/95 backdrop-blur-sm border-b border-gray-200 z-50">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 gradient-lumen rounded-lg flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-900">Lumen</span>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#caracteristicas" class="text-gray-600 hover:text-purple-600 transition-colors">Características</a>
                    <a href="#planes" class="text-gray-600 hover:text-purple-600 transition-colors">Planes</a>
                    <a href="#demo" class="text-gray-600 hover:text-purple-600 transition-colors">Demo</a>
                    <a href="login.php" class="px-6 py-2 gradient-lumen text-white font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        Acceder
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 text-gray-600 hover:text-purple-600">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 bg-white">
            <div class="container mx-auto px-4 py-4 space-y-4">
                <a href="#caracteristicas" class="block text-gray-600 hover:text-purple-600 transition-colors">Características</a>
                <a href="#planes" class="block text-gray-600 hover:text-purple-600 transition-colors">Planes</a>
                <a href="#demo" class="block text-gray-600 hover:text-purple-600 transition-colors">Demo</a>
                <a href="login.php" class="block px-6 py-2 gradient-lumen text-white font-semibold rounded-lg text-center">
                    Acceder
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 bg-gradient-to-b from-purple-50 to-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid lg:grid-cols-2 gap-12 items-center max-w-6xl mx-auto">
                <!-- Content -->
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold mb-6">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                        <span>Solución Profesional para Creativos</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                        Tu Portfolio Digital
                        <span class="block gradient-lumen-text">Iluminado</span>
                    </h1>

                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Gestiona y presenta tu trabajo creativo con una galería profesional de alto impacto. Lumen es la solución completa para fotógrafos que valoran su trabajo.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="#planes" class="px-8 py-4 gradient-lumen text-white font-bold rounded-lg hover:opacity-90 transition-all shadow-lg hover:shadow-xl hover:scale-105">
                            Ver Planes
                        </a>
                        <a href="#demo" class="px-8 py-4 bg-white text-purple-700 font-bold rounded-lg border-2 border-purple-300 hover:border-purple-500 transition-all">
                            Ver Demo
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6 mt-12 pt-8 border-t border-gray-200">
                        <div>
                            <div class="text-2xl font-bold text-purple-600">100%</div>
                            <div class="text-sm text-gray-600">Seguro</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-purple-600">∞</div>
                            <div class="text-sm text-gray-600">Galerías</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-purple-600">HD</div>
                            <div class="text-sm text-gray-600">Calidad</div>
                        </div>
                    </div>
                </div>

                <!-- Mockup -->
                <div class="relative">
                    <div class="float-animation">
                        <div class="bg-gradient-to-br from-purple-100 to-pink-100 rounded-2xl p-8 shadow-2xl">
                            <div class="aspect-[4/3] bg-white rounded-xl shadow-lg overflow-hidden">
                                <!-- Simulated Portfolio -->
                                <div class="h-full flex flex-col">
                                    <div class="bg-gray-50 p-4 border-b border-gray-200 flex items-center gap-3">
                                        <div class="w-8 h-8 gradient-lumen rounded-lg"></div>
                                        <div class="text-sm font-semibold text-gray-700">Mi Portfolio</div>
                                    </div>
                                    <div class="flex-1 p-4 grid grid-cols-3 gap-2">
                                        <div class="bg-gradient-to-br from-purple-300 to-pink-300 rounded aspect-square"></div>
                                        <div class="bg-gradient-to-br from-blue-300 to-purple-300 rounded aspect-square"></div>
                                        <div class="bg-gradient-to-br from-pink-300 to-red-300 rounded aspect-square"></div>
                                        <div class="bg-gradient-to-br from-yellow-300 to-orange-300 rounded aspect-square"></div>
                                        <div class="bg-gradient-to-br from-green-300 to-teal-300 rounded aspect-square"></div>
                                        <div class="bg-gradient-to-br from-indigo-300 to-purple-300 rounded aspect-square"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Decorative Elements -->
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-purple-200 rounded-full blur-3xl opacity-60"></div>
                    <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-pink-200 rounded-full blur-3xl opacity-60"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="caracteristicas" class="py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Características Profesionales
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Todo lo que necesitas para gestionar y presentar tu trabajo creativo
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Feature Cards -->
                <div class="bg-gradient-to-br from-purple-50 to-white p-8 rounded-2xl border border-purple-100 hover:border-purple-300 transition-all hover:shadow-lg">
                    <div class="w-12 h-12 gradient-lumen rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="images" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Galerías Ilimitadas</h3>
                    <p class="text-gray-600">Organiza tu trabajo en múltiples galerías temáticas. Bodas, eventos, retratos - cada categoría con su propio espacio.</p>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-white p-8 rounded-2xl border border-blue-100 hover:border-blue-300 transition-all hover:shadow-lg">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="shield-check" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Protección Anti-Descarga</h3>
                    <p class="text-gray-600">Protege tu trabajo con sistemas de seguridad avanzados. Marca de agua automática y protección contra descargas no autorizadas.</p>
                </div>

                <div class="bg-gradient-to-br from-pink-50 to-white p-8 rounded-2xl border border-pink-100 hover:border-pink-300 transition-all hover:shadow-lg">
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="users" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Entrega a Clientes</h3>
                    <p class="text-gray-600">Comparte galerías privadas con tus clientes. Links únicos y acceso controlado para cada proyecto.</p>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-white p-8 rounded-2xl border border-green-100 hover:border-green-300 transition-all hover:shadow-lg">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="zap" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Optimización Automática</h3>
                    <p class="text-gray-600">Tus imágenes se optimizan automáticamente para web sin perder calidad. Carga rápida garantizada.</p>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-white p-8 rounded-2xl border border-yellow-100 hover:border-yellow-300 transition-all hover:shadow-lg">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="palette" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Branding Personalizado</h3>
                    <p class="text-gray-600">Personaliza colores, fuentes y diseño para que tu portfolio refleje tu identidad visual única.</p>
                </div>

                <div class="bg-gradient-to-br from-indigo-50 to-white p-8 rounded-2xl border border-indigo-100 hover:border-indigo-300 transition-all hover:shadow-lg">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mb-4">
                        <i data-lucide="smartphone" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">100% Responsive</h3>
                    <p class="text-gray-600">Tu portfolio se ve perfecto en cualquier dispositivo. Desktop, tablet o móvil - siempre impecable.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="planes" class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Planes TarjetaDigital + Lumen
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Lumen está incluido como bonus en todos los planes de TarjetaDigital. Elige el plan que mejor se adapte a tus necesidades.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <!-- Plan Basicum -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 overflow-hidden hover:border-purple-400 hover:shadow-2xl transition-all">
                    <div class="bg-gradient-to-br from-gray-600 to-gray-700 p-6 text-white text-center">
                        <h3 class="text-2xl font-bold mb-2">Basicum</h3>
                        <div class="text-4xl font-bold mb-2">$19.99<span class="text-lg font-normal">/mes</span></div>
                        <p class="text-gray-200 text-sm">Perfecto para empezar</p>
                    </div>

                    <div class="p-6">
                        <div class="space-y-3 mb-6">
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700"><strong>TarjetaDigital</strong> básico</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700"><strong>Lumen:</strong> 50 fotos</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">3 galerías</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">5 GB almacenamiento</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Protección básica</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Subdominio incluido</span>
                            </div>
                        </div>

                        <a href="../index.html" class="block w-full px-6 py-3 bg-gray-600 text-white font-bold rounded-lg text-center hover:bg-gray-700 transition-colors">
                            Comenzar
                        </a>
                    </div>
                </div>

                <!-- Plan Premium (Destacado) -->
                <div class="bg-white rounded-2xl shadow-2xl border-2 border-purple-400 overflow-hidden transform lg:scale-105 relative">
                    <div class="absolute top-0 right-0 bg-purple-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                        POPULAR
                    </div>
                    <div class="gradient-lumen p-6 text-white text-center">
                        <h3 class="text-2xl font-bold mb-2">Premium</h3>
                        <div class="text-4xl font-bold mb-2">$39.99<span class="text-lg font-normal">/mes</span></div>
                        <p class="text-purple-100 text-sm">Lo mejor para fotógrafos</p>
                    </div>

                    <div class="p-6">
                        <div class="text-xs text-gray-600 mb-3 italic">Todo de Basicum, más:</div>
                        <div class="space-y-3 mb-6">
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700"><strong>Lumen:</strong> 200 fotos</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">10 galerías</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">20 GB almacenamiento</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Marca de agua personalizada</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Entrega privada a clientes</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">SEO optimizado</span>
                            </div>
                        </div>

                        <a href="../index.html" class="block w-full px-6 py-3 gradient-lumen text-white font-bold rounded-lg text-center hover:opacity-90 transition-opacity shadow-lg">
                            Contratar Ahora
                        </a>
                    </div>
                </div>

                <!-- Plan Pro -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 overflow-hidden hover:border-blue-400 hover:shadow-2xl transition-all">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-6 text-white text-center">
                        <h3 class="text-2xl font-bold mb-2">Pro</h3>
                        <div class="text-4xl font-bold mb-2">$69.99<span class="text-lg font-normal">/mes</span></div>
                        <p class="text-blue-100 text-sm">Para profesionales</p>
                    </div>

                    <div class="p-6">
                        <div class="text-xs text-gray-600 mb-3 italic">Todo de Premium, más:</div>
                        <div class="space-y-3 mb-6">
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700"><strong>Lumen:</strong> 500 fotos</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Galerías ilimitadas</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">50 GB almacenamiento</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Dominio personalizado</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Análisis y estadísticas</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Soporte prioritario</span>
                            </div>
                        </div>

                        <a href="../index.html" class="block w-full px-6 py-3 bg-blue-600 text-white font-bold rounded-lg text-center hover:bg-blue-700 transition-colors">
                            Contratar Pro
                        </a>
                    </div>
                </div>

                <!-- Plan Elite -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 overflow-hidden hover:border-yellow-400 hover:shadow-2xl transition-all">
                    <div class="bg-gradient-to-br from-yellow-600 to-orange-600 p-6 text-white text-center">
                        <h3 class="text-2xl font-bold mb-2">Elite</h3>
                        <div class="text-4xl font-bold mb-2">$129.99<span class="text-lg font-normal">/mes</span></div>
                        <p class="text-yellow-100 text-sm">Solución completa</p>
                    </div>

                    <div class="p-6">
                        <div class="text-xs text-gray-600 mb-3 italic">Todo de Pro, más:</div>
                        <div class="space-y-3 mb-6">
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700"><strong>Lumen:</strong> Fotos ilimitadas</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">100 GB almacenamiento</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Multi-usuario (equipo)</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">API para integración</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Backup automático</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <i data-lucide="check" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                                <span class="text-sm text-gray-700">Soporte dedicado 24/7</span>
                            </div>
                        </div>

                        <a href="../index.html" class="block w-full px-6 py-3 bg-gradient-to-r from-yellow-600 to-orange-600 text-white font-bold rounded-lg text-center hover:opacity-90 transition-opacity">
                            Contratar Elite
                        </a>
                    </div>
                </div>
            </div>

            <!-- Comparison Note -->
            <div class="mt-12 text-center">
                <p class="text-gray-600 mb-2">¿No estás seguro cuál plan elegir?</p>
                <a href="../index.html#planes" class="text-purple-600 hover:text-purple-700 font-semibold">
                    Ver comparación completa de planes →
                </a>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="py-20 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Ve Lumen en Acción
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Explora un portfolio real creado con Lumen
                </p>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-8 text-center">
                    <div class="mb-6">
                        <i data-lucide="eye" class="w-16 h-16 mx-auto text-purple-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Portfolio de FotosJuan</h3>
                    <p class="text-gray-600 mb-8">
                        Juan Martínez, fotógrafo profesional, usa Lumen para mostrar su trabajo y entregar galerías a sus clientes.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="../fotosjuan/" class="px-8 py-4 gradient-lumen text-white font-bold rounded-lg hover:opacity-90 transition-all shadow-lg hover:shadow-xl">
                            Ver Landing (TarjetaDigital)
                        </a>
                        <a href="../lumen.php?id=fotosjuan" class="px-8 py-4 bg-white text-purple-700 font-bold rounded-lg border-2 border-purple-300 hover:border-purple-500 transition-all">
                            Ver Portfolio (Lumen)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-lumen text-white">
        <div class="container mx-auto px-4 sm:px-6 text-center">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold mb-6">
                    ¿Listo para Iluminar tu Trabajo?
                </h2>
                <p class="text-xl text-purple-100 mb-8">
                    Únete a fotógrafos y creativos que ya confían en Lumen para presentar su trabajo profesionalmente.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="../index.html" class="px-8 py-4 bg-white text-purple-700 font-bold rounded-lg hover:bg-gray-100 transition-all shadow-lg">
                        Comenzar Ahora
                    </a>
                    <a href="login.php" class="px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-bold rounded-lg border-2 border-white/30 hover:bg-white/20 transition-all">
                        Acceder a mi Cuenta
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 gradient-lumen rounded-lg flex items-center justify-center">
                            <i data-lucide="sparkles" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="text-xl font-bold text-white">Lumen</span>
                    </div>
                    <p class="text-sm">Portfolio digital profesional para fotógrafos y creativos.</p>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Producto</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#caracteristicas" class="hover:text-white transition-colors">Características</a></li>
                        <li><a href="#planes" class="hover:text-white transition-colors">Planes</a></li>
                        <li><a href="#demo" class="hover:text-white transition-colors">Demo</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Empresa</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="../index.html" class="hover:text-white transition-colors">OriginalisDoc</a></li>
                        <li><a href="../index.html#soluciones" class="hover:text-white transition-colors">Otras Soluciones</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Soporte</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="login.php" class="hover:text-white transition-colors">Acceder</a></li>
                        <li><a href="mailto:soporte@originalis.com" class="hover:text-white transition-colors">Contacto</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-sm">
                <p>&copy; 2025 Lumen by OriginalisDoc. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    mobileMenu.classList.add('hidden');
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
