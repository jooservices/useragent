<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Integration;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end integration tests for UA generation.
 */
final class EndToEndGenerationTest extends TestCase
{
    private UserAgentService $service;

    protected function setUp(): void
    {
        $this->service = new UserAgentService;
    }

    public function test_it_generates_complete_desktop_chrome_ua(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->device(DeviceType::Desktop)
            ->os(OperatingSystem::Windows)
            ->versionExact(120)
            ->locale('en-US')
            ->arch('x86_64')
            ->build();

        $ua = $this->service->generate($spec, 12345);

        // Verify UA structure
        $this->assertNotEmpty($ua);
        $this->assertStringStartsWith('Mozilla/', $ua);
        $this->assertStringContainsString('Windows', $ua);
        $this->assertStringContainsString('Chrome', $ua);
        $this->assertStringContainsString('120', $ua);
    }

    public function test_it_generates_complete_mobile_safari_ua(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Safari)
            ->device(DeviceType::Mobile)
            ->os(OperatingSystem::iOS)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
        $this->assertStringStartsWith('Mozilla/', $ua);
        $this->assertStringContainsString('iPhone', $ua);
        $this->assertStringContainsString('Safari', $ua);
    }

    public function test_it_generates_complete_android_chrome_ua(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->device(DeviceType::Mobile)
            ->os(OperatingSystem::Android)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Android', $ua);
        $this->assertStringContainsString('Chrome', $ua);
        $this->assertStringContainsString('Mobile', $ua);
    }

    public function test_it_generates_firefox_ua(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Firefox)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Firefox', $ua);
    }

    public function test_it_generates_edge_ua(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Edge)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Edg', $ua);
    }

    public function test_it_generates_different_uas_for_different_specs(): void
    {
        $chromeSpec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->build();

        $firefoxSpec = GenerationSpec::create()
            ->browser(BrowserFamily::Firefox)
            ->build();

        $chromeUa = $this->service->generate($chromeSpec, 12345);
        $firefoxUa = $this->service->generate($firefoxSpec, 12345);

        $this->assertNotSame($chromeUa, $firefoxUa);
    }

    public function test_it_generates_realistic_ua_strings(): void
    {
        // Generate 10 UAs and verify they all look realistic
        for ($i = 0; $i < 10; $i++) {
            $ua = $this->service->generate(null, $i);

            $this->assertNotEmpty($ua);
            $this->assertStringStartsWith('Mozilla/', $ua);
            $this->assertGreaterThan(50, strlen($ua)); // Realistic length
            $this->assertLessThan(300, strlen($ua)); // Not too long
        }
    }

    public function test_full_pipeline_with_all_components(): void
    {
        // Test that all components work together
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->device(DeviceType::Desktop)
            ->os(OperatingSystem::MacOS)
            ->versionMin(120)
            ->versionMax(145)
            ->locale('fr-FR')
            ->arch('ARM64')
            ->build();

        $ua = $this->service->generate($spec, 99999);

        // Verify all components contributed
        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Macintosh', $ua);
        $this->assertStringContainsString('Chrome', $ua);

        // Verify determinism
        $ua2 = $this->service->generate($spec, 99999);
        $this->assertSame($ua, $ua2);
    }
}
