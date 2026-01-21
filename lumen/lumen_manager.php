<?php
/**
 * Lumen Manager - Gestión de portfolios
 * Funciones para crear galerías, subir fotos y actualizar lumen_datos.php
 */

// Cargar datos actuales
require_once __DIR__ . '/../lumen_datos.php';

/**
 * Crear una nueva galería
 */
function crearGaleria($cliente_id, $datos_galeria) {
    global $lumen_portfolios;

    if (!isset($lumen_portfolios[$cliente_id])) {
        return ['exito' => false, 'mensaje' => 'Cliente no encontrado'];
    }

    // Validar datos requeridos
    if (empty($datos_galeria['id']) || empty($datos_galeria['nombre'])) {
        return ['exito' => false, 'mensaje' => 'ID y nombre son requeridos'];
    }

    // Verificar que no exista la galería
    if (isset($lumen_portfolios[$cliente_id]['galerias'][$datos_galeria['id']])) {
        return ['exito' => false, 'mensaje' => 'Ya existe una galería con ese ID'];
    }

    // Crear estructura de galería
    $nueva_galeria = [
        'id' => $datos_galeria['id'],
        'nombre' => $datos_galeria['nombre'],
        'slug' => generarSlug($datos_galeria['nombre']),
        'descripcion' => $datos_galeria['descripcion'] ?? '',
        'icono' => $datos_galeria['icono'] ?? 'folder',
        'color' => $datos_galeria['color'] ?? '#667eea',
        'orden' => $datos_galeria['orden'] ?? count($lumen_portfolios[$cliente_id]['galerias']) + 1,
        'publica' => $datos_galeria['publica'] ?? true,
        'fecha_creacion' => date('Y-m-d'),
        'fotos' => []
    ];

    // Agregar galería
    $lumen_portfolios[$cliente_id]['galerias'][$datos_galeria['id']] = $nueva_galeria;

    // Crear directorio físico
    $directorio = __DIR__ . "/uploads/{$cliente_id}/{$datos_galeria['id']}";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0755, true);
    }

    // Guardar cambios
    if (guardarDatos($lumen_portfolios)) {
        return ['exito' => true, 'mensaje' => 'Galería creada exitosamente', 'galeria' => $nueva_galeria];
    } else {
        return ['exito' => false, 'mensaje' => 'Error al guardar los datos'];
    }
}

/**
 * Subir fotos a una galería
 */
function subirFotos($cliente_id, $galeria_id, $archivos) {
    global $lumen_portfolios;

    if (!isset($lumen_portfolios[$cliente_id]['galerias'][$galeria_id])) {
        return ['exito' => false, 'mensaje' => 'Galería no encontrada'];
    }

    $directorio = __DIR__ . "/uploads/{$cliente_id}/{$galeria_id}";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0755, true);
    }

    $fotos_subidas = [];
    $errores = [];
    $advertencias = [];

    foreach ($archivos['name'] as $key => $nombre) {
        // Validar archivo
        $tipo = $archivos['type'][$key];
        $tmp = $archivos['tmp_name'][$key];
        $error = $archivos['error'][$key];
        $tamanio = $archivos['size'][$key];

        // Verificar errores de PHP
        if ($error !== UPLOAD_ERR_OK) {
            switch ($error) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errores[] = "{$nombre}: Archivo demasiado grande";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errores[] = "{$nombre}: Archivo subido parcialmente";
                    break;
                default:
                    $errores[] = "{$nombre}: Error al subir archivo";
            }
            continue;
        }

        // Obtener información real del archivo
        $info_imagen = @getimagesize($tmp);
        $extension_original = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        // Lista de formatos soportados
        $formatos_directos = ['jpg', 'jpeg', 'png']; // Estos se aceptan directamente
        $formatos_convertibles = ['tiff', 'tif', 'bmp', 'gif', 'webp']; // Estos se convierten a JPG

        $necesita_conversion = false;
        $es_formato_valido = false;

        // Verificar si es un formato directo o convertible
        if (in_array($extension_original, $formatos_directos)) {
            $es_formato_valido = true;
        } elseif (in_array($extension_original, $formatos_convertibles)) {
            $es_formato_valido = true;
            $necesita_conversion = true;
            $advertencias[] = "{$nombre}: Convertido automáticamente a JPG";
        } else {
            $errores[] = "{$nombre}: Formato no soportado (use JPG, PNG, TIFF, BMP, GIF o WebP)";
            continue;
        }

        // Verificar tamaño (50MB máximo para permitir archivos RAW/TIFF que luego se optimizan)
        $limite_tamanio = 50 * 1024 * 1024; // 50MB
        if ($tamanio > $limite_tamanio) {
            $errores[] = "{$nombre}: Archivo muy grande (máx 50MB)";
            continue;
        }

        // Generar nombre único
        $nombre_limpio = sanitizarNombre(pathinfo($nombre, PATHINFO_FILENAME));
        $timestamp = time() . '_' . rand(1000, 9999);

        // Si necesita conversión, la extensión final será jpg
        $extension_final = $necesita_conversion ? 'jpg' : $extension_original;
        $nombre_archivo = $nombre_limpio . '_' . $timestamp . '.' . $extension_final;
        $ruta_destino = $directorio . '/' . $nombre_archivo;

        $procesado_exitoso = false;

        // Procesar imagen
        if ($necesita_conversion) {
            // Convertir a JPG
            $resultado_conversion = convertirAJPG($tmp, $ruta_destino, $info_imagen);
            if ($resultado_conversion['exito']) {
                $procesado_exitoso = true;
                if (isset($resultado_conversion['advertencia'])) {
                    $advertencias[] = "{$nombre}: {$resultado_conversion['advertencia']}";
                }
            } else {
                $errores[] = "{$nombre}: {$resultado_conversion['mensaje']}";
                continue;
            }
        } else {
            // Mover archivo directamente pero optimizar si es muy grande
            if (move_uploaded_file($tmp, $ruta_destino)) {
                // Si es mayor a 5MB, optimizar
                if ($tamanio > 5 * 1024 * 1024) {
                    $resultado_opt = optimizarImagen($ruta_destino, $info_imagen);
                    if ($resultado_opt['optimizada']) {
                        $advertencias[] = "{$nombre}: Optimizada automáticamente para web";
                    }
                }
                $procesado_exitoso = true;
            } else {
                $errores[] = "{$nombre}: Error al guardar el archivo";
                continue;
            }
        }

        // Si el procesamiento fue exitoso, agregar a datos
        if ($procesado_exitoso) {
            $foto = [
                'id' => uniqid('foto_'),
                'archivo_original' => $nombre_archivo,
                'titulo' => ucfirst(str_replace('_', ' ', $nombre_limpio)),
                'descripcion' => '',
                'fecha' => date('Y-m-d'),
                'orden' => count($lumen_portfolios[$cliente_id]['galerias'][$galeria_id]['fotos']) + 1,
                'destacada' => false,
                'tags' => [$galeria_id]
            ];

            $lumen_portfolios[$cliente_id]['galerias'][$galeria_id]['fotos'][] = $foto;
            $fotos_subidas[] = $nombre_archivo;
        }
    }

    // Guardar cambios si hubo fotos subidas
    if (count($fotos_subidas) > 0) {
        if (guardarDatos($lumen_portfolios)) {
            $mensaje = count($fotos_subidas) . " foto(s) subida(s) exitosamente";

            if (count($advertencias) > 0) {
                $mensaje .= ". Notas: " . implode('; ', array_unique($advertencias));
            }

            if (count($errores) > 0) {
                $mensaje .= ". Errores: " . implode('; ', $errores);
            }

            return ['exito' => true, 'mensaje' => $mensaje, 'fotos' => $fotos_subidas];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al guardar los datos'];
        }
    } else {
        return ['exito' => false, 'mensaje' => 'No se pudo subir ninguna foto. ' . implode('; ', $errores)];
    }
}

/**
 * Convertir imagen a JPG con compresión
 */
function convertirAJPG($ruta_origen, $ruta_destino, $info_imagen) {
    if (!$info_imagen) {
        return ['exito' => false, 'mensaje' => 'No se pudo leer la imagen'];
    }

    $imagen_origen = null;
    $ancho = $info_imagen[0];
    $alto = $info_imagen[1];

    // Cargar imagen según tipo
    switch ($info_imagen[2]) {
        case IMAGETYPE_JPEG:
            $imagen_origen = @imagecreatefromjpeg($ruta_origen);
            break;
        case IMAGETYPE_PNG:
            $imagen_origen = @imagecreatefrompng($ruta_origen);
            break;
        case IMAGETYPE_GIF:
            $imagen_origen = @imagecreatefromgif($ruta_origen);
            break;
        case IMAGETYPE_BMP:
            $imagen_origen = @imagecreatefrombmp($ruta_origen);
            break;
        case IMAGETYPE_WEBP:
            $imagen_origen = @imagecreatefromwebp($ruta_origen);
            break;
        case IMAGETYPE_TIFF_II:
        case IMAGETYPE_TIFF_MM:
            // TIFF requiere extensión adicional, intentar con GD si está disponible
            if (function_exists('imagecreatefromstring')) {
                $imagen_origen = @imagecreatefromstring(file_get_contents($ruta_origen));
            }
            break;
    }

    if (!$imagen_origen) {
        return ['exito' => false, 'mensaje' => 'No se pudo procesar la imagen'];
    }

    // Redimensionar si es muy grande (max 4000px en lado más largo)
    $max_dimension = 4000;
    $advertencia = null;

    if ($ancho > $max_dimension || $alto > $max_dimension) {
        $ratio = $ancho / $alto;
        if ($ancho > $alto) {
            $nuevo_ancho = $max_dimension;
            $nuevo_alto = $max_dimension / $ratio;
        } else {
            $nuevo_alto = $max_dimension;
            $nuevo_ancho = $max_dimension * $ratio;
        }

        $imagen_redimensionada = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
        imagecopyresampled($imagen_redimensionada, $imagen_origen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);
        imagedestroy($imagen_origen);
        $imagen_origen = $imagen_redimensionada;

        $advertencia = "Redimensionada de {$ancho}x{$alto} a {$nuevo_ancho}x{$nuevo_alto}px";
    }

    // Guardar como JPG con 90% calidad
    $resultado = imagejpeg($imagen_origen, $ruta_destino, 90);
    imagedestroy($imagen_origen);

    if ($resultado) {
        return ['exito' => true, 'advertencia' => $advertencia];
    } else {
        return ['exito' => false, 'mensaje' => 'Error al guardar imagen convertida'];
    }
}

/**
 * Optimizar imagen existente
 */
function optimizarImagen($ruta_archivo, $info_imagen) {
    if (!$info_imagen) {
        return ['optimizada' => false];
    }

    $imagen = null;
    $ancho = $info_imagen[0];
    $alto = $info_imagen[1];

    // Solo optimizar si es muy grande (> 3000px)
    if ($ancho <= 3000 && $alto <= 3000) {
        return ['optimizada' => false];
    }

    // Cargar imagen
    switch ($info_imagen[2]) {
        case IMAGETYPE_JPEG:
            $imagen = @imagecreatefromjpeg($ruta_archivo);
            break;
        case IMAGETYPE_PNG:
            $imagen = @imagecreatefrompng($ruta_archivo);
            break;
    }

    if (!$imagen) {
        return ['optimizada' => false];
    }

    // Redimensionar a max 3000px
    $max_dimension = 3000;
    $ratio = $ancho / $alto;

    if ($ancho > $alto) {
        $nuevo_ancho = $max_dimension;
        $nuevo_alto = $max_dimension / $ratio;
    } else {
        $nuevo_alto = $max_dimension;
        $nuevo_ancho = $max_dimension * $ratio;
    }

    $imagen_optimizada = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);

    // Preservar transparencia si es PNG
    if ($info_imagen[2] == IMAGETYPE_PNG) {
        imagealphablending($imagen_optimizada, false);
        imagesavealpha($imagen_optimizada, true);
    }

    imagecopyresampled($imagen_optimizada, $imagen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);

    // Guardar optimizada
    if ($info_imagen[2] == IMAGETYPE_JPEG) {
        $resultado = imagejpeg($imagen_optimizada, $ruta_archivo, 85);
    } else {
        $resultado = imagepng($imagen_optimizada, $ruta_archivo, 6);
    }

    imagedestroy($imagen);
    imagedestroy($imagen_optimizada);

    return ['optimizada' => $resultado];
}

/**
 * Eliminar una foto
 */
function eliminarFoto($cliente_id, $galeria_id, $foto_id) {
    global $lumen_portfolios;

    if (!isset($lumen_portfolios[$cliente_id]['galerias'][$galeria_id])) {
        return ['exito' => false, 'mensaje' => 'Galería no encontrada'];
    }

    $galeria = &$lumen_portfolios[$cliente_id]['galerias'][$galeria_id];
    $foto_encontrada = false;
    $archivo_eliminar = '';

    foreach ($galeria['fotos'] as $key => $foto) {
        if ($foto['id'] === $foto_id) {
            $archivo_eliminar = $foto['archivo_original'];
            unset($galeria['fotos'][$key]);
            $galeria['fotos'] = array_values($galeria['fotos']); // Reindexar
            $foto_encontrada = true;
            break;
        }
    }

    if (!$foto_encontrada) {
        return ['exito' => false, 'mensaje' => 'Foto no encontrada'];
    }

    // Eliminar archivo físico
    $ruta_archivo = __DIR__ . "/uploads/{$cliente_id}/{$galeria_id}/{$archivo_eliminar}";
    if (file_exists($ruta_archivo)) {
        unlink($ruta_archivo);
    }

    // Guardar cambios
    if (guardarDatos($lumen_portfolios)) {
        return ['exito' => true, 'mensaje' => 'Foto eliminada exitosamente'];
    } else {
        return ['exito' => false, 'mensaje' => 'Error al guardar los datos'];
    }
}

/**
 * Actualizar configuración de galería
 */
function actualizarGaleria($cliente_id, $galeria_id, $datos) {
    global $lumen_portfolios;

    if (!isset($lumen_portfolios[$cliente_id]['galerias'][$galeria_id])) {
        return ['exito' => false, 'mensaje' => 'Galería no encontrada'];
    }

    // Actualizar campos permitidos
    $campos_actualizables = ['nombre', 'descripcion', 'color', 'publica', 'orden'];
    foreach ($campos_actualizables as $campo) {
        if (isset($datos[$campo])) {
            $lumen_portfolios[$cliente_id]['galerias'][$galeria_id][$campo] = $datos[$campo];
        }
    }

    // Guardar cambios
    if (guardarDatos($lumen_portfolios)) {
        return ['exito' => true, 'mensaje' => 'Galería actualizada exitosamente'];
    } else {
        return ['exito' => false, 'mensaje' => 'Error al guardar los datos'];
    }
}

/**
 * Guardar datos en lumen_datos.php
 */
function guardarDatos($datos) {
    $archivo = __DIR__ . '/../lumen_datos.php';

    // Leer archivo original para preservar las funciones helper
    $contenido_original = file_get_contents($archivo);

    // Encontrar donde empiezan las funciones helper (después del array)
    $pos_funciones = strpos($contenido_original, '/**' . "\n" . ' * Función auxiliar para obtener portfolio por cliente');

    if ($pos_funciones === false) {
        // Fallback si no encuentra el marcador
        $pos_funciones = strpos($contenido_original, 'function obtenerPortfolioLumen');
    }

    // Extraer las funciones helper
    $funciones_helper = '';
    if ($pos_funciones !== false) {
        // Retroceder un poco para capturar el comentario anterior
        $funciones_helper = substr($contenido_original, $pos_funciones);
    }

    // Generar nuevo contenido
    $contenido = "<?php\n";
    $contenido .= "/**\n";
    $contenido .= " * LUMEN - Base de datos de portfolios\n";
    $contenido .= " * Actualizado: " . date('Y-m-d H:i:s') . "\n";
    $contenido .= " */\n\n";
    $contenido .= "\$lumen_portfolios = " . var_export($datos, true) . ";\n\n";

    // Agregar funciones helper si existen
    if (!empty($funciones_helper)) {
        $contenido .= $funciones_helper;
    }

    // Escribir archivo
    return file_put_contents($archivo, $contenido) !== false;
}

/**
 * Generar slug desde texto
 */
function generarSlug($texto) {
    $slug = strtolower(trim($texto));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Sanitizar nombre de archivo
 */
function sanitizarNombre($nombre) {
    $nombre = strtolower(trim($nombre));
    $nombre = preg_replace('/[^a-z0-9_-]/', '_', $nombre);
    $nombre = preg_replace('/_+/', '_', $nombre);
    return trim($nombre, '_');
}
?>
