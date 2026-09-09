<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contract;

interface HistoryStore
{
    public function contains(string $userAgent): bool;
    public function add(string $userAgent): void;
    public function size(): int;
    public function clear(): void;
}
