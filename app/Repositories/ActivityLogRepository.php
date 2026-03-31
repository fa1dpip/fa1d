<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ActivityLogRepository
{
    public function __construct(private PDO $connection)
    {
    }

    public function record(?int $userId, string $email, string $role, string $message): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO activity_logs (user_id, email, role, message, created_at)
             VALUES (:user_id, :email, :role, :message, :created_at)'
        );

        $statement->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':email', $email);
        $statement->bindValue(':role', $role);
        $statement->bindValue(':message', $message);
        $statement->bindValue(':created_at', date(DATE_ATOM));
        $statement->execute();
    }

    public function latest(int $limit = 12): array
    {
        $statement = $this->connection->prepare(
            'SELECT email, role, message, created_at
             FROM activity_logs
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function forUser(int $userId, int $limit = 12): array
    {
        $statement = $this->connection->prepare(
            'SELECT email, role, message, created_at
             FROM activity_logs
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}

