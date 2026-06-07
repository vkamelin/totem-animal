<?php

declare(strict_types=1);

return [
    'enabled' => (bool) ((int) ($_ENV['RATE_LIMIT_ENABLED'] ?? 1)),
    'default_limit' => (int) ($_ENV['RATE_LIMIT_DEFAULT_LIMIT'] ?? 60),
    'default_window' => (int) ($_ENV['RATE_LIMIT_DEFAULT_WINDOW'] ?? 60),
    'key_prefix' => $_ENV['RATE_LIMIT_KEY_PREFIX'] ?? 'rate_limit',
    'rules' => [
        'GET:/api/health' => [
            'limit' => 120,
            'window' => 60,
        ],
        'POST:/api/me' => [
            'limit' => 20,
            'window' => 60,
        ],
        'POST:/api/test/start' => [
            'limit' => 10,
            'window' => 60,
        ],
        'POST:/api/test/finish' => [
            'limit' => 6,
            'window' => 60,
        ],
        'GET:/api/result/{public_id}' => [
            'limit' => 30,
            'window' => 60,
        ],
    ],
];
