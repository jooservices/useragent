<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use JOOservices\UserAgent\Contract\HistoryStore;
use JOOservices\UserAgent\Contract\RandomSource;

final readonly class GenerationSession
{
    public function __construct(
        public RandomSource $random,
        public HistoryStore $history,
        public ?int $seed,
    ) {
    }
}
