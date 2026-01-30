<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\ValueObjects;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use JOOservices\UserAgent\Domain\Enums\Engine;
use JOOservices\UserAgent\Domain\Enums\OperatingSystem;
use JOOservices\UserAgent\Domain\Enums\RiskLevel;

/**
 * User-agent metadata value object.
 */
final readonly class Meta
{
    /**
     * @param array<string> $tags
     */
    public function __construct(
        public ?DeviceType $device = null,
        public ?OperatingSystem $os = null,
        public ?BrowserFamily $browser = null,
        public ?Engine $engine = null,
        public ?int $version = null,
        public ?MarketShare $marketShare = null,
        public ?RiskLevel $riskLevel = null,
        public array $tags = [],
    ) {}
}
