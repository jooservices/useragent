<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Dataset;

use JOOservices\UserAgent\Domain\DatasetProvenance;

final readonly class Dataset
{
    /**
     * @param non-empty-list<array{browser: string, device: string, platform: string, weight: int|float}> $compatibility
     * @param array<string, array{engine: string, versions: non-empty-list<array{major: int, full: string, releasedAt: string, weight: int|float}>}> $browsers
     * @param array<string, array<string, array{osVersions: non-empty-list<array{id: string, uaVersion: string, token: string, weight: int|float}>, architectures: non-empty-list<array{id: string, arch: string, token: string, weight: int|float}>, deviceToken?: string}>> $platforms
     * @param array<string, non-empty-list<array{id: string, token: string, weight: int|float}>> $models
     * @param array<string, string> $templates
     */
    public function __construct(
        public DatasetProvenance $provenance,
        public array $compatibility,
        public array $browsers,
        public array $platforms,
        public array $models,
        public array $templates,
    ) {
    }
}
