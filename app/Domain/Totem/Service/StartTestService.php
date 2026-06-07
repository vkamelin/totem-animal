<?php

declare(strict_types=1);

namespace App\Domain\Totem\Service;

use App\Domain\Totem\Exception\ClientNotFoundException;
use App\Domain\Totem\Exception\QuestionsNotConfiguredException;
use App\Domain\Totem\Exception\ResultAlreadyExistsException;
use DateTimeImmutable;
use Illuminate\Database\Capsule\Manager as Capsule;
use Throwable;

final class StartTestService
{
    /**
     * @return array{
     *     public_id:string,
     *     test_session_id:int,
     *     questions_count:int,
     *     questions: array<int, array{
     *         code:string,
     *         text:string,
     *         answers: array<int, array{
     *             code:string,
     *             text:string
     *         }>
     *     }>
     * }
     * @throws Throwable
     */
    public function start(string $publicId): array
    {
        $client = $this->findClientByPublicId($publicId);

        if ($client === null) {
            throw new ClientNotFoundException();
        }

        $this->touchClient($client['id']);

        $this->assertResultDoesNotExist($publicId);

        $questions = $this->loadQuestionsWithAnswers();

        if ($questions === []) {
            throw new QuestionsNotConfiguredException();
        }

        return Capsule::connection()->transaction(function () use ($client, $publicId, $questions): array {
            $this->assertResultDoesNotExist($publicId);

            $testSessionId = $this->createTestSession($client, count($questions));

            return $this->buildResponse($publicId, $testSessionId, $questions);
        });
    }

    /**
     * @return array{id:int, public_id:string}|null
     */
    public function findClientByPublicId(string $publicId): ?array
    {
        $client = Capsule::table('app_clients')
            ->where('public_id', $publicId)
            ->first(['id', 'public_id']);

        if ($client === null) {
            return null;
        }

        return [
            'id' => (int) $client->id,
            'public_id' => (string) $client->public_id,
        ];
    }

    public function assertResultDoesNotExist(string $publicId): void
    {
        $exists = Capsule::table('test_results')
            ->where('public_id', $publicId)
            ->exists();

        if ($exists) {
            throw new ResultAlreadyExistsException();
        }
    }

    /**
     * @return array<int, array{
     *     code:string,
     *     text:string,
     *     answers: array<int, array{
     *         code:string,
     *         text:string
     *     }>
     * }>
     */
    public function loadQuestionsWithAnswers(): array
    {
        $questions = Capsule::table('questions')
            ->select(['id', 'code', 'text'])
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($questions->isEmpty()) {
            return [];
        }

        $questionIds = $questions->pluck('id')->all();

        $answers = Capsule::table('answers')
            ->select(['question_id', 'code', 'text'])
            ->whereIn('question_id', $questionIds)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $answersByQuestionId = [];

        foreach ($answers as $answer) {
            $questionId = (int) $answer->question_id;

            if (!array_key_exists($questionId, $answersByQuestionId)) {
                $answersByQuestionId[$questionId] = [];
            }

            $answersByQuestionId[$questionId][] = [
                'code' => (string) $answer->code,
                'text' => (string) $answer->text,
            ];
        }

        $result = [];

        foreach ($questions as $question) {
            $questionId = (int) $question->id;
            $questionAnswers = $answersByQuestionId[$questionId] ?? [];

            if ($questionAnswers === []) {
                continue;
            }

            $result[] = [
                'code' => (string) $question->code,
                'text' => (string) $question->text,
                'answers' => $questionAnswers,
            ];
        }

        return $result;
    }

    /**
     * @param array{id:int, public_id:string} $client
     */
    public function createTestSession(array $client, int $questionsCount): int
    {
        $nowValue = $this->formatDateTime($this->now());

        return (int) Capsule::table('test_sessions')->insertGetId([
            'public_id' => $client['public_id'],
            'client_id' => $client['id'],
            'status' => 'started',
            'questions_count' => $questionsCount,
            'answers_count' => 0,
            'started_at' => $nowValue,
            'completed_at' => null,
            'last_activity_at' => $nowValue,
            'created_at' => $nowValue,
            'updated_at' => $nowValue,
        ]);
    }

    /**
     * @param array<int, array{
     *     code:string,
     *     text:string,
     *     answers: array<int, array{
     *         code:string,
     *         text:string
     *     }>
     * }> $questions
     * @return array{
     *     public_id:string,
     *     test_session_id:int,
     *     questions_count:int,
     *     questions: array<int, array{
     *         code:string,
     *         text:string,
     *         answers: array<int, array{
     *             code:string,
     *             text:string
     *         }>
     *     }>
     * }
     */
    public function buildResponse(string $publicId, int $testSessionId, array $questions): array
    {
        return [
            'public_id' => $publicId,
            'test_session_id' => $testSessionId,
            'questions_count' => count($questions),
            'questions' => $questions,
        ];
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    private function formatDateTime(DateTimeImmutable $dateTime): string
    {
        return $dateTime->format('Y-m-d H:i:s');
    }

    private function touchClient(int $clientId): void
    {
        $timestamp = $this->formatDateTime($this->now());

        Capsule::table('app_clients')
            ->where('id', $clientId)
            ->update([
                'last_seen_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
    }
}
