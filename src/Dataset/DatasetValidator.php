<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Dataset;

use JOOservices\UserAgent\Domain\Architecture;
use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\DatasetProvenance;
use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\Engine;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Exceptions\InvalidDatasetException;

final class DatasetValidator
{
    /**
     * @param array<string, mixed> $manifest
     * @param array<string, mixed> $decoded
     */
    public function validate(array $manifest, array $decoded, string $calculatedChecksum): Dataset
    {
        $manifestKeys = ['schemaVersion', 'revision', 'name', 'license', 'terms', 'source', 'fetchedAt', 'effectiveAt', 'checksumSha256'];
        $this->exactKeys($manifest, $manifestKeys, 'manifest');
        if (($manifest['schemaVersion'] ?? null) !== Schema::VERSION) {
            throw new InvalidDatasetException('Unsupported dataset schema version.');
        }
        $checksum = $this->requiredString($manifest, 'checksumSha256');
        if (!hash_equals($checksum, $calculatedChecksum)) {
            throw new InvalidDatasetException('Dataset checksum mismatch.');
        }
        foreach (['revision', 'name', 'license', 'terms', 'source', 'fetchedAt', 'effectiveAt'] as $key) {
            if (!is_string($manifest[$key]) || $manifest[$key] === '') {
                throw new InvalidDatasetException("Manifest field {$key} must be a non-empty string.");
            }
        }
        $compatibility = $this->list($decoded['compatibility.json'] ?? null, 'compatibility');
        if ($compatibility === []) {
            throw new InvalidDatasetException('Compatibility cannot be empty.');
        }
        $browsers = $this->map($decoded['browsers.json'] ?? null, 'browsers');
        $platforms = $this->map($decoded['platforms.json'] ?? null, 'platforms');
        $models = $this->map($decoded['models.json'] ?? null, 'models');
        $templates = $this->stringMap($decoded['templates.json'] ?? null, 'templates');

        $seen = [];
        foreach ($compatibility as $index => $row) {
            if (!is_array($row)) {
                throw new InvalidDatasetException("Compatibility row {$index} must be an object.");
            }
            /** @var array<string, mixed> $row */
            $this->exactKeys($row, ['browser', 'device', 'platform', 'weight'], "compatibility row {$index}");
            $browser = $this->enumValue($row['browser'], BrowserFamily::class, 'browser');
            $device = $this->enumValue($row['device'], DeviceClass::class, 'device');
            $platform = $this->enumValue($row['platform'], Platform::class, 'platform');
            $this->positiveWeight($row['weight'] ?? null);
            $key = "{$browser}|{$device}|{$platform}";
            if (isset($seen[$key])) {
                throw new InvalidDatasetException("Duplicate compatibility tuple {$key}.");
            }
            $seen[$key] = true;
            if (!isset($templates[$key])) {
                throw new InvalidDatasetException("Missing template for {$key}.");
            }
            $platformDevices = $platforms[$platform] ?? null;
            if (!isset($browsers[$browser]) || !is_array($platformDevices) || !isset($platformDevices[$device])) {
                throw new InvalidDatasetException("Dangling dataset reference for {$key}.");
            }
            $platformConfig = $platformDevices[$device];
            if (
                str_contains($templates[$key], '{deviceToken}')
                && (!is_array($platformConfig) || !isset($platformConfig['deviceToken']))
            ) {
                throw new InvalidDatasetException("Template requires a device token for {$key}.");
            }
            $this->validateTemplate($templates[$key], $platform, $models);
        }
        $this->validateBrowsers($browsers);
        $this->validatePlatforms($platforms);
        $this->validateModels($models);

        $provenance = new DatasetProvenance(
            name: $this->requiredString($manifest, 'name'),
            schemaVersion: Schema::VERSION,
            revision: $this->requiredString($manifest, 'revision'),
            license: $this->requiredString($manifest, 'license'),
            terms: $this->requiredString($manifest, 'terms'),
            source: $this->requiredString($manifest, 'source'),
            fetchedAt: $this->requiredString($manifest, 'fetchedAt'),
            effectiveAt: $this->requiredString($manifest, 'effectiveAt'),
            checksumSha256: $checksum,
        );

        /** @var non-empty-list<array{browser: string, device: string, platform: string, weight: int|float}> $compatibility */
        /** @var array<string, array{engine: string, versions: non-empty-list<array{major: int, full: string, releasedAt: string, weight: int|float}>}> $browsers */
        /** @var array<string, array<string, array{osVersions: non-empty-list<array{id: string, uaVersion: string, token: string, weight: int|float}>, architectures: non-empty-list<array{id: string, arch: string, token: string, weight: int|float}>, deviceToken?: string}>> $platforms */
        /** @var array<string, non-empty-list<array{id: string, token: string, weight: int|float}>> $models */

        return new Dataset($provenance, $compatibility, $browsers, $platforms, $models, $templates);
    }

    /** @param array<string, mixed> $browsers */
    private function validateBrowsers(array $browsers): void
    {
        foreach ($browsers as $family => $browser) {
            if (BrowserFamily::tryFrom($family) === null || !is_array($browser)) {
                throw new InvalidDatasetException('Invalid browser entry.');
            }
            /** @var array<string, mixed> $browser */
            $this->exactKeys($browser, ['engine', 'versions'], "browser {$family}");
            if (!is_string($browser['engine']) || Engine::tryFrom($browser['engine']) === null) {
                throw new InvalidDatasetException("Invalid engine for {$family}.");
            }
            $versions = $this->list($browser['versions'], "versions for {$family}");
            if ($versions === []) {
                throw new InvalidDatasetException("Browser {$family} has no versions.");
            }
            foreach ($versions as $version) {
                if (!is_array($version)) {
                    throw new InvalidDatasetException('Invalid browser version row.');
                }
                /** @var array<string, mixed> $version */
                $this->exactKeys($version, ['major', 'full', 'releasedAt', 'weight'], 'browser version');
                if (!is_int($version['major']) || $version['major'] < 1 || !is_string($version['full']) || !is_string($version['releasedAt'])) {
                    throw new InvalidDatasetException('Invalid browser version values.');
                }
                $this->positiveWeight($version['weight']);
            }
        }
    }

    /** @param array<string, mixed> $platforms */
    private function validatePlatforms(array $platforms): void
    {
        foreach ($platforms as $platform => $devices) {
            if (Platform::tryFrom($platform) === null || !is_array($devices)) {
                throw new InvalidDatasetException('Invalid platform entry.');
            }
            foreach ($devices as $device => $bits) {
                if (DeviceClass::tryFrom($device) === null || !is_array($bits)) {
                    throw new InvalidDatasetException('Invalid platform device entry.');
                }
                /** @var array<string, mixed> $bits */
                $allowed = ['osVersions', 'architectures'];
                if (array_key_exists('deviceToken', $bits)) {
                    $allowed[] = 'deviceToken';
                    $this->safeToken($bits['deviceToken']);
                }
                $this->exactKeys($bits, $allowed, "platform {$platform}/{$device}");
                $osVersions = $this->list($bits['osVersions'] ?? null, 'osVersions');
                $architectures = $this->list($bits['architectures'] ?? null, 'architectures');
                if ($osVersions === [] || $architectures === []) {
                    throw new InvalidDatasetException('Platform selections cannot be empty.');
                }
                foreach ($osVersions as $row) {
                    if (!is_array($row)) {
                        throw new InvalidDatasetException('Invalid OS version.');
                    }
                    /** @var array<string, mixed> $row */
                    $this->exactKeys($row, ['id', 'uaVersion', 'token', 'weight'], 'OS version');
                    $this->safeToken($row['token'] ?? null);
                    $this->positiveWeight($row['weight'] ?? null);
                }
                foreach ($architectures as $row) {
                    if (!is_array($row)) {
                        throw new InvalidDatasetException('Invalid architecture.');
                    }
                    /** @var array<string, mixed> $row */
                    $this->exactKeys($row, ['id', 'arch', 'token', 'weight'], 'architecture');
                    if (!is_string($row['arch']) || Architecture::tryFrom($row['arch']) === null) {
                        throw new InvalidDatasetException('Unknown architecture.');
                    }
                    $this->safeToken($row['token'] ?? null);
                    $this->positiveWeight($row['weight'] ?? null);
                }
            }
        }
    }

    /** @param array<string, mixed> $models */
    private function validateModels(array $models): void
    {
        foreach ($models as $platform => $rows) {
            if (Platform::tryFrom($platform) === null) {
                throw new InvalidDatasetException('Unknown model platform.');
            }
            $modelRows = $this->list($rows, 'models');
            if ($modelRows === []) {
                throw new InvalidDatasetException('Models cannot be empty.');
            }
            foreach ($modelRows as $row) {
                if (!is_array($row)) {
                    throw new InvalidDatasetException('Invalid model row.');
                }
                /** @var array<string, mixed> $row */
                $this->exactKeys($row, ['id', 'token', 'weight'], 'model');
                $this->safeToken($row['token'] ?? null);
                $this->positiveWeight($row['weight'] ?? null);
            }
        }
    }

    /** @param array<string, mixed> $models */
    private function validateTemplate(string $template, string $platform, array $models): void
    {
        if ($template === '' || preg_match('/[\x00-\x1F\x7F]/', $template) === 1) {
            throw new InvalidDatasetException('Template contains unsafe characters.');
        }
        preg_match_all('/\{([A-Za-z][A-Za-z0-9]*)\}/', $template, $matches);
        foreach ($matches[1] as $placeholder) {
            if (!in_array($placeholder, Schema::PLACEHOLDERS, true)) {
                throw new InvalidDatasetException("Unknown template placeholder {$placeholder}.");
            }
        }
        if (!str_contains($template, '{fullVersion}') || !str_contains($template, '{osToken}')) {
            throw new InvalidDatasetException('Every template must render fullVersion and osToken.');
        }
        if (str_contains($template, '{model}') && !isset($models[$platform])) {
            throw new InvalidDatasetException("Template requires models for {$platform}.");
        }
    }

    /**
     * @param array<string, mixed> $value
     * @param list<string> $keys
     */
    private function exactKeys(array $value, array $keys, string $label): void
    {
        $actual = array_keys($value);
        sort($actual);
        sort($keys);
        if ($actual !== $keys) {
            throw new InvalidDatasetException("Unexpected keys in {$label}.");
        }
    }

    /** @return list<mixed> */
    private function list(mixed $value, string $label): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidDatasetException("{$label} must be a list.");
        }

        return $value;
    }

    /** @return array<string, mixed> */
    private function map(mixed $value, string $label): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidDatasetException("{$label} must be an object.");
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    /** @return array<string, string> */
    private function stringMap(mixed $value, string $label): array
    {
        $map = $this->map($value, $label);
        foreach ($map as $item) {
            if (!is_string($item)) {
                throw new InvalidDatasetException("{$label} values must be strings.");
            }
        }

        /** @var array<string, string> $map */
        return $map;
    }

    /** @param class-string<\BackedEnum> $enum */
    private function enumValue(mixed $value, string $enum, string $label): string
    {
        if (!is_string($value) || $enum::tryFrom($value) === null) {
            throw new InvalidDatasetException("Unknown {$label}.");
        }

        return $value;
    }

    private function positiveWeight(mixed $weight): void
    {
        if ((!is_int($weight) && !is_float($weight)) || $weight <= 0) {
            throw new InvalidDatasetException('Dataset weights must be positive numbers.');
        }
    }

    private function safeToken(mixed $token): void
    {
        if (!is_string($token) || $token === '' || strlen($token) > 128 || preg_match('/[\x00-\x1F\x7F{}]/', $token) === 1) {
            throw new InvalidDatasetException('Dataset token is invalid.');
        }
    }

    /** @param array<string, mixed> $values */
    private function requiredString(array $values, string $key): string
    {
        $value = $values[$key] ?? null;
        if (!is_string($value) || $value === '') {
            throw new InvalidDatasetException("Dataset field {$key} must be a non-empty string.");
        }

        return $value;
    }
}
