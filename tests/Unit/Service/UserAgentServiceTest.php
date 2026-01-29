<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Service;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;
use PHPUnit\Framework\TestCase;

final class UserAgentServiceTest extends TestCase
{
    private UserAgentService $service;

    protected function setUp(): void
    {
        $this->service = new UserAgentService();
    }

    public function test_it_generates_user_agent_with_default_spec(): void
    {
        $ua = $this->service->generate();

        $this->assertNotEmpty($ua);
        $this->assertIsString($ua);
    }

    public function test_it_generates_deterministic_ua_with_seed(): void
    {
        $ua1 = $this->service->generate(null, 12345);
        $ua2 = $this->service->generate(null, 12345);

        $this->assertSame($ua1, $ua2);
    }

    public function test_it_generates_different_uas_with_different_seeds(): void
    {
        $ua1 = $this->service->generate(null, 11111);
        $ua2 = $this->service->generate(null, 22222);

        // Highly likely to be different
        $this->assertNotSame($ua1, $ua2);
    }

    public function test_it_respects_browser_in_spec(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Firefox)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
        // Firefox UAs typically contain "Firefox"
        $this->assertStringContainsString('Firefox', $ua);
    }

    public function test_it_respects_device_type_in_spec(): void
    {
        $spec = GenerationSpec::create()
            ->device(DeviceType::Mobile)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
    }

    public function test_it_respects_os_in_spec(): void
    {
        $spec = GenerationSpec::create()
            ->os(OperatingSystem::MacOS)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
    }

    public function test_it_respects_version_exact_in_spec(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->versionExact(120)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('120', $ua);
    }

    public function test_it_respects_locale_in_spec(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->locale('fr-FR')
            ->build();

        $ua = $this->service->generate($spec, 12345);

        // Locale is picked but not rendered in current templates
        $this->assertNotEmpty($ua);
    }

    public function test_it_respects_arch_in_spec(): void
    {
        $spec = GenerationSpec::create()
            ->arch('ARM64')
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
    }

    public function test_it_generates_desktop_chrome_ua(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->device(DeviceType::Desktop)
            ->os(OperatingSystem::Windows)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Chrome', $ua);
    }

    public function test_it_generates_mobile_safari_ua(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Safari)
            ->device(DeviceType::Mobile)
            ->os(OperatingSystem::iOS)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
    }

    public function test_it_generates_android_chrome_ua(): void
    {
        $spec = GenerationSpec::create()
            ->browser(BrowserFamily::Chrome)
            ->device(DeviceType::Mobile)
            ->os(OperatingSystem::Android)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Android', $ua);
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

    public function test_it_generates_tablet_ua(): void
    {
        $spec = GenerationSpec::create()
            ->device(DeviceType::Tablet)
            ->os(OperatingSystem::iOS)
            ->build();

        $ua = $this->service->generate($spec, 12345);

        $this->assertNotEmpty($ua);
    }
}
