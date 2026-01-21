<?php
/**
 * TemplateService - Gestión de Templates y Bloques para Identitas
 *
 * Permite obtener templates disponibles, contenido de bloques,
 * y renderizar páginas con contenido estructurado.
 */

namespace VERUMax\Services;

class TemplateService
{
    /**
     * Obtener todos los templates disponibles para una página
     */
    public static function getTemplatesForPage(string $pagina): array
    {
        $pdo = DatabaseService::get('identitas');

        $stmt = $pdo->prepare("
            SELECT id_template, slug, nombre, descripcion, thumbnail_url
            FROM identitas_templates
            WHERE pagina = :pagina AND activo = 1
            ORDER BY orden
        ");
        $stmt->execute(['pagina' => $pagina]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtener el template seleccionado por una instancia para una página
     */
    public static function getSelectedTemplate(int $idInstancia, string $pagina): ?array
    {
        $pdo = DatabaseService::get('identitas');

        $stmt = $pdo->prepare("
            SELECT t.id_template, t.slug, t.nombre, t.descripcion
            FROM identitas_instancia_templates it
            JOIN identitas_templates t ON it.id_template = t.id_template
            WHERE it.id_instancia = :id_instancia AND it.pagina = :pagina
        ");
        $stmt->execute([
            'id_instancia' => $idInstancia,
            'pagina' => $pagina
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtener bloques de un template
     */
    public static function getTemplateBloques(int $idTemplate): array
    {
        $pdo = DatabaseService::get('identitas');

        $stmt = $pdo->prepare("
            SELECT tipo_bloque, orden, config
            FROM identitas_template_bloques
            WHERE id_template = :id_template
            ORDER BY orden
        ");
        $stmt->execute(['id_template' => $idTemplate]);

        $bloques = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Decodificar config JSON
        foreach ($bloques as &$bloque) {
            $bloque['config'] = json_decode($bloque['config'], true) ?? [];
        }

        return $bloques;
    }

    /**
     * Obtener contenido de un bloque para una instancia
     */
    public static function getBloqueContent(int $idInstancia, string $pagina, string $tipoBloque): ?array
    {
        $pdo = DatabaseService::get('identitas');

        $stmt = $pdo->prepare("
            SELECT contenido
            FROM identitas_contenido_bloques
            WHERE id_instancia = :id_instancia
              AND pagina = :pagina
              AND tipo_bloque = :tipo_bloque
        ");
        $stmt->execute([
            'id_instancia' => $idInstancia,
            'pagina' => $pagina,
            'tipo_bloque' => $tipoBloque
        ]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            return json_decode($row['contenido'], true);
        }

        return null;
    }

    /**
     * Obtener todo el contenido de una página para una instancia
     */
    public static function getPageContent(int $idInstancia, string $pagina): array
    {
        $pdo = DatabaseService::get('identitas');

        $stmt = $pdo->prepare("
            SELECT tipo_bloque, contenido
            FROM identitas_contenido_bloques
            WHERE id_instancia = :id_instancia AND pagina = :pagina
        ");
        $stmt->execute([
            'id_instancia' => $idInstancia,
            'pagina' => $pagina
        ]);

        $content = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $content[$row['tipo_bloque']] = json_decode($row['contenido'], true);
        }

        return $content;
    }

    /**
     * Guardar template seleccionado para una instancia
     */
    public static function setSelectedTemplate(int $idInstancia, string $pagina, int $idTemplate): bool
    {
        $pdo = DatabaseService::get('identitas');

        $stmt = $pdo->prepare("
            INSERT INTO identitas_instancia_templates (id_instancia, pagina, id_template)
            VALUES (:id_instancia, :pagina, :id_template)
            ON DUPLICATE KEY UPDATE id_template = :id_template2, fecha_seleccion = CURRENT_TIMESTAMP
        ");

        return $stmt->execute([
            'id_instancia' => $idInstancia,
            'pagina' => $pagina,
            'id_template' => $idTemplate,
            'id_template2' => $idTemplate
        ]);
    }

    /**
     * Guardar contenido de un bloque
     */
    public static function saveBloqueContent(int $idInstancia, string $pagina, string $tipoBloque, array $contenido): bool
    {
        $pdo = DatabaseService::get('identitas');

        $stmt = $pdo->prepare("
            INSERT INTO identitas_contenido_bloques (id_instancia, pagina, tipo_bloque, contenido)
            VALUES (:id_instancia, :pagina, :tipo_bloque, :contenido)
            ON DUPLICATE KEY UPDATE contenido = :contenido2, fecha_actualizacion = CURRENT_TIMESTAMP
        ");

        $json = json_encode($contenido, JSON_UNESCAPED_UNICODE);

        return $stmt->execute([
            'id_instancia' => $idInstancia,
            'pagina' => $pagina,
            'tipo_bloque' => $tipoBloque,
            'contenido' => $json,
            'contenido2' => $json
        ]);
    }

    /**
     * Obtener datos completos para renderizar una página
     * Incluye template seleccionado, bloques y contenido
     */
    public static function getPageRenderData(int $idInstancia, string $pagina): array
    {
        // Obtener template seleccionado
        $template = self::getSelectedTemplate($idInstancia, $pagina);

        if (!$template) {
            // Si no hay template seleccionado, usar el primero disponible
            $templates = self::getTemplatesForPage($pagina);
            if (!empty($templates)) {
                $template = $templates[0];
            }
        }

        if (!$template) {
            return [
                'template' => null,
                'bloques' => [],
                'contenido' => []
            ];
        }

        // Obtener bloques del template
        $bloques = self::getTemplateBloques($template['id_template']);

        // Obtener contenido
        $contenido = self::getPageContent($idInstancia, $pagina);

        return [
            'template' => $template,
            'bloques' => $bloques,
            'contenido' => $contenido
        ];
    }

    /**
     * Renderizar un bloque específico
     */
    public static function renderBloque(string $tipoBloque, array $contenido, array $config = [], array $colores = [], int $idInstancia = 0): string
    {
        $bloqueFile = __DIR__ . '/../../../identitas/templates/bloques/' . $tipoBloque . '.php';

        if (!file_exists($bloqueFile)) {
            return "<!-- Bloque no encontrado: {$tipoBloque} -->";
        }

        // Capturar output del bloque
        ob_start();
        include $bloqueFile;
        return ob_get_clean();
    }

    /**
     * Renderizar página completa con template
     */
    public static function renderPage(int $idInstancia, string $pagina, array $colores = []): string
    {
        $data = self::getPageRenderData($idInstancia, $pagina);

        if (!$data['template']) {
            return "<!-- No hay template para esta página -->";
        }

        $html = '';

        foreach ($data['bloques'] as $bloque) {
            $tipoBloque = $bloque['tipo_bloque'];
            $config = $bloque['config'];

            // Buscar contenido para este bloque
            // Primero buscar exacto
            $contenido = $data['contenido'][$tipoBloque] ?? [];

            // Si no hay contenido exacto, buscar bloques relacionados
            if (empty($contenido)) {
                // Caso especial: bloques compuestos como "mision_con_stats"
                if ($tipoBloque === 'mision_con_stats') {
                    // Combinar contenido de 'mision' y 'stats'
                    $contenido = [
                        'mision' => $data['contenido']['mision'] ?? [],
                        'stats' => $data['contenido']['stats'] ?? []
                    ];
                } else {
                    // Búsqueda genérica por prefijo/substring
                    foreach ($data['contenido'] as $key => $value) {
                        if (strpos($tipoBloque, $key) !== false || strpos($key, $tipoBloque) !== false) {
                            $contenido = $value;
                            break;
                        }
                    }
                }
            }

            $html .= self::renderBloque($tipoBloque, $contenido, $config, $colores, $idInstancia);
        }

        return $html;
    }

    /**
     * Obtener definición de campos para un tipo de bloque
     * Usado en el admin para generar el formulario
     */
    public static function getBloqueFields(string $tipoBloque): array
    {
        $definitions = [
            'mision' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título', 'required' => true],
                ['name' => 'texto', 'type' => 'editor', 'label' => 'Texto', 'required' => true],
                ['name' => 'link_texto', 'type' => 'text', 'label' => 'Texto del enlace'],
                ['name' => 'link_url', 'type' => 'url', 'label' => 'URL del enlace']
            ],
            'stats' => [
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Estadísticas', 'fields' => [
                    ['name' => 'titulo', 'type' => 'text', 'label' => 'Título'],
                    ['name' => 'texto', 'type' => 'textarea', 'label' => 'Descripción']
                ], 'min' => 2, 'max' => 6]
            ],
            'mision_con_stats' => [
                ['name' => 'mision_titulo', 'type' => 'text', 'label' => 'Título Misión', 'required' => true],
                ['name' => 'mision_texto', 'type' => 'editor', 'label' => 'Texto Misión', 'required' => true],
                ['name' => 'mision_link_texto', 'type' => 'text', 'label' => 'Texto del enlace'],
                ['name' => 'mision_link_url', 'type' => 'url', 'label' => 'URL del enlace'],
                ['name' => 'stats', 'type' => 'repeater', 'label' => 'Estadísticas', 'fields' => [
                    ['name' => 'titulo', 'type' => 'text', 'label' => 'Título'],
                    ['name' => 'texto', 'type' => 'textarea', 'label' => 'Descripción']
                ], 'min' => 4, 'max' => 4]
            ],
            'servicios_grid' => [
                ['name' => 'titulo_seccion', 'type' => 'text', 'label' => 'Título de la sección'],
                ['name' => 'subtitulo', 'type' => 'textarea', 'label' => 'Subtítulo'],
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Servicios', 'fields' => [
                    ['name' => 'icono', 'type' => 'icon', 'label' => 'Icono'],
                    ['name' => 'titulo', 'type' => 'text', 'label' => 'Título'],
                    ['name' => 'texto', 'type' => 'textarea', 'label' => 'Descripción']
                ], 'min' => 1, 'max' => 12]
            ],
            'contacto_info' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título'],
                ['name' => 'texto', 'type' => 'editor', 'label' => 'Texto introductorio'],
                ['name' => 'email', 'type' => 'email', 'label' => 'Email de contacto'],
                ['name' => 'telefono', 'type' => 'text', 'label' => 'Teléfono'],
                ['name' => 'web', 'type' => 'url', 'label' => 'Sitio web']
            ],
            // ========== NUEVOS BLOQUES - SOBRE NOSOTROS ==========
            'hero_institucional' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título principal', 'required' => true],
                ['name' => 'subtitulo', 'type' => 'textarea', 'label' => 'Subtítulo'],
                ['name' => 'imagen_fondo', 'type' => 'image', 'label' => 'Imagen de fondo'],
                ['name' => 'mostrar_logo', 'type' => 'checkbox', 'label' => 'Mostrar logo institucional']
            ],
            'mision_vision' => [
                ['name' => 'mision_titulo', 'type' => 'text', 'label' => 'Título Misión'],
                ['name' => 'mision_texto', 'type' => 'editor', 'label' => 'Texto Misión', 'required' => true],
                ['name' => 'vision_titulo', 'type' => 'text', 'label' => 'Título Visión'],
                ['name' => 'vision_texto', 'type' => 'editor', 'label' => 'Texto Visión', 'required' => true],
                ['name' => 'valores_titulo', 'type' => 'text', 'label' => 'Título Valores'],
                ['name' => 'valores', 'type' => 'repeater', 'label' => 'Valores', 'fields' => [
                    ['name' => 'icono', 'type' => 'icon', 'label' => 'Icono'],
                    ['name' => 'nombre', 'type' => 'text', 'label' => 'Nombre del valor'],
                    ['name' => 'descripcion', 'type' => 'textarea', 'label' => 'Descripción']
                ], 'min' => 3, 'max' => 8]
            ],
            'equipo' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título de la sección'],
                ['name' => 'subtitulo', 'type' => 'textarea', 'label' => 'Subtítulo'],
                ['name' => 'miembros', 'type' => 'repeater', 'label' => 'Miembros del equipo', 'fields' => [
                    ['name' => 'foto', 'type' => 'image', 'label' => 'Foto'],
                    ['name' => 'nombre', 'type' => 'text', 'label' => 'Nombre'],
                    ['name' => 'cargo', 'type' => 'text', 'label' => 'Cargo'],
                    ['name' => 'bio', 'type' => 'textarea', 'label' => 'Biografía breve'],
                    ['name' => 'linkedin', 'type' => 'url', 'label' => 'LinkedIn']
                ], 'min' => 1, 'max' => 12]
            ],
            'intro_academica' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título', 'required' => true],
                ['name' => 'texto', 'type' => 'editor', 'label' => 'Introducción académica', 'required' => true],
                ['name' => 'imagen', 'type' => 'image', 'label' => 'Imagen ilustrativa'],
                ['name' => 'cita', 'type' => 'textarea', 'label' => 'Cita destacada'],
                ['name' => 'autor_cita', 'type' => 'text', 'label' => 'Autor de la cita']
            ],
            'areas_investigacion' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título de la sección'],
                ['name' => 'areas', 'type' => 'repeater', 'label' => 'Áreas de investigación', 'fields' => [
                    ['name' => 'icono', 'type' => 'icon', 'label' => 'Icono'],
                    ['name' => 'nombre', 'type' => 'text', 'label' => 'Nombre del área'],
                    ['name' => 'descripcion', 'type' => 'textarea', 'label' => 'Descripción'],
                    ['name' => 'link', 'type' => 'url', 'label' => 'Enlace a más información']
                ], 'min' => 2, 'max' => 8]
            ],
            'publicaciones' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título de la sección'],
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Publicaciones', 'fields' => [
                    ['name' => 'titulo', 'type' => 'text', 'label' => 'Título de la publicación'],
                    ['name' => 'autores', 'type' => 'text', 'label' => 'Autores'],
                    ['name' => 'fecha', 'type' => 'date', 'label' => 'Fecha'],
                    ['name' => 'revista', 'type' => 'text', 'label' => 'Revista/Editorial'],
                    ['name' => 'link', 'type' => 'url', 'label' => 'Enlace']
                ], 'min' => 1, 'max' => 10]
            ],
            'reconocimientos' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título de la sección'],
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Reconocimientos', 'fields' => [
                    ['name' => 'nombre', 'type' => 'text', 'label' => 'Nombre del reconocimiento'],
                    ['name' => 'otorgante', 'type' => 'text', 'label' => 'Institución otorgante'],
                    ['name' => 'anio', 'type' => 'text', 'label' => 'Año'],
                    ['name' => 'imagen', 'type' => 'image', 'label' => 'Imagen/Badge']
                ], 'min' => 1, 'max' => 8]
            ],
            'intro_historia' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título', 'required' => true],
                ['name' => 'texto', 'type' => 'editor', 'label' => 'Introducción', 'required' => true],
                ['name' => 'anio_fundacion', 'type' => 'text', 'label' => 'Año de fundación']
            ],
            'timeline_vertical' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título de la línea de tiempo'],
                ['name' => 'eventos', 'type' => 'repeater', 'label' => 'Eventos/Hitos', 'fields' => [
                    ['name' => 'anio', 'type' => 'text', 'label' => 'Año'],
                    ['name' => 'titulo', 'type' => 'text', 'label' => 'Título del evento'],
                    ['name' => 'descripcion', 'type' => 'textarea', 'label' => 'Descripción'],
                    ['name' => 'imagen', 'type' => 'image', 'label' => 'Imagen']
                ], 'min' => 3, 'max' => 15]
            ],
            // ========== NUEVOS BLOQUES - SERVICIOS ==========
            'servicios_header' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título principal', 'required' => true],
                ['name' => 'subtitulo', 'type' => 'textarea', 'label' => 'Subtítulo/Descripción'],
                ['name' => 'imagen', 'type' => 'image', 'label' => 'Imagen de cabecera']
            ],
            'servicios_cards' => [
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Servicios', 'fields' => [
                    ['name' => 'icono', 'type' => 'icon', 'label' => 'Icono'],
                    ['name' => 'titulo', 'type' => 'text', 'label' => 'Título'],
                    ['name' => 'descripcion', 'type' => 'textarea', 'label' => 'Descripción'],
                    ['name' => 'link', 'type' => 'url', 'label' => 'Enlace'],
                    ['name' => 'destacado', 'type' => 'checkbox', 'label' => 'Destacar este servicio']
                ], 'min' => 3, 'max' => 12]
            ],
            'servicios_accordion' => [
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Servicios', 'fields' => [
                    ['name' => 'titulo', 'type' => 'text', 'label' => 'Título del servicio'],
                    ['name' => 'descripcion_corta', 'type' => 'textarea', 'label' => 'Descripción corta'],
                    ['name' => 'contenido', 'type' => 'editor', 'label' => 'Contenido detallado'],
                    ['name' => 'icono', 'type' => 'icon', 'label' => 'Icono']
                ], 'min' => 2, 'max' => 10]
            ],
            'servicios_tabs' => [
                ['name' => 'categorias', 'type' => 'repeater', 'label' => 'Categorías/Pestañas', 'fields' => [
                    ['name' => 'nombre', 'type' => 'text', 'label' => 'Nombre de la pestaña'],
                    ['name' => 'icono', 'type' => 'icon', 'label' => 'Icono'],
                    ['name' => 'servicios', 'type' => 'textarea', 'label' => 'Servicios (uno por línea)']
                ], 'min' => 2, 'max' => 6]
            ],
            'cta_servicios' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título del CTA'],
                ['name' => 'texto', 'type' => 'textarea', 'label' => 'Texto'],
                ['name' => 'boton_texto', 'type' => 'text', 'label' => 'Texto del botón'],
                ['name' => 'boton_url', 'type' => 'url', 'label' => 'URL del botón'],
                ['name' => 'estilo', 'type' => 'select', 'label' => 'Estilo', 'options' => ['banner', 'card', 'minimal']]
            ],
            'faq_servicios' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título de la sección'],
                ['name' => 'preguntas', 'type' => 'repeater', 'label' => 'Preguntas frecuentes', 'fields' => [
                    ['name' => 'pregunta', 'type' => 'text', 'label' => 'Pregunta'],
                    ['name' => 'respuesta', 'type' => 'editor', 'label' => 'Respuesta']
                ], 'min' => 3, 'max' => 10]
            ],
            'testimonios_servicios' => [
                ['name' => 'titulo', 'type' => 'text', 'label' => 'Título de la sección'],
                ['name' => 'items', 'type' => 'repeater', 'label' => 'Testimonios', 'fields' => [
                    ['name' => 'texto', 'type' => 'textarea', 'label' => 'Testimonio'],
                    ['name' => 'nombre', 'type' => 'text', 'label' => 'Nombre'],
                    ['name' => 'cargo', 'type' => 'text', 'label' => 'Cargo/Institución'],
                    ['name' => 'foto', 'type' => 'image', 'label' => 'Foto']
                ], 'min' => 2, 'max' => 6]
            ]
        ];

        return $definitions[$tipoBloque] ?? [];
    }
}
