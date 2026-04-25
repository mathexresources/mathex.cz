<?php

declare(strict_types=1);

namespace App\Model\Service;

use Nette\Database\Table\Selection;

final class CalendarExportService
{
    public function __construct(
        private readonly string $siteTitle,
        private readonly string $siteUrl,
    ) {
    }

    /**
     * Generates an ICS file content from a meetings Selection.
     * Omits cancelled meetings by default.
     */
    public function generateIcs(Selection $meetings, bool $includeCancelled = false): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//' . $this->escapeSafeString($this->siteTitle) . '//Meeting Calendar//CS',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $this->escapeSafeString($this->siteTitle) . ' – Schůzky',
            'X-WR-TIMEZONE:Europe/Prague',
        ];

        foreach ($meetings as $m) {
            if (!$includeCancelled && $m->status === 'cancelled') {
                continue;
            }
            if (!$m->preferred_date) {
                continue;
            }

            $uid = 'meeting-' . $m->id . '@' . parse_url($this->siteUrl, PHP_URL_HOST);
            $created = date('Ymd\THis\Z', strtotime((string) $m->created_at));
            $stamp   = date('Ymd\THis\Z');

            if ($m->preferred_time) {
                $dtStart  = date('Ymd\THis', strtotime((string) $m->preferred_date . ' ' . $m->preferred_time));
                $dtEnd    = date('Ymd\THis', strtotime((string) $m->preferred_date . ' ' . $m->preferred_time) + 3600);
                $tzId     = 'TZID=Europe/Prague:';
                $startLine = 'DTSTART;' . $tzId . $dtStart;
                $endLine   = 'DTEND;' . $tzId . $dtEnd;
            } else {
                $startLine = 'DTSTART;VALUE=DATE:' . date('Ymd', strtotime((string) $m->preferred_date));
                $endLine   = 'DTEND;VALUE=DATE:' . date('Ymd', strtotime((string) $m->preferred_date) + 86400);
            }

            $summary = $this->escapeSafeString(
                'Schůzka: ' . $m->name . ($m->company ? ' (' . $m->company . ')' : ''),
            );

            $descParts = [];
            if ($m->service_type) {
                $descParts[] = 'Typ: ' . $m->service_type;
            }
            if ($m->email) {
                $descParts[] = 'E-mail: ' . $m->email;
            }
            if ($m->phone) {
                $descParts[] = 'Telefon: ' . $m->phone;
            }
            if ($m->message) {
                $descParts[] = 'Zpráva: ' . str_replace(["\r\n", "\n", "\r"], ' ', (string) $m->message);
            }
            $description = $this->escapeSafeString(implode(' | ', $descParts));

            $status = match ($m->status) {
                'confirmed' => 'CONFIRMED',
                'cancelled' => 'CANCELLED',
                default     => 'TENTATIVE',
            };

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . $stamp;
            $lines[] = 'CREATED:' . $created;
            $lines[] = $startLine;
            $lines[] = $endLine;
            $lines[] = 'SUMMARY:' . $summary;
            if ($description) {
                $lines[] = 'DESCRIPTION:' . $description;
            }
            $lines[] = 'STATUS:' . $status;
            $lines[] = 'URL:' . rtrim($this->siteUrl, '/') . '/admin/meetings/detail/' . $m->id;
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines) . "\r\n";
    }

    private function escapeSafeString(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\n"],
            ['\\\\', '\\;', '\\,', '\\n'],
            $value,
        );
    }
}
