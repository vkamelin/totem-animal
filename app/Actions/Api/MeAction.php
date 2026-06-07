<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Domain\Client\Service\ClientMeService;
use App\Support\ResponseFactory;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MeAction
{
    public function __construct(
        private readonly ClientMeService $clientMeService,
    ) {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $payload = $this->parseJsonBody($request);
            $publicId = $this->normalizePublicId($payload['public_id'] ?? null);
        } catch (InvalidArgumentException|JsonException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Invalid public_id.',
                ],
            ], 422);
        }

        $client = $this->clientMeService->resolveClient($publicId);
        $result = $this->clientMeService->findResult($client['public_id']);

        return ResponseFactory::json(
            $response,
            $this->clientMeService->buildResponse($client['public_id'], $result)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJsonBody(ServerRequestInterface $request): array
    {
        $body = trim((string) $request->getBody());

        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new InvalidArgumentException('Invalid request body.');
        }

        return $decoded;
    }

    /**
     * @param mixed $value
     */
    private function normalizePublicId(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid public_id.');
        }

        $publicId = trim($value);

        if ($publicId === '') {
            return null;
        }

        if (!preg_match('/\A[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}\z/', $publicId)) {
            throw new InvalidArgumentException('Invalid public_id.');
        }

        return strtolower($publicId);
    }
}
