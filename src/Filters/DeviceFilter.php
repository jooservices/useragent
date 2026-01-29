<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Filters;

use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Filter templates by supported device types.
 */
final class DeviceFilter
{
    /**
     * @param array<DeviceType> $allowedDevices
     */
    public function __construct(
        private readonly array $allowedDevices
    ) {
    }

    /**
     * Check if template supports any of the allowed devices.
     */
    public function matches(BrowserTemplate $template): bool
    {
        $supportedDevices = $template->getSupportedDevices();

        foreach ($this->allowedDevices as $device) {
            if (in_array($device, $supportedDevices, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter array of templates.
     *
     * @param array<BrowserTemplate> $templates
     *
     * @return array<BrowserTemplate>
     */
    public function filter(array $templates): array
    {
        return array_filter(
            $templates,
            fn (BrowserTemplate $template) => $this->matches($template)
        );
    }
}
