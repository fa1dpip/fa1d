<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\SessionManager;
use App\Repositories\UserRepository;
use PDOException;

final class RegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private SessionManager $sessionManager,
    ) {
    }

    public function register(string $name, string $email, string $password, string $confirmPassword): bool
    {
        $cleanName = trim($name);
        $cleanEmail = strtolower(trim($email));

        if (strlen($cleanName) < 2) {
            $this->sessionManager->flash('error', 'Name must contain at least 2 characters.');
            return false;
        }

        if (!filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            $this->sessionManager->flash('error', 'Please provide a valid email address.');
            return false;
        }

        if (strlen($password) < 8) {
            $this->sessionManager->flash('error', 'Password must be at least 8 characters long.');
            return false;
        }

        if ($password !== $confirmPassword) {
            $this->sessionManager->flash('error', 'Password confirmation does not match.');
            return false;
        }

        if ($this->userRepository->findByEmail($cleanEmail) !== null) {
            $this->sessionManager->flash('error', 'That email is already registered.');
            return false;
        }

        try {
            $this->userRepository->createRegularUser($cleanName, $cleanEmail, $password);
        } catch (PDOException) {
            $this->sessionManager->flash('error', 'The account could not be created.');
            return false;
        }

        $this->sessionManager->flash('success', 'Registration completed. Your account is ready to use.');

        return true;
    }
}
