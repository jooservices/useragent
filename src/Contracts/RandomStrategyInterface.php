<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contracts;

use JOOservices\UserAgent\Spec\RandomSpec;

/**
 * Random strategy interface for selecting from candidates.
 *
 * Implementations define different randomization algorithms.
 */
interface RandomStrategyInterface
{
    /**
     * Select an index from candidates array based on strategy.
     *
     * @param array<int, mixed> $candidates
     */
    public function select(array $candidates, RandomSpec $spec): int;
}
