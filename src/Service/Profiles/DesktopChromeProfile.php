<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Service\Profiles;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;

/**
 * Pre-configured profile for desktop Chrome.
 */
final class DesktopChromeProfile
{
    public function __construct(
        private readonly UserAgentService $service
    ) {}

    /**
     * Generate desktop Chrome UA on Windows.
     */
    public function windows(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Chrome)
                ->device(DeviceType::Desktop)
                ->os(OperatingSystem::Windows)
                ->build(),
            $seed
        );
    }

    /**
     * Generate desktop Chrome UA on macOS.
     */
    public function macos(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Chrome)
                ->device(DeviceType::Desktop)
                ->os(OperatingSystem::MacOS)
                ->build(),
            $seed
        );
    }

    /**
     * Generate desktop Chrome UA on Linux.
     */
    public function linux(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Chrome)
                ->device(DeviceType::Desktop)
                ->os(OperatingSystem::Linux)
                ->build(),
            $seed
        );
    }

    /**
     * Generate desktop Chrome UA on any OS (random).
     */
    public function any(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Chrome)
                ->device(DeviceType::Desktop)
                ->build(),
            $seed
        );
    }
}
