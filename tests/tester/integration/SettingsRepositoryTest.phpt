<?php

declare(strict_types=1);

use App\Model\Database\SettingsRepository;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$db = null;

setUp(function () use (&$db) {
    $db = createSettingsDb();
});


test('get returns default when key is missing', function () use (&$db) {
    $repo = new SettingsRepository($db);
    Assert::same('fallback', $repo->get('missing_key', 'fallback'));
});

test('get returns null default when not specified', function () use (&$db) {
    $repo = new SettingsRepository($db);
    Assert::null($repo->get('missing_key'));
});

test('set inserts new key and get retrieves it', function () use (&$db) {
    $repo = new SettingsRepository($db);
    $repo->set('site_title', 'Mathex.cz');
    Assert::same('Mathex.cz', $repo->get('site_title'));
});

test('set updates existing key', function () use (&$db) {
    $repo = new SettingsRepository($db);
    $repo->set('ga_tag', 'UA-111');
    $repo->set('ga_tag', 'G-222');
    Assert::same('G-222', $repo->get('ga_tag'));
});

test('delete removes the key', function () use (&$db) {
    $repo = new SettingsRepository($db);
    $repo->set('temp_key', 'value');
    $repo->delete('temp_key');
    Assert::null($repo->get('temp_key'));
});

test('getAll returns all keys as a flat array', function () use (&$db) {
    $repo = new SettingsRepository($db);
    $repo->set('k1', 'v1');
    $repo->set('k2', 'v2');
    $all = $repo->getAll();
    Assert::true(array_key_exists('k1', $all));
    Assert::true(array_key_exists('k2', $all));
});

test('findByKey returns ActiveRow for existing key', function () use (&$db) {
    $repo = new SettingsRepository($db);
    $repo->set('found_key', 'hello');
    $row = $repo->findByKey('found_key');
    Assert::notNull($row);
    Assert::same('hello', $row->value);
});

test('findByKey returns null for missing key', function () use (&$db) {
    $repo = new SettingsRepository($db);
    Assert::null($repo->findByKey('no_such_key'));
});


// ─── Helpers ──────────────────────────────────────────────────────────────────

function createSettingsDb(): \Nette\Database\Explorer
{
    $connection  = new \Nette\Database\Connection('sqlite::memory:');
    $storage     = new \Nette\Caching\Storages\MemoryStorage();
    $structure   = new \Nette\Database\Structure($connection, $storage);
    $conventions = new \Nette\Database\Conventions\DiscoveredConventions($structure);
    $db          = new \Nette\Database\Explorer($connection, $structure, $conventions);

    $connection->query('
        CREATE TABLE site_settings (
            id      INTEGER PRIMARY KEY AUTOINCREMENT,
            `key`   TEXT NOT NULL UNIQUE,
            value   TEXT,
            `group` TEXT
        )
    ');

    return $db;
}
