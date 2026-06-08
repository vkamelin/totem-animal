<?php

declare(strict_types=1);

use App\Actions\Admin\DashboardAction;
use App\Actions\Api\FinishTestAction;
use App\Actions\Api\GetResultAction;
use App\Actions\Api\HealthAction;
use App\Actions\Api\MeAction;
use App\Actions\Api\StartTestAction;
use App\Actions\Api\SubmitAnswerAction;
use App\Infrastructure\RateLimit\RateLimiter;
use App\Infrastructure\Redis\RedisConnection;
use App\Middleware\RateLimiterMiddleware;
use App\Middleware\VkSignatureMiddleware;
use App\Support\Env;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

$redisConnection = new RedisConnection(require __DIR__ . '/../config/redis.php');
$rateLimiterMiddleware = new RateLimiterMiddleware(
    new RateLimiter($redisConnection),
    require __DIR__ . '/../config/rate_limit.php'
);
$vkSignatureMiddleware = new VkSignatureMiddleware(Env::string('VK_APP_SECRET', ''));

return static function (App $app) use ($rateLimiterMiddleware, $vkSignatureMiddleware): void {
    $app->group('/api', static function (RouteCollectorProxy $group) use ($rateLimiterMiddleware, $vkSignatureMiddleware): void {
        $group->get('/health', HealthAction::class)->add($rateLimiterMiddleware);
        $group->post('/me', MeAction::class)->add($rateLimiterMiddleware);
        $group->post('/test/start', StartTestAction::class)
            ->add($rateLimiterMiddleware)
            ->add($vkSignatureMiddleware);
        $group->post('/test/finish', FinishTestAction::class)
            ->add($rateLimiterMiddleware)
            ->add($vkSignatureMiddleware);
        $group->get('/result/{public_id}', GetResultAction::class)
            ->add($rateLimiterMiddleware)
            ->add($vkSignatureMiddleware);
    });

    $app->get('/dashboard', DashboardAction::class);
};
