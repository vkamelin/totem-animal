<?php

declare(strict_types=1);

use App\Middleware\CorsMiddleware;
use App\Middleware\ErrorHandlerMiddleware;
use Slim\App;

return static function (App $app): void {
    $app->add(new ErrorHandlerMiddleware());
    $app->add(new CorsMiddleware());
};
