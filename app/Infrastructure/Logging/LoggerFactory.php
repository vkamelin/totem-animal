<?php

declare(strict_types=1);

namespace App\Infrastructure\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

final class LoggerFactory
{
    /**
     * @param array{
     *     name?: string,
     *     path?: string,
     *     level?: string
     * } $config
     */
    public static function create(array $config): Logger
    {
        $name = $config['name'] ?? 'totem-animal';
        $path = $config['path'] ?? 'php://stdout';
        $levelName = strtolower($config['level'] ?? 'debug');

        $level = match ($levelName) {
            'alert' => Level::Alert,
            'critical' => Level::Critical,
            'debug' => Level::Debug,
            'emergency' => Level::Emergency,
            'error' => Level::Error,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            default => Level::Debug,
        };

        $logger = new Logger($name);

        $logger->pushHandler(
            new StreamHandler($path, $level)
        );

        return $logger;
    }
}
