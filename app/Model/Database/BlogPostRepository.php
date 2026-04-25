<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

final class BlogPostRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'blog_posts';
    }

    public function findPublished(): Selection
    {
        return $this->getTable()
            ->where('status', 'published')
            ->where('published_at <= NOW()')
            ->order('published_at DESC');
    }

    public function findFeatured(int $limit = 3): Selection
    {
        return $this->getTable()
            ->where('status', 'published')
            ->where('published_at <= NOW()')
            ->order('views_count DESC, published_at DESC')
            ->limit($limit);
    }

    public function findByTag(string $tag): Selection
    {
        return $this->getTable()
            ->where('status', 'published')
            ->where('published_at <= NOW()')
            ->where('JSON_CONTAINS(tags, ?)', json_encode($tag))
            ->order('published_at DESC');
    }

    public function findBySlug(string $slug): ?ActiveRow
    {
        return $this->getTable()->where('slug', $slug)->fetch() ?: null;
    }

    public function search(string $query): Selection
    {
        $like = "%{$query}%";
        return $this->getTable()
            ->where('status', 'published')
            ->where('published_at <= NOW()')
            ->where(
                'title_cs LIKE ? OR title_en LIKE ? OR perex_cs LIKE ? OR perex_en LIKE ?',
                $like, $like, $like, $like,
            )
            ->order('published_at DESC');
    }

    /**
     * FULLTEXT search across all text columns.
     * Falls back to LIKE search if query is too short for FULLTEXT.
     */
    public function searchFulltext(string $query): Selection
    {
        if (mb_strlen($query) < 3) {
            return $this->search($query);
        }

        return $this->getTable()
            ->where('status', 'published')
            ->where('published_at <= NOW()')
            ->where(
                'MATCH(title_cs, title_en, perex_cs, perex_en, content_cs, content_en) AGAINST (? IN BOOLEAN MODE)',
                $query . '*',
            )
            ->order('published_at DESC');
    }

    /**
     * Returns up to $limit posts that share the most tags with $tags.
     * Falls back to most recent posts if no matches found.
     *
     * @param list<string> $tags
     * @return list<ActiveRow>
     */
    public function findRelatedByTags(int $excludeId, array $tags, int $limit = 3): array
    {
        if (empty($tags)) {
            return iterator_to_array(
                $this->findPublished()->where('id != ?', $excludeId)->limit($limit),
            );
        }

        // Fetch a wider pool of published posts sharing at least one tag
        $pool = $this->findPublished()->where('id != ?', $excludeId)->limit(50);

        $scored = [];
        foreach ($pool as $post) {
            $postTags = $post->tags ? json_decode((string) $post->tags, true) : [];
            $shared   = count(array_intersect($tags, (array) $postTags));
            if ($shared > 0) {
                $scored[] = ['post' => $post, 'score' => $shared];
            }
        }

        if (empty($scored)) {
            return iterator_to_array(
                $this->findPublished()->where('id != ?', $excludeId)->limit($limit),
            );
        }

        usort($scored, static fn($a, $b) => $b['score'] <=> $a['score']);

        return array_map(
            static fn($item) => $item['post'],
            array_slice($scored, 0, $limit),
        );
    }

    /**
     * Returns all unique tags used across published posts, sorted alphabetically.
     *
     * @return list<string>
     */
    public function getAllTags(): array
    {
        $rows = $this->getTable()
            ->select('tags')
            ->where('tags IS NOT NULL')
            ->fetchAll();

        $tags = [];
        foreach ($rows as $row) {
            $decoded = json_decode((string) $row->tags, true);
            if (is_array($decoded)) {
                $tags = array_merge($tags, $decoded);
            }
        }

        $tags = array_values(array_unique(array_filter($tags)));
        sort($tags);

        return $tags;
    }

    public function incrementViews(int $id): void
    {
        $this->getTable()
            ->where('id', $id)
            ->update(['views_count+=' => 1]);
    }

    public function findByAuthor(int $authorId): Selection
    {
        return $this->getTable()
            ->where('author_id', $authorId)
            ->order('created_at DESC');
    }
}
