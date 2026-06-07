<?php

declare(strict_types=1);

return [
    'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
    'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
    'password' => $_ENV['REDIS_PASSWORD'] ?? null,
    'database' => (int) ($_ENV['REDIS_DATABASE'] ?? 0),
    'timeout' => (float) ($_ENV['REDIS_TIMEOUT'] ?? 1.5),
    'prefix' => $_ENV['REDIS_PREFIX'] ?? 'totem:',
];
