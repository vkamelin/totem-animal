<?php

declare(strict_types=1);

namespace App\Actions\Api;

use App\Domain\Totem\Exception\AnimalsNotConfiguredException;
use App\Domain\Totem\Exception\AnswersCountMismatchException;
use App\Domain\Totem\Exception\ClientNotFoundException;
use App\Domain\Totem\Exception\DuplicateAnswersException;
use App\Domain\Totem\Exception\FinishTestValidationException;
use App\Domain\Totem\Exception\InvalidAnswersException;
use App\Domain\Totem\Exception\ResultAlreadyExistsException;
use App\Domain\Totem\Exception\TestSessionAlreadyCompletedException;
use App\Domain\Totem\Exception\TestSessionNotFoundException;
use App\Domain\Totem\Service\FinishTestService;
use App\Support\ResponseFactory;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FinishTestAction
{
    public function __construct(
        private readonly FinishTestService $finishTestService,
    ) {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        try {
            $payload = $this->parseJsonBody($request);
            $publicId = $this->validatePublicId($payload);
            $testSessionId = $this->validateTestSessionId($payload);
            $answers = $this->validateAnswersPayload($payload);

            $result = $this->finishTestService->finish($publicId, $testSessionId, $answers);
        } catch (JsonException|FinishTestValidationException $exception) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $this->mapValidationMessage($exception),
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
        } catch (TestSessionNotFoundException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'TEST_SESSION_NOT_FOUND',
                    'message' => 'Test session not found.',
                ],
            ], 404);
        } catch (TestSessionAlreadyCompletedException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'TEST_SESSION_ALREADY_COMPLETED',
                    'message' => 'Test session is already completed.',
                ],
            ], 409);
        } catch (ResultAlreadyExistsException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'RESULT_ALREADY_EXISTS',
                    'message' => 'Test result already exists.',
                ],
            ], 409);
        } catch (DuplicateAnswersException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'DUPLICATE_ANSWERS',
                    'message' => 'Only one answer per question is allowed.',
                ],
            ], 422);
        } catch (InvalidAnswersException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'INVALID_ANSWERS',
                    'message' => 'One or more answers are invalid.',
                ],
            ], 422);
        } catch (AnswersCountMismatchException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'ANSWERS_COUNT_MISMATCH',
                    'message' => 'Answers count does not match test session questions count.',
                ],
            ], 422);
        } catch (AnimalsNotConfiguredException) {
            return ResponseFactory::json($response, [
                'success' => false,
                'error' => [
                    'code' => 'ANIMALS_NOT_CONFIGURED',
                    'message' => 'Active animals are not configured.',
                ],
            ], 500);
        }

        return ResponseFactory::json($response, [
            'success' => true,
            'data' => $result,
        ]);
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
            throw new FinishTestValidationException('Invalid request body.');
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validatePublicId(array $payload): string
    {
        if (!array_key_exists('public_id', $payload) || $payload['public_id'] === null) {
            throw new FinishTestValidationException('public_id is required.');
        }

        if (!is_string($payload['public_id'])) {
            throw new FinishTestValidationException('Invalid public_id.');
        }

        $publicId = trim($payload['public_id']);

        if ($publicId === '') {
            throw new FinishTestValidationException('public_id is required.');
        }

        if (!preg_match('/\A[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}\z/', $publicId)) {
            throw new FinishTestValidationException('Invalid public_id.');
        }

        return strtolower($publicId);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function validateTestSessionId(array $payload): int
    {
        if (!array_key_exists('test_session_id', $payload)) {
            throw new FinishTestValidationException('Invalid test_session_id.');
        }

        $value = $payload['test_session_id'];

        if (is_int($value)) {
            if ($value <= 0) {
                throw new FinishTestValidationException('Invalid test_session_id.');
            }

            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            $intValue = (int) $value;

            if ($intValue <= 0) {
                throw new FinishTestValidationException('Invalid test_session_id.');
            }

            return $intValue;
        }

        throw new FinishTestValidationException('Invalid test_session_id.');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array{question_code:string, answer_code:string}>
     */
    private function validateAnswersPayload(array $payload): array
    {
        if (!array_key_exists('answers', $payload) || !is_array($payload['answers']) || $payload['answers'] === []) {
            throw new FinishTestValidationException('answers must be a non-empty array.');
        }

        $answers = [];

        foreach ($payload['answers'] as $answer) {
            if (!is_array($answer)) {
                throw new FinishTestValidationException('One or more answers are invalid.');
            }

            if (!array_key_exists('question_code', $answer) || !is_string($answer['question_code']) || trim($answer['question_code']) === '') {
                throw new FinishTestValidationException('One or more answers are invalid.');
            }

            if (!array_key_exists('answer_code', $answer) || !is_string($answer['answer_code']) || trim($answer['answer_code']) === '') {
                throw new FinishTestValidationException('One or more answers are invalid.');
            }

            $answers[] = [
                'question_code' => trim($answer['question_code']),
                'answer_code' => trim($answer['answer_code']),
            ];
        }

        return $answers;
    }

    private function mapValidationMessage(FinishTestValidationException $exception): string
    {
        $message = $exception->getMessage();

        if ($message === 'public_id is required.' || $message === 'Invalid public_id.') {
            return $message;
        }

        if ($message === 'Invalid test_session_id.' || $message === 'answers must be a non-empty array.') {
            return $message;
        }

        return 'One or more answers are invalid.';
    }
}
