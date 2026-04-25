<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

/**
 * Bilingual blog posts (CS + EN).
 */
final class CreateBlogPostsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('blog_posts', ['comment' => 'Bilingual blog articles'])
            ->addColumn('slug',             'string',    ['limit' => 255, 'null' => false])
            // Czech content
            ->addColumn('title_cs',         'string',    ['limit' => 500, 'null' => false])
            ->addColumn('perex_cs',         'text',      ['null' => true,  'default' => null])
            ->addColumn('content_cs',       'text',      ['null' => true,  'default' => null, 'limit' => MysqlAdapter::TEXT_LONG])
            ->addColumn('seo_title_cs',     'string',    ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('seo_desc_cs',      'string',    ['limit' => 500, 'null' => true, 'default' => null])
            // English content
            ->addColumn('title_en',         'string',    ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('perex_en',         'text',      ['null' => true,  'default' => null])
            ->addColumn('content_en',       'text',      ['null' => true,  'default' => null, 'limit' => MysqlAdapter::TEXT_LONG])
            ->addColumn('seo_title_en',     'string',    ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('seo_desc_en',      'string',    ['limit' => 500, 'null' => true, 'default' => null])
            // Media & meta
            ->addColumn('cover_image',      'string',    ['limit' => 500, 'null' => true, 'default' => null])
            ->addColumn('tags',             'json',      ['null' => true,  'default' => null, 'comment' => 'JSON array of tag slugs'])
            ->addColumn('author_id',        'integer',   ['null' => true,  'default' => null, 'signed' => false])
            ->addColumn('status',           'enum',      ['values' => ['draft', 'published', 'archived'], 'default' => 'draft', 'null' => false])
            ->addColumn('reading_time_min', 'integer',   ['null' => true,  'default' => null, 'signed' => false, 'comment' => 'Estimated reading time in minutes'])
            ->addColumn('views_count',      'integer',   ['default' => 0, 'null' => false, 'signed' => false])
            ->addColumn('published_at',     'timestamp', ['null' => true,  'default' => null])
            ->addColumn('created_at',       'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addColumn('updated_at',       'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'null' => false])
            // Indexes
            ->addIndex(['slug'],         ['unique' => true, 'name' => 'uq_blog_slug'])
            ->addIndex(['status'])
            ->addIndex(['published_at'])
            ->addIndex(['status', 'published_at'], ['name' => 'idx_status_published'])
            ->addIndex(['author_id'])
            ->create();
    }
}
