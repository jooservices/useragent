<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use JOOservices\UserAgent\Contract\RandomSource;
use JOOservices\UserAgent\Contract\SelectionPolicy;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;

final class RoundRobinPolicy implements SelectionPolicy
{
    private int $next = 0;

    #[\Override]
    public function selectIndex(array $candidates, RandomSource $random): int
    {
        if ($candidates === []) {
            throw new InvalidRequestException('Selection candidates cannot be empty.');
        }
        $index = $this->next % count($candidates);
        ++$this->next;

        return $index;
    }
}
