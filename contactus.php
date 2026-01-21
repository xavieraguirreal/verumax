<?php
/**
 * OriginalisDoc - Formulario de Contacto
 */
require_once 'config.php';
require_once 'lang_config.php';
?>
<!DOCTYPE html>
<html lang="<?php echo substr($current_language, 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - <?php echo APP_NAME; ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Contáctanos para solicitar una demo o consultar sobre nuestros servicios de certificación digital">
    <meta name="keywords" content="contacto, demo, consultas, OriginalisDoc">

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
    </style>
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>styles.css">
</head>
<body class="bg-black text-gray-100">

    <!-- Header -->
    <header class="bg-black/95 backdrop-blur-md border-b border-gold/20 sticky top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle fill="#D4AF37" cx="50" cy="50" r="45"/><path fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" d="M30 50 L42 62 L70 38"/></svg>
                <a href="index.php" class="text-2xl font-bold text-gold">OriginalisDoc</a>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-300 hover:text-gold font-medium transition-colors">Inicio</a>
                <a href="index.php#categorias" class="text-gray-300 hover:text-gold font-medium transition-colors">Soluciones</a>
                <a href="index.php#productos" class="text-gray-300 hover:text-gold font-medium transition-colors">Productos</a>
                <a href="contactus.php" class="px-5 py-2 text-black font-semibold gold-gradient rounded-lg">Contacto</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="py-20 bg-gradient-to-b from-black via-gray-900 to-black relative overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-gold rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-gold rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-gold/20 border border-gold/30 text-gold rounded-full text-sm font-semibold mb-6">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                    <span>Estamos aquí para ayudarte</span>
                </div>

                <h1 class="text-4xl md:text-6xl font-extrabold mb-6">
                    <span class="text-transparent bg-clip-text gold-gradient">Contáctanos</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-300">
                    ¿Tienes preguntas? ¿Quieres solicitar una demo? Completa el formulario y te responderemos a la brevedad.
                </p>
            </div>

            <!-- Contact Form -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gold/30 rounded-2xl p-8">
                    <form id="contactForm" class="space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-300 mb-2">
                                <i data-lucide="user" class="w-4 h-4 inline-block mr-1"></i>
                                Nombre completo *
                            </label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 transition-all"
                                placeholder="Juan Pérez"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                                <i data-lucide="mail" class="w-4 h-4 inline-block mr-1"></i>
                                Email *
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 transition-all"
                                placeholder="tu@email.com"
                            />
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-300 mb-2">
                                <i data-lucide="phone" class="w-4 h-4 inline-block mr-1"></i>
                                Teléfono (opcional)
                            </label>
                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 transition-all"
                                placeholder="+54 11 1234-5678"
                            />
                        </div>

                        <!-- Empresa/Institución -->
                        <div>
                            <label for="empresa" class="block text-sm font-medium text-gray-300 mb-2">
                                <i data-lucide="building" class="w-4 h-4 inline-block mr-1"></i>
                                Empresa/Institución (opcional)
                            </label>
                            <input
                                type="text"
                                id="empresa"
                                name="empresa"
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 transition-all"
                                placeholder="Tu empresa o institución"
                            />
                        </div>

                        <!-- Tipo de Consulta -->
                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-300 mb-2">
                                <i data-lucide="tag" class="w-4 h-4 inline-block mr-1"></i>
                                Tipo de consulta *
                            </label>
                            <select
                                id="tipo"
                                name="tipo"
                                required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 transition-all"
                            >
                                <option value="">Selecciona una opción</option>
                                <option value="demo">Solicitar Demo</option>
                                <option value="informacion">Información de Servicios</option>
                                <option value="cotizacion">Solicitar Cotización</option>
                                <option value="soporte">Soporte Técnico</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>

                        <!-- Servicio de Interés -->
                        <div id="servicioField" class="hidden">
                            <label for="servicio" class="block text-sm font-medium text-gray-300 mb-2">
                                <i data-lucide="package" class="w-4 h-4 inline-block mr-1"></i>
                                Servicio de interés
                            </label>
                            <input
                                type="text"
                                id="servicio"
                                name="servicio"
                                readonly
                                class="w-full px-4 py-3 bg-gray-700 border border-gray-600 text-gray-300 rounded-lg"
                            />
                        </div>

                        <!-- Mensaje -->
                        <div>
                            <label for="mensaje" class="block text-sm font-medium text-gray-300 mb-2">
                                <i data-lucide="message-square" class="w-4 h-4 inline-block mr-1"></i>
                                Mensaje *
                            </label>
                            <textarea
                                id="mensaje"
                                name="mensaje"
                                rows="5"
                                required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 transition-all resize-none"
                                placeholder="Cuéntanos cómo podemos ayudarte..."
                            ></textarea>
                        </div>

                        <!-- Error/Success Messages -->
                        <div id="formError" class="hidden bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                            <p class="text-red-400 text-sm flex items-center gap-2">
                                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                <span id="formErrorText">Ocurrió un error. Por favor, intenta nuevamente.</span>
                            </p>
                        </div>

                        <div id="formSuccess" class="hidden bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                            <p class="text-green-400 text-sm flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                ¡Mensaje enviado con éxito! Te contactaremos pronto.
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            id="submitBtn"
                            class="w-full px-8 py-4 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
                        >
                            <i data-lucide="send" class="w-5 h-5"></i>
                            <span id="submitBtnText">Enviar Mensaje</span>
                        </button>

                        <p class="text-sm text-gray-400 text-center">
                            * Campos obligatorios
                        </p>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="mt-12 grid md:grid-cols-3 gap-6">
                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 text-center">
                        <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="mail" class="w-6 h-6 text-black"></i>
                        </div>
                        <h3 class="text-gold font-semibold mb-2">Email</h3>
                        <a href="mailto:contacto@validarcert.com" class="text-gray-300 hover:text-gold transition-colors text-sm">
                            contacto@validarcert.com
                        </a>
                    </div>

                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 text-center">
                        <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="clock" class="w-6 h-6 text-black"></i>
                        </div>
                        <h3 class="text-gold font-semibold mb-2">Horario</h3>
                        <p class="text-gray-300 text-sm">Lun - Vie: 9:00 - 18:00</p>
                    </div>

                    <div class="bg-gray-900/50 border border-gold/20 rounded-xl p-6 text-center">
                        <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="map-pin" class="w-6 h-6 text-black"></i>
                        </div>
                        <h3 class="text-gold font-semibold mb-2">Ubicación</h3>
                        <p class="text-gray-300 text-sm">Buenos Aires, Argentina</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Inicializar iconos Lucide
        lucide.createIcons();

        const contactForm = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const formError = document.getElementById('formError');
        const formErrorText = document.getElementById('formErrorText');
        const formSuccess = document.getElementById('formSuccess');
        const servicioField = document.getElementById('servicioField');
        const servicioInput = document.getElementById('servicio');

        // Detectar si viene desde el modal con parámetro de servicio
        const urlParams = new URLSearchParams(window.location.search);
        const servicioParam = urlParams.get('servicio');
        if (servicioParam) {
            servicioInput.value = decodeURIComponent(servicioParam);
            servicioField.classList.remove('hidden');
            document.getElementById('tipo').value = 'informacion';
        }

        // Validación de email
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Manejo del formulario
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Ocultar mensajes previos
            formError.classList.add('hidden');
            formSuccess.classList.add('hidden');

            // Obtener datos del formulario
            const formData = {
                nombre: document.getElementById('nombre').value.trim(),
                email: document.getElementById('email').value.trim(),
                telefono: document.getElementById('telefono').value.trim(),
                empresa: document.getElementById('empresa').value.trim(),
                tipo: document.getElementById('tipo').value,
                servicio: servicioInput.value,
                mensaje: document.getElementById('mensaje').value.trim(),
                timestamp: new Date().toISOString(),
                page: document.referrer || 'directo'
            };

            // Validaciones
            if (!formData.nombre || !formData.email || !formData.tipo || !formData.mensaje) {
                formErrorText.textContent = 'Por favor, completa todos los campos obligatorios';
                formError.classList.remove('hidden');
                return;
            }

            if (!isValidEmail(formData.email)) {
                formErrorText.textContent = 'Por favor, ingresa un email válido';
                formError.classList.remove('hidden');
                return;
            }

            // Deshabilitar botón
            submitBtn.disabled = true;
            submitBtnText.textContent = 'Enviando...';

            // TODO: Implementar envío a backend
            // fetch('/api/contactus.php', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //     },
            //     body: JSON.stringify(formData)
            // })
            // .then(response => response.json())
            // .then(data => {
            //     if (data.success) {
            //         formSuccess.classList.remove('hidden');
            //         contactForm.reset();
            //         servicioField.classList.add('hidden');
            //     } else {
            //         formErrorText.textContent = data.message || 'Ocurrió un error. Intenta nuevamente.';
            //         formError.classList.remove('hidden');
            //     }
            // })
            // .catch(error => {
            //     formErrorText.textContent = 'Error de conexión. Intenta nuevamente.';
            //     formError.classList.remove('hidden');
            // })
            // .finally(() => {
            //     submitBtn.disabled = false;
            //     submitBtnText.textContent = 'Enviar Mensaje';
            // });

            // Por ahora, guardar en localStorage
            console.log('Formulario de contacto:', formData);

            const contactos = JSON.parse(localStorage.getItem('pendingContacts') || '[]');
            contactos.push(formData);
            localStorage.setItem('pendingContacts', JSON.stringify(contactos));

            // Simular envío exitoso
            setTimeout(function() {
                formSuccess.classList.remove('hidden');
                contactForm.reset();
                servicioField.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtnText.textContent = 'Enviar Mensaje';

                // Scroll al mensaje de éxito
                formSuccess.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 1500);
        });

        // Limpiar errores al escribir
        contactForm.addEventListener('input', function() {
            if (!formError.classList.contains('hidden')) {
                formError.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
