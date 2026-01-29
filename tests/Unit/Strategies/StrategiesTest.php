<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Strategies;

use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use JOOservices\UserAgent\Strategies\AvoidRecentStrategy;
use JOOservices\UserAgent\Strategies\RoundRobinStrategy;
use JOOservices\UserAgent\Strategies\UniformStrategy;
use JOOservices\UserAgent\Strategies\WeightedStrategy;
use PHPUnit\Framework\TestCase;

/**
 * Consolidated tests for all strategy classes.
 */
final class StrategiesTest extends TestCase
{
    // ========== UNIFORM STRATEGY TESTS ==========

    public function test_uniform_strategy_returns_browser_template(): void
    {
        $strategy = new UniformStrategy();

        $template = $strategy->select();

        $this->assertNotNull($template);
        $this->assertContains($template->getBrowser(), [
            BrowserFamily::Chrome,
            BrowserFamily::Firefox,
            BrowserFamily::Safari,
            BrowserFamily::Edge,
        ]);
    }

    public function test_uniform_strategy_is_deterministic_with_seed(): void
    {
        $strategy = new UniformStrategy();

        $template1 = $strategy->select(12345);
        $template2 = $strategy->select(12345);

        $this->assertSame($template1->getBrowser(), $template2->getBrowser());
    }

    public function test_uniform_strategy_returns_all_templates(): void
    {
        $strategy = new UniformStrategy();

        $templates = $strategy->getTemplates();

        $this->assertCount(4, $templates);
    }

    public function test_uniform_strategy_has_equal_distribution(): void
    {
        $strategy = new UniformStrategy();
        $counts = [];

        // Run many times to check distribution
        for ($i = 0; $i < 100; $i++) {
            $template = $strategy->select();
            $browser = $template->getBrowser()->value;
            $counts[$browser] = ($counts[$browser] ?? 0) + 1;
        }

        // All browsers should be selected at least once
        $this->assertCount(4, $counts);
    }

    // ========== WEIGHTED STRATEGY TESTS ==========

    public function test_weighted_strategy_returns_browser_template(): void
    {
        $strategy = new WeightedStrategy();

        $template = $strategy->select();

        $this->assertNotNull($template);
        $this->assertContains($template->getBrowser(), [
            BrowserFamily::Chrome,
            BrowserFamily::Firefox,
            BrowserFamily::Safari,
            BrowserFamily::Edge,
        ]);
    }

    public function test_weighted_strategy_is_deterministic_with_seed(): void
    {
        $strategy = new WeightedStrategy();

        $template1 = $strategy->select(99999);
        $template2 = $strategy->select(99999);

        $this->assertSame($template1->getBrowser(), $template2->getBrowser());
    }

    public function test_weighted_strategy_returns_templates_with_weights(): void
    {
        $strategy = new WeightedStrategy();

        $templatesWithWeights = $strategy->getTemplatesWithWeights();

        $this->assertCount(4, $templatesWithWeights);
        foreach ($templatesWithWeights as $item) {
            $this->assertArrayHasKey('template', $item);
            $this->assertArrayHasKey('weight', $item);
            $this->assertGreaterThan(0, $item['weight']);
        }
    }

    public function test_weighted_strategy_total_weight_matches_sum(): void
    {
        $strategy = new WeightedStrategy();

        $totalWeight = $strategy->getTotalWeight();
        $templatesWithWeights = $strategy->getTemplatesWithWeights();

        $sum = array_sum(array_column($templatesWithWeights, 'weight'));

        $this->assertEquals($sum, $totalWeight, '', 0.01);
    }

    public function test_weighted_strategy_prefers_chrome(): void
    {
        $strategy = new WeightedStrategy();
        $counts = [];

        // Run many times to check distribution
        for ($i = 0; $i < 200; $i++) {
            $template = $strategy->select();
            $browser = $template->getBrowser()->value;
            $counts[$browser] = ($counts[$browser] ?? 0) + 1;
        }

        // Chrome should be selected most often (64% market share)
        $this->assertGreaterThan($counts['firefox'] ?? 0, $counts['chrome'] ?? 0);
        $this->assertGreaterThan($counts['edge'] ?? 0, $counts['chrome'] ?? 0);
    }

    // ========== ROUND ROBIN STRATEGY TESTS ==========

    public function test_round_robin_strategy_cycles_through_browsers(): void
    {
        $strategy = new RoundRobinStrategy();

        $browsers = [];
        for ($i = 0; $i < 4; $i++) {
            $template = $strategy->select();
            $browsers[] = $template->getBrowser()->value;
        }

        // Should have all 4 different browsers
        $this->assertCount(4, array_unique($browsers));
    }

    public function test_round_robin_strategy_wraps_around(): void
    {
        $strategy = new RoundRobinStrategy();

        // Get first browser
        $first = $strategy->select()->getBrowser()->value;

        // Cycle through all 4
        for ($i = 0; $i < 3; $i++) {
            $strategy->select();
        }

        // Should wrap back to first
        $wrapped = $strategy->select()->getBrowser()->value;

        $this->assertSame($first, $wrapped);
    }

    public function test_round_robin_strategy_resets_to_start(): void
    {
        $strategy = new RoundRobinStrategy();

        $first = $strategy->select()->getBrowser()->value;
        $strategy->select();
        $strategy->select();

        $strategy->reset();

        $afterReset = $strategy->select()->getBrowser()->value;

        $this->assertSame($first, $afterReset);
    }

    public function test_round_robin_strategy_tracks_current_index(): void
    {
        $strategy = new RoundRobinStrategy();

        $this->assertSame(0, $strategy->getCurrentIndex());

        $strategy->select();
        $this->assertSame(1, $strategy->getCurrentIndex());

        $strategy->select();
        $this->assertSame(2, $strategy->getCurrentIndex());
    }

    public function test_round_robin_strategy_returns_correct_count(): void
    {
        $strategy = new RoundRobinStrategy();

        $this->assertSame(4, $strategy->getCount());
    }

    // ========== AVOID RECENT STRATEGY TESTS ==========

    public function test_avoid_recent_strategy_returns_browser_template(): void
    {
        $strategy = new AvoidRecentStrategy();

        $template = $strategy->select();

        $this->assertNotNull($template);
        $this->assertContains($template->getBrowser(), [
            BrowserFamily::Chrome,
            BrowserFamily::Firefox,
            BrowserFamily::Safari,
            BrowserFamily::Edge,
        ]);
    }

    public function test_avoid_recent_strategy_avoids_repetition(): void
    {
        $strategy = new AvoidRecentStrategy(historySize: 3);

        $first = $strategy->select(12345)->getBrowser()->value;
        $second = $strategy->select(12346)->getBrowser()->value;

        // Second selection should be different from first
        $this->assertNotSame($first, $second);
    }

    public function test_avoid_recent_strategy_tracks_history(): void
    {
        $strategy = new AvoidRecentStrategy(historySize: 5);

        $this->assertSame(0, $strategy->getHistorySize());

        $strategy->select();
        $this->assertSame(1, $strategy->getHistorySize());

        $strategy->select();
        $this->assertSame(2, $strategy->getHistorySize());
    }

    public function test_avoid_recent_strategy_clears_history(): void
    {
        $strategy = new AvoidRecentStrategy();

        $strategy->select();
        $strategy->select();
        $this->assertGreaterThan(0, $strategy->getHistorySize());

        $strategy->clearHistory();

        $this->assertSame(0, $strategy->getHistorySize());
    }

    public function test_avoid_recent_strategy_eventually_allows_repetition_when_all_used(): void
    {
        $strategy = new AvoidRecentStrategy(historySize: 10, maxRetries: 2);

        // Fill history with all 4 browsers
        for ($i = 0; $i < 4; $i++) {
            $strategy->select();
        }

        // Should still be able to select (will pick despite being in history)
        $template = $strategy->select();
        $this->assertNotNull($template);
    }

    public function test_avoid_recent_strategy_is_deterministic_with_seed(): void
    {
        $strategy1 = new AvoidRecentStrategy();
        $strategy2 = new AvoidRecentStrategy();

        $template1 = $strategy1->select(77777);
        $template2 = $strategy2->select(77777);

        $this->assertSame($template1->getBrowser(), $template2->getBrowser());
    }
}
