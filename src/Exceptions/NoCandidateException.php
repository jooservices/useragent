<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Exceptions;

/**
 * Exception thrown when no candidates match filter criteria.
 */
final class NoCandidateException extends UserAgentException
{
    public static function fromFilters(string $reason): self
    {
        return new self(sprintf('No user-agent candidates found: %s', $reason));
    }

    public static function afterMaxAttempts(int $attempts): self
    {
        return new self(sprintf('No candidates found after %d attempts', $attempts));
    }
}
