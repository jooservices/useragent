<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\Enums;

/**
 * Device type enumeration.
 */
enum DeviceType: string
{
    case Desktop = 'desktop';
    case Mobile = 'mobile';
    case Tablet = 'tablet';
    case Bot = 'bot';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Desktop => 'Desktop',
            self::Mobile => 'Mobile',
            self::Tablet => 'Tablet',
            self::Bot => 'Bot',
        };
    }
}
