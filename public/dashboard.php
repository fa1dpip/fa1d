<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Database;
use App\Core\SessionManager;
use App\Repositories\ActivityLogRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\UserFactory;

$sessionManager = new SessionManager();
$sessionManager->start();

$userRepository = new UserRepository(Database::connection());
$activityLogRepository = new ActivityLogRepository(Database::connection());
$authService = new AuthService($userRepository, new UserFactory(), $sessionManager);
$currentUser = $authService->currentUser();

if ($currentUser === null) {
    $sessionManager->flash('error', 'Please log in before opening the dashboard.');
    header('Location: index.php');
    exit;
}

$flashMessages = $sessionManager->pullFlash();
$isAdmin = in_array('view_users', $currentUser->permissions(), true);
$allUsers = $isAdmin ? $userRepository->all() : [];
$recentLogs = $isAdmin
    ? $activityLogRepository->latest(12)
    : $activityLogRepository->forUser((int) $currentUser->getId(), 8);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<main class="shell dashboard-shell">
    <section class="card dashboard-hero">
        <div>
            <p class="eyebrow">Session-authenticated dashboard</p>
            <h1>Welcome, <?= h($currentUser->getName()) ?></h1>
            <p class="lede">
                You are signed in as <strong><?= h($currentUser->userRole()) ?></strong>. Your permissions come from the concrete
                class implementation, while the shared properties come from the abstract base class.
            </p>
        </div>
        <a class="ghost-button" href="logout.php">Logout</a>
    </section>

    <?php if ($flashMessages !== []): ?>
        <section class="flash-stack">
            <?php foreach ($flashMessages as $message): ?>
                <div class="flash flash-<?= h((string) $message['type']) ?>">
                    <?= h((string) $message['message']) ?>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <section class="panel-grid">
        <article class="card">
            <h2>Profile</h2>
            <dl class="profile-list">
                <div>
                    <dt>Name</dt>
                    <dd><?= h($currentUser->getName()) ?></dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd><?= h($currentUser->getEmail()) ?></dd>
                </div>
                <div>
                    <dt>Role</dt>
                    <dd><?= h($currentUser->userRole()) ?></dd>
                </div>
                <div>
                    <dt>Created At</dt>
                    <dd><?= h($currentUser->getCreatedAt()) ?></dd>
                </div>
                <div>
                    <dt>Last Login</dt>
                    <dd><?= h($currentUser->getLastLoginAt() ?? 'Current session') ?></dd>
                </div>
            </dl>
        </article>

        <article class="card">
            <h2>Permissions</h2>
            <ul class="pill-list">
                <?php foreach ($currentUser->permissions() as $permission): ?>
                    <li><?= h($permission) ?></li>
                <?php endforeach; ?>
            </ul>
            <p class="muted">
                Admins can review all users and activity logs. Regular users can only see their own account details.
            </p>
        </article>
    </section>

    <?php if ($isAdmin): ?>
        <section class="card">
            <h2>All Registered Users</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Last Login</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allUsers as $user): ?>
                        <tr>
                            <td><?= h((string) $user['id']) ?></td>
                            <td><?= h((string) $user['name']) ?></td>
                            <td><?= h((string) $user['email']) ?></td>
                            <td><?= h((string) $user['role']) ?></td>
                            <td><?= h((string) $user['created_at']) ?></td>
                            <td><?= h((string) ($user['last_login_at'] ?? 'Never')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <section class="card">
        <h2><?= $isAdmin ? 'Recent Activity Logs' : 'Your Activity Logs' ?></h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Message</th>
                    <th>Time</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recentLogs as $log): ?>
                    <tr>
                        <td><?= h((string) $log['email']) ?></td>
                        <td><?= h((string) $log['role']) ?></td>
                        <td><?= h((string) $log['message']) ?></td>
                        <td><?= h((string) $log['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>

