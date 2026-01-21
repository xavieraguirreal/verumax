<?php
/**
 * MODULO: IDENTITAS TEMPLATES
 * Sistema dinamico de gestion de templates y bloques de contenido
 * Sin codigo hardcodeado - todo se genera desde las definiciones de campos
 */

// Ya estamos autenticados por index.php
// $admin ya esta disponible

// Cargar configuración global (contiene VERUMAX_IA_API_KEY)
require_once __DIR__ . '/../../config.php';

// Cargar config de Identitas
require_once __DIR__ . '/../../identitas/config.php';

use VERUMax\Services\TemplateService;
use VERUMax\Services\DatabaseService;

$slug = $admin['slug'];
$pdo = getDBConnection();

// Obtener datos de la institucion para autocompletado
$instance = getInstanceConfig($slug);
$redes_sociales = json_decode($instance['redes_sociales'] ?? '{}', true);

// Verificar si IA esta habilitada para esta institucion
// La API key es centralizada en config.php, solo verificamos si la institucion tiene IA activa
$ia_habilitada = !empty($instance['ia_habilitada']);

// ============================================================================
// DATOS PARA AUTOCOMPLETADO
// Mapeo: campo_nombre => [datos_institucion, datos_estandar_fallback]
// ============================================================================
$datos_autocompletar = [
    // Campos de texto generales
    'titulo' => $instance['nombre_completo'] ?? $instance['nombre'] ?? 'Nuestra Institucion',
    'subtitulo' => 'Liderando el camino hacia la excelencia',
    'texto' => 'Somos una institucion comprometida con la excelencia y la innovacion. Trabajamos dia a dia para brindar el mejor servicio a nuestra comunidad.',
    'descripcion' => 'Con anos de experiencia y un equipo de profesionales dedicados, nos hemos posicionado como referentes en nuestro sector.',

    // Mision/Vision/Valores
    'mision' => 'Nuestra mision es proporcionar servicios de la mas alta calidad, contribuyendo al desarrollo integral de nuestra comunidad con compromiso, etica y responsabilidad social.',
    'vision' => 'Ser reconocidos como la institucion lider en nuestro campo, destacandonos por la excelencia, la innovacion y el impacto positivo en la sociedad.',
    'valores' => 'Compromiso, Excelencia, Integridad, Innovacion, Responsabilidad Social',

    // Datos de contacto
    'email' => $instance['email_contacto'] ?? 'contacto@ejemplo.com',
    'telefono' => $redes_sociales['whatsapp'] ?? '+54 11 1234-5678',
    'direccion' => 'Av. Principal 1234, Ciudad',
    'horario' => 'Lunes a Viernes de 9:00 a 18:00',

    // URLs y enlaces
    'url' => $instance['sitio_web_oficial'] ?? 'https://www.ejemplo.com',
    'boton_url' => '/contacto',
    'link' => '#',

    // Textos de botones
    'boton_texto' => 'Contactanos',
    'cta_texto' => 'Solicitar Informacion',

    // Redes sociales
    'instagram' => $redes_sociales['instagram'] ?? '@institucion',
    'facebook' => $redes_sociales['facebook'] ?? 'https://facebook.com/institucion',
    'linkedin' => $redes_sociales['linkedin'] ?? 'https://linkedin.com/company/institucion',
    'twitter' => $redes_sociales['twitter'] ?? '@institucion',
    'youtube' => $redes_sociales['youtube'] ?? 'https://youtube.com/@institucion',
    'whatsapp' => $redes_sociales['whatsapp'] ?? '+54 9 11 1234-5678',

    // Nombre de institucion
    'nombre' => $instance['nombre'] ?? 'Nombre de la Institucion',
    'nombre_completo' => $instance['nombre_completo'] ?? 'Nombre Completo de la Institucion',
    'nombre_institucion' => $instance['nombre_completo'] ?? $instance['nombre'] ?? 'Nuestra Institucion',

    // Campos de equipo/persona
    'cargo' => 'Director/a General',
    'foto' => 'https://via.placeholder.com/200x200?text=Foto',
    'bio' => 'Profesional con amplia experiencia en el sector, liderando equipos hacia la excelencia.',

    // Servicios
    'nombre_servicio' => 'Servicio Profesional',
    'descripcion_servicio' => 'Ofrecemos soluciones integrales adaptadas a las necesidades de cada cliente.',
    'precio' => 'Consultar',

    // Estadisticas
    'numero' => '100+',
    'etiqueta' => 'Clientes Satisfechos',
    'stat_valor' => '500+',
    'stat_label' => 'Proyectos Realizados',

    // Timeline/Historia
    'ano' => date('Y'),
    'fecha' => date('Y'),
    'evento' => 'Hito importante en nuestra historia',
    'detalle' => 'Descripcion del evento o logro alcanzado.',

    // FAQ
    'pregunta' => 'Cual es el horario de atencion?',
    'respuesta' => 'Nuestro horario de atencion es de lunes a viernes de 9:00 a 18:00 hs.',

    // Testimonios
    'testimonio' => 'Excelente servicio, muy profesionales y atentos. Totalmente recomendados.',
    'autor' => 'Cliente Satisfecho',

    // Areas/Categorias
    'area' => 'Area de Especializacion',
    'categoria' => 'Categoria Principal',

    // Publicaciones/Investigacion
    'titulo_publicacion' => 'Titulo de la Publicacion',
    'revista' => 'Revista Especializada',
    'autores' => 'Autor Principal, Co-autor',

    // Iconos default
    'icono' => 'award',
    'icon' => 'star',
];

// Verificar si las tablas de templates existen
$tablas_ok = false;
try {
    $pdo->query("SELECT 1 FROM identitas_templates LIMIT 1");
    $pdo->query("SELECT 1 FROM identitas_instancia_templates LIMIT 1");
    $pdo->query("SELECT 1 FROM identitas_contenido_bloques LIMIT 1");
    $tablas_ok = true;
} catch (\PDOException $e) {
    $tablas_ok = false;
}

if (!$tablas_ok):
?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg">
    <div class="flex items-start gap-4">
        <i data-lucide="alert-triangle" class="w-8 h-8 text-yellow-600 flex-shrink-0"></i>
        <div>
            <h3 class="text-lg font-bold text-yellow-800 mb-2">Sistema de Templates no configurado</h3>
            <p class="text-yellow-700 mb-4">
                Las tablas necesarias para el sistema de templates aun no existen en la base de datos.
            </p>
            <div class="bg-white rounded-lg p-4 border border-yellow-200">
                <p class="text-sm text-gray-700 mb-2"><strong>Para activar esta funcionalidad:</strong></p>
                <ol class="list-decimal list-inside text-sm text-gray-600 space-y-1">
                    <li>Abre phpMyAdmin en el servidor</li>
                    <li>Selecciona la base de datos <code class="bg-gray-100 px-1 rounded">identitas</code></li>
                    <li>Ve a la pestana "SQL"</li>
                    <li>Ejecuta el contenido del archivo <code class="bg-gray-100 px-1 rounded">SQL_TEMPLATES_IDENTITAS.sql</code></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<?php
return;
endif;

// Obtener instancia
$stmt = $pdo->prepare("SELECT * FROM identitas_instances WHERE slug = :slug");
$stmt->execute(['slug' => $slug]);
$instance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$instance) {
    die('Error: Instancia no encontrada');
}

$id_instancia = $instance['id_instancia'];

// Manejar POST
$mensaje = '';
$tipo_mensaje = '';
$scroll_to = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // === ACCIONES AJAX DE IA (devuelven JSON y hacen exit) ===

    // Generar campos combinados con IA
    if ($accion === 'generar_combinado_ia') {
        header('Content-Type: application/json');

        if (!class_exists('VERUMax\\Services\\OpenAIService')) {
            require_once __DIR__ . '/../../src/VERUMax/Services/OpenAIService.php';
        }

        if (!\VERUMax\Services\OpenAIService::isEnabledForInstitution($slug)) {
            echo json_encode(['success' => false, 'error' => 'IA no habilitada']);
            exit;
        }

        $groupType = $_POST['group_type'] ?? '';
        $existingValues = isset($_POST['existing_values']) ? json_decode($_POST['existing_values'], true) : [];

        $context = [
            'nombre' => $instance['nombre'] ?? '',
            'nombre_completo' => $instance['nombre_completo'] ?? '',
            'tipo' => 'institución educativa',
            'existing_values' => $existingValues
        ];

        $result = \VERUMax\Services\OpenAIService::generarCamposCombinados($groupType, $context);
        echo json_encode($result);
        exit;
    }

    // Autocompletar campo con IA
    if ($accion === 'autocompletar_ia') {
        header('Content-Type: application/json');

        if (!class_exists('VERUMax\\Services\\OpenAIService')) {
            require_once __DIR__ . '/../../src/VERUMax/Services/OpenAIService.php';
        }

        if (!\VERUMax\Services\OpenAIService::isEnabledForInstitution($slug)) {
            echo json_encode(['success' => false, 'error' => 'IA no habilitada']);
            exit;
        }

        $fieldName = $_POST['field_name'] ?? '';
        $fieldLabel = $_POST['field_label'] ?? '';
        $fieldType = $_POST['field_type'] ?? 'text';
        $bloque = $_POST['bloque'] ?? '';
        $existingValues = isset($_POST['existing_values']) ? json_decode($_POST['existing_values'], true) : [];

        $context = [
            'nombre' => $instance['nombre'] ?? '',
            'nombre_completo' => $instance['nombre_completo'] ?? '',
            'tipo' => 'institución educativa',
            'bloque' => $bloque,
            'existing_values' => $existingValues
        ];

        $result = \VERUMax\Services\OpenAIService::autocompletarCampo($fieldName, $fieldLabel, $fieldType, $context);
        echo json_encode($result);
        exit;
    }

    // Generar imagen con IA
    if ($accion === 'generar_imagen_ia') {
        header('Content-Type: application/json');

        if (!class_exists('VERUMax\\Services\\OpenAIService')) {
            require_once __DIR__ . '/../../src/VERUMax/Services/OpenAIService.php';
        }

        if (!\VERUMax\Services\OpenAIService::isEnabledForInstitution($slug)) {
            echo json_encode(['success' => false, 'error' => 'IA no habilitada']);
            exit;
        }

        $context = [
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'anio' => $_POST['anio'] ?? '',
            'nombre_institucion' => $instance['nombre_completo'] ?? $instance['nombre'] ?? '',
            'size' => $_POST['size'] ?? '1024x1024'
        ];

        $result = \VERUMax\Services\OpenAIService::generarImagen($context);
        echo json_encode($result);
        exit;
    }

    switch ($accion) {
        case 'seleccionar_template':
            $pagina = $_POST['pagina'] ?? '';
            $id_template = intval($_POST['id_template'] ?? 0);

            if ($pagina && $id_template) {
                if (TemplateService::setSelectedTemplate($id_instancia, $pagina, $id_template)) {
                    $mensaje = 'Template seleccionado correctamente';
                    $tipo_mensaje = 'success';
                    $scroll_to = 'template-selector';
                } else {
                    $mensaje = 'Error al seleccionar template';
                    $tipo_mensaje = 'error';
                }
            }
            break;

        case 'guardar_bloque':
            $pagina = $_POST['pagina'] ?? '';
            $tipo_bloque = $_POST['tipo_bloque'] ?? '';
            $contenido = $_POST['contenido'] ?? [];

            if ($pagina && $tipo_bloque && !empty($contenido)) {
                if (TemplateService::saveBloqueContent($id_instancia, $pagina, $tipo_bloque, $contenido)) {
                    $mensaje = 'Contenido del bloque "' . ucfirst(str_replace('_', ' ', $tipo_bloque)) . '" guardado correctamente';
                    $tipo_mensaje = 'success';
                    $scroll_to = 'bloque-' . $tipo_bloque;
                } else {
                    $mensaje = 'Error al guardar contenido';
                    $tipo_mensaje = 'error';
                }
            }
            break;
    }
}

// Obtener datos
$paginas_disponibles = ['sobre-nosotros', 'servicios', 'contacto'];
$pagina_actual = $_GET['pagina'] ?? 'sobre-nosotros';

// Templates disponibles para esta pagina
$templates = TemplateService::getTemplatesForPage($pagina_actual);

// Template seleccionado
$template_seleccionado = TemplateService::getSelectedTemplate($id_instancia, $pagina_actual);

// Si no hay template seleccionado, usar el primero
if (!$template_seleccionado && !empty($templates)) {
    $template_seleccionado = $templates[0];
}

// Obtener bloques del template seleccionado
$bloques_template = [];
if ($template_seleccionado) {
    $bloques_template = TemplateService::getTemplateBloques($template_seleccionado['id_template']);
}

// Contenido actual de todos los bloques
$contenido_pagina = TemplateService::getPageContent($id_instancia, $pagina_actual);

// Iconos disponibles para selectores
$iconos_disponibles = [
    'award', 'users', 'book-open', 'lightbulb', 'search', 'calendar', 'briefcase',
    'heart', 'star', 'zap', 'shield', 'globe', 'target', 'eye', 'check-circle',
    'file-text', 'folder', 'home', 'mail', 'phone', 'map-pin', 'clock', 'tag',
    'thumbs-up', 'trending-up', 'bar-chart-2', 'pie-chart', 'activity', 'cpu',
    'database', 'layers', 'layout', 'monitor', 'smartphone', 'tablet', 'tv',
    'video', 'image', 'music', 'headphones', 'mic', 'camera', 'film', 'youtube',
    'linkedin', 'twitter', 'facebook', 'instagram', 'github', 'link', 'external-link'
];

/**
 * Funcion para renderizar un campo de formulario basado en su definicion
 * Incluye boton de autocompletar con datos de la institucion o estandar
 *
 * @param array $field Definicion del campo
 * @param mixed $value Valor actual
 * @param string $namePrefix Prefijo para el name del input
 * @param int|null $fieldIndex Indice para repeaters
 * @param string $bloqueContext Contexto del bloque (stats, equipo, etc.)
 */
function renderFormField($field, $value, $namePrefix, $fieldIndex = null, $bloqueContext = '') {
    global $iconos_disponibles, $datos_autocompletar, $ia_habilitada;

    $name = $field['name'];
    $type = $field['type'];
    $label = $field['label'];
    $required = $field['required'] ?? false;
    $placeholder = $field['placeholder'] ?? '';

    $fieldName = $namePrefix . '[' . $name . ']';
    // Incluir bloqueContext en el ID para evitar duplicados entre bloques
    $fieldIdBase = 'field-' . str_replace(['[', ']'], ['-', ''], $fieldName);
    $fieldId = !empty($bloqueContext) ? $fieldIdBase . '-' . $bloqueContext : $fieldIdBase;

    $requiredAttr = $required ? 'required' : '';
    $requiredStar = $required ? '<span class="text-red-500">*</span>' : '';

    // Determinar si el campo puede tener autocompletado
    $tipos_con_autocompletar = ['text', 'textarea', 'editor', 'url', 'email'];
    $tiene_autocompletar = in_array($type, $tipos_con_autocompletar);

    // Obtener valor de autocompletado para este campo
    $valor_autocompletar = '';
    if ($tiene_autocompletar) {
        // Buscar por nombre exacto o variaciones
        $valor_autocompletar = $datos_autocompletar[$name] ?? '';

        // Si no encontro, buscar por palabras clave en el nombre del campo
        if (empty($valor_autocompletar)) {
            $name_lower = strtolower($name);
            foreach ($datos_autocompletar as $key => $val) {
                if (strpos($name_lower, $key) !== false || strpos($key, $name_lower) !== false) {
                    $valor_autocompletar = $val;
                    break;
                }
            }
        }
    }

    $html = '<div class="mb-4">';

    // Detectar si este campo es parte de un grupo combinado
    // Grupos: cita+autor_cita, titulo+subtitulo (en equipo), intro_academica, area_investigacion, etc.
    $es_campo_combinado_principal = false;
    $es_campo_combinado_secundario = false;
    $grupo_combinado = '';
    $campos_grupo_ids = [];
    $es_repeater_item = ($fieldIndex !== null);

    // ========== BLOQUE INTRO_ACADEMICA ==========
    // Campo "titulo" en intro_academica genera tambien "texto"
    if ($name === 'titulo' && $bloqueContext === 'intro_academica') {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'intro_academica';
        $texto_fieldId = str_replace('-titulo-', '-texto-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'texto' => $texto_fieldId
        ];
    }
    // Campo "texto" en intro_academica - no mostrar boton IA individual
    if ($name === 'texto' && $bloqueContext === 'intro_academica') {
        $es_campo_combinado_secundario = true;
    }

    // Campo "cita" genera tambien "autor_cita"
    if ($name === 'cita' && $bloqueContext === 'intro_academica') {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'cita_autor';
        $autor_fieldId = str_replace('-cita-', '-autor_cita-', $fieldId);
        $campos_grupo_ids = [
            'cita' => $fieldId,
            'autor_cita' => $autor_fieldId
        ];
    }
    // Campo "autor_cita" - no mostrar boton IA individual
    if ($name === 'autor_cita') {
        $es_campo_combinado_secundario = true;
    }

    // ========== BLOQUE EQUIPO ==========
    // Campo "titulo" en equipo genera tambien "subtitulo"
    if ($name === 'titulo' && $bloqueContext === 'equipo') {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'titulo_subtitulo';
        $subtitulo_fieldId = str_replace('-titulo-', '-subtitulo-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'subtitulo' => $subtitulo_fieldId
        ];
    }
    // Campo "subtitulo" en equipo - no mostrar boton IA individual
    if ($name === 'subtitulo' && $bloqueContext === 'equipo') {
        $es_campo_combinado_secundario = true;
    }

    // ========== BLOQUE AREAS_INVESTIGACION (REPEATER) ==========
    // Campo "icono" en areas_investigacion genera tambien "nombre" y "descripcion"
    if ($name === 'icono' && $bloqueContext === 'areas_investigacion' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'area_investigacion';
        // En repeaters, el ID tiene formato: field-contenido-areas-N-icono-areas_investigacion
        // Necesitamos reemplazar el nombre del campo manteniendo el indice
        $nombre_fieldId = preg_replace('/-icono-/', '-nombre-', $fieldId);
        $descripcion_fieldId = preg_replace('/-icono-/', '-descripcion-', $fieldId);
        $campos_grupo_ids = [
            'icono' => $fieldId,
            'nombre' => $nombre_fieldId,
            'descripcion' => $descripcion_fieldId
        ];
    }
    // Campos secundarios en areas_investigacion
    if (($name === 'nombre' || $name === 'descripcion') && $bloqueContext === 'areas_investigacion' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== BLOQUE HERO_INSTITUCIONAL ==========
    // Campo "titulo" en hero_institucional genera tambien "subtitulo"
    if ($name === 'titulo' && $bloqueContext === 'hero_institucional') {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'hero';
        $subtitulo_fieldId = str_replace('-titulo-', '-subtitulo-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'subtitulo' => $subtitulo_fieldId
        ];
    }
    if ($name === 'subtitulo' && $bloqueContext === 'hero_institucional') {
        $es_campo_combinado_secundario = true;
    }

    // ========== BLOQUE MISION_VISION ==========
    // Campo "mision_titulo" genera tambien "mision_texto"
    if ($name === 'mision_titulo' && $bloqueContext === 'mision_vision') {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'mision';
        $texto_fieldId = str_replace('-mision_titulo-', '-mision_texto-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'texto' => $texto_fieldId
        ];
    }
    if ($name === 'mision_texto' && $bloqueContext === 'mision_vision') {
        $es_campo_combinado_secundario = true;
    }

    // Campo "vision_titulo" genera tambien "vision_texto"
    if ($name === 'vision_titulo' && $bloqueContext === 'mision_vision') {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'vision';
        $texto_fieldId = str_replace('-vision_titulo-', '-vision_texto-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'texto' => $texto_fieldId
        ];
    }
    if ($name === 'vision_texto' && $bloqueContext === 'mision_vision') {
        $es_campo_combinado_secundario = true;
    }

    // Repeater VALORES dentro de mision_vision
    // Campo "icono" en valores genera tambien "nombre" y "descripcion"
    if ($name === 'icono' && $bloqueContext === 'mision_vision' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'valor';
        $nombre_fieldId = preg_replace('/-icono-/', '-nombre-', $fieldId);
        $descripcion_fieldId = preg_replace('/-icono-/', '-descripcion-', $fieldId);
        $campos_grupo_ids = [
            'icono' => $fieldId,
            'nombre' => $nombre_fieldId,
            'descripcion' => $descripcion_fieldId
        ];
    }
    if (($name === 'nombre' || $name === 'descripcion') && $bloqueContext === 'mision_vision' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== BLOQUE EQUIPO ==========
    // Campo "titulo" del bloque equipo genera tambien "subtitulo" (campos directos, no repeater)
    if ($name === 'titulo' && $bloqueContext === 'equipo' && !$es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'titulo_subtitulo';
        $subtitulo_fieldId = str_replace('-titulo-', '-subtitulo-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'subtitulo' => $subtitulo_fieldId
        ];
    }
    if ($name === 'subtitulo' && $bloqueContext === 'equipo' && !$es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // Repeater MIEMBROS dentro de equipo
    // Campo "nombre" en miembros genera tambien "cargo" y "bio"
    if ($name === 'nombre' && $bloqueContext === 'equipo' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'miembro';
        $cargo_fieldId = preg_replace('/-nombre-/', '-cargo-', $fieldId);
        $bio_fieldId = preg_replace('/-nombre-/', '-bio-', $fieldId);
        $campos_grupo_ids = [
            'nombre' => $fieldId,
            'cargo' => $cargo_fieldId,
            'bio' => $bio_fieldId
        ];
    }
    if (($name === 'cargo' || $name === 'bio') && $bloqueContext === 'equipo' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== BLOQUE STATS (REPEATER) ==========
    // Campo "titulo" en stats genera tambien "texto"
    if ($name === 'titulo' && $bloqueContext === 'stats' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'stat';
        $texto_fieldId = preg_replace('/-titulo-/', '-texto-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'texto' => $texto_fieldId
        ];
    }
    if ($name === 'texto' && $bloqueContext === 'stats' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== BLOQUE INTRO_HISTORIA ==========
    // Campo "titulo" en intro_historia genera tambien "texto" y "anio_fundacion"
    if ($name === 'titulo' && $bloqueContext === 'intro_historia') {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'intro_historia';
        $texto_fieldId = str_replace('-titulo-', '-texto-', $fieldId);
        $anio_fieldId = str_replace('-titulo-', '-anio_fundacion-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'texto' => $texto_fieldId,
            'anio_fundacion' => $anio_fieldId
        ];
    }
    if (($name === 'texto' || $name === 'anio_fundacion') && $bloqueContext === 'intro_historia') {
        $es_campo_combinado_secundario = true;
    }

    // ========== BLOQUE TIMELINE_VERTICAL (REPEATER EVENTOS) ==========
    // Campo "anio" en eventos genera tambien "titulo" y "descripcion"
    if ($name === 'anio' && $bloqueContext === 'timeline_vertical' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'timeline_evento';
        $titulo_fieldId = preg_replace('/-anio-/', '-titulo-', $fieldId);
        $descripcion_fieldId = preg_replace('/-anio-/', '-descripcion-', $fieldId);
        $campos_grupo_ids = [
            'anio' => $fieldId,
            'titulo' => $titulo_fieldId,
            'descripcion' => $descripcion_fieldId
        ];
    }
    if (($name === 'titulo' || $name === 'descripcion') && $bloqueContext === 'timeline_vertical' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== SERVICIOS: SERVICIOS_CARDS (REPEATER ITEMS) ==========
    // Items de servicios: icono + titulo + descripcion
    if ($name === 'icono' && $bloqueContext === 'servicios_cards' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'servicio_card';
        $titulo_fieldId = preg_replace('/-icono-/', '-titulo-', $fieldId);
        $descripcion_fieldId = preg_replace('/-icono-/', '-descripcion-', $fieldId);
        $campos_grupo_ids = [
            'icono' => $fieldId,
            'titulo' => $titulo_fieldId,
            'descripcion' => $descripcion_fieldId
        ];
    }
    if (($name === 'titulo' || $name === 'descripcion') && $bloqueContext === 'servicios_cards' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== SERVICIOS: SERVICIOS_ACCORDION (REPEATER ITEMS) ==========
    // Items de accordion: icono + titulo + descripcion_corta + contenido
    // Permitir que tanto 'icono' como 'titulo' disparen la generacion combinada
    if ($name === 'icono' && $bloqueContext === 'servicios_accordion' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'servicio_accordion';
        $titulo_fieldId = preg_replace('/-icono-/', '-titulo-', $fieldId);
        $descripcion_fieldId = preg_replace('/-icono-/', '-descripcion_corta-', $fieldId);
        $contenido_fieldId = preg_replace('/-icono-/', '-contenido-', $fieldId);
        $campos_grupo_ids = [
            'icono' => $fieldId,
            'titulo' => $titulo_fieldId,
            'descripcion_corta' => $descripcion_fieldId,
            'contenido' => $contenido_fieldId
        ];
    }
    if ($name === 'titulo' && $bloqueContext === 'servicios_accordion' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'servicio_accordion';
        $icono_fieldId = preg_replace('/-titulo-/', '-icono-', $fieldId);
        $descripcion_fieldId = preg_replace('/-titulo-/', '-descripcion_corta-', $fieldId);
        $contenido_fieldId = preg_replace('/-titulo-/', '-contenido-', $fieldId);
        $campos_grupo_ids = [
            'icono' => $icono_fieldId,
            'titulo' => $fieldId,
            'descripcion_corta' => $descripcion_fieldId,
            'contenido' => $contenido_fieldId
        ];
    }
    if (($name === 'descripcion_corta' || $name === 'contenido') && $bloqueContext === 'servicios_accordion' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== SERVICIOS: SERVICIOS_HEADER (CAMPOS DE NIVEL SUPERIOR) ==========
    // Titulo + subtitulo del header
    if ($name === 'titulo' && $bloqueContext === 'servicios_header' && !$es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'servicios_header_texto';
        $subtitulo_fieldId = preg_replace('/-titulo-/', '-subtitulo-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'subtitulo' => $subtitulo_fieldId
        ];
    }
    if ($name === 'subtitulo' && $bloqueContext === 'servicios_header' && !$es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== SERVICIOS: FAQ_SERVICIOS (REPEATER PREGUNTAS) ==========
    // Preguntas: pregunta + respuesta
    if ($name === 'pregunta' && $bloqueContext === 'faq_servicios' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'faq_item';
        $respuesta_fieldId = preg_replace('/-pregunta-/', '-respuesta-', $fieldId);
        $campos_grupo_ids = [
            'pregunta' => $fieldId,
            'respuesta' => $respuesta_fieldId
        ];
    }
    if ($name === 'respuesta' && $bloqueContext === 'faq_servicios' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== SERVICIOS: TESTIMONIOS_SERVICIOS (REPEATER ITEMS) ==========
    // Items de testimonios: nombre + cargo + texto
    if ($name === 'nombre' && $bloqueContext === 'testimonios_servicios' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'testimonio';
        $cargo_fieldId = preg_replace('/-nombre-/', '-cargo-', $fieldId);
        $texto_fieldId = preg_replace('/-nombre-/', '-texto-', $fieldId);
        $campos_grupo_ids = [
            'nombre' => $fieldId,
            'cargo' => $cargo_fieldId,
            'texto' => $texto_fieldId
        ];
    }
    if (($name === 'cargo' || $name === 'texto') && $bloqueContext === 'testimonios_servicios' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== SERVICIOS: SERVICIOS_GRID (NIVEL SUPERIOR + REPEATER) ==========
    // Titulo_seccion + subtitulo (nivel superior)
    if ($name === 'titulo_seccion' && $bloqueContext === 'servicios_grid' && !$es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'servicios_grid_header';
        $subtitulo_fieldId = preg_replace('/-titulo_seccion-/', '-subtitulo-', $fieldId);
        $campos_grupo_ids = [
            'titulo_seccion' => $fieldId,
            'subtitulo' => $subtitulo_fieldId
        ];
    }
    if ($name === 'subtitulo' && $bloqueContext === 'servicios_grid' && !$es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }
    // Items del grid: titulo + texto
    if ($name === 'titulo' && $bloqueContext === 'servicios_grid' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'servicio_grid_item';
        $texto_fieldId = preg_replace('/-titulo-/', '-texto-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'texto' => $texto_fieldId
        ];
    }
    if ($name === 'texto' && $bloqueContext === 'servicios_grid' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== SERVICIOS: SERVICIOS_TABS (REPEATER CATEGORIAS) ==========
    // Categorias: nombre + servicios
    if ($name === 'nombre' && $bloqueContext === 'servicios_tabs' && $es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'servicio_tab';
        $servicios_fieldId = preg_replace('/-nombre-/', '-servicios-', $fieldId);
        $campos_grupo_ids = [
            'nombre' => $fieldId,
            'servicios' => $servicios_fieldId
        ];
    }
    if ($name === 'servicios' && $bloqueContext === 'servicios_tabs' && $es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // ========== SERVICIOS: CTA_SERVICIOS (CAMPOS DE NIVEL SUPERIOR) ==========
    // Titulo + texto + boton_texto
    if ($name === 'titulo' && $bloqueContext === 'cta_servicios' && !$es_repeater_item) {
        $es_campo_combinado_principal = true;
        $grupo_combinado = 'cta_servicios_texto';
        $texto_fieldId = preg_replace('/-titulo-/', '-texto-', $fieldId);
        $boton_fieldId = preg_replace('/-titulo-/', '-boton_texto-', $fieldId);
        $campos_grupo_ids = [
            'titulo' => $fieldId,
            'texto' => $texto_fieldId,
            'boton_texto' => $boton_fieldId
        ];
    }
    if (($name === 'texto' || $name === 'boton_texto') && $bloqueContext === 'cta_servicios' && !$es_repeater_item) {
        $es_campo_combinado_secundario = true;
    }

    // Botones de autocompletar HTML
    $autocompletarBtn = '';
    // Generar boton si el campo soporta autocompletar O si es campo combinado principal
    if ($tiene_autocompletar || $es_campo_combinado_principal) {
        $label_escaped = addslashes($label);
        $type_escaped = addslashes($type);

        // Boton IA (si esta habilitado)
        if ($ia_habilitada) {
            // Si es campo combinado principal, mostrar boton que genera multiples campos
            if ($es_campo_combinado_principal && !empty($campos_grupo_ids)) {
                $campos_json = json_encode($campos_grupo_ids);
                $autocompletarBtn .= '<button type="button"
                                             onclick="generarCamposCombinados(\'' . $grupo_combinado . '\', ' . htmlspecialchars($campos_json) . ')"
                                             class="ml-2 px-2 py-1 text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 rounded border border-purple-300 transition flex-shrink-0"
                                             title="Generar campos relacionados con IA">
                                        <i data-lucide="sparkles" class="w-3 h-3 inline"></i> IA (grupo)
                                    </button>';
            }
            // Si es campo secundario de un grupo, no mostrar boton IA individual
            elseif ($es_campo_combinado_secundario) {
                // No agregar boton IA - se genera desde el campo principal
            }
            // Campo normal - boton IA individual
            else {
                $bloque_escaped = addslashes($bloqueContext);
                $autocompletarBtn .= '<button type="button"
                                             onclick="autocompletarConIA(\'' . $fieldId . '\', \'' . addslashes($name) . '\', \'' . $label_escaped . '\', \'' . $type_escaped . '\', \'' . $bloque_escaped . '\')"
                                             class="ml-2 px-2 py-1 text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 rounded border border-purple-300 transition flex-shrink-0"
                                             title="Generar con Inteligencia Artificial">
                                        <i data-lucide="sparkles" class="w-3 h-3 inline"></i> IA
                                    </button>';
            }
        }

        // Boton datos estandar (siempre disponible si hay valor)
        if (!empty($valor_autocompletar)) {
            $autocompletarBtn .= '<button type="button"
                                         onclick="autocompletarCampo(\'' . $fieldId . '\', \'' . addslashes($valor_autocompletar) . '\')"
                                         class="ml-1 px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded border border-gray-300 transition flex-shrink-0"
                                         title="Autocompletar con datos predefinidos">
                                    <i data-lucide="file-text" class="w-3 h-3 inline"></i>
                                </button>';
        }
    }

    switch ($type) {
        case 'text':
            $html .= '<div class="flex items-center justify-between mb-1">';
            $html .= '<label class="block text-sm font-medium text-gray-700">' . htmlspecialchars($label) . ' ' . $requiredStar . '</label>';
            $html .= $autocompletarBtn;
            $html .= '</div>';
            $html .= '<input type="text" name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '"
                             value="' . htmlspecialchars($value ?? '') . '"
                             placeholder="' . htmlspecialchars($placeholder) . '"
                             class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" ' . $requiredAttr . '>';
            break;

        case 'textarea':
            $html .= '<div class="flex items-center justify-between mb-1">';
            $html .= '<label class="block text-sm font-medium text-gray-700">' . htmlspecialchars($label) . ' ' . $requiredStar . '</label>';
            $html .= $autocompletarBtn;
            $html .= '</div>';
            $html .= '<textarea name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '" rows="3"
                                placeholder="' . htmlspecialchars($placeholder) . '"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" ' . $requiredAttr . '>' . htmlspecialchars($value ?? '') . '</textarea>';
            break;

        case 'editor':
            $html .= '<div class="flex items-center justify-between mb-1">';
            $html .= '<label class="block text-sm font-medium text-gray-700">' . htmlspecialchars($label) . ' ' . $requiredStar . '</label>';
            $html .= $autocompletarBtn;
            $html .= '</div>';
            $html .= '<textarea name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '"
                                class="ckeditor-bloque w-full" ' . $requiredAttr . '>' . htmlspecialchars($value ?? '') . '</textarea>';
            break;

        case 'url':
            $html .= '<div class="flex items-center justify-between mb-1">';
            $html .= '<label class="block text-sm font-medium text-gray-700">' . htmlspecialchars($label) . ' ' . $requiredStar . '</label>';
            $html .= $autocompletarBtn;
            $html .= '</div>';
            $html .= '<input type="url" name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '"
                             value="' . htmlspecialchars($value ?? '') . '"
                             placeholder="https://..."
                             class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" ' . $requiredAttr . '>';
            break;

        case 'email':
            $html .= '<div class="flex items-center justify-between mb-1">';
            $html .= '<label class="block text-sm font-medium text-gray-700">' . htmlspecialchars($label) . ' ' . $requiredStar . '</label>';
            $html .= $autocompletarBtn;
            $html .= '</div>';
            $html .= '<input type="email" name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '"
                             value="' . htmlspecialchars($value ?? '') . '"
                             placeholder="email@ejemplo.com"
                             class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" ' . $requiredAttr . '>';
            break;

        case 'date':
            $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">' . htmlspecialchars($label) . ' ' . $requiredStar . '</label>';
            $html .= '<input type="date" name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '"
                             value="' . htmlspecialchars($value ?? '') . '"
                             class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" ' . $requiredAttr . '>';
            break;

        case 'checkbox':
            $checked = $value ? 'checked' : '';
            $html .= '<label class="flex items-center gap-2 cursor-pointer">';
            $html .= '<input type="checkbox" name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '" value="1"
                             class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500" ' . $checked . '>';
            $html .= '<span class="text-sm font-medium text-gray-700">' . htmlspecialchars($label) . '</span>';
            $html .= '</label>';
            break;

        case 'image':
            // Detectar si el campo de imagen debe tener boton IA
            $imagen_con_ia = false;
            $imagen_btn_ia = '';

            // Para imagenes en eventos de timeline
            if ($name === 'imagen' && $bloqueContext === 'timeline_vertical' && $es_repeater_item) {
                $imagen_con_ia = true;
                // Obtener IDs de los campos relacionados (anio, titulo, descripcion)
                $anio_fieldId = preg_replace('/-imagen-/', '-anio-', $fieldId);
                $titulo_fieldId = preg_replace('/-imagen-/', '-titulo-', $fieldId);
                $descripcion_fieldId = preg_replace('/-imagen-/', '-descripcion-', $fieldId);

                // Selector de aspecto + botón generar
                $sizeSelectId = $fieldId . '-size';
                $imagen_btn_ia = '<div class="flex items-center gap-2">
                    <select id="' . $sizeSelectId . '" class="text-sm border border-gray-300 rounded px-2 py-1" title="Aspecto de imagen">
                        <option value="1024x1024" selected>1:1 Cuadrado</option>
                        <option value="1792x1024">16:9 Horizontal</option>
                        <option value="1024x1792">9:16 Vertical</option>
                    </select>
                    <button type="button"
                        onclick="generarImagenIA(\'' . $fieldId . '\', \'' . $anio_fieldId . '\', \'' . $titulo_fieldId . '\', \'' . $descripcion_fieldId . '\', \'' . $sizeSelectId . '\')"
                        class="text-purple-600 hover:text-purple-700 font-medium text-sm flex items-center gap-1"
                        title="Generar imagen con IA">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Generar con IA
                    </button>
                </div>';
            }

            // Para imagen en servicios_header (nivel superior: titulo + subtitulo)
            if ($name === 'imagen' && $bloqueContext === 'servicios_header' && !$es_repeater_item) {
                $imagen_con_ia = true;
                $titulo_fieldId = preg_replace('/-imagen-/', '-titulo-', $fieldId);
                $subtitulo_fieldId = preg_replace('/-imagen-/', '-subtitulo-', $fieldId);

                $sizeSelectId = $fieldId . '-size';
                $imagen_btn_ia = '<div class="flex items-center gap-2">
                    <select id="' . $sizeSelectId . '" class="text-sm border border-gray-300 rounded px-2 py-1" title="Aspecto de imagen">
                        <option value="1024x1024" selected>1:1 Cuadrado</option>
                        <option value="1792x1024">16:9 Horizontal</option>
                        <option value="1024x1792">9:16 Vertical</option>
                    </select>
                    <button type="button"
                        onclick="generarImagenIA(\'' . $fieldId . '\', \'\', \'' . $titulo_fieldId . '\', \'' . $subtitulo_fieldId . '\', \'' . $sizeSelectId . '\')"
                        class="text-purple-600 hover:text-purple-700 font-medium text-sm flex items-center gap-1"
                        title="Generar imagen con IA">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Generar con IA
                    </button>
                </div>';
            }

            // Para foto en testimonios_servicios (repeater: nombre + cargo + texto)
            if ($name === 'foto' && $bloqueContext === 'testimonios_servicios' && $es_repeater_item) {
                $imagen_con_ia = true;
                $nombre_fieldId = preg_replace('/-foto-/', '-nombre-', $fieldId);
                $cargo_fieldId = preg_replace('/-foto-/', '-cargo-', $fieldId);
                $texto_fieldId = preg_replace('/-foto-/', '-texto-', $fieldId);

                $sizeSelectId = $fieldId . '-size';
                $imagen_btn_ia = '<div class="flex items-center gap-2">
                    <select id="' . $sizeSelectId . '" class="text-sm border border-gray-300 rounded px-2 py-1" title="Aspecto de imagen">
                        <option value="1024x1024" selected>1:1 Cuadrado</option>
                        <option value="1792x1024">16:9 Horizontal</option>
                        <option value="1024x1792">9:16 Vertical</option>
                    </select>
                    <button type="button"
                        onclick="generarImagenIA(\'' . $fieldId . '\', \'' . $nombre_fieldId . '\', \'' . $cargo_fieldId . '\', \'' . $texto_fieldId . '\', \'' . $sizeSelectId . '\')"
                        class="text-purple-600 hover:text-purple-700 font-medium text-sm flex items-center gap-1"
                        title="Generar imagen con IA">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Generar con IA
                    </button>
                </div>';
            }

            if ($imagen_con_ia) {
                $html .= '<div class="flex items-center justify-between mb-1">';
                $html .= '<label class="block text-sm font-medium text-gray-700">' . htmlspecialchars($label) . '</label>';
                $html .= $imagen_btn_ia;
                $html .= '</div>';
            } else {
                $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">' . htmlspecialchars($label) . '</label>';
            }

            $html .= '<input type="url" name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '"
                             value="' . htmlspecialchars($value ?? '') . '"
                             placeholder="URL de la imagen..."
                             class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">';
            if ($value) {
                $html .= '<img src="' . htmlspecialchars($value) . '" alt="Preview" class="mt-2 h-20 rounded border" id="' . $fieldId . '-preview">';
            } else {
                $html .= '<img src="" alt="Preview" class="mt-2 h-20 rounded border hidden" id="' . $fieldId . '-preview">';
            }
            break;

        case 'icon':
            $html .= '<div class="flex items-center justify-between mb-1">';
            $html .= '<label class="block text-sm font-medium text-gray-700">' . htmlspecialchars($label) . '</label>';
            $html .= $autocompletarBtn;
            $html .= '</div>';
            $html .= '<select name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">';
            foreach ($iconos_disponibles as $icono) {
                $selected = ($value ?? '') === $icono ? 'selected' : '';
                $html .= '<option value="' . $icono . '" ' . $selected . '>' . $icono . '</option>';
            }
            $html .= '</select>';
            break;

        case 'select':
            $html .= '<label class="block text-sm font-medium text-gray-700 mb-1">' . htmlspecialchars($label) . '</label>';
            $html .= '<select name="' . htmlspecialchars($fieldName) . '" id="' . $fieldId . '"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">';
            foreach ($field['options'] ?? [] as $option) {
                $selected = ($value ?? '') === $option ? 'selected' : '';
                $html .= '<option value="' . htmlspecialchars($option) . '" ' . $selected . '>' . htmlspecialchars(ucfirst($option)) . '</option>';
            }
            $html .= '</select>';
            break;

        case 'repeater':
            $html .= '<label class="block text-sm font-medium text-gray-700 mb-2">' . htmlspecialchars($label) . '</label>';
            $items = is_array($value) ? $value : [];
            $min = $field['min'] ?? 1;
            $max = $field['max'] ?? 10;
            $subfields = $field['fields'] ?? [];

            // Asegurar minimo de items
            while (count($items) < $min) {
                $items[] = [];
            }

            $html .= '<div class="space-y-3 repeater-container" data-min="' . $min . '" data-max="' . $max . '">';
            foreach ($items as $i => $item) {
                $html .= '<div class="p-4 bg-white rounded-lg border border-gray-200 repeater-item">';
                $html .= '<div class="flex justify-between items-center mb-3">';
                $html .= '<span class="text-sm font-medium text-gray-500">Item ' . ($i + 1) . '</span>';
                if (count($items) > $min) {
                    $html .= '<button type="button" onclick="this.closest(\'.repeater-item\').remove()" class="text-red-500 hover:text-red-700 text-sm">Eliminar</button>';
                }
                $html .= '</div>';
                $html .= '<div class="grid gap-3 ' . (count($subfields) > 2 ? 'md:grid-cols-2' : '') . '">';
                foreach ($subfields as $subfield) {
                    $subValue = $item[$subfield['name']] ?? '';
                    $subNamePrefix = $fieldName . '[' . $i . ']';
                    $html .= renderFormField($subfield, $subValue, $subNamePrefix, $i, $bloqueContext);
                }
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';

            if (count($items) < $max) {
                $html .= '<button type="button" onclick="addRepeaterItem(this)" class="mt-2 px-4 py-2 text-sm text-blue-600 border border-blue-300 rounded-lg hover:bg-blue-50">
                            + Agregar ' . htmlspecialchars($label) . '
                          </button>';
            }
            break;
    }

    $html .= '</div>';
    return $html;
}
?>

<!-- Tabs de navegacion de Identitas (sincronizados con identitas.php) -->
<?php
// Obtener conteos para badges
$stmt_paginas = $pdo->prepare("SELECT COUNT(*) FROM identitas_paginas WHERE id_instancia = :id");
$stmt_paginas->execute(['id' => $id_instancia]);
$count_paginas = $stmt_paginas->fetchColumn();

$stmt_contactos = $pdo->prepare("SELECT COUNT(*) FROM identitas_contactos WHERE id_instancia = :id AND leido = 0");
$stmt_contactos->execute(['id' => $id_instancia]);
$count_contactos_no_leidos = $stmt_contactos->fetchColumn();
?>
<div class="bg-white rounded-lg shadow-sm mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px overflow-x-auto">
            <button onclick="window.location.href='?modulo=identitas&tab=configuracion'" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition whitespace-nowrap">
                <i data-lucide="settings" class="w-4 h-4 inline mr-2"></i>
                Configuración
            </button>
            <button onclick="window.location.href='?modulo=identitas&tab=paginas'" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition whitespace-nowrap">
                <i data-lucide="file-text" class="w-4 h-4 inline mr-2"></i>
                Páginas (<?php echo $count_paginas; ?>)
            </button>
            <button class="px-6 py-4 text-sm font-medium border-b-2 border-blue-500 text-blue-600 whitespace-nowrap">
                <i data-lucide="blocks" class="w-4 h-4 inline mr-2"></i>
                Templates
            </button>
            <button onclick="window.location.href='?modulo=identitas&tab=contactos'" class="px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition whitespace-nowrap">
                <i data-lucide="mail" class="w-4 h-4 inline mr-2"></i>
                Contactos
                <?php if ($count_contactos_no_leidos > 0): ?>
                    <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $count_contactos_no_leidos; ?></span>
                <?php endif; ?>
            </button>
        </nav>
    </div>
</div>

<!-- Navegacion de paginas -->
<div class="mb-6">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <?php foreach ($paginas_disponibles as $pag):
                $isActive = $pag === $pagina_actual;
                $activeClass = $isActive ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
            ?>
            <a href="?modulo=identitas_templates&pagina=<?php echo $pag; ?>"
               class="<?php echo $activeClass; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm capitalize">
                <?php echo str_replace('-', ' ', $pag); ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>

<!-- Sistema de Toast Notification -->
<?php if ($mensaje): ?>
    <script>
        window.templatesPageMessage = {
            mensaje: '<?php echo addslashes($mensaje); ?>',
            tipo: '<?php echo $tipo_mensaje; ?>',
            scrollTo: '<?php echo $scroll_to; ?>'
        };
    </script>
<?php endif; ?>

<!-- Selector de Template -->
<div id="template-selector" class="bg-white rounded-lg shadow-sm border p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i data-lucide="layout" class="w-5 h-5 inline mr-2"></i>
        Elegir Template para "<?php echo ucfirst(str_replace('-', ' ', $pagina_actual)); ?>"
    </h3>

    <?php if (empty($templates)): ?>
        <div class="text-gray-500 italic">No hay templates disponibles para esta pagina.</div>
    <?php else: ?>
    <form method="POST" class="flex items-end gap-4">
        <input type="hidden" name="accion" value="seleccionar_template">
        <input type="hidden" name="pagina" value="<?php echo htmlspecialchars($pagina_actual); ?>">

        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Template</label>
            <select name="id_template" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <?php foreach ($templates as $tpl): ?>
                <option value="<?php echo $tpl['id_template']; ?>"
                        <?php echo ($template_seleccionado && $template_seleccionado['id_template'] == $tpl['id_template']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($tpl['nombre']); ?> - <?php echo htmlspecialchars($tpl['descripcion']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2">
            <i data-lucide="check" class="w-5 h-5"></i>
            Aplicar Template
        </button>
    </form>
    <?php endif; ?>
</div>

<!-- Editar Contenido de Bloques (DINAMICO) -->
<div class="bg-white rounded-lg shadow-sm border p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-6">
        <i data-lucide="edit-3" class="w-5 h-5 inline mr-2"></i>
        Editar Contenido
        <?php if ($template_seleccionado): ?>
        <span class="text-sm font-normal text-gray-500 ml-2">
            (Template: <?php echo htmlspecialchars($template_seleccionado['nombre']); ?>)
        </span>
        <?php endif; ?>
    </h3>

    <?php if (empty($bloques_template)): ?>
        <div class="text-gray-500 italic p-4 bg-gray-50 rounded-lg">
            <i data-lucide="info" class="w-5 h-5 inline mr-2"></i>
            Selecciona un template para ver los bloques de contenido disponibles.
        </div>
    <?php else: ?>
        <?php foreach ($bloques_template as $bloque):
            $tipo_bloque = $bloque['tipo_bloque'];
            $campos = TemplateService::getBloqueFields($tipo_bloque);
            $contenido_bloque = $contenido_pagina[$tipo_bloque] ?? [];

            // Si no hay definicion de campos, mostrar mensaje
            if (empty($campos)):
        ?>
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-700">
                    <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-2"></i>
                    El bloque "<?php echo htmlspecialchars($tipo_bloque); ?>" no tiene campos definidos.
                </p>
            </div>
        <?php else: ?>
            <form method="POST" id="bloque-<?php echo $tipo_bloque; ?>" class="mb-8 p-6 bg-gray-50 rounded-lg">
                <input type="hidden" name="accion" value="guardar_bloque">
                <input type="hidden" name="pagina" value="<?php echo htmlspecialchars($pagina_actual); ?>">
                <input type="hidden" name="tipo_bloque" value="<?php echo htmlspecialchars($tipo_bloque); ?>">

                <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                    <i data-lucide="box" class="w-5 h-5 mr-2 text-blue-600"></i>
                    Bloque: <?php echo ucfirst(str_replace('_', ' ', $tipo_bloque)); ?>
                </h4>

                <div class="space-y-4">
                    <?php foreach ($campos as $campo):
                        $valor = $contenido_bloque[$campo['name']] ?? null;
                        echo renderFormField($campo, $valor, 'contenido', null, $tipo_bloque);
                    endforeach; ?>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        Guardar <?php echo ucfirst(str_replace('_', ' ', $tipo_bloque)); ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- CKEditor 5 y Scripts -->
<style>
    .ck-editor__editable {
        min-height: 150px;
        max-height: 300px;
    }
</style>
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
<script>
// ============================================================================
// SISTEMA DE TOAST Y SCROLL
// ============================================================================

function showToast(mensaje, tipo = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 z-50 transition-all duration-300 ${
        tipo === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
    }`;

    const icon = tipo === 'success'
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';

    toast.innerHTML = `${icon}<span class="font-semibold">${mensaje}</span>`;
    document.body.appendChild(toast);

    setTimeout(() => toast.style.opacity = '0', 3000);
    setTimeout(() => toast.remove(), 3500);
}

// DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar change detection
    initTemplatesFormChangeDetection();

    // Mostrar toast y scroll si hay mensaje
    if (window.templatesPageMessage) {
        const msg = window.templatesPageMessage;

        setTimeout(() => {
            showToast(msg.mensaje, msg.tipo);
        }, 100);

        if (msg.scrollTo) {
            setTimeout(() => {
                const elemento = document.getElementById(msg.scrollTo);
                if (elemento) {
                    elemento.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    elemento.classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
                    setTimeout(() => {
                        elemento.classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
                    }, 2000);
                }
            }, 600);
        }
    }

    // Inicializar CKEditors
    initCKEditors();

    // Reinicializar Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// ============================================================================
// CKEDITOR INITIALIZATION
// ============================================================================

function initCKEditors() {
    document.querySelectorAll('.ckeditor-bloque').forEach(textarea => {
        if (textarea.classList.contains('ck-editor-initialized')) return;

        ClassicEditor
            .create(textarea, {
                toolbar: ['bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
                language: 'es',
                placeholder: 'Escribe el contenido aqui...'
            })
            .then(editor => {
                textarea.classList.add('ck-editor-initialized');
                // Detectar cambios en CKEditor
                editor.model.document.on('change:data', () => {
                    textarea.value = editor.getData();
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                });
            })
            .catch(error => {
                console.error('Error CKEditor:', error);
            });
    });
}

// ============================================================================
// CHANGE DETECTION
// ============================================================================

function initTemplatesFormChangeDetection() {
    document.querySelectorAll('form').forEach(form => {
        const submitBtn = form.querySelector('button.save-button[type="submit"]');
        if (!submitBtn) return;

        const formData = new FormData(form);
        const originalData = {};
        for (let [key, value] of formData.entries()) {
            originalData[key] = value;
        }

        // Estado inicial: boton en gris (sin cambios)
        submitBtn.className = 'px-6 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed transition font-semibold flex items-center gap-2 save-button';
        submitBtn.disabled = true;

        const checkChanges = () => {
            const currentData = new FormData(form);
            let hasChanges = false;

            for (let [key, value] of currentData.entries()) {
                if (key !== 'accion') {
                    if (!(key in originalData) || originalData[key] !== value) {
                        hasChanges = true;
                        break;
                    }
                }
            }

            if (!hasChanges) {
                for (let key in originalData) {
                    if (key !== 'accion' && !currentData.has(key)) {
                        hasChanges = true;
                        break;
                    }
                }
            }

            if (hasChanges) {
                submitBtn.disabled = false;
                submitBtn.className = 'px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2 save-button';
            } else {
                submitBtn.disabled = true;
                submitBtn.className = 'px-6 py-3 bg-gray-400 text-white rounded-lg cursor-not-allowed transition font-semibold flex items-center gap-2 save-button';
            }
        };

        // Usar event delegation para detectar cambios en campos dinamicos tambien
        form.addEventListener('input', (e) => {
            if (e.target.matches('input, select, textarea')) {
                checkChanges();
            }
        });
        form.addEventListener('change', (e) => {
            if (e.target.matches('input, select, textarea')) {
                checkChanges();
            }
        });

        // Exponer checkChanges globalmente para llamarlo desde funciones de IA
        window.checkFormChanges = checkChanges;

        form.addEventListener('submit', function(e) {
            submitBtn.innerHTML = '<i data-lucide="loader" class="w-5 h-5 inline mr-2 animate-spin"></i>Guardando...';
            submitBtn.disabled = true;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    });
}

// ============================================================================
// AUTOCOMPLETAR CON IA (OpenAI)
// ============================================================================

/**
 * Genera contenido combinado para grupos de campos relacionados
 * @param {string} groupType - Tipo de grupo (cita_autor, titulo_subtitulo, stat, etc.)
 * @param {object} fieldIds - Objeto con los IDs de los campos a llenar {cita: 'id1', autor_cita: 'id2'}
 */
async function generarCamposCombinados(groupType, fieldIds) {
    console.log('[IA Combinado] Generando grupo:', groupType, 'campos:', fieldIds);

    // Mostrar indicador de carga en el boton
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="loader" class="w-3 h-3 inline animate-spin"></i> Generando...';
    btn.disabled = true;
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // Recolectar valores existentes en otros items del repeater para evitar duplicados
    let existingValues = [];

    // Determinar el campo principal para buscar valores
    const mainFieldId = Object.values(fieldIds)[0];
    if (mainFieldId) {
        const mainField = document.getElementById(mainFieldId);
        if (mainField) {
            const repeaterItem = mainField.closest('.repeater-item');
            if (repeaterItem) {
                const container = repeaterItem.closest('.repeater-container');
                if (container) {
                    // Buscar el mismo tipo de campo en otros items
                    container.querySelectorAll('.repeater-item').forEach(item => {
                        if (item !== repeaterItem) {
                            // Para cada campo en fieldIds, buscar su valor en otros items
                            Object.entries(fieldIds).forEach(([fieldName, fieldId]) => {
                                // Extraer patron del ID para buscar en otros items
                                // Ejemplo: field-contenido-valores-0-nombre-mision_vision
                                // -> buscar field-contenido-valores-N-nombre-mision_vision
                                const idPattern = fieldId.replace(/-\d+-/, '-\\d+-');
                                const regex = new RegExp(idPattern.replace(/[.*+?^${}()|[\]\\]/g, '\\$&').replace('\\\\d\\+', '\\d+'));

                                item.querySelectorAll('input, textarea, select').forEach(input => {
                                    if (input.id && regex.test(input.id)) {
                                        const val = input.value?.trim();
                                        if (val && !existingValues.includes(val)) {
                                            existingValues.push(val);
                                        }
                                    }
                                });
                            });
                        }
                    });
                }
            }
        }
    }

    console.log('[IA Combinado] Valores existentes encontrados:', existingValues);

    try {
        const response = await fetch('index.php?modulo=identitas_templates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                accion: 'generar_combinado_ia',
                group_type: groupType,
                existing_values: JSON.stringify(existingValues)
            })
        });

        const data = await response.json();
        console.log('[IA Combinado] Respuesta:', data);

        if (data.success && data.fields) {
            // Mapear los nombres de campo devueltos a los IDs proporcionados
            let camposLlenados = 0;

            // Mapeos especiales entre nombres JSON y nombres de campo
            const fieldMapping = {
                'autor': 'autor_cita'  // Mapeo especial para cita_autor
            };

            for (const [fieldName, value] of Object.entries(data.fields)) {
                // Buscar el ID correcto (con posible mapeo)
                const mappedName = fieldMapping[fieldName] || fieldName;
                const fieldId = fieldIds[mappedName] || fieldIds[fieldName];

                if (fieldId && value) {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        // Determinar tipo de campo y asignar valor apropiadamente
                        const tagName = field.tagName.toLowerCase();

                        if (tagName === 'select') {
                            // Campo select (ej: iconos) - buscar la opcion
                            let option = field.querySelector(`option[value="${value}"]`);
                            if (!option) {
                                // Si no existe, crear la opción dinámicamente
                                console.log('[IA Combinado] Creando opción para select:', value);
                                option = document.createElement('option');
                                option.value = value;
                                option.textContent = value;
                                field.appendChild(option);
                            }
                            field.value = value;
                            console.log('[IA Combinado] Select actualizado:', field.id, '=', value);
                        } else if (field.classList.contains('ckeditor-bloque') || field.classList.contains('ckeditor') || field.tagName.toLowerCase() === 'textarea') {
                            // Campo CKEditor - actualizar textarea subyacente PRIMERO
                            field.value = value;
                            console.log('[IA Combinado] Textarea actualizado:', field.id, '=', value.substring(0, 50));

                            // Intentar actualizar instancia de CKEditor si existe
                            const editorContainer = field.closest('.mb-4');
                            const editorElement = editorContainer?.querySelector('.ck-editor__editable');

                            if (editorElement) {
                                // Método 1: Intentar acceder a ckeditorInstance
                                if (editorElement.ckeditorInstance) {
                                    try {
                                        editorElement.ckeditorInstance.setData(value);
                                        console.log('[IA Combinado] CKEditor actualizado via ckeditorInstance');
                                    } catch (e) {
                                        console.warn('[IA Combinado] Error al actualizar CKEditor:', e);
                                    }
                                } else {
                                    console.log('[IA Combinado] CKEditor no inicializado aún, valor guardado en textarea');
                                }
                            }
                        } else {
                            // Campo normal (input, textarea)
                            field.value = value;
                        }

                        // Disparar eventos de cambio
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                        field.dispatchEvent(new Event('change', { bubbles: true }));

                        // Feedback visual
                        field.classList.add('ring-2', 'ring-green-500');
                        setTimeout(() => field.classList.remove('ring-2', 'ring-green-500'), 1500);

                        camposLlenados++;
                        const displayValue = typeof value === 'string' ? value.substring(0, 50) : value;
                        console.log('[IA Combinado] Campo llenado:', fieldId, '=', displayValue);
                    } else {
                        console.warn('[IA Combinado] Campo no encontrado:', fieldId);
                    }
                }
            }

            if (camposLlenados > 0) {
                showToast(`${camposLlenados} campos generados con IA`, 'success');
                // Forzar verificacion de cambios para habilitar boton guardar
                if (typeof window.checkFormChanges === 'function') {
                    window.checkFormChanges();
                }
            } else {
                showToast('No se pudieron llenar los campos', 'warning');
            }
        } else {
            showToast(data.error || 'Error al generar contenido', 'error');
        }
    } catch (error) {
        console.error('[IA Combinado] Error:', error);
        showToast('Error de conexion', 'error');
    } finally {
        // Restaurar boton
        btn.innerHTML = originalContent;
        btn.disabled = false;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

async function autocompletarConIA(fieldId, fieldName, fieldLabel, fieldType, bloque = '') {
    const field = document.getElementById(fieldId);
    if (!field) {
        console.error('[IA] Campo no encontrado:', fieldId);
        showToast('Error: Campo no encontrado', 'error');
        return;
    }
    console.log('[IA] Autocompletando campo:', fieldId, 'bloque:', bloque);

    // Buscar valores existentes en campos hermanos (para evitar duplicados)
    let existingValues = [];
    const repeaterItem = field.closest('.repeater-item');
    if (repeaterItem) {
        const container = repeaterItem.closest('.repeater-container');
        if (container) {
            // Buscar todos los campos con el mismo nombre base en otros items
            container.querySelectorAll('.repeater-item').forEach(item => {
                if (item !== repeaterItem) {
                    // Buscar campos que coincidan con el fieldName
                    item.querySelectorAll('input[name*="[' + fieldName + ']"], textarea[name*="[' + fieldName + ']"]').forEach(input => {
                        if (input.value && input.value.trim()) {
                            existingValues.push(input.value.trim());
                        }
                    });
                }
            });
        }
    }

    // Mostrar indicador de carga
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i data-lucide="loader" class="w-3 h-3 inline animate-spin"></i>';
    btn.disabled = true;
    if (typeof lucide !== 'undefined') lucide.createIcons();

    try {
        const response = await fetch('index.php?modulo=identitas_templates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                accion: 'autocompletar_ia',
                field_name: fieldName,
                field_label: fieldLabel,
                field_type: fieldType,
                bloque: bloque,
                existing_values: JSON.stringify(existingValues)
            })
        });

        const data = await response.json();

        if (data.success && data.content) {
            // Establecer valor
            autocompletarCampo(fieldId, data.content);
            showToast('Generado con IA', 'success');
            // Forzar verificacion de cambios para habilitar boton guardar
            if (typeof window.checkFormChanges === 'function') {
                window.checkFormChanges();
            }
        } else if (data.success && !data.content) {
            showToast('La IA no genero contenido', 'warning');
        } else {
            // Mostrar error
            if (data.code === 'NOT_CONFIGURED') {
                showToast('OpenAI no configurado. Ve a Ajustes > Integraciones.', 'error');
            } else {
                showToast(data.error || 'Error al generar con IA', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexion', 'error');
    } finally {
        // Restaurar boton
        btn.innerHTML = originalContent;
        btn.disabled = false;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

// ============================================================================
// AUTOCOMPLETAR FUNCTIONALITY (DATOS ESTANDAR)
// ============================================================================

function autocompletarCampo(fieldId, valor) {
    const field = document.getElementById(fieldId);
    if (!field) {
        console.error('[Autocompletar] Campo no encontrado:', fieldId);
        return;
    }
    console.log('[Autocompletar] Insertando en campo:', fieldId, 'valor:', valor.substring(0, 50) + '...');

    // Verificar si es un CKEditor
    const ckeditorContainer = field.closest('.ck-editor');
    if (ckeditorContainer || field.classList.contains('ck-editor-initialized')) {
        // Es un CKEditor - buscar la instancia
        const editorElement = field.closest('.mb-4')?.querySelector('.ck-editor__editable');
        if (editorElement && editorElement.ckeditorInstance) {
            editorElement.ckeditorInstance.setData(valor);
            field.value = valor;
            field.dispatchEvent(new Event('input', { bubbles: true }));
            showToast('Campo autocompletado', 'success');
            return;
        }
        // Fallback: establecer valor en textarea original
        field.value = valor;
    } else {
        // Campo normal (input, textarea)
        field.value = valor;
    }

    // Disparar evento de cambio para activar change detection
    field.dispatchEvent(new Event('input', { bubbles: true }));
    field.dispatchEvent(new Event('change', { bubbles: true }));

    // Feedback visual
    field.classList.add('ring-2', 'ring-green-500');
    setTimeout(() => {
        field.classList.remove('ring-2', 'ring-green-500');
    }, 1000);

    showToast('Campo autocompletado', 'success');
}

// ============================================================================
// REPEATER FUNCTIONALITY
// ============================================================================

function addRepeaterItem(button) {
    const container = button.previousElementSibling;
    const items = container.querySelectorAll('.repeater-item');
    const max = parseInt(container.dataset.max) || 10;

    if (items.length >= max) {
        alert('Maximo de ' + max + ' items alcanzado');
        return;
    }

    // Clonar el ultimo item
    const lastItem = items[items.length - 1];
    const newItem = lastItem.cloneNode(true);

    // IMPORTANTE: Eliminar CKEditor clonados y recrear textareas limpios
    // Cuando clonamos, CKEditor viene con toda su estructura DOM y contenido
    // Necesitamos eliminar esto completamente y recrear el textarea desde cero
    newItem.querySelectorAll('.ck-editor').forEach(ckEditorWrapper => {
        // Encontrar el textarea original dentro del wrapper
        const originalTextarea = ckEditorWrapper.previousElementSibling;
        if (originalTextarea && originalTextarea.tagName === 'TEXTAREA') {
            // Guardar atributos del textarea original
            const textareaId = originalTextarea.id;
            const textareaName = originalTextarea.name;
            const textareaClasses = originalTextarea.className;

            // Crear nuevo textarea limpio
            const newTextarea = document.createElement('textarea');
            newTextarea.id = textareaId;
            newTextarea.name = textareaName;
            newTextarea.className = textareaClasses;
            newTextarea.value = ''; // Vacío

            // Reemplazar el textarea clonado con el nuevo
            originalTextarea.parentNode.replaceChild(newTextarea, originalTextarea);
        }
        // Eliminar el wrapper de CKEditor clonado
        ckEditorWrapper.remove();
    });

    // Actualizar indices
    const newIndex = items.length;
    newItem.querySelectorAll('[name]').forEach(input => {
        // Actualizar name
        const oldName = input.name;
        input.name = input.name.replace(/\[\d+\]/, '[' + newIndex + ']');

        // Actualizar ID
        if (input.id) {
            const newId = input.id.replace(/-\d+-/, '-' + newIndex + '-');
            input.id = newId;
        }

        input.value = '';
        if (input.type === 'checkbox') input.checked = false;
    });

    // Actualizar los onclick de los botones IA (campos individuales)
    newItem.querySelectorAll('button[onclick*="autocompletarConIA"]').forEach(btn => {
        const oldOnclick = btn.getAttribute('onclick');
        if (oldOnclick) {
            // Reemplazar el indice en el fieldId del onclick
            const newOnclick = oldOnclick.replace(/-\d+-/, '-' + newIndex + '-');
            btn.setAttribute('onclick', newOnclick);
        }
    });

    // Actualizar los onclick de los botones IA (grupos combinados)
    newItem.querySelectorAll('button[onclick*="generarCamposCombinados"]').forEach(btn => {
        const oldOnclick = btn.getAttribute('onclick');
        if (oldOnclick) {
            // Reemplazar TODOS los indices en el JSON del onclick (usar replace global)
            const newOnclick = oldOnclick.replace(/-\d+-/g, '-' + newIndex + '-');
            btn.setAttribute('onclick', newOnclick);
        }
    });

    // Actualizar los onclick de los botones de datos estandar
    newItem.querySelectorAll('button[onclick*="autocompletarCampo"]').forEach(btn => {
        const oldOnclick = btn.getAttribute('onclick');
        if (oldOnclick) {
            const newOnclick = oldOnclick.replace(/-\d+-/, '-' + newIndex + '-');
            btn.setAttribute('onclick', newOnclick);
        }
    });

    // Actualizar label del item
    const itemLabel = newItem.querySelector('.text-gray-500');
    if (itemLabel) {
        itemLabel.textContent = 'Item ' + (newIndex + 1);
    }

    // Mostrar boton eliminar
    const deleteBtn = newItem.querySelector('button[onclick*="remove"]');
    if (deleteBtn) {
        deleteBtn.style.display = '';
    }

    container.appendChild(newItem);

    // Reinicializar CKEditors en el nuevo item
    initCKEditors();

    // Reinicializar Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Trigger change detection
    newItem.querySelector('input, textarea, select')?.dispatchEvent(new Event('input', { bubbles: true }));
}

// ==================== GENERACION DE IMAGENES CON IA ====================
async function generarImagenIA(imagenFieldId, anioFieldId, tituloFieldId, descripcionFieldId, sizeSelectId) {
    console.log('[IA Imagen] Generando imagen para:', imagenFieldId);

    // Obtener valores de contexto
    const anioField = document.getElementById(anioFieldId);
    const tituloField = document.getElementById(tituloFieldId);
    const descripcionField = document.getElementById(descripcionFieldId);
    const sizeSelect = document.getElementById(sizeSelectId);

    const anio = anioField?.value || '';
    const titulo = tituloField?.value || '';
    const descripcion = descripcionField?.value || '';
    const size = sizeSelect?.value || '1024x1024'; // Cuadrado por defecto

    if (!titulo && !descripcion) {
        showToast('Primero completa el título o descripción del evento', 'warning');
        return;
    }

    // Mostrar indicador de carga
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generando...';

    try {
        const response = await fetch('index.php?modulo=identitas_templates', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                accion: 'generar_imagen_ia',
                anio: anio,
                titulo: titulo,
                descripcion: descripcion,
                size: size
            })
        });

        const data = await response.json();
        console.log('[IA Imagen] Respuesta:', data);

        if (data.success && data.image_url) {
            // Insertar URL de imagen en el campo
            const imagenField = document.getElementById(imagenFieldId);
            if (imagenField) {
                imagenField.value = data.image_url;
                imagenField.dispatchEvent(new Event('input', { bubbles: true }));
                imagenField.dispatchEvent(new Event('change', { bubbles: true }));

                // Actualizar preview
                const previewImg = document.getElementById(imagenFieldId + '-preview');
                if (previewImg) {
                    previewImg.src = data.image_url;
                    previewImg.classList.remove('hidden');
                }

                // Feedback visual
                imagenField.classList.add('ring-2', 'ring-purple-500');
                setTimeout(() => imagenField.classList.remove('ring-2', 'ring-purple-500'), 1500);

                showToast('Imagen generada con IA', 'success');

                // Forzar verificacion de cambios
                if (typeof window.checkFormChanges === 'function') {
                    window.checkFormChanges();
                }
            }
        } else {
            showToast(data.error || 'Error al generar imagen', 'error');
        }
    } catch (error) {
        console.error('[IA Imagen] Error:', error);
        showToast('Error de conexión al generar imagen', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}
</script>
