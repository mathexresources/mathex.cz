<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\Selection;

final class ContactRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'contact_messages';
    }

    public function findUnread(): Selection
    {
        return $this->getTable()
            ->where('is_read', 0)
            ->order('created_at DESC');
    }

    public function findRead(): Selection
    {
        return $this->getTable()
            ->where('is_read', 1)
            ->order('created_at DESC');
    }

    public function markRead(int $id): int
    {
        return $this->update($id, ['is_read' => 1]);
    }

    public function markReplied(int $id): int
    {
        return $this->update($id, [
            'is_read' => 1,
            'replied_at' => new \DateTime(),
        ]);
    }

    public function countUnread(): int
    {
        return $this->count(['is_read' => 0]);
    }
}
