<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain\Enums;

use Faker\Factory;
use Faker\Generator;
use JOOservices\UserAgent\Domain\Enums\DeviceType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JOOservices\UserAgent\Domain\Enums\DeviceType
 */
final class DeviceTypeTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function test_has_all_expected_cases(): void
    {
        $cases = DeviceType::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(DeviceType::Desktop, $cases);
        $this->assertContains(DeviceType::Mobile, $cases);
        $this->assertContains(DeviceType::Tablet, $cases);
        $this->assertContains(DeviceType::Bot, $cases);
    }

    public function test_values_are_lowercase_strings(): void
    {
        $this->assertSame('desktop', DeviceType::Desktop->value);
        $this->assertSame('mobile', DeviceType::Mobile->value);
        $this->assertSame('tablet', DeviceType::Tablet->value);
        $this->assertSame('bot', DeviceType::Bot->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Desktop', DeviceType::Desktop->label());
        $this->assertSame('Mobile', DeviceType::Mobile->label());
        $this->assertSame('Tablet', DeviceType::Tablet->label());
        $this->assertSame('Bot', DeviceType::Bot->label());
    }

    public function test_can_be_instantiated_from_value(): void
    {
        $this->assertSame(DeviceType::Desktop, DeviceType::from('desktop'));
        $this->assertSame(DeviceType::Mobile, DeviceType::from('mobile'));
    }

    public function test_random_case_selection_with_faker(): void
    {
        $randomCase = $this->faker->randomElement(DeviceType::cases());

        $this->assertInstanceOf(DeviceType::class, $randomCase);
        $this->assertNotEmpty($randomCase->value);
        $this->assertNotEmpty($randomCase->label());
    }
}
