<?php
/**
 * Upload Background - Endpoint para subir fondo de template
 *
 * Recibe una imagen y la guarda en assets/templates/certificados/{slug}/
 *
 * @version 1.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('No se recibieron datos');
    }

    $slug = $input['slug'] ?? '';
    $image_data = $input['image'] ?? '';
    $filename = $input['filename'] ?? 'fondo.jpg';

    // Validar slug
    if (empty($slug)) {
        throw new Exception('Slug del template requerido');
    }

    // Validar que el slug sea seguro (solo letras, números, guiones y guiones bajos)
    if (!preg_match('/^[a-z0-9_-]+$/i', $slug)) {
        throw new Exception('Slug inválido. Solo se permiten letras, números, guiones y guiones bajos.');
    }

    // Validar imagen
    if (empty($image_data)) {
        throw new Exception('Imagen requerida');
    }

    // Validar que sea base64 de imagen
    if (!preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $image_data)) {
        throw new Exception('Formato de imagen inválido. Use JPG, PNG o WebP.');
    }

    // Extraer datos de la imagen
    $image_parts = explode(',', $image_data);
    $image_base64 = $image_parts[1] ?? '';
    $image_binary = base64_decode($image_base64);

    if ($image_binary === false) {
        throw new Exception('Error al decodificar la imagen');
    }

    // Determinar extensión desde el data URL
    preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/', $image_data, $matches);
    $extension = $matches[1] ?? 'jpg';
    if ($extension === 'jpeg') $extension = 'jpg';

    // Sanitizar filename
    $filename = preg_replace('/[^a-z0-9_.-]/i', '_', $filename);
    if (!preg_match('/\.(jpg|jpeg|png|webp)$/i', $filename)) {
        $filename = 'fondo.' . $extension;
    }

    // Directorio destino
    $base_dir = __DIR__ . '/../../assets/templates/certificados/';
    $dest_dir = $base_dir . $slug . '/';
    $dest_path = $dest_dir . $filename;

    // Crear directorio si no existe
    if (!is_dir($dest_dir)) {
        if (!mkdir($dest_dir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio del template');
        }
    }

    // Verificar permisos
    if (!is_writable($dest_dir)) {
        throw new Exception('Sin permisos de escritura en el directorio');
    }

    // Guardar archivo
    $bytes_written = file_put_contents($dest_path, $image_binary);

    if ($bytes_written === false) {
        throw new Exception('Error al guardar la imagen');
    }

    // URL pública
    $public_url = '/assets/templates/certificados/' . $slug . '/' . $filename;

    echo json_encode([
        'success' => true,
        'message' => 'Fondo subido correctamente',
        'path' => $public_url,
        'filename' => $filename,
        'slug' => $slug,
        'size' => $bytes_written
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
