<?php

declare(strict_types=1);

// Load .env so phinx can be run directly on host (outside Docker) too
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if (getenv($k) === false) {
            putenv("{$k}={$v}");
        }
    }
}

$sharedConfig = [
    'adapter'   => 'mysql',
    'host'      => getenv('DB_HOST')     ?: 'mysql',
    'name'      => getenv('DB_NAME')     ?: 'mathex',
    'user'      => getenv('DB_USER')     ?: 'mathex',
    'pass'      => getenv('DB_PASSWORD') ?: '',
    'port'      => (int) (getenv('DB_PORT') ?: 3306),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/migrations',
        'seeds'      => '%%PHINX_CONFIG_DIR%%/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinx_migrations',
        'default_environment'     => 'development',
        'development'             => $sharedConfig,
        'production'              => $sharedConfig,
    ],
    'version_order' => 'creation',
];
