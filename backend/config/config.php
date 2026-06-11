<?php
declare(strict_types=1);

function env_value(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : $value;
}

define('APP_NAME', env_value('APP_NAME', 'TechShop'));
define('APP_ENV', env_value('APP_ENV', 'production'));
define('BASE_URL', rtrim((string) env_value('BASE_URL', 'http://localhost/webbanhang'), '/'));
define('FRONTEND_URL', rtrim((string) env_value('FRONTEND_URL', 'http://localhost:5173'), '/'));
define('DB_HOST', env_value('DB_HOST', 'localhost'));
define('DB_PORT', (int) env_value('DB_PORT', 3306));
define('DB_NAME', env_value('DB_NAME', 'webbanhang'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS', ''));
define('DB_CHARSET', env_value('DB_CHARSET', 'utf8mb4'));
