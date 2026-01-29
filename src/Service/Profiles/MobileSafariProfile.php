<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Service\Profiles;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;

/**
 * Pre-configured profile for mobile Safari.
 */
final class MobileSafariProfile
{
    public function __construct(
        private readonly UserAgentService $service
    ) {
    }

    /**
     * Generate mobile Safari UA on iPhone.
     */
    public function iphone(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Safari)
                ->device(DeviceType::Mobile)
                ->os(OperatingSystem::iOS)
                ->build(),
            $seed
        );
    }

    /**
     * Generate mobile Safari UA on iPad.
     */
    public function ipad(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Safari)
                ->device(DeviceType::Tablet)
                ->os(OperatingSystem::iOS)
                ->build(),
            $seed
        );
    }

    /**
     * Generate mobile Safari UA on any iOS device (random).
     */
    public function any(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Safari)
                ->device(DeviceType::Mobile)
                ->os(OperatingSystem::iOS)
                ->build(),
            $seed
        );
    }
}
