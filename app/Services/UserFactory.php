<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\AbstractUser;
use App\Models\Admin;
use App\Models\RegularUser;

final class UserFactory
{
    public function fromRecord(array $record): AbstractUser
    {
        $role = $record['role'] ?? 'regular';

        if ($role === 'admin') {
            return new Admin(
                (int) $record['id'],
                (string) $record['name'],
                (string) $record['email'],
                (string) $record['password'],
                (string) $record['created_at'],
                $record['last_login_at'] ?? null,
            );
        }

        return new RegularUser(
            (int) $record['id'],
            (string) $record['name'],
            (string) $record['email'],
            (string) $record['password'],
            (string) $record['created_at'],
            $record['last_login_at'] ?? null,
        );
    }
}
