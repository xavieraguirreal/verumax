<?php
/**
 * OriginalisDoc - Botón de Chat con Veritas (Agente IA)
 * Version: 1.0.0
 */
?>

<!-- Botón Chat Veritas (IA) -->
<button id="veritasChatBtn" class="fixed bottom-8 right-24 w-14 h-14 bg-gradient-to-br from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 z-50 flex items-center justify-center group">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
    </svg>
    <span class="absolute right-16 bg-gray-900 text-white px-3 py-2 rounded-lg text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
        <?= $lang['veritas_chat_btn'] ?? 'Chat con Veritas IA' ?>
    </span>
</button>

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
            <?= $lang['veritas_titulo'] ?? 'Veritas IA' ?>
        </h2>
        <p class="text-gray-300 text-center mb-2">
            <?= $lang['veritas_subtitulo'] ?? 'Nuestro Agente de Inteligencia Artificial Especializado' ?>
        </p>
        <p class="text-gold text-center text-lg font-semibold mb-6">
            <?= $lang['veritas_proximamente'] ?? '¡Próximamente!' ?>
        </p>

        <div class="bg-gray-800/50 border border-purple-500/20 rounded-xl p-4 mb-6">
            <p class="text-sm text-gray-400 text-center leading-relaxed">
                <?= $lang['veritas_descripcion'] ?? 'Veritas estará disponible muy pronto para ayudarte con consultas sobre certificados, validaciones y más.' ?>
            </p>
        </div>

        <button id="closeVeritasBtn" class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold rounded-lg transition-all duration-200">
            <?= $lang['veritas_entendido'] ?? 'Entendido' ?>
        </button>
    </div>
</div>

<style>
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
</script>
