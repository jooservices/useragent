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
 * Microsoft Edge browser template.
 *
 * Supports: Desktop (Windows, macOS), Mobile (Android, iOS)
 * Current stable: 144 (as of Jan 2026)
 * Min supported: 90 (~2 years back)
 */
final class EdgeTemplate extends BrowserTemplate
{
    public function getBrowser(): BrowserFamily
    {
        return BrowserFamily::Edge;
    }

    public function getEngine(): Engine
    {
        return Engine::Blink;
    }

    /**
     * @return array<DeviceType>
     */
    public function getSupportedDevices(): array
    {
        return [DeviceType::Desktop, DeviceType::Mobile, DeviceType::Tablet];
    }

    /**
     * @return array<OperatingSystem>
     */
    public function getSupportedOs(DeviceType $device): array
    {
        return match ($device) {
            DeviceType::Desktop => [
                OperatingSystem::Windows,
                OperatingSystem::MacOS,
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
        return 144; // Latest stable as of Jan 2026
    }

    public function getMinVersion(): int
    {
        return 90; // ~2 years back
    }

    public function getMaxVersion(): int
    {
        return 144; // Latest released
    }

    public function getMarketShare(): MarketShare
    {
        return new MarketShare(5.0); // 5% overall market share
    }

    public function getRiskLevel(): RiskLevel
    {
        return RiskLevel::Low;
    }

    public function getDesktopTemplate(OperatingSystem $os): string
    {
        return match ($os) {
            OperatingSystem::Windows => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{version}.0.0.0 Safari/537.36 Edg/{version}.0.0.0',
            OperatingSystem::MacOS => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{version}.0.0.0 Safari/537.36 Edg/{version}.0.0.0',
            default => '',
        };
    }

    public function getMobileTemplate(OperatingSystem $os): string
    {
        return match ($os) {
            OperatingSystem::Android => 'Mozilla/5.0 (Linux; Android {os_version}; {model}) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/{version}.0.0.0 Mobile Safari/537.36 EdgA/{version}.0.0.0',
            OperatingSystem::iOS => 'Mozilla/5.0 (iPhone; CPU iPhone OS {os_version} like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 EdgiOS/{version}.0.0.0 Mobile/15E148 Safari/604.1',
            default => '',
        };
    }

    public function getEngineVersion(int $browserVersion): string
    {
        // Edge uses Chromium/Blink, version matches
        return (string) $browserVersion;
    }
}
