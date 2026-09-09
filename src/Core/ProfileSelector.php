<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use DateTimeImmutable;
use JOOservices\UserAgent\Dataset\Dataset;
use JOOservices\UserAgent\Domain\Architecture;
use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\Engine;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Domain\UserAgentProfile;
use JOOservices\UserAgent\Exceptions\IncompatibleRequestException;

final readonly class ProfileSelector
{
    public function __construct(private SelectionPolicies $policies = new SelectionPolicies())
    {
    }

    /** @param non-empty-list<array{browser: string, device: string, platform: string, weight: int|float}> $tuples */
    public function select(Dataset $dataset, GenerationRequest $request, GenerationSession $session, array $tuples): UserAgentProfile
    {
        $index = $this->policies->for($request->selection)->selectIndex($tuples, $session->random);
        $tuple = $tuples[$index];
        $browser = $dataset->browsers[$tuple['browser']];
        $versions = $this->versions($browser['versions'], $request, $dataset);
        $version = $this->weighted($versions, $session);
        $platform = $dataset->platforms[$tuple['platform']][$tuple['device']];
        $operatingSystem = $this->weighted($platform['osVersions'], $session);
        $architectures = $platform['architectures'];
        if ($request->architecture !== null) {
            $architectures = array_values(array_filter(
                $architectures,
                static fn(array $row): bool => $row['arch'] === $request->architecture->value,
            ));
        }
        if ($architectures === []) {
            throw $this->incompatible($request, 'The requested architecture is not available.');
        }
        $architecture = $this->weighted($architectures, $session);
        $model = null;
        $templateKey = implode('|', [$tuple['browser'], $tuple['device'], $tuple['platform']]);
        if (str_contains($dataset->templates[$templateKey], '{model}') && isset($dataset->models[$tuple['platform']])) {
            $model = $this->weighted($dataset->models[$tuple['platform']], $session)['token'];
        }

        return new UserAgentProfile(
            browser: BrowserFamily::from($tuple['browser']),
            device: DeviceClass::from($tuple['device']),
            platform: Platform::from($tuple['platform']),
            engine: Engine::from($browser['engine']),
            browserVersion: $version['full'],
            browserMajor: $version['major'],
            osVersion: $operatingSystem['uaVersion'],
            osToken: $operatingSystem['token'],
            architecture: Architecture::from($architecture['arch']),
            archToken: $architecture['token'],
            model: $model,
            deviceToken: $platform['deviceToken'] ?? null,
            locale: $request->locale,
            localeRendered: false,
            releasedAt: $version['releasedAt'],
            provenance: $dataset->provenance,
        );
    }

    /**
     * @param list<array{major: int, full: string, releasedAt: string, weight: int|float}> $versions
     * @return non-empty-list<array{major: int, full: string, releasedAt: string, weight: int|float}>
     */
    private function versions(array $versions, GenerationRequest $request, Dataset $dataset): array
    {
        $filtered = array_values(array_filter($versions, function (array $version) use ($request, $dataset): bool {
            if ($request->versionExact !== null && $version['major'] !== $request->versionExact) {
                return false;
            }
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
        }));
        if ($filtered === []) {
            throw $this->incompatible($request, 'No browser version matches the requested version policy.');
        }

        return $filtered;
    }

    /**
     * @template T of array{weight: int|float}
     * @param non-empty-list<T> $rows
     * @return T
     */
    private function weighted(array $rows, GenerationSession $session): array
    {
        $weights = array_map(static fn(array $row): int | float => $row['weight'], $rows);
        /** @var T $selected */
        $selected = $session->random->weighted($rows, $weights);

        return $selected;
    }

    private function incompatible(GenerationRequest $request, string $reason): IncompatibleRequestException
    {
        return IncompatibleRequestException::forTuple([
            'browser' => $request->browser?->value,
            'device' => $request->device?->value,
            'platform' => $request->platform?->value,
        ], $reason, []);
    }
}
