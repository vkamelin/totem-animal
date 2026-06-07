<?php

declare(strict_types=1);

namespace App\Domain\Totem\Entity;

use function array_key_exists;
use function filter_var;

final class Animal
{
    private function __construct(
        private int $id,
        private string $code,
        private string $name,
        private ?string $title,
        private string $description,
        private string $imagePath,
        private int $extraversion,
        private int $openness,
        private int $selfControl,
        private int $agreeableness,
        private int $emotionalStability,
        private int $dominance,
        private int $adaptability,
        private bool $isActive,
        private int $sortOrder,
        private ?string $createdAt,
        private ?string $updatedAt,
        private ?string $deletedAt,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            self::intValue($data, 'id'),
            self::stringValue($data, 'code'),
            self::stringValue($data, 'name'),
            self::stringOrNull($data, 'title'),
            self::stringValue($data, 'description'),
            self::stringValue($data, 'image_path'),
            self::intValue($data, 'extraversion'),
            self::intValue($data, 'openness'),
            self::intValue($data, 'self_control'),
            self::intValue($data, 'agreeableness'),
            self::intValue($data, 'emotional_stability'),
            self::intValue($data, 'dominance'),
            self::intValue($data, 'adaptability'),
            self::boolValue($data, 'is_active'),
            self::intValue($data, 'sort_order'),
            self::stringOrNull($data, 'created_at'),
            self::stringOrNull($data, 'updated_at'),
            self::stringOrNull($data, 'deleted_at'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'image_path' => $this->imagePath,
            'extraversion' => $this->extraversion,
            'openness' => $this->openness,
            'self_control' => $this->selfControl,
            'agreeableness' => $this->agreeableness,
            'emotional_stability' => $this->emotionalStability,
            'dominance' => $this->dominance,
            'adaptability' => $this->adaptability,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'deleted_at' => $this->deletedAt,
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function getExtraversion(): int
    {
        return $this->extraversion;
    }

    public function getOpenness(): int
    {
        return $this->openness;
    }

    public function getSelfControl(): int
    {
        return $this->selfControl;
    }

    public function getAgreeableness(): int
    {
        return $this->agreeableness;
    }

    public function getEmotionalStability(): int
    {
        return $this->emotionalStability;
    }

    public function getDominance(): int
    {
        return $this->dominance;
    }

    public function getAdaptability(): int
    {
        return $this->adaptability;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    public function getTraits(): array
    {
        return [
            'extraversion' => $this->extraversion,
            'openness' => $this->openness,
            'self_control' => $this->selfControl,
            'agreeableness' => $this->agreeableness,
            'emotional_stability' => $this->emotionalStability,
            'dominance' => $this->dominance,
            'adaptability' => $this->adaptability,
        ];
    }

    private static function intValue(array $data, string $key): int
    {
        return (int) ($data[$key] ?? 0);
    }

    private static function stringValue(array $data, string $key): string
    {
        return (string) ($data[$key] ?? '');
    }

    private static function stringOrNull(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        return (string) $data[$key];
    }

    private static function boolValue(array $data, string $key): bool
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return false;
        }

        $value = filter_var($data[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $value ?? (bool) $data[$key];
    }
}
