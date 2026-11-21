<?php

declare(strict_types=1);

final class Database
{
    private static ?PDO $connexion = null;

    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            $host = getenv('AGRILOOP_DB_HOST') ?: '127.0.0.1';
            $port = getenv('AGRILOOP_DB_PORT') ?: '3306';
            $database = getenv('AGRILOOP_DB_NAME') ?: 'agriloop';
            $username = getenv('AGRILOOP_DB_USER') ?: 'root';
            $password = getenv('AGRILOOP_DB_PASSWORD') ?: '';
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $host,
                $port,
                $database
            );

            self::$connexion = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$connexion;
    }
}
