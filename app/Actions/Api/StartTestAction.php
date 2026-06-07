<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Domain\Totem\Exception\ClientNotFoundException;
use App\Domain\Totem\Exception\QuestionsNotConfiguredException;
use App\Domain\Totem\Exception\ResultAlreadyExistsException;
use App\Domain\Totem\Service\StartTestService;
use App\Support\ResponseFactory;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class StartTestAction
{
    public function __construct(
        private readonly StartTestService $startTestService,
    ) {
    }

    /**
     * @param array<string, mixed> $args
     * @throws Throwable
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $payload = $this->parseJsonBody($request);
            $publicId = $this->validatePublicId($payload);
            $data = $this->startTestService->start($publicId);
        } catch (JsonException|InvalidArgumentException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $this->buildValidationMessage($payload ?? null),
                ],
            ], 422);
        } catch (ClientNotFoundException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'CLIENT_NOT_FOUND',
                    'message' => 'Client not found.',
                ],
            ], 404);
        } catch (ResultAlreadyExistsException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'RESULT_ALREADY_EXISTS',
                    'message' => 'Test result already exists.',
                ],
            ], 409);
        } catch (QuestionsNotConfiguredException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'QUESTIONS_NOT_CONFIGURED',
                    'message' => 'Active questions are not configured.',
                ],
            ], 500);
        }

        return ResponseFactory::json($response, [
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * @return array<string, mixed>
     * @throws JsonException
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
     * @param array<string, mixed> $payload
     */
    private function validatePublicId(array $payload): string
    {
        if (!array_key_exists('public_id', $payload) || $payload['public_id'] === null) {
            throw new InvalidArgumentException('public_id is required.');
        }

        if (!is_string($payload['public_id'])) {
            throw new InvalidArgumentException('Invalid public_id.');
        }

        $publicId = trim($payload['public_id']);

        if ($publicId === '') {
            throw new InvalidArgumentException('public_id is required.');
        }

        if (!preg_match('/\A[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}\z/', $publicId)) {
            throw new InvalidArgumentException('Invalid public_id.');
        }

        return strtolower($publicId);
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function buildValidationMessage(?array $payload): string
    {
        if ($payload === null || !array_key_exists('public_id', $payload) || $payload['public_id'] === null) {
            return 'public_id is required.';
        }

        if (!is_string($payload['public_id'])) {
            return 'Invalid public_id.';
        }

        if (trim($payload['public_id']) === '') {
            return 'public_id is required.';
        }

        return 'Invalid public_id.';
    }
}
