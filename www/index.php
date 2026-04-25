<?php

declare(strict_types=1);

ob_start();

// Load .env file if it exists (simple parser for Docker/dev fallback)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (!isset($_ENV[$key]) && !isset($_SERVER[$key])) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

$container = require __DIR__ . '/../app/Bootstrap.php';
$container->getByType(Nette\Application\Application::class)->run();
