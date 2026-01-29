<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Service\Profiles;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Service\UserAgentService;
use JOOservices\UserAgent\Spec\GenerationSpec;

/**
 * Convenience class for quick profile access.
 */
final class Profiles
{
    public readonly DesktopChromeProfile $desktopChrome;

    public readonly MobileSafariProfile $mobileSafari;

    public readonly AndroidChromeProfile $androidChrome;

    public function __construct(
        private readonly UserAgentService $service
    ) {
        $this->desktopChrome = new DesktopChromeProfile($service);
        $this->mobileSafari = new MobileSafariProfile($service);
        $this->androidChrome = new AndroidChromeProfile($service);
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
