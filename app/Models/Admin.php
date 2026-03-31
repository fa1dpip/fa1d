<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\AbstractUser;
use App\Core\AuthInterface;
use App\Core\LoggerTrait;
use App\Core\SessionManager;

final class Admin extends AbstractUser implements AuthInterface
{
    use LoggerTrait;

    public function userRole(): string
    {
        return 'Admin';
    }

    public function permissions(): array
    {
        return ['view_profile', 'view_users', 'view_logs'];
    }

    public function login(SessionManager $sessionManager): void
    {
        $sessionManager->loginUser($this);
        $this->logActivity(sprintf('Admin %s logged in.', $this->getName()));
    }

    public function logout(SessionManager $sessionManager): void
    {
        $this->logActivity(sprintf('Admin %s logged out.', $this->getName()));
        $sessionManager->logoutUser();
    }
}

