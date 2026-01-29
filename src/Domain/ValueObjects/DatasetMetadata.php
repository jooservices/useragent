<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\ValueObjects;

use DateTimeImmutable;

/**
 * Dataset metadata value object.
 */
final readonly class DatasetMetadata
{
    public function __construct(
        public string $name,
        public string $version,
        public DateTimeImmutable $updatedAt,
        public string $license,
        public ?string $source = null,
    ) {
    }

    /**
     * Create from array.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $name = isset($data['name']) && is_string($data['name']) ? $data['name'] : 'unknown';
        $version = isset($data['version']) && is_string($data['version']) ? $data['version'] : '1.0.0';
        $license = isset($data['license']) && is_string($data['license']) ? $data['license'] : 'unknown';
        $source = isset($data['source']) && is_string($data['source']) ? $data['source'] : null;

        $updatedAt = isset($data['updated_at']) && is_string($data['updated_at'])
            ? new DateTimeImmutable($data['updated_at'])
            : new DateTimeImmutable();

        return new self($name, $version, $updatedAt, $license, $source);
    }
}
