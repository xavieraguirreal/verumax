    <!-- Footer Profesional -->
    <footer class="bg-gray-900 dark:bg-black text-white transition-colors duration-300 mt-20">
        <div class="<?php echo ($page_type ?? 'home') === 'certificatum' ? 'max-w-7xl' : 'container'; ?> mx-auto px-6 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Columna 1: Logo y descripción -->
                <div class="md:col-span-2">
                    <?php
                    // Configuración del logo
                    $logo_estilo = $instance['logo_estilo'] ?? 'rectangular';
                    $logo_mostrar_texto = $instance['logo_mostrar_texto'] ?? 1;
                    $logo_class_footer = 'h-12 w-auto object-contain';
                    if ($logo_estilo === 'circular') {
                        $logo_class_footer = 'h-12 w-12 rounded-full object-cover';
                    } elseif ($logo_estilo === 'rectangular-rounded') {
                        $logo_class_footer = 'h-12 w-auto object-contain rounded-lg';
                    }
                    ?>
                    <div class="flex items-center gap-3 mb-4">
                        <?php if (!empty($instance['logo_url'])): ?>
                            <img src="<?php echo htmlspecialchars($instance['logo_url']); ?>"
                                 alt="Logo <?php echo htmlspecialchars($instance['nombre']); ?>"
                                 class="<?php echo $logo_class_footer; ?>">
                        <?php else: ?>
                            <div class="h-12 w-12 rounded-full flex items-center justify-center text-white font-bold"
                                 style="background-color: <?php echo htmlspecialchars($instance['color_primario'] ?? '#2E7D32'); ?>;">
                                <?php echo strtoupper(substr($instance['nombre'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($logo_mostrar_texto): ?>
                            <span class="text-2xl font-bold"><?php echo htmlspecialchars($instance['nombre']); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php
                    // Intentar obtener misión traducida, si no existe usar la original
                    $id_instancia = $instance['id_instancia'] ?? null;
                    $mision_original = $instance['mision'] ?? $instance['config']['mision'] ?? null;

                    if ($id_instancia && $mision_original) {
                        $mision = \VERUMax\Services\LanguageService::getContent(
                            (int)$id_instancia,
                            'mision',
                            null,
                            $mision_original
                        );
                    } else {
                        $mision = $mision_original;
                    }

                    if (!empty($mision)):
                    ?>
                        <p class="text-gray-400 text-sm leading-relaxed max-w-md">
                            <?php echo htmlspecialchars(substr($mision, 0, 200)); ?>
                            <?php if (strlen($mision) > 200) echo '...'; ?>
                        </p>
                    <?php endif; ?>

                    <?php
                    $email_contacto = $instance['email_contacto'] ?? $instance['config']['email_contacto'] ?? null;
                    $sitio_web = $instance['sitio_web_oficial'] ?? $instance['config']['sitio_web_oficial'] ?? null;

                    if (!empty($email_contacto) || !empty($sitio_web)):
                    ?>
                        <div class="mt-4 space-y-2">
                            <?php if (!empty($email_contacto)): ?>
                                <div class="flex items-center gap-2 text-sm text-gray-400">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($email_contacto); ?>"
                                       class="hover:text-primario transition-colors">
                                        <?php echo htmlspecialchars($email_contacto); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($sitio_web)): ?>
                                <div class="flex items-center gap-2 text-sm text-gray-400">
                                    <i data-lucide="globe" class="w-4 h-4"></i>
                                    <a href="<?php echo htmlspecialchars($sitio_web); ?>"
                                       target="_blank"
                                       class="hover:text-primario transition-colors">
                                        <?php echo \VERUMax\Services\LanguageService::get('footer_official_website', [], 'Sitio web oficial'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Columna 2: Navegación (solo para Identitas) -->
                <?php if (($page_type ?? 'home') === 'identitas' && !empty($paginas)): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Navegación</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="/" class="text-gray-400 hover:text-primario transition-colors text-sm flex items-center gap-2">
                                    <i data-lucide="home" class="w-4 h-4"></i>
                                    Inicio
                                </a>
                            </li>
                            <?php foreach ($paginas as $pag): ?>
                                <?php if ($pag['visible_menu']): ?>
                                    <li>
                                        <a href="?page=<?php echo urlencode($pag['slug']); ?>"
                                           class="text-gray-400 hover:text-primario transition-colors text-sm flex items-center gap-2">
                                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                            <?php echo htmlspecialchars($pag['titulo']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php elseif (($page_type ?? 'home') === 'certificatum'): ?>
                    <!-- Placeholder para Certificatum -->
                    <div></div>
                <?php endif; ?>

                <!-- Columna 3: Redes Sociales -->
                <?php
                $redes = null;
                if (!empty($instance['redes_sociales'])) {
                    $redes = is_string($instance['redes_sociales']) ? json_decode($instance['redes_sociales'], true) : $instance['redes_sociales'];
                }

                if ($redes && array_filter($redes)):
                ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-4"><?php echo \VERUMax\Services\LanguageService::get('footer_follow_us', [], 'Seguinos'); ?></h3>
                        <div class="flex flex-wrap gap-3">
                            <?php if (!empty($redes['instagram'])): ?>
                                <a href="<?php echo htmlspecialchars($redes['instagram']); ?>"
                                   target="_blank"
                                   class="w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 flex items-center justify-center transition-colors"
                                   aria-label="Instagram">
                                    <i data-lucide="instagram" class="w-5 h-5"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($redes['facebook'])): ?>
                                <a href="<?php echo htmlspecialchars($redes['facebook']); ?>"
                                   target="_blank"
                                   class="w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 flex items-center justify-center transition-colors"
                                   aria-label="Facebook">
                                    <i data-lucide="facebook" class="w-5 h-5"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($redes['linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($redes['linkedin']); ?>"
                                   target="_blank"
                                   class="w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 flex items-center justify-center transition-colors"
                                   aria-label="LinkedIn">
                                    <i data-lucide="linkedin" class="w-5 h-5"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($redes['twitter'])): ?>
                                <a href="<?php echo htmlspecialchars($redes['twitter']); ?>"
                                   target="_blank"
                                   class="w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 flex items-center justify-center transition-colors"
                                   aria-label="Twitter / X">
                                    <i data-lucide="twitter" class="w-5 h-5"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($redes['youtube'])): ?>
                                <a href="<?php echo htmlspecialchars($redes['youtube']); ?>"
                                   target="_blank"
                                   class="w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 flex items-center justify-center transition-colors"
                                   aria-label="YouTube">
                                    <i data-lucide="youtube" class="w-5 h-5"></i>
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($redes['whatsapp'])): ?>
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $redes['whatsapp']); ?>"
                                   target="_blank"
                                   class="w-10 h-10 rounded-full bg-gray-800 hover:bg-gray-700 flex items-center justify-center transition-colors"
                                   aria-label="WhatsApp">
                                    <i data-lucide="phone" class="w-5 h-5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Línea divisoria -->
            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm text-center md:text-left">
                    &copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($instance['nombre']); ?>.
                    <?php echo \VERUMax\Services\LanguageService::get('footer_all_rights', [], 'Todos los derechos reservados'); ?>.
                </p>

                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <i data-lucide="shield-check" class="w-3 h-3 text-gray-400"></i>
                    <?php
                    $solucion_nombre = ($page_type ?? 'home') === 'certificatum' ? 'Certificatum' : 'Identitas';
                    $solucion_url = ($page_type ?? 'home') === 'certificatum' ? 'https://verumax.com/certificatum/' : 'https://verumax.com/identitas/';
                    ?>
                    <span>
                        <?php echo \VERUMax\Services\LanguageService::get('footer_powered_by', [], 'Potestate'); ?>
                        <a href="<?php echo $solucion_url; ?>" target="_blank" class="font-semibold text-gray-400 hover:text-primario transition-colors">
                            <?php echo $solucion_nombre; ?></a>,
                        <?php echo \VERUMax\Services\LanguageService::get('footer_solution_verumax', [], 'solutio VERUMax'); ?>
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Botón Scroll to Top -->
    <button id="scroll-to-top"
            class="fixed bottom-6 right-6 bg-gray-900 dark:bg-gray-800 text-white p-3 rounded-full shadow-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-all opacity-0 invisible"
            aria-label="Volver arriba"
            style="transition: opacity 0.3s, visibility 0.3s, transform 0.3s;">
        <i data-lucide="arrow-up" class="w-5 h-5"></i>
    </button>

    <!-- Banner de Cookies -->
    <div id="cookie-banner"
         class="fixed bottom-0 left-0 right-0 bg-gray-800 dark:bg-gray-900 text-white px-4 py-3 shadow-lg z-50 transform translate-y-full transition-transform duration-300"
         style="display: none;">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3 text-sm">
                <span class="text-xl">🍪</span>
                <p>
                    <?php echo \VERUMax\Services\LanguageService::get('cookies_message', [],
                        'Usamos cookies para recordar tus preferencias de idioma y tema.'); ?>
                    <a href="/privacidad" target="_blank" class="underline hover:text-gray-300 ml-1">
                        <?php echo \VERUMax\Services\LanguageService::get('cookies_more_info', [], 'Ver más'); ?>
                    </a>
                </p>
            </div>
            <button id="cookie-accept"
                    class="px-4 py-2 bg-white text-gray-800 rounded-lg font-medium hover:bg-gray-100 transition-colors text-sm whitespace-nowrap">
                <?php echo \VERUMax\Services\LanguageService::get('cookies_accept', [], 'Entendido'); ?>
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // ============================================================================
        // MODO OSCURO - Configurable por institución con persistencia de usuario
        // ============================================================================
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const html = document.documentElement;

        // Tema por defecto de la institución (desde BD)
        const defaultTheme = '<?php echo htmlspecialchars($instance['tema_default'] ?? 'dark'); ?>';

        function updateDarkModeButton() {
            if (!darkModeToggle) return;

            const isDark = html.classList.contains('dark');

            if (isDark) {
                darkModeToggle.innerHTML = '<i data-lucide="sun" class="w-5 h-5 text-yellow-400"></i>';
            } else {
                darkModeToggle.innerHTML = '<i data-lucide="moon" class="w-5 h-5 text-gray-600"></i>';
            }

            lucide.createIcons();
        }

        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', (e) => {
                e.preventDefault();
                html.classList.toggle('dark');
                const isDark = html.classList.contains('dark');
                localStorage.setItem('darkMode', isDark); // Persistir preferencia del usuario
                updateDarkModeButton();
            });

            // Lógica de carga: 1) Preferencia guardada del usuario, 2) Default de institución
            const savedPreference = localStorage.getItem('darkMode');

            if (savedPreference !== null) {
                // El usuario ya cambió el tema alguna vez -> respetar su preferencia
                if (savedPreference === 'true') {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
            } else {
                // Primera visita -> usar tema por defecto de la institución
                if (defaultTheme === 'dark') {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
            }

            updateDarkModeButton();
        }

        // ============================================================================
        // BOTÓN SCROLL TO TOP
        // ============================================================================
        const scrollToTopBtn = document.getElementById('scroll-to-top');

        if (scrollToTopBtn) {
            // Mostrar/ocultar botón según scroll
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.style.opacity = '1';
                    scrollToTopBtn.style.visibility = 'visible';
                } else {
                    scrollToTopBtn.style.opacity = '0';
                    scrollToTopBtn.style.visibility = 'hidden';
                }
            });

            // Click para volver arriba
            scrollToTopBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // ============================================================================
        // BANNER DE COOKIES
        // ============================================================================
        const cookieBanner = document.getElementById('cookie-banner');
        const cookieAccept = document.getElementById('cookie-accept');

        if (cookieBanner && !localStorage.getItem('cookiesAccepted')) {
            // Mostrar banner con animación después de 1 segundo
            setTimeout(() => {
                cookieBanner.style.display = 'block';
                // Forzar reflow para que la animación funcione
                cookieBanner.offsetHeight;
                cookieBanner.classList.remove('translate-y-full');
            }, 1000);
        }

        if (cookieAccept) {
            cookieAccept.addEventListener('click', () => {
                // Ocultar con animación
                cookieBanner.classList.add('translate-y-full');
                // Guardar preferencia
                localStorage.setItem('cookiesAccepted', 'true');
                // Remover del DOM después de la animación
                setTimeout(() => {
                    cookieBanner.style.display = 'none';
                }, 300);
            });
        }
    </script>
</body>
</html>
