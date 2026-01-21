<?php
/**
 * PDFService
 *
 * Servicio para generar documentos PDF usando mPDF.
 * Soporta certificados, constancias y analíticos.
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PDFService
{
    /**
     * Configuraciones predefinidas para diferentes tipos de documentos
     */
    private const CONFIGS = [
        'certificate' => [
            'format' => [297, 210], // A4 Landscape en mm
            'orientation' => 'L',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'default_font' => 'dejavusans',
        ],
        'constancy' => [
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 20,
            'margin_bottom' => 20,
            'default_font' => 'dejavusans',
        ],
        'analytical' => [
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'dejavusans',
        ],
    ];

    /**
     * Directorio temporal para mPDF
     */
    private static ?string $tempDir = null;

    /**
     * Inicializa el directorio temporal
     */
    private static function initTempDir(): string
    {
        if (self::$tempDir === null) {
            self::$tempDir = sys_get_temp_dir() . '/mpdf';
            if (!is_dir(self::$tempDir)) {
                mkdir(self::$tempDir, 0755, true);
            }
        }
        return self::$tempDir;
    }

    /**
     * Crea una instancia de mPDF configurada
     *
     * @param string $type Tipo de documento (certificate, constancy, analytical)
     * @return Mpdf
     */
    public static function create(string $type = 'certificate'): Mpdf
    {
        $config = self::CONFIGS[$type] ?? self::CONFIGS['certificate'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $config['format'],
            'orientation' => $config['orientation'],
            'margin_left' => $config['margin_left'],
            'margin_right' => $config['margin_right'],
            'margin_top' => $config['margin_top'],
            'margin_bottom' => $config['margin_bottom'],
            'margin_header' => 0,
            'margin_footer' => 0,
            'default_font' => $config['default_font'],
            'tempDir' => self::initTempDir(),
        ]);

        // Configuraciones adicionales
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetProtection(['print', 'copy']); // Permitir imprimir y copiar

        return $mpdf;
    }

    /**
     * Genera un PDF a partir de HTML y lo envía al navegador para descarga
     *
     * @param string $html Contenido HTML (puede ser documento completo o fragmento)
     * @param string $filename Nombre del archivo (sin extensión)
     * @param string $type Tipo de documento
     * @param bool $download true = descarga, false = mostrar en navegador
     */
    public static function generateFromHtml(
        string $html,
        string $filename,
        string $type = 'certificate',
        bool $download = true
    ): void {
        $mpdf = self::create($type);

        // Si el HTML contiene documento completo, escribirlo directamente
        // mPDF puede procesar documentos HTML completos con WriteHTML
        $mpdf->WriteHTML($html);

        // Limpiar el nombre del archivo
        $filename = self::sanitizeFilename($filename);

        // Salida
        $destination = $download ? Destination::DOWNLOAD : Destination::INLINE;
        $mpdf->Output($filename . '.pdf', $destination);
    }

    /**
     * Genera un PDF y lo guarda en el servidor
     *
     * @param string $html Contenido HTML
     * @param string $filepath Ruta completa donde guardar
     * @param string $type Tipo de documento
     * @return bool
     */
    public static function saveToFile(string $html, string $filepath, string $type = 'certificate'): bool
    {
        try {
            $mpdf = self::create($type);
            $mpdf->WriteHTML(self::getBaseStyles(), \Mpdf\HTMLParserMode::HEADER_CSS);
            $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            $mpdf->Output($filepath, Destination::FILE);
            return true;
        } catch (\Exception $e) {
            error_log("Error guardando PDF: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera un PDF y lo retorna como string (para enviar por email, etc.)
     *
     * @param string $html Contenido HTML
     * @param string $type Tipo de documento
     * @return string PDF como string binario
     */
    public static function generateAsString(string $html, string $type = 'certificate'): string
    {
        $mpdf = self::create($type);
        $mpdf->WriteHTML(self::getBaseStyles(), \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    /**
     * Estilos base para los PDFs
     */
    private static function getBaseStyles(): string
    {
        return '
            <style>
                @page {
                    margin: 0;
                }
                body {
                    font-family: DejaVu Sans, sans-serif;
                    margin: 0;
                    padding: 0;
                }
                .certificado-container {
                    width: 100%;
                    height: 100%;
                    position: relative;
                }
                .text-center { text-align: center; }
                .font-bold { font-weight: bold; }
                .uppercase { text-transform: uppercase; }

                /* Fuentes decorativas */
                @font-face {
                    font-family: "Great Vibes";
                    src: url("https://fonts.gstatic.com/s/greatvibes/v18/RWmMoKWR9v4ksMfaWd_JN-XC.ttf");
                }
            </style>
        ';
    }

    /**
     * Limpia el nombre de archivo de caracteres no permitidos
     */
    private static function sanitizeFilename(string $filename): string
    {
        // Reemplazar caracteres especiales
        $filename = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', ' '],
            ['a', 'e', 'i', 'o', 'u', 'n', 'A', 'E', 'I', 'O', 'U', 'N', '_'],
            $filename
        );

        // Eliminar caracteres no alfanuméricos excepto guiones y guiones bajos
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $filename);

        return $filename ?: 'documento';
    }

    /**
     * Genera un certificado con imagen de fondo
     *
     * @param string $backgroundImage URL o path de la imagen de fondo
     * @param string $contentHtml HTML del contenido superpuesto
     * @param string $filename Nombre del archivo
     * @param bool $download Descargar o mostrar
     */
    public static function generateCertificateWithBackground(
        string $backgroundImage,
        string $contentHtml,
        string $filename,
        bool $download = true
    ): void {
        $mpdf = self::create('certificate');

        // Configurar imagen de fondo
        $mpdf->SetDefaultBodyCSS('background', "url('$backgroundImage')");
        $mpdf->SetDefaultBodyCSS('background-image-resize', 6); // Stretch to fit

        $mpdf->WriteHTML(self::getBaseStyles(), \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($contentHtml, \Mpdf\HTMLParserMode::HTML_BODY);

        $filename = self::sanitizeFilename($filename);
        $destination = $download ? Destination::DOWNLOAD : Destination::INLINE;
        $mpdf->Output($filename . '.pdf', $destination);
    }
}
