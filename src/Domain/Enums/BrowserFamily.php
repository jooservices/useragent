<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\Enums;

/**
 * Browser family enumeration.
 */
enum BrowserFamily: string
{
    case Chrome = 'chrome';
    case Firefox = 'firefox';
    case Safari = 'safari';
    case Edge = 'edge';
    case Opera = 'opera';
    case InternetExplorer = 'ie';
    case Samsung = 'samsung';
    case Other = 'other';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Chrome => 'Chrome',
            self::Firefox => 'Firefox',
            self::Safari => 'Safari',
            self::Edge => 'Edge',
            self::Opera => 'Opera',
            self::InternetExplorer => 'Internet Explorer',
            self::Samsung => 'Samsung Internet',
            self::Other => 'Other',
        };
    }
}
