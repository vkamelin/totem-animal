<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testItPasses(): void
    {
        $value = random_int(0, 1);

        self::assertGreaterThanOrEqual(0, $value);
    }
}
