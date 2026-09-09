<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use JOOservices\UserAgent\Contract\RandomSource;
use JOOservices\UserAgent\Contract\SelectionPolicy;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;

final class UniformPolicy implements SelectionPolicy
{
    #[\Override]
    public function selectIndex(array $candidates, RandomSource $random): int
    {
        if ($candidates === []) {
            throw new InvalidRequestException('Selection candidates cannot be empty.');
        }

        return $random->int(0, count($candidates) - 1);
    }
}
