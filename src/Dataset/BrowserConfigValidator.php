<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Dataset;

use DateTimeImmutable;
use DateTimeZone;
use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\Engine;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Exceptions\InvalidDatasetException;

final class BrowserConfigValidator
{
    /**
     * @param array<string, mixed> $browsers
     * @param list<array{browser: string, device: string, platform: string, weight: int|float}> $compatibility
     */
    public function validate(array $browsers, array $compatibility, DateTimeImmutable $effectiveAt): void
    {
        foreach ($browsers as $family => $browser) {
            if (BrowserFamily::tryFrom($family) === null || !is_array($browser)) {
                throw new InvalidDatasetException('Invalid browser entry.');
            }
            /** @var array<string, mixed> $browser */
            $this->validateBrowser($family, $browser, $compatibility, $effectiveAt);
        }
    }

    /**
     * @param array<string, mixed> $browser
     * @param list<array{browser: string, device: string, platform: string, weight: int|float}> $compatibility
     */
    private function validateBrowser(string $family, array $browser, array $compatibility, DateTimeImmutable $effectiveAt): void
    {
        $keys = ['engine', 'versions'];
        if (array_key_exists('engineOverrides', $browser)) {
            $keys[] = 'engineOverrides';
        }
        $this->exactKeys($browser, $keys, "browser {$family}");
        if (!is_string($browser['engine']) || Engine::tryFrom($browser['engine']) === null) {
            throw new InvalidDatasetException("Invalid engine for {$family}.");
        }
        if (isset($browser['engineOverrides'])) {
            $this->validateOverrides($family, $browser['engineOverrides'], $compatibility);
        }
        $this->validateVersions($family, $browser['versions'], $effectiveAt);
    }

    /**
     * @param list<array{browser: string, device: string, platform: string, weight: int|float}> $compatibility
     */
    private function validateOverrides(string $family, mixed $value, array $compatibility): void
    {
        foreach ($this->map($value, "engine overrides for {$family}") as $platform => $engine) {
            if (Platform::tryFrom($platform) === null || !is_string($engine) || Engine::tryFrom($engine) === null) {
                throw new InvalidDatasetException("Invalid engine override for {$family}.");
            }
            $hasTuple = array_any(
                $compatibility,
                static fn(array $row): bool => $row['browser'] === $family && $row['platform'] === $platform,
            );
            if (!$hasTuple) {
                throw new InvalidDatasetException("Engine override has no compatibility tuple for {$family}/{$platform}.");
            }
        }
    }

    private function validateVersions(string $family, mixed $value, DateTimeImmutable $effectiveAt): void
    {
        $versions = $this->list($value, "versions for {$family}");
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
            if ($this->date($version['releasedAt'], "releasedAt for {$family}") > $effectiveAt) {
                throw new InvalidDatasetException("Browser release date is after effectiveAt for {$family}.");
            }
            $this->positiveWeight($version['weight']);
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

    private function positiveWeight(mixed $weight): void
    {
        if ((!is_int($weight) && !is_float($weight)) || $weight <= 0) {
            throw new InvalidDatasetException('Dataset weights must be positive numbers.');
        }
    }

    private function date(string $value, string $label): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('UTC'));
        $errors = DateTimeImmutable::getLastErrors();
        if (
            $date === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d') !== $value
        ) {
            throw new InvalidDatasetException("Dataset date {$label} must use a valid YYYY-MM-DD date.");
        }

        return $date;
    }
}
