    </main>

    <?php
    // Helper de traducción (si no está disponible del header)
    if (!isset($t)) {
        $t = fn($key, $params = [], $default = null) => \VERUMax\Services\LanguageService::get($key, $params, $default);
    }
    ?>

    <!-- Footer Profesional -->
    <footer class="bg-gray-900 dark:bg-black text-white transition-colors duration-300 mt-20">
        <div class="container mx-auto px-6 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <!-- Columna 1: Logo y descripción -->
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <?php if (!empty($instance['logo_url'])): ?>
                            <img src="<?php echo htmlspecialchars($instance['logo_url']); ?>"
                                 alt="Logo <?php echo htmlspecialchars($instance['nombre']); ?>"
                                 class="<?php echo getLogoClasses($instance['logo_estilo'] ?? 'rectangular', 'h-12'); ?>">
                        <?php else: ?>
                            <div class="h-12 w-12 rounded-full flex items-center justify-center text-white font-bold"
                                 style="background-color: <?php echo htmlspecialchars($instance['color_primario'] ?? '#D4AF37'); ?>;">
                                <?php echo strtoupper(substr($instance['nombre'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                        <span class="text-2xl font-bold"><?php echo htmlspecialchars($instance['nombre']); ?></span>
                    </div>
                    <?php if (!empty($instance['config']['mision'])): ?>
                        <p class="text-gray-400 text-sm leading-relaxed max-w-md">
                            <?php echo htmlspecialchars(substr($instance['config']['mision'], 0, 200)); ?>
                            <?php if (strlen($instance['config']['mision']) > 200) echo '...'; ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($instance['config']['email_contacto']) || !empty($instance['config']['sitio_web_oficial'])): ?>
                        <div class="mt-4 space-y-2">
                            <?php if (!empty($instance['config']['email_contacto'])): ?>
                                <div class="flex items-center gap-2 text-sm text-gray-400">
                                    <i data-lucide="mail" class="w-4 h-4"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($instance['config']['email_contacto']); ?>"
                                       class="hover:text-white transition-colors">
                                        <?php echo htmlspecialchars($instance['config']['email_contacto']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($instance['config']['sitio_web_oficial'])): ?>
                                <div class="flex items-center gap-2 text-sm text-gray-400">
                                    <i data-lucide="globe" class="w-4 h-4"></i>
                                    <a href="<?php echo htmlspecialchars($instance['config']['sitio_web_oficial']); ?>"
                                       target="_blank"
                                       class="hover:text-white transition-colors">
                                        <?php echo $t('footer_official_website', [], 'Sitio web oficial'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Columna 2: Navegación -->
                <?php if (!empty($paginas)): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-4"><?php echo $t('footer_navigation', [], 'Navegación'); ?></h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="/" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                                    <i data-lucide="home" class="w-4 h-4"></i>
                                    <?php echo $t('nav_home', [], 'Inicio'); ?>
                                </a>
                            </li>
                            <?php foreach ($paginas as $pag): ?>
                                <?php if ($pag['visible_menu']): ?>
                                    <li>
                                        <a href="?page=<?php echo urlencode($pag['slug']); ?>"
                                           class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                            <?php echo $t('identitas.menu_' . $pag['slug'], [], $pag['titulo']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Columna 3: Módulos -->
                <?php if (!empty($modulos_activos) && array_filter($modulos_activos)): ?>
                    <div>
                        <h3 class="text-lg font-semibold mb-4"><?php echo $t('identitas.footer_services', [], 'Servicios'); ?></h3>
                        <ul class="space-y-2">
                            <?php if ($modulos_activos['certificatum']): ?>
                                <li>
                                    <a href="/certificatum/?lang=<?php echo urlencode(\VERUMax\Services\LanguageService::getCurrentLang()); ?>"
                                       class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                                        <i data-lucide="award" class="w-4 h-4"></i>
                                        <?php echo $t('identitas.footer_certificates_portal', [], 'Portal de Certificados'); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php if ($modulos_activos['scripta']): ?>
                                <li>
                                    <a href="/scripta/"
                                       class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                        Scripta
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Línea divisoria -->
            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm text-center md:text-left">
                    &copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars($instance['nombre']); ?>.
                    <?php echo $t('footer_all_rights', [], 'Todos los derechos reservados'); ?>.
                </p>

                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span><?php echo $t('footer_powered_by', [], 'Potenciado por'); ?></span>
                    <a href="https://verumax.com" target="_blank" class="font-semibold text-gray-400 hover:text-white transition-colors">
                        VERUMax
                    </a>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="shield-check" class="w-3 h-3"></i>
                        Identitas
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Botón Scroll to Top -->
    <button id="scroll-to-top"
            class="fixed bottom-6 right-6 bg-gray-900 dark:bg-gray-800 text-white p-3 rounded-full shadow-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-all opacity-0 invisible"
            aria-label="<?php echo $t('footer_scroll_top', [], 'Volver arriba'); ?>"
            style="transition: opacity 0.3s, visibility 0.3s, transform 0.3s;">
        <i data-lucide="arrow-up" class="w-5 h-5"></i>
    </button>

    <script>
        lucide.createIcons();

        // Toggle de modo oscuro
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const html = document.documentElement;

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
                localStorage.setItem('darkMode', isDark);
                updateDarkModeButton();
            });

            // Cargar modo oscuro: 1) localStorage, 2) config del admin, 3) mantener HTML del servidor
            const storedPreference = localStorage.getItem('darkMode');
            const adminDefault = html.getAttribute('data-tema-default'); // 'dark' o 'light'

            if (storedPreference !== null) {
                // Usuario ya eligió manualmente - respetar su elección
                if (storedPreference === 'true') {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
            }
            // Si no hay preferencia guardada, el HTML ya viene con la clase correcta desde el servidor

            updateDarkModeButton();
        }

        // Botón Scroll to Top
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
    </script>
</body>
</html>
