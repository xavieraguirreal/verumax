<?php
/**
 * DatabaseService
 *
 * Servicio centralizado para conexiones a base de datos.
 * Implementa patrón Singleton para reutilizar conexiones.
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

use PDO;
use PDOException;
use Exception;

class DatabaseService
{
    /**
     * Conexiones activas (singleton por base de datos)
     */
    private static array $connections = [];

    /**
     * Configuraciones de bases de datos
     */
    private static array $configs = [];

    /**
     * Zona horaria por defecto
     */
    private const DEFAULT_TIMEZONE = 'America/Argentina/Buenos_Aires';

    /**
     * Registra una configuración de base de datos
     *
     * @param string $name Nombre identificador (ej: 'certificatum', 'identitas')
     * @param array $config Configuración ['host', 'user', 'password', 'database']
     * @return void
     */
    public static function configure(string $name, array $config): void
    {
        self::$configs[$name] = array_merge([
            'host' => 'localhost',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ], $config);
    }

    /**
     * Obtiene una conexión PDO
     *
     * @param string $name Nombre de la base de datos configurada
     * @return PDO Conexión activa
     * @throws Exception Si no hay configuración o falla la conexión
     */
    public static function connection(string $name = 'default'): PDO
    {
        // Retornar conexión existente si está activa
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        // Verificar que existe configuración
        if (!isset(self::$configs[$name])) {
            throw new Exception("Base de datos '$name' no configurada. Use DatabaseService::configure() primero.");
        }

        $config = self::$configs[$name];

        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['database'],
                $config['charset']
            );

            $conn = new PDO($dsn, $config['user'], $config['password'], $config['options']);
            $conn->exec("SET NAMES '{$config['charset']}'");

            // Guardar conexión para reutilizar
            self::$connections[$name] = $conn;

            return $conn;

        } catch (PDOException $e) {
            error_log("Error de conexión BD [$name]: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos. Por favor, intente más tarde.");
        }
    }

    /**
     * Alias corto para obtener conexión
     *
     * @param string $name Nombre de la base de datos
     * @return PDO
     */
    public static function get(string $name = 'default'): PDO
    {
        return self::connection($name);
    }

    /**
     * Ejecuta una consulta SELECT y retorna todos los resultados
     *
     * @param string $name Nombre de la BD
     * @param string $sql Consulta SQL con placeholders
     * @param array $params Parámetros para la consulta
     * @return array Resultados
     */
    public static function fetchAll(string $name, string $sql, array $params = []): array
    {
        $stmt = self::connection($name)->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta una consulta SELECT y retorna un solo registro
     *
     * @param string $name Nombre de la BD
     * @param string $sql Consulta SQL con placeholders
     * @param array $params Parámetros para la consulta
     * @return array|null Registro o null si no existe
     */
    public static function fetchOne(string $name, string $sql, array $params = []): ?array
    {
        $stmt = self::connection($name)->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Ejecuta una consulta SELECT y retorna un solo valor
     *
     * @param string $name Nombre de la BD
     * @param string $sql Consulta SQL con placeholders
     * @param array $params Parámetros para la consulta
     * @return mixed Valor o null
     */
    public static function fetchColumn(string $name, string $sql, array $params = [])
    {
        $stmt = self::connection($name)->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Ejecuta una consulta INSERT/UPDATE/DELETE
     *
     * @param string $name Nombre de la BD
     * @param string $sql Consulta SQL con placeholders
     * @param array $params Parámetros para la consulta
     * @return int Número de filas afectadas
     */
    public static function execute(string $name, string $sql, array $params = []): int
    {
        $stmt = self::connection($name)->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Obtiene el último ID insertado
     *
     * @param string $name Nombre de la BD
     * @return string Último ID
     */
    public static function lastInsertId(string $name = 'default'): string
    {
        return self::connection($name)->lastInsertId();
    }

    /**
     * Inicia una transacción
     *
     * @param string $name Nombre de la BD
     * @return bool
     */
    public static function beginTransaction(string $name = 'default'): bool
    {
        return self::connection($name)->beginTransaction();
    }

    /**
     * Confirma una transacción
     *
     * @param string $name Nombre de la BD
     * @return bool
     */
    public static function commit(string $name = 'default'): bool
    {
        return self::connection($name)->commit();
    }

    /**
     * Revierte una transacción
     *
     * @param string $name Nombre de la BD
     * @return bool
     */
    public static function rollback(string $name = 'default'): bool
    {
        return self::connection($name)->rollBack();
    }

    /**
     * Cierra una conexión específica
     *
     * @param string $name Nombre de la BD
     * @return void
     */
    public static function close(string $name): void
    {
        if (isset(self::$connections[$name])) {
            self::$connections[$name] = null;
            unset(self::$connections[$name]);
        }
    }

    /**
     * Cierra todas las conexiones
     *
     * @return void
     */
    public static function closeAll(): void
    {
        foreach (array_keys(self::$connections) as $name) {
            self::close($name);
        }
    }

    /**
     * Verifica si hay una conexión activa
     *
     * @param string $name Nombre de la BD
     * @return bool
     */
    public static function isConnected(string $name): bool
    {
        return isset(self::$connections[$name]);
    }

    /**
     * Configura la zona horaria de PHP
     *
     * @param string $timezone Zona horaria
     * @return void
     */
    public static function setTimezone(string $timezone = self::DEFAULT_TIMEZONE): void
    {
        date_default_timezone_set($timezone);
    }
}
