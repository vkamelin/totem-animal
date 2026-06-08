<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Support\ResponseFactory;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HealthAction
{
    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        return ResponseFactory::json($response, [
            'success' => true,
            'data' => [
                'status' => 'ok',
                'service' => 'totem-animal-api',
                'timestamp' => new DateTimeImmutable()->format(DATE_ATOM),
            ],
        ]);
    }
}
