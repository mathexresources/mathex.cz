<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\Selection;

final class BlogCommentRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'blog_comments';
    }

    public function findApprovedForPost(int $postId): Selection
    {
        return $this->getTable()
            ->where('post_id', $postId)
            ->where('status', 'approved')
            ->order('created_at ASC');
    }

    public function findPending(): Selection
    {
        return $this->getTable()
            ->where('status', 'pending')
            ->order('created_at DESC');
    }

    public function findByPost(int $postId): Selection
    {
        return $this->getTable()
            ->where('post_id', $postId)
            ->order('created_at DESC');
    }

    public function countPending(): int
    {
        return $this->getTable()->where('status', 'pending')->count('*');
    }

    public function approve(int $id): void
    {
        $this->getTable()->where('id', $id)->update(['status' => 'approved']);
    }

    public function reject(int $id): void
    {
        $this->getTable()->where('id', $id)->update(['status' => 'rejected']);
    }
}
