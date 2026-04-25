<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateBlockedDatesTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('blocked_dates', ['comment' => 'Admin-blocked date ranges for meeting bookings'])
            ->addColumn('date_from',  'date',      ['null' => false])
            ->addColumn('date_to',    'date',      ['null' => false])
            ->addColumn('reason',     'string',    ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->addIndex(['date_from'])
            ->addIndex(['date_to'])
            ->create();

        // Seed default meeting time slots into site_settings
        $this->table('site_settings')->insert([
            ['key' => 'meeting_available_times', 'value' => '09:00,10:00,11:00,13:00,14:00,15:00,16:00', 'type' => 'text',    'label' => 'Dostupné časy schůzek (oddělené čárkou)', 'group' => 'meetings'],
            ['key' => 'meeting_duration',        'value' => '60',                                          'type' => 'text',    'label' => 'Délka schůzky (minut)',                   'group' => 'meetings'],
            ['key' => 'google_calendar_enabled', 'value' => '0',                                           'type' => 'boolean', 'label' => 'Aktivovat Google Calendar',               'group' => 'meetings'],
            ['key' => 'google_calendar_id',      'value' => '',                                            'type' => 'text',    'label' => 'Google Calendar ID',                     'group' => 'meetings'],
            ['key' => 'google_credentials_json', 'value' => '',                                            'type' => 'textarea','label' => 'Google Service Account JSON',             'group' => 'meetings'],
            ['key' => 'webhook_url',             'value' => '',                                            'type' => 'text',    'label' => 'Webhook URL (Zapier / Make)',             'group' => 'meetings'],
        ])->save();
    }
}
