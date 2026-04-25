<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Basic analytics: page view events.
 *
 * IPs are stored as SHA-256 hashes for GDPR compliance.
 * Use partitioning or a cron job to prune rows older than ~90 days in production.
 */
final class CreatePageViewsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('page_views', ['comment' => 'Privacy-safe page view log for basic analytics'])
            ->addColumn('path',       'string',   ['limit' => 500,  'null' => false, 'comment' => 'Request path, e.g. /projekty/eshop-modni-dum'])
            ->addColumn('referrer',   'string',   ['limit' => 1000, 'null' => true,  'default' => null])
            ->addColumn('user_agent', 'string',   ['limit' => 500,  'null' => true,  'default' => null])
            ->addColumn('ip_hash',    'string',   ['limit' => 64,   'null' => true,  'default' => null, 'comment' => 'SHA-256 of IP – not reversible'])
            ->addColumn('session_id', 'string',   ['limit' => 64,   'null' => true,  'default' => null, 'comment' => 'Hashed session identifier'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            // Core analytics queries: views per page, views over time, unique sessions per page
            ->addIndex(['path'])
            ->addIndex(['created_at'])
            ->addIndex(['path', 'created_at'],   ['name' => 'idx_pv_path_date'])
            ->addIndex(['session_id'])
            ->create();
    }
}
