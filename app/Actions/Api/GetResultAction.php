<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Support\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetResultAction
{
    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        return ResponseFactory::json($response, [
            'status' => 'placeholder',
            'action' => 'get-result',
            'public_id' => $args['public_id'] ?? null,
        ]);
    }
}
