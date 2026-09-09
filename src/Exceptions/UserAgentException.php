<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Exceptions;

abstract class UserAgentException extends \RuntimeException
{
    /** @return array{error: string, message: string} */
    public function toArray(): array
    {
        return ['error' => static::class, 'message' => $this->getMessage()];
    }
}
