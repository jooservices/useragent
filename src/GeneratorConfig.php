<?php

declare(strict_types=1);

namespace JOOservices\UserAgent;

use JOOservices\UserAgent\Exceptions\InvalidRequestException;

final readonly class GeneratorConfig
{
    public function __construct(
        public int $historySize = 10_000,
        public int $retryMultiplier = 20,
        public int $maxAttempts = 10_000,
    ) {
        if ($historySize < 1 || $retryMultiplier < 1 || $maxAttempts < 1) {
            throw new InvalidRequestException('Generator limits must be positive.');
        }
    }

    public function attemptBudget(int $count): int
    {
        return min($this->maxAttempts, max(50, $count * $this->retryMultiplier));
    }
}
