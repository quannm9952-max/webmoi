<?php
declare(strict_types=1);

function redirect(string $path = ''): void
{
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    header('Location: ' . ($path ? $base . '/' . $path : $base));
    exit;
}

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function asset_url(string $path): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function format_price(mixed $price): string
{
    return number_format((float)$price, 0, ',', '.') . ' đ';
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function flash(string $key): ?string
{
    $value = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return is_string($value) ? $value : null;
}

function old(string $key, string $default = ''): string
{
    return h($_SESSION['_old'][$key] ?? $default);
}

if (!function_exists('product_img_url')) {
    function product_img_url(?string $path): string
    {
        $path = trim((string)$path);

        if ($path === '') {
            return asset_url('assets/images/no-image.jpg');
        }

        if (preg_match('~^https?://~i', $path)) {
            return $path;
        }

        return asset_url($path);
    }
}
