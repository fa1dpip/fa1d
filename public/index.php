<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Database;
use App\Core\SessionManager;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\RegistrationService;
use App\Services\UserFactory;

$sessionManager = new SessionManager();
$sessionManager->start();

$userRepository = new UserRepository(Database::connection());
$userFactory = new UserFactory();
$authService = new AuthService($userRepository, $userFactory, $sessionManager);
$registrationService = new RegistrationService($userRepository, $sessionManager);

if ($authService->currentUser() !== null) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        if ($authService->attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            header('Location: dashboard.php');
            exit;
        }
    }

    if ($action === 'register') {
        $registered = $registrationService->register(
            (string) ($_POST['name'] ?? ''),
            (string) ($_POST['email'] ?? ''),
            (string) ($_POST['password'] ?? ''),
            (string) ($_POST['confirm_password'] ?? ''),
        );

        if ($registered) {
            $authService->attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));
            header('Location: dashboard.php');
            exit;
        }
    }

    header('Location: index.php');
    exit;
}

$flashMessages = $sessionManager->pullFlash();
$totalUsers = count($userRepository->all());
$adminCount = $userRepository->countByRole('admin');
$regularCount = $userRepository->countByRole('regular');

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
    <title>User Management System</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="landing">
<main class="shell">
    <section class="hero card">
        <p class="eyebrow">Practical PHP OOP Project</p>
        <h1>User Management System</h1>
        <p class="lede">
            Sessions handle login and logout, SQLite stores user data, and the codebase uses namespaces,
            an abstract class, an interface, and a trait just like the assignment requires.
        </p>
        <div class="stats-grid">
            <article>
                <span class="stat-value"><?= h((string) $totalUsers) ?></span>
                <span class="stat-label">Users in database</span>
            </article>
            <article>
                <span class="stat-value"><?= h((string) $adminCount) ?></span>
                <span class="stat-label">Admin accounts</span>
            </article>
            <article>
                <span class="stat-value"><?= h((string) $regularCount) ?></span>
                <span class="stat-label">Regular accounts</span>
            </article>
        </div>
        <div class="credential-box">
            <strong>Seeded admin:</strong>
            <span>`alice@example.com` / `admin123`</span>
        </div>
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
        <article class="card form-card">
            <h2>Login</h2>
            <p>Use the admin account above or sign in with a registered regular-user account.</p>
            <form method="post" class="stack">
                <input type="hidden" name="action" value="login">
                <label>
                    <span>Email</span>
                    <input type="email" name="email" placeholder="alice@example.com" required>
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </label>
                <button type="submit">Login with Session</button>
            </form>
        </article>

        <article class="card form-card">
            <h2>Register</h2>
            <p>Registration creates a regular user account and stores it in the SQLite database.</p>
            <form method="post" class="stack">
                <input type="hidden" name="action" value="register">
                <label>
                    <span>Name</span>
                    <input type="text" name="name" placeholder="Bob Example" required>
                </label>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" placeholder="bob@example.com" required>
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" placeholder="Minimum 8 characters" required>
                </label>
                <label>
                    <span>Confirm Password</span>
                    <input type="password" name="confirm_password" placeholder="Repeat your password" required>
                </label>
                <button type="submit">Create Regular User</button>
            </form>
        </article>
    </section>
</main>
</body>
</html>

