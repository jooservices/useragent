<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contracts;

use JOOservices\UserAgent\Domain\ValueObjects\UserAgent;

/**
 * Filter interface for matching user-agent candidates.
 *
 * Implementations must be immutable and have single responsibility.
 */
interface FilterInterface
{
    /**
     * Check if user-agent matches filter criteria.
     */
    public function matches(UserAgent $ua): bool;
}
