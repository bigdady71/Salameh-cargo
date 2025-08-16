<?php
// Load environment variables from .env using phpdotenv
if (file_exists(__DIR__ . '/../.env')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Define application base path and asset helpers
if (!defined('APP_BASE')) {
    $env = getenv('APP_BASE');
    $base = ($env !== false) ? rtrim($env, "/") . "/" : "/"; // e.g. "/" or "/Salameh-cargo/"
    define('APP_BASE', $base);
}

function asset(string $path): string
{
    return APP_BASE . 'assets/' . ltrim($path, '/');
}

function urlp(string $path): string
{
    return APP_BASE . ltrim($path, '/');
}
