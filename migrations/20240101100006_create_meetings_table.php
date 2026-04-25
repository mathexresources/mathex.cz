<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Meeting / discovery-call requests from visitors.
 */
final class CreateMeetingsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('meetings', ['comment' => 'Meeting and discovery-call requests'])
            ->addColumn('name',           'string',    ['limit' => 255, 'null' => false])
            ->addColumn('email',          'string',    ['limit' => 255, 'null' => false])
            ->addColumn('phone',          'string',    ['limit' => 30,  'null' => true, 'default' => null])
            ->addColumn('company',        'string',    ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('service_type',   'string',    ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'Matches services.slug'])
            ->addColumn('message',        'text',      ['null' => true, 'default' => null])
            ->addColumn('preferred_date', 'date',      ['null' => true, 'default' => null])
            ->addColumn('preferred_time', 'time',      ['null' => true, 'default' => null])
            ->addColumn('status',         'enum',      ['values' => ['pending', 'confirmed', 'cancelled', 'completed'], 'default' => 'pending', 'null' => false])
            ->addColumn('admin_notes',    'text',      ['null' => true, 'default' => null])
            ->addColumn('ip_address',     'string',    ['limit' => 45, 'null' => true, 'default' => null])
            ->addColumn('created_at',     'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at',     'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['status'])
            ->addIndex(['email'])
            ->addIndex(['preferred_date'])
            ->addIndex(['created_at'])
            ->create();
    }
}
