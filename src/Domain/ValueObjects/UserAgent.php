<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * User-agent value object with optional metadata.
 */
final readonly class UserAgent
{
    public function __construct(
        public string $uaString,
        public ?Meta $meta = null,
    ) {
        if (trim($uaString) === '') {
            throw new InvalidArgumentException('User-agent string cannot be empty');
        }
    }

    /**
     * Create from UA string only.
     */
    public static function fromString(string $uaString): self
    {
        return new self($uaString);
    }

    /**
     * Create with metadata.
     */
    public static function withMeta(string $uaString, Meta $meta): self
    {
        return new self($uaString, $meta);
    }

    /**
     * Get UA string representation.
     */
    public function toString(): string
    {
        return $this->uaString;
    }

    /**
     * Check if has metadata.
     */
    public function hasMeta(): bool
    {
        return $this->meta !== null;
    }
}
