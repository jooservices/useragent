<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Console;

use BackedEnum;
use JOOservices\UserAgent\Domain\Architecture;
use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Domain\SelectionPolicyId;
use JOOservices\UserAgent\Exceptions\ConsoleException;

final class InputMapper
{
    /** @param array<string, string|bool> $input */
    public function request(array $input): GenerationRequest
    {
        return new GenerationRequest(
            browser: $this->enum($input, 'browser', BrowserFamily::class),
            device: $this->enum($input, 'device', DeviceClass::class),
            platform: $this->platform($input),
            locale: $this->string($input, 'locale'),
            architecture: $this->enum($input, 'arch', Architecture::class),
            versionExact: $this->integer($input, 'version'),
            versionMin: $this->integer($input, 'version-min'),
            versionMax: $this->integer($input, 'version-max'),
            releasedWithinMonths: $this->integer($input, 'recent'),
            selection: $this->enum($input, 'selection', SelectionPolicyId::class) ?? SelectionPolicyId::Weighted,
            seed: $this->integer($input, 'seed'),
        );
    }

    /** @param array<string, string|bool> $input */
    public function count(array $input): int
    {
        $count = $this->integer($input, 'count') ?? 1;
        if ($count < 1 || $count > 100) {
            throw new ConsoleException('--count must be between 1 and 100.');
        }

        return $count;
    }

    /** @param array<string, string|bool> $input */
    public function format(array $input): string
    {
        $format = strtolower($this->string($input, 'format') ?? 'text');
        if (!in_array($format, ['text', 'json', 'ndjson'], true)) {
            throw new ConsoleException('--format must be text, json, or ndjson.');
        }

        return $format;
    }

    /** @param array<string, string|bool> $input */
    private function platform(array $input): ?Platform
    {
        $value = $this->string($input, 'os');
        if ($value === null) {
            return null;
        }
        $normalized = match (strtolower($value)) {
            'mac' => 'macos',
            'win' => 'windows',
            'cros' => 'chromeos',
            default => strtolower($value),
        };

        return Platform::tryFrom($normalized) ?? throw new ConsoleException("Unknown os: {$value}");
    }

    /**
     * @template T of BackedEnum
     * @param array<string, string|bool> $input
     * @param class-string<T> $enum
     * @return T|null
     */
    private function enum(array $input, string $key, string $enum): ?BackedEnum
    {
        $value = $this->string($input, $key);
        if ($value === null) {
            return null;
        }
        $case = $enum::tryFrom(strtolower($value));
        if ($case === null) {
            throw new ConsoleException("Unknown {$key}: {$value}");
        }

        return $case;
    }

    /** @param array<string, string|bool> $input */
    private function integer(array $input, string $key): ?int
    {
        $value = $this->string($input, $key);
        if ($value === null) {
            return null;
        }
        if (preg_match('/^[0-9]+$/D', $value) !== 1 || strlen($value) > 10) {
            throw new ConsoleException("--{$key} must be a non-negative integer.");
        }

        return (int) $value;
    }

    /** @param array<string, string|bool> $input */
    private function string(array $input, string $key): ?string
    {
        $value = $input[$key] ?? null;

        return is_string($value) ? $value : null;
    }
}
