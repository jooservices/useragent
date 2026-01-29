<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\Enums;

/**
 * Risk level enumeration for bot detection probability.
 */
enum RiskLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low Risk',
            self::Medium => 'Medium Risk',
            self::High => 'High Risk',
        };
    }

    /**
     * Get numeric score for comparison.
     */
    public function score(): int
    {
        return match ($this) {
            self::Low => 1,
            self::Medium => 2,
            self::High => 3,
        };
    }
}
