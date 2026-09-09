<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Integration;

use JOOservices\UserAgent\Core\Renderer;
use JOOservices\UserAgent\Dataset\BundledDatasetRepository;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Generator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BundledGenerationTest extends TestCase
{
    #[Test]
    public function test_DS_H01_H04_bundled_dataset_checksum_loads(): void
    {
        $dataset = (new BundledDatasetRepository())->load();
        self::assertSame('2026.08.28.1', $dataset->provenance->revision);
        self::assertCount(24, $dataset->compatibility);
        self::assertSame(64, strlen($dataset->provenance->checksumSha256));
    }

    #[Test]
    public function test_GOLD02_bundled_seed_42_is_stable(): void
    {
        $first = Generator::create()->generate(new GenerationRequest(seed: 42));
        $second = Generator::create()->generate(new GenerationRequest(seed: 42));
        self::assertSame($first->toJson(), $second->toJson());
        $golden = file_get_contents(dirname(__DIR__) . '/Fixtures/golden/bundled-seed-42.json');
        self::assertIsString($golden);
        self::assertSame(json_decode($golden, true, 32, JSON_THROW_ON_ERROR), $first->toArray());
        self::assertSame('firefox', $first->profile->browser->value);
        self::assertSame('linux', $first->profile->platform->value);
    }

    #[Test]
    public function test_INV03_every_bundled_tuple_generates(): void
    {
        $generator = Generator::create();
        foreach ($generator->matrix() as $tuple) {
            $result = $generator->generate(new GenerationRequest(
                browser: $tuple->browser,
                device: $tuple->device,
                platform: $tuple->platform,
                seed: 9,
            ));
            self::assertTrue((new Renderer())->profileMatches($result->userAgent, $result->profile));
        }
    }

    #[Test]
    public function test_INV_android_models_match_mobile_and_tablet_profiles(): void
    {
        $generator = Generator::create();
        foreach (range(1, 20) as $seed) {
            $profile = $generator->generate(new GenerationRequest(platform: Platform::Android, seed: $seed))->profile;
            if ($profile->device === DeviceClass::Tablet) {
                self::assertNull($profile->model);
                self::assertSame('Pixel Tablet', $profile->deviceToken);
            } else {
                self::assertContains($profile->model, ['Pixel 8', 'SM-S918B']);
                self::assertNull($profile->deviceToken);
            }
        }
    }
}
