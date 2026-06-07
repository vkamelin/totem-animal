<?php

declare(strict_types=1);

namespace App\Domain\Totem\Service;

use App\Domain\Totem\Entity\Animal;
use InvalidArgumentException;

final class AnimalMatcher
{
    private const array REQUIRED_TRAITS = [
        'extraversion',
        'openness',
        'self_control',
        'agreeableness',
        'emotional_stability',
        'dominance',
        'adaptability',
    ];

    /**
     * @param array<string, mixed> $userTraits
     * @param array<int, Animal> $animals
     */
    public function match(array $userTraits, array $animals): MatchResult
    {
        $this->validateUserTraits($userTraits);

        if ($animals === []) {
            throw new InvalidArgumentException('Animals list must not be empty.');
        }

        $bestAnimal = null;
        $bestDistance = null;
        $bestSortOrder = null;
        $bestId = null;

        foreach ($animals as $animal) {
            if (!$animal instanceof Animal) {
                throw new InvalidArgumentException('Each animal must be an instance of ' . Animal::class . '.');
            }

            if (!$animal->isActive()) {
                continue;
            }

            $distance = $this->calculateDistance($userTraits, $animal);
            $sortOrder = $animal->getSortOrder();
            $id = $animal->getId();

            if (
                $bestAnimal === null
                || $distance < $bestDistance
                || ($distance === $bestDistance && $sortOrder < $bestSortOrder)
                || ($distance === $bestDistance && $sortOrder === $bestSortOrder && $id < $bestId)
            ) {
                $bestAnimal = $animal;
                $bestDistance = $distance;
                $bestSortOrder = $sortOrder;
                $bestId = $id;
            }
        }

        if ($bestAnimal === null || $bestDistance === null) {
            throw new InvalidArgumentException('No active animals available for matching.');
        }

        return new MatchResult($bestAnimal, $bestDistance);
    }

    /**
     * @param array<string, mixed> $userTraits
     */
    private function calculateDistance(array $userTraits, Animal $animal): float
    {
        $sum = 0.0;

        foreach (self::REQUIRED_TRAITS as $trait) {
            $userValue = (float) $userTraits[$trait];
            $animalValue = (float) $this->getAnimalTraitValue($animal, $trait);

            $sum += ($userValue - $animalValue) ** 2;
        }

        return sqrt($sum);
    }

    /**
     * @param array<string, mixed> $userTraits
     */
    private function validateUserTraits(array $userTraits): void
    {
        foreach (self::REQUIRED_TRAITS as $trait) {
            if (!array_key_exists($trait, $userTraits)) {
                throw new InvalidArgumentException(sprintf('User trait "%s" is required.', $trait));
            }

            if (!is_numeric($userTraits[$trait])) {
                throw new InvalidArgumentException(sprintf('User trait "%s" must be numeric.', $trait));
            }

            $value = (float) $userTraits[$trait];

            if ($value < 0.0 || $value > 100.0) {
                throw new InvalidArgumentException(sprintf('User trait "%s" must be between 0 and 100.', $trait));
            }
        }
    }

    private function getAnimalTraitValue(Animal $animal, string $trait): int
    {
        return match ($trait) {
            'extraversion' => $animal->getExtraversion(),
            'openness' => $animal->getOpenness(),
            'self_control' => $animal->getSelfControl(),
            'agreeableness' => $animal->getAgreeableness(),
            'emotional_stability' => $animal->getEmotionalStability(),
            'dominance' => $animal->getDominance(),
            'adaptability' => $animal->getAdaptability(),
            default => throw new InvalidArgumentException(sprintf('Invalid trait "%s".', $trait)),
        };
    }
}
