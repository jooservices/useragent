<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\Enums;

/**
 * Operating system enumeration.
 */
enum OperatingSystem: string
{
    case Windows = 'windows';
    case MacOS = 'macos';
    case Linux = 'linux';
    case Android = 'android';
    case iOS = 'ios';
    case ChromeOS = 'chromeos';
    case Other = 'other';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Windows => 'Windows',
            self::MacOS => 'macOS',
            self::Linux => 'Linux',
            self::Android => 'Android',
            self::iOS => 'iOS',
            self::ChromeOS => 'ChromeOS',
            self::Other => 'Other',
        };
    }
}
