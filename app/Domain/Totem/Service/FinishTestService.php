<?php

declare(strict_types=1);

namespace App\Domain\Totem\Service;

use App\Domain\Totem\Entity\Animal;
use App\Domain\Totem\Exception\AnimalsNotConfiguredException;
use App\Domain\Totem\Exception\AnswersCountMismatchException;
use App\Domain\Totem\Exception\ClientNotFoundException;
use App\Domain\Totem\Exception\DuplicateAnswersException;
use App\Domain\Totem\Exception\InvalidAnswersException;
use App\Domain\Totem\Exception\ResultAlreadyExistsException;
use App\Domain\Totem\Exception\TestSessionAlreadyCompletedException;
use App\Domain\Totem\Exception\TestSessionNotFoundException;
use DateTimeImmutable;
use Illuminate\Database\Capsule\Manager as Capsule;
use JsonException;
use Throwable;

final class FinishTestService
{
    public function __construct(
        private readonly TotemCalculator $totemCalculator,
        private readonly AnimalMatcher $animalMatcher,
    ) {
    }

    /**
     * @param array<int, array{question_code:string, answer_code:string}> $answers
     * @return array{public_id:string,test_session_id:int,result:array{animal_code:string,animal_name:string,result_title:string,result_description:string,result_image_path:string}}
     * @throws Throwable
     */
    public function finish(string $publicId, int $testSessionId, array $answers): array
    {
        $this->validateAnswerPayload($answers);
        $this->assertNoDuplicateQuestions($answers);

        $client = $this->findClientByPublicId($publicId);

        if ($client === null) {
            throw new ClientNotFoundException();
        }

        $testSession = $this->findStartedTestSession($publicId, (int) $client['id'], $testSessionId);

        if ($testSession === null) {
            throw new TestSessionNotFoundException();
        }

        if (($testSession['status'] ?? null) === 'completed') {
            throw new TestSessionAlreadyCompletedException();
        }

        $this->assertResultDoesNotExist($publicId, $testSessionId);

        $selectedAnswers = $this->loadSelectedAnswers($answers);
        $this->assertAnswersCountMatchesSession($testSession, $selectedAnswers);

        $animals = $this->loadActiveAnimals();

        if ($animals === []) {
            throw new AnimalsNotConfiguredException();
        }

        return Capsule::connection()->transaction(function () use ($client, $testSession, $selectedAnswers): array {
            $this->saveSessionAnswers((int) $testSession['id'], $selectedAnswers);

            $userTraits = $this->calculateUserTraits($selectedAnswers);
            $animals = $this->loadActiveAnimals();

            if ($animals === []) {
                throw new AnimalsNotConfiguredException();
            }

            $matchResult = $this->matchAnimal($userTraits, $animals);
            $testResult = $this->saveTestResult($client, $testSession, $userTraits, $matchResult);

            $this->completeTestSession((int) $testSession['id'], count($selectedAnswers));
            $this->touchClient((int) $client['id']);

            return $this->buildResponse($testResult);
        });
    }

    /**
     * @return array{id:int,public_id:string}|null
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

    /**
     * @return array{id:int,public_id:string,client_id:int,status:string,questions_count:int,answers_count:int}|null
     */
    public function findStartedTestSession(string $publicId, int $clientId, int $testSessionId): ?array
    {
        $testSession = Capsule::table('test_sessions')
            ->where('id', $testSessionId)
            ->where('public_id', $publicId)
            ->where('client_id', $clientId)
            ->first([
                'id',
                'public_id',
                'client_id',
                'status',
                'questions_count',
                'answers_count',
            ]);

        if ($testSession === null) {
            return null;
        }

        return [
            'id' => (int) $testSession->id,
            'public_id' => (string) $testSession->public_id,
            'client_id' => (int) $testSession->client_id,
            'status' => (string) $testSession->status,
            'questions_count' => (int) $testSession->questions_count,
            'answers_count' => (int) $testSession->answers_count,
        ];
    }

    public function assertResultDoesNotExist(string $publicId, int $testSessionId): void
    {
        $exists = Capsule::table('test_results')
            ->where('public_id', $publicId)
            ->orWhere('test_session_id', $testSessionId)
            ->exists();

        if ($exists) {
            throw new ResultAlreadyExistsException();
        }
    }

    /**
     * @param array<int, array{question_code:string, answer_code:string}> $answers
     */
    public function validateAnswerPayload(array $answers): void
    {
        if ($answers === []) {
            throw new InvalidAnswersException();
        }

        foreach ($answers as $answer) {
            if (!array_key_exists('question_code', $answer) || !is_string($answer['question_code']) || trim($answer['question_code']) === '') {
                throw new InvalidAnswersException();
            }

            if (!array_key_exists('answer_code', $answer) || !is_string($answer['answer_code']) || trim($answer['answer_code']) === '') {
                throw new InvalidAnswersException();
            }
        }
    }

    /**
     * @param array<int, array{question_code:string, answer_code:string}> $answers
     * @return array<int, array{
     *     question_id:int,
     *     question_code:string,
     *     question_text:string,
     *     answer_id:int,
     *     answer_code:string,
     *     answer_text:string,
     *     weights_snapshot: array<string, int>,
     *     weights: array<string, int>
     * }>
     * @throws JsonException
     */
    public function loadSelectedAnswers(array $answers): array
    {
        $questionCodes = [];
        $answerCodes = [];

        foreach ($answers as $answer) {
            $questionCodes[] = $answer['question_code'];
            $answerCodes[] = $answer['answer_code'];
        }

        $questions = Capsule::table('questions')
            ->select(['id', 'code', 'text'])
            ->whereIn('code', array_values(array_unique($questionCodes)))
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->get();

        $questionsByCode = [];

        foreach ($questions as $question) {
            $questionsByCode[(string) $question->code] = [
                'id' => (int) $question->id,
                'code' => (string) $question->code,
                'text' => (string) $question->text,
            ];
        }

        if (count($questionsByCode) !== count(array_unique($questionCodes))) {
            throw new InvalidAnswersException();
        }

        $questionIds = array_map(
            static fn (array $question): int => $question['id'],
            array_values($questionsByCode),
        );

        $answersRows = Capsule::table('answers')
            ->select(['id', 'question_id', 'code', 'text', 'weights'])
            ->whereIn('question_id', $questionIds)
            ->whereIn('code', array_values(array_unique($answerCodes)))
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->get();

        $answersByQuestionAndCode = [];

        foreach ($answersRows as $row) {
            $weights = $this->decodeWeights($row->weights);

            $answersByQuestionAndCode[(int) $row->question_id . ':' . $row->code] = [
                'id' => (int) $row->id,
                'question_id' => (int) $row->question_id,
                'code' => (string) $row->code,
                'text' => (string) $row->text,
                'weights' => $weights,
            ];
        }

        $selected = [];

        foreach ($answers as $answer) {
            $question = $questionsByCode[$answer['question_code']] ?? null;

            if ($question === null) {
                throw new InvalidAnswersException();
            }

            $answerKey = $question['id'] . ':' . $answer['answer_code'];
            $matchedAnswer = $answersByQuestionAndCode[$answerKey] ?? null;

            if ($matchedAnswer === null) {
                throw new InvalidAnswersException();
            }

            $selected[] = [
                'question_id' => $question['id'],
                'question_code' => $question['code'],
                'question_text' => $question['text'],
                'answer_id' => $matchedAnswer['id'],
                'answer_code' => $matchedAnswer['code'],
                'answer_text' => $matchedAnswer['text'],
                'weights_snapshot' => $matchedAnswer['weights'],
                'weights' => $matchedAnswer['weights'],
            ];
        }

        return $selected;
    }

    /**
     * @param array<int, array{question_code:string, answer_code:string}> $answers
     */
    public function assertNoDuplicateQuestions(array $answers): void
    {
        $seen = [];

        foreach ($answers as $answer) {
            $questionCode = $answer['question_code'];

            if (array_key_exists($questionCode, $seen)) {
                throw new DuplicateAnswersException();
            }

            $seen[$questionCode] = true;
        }
    }

    /**
     * @param array{id:int,public_id:string,client_id:int,status:string,questions_count:int,answers_count:int} $testSession
     * @param array<int, array<string, mixed>> $selectedAnswers
     */
    public function assertAnswersCountMatchesSession(array $testSession, array $selectedAnswers): void
    {
        if (count($selectedAnswers) !== (int) $testSession['questions_count']) {
            throw new AnswersCountMismatchException();
        }
    }

    /**
     * @return array<int, Animal>
     */
    public function loadActiveAnimals(): array
    {
        $rows = Capsule::table('animals')
            ->select([
                'id',
                'code',
                'name',
                'title',
                'description',
                'image_path',
                'extraversion',
                'openness',
                'self_control',
                'agreeableness',
                'emotional_stability',
                'dominance',
                'adaptability',
                'is_active',
                'sort_order',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $animals = [];

        foreach ($rows as $row) {
            $animals[] = Animal::fromArray((array) $row);
        }

        return $animals;
    }

    /**
     * @param array<int, array{
     *     question_id:int,
     *     question_code:string,
     *     question_text:string,
     *     answer_id:int,
     *     answer_code:string,
     *     answer_text:string,
     *     weights_snapshot: array<string, int>,
     *     weights: array<string, int>
     * }> $selectedAnswers
     * @throws JsonException
     */
    public function saveSessionAnswers(int $testSessionId, array $selectedAnswers): void
    {
        $timestamp = $this->now()->format('Y-m-d H:i:s');
        $rows = [];

        foreach ($selectedAnswers as $selectedAnswer) {
            $rows[] = [
                'test_session_id' => $testSessionId,
                'question_id' => $selectedAnswer['question_id'],
                'answer_id' => $selectedAnswer['answer_id'],
                'question_code' => $selectedAnswer['question_code'],
                'answer_code' => $selectedAnswer['answer_code'],
                'question_text' => $selectedAnswer['question_text'],
                'answer_text' => $selectedAnswer['answer_text'],
                'weights_snapshot' => json_encode($selectedAnswer['weights_snapshot'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'answered_at' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        Capsule::table('test_session_answers')->insert($rows);
    }

    /**
     * @param array<int, array<string, mixed>> $selectedAnswers
     * @return array<string, int>
     */
    public function calculateUserTraits(array $selectedAnswers): array
    {
        return $this->totemCalculator->calculate($selectedAnswers);
    }

    /**
     * @param array<string, int|float> $userTraits
     * @param array<int, Animal> $animals
     */
    public function matchAnimal(array $userTraits, array $animals): MatchResult
    {
        return $this->animalMatcher->match($userTraits, $animals);
    }

    /**
     * @param array{id:int,public_id:string} $client
     * @param array{id:int,public_id:string,client_id:int,status:string,questions_count:int,answers_count:int} $testSession
     * @param array<string, int> $userTraits
     */
    public function saveTestResult(array $client, array $testSession, array $userTraits, MatchResult $matchResult): array
    {
        $animal = $matchResult->getAnimal();
        $timestamp = $this->now()->format('Y-m-d H:i:s');
        $scoreDistance = number_format($matchResult->getDistance(), 4, '.', '');

        $data = [
            'public_id' => $testSession['public_id'],
            'test_session_id' => $testSession['id'],
            'client_id' => $client['id'],
            'animal_id' => $animal->getId(),
            'animal_code' => $animal->getCode(),
            'animal_name' => $animal->getName(),
            'result_title' => $this->resolveResultTitle($animal),
            'result_description' => $animal->getDescription(),
            'result_image_path' => $animal->getImagePath(),
            'user_extraversion' => $userTraits['extraversion'],
            'user_openness' => $userTraits['openness'],
            'user_self_control' => $userTraits['self_control'],
            'user_agreeableness' => $userTraits['agreeableness'],
            'user_emotional_stability' => $userTraits['emotional_stability'],
            'user_dominance' => $userTraits['dominance'],
            'user_adaptability' => $userTraits['adaptability'],
            'animal_extraversion' => $animal->getExtraversion(),
            'animal_openness' => $animal->getOpenness(),
            'animal_self_control' => $animal->getSelfControl(),
            'animal_agreeableness' => $animal->getAgreeableness(),
            'animal_emotional_stability' => $animal->getEmotionalStability(),
            'animal_dominance' => $animal->getDominance(),
            'animal_adaptability' => $animal->getAdaptability(),
            'score_distance' => $scoreDistance,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];

        Capsule::table('test_results')->insert($data);

        return $data;
    }

    public function completeTestSession(int $testSessionId, int $answersCount): void
    {
        $timestamp = $this->now()->format('Y-m-d H:i:s');

        Capsule::table('test_sessions')
            ->where('id', $testSessionId)
            ->update([
                'status' => 'completed',
                'answers_count' => $answersCount,
                'completed_at' => $timestamp,
                'last_activity_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
    }

    /**
     * @param array<string, mixed> $testResult
     * @return array{public_id:string,test_session_id:int,result:array{animal_code:string,animal_name:string,result_title:string,result_description:string,result_image_path:string}}
     */
    public function buildResponse(array $testResult): array
    {
        return [
            'public_id' => (string) $testResult['public_id'],
            'test_session_id' => (int) $testResult['test_session_id'],
            'result' => [
                'animal_code' => (string) $testResult['animal_code'],
                'animal_name' => (string) $testResult['animal_name'],
                'result_title' => (string) $testResult['result_title'],
                'result_description' => (string) $testResult['result_description'],
                'result_image_path' => (string) $testResult['result_image_path'],
            ],
        ];
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    private function touchClient(int $clientId): void
    {
        $timestamp = $this->now()->format('Y-m-d H:i:s');

        Capsule::table('app_clients')
            ->where('id', $clientId)
            ->update([
                'last_seen_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
    }

    /**
     * @param mixed $weights
     * @return array<string, int>
     * @throws JsonException
     */
    private function decodeWeights(mixed $weights): array
    {
        if (is_array($weights)) {
            return $weights;
        }

        if (is_string($weights)) {
            $decoded = json_decode($weights, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($decoded)) {
                throw new InvalidAnswersException();
            }

            return $decoded;
        }

        throw new InvalidAnswersException();
    }

    private function resolveResultTitle(Animal $animal): string
    {
        $title = $animal->getTitle();

        if ($title !== null && trim($title) !== '') {
            return $title;
        }

        return sprintf('Твоё тотемное животное — %s', $animal->getName());
    }
}
