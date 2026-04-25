<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\Selection;

final class SkillRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'skills';
    }

    public function findByCategory(string $category): Selection
    {
        return $this->getTable()
            ->where('category', $category)
            ->order('order_index ASC');
    }

    public function findAllGrouped(): array
    {
        $skills = $this->getTable()->order('category ASC, order_index ASC')->fetchAll();
        $grouped = [];
        foreach ($skills as $skill) {
            $grouped[$skill->category][] = $skill;
        }
        return $grouped;
    }
}
