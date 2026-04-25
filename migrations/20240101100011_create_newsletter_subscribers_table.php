<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Newsletter subscribers with double-opt-in flow.
 */
final class CreateNewsletterSubscribersTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('newsletter_subscribers', ['comment' => 'Newsletter opt-in list (double opt-in)'])
            ->addColumn('email',            'string',    ['limit' => 255, 'null' => false])
            ->addColumn('confirmed',        'boolean',   ['default' => false, 'null' => false, 'comment' => 'True after double-opt-in click'])
            ->addColumn('token',            'string',    ['limit' => 64,  'null' => false, 'comment' => 'Confirmation / unsubscribe token'])
            ->addColumn('confirmed_at',     'timestamp', ['null' => true, 'default' => null])
            ->addColumn('unsubscribed_at',  'timestamp', ['null' => true, 'default' => null])
            ->addColumn('created_at',       'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['email'],   ['unique' => true, 'name' => 'uq_newsletter_email'])
            ->addIndex(['token'],   ['unique' => true, 'name' => 'uq_newsletter_token'])
            ->addIndex(['confirmed'])
            ->create();
    }
}
