<?php

declare(strict_types=1);

namespace App\Core;

final class SessionManager
{
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function loginUser(AbstractUser $user): void
    {
        $this->start();
        session_regenerate_id(true);

        $_SESSION['auth'] = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'role' => $user->userRole(),
            'permissions' => $user->permissions(),
        ];
    }

    public function logoutUser(): void
    {
        $this->start();
        unset($_SESSION['auth']);
        session_regenerate_id(true);
    }

    public function isAuthenticated(): bool
    {
        $this->start();

        return isset($_SESSION['auth']['id']);
    }

    public function userId(): ?int
    {
        $this->start();

        if (!isset($_SESSION['auth']['id'])) {
            return null;
        }

        return (int) $_SESSION['auth']['id'];
    }

    public function flash(string $type, string $message): void
    {
        $this->start();
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public function pullFlash(): array
    {
        $this->start();
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return $messages;
    }
}
