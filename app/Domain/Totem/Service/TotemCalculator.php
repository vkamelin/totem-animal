<?php

declare(strict_types=1);

namespace App\Domain\Totem\Service;

use InvalidArgumentException;

final class TotemCalculator
{
    private const int BASE_VALUE = 50;
    private const int MIN_VALUE = 0;
    private const int MAX_VALUE = 100;
    private const int MIN_WEIGHT = -14;
    private const int MAX_WEIGHT = 14;
    private const float MULTIPLIER = 3.2;
    private const array TRAITS = [
        'extraversion',
        'openness',
        'self_control',
        'agreeableness',
        'emotional_stability',
        'dominance',
        'adaptability',
    ];

    /**
     * @param array<int, mixed> $selectedAnswers
     * @return array<string, int>
     */
    public function calculate(array $selectedAnswers): array
    {
        $traitTotals = [];
        $traitCounts = [];

        foreach (self::TRAITS as $trait) {
            $traitTotals[$trait] = 0;
            $traitCounts[$trait] = 0;
        }

        foreach ($selectedAnswers as $selectedAnswer) {
            $weights = $this->extractWeights($selectedAnswer);

            foreach ($weights as $trait => $delta) {
                $traitTotals[$trait] += $delta;
                $traitCounts[$trait]++;
            }
        }

        $traits = $this->getBaseTraits();

        foreach (self::TRAITS as $trait) {
            if ($traitCounts[$trait] === 0) {
                continue;
            }

            $averageDelta = $traitTotals[$trait] / $traitCounts[$trait];
            $value = (int) round(self::BASE_VALUE + $averageDelta * self::MULTIPLIER);

            $traits[$trait] = $this->clamp($value);
        }

        return $traits;
    }

    /**
     * @return array<int, string>
     */
    public function getTraitKeys(): array
    {
        return self::TRAITS;
    }

    /**
     * @return array<string, int>
     */
    public function getBaseTraits(): array
    {
        $traits = [];

        foreach (self::TRAITS as $trait) {
            $traits[$trait] = self::BASE_VALUE;
        }

        return $traits;
    }

    /**
     * @param mixed $selectedAnswer
     * @return array<string, int>
     */
    private function extractWeights(mixed $selectedAnswer): array
    {
        if (is_array($selectedAnswer)) {
            if (!array_key_exists('weights', $selectedAnswer) || !is_array($selectedAnswer['weights'])) {
                throw new InvalidArgumentException('Selected answer must contain a weights array.');
            }

            return $this->validateWeights($selectedAnswer['weights']);
        }

        if (is_object($selectedAnswer)) {
            if (method_exists($selectedAnswer, 'getWeights')) {
                $weights = $selectedAnswer->getWeights();

                if (!is_array($weights)) {
                    throw new InvalidArgumentException('Selected answer weights must be an array.');
                }

                return $this->validateWeights($weights);
            }

            if (method_exists($selectedAnswer, 'weights')) {
                $weights = $selectedAnswer->weights();

                if (!is_array($weights)) {
                    throw new InvalidArgumentException('Selected answer weights must be an array.');
                }

                return $this->validateWeights($weights);
            }
        }

        throw new InvalidArgumentException('Selected answer must be an array or expose weights.');
    }

    /**
     * @param array $weights
     * @return array<string, int>
     */
    private function validateWeights(array $weights): array
    {
        $validatedWeights = [];

        foreach ($weights as $trait => $weight) {
            if (!is_string($trait) || !in_array($trait, self::TRAITS, true)) {
                throw new InvalidArgumentException(sprintf('Invalid trait key "%s".', (string) $trait));
            }

            if (!is_int($weight) && !is_numeric($weight)) {
                throw new InvalidArgumentException(sprintf('Weight for trait "%s" must be numeric.', $trait));
            }

            $numericWeight = (float) $weight;
            $delta = (int) $numericWeight;

            if ($numericWeight !== (float) $delta) {
                throw new InvalidArgumentException(sprintf('Weight for trait "%s" must be safely castable to integer.', $trait));
            }

            if ($delta < self::MIN_WEIGHT || $delta > self::MAX_WEIGHT) {
                throw new InvalidArgumentException(sprintf('Weight for trait "%s" must be between %d and %d.', $trait, self::MIN_WEIGHT, self::MAX_WEIGHT));
            }

            $validatedWeights[$trait] = $delta;
        }

        return $validatedWeights;
    }

    private function clamp(int $value): int
    {
        return max(self::MIN_VALUE, min(self::MAX_VALUE, $value));
    }
}
