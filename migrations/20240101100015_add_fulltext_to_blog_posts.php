<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFulltextToBlogPosts extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            'ALTER TABLE blog_posts ADD FULLTEXT INDEX ft_blog_search (title_cs, title_en, perex_cs, perex_en, content_cs, content_en)',
        );
    }

    public function down(): void
    {
        $this->execute('ALTER TABLE blog_posts DROP INDEX ft_blog_search');
    }
}
