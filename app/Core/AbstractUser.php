<?php

declare(strict_types=1);

namespace App\Core;

abstract class AbstractUser
{
    public function __construct(
        protected ?int $id,
        protected string $name,
        protected string $email,
        protected string $passwordHash,
        protected string $createdAt,
        protected ?string $lastLoginAt = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getLastLoginAt(): ?string
    {
        return $this->lastLoginAt;
    }

    public function matchesPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    abstract public function userRole(): string;

    abstract public function permissions(): array;
}

