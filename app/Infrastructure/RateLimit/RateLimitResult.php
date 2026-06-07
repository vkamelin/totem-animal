<?php

declare(strict_types=1);

namespace App\Infrastructure\RateLimit;

final readonly class RateLimitResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly int $limit,
        public readonly int $remaining,
        public readonly int $retryAfter,
        public readonly int $resetAfter,
    ) {
    }
}
