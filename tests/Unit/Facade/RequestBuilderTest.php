<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Facade;

use JOOservices\UserAgent\Domain\BrowserFamily;
use JOOservices\UserAgent\Domain\DeviceClass;
use JOOservices\UserAgent\Domain\GenerationRequest;
use JOOservices\UserAgent\Domain\Platform;
use JOOservices\UserAgent\Domain\SelectionPolicyId;
use JOOservices\UserAgent\Domain\UniquePolicy;
use JOOservices\UserAgent\Facade\RequestBuilder;
use JOOservices\UserAgent\Tests\Support\FixtureDataset;
use JOOservices\UserAgent\UserAgent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequestBuilderTest extends TestCase
{
    #[Test]
    public function test_FLUENT_H01_H07_builds_and_generates_string(): void
    {
        $builder = new RequestBuilder(new GenerationRequest(), FixtureDataset::generator());
        $request = $builder->chrome()->windows()->desktop()->seed(42)->toRequest();
        self::assertSame(BrowserFamily::Chrome, $request->browser);
        self::assertSame(DeviceClass::Desktop, $request->device);
        self::assertSame(Platform::Windows, $request->platform);
        self::assertStringContainsString('Chrome/', $builder->chrome()->windows()->desktop()->seed(42)->generate());
    }

    #[Test]
    public function test_FLUENT_H08_W01_builder_is_immutable_and_last_write_wins(): void
    {
        $base = new RequestBuilder(new GenerationRequest(), FixtureDataset::generator());
        $chrome = $base->chrome();
        $safari = $chrome->safari();
        self::assertSame(BrowserFamily::Chrome, $chrome->toRequest()->browser);
        self::assertSame(BrowserFamily::Safari, $safari->toRequest()->browser);
        self::assertSame(UserAgent::chrome()->toRequest()->toArray(), UserAgent::builder()->chrome()->toRequest()->toArray());
    }

    #[Test]
    public function test_FLUENT_U02_U04_removed_methods_are_absent(): void
    {
        $builderMethods = array_map(
            static fn(\ReflectionMethod $method): string => $method->getName(),
            (new \ReflectionClass(RequestBuilder::class))->getMethods(),
        );
        $facadeMethods = array_map(
            static fn(\ReflectionMethod $method): string => $method->getName(),
            (new \ReflectionClass(UserAgent::class))->getMethods(),
        );
        self::assertNotContains('exclude', $builderMethods);
        self::assertNotContains('googlebot', $builderMethods);
        self::assertNotContains('seed', $facadeMethods);
    }

    #[Test]
    public function test_FLUENT_all_locked_setters_map_to_request_fields(): void
    {
        $builder = new RequestBuilder(new GenerationRequest(), FixtureDataset::generator());
        $request = $builder
            ->firefox()->edge()->chrome()
            ->mobile()->tablet()->desktop()
            ->linux()->android()->ios()->chromeos()->macos()->windows()
            ->locale('en-US')->architecture(\JOOservices\UserAgent\Domain\Architecture::X86_64)
            ->versionMin(120)->versionMax(145)->recent()->selection(SelectionPolicyId::Uniform)
            ->seed(12)->datasetRevision('fixture-1')->toRequest();
        self::assertSame(BrowserFamily::Chrome, $request->browser);
        self::assertSame(DeviceClass::Desktop, $request->device);
        self::assertSame(Platform::Windows, $request->platform);
        self::assertSame(6, $request->releasedWithinMonths);
        self::assertSame(SelectionPolicyId::Uniform, $request->selection);
        self::assertSame('fixture-1', $request->datasetRevision);

        $exact = $builder->versionMin(120)->versionMax(145)->version(139)->toRequest();
        self::assertSame(139, $exact->versionExact);
        self::assertNull($exact->versionMin);
        self::assertNull($exact->versionMax);
    }

    #[Test]
    public function test_FLUENT_generate_many_uses_injected_engine(): void
    {
        $batch = (new RequestBuilder(new GenerationRequest(seed: 2), FixtureDataset::generator()))
            ->generateMany(2, UniquePolicy::AllowDuplicates);
        self::assertCount(2, $batch->entries);
    }

    #[Test]
    public function testGeneratePoolReturnsOnlyUserAgentStrings(): void
    {
        $builder = new RequestBuilder(new GenerationRequest(seed: 2), FixtureDataset::generator());

        self::assertSame(
            $builder->generateMany(2, UniquePolicy::AllowDuplicates)->userAgents(),
            $builder->generatePool(2, UniquePolicy::AllowDuplicates),
        );
    }

    #[Test]
    public function test_FACADE_all_entrypoints_are_stateless_builders(): void
    {
        $builders = [
            UserAgent::firefox(), UserAgent::safari(), UserAgent::edge(),
            UserAgent::desktop(), UserAgent::mobile(), UserAgent::tablet(),
            UserAgent::windows(), UserAgent::macos(), UserAgent::linux(),
            UserAgent::android(), UserAgent::ios(), UserAgent::chromeos(),
        ];
        $requests = array_map(static fn(RequestBuilder $builder): GenerationRequest => $builder->toRequest(), $builders);
        self::assertContains(
            Platform::ChromeOS,
            array_map(static fn(GenerationRequest $request): ?Platform => $request->platform, $requests),
        );
        self::assertStringStartsWith('Mozilla/5.0', UserAgent::generate());
        self::assertStringStartsWith('Mozilla/5.0', UserAgent::generateResult()->userAgent);
        self::assertCount(24, UserAgent::matrix());
    }
}
