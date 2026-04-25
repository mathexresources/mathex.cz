<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Pricing plans – FK to services.
 */
final class CreatePricingPlansTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('pricing_plans', ['comment' => 'Service pricing tiers'])
            ->addColumn('service_id',    'integer',  ['null' => true, 'default' => null, 'signed' => false, 'comment' => 'FK → services.id, nullable for standalone plans'])
            ->addColumn('name_cs',       'string',   ['limit' => 255, 'null' => false])
            ->addColumn('name_en',       'string',   ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('price_from',    'decimal',  ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null])
            ->addColumn('price_to',      'decimal',  ['precision' => 10, 'scale' => 2, 'null' => true, 'default' => null])
            ->addColumn('currency',      'string',   ['limit' => 10, 'default' => 'CZK', 'null' => false])
            ->addColumn('billing_type',  'enum',     ['values' => ['fixed', 'hourly', 'monthly'], 'default' => 'fixed', 'null' => false])
            ->addColumn('features_cs',   'json',     ['null' => true, 'default' => null, 'comment' => 'JSON array of included feature strings'])
            ->addColumn('features_en',   'json',     ['null' => true, 'default' => null])
            ->addColumn('highlighted',   'boolean',  ['default' => false, 'null' => false, 'comment' => 'Show "recommended" badge'])
            ->addColumn('active',        'boolean',  ['default' => true,  'null' => false])
            ->addColumn('order_index',   'integer',  ['default' => 0, 'null' => false, 'signed' => false])
            ->addIndex(['service_id'])
            ->addIndex(['active'])
            ->addIndex(['billing_type'])
            ->create();
    }
}
