<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Domain\Totem\Exception\ResultNotFoundException;
use App\Domain\Totem\Exception\ResultValidationException;
use App\Domain\Totem\Service\GetResultService;
use App\Support\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetResultAction
{
    public function __construct(
        private readonly GetResultService $getResultService,
    ) {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $publicId = $this->extractPublicId($args);
            $result = $this->getResultService->getByPublicId($publicId);
        } catch (ResultValidationException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'public_id is required.',
                ],
            ], 422);
        } catch (ResultNotFoundException $exception) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => $exception->getErrorCode(),
                    'message' => $exception->getMessage(),
                ],
            ], 404);
        }

        return ResponseFactory::json($response, [
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * @param array<string, mixed> $args
     */
    private function extractPublicId(array $args): string
    {
        if (!array_key_exists('public_id', $args)) {
            throw new ResultValidationException('public_id is required.');
        }

        $publicId = $args['public_id'];

        if (!is_string($publicId) || trim($publicId) === '') {
            throw new ResultValidationException('public_id is required.');
        }

        $publicId = trim($publicId);

        if (!preg_match('/\A[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}\z/', $publicId)) {
            throw new ResultValidationException('Invalid public_id.');
        }

        return strtolower($publicId);
    }
}
