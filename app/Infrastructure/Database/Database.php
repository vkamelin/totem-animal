<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

final class Database
{
    /**
     * @param array{
     *     default?: string,
     *     connections?: array<string, array<string, mixed>>
     * } $config
     */
    public static function boot(array $config): Capsule
    {
        $capsule = new Capsule();

        $connectionName = $config['default'] ?? 'mysql';
        $connections = $config['connections'] ?? [];
        $connection = $connections[$connectionName] ?? [];

        $capsule->addConnection($connection);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        return $capsule;
    }
}
