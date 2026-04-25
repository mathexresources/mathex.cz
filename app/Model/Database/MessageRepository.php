<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\Selection;

final class MessageRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'messages';
    }

    public function findUnread(): Selection
    {
        return $this->getTable()
            ->where('is_read', 0)
            ->order('created_at DESC');
    }

    public function markAsRead(int $id): int
    {
        return $this->update($id, ['is_read' => 1]);
    }

    public function countUnread(): int
    {
        return $this->count(['is_read' => 0]);
    }
}
