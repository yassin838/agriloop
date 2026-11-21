<?php

declare(strict_types=1);

final class config
{
    private static ?PDO $pdo = null;

    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            $host = getenv('AGRILOOP_DB_HOST') ?: '127.0.0.1';
            $port = getenv('AGRILOOP_DB_PORT') ?: '3306';
            $database = getenv('AGRILOOP_DB_NAME') ?: 'agriloop';
            $username = getenv('AGRILOOP_DB_USER') ?: 'root';
            $password = getenv('AGRILOOP_DB_PASSWORD') ?: '';

            self::$pdo = new PDO(
                sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $host,
                    $port,
                    $database
                ),
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$pdo;
    }
}
