<?php

declare(strict_types=1);

use App\Model\Service\ImageUploadService;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('constructor stores uploadDir and uploadUrl', function () {
    $svc = new ImageUploadService('/tmp/uploads', '/uploads');
    Assert::same('/tmp/uploads', $svc->getUploadDir());
});

test('upload rejects oversized file', function () {
    $svc  = new ImageUploadService('/tmp/uploads', '/uploads');
    $file = createFakeUpload(size: 11 * 1024 * 1024, mime: 'image/jpeg');

    Assert::exception(
        fn() => $svc->upload($file),
        \RuntimeException::class,
        '%a%10 MB%a%',
    );
});

test('upload rejects disallowed mime type', function () {
    $svc  = new ImageUploadService('/tmp/uploads', '/uploads');
    $file = createFakeUpload(size: 1024, mime: 'image/bmp');

    Assert::exception(
        fn() => $svc->upload($file),
        \RuntimeException::class,
        '%a%JPEG, PNG, GIF%a%',
    );
});

test('upload rejects pdf disguised as image', function () {
    $svc  = new ImageUploadService('/tmp/uploads', '/uploads');
    $file = createFakeUpload(size: 1024, mime: 'application/pdf');

    Assert::exception(
        fn() => $svc->upload($file),
        \RuntimeException::class,
    );
});

test('upload rejects failed upload', function () {
    $svc  = new ImageUploadService('/tmp/uploads', '/uploads');
    $file = createFakeUpload(size: 1024, mime: 'image/jpeg', ok: false);

    Assert::exception(
        fn() => $svc->upload($file),
        \RuntimeException::class,
        '%a%failed%a%',
    );
});

test('upload accepts jpeg within size limit and writes file', function () {
    $tmpDir = sys_get_temp_dir() . '/mathex_test_' . uniqid();
    mkdir($tmpDir);

    $svc      = new ImageUploadService($tmpDir, '/uploads');
    $imgPath  = createMinimalJpeg($tmpDir);
    $file     = createRealUpload($imgPath, 'image/jpeg');

    $result = $svc->upload($file, '', null); // no thumbnail
    Assert::true(isset($result['url']));
    Assert::match('~/uploads/%a%.jpg~', $result['url']);

    // Cleanup
    array_map('unlink', glob("{$tmpDir}/*"));
    rmdir($tmpDir);
});


// ─── Helpers ──────────────────────────────────────────────────────────────────

function createFakeUpload(int $size, string $mime, bool $ok = true): \Nette\Http\FileUpload
{
    return new \Nette\Http\FileUpload([
        'name'     => 'test.jpg',
        'tmp_name' => '',
        'size'     => $size,
        'error'    => $ok ? UPLOAD_ERR_OK : UPLOAD_ERR_PARTIAL,
        'type'     => $mime,
        'full_path' => 'test.jpg',
    ]);
}

function createMinimalJpeg(string $dir): string
{
    $path = $dir . '/minimal.jpg';
    $img  = imagecreatetruecolor(10, 10);
    imagejpeg($img, $path);
    imagedestroy($img);
    return $path;
}

function createRealUpload(string $path, string $mime): \Nette\Http\FileUpload
{
    // Copy to a temp path that simulates $_FILES['tmp_name']
    $tmp = $path . '.tmp';
    copy($path, $tmp);

    return new \Nette\Http\FileUpload([
        'name'      => basename($path),
        'tmp_name'  => $tmp,
        'size'      => filesize($tmp),
        'error'     => UPLOAD_ERR_OK,
        'type'      => $mime,
        'full_path' => basename($path),
    ]);
}
