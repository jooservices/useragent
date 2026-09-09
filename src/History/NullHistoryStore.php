<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\History;

use JOOservices\UserAgent\Contract\HistoryStore;

final class NullHistoryStore implements HistoryStore
{
    #[\Override]
    public function contains(string $userAgent): bool
    {
        return false;
    }
    #[\Override]
    public function add(string $userAgent): void
    {
    }
    #[\Override]
    public function size(): int
    {
        return 0;
    }
    #[\Override]
    public function clear(): void
    {
    }
}
