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
            function (array $row) use ($dataset, $request): bool {
                if ($request->browser !== null && $row['browser'] !== $request->browser->value) {
                    return false;
                }
                if ($request->device !== null && $row['device'] !== $request->device->value) {
                    return false;
                }
                if ($request->platform !== null && $row['platform'] !== $request->platform->value) {
                    return false;
                }
                if (!$this->hasMatchingVersion($dataset, $row['browser'], $request)) {
                    return false;
                }
                if ($request->architecture === null) {
                    return true;
                }
                $architectures = $dataset->platforms[$row['platform']][$row['device']]['architectures'];

                return array_any($architectures, static fn(array $arch): bool => $arch['arch'] === $request->architecture->value);
            },
        ));
        if ($matches === []) {
            throw IncompatibleRequestException::forTuple(
                $this->requested($request),
                'No compatible browser profile matches the request.',
                $this->alternatives($dataset, $request),
            );
        }

        return $matches;
    }

    private function hasMatchingVersion(Dataset $dataset, string $browser, GenerationRequest $request): bool
    {
        foreach ($dataset->browsers[$browser]['versions'] as $version) {
            if ($request->versionExact !== null && $version['major'] !== $request->versionExact) {
                continue;
            }
            if ($request->versionMin !== null && $version['major'] < $request->versionMin) {
                continue;
            }
            if ($request->versionMax !== null && $version['major'] > $request->versionMax) {
                continue;
            }
            if ($request->releasedWithinMonths !== null) {
                $threshold = (new DateTimeImmutable($dataset->provenance->effectiveAt))
                    ->modify(sprintf('-%d months', $request->releasedWithinMonths));
                if (new DateTimeImmutable($version['releasedAt']) < $threshold) {
                    continue;
                }
            }

            return true;
        }

        return false;
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

    /** @return array{browser: ?string, device: ?string, platform: ?string} */
    private function requested(GenerationRequest $request): array
    {
        return [
            'browser' => $request->browser?->value,
            'device' => $request->device?->value,
            'platform' => $request->platform?->value,
        ];
    }

    /** @return list<array{browser: string, device: string, platform: string}> */
    private function alternatives(Dataset $dataset, GenerationRequest $request): array
    {
        $scored = $dataset->compatibility;
        usort($scored, static function (array $left, array $right) use ($request): int {
            $score = static fn(array $row): int
                => (int) ($request->browser?->value === $row['browser'])
                + (int) ($request->device?->value === $row['device'])
                + (int) ($request->platform?->value === $row['platform']);

            return $score($right) <=> $score($left);
        });

        return array_map(
            static fn(array $row): array => [
                'browser' => $row['browser'],
                'device' => $row['device'],
                'platform' => $row['platform'],
            ],
            array_slice($scored, 0, 5),
        );
    }
}
