<?php

declare(strict_types=1);

namespace App\Core;

interface AuthInterface
{
    public function login(SessionManager $sessionManager): void;

    public function logout(SessionManager $sessionManager): void;
}

