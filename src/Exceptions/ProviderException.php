<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Exceptions;

/**
 * Exception thrown by providers.
 */
final class ProviderException extends UserAgentException
{
    public static function fileNotFound(string $file): self
    {
        return new self(sprintf('File not found: %s', $file));
    }

    public static function loadFailed(string $source, string $reason): self
    {
        return new self(sprintf('Failed to load from %s: %s', $source, $reason));
    }

    public static function unsupported(string $providerClass): self
    {
        return new self(sprintf('Provider %s does not support current configuration', $providerClass));
    }
}
