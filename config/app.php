<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'name' => 'Totem Animal',
    'env' => Env::string('APP_ENV', 'production'),
    'debug' => Env::bool('APP_DEBUG', false),
    'url' => Env::string('APP_URL', 'https://example.com'),
    'timezone' => 'UTC',
    'locale' => 'en',
];
