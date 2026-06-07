<?php

declare(strict_types=1);

use App\Console\ConsoleApplication;
use App\Infrastructure\Logging\LoggerFactory;
use DI\ContainerBuilder;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

$builder = new ContainerBuilder();
$builder->addDefinitions([
    'settings' => [
        'app' => require __DIR__ . '/../config/app.php',
        'database' => require __DIR__ . '/../config/database.php',
        'logging' => require __DIR__ . '/../config/logging.php',
    ],
    LoggerInterface::class => static function (Psr\Container\ContainerInterface $container): LoggerInterface {
        /** @var array{logging: array<string, mixed>} $settings */
        $settings = $container->get('settings');

        return LoggerFactory::create($settings['logging']);
    },
    ValidatorInterface::class => static function (): ValidatorInterface {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    },
    SymfonyConsoleApplication::class => static function (): SymfonyConsoleApplication {
        return new ConsoleApplication()->create();
    },
    Logger::class => static function (Psr\Container\ContainerInterface $container): Logger {
        return $container->get(LoggerInterface::class);
    },
]);

return $builder->build();
