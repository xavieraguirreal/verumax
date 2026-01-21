<?php
/**
 * VERIFICADOR DE MODO CONSTRUCCIÓN - VERSIÓN SIMPLIFICADA
 *
 * Este archivo debe incluirse DESPUÉS de cargar config.php y definir $slug
 *
 * Uso en sajur/index.php:
 * $slug = 'sajur';
 * require_once __DIR__ . '/../verificar_construccion.php';
 */

// Solo ejecutar si no estamos ya en la página de construcción
if (strpos($_SERVER['PHP_SELF'], 'en-construccion.php') === false) {

    // Verificar que tengamos el slug definido
    if (!isset($slug)) {
        // Intentar obtenerlo del contexto
        $slug = $_GET['institucion'] ?? 'sajur';
    }

    // Cargar configuración solo si no está ya cargada
    if (!function_exists('getInstanceConfig')) {
        require_once __DIR__ . '/identitas/config.php';
    }

    // Obtener configuración de la instancia
    try {
        $instance = getInstanceConfig($slug);

        // Si el sitio está en construcción, verificar si hay módulos activos
        if ($instance && isset($instance['sitio_en_construccion']) && $instance['sitio_en_construccion'] == 1) {

            // DEBUG
            echo "<!-- DEBUG verificar_construccion.php -->";
            echo "<!-- sitio_en_construccion = 1 -->";

            // EXCEPCIÓN: Si Identitas está desactivado pero Certificatum está activo,
            // permitir acceso (mostrar solo el portal de certificados)
            $identitas_desactivado = !$instance['identitas_activo'];
            $certificatum_activo = $instance['modulo_certificatum'];

            echo "<!-- identitas_desactivado = " . ($identitas_desactivado ? 'true' : 'false') . " -->";
            echo "<!-- certificatum_activo = " . ($certificatum_activo ? 'true' : 'false') . " -->";

            if ($identitas_desactivado && $certificatum_activo) {
                echo "<!-- EXCEPCIÓN: Permitir acceso a Certificatum sin redirigir -->";
                echo "<!-- /DEBUG verificar_construccion.php -->";
                // No redirigir - permitir que se muestre solo Certificatum
                return;
            }

            echo "<!-- Redirigiendo a en-construccion.php -->";
            echo "<!-- /DEBUG verificar_construccion.php -->";

            // En otros casos, redirigir a la página de construcción
            header('Location: en-construccion.php?institucion=' . urlencode($slug));
            exit;
        }
    } catch (Exception $e) {
        // Si hay error, continuar normalmente (no bloquear el sitio)
        error_log('Error en verificar_construccion.php: ' . $e->getMessage());
    }
}
