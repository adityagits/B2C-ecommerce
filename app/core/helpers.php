<?php

function config(string $key, $default = null)
{
    static $cfg;
    $cfg ??= require ROOT . '/config/config.php';
    $val = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($val) || !array_key_exists($part, $val)) {
            return $default;
        }
        $val = $val[$part];
    }
    return $val;
}

function base_path(): string
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $base = base_path();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    return '/' . trim($path, '/');
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($amount): string
{
    return config('currency', '$') . number_format((float) $amount, 2);
}

function product_image(?string $image): string
{
    return $image ? url('uploads/' . $image) : asset('img/placeholder.svg');
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flash(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function old(string $key, $default = '')
{
    return $_SESSION['old'][$key] ?? $default;
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return (auth_user()['role'] ?? '') === 'admin';
}
