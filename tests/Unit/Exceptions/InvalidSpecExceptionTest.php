<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Exceptions;

use JOOservices\UserAgent\Exceptions\InvalidSpecException;
use JOOservices\UserAgent\Exceptions\UserAgentException;
use PHPUnit\Framework\TestCase;

final class InvalidSpecExceptionTest extends TestCase
{
    public function test_it_extends_user_agent_exception(): void
    {
        $exception = InvalidSpecException::invalidVersionRange(120, 110);

        $this->assertInstanceOf(UserAgentException::class, $exception);
        $this->assertInstanceOf(InvalidSpecException::class, $exception);
    }

    public function test_invalid_filter_spec(): void
    {
        $exception = InvalidSpecException::invalidFilterSpec('Missing required field');

        $this->assertSame('Invalid filter spec: Missing required field', $exception->getMessage());
    }

    public function test_invalid_random_spec(): void
    {
        $exception = InvalidSpecException::invalidRandomSpec('Seed must be positive');

        $this->assertSame('Invalid random spec: Seed must be positive', $exception->getMessage());
    }

    public function test_invalid_version_range(): void
    {
        $exception = InvalidSpecException::invalidVersionRange(120, 110);

        $this->assertSame('versionMin (120) cannot be greater than versionMax (110)', $exception->getMessage());
    }

    public function test_version_exact_conflict(): void
    {
        $exception = InvalidSpecException::versionExactConflict();

        $this->assertSame('Cannot use versionExact with versionMin/versionMax', $exception->getMessage());
    }

    public function test_invalid_version_min(): void
    {
        $exception = InvalidSpecException::invalidVersionMin(0);

        $this->assertSame('versionMin must be >= 1, got 0', $exception->getMessage());
    }

    public function test_invalid_version_max(): void
    {
        $exception = InvalidSpecException::invalidVersionMax(-5);

        $this->assertSame('versionMax must be >= 1, got -5', $exception->getMessage());
    }

    public function test_invalid_version_exact(): void
    {
        $exception = InvalidSpecException::invalidVersionExact(-10);

        $this->assertSame('versionExact must be >= 1, got -10', $exception->getMessage());
    }

    public function test_version_too_high(): void
    {
        $exception = InvalidSpecException::versionTooHigh('versionMin', 1000);

        $this->assertSame('versionMin too high (1000), maximum is 999', $exception->getMessage());
    }

    public function test_invalid_channel(): void
    {
        $exception = InvalidSpecException::invalidChannel('nightly', ['stable', 'beta', 'dev', 'canary']);

        $this->assertSame("Invalid channel 'nightly'. Must be one of: stable, beta, dev, canary", $exception->getMessage());
    }

    public function test_invalid_arch(): void
    {
        $exception = InvalidSpecException::invalidArch('x86', ['x86_64', 'ARM', 'ARM64']);

        $this->assertSame("Invalid arch 'x86'. Must be one of: x86_64, ARM, ARM64", $exception->getMessage());
    }

    public function test_invalid_locale(): void
    {
        $exception = InvalidSpecException::invalidLocale('english');

        $this->assertSame("Invalid locale format 'english'. Expected format: 'en-US', 'fr-FR', etc.", $exception->getMessage());
    }

    public function test_invalid_tag(): void
    {
        $exception = InvalidSpecException::invalidTag(123);

        $this->assertStringContainsString('Tag must be non-empty string', $exception->getMessage());
        $this->assertStringContainsString('int', $exception->getMessage());
    }

    public function test_invalid_history_window(): void
    {
        $exception = InvalidSpecException::invalidHistoryWindow(0);

        $this->assertSame('historyWindow must be >= 1, got 0', $exception->getMessage());
    }

    public function test_history_window_too_large(): void
    {
        $exception = InvalidSpecException::historyWindowTooLarge(10001);

        $this->assertSame('historyWindow too large (10001), maximum is 10000', $exception->getMessage());
    }

    public function test_invalid_retry_budget(): void
    {
        $exception = InvalidSpecException::invalidRetryBudget(-1);

        $this->assertSame('retryBudget must be >= 0, got -1', $exception->getMessage());
    }

    public function test_retry_budget_too_large(): void
    {
        $exception = InvalidSpecException::retryBudgetTooLarge(101);

        $this->assertSame('retryBudget too large (101), maximum is 100', $exception->getMessage());
    }

    public function test_invalid_seed(): void
    {
        $exception = InvalidSpecException::invalidSeed(-1);

        $this->assertSame('seed must be >= 0, got -1', $exception->getMessage());
    }

    public function test_version_below_minimum(): void
    {
        $exception = InvalidSpecException::versionBelowMinimum(50, 90);

        $this->assertSame('versionMin (50) is below template minimum (90)', $exception->getMessage());
    }

    public function test_version_above_maximum(): void
    {
        $exception = InvalidSpecException::versionAboveMaximum(200, 145);

        $this->assertSame('versionMax (200) is above template maximum (145)', $exception->getMessage());
    }

    public function test_version_out_of_range(): void
    {
        $exception = InvalidSpecException::versionOutOfRange(50, 90, 145);

        $this->assertSame('versionExact (50) is outside template range (90-145)', $exception->getMessage());
    }
}
