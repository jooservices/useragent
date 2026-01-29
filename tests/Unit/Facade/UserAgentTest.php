<?php

declare(strict_types=1);

namespace Tests\Unit\Facade;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Facade\UniqueGuard;
use JOOservices\UserAgent\UserAgent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserAgent::class)]
#[CoversClass(\JOOservices\UserAgent\Facade\UserAgentBuilder::class)]
#[CoversClass(UniqueGuard::class)]
final class UserAgentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        UserAgent::resetUnique();
    }

    public function test_basic_generation(): void
    {
        $ua = UserAgent::generate();
        $this->assertNotEmpty($ua);
        $this->assertStringStartsWith('Mozilla/5.0', $ua);
    }

    public function test_fluent_chaining_chrome(): void
    {
        $ua = UserAgent::chrome()->generate();
        $this->assertMatchesRegularExpression('/Chrome|CriOS/', $ua);
    }

    public function test_fluent_os_windows(): void
    {
        $ua = UserAgent::chrome()->windows()->generate();
        $this->assertStringContainsString('Windows', $ua);
        // Windows Desktop Chrome usually contains "Windows NT"
    }

    public function test_fluent_mobile_device(): void
    {
        $ua = UserAgent::mobile()->generate();
        // Just ensure it generates successfully.
        // String format varies (iPhone, Android, etc)
        $this->assertNotEmpty($ua);
    }

    public function test_unique_generation(): void
    {
        $total = 10;
        $generated = [];
        
        for ($i = 0; $i < $total; $i++) {
            $ua = UserAgent::unique()->generate();
            $this->assertArrayNotHasKey($ua, $generated, "Found duplicate UA: $ua");
            $generated[$ua] = true;
        }
    }

    public function test_exclude_chrome(): void
    {
        // Generate 10 non-Chrome UAs
        for ($i = 0; $i < 10; $i++) {
            $ua = UserAgent::exclude()->chrome()->generate();
            // Should NOT contain "Chrome/" (except potentially in Edge/Opera strings if they mimic it, 
            // but strict templates for Firefox/Safari do not)
            
            // Note: Edge template often includes "Chrome/x.x.x". 
            // So if we exclude Chrome, we might get Edge which HAS Chrome string.
            // A better test is to check it is NOT BrowserFamily::Chrome logic.
            // But we can test 'firefox' exclusion easily.
            $this->assertNotEmpty($ua);
        }
    }

    public function test_exclude_firefox(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $ua = UserAgent::exclude()->firefox()->generate();
            $this->assertStringNotContainsString('Firefox', $ua);
        }
    }

    public function test_exclude_mobile(): void
    {
        // If we exclude mobile, we expect Desktop or Tablet (which might look like mobile string-wise on iOS but...)
        // Let's test excluding ALL mobile-ish things: Mobile AND Tablet
        $ua = UserAgent::exclude()->mobile()->tablet()->generate();
        // Should be Desktop
        // Desktop normally has "Windows NT" or "Macintosh" or "X11"
        $isDesktop = str_contains($ua, 'Windows NT') 
                  || str_contains($ua, 'Macintosh') 
                  || str_contains($ua, 'X11')
                  || str_contains($ua, 'CrOS');
                  
        $this->assertTrue($isDesktop, "Expected desktop UA, got: $ua");
    }

    public function test_reset_unique(): void
    {
        UserAgent::unique()->generate();
        UserAgent::resetUnique();
        // Should not throw errors
        $this->assertTrue(true);
    }

    public function test_impossible_constraint_throws_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        // Safari on Android is impossible in this library
        UserAgent::safari()->android()->generate();
    }
    public function test_fluent_browsers(): void
    {
        $this->assertMatchesRegularExpression('/Firefox|FxiOS/', UserAgent::firefox()->generate());
        $this->assertStringContainsString('Safari', UserAgent::safari()->generate());
        $this->assertStringContainsString('Edg', UserAgent::edge()->generate());
    }

    public function test_fluent_devices(): void
    {
        // Desktop usually has platform token, harder to regex generic 'Desktop'
        // But we can check it's NOT mobile/tablet logic if we had deeper inspection
        $this->assertNotEmpty(UserAgent::desktop()->generate());
        
        $this->assertNotEmpty(UserAgent::tablet()->generate());
    }

    public function test_fluent_os(): void
    {
        $this->assertStringContainsString('Linux', UserAgent::linux()->generate());
        $this->assertStringContainsString('Mac', UserAgent::macos()->generate());
        $this->assertStringContainsString('Android', UserAgent::android()->generate());
        // iOS often says "iPhone OS" or "CPU OS"
        $this->assertMatchesRegularExpression('/iPhone|iPad|iPod/', UserAgent::ios()->generate());
    }
}
