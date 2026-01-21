<?php
/**
 * VERUMax Config Class
 *
 * Clase de ejemplo para demostrar autoloading PSR-4.
 * En el futuro, esta clase centralizará toda la configuración.
 *
 * @package VERUMax\Core
 */

namespace VERUMax\Core;

class Config
{
    /**
     * Versión de la aplicación
     */
    public const VERSION = '1.0.0';

    /**
     * Nombre de la aplicación
     */
    public const APP_NAME = 'VERUMax';

    /**
     * Configuración cargada
     */
    private static array $config = [];

    /**
     * Obtener un valor de configuración
     *
     * @param string $key Clave de configuración (dot notation: 'database.host')
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Establecer un valor de configuración
     *
     * @param string $key Clave de configuración
     * @param mixed $value Valor a establecer
     * @return void
     */
    public static function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $config[$segment] = $value;
            } else {
                if (!isset($config[$segment]) || !is_array($config[$segment])) {
                    $config[$segment] = [];
                }
                $config = &$config[$segment];
            }
        }
    }

    /**
     * Cargar configuración desde array
     *
     * @param array $config Array de configuración
     * @return void
     */
    public static function load(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    /**
     * Verificar si una clave existe
     *
     * @param string $key Clave a verificar
     * @return bool
     */
    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Obtener toda la configuración
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$config;
    }

    /**
     * Limpiar toda la configuración (útil para tests)
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$config = [];
    }
}
