<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function createRegularUser(string $name, string $email, string $password): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (name, email, password, role, created_at)
             VALUES (:name, :email, :password, :role, :created_at)'
        );

        $statement->execute([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'regular',
            'created_at' => date(DATE_ATOM),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function all(): array
    {
        $statement = $this->connection->query(
            'SELECT id, name, email, role, created_at, last_login_at
             FROM users
             ORDER BY created_at ASC'
        );

        return $statement->fetchAll();
    }

    public function touchLastLogin(int $id): void
    {
        $statement = $this->connection->prepare(
            'UPDATE users SET last_login_at = :last_login_at WHERE id = :id'
        );

        $statement->execute([
            'last_login_at' => date(DATE_ATOM),
            'id' => $id,
        ]);
    }

    public function countByRole(string $role): int
    {
        $statement = $this->connection->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $statement->execute(['role' => $role]);

        return (int) $statement->fetchColumn();
    }
}

