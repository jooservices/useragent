<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Templates;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;
use JOOservices\UserAgent\Domain\ValueObjects\MarketShare;

/**
 * Abstract template for browser user-agents.
 *
 * Each browser implementation defines:
 * - Supported OS/device combinations
 * - Version ranges
 * - Token structure
 * - Market share data
 */
abstract class BrowserTemplate
{
    /**
     * Get browser family.
     */
    abstract public function getBrowser(): BrowserFamily;

    /**
     * Get rendering engine.
     */
    abstract public function getEngine(): Engine;

    /**
     * Get supported devices for this browser.
     *
     * @return array<DeviceType>
     */
    abstract public function getSupportedDevices(): array;

    /**
     * Get supported OS for this browser on given device.
     *
     * @return array<OperatingSystem>
     */
    abstract public function getSupportedOs(DeviceType $device): array;

    /**
     * Get current stable version.
     */
    abstract public function getStableVersion(): int;

    /**
     * Get minimum supported version (for version range).
     */
    abstract public function getMinVersion(): int;

    /**
     * Get maximum version (latest released).
     */
    abstract public function getMaxVersion(): int;

    /**
     * Get market share for this browser.
     */
    abstract public function getMarketShare(): MarketShare;

    /**
     * Get risk level for this browser.
     */
    abstract public function getRiskLevel(): RiskLevel;

    /**
     * Get template structure for desktop UA.
     *
     * Placeholders: {version}, {os}, {arch}, {locale}, {engine_version}
     */
    abstract public function getDesktopTemplate(OperatingSystem $os): string;

    /**
     * Get template structure for mobile UA.
     *
     * Placeholders: {version}, {os}, {os_version}, {model}, {build}, {engine_version}
     */
    abstract public function getMobileTemplate(OperatingSystem $os): string;

    /**
     * Get engine version for given browser version.
     */
    abstract public function getEngineVersion(int $browserVersion): string;

    /**
     * Get tags for this template.
     *
     * @return array<string>
     */
    public function getTags(): array
    {
        return ['browser', strtolower($this->getBrowser()->value)];
    }

    /**
     * Check if browser supports given device type.
     */
    public function supportsDevice(DeviceType $device): bool
    {
        return in_array($device, $this->getSupportedDevices(), true);
    }

    /**
     * Check if browser supports given OS on device.
     */
    public function supportsOs(DeviceType $device, OperatingSystem $os): bool
    {
        if (! $this->supportsDevice($device)) {
            return false;
        }

        return in_array($os, $this->getSupportedOs($device), true);
    }
}
