<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\History;

use JOOservices\UserAgent\Contract\HistoryStore;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;

final class InMemoryHistoryStore implements HistoryStore
{
    /** @var list<string> */
    private array $entries = [];

    public function __construct(private readonly int $maxSize = 10_000)
    {
        if ($maxSize < 1) {
            throw new InvalidRequestException('History size must be at least one.');
        }
    }

    #[\Override]
    public function contains(string $userAgent): bool
    {
        return in_array($userAgent, $this->entries, true);
    }

    #[\Override]
    public function add(string $userAgent): void
    {
        $index = array_search($userAgent, $this->entries, true);
        if ($index !== false) {
            array_splice($this->entries, $index, 1);
        }
        $this->entries[] = $userAgent;
        if (count($this->entries) > $this->maxSize) {
            array_shift($this->entries);
        }
    }

    #[\Override]
    public function size(): int
    {
        return count($this->entries);
    }

    #[\Override]
    public function clear(): void
    {
        $this->entries = [];
    }
}
