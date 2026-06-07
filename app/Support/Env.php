<?php

declare(strict_types=1);

namespace App\Support;

final class Env
{
    /**
     * @return string|false
     */
    private static function raw(string $key): string|false
    {
        if (array_key_exists($key, $_ENV) && is_string($_ENV[$key])) {
            return $_ENV[$key];
        }

        $value = getenv($key);

        if ($value === false) {
            return false;
        }

        return $value;
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = self::raw($key);

        if (!is_string($value) || $value === '') {
            return $default;
        }

        return $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::raw($key);

        if (!is_string($value) || $value === '') {
            return $default;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $filtered ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::raw($key);

        if (!is_string($value) || $value === '') {
            return $default;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

        return is_int($filtered) ? $filtered : $default;
    }
}
