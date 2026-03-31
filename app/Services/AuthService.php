<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\AbstractUser;
use App\Core\SessionManager;
use App\Repositories\UserRepository;

final class AuthService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserFactory $userFactory,
        private SessionManager $sessionManager,
    ) {
    }

    public function attempt(string $email, string $password): bool
    {
        $normalizedEmail = strtolower(trim($email));
        $record = $this->userRepository->findByEmail($normalizedEmail);

        if ($record === null) {
            $this->sessionManager->flash('error', 'No account was found for that email address.');
            return false;
        }

        $user = $this->userFactory->fromRecord($record);

        if (!$user->matchesPassword($password)) {
            $this->sessionManager->flash('error', 'The password you entered is incorrect.');
            return false;
        }

        $this->userRepository->touchLastLogin((int) $record['id']);
        $freshRecord = $this->userRepository->findById((int) $record['id']);

        if ($freshRecord === null) {
            $this->sessionManager->flash('error', 'We could not finish the login request.');
            return false;
        }

        $freshUser = $this->userFactory->fromRecord($freshRecord);
        $freshUser->login($this->sessionManager);
        $this->sessionManager->flash('success', sprintf('%s login successful.', $freshUser->userRole()));

        return true;
    }

    public function currentUser(): ?AbstractUser
    {
        if (!$this->sessionManager->isAuthenticated()) {
            return null;
        }

        $userId = $this->sessionManager->userId();

        if ($userId === null) {
            return null;
        }

        $record = $this->userRepository->findById($userId);

        if ($record === null) {
            $this->sessionManager->logoutUser();
            return null;
        }

        return $this->userFactory->fromRecord($record);
    }

    public function logoutCurrentUser(): void
    {
        $user = $this->currentUser();

        if ($user === null) {
            $this->sessionManager->logoutUser();
            return;
        }

        $role = $user->userRole();
        $user->logout($this->sessionManager);
        $this->sessionManager->flash('success', sprintf('%s logged out successfully.', $role));
    }
}

