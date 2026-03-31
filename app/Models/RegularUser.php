<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\AbstractUser;
use App\Core\AuthInterface;
use App\Core\LoggerTrait;
use App\Core\SessionManager;

final class RegularUser extends AbstractUser implements AuthInterface
{
    use LoggerTrait;

    public function userRole(): string
    {
        return 'Regular User';
    }

    public function permissions(): array
    {
        return ['view_profile'];
    }

    public function login(SessionManager $sessionManager): void
    {
        $sessionManager->loginUser($this);
        $this->logActivity(sprintf('Regular user %s logged in.', $this->getName()));
    }

    public function logout(SessionManager $sessionManager): void
    {
        $this->logActivity(sprintf('Regular user %s logged out.', $this->getName()));
        $sessionManager->logoutUser();
    }
}

