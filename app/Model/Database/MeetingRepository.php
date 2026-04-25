<?php

declare(strict_types=1);

namespace App\Model\Database;

use DateTimeInterface;
use Nette\Database\Table\Selection;

final class MeetingRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'meetings';
    }

    public function findPending(): Selection
    {
        return $this->getTable()
            ->where('status', 'pending')
            ->order('preferred_date ASC, preferred_time ASC');
    }

    public function findByDateRange(DateTimeInterface $from, DateTimeInterface $to): Selection
    {
        return $this->getTable()
            ->where('preferred_date >= ?', $from->format('Y-m-d'))
            ->where('preferred_date <= ?', $to->format('Y-m-d'))
            ->order('preferred_date ASC, preferred_time ASC');
    }

    public function findByStatus(string $status): Selection
    {
        return $this->getTable()
            ->where('status', $status)
            ->order('created_at DESC');
    }

    public function updateStatus(int $id, string $status, ?string $adminNotes = null): int
    {
        $data = ['status' => $status];
        if ($adminNotes !== null) {
            $data['admin_notes'] = $adminNotes;
        }
        return $this->update($id, $data);
    }

    /**
     * Returns booked time slots (H:i) for a given date, excluding cancelled meetings.
     *
     * @return list<string>
     */
    public function getBookedSlotsForDate(string $date): array
    {
        $rows = $this->getTable()
            ->where('preferred_date', $date)
            ->where('status != ?', 'cancelled')
            ->where('preferred_time IS NOT NULL')
            ->fetchAll();

        return array_values(array_map(
            fn($row) => date('H:i', strtotime((string) $row->preferred_time)),
            $rows,
        ));
    }

    /**
     * Returns all meetings in a date range as an array suitable for FullCalendar JSON.
     *
     * @return list<array<string, mixed>>
     */
    public function toFullCalendarEvents(string $start, string $end): array
    {
        $rows = $this->getTable()
            ->where('preferred_date >= ?', $start)
            ->where('preferred_date <= ?', $end)
            ->order('preferred_date ASC, preferred_time ASC')
            ->fetchAll();

        $colors = [
            'pending'   => ['bg' => '#f59e0b', 'border' => '#d97706'],
            'confirmed' => ['bg' => '#22c55e', 'border' => '#16a34a'],
            'cancelled' => ['bg' => '#6b7280', 'border' => '#4b5563'],
            'completed' => ['bg' => '#6366f1', 'border' => '#4f46e5'],
        ];

        $events = [];
        foreach ($rows as $m) {
            $c = $colors[$m->status] ?? $colors['pending'];
            $start = $m->preferred_date;
            if ($m->preferred_time) {
                $start .= 'T' . date('H:i:s', strtotime((string) $m->preferred_time));
            }
            $events[] = [
                'id'            => $m->id,
                'title'         => $m->name . ($m->company ? ' – ' . $m->company : ''),
                'start'         => $start,
                'backgroundColor' => $c['bg'],
                'borderColor'     => $c['border'],
                'textColor'       => '#ffffff',
                'extendedProps' => [
                    'status'    => $m->status,
                    'email'     => $m->email,
                    'phone'     => $m->phone ?? '',
                    'company'   => $m->company ?? '',
                    'service'   => $m->service_type ?? '',
                    'message'   => $m->message ?? '',
                    'date_fmt'  => $m->preferred_date ? date('j. n. Y', strtotime((string) $m->preferred_date)) : '',
                    'time_fmt'  => $m->preferred_time ? date('H:i', strtotime((string) $m->preferred_time)) : '',
                ],
            ];
        }
        return $events;
    }

    /**
     * Returns an array of meetings grouped by day for a given month.
     *
     * @return array<string, list<\Nette\Database\Table\ActiveRow>> keyed by 'Y-m-d'
     */
    public function getCalendarData(int $year, int $month): array
    {
        $from = sprintf('%04d-%02d-01', $year, $month);
        $to   = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

        $rows = $this->getTable()
            ->where('preferred_date >= ?', $from)
            ->where('preferred_date <= ?', $to)
            ->order('preferred_date ASC, preferred_time ASC')
            ->fetchAll();

        $calendar = [];
        foreach ($rows as $row) {
            $key = $row->preferred_date ?? 'unknown';
            $calendar[$key][] = $row;
        }
        return $calendar;
    }
}
