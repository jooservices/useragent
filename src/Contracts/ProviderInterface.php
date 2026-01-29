<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contracts;

use JOOservices\UserAgent\Domain\ValueObjects\UserAgent;

/**
 * Provider interface for supplying user-agent strings with metadata.
 *
 * Implementations must be lazy-loaded and validate data on load.
 */
interface ProviderInterface
{
    /**
     * Check if provider can supply data without actually loading it.
     */
    public function supports(): bool;

    /**
     * Load and return user-agent collection.
     *
     * @return array<UserAgent>
     */
    public function load(): array;
}
