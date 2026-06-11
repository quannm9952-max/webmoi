<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

function api_cors(): void
{
    header('Access-Control-Allow-Origin: https://webmoi-five.vercel.app');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Content-Type: application/json; charset=utf-8');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function absolute_upload_url(?string $path): ?string
{
    if (!$path) return null;
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
    return BASE_URL . '/' . ltrim($path, '/');
}

api_cors();