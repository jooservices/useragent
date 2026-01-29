<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain\Enums;

use JOOservices\UserAgent\Domain\Enums\RiskLevel;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JOOservices\UserAgent\Domain\Enums\RiskLevel
 */
class RiskLevelTest extends TestCase
{
    public function test_has_three_risk_levels(): void
    {
        $cases = RiskLevel::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(RiskLevel::Low, $cases);
        $this->assertContains(RiskLevel::Medium, $cases);
        $this->assertContains(RiskLevel::High, $cases);
    }

    public function test_scores_are_ascending(): void
    {
        $this->assertSame(1, RiskLevel::Low->score());
        $this->assertSame(2, RiskLevel::Medium->score());
        $this->assertSame(3, RiskLevel::High->score());
    }

    public function test_labels_are_descriptive(): void
    {
        $this->assertSame('Low Risk', RiskLevel::Low->label());
        $this->assertSame('Medium Risk', RiskLevel::Medium->label());
        $this->assertSame('High Risk', RiskLevel::High->label());
    }

    public function test_can_compare_risk_levels_by_score(): void
    {
        $low = RiskLevel::Low;
        $medium = RiskLevel::Medium;
        $high = RiskLevel::High;

        $this->assertLessThan($medium->score(), $low->score());
        $this->assertLessThan($high->score(), $medium->score());
        $this->assertGreaterThan($low->score(), $high->score());
    }
}
