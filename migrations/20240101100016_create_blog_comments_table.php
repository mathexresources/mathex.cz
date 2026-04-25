<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBlogCommentsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('blog_comments', ['comment' => 'Moderated blog post comments'])
            ->addColumn('post_id',    'integer',   ['null' => false, 'signed' => false])
            ->addColumn('name',       'string',    ['limit' => 255, 'null' => false])
            ->addColumn('email',      'string',    ['limit' => 255, 'null' => false])
            ->addColumn('content',    'text',      ['null' => false])
            ->addColumn('status',     'enum',      ['values' => ['pending', 'approved', 'rejected'], 'default' => 'pending', 'null' => false])
            ->addColumn('ip_address', 'string',    ['limit' => 45, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['post_id'])
            ->addIndex(['status'])
            ->addIndex(['post_id', 'status'], ['name' => 'idx_comment_post_status'])
            ->addForeignKey('post_id', 'blog_posts', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
