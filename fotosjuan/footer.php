    <!-- Footer -->
    <footer class="bg-gray-900 dark:bg-black text-gray-300 py-12 border-t border-gray-800 transition-colors duration-300">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Logo y Descripción -->
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                            <circle fill="#0ea5e9" cx="50" cy="50" r="45"/>
                            <path fill="#fff" stroke="#fff" stroke-width="4" d="M35 40h30v20h-30z M30 35h5v5h-5z M45 50h10v5h-10z"/>
                        </svg>
                        <div>
                            <h3 class="text-xl font-bold text-white">FotosJuan</h3>
                            <p class="text-xs text-gray-400">Photography</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Fotografía profesional con más de 10 años de experiencia capturando momentos únicos e irrepetibles.
                    </p>
                </div>

                <!-- Enlaces Rápidos -->
                <div>
                    <h4 class="text-white font-bold mb-4">Navegación</h4>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="index.php" class="text-gray-400 hover:text-fj-blue transition-colors flex items-center gap-2">
                                <i data-lucide="home" class="w-4 h-4"></i>
                                <span>Inicio</span>
                            </a>
                        </li>
                        <li>
                            <a href="index.php#servicios" class="text-gray-400 hover:text-fj-blue transition-colors flex items-center gap-2">
                                <i data-lucide="camera" class="w-4 h-4"></i>
                                <span>Servicios</span>
                            </a>
                        </li>
                        <li>
                            <a href="index.php#portfolio" class="text-gray-400 hover:text-fj-blue transition-colors flex items-center gap-2">
                                <i data-lucide="image" class="w-4 h-4"></i>
                                <span>Portfolio</span>
                            </a>
                        </li>
                        <li>
                            <a href="index.php#contacto" class="text-gray-400 hover:text-fj-blue transition-colors flex items-center gap-2">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                                <span>Contacto</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contacto y Redes -->
                <div>
                    <h4 class="text-white font-bold mb-4">Contacto</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-fj-blue mt-1 flex-shrink-0"></i>
                            <a href="mailto:info@fotosjuan.com" class="text-gray-400 hover:text-fj-blue transition-colors">
                                info@fotosjuan.com
                            </a>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="phone" class="w-4 h-4 text-fj-blue mt-1 flex-shrink-0"></i>
                            <a href="tel:+541155551234" class="text-gray-400 hover:text-fj-blue transition-colors">
                                +54 11 5555-1234
                            </a>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="instagram" class="w-4 h-4 text-fj-blue mt-1 flex-shrink-0"></i>
                            <a href="https://instagram.com/fotosjuan" target="_blank" class="text-gray-400 hover:text-fj-blue transition-colors">
                                @fotosjuan
                            </a>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-fj-blue mt-1 flex-shrink-0"></i>
                            <span class="text-gray-400">
                                Palermo, Buenos Aires, Argentina
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Separador -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-sm text-gray-500 text-center md:text-left">
                        &copy; <?php echo date('Y'); ?> FotosJuan Photography. Todos los derechos reservados.
                    </p>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span>Powered by</span>
                        <a href="../index.php" class="text-fj-gold hover:text-fj-gold-light transition-colors font-semibold">
                            OriginalisDoc
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Script para inicializar íconos en el footer -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
