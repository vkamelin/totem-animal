<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class QuestionsSeeder extends AbstractSeed
{
    private const array TRAIT_KEYS = [
        'extraversion',
        'openness',
        'self_control',
        'agreeableness',
        'emotional_stability',
        'dominance',
        'adaptability',
    ];

    private const array QUESTION_REQUIRED_KEYS = [
        'code',
        'text',
        'answers',
    ];

    private const array ANSWER_REQUIRED_KEYS = [
        'code',
        'text',
        'weights',
    ];

    public function run(): void
    {
        $questions = require __DIR__ . '/data/questions.php';
        $this->validateQuestions($questions);

        $connection = $this->getAdapter()->getConnection();
        $startedTransaction = false;
        $timestamp = gmdate('Y-m-d H:i:s');

        $questionSql = file_get_contents(__DIR__ . '/data/questions.sql');

        $answerSql = file_get_contents(__DIR__ . '/data/answers.sql');

        $questionIdSql = 'SELECT id FROM questions WHERE code = :code';

        $questionStmt = $connection->prepare($questionSql);
        $answerStmt = $connection->prepare($answerSql);
        $questionIdStmt = $connection->prepare($questionIdSql);

        try {
            if (!$connection->inTransaction()) {
                $connection->beginTransaction();
                $startedTransaction = true;
            }

            foreach ($questions as $questionIndex => $question) {
                $questionSortOrder = ($questionIndex + 1) * 10;

                $questionStmt->execute([
                    'code' => $question['code'],
                    'text' => $question['text'],
                    'is_active' => 1,
                    'sort_order' => $questionSortOrder,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                    'deleted_at' => null,
                ]);

                $questionIdStmt->execute([
                    'code' => $question['code'],
                ]);

                $questionId = $questionIdStmt->fetchColumn();
                if ($questionId === false) {
                    throw new RuntimeException(sprintf(
                        'Failed to resolve question id for code "%s".',
                        $question['code']
                    ));
                }

                foreach ($question['answers'] as $answerIndex => $answer) {
                    $answerStmt->execute([
                        'question_id' => (int) $questionId,
                        'code' => $answer['code'],
                        'text' => $answer['text'],
                        'weights' => json_encode($answer['weights'], JSON_THROW_ON_ERROR),
                        'sort_order' => ($answerIndex + 1) * 10,
                        'is_active' => 1,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'deleted_at' => null,
                    ]);
                }
            }

            if ($startedTransaction) {
                $connection->commit();
            }
        } catch (Throwable $throwable) {
            if ($startedTransaction && $connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }
    }

    /**
     * @param mixed $questions
     */
    private function validateQuestions(mixed $questions): void
    {
        if (!is_array($questions)) {
            throw new RuntimeException('Questions seed data must return an array.');
        }

        $questionCodes = [];

        foreach ($questions as $questionIndex => $question) {
            if (!is_array($question)) {
                throw new RuntimeException(sprintf(
                    'Question record at index %d must be an array.',
                    $questionIndex
                ));
            }

            foreach (self::QUESTION_REQUIRED_KEYS as $requiredKey) {
                if (!array_key_exists($requiredKey, $question)) {
                    throw new RuntimeException(sprintf(
                        'Question record at index %d is missing required key "%s".',
                        $questionIndex,
                        $requiredKey
                    ));
                }
            }

            $questionCode = $this->assertNonEmptyString($question['code'], $questionIndex, 'code');
            $this->assertNonEmptyString($question['text'], $questionIndex, 'text');

            if (!is_array($question['answers']) || $question['answers'] === []) {
                throw new RuntimeException(sprintf(
                    'Question record "%s" must contain a non-empty answers array.',
                    $questionCode
                ));
            }

            if (in_array($questionCode, $questionCodes, true)) {
                throw new RuntimeException(sprintf(
                    'Duplicate question code "%s" found in seed data.',
                    $questionCode
                ));
            }

            $questionCodes[] = $questionCode;
            $answerCodes = [];

            foreach ($question['answers'] as $answerIndex => $answer) {
                if (!is_array($answer)) {
                    throw new RuntimeException(sprintf(
                        'Answer record at question "%s", index %d must be an array.',
                        $questionCode,
                        $answerIndex
                    ));
                }

                foreach (self::ANSWER_REQUIRED_KEYS as $requiredKey) {
                    if (!array_key_exists($requiredKey, $answer)) {
                        throw new RuntimeException(sprintf(
                            'Answer record at question "%s", index %d is missing required key "%s".',
                            $questionCode,
                            $answerIndex,
                            $requiredKey
                        ));
                    }
                }

                $answerCode = $this->assertNonEmptyString($answer['code'], $questionIndex, 'answers.code');
                $this->assertNonEmptyString($answer['text'], $questionIndex, 'answers.text');

                if (in_array($answerCode, $answerCodes, true)) {
                    throw new RuntimeException(sprintf(
                        'Duplicate answer code "%s" found for question "%s".',
                        $answerCode,
                        $questionCode
                    ));
                }

                $answerCodes[] = $answerCode;

                if (!is_array($answer['weights']) || $answer['weights'] === []) {
                    throw new RuntimeException(sprintf(
                        'Answer "%s" for question "%s" must contain a non-empty weights array.',
                        $answerCode,
                        $questionCode
                    ));
                }

                foreach ($answer['weights'] as $trait => $weight) {
                    if (!is_string($trait) || $trait === '') {
                        throw new RuntimeException(sprintf(
                            'Answer "%s" for question "%s" contains an invalid trait key.',
                            $answerCode,
                            $questionCode
                        ));
                    }

                    if (!in_array($trait, self::TRAIT_KEYS, true)) {
                        throw new RuntimeException(sprintf(
                            'Answer "%s" for question "%s" contains unsupported trait "%s".',
                            $answerCode,
                            $questionCode,
                            $trait
                        ));
                    }

                    if (!is_int($weight)) {
                        throw new RuntimeException(sprintf(
                            'Answer "%s" for question "%s" trait "%s" must be an integer.',
                            $answerCode,
                            $questionCode,
                            $trait
                        ));
                    }
                }
            }
        }
    }

    /**
     * @param mixed $value
     */
    private function assertNonEmptyString(mixed $value, int $index, string $field): string
    {
        if (!is_string($value) || trim($value) === '') {
            throw new RuntimeException(sprintf(
                'Record at index %d field "%s" must be a non-empty string.',
                $index,
                $field
            ));
        }

        return $value;
    }
}
