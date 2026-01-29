<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Service\Profiles;

use JOOservices\UserAgent\Service\Profiles\AndroidChromeProfile;
use JOOservices\UserAgent\Service\Profiles\DesktopChromeProfile;
use JOOservices\UserAgent\Service\Profiles\MobileSafariProfile;
use JOOservices\UserAgent\Service\Profiles\Profiles;
use JOOservices\UserAgent\Service\UserAgentService;
use PHPUnit\Framework\TestCase;

final class ProfilesTest extends TestCase
{
    private UserAgentService $service;

    private Profiles $profiles;

    protected function setUp(): void
    {
        $this->service = new UserAgentService();
        $this->profiles = new Profiles($this->service);
    }

    // DesktopChromeProfile Tests
    public function test_desktop_chrome_windows(): void
    {
        $profile = new DesktopChromeProfile($this->service);
        $ua = $profile->windows(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Windows', $ua);
        $this->assertStringContainsString('Chrome', $ua);
    }

    public function test_desktop_chrome_macos(): void
    {
        $profile = new DesktopChromeProfile($this->service);
        $ua = $profile->macos(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Macintosh', $ua);
        $this->assertStringContainsString('Chrome', $ua);
    }

    public function test_desktop_chrome_linux(): void
    {
        $profile = new DesktopChromeProfile($this->service);
        $ua = $profile->linux(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Linux', $ua);
        $this->assertStringContainsString('Chrome', $ua);
    }

    public function test_desktop_chrome_any(): void
    {
        $profile = new DesktopChromeProfile($this->service);
        $ua = $profile->any(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Chrome', $ua);
    }

    // MobileSafariProfile Tests
    public function test_mobile_safari_iphone(): void
    {
        $profile = new MobileSafariProfile($this->service);
        $ua = $profile->iphone(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('iPhone', $ua);
    }

    public function test_mobile_safari_ipad(): void
    {
        $profile = new MobileSafariProfile($this->service);
        $ua = $profile->ipad(12345);

        // iPad uses mobile template, so just check it's not empty
        $this->assertNotEmpty($ua);
    }

    public function test_mobile_safari_any(): void
    {
        $profile = new MobileSafariProfile($this->service);
        $ua = $profile->any(12345);

        // Safari only supports iOS, should generate valid UA
        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Safari', $ua);
    }

    // AndroidChromeProfile Tests
    public function test_android_chrome_phone(): void
    {
        $profile = new AndroidChromeProfile($this->service);
        $ua = $profile->phone(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Android', $ua);
        $this->assertStringContainsString('Mobile', $ua);
    }

    public function test_android_chrome_tablet(): void
    {
        $profile = new AndroidChromeProfile($this->service);
        $ua = $profile->tablet(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Android', $ua);
    }

    public function test_android_chrome_any(): void
    {
        $profile = new AndroidChromeProfile($this->service);
        $ua = $profile->any(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Android', $ua);
        $this->assertStringContainsString('Chrome', $ua);
    }

    // Profiles Convenience Class Tests
    public function test_profiles_desktop_chrome(): void
    {
        $ua = $this->profiles->desktopChrome->windows(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Chrome', $ua);
    }

    public function test_profiles_mobile_safari(): void
    {
        $ua = $this->profiles->mobileSafari->iphone(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('iPhone', $ua);
    }

    public function test_profiles_android_chrome(): void
    {
        $ua = $this->profiles->androidChrome->phone(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Android', $ua);
    }

    public function test_profiles_random_mobile(): void
    {
        $ua = $this->profiles->randomMobile(12345);

        $this->assertNotEmpty($ua);
    }

    public function test_profiles_random_desktop(): void
    {
        $ua = $this->profiles->randomDesktop(12345);

        $this->assertNotEmpty($ua);
    }

    public function test_profiles_firefox(): void
    {
        $ua = $this->profiles->firefox(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Firefox', $ua);
    }

    public function test_profiles_edge(): void
    {
        $ua = $this->profiles->edge(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Edg', $ua);
    }

    public function test_profiles_deterministic(): void
    {
        $ua1 = $this->profiles->desktopChrome->windows(99999);
        $ua2 = $this->profiles->desktopChrome->windows(99999);

        $this->assertSame($ua1, $ua2);
    }
}
