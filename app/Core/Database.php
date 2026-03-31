<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private static ?PDO $connection = null;

    public static function initialize(): void
    {
        self::migrate();
        self::seed();
    }

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $databasePath = self::databasePath();
        $directory = dirname($databasePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        self::$connection = new PDO('sqlite:' . $databasePath);
        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        self::$connection->exec('PRAGMA foreign_keys = ON');

        return self::$connection;
    }

    public static function reset(): void
    {
        self::$connection = null;

        $databasePath = self::databasePath();

        if (is_file($databasePath)) {
            unlink($databasePath);
        }
    }

    private static function migrate(): void
    {
        $connection = self::connection();

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT NOT NULL CHECK(role IN ("admin", "regular")),
                created_at TEXT NOT NULL,
                last_login_at TEXT DEFAULT NULL
            )'
        );

        $connection->exec(
            'CREATE TABLE IF NOT EXISTS activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER DEFAULT NULL,
                email TEXT NOT NULL,
                role TEXT NOT NULL,
                message TEXT NOT NULL,
                created_at TEXT NOT NULL,
                FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
            )'
        );
    }

    private static function seed(): void
    {
        $connection = self::connection();
        $statement = $connection->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $statement->execute(['role' => 'admin']);

        if ((int) $statement->fetchColumn() > 0) {
            return;
        }

        $seedAdmin = $connection->prepare(
            'INSERT INTO users (name, email, password, role, created_at)
             VALUES (:name, :email, :password, :role, :created_at)'
        );

        $seedAdmin->execute([
            'name' => 'Alice Admin',
            'email' => 'alice@example.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'created_at' => date(DATE_ATOM),
        ]);
    }

    private static function databasePath(): string
    {
        if (defined('DATABASE_PATH')) {
            return DATABASE_PATH;
        }

        return BASE_PATH . '/database/user_management.sqlite';
    }
}

