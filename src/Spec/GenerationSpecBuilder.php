<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Spec;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;

/**
 * Fluent builder for GenerationSpec.
 */
final class GenerationSpecBuilder
{
    private ?BrowserFamily $browser = null;

    private ?Engine $engine = null;

    private ?OperatingSystem $os = null;

    private ?DeviceType $device = null;

    private ?int $versionMin = null;

    private ?int $versionMax = null;

    private ?int $versionExact = null;

    private ?string $channel = null;

    private ?string $locale = null;

    private ?string $arch = null;

    /** @var array<string> */
    private array $tags = [];

    private ?RiskLevel $riskLevel = null;

    /** @var array<string, mixed> */
    private array $weights = [];

    private ?RandomSpec $randomSpec = null;

    private ?string $strategy = null;

    public function browser(BrowserFamily $browser): self
    {
        $this->browser = $browser;

        return $this;
    }

    public function engine(Engine $engine): self
    {
        $this->engine = $engine;

        return $this;
    }

    public function os(OperatingSystem $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function device(DeviceType $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function versionMin(int $versionMin): self
    {
        $this->versionMin = $versionMin;

        return $this;
    }

    public function versionMax(int $versionMax): self
    {
        $this->versionMax = $versionMax;

        return $this;
    }

    public function versionExact(int $versionExact): self
    {
        $this->versionExact = $versionExact;

        return $this;
    }

    public function channel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

    public function locale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function arch(string $arch): self
    {
        $this->arch = $arch;

        return $this;
    }

    /**
     * @param array<string> $tags
     */
    public function tags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function riskLevel(RiskLevel $riskLevel): self
    {
        $this->riskLevel = $riskLevel;

        return $this;
    }

    /**
     * @param array<string, mixed> $weights
     */
    public function weights(array $weights): self
    {
        $this->weights = $weights;

        return $this;
    }

    public function randomSpec(RandomSpec $randomSpec): self
    {
        $this->randomSpec = $randomSpec;

        return $this;
    }

    public function strategy(string $strategyClass): self
    {
        $this->strategy = $strategyClass;

        return $this;
    }

    public function build(): GenerationSpec
    {
        return new GenerationSpec(
            browser: $this->browser,
            engine: $this->engine,
            os: $this->os,
            device: $this->device,
            versionMin: $this->versionMin,
            versionMax: $this->versionMax,
            versionExact: $this->versionExact,
            channel: $this->channel,
            locale: $this->locale,
            arch: $this->arch,
            tags: $this->tags,
            randomSpec: $this->randomSpec,
            strategy: $this->strategy,
        );
    }
}
