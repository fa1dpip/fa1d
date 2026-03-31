<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

require BASE_PATH . '/autoload.php';

use App\Core\Database;
use App\Core\SessionManager;

Database::initialize();

$sessionManager = new SessionManager();
$sessionManager->start();

