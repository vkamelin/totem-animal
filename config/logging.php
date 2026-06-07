<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'default' => Env::string('LOG_CHANNEL', 'single'),
    'channels' => [
        'single' => [
            'name' => 'totem-animal',
            'path' => Env::string('LOG_PATH', dirname(__DIR__) . '/runtime/logs/app.log'),
            'level' => Env::bool('APP_DEBUG', false) ? 'debug' : 'info',
        ],
    ],
];
