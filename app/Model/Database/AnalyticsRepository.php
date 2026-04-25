<?php

declare(strict_types=1);

namespace App\Model\Database;

use DateTimeInterface;
use Nette\Database\Explorer;

final class AnalyticsRepository
{
    private const TABLE = 'page_views';

    public function __construct(
        private readonly Explorer $db,
    ) {
    }

    public function record(string $path, ?string $referrer, ?string $userAgent, ?string $ipHash, ?string $sessionId): void
    {
        $this->db->table(self::TABLE)->insert([
            'path'       => $path,
            'referrer'   => $referrer,
            'user_agent' => $userAgent,
            'ip_hash'    => $ipHash,
            'session_id' => $sessionId,
        ]);
    }

    public function getPageViews(DateTimeInterface $from, DateTimeInterface $to): int
    {
        return (int) $this->db->fetchField(
            'SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE created_at BETWEEN ? AND ?',
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s'),
        );
    }

    /**
     * @return list<array{path: string, views: int}>
     */
    public function getTopPages(int $limit = 10, ?DateTimeInterface $from = null, ?DateTimeInterface $to = null): array
    {
        $sql = 'SELECT path, COUNT(*) AS views FROM ' . self::TABLE;
        $params = [];

        if ($from && $to) {
            $sql .= ' WHERE created_at BETWEEN ? AND ?';
            $params[] = $from->format('Y-m-d H:i:s');
            $params[] = $to->format('Y-m-d H:i:s');
        }

        $sql .= ' GROUP BY path ORDER BY views DESC LIMIT ?';
        $params[] = $limit;

        return array_map(
            fn($row) => ['path' => $row->path, 'views' => (int) $row->views],
            iterator_to_array($this->db->query($sql, ...$params)),
        );
    }

    /**
     * Returns daily view counts and unique session counts for the given range.
     *
     * @return list<array{date: string, views: int, unique_sessions: int}>
     */
    public function getVisitorStats(DateTimeInterface $from, DateTimeInterface $to): array
    {
        $rows = $this->db->query(
            'SELECT DATE(created_at) AS date,
                    COUNT(*) AS views,
                    COUNT(DISTINCT session_id) AS unique_sessions
             FROM ' . self::TABLE . '
             WHERE created_at BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date ASC',
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s'),
        );

        return array_map(
            fn($row) => [
                'date'            => $row->date,
                'views'           => (int) $row->views,
                'unique_sessions' => (int) $row->unique_sessions,
            ],
            iterator_to_array($rows),
        );
    }

    public function getReferrerStats(int $limit = 10): array
    {
        $rows = $this->db->query(
            'SELECT referrer, COUNT(*) AS views
             FROM ' . self::TABLE . '
             WHERE referrer IS NOT NULL AND referrer != ""
             GROUP BY referrer
             ORDER BY views DESC
             LIMIT ?',
            $limit,
        );

        return array_map(
            fn($row) => ['referrer' => $row->referrer, 'views' => (int) $row->views],
            iterator_to_array($rows),
        );
    }
}
