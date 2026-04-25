<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

final class ProjectRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'projects';
    }

    public function findPublished(): Selection
    {
        return $this->getTable()
            ->where('published', 1)
            ->order('order_index ASC, id DESC');
    }

    public function findFeatured(int $limit = 3): Selection
    {
        return $this->getTable()
            ->where('published', 1)
            ->where('featured', 1)
            ->order('order_index ASC')
            ->limit($limit);
    }

    public function findBySlug(string $slug): ?ActiveRow
    {
        return $this->getTable()->where('slug', $slug)->fetch() ?: null;
    }

    /** @return array<string, string> */
    public function findDistinctCategories(): array
    {
        return $this->getTable()
            ->where('published', 1)
            ->select('category')
            ->group('category')
            ->fetchPairs('category', 'category');
    }

    public function findByCategory(string $category): Selection
    {
        return $this->getTable()
            ->where('category', $category)
            ->where('published', 1)
            ->order('order_index ASC');
    }

    public function findAllOrdered(): Selection
    {
        return $this->getTable()->order('order_index ASC, id DESC');
    }

    /** @param array<int, int> $orderedIds Map of id => new order_index */
    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $id => $orderIndex) {
            $this->update($id, ['order_index' => $orderIndex]);
        }
    }
}
