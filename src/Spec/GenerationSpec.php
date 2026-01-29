<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Spec;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;

/**
 * Immutable specification for user-agent generation.
 *
 * Use builder pattern for construction:
 * GenerationSpec::create()->browser(BrowserFamily::Chrome)->device(DeviceType::Desktop)
 */
final readonly class GenerationSpec
{
    /**
     * @param array<string>        $tags
     * @param array<string, mixed> $weights
     */
    public function __construct(
        public ?BrowserFamily $browser = null,
        public ?Engine $engine = null,
        public ?OperatingSystem $os = null,
        public ?DeviceType $device = null,
        public ?int $versionMin = null,
        public ?int $versionMax = null,
        public ?int $versionExact = null,
        public ?string $channel = null,      // stable, beta, dev, canary
        public ?string $locale = null,
        public ?string $arch = null,
        public array $tags = [],
        public ?RiskLevel $riskLevel = null,
        public array $weights = [],
        public ?RandomSpec $randomSpec = null,
        public ?string $strategy = null,
    ) {
    }

    /**
     * Start building a spec.
     */
    public static function create(): GenerationSpecBuilder
    {
        return new GenerationSpecBuilder();
    }

    /**
     * Create from array (DSL support).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            browser: isset($data['browser']) ? BrowserFamily::from($data['browser']) : null,
            engine: isset($data['engine']) ? Engine::from($data['engine']) : null,
            os: isset($data['os']) ? OperatingSystem::from($data['os']) : null,
            device: isset($data['device']) ? DeviceType::from($data['device']) : null,
            versionMin: $data['versionMin'] ?? null,
            versionMax: $data['versionMax'] ?? null,
            versionExact: $data['versionExact'] ?? null,
            channel: $data['channel'] ?? null,
            locale: $data['locale'] ?? null,
            arch: $data['arch'] ?? null,
            tags: $data['tags'] ?? [],
            riskLevel: isset($data['riskLevel']) ? RiskLevel::from($data['riskLevel']) : null,
            weights: $data['weights'] ?? [],
            randomSpec: isset($data['randomSpec']) ? RandomSpec::fromArray($data['randomSpec']) : null,
            strategy: $data['strategy'] ?? null,
        );
    }

    /**
     * Check if spec has any constraints.
     */
    public function isEmpty(): bool
    {
        return $this->browser === null
            && $this->engine === null
            && $this->os === null
            && $this->device === null
            && $this->versionMin === null
            && $this->versionMax === null
            && $this->versionExact === null
            && $this->riskLevel === null
            && empty($this->tags);
    }
}
