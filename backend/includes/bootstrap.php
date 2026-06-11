<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
    if (class_exists(Dotenv\Dotenv::class) && is_file(__DIR__ . '/../.env')) {
        Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
    }
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/oauth.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', APP_ENV === 'development' ? '1' : '0');

sync_user_session();
