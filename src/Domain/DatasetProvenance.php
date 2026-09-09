<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

use JOOservices\Dto\Core\Dto;

final class DatasetProvenance extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly int $schemaVersion,
        public readonly string $revision,
        public readonly string $license,
        public readonly string $terms,
        public readonly string $source,
        public readonly string $fetchedAt,
        public readonly string $effectiveAt,
        public readonly string $checksumSha256,
    ) {
    }
}
