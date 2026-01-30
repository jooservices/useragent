<?php

declare(strict_types=1);

namespace Tests\Unit\Facade;

use JOOservices\UserAgent\Facade\ProfileBuilder;
use JOOservices\UserAgent\Service\Profiles\AndroidChromeProfile;
use JOOservices\UserAgent\Service\Profiles\DesktopChromeProfile;
use JOOservices\UserAgent\Service\Profiles\MobileSafariProfile;
use JOOservices\UserAgent\Service\UserAgentService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProfileBuilder::class)]
final class ProfileBuilderTest extends TestCase
{
    private ProfileBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new ProfileBuilder(new UserAgentService);
    }

    public function test_desktop_chrome_returns_correct_type(): void
    {
        $profile = $this->builder->desktopChrome();
        $this->assertInstanceOf(DesktopChromeProfile::class, $profile);
    }

    public function test_mobile_safari_returns_correct_type(): void
    {
        $profile = $this->builder->mobileSafari();
        $this->assertInstanceOf(MobileSafariProfile::class, $profile);
    }

    public function test_android_chrome_returns_correct_type(): void
    {
        $profile = $this->builder->androidChrome();
        $this->assertInstanceOf(AndroidChromeProfile::class, $profile);
    }

    public function test_random_mobile(): void
    {
        $ua = $this->builder->randomMobile();
        $this->assertNotEmpty($ua);
        // Mobile UAs usually contain 'Mobile' or 'Android' or 'iPhone'
        $isMobile = str_contains($ua, 'Mobile')
            || str_contains($ua, 'Android')
            || str_contains($ua, 'iPhone')
            || str_contains($ua, 'iPad');
        $this->assertTrue($isMobile, "Expected mobile UA, got: $ua");
    }

    public function test_random_desktop(): void
    {
        $ua = $this->builder->randomDesktop();
        $this->assertNotEmpty($ua);
        // Desktop UAs contain platform tokens
        $isDesktop = str_contains($ua, 'Windows NT')
            || str_contains($ua, 'Macintosh')
            || str_contains($ua, 'X11')
            || str_contains($ua, 'CrOS');
        $this->assertTrue($isDesktop, "Expected desktop UA, got: $ua");
    }

    public function test_firefox(): void
    {
        $ua = $this->builder->firefox();
        $this->assertMatchesRegularExpression('/Firefox|FxiOS/', $ua);
    }

    public function test_edge(): void
    {
        $ua = $this->builder->edge();
        $this->assertStringContainsString('Edg', $ua);
    }

    public function test_desktop_chrome_windows(): void
    {
        $ua = $this->builder->desktopChrome()->windows();
        $this->assertStringContainsString('Windows', $ua);
        $this->assertMatchesRegularExpression('/Chrome\//', $ua);
    }

    public function test_desktop_chrome_macos(): void
    {
        $ua = $this->builder->desktopChrome()->macos();
        $this->assertStringContainsString('Macintosh', $ua);
        $this->assertMatchesRegularExpression('/Chrome\//', $ua);
    }

    public function test_desktop_chrome_linux(): void
    {
        $ua = $this->builder->desktopChrome()->linux();
        $this->assertStringContainsString('Linux', $ua);
        $this->assertMatchesRegularExpression('/Chrome\//', $ua);
    }

    public function test_mobile_safari_iphone(): void
    {
        $ua = $this->builder->mobileSafari()->iphone();
        $this->assertStringContainsString('iPhone', $ua);
        $this->assertStringContainsString('Safari', $ua);
    }

    public function test_mobile_safari_ipad(): void
    {
        $ua = $this->builder->mobileSafari()->ipad();
        // Note: Safari template currently uses same template for Mobile/Tablet
        // The library renders iOS Safari for tablets using the mobile template
        $this->assertStringContainsString('Safari', $ua);
        $this->assertMatchesRegularExpression('/iPhone|iPad/', $ua);
    }

    public function test_android_chrome_phone(): void
    {
        $ua = $this->builder->androidChrome()->phone();
        $this->assertStringContainsString('Android', $ua);
        $this->assertMatchesRegularExpression('/Chrome\//', $ua);
    }

    public function test_android_chrome_tablet(): void
    {
        $ua = $this->builder->androidChrome()->tablet();
        $this->assertStringContainsString('Android', $ua);
        $this->assertMatchesRegularExpression('/Chrome\//', $ua);
    }
}
