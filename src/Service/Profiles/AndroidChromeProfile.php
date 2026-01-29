<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Service\Profiles;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;

/**
 * Pre-configured profile for Android Chrome.
 */
final class AndroidChromeProfile
{
    public function __construct(
        private readonly UserAgentService $service
    ) {
    }

    /**
     * Generate Android Chrome UA on mobile phone.
     */
    public function phone(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Chrome)
                ->device(DeviceType::Mobile)
                ->os(OperatingSystem::Android)
                ->build(),
            $seed
        );
    }

    /**
     * Generate Android Chrome UA on tablet.
     */
    public function tablet(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Chrome)
                ->device(DeviceType::Tablet)
                ->os(OperatingSystem::Android)
                ->build(),
            $seed
        );
    }

    /**
     * Generate Android Chrome UA on any device (random).
     */
    public function any(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Chrome)
                ->device(DeviceType::Mobile)
                ->os(OperatingSystem::Android)
                ->build(),
            $seed
        );
    }
}
