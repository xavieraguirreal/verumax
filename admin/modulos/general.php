<?php
/**
 * MÓDULO: GENERAL
 * Control de activación de módulos y configuración global
 */

// Ya estamos autenticados por index.php
// $admin ya está disponible

require_once __DIR__ . '/../../identitas/config.php';

use VERUMax\Services\InstitutionService;
use VERUMax\Services\DatabaseService;

$slug = $admin['slug'];
$pdo = getDBConnection();

/**
 * Helper: Obtiene conexión a verumax_general usando DatabaseService
 * (lee credenciales desde .env según el entorno)
 */
function getGeneralConnection() {
    return DatabaseService::get('general');
}

/**
 * Helper: Normaliza URL agregando https:// si no tiene protocolo
 */
function normalizarUrl($url) {
    $url = trim($url);
    if (empty($url)) {
        return '';
    }
    // Si no tiene protocolo, agregar https://
    if (!preg_match('/^https?:\/\//i', $url)) {
        $url = 'https://' . $url;
    }
    return $url;
}

// Obtener configuración actual
$instance = getInstanceConfig($slug);
if (!$instance) {
    die('Error: Instancia no encontrada');
}

// Manejar mensajes de éxito desde redirect
$mensaje = '';
$tipo_mensaje = '';
$scroll_to = '';
// Tab activo: priorizar POST, luego GET, por defecto 'informacion'
// Mapear nombres antiguos a nuevos para compatibilidad
$active_tab_raw = $_POST['active_tab'] ?? $_GET['tab'] ?? 'institucion';
$tab_mapping = [
    'informacion' => 'institucion',
    'branding' => 'apariencia',
    'modulos' => 'configuracion',
    'firmantes' => 'configuracion'
];
$active_tab = $tab_mapping[$active_tab_raw] ?? $active_tab_raw;

if (isset($_GET['success'])) {
    $tipo_mensaje = 'success';
    switch ($_GET['success']) {
        case 'informacion':
            $mensaje = 'Información institucional actualizada correctamente';
            $scroll_to = 'info-institucional';
            $active_tab = 'institucion';
            break;
        case 'colores':
            $mensaje = 'Paleta de colores actualizada correctamente';
            $scroll_to = 'paleta-colores';
            $active_tab = 'apariencia';
            break;
        case 'modulos':
            $mensaje = 'Configuración de módulos actualizada correctamente';
            $scroll_to = 'activacion-modulos';
            $active_tab = 'configuracion';
            break;
        case 'certificatum':
            $mensaje = 'Configuración de integración de Certificatum actualizada correctamente';
            $scroll_to = 'config-certificatum';
            $active_tab = 'configuracion';
            break;
        case 'logo':
            $mensaje = 'Logo actualizado correctamente';
            $scroll_to = 'logo-favicon';
            $active_tab = 'apariencia';
            break;
        case 'favicon':
            $mensaje = 'Favicons generados exitosamente';
            $scroll_to = 'logo-favicon';
            $active_tab = 'apariencia';
            break;
        case 'favicon_eliminado':
            $mensaje = 'Favicons eliminados correctamente';
            $scroll_to = 'logo-favicon';
            $active_tab = 'apariencia';
            break;
        case 'construccion':
            $mensaje = 'Configuración de sitio en construcción actualizada correctamente';
            $scroll_to = 'sitio-construccion';
            $active_tab = 'configuracion';
            break;
        case 'robots':
            $mensaje = 'Configuración de indexación actualizada correctamente';
            $scroll_to = 'seo-robots';
            $active_tab = 'configuracion';
            break;
        case 'firmantes':
            $mensaje = 'Firmantes actualizados correctamente';
            $scroll_to = 'config-firmantes';
            $active_tab = 'configuracion';
            break;
        case 'idiomas':
            $mensaje = 'Configuración de idiomas actualizada correctamente';
            $scroll_to = 'config-idiomas';
            $active_tab = 'configuracion';
            break;
        default:
            $mensaje = 'Cambios guardados correctamente';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['modulo']) && $_GET['modulo'] === 'general') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'actualizar_informacion':
            try {
                // Procesar redes sociales
                $redes = json_encode([
                    'instagram' => $_POST['instagram'] ?? '',
                    'facebook' => $_POST['facebook'] ?? '',
                    'linkedin' => $_POST['linkedin'] ?? '',
                    'whatsapp' => $_POST['whatsapp'] ?? '',
                    'twitter' => $_POST['twitter'] ?? '',
                    'youtube' => $_POST['youtube'] ?? ''
                ]);

                // Conectar a verumax_general (usa credenciales del .env)
                $pdo_general = getGeneralConnection();

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET nombre = :nombre,
                        nombre_completo = :nombre_completo,
                        mision = :mision,
                        sitio_web_oficial = :sitio_web,
                        email_contacto = :email,
                        redes_sociales = :redes
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'nombre' => $_POST['nombre'],
                    'nombre_completo' => $_POST['nombre_completo'],
                    'mision' => $_POST['mision'] ?? '',
                    'sitio_web' => normalizarUrl($_POST['sitio_web_oficial'] ?? ''),
                    'email' => $_POST['email_contacto'] ?? '',
                    'redes' => $redes,
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Limpiar cache
                InstitutionService::clearCache($slug);

                // Mensaje de éxito (sin redirect porque estamos incluidos desde index.php)
                $mensaje = 'Información institucional actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'info-institucional';
                $active_tab = 'institucion'; // Mantener en la pestaña Información

                // Recargar instancia con nuevos datos
                $instance = getInstanceConfig($slug);

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar información: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'actualizar_colores':
            try {
                // Conectar a verumax_general (usa credenciales del .env)
                $pdo_general = getGeneralConnection();

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET color_primario = :color_primario,
                        color_secundario = :color_secundario,
                        color_acento = :color_acento,
                        paleta_colores = :paleta_colores,
                        tema_default = :tema_default
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'color_primario' => $_POST['color_primario'],
                    'color_secundario' => $_POST['color_secundario'],
                    'color_acento' => $_POST['color_acento'],
                    'paleta_colores' => $_POST['paleta_colores'],
                    'tema_default' => $_POST['tema_default'] ?? 'dark',
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Limpiar cache
                InstitutionService::clearCache($slug);

                // Mensaje de éxito (sin redirect porque estamos incluidos desde index.php)
                $mensaje = 'Paleta de colores actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'paleta-colores';
                $active_tab = 'apariencia'; // Mantener en la pestaña Branding

                // Recargar instancia con nuevos datos
                $instance = getInstanceConfig($slug);

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar colores: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'actualizar_modulos':
            try {
                $identitas_activo = isset($_POST['identitas_activo']) ? 1 : 0;
                $certificatum_activo = isset($_POST['modulo_certificatum']) ? 1 : 0;

                // Nota: Ahora permitimos desactivar Identitas manteniendo Certificatum activo
                // Esto permite tener solo el portal de certificados sin el sitio institucional

                // Conectar a verumax_general (usa credenciales del .env)
                $pdo_general = getGeneralConnection();

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET identitas_activo = :identitas_activo,
                        modulo_certificatum = :certificatum_activo
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'identitas_activo' => $identitas_activo,
                    'certificatum_activo' => $certificatum_activo,
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Mensaje de éxito
                $mensaje = 'Configuración de módulos actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'activacion-modulos';
                $active_tab = 'configuracion'; // Mantener en la pestaña Módulos

                // Limpiar cache y recargar instancia
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar módulos: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'actualizar_openai':
            try {
                $ia_habilitada = isset($_POST['openai_habilitado']) ? 1 : 0;

                // Conectar a verumax_general (usa credenciales del .env)
                $pdo_general = getGeneralConnection();

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET ia_habilitada = :ia_habilitada
                    WHERE id_instancia = :id_instancia
                ");
                $stmt->execute([
                    'ia_habilitada' => $ia_habilitada,
                    'id_instancia' => $instance['id_instancia']
                ]);

                $mensaje = 'Configuracion de IA actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'config-openai';
                $active_tab = 'configuracion';

                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar IA: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'actualizar_integracion_certificatum':
            try {
                // Conectar a verumax_certifi para actualizar configuración de certificatum
                $pdo_certifi = new PDO(
                    "mysql:host=" . env('CERTIFI_DB_HOST', 'localhost') . ";dbname=" . env('CERTIFI_DB_NAME', 'verumax_certifi') . ";charset=utf8mb4",
                    env('CERTIFI_DB_USER', 'root'),
                    env('CERTIFI_DB_PASS', ''),
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                $stmt = $pdo_certifi->prepare("
                    UPDATE certificatum_config
                    SET certificatum_modo = :modo,
                        certificatum_titulo = :titulo,
                        certificatum_posicion = :posicion,
                        certificatum_icono = :icono
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'modo' => $_POST['certificatum_modo'],
                    'titulo' => $_POST['certificatum_titulo'],
                    'posicion' => $_POST['certificatum_posicion'],
                    'icono' => $_POST['certificatum_icono'],
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Mensaje de éxito
                $mensaje = 'Configuración de integración de Certificatum actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'config-certificatum';
                $active_tab = 'configuracion'; // Mantener en la pestaña Módulos

                // Limpiar cache y recargar instancia
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar integración: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'actualizar_logo':
            try {
                $logo_url = $_POST['logo_url'] ?? '';

                // Si se subió un archivo, procesarlo
                if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
                    // Crear carpeta de uploads si no existe
                    $upload_dir = __DIR__ . '/../../uploads/logos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Validar tipo de archivo
                    $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'];
                    $file_type = $_FILES['logo_file']['type'];

                    if (!in_array($file_type, $allowed_types)) {
                        $mensaje = 'Tipo de archivo no permitido. Solo PNG, JPG o SVG.';
                        $tipo_mensaje = 'error';
                        break;
                    }

                    // Validar tamaño (máximo 5MB)
                    if ($_FILES['logo_file']['size'] > 5 * 1024 * 1024) {
                        $mensaje = 'El archivo es muy grande. Máximo 5MB.';
                        $tipo_mensaje = 'error';
                        break;
                    }

                    // Generar nombre único para el archivo
                    $extension = pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION);
                    $filename = $slug . '-logo-' . time() . '.' . $extension;
                    $filepath = $upload_dir . $filename;

                    // Mover archivo
                    if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $filepath)) {
                        // Generar URL absoluta (para soportar subdominios)
                        $dominio_principal = 'https://verumax.com';
                        $logo_url = $dominio_principal . '/uploads/logos/' . $filename;
                    } else {
                        $mensaje = 'Error al subir el archivo';
                        $tipo_mensaje = 'error';
                        break;
                    }
                }

                // Actualizar en base de datos (usa credenciales del .env)
                $pdo_general = getGeneralConnection();

                $logo_estilo = $_POST['logo_estilo'] ?? 'rectangular';
                $logo_mostrar_texto = isset($_POST['logo_mostrar_texto']) ? 1 : 0;

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET logo_url = :logo_url,
                        logo_estilo = :logo_estilo,
                        logo_mostrar_texto = :logo_mostrar_texto
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'logo_url' => $logo_url,
                    'logo_estilo' => $logo_estilo,
                    'logo_mostrar_texto' => $logo_mostrar_texto,
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Mensaje de éxito
                $mensaje = 'Logo actualizado correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'logo-favicon';
                $active_tab = 'apariencia'; // Mantener en la pestaña Branding

                // Limpiar cache y recargar instancia
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar logo: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'generar_favicon':
            require_once __DIR__ . '/../../includes/favicon_generator.php';

            if (empty($instance['logo_url'])) {
                $mensaje = 'Primero debes subir un logo para poder generar el favicon';
                $tipo_mensaje = 'error';
            } else {
                $result = generarFavicon($instance['logo_url'], $slug);

                if ($result['success']) {
                    // Mensaje de éxito
                    $mensaje = 'Favicon generado correctamente';
                    $tipo_mensaje = 'success';
                    $scroll_to = 'logo-favicon';
                    $active_tab = 'apariencia'; // Mantener en la pestaña Branding

                    // Limpiar cache y recargar instancia
                    InstitutionService::clearCache($slug);
                    $instance = getInstanceConfig($slug);
                } else {
                    $mensaje = 'Error al generar favicon: ' . $result['mensaje'];
                    $tipo_mensaje = 'error';
                }
            }
            break;

        case 'eliminar_favicon':
            require_once __DIR__ . '/../../includes/favicon_generator.php';

            if (eliminarFavicons($slug)) {
                // Mensaje de éxito
                $mensaje = 'Favicons eliminados correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'logo-favicon';
                $active_tab = 'apariencia'; // Mantener en la pestaña Branding

                // Limpiar cache y recargar instancia
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);
            } else {
                $mensaje = 'Error al eliminar favicons';
                $tipo_mensaje = 'error';
            }
            break;

        case 'actualizar_construccion':
            try {
                $pdo_general = getGeneralConnection();

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET sitio_en_construccion = :en_construccion,
                        mensaje_construccion = :mensaje
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'en_construccion' => isset($_POST['sitio_en_construccion']) ? 1 : 0,
                    'mensaje' => $_POST['mensaje_construccion'] ?? '',
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Mensaje de éxito
                $mensaje = 'Configuración de construcción actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'sitio-construccion';
                $active_tab = 'apariencia'; // La sección está en la pestaña Apariencia

                // Limpiar cache y recargar instancia
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar configuración: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'actualizar_seo':
            try{
                $pdo_general = getGeneralConnection();

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET seo_title = :seo_title,
                        seo_description = :seo_description,
                        seo_keywords = :seo_keywords
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'seo_title' => $_POST['seo_title'] ?? '',
                    'seo_description' => $_POST['seo_description'] ?? '',
                    'seo_keywords' => $_POST['seo_keywords'] ?? '',
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Limpiar cache y recargar
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

                $mensaje = 'Optimización SEO actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'optimizacion-seo';
                $active_tab = 'apariencia';

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar SEO: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'actualizar_seo_robots':
            try {
                // Conectar a verumax_general (usa credenciales del .env)
                $pdo_general = getGeneralConnection();

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET robots_noindex = :robots_noindex
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'robots_noindex' => isset($_POST['robots_noindex']) ? 1 : 0,
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Mensaje de éxito
                $mensaje = 'Configuración de robots actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'seo-robots';
                $active_tab = 'configuracion'; // Mantener en la pestaña Módulos

                // Limpiar cache y recargar instancia
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar configuración: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            break;

        case 'eliminar_firma':
            try {
                $pdo_general = getGeneralConnection();
                $num_firma = (int)($_POST['num_firma'] ?? 0);

                if ($num_firma === 1) {
                    // Eliminar archivo físico
                    $firmas_dir = __DIR__ . '/../../assets/images/firmas/';
                    $patterns = [$slug . '_firma.png', $slug . '_firma_1.png', $slug . '_firma.jpg', $slug . '_firma_1.jpg'];
                    foreach ($patterns as $p) {
                        if (file_exists($firmas_dir . $p)) {
                            unlink($firmas_dir . $p);
                        }
                    }
                    // Limpiar BD
                    $stmt = $pdo_general->prepare("UPDATE instances SET firmante_1_firma_url = NULL WHERE id_instancia = :id");
                    $stmt->execute(['id' => $instance['id_instancia']]);
                    $mensaje = 'Firma 1 eliminada correctamente';
                } elseif ($num_firma === 2) {
                    // Eliminar archivo físico
                    $firmas_dir = __DIR__ . '/../../assets/images/firmas/';
                    $patterns = [$slug . '_firma_2.png', $slug . '_firma_2.jpg'];
                    foreach ($patterns as $p) {
                        if (file_exists($firmas_dir . $p)) {
                            unlink($firmas_dir . $p);
                        }
                    }
                    // Limpiar BD
                    $stmt = $pdo_general->prepare("UPDATE instances SET firmante_2_firma_url = NULL WHERE id_instancia = :id");
                    $stmt->execute(['id' => $instance['id_instancia']]);
                    $mensaje = 'Firma 2 eliminada correctamente';
                } else {
                    throw new Exception('Número de firma inválido');
                }

                $tipo_mensaje = 'success';
                $scroll_to = 'config-firmantes';
                $active_tab = 'configuracion';

                // Recargar config
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

            } catch (Exception $e) {
                $mensaje = 'Error al eliminar firma: ' . $e->getMessage();
                $tipo_mensaje = 'error';
                $active_tab = 'configuracion';
            }
            break;

        case 'actualizar_firmantes':
            try {
                $pdo_general = getGeneralConnection();

                // Preparar datos de firmantes
                $firmante_nombre = trim($_POST['firmante_nombre'] ?? '');
                $firmante_cargo = trim($_POST['firmante_cargo'] ?? '');
                $firmante_2_nombre = trim($_POST['firmante_2_nombre'] ?? '');
                $firmante_2_cargo = trim($_POST['firmante_2_cargo'] ?? '');

                // Procesar imagen firma 1
                $firmante_1_firma_url = $instance['firmante_1_firma_url'] ?? null;
                if (isset($_FILES['firmante_1_firma']) && $_FILES['firmante_1_firma']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../../assets/images/firmas/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $extension = pathinfo($_FILES['firmante_1_firma']['name'], PATHINFO_EXTENSION);
                    $filename = $slug . '_firma_1.' . $extension;
                    $filepath = $upload_dir . $filename;

                    if (move_uploaded_file($_FILES['firmante_1_firma']['tmp_name'], $filepath)) {
                        $firmante_1_firma_url = '/assets/images/firmas/' . $filename;
                    }
                }

                // Procesar imagen firma 2
                $firmante_2_firma_url = $instance['firmante_2_firma_url'] ?? null;
                if (isset($_FILES['firmante_2_firma']) && $_FILES['firmante_2_firma']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../../assets/images/firmas/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    $extension = pathinfo($_FILES['firmante_2_firma']['name'], PATHINFO_EXTENSION);
                    $filename = $slug . '_firma_2.' . $extension;
                    $filepath = $upload_dir . $filename;

                    if (move_uploaded_file($_FILES['firmante_2_firma']['tmp_name'], $filepath)) {
                        $firmante_2_firma_url = '/assets/images/firmas/' . $filename;
                    }
                }

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET firmante_nombre = :firmante_nombre,
                        firmante_cargo = :firmante_cargo,
                        firmante_1_firma_url = :firmante_1_firma_url,
                        firmante_2_nombre = :firmante_2_nombre,
                        firmante_2_cargo = :firmante_2_cargo,
                        firmante_2_firma_url = :firmante_2_firma_url
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'firmante_nombre' => $firmante_nombre ?: null,
                    'firmante_cargo' => $firmante_cargo ?: null,
                    'firmante_1_firma_url' => $firmante_1_firma_url,
                    'firmante_2_nombre' => $firmante_2_nombre ?: null,
                    'firmante_2_cargo' => $firmante_2_cargo ?: null,
                    'firmante_2_firma_url' => $firmante_2_firma_url,
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Limpiar cache y recargar
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

                $mensaje = 'Firmantes actualizados correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'config-firmantes';
                $active_tab = 'configuracion';

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar firmantes: ' . $e->getMessage();
                $tipo_mensaje = 'error';
                $active_tab = 'configuracion';
            }
            break;

        case 'actualizar_idiomas':
            try {
                $pdo_general = getGeneralConnection();

                // Obtener idioma predeterminado
                $idioma_default = $_POST['idioma_default'] ?? 'es_AR';

                // Obtener idiomas habilitados (checkbox array)
                $idiomas_habilitados_arr = $_POST['idiomas_habilitados'] ?? [];

                // Asegurar que el idioma por defecto esté en los habilitados
                if (!in_array($idioma_default, $idiomas_habilitados_arr)) {
                    $idiomas_habilitados_arr[] = $idioma_default;
                }

                // Convertir a string separado por comas
                $idiomas_habilitados = implode(',', $idiomas_habilitados_arr);

                $stmt = $pdo_general->prepare("
                    UPDATE instances
                    SET idioma_default = :idioma_default,
                        idiomas_habilitados = :idiomas_habilitados
                    WHERE id_instancia = :id_instancia
                ");

                $stmt->execute([
                    'idioma_default' => $idioma_default,
                    'idiomas_habilitados' => $idiomas_habilitados,
                    'id_instancia' => $instance['id_instancia']
                ]);

                // Limpiar cache y recargar
                InstitutionService::clearCache($slug);
                $instance = getInstanceConfig($slug);

                $mensaje = 'Configuración de idiomas actualizada correctamente';
                $tipo_mensaje = 'success';
                $scroll_to = 'config-idiomas';
                $active_tab = 'configuracion';

            } catch (PDOException $e) {
                $mensaje = 'Error al actualizar idiomas: ' . $e->getMessage();
                $tipo_mensaje = 'error';
                $active_tab = 'configuracion';
            }
            break;
    }
}
?>

<?php if ($mensaje): ?>
    <!-- Guardar mensaje para mostrar después de que los tabs se inicialicen -->
    <script>
        window.generalPageMessage = {
            mensaje: '<?php echo addslashes($mensaje); ?>',
            tipo: '<?php echo $tipo_mensaje; ?>',
            scrollTo: '<?php echo $scroll_to; ?>',
            activeTab: '<?php echo $active_tab; ?>'
        };
    </script>
<?php endif; ?>

<!-- Tabs de navegación de General (3 tabs) - Responsive -->
<div class="bg-white rounded-lg shadow-sm mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px overflow-x-auto scrollbar-hide">
            <button onclick="switchGeneralTab('institucion')" class="general-tab-button flex-1 sm:flex-none px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium border-b-2 <?php echo in_array($active_tab, ['institucion', 'informacion']) ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> transition whitespace-nowrap">
                <i data-lucide="building-2" class="w-4 h-4 sm:inline sm:mr-2 mx-auto sm:mx-0 block sm:inline-block mb-1 sm:mb-0"></i>
                <span class="block sm:inline">Institución</span>
            </button>
            <button onclick="switchGeneralTab('apariencia')" class="general-tab-button flex-1 sm:flex-none px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium border-b-2 <?php echo in_array($active_tab, ['apariencia', 'branding']) ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> transition whitespace-nowrap">
                <i data-lucide="palette" class="w-4 h-4 sm:inline sm:mr-2 mx-auto sm:mx-0 block sm:inline-block mb-1 sm:mb-0"></i>
                <span class="block sm:inline">Apariencia</span>
            </button>
            <button onclick="switchGeneralTab('configuracion')" class="general-tab-button flex-1 sm:flex-none px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium border-b-2 <?php echo in_array($active_tab, ['configuracion', 'modulos', 'firmantes']) ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?> transition whitespace-nowrap">
                <i data-lucide="settings" class="w-4 h-4 sm:inline sm:mr-2 mx-auto sm:mx-0 block sm:inline-block mb-1 sm:mb-0"></i>
                <span class="block sm:inline">Configuración</span>
            </button>
        </nav>
    </div>
</div>

<!-- Tab: Institución (nombre, logo, misión, redes) -->
<div id="general-tab-institucion" class="general-tab-content <?php echo in_array($active_tab, ['institucion', 'informacion']) ? 'active' : ''; ?>" style="display: <?php echo in_array($active_tab, ['institucion', 'informacion']) ? 'block' : 'none'; ?>;">
<div class="space-y-6">

    <!-- DASHBOARD DE ESTADO -->
    <?php
    // Calcular estados para el dashboard
    $logo_ok = !empty($instance['logo_url']);
    $favicon_ok = !empty($instance['favicon_generado']) || $logo_ok; // Asumimos que si hay logo, puede generarse favicon
    $colores_personalizados = ($instance['color_primario'] ?? '#2E7D32') !== '#2E7D32' ||
                               ($instance['color_secundario'] ?? '#1B5E20') !== '#1B5E20';
    $modulos_activos = (($instance['identitas_activo'] ?? 1) ? 1 : 0) +
                       (($instance['modulo_certificatum'] ?? 0) ? 1 : 0);
    $modulos_total = 2;
    $firmante_ok = !empty($instance['firmante_nombre']) && !empty($instance['firmante_cargo']);
    ?>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">
                <i data-lucide="layout-dashboard" class="w-5 h-5 inline mr-2"></i>
                Estado de Configuración
            </h2>
            <span class="text-xs text-gray-500">Resumen rápido</span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Logo -->
            <div class="p-4 rounded-lg border-2 <?php echo $logo_ok ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50'; ?>">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="<?php echo $logo_ok ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5 <?php echo $logo_ok ? 'text-green-600' : 'text-amber-600'; ?>"></i>
                    <span class="text-sm font-medium text-gray-700">Logo</span>
                </div>
                <p class="text-xs <?php echo $logo_ok ? 'text-green-700' : 'text-amber-700'; ?>">
                    <?php echo $logo_ok ? 'Configurado' : 'Pendiente'; ?>
                </p>
            </div>

            <!-- Favicon -->
            <div class="p-4 rounded-lg border-2 <?php echo $favicon_ok ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50'; ?>">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="<?php echo $favicon_ok ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5 <?php echo $favicon_ok ? 'text-green-600' : 'text-amber-600'; ?>"></i>
                    <span class="text-sm font-medium text-gray-700">Favicon</span>
                </div>
                <p class="text-xs <?php echo $favicon_ok ? 'text-green-700' : 'text-amber-700'; ?>">
                    <?php echo $favicon_ok ? 'Listo' : 'Sin logo'; ?>
                </p>
            </div>

            <!-- Colores -->
            <div class="p-4 rounded-lg border-2 <?php echo $colores_personalizados ? 'border-green-200 bg-green-50' : 'border-blue-200 bg-blue-50'; ?>">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="palette" class="w-5 h-5 <?php echo $colores_personalizados ? 'text-green-600' : 'text-blue-600'; ?>"></i>
                    <span class="text-sm font-medium text-gray-700">Colores</span>
                </div>
                <p class="text-xs <?php echo $colores_personalizados ? 'text-green-700' : 'text-blue-700'; ?>">
                    <?php echo $colores_personalizados ? 'Personalizados' : 'Por defecto'; ?>
                </p>
            </div>

            <!-- Módulos -->
            <div class="p-4 rounded-lg border-2 border-blue-200 bg-blue-50">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="package" class="w-5 h-5 text-blue-600"></i>
                    <span class="text-sm font-medium text-gray-700">Módulos</span>
                </div>
                <p class="text-xs text-blue-700">
                    <?php echo $modulos_activos; ?> de <?php echo $modulos_total; ?> activos
                </p>
            </div>

            <!-- Firmante -->
            <div class="p-4 rounded-lg border-2 <?php echo $firmante_ok ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50'; ?>">
                <div class="flex items-center gap-2 mb-2">
                    <i data-lucide="<?php echo $firmante_ok ? 'check-circle' : 'alert-circle'; ?>" class="w-5 h-5 <?php echo $firmante_ok ? 'text-green-600' : 'text-amber-600'; ?>"></i>
                    <span class="text-sm font-medium text-gray-700">Firmante</span>
                </div>
                <p class="text-xs <?php echo $firmante_ok ? 'text-green-700' : 'text-amber-700'; ?>">
                    <?php echo $firmante_ok ? 'Configurado' : 'Pendiente'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN INSTITUCIONAL -->
    <div id="info-institucional" class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            <i data-lucide="building" class="w-7 h-7 inline mr-2"></i>
            Información Institucional
        </h2>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-2">Información básica de tu institución</p>
                    <p>Esta información se utiliza en TODOS los módulos y soluciones de VERUMax (Identitas, Certificatum, etc.)</p>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_informacion">
            <input type="hidden" name="active_tab" value="institucion">

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre corto</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($instance['nombre']); ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Ej: SAJuR, Liberté</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre completo</label>
                    <input type="text" name="nombre_completo" value="<?php echo htmlspecialchars($instance['nombre_completo']); ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Nombre completo de la institución</p>
                </div>
            </div>

            <!-- Sección Colapsable: Información Adicional -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" onclick="toggleSection('info-adicional')"
                        class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 flex items-center justify-between text-left transition">
                    <span class="font-medium text-gray-700 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                        Información Adicional
                        <span class="text-xs text-gray-500 font-normal">(Misión, sitio web, email)</span>
                    </span>
                    <i data-lucide="chevron-down" id="chevron-info-adicional" class="w-5 h-5 text-gray-500 transition-transform"></i>
                </button>
                <div id="section-info-adicional" class="p-4 space-y-4 hidden">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Misión / Descripción (opcional)</label>
                            <?php if ($instance['ia_habilitada'] ?? 0): ?>
                                <button type="button"
                                        onclick="autocompletarConIA('mision', 'mision', 'Misión / Descripción', 'textarea', 'Información')"
                                        class="px-2 py-1 text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 rounded border border-purple-300 transition"
                                        title="Generar con Inteligencia Artificial">
                                    <i data-lucide="sparkles" class="w-3 h-3 inline"></i> IA
                                </button>
                            <?php endif; ?>
                        </div>
                        <textarea id="mision" name="mision" rows="3"
                                  placeholder="Descripción breve de la institución que aparece en el footer..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($instance['mision'] ?? ''); ?></textarea>
                        <p class="mt-1 text-sm text-gray-500">Se muestra en el footer del sitio (máx. 200 caracteres se mostrarán)</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sitio web oficial (opcional)</label>
                            <input type="text" name="sitio_web_oficial" value="<?php echo htmlspecialchars($instance['sitio_web_oficial'] ?? ''); ?>"
                                   placeholder="www.ejemplo.com"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Se agregará https:// automáticamente si no lo incluís</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email de contacto (opcional)</label>
                            <input type="email" name="email_contacto" value="<?php echo htmlspecialchars($instance['email_contacto'] ?? ''); ?>"
                                   placeholder="contacto@ejemplo.com"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección Colapsable: Redes Sociales -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" onclick="toggleSection('redes-sociales')"
                        class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 flex items-center justify-between text-left transition">
                    <span class="font-medium text-gray-700 flex items-center gap-2">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                        Redes Sociales
                        <span class="text-xs text-gray-500 font-normal">(Instagram, Facebook, etc.)</span>
                    </span>
                    <i data-lucide="chevron-down" id="chevron-redes-sociales" class="w-5 h-5 text-gray-500 transition-transform"></i>
                </button>
                <div id="section-redes-sociales" class="p-4 hidden">

            <?php
            $redes = json_decode($instance['redes_sociales'] ?? '{}', true);
            ?>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i data-lucide="instagram" class="w-4 h-4 inline mr-1"></i>
                        Instagram
                    </label>
                    <input type="text" name="instagram" value="<?php echo htmlspecialchars($redes['instagram'] ?? ''); ?>"
                           placeholder="@usuario o URL completa"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i data-lucide="facebook" class="w-4 h-4 inline mr-1"></i>
                        Facebook
                    </label>
                    <input type="text" name="facebook" value="<?php echo htmlspecialchars($redes['facebook'] ?? ''); ?>"
                           placeholder="URL completa"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i data-lucide="linkedin" class="w-4 h-4 inline mr-1"></i>
                        LinkedIn
                    </label>
                    <input type="text" name="linkedin" value="<?php echo htmlspecialchars($redes['linkedin'] ?? ''); ?>"
                           placeholder="URL completa"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i data-lucide="message-circle" class="w-4 h-4 inline mr-1"></i>
                        WhatsApp
                    </label>
                    <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($redes['whatsapp'] ?? ''); ?>"
                           placeholder="Número con código de país (ej: +54911...)"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i data-lucide="twitter" class="w-4 h-4 inline mr-1"></i>
                        Twitter/X
                    </label>
                    <input type="text" name="twitter" value="<?php echo htmlspecialchars($redes['twitter'] ?? ''); ?>"
                           placeholder="@usuario o URL completa"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i data-lucide="youtube" class="w-4 h-4 inline mr-1"></i>
                        YouTube
                    </label>
                    <input type="text" name="youtube" value="<?php echo htmlspecialchars($redes['youtube'] ?? ''); ?>"
                           placeholder="URL del canal"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Información
                </button>
            </div>
        </form>
    </div>
</div>
</div>

<!-- Tab: Apariencia (logo, colores, tema) -->
<div id="general-tab-apariencia" class="general-tab-content <?php echo in_array($active_tab, ['apariencia', 'branding']) ? 'active' : ''; ?>" style="display: <?php echo in_array($active_tab, ['apariencia', 'branding']) ? 'block' : 'none'; ?>;">
<div class="space-y-6">
    <!-- LOGO Y FAVICON INSTITUCIONAL -->
    <div id="logo-favicon" class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                <i data-lucide="image" class="w-7 h-7 inline mr-2"></i>
                Logo y Favicon Institucional
            </h2>
            <button onclick="mostrarAyuda('logo-favicon')" class="text-blue-600 hover:text-blue-800">
                <i data-lucide="help-circle" class="w-6 h-6"></i>
            </button>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-2">Logo institucional global</p>
                    <p>El logo se mostrará en todas las páginas y módulos. También se usa para generar automáticamente el favicon del sitio.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_logo">

            <!-- Upload de archivo (principal) -->
            <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                <div class="flex items-start gap-6">
                    <!-- Preview del logo -->
                    <div class="flex-shrink-0">
                        <div id="logo-preview-container" class="w-24 h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-white overflow-hidden">
                            <?php if (!empty($instance['logo_url'])): ?>
                                <img id="logo-preview" src="<?php echo htmlspecialchars($instance['logo_url']); ?>" alt="Logo" class="max-w-full max-h-full object-contain">
                            <?php else: ?>
                                <div id="logo-placeholder" class="text-center p-2">
                                    <i data-lucide="image" class="w-8 h-8 text-gray-400 mx-auto"></i>
                                    <span class="text-xs text-gray-400 mt-1 block">Sin logo</span>
                                </div>
                                <img id="logo-preview" src="" alt="Logo" class="max-w-full max-h-full object-contain hidden">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Campos de upload -->
                    <div class="flex-grow space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i data-lucide="upload" class="w-4 h-4 inline mr-1"></i>
                                Subir logo
                            </label>
                            <input type="file" id="logo_file" name="logo_file" accept="image/png,image/jpeg,image/jpg,image/svg+xml"
                                   onchange="previewLogoFile(this)"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                            <p class="mt-1 text-sm text-gray-500">PNG, JPG o SVG • Máximo 5MB</p>
                        </div>

                        <!-- Link expandible para URL -->
                        <div>
                            <button type="button" onclick="toggleLogoUrl()" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                <i data-lucide="link" class="w-4 h-4"></i>
                                <span id="toggle-url-text">¿Tienes una URL externa?</span>
                                <i data-lucide="chevron-down" id="chevron-logo-url" class="w-4 h-4 transition-transform"></i>
                            </button>

                            <div id="logo-url-section" class="hidden mt-3">
                                <input type="text" name="logo_url" id="logo_url_input"
                                       value="<?php echo htmlspecialchars($instance['logo_url'] ?? ''); ?>"
                                       placeholder="https://ejemplo.com/logo.png"
                                       onchange="previewLogoUrl(this.value)"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-sm text-gray-500">URL completa o ruta relativa al logo</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estilo de visualización (compacto) -->
            <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                <div class="flex items-center gap-6">
                    <!-- Preview del estilo -->
                    <div class="flex-shrink-0">
                        <div id="estilo-preview-container" class="w-20 h-20 border-2 border-gray-300 bg-white flex items-center justify-center transition-all duration-300
                            <?php
                            $estilo = $instance['logo_estilo'] ?? 'rectangular';
                            echo match($estilo) {
                                'rectangular-rounded' => 'rounded-lg',
                                'cuadrado' => '',
                                'cuadrado-rounded' => 'rounded-lg',
                                'circular' => 'rounded-full',
                                default => ''
                            };
                            ?>">
                            <?php if (!empty($instance['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($instance['logo_url']); ?>" alt="Preview" class="max-w-full max-h-full object-contain p-1">
                            <?php else: ?>
                                <div class="w-12 h-8 bg-gray-300"></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dropdown de estilo -->
                    <div class="flex-grow">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i data-lucide="layout" class="w-4 h-4 inline mr-1"></i>
                            Estilo del logo
                        </label>
                        <select name="logo_estilo" id="logo_estilo_select" onchange="updateEstiloPreview(this.value)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="rectangular" <?php echo (!isset($instance['logo_estilo']) || $instance['logo_estilo'] === 'rectangular') ? 'selected' : ''; ?>>
                                Rectangular (sin redondeo)
                            </option>
                            <option value="rectangular-rounded" <?php echo (isset($instance['logo_estilo']) && $instance['logo_estilo'] === 'rectangular-rounded') ? 'selected' : ''; ?>>
                                Rectangular (con redondeo)
                            </option>
                            <option value="cuadrado" <?php echo (isset($instance['logo_estilo']) && $instance['logo_estilo'] === 'cuadrado') ? 'selected' : ''; ?>>
                                Cuadrado (sin redondeo)
                            </option>
                            <option value="cuadrado-rounded" <?php echo (isset($instance['logo_estilo']) && $instance['logo_estilo'] === 'cuadrado-rounded') ? 'selected' : ''; ?>>
                                Cuadrado (con redondeo)
                            </option>
                            <option value="circular" <?php echo (isset($instance['logo_estilo']) && $instance['logo_estilo'] === 'circular') ? 'selected' : ''; ?>>
                                Circular (redondo)
                            </option>
                        </select>
                        <p class="mt-2 text-sm text-gray-500">Se aplica en todas las páginas</p>
                    </div>
                </div>
            </div>

            <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                <h3 class="font-semibold text-gray-900 mb-4">Texto junto al Logo</h3>

                <div class="flex items-start gap-3">
                    <input type="checkbox" name="logo_mostrar_texto" id="logo_mostrar_texto"
                           <?php echo (!isset($instance['logo_mostrar_texto']) || $instance['logo_mostrar_texto'] == 1) ? 'checked' : ''; ?>
                           class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <div>
                        <label for="logo_mostrar_texto" class="block text-sm font-medium text-gray-700 cursor-pointer">
                            Mostrar nombre de la institución al lado del logo
                        </label>
                        <p class="mt-1 text-sm text-gray-500">
                            Si está marcado, se mostrará el nombre al lado del logo en el header. Si no, solo se mostrará el logo.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Logo
                </button>
            </div>
        </form>

        <!-- FAVICON -->
        <?php if (!empty($instance['logo_url'])): ?>
            <hr class="my-6 border-gray-200">

            <h3 class="text-xl font-bold text-gray-900 mb-4">
                <i data-lucide="star" class="w-6 h-6 inline mr-2"></i>
                Favicon del Sitio
            </h3>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-green-600 mt-0.5"></i>
                    <div class="text-sm text-green-800">
                        <p class="font-semibold mb-2">El favicon es el pequeño icono que aparece en la pestaña del navegador</p>
                        <p>Se genera automáticamente desde tu logo en todos los tamaños necesarios (16x16, 32x32, 96x96, 180x180, 192x192).</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-start gap-6">
                    <?php if (!empty($instance['favicon_url'])): ?>
                        <?php
                        // Construir URL absoluta del favicon (funciona desde cualquier dominio)
                        $favicon_preview_url = 'https://' . $slug . '.verumax.com' . $instance['favicon_url'];
                        ?>
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Favicon generado:</p>
                            <div class="flex items-center gap-4">
                                <img src="<?php echo htmlspecialchars($favicon_preview_url); ?>?v=<?php echo time(); ?>"
                                     alt="Favicon" class="h-16 w-16 border border-gray-300 bg-gray-100 p-1">
                                <div class="text-sm text-gray-600">
                                    <p class="font-medium">✅ Favicon activo</p>
                                    <?php if (!empty($instance['favicon_generated_at'])): ?>
                                        <p class="text-xs mt-1">
                                            Generado: <?php echo date('d/m/Y H:i', strtotime($instance['favicon_generated_at'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-gray-500 italic">
                            Aún no se ha generado un favicon
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Botones -->
                <div class="mt-6 flex gap-3">
                    <form method="POST" action="?modulo=general" class="inline">
                        <input type="hidden" name="accion" value="generar_favicon">
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            <?php echo !empty($instance['favicon_url']) ? 'Regenerar Favicon' : 'Generar Favicon'; ?>
                        </button>
                    </form>

                    <?php if (!empty($instance['favicon_url'])): ?>
                        <form method="POST" action="?modulo=general" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar el favicon?');">
                            <input type="hidden" name="accion" value="eliminar_favicon">
                            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                Eliminar Favicon
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (!empty($instance['favicon_url'])): ?>
                    <div class="mt-6 p-4 bg-gray-50 rounded border border-gray-200">
                        <p class="text-sm font-medium text-gray-700 mb-2">Archivos generados:</p>
                        <ul class="text-xs text-gray-600 space-y-1">
                            <li>• favicon-16x16.png (para navegadores antiguos)</li>
                            <li>• favicon-32x32.png (para navegadores modernos)</li>
                            <li>• favicon-96x96.png (para alta resolución)</li>
                            <li>• apple-touch-icon.png (para iOS/iPhone/iPad)</li>
                            <li>• android-chrome-192x192.png (para Android)</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <hr class="my-6 border-gray-200">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-yellow-600"></i>
                    <div>
                        <p class="font-semibold text-yellow-800">Primero debes subir un logo</p>
                        <p class="text-sm text-yellow-700 mt-1">
                            Sube un logo arriba y luego podrás generar el favicon automáticamente.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- SITIO EN CONSTRUCCIÓN -->
    <div id="sitio-construccion" class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                <i data-lucide="hard-hat" class="w-7 h-7 inline mr-2"></i>
                Sitio en Construcción
            </h2>
            <button onclick="mostrarAyuda('construccion')" class="text-blue-600 hover:text-blue-800">
                <i data-lucide="help-circle" class="w-6 h-6"></i>
            </button>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-orange-600 mt-0.5"></i>
                <div class="text-sm text-orange-800">
                    <p class="font-semibold mb-2">Modo "En Construcción"</p>
                    <p>Cuando actives este modo, los visitantes verán una página informando que el sitio está en construcción, pero tú podrás seguir trabajando en el admin.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_construccion">

            <div>
                <label class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="sitio_en_construccion" value="1"
                           <?php echo ($instance['sitio_en_construccion'] ?? 0) ? 'checked' : ''; ?>
                           class="w-5 h-5 text-orange-600 rounded">
                    <div>
                        <div class="font-semibold text-gray-900">
                            Activar modo "En Construcción"
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            El sitio público mostrará una página indicando que está en desarrollo
                        </div>
                    </div>
                </label>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Mensaje personalizado (opcional)</label>
                    <?php if ($instance['ia_habilitada'] ?? 0): ?>
                        <button type="button"
                                onclick="autocompletarConIA('mensaje_construccion', 'mensaje_construccion', 'Mensaje de sitio en construcción', 'textarea', 'Branding')"
                                class="px-2 py-1 text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 rounded border border-purple-300 transition"
                                title="Generar con Inteligencia Artificial">
                            <i data-lucide="sparkles" class="w-3 h-3 inline"></i> IA
                        </button>
                    <?php endif; ?>
                </div>
                <textarea id="mensaje_construccion" name="mensaje_construccion" rows="3"
                          placeholder="Ej: Estamos trabajando en mejorar tu experiencia. Pronto volveremos con novedades."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($instance['mensaje_construccion'] ?? ''); ?></textarea>
                <p class="mt-1 text-sm text-gray-500">Si lo dejas vacío, se mostrará un mensaje predeterminado</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Modo Construcción
                </button>
            </div>
        </form>
    </div>

    <!-- OPTIMIZACIÓN SEO -->
    <div id="optimizacion-seo" class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                <i data-lucide="search" class="w-7 h-7 inline mr-2"></i>
                Optimización SEO
            </h2>
            <?php if ($instance['ia_habilitada'] ?? 0): ?>
                <button type="button"
                        onclick="generarCamposSEOConIA()"
                        class="px-3 py-2 text-sm bg-purple-100 hover:bg-purple-200 text-purple-700 rounded border border-purple-300 transition font-semibold"
                        title="Generar todos los campos SEO con IA">
                    <i data-lucide="sparkles" class="w-4 h-4 inline"></i> IA (grupo)
                </button>
            <?php endif; ?>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-2">Configura cómo aparece tu sitio en Google y redes sociales</p>
                    <ul class="space-y-1 ml-4">
                        <li><strong>Título SEO:</strong> Aparece en la pestaña del navegador y resultados de Google (máx. 70 caracteres)</li>
                        <li><strong>Descripción:</strong> Texto que se muestra debajo del título en Google (máx. 160 caracteres)</li>
                        <li><strong>Palabras clave:</strong> Términos que describen tu sitio (separados por comas)</li>
                    </ul>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" class="space-y-6" id="form-seo">
            <input type="hidden" name="accion" value="actualizar_seo">
            <input type="hidden" name="active_tab" value="apariencia">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Título SEO
                    <span class="text-gray-500 font-normal">(Opcional - Máx. 70 caracteres)</span>
                </label>
                <input type="text" id="seo_title" name="seo_title"
                       value="<?php echo htmlspecialchars($instance['seo_title'] ?? ''); ?>"
                       maxlength="70"
                       placeholder="Ej: SAJuR - Sociedad Argentina de Justicia Restaurativa"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <p class="mt-1 text-sm text-gray-500">
                    Si se deja vacío, se genera automáticamente desde la misión o nombre
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    <span id="seo-title-count">0</span>/70 caracteres
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Descripción SEO
                    <span class="text-gray-500 font-normal">(Opcional - Máx. 160 caracteres)</span>
                </label>
                <textarea id="seo_description" name="seo_description" rows="3"
                          maxlength="160"
                          placeholder="Texto que se muestra debajo del título en Google"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($instance['seo_description'] ?? ''); ?></textarea>
                <p class="mt-1 text-sm text-gray-500">
                    Si se deja vacío, se genera automáticamente desde la misión o nombre
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    <span id="seo-desc-count">0</span>/160 caracteres
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Palabras Clave
                    <span class="text-gray-500 font-normal">(Opcional - Separadas por comas)</span>
                </label>
                <input type="text" id="seo_keywords" name="seo_keywords"
                       value="<?php echo htmlspecialchars($instance['seo_keywords'] ?? ''); ?>"
                       placeholder="Ej: SAJuR, justicia restaurativa, educación, certificados, Argentina"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <p class="mt-1 text-sm text-gray-500">
                    Ayudan a categorizar el sitio (aunque Google no las usa tanto como antes)
                </p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Vista previa en Google:</p>
                <div class="bg-white p-4 rounded border">
                    <h3 class="text-blue-600 text-lg hover:underline cursor-pointer">
                        <?php
                        $preview_title = $instance['seo_title'] ?? $instance['nombre'] ?? 'Tu Institución';
                        echo htmlspecialchars($preview_title);
                        ?>
                    </h3>
                    <p class="text-green-700 text-sm">https://<?php echo $slug; ?>.verumax.com</p>
                    <p class="text-gray-600 text-sm mt-1">
                        <?php
                        $preview_desc = $instance['seo_description'] ?? $instance['mision'] ?? 'Descripción de tu institución';
                        echo htmlspecialchars(substr($preview_desc, 0, 160));
                        ?>
                    </p>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" id="btn-guardar-seo"
                        class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors inline-flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar SEO
                </button>
            </div>
        </form>
    </div>

    <script>
        // Contador de caracteres para SEO
        const seoTitleInput = document.querySelector('input[name="seo_title"]');
        const seoDescInput = document.querySelector('textarea[name="seo_description"]');
        const seoKeywordsInput = document.querySelector('input[name="seo_keywords"]');
        const btnGuardarSeo = document.getElementById('btn-guardar-seo');
        const formSeo = document.getElementById('form-seo');

        if (seoTitleInput) {
            const updateTitleCount = () => {
                document.getElementById('seo-title-count').textContent = seoTitleInput.value.length;
            };
            updateTitleCount();
            seoTitleInput.addEventListener('input', updateTitleCount);
        }

        if (seoDescInput) {
            const updateDescCount = () => {
                document.getElementById('seo-desc-count').textContent = seoDescInput.value.length;
            };
            updateDescCount();
            seoDescInput.addEventListener('input', updateDescCount);
        }

        // Detección de cambios en formulario SEO
        if (formSeo && btnGuardarSeo) {
            // Capturar valores iniciales
            const initialValues = {
                seo_title: seoTitleInput ? seoTitleInput.value : '',
                seo_description: seoDescInput ? seoDescInput.value : '',
                seo_keywords: seoKeywordsInput ? seoKeywordsInput.value : ''
            };

            // Función para detectar si hay cambios
            const checkSeoChanges = () => {
                const hasChanges =
                    (seoTitleInput && seoTitleInput.value !== initialValues.seo_title) ||
                    (seoDescInput && seoDescInput.value !== initialValues.seo_description) ||
                    (seoKeywordsInput && seoKeywordsInput.value !== initialValues.seo_keywords);

                btnGuardarSeo.disabled = !hasChanges;
            };

            // Agregar listeners a todos los campos
            if (seoTitleInput) seoTitleInput.addEventListener('input', checkSeoChanges);
            if (seoDescInput) seoDescInput.addEventListener('input', checkSeoChanges);
            if (seoKeywordsInput) seoKeywordsInput.addEventListener('input', checkSeoChanges);
        }
    </script>

    <!-- CONTROL DE INDEXACIÓN (ROBOTS) -->
    <div id="seo-robots" class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                <i data-lucide="search" class="w-7 h-7 inline mr-2"></i>
                Control de Indexación (SEO)
            </h2>
            <button onclick="mostrarAyuda('robots')" class="text-blue-600 hover:text-blue-800">
                <i data-lucide="help-circle" class="w-6 h-6"></i>
            </button>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-purple-600 mt-0.5"></i>
                <div class="text-sm text-purple-800">
                    <p class="font-semibold mb-2">Control de buscadores (Google, Bing, etc.)</p>
                    <p><strong>Por seguridad, por defecto el sitio NO se indexa</strong> hasta que estés listo para hacerlo público.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_seo_robots">

            <div>
                <label class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="robots_noindex" value="1"
                           <?php echo ($instance['robots_noindex'] ?? 1) ? 'checked' : ''; ?>
                           class="w-5 h-5 text-purple-600 rounded">
                    <div>
                        <div class="font-semibold text-gray-900">
                            No permitir indexación (noindex)
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            ⚠️ Recomendado mientras el sitio está en desarrollo. Los buscadores NO indexarán el sitio.
                        </div>
                    </div>
                </label>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <p class="text-sm font-medium text-gray-700 mb-2">¿Cuándo desmarcar esta opción?</p>
                <ul class="text-sm text-gray-600 space-y-1 ml-4">
                    <li>✅ Cuando el contenido del sitio esté completo</li>
                    <li>✅ Cuando hayas configurado el SEO correctamente</li>
                    <li>✅ Cuando estés listo para que Google encuentre tu sitio</li>
                </ul>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Indexación
                </button>
            </div>
        </form>
    </div>

    <!-- PALETA DE COLORES GLOBAL -->
    <div id="paleta-colores" class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            <i data-lucide="palette" class="w-7 h-7 inline mr-2"></i>
            Paleta de Colores Global
        </h2>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-2">Configuración de colores institucionales</p>
                    <p>Esta paleta de colores se aplica automáticamente a <strong>TODOS los módulos</strong> (Identitas, Certificatum, y futuros módulos). Es el branding visual de tu institución.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_colores">
            <input type="hidden" name="active_tab" value="apariencia">

            <!-- Botones visuales de paletas -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Selección rápida de paleta</label>
                <div class="flex flex-wrap gap-3">
                    <button type="button" onclick="aplicarPaleta('verde-elegante')" title="Verde Elegante"
                            class="paleta-btn group flex items-center gap-2 px-3 py-2 border-2 rounded-lg transition hover:border-gray-400 <?php echo ($instance['paleta_colores'] ?? 'verde-elegante') === 'verde-elegante' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                        <div class="flex -space-x-1">
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #2E7D32;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #1B5E20;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #66BB6A;"></div>
                        </div>
                        <span class="text-xs text-gray-600 group-hover:text-gray-900">Verde</span>
                    </button>
                    <button type="button" onclick="aplicarPaleta('azul-profesional')" title="Azul Profesional"
                            class="paleta-btn group flex items-center gap-2 px-3 py-2 border-2 rounded-lg transition hover:border-gray-400 <?php echo ($instance['paleta_colores'] ?? '') === 'azul-profesional' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                        <div class="flex -space-x-1">
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #1976D2;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #0D47A1;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #42A5F5;"></div>
                        </div>
                        <span class="text-xs text-gray-600 group-hover:text-gray-900">Azul</span>
                    </button>
                    <button type="button" onclick="aplicarPaleta('morado-creativo')" title="Morado Creativo"
                            class="paleta-btn group flex items-center gap-2 px-3 py-2 border-2 rounded-lg transition hover:border-gray-400 <?php echo ($instance['paleta_colores'] ?? '') === 'morado-creativo' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                        <div class="flex -space-x-1">
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #7B1FA2;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #4A148C;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #BA68C8;"></div>
                        </div>
                        <span class="text-xs text-gray-600 group-hover:text-gray-900">Morado</span>
                    </button>
                    <button type="button" onclick="aplicarPaleta('naranja-energetico')" title="Naranja Energético"
                            class="paleta-btn group flex items-center gap-2 px-3 py-2 border-2 rounded-lg transition hover:border-gray-400 <?php echo ($instance['paleta_colores'] ?? '') === 'naranja-energetico' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                        <div class="flex -space-x-1">
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #F57C00;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #E65100;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #FFB74D;"></div>
                        </div>
                        <span class="text-xs text-gray-600 group-hover:text-gray-900">Naranja</span>
                    </button>
                    <button type="button" onclick="aplicarPaleta('rojo-institucional')" title="Rojo Institucional"
                            class="paleta-btn group flex items-center gap-2 px-3 py-2 border-2 rounded-lg transition hover:border-gray-400 <?php echo ($instance['paleta_colores'] ?? '') === 'rojo-institucional' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                        <div class="flex -space-x-1">
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #C62828;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #B71C1C;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #EF5350;"></div>
                        </div>
                        <span class="text-xs text-gray-600 group-hover:text-gray-900">Rojo</span>
                    </button>
                    <button type="button" onclick="aplicarPaleta('gris-minimalista')" title="Gris Minimalista"
                            class="paleta-btn group flex items-center gap-2 px-3 py-2 border-2 rounded-lg transition hover:border-gray-400 <?php echo ($instance['paleta_colores'] ?? '') === 'gris-minimalista' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'; ?>">
                        <div class="flex -space-x-1">
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #424242;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #212121;"></div>
                            <div class="w-6 h-6 rounded-full border-2 border-white shadow" style="background: #757575;"></div>
                        </div>
                        <span class="text-xs text-gray-600 group-hover:text-gray-900">Gris</span>
                    </button>
                </div>
            </div>

            <!-- Dropdown oculto para compatibilidad -->
            <input type="hidden" name="paleta_colores" id="paleta-select" value="<?php echo htmlspecialchars($instance['paleta_colores'] ?? 'verde-elegante'); ?>">

            <!-- Tema por defecto -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tema por defecto</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 px-4 py-3 border rounded-lg cursor-pointer transition-all <?php echo ($instance['tema_default'] ?? 'dark') === 'light' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'; ?>">
                        <input type="radio" name="tema_default" value="light"
                               <?php echo ($instance['tema_default'] ?? 'dark') === 'light' ? 'checked' : ''; ?>
                               class="w-4 h-4 text-blue-600"
                               onchange="this.closest('.flex').querySelectorAll('label').forEach(l => l.classList.remove('border-blue-500', 'bg-blue-50')); this.closest('label').classList.add('border-blue-500', 'bg-blue-50');">
                        <i data-lucide="sun" class="w-5 h-5 text-amber-500"></i>
                        <span>Modo Claro</span>
                    </label>
                    <label class="flex items-center gap-2 px-4 py-3 border rounded-lg cursor-pointer transition-all <?php echo ($instance['tema_default'] ?? 'dark') === 'dark' ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'; ?>">
                        <input type="radio" name="tema_default" value="dark"
                               <?php echo ($instance['tema_default'] ?? 'dark') === 'dark' ? 'checked' : ''; ?>
                               class="w-4 h-4 text-blue-600"
                               onchange="this.closest('.flex').querySelectorAll('label').forEach(l => l.classList.remove('border-blue-500', 'bg-blue-50')); this.closest('label').classList.add('border-blue-500', 'bg-blue-50');">
                        <i data-lucide="moon" class="w-5 h-5 text-indigo-500"></i>
                        <span>Modo Oscuro</span>
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">El tema que verán los usuarios en su primera visita. Si el usuario cambia el tema, se respeta su preferencia.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color Primario</label>
                    <div class="flex gap-2">
                        <input type="color" name="color_primario" id="color-primario"
                               value="<?php echo htmlspecialchars($instance['color_primario'] ?? '#2E7D32'); ?>"
                               class="h-10 w-20 border border-gray-300 rounded-lg">
                        <input type="text" id="color-primario-text"
                               value="<?php echo htmlspecialchars($instance['color_primario'] ?? '#2E7D32'); ?>" readonly
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color Secundario</label>
                    <div class="flex gap-2">
                        <input type="color" name="color_secundario" id="color-secundario"
                               value="<?php echo htmlspecialchars($instance['color_secundario'] ?? '#1B5E20'); ?>"
                               class="h-10 w-20 border border-gray-300 rounded-lg">
                        <input type="text" id="color-secundario-text"
                               value="<?php echo htmlspecialchars($instance['color_secundario'] ?? '#1B5E20'); ?>" readonly
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color de Acento</label>
                    <div class="flex gap-2">
                        <input type="color" name="color_acento" id="color-acento"
                               value="<?php echo htmlspecialchars($instance['color_acento'] ?? '#66BB6A'); ?>"
                               class="h-10 w-20 border border-gray-300 rounded-lg">
                        <input type="text" id="color-acento-text"
                               value="<?php echo htmlspecialchars($instance['color_acento'] ?? '#66BB6A'); ?>" readonly
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>
            </div>

            <!-- Preview del Header en tiempo real -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                    <span class="text-sm font-medium text-gray-600">Vista previa del header</span>
                </div>
                <div id="header-preview" class="p-4" style="background: linear-gradient(135deg, <?php echo htmlspecialchars($instance['color_primario'] ?? '#2E7D32'); ?> 0%, <?php echo htmlspecialchars($instance['color_secundario'] ?? '#1B5E20'); ?> 100%);">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <?php if (!empty($instance['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($instance['logo_url']); ?>" alt="Logo" class="h-10 w-auto rounded">
                            <?php else: ?>
                                <div class="w-10 h-10 bg-white/20 rounded flex items-center justify-center">
                                    <i data-lucide="building-2" class="w-6 h-6 text-white"></i>
                                </div>
                            <?php endif; ?>
                            <span class="text-white font-semibold"><?php echo htmlspecialchars($instance['nombre'] ?? 'Nombre Institución'); ?></span>
                        </div>
                        <div class="flex gap-2">
                            <span id="preview-acento" class="px-3 py-1 rounded text-xs font-medium text-white" style="background: <?php echo htmlspecialchars($instance['color_acento'] ?? '#66BB6A'); ?>;">Acento</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Paleta de Colores
                </button>
            </div>
        </form>
    </div>
</div>
</div>

<!-- Tab: Configuración (módulos + firmantes) -->
<div id="general-tab-configuracion" class="general-tab-content <?php echo in_array($active_tab, ['configuracion', 'modulos', 'firmantes']) ? 'active' : ''; ?>" style="display: <?php echo in_array($active_tab, ['configuracion', 'modulos', 'firmantes']) ? 'block' : 'none'; ?>;">
<div class="space-y-6">
    <!-- MÓDULOS Y SU INTEGRACIÓN -->
    <div id="activacion-modulos" class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            <i data-lucide="settings" class="w-7 h-7 inline mr-2"></i>
            Módulos y su Integración
        </h2>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-semibold mb-2">Control de módulos de VERUMax</p>
                    <ul class="space-y-2 ml-4">
                        <li><strong>Identitas:</strong> Módulo base. Si se desactiva, el sitio mostrará "En construcción"</li>
                        <li><strong>Certificatum:</strong> Portal de certificados. Configurá cómo se integra en Identitas</li>
                    </ul>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_modulos">

            <!-- Identitas (módulo base) -->
            <div id="identitas-card" class="border-2 rounded-xl p-6 transition-all duration-300 <?php echo ($instance['identitas_activo'] ?? 1) ? 'border-blue-300 bg-blue-50/50' : 'border-gray-200 bg-gray-50'; ?>">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-lg <?php echo ($instance['identitas_activo'] ?? 1) ? 'bg-blue-100' : 'bg-gray-200'; ?> transition-colors">
                                <i data-lucide="layout" class="w-6 h-6 <?php echo ($instance['identitas_activo'] ?? 1) ? 'text-blue-600' : 'text-gray-400'; ?>"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Identitas</h3>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-medium">MÓDULO BASE</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-3 ml-12">
                            Presencia digital profesional. Tu sitio web institucional con diseño moderno y totalmente personalizable.
                        </p>

                        <!-- Advertencia al desactivar -->
                        <div class="hidden" id="identitas-warning">
                            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5"></i>
                                    <div class="text-sm">
                                        <p class="font-semibold text-yellow-800 mb-2">Al desactivar Identitas se perderán:</p>
                                        <ul class="text-yellow-700 space-y-1 ml-4 list-disc">
                                            <li>Páginas personalizables (Sobre nosotros, Misión, etc.)</li>
                                            <li>Formulario de contacto</li>
                                            <li>One-page institucional completo</li>
                                        </ul>
                                        <p class="mt-2 text-yellow-800 font-medium">
                                            ✅ Se mantendrán: Logo, colores, favicon, configuración general
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ml-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="identitas_activo" id="identitas-toggle"
                                   <?php echo ($instance['identitas_activo'] ?? 1) ? 'checked' : ''; ?>
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                        <p class="text-xs text-center mt-2 font-medium" id="identitas-status">
                            <?php echo ($instance['identitas_activo'] ?? 1) ? 'ACTIVO' : 'DESACTIVADO'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Certificatum -->
            <div id="certificatum-card" class="border-2 rounded-xl p-6 transition-all duration-300 <?php echo $instance['modulo_certificatum'] ? 'border-green-300 bg-green-50/50' : 'border-gray-200 bg-gray-50'; ?>">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-lg <?php echo $instance['modulo_certificatum'] ? 'bg-green-100' : 'bg-gray-200'; ?> transition-colors">
                                <i data-lucide="award" class="w-6 h-6 <?php echo $instance['modulo_certificatum'] ? 'text-green-600' : 'text-gray-400'; ?>"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Certificatum</h3>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded font-medium">INDEPENDIENTE</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-3 ml-12">
                            Portal de certificados y constancias. Puede funcionar independientemente de Identitas.
                        </p>

                        <?php if (!($instance['identitas_activo'] ?? 1) && $instance['modulo_certificatum']): ?>
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded mt-2">
                                <p class="text-sm text-blue-800">
                                    <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                                    <strong>Modo portal:</strong> El sitio mostrará solo el formulario de certificados
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="ml-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="modulo_certificatum" id="certificatum-toggle"
                                   <?php echo $instance['modulo_certificatum'] ? 'checked' : ''; ?>
                                   class="sr-only peer">
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                        <p class="text-xs text-center mt-2 font-medium" id="certificatum-status">
                            <?php echo $instance['modulo_certificatum'] ? 'ACTIVO' : 'DESACTIVADO'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Activación de Módulos
                </button>
            </div>
        </form>

        <!-- Configuración de Integración de Certificatum (solo si Identitas está activo) -->
        <?php if ($instance['modulo_certificatum'] && $instance['identitas_activo']): ?>
            <hr class="my-8 border-gray-200">

            <h3 id="config-certificatum" class="text-xl font-bold text-gray-900 mb-6">
                <i data-lucide="link" class="w-6 h-6 inline mr-2"></i>
                Configuración de Integración: Certificatum
            </h3>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-green-600 mt-0.5"></i>
                    <div class="text-sm text-green-800">
                        <p class="font-semibold mb-2">Elige cómo quieres integrar Certificatum en Identitas:</p>
                        <ul class="space-y-2 ml-4">
                            <li><strong>📄 Sección integrada:</strong> Se integra como sección dentro de la página principal (one-page)</li>
                            <li><strong>📋 Página independiente:</strong> Página separada con su propia URL pero mantiene el branding</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form method="POST" action="?modulo=general" class="space-y-6">
                <input type="hidden" name="accion" value="actualizar_integracion_certificatum">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modo de integración</label>
                    <select name="certificatum_modo" id="certificatum-modo" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="seccion" <?php echo ($instance['certificatum_modo'] ?? 'seccion') === 'seccion' ? 'selected' : ''; ?>>
                            📄 Sección integrada (recomendado)
                        </option>
                        <option value="pagina" <?php echo ($instance['certificatum_modo'] ?? '') === 'pagina' ? 'selected' : ''; ?>>
                            📋 Página independiente
                        </option>
                    </select>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Título en el menú</label>
                        <input type="text" name="certificatum_titulo"
                               value="<?php echo htmlspecialchars($instance['certificatum_titulo'] ?? 'Certificados'); ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Icono (Lucide)</label>
                        <input type="text" name="certificatum_icono"
                               value="<?php echo htmlspecialchars($instance['certificatum_icono'] ?? 'award'); ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">
                            <a href="https://lucide.dev/icons" target="_blank" class="text-blue-600 hover:underline">
                                Ver iconos disponibles
                            </a>
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Posición en menú</label>
                    <input type="number" name="certificatum_posicion"
                           value="<?php echo htmlspecialchars($instance['certificatum_posicion'] ?? '99'); ?>" required min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Orden en el menú de navegación (menor = más arriba)</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        Guardar Integración de Certificatum
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Configuración de Inteligencia Artificial -->
        <hr class="my-8 border-gray-200">

        <h3 id="config-openai" class="text-xl font-bold text-gray-900 mb-6">
            <i data-lucide="sparkles" class="w-6 h-6 inline mr-2"></i>
            Inteligencia Artificial
        </h3>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-purple-600 mt-0.5"></i>
                <div class="text-sm text-purple-800">
                    <p class="font-semibold mb-2">Autocompletado inteligente con IA</p>
                    <p>Al habilitar IA, podras usar inteligencia artificial para:</p>
                    <ul class="mt-2 space-y-1 ml-4">
                        <li>- Autocompletar campos de texto en Templates</li>
                        <li>- Generar descripciones de cursos en Certificatum</li>
                        <li>- Crear textos SEO automaticamente</li>
                        <li>- Generar preguntas frecuentes (FAQ)</li>
                    </ul>
                    <p class="mt-3 text-xs text-purple-600">
                        <i data-lucide="zap" class="w-3 h-3 inline"></i>
                        Este servicio esta incluido en tu plan de VERUMax.
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_openai">

            <div>
                <label class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="openai_habilitado" value="1"
                           <?php echo ($instance['ia_habilitada'] ?? 0) ? 'checked' : ''; ?>
                           class="w-5 h-5 text-purple-600 rounded">
                    <div>
                        <div class="font-semibold text-gray-900">
                            Habilitar Inteligencia Artificial
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            Activa las funciones de IA en todo el sistema VERUMax
                        </div>
                    </div>
                </label>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Configuracion IA
                </button>
            </div>
        </form>
    </div>

    <!-- CONFIGURACIÓN DE IDIOMAS -->
    <div id="config-idiomas" class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            <i data-lucide="languages" class="w-7 h-7 inline mr-2"></i>
            Configuración de Idiomas
        </h2>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">Idiomas de la plataforma</p>
                    <p>Esta configuración afecta a todos los módulos: Identitas, Certificatum, y cualquier otro módulo de VERUMax.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" class="space-y-6">
            <input type="hidden" name="accion" value="actualizar_idiomas">

            <!-- Idioma predeterminado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Idioma predeterminado</label>
                <select name="idioma_default" class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="es_AR" <?php echo ($instance['idioma_default'] ?? 'es_AR') === 'es_AR' ? 'selected' : ''; ?>>
                        🇦🇷 Español (Argentina)
                    </option>
                    <option value="es_BO" <?php echo ($instance['idioma_default'] ?? '') === 'es_BO' ? 'selected' : ''; ?>>
                        🇧🇴 Español (Bolivia)
                    </option>
                    <option value="es_CL" <?php echo ($instance['idioma_default'] ?? '') === 'es_CL' ? 'selected' : ''; ?>>
                        🇨🇱 Español (Chile)
                    </option>
                    <option value="es_PY" <?php echo ($instance['idioma_default'] ?? '') === 'es_PY' ? 'selected' : ''; ?>>
                        🇵🇾 Español (Paraguay)
                    </option>
                    <option value="es_UY" <?php echo ($instance['idioma_default'] ?? '') === 'es_UY' ? 'selected' : ''; ?>>
                        🇺🇾 Español (Uruguay)
                    </option>
                    <option value="pt_BR" <?php echo ($instance['idioma_default'] ?? '') === 'pt_BR' ? 'selected' : ''; ?>>
                        🇧🇷 Português (Brasil)
                    </option>
                    <option value="en_US" <?php echo ($instance['idioma_default'] ?? '') === 'en_US' ? 'selected' : ''; ?>>
                        🇺🇸 English (US)
                    </option>
                </select>
                <p class="text-sm text-gray-500 mt-1">El idioma que se mostrará por defecto en toda la plataforma</p>
            </div>

            <!-- Idiomas habilitados -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Idiomas habilitados</label>
                <?php
                $idiomas_habilitados_general = explode(',', $instance['idiomas_habilitados'] ?? 'es_AR');
                $todos_idiomas_general = [
                    'es_AR' => '🇦🇷 Español (Argentina)',
                    'es_BO' => '🇧🇴 Español (Bolivia)',
                    'es_CL' => '🇨🇱 Español (Chile)',
                    'es_PY' => '🇵🇾 Español (Paraguay)',
                    'es_UY' => '🇺🇾 Español (Uruguay)',
                    'pt_BR' => '🇧🇷 Português (Brasil)',
                    'en_US' => '🇺🇸 English (US)'
                ];
                ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <?php foreach ($todos_idiomas_general as $codigo => $nombre): ?>
                        <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition <?php echo in_array($codigo, $idiomas_habilitados_general) ? 'border-blue-300 bg-blue-50' : 'border-gray-200'; ?>">
                            <input type="checkbox" name="idiomas_habilitados[]" value="<?php echo $codigo; ?>"
                                   <?php echo in_array($codigo, $idiomas_habilitados_general) ? 'checked' : ''; ?>
                                   class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm"><?php echo $nombre; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-sm text-gray-500 mt-2">Los usuarios podrán cambiar entre estos idiomas</p>
            </div>

            <!-- Preview -->
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-xs text-gray-500 mb-2 uppercase tracking-wide">Vista previa del selector de idiomas:</p>
                <div class="flex items-center gap-2 flex-wrap">
                    <?php foreach ($idiomas_habilitados_general as $codigo): ?>
                        <?php if (isset($todos_idiomas_general[$codigo])): ?>
                            <span class="px-3 py-1 text-sm rounded-full <?php echo $codigo === ($instance['idioma_default'] ?? 'es_AR') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                                <?php echo $todos_idiomas_general[$codigo]; ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Idiomas
                </button>
            </div>
        </form>
    </div>

    <!-- CONFIGURACIÓN DE FIRMANTES -->
    <div id="config-firmantes" class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            <i data-lucide="pen-tool" class="w-7 h-7 inline mr-2"></i>
            Firmantes por Defecto
        </h2>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">Firmantes para certificados</p>
                    <p>Configurá los firmantes por defecto que aparecerán en todos los certificados. Podés sobrescribir estos valores en cada curso individual si es necesario.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="?modulo=general" enctype="multipart/form-data" class="space-y-8">
            <input type="hidden" name="accion" value="actualizar_firmantes">
            <input type="hidden" name="active_tab" value="configuracion">

            <!-- Firmante 1 -->
            <div class="border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">Firmante 1</span>
                    Principal
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre completo
                        </label>
                        <input type="text" name="firmante_nombre"
                               value="<?php echo htmlspecialchars($instance['firmante_nombre'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ej: Dra. María González">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cargo
                        </label>
                        <input type="text" name="firmante_cargo"
                               value="<?php echo htmlspecialchars($instance['firmante_cargo'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ej: Directora Académica">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Imagen de firma
                    </label>
                    <?php
                    // Detectar firma 1: primero BD, luego archivos legacy
                    $firma_1_preview = null;
                    $firma_1_source = '';
                    $firmas_dir = __DIR__ . '/../../assets/images/firmas/';

                    if (!empty($instance['firmante_1_firma_url'])) {
                        // Usar URL de BD, convertir a base64 si es archivo local
                        $url_path = $instance['firmante_1_firma_url'];
                        $file_path = __DIR__ . '/../../' . ltrim($url_path, '/');
                        if (file_exists($file_path)) {
                            $firma_1_preview = 'data:image/png;base64,' . base64_encode(file_get_contents($file_path));
                        } else {
                            $firma_1_preview = $url_path; // URL externa
                        }
                        $firma_1_source = 'configurada';
                    } else {
                        // Buscar archivos legacy
                        $legacy_names = [
                            $slug . '_firma.png',
                            $slug . '_firma_1.png',
                            $slug . '_firma.jpg',
                            $slug . '_firma_1.jpg'
                        ];
                        foreach ($legacy_names as $fname) {
                            if (file_exists($firmas_dir . $fname)) {
                                $firma_1_preview = 'data:image/png;base64,' . base64_encode(file_get_contents($firmas_dir . $fname));
                                $firma_1_source = 'detectada en servidor';
                                break;
                            }
                        }
                    }
                    ?>
                    <?php if ($firma_1_preview): ?>
                        <div class="mb-3 p-3 bg-gray-50 rounded-lg inline-flex items-start gap-3">
                            <div>
                                <img src="<?php echo htmlspecialchars($firma_1_preview); ?>"
                                     alt="Firma actual" class="max-h-16 max-w-48 border border-gray-200">
                                <p class="text-xs text-gray-500 mt-1">Firma actual (<?php echo $firma_1_source; ?>)</p>
                            </div>
                            <button type="button" onclick="eliminarFirma(1)"
                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition"
                                    title="Eliminar firma">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg inline-block">
                            <p class="text-sm text-yellow-700"><i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i> Sin firma cargada</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="firmante_1_firma" accept="image/png,image/jpeg,image/webp"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">PNG transparente recomendado. Tamaño máximo: 2MB</p>
                </div>
            </div>

            <!-- Firmante 2 -->
            <div class="border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-sm">Firmante 2</span>
                    Secundario (opcional)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre completo
                        </label>
                        <input type="text" name="firmante_2_nombre"
                               value="<?php echo htmlspecialchars($instance['firmante_2_nombre'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ej: Dr. Juan Pérez">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cargo
                        </label>
                        <input type="text" name="firmante_2_cargo"
                               value="<?php echo htmlspecialchars($instance['firmante_2_cargo'] ?? ''); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ej: Secretario General">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Imagen de firma
                    </label>
                    <?php
                    // Detectar firma 2: primero BD, luego archivos legacy
                    $firma_2_preview = null;
                    $firma_2_source = '';
                    $firmas_dir_2 = __DIR__ . '/../../assets/images/firmas/';

                    if (!empty($instance['firmante_2_firma_url'])) {
                        // Usar URL de BD, convertir a base64 si es archivo local
                        $url_path_2 = $instance['firmante_2_firma_url'];
                        $file_path_2 = __DIR__ . '/../../' . ltrim($url_path_2, '/');
                        if (file_exists($file_path_2)) {
                            $firma_2_preview = 'data:image/png;base64,' . base64_encode(file_get_contents($file_path_2));
                        } else {
                            $firma_2_preview = $url_path_2; // URL externa
                        }
                        $firma_2_source = 'configurada';
                    } else {
                        // Buscar archivos legacy
                        $legacy_names_2 = [
                            $slug . '_firma_2.png',
                            $slug . '_firma_2.jpg'
                        ];
                        foreach ($legacy_names_2 as $fname) {
                            if (file_exists($firmas_dir_2 . $fname)) {
                                $firma_2_preview = 'data:image/png;base64,' . base64_encode(file_get_contents($firmas_dir_2 . $fname));
                                $firma_2_source = 'detectada en servidor';
                                break;
                            }
                        }
                    }
                    ?>
                    <?php if ($firma_2_preview): ?>
                        <div class="mb-3 p-3 bg-gray-50 rounded-lg inline-flex items-start gap-3">
                            <div>
                                <img src="<?php echo htmlspecialchars($firma_2_preview); ?>"
                                     alt="Firma actual" class="max-h-16 max-w-48 border border-gray-200">
                                <p class="text-xs text-gray-500 mt-1">Firma actual (<?php echo $firma_2_source; ?>)</p>
                            </div>
                            <button type="button" onclick="eliminarFirma(2)"
                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition"
                                    title="Eliminar firma">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="mb-3 p-3 bg-gray-50 border border-gray-200 rounded-lg inline-block">
                            <p class="text-sm text-gray-500"><i data-lucide="image-off" class="w-4 h-4 inline mr-1"></i> Sin firma secundaria</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="firmante_2_firma" accept="image/png,image/jpeg,image/webp"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">PNG transparente recomendado. Tamaño máximo: 2MB</p>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Firmantes
                </button>
            </div>
        </form>
    </div>
</div>
</div>

<!-- CSS para mejoras mobile -->
<style>
/* Ocultar scrollbar pero mantener scroll */
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Ajustes mobile */
@media (max-width: 640px) {
    /* Paletas de colores: ocultar texto, solo mostrar colores */
    .paleta-btn {
        padding: 0.5rem !important;
    }
    .paleta-btn span {
        display: none;
    }

    /* Cards de módulos: reducir padding */
    #identitas-card,
    #certificatum-card {
        padding: 1rem;
    }

    /* Reducir margen del texto en cards */
    #identitas-card p.ml-12,
    #certificatum-card p.ml-12 {
        margin-left: 0;
    }

    /* Preview del header: reducir altura */
    #header-preview {
        padding: 0.75rem !important;
    }

    /* Secciones colapsables: texto más pequeño */
    .collapse-toggle span {
        font-size: 0.875rem;
    }
}

/* Ajustes para pantallas muy pequeñas */
@media (max-width: 380px) {
    .general-tab-button {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
}
</style>

<!-- ============================================================================ -->
<!-- PANEL DE AYUDA LATERAL -->
<!-- ============================================================================ -->

<!-- Botón flotante de ayuda -->
<button id="btn-ayuda-flotante" onclick="togglePanelAyuda()"
        class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center z-40 transition-all hover:scale-110 group"
        title="Centro de Ayuda (F1)">
    <i data-lucide="help-circle" class="w-7 h-7"></i>
    <span class="absolute -top-1 -right-1 bg-gray-800 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm opacity-80 group-hover:opacity-100">F1</span>
</button>

<!-- Panel lateral de ayuda -->
<div id="panel-ayuda" class="fixed top-0 right-0 h-full w-96 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 flex-shrink-0">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold flex items-center gap-2">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                Centro de Ayuda
            </h2>
            <button onclick="togglePanelAyuda()" class="p-1 hover:bg-blue-500 rounded transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="mt-3 relative">
            <input type="text" id="busqueda-ayuda" placeholder="Buscar en la ayuda..."
                   onkeyup="filtrarAyuda(this.value)"
                   class="w-full px-4 py-2 rounded-lg text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
            <i data-lucide="search" class="w-4 h-4 absolute right-3 top-2.5 text-gray-400"></i>
        </div>
        <!-- Navegación rápida -->
        <div class="mt-3 flex gap-2">
            <button onclick="mostrarSeccionAyuda('general')" class="flex-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 rounded-lg text-xs font-medium transition flex items-center justify-center gap-1">
                <i data-lucide="rocket" class="w-3 h-3"></i>
                Guías
            </button>
            <button onclick="actualizarAyudaContextual()" class="flex-1 px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-medium transition flex items-center justify-center gap-1">
                <i data-lucide="help-circle" class="w-3 h-3"></i>
                Ayuda contextual
            </button>
        </div>
    </div>
    <div id="ayuda-contexto" class="px-4 py-2 bg-blue-50 border-b text-sm text-blue-700 flex items-center gap-2 flex-shrink-0">
        <i data-lucide="info" class="w-4 h-4"></i>
        <span id="ayuda-contexto-texto">Configuración General</span>
    </div>
    <div id="ayuda-contenido" class="flex-1 overflow-y-auto p-4"></div>
    <!-- Recursos Globales - Siempre visibles -->
    <div class="p-3 bg-gradient-to-r from-slate-50 to-gray-100 border-t flex-shrink-0">
        <p class="text-xs text-gray-500 mb-2 font-medium flex items-center gap-1">
            <i data-lucide="library" class="w-3 h-3"></i> Recursos de ayuda
        </p>
        <div class="grid grid-cols-3 gap-2">
            <button onclick="mostrarAyudaSeccion('faq')" class="flex flex-col items-center gap-1 p-2 bg-white rounded-lg border hover:bg-amber-50 hover:border-amber-300 transition group">
                <i data-lucide="help-circle" class="w-4 h-4 text-amber-500 group-hover:text-amber-600"></i>
                <span class="text-xs text-gray-600 group-hover:text-amber-700">FAQ</span>
            </button>
            <button onclick="mostrarAyudaSeccion('glosario')" class="flex flex-col items-center gap-1 p-2 bg-white rounded-lg border hover:bg-blue-50 hover:border-blue-300 transition group">
                <i data-lucide="book-open" class="w-4 h-4 text-blue-500 group-hover:text-blue-600"></i>
                <span class="text-xs text-gray-600 group-hover:text-blue-700">Glosario</span>
            </button>
            <button onclick="mostrarAyudaSeccion('errores-comunes')" class="flex flex-col items-center gap-1 p-2 bg-white rounded-lg border hover:bg-red-50 hover:border-red-300 transition group">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 group-hover:text-red-600"></i>
                <span class="text-xs text-gray-600 group-hover:text-red-700">Errores</span>
            </button>
        </div>
    </div>
    <div class="px-4 py-2 bg-gray-50 border-t flex-shrink-0">
        <a href="mailto:soporte@verumax.com" class="text-xs text-blue-600 hover:underline flex items-center justify-center gap-1">
            <i data-lucide="headphones" class="w-3 h-3"></i> Contactar soporte
        </a>
    </div>
</div>
<div id="overlay-ayuda" onclick="togglePanelAyuda()" class="fixed inset-0 bg-black bg-opacity-30 z-40 hidden"></div>

<!-- Modal Tutorial Paso a Paso -->
<div id="modal-tutorial" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50" onclick="cerrarTutorial()"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <!-- Header del tutorial -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-5 rounded-t-2xl flex-shrink-0">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-blue-200 text-xs uppercase tracking-wide mb-1">Tutorial paso a paso</p>
                    <h3 id="tutorial-titulo" class="text-xl font-bold">Título del Tutorial</h3>
                </div>
                <button onclick="cerrarTutorial()" class="p-1 hover:bg-white/20 rounded-lg transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <!-- Barra de progreso -->
            <div class="mt-4 flex items-center gap-3">
                <div class="flex-1 h-2 bg-white/30 rounded-full overflow-hidden">
                    <div id="tutorial-progreso" class="h-full bg-white rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <span id="tutorial-contador" class="text-sm font-medium">1/4</span>
            </div>
        </div>

        <!-- Contenido del paso -->
        <div id="tutorial-contenido" class="flex-1 overflow-y-auto p-6">
            <!-- Contenido dinámico -->
        </div>

        <!-- Footer con navegación -->
        <div class="p-4 border-t bg-gray-50 rounded-b-2xl flex justify-between items-center flex-shrink-0">
            <button id="btn-tutorial-anterior" onclick="tutorialAnterior()"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-lg transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                Anterior
            </button>
            <div class="flex gap-1" id="tutorial-dots">
                <!-- Dots dinámicos -->
            </div>
            <button id="btn-tutorial-siguiente" onclick="tutorialSiguiente()"
                    class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition flex items-center gap-2">
                Siguiente
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>

<script>
// ============================================================================
// LOGO ESTILO - Preview dinámico del estilo seleccionado
// ============================================================================
function updateEstiloPreview(estilo) {
    const container = document.getElementById('estilo-preview-container');
    if (!container) return;

    // Remover todas las clases de redondeo
    container.classList.remove('rounded-lg', 'rounded-full');

    // Aplicar clase según estilo
    switch(estilo) {
        case 'rectangular-rounded':
        case 'cuadrado-rounded':
            container.classList.add('rounded-lg');
            break;
        case 'circular':
            container.classList.add('rounded-full');
            break;
        // rectangular y cuadrado no tienen redondeo
    }
}

// ============================================================================
// LOGO UPLOAD - Preview en tiempo real y toggle URL
// ============================================================================
function toggleLogoUrl() {
    const section = document.getElementById('logo-url-section');
    const chevron = document.getElementById('chevron-logo-url');
    const toggleText = document.getElementById('toggle-url-text');

    if (section && chevron) {
        section.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
        toggleText.textContent = section.classList.contains('hidden')
            ? '¿Tienes una URL externa?'
            : 'Ocultar campo URL';
    }
}

function previewLogoFile(input) {
    const preview = document.getElementById('logo-preview');
    const placeholder = document.getElementById('logo-placeholder');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            if (preview) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
        };

        reader.readAsDataURL(input.files[0]);

        // Limpiar campo URL si se selecciona archivo
        const urlInput = document.getElementById('logo_url_input');
        if (urlInput) {
            urlInput.value = '';
        }
    }
}

function previewLogoUrl(url) {
    const preview = document.getElementById('logo-preview');
    const placeholder = document.getElementById('logo-placeholder');

    if (url && url.trim() !== '') {
        if (preview) {
            preview.src = url;
            preview.classList.remove('hidden');
        }
        if (placeholder) {
            placeholder.classList.add('hidden');
        }

        // Limpiar campo de archivo si se ingresa URL
        const fileInput = document.getElementById('logo_file');
        if (fileInput) {
            fileInput.value = '';
        }
    }
}

// ============================================================================
// SECCIONES COLAPSABLES
// ============================================================================
function toggleSection(sectionId) {
    const section = document.getElementById('section-' + sectionId);
    const chevron = document.getElementById('chevron-' + sectionId);

    if (section && chevron) {
        section.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');

        // Guardar estado en localStorage
        const isHidden = section.classList.contains('hidden');
        localStorage.setItem('section-' + sectionId, isHidden ? 'collapsed' : 'expanded');
    }
}

// Restaurar estado de secciones colapsables al cargar
document.addEventListener('DOMContentLoaded', function() {
    const sections = ['info-adicional', 'redes-sociales'];
    sections.forEach(function(sectionId) {
        const savedState = localStorage.getItem('section-' + sectionId);
        const section = document.getElementById('section-' + sectionId);
        const chevron = document.getElementById('chevron-' + sectionId);

        if (section && chevron) {
            if (savedState === 'expanded') {
                section.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            }
            // Por defecto están colapsados (hidden)
        }
    });
});

// Paletas predefinidas
const paletas = {
    'verde-elegante': { primario: '#2E7D32', secundario: '#1B5E20', acento: '#66BB6A' },
    'azul-profesional': { primario: '#1976D2', secundario: '#0D47A1', acento: '#42A5F5' },
    'morado-creativo': { primario: '#7B1FA2', secundario: '#4A148C', acento: '#BA68C8' },
    'naranja-energetico': { primario: '#F57C00', secundario: '#E65100', acento: '#FFB74D' },
    'rojo-institucional': { primario: '#C62828', secundario: '#B71C1C', acento: '#EF5350' },
    'gris-minimalista': { primario: '#424242', secundario: '#212121', acento: '#757575' }
};

const paletaSelect = document.getElementById('paleta-select');
const colorPrimario = document.getElementById('color-primario');
const colorSecundario = document.getElementById('color-secundario');
const colorAcento = document.getElementById('color-acento');
const colorPrimarioText = document.getElementById('color-primario-text');
const colorSecundarioText = document.getElementById('color-secundario-text');
const colorAcentoText = document.getElementById('color-acento-text');

// Aplicar paleta desde botones visuales
function aplicarPaleta(nombrePaleta) {
    if (!paletas[nombrePaleta]) return;

    const p = paletas[nombrePaleta];

    // Actualizar hidden input
    if (paletaSelect) {
        paletaSelect.value = nombrePaleta;
        paletaSelect.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Actualizar color pickers y disparar eventos para detección de cambios
    if (colorPrimario) {
        colorPrimario.value = p.primario;
        colorPrimario.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (colorSecundario) {
        colorSecundario.value = p.secundario;
        colorSecundario.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (colorAcento) {
        colorAcento.value = p.acento;
        colorAcento.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Actualizar textos
    if (colorPrimarioText) colorPrimarioText.value = p.primario;
    if (colorSecundarioText) colorSecundarioText.value = p.secundario;
    if (colorAcentoText) colorAcentoText.value = p.acento;

    // Actualizar preview del header
    actualizarHeaderPreview(p.primario, p.secundario, p.acento);

    // Actualizar botones visuales (marcar el seleccionado)
    document.querySelectorAll('.paleta-btn').forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50');
        btn.classList.add('border-gray-200');
    });
    event.currentTarget.classList.remove('border-gray-200');
    event.currentTarget.classList.add('border-blue-500', 'bg-blue-50');
}

// Actualizar preview del header
function actualizarHeaderPreview(primario, secundario, acento) {
    const preview = document.getElementById('header-preview');
    const previewAcento = document.getElementById('preview-acento');

    if (preview) {
        preview.style.background = `linear-gradient(135deg, ${primario} 0%, ${secundario} 100%)`;
    }
    if (previewAcento) {
        previewAcento.style.background = acento;
    }
}

// Sync color pickers with text AND preview
colorPrimario?.addEventListener('input', (e) => {
    colorPrimarioText.value = e.target.value;
    actualizarHeaderPreview(e.target.value, colorSecundario?.value, colorAcento?.value);
});
colorSecundario?.addEventListener('input', (e) => {
    colorSecundarioText.value = e.target.value;
    actualizarHeaderPreview(colorPrimario?.value, e.target.value, colorAcento?.value);
});
colorAcento?.addEventListener('input', (e) => {
    colorAcentoText.value = e.target.value;
    actualizarHeaderPreview(colorPrimario?.value, colorSecundario?.value, e.target.value);
});

// Toggle interactivo para Identitas
const identitasToggle = document.getElementById('identitas-toggle');
const identitasStatus = document.getElementById('identitas-status');
const identitasWarning = document.getElementById('identitas-warning');
const certificatumToggle = document.getElementById('certificatum-toggle');
const certificatumStatus = document.getElementById('certificatum-status');

identitasToggle?.addEventListener('change', function() {
    identitasStatus.textContent = this.checked ? 'ACTIVO' : 'DESACTIVADO';
    const card = document.getElementById('identitas-card');
    const iconContainer = card?.querySelector('.p-2.rounded-lg');
    const icon = card?.querySelector('i[data-lucide="layout"]');

    if (this.checked) {
        card?.classList.remove('border-gray-200', 'bg-gray-50');
        card?.classList.add('border-blue-300', 'bg-blue-50/50');
        iconContainer?.classList.remove('bg-gray-200');
        iconContainer?.classList.add('bg-blue-100');
        icon?.classList.remove('text-gray-400');
        icon?.classList.add('text-blue-600');
        identitasWarning?.classList.add('hidden');
    } else {
        card?.classList.remove('border-blue-300', 'bg-blue-50/50');
        card?.classList.add('border-gray-200', 'bg-gray-50');
        iconContainer?.classList.remove('bg-blue-100');
        iconContainer?.classList.add('bg-gray-200');
        icon?.classList.remove('text-blue-600');
        icon?.classList.add('text-gray-400');
        identitasWarning?.classList.remove('hidden');
    }
    lucide.createIcons();
});

certificatumToggle?.addEventListener('change', function() {
    certificatumStatus.textContent = this.checked ? 'ACTIVO' : 'DESACTIVADO';
    const card = document.getElementById('certificatum-card');
    const iconContainer = card?.querySelector('.p-2.rounded-lg');
    const icon = card?.querySelector('i[data-lucide="award"]');

    if (this.checked) {
        card?.classList.remove('border-gray-200', 'bg-gray-50');
        card?.classList.add('border-green-300', 'bg-green-50/50');
        iconContainer?.classList.remove('bg-gray-200');
        iconContainer?.classList.add('bg-green-100');
        icon?.classList.remove('text-gray-400');
        icon?.classList.add('text-green-600');
    } else {
        card?.classList.remove('border-green-300', 'bg-green-50/50');
        card?.classList.add('border-gray-200', 'bg-gray-50');
        iconContainer?.classList.remove('bg-green-100');
        iconContainer?.classList.add('bg-gray-200');
        icon?.classList.remove('text-green-600');
        icon?.classList.add('text-gray-400');
    }
    lucide.createIcons();
});

// ============================================================================
// SISTEMA DE AYUDA CON MODALES
// ============================================================================

function mostrarAyuda(tema) {
    const ayudas = {
        'logo-favicon': {
            titulo: 'Logo y Favicon Institucional',
            contenido: `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">¿Qué es el logo institucional?</h4>
                        <p class="text-sm text-gray-700">Es la imagen que represent a tu institución y aparecerá en todas las páginas y módulos del sitio.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">¿Qué es el favicon?</h4>
                        <p class="text-sm text-gray-700">Es el pequeño icono que aparece en la pestaña del navegador junto al título de tu sitio. Se genera automáticamente desde tu logo.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">Formatos recomendados</h4>
                        <ul class="text-sm text-gray-700 space-y-1 ml-4">
                            <li>• PNG con fondo transparente (recomendado)</li>
                            <li>• SVG para máxima calidad</li>
                            <li>• JPG si es una fotografía</li>
                            <li>• Tamaño mínimo: 200x200 px</li>
                        </ul>
                    </div>
                </div>
            `
        },
        'construccion': {
            titulo: 'Modo "En Construcción"',
            contenido: `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">¿Cuándo usar este modo?</h4>
                        <p class="text-sm text-gray-700">Cuando estés trabajando en el sitio y no quieras que los visitantes vean contenido incompleto.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">¿Qué verán los visitantes?</h4>
                        <p class="text-sm text-gray-700">Una página profesional indicando que el sitio está en construcción, con tu mensaje personalizado si lo configuraste.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">¿Puedo seguir trabajando?</h4>
                        <p class="text-sm text-gray-700">Sí, tú podrás acceder al admin y configurar todo normalmente. Solo los visitantes públicos verán la página de construcción.</p>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                        <p class="text-sm text-yellow-800"><strong>Importante:</strong> No olvides desactivar este modo cuando tu sitio esté listo para ser público.</p>
                    </div>
                </div>
            `
        },
        'robots': {
            titulo: 'Control de Indexación (Robots)',
            contenido: `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">¿Qué significa "noindex"?</h4>
                        <p class="text-sm text-gray-700">Es una instrucción para que los buscadores (Google, Bing, etc.) NO indexen tu sitio en sus resultados de búsqueda.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">¿Por qué está activado por defecto?</h4>
                        <p class="text-sm text-gray-700">Por seguridad. Así Google no indexará contenido mientras estás desarrollando el sitio.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-2">¿Cuándo debo desactivarlo?</h4>
                        <ul class="text-sm text-gray-700 space-y-2 ml-4">
                            <li>✅ Cuando el contenido esté completo y revisado</li>
                            <li>✅ Cuando hayas configurado títulos y descripciones SEO</li>
                            <li>✅ Cuando estés listo para que la gente encuentre tu sitio en Google</li>
                        </ul>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded p-3">
                        <p class="text-sm text-blue-800"><strong>Recomendación:</strong> Deja este modo activo hasta que tu sitio esté 100% listo para ser público.</p>
                    </div>
                </div>
            `
        }
    };

    const ayuda = ayudas[tema];
    if (!ayuda) return;

    // Crear modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">${ayuda.titulo}</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="text-gray-700">
                    ${ayuda.contenido}
                </div>
                <div class="mt-6 flex justify-end">
                    <button onclick="this.closest('.fixed').remove()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    lucide.createIcons();

    // Cerrar con ESC
    const closeOnEsc = (e) => {
        if (e.key === 'Escape') {
            modal.remove();
            document.removeEventListener('keydown', closeOnEsc);
        }
    };
    document.addEventListener('keydown', closeOnEsc);

    // Cerrar al hacer clic fuera
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// ============================================================================
// SISTEMA DE TABS DE GENERAL
// ============================================================================
function switchGeneralTab(tabName) {
    // Ocultar todos los tabs
    document.querySelectorAll('.general-tab-content').forEach(tab => {
        tab.style.display = 'none';
        tab.classList.remove('active');
    });

    // Mostrar tab seleccionado
    const selectedTab = document.getElementById('general-tab-' + tabName);
    if (selectedTab) {
        selectedTab.style.display = 'block';
        selectedTab.classList.add('active');
    }

    // Actualizar estado de botones
    document.querySelectorAll('.general-tab-button').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    // Activar botón seleccionado - buscar por el tab name (3 tabs)
    document.querySelectorAll('.general-tab-button').forEach(btn => {
        const btnText = btn.textContent.trim().toLowerCase();
        if ((tabName === 'institucion' && btnText.includes('institución')) ||
            (tabName === 'apariencia' && btnText.includes('apariencia')) ||
            (tabName === 'configuracion' && btnText.includes('configuración'))) {
            btn.classList.remove('border-transparent', 'text-gray-500');
            btn.classList.add('border-blue-500', 'text-blue-600');
        }
    });

    // Actualizar campos hidden en formularios con el tab activo
    updateActiveTabFields(tabName);

    // Recrear iconos de Lucide
    lucide.createIcons();
}

// Función para actualizar campos hidden de active_tab
function updateActiveTabFields(tabName) {
    document.querySelectorAll('input[name="active_tab"]').forEach(input => {
        input.value = tabName;
    });
}

// Función para eliminar firma
function eliminarFirma(numFirma) {
    if (!confirm('¿Estás seguro de eliminar esta firma? Esta acción no se puede deshacer.')) {
        return;
    }

    // Crear form y enviar
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '?modulo=general';

    const accionInput = document.createElement('input');
    accionInput.type = 'hidden';
    accionInput.name = 'accion';
    accionInput.value = 'eliminar_firma';
    form.appendChild(accionInput);

    const numInput = document.createElement('input');
    numInput.type = 'hidden';
    numInput.name = 'num_firma';
    numInput.value = numFirma;
    form.appendChild(numInput);

    const tabInput = document.createElement('input');
    tabInput.type = 'hidden';
    tabInput.name = 'active_tab';
    tabInput.value = 'configuracion';
    form.appendChild(tabInput);

    document.body.appendChild(form);
    form.submit();
}

// Agregar campos active_tab a todos los formularios al cargar
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔵 DOMContentLoaded ejecutado');

    // Leer parámetro tab de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const urlTab = urlParams.get('tab');
    console.log('🔵 URL tab param:', urlTab);

    // Si hay un tab en la URL, activarlo
    if (urlTab) {
        console.log('🔵 Activando tab:', urlTab);
        switchGeneralTab(urlTab);
    }

    // Detectar tab activo actual
    const activeTab = document.querySelector('.general-tab-content.active');
    const tabName = activeTab ? activeTab.id.replace('general-tab-', '') : 'institucion';
    console.log('🔵 Tab activo:', tabName);

    // Agregar campo hidden a todos los formularios que no lo tengan
    document.querySelectorAll('form[action*="modulo=general"]').forEach(form => {
        if (!form.querySelector('input[name="active_tab"]')) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'active_tab';
            hiddenInput.value = tabName;
            form.appendChild(hiddenInput);
        }
    });

    // Inicializar sistema de detección de cambios
    initFormChangeDetection();

    // Mostrar toast y scroll si hay mensaje (después de inicializar tabs)
    console.log('🔵 generalPageMessage:', window.generalPageMessage);
    if (window.generalPageMessage) {
        const msg = window.generalPageMessage;
        console.log('🟢 Mostrando toast:', msg.mensaje);

        // Activar tab correcto si viene en el mensaje
        if (msg.activeTab) {
            console.log('🟢 Activando tab desde mensaje:', msg.activeTab);
            switchGeneralTab(msg.activeTab);
        }

        // Mostrar toast
        setTimeout(() => {
            console.log('🟢 Ejecutando showToast...');
            showToast(msg.mensaje, msg.tipo);
        }, 100);

        // Hacer scroll si hay elemento
        if (msg.scrollTo) {
            console.log('🟢 Scroll to:', msg.scrollTo);
            setTimeout(() => {
                const elemento = document.getElementById(msg.scrollTo);
                if (elemento) {
                    console.log('🟢 Elemento encontrado, haciendo scroll');

                    // Hacer scroll
                    elemento.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Agregar highlight temporal
                    elemento.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
                    setTimeout(() => {
                        elemento.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
                    }, 2000);
                } else {
                    console.log('🔴 Elemento no encontrado:', msg.scrollTo);
                }
            }, 600);
        }
    } else {
        console.log('🔴 No hay generalPageMessage');
    }
});

// ============================================================================
// SISTEMA DE TOAST NOTIFICATIONS
// ============================================================================
function showToast(message, type = 'success') {
    // Crear contenedor de toasts si no existe
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed top-4 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(toastContainer);
    }

    // Crear toast
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    const icon = type === 'success' ? 'check-circle' : 'alert-circle';

    toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] transform transition-all duration-300 translate-x-full`;
    toast.innerHTML = `
        <i data-lucide="${icon}" class="w-5 h-5 flex-shrink-0"></i>
        <span class="flex-1">${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    `;

    toastContainer.appendChild(toast);
    lucide.createIcons();

    // Animación de entrada
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 10);

    // Auto-ocultar después de 4 segundos
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ============================================================================
// SISTEMA DE DETECCIÓN DE CAMBIOS EN FORMULARIOS
// ============================================================================
function initFormChangeDetection() {
    document.querySelectorAll('form[action*="modulo=general"]').forEach(form => {
        const submitBtn = form.querySelector('button.save-button[type="submit"]');
        if (!submitBtn) return;

        // Guardar datos originales del formulario
        const formData = new FormData(form);
        const originalData = {};
        for (let [key, value] of formData.entries()) {
            originalData[key] = value;
        }

        // Guardar textos originales del botón
        const originalBtnHTML = submitBtn.innerHTML;

        // Estado inicial: botón en gris (sin cambios)
        submitBtn.className = 'px-6 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed transition font-semibold flex items-center gap-2 save-button';
        submitBtn.disabled = true;

        // Función para verificar cambios
        const checkChanges = () => {
            const currentData = new FormData(form);
            let hasChanges = false;

            // Verificar cambios en valores existentes y nuevas keys
            for (let [key, value] of currentData.entries()) {
                if (key !== 'active_tab' && key !== 'accion') {
                    if (!(key in originalData) || originalData[key] !== value) {
                        hasChanges = true;
                        break;
                    }
                }
            }

            // Verificar keys que desaparecieron (checkboxes desmarcados)
            if (!hasChanges) {
                for (let key in originalData) {
                    if (key !== 'active_tab' && key !== 'accion' && !currentData.has(key)) {
                        hasChanges = true;
                        break;
                    }
                }
            }

            // Actualizar estado del botón
            if (hasChanges) {
                submitBtn.disabled = false;
                submitBtn.className = 'px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button';
            } else {
                submitBtn.disabled = true;
                submitBtn.className = 'px-6 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed transition font-semibold flex items-center gap-2 save-button';
            }
        };

        // Escuchar cambios en todos los campos
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', checkChanges);
            field.addEventListener('change', checkChanges);
        });

        // Feedback al enviar
        form.addEventListener('submit', function(e) {
            submitBtn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 inline mr-2 animate-spin"></i>Guardando...';
            submitBtn.disabled = true;
            lucide.createIcons();
        });
    });
}

// Función para marcar botón como guardado exitosamente
function markButtonAsSaved(button) {
    const originalHTML = button.innerHTML;
    const originalClass = button.className;

    button.className = button.className.replace('bg-blue-600', 'bg-green-600').replace('hover:bg-blue-700', 'hover:bg-green-700');
    button.innerHTML = '<i data-lucide="check" class="w-5 h-5 inline mr-2"></i>Guardado';
    lucide.createIcons();

    setTimeout(() => {
        button.className = originalClass;
        button.innerHTML = originalHTML;
        lucide.createIcons();
    }, 2000);
}

// ============================================================================
// FUNCIONES DE IA - AUTOCOMPLETAR CON INTELIGENCIA ARTIFICIAL
// ============================================================================

/**
 * Autocompletar un campo individual con IA
 */
async function autocompletarConIA(fieldId, fieldName, fieldLabel, fieldType, bloque = '') {
    const field = document.getElementById(fieldId);
    if (!field) {
        console.error('[IA] Campo no encontrado:', fieldId);
        showToast('Error: Campo no encontrado', 'error');
        return;
    }
    console.log('[IA] Autocompletando campo:', fieldId, 'bloque:', bloque);

    // Mostrar indicador de carga
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="loader" class="w-3 h-3 inline animate-spin"></i>';
    btn.disabled = true;
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
        const response = await fetch('ajax/openai_autocompletar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                field_name: fieldName,
                field_label: fieldLabel,
                field_type: fieldType,
                bloque: bloque,
                existing_values: ''
            })
        });

        const data = await response.json();

        if (data.success && data.content) {
            // Establecer valor
            autocompletarCampo(fieldId, data.content);
            showToast('Generado con IA ✨', 'success');
            // Forzar verificacion de cambios para habilitar boton guardar
            if (typeof window.checkFormChanges === 'function') {
                window.checkFormChanges();
            }
        } else if (data.success && !data.content) {
            showToast('La IA no generó contenido', 'warning');
        } else {
            // Mostrar error
            if (data.code === 'NOT_CONFIGURED') {
                showToast('OpenAI no configurado. Habilita IA en Módulos.', 'error');
            } else {
                showToast(data.error || 'Error al generar con IA', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    } finally {
        // Restaurar boton
        btn.innerHTML = originalContent;
        btn.disabled = false;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

/**
 * Generar todos los campos SEO con IA (función agrupada)
 */
async function generarCamposSEOConIA() {
    console.log('[IA] Generando campos SEO agrupados');

    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 inline animate-spin"></i> Generando...';
    btn.disabled = true;
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
        const response = await fetch('ajax/openai_generar_combinado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                group_type: 'seo'
            })
        });

        const data = await response.json();

        if (data.success && data.fields) {
            let generados = 0;
            // Autocompletar cada campo
            for (let fieldId in data.fields) {
                if (data.fields[fieldId]) {
                    autocompletarCampo(fieldId, data.fields[fieldId]);
                    generados++;
                }
            }
            showToast(`${generados} campos SEO generados con IA ✨`, 'success');
            // Forzar verificacion de cambios
            if (typeof window.checkFormChanges === 'function') {
                window.checkFormChanges();
            }
            // Habilitar botón guardar SEO
            const btnGuardarSeo = document.getElementById('btn-guardar-seo');
            if (btnGuardarSeo) btnGuardarSeo.disabled = false;
        } else {
            if (data.code === 'NOT_CONFIGURED') {
                showToast('OpenAI no configurado. Habilita IA en Módulos.', 'error');
            } else {
                showToast(data.error || 'Error al generar con IA', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    } finally {
        btn.innerHTML = originalContent;
        btn.disabled = false;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

/**
 * Autocompletar campo (helper)
 */
function autocompletarCampo(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.value = value;
        // Disparar evento input para actualizar contadores
        field.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

// ============================================================================
// PANEL DE AYUDA
// ============================================================================

const contenidoAyudaGeneral = {
    'general': {
        titulo: 'Bienvenido a Configuración General',
        contenido: `
            <div class="space-y-4">
                <div class="bg-gradient-to-br from-blue-50 to-green-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="settings" class="w-4 h-4"></i> Centro de Configuración
                    </h3>
                    <p class="text-sm text-gray-700">Desde aquí podés configurar toda la identidad visual y operativa de tu institución.</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Secciones disponibles:</h3>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-center gap-2"><i data-lucide="building-2" class="w-4 h-4 text-green-400"></i> <strong>Institución:</strong> Nombre, logo, redes sociales</li>
                        <li class="flex items-center gap-2"><i data-lucide="palette" class="w-4 h-4 text-purple-400"></i> <strong>Apariencia:</strong> Colores, branding visual</li>
                        <li class="flex items-center gap-2"><i data-lucide="settings" class="w-4 h-4 text-blue-400"></i> <strong>Configuración:</strong> Módulos, firmantes, idiomas</li>
                    </ul>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-3">
                        <i data-lucide="rocket" class="w-4 h-4"></i> Guía de Inicio Rápido
                    </h3>
                    <p class="text-sm text-green-700 mb-3">¿Primera vez configurando? Seguí estos pasos:</p>
                    <div class="space-y-2">
                        <button onclick="abrirTutorial('configuracion-inicial')" class="w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                            Configuración inicial completa
                        </button>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="lightbulb" class="w-4 h-4"></i> Tip
                    </h3>
                    <p class="text-sm text-amber-700">Completá primero la información institucional y el logo. El sistema usará estos datos en todos los documentos y comunicaciones.</p>
                </div>
            </div>
        `
    },
    'institucion': {
        titulo: 'Información Institucional',
        contenido: `
            <div class="space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="building-2" class="w-4 h-4"></i> Datos Básicos
                    </h3>
                    <p class="text-sm text-green-700">Configurá el nombre de tu institución, logo y descripción que aparecerán en todos los documentos.</p>
                    <ul class="text-xs text-green-600 mt-2 space-y-1 ml-4 list-disc">
                        <li>El nombre aparece en certificados y encabezados</li>
                        <li>La descripción se usa para SEO y metadatos</li>
                    </ul>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="image" class="w-4 h-4"></i> Logo
                    </h3>
                    <p class="text-sm text-blue-700">Subí tu logo en formato PNG o JPG. Recomendamos PNG con fondo transparente.</p>
                    <div class="mt-2 text-xs text-blue-600 bg-blue-100 rounded p-2">
                        <strong>Tip:</strong> Tamaño ideal: 400x100px para rectangular, 200x200px para cuadrado.
                    </div>
                    <button onclick="abrirTutorial('subir-logo')" class="mt-3 w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="image" class="w-4 h-4"></i> Estilos de Logo
                    </h3>
                    <p class="text-sm text-purple-700">Elegí cómo se muestra tu logo:</p>
                    <ul class="text-xs text-purple-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Rectangular:</strong> Para logos horizontales</li>
                        <li><strong>Rectangular redondeado:</strong> Bordes suaves</li>
                        <li><strong>Circular:</strong> Para isotipos o escudos</li>
                        <li><strong>Cuadrado:</strong> Para logos compactos</li>
                    </ul>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="share-2" class="w-4 h-4"></i> Redes Sociales
                    </h3>
                    <p class="text-sm text-amber-700">Agregá los enlaces completos a tus redes (incluyendo https://).</p>
                    <p class="text-xs text-amber-600 mt-1">Los íconos se muestran automáticamente en el footer de tu sitio.</p>
                </div>
            </div>
        `
    },
    'apariencia': {
        titulo: 'Apariencia y Branding',
        contenido: `
            <div class="space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="palette" class="w-4 h-4"></i> Paleta de Colores
                    </h3>
                    <p class="text-sm text-green-700">Tu identidad visual define la percepción de tu marca.</p>
                    <ul class="text-xs text-green-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Primario:</strong> Headers, botones principales, títulos</li>
                        <li><strong>Secundario:</strong> Acentos, fondos alternos</li>
                        <li><strong>Acento:</strong> Llamadas a la acción, destacados</li>
                    </ul>
                    <button onclick="abrirTutorial('configurar-colores')" class="mt-3 w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="sparkles" class="w-4 h-4"></i> Presets de Colores
                    </h3>
                    <p class="text-sm text-blue-700">Usá las paletas predefinidas como punto de partida:</p>
                    <ul class="text-xs text-blue-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Profesional Azul:</strong> Confianza, seriedad</li>
                        <li><strong>Naturaleza Verde:</strong> Frescura, crecimiento</li>
                        <li><strong>Ejecutivo Negro:</strong> Elegancia, exclusividad</li>
                        <li><strong>Energía Naranja:</strong> Dinamismo, creatividad</li>
                    </ul>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="star" class="w-4 h-4"></i> Favicon
                    </h3>
                    <p class="text-sm text-purple-700">El favicon aparece en la pestaña del navegador y favoritos.</p>
                    <div class="mt-2 text-xs text-purple-600 bg-purple-100 rounded p-2">
                        <strong>Tip:</strong> Se genera automáticamente desde tu logo. Si usás un logo rectangular, se recorta al centro.
                    </div>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="eye" class="w-4 h-4"></i> Preview en Tiempo Real
                    </h3>
                    <p class="text-sm text-amber-700">El preview del header te muestra cómo quedan los colores aplicados.</p>
                    <p class="text-xs text-amber-600 mt-1">Los cambios se visualizan inmediatamente pero no se guardan hasta hacer clic en "Guardar".</p>
                </div>
            </div>
        `
    },
    'configuracion': {
        titulo: 'Configuración del Sistema',
        contenido: `
            <div class="space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i> Módulos Disponibles
                    </h3>
                    <p class="text-sm text-green-700">Activá solo los módulos que necesitás:</p>
                    <ul class="text-xs text-green-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Identitas:</strong> Sitio web institucional</li>
                        <li><strong>Certificatum:</strong> Gestión de certificados y cursos</li>
                        <li><strong>Scripta:</strong> Documentación digital</li>
                        <li><strong>Nexus:</strong> Red de contactos y networking</li>
                    </ul>
                    <button onclick="abrirTutorial('activar-modulos')" class="mt-3 w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="pen-tool" class="w-4 h-4"></i> Firmantes
                    </h3>
                    <p class="text-sm text-blue-700">Configurá hasta 2 firmantes para los documentos oficiales.</p>
                    <ul class="text-xs text-blue-600 mt-2 space-y-1 ml-4 list-disc">
                        <li>Subí la firma digitalizada en PNG con fondo transparente</li>
                        <li>Tamaño recomendado: 300x100px</li>
                        <li>Incluí nombre completo y cargo oficial</li>
                    </ul>
                    <button onclick="abrirTutorial('configurar-firmantes')" class="mt-3 w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-4 h-4"></i>
                        Ver paso a paso
                    </button>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 flex items-center gap-2 mb-2">
                        <i data-lucide="globe" class="w-4 h-4"></i> Idiomas
                    </h3>
                    <p class="text-sm text-purple-700">Configurá los idiomas de tu plataforma:</p>
                    <ul class="text-xs text-purple-600 mt-2 space-y-1 ml-4 list-disc">
                        <li><strong>Idioma por defecto:</strong> Se usa si el visitante no selecciona otro</li>
                        <li><strong>Idiomas habilitados:</strong> Opciones disponibles en el selector</li>
                    </ul>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="construction" class="w-4 h-4"></i> Modo Construcción
                    </h3>
                    <p class="text-sm text-amber-700">Mostrá una página de "próximamente" mientras configurás tu sitio.</p>
                    <div class="mt-2 text-xs text-amber-600 bg-amber-100 rounded p-2">
                        <strong>Nota:</strong> El panel de administración sigue funcionando normalmente.
                    </div>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2 mb-2">
                        <i data-lucide="search" class="w-4 h-4"></i> SEO y Metadatos
                    </h3>
                    <p class="text-sm text-gray-700">Optimizá tu sitio para buscadores:</p>
                    <ul class="text-xs text-gray-600 mt-2 space-y-1 ml-4 list-disc">
                        <li>El nombre de institución se usa como título SEO</li>
                        <li>La descripción aparece en resultados de Google</li>
                        <li>Las redes sociales mejoran tu presencia online</li>
                    </ul>
                </div>
            </div>
        `
    },
    'general': {
        titulo: 'Configuración General',
        contenido: `
            <div class="space-y-4">
                <div class="bg-gradient-to-br from-green-50 to-blue-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 flex items-center gap-2 mb-2">
                        <i data-lucide="settings" class="w-4 h-4"></i> ¿Qué es este módulo?
                    </h3>
                    <p class="text-sm text-gray-700">Desde aquí configurás los datos generales de tu institución que afectan <strong>toda la plataforma</strong>.</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Secciones disponibles:</h3>
                    <ul class="text-sm text-gray-600 space-y-3">
                        <li class="flex items-start gap-2">
                            <i data-lucide="building-2" class="w-4 h-4 text-blue-400 mt-0.5"></i>
                            <div>
                                <strong>Institución:</strong>
                                <p class="text-xs text-gray-500">Nombre, logo, misión, redes sociales</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="palette" class="w-4 h-4 text-purple-400 mt-0.5"></i>
                            <div>
                                <strong>Apariencia:</strong>
                                <p class="text-xs text-gray-500">Colores, favicon, tema visual</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <i data-lucide="settings" class="w-4 h-4 text-gray-400 mt-0.5"></i>
                            <div>
                                <strong>Configuración:</strong>
                                <p class="text-xs text-gray-500">Módulos, firmantes, idiomas, SEO</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-300 rounded-lg p-4">
                    <h3 class="font-semibold text-amber-800 flex items-center gap-2 mb-2">
                        <i data-lucide="book-open" class="w-4 h-4"></i> 📚 Recursos de Ayuda
                    </h3>
                    <div class="space-y-2 mt-3">
                        <button onclick="mostrarAyudaSeccion('faq')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                            <i data-lucide="help-circle" class="w-4 h-4 text-amber-600"></i>
                            Preguntas Frecuentes (FAQ)
                        </button>
                        <button onclick="mostrarAyudaSeccion('glosario')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                            <i data-lucide="book" class="w-4 h-4 text-blue-600"></i>
                            Glosario de Términos
                        </button>
                        <button onclick="mostrarAyudaSeccion('errores-comunes')" class="w-full text-left px-3 py-2 bg-white rounded border hover:bg-amber-50 text-sm transition flex items-center gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600"></i>
                            Errores Comunes y Soluciones
                        </button>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="lightbulb" class="w-4 h-4"></i> Consejos
                    </h3>
                    <ul class="text-xs text-blue-700 space-y-1 ml-4 list-disc">
                        <li>Completá toda la información antes de activar módulos</li>
                        <li>Usá el dashboard para verificar qué falta configurar</li>
                        <li>Los cambios se guardan por sección (no olvides hacer clic en "Guardar")</li>
                    </ul>
                </div>
            </div>
        `
    },
    'faq': {
        titulo: 'Preguntas Frecuentes (FAQ)',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="faq preguntas frecuentes ayuda dudas problemas">
                <div class="bg-gradient-to-r from-amber-100 to-yellow-100 border border-amber-300 rounded-lg p-4">
                    <h3 class="font-bold text-amber-800 text-lg mb-1">Centro de Preguntas Frecuentes</h3>
                    <p class="text-sm text-amber-700">Respuestas a las consultas más comunes sobre VERUMax.</p>
                </div>

                <!-- Categoría: Configuración General -->
                <div class="border rounded-lg overflow-hidden">
                    <button onclick="toggleFaqCategoria('faq-config')" class="w-full flex items-center justify-between p-3 bg-blue-50 text-blue-800 font-medium text-left hover:bg-blue-100 transition">
                        <span class="flex items-center gap-2"><i data-lucide="settings" class="w-4 h-4"></i> Configuración General</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" id="faq-config-icon"></i>
                    </button>
                    <div id="faq-config" class="hidden divide-y">
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Cómo cambio el logo de mi institución?</p>
                            <p class="text-xs text-gray-600">Andá a <strong>General</strong> → <strong>Institución</strong> → sección "Logo". Subí un archivo PNG o JPG y elegí el estilo de visualización.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Por qué no se ven los cambios de colores?</p>
                            <p class="text-xs text-gray-600">Asegurate de hacer clic en <strong>"Guardar"</strong> después de elegir los colores. También probá limpiar la caché del navegador (Ctrl+F5).</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Cuántos firmantes puedo configurar?</p>
                            <p class="text-xs text-gray-600">Podés configurar hasta <strong>2 firmantes</strong>. El firmante 1 es obligatorio, el firmante 2 es opcional.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Cómo agrego idiomas a la plataforma?</p>
                            <p class="text-xs text-gray-600">En <strong>General</strong> → <strong>Configuración</strong> → "Idiomas", seleccioná los idiomas habilitados y el idioma por defecto.</p>
                        </div>
                    </div>
                </div>

                <!-- Categoría: Certificados -->
                <div class="border rounded-lg overflow-hidden">
                    <button onclick="toggleFaqCategoria('faq-cert')" class="w-full flex items-center justify-between p-3 bg-green-50 text-green-800 font-medium text-left hover:bg-green-100 transition">
                        <span class="flex items-center gap-2"><i data-lucide="award" class="w-4 h-4"></i> Certificados</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" id="faq-cert-icon"></i>
                    </button>
                    <div id="faq-cert" class="hidden divide-y">
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Por qué un certificado no se genera?</p>
                            <p class="text-xs text-gray-600">Verificá que: 1) El estudiante esté en estado <strong>"Aprobado"</strong>, 2) El curso tenga fechas definidas, 3) Exista al menos un firmante configurado.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Cómo regenero un certificado existente?</p>
                            <p class="text-xs text-gray-600">Los certificados se generan dinámicamente. Si cambiás la configuración (logo, colores, firmante), el próximo PDF que descargues ya tendrá los cambios.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Puedo modificar el texto del certificado?</p>
                            <p class="text-xs text-gray-600">Sí, en <strong>Certificatum</strong> → <strong>Configuración</strong> podés editar los textos que aparecen en los certificados usando las traducciones multiidioma.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Los certificados tienen validez legal?</p>
                            <p class="text-xs text-gray-600">Los certificados incluyen código QR de validación que permite verificar su autenticidad. La validez legal depende de la normativa de tu país/jurisdicción.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Qué pasa si escaneo un código QR?</p>
                            <p class="text-xs text-gray-600">Abre una página de verificación que muestra los datos del certificado, confirmando que es auténtico y fue emitido por tu institución.</p>
                        </div>
                    </div>
                </div>

                <!-- Categoría: Estudiantes y Cursos -->
                <div class="border rounded-lg overflow-hidden">
                    <button onclick="toggleFaqCategoria('faq-est')" class="w-full flex items-center justify-between p-3 bg-purple-50 text-purple-800 font-medium text-left hover:bg-purple-100 transition">
                        <span class="flex items-center gap-2"><i data-lucide="users" class="w-4 h-4"></i> Estudiantes y Cursos</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" id="faq-est-icon"></i>
                    </button>
                    <div id="faq-est" class="hidden divide-y">
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Por qué no puedo eliminar un estudiante?</p>
                            <p class="text-xs text-gray-600">Si el estudiante tiene inscripciones activas, primero debés eliminar las inscripciones o marcarlas como "Abandonado".</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Cómo importo muchos estudiantes a la vez?</p>
                            <p class="text-xs text-gray-600">En <strong>Certificatum</strong> → <strong>Personas</strong> → <strong>Importar</strong>. Usá un archivo CSV con columnas: DNI, Nombre, Apellido, Email.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Un estudiante puede estar en varios cursos?</p>
                            <p class="text-xs text-gray-600">Sí, un estudiante puede inscribirse a múltiples cursos. Cada inscripción genera su propio certificado.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Qué pasa si ingreso un DNI duplicado?</p>
                            <p class="text-xs text-gray-600">El sistema detecta duplicados. Si el DNI ya existe, podés actualizar los datos del estudiante existente en lugar de crear uno nuevo.</p>
                        </div>
                    </div>
                </div>

                <!-- Categoría: Emails y Notificaciones -->
                <div class="border rounded-lg overflow-hidden">
                    <button onclick="toggleFaqCategoria('faq-email')" class="w-full flex items-center justify-between p-3 bg-indigo-50 text-indigo-800 font-medium text-left hover:bg-indigo-100 transition">
                        <span class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4"></i> Emails y Notificaciones</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" id="faq-email-icon"></i>
                    </button>
                    <div id="faq-email" class="hidden divide-y">
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Por qué los emails no llegan?</p>
                            <p class="text-xs text-gray-600">Verificá: 1) Que el email del estudiante sea correcto, 2) Que no esté en spam/correo no deseado, 3) Que SendGrid esté configurado correctamente.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Qué significa "rebotado" en los emails?</p>
                            <p class="text-xs text-gray-600">El email no pudo ser entregado. Causas comunes: dirección inexistente, buzón lleno, o el servidor rechazó el mensaje.</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Puedo reenviar un email?</p>
                            <p class="text-xs text-gray-600">Sí, desde <strong>Certificatum</strong> → <strong>Inscripciones</strong>, seleccioná la inscripción y usá la acción "Enviar notificación".</p>
                        </div>
                    </div>
                </div>

                <!-- Categoría: Cuenta y Acceso -->
                <div class="border rounded-lg overflow-hidden">
                    <button onclick="toggleFaqCategoria('faq-cuenta')" class="w-full flex items-center justify-between p-3 bg-gray-50 text-gray-800 font-medium text-left hover:bg-gray-100 transition">
                        <span class="flex items-center gap-2"><i data-lucide="user" class="w-4 h-4"></i> Cuenta y Acceso</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" id="faq-cuenta-icon"></i>
                    </button>
                    <div id="faq-cuenta" class="hidden divide-y">
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Cómo cambio mi contraseña?</p>
                            <p class="text-xs text-gray-600">Hacé clic en tu nombre de usuario en la esquina superior derecha y seleccioná "Cambiar contraseña".</p>
                        </div>
                        <div class="p-3">
                            <p class="font-medium text-gray-800 text-sm mb-1">¿Puedo tener varios administradores?</p>
                            <p class="text-xs text-gray-600">Sí, contactá al soporte para agregar usuarios administradores adicionales a tu institución.</p>
                        </div>
                    </div>
                </div>
            </div>
        `
    },
    'glosario': {
        titulo: 'Glosario de Términos',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="glosario términos definiciones vocabulario significado">
                <div class="bg-gradient-to-r from-blue-100 to-indigo-100 border border-blue-300 rounded-lg p-4">
                    <h3 class="font-bold text-blue-800 text-lg mb-1">Glosario VERUMax</h3>
                    <p class="text-sm text-blue-700">Definiciones de los términos más usados en la plataforma.</p>
                </div>

                <!-- Letra A-C -->
                <div class="border rounded-lg p-4">
                    <h4 class="font-bold text-gray-800 mb-3 pb-2 border-b">A - C</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="font-semibold text-blue-700 text-sm">Analítico Académico</p>
                            <p class="text-xs text-gray-600">Documento que muestra el historial completo de un estudiante en un curso: timeline de eventos, notas, competencias y asistencia.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-blue-700 text-sm">Asignación (de docente)</p>
                            <p class="text-xs text-gray-600">Vinculación de un docente a un curso con un rol específico (docente, tutor, instructor, etc.).</p>
                        </div>
                        <div>
                            <p class="font-semibold text-blue-700 text-sm">Certificatum</p>
                            <p class="text-xs text-gray-600">Módulo de VERUMax para gestión de certificados académicos, estudiantes, cursos e inscripciones.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-blue-700 text-sm">Cohorte</p>
                            <p class="text-xs text-gray-600">Grupo o edición de un curso. Ejemplo: "Diplomatura 2024 - Cohorte A" y "Diplomatura 2024 - Cohorte B" son dos cohortes del mismo curso.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-blue-700 text-sm">Competencia</p>
                            <p class="text-xs text-gray-600">Habilidad o conocimiento específico que el estudiante adquiere al completar un curso. Se muestran en el analítico académico.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-blue-700 text-sm">Constancia</p>
                            <p class="text-xs text-gray-600">Documento que certifica un estado temporal (inscripción, cursado, asignación). A diferencia del certificado, no implica aprobación.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-blue-700 text-sm">Código de Validación</p>
                            <p class="text-xs text-gray-600">Código único alfanumérico que identifica un certificado. Formato: VALID-XXXXXXXX. Se usa para verificar autenticidad.</p>
                        </div>
                    </div>
                </div>

                <!-- Letra D-I -->
                <div class="border rounded-lg p-4">
                    <h4 class="font-bold text-gray-800 mb-3 pb-2 border-b">D - I</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="font-semibold text-green-700 text-sm">DNI / Documento</p>
                            <p class="text-xs text-gray-600">Número de identificación del estudiante o docente. Es el identificador único principal en el sistema.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-green-700 text-sm">Estado de Inscripción</p>
                            <p class="text-xs text-gray-600">Situación actual del estudiante en un curso:
                            <br>• <strong>Inscrito:</strong> Registrado pero aún no inició
                            <br>• <strong>Cursando:</strong> Actualmente tomando el curso
                            <br>• <strong>Aprobado:</strong> Completó satisfactoriamente
                            <br>• <strong>Reprobado:</strong> No alcanzó los requisitos
                            <br>• <strong>Abandonado:</strong> Dejó el curso</p>
                        </div>
                        <div>
                            <p class="font-semibold text-green-700 text-sm">Evaluación</p>
                            <p class="text-xs text-gray-600">Examen online de opción múltiple vinculado a un curso. Los estudiantes lo rinden a través de un enlace único.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-green-700 text-sm">Identitas</p>
                            <p class="text-xs text-gray-600">Módulo de VERUMax para crear y gestionar el sitio web público de la institución.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-green-700 text-sm">Inscripción</p>
                            <p class="text-xs text-gray-600">Registro que vincula un estudiante con un curso. Contiene estado, notas, asistencia y fechas.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-green-700 text-sm">Instancia</p>
                            <p class="text-xs text-gray-600">Una institución dentro de VERUMax. Cada instancia tiene su propia configuración, datos y branding.</p>
                        </div>
                    </div>
                </div>

                <!-- Letra M-R -->
                <div class="border rounded-lg p-4">
                    <h4 class="font-bold text-gray-800 mb-3 pb-2 border-b">M - R</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="font-semibold text-purple-700 text-sm">Matrícula</p>
                            <p class="text-xs text-gray-600">Sinónimo de inscripción. Vinculación formal de una persona (estudiante o docente) a un curso.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-purple-700 text-sm">Módulo</p>
                            <p class="text-xs text-gray-600">Funcionalidad específica de VERUMax que se puede activar/desactivar: Certificatum, Identitas, Scripta, Nexus, etc.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-purple-700 text-sm">Multi-tenant</p>
                            <p class="text-xs text-gray-600">Arquitectura donde múltiples instituciones comparten la misma plataforma pero con datos completamente separados.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-purple-700 text-sm">QR (Código QR)</p>
                            <p class="text-xs text-gray-600">Código bidimensional escaneable que contiene la URL de validación del certificado.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-purple-700 text-sm">Rol (de docente)</p>
                            <p class="text-xs text-gray-600">Función que cumple un docente en un curso: Docente, Instructor, Tutor, Orador, Coordinador, Facilitador, Conferencista.</p>
                        </div>
                    </div>
                </div>

                <!-- Letra S-Z -->
                <div class="border rounded-lg p-4">
                    <h4 class="font-bold text-gray-800 mb-3 pb-2 border-b">S - Z</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="font-semibold text-amber-700 text-sm">SendGrid</p>
                            <p class="text-xs text-gray-600">Servicio externo de envío de emails que usa VERUMax para notificaciones y comunicaciones.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-amber-700 text-sm">Slug</p>
                            <p class="text-xs text-gray-600">Identificador corto de la institución usado en URLs. Ejemplo: "sajur" en sajur.verumax.com</p>
                        </div>
                        <div>
                            <p class="font-semibold text-amber-700 text-sm">Template (Plantilla)</p>
                            <p class="text-xs text-gray-600">Diseño visual predefinido para certificados. Puede incluir imagen de fondo, posicionamiento de elementos y estilos.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-amber-700 text-sm">Trayectoria</p>
                            <p class="text-xs text-gray-600">Historial de eventos de un estudiante en un curso: inscripción, inicio de clases, evaluaciones, finalización, etc.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-amber-700 text-sm">Validación</p>
                            <p class="text-xs text-gray-600">Proceso de verificar la autenticidad de un certificado escaneando el código QR o ingresando el código de validación.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-amber-700 text-sm">Veritas</p>
                            <p class="text-xs text-gray-600">Asistente de inteligencia artificial integrado en VERUMax para ayuda y soporte.</p>
                        </div>
                    </div>
                </div>
            </div>
        `
    },
    'errores-comunes': {
        titulo: 'Errores Comunes y Soluciones',
        contenido: `
            <div class="space-y-4 ayuda-seccion" data-keywords="errores problemas soluciones bugs fallos arreglar solucionar">
                <div class="bg-gradient-to-r from-red-100 to-orange-100 border border-red-300 rounded-lg p-4">
                    <h3 class="font-bold text-red-800 text-lg mb-1">Solución de Problemas</h3>
                    <p class="text-sm text-red-700">Guía para resolver los errores más comunes en VERUMax.</p>
                </div>

                <!-- Error: Certificado no se genera -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            El certificado no se genera / aparece en blanco
                        </p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-sm text-gray-700"><strong>Causas posibles:</strong></p>
                        <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                            <li>El estudiante no está en estado "Aprobado"</li>
                            <li>El curso no tiene fechas de inicio/fin definidas</li>
                            <li>No hay firmantes configurados</li>
                            <li>El logo no está cargado correctamente</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>✅ Solución:</strong> Verificá que todos los requisitos estén completos. Revisá el dashboard de General para ver qué falta configurar.</p>
                        </div>
                    </div>
                </div>

                <!-- Error: DNI duplicado -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            "El DNI ya existe" al crear estudiante
                        </p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-sm text-gray-700"><strong>Causa:</strong></p>
                        <p class="text-xs text-gray-600">Ya existe un estudiante con ese número de documento en tu institución.</p>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>✅ Solución:</strong> Buscá el estudiante existente por DNI. Si necesitás actualizar sus datos, editá el registro existente en lugar de crear uno nuevo.</p>
                        </div>
                    </div>
                </div>

                <!-- Error: Email no llega -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Los emails no llegan al destinatario
                        </p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-sm text-gray-700"><strong>Causas posibles:</strong></p>
                        <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                            <li>Dirección de email incorrecta o con errores de tipeo</li>
                            <li>El email cayó en carpeta de spam/correo no deseado</li>
                            <li>El buzón del destinatario está lleno</li>
                            <li>El servidor de destino rechazó el email</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>✅ Solución:</strong> 1) Verificá la dirección de email, 2) Pedile al destinatario que revise spam, 3) Revisá el estado en Actividad → Comunicaciones.</p>
                        </div>
                    </div>
                </div>

                <!-- Error: No puedo eliminar estudiante -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            No puedo eliminar un estudiante
                        </p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-sm text-gray-700"><strong>Causa:</strong></p>
                        <p class="text-xs text-gray-600">El estudiante tiene inscripciones activas vinculadas. Por integridad de datos, no se puede eliminar.</p>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>✅ Solución:</strong> Primero eliminá o cambiá a estado "Abandonado" todas las inscripciones del estudiante. Luego podrás eliminarlo.</p>
                        </div>
                    </div>
                </div>

                <!-- Error: Cambios no se guardan -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            Los cambios no se guardan
                        </p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-sm text-gray-700"><strong>Causas posibles:</strong></p>
                        <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                            <li>No hiciste clic en el botón "Guardar"</li>
                            <li>Hay un campo requerido vacío o con formato incorrecto</li>
                            <li>La sesión expiró</li>
                            <li>Error de conexión a internet</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>✅ Solución:</strong> 1) Verificá que todos los campos obligatorios (*) estén completos, 2) Hacé clic en "Guardar", 3) Si persiste, refrescá la página y volvé a intentar.</p>
                        </div>
                    </div>
                </div>

                <!-- Error: Logo no se ve -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            El logo no se muestra o aparece cortado
                        </p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-sm text-gray-700"><strong>Causas posibles:</strong></p>
                        <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                            <li>Formato de imagen no soportado (debe ser PNG o JPG)</li>
                            <li>La imagen es muy pesada (máximo 2MB)</li>
                            <li>El estilo de logo no coincide con las proporciones</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>✅ Solución:</strong> Usá PNG con fondo transparente. Tamaño recomendado: 400x100px para rectangular o 200x200px para circular. Elegí el estilo de logo que coincida con tu imagen.</p>
                        </div>
                    </div>
                </div>

                <!-- Error: QR no funciona -->
                <div class="border border-red-200 rounded-lg overflow-hidden">
                    <div class="bg-red-50 p-3">
                        <p class="font-bold text-red-800 flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                            El código QR no funciona al escanearlo
                        </p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-sm text-gray-700"><strong>Causas posibles:</strong></p>
                        <ul class="text-xs text-gray-600 space-y-1 ml-4 list-disc">
                            <li>El PDF está muy comprimido y el QR perdió calidad</li>
                            <li>La impresión está borrosa</li>
                            <li>El lector QR no reconoce el formato</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded p-2 mt-2">
                            <p class="text-xs text-green-700"><strong>✅ Solución:</strong> Descargá el PDF original (no capturas). Imprimí en calidad alta. Si persiste, probá con otra app de escaneo QR o ingresá el código manualmente en la URL de validación.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <h4 class="font-semibold text-blue-800 flex items-center gap-2 mb-2">
                        <i data-lucide="headphones" class="w-4 h-4"></i> ¿Necesitás más ayuda?
                    </h4>
                    <p class="text-sm text-blue-700">Si tu problema no está listado aquí, contactá al soporte técnico de VERUMax.</p>
                </div>
            </div>
        `
    }
};

let panelAyudaAbierto = false;

function togglePanelAyuda() {
    const panel = document.getElementById('panel-ayuda');
    const overlay = document.getElementById('overlay-ayuda');
    const btnFlotante = document.getElementById('btn-ayuda-flotante');

    panelAyudaAbierto = !panelAyudaAbierto;

    if (panelAyudaAbierto) {
        panel.classList.remove('translate-x-full');
        overlay.classList.remove('hidden');
        btnFlotante.classList.add('hidden');
        actualizarAyudaContextual();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    } else {
        panel.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        btnFlotante.classList.remove('hidden');
    }
}

// Atajo de teclado F1 para abrir ayuda
document.addEventListener('keydown', function(e) {
    if (e.key === 'F1') {
        e.preventDefault(); // Evitar ayuda del navegador
        togglePanelAyuda();
    }
    // Escape para cerrar
    if (e.key === 'Escape' && panelAyudaAbierto) {
        togglePanelAyuda();
    }
});

function actualizarAyudaContextual() {
    const tabActivo = document.querySelector('.general-tab-button.border-blue-500');
    let contexto = 'general';

    if (tabActivo) {
        const onclickAttr = tabActivo.getAttribute('onclick') || '';
        const match = onclickAttr.match(/switchGeneralTab\('(\w+)'/);
        if (match && contenidoAyudaGeneral[match[1]]) {
            contexto = match[1];
        }
    }

    document.getElementById('ayuda-contexto-texto').textContent = contenidoAyudaGeneral[contexto].titulo;
    document.getElementById('ayuda-contenido').innerHTML = contenidoAyudaGeneral[contexto].contenido;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function mostrarSeccionAyuda(seccion) {
    if (!contenidoAyudaGeneral[seccion]) return;
    document.getElementById('ayuda-contexto-texto').textContent = contenidoAyudaGeneral[seccion].titulo;
    document.getElementById('ayuda-contenido').innerHTML = contenidoAyudaGeneral[seccion].contenido;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// Alias para compatibilidad con recursos globales
function mostrarAyudaSeccion(seccion) {
    // Abrir panel si está cerrado
    if (!panelAyudaAbierto) {
        togglePanelAyuda();
    }
    mostrarSeccionAyuda(seccion);
}

function filtrarAyuda(termino) {
    termino = termino.toLowerCase().trim();
    const contenedor = document.getElementById('ayuda-contenido');

    if (!termino) {
        actualizarAyudaContextual();
        return;
    }

    let resultados = '';
    let encontrados = 0;

    for (const [key, data] of Object.entries(contenidoAyudaGeneral)) {
        if (data.contenido.toLowerCase().includes(termino) || data.titulo.toLowerCase().includes(termino)) {
            encontrados++;
            resultados += `
                <div class="mb-4 p-3 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100 transition"
                     onclick="mostrarAyudaSeccion('${key}')">
                    <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-blue-500"></i>
                        ${data.titulo}
                    </h4>
                </div>
            `;
        }
    }

    if (encontrados === 0) {
        resultados = `<div class="text-center py-8 text-gray-500"><i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i><p>No se encontraron resultados</p></div>`;
    }

    contenedor.innerHTML = resultados;
    document.getElementById('ayuda-contexto-texto').textContent = 'Resultados de búsqueda';
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function mostrarAyudaSeccion(seccion) {
    document.getElementById('busqueda-ayuda').value = '';
    document.getElementById('ayuda-contexto-texto').textContent = contenidoAyudaGeneral[seccion].titulo;
    document.getElementById('ayuda-contenido').innerHTML = contenidoAyudaGeneral[seccion].contenido;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

// ============================================================================
// SISTEMA DE TUTORIALES PASO A PASO
// ============================================================================

const tutorialesGeneral = {
    'subir-logo': {
        titulo: 'Cómo subir tu logo',
        pasos: [
            {
                titulo: 'Paso 1: Preparar la imagen',
                icono: 'image',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Antes de subir, asegurate de tener tu logo listo:</p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold">✓</span>
                            <span><strong>Formato:</strong> PNG (preferido) o JPG</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold">✓</span>
                            <span><strong>Fondo:</strong> Transparente (PNG) para mejor integración</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold">✓</span>
                            <span><strong>Tamaño:</strong> Mínimo 200x200px, máximo 2MB</span>
                        </li>
                    </ul>
                    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                        <i data-lucide="lightbulb" class="w-4 h-4 inline mr-1"></i>
                        <strong>Tip:</strong> Un logo rectangular de 400x100px funciona mejor en el header.
                    </div>
                `
            },
            {
                titulo: 'Paso 2: Seleccionar el archivo',
                icono: 'upload',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Ubicá la sección "Logo" en la pestaña Institución:</p>
                    <div class="bg-gray-100 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-3 border-2 border-dashed border-gray-300 rounded-lg p-4 bg-white">
                            <i data-lucide="upload" class="w-8 h-8 text-gray-400"></i>
                            <div>
                                <p class="font-medium text-gray-700">Hacé clic en "Elegir archivo"</p>
                                <p class="text-sm text-gray-500">o arrastrá tu imagen aquí</p>
                            </div>
                        </div>
                    </div>
                    <ol class="space-y-2 text-sm text-gray-600">
                        <li><strong>1.</strong> Hacé clic en el botón "Elegir archivo"</li>
                        <li><strong>2.</strong> Navegá hasta donde está tu logo</li>
                        <li><strong>3.</strong> Seleccioná el archivo y hacé clic en "Abrir"</li>
                    </ol>
                `
            },
            {
                titulo: 'Paso 3: Elegir el estilo',
                icono: 'layout',
                color: 'purple',
                contenido: `
                    <p class="text-gray-600 mb-4">Seleccioná cómo querés que se vea tu logo:</p>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="border rounded-lg p-3 text-center">
                            <div class="w-16 h-10 bg-gray-300 mx-auto mb-2"></div>
                            <span class="text-xs">Rectangular</span>
                        </div>
                        <div class="border rounded-lg p-3 text-center">
                            <div class="w-16 h-10 bg-gray-300 mx-auto mb-2 rounded-lg"></div>
                            <span class="text-xs">Rect. Redondeado</span>
                        </div>
                        <div class="border rounded-lg p-3 text-center">
                            <div class="w-10 h-10 bg-gray-300 mx-auto mb-2 rounded-full"></div>
                            <span class="text-xs">Circular</span>
                        </div>
                        <div class="border rounded-lg p-3 text-center">
                            <div class="w-10 h-10 bg-gray-300 mx-auto mb-2"></div>
                            <span class="text-xs">Cuadrado</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">El preview se actualiza automáticamente al seleccionar.</p>
                `
            },
            {
                titulo: 'Paso 4: Guardar cambios',
                icono: 'save',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Para aplicar los cambios:</p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                                <i data-lucide="save" class="w-5 h-5 text-white"></i>
                            </div>
                            <div>
                                <p class="font-medium text-green-800">Hacé clic en "Guardar"</p>
                                <p class="text-sm text-green-600">Botón azul al final de la sección</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                        <p class="text-blue-700"><i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i> ¡Listo! Tu logo aparecerá en el sitio y en los documentos.</p>
                    </div>
                `
            }
        ]
    },
    'configurar-colores': {
        titulo: 'Cómo configurar los colores',
        pasos: [
            {
                titulo: 'Paso 1: Ir a Apariencia',
                icono: 'palette',
                color: 'purple',
                contenido: `
                    <p class="text-gray-600 mb-4">Los colores se configuran en la pestaña "Apariencia":</p>
                    <div class="flex gap-2 mb-4">
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Institución</span>
                        <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">Apariencia</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Configuración</span>
                    </div>
                    <p class="text-sm text-gray-500">Hacé clic en la pestaña "Apariencia" para acceder a la configuración de colores.</p>
                `
            },
            {
                titulo: 'Paso 2: Elegir una paleta',
                icono: 'sparkles',
                color: 'amber',
                contenido: `
                    <p class="text-gray-600 mb-4">Podés empezar con una paleta predefinida:</p>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div class="border rounded-lg p-2 flex items-center gap-2">
                            <div class="w-4 h-4 bg-blue-600 rounded"></div>
                            <span class="text-xs">Profesional Azul</span>
                        </div>
                        <div class="border rounded-lg p-2 flex items-center gap-2">
                            <div class="w-4 h-4 bg-green-600 rounded"></div>
                            <span class="text-xs">Naturaleza Verde</span>
                        </div>
                        <div class="border rounded-lg p-2 flex items-center gap-2">
                            <div class="w-4 h-4 bg-gray-800 rounded"></div>
                            <span class="text-xs">Ejecutivo Negro</span>
                        </div>
                        <div class="border rounded-lg p-2 flex items-center gap-2">
                            <div class="w-4 h-4 bg-orange-500 rounded"></div>
                            <span class="text-xs">Energía Naranja</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Hacé clic en cualquier preset para aplicarlo automáticamente.</p>
                `
            },
            {
                titulo: 'Paso 3: Personalizar colores',
                icono: 'sliders',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Ajustá cada color individualmente:</p>
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium">Color Primario</span>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-600 rounded border-2 border-white shadow"></div>
                                <span class="text-xs text-gray-500">#2563eb</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium">Color Secundario</span>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-800 rounded border-2 border-white shadow"></div>
                                <span class="text-xs text-gray-500">#1e40af</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Hacé clic en el cuadro de color para abrir el selector.</p>
                `
            },
            {
                titulo: 'Paso 4: Verificar preview',
                icono: 'eye',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Mirá el preview del header para ver el resultado:</p>
                    <div class="border rounded-lg overflow-hidden mb-4">
                        <div class="h-12 bg-gradient-to-r from-blue-600 to-blue-700 flex items-center px-4">
                            <div class="w-8 h-8 bg-white/20 rounded"></div>
                            <span class="ml-3 text-white text-sm">Mi Institución</span>
                        </div>
                    </div>
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm">
                        <p class="text-green-700"><i data-lucide="info" class="w-4 h-4 inline mr-1"></i> El preview se actualiza en tiempo real mientras elegís colores.</p>
                    </div>
                `
            },
            {
                titulo: 'Paso 5: Guardar',
                icono: 'save',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Cuando estés conforme con los colores:</p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 text-center">
                        <button class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium">
                            <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                            Guardar Apariencia
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 text-center">Los colores se aplicarán a todo el sitio y documentos.</p>
                `
            }
        ]
    },
    'activar-modulos': {
        titulo: 'Cómo activar módulos',
        pasos: [
            {
                titulo: 'Paso 1: Ir a Configuración',
                icono: 'settings',
                color: 'gray',
                contenido: `
                    <p class="text-gray-600 mb-4">Los módulos se gestionan en la pestaña "Configuración":</p>
                    <div class="flex gap-2 mb-4">
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Institución</span>
                        <span class="px-3 py-1.5 bg-gray-200 text-gray-600 rounded-lg text-sm">Apariencia</span>
                        <span class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium">Configuración</span>
                    </div>
                    <p class="text-sm text-gray-500">Hacé clic en la pestaña "Configuración".</p>
                `
            },
            {
                titulo: 'Paso 2: Ubicar los módulos',
                icono: 'layout-grid',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Encontrá la sección "Módulos Disponibles":</p>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div class="border rounded-lg p-3 flex items-center gap-2">
                            <i data-lucide="award" class="w-5 h-5 text-yellow-500"></i>
                            <span class="text-sm">Certificatum</span>
                        </div>
                        <div class="border rounded-lg p-3 flex items-center gap-2">
                            <i data-lucide="pen-tool" class="w-5 h-5 text-purple-500"></i>
                            <span class="text-sm">Scripta</span>
                        </div>
                        <div class="border rounded-lg p-3 flex items-center gap-2">
                            <i data-lucide="users" class="w-5 h-5 text-blue-500"></i>
                            <span class="text-sm">Nexus</span>
                        </div>
                        <div class="border rounded-lg p-3 flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-5 h-5 text-green-500"></i>
                            <span class="text-sm">IA (OpenAI)</span>
                        </div>
                    </div>
                `
            },
            {
                titulo: 'Paso 3: Activar/Desactivar',
                icono: 'toggle-right',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Hacé clic en la card del módulo para cambiar su estado:</p>
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i data-lucide="award" class="w-5 h-5 text-green-600"></i>
                                <span class="font-medium text-green-800">Certificatum</span>
                            </div>
                            <span class="px-2 py-1 bg-green-600 text-white text-xs rounded">ACTIVO</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i data-lucide="pen-tool" class="w-5 h-5 text-gray-400"></i>
                                <span class="font-medium text-gray-600">Scripta</span>
                            </div>
                            <span class="px-2 py-1 bg-gray-400 text-white text-xs rounded">INACTIVO</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Los módulos activos cambian a color verde.</p>
                `
            },
            {
                titulo: 'Paso 4: Guardar',
                icono: 'save',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">No olvides guardar los cambios:</p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 text-center">
                        <button class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium">
                            <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                            Guardar Configuración
                        </button>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                        <p class="text-blue-700"><i data-lucide="info" class="w-4 h-4 inline mr-1"></i> Los módulos activados aparecerán como pestañas en el menú superior.</p>
                    </div>
                `
            }
        ]
    },
    'configurar-firmantes': {
        titulo: 'Cómo configurar firmantes',
        pasos: [
            {
                titulo: 'Paso 1: Ubicar la sección',
                icono: 'pen-tool',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Los firmantes están en Configuración, sección "Firmantes":</p>
                    <div class="bg-gray-100 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 text-gray-700 font-medium mb-2">
                            <i data-lucide="pen-tool" class="w-5 h-5"></i>
                            Firmantes de Documentos
                        </div>
                        <p class="text-sm text-gray-500">Hasta 2 firmantes para certificados</p>
                    </div>
                    <p class="text-sm text-gray-500">Scrolleá hacia abajo en la pestaña Configuración.</p>
                `
            },
            {
                titulo: 'Paso 2: Completar datos',
                icono: 'user',
                color: 'purple',
                contenido: `
                    <p class="text-gray-600 mb-4">Ingresá los datos del firmante:</p>
                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="text-xs text-gray-500">Nombre completo</label>
                            <div class="border rounded-lg p-2 bg-gray-50 text-sm">Dra. María García</div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Cargo</label>
                            <div class="border rounded-lg p-2 bg-gray-50 text-sm">Directora Académica</div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Estos datos aparecerán debajo de la firma en los documentos.</p>
                `
            },
            {
                titulo: 'Paso 3: Subir la firma',
                icono: 'upload',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Subí la imagen de la firma digitalizada:</p>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 mb-4 text-center">
                        <div class="w-32 h-16 mx-auto mb-2 bg-gray-100 rounded flex items-center justify-center">
                            <svg class="w-24 h-10 text-gray-400" viewBox="0 0 100 40">
                                <path d="M10,30 Q30,10 50,25 T90,15" fill="none" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500">PNG con fondo transparente</p>
                    </div>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm">
                        <p class="text-amber-700"><i data-lucide="lightbulb" class="w-4 h-4 inline mr-1"></i> Tamaño recomendado: 300x100 píxeles</p>
                    </div>
                `
            },
            {
                titulo: 'Paso 4: Verificar preview',
                icono: 'eye',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Revisá cómo se verá en el documento:</p>
                    <div class="border rounded-lg p-4 mb-4 text-center">
                        <div class="w-32 h-16 mx-auto mb-2 bg-gray-50 rounded flex items-center justify-center">
                            <svg class="w-24 h-10 text-gray-600" viewBox="0 0 100 40">
                                <path d="M10,30 Q30,10 50,25 T90,15" fill="none" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="border-t border-gray-300 w-32 mx-auto mb-1"></div>
                        <p class="font-medium text-sm">Dra. María García</p>
                        <p class="text-xs text-gray-500">Directora Académica</p>
                    </div>
                `
            },
            {
                titulo: 'Paso 5: Guardar',
                icono: 'save',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Guardá los cambios para aplicar el firmante:</p>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4 text-center">
                        <button class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium">
                            <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                            Guardar Configuración
                        </button>
                    </div>
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm">
                        <p class="text-green-700"><i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i> ¡Listo! La firma aparecerá en todos los certificados.</p>
                    </div>
                `
            }
        ]
    },
    'configuracion-inicial': {
        titulo: 'Configuración inicial de tu institución',
        pasos: [
            {
                titulo: 'Paso 1: Información básica',
                icono: 'building-2',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Lo primero es configurar los datos de tu institución:</p>
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center gap-3 p-3 bg-green-50 rounded-lg border border-green-200">
                            <i data-lucide="type" class="w-5 h-5 text-green-600"></i>
                            <div>
                                <p class="font-medium text-green-800">Nombre de la institución</p>
                                <p class="text-xs text-green-600">Aparecerá en certificados y encabezados</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <i data-lucide="file-text" class="w-5 h-5 text-blue-600"></i>
                            <div>
                                <p class="font-medium text-blue-800">Descripción breve</p>
                                <p class="text-xs text-blue-600">Para SEO y metadatos del sitio</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Ir a la pestaña "Institución" y completar estos campos.</p>
                `
            },
            {
                titulo: 'Paso 2: Subir el logo',
                icono: 'image',
                color: 'blue',
                contenido: `
                    <p class="text-gray-600 mb-4">Tu logo es fundamental para la identidad visual:</p>
                    <div class="bg-gray-100 rounded-lg p-4 mb-4 text-center">
                        <div class="w-24 h-16 mx-auto bg-white rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center mb-2">
                            <i data-lucide="image" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-600">PNG con fondo transparente</p>
                        <p class="text-xs text-gray-500">Recomendado: 400x100px</p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600">✓ Subí tu logo en la sección correspondiente</p>
                        <p class="text-sm text-gray-600">✓ Elegí el estilo (rectangular, circular, etc.)</p>
                    </div>
                `
            },
            {
                titulo: 'Paso 3: Definir colores',
                icono: 'palette',
                color: 'purple',
                contenido: `
                    <p class="text-gray-600 mb-4">Los colores definen tu marca en toda la plataforma:</p>
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium">Color Primario</span>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-blue-600 rounded shadow"></div>
                                <span class="text-xs text-gray-500">Headers, botones</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium">Color Secundario</span>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-blue-800 rounded shadow"></div>
                                <span class="text-xs text-gray-500">Acentos, fondos</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium">Color Acento</span>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-amber-500 rounded shadow"></div>
                                <span class="text-xs text-gray-500">Destacados</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">Ir a "Apariencia" y elegir una paleta o personalizar.</p>
                `
            },
            {
                titulo: 'Paso 4: Configurar firmantes',
                icono: 'pen-tool',
                color: 'amber',
                contenido: `
                    <p class="text-gray-600 mb-4">Los certificados necesitan firmas autorizadas:</p>
                    <div class="border rounded-lg p-4 mb-4 text-center">
                        <div class="w-32 h-12 mx-auto mb-2 bg-gray-50 rounded flex items-center justify-center">
                            <svg class="w-24 h-8 text-gray-400" viewBox="0 0 100 40">
                                <path d="M10,30 Q30,10 50,25 T90,15" fill="none" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </div>
                        <div class="border-t border-gray-300 w-32 mx-auto mb-1"></div>
                        <p class="font-medium text-sm">Nombre del firmante</p>
                        <p class="text-xs text-gray-500">Cargo oficial</p>
                    </div>
                    <p class="text-sm text-gray-500">Ir a "Configuración" > "Firmantes" y completar los datos.</p>
                `
            },
            {
                titulo: 'Paso 5: Activar módulos',
                icono: 'layout-grid',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Elegí qué funcionalidades necesitás:</p>
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div class="flex items-center gap-2 p-2 bg-green-50 border border-green-200 rounded-lg">
                            <i data-lucide="award" class="w-4 h-4 text-green-600"></i>
                            <span class="text-xs font-medium">Certificatum</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-gray-50 border border-gray-200 rounded-lg">
                            <i data-lucide="globe" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-xs">Identitas</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-gray-50 border border-gray-200 rounded-lg">
                            <i data-lucide="pen-tool" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-xs">Scripta</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-gray-50 border border-gray-200 rounded-lg">
                            <i data-lucide="users" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-xs">Nexus</span>
                        </div>
                    </div>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm">
                        <p class="text-amber-700"><i data-lucide="lightbulb" class="w-4 h-4 inline mr-1"></i> Activá solo los módulos que usarás. Siempre podés agregar más después.</p>
                    </div>
                `
            },
            {
                titulo: 'Paso 6: ¡Todo listo!',
                icono: 'check-circle',
                color: 'green',
                contenido: `
                    <p class="text-gray-600 mb-4">Tu institución está configurada. Ahora podés:</p>
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center gap-3 p-3 bg-green-50 rounded-lg border border-green-200">
                            <i data-lucide="users" class="w-5 h-5 text-green-600"></i>
                            <span class="text-sm">Crear estudiantes en <strong>Certificatum</strong></span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <i data-lucide="book-open" class="w-5 h-5 text-blue-600"></i>
                            <span class="text-sm">Crear cursos y matricular estudiantes</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-lg border border-purple-200">
                            <i data-lucide="award" class="w-5 h-5 text-purple-600"></i>
                            <span class="text-sm">Generar certificados digitales</span>
                        </div>
                    </div>
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm">
                        <p class="text-green-700"><i data-lucide="rocket" class="w-4 h-4 inline mr-1"></i> <strong>¡Felicitaciones!</strong> Tu institución está lista para empezar.</p>
                    </div>
                `
            }
        ]
    }
};

let tutorialActual = null;
let pasoActual = 0;

function abrirTutorial(tutorialId) {
    const tutorial = tutorialesGeneral[tutorialId];
    if (!tutorial) return;

    tutorialActual = tutorial;
    pasoActual = 0;

    document.getElementById('modal-tutorial').classList.remove('hidden');
    document.getElementById('tutorial-titulo').textContent = tutorial.titulo;

    // Generar dots
    const dotsContainer = document.getElementById('tutorial-dots');
    dotsContainer.innerHTML = tutorial.pasos.map((_, i) =>
        `<button onclick="irAPaso(${i})" class="w-2 h-2 rounded-full transition-all ${i === 0 ? 'bg-blue-600 w-4' : 'bg-gray-300 hover:bg-gray-400'}"></button>`
    ).join('');

    mostrarPaso(0);
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function cerrarTutorial() {
    document.getElementById('modal-tutorial').classList.add('hidden');
    tutorialActual = null;
    pasoActual = 0;
}

function mostrarPaso(index) {
    if (!tutorialActual) return;

    pasoActual = index;
    const paso = tutorialActual.pasos[index];
    const total = tutorialActual.pasos.length;

    // Actualizar progreso
    const progreso = ((index + 1) / total) * 100;
    document.getElementById('tutorial-progreso').style.width = `${progreso}%`;
    document.getElementById('tutorial-contador').textContent = `${index + 1}/${total}`;

    // Actualizar contenido
    const colores = {
        blue: 'bg-blue-100 text-blue-600',
        green: 'bg-green-100 text-green-600',
        purple: 'bg-purple-100 text-purple-600',
        amber: 'bg-amber-100 text-amber-600',
        gray: 'bg-gray-100 text-gray-600'
    };

    document.getElementById('tutorial-contenido').innerHTML = `
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 ${colores[paso.color] || colores.blue} rounded-xl flex items-center justify-center">
                <i data-lucide="${paso.icono}" class="w-6 h-6"></i>
            </div>
            <h4 class="text-lg font-bold text-gray-800">${paso.titulo}</h4>
        </div>
        <div class="text-gray-600">
            ${paso.contenido}
        </div>
    `;

    // Actualizar dots
    document.querySelectorAll('#tutorial-dots button').forEach((dot, i) => {
        if (i === index) {
            dot.className = 'w-4 h-2 rounded-full bg-blue-600 transition-all';
        } else if (i < index) {
            dot.className = 'w-2 h-2 rounded-full bg-blue-400 transition-all hover:bg-blue-500';
        } else {
            dot.className = 'w-2 h-2 rounded-full bg-gray-300 transition-all hover:bg-gray-400';
        }
    });

    // Actualizar botones
    const btnAnterior = document.getElementById('btn-tutorial-anterior');
    const btnSiguiente = document.getElementById('btn-tutorial-siguiente');

    btnAnterior.disabled = index === 0;

    if (index === total - 1) {
        btnSiguiente.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Finalizar';
        btnSiguiente.onclick = cerrarTutorial;
    } else {
        btnSiguiente.innerHTML = 'Siguiente <i data-lucide="chevron-right" class="w-4 h-4"></i>';
        btnSiguiente.onclick = tutorialSiguiente;
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function tutorialAnterior() {
    if (pasoActual > 0) {
        mostrarPaso(pasoActual - 1);
    }
}

function tutorialSiguiente() {
    if (tutorialActual && pasoActual < tutorialActual.pasos.length - 1) {
        mostrarPaso(pasoActual + 1);
    }
}

function irAPaso(index) {
    mostrarPaso(index);
}

// Función para toggle de categorías del FAQ
function toggleFaqCategoria(categoriaId) {
    const contenido = document.getElementById(categoriaId);
    const icono = document.getElementById(categoriaId + '-icon');

    if (contenido) {
        contenido.classList.toggle('hidden');
        if (icono) {
            icono.classList.toggle('rotate-180');
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}
</script>
