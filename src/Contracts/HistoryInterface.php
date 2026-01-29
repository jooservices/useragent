<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contracts;

/**
 * History interface for tracking selected user-agents.
 *
 * Used by high-entropy strategies to avoid repetition.
 */
interface HistoryInterface
{
    /**
     * Add user-agent string to history.
     */
    public function add(string $userAgent): void;

    /**
     * Check if user-agent was recently selected.
     */
    public function contains(string $userAgent): bool;

    /**
     * Get recent history entries.
     *
     * @return array<string>
     */
    public function getRecent(int $limit): array;

    /**
     * Clear all history.
     */
    public function clear(): void;
}
