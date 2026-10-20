<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Core;

use JOOservices\UserAgent\Core\BatchGenerator;
use JOOservices\UserAgent\Core\GenerationSession;
use JOOservices\UserAgent\Core\Renderer;
use JOOservices\UserAgent\Domain\Architecture;
use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\GenerationResult;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Domain\SelectionPolicyId;
use JOOservices\UserAgent\Domain\UniquePolicy;
use JOOservices\UserAgent\Exceptions\IncompatibleRequestException;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;
use JOOservices\UserAgent\Exceptions\UniqueGenerationExhausted;
use JOOservices\UserAgent\Generator;
use JOOservices\UserAgent\GeneratorConfig;
use JOOservices\UserAgent\History\InMemoryHistoryStore;
use JOOservices\UserAgent\Random\MtRandomSource;
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
    public function test_GEN_ios_profiles_report_webkit_engine_for_all_browser_families(): void
    {
        $generator = Generator::create();
        foreach ([BrowserFamily::Chrome, BrowserFamily::Firefox, BrowserFamily::Edge] as $browser) {
            $result = $generator->generate(new GenerationRequest(
                browser: $browser,
                platform: Platform::iOS,
                seed: 42,
            ));
            self::assertSame(\JOOservices\UserAgent\Domain\Engine::WebKit, $result->profile->engine);
        }
        $safari = $generator->generate(new GenerationRequest(
            browser: BrowserFamily::Safari,
            platform: Platform::iOS,
            seed: 42,
        ));
        self::assertSame(\JOOservices\UserAgent\Domain\Engine::WebKit, $safari->profile->engine);
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
    public function test_COMP_U02_version_error_reports_available_versions_without_invalid_alternatives(): void
    {
        try {
            Generator::create()->generate(new GenerationRequest(
                browser: BrowserFamily::Chrome,
                device: DeviceClass::Desktop,
                platform: Platform::Windows,
                versionExact: 999,
            ));
            self::fail('Expected incompatible request.');
        } catch (IncompatibleRequestException $exception) {
            self::assertSame(999, $exception->requested['versionExact']);
            self::assertSame(['chrome' => [139, 145]], $exception->availableVersions);
            self::assertSame([], $exception->alternatives);
            self::assertStringContainsString('999', $exception->getMessage());
        }
    }

    #[Test]
    public function test_COMP_U03_alternatives_satisfy_remaining_request_filters(): void
    {
        try {
            Generator::create()->generate(new GenerationRequest(
                browser: BrowserFamily::Safari,
                device: DeviceClass::Desktop,
                platform: Platform::Windows,
            ));
            self::fail('Expected incompatible request.');
        } catch (IncompatibleRequestException $exception) {
            self::assertNotEmpty($exception->alternatives);
            foreach ($exception->alternatives as $alternative) {
                $result = Generator::create()->generate(new GenerationRequest(
                    browser: BrowserFamily::from($alternative['browser']),
                    device: DeviceClass::from($alternative['device']),
                    platform: Platform::from($alternative['platform']),
                    seed: 1,
                ));
                self::assertSame($alternative['browser'], $result->profile->browser->value);
                self::assertSame($alternative['device'], $result->profile->device->value);
                self::assertSame($alternative['platform'], $result->profile->platform->value);
            }
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
    public function test_BATCH_U02_unique_results_do_not_depend_on_bounded_history_size(): void
    {
        $generator = Generator::create(new GeneratorConfig(historySize: 1));
        $first = $generator->generate(new GenerationRequest(platform: Platform::Windows, seed: 1));
        $second = $generator->generate(new GenerationRequest(platform: Platform::Android, seed: 1));
        self::assertNotSame($first->userAgent, $second->userAgent);
        $generated = 0;
        try {
            (new BatchGenerator())->generate(
                request: new GenerationRequest(),
                count: 3,
                unique: UniquePolicy::Fail,
                attemptBudget: 3,
                session: new GenerationSession(MtRandomSource::seeded(1), new InMemoryHistoryStore(1), 1),
                generateOne: static function (GenerationRequest $_request, GenerationSession $_session) use (&$generated, $first, $second): GenerationResult {
                    return match ($generated++ % 3) {
                        0, 2 => $first,
                        default => $second,
                    };
                },
            );
            self::fail('Expected the repeated third result to be rejected.');
        } catch (UniqueGenerationExhausted $exception) {
            self::assertSame(2, $exception->produced);
            self::assertSame(3, $exception->attempts);
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
