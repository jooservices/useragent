<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Pickers;

use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Spec\GenerationSpec;
use JOOservices\UserAgent\Templates\Catalogs\ArchCatalog;

/**
 * Picks CPU architecture based on device type and weights.
 */
final class ArchPicker
{
    /**
     * Pick architecture for given device type.
     */
    public function pick(DeviceType $device, GenerationSpec $spec, ?int $seed = null): string
    {
        // Use spec arch if provided
        if ($spec->arch !== null) {
            return $spec->arch;
        }

        // Otherwise use weighted random from catalog
        $isMobile = in_array($device, [DeviceType::Mobile, DeviceType::Tablet], true);

        return ArchCatalog::getRandomArchitecture($seed, $isMobile);
    }
}
