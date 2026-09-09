<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Core;

use JOOservices\UserAgent\Contract\RandomSource;
use JOOservices\UserAgent\Contract\SelectionPolicy;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;

final class WeightedPolicy implements SelectionPolicy
{
    #[\Override]
    public function selectIndex(array $candidates, RandomSource $random): int
    {
        if ($candidates === []) {
            throw new InvalidRequestException('Selection candidates cannot be empty.');
        }
        $indices = array_keys($candidates);
        $weights = [];
        foreach ($candidates as $index => $candidate) {
            if (!is_array($candidate) || !isset($candidate['weight']) || (!is_int($candidate['weight']) && !is_float($candidate['weight']))) {
                throw new InvalidRequestException('Weighted candidates must contain numeric weights.');
            }
            $weights[$index] = $candidate['weight'];
        }

        /** @var int $selected */
        $selected = $random->weighted($indices, $weights);

        return $selected;
    }
}
