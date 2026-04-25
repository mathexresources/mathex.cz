<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\ActiveRow;

final class UserRepository extends Repository
{
    private const MAX_FAILED_LOGINS = 5;
    private const LOCKOUT_MINUTES   = 15;

    protected function getTableName(): string
    {
        return 'users';
    }

    public function findByEmail(string $email): ?ActiveRow
    {
        return $this->getTable()->where('email', $email)->fetch() ?: null;
    }

    /** @deprecated Use findByEmail() – users table has no username column */
    public function findByUsername(string $username): ?ActiveRow
    {
        return $this->findByEmail($username);
    }

    public function hasAnyAdmin(): bool
    {
        return $this->count(['role' => 'admin']) > 0;
    }

    public function recordFailedLogin(int $id): void
    {
        $user = $this->find($id);
        if (!$user) {
            return;
        }

        $failed = (int) $user->failed_logins + 1;
        $data   = ['failed_logins' => $failed];

        if ($failed >= self::MAX_FAILED_LOGINS) {
            $lockedUntil = (new \DateTimeImmutable())->modify('+' . self::LOCKOUT_MINUTES . ' minutes');
            $data['locked_until'] = $lockedUntil->format('Y-m-d H:i:s');
        }

        $this->update($id, $data);
    }

    public function resetFailedLogins(int $id): void
    {
        $this->update($id, ['failed_logins' => 0, 'locked_until' => null, 'last_login_at' => date('Y-m-d H:i:s')]);
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $this->update($id, ['password_hash' => $passwordHash]);
    }
}
