<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Filters;

use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Templates\BrowserTemplate;

/**
 * Filter templates by operating system support.
 */
final class OsFilter
{
    private DeviceType $deviceType;

    /**
     * @param array<OperatingSystem> $allowedOs
     */
    public function __construct(
        private readonly array $allowedOs,
        ?DeviceType $deviceType = null
    ) {
        $this->deviceType = $deviceType ?? DeviceType::Desktop;
    }

    /**
     * Check if template supports any of the allowed OS.
     */
    public function matches(BrowserTemplate $template): bool
    {
        $supportedOs = $template->getSupportedOs($this->deviceType);

        foreach ($this->allowedOs as $os) {
            if (in_array($os, $supportedOs, true)) {
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
