<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Load .env for test environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        if (!isset($_ENV[trim($k)])) {
            putenv(trim($k) . '=' . trim($v));
            $_ENV[trim($k)] = trim($v);
        }
    }
}
