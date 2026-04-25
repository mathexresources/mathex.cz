<?php

declare(strict_types=1);

namespace App\Model\Service;

use Nette\Database\Explorer;
use Nette\Utils\Strings;

final class SlugService
{
    public function __construct(
        private readonly Explorer $db,
    ) {
    }

    public function slugify(string $text): string
    {
        $slug = Strings::webalize($text);
        // Replace multiple dashes with single
        return (string) preg_replace('/-+/', '-', $slug);
    }

    /**
     * Generates a slug that is unique within the given table + column.
     * Appends -2, -3, ... until the slug is available.
     */
    public function generateUnique(string $text, string $table, string $column = 'slug', ?int $excludeId = null): string
    {
        $base = $this->slugify($text);
        $slug = $base;
        $i = 2;

        while ($this->exists($slug, $table, $column, $excludeId)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function exists(string $slug, string $table, string $column, ?int $excludeId): bool
    {
        $selection = $this->db->table($table)->where($column, $slug);
        if ($excludeId !== null) {
            $selection->where('id != ?', $excludeId);
        }
        return (bool) $selection->count('*');
    }
}
