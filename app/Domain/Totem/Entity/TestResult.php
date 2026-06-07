<?php

declare(strict_types=1);

namespace App\Domain\Totem\Entity;

use DateTimeImmutable;

final class TestResult
{
    /**
     * @param array{extraversion:int,openness:int,self_control:int,agreeableness:int,emotional_stability:int,dominance:int,adaptability:int} $userTraits
     * @param array{extraversion:int,openness:int,self_control:int,agreeableness:int,emotional_stability:int,dominance:int,adaptability:int} $animalTraits
     */
    public function __construct(
        private ?int $id = null,
        private string $publicId = '',
        private int $testSessionId = 0,
        private ?int $clientId = null,
        private ?int $animalId = null,
        private string $animalCode = '',
        private string $animalName = '',
        private string $resultTitle = '',
        private string $resultDescription = '',
        private string $resultImagePath = '',
        private int $userExtraversion = 0,
        private int $userOpenness = 0,
        private int $userSelfControl = 0,
        private int $userAgreeableness = 0,
        private int $userEmotionalStability = 0,
        private int $userDominance = 0,
        private int $userAdaptability = 0,
        private int $animalExtraversion = 0,
        private int $animalOpenness = 0,
        private int $animalSelfControl = 0,
        private int $animalAgreeableness = 0,
        private int $animalEmotionalStability = 0,
        private int $animalDominance = 0,
        private int $animalAdaptability = 0,
        private ?float $scoreDistance = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getTestSessionId(): int
    {
        return $this->testSessionId;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function getAnimalId(): ?int
    {
        return $this->animalId;
    }

    public function getAnimalCode(): string
    {
        return $this->animalCode;
    }

    public function getAnimalName(): string
    {
        return $this->animalName;
    }

    public function getResultTitle(): string
    {
        return $this->resultTitle;
    }

    public function getResultDescription(): string
    {
        return $this->resultDescription;
    }

    public function getResultImagePath(): string
    {
        return $this->resultImagePath;
    }

    public function getUserExtraversion(): int
    {
        return $this->userExtraversion;
    }

    public function getUserOpenness(): int
    {
        return $this->userOpenness;
    }

    public function getUserSelfControl(): int
    {
        return $this->userSelfControl;
    }

    public function getUserAgreeableness(): int
    {
        return $this->userAgreeableness;
    }

    public function getUserEmotionalStability(): int
    {
        return $this->userEmotionalStability;
    }

    public function getUserDominance(): int
    {
        return $this->userDominance;
    }

    public function getUserAdaptability(): int
    {
        return $this->userAdaptability;
    }

    public function getAnimalExtraversion(): int
    {
        return $this->animalExtraversion;
    }

    public function getAnimalOpenness(): int
    {
        return $this->animalOpenness;
    }

    public function getAnimalSelfControl(): int
    {
        return $this->animalSelfControl;
    }

    public function getAnimalAgreeableness(): int
    {
        return $this->animalAgreeableness;
    }

    public function getAnimalEmotionalStability(): int
    {
        return $this->animalEmotionalStability;
    }

    public function getAnimalDominance(): int
    {
        return $this->animalDominance;
    }

    public function getAnimalAdaptability(): int
    {
        return $this->animalAdaptability;
    }

    public function getScoreDistance(): ?float
    {
        return $this->scoreDistance;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return array{extraversion:int,openness:int,self_control:int,agreeableness:int,emotional_stability:int,dominance:int,adaptability:int}
     */
    public function getUserTraits(): array
    {
        return [
            'extraversion' => $this->userExtraversion,
            'openness' => $this->userOpenness,
            'self_control' => $this->userSelfControl,
            'agreeableness' => $this->userAgreeableness,
            'emotional_stability' => $this->userEmotionalStability,
            'dominance' => $this->userDominance,
            'adaptability' => $this->userAdaptability,
        ];
    }

    /**
     * @return array{extraversion:int,openness:int,self_control:int,agreeableness:int,emotional_stability:int,dominance:int,adaptability:int}
     */
    public function getAnimalTraits(): array
    {
        return [
            'extraversion' => $this->animalExtraversion,
            'openness' => $this->animalOpenness,
            'self_control' => $this->animalSelfControl,
            'agreeableness' => $this->animalAgreeableness,
            'emotional_stability' => $this->animalEmotionalStability,
            'dominance' => $this->animalDominance,
            'adaptability' => $this->animalAdaptability,
        ];
    }

    public function getResultSnapshot(): array
    {
        return [
            'public_id' => $this->publicId,
            'animal_code' => $this->animalCode,
            'animal_name' => $this->animalName,
            'result_title' => $this->resultTitle,
            'result_description' => $this->resultDescription,
            'result_image_path' => $this->resultImagePath,
            'user_traits' => $this->getUserTraits(),
            'animal_traits' => $this->getAnimalTraits(),
            'score_distance' => $this->scoreDistance,
            'created_at' => $this->createdAt?->format(DATE_ATOM),
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->publicId,
            'test_session_id' => $this->testSessionId,
            'client_id' => $this->clientId,
            'animal_id' => $this->animalId,
            'animal_code' => $this->animalCode,
            'animal_name' => $this->animalName,
            'result_title' => $this->resultTitle,
            'result_description' => $this->resultDescription,
            'result_image_path' => $this->resultImagePath,
            'user_extraversion' => $this->userExtraversion,
            'user_openness' => $this->userOpenness,
            'user_self_control' => $this->userSelfControl,
            'user_agreeableness' => $this->userAgreeableness,
            'user_emotional_stability' => $this->userEmotionalStability,
            'user_dominance' => $this->userDominance,
            'user_adaptability' => $this->userAdaptability,
            'animal_extraversion' => $this->animalExtraversion,
            'animal_openness' => $this->animalOpenness,
            'animal_self_control' => $this->animalSelfControl,
            'animal_agreeableness' => $this->animalAgreeableness,
            'animal_emotional_stability' => $this->animalEmotionalStability,
            'animal_dominance' => $this->animalDominance,
            'animal_adaptability' => $this->animalAdaptability,
            'score_distance' => $this->scoreDistance,
            'created_at' => $this->createdAt?->format(DATE_ATOM),
            'updated_at' => $this->updatedAt?->format(DATE_ATOM),
        ];
    }
}
