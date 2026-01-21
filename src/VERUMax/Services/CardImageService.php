<?php
/**
 * CardImageService
 *
 * Servicio para generar tarjetas digitales en formato JPG.
 * Funciona similar a TCPDF en Certificatum: posiciona elementos sobre un template de fondo.
 *
 * Flujo:
 * 1. Cargar template de fondo (JPG)
 * 2. Posicionar elementos (logo, nombre, datos, QR)
 * 3. Generar imagen final JPG
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

class CardImageService
{
    /**
     * Dimensiones por defecto de tarjeta (relación tarjeta de visita)
     */
    public const DEFAULT_WIDTH = 1050;
    public const DEFAULT_HEIGHT = 600;

    /**
     * Calidad JPG por defecto
     */
    public const DEFAULT_QUALITY = 90;

    /**
     * Ruta base de templates
     */
    private const TEMPLATES_PATH = 'assets/templates/tarjetas';

    /**
     * Ruta base de tarjetas generadas
     */
    private const OUTPUT_PATH = 'uploads/tarjetas';

    /**
     * Configuración de posicionamiento por defecto
     * Estas posiciones se ajustarán según el template específico
     */
    private static array $defaultPositions = [
        'logo' => ['x' => 50, 'y' => 50, 'width' => 120, 'height' => 120],
        'nombre' => ['x' => 200, 'y' => 80, 'font_size' => 36, 'color' => '#FFFFFF'],
        'cargo' => ['x' => 200, 'y' => 130, 'font_size' => 18, 'color' => '#CCCCCC'],
        'telefono' => ['x' => 50, 'y' => 450, 'font_size' => 16, 'color' => '#FFFFFF'],
        'email' => ['x' => 50, 'y' => 480, 'font_size' => 16, 'color' => '#FFFFFF'],
        'web' => ['x' => 50, 'y' => 510, 'font_size' => 14, 'color' => '#AAAAAA'],
        'qr' => ['x' => 830, 'y' => 380, 'size' => 180],
    ];

    /**
     * Genera una tarjeta digital JPG
     *
     * @param array $datos Datos para la tarjeta
     *   - nombre: string (requerido)
     *   - cargo: string
     *   - telefono: string
     *   - email: string
     *   - web: string
     *   - logo_url: string - URL o ruta del logo
     *   - qr_url: string - URL destino del QR
     * @param string $templateSlug Slug del template a usar (ej: 'clasico', 'moderno')
     * @param string $institucion Slug de la institución
     * @param array $positions Posiciones personalizadas (opcional)
     * @return array ['success' => bool, 'path' => string, 'url' => string, 'error' => string]
     */
    public static function generate(
        array $datos,
        string $templateSlug = 'default',
        string $institucion = '',
        array $positions = []
    ): array {
        // Verificar extensión GD
        if (!extension_loaded('gd')) {
            return [
                'success' => false,
                'error' => 'Extensión GD no disponible'
            ];
        }

        try {
            // Cargar template de fondo
            $templatePath = self::getTemplatePath($templateSlug, $institucion);
            $background = self::loadTemplate($templatePath);

            if (!$background) {
                return [
                    'success' => false,
                    'error' => 'No se pudo cargar el template: ' . $templateSlug
                ];
            }

            // Obtener dimensiones del template
            $width = imagesx($background);
            $height = imagesy($background);

            // Combinar posiciones por defecto con personalizadas
            $pos = array_merge(self::$defaultPositions, $positions);

            // Cargar configuración del template si existe
            $templateConfig = self::loadTemplateConfig($templateSlug, $institucion);
            if ($templateConfig && isset($templateConfig['positions'])) {
                $pos = array_merge($pos, $templateConfig['positions']);
            }

            // Dibujar elementos sobre el template
            self::drawLogo($background, $datos, $pos);
            self::drawText($background, $datos, $pos);
            self::drawQR($background, $datos, $pos);

            // Generar nombre de archivo único
            $filename = self::generateFilename($institucion, $datos);
            $outputPath = self::getOutputPath($institucion);

            // Crear directorio si no existe
            if (!is_dir($outputPath)) {
                mkdir($outputPath, 0755, true);
            }

            $fullPath = $outputPath . '/' . $filename;

            // Guardar imagen
            $result = imagejpeg($background, $fullPath, self::DEFAULT_QUALITY);
            imagedestroy($background);

            if (!$result) {
                return [
                    'success' => false,
                    'error' => 'Error al guardar la imagen'
                ];
            }

            // Generar URL relativa
            $relativePath = str_replace(ROOT_PATH, '', $fullPath);
            $url = 'https://verumax.com' . str_replace('\\', '/', $relativePath);

            return [
                'success' => true,
                'path' => $fullPath,
                'url' => $url,
                'filename' => $filename
            ];

        } catch (\Exception $e) {
            error_log('[CardImageService] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Genera tarjeta desde configuración de instancia
     *
     * @param array $instanceConfig Configuración de instancia
     * @param array $personaData Datos de la persona (si es tarjeta personal)
     * @param string $templateSlug Template a usar
     * @return array
     */
    public static function generateFromInstance(
        array $instanceConfig,
        array $personaData = [],
        string $templateSlug = 'default'
    ): array {
        $institucion = $instanceConfig['slug'] ?? '';

        // Extraer config
        $config = [];
        if (!empty($instanceConfig['config'])) {
            $config = is_string($instanceConfig['config'])
                ? json_decode($instanceConfig['config'], true) ?: []
                : $instanceConfig['config'];
        }

        // Preparar datos
        $datos = [
            'nombre' => $personaData['nombre'] ?? $instanceConfig['nombre_completo'] ?? $instanceConfig['nombre'] ?? '',
            'cargo' => $personaData['cargo'] ?? '',
            'telefono' => $personaData['telefono'] ?? $instanceConfig['telefono'] ?? $config['telefono'] ?? '',
            'email' => $personaData['email'] ?? $instanceConfig['email_contacto'] ?? $config['email_contacto'] ?? '',
            'web' => $config['sitio_web_oficial'] ?? 'https://' . $institucion . '.verumax.com',
            'logo_url' => $instanceConfig['logo_url'] ?? '',
            'qr_url' => 'https://' . $institucion . '.verumax.com',
            'color_primario' => $instanceConfig['color_primario'] ?? '#2E7D32',
        ];

        return self::generate($datos, $templateSlug, $institucion);
    }

    /**
     * Obtiene lista de templates disponibles para una institución
     *
     * @param string $institucion Slug de la institución
     * @return array Lista de templates disponibles
     */
    public static function getAvailableTemplates(string $institucion = ''): array
    {
        $templates = [];

        // Templates globales
        $globalPath = ROOT_PATH . '/' . self::TEMPLATES_PATH;
        if (is_dir($globalPath)) {
            foreach (glob($globalPath . '/*.{jpg,jpeg,png}', GLOB_BRACE) as $file) {
                $slug = pathinfo($file, PATHINFO_FILENAME);
                $templates[$slug] = [
                    'slug' => $slug,
                    'name' => ucfirst(str_replace('_', ' ', $slug)),
                    'path' => $file,
                    'type' => 'global'
                ];
            }
        }

        // Templates de la institución (sobreescriben globales)
        if (!empty($institucion)) {
            $instPath = ROOT_PATH . '/' . self::TEMPLATES_PATH . '/' . $institucion;
            if (is_dir($instPath)) {
                foreach (glob($instPath . '/*.{jpg,jpeg,png}', GLOB_BRACE) as $file) {
                    $slug = pathinfo($file, PATHINFO_FILENAME);
                    $templates[$slug] = [
                        'slug' => $slug,
                        'name' => ucfirst(str_replace('_', ' ', $slug)),
                        'path' => $file,
                        'type' => 'institution'
                    ];
                }
            }
        }

        return $templates;
    }

    /**
     * Obtiene la ruta del template
     */
    private static function getTemplatePath(string $templateSlug, string $institucion): string
    {
        // Primero buscar template de institución
        if (!empty($institucion)) {
            $instPath = ROOT_PATH . '/' . self::TEMPLATES_PATH . '/' . $institucion . '/' . $templateSlug;
            foreach (['.jpg', '.jpeg', '.png'] as $ext) {
                if (file_exists($instPath . $ext)) {
                    return $instPath . $ext;
                }
            }
        }

        // Luego template global
        $globalPath = ROOT_PATH . '/' . self::TEMPLATES_PATH . '/' . $templateSlug;
        foreach (['.jpg', '.jpeg', '.png'] as $ext) {
            if (file_exists($globalPath . $ext)) {
                return $globalPath . $ext;
            }
        }

        // Template por defecto
        return ROOT_PATH . '/' . self::TEMPLATES_PATH . '/default.jpg';
    }

    /**
     * Carga un template de imagen
     */
    private static function loadTemplate(string $path): ?\GdImage
    {
        if (!file_exists($path)) {
            // Crear imagen por defecto si no existe template
            return self::createDefaultBackground();
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($path);
            case 'png':
                return imagecreatefrompng($path);
            default:
                return null;
        }
    }

    /**
     * Crea un fondo por defecto si no existe template
     */
    private static function createDefaultBackground(): \GdImage
    {
        $image = imagecreatetruecolor(self::DEFAULT_WIDTH, self::DEFAULT_HEIGHT);

        // Gradiente de fondo
        $color1 = imagecolorallocate($image, 46, 125, 50);  // Verde oscuro
        $color2 = imagecolorallocate($image, 27, 94, 32);   // Verde más oscuro

        // Dibujar gradiente simple
        for ($y = 0; $y < self::DEFAULT_HEIGHT; $y++) {
            $ratio = $y / self::DEFAULT_HEIGHT;
            $r = (int)(46 + (27 - 46) * $ratio);
            $g = (int)(125 + (94 - 125) * $ratio);
            $b = (int)(50 + (32 - 50) * $ratio);
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, self::DEFAULT_WIDTH, $y, $color);
        }

        return $image;
    }

    /**
     * Carga configuración JSON del template
     */
    private static function loadTemplateConfig(string $templateSlug, string $institucion): ?array
    {
        $paths = [];

        if (!empty($institucion)) {
            $paths[] = ROOT_PATH . '/' . self::TEMPLATES_PATH . '/' . $institucion . '/' . $templateSlug . '.json';
        }
        $paths[] = ROOT_PATH . '/' . self::TEMPLATES_PATH . '/' . $templateSlug . '.json';

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $content = file_get_contents($path);
                return json_decode($content, true);
            }
        }

        return null;
    }

    /**
     * Dibuja el logo en la imagen
     */
    private static function drawLogo(\GdImage &$image, array $datos, array $pos): void
    {
        if (empty($datos['logo_url'])) {
            return;
        }

        $logoPos = $pos['logo'];

        // Cargar logo desde URL o archivo
        $logoImage = self::loadImageFromUrl($datos['logo_url']);
        if (!$logoImage) {
            return;
        }

        // Redimensionar logo
        $logoWidth = imagesx($logoImage);
        $logoHeight = imagesy($logoImage);

        $targetWidth = $logoPos['width'] ?? 120;
        $targetHeight = $logoPos['height'] ?? 120;

        // Mantener proporción
        $ratio = min($targetWidth / $logoWidth, $targetHeight / $logoHeight);
        $newWidth = (int)($logoWidth * $ratio);
        $newHeight = (int)($logoHeight * $ratio);

        // Crear imagen redimensionada
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);

        imagecopyresampled(
            $resized, $logoImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $logoWidth, $logoHeight
        );

        // Copiar al canvas principal
        imagecopy(
            $image, $resized,
            $logoPos['x'], $logoPos['y'],
            0, 0,
            $newWidth, $newHeight
        );

        imagedestroy($logoImage);
        imagedestroy($resized);
    }

    /**
     * Dibuja los textos en la imagen
     */
    private static function drawText(\GdImage &$image, array $datos, array $pos): void
    {
        // Fuente por defecto (usar fuente del sistema si no hay custom)
        $fontPath = ROOT_PATH . '/assets/fonts/Roboto-Bold.ttf';
        $fontPathRegular = ROOT_PATH . '/assets/fonts/Roboto-Regular.ttf';

        // Si no existe la fuente, usar fuente built-in
        $useBuiltIn = !file_exists($fontPath);

        $fields = [
            'nombre' => ['data_key' => 'nombre', 'font' => $fontPath, 'bold' => true],
            'cargo' => ['data_key' => 'cargo', 'font' => $fontPathRegular, 'bold' => false],
            'telefono' => ['data_key' => 'telefono', 'font' => $fontPathRegular, 'bold' => false, 'prefix' => 'Tel: '],
            'email' => ['data_key' => 'email', 'font' => $fontPathRegular, 'bold' => false],
            'web' => ['data_key' => 'web', 'font' => $fontPathRegular, 'bold' => false],
        ];

        foreach ($fields as $field => $config) {
            if (empty($datos[$config['data_key']])) {
                continue;
            }

            $fieldPos = $pos[$field] ?? null;
            if (!$fieldPos) {
                continue;
            }

            $text = ($config['prefix'] ?? '') . $datos[$config['data_key']];
            $fontSize = $fieldPos['font_size'] ?? 16;
            $color = self::hexToRgb($fieldPos['color'] ?? '#FFFFFF');
            $textColor = imagecolorallocate($image, $color['r'], $color['g'], $color['b']);

            if ($useBuiltIn) {
                // Usar fuente built-in
                $builtInSize = (int)($fontSize / 4);
                imagestring($image, $builtInSize, $fieldPos['x'], $fieldPos['y'], $text, $textColor);
            } else {
                // Usar TTF
                imagettftext(
                    $image,
                    $fontSize,
                    0,
                    $fieldPos['x'],
                    $fieldPos['y'] + $fontSize,
                    $textColor,
                    $config['font'],
                    $text
                );
            }
        }
    }

    /**
     * Dibuja el código QR en la imagen
     */
    private static function drawQR(\GdImage &$image, array $datos, array $pos): void
    {
        $qrUrl = $datos['qr_url'] ?? '';
        if (empty($qrUrl)) {
            return;
        }

        $qrPos = $pos['qr'];
        $qrSize = $qrPos['size'] ?? 180;

        // Generar URL del QR usando QRCodeService
        $qrImageUrl = QRCodeService::generate($qrUrl, $qrSize);

        // Descargar imagen del QR
        $qrImage = self::loadImageFromUrl($qrImageUrl);
        if (!$qrImage) {
            return;
        }

        // Copiar QR al canvas
        imagecopy(
            $image, $qrImage,
            $qrPos['x'], $qrPos['y'],
            0, 0,
            imagesx($qrImage), imagesy($qrImage)
        );

        imagedestroy($qrImage);
    }

    /**
     * Carga imagen desde URL
     */
    private static function loadImageFromUrl(string $url): ?\GdImage
    {
        // Si es archivo local
        if (file_exists($url)) {
            $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    return imagecreatefromjpeg($url);
                case 'png':
                    return imagecreatefrompng($url);
                case 'gif':
                    return imagecreatefromgif($url);
            }
        }

        // Si es URL remota
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'VERUMax/1.0'
            ]
        ]);

        $content = @file_get_contents($url, false, $context);
        if (!$content) {
            return null;
        }

        return imagecreatefromstring($content);
    }

    /**
     * Genera nombre de archivo único
     */
    private static function generateFilename(string $institucion, array $datos): string
    {
        $prefix = $institucion ?: 'card';
        $name = preg_replace('/[^a-zA-Z0-9]/', '_', $datos['nombre'] ?? 'tarjeta');
        $timestamp = date('YmdHis');
        $random = substr(md5(uniqid()), 0, 6);

        return "{$prefix}_{$name}_{$timestamp}_{$random}.jpg";
    }

    /**
     * Obtiene ruta de salida
     */
    private static function getOutputPath(string $institucion): string
    {
        $basePath = ROOT_PATH . '/' . self::OUTPUT_PATH;

        if (!empty($institucion)) {
            return $basePath . '/' . $institucion;
        }

        return $basePath;
    }

    /**
     * Convierte color hexadecimal a RGB
     */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}
