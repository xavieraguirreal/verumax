<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OriginalisDoc - Ecosistema de Identidad y Credenciales Digitales</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; }
        .gold-gradient { background: linear-gradient(135deg, #D4AF37 0%, #F0D377 100%); }
        .hero-bg { background-image: linear-gradient(135deg, rgba(10, 10, 10, 0.98) 0%, rgba(26, 26, 26, 0.95) 100%), repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(212, 175, 55, 0.03) 10px, rgba(212, 175, 55, 0.03) 20px); }
        .premium-card { background-color: rgba(255, 255, 255, 0.03); border: 1px solid rgba(212, 175, 55, 0.1); backdrop-filter: blur(10px); }
        .text-gold { color: #D4AF37; }
    </style>
</head>
<body class="bg-black text-white">

    <!-- Header -->
    <header class="bg-black/80 backdrop-blur-sm sticky top-0 z-50 border-b border-gold/20">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="#" class="text-xl font-bold gold-gradient bg-clip-text text-transparent">OriginalisDoc</a>
            <div class="hidden md:flex items-center space-x-8">
                <a href="#sectores" class="text-gray-300 hover:text-gold transition-colors">Por Sector</a>
                <a href="#herramientas" class="text-gray-300 hover:text-gold transition-colors">Por Herramienta</a>
                <a href="#validar" class="text-gray-300 hover:text-gold transition-colors">Validar</a>
            </div>
            <a href="#contacto" class="hidden md:inline-block px-5 py-2 gold-gradient text-black font-semibold rounded-lg hover:opacity-90 transition-opacity">Contacto</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-bg py-24 md:py-32">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-6xl font-extrabold text-white leading-tight">
                Crea, Gestiona y Valida <br> tu <span class="gold-gradient bg-clip-text text-transparent">Prestigio Digital</span>
            </h1>
            <p class="mt-6 text-lg text-gray-400 max-w-3xl mx-auto">
                Un ecosistema de herramientas para emitir <strong class="text-gold">credenciales infalsificables</strong>, construir tu <strong class="text-gold">marca personal verificada</strong> e implementar <strong class="text-gold">asistentes de IA</strong> especializados.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#sectores" class="px-8 py-4 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/30 text-lg">
                    Explorar Soluciones
                </a>
            </div>
        </div>
    </section>
    
    <!-- Fichas por Sector -->
    <section id="sectores" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gold">Soluciones Especializadas por Sector</h2>
                <p class="mt-4 text-gray-400 max-w-3xl mx-auto">Encuentra la solución diseñada específicamente para las necesidades de tu campo profesional o institucional.</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <!-- Académico -->
                <div class="premium-card p-6 rounded-2xl border-t-4 border-gold h-full flex flex-col">
                    <div class="flex items-center gap-4"><div class="bg-gold/10 p-3 rounded-lg"><i data-lucide="graduation-cap" class="w-8 h-8 text-gold"></i></div><h3 class="text-2xl font-bold text-white">Académico</h3></div>
                    <p class="text-gray-400 mt-4 flex-grow">Instituciones Educativas, Centros de Formación y Formadores Particulares.</p>
                    <a href="academico.php" class="block w-full text-center mt-6 px-4 py-2 font-semibold text-black gold-gradient rounded-lg hover:opacity-90">Ver Solución →</a>
                </div>
                <!-- Perfiles Pro -->
                <div class="premium-card p-6 rounded-2xl border-t-4 border-gold h-full flex flex-col">
                    <div class="flex items-center gap-4"><div class="bg-gold/10 p-3 rounded-lg"><i data-lucide="contact-2" class="w-8 h-8 text-gold"></i></div><h3 class="text-2xl font-bold text-white">Perfiles Pro</h3></div>
                    <p class="text-gray-400 mt-4 flex-grow">Profesionales, Freelancers y Estudiantes que buscan construir su marca personal.</p>
                    <a href="perfiles.php" class="block w-full text-center mt-6 px-4 py-2 font-semibold text-black gold-gradient rounded-lg hover:opacity-90">Ver Solución →</a>
                </div>
                <!-- Artistas -->
                <div class="premium-card p-6 rounded-2xl border-t-4 border-gold h-full flex flex-col">
                    <div class="flex items-center gap-4"><div class="bg-gold/10 p-3 rounded-lg"><i data-lucide="palette" class="w-8 h-8 text-gold"></i></div><h3 class="text-2xl font-bold text-white">Artistas</h3></div>
                    <p class="text-gray-400 mt-4 flex-grow">Fotógrafos, pintores, artesanos y creadores que necesitan proteger y presentar su obra.</p>
                    <a href="artistas.php" class="block w-full text-center mt-6 px-4 py-2 font-semibold text-black gold-gradient rounded-lg hover:opacity-90">Ver Solución →</a>
                </div>
                <!-- ... (Otras fichas como Eventos, Empresarial, etc. pueden ir aquí con un botón de "Próximamente") ... -->
            </div>
        </div>
    </section>

    <!-- Herramientas Individuales -->
    <section id="herramientas" class="py-20 bg-black border-y border-gold/20">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gold">Un Ecosistema de Herramientas a tu Disposición</h2>
                <p class="mt-4 text-gray-400 max-w-3xl mx-auto">Descubre nuestros productos individuales. Cada uno construido sobre nuestra tecnología de validación inalterable.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <div class="premium-card p-6 rounded-xl text-center hover:border-gold/30 transition-all"><i data-lucide="award" class="w-10 h-10 text-gold mx-auto mb-3"></i><h4 class="font-bold text-white">Certificados y Diplomas</h4></div>
                <div class="premium-card p-6 rounded-xl text-center hover:border-gold/30 transition-all"><i data-lucide="contact-2" class="w-10 h-10 text-gold mx-auto mb-3"></i><h4 class="font-bold text-white">Perfiles & Landing Pages</h4></div>
                <div class="premium-card p-6 rounded-xl text-center hover:border-gold/30 transition-all"><i data-lucide="qr-code" class="w-10 h-10 text-gold mx-auto mb-3"></i><h4 class="font-bold text-white">Tarjetas de Contacto QR</h4></div>
                <div class="premium-card p-6 rounded-xl text-center hover:border-gold/30 transition-all"><i data-lucide="layout-grid" class="w-10 h-10 text-gold mx-auto mb-3"></i><h4 class="font-bold text-white">Portfolios de Proyectos</h4></div>
                <div class="premium-card p-6 rounded-xl text-center hover:border-gold/30 transition-all"><i data-lucide="file-check-2" class="w-10 h-10 text-gold mx-auto mb-3"></i><h4 class="font-bold text-white">CV Inteligente</h4></div>
                <div class="premium-card p-6 rounded-xl text-center hover:border-gold/30 transition-all"><i data-lucide="shield-check" class="w-10 h-10 text-gold mx-auto mb-3"></i><h4 class="font-bold text-white">Certificados de Autenticidad</h4></div>
                <div class="premium-card p-6 rounded-xl text-center hover:border-gold/30 transition-all"><i data-lucide="image" class="w-10 h-10 text-gold mx-auto mb-3"></i><h4 class="font-bold text-white">Galerías de Cliente</h4></div>
                <div class="premium-card p-6 rounded-xl text-center hover:border-gold/30 transition-all"><i data-lucide="bot" class="w-10 h-10 text-gold mx-auto mb-3"></i><h4 class="font-bold text-white">Agentes de IA</h4></div>
            </div>
        </div>
    </section>

    <!-- Pilares de la Plataforma -->
    <section id="pilares" class="py-20 bg-gray-950">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gold">Los 3 Pilares de Nuestra Plataforma</h2>
                <p class="mt-4 text-gray-400 max-w-3xl mx-auto">Nuestras soluciones se construyen sobre tres áreas tecnológicas fundamentales que garantizan seguridad, profesionalismo e inteligencia.</p>
            </div>
            <div class="grid lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
                <!-- Pilar 1: Credenciales -->
                <div class="premium-card p-8 rounded-2xl">
                    <div class="flex items-center gap-4"><div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center"><i data-lucide="shield-check" class="w-6 h-6 text-black"></i></div><h3 class="text-2xl font-bold text-white">Credenciales Inalterables</h3></div>
                    <p class="mt-4 text-gray-400">El motor para emitir documentos digitales con validación QR, garantizando su autenticidad de por vida.</p>
                </div>
                <!-- Pilar 2: Perfiles -->
                <div class="premium-card p-8 rounded-2xl">
                    <div class="flex items-center gap-4"><div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center"><i data-lucide="contact-2" class="w-6 h-6 text-black"></i></div><h3 class="text-2xl font-bold text-white">Identidad Digital Profesional</h3></div>
                    <p class="mt-4 text-gray-400">Las herramientas para construir y presentar tu marca personal, desde landing pages hasta portfolios y CVs.</p>
                </div>
                <!-- Pilar 3: Inteligencia -->
                <div class="premium-card p-8 rounded-2xl">
                    <div class="flex items-center gap-4"><div class="w-12 h-12 gold-gradient rounded-lg flex items-center justify-center"><i data-lucide="brain-circuit" class="w-6 h-6 text-black"></i></div><h3 class="text-2xl font-bold text-white">Interacción Inteligente</h3></div>
                    <p class="mt-4 text-gray-400">La tecnología para crear Agentes de IA que actúan como expertos en tu conocimiento específico, disponibles 24/7.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Sección de Validación Manual -->
    <section id="validar" class="py-20 bg-black border-y border-gold/20">
         <!-- ... (código sin cambios) ... -->
    </section>

    <!-- Footer -->
    <footer id="contacto" class="bg-gray-950">
        <!-- ... (código sin cambios) ... -->
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

