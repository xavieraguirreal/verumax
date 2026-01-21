<?php
/**
 * ValidationCodeService
 *
 * Servicio para generar y validar códigos de certificados.
 * Centraliza la lógica de generación de códigos VALID-xxx.
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

class ValidationCodeService
{
    /**
     * Prefijo para códigos de validación
     */
    public const CODE_PREFIX = 'VALID-';

    /**
     * Longitud del hash (caracteres después del prefijo)
     */
    public const HASH_LENGTH = 12;

    /**
     * Genera un código de validación único
     *
     * Formato: VALID-XXXXXXXXXXXX (12 caracteres hexadecimales)
     *
     * @param string $dni DNI del estudiante
     * @param string $courseId ID del curso
     * @return string Código de validación
     */
    public static function generate(string $dni, string $courseId): string
    {
        $hash = md5($dni . $courseId);
        $shortHash = strtoupper(substr($hash, 0, self::HASH_LENGTH));

        return self::CODE_PREFIX . $shortHash;
    }

    /**
     * Valida el formato de un código
     *
     * @param string $code Código a validar
     * @return bool True si el formato es válido
     */
    public static function isValidFormat(string $code): bool
    {
        // Formato: VALID- seguido de 12 caracteres hexadecimales
        $pattern = '/^' . preg_quote(self::CODE_PREFIX, '/') . '[A-F0-9]{' . self::HASH_LENGTH . '}$/i';

        return (bool) preg_match($pattern, $code);
    }

    /**
     * Verifica si un código corresponde a un DNI y curso específicos
     *
     * @param string $code Código a verificar
     * @param string $dni DNI del estudiante
     * @param string $courseId ID del curso
     * @return bool True si el código es válido para ese DNI y curso
     */
    public static function verify(string $code, string $dni, string $courseId): bool
    {
        $expectedCode = self::generate($dni, $courseId);

        return strtoupper($code) === strtoupper($expectedCode);
    }

    /**
     * Extrae el hash de un código de validación
     *
     * @param string $code Código completo (VALID-XXXXXXXXXXXX)
     * @return string|null Hash sin el prefijo, o null si el formato es inválido
     */
    public static function extractHash(string $code): ?string
    {
        if (!self::isValidFormat($code)) {
            return null;
        }

        return strtoupper(substr($code, strlen(self::CODE_PREFIX)));
    }

    /**
     * Sanitiza un código de entrada (remueve espacios, normaliza mayúsculas)
     *
     * @param string $code Código ingresado por usuario
     * @return string Código sanitizado
     */
    public static function sanitize(string $code): string
    {
        // Remover espacios y caracteres no válidos
        $code = preg_replace('/[^A-Za-z0-9\-]/', '', $code);

        return strtoupper(trim($code));
    }

    /**
     * Genera la URL de validación completa
     *
     * @param string $code Código de validación
     * @param string $baseUrl URL base del sitio (ej: https://verumax.com)
     * @return string URL completa de validación
     */
    public static function getValidationUrl(string $code, string $baseUrl = 'https://verumax.com'): string
    {
        $baseUrl = rtrim($baseUrl, '/');

        return $baseUrl . '/validar.php?codigo=' . urlencode($code);
    }
}
