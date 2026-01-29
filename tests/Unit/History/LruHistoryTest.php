<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\History;

use JOOservices\UserAgent\History\LruHistory;
use PHPUnit\Framework\TestCase;

final class LruHistoryTest extends TestCase
{
    public function test_it_starts_empty(): void
    {
        $history = new LruHistory();

        $this->assertSame(0, $history->size());
        $this->assertFalse($history->contains('test'));
    }

    public function test_it_adds_entries(): void
    {
        $history = new LruHistory();

        $history->add('UA1');
        $history->add('UA2');

        $this->assertSame(2, $history->size());
        $this->assertTrue($history->contains('UA1'));
        $this->assertTrue($history->contains('UA2'));
    }

    public function test_it_does_not_contain_non_existent_entry(): void
    {
        $history = new LruHistory();
        $history->add('UA1');

        $this->assertFalse($history->contains('UA2'));
    }

    public function test_it_evicts_oldest_when_over_capacity(): void
    {
        $history = new LruHistory(maxSize: 3);

        $history->add('UA1');
        $history->add('UA2');
        $history->add('UA3');
        $history->add('UA4'); // Should evict UA1

        $this->assertSame(3, $history->size());
        $this->assertFalse($history->contains('UA1'));
        $this->assertTrue($history->contains('UA2'));
        $this->assertTrue($history->contains('UA3'));
        $this->assertTrue($history->contains('UA4'));
    }

    public function test_it_evicts_least_recently_used(): void
    {
        $history = new LruHistory(maxSize: 2);

        $history->add('UA1');
        $history->add('UA2');
        $history->add('UA3'); // Should evict UA1 (oldest)

        $this->assertFalse($history->contains('UA1'));
        $this->assertTrue($history->contains('UA2'));
        $this->assertTrue($history->contains('UA3'));
    }

    public function test_it_clears_all_entries(): void
    {
        $history = new LruHistory();
        $history->add('UA1');
        $history->add('UA2');

        $history->clear();

        $this->assertSame(0, $history->size());
        $this->assertFalse($history->contains('UA1'));
        $this->assertFalse($history->contains('UA2'));
    }

    public function test_it_returns_all_entries(): void
    {
        $history = new LruHistory();
        $history->add('UA1');
        $history->add('UA2');

        $all = $history->getAll();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('UA1', $all);
        $this->assertArrayHasKey('UA2', $all);
    }

    public function test_it_tracks_access_order(): void
    {
        $history = new LruHistory();
        $history->add('UA1');
        $history->add('UA2');
        $history->add('UA3');

        $all = $history->getAll();

        // Access times should be increasing
        $this->assertLessThan($all['UA2'], $all['UA1']);
        $this->assertLessThan($all['UA3'], $all['UA2']);
    }

    public function test_it_handles_duplicate_adds(): void
    {
        $history = new LruHistory();
        $history->add('UA1');
        $history->add('UA1'); // Duplicate

        // Should update access time, not add duplicate
        $this->assertSame(1, $history->size());
        $this->assertTrue($history->contains('UA1'));
    }

    public function test_it_evicts_correctly_with_many_entries(): void
    {
        $history = new LruHistory(maxSize: 5);

        for ($i = 1; $i <= 10; $i++) {
            $history->add("UA{$i}");
        }

        $this->assertSame(5, $history->size());
        // Should contain last 5
        $this->assertTrue($history->contains('UA6'));
        $this->assertTrue($history->contains('UA7'));
        $this->assertTrue($history->contains('UA8'));
        $this->assertTrue($history->contains('UA9'));
        $this->assertTrue($history->contains('UA10'));
        // Should not contain first 5
        $this->assertFalse($history->contains('UA1'));
        $this->assertFalse($history->contains('UA5'));
    }

    public function test_it_handles_single_entry_capacity(): void
    {
        $history = new LruHistory(maxSize: 1);

        $history->add('UA1');
        $history->add('UA2');

        $this->assertSame(1, $history->size());
        $this->assertFalse($history->contains('UA1'));
        $this->assertTrue($history->contains('UA2'));
    }

    public function test_it_clears_access_counter_on_clear(): void
    {
        $history = new LruHistory();
        $history->add('UA1');
        $history->add('UA2');

        $history->clear();
        $history->add('UA3');

        $all = $history->getAll();
        // Access counter should restart from 1
        $this->assertSame(1, $all['UA3']);
    }
}
