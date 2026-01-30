<?php
/**
 * OriginalisDoc - Footer Compartido
 * Version: 1.0.0
 */
?>

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
                    <span class="text-xl font-bold text-gold"><?php echo APP_NAME; ?></span>
                </div>
                <p class="text-sm leading-relaxed text-gray-400">
                    <?php echo $lang['footer_descripcion'] ?? 'Plataforma profesional de validación de certificados digitales con códigos QR infalsificables.'; ?>
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-gold font-bold mb-4"><?php echo $lang['footer_enlaces'] ?? 'Enlaces Rápidos'; ?></h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/index.php?lang=<?php echo $current_language; ?>" class="hover:text-gold transition-colors"><?php echo $lang['footer_inicio'] ?? 'Inicio'; ?></a></li>
                    <li><a href="/contactus.php" class="hover:text-gold transition-colors"><?php echo $lang['footer_contacto'] ?? 'Contacto'; ?></a></li>
                </ul>
            </div>

            <!-- Legal Links -->
            <div>
                <h4 class="text-gold font-bold mb-4"><?php echo $lang['footer_legal'] ?? 'Legal'; ?></h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/stipulationes.php?lang=<?php echo $current_language; ?>" target="_blank" class="hover:text-gold transition-colors"><?php echo $lang['footer_terminos'] ?? 'Términos y Condiciones'; ?></a></li>
                    <li><a href="/secretum.php?lang=<?php echo $current_language; ?>" target="_blank" class="hover:text-gold transition-colors"><?php echo $lang['footer_privacidad'] ?? 'Política de Privacidad'; ?></a></li>
                    <li><a href="mailto:contacto@verumax.com" class="hover:text-gold transition-colors">contacto@verumax.com</a></li>
                </ul>
            </div>
        </div>

        <!-- Security & Trust Badges -->
        <div class="mb-12 pt-8 border-t border-gray-800">
            <div class="text-center mb-6">
                <p class="text-sm text-gray-500 mb-4"><?php echo $lang['footer_seguridad'] ?? 'Tecnología y Seguridad de Nivel Empresarial'; ?></p>
            </div>
            <div class="flex flex-wrap justify-center items-center gap-4 md:gap-6">
                <!-- SSL Secure -->
                <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div class="text-left">
                        <div class="text-xs font-bold text-gold"><?php echo $lang['badge_ssl'] ?? 'SSL'; ?></div>
                        <div class="text-xs text-gray-400"><?php echo $lang['badge_ssl_desc'] ?? 'Encriptación 256-bit'; ?></div>
                    </div>
                </div>

                <!-- HTTPS -->
                <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <div class="text-left">
                        <div class="text-xs font-bold text-gold"><?php echo $lang['badge_https'] ?? 'HTTPS'; ?></div>
                        <div class="text-xs text-gray-400"><?php echo $lang['badge_https_desc'] ?? 'Conexión Segura'; ?></div>
                    </div>
                </div>

                <!-- Privacy -->
                <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <div class="text-left">
                        <div class="text-xs font-bold text-gold"><?php echo $lang['badge_privacidad'] ?? 'Privacidad'; ?></div>
                        <div class="text-xs text-gray-400"><?php echo $lang['badge_privacidad_desc'] ?? 'Datos Protegidos'; ?></div>
                    </div>
                </div>

                <!-- Backup -->
                <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    <div class="text-left">
                        <div class="text-xs font-bold text-gold"><?php echo $lang['badge_backup'] ?? 'Backup'; ?></div>
                        <div class="text-xs text-gray-400"><?php echo $lang['badge_backup_desc'] ?? 'Automático Diario'; ?></div>
                    </div>
                </div>

                <!-- Uptime -->
                <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <div class="text-left">
                        <div class="text-xs font-bold text-gold"><?php echo $lang['badge_uptime'] ?? 'Uptime 99.9%'; ?></div>
                        <div class="text-xs text-gray-400"><?php echo $lang['badge_uptime_desc'] ?? 'Disponibilidad'; ?></div>
                    </div>
                </div>

                <!-- Support -->
                <div class="flex items-center gap-2 bg-gray-900 border border-gold/20 px-4 py-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-metallic-green-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <div class="text-left">
                        <div class="text-xs font-bold text-gold"><?php echo $lang['badge_soporte'] ?? 'Soporte'; ?></div>
                        <div class="text-xs text-gray-400"><?php echo $lang['badge_soporte_desc'] ?? 'Técnico 24/7'; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center text-sm">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <p class="text-gray-500">&copy; <?php echo APP_YEAR; ?> <?php echo APP_NAME; ?>. <?php echo $lang['footer_copyright'] ?? 'Todos los derechos reservados.'; ?></p>
                <p class="mt-1 text-xs text-gray-600 opacity-60">v<?php echo APP_VERSION; ?></p>
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
        <?php echo $lang['veritas_chat_btn'] ?? 'Chat con Veritas IA'; ?>
    </span>
</button>

<!-- Botón Scroll to Top -->
<button id="scrollToTop" class="fixed bottom-8 right-8 w-12 h-12 bg-gold hover:bg-gold-light text-black rounded-full shadow-lg hover:shadow-xl transition-all duration-300 opacity-0 pointer-events-none z-50 flex items-center justify-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
</button>

<!-- Banner de Cookies -->
<div id="cookie-banner"
     class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white px-4 py-3 shadow-lg z-50 transform translate-y-full transition-transform duration-300"
     style="display: none;">
    <div class="container mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center gap-3 text-sm">
            <span class="text-xl">🍪</span>
            <p>
                <?php echo $lang['cookies_message'] ?? 'Usamos cookies para recordar sus preferencias de idioma y tema.'; ?>
                <a href="https://verumax.com/secretum.php" target="_blank" class="underline hover:text-yellow-400 ml-1">
                    <?php echo $lang['cookies_more_info'] ?? 'Ver más'; ?>
                </a>
            </p>
        </div>
        <button id="cookie-accept"
                class="px-4 py-2 bg-amber-500 text-black rounded-lg font-medium hover:bg-amber-400 transition-colors text-sm whitespace-nowrap">
            <?php echo $lang['cookies_accept'] ?? 'Entendido'; ?>
        </button>
    </div>
</div>

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
            <?php echo $lang['veritas_titulo'] ?? 'Veritas IA'; ?>
        </h2>
        <p class="text-gray-300 text-center mb-2">
            <?php echo $lang['veritas_subtitulo'] ?? 'Nuestro Agente de Inteligencia Artificial Especializado'; ?>
        </p>
        <p class="text-gold text-center text-lg font-semibold mb-6">
            <?php echo $lang['veritas_proximamente'] ?? '¡Próximamente!'; ?>
        </p>

        <div class="bg-gray-800/50 border border-purple-500/20 rounded-xl p-4 mb-6">
            <p class="text-sm text-gray-400 text-center leading-relaxed">
                <?php echo $lang['veritas_descripcion'] ?? 'Veritas estará disponible muy pronto para ayudarte con consultas sobre certificados, validaciones y más.'; ?>
            </p>
        </div>

        <button id="closeVeritasBtn" class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-lg transition-all duration-200">
            <?php echo $lang['veritas_entendido'] ?? 'Entendido'; ?>
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

// Preservar posición de scroll al cambiar de idioma
(function() {
    // Restaurar posición de scroll guardada
    const savedScrollPos = sessionStorage.getItem('verumax_scroll_pos');
    if (savedScrollPos) {
        sessionStorage.removeItem('verumax_scroll_pos');
        // Pequeño delay para asegurar que la página esté completamente cargada
        setTimeout(function() {
            window.scrollTo(0, parseInt(savedScrollPos));
        }, 100);
    }

    // Interceptar clics en enlaces de cambio de idioma
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[href*="?lang="], a[href*="&lang="]');
        if (link) {
            // Guardar posición actual de scroll
            sessionStorage.setItem('verumax_scroll_pos', window.scrollY.toString());
        }
    });
})();

// Banner de Cookies
const cookieBanner = document.getElementById('cookie-banner');
const cookieAccept = document.getElementById('cookie-accept');

if (cookieBanner && !localStorage.getItem('cookiesAccepted')) {
    setTimeout(() => {
        cookieBanner.style.display = 'block';
        cookieBanner.offsetHeight;
        cookieBanner.classList.remove('translate-y-full');
    }, 1000);
}

if (cookieAccept) {
    cookieAccept.addEventListener('click', () => {
        cookieBanner.classList.add('translate-y-full');
        localStorage.setItem('cookiesAccepted', 'true');
        setTimeout(() => {
            cookieBanner.style.display = 'none';
        }, 300);
    });
}
</script>
