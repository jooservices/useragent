<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Random;

use JOOservices\UserAgent\Core\RoundRobinPolicy;
use JOOservices\UserAgent\Core\UniformPolicy;
use JOOservices\UserAgent\Core\WeightedPolicy;
use JOOservices\UserAgent\Exceptions\InvalidRequestException;
use JOOservices\UserAgent\History\InMemoryHistoryStore;
use JOOservices\UserAgent\History\NullHistoryStore;
use JOOservices\UserAgent\Random\MtRandomSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RandomAndHistoryTest extends TestCase
{
    #[Test]
    public function test_SEL_H01_H03_policies_return_valid_indices(): void
    {
        $random = MtRandomSource::seeded(42);
        self::assertContains((new UniformPolicy())->selectIndex([1, 2], $random), [0, 1]);
        self::assertContains((new WeightedPolicy())->selectIndex([['weight' => 1], ['weight' => 2]], $random), [0, 1]);
        $roundRobin = new RoundRobinPolicy();
        self::assertSame([0, 1, 0], [
            $roundRobin->selectIndex([1, 2], $random),
            $roundRobin->selectIndex([1, 2], $random),
            $roundRobin->selectIndex([1, 2], $random),
        ]);
        self::assertLessThan(1.0, $random->float());
    }

    #[Test]
    public function test_SEL_U01_empty_selection_throws(): void
    {
        /** @var list<mixed> $empty */
        $empty = [];
        $this->expectException(InvalidRequestException::class);
        (new UniformPolicy())->selectIndex($empty, MtRandomSource::seeded(1));
    }

    #[Test]
    public function test_HISTORY_lru_is_bounded_and_clearable(): void
    {
        $history = new InMemoryHistoryStore(2);
        $history->add('a');
        $history->add('b');
        $history->add('c');
        self::assertFalse($history->contains('a'));
        self::assertTrue($history->contains('c'));
        self::assertSame(2, $history->size());
        $history->clear();
        self::assertSame(0, $history->size());
    }

    #[Test]
    public function test_RANDOM_weight_validation_and_null_history(): void
    {
        $random = MtRandomSource::seeded(0);
        self::assertContains($random->key(['a', 'b']), [0, 1]);
        foreach ([[[], []], [['a'], [0]], [['a'], [1, 2]]] as [$items, $weights]) {
            try {
                $random->weighted($items, $weights);
                self::fail('Expected invalid weights.');
            } catch (InvalidRequestException) {
                self::addToAssertionCount(1);
            }
        }
        $history = new NullHistoryStore();
        self::assertFalse($history->contains('ua'));
        $history->add('ua');
        self::assertSame(0, $history->size());
        $history->clear();
    }
}
