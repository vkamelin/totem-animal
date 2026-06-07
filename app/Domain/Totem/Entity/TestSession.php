<?php

declare(strict_types=1);

namespace App\Domain\Totem\Entity;

use DateTimeImmutable;
use InvalidArgumentException;

final class TestSession
{
    private const array ALLOWED_STATUSES = [
        'started',
        'completed',
        'abandoned',
    ];

    public function __construct(
        private string $publicId,
        private ?int $id = null,
        private ?int $clientId = null,
        private string $status = 'started',
        private int $questionsCount = 0,
        private int $answersCount = 0,
        private ?DateTimeImmutable $startedAt = null,
        private ?DateTimeImmutable $completedAt = null,
        private ?DateTimeImmutable $lastActivityAt = null,
        private ?string $clientIpHash = null,
        private ?string $userAgentHash = null,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $updatedAt = null,
    ) {
        $this->assertValidStatus($this->status);
        $this->assertNonNegative($this->questionsCount, 'questionsCount');
        $this->assertNonNegative($this->answersCount, 'answersCount');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getQuestionsCount(): int
    {
        return $this->questionsCount;
    }

    public function getAnswersCount(): int
    {
        return $this->answersCount;
    }

    public function getStartedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getLastActivityAt(): ?DateTimeImmutable
    {
        return $this->lastActivityAt;
    }

    public function getClientIpHash(): ?string
    {
        return $this->clientIpHash;
    }

    public function getUserAgentHash(): ?string
    {
        return $this->userAgentHash;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function markStarted(?DateTimeImmutable $date = null): void
    {
        $date ??= new DateTimeImmutable();

        $this->status = 'started';
        $this->startedAt ??= $date;
        $this->lastActivityAt = $date;
    }

    public function markCompleted(?DateTimeImmutable $date = null): void
    {
        $date ??= new DateTimeImmutable();

        $this->status = 'completed';
        $this->completedAt = $date;
        $this->lastActivityAt = $date;
    }

    public function markAbandoned(?DateTimeImmutable $date = null): void
    {
        $date ??= new DateTimeImmutable();

        $this->status = 'abandoned';
        $this->lastActivityAt = $date;
    }

    public function touchActivity(?DateTimeImmutable $date = null): void
    {
        $this->lastActivityAt = $date ?? new DateTimeImmutable();
    }

    public function setQuestionsCount(int $questionsCount): void
    {
        $this->assertNonNegative($questionsCount, 'questionsCount');
        $this->questionsCount = $questionsCount;
    }

    public function setAnswersCount(int $answersCount): void
    {
        $this->assertNonNegative($answersCount, 'answersCount');
        $this->answersCount = $answersCount;
    }

    public function incrementAnswersCount(): void
    {
        $this->answersCount++;
    }

    public function isStarted(): bool
    {
        return $this->status === 'started';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isAbandoned(): bool
    {
        return $this->status === 'abandoned';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->publicId,
            'client_id' => $this->clientId,
            'status' => $this->status,
            'questions_count' => $this->questionsCount,
            'answers_count' => $this->answersCount,
            'started_at' => $this->startedAt?->format(DATE_ATOM),
            'completed_at' => $this->completedAt?->format(DATE_ATOM),
            'last_activity_at' => $this->lastActivityAt?->format(DATE_ATOM),
            'client_ip_hash' => $this->clientIpHash,
            'user_agent_hash' => $this->userAgentHash,
            'created_at' => $this->createdAt?->format(DATE_ATOM),
            'updated_at' => $this->updatedAt?->format(DATE_ATOM),
        ];
    }

    private function assertValidStatus(string $status): void
    {
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException(sprintf('Invalid status "%s".', $status));
        }
    }

    private function assertNonNegative(int $value, string $field): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(sprintf('%s must not be negative.', $field));
        }
    }
}
