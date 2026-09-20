<?php

declare(strict_types=1);

namespace App\Support;

use PDO;

/** Shared PDO connection factory; configuration is read from the local .env file. */
final class Database
{
    public static function connect(string $root): PDO
    {
        $settings = self::settings($root);
        $host = $settings['DB_HOST'] ?? '127.0.0.1';
        $port = $settings['DB_PORT'] ?? '3306';
        $name = $settings['DB_NAME'] ?? 'klaxon';
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $db = new PDO($dsn, $settings['DB_USER'] ?? 'root', $settings['DB_PASSWORD'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $offset = (new \DateTimeImmutable())->format('P');
        $db->exec("SET time_zone = " . $db->quote($offset));
        return $db;
    }

    /** @return array<string,string> */
    public static function settings(string $root): array
    {
        $settings = [];
        $file = $root . '/.env';
        if (is_file($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $settings[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
            }
        }
        return array_merge($settings, array_filter([
            'DB_HOST' => getenv('DB_HOST') ?: null,
            'DB_PORT' => getenv('DB_PORT') ?: null,
            'DB_NAME' => getenv('DB_NAME') ?: null,
            'DB_USER' => getenv('DB_USER') ?: null,
            'DB_PASSWORD' => getenv('DB_PASSWORD') ?: null,
            'APP_TIMEZONE' => getenv('APP_TIMEZONE') ?: null,
        ], static fn ($value): bool => $value !== null));
    }
}
