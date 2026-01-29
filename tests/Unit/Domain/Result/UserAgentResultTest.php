<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Domain\Result;

use Faker\Factory;
use Faker\Generator;
use JOOservices\UserAgent\Domain\Result\UserAgentResult;
use JOOservices\UserAgent\Domain\ValueObjects\UserAgent;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \JOOservices\UserAgent\Domain\Result\UserAgentResult
 */
final class UserAgentResultTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function test_creates_with_minimal_data(): void
    {
        $ua = UserAgent::fromString($this->faker->userAgent());
        $strategy = $this->faker->word();

        $result = new UserAgentResult($ua, $strategy);

        $this->assertSame($ua, $result->userAgent);
        $this->assertSame($strategy, $result->appliedStrategy);
        $this->assertSame(1.0, $result->confidence);
        $this->assertEmpty($result->debug);
        $this->assertFalse($result->hasDebug());
    }

    public function test_creates_with_full_data(): void
    {
        $ua = UserAgent::fromString($this->faker->userAgent());
        $strategy = $this->faker->word();
        $confidence = $this->faker->randomFloat(2, 0, 1);
        $debug = ['reason' => $this->faker->sentence()];

        $result = new UserAgentResult($ua, $strategy, $confidence, $debug);

        $this->assertSame($confidence, $result->confidence);
        $this->assertSame($debug, $result->debug);
        $this->assertTrue($result->hasDebug());
    }

    public function test_to_string_returns_ua_string(): void
    {
        $uaString = $this->faker->userAgent();
        $ua = UserAgent::fromString($uaString);
        $result = new UserAgentResult($ua, 'test');

        $this->assertSame($uaString, $result->toString());
    }

    public function test_is_readonly(): void
    {
        $ua = UserAgent::fromString($this->faker->userAgent());
        $result = new UserAgentResult($ua, 'test');

        $reflection = new ReflectionClass($result);

        $this->assertTrue($reflection->isReadOnly());
    }
}
