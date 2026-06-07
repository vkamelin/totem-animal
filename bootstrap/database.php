<?php

declare(strict_types=1);

use App\Infrastructure\Database\Database;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;

return static function (ContainerInterface $container): Capsule {
    /** @var array{database: array<string, mixed>} $settings */
    $settings = $container->get('settings');

    return Database::boot($settings['database']);
};
