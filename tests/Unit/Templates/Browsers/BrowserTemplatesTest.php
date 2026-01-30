<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Templates\Browsers;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;
use JOOservices\UserAgent\Templates\Browsers\EdgeTemplate;
use JOOservices\UserAgent\Templates\Browsers\FirefoxTemplate;
use JOOservices\UserAgent\Templates\Browsers\SafariTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Consolidated tests for Firefox, Safari, and Edge templates.
 */
final class BrowserTemplatesTest extends TestCase
{
    // ========== FIREFOX TESTS ==========

    public function test_firefox_returns_correct_browser(): void
    {
        $template = new FirefoxTemplate;
        $this->assertSame(BrowserFamily::Firefox, $template->getBrowser());
    }

    public function test_firefox_returns_correct_engine(): void
    {
        $template = new FirefoxTemplate;
        $this->assertSame(Engine::Gecko, $template->getEngine());
    }

    public function test_firefox_returns_correct_versions(): void
    {
        $template = new FirefoxTemplate;
        $this->assertSame(147, $template->getStableVersion());
        $this->assertSame(100, $template->getMinVersion());
        $this->assertSame(147, $template->getMaxVersion());
    }

    public function test_firefox_returns_market_share(): void
    {
        $template = new FirefoxTemplate;
        $this->assertSame(3.0, $template->getMarketShare()->percentage);
    }

    public function test_firefox_returns_risk_level(): void
    {
        $template = new FirefoxTemplate;
        $this->assertSame(RiskLevel::Low, $template->getRiskLevel());
    }

    public function test_firefox_supports_desktop_devices(): void
    {
        $template = new FirefoxTemplate;
        $this->assertTrue($template->supportsDevice(DeviceType::Desktop));
        $this->assertTrue($template->supportsDevice(DeviceType::Mobile));
    }

    public function test_firefox_desktop_template_contains_gecko(): void
    {
        $template = new FirefoxTemplate;
        $ua = $template->getDesktopTemplate(OperatingSystem::Windows);
        $this->assertStringContainsString('Gecko', $ua);
        $this->assertStringContainsString('Firefox/{version}', $ua);
    }

    public function test_firefox_mobile_template_for_android(): void
    {
        $template = new FirefoxTemplate;
        $ua = $template->getMobileTemplate(OperatingSystem::Android);
        $this->assertStringContainsString('Android', $ua);
        $this->assertStringContainsString('Firefox/{version}', $ua);
    }

    public function test_firefox_engine_version_matches_browser_version(): void
    {
        $template = new FirefoxTemplate;
        $this->assertSame('147', $template->getEngineVersion(147));
    }

    public function test_firefox_tags(): void
    {
        $template = new FirefoxTemplate;
        $tags = $template->getTags();
        $this->assertContains('browser', $tags);
        $this->assertContains('firefox', $tags);
    }

    public function test_firefox_returns_empty_template_for_unsupported_desktop_os(): void
    {
        $template = new FirefoxTemplate;
        $this->assertSame('', $template->getDesktopTemplate(OperatingSystem::Android));
        $this->assertSame('', $template->getDesktopTemplate(OperatingSystem::iOS));
    }

    public function test_firefox_returns_empty_template_for_unsupported_mobile_os(): void
    {
        $template = new FirefoxTemplate;
        $this->assertSame('', $template->getMobileTemplate(OperatingSystem::Windows));
        $this->assertSame('', $template->getMobileTemplate(OperatingSystem::MacOS));
    }

    public function test_firefox_all_desktop_os_templates(): void
    {
        $template = new FirefoxTemplate;
        $this->assertNotEmpty($template->getDesktopTemplate(OperatingSystem::Windows));
        $this->assertNotEmpty($template->getDesktopTemplate(OperatingSystem::MacOS));
        $this->assertNotEmpty($template->getDesktopTemplate(OperatingSystem::Linux));
    }

    public function test_firefox_mobile_template_for_ios(): void
    {
        $template = new FirefoxTemplate;
        $ua = $template->getMobileTemplate(OperatingSystem::iOS);
        $this->assertStringContainsString('FxiOS/{version}', $ua);
    }

    public function test_firefox_returns_empty_for_unsupported_device_os(): void
    {
        $template = new FirefoxTemplate;
        $os = $template->getSupportedOs(DeviceType::Bot);
        $this->assertEmpty($os);
    }

    // ========== SAFARI TESTS ==========

    public function test_safari_returns_correct_browser(): void
    {
        $template = new SafariTemplate;
        $this->assertSame(BrowserFamily::Safari, $template->getBrowser());
    }

    public function test_safari_returns_correct_engine(): void
    {
        $template = new SafariTemplate;
        $this->assertSame(Engine::WebKit, $template->getEngine());
    }

    public function test_safari_returns_correct_versions(): void
    {
        $template = new SafariTemplate;
        $this->assertSame(26, $template->getStableVersion());
        $this->assertSame(14, $template->getMinVersion());
        $this->assertSame(26, $template->getMaxVersion());
    }

    public function test_safari_returns_market_share(): void
    {
        $template = new SafariTemplate;
        $this->assertSame(20.0, $template->getMarketShare()->percentage);
    }

    public function test_safari_only_supports_apple_platforms(): void
    {
        $template = new SafariTemplate;
        $desktopOs = $template->getSupportedOs(DeviceType::Desktop);
        $this->assertCount(1, $desktopOs);
        $this->assertContains(OperatingSystem::MacOS, $desktopOs);

        $mobileOs = $template->getSupportedOs(DeviceType::Mobile);
        $this->assertCount(1, $mobileOs);
        $this->assertContains(OperatingSystem::iOS, $mobileOs);
    }

    public function test_safari_desktop_template_contains_webkit(): void
    {
        $template = new SafariTemplate;
        $ua = $template->getDesktopTemplate(OperatingSystem::MacOS);
        $this->assertStringContainsString('AppleWebKit', $ua);
        $this->assertStringContainsString('Safari', $ua);
        $this->assertStringContainsString('Version/{version}', $ua);
    }

    public function test_safari_mobile_template_for_ios(): void
    {
        $template = new SafariTemplate;
        $ua = $template->getMobileTemplate(OperatingSystem::iOS);
        $this->assertStringContainsString('iPhone', $ua);
        $this->assertStringContainsString('Version/{version}', $ua);
    }

    public function test_safari_engine_version_is_stable(): void
    {
        $template = new SafariTemplate;
        $this->assertSame('605.1.15', $template->getEngineVersion(26));
        $this->assertSame('605.1.15', $template->getEngineVersion(14)); // Same for all versions
    }

    public function test_safari_does_not_support_windows(): void
    {
        $template = new SafariTemplate;
        $this->assertFalse($template->supportsOs(DeviceType::Desktop, OperatingSystem::Windows));
    }

    public function test_safari_tags(): void
    {
        $template = new SafariTemplate;
        $tags = $template->getTags();
        $this->assertContains('browser', $tags);
        $this->assertContains('safari', $tags);
    }

    public function test_safari_returns_empty_template_for_unsupported_desktop_os(): void
    {
        $template = new SafariTemplate;
        $this->assertSame('', $template->getDesktopTemplate(OperatingSystem::Windows));
        $this->assertSame('', $template->getDesktopTemplate(OperatingSystem::Linux));
    }

    public function test_safari_returns_empty_template_for_unsupported_mobile_os(): void
    {
        $template = new SafariTemplate;
        $this->assertSame('', $template->getMobileTemplate(OperatingSystem::Android));
    }

    public function test_safari_returns_empty_for_unsupported_device_os(): void
    {
        $template = new SafariTemplate;
        $os = $template->getSupportedOs(DeviceType::Bot);
        $this->assertEmpty($os);
    }

    public function test_safari_tablet_os_same_as_mobile(): void
    {
        $template = new SafariTemplate;
        $tabletOs = $template->getSupportedOs(DeviceType::Tablet);
        $mobileOs = $template->getSupportedOs(DeviceType::Mobile);
        $this->assertSame($mobileOs, $tabletOs);
    }

    // ========== EDGE TESTS ==========

    public function test_edge_returns_correct_browser(): void
    {
        $template = new EdgeTemplate;
        $this->assertSame(BrowserFamily::Edge, $template->getBrowser());
    }

    public function test_edge_returns_correct_engine(): void
    {
        $template = new EdgeTemplate;
        $this->assertSame(Engine::Blink, $template->getEngine());
    }

    public function test_edge_returns_correct_versions(): void
    {
        $template = new EdgeTemplate;
        $this->assertSame(144, $template->getStableVersion());
        $this->assertSame(90, $template->getMinVersion());
        $this->assertSame(144, $template->getMaxVersion());
    }

    public function test_edge_returns_market_share(): void
    {
        $template = new EdgeTemplate;
        $this->assertSame(5.0, $template->getMarketShare()->percentage);
    }

    public function test_edge_supports_windows_and_macos(): void
    {
        $template = new EdgeTemplate;
        $desktopOs = $template->getSupportedOs(DeviceType::Desktop);
        $this->assertCount(2, $desktopOs);
        $this->assertContains(OperatingSystem::Windows, $desktopOs);
        $this->assertContains(OperatingSystem::MacOS, $desktopOs);
    }

    public function test_edge_desktop_template_contains_edg_token(): void
    {
        $template = new EdgeTemplate;
        $ua = $template->getDesktopTemplate(OperatingSystem::Windows);
        $this->assertStringContainsString('Edg/{version}', $ua);
        $this->assertStringContainsString('Chrome/{version}', $ua);
    }

    public function test_edge_mobile_template_for_android(): void
    {
        $template = new EdgeTemplate;
        $ua = $template->getMobileTemplate(OperatingSystem::Android);
        $this->assertStringContainsString('EdgA/{version}', $ua);
    }

    public function test_edge_mobile_template_for_ios(): void
    {
        $template = new EdgeTemplate;
        $ua = $template->getMobileTemplate(OperatingSystem::iOS);
        $this->assertStringContainsString('EdgiOS/{version}', $ua);
    }

    public function test_edge_engine_version_matches_browser_version(): void
    {
        $template = new EdgeTemplate;
        $this->assertSame('144', $template->getEngineVersion(144));
    }

    public function test_edge_does_not_support_linux(): void
    {
        $template = new EdgeTemplate;
        $this->assertFalse($template->supportsOs(DeviceType::Desktop, OperatingSystem::Linux));
    }

    public function test_edge_tags(): void
    {
        $template = new EdgeTemplate;
        $tags = $template->getTags();
        $this->assertContains('browser', $tags);
        $this->assertContains('edge', $tags);
    }

    public function test_edge_returns_empty_template_for_unsupported_desktop_os(): void
    {
        $template = new EdgeTemplate;
        $this->assertSame('', $template->getDesktopTemplate(OperatingSystem::Linux));
        $this->assertSame('', $template->getDesktopTemplate(OperatingSystem::Android));
    }

    public function test_edge_returns_empty_template_for_unsupported_mobile_os(): void
    {
        $template = new EdgeTemplate;
        $this->assertSame('', $template->getMobileTemplate(OperatingSystem::Windows));
        $this->assertSame('', $template->getMobileTemplate(OperatingSystem::MacOS));
    }

    public function test_edge_all_desktop_os_templates(): void
    {
        $template = new EdgeTemplate;
        $this->assertNotEmpty($template->getDesktopTemplate(OperatingSystem::Windows));
        $this->assertNotEmpty($template->getDesktopTemplate(OperatingSystem::MacOS));
    }

    public function test_edge_returns_empty_for_unsupported_device_os(): void
    {
        $template = new EdgeTemplate;
        $os = $template->getSupportedOs(DeviceType::Bot);
        $this->assertEmpty($os);
    }
}
