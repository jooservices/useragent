<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Core;

use JOOservices\UserAgent\Core\Renderer;
use JOOservices\UserAgent\Domain\Architecture;
use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Domain\SelectionPolicyId;
use JOOservices\UserAgent\Domain\UniquePolicy;
use JOOservices\UserAgent\Exceptions\IncompatibleRequestException;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;
use JOOservices\UserAgent\Exceptions\UniqueGenerationExhausted;
use JOOservices\UserAgent\Tests\Support\FixtureDataset;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GeneratorTest extends TestCase
{
    #[Test]
    public function testBuilderUsesTheExistingGeneratorInstance(): void
    {
        $generator = FixtureDataset::generator();

        self::assertSame(
            $generator->generate(new GenerationRequest(seed: 42))->userAgent,
            $generator->builder()->seed(42)->generate(),
        );
    }

    #[Test]
    public function test_GEN_H01_H03_seeded_result_is_coherent_and_repeatable(): void
    {
        $request = new GenerationRequest(seed: 42);
        $first = FixtureDataset::generator()->generate($request);
        $second = FixtureDataset::generator()->generate($request);
        self::assertSame($first->toJson(), $second->toJson());
        $golden = file_get_contents(dirname(__DIR__, 2) . '/Fixtures/golden/fixture-seed-42.json');
        self::assertIsString($golden);
        self::assertSame(json_decode($golden, true, 32, JSON_THROW_ON_ERROR), $first->toArray());
        self::assertStringStartsWith('Mozilla/5.0', $first->userAgent);
        self::assertTrue((new Renderer())->profileMatches($first->userAgent, $first->profile));
        self::assertSame('fixture-1', $first->provenance->revision);
    }

    #[Test]
    public function test_GEN_H02_H05_explicit_tuple_and_version_are_respected(): void
    {
        $result = FixtureDataset::generator()->generate(new GenerationRequest(
            browser: BrowserFamily::Chrome,
            device: DeviceClass::Desktop,
            platform: Platform::Windows,
            architecture: Architecture::X86_64,
            versionExact: 145,
            seed: 7,
        ));
        self::assertSame(BrowserFamily::Chrome, $result->profile->browser);
        self::assertSame(145, $result->profile->browserMajor);
        self::assertSame('11.0', $result->profile->osVersion);
        self::assertStringContainsString('Windows NT 10.0', $result->userAgent);
    }

    #[Test]
    public function test_REN_H02_tablet_ios_uses_ipad(): void
    {
        $result = FixtureDataset::generator()->generate(new GenerationRequest(
            browser: BrowserFamily::Safari,
            device: DeviceClass::Tablet,
            platform: Platform::iOS,
            seed: 2,
        ));
        self::assertStringContainsString('iPad', $result->userAgent);
        self::assertStringNotContainsString('iPhone', $result->userAgent);
        self::assertStringContainsString('18_0', $result->userAgent);
    }

    #[Test]
    public function test_GEN_H04_recent_filters_old_versions(): void
    {
        $result = FixtureDataset::generator()->generate(new GenerationRequest(
            browser: BrowserFamily::Chrome,
            releasedWithinMonths: 1,
            seed: 1,
        ));
        self::assertSame(145, $result->profile->browserMajor);
    }

    #[Test]
    public function test_COMP_version_policy_filters_tuples_before_selection(): void
    {
        $result = FixtureDataset::generator()->generate(new GenerationRequest(versionExact: 26, seed: 1));
        self::assertSame(BrowserFamily::Safari, $result->profile->browser);
    }

    #[Test]
    public function test_COMP_U01_invalid_tuple_has_alternatives(): void
    {
        try {
            FixtureDataset::generator()->generate(new GenerationRequest(
                browser: BrowserFamily::Safari,
                device: DeviceClass::Desktop,
                platform: Platform::Windows,
            ));
            self::fail('Expected incompatible request.');
        } catch (IncompatibleRequestException $exception) {
            self::assertStringContainsString('safari', $exception->getMessage());
            self::assertStringContainsString('macos', $exception->getMessage());
        }
    }

    #[Test]
    public function test_GEN_U02_unknown_revision_is_rejected(): void
    {
        $this->expectException(InvalidRequestException::class);
        FixtureDataset::generator()->generate(new GenerationRequest(datasetRevision: 'nope'));
    }

    #[Test]
    public function test_BATCH_H01_allow_duplicates_returns_requested_count(): void
    {
        $batch = FixtureDataset::generator()->generateMany(
            new GenerationRequest(browser: BrowserFamily::Chrome, device: DeviceClass::Desktop, platform: Platform::Windows, versionExact: 145),
            2,
            UniquePolicy::AllowDuplicates,
        );
        self::assertCount(2, $batch->entries);
        self::assertSame($batch->entries[0]->userAgent, $batch->entries[1]->userAgent);
        self::assertSame(2, $batch->attemptCount);
    }

    #[Test]
    public function test_BATCH_U01_uniqueness_exhaustion_is_typed_and_bounded(): void
    {
        try {
            FixtureDataset::generator()->generateMany(
                new GenerationRequest(browser: BrowserFamily::Chrome, device: DeviceClass::Desktop, platform: Platform::Windows, architecture: Architecture::X86_64, versionExact: 145),
                2,
            );
            self::fail('Expected exhaustion.');
        } catch (UniqueGenerationExhausted $exception) {
            self::assertSame(2, $exception->requested);
            self::assertSame(1, $exception->produced);
            self::assertSame(50, $exception->attempts);
        }
    }

    #[Test]
    public function test_GEN_U04_U05_batch_limits_are_enforced(): void
    {
        foreach ([0, 1001] as $count) {
            try {
                FixtureDataset::generator()->generateMany(new GenerationRequest(), $count);
                self::fail('Expected invalid batch count.');
            } catch (InvalidRequestException) {
                self::addToAssertionCount(1);
            }
        }
    }

    #[Test]
    public function test_SEL_H03_round_robin_advances_on_shared_generator(): void
    {
        $generator = FixtureDataset::generator();
        $request = new GenerationRequest(selection: SelectionPolicyId::RoundRobin, seed: 0);
        $families = [];
        foreach (range(1, 5) as $_) {
            $result = $generator->generate($request);
            $families[] = $result->profile->browser->value . '|' . $result->profile->device->value;
        }
        self::assertSame($families[0], $families[4]);
        self::assertCount(4, array_unique(array_slice($families, 0, 4)));
    }

    #[Test]
    public function test_INV02_every_fixture_tuple_generates(): void
    {
        $generator = FixtureDataset::generator();
        foreach ($generator->matrix() as $tuple) {
            $result = $generator->generate(new GenerationRequest(
                browser: $tuple->browser,
                device: $tuple->device,
                platform: $tuple->platform,
                seed: 3,
            ));
            self::assertTrue((new Renderer())->profileMatches($result->userAgent, $result->profile));
        }
    }
}
