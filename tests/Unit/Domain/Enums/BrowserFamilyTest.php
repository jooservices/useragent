<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain\Enums;

use Faker\Factory;
use Faker\Generator;
use JOOservices\UserAgent\Domain\Enums\BrowserFamily;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JOOservices\UserAgent\Domain\Enums\BrowserFamily
 */
final class BrowserFamilyTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function test_has_all_expected_cases(): void
    {
        $cases = BrowserFamily::cases();

        $this->assertGreaterThanOrEqual(8, count($cases));
        $this->assertContains(BrowserFamily::Chrome, $cases);
        $this->assertContains(BrowserFamily::Firefox, $cases);
        $this->assertContains(BrowserFamily::Safari, $cases);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Chrome', BrowserFamily::Chrome->label());
        $this->assertSame('Firefox', BrowserFamily::Firefox->label());
        $this->assertSame('Safari', BrowserFamily::Safari->label());
        $this->assertSame('Edge', BrowserFamily::Edge->label());
        $this->assertSame('Internet Explorer', BrowserFamily::InternetExplorer->label());
    }

    public function test_random_case_has_valid_label(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $randomCase = $this->faker->randomElement(BrowserFamily::cases());

            $this->assertInstanceOf(BrowserFamily::class, $randomCase);

            $label = $randomCase->label();

            $this->assertNotEmpty($label);
        }
    }
}
