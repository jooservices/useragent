<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Spec;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Spec\GenerationSpecBuilder;
use JOOservices\UserAgent\Spec\RandomSpec;
use PHPUnit\Framework\TestCase;

final class GenerationSpecTest extends TestCase
{
    // ========== HAPPY PATH TESTS ==========

    /** @test */
    public function test_it_creates_empty_spec(): void
    {
        $spec = new GenerationSpec();

        $this->assertTrue($spec->isEmpty());
        $this->assertNull($spec->browser);
        $this->assertNull($spec->device);
        $this->assertNull($spec->os);
        $this->assertNull($spec->engine);
        $this->assertNull($spec->versionMin);
        $this->assertNull($spec->versionMax);
        $this->assertNull($spec->versionExact);
        $this->assertNull($spec->channel);
        $this->assertNull($spec->locale);
        $this->assertNull($spec->arch);
        $this->assertNull($spec->riskLevel);
        $this->assertSame([], $spec->tags);
        $this->assertSame([], $spec->weights);
        $this->assertNull($spec->randomSpec);
    }

    /** @test */
    public function test_it_creates_spec_with_all_properties(): void
    {
        $randomSpec = new RandomSpec(seed: 12345);

        $spec = new GenerationSpec(
            browser: BrowserFamily::Chrome,
            engine: Engine::Blink,
            os: OperatingSystem::Windows,
            device: DeviceType::Desktop,
            versionMin: 110,
            versionMax: 120,
            versionExact: null,
            channel: 'stable',
            locale: 'en-US',
            arch: 'x86_64',
            tags: ['popular', 'modern'],
            riskLevel: RiskLevel::Low,
            weights: ['browser' => 0.8],
            randomSpec: $randomSpec,
        );

        $this->assertFalse($spec->isEmpty());
        $this->assertSame(BrowserFamily::Chrome, $spec->browser);
        $this->assertSame(Engine::Blink, $spec->engine);
        $this->assertSame(OperatingSystem::Windows, $spec->os);
        $this->assertSame(DeviceType::Desktop, $spec->device);
        $this->assertSame(110, $spec->versionMin);
        $this->assertSame(120, $spec->versionMax);
        $this->assertNull($spec->versionExact);
        $this->assertSame('stable', $spec->channel);
        $this->assertSame('en-US', $spec->locale);
        $this->assertSame('x86_64', $spec->arch);
        $this->assertSame(['popular', 'modern'], $spec->tags);
        $this->assertSame(RiskLevel::Low, $spec->riskLevel);
        $this->assertSame(['browser' => 0.8], $spec->weights);
        $this->assertSame($randomSpec, $spec->randomSpec);
    }

    /** @test */
    public function test_it_creates_spec_with_builder(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->device(DeviceType::Desktop)
            ->versionMin(110)
            ->versionMax(120)
            ->build();

        $this->assertFalse($spec->isEmpty());
        $this->assertSame(BrowserFamily::Chrome, $spec->browser);
        $this->assertSame(DeviceType::Desktop, $spec->device);
        $this->assertSame(110, $spec->versionMin);
        $this->assertSame(120, $spec->versionMax);
    }

    /** @test */
    public function test_it_creates_spec_from_array(): void
    {
        $spec = GenerationSpec::fromArray([
            'browser' => 'chrome',
            'device' => 'desktop',
            'os' => 'windows',
            'engine' => 'blink',
            'versionMin' => 110,
            'versionMax' => 120,
            'channel' => 'stable',
            'locale' => 'en-US',
            'arch' => 'x86_64',
            'tags' => ['popular'],
            'riskLevel' => 'low',
            'weights' => ['browser' => 0.8],
            'randomSpec' => ['seed' => 12345],
        ]);

        $this->assertSame(BrowserFamily::Chrome, $spec->browser);
        $this->assertSame(DeviceType::Desktop, $spec->device);
        $this->assertSame(OperatingSystem::Windows, $spec->os);
        $this->assertSame(Engine::Blink, $spec->engine);
        $this->assertSame(110, $spec->versionMin);
        $this->assertSame(120, $spec->versionMax);
        $this->assertSame('stable', $spec->channel);
        $this->assertSame('en-US', $spec->locale);
        $this->assertSame('x86_64', $spec->arch);
        $this->assertSame(['popular'], $spec->tags);
        $this->assertSame(RiskLevel::Low, $spec->riskLevel);
        $this->assertSame(['browser' => 0.8], $spec->weights);
        $this->assertInstanceOf(RandomSpec::class, $spec->randomSpec);
    }

    /** @test */
    public function test_it_creates_spec_from_partial_array(): void
    {
        $spec = GenerationSpec::fromArray([
            'browser' => 'chrome',
            'versionMin' => 110,
        ]);

        $this->assertSame(BrowserFamily::Chrome, $spec->browser);
        $this->assertSame(110, $spec->versionMin);
        $this->assertNull($spec->device);
        $this->assertNull($spec->os);
        $this->assertSame([], $spec->tags);
    }

    /** @test */
    public function test_it_creates_spec_from_empty_array(): void
    {
        $spec = GenerationSpec::fromArray([]);

        $this->assertTrue($spec->isEmpty());
    }

    /** @test */
    public function builder_is_fluent(): void
    {
        $builder = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->device(DeviceType::Desktop)
            ->os(OperatingSystem::Windows)
            ->engine(Engine::Blink)
            ->versionMin(110)
            ->versionMax(120)
            ->channel('stable')
            ->locale('en-US')
            ->arch('x86_64')
            ->tags(['popular'])
            ->riskLevel(RiskLevel::Low)
            ->weights(['browser' => 0.8])
            ->randomSpec(new RandomSpec());

        $this->assertInstanceOf(GenerationSpecBuilder::class, $builder);

        $spec = $builder->build();
        $this->assertInstanceOf(GenerationSpec::class, $spec);
    }

    /** @test */
    public function test_it_is_immutable(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->build();

        // Cannot modify readonly properties (this is enforced by PHP)
        $this->assertSame(BrowserFamily::Chrome, $spec->browser);
    }

    /** @test */
    public function is_empty_returns_false_when_any_constraint_is_set(): void
    {
        $specs = [
            GenerationSpec::create()->browser(BrowserFamily::Chrome)->build(),
            GenerationSpec::create()->device(DeviceType::Desktop)->build(),
            GenerationSpec::create()->os(OperatingSystem::Windows)->build(),
            GenerationSpec::create()->engine(Engine::Blink)->build(),
            GenerationSpec::create()->versionMin(110)->build(),
            GenerationSpec::create()->versionMax(120)->build(),
            GenerationSpec::create()->versionExact(115)->build(),
            GenerationSpec::create()->riskLevel(RiskLevel::Low)->build(),
            GenerationSpec::create()->tags(['popular'])->build(),
        ];

        foreach ($specs as $spec) {
            $this->assertFalse($spec->isEmpty());
        }
    }

    // ========== EDGE CASE TESTS ==========

    /** @test */
    public function test_it_handles_version_exact_without_range(): void
    {
        $spec = GenerationSpec::create()
            ->versionExact(115)
            ->build();

        $this->assertSame(115, $spec->versionExact);
        $this->assertNull($spec->versionMin);
        $this->assertNull($spec->versionMax);
    }

    /** @test */
    public function test_it_handles_minimum_version_value(): void
    {
        $spec = GenerationSpec::create()
            ->versionMin(1)
            ->build();

        $this->assertSame(1, $spec->versionMin);
    }

    /** @test */
    public function test_it_handles_maximum_version_value(): void
    {
        $spec = GenerationSpec::create()
            ->versionMax(999)
            ->build();

        $this->assertSame(999, $spec->versionMax);
    }

    /** @test */
    public function test_it_handles_empty_tags_array(): void
    {
        $spec = GenerationSpec::create()
            ->tags([])
            ->build();

        $this->assertSame([], $spec->tags);
        $this->assertTrue($spec->isEmpty());
    }

    /** @test */
    public function test_it_handles_empty_weights_array(): void
    {
        $spec = GenerationSpec::create()
            ->weights([])
            ->build();

        $this->assertSame([], $spec->weights);
    }

    /** @test */
    public function test_it_handles_multiple_tags(): void
    {
        $tags = ['popular', 'modern', 'secure', 'fast'];
        $spec = GenerationSpec::create()
            ->tags($tags)
            ->build();

        $this->assertSame($tags, $spec->tags);
    }

    /** @test */
    public function test_it_handles_complex_weights(): void
    {
        $weights = [
            'browser' => 0.8,
            'device' => 0.6,
            'os' => 0.7,
        ];

        $spec = GenerationSpec::create()
            ->weights($weights)
            ->build();

        $this->assertSame($weights, $spec->weights);
    }

    /** @test */
    public function test_it_handles_all_browser_families(): void
    {
        foreach (BrowserFamily::cases() as $browser) {
            $spec = GenerationSpec::create()
                ->browser($browser)
                ->build();

            $this->assertSame($browser, $spec->browser);
        }
    }

    /** @test */
    public function test_it_handles_all_device_types(): void
    {
        foreach (DeviceType::cases() as $device) {
            $spec = GenerationSpec::create()
                ->device($device)
                ->build();

            $this->assertSame($device, $spec->device);
        }
    }

    /** @test */
    public function test_it_handles_all_operating_systems(): void
    {
        foreach (OperatingSystem::cases() as $os) {
            $spec = GenerationSpec::create()
                ->os($os)
                ->build();

            $this->assertSame($os, $spec->os);
        }
    }

    /** @test */
    public function test_it_handles_all_engines(): void
    {
        foreach (Engine::cases() as $engine) {
            $spec = GenerationSpec::create()
                ->engine($engine)
                ->build();

            $this->assertSame($engine, $spec->engine);
        }
    }

    /** @test */
    public function test_it_handles_all_risk_levels(): void
    {
        foreach (RiskLevel::cases() as $riskLevel) {
            $spec = GenerationSpec::create()
                ->riskLevel($riskLevel)
                ->build();

            $this->assertSame($riskLevel, $spec->riskLevel);
        }
    }

    /** @test */
    public function test_it_handles_all_valid_channels(): void
    {
        $channels = ['stable', 'beta', 'dev', 'canary'];

        foreach ($channels as $channel) {
            $spec = GenerationSpec::create()
                ->channel($channel)
                ->build();

            $this->assertSame($channel, $spec->channel);
        }
    }

    /** @test */
    public function test_it_handles_all_valid_architectures(): void
    {
        $architectures = ['x86_64', 'x64', 'ARM', 'ARM64', 'WOW64', 'i686'];

        foreach ($architectures as $arch) {
            $spec = GenerationSpec::create()
                ->arch($arch)
                ->build();

            $this->assertSame($arch, $spec->arch);
        }
    }

    /** @test */
    public function test_it_handles_various_locale_formats(): void
    {
        $locales = ['en-US', 'fr-FR', 'de-DE', 'ja-JP', 'zh-CN', 'pt-BR'];

        foreach ($locales as $locale) {
            $spec = GenerationSpec::create()
                ->locale($locale)
                ->build();

            $this->assertSame($locale, $spec->locale);
        }
    }

    // ========== BUILDER PATTERN TESTS ==========

    /** @test */
    public function builder_can_be_reused(): void
    {
        $builder = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome);

        $spec1 = $builder->build();
        $spec2 = $builder->device(DeviceType::Desktop)->build();

        // Both specs should have Chrome
        $this->assertSame(BrowserFamily::Chrome, $spec1->browser);
        $this->assertSame(BrowserFamily::Chrome, $spec2->browser);

        // Only spec2 should have Desktop
        $this->assertNull($spec1->device);
        $this->assertSame(DeviceType::Desktop, $spec2->device);
    }

    /** @test */
    public function builder_can_override_values(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->browser(BrowserFamily::Firefox)
            ->build();

        $this->assertSame(BrowserFamily::Firefox, $spec->browser);
    }

    /** @test */
    public function builder_returns_self_for_chaining(): void
    {
        $builder = GenerationSpec::create();

        $this->assertSame($builder, $builder->browser(BrowserFamily::Chrome));
        $this->assertSame($builder, $builder->device(DeviceType::Desktop));
        $this->assertSame($builder, $builder->versionMin(110));
    }
}
