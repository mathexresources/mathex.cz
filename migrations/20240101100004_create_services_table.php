<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Service offerings (what the freelancer sells).
 */
final class CreateServicesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('services', ['comment' => 'Freelancer service offerings'])
            ->addColumn('slug',           'string',  ['limit' => 255, 'null' => false])
            ->addColumn('icon',           'string',  ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'CSS class or emoji'])
            ->addColumn('title_cs',       'string',  ['limit' => 255, 'null' => false])
            ->addColumn('title_en',       'string',  ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('description_cs', 'text',    ['null' => true, 'default' => null])
            ->addColumn('description_en', 'text',    ['null' => true, 'default' => null])
            ->addColumn('features_cs',    'json',    ['null' => true, 'default' => null, 'comment' => 'JSON array of feature bullet points'])
            ->addColumn('features_en',    'json',    ['null' => true, 'default' => null])
            ->addColumn('order_index',    'integer', ['default' => 0, 'null' => false, 'signed' => false])
            ->addColumn('active',         'boolean', ['default' => true, 'null' => false])
            ->addIndex(['slug'],       ['unique' => true, 'name' => 'uq_services_slug'])
            ->addIndex(['active'])
            ->addIndex(['order_index'])
            ->create();
    }
}
