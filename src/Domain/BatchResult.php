<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

use JOOservices\Dto\Core\Dto;

final class BatchResult extends Dto
{
    /** @param list<GenerationResult> $entries */
    public function __construct(
        public readonly array $entries,
        public readonly int $requestedCount,
        public readonly int $producedCount,
        public readonly int $attemptCount,
        public readonly UniquePolicy $uniquePolicy,
    ) {
    }

    /** @return list<string> */
    public function userAgents(): array
    {
        return array_map(static fn(GenerationResult $entry): string => $entry->userAgent, $this->entries);
    }
}
