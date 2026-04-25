<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

final class NewsletterRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'newsletter_subscribers';
    }

    public function findByEmail(string $email): ?ActiveRow
    {
        return $this->getTable()->where('email', $email)->fetch() ?: null;
    }

    public function findByToken(string $token): ?ActiveRow
    {
        return $this->getTable()->where('token', $token)->fetch() ?: null;
    }

    public function findConfirmed(): Selection
    {
        return $this->getTable()
            ->where('confirmed', true)
            ->where('unsubscribed_at IS NULL')
            ->order('created_at DESC');
    }

    public function findAll(): Selection
    {
        return $this->getTable()->order('created_at DESC');
    }

    public function getConfirmedCount(): int
    {
        return $this->getTable()
            ->where('confirmed', true)
            ->where('unsubscribed_at IS NULL')
            ->count('*');
    }

    public function confirm(int $id): void
    {
        $this->getTable()->where('id', $id)->update([
            'confirmed'    => true,
            'confirmed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function unsubscribeByToken(string $token): bool
    {
        $row = $this->findByToken($token);
        if (!$row) {
            return false;
        }
        $this->getTable()->where('id', $row->id)->update([
            'unsubscribed_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }
}
