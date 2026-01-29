<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Exceptions;

/**
 * Exception thrown when dataset is invalid.
 */
final class InvalidDatasetException extends UserAgentException
{
    public static function invalidFormat(string $reason): self
    {
        return new self(sprintf('Invalid dataset format: %s', $reason));
    }

    public static function invalidJson(string $file, string $error): self
    {
        return new self(sprintf('Invalid JSON in file %s: %s', $file, $error));
    }

    public static function emptyDataset(): self
    {
        return new self('Dataset cannot be empty');
    }
}
