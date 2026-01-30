<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Facade;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Service\Profiles\AndroidChromeProfile;
use JOOservices\UserAgent\Service\Profiles\DesktopChromeProfile;
use JOOservices\UserAgent\Service\Profiles\MobileSafariProfile;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;

/**
 * Fluent builder for accessing pre-configured profiles.
 *
 * Usage:
 * UserAgent::profile()->desktopChrome()->windows();
 * UserAgent::profile()->mobileSafari()->iphone();
 * UserAgent::profile()->randomMobile();
 */
final class ProfileBuilder
{
    public function __construct(
        private readonly UserAgentService $service
    ) {}

    /**
     * Get Desktop Chrome profile.
     */
    public function desktopChrome(): DesktopChromeProfile
    {
        return new DesktopChromeProfile($this->service);
    }

    /**
     * Get Mobile Safari profile.
     */
    public function mobileSafari(): MobileSafariProfile
    {
        return new MobileSafariProfile($this->service);
    }

    /**
     * Get Android Chrome profile.
     */
    public function androidChrome(): AndroidChromeProfile
    {
        return new AndroidChromeProfile($this->service);
    }

    /**
     * Generate random mobile UA (any browser, any mobile OS).
     */
    public function randomMobile(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->device(DeviceType::Mobile)
                ->build(),
            $seed
        );
    }

    /**
     * Generate random desktop UA (any browser, any desktop OS).
     */
    public function randomDesktop(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->device(DeviceType::Desktop)
                ->build(),
            $seed
        );
    }

    /**
     * Generate Firefox UA (any OS).
     */
    public function firefox(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Firefox)
                ->build(),
            $seed
        );
    }

    /**
     * Generate Edge UA (any OS).
     */
    public function edge(?int $seed = null): string
    {
        return $this->service->generate(
            GenerationSpec::create()
                ->browser(BrowserFamily::Edge)
                ->build(),
            $seed
        );
    }
}
