<?php

declare(strict_types=1);

namespace App\Domain\Totem\Service;

use App\Domain\Totem\Entity\Animal;

final class MatchResult
{
    public function __construct(
        private readonly Animal $animal,
        private readonly float $distance,
    ) {
    }

    public function getAnimal(): Animal
    {
        return $this->animal;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }
}
