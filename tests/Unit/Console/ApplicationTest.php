<?php

declare(strict_types=1);

namespace JOOservices\UserAgent\Tests\Unit\Console;

use JOOservices\UserAgent\Console\Application;
use JOOservices\UserAgent\Tests\Support\FixtureDataset;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    #[Test]
    public function test_CLI_H01_H04_json_is_always_an_array(): void
    {
        $stdout = '';
        $stderr = '';
        $app = new Application(
            FixtureDataset::generator(),
            static function (string $text) use (&$stdout): void {
                $stdout .= $text;
            },
            static function (string $text) use (&$stderr): void {
                $stderr .= $text;
            },
        );
        $exit = $app->run(['useragent', '--browser=chrome', '--os=windows', '--device=desktop', '--seed=42', '--format=json']);
        self::assertSame(0, $exit);
        self::assertSame('', $stderr);
        $decoded = json_decode($stdout, true, 32, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        self::assertCount(1, $decoded);
        self::assertIsArray($decoded[0]);
        self::assertIsString($decoded[0]['userAgent']);
        self::assertStringContainsString('Chrome/', $decoded[0]['userAgent']);
    }

    #[Test]
    public function test_CLI_H03_android_without_device_is_valid(): void
    {
        [$exit, $stdout] = $this->execute(['useragent', '--os=android']);
        self::assertSame(0, $exit);
        self::assertStringContainsString('Android', $stdout);
    }

    #[Test]
    public function test_CLI_H05_ndjson_has_one_object_per_line(): void
    {
        [$exit, $stdout] = $this->execute(['useragent', '--count=2', '--format=ndjson', '--seed=4']);
        self::assertSame(0, $exit);
        self::assertCount(2, array_filter(explode("\n", $stdout), static fn(string $line): bool => $line !== ''));
    }

    #[Test]
    public function test_CLI_H07_help_is_safe(): void
    {
        [$exit, $stdout] = $this->execute(['useragent', '--help']);
        self::assertSame(0, $exit);
        self::assertStringContainsString('--browser', $stdout);
        self::assertStringNotContainsStringIgnoringCase('googlebot', $stdout);
    }

    #[Test]
    public function test_CLI_U01_U02_usage_errors_exit_two(): void
    {
        foreach ([['useragent', '--browser=opera'], ['useragent', '--nope'], ['useragent', 'foo'], ['useragent', '--count=101']] as $argv) {
            [$exit, , $stderr] = $this->execute($argv);
            self::assertSame(2, $exit);
            self::assertNotSame('', $stderr);
        }
    }

    #[Test]
    public function test_CLI_U03_generation_error_exits_one(): void
    {
        [$exit, , $stderr] = $this->execute(['useragent', '--browser=safari', '--os=windows', '--device=desktop']);
        self::assertSame(1, $exit);
        self::assertStringContainsString('No compatible', $stderr);
    }

    /**
     * @param list<string> $argv
     * @return array{int, string, string}
     */
    private function execute(array $argv): array
    {
        $stdout = '';
        $stderr = '';
        $app = new Application(
            FixtureDataset::generator(),
            static function (string $text) use (&$stdout): void {
                $stdout .= $text;
            },
            static function (string $text) use (&$stderr): void {
                $stderr .= $text;
            },
        );

        return [$app->run($argv), $stdout, $stderr];
    }
}
