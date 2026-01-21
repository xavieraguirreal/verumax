<?php
/**
 * QRCodeService
 *
 * Servicio para generar códigos QR usando API externa.
 * Centraliza la generación de URLs de QR para certificados.
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

class QRCodeService
{
    /**
     * URL base de la API de QR
     */
    private const API_BASE_URL = 'https://api.qrserver.com/v1/create-qr-code/';

    /**
     * Tamaño por defecto del QR (píxeles)
     */
    public const DEFAULT_SIZE = 100;

    /**
     * Tamaño grande para documentos de alta resolución
     */
    public const LARGE_SIZE = 200;

    /**
     * Tamaño pequeño para vistas previas
     */
    public const SMALL_SIZE = 80;

    /**
     * Genera la URL de un código QR
     *
     * @param string $data Datos a codificar en el QR
     * @param int $size Tamaño en píxeles (ancho y alto)
     * @param string $format Formato de imagen (png, gif, jpeg, svg)
     * @return string URL del código QR
     */
    public static function generate(string $data, int $size = self::DEFAULT_SIZE, string $format = 'png'): string
    {
        $params = [
            'size' => $size . 'x' . $size,
            'data' => $data,
            'format' => $format,
        ];

        return self::API_BASE_URL . '?' . http_build_query($params);
    }

    /**
     * Genera un QR para una URL de validación de certificado
     *
     * @param string $validationCode Código de validación (VALID-xxx)
     * @param int $size Tamaño del QR
     * @param string $baseUrl URL base del sitio
     * @return string URL del código QR
     */
    public static function forValidation(
        string $validationCode,
        int $size = self::DEFAULT_SIZE,
        string $baseUrl = 'https://verumax.com'
    ): string {
        $validationUrl = ValidationCodeService::getValidationUrl($validationCode, $baseUrl);

        return self::generate($validationUrl, $size);
    }

    /**
     * Genera un QR directamente desde DNI y curso
     *
     * @param string $dni DNI del estudiante
     * @param string $courseId ID del curso
     * @param int $size Tamaño del QR
     * @param string $baseUrl URL base del sitio
     * @return string URL del código QR
     */
    public static function forCertificate(
        string $dni,
        string $courseId,
        int $size = self::DEFAULT_SIZE,
        string $baseUrl = 'https://verumax.com'
    ): string {
        $code = ValidationCodeService::generate($dni, $courseId);

        return self::forValidation($code, $size, $baseUrl);
    }

    /**
     * Genera el tag HTML <img> completo para un QR
     *
     * @param string $data Datos a codificar
     * @param int $size Tamaño del QR
     * @param string $alt Texto alternativo
     * @param string $class Clases CSS adicionales
     * @return string Tag HTML <img>
     */
    public static function toHtml(
        string $data,
        int $size = self::DEFAULT_SIZE,
        string $alt = 'Código QR',
        string $class = ''
    ): string {
        $url = self::generate($data, $size);
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escapedAlt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
        $classAttr = $class ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';

        return sprintf(
            '<img src="%s" alt="%s" width="%d" height="%d"%s>',
            $escapedUrl,
            $escapedAlt,
            $size,
            $size,
            $classAttr
        );
    }

    /**
     * Genera el tag HTML para un QR de validación de certificado
     *
     * @param string $validationCode Código de validación
     * @param int $size Tamaño del QR
     * @param string $baseUrl URL base
     * @return string Tag HTML <img>
     */
    public static function validationHtml(
        string $validationCode,
        int $size = self::DEFAULT_SIZE,
        string $baseUrl = 'https://verumax.com'
    ): string {
        $qrUrl = self::forValidation($validationCode, $size, $baseUrl);

        return sprintf(
            '<img src="%s" alt="Código QR de validación" width="%d" height="%d">',
            htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8'),
            $size,
            $size
        );
    }
}
