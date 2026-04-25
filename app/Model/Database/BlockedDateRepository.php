<?php

declare(strict_types=1);

namespace App\Model\Database;

use Nette\Database\Table\Selection;

final class BlockedDateRepository extends Repository
{
    protected function getTableName(): string
    {
        return 'blocked_dates';
    }

    public function findActive(): Selection
    {
        return $this->getTable()->order('date_from ASC');
    }

    public function isBlocked(string $date): bool
    {
        return (bool) $this->getTable()
            ->where('date_from <= ?', $date)
            ->where('date_to >= ?', $date)
            ->fetch();
    }

    /** Returns all ranges that overlap [from, to]. */
    public function findOverlapping(string $dateFrom, string $dateTo): Selection
    {
        return $this->getTable()
            ->where('date_from <= ?', $dateTo)
            ->where('date_to >= ?', $dateFrom);
    }

    /** Returns blocked dates as [['start'=>'Y-m-d','end'=>'Y-m-d','reason'=>'...'], ...] */
    public function toCalendarArray(): array
    {
        $result = [];
        foreach ($this->findActive() as $row) {
            $result[] = [
                'id'     => $row->id,
                'start'  => $row->date_from,
                'end'    => $row->date_to,
                'reason' => $row->reason ?? '',
            ];
        }
        return $result;
    }
}
