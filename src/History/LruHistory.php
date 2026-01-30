<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\History;

/**
 * LRU (Least Recently Used) history tracker for generated user-agents.
 *
 * Prevents generating the same UA string repeatedly by tracking recent generations.
 */
final class LruHistory
{
    /** @var array<string, int> */
    private array $history = [];

    private int $accessCounter = 0;

    public function __construct(
        private readonly int $maxSize = 100
    ) {}

    /**
     * Check if UA was recently generated.
     */
    public function contains(string $userAgent): bool
    {
        return isset($this->history[$userAgent]);
    }

    /**
     * Add UA to history.
     */
    public function add(string $userAgent): void
    {
        $this->history[$userAgent] = ++$this->accessCounter;

        // Evict oldest if over capacity
        if (count($this->history) > $this->maxSize) {
            $this->evictOldest();
        }
    }

    /**
     * Get history size.
     */
    public function size(): int
    {
        return count($this->history);
    }

    /**
     * Clear all history.
     */
    public function clear(): void
    {
        $this->history = [];
        $this->accessCounter = 0;
    }

    /**
     * Get all entries (for testing).
     *
     * @return array<string, int>
     */
    public function getAll(): array
    {
        return $this->history;
    }

    /**
     * Evict the least recently used entry.
     */
    private function evictOldest(): void
    {
        $oldest = null;
        $oldestTime = PHP_INT_MAX;

        foreach ($this->history as $ua => $time) {
            if ($time < $oldestTime) {
                $oldestTime = $time;
                $oldest = $ua;
            }
        }

        if ($oldest !== null) {
            unset($this->history[$oldest]);
        }
    }
}
