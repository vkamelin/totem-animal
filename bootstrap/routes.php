<?php

declare(strict_types=1);

use App\Actions\Admin\DashboardAction;
use App\Actions\Api\FinishTestAction;
use App\Actions\Api\GetResultAction;
use App\Actions\Api\HealthAction;
use App\Actions\Api\StartTestAction;
use App\Actions\Api\SubmitAnswerAction;
use Slim\App;

return static function (App $app): void {
    $app->get('/api/health', HealthAction::class);
    $app->post('/api/test/start', StartTestAction::class);
    $app->post('/api/test/answer', SubmitAnswerAction::class);
    $app->post('/api/test/finish', FinishTestAction::class);
    $app->get('/api/result/{id}', GetResultAction::class);
    $app->get('/dashboard', DashboardAction::class);
};
