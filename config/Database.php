<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = self::env('ASTERIA_DB_HOST', '127.0.0.1');
        $port = (int) self::env('ASTERIA_DB_PORT', '3306');
        $database = self::env('ASTERIA_DB_NAME', 'edumatch');
        $username = self::env('ASTERIA_DB_USER', 'root');
        $password = self::env('ASTERIA_DB_PASSWORD', '');

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $database
        );

        self::$connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }

    private static function env(string $name, string $fallback): string
    {
        $value = getenv($name);

        return $value === false ? $fallback : $value;
    }
}
