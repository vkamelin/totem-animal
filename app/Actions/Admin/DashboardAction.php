<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Support\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DashboardAction
{
    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        return ResponseFactory::json($response, [
            'status' => 'placeholder',
            'area' => 'admin-dashboard',
        ]);
    }
}
