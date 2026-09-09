<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contract;

interface RandomSource
{
    public function int(int $min, int $max): int;

    public function float(): float;

    /** @param array<array-key, mixed> $items */
    public function key(array $items): int | string;

    /**
     * @param array<array-key, mixed> $items
     * @param array<array-key, int|float> $weights
     */
    public function weighted(array $items, array $weights): mixed;
}
