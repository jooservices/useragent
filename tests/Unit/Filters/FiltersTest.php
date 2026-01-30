<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Filters;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;
use JOOservices\UserAgent\Filters\BrowserFilter;
use JOOservices\UserAgent\Filters\CompositeFilter;
use JOOservices\UserAgent\Filters\DeviceFilter;
use JOOservices\UserAgent\Filters\EngineFilter;
use JOOservices\UserAgent\Filters\OsFilter;
use JOOservices\UserAgent\Filters\RiskLevelFilter;
use JOOservices\UserAgent\Filters\VersionRangeFilter;
use JOOservices\UserAgent\Templates\Browsers\ChromeTemplate;
use JOOservices\UserAgent\Templates\Browsers\EdgeTemplate;
use JOOservices\UserAgent\Templates\Browsers\FirefoxTemplate;
use JOOservices\UserAgent\Templates\Browsers\SafariTemplate;
use PHPUnit\Framework\TestCase;

final class FiltersTest extends TestCase
{
    private ChromeTemplate $chrome;

    private FirefoxTemplate $firefox;

    private SafariTemplate $safari;

    private EdgeTemplate $edge;

    /** @var array<\JOOservices\UserAgent\Templates\BrowserTemplate> */
    private array $allTemplates;

    protected function setUp(): void
    {
        $this->chrome = new ChromeTemplate;
        $this->firefox = new FirefoxTemplate;
        $this->safari = new SafariTemplate;
        $this->edge = new EdgeTemplate;
        $this->allTemplates = [$this->chrome, $this->firefox, $this->safari, $this->edge];
    }

    // BrowserFilter Tests
    public function test_browser_filter_matches_single_browser(): void
    {
        $filter = new BrowserFilter([BrowserFamily::Chrome]);

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertFalse($filter->matches($this->firefox));
    }

    public function test_browser_filter_matches_multiple_browsers(): void
    {
        $filter = new BrowserFilter([BrowserFamily::Chrome, BrowserFamily::Firefox]);

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertTrue($filter->matches($this->firefox));
        $this->assertFalse($filter->matches($this->safari));
    }

    public function test_browser_filter_filters_array(): void
    {
        $filter = new BrowserFilter([BrowserFamily::Chrome]);
        $result = $filter->filter($this->allTemplates);

        $this->assertCount(1, $result);
        $this->assertContains($this->chrome, $result);
    }

    // DeviceFilter Tests
    public function test_device_filter_matches_desktop(): void
    {
        $filter = new DeviceFilter([DeviceType::Desktop]);

        $this->assertTrue($filter->matches($this->chrome)); // Supports desktop
        $this->assertTrue($filter->matches($this->safari)); // Supports desktop
    }

    public function test_device_filter_matches_mobile(): void
    {
        $filter = new DeviceFilter([DeviceType::Mobile]);

        $this->assertTrue($filter->matches($this->chrome)); // Supports mobile
        $this->assertTrue($filter->matches($this->safari)); // Supports mobile
    }

    public function test_device_filter_filters_array(): void
    {
        $filter = new DeviceFilter([DeviceType::Desktop]);
        $result = $filter->filter($this->allTemplates);

        $this->assertGreaterThan(0, count($result));
    }

    // OsFilter Tests
    public function test_os_filter_matches_windows(): void
    {
        $filter = new OsFilter([OperatingSystem::Windows], DeviceType::Desktop);

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertTrue($filter->matches($this->firefox));
        $this->assertFalse($filter->matches($this->safari)); // Safari doesn't support Windows
    }

    public function test_os_filter_matches_macos(): void
    {
        $filter = new OsFilter([OperatingSystem::MacOS], DeviceType::Desktop);

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertTrue($filter->matches($this->safari));
    }

    public function test_os_filter_matches_android(): void
    {
        $filter = new OsFilter([OperatingSystem::Android], DeviceType::Mobile);

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertTrue($filter->matches($this->firefox));
        $this->assertFalse($filter->matches($this->safari)); // Safari doesn't support Android
    }

    public function test_os_filter_filters_array(): void
    {
        $filter = new OsFilter([OperatingSystem::Windows], DeviceType::Desktop);
        $result = $filter->filter($this->allTemplates);

        $this->assertGreaterThan(0, count($result));
        $this->assertNotContains($this->safari, $result);
    }

    // EngineFilter Tests
    public function test_engine_filter_matches_blink(): void
    {
        $filter = new EngineFilter([Engine::Blink]);

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertTrue($filter->matches($this->edge));
        $this->assertFalse($filter->matches($this->firefox));
        $this->assertFalse($filter->matches($this->safari));
    }

    public function test_engine_filter_matches_gecko(): void
    {
        $filter = new EngineFilter([Engine::Gecko]);

        $this->assertTrue($filter->matches($this->firefox));
        $this->assertFalse($filter->matches($this->chrome));
    }

    public function test_engine_filter_matches_webkit(): void
    {
        $filter = new EngineFilter([Engine::WebKit]);

        $this->assertTrue($filter->matches($this->safari));
        $this->assertFalse($filter->matches($this->chrome));
    }

    public function test_engine_filter_filters_array(): void
    {
        $filter = new EngineFilter([Engine::Blink]);
        $result = $filter->filter($this->allTemplates);

        $this->assertCount(2, $result); // Chrome and Edge
    }

    // VersionRangeFilter Tests
    public function test_version_range_filter_no_constraints_matches_all(): void
    {
        $filter = new VersionRangeFilter;

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertTrue($filter->matches($this->firefox));
    }

    public function test_version_range_filter_min_version(): void
    {
        $filter = new VersionRangeFilter(minVersion: 100);

        $this->assertTrue($filter->matches($this->chrome)); // Chrome max is 145
        $this->assertTrue($filter->matches($this->firefox)); // Firefox max is 147
    }

    public function test_version_range_filter_max_version(): void
    {
        $filter = new VersionRangeFilter(maxVersion: 100);

        $this->assertTrue($filter->matches($this->chrome)); // Chrome min is 90
        $this->assertTrue($filter->matches($this->firefox)); // Firefox min is 100
    }

    public function test_version_range_filter_range(): void
    {
        $filter = new VersionRangeFilter(minVersion: 100, maxVersion: 150);

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertTrue($filter->matches($this->firefox));
    }

    public function test_version_range_filter_excludes_out_of_range(): void
    {
        $filter = new VersionRangeFilter(minVersion: 200, maxVersion: 300);

        $this->assertFalse($filter->matches($this->chrome)); // Max is 145
        $this->assertFalse($filter->matches($this->firefox)); // Max is 147
    }

    public function test_version_range_filter_filters_array(): void
    {
        $filter = new VersionRangeFilter(minVersion: 100);
        $result = $filter->filter($this->allTemplates);

        $this->assertGreaterThan(0, count($result));
    }

    // RiskLevelFilter Tests
    public function test_risk_level_filter_matches_low(): void
    {
        $filter = new RiskLevelFilter([RiskLevel::Low]);

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertTrue($filter->matches($this->firefox));
    }

    public function test_risk_level_filter_filters_array(): void
    {
        $filter = new RiskLevelFilter([RiskLevel::Low]);
        $result = $filter->filter($this->allTemplates);

        $this->assertGreaterThan(0, count($result));
    }

    // CompositeFilter Tests
    public function test_composite_filter_and_logic(): void
    {
        $filter = new CompositeFilter([
            new BrowserFilter([BrowserFamily::Chrome, BrowserFamily::Edge]),
            new EngineFilter([Engine::Blink]),
        ], useAndLogic: true);

        $this->assertTrue($filter->matches($this->chrome)); // Chrome AND Blink
        $this->assertTrue($filter->matches($this->edge)); // Edge AND Blink
        $this->assertFalse($filter->matches($this->firefox)); // Firefox but not Blink
    }

    public function test_composite_filter_or_logic(): void
    {
        $filter = new CompositeFilter([
            new BrowserFilter([BrowserFamily::Chrome]),
            new BrowserFilter([BrowserFamily::Firefox]),
        ], useAndLogic: false);

        $this->assertTrue($filter->matches($this->chrome)); // Matches first filter
        $this->assertTrue($filter->matches($this->firefox)); // Matches second filter
        $this->assertFalse($filter->matches($this->safari)); // Matches neither
    }

    public function test_composite_filter_empty_filters_matches_all(): void
    {
        $filter = new CompositeFilter([]);

        $this->assertTrue($filter->matches($this->chrome));
        $this->assertTrue($filter->matches($this->firefox));
    }

    public function test_composite_filter_complex_and_logic(): void
    {
        $filter = new CompositeFilter([
            new DeviceFilter([DeviceType::Desktop]),
            new OsFilter([OperatingSystem::Windows], DeviceType::Desktop),
            new EngineFilter([Engine::Blink]),
        ], useAndLogic: true);

        $this->assertTrue($filter->matches($this->chrome)); // All conditions met
        $this->assertFalse($filter->matches($this->safari)); // Doesn't support Windows
    }

    public function test_composite_filter_filters_array(): void
    {
        $filter = new CompositeFilter([
            new BrowserFilter([BrowserFamily::Chrome]),
            new EngineFilter([Engine::Blink]),
        ], useAndLogic: true);

        $result = $filter->filter($this->allTemplates);

        $this->assertCount(1, $result);
        $this->assertContains($this->chrome, $result);
    }
}
