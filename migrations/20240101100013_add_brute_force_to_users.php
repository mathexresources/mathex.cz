<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddBruteForceToUsers extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('username',      'string',    ['limit' => 100, 'null' => true, 'default' => null, 'after' => 'email'])
            ->addColumn('failed_logins', 'integer',   ['default' => 0, 'null' => false, 'signed' => false, 'after' => 'last_login_at'])
            ->addColumn('locked_until',  'timestamp', ['null' => true, 'default' => null, 'after' => 'failed_logins'])
            ->addIndex(['username'], ['unique' => true, 'name' => 'uq_users_username'])
            ->update();
    }
}
