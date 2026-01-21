-- ============================================================================
-- CORREGIR COLORES HARDCODEADOS EN IDENTITAS
-- ============================================================================
-- Fecha: 22/11/2025
-- Base de datos: verumax_identi
-- Este SQL reemplaza los colores verdes hardcodeados con variables CSS dinámicas
-- para que respeten la paleta de colores configurada en cada institución
-- ============================================================================

USE verumax_identi;

-- ============================================================================
-- PÁGINA: Sobre Nosotros - Reemplazar colores verdes con variables CSS
-- ============================================================================

UPDATE identitas_paginas
SET
    contenido = '
<div class="grid md:grid-cols-2 gap-12 items-center">
    <div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Nuestra Misión</h2>
        <p class="mt-4 text-base text-gray-600 dark:text-gray-400 leading-relaxed">
            La <strong>Sociedad Argentina de Justicia Restaurativa (SAJuR)</strong> es una asociación civil sin fines de lucro, fundada en 2017, con el objetivo de promover, difundir e investigar la Justicia Restaurativa como un nuevo paradigma de respuesta al conflicto y al delito.
        </p>
        <p class="mt-4 text-base text-gray-600 dark:text-gray-400 leading-relaxed">
            Buscamos generar espacios de diálogo y formación para la construcción de una sociedad más justa, pacífica e inclusiva, fortaleciendo los lazos comunitarios y reparando el daño a través de la participación de todos los involucrados.
        </p>
        <a href="https://sajur.org/es/quienes-somos" target="_blank" class="mt-6 inline-flex items-center gap-1 font-semibold hover:underline" style="color: var(--color-primario);">
            Conoce más sobre nosotros
            <i data-lucide="arrow-right" class="inline w-4 h-4"></i>
        </a>
    </div>

    <div class="relative">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-8 border dark:border-gray-700">
            <div class="grid grid-cols-2 gap-8 text-center">
                <div>
                    <span class="text-4xl font-bold" style="color: var(--color-primario);">Visión</span>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Ser un referente en la promoción de prácticas restaurativas.</p>
                </div>
                <div>
                    <span class="text-4xl font-bold" style="color: var(--color-primario);">Valores</span>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Diálogo, Respeto, Inclusión y Reparación.</p>
                </div>
                <div>
                    <span class="text-4xl font-bold" style="color: var(--color-primario);">Impacto</span>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Fortalecimiento de la comunidad y la paz social.</p>
                </div>
                <div>
                    <span class="text-4xl font-bold" style="color: var(--color-primario);">Formación</span>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">Capacitación continua para profesionales y la comunidad.</p>
                </div>
            </div>
        </div>
    </div>
</div>
    '
WHERE slug = 'sobre-nosotros'
  AND id_instancia = (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur');

-- ============================================================================
-- PÁGINA: Servicios - Reemplazar colores verdes con variables CSS
-- ============================================================================

UPDATE identitas_paginas
SET
    contenido = '
<div class="text-center mb-12">
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Nuestros Servicios</h2>
    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
        Ofrecemos una amplia gama de servicios orientados a la formación, capacitación y promoción de la Justicia Restaurativa.
    </p>
</div>

<div class="grid md:grid-cols-3 gap-8">
    <!-- Certificados Verificados -->
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700">
        <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4" style="background-color: var(--color-primario); opacity: 0.1;">
            <i data-lucide="award" class="w-6 h-6" style="color: var(--color-primario);"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Certificados Verificados</h3>
        <p class="text-gray-600 dark:text-gray-400">
            Accede a tus certificados académicos con validación QR infalsificable. Sistema de verificación en línea para todos nuestros cursos y programas.
        </p>
    </div>

    <!-- Formación Continua -->
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700">
        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4">
            <i data-lucide="users" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Formación Continua</h3>
        <p class="text-gray-600 dark:text-gray-400">
            Programas de capacitación para profesionales del derecho, trabajo social, educación y la comunidad en general.
        </p>
    </div>

    <!-- Recursos Educativos -->
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700">
        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4">
            <i data-lucide="book-open" class="w-6 h-6 text-purple-600 dark:text-purple-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Recursos Educativos</h3>
        <p class="text-gray-600 dark:text-gray-400">
            Material de estudio, investigaciones y recursos bibliográficos para tu desarrollo profesional en Justicia Restaurativa.
        </p>
    </div>

    <!-- Asesoramiento -->
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700">
        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center mb-4">
            <i data-lucide="lightbulb" class="w-6 h-6 text-orange-600 dark:text-orange-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Asesoramiento</h3>
        <p class="text-gray-600 dark:text-gray-400">
            Consultoría y acompañamiento en la implementación de prácticas restaurativas en organizaciones e instituciones.
        </p>
    </div>

    <!-- Investigación -->
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700">
        <div class="w-12 h-12 bg-pink-100 dark:bg-pink-900 rounded-lg flex items-center justify-center mb-4">
            <i data-lucide="search" class="w-6 h-6 text-pink-600 dark:text-pink-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Investigación</h3>
        <p class="text-gray-600 dark:text-gray-400">
            Desarrollo de investigaciones académicas y estudios de campo sobre Justicia Restaurativa en Argentina y América Latina.
        </p>
    </div>

    <!-- Eventos y Conferencias -->
    <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-sm border dark:border-gray-700">
        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mb-4">
            <i data-lucide="calendar" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Eventos y Conferencias</h3>
        <p class="text-gray-600 dark:text-gray-400">
            Organización de jornadas, seminarios y conferencias nacionales e internacionales sobre Justicia Restaurativa.
        </p>
    </div>
</div>
    '
WHERE slug = 'servicios'
  AND id_instancia = (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur');

-- ============================================================================
-- PÁGINA: Contacto - Reemplazar colores verdes con variables CSS
-- ============================================================================

UPDATE identitas_paginas
SET
    contenido = '
<div class="text-center mb-12">
    <i data-lucide="mail" class="w-12 h-12 mx-auto" style="color: var(--color-primario);"></i>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mt-4">Contacto Administrativo</h2>
    <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
        Si tienes problemas para acceder a tus certificados o necesitas contactar con nuestra administración, no dudes en escribirnos.
    </p>
</div>

<div class="max-w-2xl mx-auto">
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-8 mb-8">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Información de Contacto</h3>
        <div class="space-y-3">
            <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                <i data-lucide="mail" class="w-5 h-5" style="color: var(--color-primario);"></i>
                <a href="mailto:info@sajur.org" class="hover:underline transition" style="hover:color: var(--color-primario);">
                    info@sajur.org
                </a>
            </div>
            <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                <i data-lucide="globe" class="w-5 h-5" style="color: var(--color-primario);"></i>
                <a href="https://sajur.org/es/" target="_blank" class="hover:underline transition" style="hover:color: var(--color-primario);">
                    www.sajur.org
                </a>
            </div>
        </div>
    </div>
</div>
    '
WHERE slug = 'contacto'
  AND id_instancia = (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur');

-- ============================================================================
-- TAMBIÉN CORREGIR EL MENSAJE DE ÉXITO DEL FORMULARIO EN home.php
-- ============================================================================
-- NOTA: Este cambio debe hacerse en el archivo identitas/templates/home.php
-- línea 314, reemplazar:
--   bg-green-50 dark:bg-green-900/20 border-2 border-green-300 dark:border-green-800 text-green-700 dark:text-green-300
-- por:
--   bg-opacity-10 border-2 text-gray-900 dark:text-white
--   style="background-color: var(--color-primario); border-color: var(--color-primario);"

-- ============================================================================
-- VERIFICAR RESULTADOS
-- ============================================================================

SELECT
    slug,
    titulo,
    orden,
    LEFT(contenido, 150) as contenido_preview
FROM identitas_paginas
WHERE id_instancia = (SELECT id_instancia FROM identitas_instances WHERE slug = 'sajur')
ORDER BY orden;

-- ============================================================================
-- CAMBIOS REALIZADOS
-- ============================================================================
--
-- ✅ SOBRE NOSOTROS:
--    - Visión, Valores, Impacto, Formación: text-green-700 → style="color: var(--color-primario);"
--    - Enlaces: text-green-700 → style="color: var(--color-primario);"
--
-- ✅ SERVICIOS:
--    - Ícono "Certificados Verificados": bg-green-100 → style="background-color: var(--color-primario); opacity: 0.1;"
--    - Ícono color: text-green-600 → style="color: var(--color-primario);"
--
-- ✅ CONTACTO:
--    - Íconos: text-green-700 → style="color: var(--color-primario);"
--    - Enlaces hover: hover:text-green-700 → style="hover:color: var(--color-primario);"
--
-- ⏳ PENDIENTE (archivo PHP):
--    - identitas/templates/home.php línea 314 (mensaje de éxito del formulario)
--
-- ============================================================================
