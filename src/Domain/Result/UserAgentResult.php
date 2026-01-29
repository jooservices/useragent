<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Domain\Result;

use JOOservices\UserAgent\Domain\ValueObjects\UserAgent;

/**
 * User-agent selection result with debug information.
 */
final readonly class UserAgentResult
{
    /**
     * @param array<string, mixed> $debug
     */
    public function __construct(
        public UserAgent $userAgent,
        public string $appliedStrategy,
        public float $confidence = 1.0,
        public array $debug = [],
    ) {
    }

    /**
     * Get UA string shortcut.
     */
    public function toString(): string
    {
        return $this->userAgent->toString();
    }

    /**
     * Check if result has debug information.
     */
    public function hasDebug(): bool
    {
        return count($this->debug) > 0;
    }
}
