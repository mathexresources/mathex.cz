<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

/**
 * Portfolio projects.
 * Supersedes the projects table from legacy 001_initial_schema.sql.
 */
final class CreateProjectsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('projects', ['comment' => 'Portfolio projects'])
            ->addColumn('slug',                'string',  ['limit' => 255, 'null' => false])
            ->addColumn('title',               'string',  ['limit' => 500, 'null' => false])
            ->addColumn('description_cs',      'text',    ['null' => true, 'default' => null])
            ->addColumn('description_en',      'text',    ['null' => true, 'default' => null])
            ->addColumn('technologies',        'json',    ['null' => true, 'default' => null,  'comment' => 'JSON string array, e.g. ["PHP","Nette","Vue.js"]'])
            ->addColumn('cover_image',         'string',  ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('gallery_images',      'json',    ['null' => true, 'default' => null,  'comment' => 'JSON array of image paths'])
            ->addColumn('project_url',         'string',  ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('github_url',          'string',  ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('case_study_content',  'text',    ['null' => true, 'default' => null,  'limit' => MysqlAdapter::TEXT_LONG, 'comment' => 'Full case-study HTML content'])
            ->addColumn('client_name',         'string',  ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('year',                'integer', ['null' => true, 'default' => null,  'signed' => false, 'limit' => MysqlAdapter::INT_SMALL])
            ->addColumn('category',            'string',  ['limit' => 100, 'null' => true, 'default' => null, 'comment' => 'e.g. eshop, webapp, api, mobile'])
            ->addColumn('featured',            'boolean', ['default' => false, 'null' => false])
            ->addColumn('order_index',         'integer', ['default' => 0, 'null' => false, 'signed' => false])
            ->addColumn('published',           'boolean', ['default' => true,  'null' => false])
            ->addColumn('created_at',          'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at',          'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['slug'],     ['unique' => true, 'name' => 'uq_projects_slug'])
            ->addIndex(['featured'])
            ->addIndex(['category'])
            ->addIndex(['published'])
            ->addIndex(['order_index'])
            ->addIndex(['year'])
            ->create();
    }
}
