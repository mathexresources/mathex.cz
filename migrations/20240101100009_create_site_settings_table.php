<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Key-value store for editable site-wide settings.
 */
final class CreateSiteSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('site_settings', ['comment' => 'Editable site-wide settings (key-value)'])
            ->addColumn('key',   'string', ['limit' => 100, 'null' => false, 'comment' => 'Unique setting key, e.g. "site_name"'])
            ->addColumn('value', 'text',   ['null' => true, 'default' => null])
            ->addColumn('type',  'enum',   ['values' => ['text', 'textarea', 'image', 'json', 'boolean'], 'default' => 'text', 'null' => false, 'comment' => 'Determines admin UI widget'])
            ->addColumn('label', 'string', ['limit' => 255, 'null' => false, 'comment' => 'Human-readable label for the admin form'])
            ->addColumn('group', 'string', ['limit' => 100, 'null' => false, 'default' => 'general', 'comment' => 'Groups settings into tabs in admin UI'])
            ->addIndex(['key'],   ['unique' => true, 'name' => 'uq_settings_key'])
            ->addIndex(['group'])
            ->create();
    }
}
