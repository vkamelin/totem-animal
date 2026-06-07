<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class AnimalsSeeder extends AbstractSeed
{
    private const array REQUIRED_KEYS = [
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
    ];

    public function run(): void
    {
        $animals = require __DIR__ . '/data/animals.php';
        $this->validateAnimals($animals);

        $timestamp = gmdate('Y-m-d H:i:s');
        $sql = file_get_contents(__DIR__ . '/data/animals.sql');

        $connection = $this->getAdapter()->getConnection();
        $startedTransaction = false;

        try {
            if (!$connection->inTransaction()) {
                $connection->beginTransaction();
                $startedTransaction = true;
            }

            foreach ($animals as $animal) {
                $this->execute($sql, [
                    'code' => $animal['code'],
                    'name' => $animal['name'],
                    'title' => $animal['title'],
                    'description' => $animal['description'],
                    'image_path' => $animal['image_path'],
                    'extraversion' => $animal['extraversion'],
                    'openness' => $animal['openness'],
                    'self_control' => $animal['self_control'],
                    'agreeableness' => $animal['agreeableness'],
                    'emotional_stability' => $animal['emotional_stability'],
                    'dominance' => $animal['dominance'],
                    'adaptability' => $animal['adaptability'],
                    'is_active' => $animal['is_active'],
                    'sort_order' => $animal['sort_order'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                    'deleted_at' => null,
                ]);
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
     * @param mixed $animals
     */
    private function validateAnimals(mixed $animals): void
    {
        if (!is_array($animals)) {
            throw new RuntimeException('Animals seed data must return an array.');
        }

        if (count($animals) !== 40) {
            throw new RuntimeException('Animals seed data must contain exactly 40 records.');
        }

        $codes = [];

        foreach ($animals as $index => $animal) {
            if (!is_array($animal)) {
                throw new RuntimeException(sprintf('Animal record at index %d must be an array.', $index));
            }

            foreach (self::REQUIRED_KEYS as $requiredKey) {
                if (!array_key_exists($requiredKey, $animal)) {
                    throw new RuntimeException(sprintf(
                        'Animal record at index %d is missing required key "%s".',
                        $index,
                        $requiredKey
                    ));
                }
            }

            $code = $this->assertNonEmptyString($animal['code'], $index, 'code');
            $this->assertNonEmptyString($animal['name'], $index, 'name');
            $this->assertNonEmptyString($animal['title'], $index, 'title');
            $description = $this->assertNonEmptyString($animal['description'], $index, 'description');
            $imagePath = $this->assertNonEmptyString($animal['image_path'], $index, 'image_path');

            $this->assertIntegerRange($animal['extraversion'], $index, 'extraversion', 0, 100);
            $this->assertIntegerRange($animal['openness'], $index, 'openness', 0, 100);
            $this->assertIntegerRange($animal['self_control'], $index, 'self_control', 0, 100);
            $this->assertIntegerRange($animal['agreeableness'], $index, 'agreeableness', 0, 100);
            $this->assertIntegerRange($animal['emotional_stability'], $index, 'emotional_stability', 0, 100);
            $this->assertIntegerRange($animal['dominance'], $index, 'dominance', 0, 100);
            $this->assertIntegerRange($animal['adaptability'], $index, 'adaptability', 0, 100);

            if (!in_array($animal['is_active'], [0, 1], true)) {
                throw new RuntimeException(sprintf(
                    'Animal record "%s" field "is_active" must be integer 0 or 1.',
                    $code
                ));
            }

            if (!is_int($animal['sort_order'])) {
                throw new RuntimeException(sprintf(
                    'Animal record "%s" field "sort_order" must be an integer.',
                    $code
                ));
            }

            $codes[] = $code;
        }

        if (count($codes) !== count(array_unique($codes))) {
            throw new RuntimeException('Animals seed data contains duplicate code values.');
        }
    }

    /**
     * @param mixed $value
     * @param int $index
     * @param string $field
     * @return string
     */
    private function assertNonEmptyString(mixed $value, int $index, string $field): string
    {
        if (!is_string($value) || trim($value) === '') {
            throw new RuntimeException(sprintf(
                'Animal record at index %d field "%s" must be a non-empty string.',
                $index,
                $field
            ));
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param int $index
     * @param string $field
     * @param int $min
     * @param int $max
     */
    private function assertIntegerRange(mixed $value, int $index, string $field, int $min, int $max): void
    {
        if (!is_int($value) || $value < $min || $value > $max) {
            throw new RuntimeException(sprintf(
                'Animal record at index %d field "%s" must be an integer between %d and %d.',
                $index,
                $field,
                $min,
                $max
            ));
        }
    }

    private function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }

        return strlen($value);
    }
}
