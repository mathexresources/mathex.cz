<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Load .env file if env vars are not already provided (e.g., outside Docker)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile) && getenv('APP_ENV') === false) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        putenv("{$k}={$v}");
        $_ENV[$k] = $v;
        $_SERVER[$k] = $v;
    }
}

$configurator = new Nette\Bootstrap\Configurator();

$isProduction = (getenv('APP_ENV') === 'production');

if (!$isProduction) {
    $configurator->setDebugMode(true);
}

$configurator->enableTracy(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->addStaticParameters(['consoleMode' => PHP_SAPI === 'cli']);
$rawEnv = getenv();
$castEnv = array_map(static function (string $v): string|int|float {
    if (ctype_digit($v)) {
        return (int) $v;
    }
    if (is_numeric($v)) {
        return (float) $v;
    }
    return $v;
}, $rawEnv);
$configurator->addDynamicParameters(['env' => $castEnv]);

$configurator->addConfig(__DIR__ . '/Config/common.neon');
$configurator->addConfig(__DIR__ . '/Config/services.neon');

$localConfig = __DIR__ . '/Config/local.neon';
if (file_exists($localConfig)) {
    $configurator->addConfig($localConfig);
}

return $configurator->createContainer();
