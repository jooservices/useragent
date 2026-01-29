<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contracts;

use DateTimeImmutable;

/**
 * Clock interface for getting current time.
 *
 * Allows deterministic time in tests.
 */
interface ClockInterface
{
    /**
     * Get current time.
     */
    public function now(): DateTimeImmutable;
}
