<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Market share value object (0-100).
 */
final readonly class MarketShare
{
    public function __construct(
        public float $percentage,
    ) {
        if ($percentage < 0.0 || $percentage > 100.0) {
            throw new InvalidArgumentException(
                sprintf('Market share must be between 0 and 100, got: %f', $percentage)
            );
        }
    }

    /**
     * Create from percentage value.
     */
    public static function fromPercentage(float $percentage): self
    {
        return new self($percentage);
    }

    /**
     * Create from decimal (0.0-1.0).
     */
    public static function fromDecimal(float $decimal): self
    {
        return new self($decimal * 100.0);
    }

    /**
     * Get as decimal (0.0-1.0).
     */
    public function toDecimal(): float
    {
        return $this->percentage / 100.0;
    }
}
