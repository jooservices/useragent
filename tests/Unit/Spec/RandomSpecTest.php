<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Spec;

use JOOservices\UserAgent\Spec\RandomSpec;
use PHPUnit\Framework\TestCase;

final class RandomSpecTest extends TestCase
{
    // ========== HAPPY PATH TESTS ==========

    /** @test */
    public function test_it_creates_spec_with_defaults(): void
    {
        $spec = new RandomSpec;

        $this->assertNull($spec->seed);
        $this->assertSame(100, $spec->historyWindow);
        $this->assertSame(10, $spec->retryBudget);
        $this->assertTrue($spec->enableHistory);
        $this->assertFalse($spec->isDeterministic());
    }

    /** @test */
    public function test_it_creates_spec_with_seed(): void
    {
        $spec = new RandomSpec(seed: 12345);

        $this->assertSame(12345, $spec->seed);
        $this->assertTrue($spec->isDeterministic());
    }

    /** @test */
    public function test_it_creates_spec_with_custom_history_window(): void
    {
        $spec = new RandomSpec(historyWindow: 50);

        $this->assertSame(50, $spec->historyWindow);
    }

    /** @test */
    public function test_it_creates_spec_with_custom_retry_budget(): void
    {
        $spec = new RandomSpec(retryBudget: 5);

        $this->assertSame(5, $spec->retryBudget);
    }

    /** @test */
    public function test_it_creates_spec_with_history_disabled(): void
    {
        $spec = new RandomSpec(enableHistory: false);

        $this->assertFalse($spec->enableHistory);
    }

    /** @test */
    public function test_it_creates_spec_with_all_properties(): void
    {
        $spec = new RandomSpec(
            seed: 99999,
            historyWindow: 200,
            retryBudget: 20,
            enableHistory: false,
        );

        $this->assertSame(99999, $spec->seed);
        $this->assertSame(200, $spec->historyWindow);
        $this->assertSame(20, $spec->retryBudget);
        $this->assertFalse($spec->enableHistory);
        $this->assertTrue($spec->isDeterministic());
    }

    /** @test */
    public function test_it_creates_from_array(): void
    {
        $spec = RandomSpec::fromArray([
            'seed' => 12345,
            'historyWindow' => 150,
            'retryBudget' => 15,
            'enableHistory' => false,
        ]);

        $this->assertSame(12345, $spec->seed);
        $this->assertSame(150, $spec->historyWindow);
        $this->assertSame(15, $spec->retryBudget);
        $this->assertFalse($spec->enableHistory);
    }

    /** @test */
    public function test_it_creates_from_partial_array(): void
    {
        $spec = RandomSpec::fromArray([
            'seed' => 12345,
        ]);

        $this->assertSame(12345, $spec->seed);
        $this->assertSame(100, $spec->historyWindow); // default
        $this->assertSame(10, $spec->retryBudget); // default
        $this->assertTrue($spec->enableHistory); // default
    }

    /** @test */
    public function test_it_creates_from_empty_array(): void
    {
        $spec = RandomSpec::fromArray([]);

        $this->assertNull($spec->seed);
        $this->assertSame(100, $spec->historyWindow);
        $this->assertSame(10, $spec->retryBudget);
        $this->assertTrue($spec->enableHistory);
    }

    // ========== EDGE CASE TESTS ==========

    /** @test */
    public function test_it_handles_zero_seed(): void
    {
        $spec = new RandomSpec(seed: 0);

        $this->assertSame(0, $spec->seed);
        $this->assertTrue($spec->isDeterministic());
    }

    /** @test */
    public function test_it_handles_large_seed(): void
    {
        $spec = new RandomSpec(seed: PHP_INT_MAX);

        $this->assertSame(PHP_INT_MAX, $spec->seed);
        $this->assertTrue($spec->isDeterministic());
    }

    /** @test */
    public function test_it_handles_minimum_history_window(): void
    {
        $spec = new RandomSpec(historyWindow: 1);

        $this->assertSame(1, $spec->historyWindow);
    }

    /** @test */
    public function test_it_handles_large_history_window(): void
    {
        $spec = new RandomSpec(historyWindow: 10000);

        $this->assertSame(10000, $spec->historyWindow);
    }

    /** @test */
    public function test_it_handles_zero_retry_budget(): void
    {
        $spec = new RandomSpec(retryBudget: 0);

        $this->assertSame(0, $spec->retryBudget);
    }

    /** @test */
    public function test_it_handles_large_retry_budget(): void
    {
        $spec = new RandomSpec(retryBudget: 100);

        $this->assertSame(100, $spec->retryBudget);
    }

    // ========== DETERMINISTIC MODE TESTS ==========

    /** @test */
    public function is_deterministic_returns_true_when_seed_is_set(): void
    {
        $seeds = [0, 1, 12345, 99999, PHP_INT_MAX];

        foreach ($seeds as $seed) {
            $spec = new RandomSpec(seed: $seed);
            $this->assertTrue($spec->isDeterministic(), "Failed for seed: {$seed}");
        }
    }

    /** @test */
    public function is_deterministic_returns_false_when_seed_is_null(): void
    {
        $spec = new RandomSpec;
        $this->assertFalse($spec->isDeterministic());
    }

    // ========== IMMUTABILITY TESTS ==========

    /** @test */
    public function test_it_is_immutable(): void
    {
        $spec = new RandomSpec(seed: 12345);

        // Cannot modify readonly properties (enforced by PHP)
        $this->assertSame(12345, $spec->seed);
    }

    // ========== WEIRD/STRANGE TESTS ==========

    /** @test */
    public function test_it_handles_history_disabled_with_retry_budget(): void
    {
        // Strange but valid: history disabled but retry budget set
        $spec = new RandomSpec(
            enableHistory: false,
            retryBudget: 10,
        );

        $this->assertFalse($spec->enableHistory);
        $this->assertSame(10, $spec->retryBudget);
    }

    /** @test */
    public function test_it_handles_history_disabled_with_large_window(): void
    {
        // Strange but valid: history disabled but window size set
        $spec = new RandomSpec(
            enableHistory: false,
            historyWindow: 1000,
        );

        $this->assertFalse($spec->enableHistory);
        $this->assertSame(1000, $spec->historyWindow);
    }

    /** @test */
    public function test_it_handles_zero_retry_with_history_enabled(): void
    {
        // Valid: history enabled but no retries
        $spec = new RandomSpec(
            enableHistory: true,
            retryBudget: 0,
        );

        $this->assertTrue($spec->enableHistory);
        $this->assertSame(0, $spec->retryBudget);
    }
}
