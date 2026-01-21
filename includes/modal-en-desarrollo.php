<?php
/**
 * Modal de Servicio en Desarrollo
 * Componente reutilizable para mostrar que un servicio/solución está en fase de implementación
 */
?>

<!-- Modal para servicios en desarrollo -->
<div id="servicioModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
    <div class="bg-gradient-to-br from-gray-900 to-black border-2 border-gold/30 rounded-2xl max-w-lg w-full p-8 relative animate-modal-in">
        <!-- Close button -->
        <button id="closeModalBtn" class="absolute top-4 right-4 text-gray-400 hover:text-white transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Icon -->
        <div class="w-20 h-20 mx-auto mb-6 bg-gold/20 rounded-full flex items-center justify-center border-2 border-gold/40">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
        </div>

        <!-- Content -->
        <h2 id="modalTitle" class="text-2xl font-bold text-center mb-3 text-gold">
            Título del Servicio
        </h2>

        <p id="modalDescription" class="text-gray-300 text-center mb-6 leading-relaxed">
            Descripción del servicio que estará disponible próximamente.
        </p>

        <!-- Status Badge -->
        <div class="bg-gold/10 border border-gold/30 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gold flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-gold font-semibold">En Fase de Desarrollo</span>
            </div>
            <p class="text-sm text-gray-400 leading-relaxed">
                Estamos trabajando en esta funcionalidad. Dejanos tu email y te notificaremos cuando esté lista.
            </p>
        </div>

        <!-- Email Notification Form -->
        <form id="notifyForm" class="mb-6">
            <div class="mb-4">
                <label for="notifyEmail" class="block text-sm font-medium text-gray-300 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Notificarme cuando esté disponible
                </label>
                <input
                    type="email"
                    id="notifyEmail"
                    name="email"
                    placeholder="tu@email.com"
                    required
                    class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-lg focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 transition-all"
                />
                <p id="emailError" class="text-red-400 text-sm mt-2 hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="emailErrorText">Por favor, ingresa un email válido</span>
                </p>
                <p id="emailSuccess" class="text-green-400 text-sm mt-2 hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    ¡Gracias! Te notificaremos cuando esté disponible
                </p>
            </div>

            <button
                type="submit"
                id="notifyBtn"
                class="w-full px-6 py-3 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="notifyBtnText">Notificarme</span>
            </button>
        </form>

        <!-- Actions -->
        <div class="flex gap-3">
            <button id="closeModalBtnBottom" class="flex-1 px-6 py-3 bg-gray-800 border border-gray-700 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                Cerrar
            </button>
            <a href="#" id="contactarBtn" class="flex-1 px-6 py-3 bg-gray-700 border border-gray-600 text-white font-semibold rounded-lg hover:bg-gray-600 transition-colors text-center flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Contactar
            </a>
        </div>
    </div>
</div>

<style>
@keyframes modal-in {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.animate-modal-in {
    animation: modal-in 0.3s ease-out;
}

#servicioModal.active {
    display: flex !important;
}
</style>

<script>
(function() {
    const modal = document.getElementById('servicioModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const closeBtn = document.getElementById('closeModalBtn');
    const closeBtnBottom = document.getElementById('closeModalBtnBottom');
    const contactarBtn = document.getElementById('contactarBtn');
    const notifyForm = document.getElementById('notifyForm');
    const notifyEmail = document.getElementById('notifyEmail');
    const notifyBtn = document.getElementById('notifyBtn');
    const notifyBtnText = document.getElementById('notifyBtnText');
    const emailError = document.getElementById('emailError');
    const emailErrorText = document.getElementById('emailErrorText');
    const emailSuccess = document.getElementById('emailSuccess');

    let currentService = '';

    // Función para validar email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Función para abrir modal
    window.openServicioModal = function(title, description) {
        modalTitle.textContent = title;
        modalDescription.textContent = description;
        currentService = title;

        // Reset form
        notifyForm.reset();
        emailError.classList.add('hidden');
        emailSuccess.classList.add('hidden');
        notifyBtn.disabled = false;
        notifyBtnText.textContent = 'Notificarme';

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    // Función para cerrar modal
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Manejo del formulario
    notifyForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const email = notifyEmail.value.trim();

        // Ocultar mensajes previos
        emailError.classList.add('hidden');
        emailSuccess.classList.add('hidden');

        // Validar email
        if (!email) {
            emailErrorText.textContent = 'Por favor, ingresa tu email';
            emailError.classList.remove('hidden');
            notifyEmail.focus();
            return;
        }

        if (!isValidEmail(email)) {
            emailErrorText.textContent = 'Por favor, ingresa un email válido';
            emailError.classList.remove('hidden');
            notifyEmail.focus();
            return;
        }

        // Deshabilitar botón mientras procesa
        notifyBtn.disabled = true;
        notifyBtnText.textContent = 'Procesando...';

        // Preparar datos para envío futuro
        const notificationData = {
            email: email,
            service: currentService,
            timestamp: new Date().toISOString(),
            page: window.location.pathname
        };

        // TODO: Implementar envío a backend
        // fetch('/api/notify-service.php', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //     },
        //     body: JSON.stringify(notificationData)
        // })
        // .then(response => response.json())
        // .then(data => {
        //     if (data.success) {
        //         emailSuccess.classList.remove('hidden');
        //         notifyForm.reset();
        //     } else {
        //         emailErrorText.textContent = data.message || 'Ocurrió un error. Intenta nuevamente.';
        //         emailError.classList.remove('hidden');
        //     }
        // })
        // .catch(error => {
        //     emailErrorText.textContent = 'Error de conexión. Intenta nuevamente.';
        //     emailError.classList.remove('hidden');
        // })
        // .finally(() => {
        //     notifyBtn.disabled = false;
        //     notifyBtnText.textContent = 'Notificarme';
        // });

        // Por ahora, solo guardamos en localStorage y mostramos mensaje de éxito
        console.log('Notificación registrada:', notificationData);

        // Guardar en localStorage (temporal hasta implementar backend)
        const notifications = JSON.parse(localStorage.getItem('pendingNotifications') || '[]');
        notifications.push(notificationData);
        localStorage.setItem('pendingNotifications', JSON.stringify(notifications));

        // Mostrar mensaje de éxito
        setTimeout(function() {
            emailSuccess.classList.remove('hidden');
            notifyForm.reset();
            notifyBtn.disabled = false;
            notifyBtnText.textContent = 'Notificarme';

            // Opcional: cerrar modal después de 3 segundos
            setTimeout(function() {
                closeModal();
            }, 3000);
        }, 1000);
    });

    // Limpiar error al escribir
    notifyEmail.addEventListener('input', function() {
        if (emailError.classList.contains('hidden') === false) {
            emailError.classList.add('hidden');
        }
    });

    // Event listener para botón contactar
    contactarBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'contactus.php?servicio=' + encodeURIComponent(currentService);
    });

    // Event listeners para cerrar
    closeBtn.addEventListener('click', closeModal);
    closeBtnBottom.addEventListener('click', closeModal);

    // Cerrar al hacer clic fuera del modal
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Cerrar con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    // Auto-bind elementos con data-servicio
    document.addEventListener('DOMContentLoaded', function() {
        const servicioElements = document.querySelectorAll('[data-servicio]');
        servicioElements.forEach(function(element) {
            element.style.cursor = 'pointer';
            element.addEventListener('click', function(e) {
                e.preventDefault();
                const title = element.getAttribute('data-servicio');
                const description = element.getAttribute('data-descripcion') || 'Esta funcionalidad estará disponible próximamente.';
                openServicioModal(title, description);
            });
        });

        // Debug: Ver notificaciones pendientes en consola
        const pendingNotifications = JSON.parse(localStorage.getItem('pendingNotifications') || '[]');
        if (pendingNotifications.length > 0) {
            console.log('Notificaciones pendientes de envío:', pendingNotifications);
        }
    });
})();
</script>
