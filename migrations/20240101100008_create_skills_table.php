<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Technical skills shown on the about/skills section.
 */
final class CreateSkillsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('skills', ['comment' => 'Technical skill set with proficiency levels'])
            ->addColumn('name',        'string',  ['limit' => 100, 'null' => false])
            ->addColumn('level',       'integer', ['null' => false, 'signed' => false, 'comment' => 'Proficiency 1–100'])
            ->addColumn('category',    'enum',    ['values' => ['frontend', 'backend', 'devops', 'other'], 'default' => 'backend', 'null' => false])
            ->addColumn('icon_class',  'string',  ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'CSS class, SVG id, or devicon class'])
            ->addColumn('order_index', 'integer', ['default' => 0, 'null' => false, 'signed' => false])
            ->addIndex(['category'])
            ->addIndex(['category', 'order_index'], ['name' => 'idx_skills_category_order'])
            ->create();
    }
}
