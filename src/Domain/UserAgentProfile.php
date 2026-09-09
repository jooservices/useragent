<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain;

use JOOservices\Dto\Core\Dto;

final class UserAgentProfile extends Dto
{
    public function __construct(
        public readonly BrowserFamily $browser,
        public readonly DeviceClass $device,
        public readonly Platform $platform,
        public readonly Engine $engine,
        public readonly string $browserVersion,
        public readonly int $browserMajor,
        public readonly string $osVersion,
        public readonly string $osToken,
        public readonly Architecture $architecture,
        public readonly string $archToken,
        public readonly ?string $model,
        public readonly ?string $deviceToken,
        public readonly ?string $locale,
        public readonly bool $localeRendered,
        public readonly string $releasedAt,
        public readonly DatasetProvenance $provenance,
    ) {
    }

    public function identity(): string
    {
        return sprintf(
            '%s/%s %s/%s %s',
            $this->browser->value,
            $this->browserVersion,
            $this->platform->value,
            $this->device->value,
            $this->architecture->value,
        );
    }
}
