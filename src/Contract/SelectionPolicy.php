<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Contract;

interface SelectionPolicy
{
    /** @param list<mixed> $candidates */
    public function selectIndex(array $candidates, RandomSource $random): int;
}
