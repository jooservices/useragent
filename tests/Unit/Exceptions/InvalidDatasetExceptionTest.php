<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Exceptions;

use Faker\Factory;
use Faker\Generator;
use JOOservices\UserAgent\Exceptions\InvalidDatasetException;
use JOOservices\UserAgent\Exceptions\UserAgentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JOOservices\UserAgent\Exceptions\InvalidDatasetException
 */
final class InvalidDatasetExceptionTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }

    public function test_extends_base_exception(): void
    {
        $exception = InvalidDatasetException::invalidFormat('test');

        $this->assertInstanceOf(UserAgentException::class, $exception);
    }

    public function test_invalid_format_creates_exception(): void
    {
        $reason = $this->faker->sentence();
        $exception = InvalidDatasetException::invalidFormat($reason);

        $this->assertStringContainsString($reason, $exception->getMessage());
        $this->assertStringContainsString('Invalid dataset format', $exception->getMessage());
    }

    public function test_invalid_json_creates_exception_with_file_and_error(): void
    {
        $file = $this->faker->filePath();
        $error = $this->faker->sentence();
        $exception = InvalidDatasetException::invalidJson($file, $error);

        $this->assertStringContainsString($file, $exception->getMessage());
        $this->assertStringContainsString($error, $exception->getMessage());
    }

    public function test_empty_dataset_creates_exception(): void
    {
        $exception = InvalidDatasetException::emptyDataset();

        $this->assertStringContainsString('Dataset cannot be empty', $exception->getMessage());
    }
}
