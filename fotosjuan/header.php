<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'FotosJuan - Fotografía Profesional'; ?></title>

    <!-- SEO -->
    <meta name="description" content="Portal de galerías privadas - FotosJuan Photography. Accede a tus fotos profesionales, descarga en alta resolución y obtén certificados de autenticidad.">
    <meta name="keywords" content="fotografía profesional, galerías privadas, bodas, eventos, retratos, fotografía corporativa">

    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle fill='%230ea5e9' cx='50' cy='50' r='45'/><path fill='%23fff' stroke='%23fff' stroke-width='4' d='M35 40h30v20h-30z M30 35h5v5h-5z M45 50h10v5h-10z'/></svg>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'fj-blue': {
                            DEFAULT: '#0ea5e9',
                            light: '#38bdf8',
                            dark: '#0284c7'
                        },
                        'fj-gold': {
                            DEFAULT: '#D4AF37',
                            light: '#F0D377',
                            dark: '#B8941E'
                        }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }
        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <!-- Header -->
    <header class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 sticky top-0 left-0 right-0 z-50 shadow-sm transition-colors duration-300">
        <div class="container mx-auto px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                <!-- Ícono de cámara -->
                <svg class="h-8 w-8 sm:h-10 sm:w-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                    <circle fill="#0ea5e9" cx="50" cy="50" r="45"/>
                    <path fill="#fff" stroke="#fff" stroke-width="4" d="M35 40h30v20h-30z M30 35h5v5h-5z M45 50h10v5h-10z"/>
                </svg>
                <div>
                    <a href="index.php" class="text-xl sm:text-2xl font-bold text-transparent bg-clip-text fotosjuan-gradient">
                        FotosJuan
                    </a>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 -mt-1">Photography</p>
                </div>
            </div>

            <!-- Navigation - Desktop -->
            <nav class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    Inicio
                </a>
                <a href="../lumen.php?id=fotosjuan" class="flex items-center gap-1 text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    <i data-lucide="images" class="w-4 h-4"></i>
                    <span>Portfolio</span>
                </a>
                <a href="index.php#servicios" class="text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    Servicios
                </a>
                <a href="dashboard.php" class="flex items-center gap-1 text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>
                <a href="index.php#contacto" class="text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    Contacto
                </a>
            </nav>

            <!-- Dark Mode Toggle -->
            <button id="theme-toggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block text-yellow-400"></i>
                <i data-lucide="moon" class="w-5 h-5 block dark:hidden text-gray-700"></i>
            </button>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden p-2 text-gray-700 dark:text-gray-300">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-colors duration-300">
            <nav class="container mx-auto px-6 py-4 flex flex-col space-y-3">
                <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    Inicio
                </a>
                <a href="../lumen.php?id=fotosjuan" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    <i data-lucide="images" class="w-4 h-4"></i>
                    <span>Portfolio</span>
                </a>
                <a href="index.php#servicios" class="text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    Servicios
                </a>
                <a href="dashboard.php" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>
                <a href="index.php#contacto" class="text-gray-700 dark:text-gray-300 hover:text-fj-blue dark:hover:text-fj-blue font-medium transition-colors">
                    Contacto
                </a>
            </nav>
        </div>
    </header>

    <!-- Inicializar íconos Lucide -->
    <script>
        lucide.createIcons();

        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;

        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';
        html.classList.toggle('dark', currentTheme === 'dark');

        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            const newTheme = html.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', newTheme);
            lucide.createIcons(); // Recreate icons after theme change
        });

        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            lucide.createIcons();
        });
    </script>
