<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

final class Database
{
    private static ?PDO $connexion = null;

    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            self::$connexion = config::getConnexion();
        }

        return self::$connexion;
    }
}
