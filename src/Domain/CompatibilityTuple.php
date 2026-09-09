<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

use JOOservices\Dto\Core\Dto;

final class CompatibilityTuple extends Dto
{
    public function __construct(
        public readonly BrowserFamily $browser,
        public readonly DeviceClass $device,
        public readonly Platform $platform,
    ) {
    }
}
