<?php

declare(strict_types=1);

namespace App\Model\Service;

use Predis\ClientInterface;

final class RateLimiterService
{
    public function __construct(private readonly ClientInterface $redis)
    {
    }

    /**
     * Returns true if the request is allowed (under the limit).
     */
    public function isAllowed(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $count = (int) $this->redis->incr($key);
        if ($count === 1) {
            $this->redis->expire($key, $windowSeconds);
        }
        return $count <= $maxAttempts;
    }

    public function ttl(string $key): int
    {
        $ttl = $this->redis->ttl($key);
        return max(0, (int) $ttl);
    }
}
