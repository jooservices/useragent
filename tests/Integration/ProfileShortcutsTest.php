<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Integration;

use JOOservices\UserAgent\Service\Profiles\Profiles;
use JOOservices\UserAgent\Service\UserAgentService;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for profile shortcuts.
 */
final class ProfileShortcutsTest extends TestCase
{
    private Profiles $profiles;

    protected function setUp(): void
    {
        $service = new UserAgentService;
        $this->profiles = new Profiles($service);
    }

    public function test_desktop_chrome_profile_generates_valid_uas(): void
    {
        $ua = $this->profiles->desktopChrome->windows(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Windows', $ua);
        $this->assertStringContainsString('Chrome', $ua);
    }

    public function test_mobile_safari_profile_generates_valid_uas(): void
    {
        $ua = $this->profiles->mobileSafari->iphone(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('iPhone', $ua);
    }

    public function test_android_chrome_profile_generates_valid_uas(): void
    {
        $ua = $this->profiles->androidChrome->phone(12345);

        $this->assertNotEmpty($ua);
        $this->assertStringContainsString('Android', $ua);
        $this->assertStringContainsString('Chrome', $ua);
    }

    public function test_random_profiles_generate_different_uas(): void
    {
        $mobile1 = $this->profiles->randomMobile(111);
        $mobile2 = $this->profiles->randomMobile(222);

        // Different seeds should produce different UAs
        $this->assertNotSame($mobile1, $mobile2);
    }

    public function test_all_profiles_are_deterministic(): void
    {
        $ua1 = $this->profiles->firefox(99999);
        $ua2 = $this->profiles->firefox(99999);

        $this->assertSame($ua1, $ua2);
    }
}
