<?php

declare(strict_types=1);

namespace App\Model\Service;

use Nette\Database\Table\ActiveRow;

/**
 * Scaffold for Google Calendar API integration.
 *
 * Enable by setting 'google_calendar_enabled'=1 and providing
 * 'google_calendar_id' and 'google_credentials_json' in site_settings.
 *
 * To activate, install google/apiclient and implement the methods below.
 */
final class GoogleCalendarService
{
    private bool $enabled;

    public function __construct(
        bool $enabled,
        private readonly string $calendarId,
        private readonly string $credentialsJson,
    ) {
        $this->enabled = $enabled && $calendarId !== '' && $credentialsJson !== '';
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Creates a Google Calendar event for the given meeting.
     * Returns the created event ID, or null if disabled / on failure.
     */
    public function createEvent(ActiveRow $meeting): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        // TODO: implement with google/apiclient once credentials are configured.
        // $client = $this->buildClient();
        // $service = new \Google\Service\Calendar($client);
        // $event = new \Google\Service\Calendar\Event([...]);
        // $result = $service->events->insert($this->calendarId, $event);
        // return $result->getId();

        return null;
    }

    /**
     * Deletes the Google Calendar event with the given event ID.
     */
    public function deleteEvent(string $eventId): void
    {
        if (!$this->enabled) {
            return;
        }

        // TODO: implement with google/apiclient
    }

    /**
     * Updates an existing Google Calendar event to reflect a status change.
     */
    public function updateEvent(string $eventId, ActiveRow $meeting): void
    {
        if (!$this->enabled) {
            return;
        }

        // TODO: implement with google/apiclient
    }
}
