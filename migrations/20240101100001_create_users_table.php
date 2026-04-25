<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Admin user accounts.
 * Supersedes the users table from the legacy 001_initial_schema.sql.
 */
final class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users', ['comment' => 'Admin user accounts'])
            ->addColumn('email',         'string',    ['limit' => 255, 'null' => false, 'comment' => 'Login email'])
            ->addColumn('password_hash', 'string',    ['limit' => 255, 'null' => false])
            ->addColumn('name',          'string',    ['limit' => 255, 'null' => false])
            ->addColumn('role',          'enum',      ['values' => ['admin', 'editor'], 'default' => 'editor', 'null' => false])
            ->addColumn('is_active',     'boolean',   ['default' => true,  'null' => false])
            ->addColumn('avatar_url',    'string',    ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('last_login_at', 'timestamp', ['null' => true, 'default' => null])
            ->addColumn('created_at',    'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at',    'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['email'], ['unique' => true, 'name' => 'uq_users_email'])
            ->addIndex(['role'])
            ->addIndex(['is_active'])
            ->create();
    }
}
