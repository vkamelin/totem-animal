<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => Env::string('DB_HOST', '127.0.0.1'),
            'port' => Env::int('DB_PORT', 3306),
            'database' => Env::string('DB_DATABASE', 'totem_animal'),
            'username' => Env::string('DB_USERNAME', 'root'),
            'password' => Env::string('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
    ],
];
