<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain\ValueObjects;

use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use JOOservices\UserAgent\Domain\ValueObjects\MarketShare;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \JOOservices\UserAgent\Domain\ValueObjects\MarketShare
 */
final class MarketShareTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function test_creates_from_valid_percentage(): void
    {
        $percentage = $this->faker->randomFloat(2, 0, 100);
        $marketShare = new MarketShare($percentage);

        $this->assertSame($percentage, $marketShare->percentage);
    }

    public function test_creates_from_percentage_method(): void
    {
        $percentage = $this->faker->randomFloat(2, 0, 100);
        $marketShare = MarketShare::fromPercentage($percentage);

        $this->assertSame($percentage, $marketShare->percentage);
    }

    public function test_creates_from_decimal(): void
    {
        $decimal = 0.75;
        $marketShare = MarketShare::fromDecimal($decimal);

        $this->assertSame(75.0, $marketShare->percentage);
    }

    public function test_converts_to_decimal(): void
    {
        $marketShare = new MarketShare(75.0);

        $this->assertSame(0.75, $marketShare->toDecimal());
    }

    public function test_rejects_negative_percentage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Market share must be between 0 and 100');

        new MarketShare(-1.0);
    }

    public function test_rejects_percentage_above_100(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Market share must be between 0 and 100');

        new MarketShare(100.1);
    }

    public function test_accepts_boundary_values(): void
    {
        $zero = new MarketShare(0.0);
        $hundred = new MarketShare(100.0);

        $this->assertSame(0.0, $zero->percentage);
        $this->assertSame(100.0, $hundred->percentage);
    }

    public function test_is_readonly(): void
    {
        $marketShare = new MarketShare(50.0);

        $reflection = new ReflectionClass($marketShare);

        $this->assertTrue($reflection->isReadOnly());
    }
}
