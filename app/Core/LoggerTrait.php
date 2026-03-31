<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\ActivityLogRepository;

trait LoggerTrait
{
    protected function logActivity(string $message): void
    {
        $repository = new ActivityLogRepository(Database::connection());
        $repository->record($this->getId(), $this->getEmail(), $this->userRole(), $message);
    }
}

