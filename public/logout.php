<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Database;
use App\Core\SessionManager;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\UserFactory;

$sessionManager = new SessionManager();
$sessionManager->start();

$authService = new AuthService(
    new UserRepository(Database::connection()),
    new UserFactory(),
    $sessionManager,
);

$authService->logoutCurrentUser();

header('Location: index.php');
exit;
