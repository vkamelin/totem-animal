<?php

declare(strict_types=1);

namespace App\Infrastructure\RateLimit;

use App\Infrastructure\Redis\RedisConnection;
use RedisException;
use RuntimeException;

final class RateLimiter
{
    public function __construct(
        private readonly RedisConnection $connection,
    ) {
    }

    public function hit(string $key, int $limit, int $windowSeconds): RateLimitResult
    {
        if ($limit < 0) {
            throw new RuntimeException('Rate limit must not be negative.');
        }

        if ($windowSeconds <= 0) {
            throw new RuntimeException('Rate limit window must be greater than 0.');
        }

        $redis = $this->connection->getClient();

        $script = <<<'LUA'
local current = redis.call('INCR', KEYS[1])
if current == 1 then
    redis.call('EXPIRE', KEYS[1], ARGV[1])
end
local ttl = redis.call('TTL', KEYS[1])
if ttl < 0 then
    redis.call('EXPIRE', KEYS[1], ARGV[1])
    ttl = redis.call('TTL', KEYS[1])
end
return {current, ttl}
LUA;

        try {
            $result = $redis->eval($script, [$key, (string) $windowSeconds], 1);
        } catch (RedisException $exception) {
            throw new RuntimeException('Unable to evaluate Redis rate limiter script.', 0, $exception);
        }

        if (!is_array($result) || !array_key_exists(0, $result) || !array_key_exists(1, $result)) {
            throw new RuntimeException('Unexpected Redis rate limiter response.');
        }

        $counter = (int) $result[0];
        $ttl = (int) $result[1];

        if ($ttl < 0) {
            $ttl = $windowSeconds;
        }

        $allowed = $counter <= $limit;
        $remaining = max(0, $limit - $counter);
        $retryAfter = $allowed ? 0 : $ttl;

        return new RateLimitResult(
            allowed: $allowed,
            limit: $limit,
            remaining: $remaining,
            retryAfter: $retryAfter,
            resetAfter: $ttl,
        );
    }
}
