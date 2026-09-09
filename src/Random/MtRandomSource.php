<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Random;

use JOOservices\UserAgent\Contract\RandomSource;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;
use Random\Engine\Mt19937;
use Random\IntervalBoundary;
use Random\Randomizer;

final class MtRandomSource implements RandomSource
{
    public function __construct(private readonly Randomizer $randomizer = new Randomizer())
    {
    }

    public static function seeded(int $seed): self
    {
        return new self(new Randomizer(new Mt19937($seed)));
    }

    #[\Override]
    public function int(int $min, int $max): int
    {
        return $this->randomizer->getInt($min, $max);
    }

    #[\Override]
    public function float(): float
    {
        return $this->randomizer->getFloat(0.0, 1.0, IntervalBoundary::ClosedOpen);
    }

    #[\Override]
    public function key(array $items): int | string
    {
        if ($items === []) {
            throw new InvalidRequestException('Cannot select from an empty collection.');
        }
        $keys = array_keys($items);

        return $keys[$this->int(0, count($keys) - 1)];
    }

    #[\Override]
    public function weighted(array $items, array $weights): mixed
    {
        if ($items === [] || array_keys($items) !== array_keys($weights)) {
            throw new InvalidRequestException('Weighted items and weights must be non-empty with identical keys.');
        }
        $total = 0.0;
        foreach ($weights as $weight) {
            if ($weight <= 0) {
                throw new InvalidRequestException('Weights must be greater than zero.');
            }
            $total += $weight;
        }
        $needle = $this->float() * $total;
        $cumulative = 0.0;
        foreach ($items as $key => $item) {
            $cumulative += $weights[$key];
            if ($needle < $cumulative) {
                return $item;
            }
        }

        return end($items);
    }
}
