<?php

class Config {
    private static $pdo = null;

    public static function getConnexion(): PDO {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                "mysql:host=localhost;dbname=edumatch;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        }
        return self::$pdo;
    }
}

/**
 * Wrapper de compatibilite pour le module devoirs
 * Tous les modules doivent utiliser Config::getConnexion()
 */
if (!function_exists('getDBConnection')) {
    function getDBConnection(): PDO {
        return Config::getConnexion();
    }
}
