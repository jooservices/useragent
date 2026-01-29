<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Exceptions;

use Faker\Factory;
use Faker\Generator;
use JOOservices\UserAgent\Exceptions\NoCandidateException;
use JOOservices\UserAgent\Exceptions\UserAgentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JOOservices\UserAgent\Exceptions\NoCandidateException
 */
final class NoCandidateExceptionTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function test_extends_base_exception(): void
    {
        $exception = NoCandidateException::fromFilters('test');

        $this->assertInstanceOf(UserAgentException::class, $exception);
    }

    public function test_from_filters_creates_exception_with_reason(): void
    {
        $reason = $this->faker->sentence();
        $exception = NoCandidateException::fromFilters($reason);

        $this->assertStringContainsString($reason, $exception->getMessage());
        $this->assertStringContainsString('No user-agent candidates found', $exception->getMessage());
    }

    public function test_after_max_attempts_creates_exception_with_count(): void
    {
        $attempts = $this->faker->numberBetween(1, 100);
        $exception = NoCandidateException::afterMaxAttempts($attempts);

        $this->assertStringContainsString((string) $attempts, $exception->getMessage());
        $this->assertStringContainsString('No candidates found after', $exception->getMessage());
    }
}
