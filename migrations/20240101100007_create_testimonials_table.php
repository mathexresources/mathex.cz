<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Client testimonials / reviews.
 * Optional FK to projects.
 */
final class CreateTestimonialsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('testimonials', ['comment' => 'Client testimonials'])
            ->addColumn('client_name',     'string',  ['limit' => 255, 'null' => false])
            ->addColumn('client_company',  'string',  ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('client_position', 'string',  ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('avatar_url',      'string',  ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('text_cs',         'text',    ['null' => false])
            ->addColumn('text_en',         'text',    ['null' => true, 'default' => null])
            ->addColumn('rating',          'integer', ['null' => false, 'default' => 5, 'signed' => false, 'comment' => '1–5 stars'])
            ->addColumn('project_id',      'integer', ['null' => true, 'default' => null, 'signed' => false, 'comment' => 'FK → projects.id (nullable)'])
            ->addColumn('featured',        'boolean', ['default' => false, 'null' => false])
            ->addColumn('order_index',     'integer', ['default' => 0, 'null' => false, 'signed' => false])
            ->addColumn('active',          'boolean', ['default' => true, 'null' => false])
            ->addIndex(['project_id'])
            ->addIndex(['featured'])
            ->addIndex(['active', 'order_index'], ['name' => 'idx_testimonials_active_order'])
            ->create();
    }
}
