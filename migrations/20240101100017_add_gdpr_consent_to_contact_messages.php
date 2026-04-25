<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGdprConsentToContactMessages extends AbstractMigration
{
    public function change(): void
    {
        $this->table('contact_messages')
            ->addColumn('gdpr_consent', 'boolean', [
                'default' => false,
                'null'    => false,
                'after'   => 'ip_address',
                'comment' => 'Explicit GDPR consent checkbox on the contact form',
            ])
            ->save();
    }
}
