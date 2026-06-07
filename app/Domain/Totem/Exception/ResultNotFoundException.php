<?php

declare(strict_types=1);

namespace App\Domain\Totem\Exception;

use RuntimeException;

final class ResultNotFoundException extends RuntimeException
{
    public function __construct(
        string $message = 'Test result not found.',
        private readonly string $errorCode = 'RESULT_NOT_FOUND',
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
