<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\Selection;

final class TestimonialRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'testimonials';
    }

    public function findActive(): Selection
    {
        return $this->getTable()
            ->where('active', 1)
            ->order('order_index ASC');
    }

    public function findFeatured(int $limit = 6): Selection
    {
        return $this->getTable()
            ->where('active', 1)
            ->where('featured', 1)
            ->order('order_index ASC')
            ->limit($limit);
    }

    public function findByProject(int $projectId): Selection
    {
        return $this->getTable()
            ->where('project_id', $projectId)
            ->where('active', 1);
    }
}
