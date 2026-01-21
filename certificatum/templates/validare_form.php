<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Certificado - VERUMax</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/assets/images/logo-verumax-escudo.png">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gold-gradient {
            background: linear-gradient(135deg, #D4AF37 0%, #F0D377 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-black to-gray-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3">
                <img src="/assets/images/logo-verumax-escudo.png" alt="VERUMax" class="h-12 w-auto">
                <span class="text-2xl font-bold text-gold">VERUMax</span>
            </a>
        </div>

        <!-- Card principal -->
        <div class="bg-white/5 backdrop-blur-lg border border-gold/20 rounded-2xl p-8 shadow-2xl">
            <!-- Icono -->
            <div class="mx-auto w-16 h-16 bg-gold/10 rounded-full flex items-center justify-center mb-6">
                <i data-lucide="shield-check" class="w-8 h-8 text-gold"></i>
            </div>

            <h1 class="text-2xl font-bold text-white text-center mb-2">Validar Certificado</h1>
            <p class="text-gray-400 text-center mb-8">
                Ingresá el código de validación para verificar la autenticidad del documento.
            </p>

            <!-- Formulario -->
            <form action="" method="POST" class="space-y-6">
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-300 mb-2">
                        Código de validación
                    </label>
                    <input
                        type="text"
                        id="codigo"
                        name="codigo"
                        placeholder="VALID-XXXXXXXXXXXX"
                        required
                        autocomplete="off"
                        class="w-full px-4 py-3 bg-black/50 border border-gold/30 rounded-lg text-white text-center font-mono uppercase tracking-wider placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-gold focus:border-transparent transition-all"
                    >
                    <p class="mt-2 text-xs text-gray-500 text-center">
                        El código se encuentra debajo del QR en tu certificado
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 gold-gradient text-black font-bold rounded-lg hover:opacity-90 transition-opacity shadow-lg shadow-gold/20 flex items-center justify-center gap-2"
                >
                    <i data-lucide="search" class="w-5 h-5"></i>
                    Verificar Documento
                </button>
            </form>

            <!-- Info adicional -->
            <div class="mt-8 pt-6 border-t border-gray-700">
                <div class="flex items-start gap-3 text-sm">
                    <i data-lucide="info" class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5"></i>
                    <p class="text-gray-400">
                        También podés escanear el código QR del certificado directamente con tu celular para validarlo.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-600 text-xs mt-6">
            Sistema Certificatum - Credenciales Verificadas by VERUMax
        </p>
    </div>

    <script>
        lucide.createIcons();

        // Auto-focus en el input
        document.getElementById('codigo').focus();

        // Convertir a mayúsculas mientras escribe
        document.getElementById('codigo').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html>
