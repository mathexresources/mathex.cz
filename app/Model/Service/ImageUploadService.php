<?php

declare(strict_types=1);

namespace App\Model\Service;

use Nette\Http\FileUpload;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;

final class ImageUploadService
{
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_SIZE     = 10 * 1024 * 1024; // 10 MB

    public function __construct(
        private readonly string $uploadDir,
        private readonly string $uploadUrl,
    ) {
    }

    /**
     * Uploads an image file and optionally generates a thumbnail.
     *
     * @return array{path: string, url: string, thumb_path: string|null, thumb_url: string|null}
     * @throws \RuntimeException on validation or write failure
     */
    public function upload(FileUpload $file, string $subdir = '', ?int $thumbWidth = 300, ?int $thumbHeight = null): array
    {
        $this->validateUpload($file);

        $ext      = strtolower(pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION));
        $filename = Random::generate(16) . '.' . $ext;
        $dir      = rtrim($this->uploadDir, '/') . ($subdir ? '/' . ltrim($subdir, '/') : '');

        FileSystem::createDir($dir);

        $filePath = $dir . '/' . $filename;
        $file->move($filePath);

        $fileUrl = rtrim($this->uploadUrl, '/') . ($subdir ? '/' . ltrim($subdir, '/') : '') . '/' . $filename;

        $thumbPath = null;
        $thumbUrl  = null;

        if ($thumbWidth) {
            $thumbFilename = 'thumb_' . $filename;
            $thumbPath     = $dir . '/' . $thumbFilename;
            $thumbUrl      = rtrim($this->uploadUrl, '/') . ($subdir ? '/' . ltrim($subdir, '/') : '') . '/' . $thumbFilename;
            $this->createThumbnail($filePath, $thumbPath, $thumbWidth, $thumbHeight);
        }

        return [
            'path'       => $filePath,
            'url'        => $fileUrl,
            'thumb_path' => $thumbPath,
            'thumb_url'  => $thumbUrl,
        ];
    }

    public function getUploadDir(): string
    {
        return $this->uploadDir;
    }

    public function uploadFile(\Nette\Http\FileUpload $file, string $subdir): string
    {
        if (!$file->isOk()) {
            throw new \RuntimeException('File upload failed.');
        }

        $ext      = strtolower(pathinfo($file->getSanitizedName(), PATHINFO_EXTENSION));
        $filename = Random::generate(16) . '.' . $ext;
        $dir      = rtrim($this->uploadDir, '/') . ($subdir ? '/' . ltrim($subdir, '/') : '');

        FileSystem::createDir($dir);

        $filePath = $dir . '/' . $filename;
        $file->move($filePath);

        return rtrim($this->uploadUrl, '/') . ($subdir ? '/' . ltrim($subdir, '/') : '') . '/' . $filename;
    }

    /**
     * Uploads a blog cover image, center-cropping and resizing to exactly 1200×630 (OG image size).
     * Returns the public URL of the stored image.
     */
    public function uploadBlogCover(FileUpload $file): string
    {
        $this->validateUpload($file);

        $ext      = 'jpg';
        $filename = Random::generate(16) . '.' . $ext;
        $dir      = rtrim($this->uploadDir, '/') . '/blog';
        FileSystem::createDir($dir);

        $tmpPath = $dir . '/tmp_' . $filename;
        $file->move($tmpPath);

        $finalPath = $dir . '/' . $filename;
        $this->cropAndResize($tmpPath, $finalPath, 1200, 630);
        @unlink($tmpPath);

        return rtrim($this->uploadUrl, '/') . '/blog/' . $filename;
    }

    public function delete(string $filePath, ?string $thumbPath = null): void
    {
        if ($filePath && is_file($filePath)) {
            unlink($filePath);
        }
        if ($thumbPath && is_file($thumbPath)) {
            unlink($thumbPath);
        }
    }

    private function validateUpload(FileUpload $file): void
    {
        if (!$file->isOk()) {
            throw new \RuntimeException('File upload failed with error: ' . $file->getError());
        }

        if ($file->getSize() > self::MAX_SIZE) {
            throw new \RuntimeException('File size exceeds the 10 MB limit.');
        }

        if (!in_array($file->getContentType(), self::ALLOWED_MIME, true)) {
            throw new \RuntimeException('Only JPEG, PNG, GIF and WebP images are allowed.');
        }
    }

    private function cropAndResize(string $sourcePath, string $destPath, int $targetW, int $targetH): void
    {
        [$origW, $origH, $type] = getimagesize($sourcePath);

        $src = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF  => imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default        => throw new \RuntimeException('Unsupported image type for cover resize.'),
        };

        $srcRatio = $origW / $origH;
        $dstRatio = $targetW / $targetH;

        if ($srcRatio > $dstRatio) {
            // Source is wider – crop left/right
            $cropH = $origH;
            $cropW = (int) ($origH * $dstRatio);
            $cropX = (int) (($origW - $cropW) / 2);
            $cropY = 0;
        } else {
            // Source is taller – crop top/bottom
            $cropW = $origW;
            $cropH = (int) ($origW / $dstRatio);
            $cropX = 0;
            $cropY = (int) (($origH - $cropH) / 2);
        }

        $dst = imagecreatetruecolor($targetW, $targetH);
        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
        imagejpeg($dst, $destPath, 88);

        imagedestroy($src);
        imagedestroy($dst);
    }

    private function createThumbnail(string $sourcePath, string $destPath, int $maxWidth, ?int $maxHeight): void
    {
        [$origWidth, $origHeight, $type] = getimagesize($sourcePath);

        $ratio  = $origWidth / $origHeight;
        $width  = $maxWidth;
        $height = (int) ($maxWidth / $ratio);

        if ($maxHeight !== null && $height > $maxHeight) {
            $height = $maxHeight;
            $width  = (int) ($maxHeight * $ratio);
        }

        $src = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF  => imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
            default        => throw new \RuntimeException('Unsupported image type for thumbnail generation.'),
        };

        $dst = imagecreatetruecolor($width, $height);

        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);

        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($dst, $destPath, 85),
            IMAGETYPE_PNG  => imagepng($dst, $destPath, 7),
            IMAGETYPE_GIF  => imagegif($dst, $destPath),
            IMAGETYPE_WEBP => imagewebp($dst, $destPath, 85),
            default        => null,
        };

        imagedestroy($src);
        imagedestroy($dst);
    }
}
