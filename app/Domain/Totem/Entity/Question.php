<?php

declare(strict_types=1);

namespace App\Domain\Totem\Entity;

use DateTimeImmutable;
use InvalidArgumentException;

final class Question
{
    /**
     * @param array<int, Answer> $answers
     */
    public function __construct(
        private ?int $id = null,
        private string $code = '',
        private string $text = '',
        private array $answers = [],
        private bool $isActive = true,
        private int $sortOrder = 0,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
        private ?DateTimeImmutable $deletedAt = null,
    ) {
        $this->setAnswers($answers);
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return array<int, Answer>
     */
    public function getAnswers(): array
    {
        return $this->answers;
    }

    /**
     * @return array<int, Answer>
     */
    public function getActiveAnswers(): array
    {
        return array_values(array_filter(
            $this->answers,
            static fn (Answer $answer): bool => $answer->isActive(),
        ));
    }

    public function hasAnswers(): bool
    {
        return $this->answers !== [];
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
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

    public function addAnswer(Answer $answer): void
    {
        $this->answers[] = $answer;
    }

    /**
     * @param array<int, Answer> $answers
     */
    public function setAnswers(array $answers): void
    {
        foreach ($answers as $answer) {
            if (!$answer instanceof Answer) {
                throw new InvalidArgumentException('Each answer must be an instance of ' . Answer::class . '.');
            }
        }

        $this->answers = array_values($answers);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'text' => $this->text,
            'answers' => array_map(
                static fn (Answer $answer): array => $answer->toArray(),
                $this->answers,
            ),
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt?->format(DATE_ATOM),
            'updated_at' => $this->updatedAt?->format(DATE_ATOM),
            'deleted_at' => $this->deletedAt?->format(DATE_ATOM),
        ];
    }
}
