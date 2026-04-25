<?php

declare(strict_types=1);

use App\Model\Service\SlugService;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('slugify converts Czech characters to ASCII', function () {
    $svc = createSlugService();
    Assert::same('cesky-titulek', $svc->slugify('Český titulek'));
});

test('slugify lowercases the result', function () {
    $svc = createSlugService();
    Assert::same('hello-world', $svc->slugify('Hello World'));
});

test('slugify strips special characters', function () {
    $svc = createSlugService();
    Assert::same('hello-world', $svc->slugify('Hello, World!'));
});

test('slugify collapses multiple dashes into one', function () {
    $svc = createSlugService();
    Assert::same('foo-bar-baz', $svc->slugify('foo---bar---baz'));
});

test('slugify handles empty string', function () {
    $svc = createSlugService();
    Assert::same('', $svc->slugify(''));
});

test('slugify handles numbers', function () {
    $svc = createSlugService();
    Assert::same('php-8-2', $svc->slugify('PHP 8.2'));
});

test('slugify handles already-slugified string unchanged', function () {
    $svc = createSlugService();
    Assert::same('hello-world', $svc->slugify('hello-world'));
});

test('slugify strips leading and trailing dashes', function () {
    $svc = createSlugService();
    $result = $svc->slugify('  Vývoj webu  ');
    Assert::same('vyvoj-webu', $result);
});

test('generateUnique returns base slug when table is empty', function () {
    $svc = createSlugServiceWithDb();
    $slug = $svc->generateUnique('Hello World', 'test_slugs');
    Assert::same('hello-world', $slug);
});

test('generateUnique appends suffix when slug already exists', function () {
    $svc = createSlugServiceWithDb();
    $db  = createTestDb();
    $db->query('INSERT INTO test_slugs (slug) VALUES (?)', 'my-post');
    $slug = $svc->generateUnique('My post', 'test_slugs');
    Assert::same('my-post-2', $slug);
});

test('generateUnique increments suffix until unique', function () {
    $svc = createSlugServiceWithDb();
    $db  = createTestDb();
    $db->query('INSERT INTO test_slugs (slug) VALUES (?)', 'my-post');
    $db->query('INSERT INTO test_slugs (slug) VALUES (?)', 'my-post-2');
    $slug = $svc->generateUnique('My post', 'test_slugs');
    Assert::same('my-post-3', $slug);
});

test('generateUnique excludeId ignores own row', function () {
    $svc = createSlugServiceWithDb();
    $db  = createTestDb();
    $db->query('INSERT INTO test_slugs (slug) VALUES (?)', 'my-post');
    // id=1 owns the slug – updating it should keep the same slug
    $slug = $svc->generateUnique('My post', 'test_slugs', 'slug', 1);
    Assert::same('my-post', $slug);
});


// ─── Helpers ──────────────────────────────────────────────────────────────────

function createSlugService(): SlugService
{
    return new SlugService(createTestDb());
}

function createTestDb(): \Nette\Database\Explorer
{
    static $db = null;
    if ($db === null) {
        $connection = new \Nette\Database\Connection('sqlite::memory:');
        $structure  = new \Nette\Database\Structure($connection, new \Nette\Caching\Storages\MemoryStorage());
        $conventions = new \Nette\Database\Conventions\DiscoveredConventions($structure);
        $db = new \Nette\Database\Explorer($connection, $structure, $conventions);
        $connection->query('CREATE TABLE test_slugs (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT NOT NULL)');
    }
    return $db;
}

function createSlugServiceWithDb(): SlugService
{
    return new SlugService(createTestDb());
}
