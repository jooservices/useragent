<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use DateTimeImmutable;
use JOOservices\UserAgent\Dataset\Dataset;
use JOOservices\UserAgent\Domain\CompatibilityTuple;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Exceptions\IncompatibleRequestException;

final class CompatibilityResolver
{
    /** @return non-empty-list<array{browser: string, device: string, platform: string, weight: int|float}> */
    public function filter(Dataset $dataset, GenerationRequest $request): array
    {
        $matches = array_values(array_filter(
            $dataset->compatibility,
            fn(array $row): bool => $this->matchesTuple($dataset, $row, $request),
        ));
        if ($matches === []) {
            throw IncompatibleRequestException::forRequest(
                $this->requested($request),
                'No compatible browser profile matches the request.',
                $this->alternatives($dataset, $request),
                $this->availableVersions($dataset, $request),
            );
        }

        return $matches;
    }

    /**
     * @param array{browser: string, device: string, platform: string, weight: int|float} $row
     */
    private function matchesTuple(Dataset $dataset, array $row, GenerationRequest $request): bool
    {
        if ($request->browser !== null && $row['browser'] !== $request->browser->value) {
            return false;
        }
        if ($request->device !== null && $row['device'] !== $request->device->value) {
            return false;
        }
        if ($request->platform !== null && $row['platform'] !== $request->platform->value) {
            return false;
        }

        return $this->matchesProfileFilters($dataset, $row, $request);
    }

    /**
     * @param array{browser: string, device: string, platform: string, weight: int|float} $row
     */
    private function matchesProfileFilters(Dataset $dataset, array $row, GenerationRequest $request): bool
    {
        if (!$this->hasMatchingVersion($dataset, $row['browser'], $request)) {
            return false;
        }
        if ($request->architecture === null) {
            return true;
        }
        $architectures = $dataset->platforms[$row['platform']][$row['device']]['architectures'];

        return array_any($architectures, static fn(array $arch): bool => $arch['arch'] === $request->architecture->value);
    }

    private function hasMatchingVersion(Dataset $dataset, string $browser, GenerationRequest $request): bool
    {
        foreach ($dataset->browsers[$browser]['versions'] as $version) {
            if ($this->versionMatches($dataset, $version, $request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{major: int, full: string, releasedAt: string, weight: int|float} $version
     */
    private function versionMatches(Dataset $dataset, array $version, GenerationRequest $request): bool
    {
        if ($request->versionExact !== null && $version['major'] !== $request->versionExact) {
            return false;
        }

        return $this->versionMatchesOtherFilters($dataset, $version, $request);
    }

    /**
     * @param array{major: int, full: string, releasedAt: string, weight: int|float} $version
     */
    private function versionMatchesOtherFilters(Dataset $dataset, array $version, GenerationRequest $request): bool
    {
        if ($request->versionMin !== null && $version['major'] < $request->versionMin) {
            return false;
        }
        if ($request->versionMax !== null && $version['major'] > $request->versionMax) {
            return false;
        }
        if ($request->releasedWithinMonths !== null) {
            $threshold = (new DateTimeImmutable($dataset->provenance->effectiveAt))
                ->modify(sprintf('-%d months', $request->releasedWithinMonths));

            return new DateTimeImmutable($version['releasedAt']) >= $threshold;
        }

        return true;
    }

    /** @return list<CompatibilityTuple> */
    public function matrix(Dataset $dataset, ?GenerationRequest $filter = null): array
    {
        $rows = $filter === null ? $dataset->compatibility : $this->filter($dataset, $filter);

        return array_map(
            static fn(array $row): CompatibilityTuple => new CompatibilityTuple(
                browser: \JOOservices\UserAgent\Domain\BrowserFamily::from($row['browser']),
                device: \JOOservices\UserAgent\Domain\DeviceClass::from($row['device']),
                platform: \JOOservices\UserAgent\Domain\Platform::from($row['platform']),
            ),
            $rows,
        );
    }

    /** @return array<string, int|string|null> */
    private function requested(GenerationRequest $request): array
    {
        return [
            'browser' => $request->browser?->value,
            'device' => $request->device?->value,
            'platform' => $request->platform?->value,
            'architecture' => $request->architecture?->value,
            'versionExact' => $request->versionExact,
            'versionMin' => $request->versionMin,
            'versionMax' => $request->versionMax,
            'releasedWithinMonths' => $request->releasedWithinMonths,
        ];
    }

    /** @return list<array{browser: string, device: string, platform: string}> */
    private function alternatives(Dataset $dataset, GenerationRequest $request): array
    {
        $scored = [];
        foreach ($dataset->compatibility as $index => $row) {
            if (!$this->matchesProfileFilters($dataset, $row, $request)) {
                continue;
            }
            $scored[] = [
                'score' => (int) ($request->browser?->value === $row['browser'])
                    + (int) ($request->device?->value === $row['device'])
                    + (int) ($request->platform?->value === $row['platform']),
                'index' => $index,
                'row' => [
                    'browser' => $row['browser'],
                    'device' => $row['device'],
                    'platform' => $row['platform'],
                ],
            ];
        }
        usort($scored, static fn(array $left, array $right): int => ($right['score'] <=> $left['score']) !== 0
            ? $right['score'] <=> $left['score']
            : $left['index'] <=> $right['index']);

        return array_column(array_slice($scored, 0, 5), 'row');
    }

    /** @return array<string, list<int>> */
    private function availableVersions(Dataset $dataset, GenerationRequest $request): array
    {
        if (
            $request->versionExact === null
            && $request->versionMin === null
            && $request->versionMax === null
            && $request->releasedWithinMonths === null
        ) {
            return [];
        }
        $browsers = $request->browser === null
            ? $dataset->browsers
            : [$request->browser->value => $dataset->browsers[$request->browser->value]];
        $available = [];
        foreach ($browsers as $browser => $config) {
            $versions = [];
            foreach ($config['versions'] as $version) {
                if ($this->versionMatchesOtherFilters($dataset, $version, $request)) {
                    $versions[] = $version['major'];
                }
            }
            if ($versions !== []) {
                $available[$browser] = array_values(array_unique($versions));
            }
        }

        return $available;
    }
}
