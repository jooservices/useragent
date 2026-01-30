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
 * Safari browser template.
 *
 * Supports: Desktop (macOS), Mobile (iOS)
 * Current stable: 26 (as of Jan 2026)
 * Min supported: 14 (~2 years back)
 */
final class SafariTemplate extends BrowserTemplate
{
    public function getBrowser(): BrowserFamily
    {
        return BrowserFamily::Safari;
    }

    public function getEngine(): Engine
    {
        return Engine::WebKit;
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
                OperatingSystem::MacOS,
            ],
            DeviceType::Mobile, DeviceType::Tablet => [
                OperatingSystem::iOS,
            ],
            default => [],
        };
    }

    public function getStableVersion(): int
    {
        return 26; // Latest stable as of Jan 2026
    }

    public function getMinVersion(): int
    {
        return 14; // ~2 years back
    }

    public function getMaxVersion(): int
    {
        return 26; // Latest released
    }

    public function getMarketShare(): MarketShare
    {
        return new MarketShare(20.0); // 20% overall market share
    }

    public function getRiskLevel(): RiskLevel
    {
        return RiskLevel::Low;
    }

    public function getDesktopTemplate(OperatingSystem $os): string
    {
        return match ($os) {
            OperatingSystem::MacOS => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{version}.0 Safari/605.1.15',
            default => '',
        };
    }

    public function getMobileTemplate(OperatingSystem $os): string
    {
        return match ($os) {
            OperatingSystem::iOS => 'Mozilla/5.0 (iPhone; CPU iPhone OS {os_version} like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/{version}.0 Mobile/15E148 Safari/604.1',
            default => '',
        };
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getEngineVersion(int $_browserVersion): string
    {
        // WebKit version is relatively stable
        return '605.1.15';
    }
}
