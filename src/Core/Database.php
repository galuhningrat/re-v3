<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Class Database — PDO Singleton untuk PostgreSQL
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s',
            DB_DRIVER,   // pgsql
            DB_HOST,     // localhost
            DB_PORT,     // 5432
            DB_NAME      // db_rumah_sakit
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new \RuntimeException('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    private function __clone() {}

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
