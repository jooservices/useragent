<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

use JOOservices\Dto\Core\Dto;

final class GenerationResult extends Dto
{
    public function __construct(
        public readonly string $userAgent,
        public readonly UserAgentProfile $profile,
        public readonly DatasetProvenance $provenance,
        public readonly ?int $seed,
        public readonly SelectionPolicyId $selection,
        public readonly int $candidateCount,
    ) {
    }
}
