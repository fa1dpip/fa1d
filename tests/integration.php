<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('DATABASE_PATH', BASE_PATH . '/tests/tmp/integration.sqlite');

require BASE_PATH . '/autoload.php';

use App\Core\Database;
use App\Core\SessionManager;
use App\Repositories\ActivityLogRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\RegistrationService;
use App\Services\UserFactory;

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

@mkdir(BASE_PATH . '/tests/tmp', 0777, true);
Database::reset();
Database::initialize();

session_id('integration-suite');
$_SESSION = [];

$sessionManager = new SessionManager();
$sessionManager->start();

$userRepository = new UserRepository(Database::connection());
$activityLogRepository = new ActivityLogRepository(Database::connection());
$authService = new AuthService($userRepository, new UserFactory(), $sessionManager);
$registrationService = new RegistrationService($userRepository, $sessionManager);

$results = [];

$assert = function (bool $condition, string $message) use (&$results): void {
    $results[] = [
        'message' => $message,
        'status' => $condition ? 'PASS' : 'FAIL',
    ];
};

$assert($userRepository->findByEmail('alice@example.com') !== null, 'Seeded admin account is created in SQLite.');
$assert(
    $registrationService->register('Bob Example', 'bob@example.com', 'user12345', 'user12345'),
    'Regular users can register through the service layer.'
);
$assert(
    !$registrationService->register('Bob Example', 'bob@example.com', 'user12345', 'user12345'),
    'Duplicate email addresses are rejected.'
);
$assert($authService->attempt('bob@example.com', 'user12345'), 'Registered regular user can log in.');

$currentUser = $authService->currentUser();
$assert($currentUser !== null && $currentUser->userRole() === 'Regular User', 'Regular user is restored from the session.');
$assert($currentUser !== null && $currentUser->permissions() === ['view_profile'], 'Regular user permissions stay limited.');

$authService->logoutCurrentUser();
$assert($authService->currentUser() === null, 'Logout clears the active session.');
$assert($authService->attempt('alice@example.com', 'admin123'), 'Seeded admin can log in.');

$adminUser = $authService->currentUser();
$assert(
    $adminUser !== null && in_array('view_users', $adminUser->permissions(), true),
    'Admin receives elevated permissions.'
);
$assert(count($userRepository->all()) >= 2, 'The database persists multiple user records.');
$assert(count($activityLogRepository->latest(20)) >= 3, 'The logger trait writes activity records to the database.');

$failures = array_filter($results, static fn (array $result): bool => $result['status'] === 'FAIL');

echo "Integration Test Results\n";
echo "========================\n";

foreach ($results as $result) {
    echo sprintf("[%s] %s\n", $result['status'], $result['message']);
}

echo "------------------------\n";
echo sprintf("Passed: %d\n", count($results) - count($failures));
echo sprintf("Failed: %d\n", count($failures));

exit($failures === [] ? 0 : 1);

