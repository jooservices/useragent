<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\Enums;

/**
 * Browser engine enumeration.
 */
enum Engine: string
{
    case Blink = 'blink';
    case Gecko = 'gecko';
    case WebKit = 'webkit';
    case Trident = 'trident';
    case EdgeHTML = 'edgehtml';
    case Other = 'other';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Blink => 'Blink',
            self::Gecko => 'Gecko',
            self::WebKit => 'WebKit',
            self::Trident => 'Trident',
            self::EdgeHTML => 'EdgeHTML',
            self::Other => 'Other',
        };
    }
}
