<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use Dotenv\Dotenv;
use Slim\App;

$projectRoot = dirname(__DIR__);
$dotenv = Dotenv::createImmutable($projectRoot);
$dotenv->safeLoad();

$container = require __DIR__ . '/container.php';

/** @var App $app */
$app = Bridge::create($container);

(require __DIR__ . '/database.php')($container);
(require __DIR__ . '/middleware.php')($app);
(require __DIR__ . '/routes.php')($app);

return $app;
