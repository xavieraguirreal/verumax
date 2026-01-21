<?php
/**
 * VERUMax - Generador de Favicon
 * Genera favicons en múltiples tamaños desde el logo institucional
 * Ubicación: /includes/favicon_generator.php
 */

use VERUMax\Services\DatabaseService;

/**
 * Obtiene conexión a la BD verumax_general
 */
function getFaviconDBConnection() {
    // Cargar env_loader si no está cargado
    $env_loader = dirname(__DIR__) . '/admin/env_loader.php';
    if (!function_exists('env') && file_exists($env_loader)) {
        require_once $env_loader;
    }

    return DatabaseService::get('general');
}

/**
 * Genera favicons en múltiples tamaños desde una imagen
 *
 * @param string $logo_url URL del logo a convertir
 * @param string $slug Slug de la institución
 * @return array Resultado con success y mensaje/datos
 */
function generarFavicon($logo_url, $slug) {
    try {
        // Directorio de destino: /{slug}/assets/favicons/
        $base_dir = dirname(__DIR__); // Raíz del proyecto (subir 1 nivel desde /includes/)
        $favicon_dir = $base_dir . '/' . $slug . '/assets/favicons/';
        if (!is_dir($favicon_dir)) {
            if (!mkdir($favicon_dir, 0755, true)) {
                return ['success' => false, 'mensaje' => 'No se pudo crear el directorio de favicons'];
            }
        }

        // Descargar imagen si es URL externa
        if (strpos($logo_url, 'http') === 0) {
            $temp_file = sys_get_temp_dir() . '/' . uniqid('logo_') . '.tmp';
            $logo_content = @file_get_contents($logo_url);

            if ($logo_content === false) {
                return ['success' => false, 'mensaje' => 'No se pudo descargar la imagen del logo'];
            }

            file_put_contents($temp_file, $logo_content);
            $logo_path = $temp_file;
        } else {
            // Ruta local
            $logo_path = $_SERVER['DOCUMENT_ROOT'] . $logo_url;
            if (!file_exists($logo_path)) {
                return ['success' => false, 'mensaje' => 'El archivo del logo no existe'];
            }
        }

        // Obtener información de la imagen
        $image_info = @getimagesize($logo_path);
        if ($image_info === false) {
            return ['success' => false, 'mensaje' => 'El archivo no es una imagen válida'];
        }

        $mime = $image_info['mime'];

        // Cargar imagen según tipo
        switch ($mime) {
            case 'image/jpeg':
                $source = @imagecreatefromjpeg($logo_path);
                break;
            case 'image/png':
                $source = @imagecreatefrompng($logo_path);
                break;
            case 'image/gif':
                $source = @imagecreatefromgif($logo_path);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $source = @imagecreatefromwebp($logo_path);
                } else {
                    return ['success' => false, 'mensaje' => 'WebP no soportado en este servidor'];
                }
                break;
            default:
                return ['success' => false, 'mensaje' => 'Formato de imagen no soportado: ' . $mime];
        }

        if ($source === false) {
            return ['success' => false, 'mensaje' => 'No se pudo procesar la imagen'];
        }

        // Habilitar alpha blending para transparencias
        imagealphablending($source, true);
        imagesavealpha($source, true);

        // Tamaños a generar
        $sizes = [
            'favicon-16x16.png' => 16,
            'favicon-32x32.png' => 32,
            'favicon-96x96.png' => 96,
            'apple-touch-icon.png' => 180,
            'android-chrome-192x192.png' => 192
        ];

        $generated_files = [];

        foreach ($sizes as $filename => $size) {
            // Crear imagen redimensionada
            $resized = imagecreatetruecolor($size, $size);

            // Preservar transparencia
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $size, $size, $transparent);
            imagealphablending($resized, true);

            // Redimensionar
            $orig_width = imagesx($source);
            $orig_height = imagesy($source);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $size, $size, $orig_width, $orig_height);

            // Guardar
            $output_path = $favicon_dir . $filename;
            // Para favicon.ico, guardamos como PNG (navegadores modernos lo aceptan)
            if (imagepng($resized, $output_path, 9)) {
                $generated_files[] = $filename;
            }

            // Si es favicon-32x32, también crear una copia en la raíz de la institución como favicon.ico
            if ($filename === 'favicon-32x32.png') {
                $favicon_ico_path = $base_dir . '/' . $slug . '/favicon.ico';
                copy($output_path, $favicon_ico_path);
            }

            imagedestroy($resized);
        }

        imagedestroy($source);

        // Limpiar archivo temporal si existe
        if (isset($temp_file) && file_exists($temp_file)) {
            @unlink($temp_file);
        }

        if (empty($generated_files)) {
            return ['success' => false, 'mensaje' => 'No se pudo generar ningún favicon'];
        }

        // Actualizar base de datos en verumax_general
        $pdo_general = getFaviconDBConnection();

        $stmt = $pdo_general->prepare("
            UPDATE instances
            SET favicon_url = :url,
                favicon_generated_at = NOW(),
                favicon_generado = 1
            WHERE slug = :slug
        ");

        // URL relativa desde el subdominio (funciona porque está en /{slug}/assets/favicons/)
        $favicon_url = '/assets/favicons/favicon-32x32.png';

        $stmt->execute([
            'url' => $favicon_url,
            'slug' => $slug
        ]);

        return [
            'success' => true,
            'mensaje' => 'Favicons generados exitosamente',
            'archivos' => $generated_files,
            'favicon_url' => $favicon_url
        ];

    } catch (Exception $e) {
        error_log('[FAVICON] Error al generar favicon: ' . $e->getMessage());
        return ['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Elimina los favicons de una institución
 *
 * @param string $slug Slug de la institución
 * @return bool
 */
function eliminarFavicons($slug) {
    $base_dir = dirname(__DIR__); // Raíz del proyecto
    $favicon_dir = $base_dir . '/' . $slug . '/assets/favicons/';
    $pattern = $favicon_dir . '*.png';

    $files = glob($pattern);
    foreach ($files as $file) {
        @unlink($file);
    }

    // Actualizar BD en verumax_general
    try {
        $pdo_general = getFaviconDBConnection();

        $stmt = $pdo_general->prepare("
            UPDATE instances
            SET favicon_url = NULL,
                favicon_generated_at = NULL,
                favicon_generado = 0
            WHERE slug = :slug
        ");
        $stmt->execute(['slug' => $slug]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}
