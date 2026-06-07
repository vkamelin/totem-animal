<?php

declare(strict_types=1);

namespace App\Domain\Totem\Entity;

use DateTimeImmutable;
use InvalidArgumentException;

final class Answer
{
    private const array ALLOWED_TRAIT_KEYS = [
        'extraversion',
        'openness',
        'self_control',
        'agreeableness',
        'emotional_stability',
        'dominance',
        'adaptability',
    ];

    /**
     * @param array<string, int> $weights
     */
    public function __construct(
        private ?int $id,
        private int $questionId,
        private string $code,
        private string $text,
        private array $weights,
        private int $sortOrder = 0,
        private bool $isActive = true,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ) {
        $this->assertValid();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return array<string, int>
     */
    public function getWeights(): array
    {
        return $this->weights;
    }

    public function getWeight(string $trait): int
    {
        return $this->weights[$trait] ?? 0;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->questionId,
            'code' => $this->code,
            'text' => $this->text,
            'weights' => $this->weights,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt?->format(DATE_ATOM),
            'updated_at' => $this->updatedAt?->format(DATE_ATOM),
            'deleted_at' => $this->deletedAt?->format(DATE_ATOM),
        ];
    }

    private function assertValid(): void
    {
        if ($this->questionId <= 0) {
            throw new InvalidArgumentException('questionId must be greater than 0.');
        }

        if (trim($this->code) === '') {
            throw new InvalidArgumentException('code must not be empty.');
        }

        if (trim($this->text) === '') {
            throw new InvalidArgumentException('text must not be empty.');
        }

        if ($this->sortOrder < 0) {
            throw new InvalidArgumentException('sortOrder must not be negative.');
        }

        foreach ($this->weights as $trait => $weight) {
            if (!in_array($trait, self::ALLOWED_TRAIT_KEYS, true)) {
                throw new InvalidArgumentException(sprintf('Invalid trait key "%s".', (string) $trait));
            }

            if (!is_int($weight)) {
                throw new InvalidArgumentException(sprintf('Weight for trait "%s" must be an integer.', $trait));
            }

            if ($weight < -14 || $weight > 14) {
                throw new InvalidArgumentException(sprintf('Weight for trait "%s" must be between -14 and 14.', $trait));
            }
        }
    }
}
