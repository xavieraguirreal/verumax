<?php
/**
 * OriginalisDoc - Scripts JavaScript Compartidos
 * Version: 1.0.0
 *
 * Scripts comunes para:
 * - Toggle de idioma
 * - Toggle de tema
 * - Scroll suave
 * - Botón scroll to top (si está presente)
 */
?>

<script>
    // Toggle del menú de idiomas
    document.getElementById('langToggle').addEventListener('click', function() {
        document.getElementById('langMenu').classList.toggle('hidden');
    });

    document.addEventListener('click', function(event) {
        const langToggle = document.getElementById('langToggle');
        const langMenu = document.getElementById('langMenu');
        if (!langToggle.contains(event.target) && !langMenu.contains(event.target)) {
            langMenu.classList.add('hidden');
        }
    });

    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;

    // Check for saved theme preference or default to dark mode
    const currentTheme = localStorage.getItem('theme') || 'dark';
    if (currentTheme === 'light') {
        body.classList.add('light-mode');
    }

    themeToggle.addEventListener('click', function() {
        body.classList.toggle('light-mode');

        // Update tooltip
        if (body.classList.contains('light-mode')) {
            themeToggle.setAttribute('title', '<?php echo $lang['nav_tema_oscuro']; ?>');
        } else {
            themeToggle.setAttribute('title', '<?php echo $lang['nav_tema_claro']; ?>');
        }

        // Save preference
        const theme = body.classList.contains('light-mode') ? 'light' : 'dark';
        localStorage.setItem('theme', theme);
    });

    // Smooth scroll para anclas
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Scroll to Top Button (si existe en la página)
    const scrollToTopBtn = document.getElementById('scrollToTop');
    if (scrollToTopBtn) {
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
    }
</script>
