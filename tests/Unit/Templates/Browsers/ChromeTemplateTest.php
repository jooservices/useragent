<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Templates\Browsers;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;
use JOOservices\UserAgent\Templates\Browsers\ChromeTemplate;
use PHPUnit\Framework\TestCase;

final class ChromeTemplateTest extends TestCase
{
    private ChromeTemplate $template;

    protected function setUp(): void
    {
        $this->template = new ChromeTemplate;
    }

    public function test_it_returns_correct_browser(): void
    {
        $this->assertSame(BrowserFamily::Chrome, $this->template->getBrowser());
    }

    public function test_it_returns_correct_engine(): void
    {
        $this->assertSame(Engine::Blink, $this->template->getEngine());
    }

    public function test_it_returns_supported_devices(): void
    {
        $devices = $this->template->getSupportedDevices();

        $this->assertCount(3, $devices);
        $this->assertContains(DeviceType::Desktop, $devices);
        $this->assertContains(DeviceType::Mobile, $devices);
        $this->assertContains(DeviceType::Tablet, $devices);
    }

    public function test_it_returns_desktop_os(): void
    {
        $os = $this->template->getSupportedOs(DeviceType::Desktop);

        $this->assertCount(4, $os);
        $this->assertContains(OperatingSystem::Windows, $os);
        $this->assertContains(OperatingSystem::MacOS, $os);
        $this->assertContains(OperatingSystem::Linux, $os);
        $this->assertContains(OperatingSystem::ChromeOS, $os);
    }

    public function test_it_returns_mobile_os(): void
    {
        $os = $this->template->getSupportedOs(DeviceType::Mobile);

        $this->assertCount(2, $os);
        $this->assertContains(OperatingSystem::Android, $os);
        $this->assertContains(OperatingSystem::iOS, $os);
    }

    public function test_it_returns_tablet_os(): void
    {
        $os = $this->template->getSupportedOs(DeviceType::Tablet);

        $this->assertCount(2, $os);
        $this->assertContains(OperatingSystem::Android, $os);
        $this->assertContains(OperatingSystem::iOS, $os);
    }

    public function test_it_returns_empty_array_for_unsupported_device(): void
    {
        $os = $this->template->getSupportedOs(DeviceType::Bot);

        $this->assertEmpty($os);
    }

    public function test_it_returns_correct_versions(): void
    {
        $this->assertSame(145, $this->template->getStableVersion());
        $this->assertSame(90, $this->template->getMinVersion());
        $this->assertSame(145, $this->template->getMaxVersion());
    }

    public function test_it_returns_market_share(): void
    {
        $marketShare = $this->template->getMarketShare();

        $this->assertSame(64.0, $marketShare->percentage);
    }

    public function test_it_returns_risk_level(): void
    {
        $this->assertSame(RiskLevel::Low, $this->template->getRiskLevel());
    }

    public function test_it_returns_desktop_template_for_windows(): void
    {
        $template = $this->template->getDesktopTemplate(OperatingSystem::Windows);

        $this->assertStringContainsString('Windows NT', $template);
        $this->assertStringContainsString('Chrome/{version}', $template);
        $this->assertStringContainsString('AppleWebKit/537.36', $template);
    }

    public function test_it_returns_desktop_template_for_macos(): void
    {
        $template = $this->template->getDesktopTemplate(OperatingSystem::MacOS);

        $this->assertStringContainsString('Macintosh', $template);
        $this->assertStringContainsString('Mac OS X', $template);
        $this->assertStringContainsString('Chrome/{version}', $template);
    }

    public function test_it_returns_desktop_template_for_linux(): void
    {
        $template = $this->template->getDesktopTemplate(OperatingSystem::Linux);

        $this->assertStringContainsString('X11; Linux', $template);
        $this->assertStringContainsString('Chrome/{version}', $template);
    }

    public function test_it_returns_desktop_template_for_chromeos(): void
    {
        $template = $this->template->getDesktopTemplate(OperatingSystem::ChromeOS);

        $this->assertStringContainsString('CrOS', $template);
        $this->assertStringContainsString('Chrome/{version}', $template);
    }

    public function test_it_returns_empty_template_for_unsupported_desktop_os(): void
    {
        $template = $this->template->getDesktopTemplate(OperatingSystem::Android);

        $this->assertSame('', $template);
    }

    public function test_it_returns_mobile_template_for_android(): void
    {
        $template = $this->template->getMobileTemplate(OperatingSystem::Android);

        $this->assertStringContainsString('Linux; Android', $template);
        $this->assertStringContainsString('Chrome/{version}', $template);
        $this->assertStringContainsString('Mobile', $template);
    }

    public function test_it_returns_mobile_template_for_ios(): void
    {
        $template = $this->template->getMobileTemplate(OperatingSystem::iOS);

        $this->assertStringContainsString('iPhone', $template);
        $this->assertStringContainsString('CriOS/{version}', $template);
    }

    public function test_it_returns_empty_template_for_unsupported_mobile_os(): void
    {
        $template = $this->template->getMobileTemplate(OperatingSystem::Windows);

        $this->assertSame('', $template);
    }

    public function test_it_returns_engine_version(): void
    {
        $version = $this->template->getEngineVersion(145);

        $this->assertSame('145', $version);
    }

    public function test_it_returns_tags(): void
    {
        $tags = $this->template->getTags();

        $this->assertCount(2, $tags);
        $this->assertContains('browser', $tags);
        $this->assertContains('chrome', $tags);
    }

    public function test_it_supports_desktop_device(): void
    {
        $this->assertTrue($this->template->supportsDevice(DeviceType::Desktop));
    }

    public function test_it_supports_mobile_device(): void
    {
        $this->assertTrue($this->template->supportsDevice(DeviceType::Mobile));
    }

    public function test_it_does_not_support_bot_device(): void
    {
        $this->assertFalse($this->template->supportsDevice(DeviceType::Bot));
    }

    public function test_it_supports_windows_on_desktop(): void
    {
        $this->assertTrue($this->template->supportsOs(DeviceType::Desktop, OperatingSystem::Windows));
    }

    public function test_it_supports_android_on_mobile(): void
    {
        $this->assertTrue($this->template->supportsOs(DeviceType::Mobile, OperatingSystem::Android));
    }

    public function test_it_does_not_support_windows_on_mobile(): void
    {
        $this->assertFalse($this->template->supportsOs(DeviceType::Mobile, OperatingSystem::Windows));
    }

    public function test_it_does_not_support_unsupported_device(): void
    {
        $this->assertFalse($this->template->supportsOs(DeviceType::Bot, OperatingSystem::Windows));
    }
}
