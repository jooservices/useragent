<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Templates\Browsers;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;
use JOOservices\UserAgent\Domain\ValueObjects\MarketShare;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Firefox browser template.
 *
 * Supports: Desktop (Windows, macOS, Linux), Mobile (Android, iOS)
 * Current stable: 147 (as of Jan 2026)
 * Min supported: 100 (~2 years back)
 */
final class FirefoxTemplate extends BrowserTemplate
{
    public function getBrowser(): BrowserFamily
    {
        return BrowserFamily::Firefox;
    }

    public function getEngine(): Engine
    {
        return Engine::Gecko;
    }

    public function getSupportedDevices(): array
    {
        return [DeviceType::Desktop, DeviceType::Mobile, DeviceType::Tablet];
    }

    public function getSupportedOs(DeviceType $device): array
    {
        return match ($device) {
            DeviceType::Desktop => [
                OperatingSystem::Windows,
                OperatingSystem::MacOS,
                OperatingSystem::Linux,
            ],
            DeviceType::Mobile, DeviceType::Tablet => [
                OperatingSystem::Android,
                OperatingSystem::iOS,
            ],
            default => [],
        };
    }

    public function getStableVersion(): int
    {
        return 147; // Latest stable as of Jan 2026
    }

    public function getMinVersion(): int
    {
        return 100; // ~2 years back
    }

    public function getMaxVersion(): int
    {
        return 147; // Latest released
    }

    public function getMarketShare(): MarketShare
    {
        return new MarketShare(3.0); // 3% overall market share
    }

    public function getRiskLevel(): RiskLevel
    {
        return RiskLevel::Low;
    }

    public function getDesktopTemplate(OperatingSystem $os): string
    {
        return match ($os) {
            OperatingSystem::Windows => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:{version}.0) Gecko/20100101 Firefox/{version}.0',
            OperatingSystem::MacOS => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:{version}.0) Gecko/20100101 Firefox/{version}.0',
            OperatingSystem::Linux => 'Mozilla/5.0 (X11; Linux x86_64; rv:{version}.0) Gecko/20100101 Firefox/{version}.0',
            default => '',
        };
    }

    public function getMobileTemplate(OperatingSystem $os): string
    {
        return match ($os) {
            OperatingSystem::Android => 'Mozilla/5.0 (Android {os_version}; Mobile; rv:{version}.0) Gecko/{version}.0 Firefox/{version}.0',
            OperatingSystem::iOS => 'Mozilla/5.0 (iPhone; CPU iPhone OS {os_version} like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) FxiOS/{version}.0 Mobile/15E148 Safari/605.1.15',
            default => '',
        };
    }

    public function getEngineVersion(int $browserVersion): string
    {
        // Gecko version matches Firefox version
        return (string) $browserVersion;
    }
}
