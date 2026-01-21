<?php
/**
 * OpenAIService
 *
 * Servicio centralizado para interactuar con la API de OpenAI.
 * Reutilizable en todo el proyecto VERUMax.
 *
 * @updated 2025-11-23 02:30 - Fix tokens para modelo de razonamiento
 *
 * Usos posibles:
 * - Autocompletar campos de templates (Identitas)
 * - Generar descripciones de cursos (Certificatum)
 * - Crear textos SEO automaticos
 * - Generar respuestas para FAQ
 * - Resumir contenido
 * - Traducir textos
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

use Exception;

class OpenAIService
{
    /**
     * URL base de la API de OpenAI
     */
    private const API_URL = 'https://api.openai.com/v1/chat/completions';

    /**
     * Modelo por defecto - Se obtiene de config.php
     */
    private static function getDefaultModel(): string
    {
        return defined('VERUMAX_IA_MODEL') ? VERUMAX_IA_MODEL : 'gpt-5-nano-2025-08-07';
    }

    /**
     * API Key de OpenAI - Se obtiene de config.php
     */
    private static ?string $apiKey = null;

    /**
     * Inicializa la API Key desde config.php si no esta configurada
     */
    private static function initFromConfig(): void
    {
        if (self::$apiKey === null && defined('VERUMAX_IA_API_KEY')) {
            self::$apiKey = VERUMAX_IA_API_KEY;
        }
    }

    /**
     * Cache en memoria para respuestas (evita llamadas repetidas)
     */
    private static array $cache = [];

    /**
     * Configura la API Key de OpenAI
     *
     * @param string $apiKey
     * @return void
     */
    public static function setApiKey(string $apiKey): void
    {
        self::$apiKey = $apiKey;
    }

    /**
     * Verifica si la IA esta habilitada para una institucion
     *
     * @param string $slug Slug de la institucion
     * @return bool
     */
    public static function isEnabledForInstitution(string $slug): bool
    {
        try {
            // Usar DatabaseService centralizado (lee credenciales del .env)
            $result = DatabaseService::fetchOne('general',
                "SELECT ia_habilitada FROM instances WHERE slug = :slug",
                ['slug' => $slug]
            );
            return !empty($result['ia_habilitada']);
        } catch (Exception $e) {
            error_log("OpenAIService::isEnabledForInstitution error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si el servicio esta configurado y disponible
     *
     * @return bool
     */
    public static function isConfigured(): bool
    {
        self::initFromConfig();
        return !empty(self::$apiKey);
    }

    /**
     * Realiza una llamada a la API de OpenAI
     *
     * @param string $prompt El prompt/instruccion para el modelo
     * @param array $options Opciones adicionales
     * @return array ['success' => bool, 'content' => string, 'error' => string|null]
     */
    public static function chat(string $prompt, array $options = []): array
    {
        if (!self::isConfigured()) {
            return [
                'success' => false,
                'content' => '',
                'error' => 'Servicio de IA no configurado'
            ];
        }

        // Opciones por defecto
        $model = $options['model'] ?? self::getDefaultModel();
        $maxTokens = $options['max_tokens'] ?? 500;
        $temperature = $options['temperature'] ?? 0.7;
        $systemPrompt = $options['system'] ?? 'Eres un redactor profesional. Responde en espanol.';

        // Verificar cache
        $cacheKey = md5($prompt . $model . $systemPrompt);
        if (isset(self::$cache[$cacheKey]) && !($options['no_cache'] ?? false)) {
            return self::$cache[$cacheKey];
        }

        // Detectar si es modelo gpt-5-nano (usa parametros diferentes)
        $isNanoModel = strpos($model, 'gpt-5-nano') !== false;

        // Preparar mensajes - para nano, omitir system message para ahorrar tokens
        if ($isNanoModel) {
            $messages = [
                ['role' => 'user', 'content' => $prompt]
            ];
            // gpt-5-nano usa max_completion_tokens y no soporta temperature
            $data = [
                'model' => $model,
                'messages' => $messages,
                'max_completion_tokens' => $maxTokens
            ];
        } else {
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt]
            ];
            // Otros modelos usan max_tokens y temperature
            $data = [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature
            ];
        }

        // Realizar llamada
        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . self::$apiKey
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Manejar errores de curl
        if ($curlError) {
            return [
                'success' => false,
                'content' => '',
                'error' => 'Error de conexion: ' . $curlError
            ];
        }

        // Parsear respuesta
        $result = json_decode($response, true);

        // Verificar errores de API
        if ($httpCode !== 200 || isset($result['error'])) {
            return [
                'success' => false,
                'content' => '',
                'error' => $result['error']['message'] ?? 'Error HTTP ' . $httpCode
            ];
        }

        // Extraer contenido - verificar diferentes estructuras de respuesta
        $content = '';
        if (isset($result['choices'][0]['message']['content'])) {
            $content = $result['choices'][0]['message']['content'];
        } elseif (isset($result['choices'][0]['text'])) {
            // Formato alternativo de algunos modelos
            $content = $result['choices'][0]['text'];
        } elseif (isset($result['output'])) {
            // Otro formato posible
            $content = is_array($result['output']) ? json_encode($result['output']) : $result['output'];
        }

        $response = [
            'success' => true,
            'content' => trim($content),
            'error' => null,
            'usage' => $result['usage'] ?? null
        ];

        // Guardar en cache
        self::$cache[$cacheKey] = $response;

        return $response;
    }

    /**
     * Genera texto para autocompletar un campo de formulario
     *
     * @param string $fieldName Nombre del campo
     * @param string $fieldLabel Etiqueta del campo
     * @param string $fieldType Tipo de campo (text, textarea, editor)
     * @param array $context Contexto de la institucion
     * @return array
     */
    public static function autocompletarCampo(string $fieldName, string $fieldLabel, string $fieldType, array $context): array
    {
        $nombreInstitucion = $context['nombre'] ?? 'la institucion';
        $nombreCompleto = $context['nombre_completo'] ?? $nombreInstitucion;
        $tipoInstitucion = $context['tipo'] ?? 'institucion educativa';
        $bloque = $context['bloque'] ?? '';
        $existingValues = $context['existing_values'] ?? [];

        // Obtener prompt especifico segun el campo
        $promptData = self::getPromptParaCampo($fieldName, $fieldLabel, $fieldType, $nombreCompleto, $tipoInstitucion, $bloque, $existingValues);

        return self::chat($promptData['prompt'], [
            'max_tokens' => $promptData['max_tokens'],
            'temperature' => 0.7
        ]);
    }

    /**
     * Genera prompt especifico segun el tipo de campo y su contexto
     *
     * @param string $fieldName Nombre del campo
     * @param string $fieldLabel Etiqueta del campo
     * @param string $fieldType Tipo de campo
     * @param string $nombreInstitucion Nombre de la institucion
     * @param string $tipoInstitucion Tipo de institucion
     * @param string $bloque Nombre del bloque (stats, equipo, valores, etc.)
     * @param array $existingValues Valores existentes en otros items (para evitar duplicados)
     * @return array ['prompt' => string, 'max_tokens' => int]
     */
    private static function getPromptParaCampo(string $fieldName, string $fieldLabel, string $fieldType, string $nombreInstitucion, string $tipoInstitucion, string $bloque = '', array $existingValues = []): array
    {
        $fieldNameLower = strtolower($fieldName);
        $fieldLabelLower = strtolower($fieldLabel);
        $bloqueLower = strtolower($bloque);

        // Preparar texto de exclusion si hay valores existentes
        $exclusionText = '';
        if (!empty($existingValues)) {
            $exclusionText = "\nIMPORTANTE: NO usar ninguno de estos valores que ya existen: " . implode(', ', $existingValues) . ". Genera uno DIFERENTE.";
        }

        // =====================================================================
        // DETECCION POR BLOQUE - PRIORIDAD MAXIMA
        // =====================================================================

        // Bloque STATS - Estadisticas
        if (strpos($bloqueLower, 'stats') !== false || strpos($bloqueLower, 'mision_con_stats') !== false) {
            if ($fieldNameLower === 'titulo') {
                return [
                    'prompt' => "Genera un numero o estadistica impactante (1 dato corto) para mostrar en la web de {$nombreInstitucion}.
Debe ser un numero con simbolo, NO una frase.
Ejemplos: 500+, 100%, 25, 10K, 15+, 98%, 50+, 1000+.{$exclusionText}
Responde SOLO con el numero/estadistica, sin texto adicional ni explicaciones.",
                    'max_tokens' => 30
                ];
            }
            if ($fieldNameLower === 'texto') {
                return [
                    'prompt' => "Genera una etiqueta corta (2-4 palabras) para describir una estadistica de {$nombreInstitucion}.
NO incluir numeros. Solo la etiqueta descriptiva.
Ejemplos: Clientes satisfechos, Anos de experiencia, Proyectos completados, Profesionales certificados, Cursos dictados, Alumnos egresados.{$exclusionText}
Responde SOLO con la etiqueta.",
                    'max_tokens' => 50
                ];
            }
        }

        // Bloque EQUIPO - Miembros del equipo
        if (strpos($bloqueLower, 'equipo') !== false) {
            if ($fieldNameLower === 'titulo') {
                return [
                    'prompt' => "Genera un titulo para la seccion de equipo (3-6 palabras) de {$nombreInstitucion}.
Ejemplos: Nuestro Equipo, Conoce a Nuestro Equipo, Quienes Somos, El Equipo Detras, Profesionales Comprometidos.
Responde SOLO con el titulo, sin puntuacion final.",
                    'max_tokens' => 50
                ];
            }
            if ($fieldNameLower === 'subtitulo') {
                return [
                    'prompt' => "Genera un subtitulo para la seccion de equipo (10-20 palabras) de {$nombreInstitucion}.
Debe complementar el titulo y describir brevemente al equipo.
Ejemplo: 'Un grupo de profesionales dedicados a brindar el mejor servicio con experiencia y compromiso'.
Responde SOLO con el subtitulo, sin comillas.",
                    'max_tokens' => 80
                ];
            }
            if ($fieldNameLower === 'nombre') {
                return [
                    'prompt' => "Genera un nombre de persona ficticio pero realista para un miembro de equipo de una {$tipoInstitucion} en Argentina.
Ejemplos: Maria Gonzalez, Carlos Rodriguez, Ana Martinez, Juan Pablo Fernandez.{$exclusionText}
Responde SOLO con el nombre completo, sin titulo ni cargo.",
                    'max_tokens' => 50
                ];
            }
            if ($fieldNameLower === 'cargo') {
                return [
                    'prompt' => "Genera un cargo profesional (2-4 palabras) para un miembro de equipo de {$nombreInstitucion}.
Ejemplos: Director General, Coordinadora Academica, Jefe de Investigacion, Secretario Ejecutivo.{$exclusionText}
Responde SOLO con el cargo, sin nombre ni explicaciones.",
                    'max_tokens' => 50
                ];
            }
        }

        // Bloque MISION_VISION - Valores institucionales
        if (strpos($bloqueLower, 'mision_vision') !== false || strpos($bloqueLower, 'valores') !== false) {
            if ($fieldNameLower === 'nombre') {
                return [
                    'prompt' => "Genera UN SOLO valor institucional (1-2 palabras maximo) para {$nombreInstitucion}.
Ejemplos: Integridad, Compromiso, Excelencia, Innovacion, Responsabilidad, Etica, Respeto, Colaboracion, Transparencia.{$exclusionText}
Responde SOLO con el valor, sin puntuacion ni explicaciones.",
                    'max_tokens' => 30
                ];
            }
        }

        // Bloque AREAS_INVESTIGACION
        if (strpos($bloqueLower, 'areas_investigacion') !== false) {
            if ($fieldNameLower === 'titulo') {
                return [
                    'prompt' => "Genera un titulo para la seccion de areas de investigacion (3-6 palabras) de {$nombreInstitucion}.
Ejemplos: Nuestras Areas de Investigacion, Lineas de Trabajo, Areas de Especializacion, Campos de Estudio.
Responde SOLO con el titulo, sin puntuacion final.",
                    'max_tokens' => 50
                ];
            }
            if ($fieldNameLower === 'nombre') {
                return [
                    'prompt' => "Genera el nombre de un area de investigacion o especializacion (2-5 palabras) para {$nombreInstitucion}.
NO es un nombre de persona. Es un campo de estudio o investigacion.
Ejemplos: Justicia Restaurativa, Mediacion Comunitaria, Resolucion de Conflictos, Derechos Humanos.{$exclusionText}
Responde SOLO con el nombre del area.",
                    'max_tokens' => 50
                ];
            }
        }

        // Bloque PUBLICACIONES
        if (strpos($bloqueLower, 'publicaciones') !== false) {
            if ($fieldNameLower === 'titulo') {
                return [
                    'prompt' => "Genera un titulo para la seccion de publicaciones (3-6 palabras) de {$nombreInstitucion}.
Ejemplos: Nuestras Publicaciones, Investigaciones y Articulos, Produccion Academica, Biblioteca de Recursos.
Responde SOLO con el titulo, sin puntuacion final.",
                    'max_tokens' => 50
                ];
            }
        }

        // Bloque RECONOCIMIENTOS
        if (strpos($bloqueLower, 'reconocimientos') !== false) {
            if ($fieldNameLower === 'titulo') {
                return [
                    'prompt' => "Genera un titulo para la seccion de reconocimientos (3-6 palabras) de {$nombreInstitucion}.
Ejemplos: Reconocimientos y Premios, Nuestros Logros, Distinciones Recibidas, Trayectoria de Excelencia.
Responde SOLO con el titulo, sin puntuacion final.",
                    'max_tokens' => 50
                ];
            }
            if ($fieldNameLower === 'nombre') {
                return [
                    'prompt' => "Genera el nombre de un reconocimiento o premio (3-6 palabras) que podria recibir {$nombreInstitucion}.
Ejemplos: Premio a la Excelencia Academica, Certificacion de Calidad, Reconocimiento a la Innovacion Social.{$exclusionText}
Responde SOLO con el nombre del premio.",
                    'max_tokens' => 60
                ];
            }
        }

        // =====================================================================
        // CAMPOS DE VALORES INSTITUCIONALES (fallback por label)
        // =====================================================================
        if ($fieldNameLower === 'nombre' && (
            strpos($fieldLabelLower, 'valor') !== false ||
            strpos($fieldLabelLower, 'value') !== false
        )) {
            return [
                'prompt' => "Genera UN SOLO valor institucional (1-2 palabras maximo) para {$nombreInstitucion}.
Ejemplos: Integridad, Compromiso, Excelencia, Innovacion, Responsabilidad, Etica, Respeto, Colaboracion.
Responde SOLO con el valor, sin puntuacion ni explicaciones.",
                'max_tokens' => 50
            ];
        }

        // =====================================================================
        // CAMPOS DE EQUIPO / PERSONAS
        // =====================================================================
        if ($fieldNameLower === 'nombre' && (
            strpos($fieldLabelLower, 'integrante') !== false ||
            strpos($fieldLabelLower, 'miembro') !== false ||
            strpos($fieldLabelLower, 'persona') !== false ||
            strpos($fieldLabelLower, 'nombre') !== false
        )) {
            return [
                'prompt' => "Genera un nombre de persona ficticio pero realista para un miembro de equipo de una {$tipoInstitucion} en Argentina.
Ejemplos: Maria Gonzalez, Carlos Rodriguez, Ana Martinez, Juan Pablo Fernandez.
Responde SOLO con el nombre completo, sin titulo ni cargo.",
                'max_tokens' => 50
            ];
        }

        if ($fieldNameLower === 'cargo') {
            return [
                'prompt' => "Genera un cargo profesional (2-4 palabras) para un miembro de equipo de {$nombreInstitucion}.
Ejemplos: Director General, Coordinadora Academica, Jefe de Investigacion, Secretario Ejecutivo, Responsable de Comunicacion.
Responde SOLO con el cargo, sin nombre ni explicaciones.",
                'max_tokens' => 50
            ];
        }

        if ($fieldNameLower === 'bio' || strpos($fieldLabelLower, 'biografia') !== false) {
            return [
                'prompt' => "Genera una biografia profesional breve (2 oraciones maximo, 30 palabras) para un miembro de equipo de {$nombreInstitucion}.
Debe mencionar experiencia y especialidad. No inventar nombre.
Ejemplo: 'Profesional con mas de 15 anos de experiencia en el sector. Especialista en desarrollo institucional y gestion de proyectos.'
Responde SOLO con la biografia.",
                'max_tokens' => 150
            ];
        }

        // =====================================================================
        // CAMPOS DE ESTADISTICAS / STATS
        // =====================================================================
        if (strpos($fieldLabelLower, 'titulo') !== false && (
            strpos($fieldLabelLower, 'stat') !== false ||
            strpos($fieldLabelLower, 'estadistica') !== false
        )) {
            return [
                'prompt' => "Genera un numero o estadistica impactante (1 dato) para mostrar en la web de {$nombreInstitucion}.
Ejemplos: 500+, 100%, 25, 10K, 15+, 98%.
Responde SOLO con el numero/estadistica, sin texto adicional.",
                'max_tokens' => 30
            ];
        }

        // Stats: titulo generico (numero)
        if ($fieldNameLower === 'titulo' && $fieldType === 'text') {
            // Verificamos si el label sugiere que es un stat
            if (strpos($fieldLabelLower, 'item') !== false) {
                return [
                    'prompt' => "Genera un numero o estadistica impactante para una {$tipoInstitucion}.
Ejemplos: 500+, 100%, 25, 10K, 15+, 98%, 50+.
Responde SOLO con el numero, sin texto.",
                    'max_tokens' => 30
                ];
            }
        }

        // Stats: descripcion/etiqueta
        if ($fieldNameLower === 'texto' && $fieldType === 'textarea') {
            return [
                'prompt' => "Genera una etiqueta corta (2-4 palabras) para describir una estadistica de {$nombreInstitucion}.
Ejemplos: Clientes satisfechos, Anos de experiencia, Proyectos completados, Profesionales certificados, Cursos dictados.
Responde SOLO con la etiqueta, sin el numero.",
                'max_tokens' => 50
            ];
        }

        // =====================================================================
        // CAMPOS DE AREAS / INVESTIGACION
        // =====================================================================
        if ($fieldNameLower === 'nombre' && (
            strpos($fieldLabelLower, 'area') !== false ||
            strpos($fieldLabelLower, 'investigacion') !== false
        )) {
            return [
                'prompt' => "Genera el nombre de un area de especializacion o investigacion (2-5 palabras) para {$nombreInstitucion}.
Ejemplos: Desarrollo Sostenible, Innovacion Educativa, Gestion del Conocimiento, Tecnologia Aplicada.
Responde SOLO con el nombre del area.",
                'max_tokens' => 50
            ];
        }

        // =====================================================================
        // CAMPOS DE RECONOCIMIENTOS
        // =====================================================================
        if ($fieldNameLower === 'nombre' && strpos($fieldLabelLower, 'reconocimiento') !== false) {
            return [
                'prompt' => "Genera el nombre de un reconocimiento o premio (3-6 palabras) que podria recibir {$nombreInstitucion}.
Ejemplos: Premio a la Excelencia Academica, Certificacion de Calidad ISO, Reconocimiento a la Innovacion.
Responde SOLO con el nombre del premio.",
                'max_tokens' => 60
            ];
        }

        if ($fieldNameLower === 'otorgante') {
            return [
                'prompt' => "Genera el nombre de una institucion que otorga premios o reconocimientos en Argentina (3-6 palabras).
Ejemplos: Ministerio de Educacion, Fundacion para la Excelencia, Consejo Nacional de Calidad.
Responde SOLO con el nombre de la institucion.",
                'max_tokens' => 60
            ];
        }

        // =====================================================================
        // CAMPOS DE PUBLICACIONES
        // =====================================================================
        if ($fieldNameLower === 'titulo' && strpos($fieldLabelLower, 'publicacion') !== false) {
            return [
                'prompt' => "Genera el titulo de una publicacion academica o articulo (6-12 palabras) relacionado con el area de {$nombreInstitucion}.
Ejemplo: 'Avances en la implementacion de metodologias innovadoras en educacion superior'.
Responde SOLO con el titulo.",
                'max_tokens' => 80
            ];
        }

        if ($fieldNameLower === 'autores') {
            return [
                'prompt' => "Genera una lista de autores (2-3 nombres) para una publicacion academica.
Formato: Apellido, N., Apellido, M., Apellido, J.
Ejemplo: Rodriguez, M., Fernandez, A., Lopez, J.
Responde SOLO con los autores en ese formato.",
                'max_tokens' => 60
            ];
        }

        if ($fieldNameLower === 'revista') {
            return [
                'prompt' => "Genera el nombre de una revista o editorial academica (3-6 palabras).
Ejemplos: Revista Argentina de Educacion, Editorial Academica Nacional, Journal of Social Sciences.
Responde SOLO con el nombre.",
                'max_tokens' => 60
            ];
        }

        // =====================================================================
        // CAMPOS DE PREGUNTAS FRECUENTES
        // =====================================================================
        if ($fieldNameLower === 'pregunta') {
            return [
                'prompt' => "Genera una pregunta frecuente (1 oracion) que los usuarios podrian hacer sobre {$nombreInstitucion}.
Ejemplos: Cuales son los horarios de atencion?, Como puedo inscribirme?, Que requisitos necesito?
Responde SOLO con la pregunta.",
                'max_tokens' => 80
            ];
        }

        if ($fieldNameLower === 'respuesta' && $fieldType === 'editor') {
            return [
                'prompt' => "Genera una respuesta profesional y clara (2-3 oraciones) para una pregunta frecuente de {$nombreInstitucion}.
Debe ser informativa y amable. No incluir la pregunta, solo la respuesta.
Ejemplo: 'Nuestro horario de atencion es de lunes a viernes de 9 a 18 hs. Tambien puede contactarnos por correo electronico en cualquier momento.'
Responde SOLO con la respuesta.",
                'max_tokens' => 200
            ];
        }

        // =====================================================================
        // CAMPOS DE TESTIMONIOS
        // =====================================================================
        if ($fieldNameLower === 'texto' && strpos($fieldLabelLower, 'testimonio') !== false) {
            return [
                'prompt' => "Genera un testimonio positivo breve (2-3 oraciones) sobre {$nombreInstitucion}.
Debe sonar autentico y profesional.
Ejemplo: 'Excelente experiencia. El equipo es muy profesional y atento. Totalmente recomendado.'
Responde SOLO con el testimonio, sin comillas.",
                'max_tokens' => 150
            ];
        }

        // =====================================================================
        // CAMPOS DE SERVICIOS
        // =====================================================================
        if ($fieldNameLower === 'titulo' && strpos($fieldLabelLower, 'servicio') !== false) {
            return [
                'prompt' => "Genera el nombre de un servicio (2-4 palabras) que podria ofrecer {$nombreInstitucion}.
Ejemplos: Consultoria Especializada, Capacitacion Profesional, Asesoria Integral, Gestion de Proyectos.
Responde SOLO con el nombre del servicio.",
                'max_tokens' => 50
            ];
        }

        if ($fieldNameLower === 'descripcion' && strpos($fieldLabelLower, 'servicio') !== false) {
            return [
                'prompt' => "Genera una descripcion breve (2-3 oraciones) de un servicio profesional de {$nombreInstitucion}.
Debe explicar el valor que ofrece. No incluir el nombre del servicio.
Responde SOLO con la descripcion.",
                'max_tokens' => 200
            ];
        }

        // =====================================================================
        // CAMPOS DE CTA (Call to Action)
        // =====================================================================
        if (strpos($fieldNameLower, 'boton') !== false || strpos($fieldLabelLower, 'boton') !== false) {
            return [
                'prompt' => "Genera el texto de un boton de accion (2-4 palabras) para la web de {$nombreInstitucion}.
Ejemplos: Contactanos ahora, Solicitar informacion, Ver mas, Inscribirse, Descargar brochure.
Responde SOLO con el texto del boton.",
                'max_tokens' => 30
            ];
        }

        if (strpos($fieldNameLower, 'cta') !== false) {
            return [
                'prompt' => "Genera un titulo llamativo para una seccion de llamada a la accion (5-10 palabras) de {$nombreInstitucion}.
Ejemplos: Comienza tu camino hacia la excelencia hoy, Descubre todo lo que podemos ofrecerte.
Responde SOLO con el titulo.",
                'max_tokens' => 60
            ];
        }

        // =====================================================================
        // CAMPOS DE TIMELINE / EVENTOS
        // =====================================================================
        if ($fieldNameLower === 'anio' || $fieldNameLower === 'ano') {
            return [
                'prompt' => "Genera un ano (solo el numero, 4 digitos) entre 2000 y 2024 para un hito historico.
Responde SOLO con el ano, ejemplo: 2015",
                'max_tokens' => 20
            ];
        }

        if ($fieldNameLower === 'evento' || (strpos($fieldLabelLower, 'titulo') !== false && strpos($fieldLabelLower, 'evento') !== false)) {
            return [
                'prompt' => "Genera el titulo de un hito o evento historico (4-8 palabras) para {$nombreInstitucion}.
Ejemplos: Inauguracion de nueva sede, Lanzamiento del programa de becas, Primera certificacion internacional.
Responde SOLO con el titulo del evento.",
                'max_tokens' => 60
            ];
        }

        // =====================================================================
        // CAMPOS GENERICOS POR TIPO
        // =====================================================================

        // Titulos genericos
        if ($fieldNameLower === 'titulo' || strpos($fieldLabelLower, 'titulo') !== false) {
            return [
                'prompt' => "Genera un titulo atractivo (4-8 palabras) para la seccion \"{$fieldLabel}\" de {$nombreInstitucion}.
Debe ser profesional y descriptivo. Sin puntuacion final.
Responde SOLO con el titulo.",
                'max_tokens' => 60
            ];
        }

        // Subtitulos
        if ($fieldNameLower === 'subtitulo' || strpos($fieldLabelLower, 'subtitulo') !== false) {
            return [
                'prompt' => "Genera un subtitulo complementario (8-15 palabras) para {$nombreInstitucion}.
Debe ampliar el titulo principal y ser atractivo. Sin puntuacion final.
Ejemplo: 'Liderando el camino hacia la excelencia con compromiso y dedicacion'.
Responde SOLO con el subtitulo.",
                'max_tokens' => 80
            ];
        }

        // Mision
        if (strpos($fieldNameLower, 'mision') !== false || strpos($fieldLabelLower, 'mision') !== false) {
            return [
                'prompt' => "Genera una declaracion de mision (2-3 oraciones) para {$nombreInstitucion}.
Debe explicar el proposito y como se logra. Tono profesional.
Responde SOLO con el texto de la mision.",
                'max_tokens' => 250
            ];
        }

        // Vision
        if (strpos($fieldNameLower, 'vision') !== false || strpos($fieldLabelLower, 'vision') !== false) {
            return [
                'prompt' => "Genera una declaracion de vision (2-3 oraciones) para {$nombreInstitucion}.
Debe describir la aspiracion futura. Tono inspirador y profesional.
Responde SOLO con el texto de la vision.",
                'max_tokens' => 250
            ];
        }

        // Descripcion generica
        if ($fieldNameLower === 'descripcion' || strpos($fieldLabelLower, 'descripcion') !== false) {
            return [
                'prompt' => "Genera una descripcion profesional (2-3 oraciones) para el campo \"{$fieldLabel}\" de {$nombreInstitucion}.
Responde SOLO con la descripcion.",
                'max_tokens' => 200
            ];
        }

        // Cita
        if ($fieldNameLower === 'cita' || strpos($fieldLabelLower, 'cita destacada') !== false) {
            return [
                'prompt' => "Genera una cita inspiradora (1-2 oraciones) relacionada con el campo de {$nombreInstitucion}.
Debe ser motivacional y profesional. Sin comillas.
Responde SOLO con la cita.",
                'max_tokens' => 100
            ];
        }

        // Autor de cita - SOLO nombre de persona
        if ($fieldNameLower === 'autor_cita') {
            return [
                'prompt' => "Genera SOLO el nombre de una persona famosa (pensador, cientifico, lider) que podria ser autor de una cita inspiradora.
NO generar una cita ni frase. SOLO el nombre de la persona.
Ejemplos correctos: Albert Einstein, Nelson Mandela, Paulo Freire, Mahatma Gandhi.
Responde UNICAMENTE con el nombre (2-3 palabras maximo).",
                'max_tokens' => 30
            ];
        }

        // =====================================================================
        // CAMPOS URL
        // =====================================================================
        if ($fieldType === 'url') {
            if (strpos($fieldNameLower, 'linkedin') !== false) {
                return [
                    'prompt' => "Genera una URL de perfil de LinkedIn ficticia pero realista.
Formato: https://linkedin.com/in/nombre-apellido
Responde SOLO con la URL.",
                    'max_tokens' => 50
                ];
            }
            return [
                'prompt' => "Genera una URL de ejemplo apropiada para el campo \"{$fieldLabel}\".
Formato: https://ejemplo.com/pagina o #seccion para enlaces internos.
Responde SOLO con la URL.",
                'max_tokens' => 50
            ];
        }

        // =====================================================================
        // FALLBACK GENERICO
        // =====================================================================
        $longitud = match($fieldType) {
            'text' => 'muy breve (maximo 8 palabras)',
            'textarea' => 'un parrafo corto (2-3 oraciones)',
            'editor' => 'uno o dos parrafos (4-6 oraciones)',
            'email' => 'un email de ejemplo como contacto@ejemplo.com',
            default => 'breve y conciso'
        };

        return [
            'prompt' => "Genera contenido {$longitud} para el campo \"{$fieldLabel}\" del sitio web de {$nombreInstitucion}.
Contexto: {$tipoInstitucion}. Tono profesional.
Responde SOLO con el contenido, sin explicaciones.",
            'max_tokens' => $fieldType === 'editor' ? 400 : ($fieldType === 'textarea' ? 200 : 80)
        ];
    }

    /**
     * Genera contenido SEO (titulo y descripcion)
     *
     * @param string $pageName Nombre de la pagina
     * @param array $context Contexto de la institucion
     * @return array ['titulo' => string, 'descripcion' => string]
     */
    public static function generarSEO(string $pageName, array $context): array
    {
        $nombreInstitucion = $context['nombre_completo'] ?? $context['nombre'] ?? 'Institucion';

        $prompt = "Genera un titulo SEO y una meta descripcion para la pagina \"{$pageName}\" del sitio web de \"{$nombreInstitucion}\".

Requisitos:
- Titulo: maximo 60 caracteres, incluir nombre de la institucion
- Descripcion: maximo 155 caracteres, persuasiva y con call to action
- Espanol argentino formal

Responde en formato JSON exacto:
{\"titulo\": \"...\", \"descripcion\": \"...\"}";

        $result = self::chat($prompt, ['max_tokens' => 150, 'temperature' => 0.6]);

        if ($result['success']) {
            $parsed = json_decode($result['content'], true);
            if ($parsed) {
                return [
                    'success' => true,
                    'titulo' => $parsed['titulo'] ?? '',
                    'descripcion' => $parsed['descripcion'] ?? ''
                ];
            }
        }

        return [
            'success' => false,
            'titulo' => '',
            'descripcion' => '',
            'error' => $result['error'] ?? 'Error al parsear respuesta'
        ];
    }

    /**
     * Genera descripcion para un curso
     *
     * @param string $nombreCurso Nombre del curso
     * @param int $cargaHoraria Carga horaria
     * @param array $context Contexto adicional
     * @return array
     */
    public static function generarDescripcionCurso(string $nombreCurso, int $cargaHoraria, array $context = []): array
    {
        $nombreInstitucion = $context['nombre_institucion'] ?? 'la institucion';

        $prompt = "Genera una descripcion atractiva para el siguiente curso:

Nombre del curso: {$nombreCurso}
Carga horaria: {$cargaHoraria} horas
Institucion: {$nombreInstitucion}

Requisitos:
- 2-3 parrafos
- Incluir objetivos del curso
- Mencionar a quien va dirigido
- Tono profesional y motivador
- Espanol argentino

Genera SOLO la descripcion, sin encabezados.";

        return self::chat($prompt, ['max_tokens' => 400, 'temperature' => 0.7]);
    }

    /**
     * Genera preguntas frecuentes basadas en el contexto
     *
     * @param string $tema Tema para las FAQ
     * @param int $cantidad Cantidad de preguntas
     * @param array $context Contexto
     * @return array
     */
    public static function generarFAQ(string $tema, int $cantidad = 5, array $context = []): array
    {
        $nombreInstitucion = $context['nombre_completo'] ?? $context['nombre'] ?? 'la institucion';

        $prompt = "Genera {$cantidad} preguntas frecuentes con sus respuestas sobre \"{$tema}\" para el sitio web de \"{$nombreInstitucion}\".

Requisitos:
- Preguntas relevantes y comunes
- Respuestas claras y concisas (2-3 oraciones cada una)
- Espanol argentino formal

Responde en formato JSON:
[{\"pregunta\": \"...\", \"respuesta\": \"...\"}, ...]";

        $result = self::chat($prompt, ['max_tokens' => 800, 'temperature' => 0.7]);

        if ($result['success']) {
            $parsed = json_decode($result['content'], true);
            if (is_array($parsed)) {
                return [
                    'success' => true,
                    'faqs' => $parsed
                ];
            }
        }

        return [
            'success' => false,
            'faqs' => [],
            'error' => $result['error'] ?? 'Error al parsear respuesta'
        ];
    }

    /**
     * Resume un texto largo
     *
     * @param string $texto Texto a resumir
     * @param int $maxPalabras Maximo de palabras en el resumen
     * @return array
     */
    public static function resumir(string $texto, int $maxPalabras = 50): array
    {
        $prompt = "Resume el siguiente texto en maximo {$maxPalabras} palabras, manteniendo las ideas principales:

{$texto}

Genera SOLO el resumen, sin introduccion.";

        return self::chat($prompt, ['max_tokens' => $maxPalabras * 2, 'temperature' => 0.5]);
    }

    /**
     * Mejora/reescribe un texto
     *
     * @param string $texto Texto original
     * @param string $estilo Estilo deseado (formal, amigable, profesional)
     * @return array
     */
    public static function mejorarTexto(string $texto, string $estilo = 'profesional'): array
    {
        $prompt = "Mejora el siguiente texto para que sea mas {$estilo}, corrigiendo errores y mejorando la claridad:

{$texto}

Genera SOLO el texto mejorado.";

        return self::chat($prompt, ['max_tokens' => strlen($texto) * 2, 'temperature' => 0.6]);
    }

    /**
     * Limpia el cache en memoria
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Genera contenido combinado para grupos de campos relacionados
     *
     * @param string $groupType Tipo de grupo (cita_autor, titulo_subtitulo, stat, etc.)
     * @param array $context Contexto de la institucion
     * @return array ['success' => bool, 'fields' => [...], 'error' => string|null]
     */
    public static function generarCamposCombinados(string $groupType, array $context): array
    {
        $nombreInstitucion = $context['nombre_completo'] ?? $context['nombre'] ?? 'la institucion';
        $tipoInstitucion = $context['tipo'] ?? 'institucion educativa';
        $existingValues = $context['existing_values'] ?? [];

        // Preparar texto de exclusión si hay valores existentes
        $exclusionText = '';
        if (!empty($existingValues)) {
            $exclusionText = "\n\nIMPORTANTE: NO usar ninguno de estos valores que ya existen: " . implode(', ', $existingValues) . ". Debes generar algo DIFERENTE y unico.";
        }

        $prompt = '';
        $fields = [];
        $maxTokens = 200;

        switch ($groupType) {
            case 'cita_autor':
                $prompt = "Genera una cita inspiradora con su autor para el sitio web de \"{$nombreInstitucion}\".

Requisitos:
- La cita debe ser motivacional y relacionada con el campo de la institucion
- El autor debe ser una persona real y famosa (pensador, lider, cientifico)
- La cita debe tener 1-2 oraciones
- NO inventar citas, usar citas reales o parafrasear ideas conocidas

Responde en formato JSON exacto:
{\"cita\": \"texto de la cita sin comillas\", \"autor\": \"Nombre del Autor\"}";
                $fields = ['cita', 'autor_cita'];
                $maxTokens = 200;
                break;

            case 'titulo_subtitulo':
                $prompt = "Genera un titulo y subtitulo para una seccion del sitio web de \"{$nombreInstitucion}\".

Requisitos:
- Titulo: 3-6 palabras, impactante y profesional
- Subtitulo: 10-20 palabras, complementa el titulo

Responde en formato JSON exacto:
{\"titulo\": \"El titulo aqui\", \"subtitulo\": \"El subtitulo complementario aqui\"}";
                $fields = ['titulo', 'subtitulo'];
                $maxTokens = 150;
                break;

            case 'stat':
                $prompt = "Genera una estadistica impactante para mostrar en el sitio web de \"{$nombreInstitucion}\".

Requisitos:
- titulo: Un numero o estadistica (ej: 500+, 98%, 25, 10K)
- texto: Una etiqueta corta de 2-4 palabras que describe la estadistica{$exclusionText}

Responde en formato JSON exacto:
{\"titulo\": \"500+\", \"texto\": \"Clientes satisfechos\"}";
                $fields = ['titulo', 'texto'];
                $maxTokens = 80;
                break;

            case 'testimonio':
                $prompt = "Genera un testimonio positivo para el sitio web de \"{$nombreInstitucion}\".

Requisitos:
- texto: Testimonio de 2-3 oraciones, autentico y profesional
- nombre: Nombre ficticio pero realista de quien da el testimonio
- cargo: Cargo o posicion de la persona (opcional)

Responde en formato JSON exacto:
{\"texto\": \"El testimonio aqui\", \"nombre\": \"Nombre Apellido\", \"cargo\": \"Cargo de la persona\"}";
                $fields = ['texto', 'nombre', 'cargo'];
                $maxTokens = 250;
                break;

            case 'faq':
                $prompt = "Genera una pregunta frecuente con su respuesta para el sitio web de \"{$nombreInstitucion}\".

Requisitos:
- pregunta: Una pregunta comun que los usuarios podrian hacer
- respuesta: Respuesta clara y profesional de 2-3 oraciones

Responde en formato JSON exacto:
{\"pregunta\": \"La pregunta aqui?\", \"respuesta\": \"La respuesta profesional aqui.\"}";
                $fields = ['pregunta', 'respuesta'];
                $maxTokens = 250;
                break;

            case 'timeline_evento':
                $prompt = "Genera un evento o hito historico para la linea de tiempo de \"{$nombreInstitucion}\".

Requisitos:
- anio: Un ano entre 2000 y 2024
- titulo: Titulo del evento (4-8 palabras)
- descripcion: Descripcion breve del evento (1-2 oraciones){$exclusionText}

Responde en formato JSON exacto:
{\"anio\": \"2015\", \"titulo\": \"Titulo del evento\", \"descripcion\": \"Descripcion del hito alcanzado.\"}";
                $fields = ['anio', 'titulo', 'descripcion'];
                $maxTokens = 200;
                break;

            case 'cta':
                $prompt = "Genera contenido para una seccion de llamada a la accion (CTA) del sitio web de \"{$nombreInstitucion}\".

Requisitos:
- titulo: Titulo llamativo de 5-10 palabras
- texto: Texto persuasivo de 1-2 oraciones
- boton_texto: Texto del boton de 2-4 palabras

Responde en formato JSON exacto:
{\"titulo\": \"Titulo del CTA\", \"texto\": \"Texto persuasivo aqui.\", \"boton_texto\": \"Contactanos\"}";
                $fields = ['titulo', 'texto', 'boton_texto'];
                $maxTokens = 200;
                break;

            case 'miembro_equipo':
                $prompt = "Genera datos para un miembro del equipo de \"{$nombreInstitucion}\".

Requisitos:
- nombre: Nombre ficticio pero realista (nombre y apellido)
- cargo: Cargo profesional (2-4 palabras)
- bio: Biografia breve de 2 oraciones

Responde en formato JSON exacto:
{\"nombre\": \"Nombre Apellido\", \"cargo\": \"Director General\", \"bio\": \"Profesional con experiencia. Especialista en su area.\"}";
                $fields = ['nombre', 'cargo', 'bio'];
                $maxTokens = 200;
                break;

            case 'intro_academica':
                $prompt = "Genera contenido para la seccion de introduccion academica del sitio web de \"{$nombreInstitucion}\".

Requisitos:
- titulo: Titulo de la seccion (3-6 palabras)
- texto: Introduccion academica profesional (2-3 parrafos, describiendo la institucion, su enfoque academico y trayectoria)

Responde en formato JSON exacto:
{\"titulo\": \"Nuestra Trayectoria Academica\", \"texto\": \"Parrafo 1 sobre la institucion.\\n\\nParrafo 2 sobre el enfoque academico.\"}";
                $fields = ['titulo', 'texto'];
                $maxTokens = 500;
                break;

            case 'area_investigacion':
                // Lista de iconos disponibles para seleccionar
                $iconos = ['award', 'users', 'book-open', 'lightbulb', 'search', 'briefcase', 'heart', 'star', 'zap', 'shield', 'globe', 'target', 'eye', 'check-circle', 'file-text', 'folder', 'database', 'layers', 'cpu', 'activity'];
                $iconos_str = implode(', ', $iconos);

                $prompt = "Genera datos para UNA SOLA area de investigacion de \"{$nombreInstitucion}\".

IMPORTANTE: Responde con UN SOLO objeto JSON, NO un array.

Requisitos:
- icono: UN icono de esta lista: {$iconos_str}
- nombre: Nombre del area (2-5 palabras)
- descripcion: Descripcion breve (1-2 oraciones){$exclusionText}

Responde SOLO con este formato JSON (sin corchetes, sin array):
{\"icono\": \"book-open\", \"nombre\": \"Nombre del Area\", \"descripcion\": \"Descripcion breve.\"}";
                $fields = ['icono', 'nombre', 'descripcion'];
                $maxTokens = 150;
                break;

            case 'intro_historia':
                $prompt = "Genera contenido para la seccion de introduccion historica de \"{$nombreInstitucion}\".

Requisitos:
- titulo: Titulo de la seccion (3-6 palabras, ej: Nuestra Historia, Nuestro Legado)
- texto: Texto introductorio sobre la historia de la institucion (2-3 parrafos breves)
- anio_fundacion: Año de fundacion (4 digitos, entre 1900 y " . date('Y') . ")

Responde SOLO en formato JSON:
{\"titulo\": \"Nuestra Historia\", \"texto\": \"Parrafo 1.\\n\\nParrafo 2.\", \"anio_fundacion\": \"2010\"}";
                $fields = ['titulo', 'texto', 'anio_fundacion'];
                $maxTokens = 400;
                break;

            case 'hero':
                $prompt = "Genera contenido para la seccion hero del sitio web de \"{$nombreInstitucion}\".

Requisitos:
- titulo: Titulo principal impactante (4-8 palabras)
- subtitulo: Subtitulo descriptivo que complementa (10-20 palabras)

Responde SOLO en formato JSON:
{\"titulo\": \"Titulo impactante aqui\", \"subtitulo\": \"Subtitulo descriptivo que complementa el titulo.\"}";
                $fields = ['titulo', 'subtitulo'];
                $maxTokens = 100;
                break;

            case 'mision':
                $prompt = "Genera contenido para la seccion de MISION de \"{$nombreInstitucion}\".

Requisitos:
- titulo: Titulo de la seccion (2-4 palabras, ej: Nuestra Mision)
- texto: Declaracion de mision profesional (2-3 oraciones sobre el proposito de la institucion)

Responde SOLO en formato JSON:
{\"titulo\": \"Nuestra Mision\", \"texto\": \"Declaracion de mision aqui.\"}";
                $fields = ['titulo', 'texto'];
                $maxTokens = 200;
                break;

            case 'vision':
                $prompt = "Genera contenido para la seccion de VISION de \"{$nombreInstitucion}\".

Requisitos:
- titulo: Titulo de la seccion (2-4 palabras, ej: Nuestra Vision)
- texto: Declaracion de vision profesional (2-3 oraciones sobre hacia donde aspira la institucion)

Responde SOLO en formato JSON:
{\"titulo\": \"Nuestra Vision\", \"texto\": \"Declaracion de vision aqui.\"}";
                $fields = ['titulo', 'texto'];
                $maxTokens = 200;
                break;

            case 'valor':
                $iconos = ['award', 'users', 'book-open', 'lightbulb', 'search', 'briefcase', 'heart', 'star', 'zap', 'shield', 'globe', 'target', 'eye', 'check-circle', 'file-text'];
                $iconos_str = implode(', ', $iconos);

                $prompt = "Genera UN valor institucional para \"{$nombreInstitucion}\".

IMPORTANTE: Responde con UN SOLO objeto JSON, NO un array.

Requisitos:
- icono: UN icono de esta lista: {$iconos_str}
- nombre: Nombre del valor (1-2 palabras, ej: Integridad, Excelencia)
- descripcion: Descripcion breve del valor (1-2 oraciones){$exclusionText}

Responde SOLO con este formato JSON:
{\"icono\": \"heart\", \"nombre\": \"Compromiso\", \"descripcion\": \"Descripcion del valor.\"}";
                $fields = ['icono', 'nombre', 'descripcion'];
                $maxTokens = 150;
                break;

            case 'miembro':
                $prompt = "Genera datos para UN miembro del equipo de \"{$nombreInstitucion}\".

IMPORTANTE: Responde con UN SOLO objeto JSON, NO un array.

Requisitos:
- nombre: Nombre completo ficticio pero realista
- cargo: Cargo profesional (2-4 palabras)
- bio: Biografia breve (1-2 oraciones){$exclusionText}

Responde SOLO con este formato JSON:
{\"nombre\": \"Nombre Apellido\", \"cargo\": \"Director de Area\", \"bio\": \"Breve biografia profesional.\"}";
                $fields = ['nombre', 'cargo', 'bio'];
                $maxTokens = 150;
                break;

            case 'servicio_card':
                $prompt = "Genera contenido para UNA tarjeta de servicio de \"{$nombreInstitucion}\".

Requisitos:
- icono: Selecciona SOLO uno de estos iconos válidos de Lucide Icons (escribe exactamente el nombre):
  award, briefcase, certificate, check-circle, circle-check, crown, gift, graduation-cap,
  handshake, heart, lightbulb, medal, rocket, settings, shield, sparkles, star, target,
  trophy, users, zap
- titulo: Nombre del servicio (2-5 palabras)
- descripcion: Descripción breve del servicio (1-2 oraciones, máximo 120 caracteres){$exclusionText}

IMPORTANTE: El icono debe ser EXACTAMENTE uno de la lista anterior, sin modificaciones.

Responde SOLO con este formato JSON:
{\"icono\": \"award\", \"titulo\": \"Nombre del Servicio\", \"descripcion\": \"Descripción concisa del servicio ofrecido.\"}";
                $fields = ['icono', 'titulo', 'descripcion'];
                $maxTokens = 180;
                break;

            case 'servicio_accordion':
                $prompt = "Genera contenido para UN item de acordeón de servicios de \"{$nombreInstitucion}\".

Requisitos:
- icono: Selecciona SOLO uno de estos iconos válidos de Lucide Icons:
  award, briefcase, certificate, check-circle, circle-check, crown, gift, graduation-cap,
  handshake, heart, lightbulb, medal, rocket, settings, shield, sparkles, star, target,
  trophy, users, zap
  IMPORTANTE: El icono debe ser EXACTAMENTE uno de la lista anterior, sin modificaciones.
- titulo: Título del servicio (3-6 palabras)
- descripcion_corta: Resumen breve (1 oración corta, máximo 80 caracteres)
- contenido: Descripción completa del servicio (2-3 oraciones){$exclusionText}

Responde SOLO con este formato JSON:
{\"icono\": \"award\", \"titulo\": \"Título del Servicio\", \"descripcion_corta\": \"Resumen breve.\", \"contenido\": \"Descripción detallada del servicio que se ofrece.\"}";
                $fields = ['icono', 'titulo', 'descripcion_corta', 'contenido'];
                $maxTokens = 250;
                break;

            case 'servicios_header_texto':
                $prompt = "Genera encabezado de sección de servicios para \"{$nombreInstitucion}\".

Requisitos:
- titulo: Título principal de la sección de servicios (2-4 palabras)
- subtitulo: Subtítulo descriptivo (1 oración, máximo 100 caracteres)

Responde SOLO con este formato JSON:
{\"titulo\": \"Nuestros Servicios\", \"subtitulo\": \"Descripción breve que explica la propuesta de valor.\"}";
                $fields = ['titulo', 'subtitulo'];
                $maxTokens = 100;
                break;

            case 'faq_item':
                $prompt = "Genera UNA pregunta frecuente con su respuesta sobre los servicios de \"{$nombreInstitucion}\".

Requisitos:
- pregunta: Pregunta común que los usuarios hacen (formato pregunta, máximo 100 caracteres)
- respuesta: Respuesta clara y útil (1-2 oraciones){$exclusionText}

Responde SOLO con este formato JSON:
{\"pregunta\": \"¿Pregunta frecuente sobre el servicio?\", \"respuesta\": \"Respuesta clara y concisa a la pregunta.\"}";
                $fields = ['pregunta', 'respuesta'];
                $maxTokens = 150;
                break;

            case 'testimonio':
                $prompt = "Genera UN testimonio de cliente satisfecho de \"{$nombreInstitucion}\".

Requisitos:
- nombre: Nombre completo ficticio pero realista
- cargo: Cargo o rol del cliente (2-4 palabras)
- texto: Testimonio positivo sobre el servicio (2-3 oraciones, máximo 200 caracteres){$exclusionText}

Responde SOLO con este formato JSON:
{\"nombre\": \"Nombre Apellido\", \"cargo\": \"Director de Empresa\", \"texto\": \"Testimonio positivo sobre la experiencia con el servicio.\"}";
                $fields = ['nombre', 'cargo', 'texto'];
                $maxTokens = 180;
                break;

            case 'servicios_grid_header':
                $prompt = "Genera encabezado para grilla de servicios de \"{$nombreInstitucion}\".

Requisitos:
- titulo_seccion: Título de la sección (2-4 palabras)
- subtitulo: Descripción breve (1 oración, máximo 100 caracteres)

Responde SOLO con este formato JSON:
{\"titulo_seccion\": \"Servicios Destacados\", \"subtitulo\": \"Descripción de la categoría de servicios.\"}";
                $fields = ['titulo_seccion', 'subtitulo'];
                $maxTokens = 100;
                break;

            case 'servicio_grid_item':
                $prompt = "Genera contenido para UN item de grilla de servicios de \"{$nombreInstitucion}\".

Requisitos:
- titulo: Nombre del servicio (2-5 palabras)
- texto: Descripción del servicio (1-2 oraciones, máximo 120 caracteres){$exclusionText}

Responde SOLO con este formato JSON:
{\"titulo\": \"Nombre del Servicio\", \"texto\": \"Descripción breve del servicio ofrecido.\"}";
                $fields = ['titulo', 'texto'];
                $maxTokens = 120;
                break;

            case 'servicio_tab':
                $prompt = "Genera contenido para UNA pestaña de categoría de servicios de \"{$nombreInstitucion}\".

Requisitos:
- nombre: Nombre de la categoría (2-4 palabras)
- servicios: Lista de servicios en esta categoría (texto descriptivo, máximo 150 caracteres){$exclusionText}

Responde SOLO con este formato JSON:
{\"nombre\": \"Categoría de Servicios\", \"servicios\": \"Descripción de los servicios incluidos en esta categoría.\"}";
                $fields = ['nombre', 'servicios'];
                $maxTokens = 130;
                break;

            case 'cta_servicios_texto':
                $prompt = "Genera llamado a la acción (CTA) para servicios de \"{$nombreInstitucion}\".

Requisitos:
- titulo: Título persuasivo del CTA (3-6 palabras)
- texto: Mensaje motivador (1-2 oraciones, máximo 120 caracteres)
- boton_texto: Texto del botón de acción (2-4 palabras)

Responde SOLO con este formato JSON:
{\"titulo\": \"Título del CTA\", \"texto\": \"Mensaje persuasivo para motivar la acción.\", \"boton_texto\": \"Texto Botón\"}";
                $fields = ['titulo', 'texto', 'boton_texto'];
                $maxTokens = 150;
                break;

            case 'seo':
                $prompt = "Genera contenido SEO optimizado para el sitio web de \"{$nombreInstitucion}\".

Requisitos:
- seo_title: Título SEO atractivo (máximo 60 caracteres), debe incluir el nombre de la institución
- seo_description: Meta descripción persuasiva (máximo 155 caracteres), debe invitar a visitar el sitio
- seo_keywords: Palabras clave relevantes separadas por comas (5-8 palabras clave)

Responde SOLO con este formato JSON:
{\"seo_title\": \"Título SEO aquí\", \"seo_description\": \"Descripción meta aquí.\", \"seo_keywords\": \"palabra1, palabra2, palabra3\"}";
                $fields = ['seo_title', 'seo_description', 'seo_keywords'];
                $maxTokens = 250;
                break;

            case 'certificatum_stats':
                $prompt = "Genera estadísticas impactantes para el portal de certificados de \"{$nombreInstitucion}\".

Requisitos:
- stats_certificados: Un número impresionante de certificados emitidos (formato: número con + al final, ej: 500+, 1000+, 250+)
- stats_estudiantes: Un número de estudiantes activos o egresados (formato: número con + al final, ej: 300+, 800+, 150+)
- stats_cursos: Un número de cursos o programas disponibles (formato: número con + al final, ej: 15+, 25+, 10+)

Los números deben ser realistas para una institución educativa.

Responde SOLO con este formato JSON:
{\"stats_certificados\": \"500+\", \"stats_estudiantes\": \"300+\", \"stats_cursos\": \"15+\"}";
                $fields = ['stats_certificados', 'stats_estudiantes', 'stats_cursos'];
                $maxTokens = 100;
                break;

            default:
                return [
                    'success' => false,
                    'fields' => [],
                    'error' => 'Tipo de grupo no reconocido: ' . $groupType
                ];
        }

        $result = self::chat($prompt, ['max_tokens' => $maxTokens, 'temperature' => 0.7]);

        if ($result['success']) {
            $parsed = json_decode($result['content'], true);
            if ($parsed && is_array($parsed)) {
                // Si la IA devolvio un array de objetos, tomar el primero
                if (isset($parsed[0]) && is_array($parsed[0])) {
                    $parsed = $parsed[0];
                }
                return [
                    'success' => true,
                    'fields' => $parsed,
                    'field_names' => $fields
                ];
            }
            return [
                'success' => false,
                'fields' => [],
                'error' => 'Error al parsear respuesta JSON: ' . $result['content']
            ];
        }

        return [
            'success' => false,
            'fields' => [],
            'error' => $result['error'] ?? 'Error al generar contenido'
        ];
    }

    /**
     * Generar imagen con DALL-E 3
     *
     * @param array $context Contexto del evento (titulo, descripcion, anio, nombre_institucion, size)
     * @return array ['success' => bool, 'image_url' => string|null, 'error' => string|null]
     */
    public static function generarImagen(array $context): array
    {
        self::initFromConfig();

        if (empty(self::$apiKey)) {
            return [
                'success' => false,
                'image_url' => null,
                'error' => 'API key de OpenAI no configurada'
            ];
        }

        // Construir prompt descriptivo para DALL-E
        $nombreInstitucion = $context['nombre_institucion'] ?? '';
        $anio = $context['anio'] ?? '';
        $titulo = $context['titulo'] ?? '';
        $descripcion = $context['descripcion'] ?? '';
        $size = $context['size'] ?? '1024x1024'; // Cuadrado por defecto

        // Validar tamaño (DALL-E 3 soporta: 1024x1024, 1024x1792, 1792x1024)
        $validSizes = ['1024x1024', '1024x1792', '1792x1024'];
        if (!in_array($size, $validSizes)) {
            $size = '1024x1024';
        }

        // Crear prompt detallado
        $prompt = "Create a professional, high-quality image that represents a historical event";

        if (!empty($anio)) {
            $prompt .= " from the year {$anio}";
        }

        if (!empty($titulo)) {
            $prompt .= ": {$titulo}";
        }

        if (!empty($descripcion)) {
            $prompt .= ". Context: {$descripcion}";
        }

        if (!empty($nombreInstitucion)) {
            $prompt .= ". This event is part of the history of {$nombreInstitucion}";
        }

        $prompt .= ". Style: clean, professional, suitable for an educational institution timeline. Avoid text in the image.";

        // Limitar longitud del prompt (DALL-E 3 max: 4000 caracteres)
        if (strlen($prompt) > 1000) {
            $prompt = substr($prompt, 0, 997) . '...';
        }

        try {
            // Llamada a DALL-E 3 API
            $ch = curl_init('https://api.openai.com/v1/images/generations');

            $requestData = [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => $size,  // Tamaño configurable (1024x1024, 1024x1792, 1792x1024)
                'quality' => 'standard',  // standard o hd
                'style' => 'natural'      // natural o vivid
            ];

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($requestData),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . self::$apiKey
                ],
                CURLOPT_TIMEOUT => 60  // DALL-E puede tardar
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                return [
                    'success' => false,
                    'image_url' => null,
                    'error' => 'Error de conexión con OpenAI: ' . $error
                ];
            }

            curl_close($ch);

            $data = json_decode($response, true);

            if ($httpCode !== 200) {
                $errorMsg = $data['error']['message'] ?? 'Error desconocido';
                return [
                    'success' => false,
                    'image_url' => null,
                    'error' => 'Error de OpenAI: ' . $errorMsg
                ];
            }

            // Extraer URL de la imagen
            if (isset($data['data'][0]['url'])) {
                $openaiImageUrl = $data['data'][0]['url'];

                // Descargar la imagen y guardarla en el servidor
                $localImageUrl = self::downloadAndSaveImage($openaiImageUrl);

                if ($localImageUrl === false) {
                    return [
                        'success' => false,
                        'image_url' => null,
                        'error' => 'Error al descargar y guardar la imagen en el servidor'
                    ];
                }

                return [
                    'success' => true,
                    'image_url' => $localImageUrl,
                    'error' => null
                ];
            }

            return [
                'success' => false,
                'image_url' => null,
                'error' => 'No se pudo obtener la URL de la imagen generada'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'image_url' => null,
                'error' => 'Excepción al generar imagen: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Descarga una imagen desde una URL y la guarda en el servidor
     *
     * @param string $imageUrl URL de la imagen a descargar
     * @return string|false URL local de la imagen guardada o false si falla
     */
    private static function downloadAndSaveImage(string $imageUrl)
    {
        try {
            // Descargar la imagen usando cURL
            $ch = curl_init($imageUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$imageData) {
                error_log('Error descargando imagen de OpenAI: HTTP ' . $httpCode);
                return false;
            }

            // Crear nombre único para la imagen
            $fileName = 'ia-' . date('Ymd-His') . '-' . uniqid() . '.png';

            // Ruta local donde guardar la imagen
            $uploadDir = __DIR__ . '/../../../uploads/ia-images/';

            // Crear carpeta si no existe
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $localPath = $uploadDir . $fileName;

            // Guardar la imagen
            if (file_put_contents($localPath, $imageData) === false) {
                error_log('Error guardando imagen en: ' . $localPath);
                return false;
            }

            // Retornar URL completa para que pase validación del formulario
            return 'https://verumax.com/uploads/ia-images/' . $fileName;

        } catch (Exception $e) {
            error_log('Excepción al descargar imagen: ' . $e->getMessage());
            return false;
        }
    }
}
