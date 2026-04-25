<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Contact form submissions.
 * Supersedes the legacy "messages" table.
 */
final class CreateContactMessagesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('contact_messages', ['comment' => 'Contact form submissions'])
            ->addColumn('name',        'string',    ['limit' => 255, 'null' => false])
            ->addColumn('email',       'string',    ['limit' => 255, 'null' => false])
            ->addColumn('subject',     'string',    ['limit' => 500, 'null' => true,  'default' => null])
            ->addColumn('message',     'text',      ['null' => false])
            ->addColumn('ip_address',  'string',    ['limit' => 45,  'null' => true,  'default' => null])
            ->addColumn('is_read',     'boolean',   ['default' => false, 'null' => false])
            ->addColumn('replied_at',  'timestamp', ['null' => true, 'default' => null])
            ->addColumn('created_at',  'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['is_read'])
            ->addIndex(['email'])
            ->addIndex(['created_at'])
            ->addIndex(['is_read', 'created_at'], ['name' => 'idx_messages_read_date'])
            ->create();
    }
}
