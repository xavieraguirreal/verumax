<?php
/**
 * VERUMAX SUPER ADMIN - Clase Database
 *
 * Gestiona conexiones PDO a las bases de datos de VERUMax.
 */

namespace VERUMaxAdmin;

use PDO;
use PDOException;

class Database {
    private static ?PDO $general = null;
    private static ?PDO $certifi = null;
    private static ?PDO $identi = null;
    private static ?PDO $nexus = null;
    private static ?PDO $academi = null;

    /**
     * Obtiene conexión a verumax_general
     */
    public static function general(): PDO {
        if (self::$general === null) {
            self::$general = self::connect(
                DB_HOST,
                DB_GENERAL_NAME,
                DB_GENERAL_USER,
                DB_GENERAL_PASS
            );
        }
        return self::$general;
    }

    /**
     * Obtiene conexión a verumax_certifi
     */
    public static function certifi(): PDO {
        if (self::$certifi === null) {
            self::$certifi = self::connect(
                DB_HOST,
                'verumax_certifi',
                DB_GENERAL_USER,
                DB_GENERAL_PASS
            );
        }
        return self::$certifi;
    }

    /**
     * Obtiene conexión a verumax_identi
     */
    public static function identi(): PDO {
        if (self::$identi === null) {
            self::$identi = self::connect(
                DB_HOST,
                'verumax_identi',
                DB_GENERAL_USER,
                DB_GENERAL_PASS
            );
        }
        return self::$identi;
    }

    /**
     * Obtiene conexión a verumax_nexus
     */
    public static function nexus(): PDO {
        if (self::$nexus === null) {
            self::$nexus = self::connect(
                DB_HOST,
                'verumax_nexus',
                DB_GENERAL_USER,
                DB_GENERAL_PASS
            );
        }
        return self::$nexus;
    }

    /**
     * Obtiene conexión a verumax_academi
     */
    public static function academi(): PDO {
        if (self::$academi === null) {
            self::$academi = self::connect(
                DB_HOST,
                'verumax_academi',
                DB_GENERAL_USER,
                DB_GENERAL_PASS
            );
        }
        return self::$academi;
    }

    /**
     * Crea conexión PDO
     */
    private static function connect(
        string $host,
        string $database,
        string $username,
        string $password
    ): PDO {
        $dsn = "mysql:host={$host};dbname={$database};charset=" . DB_CHARSET;

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ]);
            return $pdo;
        } catch (PDOException $e) {
            if (VERUMAX_DEBUG) {
                die('Error de conexión: ' . $e->getMessage());
            }
            die('Error de conexión a la base de datos');
        }
    }

    /**
     * Ejecuta query con parámetros y retorna resultados
     */
    public static function query(string $sql, array $params = []): array {
        $stmt = self::general()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta query y retorna una sola fila
     */
    public static function queryOne(string $sql, array $params = []): ?array {
        $stmt = self::general()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Ejecuta INSERT/UPDATE/DELETE y retorna filas afectadas
     */
    public static function execute(string $sql, array $params = []): int {
        $stmt = self::general()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Inserta y retorna el ID generado
     */
    public static function insert(string $sql, array $params = []): int {
        $stmt = self::general()->prepare($sql);
        $stmt->execute($params);
        return (int) self::general()->lastInsertId();
    }

    /**
     * Inicia transacción
     */
    public static function beginTransaction(): void {
        self::general()->beginTransaction();
    }

    /**
     * Confirma transacción
     */
    public static function commit(): void {
        self::general()->commit();
    }

    /**
     * Revierte transacción
     */
    public static function rollback(): void {
        self::general()->rollBack();
    }

    /**
     * Inserta en verumax_certifi y retorna el ID generado
     */
    public static function insertCertifi(string $sql, array $params = []): int {
        $stmt = self::certifi()->prepare($sql);
        $stmt->execute($params);
        return (int) self::certifi()->lastInsertId();
    }

    /**
     * Ejecuta query en verumax_certifi
     */
    public static function queryCertifi(string $sql, array $params = []): array {
        $stmt = self::certifi()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Inserta en verumax_identi y retorna el ID generado
     */
    public static function insertIdenti(string $sql, array $params = []): int {
        $stmt = self::identi()->prepare($sql);
        $stmt->execute($params);
        return (int) self::identi()->lastInsertId();
    }

    /**
     * Ejecuta query en verumax_identi
     */
    public static function queryIdenti(string $sql, array $params = []): array {
        $stmt = self::identi()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta query y retorna una sola fila de verumax_identi
     */
    public static function queryOneIdenti(string $sql, array $params = []): ?array {
        $stmt = self::identi()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Ejecuta INSERT/UPDATE/DELETE en verumax_identi
     */
    public static function executeIdenti(string $sql, array $params = []): int {
        $stmt = self::identi()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Ejecuta query en verumax_nexus
     */
    public static function queryNexus(string $sql, array $params = []): array {
        $stmt = self::nexus()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta INSERT/UPDATE/DELETE en verumax_nexus
     */
    public static function executeNexus(string $sql, array $params = []): int {
        $stmt = self::nexus()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Ejecuta query en verumax_academi
     */
    public static function queryAcademi(string $sql, array $params = []): array {
        $stmt = self::academi()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta INSERT/UPDATE/DELETE en verumax_academi
     */
    public static function executeAcademi(string $sql, array $params = []): int {
        $stmt = self::academi()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Ejecuta INSERT/UPDATE/DELETE en verumax_certifi
     */
    public static function executeCertifi(string $sql, array $params = []): int {
        $stmt = self::certifi()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
